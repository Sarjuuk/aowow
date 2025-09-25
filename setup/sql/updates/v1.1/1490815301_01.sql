DROP TABLE IF EXISTS `aowow_icons`;
CREATE TABLE `aowow_icons` (
    `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cuFlags` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `name` VARCHAR(55) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    INDEX `name` (`name`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB AUTO_INCREMENT=1;

ALTER TABLE `aowow_items`
    ADD COLUMN `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `name_loc8`,
    ADD INDEX `iconId` (`iconId`);

ALTER TABLE `aowow_spell`
    CHANGE COLUMN `iconId` `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `effect3BonusMultiplier`,
    ADD COLUMN `iconIdBak` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `iconId`,
    CHANGE COLUMN `iconIdAlt` `iconIdAlt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `iconIdBak`,
    ADD INDEX `iconId` (`iconId`);

ALTER TABLE `aowow_achievement`
    ALTER `iconId` DROP DEFAULT;
ALTER TABLE `aowow_achievement`
    CHANGE COLUMN `iconId` `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `orderInGroup`,
    ADD COLUMN `iconIdBak` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `iconId`,
    ADD INDEX `iconId` (`iconId`);

ALTER TABLE `aowow_skillline`
    CHANGE COLUMN `iconId` `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `description_loc8`,
    ADD COLUMN `iconIdBak` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `iconId`;

ALTER TABLE `aowow_glyphproperties`
    CHANGE COLUMN `iconId` `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `typeFlags`,
    ADD COLUMN `iconIdBak` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `iconId`;

ALTER TABLE `aowow_currencies`
    ALTER `iconId` DROP DEFAULT;
ALTER TABLE `aowow_currencies`
    CHANGE COLUMN `iconId` `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `cuFlags`,
    ADD INDEX `iconId` (`iconId`);

ALTER TABLE `aowow_pet`
    CHANGE COLUMN `iconString` `iconId` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `name_loc8`,
    ADD INDEX `iconId` (`iconId`);

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' icons glyphproperties skillline items spell pet achievement'), `build` = CONCAT(IFNULL(`build`, ''), ' simpleImg');
