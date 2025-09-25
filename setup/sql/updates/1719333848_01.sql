DROP TABLE IF EXISTS dbc_areatrigger, dbc_soundemitters;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' areatrigger soundemitters spawns');
