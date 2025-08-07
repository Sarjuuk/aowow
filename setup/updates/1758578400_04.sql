ALTER TABLE `aowow_account`
    CHANGE COLUMN `avatar` `wowicon` varchar(55) NOT NULL DEFAULT '' COMMENT 'iconname as avatar',
    ADD COLUMN `avatar` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'selected avatar mode' AFTER `userGroups`;

DROP TABLE IF EXISTS `aowow_account_avatars`;
CREATE TABLE `aowow_account_avatars` (
    `id` mediumint unsigned NOT NULL,
    `userId` int unsigned NOT NULL,
    `name` varchar(20) NOT NULL,
    `size` mediumint unsigned NOT NULL,
    `when` int unsigned NOT NULL,
    `current` tinyint unsigned NOT NULL DEFAULT 0,
    `status` tinyint unsigned NOT NULL DEFAULT 0,
    UNIQUE KEY `id` (`id`) USING BTREE,
    KEY `userId` (`userId`) USING BTREE,
    CONSTRAINT `FK_acc_avatars` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
