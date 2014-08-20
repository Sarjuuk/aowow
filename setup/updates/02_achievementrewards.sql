ALTER TABLE `aowow_achievement` CHANGE COLUMN `rewardIds` `itemExtra` MEDIUMINT(8) UNSIGNED NOT NULL AFTER `refAchievement`;
UPDATE `aowow_achievement` SET `itemExtra` = 0;
UPDATE `aowow_achievement` SET `itemExtra` = 44738 WHERE `id` = 1956;
