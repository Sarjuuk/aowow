<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $info = array(
        'shapeshiftforms' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Spell from dbc.']
    );

    protected $dbcSourceFiles = ['spellshapeshiftform'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_shapeshiftforms');
        DB::Aowow()->query(
           'INSERT INTO ?_shapeshiftforms
            SELECT      id, flags, creatureType, displayIdA, displayIdH,
                        spellId1, spellId2, spellId3, spellId4, spellId5, spellId6, spellId7, spellId8,
                        IF(name_loc0 = "", IF(name_loc2 = "", IF(name_loc3 = "", IF(name_loc4 = "", IF(name_loc6 = "", IF(name_loc8 = "", "???", name_loc8), name_loc6), name_loc4), name_loc3), name_loc2), name_loc0)
            FROM        dbc_spellshapeshiftform'
        );

        return true;
    }
});

?>
