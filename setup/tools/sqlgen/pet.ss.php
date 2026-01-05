<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;

    protected $info = array(
        'pet' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Pet from dbc and world db.']
    );

    protected $dbcSourceFiles     = ['talent', 'spell', 'skilllineability', 'creaturefamily'];
    protected $worldDependency    = ['creature_template', 'creature'];
    protected $setupAfter         = [['icons'], []];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::pet');

        // basic copy from creaturefamily.dbc
        DB::Aowow()->qry(
           'INSERT INTO ::pet
            SELECT      f.`id`,
                        `categoryEnumId`,
                        0,                                  -- cuFlags
                        0,                                  -- minLevel
                        0,                                  -- maxLevel
                        `petFoodMask`,
                        `petTalentType`,
                        0,                                  -- exotic
                        0,                                  -- expansion
                        `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`,
                        ic.`id`,
                        `skillLine1`,
                        0, 0, 0, 0,                         -- spell[1-4]
                        0, 0, 0                             -- armor, damage, health
            FROM        dbc_creaturefamily f
            LEFT JOIN   ::icons ic ON ic.`name_source` = LOWER(SUBSTRING_INDEX(f.`iconString`, "\\", -1))
            WHERE       `petTalentType` <> -1'
        );

        // stats from craeture_template
        $spawnInfo = DB::World()->selectAssoc(
           'SELECT   ct.`family`                    AS ARRAY_KEY,
                     MIN(ct.`minlevel`)             AS "minLevel",
                     MAX(ct.`maxlevel`)             AS "maxLevel",
                     IF(ct.`type_flags` & %i, 1, 0) AS "exotic"
            FROM     creature_template ct
            JOIN     creature c ON ct.`entry` = c.`id`
            WHERE    ct.`type_flags` & %i
            GROUP BY ct.`family`',
            NPC_TYPEFLAG_EXOTIC_PET, NPC_TYPEFLAG_TAMEABLE
        );
        foreach ($spawnInfo as $id => $info)
            DB::Aowow()->qry('UPDATE ::pet SET %a WHERE id = %i', $info, $id);

        // add petFamilyModifier to health, mana, dmg
        DB::Aowow()->qry(
           'UPDATE ::pet p,
                   dbc_skilllineability sla,
                   dbc_spell s
            SET    `armor`  = s.`effect2BasePoints` + s.`effect2DieSides`,
                   `damage` = s.`effect1BasePoints` + s.`effect1DieSides`,
                   `health` = s.`effect3BasePoints` + s.`effect3DieSides`
            WHERE  p.`skillLineId` = sla.`skillLineId` AND
                   sla.`spellId`   = s.`id` AND
                   s.`name_loc0`   = "Tamed Pet Passive (DND)"'
        );

        // assign pet spells
        $pets = DB::Aowow()->selectAssoc(
           'SELECT          p.`id`, MAX(s.`id`) AS "spell"
            FROM            dbc_skilllineability sla
            JOIN            ::pet p      ON p.`skillLineId` = sla.`skillLineId`
            JOIN            dbc_spell s  ON sla.`spellId` = s.`id`
            LEFT OUTER JOIN dbc_talent t ON s.`id`  = t.`rank1`
            WHERE           (s.`attributes0` & %i) = 0 AND t.`id` IS NULL
            GROUP BY        s.`name_loc0`, p.`id`',
            SPELL_ATTR0_PASSIVE
        );

        $petSpells = [];
        foreach ($pets as $pet)                             // convert to usable structure
        {
            if (!isset($petSpells[$pet['id']]))
            $petSpells[$pet['id']] = [];

            $petSpells[$pet['id']]['spellId'.(count($petSpells[$pet['id']]) + 1)] = $pet['spell'];
        }

        foreach ($petSpells as $petId => $row)
            DB::Aowow()->qry('UPDATE ::pet SET %a WHERE `id` = %i', $row, $petId);

        $this->reapplyCCFlags('pet', Type::PET);

        return true;
    }
});

?>
