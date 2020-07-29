<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;

    protected $command = 'zones';

    protected $tblDependencyTC = ['access_requirement'];
    protected $dbcSourceFiles  = ['worldmaptransforms', 'worldmaparea', 'map', 'mapdifficulty', 'areatable', 'lfgdungeons', 'battlemasterlist'];

    private $customData = array(
        2257 => ['cuFlags' => 0, 'category' => 0, 'type' => 1], // deeprun tram => type: transit
        3698 => ['expansion' => 1],                         // arenas
        3702 => ['expansion' => 1],
        3968 => ['expansion' => 1],
        4378 => ['expansion' => 2],
        4406 => ['expansion' => 2],
        2597 => ['maxPlayer' => 40],                        // is 5 in battlemasterlist ... dafuq?
        4710 => ['maxPlayer' => 40],
        3456 => ['parentAreaId' => 65,   'parentX' => 87.3, 'parentY' => 51.1], // has no coordinates set in map.dbc
        // individual Tempest Keep ships
        3849 => ['parentAreaId' => 3523, 'parentX' => 70.5, 'parentY' => 69.6],
        3847 => ['parentAreaId' => 3523, 'parentX' => 71.7, 'parentY' => 55.1],
        3848 => ['parentAreaId' => 3523, 'parentX' => 74.3, 'parentY' => 57.8],
        3845 => ['parentAreaId' => 3523, 'parentX' => 73.5, 'parentY' => 63.7],
        // individual Icecrown Citadel wings
        4893 => ['parentAreaId' => 4812, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        4894 => ['parentAreaId' => 4812, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        4895 => ['parentAreaId' => 4812, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        4896 => ['parentAreaId' => 4812, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        4897 => ['parentAreaId' => 4812, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        // uncaught unused zones
        207  => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        208  => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        616  => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
        1417 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW]
    );

    public function generate(array $ids = []) : bool
    {
        // base query
        DB::Aowow()->query('
            REPLACE INTO ?_zones
                SELECT
                    a.id,
                    IFNULL(wmt.targetMapId, m.id),              -- map
                    m.id,                                       -- mapBak
                    a.areaTable,                                -- parentArea
                    IFNULL(wmt.targetMapId,                     -- g_zone_categories
                        IF(m.areaType = 1, 2,
                            IF(m.areaType = 2, 3,
                                IF(m.areaType = 4, 9,
                                    IF(m.isBG = 1, 6,
                                        IF(m.id = 609, 1,
                                            IF(m.id = 571, 10,
                                                IF(m.id = 530, 8, m.id)))))))),
                    a.flags,
                    IF(areaTable <> 0 OR                        -- cuFlags
                        (wma.id IS NULL AND pa.areaId IS NULL AND (flags & 0x11000) = 0), ?d, 0),
                    IF(a.flags & 0x01000000, 5,                 -- g_zone_territories
                        IF(m.isBG = 1, 4,
                            IF(m.areaType = 4, 4,
                                IF(a.flags & 0x00000800, 3,
                                    IF(a.factionGroupMask = 6, 2,
                                        IF(a.factionGroupMask > 0, LOG2(a.factionGroupMask) - 1, 2)))))),
                    m.expansion,
                    IF(m.areaType = 0, 0,                       -- g_zone_instancetypes
                        IF(m.isBG = 1, 4,
                            IF(m.areaType = 4, 6,
                                IF(md.modeMask & 0xC, 8,
                                    IF(md.minPl = 10 AND md.maxPL = 25, 7,
                                        IF(m.areaType = 2, 3,
                                            IF(m.areaType = 1 AND md.modeMask & 0x2, 5, 2))))))),
                    IF (md.minPl = 10 AND md.maxPl = 25, -2,
                        IFNULL(bm.maxPlayers, IFNULL(md.maxPl, m.maxPlayers))),
                    0,                                          -- itemLevelN
                    0,                                          -- itemLevelH
                    0,                                          -- levelReq
                    IFNULL(lfgIni.levelLFG, 0),                 -- levelReqLFG
                    0,                                          -- levelHeroic
                    IF(a.flags & 0x8, 1,                        -- levelMin
                        IFNULL(bm.minLevel,
                            IFNULL(lfgIni.levelMin,
                                IFNULL(lfgOpen.levelMin, 0)))),
                    IF(a.flags & 0x8, ?d,                       -- levelMax
                        IFNULL(bm.maxLevel,
                            IFNULL(lfgIni.levelMax,
                                IFNULL(lfgOpen.levelMax, 0)))),
                    "",                                         -- attunements
                    "",                                         -- heroic attunements
                    IFNULL(pa.areaId, 0),
                    IFNULL(pa.posX, 0),
                    IFNULL(pa.posY, 0),
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
                        mapId, m.id, `left`, `right`, `top`, `bottom`,
                        IF((abs(((m.parentY - `right`)  * 100 / (`left` - `right`)) - 50)) > abs(((m.parentX - `bottom`) * 100 / (`top` - `bottom`)) - 50),
                           (abs(((m.parentY - `right`)  * 100 / (`left` - `right`)) - 50)),
                           (abs(((m.parentX - `bottom`) * 100 / (`top` - `bottom`)) - 50))) AS diff,
                        areaId,                                                             -- parentArea
                        100 - ROUND((m.parentY - `right`)  * 100 / (`left` - `right`), 1) as posX,
                        100 - ROUND((m.parentX - `bottom`) * 100 / (`top` - `bottom`), 1) as posY
                    FROM
                        dbc_worldmaparea wma
                    JOIN
                        dbc_map m ON m.parentMapId = wma.mapid
                    WHERE
                        m.parentMapId IN (0, 1, 530, 571) AND areaId <> 0 AND
                        m.parentY BETWEEN `right` AND `left` AND
                        m.parentX BETWEEN bottom  AND top
                    ORDER BY
                        diff ASC
                ) pa ON pa.id = m.id AND m.parentMapId > -1 AND m.parentX <> 0 AND m.parentY <> 0 AND m.parentMapId = pa.mapId AND m.parentY BETWEEN pa.`right` AND pa.`left` AND m.parentX BETWEEN pa.bottom AND pa.top
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
                GROUP BY
                    a.id
        ', CUSTOM_EXCLUDE_FOR_LISTVIEW, MAX_LEVEL);

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
                    $aN[TYPE_ITEM][] = $_;

            if ($req['reqItemH'] && ($entries = explode(' ', $req['reqItemH'])))
                if ($entries = array_diff($entries, @(array)$aN[TYPE_ITEM]))
                    foreach ($entries as $_)
                        $aH[TYPE_ITEM][] = $_;

            if ($req['reqQuestN'] && ($entries = explode(' ', $req['reqQuestN'])))
                foreach ($entries as $_)
                    $aN[TYPE_QUEST][] = $_;

            if ($req['reqQuestH'] && ($entries = explode(' ', $req['reqQuestH'])))
                if ($entries = array_diff($entries, @(array)$aN[TYPE_QUEST]))
                    foreach ($entries as $_)
                        $aH[TYPE_QUEST][] = $_;

            if ($req['reqAchievementN'] && ($entries = explode(' ', $req['reqAchievementN'])))
                foreach ($entries as $_)
                    $aN[TYPE_ACHIEVEMENT][] = $_;

            if ($req['reqAchievementH'] && ($entries = explode(' ', $req['reqAchievementH'])))
                if ($entries = array_diff($entries, @(array)$aN[TYPE_ACHIEVEMENT]))
                    foreach ($entries as $_)
                        $aH[TYPE_ACHIEVEMENT][] = $_;

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

        return true;
    }
});

?>
