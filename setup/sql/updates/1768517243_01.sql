UPDATE `aowow_items` SET
    `requiredClass` = IF((`requiredClass` & 1535) = 1535, 0, `requiredClass` & 1535),
    `requiredRace`  = IF((`requiredRace`  & 1791) = 1791, 0, `requiredRace`  & 1791)
;

ALTER TABLE `aowow_items`
    MODIFY COLUMN `requiredClass` smallint(5) unsigned NOT NULL DEFAULT 0,
    MODIFY COLUMN `requiredRace` smallint(5) unsigned NOT NULL DEFAULT 0
;
