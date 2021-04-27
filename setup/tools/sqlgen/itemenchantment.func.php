<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'itemenchantment';

    protected $tblDependencyTC = ['spell_enchant_proc_data'];
    protected $dbcSourceFiles  = ['spellitemenchantment'];

    public function generate(array $ids = []) : bool
    {
        $baseQuery = '
            REPLACE INTO
                ?_itemenchantment
            SELECT
                Id, charges, 0, 0, 0, type1, type2, type3, amount1, amount2, amount3, object1, object2, object3, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, conditionId, skillLine, skillLevel, requiredLevel
            FROM
                dbc_spellitemenchantment';

        DB::Aowow()->query($baseQuery);

        $cuProcs = DB::World()->select('SELECT EnchantID AS ARRAY_KEY, Chance AS procChance, ratePerMinute AS ppmRate FROM spell_enchant_proc_data');
        foreach ($cuProcs as $id => $vals)
            DB::Aowow()->query('UPDATE ?_itemenchantment SET ?a WHERE id = ?d', $vals, $id);

        // hide strange stuff
        DB::Aowow()->query('UPDATE ?_itemenchantment SET cuFlags = ?d WHERE type1 = 0 AND type2 = 0 AND type3 = 0', CUSTOM_EXCLUDE_FOR_LISTVIEW);
        DB::Aowow()->query('UPDATE ?_itemenchantment SET cuFlags = ?d WHERE name_loc0 LIKE "%test%"', CUSTOM_EXCLUDE_FOR_LISTVIEW);

        return true;
    }
});

?>
