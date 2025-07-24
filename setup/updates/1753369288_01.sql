ALTER TABLE aowow_account_weightscales
    ADD COLUMN `orderIdx` tinyint unsigned NOT NULL DEFAULT 0 COMMENT 'check how Profiler handles classes with more than 3 specs before modifying' AFTER `class`;

UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  1 AND `name` = 'fury';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  1 AND `name` = 'prot';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  2 AND `name` = 'prot';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  2 AND `name` = 'retrib';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  3 AND `name` = 'marks';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  3 AND `name` = 'surv';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  4 AND `name` = 'combat';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  4 AND `name` = 'subtle';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  5 AND `name` = 'holy';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  5 AND `name` = 'shadow';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  6 AND `name` = 'frostdps';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  6 AND `name` = 'frosttank';
UPDATE aowow_account_weightscales SET `orderIdx` = 3 WHERE `userId` = 0 AND `class` =  6 AND `name` = 'unholydps';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  7 AND `name` = 'enhance';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  7 AND `name` = 'resto';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  8 AND `name` = 'fire';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  8 AND `name` = 'frost';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` =  9 AND `name` = 'demo';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` =  9 AND `name` = 'destro';
UPDATE aowow_account_weightscales SET `orderIdx` = 1 WHERE `userId` = 0 AND `class` = 11 AND `name` = 'feraldps';
UPDATE aowow_account_weightscales SET `orderIdx` = 2 WHERE `userId` = 0 AND `class` = 11 AND `name` = 'feraltank';
UPDATE aowow_account_weightscales SET `orderIdx` = 3 WHERE `userId` = 0 AND `class` = 11 AND `name` = 'resto';

UPDATE `aowow_dbversion` SET `build` = CONCAT(IFNULL(`build`, ''), ' weightpresets');
