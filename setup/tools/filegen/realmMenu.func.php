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
            ['us', 'US & Oceanic', null,[[Util::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, &$subUS]]],
            ['eu', 'Europe',       null,[[Util::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, &$subEU]]]
        ];

        foreach (Util::getRealms() as $row)
        {
            if ($row['region'] == 'eu')
            {
                $set |= 0x1;
                $subEU[] = [Util::urlize($row['name']), $row['name']];
            }
            else if ($row['region'] == 'us')
            {
                $set |= 0x2;
                $subUS[] = [Util::urlize($row['name']), $row['name']];
            }
        }

        if (!$set)
            CLISetup::log(' - realmMenu: Auth-DB not set up .. menu will be empty', CLISetup::LOG_WARN);

        if (!($set & 0x1))
            array_pop($menu);

        if (!($set & 0x2))
            array_shift($menu);

        return Util::toJSON($menu);
    }

?>
