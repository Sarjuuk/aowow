<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // builds 'pets'-file for available locales
    // this script requires the following dbc-files to be parsed and available
    // CreatureFamily, CreatureDisplayInfo, FactionTemplate, AreaTable

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

    function pets(&$log, $locales)
    {
        $success    = true;
        $locations  = [];
        $petList    = DB::Aowow()->Select(
           'SELECT    cr. id,
                      cr.name_loc0, cr.name_loc2, cr.name_loc3, cr.name_loc6, cr.name_loc8,
                      cr.minLevel,
                      cr.maxLevel,
                      CONCAT("[", ft.A, ", ", ft.H, "]") AS react,
                      cr.rank AS classification,
                      cr.family,
                      cr.displayId1 AS displayId,
                      cdi.skin1 AS skin,
                      SUBSTRING_INDEX(cf.iconFile, "\\\\", -1) AS icon,
                      cf.petTalentType AS type
            FROM      ?_creature cr
            JOIN      ?_factiontemplate ft ON ft.Id = cr.faction
            JOIN      dbc.creaturefamily cf ON cf.Id = cr.family
            JOIN      dbc.creaturedisplayinfo cdi ON cdi.id = cr.displayId1
            WHERE     cf.petTalentType <> -1 AND cr.typeFlags & 0x1 AND (cr.cuFlags & 0x2) = 0
            ORDER BY  cr.id ASC');

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!writeDir('datasets/'.$dir, $log))
                $success = false;

        foreach ($locales as $lId)
        {
            User::useLocale($lId);
            Lang::load(Util::$localeStrings[$lId]);

            $petsOut = [];
            foreach ($petList as $pet)
            {
                // get locations
                // again: caching will save you time and nerves
                if (!isset($locations[$pet['id']]))
                    $locations[$pet['id']] = DB::Aowow()->SelectCol('SELECT DISTINCT areaId FROM ?_spawns WHERE type = ?d AND typeId = ?d', TYPE_NPC, $pet['id']);

                $petsOut[$pet['id']] = array(
                    'id'             => $pet['id'],
                    'name'           => Util::localizedString($pet, 'name'),
                    'minlevel'       => $pet['minLevel'],
                    'maxlevel'       => $pet['maxLevel'],
                    'location'       => $locations[$pet['id']],
                    'react'          => $pet['react'],
                    'classification' => $pet['classification'],
                    'family'         => $pet['family'],
                    'displayId'      => $pet['displayId'],
                    'skin'           => $pet['skin'],
                    'icon'           => $pet['icon'],
                    'type'           => $pet['type']
                );
            }

            $toFile  = "var g_pets = ";
            $toFile .= json_encode($petsOut, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            $toFile .= ";";
            $file    = 'datasets/'.User::$localeString.'/pets';

            if (!writeFile($file, $toFile, $log))
                $success = false;
        }

        return $success;
    }
?>
