<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'quests_startend' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Quest from world db.']
    );

    protected $worldDependency = ['creature_queststarter', 'creature_questender', 'game_event_creature_quest', 'gameobject_queststarter', 'gameobject_questender', 'game_event_gameobject_quest', 'item_template'];

    public function generate(array $ids = []) : bool
    {
        $query['NPC'] =
           'SELECT 1 AS `type`, `id` AS `typeId`, `quest` AS `questId`, 1 AS `method`, 0            AS `eventId` FROM creature_queststarter UNION
            SELECT 1 AS `type`, `id` AS `typeId`, `quest` AS `questId`, 2 AS `method`, 0            AS `eventId` FROM creature_questender   UNION
            SELECT 1 AS `type`, `id` AS `typeId`, `quest` AS `questId`, 1 AS `method`, `eventEntry` AS `eventId` FROM game_event_creature_quest';

        $query['Object'] =
           'SELECT 2 AS `type`, `id` AS `typeId`, `quest` AS `questId`, 1 AS `method`, 0            AS `eventId` FROM gameobject_queststarter UNION
            SELECT 2 AS `type`, `id` AS `typeId`, `quest` AS `questId`, 2 AS `method`, 0            AS `eventId` FROM gameobject_questender   UNION
            SELECT 2 AS `type`, `id` AS `typeId`, `quest` AS `questId`, 1 AS `method`, `eventEntry` AS `eventId` FROM game_event_gameobject_quest';

        $query['Item'] = 'SELECT 3 AS `type`, `entry` AS `typeId`, `startquest` AS `questId`, 1 AS `method`, 0 AS `eventId` FROM item_template WHERE `startquest` <> 0';

        DB::Aowow()->query('TRUNCATE ?_quests_startend');

        foreach ($query as $n => $q)
        {
            CLI::write(' - ' . $n . ' start/end-points', CLI::LOG_BLANK, true, true);

            $data = DB::World()->select($q);
            foreach ($data as $d)
                DB::Aowow()->query('INSERT INTO ?_quests_startend (?#) VALUES (?a) ON DUPLICATE KEY UPDATE `method` = `method` | ?d, `eventId` = IF(`eventId` = 0, ?d, `eventId`)', array_keys($d), array_values($d), $d['method'], $d['eventId']);
        }

        // update quests without start as unavailable
        Db::Aowow()->query('UPDATE ?_quests q LEFT JOIN ?_quests_startend qse ON qse.`questId` = q.`id` AND qse.`method` & 1 SET q.`cuFlags` = q.`cuFlags` | ?d WHERE qse.`questId` IS NULL', CUSTOM_UNAVAILABLE);

        return true;
    }
});

?>
