ALTER TABLE aowow_creature
    ADD COLUMN `schoolImmuneMask` int(10) unsigned NOT NULL DEFAULT 0 AFTER `mechanicImmuneMask`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' creature');
