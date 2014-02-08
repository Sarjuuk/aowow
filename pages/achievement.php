<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*  Notes:
*       maybe for future use:
*           - wring something useful/displayable out of world.achievement_criteria_data
*           - display on firstkills if achieved yet or not
*
*       create bars with
*       g_createProgressBar(c)
*       var c = {
*           text: "",
*           hoverText: "",
*           color: "",      // cssClassName rep[0-7] | ach[0|1]
*           width: ,        // 0 <=> 100
*       }
*/

require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_ACHIEVEMENT, $_id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_ACHIEVEMENT, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $acv = new AchievementList(array(['id', $_id]));
        if ($acv->error)
            die('$WowheadPower.registerAchievement(\''.$_id.'\', '.User::$localeId.', {})');

        $x = '$WowheadPower.registerAchievement('.$_id.', '.User::$localeId.",{\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($acv->getField('name', true))."',\n";
        $x .= "\ticon: '".urlencode($acv->getField('iconString'))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".$acv->renderTooltip()."'\n";
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }
    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $acv = new AchievementList(array(['id', $_id]));
    if ($acv->error)
        $smarty->notFound(Lang::$game['achievement'], $_id);

    // create page title and path
    $curCat  = $acv->getField('category');
    $path    = [];
    do
    {
        array_unshift($path, $curCat);
        $curCat = DB::Aowow()->SelectCell('SELECT parentCategory FROM ?_achievementcategory WHERE id = ?d', $curCat);
    }
    while ($curCat > 0);

    array_unshift($path, 0, 9);

    $acv->addGlobalsToJscript($smarty, GLOBALINFO_REWARDS);

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // points
    if ($_ = $acv->getField('points'))
        $infobox[] = Lang::$achievement['points'].Lang::$colon.'[achievementpoints='.$_.']';

    // location
        // todo (low)

    // faction
    switch ($acv->getField('faction'))
    {
        case 1:
            $infobox[] = Lang::$main['side'].Lang::$colon.'[span class=alliance-icon]'.Lang::$game['si'][SIDE_ALLIANCE].'[/span]';
            break;
        case 2:
            $infobox[] = Lang::$main['side'].Lang::$colon.'[span class=horde-icon]'.Lang::$game['si'][SIDE_HORDE].'[/span]';
            break;
        default:                                        // case 3
            $infobox[] = Lang::$main['side'].Lang::$colon.Lang::$game['si'][SIDE_BOTH];
    }

    // todo (low): crosslink with charactersDB to check if realmFirsts are still available

    $infobox = array_merge($infobox, Lang::getInfoBoxForFlags($acv->getField('cuFlags')));

    /**********/
    /* Series */
    /**********/

    $series = [];

    if ($c = $acv->getField('chainId'))
    {
        $chainAcv = new AchievementList(array(['chainId', $c]));

        foreach ($chainAcv->iterate() as $aId => $__)
        {
            $pos = $chainAcv->getField('chainPos');
            if (!isset($series[$pos]))
                $series[$pos] = [];

            $series[$pos][] = array(
                'side'    => $chainAcv->getField('faction'),
                'typeStr' => Util::$typeStrings[TYPE_ACHIEVEMENT],
                'typeId'  => $aId,
                'name'    => $chainAcv->getField('name', true)
            );
        }
    }

    /****************/
    /* Main Content */
    /****************/

    // menuId 9: Achievement  g_initPath()
    //  tabId 0: Database     g_initHeader()
    $pageData = array(
        'page'    => array(
            'title'       => $acv->getField('name', true).' - '.Util::ucfirst(Lang::$game['achievement']),
            'path'        => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'         => 0,
            'type'        => TYPE_ACHIEVEMENT,
            'typeId'      => $_id,
            'headIcons'   => $acv->getField('iconString'),
            'infobox'     => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
            'series'      => $series,
            'redButtons'  => array(
                BUTTON_LINKS   => ['color' => 'ffffff00', 'linkId' => Util::$typeStrings[TYPE_ACHIEVEMENT].':'.$_id.':&quot;..UnitGUID(&quot;player&quot;)..&quot;:0:0:0:0:0:0:0:0'],
                BUTTON_WOWHEAD => true
            ),
            'name'        => $acv->getField('name', true),
            'description' => $acv->getField('description', true),
            'count'       => $acv->getField('reqCriteriaCount'),
            'reward'      => $acv->getField('reward', true),
            'nCriteria'   => count($acv->getCriteria()),
            'titleReward' => [],
            'itemReward'  => [],
            'criteria'    => [],
            'icons'       => []
        ),
        'relTabs' => []
    );

    // create rewards
    if ($foo = $acv->getField('rewards')[TYPE_ITEM])
    {
        $bar = new ItemList(array(['i.id', $foo]));
        foreach ($bar->iterate() as $__)
        {
            $pageData['page']['itemReward'][$bar->id] = array(
                'name'    => $bar->getField('name', true),
                'quality' => $bar->getField('quality')
            );
        }
    }

    if ($foo = $acv->getField('rewards')[TYPE_TITLE])
    {
        $bar = new TitleList(array(['id', $foo]));
        foreach ($bar->iterate() as $__)
            $pageData['page']['titleReward'][] = sprintf(Lang::$achievement['titleReward'], $bar->id, trim(str_replace('%s', '', $bar->getField('male', true))));
    }

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: see also
    $conditions = array(
        ['name_loc'.User::$localeId, $acv->getField('name', true)],
        ['id', $_id, '!']
    );
    $saList = new AchievementList($conditions);
    $pageData['relTabs'][] = array(
        'file'   => 'achievement',
        'data'   => $saList->getListviewData(),
        'params' => array(
            'id'          => 'see-also',
            'name'        => '$LANG.tab_seealso',
            'visibleCols' => "$['category']",
            'tabs'        => '$tabsRelated'
        )
    );

    $saList->addGlobalsToJscript($smarty);

    // tab: criteria of
    $refs = DB::Aowow()->SelectCol('SELECT refAchievementId FROM ?_achievementcriteria WHERE Type = ?d AND value1 = ?d',
        ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT,
        $_id
    );
    if (!empty($refs))
    {
        $coList = new AchievementList(array(['id', $refs]));
        $pageData['relTabs'][] = array(
            'file'   => 'achievement',
            'data'   => $coList->getListviewData(),
            'params' => array(
                'id'          => 'criteria-of',
                'name'        => '$LANG.tab_criteriaof',
                'visibleCols' => "$['category']",
                'tabs'        => '$tabsRelated'
            )
        );

        $coList->addGlobalsToJscript($smarty);
    }

    /*****************/
    /* Criteria List */
    /*****************/

    $iconId   = 1;
    $rightCol = [];

    foreach ($acv->getCriteria() as $i => $crt)
    {
        // hide hidden criteria for regular users (really do..?)
        // if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::$perms > 0)
            // continue;

        // alternative display option
        $displayMoney = $crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER;

        $crtName      = Util::localizedString($crt, 'name');
        $tmp          = array(
            'id'   => $crt['id'],
            'name' => $crtName,
            'type' => $crt['type'],
        );

        $obj = (int)$crt['value1'];
        $qty = (int)$crt['value2'];

        switch ($crt['type'])
        {
            // link to npc
            case ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE:
            case ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE:
                $tmp['link'] = array(
                    'href' => '?npc='.$obj,
                    'text' => $crtName,
                );
                $tmp['extra_text'] = Lang::$achievement['slain'];
                break;
            // link to area (by map)
            case ACHIEVEMENT_CRITERIA_TYPE_WIN_BG:
            case ACHIEVEMENT_CRITERIA_TYPE_WIN_ARENA:
            case ACHIEVEMENT_CRITERIA_TYPE_PLAY_ARENA:
            case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_BATTLEGROUND:
            case ACHIEVEMENT_CRITERIA_TYPE_DEATH_AT_MAP:
                if ($zoneId = DB::Aowow()->selectCell('SELECT id FROM ?_zones WHERE mapId = ? LIMIT 1', $obj))
                    $tmp['link'] = array(
                        'href' => '?zone='.$zoneId,
                        'text' => $crtName,
                    );
                else
                    $tmp['extra_text'] = $crtName;
                break;
            // link to area
            case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE:
            case ACHIEVEMENT_CRITERIA_TYPE_HONORABLE_KILL_AT_AREA:
                $tmp['link'] = array(
                    'href' => '?zone='.$obj,
                    'text' => $crtName,
                );
                break;
            // link to skills
            case ACHIEVEMENT_CRITERIA_TYPE_REACH_SKILL_LEVEL:
            case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LEVEL:
            case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILLLINE_SPELLS:
            case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LINE:
                $tmp['link'] = array(
                    'href' => '?skill='.$obj,
                    'text' => $crtName,
                );
                break;
            // link to class
            case ACHIEVEMENT_CRITERIA_TYPE_HK_CLASS:
                $tmp['link'] = array(
                    'href' => '?class='.$obj,
                    'text' => $crtName,
                );
                break;
            // link to race
            case ACHIEVEMENT_CRITERIA_TYPE_HK_RACE:
                $tmp['link'] = array(
                    'href' => '?race='.$obj,
                    'text' => $crtName,
                );
                break;
            // link to title - todo (low): crosslink
            case ACHIEVEMENT_CRITERIA_TYPE_EARNED_PVP_TITLE:
                $tmp['extra_text'] = Util::ucFirst(Lang::$game['title']).Lang::$colon.$crtName;
                break;
            // link to achivement (/w icon)
            case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT:
                $tmp['link'] = array(
                    'href' => '?achievement='.$obj,
                    'text' => $crtName,
                );
                $tmp['icon'] = $iconId;
                $pageData['page']['icons'][] = array(
                    'itr'  => $iconId++,
                    'type' => 'g_achievements',
                    'id'   => $obj,
                );
                $smarty->extendGlobalIds(TYPE_ACHIEVEMENT, $obj);
                break;
            // link to quest
            case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST:
                // $crtName = ;
                $tmp['link'] = array(
                    'href' => '?quest='.$obj,
                    'text' => $crtName ? $crtName : QuestList::getName($obj),
                );
                break;
            // link to spell (/w icon)
            case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
            case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
            case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
            case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
            case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                $text = $crtName ? $crtName : SpellList::getName($obj);
                $tmp['link'] = array(
                    'href' => '?spell='.$obj,
                    'text' => $text
                );
                $smarty->extendGlobalIds(TYPE_SPELL, $obj);
                $tmp['icon'] = $iconId;
                $pageData['page']['icons'][] = array(
                    'itr'  => $iconId++,
                    'type' => 'g_spells',
                    'id'   => $obj,
                );
                break;
            // link to item (/w icon)
            case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
            case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
            case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
            case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                $crtItm = new ItemList(array(['i.id', $obj]));
                $text = $crtName ? $crtName : $crtItm->getField('name', true);
                $tmp['link'] = array(
                    'href'    => '?item='.$obj,
                    'text'    => $text,
                    'quality' => $crtItm->getField('quality'),
                    'count'   => $qty,
                );
                $crtItm->addGlobalsToJscript($smarty);
                $tmp['icon'] = $iconId;
                $pageData['page']['icons'][] = array(
                    'itr'   => $iconId++,
                    'type'  => 'g_items',
                    'id'    => $obj,
                    'count' => $qty,
                );
                break;
            // link to faction (/w target reputation)
            case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                $tmp['link'] = array(
                    'href' => '?faction='.$obj,
                    'text' => $crtName ? $crtName : FactionList::getName($obj),
                );
                $tmp['extra_text'] = ' ('.Lang::getReputationLevelForPoints($qty).')';
                break;
            // link to GObject
            case ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT:
            case ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT:
                $tmp['link'] = array(
                    'href' => '?object='.$obj,
                    'text' => $crtName,
                );
                break;
            default:
                $tmp['standard'] = true;
                // Add a gold coin icon
                $tmp['extra_text'] = $displayMoney ? Util::formatMoney($qty) : $crtName;
                var_dump($tmp);
                break;
        }
        // If the right column
        if ($i % 2)
            $pageData['page']['criteria'][] = $tmp;
        else
            $rightCol[] = $tmp;
    }

    // If you found the second column - merge data from it to the end of the main body
    if ($rightCol)
        $pageData['page']['criteria'] = array_merge($pageData['page']['criteria'], $rightCol);

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_ACHIEVEMENT, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$achievement, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('achievement.tpl');

?>
