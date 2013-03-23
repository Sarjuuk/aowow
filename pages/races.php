<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cacheKey = implode('_', [CACHETYPE_PAGE, TYPE_RACE, -1, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $races = new CharRaceList(array(['side', 0, '!']));     // only playable

    $pageData = array(
        'file'   => 'race',
        'data'   => $races->getListviewData(),
        'params' => array(
            'tabs'   => false
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}


$page = array(
    'tab'   => 0,                                           // for g_initHeader($tab)
    'title' => ucFirst(Lang::$game['races']),
    'path'  => "[0, 13]",
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->assign('lvData', $pageData);
$smarty->display('generic-no-filter.tpl');

?>
