<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

/* deps:
 * quest_template
 * game_event_seasonal_questrelation
 * game_event
 * achievement_reward
*/

$customData = array(
    137 => ['holidayId' => 201, 'gender' => 2],
    138 => ['holidayId' => 201, 'gender' => 1],
    124 => ['holidayId' => 324],
    135 => ['holidayId' => 423],
    155 => ['holidayId' => 181],
    133 => ['holidayId' => 372],
     74 => ['holidayId' => 327],
     75 => ['holidayId' => 341],
     76 => ['holidayId' => 341],
    134 => ['holidayId' => 141],
    168 => ['holidayId' => 404]
);
$reqDBC = ['chartitles'];

function titles()
{
    $questQuery = '
        SELECT
             qt.RewardTitle AS ARRAY_KEY,
             qt.RequiredRaces,
             ge.holiday
        FROM
            quest_template qt
        LEFT JOIN
            game_event_seasonal_questrelation sq ON sq.questId = qt.ID
        LEFT JOIN
            game_event ge ON ge.eventEntry = sq.eventEntry
        WHERE
             qt.RewardTitle <> 0';

    DB::Aowow()->query('REPLACE INTO ?_titles SELECT Id, 0, 0, 0, 0, 0, 0, 0, male_loc0, male_loc2, male_loc3, male_loc6, male_loc8, female_loc0, female_loc2, female_loc3, female_loc6, female_loc8 FROM dbc_chartitles');

    // hide unused titles
    DB::Aowow()->query('UPDATE ?_titles SET cuFlags = ?d WHERE id BETWEEN 85 AND 123 AND id NOT IN (113, 120, 121, 122)', CUSTOM_EXCLUDE_FOR_LISTVIEW);

    // set expansion
    DB::Aowow()->query('UPDATE ?_titles SET expansion = 2 WHERE id >= 72 AND id <> 80');
    DB::Aowow()->query('UPDATE ?_titles SET expansion = 1 WHERE id >= 42 AND id <> 46 AND expansion = 0');

    // set category
    DB::Aowow()->query('UPDATE ?_titles SET category = 1 WHERE id <= 28 OR id IN (42, 43, 44, 45, 47, 48, 62, 71, 72, 80, 82, 126, 127, 128, 157, 163, 167, 169, 177)');
    DB::Aowow()->query('UPDATE ?_titles SET category = 5 WHERE id BETWEEN 96 AND 109 OR id IN (83, 84)');
    DB::Aowow()->query('UPDATE ?_titles SET category = 2 WHERE id BETWEEN 144 AND 156 OR id IN (63, 77, 79, 113, 123, 130, 131, 132, 176)');
    DB::Aowow()->query('UPDATE ?_titles SET category = 6 WHERE id IN (46, 74, 75, 76, 124, 133, 134, 135, 137, 138, 155, 168)');
    DB::Aowow()->query('UPDATE ?_titles SET category = 4 WHERE id IN (81, 125)');
    DB::Aowow()->query('UPDATE ?_titles SET category = 3 WHERE id IN (53, 64, 120, 121, 122, 129, 139, 140, 141, 142) OR (id >= 158 AND category = 0)');

    // update side
    $questInfo = DB::World()->select($questQuery);
    $sideUpd   = DB::World()->selectCol('SELECT IF (title_A, title_A, title_H) AS ARRAY_KEY, BIT_OR(IF(title_A, 1, 2)) AS side FROM achievement_reward WHERE (title_A <> 0 AND title_H = 0) OR (title_H <> 0 AND title_A = 0) GROUP BY ARRAY_KEY HAVING side <> 3');
    foreach ($questInfo as $tId => $data)
    {
        if ($data['holiday'])
            DB::Aowow()->query('UPDATE ?_titles SET holidayId = ?d WHERE id = ?d', $data['holiday'], $tId);

        $side = Util::sideByRaceMask($data['RequiredRaces']);
        if ($side == 3)
            continue;

        if (!isset($sideUpd[$tId]))
            $sideUpd[$tId]  = $side;
        else
            $sideUpd[$tId] |= $side;
    }
    foreach ($sideUpd as $tId => $side)
        if ($side != 3)
            DB::Aowow()->query("UPDATE ?_titles SET side = ?d WHERE id = ?d", $side, $tId);

    // update side - sourceless titles (maintain query order)
    DB::Aowow()->query('UPDATE ?_titles SET side = 2 WHERE id <= 28 OR id IN (118, 119, 116, 117, 110, 127)');
    DB::Aowow()->query('UPDATE ?_titles SET side = 1 WHERE id <= 14 OR id IN (111, 115, 112, 114, 126)');

    // ! src12Ext pendant in source-script !
    $doubles = DB::World()->selectCol('SELECT IF(title_A, title_A, title_H) AS ARRAY_KEY, GROUP_CONCAT(entry SEPARATOR " "), count(1) FROM achievement_reward WHERE title_A <> 0 OR title_H <> 0 GROUP BY ARRAY_KEY HAVING count(1) > 1');
    foreach ($doubles as $tId => $acvIds)
        DB::Aowow()->query('UPDATE ?_titles SET src12Ext = ?d WHERE id = ?d', explode(' ', $acvIds)[1], $tId);

    return true;
}

?>