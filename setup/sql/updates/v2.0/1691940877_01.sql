DROP TABLE IF EXISTS `dbc_emotes`;
DROP TABLE IF EXISTS `dbc_emotestext`;
DROP TABLE IF EXISTS `dbc_emotestextdata`;

DROP TABLE IF EXISTS `aowow_emotes`;
CREATE TABLE `aowow_emotes` (
    `id` SMALLINT(5) SIGNED NOT NULL,
    `cmd` VARCHAR(35) COLLATE utf8mb4_unicode_ci NOT NULL,
    `isAnimated` TINYINT(1) UNSIGNED NOT NULL,
    `flags` SMALLINT(5) UNSIGNED NOT NULL,
    `parentEmote` SMALLINT(5) UNSIGNED NOT NULL,
    `soundId` SMALLINT(5) SIGNED NOT NULL,
    `state` TINYINT(1) UNSIGNED NOT NULL,
    `stateParam` TINYINT(1) UNSIGNED NOT NULL,
    `cuFlags` INT(10) UNSIGNED NOT NULL,
    `extToExt_loc0` VARCHAR(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToExt_loc2` VARCHAR(113) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToExt_loc3` VARCHAR(91) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToExt_loc4` VARCHAR(71) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToExt_loc6` VARCHAR(89) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToExt_loc8` VARCHAR(123) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToMe_loc0` VARCHAR(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToMe_loc2` VARCHAR(113) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToMe_loc3` VARCHAR(91) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToMe_loc4` VARCHAR(71) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToMe_loc6` VARCHAR(89) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToMe_loc8` VARCHAR(123) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToExt_loc0` VARCHAR(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToExt_loc2` VARCHAR(113) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToExt_loc3` VARCHAR(91) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToExt_loc4` VARCHAR(71) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToExt_loc6` VARCHAR(89) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToExt_loc8` VARCHAR(123) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToNone_loc0` VARCHAR(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToNone_loc2` VARCHAR(113) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToNone_loc3` VARCHAR(91) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToNone_loc4` VARCHAR(71) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToNone_loc6` VARCHAR(89) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `extToNone_loc8` VARCHAR(123) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToNone_loc0` VARCHAR(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToNone_loc2` VARCHAR(113) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToNone_loc3` VARCHAR(91) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToNone_loc4` VARCHAR(71) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToNone_loc6` VARCHAR(89) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `meToNone_loc8` VARCHAR(123) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' emotes');
