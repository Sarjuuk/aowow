<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


header('Content-type: application/x-javascript');

// different data can be strung together

$datasets = array_unique(explode('.', $_GET['data']));
$params   = '';

foreach ($datasets as $data)
{
    switch ($data)
    {
        // Profiler
        case 'factions':
        case 'quests':
        case 'companions':
        case 'recipes':
        case 'mounts':
            if (empty($_GET['callback']) || empty($_GET['t']))
                break;

            $token    = intVal($_GET['t']);
            $callback = $_GET['callback'];
            if (!$token || substr($callback, 0, 17) != '$WowheadProfiler.')
                break;

/*
    get data via token:
    > echo data in unknown format here
    echo '$WowheadProfiler.loadOnDemand('.$data.', <catg?>);';
*/
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
