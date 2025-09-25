-- `key` is too small for our new configs
ALTER TABLE `aowow_config`
    MODIFY COLUMN `key` varchar(50) NOT NULL;

-- split generic upload in ss / vi
UPDATE `aowow_config` SET `key` = 'rep_reward_submit_screenshot', `comment` = 'uploaded screenshot was approved' WHERE `key` = 'rep_reward_upload';
DELETE FROM `aowow_config` WHERE `key` = 'rep_reward_suggest_video';
INSERT INTO `aowow_config` VALUES ('rep_reward_suggest_video', '10', '10', 5, 129, 'suggested video was approved');
