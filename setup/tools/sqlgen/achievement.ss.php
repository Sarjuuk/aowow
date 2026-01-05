<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup('sql', new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $info = array(
        'achievement' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Achievement from dbc and world db.']
    );

    protected $dbcSourceFiles     = ['achievement_category', 'achievement', 'spellicon'];
    protected $worldDependency    = ['dbc_achievement', 'disables'];
    protected $setupAfter         = [['icons'], []];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::achievement');
        DB::Aowow()->qry('TRUNCATE ::achievementcategory');

        /**************/
        /* categories */
        /**************/

        CLI::write('[achievement] - resolving categories');

        DB::Aowow()->qry('INSERT INTO ::achievementcategory SELECT ac.id, GREATEST(ac.parentcategory, 0), GREATEST(IFNULL(ac1.parentcategory, 0), 0) FROM dbc_achievement_category ac LEFT JOIN dbc_achievement_category ac1 ON ac1.id = ac.parentCategory');

        /************/
        /* dbc data */
        /************/

        CLI::write('[achievement] - basic dbc data');

        DB::Aowow()->qry(
           'INSERT INTO ::achievement
            SELECT      a.id,
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
            FROM        dbc_achievement a
            LEFT JOIN   dbc_achievement_category ac ON ac.id = a.category
            LEFT JOIN   dbc_spellicon si ON si.id = a.iconId
            LEFT JOIN   ::icons i ON LOWER(SUBSTRING_INDEX(si.iconPath, "\\", -1)) = i.name_source',
        );


        /*******************/
        /* serverside data */
        /*******************/

        CLI::write('[achievement] - serverside achievement data');

        $serverAchievements = DB::World()->selectAssoc('SELECT `ID` AS "id", IF(`requiredFaction` = -1, 3, IF(`requiredFaction` = 0, 2, 1)) AS "faction", `mapID` AS "map", `points`, `flags`, `count` AS "reqCriteriaCount", `refAchievement` FROM achievement_dbc');

        foreach ($serverAchievements as &$sa)
        {
            $sa['cuFlags'] = CUSTOM_SERVERSIDE;
            foreach (CLISetup::$locales as $i => $_)
                if ($_)
                    $sa['name_loc'.$i] = 'Serverside - #'.$sa['id'];
        }

        unset($sa);

        foreach ($serverAchievements as $sa)
            DB::Aowow()->qry('INSERT INTO ::achievement %v', $sa);


        /********************************/
        /* create chain of achievements */
        /********************************/

        CLI::write('[achievement] - linking achievements to chain');

        $parents = DB::Aowow()->selectCol('SELECT a.`id` FROM dbc_achievement a JOIN dbc_achievement b ON b.`previous` = a.`id` WHERE a.`previous` = 0');
        foreach ($parents as $chainId => $next)
        {
            $tree = [null, $next];
            while ($next = DB::Aowow()->selectCell('SELECT `id` FROM dbc_achievement WHERE `previous` = %i', $next))
                $tree[] = $next;

            foreach ($tree as $idx => $aId)
            {
                if (!$aId)
                    continue;

                DB::Aowow()->qry('UPDATE ::achievement SET `cuFlags` = `cuFlags` | %i, `chainId` = %i, `chainPos` = %i WHERE `id` = %i',
                    $idx == 1 ? ACHIEVEMENT_CU_FIRST_SERIES : (count($tree) == $idx + 1 ? ACHIEVEMENT_CU_LAST_SERIES : 0),
                    $chainId + 1,
                    $idx,
                    $aId
                );
            }
        }


        /*********************/
        /* applying disables */
        /*********************/

        CLI::write('[achievement] - disabling disabled achievements from table disables');

        if ($criteria = DB::World()->selectCol('SELECT `entry` FROM disables WHERE `sourceType` = 4'))
            DB::Aowow()->qry('UPDATE ::achievement a JOIN ::achievementcriteria ac ON a.`id` = ac.`refAchievementId` SET a.`cuFlags` = %i WHERE ac.`id` IN %in', CUSTOM_DISABLED, $criteria);

        $this->reapplyCCFlags('achievement', Type::ACHIEVEMENT);

        return true;
    }
});

?>
