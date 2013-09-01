<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$path      = [0, 10];
$validCats = [0, 1, 2, 3, 4, 5, 6];
$title     = [Util::ucFirst(Lang::$game['titles'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_TITLE, -1, isset($cat) ? $cat : -1, User::$localeId]);

if (!in_array($cat, $validCats))
    $smarty->error();

$path[] = $cat;                                             // should be only one parameter anyway

if (isset($cat))
    array_unshift($title, Lang::$title['cat'][$cat]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $titles = new TitleList(isset($cat) ? array(['category', (int)$cat]) : []);

    $pageData = array(
        'listviews' => []
    );

    $lvTitles = array(
        'file'   => 'title',
        'data'   => $titles->getListviewData(),
        'params' => []
    );

    if ($titles->hasDiffFields(['category']))
        $lvTitles['params']['visibleCols'] = "$['category']";

    if (!$titles->hasAnySource())
        $lvTitles['params']['hiddenCols'] = "$['source']";

    $pageData['listviews'][] = $lvTitles;

    $smarty->saveCache($cacheKey, $pageData);
}


// menuId 10: Title    g_initPath()
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
