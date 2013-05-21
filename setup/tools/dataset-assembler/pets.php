<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // builds 'pets'-file for available locales
    // this script requires the following dbc-files to be parsed and available
    // CreatureFamily, CreatureDisplayInfo, FactionTemplate, AreaTable

    // Todo:
    // locations are a tiny bit wide at the moment.
    // I'm still undecided wether the old system is pure genius or pure madness. While building the zone-maps it also generated masks for that zone, using the alpha-channel in the *.blp
    // When deciding what spawn lies where you could check against the relative coordinates of that mask. black => isInZone; white => notInZone
    // Since i'm lacking other options this will probably be reimplemented.

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

    $petQuery = '
        SELECT
            ct.entry as id,
            ct.name,
            lc.*,
            ct.minlevel,
            ct.maxlevel,
            CONCAT("[", ft.A, ", ", ft.H, "]") as react,
            ct.rank as classification,
            ct.family,
            ct.modelId1 as displayId,
            cdi.skin1 as skin,
            cf.iconString as icon,
            cf.petTalentType as type
        FROM
            world.creature_template ct
        JOIN
            ?_factionTemplate ft ON
                ft.Id = ct.faction_A    -- no beast has different faction set for Horde
        JOIN
            ?_creatureFamily cf ON
                cf.Id = ct.family
        JOIN
            world.locales_creature lc ON
                lc.entry = ct.entry
        JOIN
            dbc.creatureDisplayInfo cdi ON
                cdi.id = ct.modelId1
        WHERE
            cf.petTalentType <> -1 AND
            ct.type_flags & 0x1
        ORDER BY
            ct.entry ASC;
    ';

    $queryZones = '
        SELECT DISTINCT
            z.id AS location
        FROM
            world.creature c
        JOIN
            ?_zones z ON
                z.x_min < c.position_x AND
                z.x_max > c.position_x AND
                z.y_min < c.position_y AND
                z.y_max > c.position_y AND
                z.mapId = c.map
        WHERE
            c.id = ?d;
    ';

    $queryInstanceZone = '
        SELECT DISTINCT
            z.id AS location
        FROM
            world.creature c,
            ?_zones z
        WHERE
            z.mapId = c.map AND
            c.id = ?d;
    ';

    $petList   = DB::Aowow()->Select($petQuery);
    $locales   = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    $locations = [];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);

        $petsOut = [];

        foreach ($petList as $pet)
        {
            // get locations
            // again: caching will save you time and nerves
            if (!isset($locations[$pet['id']]))
            {
                $locations[$pet['id']] = DB::Aowow()->SelectCol($queryZones, $pet['id']);

                // probably instanced, map <=> areaId _should_ be bijective
                if (empty($locations[$pet['id']]))
                    if ($z = DB::Aowow()->SelectCell($queryInstanceZone, $pet['id']))
                        $locations[$pet['id']][] = $z;
            }

            $pet = array(
                'id'             => $pet['id'],
                'name'           => Util::localizedString($pet, 'name'),
                'minlevel'       => $pet['minlevel'],
                'maxlevel'       => $pet['maxlevel'],
                'location'       => $locations[$pet['id']],
                'react'          => $pet['react'],
                'classification' => $pet['classification'],
                'family'         => $pet['family'],
                'displayId'      => $pet['displayId'],
                'skin'           => $pet['skin'],
                'icon'           => $pet['icon'],
                'type'           => $pet['type']
            );

            $petsOut[$pet['id']] = $pet;
        }

        $toFile  = "var g_pets = ";
        $toFile .= json_encode($petsOut, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        $toFile .= ";";
        $file    = 'datasets\\'.User::$localeString.'\\pets';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done pets loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    User::useLocale(LOCALE_EN);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);

?>
