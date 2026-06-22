ALTER TABLE `aowow_itemenchantment` ADD COLUMN
  `itemVisualId` smallint unsigned NOT NULL AFTER `name_loc8`;

DROP TABLE IF EXISTS `dbc_spellitemenchantment`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' itemenchantment itemvisuals');
