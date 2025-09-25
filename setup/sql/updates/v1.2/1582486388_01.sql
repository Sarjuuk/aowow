ALTER TABLE `aowow_creature`
	CHANGE COLUMN `trainerSpell` `trainerRequirement` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `trainerType`,
	DROP COLUMN `trainerClass`,
	DROP COLUMN `trainerRace`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spell, source, creature');
