DELETE FROM `aowow_config` WHERE `key` = 'acc_rename_decay';
INSERT INTO `aowow_config` VALUES ('acc_rename_decay', 30 * 24 * 60 * 60, '30 * 24 * 60 * 60', 3, 129, 'delay between username changes');
