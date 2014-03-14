<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // Create 'gems'-file for available locales
    // this script requires the following dbc-files to be parsed and available
    // ItemEnchantment, GemProperties, Spells, Icons

    /* Example
        22460: {
            name:'Prismatic Sphere',
            quality:3,
            icon:'INV_Enchant_PrismaticSphere',
            enchantment:'+3 Resist All',
            jsonequip:{"arcres":3,"avgbuyout":242980,"firres":3,"frores":3,"holres":3,"natres":3,"shares":3},
            colors:14,
            expansion:1
            gearscore:8  // as if.....
        },
    */

    // sketchy, but should work
    // Id < 36'000 || ilevel < 70 ? BC : WOTLK

    $gemQuery = "
        SELECT
            it.entry as itemId,
            it.name,
            li.*,
            IF (it.entry < 36000 OR it.ItemLevel < 70, 1 , 2) AS expansion,
            (it.Quality) AS quality,
            i.inventoryicon1 as icon,
            ie.*,
            gp.colorMask as colors
        FROM
            item_template it
        LEFT JOIN
            locales_item li ON li.entry = it.entry
        JOIN
            dbc.gemProperties gp ON gp.Id = it.GemProperties
        JOIN
            ?_itemEnchantment ie ON gp.spellItemEnchantmentId = ie.Id
        JOIN
            dbc.itemdisplayinfo i ON i.Id = it.displayid
        WHERE
            it.GemProperties <> 0
        ORDER BY
            it.entry DESC
        ;
    ";

    $gems     = Db::Aowow()->Select($gemQuery);
    $locales  = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    $jsonGems = [];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);

        $gemsOut = [];
        foreach ($gems as $pop)
        {
            // costy and locale-independant -> cache
            if (!isset($jsonGems[$pop['itemId']]))
                $jsonGems[$pop['itemId']] = Util::parseItemEnchantment($pop);

            $gemsOut[$pop['itemId']] = array(
                'name'        => Util::localizedString($pop, 'name'),
                'quality'     => $pop['quality'],
                'icon'        => strToLower($pop['icon']),
                'enchantment' => Util::localizedString($pop, 'text'),
                'jsonequip'   => $jsonGems[$pop['itemId']],
                'colors'      => $pop['colors'],
                'expansion'   => $pop['expansion']
            );
        }

        $toFile  = "var g_gems = ";
        $toFile .= json_encode($gemsOut, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        $toFile .= ";";
        $file    = 'datasets\\'.User::$localeString.'\\gems';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done gems loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    User::useLocale(LOCALE_EN);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);

?>
