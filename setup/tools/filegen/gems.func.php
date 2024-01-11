<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


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
            gearscore:8  // if only..
        },
    */

    function gems()
    {
        // sketchy, but should work
        // id < 36'000 || ilevel < 70 ? BC : WOTLK
        $gems   = DB::Aowow()->Select(
           'SELECT    i.id AS itemId,
                      i.name_loc0, i.name_loc2, i.name_loc3, i.name_loc4, i.name_loc6, i.name_loc8,
                      IF (i.id < 36000 OR i.itemLevel < 70, 1 , 2) AS expansion,
                      i.quality,
                      ic.name AS icon,
                      i.gemEnchantmentId AS enchId,
                      i.gemColorMask AS colors,
                      i.requiredSkill,
                      i.itemLevel
            FROM      ?_items i
            JOIN      ?_icons ic ON ic.id = i.iconId
            WHERE     i.gemEnchantmentId <> 0
            ORDER BY  i.id DESC');
        $success = true;

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!CLISetup::writeDir('datasets/'.$dir))
                $success = false;

        $enchIds = [];
        foreach ($gems as $pop)
            $enchIds[] = $pop['enchId'];

        $enchantments = new EnchantmentList(array(['id', $enchIds], CFG_SQL_LIMIT_NONE));
        if ($enchantments->error)
        {
            CLI::write('Required table ?_itemenchantment seems to be empty! Leaving gems()...', CLI::LOG_ERROR);
            CLI::write();
            return false;
        }

        foreach (CLISetup::$localeIds as $lId)
        {
            set_time_limit(5);

            User::useLocale($lId);
            Lang::load($lId);

            $gemsOut = [];
            foreach ($gems as $pop)
            {
                if (!$enchantments->getEntry($pop['enchId']))
                {
                    CLI::write(' * could not find enchantment #'.$pop['enchId'].' referenced by item #'.$gem['itemId'], CLI::LOG_WARN);
                    continue;
                }

                $gemsOut[$pop['itemId']] = array(
                    'name'        => Util::localizedString($pop, 'name'),
                    'quality'     => $pop['quality'],
                    'icon'        => strToLower($pop['icon']),
                    'enchantment' => $enchantments->getField('name', true),
                    'jsonequip'   => $enchantments->getStatGain(),
                    'colors'      => $pop['colors'],
                    'expansion'   => $pop['expansion'],
                    'gearscore'   => Util::getGemScore($pop['itemLevel'], $pop['quality'], $pop['requiredSkill'] == 755, $pop['itemId'])
                );
            }

            $toFile = "var g_gems = ".Util::toJSON($gemsOut).";";
            $file   = 'datasets/'.User::$localeString.'/gems';

            if (!CLISetup::writeFile($file, $toFile))
                $success = false;
        }

        return $success;
    }

?>
