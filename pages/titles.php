<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat      = Util::extractURLParams($pageParam)[0];
$path     = [0, 10];
$cacheKey = implode('_', [CACHETYPE_PAGE, TYPE_TITLE, -1, isset($cat) ? $cat : -1, User::$localeId]);
$title    = [ucFirst(Lang::$game['titles'])];

$path[] = $cat;                                             // should be only one parameter anyway

if (isset($cat))
    array_unshift($title, Lang::$title['cat'][$cat]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $titles = new TitleList(isset($cat) ? array(['category', (int)$cat]) : []);
    $listview = $titles->getListviewData();

    $pageData = array(
        'page'   => $listview,
        'params' => array(
            'parent' => false,
            'tabs'   => false
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}

// Announcements
$announcements = DB::Aowow()->Select('SELECT * FROM ?_announcements WHERE flags & 0x10 AND (page = "titles" OR page = "*")');
foreach ($announcements as $k => $v)
    $announcements[$k]['text'] = Util::localizedString($v, 'text');

$page = array(
    'tab'   => 0,                                           // for g_initHeader($tab)
    'title' => implode(" - ", $title),
    'path'  => "[".implode(", ", $path)."]",
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('data', $pageData);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->assign('announcements', $announcements);
$smarty->display('titles.tpl');

?>
