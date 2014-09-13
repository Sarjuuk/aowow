<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


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

function mnProfiles(/* &$log */)
{
    $menu = [
        ["us", "US & Oceanic", null,[
            [Util::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, []]
        ]],
        ["eu", "Europe", null,[
            [Util::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP, null, []]
        ]]
    ];

    $rows = DB::Auth()->select('SELECT name, IF(timezone IN (8, 9, 10, 11, 12), "eu", "us") AS region FROM realmlist WHERE allowedSecurityLevel = 0');
    $set  = 0x0;

    foreach ($rows as $row)
    {
        if ($row['region'] == 'eu')
        {
            $set |= 0x1;
            $menu[1][3][0][3][] = [Util::urlize($row['name']),$row['name']];
        }
        else if ($row['region'] == 'us')
        {
            $set |= 0x2;
            $menu[0][3][0][3][] = [Util::urlize($row['name']),$row['name']];
        }
    }

    if (!($set & 0x1))
        array_pop($menu);

    if (!($set & 0x2))
        array_shift($menu);

    return json_encode($menu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

?>
