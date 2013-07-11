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

require 'includes/class.community.php';

$id  = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_ACHIEVEMENT, $id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_ACHIEVEMENT, $id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $acv = new AchievementList(array(['id', $id]));
        if ($acv->error)
            die('$WowheadPower.registerAchievement(\''.$id.'\', '.User::$localeId.', {})');

        $x = '$WowheadPower.registerAchievement('.$id.', '.User::$localeId.",{\n";
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
    $acv = new AchievementList(array(['id', $id]));
    if ($acv->error)
        $smarty->notFound(Lang::$game['achievement']);

    $pageData['path']  = [0, 9];
    $pageData['title'] = [Util::ucfirst(Lang::$game['achievement'])];

    // create page title and path
    $curCat  = $acv->getField('category');
    $tmpPath = [];
    do
    {
        $tmpPath[] = $curCat;
        $curCat = DB::Aowow()->SelectCell('SELECT parentCategory FROM ?_achievementcategory WHERE id = ?d', $curCat);
    }
    while ($curCat > 0);

    if (!empty($tmpPath))
        $pageData['path'] = array_merge($pageData['path'], array_reverse($tmpPath));

    array_unshift($pageData['title'], $acv->getField('name', true));

    $acv->addGlobalsToJscript($smarty, GLOBALINFO_REWARDS);
    $pageData['page'] = $acv->getDetailedData()[$id];
    $acv->reset();

    // infobox content
    switch ($acv->getField('faction'))
    {
        case 1:
            $pageData['infoBox'][] = Lang::$main['side'].': <span class="alliance-icon">'.Lang::$game['si'][SIDE_ALLIANCE].'</span>';
            break;
        case 2:
            $pageData['infoBox'][] = Lang::$main['side'].': <span class="horde-icon">'.Lang::$game['si'][SIDE_HORDE].'</span>';
            break;
        default:                                        // case 3
            $pageData['infoBox'][] = Lang::$main['side'].': '.Lang::$game['si'][SIDE_BOTH];
    }

    // todo: crosslink with charactersDB to check if realmFirsts are still available

    $pageData['infoBox'] = array_merge($pageData['infoBox'], Lang::getInfoBoxForFlags($acv->getField('cuFlags')));

    // listview: "see also"
    $conditions = array(
        ['name_loc'.User::$localeId, $acv->getField('name', true)],
        ['id', $id, '!']
    );
    $saList = new AchievementList($conditions);
    $pageData['page']['saData']   = $saList->getListviewData();
    $pageData['page']['saParams'] = array(
        'id'          => 'see-also',
        'name'        => '$LANG.tab_seealso',
        'visibleCols' => "$['category']",
        'tabs'        => '$tabsRelated'
    );

    $saList->addGlobalsToJscript($smarty);

    // listview: "criteria of"
    $refs = DB::Aowow()->SelectCol('SELECT refAchievement FROM ?_achievementcriteria WHERE Type = ?d AND value1 = ?d',
        ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT,
        $id
    );
    if (!empty($refs))
    {
        $coList = new AchievementList(array(['id', $refs]));
        $pageData['page']['coData']   = $coList->getListviewData();
        $pageData['page']['coParams'] = array(
            'id'          => 'criteria-of',
            'name'        => '$LANG.tab_criteriaof',
            'visibleCols' => "$['category']",
            'tabs'        => '$tabsRelated'
        );

        $coList->addGlobalsToJscript($smarty);
    }

    // create rewards
    $pageData['page']['titleReward'] = [];
    $pageData['page']['itemReward']  = [];

    if ($foo = $acv->getField('rewards')[TYPE_ITEM])
    {
        $bar = new ItemList(array(['i.entry', $foo]));
        while ($bar->iterate())
        {
            $pageData['page']['itemReward'][$bar->id] = array(
                'name'    => $bar->getField('name', true),
                'quality' => $bar->getField('Quality')
            );
        }
    }

    if ($foo = $acv->getField('rewards')[TYPE_TITLE])
    {
        $bar = new TitleList(array(['id', $foo]));
        while ($bar->iterate())
            $pageData['page']['titleReward'][] = sprintf(Lang::$achievement['titleReward'], $bar->id, trim(str_replace('%s', '', $bar->getField('male', true))));
    }

    // *****
    // ACHIEVEMENT CRITERIA
    // *****

    $pageData['page']['criteria'] = [];
    $iconId = 1;
    $tmp_arr = [];
    $pageData['page']['icons'] = [];
    $pageData['page']['total_criteria'] = count($acv->getCriteria());

    $i = 0;                                             // stupid uninitialized iterator.....
    foreach ($acv->getCriteria() as $crt)
    {
        // hide hidden criteria for regular users (really do..?)
        // if (($crt['complete_flags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::$perms > 0)
            // continue;

        // alternative display option
        $displayMoney = $crt['complete_flags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER;

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
            // link to title
            case ACHIEVEMENT_CRITERIA_TYPE_EARNED_PVP_TITLE:
            // todo: crosslink
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
                $crtName = QuestList::getName($obj);
                $tmp['link'] = array(
                    'href' => '?quest='.$obj,
                    'text' => $crtName ? $crtName : $crtName,
                );
                break;
            // link to spell (/w icon)
            case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
            case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
            case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
            case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
            case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                $text = !empty($crtName) ? $crtName : SpellList::getName($obj);
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
                $crtItm = new ItemList(array(['id', $obj]));
                $text = $crtName ? $crtName : $crtItm->getField('name', true);
                $tmp['link'] = array(
                    'href'    => '?item='.$obj,
                    'text'    => $text,
                    'quality' => $crtItm->getField('Quality'),
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
                $crtName = FactionList::getName($obj);
                $tmp['link'] = array(
                    'href' => '?faction='.$obj,
                    'text' => $crtName ? $crtName : $crtName,
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
                break;
        }
        // If the right column
        if ($i++ % 2)
            $tmp_arr[] = $tmp;
        else
            $pageData['page']['criteria'][] = $tmp;

    }
    // If you found the second column - merge data from it to the end of the main body
    if ($tmp_arr)
        $pageData['page']['criteria'] = array_merge($pageData['page']['criteria'], $tmp_arr);

    // *****
    // ACHIEVEMENT CHAIN
    // *****

    if ($acv->getField('series'))
    {
        $pageData['page']['series'] = array(
            array(
                'id'     => $id,
                'name'   => $acv->getField('name', true),
                'parent' => $acv->getField('series') >> 16,
            )
        );
        $tmp = $pageData['page']['series'][0];
        while ($tmp)
        {
            $tmp = DB::Aowow()->selectRow('SELECT id, name_loc0, name_loc?d, series >> 16 AS parent FROM ?_achievement WHERE id = ?',
                User::$localeId,
                $pageData['page']['series'][0]['parent']
            );
            if ($tmp)
            {
                $tmp['name'] = Util::localizedString($tmp, 'name');
                array_unshift($pageData['page']['series'], $tmp);
            }
        }
        $tmp = end($pageData['page']['series']);
        while ($tmp)
        {
            $end = end($pageData['page']['series']);
            $tmp = DB::Aowow()->selectRow('SELECT id, name_loc0, name_loc?d, series >> 16 AS parent FROM ?_achievement WHERE (series >> 16) = ?',
                User::$localeId,
                $end['id']
            );
            if ($tmp)
            {
                $tmp['name'] = Util::localizedString($tmp, 'name');
                array_push($pageData['page']['series'], $tmp);
            }
        }
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// menuId 9: Achievement  g_initPath()
//  tabId 0: Database     g_initHeader()
$smarty->updatePageVars(array(
    'title'  => implode(" - ", $pageData['title']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_ACHIEVEMENT,
    'typeId' => $id
));

$smarty->assign('community', CommunityContent::getAll(TYPE_ACHIEVEMENT, $id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$achievement));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('achievement.tpl');

?>
