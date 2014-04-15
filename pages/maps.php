<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
$smarty->updatePageVars(array(
    'title'  => Lang::$maps['maps'],
    'tab'    => 1,
    'reqCSS' => array(
        ['string' => 'zone-picker { margin-left: 4px }'],
        ['path' => STATIC_URL.'/css/Mapper.css'],
        ['path' => STATIC_URL.'/css/Mapper_ie6.css', 'ieCond' => 'lte IE 6']
    ),
    'reqJS'  => array(
        STATIC_URL.'/js/maps.js',
        STATIC_URL.'/js/Mapper.js',
        '?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']
    )
));
$smarty->assign('lang', array_merge(Lang::$main, Lang::$maps));

// load the page
$smarty->display('maps.tpl');

?>
