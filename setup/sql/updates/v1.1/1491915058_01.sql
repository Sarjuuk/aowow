ALTER TABLE `aowow_creature`
	ADD COLUMN `humanoid` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `modelId`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' creature');
