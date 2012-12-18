<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// Announcements
$announcements = DB::Aowow()->Select('SELECT * FROM ?_announcements WHERE flags & 0x10 AND (page = "maps" OR page = "*")');
foreach ($announcements as $k => $v)
    $announcements[$k]['text'] = Util::localizedString($v, 'text');

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
$smarty->assign('announcements', $announcements);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->display('maps.tpl');

?>
