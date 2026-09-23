<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup('sql', new class extends SetupScript
{
    protected array $info = array(
        'achievementcriteria' => [[], CLISetup::ARGV_PARAM, 'Compiles criteria for type: Achievement from dbc.']
    );

    protected string $command        = 'achievementcriteria';
    protected array  $dbcSourceFiles = ['achievement_criteria', 'worldmapoverlay'];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::achievementcriteria');

        // resolve worldmapaoverlay/id to areatable/id
        DB::Aowow()->qry(
           'INSERT INTO ::achievementcriteria
            SELECT    ac.`id`, ac.`refAchievementId`, ac.`type`,
                      IFNULL(wmo.`areaTableId`, ac.`value1`), ac.`value2`, ac.`value3`, ac.`value4`, ac.`value5`, ac.`value6`,
                      ac.`name_loc0`, ac.`name_loc2`, ac.`name_loc3`, ac.`name_loc4`, ac.`name_loc6`, ac.`name_loc8`,
                      ac.`completionFlags`, ac.`groupFlags`, ac.`timeLimit`, ac.`order`
            FROM      dbc_achievement_criteria ac
            LEFT JOIN dbc_worldmapoverlay wmo ON ac.`type` = %i AND ac.`value1` = wmo.`id`',
            ACHIEVEMENT_CRITERIA_TYPE_EXPLORE_AREA
        );

        return true;
    }
});

?>
