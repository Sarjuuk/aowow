<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // Create 'realms'-file in datasets
    // this script requires all realms in use to be defined in auth.realmlist
    // battlegroups has to be set in aowow.aowow_config

    /*  seems to be unused, was located in the same file as g_realms
        var g_regions = {
            us => 'www.wowarmory.com',
            eu => 'eu.wowarmory.com'
        };
    */

    /* Examples
        1 => {
            name:'Eldre\'Thalas',
            battlegroup:'Reckoning',
            region:'us'
        },
    */

    function realms(&$log)
    {
        $file = 'datasets/realms';
        $rows = DB::Auth()->select('SELECT id AS ARRAY_KEY, name, ? AS battlegroup, IF(timezone IN (8, 9, 10, 11, 12), "eu", "us") AS region FROM realmlist WHERE allowedSecurityLevel = 0', CFG_BATTLEGROUP);
        $str  = 'var g_realms = '.json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).';';

        return writeFile($file, $str, $log);
    }

?>
