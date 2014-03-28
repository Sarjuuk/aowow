<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$filter     = [];
$cats       = Util::extractURLParams($pageParam);
$path       = [0, 4];
$title      = [Util::ucFirst(Lang::$game['npcs'])];
$filterHash = !empty($_GET['filter']) ? sha1(serialize($_GET['filter'])) : -1;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_NPC, -1, ($cats ? $cats[0] : -1).$filterHash, User::$localeId]);
$validCats  = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    $conditions = [];
    if ($cats)
    {
        $conditions[] = ['type', $cats[0]];
        $path[] = $cats[0];
        array_unshift($title, Lang::$npc['cat'][$cats[0]]);
    }

    $npcFilter = new CreatureListFilter();
    if ($_ = $npcFilter->getConditions())
        $conditions[] = $_;

    // beast subtypes are selected via filter
    $npcs = new CreatureList($conditions, ['extraOpts' => $npcFilter->extraOpts]);

    // recreate form selection
    $filter = array_merge($npcFilter->getForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $npcFilter->getForm();

    if (isset($filter['fa']))
        $path[] = $filter['fa'];

    // menuId 4: NPC      g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'petFamPanel' => ($cats && $cats[0] == 1),
            'title'       => implode(' - ', $title),
            'path'        => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'         => 0,
            'subCat'      => $pageParam ? '='.$pageParam : '',
            'reqJS'       => array(
                'static/js/filters.js'
            )
        ),
        'lv' => []
    );

    $lv = array(
        'data'   => $npcs->getListviewData(),           // listview content
        'params' => []
    );

    if (!empty($filter['fi']['extraCols']))
        $lv['params']['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

    // create note if search limit was exceeded
    if ($npcs->getMatches() > CFG_SQL_LIMIT_DEFAULT)
    {
        $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), CFG_SQL_LIMIT_DEFAULT);
        $lv['params']['_truncated'] = 1;
    }

    if ($npcFilter->error)
        $lv['params']['_errors'] = '$1';

    $pageData['lv'] = $lv;

    $smarty->saveCache($cacheKey, $pageData);
}

// sort for dropdown-menus
asort(Lang::$game['fa']);

$smarty->updatePageVars($pageData['page']);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$npc, Lang::$game, ['colon' => lang::$colon]));
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('npcs.tpl');

?>
