UPDATE `aowow_guides` SET `rev` = 1 WHERE `rev` = 0 AND `id` BETWEEN 1 AND 9;
UPDATE `aowow_articles` SET `rev` = 1 WHERE `rev` = 0 AND `type` = 300 AND `typeId` BETWEEN 1 AND 9;

UPDATE `aowow_guides` SET `classId` = 0 WHERE `classId` IS NULL AND `id` BETWEEN 1 AND 9;
UPDATE `aowow_guides` SET `specId` = -1 WHERE `specId` IS NULL AND `id` BETWEEN 1 AND 9;

ALTER TABLE `aowow_guides`
    MODIFY COLUMN `classId` tinyint(3) unsigned NOT NULL DEFAULT 0,
    MODIFY COLUMN `specId` tinyint(3) signed NOT NULL DEFAULT -1
;

UPDATE `aowow_dbversion` SET `build` = CONCAT(IFNULL(`build`, ''), ' globaljs');
