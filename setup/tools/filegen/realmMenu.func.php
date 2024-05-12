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
        $subs = [];
        $set  = 0x0;
        $menu = [
            // skip usage of battlegroup
            // ['us', Lang::profiler('regions', 'us'), null,[[Profiler::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, &$subUS]]],
            // ['eu', Lang::profiler('regions', 'eu'), null,[[Profiler::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, &$subEU]]]
        ];

        foreach (Util::$regions as $idx => $n)
            $subs[$idx] = [];

        foreach (Profiler::getRealms() as $row)
        {
            $idx = array_search($row['region'], Util::$regions);
            if ($idx !== false)
            {
                $set |= (1 << $idx);
                $subs[$idx][] = [Profiler::urlize($row['name'], true), $row['name'], null, null, $row['access'] ? ['requiredAccess' => $row['access']] : null];
            }

        }
        if (!$set)
            CLI::write(' - realmMenu: Auth-DB not set up .. realm menu will be empty', CLI::LOG_WARN);

        // why is this file not localized!?
        foreach (Util::$regions as $idx => $n)
            if ($set & (1 << $idx))
                $menu[] = [$n, Lang::profiler('regions', $n), null, &$subs[$idx]];

        return Util::toJSON($menu);
    }
?>
