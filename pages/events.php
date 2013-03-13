<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$condition = [];
$path      = [0, 11];
$validCats = [0, 1, 2, 3];
$title     = [Lang::$game['events']];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_WORLDEVENT, -1, $cat, User::$localeId]);

if (!in_array($cat, $validCats))
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
                $condition[] = ['e.holidayId', 0];
                break;
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
    }

    $events = new WorldEventList($condition);

    $pageData = array(
        'page'   => $events->getListviewData(),
        'params' => array(
            'tabs'   => '$myTabs'
        )
    );

    $events->addGlobalsToJScript($pageData);

    $smarty->saveCache($cacheKey, $pageData);
}

// recalculate dates with now(); can't be cached, obviously
foreach ($pageData['page'] as &$data)
{
    $updated = WorldEventList::updateDates($data['startDate'], $data['endDate'], $data['rec']);
    $data['startDate'] = date(Util::$dateFormatLong, $updated['start']);
    $data['endDate']   = date(Util::$dateFormatLong, $updated['end']);
}


$page = array(
    'tab'   => 0,                                           // for g_initHeader($tab)
    'title' => implode(" - ", $title),
    'path'  => "[".implode(", ", $path)."]"
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->assign('data', $pageData);
$smarty->display('events.tpl');

?>
