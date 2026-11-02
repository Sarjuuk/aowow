DROP TABLE IF EXISTS `dbc_factiontemplate`;

ALTER TABLE `aowow_factiontemplate`
    ADD COLUMN `flags` int unsigned NOT NULL AFTER `factionId`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' factions');
