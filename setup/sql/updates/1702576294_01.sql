UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' source');
