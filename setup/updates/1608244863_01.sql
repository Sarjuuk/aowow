ALTER TABLE `aowow_creature`
	ADD COLUMN `resistance1` SMALLINT NOT NULL DEFAULT 0 AFTER `armorMax`,
	ADD COLUMN `resistance2` SMALLINT NOT NULL DEFAULT 0 AFTER `resistance1`,
	ADD COLUMN `resistance3` SMALLINT NOT NULL DEFAULT 0 AFTER `resistance2`,
	ADD COLUMN `resistance4` SMALLINT NOT NULL DEFAULT 0 AFTER `resistance3`,
	ADD COLUMN `resistance5` SMALLINT NOT NULL DEFAULT 0 AFTER `resistance4`,
	ADD COLUMN `resistance6` SMALLINT NOT NULL DEFAULT 0 AFTER `resistance5`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' creature');
