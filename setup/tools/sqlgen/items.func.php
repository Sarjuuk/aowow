<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'items';

    protected $tblDependencyAowow = ['icons'];
    protected $tblDependencyTC    = ['item_template', 'item_template_locale', 'spell_group', 'game_event'];
    protected $dbcSourceFiles     = ['gemproperties', 'itemdisplayinfo', 'spell', 'glyphproperties', 'durabilityquality', 'durabilitycosts'];

    private $skill2cat = array(
        SKILL_INSCRIPTION => 11,
        SKILL_FISHING     =>  9,
        SKILL_MINING      => 12,
        SKILL_COOKING     =>  5,
        SKILL_ALCHEMY     =>  6
    );

    public function generate(array $ids = []) : bool
    {
        $baseQuery = '
            SELECT
                it.entry,
                class,                  class as classBak,
                subclass,               subclass AS subClassBak,
                SoundOverrideSubclass,
                IFNULL(sg.id, 0) AS subSubClass,
                it.name,                IFNULL(itl2.Name, ""),  IFNULL(itl3.Name, ""),  IFNULL(itl4.Name, ""),  IFNULL(itl6.Name, ""),  IFNULL(itl8.Name, ""),
                0 AS iconId,
                displayid,
                0 AS spellVisualId,
                Quality,
                Flags,                  FlagsExtra,
                BuyCount,               BuyPrice,               SellPrice,
                0 AS repairPrice,
                InventoryType AS slot,  InventoryType AS slotBak,
                AllowableClass,         AllowableRace,
                ItemLevel,
                RequiredLevel,
                RequiredSkill,          RequiredSkillRank,
                requiredspell,
                requiredhonorrank,
                RequiredCityRank,
                RequiredReputationFaction,
                RequiredReputationRank,
                maxcount,
                0 AS cuFlags,
                0 AS model,
                stackable,
                ContainerSlots,
                stat_type1,             stat_value1,
                stat_type2,             stat_value2,
                stat_type3,             stat_value3,
                stat_type4,             stat_value4,
                stat_type5,             stat_value5,
                stat_type6,             stat_value6,
                stat_type7,             stat_value7,
                stat_type8,             stat_value8,
                stat_type9,             stat_value9,
                stat_type10,            stat_value10,
                ScalingStatDistribution,
                ScalingStatValue,
                dmg_min1,               dmg_max1,               dmg_type1,
                dmg_min2,               dmg_max2,               dmg_type2,
                delay,
                armor,                  ArmorDamageModifier,
                block,
                holy_res,               fire_res,               nature_res,             frost_res,              shadow_res,             arcane_res,
                ammo_type,
                RangedModRange,
                spellid_1,              spelltrigger_1,         spellcharges_1,         spellppmRate_1,         spellcooldown_1,        spellcategory_1,        spellcategorycooldown_1,
                spellid_2,              spelltrigger_2,         spellcharges_2,         spellppmRate_2,         spellcooldown_2,        spellcategory_2,        spellcategorycooldown_2,
                spellid_3,              spelltrigger_3,         spellcharges_3,         spellppmRate_3,         spellcooldown_3,        spellcategory_3,        spellcategorycooldown_3,
                spellid_4,              spelltrigger_4,         spellcharges_4,         spellppmRate_4,         spellcooldown_4,        spellcategory_4,        spellcategorycooldown_4,
                spellid_5,              spelltrigger_5,         spellcharges_5,         spellppmRate_5,         spellcooldown_5,        spellcategory_5,        spellcategorycooldown_5,
                bonding,
                it.description,         IFNULL(itl2.Description, ""), IFNULL(itl3.Description, ""), IFNULL(itl4.Description, ""), IFNULL(itl6.Description, ""), IFNULL(itl8.Description, ""),
                PageText,
                LanguageID,
                startquest,
                lockid,
                Material,
                IF(RandomProperty > 0, RandomProperty, -RandomSuffix) AS randomEnchant,
                itemset,
                MaxDurability,
                area,
                Map,
                BagFamily,
                TotemCategory,
                socketColor_1,          socketContent_1,
                socketColor_2,          socketContent_2,
                socketColor_3,          socketContent_3,
                socketBonus,
                GemProperties,
                RequiredDisenchantSkill,
                DisenchantID,
                duration,
                ItemLimitCategory,
                IFNULL(ge.eventEntry, 0),
                ScriptName,
                FoodType,
                0 AS gemEnchantmentId,
                minMoneyLoot,           maxMoneyLoot,
                0 AS pickUpSoundId,
                0 AS dropDownSoundId,
                0 AS sheatheSoundId,
                0 AS unsheatheSoundId,
                flagsCustom
            FROM
                item_template it
            LEFT JOIN
                item_template_locale itl2 ON it.entry = itl2.ID AND itl2.locale = "frFR"
            LEFT JOIN
                item_template_locale itl3 ON it.entry = itl3.ID AND itl3.locale = "deDE"
            LEFT JOIN
                item_template_locale itl4 ON it.entry = itl4.ID AND itl4.locale = "zhCN"
            LEFT JOIN
                item_template_locale itl6 ON it.entry = itl6.ID AND itl6.locale = "esES"
            LEFT JOIN
                item_template_locale itl8 ON it.entry = itl8.ID AND itl8.locale = "ruRU"
            LEFT JOIN
                spell_group sg ON sg.spell_id = it.spellid_1 AND it.class = 0 AND it.subclass = 2 AND sg.id IN (1, 2)
            LEFT JOIN
                game_event ge ON ge.holiday = it.HolidayId AND it.HolidayId > 0
            {
            WHERE
                it.entry IN (?a)
            }
            LIMIT
               ?d, ?d';

        $i = 0;
        DB::Aowow()->query('TRUNCATE ?_items');
        while ($items = DB::World()->select($baseQuery, $ids ?: DBSIMPLE_SKIP, SqlGen::$sqlBatchSize * $i, SqlGen::$sqlBatchSize))
        {
            CLI::write(' * batch #' . ++$i . ' (' . count($items) . ')', CLI::LOG_BLANK, true, true);

            foreach ($items as $item)
                DB::Aowow()->query('INSERT INTO ?_items VALUES (?a)', array_values($item));
        }

        // merge with gemProperties
        DB::Aowow()->query('UPDATE ?_items i, dbc_gemproperties gp SET i.gemEnchantmentId = gp.enchantmentId, i.gemColorMask = gp.colorMask WHERE i.gemColorMask = gp.id');

        // get modelString
        DB::Aowow()->query('UPDATE ?_items i, dbc_itemdisplayinfo idi SET i.model = IF(idi.leftModelName = "", idi.rightModelName, idi.leftModelName) WHERE i.displayId = idi.id');

        // get iconId
        DB::Aowow()->query('UPDATE ?_items i, dbc_itemdisplayinfo idi, ?_icons ic SET i.iconId = ic.id WHERE i.displayId = idi.id AND LOWER(idi.inventoryIcon1) = ic.name');

        // unify slots:  Robes => Chest; Ranged (right) => Ranged
        DB::Aowow()->query('UPDATE ?_items SET slot = 15 WHERE slotbak = 26');
        DB::Aowow()->query('UPDATE ?_items SET slot =  5 WHERE slotbak = 20');

        // custom sub-classes
        DB::Aowow()->query('
            UPDATE ?_items SET subclass = IF(
                slotbak = 4, -8, IF(                                  -- shirt
                    slotbak = 19, -7, IF(                             -- tabard
                        slotbak = 16, -6, IF(                         -- cloak
                            slotbak = 23, -5, IF(                     -- held in offhand
                                slotbak = 12, -4, IF(                 -- trinket
                                    slotbak = 2, -3, IF(              -- amulet
                                        slotbak = 11, -2, subClassBak -- ring
            ))))))) WHERE class = 4');

        // move alchemist stones to trinkets (Armor)
        DB::Aowow()->query('UPDATE ?_items SET class = 4, subClass = -4 WHERE classBak = 7 AND subClassBak = 11 AND slotBak = 12');

        // mark keys as key (if not quest items)
        DB::Aowow()->query('UPDATE ?_items SET class = 13, subClass = 0 WHERE classBak IN (0, 15) AND bagFamily & 0x100');

        // set subSubClass for Glyphs (major/minor)
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s, dbc_glyphproperties gp SET i.subSubClass = IF(gp.typeFlags & 0x1, 2, 1) WHERE i.spellId1 = s.id AND s.effect1MiscValue = gp.id AND i.classBak = 16');

        // filter misc(class:15) junk(subclass:0) to appropriate categories

        // assign pets and mounts to category
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s SET subClass = IF(effect1AuraId <> 78, 2, IF(effect2AuraId = 207 OR effect3AuraId = 207 OR (s.id <> 65917 AND effect2AuraId = 4 AND effect3Id = 77), -7, 5)) WHERE s.id = spellId2 AND class = 15 AND spellId1 IN (?a)', LEARN_SPELLS);

        // more corner cases (mounts that are not actualy learned)
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s SET i.subClass = -7 WHERE (effect1Id = 64 OR (effect1AuraId = 78 AND effect2AuraId = 4 AND effect3Id = 77) OR effect1AuraId = 207 OR effect2AuraId = 207 OR effect3AuraId = 207) AND s.id = i.spellId1 AND i.class = 15 AND i.subClass = 5');
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s SET i.subClass =  5 WHERE s.effect1AuraId = 78 AND s.id = i.spellId1 AND i.class = 15 AND i.subClass = 0');

        // move some permanent enchantments to own category
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s SET i.class = 0, i.subClass = 6 WHERE s.effect1Id = 53 AND s.id = i.spellId1 AND i.class = 15');

        // move temporary enchantments to own category
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s SET i.subClass = -3 WHERE s.effect1Id = 54 AND s.id = i.spellId1 AND i.class = 0 AND i.subClassBak = 8');

        // move armor tokens to own category
        DB::Aowow()->query('UPDATE ?_items SET subClass = -2 WHERE quality = 4 AND class = 15 AND subClassBak = 0 AND requiredClass AND (requiredClass & 0x5FF) <> 0x5FF');

        // move some junk to holiday if it requires one
        DB::Aowow()->query('UPDATE ?_items SET subClass = 3 WHERE classBak = 15 AND subClassBak = 0 AND eventId <> 0');

        // move misc items that start quests to class: quest (except Sayges scrolls for consistency)
        DB::Aowow()->query('UPDATE ?_items SET class = 12 WHERE classBak = 15 AND startQuest <> 0 AND name_loc0 NOT LIKE "sayge\'s fortune%"');

        // move perm. enchantments into appropriate cat/subcat
        DB::Aowow()->query('UPDATE ?_items i, dbc_spell s SET i.class = 0, i.subClass = 6 WHERE s.id = i.spellId1 AND s.effect1Id = 53 AND i.classBak = 12');

        // move some generic recipes into appropriate sub-categories
        foreach ($this->skill2cat as $skill => $cat)
            DB::Aowow()->query('UPDATE ?_items SET subClass = ?d WHERE classBak = 9 AND subClassBak = 0 AND requiredSkill = ?d', $cat, $skill);

        // calculate durabilityCosts
        DB::Aowow()->query('
            UPDATE
                ?_items i
            JOIN
                dbc_durabilityquality dq ON dq.id = ((i.quality + 1) * 2)
            JOIN
                dbc_durabilitycosts   dc ON dc.id = i.itemLevel
            SET
                i.repairPrice = (durability* dq.mod * IF(i.classBak = 2,
                    CASE i.subClassBak
                        WHEN  0 THEN  w0 WHEN  1 THEN  w1 WHEN  2 THEN  w2 WHEN  3 THEN  w3 WHEN  4 THEN  w4
                        WHEN  5 THEN  w5 WHEN  6 THEN  w6 WHEN  7 THEN  w7 WHEN  8 THEN  w8 WHEN 10 THEN w10
                        WHEN 11 THEN w11 WHEN 12 THEN w12 WHEN 13 THEN w13 WHEN 14 THEN w14 WHEN 15 THEN w15
                        WHEN 16 THEN w16 WHEN 17 THEN w17 WHEN 18 THEN w18 WHEN 19 THEN w19 WHEN 20 THEN w20
                    END,
                    CASE i.subClassBak
                        WHEN  1 THEN  a1 WHEN  2 THEN  a2 WHEN  3 THEN  a3 WHEN  4 THEN  a4 WHEN  6 THEN  a6
                    END
                ))
            WHERE
                durability > 0 AND ((classBak = 4 AND subClassBak IN (1, 2, 3, 4, 6)) OR (classBak = 2 AND subClassBak <> 9))');

        // hide some nonsense
        DB::Aowow()->query('UPDATE ?_items SET `cuFlags` = `cuFlags` | ?d WHERE
            `name_loc0` LIKE "Monster - %"  OR  `name_loc0` LIKE "Creature - %" OR
            `name_loc0` LIKE "%[PH]%"       OR  `name_loc0` LIKE "% PH %"       OR
            `name_loc0` LIKE "%(new)%"      OR  `name_loc0` LIKE "%(old)%"      OR
            `name_loc0` LIKE "%deprecated%" OR  `name_loc0` LIKE "%obsolete%"   OR
            `name_loc0` LIKE "%1H%"         OR  `name_loc0` LIKE "%QA%"         OR
            `name_loc0` LIKE "%(test)%"     OR  `name_loc0` LIKE "test %"       OR (`name_loc0` LIKE "% test %" AND `class` > 0)',
            CUSTOM_EXCLUDE_FOR_LISTVIEW
        );

        // sanity check weapon class and invtype relation
        $checks = array(
            [[INVTYPE_WEAPONOFFHAND, INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPON], [0, 4, 7, 13, 14, 15]],
            [[INVTYPE_2HWEAPON], [1, 5, 6, 8, 10, 14, 20]],
            [[INVTYPE_RANGED, INVTYPE_RANGEDRIGHT], [2, 3, 16, 18, 14, 19]]
        );
        foreach ($checks as [$slots, $subclasses])
            DB::Aowow()->query('UPDATE ?_items SET `cuFlags` = `cuFlags` | ?d WHERE `class`= ?d AND `slotBak` IN (?a) AND `subClass` NOT IN (?a)', CUSTOM_EXCLUDE_FOR_LISTVIEW, ITEM_CLASS_WEAPON, $slots, $subclasses);

        $this->reapplyCCFlags('items', Type::ITEM);

        return true;
    }
});

?>
