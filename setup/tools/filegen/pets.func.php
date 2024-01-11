<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // builds 'pets'-file for available locales

    /* Example data
        30: {
            id:30,
            name:'Forest Spider',
            minlevel:5,
            maxlevel:6,
            location:[12],                                  // master-AreaTableId's (?)
            react:[-1,-1],
            classification:0,                               // 0:"Normal", 1:"Elite", 2:"Rar Elite", 3:"Boss", 4:"Rar"
            family:3,                                       // creatureFamily
            displayId:382,
            skin:'TarantulaSkinOrange',
            icon:'Ability_Hunter_Pet_Spider',               // from creatureFamily.dbc
            type:2                                          // 0:Ferocity, 1:Tenacity, 2:Cunning
        },
    */

    $reqDBC = ['creaturefamily'];

    function pets()
    {
        $success    = true;
        $locations  = [];
        $petList    = DB::Aowow()->Select(
           'SELECT    cr.id,
                      cr.name_loc0, cr.name_loc2, cr.name_loc3, cr.name_loc4, cr.name_loc6, cr.name_loc8,
                      cr.minLevel,
                      cr.maxLevel,
                      ft.A,
                      ft.H,
                      cr.rank AS classification,
                      cr.family,
                      cr.displayId1 AS displayId,
                      cr.textureString AS skin,
                      LOWER(SUBSTRING_INDEX(cf.iconString, "\\\\", -1)) AS icon,
                      cf.petTalentType AS type
            FROM      ?_creature cr
            JOIN      ?_factiontemplate  ft ON ft.id = cr.faction
            JOIN      dbc_creaturefamily cf ON cf.id = cr.family
            WHERE     cr.typeFlags & 0x1 AND (cr.cuFlags & 0x2) = 0
            ORDER BY  cr.id ASC');

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!CLISetup::writeDir('datasets/'.$dir))
                $success = false;

        foreach (CLISetup::$localeIds as $lId)
        {
            User::useLocale($lId);
            Lang::load($lId);

            $petsOut = [];
            foreach ($petList as $pet)
            {
                // get locations
                // again: caching will save you time and nerves
                if (!isset($locations[$pet['id']]))
                    $locations[$pet['id']] = DB::Aowow()->SelectCol('SELECT DISTINCT areaId FROM ?_spawns WHERE type = ?d AND typeId = ?d', Type::NPC, $pet['id']);

                $petsOut[$pet['id']] = array(
                    'id'             => $pet['id'],
                    'name'           => Util::localizedString($pet, 'name'),
                    'minlevel'       => $pet['minLevel'],
                    'maxlevel'       => $pet['maxLevel'],
                    'location'       => $locations[$pet['id']],
                    'react'          => [$pet['A'], $pet['H']],
                    'classification' => $pet['classification'],
                    'family'         => $pet['family'],
                    'displayId'      => $pet['displayId'],
                    'skin'           => $pet['skin'],
                    'icon'           => $pet['icon'],
                    'type'           => $pet['type']
                );
            }

            $toFile = "var g_pets = ".Util::toJSON($petsOut).";";
            $file   = 'datasets/'.User::$localeString.'/pets';

            if (!CLISetup::writeFile($file, $toFile))
                $success = false;
        }

        return $success;
    }
?>
