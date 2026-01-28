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
        'spell' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Spell from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['spell', 'spellradius', 'spellduration', 'spellrunecost', 'spellcasttimes', 'skillline', 'skilllineability', 'skillraceclassinfo', 'talent', 'talenttab', 'glyphproperties', 'spellicon', 'itemdisplayinfo'];
    protected $worldDependency = ['item_template', 'creature_template', 'creature_template_addon', 'creature_template_spell', 'smart_scripts', 'trainer_spell', 'disables', 'spell_ranks', 'spell_dbc', 'skill_discovery_template'];
    protected $setupAfter      = [['icons', 'spellrange'], []]; // spellrange required to use SpellList

    public function generate(array $ids = []) : bool
    {
        $ssQuery = 'SELECT id,
                           0 AS category,
                           Dispel,
                           Mechanic,
                           Attributes,         AttributesEx,       AttributesEx2,      AttributesEx3,      AttributesEx4,      AttributesEx5,      AttributesEx6,      AttributesEx7,
                           ?d AS cuFlags,
                           0 AS typeCat,
                           Stances,            StancesNot,
                           Targets,
                           0 AS spellFocus,
                           CastingTimeIndex AS castTime,
                           0 AS recoveryTime,      0 AS recoveryTimeCategory,
                           0 AS startRecoveryTime, 0 AS startRecoveryCategory,
                           ProcChance,         ProcCharges,
                           0 AS procCustom,    0 AS procCooldown,
                           MaxLevel,           BaseLevel,          SpellLevel,         0 AS talentLevel,
                           DurationIndex AS duration,
                           0 AS powerType,
                           0 AS powerCost,
                           0 AS powerCostPerLevel,
                           0 AS powerCostPercent,
                           0 AS powerPerSecond,
                           0 AS powerPerSecondPerLevel,
                           0 AS powerGainRunicPower,
                           0 AS powerCostRunes,
                           RangeIndex,
                           StackAmount,
                           0 AS tool1,         0 AS tool2,
                           0 AS toolCategory1, 0 AS toolCategory2,
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
                           EffectRadiusIndex1 AS effect1RadiusMin, 0 AS effect1RadiusMax,
                           EffectRadiusIndex2 AS effect2RadiusMin, 0 AS effect2RadiusMax,
                           EffectRadiusIndex3 AS effect3RadiusMin, 0 AS effect3RadiusMax,
                           EffectApplyAuraName1,                   EffectApplyAuraName2,                   EffectApplyAuraName3,
                           EffectAmplitude1,                       EffectAmplitude2,                       EffectAmplitude3,
                           EffectMultipleValue1,                   EffectMultipleValue2,                   EffectMultipleValue3,
                           0 AS effect1ChainTarget,                0 AS effect2ChainTarget,                0 AS effect3ChainTarget,
                           EffectItemType1,                        EffectItemType2,                        EffectItemType3,
                           EffectMiscValue1,                       EffectMiscValue2,                       EffectMiscValue3,
                           EffectMiscValueB1,                      EffectMiscValueB2,                      EffectMiscValueB3,
                           EffectTriggerSpell1,                    EffectTriggerSpell2,                    EffectTriggerSpell3,
                           0 AS effect1PointsPerComboPoint,        0 AS effect2PointsPerComboPoint,        0 AS effect3PointsPerComboPoint,
                           EffectSpellClassMaskA1,                 EffectSpellClassMaskB1,                 EffectSpellClassMaskC1,
                           EffectSpellClassMaskA2,                 EffectSpellClassMaskB2,                 EffectSpellClassMaskC2,
                           EffectSpellClassMaskA3,                 EffectSpellClassMaskB3,                 EffectSpellClassMaskC3,
                           DmgMultiplier1,                         DmgMultiplier2,                         DmgMultiplier3,
                           0 AS effect1BonusMultiplier,            0 AS effect2BonusMultiplier,            0 AS effect3BonusMultiplier,
                           0 AS iconId,                            0 AS iconIdBak,                         0 AS iconIdAlt,
                           0 AS rankId,                            0 AS spellVisualId1,
                           CONCAT("Serverside - ",SpellName) AS name_loc0,CONCAT("Serverside - ",SpellName) AS name_loc2,CONCAT("Serverside - ",SpellName) AS name_loc3,CONCAT("Serverside - ",SpellName) AS name_loc4,CONCAT("Serverside - ",SpellName) AS name_loc6,CONCAT("Serverside - ",SpellName) AS name_loc8,
                           "" AS rank_loc0,                        "" AS rank_loc2,                        "" AS rank_loc3,                        "" AS rank_loc4,                        "" AS rank_loc6,                        "" AS rank_loc8,
                           "" AS description_loc0,                 "" AS description_loc2,                 "" AS description_loc3,                 "" AS description_loc4,                 "" AS description_loc6,                 "" AS description_loc8,
                           "" AS buff_loc0,                        "" AS buff_loc2,                        "" AS buff_loc3,                        "" AS buff_loc4,                        "" AS buff_loc6,                        "" AS buff_loc8,
                           MaxTargetLevel,
                           SpellFamilyName,
                           SpellFamilyFlags1,                      SpellFamilyFlags2,                      SpellFamilyFlags3,
                           MaxAffectedTargets,
                           DmgClass,
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
                           0 AS spellDescriptionVariable,
                           0 AS trainingCost
                    FROM   spell_dbc
                    LIMIT  ?d,?d';

        $baseQry = 'SELECT    s.id,
                              category,
                              dispelType,
                              mechanic,
                              attributes0,        attributes1,        attributes2,        attributes3,        attributes4,        attributes5,        attributes6,        attributes7,
                              0 AS cuFlags,
                              0 AS typeCat,
                              stanceMask,         stanceMaskNot,
                              targets,
                              spellFocus,
                              GREATEST(IFNULL(sct.baseTime, 0), 0) / 1000 AS castTime,
                              recoveryTime,       recoveryTimeCategory,
                              startRecoveryTime,  startRecoveryCategory,
                              procChance,         IF(procCharges > 255, 0, procCharges),
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
                              GREATEST(reagent1, 0), GREATEST(reagent2, 0), GREATEST(reagent3, 0), GREATEST(reagent4, 0), GREATEST(reagent5, 0), GREATEST(reagent6, 0), GREATEST(reagent7, 0), GREATEST(reagent8, 0),
                              reagentCount1,         reagentCount2,         reagentCount3,         reagentCount4,         reagentCount5,         reagentCount6,         reagentCount7,         reagentCount8,
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
                              GREATEST(effect1CreateItemId, 0),       GREATEST(effect2CreateItemId, 0),       GREATEST(effect3CreateItemId, 0),
                              effect1MiscValue,                       effect2MiscValue,                       effect3MiscValue,
                              effect1MiscValueB,                      effect2MiscValueB,                      effect3MiscValueB,
                              effect1TriggerSpell,                    effect2TriggerSpell,                    effect3TriggerSpell,
                              effect1PointsPerComboPoint,             effect2PointsPerComboPoint,             effect3PointsPerComboPoint,
                              effect1SpellClassMaskA,                 effect1SpellClassMaskB,                 effect1SpellClassMaskC,
                              effect2SpellClassMaskA,                 effect2SpellClassMaskB,                 effect2SpellClassMaskC,
                              effect3SpellClassMaskA,                 effect3SpellClassMaskB,                 effect3SpellClassMaskC,
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
                              GREATEST(spellDescriptionVariable, 0),
                              0 AS trainingCost
                    FROM      dbc_spell s
                    LEFT JOIN dbc_spellcasttimes sct ON s.castTimeId      = sct.id
                    LEFT JOIN dbc_spellrunecost  src ON s.runeCostId      = src.id
                    LEFT JOIN dbc_spellduration   sd ON s.durationId      = sd.id
                    LEFT JOIN dbc_spellradius    sr1 ON s.effect1RadiusId = sr1.id
                    LEFT JOIN dbc_spellradius    sr2 ON s.effect2RadiusId = sr2.id
                    LEFT JOIN dbc_spellradius    sr3 ON s.effect3RadiusId = sr3.id
                    LIMIT     ?d,?d';


        DB::Aowow()->query('TRUNCATE ?_spell');
        DB::Aowow()->query('SET SESSION innodb_ft_enable_stopword = OFF');

        // merge serverside spells into aowow_spell
        $lastMax = 0;
        $n = 0;
        CLI::write('[spell] - copying serverside spells into aowow_spell');
        while ($spells = DB::World()->select($ssQuery, CUSTOM_SERVERSIDE, $n++ * CLISetup::SQL_BATCH, CLISetup::SQL_BATCH))
        {
            $newMax = max(array_column($spells, 'id'));

            CLI::write(' * sets '.($lastMax + 1).' - '.$newMax, CLI::LOG_BLANK, true, true);

            $lastMax = $newMax;

            foreach ($spells as $spell)
                DB::Aowow()->query('INSERT INTO ?_spell VALUES (?a)', array_values($spell));
        }

        // apply spell radii, duration & casting time
        DB::Aowow()->query('UPDATE ?_spell s LEFT JOIN dbc_spellradius    sr  ON s.`effect1RadiusMin` = sr.`id`  SET s.`effect1RadiusMin` = IFNULL(sr.`radiusMin`, 0), s.`effect1RadiusMax` = IFNULL(sr.`radiusMax`, 0)');
        DB::Aowow()->query('UPDATE ?_spell s LEFT JOIN dbc_spellradius    sr  ON s.`effect2RadiusMin` = sr.`id`  SET s.`effect2RadiusMin` = IFNULL(sr.`radiusMin`, 0), s.`effect2RadiusMax` = IFNULL(sr.`radiusMax`, 0)');
        DB::Aowow()->query('UPDATE ?_spell s LEFT JOIN dbc_spellradius    sr  ON s.`effect3RadiusMin` = sr.`id`  SET s.`effect3RadiusMin` = IFNULL(sr.`radiusMin`, 0), s.`effect3RadiusMax` = IFNULL(sr.`radiusMax`, 0)');
        DB::Aowow()->query('UPDATE ?_spell s LEFT JOIN dbc_spellduration  sd  ON s.`duration`         = sd.`id`  SET s.`duration` = IF(sd.`baseTime` iS NULL, -1, IF(sd.`baseTime` <> -1, ABS(sd.`baseTime`), -1))');
        DB::Aowow()->query('UPDATE ?_spell s LEFT JOIN dbc_spellcasttimes sct ON s.`castTime`         = sct.`id` SET s.`castTime` = GREATEST(IFNULL(sct.`baseTime`, 0), 0) / 1000');

        // merge spell.dbc into aowow_spell
        $lastMax = 0;
        $n = 0;
        CLI::write('[spell] - merging spell.dbc into aowow_spell');
        while ($spells = DB::Aowow()->select($baseQry, $n++ * CLISetup::SQL_BATCH, CLISetup::SQL_BATCH))
        {
            $newMax = max(array_column($spells, 'id'));

            CLI::write(' * sets '.($lastMax + 1).' - '.$newMax, CLI::LOG_BLANK, true, true);

            $lastMax = $newMax;

            foreach ($spells as $spell)
                DB::Aowow()->query('INSERT INTO ?_spell VALUES (?a)', array_values($spell));
        }

        // apply flag: CUSTOM_DISABLED [0xD: players (0x1), pets (0x4), general (0x8); only generally disabled spells]
        if ($disables = DB::World()->selectCol('SELECT `entry` FROM disables WHERE `sourceType` = 0 AND `params_0` = "" AND `params_1` = "" AND `flags` & 0xD'))
            DB::Aowow()->query('UPDATE ?_spell SET `cuFlags` = `cuFlags` | ?d WHERE `id` IN (?a)', CUSTOM_DISABLED, $disables);

        // apply spell ranks (can't use skilllineability.dbc, as it does not contain ranks for non-player/pet spells)
        $ranks = DB::World()->selectCol('SELECT `first_spell_id` AS ARRAY_KEY, `spell_id` AS ARRAY_KEY2, `rank` FROM spell_ranks');
        foreach ($ranks as $firstSpell => $sets)
        {
            // apply flag: SPELL_CU_FIRST_RANK
            DB::Aowow()->query('UPDATE ?_spell SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', SPELL_CU_FIRST_RANK, $firstSpell);

            foreach ($sets as $spell => $rank)
                DB::Aowow()->query('UPDATE ?_spell SET `rankNo` = ?d WHERE `id` = ?d', $rank, $spell);

            // apply flag: SPELL_CU_LAST_RANK
            end($sets);
            DB::Aowow()->query('UPDATE ?_spell SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', SPELL_CU_LAST_RANK, key($sets));
        }


        /*************************************/
        /* merge SkillLineAbility into Spell */
        /*************************************/

        /* acquireMethod
            ABILITY_LEARNED_ON_GET_PROFESSION_SKILL     = 1,        learnedAt = 1
            ABILITY_LEARNED_ON_GET_RACE_OR_CLASS_SKILL  = 2         not used for now
        */

        CLI::write('[spell] - linking with skilllineability');

        $results  = DB::Aowow()->select('SELECT `spellId` AS ARRAY_KEY, `id` AS ARRAY_KEY2, `skillLineId`, `reqRaceMask`, `reqClassMask`, `reqSkillLevel`, `acquireMethod`, `skillLevelGrey`, `skillLevelYellow` FROM dbc_skilllineability sla');
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
                DB::Aowow()->query('UPDATE ?_spell SET `learnedAt` = 1 WHERE `id` = ?d', $spellId);

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
                        foreach (Game::$skillLineMask[$i] as $k => [, $skillLineId])
                        {
                            if (in_array($skillLineId, $lines))
                            {
                                $update['skillLine1']        = $i;
                                $update['skillLine2OrMask'] |= 1 << $k;
                            }
                        }
                    }
            }

            DB::Aowow()->query('UPDATE ?_spell SET ?a WHERE `id` = ?d', $update, $spellId);
        }

        // fill learnedAt, trainingCost from trainer
        if ($trainer = DB::World()->select('SELECT `spellID` AS ARRAY_KEY, MIN(`ReqSkillRank`) AS `reqSkill`, MIN(`MoneyCost`) AS `cost`, `ReqAbility1` AS `reqSpellId`, COUNT(*) AS `count` FROM trainer_spell GROUP BY `SpellID`'))
        {
            $spells = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `effect1Id`, `effect2Id`, `effect3Id`, `effect1TriggerSpell`, `effect2TriggerSpell`, `effect3TriggerSpell` FROM dbc_spell WHERE `id` IN (?a)', array_keys($trainer));
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
                    if ($effects['effect'.$i.'Id'] != SPELL_EFFECT_LEARN_SPELL)
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
                DB::Aowow()->query("UPDATE ?_spell s SET s.`learnedAt` = ?d, s.`trainingCost` = ?d WHERE s.`id` = ?d", $link[0], $link[1], $spell);
        }

        // fill learnedAt from recipe-items
        $recipes = DB::World()->selectCol('SELECT IF(`spelltrigger_2` = ?d, `spellid_2`, `spellid_1`) AS ARRAY_KEY, MIN(`RequiredSkillRank`) FROM item_template WHERE `class` = ?d AND `spelltrigger_1` <> ?d AND `RequiredSkillRank` > 0 GROUP BY ARRAY_KEY',
            SPELL_TRIGGER_LEARN, ITEM_CLASS_RECIPE, SPELL_TRIGGER_EQUIP);
        foreach ($recipes as $spell => $reqSkill)
            DB::Aowow()->query('UPDATE ?_spell SET `learnedAt` = IF(`learnedAt` = 0 OR `learnedAt` > ?d, ?d, `learnedAt`) WHERE `id` = ?d', $reqSkill, $reqSkill, $spell);

        // fill learnedAt from Discovery
            // 61756: Northrend Inscription Research (FAST QA VERSION);
            // 64323: Book of Glyph Mastery (todo: get reqSkill from item [425])
            // 28571 - 28576: $element Protection Potion (todo: get reqSkill from teaching spell [360])
        $discovery = DB::World()->selectCol(
           'SELECT `spellId` AS ARRAY_KEY,
                   IF(`reqSpell` = ?d, ?d,
                   IF(`reqSpell` BETWEEN ?d AND ?d, ?d,
                   IF(`reqSkillValue`, `reqSkillValue`, 1)))
            FROM   skill_discovery_template
            WHERE  `reqSpell` NOT IN (?a)',
            64323, 425, 28571, 28576, 360, [61756]
        );
        foreach ($discovery as $spell => $reqSkill)
            DB::Aowow()->query('UPDATE ?_spell SET `learnedAt` = ?d WHERE `id` = ?d', $reqSkill, $spell);

        // calc reqSkill for gathering-passives (herbing, mining, skinning) (on second thought .. it is set in skilllineability >.<)
        $sets = DB::World()->selectCol('SELECT `spell_id` AS ARRAY_KEY, `rank` * 75 AS `reqSkill` FROM spell_ranks WHERE `first_spell_id` IN (?a)', [55428, 53120, 53125]);
        foreach ($sets as $spell => $reqSkill)
            DB::Aowow()->query('UPDATE ?_spell SET `learnedAt` = ?d WHERE `id` = ?d', $reqSkill, $spell);


        /******************/
        /* talent related */
        /******************/

        CLI::write('[spell] - linking with talent');

        for ($i = 1; $i < 6; $i++)
        {
            // classMask
            DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t, dbc_talenttab tt SET s.`reqClassMask` = tt.`classMask` WHERE tt.`creatureFamilyMask` = 0 AND tt.`id` = t.`tabId` AND t.?# = s.`id`', 'rank'.$i);
            // talentLevel
            DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t, dbc_talenttab tt SET s.`talentLevel` = (t.`row` *  5) + 10 + (?d * 1) WHERE tt.`id` = t.`tabId` AND tt.`creatureFamilyMask`  = 0 AND t.?# = s.`id`', $i - 1, 'rank'.$i);
            DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t, dbc_talenttab tt SET s.`talentLevel` = (t.`row` * 12) + 20 + (?d * 4) WHERE tt.`id` = t.`tabId` AND tt.`creatureFamilyMask` <> 0 AND t.?# = s.`id`', $i - 1, 'rank'.$i);
        }
        // passive talent
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.`cuFlags` = s.`cuFlags` | ?d WHERE t.`talentSpell` = 0 AND (s.`id` = t.`rank1` OR s.`id` = t.`rank2` OR s.`id` = t.`rank3` OR s.`id` = t.`rank4` OR s.`id` = t.`rank5`)', SPELL_CU_TALENT);

        // spell taught by talent
        DB::Aowow()->query('UPDATE ?_spell s, dbc_talent t SET s.`cuFlags` = s.`cuFlags` | ?d WHERE t.`talentSpell` = 1 AND (s.`id` = t.`rank1` OR s.`id` = t.`rank2` OR s.`id` = t.`rank3` OR s.`id` = t.`rank4` OR s.`id` = t.`rank5`)', SPELL_CU_TALENTSPELL);


        /*********/
        /* Other */
        /*********/

        CLI::write('[spell] - misc fixups & icons');

        // FU [FixUps]
        DB::Aowow()->query('UPDATE ?_spell SET `reqRaceMask`  = ?d WHERE `skillLine1` = ?d', ChrRace::DRAENEI->toMask(),  760); // Draenei Racials
        DB::Aowow()->query('UPDATE ?_spell SET `reqRaceMask`  = ?d WHERE `skillLine1` = ?d', ChrRace::BLOODELF->toMask(), 756); // Bloodelf Racials
        DB::Aowow()->query('UPDATE ?_spell SET `reqClassMask` = ?d WHERE `id`         = ?d', ChrClass::MAGE->toMask(),  30449); // Mage - Spellsteal

        // triggered by spell
        DB::Aowow()->query(
           'UPDATE ?_spell a
            JOIN   ( SELECT effect1TriggerSpell as id FROM ?_spell WHERE effect1Id NOT IN (36, 57, 133) AND effect1TriggerSpell <> 0 UNION
                     SELECT effect2TriggerSpell as id FROM ?_spell WHERE effect2Id NOT IN (36, 57, 133) AND effect2TriggerSpell <> 0 UNION
                     SELECT effect3TriggerSpell as id FROM ?_spell WHERE effect3Id NOT IN (36, 57, 133) AND effect3TriggerSpell <> 0  ) as b
            SET    cuFlags = cuFlags | ?d
            WHERE  a.id = b.id',
        SPELL_CU_TRIGGERED);

        // altIcons and quality for craftSpells
        $itemSpells = DB::Aowow()->selectCol(
           'SELECT    s.id AS ARRAY_KEY, effect1CreateItemId
            FROM      dbc_spell s
            LEFT JOIN dbc_talent t1 ON t1.rank1 = s.id
            LEFT JOIN dbc_talent t2 ON t2.rank2 = s.id
            LEFT JOIN dbc_talent t3 ON t3.rank3 = s.id
            WHERE     effect1CreateItemId > 0 AND (effect1Id in (?a) OR effect1AuraId in (?a)) AND t1.id IS NULL AND t2.id IS NULL AND t3.id IS NULL
            UNION
            SELECT    s.id AS ARRAY_KEY, effect2CreateItemId
            FROM      dbc_spell s
            LEFT JOIN dbc_talent t1 ON t1.rank1 = s.id
            LEFT JOIN dbc_talent t2 ON t2.rank2 = s.id
            LEFT JOIN dbc_talent t3 ON t3.rank3 = s.id
            WHERE     effect2CreateItemId > 0 AND (effect2Id in (?a) OR effect2AuraId in (?a)) AND t1.id IS NULL AND t2.id IS NULL AND t3.id IS NULL
            UNION
            SELECT    s.id AS ARRAY_KEY, effect3CreateItemId
            FROM      dbc_spell s
            LEFT JOIN dbc_talent t1 ON t1.rank1 = s.id
            LEFT JOIN dbc_talent t2 ON t2.rank2 = s.id
            LEFT JOIN dbc_talent t3 ON t3.rank3 = s.id
            WHERE     effect3CreateItemId > 0 AND (effect3Id in (?a) OR effect3AuraId in (?a)) AND t1.id IS NULL AND t2.id IS NULL AND t3.id IS NULL',
        SpellList::EFFECTS_ITEM_CREATE, SpellList::AURAS_ITEM_CREATE,
        SpellList::EFFECTS_ITEM_CREATE, SpellList::AURAS_ITEM_CREATE,
        SpellList::EFFECTS_ITEM_CREATE, SpellList::AURAS_ITEM_CREATE);

        $itemInfo = DB::World()->select('SELECT entry AS ARRAY_KEY, displayId AS d, Quality AS q FROM item_template WHERE entry IN (?a)', $itemSpells);
        foreach ($itemSpells as $sId => $itemId)
            if (isset($itemInfo[$itemId]))
                DB::Aowow()->query('UPDATE ?_spell s, ?_icons ic, dbc_itemdisplayinfo idi SET s.iconIdAlt = ic.id, s.cuFlags = s.cuFlags | ?d WHERE ic.name_source = LOWER(idi.inventoryIcon1) AND idi.id = ?d AND s.id = ?d', ((7 - $itemInfo[$itemId]['q']) << 8), $itemInfo[$itemId]['d'], $sId);

        $itemReqs = DB::World()->selectCol('SELECT entry AS ARRAY_KEY, requiredSpell FROM item_template WHERE requiredSpell NOT IN (?a)', [0, 34090, 34091]); // not riding
        foreach ($itemReqs AS $itemId => $req)
            DB::Aowow()->query('UPDATE ?_spell SET reqSpellId = ?d WHERE skillLine1 IN (?a) AND effect1CreateItemId = ?d', $req, [SKILL_BLACKSMITHING, SKILL_LEATHERWORKING, SKILL_TAILORING, SKILL_ENGINEERING], $itemId);

        // setting icons
        DB::Aowow()->query('UPDATE ?_spell s, ?_icons ic, dbc_spellicon si SET s.iconId = ic.id WHERE s.iconIdBak = si.id AND ic.name_source = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1))');

        // hide internal stuff from listviews
        // QA*; *DND*; square brackets anything; *(NYI)*; *(TEST)*
        // cant catch raw: NYI (uNYIelding); PH (PHasing)
        DB::Aowow()->query('UPDATE ?_spell SET cuFlags = cuFlags | ?d WHERE name_loc0 LIKE "QA%" OR name_loc0 LIKE "%DND%" OR name_loc0 LIKE "%[%" OR name_loc0 LIKE "%(NYI)%" OR name_loc0 LIKE "%(TEST)%"', CUSTOM_EXCLUDE_FOR_LISTVIEW);


        /**************/
        /* Categories */
        /**************/

        CLI::write('[spell] - applying categories');

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
            (s.attributes0 = 0x20000000 AND s.attributes3 = 0x10000000)                                                                                                                -- Master Demonologist (FamilyId = 0)
        )', CUSTOM_EXCLUDE_FOR_LISTVIEW);

        foreach (ChrClass::cases() as $cl)
            DB::Aowow()->query(
               'UPDATE ?_spell s, dbc_skillline sl, dbc_skillraceclassinfo srci
                SET    s.`reqClassMask` = srci.`classMask`
                WHERE  s.`typeCat` IN (-2, 7)  AND (s.`attributes0` & 0x80) = 0 AND s.`skillLine1` = srci.`skillLine` AND sl.`categoryId` = 7 AND
                       srci.`skillline` <> 769 AND srci.`skillline` = sl.`id`   AND srci.`flags` & 0x90               AND srci.`classMask` & ?d',
                $cl->toMask()
            );

        // secondary Skills (9)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = 9 WHERE s.typeCat = 0 AND (s.skillLine1 IN (?a) OR (s.skillLine1 > 0 AND s.skillLine2OrMask IN (?a)))', SKILLS_TRADE_SECONDARY, SKILLS_TRADE_SECONDARY);

        // primary Skills (11)
        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = 11 WHERE s.typeCat = 0 AND s.skillLine1 IN (?a)', SKILLS_TRADE_PRIMARY);

        // npc spells (-8) (run as last! .. missing from npc_scripts? "enum Spells { \s+(\w\d_)+\s+=\s(\d+) }" and "#define SPELL_(\d\w_)+\s+(\d+)") // RAID_MODE(1, 2[, 3, 4]) - macro still not considered
        $world = DB::World()->selectCol(
           'SELECT ss.`action_param1` FROM smart_scripts ss            WHERE ss.`action_type` IN (?a) UNION
            SELECT cts.`Spell`        FROM creature_template_spell cts                                UNION
            SELECT nscs.`spell_id`    FROM npc_spellclick_spells nscs',
            [SmartAction::ACTION_CAST, SmartAction::ACTION_ADD_AURA, SmartAction::ACTION_SELF_CAST, SmartAction::ACTION_CROSS_CAST]
        );

        $auras = DB::World()->selectCol('SELECT `entry` AS ARRAY_KEY, cta.`auras` FROM creature_template_addon cta WHERE `auras` <> ""');
        foreach ($auras as $e => $aur)
        {
            // people keep trying to seperate auras with commas
            $a = preg_replace('/[^\d ]/', ' ', $aur, -1, $nErrors);
            if ($nErrors > 0)
                CLI::write('[spell] creature_template_addon entry #' . CLI::bold($e) . ' has invalid chars in auras string "'. CLI::bold($aur).'"', CLI::LOG_WARN);

            $world = array_merge($world, array_filter(explode(' ', $a)));
        }

        DB::Aowow()->query('UPDATE ?_spell s SET s.typeCat = -8 WHERE s.typeCat = 0 AND s.id IN (?a)', $world);


        /**********/
        /* Glyphs */
        /**********/

        CLI::write('[spell] - fixing glyph data');

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

        $queryIcons =
           'SELECT    s.id, s.name_loc0, s.skillLine1 as skill, ic.id as icon, s.typeCat * s.typeCat AS prio
            FROM      ?_spell s
            LEFT JOIN dbc_spellicon si ON s.iconIdBak = si.id
            LEFT JOIN ?_icons ic ON ic.name_source = LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1))
            WHERE     [WHERE] AND (s.cuFlags & ?d) = 0 AND s.typeCat IN (0, 7, -2)  -- not triggered; class spells first, talents second, unk last
            ORDER BY  prio DESC';

        $effects = DB::Aowow()->select(
           'SELECT s2.id AS ARRAY_KEY,
                   s1.id,
                   s1.name_loc0,
                   s1.spellFamilyId,
                   s1.spellFamilyFlags1,      s1.spellFamilyFlags2,       s1.spellFamilyFlags3,
                   s1.effect1Id,              s1.effect2Id,               s1.effect3Id,
                   s1.effect1SpellClassMaskA, s1.effect2SpellClassMaskA,  s1.effect3SpellClassMaskA,
                   s1.effect1SpellClassMaskB, s1.effect2SpellClassMaskB,  s1.effect3SpellClassMaskB,
                   s1.effect1SpellClassMaskC, s1.effect2SpellClassMaskC,  s1.effect3SpellClassMaskC
            FROM   dbc_glyphproperties gp
            JOIN   ?_spell s1 ON s1.id = gp.spellId
            JOIN   ?_spell s2 ON s2.effect1MiscValue = gp.id AND s2.effect1Id = 74
            WHERE  gp.typeFlags IN (0, 1)' // AND s2.id In (58271, 56297, 56289, 63941, 58275)
        );

        foreach ($effects as $applyId => $glyphEffect)
        {
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
                $m1  = $glyphEffect['effect'.$i.'SpellClassMaskA'];
                $m2  = $glyphEffect['effect'.$i.'SpellClassMaskB'];
                $m3  = $glyphEffect['effect'.$i.'SpellClassMaskC'];

                if ($glyphEffect['effect'.$i.'Id'] != 6 || (!$m1 && !$m2 && !$m3))
                    continue;

                $where = "s.SpellFamilyId = ?d AND (s.SpellFamilyFlags1 & ?d OR s.SpellFamilyFlags2 & ?d OR s.SpellFamilyFlags3 & ?d)";

                $icons = DB::Aowow()->selectRow(str_replace('[WHERE]', $where, $queryIcons), $fam, $m1, $m2, $m3, SPELL_CU_TRIGGERED);
            }

            if ($icons)
                DB::Aowow()->query('UPDATE ?_spell s SET s.skillLine1 = ?d, s.iconIdAlt = ?d WHERE s.id = ?d', $icons['skill'], $icons['icon'], $applyId);
            else
                CLI::write('[spell] '.str_pad('['.$glyphEffect['id'].']', 8).'could not match '.CLI::bold($glyphEffect['name_loc0']).' with affected spells', CLI::LOG_WARN);
        }

        $this->reapplyCCFlags('spell', Type::SPELL);

        return true;
    }
});

?>
