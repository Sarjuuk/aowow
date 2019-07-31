<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'talents';

    protected $dbcSourceFiles = ['talent', 'talenttab'];

    public function generate(array $ids = []) : bool
    {
        // class: 0 => hunter pets
        for ($i = 1; $i < 6; $i++)
            DB::Aowow()->query('
                REPLACE INTO
                    ?_talents
                SELECT
                    t.id,
                    IF(tt.classMask <> 0, LOG(2, tt.classMask) + 1, 0),
                    tt.creatureFamilyMask,
                    IF(tt.creaturefamilyMask <> 0, LOG(2, tt.creaturefamilyMask), tt.tabNumber),
                    t.row,
                    t.column,
                    t.rank?d,
                    ?d
                FROM
                    dbc_talenttab tt
                JOIN
                    dbc_talent t ON tt.id = t.tabId
                WHERE
                    t.rank?d <> 0
            ', $i, $i, $i);

        return true;
    }
});

?>
