<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// requires https://github.com/TrinityCore/TrinityCore/commit/f989c7182c4cc30f1d0ffdc566c7624a5e108a2f

/* deps:
 * creature
 * creature_addon
 * gameobject
 * gameobject_template
 * vehicle_accessory
 * vehicle_accessory_template
 * script_waypoint
 * waypoints
 * waypoint_data
*/

$customData = array(
);
$reqDBC = ['worldmaparea', 'map', 'worldmaptransforms', 'dungeonmap', 'taxipathnode'];

function spawns()                                           // and waypoints
{
    $alphaMapCache = [];
    $alphaMapCheck = function ($areaId, array &$set) use (&$alphaMapCache)
    {
        $file = 'setup/generated/alphaMaps/'.$areaId.'.png';
        if (!file_exists($file))                            // file does not exist (probably instanced area)
            return false;

        // invalid and corner cases (literally)
        if (!is_array($set) || empty($set['posX']) || empty($set['posY']) || $set['posX'] >= 100 || $set['posY'] >= 100)
        {
            $set = null;
            return true;
        }

        if (empty($alphaMapCache[$areaId]))
            $alphaMapCache[$areaId] = imagecreatefrompng($file);

        // alphaMaps are 1000 x 1000, adapt points [black => valid point]
        if (!imagecolorat($alphaMapCache[$areaId], $set['posX'] * 10, $set['posY'] * 10))
            $set = null;

        return true;
    };

    $checkCoords = function ($points) use($alphaMapCheck)
    {
        $result   = [];
        $capitals = array(                                  // capitals take precedence over their surroundings
            1497, 1637, 1638, 3487,                         // Undercity,      Ogrimmar,  Thunder Bluff, Silvermoon City
            1519, 1537, 1657, 3557,                         // Stormwind City, Ironforge, Darnassus,     The Exodar
            3703, 4395                                      // Shattrath City, Dalaran
        );

        foreach ($points as $res)
        {
            if ($alphaMapCheck($res['areaId'], $res))
            {
                if (!$res)
                    continue;

                // some rough measure how central the spawn is on the map (the lower the number, the better)
                // 0: perfect center; 1: touches a border
                $q = abs( (($res['posX'] - 50) / 50) * (($res['posY'] - 50) / 50) );

                if (empty($result) || $result[0] > $q)
                    $result = [$q, $res];
            }
            else if (in_array($res['areaId'], $capitals))   // capitals (auto-discovered) and no hand-made alphaMap available
                return $res;
            else if (empty($result))                        // add with lowest quality if alpha map is missing
                $result = [1.0, $res];
        }

        // spawn does not really match on a map, but we need at least one result
        if (!$result)
        {
            usort($points, function ($a, $b) { return ($a['quality'] < $b['quality']) ? -1 : 1; });
            $result = [1.0, $points[0]];
        }

        return $result[1];
    };

    $query[1] = ['SELECT c.guid, 1 AS "type", c.id AS typeId, c.spawntimesecs AS respawn, c.phaseMask, c.zoneId AS areaId, c.map, IFNULL(ca.path_id, 0) AS pathId, c.position_y AS `posX`, c.position_x AS `posY` ' .
                 'FROM creature c LEFT JOIN creature_addon ca ON ca.guid = c.guid',
                 ' - assembling '.CLISetup::bold('creature').' spawns'];

    $query[2] = ['SELECT c.guid, 2 AS "type", c.id AS typeId, ABS(c.spawntimesecs) AS respawn, c.phaseMask, c.zoneId AS areaId, c.map, 0 as pathId, c.position_y AS `posX`, c.position_x AS `posY` ' .
                 'FROM gameobject c',
                 ' - assembling '.CLISetup::bold('gameobject').' spawns'];

    $query[3] = ['SELECT c.guid, w.entry AS "npcOrPath", w.pointId AS "point", c.zoneId AS areaId, c.map, w.waittime AS "wait", w.location_y AS `posX`, w.location_x AS `posY` ' .
                 'FROM creature c JOIN script_waypoint w ON c.id = w.entry',
                 ' - assembling waypoints from '.CLISetup::bold('script_waypoint')];

    $query[4] = ['SELECT c.guid, w.entry AS "npcOrPath", w.pointId AS "point", c.zoneId AS areaId, c.map, 0 AS "wait", w.position_y AS `posX`, w.position_x AS `posY` ' .
                 'FROM creature c JOIN waypoints w ON c.id = w.entry',
                 ' - assembling waypoints from '.CLISetup::bold('waypoints')];

    $query[5] = ['SELECT c.guid, -w.id AS "npcOrPath", w.point, c.zoneId AS areaId, c.map, w.delay AS "wait", w.position_y AS `posX`, w.position_x AS `posY` ' .
                 'FROM creature c JOIN creature_addon ca ON ca.guid = c.guid JOIN waypoint_data w ON w.id = ca.path_id WHERE ca.path_id <> 0',
                 ' - assembling waypoints from '.CLISetup::bold('waypoint_data')];

    $queryPost = 'SELECT dm.Id, wma.areaId, IFNULL(dm.floor, 0) AS floor, ' .
                 '100 - ROUND(IF(dm.Id IS NOT NULL, (?f - dm.minY) * 100 / (dm.maxY - dm.minY), (?f - wma.right)  * 100 / (wma.left - wma.right)), 1) AS `posX`, ' .
                 '100 - ROUND(IF(dm.Id IS NOT NULL, (?f - dm.minX) * 100 / (dm.maxX - dm.minX), (?f - wma.bottom) * 100 / (wma.top - wma.bottom)), 1) AS `posY`, ' .
                 '((abs(IF(dm.Id IS NOT NULL, (?f - dm.minY) * 100 / (dm.maxY - dm.minY), (?f - wma.right)  * 100 / (wma.left - wma.right)) - 50) / 50) * ' .
                 ' (abs(IF(dm.Id IS NOT NULL, (?f - dm.minX) * 100 / (dm.maxX - dm.minX), (?f - wma.bottom) * 100 / (wma.top - wma.bottom)) - 50) / 50)) AS quality ' .
                 'FROM dbc_worldmaparea wma ' .
                 'LEFT JOIN dbc_dungeonmap dm ON dm.mapId = IF(?d AND (wma.mapId NOT IN (0, 1, 530, 571) OR wma.areaId = 4395), wma.mapId, -1) ' .
                 'WHERE wma.mapId = ?d AND IF(?d, wma.areaId = ?d, wma.areaId <> 0) ' .
                 'HAVING (`posX` BETWEEN 0.1 AND 99.9 AND `posY` BETWEEN 0.1 AND 99.9) AND (dm.Id IS NULL OR ?d) ' .
                 'ORDER BY quality ASC';


    /*********************/
    /* truncate old data */
    /*********************/

    DB::Aowow()->query('TRUNCATE TABLE ?_spawns');
    DB::Aowow()->query('TRUNCATE TABLE ?_creature_waypoints');


    /**************************/
    /* offsets for transports */
    /**************************/

    $transports = DB::World()->selectCol('SELECT Data0 AS pathId, Data6 AS ARRAY_KEY FROM gameobject_template WHERE type = 15 AND Data6 <> 0');
    foreach ($transports as &$t)
        $t = DB::Aowow()->selectRow('SELECT posX, posY, mapId FROM dbc_taxipathnode tpn WHERE tpn.pathId = ?d AND nodeIdx = 0', $t);


    /**************/
    /* perform... */
    /**************/

    foreach ($query as $idx => $q)
    {
        CLISetup::log($q[1]);

        $n   = 0;
        $sum = 0;
        foreach (DB::World()->select($q[0]) as $spawn)
        {
            if (!$n)
                CLISetup::log(' * sets '.($sum + 1).' - '.($sum += SqlGen::$stepSize));

            if ($n++ > SqlGen::$stepSize)
                $n = 0;

            // npc/object is on a transport -> apply offsets to path of transport
            // note, that the coordinates are mixed up .. again
            // also note, that transport DO spawn outside of displayable area maps .. another todo i guess..
            if (isset($transports[$spawn['map']]))
            {
                $spawn['posX'] += $transports[$spawn['map']]['posY'];
                $spawn['posY'] += $transports[$spawn['map']]['posX'];
                $spawn['map']   = $transports[$spawn['map']]['mapId'];
            }

            $points = DB::Aowow()->select($queryPost, $spawn['posX'], $spawn['posX'], $spawn['posY'], $spawn['posY'], $spawn['posX'], $spawn['posX'], $spawn['posY'], $spawn['posY'], 1, $spawn['map'], $spawn['areaId'], $spawn['areaId'], $spawn['areaId'] ? 1 : 0);
            if (!$points)                                   // retry: TC counts pre-instance subareas as instance-maps .. which have no map file
                $points = DB::Aowow()->select($queryPost, $spawn['posX'], $spawn['posX'], $spawn['posY'], $spawn['posY'], $spawn['posX'], $spawn['posX'], $spawn['posY'], $spawn['posY'], 0, $spawn['map'], 0, 0, 1);

            if (!$points)                                   // still impossible (there are areas that are intentionally off the map (e.g. the isles south of tanaris))
            {
                CLISetup::log('GUID '.$spawn['guid'].($idx < 3 ? '' : ' on path/point '.$spawn['npcOrPath'].'/'.$spawn['point']).' could not be matched to displayable area [A:'.$spawn['areaId'].'; X:'.$spawn['posY'].'; Y:'.$spawn['posX'].']', CLISetup::LOG_WARN);
                continue;
            }

            // if areaId is set, area was determined by TC .. we're fine .. mostly
            $final  = $spawn['areaId'] ? $points[0] : $checkCoords($points);

            if ($idx < 3)
            {
                $set = array(
                    'guid'      => $spawn['guid'],
                    'type'      => $spawn['type'],
                    'typeId'    => $spawn['typeId'],
                    'respawn'   => $spawn['respawn'],
                    'phaseMask' => $spawn['phaseMask'],
                    'pathId'    => $spawn['pathId'],
                    'areaId'    => $final['areaId'],
                    'floor'     => $final['floor'],
                    'posX'      => $final['posX'],
                    'posY'      => $final['posY']
                );

                DB::Aowow()->query('REPLACE INTO ?_spawns (?#) VALUES (?a)', array_keys($set), array_values($set));
            }
            else
            {
                $set = array(
                    'creatureOrPath' => $spawn['npcOrPath'],
                    'point'          => $spawn['point'],
                    'wait'           => $spawn['wait'],
                    'areaId'         => $final['areaId'],
                    'floor'          => $final['floor'],
                    'posX'           => $final['posX'],
                    'posY'           => $final['posY']
                );

                DB::Aowow()->query('REPLACE INTO ?_creature_waypoints (?#) VALUES (?a)', array_keys($set), array_values($set));
            }
        }
    }


    /*****************************/
    /* spawn vehicle accessories */
    /*****************************/

    // get vehicle template accessories
    $accessories = DB::World()->select('
        SELECT vta.accessory_entry AS typeId,  c.guid,  vta.entry, count(1) AS nSeats FROM vehicle_template_accessory vta LEFT JOIN creature c ON c.id = vta.entry GROUP BY accessory_entry,  c.guid UNION
        SELECT  va.accessory_entry AS typeId, va.guid, 0 AS entry, count(1) AS nSeats FROM vehicle_accessory           va                                          GROUP BY accessory_entry, va.guid');

    // accessories may also be vehicles (e.g. "Kor'kron Infiltrator" is seated on "Kor'kron Suppression Turret" is seated on "Kor'kron Troop Transport")
    // so we will retry finding a spawned vehicle if none were found on the previous pass and a change occured
    $vGuid   = 0;                                           // not really used, but we need some kind of index
    $n       = 0;
    $matches = -1;
    while ($matches)
    {
        $matches = 0;
        foreach ($accessories as $idx => $data)
        {
            $vehicles = [];
            if ($data['guid'])                              // vehicle already spawned
                $vehicles = DB::Aowow()->select('SELECT s.areaId, s.posX, s.posY, s.floor FROM ?_spawns s WHERE s.guid   = ?d AND s.type = ?d', $data['guid'], TYPE_NPC);
            else if ($data['entry'])                        // vehicle on unspawned vehicle action
                $vehicles = DB::Aowow()->select('SELECT s.areaId, s.posX, s.posY, s.floor FROM ?_spawns s WHERE s.typeId = ?d AND s.type = ?d', $data['entry'], TYPE_NPC);

            if ($vehicles)
            {
                $matches++;
                foreach ($vehicles as $v)                   // if there is more than one vehicle, its probably due to overlapping zones
                    for ($i = 0; $i < $data['nSeats']; $i++)
                        DB::Aowow()->query('
                            REPLACE INTO ?_spawns (`guid`, `type`, `typeId`, `respawn`, `spawnMask`, `phaseMask`, `areaId`, `floor`, `posX`, `posY`, `pathId`) VALUES
                            (?d, ?d, ?d, 0, 0, 1, ?d, ?d, ?d, ?d, 0)', --$vGuid, TYPE_NPC, $data['typeId'], $v['areaId'], $v['floor'], $v['posX'], $v['posY']);

                unset($accessories[$idx]);
            }
        }
        if ($matches)
            CLISetup::log(' * assigned '.$matches.' accessories on '.++$n.'. pass on vehicle accessories');
    }
    if ($accessories)
        CLISetup::log(count($accessories).' accessories could not be fitted onto a spawned vehicle.', CLISetup::LOG_WARN);


    /********************************/
    /* restrict difficulty displays */
    /********************************/

    DB::Aowow()->query('UPDATE ?_spawns s, dbc_worldmaparea wma, dbc_map m SET s.spawnMask = 0 WHERE s.areaId = wma.areaId AND wma.mapId = m.Id AND m.areaType IN (0, 3, 4)');

    return true;
}

?>
