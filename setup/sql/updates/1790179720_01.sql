DROP TABLE IF EXISTS `aowow_achievementcriteria`;
CREATE TABLE `aowow_achievementcriteria` (
  `id` smallint(6) NOT NULL,
  `refAchievementId` smallint(6) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `value1` int(11) NOT NULL,
  `value2` int(11) NOT NULL,
  `value3` int(11) NOT NULL,
  `value4` int(11) NOT NULL,
  `value5` int(11) NOT NULL,
  `value6` int(11) NOT NULL,
  `name_loc0` varchar(50) DEFAULT NULL,
  `name_loc2` varchar(50) DEFAULT NULL,
  `name_loc3` varchar(50) DEFAULT NULL,
  `name_loc4` varchar(50) DEFAULT NULL,
  `name_loc6` varchar(50) DEFAULT NULL,
  `name_loc8` varchar(50) DEFAULT NULL,
  `completionFlags` tinyint(4) NOT NULL,
  `groupFlags` tinyint(4) NOT NULL,
  `timeLimit` smallint(6) NOT NULL,
  `order` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_value1` (`value1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' achievementcriteria');
