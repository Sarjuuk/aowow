<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// requires https://github.com/TrinityCore/TrinityCore/commit/f989c7182c4cc30f1d0ffdc566c7624a5e108a2f to have been used at least once

CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected array $info = array(
        'spawns'       => [[   ], CLISetup::ARGV_PARAM,    'Compiles map points from dbc and world db.'       ],
        'creatures'    => [['1'], CLISetup::ARGV_OPTIONAL, '...just the creature positions.'                  ],
        'objects'      => [['2'], CLISetup::ARGV_OPTIONAL, '...just the gameobject positions.'                ],
        'soundemitter' => [['3'], CLISetup::ARGV_OPTIONAL, '...just the soundemitter positions.'              ],
        'areatrigger'  => [['4'], CLISetup::ARGV_OPTIONAL, '...just the areatrigger and teleporter positions.'],
        'instances'    => [['5'], CLISetup::ARGV_OPTIONAL, '...just the instance portal positions.'           ],
        'waypoints'    => [['6'], CLISetup::ARGV_OPTIONAL, '...just the creature waypoints.'                  ],
        'motransports' => [['7'], CLISetup::ARGV_OPTIONAL, '...just the map object transport waypoints.'      ]
   );

    protected array $dbcSourceFiles  = ['worldmaparea', 'map', 'taxipathnode', 'soundemitters', 'areatrigger', 'areatable'];
    protected array $worldDependency = ['creature', 'creature_addon', 'creature_template_addon', 'gameobject', 'gameobject_template', 'vehicle_accessory', 'vehicle_accessory_template', 'waypoint_data', 'smart_scripts', 'areatrigger_teleport'];
    protected array $setupAfter      = [['dungeonmap', 'worldmaparea', 'zones'], ['img-maps']];

    private array $transports   = [];
    private array $overrideData = [];
    private array $mapToArea    = [];
    private array $areaParents  = [];
    /** @var array<int, int> $vGUIDS virtual GUIDS for entities not spawned by default. table => guids */
    private array $vGUIDSs      = [Type::NPC => 0, Type::OBJECT => 0, Type::AREATRIGGER => 0];

    private array $areatrigger {                            // reuse for smart_script spawns
        get => $this->areatrigger ??= DB::Aowow()->selectAssoc('SELECT `id` AS "guid", `id` AS ARRAY_KEY, `id` AS "typeId", 0 AS "flags", `mapId`, `posX` AS "mapX", `posY` AS "mapY" FROM dbc_areatrigger');
    }

    private const STEP_FLAG_WAYPOINT      = 0x1;
    private const STEP_FLAG_TRANSPORTABLE = 0x2;

    private array $steps = array(
        0x01 => ['creature',     Type::NPC,         self::STEP_FLAG_TRANSPORTABLE,                            'creature spawns',                                  ],
        0x02 => ['gameobject',   Type::OBJECT,      self::STEP_FLAG_TRANSPORTABLE,                            'gameobject spawns',                                ],
        0x04 => ['soundemitter', Type::SOUND,       self::STEP_FLAG_TRANSPORTABLE,                            'SoundEmitters.dbc positions',                      ],
        0x08 => ['areatrigger',  Type::AREATRIGGER, 0x0,                                                      'AreaTrigger.dbc positions and teleporter endpoints'],
        0x10 => ['instances',    Type::ZONE,        0x0,                                                      'Map.dbc instance portals positions'                ],
        0x20 => ['waypoints',    Type::NPC,         self::STEP_FLAG_WAYPOINT | self::STEP_FLAG_TRANSPORTABLE, 'NPC waypoints from `waypoint_data`'                ],
        0x40 => ['motransports', Type::OBJECT,      self::STEP_FLAG_WAYPOINT,                                 'MO Transport waypoints from TaxiPathNode.dbc'      ]
    );

    public function generate() : bool
    {
        /*****************************/
        /* find out what to generate */
        /*****************************/

        $opts     = array_slice(array_keys($this->info), 1);
        $getOpt   = CLISetup::getOpt(...$opts);
        $todoMask = 0x0;

        if ($getOpt['creatures'])
            $todoMask |= 0x01;
        if ($getOpt['objects'])
            $todoMask |= 0x02;
        if ($getOpt['soundemitter'])
            $todoMask |= 0x04;
        if ($getOpt['areatrigger'])
            $todoMask |= 0x08;
        if ($getOpt['instances'])
            $todoMask |= 0x10;
        if ($getOpt['waypoints'])
            $todoMask |= 0x20;
        if ($getOpt['motransports'])
            $todoMask |= 0x40;

        if ($todoMask)
            foreach ($this->steps as $idx => $_)
                if (!($todoMask & $idx))
                    unset($this->steps[$idx]);


        /*********************************/
        /* selectively truncate old data */
        /*********************************/

        foreach ($this->steps as $idx => [, $type, $stepFlags, ])
            if (!($stepFlags & self::STEP_FLAG_WAYPOINT) && (!$todoMask || ($idx & $todoMask)) && ($tbl = WorldPosition::getStorageByType($type)))
                DB::Aowow()->qry('TRUNCATE TABLE %n', $tbl);

        if (!$todoMask || ($todoMask & 0x20))
            DB::Aowow()->qry('TRUNCATE TABLE ::creature_waypoints');
        if (!$todoMask || ($todoMask & 0x40))
            DB::Aowow()->qry('TRUNCATE TABLE ::objects_waypoints');


        /**************************/
        /* offsets for transports */
        /**************************/

        $this->transports = DB::World()->selectAssoc('SELECT `data0` AS ARRAY_KEY, `data6` AS "moMapId", `entry` AS "typeId" FROM gameobject_template WHERE `type` = %i', OBJECT_MO_TRANSPORT);
        foreach ($this->transports as $pathId => &$t)       // use a node near where the transport is stopped. Its more likely to be in a mappable area and does not overlap the node with stop info.
        {
            $t += ['guid' => --$this->vGUIDSs[Type::OBJECT]];
            $t += (DB::Aowow()->selectRow('SELECT n.`mapId`, n.`posX` AS "mapX", n.`posY` AS "mapY" FROM dbc_taxipathnode n JOIN dbc_taxipathnode x ON x.`pathId` = n.`pathId` AND x.`nodeIdx` = n.`nodeIdx` + 1 WHERE x.`pathId` = %i AND x.`flags` & 2 ORDER BY n.nodeIdx ASC', $pathId) ?: []);
        }


        /*********************/
        /* get override data */
        /*********************/

        $this->overrideData = DB::Aowow()->selectAssoc('SELECT `type` AS ARRAY_KEY, `typeGuid` AS ARRAY_KEY2, `areaId`, `floor` FROM ::spawns_override');
        $this->mapToArea    = DB::Aowow()->selectCol('SELECT `mapId` AS ARRAY_KEY, `id` FROM ::zones WHERE `parentArea` = 0 AND (`cuFlags` & %i) = 0', CUSTOM_EXCLUDE_FOR_LISTVIEW);
        $this->areaParents  = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, IF(`parentArea`, `parentArea`, `id`) FROM ::zones');


        /**************/
        /* perform... */
        /**************/

        foreach (array_values($this->steps) as $i => [$generator, $type, $stepFlags, $comment])
        {
            $tbl           = WorldPosition::getStorageByType($type);
            $time          = new Timer(500);
            $sum           = 0;
            $lastOverride  = 0;
            $insertData    = [];
            $nSteps        = count($this->steps);
            $queryResult   = $this->$generator();
            $queryTotal    = count($queryResult);
            $queryTotalLen = strlen($queryTotal);

            CLI::write(' - '.$generator);

            foreach ($queryResult as $spawn)
            {
                $notice = '';
                $guid   = $spawn['guid'] ?? 0;

                $sum++;
                if ($time->update())
                    CLI::write(' * '.($i + 1).'/'.$nSteps.': '. CLI::bold($comment).' - '.sprintf('%'.$queryTotalLen.'d / %d (%4.1f%%)', $sum,  $queryTotal, round(100 * $sum / $queryTotal, 1)), CLI::LOG_BLANK, true, true);

                if (!$transformedPoints = $this->transformPoint($spawn, $type, $stepFlags, $notice))
                {
                    CLI::write('[points] '.str_pad(isset($spawn['point']) ? '['.($spawn['pathId']).'; '.$spawn['point'].'] ' : '['.$guid.'] ', 15).'could not be matched to displayable area [M:'.$spawn['mapId'].'; A:'.($spawn['areaId'] ?? '_').'; X:'.$spawn['mapY'].'; Y:'.$spawn['mapX'].']', CLI::LOG_WARN);
                    $time->reset();
                    continue;
                }

                if ($notice && $lastOverride != $guid)
                {
                    CLI::write($notice, CLI::LOG_INFO);
                    $time->reset();
                    $lastOverride = $guid;                  // don't spam this for waypoints
                }

                // transports effectively duplicate spawn points when the transport map gets reused
                // overriding $guid is intentional
                foreach ($transformedPoints as [$areaId, $areaX, $areaY, $floor, $flags, $guid])
                {
                    $insertData['areaId'][] = $areaId;
                    $insertData['areaX'][]  = $areaX;
                    $insertData['areaY'][]  = $areaY;
                    $insertData['floor'][]  = $floor;

                    if (!($stepFlags & self::STEP_FLAG_WAYPOINT))
                    {
                        $insertData['flags'][] = $flags;
                        $insertData['guid'][]  = $guid;
                    }

                    unset($spawn['areaId'], $spawn['areaX'], $spawn['areaY'], $spawn['flags'], $spawn['guid']/* , $spawn['pathId'] */);
                    foreach ($spawn as $k => $v)
                        $insertData[$k][] = $v;
                }

                if (!($sum % 1000) || $sum == $queryTotal)
                {
                    if (!($stepFlags & self::STEP_FLAG_WAYPOINT))
                        DB::Aowow()->qry('INSERT INTO %n %m', $tbl, $insertData);
                    else
                    {
                        unset($insertData['guid']);
                        // REPLACE: because there is bogus data where one path may be assigned to multiple npcs
                        DB::Aowow()->qry('REPLACE INTO %n %m', $type == Type::NPC ? '::creature_waypoints' : '::objects_waypoints', $insertData);
                    }

                    $insertData = [];
                }
            }
        }


        /*****************************/
        /* spawn vehicle accessories */
        /*****************************/

        if ($todoMask & 0x01)                               // only when creature is set
        {
            // get vehicle template accessories
            $accessories = DB::World()->selectAssoc(
               'SELECT vta.`accessory_entry` AS "typeId",  c.`guid`,  vta.`entry`, COUNT(1) AS "nSeats" FROM vehicle_template_accessory vta LEFT JOIN creature c ON c.`id` = vta.`entry` GROUP BY `accessory_entry`,  c.`guid` UNION
                SELECT  va.`accessory_entry` AS "typeId", va.`guid`, 0 AS "entry", COUNT(1) AS "nSeats" FROM vehicle_accessory           va                                              GROUP BY `accessory_entry`, va.`guid`'
            );

            // accessories may also be vehicles (e.g. "Kor'kron Infiltrator" is seated on "Kor'kron Suppression Turret" is seated on "Kor'kron Troop Transport")
            // so we will retry finding a spawned vehicle if none were found on the previous pass and a change occured
            $n       = 0;
            $matches = -1;
            while ($matches)
            {
                $matches = 0;
                foreach ($accessories as $idx => $data)
                {
                    $vehicles = [];
                    if ($data['guid'])                      // vehicle already spawned
                        $vehicles = DB::Aowow()->selectAssoc('SELECT `areaId`, `areaX`, `areaY`, `floor`, `mapId`, `mapX`, `mapY` FROM ::creature_spawns WHERE `guid`   = %i', $data['guid']);
                    else if ($data['entry'])                // vehicle on unspawned vehicle action
                        $vehicles = DB::Aowow()->selectAssoc('SELECT `areaId`, `areaX`, `areaY`, `floor`, `mapId`, `mapX`, `mapY` FROM ::creature_spawns WHERE `typeId` = %i', $data['entry']);

                    if ($vehicles)
                    {
                        $matches++;
                        foreach ($vehicles as $v)           // if there is more than one vehicle, its probably due to overlapping zones
                            for ($i = 0; $i < $data['nSeats']; $i++)
                                DB::Aowow()->qry('INSERT INTO ::creature_spawns %v', $v + ['guid' => --$this->vGUIDSs[Type::NPC], 'flags' => SPAWNTYPE_VEHICLE_ACCESSORY, 'typeId' => $data['typeId']]);

                        unset($accessories[$idx]);
                    }
                }
                if ($matches)
                    CLI::write(' * assigned '.$matches.' accessories on '.++$n.'. pass on vehicle accessories', CLI::LOG_BLANK, true, true);
            }
            if ($accessories)
                CLI::write('[spawns] - '.count($accessories).' accessories could not be fitted onto a spawned vehicle.', CLI::LOG_WARN);
        }


        /********************************/
        /* restrict difficulty displays */
        /********************************/

        // no spawnmask on continents, in battlegrounds and in arenas
        DB::Aowow()->qry('UPDATE ::creature_spawns s, dbc_worldmaparea wma, dbc_map m SET s.`spawnMask` = 0 WHERE s.`areaId` = wma.`areaId` AND wma.`mapId` = m.`id` AND m.`areaType` IN (0, 3, 4)');
        DB::Aowow()->qry('UPDATE ::objects_spawns  s, dbc_worldmaparea wma, dbc_map m SET s.`spawnMask` = 0 WHERE s.`areaId` = wma.`areaId` AND wma.`mapId` = m.`id` AND m.`areaType` IN (0, 3, 4)');

        return true;
    }

    // {guid, }typeId, flags, {areaId, areaX, areaY, floor, }mapId, mapX, mapY, respawn, spawnMask, phaseMask, pathId, ScriptName, StringId
    private function creature() : array
    {
        // todo? - spawns from spell with SPELL_EFFECT_SUMMON (28) and spellFocusId > 0, if only one location and not a trigger creature?

        CLI::write(' * (1/4) base creature spawns', CLI::LOG_BLANK, true, true);

        $base = DB::World()->selectAssoc(
           'SELECT    c.`guid`, c.`id` AS "typeId", 0 AS "flags", c.`zoneId` AS "areaId", c.`map` AS "mapId", c.`position_x` AS "mapX", c.`position_y` AS "mapY", c.`spawntimesecs` AS "respawn", c.`spawnMask`, c.`phaseMask`, IFNULL(ca.`path_id`, IFNULL(cta.`path_id`, 0)) AS "pathId", NULLIF(`ScriptName`, "") AS "ScriptName", NULLIF(`StringId`, "") AS "StringId"
            FROM      creature c
            LEFT JOIN creature_addon ca           ON ca.guid   = c.guid
            LEFT JOIN creature_template_addon cta ON cta.entry = c.id'
        );

        CLI::write(' * (2/4) creatures spawned directly through SAI', CLI::LOG_BLANK, true, true);

        $smartS = DB::World()->selectAssoc(
           'SELECT    s.`action_param1` AS "typeId", %i AS "flags", x.`zoneId` AS "areaId", IF(s.`source_type` = %i, -s.`entryOrGUID`, x.`map`) AS "mapId", s.`target_x` AS "mapX",  s.`target_y` AS "mapY", -s.`action_param2` AS "respawn", 0 AS "pathId", 0 AS "spawnMask", 0 AS "phaseMask", NULL AS "ScriptName", NULL AS "StringId"
            FROM      smart_scripts s
            LEFT JOIN ( SELECT 0 AS "sType", `guid`, `id`, `zoneId`, `map` FROM creature    c UNION
                        SELECT 1 AS "sType", `guid`, `id`, `zoneId`, `map` FROM gameobject go ) x
                      ON s.`source_Type` = x.`sType` AND IF(s.`entryOrGUID` < 0, x.`guid`, x.`id`) = ABS(s.`entryOrGUID`)
            WHERE     s.`source_type` <> %i AND s.`action_type` = %i AND s.`target_type` = %i',
            SPAWNTYPE_SMART_SCRIPTS, SmartAI::SRC_TYPE_AREATRIGGER,
            SmartAI::SRC_TYPE_ACTIONLIST, SmartAction::ACTION_SUMMON_CREATURE, SmartTarget::TARGET_POSITION
        );

        CLI::write(' * (3/4) creatures spawned indirectly through SAI TALs', CLI::LOG_BLANK, true, true);

        $smartS = array_merge($smartS, DB::World()->selectAssoc(
           'SELECT    tal.`action_param1` AS "typeId", %i AS "flags", x.`zoneId` AS "areaId", IF(s.`source_type` = %i, -s.`entryOrGUID`, x.`map`) AS "mapId", tal.`target_x` AS "mapX", tal.`target_y` AS "mapY", -tal.`action_param2` AS "respawn", 0 AS "pathId", 0 AS "spawnMask", 0 AS "phaseMask", NULL AS "ScriptName", NULL AS "StringId"
            FROM      smart_scripts tal
            JOIN      smart_scripts s
                      ON (s.`action_type` = %i AND tal.`entryOrGUID` = s.`action_param1`) OR
                         (s.`action_type` = %i AND tal.`entryOrGUID` IN (s.`action_param1`, s.`action_param2`, s.`action_param3`, s.`action_param4`, s.`action_param5`, s.`action_param6`)) OR
                         (s.`action_type` = %i AND tal.`entryOrGUID` BETWEEN s.`action_param1` AND s.`action_param2`)
            LEFT JOIN ( SELECT 0 AS "sType", `id`, `guid`, `zoneId`, `map` FROM creature    c UNION
                        SELECT 1 AS "sType", `id`, `guid`, `zoneId`, `map` FROM gameobject go ) x
                      ON s.`source_type` = x.`sType` AND IF(s.`entryOrGUID` < 0, x.`guid`, x.`id`) = ABS(s.`entryOrGUID`)
            WHERE     tal.`source_type` = %i AND tal.`action_type` = %i AND tal.`target_type` = %i',
            SPAWNTYPE_SMART_SCRIPTS, SmartAI::SRC_TYPE_AREATRIGGER,
            SmartAction::ACTION_CALL_TIMED_ACTIONLIST, SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST, SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST,
            SmartAI::SRC_TYPE_ACTIONLIST, SmartAction::ACTION_SUMMON_CREATURE, SmartTarget::TARGET_POSITION
        ));

        CLI::write(' * (4/4) creatures spawned from summon groups', CLI::LOG_BLANK, true, true);

        $sGroups = DB::World()->selectAssoc(
           'SELECT    csg.`entry` AS "typeId", %i AS "flags", x.`zoneId` AS "areaId", IF(csg.`summonerType` = 2, csg.`summonerId`, x.`map`) AS "mapId", csg.`position_x` AS "mapX", csg.`position_y` AS "mapY", IF(csg.`summonType` IN (1, 2, 3, 4, 6), -csg.`summonTime`, 0) AS "respawn", 0 AS "pathId", 0 AS "spawnMask", 0 AS "phaseMask", NULL AS "ScriptName", NULL AS "StringId"
            FROM      creature_summon_groups csg
            LEFT JOIN ( SELECT 0 AS "sType", `id`, `zoneId`, IF(COUNT(DISTINCT `map`) = 1, `map`, NULL) AS "map" FROM creature   GROUP BY `id` UNION
                        SELECT 1 AS "sType", `id`, `zoneId`, IF(COUNT(DISTINCT `map`) = 1, `map`, NULL) AS "map" FROM gameobject GROUP BY `id` ) x
                      ON x.id = csg.`summonerId` AND csg.`summonerType` = x.sType
            HAVING    mapId IS NOT NULL',
            SPAWNTYPE_SUMMON_GROUPS
        );

        foreach ($smartS as &$s)
        {
            $s['guid'] = --$this->vGUIDSs[Type::NPC];
            if ($s['mapId'] < 0)
                $s['mapId'] = $this->areatrigger[-$s['mapId']]['mapId'] ?? null;
        }

        foreach ($sGroups as &$u)
            $u['guid'] = --$this->vGUIDSs[Type::NPC];

        return array_merge(
            $base,
            array_filter($smartS, fn($x) => $x['mapId'] !== null),
            $sGroups
        );
    }

    // {guid, }typeId, flags, {areaId, areaX, areaY, floor, }mapId, mapX, mapY, respawn, spawnMask, phaseMask, pathId, ScriptName, StringId
    private function gameobject() : array
    {
        // todo? - spawns from spell with SPELL_EFFECT_SUMMON_OBJECT_WILD (76) and spellFocusId > 0, if only one location

        CLI::write(' * (1/4) base gameobject spawns', CLI::LOG_BLANK, true, true);

        $base = DB::World()->selectAssoc(
           'SELECT `guid`, `id` AS "typeId", 0 AS "flags", `zoneId` AS "areaId", `map` AS "mapId", `position_x` AS "mapX", `position_y` AS "mapY", `spawntimesecs` AS "respawn", `spawnMask`, `phaseMask`, 0 AS "pathId", NULLIF(`ScriptName`, "") AS "ScriptName", NULLIF(`StringId`, "") AS "StringId"
            FROM   gameobject'
        );

        CLI::write(' * (2/4) mo transport spawns', CLI::LOG_BLANK, true, true);

        // those are not spawned via gamoeobject table
        $transports = [];
        $tBase      = ['flags' => SPAWNTYPE_MO_TRANSPORT, 'respawn' => 0, 'spawnMask' => 0, 'phaseMask' => 0, 'areaId' => 0, 'ScriptName' => null, 'StringId' => null];
        foreach ($this->transports as $pathId => $t)
        {
            unset($t['moMapId']);
            $transports[] = $tBase + $t + ['pathId' => $pathId];
        }

        CLI::write(' * (3/4) gameobjects spawned directly through SAI', CLI::LOG_BLANK, true, true);

        $smartS = DB::World()->selectAssoc(
           'SELECT    s.`action_param1` AS "typeId", %i AS "flags", x.`zoneId` AS "areaId", IF(s.`source_type` = %i, -s.`entryOrGUID`, x.`map`) AS "mapId", s.`target_x` AS "mapX",  s.`target_y` AS "mapY", -s.`action_param2` AS "respawn", 0 AS "spawnMask", 0 AS "phaseMask", 0 AS "pathId", NULL AS "ScriptName", NULL AS "StringId"
            FROM      smart_scripts s
            LEFT JOIN ( SELECT 0 AS "sType", `id`, `guid`, `zoneId`, `map` FROM creature    c UNION
                        SELECT 1 AS "sType", `id`, `guid`, `zoneId`, `map` FROM gameobject go ) x
                      ON s.`source_Type` = x.`sType` AND IF(s.`entryOrGUID` < 0, x.`guid`, x.`id`) = ABS(s.`entryOrGUID`)
            WHERE     s.`source_type` <> %i AND s.`action_type` = %i AND s.`target_type` = %i',
            SPAWNTYPE_SMART_SCRIPTS, SmartAI::SRC_TYPE_AREATRIGGER,
            SmartAI::SRC_TYPE_ACTIONLIST, SmartAction::ACTION_SUMMON_GO, SmartTarget::TARGET_POSITION
        );

        CLI::write(' * (4/4) gameobjects spawned indirectly through SAI TALs', CLI::LOG_BLANK, true, true);

        $smartS = array_merge($smartS, DB::World()->selectAssoc(
           'SELECT    tal.`action_param1` AS "typeId", %i AS "flags", x.`zoneId` AS "areaId", IF(s.`source_type` = %i, -s.`entryOrGUID`, x.`map`) AS "mapId", tal.`target_x` AS "mapX", tal.`target_y` AS "mapY", -tal.`action_param2` AS "respawn", 0 AS "spawnMask", 0 AS "phaseMask", 0 AS "pathId", NULL AS "ScriptName", NULL AS "StringId"
            FROM      smart_scripts tal
            JOIN      smart_scripts s
                      ON (s.`action_type` = %i AND tal.`entryOrGUID` = s.`action_param1`) OR
                         (s.`action_type` = %i AND tal.`entryOrGUID` IN (s.`action_param1`, s.`action_param2`, s.`action_param3`, s.`action_param4`, s.`action_param5`, s.`action_param6`)) OR
                         (s.`action_type` = %i AND tal.`entryOrGUID` BETWEEN s.`action_param1` AND s.`action_param2`)
            LEFT JOIN ( SELECT 0 AS "sType", `id`, `guid`, `zoneId`, `map` FROM creature    c UNION
                        SELECT 1 AS "sType", `id`, `guid`, `zoneId`, `map` FROM gameobject go ) x
                      ON s.`source_type` = x.`sType` AND IF(s.`entryOrGUID` < 0, x.`guid`, x.`id`) = ABS(s.`entryOrGUID`)
            WHERE     tal.`source_type` = %i AND tal.`action_type` = %i AND tal.`target_type` = %i',
            SPAWNTYPE_SMART_SCRIPTS, SmartAI::SRC_TYPE_AREATRIGGER,
            SmartAction::ACTION_CALL_TIMED_ACTIONLIST, SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST, SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST,
            SmartAI::SRC_TYPE_ACTIONLIST, SmartAction::ACTION_SUMMON_GO, SmartTarget::TARGET_POSITION
        ));

        foreach ($smartS as &$s)
        {
            $s['guid'] = --$this->vGUIDSs[Type::OBJECT];
            if ($s['mapId'] < 0)
                $s['mapId'] = $this->areatrigger[-$s['mapId']]['mapId'] ?? null;
        }

        return array_merge(
            $base,
            $transports,
            array_filter($smartS, fn($x) => $x['mapId'] !== null)
        );
    }

    // {guid, }typeId, flags, {areaId, areaX, areaY, floor, }mapId, mapX, mapY
    private function soundemitter() : array
    {
        return DB::Aowow()->selectAssoc('SELECT `id` AS "guid", `soundId` AS "typeId", 0 AS "flags", `mapId`, `posX` AS "mapX", `posY` AS "mapY" FROM dbc_soundemitters');
    }

    // {guid, }typeId, flags, {areaId, areaX, areaY, floor, }mapId, mapX, mapY
    private function areatrigger() : array
    {
        $teleDestinations = DB::World()->selectAssoc(
           'SELECT `ID`          AS "typeId", %i AS "flags",    `target_map` AS "mapId", `target_position_x` AS "mapX", `target_position_y` AS "mapY"
            FROM   areatrigger_teleport UNION
            SELECT `entryorguid` AS "typeId", %i AS "flags", `action_param1` AS "mapId", `target_x`          AS "mapX", `target_y`          AS "mapY"
            FROM   smart_scripts s
            JOIN   areatrigger_scripts a ON a.`entry` = s.`entryorguid` AND a.`ScriptName` = "SmartTrigger"
            WHERE `source_type` = %i AND `action_type` = %i',
            SPAWNTYPE_TELEPORTER_DEST, SPAWNTYPE_TELEPORTER_DEST, SmartAI::SRC_TYPE_AREATRIGGER, SmartAction::ACTION_TELEPORT
        );

        array_walk($teleDestinations, fn(&$x) => $x['guid'] = --$this->vGUIDSs[Type::AREATRIGGER]);

        return array_merge($this->areatrigger, $teleDestinations);
    }

    // {guid, }flags, {areaId, areaX, areaY, floor, }mapId, mapX, mapY
    private function instances() : array
    {
        // maps with set graveyard
        $instances = DB::Aowow()->selectAssoc(
           'SELECT a.`id` AS "guid", 0 AS "flags", m.`parentMapId` AS "mapId", m.`parentX` AS "mapX", m.`parentY` AS "mapY"
            FROM   dbc_areatable a
            JOIN   dbc_map m ON m.id = IF(a.id = 2159, 249, a.mapId) -- Zone: Onyxias Lair is linked to the wrong map
            WHERE m.`parentX` <> 0 AND m.`parentY` <> 0 AND a.`areaTable` = 0'
        );

        // set missing graveyards from areatrigger data (auto-resurrect map or just plain errors)
        // grouped because naxxramas _just has_ to be special with 4 entrances...
        if ($missingMaps = DB::Aowow()->selectCol('SELECT `id` FROM dbc_map WHERE `parentX` = 0 AND `parentY` = 0 AND `parentMapId` > -1 AND `areaType` NOT IN (0, 3, 4)'))
            if ($triggerIds = DB::World()->selectCol('SELECT `target_map`, `id` AS ARRAY_KEY FROM areatrigger_teleport WHERE `target_map` IN %in GROUP BY `target_map`', $missingMaps))
                $positions = DB::Aowow()->selectAssoc('SELECT `id` AS "guid", 0 AS "flags", `mapId`, `posX` AS "mapX", `posY` AS "mapY" FROM dbc_areatrigger WHERE `id` IN %in', array_keys($triggerIds));

        return array_merge(
            $instances,
            $positions ?? []
        );
    }

    // pathId, point, {areaId, areaX, areaY, floor, }mapId, mapX, mapY, wait, action, actionChance
    private function waypoints() : array
    {
        // assume that creature_template_addon data isn't stupid and only creatures with a single spawn are referenced here
        $wPoints = DB::World()->selectAssoc(
           'SELECT w.`id` AS "pathId", w.`point`, c.`zoneId` AS "areaId", c.`map` AS "mapId", w.`position_x` AS "mapX", w.`position_y` AS "mapY", w.`delay` AS "wait", w.`action`, w.`action_chance` AS "actionChance"
            FROM   creature c
            JOIN   creature_addon ca ON ca.`guid` = c.`guid`
            JOIN   waypoint_data w ON w.`id` = ca.`path_id`
            WHERE  ca.`path_id` <> 0 UNION
            SELECT w.`id` AS "pathId", w.`point`, c.`zoneId` AS "areaId", c.`map` AS "mapId", w.`position_x` AS "mapX", w.`position_y` AS "mapY", w.`delay` AS "wait", w.`action`, w.`action_chance` AS "actionChance"
            FROM   creature c
            JOIN   creature_template_addon cta ON cta.`entry` = c.`id`
            JOIN   waypoint_data w ON w.`id` = cta.`path_id`
            WHERE  cta.`path_id` <> 0'
        );

        // todo: one creature may have multiple paths assigned (a particular egregious example being npc 25244) no idea yet how best to handle so the queries are grouped for now.
        $smartWP = DB::World()->selectAssoc(
           'SELECT    s.`entryorguid`, s.`action_type`, s.`action_param1`, s.`action_param2`, s.`action_param3`, s.`action_param4`, s.`action_param5`, s.`action_param6`
            FROM      smart_scripts s
            WHERE     s.`source_type` <> %i AND s.`action_type` IN %in
            GROUP BY  s.`entryorguid`, s.`action_type`, s.`action_param2`',
            SmartAI::SRC_TYPE_ACTIONLIST, [SmartAction::ACTION_WP_START, SmartAction::ACTION_START_CLOSEST_WAYPOINT]
        );

        $smartWP = array_merge($smartWP, DB::World()->selectAssoc(
           'SELECT    s.`entryorguid`, tal.`action_type`, tal.`action_param1`, tal.`action_param2`, tal.`action_param3`, tal.`action_param4`, tal.`action_param5`, tal.`action_param6`
            FROM      smart_scripts tal
            JOIN      smart_scripts s
                      ON (s.`action_type` = %i AND tal.`entryOrGUID` = s.`action_param1`) OR
                         (s.`action_type` = %i AND tal.`entryOrGUID` IN (s.`action_param1`, s.`action_param2`, s.`action_param3`, s.`action_param4`, s.`action_param5`, s.`action_param6`)) OR
                         (s.`action_type` = %i AND tal.`entryOrGUID` BETWEEN s.`action_param1` AND s.`action_param2`)
            WHERE     tal.`source_type` = %i AND tal.`action_type` IN %in
            GROUP BY  s.`entryorguid`, tal.`action_type`, tal.`action_param2`',
            SmartAction::ACTION_CALL_TIMED_ACTIONLIST, SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST, SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST,
            SmartAI::SRC_TYPE_ACTIONLIST, [SmartAction::ACTION_WP_START, SmartAction::ACTION_START_CLOSEST_WAYPOINT]
        ));

        $fixed = $flex = $smartPaths = [];
        foreach ($smartWP as $s)
        {
            if ($s['action_type'] == SmartAction::ACTION_WP_START)
                $fixed[$s['entryorguid']] = $s['action_param2'];
            else // SmartAction::ACTION_START_CLOSEST_WAYPOINT
                for ($i = 1; $i < 7; $i++)
                    if ($s['action_param'.$i])
                        $flex[$s['entryorguid']][] = $s['action_param'.$i];
        }

        $allPaths = DB::World()->selectAssoc('SELECT `id` AS "pathId", `point`, `position_x` AS "mapX", `position_y` AS "mapY", `delay` AS "wait", `action`, `action_chance` AS "actionChance" FROM waypoint_data WHERE `id` IN %in', array_merge($fixed, ...$flex));

        // find creature for SmartAction::ACTION_WP_START
        foreach ($fixed as $id => $pathId)
        {
            $keyCol = $id > 0 ? 'typeId' : 'guid';
            $npcs   = DB::Aowow()->selectAssoc('SELECT %n AS ARRAY_KEY, `mapId`, `mapX`, `mapY`, `areaId` FROM ::creature_spawns WHERE %n = %i', $keyCol, $keyCol, abs($id));
            $paths  = array_filter($allPaths, fn($x) => $x['pathId'] == $pathId);
            foreach ($npcs as $n)
            {
                foreach ($paths as $p)
                    $smartPaths[] = $p + ['mapId' => $n['mapId'], 'areaId' => $n['areaId']];

                DB::Aowow()->qry('UPDATE ::creature_spawns SET `pathId` = %i WHERE %n = %i', current($paths)['pathId'], $keyCol, abs($id));
                if ($id > 0)                                // assign by typeId only once
                    continue;
            }
        }

        // find nearest creature for SmartAction::ACTION_START_CLOSEST_WAYPOINT
        foreach ($flex as $id => $pathIds)
        {
            $keyCol = $id > 0 ? 'typeId' : 'guid';
            $npcs   = DB::Aowow()->selectAssoc('SELECT %n AS ARRAY_KEY, `mapId`, `mapX`, `mapY`, `areaId` FROM ::creature_spawns WHERE %n = %i', $keyCol, $keyCol, abs($id));
            $paths  = array_filter($allPaths, fn($x) => in_array($x['pathId'], $pathIds) && $x['point'] == 0);
            foreach ($npcs as $n)
            {
                $maxDist   = 0;
                $selectedP = 0;
                foreach ($paths as $p)
                {
                    if (($_ = sqrt(($n['mapX'] - $p['mapX']) ** 2 + ($n['mapY'] - $p['mapY']) ** 2)) > $maxDist && $selectedP)
                        continue;

                    $selectedP = $p['pathId'];
                    $maxDist   = $_;
                }

                foreach (array_filter($allPaths, fn($x) => $x['pathId'] == $selectedP) as $p)
                    $smartPaths[] = $p + ['mapId' => $n['mapId'], 'areaId' => $n['areaId']];

                DB::Aowow()->qry('UPDATE ::creature_spawns SET `pathId` = %i WHERE %n = %i', $selectedP, $keyCol, abs($id));
                if ($id > 0)                                // assign by typeId only once
                    continue;
            }
        }

        return array_merge(
            $wPoints,
            $smartPaths
        );
    }

    // pathId, point, {areaId, areaX, areaY, floor, }mapId, mapX, mapY, wait, arrivalEventId, departureEventId
    private function motransports() : array
    {
        return DB::Aowow()->selectAssoc(
           'SELECT `pathId`, `nodeIdx` AS "point", 0 AS "areaId", `mapId` AS "mapId", `posX` AS "mapX", `posY` AS "mapY", 1000 * `delay` AS "wait", `arrivalEventId`, `departureEventId`
            FROM   dbc_taxipathnode
            WHERE  `pathId` IN %in',
            array_keys($this->transports)
        );
    }

    private function transformPoint(array $point, int $type, int $stepFlags, ?string &$notice = '') : ?array
    {
        $point['guid' ]  ??= 0;
        $point['areaId'] ??= 0;
        $point['flags']  ??= 0;

        $points = [&$point];

        // npc/object is on a transport -> apply offsets to path of transport
        // note that the transports on map 631 (ICC) get reused, effectively duplicating the entities on them
        if (($stepFlags & self::STEP_FLAG_TRANSPORTABLE) && $point['mapId'] > 0 && $mots = array_filter($this->transports, fn($x) => $point['mapId'] == $x['moMapId']))
        {
            $basePoint = $point;                            // each copy needs the original transport-local coordinates
            $i = -1;
            while ($mot = array_pop($mots))
            {
                if (!isset($points[++$i]))
                    $points[$i] = $basePoint;

                $points[$i]['mapX']  += $mot['mapX'];
                $points[$i]['mapY']  += $mot['mapY'];
                $points[$i]['mapId']  = $mot['mapId'];
                $points[$i]['flags'] |= ($mot['moMapId'] & 0xFFF) << 8;

                if ($i)
                    $points[$i]['guid'] = --$this->vGUIDSs[$type];
            }
        }

        $result = [];
        foreach ($points as $p)
        {
            $floor = -1;
            // note: override data for creatures on transports shouldn't exist.
            if (!($stepFlags & self::STEP_FLAG_WAYPOINT) && isset($this->overrideData[$type][$p['guid']]))
            {
                $p['areaId'] = $this->overrideData[$type][$p['guid']]['areaId'];
                $floor       = $this->overrideData[$type][$p['guid']]['floor'];
                $notice      = '[points] '.str_pad('['.$p['guid'].'] ', 15).'manually moved to [A:'.($p['areaId'] ?? 0).' => '.$p['areaId'].'; F: '.$floor.']';
            }

            if ($zonePos = WorldPosition::toZonePos($p['mapId'], $p['mapX'], $p['mapY'], $p['areaId'], $floor))
            {
                // if areaId is set and we match it .. we're fine .. mostly
                if (count($zonePos) == 1 && $p['areaId'] == $zonePos[0]['areaId'])
                    $result[] = [$zonePos[0]['areaId'], $zonePos[0]['posX'], $zonePos[0]['posY'], $zonePos[0]['floor'], $p['flags'], $p['guid']];
                else
                {
                    $pos = WorldPosition::checkZonePos($zonePos); // try to determine best found point by alphamap
                    $result[] = [$pos['areaId'], $pos['posX'], $pos['posY'], $pos['floor'], $p['flags'], $p['guid']];
                }
            }
        }

        if ($result)
            return $result;

        // cannot be placed on a map, try to reuse TC assigned areaId (note: area has been invalid in the past)
        if ($point['areaId'] && isset($this->areaParents[$point['areaId']]))
            return [[$this->areaParents[$point['areaId']], 0, 0, 0, 0, $point['guid']]];

        // we know the instanced map; try to assign a zone this way
        if (!in_array($point['mapId'], [0, 1, 530, 571]) && isset($this->mapToArea[$point['mapId']]))
            return [[$this->mapToArea[$point['mapId']], 0, 0, 0, 0, $point['guid']]];

        return null;
    }
});

?>
