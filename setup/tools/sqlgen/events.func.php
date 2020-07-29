<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'events';

    protected $tblDependencyTC = ['game_event', 'game_event_prerequisite'];

    public function generate(array $ids = []) : bool
    {
        $eventQuery = '
            SELECT
                ge.eventEntry,
                holiday,
                0,                                              -- cuFlags
                IFNULL(UNIX_TIMESTAMP(start_time), 0),
                IFNULL(UNIX_TIMESTAMP(end_time), 0),
                occurence * 60,
                length * 60,
                IF (gep.eventEntry IS NOT NULL, GROUP_CONCAT(prerequisite_event SEPARATOR " "), NULL),
                description
            FROM
                game_event ge
            LEFT JOIN
                game_event_prerequisite gep ON gep.eventEntry = ge.eventEntry
            {
            WHERE
                ge.eventEntry IN (?a)
            }
            GROUP BY
                ge.eventEntry';

        $events = DB::World()->select($eventQuery, $ids ?: DBSIMPLE_SKIP);

        foreach ($events as $e)
            DB::Aowow()->query('REPLACE INTO ?_events VALUES (?a)', array_values($e));

        return true;
    }
});

?>
