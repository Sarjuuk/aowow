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
UPDATE dbc.worldmaparea a, world.aowow_zones z SET yMax = `left`, xMax = top, yMin = `right`, xMin = bottom WHERE a.areaId = z.id;

    LFG_TYPE_NONE                                = 0,      // Internal use only
    LFG_TYPE_DUNGEON                             = 1,
    LFG_TYPE_RAID                                = 2,
    LFG_TYPE_QUEST                               = 3,
    LFG_TYPE_ZONE                                = 4,
    LFG_TYPE_HEROIC                              = 5,
    LFG_TYPE_RANDOM                              = 6

CREATE TABLE `aowow_zones` (
    `id`          mediumint(8) UNSIGNED NOT NULL COMMENT 'Zone Id' ,
    `mapId`       mediumint(8) UNSIGNED NOT NULL COMMENT 'Map Identifier' ,
    `category`    smallint(6) NOT NULL ,
    `flags`       int(11) NOT NULL ,
    `faction`     tinyint(2) NOT NULL ,
    `expansion`   tinyint(2) NOT NULL ,
    `type`        tinyint(2) UNSIGNED NOT NULL ,
    `maxPlayer`   smallint(6) NOT NULL ,
    `levelReq`    smallint(6) NOT NULL ,
    `levelReqLFG` smallint(6) NOT NULL ,
    `levelHeroic` smallint(6) NOT NULL ,
    `levelMax`    smallint(6) NOT NULL ,
    `levelMin`    smallint(6) NOT NULL ,
    `name_loc0`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `name_loc2`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `name_loc3`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `name_loc6`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `name_loc8`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;

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

    public function addGlobalsToJScript($addMask = 0)
    {
        foreach ($this->iterate() as $__)
            Util::$pageTemplate->extendGlobalData(self::$type, [$this->id => ['name' => $this->getField('name', true)]]);
    }

    public function renderTooltip() { }
}

?>
