<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$page = array(
    'reqCSS' => array(
        array('string' => 'zone-picker { margin-left: 4px }'),
        array('path' => 'template/css/Mapper.css', 'condition' => false),
        array('path' => 'template/css/Mapper_ie6.css', 'condition' => 'lte IE 6'),
    ),
    'reqJS'  => array(
        array('path' => 'template/js/maps.js'),
        array('path' => 'template/js/Mapper.js'),
        array('path' => '?data=zones'),
    ),
    'title' => Lang::$maps['maps'],
    'tab' => 1
);


$smarty->updatePageVars($page);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$maps));
$smarty->display('maps.tpl');

?>
