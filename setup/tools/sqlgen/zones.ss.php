<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB
/* todo: fix custom data for naxx
    | zones   |  3456 | parentAreaId | 65         | Naxxramas -  Parent: Netherstorm [not set in map.dbc]          |
    | zones   |  3456 | parentX      | 87.3       | Naxxramas - Entrance xPos                                      |
    | zones   |  3456 | parentY      | 87.3       | Naxxramas - Entrance yPos                                      |
*/

    protected $info = array(
        'zones' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Zone from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['worldmaptransforms', 'worldmaparea', 'map', 'mapdifficulty', 'areatable', 'lfgdungeons', 'battlemasterlist', 'areatrigger'];
    protected $worldDependency = ['access_requirement', 'areatrigger_teleport'];
    protected $setupAfter      = [['dungeonmap', 'worldmaparea'], []];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_zones');

        $baseData = DB::Aowow()->query(
           'SELECT    a.id,
                      IFNULL(wmt.targetMapId, m.id) AS map,
                      m.id AS mapBak,
                      a.areaTable AS parentArea,
                      IFNULL(wmt.targetMapId, IF(m.areaType = 1, 2, IF(m.areaType = 2, 3, IF(m.areaType = 4, 9, IF(m.isBG = 1, 6, IF(m.id = 571, 10, IF(m.id = 530, 8, m.id))))))) AS category,
                      a.flags,
                      IF(a.mapId IN (13, 25, 37, 42, 169) OR (a.mapId IN (0, 1, 530, 571) AND wma.id IS NULL) OR a.areaTable <> 0 OR (a.soundAmbience = 0 AND a.mapId IN (0, 1, 530, 571)), ?d, 0) AS cuFlags,
                      IF(a.flags & 0x01000000, 5, IF(m.isBG = 1, 4, IF(m.areaType = 4, 4, IF(a.flags & 0x00000800, 3, IF(a.factionGroupMask = 6, 2, IF(a.factionGroupMask > 0, LOG2(a.factionGroupMask) - 1, 2)))))) AS faction, -- g_zone_territories
                      m.expansion,
                      IF(m.areaType = 0, 0, IF(m.isBG = 1, 4, IF(m.areaType = 4, 6, IF(md.modeMask & 0xC, 8, IF(md.minPl = 10 AND md.maxPL = 25, 7, IF(m.areaType = 2, 3, IF(m.areaType = 1 AND md.modeMask & 0x2, 5, 2))))))) AS `type`, -- g_zone_instancetypes
                      IF (md.minPl = 10 AND md.maxPl = 25, -2, IFNULL(bm.maxPlayers, IFNULL(md.maxPl, m.maxPlayers))) AS maxPlayer,
                      0 AS `itemLevelN`,
                      0 AS `itemLevelH`,
                      0 AS `levelReq`,
                      IFNULL(lfgIni.levelLFG, 0) AS `levelReqLFG`,
                      0 AS `levelHeroic`,
                      IF(a.flags & 0x8,  1, IFNULL(bm.minLevel, IFNULL(lfgIni.levelMin, IFNULL(lfgOpen.levelMin, 0)))) AS `levelMin`,
                      IF(a.flags & 0x8, ?d, IFNULL(bm.maxLevel, IFNULL(lfgIni.levelMax, IFNULL(lfgOpen.levelMax, 0)))) AS `levelMax`,
                      "" AS `attunementsN`,
                      "" AS `attunementsH`,
                      GREATEST(m.parentMapId, 0),
                      m.parentX,
                      m.parentY,
                      IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc0, m.name_loc0),
                      IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc2, m.name_loc2),
                      IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc3, m.name_loc3),
                      IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc4, m.name_loc4),
                      IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc6, m.name_loc6),
                      IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc8, m.name_loc8)
            FROM      dbc_areatable a
            JOIN      dbc_map m ON m.id = IF(a.id = 2159, 249, a.mapId) -- Zone: Onyxias Lair is linked to the wrong map
            LEFT JOIN (SELECT mapId, BIT_OR(1 << difficulty) AS modeMask, MIN(nPlayer) AS minPl, MAX(nPlayer) AS maxPl FROM dbc_mapdifficulty GROUP BY mapId) md ON md.mapId = m.id
            LEFT JOIN dbc_lfgdungeons lfgOpen ON a.mapId IN (0, 1, 530, 571) AND a.name_loc0 LIKE CONCAT("%", lfgOpen.name_loc0) AND lfgOpen.type = 4
            LEFT JOIN (SELECT   mapId,
                                MIN(IF(targetLevelMin, targetLevelMin, levelMin))    AS levelMin,
                                MAX(IF(targetLevelMax, targetLevelMax, targetLevel)) AS levelMax,
                                MIN(IF(levelMin,       levelMin,       targetLevel)) AS levelLFG
                       FROM     dbc_lfgdungeons
                       WHERE    type NOT IN (4, 6) AND groupId <> 11
                       GROUP BY mapId) lfgIni ON lfgIni.mapId = m.id
            LEFT JOIN dbc_battlemasterlist bm ON bm.mapId = a.mapId AND bm.moreMapId < 0
            LEFT JOIN dbc_worldmaparea wma ON wma.areaId = a.id
            LEFT JOIN dbc_worldmaptransforms wmt ON wmt.targetMapId <> wmt.sourceMapId AND wma.mapId  = wmt.sourceMapId AND
                      wma.left   < wmt.maxY AND wma.right  > wmt.minY AND
                      wma.top    < wmt.maxX AND wma.bottom > wmt.minX',
            CUSTOM_EXCLUDE_FOR_LISTVIEW, MAX_LEVEL
        );

        DB::Aowow()->query('INSERT INTO ?_zones VALUES (?a)', $baseData);


        // set missing graveyards from areatrigger data (auto-resurrect map or just plain errors)
        // grouped because naxxramas _just has_ to be special with 4 entrances...
        if ($missingMaps = DB::Aowow()->selectCol('SELECT `id` FROM dbc_map WHERE `parentX` = 0 AND `parentY` = 0 AND `parentMapId` > -1 AND `areaType` NOT IN (0, 3, 4)'))
            if ($triggerIds = DB::World()->selectCol('SELECT `target_map`, `id` AS ARRAY_KEY FROM areatrigger_teleport WHERE `target_map` IN (?a) GROUP BY `target_map`', $missingMaps))
                if ($positions = DB::Aowow()->select('SELECT `id` AS `ARRAY_KEY`, `mapId` AS "parentMapId", `posX` AS "parentX", `posY` AS "parentY" FROM dbc_areatrigger WHERE `id` IN (?a)', array_keys($triggerIds)))
                    foreach ($positions as $atId => $parentPos)
                        DB::Aowow()->query('UPDATE ?_zones SET ?a WHERE `mapId` = ?d', $parentPos, $triggerIds[$atId]);


        // get requirements from world.access_requirement
        $zoneReq = DB::World()->select(
           'SELECT   mapId AS ARRAY_KEY,
                     MIN(level_min) AS reqLevel,
                     MAX(IF(difficulty > 0, level_min,  0)) AS heroicLevel,
                     MAX(IF(difficulty = 0, item_level, 0)) AS reqItemLevelN,
                     MAX(IF(difficulty > 0, item_level, 0)) AS reqItemLevelH,
                     CONCAT_WS(" ", GROUP_CONCAT(IF(difficulty = 0 AND item, item, NULL) SEPARATOR " "), GROUP_CONCAT(IF(difficulty = 0 AND item2 AND item2 <> item, item2, NULL) SEPARATOR " ")) AS reqItemN,
                     CONCAT_WS(" ", GROUP_CONCAT(IF(difficulty > 0 AND item, item, NULL) SEPARATOR " "), GROUP_CONCAT(IF(difficulty > 0 AND item2 AND item2 <> item, item2, NULL) SEPARATOR " ")) AS reqItemH,
                     CONCAT_WS(" ", GROUP_CONCAT(IF(difficulty = 0 AND quest_done_A, quest_done_A, NULL) SEPARATOR " "), GROUP_CONCAT(IF(difficulty = 0 AND quest_done_H AND quest_done_H <> quest_done_A, quest_done_H, NULL) SEPARATOR " ")) AS reqQuestN,
                     CONCAT_WS(" ", GROUP_CONCAT(IF(difficulty > 0 AND quest_done_A, quest_done_A, NULL) SEPARATOR " "), GROUP_CONCAT(IF(difficulty > 0 AND quest_done_H AND quest_done_H <> quest_done_A, quest_done_H, NULL) SEPARATOR " ")) AS reqQuestH,
                     CONCAT_WS(" ", GROUP_CONCAT(IF(difficulty = 0 AND completed_achievement, completed_achievement, NULL) SEPARATOR " ")) AS reqAchievementN,
                     CONCAT_WS(" ", GROUP_CONCAT(IF(difficulty > 0 AND completed_achievement, completed_achievement, NULL) SEPARATOR " ")) AS reqAchievementH
            FROM     access_requirement
            GROUP BY mapId'
        );

        $heroics = DB::Aowow()->selectCol('SELECT DISTINCT mapId FROM ?_zones WHERE type IN (5, 8)');

        foreach ($zoneReq as $mapId => $req)
        {
            $update   = ['levelReq' => $req['reqLevel']];
            $aN = $aH = [];

            if ($req['heroicLevel'] && in_array($mapId, $heroics))
                $update['levelHeroic'] = $req['heroicLevel'];

            if ($req['reqItemLevelN'])
                $update['itemLevelReqN'] = $req['reqItemLevelN'];

            if ($req['reqItemLevelH'] && $req['reqItemLevelH'] > $req['reqItemLevelN'])
                $update['itemLevelReqH'] = $req['reqItemLevelH'];

            if ($req['reqItemN'] && ($entries = explode(' ', $req['reqItemN'])))
                foreach ($entries as $_)
                    $aN[Type::ITEM][] = $_;

            if ($req['reqItemH'] && ($entries = explode(' ', $req['reqItemH'])))
                if ($entries = array_diff($entries, $aN[Type::ITEM] ?? []))
                    foreach ($entries as $_)
                        $aH[Type::ITEM][] = $_;

            if ($req['reqQuestN'] && ($entries = explode(' ', $req['reqQuestN'])))
                foreach ($entries as $_)
                    $aN[Type::QUEST][] = $_;

            if ($req['reqQuestH'] && ($entries = explode(' ', $req['reqQuestH'])))
                if ($entries = array_diff($entries, $aN[Type::QUEST] ?? []))
                    foreach ($entries as $_)
                        $aH[Type::QUEST][] = $_;

            if ($req['reqAchievementN'] && ($entries = explode(' ', $req['reqAchievementN'])))
                foreach ($entries as $_)
                    $aN[Type::ACHIEVEMENT][] = $_;

            if ($req['reqAchievementH'] && ($entries = explode(' ', $req['reqAchievementH'])))
                if ($entries = array_diff($entries, $aN[Type::ACHIEVEMENT] ?? []))
                    foreach ($entries as $_)
                        $aH[Type::ACHIEVEMENT][] = $_;

            if ($aN)
            {
                foreach ($aN as $type => $entries)
                    $aN[$type] = $type.':'.implode(' '.$type.':', $entries);

                $update['attunementsN'] = implode(' ', $aN);
            }

            if ($aH)
            {
                foreach ($aH as $type => $entries)
                    $aH[$type] = $type.':'.implode(' '.$type.':', $entries);

                $update['attunementsH'] = implode(' ', $aH);
            }

            DB::Aowow()->query('UPDATE ?_zones SET ?a WHERE mapId = ?d', $update, $mapId);
        }

        $this->reapplyCCFlags('zones', Type::ZONE);

        return true;
    }
});

?>
