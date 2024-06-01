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
        $realms = Profiler::getRealms();
        if (!$realms)
            CLI::write(' - realms: Auth-DB not set up .. static data g_realms will be empty', CLI::LOG_WARN);
        // else
            // foreach ($realms as &$r)
                // $r['battlegroup'] = Cfg::get('BATTLEGROUP');

        // remove access column
        array_walk($realms, function (&$x) { unset($x['access']); });

        $toFile = "var g_realms = ".Util::toJSON($realms).";";
        $file   = 'datasets/realms';

        return CLISetup::writeFile($file, $toFile);
    }

?>
