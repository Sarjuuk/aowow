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
        // locale independant
        case 'zones':
            if (file_exists('datasets/zones'))
                echo file_get_contents('datasets/zones');
            else
                echo "/* could not fetch multi-level areas */\n\Mapper.multiLevelZones = {};";
            echo "\n\n";
            break;
        case 'weight-presets':
            if (file_exists('datasets/weight-presets'))
                echo file_get_contents('datasets/weight-presets');
            else
                echo "/* could not fetch weight-presets */\n\var wt_presets = {};";
            echo "\n\n";
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
            else
                echo "/* could not fetch ".$data.$params." for locale ".User::$localeString." */";
            echo "\n\n";
            break;
        default:
            break;
    }
}

?>
