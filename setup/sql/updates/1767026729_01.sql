ALTER TABLE `aowow_spawns`
    ADD COLUMN `ScriptName` varchar(64) DEFAULT NULL AFTER `pathId`,
    ADD COLUMN `StringId` varchar(64) DEFAULT NULL AFTER `ScriptName`
;

ALTER TABLE `aowow_objects`
    MODIFY COLUMN `ScriptOrAI` varchar(64) DEFAULT NULL,
    ADD COLUMN `StringId` varchar(64) DEFAULT NULL AFTER `ScriptOrAI`
;

ALTER TABLE `aowow_creature`
    DROP COLUMN `aiName`,
    DROP COLUMN `scriptName`,
    ADD COLUMN `ScriptOrAI` varchar(64) DEFAULT NULL AFTER `flagsExtra`,
    ADD COLUMN `StringId` varchar(64) DEFAULT NULL AFTER `ScriptOrAI`
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' creature objects spawns');
