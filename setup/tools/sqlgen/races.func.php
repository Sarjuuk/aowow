<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'races';

    protected $dbcSourceFiles = ['chrraces', 'charbaseinfo'];

    public function generate(array $ids = []) : bool
    {
        /**********/
        /* Basics */
        /**********/

        $baseQuery = '
            REPLACE INTO
                ?_races
            SELECT
                id, 0, flags, 0, factionId, 0, 0, baseLanguage, IF(side = 2, 0, side + 1), fileString, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, expansion
            FROM
                dbc_chrraces';

        DB::Aowow()->query($baseQuery);

        // add classMask
        DB::Aowow()->query('UPDATE ?_races r JOIN (SELECT BIT_OR(1 << (classId - 1)) as classMask, raceId FROM dbc_charbaseinfo GROUP BY raceId) cbi ON cbi.raceId = r.id SET r.classMask = cbi.classMask');

        // add cuFlags
        DB::Aowow()->query('UPDATE ?_races SET cuFlags = ?d WHERE flags & ?d', CUSTOM_EXCLUDE_FOR_LISTVIEW, 0x1);

        $this->reapplyCCFlags('races', Type::CHR_RACE);

        return true;
    }
});

?>
