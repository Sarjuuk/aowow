<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$path      = [0, 8];
$validCats = [0, 1, 2];
$title     = [Util::ucFirst(Lang::$game['pets'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_PET, -1, isset($cat) ? $cat : -1, User::$localeId]);

if (!in_array($cat, $validCats))
    $smarty->error();

$path[] = $cat;                                             // should be only one parameter anyway

if (isset($cat))
    array_unshift($title, Lang::$pet['cat'][$cat]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $pets = new PetList(isset($cat) ? array(['type', (int)$cat]) : []);

    $pageData = array(
        'file'   => 'pet',
        'data'   => $pets->getListviewData(),
        'params' => array(
            'visibleCols' => "$['abilities']"
        )
    );

    if (($mask = $pets->hasDiffFields(['type'])) == 0x0)
        $pageData['params']['hiddenCols'] = "$['type']";

    $pets->reset();
    $pets->addGlobalsToJscript($smarty, GLOBALINFO_RELATED);

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
