<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * creature_template
 * locales_creature
 * creature_classlevelstats
 * instance_encounters
*/


$customData = array(
);
$reqDBC = ['creaturedisplayinfo', 'creaturedisplayinfoextra'];

function creature(array $ids = [])
{
    $baseQuery = '
        SELECT
            ct.entry,
            IF(ie.entry IS NULL, 0, ?d) AS cuFlags,          -- cuFlags
            difficulty_entry_1, difficulty_entry_2, difficulty_entry_3,
            KillCredit1, KillCredit2,
            modelid1, modelid2, modelid3, modelid4,
            "" AS textureString,                            -- textureString
            0 AS modelId,                                   -- modelId
            "" AS iconString,                               -- iconString
            name, IFNULL(name_loc2, ""), IFNULL(name_loc3, ""), IFNULL(name_loc6, ""), IFNULL(name_loc8, ""),
            subname, IFNULL(subname_loc2, ""), IFNULL(subname_loc3, ""), IFNULL(subname_loc6, ""), IFNULL(subname_loc8, ""),
            minLevel, maxLevel,
            exp,
            faction,
            npcflag,
            rank,
            dmgSchool,
            DamageModifier,
            BaseAttackTime,
            RangeAttackTime,
            BaseVariance,
            RangeVariance,
            unit_class,
            unit_flags, unit_flags2, dynamicflags,
            family,
            trainer_type,
            trainer_spell,
            trainer_class,
            trainer_race,
            (CASE ct.exp WHEN 0 THEN min.damage_base WHEN 1 THEN min.damage_exp1 ELSE min.damage_exp2 END) AS dmgMin,
            (CASE ct.exp WHEN 0 THEN max.damage_base WHEN 1 THEN max.damage_exp1 ELSE max.damage_exp2 END) AS dmgMax,
            min.attackpower AS mleAtkPwrMin,
            max.attackpower AS mleAtkPwrMax,
            min.rangedattackpower AS rmgAtkPwrMin,
            max.rangedattackpower AS rmgAtkPwrMax,
            type,
            type_flags,
            lootid, pickpocketloot, skinloot,
            spell1, spell2, spell3, spell4, spell5, spell6, spell7, spell8,
            PetSpellDataId,
            VehicleId,
            mingold, maxgold,
            AIName,
            (CASE ct.exp WHEN 0 THEN min.basehp0 WHEN 1 THEN min.basehp1 ELSE min.basehp2 END) * ct.HealthModifier AS healthMin,
            (CASE ct.exp WHEN 0 THEN max.basehp0 WHEN 1 THEN max.basehp1 ELSE max.basehp2 END) * ct.HealthModifier AS healthMax,
            min.basemana  * ct.ManaModifier AS manaMin,
            max.basemana  * ct.ManaModifier AS manaMax,
            min.basearmor * ct.ArmorModifier AS armorMin,
            max.basearmor * ct.ArmorModifier AS armorMax,
            RacialLeader,
            questItem1, questItem2, questItem3, questItem4, questItem5, questItem6,
            mechanic_immune_mask,
            flags_extra,
            ScriptName
        FROM
            creature_template ct
        JOIN
            creature_classlevelstats min ON ct.unit_class = min.class AND ct.minlevel = min.level
        JOIN
            creature_classlevelstats max ON ct.unit_class = max.class AND ct.maxlevel = max.level
        LEFT JOIN
            locales_creature lc ON lc.entry = ct.entry
        LEFT JOIN
            instance_encounters ie ON ie.creditEntry = ct.entry AND ie.creditType = 0
        {
        WHERE
            ct.entry IN (?a)
        }
        LIMIT
           ?d, ?d';

    $dummyQuery = '
        UPDATE
            ?_creature a
        JOIN
        (
            SELECT b.difficultyEntry1 AS dummy FROM ?_creature b UNION
            SELECT c.difficultyEntry2 AS dummy FROM ?_creature c UNION
            SELECT d.difficultyEntry3 AS dummy FROM ?_creature d
        ) j
        SET
            a.cuFlags = a.cuFlags | ?d
        WHERE
            a.id = j.dummy';

    $displayInfoQuery = '
        UPDATE
            ?_creature c
        JOIN
            dbc_creaturedisplayinfo cdi ON c.displayId1 = cdi.id
        LEFT JOIN
            dbc_creaturedisplayinfoextra cdie ON cdi.extraInfoId = cdie.id
        SET
            c.textureString = IFNULL(cdie.textureString, cdi.skin1),
            c.modelId = cdi.modelId,
            c.iconString = cdi.iconString';

    $offset = 0;
    while ($npcs = DB::World()->select($baseQuery, NPC_CU_INSTANCE_BOSS, $ids ?: DBSIMPLE_SKIP, $offset, SqlGen::$stepSize))
    {
        CLISetup::log(' * sets '.($offset + 1).' - '.($offset + count($npcs)));

        $offset += SqlGen::$stepSize;

        foreach ($npcs as $npc)
            DB::Aowow()->query('REPLACE INTO ?_creature VALUES (?a)', array_values($npc));
    }

    // apply "textureString", "modelId" and "iconSring"
    DB::Aowow()->query($displayInfoQuery);

    // apply cuFlag: difficultyDummy
    DB::Aowow()->query($dummyQuery, NPC_CU_DIFFICULTY_DUMMY | CUSTOM_EXCLUDE_FOR_LISTVIEW);

    // apply cuFlag: excludeFromListview [for trigger-creatures]
    DB::Aowow()->query('UPDATE ?_creature SET cuFlags = cuFlags | ?d WHERE flagsExtra & ?d', CUSTOM_EXCLUDE_FOR_LISTVIEW, 0x80);

    return true;
}

?>
