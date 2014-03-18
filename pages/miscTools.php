<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// require 'includes/community.class.php';

$_path   = [1, 8];
$subMenu = $h1Links = null;
$_title  = '';
$_rss    = isset($_GET['rss']);
$lv      = [];

switch ($pageCall)
{
    case 'random':
        $type   = array_rand(array_filter(Util::$typeStrings));
        $typeId = 0;

        if ($type != TYPE_QUEST)
            $typeId = (new Util::$typeClasses[$type](null))->getRandomId();

        header('Location: ?'.Util::$typeStrings[$type].'='.$typeId);
        die();
	case 'latest-comments':
        $menu = 2;
        $lv   = array(
            array(
                'file'   => 'commentpreview',
                'data'   => [],
                'params' => []
            )
        );
		break;
	case 'latest-screenshots':
        $menu = 3;
        $lv[] = array(
            'file'   => 'screenshot',
            'data'   => [],
            'params' => []
        );
        break;
    case 'latest-videos':
        $menu = 11;
        $lv[] = array(
            'file'   => 'video',
            'data'   => [],
            'params' => []
        );
        break;
    case 'latest-articles':
        $menu = 1;
        $lv   = [];
        break;
    case 'latest-additions':
        $menu = 0;
        $extraText = '';
        break;
    case 'unrated-comments':
        $menu = 5;
        $lv[] = array(
            'file'   => 'commentpreview',
            'data'   => [],
            'params' => []
        );
        break;
    case 'missing-screenshots':
        $menu = 13;
        $cnd  = [[['cuFlags', CUSTOM_HAS_SCREENSHOT, '&'], 0]];

        if (!User::isInGroup(U_GROUP_STAFF))
            $cnd[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        foreach (Util::$typeClasses as $classStr)
        {
            // temp: as long as we use world.quest_template
            if ($classStr == 'QuestList')
                continue;

            $typeObj = new $classStr($cnd);

            if (!$typeObj->error)
            {
                $typeObj->addGlobalsToJscript(Util::$pageTemplate, GLOBALINFO_SELF | GLOBALINFO_RELATED);

                $lv[] = array(
                    'file'   => (new ReflectionProperty($typeObj, 'brickFile'))->getValue(),
                    'data'   => $typeObj->getListviewData(),
                    'params' => ['tabs' => '$myTabs']
                );
            }
        }
        break;
    case 'most-comments':
        if ($pageParam && !in_array($pageParam, [1, 7, 30]))
            header('Location: ?most-comments=1'.($_rss ? '&rss' : null));

        if (in_array($pageParam, [7, 30]))
        {
            $subMenu = $pageParam;
            $_title  = sprintf(Lang::$main['mostComments'][1], $pageParam);
        }
        else
        {
            $subMenu = 1;
            $_title  = Lang::$main['mostComments'][0];
        }

        $menu = 12;
        $lv[] = array(
            'file'   => 'commentpreview',
            'data'   => [],
            'params' => []
        );
        break;
    default:
		$smarty->error();
}

if (strstr($pageCall, 'latest') || $pageCall == 'most-comments')
{
    if ($_rss)
    {
        header("Content-Type: application/rss+xml; charset=ISO-8859-1");

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
            "<rss version=\"2.0\">\n\t<channel>\n".
            "\t\t<title>".CFG_NAME_SHORT.' - '.Lang::$main['utilities'][$menu] . ($_title ? Lang::$colon . $_title : null)."</title>\n".
            "\t\t<link>".STATIC_URL.'?'.$pageCall . ($pageParam ? '='.$pageParam : null)."</link>\n".
            "\t\t<description>".CFG_NAME."</description>\n".
            "\t\t<language>".implode('-', str_split(User::$localeString, 2))."</language>\n".
            "\t\t<ttl>".CFG_TTL_RSS."</ttl>\n".
            // <lastBuildDate>Sat, 31 Aug 2013 15:33:16 -0500</lastBuildDate>
            "\t</channel>\n";

    /*
        generate <item>'s here
    */

        $xml .= '</rss>';

        die($xml);
    }
    else
        $h1Links = '<small><a href="?'.$pageCall.($pageParam ? '='.$pageParam : null).'&rss" class="icon-rss">'.Lang::$main['subscribe'].'</a></small>';
}

array_push($_path, $menu);
if ($subMenu)
    array_push($_path, $subMenu);


// menuId  8: Utilities g_initPath()
//  tabId  1: Tools     g_initHeader()
$smarty->updatePageVars(array(
    'name'    => Lang::$main['utilities'][$menu] . ($_title ? Lang::$colon . $_title : null),
    'h1Links' => $h1Links,
    'title'   => Lang::$main['utilities'][$menu] . ($_title ? ' - ' . $_title : null),
    'path'    => json_encode($_path, JSON_NUMERIC_CHECK),
    'tab'     => 1
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $lv);

// load the page
$smarty->display('list-page-generic.tpl');

?>
