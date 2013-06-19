ALTER TABLE `aowow_achievement` MODIFY COLUMN `cuFlags` int(32) NOT NULL COMMENT 'see defines.php' AFTER `rewardIds`;
UPDATE aowow_achievement SET `cuFlags` = `cuFlags` << 24 WHERE `cuFlags` & 0xFF;