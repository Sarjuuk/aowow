ALTER TABLE `aowow_creature_spawns` MODIFY `phaseMask` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `aowow_objects_spawns` MODIFY `phaseMask` int(10) unsigned NOT NULL DEFAULT 0;
UPDATE `aowow_dbversion` SET `build` = CONCAT(IFNULL(`build`, ''), ' spawns');
