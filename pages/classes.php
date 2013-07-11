<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cacheKey = implode('_', [CACHETYPE_PAGE, TYPE_CLASS, -1, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $classes = new CharClassList();

    $pageData = array(
        'file'   => 'class',
        'data'   => $classes->getListviewData(),
        'params' => []
    );

    $smarty->saveCache($cacheKey, $pageData);
}


$page = array(
    'tab'   => 0,                                           // for g_initHeader($tab)
    'title' => Util::ucFirst(Lang::$game['classes']),
    'path'  => "[0, 12]",
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);
$smarty->display('generic-no-filter.tpl');

?>
