<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'objects';

    protected $tblDependencyTC    = ['gameobject_template', 'gameobject_template_locale', 'gameobject_questitem'];
    protected $dbcSourceFiles     = ['lock'];

    public function generate(array $ids = []) : bool
    {
        $baseQuery = '
            SELECT
                go.entry,
                `type`,
                IF(`type` = 2, -2,                                                                  -- quests 1
                    IF(`type` = 8 AND Data0 IN (1, 2, 3, 4, 1552), -6,                              -- tools
                    IF(`type` = 3 AND IFNULL(gqi.ItemId, 0) <> 0, -2,                               -- quests 2
                    IF(`type` IN (3, 6, 9, 25), `type`, 0)))),                                      -- regular chests, traps, books, fishing pools
                0 AS event,                                                                         -- linked worldevent
                displayId,
                go.name,
                IFNULL(gtl2.`name`, "") AS name_loc2,
                IFNULL(gtl3.`name`, "") AS name_loc3,
                IFNULL(gtl4.`name`, "") AS name_loc4,
                IFNULL(gtl6.`name`, "") AS name_loc6,
                IFNULL(gtl8.`name`, "") AS name_loc8,
                IFNULL(goa.faction, 0),
                IFNULL(goa.flags, 0),
                0 AS cuFlags,                                                                       -- custom Flags
                IF(`type` IN (3, 25), Data1, 0),                                                    -- lootId
                IF(`type` IN (2, 3, 6, 10, 13, 24, 26), Data0, IF(`type` IN (0, 1), Data1, 0)),     -- lockId
                0 AS reqSkill,                                                                      -- reqSkill
                IF(`type` = 9, Data0, IF(`type` = 10, Data7, 0)),                                   -- pageTextId
                IF(`type` = 1, Data3,                                                               -- linkedTrapIds
                    IF(`type` = 3, Data7,
                        IF(`type` = 10, Data12,
                            IF(`type` = 8, Data2, 0)))),
                IF(`type` = 5, Data5,                                                               -- reqQuest
                    IF(`type` = 3, Data8,
                        IF(`type` = 10, Data1,
                            IF(`type` = 8, Data4, 0)))),
                IF(`type` = 8, Data0, 0),                                                           -- spellFocusId
                IF(`type` = 10, Data10,                                                             -- onUseSpell
                    IF(`type` IN (18, 24), Data1,
                        IF(`type` = 26, Data2,
                            IF(`type` = 22, Data0, 0)))),
                IF(`type` = 18, Data4, 0),                                                          -- onSuccessSpell
                IF(`type` = 18, Data2, IF(`type` = 24, Data3, 0)),                                  -- auraSpell
                IF(`type` = 30, Data2, IF(`type` = 24, Data4, IF(`type` = 6, Data3, 0))),           -- triggeredSpell
                IF(`type` = 29, CONCAT_WS(" ", Data14, Data15, Data16, Data17, Data0),              -- miscInfo: capturePoint
                    IF(`type` =  3, CONCAT_WS(" ", Data4, Data5, Data2),                            -- miscInfo: loot v
                        IF(`type` = 25, CONCAT_WS(" ", Data2, Data3, 0),
                            IF(`type` = 23, CONCAT_WS(" ", Data0, Data1, Data2), "")))),            -- miscInfo: meetingStone
                IF(ScriptName <> "", ScriptName, AIName)
            FROM
                gameobject_template go
            LEFT JOIN
                gameobject_template_addon goa ON go.entry = goa.entry
            LEFT JOIN
                gameobject_template_locale gtl2 ON go.entry = gtl2.entry AND gtl2.`locale` = "frFR"
            LEFT JOIN
                gameobject_template_locale gtl3 ON go.entry = gtl3.entry AND gtl3.`locale` = "deDE"
            LEFT JOIN
                gameobject_template_locale gtl4 ON go.entry = gtl4.entry AND gtl4.`locale` = "zhCN"
            LEFT JOIN
                gameobject_template_locale gtl6 ON go.entry = gtl6.entry AND gtl6.`locale` = "esES"
            LEFT JOIN
                gameobject_template_locale gtl8 ON go.entry = gtl8.entry AND gtl8.`locale` = "ruRU"
            LEFT JOIN
                gameobject_questitem gqi ON gqi.GameObjectEntry = go.entry
            {
            WHERE
                go.entry IN (?a)
            }
            GROUP BY
                go.entry
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

        $i = 0;
        DB::Aowow()->query('TRUNCATE ?_objects');
        while ($objects = DB::World()->select($baseQuery, $ids ?: DBSIMPLE_SKIP, SqlGen::$sqlBatchSize * $i, SqlGen::$sqlBatchSize))
        {
            CLI::write(' * batch #' . ++$i . ' (' . count($objects) . ')', CLI::LOG_BLANK, true, true);

            foreach ($objects as $object)
                DB::Aowow()->query('INSERT INTO ?_objects VALUES (?a)', array_values($object));
        }

        // apply typeCat and reqSkill depending on locks
        DB::Aowow()->query($updateQuery, $ids ?: DBSIMPLE_SKIP);

        $this->reapplyCCFlags('objects', Type::OBJECT);

        return true;
    }
});

?>
