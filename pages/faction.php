<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_FACTION, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $faction = new FactionList(array(['id', $_id]));
    if ($faction->error)
        $smarty->notFound(Lang::$game['faction']);

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // Quartermaster if any
    if ($ids = $faction->getField('qmNpcIds'))
    {
        Util::$pageTemplate->extendGlobalIds(TYPE_NPC, $ids);

        $qmStr = Lang::$faction['quartermaster'].Lang::$colon;

        if (count($ids) ==  1)
            $qmStr .= '[npc='.$ids[0].']';
        else if (count($ids) > 1)
        {
            $qmStr .= '[ul]';
            foreach ($ids as $id)
                $qmStr .= '[li][npc='.$id.'][/li]';

            $qmStr .= '[/ul]';
        }

        $infobox[] = $qmStr;
    }

    // side if any
    if ($_ = $faction->getField('side'))
        $infobox[] = Lang::$main['side'].Lang::$colon.'[span class='.($_ == 1 ? 'alliance' : 'horde').'-icon]'.Lang::$game['si'][$_].'[/span]';

    /****************/
    /* Main Content */
    /****************/

    $pageData = array(
        'title'     => $faction->getField('name', true),
        'path'      => [0, 7],
        'relTabs'   => [],
        'spillover' => null,
        'infobox'   => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
        'buttons'   => array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true
        ),
        'page'      => array(
            'name' => $faction->getField('name', true),
            'id'   => $_id
        ),
    );

    if ($_ = $faction->getField('cat2'))
        $pageData['path'][] = $_;

    if ($_ = $faction->getField('cat'))
        $pageData['path'][] = $_;

    // Spillover Effects
    $conditions = array(
        ['id', $_id, '!'],                              // not self
        ['reputationIndex', -1, '!']                    // only gainable
    );

    if ($p = $faction->getField('parentFactionId'))     // linked via parent
        $conditions[] = ['OR', ['id', $p], ['parentFactionId', $p]];
    else
        $conditions[] = ['parentFactionId', $_id];      // self as parent

    $spillover = new FactionList($conditions);
    $spillover->addGlobalsToJscript(Util::$pageTemplate);
    $buff = [];

    foreach ($spillover->iterate() as $spillId => $__)
        if ($val = ($spillover->getField('spilloverRateIn') * $faction->getField('spilloverRateOut') * 100))
            $buff[] = '[tr][td][faction='.$spillId.'][/td][td][span class=q'.($val > 0 ? '2]+' : '10]').$val.'%[/span][/td][td]'.Lang::$game['rep'][$spillover->getField('spilloverMaxRank')].'[/td][/tr]';

    if ($buff)
        $pageData['spillover'] = '[h3 class=clear]'.Lang::$faction['spillover'].'[/h3][div margin=15px]'.Lang::$faction['spilloverDesc'].'[/div][table class=grid width=400px][tr][td width=150px][b]'.Util::ucFirst(Lang::$game['faction']).'[/b][/td][td width=100px][b]'.Lang::$spell['_value'].'[/b][/td][td width=150px][b]'.Lang::$faction['maxStanding'].'[/b][/td][/tr]'.implode('', $buff).'[/table]';

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: items
    $items = new ItemList(array(['requiredFaction', $_id]));
    if (!$items->error)
    {
        $items->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        $pageData['relTabs'][] = array(
            'file'    => 'item',
            'data'    => $items->getListviewData(),
            'showRep' => true,
            'params'  => array(
                'tabs'      => '$tabsRelated',
                'extraCols' => '$_',
                'sort'      => "$['standing', 'name']",
                'note'      => sprintf(Util::$filterResultString, '?items&filter=cr=17;crs='.$_id.';crv=0')
            )
        );
    }

    // tab: creatures with onKill reputation
    $cIds = DB::Aowow()->selectCol('SELECT DISTINCT creature_id FROM creature_onkill_reputation cor, ?_factions f WHERE
        (RewOnKillRepValue1 > 0 AND (RewOnKillRepFaction1 = ?d OR (cor.RewOnKillRepFaction1 = f.id AND f.parentFactionId = ?d AND IsTeamAward1 <> 0))) OR
        (RewOnKillRepValue2 > 0 AND (RewOnKillRepFaction2 = ?d OR (cor.RewOnKillRepFaction2 = f.id AND f.parentFactionId = ?d AND IsTeamAward2 <> 0)))',
        $_id, $faction->getField('parentFactionId'),
        $_id, $faction->getField('parentFactionId')
    );
    $killCreatures = new CreatureList(array(['id', $cIds]));
    if (!$killCreatures->error)
    {
        $killCreatures->addGlobalsToJscript($smarty);

        $pageData['relTabs'][] = array(
            'file'    => 'npc',
            'data'    => $killCreatures->getListviewData(),
            'showRep' => true,
            'params'  => array(
                'tabs'      => '$tabsRelated',
            )
        );
    }

    // tab: members
    $conditions = array(
        ['factionA', $faction->getField('templateIds')],
        ['factionH', $faction->getField('templateIds')],
        'OR'
    );

    $killCreatures = new CreatureList($conditions);
    if (!$killCreatures->error)
    {
        $killCreatures->addGlobalsToJscript($smarty);

        $pageData['relTabs'][] = array(
            'file'    => 'npc',
            'data'    => $killCreatures->getListviewData(),
            'showRep' => true,
            'params'  => array(
                'id'   => 'member',
                'name' => '$LANG.tab_member',
                'tabs' => '$tabsRelated',
            )
        );
    }

    // tab: quests
    $conditions = array(
        ['AND', ['RewardFactionId1', $_id], ['OR', ['RewardFactionValueId1', 0, '>'], ['RewardFactionValueIdOverride1', 0, '>']]],
        ['AND', ['RewardFactionId2', $_id], ['OR', ['RewardFactionValueId2', 0, '>'], ['RewardFactionValueIdOverride2', 0, '>']]],
        ['AND', ['RewardFactionId3', $_id], ['OR', ['RewardFactionValueId3', 0, '>'], ['RewardFactionValueIdOverride3', 0, '>']]],
        ['AND', ['RewardFactionId4', $_id], ['OR', ['RewardFactionValueId4', 0, '>'], ['RewardFactionValueIdOverride4', 0, '>']]],
        ['AND', ['RewardFactionId5', $_id], ['OR', ['RewardFactionValueId5', 0, '>'], ['RewardFactionValueIdOverride5', 0, '>']]],
        'OR'
    );
    $quests = new QuestList($conditions);
    if (!$quests->error)
    {
        $quests->addGlobalsToJscript($smarty, GLOBALINFO_ANY);

        $pageData['relTabs'][] = array(
            'file'    => 'quest',
            'data'    => $quests->getListviewData($_id),
            'showRep' => true,
            'params'  => array(
                'tabs' => '$tabsRelated',
                'extraCols' => '$_',
                'note' => sprintf(Util::$filterResultString, '?quests?filter=cr=1;crs='.$_id.';crv=0')
            )
        );
    }

    // tab: achievements
    $conditions = array(
        ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION],
        ['ac.value1', $_id]
    );
    $acvs = new AchievementList($conditions);
    if (!$acvs->error)
    {
        $acvs->addGlobalsToJscript($smarty, GLOBALINFO_ANY);

        $pageData['relTabs'][] = array(
            'file'   => 'achievement',
            'data'   => $acvs->getListviewData(),
            'params' => array(
                'id'          => 'criteria-of',
                'name'        => '$LANG.tab_criteriaof',
                'tabs'        => '$tabsRelated',
                'visibleCols' => "$['category']"
            )
        );
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}


// menuId 7: Faction  g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => $pageData['title']." - ".Util::ucfirst(Lang::$game['skill']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_FACTION,
    'typeId' => $_id
));
$smarty->assign('redButtons', $pageData['buttons']);
$smarty->assign('community', CommunityContent::getAll(TYPE_FACTION, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, [Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('faction.tpl');

?>
