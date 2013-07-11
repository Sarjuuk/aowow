<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.filter.php';

$filter     = [];
$path       = [0, 2];
$filterHash = !empty($_GET['filter']) ? sha1(serialize($_GET['filter'])) : -1;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_ITEMSET, -1, $filterHash, User::$localeId]);

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $itemsets = new ItemsetList([], true);                  // class selection is via filter, nothing applies here

    $pageData = array(
        'data'   => $itemsets->getListviewData(),           // listview content
        'params' => []
    );

    $itemsets->addGlobalsToJscript($smarty);

    // recreate form selection
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['setCr'] = $itemsets->filterGetSetCriteria();
    $filter = array_merge($itemsets->filterGetForm(), $filter);

    if (isset($filter['cl']))
        $path[] = $filter['cl'];

    // create note if search limit was exceeded
    if ($itemsets->getMatches() > $AoWoWconf['sqlLimit'])
    {
        $pageData['params']['note'] = '$'.sprintf(Util::$filterResultString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), $AoWoWconf['sqlLimit']);
        $pageData['params']['_truncated'] = 1;
    }

    if ($itemsets->filterGetError())
        $pageData['params']['_errors'] = '$1';

    $smarty->saveCache($cacheKey, $pageData);
}


$page = array(
    'tab'    => 0,                                          // for g_initHeader($tab)
    'subCat' => $pageParam ? '='.$pageParam : '',
    'title'  => Util::ucFirst(Lang::$game['itemsets']),
    'path'   => json_encode($path, JSON_NUMERIC_CHECK),
    'reqJS'  => array(
        array('path' => 'template/js/filters.js', 'conditional' => false),
        array('path' => '?data=weight-presets',   'conditional' => false),
   )
);

// sort for dropdown-menus
asort(Lang::$itemset['notes'], SORT_NATURAL);
asort(Lang::$game['cl']);

$smarty->updatePageVars($page);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$itemset, Lang::$item));
$smarty->assign('lvData', $pageData);
$smarty->display('itemsets.tpl');

?>
