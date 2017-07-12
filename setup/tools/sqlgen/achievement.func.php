<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * dbc_achievement
 * disables
*/

// Higher Learning - item rewarded through gossip
$customData = array(
    1956 => ['itemExtra' => 44738]
);
$reqDBC = ['achievement_category', 'achievement', 'spellicon'];

function achievement(array $ids = [])
{
    if ($ids)
        DB::Aowow()->query('DELETE FROM ?_achievement WHERE id IN (?a)', $ids);
    else
    {
        DB::Aowow()->query('
            REPLACE INTO
                ?_achievement
            SELECT
                a.id,
                2 - a.faction,
                a.map,
                0,
                0,
                a.category,
                ac.parentCategory,
                a.points,
                a.orderInGroup,
                IFNULL(i.id, 0),
                a.iconId,
                a.flags,
                a.reqCriteriaCount,
                a.refAchievement,
                0,
                0,
                a.name_loc0,        a.name_loc2,        a.name_loc3,        a.name_loc4,        a.name_loc6,        a.name_loc8,
                a.description_loc0, a.description_loc2, a.description_loc3, a.description_loc4, a.description_loc6, a.description_loc8,
                a.reward_loc0,      a.reward_loc2,      a.reward_loc3,      a.reward_loc4,      a.reward_loc6,      a.reward_loc8
            FROM
                dbc_achievement a
            LEFT JOIN
                dbc_achievement_category ac ON ac.id = a.category
            LEFT JOIN
                dbc_spellicon si ON si.id = a.iconId
            LEFT JOIN
                ?_icons i ON LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1)) = i.name
        ');
    }

    // serverside achievements
    $serverAchievements = DB::World()->select('SELECT ID, IF(requiredFaction = -1, 3, IF(requiredFaction = 0, 2, 1)) AS "faction", mapID, points, flags, count, refAchievement FROM achievement_dbc{ WHERE id IN (?a)}',
        $ids ?: DBSIMPLE_SKIP
    );
    foreach ($serverAchievements as $sa)
        DB::Aowow()->query('REPLACE INTO ?_achievement (id, faction, map, points, flags, reqCriteriaCount, refAchievement, cuFlags, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8) VALUES (?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?, ?, ?, ?, ?, ?)',
            $sa['ID'], $sa['faction'], $sa['mapID'], $sa['points'], $sa['flags'], $sa['count'], $sa['refAchievement'], CUSTOM_SERVERSIDE,
            'Serverside - #'.$sa['ID'], 'Serverside - #'.$sa['ID'], 'Serverside - #'.$sa['ID'], 'Serverside - #'.$sa['ID'], 'Serverside - #'.$sa['ID'], 'Serverside - #'.$sa['ID']
        );

    if ($ids)
        return true;

    // create chain of achievements
    $chainIdx = 0;
    $parents  = DB::Aowow()->selectCol('SELECT a.id FROM dbc_achievement a JOIN dbc_achievement b ON b.previous = a.id WHERE a.previous = 0');
    foreach ($parents as $chainId => $next)
    {
        $tree = [null, $next];
        while ($next = DB::Aowow()->selectCell('SELECT id FROM dbc_achievement WHERE previous = ?d', $next))
            $tree[] = $next;

        foreach ($tree as $idx => $aId)
        {
            if (!$aId)
                continue;

            DB::Aowow()->query('UPDATE ?_achievement SET cuFlags = cuFlags | ?d, chainId = ?d, chainPos = ?d WHERE id = ?d',
                $idx == 1 ? ACHIEVEMENT_CU_FIRST_SERIES : (count($tree) == $idx + 1 ? ACHIEVEMENT_CU_LAST_SERIES : 0),
                $chainId + 1,
                $idx,
                $aId
            );
        }
    }

    // apply disables
    if ($criteria = DB::World()->selectCol('SELECT entry FROM disables WHERE sourceType = 4'))
        DB::Aowow()->query('UPDATE aowow_achievement a JOIN aowow_achievementcriteria ac ON a.id = ac.refAchievementId SET a.cuFlags = ?d WHERE ac.id IN (?a)', CUSTOM_DISABLED, $criteria);

    return true;
}

?>
