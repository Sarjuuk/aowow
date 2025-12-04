-- drop obsolete custom data for holiday icons
DELETE FROM aowow_setup_custom_data WHERE `command` = 'holidays' AND `field` = 'iconString';
UPDATE aowow_holidays SET `iconString` = '';

-- support calendar_* icons
ALTER TABLE aowow_holidays
    CHANGE COLUMN `iconString` `iconId` smallint(5) unsigned NOT NULL DEFAULT 0
;

-- support class_* icons
ALTER TABLE aowow_classes
    ADD COLUMN `iconId` smallint(5) unsigned NOT NULL DEFAULT 0 AFTER `fileString`
;

-- support race_* icons
ALTER TABLE aowow_races
    ADD COLUMN `iconId0` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT "male icon" AFTER `fileString`,
    ADD COLUMN `iconId1` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT "female icon" AFTER `iconId0`
;
