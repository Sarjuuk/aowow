DROP TABLE IF EXISTS `aowow_account_sessions`;
CREATE TABLE `aowow_account_sessions` (
    `userId` int unsigned NOT NULL,
    `sessionId` varchar(190) NOT NULL COMMENT 'PHPSESSID', -- max size (for utf8mb4) to still be a key
    `created` int unsigned NOT NULL,
    `expires` int unsigned NOT NULL COMMENT 'timestamp or 0 (never expires)',
    `touched` int unsigned NOT NULL COMMENT 'timestamp - last used',
    `deviceInfo` varchar(256) NOT NULL,
    `ip` varchar(45) NOT NULL COMMENT 'can change; just last used ip', -- think mobile switching between WLAN and mobile data
    `status` enum('ACTIVE', 'LOGOUT', 'FORCEDLOGOUT', 'EXPIRED') NOT NULL,
    UNIQUE KEY `sessionId` (`sessionId`) USING BTREE,
    KEY `userId` (`userId`) USING BTREE,
    CONSTRAINT `FK_acc_sessions` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

ALTER TABLE `aowow_account`
    DROP COLUMN `allowExpire`;
