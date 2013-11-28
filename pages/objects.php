<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$path      = [0, 5];
$validCats = [-2, -3, -4, -5, 3, 9];
$title     = [Util::ucFirst(Lang::$game['gameObjects'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_OBJECT, -1, isset($cat) ? $cat : -1, User::$localeId]);

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if (isset($cat))
{
    $path[] = $cat;                                         // should be only one parameter anyway
    // array_unshift($title, Lang::$object['cat'][$cat]);
}

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $pageData = array(
        'listviews' => []
    );

    $conditions = [];

    if ($cat == -3)
    {
        $conditions[] = ['type', 3];
        $conditions[] = ['l.properties1', LOCK_PROPERTY_HERBALISM];
    }
    else if ($cat == -4)
    {
        $conditions[] = ['type', 3];
        $conditions[] = ['l.properties1', LOCK_PROPERTY_MINING];
    }
    else if ($cat == -5)
    {
        $conditions[] = ['type', 3];
        $conditions[] = ['l.properties2', LOCK_PROPERTY_FOOTLOCKER];
    }
    else
        $conditions[] = ['type', (int)$cat];       // quest not supported

    $objects = new GameObjectList($conditions);

    $params = [];
    if ($objects->hasSetFields(['reqSkill']))
        $params['visibleCols'] = "$['skill']";

    $pageData['listviews'][] = array(
        'file'   => 'object',
        'data'   => $objects->getListviewData(),
        'params' => $params
    );



    $objects->addGlobalsToJscript($smarty);

    $smarty->saveCache($cacheKey, $pageData);
}

// menuId 5: Object   g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'tab'   => 0,
    'title' => implode(" - ", $title),
    'path'  => "[".implode(", ", $path)."]"
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
// $smarty->display('objects.tpl');
$smarty->display('generic-no-filter.tpl');

?>
