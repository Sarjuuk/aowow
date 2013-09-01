<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cacheKey = implode('_', [CACHETYPE_PAGE, TYPE_RACE, -1, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $races = new CharRaceList(array(['side', 0, '!']));     // only playable

    $pageData = array(
        'listviews' => array(
            array(
                'file'   => 'race',
                'data'   => $races->getListviewData(),
                'params' => []
            )
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}


// menuId 13: Race     g_initPath()
//  tabId  0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title' => Util::ucFirst(Lang::$game['races']),
    'path'  => "[0, 13]",
    'tab'   => 0
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('generic-no-filter.tpl');

?>
