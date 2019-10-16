ALTER TABLE `aowow_spawns`
    ALTER `type` DROP DEFAULT;
ALTER TABLE `aowow_spawns`
    CHANGE COLUMN `type` `type` SMALLINT UNSIGNED NOT NULL AFTER `guid`;

UPDATE `aowow_dbversion` SET  `sql` = CONCAT(IFNULL(`sql`, ''), ' spawns');
