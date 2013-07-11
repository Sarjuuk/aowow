<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$path      = [0, 15];
$validCats = [1, 2, 3, 22];
$title     = [Util::ucFirst(Lang::$game['currencies'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_CURRENCY, -1, isset($cat) ? $cat : -1, User::$localeId]);

if ($cat !== null && !in_array($cat, $validCats))
    $smarty->error();

if (isset($cat))
{
    $path[] = $cat;                                         // should be only one parameter anyway
    array_unshift($title, Lang::$currency['cat'][$cat]);
}

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $money = new CurrencyList(isset($cat) ? array(['category', (int)$cat]) : []);

    $pageData = array(
        'file'   => 'currency',
        'data'   => $money->getListviewData(),
        'params' => []
    );

    $money->addGlobalsToJscript($smarty);

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
