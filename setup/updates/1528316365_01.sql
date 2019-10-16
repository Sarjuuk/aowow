DROP TABLE IF EXISTS `dbc_spell`;

ALTER TABLE `aowow_spell`
    ADD COLUMN `targets` MEDIUMINT UNSIGNED NOT NULL AFTER `stanceMaskNot`,
    CHANGE COLUMN `castTime` `castTime` FLOAT UNSIGNED NOT NULL AFTER `spellFocusObject`,
	CHANGE COLUMN `powerType` `powerType` SMALLINT NOT NULL AFTER `duration`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spell');
