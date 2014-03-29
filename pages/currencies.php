<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam);
$path      = [0, 15];
$validCats = [1, 2, 3, 22];
$title     = [Util::ucFirst(Lang::$game['currencies'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_CURRENCY, -1, $cat ? $cat[0] : -1, User::$localeId]);

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if ($cat)
{
    $path[] = $cat[0];                                      // should be only one parameter anyway
    array_unshift($title, Lang::$currency['cat'][$cat[0]]);
}

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $money = new CurrencyList($cat ? array(['category', (int)$cat[0]]) : []);
    $money->addGlobalsToJscript();

    // menuId 15: Currency g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'tab'   => 0,
            'title' => implode(" - ", $title),
            'path'  => json_encode($path, JSON_NUMERIC_CHECK)
        ),
        'lv' => array(
            array(
                'file'   => 'currency',
                'data'   => $money->getListviewData(),
                'params' => []
            )
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('list-page-generic.tpl');

?>
