<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'pet';

    protected $tblDependencyAowow = ['icons'];
    protected $tblDependencyTC    = ['creature_template', 'creature'];
    protected $dbcSourceFiles     = ['talent', 'spell', 'skilllineability', 'creaturefamily'];

    public function generate(array $ids = []) : bool
    {
        $baseQuery = '
            REPLACE INTO
                ?_pet
            SELECT
                f.id,
                categoryEnumId,
                0,                                              -- cuFlags
                0,                                              -- minLevel
                0,                                              -- maxLevel
                petFoodMask,
                petTalentType,
                0,                                              -- exotic
                0,                                              -- expansion
                name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8,
                ic.id,
                skillLine1,
                0, 0, 0, 0,                                     -- spell[1-4]
                0, 0, 0                                         -- armor, damage, health
            FROM
                dbc_creaturefamily f
            LEFT JOIN
                ?_icons ic ON ic.name = LOWER(SUBSTRING_INDEX(f.iconString, "\\\\", -1))
            WHERE
                petTalentType <> -1';

        $spawnQuery = '
            SELECT
                ct.family AS ARRAY_KEY,
                MIN(ct.minlevel) AS minLevel,
                MAX(ct.maxlevel) AS maxLevel,
                IF(ct.type_flags & 0x10000, 1, 0) AS exotic
            FROM
                creature_template ct
            JOIN
                creature c ON ct.entry = c.id
            WHERE
                ct.type_flags & 0x1
            GROUP BY
                ct.family';

        $bonusQuery = '
            UPDATE
                ?_pet p,
                dbc_skilllineability sla,
                dbc_spell s
            SET
                armor  = s.effect2BasePoints + s.effect2DieSides,
                damage = s.effect1BasePoints + s.effect1DieSides,
                health = s.effect3BasePoints + s.effect3DieSides
            WHERE
                p.skillLineId = sla.skillLineId AND
                sla.spellId   = s.id AND
                s.name_loc0   = "Tamed Pet Passive (DND)"';

        $spellQuery = '
            SELECT
                p.id,
                MAX(s.id) AS spell
            FROM
                dbc_skilllineability sla
            JOIN
                ?_pet p ON p.skillLineId = sla.skillLineId
            JOIN
                dbc_spell s ON sla.spellId = s.id
            LEFT OUTER JOIN
                dbc_talent t ON s.id  = t.rank1
            WHERE
                (s.attributes0 & 0x40) = 0 AND
                t.id IS NULL
            GROUP BY
                s.name_loc0, p.id';

        // basic copy from creaturefamily.dbc
        DB::Aowow()->query($baseQuery);

        // stats from craeture_template
        $spawnInfo = DB::World()->query($spawnQuery);
        foreach ($spawnInfo as $id => $info)
            DB::Aowow()->query('UPDATE ?_pet SET ?a WHERE id = ?d', $info, $id);

        // add petFamilyModifier to health, mana, dmg
        DB::Aowow()->query($bonusQuery);

        // add expansion manually
        DB::Aowow()->query('UPDATE ?_pet SET expansion = 1 WHERE id IN (30, 31, 32, 33, 34)');
        DB::Aowow()->query('UPDATE ?_pet SET expansion = 2 WHERE id IN (37, 38, 39, 41, 42, 43, 44, 45, 46)');

        // assign pet spells
        $pets = DB::Aowow()->select($spellQuery);
        $res  = [];

        foreach ($pets as $set)                             // convert to usable structure
        {
            if (!isset($res[$set['id']]))
                $res[$set['id']] = [];

            $res[$set['id']]['spellId'.(count($res[$set['id']]) + 1)] = $set['spell'];
        }

        foreach ($res as $pId => $row)
            DB::Aowow()->query('UPDATE ?_pet SET ?a WHERE id = ?d', $row, $pId);

        return true;
    }
});

?>
