<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // Create 'realms'-file in datasets; update profile_all with real realms
    // this script requires all realms in use to be defined in auth.realmlist
    // battlegroups has to be set in config file

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

    function urlize($str)
    {
        $pairs = [
			"ß" => "ss",
			"á" => "a", "ä" => "a", "à" => "a", "â" => "a",
			"è" => "e", "ê" => "e", "é" => "e", "ë" => "e",
			"í" => "i", "î" => "i", "ì" => "i", "ï" => "i",
			"ñ" => "n",
			"ò" => "o", "ó" => "o", "ö" => "o", "ô" => "o",
			"ú" => "u", "ü" => "u", "û" => "u", "ù" => "u",
			"œ" => "oe",
			"Á" => "A", "Ä" => "A", "À" => "A", "Â" => "A",
			"È" => "E", "Ê" => "E", "É" => "E", "Ë" => "E",
			"Í" => "I", "Î" => "I", "Ì" => "I", "Ï" => "I",
			"Ñ" => "N",
			"Ò" => "O", "Ó" => "O", "Ö" => "O", "Ô" => "O",
			"Ú" => "U", "Ü" => "U", "Û" => "U", "Ù" => "U",
			"œ" => "Oe",
            " " => "-"
		];

        return preg_replace('/[^\d\w\-]/', '', strtr(strToLower($str), $pairs));
    }

    if (!file_exists('config\\profile_all.js.in'))
        die('profile_all source file is missing; cannot create realm file');

    $menu = [
        ["us","US & Oceanic", null,[
            [urlize($AoWoWconf['battlegroup']),$AoWoWconf['battlegroup'],null,[]]
        ]],
        ["eu","Europe", null,[
            [urlize($AoWoWconf['battlegroup']),$AoWoWconf['battlegroup'],null,[]]
        ]]
    ];

    $rows = DB::Auth()->select('SELECT id AS ARRAY_KEY, name, ?s AS battlegroup, IF(timezone IN (8, 9, 10, 11, 12), "eu", "us") AS region FROM realmlist WHERE allowedSecurityLevel = 0', $AoWoWconf['battlegroup']);
    $str  = 'var g_realms = '.json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).';';

    $handle = fOpen('datasets\\realms', "w");
    fWrite($handle, $str);
    fClose($handle);

    $set = 0x0;
    foreach ($rows as $row)
    {
        if ($row['region'] == 'eu')
        {
            $set |= 0x1;
            $menu[1][3][0][3][] = [urlize($row['name']),$row['name']];
        }
        else if ($row['region'] == 'us')
        {
            $set |= 0x2;
            $menu[0][3][0][3][] = [urlize($row['name']),$row['name']];
        }
    }

    if (!($set & 0x1))
        array_pop($menu);

    if (!($set & 0x2))
        array_shift($menu);

    $file = file_get_contents('config\\profile_all.js.in');
    $dest = fOpen('template\\js\\profile_all.js', "w");

    fWrite($dest, str_replace('[/*setup:realms*/]', json_encode($menu, JSON_UNESCAPED_UNICODE), $file));
    fClose($dest);

    echo 'all done';
?>
