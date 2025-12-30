ALTER TABLE aowow_quests
    CHANGE COLUMN `method` `questType` tinyint(3) unsigned NOT NULL DEFAULT 2,
    CHANGE COLUMN `zoneOrSort` `questSortId` smallint(6) NOT NULL DEFAULT 0,
    CHANGE COLUMN `zoneOrSortBak` `questSortIdBak` smallint(6) NOT NULL DEFAULT 0,
    CHANGE COLUMN `type` `questInfoId` smallint(5) unsigned NOT NULL DEFAULT 0
;
