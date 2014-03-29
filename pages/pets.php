<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam);
$path      = [0, 8];
$validCats = [0, 1, 2];
$title     = [Util::ucFirst(Lang::$game['pets'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_PET, -1, $cat ? $cat[0] : -1, User::$localeId]);

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if ($cat)
{
    $path[] = $cat;
    array_unshift($title, Lang::$pet['cat'][$cat[0]]);
}

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $pets = new PetList($cat ? array(['type', (int)$cat[0]]) : []);
    $pets->addGlobalsToJScript(GLOBALINFO_RELATED);

    $lvPet = array(
        'file'   => 'pet',
        'data'   => $pets->getListviewData(),
        'params' => array(
            'visibleCols' => "$['abilities']"
        )
    );

    if (!$pets->hasDiffFields(['type']))
        $lvPet['params']['hiddenCols'] = "$['type']";

    // menuId 8: Pets     g_initPath()
    //  tabid 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title' => implode(" - ", $title),
            'path'  => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'   => 0
        ),
        'lv'   => [$lvPet]
    );

    $smarty->saveCache($cacheKey, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('list-page-generic.tpl');

?>
