ALTER TABLE `aowow_titles`
  ADD COLUMN `bitIdx` tinyint(3) unsigned NOT NULL AFTER `eventId`,
  ADD INDEX `bitIdx` (`bitIdx`);

DROP TABLE IF EXISTS `dbc_chartitles`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(`sql`, ' titles');
