DROP TABLE IF EXISTS `aowow_emotes`;
CREATE TABLE `aowow_emotes` (
    `id` SMALLINT(5) UNSIGNED NOT NULL,
    `cmd` VARCHAR(15) NOT NULL,
    `isAnimated` TINYINT(1) UNSIGNED NOT NULL,
    `target_loc0` VARCHAR(65) NULL DEFAULT NULL,
    `target_loc2` VARCHAR(70) NULL DEFAULT NULL,
    `target_loc3` VARCHAR(95) NULL DEFAULT NULL,
    `target_loc6` VARCHAR(90) NULL DEFAULT NULL,
    `target_loc8` VARCHAR(70) NULL DEFAULT NULL,
    `noTarget_loc0` VARCHAR(65) NULL DEFAULT NULL,
    `noTarget_loc2` VARCHAR(110) NULL DEFAULT NULL,
    `noTarget_loc3` VARCHAR(85) NULL DEFAULT NULL,
    `noTarget_loc6` VARCHAR(75) NULL DEFAULT NULL,
    `noTarget_loc8` VARCHAR(60) NULL DEFAULT NULL,
    `self_loc0` VARCHAR(65) NULL DEFAULT NULL,
    `self_loc2` VARCHAR(115) NULL DEFAULT NULL,
    `self_loc3` VARCHAR(85) NULL DEFAULT NULL,
    `self_loc6` VARCHAR(75) NULL DEFAULT NULL,
    `self_loc8` VARCHAR(70) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `aowow_emotes_aliasses`;
CREATE TABLE `aowow_emotes_aliasses` (
    `id` SMALLINT(6) UNSIGNED NOT NULL,
    `locales` SMALLINT(6) UNSIGNED NOT NULL,
    `command` VARCHAR(15) NOT NULL,
    UNIQUE INDEX `id_command` (`id`, `command`),
    INDEX `id` (`id`)
) ENGINE=MyISAM;
