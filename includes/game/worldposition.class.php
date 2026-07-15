<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/**
 * collection of functions to get a displayable map point from world coordinates
 */
abstract class WorldPosition
{
    private const /* string */ ALPHAMAP_PATH = 'cache/alphaMaps/%d.png';
    private const /* array  */ CAPITAL_CITIES = array(      // capitals take precedence over their surrounding area
        1497, 1637, 1638, 3487,                             // Undercity,      Ogrimmar,  Thunder Bluff, Silvermoon City
        1519, 1537, 1657, 3557,                             // Stormwind City, Ironforge, Darnassus,     The Exodar
        3703, 4395                                          // Shattrath City, Dalaran
    );

    private static array $zoneMapCache  = [];
    private static array $alphaMapCache = [];

    /**
     * test zone positions for placability and select most centered position
     * @param   array[] $points [zonePosSet, ...]
     * * int areaId
     * * float posX
     * * float posY
     * * float dist
     * @return array            best available $point
     */
    public static function checkZonePos(array $points) : array
    {
        $result = [];

        foreach ($points as $res)
        {
            if (self::alphaMapCheck($res['areaId'], $res))
            {
                if (!$res)
                    continue;

                // some rough measure how central the spawn is on the map (the lower the number, the better)
                // 0: perfect center; 1: touches a border
                $q = abs( (($res['posX'] - 50) / 50) * (($res['posY'] - 50) / 50) );

                if (empty($result) || $result[0] > $q)
                    $result = [$q, $res];
            }
            // capitals (auto-discovered) and no hand-made alphaMap available
            else if (in_array($res['areaId'], self::CAPITAL_CITIES))
                return $res;
            // add with lowest quality if alpha map is missing
            else if (empty($result))
                $result = [1.0, $res];
        }

        // spawn does not really match on a map, but we need at least one result
        if (!$result)
        {
            usort($points, fn($a, $b) => $a['dist'] <=> $b['dist']);
            $result = [1.0, $points[0]];
        }

        return $result[1];
    }

    /**
     * get world position for object type and guid
     * @param  int      $type           applicable DBType
     * * Creature
     * * Gameobject
     * * Areatrigger
     * * Sound (emitter)
     * * Zone (instance teleporter coords)
     * @param  int[]    $guids          guids to look up
     * @return array                    [worldPosSet, ...]
     * * int id DBTypeEntry id
     * * int mapId
     * * float posX
     * * float posY
     */
    public static function getForGUID(int $type, int ...$guids) : array
    {
        $result = [];

        switch ($type)
        {
            case Type::NPC:
                $result = DB::World()->selectAssoc('SELECT `guid` AS ARRAY_KEY,              `id`, `map`         AS `mapId`, `position_x` AS `posX`, `position_y` AS `posY` FROM creature        WHERE `guid` IN %in', $guids);
                break;
            case Type::OBJECT:
                $result = DB::World()->selectAssoc('SELECT `guid` AS ARRAY_KEY,              `id`, `map`         AS `mapId`, `position_x` AS `posX`, `position_y` AS `posY` FROM gameobject      WHERE `guid` IN %in', $guids);
                break;
            case Type::SOUND:
                $result = DB::AoWoW()->selectAssoc('SELECT `id`   AS ARRAY_KEY, `soundId` AS `id`,                  `mapId`,                 `posX`,                 `posY` FROM ::soundemitters WHERE `id`   IN %in', $guids);
                break;
            case Type::ZONE:
                $result = DB::Aowow()->selectAssoc('SELECT -`id`  AS ARRAY_KEY,              `id`, `parentMapId` AS `mapId`, `parentX`    AS `posX`, `parentY`    AS `posY` FROM ::zones         WHERE -`id`  IN %in', $guids);
                break;
            case Type::AREATRIGGER:
                if ($base = array_filter($guids, fn($x) => $x > 0))
                    $result = array_replace($result, DB::AoWoW()->selectAssoc('SELECT `id`   AS ARRAY_KEY, `id`,    `mapId`,                 `posX`,                 `posY` FROM ::areatrigger   WHERE `id`   IN %in', $base));
                if ($endpoints = array_filter($guids, fn($x) => $x < 0))
                    $result = array_replace($result, DB::World()->selectAssoc(
                       'SELECT -`ID`          AS ARRAY_KEY, ID          AS `id`,    `target_map` AS `mapId`, `target_position_x` AS `posX`, `target_position_y` AS `posY` FROM areatrigger_teleport WHERE -`id`          IN %in UNION
                        SELECT -`entryorguid` AS ARRAY_KEY, entryorguid AS `id`, `action_param1` AS `mapId`, `target_x`          AS `posX`, `target_y`          AS `posY` FROM smart_scripts        WHERE -`entryorguid` IN %in AND `source_type` = %i AND `action_type` = %i',
                        $endpoints, $endpoints, SmartAI::SRC_TYPE_AREATRIGGER, SmartAction::ACTION_TELEPORT
                     ));
                break;
            default:
                trigger_error('WorldPosition::getForGUID - unsupported TYPE #'.$type, E_USER_WARNING);
        }

        if ($diff = array_diff($guids, array_keys($result)))
            trigger_error('WorldPosition::getForGUID - no spawn points for TYPE #'.$type.' GUIDS: '.implode(', ', $diff), E_USER_WARNING);

        return $result;
    }

    /**
     * convert world position to zone position and sort results by most centered first
     * @param  int      $mapId          map id
     * @param  float    $mapX           world X
     * @param  float    $mapY           world Y
     * @param  int      $preferedAreaId try to place zone position within this area (ignored if it would yield no results)
     * @param  int      $preferedFloor  try to place zone position on this floor (ignored if it would yield no results)
     * @return array                    [zonePosSet, ...]
     * * int id dungeonmap id (or 0)
     * * int areaId
     * * int floor
     * * bool multifloor zone has multiple floors
     * * bool srcPrio takes precedence over similar zone definitions (dungeonmap over worldmaparea)
     * * float posX zone X
     * * float posY zone Y
     * * float dist distance to zone center: i.e. pick priority
     *
     * Reminder that world positions are rotated 90° counterclockwise in relation to zone positions
     */
    public static function toZonePos(int $mapId, float $mapX, float $mapY, int $preferedAreaId = 0, int $preferedFloor = -1) : array
    {
        if (!$mapId < 0)
            return [];

        self::$zoneMapCache[$mapId] ??= self::loadZones($mapId);

        $points = [];
        for ($i = 0; $i < 2; $i++)
        {
            foreach (self::$zoneMapCache[$mapId] as $area)
            {
                if (!$i && $preferedAreaId != 0 && $area['areaId'] != $preferedAreaId)
                    continue;

                if (!$i && $preferedFloor >= 0 && $area['floor'] != $preferedFloor)
                    continue;

                if ($mapX < $area['minX'] || $mapX > $area['maxX'] ||
                    $mapY < $area['minY'] || $mapY > $area['maxY'])
                    continue;

                // dist BETWEEN 0 (center) AND 70.7 (corner)
                $posX = round(($area['maxY'] - $mapY) * 100 / ($area['maxY'] - $area['minY']), 1);
                $posY = round(($area['maxX'] - $mapX) * 100 / ($area['maxX'] - $area['minX']), 1);
                $dist = sqrt(pow(abs($posX - 50), 2) + pow(abs($posY - 50), 2));

                $points[] = array(
                    'id'         => $area['id'],
                    'areaId'     => $area['areaId'],
                    'floor'      => $area['floor'],
                    'multifloor' => $area['multifloor'],
                    'srcPrio'    => $area['srcPrio'],
                    'posX'       => $posX,
                    'posY'       => $posY,
                    'dist'       => $dist
                );
            }

            // retry: pre-instance subareas belong to the instance-maps but are displayed on the outside. There also cases where the zone reaches outside it's own map.
            if ($points)
                break;
        }

        // sort by srcPrio DESC (primary), dist ASC (secondary)
        usort($points, fn($a, $b) => ($b['srcPrio'] <=> $a['srcPrio']) ?: ($a['dist'] <=> $b['dist']));

        return $points;
    }

    /**
     * crude measure to determine if a set of world coordinates is on a displayable part of a map file
     * alpha maps have to be generated by setup (see: aowow --build=img-maps --help)
     * you should rely on your core to calculate zone id and zone coordinates for creature/gameobject spawns
     *
     * @param   int                 $areaId or zone id (used interchangeably) to test on
     * @param   array{float, float} $set    [posX, posY] set of coordinates to test. Set null on mismatch.
     * @return  bool                        check success
     */
    private static function alphaMapCheck(int $areaId, array &$set) : bool
    {
        $file = sprintf(self::ALPHAMAP_PATH, $areaId);
        if (!file_exists($file))                            // file does not exist (probably instanced area)
            return false;

        // invalid and corner cases (literally)
        if (empty($set['posX']) || empty($set['posY']) || $set['posX'] >= 100 || $set['posY'] >= 100)
        {
            $set = null;
            return true;
        }

        self::$alphaMapCache[$areaId] ??= imagecreatefrompng($file);

        // alphaMaps are 1000 x 1000, adapt points [black => valid point]
        if (!imagecolorat(self::$alphaMapCache[$areaId], $set['posX'] * 10, $set['posY'] * 10))
            $set = null;

        return true;
    }

    /**
     * load zone data for cache
     *
     * @param   int     $mapId  load zone data for this map
     * @return  array           [zoneDataset, ...]
     * * int id dungeonmap id (or 0)
     * * int areaId
     * * float minX
     * * float maxX
     * * float minY
     * * float maxY
     * * int floor floor index
     * * bool srcPrio takes precedence over similar zone definitions (dungeonmap over worldmaparea)
     * * bool multifloor zone has multiple floors
     */
    private static function loadZones(int $mapId) : array
    {
        return DB::Aowow()->selectAssoc(
           'SELECT
                x.`id`,
                x.`areaId`,
                x.`minX`, x.`maxX`, x.`minY`, x.`maxY`,
                IF(x.`defaultDungeonMapId` < 0, x.`floor` + 1, x.`floor`) AS `floor`,
                IF(useDM.`id`   IS NOT NULL OR x.`defaultDungeonMapId` < 0, 1, 0) AS `srcPrio`,
                IF(multiDM.`id` IS NOT NULL OR x.`defaultDungeonMapId` < 0, 1, 0) AS `multifloor`
            FROM
                (SELECT 0 AS `id`, `areaId`,     `mapId`, `right` AS `minY`, `left` AS `maxY`, `top` AS `maxX`, `bottom` AS `minX`, 0 AS `floor`, 0 AS `worldMapAreaId`, `defaultDungeonMapId` FROM ::worldmaparea wma UNION
                 SELECT   dm.`id`, `areaId`, wma.`mapId`,            `minY`,           `maxY`,          `maxX`,             `minX`,      `floor`,      `worldMapAreaId`, `defaultDungeonMapId` FROM ::worldmaparea wma
                 JOIN   ::dungeonmap dm ON dm.`mapId` = wma.`mapId` WHERE wma.`mapId` NOT IN (0, 1, 530, 571) OR wma.`areaId` = 4395) x
            LEFT JOIN
                ::dungeonmap useDM   ON useDM.`mapId`   = x.`mapId` AND useDM.`worldMapAreaId`   = x.`worldMapAreaId` AND useDM.`floor`   =  x.`floor` AND useDM.`worldMapAreaId`   > 0
            LEFT JOIN
                ::dungeonmap multiDM ON multiDM.`mapId` = x.`mapId` AND multiDM.`worldMapAreaId` = x.`worldMapAreaId` AND multiDM.`floor` <> x.`floor` AND multiDM.`worldMapAreaId` > 0
            WHERE
                x.`mapId` = %i AND x.`areaId` <> 0 AND
                x.`minX` <> 0 AND x.`maxX` <> 0 AND x.`minY` <> 0 AND x.`maxY` <> 0
            GROUP BY
                x.`id`, x.`areaId`',
            $mapId
        ) ?: [];
    }
}

?>
