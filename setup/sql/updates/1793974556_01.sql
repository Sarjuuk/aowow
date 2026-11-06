DROP TABLE IF EXISTS `dbc_spellduration`;
DROP TABLE IF EXISTS `dbc_spellcasttimes`;
DROP TABLE IF EXISTS `dbc_spell`;

ALTER TABLE `aowow_spell`
    CHANGE COLUMN `castTime` `castTimeId` smallint(5) unsigned NOT NULL,
    CHANGE COLUMN `duration` `durationId` smallint(5) unsigned NOT NULL,
    ADD INDEX idx_casttime (`castTimeId`),
    ADD INDEX idx_duration (`durationId`),
    ADD COLUMN `minFactionId` smallint(6) unsigned NOT NULL AFTER `damageClass`,
    ADD COLUMN `minReputation` tinyint(3) unsigned NOT NULL AFTER `minFactionId`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spell');
