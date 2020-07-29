<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'quests_startend';

    protected $tblDependencyTC = ['creature_queststarter', 'creature_questender', 'game_event_creature_quest', 'gameobject_queststarter', 'gameobject_questender', 'game_event_gameobject_quest', 'item_template'];

    public function generate(array $ids = []) : bool
    {
        $query['creature'] = '
            SELECT 1 AS type, id AS typeId, quest AS questId, 1 AS method, 0          AS eventId FROM creature_queststarter UNION
            SELECT 1 AS type, id AS typeId, quest AS questId, 2 AS method, 0          AS eventId FROM creature_questender   UNION
            SELECT 1 AS type, id AS typeId, quest AS questId, 1 AS method, eventEntry AS eventId FROM game_event_creature_quest';

        $query['object'] = '
            SELECT 2 AS type, id AS typeId, quest AS questId, 1 AS method, 0          AS eventId FROM gameobject_queststarter UNION
            SELECT 2 AS type, id AS typeId, quest AS questId, 2 AS method, 0          AS eventId FROM gameobject_questender   UNION
            SELECT 2 AS type, id AS typeId, quest AS questId, 1 AS method, eventEntry AS eventId FROM game_event_gameobject_quest';

        $query['item'] = 'SELECT 3 AS type, entry AS typeId, startquest AS questId, 1 AS method, 0 AS eventId FROM item_template WHERE startquest <> 0';

        // always rebuild this table from scratch
        // or how would i know what to fetch specifically
        DB::Aowow()->query('TRUNCATE TABLE ?_quests_startend');

        foreach ($query as $q)
        {
            $data = DB::World()->select($q);
            foreach ($data as $d)
                DB::Aowow()->query('INSERT INTO ?_quests_startend (?#) VALUES (?a) ON DUPLICATE KEY UPDATE method = method | VALUES(method), eventId = IF(eventId = 0, VALUES(eventId), eventId)', array_keys($d), array_values($d));
        }

        // update quests without start as unavailable
        Db::Aowow()->query('UPDATE ?_quests q LEFT JOIN ?_quests_startend qse ON qse.questId = q.id AND qse.method & 1 SET q.cuFlags = q.cuFlags | ?d WHERE qse.questId IS NULL', CUSTOM_UNAVAILABLE);

        return true;
    }
});

?>
