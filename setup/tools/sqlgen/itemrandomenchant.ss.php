<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'itemrandomenchant' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Item from dbc.']
    );

    protected $dbcSourceFiles = ['itemrandomsuffix', 'itemrandomproperties'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_itemrandomenchant');
        DB::Aowow()->query(
           'INSERT INTO ?_itemrandomenchant
                SELECT -id, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, nameINT, enchantId1, enchantId2, enchantId3, enchantId4, enchantId5, allocationPct1, allocationPct2, allocationPct3, allocationPct4, allocationPct5 FROM dbc_itemrandomsuffix UNION
                SELECT  id, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, nameINT, enchantId1, enchantId2, enchantId3, enchantId4, enchantId5, 0,              0,              0,              0,              0              FROM dbc_itemrandomproperties'
        );

        return true;
    }
});

?>
