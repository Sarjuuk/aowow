<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;

    protected $command = 'races';

    protected $dbcSourceFiles = ['chrraces', 'charbaseinfo'];

    private $customData = array(
        null,
        ['leader' => 29611, 'factionId' => 72,  'startAreaId' =>   12],
        ['leader' => 4949,  'factionId' => 76,  'startAreaId' =>   14],
        ['leader' => 2784,  'factionId' => 47,  'startAreaId' =>    1],
        ['leader' => 7999,  'factionId' => 69,  'startAreaId' =>  141],
        ['leader' => 10181, 'factionId' => 68,  'startAreaId' =>   85],
        ['leader' => 3057,  'factionId' => 81,  'startAreaId' =>  215],
        ['leader' => 7937,  'factionId' => 54,  'startAreaId' =>    1],
        ['leader' => 10540, 'factionId' => 530, 'startAreaId' =>   14],
        null,
        ['leader' => 16802, 'factionId' => 911, 'startAreaId' => 3430],
        ['leader' => 17468, 'factionId' => 930, 'startAreaId' => 3524]
    );

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

        return true;
    }
});

?>
