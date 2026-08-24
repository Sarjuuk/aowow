UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' objects');
