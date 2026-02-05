<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class WorldPosition
{
    private static array $zoneMapCache  = [];
    private static array $alphaMapCache = [];
    private static array $capitalCities = array(        // capitals take precedence over their surrounding area
        1497, 1637, 1638, 3487,                         // Undercity,      Ogrimmar,  Thunder Bluff, Silvermoon City
        1519, 1537, 1657, 3557,                         // Stormwind City, Ironforge, Darnassus,     The Exodar
        3703, 4395                                      // Shattrath City, Dalaran
    );

    private static function alphaMapCheck(int $areaId, array &$set) : bool
    {
        $file = 'cache/alphaMaps/'.$areaId.'.png';
        if (!file_exists($file))                            // file does not exist (probably instanced area)
            return false;

        // invalid and corner cases (literally)
        if (empty($set['posX']) || empty($set['posY']) || $set['posX'] >= 100 || $set['posY'] >= 100)
        {
            $set = null;
            return true;
        }

        if (empty(self::$alphaMapCache[$areaId]))
            self::$alphaMapCache[$areaId] = imagecreatefrompng($file);

        // alphaMaps are 1000 x 1000, adapt points [black => valid point]
        if (!imagecolorat(self::$alphaMapCache[$areaId], $set['posX'] * 10, $set['posY'] * 10))
            $set = null;

        return true;
    }

    public static function checkZonePos(array $points) : array
    {
        $result   = [];

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
            else if (in_array($res['areaId'], self::$capitalCities))
                return $res;
            // add with lowest quality if alpha map is missing
            else if (empty($result))
                $result = [1.0, $res];
        }

        // spawn does not really match on a map, but we need at least one result
        if (!$result)
        {
            usort($points, fn ($a, $b) => $a['dist'] <=> $b['dist']);
            $result = [1.0, $points[0]];
        }

        return $result[1];
    }

    public static function getForGUID(int $type, int ...$guids) : array
    {
        $result = [];

        switch ($type)
        {
            case Type::NPC:
                $result = DB::World()->select('SELECT `guid` AS ARRAY_KEY,              `id`, `map`         AS `mapId`, `position_x` AS `posX`, `position_y` AS `posY` FROM creature        WHERE `guid` IN (?a)', $guids);
                break;
            case Type::OBJECT:
                $result = DB::World()->select('SELECT `guid` AS ARRAY_KEY,              `id`, `map`         AS `mapId`, `position_x` AS `posX`, `position_y` AS `posY` FROM gameobject      WHERE `guid` IN (?a)', $guids);
                break;
            case Type::SOUND:
                $result = DB::AoWoW()->select('SELECT `id`   AS ARRAY_KEY, `soundId` AS `id`,                  `mapId`,                 `posX`,                 `posY` FROM ?_soundemitters WHERE `id`   IN (?a)', $guids);
                break;
            case Type::ZONE:
                $result = DB::Aowow()->select('SELECT -`id`  AS ARRAY_KEY,              `id`, `parentMapId` AS `mapId`, `parentX`    AS `posX`, `parentY`    AS `posY` FROM ?_zones         WHERE -`id`  IN (?a)', $guids);
                break;
            case Type::AREATRIGGER:
                $result = [];
                if ($base = array_filter($guids, fn($x) => $x > 0))
                    $result = array_replace($result, DB::AoWoW()->select('SELECT `id`   AS ARRAY_KEY, `id`,    `mapId`,                 `posX`,                 `posY` FROM ?_areatrigger   WHERE `id`   IN (?a)', $base));
                if ($endpoints = array_filter($guids, fn($x) => $x < 0))
                    $result = array_replace($result, DB::World()->select(
                       'SELECT -`ID`          AS ARRAY_KEY, ID          AS `id`,    `target_map` AS `mapId`, `target_position_x` AS `posX`, `target_position_y` AS `posY` FROM areatrigger_teleport WHERE -`id`          IN (?a) UNION
                        SELECT -`entryorguid` AS ARRAY_KEY, entryorguid AS `id`, `action_param1` AS `mapId`, `target_x`          AS `posX`, `target_y`          AS `posY` FROM smart_scripts        WHERE -`entryorguid` IN (?a) AND `source_type` = ?d AND `action_type` = ?d',
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

    public static function toZonePos(int $mapId, float $mapX, float $mapY, int $preferedAreaId = 0, int $preferedFloor = -1) : array
    {
        if (!$mapId < 0)
            return [];

        if (!isset(self::$zoneMapCache[$mapId]))
            self::initZoneMaps($mapId);

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
        usort($points, fn ($a, $b) => ($b['srcPrio'] <=> $a['srcPrio']) ?: ($a['dist'] <=> $b['dist']));

        return $points;
    }

    private static function initZoneMaps(int $mapId) : void
    {
        self::$zoneMapCache[$mapId] = DB::Aowow()->select(
           'SELECT
                x.`id`,
                x.`areaId`,
                x.`minX`, x.`maxX`, x.`minY`, x.`maxY`,
                IF(x.`defaultDungeonMapId` < 0, x.`floor` + 1, x.`floor`) AS `floor`,
                IF(useDM.`id`   IS NOT NULL OR x.`defaultDungeonMapId` < 0, 1, 0) AS `srcPrio`,
                IF(multiDM.`id` IS NOT NULL OR x.`defaultDungeonMapId` < 0, 1, 0) AS `multifloor`
            FROM
                (SELECT 0 AS `id`, `areaId`,     `mapId`, `right` AS `minY`, `left` AS `maxY`, `top` AS `maxX`, `bottom` AS `minX`, 0 AS `floor`, 0 AS `worldMapAreaId`, `defaultDungeonMapId` FROM ?_worldmaparea wma UNION
                 SELECT   dm.`id`, `areaId`, wma.`mapId`,            `minY`,           `maxY`,          `maxX`,             `minX`,      `floor`,      `worldMapAreaId`, `defaultDungeonMapId` FROM ?_worldmaparea wma
                 JOIN   ?_dungeonmap dm ON dm.`mapId` = wma.`mapId` WHERE wma.`mapId` NOT IN (0, 1, 530, 571) OR wma.`areaId` = 4395) x
            LEFT JOIN
                ?_dungeonmap useDM   ON useDM.`mapId`   = x.`mapId` AND useDM.`worldMapAreaId`   = x.`worldMapAreaId` AND useDM.`floor`   =  x.`floor` AND useDM.`worldMapAreaId`   > 0
            LEFT JOIN
                ?_dungeonmap multiDM ON multiDM.`mapId` = x.`mapId` AND multiDM.`worldMapAreaId` = x.`worldMapAreaId` AND multiDM.`floor` <> x.`floor` AND multiDM.`worldMapAreaId` > 0
            WHERE
                x.`mapId` = ?d AND x.`areaId` <> 0 AND
                x.`minX` <> 0 AND x.`maxX` <> 0 AND x.`minY` <> 0 AND x.`maxY`
            GROUP BY
                x.`id`, x.`areaId`',
            $mapId
        ) ?: [];
    }
}

?>
