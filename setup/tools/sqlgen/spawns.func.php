<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// requires https://github.com/TrinityCore/TrinityCore/commit/f989c7182c4cc30f1d0ffdc566c7624a5e108a2f to have been used at least once

SqlGen::register(new class extends SetupScript
{
    protected $command = 'spawns';                          // and waypoints

    protected $tblDependencyTC = ['creature', 'creature_addon', 'gameobject', 'gameobject_template', 'vehicle_accessory', 'vehicle_accessory_template', 'script_waypoint', 'waypoints', 'waypoint_data'];
    protected $dbcSourceFiles  = ['worldmaparea', 'map', 'dungeonmap', 'taxipathnode', 'soundemitters', 'areatrigger', 'areatable'];

    private $querys = array(
        1 => ['SELECT c.guid, 1 AS "type", c.id AS typeId, c.spawntimesecs AS respawn, c.phaseMask, c.zoneId AS areaId, c.map, IFNULL(ca.path_id, 0) AS pathId, c.position_y AS `posX`, c.position_x AS `posY` ' .
              'FROM creature c LEFT JOIN creature_addon ca ON ca.guid = c.guid',
              'creature spawns', Type::NPC],

        2 => ['SELECT c.guid, 2 AS "type", c.id AS typeId, c.spawntimesecs AS respawn, c.phaseMask, c.zoneId AS areaId, c.map, 0 as pathId, c.position_y AS `posX`, c.position_x AS `posY` ' .
              'FROM gameobject c',
              'gameobject spawns', Type::OBJECT],

        3 => ['SELECT id AS "guid", 19 AS "type", soundId AS typeId, 0 AS respawn, 0 AS phaseMask, 0 AS areaId, mapId AS "map", 0 AS pathId, posX, posY ' .
              'FROM dbc_soundemitters',
              'sound emitter spawns', Type::SOUND],

        4 => ['SELECT id AS "guid", 503 AS "type", id AS typeId, 0 AS respawn, 0 AS phaseMask, 0 AS areaId, mapId AS "map", 0 AS pathId, posX, posY ' .
              'FROM dbc_areatrigger',
              'areatrigger spawns', Type::AREATRIGGER],

        5 => ['SELECT c.guid, w.entry AS "npcOrPath", w.pointId AS "point", c.zoneId AS areaId, c.map, w.waittime AS "wait", w.location_y AS `posX`, w.location_x AS `posY` ' .
              'FROM creature c JOIN script_waypoint w ON c.id = w.entry',
              'script_waypoint table', Type::NPC],

        6 => ['SELECT c.guid, w.entry AS "npcOrPath", w.pointId AS "point", c.zoneId AS areaId, c.map, 0 AS "wait", w.position_y AS `posX`, w.position_x AS `posY` ' .
              'FROM creature c JOIN waypoints w ON c.id = w.entry',
              'waypoints table', Type::NPC],

        7 => ['SELECT c.guid, -w.id AS "npcOrPath", w.point, c.zoneId AS areaId, c.map, w.delay AS "wait", w.position_y AS `posX`, w.position_x AS `posY` ' .
              'FROM creature c JOIN creature_addon ca ON ca.guid = c.guid JOIN waypoint_data w ON w.id = ca.path_id WHERE ca.path_id <> 0',
              'waypoint_data table', Type::NPC]
    );

    public function generate(array $ids = []) : bool
    {
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


        /*********************/
        /* get override data */
        /*********************/

        $overrideData = DB::Aowow()->select('SELECT `type` AS ARRAY_KEY, `typeGuid` AS ARRAY_KEY2, areaId, floor FROM ?_spawns_override');


        /**************/
        /* perform... */
        /**************/

        foreach ($this->querys as $idx => $q)
        {
            $intv = 0.5;
            $time = microtime(true);
            $sum  = 0;

            if ($idx == 3 || $idx == 4)
                $queryResult = DB::Aowow()->select($q[0]);
            else
                $queryResult = DB::World()->select($q[0]);

            $qryProgress = array_combine(array_keys($this->querys), array_column($this->querys, 1));
            array_walk($qryProgress, function (&$v, $k) use ($idx) { if ($idx == $k) $v = CLI::bold('<'.$v.'>'); });
            $qryProgress = ' ['.implode(', ', $qryProgress).']';

            $qryTotal = count($queryResult);
            $qtLen    = strlen($qryTotal);
            $doneGUID = 0;
            foreach ($queryResult as $spawn)
            {
                $sum++;
                $newTime = microtime(true);
                if ($newTime > $time + $intv)
                {
                    CLI::write(sprintf(' * %'.$qtLen.'d / %d (%4.1f%%)', $sum,  $qryTotal, round(100 * $sum / $qryTotal, 1)) . $qryProgress, CLI::LOG_BLANK, true, true);
                    $time = $newTime;
                }

                // npc/object is on a transport -> apply offsets to path of transport
                // note, that the coordinates are mixed up .. again
                // also note, that transport DO spawn outside of displayable area maps .. another todo i guess..
                if (isset($transports[$spawn['map']]))
                {
                    $spawn['posX'] += $transports[$spawn['map']]['posY'];
                    $spawn['posY'] += $transports[$spawn['map']]['posX'];
                    $spawn['map']   = $transports[$spawn['map']]['mapId'];
                }

                $area  = $spawn['areaId'];
                $floor = -1;
                if (isset($overrideData[$q[2]][$spawn['guid']]))
                {
                    $area  = $overrideData[$q[2]][$spawn['guid']]['areaId'];
                    $floor = $overrideData[$q[2]][$spawn['guid']]['floor'];
                    if ($doneGUID != $spawn['guid'])
                    {
                        CLI::write('GUID '.$spawn['guid'].' was manually moved [A:'.$spawn['areaId'].' => '.$area.'; F: '.$floor.']', CLI::LOG_INFO);
                        $time = 0;                          // force refresh progress
                        $doneGUID = $spawn['guid'];         // do not spam on waypoints
                    }
                }

                $points = Game::worldPosToZonePos($spawn['map'], $spawn['posX'], $spawn['posY'], $area, $floor);

                // cannot be placed, but we know the instanced map and can thus at least assign a zone
                if (!$points && !in_array($spawn['map'], [0, 1, 530, 571]) && $idx < 5)
                {
                    // use server calculated areaId if possible
                    $area = $area ?: DB::Aowow()->selectCell('SELECT id FROM aowow_zones WHERE mapId = ?d AND parentArea = 0 AND (cuFlags & ?d) = 0', $spawn['map'], CUSTOM_EXCLUDE_FOR_LISTVIEW);
                    if (!$area)
                    {
                        CLI::write('tried to default GUID '.$spawn['guid'].' to instanced area by mapId, but returned empty [M:'.$spawn['map'].']', CLI::LOG_WARN);
                        $time = 0;                          // force refresh progress
                        continue;
                    }
                    $final = ['areaId' => $area, 'posX' => 0, 'posY' => 0, 'floor' => 0];
                }
                else if (!$points)                          // still impossible (there are areas that are intentionally off the map (e.g. the isles south of tanaris))
                {
                    CLI::write('GUID '.$spawn['guid'].($idx < 5 ? '' : ' on path/point '.$spawn['npcOrPath'].'/'.$spawn['point']).' could not be matched to displayable area [A:'.$area.'; X:'.$spawn['posY'].'; Y:'.$spawn['posX'].']', CLI::LOG_WARN);
                    $time = 0;                              // force refresh progress
                    continue;
                }
                else                                        // if areaId is set, area was determined by TC .. we're fine .. mostly
                    $final = $area ? $points[0] : Game::checkCoords($points);

                if ($idx < 5)
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
        $vGuid   = 0;                                       // not really used, but we need some kind of index
        $n       = 0;
        $matches = -1;
        while ($matches)
        {
            $matches = 0;
            foreach ($accessories as $idx => $data)
            {
                $vehicles = [];
                if ($data['guid'])                          // vehicle already spawned
                    $vehicles = DB::Aowow()->select('SELECT s.areaId, s.posX, s.posY, s.floor FROM ?_spawns s WHERE s.guid   = ?d AND s.type = ?d', $data['guid'], Type::NPC);
                else if ($data['entry'])                    // vehicle on unspawned vehicle action
                    $vehicles = DB::Aowow()->select('SELECT s.areaId, s.posX, s.posY, s.floor FROM ?_spawns s WHERE s.typeId = ?d AND s.type = ?d', $data['entry'], Type::NPC);

                if ($vehicles)
                {
                    $matches++;
                    foreach ($vehicles as $v)               // if there is more than one vehicle, its probably due to overlapping zones
                        for ($i = 0; $i < $data['nSeats']; $i++)
                            DB::Aowow()->query('
                                REPLACE INTO ?_spawns (`guid`, `type`, `typeId`, `respawn`, `spawnMask`, `phaseMask`, `areaId`, `floor`, `posX`, `posY`, `pathId`) VALUES
                                (?d, ?d, ?d, 0, 0, 1, ?d, ?d, ?f, ?f, 0)', --$vGuid, Type::NPC, $data['typeId'], $v['areaId'], $v['floor'], $v['posX'], $v['posY']);

                    unset($accessories[$idx]);
                }
            }
            if ($matches)
                CLI::write(' * assigned '.$matches.' accessories on '.++$n.'. pass on vehicle accessories', CLI::LOG_BLANK, true, true);
        }
        if ($accessories)
            CLI::write(count($accessories).' accessories could not be fitted onto a spawned vehicle.', CLI::LOG_WARN);


        /********************************/
        /* restrict difficulty displays */
        /********************************/

        DB::Aowow()->query('UPDATE ?_spawns s, dbc_worldmaparea wma, dbc_map m SET s.spawnMask = 0 WHERE s.areaId = wma.areaId AND wma.mapId = m.id AND m.areaType IN (0, 3, 4)');

        return true;
    }
});

?>
