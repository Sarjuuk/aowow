ALTER TABLE `aowow_dbversion`
    CHANGE COLUMN `sql` `sql` TEXT NULL AFTER `part`,
    CHANGE COLUMN `build` `build` TEXT NULL AFTER `sql`;
