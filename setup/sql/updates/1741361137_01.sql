UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' itemset'), `build` = CONCAT(IFNULL(`build`, ''), ' itemsets');
