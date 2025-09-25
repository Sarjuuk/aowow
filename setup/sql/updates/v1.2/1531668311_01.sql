DROP TABLE IF EXISTS `dbc_areatrigger`;
DROP TABLE IF EXISTS `aowow_areatrigger`;
CREATE TABLE `aowow_areatrigger` (
    `id` INT(10) UNSIGNED NOT NULL,
    `cuFlags` INT(10) UNSIGNED NOT NULL,
    `type` SMALLINT(5) UNSIGNED NOT NULL,
    `name` VARCHAR(100) NULL DEFAULT NULL,
    `orientation` FLOAT NOT NULL,
    `quest` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `teleportA` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
    `teleportX` FLOAT UNSIGNED NULL DEFAULT NULL,
    `teleportY` FLOAT UNSIGNED NULL DEFAULT NULL,
    `teleportO` FLOAT NULL DEFAULT NULL,
    `teleportF` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `quest` (`quest`),
    INDEX `type` (`type`)
) COLLATE='utf8mb4_general_ci' ENGINE=MyISAM;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' areatrigger');
