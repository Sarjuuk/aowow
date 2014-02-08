<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$filter     = [];
$path       = [0, 2];
$filterHash = !empty($_GET['filter']) ? sha1(serialize($_GET['filter'])) : -1;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_ITEMSET, -1, $filterHash, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    $itemsets = new ItemsetList([], true);                  // class selection is via filter, nothing applies here
    $itemsets->addGlobalsToJscript($smarty);

    // recreate form selection
    $filter = array_merge($itemsets->filterGetForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $itemsets->filterGetForm();

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
                'template/js/filters.js',
                '?data=weight-presets'
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
    if ($itemsets->getMatches() > SQL_LIMIT_DEFAULT)
    {
        $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), SQL_LIMIT_DEFAULT);
        $lv['params']['_truncated'] = 1;
    }

    if ($itemsets->filterGetError())
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
