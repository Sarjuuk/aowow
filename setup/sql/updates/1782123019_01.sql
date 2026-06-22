ALTER TABLE `aowow_items` ADD COLUMN
  `itemVisualId` smallint unsigned NOT NULL DEFAULT 0 AFTER `spellVisualId`;

DROP TABLE IF EXISTS `dbc_itemdisplayinfo`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' items itemvisuals');
