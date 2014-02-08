<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// require 'includes/community.class.php';

$_path   = [1, 8];
$subMenu = $h1Links = null;
$_title  = '';
$_rss    = isset($_GET['rss']);

switch ($pageCall)
{
    case 'random':
        $type   = array_rand(array_filter(Util::$typeStrings));
        $page   = Util::$typeStrings[$type];
        $id     = 0;

        switch ($type)
        {
            case TYPE_NPC:          $id = (new CreatureList(null))->getRandomId();      break;
            case TYPE_OBJECT:       $id = (new GameobjectList(null))->getRandomId();    break;
            case TYPE_ITEM:         $id = (new ItemList(null))->getRandomId();          break;
            case TYPE_ITEMSET:      $id = (new ItemsetList(null))->getRandomId();       break;
            case TYPE_QUEST:        $id = (new QuestList(null))->getRandomId();         break;
            case TYPE_SPELL:        $id = (new SpellList(null))->getRandomId();         break;
            case TYPE_ZONE:         $id = (new ZoneList(null))->getRandomId();          break;
            case TYPE_FACTION:      $id = (new FactionList(null))->getRandomId();       break;
            case TYPE_PET:          $id = (new PetList(null))->getRandomId();           break;
            case TYPE_ACHIEVEMENT:  $id = (new AchievementList(null))->getRandomId();   break;
            case TYPE_TITLE:        $id = (new TitleList(null))->getRandomId();         break;
            case TYPE_WORLDEVENT:   $id = (new WorldEventList(null))->getRandomId();    break;
            case TYPE_CLASS:        $id = (new CharClassList(null))->getRandomId();     break;
            case TYPE_RACE:         $id = (new CharRaceList(null))->getRandomId();      break;
            case TYPE_SKILL:        $id = (new SkillList(null))->getRandomId();         break;
            case TYPE_CURRENCY:     $id = (new CurrencyList(null))->getRandomId();      break;
        }

        header('Location: ?'.$page.'='.$id);
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
        $lv   = array(
            array(
                'file'   => 'screenshot',
                'data'   => [],
                'params' => []
            )
        );
        break;
    case 'latest-videos':
        $menu = 11;
        $lv   = array(
            array(
                'file'   => 'video',
                'data'   => [],
                'params' => []
            )
        );
        break;
    case 'latest-articles':
        $menu = 1;
        $lv   = [];
        break;
    case 'latest-additions':
        $menu = 0;
        $lv   = [];
        break;
    case 'unrated-comments':
        $menu = 5;
        $lv   = [];
        break;
    case 'missing-screenshots':
        $menu = 13;
        $lv   = [];
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
        $lv   = [];
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
            "\t\t<title>".$AoWoWconf['shortName'].' - '.Lang::$main['utilities'][$menu] . ($_title ? Lang::$colon . $_title : null)."</title>\n".
            "\t\t<link>".STATIC_URL.'?'.$pageCall . ($pageParam ? '='.$pageParam : null)."</link>\n".
            "\t\t<description>".$AoWoWconf['name']."</description>\n".
            "\t\t<language>".implode('-', str_split(User::$localeString, 2))."</language>\n".
            "\t\t<ttl>".$AoWoWconf['ttl']."</ttl>\n".
            // <lastBuildDate>Sat, 31 Aug 2013 15:33:16 -0500</lastBuildDate>
            "\t</channel>\n";

    /*
        generate <item>'s here
    */

        $xml .= '</rss>';

        die($xml);
    }
    else
        $h1Links = '<small><a href="?'.$pageCall.($pageParam ? '='.$pageParam : null).'&rss" class="rss-icon">'.Lang::$main['subscribe'].'</a></small>';
}

$pageData = array(
    'listviews' => $lv,
    'page' => array(
        'name'    => Lang::$main['utilities'][$menu] . ($_title ? Lang::$colon . $_title : null),
        'h1Links' => $h1Links,
    )
);

array_push($_path, $menu);
if ($subMenu)
    array_push($_path, $subMenu);


// menuId  8: Utilities g_initPath()
//  tabId  1: Tools     g_initHeader()
$smarty->updatePageVars(array(
    'title' => Lang::$main['utilities'][$menu] . ($_title ? ' - ' . $_title : null),
    'path'  => json_encode($_path, JSON_NUMERIC_CHECK),
    'tab'   => 1
));
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('generic-no-filter.tpl');

?>
