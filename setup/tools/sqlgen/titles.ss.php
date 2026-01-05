<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $info = array(
        'titles' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Title from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['chartitles'];
    protected $worldDependency = ['quest_template', 'game_event_seasonal_questrelation', 'game_event', 'achievement_reward'];

    private $titleHoliday = array(
        137 => 201,
        138 => 201,
        124 => 324,
        135 => 423,
        155 => 181,
        133 => 372,
         74 => 327,
         75 => 341,
         76 => 341,
        134 => 141,
        168 => 404
    );

    public function generate() : bool
    {
        $questQuery =
           'SELECT    qt.`RewardTitle` AS ARRAY_KEY, qt.`AllowableRaces`, IFNULL(ge.`eventEntry`, 0) AS `eventEntry`
            FROM      quest_template qt
            LEFT JOIN game_event_seasonal_questrelation sq ON sq.`questId` = qt.`Id`
            LEFT JOIN game_event ge ON ge.`eventEntry` = sq.`eventEntry`
            WHERE     qt.`RewardTitle` <> 0';

        DB::Aowow()->qry('TRUNCATE ::titles');
        DB::Aowow()->qry('INSERT INTO ::titles SELECT `id`, 0, 0, 0, 0, 0, 0, 0, `bitIdx`, `male_loc0`, `male_loc2`, `male_loc3`, `male_loc4`, `male_loc6`, `male_loc8`, `female_loc0`, `female_loc2`, `female_loc3`, `female_loc4`, `female_loc6`, `female_loc8` FROM dbc_chartitles');

        // hide unused titles
        DB::Aowow()->qry('UPDATE ::titles SET `cuFlags` = %i WHERE `id` BETWEEN 85 AND 123 AND `id` NOT IN (113, 120, 121, 122)', CUSTOM_EXCLUDE_FOR_LISTVIEW);

        // set expansion
        DB::Aowow()->qry('UPDATE ::titles SET `expansion` = 2 WHERE `id` >= 72 AND `id` <> 80');
        DB::Aowow()->qry('UPDATE ::titles SET `expansion` = 1 WHERE `id` >= 42 AND `id` <> 46 AND `expansion` = 0');

        // set category
        DB::Aowow()->qry('UPDATE ::titles SET `category` = 1 WHERE `id` <= 28 OR `id` IN (42, 43, 44, 45, 47, 48, 62, 71, 72, 80, 82, 126, 127, 128, 157, 163, 167, 169, 177)');
        DB::Aowow()->qry('UPDATE ::titles SET `category` = 5 WHERE `id` BETWEEN 96 AND 109 OR `id` IN (83, 84)');
        DB::Aowow()->qry('UPDATE ::titles SET `category` = 2 WHERE `id` BETWEEN 144 AND 156 OR `id` IN (63, 77, 79, 113, 123, 130, 131, 132, 176)');
        DB::Aowow()->qry('UPDATE ::titles SET `category` = 6 WHERE `id` IN (46, 74, 75, 76, 124, 133, 134, 135, 137, 138, 155, 168)');
        DB::Aowow()->qry('UPDATE ::titles SET `category` = 4 WHERE `id` IN (81, 125)');
        DB::Aowow()->qry('UPDATE ::titles SET `category` = 3 WHERE `id` IN (53, 64, 120, 121, 122, 129, 139, 140, 141, 142) OR (`id` >= 158 AND `category` = 0)');

        // update event
        if ($assoc = DB::World()->selectCol('SELECT `holiday` AS ARRAY_KEY, `eventEntry` FROM game_event WHERE `holiday` IN %in', array_values($this->titleHoliday)))
            foreach ($this->titleHoliday as $tId => $hId)
                if (!empty($assoc[$hId]))
                    DB::Aowow()->qry('UPDATE ::titles SET `eventId` = %i WHERE `id` = %i', $assoc[$hId], $tId);

        // update side
        $questInfo = DB::World()->selectAssoc($questQuery);
        $sideUpd   = DB::World()->selectCol('SELECT IF(`TitleA`, `TitleA`, `TitleH`) AS ARRAY_KEY, BIT_OR(IF(`TitleA`, %i, %i)) AS `side` FROM achievement_reward WHERE (`TitleA` <> 0 AND `TitleH` = 0) OR (`TitleH` <> 0 AND `TitleA` = 0) GROUP BY ARRAY_KEY HAVING `side` <> %i',
            SIDE_ALLIANCE, SIDE_HORDE, SIDE_BOTH);
        foreach ($questInfo as $tId => $data)
        {
            if ($data['eventEntry'])
                DB::Aowow()->qry('UPDATE ::titles SET `eventId` = %i WHERE `id` = %i', $data['eventEntry'], $tId);

            $side = ChrRace::sideFromMask($data['AllowableRaces']);
            if ($side == SIDE_BOTH)
                continue;

            if (!isset($sideUpd[$tId]))
                $sideUpd[$tId]  = $side;
            else
                $sideUpd[$tId] |= $side;
        }
        foreach ($sideUpd as $tId => $side)
            if ($side != SIDE_BOTH)
                DB::Aowow()->qry("UPDATE ::titles SET `side` = %i WHERE `id` = %i", $side, $tId);

        // update side - sourceless titles (maintain query order)
        DB::Aowow()->qry('UPDATE ::titles SET `side` = %i WHERE `id` <= 28 OR `id` IN (118, 119, 116, 117, 110, 127)', SIDE_HORDE);
        DB::Aowow()->qry('UPDATE ::titles SET `side` = %i WHERE `id` <= 14 OR `id` IN (111, 115, 112, 114, 126)', SIDE_ALLIANCE);

        $this->reapplyCCFlags('titles', Type::TITLE);

        return true;
    }
});

?>
