<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$smarty->assign('lang', array_merge(Lang::$main, Lang::$error));
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->display('error.tpl');
exit();

?>
