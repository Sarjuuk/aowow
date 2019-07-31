<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;

    protected $command = 'shapeshiftforms';

    protected $dbcSourceFiles = ['spellshapeshiftform'];

    private $customData = array(
         1 => ['displayIdH' => 8571],
        15 => ['displayIdH' => 8571],
         5 => ['displayIdH' => 2289],
         8 => ['displayIdH' => 2289],
        14 => ['displayIdH' => 2289],
        27 => ['displayIdH' => 21244],
        29 => ['displayIdH' => 20872]
    );

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('
            REPLACE INTO
                ?_shapeshiftforms
            SELECT
                id, flags, creatureType,
                displayIdA, displayIdH,
                spellId1, spellId2, spellId3, spellId4, spellId5, spellId6, spellId7, spellId8,
                IF(name_loc0 = "", IF(name_loc2 = "", IF(name_loc3 = "", IF(name_loc6 = "", IF(name_loc8 = "", "???", name_loc8), name_loc6), name_loc3), name_loc2), name_loc0)
            FROM
                dbc_spellshapeshiftform'
        );

        return true;
    }
});

?>
