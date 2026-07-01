ALTER TABLE `aowow_itemset`
    CHANGE COLUMN `reqLevel` `minReqLevel` tinyint(3) NOT NULL DEFAULT 0,
    ADD COLUMN `maxReqLevel` tinyint(3) NOT NULL DEFAULT 0 AFTER `minReqLevel`,
    ADD COLUMN `expansion` tinyint(3) NOT NULL DEFAULT 0 AFTER `maxReqLevel`,
    ADD COLUMN `side` tinyint(3) NOT NULL DEFAULT 0 AFTER `expansion`
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' itemset');
