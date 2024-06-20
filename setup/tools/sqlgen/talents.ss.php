<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'talents' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Profile from dbc.']
    );

    protected $dbcSourceFiles = ['talent', 'talenttab'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_talents');

        // class: 0 => hunter pets
        for ($i = 1; $i < 6; $i++)
            DB::Aowow()->query(
               'INSERT INTO ?_talents
                    SELECT  t.id,
                            IF(tt.classMask <> 0, LOG(2, tt.classMask) + 1, 0),
                            tt.creatureFamilyMask,
                            IF(tt.creaturefamilyMask <> 0, LOG(2, tt.creaturefamilyMask), tt.tabNumber),
                            t.row,
                            t.column,
                            t.rank?d,
                            ?d
                    FROM    dbc_talenttab tt
                    JOIN    dbc_talent t ON tt.id = t.tabId
                    WHERE   t.rank?d <> 0',
                $i, $i, $i
            );

        return true;
    }
});

?>
