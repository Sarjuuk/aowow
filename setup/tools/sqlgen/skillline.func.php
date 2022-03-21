<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'skillline';

    protected $tblDependencyAowow = ['icons'];
    protected $dbcSourceFiles     = ['skillline', 'spell', 'skilllineability'];

    public function generate(array $ids = []) : bool
    {
        $baseQuery = '
            REPLACE INTO
                ?_skillline
            SELECT
                id, categoryId, 0, categoryId, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, description_loc0, description_loc2, description_loc3, description_loc4, description_loc6, description_loc8, 0, iconId, 0, 0, ""
            FROM
                dbc_skillline';

        DB::Aowow()->query($baseQuery);

        // categorization
        DB::Aowow()->query('UPDATE ?_skillline SET typeCat = -5 WHERE id = 777 OR (categoryId = 9 AND id NOT IN (356, 129, 185, 142, 155))');
        DB::Aowow()->query('UPDATE ?_skillline SET typeCat = -4 WHERE categoryId = 9 AND name_loc0 LIKE "%racial%"');
        DB::Aowow()->query('UPDATE ?_skillline SET typeCat = -6 WHERE id IN (778, 788, 758) OR (categoryId = 7 AND name_loc0 LIKE "%pet%")');

        // more complex fixups
        DB::Aowow()->query('UPDATE ?_skillline SET name_loc8 = REPLACE(name_loc8, " - ", ": ") WHERE categoryId = 7 OR id IN (758, 788)');
        DB::Aowow()->query('UPDATE ?_skillline SET cuFlags = ?d WHERE id IN (?a)', CUSTOM_EXCLUDE_FOR_LISTVIEW, [142, 148, 149, 150, 152, 155, 183, 533, 553, 554, 713, 769]);

        // apply icons
        DB::Aowow()->query('UPDATE ?_skillline sl, ?_icons ic, dbc_spellicon si SET sl.iconId = ic.id WHERE sl.iconIdBak = si.id AND ic.name = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1))');
        DB::Aowow()->query('
            UPDATE
                ?_skillline sl,
                dbc_spell s,
                dbc_skilllineability sla,
                ?_icons ic,
                dbc_spellicon si
            SET
                sl.iconId = ic.id
            WHERE
                (s.effect1Id IN (25, 26, 40) OR s.effect2Id = 60) AND
                ic.name = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1)) AND
                s.iconId = si.id AND
                sla.spellId = s.id AND
                sl.id = sla.skillLineId
        ');
        DB::Aowow()->query('UPDATE ?_skillline sl, ?_icons ic SET sl.iconId = ic.id WHERE ic.name = ? AND sl.id = ?d', 'inv_misc_pelt_wolf_01', 393);
        DB::Aowow()->query('UPDATE ?_skillline sl, ?_icons ic SET sl.iconId = ic.id WHERE ic.name = ? AND sl.id = ?d', 'inv_misc_key_03', 633);

        $this->reapplyCCFlags('skillline', Type::SKILL);

        return true;
    }
});

?>
