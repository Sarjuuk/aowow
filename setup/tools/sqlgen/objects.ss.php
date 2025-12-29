<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'objects' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Gameobject from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['lock'];
    protected $worldDependency = ['gameobject_template', 'gameobject_template_addon', 'gameobject_template_locale', 'gameobject_questitem'];

    public function generate(array $ids = []) : bool
    {
        $baseQuery =
           'SELECT    go.entry,
                      `type`,
                      IF(`type` = 2, -2,                                     -- quests 1
                          IF(`type` = 8 AND data0 IN (1, 2, 3, 4, 1552), -6, -- tools
                          IF(`type` = 3 AND IFNULL(gqi.ItemId, 0) <> 0, -2,  -- quests 2
                          IF(`type` IN (3, 6, 9, 25), `type`, 0)))),         -- regular chests, traps, books, pools
                      0 AS event,                                            -- linked worldevent
                      displayId,
                      go.name,
                      IFNULL(gtl2.`name`, "") AS name_loc2,
                      IFNULL(gtl3.`name`, "") AS name_loc3,
                      IFNULL(gtl4.`name`, "") AS name_loc4,
                      IFNULL(gtl6.`name`, "") AS name_loc6,
                      IFNULL(gtl8.`name`, "") AS name_loc8,
                      IFNULL(goa.faction, 0),
                      IFNULL(goa.flags, 0),
                      0 AS cuFlags,
                      IF(`type` IN (3, 25), data1, 0) AS lootId,
                      IF(`type` IN (2, 3, 6, 10, 13, 24, 26), data0, IF(`type` IN (0, 1), data1, 0)) AS lockId,
                      0 AS reqSkill,
                      IF(`type` = 9, data0, IF(`type` = 10, data7, 0)) AS pageTextId,
                      IF(`type` = 1, data3, IF(`type` =  3, data7, IF(`type` = 10, data12, IF(`type` = 8, data2, 0)))) AS linkedTrapId,
                      GREATEST(IF(`type` = 5, data5, IF(`type` =  3, data8, IF(`type` = 10, data1,  IF(`type` = 8, data4, 0)))), 0) AS reqQuest,
                      IF(`type` = 8, data0, 0) AS spellFocusId,
                      IF(`type` = 10, data10, IF(`type` IN (18, 24), data1, IF(`type` = 26, data2, IF(`type` = 22, data0, 0)))) AS onUseSpellId,
                      IF(`type` = 18, data4, 0) AS onSuccessSpell,
                      IF(`type` = 18, data2, IF(`type` = 24, data3, 0)) AS auraSpellId,
                      IF(`type` = 30, data2, IF(`type` = 24, data4, IF(`type` = 6, data3, 0))) AS triggeredSpellId,
                      IF(`type` = 29, CONCAT_WS(" ", data14, data15, data16, data17, data0),      -- miscInfo: capturePoint
                          IF(`type` =  3, CONCAT_WS(" ", data4, data5, data2),                    -- miscInfo: loot v
                              IF(`type` = 25, CONCAT_WS(" ", data2, data3, 0),
                                  IF(`type` = 23, CONCAT_WS(" ", data0, data1, data2), "")))),    -- miscInfo: meetingStone
                      NULLIF(IF(ScriptName <> "", ScriptName, AIName), ""),
                      StringId
            FROM      gameobject_template go
            LEFT JOIN gameobject_template_addon goa ON go.entry = goa.entry
            LEFT JOIN gameobject_template_locale gtl2 ON go.entry = gtl2.entry AND gtl2.`locale` = "frFR"
            LEFT JOIN gameobject_template_locale gtl3 ON go.entry = gtl3.entry AND gtl3.`locale` = "deDE"
            LEFT JOIN gameobject_template_locale gtl4 ON go.entry = gtl4.entry AND gtl4.`locale` = "zhCN"
            LEFT JOIN gameobject_template_locale gtl6 ON go.entry = gtl6.entry AND gtl6.`locale` = "esES"
            LEFT JOIN gameobject_template_locale gtl8 ON go.entry = gtl8.entry AND gtl8.`locale` = "ruRU"
            LEFT JOIN gameobject_questitem gqi ON gqi.GameObjectEntry = go.entry
           { WHERE     go.entry IN (?a) }
            GROUP BY  go.entry
            LIMIT     ?d, ?d';

        $i = 0;
        DB::Aowow()->query('TRUNCATE ?_objects');
        while ($objects = DB::World()->select($baseQuery, $ids ?: DBSIMPLE_SKIP, CLISetup::SQL_BATCH * $i, CLISetup::SQL_BATCH))
        {
            CLI::write(' * batch #' . ++$i . ' (' . count($objects) . ')', CLI::LOG_BLANK, true, true);

            foreach ($objects as $object)
                DB::Aowow()->query('INSERT INTO ?_objects VALUES (?a)', array_values($object));
        }

        // apply typeCat and reqSkill depending on locks
        DB::Aowow()->query(
            'UPDATE    ?_objects o
            LEFT JOIN dbc_lock l ON l.id = IF(o.`type` = 3, lockId, null)
            SET       typeCat  = IF(`type` = 3 AND (l.properties1 = 1 OR l.properties2 = 1), -5,    -- footlocker
                                 IF(`type` = 3 AND (l.properties1 = 2), -3,                         -- herb
                                 IF(`type` = 3 AND (l.properties1 = 3), -4, typeCat))),             -- ore
                      reqSkill = IF(`type` = 3 AND l.properties1 IN (1, 2, 3), IF(l.reqSkill1 > 1, l.reqSkill1, 1),
                                 IF(`type` = 3 AND l.properties2 = 1, IF(l.reqSkill2 > 1, l.reqSkill2, 1), 0))
          { WHERE     o.id IN (?a) }',
            $ids ?: DBSIMPLE_SKIP
        );

        $this->reapplyCCFlags('objects', Type::OBJECT);

        return true;
    }
});

?>
