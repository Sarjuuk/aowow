ALTER TABLE `aowow_profiler_pets`
    MODIFY COLUMN `talents` varchar(22) DEFAULT NULL;

UPDATE `aowow_dbversion` SET `build` = CONCAT(IFNULL(`build`, ''), ' talenticons talentcalc');

-- flag all hunters as requiring update
UPDATE `aowow_profiler_profiles` SET `flags` = `flags` | 16, `lastupdated` = 0 WHERE `class` = 3 AND `realmGUID` IS NOT NULL;
