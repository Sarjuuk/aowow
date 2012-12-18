<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

$smarty->assign('lang', array_merge(Lang::$main, Lang::$error));
$smarty->display('error.tpl');
exit();

?>
