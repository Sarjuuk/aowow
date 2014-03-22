<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

$type        =  0;                                          // "type, the unclean" (jupp, this var is dancing (in a pile of crap and goo))
$typeId      = -1;
$_path       = [2];
$title       = '';                                          // not translated except for help
$validParams = array(
    "commenting-and-you",
    "modelviewer",
    "screenshots-tips-tricks",
    "stat-weighting",
    "talent-calculator",
    "item-comparison",
    "profiler"
);

switch($pageCall)
{
    case 'help':
        $type = -13;
        if (isset($validParams[$pageParam]))
            header('Location: ?help='.$validParams[$pageParam]);

        if (($typeId = array_search($pageParam, $validParams)) === false)
            Util::$pageTemplate->error();

        $title = Lang::$main['helpTopics'][$typeId];
        break;
    case 'tooltips':
        $type   = -10;
        $typeId =   0;
        $title  = 'Tooltips';
        break;
    case 'faq':
        $type   = -3;
        $typeId =  0;
        $title  = 'Frequently Asked Questions';
        break;
    case 'aboutus':
        $type   = 0;
        $typeId = 0;
        $title  = 'What is AoWoW?';
        break;
    case 'searchplugins':
        $type   = -8;
        $typeId =  0;
        $title  = 'Search Plugins';
        break;
    case 'searchbox':
        $type   = -16;
        $typeId =   0;
        $title  = 'Search Box';
        break;
     case 'whats-new':
        $type   = -7;
        $typeId =  0;
        $title  = 'What\'s New';
        break;
    default:
        Util::$pageTemplate->error();
}

$_path[] = abs($type);

if ($typeId > -1)
    $_path[] = $typeId;

// the actual text is an article accessed by type + typeId
// menuId 2: More g_initPath()
//  tabid 2: More g_initHeader()
$pageData = array(
    'name'   => $title,
    'title'  => $title,
    'path'   => json_encode($_path, JSON_NUMERIC_CHECK),
    'tab'    => 2,
    'type'   => $type,
    'typeId' => $typeId
);

$smarty->updatePageVars($pageData);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game));

// load the page
$smarty->display('text-page-generic.tpl');

?>
