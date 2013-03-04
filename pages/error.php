<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

// Announcements
$announcements = DB::Aowow()->Select('SELECT * FROM ?_announcements WHERE flags & 0x10 AND (page = "*")');
foreach ($announcements as $k => $v)
    $announcements[$k]['text'] = Util::localizedString($v, 'text');


$smarty->assign('lang', array_merge(Lang::$main, Lang::$error));
$smarty->assign('announcements', $announcements);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->display('error.tpl');
exit();

?>
