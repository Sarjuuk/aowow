<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

@list($cat)   = Util::extractURLParams($pageParam);
$condition    = [];
$path         = [0, 11];
$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_WORLDEVENT, -1, $cat, User::$localeId]);

if ($cat)
    $path[] = $cat;

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    switch ($cat)
    {
        case 1:
            $condition[] = ['h.scheduleType', -1];
            break;
        case 2:
            $condition[] = ['h.scheduleType', [0, 1]];
            break;
        case 3:
            $condition[] = ['h.scheduleType', 2];
            break;
    }

    $events = new WorldEventList($condition);

    $pageData = array(
        'page'   => $events->getListviewData(),
        'params' => array(
            'tabs'   => '$myTabs'
        )
    );

    $events->addGlobalsToJScript($pageData);

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// recalculate dates with now(); can't be cached, obviously
foreach ($pageData['page'] as &$data)
{
    $updated = WorldEventList::updateDates($data['startDate'], $data['endDate'], $data['rec']);
    $data['startDate'] = date(Util::$dateFormatLong, $updated['start']);
    $data['endDate']   = date(Util::$dateFormatLong, $updated['end']);
}

$page = array(
    'tab'       => 0,                                       // for g_initHeader($tab)
    'title'     => ($cat ? Lang::$event['category'][$cat].' - ' : null) . Lang::$game['events'],
    'path'      => json_encode($path, JSON_NUMERIC_CHECK),
);


$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->assign('data', $pageData);
$smarty->display('events.tpl');

?>
