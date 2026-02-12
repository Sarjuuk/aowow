-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: aowow
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aowow_account`
--

DROP TABLE IF EXISTS `aowow_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `extId` int(10) unsigned DEFAULT NULL COMMENT 'external user id',
  `login` varchar(64) NOT NULL DEFAULT '' COMMENT 'only used for login',
  `passHash` varchar(128) NOT NULL,
  `username` varchar(64) NOT NULL COMMENT 'unique; used for for links and display',
  `email` varchar(64) DEFAULT NULL COMMENT 'unique; can be used for login if AUTH_SELF and can be NULL if not',
  `joinDate` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `dailyVotes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `consecutiveVisits` smallint(5) unsigned NOT NULL DEFAULT 0,
  `curIP` varchar(45) NOT NULL DEFAULT '',
  `prevIP` varchar(45) NOT NULL DEFAULT '',
  `curLogin` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'unixtime',
  `prevLogin` int(10) unsigned NOT NULL DEFAULT 0,
  `locale` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0,2,3,4,6,8',
  `userGroups` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'bitmask',
  `debug` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'show ids in lists user option',
  `avatar` tinyint(4) DEFAULT 0,
  `avatarborder` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `wowicon` varchar(55) NOT NULL DEFAULT '' COMMENT 'iconname as avatar',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT 'user can obtain custom titles',
  `description` text NOT NULL DEFAULT '',
  `excludeGroups` smallint(5) unsigned NOT NULL DEFAULT 1 COMMENT 'profiler - exclude bitmask',
  `userPerms` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'bool isAdmin',
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'flag, see defines',
  `statusTimer` int(10) unsigned NOT NULL DEFAULT 0,
  `token` varchar(40) DEFAULT NULL COMMENT 'identification key for changes to account',
  `updateValue` varchar(128) DEFAULT NULL COMMENT 'temp store for new passHash / email',
  `renameCooldown` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'timestamp when rename is available again',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_avatars`
--

DROP TABLE IF EXISTS `aowow_account_avatars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_avatars` (
  `id` mediumint(8) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(20) NOT NULL,
  `size` mediumint(8) unsigned NOT NULL,
  `when` int(10) unsigned NOT NULL,
  `current` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `userId` (`userId`) USING BTREE,
  CONSTRAINT `FK_acc_avatars` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_banned`
--

DROP TABLE IF EXISTS `aowow_account_banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_banned` (
  `id` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL COMMENT 'affected accountId',
  `staffId` int(10) unsigned NOT NULL COMMENT 'executive accountId',
  `typeMask` tinyint(3) unsigned NOT NULL COMMENT 'ACC_BAN_*',
  `start` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `end` int(10) unsigned NOT NULL COMMENT 'automatic unban @ unixtime',
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_banned` (`userId`),
  CONSTRAINT `FK_acc_banned` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_bannedips`
--

DROP TABLE IF EXISTS `aowow_account_bannedips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_bannedips` (
  `ip` varchar(45) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '0: onSignin; 1:onSignup',
  `count` smallint(6) NOT NULL COMMENT 'nFails',
  `unbanDate` int(11) NOT NULL COMMENT 'automatic remove @ unixtime',
  PRIMARY KEY (`ip`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_cookies`
--

DROP TABLE IF EXISTS `aowow_account_cookies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_cookies` (
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(127) NOT NULL,
  `data` text NOT NULL,
  UNIQUE KEY `userId_name` (`userId`,`name`) USING BTREE,
  KEY `userId` (`userId`) USING BTREE,
  CONSTRAINT `FK_acc_cookies` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_excludes`
--

DROP TABLE IF EXISTS `aowow_account_excludes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_excludes` (
  `userId` int(10) unsigned NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(8) unsigned NOT NULL,
  `mode` enum('EXCLUDE','INCLUDE') NOT NULL,
  UNIQUE KEY `userId_type_typeId` (`userId`,`type`,`typeId`),
  KEY `userId` (`userId`),
  CONSTRAINT `FK_acc_excludes` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_favorites`
--

DROP TABLE IF EXISTS `aowow_account_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_favorites` (
  `userId` int(10) unsigned NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `userId_type_typeId` (`userId`,`type`,`typeId`),
  KEY `userId` (`userId`),
  CONSTRAINT `FK_acc_favorites` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_profiles`
--

DROP TABLE IF EXISTS `aowow_account_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_profiles` (
  `accountId` int(10) unsigned NOT NULL,
  `profileId` int(10) unsigned NOT NULL,
  `extraFlags` int(10) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `accountId_profileId` (`accountId`,`profileId`),
  KEY `accountId` (`accountId`),
  KEY `profileId` (`profileId`),
  CONSTRAINT `FK_account_id` FOREIGN KEY (`accountId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_profile_id` FOREIGN KEY (`profileId`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_reputation`
--

DROP TABLE IF EXISTS `aowow_account_reputation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_reputation` (
  `userId` int(10) unsigned NOT NULL,
  `action` tinyint(3) unsigned NOT NULL COMMENT 'e.g. upvote a comment',
  `amount` tinyint(3) NOT NULL,
  `sourceA` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'e.g. upvoting user',
  `sourceB` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'e.g. upvoted commentId',
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `userId_action_source` (`userId`,`action`,`sourceA`,`sourceB`),
  KEY `userId` (`userId`),
  CONSTRAINT `FK_acc_rep` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='reputation log';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_sessions`
--

DROP TABLE IF EXISTS `aowow_account_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_sessions` (
  `userId` int(10) unsigned NOT NULL,
  `sessionId` varchar(190) NOT NULL COMMENT 'PHPSESSID',
  `created` int(10) unsigned NOT NULL,
  `expires` int(10) unsigned NOT NULL COMMENT 'timestamp or 0 (never expires)',
  `touched` int(10) unsigned NOT NULL COMMENT 'timestamp - last used',
  `deviceInfo` varchar(256) NOT NULL,
  `ip` varchar(45) NOT NULL COMMENT 'can change; just last used ip',
  `status` enum('ACTIVE','LOGOUT','FORCEDLOGOUT','EXPIRED') NOT NULL,
  UNIQUE KEY `sessionId` (`sessionId`) USING BTREE,
  KEY `userId` (`userId`) USING BTREE,
  CONSTRAINT `FK_acc_sessions` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_weightscale_data`
--

DROP TABLE IF EXISTS `aowow_account_weightscale_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_weightscale_data` (
  `id` int(11) NOT NULL,
  `field` varchar(15) NOT NULL,
  `val` smallint(5) unsigned NOT NULL,
  KEY `id` (`id`),
  CONSTRAINT `FK_acc_weightscales` FOREIGN KEY (`id`) REFERENCES `aowow_account_weightscales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_weightscales`
--

DROP TABLE IF EXISTS `aowow_account_weightscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_weightscales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `class` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `orderIdx` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'check how Profiler handles classes with more than 3 specs before modifying',
  `icon` varchar(51) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_acc_weights` (`userId`),
  CONSTRAINT `FK_acc_weights` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_achievement`
--

DROP TABLE IF EXISTS `aowow_achievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_achievement` (
  `id` smallint(5) unsigned NOT NULL,
  `faction` tinyint(3) unsigned NOT NULL,
  `map` smallint(6) NOT NULL,
  `chainId` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `chainPos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `category` smallint(5) unsigned NOT NULL DEFAULT 0,
  `parentCat` smallint(6) NOT NULL DEFAULT 0,
  `points` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `orderInGroup` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `iconIdBak` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `flags` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqCriteriaCount` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `refAchievement` smallint(5) unsigned NOT NULL DEFAULT 0,
  `itemExtra` mediumint(8) unsigned DEFAULT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `name_loc0` varchar(78) DEFAULT NULL,
  `name_loc2` varchar(79) DEFAULT NULL,
  `name_loc3` varchar(86) DEFAULT NULL,
  `name_loc4` varchar(86) DEFAULT NULL,
  `name_loc6` varchar(78) DEFAULT NULL,
  `name_loc8` varchar(76) DEFAULT NULL,
  `description_loc0` text DEFAULT NULL,
  `description_loc2` text DEFAULT NULL,
  `description_loc3` text DEFAULT NULL,
  `description_loc4` text DEFAULT NULL,
  `description_loc6` text DEFAULT NULL,
  `description_loc8` text DEFAULT NULL,
  `reward_loc0` varchar(74) DEFAULT NULL,
  `reward_loc2` varchar(88) DEFAULT NULL,
  `reward_loc3` varchar(92) DEFAULT NULL,
  `reward_loc4` varchar(92) DEFAULT NULL,
  `reward_loc6` varchar(83) DEFAULT NULL,
  `reward_loc8` varchar(95) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `iconId` (`iconId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_achievementcategory`
--

DROP TABLE IF EXISTS `aowow_achievementcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_achievementcategory` (
  `id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `parentCat` smallint(6) NOT NULL DEFAULT 0,
  `parentCat2` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_announcements`
--

DROP TABLE IF EXISTS `aowow_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'iirc negative Ids cant be deleted',
  `page` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `groupMask` smallint(5) unsigned NOT NULL,
  `style` varchar(256) NOT NULL,
  `mode` tinyint(3) unsigned NOT NULL COMMENT '0:pageTop; 1:contentTop',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0:disabled; 1:enabled; 2:deleted',
  `text_loc0` text DEFAULT NULL,
  `text_loc2` text DEFAULT NULL,
  `text_loc3` text DEFAULT NULL,
  `text_loc4` text DEFAULT NULL,
  `text_loc6` text DEFAULT NULL,
  `text_loc8` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_areatrigger`
--

DROP TABLE IF EXISTS `aowow_areatrigger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_areatrigger` (
  `id` int(10) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `type` smallint(5) unsigned NOT NULL,
  `mapId` smallint(5) unsigned NOT NULL COMMENT 'world pos. from dbc',
  `posX` float NOT NULL COMMENT 'world pos. from dbc',
  `posY` float NOT NULL COMMENT 'world pos. from dbc',
  `orientation` float NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quest` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quest` (`quest`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_articles`
--

DROP TABLE IF EXISTS `aowow_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_articles` (
  `type` smallint(6) DEFAULT NULL,
  `typeId` mediumint(9) DEFAULT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `url` varchar(50) DEFAULT NULL,
  `rev` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `editAccess` smallint(5) unsigned NOT NULL DEFAULT 2,
  `article` mediumtext DEFAULT NULL COMMENT 'Markdown formated',
  UNIQUE KEY `type` (`type`,`typeId`,`locale`,`rev`),
  UNIQUE KEY `url` (`url`,`locale`,`rev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_classes`
--

DROP TABLE IF EXISTS `aowow_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_classes` (
  `id` int(11) NOT NULL,
  `fileString` varchar(128) DEFAULT NULL,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `name_loc0` varchar(128) DEFAULT NULL,
  `name_loc2` varchar(128) DEFAULT NULL,
  `name_loc3` varchar(128) DEFAULT NULL,
  `name_loc4` varchar(128) DEFAULT NULL,
  `name_loc6` varchar(128) DEFAULT NULL,
  `name_loc8` varchar(128) DEFAULT NULL,
  `powerType` tinyint(4) NOT NULL DEFAULT 0,
  `raceMask` int(11) NOT NULL DEFAULT 0,
  `roles` int(11) NOT NULL DEFAULT 0,
  `skills` varchar(32) NOT NULL DEFAULT '',
  `flags` mediumint(9) NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `weaponTypeMask` int(11) NOT NULL DEFAULT 0,
  `armorTypeMask` int(11) NOT NULL DEFAULT 0,
  `expansion` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_comments`
--

DROP TABLE IF EXISTS `aowow_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Comment ID',
  `type` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Type of Page',
  `typeId` mediumint(9) NOT NULL DEFAULT 0 COMMENT 'ID Of Page',
  `userId` int(10) unsigned DEFAULT NULL COMMENT 'User ID',
  `roles` smallint(5) unsigned NOT NULL,
  `body` text NOT NULL COMMENT 'Comment text',
  `date` int(11) NOT NULL COMMENT 'Comment timestap',
  `flags` smallint(6) NOT NULL DEFAULT 0 COMMENT 'deleted, outofdate, sticky',
  `replyTo` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Reply To, comment ID',
  `editUserId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Last Edit User ID',
  `editDate` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Last Edit Time',
  `editCount` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Count Of Edits',
  `deleteUserId` int(10) unsigned NOT NULL DEFAULT 0,
  `deleteDate` int(10) unsigned NOT NULL DEFAULT 0,
  `responseUserId` int(10) unsigned NOT NULL DEFAULT 0,
  `responseBody` text DEFAULT NULL,
  `responseRoles` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type_typeId` (`type`,`typeId`),
  KEY `FK_acc_co` (`userId`),
  CONSTRAINT `FK_acc_co` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_config`
--

DROP TABLE IF EXISTS `aowow_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_config` (
  `key` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `default` varchar(255) DEFAULT NULL,
  `cat` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `flags` smallint(5) unsigned NOT NULL DEFAULT 0,
  `comment` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_creature`
--

DROP TABLE IF EXISTS `aowow_creature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_creature` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `difficultyEntry1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `difficultyEntry2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `difficultyEntry3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `KillCredit1` int(10) unsigned NOT NULL DEFAULT 0,
  `KillCredit2` int(10) unsigned NOT NULL DEFAULT 0,
  `displayId1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `displayId2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `displayId3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `displayId4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `textureString` varchar(50) DEFAULT NULL,
  `modelId` mediumint(9) NOT NULL DEFAULT 0,
  `humanoid` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `iconString` varchar(50) DEFAULT NULL COMMENT 'first texture of first model for search (up to 11 other skins omitted..)',
  `name_loc0` varchar(100) DEFAULT NULL,
  `name_loc2` varchar(100) DEFAULT NULL,
  `name_loc3` varchar(100) DEFAULT NULL,
  `name_loc4` varchar(100) DEFAULT NULL,
  `name_loc6` varchar(100) DEFAULT NULL,
  `name_loc8` varchar(100) DEFAULT NULL,
  `subname_loc0` varchar(100) DEFAULT NULL,
  `subname_loc2` varchar(100) DEFAULT NULL,
  `subname_loc3` varchar(100) DEFAULT NULL,
  `subname_loc4` varchar(100) DEFAULT NULL,
  `subname_loc6` varchar(100) DEFAULT NULL,
  `subname_loc8` varchar(100) DEFAULT NULL,
  `minLevel` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `maxLevel` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `exp` smallint(6) NOT NULL DEFAULT 0,
  `faction` smallint(5) unsigned NOT NULL DEFAULT 0,
  `npcflag` int(10) unsigned NOT NULL DEFAULT 0,
  `rank` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dmgSchool` tinyint(4) NOT NULL DEFAULT 0,
  `dmgMultiplier` float NOT NULL DEFAULT 1,
  `atkSpeed` int(10) unsigned NOT NULL DEFAULT 0,
  `rngAtkSpeed` int(10) unsigned NOT NULL DEFAULT 0,
  `mleVariance` float NOT NULL DEFAULT 1,
  `rngVariance` float NOT NULL DEFAULT 1,
  `unitClass` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `unitFlags` int(10) unsigned NOT NULL DEFAULT 0,
  `unitFlags2` int(10) unsigned NOT NULL DEFAULT 0,
  `dynamicFlags` int(10) unsigned NOT NULL DEFAULT 0,
  `family` tinyint(4) NOT NULL DEFAULT 0,
  `trainerType` tinyint(4) NOT NULL DEFAULT 0,
  `trainerRequirement` smallint(5) unsigned NOT NULL DEFAULT 0,
  `dmgMin` float unsigned NOT NULL DEFAULT 0,
  `dmgMax` float unsigned NOT NULL DEFAULT 0,
  `mleAtkPwrMin` smallint(5) unsigned NOT NULL DEFAULT 0,
  `mleAtkPwrMax` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rngAtkPwrMin` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rngAtkPwrMax` smallint(5) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `typeFlags` int(10) unsigned NOT NULL DEFAULT 0,
  `lootId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pickpocketLootId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `skinLootId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell5` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell6` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell7` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell8` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `petSpellDataId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `vehicleId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `minGold` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `maxGold` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `healthMin` int(10) unsigned NOT NULL DEFAULT 1,
  `healthMax` int(10) unsigned NOT NULL DEFAULT 1,
  `manaMin` int(10) unsigned NOT NULL DEFAULT 1,
  `manaMax` int(10) unsigned NOT NULL DEFAULT 1,
  `armorMin` mediumint(8) unsigned NOT NULL DEFAULT 1,
  `armorMax` mediumint(8) unsigned NOT NULL DEFAULT 1,
  `resistance1` smallint(6) NOT NULL DEFAULT 0,
  `resistance2` smallint(6) NOT NULL DEFAULT 0,
  `resistance3` smallint(6) NOT NULL DEFAULT 0,
  `resistance4` smallint(6) NOT NULL DEFAULT 0,
  `resistance5` smallint(6) NOT NULL DEFAULT 0,
  `resistance6` smallint(6) NOT NULL DEFAULT 0,
  `racialLeader` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mechanicImmuneMask` int(10) unsigned NOT NULL DEFAULT 0,
  `schoolImmuneMask` int(10) unsigned NOT NULL DEFAULT 0,
  `flagsExtra` int(10) unsigned NOT NULL DEFAULT 0,
  `ScriptOrAI` varchar(64) DEFAULT NULL,
  `StringId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `difficultyEntry1` (`difficultyEntry1`),
  KEY `difficultyEntry2` (`difficultyEntry2`),
  KEY `difficultyEntry3` (`difficultyEntry3`),
  KEY `idx_loot` (`lootId`),
  KEY `idx_pickpocketloot` (`pickpocketLootId`),
  KEY `idx_skinloot` (`skinLootId`),
  KEY `idx_trainer` (`trainerType`),
  KEY `idx_trainerrequirement` (`trainerRequirement`),
  FULLTEXT `idx_ft_name0` (`name_loc0`),
  FULLTEXT `idx_ft_name2` (`name_loc2`),
  FULLTEXT `idx_ft_name3` (`name_loc3`),
  FULLTEXT `idx_ft_name6` (`name_loc6`),
  FULLTEXT `idx_ft_name8` (`name_loc8`),
  KEY `idx_name0` (`name_loc0`),
  KEY `idx_name2` (`name_loc2`),
  KEY `idx_name3` (`name_loc3`),
  KEY `idx_name4` (`name_loc4`),
  KEY `idx_name6` (`name_loc6`),
  KEY `idx_name8` (`name_loc8`),
  KEY `idx_spell1` (`spell1`),
  KEY `idx_spell2` (`spell2`),
  KEY `idx_spell3` (`spell3`),
  KEY `idx_spell4` (`spell4`),
  KEY `idx_spell5` (`spell5`),
  KEY `idx_spell6` (`spell6`),
  KEY `idx_spell7` (`spell7`),
  KEY `idx_spell8` (`spell8`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_creature_sounds`
--

DROP TABLE IF EXISTS `aowow_creature_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_creature_sounds` (
  `id` smallint(5) unsigned NOT NULL COMMENT 'CreatureDisplayInfo.dbc/id',
  `greeting` smallint(5) unsigned NOT NULL DEFAULT 0,
  `farewell` smallint(5) unsigned NOT NULL DEFAULT 0,
  `angry` smallint(5) unsigned NOT NULL DEFAULT 0,
  `exertion` smallint(5) unsigned NOT NULL DEFAULT 0,
  `exertioncritical` smallint(5) unsigned NOT NULL DEFAULT 0,
  `injury` smallint(5) unsigned NOT NULL DEFAULT 0,
  `injurycritical` smallint(5) unsigned NOT NULL DEFAULT 0,
  `death` smallint(5) unsigned NOT NULL DEFAULT 0,
  `stun` smallint(5) unsigned NOT NULL DEFAULT 0,
  `stand` smallint(5) unsigned NOT NULL DEFAULT 0,
  `footstep` smallint(5) unsigned NOT NULL DEFAULT 0,
  `aggro` smallint(5) unsigned NOT NULL DEFAULT 0,
  `wingflap` smallint(5) unsigned NOT NULL DEFAULT 0,
  `wingglide` smallint(5) unsigned NOT NULL DEFAULT 0,
  `alert` smallint(5) unsigned NOT NULL DEFAULT 0,
  `fidget` smallint(5) unsigned NOT NULL DEFAULT 0,
  `customattack` smallint(5) unsigned NOT NULL DEFAULT 0,
  `loop` smallint(5) unsigned NOT NULL DEFAULT 0,
  `jumpstart` smallint(5) unsigned NOT NULL DEFAULT 0,
  `jumpend` smallint(5) unsigned NOT NULL DEFAULT 0,
  `petattack` smallint(5) unsigned NOT NULL DEFAULT 0,
  `petorder` smallint(5) unsigned NOT NULL DEFAULT 0,
  `petdismiss` smallint(5) unsigned NOT NULL DEFAULT 0,
  `birth` smallint(5) unsigned NOT NULL DEFAULT 0,
  `spellcast` smallint(5) unsigned NOT NULL DEFAULT 0,
  `submerge` smallint(5) unsigned NOT NULL DEFAULT 0,
  `submerged` smallint(5) unsigned NOT NULL DEFAULT 0,
  `transform` smallint(5) unsigned NOT NULL DEFAULT 0,
  `transformanimated` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='!ATTENTION!\r\nthe primary key of this table is NOT a creatureId, but displayId\r\n\r\ncolumn names from LANG.sound_activities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_creature_waypoints`
--

DROP TABLE IF EXISTS `aowow_creature_waypoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_creature_waypoints` (
  `creatureOrPath` int(11) NOT NULL,
  `point` smallint(5) unsigned NOT NULL,
  `areaId` smallint(5) unsigned NOT NULL,
  `floor` tinyint(4) NOT NULL DEFAULT -1,
  `posX` float unsigned NOT NULL,
  `posY` float unsigned NOT NULL,
  `wait` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`creatureOrPath`,`point`,`areaId`,`floor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_currencies`
--

DROP TABLE IF EXISTS `aowow_currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_currencies` (
  `id` int(11) NOT NULL,
  `category` mediumint(9) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `itemId` int(11) NOT NULL DEFAULT 0,
  `cap` int(10) unsigned NOT NULL DEFAULT 0,
  `name_loc0` varchar(64) DEFAULT NULL,
  `name_loc2` varchar(64) DEFAULT NULL,
  `name_loc3` varchar(64) DEFAULT NULL,
  `name_loc4` varchar(64) DEFAULT NULL,
  `name_loc6` varchar(64) DEFAULT NULL,
  `name_loc8` varchar(64) DEFAULT NULL,
  `description_loc0` varchar(256) DEFAULT NULL,
  `description_loc2` varchar(256) DEFAULT NULL,
  `description_loc3` varchar(256) DEFAULT NULL,
  `description_loc4` varchar(256) DEFAULT NULL,
  `description_loc6` varchar(256) DEFAULT NULL,
  `description_loc8` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `iconId` (`iconId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_dbversion`
--

DROP TABLE IF EXISTS `aowow_dbversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_dbversion` (
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `part` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sql` text DEFAULT NULL,
  `build` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_declinedword`
--

DROP TABLE IF EXISTS `aowow_declinedword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_declinedword` (
  `id` smallint(5) unsigned NOT NULL,
  `word` varchar(127) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_declinedwordcases`
--

DROP TABLE IF EXISTS `aowow_declinedwordcases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_declinedwordcases` (
  `wordId` smallint(5) unsigned NOT NULL,
  `caseIdx` tinyint(3) unsigned NOT NULL,
  `word` varchar(131) NOT NULL,
  PRIMARY KEY (`wordId`,`caseIdx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_emotes`
--

DROP TABLE IF EXISTS `aowow_emotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_emotes` (
  `id` smallint(6) NOT NULL,
  `cmd` varchar(35) NOT NULL,
  `isAnimated` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `flags` smallint(5) unsigned NOT NULL DEFAULT 0,
  `parentEmote` smallint(6) NOT NULL DEFAULT 0,
  `soundId` smallint(6) NOT NULL DEFAULT 0,
  `state` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `stateParam` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `extToExt_loc0` varchar(150) DEFAULT NULL,
  `extToExt_loc2` varchar(150) DEFAULT NULL,
  `extToExt_loc3` varchar(150) DEFAULT NULL,
  `extToExt_loc4` varchar(150) DEFAULT NULL,
  `extToExt_loc6` varchar(150) DEFAULT NULL,
  `extToExt_loc8` varchar(150) DEFAULT NULL,
  `extToMe_loc0` varchar(150) DEFAULT NULL,
  `extToMe_loc2` varchar(150) DEFAULT NULL,
  `extToMe_loc3` varchar(150) DEFAULT NULL,
  `extToMe_loc4` varchar(150) DEFAULT NULL,
  `extToMe_loc6` varchar(150) DEFAULT NULL,
  `extToMe_loc8` varchar(150) DEFAULT NULL,
  `meToExt_loc0` varchar(150) DEFAULT NULL,
  `meToExt_loc2` varchar(150) DEFAULT NULL,
  `meToExt_loc3` varchar(150) DEFAULT NULL,
  `meToExt_loc4` varchar(150) DEFAULT NULL,
  `meToExt_loc6` varchar(150) DEFAULT NULL,
  `meToExt_loc8` varchar(150) DEFAULT NULL,
  `extToNone_loc0` varchar(150) DEFAULT NULL,
  `extToNone_loc2` varchar(150) DEFAULT NULL,
  `extToNone_loc3` varchar(150) DEFAULT NULL,
  `extToNone_loc4` varchar(150) DEFAULT NULL,
  `extToNone_loc6` varchar(150) DEFAULT NULL,
  `extToNone_loc8` varchar(150) DEFAULT NULL,
  `meToNone_loc0` varchar(150) DEFAULT NULL,
  `meToNone_loc2` varchar(150) DEFAULT NULL,
  `meToNone_loc3` varchar(150) DEFAULT NULL,
  `meToNone_loc4` varchar(150) DEFAULT NULL,
  `meToNone_loc6` varchar(150) DEFAULT NULL,
  `meToNone_loc8` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_emotes_aliasses`
--

DROP TABLE IF EXISTS `aowow_emotes_aliasses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_emotes_aliasses` (
  `id` smallint(5) unsigned NOT NULL,
  `locales` smallint(5) unsigned NOT NULL,
  `command` varchar(20) NOT NULL,
  UNIQUE KEY `id_command` (`id`,`command`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_emotes_sounds`
--

DROP TABLE IF EXISTS `aowow_emotes_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_emotes_sounds` (
  `emoteId` smallint(5) unsigned NOT NULL,
  `raceId` tinyint(3) unsigned NOT NULL,
  `gender` tinyint(3) unsigned NOT NULL,
  `soundId` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `emoteId_raceId_gender_soundId` (`emoteId`,`raceId`,`gender`,`soundId`),
  KEY `emoteId` (`emoteId`),
  KEY `raceId` (`raceId`),
  KEY `soundId` (`soundId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_errors`
--

DROP TABLE IF EXISTS `aowow_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_errors` (
  `date` int(10) unsigned DEFAULT NULL,
  `version` tinyint(3) unsigned NOT NULL,
  `phpError` smallint(5) unsigned NOT NULL,
  `file` varchar(150) NOT NULL,
  `line` smallint(5) unsigned NOT NULL,
  `query` varchar(250) NOT NULL,
  `post` text NOT NULL,
  `userGroups` smallint(5) unsigned NOT NULL,
  `message` text DEFAULT NULL,
  PRIMARY KEY (`file`,`line`,`phpError`,`version`,`userGroups`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_events`
--

DROP TABLE IF EXISTS `aowow_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_events` (
  `id` smallint(5) unsigned NOT NULL,
  `holidayId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `occurence` int(10) unsigned NOT NULL,
  `length` int(10) unsigned NOT NULL,
  `requires` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `holidayId` (`holidayId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_factions`
--

DROP TABLE IF EXISTS `aowow_factions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_factions` (
  `id` smallint(5) unsigned NOT NULL,
  `repIdx` smallint(6) NOT NULL,
  `baseRepRaceMask1` mediumint(8) unsigned NOT NULL,
  `baseRepRaceMask2` mediumint(8) unsigned NOT NULL,
  `baseRepRaceMask3` mediumint(8) unsigned NOT NULL,
  `baseRepRaceMask4` mediumint(8) unsigned NOT NULL,
  `baseRepClassMask1` mediumint(8) unsigned NOT NULL,
  `baseRepClassMask2` mediumint(8) unsigned NOT NULL,
  `baseRepClassMask3` mediumint(8) unsigned NOT NULL,
  `baseRepClassMask4` mediumint(8) unsigned NOT NULL,
  `baseRepValue1` mediumint(9) NOT NULL,
  `baseRepValue2` mediumint(9) NOT NULL,
  `baseRepValue3` mediumint(9) NOT NULL,
  `baseRepValue4` mediumint(9) NOT NULL,
  `side` tinyint(3) unsigned NOT NULL,
  `expansion` tinyint(3) unsigned NOT NULL,
  `qmNpcIds` varchar(12) NOT NULL COMMENT 'space separated',
  `templateIds` text NOT NULL COMMENT 'space separated',
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `parentFactionId` smallint(5) unsigned NOT NULL,
  `spilloverRateIn` float(8,2) NOT NULL,
  `spilloverRateOut` float(8,2) NOT NULL,
  `spilloverMaxRank` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(35) DEFAULT NULL,
  `name_loc2` varchar(49) DEFAULT NULL,
  `name_loc3` varchar(40) DEFAULT NULL,
  `name_loc4` varchar(40) DEFAULT NULL,
  `name_loc6` varchar(50) DEFAULT NULL,
  `name_loc8` varchar(47) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_factiontemplate`
--

DROP TABLE IF EXISTS `aowow_factiontemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_factiontemplate` (
  `id` smallint(5) unsigned NOT NULL,
  `factionId` smallint(5) unsigned NOT NULL,
  `A` tinyint(4) NOT NULL COMMENT 'Aliance: -1 - hostile, 1 - friendly, 0 - neutral',
  `H` tinyint(4) NOT NULL COMMENT 'Horde: -1 - hostile, 1 - friendly, 0 - neutral',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_glyphproperties`
--

DROP TABLE IF EXISTS `aowow_glyphproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_glyphproperties` (
  `id` smallint(5) unsigned NOT NULL,
  `spellId` mediumint(8) unsigned NOT NULL,
  `typeFlags` tinyint(3) unsigned NOT NULL,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `iconIdBak` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_guides`
--

DROP TABLE IF EXISTS `aowow_guides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_guides` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `category` smallint(5) unsigned NOT NULL DEFAULT 0,
  `classId` tinyint(3) unsigned DEFAULT NULL,
  `specId` tinyint(4) DEFAULT NULL,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT 'title for menus + lists',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT 'title for the page tiself',
  `description` varchar(200) NOT NULL DEFAULT '',
  `url` varchar(50) DEFAULT NULL,
  `locale` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `rev` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `roles` smallint(5) unsigned NOT NULL DEFAULT 0,
  `views` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `userId` mediumint(8) unsigned DEFAULT NULL,
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `approveUserId` mediumint(8) unsigned DEFAULT NULL,
  `approveDate` int(10) unsigned NOT NULL DEFAULT 0,
  `deleteUserId` mediumint(8) unsigned DEFAULT NULL,
  `deleteData` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_guides_changelog`
--

DROP TABLE IF EXISTS `aowow_guides_changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_guides_changelog` (
  `id` mediumint(8) unsigned NOT NULL,
  `rev` tinyint(3) unsigned DEFAULT NULL,
  `date` int(10) unsigned NOT NULL,
  `userId` mediumint(8) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `msg` varchar(200) DEFAULT '',
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_holidays`
--

DROP TABLE IF EXISTS `aowow_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_holidays` (
  `id` smallint(5) unsigned NOT NULL,
  `bossCreature` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `achievementCatOrId` mediumint(9) NOT NULL DEFAULT 0,
  `name_loc0` varchar(36) DEFAULT NULL,
  `name_loc2` varchar(42) DEFAULT NULL,
  `name_loc3` varchar(36) DEFAULT NULL,
  `name_loc4` varchar(36) DEFAULT NULL,
  `name_loc6` varchar(49) DEFAULT NULL,
  `name_loc8` varchar(29) DEFAULT NULL,
  `description_loc0` text DEFAULT NULL,
  `description_loc2` text DEFAULT NULL,
  `description_loc3` text DEFAULT NULL,
  `description_loc4` text DEFAULT NULL,
  `description_loc6` text DEFAULT NULL,
  `description_loc8` text DEFAULT NULL,
  `looping` tinyint(4) NOT NULL,
  `scheduleType` tinyint(4) NOT NULL,
  `textureString` varchar(30) NOT NULL DEFAULT '',
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_home_featuredbox`
--

DROP TABLE IF EXISTS `aowow_home_featuredbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_home_featuredbox` (
  `id` smallint(5) unsigned NOT NULL,
  `editorId` int(10) unsigned DEFAULT NULL,
  `editDate` int(10) unsigned NOT NULL,
  `startDate` int(10) unsigned NOT NULL DEFAULT 0,
  `endDate` int(10) unsigned NOT NULL DEFAULT 0,
  `extraWide` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `boxBG` varchar(150) DEFAULT NULL,
  `altHomeLogo` varchar(150) DEFAULT NULL,
  `altHeaderLogo` varchar(150) DEFAULT NULL,
  `text_loc0` text DEFAULT NULL,
  `text_loc2` text DEFAULT NULL,
  `text_loc3` text DEFAULT NULL,
  `text_loc4` text DEFAULT NULL,
  `text_loc6` text DEFAULT NULL,
  `text_loc8` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_hFBox` (`editorId`),
  CONSTRAINT `FK_acc_hFBox` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_home_featuredbox_overlay`
--

DROP TABLE IF EXISTS `aowow_home_featuredbox_overlay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_home_featuredbox_overlay` (
  `featureId` smallint(5) unsigned NOT NULL,
  `left` smallint(5) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `title_loc0` varchar(100) DEFAULT '',
  `title_loc2` varchar(100) DEFAULT '',
  `title_loc3` varchar(100) DEFAULT '',
  `title_loc4` varchar(100) DEFAULT '',
  `title_loc6` varchar(100) DEFAULT '',
  `title_loc8` varchar(100) DEFAULT '',
  KEY `FK_home_featurebox` (`featureId`),
  CONSTRAINT `FK_home_featurebox` FOREIGN KEY (`featureId`) REFERENCES `aowow_home_featuredbox` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_home_oneliner`
--

DROP TABLE IF EXISTS `aowow_home_oneliner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_home_oneliner` (
  `id` smallint(5) unsigned NOT NULL,
  `editorId` int(10) unsigned DEFAULT NULL,
  `editDate` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `text_loc0` varchar(200) DEFAULT NULL,
  `text_loc2` varchar(200) DEFAULT NULL,
  `text_loc3` varchar(200) DEFAULT NULL,
  `text_loc4` varchar(200) DEFAULT NULL,
  `text_loc6` varchar(200) DEFAULT NULL,
  `text_loc8` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_hOneliner` (`editorId`),
  CONSTRAINT `FK_acc_hOneliner` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_home_titles`
--

DROP TABLE IF EXISTS `aowow_home_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_home_titles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `editorId` int(10) unsigned DEFAULT NULL,
  `editDate` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_title` (`locale`,`title`),
  KEY `FK_acc_hTitles` (`editorId`),
  CONSTRAINT `FK_acc_hTitles` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_icons`
--

DROP TABLE IF EXISTS `aowow_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_icons` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `name` varchar(55) NOT NULL DEFAULT '',
  `name_source` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `idx_sourcename` (`name_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_item_stats`
--

DROP TABLE IF EXISTS `aowow_item_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_item_stats` (
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(8) NOT NULL,
  `nsockets` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dps` float(8,2) DEFAULT NULL,
  `damagetype` tinyint(4) DEFAULT NULL,
  `dmgmin1` mediumint(5) unsigned DEFAULT NULL,
  `dmgmax1` mediumint(5) unsigned DEFAULT NULL,
  `speed` float(8,2) DEFAULT NULL,
  `mledps` float(8,2) DEFAULT NULL,
  `mledmgmin` mediumint(5) unsigned DEFAULT NULL,
  `mledmgmax` mediumint(5) unsigned DEFAULT NULL,
  `mlespeed` float(8,2) DEFAULT NULL,
  `rgddps` float(8,2) DEFAULT NULL,
  `rgddmgmin` mediumint(5) unsigned DEFAULT NULL,
  `rgddmgmax` mediumint(5) unsigned DEFAULT NULL,
  `rgdspeed` float(8,2) DEFAULT NULL,
  `dmg` float(8,2) NOT NULL DEFAULT 0.00,
  `mana` mediumint(6) NOT NULL DEFAULT 0,
  `health` mediumint(6) NOT NULL DEFAULT 0,
  `agi` mediumint(6) NOT NULL DEFAULT 0,
  `str` mediumint(6) NOT NULL DEFAULT 0,
  `int` mediumint(6) NOT NULL DEFAULT 0,
  `spi` mediumint(6) NOT NULL DEFAULT 0,
  `sta` mediumint(6) NOT NULL DEFAULT 0,
  `energy` mediumint(6) NOT NULL DEFAULT 0,
  `rage` mediumint(6) NOT NULL DEFAULT 0,
  `focus` mediumint(6) NOT NULL DEFAULT 0,
  `runic` mediumint(6) NOT NULL DEFAULT 0,
  `defrtng` mediumint(6) NOT NULL DEFAULT 0,
  `dodgertng` mediumint(6) NOT NULL DEFAULT 0,
  `parryrtng` mediumint(6) NOT NULL DEFAULT 0,
  `blockrtng` mediumint(6) NOT NULL DEFAULT 0,
  `mlehitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `rgdhitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `splhitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `mlecritstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `rgdcritstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `splcritstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_mlehitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_rgdhitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_splhitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_mlecritstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_rgdcritstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_splcritstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `mlehastertng` mediumint(6) NOT NULL DEFAULT 0,
  `rgdhastertng` mediumint(6) NOT NULL DEFAULT 0,
  `splhastertng` mediumint(6) NOT NULL DEFAULT 0,
  `hitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `critstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_hitrtng` mediumint(6) NOT NULL DEFAULT 0,
  `_critstrkrtng` mediumint(6) NOT NULL DEFAULT 0,
  `resirtng` mediumint(6) NOT NULL DEFAULT 0,
  `hastertng` mediumint(6) NOT NULL DEFAULT 0,
  `exprtng` mediumint(6) NOT NULL DEFAULT 0,
  `atkpwr` mediumint(6) NOT NULL DEFAULT 0,
  `mleatkpwr` mediumint(6) NOT NULL DEFAULT 0,
  `rgdatkpwr` mediumint(6) NOT NULL DEFAULT 0,
  `feratkpwr` mediumint(6) NOT NULL DEFAULT 0,
  `splheal` mediumint(6) NOT NULL DEFAULT 0,
  `spldmg` mediumint(6) NOT NULL DEFAULT 0,
  `manargn` mediumint(6) NOT NULL DEFAULT 0,
  `armorpenrtng` mediumint(6) NOT NULL DEFAULT 0,
  `splpwr` mediumint(6) NOT NULL DEFAULT 0,
  `healthrgn` mediumint(6) NOT NULL DEFAULT 0,
  `splpen` mediumint(6) NOT NULL DEFAULT 0,
  `block` mediumint(6) NOT NULL DEFAULT 0,
  `mastrtng` mediumint(6) NOT NULL DEFAULT 0,
  `armor` mediumint(6) NOT NULL DEFAULT 0,
  `armorbonus` mediumint(6) DEFAULT NULL,
  `firres` mediumint(6) NOT NULL DEFAULT 0,
  `frores` mediumint(6) NOT NULL DEFAULT 0,
  `holres` mediumint(6) NOT NULL DEFAULT 0,
  `shares` mediumint(6) NOT NULL DEFAULT 0,
  `natres` mediumint(6) NOT NULL DEFAULT 0,
  `arcres` mediumint(6) NOT NULL DEFAULT 0,
  `firsplpwr` mediumint(6) NOT NULL DEFAULT 0,
  `frosplpwr` mediumint(6) NOT NULL DEFAULT 0,
  `holsplpwr` mediumint(6) NOT NULL DEFAULT 0,
  `shasplpwr` mediumint(6) NOT NULL DEFAULT 0,
  `natsplpwr` mediumint(6) NOT NULL DEFAULT 0,
  `arcsplpwr` mediumint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`type`,`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemenchantment`
--

DROP TABLE IF EXISTS `aowow_itemenchantment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemenchantment` (
  `id` smallint(5) unsigned NOT NULL,
  `charges` tinyint(3) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `procChance` tinyint(3) unsigned NOT NULL,
  `ppmRate` float NOT NULL,
  `type1` tinyint(3) unsigned NOT NULL,
  `type2` tinyint(3) unsigned NOT NULL,
  `type3` tinyint(3) unsigned NOT NULL,
  `amount1` smallint(6) NOT NULL,
  `amount2` smallint(6) NOT NULL,
  `amount3` smallint(6) NOT NULL,
  `object1` mediumint(8) unsigned NOT NULL,
  `object2` mediumint(8) unsigned NOT NULL,
  `object3` smallint(5) unsigned NOT NULL,
  `name_loc0` varchar(65) DEFAULT NULL,
  `name_loc2` varchar(91) DEFAULT NULL,
  `name_loc3` varchar(84) DEFAULT NULL,
  `name_loc4` varchar(84) DEFAULT NULL,
  `name_loc6` varchar(89) DEFAULT NULL,
  `name_loc8` varchar(96) DEFAULT NULL,
  `conditionId` tinyint(3) unsigned NOT NULL,
  `skillLine` smallint(5) unsigned NOT NULL,
  `skillLevel` smallint(5) unsigned NOT NULL,
  `requiredLevel` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemrandomenchant`
--

DROP TABLE IF EXISTS `aowow_itemrandomenchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemrandomenchant` (
  `id` smallint(6) NOT NULL,
  `name_loc0` varchar(250) DEFAULT NULL,
  `name_loc2` varchar(250) DEFAULT NULL,
  `name_loc3` varchar(250) DEFAULT NULL,
  `name_loc4` varchar(250) DEFAULT NULL,
  `name_loc6` varchar(250) DEFAULT NULL,
  `name_loc8` varchar(250) DEFAULT NULL,
  `nameINT` char(250) NOT NULL,
  `enchantId1` smallint(5) unsigned NOT NULL,
  `enchantId2` smallint(5) unsigned NOT NULL,
  `enchantId3` smallint(5) unsigned NOT NULL,
  `enchantId4` smallint(5) unsigned NOT NULL,
  `enchantId5` smallint(5) unsigned NOT NULL,
  `allocationPct1` smallint(5) unsigned NOT NULL,
  `allocationPct2` smallint(5) unsigned NOT NULL,
  `allocationPct3` smallint(5) unsigned NOT NULL,
  `allocationPct4` smallint(5) unsigned NOT NULL,
  `allocationPct5` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_items`
--

DROP TABLE IF EXISTS `aowow_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_items` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `class` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `classBak` tinyint(4) NOT NULL,
  `subClass` tinyint(4) NOT NULL DEFAULT 0,
  `subClassBak` tinyint(4) NOT NULL,
  `soundOverrideSubclass` tinyint(4) NOT NULL,
  `subSubClass` tinyint(4) NOT NULL,
  `name_loc0` varchar(127) DEFAULT NULL,
  `name_loc2` varchar(127) DEFAULT NULL,
  `name_loc3` varchar(127) DEFAULT NULL,
  `name_loc4` varchar(127) DEFAULT NULL,
  `name_loc6` varchar(127) DEFAULT NULL,
  `name_loc8` varchar(127) DEFAULT NULL,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `displayId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spellVisualId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `quality` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `flags` int(10) unsigned NOT NULL DEFAULT 0,
  `flagsExtra` int(10) unsigned NOT NULL DEFAULT 0,
  `buyCount` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `buyPrice` int(11) NOT NULL DEFAULT 0,
  `sellPrice` int(10) unsigned NOT NULL DEFAULT 0,
  `repairPrice` int(10) unsigned NOT NULL,
  `slot` tinyint(4) NOT NULL,
  `slotBak` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `requiredClass` smallint(5) unsigned NOT NULL DEFAULT 0,
  `requiredRace` smallint(5) unsigned NOT NULL DEFAULT 0,
  `itemLevel` smallint(5) unsigned NOT NULL DEFAULT 0,
  `requiredLevel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `requiredSkill` smallint(5) unsigned NOT NULL DEFAULT 0,
  `requiredSkillRank` smallint(5) unsigned NOT NULL DEFAULT 0,
  `requiredSpell` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `requiredHonorRank` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `requiredCityRank` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `requiredFaction` smallint(5) unsigned NOT NULL DEFAULT 0,
  `requiredFactionRank` smallint(5) unsigned NOT NULL DEFAULT 0,
  `maxCount` int(11) NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `model` varchar(50) NOT NULL,
  `stackable` int(11) DEFAULT 1,
  `slots` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statType1` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue1` smallint(6) NOT NULL DEFAULT 0,
  `statType2` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue2` smallint(6) NOT NULL DEFAULT 0,
  `statType3` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue3` smallint(6) NOT NULL DEFAULT 0,
  `statType4` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue4` smallint(6) NOT NULL DEFAULT 0,
  `statType5` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue5` smallint(6) NOT NULL DEFAULT 0,
  `statType6` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue6` smallint(6) NOT NULL DEFAULT 0,
  `statType7` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue7` smallint(6) NOT NULL DEFAULT 0,
  `statType8` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue8` smallint(6) NOT NULL DEFAULT 0,
  `statType9` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue9` smallint(6) NOT NULL DEFAULT 0,
  `statType10` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `statValue10` smallint(6) NOT NULL DEFAULT 0,
  `scalingStatDistribution` smallint(6) NOT NULL DEFAULT 0,
  `scalingStatValue` int(10) unsigned NOT NULL DEFAULT 0,
  `dmgMin1` float NOT NULL DEFAULT 0,
  `dmgMax1` float NOT NULL DEFAULT 0,
  `dmgType1` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dmgMin2` float NOT NULL DEFAULT 0,
  `dmgMax2` float NOT NULL DEFAULT 0,
  `dmgType2` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `delay` smallint(5) unsigned NOT NULL DEFAULT 1000,
  `armor` smallint(5) unsigned NOT NULL DEFAULT 0,
  `armorDamageModifier` float NOT NULL DEFAULT 0,
  `block` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `resHoly` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `resFire` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `resNature` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `resFrost` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `resShadow` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `resArcane` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ammoType` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rangedModRange` float NOT NULL DEFAULT 0,
  `spellId1` mediumint(9) NOT NULL DEFAULT 0,
  `spellTrigger1` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `spellCharges1` smallint(6) DEFAULT NULL,
  `spellppmRate1` float NOT NULL DEFAULT 0,
  `spellCooldown1` int(11) NOT NULL DEFAULT -1,
  `spellCategory1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `spellCategoryCooldown1` int(11) NOT NULL DEFAULT -1,
  `spellId2` mediumint(9) NOT NULL DEFAULT 0,
  `spellTrigger2` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `spellCharges2` smallint(6) DEFAULT NULL,
  `spellppmRate2` float NOT NULL DEFAULT 0,
  `spellCooldown2` int(11) NOT NULL DEFAULT -1,
  `spellCategory2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `spellCategoryCooldown2` int(11) NOT NULL DEFAULT -1,
  `spellId3` mediumint(9) NOT NULL DEFAULT 0,
  `spellTrigger3` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `spellCharges3` smallint(6) DEFAULT NULL,
  `spellppmRate3` float NOT NULL DEFAULT 0,
  `spellCooldown3` int(11) NOT NULL DEFAULT -1,
  `spellCategory3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `spellCategoryCooldown3` int(11) NOT NULL DEFAULT -1,
  `spellId4` mediumint(9) NOT NULL DEFAULT 0,
  `spellTrigger4` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `spellCharges4` smallint(6) DEFAULT NULL,
  `spellppmRate4` float NOT NULL DEFAULT 0,
  `spellCooldown4` int(11) NOT NULL DEFAULT -1,
  `spellCategory4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `spellCategoryCooldown4` int(11) NOT NULL DEFAULT -1,
  `spellId5` mediumint(9) NOT NULL DEFAULT 0,
  `spellTrigger5` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `spellCharges5` smallint(6) DEFAULT NULL,
  `spellppmRate5` float NOT NULL DEFAULT 0,
  `spellCooldown5` int(11) NOT NULL DEFAULT -1,
  `spellCategory5` smallint(5) unsigned NOT NULL DEFAULT 0,
  `spellCategoryCooldown5` int(11) NOT NULL DEFAULT -1,
  `bonding` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `description_loc0` varchar(255) DEFAULT NULL,
  `description_loc2` varchar(255) DEFAULT NULL,
  `description_loc3` varchar(255) DEFAULT NULL,
  `description_loc4` varchar(255) DEFAULT NULL,
  `description_loc6` varchar(255) DEFAULT NULL,
  `description_loc8` varchar(255) DEFAULT NULL,
  `pageTextId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `languageId` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `startQuest` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `lockId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `material` tinyint(4) NOT NULL DEFAULT 0,
  `randomEnchant` mediumint(9) NOT NULL DEFAULT 0,
  `itemset` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `durability` smallint(5) unsigned NOT NULL DEFAULT 0,
  `area` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `map` smallint(6) NOT NULL DEFAULT 0,
  `bagFamily` mediumint(9) NOT NULL DEFAULT 0,
  `totemCategory` mediumint(9) NOT NULL DEFAULT 0,
  `socketColor1` tinyint(4) NOT NULL DEFAULT 0,
  `socketContent1` mediumint(9) NOT NULL DEFAULT 0,
  `socketColor2` tinyint(4) NOT NULL DEFAULT 0,
  `socketContent2` mediumint(9) NOT NULL DEFAULT 0,
  `socketColor3` tinyint(4) NOT NULL DEFAULT 0,
  `socketContent3` mediumint(9) NOT NULL DEFAULT 0,
  `socketBonus` mediumint(9) NOT NULL DEFAULT 0,
  `gemColorMask` mediumint(9) NOT NULL DEFAULT 0,
  `requiredDisenchantSkill` smallint(6) NOT NULL DEFAULT -1,
  `disenchantId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `duration` int(10) unsigned NOT NULL DEFAULT 0,
  `itemLimitCategory` smallint(6) NOT NULL DEFAULT 0,
  `eventId` smallint(5) unsigned NOT NULL,
  `scriptName` varchar(64) NOT NULL DEFAULT '',
  `foodType` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `gemEnchantmentId` mediumint(9) NOT NULL,
  `minMoneyLoot` int(10) unsigned NOT NULL DEFAULT 0,
  `maxMoneyLoot` int(10) unsigned NOT NULL DEFAULT 0,
  `pickUpSoundId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `dropDownSoundId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sheatheSoundId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `unsheatheSoundId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `flagsCustom` int(10) unsigned NOT NULL DEFAULT 0,
  `effects_loc0` text DEFAULT NULL,
  `effects_loc2` text DEFAULT NULL,
  `effects_loc3` text DEFAULT NULL,
  `effects_loc4` text DEFAULT NULL,
  `effects_loc6` text DEFAULT NULL,
  `effects_loc8` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_index` (`class`),
  KEY `idx_model` (`displayId`),
  KEY `idx_faction` (`requiredFaction`),
  KEY `iconId` (`iconId`),
  KEY `idx_spell1` (`spellId1`),
  KEY `idx_spell2` (`spellId2`),
  KEY `idx_spell3` (`spellId3`),
  KEY `idx_spell4` (`spellId4`),
  KEY `idx_spell5` (`spellId5`),
  KEY `idx_trigger1` (`spellTrigger1`),
  KEY `idx_trigger2` (`spellTrigger2`),
  KEY `idx_trigger3` (`spellTrigger3`),
  KEY `idx_trigger4` (`spellTrigger4`),
  KEY `idx_trigger5` (`spellTrigger5`),
  KEY `idx_reqskill` (`requiredSkill`),
  FULLTEXT `idx_ft_name0` (`name_loc0`),
  FULLTEXT `idx_ft_name2` (`name_loc2`),
  FULLTEXT `idx_ft_name3` (`name_loc3`),
  FULLTEXT `idx_ft_name6` (`name_loc6`),
  FULLTEXT `idx_ft_name8` (`name_loc8`),
  KEY `idx_name0` (`name_loc0`),
  KEY `idx_name2` (`name_loc2`),
  KEY `idx_name3` (`name_loc3`),
  KEY `idx_name4` (`name_loc4`),
  KEY `idx_name6` (`name_loc6`),
  KEY `idx_name8` (`name_loc8`),
  KEY `idx_itemset` (`itemset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_items_sounds`
--

DROP TABLE IF EXISTS `aowow_items_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_items_sounds` (
  `soundId` smallint(5) unsigned NOT NULL,
  `subClassMask` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`soundId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='actually .. its only weapon related sounds in here';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemset`
--

DROP TABLE IF EXISTS `aowow_itemset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemset` (
  `id` int(11) NOT NULL,
  `refSetId` int(11) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `name_loc0` varchar(255) DEFAULT NULL,
  `name_loc2` varchar(255) DEFAULT NULL,
  `name_loc3` varchar(255) DEFAULT NULL,
  `name_loc4` varchar(255) DEFAULT NULL,
  `name_loc6` varchar(255) DEFAULT NULL,
  `name_loc8` varchar(255) DEFAULT NULL,
  `item1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item5` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item6` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item7` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item8` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item9` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `item10` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell5` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell6` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell7` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `spell8` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `bonus1` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus2` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus3` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus4` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus5` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus6` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus7` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonus8` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bonusText_loc0` text DEFAULT NULL,
  `bonusText_loc2` text DEFAULT NULL,
  `bonusText_loc3` text DEFAULT NULL,
  `bonusText_loc4` text DEFAULT NULL,
  `bonusText_loc6` text DEFAULT NULL,
  `bonusText_loc8` text DEFAULT NULL,
  `npieces` tinyint(4) NOT NULL DEFAULT 0,
  `minLevel` smallint(6) NOT NULL DEFAULT 0,
  `maxLevel` smallint(6) NOT NULL DEFAULT 0,
  `reqLevel` smallint(6) NOT NULL DEFAULT 0,
  `classMask` mediumint(9) NOT NULL DEFAULT 0,
  `heroic` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'bool',
  `quality` tinyint(4) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 0 COMMENT 'g_itemset_types',
  `contentGroup` smallint(6) NOT NULL DEFAULT 0 COMMENT 'g_itemset_notes',
  `eventId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `skillId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `skillLevel` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_loot_link`
--

DROP TABLE IF EXISTS `aowow_loot_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_loot_link` (
  `npcId` mediumint(8) unsigned NOT NULL,
  `objectId` mediumint(8) unsigned NOT NULL,
  `difficulty` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `priority` tinyint(3) unsigned NOT NULL COMMENT '1: use this npc from group encounter (others 0)',
  `encounterId` mediumint(8) unsigned NOT NULL COMMENT 'as title reference',
  UNIQUE KEY `npcId_difficulty` (`npcId`,`difficulty`),
  KEY `objectId` (`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_mails`
--

DROP TABLE IF EXISTS `aowow_mails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_mails` (
  `id` smallint(6) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `subject_loc0` varchar(128) DEFAULT NULL,
  `subject_loc2` varchar(128) DEFAULT NULL,
  `subject_loc3` varchar(128) DEFAULT NULL,
  `subject_loc4` varchar(128) DEFAULT NULL,
  `subject_loc6` varchar(128) DEFAULT NULL,
  `subject_loc8` varchar(128) DEFAULT NULL,
  `text_loc0` text DEFAULT NULL,
  `text_loc2` text DEFAULT NULL,
  `text_loc3` text DEFAULT NULL,
  `text_loc4` text DEFAULT NULL,
  `text_loc6` text DEFAULT NULL,
  `text_loc8` text DEFAULT NULL,
  `attachment` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_objectdifficulty`
--

DROP TABLE IF EXISTS `aowow_objectdifficulty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_objectdifficulty` (
  `normal10` mediumint(8) unsigned NOT NULL,
  `normal25` mediumint(8) unsigned NOT NULL,
  `heroic10` mediumint(8) unsigned NOT NULL,
  `heroic25` mediumint(8) unsigned NOT NULL,
  `mapType` tinyint(3) unsigned NOT NULL,
  KEY `normal10` (`normal10`),
  KEY `normal25` (`normal25`),
  KEY `heroic10` (`heroic10`),
  KEY `heroic25` (`heroic25`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_objects`
--

DROP TABLE IF EXISTS `aowow_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_objects` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `typeCat` tinyint(4) NOT NULL DEFAULT 0,
  `event` smallint(5) unsigned NOT NULL DEFAULT 0,
  `displayId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `name_loc0` varchar(100) DEFAULT NULL,
  `name_loc2` varchar(100) DEFAULT NULL,
  `name_loc3` varchar(100) DEFAULT NULL,
  `name_loc4` varchar(100) DEFAULT NULL,
  `name_loc6` varchar(100) DEFAULT NULL,
  `name_loc8` varchar(100) DEFAULT NULL,
  `faction` smallint(5) unsigned NOT NULL DEFAULT 0,
  `flags` int(10) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `lootId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `lockId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSkill` smallint(5) unsigned NOT NULL DEFAULT 0,
  `pageTextId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `linkedTrap` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqQuest` mediumint(9) NOT NULL DEFAULT 0,
  `spellFocusId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `onUseSpell` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `onSuccessSpell` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `auraSpell` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `triggeredSpell` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `miscInfo` varchar(128) NOT NULL,
  `ScriptOrAI` varchar(64) DEFAULT NULL,
  `StringId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_onusespell` (`onUseSpell`),
  KEY `idx_onsuccessspell` (`onSuccessSpell`),
  KEY `idx_auraspell` (`auraSpell`),
  KEY `idx_triggeredspell` (`triggeredSpell`),
  FULLTEXT `idx_ft_name0` (`name_loc0`),
  FULLTEXT `idx_ft_name2` (`name_loc2`),
  FULLTEXT `idx_ft_name3` (`name_loc3`),
  FULLTEXT `idx_ft_name6` (`name_loc6`),
  FULLTEXT `idx_ft_name8` (`name_loc8`),
  KEY `idx_name0` (`name_loc0`),
  KEY `idx_name2` (`name_loc2`),
  KEY `idx_name3` (`name_loc3`),
  KEY `idx_name4` (`name_loc4`),
  KEY `idx_name6` (`name_loc6`),
  KEY `idx_name8` (`name_loc8`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_pet`
--

DROP TABLE IF EXISTS `aowow_pet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_pet` (
  `id` int(11) NOT NULL,
  `category` mediumint(9) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `minLevel` smallint(6) NOT NULL,
  `maxLevel` smallint(6) NOT NULL,
  `foodMask` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `exotic` tinyint(4) NOT NULL,
  `expansion` tinyint(4) NOT NULL,
  `name_loc0` varchar(64) DEFAULT NULL,
  `name_loc2` varchar(64) DEFAULT NULL,
  `name_loc3` varchar(64) DEFAULT NULL,
  `name_loc4` varchar(64) DEFAULT NULL,
  `name_loc6` varchar(64) DEFAULT NULL,
  `name_loc8` varchar(64) DEFAULT NULL,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `skillLineId` mediumint(9) NOT NULL,
  `spellId1` mediumint(9) NOT NULL,
  `spellId2` mediumint(9) NOT NULL,
  `spellId3` mediumint(9) NOT NULL,
  `spellId4` mediumint(9) NOT NULL,
  `armor` mediumint(9) NOT NULL,
  `damage` mediumint(9) NOT NULL,
  `health` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `iconId` (`iconId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_arena_team`
--

DROP TABLE IF EXISTS `aowow_profiler_arena_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_arena_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `realm` tinyint(3) unsigned NOT NULL,
  `realmGUID` int(10) unsigned NOT NULL,
  `name` varchar(24) NOT NULL,
  `nameUrl` varchar(24) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `stub` tinyint(1) DEFAULT 0 COMMENT 'arena team stub needs resync',
  `rating` smallint(5) unsigned NOT NULL DEFAULT 0,
  `seasonGames` smallint(5) unsigned NOT NULL DEFAULT 0,
  `seasonWins` smallint(5) unsigned NOT NULL DEFAULT 0,
  `weekGames` smallint(5) unsigned NOT NULL DEFAULT 0,
  `weekWins` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rank` int(10) unsigned NOT NULL DEFAULT 0,
  `backgroundColor` int(10) unsigned NOT NULL DEFAULT 0,
  `emblemStyle` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `emblemColor` int(10) unsigned NOT NULL DEFAULT 0,
  `borderStyle` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `borderColor` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realm_realmGUID` (`realm`,`realmGUID`),
  KEY `name` (`name`),
  KEY `idx_stub` (`stub`),
  KEY `idx_type` (`type`),
  KEY `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_arena_team_member`
--

DROP TABLE IF EXISTS `aowow_profiler_arena_team_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_arena_team_member` (
  `arenaTeamId` int(10) unsigned NOT NULL DEFAULT 0,
  `profileId` int(10) unsigned NOT NULL DEFAULT 0,
  `captain` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `weekGames` smallint(5) unsigned NOT NULL DEFAULT 0,
  `weekWins` smallint(5) unsigned NOT NULL DEFAULT 0,
  `seasonGames` smallint(5) unsigned NOT NULL DEFAULT 0,
  `seasonWins` smallint(5) unsigned NOT NULL DEFAULT 0,
  `personalRating` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`arenaTeamId`,`profileId`),
  KEY `guid` (`profileId`),
  CONSTRAINT `FK_aowow_profiler_arena_team_member_aowow_profiler_arena_team` FOREIGN KEY (`arenaTeamId`) REFERENCES `aowow_profiler_arena_team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_aowow_profiler_arena_team_member_aowow_profiler_profiles` FOREIGN KEY (`profileId`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_achievements`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_achievements` (
  `id` int(10) unsigned NOT NULL,
  `achievementId` smallint(5) unsigned NOT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  KEY `id` (`id`),
  KEY `typeId` (`achievementId`),
  CONSTRAINT `FK_pr_completion_achievements` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_quests`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_quests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_quests` (
  `id` int(10) unsigned NOT NULL,
  `questId` mediumint(8) unsigned NOT NULL,
  KEY `id` (`id`),
  KEY `typeId` (`questId`),
  CONSTRAINT `FK_pr_completion_quests` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_reputation`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_reputation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_reputation` (
  `id` int(10) unsigned NOT NULL,
  `factionId` smallint(5) unsigned NOT NULL,
  `standing` mediumint(9) DEFAULT NULL,
  `exalted` tinyint(1) GENERATED ALWAYS AS (`standing` >= 42000) STORED,
  KEY `id` (`id`),
  KEY `typeId` (`factionId`),
  KEY `idx_exalted` (`exalted`),
  CONSTRAINT `FK_pr_completion_reputation` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_skills`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_skills` (
  `id` int(10) unsigned NOT NULL,
  `skillId` smallint(5) unsigned NOT NULL,
  `value` smallint(5) unsigned DEFAULT NULL,
  `max` smallint(5) unsigned DEFAULT NULL,
  KEY `id` (`id`),
  KEY `typeId` (`skillId`),
  KEY `idx_value` (`value`),
  CONSTRAINT `FK_pr_completion_skills` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_spells`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_spells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_spells` (
  `id` int(10) unsigned NOT NULL,
  `spellId` mediumint(8) unsigned NOT NULL,
  KEY `id` (`id`),
  KEY `typeId` (`spellId`),
  CONSTRAINT `FK_pr_completion_spells` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_statistics`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_statistics` (
  `id` int(10) unsigned NOT NULL,
  `achievementId` smallint(6) NOT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  `counter` smallint(5) unsigned DEFAULT NULL,
  KEY `id` (`id`),
  KEY `typeId` (`achievementId`),
  CONSTRAINT `FK_pr_completion_statistics` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_completion_titles`
--

DROP TABLE IF EXISTS `aowow_profiler_completion_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_completion_titles` (
  `id` int(10) unsigned NOT NULL,
  `titleId` tinyint(3) unsigned NOT NULL,
  KEY `id` (`id`),
  KEY `typeId` (`titleId`),
  CONSTRAINT `FK_pr_completion_titles` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_excludes`
--

DROP TABLE IF EXISTS `aowow_profiler_excludes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_excludes` (
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(8) unsigned NOT NULL,
  `groups` smallint(5) unsigned NOT NULL COMMENT 'see exclude group defines',
  `comment` varchar(50) NOT NULL COMMENT 'rebuilding profiler files will delete everything without a comment',
  PRIMARY KEY (`type`,`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_guild`
--

DROP TABLE IF EXISTS `aowow_profiler_guild`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_guild` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `realm` int(10) unsigned NOT NULL,
  `realmGUID` int(10) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `stub` tinyint(1) DEFAULT 0 COMMENT 'guild stub needs resync',
  `name` varchar(26) NOT NULL,
  `nameUrl` varchar(26) NOT NULL,
  `emblemStyle` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `emblemColor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `borderStyle` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `borderColor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `backgroundColor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `info` varchar(500) NOT NULL DEFAULT '',
  `createDate` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realm_realmGUID` (`realm`,`realmGUID`),
  KEY `name` (`name`),
  KEY `idx_stub` (`stub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_guild_rank`
--

DROP TABLE IF EXISTS `aowow_profiler_guild_rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_guild_rank` (
  `guildId` int(10) unsigned NOT NULL DEFAULT 0,
  `rank` tinyint(3) unsigned NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`guildId`,`rank`),
  KEY `rank` (`rank`),
  CONSTRAINT `FK_aowow_profiler_guild_rank_aowow_profiler_guild` FOREIGN KEY (`guildId`) REFERENCES `aowow_profiler_guild` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_items`
--

DROP TABLE IF EXISTS `aowow_profiler_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_items` (
  `id` int(10) unsigned DEFAULT NULL,
  `slot` tinyint(3) unsigned DEFAULT NULL,
  `item` mediumint(8) unsigned DEFAULT NULL,
  `subItem` smallint(6) DEFAULT NULL,
  `permEnchant` mediumint(8) unsigned DEFAULT NULL,
  `tempEnchant` mediumint(8) unsigned DEFAULT NULL,
  `extraSocket` tinyint(3) unsigned DEFAULT NULL COMMENT 'not used .. the appropriate gem slot is set to -1 instead',
  `gem1` mediumint(9) DEFAULT NULL,
  `gem2` mediumint(9) DEFAULT NULL,
  `gem3` mediumint(9) DEFAULT NULL,
  `gem4` mediumint(9) DEFAULT NULL,
  UNIQUE KEY `id_slot` (`id`,`slot`),
  KEY `id` (`id`),
  KEY `item` (`item`),
  CONSTRAINT `FK_pr_items` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_pets`
--

DROP TABLE IF EXISTS `aowow_profiler_pets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_pets` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `owner` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `family` tinyint(3) unsigned DEFAULT NULL,
  `npc` smallint(5) unsigned DEFAULT NULL,
  `displayId` smallint(5) unsigned DEFAULT NULL,
  `talents` varchar(22) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`),
  CONSTRAINT `FK_pr_pets` FOREIGN KEY (`owner`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_profiles`
--

DROP TABLE IF EXISTS `aowow_profiler_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `realm` tinyint(3) unsigned DEFAULT NULL,
  `realmGUID` int(10) unsigned DEFAULT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `custom` tinyint(1) DEFAULT 0 COMMENT 'custom profile',
  `stub` tinyint(1) DEFAULT 0 COMMENT 'profile stub needs resync',
  `deleted` tinyint(1) DEFAULT 0 COMMENT 'only on custom profiles',
  `sourceId` int(10) unsigned DEFAULT NULL,
  `sourceName` varchar(50) DEFAULT NULL,
  `copy` int(10) unsigned DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `renameItr` tinyint(3) unsigned DEFAULT NULL,
  `race` tinyint(3) unsigned NOT NULL,
  `class` tinyint(3) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `gender` tinyint(3) unsigned NOT NULL,
  `guild` int(10) unsigned DEFAULT NULL,
  `guildrank` tinyint(3) unsigned DEFAULT NULL COMMENT '0: guild master',
  `skincolor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `hairstyle` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `haircolor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `facetype` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `features` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `nomodelMask` int(10) unsigned NOT NULL DEFAULT 0,
  `title` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `playedtime` int(10) unsigned NOT NULL DEFAULT 0,
  `gearscore` smallint(5) unsigned NOT NULL DEFAULT 0,
  `achievementpoints` smallint(5) unsigned NOT NULL DEFAULT 0,
  `lastupdated` int(11) NOT NULL DEFAULT 0,
  `talenttree1` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'points spend in 1st tree',
  `talenttree2` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'points spend in 2nd tree',
  `talenttree3` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'points spend in 3rd tree',
  `talentbuild1` varchar(105) NOT NULL DEFAULT '',
  `talentbuild2` varchar(105) NOT NULL DEFAULT '',
  `glyphs1` varchar(45) NOT NULL DEFAULT '',
  `glyphs2` varchar(45) NOT NULL DEFAULT '',
  `activespec` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realm_realmGUID` (`realm`,`realmGUID`),
  KEY `user` (`user`),
  KEY `guild` (`guild`),
  KEY `name` (`name`),
  KEY `idx_custom` (`custom`),
  KEY `idx_stub` (`stub`),
  KEY `idx_deleted` (`deleted`),
  KEY `idx_race` (`race`),
  KEY `idx_class` (`class`),
  KEY `idx_level` (`level`),
  KEY `idx_guildrank` (`guildrank`),
  KEY `idx_gearscore` (`gearscore`),
  KEY `idx_achievementpoints` (`achievementpoints`),
  KEY `idx_talenttree1` (`talenttree1`),
  KEY `idx_talenttree2` (`talenttree2`),
  KEY `idx_talenttree3` (`talenttree3`),
  CONSTRAINT `FK_aowow_profiler_profiles_aowow_profiler_guild` FOREIGN KEY (`guild`) REFERENCES `aowow_profiler_guild` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_profiler_sync`
--

DROP TABLE IF EXISTS `aowow_profiler_sync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_profiler_sync` (
  `realm` tinyint(3) unsigned NOT NULL,
  `realmGUID` int(10) unsigned NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` int(10) unsigned NOT NULL,
  `requestTime` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `errorCode` tinyint(3) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `realm_realmGUID_type_typeId` (`realm`,`realmGUID`,`type`),
  UNIQUE KEY `type_typeId` (`type`,`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_quests`
--

DROP TABLE IF EXISTS `aowow_quests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_quests` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `questType` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `level` smallint(6) NOT NULL DEFAULT 1,
  `minLevel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `maxLevel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `questSortId` smallint(6) NOT NULL DEFAULT 0,
  `questSortIdBak` smallint(6) NOT NULL DEFAULT 0,
  `questInfoId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `suggestedPlayers` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `timeLimit` int(10) unsigned NOT NULL DEFAULT 0,
  `eventId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `prevQuestId` mediumint(9) NOT NULL DEFAULT 0,
  `nextQuestId` mediumint(9) NOT NULL DEFAULT 0,
  `breadcrumbForQuestId` mediumint(9) NOT NULL DEFAULT 0,
  `exclusiveGroup` mediumint(9) NOT NULL DEFAULT 0,
  `nextQuestIdChain` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `flags` int(10) unsigned NOT NULL DEFAULT 0,
  `specialFlags` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `reqClassMask` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqRaceMask` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSkillId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSkillPoints` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqFactionId1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqFactionId2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqFactionValue1` mediumint(9) NOT NULL DEFAULT 0,
  `reqFactionValue2` mediumint(9) NOT NULL DEFAULT 0,
  `reqMinRepFaction` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqMaxRepFaction` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqMinRepValue` mediumint(9) NOT NULL DEFAULT 0,
  `reqMaxRepValue` mediumint(9) NOT NULL DEFAULT 0,
  `reqPlayerKills` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sourceItemId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `sourceItemCount` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sourceSpellId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardXP` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardOrReqMoney` int(11) NOT NULL DEFAULT 0,
  `rewardMoneyMaxLevel` int(10) unsigned NOT NULL DEFAULT 0,
  `rewardSpell` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardSpellCast` int(11) NOT NULL DEFAULT 0,
  `rewardHonorPoints` int(11) NOT NULL DEFAULT 0,
  `rewardMailTemplateId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardMailDelay` int(10) unsigned NOT NULL DEFAULT 0,
  `rewardTitleId` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rewardTalents` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rewardArenaPoints` smallint(6) NOT NULL DEFAULT 0,
  `rewardItemId1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardItemId2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardItemId3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardItemId4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardItemCount1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardItemCount2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardItemCount3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardItemCount4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemId1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemId2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemId3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemId4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemId5` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemId6` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemCount1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemCount2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemCount3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemCount4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemCount5` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardChoiceItemCount6` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rewardFactionId1` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId2` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId3` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId4` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId5` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionValue1` mediumint(9) NOT NULL DEFAULT 0,
  `rewardFactionValue2` mediumint(9) NOT NULL DEFAULT 0,
  `rewardFactionValue3` mediumint(9) NOT NULL DEFAULT 0,
  `rewardFactionValue4` mediumint(9) NOT NULL DEFAULT 0,
  `rewardFactionValue5` mediumint(9) NOT NULL DEFAULT 0,
  `name_loc0` varchar(100) DEFAULT NULL,
  `name_loc2` varchar(100) DEFAULT NULL,
  `name_loc3` varchar(100) DEFAULT NULL,
  `name_loc4` varchar(100) DEFAULT NULL,
  `name_loc6` varchar(100) DEFAULT NULL,
  `name_loc8` varchar(100) DEFAULT NULL,
  `objectives_loc0` text DEFAULT NULL,
  `objectives_loc2` text DEFAULT NULL,
  `objectives_loc3` text DEFAULT NULL,
  `objectives_loc4` text DEFAULT NULL,
  `objectives_loc6` text DEFAULT NULL,
  `objectives_loc8` text DEFAULT NULL,
  `details_loc0` text DEFAULT NULL,
  `details_loc2` text DEFAULT NULL,
  `details_loc3` text DEFAULT NULL,
  `details_loc4` text DEFAULT NULL,
  `details_loc6` text DEFAULT NULL,
  `details_loc8` text DEFAULT NULL,
  `end_loc0` text DEFAULT NULL,
  `end_loc2` text DEFAULT NULL,
  `end_loc3` text DEFAULT NULL,
  `end_loc4` text DEFAULT NULL,
  `end_loc6` text DEFAULT NULL,
  `end_loc8` text DEFAULT NULL,
  `offerReward_loc0` text DEFAULT NULL,
  `offerReward_loc2` text DEFAULT NULL,
  `offerReward_loc3` text DEFAULT NULL,
  `offerReward_loc4` text DEFAULT NULL,
  `offerReward_loc6` text DEFAULT NULL,
  `offerReward_loc8` text DEFAULT NULL,
  `requestItems_loc0` text DEFAULT NULL,
  `requestItems_loc2` text DEFAULT NULL,
  `requestItems_loc3` text DEFAULT NULL,
  `requestItems_loc4` text DEFAULT NULL,
  `requestItems_loc6` text DEFAULT NULL,
  `requestItems_loc8` text DEFAULT NULL,
  `completed_loc0` text DEFAULT NULL,
  `completed_loc2` text DEFAULT NULL,
  `completed_loc3` text DEFAULT NULL,
  `completed_loc4` text DEFAULT NULL,
  `completed_loc6` text DEFAULT NULL,
  `completed_loc8` text DEFAULT NULL,
  `reqNpcOrGo1` mediumint(9) NOT NULL DEFAULT 0,
  `reqNpcOrGo2` mediumint(9) NOT NULL DEFAULT 0,
  `reqNpcOrGo3` mediumint(9) NOT NULL DEFAULT 0,
  `reqNpcOrGo4` mediumint(9) NOT NULL DEFAULT 0,
  `reqNpcOrGoCount1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqNpcOrGoCount2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqNpcOrGoCount3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqNpcOrGoCount4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemId1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemId2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemId3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemId4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemCount1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemCount2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemCount3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSourceItemCount4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqItemId1` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqItemId2` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqItemId3` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqItemId4` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqItemId5` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqItemId6` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqItemCount1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqItemCount2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqItemCount3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqItemCount4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqItemCount5` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqItemCount6` smallint(5) unsigned NOT NULL DEFAULT 0,
  `objectiveText1_loc0` text DEFAULT NULL,
  `objectiveText1_loc2` text DEFAULT NULL,
  `objectiveText1_loc3` text DEFAULT NULL,
  `objectiveText1_loc4` text DEFAULT NULL,
  `objectiveText1_loc6` text DEFAULT NULL,
  `objectiveText1_loc8` text DEFAULT NULL,
  `objectiveText2_loc0` text DEFAULT NULL,
  `objectiveText2_loc2` text DEFAULT NULL,
  `objectiveText2_loc3` text DEFAULT NULL,
  `objectiveText2_loc4` text DEFAULT NULL,
  `objectiveText2_loc6` text DEFAULT NULL,
  `objectiveText2_loc8` text DEFAULT NULL,
  `objectiveText3_loc0` text DEFAULT NULL,
  `objectiveText3_loc2` text DEFAULT NULL,
  `objectiveText3_loc3` text DEFAULT NULL,
  `objectiveText3_loc4` text DEFAULT NULL,
  `objectiveText3_loc6` text DEFAULT NULL,
  `objectiveText3_loc8` text DEFAULT NULL,
  `objectiveText4_loc0` text DEFAULT NULL,
  `objectiveText4_loc2` text DEFAULT NULL,
  `objectiveText4_loc3` text DEFAULT NULL,
  `objectiveText4_loc4` text DEFAULT NULL,
  `objectiveText4_loc6` text DEFAULT NULL,
  `objectiveText4_loc8` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nextQuestIdChain` (`nextQuestIdChain`),
  FULLTEXT `idx_ft_name0` (`name_loc0`),
  FULLTEXT `idx_ft_name2` (`name_loc2`),
  FULLTEXT `idx_ft_name3` (`name_loc3`),
  FULLTEXT `idx_ft_name6` (`name_loc6`),
  FULLTEXT `idx_ft_name8` (`name_loc8`),
  KEY `idx_name0` (`name_loc0`),
  KEY `idx_name2` (`name_loc2`),
  KEY `idx_name3` (`name_loc3`),
  KEY `idx_name4` (`name_loc4`),
  KEY `idx_name6` (`name_loc6`),
  KEY `idx_name8` (`name_loc8`),
  KEY `idx_sourcespell` (`sourceSpellId`),
  KEY `idx_rewardspell` (`rewardSpell`),
  KEY `idx_rewardcastspell` (`rewardSpellCast`),
  KEY `idx_classmask` (`reqRaceMask`),
  KEY `idx_racemask` (`reqClassMask`),
  KEY `idx_questsort` (`questSortId`),
  KEY `idx_rewarditem1` (`rewardChoiceItemId1`),
  KEY `idx_rewarditem2` (`rewardChoiceItemId2`),
  KEY `idx_rewarditem3` (`rewardChoiceItemId3`),
  KEY `idx_rewarditem4` (`rewardChoiceItemId4`),
  KEY `idx_rewarditem5` (`rewardChoiceItemId5`),
  KEY `idx_rewarditem6` (`rewardChoiceItemId6`),
  KEY `idx_rewardfaction1` (`rewardFactionId1`),
  KEY `idx_rewardfaction2` (`rewardFactionId2`),
  KEY `idx_rewardfaction3` (`rewardFactionId3`),
  KEY `idx_rewardfaction4` (`rewardFactionId4`),
  KEY `idx_rewardfaction5` (`rewardFactionId5`),
  KEY `idx_choiceitem1` (`rewardItemId1`),
  KEY `idx_choiceitem2` (`rewardItemId2`),
  KEY `idx_choiceitem3` (`rewardItemId3`),
  KEY `idx_choiceitem4` (`rewardItemId4`),
  KEY `idx_requirement1` (`reqNpcOrGo1`),
  KEY `idx_requirement2` (`reqNpcOrGo2`),
  KEY `idx_requirement3` (`reqNpcOrGo3`),
  KEY `idx_requirement4` (`reqNpcOrGo4`),
  KEY `idx_event` (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_quests_startend`
--

DROP TABLE IF EXISTS `aowow_quests_startend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_quests_startend` (
  `type` tinyint(3) unsigned NOT NULL,
  `typeId` mediumint(8) unsigned NOT NULL,
  `questId` mediumint(8) unsigned NOT NULL,
  `method` tinyint(3) unsigned NOT NULL COMMENT '&0x1: starts; &0x2:ends',
  `eventId` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`type`,`typeId`,`questId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_quickfacts`
--

DROP TABLE IF EXISTS `aowow_quickfacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_quickfacts` (
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `orderIdx` tinyint(4) NOT NULL COMMENT '<0: prepend to generic list; >0: append to generic list',
  `row` varchar(200) NOT NULL COMMENT 'Markdown formated',
  UNIQUE KEY `row` (`type`,`typeId`,`orderIdx`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_races`
--

DROP TABLE IF EXISTS `aowow_races`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_races` (
  `id` int(10) unsigned NOT NULL,
  `classMask` smallint(5) unsigned NOT NULL,
  `flags` tinyint(3) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `factionId` smallint(6) NOT NULL,
  `startAreaId` smallint(6) NOT NULL,
  `leader` mediumint(8) unsigned NOT NULL,
  `baseLanguage` tinyint(3) unsigned NOT NULL,
  `side` tinyint(3) unsigned NOT NULL,
  `fileString` varchar(64) DEFAULT NULL,
  `iconId0` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'male icon',
  `iconId1` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'female icon',
  `name_loc0` varchar(64) DEFAULT NULL,
  `name_loc2` varchar(64) DEFAULT NULL,
  `name_loc3` varchar(64) DEFAULT NULL,
  `name_loc4` varchar(64) DEFAULT NULL,
  `name_loc6` varchar(64) DEFAULT NULL,
  `name_loc8` varchar(64) DEFAULT NULL,
  `expansion` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_races_sounds`
--

DROP TABLE IF EXISTS `aowow_races_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_races_sounds` (
  `raceId` tinyint(3) unsigned NOT NULL,
  `soundId` smallint(5) unsigned NOT NULL,
  `gender` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `race_soundId_gender` (`raceId`,`soundId`,`gender`),
  KEY `race` (`raceId`),
  KEY `soundId` (`soundId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_reports`
--

DROP TABLE IF EXISTS `aowow_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_reports` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userId` mediumint(8) unsigned NOT NULL,
  `assigned` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0:new; 1:solved; 2:rejected',
  `createDate` int(10) unsigned NOT NULL,
  `mode` tinyint(3) unsigned NOT NULL,
  `reason` tinyint(3) unsigned NOT NULL,
  `subject` mediumint(9) NOT NULL DEFAULT 0,
  `ip` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `userAgent` varchar(255) NOT NULL,
  `appName` varchar(32) NOT NULL,
  `url` varchar(255) NOT NULL,
  `relatedUrl` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_screeneffect_sounds`
--

DROP TABLE IF EXISTS `aowow_screeneffect_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_screeneffect_sounds` (
  `id` smallint(5) unsigned NOT NULL,
  `name` varchar(40) NOT NULL,
  `ambienceDay` smallint(5) unsigned NOT NULL,
  `ambienceNight` smallint(5) unsigned NOT NULL,
  `musicDay` smallint(5) unsigned NOT NULL,
  `musicNight` smallint(5) unsigned NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_screenshots`
--

DROP TABLE IF EXISTS `aowow_screenshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_screenshots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `userIdOwner` int(10) unsigned DEFAULT NULL,
  `date` int(10) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL COMMENT 'see defines.php - CC_FLAG_*',
  `userIdApprove` int(10) unsigned DEFAULT NULL,
  `userIdDelete` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`typeId`),
  KEY `FK_acc_ss` (`userIdOwner`),
  CONSTRAINT `FK_acc_ss` FOREIGN KEY (`userIdOwner`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_setup_custom_data`
--

DROP TABLE IF EXISTS `aowow_setup_custom_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_setup_custom_data` (
  `command` varchar(100) NOT NULL DEFAULT '',
  `entry` int(11) NOT NULL DEFAULT 0 COMMENT 'typeId',
  `field` varchar(100) NOT NULL DEFAULT '',
  `value` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  KEY `aowow_setup_custom_data_command_IDX` (`command`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_shapeshiftforms`
--

DROP TABLE IF EXISTS `aowow_shapeshiftforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_shapeshiftforms` (
  `Id` tinyint(3) unsigned NOT NULL,
  `flags` smallint(5) unsigned NOT NULL,
  `creatureType` tinyint(4) NOT NULL,
  `displayIdA` smallint(5) unsigned NOT NULL,
  `displayIdH` smallint(5) unsigned NOT NULL,
  `spellId1` mediumint(8) unsigned NOT NULL,
  `spellId2` mediumint(8) unsigned NOT NULL,
  `spellId3` mediumint(8) unsigned NOT NULL,
  `spellId4` mediumint(8) unsigned NOT NULL,
  `spellId5` mediumint(8) unsigned NOT NULL,
  `spellId6` mediumint(8) unsigned NOT NULL,
  `spellId7` mediumint(8) unsigned NOT NULL,
  `spellId8` mediumint(8) unsigned NOT NULL,
  `comment` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_skillline`
--

DROP TABLE IF EXISTS `aowow_skillline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_skillline` (
  `Id` smallint(5) unsigned NOT NULL,
  `typeCat` tinyint(4) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `categoryId` tinyint(4) NOT NULL,
  `name_loc0` varchar(64) DEFAULT NULL,
  `name_loc2` varchar(64) DEFAULT NULL,
  `name_loc3` varchar(64) DEFAULT NULL,
  `name_loc4` varchar(64) DEFAULT NULL,
  `name_loc6` varchar(64) DEFAULT NULL,
  `name_loc8` varchar(64) DEFAULT NULL,
  `description_loc0` text DEFAULT NULL,
  `description_loc2` text DEFAULT NULL,
  `description_loc3` text DEFAULT NULL,
  `description_loc4` text DEFAULT NULL,
  `description_loc6` text DEFAULT NULL,
  `description_loc8` text DEFAULT NULL,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `iconIdBak` smallint(5) unsigned NOT NULL DEFAULT 0,
  `professionMask` smallint(5) unsigned NOT NULL,
  `recipeSubClass` tinyint(3) unsigned NOT NULL,
  `specializations` varchar(30) NOT NULL COMMENT 'space-separated spellIds',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_sounds`
--

DROP TABLE IF EXISTS `aowow_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_sounds` (
  `id` smallint(5) unsigned NOT NULL,
  `cat` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `soundFile1` smallint(5) unsigned DEFAULT NULL,
  `soundFile2` smallint(5) unsigned DEFAULT NULL,
  `soundFile3` smallint(5) unsigned DEFAULT NULL,
  `soundFile4` smallint(5) unsigned DEFAULT NULL,
  `soundFile5` smallint(5) unsigned DEFAULT NULL,
  `soundFile6` smallint(5) unsigned DEFAULT NULL,
  `soundFile7` smallint(5) unsigned DEFAULT NULL,
  `soundFile8` smallint(5) unsigned DEFAULT NULL,
  `soundFile9` smallint(5) unsigned DEFAULT NULL,
  `soundFile10` smallint(5) unsigned DEFAULT NULL,
  `flags` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cat` (`cat`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_sounds_files`
--

DROP TABLE IF EXISTS `aowow_sounds_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_sounds_files` (
  `id` smallint(6) NOT NULL COMMENT '<0 not found in client files',
  `file` varchar(75) NOT NULL,
  `path` varchar(75) NOT NULL COMMENT 'in client',
  `type` enum('OGG','MP3') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_source`
--

DROP TABLE IF EXISTS `aowow_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_source` (
  `type` tinyint(3) unsigned NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `moreType` tinyint(3) unsigned DEFAULT NULL,
  `moreTypeId` mediumint(8) unsigned DEFAULT NULL,
  `moreZoneId` mediumint(8) unsigned DEFAULT NULL,
  `moreMask` mediumint(8) unsigned DEFAULT NULL,
  `src1` tinyint(3) unsigned DEFAULT NULL COMMENT 'Crafted',
  `src2` tinyint(3) unsigned DEFAULT NULL COMMENT 'Drop (npc / object / item) (modeMask)',
  `src3` tinyint(3) unsigned DEFAULT NULL COMMENT 'PvP (g_sources_pvp)',
  `src4` tinyint(3) unsigned DEFAULT NULL COMMENT 'Quest (side)',
  `src5` tinyint(3) unsigned DEFAULT NULL COMMENT 'Vendor',
  `src6` tinyint(3) unsigned DEFAULT NULL COMMENT 'Trainer',
  `src7` tinyint(3) unsigned DEFAULT NULL COMMENT 'Discovery',
  `src8` tinyint(3) unsigned DEFAULT NULL COMMENT 'Redemption',
  `src9` tinyint(3) unsigned DEFAULT NULL COMMENT 'Talent',
  `src10` tinyint(3) unsigned DEFAULT NULL COMMENT 'Starter',
  `src11` tinyint(3) unsigned DEFAULT NULL COMMENT 'Event (special; not holidays) [not used]',
  `src12` tinyint(3) unsigned DEFAULT NULL COMMENT 'Achievemement',
  `src13` tinyint(3) unsigned DEFAULT NULL COMMENT 'Misc Source (sourceStringId)',
  `src14` tinyint(3) unsigned DEFAULT NULL COMMENT 'Black Market [not used]',
  `src15` tinyint(3) unsigned DEFAULT NULL COMMENT 'Disenchanted',
  `src16` tinyint(3) unsigned DEFAULT NULL COMMENT 'Fished',
  `src17` tinyint(3) unsigned DEFAULT NULL COMMENT 'Gathered',
  `src18` tinyint(3) unsigned DEFAULT NULL COMMENT 'Milled',
  `src19` tinyint(3) unsigned DEFAULT NULL COMMENT 'Mined',
  `src20` tinyint(3) unsigned DEFAULT NULL COMMENT 'Prospected',
  `src21` tinyint(3) unsigned DEFAULT NULL COMMENT 'Pickpocketed',
  `src22` tinyint(3) unsigned DEFAULT NULL COMMENT 'Salvaged',
  `src23` tinyint(3) unsigned DEFAULT NULL COMMENT 'Skinned',
  `src24` tinyint(3) unsigned DEFAULT NULL COMMENT 'In-Game Store [not used]',
  PRIMARY KEY (`type`,`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spawns`
--

DROP TABLE IF EXISTS `aowow_spawns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spawns` (
  `guid` int(11) NOT NULL COMMENT '< 0: vehicle accessory',
  `type` smallint(5) unsigned NOT NULL,
  `typeId` int(10) unsigned NOT NULL,
  `respawn` int(11) NOT NULL DEFAULT 0 COMMENT 'in seconds',
  `spawnMask` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `phaseMask` smallint(5) unsigned NOT NULL DEFAULT 0,
  `areaId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `floor` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `posX` float unsigned NOT NULL,
  `posY` float unsigned NOT NULL,
  `pathId` int(10) unsigned NOT NULL DEFAULT 0,
  `ScriptName` varchar(64) DEFAULT NULL,
  `StringId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`guid`,`type`,`floor`),
  KEY `type_idx` (`typeId`,`type`),
  KEY `zone_idx` (`areaId`),
  KEY `guid` (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spawns_override`
--

DROP TABLE IF EXISTS `aowow_spawns_override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spawns_override` (
  `type` smallint(5) unsigned NOT NULL,
  `typeGuid` mediumint(9) NOT NULL,
  `areaId` mediumint(8) unsigned NOT NULL,
  `floor` mediumint(8) unsigned NOT NULL,
  `revision` tinyint(3) unsigned NOT NULL COMMENT 'Aowow revision, when this override was applied',
  PRIMARY KEY (`type`,`typeGuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spell`
--

DROP TABLE IF EXISTS `aowow_spell`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spell` (
  `id` mediumint(8) unsigned NOT NULL,
  `category` smallint(5) unsigned NOT NULL,
  `dispelType` tinyint(3) unsigned NOT NULL,
  `mechanic` tinyint(3) unsigned NOT NULL,
  `attributes0` int(10) unsigned NOT NULL,
  `attributes1` int(10) unsigned NOT NULL,
  `attributes2` int(10) unsigned NOT NULL,
  `attributes3` int(10) unsigned NOT NULL,
  `attributes4` int(10) unsigned NOT NULL,
  `attributes5` int(10) unsigned NOT NULL,
  `attributes6` int(10) unsigned NOT NULL,
  `attributes7` int(10) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `typeCat` smallint(6) NOT NULL,
  `stanceMask` int(11) NOT NULL,
  `stanceMaskNot` int(11) NOT NULL,
  `targets` mediumint(8) unsigned NOT NULL,
  `spellFocusObject` smallint(5) unsigned NOT NULL,
  `castTime` float unsigned NOT NULL,
  `recoveryTime` int(10) unsigned NOT NULL,
  `recoveryCategory` int(10) unsigned NOT NULL,
  `startRecoveryTime` mediumint(8) unsigned NOT NULL,
  `startRecoveryCategory` smallint(5) unsigned NOT NULL,
  `procChance` tinyint(3) unsigned NOT NULL,
  `procCharges` mediumint(8) unsigned NOT NULL,
  `procCustom` float NOT NULL,
  `procCooldown` smallint(5) unsigned NOT NULL,
  `maxLevel` smallint(5) unsigned NOT NULL,
  `baseLevel` smallint(5) unsigned NOT NULL,
  `spellLevel` smallint(5) unsigned NOT NULL,
  `talentLevel` tinyint(3) unsigned NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 0,
  `powerType` smallint(6) NOT NULL,
  `powerCost` smallint(5) unsigned NOT NULL,
  `powerCostPerLevel` tinyint(3) unsigned NOT NULL,
  `powerCostPercent` tinyint(3) unsigned NOT NULL,
  `powerPerSecond` smallint(5) unsigned NOT NULL,
  `powerPerSecondPerLevel` tinyint(3) unsigned NOT NULL,
  `powerGainRunicPower` smallint(5) unsigned NOT NULL,
  `powerCostRunes` smallint(5) unsigned NOT NULL,
  `rangeId` smallint(5) unsigned NOT NULL,
  `stackAmount` mediumint(8) unsigned NOT NULL,
  `tool1` mediumint(8) unsigned NOT NULL,
  `tool2` mediumint(8) unsigned NOT NULL,
  `toolCategory1` tinyint(3) unsigned NOT NULL,
  `toolCategory2` tinyint(3) unsigned NOT NULL,
  `reagent1` mediumint(9) NOT NULL,
  `reagent2` mediumint(9) NOT NULL,
  `reagent3` mediumint(9) NOT NULL,
  `reagent4` mediumint(9) NOT NULL,
  `reagent5` mediumint(9) NOT NULL,
  `reagent6` mediumint(9) NOT NULL,
  `reagent7` mediumint(9) NOT NULL,
  `reagent8` mediumint(9) NOT NULL,
  `reagentCount1` tinyint(4) NOT NULL,
  `reagentCount2` tinyint(4) NOT NULL,
  `reagentCount3` tinyint(4) NOT NULL,
  `reagentCount4` tinyint(4) NOT NULL,
  `reagentCount5` tinyint(4) NOT NULL,
  `reagentCount6` tinyint(4) NOT NULL,
  `reagentCount7` tinyint(4) NOT NULL,
  `reagentCount8` tinyint(4) NOT NULL,
  `equippedItemClass` tinyint(4) NOT NULL,
  `equippedItemSubClassMask` int(11) NOT NULL,
  `equippedItemInventoryTypeMask` int(10) unsigned NOT NULL,
  `effect1Id` smallint(5) unsigned NOT NULL,
  `effect2Id` smallint(5) unsigned NOT NULL,
  `effect3Id` smallint(5) unsigned NOT NULL,
  `effect1DieSides` int(11) NOT NULL,
  `effect2DieSides` int(11) NOT NULL,
  `effect3DieSides` int(11) NOT NULL,
  `effect1RealPointsPerLevel` float NOT NULL,
  `effect2RealPointsPerLevel` float NOT NULL,
  `effect3RealPointsPerLevel` float NOT NULL,
  `effect1BasePoints` int(11) NOT NULL,
  `effect2BasePoints` int(11) NOT NULL,
  `effect3BasePoints` int(11) NOT NULL,
  `effect1Mechanic` tinyint(3) unsigned NOT NULL,
  `effect2Mechanic` tinyint(3) unsigned NOT NULL,
  `effect3Mechanic` tinyint(3) unsigned NOT NULL,
  `effect1ImplicitTargetA` smallint(6) NOT NULL,
  `effect2ImplicitTargetA` smallint(6) NOT NULL,
  `effect3ImplicitTargetA` smallint(6) NOT NULL,
  `effect1ImplicitTargetB` smallint(6) NOT NULL,
  `effect2ImplicitTargetB` smallint(6) NOT NULL,
  `effect3ImplicitTargetB` smallint(6) NOT NULL,
  `effect1RadiusMin` smallint(5) unsigned NOT NULL,
  `effect1RadiusMax` smallint(5) unsigned NOT NULL DEFAULT 0,
  `effect2RadiusMin` smallint(5) unsigned NOT NULL,
  `effect2RadiusMax` smallint(5) unsigned NOT NULL DEFAULT 0,
  `effect3RadiusMin` smallint(5) unsigned NOT NULL,
  `effect3RadiusMax` smallint(5) unsigned NOT NULL DEFAULT 0,
  `effect1AuraId` smallint(5) unsigned NOT NULL,
  `effect2AuraId` smallint(5) unsigned NOT NULL,
  `effect3AuraId` smallint(5) unsigned NOT NULL,
  `effect1Periode` mediumint(8) unsigned NOT NULL,
  `effect2Periode` mediumint(8) unsigned NOT NULL,
  `effect3Periode` mediumint(8) unsigned NOT NULL,
  `effect1ValueMultiplier` float NOT NULL,
  `effect2ValueMultiplier` float NOT NULL,
  `effect3ValueMultiplier` float NOT NULL,
  `effect1ChainTarget` smallint(5) unsigned NOT NULL,
  `effect2ChainTarget` smallint(5) unsigned NOT NULL,
  `effect3ChainTarget` smallint(5) unsigned NOT NULL,
  `effect1CreateItemId` int(11) NOT NULL,
  `effect2CreateItemId` int(11) NOT NULL,
  `effect3CreateItemId` int(11) NOT NULL,
  `effect1MiscValue` int(11) NOT NULL,
  `effect2MiscValue` int(11) NOT NULL,
  `effect3MiscValue` int(11) NOT NULL,
  `effect1MiscValueB` mediumint(9) NOT NULL,
  `effect2MiscValueB` mediumint(9) NOT NULL,
  `effect3MiscValueB` mediumint(9) NOT NULL,
  `effect1TriggerSpell` mediumint(9) NOT NULL,
  `effect2TriggerSpell` mediumint(9) NOT NULL,
  `effect3TriggerSpell` mediumint(9) NOT NULL,
  `effect1PointsPerComboPoint` mediumint(9) NOT NULL,
  `effect2PointsPerComboPoint` mediumint(9) NOT NULL,
  `effect3PointsPerComboPoint` mediumint(9) NOT NULL,
  `effect1SpellClassMaskA` int(11) NOT NULL,
  `effect1SpellClassMaskB` int(11) NOT NULL,
  `effect1SpellClassMaskC` int(11) NOT NULL,
  `effect2SpellClassMaskA` int(11) NOT NULL,
  `effect2SpellClassMaskB` int(11) NOT NULL,
  `effect2SpellClassMaskC` int(11) NOT NULL,
  `effect3SpellClassMaskA` int(11) NOT NULL,
  `effect3SpellClassMaskB` int(11) NOT NULL,
  `effect3SpellClassMaskC` int(11) NOT NULL,
  `effect1DamageMultiplier` float NOT NULL,
  `effect2DamageMultiplier` float NOT NULL,
  `effect3DamageMultiplier` float NOT NULL,
  `effect1BonusMultiplier` float NOT NULL,
  `effect2BonusMultiplier` float NOT NULL,
  `effect3BonusMultiplier` float NOT NULL,
  `iconId` smallint(5) unsigned NOT NULL DEFAULT 0,
  `iconIdBak` smallint(5) unsigned NOT NULL DEFAULT 0,
  `iconIdAlt` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rankNo` tinyint(3) unsigned NOT NULL,
  `spellVisualId` smallint(5) unsigned NOT NULL,
  `name_loc0` varchar(115) DEFAULT NULL,
  `name_loc2` varchar(115) DEFAULT NULL,
  `name_loc3` varchar(115) DEFAULT NULL,
  `name_loc4` varchar(115) DEFAULT NULL,
  `name_loc6` varchar(115) DEFAULT NULL,
  `name_loc8` varchar(184) DEFAULT NULL,
  `rank_loc0` varchar(21) DEFAULT NULL,
  `rank_loc2` varchar(25) DEFAULT NULL,
  `rank_loc3` varchar(22) DEFAULT NULL,
  `rank_loc4` varchar(21) DEFAULT NULL,
  `rank_loc6` varchar(29) DEFAULT NULL,
  `rank_loc8` varchar(56) DEFAULT NULL,
  `description_loc0` text DEFAULT NULL,
  `description_loc2` text DEFAULT NULL,
  `description_loc3` text DEFAULT NULL,
  `description_loc4` text DEFAULT NULL,
  `description_loc6` text DEFAULT NULL,
  `description_loc8` text DEFAULT NULL,
  `buff_loc0` text DEFAULT NULL,
  `buff_loc2` text DEFAULT NULL,
  `buff_loc3` text DEFAULT NULL,
  `buff_loc4` text DEFAULT NULL,
  `buff_loc6` text DEFAULT NULL,
  `buff_loc8` text DEFAULT NULL,
  `maxTargetLevel` tinyint(3) unsigned NOT NULL,
  `spellFamilyId` tinyint(3) unsigned NOT NULL,
  `spellFamilyFlags1` int(11) NOT NULL,
  `spellFamilyFlags2` int(11) NOT NULL,
  `spellFamilyFlags3` int(11) NOT NULL,
  `maxAffectedTargets` tinyint(3) unsigned NOT NULL,
  `damageClass` tinyint(3) unsigned NOT NULL,
  `skillLine1` smallint(6) NOT NULL DEFAULT 0,
  `skillLine2OrMask` bigint(20) NOT NULL DEFAULT 0,
  `reqRaceMask` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqClassMask` smallint(5) unsigned NOT NULL DEFAULT 0,
  `reqSpellId` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `reqSkillLevel` smallint(5) unsigned NOT NULL DEFAULT 0,
  `learnedAt` smallint(5) unsigned NOT NULL DEFAULT 0,
  `skillLevelGrey` smallint(5) unsigned NOT NULL DEFAULT 0,
  `skillLevelYellow` smallint(5) unsigned NOT NULL DEFAULT 0,
  `schoolMask` tinyint(3) unsigned NOT NULL,
  `spellDescriptionVariableId` smallint(6) NOT NULL,
  `trainingCost` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`typeCat`),
  KEY `spell` (`id`) USING BTREE,
  KEY `iconId` (`iconId`),
  KEY `reagent1` (`reagent1`),
  KEY `reagent2` (`reagent2`),
  KEY `reagent3` (`reagent3`),
  KEY `reagent4` (`reagent4`),
  KEY `reagent5` (`reagent5`),
  KEY `reagent6` (`reagent6`),
  KEY `reagent7` (`reagent7`),
  KEY `reagent8` (`reagent8`),
  KEY `effect1CreateItemId` (`effect1CreateItemId`),
  KEY `effect2CreateItemId` (`effect2CreateItemId`),
  KEY `effect3CreateItemId` (`effect3CreateItemId`),
  KEY `effect1Id` (`effect1Id`),
  KEY `effect2Id` (`effect2Id`),
  KEY `effect3Id` (`effect3Id`),
  KEY `effect1AuraId` (`effect1AuraId`),
  KEY `effect2AuraId` (`effect2AuraId`),
  KEY `effect3AuraId` (`effect3AuraId`),
  KEY `idx_skill1` (`skillLine1`),
  KEY `idx_skill2` (`skillLine2OrMask`),
  FULLTEXT `idx_ft_name0` (`name_loc0`),
  FULLTEXT `idx_ft_name2` (`name_loc2`),
  FULLTEXT `idx_ft_name3` (`name_loc3`),
  FULLTEXT `idx_ft_name6` (`name_loc6`),
  FULLTEXT `idx_ft_name8` (`name_loc8`),
  KEY `idx_name0` (`name_loc0`),
  KEY `idx_name2` (`name_loc2`),
  KEY `idx_name3` (`name_loc3`),
  KEY `idx_name4` (`name_loc4`),
  KEY `idx_name6` (`name_loc6`),
  KEY `idx_name8` (`name_loc8`),
  KEY `idx_spellfamily` (`spellFamilyId`),
  KEY `idx_miscvalue1` (`effect1MiscValue`),
  KEY `idx_miscvalue2` (`effect2MiscValue`),
  KEY `idx_miscvalue3` (`effect3MiscValue`),
  KEY `idx_triggerspell1` (`effect1TriggerSpell`),
  KEY `idx_triggerspell2` (`effect2TriggerSpell`),
  KEY `idx_triggerspell3` (`effect3TriggerSpell`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spell_sounds`
--

DROP TABLE IF EXISTS `aowow_spell_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spell_sounds` (
  `id` smallint(5) unsigned NOT NULL COMMENT 'SpellVisual.dbc/id',
  `animation` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ready` smallint(5) unsigned NOT NULL DEFAULT 0,
  `precast` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cast` smallint(5) unsigned NOT NULL DEFAULT 0,
  `impact` smallint(5) unsigned NOT NULL DEFAULT 0,
  `state` smallint(5) unsigned NOT NULL DEFAULT 0,
  `statedone` smallint(5) unsigned NOT NULL DEFAULT 0,
  `channel` smallint(5) unsigned NOT NULL DEFAULT 0,
  `casterimpact` smallint(5) unsigned NOT NULL DEFAULT 0,
  `targetimpact` smallint(5) unsigned NOT NULL DEFAULT 0,
  `castertargeting` smallint(5) unsigned NOT NULL DEFAULT 0,
  `missiletargeting` smallint(5) unsigned NOT NULL DEFAULT 0,
  `instantarea` smallint(5) unsigned NOT NULL DEFAULT 0,
  `persistentarea` smallint(5) unsigned NOT NULL DEFAULT 0,
  `casterstate` smallint(5) unsigned NOT NULL DEFAULT 0,
  `targetstate` smallint(5) unsigned NOT NULL DEFAULT 0,
  `missile` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'not predicted by js',
  `impactarea` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'not predicted by js',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='!ATTENTION!\r\nthe primary key of this table is NOT a spellId, but spellVisualId\r\n\r\ncolumn names from LANG.sound_activities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spelldifficulty`
--

DROP TABLE IF EXISTS `aowow_spelldifficulty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spelldifficulty` (
  `normal10` mediumint(8) unsigned NOT NULL,
  `normal25` mediumint(8) unsigned NOT NULL,
  `heroic10` mediumint(8) unsigned NOT NULL,
  `heroic25` mediumint(8) unsigned NOT NULL,
  `mapType` tinyint(3) unsigned NOT NULL,
  KEY `normal10` (`normal10`),
  KEY `normal25` (`normal25`),
  KEY `heroic10` (`heroic10`),
  KEY `heroic25` (`heroic25`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_talents`
--

DROP TABLE IF EXISTS `aowow_talents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_talents` (
  `id` smallint(5) unsigned NOT NULL,
  `class` tinyint(3) unsigned NOT NULL,
  `petTypeMask` tinyint(3) unsigned NOT NULL,
  `tab` tinyint(3) unsigned NOT NULL,
  `row` tinyint(3) unsigned NOT NULL,
  `col` tinyint(3) unsigned NOT NULL,
  `spell` mediumint(8) unsigned NOT NULL,
  `rank` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`,`rank`),
  KEY `spell` (`spell`),
  KEY `class` (`class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_taxinodes`
--

DROP TABLE IF EXISTS `aowow_taxinodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_taxinodes` (
  `id` smallint(5) unsigned NOT NULL,
  `mapId` smallint(5) unsigned NOT NULL,
  `mapX` float unsigned NOT NULL,
  `mapY` float unsigned NOT NULL,
  `areaId` smallint(5) unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `type` enum('NPC','GOBJECT') NOT NULL,
  `typeId` mediumint(8) unsigned NOT NULL,
  `reactA` tinyint(4) NOT NULL,
  `reactH` tinyint(4) NOT NULL,
  `name_loc0` varchar(59) DEFAULT NULL,
  `name_loc2` varchar(84) DEFAULT NULL,
  `name_loc3` varchar(61) DEFAULT NULL,
  `name_loc4` varchar(59) DEFAULT NULL,
  `name_loc6` varchar(89) DEFAULT NULL,
  `name_loc8` varchar(142) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_taxipath`
--

DROP TABLE IF EXISTS `aowow_taxipath`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_taxipath` (
  `id` smallint(5) unsigned NOT NULL,
  `startNodeId` smallint(5) unsigned NOT NULL,
  `endNodeId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_titles`
--

DROP TABLE IF EXISTS `aowow_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_titles` (
  `id` tinyint(3) unsigned NOT NULL,
  `category` tinyint(3) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `gender` tinyint(3) unsigned NOT NULL,
  `side` tinyint(3) unsigned NOT NULL,
  `expansion` tinyint(3) unsigned NOT NULL,
  `src12Ext` mediumint(8) unsigned NOT NULL,
  `eventId` smallint(5) unsigned NOT NULL,
  `bitIdx` tinyint(3) unsigned NOT NULL,
  `male_loc0` varchar(33) DEFAULT NULL,
  `male_loc2` varchar(35) DEFAULT NULL,
  `male_loc3` varchar(37) DEFAULT NULL,
  `male_loc4` varchar(37) DEFAULT NULL,
  `male_loc6` varchar(34) DEFAULT NULL,
  `male_loc8` varchar(37) DEFAULT NULL,
  `female_loc0` varchar(33) DEFAULT NULL,
  `female_loc2` varchar(35) DEFAULT NULL,
  `female_loc3` varchar(39) DEFAULT NULL,
  `female_loc4` varchar(39) DEFAULT NULL,
  `female_loc6` varchar(35) DEFAULT NULL,
  `female_loc8` varchar(41) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bitIdx` (`bitIdx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_user_ratings`
--

DROP TABLE IF EXISTS `aowow_user_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_user_ratings` (
  `type` enum('COMMENT','GUIDE') NOT NULL,
  `entry` int(11) NOT NULL DEFAULT 0,
  `userId` int(10) unsigned DEFAULT NULL,
  `value` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Rating Set',
  UNIQUE KEY `type` (`type`,`entry`,`userId`),
  KEY `FK_acc_co_rate_user` (`userId`),
  CONSTRAINT `FK_userId` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_videos`
--

DROP TABLE IF EXISTS `aowow_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `userIdOwner` int(10) unsigned DEFAULT NULL,
  `date` int(11) NOT NULL,
  `videoId` varchar(12) NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  `url` varchar(64) NOT NULL COMMENT 'preview thumb',
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `userIdApprove` int(10) unsigned DEFAULT NULL,
  `userIdeDelete` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`typeId`),
  KEY `FK_acc_vi` (`userIdOwner`),
  CONSTRAINT `FK_acc_vi` FOREIGN KEY (`userIdOwner`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_zones`
--

DROP TABLE IF EXISTS `aowow_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_zones` (
  `id` smallint(5) unsigned NOT NULL COMMENT 'Zone Id',
  `mapId` smallint(5) unsigned NOT NULL COMMENT 'Map Identifier',
  `mapIdBak` smallint(5) unsigned NOT NULL,
  `parentArea` smallint(5) unsigned NOT NULL,
  `category` smallint(5) unsigned NOT NULL,
  `flags` int(10) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `faction` tinyint(3) unsigned NOT NULL,
  `expansion` tinyint(3) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `maxPlayer` tinyint(4) NOT NULL,
  `itemLevelReqN` smallint(5) unsigned NOT NULL,
  `itemLevelReqH` smallint(5) unsigned NOT NULL,
  `levelReq` tinyint(3) unsigned NOT NULL,
  `levelReqLFG` tinyint(3) unsigned NOT NULL,
  `levelHeroic` tinyint(3) unsigned NOT NULL,
  `levelMin` tinyint(3) unsigned NOT NULL,
  `levelMax` tinyint(3) unsigned NOT NULL,
  `attunementsN` text NOT NULL COMMENT 'space separated; type:typeId',
  `attunementsH` text NOT NULL COMMENT 'space separated; type:typeId',
  `parentMapId` smallint(5) unsigned NOT NULL,
  `parentX` float NOT NULL,
  `parentY` float NOT NULL,
  `name_loc0` varchar(120) DEFAULT NULL COMMENT 'Map Name',
  `name_loc2` varchar(120) DEFAULT NULL,
  `name_loc3` varchar(120) DEFAULT NULL,
  `name_loc4` varchar(120) DEFAULT NULL,
  `name_loc6` varchar(120) DEFAULT NULL,
  `name_loc8` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_zones_sounds`
--

DROP TABLE IF EXISTS `aowow_zones_sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_zones_sounds` (
  `id` smallint(5) unsigned NOT NULL,
  `ambienceDay` smallint(5) unsigned NOT NULL,
  `ambienceNight` smallint(5) unsigned NOT NULL,
  `musicDay` smallint(5) unsigned NOT NULL,
  `musicNight` smallint(5) unsigned NOT NULL,
  `intro` smallint(5) unsigned NOT NULL,
  `worldStateId` smallint(5) unsigned NOT NULL,
  `worldStateValue` smallint(6) NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-22 23:29:16
