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
        'races' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: PlayerRace from dbc.']
    );

    protected $setupAfter     = [['icons'], []];
    protected $dbcSourceFiles = ['chrraces', 'charbaseinfo'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_races');
        DB::Aowow()->query(
           'INSERT INTO ?_races
            SELECT      `id`, 0, `flags`, 0, `factionId`, 0, 0, `baseLanguage`, IF(`side` = 2, 0, `side` + 1), `fileString`, 0, 0, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, `expansion`
            FROM        dbc_chrraces'
        );

        // collect iconIds
        DB::Aowow()->query('UPDATE ?_races r, ?_icons i0, ?_icons i1 SET r.`iconId0` = i0.`id`, r.`iconId1` = i1.`id` WHERE i0.`name_source` = CONCAT("race_", LOWER(r.`fileString`), "_male") AND i1.`name_source` = CONCAT("race_", LOWER(r.`fileString`), "_female")');

        // add classMask
        DB::Aowow()->query('UPDATE ?_races r JOIN (SELECT BIT_OR(1 << (`classId` - 1)) AS "classMask", `raceId` FROM dbc_charbaseinfo GROUP BY `raceId`) cbi ON cbi.`raceId` = r.id SET r.`classMask` = cbi.`classMask`');

        // add cuFlags
        DB::Aowow()->query('UPDATE ?_races SET `cuFlags` = ?d WHERE `flags` & ?d', CUSTOM_EXCLUDE_FOR_LISTVIEW, 0x1);

        $this->reapplyCCFlags('races', Type::CHR_RACE);

        return true;
    }
});

?>
