ALTER TABLE `aowow_spelldifficulty`
    ADD COLUMN `mapType` tinyint(3) unsigned NOT NULL AFTER `heroic25`
;

-- move linked chest for icc: gunship battle. duplicate saurfang to muradin
DELETE FROM `aowow_loot_link` WHERE `npcId` IN (36939, 38156, 38637, 38638, 36948, 38157, 38639, 38640);
INSERT INTO `aowow_loot_link` (`npcId`, `objectId`, `difficulty`, `priority`, `encounterId`) VALUES
    (36939, 201873, 1, 0, 847),
    (38156, 201874, 2, 0, 847),
    (38637, 201872, 3, 0, 847),
    (38638, 201875, 4, 0, 847),
    (36948, 202178, 1, 0, 847),
    (38157, 202180, 2, 0, 847),
    (38639, 202177, 3, 0, 847),
    (38640, 202179, 4, 0, 847)
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' source spelldifficulty');
