<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'spelldifficulty' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Spell from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['spelldifficulty'];
    protected $worldDependency = ['spelldifficulty_dbc'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE TABLE ?_spelldifficulty');

        DB::Aowow()->query('INSERT INTO ?_spelldifficulty SELECT GREATEST(`normal10`, 0), GREATEST(`normal25`, 0), GREATEST(`heroic10`, 0), GREATEST(`heroic25`, 0) FROM dbc_spelldifficulty');

        $rows = DB::World()->select('SELECT `spellid0`, `spellid1`, `spellid2`, `spellid3` FROM spelldifficulty_dbc');
        foreach ($rows as $r)
            DB::Aowow()->query('INSERT INTO ?_spelldifficulty VALUES (?a)', array_values($r));

        return true;
    }
});

?>
