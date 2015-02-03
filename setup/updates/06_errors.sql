CREATE TABLE `aowow_errors` (
	`date` INT(10) UNSIGNED NULL DEFAULT NULL,
	`version` SMALLINT(5) UNSIGNED NOT NULL,
	`phpError` SMALLINT(5) UNSIGNED NOT NULL,
	`file` VARCHAR(250) NOT NULL,
	`line` SMALLINT(5) UNSIGNED NOT NULL,
	`query` VARCHAR(250) NOT NULL,
	`userGroups` SMALLINT(5) UNSIGNED NOT NULL,
	`message` TEXT NULL,
	PRIMARY KEY (`file`, `line`, `phpError`, `version`, `userGroups`)
) COLLATE='utf8_general_ci' ENGINE=MyISAM;
