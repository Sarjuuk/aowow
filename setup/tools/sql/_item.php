<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
-- custom itemSubClass

itemClass: itemSubClass - diff to Client
    0: {
        6: "Perm. Enhancement",
        "-3": "Temp. Enhancement",
    },
    15: {
        "-7": "Flying Mount",
        "-6": "Combat Pet",
        "-2": "Armor Token",
    },
}

DROP TABLE IF EXISTS `aowow_item_stats`;
CREATE TABLE `aowow_item_stats` (
    `id`  mediumint(8) UNSIGNED NOT NULL ,
    `nsockets`  mediumint(8) NOT NULL ,
    `dmgmin1`  mediumint(8) NOT NULL ,
    `dmgmax1`  mediumint(8) NOT NULL ,
    `speed`  float(8,2) NOT NULL ,
    `dps`  float(8,2) NOT NULL ,
    `mledmgmin`  mediumint(8) NOT NULL ,
    `mledmgmax`  mediumint(8) NOT NULL ,
    `mlespeed`  float(8,2) NOT NULL ,
    `mledps`  float(8,2) NOT NULL ,
    `rgddmgmin`  mediumint(8) NOT NULL ,
    `rgddmgmax`  mediumint(8) NOT NULL ,
    `rgdspeed`  float(8,2) NOT NULL ,
    `rgddps`  float(8,2) NOT NULL ,
    `dmg` float(8,2) NOT NULL ,
    `damagetype` mediumint(8) NOT NULL ,
    `mana` mediumint(8) NOT NULL ,
    `health` mediumint(8) NOT NULL ,
    `agi` mediumint(8) NOT NULL ,
    `str` mediumint(8) NOT NULL ,
    `int` mediumint(8) NOT NULL ,
    `spi` mediumint(8) NOT NULL ,
    `sta` mediumint(8) NOT NULL ,
    `energy` mediumint(8) NOT NULL ,
    `rage` mediumint(8) NOT NULL ,
    `focus` mediumint(8) NOT NULL ,
    `runicpwr` mediumint(8) NOT NULL ,
    `defrtng` mediumint(8) NOT NULL ,
    `dodgertng` mediumint(8) NOT NULL ,
    `parryrtng` mediumint(8) NOT NULL ,
    `blockrtng` mediumint(8) NOT NULL ,
    `mlehitrtng` mediumint(8) NOT NULL ,
    `rgdhitrtng` mediumint(8) NOT NULL ,
    `splhitrtng` mediumint(8) NOT NULL ,
    `mlecritstrkrtng` mediumint(8) NOT NULL ,
    `rgdcritstrkrtng` mediumint(8) NOT NULL ,
    `splcritstrkrtng` mediumint(8) NOT NULL ,
    `_mlehitrtng` mediumint(8) NOT NULL ,
    `_rgdhitrtng` mediumint(8) NOT NULL ,
    `_splhitrtng` mediumint(8) NOT NULL ,
    `_mlecritstrkrtng` mediumint(8) NOT NULL ,
    `_rgdcritstrkrtng` mediumint(8) NOT NULL ,
    `_splcritstrkrtng` mediumint(8) NOT NULL ,
    `mlehastertng` mediumint(8) NOT NULL ,
    `rgdhastertng` mediumint(8) NOT NULL ,
    `splhastertng` mediumint(8) NOT NULL ,
    `hitrtng` mediumint(8) NOT NULL ,
    `critstrkrtng` mediumint(8) NOT NULL ,
    `_hitrtng` mediumint(8) NOT NULL ,
    `_critstrkrtng` mediumint(8) NOT NULL ,
    `resirtng` mediumint(8) NOT NULL ,
    `hastertng` mediumint(8) NOT NULL ,
    `exprtng` mediumint(8) NOT NULL ,
    `atkpwr` mediumint(8) NOT NULL ,
    `mleatkpwr` mediumint(8) NOT NULL ,
    `rgdatkpwr` mediumint(8) NOT NULL ,
    `feratkpwr` mediumint(8) NOT NULL ,
    `splheal` mediumint(8) NOT NULL ,
    `spldmg` mediumint(8) NOT NULL ,
    `manargn` mediumint(8) NOT NULL ,
    `armorpenrtng` mediumint(8) NOT NULL ,
    `splpwr` mediumint(8) NOT NULL ,
    `healthrgn` mediumint(8) NOT NULL ,
    `splpen` mediumint(8) NOT NULL ,
    `block` mediumint(8) NOT NULL ,
    `mastrtng` mediumint(8) NOT NULL ,
    `armor` mediumint(8) NOT NULL ,
    `armorbonus`  mediumint(8) NOT NULL ,
    `firres` mediumint(8) NOT NULL ,
    `frores` mediumint(8) NOT NULL ,
    `holres` mediumint(8) NOT NULL ,
    `shares` mediumint(8) NOT NULL ,
    `natres` mediumint(8) NOT NULL ,
    `arcres` mediumint(8) NOT NULL ,
    `firsplpwr` mediumint(8) NOT NULL ,
    `frosplpwr` mediumint(8) NOT NULL ,
    `holsplpwr` mediumint(8) NOT NULL ,
    `shasplpwr` mediumint(8) NOT NULL ,
    `natsplpwr` mediumint(8) NOT NULL ,
    `arcsplpwr` mediumint(8) NOT NULL ,
    PRIMARY KEY (`id`),
    INDEX `item` (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;

    CREATE TABLE aowow_items LIKE item_template;
    INSERT INTO aowow_items SELECT * FROM item_template;

    ALTER TABLE `aowow_items`
        DROP COLUMN `SoundOverrideSubclass`,
        DROP COLUMN `StatsCount`,
        DROP COLUMN `Material`,
        DROP COLUMN `sheath`,
        DROP COLUMN `WDBVerified`,
        CHANGE COLUMN `entry` `id`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 FIRST ,
        ADD COLUMN `classBak`  tinyint(3) NOT NULL AFTER `class`,
        CHANGE COLUMN `subclass` `subClass`  tinyint(3) NOT NULL DEFAULT 0 AFTER `classBak`,
        ADD COLUMN `subClassBak`  tinyint(3) NOT NULL AFTER `subClass`,
        ADD COLUMN `subSubClass`  tinyint(3) NOT NULL AFTER `subClassBak`,
        CHANGE COLUMN `name` `name_loc0`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `subSubClass`,
        ADD COLUMN `name_loc2`  varchar(255) NOT NULL AFTER `name_loc0`,
        ADD COLUMN `name_loc3`  varchar(255) NOT NULL AFTER `name_loc2`,
        ADD COLUMN `name_loc6`  varchar(255) NOT NULL AFTER `name_loc3`,
        ADD COLUMN `name_loc8`  varchar(255) NOT NULL AFTER `name_loc6`,
        CHANGE COLUMN `displayid` `displayId`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `name_loc8`,
        ADD COLUMN `model`varchar(127) NOT NULL AFTER `displayId`,
        ADD COLUMN `iconString`  varchar(127) NOT NULL AFTER `model`,
        CHANGE COLUMN `Quality` `quality`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `displayId`,
        CHANGE COLUMN `Flags` `flags`  bigint(20) NOT NULL DEFAULT 0 AFTER `quality`,
        CHANGE COLUMN `FlagsExtra` `flagsExtra`  int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `flags`,
        ADD COLUMN `cuFlags`  int(10) NOT NULL AFTER `flagsExtra`,
        CHANGE COLUMN `BuyCount` `buyCount`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1 AFTER `flagsExtra`,
        CHANGE COLUMN `BuyPrice` `buyPrice`  bigint(20) NOT NULL DEFAULT 0 AFTER `buyCount`,
        CHANGE COLUMN `SellPrice` `sellPrice`  int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `buyPrice`,
        ADD COLUMN `repairPrice`  int(10) UNSIGNED NOT NULL AFTER `sellPrice`,
        ADD COLUMN `slot`  tinyint(3) NOT NULL AFTER `repairPrice`,
        CHANGE COLUMN `InventoryType` `slotBak`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `slot`,
        CHANGE COLUMN `AllowableClass` `requiredClass`  int(11) NOT NULL DEFAULT '-1' AFTER `slotBak`,
        CHANGE COLUMN `AllowableRace` `requiredRace`  int(11) NOT NULL DEFAULT '-1' AFTER `requiredClass`,
        CHANGE COLUMN `ItemLevel` `itemLevel`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredRace`,
        CHANGE COLUMN `RequiredLevel` `requiredLevel`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `itemLevel`,
        CHANGE COLUMN `RequiredSkill` `requiredSkill`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredLevel`,
        CHANGE COLUMN `RequiredSkillRank` `requiredSkillRank`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredSkill`,
        CHANGE COLUMN `requiredspell` `requiredSpell`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredSkillRank`,
        CHANGE COLUMN `requiredhonorrank` `requiredHonorRank`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredSpell`,
        CHANGE COLUMN `RequiredCityRank` `requiredCityRank`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredHonorRank`,
        CHANGE COLUMN `RequiredReputationFaction` `requiredFaction`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredCityRank`,
        CHANGE COLUMN `RequiredReputationRank` `requiredFactionRank`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredFaction`,
        CHANGE COLUMN `maxcount` `maxCount`  int(11) NOT NULL DEFAULT 0 AFTER `requiredFactionRank`,
        CHANGE COLUMN `ContainerSlots` `slots`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `stackable`,
        CHANGE COLUMN `stat_type1` `statType1`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `slots`,
        CHANGE COLUMN `stat_value1` `statValue1`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType1`,
        CHANGE COLUMN `stat_type2` `statType2`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue1`,
        CHANGE COLUMN `stat_value2` `statValue2`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType2`,
        CHANGE COLUMN `stat_type3` `statType3`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue2`,
        CHANGE COLUMN `stat_value3` `statValue3`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType3`,
        CHANGE COLUMN `stat_type4` `statType4`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue3`,
        CHANGE COLUMN `stat_value4` `statValue4`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType4`,
        CHANGE COLUMN `stat_type5` `statType5`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue4`,
        CHANGE COLUMN `stat_value5` `statValue5`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType5`,
        CHANGE COLUMN `stat_type6` `statType6`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue5`,
        CHANGE COLUMN `stat_value6` `statValue6`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType6`,
        CHANGE COLUMN `stat_type7` `statType7`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue6`,
        CHANGE COLUMN `stat_value7` `statValue7`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType7`,
        CHANGE COLUMN `stat_type8` `statType8`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue7`,
        CHANGE COLUMN `stat_value8` `statValue8`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType8`,
        CHANGE COLUMN `stat_type9` `statType9`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue8`,
        CHANGE COLUMN `stat_value9` `statValue9`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType9`,
        CHANGE COLUMN `stat_type10` `statType10`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `statValue9`,
        CHANGE COLUMN `stat_value10` `statValue10`  smallint(6) NOT NULL DEFAULT 0 AFTER `statType10`,
        CHANGE COLUMN `ScalingStatDistribution` `scalingStatDistribution`  smallint(6) NOT NULL DEFAULT 0 AFTER `statValue10`,
        CHANGE COLUMN `ScalingStatValue` `scalingStatValue`  int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `scalingStatDistribution`,
        CHANGE COLUMN `dmg_min1` `dmgMin1`  float NOT NULL DEFAULT 0 AFTER `scalingStatValue`,
        CHANGE COLUMN `dmg_max1` `dmgMax1`  float NOT NULL DEFAULT 0 AFTER `dmgMin1`,
        CHANGE COLUMN `dmg_type1` `dmgType1`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `dmgMax1`,
        CHANGE COLUMN `dmg_min2` `dmgMin2`  float NOT NULL DEFAULT 0 AFTER `dmgType1`,
        CHANGE COLUMN `dmg_max2` `dmgMax2`  float NOT NULL DEFAULT 0 AFTER `dmgMin2`,
        CHANGE COLUMN `dmg_type2` `dmgType2`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `dmgMax2`,
        MODIFY COLUMN `delay`  smallint(5) UNSIGNED NOT NULL DEFAULT 1000 AFTER `dmgType2`,
        MODIFY COLUMN `armor`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `delay`,
        CHANGE COLUMN `ArmorDamageModifier` `armorDamageModifier`  float NOT NULL DEFAULT 0 AFTER `armor`,
        MODIFY COLUMN `block`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `armorDamageModifier`,
        CHANGE COLUMN `holy_res` `resHoly`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `block`,
        CHANGE COLUMN `fire_res` `resFire`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `resHoly`,
        CHANGE COLUMN `nature_res` `resNature`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `resFire`,
        CHANGE COLUMN `frost_res` `resFrost`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `resNature`,
        CHANGE COLUMN `shadow_res` `resShadow`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `resFrost`,
        CHANGE COLUMN `arcane_res` `resArcane`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `resShadow`,
        CHANGE COLUMN `ammo_type` `ammoType`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `resArcane`,
        CHANGE COLUMN `RangedModRange` `rangedModRange`  float NOT NULL DEFAULT 0 AFTER `ammoType`,
        CHANGE COLUMN `spellid_1` `spellId1`  mediumint(8) NOT NULL DEFAULT 0 AFTER `rangedModRange`,
        CHANGE COLUMN `spelltrigger_1` `spellTrigger1`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellId1`,
        CHANGE COLUMN `spellcharges_1` `spellCharges1`  smallint(6) NULL DEFAULT NULL AFTER `spellTrigger1`,
        CHANGE COLUMN `spellppmRate_1` `spellppmRate1`  float NOT NULL DEFAULT 0 AFTER `spellCharges1`,
        CHANGE COLUMN `spellcooldown_1` `spellCooldown1`  int(11) NOT NULL DEFAULT '-1' AFTER `spellppmRate1`,
        CHANGE COLUMN `spellcategory_1` `spellCategory1`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellCooldown1`,
        CHANGE COLUMN `spellcategorycooldown_1` `spellCategoryCooldown1`  int(11) NOT NULL DEFAULT '-1' AFTER `spellCategory1`,
        CHANGE COLUMN `spellid_2` `spellId2`  mediumint(8) NOT NULL DEFAULT 0 AFTER `spellCategoryCooldown1`,
        CHANGE COLUMN `spelltrigger_2` `spellTrigger2`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellId2`,
        CHANGE COLUMN `spellcharges_2` `spellCharges2`  smallint(6) NULL DEFAULT NULL AFTER `spellTrigger2`,
        CHANGE COLUMN `spellppmRate_2` `spellppmRate2`  float NOT NULL DEFAULT 0 AFTER `spellCharges2`,
        CHANGE COLUMN `spellcooldown_2` `spellCooldown2`  int(11) NOT NULL DEFAULT '-1' AFTER `spellppmRate2`,
        CHANGE COLUMN `spellcategory_2` `spellCategory2`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellCooldown2`,
        CHANGE COLUMN `spellcategorycooldown_2` `spellCategoryCooldown2`  int(11) NOT NULL DEFAULT '-1' AFTER `spellCategory2`,
        CHANGE COLUMN `spellid_3` `spellId3`  mediumint(8) NOT NULL DEFAULT 0 AFTER `spellCategoryCooldown2`,
        CHANGE COLUMN `spelltrigger_3` `spellTrigger3`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellId3`,
        CHANGE COLUMN `spellcharges_3` `spellCharges3`  smallint(6) NULL DEFAULT NULL AFTER `spellTrigger3`,
        CHANGE COLUMN `spellppmRate_3` `spellppmRate3`  float NOT NULL DEFAULT 0 AFTER `spellCharges3`,
        CHANGE COLUMN `spellcooldown_3` `spellCooldown3`  int(11) NOT NULL DEFAULT '-1' AFTER `spellppmRate3`,
        CHANGE COLUMN `spellcategory_3` `spellCategory3`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellCooldown3`,
        CHANGE COLUMN `spellcategorycooldown_3` `spellCategoryCooldown3`  int(11) NOT NULL DEFAULT '-1' AFTER `spellCategory3`,
        CHANGE COLUMN `spellid_4` `spellId4`  mediumint(8) NOT NULL DEFAULT 0 AFTER `spellCategoryCooldown3`,
        CHANGE COLUMN `spelltrigger_4` `spellTrigger4`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellId4`,
        CHANGE COLUMN `spellcharges_4` `spellCharges4`  smallint(6) NULL DEFAULT NULL AFTER `spellTrigger4`,
        CHANGE COLUMN `spellppmRate_4` `spellppmRate4`  float NOT NULL DEFAULT 0 AFTER `spellCharges4`,
        CHANGE COLUMN `spellcooldown_4` `spellCooldown4`  int(11) NOT NULL DEFAULT '-1' AFTER `spellppmRate4`,
        CHANGE COLUMN `spellcategory_4` `spellCategory4`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellCooldown4`,
        CHANGE COLUMN `spellcategorycooldown_4` `spellCategoryCooldown4`  int(11) NOT NULL DEFAULT '-1' AFTER `spellCategory4`,
        CHANGE COLUMN `spellid_5` `spellId5`  mediumint(8) NOT NULL DEFAULT 0 AFTER `spellCategoryCooldown4`,
        CHANGE COLUMN `spelltrigger_5` `spellTrigger5`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellId5`,
        CHANGE COLUMN `spellcharges_5` `spellCharges5`  smallint(6) NULL DEFAULT NULL AFTER `spellTrigger5`,
        CHANGE COLUMN `spellppmRate_5` `spellppmRate5`  float NOT NULL DEFAULT 0 AFTER `spellCharges5`,
        CHANGE COLUMN `spellcooldown_5` `spellCooldown5`  int(11) NOT NULL DEFAULT '-1' AFTER `spellppmRate5`,
        CHANGE COLUMN `spellcategory_5` `spellCategory5`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `spellCooldown5`,
        CHANGE COLUMN `spellcategorycooldown_5` `spellCategoryCooldown5`  int(11) NOT NULL DEFAULT '-1' AFTER `spellCategory5`,
        CHANGE COLUMN `description` `description_loc0`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `bonding`,
        ADD COLUMN `description_loc2`  varchar(255) NOT NULL AFTER `description_loc0`,
        ADD COLUMN `description_loc3`  varchar(255) NOT NULL AFTER `description_loc2`,
        ADD COLUMN `description_loc6`  varchar(255) NOT NULL AFTER `description_loc3`,
        ADD COLUMN `description_loc8`  varchar(255) NOT NULL AFTER `description_loc6`,
        CHANGE COLUMN `PageText` `pageTextId`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `description_loc8`,
        CHANGE COLUMN `LanguageID` `languageId`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `pageTextId`,
        CHANGE COLUMN `PageMaterial` `pageMaterial`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `languageId`,
        CHANGE COLUMN `startquest` `startQuest`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `pageMaterial`,
        CHANGE COLUMN `lockid` `lockId`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `startQuest`,
        CHANGE COLUMN `RandomProperty` `randomEnchant`  mediumint(8) NOT NULL DEFAULT 0 AFTER `lockId`;
        MODIFY COLUMN `itemset`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `randomSuffix`,
        CHANGE COLUMN `MaxDurability` `durability`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `itemset`,
        CHANGE COLUMN `Map` `map`  smallint(6) NOT NULL DEFAULT 0 AFTER `area`,
        CHANGE COLUMN `BagFamily` `bagFamily`  mediumint(8) NOT NULL DEFAULT 0 AFTER `map`,
        CHANGE COLUMN `TotemCategory` `totemCategory`  mediumint(8) NOT NULL DEFAULT 0 AFTER `bagFamily`,
        CHANGE COLUMN `socketColor_1` `socketColor1`  tinyint(4) NOT NULL DEFAULT 0 AFTER `totemCategory`,
        CHANGE COLUMN `socketContent_1` `socketContent1`  mediumint(8) NOT NULL DEFAULT 0 AFTER `socketColor1`,
        CHANGE COLUMN `socketColor_2` `socketColor2`  tinyint(4) NOT NULL DEFAULT 0 AFTER `socketContent1`,
        CHANGE COLUMN `socketContent_2` `socketContent2`  mediumint(8) NOT NULL DEFAULT 0 AFTER `socketColor2`,
        CHANGE COLUMN `socketColor_3` `socketColor3`  tinyint(4) NOT NULL DEFAULT 0 AFTER `socketContent2`,
        CHANGE COLUMN `socketContent_3` `socketContent3`  mediumint(8) NOT NULL DEFAULT 0 AFTER `socketColor3`,
        CHANGE COLUMN `GemProperties` `gemColorMask`  mediumint(8) NOT NULL DEFAULT 0 AFTER `socketBonus`,
        ADD COLUMN `gemEnchantmentId`  mediumint(8) NOT NULL AFTER `gemColorMask`,
        CHANGE COLUMN `RequiredDisenchantSkill` `requiredDisenchantSkill`  smallint(6) NOT NULL DEFAULT '-1' AFTER `gemProperties`,
        CHANGE COLUMN `DisenchantID` `disenchantId`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `requiredDisenchantSkill`,
        MODIFY COLUMN `duration`  int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `disenchantId`,
        CHANGE COLUMN `ItemLimitCategory` `itemLimitCategory`  smallint(6) NOT NULL DEFAULT 0 AFTER `duration`,
        CHANGE COLUMN `HolidayId` `holidayId`  int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `itemLimitCategory`,
        CHANGE COLUMN `ScriptName` `scriptName`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `holidayId`,
        CHANGE COLUMN `FoodType` `foodType`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `scriptName`,
        DROP PRIMARY KEY,
        ADD PRIMARY KEY (`id`);

    -- random Attribs
    UPDATE aowow_items SET randomEnchant = -RandomSuffix WHERE RandomSuffix <> 0;
    ALTER TABLE `aowow_items` DROP COLUMN `RandomSuffix`,

    -- localization
    UPDATE aowow_items a, locales_item b SET
        a.name_loc2 = b.name_loc2,
        a.name_loc3 = b.name_loc3,
        a.name_loc6 = b.name_loc6,
        a.name_loc8 = b.name_loc8,
        a.description_loc2 = b.description_loc2,
        a.description_loc3 = b.description_loc3,
        a.description_loc6 = b.description_loc6,
        a.description_loc8 = b.description_loc8
    WHERE a.id = b.entry;

    -- merge with gemProperties
    UPDATE aowow_items a, dbc.gemProperties b SET
        a.gemEnchantmentId = b.spellItemEnchantmentId,
        a.gemColorMask = b.colorMask
    WHERE a.gemColorMask = b.id;

    -- icon
    UPDATE aowow_items a, dbc.itemDisplayInfo b SET
        a.iconString = b.inventoryIcon1,
        a.model      = (leftModelName = '', rightModelName, leftModelName)
    WHERE a.displayId = b.id;

    -- Robes => Chest and Ranged (right) => Ranged
    UPDATE aowow_items SET slot = 15 WHERE slotbak = 26;
    UPDATE aowow_items SET slot =  5 WHERE slotbak = 20;

    -- custom sub-classes
    UPDATE aowow_items SET subClassBak = subClass, classBak = class, slot = slotBak;
    UPDATE aowow_items SET subclass = IF(
            slot = 4, -8, IF(                                  -- shirt
                slot = 19, -7, IF(                             -- tabard
                    slot = 16, -6, IF(                         -- cloak
                        slot = 23, -5, IF(                     -- held in offhand
                            slot = 12, -4, IF(                 -- trinket
                                slot = 2, -3, IF(              -- amulet
                                    slot = 11, -2, subClassBak -- ring
                                )
                            )
                        )
                    )
                )
            )
        )
    WHERE class = 4;

    // move alchemist stones to trinkets (Armor)
    UPDATE aowow_items SET class = 4, subClass = -4 WHERE classBak = 7 AND subClassBak = 11 AND slotBak = 12;

    // mark keys as key (if not quest items)
    UPDATE aowow_items SET class = 13, subClass = 0 WHERE classBak IN (0, 15) AND bagFamily & 0x100;

    // set subSubClass for Glyphs (major/minor (requires spells to be set up))
    UPDATE aowow_items i, dbc.spell s, dbc.glyphProperties gp SET i.subSubClass = IF(gp.typeFlags & 0x1, 2, 1) WHERE i.spellId1 = s.id AND s.effectMiscValue1 = gp.id AND i.classBak = 16;

    // elixir-subClasses - spell_group.id = item.subSubClass (1:battle; 2:guardian)
    // query takes ~1min
    UPDATE aowow_items i, world.spell_group sg SET i.subSubClass = sg.id WHERE sg.spell_id = i.spellId1 AND i.classBak = 0 AND i.subClassBak = 2;


    // filter misc(class:15) junk(subclass:0) to appropriate categories

    // assign pets and mounts to category
    UPDATE aowow_items i, dbc.spell s SET
        subClass = IF(effectAuraId1 <> 78, 2, IF(effectAuraId2 = 207 OR effectAuraId3 = 207 OR (s.id <> 65917 AND effectAuraId2 = 4 AND effectId3 = 77), -7, 5))
    WHERE
        s.id = spellId2 AND class = 15 AND spellId1 IN (483, 55884);  -- misc items with learn-effect

    // more corner cases (mounts that are not actualy learned)
    UPDATE aowow_items i, dbc.spell s SET i.subClass = -7 WHERE
        (effectId1 = 64 OR (effectAuraId1 = 78 AND effectAuraId2 = 4 AND effectId3 = 77) OR effectAuraId1 = 207 OR effectAuraId2 = 207 OR effectAuraId3 = 207)
        AND s.id = i.spellId1 AND i.class = 15 AND i.subClass = 5;

    UPDATE aowow_items i, dbc.spell s SET i.subClass = 5 WHERE s.effectAuraId1 = 78 AND s.id = i.spellId1 AND i.class = 15 AND i.subClass = 0;

    UPDATE aowow_items i, dbc.spell s SET i.class = 0, i.subClass = 6 WHERE s.effectId1 = 53 AND s.id = i.spellId1 AND i.class = 15 AND i.subClassBak = 0;
    UPDATE aowow_items i, dbc.spell s SET i.subClass = -3 WHERE s.effectId1 = 54 AND s.id = i.spellId1 AND i.class = 0 AND i.subClassBak = 8;

    // one stray enchanting recipe .. with a strange icon
    UPDATE aowow_items SET class = 9, subClass = 8 WHERE id = 33147;

    UPDATE aowow_items SET subClass = -2 WHERE quality = 4 AND class = 15 AND subClassBak = 0 AND requiredClass AND (requiredClass & 0x5FF) <> 0x5FF;

*/

class ItemSetup extends ItemList
{
    private $cols = [];

    public function __construct($start, $end)               // i suggest steps of 3k at max (20 steps (0 - 60k)); otherwise eats your ram for breakfast
    {
        $this->cols = DB::Aowow()->selectCol('SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`="world" AND `TABLE_NAME`="aowow_item_stats"');
        set_time_limit(300);

        $conditions = array(
            ['i.id', $start, '>'],
            ['i.id', $end, '<='],
            ['class', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR, ITEM_CLASS_CONSUMABLE]],
            0
        );

        parent::__construct($conditions);
    }

    public function calcRepairCost()
    {
        foreach ($this->iterate() as $id => $__)
        {
            $cls = $this->curTpl['class'];
            $scb = $this->curTpl['subClassBak'];
            $dur = $this->curTpl['durability'];
            $qu  = $this->curTpl['quality'];

            // has no durability
            if (!in_array($cls, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) || $dur <= 0)
                continue;

            // is relic, misc or obsolete
            if ($cls == ITEM_CLASS_ARMOR && !in_array($scb, [1, 2, 3, 4, 6]))
                continue;

            $cost = DB::Aowow()->selectCell('SELECT ?# FROM ?_durabilityCost WHERE itemLevel = ?',
                'class'.$cls.'Sub'.$scb,
                $this->curTpl['itemLevel']
            );

            $qMod = Util::$itemDurabilityQualityMod[(($qu + 1) * 2)];

            DB::Aowow()->query('UPDATE ?_items SET repairPrice = ?d WHERE id = ?d', intVal($dur * $cost * $qMod), $id);
        }
    }

    public function writeStatsTable()
    {
        foreach ($this->iterate() as $__)
        {
            $this->extendJsonStats();
            $updateFields = [];

            foreach (@$this->json[$this->id] as $k => $v)
            {
                if (!in_array($k, $this->cols) || !$v || $k == 'id')
                    continue;

                $updateFields[$k] = number_format($v, 2, '.', '');
            }

            if (isset($this->itemMods[$this->id]))
            {
                foreach ($this->itemMods[$this->id] as $k => $v)
                {
                    if (!$v)
                        continue;
                    if ($str = Util::$itemMods[$k])
                        $updateFields[$str] = number_format($v, 2, '.', '');

                }
            }

            if ($updateFields)
                DB::Aowow()->query('REPLACE INTO ?_item_stats (`id`, `'.implode('`, `', array_keys($updateFields)).'`) VALUES (?d, "'.implode('", "', $updateFields).'")', $this->id);
        }
    }
}

?>
