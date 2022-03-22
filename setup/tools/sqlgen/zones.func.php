<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'zones';

    protected $tblDependencyTC = ['access_requirement'];
    protected $dbcSourceFiles  = ['worldmaptransforms', 'worldmaparea', 'map', 'mapdifficulty', 'areatable', 'lfgdungeons', 'battlemasterlist', 'dungeonmap'];

    public function generate(array $ids = []) : bool
    {
        // base query
        $baseData = DB::Aowow()->query('SELECT
                    a.id,
                    IFNULL(wmt.targetMapId, m.id) AS map,
                    m.id AS mapBak,
                    a.areaTable AS parentArea,
                    IFNULL(wmt.targetMapId,
                        IF(m.areaType = 1, 2,
                            IF(m.areaType = 2, 3,
                                IF(m.areaType = 4, 9,
                                    IF(m.isBG = 1, 6,
                                        IF(m.id = 571, 10,
                                            IF(m.id = 530, 8, m.id))))))) AS category,
                    a.flags,
                    IF(a.mapId IN (13, 25, 37, 42, 169) OR
                        (a.mapId IN (0, 1, 530, 571) AND wma.id IS NULL) OR
                        a.areaTable <> 0 OR
                        (a.soundAmbience = 0 AND a.mapId IN (0, 1, 530, 571)), ?d, 0) AS cuFlags,
                    IF(a.flags & 0x01000000, 5,                 -- g_zone_territories
                        IF(m.isBG = 1, 4,
                            IF(m.areaType = 4, 4,
                                IF(a.flags & 0x00000800, 3,
                                    IF(a.factionGroupMask = 6, 2,
                                        IF(a.factionGroupMask > 0, LOG2(a.factionGroupMask) - 1, 2)))))) AS faction,
                    m.expansion,
                    IF(m.areaType = 0, 0,                       -- g_zone_instancetypes
                        IF(m.isBG = 1, 4,
                            IF(m.areaType = 4, 6,
                                IF(md.modeMask & 0xC, 8,
                                    IF(md.minPl = 10 AND md.maxPL = 25, 7,
                                        IF(m.areaType = 2, 3,
                                            IF(m.areaType = 1 AND md.modeMask & 0x2, 5, 2))))))) AS `type`,
                    IF (md.minPl = 10 AND md.maxPl = 25, -2,
                        IFNULL(bm.maxPlayers, IFNULL(md.maxPl, m.maxPlayers))) AS maxPlayer,
                    0 AS `itemLevelN`,                                          --
                    0 AS `itemLevelH`,
                    0 AS `levelReq`,
                    IFNULL(lfgIni.levelLFG, 0) AS `levelReqLFG`,
                    0 AS `levelHeroic`,
                    IF(a.flags & 0x8, 1,
                        IFNULL(bm.minLevel,
                            IFNULL(lfgIni.levelMin,
                                IFNULL(lfgOpen.levelMin, 0)))) AS `levelMin`,
                    IF(a.flags & 0x8, ?d,
                        IFNULL(bm.maxLevel,
                            IFNULL(lfgIni.levelMax,
                                IFNULL(lfgOpen.levelMax, 0)))) AS `levelMax`,
                    "" AS `attunementsN`,
                    "" AS `attunementsH`,
                    m.parentMapId, -- IFNULL(pa.areaId, 0),
                    m.parentX, -- IFNULL(pa.posX, 0),
                    m.parentY, -- IFNULL(pa.posY, 0),
                    IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc0, m.name_loc0),
                    IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc2, m.name_loc2),
                    IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc3, m.name_loc3),
                    IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc4, m.name_loc4),
                    IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc6, m.name_loc6),
                    IF(wma.id IS NULL OR m.areaType = 0 OR a.mapId IN (269, 560) OR a.areaTable, a.name_loc8, m.name_loc8)
                FROM
                    dbc_areatable a
                JOIN
                    dbc_map m ON m.id = IF(a.id = 2159, 249, a.mapId) -- Zone: Onyxias Lair is linked to the wrong map
                LEFT JOIN (
                    SELECT mapId, BIT_OR(1 << difficulty) AS modeMask, MIN(nPlayer) AS minPl, MAX(nPlayer) AS maxPl FROM dbc_mapdifficulty GROUP BY mapId
                ) md ON md.mapId = m.id
                LEFT JOIN
                    dbc_lfgdungeons lfgOpen ON a.mapId IN (0, 1, 530, 571) AND a.name_loc0 LIKE CONCAT("%", lfgOpen.name_loc0) AND lfgOpen.type = 4
                LEFT JOIN (
                    SELECT
                        mapId,
                        MIN(IF(targetLevelMin, targetLevelMin, levelMin))    AS levelMin,
                        MAX(IF(targetLevelMax, targetLevelMax, targetLevel)) AS levelMax,
                        MIN(IF(levelMin,       levelMin,       targetLevel)) AS levelLFG
                    FROM
                        dbc_lfgdungeons
                    WHERE
                        type NOT IN (4, 6) AND
                        groupId <> 11
                    GROUP BY
                        mapId
                ) lfgIni ON lfgIni.mapId = m.id
                LEFT JOIN
                    dbc_battlemasterlist bm ON bm.mapId = a.mapId AND bm.moreMapId < 0
                LEFT JOIN
                    dbc_worldmaparea wma ON wma.areaId = a.id
                LEFT JOIN
                    dbc_worldmaptransforms wmt ON
                        wmt.targetMapId <> wmt.sourceMapId AND
                        wma.mapId  = wmt.sourceMapId AND
                        wma.left   < wmt.maxY AND
                        wma.right  > wmt.minY AND
                        wma.top    < wmt.maxX AND
                        wma.bottom > wmt.minX
        ', CUSTOM_EXCLUDE_FOR_LISTVIEW, MAX_LEVEL);

        foreach ($baseData as &$bd)
        {
            if (in_array($bd['mapBak'], [0, 1, 530, 571]))
                continue;

            if ($gPos = Game::worldPosToZonePos($bd['parentMapId'], $bd['parentY'], $bd['parentX']))
            {
                $pos = Game::checkCoords($gPos);
                $bd['parentMapId'] = $pos['areaId'] ?? $gPos[0]['areaId'];
                $bd['parentX']     = $pos['posX']   ?? $gPos[0]['posX'];
                $bd['parentY']     = $pos['posY']   ?? $gPos[0]['posY'];
            }
            else
            {
                $bd['parentMapId'] = 0;
                $bd['parentX']     = 0;
                $bd['parentY']     = 0;
            }
        }

        DB::Aowow()->query('REPLACE INTO ?_zones VALUES (?a)', $baseData);

        // get requirements from world.access_requirement
        $zoneReq = DB::World()->select('
            SELECT
                mapId AS ARRAY_KEY,
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
            FROM
                access_requirement
            GROUP BY
                mapId
        ');

        $heroics = DB::Aowow()->selectCol('SELECT DISTINCT mapId FROM aowow_zones WHERE type IN (5, 8)');

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
