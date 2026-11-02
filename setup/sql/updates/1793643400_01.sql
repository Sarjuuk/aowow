DROP TABLE IF EXISTS `dbc_itemenchantmentcondition`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' itemenchantmentcondition');
