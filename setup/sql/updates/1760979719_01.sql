ALTER TABLE `aowow_factions`
    DROP COLUMN `baseRepValue3`,
    DROP COLUMN `baseRepValue4`,
    ADD COLUMN `baseRepValue3` mediumint(9) NOT NULL AFTER `baseRepValue2`,
    ADD COLUMN `baseRepValue4` mediumint(9) NOT NULL AFTER `baseRepValue3`
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' factions');
