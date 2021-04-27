<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'spell';

    protected $tblDependencyAowow = ['icons'];
    protected $tblDependencyTC    = ['item_template', 'creature_template', 'creature_template_addon', 'smart_scripts', 'trainer_spell', 'disables', 'spell_ranks', 'spell_dbc', 'skill_discovery_template'];
    protected $dbcSourceFiles     = ['spell', 'spellradius', 'spellduration', 'spellrunecost', 'spellcasttimes', 'skillline', 'skilllineability', 'skillraceclassinfo', 'talent', 'talenttab', 'glyphproperties', 'spellicon'];

    public function generate(array $ids = []) : bool
    {
        $ssQuery = '
            SELECT
                id AS ARRAY_KEY,
                id,
                0 AS category,
                DispelType AS Dispel,
                Mechanic,
                Attributes,         AttributesEx,       AttributesExB,      AttributesExC,      AttributesExD,      AttributesExE,      AttributesExF,      AttributesExG,
                ShapeshiftMask AS Stances,     ShapeshiftExclude AS StancesNot,
                Targets,
                0 AS spellFocus,
                CastingTimeIndex,
                0 AS recoveryTime,  0 AS recoveryTimeCategory,
                ProcChance,         ProcCharges,
                MaxLevel,           BaseLevel,          SpellLevel,
                DurationIndex,
                0 AS powerType,
                0 AS powerCost,
                0 AS powerCostPerLevel,
                0 AS powerPerSecond,
                0 AS powerPerSecondPerLevel,
                RangeIndex,
                StackAmount,
                0 AS tool1,         0 AS tool2,
                0 AS reagent1,      0 AS reagent2,      0 AS reagent3,      0 AS reagent4,      0 AS reagent5,      0 AS reagent6,      0 AS reagent7,      0 AS reagent8,
                0 AS reagentCount1, 0 AS reagentCount2, 0 AS reagentCount3, 0 AS reagentCount4, 0 AS reagentCount5, 0 AS reagentCount6, 0 AS reagentCount7, 0 AS reagentCount8,
                EquippedItemClass,
                EquippedItemSubClassMask,
                EquippedItemInventoryTypeMask,
                Effect1,                                Effect2,                                Effect3,
                EffectDieSides1,                        EffectDieSides2,                        EffectDieSides3,
                EffectRealPointsPerLevel1,              EffectRealPointsPerLevel2,              EffectRealPointsPerLevel3,
                EffectBasePoints1,                      EffectBasePoints2,                      EffectBasePoints3,
                EffectMechanic1,                        EffectMechanic2,                        EffectMechanic3,
                EffectImplicitTargetA1,                 EffectImplicitTargetA2,                 EffectImplicitTargetA3,
                EffectImplicitTargetB1,                 EffectImplicitTargetB2,                 EffectImplicitTargetB3,
                EffectRadiusIndex1,                     EffectRadiusIndex2,                     EffectRadiusIndex3,
                EffectApplyAuraName1,                   EffectApplyAuraName2,                   EffectApplyAuraName3,
                EffectAmplitude1,                       EffectAmplitude2,                       EffectAmplitude3,
                EffectMultipleValue1,                   EffectMultipleValue2,                   EffectMultipleValue3,
                0 AS effect1ChainTarget,                0 AS effect2ChainTarget,                0 AS effect3ChainTarget,
                EffectItemType1,                        EffectItemType2,                        EffectItemType3,
                EffectMiscValue1,                       EffectMiscValue2,                       EffectMiscValue3,
                EffectMiscValueB1,                      EffectMiscValueB2,                      EffectMiscValueB3,
                EffectTriggerSpell1,                    EffectTriggerSpell2,                    EffectTriggerSpell3,
                0 AS effect1PointsPerComboPoint,        0 AS effect2PointsPerComboPoint,        0 AS effect3PointsPerComboPoint,
                EffectSpellClassMaskA1,                 EffectSpellClassMaskA2,                 EffectSpellClassMaskA3,
                EffectSpellClassMaskB1,                 EffectSpellClassMaskB2,                 EffectSpellClassMaskB3,
                EffectSpellClassMaskC1,                 EffectSpellClassMaskC2,                 EffectSpellClassMaskC3,
                0 AS iconId,                            0 AS iconIdAlt,
                0 AS rankId,                            0 AS spellVisualId1,
                CONCAT("Serverside - ",SpellName) AS name_loc0,CONCAT("Serverside - ",SpellName) AS name_loc2,CONCAT("Serverside - ",SpellName) AS name_loc3,CONCAT("Serverside - ",SpellName) AS name_loc4,CONCAT("Serverside - ",SpellName) AS name_loc6,CONCAT("Serverside - ",SpellName) AS name_loc8,
                "" AS rank_loc0,                        "" AS rank_loc2,                        "" AS rank_loc3,                        "" AS rank_loc4,                        "" AS rank_loc6,                        "" AS rank_loc8,
                "" AS description_loc0,                 "" AS description_loc2,                 "" AS description_loc3,                 "" AS description_loc4,                 "" AS description_loc6,                 "" AS description_loc8,
                "" AS buff_loc0,                        "" AS buff_loc2,                        "" AS buff_loc3,                        "" AS buff_loc4,                        "" AS buff_loc6,                        "" AS buff_loc8,
                0 AS powerCostPercent,
                0 AS startRecoveryCategory,
                0 AS startRecoveryTime,
                MaxTargetLevel,
                SpellFamilyName,
                SpellFamilyFlags1,
                SpellFamilyFlags2,
                SpellFamilyFlags3,
                MaxAffectedTargets,
                DmgClass,
                DmgMultiplier1,                         DmgMultiplier2,                         DmgMultiplier3,
                0 AS toolCategory1,                     0 AS toolCategory2,
                SchoolMask,
                0 AS runeCostId,
                0 AS powerDisplayId,
                0 AS effect1BonusMultiplier,            0 AS effect2BonusMultiplier,            0 AS effect3BonusMultiplier,
                0 AS spellDescriptionVariable,
                0 AS spellDifficulty
            FROM
                spell_dbc
            WHERE
                id > ?d
            LIMIT
                ?d';

        $baseQuery = '
            SELECT
                s.id,
                category,
                dispelType,
                mechanic,
                attributes0,        attributes1,        attributes2,        attributes3,        attributes4,        attributes5,        attributes6,        attributes7,
                0 AS cuFlags,
                0 AS typeCat,
                stanceMask,         stanceMaskNot,
                targets,
                spellFocus,
                IFNULL(sct.baseTime, 0) / 1000 AS castTime,
                recoveryTime,       recoveryTimeCategory,
                startRecoveryTime,  startRecoveryCategory,
                procChance,         procCharges,
                0 AS procCustom,    0 AS procCooldown,
                maxLevel,           baseLevel,          spellLevel,         0 AS talentLevel,
                IF (sd.baseTime <> -1, ABS(sd.baseTime), -1) AS duration,
                IF (powerDisplayId, -powerDisplayId, powerType) AS powerType,
                powerCost,
                powerCostPerLevel,
                powerCostPercent,
                powerPerSecond,
                powerPerSecondPerLevel,
                IFNULL (src.runicPowerGain, 0) AS powerGainRunicPower,
                IF (src.id IS NULL, 0, (src.costFrost << 8) | (src.costUnholy << 4) | src.costBlood) AS powerCostRunes,
                rangeId,
                stackAmount,
                tool1,              tool2,
                toolCategory1,      toolCategory2,
                reagent1,           reagent2,           reagent3,           reagent4,           reagent5,           reagent6,           reagent7,           reagent8,
                reagentCount1,      reagentCount2,      reagentCount3,      reagentCount4,      reagentCount5,      reagentCount6,      reagentCount7,      reagentCount8,
                equippedItemClass,
                equippedItemSubClassMask,
                equippedItemInventoryTypeMask,
                effect1Id,                              effect2Id,                              effect3Id,
                effect1DieSides,                        effect2DieSides,                        effect3DieSides,
                effect1RealPointsPerLevel,              effect2RealPointsPerLevel,              effect3RealPointsPerLevel,
                effect1BasePoints,                      effect2BasePoints,                      effect3BasePoints,
                effect1Mechanic,                        effect2Mechanic,                        effect3Mechanic,
                effect1ImplicitTargetA,                 effect2ImplicitTargetA,                 effect3ImplicitTargetA,
                effect1ImplicitTargetB,                 effect2ImplicitTargetB,                 effect3ImplicitTargetB,
                    IFNULL (sr1.radiusMin, 0) AS effect1RadiusMin,      IFNULL (sr1.radiusMax, 0) AS effect1RadiusMax,
                    IFNULL (sr2.radiusMin, 0) AS effect2RadiusMin,      IFNULL (sr2.radiusMax, 0) AS effect2RadiusMax,
                    IFNULL (sr3.radiusMin, 0) AS effect3RadiusMin,      IFNULL (sr3.radiusMax, 0) AS effect3RadiusMax,
                effect1AuraId,                          effect2AuraId,                          effect3AuraId,
                effect1Periode,                         effect2Periode,                         effect3Periode,
                effect1ValueMultiplier,                 effect2ValueMultiplier,                 effect3ValueMultiplier,
                effect1ChainTarget,                     effect2ChainTarget,                     effect3ChainTarget,
                effect1CreateItemId,                    effect2CreateItemId,                    effect3CreateItemId,
                effect1MiscValue,                       effect2MiscValue,                       effect3MiscValue,
                effect1MiscValueB,                      effect2MiscValueB,                      effect3MiscValueB,
                effect1TriggerSpell,                    effect2TriggerSpell,                    effect3TriggerSpell,
                effect1PointsPerComboPoint,             effect2PointsPerComboPoint,             effect3PointsPerComboPoint,
                effect1SpellClassMaskA,                 effect2SpellClassMaskA,                 effect3SpellClassMaskA,
                effect1SpellClassMaskB,                 effect2SpellClassMaskB,                 effect3SpellClassMaskB,
                effect1SpellClassMaskC,                 effect2SpellClassMaskC,                 effect3SpellClassMaskC,
                effect1DamageMultiplier,                effect2DamageMultiplier,                effect3DamageMultiplier,
                effect1BonusMultiplier,                 effect2BonusMultiplier,                 effect3BonusMultiplier,
                0 AS iconId,                            iconId AS iconIdBak,                    0 AS iconIdAlt,
                0 AS rankId,                            spellVisualId1,
                name_loc0,          name_loc2,          name_loc3,          name_loc4,          name_loc6,          name_loc8,
                rank_loc0,          rank_loc2,          rank_loc3,          rank_loc4,          rank_loc6,          rank_loc8,
                description_loc0,   description_loc2,   description_loc3,   description_loc4,   description_loc6,   description_loc8,
                buff_loc0,          buff_loc2,          buff_loc3,          buff_loc4,          buff_loc6,          buff_loc8,
                maxTargetLevel,
                spellFamilyId,
                spellFamilyFlags1,                      spellFamilyFlags2,                      spellFamilyFlags3,
                maxAffectedTargets,
                damageClass,
                0 AS skillLine1,
                0 AS skillLine2OrMask,
                0 AS reqRaceMask,
                0 AS reqClassMask,
                0 AS reqSpellId,
                0 AS reqSkillLevel,
                0 AS learnedAt,
                0 AS skillLevelGrey,
                0 AS skillLevelYellow,
                schoolMask,
                spellDescriptionVariable,
                0 AS trainingCost
            FROM
                dbc_spell s
            LEFT JOIN
                dbc_spellcasttimes sct ON s.castTimeId      = sct.id
            LEFT JOIN
                dbc_spellrunecost  src ON s.runeCostId      = src.id
            LEFT JOIN
                dbc_spellduration   sd ON s.durationId      = sd.id
            LEFT JOIN
                dbc_spellradius    sr1 ON s.effect1RadiusId = sr1.id
            LEFT JOIN
                dbc_spellradius    sr2 ON s.effect2RadiusId = sr2.id
            LEFT JOIN
                dbc_spellradius    sr3 ON s.effect3RadiusId = sr3.id
            WHERE
                s.id > ?d
            LIMIT
                ?d';

        $serverside = [];

        // merge serverside spells into dbc_spell (should not affect other scripts)
        $lastMax = 0;
        CLI::write(' - merging serverside spells into spell.dbc');
        while ($spells = DB::World()->select($ssQuery, $lastMax, SqlGen::$sqlBatchSize))
        {
            $newMax = max(array_column($spells, 'id'));

            CLI::write(' * sets '.($lastMax + 1).' - '.$newMax);

            $lastMax = $newMax;

            foreach ($spells as $id => $spell)
            {
                $serverside[] = $id;
                DB::Aowow()->query('REPLACE INTO dbc_spell VALUES (?a)', array_values($spell));
            }
        }

        // merge everything into aowow_spell
        $lastMax = 0;
        CLI::write(' - filling aowow_spell');
        while ($spells = DB::Aowow()->select($baseQuery, $lastMax, SqlGen::$sqlBatchSize))
        {
            $newMax = max(array_column($spells, 'id'));

            CLI::write(' * sets '.($lastMax + 1).' - '.$newMax);

            $lastMax = $newMax;

            foreach ($spells as $spell)
                DB::Aowow()->query('REPLACE INTO ?_spell VALUES (?a)', array_values($spell));
        }

        // apply flag: CUSTOM_SERVERSIDE
        if ($serverside)
            DB::Aowow()->query('UPDATE ?_spell SET cuFlags = cuFlags | ?d WHERE id IN (?a)', CUSTOM_SERVERSIDE, $serverside);

        // apply flag: CUSTOM_DISABLED [0xD: players (0x1), pets (0x4), general (0x8); only generally disabled spells]
        if ($disables = DB::World()->selectCol('SELECT entry FROM disables WHERE sourceType = 0 AND params_0 = "" AND params_1 = "" AND flags & 0xD'))
            DB::Aowow()->query('UPDATE ?_spell SET cuFlags = cuFlags | ?d WHERE id IN (?a)', CUSTOM_DISABLED, $disables);

        // apply spell ranks (can't use skilllineability.dbc, as it does not contain ranks for non-player/pet spells)
        $ranks = DB::World()->selectCol('SELECT first_spell_id AS ARRAY_KEY, spell_id AS ARRAY_KEY2, `rank` FROM spell_ranks');
        foreach ($ranks as $firstSpell => $sets)
        {
            // apply flag: SPELL_CU_FIRST_RANK
            DB::Aowow()->query('UPDATE ?_spell SET cuFlags = cuFlags | ?d WHERE id = ?d', SPELL_CU_FIRST_RANK, $firstSpell);

            foreach ($sets as $spell => $rank)
                DB::Aowow()->query('UPDATE ?_spell SET rankNo = ?d WHERE id = ?d', $rank, $spell);

            // apply flag: SPELL_CU_LAST_RANK
            end($sets);
            DB::Aowow()->query('UPDATE ?_spell SET cuFlags = cuFlags | ?d WHERE id = ?d', SPELL_CU_LAST_RANK, key($sets));
        }


        /*************************************/
        /* merge SkillLineAbility into Spell */
        /*************************************/

        /* acquireMethod
            ABILITY_LEARNED_ON_GET_PROFESSION_SKILL     = 1,        learnedAt = 1
            ABILITY_LEARNED_ON_GET_RACE_OR_CLASS_SKILL  = 2         not used for now
        */

        CLI::write(' - linking with skillineability');

        $results  = DB::Aowow()->select('SELECT spellId AS ARRAY_KEY, id AS ARRAY_KEY2, skillLineId, reqRaceMask, reqClassMask, reqSkillLevel, acquireMethod, skillLevelGrey, skillLevelYellow FROM dbc_skilllineability sla');
        foreach ($results as $spellId => $sets)
        {
            $names   = array_keys(current($sets));
            $lines   = [];
            $trainer = false;
            $update  = array(
                'skillLine1'       => 0,
                'skillLine2OrMask' => 0,
                'reqRaceMask'      => 0,
                'reqClassMask'     => 0,
                'reqSkillLevel'    => 0,
                'skillLevelGrey'   => 0,
                'skillLevelYellow' => 0
            );

            foreach ($sets as $set)
            {
                $i = 0;
                while (isset($names[$i]))
                {
                    $field = $set[$names[$i]];
                    switch ($names[$i])
                    {
                        case 'acquireMethod':
                            if ($field == 1)
                                $trainer = true;
                            break;
                        case 'skillLineId':                 // array
                            if (!in_array($field, $lines))
                                $lines[] = $field;
                            break;
                        case 'reqRaceMask':                 // mask
                        case 'reqClassMask':
                            if (((int)$update[$names[$i]] & (int)$field) != $field)
                                (int)$update[$names[$i]] |= (int)$field;
                            break;
                        case 'reqSkillLevel':               // max
                        case 'skillLevelYellow':
                        case 'skillLevelGrey':
                            if ($update[$names[$i]] < $field)
                                $update[$names[$i]] = $field;
                            break;
                    }
                    $i++;
                }
            }

            if ($trainer)
                DB::Aowow()->query('UPDATE ?_spell SET learnedAt = 1 WHERE id = ?d', $spellId);

            // check skillLineId against mask
            switch (count($lines))
            {
                case 2:
                    $update['skillLine2OrMask'] = $lines[1];
                case 1:
                    $update['skillLine1'] = $lines[0];
                    break;
                default:
                    for ($i = -count(Game::$skillLineMask); $i < 0; $i++)
                    {
                        foreach (Game::$skillLineMask[$i] as $k => $pair)
                        {
                            if (in_array($pair[1], $lines))
                            {
                                $update['skillLine1']        = $i;
                                $update['skillLine2OrMask'] |= 1 << $k;
                            }
                        }
                    }
            }

            DB::Aowow()->query('UPDATE ?_spell SET ?a WHERE id = ?d', $update, $spellId);
        }

        // fill learnedAt, trainingCost from trainer
        if ($trainer = DB::World()->select('SELECT SpellID AS ARRAY_KEY, MIN(ReqSkillRank) AS reqSkill, MIN(MoneyCost) AS cost, ReqAbility1 AS reqSpellId, COUNT(*) AS count FROM trainer_spell GROUP BY SpellID'))
        {
            $spells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a)', array_keys($trainer));
            $links  = [];

            // todo (med): this skips some spells (e.g. riding)
            foreach ($trainer as $spell => $tData)
            {
                if (!isset($spells[$spell]))
                    continue;

                $triggered = false;
                $effects   = $spells[$spell];

                for ($i = 1; $i <= 3; $i++)
                {
                    if ($effects['effect'.$i.'Id'] != 36)   // effect: learnSpell
                        continue;

                    $triggered = true;

                    $l = &$links[$effects['effect'.$i.'TriggerSpell']];

                    if (!isset($l))
                        $l = [$tData['reqSkill'], $tData['cost'], $tData['reqSpellId']];

                    if ($tData['reqSkill'] < $l[0])
                        $l[0] = $tData['reqSkill'];

                    if ($tData['cost'] < $l[1])
                        $l[1] = $tData['cost'];
                }

                if (!$triggered)
                {
                    $l = &$links[$spell];

                    if (!isset($l))
                        $l = [$tData['reqSkill'], $tData['cost'], $tData['reqSpellId']];

                    if ($tData['reqSkill'] < $l[0])
                        $l[0] = $tData['reqSkill'];

                    if ($tData['cost'] < $l[1])
                        $l[1] = $tData['cost'];
                }
            }

            foreach ($links as $spell => $link)
                DB::Aowow()->query("UPDATE ?_spell s SET s.learnedAt = ?d, s.trainingCost = ?d WHERE s.id = ?d", $link[0], $link[1], $spell);
        }

        // fill learnedAt from recipe-items
        $recipes = DB::World()->selectCol('SELECT IF(spelltrigger_2 = 6, spellid_2, spellid_1) AS ARRAY_KEY, MIN(RequiredSkillRank) FROM item_template WHERE `class` = 9 AND spelltrigger_1 <> 1 AND RequiredSkillRank > 0 GROUP BY ARRAY_KEY');
        foreach ($recipes as $spell => $reqSkill)
            DB::Aowow()->query('UPDATE ?_spell SET learnedAt = IF(learnedAt = 0 OR learnedAt > ?d, ?d, learnedAt) WHERE id = ?d', $reqSkill, $reqSkill, $spell);

        // fill learnedAt from Discovery
            // 61756: Northrend Inscription Research (FAST QA VERSION);
            // 64323: Book of Glyph Mastery (todo: get reqSkill from item [425])
            // 28571 - 28576: $element Protection Potion (todo: get reqSkill from teaching spell [360])
        $discovery = DB::World()->selectCol('
            SELECT spellId AS ARRAY_KEY,
                    IF(reqSpell = ?d, ?d,
                        IF(reqSpell BETWEEN ?d AND ?d, ?d,
                            IF(reqSkillValue, reqSkillValue, 1)))
            FROM skill_discovery_template WHERE reqSpell NOT IN (?a)', 64323, 425, 28571, 28576, 360, [61756]);
        foreach ($discovery as $spell => $reqSkill)
            DB::Aowow()->query('UPDATE ?_spell SET learnedAt = ?d WHERE id = ?d', $reqSkill, $spell);

        // calc reqSkill for gethering-passives (herbing, mining, skinning) (on second thought .. it is set in skilllineability >.<)
        $sets = DB::World()->selectCol('SELECT spell_id AS ARRAY_KEY, `rank` * 75 AS reqSkill FROM spell_ranks WHERE first_spell_id IN (?a)', [55428, 53120, 53125]);
        foreach ($sets as $spell => $reqSkill)
            DB::Aowow()->query('UPDATE ?_spell SET learnedAt = ?d WHERE id = ?d', $reqSkill, $spell);


        /******************/
        /* talent related */
        /******************/

        CLI::write(' - linking with talent');

        for ($i = 1; $i < 6; $i++)
        {
            // classMask
            DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t, dbc_talenttab tt SET s.reqClassMask = tt.classMask WHERE tt.creatureFamilyMask = 0 AND tt.id = t.tabId AND t.rank?d = s.id', $i);
            // talentLevel
            DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t, dbc_talenttab tt SET s.talentLevel = (t.row *  5) + 10 + (?d * 1) WHERE tt.id = t.tabId AND tt.creatureFamilyMask  = 0 AND t.rank?d = s.id', $i - 1, $i);
            DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t, dbc_talenttab tt SET s.talentLevel = (t.row * 12) + 20 + (?d * 4) WHERE tt.id = t.tabId AND tt.creatureFamilyMask <> 0 AND t.rank?d = s.id', $i - 1, $i);
        }

        // passive talent
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.cuFlags = s.cuFlags | ?d WHERE t.talentSpell = 0 AND (s.id = t.rank1 OR s.id = t.rank2 OR s.id = t.rank3 OR s.id = t.rank4 OR s.id = t.rank5)', SPELL_CU_TALENT);

        // spell taught by talent
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.cuFlags = s.cuFlags | ?d WHERE t.talentSpell = 1 AND (s.id = t.rank1 OR s.id = t.rank2 OR s.id = t.rank3 OR s.id = t.rank4 OR s.id = t.rank5)', SPELL_CU_TALENTSPELL);


        /*********/
        /* Other */
        /*********/

        CLI::write(' - misc fixups & icons');

        // FU [FixUps]
        DB::Aowow()->query('UPDATE ?_spell SET reqRaceMask  = ?d WHERE skillLine1 = ?d', 1 << 10, 760);      // Draenai Racials
        DB::Aowow()->query('UPDATE ?_spell SET reqRaceMask  = ?d WHERE skillLine1 = ?d', 1 <<  9, 756);      // Bloodelf Racials
        DB::Aowow()->query('UPDATE ?_spell SET reqClassMask = ?d WHERE id         = ?d', 1 <<  7, 30449);    // Mage - Spellsteal

        // triggered by spell
        DB::Aowow()->query('
            UPDATE
                ?_spell a
            JOIN (
                SELECT effect1TriggerSpell as id FROM ?_spell WHERE effect1Id NOT IN (36, 57, 133) AND effect1TriggerSpell <> 0 UNION
                SELECT effect2TriggerSpell as id FROM ?_spell WHERE effect2Id NOT IN (36, 57, 133) AND effect2TriggerSpell <> 0 UNION
                SELECT effect3TriggerSpell as id FROM ?_spell WHERE effect3Id NOT IN (36, 57, 133) AND effect3TriggerSpell <> 0
            ) as b
            SET
                cuFlags = cuFlags | ?d
            WHERE a.id = b.id',
            SPELL_CU_TRIGGERED);

        // altIcons and quality for craftSpells
        $itemSpells = DB::Aowow()->selectCol('
            SELECT    s.id AS ARRAY_KEY, effect1CreateItemId
            FROM      dbc_spell s
            LEFT JOIN dbc_talent t1 ON t1.rank1 = s.id
            LEFT JOIN dbc_talent t2 ON t2.rank2 = s.id
            LEFT JOIN dbc_talent t3 ON t3.rank3 = s.id
            WHERE     effect1CreateItemId > 0 AND effect1Id <> 53 AND t1.id IS NULL AND t2.id IS NULL AND t3.id IS NULL
        ');                                                     // no enchant-spells & no talents!
        $itemInfo   = DB::World()->select('SELECT entry AS ARRAY_KEY, displayId AS d, Quality AS q FROM item_template WHERE entry IN (?a)', $itemSpells);
        foreach ($itemSpells as $sId => $itemId)
            if (isset($itemInfo[$itemId]))
                DB::Aowow()->query('UPDATE ?_spell s, ?_icons ic, dbc_spellicon si SET s.iconIdAlt = ?d, s.cuFlags = s.cuFlags | ?d WHERE s.iconIdBak = si.id AND ic.name = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1)) AND s.id = ?d', -$itemInfo[$itemId]['d'], ((7 - $itemInfo[$itemId]['q']) << 8), $sId);

        $itemReqs = DB::World()->selectCol('SELECT entry AS ARRAY_KEY, requiredSpell FROM item_template WHERE requiredSpell NOT IN (?a)', [0, 34090, 34091]); // not riding
        foreach ($itemReqs AS $itemId => $req)
            DB::Aowow()->query('UPDATE ?_spell SET reqSpellId = ?d WHERE skillLine1 IN (?a) AND effect1CreateItemId = ?d', $req, [164, 165, 197, 202], $itemId);

        // setting icons
        DB::Aowow()->query('UPDATE ?_spell s, ?_icons ic, dbc_spellicon si SET s.iconId = ic.id WHERE s.iconIdBak = si.id AND ic.name = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1))');

        // hide internal stuff from listviews
        // QA*; *DND*; square brackets anything; *(NYI)*; *(TEST)*
        // cant catch raw: NYI (uNYIelding); PH (PHasing)
        DB::Aowow()->query('UPDATE ?_spell SET cuFlags = cuFlags | ?d WHERE name_loc0 LIKE "QA%" OR name_loc0 LIKE "%DND%" OR name_loc0 LIKE "%[%" OR name_loc0 LIKE "%(NYI)%" OR name_loc0 LIKE "%(TEST)%"', CUSTOM_EXCLUDE_FOR_LISTVIEW);


        /**************/
        /* Categories */
        /**************/

        CLI::write(' - applying categories');

        // player talents (-2)
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.typeCat = -2 WHERE t.tabId NOT IN (409, 410, 411) AND (s.id = t.rank1 OR s.id = t.rank2 OR s.id = t.rank3 OR s.id = t.rank4 OR s.id = t.rank5)');

        // pet spells (-3)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -3 WHERE (s.cuFlags & 0x3) = 0 AND s.skillline1 IN (?a)',
            array_merge(
                array_column(Game::$skillLineMask[-1], 1),  // hunter pets
                array_column(Game::$skillLineMask[-2], 1),  // warlock pets
                [270, 782],                                 // hunter generic, DK - Ghoul
                [-1, -2]                                    // super categories
            )
        );

        // racials (-4)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -4 WHERE s.skillLine1 IN (101, 124, 125, 126, 220, 733, 753, 754, 756, 760)');

        // mounts (-5)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -5 WHERE s.effect1AuraId = 78 AND (s.skillLine1 IN (354, 594, 772, 777) OR (s.skillLine1 > 0 AND s.skillLine2OrMask = 777))');

        // companions (-6)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -6 WHERE s.skillLine1 = 778');

        // pet talents (-7)
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.typeCat = -7, s.cuFlags = s.cuFlags | 0x10 WHERE t.tabId = 409 AND (s.id = t.rank1 OR s.id = t.rank2 OR s.id = t.rank3)');
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.typeCat = -7, s.cuFlags = s.cuFlags | 0x08 WHERE t.tabId = 410 AND (s.id = t.rank1 OR s.id = t.rank2 OR s.id = t.rank3)');
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.typeCat = -7, s.cuFlags = s.cuFlags | 0x20 WHERE t.tabId = 411 AND (s.id = t.rank1 OR s.id = t.rank2 OR s.id = t.rank3)');

        // internal (-9) by faaaaaar not complete
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -9 WHERE s.skillLine1 = 769');
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -9 WHERE s.typeCat = 0 AND s.cuFlags = 0 AND (
            s.name_loc0 LIKE "%qa%"       OR
            s.name_loc0 LIKE "%debug%"    OR
            s.name_loc0 LIKE "%internal%" OR
            s.name_loc0 LIKE "%(NYI)%"    OR
            s.name_loc0 LIKE "%(TEST)%"   OR
            s.name_loc0 LIKE "%(OLD)%")'
        );

        // proficiencies (-11)
        DB::Aowow()->query('UPDATE ?_spell s, dbc_skillline sl SET s.typeCat = -11 WHERE s.skillLine1 = sl.id AND sl.categoryId IN (6, 8, 10)');

        // glyphs (-13)
        DB::Aowow()->query('UPDATE ?_spell s, dbc_glyphproperties gp SET s.cuFlags = s.cuFlags | IF(gp.typeFlags, ?d, ?d), s.typeCat = -13 WHERE gp.typeFlags IN (0, 1) AND gp.id = s.effect1MiscValue AND s.effect1Id = 74', SPELL_CU_GLYPH_MINOR, SPELL_CU_GLYPH_MAJOR);
        $glyphs = DB::World()->selectCol('SELECT it.spellid_1 AS ARRAY_KEY, it.AllowableClass FROM item_template it WHERE it.class = 16');
        foreach ($glyphs as $spell => $classMask)
            DB::Aowow()->query('UPDATE ?_spell s, dbc_glyphproperties gp SET s.reqClassMask = ?d WHERE gp.typeFlags IN (0, 1) AND gp.id = s.effect1MiscValue AND s.effect1Id = 74 AND s.id = ?d', $classMask, $spell);

        // class Spells (7)
        DB::Aowow()->query('UPDATE ?_spell s, dbc_skillline sl SET s.typeCat = 7 WHERE s.typeCat = 0 AND s.skillLine1 = sl.id AND sl.categoryId = 7');

        // hide some internal/unused stuffs
        DB::Aowow()->query('UPDATE ?_spell s SET s.cuFlags = ?d WHERE s.typeCat = 7 AND (
            s.name_loc0 LIKE "%passive%" OR s.name_loc0 LIKE "%effect%" OR s.name_loc0 LIKE "%improved%" OR s.name_loc0 LIKE "%prototype%" OR                                          -- can probably be extended
            (s.id NOT IN (47241, 59879, 59671) AND s.baseLevel <= 1 AND s.reqclassMask = 0)  OR                                                                                        -- can probably still be extended
            (s.SpellFamilyId = 15 AND s.SpellFamilyFlags1 & 0x2000 AND s.SpellDescriptionVariableId <> 84) OR                                                                          -- DK: Skill Coil
            (s.SpellFamilyId = 10 AND s.SpellFamilyFlags2 & 0x1000000 AND s.attributes1 = 0) OR                                                                                        -- Paladin: Bacon of Light hmm.. Bacon.... :]
            (s.SpellFamilyId = 6  AND s.SpellFamilyFlags3 & 0x4000) OR                                                                                                                 -- Priest: Lolwell Renew
            (s.SpellFamilyId = 6  AND s.SpellFamilyFlags1 & 0x8000000 AND s.rank_loc0 <> "") OR                                                                                        -- Priest: Bling Bling
            (s.SpellFamilyId = 8  AND s.attributes0 = 0x50 AND s.attributes1 & 0x400) OR                                                                                               -- Rogue: Intuition (dropped Talent..? looks nice though)
            (s.SpellfamilyId = 11 AND s.SpellFamilyFlags1 & 3 AND s.attributes1 = 1024) OR                                                                                             -- Shaman: Lightning Overload procs
            (s.attributes0 = 0x20000000 AND s.attributes3 = 0x10000000) OR                                                                                                             -- Master Demonologist (FamilyId = 0)
            s.id IN (47633, 22845, 29442, 31643, 44450, 32841, 20154, 34919, 27813, 27817, 27818, 30708, 30874, 379, 21169, 19483, 29886, 58889, 23885, 29841, 29842, 64380, 58427) OR -- Misc
            s.id IN (48954, 17567, 66175, 66122, 66123, 66124, 52374, 49575, 56816, 50536)                                                                                             -- Misc cont.
        )', CUSTOM_EXCLUDE_FOR_LISTVIEW);

        foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 11] as $classId)
            DB::Aowow()->query('
                UPDATE
                    ?_spell s,
                    dbc_skillline sl,
                    dbc_skillraceclassinfo srci
                SET
                    s.reqClassMask = srci.classMask
                WHERE
                    s.typeCat IN (-2, 7) AND
                    (s.attributes0 & 0x80) = 0 AND
                    s.skillLine1 = srci.skillLine AND
                    sl.categoryId = 7 AND
                    srci.skillline <> 769 AND
                    srci.skillline = sl.id AND
                    srci.flags & 0x90 AND
                    srci.classMask & ?d',
                1 << ($classId - 1)
            );

        // secondary Skills (9)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = 9 WHERE s.typeCat = 0 AND (s.skillLine1 IN (129, 185, 356, 762) OR (s.skillLine1 > 0 AND s.skillLine2OrMask IN (129, 185, 356, 762)))');

        // primary Skills (11)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = 11 WHERE s.typeCat = 0 AND s.skillLine1 IN (164, 165, 171, 182, 186, 197, 202, 333, 393, 755, 773)');

        // npc spells (-8) (run as last! .. missing from npc_scripts? "enum Spells { \s+(\w\d_)+\s+=\s(\d+) }" and "#define SPELL_(\d\w_)+\s+(\d+)") // RAID_MODE(1, 2[, 3, 4]) - macro still not considered
        $world = DB::World()->selectCol('
            SELECT ss.action_param1 FROM smart_scripts ss WHERE ss.action_type IN (11, 75, 85, 86) UNION
            SELECT cts.Spell FROM creature_template_spell cts'
        );

        $auras = DB::World()->selectCol('SELECT cta.auras FROM creature_template_addon cta WHERE auras <> ""');
        foreach ($auras as $a)
            foreach (explode(' ', $a ) as $spell)
                $world[] = $spell;

        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -8 WHERE s.typeCat = 0 AND s.id In (?a)', $world);


        /**********/
        /* Glyphs */
        /**********/

        CLI::write(' - fixing glyph data');

        // glyphSpell => affectedSpell
        $glyphAffects = array(
            63959 => 50842,                                 // Pestilence
            58723 => 55090,                                 // Scourge Strike
            58721 => 46584,                                 // Raise Dead
            58711 => 52375,                                 // Death Coil
            54857 => 33876,                                 // Mangle (Cat)
            56881 => 13165,                                 // Aspect of the Hawk
            56598 => 27101,                                 // Conjure Mana Gem (Rank 5)
            63871 => 1038,                                  // Hand of Salvation
            55003 => 53407,                                 // Judgement of Justice
            63873 => 47788,                                 // Guardian Spirit
            58258 => 2983,                                  // Sprint
            55535 => 52127,                                 // Water Shield
            55558 => 16190,                                 // Mana Tide Totem
            56302 => 697,                                   // Summon Voidwalker
            56299 => 712,                                   // Summon Succubus
            58272 => 126,                                   // Summon Eye of Kilrogg
            56292 => 688,                                   // Summon Imp
            56286 => 691,                                   // Summon Felhunter
            56285 => 30146,                                 // Summon Felguard
            58275 => 29893,                                 // Ritual of Souls
            63941 => 1454,                                  // Life Tap
            56289 => 5699,                                  // Create Healthstone
            56297 => 693,                                   // Create Soulstone
            58271 => 1120,                                  // Drain Soul
            58281 => 34428,                                 // Victory Rush
            58397 => 23922,                                 // Shield Slam
            63949 => 50720                                  // Vigilance
        );

        $queryIcons = '
            SELECT    s.id, s.name_loc0, s.skillLine1 as skill, ic.id as icon, s.typeCat * s.typeCat AS prio
            FROM      ?_spell s
            LEFT JOIN dbc_spellicon si ON s.iconIdBak = si.id
            LEFT JOIN ?_icons ic ON ic.name = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1))
            WHERE     [WHERE] AND (s.cuFlags & ?d) = 0 AND s.typeCat IN (0, 7, -2)  -- not triggered; class spells first, talents second, unk last
            ORDER BY  prio DESC
        ';

        $effects = DB::Aowow()->select('
            SELECT
                 s2.id AS ARRAY_KEY,
                 s1.id,
                 s1.name_loc0,
                 s1.spellFamilyId,
                 s1.spellFamilyFlags1,      s1.spellFamilyFlags2,       s1.spellFamilyFlags3,
                 s1.effect1Id,              s1.effect2Id,               s1.effect3Id,
                 s1.effect1SpellClassMaskA, s1.effect1SpellClassMaskB,  s1.effect1SpellClassMaskC,
                 s1.effect2SpellClassMaskA, s1.effect2SpellClassMaskB,  s1.effect2SpellClassMaskC,
                 s1.effect3SpellClassMaskA, s1.effect3SpellClassMaskB,  s1.effect3SpellClassMaskC
            FROM
                dbc_glyphproperties gp
            JOIN
                ?_spell s1 ON s1.id = gp.spellId
            JOIN
                ?_spell s2 ON s2.effect1MiscValue = gp.id AND s2.effect1Id = 74
            WHERE
                gp.typeFlags IN (0, 1) -- AND s2.id In (58271, 56297, 56289, 63941, 58275)
        ');

        foreach ($effects as $applyId => $glyphEffect)
        {
            $l     = [null, 'A', 'B', 'C'];
            $i     = 0;
            $icons = [];
            $fam   = $glyphEffect['spellFamilyId'];

            // first: manuall replace
            if ($applyId == 57144)                          // has no skillLine.. :/
            {
                DB::Aowow()->query('UPDATE ?_spell s, ?_icons ic SET s.skillLine1 = ?d, s.iconIdAlt = ic.id WHERE s.id = ?d AND ic.name = ?', 253, 57144, 'ability_poisonsting');
                continue;
            }

            // second: search by name and family equality
            if (!$icons)
            {
                $search = !empty($glyphAffects[$applyId]) ? $glyphAffects[$applyId] : str_replace('Glyph of ', '', $glyphEffect['name_loc0']);
                if (is_int($search))
                    $where = "?d AND s.id = ?d";
                else
                    $where = "s.SpellFamilyId = ?d AND s.name_loc0 LIKE ?";

                $qry = str_replace('[WHERE]', $where, $queryIcons);
                $icons = DB::Aowow()->selectRow($qry, $fam ?: 1, $search, SPELL_CU_TRIGGERED);
            }

            // third: match by SpellFamily affect mask
            while (empty($icons) && $i < 3)
            {
                $i++;
                $m1  = $glyphEffect['effect1SpellClassMask'.$l[$i]];
                $m2  = $glyphEffect['effect2SpellClassMask'.$l[$i]];
                $m3  = $glyphEffect['effect3SpellClassMask'.$l[$i]];

                if ($glyphEffect['effect'.$i.'Id'] != 6 || (!$m1 && !$m2 && !$m3))
                    continue;

                $where = "s.SpellFamilyId = ?d AND (s.SpellFamilyFlags1 & ?d OR s.SpellFamilyFlags2 & ?d OR s.SpellFamilyFlags3 & ?d)";

                $icons = DB::Aowow()->selectRow(str_replace('[WHERE]', $where, $queryIcons), $fam, $m1, $m2, $m3, SPELL_CU_TRIGGERED);
            }

            if ($icons)
                DB::Aowow()->query('UPDATE ?_spell s SET s.skillLine1 = ?d, s.iconIdAlt = ?d WHERE s.id = ?d', $icons['skill'], $icons['icon'], $applyId);
            else
                CLI::write('could not match '.$glyphEffect['name_loc0'].' ('.$glyphEffect['id'].') with affected spells', CLI::LOG_WARN);
        }

        // hide unused glyphs
        DB::Aowow()->query('UPDATE ?_spell SET skillLine1 = 0, iconIdAlt = 0, cuFlags = cuFlags | ?d WHERE id IN (?a)', CUSTOM_EXCLUDE_FOR_LISTVIEW, [60460, 58166, 58239, 58240, 58261, 58262, 54910]);

        return true;
    }
});

?>
