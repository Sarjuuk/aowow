<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminSpawnoverrideResponse extends TextResponse
{
    private const /* int */ ERR_NONE          = 0;
    private const /* int */ ERR_NO_POINTS     = 1;
    private const /* int */ ERR_WORLD_POS     = 2;
    private const /* int */ ERR_WRONG_TYPE    = 3;
    private const /* int */ ERR_WRITE_DB      = 4;
    private const /* int */ ERR_MISCELLANEOUS = 999;

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
            trigger_error('AdminSpawnoverrideResponse - malformed request received', E_USER_ERROR);
            $this->result = self::ERR_MISCELLANEOUS;
            return;
        }

        $guid  = $this->_get['guid'];
        $type  = $this->_get['type'];
        $area  = $this->_get['area'];
        $floor = $this->_get['floor'];

        if (!in_array($type, [Type::NPC, Type::OBJECT, Type::SOUND, Type::AREATRIGGER, Type::ZONE]))
        {
            trigger_error('AdminSpawnoverrideResponse - can\'t move pip of type '.Type::getFileString($type), E_USER_ERROR);
            $this->result = self::ERR_WRONG_TYPE;
            return;
        }

        DB::Aowow()->query('REPLACE INTO ?_spawns_override (`type`, `typeGuid`, `areaId`, `floor`, `revision`) VALUES (?d, ?d, ?d, ?d, ?d)', $type, $guid, $area, $floor, AOWOW_REVISION);

        $wPos = WorldPosition::getForGUID($type, $guid);
        if (!$wPos)
        {
            $this->result = self::ERR_WORLD_POS;
            return;
        }

        $point = WorldPosition::toZonePos($wPos[$guid]['mapId'], $wPos[$guid]['posX'], $wPos[$guid]['posY'], $area, $floor);
        if (!$point)
        {
            $this->result = self::ERR_NO_POINTS;
            return;
        }

        $updGUIDs = [$guid];
        $newPos   = array(
            'posX'   => $point[0]['posX'],
            'posY'   => $point[0]['posY'],
            'areaId' => $point[0]['areaId'],
            'floor'  => $point[0]['floor']
        );

        // if creature try for waypoints
        if ($type == Type::NPC)
        {
            if ($swp = DB::World()->select('SELECT -w.`id` AS "entry", w.`point` AS "pointId", w.`position_x` AS "posX", w.`position_y` AS "posY" FROM creature_addon ca JOIN waypoint_data w ON w.`id` = ca.`path_id` WHERE ca.`guid` = ?d AND ca.`path_id` <> 0', $guid))
            {
                foreach ($swp as $w)
                {
                    if ($point = WorldPosition::toZonePos($wPos[$guid]['mapId'], $w['posX'], $w['posY'], $area, $floor))
                    {
                        $p = array(
                            'posX'   => $point[0]['posX'],
                            'posY'   => $point[0]['posY'],
                            'areaId' => $point[0]['areaId'],
                            'floor'  => $point[0]['floor']
                        );

                        DB::Aowow()->query('UPDATE ?_creature_waypoints SET ?a WHERE `creatureOrPath` = ?d AND `point` = ?d', $p, $w['entry'], $w['pointId']);
                    }
                }
            }

            // also move linked vehicle accessories (on the very same position)
            $updGUIDs = array_merge($updGUIDs, DB::Aowow()->selectCol('SELECT s2.`guid` FROM ?_spawns s1 JOIN ?_spawns s2 ON s1.`posX` = s2.`posX` AND s1.`posY` = s2.`posY` AND
                s1.`areaId` = s2.`areaId` AND s1.`floor` = s2.`floor` AND s2.`guid` < 0 WHERE s1.`guid` = ?d', $guid));
        }

        if (DB::Aowow()->query('UPDATE ?_spawns SET ?a WHERE `type` = ?d AND `guid` IN (?a)', $newPos, $type, $updGUIDs))
            $this->result = self::ERR_NONE;
        else
            $this->result = self::ERR_WRITE_DB;
    }
}

?>
