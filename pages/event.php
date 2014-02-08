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

    $hId = $event->getField('holidayId');

    // redirect if associated with a holiday
    if ($hId && $_id != $hId)
        header('Location: '.STATIC_URL.'?event='.$hId);

    if ($hId)
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

    // - boss
    // - faction (only darkmoon faire)

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

    // NPC spawns

    // GO spawns

    // Quests

    // Items requiring Holiday

    // tab: see also (event conditions)
    $eId = $event->getField('eventBak');
    if($rel = DB::Aowow()->selectCol('SELECT IF(eventEntry = prerequisite_event, NULL, IF(eventEntry = ?d, -prerequisite_event, eventEntry)) FROM game_event_prerequisite WHERE prerequisite_event = ?d OR eventEntry = ?d', $eId, $eId, $eId))
    {
        $list = [];
        array_walk($rel, function(&$v, $k) use (&$list) {
            if ($v > 0)
                $list[] = $v;
            else if ($v == null)
                Util::$pageTemplate->internalNotice(U_GROUP_EMPLOYEE, 'game_event_prerequisite: this event has itself as prerequisite');
        });

        $relEvents = new WorldEventList(array(['id', $list]));
        $relEvents->addGlobalsToJscript(Util::$pageTemplate);
        $relData   = $relEvents->getListviewData(true);
        foreach ($relEvents->iterate() as $id => $__)
        {
            $relData[$id]['condition'] = array(
                'type'   => TYPE_WORLDEVENT,
                'typeId' => -$eId,
                'status' => 2
            );
        }

        $event->addGlobalsToJscript(Util::$pageTemplate);
        foreach ($rel as $r)
        {
            if ($r >= 0)
                continue;

            Util::$pageTemplate->extendGlobalIds(TYPE_WORLDEVENT, -$r);

            $d = $event->getListviewData(true);
            $d[-$eId]['condition'] = array(
                'type'   => TYPE_WORLDEVENT,
                'typeId' => $r,
                'status' => 2
            );

            $relData= array_merge($relData, $d);
        }

        $pageData['relTabs'][] = array(
            'file'   => 'event',
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


    $smarty->saveCache($cacheKeyPage, $pageData);
}

/***********/
/* Infobox */
/***********/

$updated = WorldEventList::updateDates($pageData['dates']);

// in progress
if ($updated['start'] < time() && $updated['end'] > time())
    array_unshift($pageData['page']['infobox'], '[span class=q2]'.Lang::$event['inProgress'].'[/span]');

// occurence
if ($updated['rec'] > 0)
    array_unshift($pageData['page']['infobox'], Lang::$event['interval'].Lang::$colon.Util::formatTime($updated['rec'] * 1000));

// end
if ($updated['end'])
    array_unshift($pageData['page']['infobox'], Lang::$event['end'].Lang::$colon.date(Lang::$dateFmtLong, $updated['end']));

// start
if ($updated['end'])
    array_unshift($pageData['page']['infobox'], Lang::$event['start'].Lang::$colon.date(Lang::$dateFmtLong, $updated['start']));

$pageData['page']['infobox'] = '[ul][li]'.implode('[/li][li]', $pageData['page']['infobox']).'[/li][/ul]';


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_WORLDEVENT, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
