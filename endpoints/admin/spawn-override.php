<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminSpawnoverrideResponse extends TextResponse
{
    private const int ERR_NONE          = 0;
    private const int ERR_NO_POINTS     = 1;
    private const int ERR_WORLD_POS     = 2;
    private const int ERR_WRONG_TYPE    = 3;
    private const int ERR_WRITE_DB      = 4;
    private const int ERR_MISCELLANEOUS = 999;

    protected int   $requiredUserGroup = U_GROUP_MODERATOR;

    protected array $expectedGET       = array(
        'type'  => ['filter' => FILTER_VALIDATE_INT],
        'guid'  => ['filter' => FILTER_VALIDATE_INT],
        'area'  => ['filter' => FILTER_VALIDATE_INT],
        'floor' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('type', 'guid', 'area', 'floor'))
        {
            trigger_error('AdminSpawnoverrideResponse - malformed request received', E_USER_WARNING);
            $this->result = self::ERR_MISCELLANEOUS;
            return;
        }

        $guid  = $this->_get['guid'];
        $type  = $this->_get['type'];
        $area  = $this->_get['area'];
        $floor = $this->_get['floor'];

        if (!($tbl = WorldPosition::getStorageByType($type)))
        {
            trigger_error("AdminSpawnoverrideResponse - can't move pip of type ".Type::getFileString($type), E_USER_WARNING);
            $this->result = self::ERR_WRONG_TYPE;
            return;
        }

        if (!($wPos = WorldPosition::getForGUID($type, $guid)))
        {
            $this->result = self::ERR_WORLD_POS;
            return;
        }

        if (!($point = WorldPosition::toZonePos($wPos[$guid]['mapId'], $wPos[$guid]['mapX'], $wPos[$guid]['mapY'], $area, $floor)))
        {
            $this->result = self::ERR_NO_POINTS;
            return;
        }

        [$flags, $srcA, $srcX, $srcY, $srcF] = DB::Aowow()->selectRow('SELECT `flags` AS "0", `areaId` AS "1", `areaX` AS "2", `areaY` AS "3", `floor` AS "4" FROM %n WHERE `guid` = %i', $tbl, $guid) ?: null;
        if (($_ = $flags & 0xFFF00) || ($flags & SPAWNTYPE_VEHICLE_ACCESSORY))
        {
            trigger_error('AdminSpawnoverrideResponse - tried to move '.($_ ? 'transport passenger' : 'vehicle accessory').' (linked to parent spawn point) TYPE #'.$type.'; GUID #'.$guid, E_USER_WARNING);
            $this->result = self::ERR_WRONG_TYPE;
            return;
        }

        DB::Aowow()->qry('REPLACE INTO ::spawns_override %v', ['type' => $type, 'typeGuid' => $guid, 'areaId' => $area, 'floor' => $floor, 'revision' => AOWOW_REVISION]);

        $newPos   = array(
            'areaX'  => $point[0]['posX'],
            'areaY'  => $point[0]['posY'],
            'areaId' => $point[0]['areaId'],
            'floor'  => $point[0]['floor']
        );

        if (!DB::Aowow()->qry('UPDATE %n SET %a WHERE `guid` = %i', $tbl, $newPos, $guid))
        {
            $this->result = self::ERR_WRITE_DB;
            return;
        }

        // move passengers and waypoints with transport
        if ($type == Type::OBJECT && ($flags & SPAWNTYPE_MO_TRANSPORT))
        {
            $mapId  = DB::Aowow()->SelectCell('SELECT SUBSTRING_INDEX(`miscInfo`, " ", 1) FROM ::objects o JOIN ::objects_spawns s ON s.`typeId` = o.`id` WHERE s.`guid` = %i', $guid);
            $spawns = DB::Aowow()->selectAssoc(
               'SELECT `guid`, `mapId`, `mapX`, `mapY`, %i AS "type" FROM ::creature_spawns WHERE (`flags` & %i) = %i UNION
                SELECT `guid`, `mapId`, `mapX`, `mapY`, %i AS "type" FROM ::objects_spawns  WHERE (`flags` & %i) = %i',
                Type::NPC, $mapId << 8, $mapId << 8, Type::OBJECT, $mapId << 8, $mapId << 8
            );
            $this->updatePoints(false, $spawns, $area, $floor);

            if ($wps = DB::Aowow()->selectAssoc('SELECT w.`pathId`, w.`point`, w.`mapId`, w.`mapX`, w.`mapY`, %i AS "type" FROM ::objects_waypoints w JOIN ::objects_spawns o ON o.pathId = w.pathId WHERE o.`guid` = %i', Type::OBJECT, $guid))
                $this->updatePoints(true, $wps, $area, $floor);
        }

        // if creature try for waypoints
        if ($type == Type::NPC)
        {
            if ($wps = DB::Aowow()->selectAssoc('SELECT w.`pathId`, w.`point`, w.`mapId`, w.`mapX`, w.`mapY`, %i AS "type" FROM ::creature_waypoints w JOIN ::creature_spawns c ON c.pathId = w.pathId WHERE c.`guid` = %i', Type::NPC, $guid))
                $this->updatePoints(true, $wps, $area, $floor);

            // also move linked vehicle accessories (on the very same position)
            DB::Aowow()->qry('UPDATE %n SET %a WHERE (`flags` & %i) AND `areaId` = %i AND CAST(`areaX` * 10 AS SIGNED) = %i AND CAST(`areaY` * 10 AS SIGNED) = %i AND `floor` = %i',
                $tbl, $newPos, SPAWNTYPE_VEHICLE_ACCESSORY, $srcA, $srcX * 10, $srcY * 10, $srcF);
        }

        $this->result = self::ERR_NONE;
    }

    private function updatePoints(bool $isWP, array $points, int $area, int $floor) : void
    {
        foreach ($points as $p)
        {
            if ($point = WorldPosition::toZonePos($p['mapId'], $p['mapX'], $p['mapY'], $area, $floor))
            {
                $new = array(
                    'areaX'  => $point[0]['posX'],
                    'areaY'  => $point[0]['posY'],
                    'areaId' => $point[0]['areaId'],
                    'floor'  => $point[0]['floor']
                );

                if ($isWP)
                    DB::Aowow()->qry('UPDATE %n SET %a WHERE `pathId` = %i AND `point` = %i', $p['type'] == Type::NPC ? '::creature_waypoints' : '::objects_waypoints', $new, $p['pathId'], $p['point']);
                else
                    DB::Aowow()->qry('UPDATE %n SET %a WHERE `guid` = %i', $p['type'] == Type::NPC ? '::creature_spawns' : '::objects_spawns', $new, $p['guid']);
            }
        }
    }
}

?>
