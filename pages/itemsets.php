<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$filter     = [];
$path       = [0, 2];
$filterHash = !empty($_GET['filter']) ? sha1(serialize($_GET['filter'])) : -1;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_ITEMSET, -1, $filterHash, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    $itemsetFilter = new ItemsetListFilter();

    $itemsets = new ItemsetList([$itemsetFilter->getConditions()]);
    $itemsets->addGlobalsToJscript();

    // recreate form selection
    $filter = array_merge($itemsetFilter->getForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $itemsetFilter->getForm();

    if (isset($filter['cl']))
        $path[] = $filter['cl'];

    // menuId 2: Itemset  g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title'  => Util::ucFirst(Lang::$game['itemsets']),
            'path'   => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'    => 0,
            'subCat' => $pageParam ? '='.$pageParam : '',
            'reqJS'  => array(
                STATIC_URL.'/js/filters.js',
                '?data=weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']
            )
        ),
        'lv' => []
    );

    $lv = array(
        'data'   => $itemsets->getListviewData(),           // listview content
        'params' => []
    );

    if (!empty($filter['fi']['extraCols']))
        $lv['params']['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

    // create note if search limit was exceeded
    if ($itemsets->getMatches() > CFG_SQL_LIMIT_DEFAULT)
    {
        $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), CFG_SQL_LIMIT_DEFAULT);
        $lv['params']['_truncated'] = 1;
    }

    if ($itemsetFilter->error)
        $lv['params']['_errors'] = '$1';

    $pageData['lv'] = $lv;

    $smarty->saveCache($cacheKey, $pageData, $filter);
}


// sort for dropdown-menus
asort(Lang::$itemset['notes'], SORT_NATURAL);
asort(Lang::$game['cl']);

$smarty->updatePageVars($pageData['page']);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$itemset, Lang::$item, ['colon' => lang::$colon]));
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('itemsets.tpl');

?>
