<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    todo:
        - different styles for newsbox
        - flags for news .. disabled, deleted, recurring, whatever..
*/

// load news
$rows = DB::Aowow()->select('SELECT * FROM ?_news ORDER BY time DESC, id DESC LIMIT 5');

foreach ($rows as $i => $row)
    $rows[$i]['text'] = Util::localizedString($row, 'text');


$smarty->assign('news', isset($rows) ? $rows : NULL);
$smarty->assign('lang', Lang::$main);
$smarty->display('main.tpl');

?>
