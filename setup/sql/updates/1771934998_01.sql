UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' stats');
