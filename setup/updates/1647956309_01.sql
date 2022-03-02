-- create new tables

DROP TABLE IF EXISTS `aowow_guides`, `aowow_guides_changelog`, `aowow_user_ratings`;

CREATE TABLE `aowow_guides` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `category` smallint unsigned NOT NULL DEFAULT '0',
  `classId` tinyint unsigned DEFAULT NULL,
  `specId` tinyint DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'title for menus + lists',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'title for the page tiself',
  `description` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `url` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `locale` tinyint unsigned NOT NULL DEFAULT '0',
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `rev` tinyint unsigned NOT NULL DEFAULT '0',
  `cuFlags` int unsigned NOT NULL DEFAULT '0',
  `roles` smallint unsigned NOT NULL DEFAULT '0',
  `views` mediumint unsigned NOT NULL DEFAULT '0',
  `userId` mediumint unsigned DEFAULT NULL,
  `date` int unsigned NOT NULL DEFAULT '0',
  `approveUserId` mediumint unsigned DEFAULT NULL,
  `approveDate` int unsigned NOT NULL DEFAULT '0',
  `deleteUserId` mediumint unsigned DEFAULT NULL,
  `deleteData` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aowow_guides_changelog` (
  `id` mediumint unsigned NOT NULL,
  `rev` tinyint unsigned DEFAULT NULL,
  `date` int unsigned NOT NULL,
  `userId` mediumint unsigned NOT NULL,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `msg` varchar(200) COLLATE utf8mb4_general_ci DEFAULT '',
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `aowow_user_ratings` (
  `type` enum('Comment','Guide') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry` int NOT NULL DEFAULT '0',
  `userId` int unsigned NOT NULL DEFAULT '0' COMMENT 'User ID',
  `value` tinyint NOT NULL DEFAULT '0' COMMENT 'Rating Set',
  PRIMARY KEY (`type`,`entry`,`userId`),
  KEY `FK_acc_co_rate_user` (`userId`),
  CONSTRAINT `FK_userId` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;