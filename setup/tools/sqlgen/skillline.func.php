<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;

    protected $command = 'skillline';

    protected $tblDependencyAowow = ['icons'];
    protected $dbcSourceFiles     = ['skillline', 'spell', 'skilllineability'];

    private $customData = array(
        393 => ['professionMask' => 0x0000],                                                                                            // Skinning
        171 => ['professionMask' => 0x0001, 'recipeSubClass' => 6, 'specializations' => '28677 28675 28672'],                           // Alchemy
        164 => ['professionMask' => 0x0002, 'recipeSubClass' => 4, 'specializations' => '9788 9787 17041 17040 17039'],                 // Blacksmithing
        185 => ['professionMask' => 0x0004, 'recipeSubClass' => 5],                                                                     // Cooking
        333 => ['professionMask' => 0x0008, 'recipeSubClass' => 8],                                                                     // Enchanting
        202 => ['professionMask' => 0x0010, 'recipeSubClass' => 3, 'specializations' => '20219 20222'],                                 // Engineering
        129 => ['professionMask' => 0x0020, 'recipeSubClass' => 7],                                                                     // First Aid
        755 => ['professionMask' => 0x0040, 'recipeSubClass' => 10],                                                                    // Jewelcrafting
        165 => ['professionMask' => 0x0080, 'recipeSubClass' => 1, 'specializations' => '10656 10658 10660'],                           // Leatherworking
        186 => ['professionMask' => 0x0100],                                                                                            // Mining
        197 => ['professionMask' => 0x0200, 'recipeSubClass' => 2, 'specializations' => '26798 26801 26797'],                           // Tailoring
        356 => ['professionMask' => 0x0400, 'recipeSubClass' => 9],                                                                     // Fishing
        182 => ['professionMask' => 0x0800],                                                                                            // Herbalism
        773 => ['professionMask' => 0x1000, 'recipeSubClass' => 11],                                                                    // Inscription
        785 => ['name_loc0' => 'Pet - Wasp'],                                                                                           // Pet - Wasp
        781 => ['name_loc2' => 'Familier - diablosaure exotique'],                                                                      // Pet - Exotic Devilsaur
        758 => ['name_loc6' => 'Mascota: Evento - Control remoto', 'name_loc3' => 'Tier - Ereignis Ferngesteuert', 'categoryId' => 7],  // Pet - Event - Remote Control
        788 => ['categoryId' => 7],                                                                                                     // Pet - Exotic Spirit Beast
    );

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

        return true;
    }
});

?>
