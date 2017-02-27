<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * game_event
 * game_event_prerequisite
*/

$customData = array(
);
$reqDBC = array(
);

function events(array $ids = [])
{
    $eventQuery = '
        SELECT
            ge.eventEntry,
            holiday,
            0,                                              -- cuFlags
            UNIX_TIMESTAMP(start_time),
            UNIX_TIMESTAMP(end_time),
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

?>
