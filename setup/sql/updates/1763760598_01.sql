UPDATE `aowow_dbversion` SET `build` = CONCAT(IFNULL(`build`, ''), ' globaljs');
UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spell');
