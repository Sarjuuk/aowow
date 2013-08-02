<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
$smarty->updatePageVars(array(
    'title'  => Lang::$maps['maps'],
    'tab'    => 1,
    'reqCSS' => array(
        ['string' => 'zone-picker { margin-left: 4px }'],
        ['path' => 'template/css/Mapper.css'],
        ['path' => 'template/css/Mapper_ie6.css', 'ieCond' => 'lte IE 6']
    ),
    'reqJS'  => array(
        'template/js/maps.js',
        'template/js/Mapper.js',
        '?data=zones'
    )
));
$smarty->assign('lang', array_merge(Lang::$main, Lang::$maps));

// load the page
$smarty->display('maps.tpl');

?>
