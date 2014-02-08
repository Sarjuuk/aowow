<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cacheKey = implode('_', [CACHETYPE_PAGE, TYPE_RACE, -1, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $races = new CharRaceList(array(['side', 0, '!']));     // only playable

    // menuId 13: Race     g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title' => Util::ucFirst(Lang::$game['races']),
            'path'  => "[0, 13]",
            'tab'   => 0
        ),
        'lv' => array(
            array(
                'file'   => 'race',
                'data'   => $races->getListviewData(),
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
