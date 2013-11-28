<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$condition = [];
$path      = [0, 11];
$validCats = [0, 1, 2, 3];
$title     = [Lang::$game['events']];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_WORLDEVENT, -1, $cat, User::$localeId]);

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

$path[] = $cat;

if (isset($cat))
    array_unshift($title, Lang::$event['category'][$cat]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    if ($cat !== null)
    {
        switch ($cat)
        {
            case 0:
                $condition[] = ['holidayId', 0];
                break;
            case 1:
                $condition[] = ['scheduleType', -1];
                break;
            case 2:
                $condition[] = ['scheduleType', [0, 1]];
                break;
            case 3:
                $condition[] = ['scheduleType', 2];
                break;
        }
    }

    $events = new WorldEventList($condition);

    $deps = [];
    foreach ($events->iterate() as $__)
        if ($d = $events->getField('requires'))
            $deps[$events->id] = $d;

    $pageData = array(
        'listviews' => [],
        'deps'      => $deps
    );

    $pageData['listviews'][] = array(
        'file'   => 'event',
        'data'   => $events->getListviewData(),
        'params' => ['tabs' => '$myTabs']
    );

    $pageData['listviews'][] = array(
        'file'   => 'calendar',
        'data'   => array_filter($events->getListviewData(), function($x) {return $x['id'] > 0;}),
        'params' => array(
            'tabs'      => '$myTabs',
            'hideCount' => 1
        )
    );

    $events->addGlobalsToJScript($smarty);

    $smarty->saveCache($cacheKey, $pageData);
}

// recalculate dates with now(); can't be cached, obviously
foreach ($pageData['listviews'] as &$views)
{
    foreach ($views['data'] as &$data)
    {
        // is a followUp-event
        if (!empty($pageData['deps'][$data['id']]))
        {
            $data['startDate'] = $data['endDate'] = false;
            continue;
        }

        $updated = WorldEventList::updateDates($data['startDate'], $data['endDate'], $data['rec']);
        $data['startDate'] = $updated['start'] ? date(Util::$dateFormatLong, $updated['start']) : false;
        $data['endDate']   = $updated['end']   ? date(Util::$dateFormatLong, $updated['end'])   : false;
    }
}


// menuId 11: Event    g_initPath()
//  tabId  0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title' => implode(" - ", $title),
    'path'  => "[".implode(", ", $path)."]",
    'tab'   => 0
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('generic-no-filter.tpl');

?>
