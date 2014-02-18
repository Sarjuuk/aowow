<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cats      = Util::extractURLParams($pageParam);
$path      = [0, 7];
$title     = [Util::ucFirst(Lang::$game['factions'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_FACTION, -1, implode('.', $cats), User::$localeId]);
$validCats = array(
    1118 => [469, 891, 67, 892, 169],
    980  => [936],
    1097 => [1037, 1052, 1117],
    0    => true
);

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $conditions = [];

    if (!User::isInGroup(U_GROUP_STAFF))
        $conditions[] = ['reputationIndex', -1, '!'];       // unlisted factions

    if (isset($cats[0]) && empty($cats[1]))
    {
        if (!$cats[0])
            $conditions[] = ['parentFactionId', [1118, 980, 1097, 469, 891, 67, 892, 169, 1037, 1052, 1117, 936], '!'];
        else
        {
            $subs = DB::Aowow()->selectCol('SELECT id FROM ?_factions WHERE parentFactionId = ?d', $cats[0]);
            $conditions[] = ['OR', ['parentFactionId', $subs], ['id', $subs]];
        }

        $path[]       = $cats[0];

        $t = Lang::$faction['cat'][$cats[0]];
        array_unshift($title, is_array($t) ? $t[0] : $t);
    }
    else if (!empty($cats[1]))
    {
        $conditions[] = ['parentFactionId', $cats[1]];
        $path[]       = $cats[0];
        $path[]       = $cats[1];
        array_unshift($title, Lang::$faction['cat'][$cats[0]][$cats[1]]);
    }

    $factions = new FactionList($conditions);

    // menuId 7: Faction  g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title'  => implode(' - ', $title),
            'path'   => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'    => 0
        ),
        'lv' => array(
            array(
                'file'   => 'faction',
                'data'   => $factions->getListviewData(),
                'params' => []
            )
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('list-page-generic.tpl');

?>
