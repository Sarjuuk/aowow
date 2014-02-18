<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam);
$path      = [0, 14];
$title     = [Util::ucFirst(Lang::$game['skills'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_SKILL, -1, $cat ? $cat[0] : -1, User::$localeId]);
$validCats = [-6, -5, -4, 6, 7, 8, 9, 10, 11];

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $conditions = [['categoryId', 12, '!']];                // DND
    if ($cat)
    {
        $conditions[] = ['typeCat', $cat[0]];
        $path[]       = $cat[0];
        array_unshift($title, Lang::$skill['cat'][$cat[0]]);
    }

    $skills = new SkillList($conditions);

    // menuId 14: Skill    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title' => implode(' - ', $title),
            'path'  => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'   => 0
        ),
        'lv' => array(
            array(
                'file'   => 'skill',
                'data'   => $skills->getListviewData(),
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
