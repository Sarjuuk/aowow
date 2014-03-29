<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam);
$condition = [];
$path      = [0, 11];
$validCats = [0, 1, 2, 3];
$title     = [Lang::$game['events']];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_WORLDEVENT, -1, $cat ? $cat[0] : -1, User::$localeId]);

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData))
{
    if ($cat)
    {
        $path[] = $cat[0];
        array_unshift($title, Lang::$event['category'][$cat[0]]);
        switch ($cat[0])
        {
            case 0: $condition[] = ['e.holidayId', 0];          break;
            case 1: $condition[] = ['h.scheduleType', -1];     break;
            case 2: $condition[] = ['h.scheduleType', [0, 1]];  break;
            case 3: $condition[] = ['h.scheduleType', 2];       break;
        }
    }

    $events = new WorldEventList($condition);
    $events->addGlobalsToJScript();

    $deps = [];
    foreach ($events->iterate() as $__)
        if ($d = $events->getField('requires'))
            $deps[$events->id] = $d;


    // menuId 11: Event    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'tab'   => 0,
            'title' => implode(" - ", $title),
            'path'  => json_encode($path, JSON_NUMERIC_CHECK)
        ),
        'lv'   => [],
        'deps' => $deps
    );

    $pageData['lv'][] = array(
        'file'   => 'event',
        'data'   => $events->getListviewData(),
        'params' => ['tabs' => '$myTabs']
    );

    $pageData['lv'][] = array(
        'file'   => 'calendar',
        'data'   => array_filter($events->getListviewData(), function($x) {return $x['id'] > 0;}),
        'params' => array(
            'tabs'      => '$myTabs',
            'hideCount' => 1
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}

// recalculate dates with now(); can't be cached, obviously
foreach ($pageData['lv'] as &$views)
{
    foreach ($views['data'] as &$data)
    {
        // is a followUp-event
        if (!empty($pageData['deps'][$data['id']]))
        {
            $data['startDate'] = $data['endDate'] = false;
            unset($data['_date']);
            continue;
        }

        $updated = WorldEventList::updateDates($data['_date']);
        unset($data['_date']);
        $data['startDate'] = $updated['start'] ? date(Util::$dateFormatInternal, $updated['start']) : false;
        $data['endDate']   = $updated['end']   ? date(Util::$dateFormatInternal, $updated['end'])   : false;
        $data['rec']       = $updated['rec'];
    }
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('list-page-generic.tpl');

?>
