<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // Create 'profile_all.js'-file in static/js;
    // this script requires all realms in use to be defined in auth.realmlist
    // battlegroups has to be set in config file

    /* Example
        var mn_profiles = [
            ["us","US & Oceanic",,[
                ["bloodlust","Bloodlust",,[
                    ["amanthul","Aman'Thul"],
                    ["barthilas","Barthilas"]
                ]],
                ["cyclone","Cyclone",,[
                    ["azjol-nerub","Azjol-Nerub"],
                    ["bloodscalp","Bloodscalp"]
                ]]
            ]],
            ["eu","Europe",,[
                ["blackout","Blackout",,[
                    ["agamaggan","Agamaggan"],
                    ["aggramar","Aggramar"]
                ]],
                ["blutdurst","Blutdurst",,[
                    ["aegwynn","Aegwynn"],
                    ["destromath","Destromath"]
                ]]
            ]]
        ];
    */

    function realmMenu()
    {
        $subEU = [];
        $subUS = [];
        $set   = 0x0;
        $menu  = [
            // skip usage of battlegroup
            // ['us', Lang::profiler('regions', 'us'), null,[[Profiler::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, &$subUS]]],
            // ['eu', Lang::profiler('regions', 'eu'), null,[[Profiler::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, &$subEU]]]
            ['us', Lang::profiler('regions', 'us'), null, &$subUS],
            ['eu', Lang::profiler('regions', 'eu'), null, &$subEU]
        ];

        foreach (Profiler::getRealms() as $row)
        {
            if ($row['region'] == 'eu')
            {
                $set |= 0x1;
                $subEU[] = [Profiler::urlize($row['name']), $row['name']];
            }
            else if ($row['region'] == 'us')
            {
                $set |= 0x2;
                $subUS[] = [Profiler::urlize($row['name']), $row['name']];
            }
        }

        if (!$set)
            CLI::write(' - realmMenu: Auth-DB not set up .. menu will be empty', CLI::LOG_WARN);

        if (!($set & 0x1))
            array_pop($menu);

        if (!($set & 0x2))
            array_shift($menu);

        return Util::toJSON($menu);
    }

?>
