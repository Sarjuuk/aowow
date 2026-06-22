DELETE FROM aowow_setup_custom_data WHERE `command` = 'zones' AND `field` = 'category' AND `entry` = 4298;

UPDATE aowow_dbversion SET `sql` = CONCAT(IFNULL(`sql`, ''), ' zones');
