<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id   = intVal($pageParam);
$_path = [0, 7];

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_FACTION, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $faction = new FactionList(array(['id', $_id]));
    if ($faction->error)
        $smarty->notFound(Lang::$game['faction'], $_id);

    if ($_ = $faction->getField('cat2'))
        $_path[] = $_;

    if ($_ = $faction->getField('cat'))
        $_path[] = $_;

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
        $infobox[] = Lang::$main['side'].Lang::$colon.'[span class=icon-'.($_ == 1 ? 'alliance' : 'horde').']'.Lang::$game['si'][$_].'[/span]';

    /****************/
    /* Main Content */
    /****************/

    // menuId 7: Faction  g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page'    => array(
            'title'      => $faction->getField('name', true)." - ".Util::ucfirst(Lang::$game['faction']),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'        => 0,
            'type'       => TYPE_FACTION,
            'typeId'     => $_id,
            'extraText'  => '',
            'infobox'    => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true
            ),
            'name' => $faction->getField('name', true)
        ),
        'relTabs' => []
    );

    // Spillover Effects
    /* todo (low): also check on reputation_spillover_template (but its data is identical to calculation below
    $rst = DB::Aowow()->selectRow('SELECT
        CONCAT_WS(" ", faction1, faction2, faction3, faction4) AS faction,
        CONCAT_WS(" ", rate_1,   rate_2,   rate_3,   rate_4)   AS rate,
        CONCAT_WS(" ", rank_1,   rank_2,   rank_3,   rank_4)   AS rank
        FROM reputation_spillover_template WHERE faction = ?d', $_id);
    */


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
        $pageData['page']['extraText'] .= '[h3 class=clear]'.Lang::$faction['spillover'].'[/h3][div margin=15px]'.Lang::$faction['spilloverDesc'].'[/div][table class=grid width=400px][tr][td width=150px][b]'.Util::ucFirst(Lang::$game['faction']).'[/b][/td][td width=100px][b]'.Lang::$spell['_value'].'[/b][/td][td width=150px][b]'.Lang::$faction['maxStanding'].'[/b][/td][/tr]'.implode('', $buff).'[/table]';


    // reward rates
    if ($rates = DB::Aowow()->selectRow('SELECT * FROM reputation_reward_rate WHERE faction = ?d', $_id))
    {
        $buff = '[h3 class=clear][Custom Reward Rate][/h3][table width=100px]';

        foreach ($rates as $k => $v)
        {
            if ($v == 1)
                continue;

            switch ($k)
            {
                case 'quest_rate':          $buff .= '[tr][td]Quests:[/td]'; break;
                case 'quest_daily_rate':    $buff .= '[tr][td]Daily Quests:[/td]'; break;
                case 'quest_weekly_rate':   $buff .= '[tr][td]Weekly Quests:[/td]'; break;
                case 'quest_monthly_rate':  $buff .= '[tr][td]Monthly Quests:[/td]'; break;
                case 'creature_rate':       $buff .= '[tr][td]Creatures:[/td]'; break;
                case 'spell_rate':          $buff .= '[tr][td]Spells:[/td]'; break;
            }

            $buff .= '[td]x'.number_format($v, 1).'[/td][/tr]';
        }

        $buff .= '[/table]';

        $pageData['page']['extraText'] .= $buff;
    }

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
    if ($faction->getField('reputationIndex') != -1)        // only if you can actually gain reputation by kills
    {
        $cIds = DB::Aowow()->selectCol('SELECT DISTINCT cor.creature_id FROM creature_onkill_reputation cor, ?_factions f WHERE
            (cor.RewOnKillRepValue1 > 0 AND (cor.RewOnKillRepFaction1 = ?d OR (cor.RewOnKillRepFaction1 = f.id AND f.parentFactionId = ?d AND cor.IsTeamAward1 <> 0))) OR
            (cor.RewOnKillRepValue2 > 0 AND (cor.RewOnKillRepFaction2 = ?d OR (cor.RewOnKillRepFaction2 = f.id AND f.parentFactionId = ?d AND cor.IsTeamAward2 <> 0)))',
            $_id, $faction->getField('parentFactionId'),
            $_id, $faction->getField('parentFactionId')
        );

        if ($cIds)
        {
            $killCreatures = new CreatureList(array(['id', $cIds]));
            if (!$killCreatures->error)
            {
                $killCreatures->addGlobalsToJscript($smarty);

                $pageData['relTabs'][] = array(
                    'file'    => 'creature',
                    'data'    => $killCreatures->getListviewData(),
                    'showRep' => true,
                    'params'  => array(
                        'tabs'      => '$tabsRelated',
                    )
                );
            }
        }
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
            'file'    => 'creature',
            'data'    => $killCreatures->getListviewData(),
            'showRep' => true,
            'params'  => array(
                'id'   => 'member',
                'name' => '$LANG.tab_members',
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


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_FACTION, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, [Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
