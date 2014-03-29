<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

$filter    = [];
$cat       = Util::extractURLParams($pageParam);            // 0: type; 1:zoneOrSort
$path      = [0, 3];
$title     = [Util::ucFirst(Lang::$game['quests'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_QUEST, -1, $cat ? implode('.', $cat) : -1, User::$localeId]);
$validCats = Util::$questClasses;                           // to be reviewed

if (!Util::isValidPage($validCats, $cat))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    $conditions = [];

    if ($cat)
    {
        // path
        for ($i = 0; $i < count($cat); $i++)
            $path[] = $cat[$i];

        // title

        // cnd
        if (isset($cat[1]))
            $conditions[] = ['zoneOrSort', $cat[1]];
        else if (isset($cat[0]))
            $conditions[] = ['zoneOrSort', $validCats[$cat[0]]];
    }

    $questFilter = new QuestListFilter();

    if ($_ = $questFilter->getConditions())
        $conditions[] = $_;

    $quests = new QuestList($conditions, ['extraOpts' => $questFilter->extraOpts]);

    $quests->addGlobalsToJscript();

    // recreate form selection
    $filter = array_merge($questFilter->getForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $questFilter->getForm();

    $lv = array(
        'file'   => 'quest',
        'data'   => $quests->getListviewData(),
        'params' => []
    );

    if (!empty($filter['fi']['extraCols']))
        $lv['params']['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

    // create note if search limit was exceeded
    if ($quests->getMatches() > CFG_SQL_LIMIT_DEFAULT)
    {
        $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_questsfound', $quests->getMatches(), CFG_SQL_LIMIT_DEFAULT);
        $lv['params']['_truncated'] = 1;
    }
    else if (isset($cat[1]) && $cat[1] > 0)
        $lv['params']['note'] = '$$WH.sprintf(LANG.lvnote_questgivers, '.$cat[1].', g_zones['.$cat[1].'], '.$cat[1].')';

    if ($questFilter->error)
        $lv['params']['_errors'] = '$1';


    // menuId 3: Quest    g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title'  => implode(' - ', $title),
            'path'   => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'    => 0,
            'subCat' => $pageParam ? '='.$pageParam : '',
            'reqJS'  => array(
                'static/js/filters.js'
            )
        ),
        'lv' => $lv
    );

    $smarty->saveCache($cacheKey, $pageData, $filter);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$quest, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('quests.tpl');

?>
