DROP TABLE aowow_itemrandomproppoints;
UPDATE aowow_dbversion SET `sql` = CONCAT(IFNULL(`sql`, ''), ' itemrandomproppoints');
