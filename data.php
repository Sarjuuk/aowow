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
        case 'weight-presets':
        case 'item-scaling':
        case 'realms':
            if (file_exists('datasets/'.$data))
                echo file_get_contents('datasets/'.$data);
            else if ($AoWoWconf['debug'])
                echo "/* could not fetch static data: ".$data." */";
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
            else if ($AoWoWconf['debug'])
                echo "alert('could not fetch static data: ".$data.$params." for locale: ".User::$localeString."');";
            echo "\n\n";
            break;
        default:
            break;
    }
}

?>
