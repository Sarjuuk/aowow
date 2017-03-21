-- structure changed hard
DROP TABLE IF EXISTS `dbc_spellitemenchantment`;
DROP TABLE IF EXISTS `aowow_itemenchantment`;
CREATE TABLE `aowow_itemenchantment` (
  `id` smallint(5) unsigned NOT NULL,
  `charges` tinyint(4) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `procChance` tinyint(3) unsigned NOT NULL,
  `ppmRate` float NOT NULL,
  `type1` tinyint(4) unsigned NOT NULL,
  `type2` tinyint(4) unsigned NOT NULL,
  `type3` tinyint(4) unsigned NOT NULL,
  `amount1` smallint(5) NOT NULL,
  `amount2` smallint(5) NOT NULL,
  `amount3` smallint(5) NOT NULL,
  `object1` mediumint(9) unsigned NOT NULL,
  `object2` mediumint(9) unsigned NOT NULL,
  `object3` smallint(5) unsigned NOT NULL,
  `name_loc0` varchar(65) NOT NULL,
  `name_loc2` varchar(91) NOT NULL,
  `name_loc3` varchar(84) NOT NULL,
  `name_loc6` varchar(89) NOT NULL,
  `name_loc8` varchar(96) NOT NULL,
  `conditionId` tinyint(3) unsigned NOT NULL,
  `skillLine` smallint(5) unsigned NOT NULL,
  `skillLevel` smallint(5) unsigned NOT NULL,
  `requiredLevel` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `aowow_item_stats`
  ALTER `id` DROP DEFAULT;
ALTER TABLE `aowow_item_stats`
  ADD COLUMN `type` smallint(5) unsigned NOT NULL FIRST,
  CHANGE COLUMN `id` `typeId` mediumint(9) unsigned NOT NULL AFTER `type`,
  DROP INDEX `item`,
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`typeId`, `type`);

UPDATE `aowow_item_stats` SET `type` = 3;

ALTER TABLE `aowow_articles`
  ALTER `type` DROP DEFAULT,
  ALTER `typeId` DROP DEFAULT;
ALTER TABLE `aowow_articles`
  CHANGE COLUMN `type` `type` smallint(5) NOT NULL FIRST,
  CHANGE COLUMN `typeId` `typeId` mediumint(9) NOT NULL AFTER `type`;

ALTER TABLE `aowow_comments`
  ALTER `type` DROP DEFAULT,
  ALTER `typeId` DROP DEFAULT;
ALTER TABLE `aowow_comments`
  CHANGE COLUMN `type` `type` smallint(5) unsigned NOT NULL COMMENT 'Type of Page' AFTER `id`,
  CHANGE COLUMN `typeId` `typeId` mediumint(9) NOT NULL COMMENT 'ID Of Page' AFTER `type`;

ALTER TABLE `aowow_screenshots`
  ALTER `type` DROP DEFAULT;
  ALTER `typeId` DROP DEFAULT;
ALTER TABLE `aowow_screenshots`
  CHANGE COLUMN `type` `type` smallint(5) unsigned NOT NULL AFTER `id`,
  CHANGE COLUMN `typeId` `typeId` mediumint(9) NOT NULL AFTER `type`;

ALTER TABLE `aowow_videos`
  ALTER `type` DROP DEFAULT,
  ALTER `typeId` DROP DEFAULT;
ALTER TABLE `aowow_videos`
  CHANGE COLUMN `type` `type` smallint(5) unsigned NOT NULL AFTER `id`,
  CHANGE COLUMN `typeId` `typeId` mediumint(9) NOT NULL AFTER `type`;
