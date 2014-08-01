<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    /*
        areatable: discarded Ids
        unused: 276, 296, 1579, 2280, 3459, 3817, 208, 4076
    */

class ZoneList extends BaseType
{
    public static $type      = TYPE_ZONE;
    public static $brickFile = 'zone';

    protected     $queryBase = 'SELECT *, id AS ARRAY_KEY FROM ?_zones z';

    public function getListviewData()
    {
        $data = [];
/*
UPDATE dbc.worldmaparea a, world.?_zones z SET yMax = `left`, xMax = top, yMin = `right`, xMin = bottom WHERE a.areaId = z.id;

    LFG_TYPE_NONE                                = 0,      // Internal use only
    LFG_TYPE_DUNGEON                             = 1,
    LFG_TYPE_RAID                                = 2,
    LFG_TYPE_QUEST                               = 3,
    LFG_TYPE_ZONE                                = 4,
    LFG_TYPE_HEROIC                              = 5,
    LFG_TYPE_RANDOM                              = 6

CREATE TABLE `aowow_zones` (
	`id` MEDIUMINT(8) UNSIGNED NOT NULL COMMENT 'Zone Id',
	`mapId` MEDIUMINT(8) UNSIGNED NOT NULL COMMENT 'Map Identifier',
	`mapIdBak` MEDIUMINT(8) UNSIGNED NOT NULL,
	`parentArea` MEDIUMINT(8) UNSIGNED NOT NULL,
	`category` SMALLINT(6) NOT NULL,
	`flags` INT(11) NOT NULL,
	`cuFlags` INT(10) UNSIGNED NOT NULL,
	`faction` TINYINT(2) NOT NULL,
	`expansion` TINYINT(2) NOT NULL,
	`type` TINYINT(2) UNSIGNED NOT NULL,
	`areaType` TINYINT(2) UNSIGNED NOT NULL,
	`xMin` FLOAT NOT NULL,
	`xMax` FLOAT NOT NULL,
	`yMin` FLOAT NOT NULL,
	`yMax` FLOAT NOT NULL,
	`maxPlayer` SMALLINT(6) NOT NULL,
	`levelReq` SMALLINT(6) NOT NULL,
	`levelReqLFG` SMALLINT(6) NOT NULL,
	`levelHeroic` SMALLINT(6) NOT NULL,
	`levelMax` SMALLINT(6) NOT NULL,
	`levelMin` SMALLINT(6) NOT NULL,
	`name_loc0` VARCHAR(255) NOT NULL COMMENT 'Map Name',
	`name_loc2` VARCHAR(255) NOT NULL,
	`name_loc3` VARCHAR(255) NOT NULL,
	`name_loc6` VARCHAR(255) NOT NULL,
	`name_loc8` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM;

var g_zone_categories = {
	0: "Eastern Kingdoms",                                      // areaTable.map == 0 AND
	1: "Kalimdor",                                              // areaTable.map == 1 AND
	2: "Dungeons",                                              // map.areaType == 1
	3: "Raids",                                                 // map.areaType == 2
	6: "Battlegrounds",                                         // map.areaType == 3
	8: "Outland",                                               // areaTable.map == 530 AND
	9: "Arenas"                                                 // map.areaType == 4
	10: "Northrend",                                            // areaTable.map == 571 AND
};

var g_zone_instancetypes = {
	1: "Transit",                                               // [manual]
	2: "Dungeon",
	3: "Raid",         // Classic
	4: "Battleground",                                          // map.isBattleground
	5: "Dungeon",      // Heroic
	6: "Arena",                                                 // map.areaType == 4
	7: "Raid",         // 10-25
	8: "Raid"          // 10-25 Heroic
};

var g_zone_territories = {
    0: "Alliance",                                              // areaTable.factionGroupMask == 2
    1: "Horde",                                                 // areaTable.factionGroupMask == 4
    2: "Contested",                                             // areaTable.factionGroupMask == 6
    3: "Sanctuary",                                             // areaTable.flags & AREA_FLAG_SANCTUARY
    4: "PvP",                                                   // map.areaType IN [3, 4]
	5: "World PvP"                                              // areaTable.flags & AREA_FLAG_WINTERGRASP
};

visibleCols: ['heroiclevel', 'players']

    "id":5004,                                                  // areaTable.Id
    "category":2,                                               // s.o
    "expansion":3,                                              // lfgDungeons.expansion || map.expansion
    "territory":2,                                              // s.o.
    "instance":5,                                               // s.o.
    "nplayers":5,                                               // map.maxPlayers
    "reqlevel":77,                                              // access_requirement.level_min
    "heroicLevel":85,                                           // access_requirement.level_min
    "lfgReqLevel":80,                                           // lfgDungeons.targetLevel
    "maxlevel":82,                                              // lfgDungeons.levelMin
    "minlevel":80,                                              // lfgDungeons.levelMax
    "name":"Abyssal Maw: Throne of the Tides",                  // areaTable.name_X
*/
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'        => $this->id,
                'category'  => $this->curTpl['category'],
                'territory' => $this->curTpl['faction'],
                'minlevel'  => $this->curTpl['levelMin'],
                'maxlevel'  => $this->curTpl['levelMax'],
                'name'      => $this->getField('name', true)
            );

            if ($_ = $this->curTpl['expansion'])
                $data[$this->id]['expansion'] = $_;

            if ($_ = $this->curTpl['type'])
                $data[$this->id]['instance'] = $_;

            if ($_ = $this->curTpl['maxPlayer'])
                $data[$this->id]['nplayers'] = $_;

            if ($_ = $this->curTpl['levelReq'])
                $data[$this->id]['reqlevel'] = $_;

            if ($_ = $this->curTpl['levelReqLFG'])
                $data[$this->id]['lfgReqLevel'] = $_;

            if ($_ = $this->curTpl['levelHeroic'])
                $data[$this->id]['heroicLevel'] = $_;
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[TYPE_ZONE][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() { }
}

?>
