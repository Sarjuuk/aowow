ALTER TABLE aowow_achievementcriteria
    MODIFY COLUMN `name_loc0` varchar(150) DEFAULT NULL,
    MODIFY COLUMN `name_loc2` varchar(150) DEFAULT NULL,
    MODIFY COLUMN `name_loc3` varchar(150) DEFAULT NULL,
    MODIFY COLUMN `name_loc4` varchar(150) DEFAULT NULL,
    MODIFY COLUMN `name_loc6` varchar(150) DEFAULT NULL,
    MODIFY COLUMN `name_loc8` varchar(150) DEFAULT NULL;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' achievementcriteria');
