<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cats       = Util::extractURLParams($pageParam);
$path       = [0, 9];
$title      = [];
$filter     = [];
$filterHash = !empty($_GET['filter']) ? '#'.sha1(serialize($_GET['filter'])) : null;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_ACHIEVEMENT, -1, implode('.', $cats).$filterHash, User::$localeId]);
$validCats  = array(
    92  => true,
    96  => [14861, 14862, 14863],
    97  => [14777, 14778, 14779, 14780],
    95  => [165,   14801, 14802, 14803, 14804, 14881, 14901, 15003],
    168 => [14808, 14805, 14806, 14921, 14922, 14923, 14961, 14962, 15001, 15002, 15041, 15042],
    169 => [170,   171,   172],
    201 => [14864, 14865, 14866],
    155 => [160,   187,   159,   163,   161,   162,   158,   14981, 156,   14941],
    81  => true,
    1   => array (
        130   => [140,   145,   147,   191],
        141   => true,
        128   => [135,   136,   137],
        122   => [123,   124,   125,   126,   127],
        133   => true,
        14807 => [14821, 14822, 14823, 14963, 15021, 15062],
        132   => [178,   173],
        134   => true,
        131   => true,
        21    => [152,   153,   154]
    )
);

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    // include child categories if current category is empty
    $condition = !$cats[0] ? null : (int)end($cats);
    $acvList   = new AchievementList($condition ? [['category', $condition]] : [], true);
    if (!$acvList->getMatches())
    {
        $curCats = $catList = [$condition ? $condition : 0];
        while ($curCats)
        {
            $curCats = DB::Aowow()->SelectCol('SELECT Id FROM ?_achievementCategory WHERE parentCategory IN (?a)', $curCats);
            $catList = array_merge($catList, $curCats);
        }
        $acvList = new AchievementList($catList ? [['category', $catList]] : [], true);
    }

    // recreate form selection
    $filter = array_merge($acvList->filterGetForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $acvList->filterGetForm();

    // create page title and path
    if (is_array($cats))
    {
        $catrows = DB::Aowow()->Select('SELECT * FROM ?_achievementcategory WHERE id IN (?a)',
            $cats
        );

        foreach ($catrows as $cat)
        {
            $path[] = $cat['id'];
            $title[] = Util::localizedString($cat, 'name');
        }
        array_unshift($title, Util::ucFirst(Lang::$game['achievements']));
    }

    // listview content
    $pageData = array(
        'data'   => $acvList->getListviewData(),
        'params' => []
    );

    // fill g_items, g_titles, g_achievements
    $acvList->addGlobalsToJscript($smarty);

    // if we are have different cats display field
    if ($acvList->hasDiffFields(['category']))
        $pageData['params']['visibleCols'] = "$['category']";

    if (!empty($filter['fi']['extraCols']))
        $pageData['params']['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

    // create note if search limit was exceeded
    if ($acvList->getMatches() > $AoWoWconf['sqlLimit'])
    {
        $pageData['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_achievementsfound', $acvList->getMatches(), $AoWoWconf['sqlLimit']);
        $pageData['params']['_truncated'] = 1;
    }

    if ($acvList->filterGetError())
        $pageData['params']['_errors'] = '$1';

    $smarty->saveCache($cacheKey, $pageData, $filter);
}


// sort for dropdown-menus
asort(Lang::$game['si']);

// menuId 9: Achievement g_initPath()
//  tabId 0: Database    g_initHeader()
$smarty->updatePageVars(array(
    'title'  => implode(" - ", $title),
    'path'   => "[".implode(", ", $path)."]",
    'tab'    => 0,
    'subCat' => $pageParam ? '='.$pageParam : '',
    'reqJS'  => array(
        'template/js/filters.js'
    )
));
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$achievement, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('achievements.tpl');

?>
