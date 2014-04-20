<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id   = intVal($pageParam);
$_path = [0, 11];

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_WORLDEVENT, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $conditions = [];
    if ($_id < 0)
        $conditions[] = ['id', -$_id];
    else
        $conditions[] = ['holidayId', $_id];

    $event = new WorldEventList($conditions);
    if ($event->error)
        $smarty->notFound(Lang::$game['event'], $_id);

    $_hId = $event->getField('holidayId');
    $_eId = $event->getField('eventBak');

    // redirect if associated with a holiday
    if ($_hId && $_id != $_hId)
        header('Location: '.HOST_URL.'?event='.$_hId);

    $hasFilter = in_array($_hId, [372, 283, 285, 353, 420, 400, 284, 201, 374, 409, 141, 324, 321, 424, 335, 327, 341, 181, 404, 398, 301]);

    if ($_hId)
    {
        switch ($event->getField('scheduleType'))
        {
            case -1:    $_path[] = 1;   break;
            case  0:
            case  1:    $_path[] = 2;   break;
            case  2:    $_path[] = 3;   break;
        }
    }
    else
        $_path[] = 0;

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // boss
    if ($_ = $event->getField('bossCreature'))
    {
        Util::$pageTemplate->extendGlobalIds(TYPE_NPC, $_);
        $infobox[] = Lang::$npc['rank'][3].Lang::$colon.'[npc='.$_.']';
    }

    // finalized after the cache is handled

    /****************/
    /* Main Content */
    /****************/

    // menuId 11: Event    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'dates'   => array(
            'firstDate' => $event->getField('startTime'),
            'lastDate'  => $event->getField('endTime'),
            'length'    => $event->getField('length'),
            'rec'       => $event->getField('occurence')
        ),
        'page'    => array(
            'title'      => $event->getField('name', true).' - '.Util::ucFirst(Lang::$game['event']),
            'name'       => $event->getField('name', true),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'        => 0,
            'type'       => TYPE_WORLDEVENT,
            'typeId'     => $_id,
            'infobox'    => $infobox,
            'headIcons'  => [$event->getField('iconString')],
            'redButtons' => array(
                BUTTON_WOWHEAD => $_id > 0,
                BUTTON_LINKS   => true
            )
        ),
        'relTabs' => []
    );

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: npcs
    if ($npcIds = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, IF(ec.eventEntry > 0, 1, 0) AS added FROM creature c, game_event_creature ec WHERE ec.guid = c.guid AND ABS(ec.eventEntry) = ?d', $_eId))
    {
        $creatures = new CreatureList(array(['id', array_keys($npcIds)]));
        if (!$creatures->error)
        {
            $data = $creatures->getListviewData();

            foreach ($data as &$d)
                $d['method'] = $npcIds[$d['id']];

            $pageData['relTabs'][] = array(
                'file'   => CreatureList::$brickFile,
                'data'   => $data,
                'params' => array(
                    'tabs' => '$tabsRelated',
                    'note' => $hasFilter ? sprintf(Util::$filterResultString, '?npcs&filter=cr=38;crs='.$_hId.';crv=0') : null
                )
            );
        }
    }

    // tab: objects
    if ($objectIds = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, IF(eg.eventEntry > 0, 1, 0) AS added FROM gameobject g, game_event_gameobject eg WHERE eg.guid = g.guid AND ABS(eg.eventEntry) = ?d', $_eId))
    {
        $objects = new GameObjectList(array(['id', array_keys($objectIds)]));
        if (!$objects->error)
        {
            $data = $objects->getListviewData();
            foreach ($data as &$d)
                $d['method'] = $objectIds[$d['id']];

            $pageData['relTabs'][] = array(
                'file'   => GameObjectList::$brickFile,
                'data'   => $data,
                'params' => array(
                    'tabs' => '$tabsRelated',
                    'note' => $hasFilter ? sprintf(Util::$filterResultString, '?objects&filter=cr=16;crs='.$_hId.';crv=0') : null
                )
            );
        }
    }

    // tab: achievements
    if ($_ = $event->getField('achievementCatOrId'))
    {
        $condition = $_ > 0 ? [['category', $_]] : [['id', -$_]];
        $acvs = new AchievementList($condition);
        if (!$acvs->error)
        {
            $acvs->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);

            $pageData['relTabs'][] = array(
                'file'   => AchievementList::$brickFile,
                'data'   => $acvs->getListviewData(),
                'params' => array(
                    'tabs'        => '$tabsRelated',
                    'note'        => $hasFilter ? sprintf(Util::$filterResultString, '?achievements&filter=cr=11;crs='.$_hId.';crv=0') : null,
                    'visibleCols' => "$['category']"
                )
            );
        }
    }

    $itemCnd = [];
    if ($_hId)
    {
        $itemCnd = array(
            'OR',
            ['holidayId', $_hId],                               // direct requirement on item
        );

        // tab: quests (by table, go & creature)
        $quests = new QuestList(array(['holidayId', $_hId]));
        if (!$quests->error)
        {
            $quests->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_REWARDS);

            $pageData['relTabs'][] = array(
                'file'   => QuestList::$brickFile,
                'data'   => $quests->getListviewData(),
                'params' => array(
                    'tabs' => '$tabsRelated',
                    'note' => $hasFilter ? sprintf(Util::$filterResultString, '?quests&filter=cr=33;crs='.$_hId.';crv=0') : null
                )
            );

            $questItems = [];
            foreach (array_column($quests->rewards, TYPE_ITEM) as $arr)
                $questItems = array_merge($questItems, $arr);

            foreach (array_column($quests->requires, TYPE_ITEM) as $arr)
                $questItems = array_merge($questItems, $arr);

            if ($questItems)
                $itemCnd[] = ['id', $questItems];
        }
    }

    // items from creature
    if ($npcIds && !$creatures->error)
    {
        // vendor
        $cIds = $creatures->getFoundIDs();
        if ($sells = DB::Aowow()->selectCol('SELECT item FROM npc_vendor nv  WHERE entry IN (?a) UNION SELECT item FROM game_event_npc_vendor genv JOIN creature c ON genv.guid = c.guid WHERE c.id IN (?a)', $cIds, $cIds))
            $itemCnd[] = ['id', $sells];
    }

    // tab: items
    // not checking for loot ... cant distinguish between eventLoot and fillerCrapLoot
    if ($itemCnd)
    {
        $eventItems = new ItemList($itemCnd);
        if (!$eventItems->error)
        {
            $eventItems->addGlobalsToJScript(GLOBALINFO_SELF);

            $pageData['relTabs'][] = array(
                'file'   => ItemList::$brickFile,
                'data'   => $eventItems->getListviewData(),
                'params' => array(
                    'tabs' => '$tabsRelated',
                    'note' => $hasFilter ? sprintf(Util::$filterResultString, '?items&filter=cr=160;crs='.$_hId.';crv=0') : null
                )
            );
        }
    }

    // tab: see also (event conditions)
    if ($rel = DB::Aowow()->selectCol('SELECT IF(eventEntry = prerequisite_event, NULL, IF(eventEntry = ?d, -prerequisite_event, eventEntry)) FROM game_event_prerequisite WHERE prerequisite_event = ?d OR eventEntry = ?d', $_eId, $_eId, $_eId))
    {
        $list = [];
        array_walk($rel, function(&$v, $k) use (&$list) {
            if ($v > 0)
                $list[] = $v;
            else if ($v === null)
                Util::$pageTemplate->internalNotice(U_GROUP_EMPLOYEE, 'game_event_prerequisite: this event has itself as prerequisite');
        });

        if ($list)
        {
            $relEvents = new WorldEventList(array(['id', $list]));
            $relEvents->addGlobalsToJscript();
            $relData   = $relEvents->getListviewData(true);
            foreach ($relEvents->iterate() as $id => $__)
            {
                $relData[$id]['condition'] = array(
                    'type'   => TYPE_WORLDEVENT,
                    'typeId' => -$_eId,
                    'status' => 2
                );
            }

            $event->addGlobalsToJscript();
            foreach ($rel as $r)
            {
                if ($r >= 0)
                    continue;

                Util::$pageTemplate->extendGlobalIds(TYPE_WORLDEVENT, -$r);

                $d = $event->getListviewData(true);
                $d[-$_eId]['condition'][] = array(
                    'type'   => TYPE_WORLDEVENT,
                    'typeId' => $r,
                    'status' => 2
                );

                $relData= array_merge($relData, $d);
            }

            $pageData['relTabs'][] = array(
                'file'   => WorldEventList::$brickFile,
                'data'   => $relData,
                'params' => array(
                    'id'         => 'see-also',
                    'name'       => '$LANG.tab_seealso',
                    'tabs'       => '$tabsRelated',
                    'hiddenCols' => "$['date']",
                    'extraCols'  => '$[Listview.extraCols.condition]'
                )
            );
        }
    }


    $smarty->saveCache($cacheKeyPage, $pageData);
}

/***********/
/* Infobox */
/***********/

$updated = WorldEventList::updateDates($pageData['dates']);

// start
if ($updated['end'])
    array_push($pageData['page']['infobox'], Lang::$event['start'].Lang::$colon.date(Lang::$dateFmtLong, $updated['start']));

// end
if ($updated['end'])
    array_push($pageData['page']['infobox'], Lang::$event['end'].Lang::$colon.date(Lang::$dateFmtLong, $updated['end']));

// occurence
if ($updated['rec'] > 0)
    array_push($pageData['page']['infobox'], Lang::$event['interval'].Lang::$colon.Util::formatTime($updated['rec'] * 1000));

// in progress
if ($updated['start'] < time() && $updated['end'] > time())
    array_push($pageData['page']['infobox'], '[span class=q2]'.Lang::$event['inProgress'].'[/span]');

$pageData['page']['infobox'] = '[ul][li]'.implode('[/li][li]', $pageData['page']['infobox']).'[/li][/ul]';


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_WORLDEVENT, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
