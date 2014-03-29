<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$filter     = [];
$conditions = [];
$cat        = Util::extractURLParams($pageParam);
$path       = [0, 5];
$validCats  = [-2, -3, -4, -5, -6, 0, 3, 9, 25];
$title      = [Util::ucFirst(Lang::$game['gameObjects'])];
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_OBJECT, -1, $cat ? $cat[0] : -1, User::$localeId]);

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if ($cat)
{
    $path[] = $cat[0];
    array_unshift($title, Lang::$gameObject['cat'][$cat[0]]);
    $conditions[] = ['typeCat', (int)$cat[0]];
}

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{

    $objectFilter = new GameObjectListFilter();
    if ($_ = $objectFilter->getConditions())
        $conditions[] = $_;

    $objects = new GameObjectList($conditions, ['extraOpts' => $objectFilter->extraOpts]);

    // menuId 5: Object   g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'tab'    => 0,
            'title'  => implode(" - ", $title),
            'path'   => json_encode($path, JSON_NUMERIC_CHECK),
            'subCat' => $pageParam ? '='.$pageParam : '',
            'reqJS'  => ['static/js/filters.js']
        ),
        'lv' => []
    );

    // recreate form selection
    $filter = array_merge($objectFilter->getForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $objectFilter->getForm();

    $params = [];
    if ($objects->hasSetFields(['reqSkill']))
        $params['visibleCols'] = "$['skill']";

    $lv = array(
        'file'   => 'object',
        'data'   => $objects->getListviewData(),
        'params' => $params
    );

    // create note if search limit was exceeded
    if ($objects->getMatches() > CFG_SQL_LIMIT_DEFAULT)
    {
        $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_objectsfound', $objects->getMatches(), CFG_SQL_LIMIT_DEFAULT);
        $lv['params']['_truncated'] = 1;
    }

    if ($objectFilter->error)
        $lv['params']['_errors'] = '$1';

    $pageData['lv'] = $lv;

    $smarty->saveCache($cacheKey, $pageData, $filter);
}

$smarty->updatePageVars($pageData['page']);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('objects.tpl');

?>
