<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cat       = Util::extractURLParams($pageParam)[0];
$path      = [0, 14];
$title     = [Util::ucFirst(Lang::$game['skills'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_SKILL, -1, $cat ? $cat : -1, User::$localeId]);
$validCats = [-6, -5, -4, 6, 8, 9, 10, 11];

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $conditions = [['categoryId', 12, '!']];                // DND
    if ($cat)
    {
        $conditions[] = ['typeCat', $cat];
        $path[]       = $cat;
        array_unshift($title, Lang::$skill['cat'][$cat]);
    }

    $skills = new SkillList($conditions);

    $pageData = array(
        'title'     => $title,
        'path'      => $path,
        'listviews' => array(
            array(
                'file'   => 'skill',
                'data'   => $skills->getListviewData(),
                'params' => []
            )
        )
    );

    $smarty->saveCache($cacheKey, $pageData);
}


// menuId 14: Skill    g_initPath()
//  tabId  0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => implode(' - ', $title),
    'path'   => json_encode($path, JSON_NUMERIC_CHECK),
    'tab'    => 0
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('generic-no-filter.tpl');

?>
