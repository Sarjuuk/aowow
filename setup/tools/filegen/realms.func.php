<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


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

    function realms()
    {
        $realms = [];
        if (DB::isConnectable(DB_AUTH))
            $realms = DB::Auth()->select('SELECT id AS ARRAY_KEY, name, ? AS battlegroup, IF(timezone IN (8, 9, 10, 11, 12), "eu", "us") AS region FROM realmlist WHERE allowedSecurityLevel = 0', CFG_BATTLEGROUP);
        else
            CLISetup::log(' - realms: Auth-DB not set up .. static data g_realms will be empty', CLISetup::LOG_WARN);

        $toFile = "var g_realms = ".Util::toJSON($realms).";";
        $file   = 'datasets/realms';

        return CLISetup::writeFile($file, $toFile);
    }

?>
