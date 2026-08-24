UPDATE `aowow_setup_custom_data` SET `command` = 'items' WHERE `command` = 'item';
UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' items');
