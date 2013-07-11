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
        'file'   => 'title',
        'data'   => $titles->getListviewData(),
        'params' => []
    );

    if ($titles->hasDiffFields(['category']))
        $pageData['params']['visibleCols'] = "$['category']";

    if (!$titles->hasAnySource())
        $pageData['params']['hiddenCols'] = "$['source']";

    $smarty->saveCache($cacheKey, $pageData);
}


$page = array(
    'tab'   => 0,                                           // for g_initHeader($tab)
    'title' => implode(" - ", $title),
    'path'  => "[".implode(", ", $path)."]"
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);
$smarty->display('generic-no-filter.tpl');

?>
