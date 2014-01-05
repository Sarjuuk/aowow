<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cacheKey = implode('_', [CACHETYPE_PAGE, TYPE_CLASS, -1, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $classes = new CharClassList();

    $pageData = array(
        'listviews' => array(
            array(
                'file'   => 'class',
                'data'   => $classes->getListviewData(),
                'params' => []
            )
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}


// menuId 12: Class    g_initPath()
//  tabId  0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title' => Util::ucFirst(Lang::$game['classes']),
    'path'  => "[0, 12]",
    'tab'   => 0
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('generic-no-filter.tpl');

?>
