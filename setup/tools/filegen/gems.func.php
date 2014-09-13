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

    function gems(&$log, $locales)
    {
        // sketchy, but should work
        // Id < 36'000 || ilevel < 70 ? BC : WOTLK
        $gems   = DB::Aowow()->Select(
           'SELECT    it.entry AS itemId,
                      it.name,
                      li.*,
                      IF (it.entry < 36000 OR it.ItemLevel < 70, 1 , 2) AS expansion,
                      it.Quality AS quality,
                      i.inventoryicon1 AS icon,
                      gp.spellItemEnchantmentId AS enchId,
                      gp.colorMask AS colors
            FROM      item_template it
            LEFT JOIN locales_item li ON li.entry = it.entry
            JOIN      dbc.gemproperties gp ON gp.Id = it.GemProperties
            JOIN      dbc.itemdisplayinfo i ON i.Id = it.displayid
            WHERE     it.GemProperties <> 0
            ORDER BY  it.entry DESC');
        $success = true;


        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!writeDir('datasets/'.$dir, $log))
                $success = false;

        $enchIds = [];
        foreach ($gems as $pop)
            $enchIds[] = $pop['enchId'];

        $enchMisc = [];
        $enchJSON = Util::parseItemEnchantment($enchIds, false, $enchMisc);

        foreach ($locales as $lId)
        {
            set_time_limit(5);

            User::useLocale($lId);
            Lang::load(Util::$localeStrings[$lId]);

            $gemsOut = [];
            foreach ($gems as $pop)
            {
                $gemsOut[$pop['itemId']] = array(
                    'name'        => Util::localizedString($pop, 'name'),
                    'quality'     => $pop['quality'],
                    'icon'        => strToLower($pop['icon']),
                    'enchantment' => Util::localizedString(@$enchMisc[$pop['enchId']]['text'] ?: [], 'text'),
                    'jsonequip'   => @$enchJSON[$pop['enchId']] ?: [],
                    'colors'      => $pop['colors'],
                    'expansion'   => $pop['expansion']
                );
            }

            $toFile  = "var g_gems = ";
            $toFile .= json_encode($gemsOut, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            $toFile .= ";";
            $file    = 'datasets/'.User::$localeString.'/gems';

            if (!writeFile($file, $toFile, $log))
                $success = false;
        }

        return $success;
    }

?>
