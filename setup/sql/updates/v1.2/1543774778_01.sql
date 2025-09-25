-- clear synced chars to prevent conflicts
DELETE FROM `aowow_profiler_profiles` WHERE `realmGUID` IS NOT NULL;
-- clear queue
DELETE FROM `aowow_profiler_sync`;
-- update unique index
ALTER TABLE `aowow_profiler_profiles`
	ADD COLUMN `renameItr` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `name`,
	ADD INDEX `name` (`name`);
