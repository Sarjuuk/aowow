<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam);
$path      = [0, 10];
$validCats = [0, 1, 2, 3, 4, 5, 6];
$title     = [Util::ucFirst(Lang::$game['titles'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_TITLE, -1, $cat ? $cat[0] : -1, User::$localeId]);

if ($cat)
{
    if (!in_array($cat[0], $validCats))
        $smarty->error();

    $path[] = $cat[0];                                  // should be only one parameter anyway
    array_unshift($title, Lang::$title['cat'][$cat[0]]);
}

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $titles = new TitleList($cat ? array(['category', (int)$cat[0]]) : []);

    // menuId 10: Title    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title' => implode(" - ", $title),
            'path'  => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'   => 0
        ),
        'lv'   => []
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

    $pageData['lv'][] = $lvTitles;

    $smarty->saveCache($cacheKey, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('list-page-generic.tpl');

?>
