ALTER TABLE aowow_icons
    ADD COLUMN `name_source` varchar(55) NOT NULL AFTER `name`;

UPDATE `aowow_dbversion` SET
    `sql`   = CONCAT(IFNULL(`sql`, ''), ' icons races classes holidays'),
    `build` = CONCAT(IFNULL(`build`, ''), ' simpleimg')
;
