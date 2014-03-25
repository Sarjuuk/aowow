<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// requires valid token to hinder automated access
// todo (low): research, when to use the token
if ($_GET['data'] != 'item-scaling')
{
    if (empty($_GET['t']) || empty($_SESSION['dataKey']))
        die();

    if ($_GET['t'] != $_SESSION['dataKey'])
        die();
}


header('Content-type: application/x-javascript');

// different data can be strung together
$datasets = array_unique(explode('.', $_GET['data']));
$params   = '';


// great, we can set our locale .. just .. what for..?
if (isset($_GET['locale']) && is_numeric($_GET['locale']))
    User::useLocale($_GET['locale']);

foreach ($datasets as $data)
{
    switch ($data)
    {
        // Profiler (this .. _COULD_ be static . it's basicly just "ALL" available data of one type)
        case 'factions':
        case 'quests':
        case 'companions':
        case 'recipes':
        case 'mounts':
            if (empty($_GET['callback']))
                break;

            $catg     = 'null';                                 // hm, looks like its just for preselection..
            $skill    = [];
            $callback = $_GET['callback'];

            if (!empty($_GET['skill']))
            {
                $skill = explode(',', $_GET['skill']);
                array_walk($skill, function(&$v, $k) {
                    $v = intVal($v);
                });
            }

            if (substr($callback, 0, 17) != '$WowheadProfiler.')
                break;

            if ($data == 'factions')
            {
                $cnd  = null;
                $obj  = 'FactionList';
                $glob = 'g_factions';

                echo "g_faction_order = [0, 469, 891, 1037, 1118, 67, 1052, 892, 936, 1117, 169, 980, 1097];\n\n";
            }
            else if ($data == 'quests')
            {
                // may have &partial set .. what to do .. what to do..

                $cnd  = null;
                $obj  = 'QuestList';
                $glob = 'g_quests';

                echo "g_quest_catorder = [];\n\n";
            }
            else if ($data == 'companions')
            {
                $cnd  = ['typeCat', -6];
                $obj  = 'SpellList';
                $glob = 'g_spells';
                $catg = 778;
            }
            else if ($data == 'recipes')
            {
                $cnd = ['OR', ['typeCat', 9], ['typeCat', 11]];
                if ($skill)
                    $cnd = ['AND', ['skillLine1', $skill], $cnd];

                $obj  = 'SpellList';
                $glob = 'g_spells';
                // $catg = 185;

                echo "g_skill_order = [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356, 762];\n\n";
            }
            else if ($data == 'mounts')
            {
                $cnd  = ['typeCat', -5];
                $obj  = 'SpellList';
                $glob = 'g_spells';
                $catg = 777;
            }

            $cnd   = [[['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW], 0], $cnd, CFG_SQL_LIMIT_NONE];
            $list  = new $obj($cnd);
            $dataz = $list->getListviewData(ITEMINFO_MODEL);
            foreach ($dataz as $i => $d)
            {
                if (isset($d['quality']))                       // whoever thought of prepending quality to the name may burn in hell!
                {
                    $d['name'] = $d['quality'].$d['name'];
                    unset($d['quality']);
                }

                echo $glob.'['.$i.'] = '.json_encode($d, JSON_NUMERIC_CHECK).";\n";
            }

            if ($data == 'recipes')                             // todo: skip adding reagents
                foreach ($list->relItems->iterate() as $iId => $tpl)
                    echo "g_items.add(".$iId.", {'icon':'".$tpl['iconString']."'});\n";

            /*  issue:
                when we load onDemand, the jScript tries to generate the catg-tree before the it is initialized
                it cant be initialized, without loading the data as empty catg are omitted
                loading the data triggers the generation of the catg-tree

                obviously only, if we have no initial data set

                yay .. either way, we loose
            */

            echo "\n\$WowheadProfiler.loadOnDemand('".$data."', ".$catg.");\n";

            break;
        // locale independant
        case 'zones':
        case 'weight-presets':
        case 'item-scaling':
        case 'realms':
        case 'statistics':
            if (file_exists('datasets/'.$data))
                echo file_get_contents('datasets/'.$data);
            else if (CFG_DEBUG)
                echo "alert('could not fetch static data: ".$data."');";
            echo "\n\n";
            break;
        case 'user':
            // todo (high): structure probably lost; probably sent basic char stats
            // g_user = { id: 0, name: '', roles: 0, permissions: 0, ads: true, cookies: {} };
            break;
        // localized
        case 'talents':
            if (isset($_GET['class']))
                $params .= "-".intVal($_GET['class']);
        case 'pet-talents':
        case 'glyphs':
        case 'gems':
        case 'enchants':
        case 'itemsets':
        case 'pets':
            if (file_exists('datasets/'.User::$localeString.'/'.$data.$params))
                echo file_get_contents('datasets/'.User::$localeString.'/'.$data.$params);
            else if (file_exists('datasets/enus/'.$data.$params))
                echo file_get_contents('datasets/enus/'.$data.$params);
            else if (file_exists('datasets/'.$data.$params))
                echo file_get_contents('datasets/'.$data.$params);
            else if (CFG_DEBUG)
                echo "alert('could not fetch static data: ".$data.$params." for locale: ".User::$localeString."');";
            echo "\n\n";
            break;
        case 'quick-excludes':  // generated per character in profiler
        default:
            break;
    }
}

?>
