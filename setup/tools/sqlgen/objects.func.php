<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * gameobject_template
 * locales_gameobject
*/


$customData = array(
);
$reqDBC = ['lock'];

function objects(array $ids = [])
{
    $baseQuery = '
        SELECT
            go.entry,
            `type`,
            IF(`type` = 2, -2,                                                                  -- quests 1
                IF(`type` = 8 AND data0 IN (1, 2, 3, 4, 1552), -6,                              -- tools
                IF(`type` = 3 AND questitem1 <> 0, -2,                                          -- quests 2
                IF(`type` IN (3, 9, 25), `type`, 0)))),                                         -- regular chests, books, pools
            0 AS event,                                                                         -- linked worldevent
            displayId,
            name, name_loc2, name_loc3, name_loc6, name_loc8,
            faction,
            flags,
            0 AS cuFlags,                                                                       -- custom Flags
            questItem1, questItem2, questItem3, questItem4, questItem5, questItem6,
            IF(`type` IN (3, 25), data1, 0),                                                    -- lootId
            IF(`type` IN (2, 3, 6, 10, 13, 24, 26), data0, IF(`type` IN (0, 1), data1, 0)),     -- lockId
            0 AS reqSkill,                                                                      -- reqSkill
            IF(`type` = 9, data0, IF(`type` = 10, data7, 0)),                                   -- pageTextId
            IF(`type` = 1, data3,                                                               -- linkedTrapIds
                IF(`type` = 3, data7,
                    IF(`type` = 10, data12,
                        IF(`type` = 8, data2, 0)))),
            IF(`type` = 5, data5,                                                               -- reqQuest
                IF(`type` = 3, data8,
                    IF(`type` = 10, data1,
                        IF(`type` = 8, data4, 0)))),
            IF(`type` = 8, data0, 0),                                                           -- spellFocusId
            IF(`type` = 10, data10,                                                             -- onUseSpell
                IF(`type` IN (18, 24), data1,
                    IF(`type` = 26, data2,
                        IF(`type` = 22, data0, 0)))),
            IF(`type` = 18, data4, 0),                                                          -- onSuccessSpell
            IF(`type` = 18, data2, IF(`type` = 24, data3, 0)),                                  -- auraSpell
            IF(`type` = 30, data2, IF(`type` = 24, data4, IF(`type` = 6, data3, 0))),           -- triggeredSpell
            IF(`type` = 29, CONCAT_WS(" ", data14, data15, data16, data17, data0),              -- miscInfo: capturePoint
                IF(`type` =  3, CONCAT_WS(" ", data4, data5, data2),                            -- miscInfo: loot v
                    IF(`type` = 25, CONCAT_WS(" ", data2, data3, 0),
                        IF(`type` = 23, CONCAT_WS(" ", data0, data1, data2), "")))),            -- miscInfo: meetingStone
            IF(ScriptName <> "", ScriptName, AIName)
        FROM
            gameobject_template go
        LEFT JOIN
            locales_gameobject lgo ON go.entry = lgo.entry
        {
        WHERE
            go.entry IN (?a)
        }
        LIMIT
            ?d, ?d';

    $updateQuery = '
        UPDATE
            ?_objects o
        LEFT JOIN
            dbc_lock l ON l.id = IF(o.`type` = 3, lockId, null)
        SET
            typeCat = IF(`type` = 3 AND (l.properties1 = 1 OR l.properties2 = 1), -5,                       -- footlocker
                          IF(`type` = 3 AND (l.properties1 = 2), -3,                                        -- herb
                              IF(`type` = 3 AND (l.properties1 = 3), -4, typeCat))),                        -- ore
            reqSkill = IF(`type` = 3 AND l.properties1 IN (1, 2, 3), IF(l.reqSkill1 > 1, l.reqSkill1, 1),
                           IF(`type` = 3 AND l.properties2 = 1, IF(l.reqSkill2 > 1, l.reqSkill2, 1), 0))
        {
        WHERE
            o.id IN (?a)
        }';

    $offset = 0;
    while ($objects = DB::World()->select($baseQuery, $ids ?: DBSIMPLE_SKIP, $offset, SqlGen::$stepSize))
    {
        CLISetup::log(' * sets '.($offset + 1).' - '.($offset + count($objects)));

        $offset += SqlGen::$stepSize;

        foreach ($objects as $o)
            DB::Aowow()->query('REPLACE INTO ?_objects VALUES (?a)', array_values($o));
    }

    // apply typeCat and reqSkill depending on locks
    DB::Aowow()->query($updateQuery, $ids ?: DBSIMPLE_SKIP);

    return true;
}

?>