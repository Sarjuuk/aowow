-- MySQL dump 10.16  Distrib 10.1.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sarjuuk_aowow
-- ------------------------------------------------------
-- Server version	10.1.8-MariaDB-1~trusty

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
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
  `extId` int(10) unsigned NOT NULL COMMENT 'external user id',
  `user` varchar(64) NOT NULL COMMENT 'login',
  `passHash` varchar(128) NOT NULL,
  `displayName` varchar(64) NOT NULL COMMENT 'nickname',
  `email` varchar(64) NOT NULL,
  `joinDate` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `allowExpire` tinyint(1) unsigned NOT NULL,
  `dailyVotes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `consecutiveVisits` smallint(5) unsigned NOT NULL DEFAULT '0',
  `curIP` varchar(45) NOT NULL,
  `prevIP` varchar(45) NOT NULL,
  `curLogin` int(15) unsigned NOT NULL COMMENT 'unixtime',
  `prevLogin` int(15) unsigned NOT NULL,
  `locale` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0,2,3,6,8',
  `userGroups` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'bitmask',
  `avatar` varchar(50) NOT NULL DEFAULT '' COMMENT 'icon-string for internal or id for upload',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT 'user can obtain custom titles',
  `description` text NOT NULL COMMENT 'markdown formated',
  `userPerms` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'bool isAdmin',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'flag, see defines',
  `statusTimer` int(10) unsigned NOT NULL DEFAULT '0',
  `token` varchar(40) NOT NULL COMMENT 'creation & recovery',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_banned`
--

DROP TABLE IF EXISTS `aowow_account_banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_banned` (
  `id` int(16) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL COMMENT 'affected accountId',
  `staffId` int(10) unsigned NOT NULL COMMENT 'executive accountId',
  `typeMask` tinyint(4) unsigned NOT NULL COMMENT 'ACC_BAN_*',
  `start` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `end` int(10) unsigned NOT NULL COMMENT 'automatic unban @ unixtime',
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_banned` (`userId`),
  CONSTRAINT `FK_acc_banned` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  PRIMARY KEY (`userId`),
  UNIQUE KEY `userId_name` (`userId`,`name`),
  CONSTRAINT `FK_acc_cookies` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
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
  `amount` tinyint(3) unsigned NOT NULL,
  `sourceA` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'e.g. upvoting user',
  `sourceB` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'e.g. upvoted commentId',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `userId_action_source` (`userId`,`action`,`sourceA`,`sourceB`),
  KEY `userId` (`userId`),
  CONSTRAINT `FK_acc_rep` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='reputation log';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_weightscales`
--

DROP TABLE IF EXISTS `aowow_account_weightscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_weightscales` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(48) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`userId`),
  KEY `FK_acc_weights` (`userId`),
  CONSTRAINT `FK_acc_weights` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_account_weightscale_data`
--

DROP TABLE IF EXISTS `aowow_account_weightscale_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_account_weightscale_data` (
  `id` int(32) NOT NULL,
  `field` varchar(15) NOT NULL,
  `val` smallint(6) unsigned NOT NULL,
  KEY `id` (`id`),
  CONSTRAINT `FK_acc_weightscales` FOREIGN KEY (`id`) REFERENCES `aowow_account_weightscales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `chainId` tinyint(3) unsigned NOT NULL,
  `chainPos` tinyint(3) unsigned NOT NULL,
  `category` smallint(6) unsigned NOT NULL,
  `parentCat` smallint(6) NOT NULL,
  `points` tinyint(3) unsigned NOT NULL,
  `orderInGroup` tinyint(3) unsigned NOT NULL,
  `iconId` mediumint(8) unsigned NOT NULL,
  `flags` smallint(5) unsigned NOT NULL,
  `reqCriteriaCount` tinyint(3) unsigned NOT NULL,
  `refAchievement` smallint(5) unsigned NOT NULL,
  `itemExtra` mediumint(8) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL COMMENT 'see defines.php for flags',
  `name_loc0` varchar(78) NOT NULL,
  `name_loc2` varchar(79) NOT NULL,
  `name_loc3` varchar(86) NOT NULL,
  `name_loc6` varchar(78) NOT NULL,
  `name_loc8` varchar(76) NOT NULL,
  `description_loc0` text NOT NULL,
  `description_loc2` text NOT NULL,
  `description_loc3` text NOT NULL,
  `description_loc6` text NOT NULL,
  `description_loc8` text NOT NULL,
  `reward_loc0` varchar(74) NOT NULL,
  `reward_loc2` varchar(88) NOT NULL,
  `reward_loc3` varchar(92) NOT NULL,
  `reward_loc6` varchar(83) NOT NULL,
  `reward_loc8` varchar(95) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_achievementcategory`
--

DROP TABLE IF EXISTS `aowow_achievementcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_achievementcategory` (
  `id` int(16) NOT NULL,
  `parentCategory` mediumint(11) NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  `name_loc2` varchar(255) NOT NULL,
  `name_loc3` varchar(255) NOT NULL,
  `name_loc6` varchar(255) NOT NULL,
  `name_loc8` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_achievement` (`parentCategory`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_achievementcriteria`
--

DROP TABLE IF EXISTS `aowow_achievementcriteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_achievementcriteria` (
  `id` smallint(5) unsigned NOT NULL,
  `refAchievementId` smallint(5) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `value1` int(10) unsigned NOT NULL,
  `value2` int(11) unsigned NOT NULL,
  `value3` int(10) unsigned NOT NULL,
  `value4` int(11) unsigned NOT NULL,
  `value5` int(11) unsigned NOT NULL,
  `value6` int(11) unsigned NOT NULL,
  `name_loc0` varchar(92) NOT NULL,
  `name_loc2` varchar(104) NOT NULL,
  `name_loc3` varchar(128) NOT NULL,
  `name_loc6` varchar(119) NOT NULL,
  `name_loc8` varchar(118) NOT NULL,
  `completionFlags` tinyint(3) unsigned NOT NULL,
  `groupFlags` tinyint(3) unsigned NOT NULL,
  `timeLimit` smallint(5) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_achievement` (`refAchievementId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_announcements`
--

DROP TABLE IF EXISTS `aowow_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_announcements` (
  `id` int(16) NOT NULL AUTO_INCREMENT COMMENT 'iirc negative Ids cant be deleted',
  `page` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `groupMask` smallint(5) unsigned NOT NULL,
  `style` varchar(256) NOT NULL,
  `mode` tinyint(4) unsigned NOT NULL COMMENT '0:pageTop; 1:contentTop',
  `status` tinyint(4) unsigned NOT NULL COMMENT '0:disabled; 1:enabled; 2:deleted',
  `text_loc0` text NOT NULL,
  `text_loc2` text NOT NULL,
  `text_loc3` text NOT NULL,
  `text_loc6` text NOT NULL,
  `text_loc8` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_articles`
--

DROP TABLE IF EXISTS `aowow_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_articles` (
  `type` smallint(5) NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `locale` tinyint(4) NOT NULL,
  `article` text COMMENT 'Markdown formated',
  `quickInfo` text COMMENT 'Markdown formated',
  UNIQUE KEY `type` (`type`,`typeId`,`locale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_characters`
--

DROP TABLE IF EXISTS `aowow_characters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_characters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `race` tinyint(3) unsigned NOT NULL,
  `class` tinyint(3) unsigned NOT NULL,
  `gender` tinyint(3) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `description` varchar(150) NOT NULL,
  `iconString` varchar(50) NOT NULL,
  `titleId` tinyint(3) unsigned NOT NULL,
  `guildId` mediumint(8) unsigned NOT NULL,
  `guildRank` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_classes`
--

DROP TABLE IF EXISTS `aowow_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_classes` (
  `id` int(16) NOT NULL,
  `fileString` varchar(128) NOT NULL,
  `name_loc0` varchar(128) NOT NULL,
  `name_loc2` varchar(128) NOT NULL,
  `name_loc3` varchar(128) NOT NULL,
  `name_loc6` varchar(128) NOT NULL,
  `name_loc8` varchar(128) NOT NULL,
  `powerType` tinyint(4) NOT NULL,
  `raceMask` int(16) NOT NULL,
  `roles` int(16) NOT NULL,
  `skills` varchar(32) NOT NULL,
  `flags` mediumint(16) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `weaponTypeMask` int(32) NOT NULL,
  `armorTypeMask` int(32) NOT NULL,
  `expansion` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_comments`
--

DROP TABLE IF EXISTS `aowow_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Comment ID',
  `type` smallint(5) unsigned NOT NULL COMMENT 'Type of Page',
  `typeId` mediumint(9) NOT NULL COMMENT 'ID Of Page',
  `userId` int(10) unsigned NULL DEFAULT NULL COMMENT 'User ID',
  `roles` smallint(5) unsigned NOT NULL,
  `body` text NOT NULL COMMENT 'Comment text',
  `date` int(11) NOT NULL COMMENT 'Comment timestap',
  `flags` smallint(6) NOT NULL DEFAULT '0' COMMENT 'deleted, outofdate, sticky',
  `replyTo` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Reply To, comment ID',
  `editUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last Edit User ID',
  `editDate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last Edit Time',
  `editCount` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Count Of Edits',
  `deleteUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `deleteDate` int(10) unsigned NOT NULL DEFAULT '0',
  `responseUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `responseBody` text NULL,
  `responseRoles` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `type_typeId` (`type`, `typeId`),
  INDEX `FK_acc_co` (`userId`),
  CONSTRAINT `FK_acc_co` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_comments_rates`
--

DROP TABLE IF EXISTS `aowow_comments_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_comments_rates` (
  `commentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID',
  `value` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Rating Set',
  PRIMARY KEY (`commentId`,`userId`),
  UNIQUE INDEX `commentId_userId` (`commentId`,`userId`),
  INDEX `FK_acc_co_rate_user` (`userId`),
  CONSTRAINT `FK_acc_co_rate` FOREIGN KEY (`commentId`) REFERENCES `aowow_comments` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_acc_co_rate_user` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_config`
--

DROP TABLE IF EXISTS `aowow_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_config` (
  `key` varchar(25) NOT NULL,
  `value` varchar(255) NOT NULL,
  `cat` tinyint(3) unsigned NOT NULL DEFAULT '5',
  `flags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_creature`
--

DROP TABLE IF EXISTS `aowow_creature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_creature` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `cuFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `difficultyEntry1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `difficultyEntry2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `difficultyEntry3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `KillCredit1` int(10) unsigned NOT NULL DEFAULT '0',
  `KillCredit2` int(10) unsigned NOT NULL DEFAULT '0',
  `displayId1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayId2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayId3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayId4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `textureString` varchar(50) DEFAULT NULL,
  `modelId` mediumint(8) NOT NULL,
  `iconString` varchar(50) DEFAULT NULL COMMENT 'first texture of first model for search (up to 11 other skins omitted..)',
  `name_loc0` varchar(100) NOT NULL DEFAULT '0',
  `name_loc2` varchar(100) DEFAULT NULL,
  `name_loc3` varchar(100) DEFAULT NULL,
  `name_loc6` varchar(100) DEFAULT NULL,
  `name_loc8` varchar(100) DEFAULT NULL,
  `subname_loc0` varchar(100) DEFAULT NULL,
  `subname_loc2` varchar(100) DEFAULT NULL,
  `subname_loc3` varchar(100) DEFAULT NULL,
  `subname_loc6` varchar(100) DEFAULT NULL,
  `subname_loc8` varchar(100) DEFAULT NULL,
  `minLevel` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `maxLevel` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `exp` smallint(6) NOT NULL DEFAULT '0',
  `faction` smallint(5) unsigned NOT NULL DEFAULT '0',
  `npcflag` int(10) unsigned NOT NULL DEFAULT '0',
  `rank` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `dmgSchool` tinyint(4) NOT NULL DEFAULT '0',
  `dmgMultiplier` float NOT NULL DEFAULT '1',
  `atkSpeed` int(10) unsigned NOT NULL DEFAULT '0',
  `rngAtkSpeed` int(10) unsigned NOT NULL DEFAULT '0',
  `mleVariance` float NOT NULL DEFAULT '1',
  `rngVariance` float NOT NULL DEFAULT '1',
  `unitClass` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `unitFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `unitFlags2` int(10) unsigned NOT NULL DEFAULT '0',
  `dynamicFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `family` tinyint(4) NOT NULL DEFAULT '0',
  `trainerType` tinyint(4) NOT NULL DEFAULT '0',
  `trainerSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `trainerClass` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `trainerRace` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `dmgMin` float unsigned NOT NULL DEFAULT '0',
  `dmgMax` float unsigned NOT NULL DEFAULT '0',
  `mleAtkPwrMin` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mleAtkPwrMax` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rngAtkPwrMin` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rngAtkPwrMax` smallint(5) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `typeFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `lootId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pickpocketLootId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `skinLootId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell5` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell6` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell7` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `spell8` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `petSpellDataId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `vehicleId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `minGold` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `maxGold` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `aiName` varchar(50) NOT NULL DEFAULT '',
  `healthMin` int(10) unsigned NOT NULL DEFAULT '1',
  `healthMax` int(10) unsigned NOT NULL DEFAULT '1',
  `manaMin` int(10) unsigned NOT NULL DEFAULT '1',
  `manaMax` int(10) unsigned NOT NULL DEFAULT '1',
  `armorMin` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `armorMax` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `racialLeader` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mechanicImmuneMask` int(10) unsigned NOT NULL DEFAULT '0',
  `flagsExtra` int(10) unsigned NOT NULL DEFAULT '0',
  `scriptName` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name_loc0`),
  KEY `difficultyEntry1` (`difficultyEntry1`),
  KEY `difficultyEntry2` (`difficultyEntry2`),
  KEY `difficultyEntry3` (`difficultyEntry3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_creature_waypoints`
--

DROP TABLE IF EXISTS `aowow_creature_waypoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_creature_waypoints` (
  `creatureOrPath` int(11) NOT NULL,
  `point` tinyint(3) unsigned NOT NULL,
  `areaId` smallint(5) unsigned NOT NULL,
  `floor` tinyint(3) unsigned NOT NULL,
  `posX` float unsigned NOT NULL,
  `posY` float unsigned NOT NULL,
  `wait` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`creatureOrPath`,`point`,`areaId`,`floor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_currencies`
--

DROP TABLE IF EXISTS `aowow_currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_currencies` (
  `id` int(16) NOT NULL,
  `category` mediumint(8) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `iconId` mediumint(9) NOT NULL,
  `itemId` int(16) NOT NULL,
  `cap` mediumint(8) unsigned NOT NULL,
  `name_loc0` varchar(64) NOT NULL,
  `name_loc2` varchar(64) NOT NULL,
  `name_loc3` varchar(64) NOT NULL,
  `name_loc6` varchar(64) NOT NULL,
  `name_loc8` varchar(64) NOT NULL,
  `description_loc0` varchar(256) NOT NULL,
  `description_loc2` varchar(256) NOT NULL,
  `description_loc3` varchar(256) NOT NULL,
  `description_loc6` varchar(256) NOT NULL,
  `description_loc8` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_dbversion`
--

DROP TABLE IF EXISTS `aowow_dbversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_dbversion` (
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `part` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sql` text NULL,
  `build` text NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_emotes`
--

DROP TABLE IF EXISTS `aowow_emotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_emotes` (
  `id` smallint(5) unsigned NOT NULL,
  `cmd` varchar(15) NOT NULL,
  `isAnimated` tinyint(1) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `target_loc0` varchar(65) DEFAULT NULL,
  `target_loc2` varchar(70) DEFAULT NULL,
  `target_loc3` varchar(95) DEFAULT NULL,
  `target_loc6` varchar(90) DEFAULT NULL,
  `target_loc8` varchar(70) DEFAULT NULL,
  `noTarget_loc0` varchar(65) DEFAULT NULL,
  `noTarget_loc2` varchar(110) DEFAULT NULL,
  `noTarget_loc3` varchar(85) DEFAULT NULL,
  `noTarget_loc6` varchar(75) DEFAULT NULL,
  `noTarget_loc8` varchar(60) DEFAULT NULL,
  `self_loc0` varchar(65) DEFAULT NULL,
  `self_loc2` varchar(115) DEFAULT NULL,
  `self_loc3` varchar(85) DEFAULT NULL,
  `self_loc6` varchar(75) DEFAULT NULL,
  `self_loc8` varchar(70) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_emotes_aliasses`
--

DROP TABLE IF EXISTS `aowow_emotes_aliasses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_emotes_aliasses` (
  `id` smallint(6) unsigned NOT NULL,
  `locales` smallint(6) unsigned NOT NULL,
  `command` varchar(15) NOT NULL,
  UNIQUE KEY `id_command` (`id`,`command`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_errors`
--

DROP TABLE IF EXISTS `aowow_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_errors` (
  `date` int(10) unsigned DEFAULT NULL,
  `version` smallint(5) unsigned NOT NULL,
  `phpError` smallint(5) unsigned NOT NULL,
  `file` varchar(250) NOT NULL,
  `line` smallint(5) unsigned NOT NULL,
  `query` varchar(250) NOT NULL,
  `userGroups` smallint(5) unsigned NOT NULL,
  `message` text,
  PRIMARY KEY (`file`,`line`,`phpError`,`version`,`userGroups`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_events`
--

DROP TABLE IF EXISTS `aowow_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_events` (
  `id` tinyint(3) unsigned NOT NULL,
  `holidayId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `cuFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `startTime` bigint(20) NOT NULL,
  `endTime` bigint(20) NOT NULL,
  `occurence` bigint(20) unsigned NOT NULL,
  `length` bigint(20) unsigned NOT NULL,
  `requires` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `holidayId` (`holidayId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_factions`
--

DROP TABLE IF EXISTS `aowow_factions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_factions` (
  `id` smallint(5) unsigned NOT NULL,
  `repIdx` smallint(5) unsigned NOT NULL,
  `side` tinyint(1) unsigned NOT NULL,
  `expansion` tinyint(1) unsigned NOT NULL,
  `qmNpcIds` varchar(12) NOT NULL COMMENT 'space separated',
  `templateIds` tinytext NOT NULL COMMENT 'space separated',
  `cuFlags` int(10) unsigned NOT NULL,
  `parentFactionId` smallint(5) unsigned NOT NULL,
  `spilloverRateIn` float(8,2) NOT NULL,
  `spilloverRateOut` float(8,2) NOT NULL,
  `spilloverMaxRank` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(35) NOT NULL,
  `name_loc2` varchar(49) NOT NULL,
  `name_loc3` varchar(40) NOT NULL,
  `name_loc6` varchar(50) NOT NULL,
  `name_loc8` varchar(47) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_glyphproperties`
--

DROP TABLE IF EXISTS `aowow_glyphproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_glyphproperties` (
  `id` smallint(5) unsigned NOT NULL,
  `spellId` mediumint(11) unsigned NOT NULL,
  `typeFlags` tinyint(3) unsigned NOT NULL,
  `iconId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_holidays`
--

DROP TABLE IF EXISTS `aowow_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_holidays` (
  `id` smallint(6) unsigned NOT NULL,
  `bossCreature` mediumint(8) unsigned NOT NULL,
  `achievementCatOrId` mediumint(9) NOT NULL,
  `name_loc0` varchar(36) NOT NULL,
  `name_loc2` varchar(42) NOT NULL,
  `name_loc3` varchar(36) NOT NULL,
  `name_loc6` varchar(49) NOT NULL,
  `name_loc8` varchar(29) NOT NULL,
  `description_loc0` text,
  `description_loc2` text,
  `description_loc3` text,
  `description_loc6` text,
  `description_loc8` text,
  `looping` tinyint(2) NOT NULL,
  `scheduleType` tinyint(2) NOT NULL,
  `textureString` varchar(30) NOT NULL,
  `iconString` varchar(51) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `active` tinyint(1) unsigned NOT NULL,
  `extraWide` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `bgImgUrl` varchar(150) NOT NULL DEFAULT '',
  `text_loc0` text NOT NULL,
  `text_loc2` text NOT NULL,
  `text_loc3` text NOT NULL,
  `text_loc6` text NOT NULL,
  `text_loc8` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_hFBox` (`editorId`),
  CONSTRAINT `FK_acc_hFBox` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `title_loc0` varchar(100) NOT NULL DEFAULT '',
  `title_loc2` varchar(100) NOT NULL DEFAULT '',
  `title_loc3` varchar(100) NOT NULL DEFAULT '',
  `title_loc6` varchar(100) NOT NULL DEFAULT '',
  `title_loc8` varchar(100) NOT NULL DEFAULT '',
  KEY `FK_home_featurebox` (`featureId`),
  CONSTRAINT `FK_home_featurebox` FOREIGN KEY (`featureId`) REFERENCES `aowow_home_featuredbox` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `active` tinyint(1) unsigned NOT NULL,
  `text_loc0` varchar(200) NOT NULL,
  `text_loc2` varchar(200) NOT NULL,
  `text_loc3` varchar(200) NOT NULL,
  `text_loc6` varchar(200) NOT NULL,
  `text_loc8` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_hOneliner` (`editorId`),
  CONSTRAINT `FK_acc_hOneliner` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_home_titles`
--

DROP TABLE IF EXISTS `aowow_home_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_home_titles` (
  `id` smallint(5) unsigned NOT NULL,
  `editorId` int(10) unsigned DEFAULT NULL,
  `editDate` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `title_loc0` varchar(100) NOT NULL,
  `title_loc2` varchar(100) NOT NULL,
  `title_loc3` varchar(100) NOT NULL,
  `title_loc6` varchar(100) NOT NULL,
  `title_loc8` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_acc_hTitles` (`editorId`),
  CONSTRAINT `FK_acc_hTitles` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_icons`
--

DROP TABLE IF EXISTS `aowow_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_icons` (
  `id` mediumint(9) NOT NULL,
  `iconString` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_item_stats`
--

DROP TABLE IF EXISTS `aowow_item_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_item_stats` (
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(8) unsigned NOT NULL,
  `nsockets` tinyint(3) unsigned NOT NULL,
  `dmgmin1` smallint(5) unsigned NOT NULL,
  `dmgmax1` smallint(5) unsigned NOT NULL,
  `speed` float(8,2) NOT NULL,
  `dps` float(8,2) NOT NULL,
  `mledmgmin` smallint(5) unsigned NOT NULL,
  `mledmgmax` smallint(5) unsigned NOT NULL,
  `mlespeed` float(8,2) NOT NULL,
  `mledps` float(8,2) NOT NULL,
  `rgddmgmin` smallint(5) unsigned NOT NULL,
  `rgddmgmax` smallint(5) unsigned NOT NULL,
  `rgdspeed` float(8,2) NOT NULL,
  `rgddps` float(8,2) NOT NULL,
  `dmg` float(8,2) NOT NULL,
  `damagetype` tinyint(4) NOT NULL,
  `mana` smallint(6) NOT NULL,
  `health` smallint(6) NOT NULL,
  `agi` smallint(6) NOT NULL,
  `str` smallint(6) NOT NULL,
  `int` smallint(6) NOT NULL,
  `spi` smallint(6) NOT NULL,
  `sta` smallint(6) NOT NULL,
  `energy` smallint(6) NOT NULL,
  `rage` smallint(6) NOT NULL,
  `focus` smallint(6) NOT NULL,
  `runicpwr` smallint(6) NOT NULL,
  `defrtng` smallint(6) NOT NULL,
  `dodgertng` smallint(6) NOT NULL,
  `parryrtng` smallint(6) NOT NULL,
  `blockrtng` smallint(6) NOT NULL,
  `mlehitrtng` smallint(6) NOT NULL,
  `rgdhitrtng` smallint(6) NOT NULL,
  `splhitrtng` smallint(6) NOT NULL,
  `mlecritstrkrtng` smallint(6) NOT NULL,
  `rgdcritstrkrtng` smallint(6) NOT NULL,
  `splcritstrkrtng` smallint(6) NOT NULL,
  `_mlehitrtng` smallint(6) NOT NULL,
  `_rgdhitrtng` smallint(6) NOT NULL,
  `_splhitrtng` smallint(6) NOT NULL,
  `_mlecritstrkrtng` smallint(6) NOT NULL,
  `_rgdcritstrkrtng` smallint(6) NOT NULL,
  `_splcritstrkrtng` smallint(6) NOT NULL,
  `mlehastertng` smallint(6) NOT NULL,
  `rgdhastertng` smallint(6) NOT NULL,
  `splhastertng` smallint(6) NOT NULL,
  `hitrtng` smallint(6) NOT NULL,
  `critstrkrtng` smallint(6) NOT NULL,
  `_hitrtng` smallint(6) NOT NULL,
  `_critstrkrtng` smallint(6) NOT NULL,
  `resirtng` smallint(6) NOT NULL,
  `hastertng` smallint(6) NOT NULL,
  `exprtng` smallint(6) NOT NULL,
  `atkpwr` smallint(6) NOT NULL,
  `mleatkpwr` smallint(6) NOT NULL,
  `rgdatkpwr` smallint(6) NOT NULL,
  `feratkpwr` smallint(6) NOT NULL,
  `splheal` smallint(6) NOT NULL,
  `spldmg` smallint(6) NOT NULL,
  `manargn` smallint(6) NOT NULL,
  `armorpenrtng` smallint(6) NOT NULL,
  `splpwr` smallint(6) NOT NULL,
  `healthrgn` smallint(6) NOT NULL,
  `splpen` smallint(6) NOT NULL,
  `block` smallint(6) NOT NULL,
  `mastrtng` smallint(6) NOT NULL,
  `armor` smallint(6) NOT NULL,
  `armorbonus` smallint(6) NOT NULL,
  `firres` smallint(6) NOT NULL,
  `frores` smallint(6) NOT NULL,
  `holres` smallint(6) NOT NULL,
  `shares` smallint(6) NOT NULL,
  `natres` smallint(6) NOT NULL,
  `arcres` smallint(6) NOT NULL,
  `firsplpwr` smallint(6) NOT NULL,
  `frosplpwr` smallint(6) NOT NULL,
  `holsplpwr` smallint(6) NOT NULL,
  `shasplpwr` smallint(6) NOT NULL,
  `natsplpwr` smallint(6) NOT NULL,
  `arcsplpwr` smallint(6) NOT NULL,
  PRIMARY KEY (`typeId`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemenchantment`
--

DROP TABLE IF EXISTS `aowow_itemenchantment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemenchantment` (
  `id` smallint(5) unsigned NOT NULL,
  `charges` tinyint(4) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `procChance` tinyint(3) unsigned NOT NULL,
  `ppmRate` float NOT NULL,
  `type1` tinyint(4) unsigned NOT NULL,
  `type2` tinyint(4) unsigned NOT NULL,
  `type3` tinyint(4) unsigned NOT NULL,
  `amount1` smallint(6) NOT NULL,
  `amount2` smallint(6) NOT NULL,
  `amount3` smallint(6) NOT NULL,
  `object1` mediumint(9) unsigned NOT NULL,
  `object2` mediumint(9) unsigned NOT NULL,
  `object3` smallint(6) unsigned NOT NULL,
  `name_loc0` varchar(65) NOT NULL,
  `name_loc2` varchar(91) NOT NULL,
  `name_loc3` varchar(84) NOT NULL,
  `name_loc6` varchar(89) NOT NULL,
  `name_loc8` varchar(96) NOT NULL,
  `conditionId` tinyint(3) unsigned NOT NULL,
  `skillLine` smallint(5) unsigned NOT NULL,
  `skillLevel` smallint(5) unsigned NOT NULL,
  `requiredLevel` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemenchantmentcondition`
--

DROP TABLE IF EXISTS `aowow_itemenchantmentcondition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemenchantmentcondition` (
  `id` smallint(5) unsigned NOT NULL,
  `color1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `color2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `color3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `color4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `color5` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comparator1` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `comparator2` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `comparator3` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `comparator4` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `comparator5` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `cmpColor1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cmpColor2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cmpColor3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cmpColor4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cmpColor5` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `value1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `value2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `value3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `value4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `value5` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemextendedcost`
--

DROP TABLE IF EXISTS `aowow_itemextendedcost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemextendedcost` (
  `id` smallint(5) unsigned NOT NULL,
  `reqHonorPoints` mediumint(8) unsigned NOT NULL,
  `reqArenaPoints` smallint(5) unsigned NOT NULL,
  `reqArenaSlot` tinyint(3) unsigned NOT NULL,
  `reqItemId1` mediumint(8) unsigned NOT NULL,
  `reqItemId2` mediumint(8) unsigned NOT NULL,
  `reqItemId3` mediumint(8) unsigned NOT NULL,
  `reqItemId4` mediumint(8) unsigned NOT NULL,
  `reqItemId5` mediumint(8) unsigned NOT NULL,
  `itemCount1` smallint(5) unsigned NOT NULL,
  `itemCount2` smallint(5) unsigned NOT NULL,
  `itemCount3` smallint(5) unsigned NOT NULL,
  `itemCount4` smallint(5) unsigned NOT NULL,
  `itemCount5` smallint(5) unsigned NOT NULL,
  `reqPersonalRating` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemlimitcategory`
--

DROP TABLE IF EXISTS `aowow_itemlimitcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemlimitcategory` (
  `id` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(31) NOT NULL,
  `name_loc2` varchar(36) NOT NULL,
  `name_loc3` varchar(34) NOT NULL,
  `name_loc6` varchar(40) NOT NULL,
  `name_loc8` varchar(35) NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `isGem` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemrandomenchant`
--

DROP TABLE IF EXISTS `aowow_itemrandomenchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemrandomenchant` (
  `id` smallint(6) NOT NULL,
  `name_loc0` varchar(250) NOT NULL,
  `name_loc2` varchar(250) NOT NULL,
  `name_loc3` varchar(250) NOT NULL,
  `name_loc6` varchar(250) NOT NULL,
  `name_loc8` varchar(250) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemrandomproppoints`
--

DROP TABLE IF EXISTS `aowow_itemrandomproppoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemrandomproppoints` (
  `Id` smallint(5) unsigned NOT NULL,
  `epic1` smallint(5) unsigned NOT NULL,
  `epic2` smallint(5) unsigned NOT NULL,
  `epic3` smallint(5) unsigned NOT NULL,
  `epic4` smallint(5) unsigned NOT NULL,
  `epic5` smallint(5) unsigned NOT NULL,
  `rare1` smallint(5) unsigned NOT NULL,
  `rare2` smallint(5) unsigned NOT NULL,
  `rare3` smallint(5) unsigned NOT NULL,
  `rare4` smallint(5) unsigned NOT NULL,
  `rare5` smallint(5) unsigned NOT NULL,
  `uncommon1` smallint(5) unsigned NOT NULL,
  `uncommon2` smallint(5) unsigned NOT NULL,
  `uncommon3` smallint(5) unsigned NOT NULL,
  `uncommon4` smallint(5) unsigned NOT NULL,
  `uncommon5` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_items`
--

DROP TABLE IF EXISTS `aowow_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_items` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `classBak` tinyint(3) NOT NULL,
  `subClass` tinyint(3) NOT NULL DEFAULT '0',
  `subClassBak` tinyint(3) NOT NULL,
  `subSubClass` tinyint(3) NOT NULL,
  `name_loc0` varchar(127) NOT NULL DEFAULT '',
  `name_loc2` varchar(127) DEFAULT NULL,
  `name_loc3` varchar(127) DEFAULT NULL,
  `name_loc6` varchar(127) DEFAULT NULL,
  `name_loc8` varchar(127) DEFAULT NULL,
  `displayId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `quality` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `flags` bigint(20) NOT NULL DEFAULT '0',
  `flagsExtra` int(10) unsigned NOT NULL DEFAULT '0',
  `buyCount` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `buyPrice` bigint(20) NOT NULL DEFAULT '0',
  `sellPrice` int(10) unsigned NOT NULL DEFAULT '0',
  `repairPrice` int(10) unsigned NOT NULL,
  `slot` tinyint(3) NOT NULL,
  `slotBak` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `requiredClass` int(11) NOT NULL DEFAULT '-1',
  `requiredRace` int(11) NOT NULL DEFAULT '-1',
  `itemLevel` smallint(5) unsigned NOT NULL DEFAULT '0',
  `requiredLevel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `requiredSkill` smallint(5) unsigned NOT NULL DEFAULT '0',
  `requiredSkillRank` smallint(5) unsigned NOT NULL DEFAULT '0',
  `requiredSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `requiredHonorRank` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `requiredCityRank` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `requiredFaction` smallint(5) unsigned NOT NULL DEFAULT '0',
  `requiredFactionRank` smallint(5) unsigned NOT NULL DEFAULT '0',
  `maxCount` int(11) NOT NULL DEFAULT '0',
  `cuFlags` int(10) unsigned NOT NULL,
  `model` varchar(50) NOT NULL,
  `stackable` int(11) DEFAULT '1',
  `slots` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statType1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue1` smallint(6) NOT NULL DEFAULT '0',
  `statType2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue2` smallint(6) NOT NULL DEFAULT '0',
  `statType3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue3` smallint(6) NOT NULL DEFAULT '0',
  `statType4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue4` smallint(6) NOT NULL DEFAULT '0',
  `statType5` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue5` smallint(6) NOT NULL DEFAULT '0',
  `statType6` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue6` smallint(6) NOT NULL DEFAULT '0',
  `statType7` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue7` smallint(6) NOT NULL DEFAULT '0',
  `statType8` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue8` smallint(6) NOT NULL DEFAULT '0',
  `statType9` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue9` smallint(6) NOT NULL DEFAULT '0',
  `statType10` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `statValue10` smallint(6) NOT NULL DEFAULT '0',
  `scalingStatDistribution` smallint(6) NOT NULL DEFAULT '0',
  `scalingStatValue` int(10) unsigned NOT NULL DEFAULT '0',
  `dmgMin1` float NOT NULL DEFAULT '0',
  `dmgMax1` float NOT NULL DEFAULT '0',
  `dmgType1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `dmgMin2` float NOT NULL DEFAULT '0',
  `dmgMax2` float NOT NULL DEFAULT '0',
  `dmgType2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `delay` smallint(5) unsigned NOT NULL DEFAULT '1000',
  `armor` smallint(5) unsigned NOT NULL DEFAULT '0',
  `armorDamageModifier` float NOT NULL DEFAULT '0',
  `block` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `resHoly` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `resFire` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `resNature` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `resFrost` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `resShadow` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `resArcane` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ammoType` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rangedModRange` float NOT NULL DEFAULT '0',
  `spellId1` mediumint(8) NOT NULL DEFAULT '0',
  `spellTrigger1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `spellCharges1` smallint(6) DEFAULT NULL,
  `spellppmRate1` float NOT NULL DEFAULT '0',
  `spellCooldown1` int(11) NOT NULL DEFAULT '-1',
  `spellCategory1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `spellCategoryCooldown1` int(11) NOT NULL DEFAULT '-1',
  `spellId2` mediumint(8) NOT NULL DEFAULT '0',
  `spellTrigger2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `spellCharges2` smallint(6) DEFAULT NULL,
  `spellppmRate2` float NOT NULL DEFAULT '0',
  `spellCooldown2` int(11) NOT NULL DEFAULT '-1',
  `spellCategory2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `spellCategoryCooldown2` int(11) NOT NULL DEFAULT '-1',
  `spellId3` mediumint(8) NOT NULL DEFAULT '0',
  `spellTrigger3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `spellCharges3` smallint(6) DEFAULT NULL,
  `spellppmRate3` float NOT NULL DEFAULT '0',
  `spellCooldown3` int(11) NOT NULL DEFAULT '-1',
  `spellCategory3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `spellCategoryCooldown3` int(11) NOT NULL DEFAULT '-1',
  `spellId4` mediumint(8) NOT NULL DEFAULT '0',
  `spellTrigger4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `spellCharges4` smallint(6) DEFAULT NULL,
  `spellppmRate4` float NOT NULL DEFAULT '0',
  `spellCooldown4` int(11) NOT NULL DEFAULT '-1',
  `spellCategory4` smallint(5) unsigned NOT NULL DEFAULT '0',
  `spellCategoryCooldown4` int(11) NOT NULL DEFAULT '-1',
  `spellId5` mediumint(8) NOT NULL DEFAULT '0',
  `spellTrigger5` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `spellCharges5` smallint(6) DEFAULT NULL,
  `spellppmRate5` float NOT NULL DEFAULT '0',
  `spellCooldown5` int(11) NOT NULL DEFAULT '-1',
  `spellCategory5` smallint(5) unsigned NOT NULL DEFAULT '0',
  `spellCategoryCooldown5` int(11) NOT NULL DEFAULT '-1',
  `bonding` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `description_loc0` varchar(255) NOT NULL DEFAULT '',
  `description_loc2` varchar(255) DEFAULT NULL,
  `description_loc3` varchar(255) DEFAULT NULL,
  `description_loc6` varchar(255) DEFAULT NULL,
  `description_loc8` varchar(255) DEFAULT NULL,
  `pageTextId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `languageId` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `startQuest` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lockId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `randomEnchant` mediumint(8) NOT NULL DEFAULT '0',
  `itemset` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `durability` smallint(5) unsigned NOT NULL DEFAULT '0',
  `area` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `map` smallint(6) NOT NULL DEFAULT '0',
  `bagFamily` mediumint(8) NOT NULL DEFAULT '0',
  `totemCategory` mediumint(8) NOT NULL DEFAULT '0',
  `socketColor1` tinyint(4) NOT NULL DEFAULT '0',
  `socketContent1` mediumint(8) NOT NULL DEFAULT '0',
  `socketColor2` tinyint(4) NOT NULL DEFAULT '0',
  `socketContent2` mediumint(8) NOT NULL DEFAULT '0',
  `socketColor3` tinyint(4) NOT NULL DEFAULT '0',
  `socketContent3` mediumint(8) NOT NULL DEFAULT '0',
  `socketBonus` mediumint(8) NOT NULL DEFAULT '0',
  `gemColorMask` mediumint(8) NOT NULL DEFAULT '0',
  `requiredDisenchantSkill` smallint(6) NOT NULL DEFAULT '-1',
  `disenchantId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `duration` int(10) unsigned NOT NULL DEFAULT '0',
  `itemLimitCategory` smallint(6) NOT NULL DEFAULT '0',
  `eventId` smallint(5) unsigned NOT NULL,
  `scriptName` varchar(64) NOT NULL DEFAULT '',
  `foodType` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `gemEnchantmentId` mediumint(8) NOT NULL,
  `minMoneyLoot` int(10) unsigned NOT NULL DEFAULT '0',
  `maxMoneyLoot` int(10) unsigned NOT NULL DEFAULT '0',
  `flagsCustom` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name_loc0`),
  KEY `items_index` (`class`),
  KEY `idx_model` (`displayId`),
  KEY `idx_faction` (`requiredFaction`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_itemset`
--

DROP TABLE IF EXISTS `aowow_itemset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_itemset` (
  `id` int(16) NOT NULL,
  `refSetId` int(11) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  `name_loc2` varchar(255) NOT NULL,
  `name_loc3` varchar(255) NOT NULL,
  `name_loc6` varchar(255) NOT NULL,
  `name_loc8` varchar(255) NOT NULL,
  `item1` mediumint(11) unsigned NOT NULL,
  `item2` mediumint(11) unsigned NOT NULL,
  `item3` mediumint(11) unsigned NOT NULL,
  `item4` mediumint(11) unsigned NOT NULL,
  `item5` mediumint(11) unsigned NOT NULL,
  `item6` mediumint(11) unsigned NOT NULL,
  `item7` mediumint(11) unsigned NOT NULL,
  `item8` mediumint(11) unsigned NOT NULL,
  `item9` mediumint(11) unsigned NOT NULL,
  `item10` mediumint(11) unsigned NOT NULL,
  `spell1` mediumint(11) unsigned NOT NULL,
  `spell2` mediumint(11) unsigned NOT NULL,
  `spell3` mediumint(11) unsigned NOT NULL,
  `spell4` mediumint(11) unsigned NOT NULL,
  `spell5` mediumint(11) unsigned NOT NULL,
  `spell6` mediumint(11) unsigned NOT NULL,
  `spell7` mediumint(11) unsigned NOT NULL,
  `spell8` mediumint(11) unsigned NOT NULL,
  `bonus1` tinyint(1) unsigned NOT NULL,
  `bonus2` tinyint(1) unsigned NOT NULL,
  `bonus3` tinyint(1) unsigned NOT NULL,
  `bonus4` tinyint(1) unsigned NOT NULL,
  `bonus5` tinyint(1) unsigned NOT NULL,
  `bonus6` tinyint(1) unsigned NOT NULL,
  `bonus7` tinyint(1) unsigned NOT NULL,
  `bonus8` tinyint(1) unsigned NOT NULL,
  `bonusText_loc0` varchar(256) NOT NULL,
  `bonusText_loc2` varchar(256) NOT NULL,
  `bonusText_loc3` varchar(256) NOT NULL,
  `bonusText_loc6` varchar(256) NOT NULL,
  `bonusText_loc8` varchar(256) NOT NULL,
  `bonusParsed` varchar(256) NOT NULL COMMENT 'serialized itemMods',
  `npieces` tinyint(3) NOT NULL,
  `minLevel` smallint(6) NOT NULL,
  `maxLevel` smallint(6) NOT NULL,
  `reqLevel` smallint(6) NOT NULL,
  `classMask` mediumint(9) NOT NULL,
  `heroic` tinyint(1) NOT NULL COMMENT 'bool',
  `quality` tinyint(4) NOT NULL,
  `type` smallint(6) NOT NULL COMMENT 'g_itemset_types',
  `contentGroup` smallint(6) NOT NULL COMMENT 'g_itemset_notes',
  `eventId` smallint(3) unsigned NOT NULL,
  `skillId` smallint(3) unsigned NOT NULL,
  `skillLevel` smallint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_lock`
--

DROP TABLE IF EXISTS `aowow_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_lock` (
  `id` mediumint(11) unsigned NOT NULL,
  `type1` tinyint(1) unsigned NOT NULL,
  `type2` tinyint(1) unsigned NOT NULL,
  `type3` tinyint(1) unsigned NOT NULL,
  `type4` tinyint(1) unsigned NOT NULL,
  `type5` tinyint(1) unsigned NOT NULL,
  `properties1` mediumint(11) unsigned NOT NULL,
  `properties2` mediumint(11) unsigned NOT NULL,
  `properties3` mediumint(11) unsigned NOT NULL,
  `properties4` mediumint(11) unsigned NOT NULL,
  `properties5` mediumint(11) unsigned NOT NULL,
  `reqSkill1` mediumint(11) unsigned NOT NULL,
  `reqSkill2` mediumint(11) unsigned NOT NULL,
  `reqSkill3` mediumint(11) unsigned NOT NULL,
  `reqSkill4` mediumint(11) unsigned NOT NULL,
  `reqSkill5` mediumint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_loot_link`
--

DROP TABLE IF EXISTS `aowow_loot_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_loot_link` (
  `npcId` mediumint(8) NOT NULL,
  `objectId` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `npcId` (`npcId`),
  KEY `objectId` (`objectId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_mailtemplate`
--

DROP TABLE IF EXISTS `aowow_mailtemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_mailtemplate` (
  `id` smallint(5) unsigned NOT NULL,
  `subject_loc0` varchar(128) NOT NULL,
  `subject_loc2` varchar(128) NOT NULL,
  `subject_loc3` varchar(128) NOT NULL,
  `subject_loc6` varchar(128) NOT NULL,
  `subject_loc8` varchar(128) NOT NULL,
  `text_loc0` text NOT NULL,
  `text_loc2` text NOT NULL,
  `text_loc3` text NOT NULL,
  `text_loc6` text NOT NULL,
  `text_loc8` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_objects`
--

DROP TABLE IF EXISTS `aowow_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_objects` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `typeCat` tinyint(3) NOT NULL DEFAULT '0',
  `event` smallint(5) unsigned NOT NULL DEFAULT '0',
  `displayId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name_loc0` varchar(100) DEFAULT NULL,
  `name_loc2` varchar(100) DEFAULT NULL,
  `name_loc3` varchar(100) DEFAULT NULL,
  `name_loc6` varchar(100) DEFAULT NULL,
  `name_loc8` varchar(100) DEFAULT NULL,
  `faction` smallint(5) unsigned NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `cuFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `lootId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lockId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSkill` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pageTextId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `linkedTrap` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqQuest` smallint(5) unsigned NOT NULL DEFAULT '0',
  `spellFocusId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `onUseSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `onSuccessSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `auraSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `triggeredSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `miscInfo` varchar(128) NOT NULL,
  `ScriptOrAI` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name_loc0`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_pet`
--

DROP TABLE IF EXISTS `aowow_pet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_pet` (
  `id` int(11) NOT NULL,
  `category` mediumint(8) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `minLevel` smallint(6) NOT NULL,
  `maxLevel` smallint(6) NOT NULL,
  `foodMask` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `exotic` tinyint(4) NOT NULL,
  `expansion` tinyint(4) NOT NULL,
  `name_loc0` varchar(64) NOT NULL,
  `name_loc2` varchar(64) NOT NULL,
  `name_loc3` varchar(64) NOT NULL,
  `name_loc6` varchar(64) NOT NULL,
  `name_loc8` varchar(64) NOT NULL,
  `iconString` varchar(128) NOT NULL,
  `skillLineId` mediumint(9) NOT NULL,
  `spellId1` mediumint(9) NOT NULL,
  `spellId2` mediumint(9) NOT NULL,
  `spellId3` mediumint(9) NOT NULL,
  `spellId4` mediumint(9) NOT NULL,
  `armor` mediumint(9) NOT NULL,
  `damage` mediumint(9) NOT NULL,
  `health` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_quests`
--

DROP TABLE IF EXISTS `aowow_quests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_quests` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `method` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `level` smallint(3) NOT NULL DEFAULT '1',
  `minLevel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `maxLevel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `zoneOrSort` smallint(6) NOT NULL DEFAULT '0',
  `zoneOrSortBak` smallint(6) NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned NOT NULL DEFAULT '0',
  `suggestedPlayers` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `timeLimit` int(10) unsigned NOT NULL DEFAULT '0',
  `eventId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `prevQuestId` mediumint(8) NOT NULL DEFAULT '0',
  `nextQuestId` mediumint(8) NOT NULL DEFAULT '0',
  `exclusiveGroup` mediumint(8) NOT NULL DEFAULT '0',
  `nextQuestIdChain` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `specialFlags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cuFlags` int(10) unsigned NOT NULL DEFAULT '0',
  `reqClassMask` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqRaceMask` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSkillId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSkillPoints` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqFactionId1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqFactionId2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqFactionValue1` mediumint(8) NOT NULL DEFAULT '0',
  `reqFactionValue2` mediumint(8) NOT NULL DEFAULT '0',
  `reqMinRepFaction` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqMaxRepFaction` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqMinRepValue` mediumint(8) NOT NULL DEFAULT '0',
  `reqMaxRepValue` mediumint(8) NOT NULL DEFAULT '0',
  `reqPlayerKills` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sourceItemId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sourceItemCount` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sourceSpellId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardXP` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardOrReqMoney` int(11) NOT NULL DEFAULT '0',
  `rewardMoneyMaxLevel` int(10) unsigned NOT NULL DEFAULT '0',
  `rewardSpell` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardSpellCast` int(11) NOT NULL DEFAULT '0',
  `rewardHonorPoints` int(11) NOT NULL DEFAULT '0',
  `rewardMailTemplateId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardMailDelay` int(11) unsigned NOT NULL DEFAULT '0',
  `rewardTitleId` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rewardTalents` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rewardArenaPoints` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardItemId1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardItemId2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardItemId3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardItemId4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardItemCount1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardItemCount2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardItemCount3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardItemCount4` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemId1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemId2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemId3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemId4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemId5` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemId6` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemCount1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemCount2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemCount3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemCount4` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemCount5` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardChoiceItemCount6` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rewardFactionId1` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId2` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId3` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId4` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionId5` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'faction id from Faction.dbc in this case',
  `rewardFactionValue1` mediumint(8) NOT NULL DEFAULT '0',
  `rewardFactionValue2` mediumint(8) NOT NULL DEFAULT '0',
  `rewardFactionValue3` mediumint(8) NOT NULL DEFAULT '0',
  `rewardFactionValue4` mediumint(8) NOT NULL DEFAULT '0',
  `rewardFactionValue5` mediumint(8) NOT NULL DEFAULT '0',
  `name_loc0` text,
  `name_loc2` text,
  `name_loc3` text,
  `name_loc6` text,
  `name_loc8` text,
  `objectives_loc0` text,
  `objectives_loc2` text,
  `objectives_loc3` text,
  `objectives_loc6` text,
  `objectives_loc8` text,
  `details_loc0` text,
  `details_loc2` text,
  `details_loc3` text,
  `details_loc6` text,
  `details_loc8` text,
  `end_loc0` text,
  `end_loc2` text,
  `end_loc3` text,
  `end_loc6` text,
  `end_loc8` text,
  `offerReward_loc0` text,
  `offerReward_loc2` text,
  `offerReward_loc3` text,
  `offerReward_loc6` text,
  `offerReward_loc8` text,
  `requestItems_loc0` text,
  `requestItems_loc2` text,
  `requestItems_loc3` text,
  `requestItems_loc6` text,
  `requestItems_loc8` text,
  `completed_loc0` text,
  `completed_loc2` text,
  `completed_loc3` text,
  `completed_loc6` text,
  `completed_loc8` text,
  `reqNpcOrGo1` mediumint(8) NOT NULL DEFAULT '0',
  `reqNpcOrGo2` mediumint(8) NOT NULL DEFAULT '0',
  `reqNpcOrGo3` mediumint(8) NOT NULL DEFAULT '0',
  `reqNpcOrGo4` mediumint(8) NOT NULL DEFAULT '0',
  `reqNpcOrGoCount1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqNpcOrGoCount2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqNpcOrGoCount3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqNpcOrGoCount4` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemId1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemId2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemId3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemId4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemCount1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemCount2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemCount3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSourceItemCount4` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqItemId1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqItemId2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqItemId3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqItemId4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqItemId5` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqItemId6` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqItemCount1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqItemCount2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqItemCount3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqItemCount4` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqItemCount5` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqItemCount6` smallint(5) unsigned NOT NULL DEFAULT '0',
  `objectiveText1_loc0` text,
  `objectiveText1_loc2` text,
  `objectiveText1_loc3` text,
  `objectiveText1_loc6` text,
  `objectiveText1_loc8` text,
  `objectiveText2_loc0` text,
  `objectiveText2_loc2` text,
  `objectiveText2_loc3` text,
  `objectiveText2_loc6` text,
  `objectiveText2_loc8` text,
  `objectiveText3_loc0` text,
  `objectiveText3_loc2` text,
  `objectiveText3_loc3` text,
  `objectiveText3_loc6` text,
  `objectiveText3_loc8` text,
  `objectiveText4_loc0` text,
  `objectiveText4_loc2` text,
  `objectiveText4_loc3` text,
  `objectiveText4_loc6` text,
  `objectiveText4_loc8` text,
  PRIMARY KEY (`id`),
  KEY `nextQuestIdChain` (`nextQuestIdChain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_quests_startend`
--

DROP TABLE IF EXISTS `aowow_quests_startend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_quests_startend` (
  `type` tinyint(4) unsigned NOT NULL,
  `typeId` mediumint(9) unsigned NOT NULL,
  `questId` mediumint(9) unsigned NOT NULL,
  `method` tinyint(4) unsigned NOT NULL COMMENT '&0x1: starts; &0x2:ends',
  `eventId` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`type`,`typeId`,`questId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_races`
--

DROP TABLE IF EXISTS `aowow_races`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_races` (
  `id` int(16) NOT NULL,
  `classMask` bigint(20) NOT NULL,
  `flags` bigint(20) NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `factionId` bigint(20) NOT NULL,
  `startAreaId` bigint(20) NOT NULL,
  `leader` bigint(20) NOT NULL,
  `baseLanguage` bigint(20) NOT NULL,
  `side` int(3) NOT NULL,
  `fileString` varchar(64) NOT NULL,
  `name_loc0` varchar(64) NOT NULL,
  `name_loc2` varchar(64) NOT NULL,
  `name_loc3` varchar(64) NOT NULL,
  `name_loc6` varchar(64) NOT NULL,
  `name_loc8` varchar(64) NOT NULL,
  `expansion` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `assigned` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:new; 1:solved; 2:rejected',
  `mode` tinyint(3) unsigned NOT NULL,
  `reason` tinyint(3) unsigned NOT NULL,
  `subject` mediumint(9) NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `userAgent` varchar(255) NOT NULL,
  `appName` varchar(32) NOT NULL,
  `url` varchar(255) NOT NULL,
  `relatedUrl` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_scalingstatdistribution`
--

DROP TABLE IF EXISTS `aowow_scalingstatdistribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_scalingstatdistribution` (
  `id` smallint(5) unsigned NOT NULL,
  `statMod1` tinyint(4) NOT NULL,
  `statMod2` tinyint(4) NOT NULL,
  `statMod3` tinyint(4) NOT NULL,
  `statMod4` tinyint(4) NOT NULL,
  `statMod5` tinyint(4) NOT NULL,
  `statMod6` tinyint(4) NOT NULL,
  `statMod7` tinyint(4) NOT NULL,
  `statMod8` tinyint(4) NOT NULL,
  `statMod9` tinyint(4) NOT NULL,
  `statMod10` tinyint(4) NOT NULL,
  `modifier1` smallint(5) unsigned NOT NULL,
  `modifier2` smallint(5) unsigned NOT NULL,
  `modifier3` smallint(5) unsigned NOT NULL,
  `modifier4` smallint(5) unsigned NOT NULL,
  `modifier5` smallint(5) unsigned NOT NULL,
  `modifier6` smallint(5) unsigned NOT NULL,
  `modifier7` smallint(5) unsigned NOT NULL,
  `modifier8` smallint(5) unsigned NOT NULL,
  `modifier9` smallint(5) unsigned NOT NULL,
  `modifier10` smallint(5) unsigned NOT NULL,
  `maxLevel` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_scalingstatvalues`
--

DROP TABLE IF EXISTS `aowow_scalingstatvalues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_scalingstatvalues` (
  `id` tinyint(3) unsigned NOT NULL,
  `shoulderMultiplier` tinyint(3) unsigned NOT NULL,
  `trinketMultiplier` tinyint(3) unsigned NOT NULL,
  `weaponMultiplier` tinyint(3) unsigned NOT NULL,
  `rangedMultiplier` tinyint(3) unsigned NOT NULL,
  `clothShoulderArmor` tinyint(3) unsigned NOT NULL,
  `leatherShoulderArmor` smallint(5) unsigned NOT NULL,
  `mailShoulderArmor` smallint(5) unsigned NOT NULL,
  `plateShoulderArmor` smallint(5) unsigned NOT NULL,
  `weaponDPS1H` tinyint(3) unsigned NOT NULL,
  `weaponDPS2H` tinyint(3) unsigned NOT NULL,
  `casterDPS1H` tinyint(3) unsigned NOT NULL,
  `casterDPS2H` tinyint(3) unsigned NOT NULL,
  `rangedDPS` tinyint(3) unsigned NOT NULL,
  `wandDPS` tinyint(3) unsigned NOT NULL,
  `spellPower` smallint(5) unsigned NOT NULL,
  `primBudged` tinyint(3) unsigned NOT NULL,
  `tertBudged` tinyint(3) unsigned NOT NULL,
  `clothCloakArmor` tinyint(3) unsigned NOT NULL,
  `clothChestArmor` smallint(5) unsigned NOT NULL,
  `leatherChestArmor` smallint(5) unsigned NOT NULL,
  `mailChestArmor` smallint(5) unsigned NOT NULL,
  `plateChestArmor` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_screenshots`
--

DROP TABLE IF EXISTS `aowow_screenshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_screenshots` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `userIdOwner` int(10) unsigned DEFAULT NULL,
  `date` int(32) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `caption` varchar(250) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL COMMENT 'see defines.php - CC_FLAG_*',
  `userIdApprove` int(10) unsigned DEFAULT NULL,
  `userIdDelete` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`typeId`),
  KEY `FK_acc_ss` (`userIdOwner`),
  CONSTRAINT `FK_acc_ss` FOREIGN KEY (`userIdOwner`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_shapeshiftforms`
--

DROP TABLE IF EXISTS `aowow_shapeshiftforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_shapeshiftforms` (
  `Id` bigint(20) NOT NULL,
  `name_loc0` text NOT NULL,
  `name_loc2` text NOT NULL,
  `name_loc3` text NOT NULL,
  `name_loc6` text NOT NULL,
  `name_loc8` text NOT NULL,
  `flags` bigint(20) NOT NULL,
  `creatureType` bigint(20) NOT NULL,
  `displayIdA` bigint(20) NOT NULL,
  `displayIdH` bigint(20) NOT NULL,
  `spellId1` bigint(20) NOT NULL,
  `spellId2` bigint(20) NOT NULL,
  `spellId3` bigint(20) NOT NULL,
  `spellId4` bigint(20) NOT NULL,
  `spellId5` bigint(20) NOT NULL,
  `spellId6` bigint(20) NOT NULL,
  `spellId7` bigint(20) NOT NULL,
  `spellId8` bigint(20) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `cuFlags` int(10) unsigned NOT NULL,
  `categoryId` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(64) NOT NULL,
  `name_loc2` varchar(64) NOT NULL,
  `name_loc3` varchar(64) NOT NULL,
  `name_loc6` varchar(64) NOT NULL,
  `name_loc8` varchar(64) NOT NULL,
  `description_loc0` text NOT NULL,
  `description_loc2` text NOT NULL,
  `description_loc3` text NOT NULL,
  `description_loc6` text NOT NULL,
  `description_loc8` text NOT NULL,
  `iconId` smallint(5) unsigned NOT NULL,
  `professionMask` smallint(5) unsigned NOT NULL,
  `recipeSubClass` tinyint(3) unsigned NOT NULL,
  `specializations` varchar(30) NOT NULL COMMENT 'space-separated spellIds',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_source`
--

DROP TABLE IF EXISTS `aowow_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_source` (
  `type` tinyint(4) unsigned NOT NULL,
  `typeId` mediumint(9) unsigned NOT NULL,
  `moreType` tinyint(4) unsigned DEFAULT NULL,
  `moreTypeId` mediumint(9) unsigned DEFAULT NULL,
  `moreZoneId` mediumint(9) unsigned DEFAULT NULL,
  `src1` tinyint(1) unsigned DEFAULT NULL COMMENT 'Crafted',
  `src2` tinyint(3) unsigned DEFAULT NULL COMMENT 'Drop (npc / object / item) (modeMask)',
  `src3` tinyint(3) unsigned DEFAULT NULL COMMENT 'PvP (g_sources_pvp)',
  `src4` tinyint(3) unsigned DEFAULT NULL COMMENT 'Quest (side)',
  `src5` tinyint(1) unsigned DEFAULT NULL COMMENT 'Vendor',
  `src6` tinyint(1) unsigned DEFAULT NULL COMMENT 'Trainer',
  `src7` tinyint(1) unsigned DEFAULT NULL COMMENT 'Discovery',
  `src8` tinyint(1) unsigned DEFAULT NULL COMMENT 'Redemption',
  `src9` tinyint(1) unsigned DEFAULT NULL COMMENT 'Talent',
  `src10` tinyint(1) unsigned DEFAULT NULL COMMENT 'Starter',
  `src11` tinyint(1) unsigned DEFAULT NULL COMMENT 'Event (special; not holidays) [not used]',
  `src12` tinyint(1) unsigned DEFAULT NULL COMMENT 'Achievemement',
  `src13` tinyint(3) unsigned DEFAULT NULL COMMENT 'Misc Source (sourceStringId)',
  `src14` tinyint(1) unsigned DEFAULT NULL COMMENT 'Black Market [not used]',
  `src15` tinyint(1) unsigned DEFAULT NULL COMMENT 'Disenchanted',
  `src16` tinyint(1) unsigned DEFAULT NULL COMMENT 'Fished',
  `src17` tinyint(1) unsigned DEFAULT NULL COMMENT 'Gathered',
  `src18` tinyint(1) unsigned DEFAULT NULL COMMENT 'Milled',
  `src19` tinyint(1) unsigned DEFAULT NULL COMMENT 'Mined',
  `src20` tinyint(1) unsigned DEFAULT NULL COMMENT 'Prospected',
  `src21` tinyint(1) unsigned DEFAULT NULL COMMENT 'Pickpocketed',
  `src22` tinyint(1) unsigned DEFAULT NULL COMMENT 'Salvaged',
  `src23` tinyint(1) unsigned DEFAULT NULL COMMENT 'Skinned',
  `src24` tinyint(1) unsigned DEFAULT NULL COMMENT 'In-Game Store [not used]',
  PRIMARY KEY (`type`,`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_sourcestrings`
--

DROP TABLE IF EXISTS `aowow_sourcestrings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_sourcestrings` (
  `id` int(16) NOT NULL,
  `source_loc0` varchar(128) NOT NULL,
  `source_loc2` varchar(128) NOT NULL,
  `source_loc3` varchar(128) NOT NULL,
  `source_loc6` varchar(128) NOT NULL,
  `source_loc8` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spawns`
--

DROP TABLE IF EXISTS `aowow_spawns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spawns` (
  `guid` int(11) NOT NULL COMMENT '< 0: vehicle accessory',
  `type` tinyint(3) unsigned NOT NULL,
  `typeId` int(10) unsigned NOT NULL,
  `respawn` int(10) unsigned NOT NULL COMMENT 'in seconds',
  `spawnMask` tinyint(3) unsigned NOT NULL,
  `phaseMask` smallint(5) unsigned NOT NULL,
  `areaId` smallint(5) unsigned NOT NULL,
  `floor` tinyint(3) unsigned NOT NULL,
  `posX` float unsigned NOT NULL,
  `posY` float unsigned NOT NULL,
  `pathId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`guid`,`type`,`floor`),
  KEY `type_idx` (`typeId`,`type`),
  KEY `zone_idx` (`areaId`),
  KEY `guid` (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `cuFlags` int(10) unsigned NOT NULL,
  `typeCat` smallint(6) NOT NULL,
  `stanceMask` int(10) unsigned NOT NULL,
  `stanceMaskNot` int(10) unsigned NOT NULL,
  `spellFocusObject` smallint(5) unsigned NOT NULL,
  `castTime` mediumint(8) unsigned NOT NULL,
  `recoveryTime` int(10) unsigned NOT NULL,
  `recoveryCategory` int(10) unsigned NOT NULL,
  `startRecoveryTime` mediumint(8) unsigned NOT NULL,
  `startRecoveryCategory` smallint(5) unsigned NOT NULL,
  `procChance` tinyint(3) unsigned NOT NULL,
  `procCharges` tinyint(3) unsigned NOT NULL,
  `procCustom` float NOT NULL,
  `procCooldown` smallint(6) unsigned NOT NULL,
  `maxLevel` tinyint(3) unsigned NOT NULL,
  `baseLevel` tinyint(3) unsigned NOT NULL,
  `spellLevel` tinyint(3) unsigned NOT NULL,
  `talentLevel` tinyint(3) unsigned NOT NULL,
  `duration` int(16) NOT NULL DEFAULT '0',
  `powerType` tinyint(4) NOT NULL,
  `powerCost` smallint(5) unsigned NOT NULL,
  `powerCostPerLevel` tinyint(3) unsigned NOT NULL,
  `powerCostPercent` tinyint(3) unsigned NOT NULL,
  `powerPerSecond` smallint(5) unsigned NOT NULL,
  `powerPerSecondPerLevel` tinyint(3) unsigned NOT NULL,
  `powerGainRunicPower` smallint(5) unsigned NOT NULL,
  `powerCostRunes` smallint(5) unsigned NOT NULL,
  `rangeId` smallint(5) unsigned NOT NULL,
  `stackAmount` smallint(5) unsigned NOT NULL,
  `tool1` mediumint(8) unsigned NOT NULL,
  `tool2` mediumint(8) unsigned NOT NULL,
  `toolCategory1` tinyint(3) unsigned NOT NULL,
  `toolCategory2` tinyint(3) unsigned NOT NULL,
  `reagent1` mediumint(8) unsigned NOT NULL,
  `reagent2` mediumint(8) unsigned NOT NULL,
  `reagent3` mediumint(8) unsigned NOT NULL,
  `reagent4` mediumint(8) unsigned NOT NULL,
  `reagent5` mediumint(8) unsigned NOT NULL,
  `reagent6` mediumint(8) unsigned NOT NULL,
  `reagent7` mediumint(8) unsigned NOT NULL,
  `reagent8` mediumint(8) unsigned NOT NULL,
  `reagentCount1` tinyint(3) unsigned NOT NULL,
  `reagentCount2` tinyint(3) unsigned NOT NULL,
  `reagentCount3` tinyint(3) unsigned NOT NULL,
  `reagentCount4` tinyint(3) unsigned NOT NULL,
  `reagentCount5` tinyint(3) unsigned NOT NULL,
  `reagentCount6` tinyint(3) unsigned NOT NULL,
  `reagentCount7` tinyint(3) unsigned NOT NULL,
  `reagentCount8` tinyint(3) unsigned NOT NULL,
  `equippedItemClass` tinyint(4) NOT NULL,
  `equippedItemSubClassMask` int(11) NOT NULL,
  `equippedItemInventoryTypeMask` int(10) unsigned NOT NULL,
  `effect1Id` smallint(5) unsigned NOT NULL,
  `effect2Id` smallint(5) unsigned NOT NULL,
  `effect3Id` smallint(5) unsigned NOT NULL,
  `effect1DieSides` mediumint(9) NOT NULL,
  `effect2DieSides` mediumint(9) NOT NULL,
  `effect3DieSides` mediumint(9) NOT NULL,
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
  `effect1RadiusMax` smallint(5) unsigned NOT NULL DEFAULT '0',
  `effect2RadiusMin` smallint(5) unsigned NOT NULL,
  `effect2RadiusMax` smallint(5) unsigned NOT NULL DEFAULT '0',
  `effect3RadiusMin` smallint(5) unsigned NOT NULL,
  `effect3RadiusMax` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  `effect1CreateItemId` mediumint(8) unsigned NOT NULL,
  `effect2CreateItemId` mediumint(8) unsigned NOT NULL,
  `effect3CreateItemId` mediumint(8) unsigned NOT NULL,
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
  `effect1SpellClassMaskA` int(10) unsigned NOT NULL,
  `effect2SpellClassMaskA` int(10) unsigned NOT NULL,
  `effect3SpellClassMaskA` int(10) unsigned NOT NULL,
  `effect1SpellClassMaskB` int(10) unsigned NOT NULL,
  `effect2SpellClassMaskB` int(10) unsigned NOT NULL,
  `effect3SpellClassMaskB` int(10) unsigned NOT NULL,
  `effect1SpellClassMaskC` int(10) unsigned NOT NULL,
  `effect2SpellClassMaskC` int(10) unsigned NOT NULL,
  `effect3SpellClassMaskC` int(10) unsigned NOT NULL,
  `effect1DamageMultiplier` float NOT NULL,
  `effect2DamageMultiplier` float NOT NULL,
  `effect3DamageMultiplier` float NOT NULL,
  `effect1BonusMultiplier` float NOT NULL,
  `effect2BonusMultiplier` float NOT NULL,
  `effect3BonusMultiplier` float NOT NULL,
  `iconId` smallint(5) unsigned NOT NULL,
  `iconIdAlt` mediumint(9) NOT NULL,
  `rankNo` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(85) NOT NULL,
  `name_loc2` varchar(85) NOT NULL,
  `name_loc3` varchar(85) NOT NULL,
  `name_loc6` varchar(91) NOT NULL,
  `name_loc8` varchar(50) NOT NULL,
  `rank_loc0` varchar(21) NOT NULL,
  `rank_loc2` varchar(24) NOT NULL,
  `rank_loc3` varchar(22) NOT NULL,
  `rank_loc6` varchar(27) NOT NULL,
  `rank_loc8` varchar(29) NOT NULL,
  `description_loc0` text NOT NULL,
  `description_loc2` text NOT NULL,
  `description_loc3` text NOT NULL,
  `description_loc6` text NOT NULL,
  `description_loc8` text NOT NULL,
  `buff_loc0` text NOT NULL,
  `buff_loc2` text NOT NULL,
  `buff_loc3` text NOT NULL,
  `buff_loc6` text NOT NULL,
  `buff_loc8` text NOT NULL,
  `maxTargetLevel` tinyint(3) unsigned NOT NULL,
  `spellFamilyId` tinyint(3) unsigned NOT NULL,
  `spellFamilyFlags1` int(10) unsigned NOT NULL,
  `spellFamilyFlags2` int(10) unsigned NOT NULL,
  `spellFamilyFlags3` int(10) unsigned NOT NULL,
  `maxAffectedTargets` tinyint(3) unsigned NOT NULL,
  `damageClass` tinyint(3) unsigned NOT NULL,
  `skillLine1` smallint(6) NOT NULL DEFAULT '0',
  `skillLine2OrMask` bigint(32) NOT NULL DEFAULT '0',
  `reqRaceMask` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqClassMask` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reqSpellId` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reqSkillLevel` smallint(5) unsigned NOT NULL DEFAULT '0',
  `learnedAt` smallint(6) unsigned NOT NULL DEFAULT '0',
  `skillLevelGrey` smallint(6) unsigned NOT NULL DEFAULT '0',
  `skillLevelYellow` smallint(6) unsigned NOT NULL DEFAULT '0',
  `schoolMask` tinyint(3) unsigned NOT NULL,
  `spellDescriptionVariableId` tinyint(3) unsigned NOT NULL,
  `trainingCost` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`typeCat`),
  KEY `spell` (`id`) USING BTREE,
  KEY `effects` (`effect1Id`,`effect2Id`,`effect3Id`),
  KEY `items` (`effect1CreateItemId`,`effect2CreateItemId`,`effect3CreateItemId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  KEY `normal10` (`normal10`),
  KEY `normal25` (`normal25`),
  KEY `heroic10` (`heroic10`),
  KEY `heroic25` (`heroic25`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spellfocusobject`
--

DROP TABLE IF EXISTS `aowow_spellfocusobject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spellfocusobject` (
  `Id` smallint(5) unsigned NOT NULL,
  `name_loc0` varchar(83) NOT NULL,
  `name_loc2` varchar(89) NOT NULL,
  `name_loc3` varchar(95) NOT NULL,
  `name_loc6` varchar(90) NOT NULL,
  `name_loc8` varchar(91) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Id` (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='merge into gameobject..?';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spellrange`
--

DROP TABLE IF EXISTS `aowow_spellrange`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spellrange` (
  `id` tinyint(3) unsigned NOT NULL,
  `rangeMinHostile` tinyint(3) unsigned NOT NULL,
  `rangeMinFriend` tinyint(3) unsigned NOT NULL,
  `rangeMaxHostile` smallint(5) unsigned NOT NULL,
  `rangeMaxFriend` smallint(5) unsigned NOT NULL,
  `rangeType` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(27) NOT NULL,
  `name_loc2` varchar(27) NOT NULL,
  `name_loc3` varchar(27) NOT NULL,
  `name_loc6` varchar(27) NOT NULL,
  `name_loc8` varchar(27) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_spellvariables`
--

DROP TABLE IF EXISTS `aowow_spellvariables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_spellvariables` (
  `id` tinyint(3) unsigned NOT NULL,
  `vars` varchar(368) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `tab` tinyint(3) unsigned NOT NULL,
  `row` tinyint(3) unsigned NOT NULL,
  `col` tinyint(3) unsigned NOT NULL,
  `spell` mediumint(8) unsigned NOT NULL,
  `rank` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`,`rank`),
  KEY `spell` (`spell`),
  KEY `class` (`class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_taxinodes`
--

DROP TABLE IF EXISTS `aowow_taxinodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_taxinodes` (
  `id` smallint(5) unsigned NOT NULL,
  `mapId` smallint(6) unsigned NOT NULL,
  `posX` float unsigned NOT NULL,
  `posY` float unsigned NOT NULL,
  `type` tinyint(4) unsigned NOT NULL COMMENT 'usually NPC (1) but could support GOs (2)',
  `typeId` mediumint(9) unsigned NOT NULL,
  `reactA` tinyint(4) NOT NULL,
  `reactH` tinyint(4) NOT NULL,
  `name_loc0` varchar(46) NOT NULL,
  `name_loc2` varchar(62) NOT NULL,
  `name_loc3` varchar(55) NOT NULL,
  `name_loc6` varchar(63) NOT NULL,
  `name_loc8` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_taxipath`
--

DROP TABLE IF EXISTS `aowow_taxipath`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_taxipath` (
  `id` smallint(5) unsigned NOT NULL,
  `startNodeId` smallint(6) unsigned NOT NULL,
  `endNodeId` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  `cuFlags` int(10) unsigned NOT NULL,
  `gender` tinyint(3) unsigned NOT NULL,
  `side` tinyint(3) unsigned NOT NULL,
  `expansion` tinyint(3) unsigned NOT NULL,
  `src12Ext` mediumint(9) unsigned NOT NULL,
  `eventId` smallint(5) unsigned NOT NULL,
  `bitIdx` tinyint(3) unsigned NOT NULL,
  `male_loc0` varchar(33) NOT NULL,
  `male_loc2` varchar(35) NOT NULL,
  `male_loc3` varchar(37) NOT NULL,
  `male_loc6` varchar(34) NOT NULL,
  `male_loc8` varchar(37) NOT NULL,
  `female_loc0` varchar(33) NOT NULL,
  `female_loc2` varchar(35) NOT NULL,
  `female_loc3` varchar(39) NOT NULL,
  `female_loc6` varchar(35) NOT NULL,
  `female_loc8` varchar(41) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bitIdx` (`bitIdx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_totemcategory`
--

DROP TABLE IF EXISTS `aowow_totemcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_totemcategory` (
  `id` tinyint(3) unsigned NOT NULL,
  `name_loc0` varchar(29) NOT NULL,
  `name_loc2` varchar(45) NOT NULL,
  `name_loc3` varchar(31) NOT NULL,
  `name_loc6` varchar(36) NOT NULL,
  `name_loc8` varchar(69) NOT NULL,
  `category` tinyint(3) unsigned NOT NULL,
  `categoryMask` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aowow_videos`
--

DROP TABLE IF EXISTS `aowow_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_videos` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type` smallint(5) unsigned NOT NULL,
  `typeId` mediumint(9) NOT NULL,
  `userIdOwner` int(10) unsigned DEFAULT NULL,
  `date` int(32) NOT NULL,
  `videoId` varchar(12) NOT NULL,
  `caption` text,
  `status` int(8) NOT NULL,
  `userIdApprove` int(10) unsigned DEFAULT NULL,
  `userIdeDelete` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`typeId`),
  KEY `FK_acc_vi` (`userIdOwner`),
  CONSTRAINT `FK_acc_vi` FOREIGN KEY (`userIdOwner`) REFERENCES `aowow_account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
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
  `parentArea` smallint(6) unsigned NOT NULL,
  `category` tinyint(4) unsigned NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `cuFlags` int(10) unsigned NOT NULL,
  `faction` tinyint(2) unsigned NOT NULL,
  `expansion` tinyint(2) unsigned NOT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  `maxPlayer` tinyint(4) NOT NULL,
  `itemLevelReqN` smallint(5) unsigned NOT NULL,
  `itemLevelReqH` smallint(5) unsigned NOT NULL,
  `levelReq` tinyint(3) unsigned NOT NULL,
  `levelReqLFG` tinyint(4) unsigned NOT NULL,
  `levelHeroic` tinyint(3) unsigned NOT NULL,
  `levelMin` tinyint(4) unsigned NOT NULL,
  `levelMax` tinyint(4) unsigned NOT NULL,
  `attunementsN` text NOT NULL COMMENT 'space separated; type:typeId',
  `attunementsH` text NOT NULL COMMENT 'space separated; type:typeId',
  `parentAreaId` smallint(5) unsigned NOT NULL,
  `parentX` float NOT NULL,
  `parentY` float NOT NULL,
  `name_loc0` varchar(120) NOT NULL COMMENT 'Map Name',
  `name_loc2` varchar(120) NOT NULL,
  `name_loc3` varchar(120) NOT NULL,
  `name_loc6` varchar(120) NOT NULL,
  `name_loc8` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-10-31 13:22:27
-- MySQL dump 10.16  Distrib 10.1.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sarjuuk_aowow
-- ------------------------------------------------------
-- Server version	10.1.8-MariaDB-1~trusty

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `aowow_account`
--

LOCK TABLES `aowow_account` WRITE;
/*!40000 ALTER TABLE `aowow_account` DISABLE KEYS */;
INSERT INTO `aowow_account` VALUES (0,0,'<system>','','AoWoW','',0,0,0,0,'','',0,0,0,0,'','','',0,0,0,'');
/*!40000 ALTER TABLE `aowow_account` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Dumping data for table `aowow_account_weightscales`
--

LOCK TABLES `aowow_account_weightscales` WRITE;
/*!40000 ALTER TABLE `aowow_account_weightscales` DISABLE KEYS */;
INSERT INTO `aowow_account_weightscales` VALUES (1,0,'arms',1,'ability_rogue_eviscerate'),(2,0,'fury',1,'ability_warrior_innerrage'),(3,0,'prot',1,'ability_warrior_defensivestance'),(4,0,'holy',2,'spell_holy_holybolt'),(5,0,'prot',2,'ability_paladin_shieldofthetempl'),(6,0,'retrib',2,'spell_holy_auraoflight'),(7,0,'beast',3,'ability_hunter_beasttaming'),(8,0,'marks',3,'ability_marksmanship'),(9,0,'surv',3,'ability_hunter_swiftstrike'),(10,0,'assas',4,'ability_rogue_eviscerate'),(11,0,'combat',4,'ability_backstab'),(12,0,'subtle',4,'ability_stealth'),(13,0,'disc',5,'spell_holy_wordfortitude'),(14,0,'holy',5,'spell_holy_guardianspirit'),(15,0,'shadow',5,'spell_shadow_shadowwordpain'),(16,0,'blooddps',6,'spell_deathknight_bloodpresence'),(17,0,'frostdps',6,'spell_deathknight_frostpresence'),(18,0,'frosttank',6,'spell_deathknight_frostpresence'),(19,0,'unholydps',6,'spell_deathknight_unholypresence'),(20,0,'elem',7,'spell_nature_lightning'),(21,0,'enhance',7,'spell_nature_lightningshield'),(22,0,'resto',7,'spell_nature_magicimmunity'),(23,0,'arcane',8,'spell_holy_magicalsentry'),(24,0,'fire',8,'spell_fire_firebolt02'),(25,0,'frost',8,'spell_frost_frostbolt02'),(26,0,'afflic',9,'spell_shadow_deathcoil'),(27,0,'demo',9,'spell_shadow_metamorphosis'),(28,0,'destro',9,'spell_shadow_rainoffire'),(29,0,'balance',11,'spell_nature_starfall'),(30,0,'feraltank',11,'ability_racial_bearform'),(31,0,'resto',11,'spell_nature_healingtouch'),(32,0,'feraldps',11,'ability_druid_catform');
/*!40000 ALTER TABLE `aowow_account_weightscales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_account_weightscale_data`
--

LOCK TABLES `aowow_account_weightscale_data` WRITE;
/*!40000 ALTER TABLE `aowow_account_weightscale_data` DISABLE KEYS */;
INSERT INTO `aowow_account_weightscale_data` VALUES (2,'exprtng',100),(2,'str',82),(2,'critstrkrtng',66),(2,'agi',53),(2,'armorpenrtng',52),(2,'hitrtng',48),(2,'hastertng',36),(2,'atkpwr',31),(2,'armor',5),(3,'sta',100),(3,'dodgertng',90),(3,'defrtng',86),(3,'block',81),(3,'agi',67),(3,'parryrtng',67),(3,'blockrtng',48),(3,'str',48),(3,'exprtng',19),(3,'hitrtng',10),(3,'armorpenrtng',10),(3,'critstrkrtng',7),(3,'armor',6),(3,'hastertng',1),(3,'atkpwr',1),(4,'int',100),(4,'manargn',88),(4,'splpwr',58),(4,'critstrkrtng',46),(4,'hastertng',35),(5,'sta',100),(5,'dodgertng',94),(5,'block',86),(5,'defrtng',86),(5,'exprtng',79),(5,'agi',76),(5,'parryrtng',76),(5,'hitrtng',58),(5,'blockrtng',52),(5,'str',50),(5,'armor',6),(5,'atkpwr',6),(5,'splpwr',4),(5,'critstrkrtng',3),(6,'mledps',470),(6,'hitrtng',100),(6,'str',80),(6,'exprtng',66),(6,'critstrkrtng',40),(6,'atkpwr',34),(6,'agi',32),(6,'hastertng',30),(6,'armorpenrtng',22),(6,'splpwr',9),(7,'rgddps',213),(7,'hitrtng',100),(7,'agi',58),(7,'critstrkrtng',40),(7,'int',37),(7,'atkpwr',30),(7,'armorpenrtng',28),(7,'hastertng',21),(8,'rgddps',379),(8,'hitrtng',100),(8,'agi',74),(8,'critstrkrtng',57),(8,'armorpenrtng',40),(8,'int',39),(8,'atkpwr',32),(8,'hastertng',24),(9,'rgddps',181),(9,'hitrtng',100),(9,'agi',76),(9,'critstrkrtng',42),(9,'int',35),(9,'hastertng',31),(9,'atkpwr',29),(9,'armorpenrtng',26),(10,'mledps',170),(10,'agi',100),(10,'exprtng',87),(10,'hitrtng',83),(10,'critstrkrtng',81),(10,'atkpwr',65),(10,'armorpenrtng',65),(10,'hastertng',64),(10,'str',55),(11,'mledps',220),(11,'armorpenrtng',100),(11,'agi',100),(11,'exprtng',82),(11,'hitrtng',80),(11,'critstrkrtng',75),(11,'hastertng',73),(11,'str',55),(11,'atkpwr',50),(12,'mledps',228),(12,'exprtng',100),(12,'agi',100),(12,'hitrtng',80),(12,'armorpenrtng',75),(12,'critstrkrtng',75),(12,'hastertng',75),(12,'str',55),(12,'atkpwr',50),(13,'splpwr',100),(13,'manargn',67),(13,'int',65),(13,'hastertng',59),(13,'critstrkrtng',48),(13,'spi',22),(14,'manargn',100),(14,'int',69),(14,'splpwr',60),(14,'spi',52),(14,'critstrkrtng',38),(14,'hastertng',31),(15,'hitrtng',100),(15,'shasplpwr',76),(15,'splpwr',76),(15,'critstrkrtng',54),(15,'hastertng',50),(15,'spi',16),(15,'int',16),(16,'mledps',360),(16,'armorpenrtng',100),(16,'str',99),(16,'hitrtng',91),(16,'exprtng',90),(16,'critstrkrtng',57),(16,'hastertng',55),(16,'atkpwr',36),(16,'armor',1),(17,'mledps',337),(17,'hitrtng',100),(17,'str',97),(17,'exprtng',81),(17,'armorpenrtng',61),(17,'critstrkrtng',45),(17,'atkpwr',35),(17,'hastertng',28),(17,'armor',1),(18,'mledps',419),(18,'parryrtng',100),(18,'hitrtng',97),(18,'str',96),(18,'defrtng',85),(18,'exprtng',69),(18,'dodgertng',61),(18,'agi',61),(18,'sta',61),(18,'critstrkrtng',49),(18,'atkpwr',41),(18,'armorpenrtng',31),(18,'armor',5),(19,'mledps',209),(19,'str',100),(19,'hitrtng',66),(19,'exprtng',51),(19,'hastertng',48),(19,'critstrkrtng',45),(19,'atkpwr',34),(19,'armorpenrtng',32),(19,'armor',1),(20,'hitrtng',100),(20,'splpwr',60),(20,'hastertng',56),(20,'critstrkrtng',40),(20,'int',11),(21,'mledps',135),(21,'hitrtng',100),(21,'exprtng',84),(21,'agi',55),(21,'int',55),(21,'critstrkrtng',55),(21,'hastertng',42),(21,'str',35),(21,'atkpwr',32),(21,'splpwr',29),(21,'armorpenrtng',26),(22,'manargn',100),(22,'int',85),(22,'splpwr',77),(22,'critstrkrtng',62),(22,'hastertng',35),(23,'hitrtng',100),(23,'hastertng',54),(23,'arcsplpwr',49),(23,'splpwr',49),(23,'critstrkrtng',37),(23,'int',34),(23,'frosplpwr',24),(23,'firsplpwr',24),(23,'spi',14),(24,'hitrtng',100),(24,'hastertng',53),(24,'firsplpwr',46),(24,'splpwr',46),(24,'critstrkrtng',43),(24,'frosplpwr',23),(24,'arcsplpwr',23),(24,'int',13),(25,'hitrtng',100),(25,'hastertng',42),(25,'frosplpwr',39),(25,'splpwr',39),(25,'arcsplpwr',19),(25,'firsplpwr',19),(25,'critstrkrtng',19),(25,'int',6),(26,'hitrtng',100),(26,'shasplpwr',72),(26,'splpwr',72),(26,'hastertng',61),(26,'critstrkrtng',38),(26,'firsplpwr',36),(26,'spi',34),(26,'int',15),(27,'hitrtng',100),(27,'hastertng',50),(27,'firsplpwr',45),(27,'shasplpwr',45),(27,'splpwr',45),(27,'critstrkrtng',31),(27,'spi',29),(27,'int',13),(28,'hitrtng',100),(28,'firsplpwr',47),(28,'splpwr',47),(28,'hastertng',46),(28,'spi',26),(28,'shasplpwr',23),(28,'critstrkrtng',16),(28,'int',13),(29,'hitrtng',100),(29,'splpwr',66),(29,'hastertng',54),(29,'critstrkrtng',43),(29,'spi',22),(29,'int',22),(30,'agi',100),(30,'sta',75),(30,'dodgertng',65),(30,'defrtng',60),(30,'exprtng',16),(30,'str',10),(30,'armor',10),(30,'hitrtng',8),(30,'hastertng',5),(30,'atkpwr',4),(30,'feratkpwr',4),(30,'critstrkrtng',3),(31,'splpwr',100),(31,'manargn',73),(31,'hastertng',57),(31,'int',51),(31,'spi',32),(31,'critstrkrtng',11),(32,'agi',100),(32,'armorpenrtng',90),(32,'str',80),(32,'critstrkrtng',55),(32,'exprtng',50),(32,'hitrtng',50),(32,'feratkpwr',40),(32,'atkpwr',40),(32,'hastertng',35);
/*!40000 ALTER TABLE `aowow_account_weightscale_data` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Dumping data for table `aowow_announcements`
--

LOCK TABLES `aowow_announcements` WRITE;
/*!40000 ALTER TABLE `aowow_announcements` DISABLE KEYS */;
INSERT INTO `aowow_announcements` VALUES (4,'compare','Help: Item Comparison Tool',0,'padding-left: 55px; background-image: url(STATIC_URL/images/announcements/help-small.png); background-position: 10px center',1,1,'First time? - Don\'t be shy! Just check out our [url=?help=item-comparison]Help page[/url]!','Première visite? - Ne soyez pas intimidé! Vous n\'avez qu\'à lire notre [url=?help=item-comparison]page d\'aide[/url] !','Euer erstes Mal? Nur keine falsche Scheu! Schaut einfach auf unsere [url=?help=item-comparison]Hilfeseite[/url]!','¿Tu primera vez? ¡No seas vergonzoso! !Mira nuestra [url=?help=item-comparison]página de ayuda[/url]!','Впервые? Не стесняйтесь посетить нашу [url=?help=item-comparison]справочную страницу[/url]!'),(3,'profile','Quick Help: Profiler',0,'padding-left: 80px; background-image: url(STATIC_URL/images/announcements/help-large.gif); background-position: 10px center',1,1,'[h3]First Time?[/h3]\n\nThe [b]Profiler[/b] lets you [span class=tip title=\"e.g. See how\'d you look as a different race!\"]edit your character[/span], find gear upgrades, check your gear score, and more!\n\n[ul]\n[li][b]Right-click[/b] slots to change items, add gems/enchants, or find upgrades.[/li]\n[li]Use the [b]Claim character[/b] button to add your own characters to your [url=?user]user page[/url].[/li]\n[li]Save a modified character to your Aowow account by using the [b]Save as[/b] button.[/li]\n[li][b]Statistics[/b] will update in real time as you make tweaks.[/li]\n[/ul]\n\nFor more information, check out our extensive [b][url=?help=profiler]help page[/url][/b]!','','','',''),(2,'profiler','Help: Profiler',0,'padding-left: 80px; background-image: url(STATIC_URL/images/announcements/help-large.gif); background-position: 10px center',1,1,'[h3]First Time?[/h3]\r\n\r\nThe [b]Profiler[/b] tool lets you [span class=tip title=\"e.g. See how\'d you look as a different race, try different gear or talents, and more!\"]edit your character[/span], find gear upgrades, check your gear score, and more!\r\n\r\n[ul]\r\n[li][b]Right-click[/b] slots to change items, add gems/enchants, or find upgrades.[/li]\r\n[li]Use the [b]Claim character[/b] button to add your own characters to your [url=/?user]user page[/url].[/li]\r\n[li]Save a modified character to your Aowow account by using the [b]Save as[/b] button.[/li]\r\n[li][b]Statistics[/b] will update in real time as you make tweaks.[/li]\r\n[/ul]\r\n\r\nFor more information, check out our extensive [url=?help=profiler]help page[/url]!','','','','');
/*!40000 ALTER TABLE `aowow_announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_articles`
--

LOCK TABLES `aowow_articles` WRITE;
/*!40000 ALTER TABLE `aowow_articles` DISABLE KEYS */;
INSERT INTO `aowow_articles` VALUES (13,4,0,'[b][color=c4]Rogues[/color][/b] are a leather-clad melee class capable of dealing large amounts of damage to their enemies with very fast attacks. They are masters of stealth and assassination, passing by enemies unseen and striking from the shadows, then escaping from combat in the blink of an eye.\r\n\r\nThey are capable of using poisons to cripple their opponents, massively weakening them in battle. Rogues have a powerful arsenal of skills, many of which are strengthened by their ability to stealth and to incapacitate their victims.\r\n[ul]\r\n[li]Rogues can use a wide variety of melee weapons, such as daggers, fist weapons, one-handed maces, one-handed swords and one-handed axes.[/li]\r\n[li]By coating their weapons with [url=items=0.-3&filter=na=poison;ub=4]poison[/url] rogues can severely cripple or weaken their enemies.[/li]\r\n[li]When using [spell=1784] rogues will be unseen except by the most perceptive enemies.[/li]\r\n[/ul]',NULL),(14,1,0,'[b]Overview:[/b] The [b]humans[/b] are the most populous and the youngest race in Azeroth. The humans have become the [i]de facto[/i] leaders of the Alliance, with their youthful ambitions and resilience.\n\n[b]Capital City:[/b] The human seat of power is in the rebuilt city of [zone=1519].\n\n[b]Starting Zone:[/b] Humans begin questing in [zone=12].\n\n[b]Mounts:[/b] [npc=384] sells armoried ponies in Stormwind, and [npc=33307] at the Argent Tournament has a few distinct models.',NULL),(13,1,0,'[b][color=c1]Warriors[/color][/b] are a very powerful class, with the ability to tank or deal significant melee damage. The warrior\'s Protection tree contains many talents to improve their survivability and generate threat versus monsters. Protection warriors are one of the main tanking classes of the game.\n\nThey also have two damage-oriented talent trees - [icon name=ability_rogue_eviscerate][url=spells=7.1.26]Arms[/url][/icon] and [icon name=ability_warrior_innerrage][url=spells=7.1.256]Fury[/url][/icon], the latter of which includes the talent [spell=46917], which allows the warrior to wield two two-handed weapons at the same time! They are capable of strong melee AoE damage with spells such as [spell=845], [spell=1680], [spell=46924]. A warrior fights while in a specific [i]stance[/i], which grants him bonuses and access to different sets of abilities. He will use [spell=71] for tanking, and [spell=2457] or [spell=2458] for melee DPS.\n\n[ul]\n[li]All warriors can buff their raid or group by using a [i]shout[/i], [spell=6673] or [spell=469], and Fury warriors can provide the passive buff [spell=29801] which significantly increases the melee and ranged critical strike chance of his allies.[/li]\n[li]Warriors start out with only [spell=2457] at first, but learn [spell=71] at level 10 and [spell=2458] at level 30.[/li]\n[li]Warriors have numerous useful methods of getting to their target in a hurry! All warriors can use [spell=100] or [spell=20252] to reach an enemy and Protection warriors have [spell=3411], which allows them to intercept a friendly target and protect them from an attack.[/li]\n[/ul]',NULL),(13,2,0,'[b][color=c2]Paladins[/color][/b] bolster their allies with holy auras and blessing to protect their friends from harm and enhance their powers. Wearing heavy armor, they can withstand terrible blows in the thickest battles while healing their wounded allies and resurrecting the slain. In combat, they can wield massive two-handed weapons, stun their foes, destroy undead and demons, and judge their enemies with holy vengeance. Paladins are a defensive class, primarily designed to outlast their opponents.\n\nThe paladin is a mix of a melee fighter and a secondary spell caster. The paladin has a great deal of group utility due to the paladin\'s healing, blessings, and other abilities. Paladins can have one active aura per paladin on each party member and use specific blessings for specific players. Paladins are pretty hard to kill, thanks to their assortment of defensive abilities. They also make excellent tanks using their [spell=25780] ability.\n\n[ul]\n[li]Can effectively heal, tank, and deal damage in melee.[/li]\n[li]Has a wide selection of [url=spells=7.2&filter=na=blessing]Blessings[/url], [url=spells=7.2&filter=na=aura]Auras[/url], and other buffs.[/li]\n[li]Is the only class with access to a true invulnerability spell: [spell=642][/li]\n[/ul]',NULL),(14,2,0,'[b]Overview:[/b] The [b]orcs[/b] were originally a race of noble savages, residing on the world of Draenor. Unfortunately, The Burning Legion made use of them in an attempt to conquer Azeroth—they were infected with the daemonic blood of Mannoroth the Destructor, driven mad, and turned upon both the Draenei and the denizens of Azeroth. After losing the Second War, they were cut off from the corrupting influence of Mannoroth, and began to return to their shamanistic roots. Now, under the leadership of their new Warchief, the orcs are carving out a home for themselves in Azeroth.\n\n[b]Capital City:[/b] The orcs now reside in the city of [zone=1637], named after the deceased Orgrim Doomhammer, former Warchief of the Horde.\n\n[b]Starting Zone:[/b] Orcs begin questing in [zone=14].\n\n[b]Mounts:[/b] [npc=3362] in Orgrimmar sells a variety of wolves; [npc=33553] sells a few distinctive mounts at the Argent Tournament.',NULL),(13,3,0,'[b][color=c3]Hunters[/color][/b] are a very unique class in World of Warcraft. They are the sole non-magical ranged damage-dealers, fighting with bows and guns. Hunters have a number of different kinds of shots and stings, which can be used to debuff an enemy, and are capable of laying traps to deal damage or otherwise slow/incapacitate their enemy.\n\nA hunter will also tame his very own [url=pets]pet[/url] to aid them in combat. While they are not the only class which can use pet minions, the hunter\'s pet is unique in that each species has a particular type of talent tree, which the hunter can use to distribute points into various skills and passive abilities.\n\nIn addition, each species has a unique special ability. Hunters can seek out the most desirable pets based on their appearances or abilities, and if they spec deep enough into the [icon name=ability_hunter_beasttaming][url=spells=7.3.50]Beast Mastery[/url][/icon] tree they gain access to special, \"exotic\" beasts such as [pet=46] or [pet=39]!\n\n[ul]\n[li]Hunters have access to 23 (32 if [icon name=ability_hunter_beasttaming][url=spells=7.3.50]Beast Mastery[/url][/icon]) different [url=pets]species of pets[/url], featuring over 150 different appearances![/li]\n[li]Hunters have a number of survival-oriented skills which they can use to escape or avoid potential danger, such as [spell=5384] and [spell=781].[/li]\n[li][icon name=ability_hunter_swiftstrike][url=spells=7.3.51]Survival[/url][/icon] hunters can spec down the tree into [spell=53292], which allows them to provide the [spell=57669] buff to their party and raid members.[/li]\n[/ul]',NULL),(13,5,0,'[b][color=c5]Priests[/color][/b] are commonly considered one of the standard healing classes in World of Warcraft, as they have two talent specs that can be used to heal quite effectively.\n\nTheir [icon name=spell_holy_holybolt][url=spells=7.5.56]Holy[/url][/icon] tree includes talents which strongly boost the healing done to their allies, including spells that can be used to heal multiple players at once, such as [spell=48089]. The [icon name=spell_holy_wordfortitude][url=spells=7.5.613]Discipline[/url][/icon] tree, while still capable of significant raw healing output, focuses primarily on damage absorption and mitigation through use of [spell=48066] and procced shielding effects. Priests are also capable of very powerful ranged damage with their unique [icon name=spell_shadow_shadowwordpain][url=spells=7.5.78]Shadow[/url][/icon] abilities, and upon entering [spell=15473] will see a significant increase in their shadow damage while losing the ability to cast any Holy spells.\n\n[ul]\n[li]While the [icon name=spell_holy_wordfortitude][url=spells=7.5.613]Discipline[/url][/icon] talent tree is commonly used for healing, it also contains some powerful talents that can boost the priest\'s Holy damage, though [icon name=spell_shadow_shadowwordpain][url=spells=7.5.78]Shadow[/url][/icon] spells and abilities should be used primarily for DPS.[/li]\n[li]Priests provide of the most appreciated buffs in the game - [spell=48161], which grants an indispensable stamina buff to everyone in the raid. They can also buff both [spell=48073] and [spell=48169]![/li]\n[li]Shadow priests are an excellent utility class for any raid, providing the much-loved [spell=57669] buff to boost mana regeneration and can even heal their own party with [spell=15286]![/li]\n[/ul]',NULL),(13,6,0,'Introduced in the Wrath of the Lich King expansion, [b][color=c6]Death Knights[/color][/b] are World of Warcraft\'s first hero class. Death knights start at level 55 in a special, instanced zone unreachable by any other class: Acherus, the Ebon Hold, located in [zone=4298]. Here they will earn their talent points as quest rewards and even get a special summoned mount, the [spell=48778]!\n\nDeath knights have multiple very strong damage dealing options, as each of their talent trees can be specced to perform exceptionally well with a variety of melee abilities, spells and damage-over-time dealing diseases. They are also very capable tank classes, with both their Blood and Frost trees providing unique options - [icon name=spell_deathknight_bloodboil][url=spells=7.6.770]Blood[/url][/icon] dealing more with self-healing abilities and [icon name=spell_frost_frostnova][url=spells=7.6.771]Frost[/url][/icon] providing significant damage mitigation and strong AoE damage.\n\nDeath knights fight with a special buff active called a [i]presence[/i] (similar to a warrior\'s stances) which provides special bonuses to their roles. Death knights utilize a unique power system, with most spells costing either Runes, which are replenished throughout battle, or Runic Power, which can be generated by various abilities.\n\n[ul]\n[li][icon name=spell_deathknight_armyofthedead][url=spells=7.6.772]Unholy[/url][/icon] death knights can spec into [spell=52143], which makes their summoned Ghoul minion a permanent pet to aid in battle![/li]\n[li]The death knight class has its own special weapon enchanting ability called [spell=53428], which replaces the need for conventional weapon enchants.[/li]\n[li]Death knights are a very unique damage-dealing class in that their damage is dealt by both melee abilities [i]and[/i] spells![/li]\n[/ul]',NULL),(13,7,0,'[b][color=c7]Shamans[/color][/b] master elemental and nature magics and bring the most potential buffs to any group in the form of totems. A shaman can summon one totem of each element - earth, fire, air, and water - which appears at the shaman\'s feet and provides a buff to anyone in the shaman\'s party or raid within range of it. Some shaman totems, notably the fire ones, also do damage to opponents. The trick to playing any type of shaman is knowing which totems to cast under which circumstances to maximize the group\'s damage output and survivability.\n\nShamans are primarily spellcasters, although an [icon name=spell_nature_lightningshield][url=spells=7.7.373]Enhancement[/url][/icon] shaman likes to get close and personal and do damage within melee range. An enhancement shaman learns to [spell=30798] weapons and can use [spell=51533] to summon a pair of Spirit Wolves to aid in battle. Despite being primarily melee, [icon name=spell_nature_lightningshield][url=spells=7.7.373]Enhancement[/url][/icon] shamans can still gain some benefit from spellpower and can cast instant [spell=403] or heals with [spell=51530]. \n\n[icon name=spell_nature_lightning][url=spells=7.7.375]Elemental[/url][/icon] shamans stand back and cast fire and lightning spells to deal great amounts of damage. They can push back enemies with [spell=51490] and root all enemies in an area with[spell=51486]. They also bring [icon name=spell_fire_totemofwrath][url=spell=57722]Totem of Wrath[/url][/icon] and [spell=51470] as amazing spellcaster raid buffs. A shaman that choses [icon name=spell_nature_magicimmunity][url=spells=7.7.374]Restoration[/url][/icon] gains improved healing spells and can be a great raid or tank healer. Resto shamans are known for their powerful [spell=1064] ability and for providing a [spell=16190] to help their party\'s mana restoration. They also gain a powerful [spell=974], can use [spell=51886] to remove curses, and have an instant-cast direct heal plus heal over time effect called [spell=61295].\n\n[ul]\n[li]There are over twenty different totems a shaman can learn![/li]\n[li]Shamans can cast [spell=2825] (or [spell=32182]) to boost the entire group\'s damage and healing. This buff is unique and oft sought after for a raid group.[/li]\n[li]A shaman can turn into a [spell=2645] at level 16 and can even make it instant cast with [spell=16287]. This spell can be used in combat, but not indoors.[/li]\n[li]Shamans can only have one elemental shield - [spell=324] or [spell=52127] - on at a time. [spell=974], if the shaman knows it, can be cast on another player.[/li]\n[/ul]',NULL),(13,8,0,'[b][color=c8]Mages[/color][/b] wield the elements of fire, frost, and arcane to destroy or neutralize their enemies. They are a robed class that excels at dealing massive damage from afar, casting elemental bolts at a single target, or raining destruction down upon their enemies in a wide area of effect. Mages can also augment their allies\' spell-casting powers, summon food or drink to restore their friends, and even travel across the world in an instant by opening arcane portals to distant lands.\n\nWhen seeking someone to introduce monsters to a world of pain, the Mage is a good choice. With their elemental and arcane attacks, it\'s a safe bet something they can do won\'t be resisted by your chosen enemy. Damage is the name of the Mage game, and they do it well. Their arsenal includes some powerful buffs, debuffs, stuns, and snares, enabling them to dictate the terms of any fight.\n\n[ul]\n[li]Can [spell=42956] to restore their allies\' health and mana.[/li]\n[li]Are the only class that can create portals to transport other players. They cannot, however, summon players [i]from[/i] a distant location - that\'s a [icon name=class_warlock][color=c9]Warlock\'s[/color][/icon] job![/li]\n[li]Mages who use [item=50045] can have a permanent water elemental pet![/li]\n[/ul]',NULL),(13,9,0,'[b][color=c9]Warlocks[/color][/b] are masters of the demonic arts. Clothed in demonic styled cloth, they excel in using curses, firing bolts of fire or shadow, and summoning demons to help them in combat. Warlocks, while being excellent spell casters, also excel in supporting fellow allies by summoning other players or using ritual magics to conjure stones imbued with the power to heal.\r\n\r\nA warlock has very powerful abilities that, if used correctly, make them a very formidable opponent. Using their curses in combination with direct damage spells, Warlocks wreak havoc and destruction.\r\n\r\n[ul]\r\n[li]Can use a [spell=698] to summon another player to the portals location.[/li]\r\n[li]Are able to conjure [icon name=inv_stone_04][url=item=5509]Healthstones[/url][/icon] that have the ability to heal the user.[/li]\r\n[li]Can use curses on enemies to [url=spell=47865]weaken[/url] them or [url=spell=47864]damage[/url] them.[/li]\r\n[/ul]',NULL),(13,11,0,'[b][color=c11]Druids[/color][/b] are World of Warcraft\'s \"jack of all trades\" class -- that is, capable of performing in a variety of different roles and as such have one of the most varied playstyles. A druid can act as a healer, melee DPS, ranged DPS or a tank, utilizing a variety of [i]shapeshifting[/i] forms. As a druid levels up, he is able to learn new, powerful forms which he can cast to change into different creatures to suit their roles.\n\nAt lower levels, a druid will heal or ranged DPS in his caster form, but at later levels players who spec into the specialized trees will gain access to two special shapeshift forms for each different role.\n\nHealing druids will learn [spell=33891], which reduces the mana cost of their healing spells and grants a passive healing aura to their allies. Their ranged damage-dealing counterparts will learn [spell=24858], increasing their armor and granting a spell critical aura to their allies. There are also two feral form druid forms -- the mighty [spell=5487] (and at later level, [spell=9634]), a tanking-oriented form which provides additional armor and health and grants access to an arsenal of threat-building and damage mitigation abilities, and the rogue-like [spell=768] which is capable of significant melee DPS.\n\n[ul]\n[li]Druids learn their different forms through questing or training. Some shapeshifts are only learned via talents.[/li]\n[li]There are some shapeshifts that all druids can learn. [spell=5487] is obtained at level 10, [spell=1066] and [spell=783] at level 16, [spell=768] at level 20 and [spell=9634] at level 40.[/li]\n[li]Druids even have their own flying travel form! [spell=33943] can be trained at level 60, and [spell=40120] at level 71 provided the player has already trained [spell=34091].[/li]\n[li]Some druid shapeshifts are obtained via talents only - [spell=24858] can be obtained at level 40 when a player specs deep into the [icon name=spell_nature_starfall][url=spells=7.11.574]Balance[/url][/icon] tree, and [spell=33891] at level 50 after speccing deep into [icon name=spell_nature_healingtouch][url=spells=7.11.573]Restoration[/url][/icon].[/li]\n[li]Druids have their own, class-specific teleport ability that allows them to travel to and from [zone=493], which is handy when needing to train![/li]\n[li]Because feral druids do not actually swing weapons while in shapeshift forms, they instead gain a special statistic from any melee weapon they equip called \"feral attack power.\" This stat is a conversion of a weapon\'s DPS (damage per second) into an attack power-granting statistic which affects the cat or bear\'s damage output.[/li]\n[/ul]',NULL),(14,3,0,'[b]Overview:[/b] The [b]dwarves[/b] are a hardy race, hailing from Khaz Modan in the Eastern Kingdoms. Rumor has it they are descended from the Titans. There are three main clans of dwarves vying for power in Ironforge: the Bronzebeards, Wildhammers, and Dark Irons.\n\n[b]Capital City:[/b] The dwarves make their home in their ancestral seat of [zone=1537].\n\n[b]Starting Zone:[/b] Dwarves begin in [zone=1].\n\n[b]Mounts:[/b] [npc=1261] by the Amberstill Ranch sells rams, as well as [npc=33310] at the Argent Tournament.',NULL),(14,4,0,'[b]Overview:[/b] The [b]night elves[/b] are an ancient and mysterious race. They lived in Kalimdor for thousands of years, undisturbed until the world tree was sacrificed to halt the advance of the Burning Legion prior to the events of World of Warcraft.\n\n[b]Capital City:[/b] The night elf capital city is [zone=1657], situated in the branches of the world tree itself.\n\n[b]Starting Zone:[/b] Night Elves begin in [zone=141], learning about the recent political changes in Darnassus.\n\n[b]Mounts:[/b] [npc=4730] in Darnassus sells a variety of nightsabers, as well as [npc=33653] at the Argent Tournament.',NULL),(14,5,0,'[b]Overview:[/b] When the [b]undead[/b] scourge initially swept across Azeroth, they converted a number of members of the Alliance to the undead. When the combined forces of the orcs, elves, trolls, dwarves and humans began to fight back, though, [npc=36597]\'s hold on his forces began to weaken. A small faction of humans, known as the Forsaken, broke free of the Lich King\'s control.\n\nNow, free of the bonds of servitude as well as the troublesome emotions and connections of their human lives, the Forsaken have found a new home—with the Horde.\n\n[b]Capital City:[/b] The Forsaken reside in the [zone=1497], underneath the ruins of the former human city of Lordaeron.\n\n[b]Starting Zone:[/b] [zone=85] is the starting zone for Forsaken players--they are raised as second-generation Forsaken by val\'kyr and experience Sylvanas\' menacing new agenda firsthand.\n\n[b]Mounts:[/b] [npc=4731] in Tirisfal Glades sells numerous undead horses; [npc=33555] at the Argent Tournament sells a few distinct models.',NULL),(14,6,0,'[b]Overview:[/b] The [b]tauren[/b], a race with deep shamanistic roots, are longtime residents of Kalimdor. They have a deep and abiding love of nature, and the vast majority of them worship a deity known as the Earth Mother. \n\n[b]Capital City:[/b] The tauren reside in [zone=1638].\n\n[b]Starting Zone:[/b] Tauren begin questing in [zone=215].\n\n[b]Mounts:[/b] [npc=3685] sells numerous kodo mounts; [npc=33556] at the Argent Tournament sells a few distinctive models.',NULL),(14,7,0,'[b]Overview:[/b] The [b]gnomes[/b] are a quirky race, obsessed with gadgets and technology. They originally come from the city of [zone=721], which was destroyed by [npc=7937] in an attempt to save it from an invading army of troggs.\n\n[b]Capital City:[/b] The gnomes now make their home in [zone=1537]; they have made efforts to retake their beloved former city with [achievement=4786].\n\n[b]Starting Zone:[/b] Gnomes begin in [zone=1], but they have a very different quest sequence from Dwarves, covering Gnomeregan.\n\n[b]Mounts:[/b] [npc=7955] in Dun Morogh sells numerous mechanostriders, as well as [npc=33650] at the Argent Tournament.',NULL),(14,8,0,'[b]Overview:[/b] While there are many different tribes of [b]trolls[/b] scattered across Azeroth, only the [url=?faction=530]Darkspear Tribe[/url] has ever sworn allegiance to the Horde. The trolls originally lived in the Broken Isles, but were overrun by naga and murlocs and driven from their home. The orcs, led by [npc=4949], saved the Darkspear tribe from certain destruction and offered them amnesty among the Horde. In return, the Darkspear tribe swore fealty to the orcish warchief.\n\n[b]Capital City:[/b] The Darkspear Trolls live now in the Horde capital of [zone=1637].\n\n[b]Starting Zone:[/b] Trolls begin questing in [b]Echo Isles[/b].\n\n[b]Mounts:[/b] [npc=7952] in Sen\'jin Village sells numerous raptors; [npc=33554] at the Argent Tournament sells a few distinctive models.',NULL),(14,10,0,'[b]Overview:[/b] The [b]blood elves[/b] are a proud, haughty race, joining the Horde in Burning Crusade. They represent a faction of former high elves, split off from the rest of elven society; they are also survivors of Arthas\' assault on Silvermoon. Blood elves are fully dependent on magic, having revelled in its power for so long that they suffer horrible withdrawal if it were to be taken away.\n\n[b]Capital City:[/b] The blood elves have rebuilt [zone=3487].\n\n[b]Starting Zone:[/b] [zone=3430] is the starting zone for Blood Elves.\n\n[b]Mounts:[/b] [npc=16264] in Eversong Woods sells numerous hawkstriders; [npc=33557] at the Argent Tournament sells a few unique models.',NULL),(14,11,0,'[b]Overview:[/b] The [b]Draenei[/b] are followers of the Naaru and worshipers of the Holy Light. They originally hail from the distant world of Argus, fleeing after Sargeras tried to corrupt them. They then settled on the Orcish homeworld of Draenor, where after a period of peace, they were brutally murdered during Guldan\'s corruption of the Orcs. Finally they settled in Azeroth, to seek aid in their battle against the Burning Legion. Draenei were introduced in the Burning Crusade expansion.\n\n[b]Capital City:[/b] The Draenei have the seat of their power in the ruins of their once-great ship, [zone=3557].\n\n[b]Starting Zone:[/b] [zone=3524] and [zone=3525] cover the attempts of the Draenei to settle on their new island and deal with the inherent corruption present.\n\n[b]Mounts:[/b] [npc=17584] sells a variety of Elekks, as well as [npc=33657] at the Argent Tournament.',NULL),(8,21,0,'[minibox]\n[h2]Steamwheedle Cartel[/h2]\n[b]Booty Bay[/b]\n[faction=577]\n[faction=369]\n[faction=470]\n[/minibox]\n\n\n[b]Booty Bay[/b] is a large pirate town nestled into the cliffs surrounding a beautiful blue lagoon on the southern tip of [zone=33]. The city is entered by traversing through the bleached-white jaws of a giant shark.\n\nRun by the Blackwater Raiders who are closely associated with the Steamwheedle Cartel, the port offers facilities to any traveller passing through, regardless of their faction. Combined with the world renowned Salty Sailor Tavern, [event=15], numerous profession trainers, and vendors that sell everything from pets to diamond rings, it is one of the most popular locations in Azeroth.\n\n[npc=2496], ruler of this city, is hiring all the help he can get against the pesky [faction=87] and other threats of the city. He resides, together with the leader of the Blackwater Raiders, [npc=2487], at the top of the inn of Booty Bay.\n\nDue to the boat route from Booty Bay to Ratchet, players of all level ranges (mostly Horde, if lower level) can be expected to be found going about their business, although frequent visitors will more than likely fit in the 35 - 45 range. The quests available from the locals reflect this range nicely.\n\nThe water there occasionally has floating wreckages and schools of fish. The schools that are found most often are [item=6359], [item=6358], and [item=13422]. Fishing in the floating wreckages will also give you very high chances of fishing out chests and items, making Booty Bay an ideal place for fishing.\n\n[h3]Reputation[/h3]\nMost of the quests to raise reputation with Booty Bay are located in The Cape of Stranglethorn. Having a friendly or higher reputation will make the guards help you in case of initiated violence against you.\n\nIf you are Hated with Booty Bay, you can do the repeatable quest [quest=9259] to get back to Neutral.',NULL),(8,47,0,'[b]Ironforge[/b] is the faction associated with the capital city of the dwarves, [zone=1537]. [npc=2784] rules his kingdom of Khaz Modan from his throne room within the city, and the [npc=7937], leader of the gnomes, has temporarily had to settle down in Tinker Town after the recent fall of the gnome city [zone=133].\n\n[h3]History[/h3]\nIronforge is the ancient home of the dwarves. A marvel to the dwarves\' skill at shaping rock and stone, Ironforge was constructed in the very heart of the mountains, an expansive underground city home to explorers, miners, and warriors. Massive doors of rock protect the city in times of war, and lava from the mountain itself is redirected and distributed for heat, energy and smithing purposes. Before the Dark Iron Clan was banished from the city, eventually leading to the War of the Three Hammers, Ironforge was the commercial and social center of all the dwarven clans. It is now home to the Bronzebeard Clan. Many dwarven strongholds fell during the Second War between the Horde and the Alliance of Lordaeron, but the mighty city of Ironforge, nestled in the wintry peaks of [zone=1] and protected by its great gates, was never breached by the invading Horde.\n\nRelatively recently, Ironforge also became home to the Gnomeregan refugees. After the Third War, the gnomish city of Gnomeregan became overrun by troggs. Since then, a number of gnomes have settled in Ironforge, converting an area of that city to their liking, an area now known as Tinker Town.\n\nIronforge is one of most populated cities in the world, coming after the human city of [zone=1519], and housing 20,000 people.\n\nWhile the Alliance has been weakened by recent events, the dwarves of Ironforge, led by King Magni Bronzebeard, are forging a new future in the world.[h3]Reputation[/h3]\n[npc=14723] has the repeatable cloth reputation quests. As a reward for being exalted with Ironforge, non-dwarf players are able to ride [url=?items=15.5&filter=na=Ram;cr=93:92;crs=2:1;crv=0:0]rams[/url].\n\nSurrounding zones [zone=1], [zone=38] and [zone=11] contain the most quests for gaining reputation with Ironforge.',NULL),(8,54,0,'[b]Gnomeregan Exiles[/b] is the faction of gnomes who fled from their home, [zone=133] in [zone=1]. It was destroyed by the [url=?npcs=7&filter=na=Trogg]Trogg[/url] after a toxic invasion. Now a member of the Alliance, most are located in the Tinkertown section of the neighboring city [zone=1537], including leader [npc=7937].\n\n[h3]History[/h3]\nIt has been speculated that gnomes were formed as robots by the Titans, due to their inquisitive nature and technical skills.\n\nGnomes were an underground race of tinkers, residing in Gnomeregan until the troggs destroyed it. In this war, over 80% of the gnomish population was lost.\n\n[h3]Reputation[/h3]\n[npc=14724] has the repeatable cloth reputation quests. As a reward for being exalted with Ironforge, non-gnome dwarf players are able to ride [url=?items=15.5&filter=na=Mechanostrider;cr=93:92;crs=2:1;crv=0:0]mechanostriders[/url].\nSurrounding zone [zone=1] contain the most quests for gaining reputation with the Gnomeregan Exiles.',NULL),(8,59,0,'The [b]Thorium Brotherhood[/b] are an elite group of craftsmen who can reveal a number of epic recipes if you gain enough faction reputation with them. All players start off at Neutral reputation with them.\n\n[h3]History[/h3]\n\nThe [zone=51] is home to a group of exceptionally stout dwarves who have split from the Dark Iron Clan. On the cliffs overlooking the region called the Cauldron, in the far north of the Searing Gorge, the dwarves of the Thorium Brotherhood have established a base of operations, Thorium Point. From here, they keep a close eye on the Dark Iron dwarves\' activities in the Searing Gorge and beyond. Adventurers seeking out Thorium Point will find that the dwarves of the Thorium Brotherhood hold great rewards for those who aid them in their never ending struggle against their former brethren.\n\nThe Thorium Brotherhood comprises many exceptionally talented craftsmen, and the blacksmiths of the Brotherhood are rumored to be among the finest Azeroth has ever seen. They possess the knowledge required to make the arms and armaments of [npc=11502], the Fire Lord, but lack the manpower to obtain the materials required for the crafting. It is rumored that one member of the Thorium Brotherhood has been empowered to trade the dwarves\' fabled recipes and plans with those who can prove their loyalty to the Brotherhood. Of course, proving one\'s loyalty at some point may include venturing to the heart of the [zone=2717], the domain of Ragnaros, the Fire Lord himself, to supply the dwarves with the rare raw materials found there. A daunting task, no doubt, but gaining access to the Thorium Brotherhood\'s secrets should prove to be a reward well worth the effort.\n\n[h3]Reputation[/h3]\n\n[b]Neutral to Friendly[/b]\n\n[ul]\n[li]Turn in [item=18944], [item=3857] and either [item=4234], [item=3575], or [item=3356] to [npc=14624].[/li][/ul]\n[b]Friendly to Honored[/b]\n\n[ul]\n[li]Turn in [item=18945] to Master Smith Burninante.[/li][/ul]\n[b]Honored to Exalted[/b]\n\n[ul]\n[li]Turn in [item=11370] to [npc=12944].[/li]\n[li]Turn in [item=17012] to Lokhtos Darkbargainer.[/li]\n[li]Turn in [item=17010] to Lokhtos Darkbargainer.[/li]\n[li]Turn in [item=17011] to Lokhtos Darkbargainer.[/li]\n[li]Turn in [item=11382] to Lokhtos Darkbargainer.[/li][/ul]',NULL),(8,68,0,'[b]Undercity[/b] is the faction for the capital city of the Forsaken Undead, [zone=1497], ruled by Sylvanas Windrunner. It is located in [zone=85], at the northern edge of the Eastern Kingdoms. The city proper is located under the ruins of the historical City of Lordaeron. To enter it, you will walk through the ruined outer defenses of Lordaeron and the abandoned throneroom, until you reach one of three elevators guarded by two abominations.\n\n[h3]History[/h3]\nThe Undercity was originally simply a system of sewers, crypts, and catacombs beneath the Capital City of Lordaeron. After the city was destroyed by the Scourge, Arthas had the underground warren expanded and rebuilt. He originally intended for the Undercity to be his seat of power, from which he would rule the Plaguelands. However, shortly after the Third War ended, Arthas was forced to return to Northrend and save the Lich King. In his absence, [npc=10181] and her rebel Undead captured the ruins of the city. Soon after, she discovered the massive underground fortress, and decided to establish it as the main base of operations for the Undead Forsaken.\n\n[h3]Reputation[/h3]\n[npc=14729] has the Undercity repeatable cloth quests used by non-Undead Horde players to obtain the right to ride [url=?items=15.5&filter=na=Skeletal;cr=93:92;crs=2:1;crv=0:0]skeletal horses[/url] at exalted.\n\nSurrounding zones [zone=267], [zone=130], and Tirisfal Glades have the most quests to earn reputation with Undercity.',NULL),(8,69,0,'[b]Darnassus[/b] is the faction associated with [zone=1657], the capital city of the Night Elves. The high priestess, [npc=7999], resides in the Temple of the Moon, surrounded by other sisters of Elune. In the Cenarion Enclave, the [npc=3516] leads the [faction=609], often in direct opposition to his fellow druids in [zone=493] and Tyrande herself.\n\n[h3]History[/h3]\nIn the aftermath of the Third War, the night elves had to adjust to their mortal existence. Such an adjustment was far from easy, and there were many night elves who could not adjust to the prospects of aging, disease and frailty. Seeking to regain their immortality, a number of wayward druids conspired to plant a special tree that would reestablish a link between their spirits and the eternal world.\n\nWith [npc=15362] missing, Fandral Staghelm - the leader of those who wished to plant the new World Tree - became the new Arch-Druid. In no time at all, he and his fellow druids had forged ahead and planted the great tree, [zone=141], off the stormy coasts of northern Kalimdor. Under their care, the tree sprouted up above the clouds. Among the twilight boughs of the colossal tree, the wondrous city of Darnassus took root. However, the tree was not consecrated with nature\'s blessing and soon fell prey to the corruption of the Burning Legion. Now the wildlife and even the limbs of Teldrassil are tainted by a growing darkness.\n\n[h3]Reputation[/h3]\n[npc=14725] has the Darnassus repeatable [quest=7800] used by non-night elven Alliance players to obtain the right to ride [url=?items=15.5&filter=na=Reins+-Winterspring;ra=4;cr=93:92;crs=2:1;crv=0:0]night sabers[/url].[pad]Players who are at or close to level 44 looking to gain the favor of Darnassus should find and complete the quests of [zone=357]. The quests therein are associated with Darnassus and could prove to substantially increase your reputation should they all be completed.',NULL),(8,70,0,'The [b]Syndicate[/b] is a mostly Human criminal organization that operates primarily in the [zone=45] and the [zone=36], although a few small encampments are scattered in the [zone=267]. Their membership numbers around 3,000 persons.\n\nThey have three leaders: [npc=2423] (who took over from his father Aiden Perenolde), descendent of the original Lord of Alterac, who directs the Syndicate\'s actions in the Alterac Mountains from Strahnbrad; [npc=2597] directs Syndicate actions in Arathi Highlands from the main keep in the semi-abandoned fortress of Stromgarde; and Lady Beve Perenolde, daughter of Aiden Perenolde.\n\n[h3]History[/h3]\n\nDuring the Second War the Kingdom of Alterac, led by Lord Perenolde, was discovered to be in league with the Orcish Horde. Perenolde believed that a Horde victory was inevitable, and thus offered aid to the Horde by stirring up rebellions, attacking Alliance bases, and giving them supplies. When this treachery was discovered, the Alliance marched on Alterac and destroyed it. Perenolde and any nobles who went along with his plans were stripped of their titles and land. Many of the nobility managed to escape, however, and began plotting their revenge. Using their still sizable fortunes, the nobility hired a band of thieves and assassins, forming an organization known as the Syndicate.\n\nAt first the Syndicate\'s goal was just to spread chaos and disorder, striking from hidden bases in the Alterac Mountains. With the end of the Third War and the resultant chaos however, the leaders of the Syndicate saw their chance to return Alterac to its former power. They have now gained control of several outposts in the surrounding area including the sacked fortress of Durnholde Keep and a portion of the city of Stromgarde.\n\nThey are enemies of both the Alliance, whom they consider their mortal enemies, and the Horde, whom they consider mere brutes good for nothing but slave labor. As a result, the Syndicate is now hunted by both factions, with the [npc=10181], in particular, placing a bounty on their heads - guaranteeing that all captured Syndicate members will be summarily executed. In addition, [npc=4949] ordered a number of his agents, including [npc=2229], [npc=2239], [npc=2238] and their leader [npc=2316] to launch an investigation into the nature of the Syndicate and its activities, as well as to recover [item=3498], which belonged to a dear friend of his, [npc=18887] - a necklace now worn by Elysa, the mistress of Lord Aliden.\n\n[h3]Reputation[/h3]\n\nThe Syndicate as a faction in World of Warcraft is very odd in comparison to most factions in that the killing of the factions members will not lower your standing with the faction. For most players who are not a rogue, the only way for the Syndicate to appear on their Reputation Menu is to complete the quest [quest=8249], which is available to non-rogues. However, the quest requires [item=16885] ... which only rogues can obtain by pick-pocketing NPCs above level fifty, and those can only be traded to you - making it difficult to arrange such a transaction.\n\nCurrently there is only one known option to increase a player’s reputation with the Syndicate, and that is by killing members of the [faction=349] faction. There are no known rewards for increasing Syndicate reputation, and Ravenholdt-affiliated NPCs only give 1 Syndicate Reputation points, with the exception of [npc=13085], who gives 5 (although the corresponding loss of reputation with Ravenholdt is also five times as great). With all players starting at 32000/36000 hated with the faction, it would require killing 10,000 Ravenholdt NPCs to reach Neutral status with the faction; unfortunately, neutral status is the highest you can reach with the Syndicate, and if not to deter players further, none of the Ravenholdt NPCs drop loot.\n\n[b]WARNING[/b]: If you do decide to kill Ravenholdt NPCs, know that there is currently no way to restore your standings with Ravenholdt, if you do go below Neutral. The reason for the problem is that none of the quests that give Ravenholdt Reputation points will be available because none of the members from Ravenholdt will speak to you. This would mean its a permanent change and you will never be able to interact with any of the NPC loyal to Ravenholdt ever again. Also note that players start at 0/3000 reputation with Ravenholdt, and killing even one of their NPCs at this reputation level will forever prevent you from raising your reputation with them again.',NULL),(8,72,0,'[b]Stormwind[/b] is the faction associated with [zone=1519], the capital of the humans. It is located in the northwestern part of [zone=12]. The child king, [npc=1747], resides in Stormwind Keep, surrounded by his body guards and advisors, [npc=1748] (the regent), and [npc=1749]. The city is named for the occasional sudden squalls created by a ley line pattern in the mountains around the glorious city.\n\n[h3]History[/h3]\nDuring the First War, the Kingdom of Azeroth, including its capital, Stormwind Keep, was utterly destroyed by the Horde and its survivors fled to Lordaeron. After the orcs were defeated at the Dark Portal at the end of the Second War, it was decided that the city would be rebuilt, even surpassing its former grandeur. The nobles of Stormwind assembled a team of the most skilled and ingenious stonemasons and architects they could find. Under their direction, Stormwind was rebuilt in an amazingly short period of time. Now, at the end of the Third War, in the renamed Kingdom of Stormwind, it stands as one of the last bastions of human power left in the world. \n\nWith the fall of the northern kingdoms, Stormwind is by far the most populated city in the world. Boasting a population of two-hundred thousand people (predominantly human), it serves in many ways as the cultural and trade center of the Alliance, even with remote access to the sea. The humans living in the city are generally carefree and artistic, favoring light and colorful clothes, cuisine and art. It is home to the Academy of Arcane Sciences, the only wizarding school in Eastern Kingdoms, as well as SI:7, a rogue intelligence organization.\n\nHowever, the people of Stormwind find it difficult to accept Theramore\'s role as the home of the new Alliance, convinced not only that Stormwind should be the legitimate heir of Lordaeron\'s role in the past, but also that Theramore is doing little against the worsening situation within the Eastern Kingdoms.\n\n[h3]Reputation[/h3]\n[npc=14722] has the repeatable cloth quests to achieve a higher reputation with Stormwind. In return for exalted reputation, non-human players are able to ride horses.\n\nMost quests associated with Stormwind come from the surrounding areas of Elwynn Forest, [zone=40], and [zone=44].',NULL),(8,76,0,'[b]Orgrimmar[/b] is the faction for the capital city [zone=1637] of the orcs and trolls of the [faction=530]. Found at the northern edge of [zone=14], the imposing city is home to the orcish Warchief, [npc=4949].\n\n[h3]History[/h3]\nThrall led the orcs to the continent of Kalimdor, where they founded a new homeland with the help of their tauren brethren. Naming their new land Durotar after Thrall\'s murdered father, the orcs settled down to rebuild their once-glorious society. The demonic curse on their kind ended, the Horde changed from a warlike juggernaut into more of a loose coalition, dedicated to survival and prosperity rather than conquest. Aided by the noble tauren and the cunning trolls of the Darkspear tribe, Thrall and his orcs looked forward to a new era of peace in their own land. \n\nFrom there, they began the creation of the great warrior city, Orgrimmar. Named after the former Warchief, Orgrim Doomhammer, the new city was constructed in a short amount of time, with the aid of goblins, tauren, trolls, and the Mok\'Nathal Rexxar. Despite having some problems with the centaur, harpies, enraged thunder lizards, kobolds, evil orcish warlocks, quilboars, and unfortunately, the Alliance, Orgrimmar prospered in the end and became home to the orcs and Darkspear Trolls.\n\nToday, Orgrimmar lies at the base of a mountain between Durotar and [zone=16]. A warrior city indeed, it is home to countless amounts of orcs, trolls, tauren, and an increasing amount of Forsaken are now joining the city, as well as the Blood Elves who have recently been accepted into the Horde.\n\n[h3]Reputation[/h3]\n[npc=14726] has the Orgrimmar repeatable cloth quests used by non-orcish Horde players to obtain the right to ride [url=?items=15.5&filter=na=Wolf;cr=93:92;crs=2:1;crv=0:0]wolves[/url] at exalted.\n\nSurrounding areas Durotar and [zone=17] have the most quests for gaining reputation with Orgrimmar.',NULL),(8,81,0,'[b]Thunder Bluff[/b] is the faction of the Tauren capital city [zone=1638] located in the northern part of the region of [zone=215]. The whole of the city is built on bluffs several hundred feet above the surrounding landscape, and is accessible by elevators on the southwestern and northeastern sides.\n\n[h3]History[/h3]\nThe great city of Thunder Bluff lies atop a series of mesas that overlook the verdant grasslands of Mulgore. The once nomadic Tauren recently built the city as a center for trade caravans, traveling craftsmen and artisans of every kind. It was established by the mighty chief [npc=3057] after the Tauren, with help from the orcs, drove away the centaurs that originally inhabited Mulgore. Long bridges of rope and wood span the chasms between the mesas, topped with tents, longhouses, colorfully painted totems, and spirit lodges. The Tauren chief watches over the bustling city, ensuring that the united Tauren tribes live in peace and security.\n\n[h3]Reputation[/h3]\n[npc=14728] has the Thunder Bluff repeatable cloth quests used by non-tauren Horde players to obtain the right to ride [url=?items=15.5&filter=na=Kodo;cr=93:92;crs=2:1;crv=0:0]kodos[/url] at exalted.\n\nSurrounding zones Mulgore and [zone=17] have the most quests for gaining reputation with Thunder Bluff.',NULL),(8,87,0,'During the events leading up to and following the Third War, several criminal organizations appeared in Azeroth. The [b]Bloodsail Buccaneers[/b] appear to be one of these organizations, originating from the Bloodsail Hold on Plunder Isle and is where their ruler, Duke Falrevere holds court. They now plot to plunder and cripple the Steamwheedle Cartel controlled port town of [faction=21], currently under the protection of the Blackwater Raiders. It is likely the Bloodsail Buccaneers have come to take advantage of the town’s current loss of its fleet off the coast of the [zone=45], in which two of its ships were destroyed, and the remaining ship forced to find shelter in a cove, where its crew now fights to survive skirmishes with the Daggerspine Naga.\n\nIn preparation of the attack the Bloodsail Buccaneers have taken position in key locations near the town. Currently they have three ships anchored along the coastline south of Booty Bay, clear of the town’s defensive cannons, with camps also being built along the same coast in preparation of the attack. In addition, a scouting party has landed just west of the entrance to the town, reporting all activities, along with a compound being constructed along the road leading towards the town, likely to stop any re-enforcements from coming to help.\n\nBoth the Bloodsail Buccaneers and Blackwater Raiders seek to achieve their goals without having their forces engaged in battle, to this end each side now seek the aid of adventurers sympathetic to their cause.\n\n[h3]Reputation[/h3]\nThere is only one way to increase your reputation with the Bloodsail Buccaneers and that’s to unleash your wrath on any citizen of Booty Bay who can be found through out the Eastern Kingdoms. Below is a list of every citizen of Booty Bay and their reputation value. The amount gained with the Bloodsail Buccaneers is shown for a level 60 non-human. The amount lost for killing a citizen cannot be shown as it depends on your current level with Booty Bay and the importance of the person you kill. In addition to this what ever you lose with Booty Bay you will lose half of that in the other three goblin towns so if you lose 25 points in Booty Bay you will lose 12.5 points in [faction=470].\n\n[ul]\n[li][npc=4624]: 25 rep gained[/li]\n[li][npc=15088]: 25 rep gained[/li]\n[li][npc=2496]: 5 rep gained[/li]\n[li][npc=2636]: 5 rep gained[/li]\n[li][url=?npcs&filter=cr=3;crs=21;crv=0]Many more NPCs[/url]![/li]\n[/ul]\n\nThe fastest way to increase you reputation with the Bloodsail Buccaneers is to kill Booty Bay Bruisers. At first it may seem a simple task as the guards don\'t appear as threatening as the other monsters a player faces within the game. However, the guards are highly equipped to neutralize players of any class, to prevent people from attacking each other while in the town. What gives the Booty Bay Bruiser the advantage is several factors, one of them being their ability to use nets to lock you in place, preventing you from escaping. Another is the fact that they spawn every time you attack a citizen of the city or if you’re under Unfriendly status with Booty Bay the Bruisers can spawn if you enter a building, because of this players can soon find them selves swarmed by Bruisers.\n\nYet, theses are just the minor problems, in comparison to the Bruiser’s strongest ability, once it pulls out its gun its unlikely you will live, if you do not escape fast enough. Each time a guard shoots you, the attack throws you back, much like an Ogre hammer attack; the difference here is that the Bruiser can shoot in quick succession causing chain throw backs. A player can literally be thrown from one side of the town to the other, preventing you from attacking. More often you will find your self being forced into a corner, unable to move and unable to attack with each spell being interrupted by the Bruiser’s attack. Because the Bruisers do not put their guns away once they are out, the best course of action is to run away. \n\nThrough trial and error most people have discovered a safe place to kill Booty Bay Bruisers. If you follow the tunnel leading into the town, the path to your left that leads to the Blacksmith house is the ideal place to kill the guards. Only two guards patrol this path and normally don’t pass each other that closely, allowing both to be dispatched separately. Once they are gone, one can simply enter the first build on the path to cause a guard to spawn if they are below Unfriendly, if not they can simply attack one of the two NPC in the build, both of which are not high in level. Doing this a player should be able to kill 2 to 4 Bruisers before the two patrolling Bruisers re-spawn. On average a player doing this can kill about 30 to 40 Booty Bay Bruisers gaining about 800 reputation points with the pirates. The Bruisers here don’t appear to pull out their guns, but if you find your self in a bad situation, you can jump over the railing running along the path to the waters below, to escape.\n\n[h3]Rewards[/h3]\nBecoming friendly with the Bloodsail Buccaneers will grant you access to the following items:\n\n[ul]\n[li][item=12185] - Summons a [npc=11236][/li]\n[li][item=22742][/li]\n[li][item=22743][/li]\n[li][item=22745][/li]\n[/ul]\n\nYou will need Honored with the Bloodsail Buccaneers for [achievement=2336].',NULL),(8,92,0,'[b]Gelkis[/b] are a tribe of centaur who have made their home in the southmost parts of [zone=405]. They are mortal enemies of the [faction=93], a brother tribe also located in southern Desolace. The founding leader, or Khan, of the Gelkis was [npc=13741], second of the alleged offspring of Zaetar and Theradras. They are presently lead by [npc=5602] and the clan representative [npc=5397]. \n\nThe Gelkis hold no alliance with their brother tribes, but have been known to act both hostile and passive towards members of the Alliance and Horde.\n\n[h3]History[/h3]\nOriginally lead by the Second Khan Gelk, the Magram situated themselves in the southernmost regions of Desolace when the centaur divided into five tribes and have remained there ever since. \n\nWhen the Gelkis tribe spoke out against Khan Magra of the Magram\'s notion that strength was essential and the tribe’s survival depended on their fighting spirit, arguing that Theradras always watches over the centaur and will keep the tribes safe and alive, an eternal feud between the two tribes was born. \n\nAs such the Gelkis are more civilized - or as close as centaur can come to civilized - than their brethren, with an organised social structure and a firm grasp of the Common tongue. While the Magram only respect strength, the Gelkis respect nature and their birthmother Theradras, calling upon her protection and the power of earth to maintain their existence. Though the Magram view this as weak it would seem to be an erroneous view, as Earth Elementals can be sighted in Gelkis Village, putting an end to unwelcome intruders alongside their centaur masters.\n\n[h3]Reputation[/h3]\nOne of the two factions situated in Desolace, you are required to have a certain amount of reputation with the Gelkis in order to start their quests. Reputation for the Gelkis can be gained by killing [url=?npcs=7&filter=na=Magram]Magram monsters[/url]. When killing Magram monsters, you gain 20 reputation with Gelkis and lose 100 with the Magram tribe.',NULL),(8,93,0,'[b]Magram[/b] are a tribe of centaur who have made their home in the southeastern parts of [zone=405]. They are mortal enemies of the [faction=92], a brother tribe also located in southern Desolace. The founding leader, or Khan, of the Magram was [npc=13740], third of the alleged offspring of Zaetar and Theradras. They are presently lead by [npc=5601] and the clan representative [npc=5398]. \n\nThe Magram hold no alliance with their brother tribes, but have been known to act both hostile and passive towards members of the Alliance and Horde.\n\n[h3]History[/h3]\nOriginally lead by the Third Khan Magra, the Magram situated themselves against the mountain ranges of Desolace when the centaur divided into five tribes and have remained there ever since. \n\nBefore the death of Magra, he installed the idea that strength was essential and the tribe’s survival depended on their fighting spirit. When their brother tribe of Gelkis centaur spoke out against this notion, arguing that Theradras always watches over the centaur and will keep the tribes safe and alive, an eternal feud between the two tribes was born. \n\nThe life-long pursuit of strength has carried on through the Khans of Magram to this day, turning them violent and determined. To solidify their title as the strongest the tribe still fights fiercely to weaken or destroy their brother clans, viewing the Kolkar as weak, the Gelkis as nothing more than a nuisance, and the Maraudine as a formidable enemy. \n\nIt can be assumed that the Magram’s culture has developed into revolving around strength worship above all else. When compared to the Gelkis, the Magram hold very primitive forms of speech and social structure. For example, their grasp of common is limited and the position of Khan would likely be sought through a death match of sorts.\n\n[h3]Reputation[/h3]\nOne of the two factions situated in Desolace, you are required to have a certain amount of reputation with the Magram in order to start their quests. Reputation for the Magram can be gained by killing [url=?npcs=7&filter=na=Gelkis]Gelkis monsters[/url]. When killing Gelkis monsters, you gain 20 reputation with Magram and lose 100 with the Gelkis tribe.',NULL),(8,270,0,'[b]Zandalar Tribe[/b] trolls have come to Yojamba Isle in [zone=33] in the effort to recruit help against the resurrected Blood God and his Atal\'ai Priests in [zone=19] and in the [zone=1417].\n\n[h3]History[/h3]\nThe Zandalarians were the earliest known trolls, the first tribe from which all tribes originated. Over time two distinct troll empires emerged - the Amani and the Gurubashi. They existed for thousands of years until the coming of the Night Elves, who warred with them and eventually drove both empires into exile. \n\nFollowing the Great Sundering, the defeated Gurubashi grew ever more desperate to eke out a living. Searching for a means to survive, they enlisted the aid of the savage [npc=14834], also known as the Soulflayer. Hakkar grew into a merciless oppressor who demanded daily sacrifices from his devotees, and so in time the Gurubashi turned on their dark master. The strongest tribes (including the Zandalar) banded together to defeat Hakkar and his loyal troll priests, the Atal\'ai. The united tribes narrowly defeated the Blood God and cast out the Atal\'ai... despite their victory, however, the Gurubashi Empire soon fell. \n\nIn recent years the exiled Atal\'ai priests have discovered that Hakkar\'s physical form can only be summoned within the ancient and once-deserted capital of the Gurubashi Empire, Zul\'Gurub. Unfortunately, the priests have met with success in their quest to call forth Hakkar—reports confirm the presence of the dreaded Soulflayer in the heart of the ruins. \n\nAnd so the Zandalar tribe has arrived on the shores of Azeroth to battle Hakkar once again. But the Blood God has grown increasingly powerful, bending several tribes to his will and even commanding the avatars of the Primal Gods— Bat, Panther, Tiger, Spider and Snake. With the tribes splintered, the Zandalarians have been forced to recruit champions from Azeroth\'s varied and disparate races to battle, and hopefully once again defeat, the Soulflayer.\n\n[h3]Reputation[/h3]\nReputation with the Zandalar Tribe is gained from killing trash and bosses in Zul\'Gurub as well as repeatable and special quests which require instance-dropped items to complete. Each full run of Zul\'Gurub gives approximately 2,500-3,000 reputation.\n\nBefore the Burning Crusade, the main reason for gaining reputation with the tribe were the [url=?items=0.6&filter=na=Zandalar]shoulder[/url], [url=?items=0.6&filter=minrl=60;maxrl=60;cr=18:107;crs=4:0;crv=0:to+a+leg+or+head+slot+item]head and leg[/url] slot item enchants. As well, there were popular alchemy and enchanting recipes that many end-game guilds sought after. All rewarded items from the item set within Zul\'Gurub required a set level of reputation.',NULL),(8,349,0,'[b]Ravenholdt[/b] is a guild of thieves and assassins that welcomes only those of extraordinary prowess into its fold. They are diametrically opposed to the [faction=70], and are a rogue-only faction as all quests are rogue-only quests. The exception is the quest [quest=8249], which is available to non-rogues, but they would require the help of a rogue to get the items for the quest. [b]Ravenholdt Manor[/b], the faction\'s headquarters, is located in [zone=36], but to get there you have to come from the northeast corner of [zone=267].\n\n[h3]Reputation[/h3]\nAll Syndicate [url=?search=Syndicate#npcs]humanoids[/url] give 1-5 reputation points per kill depending on your current level. As well, there are a few quests that increase your reputation, but your primary method to raise your reputation is from the repeatable quests for turning in pickpocketed items.\n\nYou start off at 0/3000 Neutral with Ravenholdt, meaning if you kill any Ravenholdt NPCs before raising your reputation by at least 5, you will become Unfriendly and be unable to raise your reputation any higher ever again. To raise your reputation from Neutral to Friendly, the repeatable quest [quest=6701] is available. You will have to turn in 11-12 [item=17124] and once you are Friendly, this quest is no longer an option. From Neutral to Friendly you can also deliver five [item=16885] for Junkboxes Needed.\n\nTo raise your reputation beyond Friendly, the only choice is the repeatable quest Junkboxes Needed. There is no known faction reward for obtaining Friendly, Honored, Revered or Exalted, except that the guards speak to you with more respect. However, Exalted is required to obtain the Feat of Strength [achievement=2336].',NULL),(8,369,0,'[minibox]\n[h2]Steamwheedle Cartel[/h2]\n[faction=21]\n[faction=577]\n[b]Gadgetzan[/b]\n[faction=470]\n[/minibox]\n\n[b]Gadgetzan[/b] is the faction of the city Gadgetzan, which is home to goblinhood\'s finest engineers, alchemists and merchants and is the only spot of civilization in the entire desert. Rising out of the northern [zone=440] desert like an oasis, Gadgetzan is the headquarters of the Steamwheedle Cartel, the largest of the Goblin Cartels. The Goblins believe in profit above loyalty, thus Gadgetzan is considered neutral territory in the Horde/Alliance conflict.\n\n[h3]History[/h3]\nAlthough the goblins\' neutrality is almost universally acknowledged, there are still those who seek to sow chaos and anarchy. For Gadgetzan, this comes in the form of the Wastewander bandits, a gang of miscreants who have occupied the Waterspring Field and Noonshade Ruins of northeast Tanaris. Few goblins care about ancient ruins (unless they have treasure) – for all they care, the bandits can have the old blocks of stone. \n\nHowever, the Waterspring Field is vital to the goblins\' survival in the desert, providing them with the liquid gold of the desert. Water towers out in the field were constructed under the blazing heat of the desert sun by the backbreaking work of their slaves, and by Az, the goblins aren\'t going to give up their hard earned towers that easily. However, the Bruisers need to stay in town to keep the gnomes\' collective Napoleonic-complex from getting out of hand and to stop the seemingly endless dueling among the various visitors from disrupting business. Therefore, it falls to brave mercenaries from all corners of the world to help the goblins in their time of utmost need.\n\n[h3]Reputation[/h3]\nKilling the [url=?npcs=7&filter=na=Southsea]Southsea[/url] and [url=?npcs=7&filter=na=Wastewander]Wastewander[/url] monsters will increase your reputation with the Steamwheedle Cartel. Having a friendly or higher reputation will make the guards help you in case of initiated violence against you. Having an exalted reputation means that the guards will never attack you even if you initiate attacks on the opposite faction.\n\nMost of the quests associated with the Gadgetzan faction are located in Tanaris.\n\nIf you are Hated with Gadgetzan, you can do the repeatable quest [quest=9268] to obtain Neutral.',NULL),(8,470,0,'[minibox]\n[h2]Steamwheedle Cartel[/h2]\n[faction=21]\n[faction=577]\n[faction=369]\n[b]Ratchet[/b]\n[/minibox]\n\n[b]Ratchet[/b], the faction of the city Rachet on Kalimdor’s central east coast in [zone=17], is run by goblins and shows it. Its streets sprawl in every direction, and the architecture shows no consistency or common vision. It is a city of entertainment and trade, where anything that anyone would ever want to buy — and plenty of things that no one ever wants to buy — is on sale.\n\nRatchet is currently run by a corporate group known as the Steamwheedle Cartel a splinter group from the Venture Company, who first built the port town for trading with [zone=1637]. It is initially a neutral faction to both Horde and Alliance. A ferry conveniently connects Ratchet to Booty Bay.\n\n[h3]History[/h3]\nBuilt from equal parts of industry and decadence, the goblin port city of Ratchet sprawls along nearly a mile of of coastline where the eastern Barrens poke between [zone=14] and the [zone=15] to the sea. Ratchet is the pride of the goblins, a trade city where you can find almost anything your heart desires - and if something is not in stock, you can bet the goblins can order it. Ratchet also had regular ferries that traversed the safe though roundabout route to the island stronghold of Theramore to the south.\n\nRatchet is a city where creatures who were once the butt of jokes now reign supreme. Its streets wander without rhyme or reason through neighborhoods dedicated to one activity: commerce. Ramshackle warehouses stand next to stately stone homes. Fine shops press cheek to jowl with rude huts. Wares of every type imaginable - and some beyond the imagination - are on display in markets and in exclusive boutiques.\n\nGoblins welcome anyone with gold or items of value and a willingness to trade them for their wares and services. Merchants throng the marketplaces each day, selling everything from silks to slaves, and even at night the stores lining the twisting streets and alleys remain open for business. Those with the money can listen to skilled musicians while drinking fine ales and eating food prepared by expert chefs. For those with earthier tastes, the streets along the wharf teem with whorehouses, taprooms, and casinos.\n\nRatchet is the largest port on Kalimdor, with as many ships bringing cargo in as there are ships heading out for other sites around Kalimdor. In addition to legitimate trade vessels, pirate craft receive amnesty while in the port of Ratchet as long as they can pay the stiff docking fees. This situation makes many merchant captains furious, but they cannot hope to stay in business if they boycott Ratchet. Moreover, the Lawkeepers and hired mercenaries prowling the waterfront are eager to deal with anyone looking to cause trouble.\n\n[h3]Reputation[/h3]\nMost of the quests to raise reputation with Ratchet and the Steamwheedle Cartel are located in the Barrens. Having a friendly or higher reputation will make the guards help you in case of initiated violence against you.\n\nIf you are Hated with Rachet, you can do the repeatable quest [quest=9267] to get back to Neutral.',NULL),(8,471,0,'The Wildhammers are a clan of dwarves currently centered in the [zone=47] and [zone=3520]. The faction has been removed in patch 2.0.1.\n\n[h3]History[/h3]\n\nJust prior to the [object=175739], the Wildhammer Clan, ruled by Thane Khardros Wildhammer, inhabited the foothills and crags around the base of Ironforge. The Wildhammer Clan was unsuccessful in wresting control of [zone=1537] from the Bronzebeard and Dark Iron clans. Khardros and his Wildhammer warriors traveled north through the barrier gates of Dun Algaz, and founded their own kingdom within the distant peak of Grim Batol. There, the Wildhammers thrived and rebuilt their stores of treasure.\n\n[npc=9019] and his Dark Irons vowed revenge against Ironforge. Thaurissan and his sorceress wife, Modgud, launched a two-pronged assault against both Ironforge and Grim Batol. As Modgud confronted the enemy warriors, she used her powers to strike fear into their hearts. Shadows moved at her command, and dark things crawled up from the depths of the earth to stalk the Wildhammers in their own halls. Eventually Modgud broke through the gates and laid siege to the fortress itself. The Wildhammers fought desperately, Khardros himself wading through the roiling masses to slay the sorceress queen. With their queen lost, the Dark Irons fled before the fury of the Wildhammers.\n\nOnce the immediate Dark Iron threat was eliminated, the Wildhammers returned home to Grim Batol. However, the death of the Modgud had left an evil stain on the mountain fortress, and the Wildhammers found it uninhabitable. Khardros took his people north towards the lands of Lordaeron. Settling within the mountainous region of the Aerie Peaks and The Hinterlands, and lush forests of Northeron, the Wildhammers crafted the city of Aerie Peak, where the Wildhammers grew closer to nature and even bonded with the mighty gryphons of the area. Over time they started calling their land the Hinterlands. \n\n[b]Modern Wildhammers[/b]\nThe Wildhammer Clan currently makes its home at Aerie Peak in the Hinterlands. The most immediate threat to their security comes from the east in the form of the Witherbark Trolls and Vilebranch Trolls. They are most famous for riding into battle atop Gryphons, while wielding powerful Stormhammers.\nWildhammer dwarves have a number of clans, each ruled by a Thane. The strongest Thane rules Aerie Peak.',NULL),(8,509,0,'[b]The League of Arathor[/b] was originally established by the survivors of the Kingdom of Stromgarde to reclaim the [zone=45] from the hands of the Forsaken Defilers in Hammerfall. Today it is an organization in support of the Alliance, based out of the [zone=3358] in Refuge Pointe. They have taken it upon themselves to help supply the Alliance forces where needed, and their members include all manner of Alliance races - even though they are still predominantly Stromgardian humans.\n\n[h3]Reputation[/h3]\nPlayers can earn reputation in this faction by participating in the Arathi Basin battleground. When you fight in Arathi Basin you earn 10 reputation per 160 resources. On Arathi Basin holiday weekends the required resources is reduced to 150.\n\nYou are granted the player title [title=48] once exalted with League of Arathor and the other two battleground factions, [faction=890] and [faction=730].',NULL),(8,510,0,'[b]The Defilers[/b] seek to foil the [faction=509] in the [zone=3358] battleground. Today it is an organization in support of the Horde, based out of Hammerfall in [zone=45]. They have taken it upon themselves to help supply the Horde forces where needed, and their members include all manner of Horde races - even though they are still predominantly orcs.\n\n[h3]Reputation[/h3]\nReputation is gained through participation in the Arathi Basin battleground. When you fight in Arathi Basin you earn 10 reputation per 160 resources. On Arathi Basin holiday weekends the required resources is reduced to 150.\n\nYou are granted the player title [title=47] once exalted with the Defilers and the other two battleground factions, [faction=889] and [faction=729].',NULL),(8,529,0,'The [b]Argent Dawn[/b] is an organization focused on protecting Azeroth from the threats that seek to destroy it, such as the Burning Legion and the Scourge. Strongholds of the Argent Dawn can be found in the [zone=139] and [zone=28]. It also maintains a presence in [zone=1657] and in the [zone=85], among other less notable areas. Reputation with the Argent Dawn can be used to purchase various profession recipes, misc. consumables, and to mitigate the cost of attunement to [zone=3456]. With the expansion of the Burning Crusade, Argent Dawn reputation has decreased in value.\n\nArgent is Latin for silver, which could explain why the [item=22999] has an icon of a silver sun rising.[h3]History[/h3]After the death of the [npc=16062], the corruption of the Scarlet Crusade became apparent to some of its members, who subsequently left the ranks of the [url=?search=scarlet+crusade#M0z]Scarlet Crusade[/url] and established the Argent Dawn to protect Azeroth from the threat of the Scourge without the blind zealotry present in the Scarlet Crusade.\n\nWhile they share the same goals as the Crusade, the Argent Dawn has opened its ranks to not only other Alliance races besides Humans, but also members of the Horde and even some of the Forsaken. They caution discretion and introspection, and put a lot of emphasis on researching the Scourge and how to combat them.\n\nWith time the Argent Dawn has grown diversified, and like its progenitor — the Scourge — has split again, with an offshoot called the [url=?search=brotherhood+of+the+light]Brotherhood of the Light[/url], a compromise between the Argent Dawn\'s more scholarly approach and the Scarlet Crusade\'s fanaticism.\n\n[h3]Reputation[/h3]\n[b]Scourgestones[/b]\nWhile wearing a trinket granting the Argent Dawn Commission effect, characters can loot [url=?items=12&filter=na=scourgestone]scourgestones[/url] from undead monsters they\'ve killed, and subsequently turn them in in exchange for [item=12844]. These turn-ins require various numbers of [item=12843], [item=12841], and [item=12840]. It should be noted that the token items received from the turn-ins should be saved until after Revered status is reached, as the quest turn-ins will no longer grant reputation after this point.[pad][b]Cauldrons[/b]\nAnother way to gain reputation with the Argent Dawn is through repeatable \"Cauldron\" quests. The Cauldrons are a source of \"undeathness,\" that contribute to the Scourge\'s numbers.[pad][b]Instances[/b]\nLike most factions, the player can run instances to increase his reputation. These instances are [zone=2017] and [zone=2057]. Naturally, these instances also include quests that will raise Argent Dawn reputation, as well as include Scourgestone drops.',NULL),(8,530,0,'[b]Darkspear Trolls[/b], the tribe of exiled trolls that has joined forces with [npc=4949] and the Horde. They now call [zone=1637] their home, which they share with their orc allies. [npc=10540] is their current leader.\n\n[h3]History[/h3]\nAs tribal rivalries erupted throughout the former Gurubashi Empire, the Darkspear Tribe found themselves driven from their homeland in [zone=33]. Having settled in what are believed today to be the Broken Isles, the tribe soon found themselves entangled in a conflict with a band of murlocs. Their fate seemed sealed until the orcish Warchief Thrall and his band of newly freed orcs took shelter on their island home. Controlled by a Sea Witch, a group of rampaging murlocs captured the Darkspears\' leader Sen\'jin, along with Thrall and several other orcs and trolls. Thrall managed to free himself and others, but was ultimately unable to save the trolls\' leader. Although Sen\'jin was sacrificed to the Sea Witch, he was able to reveal a vision he had in which Thrall would lead the Darkspear from the island. \n\nAfter returning to the island, Thrall and his followers managed to fend off further attacks by the Sea Witch and her murloc minions, and set sail for Kalimdor once again. Under the new leadership of [npc=10540], the Darkspear swore allegiance to Thrall\'s Horde and followed him to Kalimdor. Now considered enemies by all other trolls except the Revantusk and the Zandalari, the Darkspear are held in contempt to this day. Yet, the Darkspear have not forgotten being driven from their ancestral homes and this animosity is eagerly returned, especially towards the other jungle trolls. Having reached the orc\'s new homeland, [zone=14], the trolls carved out another home for themselves - this time among the Echo Isles on the eastern shores of the new orc kingdom. \n\nHowever, with the coming of Kul Tiras and its navy, the Darkspear were forced to retreat inland under the onslaught of the misguided commander [npc=177201]. The trolls, fighting alongside their horde brethren, defeated the enemy and reclaimed their new homeland. Shortly thereafter, a witch doctor by the name of [npc=3205] began using dark magic to take the minds of his fellow Darkspear. As his army of mindless followers grew, Vol\'jin ordered the free trolls to evacuate, and Zalazane took control of the Echo Isles. The Darkspear have since settled on the nearby shore, naming their new village after their old leader, Sen\'jin. From Sen\'jin Village they, along with their allies, send forces to battle Zalazane and his enslaved army.\n\n[h3]Reputation[/h3]\n[npc=14727] has the repeatable cloth reputation quests. As a reward for being exalted with the Darkspear Trolls, non-troll Horde players are able to ride [url=?items=15.5&filter=na=Raptor;cr=93:92;crs=2:1;crv=0:0]raptors[/url].\n\nSurrounding zone Durotar contain the most quests for gaining reputation with the Darkspear Trolls. As well, higher level players with the Burning Crusade also have a good amount of quests in [zone=3521].',NULL),(8,576,0,'As the last uncorrupted furbolg tribe (at least in their view), the [b]Timbermaw[/b] seek to preserve their spiritual ways and end the suffering of their brethren.\n\nThe Timbermaw Furbolgs inhabit two areas: [zone=16] and [zone=361]. They are presumed to be the only furbolg tribe to escape demonic corruption, though this may not be true due to the existence of [npc=3897], an uncorrupted furbolg of unknown tribe, and the Stillpine tribe on [zone=3524] in Burning Crusade. However, many other races kill furbolg blindly now, without bothering to see if they are friend or foe. For this reason, the Timbermaw furbolg trust very few.\n\nAdventurers who seek out Timbermaw Hold in northern Felwood and prove themselves as friends of the Timbermaw will learn that the furbolgs value their friends above all else. Though they possess no fine jewels or any worldly riches, the Timbermaw\'s shamanistic tradition is still strong. They know much about the art of crafting armors from animal hides, and they are more than happy to share their healing/resurrection knowledge with friends of their tribe. Besides, any reputation above Unfriendly will also grant you untroubled access to [zone=493] and [zone=618] through their tunnels.\n\n[h3]Reputation[/h3]\nReputation with the Timbermaw Hold faction is mainly gained through quests and killing in Felwood. The members of the Deadwood Tribe, another Furbolg tribe in Felwood, are the Timbermaws\' main enemies.\n\n[ul]\n[li]Killing one [url=?npcs&filter=na=Winterfall]Winterfall[/url] or [url=?npcs&filter=na=Deadwood]Deadwood[/url] Furbolg gives 10 reputation points. Gains stop at revered; Deadwoods give 2 reputation point at honored.[/li]\n[li]Killing either one of the Deadwood Bosses [npc=9464] or [npc=9462], is worth 60 reputation. There is no reputation limit.[/li]\n[li]Killing the elite Winterfall Furbolg, [npc=10738], located in a cave east of [faction=577], awards 50 reputation. There is no reputation limit, and his respawn rate is 6 to 8 minutes.[/li]\n[li]Killing the named rare mob [npc=14342] is worth 50 reputation. He is a rare spawn at Deadwood Village in Felwood and there is no reputation limit for this mob.[/li]\n[li]Killing the named rare mob [npc=10199] is worth 50 reputation. He is a rare spawn at Winterfall Village in Winterspring. Killing him will grant reputation up until Revered.[/li]\n[li]After completing [quest=8460], turning in 5 [item=21377] yields 150 reputation.[/li]\n[li]After completing [quest=8464], you will be able to turn in [item=21383] collected from furbolgs in Winterspring. Turning in 5 beads at [npc=11556] yields 150 reputation.[/li]\n[/ul]',NULL),(-13,0,0,'[menu tab=2 path=2,13,0]One of many useful features is the user-submitted comment system. This system allows users to submit their own comments to augment the data provided here. As a rule, we promote the submission of informative comments, but we also like to see the occasional joke. Moderators and users alike will apply positive and negative ratings to comments in an effort to promote the useful ones and purge unnecessary information.\r\n\r\nWith that in mind, below is a guide that can be used to determine how your comment will likely be received by the community. \r\n\r\n[pad]\r\n\r\n[tabs name=comments]\r\n\r\n[tab name=\"Before you post\"]\r\n\r\n[ul]\r\n[li][b]Read existing comments[/b] – Sometimes, the information you have may already have been posted by another user. In this case, if the information is useful, the existing comment should be given a positive rank. Posting information that was already added in a previous comment will likely result in a negative rating.[pad][/li]\r\n[li][b]Verify your facts[/b] – Make sure that what you have to post is true. A friend might tell you that a mob is immune to Frost Nova, but unless you verify that yourself, you could be posting a potentially misleading comment.[pad][/li]\r\n[li][b]Temporary usability[/b] – If you want to correct invalid or missing information on a page, keep in mind that your comment may go from a positive ranking to a negative ranking when the correction occurs. For example, informing the community that a spell is cast by Illidan Stormrage before that data has been collected will be useful at first, but once Aowow learns to parse that information and adds it to the \'Abilities\' tab, your comment becomes redundant. If you do not want to worry about the comment or do not want one of your comments to be rated negatively, consider informing us in the [url=/?forums&board=1.]Site Feedback[/url] forum. The moderation staff will be happy to add a comment to correct invalid or missing information on the page for you. Alternatively, you can delete your comment later when it becomes redundant.[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[tab name=\"Comment ratings\"]\r\n\r\n[h3][color=q2]Positive (+1)[/color][/h3]\r\n[ul]\r\n[li][b]Corrections on drop percentages[/b] – There are many instances where drop percentages will be inaccurate. For example, quest items do not drop for people who do not have the quest, so their drop percentages will be low. Also, mobs that periodically do not drop loot when they die won\'t count against the drop percentages, so these mobs may appear to have higher drop rates for some items.[pad][/li]\r\n[li][b]Strategies[/b] – If you have a strategy that can assist other users in completing a quest or defeating a mob, by all means, share![pad][/li]\r\n[li][b]Quest coordinates[/b] – Providing coordinates for the location of quest items or mobs is always useful. When possible, you should provide links to quest targets as well.[pad][/li]\r\n[li][b]Theorycrafting[/b] – We encourage users to post any information they have regarding complex calculations they may have performed to, for example, prove one item has a higher DPS than another given certain abilities.[pad][/li]\r\n[li][b]Just for laughs[/b] – If your comment is one that would be universally funny (i.e. not an inside joke), post away. We like to laugh as much as anyone else. Of course, whether your joke is funny or not is subject to our other users. :)[/li]\r\n[/ul]\r\n\r\n[h3][color=q10]Negative (-1)[/color][/h3]\r\n[ul]\r\n[li][b]Redundant information[/b] – For instance, a comment that says \"Dropped by Ragnaros\" does not add anything to the page as that information can be viewed in the \"Dropped By\" tab of the page in question.[pad][/li]\r\n[li][b]Soloed by:[/b] Unless your comment contains a detailed explanation of how you defeated a mob, these comments do not add anything to the page. Simply stating your level, class, and that you soloed the mob by using a few skills is not enough to be useful.[pad][/li]\r\n[li][b]Dropped in X kills[/b] – Telling users that you were lucky enough to get the crusader enchant in one drop is not considered useful information.[pad][/li]\r\n[li][b]NPC/Object coordinates[/b] – The coordinates for NPC or mobs are already supplied in convenient maps within the interface.[pad][/li]\r\n[li][b]Best X before level Y[/b] – Simply posting that an item is the best twink weapon or the best dagger for a rogue is not helpful unless you can back up that claim with facts.[pad][/li]\r\n[li][b]HUNTAR WEPPON[/b] – While it would be acceptable to explain why you feel a certain class with a certain spec would gain the most benefit from an item, simply stating that you feel the weapon should always go to a hunter in a raid will result in negative moderation.[pad][/li]\r\n[li][b]Confirmed![/b] – Adding a comment that simply indicates that you have confirmed a comment left by someone else clutters the comments. The best way to confirm a comment as correct is to give it a positive ranking. A comment with a high ranking will indicate to users that many people think it is useful data.[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[tab name=Deletion]\r\n\r\nAny comment that does not abide by the same [forumrules] will be deleted by a moderator.\r\n\r\n[/tab]\r\n\r\n[/tabs]',NULL),(-13,5,0,'[menu tab=2 path=2,13,5]Can\'t find the answer you were looking for? Just [url=/?aboutus#contact]contact us[/url], or post on our [url=/?forums&board=1]forums[/url]! \r\n\r\n[pad]\r\n\r\n[tabs name=compare]\r\n\r\n[tab name=\"General usage\"]\r\n\r\n[h3]Basic Controls[/h3]\r\n\r\n[ul]\r\n[li][img src=STATIC_URL/images/icons/save.gif border=0] [b]Save[/b] – Saves the comparison so that you may continue browsing the site without losing it. When you click on the [b]Compare[/b] button found throughout the site you will be given the option to add to your saved comparison.[/li]\r\n[li][img src=STATIC_URL/images/icons/refresh.gif border=0] [b]Autosaving[/b] – Indicates that you are viewing your saved comparison, and that any changes you make will automatically be saved. To avoid modifying your saved comparison, you may click on Link to this comparison before making any changes.[/li]\r\n[li][img src=STATIC_URL/images/icons/link.gif border=0] [b]Link to this comparison[/b] – Provides a link to a new page with the current item comparison already there! Useful for showing friends your item comparisons.[/li]\r\n[li][img src=STATIC_URL/images/icons/delete.gif border=0] [b]Clear[/b] – Removes all items, groups, and weights from the comparison tool, giving you a clean slate to work with. [b]This will [u]delete[/u] your saved comparison if used while autosaving.[/b][/li]\r\n[li][img src=STATIC_URL/images/icons/add.gif border=0] [b]Weight scale[/b] – Allows you to add one or more weight scales to the item comparison using your own weights or one of our predefined presets. Each weight scale can have its own name. A saved comparison also contains the weight information, allowing you to store custom weight scales for future use.[/li]\r\n[li][img src=STATIC_URL/images/icons/add.gif border=0] [b]Item[/b] – Opens a live search that displays item suggestions as you type the name of an item. Clicking on a suggestion will add that item to your comparison.[/li]\r\n[li][img src=STATIC_URL/images/icons/add.gif border=0] [b]Item set[/b] – Opens a live search that displays item set suggestions as you type the name of an item set. Clicking on a suggestion will add all of the items in that set to your comparison.[/li]\r\n[/ul]\r\n\r\n[h3]Adding Items[/h3]\r\n[div float=right align=right][img src=STATIC_URL/images/help/item-comparison/addingitems.gif]\r\n[small]Some of the ways to add items to a comparison.[/small][/div]The comparison tool is fully integrated with our site and designed to be as convenient as possible to work with. There are many ways to add items to a comparison depending on what part of the site you are on: \r\n[ul][li]Using the [url=/?compare]item comparison tool[/url] itself, you may add items or item sets using the links in the top right corner as described above.[/li]\r\n[li]Viewing an [url=/?item=35137]item[/url] or [url=/?itemset=-17]item set[/url] page, you may click on the red [b]Compare[/b] button near the Quick Facts box.[/li]\r\n[li]Viewing [url=/?items=4.2&filter=sl=8]search results[/url] or [url=/?npc=34077#sells]any page with a list of items[/url], checkboxes are displayed next to items which can be equipped. You may select one or more items and click the [b]Compare[/b] button at the top of the list.[/li][/ul]\r\n\r\n[i]Note: If you have a comparison saved, and you add items to your comparison from elsewhere on the site, you will be given the option to add them to your saved comparison or create a new one. If you don\'t have a saved comparison, a new comparison will automatically be created and saved with the selected items.[/i]\r\n\r\n[h3]Managing Your Items[/h3]\r\n[div float=right align=right][img src=STATIC_URL/images/help/item-comparison/newgroup.gif]\r\n[small]Creating a new group by dragging an item.[/small][/div]\r\n[ul][li][b]Creating a new group[/b] – [u]Drag an item into the empty column[/u] on the right to create a new group containing that item.[/li]\r\n[li][b]Moving[/b] – To move an item or group, click on the item (or the group\'s control bar) and [u]drag it to the desired position[/u].[/li]\r\n[li][b]Copying[/b] – [u]Holding shift while dragging[/u] an item or group will make a copy of it when it is dropped.[/li]\r\n[li][b]Deleting[/b] – Items and groups can be deleted by [u]dragging them out of the row[/u]. Groups may also be deleted by clicking the X on the right side of the group\'s control bar.[/li]\r\n[li][b]Deleting all but one group[/b] – [u]Holding shift while deleting a group[/u] (see above) will cause all other groups to be deleted instead of that one.[/li]\r\n[li][b]Splitting a group[/b] – Groups of 2 or more items can be split by [u]clicking on [b]Split[/b] in the menu dropdown[/u] on the group\'s control bar. This will create a new group for each item in the current group.[/li]\r\n[li][b]Exporting a group[/b] – [u]Clicking on [b]Export[/b] in the menu dropdown[/u] of the group\'s control bar will take you to a new comparison containing only the current group.[/li]\r\n[li][b]Item Enhancements[/b] - To add gems or enchantments to an item, [u]right-click on the item icon at the top[/u], then select the desired option from the menu.  The stats will automatically update—including the set bonuses.[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[tab name=\"Advanced features\"]\r\n\r\n[h3]Level Adjustments[/h3]\r\nYou can select your desired character level from the dropdown at the top left.  When you do, all the statistics that change according to your level (including combat ratings and heirloom item stats) will automatically adjust to the corresponding value for the level you\'ve entered.\r\n\r\n[h3]Gains[/h3]\r\nAt the bottom of the item comparison is a special row called \'Gains\'. The gains row calculates the minimum values of all stats that appear in any group in the item comparison. It then displays the bonuses each row has [b]above[/b] this minimum.\r\n\r\nFor example, the minimum stamina for any group in [url=/?compare=35031;35030;35029;35028;35027]this comparison[/url] is 50. The gains row displays nothing for the items which have 50 stamina, +23 sta for the item with 73 stamina, and +27 sta for the items with 77 stamina.\r\n\r\nBasically, the gains row removes the shared stats between all groups so that you can focus on what each group brings to the table.\r\n\r\n[h3]Focus Group[/h3]\r\n\r\n[screenshot url=STATIC_URL/images/help/item-comparison/focus2.gif thumb=STATIC_URL/images/help/item-comparison/focus.gif float=right]Comparing arena sets of the first four PvP\r\nseasons using a focus group.[/screenshot]Setting a focus group is done by clicking on the eye icon in the group\'s control bar. Selecting a group as your focus will update the display of the item comparison to show the difference in stats between all other groups and the focus group.\r\n\r\nWhen a focus is set, the focus group is highlighted and each other group has numbers that indicate the stats gained or lost in comparison to the focus group.\r\n\r\n[b][color=q2]Positive[/color][/b] numbers indicate that group has a higher total for a given stat than the focus group, while [b][color=q10]negative[/color][/b] numbers indicate that group has a lower total for a given stat than the focus group. \r\n\r\n[h3]Stat Weighting[/h3]\r\nTo add a weight scale to your comparison, click on the [b]Add a weight scale[/b] link in the top right corner. You may select a weight scale from our predefined presets or create one of your own. Each weight scale may be given a name that will appear in the score tooltips to help differentiate the different scores. You may add as many weight scales as you like.\r\n\r\nTo remove a weight scale, click on the [b]X[/b] next to the appropriate score in any group. To toggle between normalized (default), raw, and percent score mode, click on the score in any group.\r\n\r\nUnlike the weighted item search, these weight scales [b]do not[/b] automatically select gems or include socket bonuses in the score at this time.\r\n\r\n[h3]Viewing a Group in 3D[/h3]\r\nClick on [b]View in 3D[/b] in the menu dropdown of the group\'s control bar to display a 3D model of the items and select the race and gender to display them on. Of course, items which do not have models, such as trinkets and rings, will not be displayed.\r\n\r\n[/tab]\r\n\r\n[/tabs]',NULL),(-13,3,0,'[menu tab=2 path=2,13,3]Can\'t find the answer you were looking for? Just [url=/?aboutus#contact]contact us[/url], or post on our [url=/?forums&board=1]forums[/url]! \r\n\r\n[pad]\r\n\r\n[tabs name=weights]\r\n\r\n[tab name=FAQ]\r\n\r\n[h3]How do weights work?[/h3]\r\nThe weighting system allows you to give a weight value to attributes that matter to you and applies your ratings to items in your search results. Each weight value is multiplied by an item\'s stat points and then added together to get the item\'s total score. This score is used to sort the results and display the highest scoring items.\r\n\r\nIf you decide that spell damage is worth twice as much as spell crit, you could add the weights as 2 and 1, 100 and 50, or any other numbers with the same ratio.\r\n\r\nPlease note that weights only work for [url=/?items=4]Armor[/url], [url=/?items=2]Weapons[/url], [url=/?items=3]Gems[/url] and [url=/?items=0]Consumables[/url]. \r\n[h3]What is the difference between weights and equivalency?[/h3]\r\nThe equivalency of two attributes describes how much one equals the other. You may find equivalency ratings that say something like 1 agility = 1.5 strength. This is [b]not[/b] the same as weight values; in fact, it\'s the exact opposite! Equivalency describes the ratio of the stats to each other, which can be used to derive the stat weights. In this example, an appropriate set of weights might be agility 3 and strength 2; this works out to agility being [i]1.5 times as valuable[/i] as strength. \r\n[h3]Is there a way to save a template that I have created?[/h3]\r\nThere sure is! You can save your stat weighting scales by going to the \'Preset\' dropdown menu, selecting \'custom,\' and then filling in your own weights. After you\'ve modified them to your liking, you can hit \'Save\' to give them a name so they can be used for future searches as well.\r\n\r\nWeights also carry over from one item list to another if you use the database menu, so going from a [url=/?items=2&filter=wt=51:48:49;wtv=83:67:58]weighted list of weapons[/url] to the [url=/?items=4&filter=wt=51:48:49;wtv=83:67:58]cloth armor listing[/url] will also maintain your current weight scale. \r\n[h3]Is it better to match sockets and gain the socket bonus, or use the best gems?[/h3]\r\nThe weighting system answers this for you automatically. It compares the score of matching gems plus the score of the socket bonus, to the score of the best gems it could put in that item. It will automatically put in the gems that result in the highest net rating, taking socket bonuses into account. When the socket colors are matched, the socket bonus text will be listed below the gems for each item. \r\n\r\n[h3]What are the default weight presets based on?[/h3]\r\nWe\'ve done a great deal of research, tracking down equivalence points for all of the classes. We\'d like to thank all of the hard-working theorycrafters at [url=http://elitistjerks.com/f47/t21302-theorycrafting_think_tank/]Elitist Jerks[/url], [url=http://forums.tkasomething.com/showthread.php?t=9542]TKA Something[/url], [url=http://shadowpanther.net/aep.htm]Shadow Panther[/url], [url=http://druid.wikispaces.com/Healing+Gear+List]The Druid Wiki[/url], [url=http://www.emmerald.net/]Emmerald[/url], [url=http://www.lootrank.com/wow/templates.asp]Lootrank[/url], [url=http://pawnmod.trenchrats.com/index.php]Pawn Mod[/url], and [url=http://www.codeplex.com/Rawr]Rawr[/url], as well as a host of threads on the World of Warcraft forums. They provided the inspiration for the weighted search and a starting point for our preset values.\r\n\r\n[/tab]\r\n\r\n[tab name=\"Helpful tips\"]\r\n\r\n[ul]\r\n[li]You can help us [b]improve[/b] our presets! Email your suggestions to [feedback].[/li]\r\n[li]Don\'t weight stats that your character is [b]already capped on[/b] (e.g. Hit rating). Be sure to tweak the presets as needed![/li]\r\n[li]You can adjust a preset by clicking on the \'show details\' button.[/li]\r\n[li]Once you have generated a weighting you like, you can bookmark that page. Then, if you browse around on other pages using the menus at the top, your weight scale will be applied to that page as well.[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[tab name=Why?]\r\n\r\n[h3]Why does it give a higher score to 2H weapons over 1H weapons, when using a 1H + OH is better?[/h3]\r\nThe scores are based off the stat weights of the item by itself. Two-handers rank higher because by themselves they do have better stats than a one-hander with nothing else in the off hand. If you add up the scores of a main hand and off hand item, the total score is what you should use to compare to that of a two-hander. We do not assume a score for your offhand item, as there is no way of knowing what you have or can obtain for that slot unless you do a weighted search for it. \r\n[h3]Why does the preset list X as more important than Y?[/h3]\r\nSome attributes come in unusual value ranges on items, which affects their equivalency to other stats. It does not mean that your should focus on or ignore that stat, but that a single point of it is worth more or less compared to other stats. Stats with high number ranges (armor, weapon damage, penetration, etc) will require smaller weight values, while stats with low number ranges (mana regeneration) will require much larger weight values.\r\n\r\nIn essence, giving mana regeneration a score of 100 and healing a score of 25 does [b]not[/b] say that mana regeneration is more important than healing, simply that each point of mana regeneration is the equivalent of 4 points of healing.\r\n[h3]Why don\'t you have a preset for PvP/Tier 6 Raiding/...? Why doesn\'t your preset give a stat value for X?[/h3]\r\nIf you would like to suggest changes to the existing presets or new presets for other specs or situations, please do so to [feedback]. \r\n[h3]Why doesn\'t the preset limit the items to X, Y, and Z?[/h3]\r\nThe weight presets are for sorting; filters are for limiting the search results. If you want to restrict the items you see, use the appropriate tool - the filter options. The only limit applied by the weight scales is that it will not display items with a score of 0 or less. You should continue to use the existing filtering system if you want to see items of a specific type, slot, source, speed, etc.\r\n[h3]Why does it suggest the gems it does for the sockets?[/h3]\r\nThe suggested gems are based on your weights. If you would like to see a different gem in the sockets, try increasing the weight of the appropriate stat. If you feel the weights in the presets need to be adjusted, please let us know at [feedback].\r\n\r\n[/tab]\r\n\r\n[/tabs]',NULL),(-13,2,0,'[menu tab=2 path=2,13,2]\r\n\r\nWe thrive on user contributions! Quest data, database comments, forum posts - you name it, we love it! One of our favorite methods of contribution is via uploaded [b]screenshots[/b], images depicting various items, NPCs or quest details in the World of Warcraft. Users can submit screenshots to any database page which will then be reviewed by our staff and, upon approval, added to a database page! Taking and uploading screenshots is easy!\r\n\r\n[small]The information below is graciously provided by [url=http://us.blizzard.com/support/article.xml?locale=en_US&articleId=21048]Blizzard Support[/url].[/small]\r\n[h3]Taking Screenshots on Windows[/h3]\r\n[ul]\r\n[li]While in the game, press the Print Screen key on your keyboard.[/li]\r\n[li]You should see a \"Screen Captured\" message.[/li]\r\n[li]The screenshot will appear as a .JPG file in the Screenshots folder, in your main World of Warcraft directory.[/li]\r\n[li]You should be able to double click on the screenshot files to view the screenshots in Windows default image viewer.[/li]\r\n[/ul]\r\n\r\n[b]Extra notes for Windows Vista users[/b]\r\n[ul]\r\n[li]Due to extra security on the system the screenshots will be saved to the following folder:C:\\\\users\\\\*your user name*\\\\AppData\\\\Local\\\\VirtualStore\\\\Program Files\\\\World of Warcraft\\\\Screenshots[/li]\r\n[li]You may also have to turn on the ability to view hidden files as the AppData folder may be hidden.\r\n[ul]\r\n[li]Click the Start/Window button, select Control Panel, Appearance and Personalization, Folder Options.[/li]\r\n[li]Next click on the View tab, under the Advanced settings, click Show hidden files and folders, and click OK to finish.[/li]\r\n[/ul][/li]\r\n[/ul]\r\n\r\n[h3]Taking Screenshots on Mac[/h3]\r\n[ul]\r\n[li]Players can take a screenshot in-game using the keyboard key bound to the Print Screen functionality.[/li]\r\n[li]If you have a keyboard with an F13 key, press the key to take an in-game screenshot. Players without an F13 key on the keyboard can change the default Screen Shot key in the Key Bindings menu.[/li]\r\n[li]You should see a \"Screen Captured\" message.[/li]\r\n[li]The screenshot will appear as a JPEG file in the Screenshots folder, in your main World of Warcraft folder.[/li]\r\n[/ul]\r\n\r\nRemember to turn off your in-game UI using the Alt+Z (or ⌘+V) command! Upon taking your screenshot, you can then go in and use an image editor (such as the free program [url=http://www.getpaint.net]Paint.NET[/url]) to crop your image for faster upload. You can select specific sections of a screenshot to upload (if you are featuring a particular piece of armor, for example) and save the file, then simply upload your pre-cropped image directly! If not, you can easily crop your screenshot after uploading but before submitting using our handy tool.\r\n\r\nTo submit a screenshot, simply navigate to the database entry for which you\'ve taken a screenshot and navigate to the \'Contribute\' section. Select the \'Submit a screenshot\' tab and click \'Choose file\' to locate the file on your system. Remember that only PNG and JPG file types are accepted! Once you have selected the screenshot simply click \"Submit\" and you\'re on your way! You will then be able to crop the image if necessary before your image is finally submitted for review. Upon approval (which may take up to 72 hours) your screenshot will then be featured on the database page, as well as in a \'Screenshots\' tab in your user profile!\r\n\r\n\r\n[h2]Quality Tips[/h2]\r\n\r\n[screenshot url=STATIC_URL/images/help/screenshots/hinterlands.jpg thumb=STATIC_URL/images/help/screenshots/hinterlands2.jpg float=right]The Hinterlands[/screenshot]A good screenshot is like a miniature piece of art. It should showcase the main object, but take into account the details around it. The same 7 elements of art design come into play here, Line, Shape, Form, Space, Texture, Light & Color. We\'ll touch on several of these and how to make use of the in game settings and mechanics to enhance your pictures.\r\n\r\nTurn your resolution and color sampling as high as your computer can handle. Turn on all the image effects and details, but turn down the weather effects to the lowest setting. In general you want all your glow and spell effects maxed to really show the environment to its fullest potential (they actually help with the lighting too!) You may find a shot that you need to play with these settings to enhance, sometimes turning down environmental detail is helpful to remove extra grasses.\r\n\r\nWorld of Warcraft actually has an internal setting for screenshot quality, and by default that quality is set to [b]3/10[/b].  You can turn this up, though, in order to take higher quality screenshots.  In order to do so, type this command into your chatbox:\r\n\r\n[code]/console screenshotQuality 10[/code]\r\n\r\nMost of the time taking the pictures from 1st person view works best, so zoom all the way in so that you\'re looking through your character\'s eyes. Occasionally the object might be too big (large NPCs especially) to use this view - if this is the case get as close to them as you can without having your body in the shot and swing the camera around to get the angle that you\'re looking for.\r\n\r\nPay attention to the light - a well lit picture is 10 times better than a dark one. You may even want to do a little color correcting before uploading - increase the brightness and contrast a touch. For instance - it\'s a lot easier to take pictures in sunny Stormwind than deep in the mountains of torch lit Ironforge. Daytime pictures also turn out better than night.\r\n\r\n[h3]Featuring Armor[/h3]\r\n\r\n[screenshot url=STATIC_URL/images/help/screenshots/armor.jpg thumb=STATIC_URL/images/help/screenshots/armor2.jpg float=right]Dreamwalker Spaulders[/screenshot]We want to see the armor! Not Joe Schmoe in the armor. In general you want close ups of the piece itself (except for full set pictures). Don\'t be afraid to submit a 4 inch picture of one glove. Once\'s it\'s cropped and loaded and shrunk down to the thumbnail it will look great!\r\n\r\nUse your best judgment when cropping armor pics, but remember - we want to see details of the armor - not the person or a far away image. Of course, this also applies to weapons or any other piece of equipment!\r\n\r\n[h3]Featuring NPCs[/h3]\r\n\r\n[screenshot url=STATIC_URL/images/help/screenshots/npc.jpg thumb=STATIC_URL/images/help/screenshots/npc2.jpg float=right]Cairne Bloodhoof <High Chieftain>[/screenshot]Full body shots should be the norm. If you can\'t get a good full shot (e.g. they\'re standing behind a counter) get the waist up shot. There\'s no need to include the on-screen text and titles of NPCs. The website already lists those, so just get in close and take a great shot of the NPC itself.\r\n\r\nGet down on their level - you may need to \"/sit\" or even \"/sleep\" to get a good view of something low to the ground (scorpions, boots, spiders, etc.)\r\n\r\nWhen capturing moving NPCs, try to get as much a head on front shot as you can, being willing to take a few hits while you take picture of a mob attacking you can make for a great shot. If you don\'t want to get your hands dirty, sitting in place for a while and waiting for it to path in front of you is often easier and faster than running around it trying to get your shot.\r\n\r\nTalking to friendly NPCs will usually make them face you - you can then spin around and get the best background for your picture. You may also catch them in an interesting motion or gesture.',NULL),(-13,6,0,'[menu tab=2 path=2,13,6]Can\'t find the answer you were looking for? Just [url=/?aboutus#contact]contact us[/url], or post on our [url=/?forums&board=1]forums[/url]!\r\n\r\n[pad]\r\n\r\n[tabs name=profiler]\r\n\r\n[tab name=\"Browsing characters\"]\r\n\r\n[div float=right align=right][img src=STATIC_URL/images/help/profiler/menu.gif]\r\n[small]Navigating the menu to your battlegroup and realm.[/small][/div]We maintain a database of [i]millions[/i] of [url=http://www.wowarmory.com/]Armory[/url] characters, guilds, and arena teams that have been imported by our users. You can browse through this extensive list by visiting the main [url=/?profiles]profiles[/url] page and selecting a region, battlegroup, or realm from the menus at the top.\r\n\r\nThis will give you an unfiltered look at the players and guilds in the area you selected, with the most recently updated characters displayed first.  You can also enter your characters name in the box at the top to jump directly to that character.\r\n\r\n[h3]Finding My Characters[/h3]\r\n\r\n[ul]\r\n[li]Use the breadcrumb listings at the top to browse to your region, battlegroup, and realm.  When you do this, a box will appear in the listing at the top of the page.  Enter your character\'s name in this box to be taken directly to your character.  You can use the \"Claim Character\", which is located under the Manage Character button, to save a character to your [url=/user=fewyn#characters]user page[/url] for later viewing.[/li]\r\n[/ul]\r\n\r\n[i]Tip: Claimed characters can be made public or private as you choose—so you only show off the characters people want you to see!  Basic information for the profiles will remain public, just as it is in the Armory—but any connection to your account will be hidden.[/i]\r\n\r\n[h3]Filters[/h3]\r\nBut that\'s not the only way to find a character! You can also search Profiles using our robust filter system, just the same way that you can search items, NPCs, or spells in game. Characters and guilds can be filtered by name, region, and realm to limit the number of displayed results.\r\n\r\nAdditionally, characters can be filtered by faction, level, race, and class – as well as a number of other unique and useful criteria. For example:\r\n\r\n[ul]\r\n[li][div float=right align=right][img src=STATIC_URL/images/help/profiler/filters.gif]\r\n[small]Searching for characters that match your criteria.[/small][/div]Let\'s see [url=/?profiles=us.draenor&filter=cl=8;ra=11;cr=35;crs=0;crv=450]all the Draenei mages on my server that have their tailoring maxed out[/url].[/li]\r\n[li]Hmm... I wonder if anyone is [url=/?profiles=eu&filter=na=Malgayne]using my name on European servers[/url]?[/li]\r\n[li]How do I compare to [url=/?profiles=us.draenor&filter=cl=2;minle=80;maxle=80;cr=7;crs=1;crv=50]other Retribution-specced paladins on my server[/url]?[/li]\r\n[li]How many [url=/?profiles&filter=cr=23;crs=0;crv=871]Bloodsail Admirals[/url] are there out there?[/li]\r\n[li]Who got caught wearing a [url=/?profiles&filter=cr=21;crs=0;crv=22279]Lovely Black Dress[/url]?[/li]\r\n[li]How many people on my server and faction [url=/?profiles=us.sentinels&filter=si=2;cr=23;crs=0;crv=2904]completed Heroic Ulduar[/url]?[/li]\r\n[/ul]\r\n\r\nWe\'ll be adding more filters as time goes on, so feel free to experiment – and let us know if you think of other ideas!\r\n\r\n[pad][pad][pad]\r\n\r\n[h3]Guild and Arena Team Rosters[/h3]\r\nWhen you click on a character\'s guild or arena team, you will be directed to a roster view listing all the characters that belong to it. The roster view displays additional information, including guild ranks and personal arena team ratings. You can further filter this information using the [b]Create a filter[/b] link, should you want to find characters matching specific criteria. Now its easy to find all of the crafters in your guild!\r\n\r\n[h3][img src=STATIC_URL/images/help/profiler/queue.gif float=right]Resync Queue[/h3]\r\nWhen a character resync is requested, it is added to the queue. The queue is used to make sure everyone\'s characters are updated and processed in the order they were submitted, without overloading the [url=http://us.battle.net/wow/en/]Battle.net Armory\'s API[/url] with requests. Whenever you access a character that does not exist in our database or has not been updated in more than 1 hour, it will automatically be added to the queue.\r\n\r\n[/tab]\r\n\r\n[tab name=\"General usage\"]\r\n\r\nThe profiler has a wealth of information it can display about characters and custom profiles, so it can seem daunting at first! Each of the sections are broken down in detail below.\r\n[h3]Basic Profile Information[/h3]\r\nAt the top of a profile you will see an expanded header with vital information about the profile itself. All profiles have an icon and the character\'s race, class and level; Armory characters display a link to the character\'s guild under the name, while custom profiles display a description set by the user that created it. A link to [b]Edit[/b] this information appears on the bottom line, allowing you to update a profile you created or make a new custom profile from an existing one.\r\n\r\n[ul]\r\n[li][img src=STATIC_URL/images/help/profiler/edit.gif float=right][b]Name [/b]– Give your profile a name! Names must start with a letter, and can only contain letters, numbers, and spaces.[/li]\r\n[li][b]Level[/b] – Select a level for your profile. Profiles must be at least level 10 (55 for Death Knights) and no more than level 85.[/li]\r\n[li][b]Race[/b] – Ever wonder what you\'d look like as a tauren instead of an orc? Choose any race for your profile, and the character model with automatically be updated.[/li]\r\n[li][b]Class[/b] – You can select any class you like, regardless of racial restrictions. See what your stats would be if you were a draenei druid![/li]\r\n[li][b]Gender[/b] – Select male or female to set your character\'s gender.[/li]\r\n[li][b]Icon[/b] – Icons are automatically generated for Armory characters and in game class/race combinations, but you can change the icon to any you like.[/li]\r\n[li][b]Description[/b] – Enter a tag line or brief description for the profile so you and others know what it is about.[/li]\r\n[li][b]Visibility[/b] – Public profiles will be visible on your user page and anyone can view a public profile. Private ones will not be displayed or visible to others.[/li]\r\n[/ul]\r\n[i]Note: If you edit a character in any way, it will become a custom profile. The reputations, achievements, and raid progress information will be removed.[/i]\r\n\r\n[h3]Managing Profiles[/h3]\r\nIn the upper right are a number of useful buttons for managing profiles without having to go back to your user page. Each of the buttons have several options that can be used to manage the character\'s page you are currently on and include the following options.\r\n\r\n[ul]\r\n[li][b]Custom Profile[/b]\r\n[ul][li][b]New[/b] – This is a quick link to creating a new, blank profile from scratch. It will open in a new window so you do not lose your current profile. This option is always available.[/li]\r\n[li][b]Save[/b] – Save any changes you have made to this profile. This option is only available for logged in users on profiles they own.[/li]\r\n[li][b]Save as[/b] – This will let you save your current changes under a new name. It is extremely useful for making copies of profiles! This option is only available for logged in users.[/li][/ul][/li]\r\n[li][b]Manage Character[/b]\r\n[ul][li][b]Resync[/b] – Request that the character be updated from the armory; it will be added to the queue. This option is only available on Armory character pages.[/li]\r\n[li][b]Claim character[/b] – Adds an Armory character to your user page. This is a good thing to do with all your alts. This option is only available for logged in users on Armory character pages.[/li]\r\n[li][b]Remove[/b] - Removes the character from your user page. Use this if you no longer play the character or have long since deleted it.[/li]\r\n[li][b]Pin/Unpin[/b] - Pin one of your characters so you can perform personalized searches throughout the database for missing or completed quests, achievements, recipes and more![/li]\r\n[/ul][/li]\r\n[/ul]\r\n\r\n[h3]From the User Page[/h3]\r\n[img src=STATIC_URL/images/help/profiler/userpage.gif float=right]All of your claimed Armory characters and custom profiles are listed in one convenient place on your user page. From the [b]Characters[/b] tab you can remove one or more claimed characters. The [b]Profiles[/b] tab allows you to create a new profile, delete profiles, or change the visibility settings of profiles. Your private profiles will not be visible to anyone else.\r\n\r\n[i]Tip: When you are logged in, all of your characters and custom profiles can be accessed from the [b]My profiles[/b] menu at the top right of any page![/i][pad]\r\n[h3]Saving Your Work[/h3]\r\nAny profile can be edited, even if you don\'t own it, but you\'ll probably want to save your work when you\'re done! You must have an account with us in order to save a profile. Once you\'ve created an account, you can bookmark any number of Armory characters and save up to 10 custom profiles. Premium users will be able to create even more, so upgrade if 10 just isn\'t enough! You can use the red buttons to save a profile from its page, and manage your existing profiles and characters from your user page. \r\n\r\n[/tab]\r\n\r\n[tab name=\"Inventory and talents\"]\r\n[img src=STATIC_URL/images/help/profiler/character.jpg height=300 float=right]The main tab for a profile is the character inventory, which includes a lot of the same information you would see by looking at your character pane in game. This tab is broken up into four key sections - the character view, quick facts box, statistics, and gear summary.\r\n\r\n[h3]Character View[/h3]\r\nThe first thing you\'ll notice, of course, is your character – as rendered by our custom built modelviewer, in all it\'s three-dimensional glory. You can turn the character with your mouse, and zoom in and out using the A and Z keys, just like the modelviewer elsewhere in the site.  [b]We even pull your face, hair, and skin color information from the Armory![/b]\r\n\r\nOn either side of the character are inventory icons which you can right click on for a menu of options:\r\n\r\n[i]Tip: You can remove a gem or enchant by clicking None in the picker window or by right clicking on it in the gear summary.[/i]\r\n\r\n[ul]\r\n[li][img src=STATIC_URL/images/help/profiler/itemmenu.gif float=right][b]Equip... / Replace...[/b] – Selecting this option will give you a quick search box in which you can type an item\'s name. Click on the item or hit return to equip it.\r\nUnequip – Unequips the item, of course. :)[/li]\r\n[li][b]Add / Replace enchant...[/b] – The spell icon on the left shows if the item is enchanted. This opens a customized picker window with all enchants available for the item slot.[/li]\r\n[li][b]Add / Replace gem...[/b] – The icon on the left shows the socket color or socketed gem. Like the enchants, this opens a picker window with valid gems for the socket.[/li]\r\n[li][b]Extra socket[/b] – The check mark on the left indicates if a blacksmithing socket has been added to this item. Click to toggle on or off.[/li]\r\n[li][b]Clear Enhancements[/b] - This will remove all reforges, enchantments, gems and extra sockets from an item. Useful if you want to start fresh with an item.[/li]\r\n[li][b]Display on character[/b] – The checkmark on the left indicates if the item is displayed on the  model. Click to toggle on or off – it works for more than just cloaks and helms![/li]\r\n[li][b]Compare[/b] – Adds the item to the [url=/?compare]item comparison tool[/url] and opens it in a new window to compare with other items.[/li]\r\n[li][b]Find upgrades[/b] – Uses our [url=/?help=stat-weighting]weighted search[/url] to find upgrades based on your talent spec.[/li]\r\n[li][b]Who wears this?[/b] – Creates a filtered list of other Armory characters who are also wearing the item.[/li]\r\n[/ul]\r\n\r\n[i]Tip: Items that can take enchantments but have no enchantment, or which have empty sockets, will even have a little notification in the tooltip![/i]\r\n\r\n[img src=STATIC_URL/images/help/profiler/quickfacts.gif float=right][h3]Quick Facts Box[/h3]\r\nOn the right hand side is a handy Quick Facts box that displays basic, defining information about a profile. This box is chock full of useful information, including talent spec, achievement points, and professions.\r\n\r\n[i]Tip: Any raid icon that\'s ringed in [color=c4]gold[/color] is a raid that the character has cleared![/i]\r\n[h3]Statistics[/h3]\r\nYou\'ll also notice that all of a profile\'s statistics are laid out beneath the character view. This is also all information you can get from the Armory (and then some), but we lay it out in a nice, convenient page so you can view it all at once – no more messing with drop down menus. You can also click on a statistic and expand it so you can see its tooltip information right there on the page—or click on the header to expand all the related statistics. Your statistics are updated as you edit any part of a profile, including race, class, level, items, enhancements, or talents – all in real time! [b]Statistic modifications from glyphs and buffs are not presently supported, but will be in the future.[/b]\r\n\r\n[i]Note: These statistics are calculated manually – they are not pulled from the Armory. Statistics calculations are still in beta and will ironed out as we go.[/i]\r\n\r\n[img src=STATIC_URL/images/help/profiler/statistics.gif float=center]\r\n\r\n[h3]Gear Summary[/h3]\r\n[div float=right align=right][img src=STATIC_URL/images/help/profiler/gearsummary.gif]\r\n[small]A warning message is displayed for missing enhancements.[/small][/div]Last on the character inventory tab, but not least, is the gear summary. This is a personalized list of all items worn by the character, with convenient column headers and in line filtering options. Use it to see where most of a character\'s items come from, what is the best and worst piece, and whether or not there are missing gems and enchants. Just in case the empty icons aren\'t clear enough, a warning appears at the top of the list if a character is missing gems, enchants, or blacksmith sockets. This [color=q10]warning[/color] is based on the professions of the character if it is an Armory profile, and otherwise shows you everything missing on custom profiles.\r\n\r\nThe gems and enchants can also be edited from within the gear summary, and have a few additional options not available in the character view. You can remove or replace an enhancement from here, and you can find upgrades using our [url=/?help=stat-weighting]weighted search[/url] – just like items!\r\n\r\n[h3]Talents[/h3]\r\nThe talents tab includes an inline version of our [url=/?talent]talent calculator[/url] with a full display of a character\'s talents. It is locked by default, but you can unlock it to begin editing talents, just as you would normally. There are two extra features in the Profiler\'s talent calculator: you can store and swap between two specs for each character, and export the current talent build to the calculator to link to your friends. When you change your talents (or swap between specs) your gear score and statistics will be updates real time!\r\n\r\n[/tab]\r\n\r\n[tab name=\"Other tabs\"]\r\n\r\n[h3]Reputation[/h3]\r\nThe reputation tab displays the complete faction information of an Armory character, with collapsible headers for each section. Its much easier to read than the tiny faction pane in game! Of course, you can link directly to the faction\'s page to get more information about that faction. \r\n[h3][img src=STATIC_URL/images/help/profiler/achievements.gif float=right]Achievements[/h3]\r\nThe achievements tab lists an Armory character\'s progress in each of the main achievement categories, and has a filterable list of achievements including date completed. All of the normal column and list filters are available, along with some new ones! You can filter the list by earned, in progress or complete achievements – complete are displayed by default – or click on any of the category progress bars to only display achievements from that category.\r\n\r\n[/tab]\r\n\r\n[tab name=Completion_Tracker]\r\n\r\n[img src=STATIC_URL/images/help/profiler/quests.jpg float=right width=450]You can use the Profiler\'s [b]Completion Tracker[/b] feature to keep track of your quests, achievements, pets, mounts, recipes, and more!\r\n\r\n[h3]Getting Started[/h3]\r\n\r\nIn order to start tracking your completion data, all you need to do is visit your character\'s page on the profiler and resync it. This will automatically collect data about your character\'s completed achievements, companion pets, mounts, quests, recipes, reputations and titles.\r\n\r\n[h3][img src=STATIC_URL/images/help/profiler/completion.jpg float=right]Tracking Your Completion Data[/h3]\r\n\r\nOnce you\'ve got your data up on the site, it will be available in the form of five new tabs: [b]mounts[/b], [b]companions[/b], [b]recipes[/b], [b]quests[/b], and [b]titles[/b].\r\n\r\nIf you open the mounts, companions, or titles tabs, you\'ll immediately be greeted by a list of all the entries you\'ve already completed.  You can cycle through the different tabs to see the ones you already have, the ones you still have yet to collect, a complete list, or a list of just the ones you\'ve \"excluded\" (more on that shortly).  You can also use the \"Search within results\" box to search the list based on a keyword, just like you can with other search results in the database.\r\n\r\nThe recipe, and quest tabs, like the Achievements tab, contain more entries—so you\'ll be presented with a box like the one shown above.  From there, all you have to do is click one of the progress bars to see the complete tabbed list in each category.\r\n\r\n[h3]Exclusions[/h3]\r\n\r\nWhen you\'re trying to make sure we check off every quest, achievement, or mount on our list, everyone knows that there are some that you just don\'t want to bother with.  To that end, we\'ve created [b]exclusions[/b].\r\n\r\n[img src=STATIC_URL/images/help/profiler/exclusions.jpg float=right]Using exclusions, you can flag certain quests, mounts, achievements, recipes, pets, or titles that \"don\'t count\" toward your completion total.  When you exclude (for example) a quest, that quest no longer appears in \"incomplete\" listings, and the total number of quests in that category is reduced by one.\r\n\r\n[b]For example:[/b] There are 632 quests in the \"Eastern Kingdoms\" category. If I were to decide that [quest=367] is for noobs and I don\'t want to count it, then all I have to do is put a check in the box next to the quest and click \"Exclude\".  After I do so, the Eastern Kingdoms progress bar will only show [i]631[/i] quests total—the remaining quest will appear in the \"Excluded\" tab but won\'t be counted for anything else.\r\n\r\nIf you want to re-include a quest, just go to the \"Excluded\" tab and then use the checkboxes to restore as many as you like.  You can do the same thing for achievements, titles, mounts, pets, or recipes.\r\n\r\nIf you [b]complete[/b] a quest that you have excluded, it will show in the progress bar as a [b]+1[/b].  Example: If there are 31 quests in the \"Miscellaneous\" category, and I\'ve completed 20 quests and excluded 1, the progress bar will show [b]20/30[/b].  If I have completed [i]the quest that I excluded[/i], then the progress bar will show [b]20(+1)/30[/b].  If I then go on to complete ALL the quests in that category (including the one I excluded), the progress bar will show [b]30(+1)/30[/b].\r\n\r\n[b]Exclusion Manager[/b]\r\nThe companions and mounts tabs let you manage your exclusions en masse with the Exclusion Manager.  Just click the \"Manage Exclusions\" button on top of the tabs to see a list of convenient categories you might want to exclude.  There\'s also a \"reset all\" button here to let you wipe all of your exclusions and start over.\r\n\r\n[b]Note:[/b] The Exclusion Manager is currently only available for companions and mounts.\r\n\r\n[i]Tip: Exclusions are tied to your account, not to a particular character.  This is so even when you look at someone else\'s character, you\'re judging them by [/i]your[i] completion standards, not anyone else\'s![/i] \r\n\r\n[/tab]\r\n\r\n[tab name=Calculations]\r\n\r\nMost of the information we display is pretty straightforward. A lot of it, particularly the stats on items, is readily available in our database and on various tooltips. There are some new numbers on profile pages that you may ask, what does this number mean? How was it calculated?\r\n[h3]Base Statistics[/h3]\r\nA character\'s five base statistics are determined primarily by his or her class and level. This base amount has a modifier applied to it depending on the character\'s race. We gathered an extensive amount of data from the armory to come up with these base numbers, using untalented individuals of every race, class, and level combination. Because racial modifiers are consistent, we are able to create statistics for \"fake\" race and class combos using the data we already know. However, the Armory does not give data on characters below level 10 or Death Knights below level 55, so we have no statistic information for these profiles. To simplify things, we have set a minimum level for custom profiles based on the available statistics.\r\n[h3]Gear Score[/h3]\r\nOkay, so a lot of sites have gear scores. Most of them (ours included) are based around the [url=http://www.wowwiki.com/Item_level]item budget[/url] Blizzard uses to determine how much of each stat can be on an item. This budget is calculated using the item\'s level, quality, and slot, and we use the budget as the item\'s gear score. You can view a complete breakdown of an item\'s gear score by mousing over it in the [url=/?help=profiler#profiler-inventory-and-talents]gear summary[/url] at the bottom of the character tab. You can view a breakdown of a profile\'s total gear score by mousing over it in the Quick Facts box, also on the character tab.\r\n\r\nEach gear score is color coded based on the item levels of the gear in reference to the character level. [b][color=q0]Grey[/color][/b] for poor, [b][color=q1]White[/color][/b] for common, [b][color=q2]Green[/color][/b] for uncommon, [b][color=q3]Blue[/color][/b] for rare, [b][color=q4]Purple[/color][/b] for epic and [b][color=q5]Orange[/color][/b] for legendary. For example, a level 70 character wearing high item-level, raiding epics from [zone=3606] and [zone=3959] will have a purple-colored gearscore, as their items are considerably \"epic\" quality for their level. However, the same character at 80, if wearing this same gear,  will have the gearscore colored blue as the items are of lower-than-optimal quality for their level.\r\n\r\nThe value of an empty socket was generated using the gear score of appropriate gems for the item in question, and subtracted from the item\'s score. This allows us to score unsocketed items lower than an item without sockets of the same level, quality, and slot. Items with better than expected gems will receive higher scores, and items with lower quality gems (or no gems at all) will receive lower scores.\r\n\r\nThe values of enchants are based off of the level of the enchantment.  Endgame enchantments are 20 points, profession perks are 40 points, etc.  The numbers go down from there.\r\n\r\nYou may notice that some profiles have different gear scores for the same item. There is an extreme difference in budget between a two-handed or one-handed weapon, which causes a discrepancy in scores between characters who should be fairly equal according to the level of their gear. To address this, the gear score of weapons has been normalized so that a character with appropriate weapon choices has the equivalent score of two two-handed weapons. Appropriate weapons are determined by your class and spec; for example, an enhancement shaman should dual wield one handed weapons, a protection warrior should have a one-hander and shield, etc. For classes which the melee weapons don\'t really matter – like hunters or spellcasters – anything they can use is considered appropriate.\r\n\r\n[i]Note: Gear score does not take into account the stats of the item. It is a measurement of quality of gear, not whether the stats on the gear are suited to the character\'s spec.[/i]\r\n\r\n[h3]Guild Scores[/h3]\r\nGuild gear scores and achievement points are derived using a weighted average of all of the known characters in that guild. Guilds with at least 25 level 80 players receive full benefit of the top 25 characters\' gear scores, while guilds with at least 10 level 80 characters receive a slight penalty, at least 1 level 80 a moderate penalty, and no level 80 characters a severe penalty. This is to prevent small guilds and bank alts from appearing to have higher scores than legitimate raiding guilds. Instead of being based on level, achievement point averages are based around 1,500 points, but the same penalties apply.\r\n\r\n[/tab]\r\n\r\n[/tabs]',NULL),(8,577,0,'[minibox]\n[h2]Steamwheedle Cartel[/h2]\n[faction=21]\n[faction=577]\n[faction=369]\n[b]Everlook[/b]\n[/minibox]\n\n[b]Everlook[/b], the faction of the town Everlook, is a trading post is run by the goblins of the Steamwheedle Cartel. It lies at the crossroads of [zone=618]\'s main trade routes.\n\n[h3]General Information[/h3]\nThis town is the last point of civilization before reaching Hyjal Summit. It is run by goblins as a trading post and is officially neutral to all races and factions. Even so, pilgrims allowed to venture up to the World Tree stop here, but otherwise this is the highest that merchants and explorers may venture without the night elves’ permission. Everlook would offer a commanding view of Kalimdor, if it were not at such a high altitude that clouds constantly shroud the mountain’s lower flanks.\n\nEverlook is the only major goblin outpost in northern Kalimdor, and it serves several purposes. First, it serves as the base of operations for goblin thorium and arcanite miners since Winterspring has some of the few untapped veins of those materials on the continent. Second, it serves as a center of trade between the Alliance and the Horde. While Everlook is hardly as safe as Moonglade, generally the Alliance and the Horde treat each other fairly well there. Additionally, Everlook is a frequent stop-off and resupply point for the faithful who make the pilgrimage through Winterspring to Hyjal Summit.\n\n[h3]Reputation[/h3]\nReputation for Everlook and the Steamwheedle Cartel is mostly gained from quests in Winterspring. Having a friendly or higher reputation will make the guards help you in case of initiated violence against you.',NULL),(-13,4,0,'[menu tab=2 path=2,13,4]Can\'t find the answer you were looking for? Just [url=/?aboutus#contact]contact us[/url], or post on our [url=/?forums&board=1]forums[/url]! \r\n\r\n[toc]\r\n\r\n[h2]General Usage[/h2]\r\n[ul]\r\n[li][screenshot url=STATIC_URL/images/help/talent-calculator/glyphs.jpg thumb=STATIC_URL/images/help/talent-calculator/glyphs2.jpg width=268 height=218 float=right][/screenshot][b]Selecting a class[/b] - Easily select a class\' talent tree by chosing from the class icon at the top, or from the dropdown menu. Clicking on a class\' name at the top left of the calculator will open that class\' page here on on this site, providing even more detailed information![/li] \r\n[li][b]Adding or removing talent points[/b] - To add points in a talent simply click the appropriate talent. To remove points, you can either right-click (or Shift+click) the talent.[/li]\r\n[li][b]Adding glyphs[/b] - Click on an empty glyph slot to open a picker window from which you can make your selection. To remove a glyph, simply right-click (or Shift+click) that glyph.[/li]\r\n[li][b]Linking to a build[/b] – Simply copy the auto-updating URL from your browser\'s address bar.[/li]\r\n[/ul]\r\n\r\n[h2]Tools + Options[/h2]\r\n[ul]\r\n[li][b]Reset all[/b] - Resets all talents across all trees.[/li]\r\n[li][img src=STATIC_URL/images/help/talent-calculator/options.jpg float=right][b]Reset tree[/b] - Clicking the red X at the top right corner of a talent tree will reset all talents in that particular tree. Other trees will not be reset.[/li]\r\n[li][b]Lock / Unlock[/b] - Locks or unlocks the talent build, preventing (or allowing) changes to be made. Linking to a build will automatically lock talents.[/li]\r\n[li][b]Import[/b] – Displays a pop-up text window where you can enter the URL of a talent build made with [url=http://www.wowarmory.com/talent-calc.xml]Blizzard\'s talent calculator[/url]. Be sure that you first select the \"Link to this build\" option in the Blizzard talent calculator so that the URL will be properly formatted for importing.[/li]\r\n[li][b]Print[/b] - Opens up a new, printer-friendly page with a textual representation of your chosen talents. Nice if you want to paste the talents you\'ve chosen somewhere, and would prefer it written out.[/li]\r\n[li][b]Link[/b] - Locks your chosen talents and creates a link to your build. Use this option to easily create a URL to share your build with others![/li]\r\n[/ul]\r\n\r\n[h2]Useful Tips[/h2]\r\n\r\n[ul]\r\n[li]When the calculator is locked, you can click talents and glyphs to view their corresponding spell or item page.[/li]\r\n[li]If you\'re building a third-party application, you can link to our talent calculator by using Blizzard-style URLs such as:\r\n[code]HOST_URL?talent#hunter-512002015051122431005311500053052002300100000000000000000000000000000000000000000[/code][/li]\r\n[/ul]',NULL),(-13,1,0,'[menu tab=2 path=2,13,1]\r\n\r\n[url=item=35350][img src=STATIC_URL/images/help/modelviewer/ss-viewin3d.gif float=right][/url]Aowow has a model viewer that will let you see the items and NPCs in the game in full 3D!\r\n\r\nYou can use the dropdown menus to select which character model you want to display armor pieces on, and the model viewer will remember your choice.\r\n\r\nThere are two different versions of the model viewer available, one written in Flash, and the other one written in Java. Aowow should remember which version you used last time, and will automatically open that model viewer the next time you click on the \"View in 3D\" button.\r\n\r\nIf you have any issues, please report them [url=/?forums&topic=202524]here[/url]!\r\n\r\n[i]Tip: You can close the box by clicking anywhere outside of the box.[/i]\r\n\r\n[h2]Modes[/h2]\r\n\r\n[tabs name=mode]\r\n\r\n[tab name=Flash]\r\n\r\n[url=item=34092][img src=STATIC_URL/images/help/modelviewer/ss-flash.png float=right][/url]The [b]Flash[/b] viewer is simple, quick to load, and should work on nearly all browsers. The Flash viewer is the default viewer, and all models will automatically load in the Flash Viewer unless you specify otherwise.\r\n\r\nIt requires the latest version of [url=http://www.adobe.com/go/BONRN]Flash[/url] to be installed on your computer.\r\n\r\n[h3]Controls[/h3]\r\n[ul]\r\n[li][b]Rotate[/b] – Click and drag / arrow keys[/li]\r\n[li][b]Zoom[/b] – Mousewheel / A & Z keys[/li]\r\n[/ul]\r\n\r\n[h3]Features[/h3]\r\n[ul]\r\n[li]Motion blur[/li]\r\n[li]Full screen mode[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[tab name=Java]\r\n\r\n[url=/?item=35350][img src=STATIC_URL/images/help/modelviewer/ss-java.png float=right][/url]The Java viewer is slower to initialize than the Flash Viewer, but once it\'s initialized it renders in [b]much greater[/b] detail. Most browsers will only need to initialize it once, and subsequent loads will be much faster. Some browsers may ask you to accept a security certificate when you initialize the viewer.\r\n\r\nIt requires the latest version of [url=http://jdl.sun.com/webapps/getjava/BrowserRedirect?locale=en&host=www.java.com]Java[/url] to be installed on your computer.\r\n\r\n[h3]Controls[/h3]\r\n[ul]\r\n[li][b]Rotate[/b] – Click and drag[/li]\r\n[li][b]Zoom[/b] – Mousewheel[/li]\r\n[li][b]Move[/b] – Right-click and drag[/li]\r\n[/ul]\r\n\r\n[h3]Features[/h3]\r\n[ul]\r\n[li]3D acceleration[/li]\r\n[li]Animations on NPCs, character models, small pets, and mounts[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[/tabs]\r\n',NULL),(-10,0,0,'[menu tab=2 path=2,10]\r\n\r\n[div float=right align=right][url=http://wow.joystiq.com/2010/04/14/breakfast-topic-using-irl-irl/][img src=STATIC_URL/images/help/tooltips/ss-wowcom.png][/url]\r\n[small]Tooltips in action on [url=http://wow.joystiq.com/2010/04/14/breakfast-topic-using-irl-irl/]WoW Insider[/url][/small][/div]\r\n\r\nIt\'s never been easier to add tooltips to your site.\r\n\r\n[ol]\r\n[li]Add this piece of HTML code in the <head> section of your page:\r\n[code]<script type=\"text/javascript\" src=\"STATIC_URL/widgets/power.js\"></script><script>var aowow_tooltips = { \"colorlinks\": true, \"iconizelinks\": true, \"renamelinks\": true }</script>[/code][/li]\r\n[li]You are done![/li]\r\n[/ol]\r\n\r\nLinks found on your site will now sport a [b]tooltip[/b] and an [b]icon[/b]. The following pages are supported: achievement, profile, item, npc, object, spell, quest. Icons show up by default, you can customize the colors of your links, and easily rename them!\r\n\r\nYou can check out this [url=STATIC_URL/widgets/power/demo.html]working demo[/url], and see how easy it is!\r\n\r\n[h2]Related[/h2]\r\n\r\n[tabs name=Related]\r\n\r\n[tab name=\"Advanced usage\"]\r\n\r\nOnce you have the <script> tag added to your site, the following parameters can be used in the [b]rel[/b] attribute of your links (<a>). They can be combined by using the ampersand character (&amp;).\r\n\r\n[h3]General[/h3]\r\n\r\n[ul]\r\n[li][b]Custom URLs[/b]\r\nYou can make your links point to any page you wish, and still display a tooltip. Example:[code]<a href=\"#\" rel=\"item=2828\">hai</a>[/code][pad][/li]\r\n[li][b]Domain[/b] - domain\r\nEnter the domain (www, de, es, fr, ru) to display a different version or localization, e.g: domain=fr[/li]\r\n[/ul]\r\n\r\n[h3]Items[/h3]\r\n\r\n[ul]\r\n[li][b]Level[/b] – lvl\r\nEnter the character\'s level, useful for heirloom items![pad][/li]\r\n[li][b]Enchant[/b] – ench\r\nEnter the ID of the enchant, such as: ench=2647[pad][/li]\r\n[li][b]Gems[/b] – gems\r\nList all the gems (item IDs) you want the item to have, separated by a colon. e.g: gems=23121[pad][/li]\r\n[li][b]Extra Socket[/b] - sock\r\nAdd an extra socket to the item. Only works for belts, bracers and gloves.[pad][/li]\r\n[li][b]Item Set Pieces[/b] – pcs\r\nList all the pieces (item IDs) you want to consider for the set bonus, separated by a colon. e.g: pcs=25695:25696:25697[pad][/li]\r\n[li][b]Random Enchantment[/b] – rand\r\nEnter the ID of random enchantment (e.g. of the Bear), such as: rand=-7. Full list of IDs under the toggle.\r\n[toggler hidden id=rand]Random Enchant ID list[/toggler]\r\n[div hidden id=rand][code]\r\nSingle-stat Suffixes\r\n15, of Spirit, Spirit\r\n16, of Stamina, Stamina\r\n17, of Strength, Strength\r\n18, of Agility, Agility\r\n19, of Intellect, Intellect\r\n20, of Power, Attack Power\r\n21, of Arcane Wrath, Arcane Damage\r\n22, of Fiery Wrath, Fire Damage\r\n23, of Frozen Wrath, Frost Damage\r\n24, of Nature\'s Wrath, Nature Damage\r\n25, of Shadow Wrath, Shadow Damage\r\n26, of Intellect, Intellect\r\n27, of Nimbleness, Dodge\r\n28, of Stamina, Stamina\r\n30, of Spirit, Spirit\r\n61, of Intellect, Intellect\r\n62, of Strength, Strength\r\n63, of Agility, Agility\r\n64, of Power, Attack Power\r\n65, of Magic, Spell Power\r\n84, of Stamina, Stamina\r\n99, of Speed, Haste\r\n\r\nTwo-stat Suffixes\r\n5, of the Monkey, Agility/Stamina\r\n6, of the Eagle, Intellect/Stamina\r\n7, of the Bear, Stamina/Strength\r\n8, of the Whale, Spirit/Stamina\r\n9, of the Owl, Intellect/Spirit\r\n10, of the Gorilla, Intellect/Strength\r\n11, of the Falcon, Agility/Intellect\r\n12, of the Boar, Spirit/Strength\r\n13, of the Wolf, Agility/Spirit\r\n\r\n14, of the Tiger, Agility/Hit\r\n29, of Eluding, Dodge/Agility\r\n31, of Arcane Protection, Stamina/Arcane Resistance\r\n32, of Fire Protection, Stamina/Fire Resistance\r\n33, of Frost Protection, Stamina/Frost Resistance\r\n34, of Nature Protection, Stamina/Nature Resistance\r\n35, of Shadow Protection, Stamina/Shadow Resistance\r\n47, of Blocking, Shield Block/Strength\r\n68, of the Bear, Strength/Stamina\r\n69, of the Eagle, Stamina/Intellect\r\n78, of the Monkey, Agility/Stamina\r\n81, of the Whale, Stamina/Spirit\r\n140, of the Wraith, Critical Strike/Spirit\r\n141, of the Wind, Spirit/Haste\r\n142, of the Master, Spirit/Mastery\r\n144, of the Shark, Critical Strike/Mastery\r\n145, of the Panther, Mastery/Haste\r\n146, Crit/Mastery, Critical Strike/Mastery\r\n147, of the Shark, Critical Strike/Mastery\r\n148, Crit/Spirit, Crit/Spirit\r\n150, of the Panther, Mastery/Haste\r\n151, of the Wind, Spirit/Haste\r\n152, of the Master, Spirit/Mastery\r\n153: of the Wraith, Haste/Mastery\r\n154, of the Shark, Critical Strike/Mastery\r\n156, of the Wraith, Critical Strike/Spirit\r\n157, of the Panther, Mastery/Haste\r\n158, of the Wind, Spirit/Haste\r\n159, of the Master, Spirit/Mastery\r\n160, of the Mongoose, Hit/Haste\r\n161, of Storms, Hit/Critical Strike\r\n162, of Flames, Hit/Mastery\r\n163, of the Mongoose, Hit/Haste\r\n164, of Storms, Hit/Critical Strike\r\n165, of Flames, Hit/Mastery\r\n166, of the Mongoose, Hit/Mastery\r\n167, of Storms, Hit/Critical Strike\r\n168, of Flames, Hit/Mastery\r\n\r\nThree-stat Suffixes (Added in TBC)\r\n36, of the Sorcerer, Stamina/Intellect/Haste\r\n37, of the Seer, Stamina/Intellect/Critical Strike\r\n38, of the Prophet, Intellect/Spirit/Haste\r\n39, of the Invoker, Intellect/Critical Strike\r\n40, of the Bandit, Agility/Stamina/Critical Strike\r\n41, of the Beast, Hit/Critical Strike/Stamina\r\n42, of the Elder, Stamina/Intellect/Spirit\r\n43, of the Soldier, Strength/Stamina/Critical Strike\r\n44, of the Elder, Stamina/Intellect/Spirit\r\n45, of the Champion, Strength/Stamina/Dodge\r\n46, of the Test, Agility/Armor/Intellect/Spirit/Stamina\r\n48, of Paladin Testing, Intellect/Stamina/Spell Power/Strength\r\n49, of the Grove, Strength/Agility/Haste\r\n50, of the Hunt, Haste/Critical Strike/Agility\r\n51, of the Mind, Intellect/Critical Strike/Haste\r\n52, of the Crusade, Strength/Stamina/Dodge\r\n53, of the Vision, Intellect/Haste/Stamina\r\n54, of the Ancestor, Strength/Critical Strike/Stamina\r\n55, of the Nightmare, Stamina/Intellect/Critical Strike\r\n56, of the Battle, Strength/Stamina/Critical Strike\r\n57, of the Shadow, Agility/Stamina/Critical Strike\r\n58, of the Sun, Critical Strike/Stamina/Intellect\r\n59, of the Moon, Intellect/Stamina/Spirit\r\n60, of the Wild, Haste/Agility/Stamina\r\n66, of the Knight, Stamina/Dodge/Expertise\r\n67, of the Seer, Stamina/Critical Strike/Intellect\r\n70, of the Ancestor, Strength/Critical Strike/Stamina\r\n71, of the Bandit, Agility/Stamina/Critical Strike\r\n72, of the Battle, Strength/Stamina/Critical Strike\r\n73, of the Elder, Stamina/Intellect/Spirit\r\n74, of the Beast, Hit/Critical Strike/Stamina\r\n75, of the Champion, Strength/Stamina/Dodge\r\n76, of the Grove,  Strength/Agility/Haste\r\n77, of the Knight, Stamina/Dodge/Expertise\r\n79, of the Moon, Intellect/Stamina/Spirit\r\n80, of the Wild, Haste/Agility/Stamina\r\n82, of the Vision, Intellect/Haste/Stamina\r\n83, of the Sun, Critical Strike/Stamina/Intellect\r\n85, of the Sorcerer, Stamina/Intellect/Haste\r\n86, of the Soldier, Strength/Stamina/Critical Strike\r\n87, of the Shadow, Agility/Stamina/Critical Strike\r\n88, of the Foreseer, Intellect/Critical Strike/Haste\r\n89, of the Thief, Stamina/Agility/Haste\r\n90, of the Necromancer, Stamina/Hit/Intellect\r\n91, of the Marksman, Stamina/Agility/Hit\r\n92, of the Squire, Stamina/Hit/Strength\r\n93, Restoration, Intellect/Spirit/Stamina\r\n139, of the Mercenary, Strength/Haste/Stamina\r\n\r\nFour-stat Suffixes (Added in Cataclysm)\r\n100, of the Principle, Critical Strike/Hit/Strength/Stamina\r\n101, of the Sentinel, Expertise/Hit/Strength/Stamina\r\n102, of the Hero, Haste, Critical Strike/Strength/Stamina\r\n103, of the Avatar, Critical Strike/Mastery/Strength/Stamina\r\n104, of the Embodiment, Haste/Mastery/Strength/Stamina\r\n105, of the Guardian, Mastery/Shield Block/Strength/Stamina\r\n106, of the Defender, Dodge/Parry/Strength/Stamina\r\n107, of the Exemplar, Strength/Dodge/Stamina/Expertise\r\n108, of the Curator, Strength/Dodge/Parry/Stamina\r\n109, of the Preserver, Mastery/Intellect/Spirit/Stamina\r\n110: of the Elements, Stamina/Intellect/Hit/Critical Strike\r\n111, of the Paradigm, Stamina/Intellect/Hit/Mastery\r\n112, of the Pattern, Stamina/Intellect/Critical Strike/Haste\r\n113, of the Essence, Stamina/Intellect/Haste/Spirit\r\n114, of the Flameblaze, Mastery/Intellect/Hit/Stamina\r\n115, of the Archetype, Stamina/Agility/Hit/Critical Strike\r\n116, of the Manifestation, Stamina/Agility/Hit/Expertise\r\n117, of the Incarnation, Stamina/Agility/Critical Strike/Haste\r\n118, of the Faultline, Mastery/Strength/Haste/Stamina\r\n119, of the Ideal, Stamina/Agility/Haste/Mastery\r\n120, of the Earthshaker, Critical Strike/Strength/Hit/Stamina\r\n121, of the Landslide, Strength/Hit/Stamina/Expertise\r\n122, of the Earthfall, Critical Strike/Strength/Haste/Stamina\r\n123, of the Earthbreaker, Critical Strike/Mastery/Strength/Stamina\r\n124, of the Mountainbed, Mastery/Strength/Stamina/Expertise\r\n125, of the Bedrock, Mastery/Strength/Parry/Stamina\r\n126, of the Substratum, Stamina/Strength/Expertise/Dodge\r\n127, of the Bouldercrag, Strength/Dodge/Parry/Stamina\r\n128, of the Rockslab, Mastery/Strength/Dodge/Stamina\r\n129, of the Wildfire, Critical Strike/Intellect/Hit/Stamina\r\n130, of the Fireflash, Critical Strike/Intellect/Haste/Stamina\r\n131, of the Undertow, Intellect/Haste/Spirit/Stamina\r\n132, of the Wavecrest, Mastery/Intellect/Spirit/Stamina\r\n133, of the Stormblast, Critical Strike/Agility/Hit/Stamina\r\n134, of the Galeburst, Agility/Hit/Stamina/Expertise\r\n135, of the Windflurry, Critical Strike/Agility/Haste/Stamina\r\n136, of the Zephyr, Mastery/Agility/Haste/Stamina\r\n137, of the Windstorm, Critical Strike/Mastery/Agility/Staina\r\n138, of the Feverflare, Mastery/Intellect/Haste/Stamina\r\n143, of the Scorpion, Stamina/Intellect/Haste/Critical Strike\r\n149, of the Scorpion, Stamina/Intellect/Haste/Critical Strike\r\n155, of the Scorpion, Stamina/Intellect/Haste/Critical Strike\r\n\r\nHeroic Scenario and Battlefield Barrens Items\r\n344, of the Decimator, Critical Strike\r\n345, of the Unerring, Hit\r\n346, of the Adroit, Expertise\r\n347, of the Savant, Mastery\r\n348, of the Impatient, Haste\r\n349, of the Bladewall, Parry\r\n350, of the Untouchable, Dodge\r\n351, of the Pious, Spirit\r\n352, of the Landslide, Hit/Expertise\r\n353, of the Stormblast, Hit/Critical Strike\r\n354, of the Galeburst, Hit/Expertise\r\n355, of the Windflurry, Critical Strike/Haste\r\n356, of the Windstorm, Critical Strike/Mastery\r\n357, of the Zephyr, Haste/Mastery\r\n359, of the Flameblaze, Mastery/Hit\r\n360, of the Fireflash, Haste/Crit\r\n361, of the Feverflare, Haste/Mastery\r\n362, of the Undertow, Haste/Spirit\r\n363, of the Wavecrest, Spirit/Mastery\r\n364, of the Earthbreaker, Critical Strike/Mastery\r\n365, of the Faultline, Haste/Mastery\r\n366, of the Mountainbed, Mastery/Expertise\r\n367, of the Bedrock, Mastery/Parry\r\n368, of the Bouldercrag, Dodge/Parry\r\n369, of the Rockslab, Dodge/Mastery\r\n370, of the Earthshaker, Hit/Critical Strike\r\n371, of the Earthfall, Critical Strike/Haste\r\n[/code][/div][/li]\r\n[/ul]\r\n\r\n[h3]Spells[/h3]\r\n\r\n[ul]\r\n[li][b]Level[/b] – lvl\r\nEnter the character\'s level, useful for scaling spells![pad][/li]\r\n[li][b]Buff[/b] – buff\r\nUse this parameter to display the buff of the current spell, instead of the regular tooltip.[/li]\r\n[/ul]\r\n\r\n[h3]Achievements[/h3]\r\n\r\n[ul]\r\n[li][b]Earned By[/b] – who & when\r\nUse both parameters to display \"Achievement earned by <who> on <when>\", e.g: who=Maelstrata&amp;when=1273022820000[/li]\r\n[/ul]\r\n\r\n[h3]Example[/h3]\r\n\r\nThis is an example of an item link with several options specified at once (gems, enchant and item set pieces):\r\n\r\n[code]<a href=\"HOST_URL?item=25697\" class=\"q3\" rel=\"gems=23121&amp;ench=2647&amp;pcs=25695:25696:25697\">[Felstalker Bracers]</a>[/code]\r\n\r\nThe result: [html]<a href=\"HOST_URL?item=25697\" class=\"q3\" rel=\"gems=23121&amp;ench=2647&amp;pcs=25695:25696:25697\">[Felstalker Bracers]</a>[/html]\r\n\r\n[h3]Hiding Tooltip Data[/h3]\r\nOur tooltips come with some information that the in-game tooltips do not display. If you\'d like your tooltips to exactly resemble the in-game ones, you can disable the following components on our\'s: \r\n[ul][li][b]reagents[/b]: At the very bottom of recipe tooltips, we add a line describing the recipe\'s reagents \"Requires Gromsblood (2), Crystal Vial (1)\"[/li]\r\n[li][b]sellprice[/b]: Blizzard added the item sell price to all item tooltips, but you can hide it as well by setting this option.[/li][/ul]\r\n\r\nYou can disable it by adding a \"hide\" section to the tooltip HTML code in the <head> section and then specifying what parts you\'d like hidden. If you wanted to hide only the Dropped By and Drop Chance information, your code would be as follows:\r\n[code]<script>var aowow_tooltips = { \"colorlinks\": true, \"iconizelinks\": true, \"renamelinks\": true, \"hide\": { \"reagents\": true, \"sellprice\": true} }</script>[/code]\r\n[/tab]\r\n\r\n[tab name=\"XML feeds\"]\r\n\r\n[h3]Items[/h3]\r\nAlso available are our item XML feeds. Every item in the database has a corresponding XML feed. You can reach those feeds either by ID or by name. For example:\r\n\r\n[ul]\r\n[li]By ID: HOST_URL?item=52021&xml[/li]\r\n[li]By name: HOST_URL?item=iceblade%20arrow&xml[/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[tab name=\"Other resources\"]\r\n\r\nInterested in using our script in your forum? Check out [url=http://wowhead.com/forums&topic=3464]this thread[/url] for information on implementing it on many popular forum systems (phpBB, vBulletin, etc.) or check out the handy guides written by Wowheads users:\r\n\r\n[ul]\r\n[li][url=http://wowhead.com/forums&topic=3464#p37094]vBulletin[/url][/li]\r\n[li]phpBB: [url=http://wowhead.com/forums&topic=3464#p37492]2.x.x[/url] - [url=http://wowhead.com/forums&topic=3464.6#p58403]2.x.x Mod Version[/url] | [url=http://wowhead.com/forums&topic=14347&p=126922]3.0[/url] [small]by craCkpot[/small] - [url=http://wowhead.com/forums&topic=3464#p37204]3.0[/url] [small]by marcimi[/small] - [url=http://wowhead.com/forums&topic=3464.3#p42858]3.0 Mod Version[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464#p37618]Simple Machines Forum (SMF)[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.3&p=4080#p40631]Invision Power Board (IPB)[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.3&p=42952#p42952]WordPress Blog[/url] ([url=http://wowhead.com/forums&topic=3464.4#p43652]Plugin Version[/url])[/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.7&p=63338#p61443]PHP Nuke-Evolution[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.3#p43232]MyBB[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.6#p48648]TikiWiki[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.6#p49640]YaBB[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.5#p46801]Drupal[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=3464.3#p42456]PunBB[/url][/li]\r\n[li][url=http://wowhead.com/forums&topic=10938]Dojo[/url][/li]\r\n[/ul]\r\n\r\n[/tab]\r\n\r\n[/tabs]',NULL),(-16,0,0,'[menu tab=2 path=2,16]\r\n\r\nThe code below will produce an iframe that contains the Aowow logo and a search box.\r\n\r\n[code]<script type=\"text/javascript\">var aowow_searchbox_format = \"160x200\"</script>\r\n<script type=\"text/javascript\" src=\"STATIC_URL/widgets/searchbox.js\"></script>[/code]\r\n\r\n[h3]Parameters[/h3]\r\n\r\n[ul]\r\n[li][b]aowow_searchbox_format[/b] – String that specifies how big the iframe should be. The following values can be used:\r\n[pad]\r\n[table width=100%]\r\n[tr]\r\n[td width=20% align=center valign=top]\r\n\"160x200\"\r\n[img src=STATIC_URL/images/help/searchbox/preview-160x200.png]\r\n[/td]\r\n[td width=20% align=center valign=top]\r\n\"120x200\"\r\n[img src=STATIC_URL/images/help/searchbox/preview-120x200.png]\r\n[/td]\r\n[td width=20% align=center valign=top]\r\n\"160x120\"\r\n[img src=STATIC_URL/images/help/searchbox/preview-160x120.png]\r\n[/td]\r\n[td width=20% align=center valign=top]\r\n\"150x120\"\r\n[img src=STATIC_URL/images/help/searchbox/preview-150x120.png]\r\n[/td]\r\n[td width=20% align=center valign=top]\r\n\"120x120\"\r\n[img src=STATIC_URL/images/help/searchbox/preview-120x120.png]\r\n[/td]\r\n[/tr]\r\n[/table]\r\n[/li]\r\n[/ul]\r\n\r\n[h3]Tips[/h3]\r\n\r\n[ul]\r\n[li]You can style the iframe (e.g. adding a border) by using the following class name in your CSS code:\r\n[code].aowow-searchbox { ... }[/code][/li]\r\n[/ul]',NULL),(-8,0,0,'[menu tab=2 path=2,8]\r\n\r\n[div float=right align=right][img src=STATIC_URL/images/help/searchplugins/ss-searchsuggestions.png]\r\n[small]Also features search suggestions![/small]\r\n[/div]\r\n\r\nSearch plugins make it easy to search the database right from your browser!\r\n\r\n[toc h3=false]\r\n\r\n[h2][img src=STATIC_URL/images/help/searchplugins/firefox.gif border=0 margin=5 float=left][img src=STATIC_URL/images/help/searchplugins/ie.gif border=0 float=left]Firefox / Internet Explorer[/h2]\r\n\r\n[div clear=left][/div]Click on the button below to install the search plugin in your browser.\r\n\r\n[pad]\r\n\r\n[script]\r\nfunction addPlugin()\r\n{\r\n    try {\r\n        if(!$.browser.msie && !$.browser.mozilla) {\r\n            throw(\'FAIL\');\r\n        }\r\n\r\n        window.external.AddSearchProvider(\'STATIC_URL/download/searchplugins/aowow.xml\');\r\n    }\r\n    catch(e)\r\n    {\r\n        alert(\'This feature is only for Firefox 2+ and Internet Explorer 7+.\');\r\n    }\r\n}\r\n[/script]\r\n\r\n[html]<a href=\"javascript:;\" class=\"button-red\" onclick=\"addPlugin()\" style=\"float: left; margin-left: 0\"><em><b><i>Install plugin</i></b><span>Install plugin</span></em></a>[/html]\r\n\r\n[div clear=left][/div][pad]\r\n\r\n[h2][img src=STATIC_URL/images/help/searchplugins/opera.gif border=0 float=left]Opera[/h2]\r\n\r\n[div clear=left][/div]\r\n\r\n[ul]\r\n[li]Right-click on the search box on the [url=/]homepage[/url].[/li]\r\n[li]Select \"Create Search\" in the menu.[/li]\r\n[li]Fill the form as follows:\r\n[pad]\r\n[img src=STATIC_URL/images/help/searchplugins/ss-opera.png border=0]\r\n[pad][/li]\r\n[li]Save your changes, and you\'ll be able to perform Aowow searches by typing \"wh\" followed by the search terms in the address bar (e.g. wh sword).[/li]\r\n[/ul]\r\n',NULL),(-99,0,2,'[tooltip name=AO815][b][color=q4]AO-815 Moteur Principal de Stabulation[/color][/b]\n[color=white]Lié lorsque utilisé\nUnique[/color]\n[color=q2]Utilise: Appelle le pouvoir de l\'Interwebs pour\ninvoquer l\'information demandé à Aowow.[/color]\n[color=q]\"En tout cas, c\'est ce que c\'est supposé faire...\"[/color][/tooltip]Quoi? Comment avez-vous... oubliez ça!\n\nIl semblerait que la page demandée n\'ait pas été trouvée. En tout cas, pas dans cette dimension.\n\nPeut-être que quelques réglages au [span class=tip tooltip=AO815][color=q4][u][AO-815 Moteur Principal de Stabulation][/u][/color][/span] pourraient résulter en l\'apparition soudaine de la page![pad][pad]\n\nOu vous pouvez essayer de [url=?aboutus#contact]nous contacter[/url] - la stabilité du AO-815 est discutable et vous ne voudriez pas un autre accident...\n\n[h2]Liens[/h2]\n[ul]\n[li]Retour à la [url=?]page d\'accueil[/url][/li]\n[li][url=?forums&board=1]Forum[/url] de feedback[/li]\n[/ul]',NULL),(-3,0,0,'[small]no questions have been asked yet[/small]\r\n\r\nbesides .. yes, i\'m insane.',NULL),(-7,0,0,'[small]this page for example[/small]',NULL),(-1,0,0,'[h3]This is [s]Sparta![/s] [u]Aowow[/u][/h3]\r\n\r\nA project for private servers to sensibly display the vast amount of data a private server contains.\r\n\r\nBuilt with TrinityCore in my neck, but i\'m trying to get away from that .. some time.\r\nWith it\'s own data structure it shouldn\'t be too hard to write a converter for MaNGOS, Ascent or whatever software you prefere.\r\n\r\nThe expected version is 3.3.5 (12340), everything else will get messy.',NULL),(-99,0,3,'[tooltip name=AO815][b][color=q4]AO-815 Großkonfabulierungsmaschine[/color][/b]\n[color=white]Bei Benutzung gebunden\nEinzigartig[/color]\n[color=q2]Benutzen: Ersucht die Mächte der Internetze darum,\nAowow die benötigten Informationen zukommen zu lassen.[/color]\n[color=q]\"Das sollte es im Prinzip eigentlich tun...\"[/color][/tooltip]Was? Wie hast du... vergesst es!\n\nAnscheinend konnte die von Euch angeforderte Seite nicht gefunden werden. Wenigstens nicht in dieser Dimension.\n\nVielleicht lassen einige Justierungen an der [span class=tip tooltip=AO815][color=q4][u][AO-815 Großkonfabulierungsmaschine][/u][/color][/span] die Seite plötzlich wieder auftauchen![pad][pad]\n\nOder, Ihr könnt es auch [url=?aboutus#contact]uns melden[/url] - die Stabilität des AO-815 ist umstritten, und wir möchten gern noch so ein Problem vermeiden...\n\n[h2]Links[/h2]\n[ul]\n[li]Zur [url=?]Titelseite[/url] zurückkehren[/li]\n[li][url=?forums&board=1]Forum[/url] für Rückmeldungen[/li]\n[/ul]',NULL),(-99,0,6,'[tooltip name=AO815][b][color=q4]Dispositivo de confabulación suprema AO-815[/color][/b]\n[color=white]Se liga al usar\nÚnico[/color]\n[color=q2]Uso: Clama a los poderes de Internet para\ninvocar información requerida a Aowow.[/color]\n[color=q]\"Al menos, eso es lo que se supone que hace...\"[/color][/tooltip]¿Pero qué? ¿Cómo? .... ¡olvídalo!\n\nParece que la página que buscas no pudo ser encontrada. Al menos, no en esta dimensión.\n\n¡Quizá un par de ajustes al [span class=tip tooltip=AO815][color=q4][u][Dispositivo de confabulación suprema AO-815][/u][/color][/span] puede que hagan que la página aparezca de repente![pad][pad]\n\nO, puedes intentar [url=?aboutus#contact]contactar con nosotros[/url] - la estabilidad del AO-815 es debatible y no queremos otro accidente...\n\n[h2]Enlaces[/h2]\n[ul]\n[li]Volver a la [url=?]página principal[/url].[/li]\n[li]Foro del [url=?forums&board=1]feedback[/url].[/li]\n[/ul]',NULL),(-99,0,0,'[tooltip name=AO815][b][color=q4]AO-815 Major Confabulation Engine[/color][/b]\n[color=white]Binds when used\nUnique[/color]\n[color=q2]Use: Calls on the powers of the Interwebs to\nsummon requested information to Aowow.[/color]\n[color=q]\"At least, that\'s what it\'s supposed to do...\"[/color][/tooltip]What? How did you... nevermind that!\n\nIt appears that the page you have requested cannot be found. At least, not in this dimension.\n\nPerhaps a few tweaks to the [span class=tip tooltip=AO815][color=q4][u][AO-815 Major Confabulation Engine][/u][/color][/span] may result in the page suddenly making an appearance![pad][pad]\n\nOr, you can try [url=?aboutus#contact]contacting us[/url] - the stability of the AO-815 is debatable, and we wouldn\'t want another accident...\n\n[h2]Links[/h2]\n[ul]\n[li]Return to the [url=?]homepage[/url][/li]\n[li]Feedback [url=?forums&board=1]forum[/url][/li]\n[/ul]',NULL),(-13,7,0,'Here we have quite a few nifty markup tags that users can insert into their comments and forum posts to improve the style and easily link to database entries! Many of these tags can easily inserted using the corresponding icon or dropdown menu found above the text box. We\'ve put together this quick reference for all of these handy tags for you guys so you can get on your way to making high quality posts and comments!\n\n[h2]Formatting Tags[/h2]\n[h3]Bold[/h3]\n\\[b]text[/b]\n\n[h3]Line break[/h3]\n\\[br] -> inserts a line break.\n\n[h3]Code[/h3]\n\\[code]text[/code] -> creates a block of text that ignores markup and uses a monospace font.\n\n[h3]Horizontal Rule[/h3]\n\\[hr] -> creates a horizontal rule\n\n[h3]Italics[/h3]\n\\[i]text[/i] -> [i]text[/i]\n\n[h3]Preformatted text[/h3]\n\\[pre]text[/pre] -> shows text with all whitespace preserved in a monospace font, but allows markup\n\n[h3]Strikethrough[/h3]\n\\[s]text[/s] -> [s]text[/s]\n\n[h3]Small text[/h3]\n\\[small]text[/small] -> [small]text[/small]\n\n[h3]Subscript[/h3]\n\\[sub]text[/sub] -> [sub]text[/sub]\n\n[h3]Superscript[/h3]\n\\[sup]text[/sup] -> [sup]text[/sup]\n\n[h3]Underline[/h3]\n\\[u]text[/u] -> [u]text[/u]\n\n[h2]Database Tags[/h2]\n\n\n[b]For all database tags:[/b]\nOptional attributes: site/domain (both work identically, only use one)\nValid options are: www (default), en, de, es, fr, ru.\nThe purpose of these is to link to localized versions of items with the pretty db tags.\n[b]Example:[/b] \\[achievement=3579 domain=ru] -> [achievement=3579 domain=ru] \n\n[h3]Achievements[/h3]\n\\[achievement=3579] -> [achievement=3579]\n\n[h3]Classes[/h3]\n\\[class=11] -> [class=11]\n\n[h3]Events[/h3]\n\\[event=1] -> [event=1]\n\n[h3]Factions[/h3]\n\\[faction=749] -> [faction=749]\n\n[h3]Items[/h3]\n\\[item=12345] -> [item=12345]\n\nTo hide the icon: \\[item=12345 icon=false] -> [item=12345 icon=false]\n\n[h3]Itemsets[/h3]\n\\[itemset=699] -> [itemset=699]\n\n[h3]NPCs[/h3]\n\\[npc=32906] -> [npc=32906]\n\n[h3]Objects[/h3]\n\\[object=1733] -> [object=1733]\n\n[h3]Pets[/h3]\n\\[pet=45] -> [pet=45]\n\n[h3]Quests[/h3]\n\\[quest=7981] -> [quest=7981]\n\n[h3]Races[/h3]\n\\[race=11] -> [race=11]\n\n[b]To specify the gender of the icon:[/b] \\[race=11 gender=1] -> [race=11 gender=1] - 0 is male, 1 is female\n\n[h3]Skills[/h3]\n\\[skill=171] -> [skill=171]\n\n[h3]Spells[/h3]\n\\[spell=52398] -> [spell=52398]\n\\[spell=31565 buff=true] -> [spell=31565 buff=true]\n\n[h3]Statistics[/h3]\n\\[statistic=1076] -> [statistic=1076]\n\n[h3]Zones[/h3]\n\\[zone=3959] -> [zone=3959]\n\n[h2]HTML Tags[/h2]\n\n[h3]Anchor[/h3]\n\\[anchor=text] -> creates an anchor with the name \\\"text\\\" at this point.\n\n[h3]Ordered List[/h3]\n\\[ol]\\[li]list item[/li][/ol] -> [ol][li]list item[/li][/ol]\n\n[h3]Tables[/h3]\n[b]\\[table][/b]\nBorder: \\[table border=2]\nSpacing: \\[table cellspacing=2]\nPadding: \\[table cellpadding=2]\nWidth: \\[table width=500px] - Valid units are px, em, %\n\n[b]\\[tr][/b] - No attributes\n\n[b]\\[td][/b]\nAlign: \\[td align=right] - Valid options are left, right, center, justify\nVertical align: \\[td valign=baseline] - Valid options are top, middle, bottom, baseline\nColumn span: \\[td colspan=2]\nRow span: \\[td rowspan=2]\nWidth: \\[td width=500px] - Valid units are px, em, %\n\n[h3]Unordered List[/h3]\n\\[ul]\\[li]list item[/li][/ul] -> [ul][li]list item[/li][/ul]\n\n[h3]URLs[/h3]\n\\[url=http://www.wowhead.com]Wowhead[/url] -> [url=http://www.wowhead.com]Wowhead[/url]\n\\[url]http://www.wowhead.com[/url] -> [url]http://www.wowhead.com[/url]\n\\[url=http://www.google.com rel=item=12345]Rel link[/url] -> [url=http://www.google.com rel=item=12345]Rel link[/url]',NULL),(8,589,0,'The [b]Wintersaber Trainers[/b] is an Alliance-only faction consisting of only two night elven NPCs that can both be found in [zone=618]. Currently, the only questgiver is [npc=10618], who is located at the top of Frostsaber Rock in Winterspring. Upon reaching exalted with this faction, Rivern will sell a special mount, the [item=13086].\n\nThis faction\'s mount is the only epic mount (100% riding speed) attainable in the game which only requires 75 riding skill (and thus only costs 90 Gold). The faction is noted for having no Horde counterpart and having the longest and most repetitive reputation grind of the entire game. The first quest can be attained at level 58, while the other two are attainable at level 60.\n\n[h3]Reputation[/h3]\nReputation with the Wintersaber Trainers can only be obtained through three repeatable quests. There are no faction items or mobs that reward repuation directly.\n\n[b]Neutral 0 to 1500[/b]\nOnly one repeatable quest will available at first, so until neutral 1500/3000 is reached the [quest=4970] quest should be repeated. Any Shardtooth and Chillvind mob in Winterspring will drop these. This quest should be done solo as the drop rates are low and not shared if others have the quest.\n\n[b]Neutral 1500 to Exalted[/b]\nHalfway through neutral the [quest=5201] quest will be available. This quest requires to kill 10 Winterfall mobs in the Winterfall Village, just east of Everlook. If the quest [quest=8464] has been done with the [faction=576], [item=21383] can drop from the Winterfall mobs. If a player wants both reputations, saving these until revered with Timbermaw Hold will result in a lot of \"free\" reputation.\n\nThis quest can be done in groups for increased speed. Players grinding either Wintersaber Trainers or Timbermaw Hold reputation can often be found in the Winterfall Village. Even with an epic mount, the travel to and from Winterfall Village takes up much time. There are tigers among the route who will daze you, which will result in a demount, this should be avoided (but can be hard as they\'ll catch up with you on a 60% mount). Usually this quest is repeated all the way to exalted, ignoring the third quest. \n\n[b]Honored to Exalted[/b]\nAt honored the third quest [quest=5981] is available. The quest requires the player to kill 8 Frostmaul giants. They are a lot harder than the Winterfall mobs and the travel lengths are quite longer. This quest is usually skipped, and instead Winterfall Intrusion is repeated.\n\nDue to some players grinding Timbermaw Hold reputation, in Winterfall Village among other places, this quest can indeed turn out to be a faster reputation reward than the Winterfall Intrusion one.',NULL),(8,609,0,'The [b]Cenarion Circle[/b] is an organization of druids, both tauren and night elf, named after Cenarius. Its members are dedicated to protecting nature and restoring the damage done to it by malevolent forces.\n\nThe Circle has many posts, but their main home is the town of Nighthaven in the [zone=493]. Druids learn the spell [spell=18960] at level 10, but anyone else will have to make it to [zone=361] and find a way through the Timbermaw Furbolg tunnels.\n\nThe Circle\'s other major presence is in [zone=1377], where they combat the Silithid, the Qiraji, and Twilight\'s Hammer. Valor\'s Rest and Cenarion Hold serve as their bases in the hostile land, and offer many opportunities to adventurers seeking to aid the druids.\n\n[h3]Notable Members[/h3]\n[ul][li][npc=11832], son of Cenarius[/li][li][npc=3516], leader of the night elven druids[/li][li][npc=5769], leader of the tauren druids[/li][/ul]\n\n[h3]Reputation[/h3]\nThere are several ways to gain reputation with the Cenarion Circle. Aside from the available [url=?quests&filter=cr=1;crs=609;crv=0]quests[/url], you may do the following to gain reputation:[ul][li]Raid the [zone=3429]. This is by far the fastest way to gain reputation, as a full clear can net over 2000 reputation.[/li][li]Kill twilight cultists. These stop yielding reputation when you reach the end of friendly for [npc=11880] and [npc=11881], and at the end of honored for [npc=15201].[/li][li]Turn in [item=20404]. These drop off the cultists, and yield 250 reputation for 10 texts.[/li][li]Turn in [item=20513], [item=20514], and [item=20515]. These drop off the minibosses that are summoned at the windstones using the [itemset=492].[/li][li]Perform the [quest=8507]. These are either [url=?search=logistics+task+briefing]Logistics quests[/url], [url=?search=combat+task+briefing]Combat quests[/url], or [url=?search=tactical+task+briefing]Tactical quests[/url]. The badges you earn from these quests may then be turned in for additional reputation, if you chose to forsake the rewards.[/li][li]Collect [object=181598] from the zone and turn it in to your faction NPC.[/li][/ul]',NULL),(8,729,0,'[b]Frostwolf Clan[/b], along with [npc=11946], lived along the [zone=36] practicing shamanism, and having Frost Wolves as their companions. The dwarven expedition known as the [faction=730] have started an expedition in the Frostwolf territory to excavate the valley and mine its veins, a transgression to the orcs who inhabited Alterac. This provoked a slaughter of the first expedition, and started the battle for [zone=2597].\n\n[h3]Reputation[/h3]\nPlayers can earn reputation in this faction by participating in the Alterac Valley battleground by doing various tasks as well as killing members of the opposite faction, the Stormpike Guard.\n\nYou are granted the player title [title=47] once exalted with the Frostwolf Clan and the other two battleground factions, [faction=889] and [faction=510].',NULL),(8,730,0,'[b]Stormpike Guard[/b] is the Alliance faction in the [zone=2597] battleground. They are an expedition of dwarves of the Stormpike Clan, native to the \"valleys of Alterac\" in [zone=36]. The Stormpikes\' search for relics of their past and harvesting of resources in Alterac Valley have led to open war with the the orcs of the [faction=729] dwelling in the southern part of the valley. They were also issued with a \"sovereign imperialistic imperative\" by [npc=2784] to take the valleys of Alterac for [zone=1537]. \n\nThe main Stormpike base is Dun Baldar, where their leader, [npc=11948], resides with his marshals. His second in command, [npc=11949], is found south of Dun Baldar, at Stonehearth Outpost.\n\n[h3]Reputation[/h3]\nPlayers can earn reputation in this faction by participating in the Alterac Valley battleground by doing various tasks as well as killing members of the opposite faction, the Frostwolf Clan.\n\nYou are granted the player title [title=48] once exalted with Stormpike Guard and the other two battleground factions, [faction=890] and [faction=509].',NULL),(8,749,0,'The [b]Hydraxian Waterlords[/b] are elementals that have made their home on the islands east of [zone=16]. Sworn enemies of the armies of [npc=11502]. Historically servants of the Old Gods, the four Elemental Lords served the gods with undying loyalty. The minions of Neptulon the Tidehunter were numerous and mindless. It is not yet known how [npc=13278] broke free of his lord\'s control (if indeed he has), or what is his ultimate goals are, but the Water elementals are the only elementals that do not attack the mortal races with abandonment.\n\nLocated on a remote island in the far east of Azshara, Duke Hydraxis offers some quests. The first two require killing various elementals in [zone=139] and [zone=1377]. Increased faction with the Waterlords opens up additional quests leading into the [zone=2717]. Any items obtained from the Hydraxian Waterlords, are obtained from its various quests.\n\nCompleting the questline allows players to obtain [item=17333] used to douse the runes found near most bosses in Molten Core. This is required to summon [npc=12018], the penultimate boss, and, after his defeat, to summon Ragnaros himself. Since there are seven runes, any raid needs at least seven players that bring a quintessence if they wish to finish the instance. Since most of the questline takes place within Molten Core, any raider can complete this task with little more than some traveling and an [zone=1583] run.\n\n[h3]Reputation[/h3]\nRepuation is gained through slaying the following elemental enemies of the waterlords.[ul][li][npc=11746] - 5 reputation, lasts until honored.[/li][li][npc=11744] - 5 reputation, lasts until honored.[/li][li][npc=7032] - 5 reputation, lasts until honored.[/li][li][npc=9017] - 15 reputation, lasts until revered.[/li][li][npc=14478] - 25 reputation, lasts until revered.[/li][li][npc=9816] - 50 reputation, lasts until revered.[/li][li][npc=11658], [npc=11673], [npc=12101] and [npc=11668] - 20 reputation, lasts until revered.[/li][li][npc=11659] and Lava Pack ([npc=12100], [npc=12076], [npc=11667], [npc=11666]) - 40 reputation, lasts until revered.[/li][li][npc=12118], [npc=11982], [npc=12259], [npc=12057], [npc=12056], [npc=12264], [npc=12098] - 100 reputation, lasts until exalted.[/li][li][npc=11988] - 150 reputation, lasts until the end of exalted.[/li][li][npc=11502] - 200 reputation, lasts until the end of exalted.[/li][/ul]Reaching revered status with the Hydraxian Waterlords allows players to obtain the [item=22754], which replenishes itself and thus eliminates the need to return to Hydraxis to obtain a new quintessence every week.',NULL),(8,809,0,'The [b]Shen\'dralar[/b] are the faction of the Night Elves remaining in [zone=2557]. They are a group of high practitioners of arcane magic in order of their former Queen Azshara, and her followers, the Highborne. They have been living in Eldre\'Thalas (previous name of Dire Maul) since the Great Sundering. They are few, but their knowledge and mystic power are great, referring to things players think are powerful such as [b]Arcanums[/b] and [b]Librams[/b] as mere cantrips.\n\nTheir leader, [npc=11486], was in charge and oversaw the construction of the pylons to contain the great demon [npc=11496] and syphon his demonic power. After many long years though, it began to dwindle so he started killing the remaining night elves to maintain energy. So their spirits come to adventurers and ask them to kill him. There are very few of the original inhabitants left alive.\n\n[h3]Reputation[/h3]\nReputation can be gained by turning repeatedly in the three Librams of Dire Maul ([item=18333], [item=18334], [item=18332]). Turning in the following class books also gives some reputation:[ul][li][item=18357] - Warrior[/li][li][item=18363] - Shaman[/li][li][item=18356] - Rogue[/li][li][item=18360] - Warlock[/li][li][item=18362] - Priest[/li][li][item=18358] - Mage[/li][li][item=18364] - Druid[/li][li][item=18361] - Hunter[/li][li][item=18359] - Paladin[/li][li][item=18401] - Warrior & Paladin[/li][/ul]Both class books and librams give 500 Reputation points each.',NULL),(8,889,0,'[b]Warsong Outriders[/b] is an orcish clan formerly led by [npc=18076], in which the clan was named after. The clan\'s Warsong Outriders form the Horde faction in the [zone=3277] battleground, where they are attempting to defend their logging operations in [zone=331] from the [faction=890].\n\nOne of the strongest and most violent clans, the Warsong Clan was also one of the most distinguished clans on Draenor and was able to evade Alliance expedition forces at every turn. Depicted as Grunts, they have mastered the use of swords and blades and a few of them have even attained the rank of a Blademaster.\n\n[h3]Reputation[/h3]\nReputation is gained through participation in the Warsong Gulch battleground. You gain 35 reputation each time your side captures a flag. This reputation gain is increased to 45 on holiday weekends.\n\nYou are granted the player title Conqueror once exalted with Warsong Outriders and the other two battleground factions, [faction=510] and [faction=729].',NULL),(8,890,0,'[b]Silverwing Sentinels[/b] are the Alliance faction for the [zone=3277] battleground. The night elves, who have begun a massive push to retake the forests of [zone=331] are now focusing their attention on ridding their land of the [faction=889] once and for all. And so, the Silverwing Sentinels have answered the call and sworn that they will not rest until every last orc is defeated and cast out of Warsong Gulch.\n\n[h3]Reputation[/h3]\nReputation is gained through participation in the Warsong Gulch battleground. You gain 35 reputation each time your side captures a flag. This reputation gain is increased to 45 on holiday weekends.\n\nYou are granted the player title [title=48] once exalted with Silverwing Sentinels and the other two battleground factions, [faction=730] and [faction=509].',NULL),(8,909,0,'The [b]Darkmoon Faire[/b] is a mysterious traveling carnival, which roams not only Azeroth but Outland as well. Led by the inimitable [npc=14823], a gnome of dubious heritage and unknown providence, the Faire brings fun, games, prizes, and exotic trinkets of unexpected power to [zone=215], [zone=12], or [zone=3519] each month.\n\nA variety of amusements can be had by the discerning fairegoer, but the most common attraction is the ticket redemption. A variety of merchants at the Faire collect items from around the worlds in exchange for [item=19182]. The tickets can, in turn, be saved up and turned in for prizes of varying worth and power. Several different ticket distributors are posted around the Faire, offering tickets for crafted items made by Leatherworkers, Blacksmiths, or Engineers as well as items gathered in the wild such as [item=11404] and [item=19933]. Tickets can be redeemed for many things, from flowers to hold in the off-hand to necklaces of great power.\n\nMany adventurers seek out the Darkmoon Faire to turn in the mystical [url=?items=15.0&filter=minle=1;cr=107;crs=0;crv=Combine+the+Ace]Darkmoon Cards[/url]. Darkmoon Cards come in eight suits, each of which has cards from Ace to Eight. Combining all cards in a suit produces a deck, which will start a quest to return that deck to the Darkmoon Faire. Each of the eight decks produces a different [url=?items=4.-4&filter=na=Darkmoon+Card]trinket[/url] with a different effect, some of which are quite powerful.\n\nThe Darkmoon Faire\'s usual schedule has it arriving on site on the first Friday of the month. For the weekend, the carnies will be seen setting up the midway, and the Faire will actually start early on the following Monday.',NULL),(8,910,0,'The [b]Brood of Nozdormu[/b] is a faction consisting of the Bronze Dragonflight. Their leader [npc=15192] can be found outside the [b]Caverns of Time[/b], with many of its agents flying in the sky of [zone=1377].\n\nIn order to open the gates of [b]Ahn\'Qiraj[/b], one champion must complete a long quest line for the bronze dragon Anachronos. This reputation is also relevant in the [zone=3428]; to obtain epic quest gear and rings.\n\n[h3]Reputation[/h3]\nPlayers begin at 0/36000 hated, the lowest level of reputation possible.\n\nBrood of Nozdormu reputation can be earned through killing bosses in both Ahn\'Qiraj instances, killing monsters inside the Temple of Ahn\'Qiraj, and doing quests related to the dungeons. You can also farm [item=20384], though this will take a lot longer, and requires one to have obtained the [item=20383] in [zone=2677] for the [item=21175] quest chain.\n\nKilling trash in the Temple of Ahn\'Qiraj can only get you to 2999 / 3000 Neutral, at which point reputation can only be further advanced through quests and handing in [item=21229] and [item=21230]. You may want to save all the insignias until after you are Neutral, since at that point gaining reputation becomes much more difficult.',NULL),(8,911,0,'[b]Silvermoon City[/b] is the capital of the blood elves, located in the northeastern part of the [zone=3430] within the kingdom of Quel\'Thalas. The breathtaking capital city of the blood elves may rival the dwarven capital of [zone=1537] as the world\'s oldest, still standing, capital. Recently rebuilt from the devastating blow dealt by the evil Prince Arthas, the city houses the largest population of blood elves left on Azeroth.[pad]Silvermoon today is only the eastern half of the original city; the western half was almost completely destroyed by the Scourge during the Third War. Falconwing Square, the second blood elf town, is the only part of western Silvermoon remaining in blood elf control. The Dead Scar (the path taken by Arthas Menethil and his undead army on the quest to resurrect Kel\'Thuzad, which carves through all of Eversong Woods) separates the rebuilt Silvermoon from the ruins of the western half. Interestingly, the Ruins of Silvermoon house no undead, instead they contain [url=?npcs&filter=na=wretched;maxle=8]Wretched[/url] and malfunctioning [npc=15638]. As it stands, what remains of Silvermoon City is still bigger than current Horde cities.\n\n[h3]History[/h3]\nThe city of Silvermoon was founded by the high elves after their arrival in Lordaeron thousands of years ago. The city was constructed out of white stone and living plants in the style of the ancient Kaldorei Empire. The city contained the famous Academies of Silvermoon as a center for the learning of Arcane Magic and Sunstrider Spire, a majestic palace home to the Royal family of the high elves. The Convocation of Silvermoon (also known as \"The Silvermoon Council\"), the ruling body of the high elves was also based here. Across a stretch of ocean to the north is the island that contains the Sunwell.[pad]Although Silvermoon itself was left relatively unscathed from the second war, in the third war the Death Knight Arthas led the Scourge into the city, attacking it on his quest to reach the Sunwell. The High Elven King was slain and the majority of the population killed. Scourge forces held the city for a time but abandoned it after the depleting of its resources.[pad]Though the city was attacked by the Scourge, it is not as destroyed as one might think. Though many of its plants are dead, and the occasional dead body is sprawled across the cobblestone, the city was immune to the fire and destruction. Silvermoon now resembles a ghost town, intact, but eerily abandoned. Nevertheless, treasure hunters often frequent Silvermoon to try and find some of the valuable artifacts that the elves left behind before they deserted the city, but the ghosts of Silvermoon\'s past inhabitants prevents anyone from taking anything.\n\n[h3]Reputation[/h3]\nA comprehensive list of quests that grant Silvermoon reputation can be found [url=?quests&filter=maxle=69;cr=1;crs=911;crv=0#00Mz]here[/url].[pad][npc=20612] is the quest giver for the repeatable [item=14047] quest that must be completed by non-blood elf Horde players in order to reach exalted and gain the ability to ride [url=?items=15.5&filter=na=hawkstrider]hawkstriders[/url], the mount of the blood elf race.',NULL),(8,922,0,'[b]Tranquillien[/b] is a joint blood elf and Forsaken town and separate faction in the [zone=3433].\n\n[h3]History[/h3]\nAs the Scourge made their way to the Sunwell, the elves had no choice but to retreat. The town of Tranquillien was abandoned by the retreating elves. The town is now used by the blood elves and the Forsaken as their base of operation to launch attacks aiming to take back the Ghostlands from the Scourge. However, the city is surrounded by the Scourge and even couriers have trouble getting past the enemy to reach the town. The undead forces of Deatholme are the most dangerous threat to the town.\n\n[h3]Reputation[/h3]\nUnlike most starting areas, the town of Tranquillien is its own faction. All quests you do for them will garner at least 1000 reputation apiece. [npc=16528] acts as the Tranquillien quartermaster. Vredigar can be found near the inn and will sell various [span class=q2]uncommon[/span] items, and even a [span class=q3]rare[/span] cloak when you reach exalted! If you complete all of the Tranquillien quests, you should be exalted by approximately level 20.[pad]There are a variety of quests mostly concerning reclaiming overrun villages, investigating undead and helping around. The \"end\" of the quest-revealed lore surrounding Tranquillien culminates with the quest to kill [npc=16329].',NULL),(8,930,0,'[b]Exodar[/b] is the faction associated with [zone=3557], the enchanted capital city of the draenei, built out of the largest husk of their crashed dimensional ship of the same name. It is located in the westernmost part of [zone=3524]. The Exodar faction leader is [npc=17468], who is located near the battlemasters in the Vault of Lights.\n\nThe history of the Exodar is a short one, as the draenei only recently raised it around the husk of their crashed ship, which is still smoking from the impact. The Exodar was once a naaru satellite structure around the dimensional fortress [url=?search=tempest+keep#z0z]Tempest Keep[/url]. The Exodar contains a large amount of technological wonders (due to its origins lying with the Tempest Keep) such as magically enchanted \"wires\" which transport holy energy throughout the ship to power the heating and lighting, as well as augmenting the draeneis\' already considerable powers.\n\n[h3]Reputation[/h3]\nAs with other major factions associated with the main races, Exodar reputation may be gained by doing repeatable cloth turn-in quests, killing the opposing faction in [zone=2597] (the blood elves), and doing the appropriately related quests. At honored, the player can purchase items from Exodar related vendors for 10% less, and at exalted, the player, if not a draenei, can purchase the [url=?items=15.5&filter=na=elekk;cr=93:92;crs=2:1;crv=0:0]various mounts[/url] sold by the Exodar. The cloth turn-in quests are available from [npc=20604] [small]<Alliance Cloth Quartermaster>[/small].',NULL),(8,932,0,'[b]The Aldor[/b] are an ancient order of draenei priests who revere the naaru, and to this day they assist the naaru known as [faction=935] in their battle against [npc=22917] and the Burning Legion. They are found primarily in [zone=3703] and [zone=3520]. Though they have suffered much at the hands of the blood elves who later became [faction=934], they have put aside open warfare for the sake of the Sha\'tar. The Aldor\'s most holy temple lies on the Aldor Rise, overlooking the city from the west.\n\nMost players will start at neutral with the Aldor. [npc=18166] in Shattrath City will give players an initial quest to become friendly with the Aldor or the Scryers. This choice is reversible if players feel the need. Draenei players will be friendly with the Aldor and hostile with the Scryers, whereas blood elf players will be hostile to the Aldor and friendly to the Scryers.\n\n[npc=19321] and [npc=20807] are located in the Aldor bank on the northern edge of the Terrace of Light. The Shrine of Unending Light on Aldor Rise is home to [npc=20616]Asuur [small]<Keeper of Sha\'tari Artifacts>[/small] and [npc=21906] [small]<Keeper of Sha\'tari Heirlooms>[/small], who exchange epic armor tokens for [url=?itemsets&filter=ta=12]Tier 4[/url] and [url=?itemsets&filter=ta=13]Tier 5[/url] gear, respectively.\n\n[i]Note: Reputation gains with Aldor correspond with a 10% greater loss of reputation with the Scryers. Most reputation gains with the Aldor will also grant 50% of the reputation gained toward your standing with the Sha\'tar.[/i]\n\n[h3]Reputation[/h3]\n[b]Until Honored[/b]\nPlayers looking to gain the higher reputation ranks (revered, exalted) may wish to save non-repeatable quests until after reaching honored.\n\nTurning in 10 [span class=q1][item=29425][/span] to [npc=18537] in Aldor Rise will grant 250 reputation with Aldor. There is also a repeatable quest for single mark turn-ins which yields 25 rep. These marks drop from low ranking Burning Legion members found in most zones in Outland, including the two camps north of Auchindoun in the Bone Wastes of [zone=3519]. Approximately 240 marks are required to go from friendly to honored. In addition these quests provide Sha\'tar reputation; 125 reputation per 10 or 12.5 reputation per single turn in.\n\nPlayers who also desire [faction=978] or [faction=941] reputation may prefer killing orcs at Kil\'Sorrow Fortress in southeastern [zone=3518], as they yield marks as well as 10 Kurenai or Mag\'har reputation per kill.[pad][b]Until Exalted[/b]\nOnce you reach level 68 you may also turn in [span class=q1][item=30809][/span] at the same rates as Marks of Kil\'jaeden. These drop from high-ranking followers of the Burning Legion. If you wish, you may turn in the higher level marks before honored reputation. In [zone=3522], grinding in Death\'s Door is the most compact group of mobs that drop marks.[pad][b]Fel Armaments[/b]\n[span class=q2][item=29740][/span] may be turned in at any time to [npc=18538]Ishanah [small]<High Priestess of Aldor>[/small] inside the Shrine of Unending Light on the Aldor Rise. This will increase your reputation with Aldor by 350 per hand-in. In addition to reputation gains, you will receive [span class=q1][item=29735][/span], which is currency for the purchase of shoulder enchants from Inscriber Saalyn in the Aldor bank.\n\n[h3]Switching to Aldor[/h3]\nTo change your faction from the Scryers to the Aldor to access their crafting recipes (and undo all reputation progress you have made), find [npc=18597], an Aldor in Lower City. She offers a repeatable quest for 8x [span class=q1][item=25802][/span]. Once you are neutral with the Aldor, you may no longer receive this quest.',NULL),(8,933,0,'Led by [npc=19674], [b]The Consortium[/b] are ethereal smugglers, traders and thieves that have come to Outland. Their main base of operations and biggest settlement is the Stormspire, but they can be found at Midrealm Post, the Aeris Landing, within the [zone=3792] of Auchindoun and various other places.\n\nUpon reaching Friendly status, players are officially considered members of the Consortium and given a salary. The salary is a bag of gems at the beginning of every month, given by [npc=18265] at Aeris Landing. Higher reputation with the Consortium yields higher qualities and quantities of jewels each month.\n\n[h3]Reputation[/h3]\n[b]Until Friendly[/b][ul][li]Run Mana-Tombs in [i]normal[/i] mode, ~1200 reputation per run.[/li][li]Turn in [item=25416] at [npc=18265].[/li][li]Turn in [item=25463] at [npc=18333].[/li][/ul][b]Friendly to Honored[/b][ul][li]Run Mana-Tombs in [i]normal[/i] mode, ~1200 reputation per run.[/li][li]Turn in [item=25433] at [npc=18265].[/li][li]Turn in [item=29209] at [npc=19880].[/li][/ul][b]Honored to Exalted[/b][ul][li]Run Mana-Tombs in [i]heroic[/i] mode, ~2400 reputation per run.[/li][li]Complete all available [url=?quests&filter=cr=1;crs=933;crv=0]quests[/url].[/li][li]Turn in [item=25433] at [npc=18265].[/li][li]Turn in [item=29209] at [npc=19880].[/li][/ul]Characters trying to simultaneously earn reputation with the [faction=941] or [faction=978] and the Consortium may want to focus on killing ogres ([url=?npcs&filter=na=boulderfist;cr=6;crs=3518;crv=0]Boulderfist[/url], [url=?npcs&filter=na=Warmaul;cr=6;crs=3518;crv=0]Warmaul[/url]) in Nagrand and saving the Obsidian Warbeads for Consortium turn-ins. The only caveat is the drop rate, which is roughly 33% for the warbeads, while it is 50% on the insignias. If you are level 70 and want a faster grind without concern for Mag\'har/Kurenai reputation, then you may want to grind insignias instead. Then again, the ogres are generally easier to grind, ranging from level 65 to 67. The choice is ultimately up to the player.',NULL),(8,934,0,'[b]The Scryers[/b] are blood elves who reside in [zone=3703] led by [npc=18530]. The group broke away from [npc=19622] and offered to assist the Naaru at Shattrath City. They are at odds with the [faction=932], and compete with them for power within Shattrath and the Naaru\'s favor.[pad]Most players will start at neutral with the Aldor. [npc=18166] in Shattrath City will give players the choice of aligning themselves with the Scryers or Aldor after completing [quest=10211]. This choice is reversible if players feel the need. Blood elf players will be friendly with the Scryers and hostile with the Aldor, whereas draenei players will be hostile to the Scryers and friendly to the Aldor.[pad]The Scryers have both a [npc=19251] trainer and a [npc=19252] trainer. Due to this, the enchanter nestled deep within [zone=1337] is rendered obsolete.[pad][npc=19331] and [npc=20808] are located in the Scryers bank on the southern edge of the Terrace of Light. The Seer\'s Library in the Scryer\'s Tier is home to [npc=20613] [small]<Keeper of Sha\'tari Artifacts>[/small] and [npc=21905] [small]<Keeper of Sha\'tari Heirlooms>[/small], who exchange epic armor tokens for [url=?itemsets&filter=ta=12]Tier 4[/url] and [url=?itemsets&filter=ta=13]Tier 5[/url] gear, respectively.[pad][i]Note: Reputation gains with Scryers correspond with a 10% greater loss of reputation with the Aldor. Most reputation gains with the Scryers will also grant 50% of the reputation gained toward your standing with the [faction=935].[/i]\n\n[h3]Lore[/h3]\nAfter enduring relentless assaults, the harried Sha\'tar and Aldor guards braced for the next wave as it marched over the horizon. This time, the attack came from the armies of [npc=22917]. A large regiment of blood elves had been sent by Illidan’s ally, Prince Kael\'thas Sunstrider, to lay waste to the city. As the regiment of blood elves crossed the bridge, the Aldor’s exarches and vindicators lined up to defend the Terrace of Light. Then the unexpected happened, the blood elves laid down their weapons in front of the city\'s defenders. Their leader, a blood elf elder known as Voren’thal, stormed into the Terrace of Light and demanded to speak to the naaru [npc=18481]. As the naaru approached him, Voren’thal knelt and uttered the following words: \"I’ve seen you in a vision, naaru. My race’s only hope for survival lies with you. My followers and I are here to serve you.\"[pad]The defection of Voren’thal and his followers was the largest loss ever incurred by Kael’thas’ forces. Many of the strongest and brightest amongst Kael’thas’ scholars and magisters had been swayed by Voren’thal\'s influence. The naaru accepted the defectors who became known as the Scryers.\n\n[h3]Reputation[/h3]\n[b]Until Honored[/b]\nPlayers looking to gain the higher reputation ranks (revered, exalted) may wish to save non-repeatable quests until after reaching honored.[pad]Turning in 10 [span class=q1][item=29426][/span] to [npc=18531] in Scryer\'s Tier will grant 250 reputation with the Scryers. These signets can also be turned in one at a time at the same exchange rate, 25 reputation per signet. These signets drop from low ranking Firewing members found in the northeast section of Terrokar Forest. This repeatable quest becomes unavailable at honored. If no other reputation quests are done, 240 signets are required to go from friendly to honored.[pad][b]Until Exalted[/b]\nOnce you reach level 68, you may also turn in [span class=q1][item=30810][/span]. These drop from high-ranking Sunfury blood elves (found in [zone=3523], [zone=3520], and the [url=?search=tempest+keep+-eye+-kael]Tempest Keep[/url] instances). If you wish, you may turn in the higher level signets before honored reputation, however it is recommended that you save them for after you hit honored. For every 10 signets, you will gain 250 reputation. Once you hit honored it will take approximately 1,320 Sunfury signets to go from honored to exalted if no other reputation is earned.[pad][b]Arcane Tomes[/b]\n[span class=q2][item=29739][/span] may be turned in at any time to Voren\'thal the Seer inside the The Seer\'s Library on the Scryer\'s Tier. This will increase your reputation with the Scryers by 350 per hand-in. If you wish, you may turn in the Arcane Tomes before honored reputation, however it is recommended that you save them for after you hit honored. Once you hit honored it will take approximately 94 Arcane Tomes to go from honored to exalted if no other reputation is earned. In addition to reputation gains, you will receive an [span class=q1][item=29736][/span], which is currency for the purchase of shoulder enchants from Inscriber Veredis, who resides in the Scryers bank.\n\n[h3]Switching to Scryers[/h3]\nTo change your faction from Aldor to Scryers to access their crafting recipes (and undo all reputation progress you have made), find [npc=18596], a Scryers in the Lower City. She offers you a repeatable quest, [quest=10024], that requires you to find eight [span class=q1][item=25744][/span]. Once you are Neutral with the Scryers, you can no longer receive this quest. The quest gives you +250 Scryers reputation and -275 Aldor reputation (in addition, the quest also gives you +125 reputation with The Sha\'tar).',NULL),(8,935,0,'[b]The Sha\'tar[/b], or \"born of light,\" are naaru that aided [faction=932], the order of draenei priests formerly led by [npc=17468], in rebuilding [zone=3703]. The city was destroyed by the Orcs during their rampage across Draenor prior to the First War. Defeat of the Burning Legion is the Sha\'tar\'s ultimate goal; the Sha\'tar are aided in this war by the Aldor and their rivals, the blood elf faction known as [faction=934]. The Aldor and the Scryers fight for the favor of the Sha\'tar so that they may be assisted in their war by the naaru\'s powers. The entity that leads the Sha\'tar is known as [npc=18481]; he can be found upon the Terrace of Light in Shattrath City.\n\nBoth Alliance and Horde players begin as Neutral toward the Sha\'tar. Players can increase their Sha\'tar reputation through various quests, by raising their reputation with the Aldor or Scryers, or by adventuring into [url=?search=Tempest+Keep#z0z]Tempest Keep[/url].\n\n[h3]Reputation[/h3]\n[b]Until Honored[/b]\nReputation can be gained from Scryer/Aldor signet/mark turn-ins. The following will only grant Sha\'tar reputation until you achieve Honored status: [item=29426], [item=30810], and [item=29739] for the Scryers; [item=29425], [item=30809], and [item=29740] for the Aldor. In addition, these will require more turn-ins to produce equable Sha\'tar reputation to the main faction. Note that this reputation gain does not show up in the combat log, but can be verified by looking at your reputation panel.\n\nReputation can also be gained by running Tempest Keep: [zone=3847], [zone=3846] and [zone=3849].\n\n[b]Through Exalted[/b]\nAfter exhausting the reputation rewards from Aldor/Scryer turn-ins and Mechanar runs, players may wish to complete the few Sha\'tar quests available. In addition to the quests, instance runs in Tempest Keep: Botanica, Arcatraz and Mechanar will continue to grant reputation. At this point, it is probably more worthwhile to run these instances in Heroic mode.',NULL),(8,941,0,'The [b]Mag\'har[/b] are a faction of brown-skinned orcs who remain on Outland and have separated themselves from the other remaining orc clans that fell prey to [npc=17257] and joined his army of fel orcs (that are now led by the powerful [npc=16808]). The Mag\'har are settled in the stronghold of Garadar in the beautiful land of [zone=3518], once home to the majority of the orcs along with [zone=3519] and the [zone=3522].[pad]The Mag\'har orcs have never been corrupted by Mannoroth or Magtheridon and thus remained untouched by the bloodlust. Unlike their former clanmates who live in the ruins of their once-mighty holds, the Mag\'har are made up of members of different orc clans who escaped corruption. The current leader of the Mag\'har, venerable [npc=18141], is an old and wise orc, yet she has recently fallen extremely ill. [npc=18063], son of the mighty Grom Hellscream, serves as the Mag\'har\'s military chief, aided by [npc=18106], son of the venerable chieftain of the Bleeding Hollow clan, Kilrogg Deadeye. In addition, there is an NPC within a Mag\'har camp to the west known as [npc=18229].[pad]It is not clear how the Mag\'har managed to retain their original brown skin. Orcish skin turns green when exposed to warlock magic, regardless of the individual\'s beliefs or practices; Garrosh and Jorin would certainly have been exposed, given the positions of their fathers. \n\nHorde players start out at unfriendly with the Mag\'har. Alliance players will always be treated as hostile. The Alliance counterpart to this faction are the [faction=978].\n\n[h3]Questing[/h3]\nQuests for the Mag\'har begin in [zone=3483] with [quest=9400] from [faction=947]. This quest will lead you to a small Mag\'har outpost north of Hellfire Citadel. Once in Nagrand, players will find the main Mag\'har city, Garadar. The city holds most of the remaining quests that will reward Mag\'har reputation.\n\nNote: You MUST have completed the quest chain of \"The Assassin\" up until the quest [quest=9410] (where you become Neutral) in order for you to talk to most people in Garadar.\n\n[h3]Reputation[/h3]\nReputation can be gained from killing [url=?npcs&filter=na=kil%27sorrow;ra=-1;rh=-1]Kil\'sorrow cult members[/url], [url=?npcs&filter=na=Murkblood;ra=-1;rh=-1;cr=6;crs=3518;crv=0]Murkblood Broken[/url], [url=?npcs&filter=na=warmaul+-marker]Warmaul[/url] and [url=?npcs&filter=na=boulderfist;minle=64;ra=-1;rh=-1]Boulderfist[/url] ogres in Nagrand. Players may also turn in 10x [item=25433], which drop from these ogres.[pad]Players seeking [faction=933] reputation may wish to save their warbeads, as Mag\'har reputation is generally easier to obtain.[pad]Players seeking [faction=932] reputation may prefer killing cult members at Kil\'Sorrow Fortress, as they drop [item=29425] for Aldor reputation turn-ins.\n\n[i]Note: These monsters and quests do not have a limit, they grant reputation all the way through exalted![/i]',NULL),(8,942,0,'Upon the reopening of the Dark Portal to Outland, the [faction=609] dispatched an exploratory force, known as the [b]Cenarion Expedition[/b], to explore the uncharted world. Much like the Circle, it is a coalition of night elf and tauren forces. Since the opening of the Dark Portal, the Cenarion Expedition has quickly gained in size and autonomy, achieving enough power to be considered its own faction. The Expedition maintains its primary base at Cenarion Refuge in [zone=3521]; it has also made its presence known on [zone=3483], in [zone=3519], and in the [zone=3522]. Cenarion Refuge is located immediately west of Thornfang Hill.\n\nThe Refuge is located in the Zangarmarsh for the primary reason of studying the rich wildlife located there. However, the Expedition has discovered troubling goings-on in the marsh. Water levels in many parts of Zangarmarsh are decreasing, and some areas such as the Dead Mire have already suffered greatly from this strange phenomenon. It has become known that this decrease in the water levels can be attributed to pumps that have been constructed in the Marsh by the naga. Their purpose is to create a new Well of Eternity for [npc=22917]. However, the Expedition cannot afford direct confrontation with the naga so numerous in the Zangarmarsh and [url=?search=coilfang#c0z]Coilfang Reservoir[/url]. It needs the aid of those willing to assist the druids in their dangerous battle against those who seek to disturb the marsh\'s natural balance. Quite naturally, those heroic enough to fight the naga at Coilfang Reservoir will be well rewarded.\n\n[h3]Reputation[/h3]\n[b]Neutral to Honored[/b]\nKill Naga, while also running [zone=3717] whenever you can; a good instance run will net reputation faster than soloing. Alternatively, the player can begin turning in [item=24401] for a chance at an [item=24407], which can be turned in for 500 reputation. It is suggested that the player save his Uncatalogued Species until after Honored status is achieved, as the quest cannot be continued past that point, while Uncatalogued Species can be used until Exalted.\n\nIf you are an herbalist, and interested in [faction=970] reputation, you may want to grind the [url=?npcs&filter=na=Bog+Lord]Bog Lords[/url] which can be found in the NE, SE, and SW corners of Zangarmarsh. Their bodies can be \"picked\" by herbalists and often yield Unidentified Plant Parts, while every kill yields 15 reputation with Sporeggar.[pad][b]Honored to Revered[/b]\nOnce the player is Honored, running Slave Pens and the [zone=3716] (with the exception of [npc=17770] and some giants), will no longer grant reputation. You should now do any Cenarion Expedition quests in Hellfire Peninsula, Zangarmarsh, Terokkar Forest and the Blade\'s Edge Mountains. It is also the time to turn in any Uncatalogued Species you have found. Doing this should get you part of the way into Revered.\n\nAlternatively, you can finish leveling to 70 and run [zone=3715]. Each run gives just over 1500 reputation if you clear all mobs. Also within the Steamvault lies a repeatable quest, [quest=9764], which begins with [item=24367]. You will then be able to turn in [item=24368], which drop in both Steamvault and Slave Pens, receiving 250 reputation for the first turn-in and 75 reputation each thereafter. This turn-in is available all the way to Exalted.\n\nOnce you are 70 and have upgraded your gear, you can opt to run Slave Pens, Underbog, and Steamvault on Heroic Mode upon purchasing the [item=30623]. While the instances are difficult, they award significant reputation: regular mobs are worth 15 reputation, 2 for non-elites, and 150/250 for bosses. This method works until Exalted.[pad][b]Revered to Exalted[/b]\nContinue with the same strategy as above: finish any remaining quests, run Steamvault, and continue with [item=24368] turn-ins.\n\nIt is also possible to run Slave Pens, Underbog, and Steamvault on Heroic Mode. The reputation gained is not much more than running Steamvault in normal mode, whilst the time investment for heroic dungeons is much higher, possibly resulting in a lower net reputation per hour, however the loot is better and you will receive [item=29434] from the bosses which can be used to purchase high quality epic gear.',NULL),(8,946,0,'A refuge of human, elven, draenei and dwarven explorers, [b]Honor Hold[/b] is the first major town Alliance explorers will encounter while traversing Outland. Vestiges of the Sons of Lothar, veterans of the Alliance that first came into Draenor, have steadfastly held on to this Hellfire outpost. They are now joined by the armies from Stormwind and Ironforge.\n\n[h3]Reputation[/h3]\nHonor Hold reputation is gained through various means in Hellfire Peninsula. Mobs in and around Hellfire Citadel reward Honor Hold reputation, as well as quests picked up in town. Due to the lack of representatives in other areas, there is a large gap between Honored and Exalted during which you may not attain any Honor Hold reputation from questing and killing mobs in Outland once you depart Hellfire Peninsula.\n\n[b]Through friendly[/b]\nMobs in [zone=3562] and [zone=3713] will award reputation through Friendly. One option is to grind reputation via Ramparts and Blood Furnace runs until honored before doing any Honor Hold quests outside the instances, as those continue to yield reputation up to Exalted. You may also want to check out the following outdoor mobs which give reputation if you are Neutral. These mobs will not give reputation once you are Friendly with Honor Hold.[ul][li][npc=19415] [/li][li][npc=16878] [/li][li][npc=16870][/li][li][npc=16867][/li][li][npc=19414] [/li][li][npc=19413] [/li][li][npc=19411] [/li][li][npc=19422][/li][/ul]To make the best use of available resources, you may want to grind reputation with Honor Hold through Hellfire Ramparts and Blood Furnace prior to completing any Honor Hold quests. \n\n[b]PvP[/b]\nPlayers that enjoy PvP can earn Honor Hold reputation through the daily quest [quest=10106]. This quest awards 70 silver and 150 Honor Hold reputation, but can only be completed once a day and counts towards your 25 daily quest limit. Completion of this quest also yields three [span class=q1][item=24579][/span], which are used as currency for various types of items and gear when turned into [npc=17657] and [npc=18266] in Honor Hold as well as the [npc=18581] in Zangarmarsh.\n\n[i]Tip: You can use these marks to purchase [span class=q1][item=24520][/span] from Warrant Officer Tracy Proudwell and increase the amount of reputation (and experience) gained while running these instances.[/i]\n\n[b]Through Exalted[/b]\nFrom here on out there are only two ways to achieve Revered and Exalted status:[ul][li][zone=3714], this instance requires level 68 and the [span class=q1][item=28395][/span] (only one party member needs the key). Mobs in Shattered Halls will yield reputation through Exalted.[/li][li]After achieving Honored status you can purchase the [span class=q1][item=30622][/span] which grants access to the heroic mode of all Hellfire Citadel instances. Mobs in all Heroic mode Hellfire Citadel instances will yield slightly more reputation than those found in non-heroic Shattered Halls, and will continue to yield reputation through Exalted.[/li][/ul]',NULL),(8,947,0,'The expedition sent through the Dark Portal by Thrall has built a stronghold in Hellfire Peninsula. [b]Thrallmar[/b] serves as a base of operations for much of the Horde\'s activities in Outland.\n\n[h3]Reputation[/h3]\nReputation for Thrallmar up to Honored is relatively easy to earn. Even the easiest quests (those that take you from one quest giver to the next up the road, for example) can yield 75 reputation points, while those that require some effort to complete typically yield 250 reputation points or more. Some group quests that involve killing an elite can yield as much as 1000 reputation points.\n\nIf you do the bulk of the Thrallmar quests instead of quickly moving on to the next zone, you might expect to reach Honored after 1 or 2 levels of play. However, once you reach Honored, you hit an earnings barrier that you can only remove when you are level 68 and can start re-earning points in the [zone=3714] dungeon.\n\n[b]Neutral through Friendly[/b]\nReputation from mobs in [zone=3562] and [zone=3713] stops at 5999/6000 friendly. One option is to grind reputation via Ramparts and Blood Furnace runs to 5999/6000 before doing any Thrallmar quests outside the instances, as those continue to yield reputation up to Exalted.\n\nAlso, the level 63 mobs outside Hellfire Citadel (on the path) give you 5 reputation each.\n\n[b]Friendly through Honored[/b]\nPlayers that enjoy PvP can earn Thrallmar reputation through the daily quest [quest=10110]. This quest awards 70 silver and 150 Thrallmar reputation, but can only be completed once a day and counts towards your 25 daily quest limit. Completion of this quest also yields three [item=24581], which are used as currency for various types of items and gear when turned into [npc=18267] and the [npc=18564] in Thrallmar and near Zabra\'jin in [zone=3521] respectively.\n\nBlood Furnace and Ramparts instance runs will be your best bet for this reputation bracket. Be aware though, that they will only take you to the end of Honored. You will need to run Shattered Halls to reach Revered status.\n\n[b]Revered to Exalted[/b]\nFrom this point on, gaining reputation through Exalted requires one of two things:[ul][li]Access to Shattered Halls, one of the wings of Hellfire Citadel, which requires level 68 and either the [span class=q1][item=28395][/span] or a rogue with 350 lockpicking skill.[/li][li]Doing Heroic versions of Hellfire Citadel dungeons, which typically require you to be well geared and level 70.[/li][/ul]Both of these give reputation until you reach Exalted status. A full clear of Shattered Halls nets you about 2000 reputation points, trash mobs generally yield 6 or 12 each, with up to 150 points from bosses. Heroic trash yields 15-25 points, with bosses worth more. \n\n[i]Tip: You can purchase [span class=q1][item=24522][/span] from Battlecryer Blackeye for use during instance runs to speed up the reputation (and experience) gaining process![/i]',NULL),(8,967,0,'[b]The Violet Eye[/b] is a secret sect founded by the Kirin Tor of Dalaran to spy on the Guardian of Tirisfal, [npc=15608], in his tower of [zone=2562]. Though Medivh is dead, the Violet Eye remains in Karazhan, defending against the evil that appears to have taken hold in the absence of its master. \n\nIt is unknown whether Medivh\'s apprentice, [npc=18166], was a member of the Violet Eye, or whether he knew of their activities at the time (though he does seem to be aware of them now).\n\n[h3]Reputation[/h3]\nViolet Eye reputation is gained by killing mobs inside Karazhan and completing Karazhan related quests. Reputation from Karazhan mobs can be gained from neutral standing all the way to exalted. Each trash mob awards around 15 reputation, with the bosses award more.\n\n[npc=18253] begins a fairly long quest chain starting with [quest=9824] and [quest=9825]. This quest line rewards players with [span class=q1][item=24490][/span] and culminates with [quest=9644]. Full completion of this quest line rewards approximately 10,270 reputation.\n\n[h3]Reputation Rewards[/h3]\n[npc=18253] will offer players rings as rewards for reputation level gains in the form of quests. The first such quest is available at neutral standing and may be completed at friendly. You will receive a new and upgraded version of the ring you chose each time you break into a new reputation tier. The rings are sorted into the following 4 categories:[ul][li][quest=10731]: [item=29280], [item=29281], [item=29282] and [item=29283][/li][li][quest=10729]: [item=29284], [item=29285], [item=29286] and [item=29287][/li][li][quest=10732]: [item=29276], [item=29277], [item=29278], and [item=29279][/li][li][quest=10730]: [item=29288], [item=29289], [item=29291] and [item=29290][/li][/ul][npc=16388], a blacksmith located inside Karazhan just after [npc=15550], offers players with high enough reputation the ability to buy epic blacksmithing plans. Players who are honored or above will also be able to repair armor and weapons at this vendor.\n\n[npc=18255], who stands just outside the main gates of Karazhan, will sell an epic jewelcrafting recipe and shoulder enchant to players who have an honored or above standing with The Violet Eye.',NULL),(8,970,0,'The sporelings are a mostly peaceful race of mushroom-men native to Outland. Their home, [b]Sporeggar[/b], is located in the western bogs of [zone=3521].\n\n[h3]Reputation[/h3]\nPlayers both Alliance and Horde start out unfriendly with Sporeggar. There are many ways to increase your reputation at the beginning:[ul][li]Bringing 10 [span class=q1][item=24290][/span] to [npc=17923] to complete [quest=9739][/li][li]Bringing 6 [span class=q1][item=24291][/span] to Fahssn to complete [quest=9743] [i](both of these quests will be available only if you are below friendly)[/i][/li][li]Killing [url=?search=bog+lord+-hungry#z0z]Bog Lords[/url] [i](lasts until the end of honored)[/i][/li][li]Killing [npc=18137] and [npc=18136] [i](lasts until the end of revered)[/i][/li][li]Bringing 10 [span class=q1][item=24245][/span] to [npc=17924] in Sporeggar [i](lasts only during neutral)[/i][/li][/ul]After you hit [b]friendly[/b], a new handful of repeatable quests opens up at the same time Fahssn\'s quests and the Glowcap turnins become unavailable, these include:[ul][li]Killing 12 each of [npc=18088] and [npc=18089] for [npc=17856] to complete [quest=9726][/li][li]Bringing 10 [span class=q1][item=24449][/span] to [npc=17925] to complete [quest=9806][/li][li]Venturing into [zone=3716] to gather 5 [span class=q1][item=24246][/span] for Gzhun\'tt to complete [quest=9715][/li][/ul]These 3 quests are repeatable and will be available to the end of exalted.\n\nPlayers who are exalted with Sporeggar should speak to [npc=17877] for one final quest.',NULL),(8,978,0,'Draenei for \"redeemed.\" These Broken have escaped the grasp of their various slavers in Outland and have made their home at Telaar in southern [zone=3518]. It is there that they seek to rediscover their destiny. They also maintain a small presence at Orebor Harborage, [zone=3521]. Their quartermaster, [npc=20240], is located outside the inn in Telaar, below the flight point.\n\nAlliance players start out at unfriendly with the Kurenai. Horde players will always be treated as hostile. The Horde counterpart to this faction are [faction=941].\n\n[i]Kurenai is Japanese for \"crimson\".[/i]\n\n[h3]Gaining Reputation[/h3]\nReputation can be gained from killing [url=?npcs&filter=na=kil%27sorrow;ra=-1;rh=-1]Kil\'sorrow cult members[/url], [url=?npcs&filter=na=Murkblood;ra=-1;rh=-1;cr=6;crs=3518;crv=0]Murkblood Broken[/url], [url=?npcs&filter=na=warmaul+-marker]Warmaul[/url] and [url=?npcs&filter=na=boulderfist;minle=64;ra=-1;rh=-1]Boulderfist[/url] ogres in Nagrand. Players may also turn in [item=25433] (10), which drop from these ogres.\n\nPlayers seeking [faction=933] reputation may wish to save their warbeads, as Kurenai reputation is generally easier to obtain.\n\nPlayers seeking [faction=932] reputation may prefer killing cult members at Kil\'Sorrow Fortress, as they drop [item=29425] for Aldor reputation turn-ins.\n\n[i]Note: These monsters and quests do not have a limit, they grant reputation all the way through exalted![/i]',NULL),(8,989,0,'The [b]Keepers of Time[/b] are bronze dragons hand-picked by Nozdormu to watch over the Caverns of Time. They are led by [npc=19932] and [npc=19933], who are also acting leaders of the Bronze Dragonflight in Nozdormu\'s absence.\n\n[h3]Reputation[/h3]\nCurrently the only way to gain the favor of the enigmatic bronze dragons is through [zone=2367] and [zone=2366] instance runs. Keepers of Time reputation rewards may be found at the Keepers\' quartermaster, [npc=21643]. The Keepers will require you to be level 66 and complete the short quest [quest=10277] before allowing passage into Old Hillsbrad Foothills to fulfill [npc=17876]\'s destiny to become the Warchief of the Horde.',NULL),(8,990,0,'The [b]Scale of the Sands[/b] is a secretive subgroup of the Bronze Dragonflight, led by [npc=19935], prime mate of [npc=15185]. It is a subgroup of the Bronze Dragonflight. Their leader, Nozdormu, sent these guardian factions to [zone=3606] where they guard the World Tree from another attack by the demons of Darkwhisper Gorge and help restore the time-stream and preserve the future of the world.\n\n[h3]Reputation[/h3]\nBoth bosses and trash monsters give reputation with each kill. [npc=17968], the final boss, awards 1500 reputation while the other four bosses give 375. General trash award 12 reputation, while [npc=17907] give 60. Yielding an average of 7800 per full clear, it would take 5-6 clears to reach exalted.\n\nCurrently some of the best [span class=q4][url=?items=4.-2&filter=na=band+of+the+eternal]rings[/url][/span] for raiding are available via this reputation. In order to recieve the rings, you must complete the previously required attunement quest, [quest=10445]. Each new reputation level awards an upgraded ring.',NULL),(8,1011,0,'The [b]Lower City[/b] of [zone=3703] is the place where the refugees gather and help out in their own ways. When someone helps any of the mixture of races who fled from war, word gets around quickly. Their quartermaster, [npc=21655], is located at the market in the Lower City. The Lower City of Shattrath also contains a very useful Mana Loom or an Alchemy Lab. Many NPCs have extensive knowledge of crafting. The Battlemasters for both sides of all four [zones=6] can also be found here, as well as the World\'s End Tavern.\n\nOther important NPCs include:[ul][li]A neutral Grand Master Leatherworker, [npc=19187].[/li][li]A neutral Grand Master Skinner, [npc=19180].[/li][li]A neutral Grand Master Alchemist, [npc=19052], with an Alchemy Lab, who also gives the quest [quest=10902] (for alchemy specialization).[/li][li]Three specialist tailors who allow you to specialize and buy new epic tailoring recipes for armor sets and special bags (including the 20-slot bag).[ul][li][npc=22212] [small]<Shadoweave Specialist>[/small] sells the patterns for the [itemset=553] set.[/li][li][npc=22213] [small]<Spellfire Specialist>[/small] sells the patterns for the [itemset=552] set.[/li][li][npc=22208] [small]<Mooncloth Specialist>[/small] sells the patterns for the [itemset=554] set.[/li][/ul][/li][/ul]\n\n[h3]Reputation[/h3]\n[b]Until Honored[/b][ul][li]Run [zone=3790] in [i]normal[/i] mode, ~750 reputation.[/li][li]Run [zone=3791] in [i]normal[/i] mode, ~1250 reputation.[/li][li]Run [zone=3789] in [i]normal[/i] mode, ~2000 reputation.[/li][li]Turn in [item=25719] at [npc=22429].[/li][/ul][i]Note: Players aiming for faction higher than Honored should wait until honored to complete the Lower City quests.[/i]\n\n[b]Honored to Revered[/b][ul][li]Run Shadow Labyrinth in [i]normal[/i] mode, ~2000 reputation.[/li][li]Complete all available [url=?quests&filter=cr=1;crs=1011;crv=0]Lower City quests[/url].[/li][/ul][b]Revered to Exalted[/b][ul][li]Run Auchenai Crypts in [i]heroic[/i] mode, ~750 reputation.[/li][li]Run Sethekk Halls in [i]heroic[/i] mode, ~1250 reputation.[/li][li]Run Shadow Labyrinth in [i]normal[/i] or [i]heroic[/i] mode, ~2000 reputation.[/li][/ul]\n\n[h3]Trivia[/h3]\n[npc=19227], a vendor in Lower City, sells amulets which are very... interesting. He is quite the salesman, with items like [item=27940], which allows you to return to life as long as you return to the place you died. [i]Buyer beware![/i]\n\nAt exalted you can purchase a [item=31778]. Strangely, none of the NPCs in Lower City can be seen wearing one. Perhaps they cannot afford one...',NULL),(8,1012,0,'The [b]Ashtongue Deathsworn[/b] are the elite of the Broken draenei tribe known as the Ashtongue. The Ashtongue tribe is led by the elder sage [npc=21700]; the Deathsworn are [i]officially[/i] aligned with [npc=22917] [small]<The Betrayer>[/small]. The Deathsworn are Akama\'s most trusted lieutenants and are privy to their leader\'s mysterious motivations.\n\nTo discover the Deathsworn as a faction, the player must begin and complete the majority of the quest line which begins with Tablets of Baa\'ri ([quest=10568] / [quest=10683]). Eventually, you will speak with Akama, whereupon you will become Neutral with the Deathsworn.',NULL),(8,1015,0,'The [b]Netherwing[/b] are a faction of dragons located in Outland. The unusual brood was spawned from the eggs of Deathwing\'s black dragonflight, and infused with raw nether-energies. Now, they seek to find their identity beyond the shadows of their father\'s destructive heritage.\n\n[h3]Reputation[/h3]\nPlayers are introduced to the Netherwing faction at 0/36000 hated reputation, and must be exalted to receive a [span class=q4][url=?items=15.-7&filter=na=Netherwing+Drake]Netherwing Drake[/url][/span]. The quest chain and reputation grind is a mostly solo endeavor involving quests that can only be completed once daily, a 5-player group quest on the way to neutral, and daily 3-player group quests after reaching revered. A flying mount is required for this reputation grind, and 300 riding skill is necessary to advance past neutral.\n\n[b]Hated to Neutral[/b]\nLevel 70 players will begin their journey to exalted reputation by picking up the quest chain offered by [npc=22113], a blood elf wandering the surface of the Netherwing Fields, in the southeast corner of [zone=3520]. The quest chain begins with the quest [quest=10804]. Completion of this quest line will provide an instant reputation boost to neutral and the choice of one of [span class=q3][url=?items&filter=qu=3;na=Netherwing+-wand]these[/url][/span] five items.\n\n[h3]Netherwing Reputation After Neutral[/h3]\nAfter completing the Kindness quest chain, Mordenai will be sure you have acquired 300 [spell=34091] skill and have you swear fealty to the Netherwing. This will grant you a Dragonmaw Fel Orc disguise when you enter Netherwing Ledge and allow you to communicate and work for the Dragonmaw stationed there. Mordenai will initially send you to [npc=23139] with a set of fake papers. Completing this quest will unlock the beginning Dragonmaw quests that you\'ll be working on to increase your Netherwing reputation. Most of these quests will have the new \"Daily\" tag added with 2.1. Daily quests differ from regular quests in that they are infinitely repeatable, but you may only complete each daily quest once per day and are restricted to ten total daily quests per day.[pad][i]Note: New quests will be unlocked with each reputation tier, and all daily quests of previous tiers will always be available, even after reaching exalted.[/i]\n\n[b][toggler id=Neutral hidden]Neutral[/toggler][/b]\n[div id=Neutral hidden]After turning in Mordenai\'s [item=32469] to Mor\'ghor to complete [quest=11013], your first group of quests will become available to start you on your way to the next tier of reputation with the Netherwing. Mor\'ghor will point you to the taskmaster to begin your grunt work, and [npc=23141] will reveal himself as a Netherwing ally in disguise and present another group of quests to you. One of which is [quest=11049]. Players will be able to turn in any [item=32506] that have a 1% chance to be found in [object=185881], [object=185877], and on almost all creatures on Netherwing Ledge. It can also be a rare find as a [object=185915] anywhere on Netherwing Ledge and in the Dragonmaw Fortress on the southeast corner of the Shadowmoon Valley mainland. This quest is not labeled as daily, and therefore can be done as many times as you can find eggs and will not hinder your daily quest limit.[pad]Other quests available from the beginning:[ul][li][i][small](Daily)[/small][/i] [quest=11018], [quest=11016], [quest=11017] - These will be available only to players who possess the respective profession to gather each item.[/li][li][i][small](Daily)[/small][/i] [quest=11015] - Simple gathering quest open to all players regardless of profession.[/li][li][i][small](Daily)[/small][/i] [quest=11020] - Yarzill will ask you to collect [item=32502] and use them to poison the peons that are working to gather resources for Dragonmaw.[/li][li][i][small](Daily)[/small][/i] [quest=11035] - You will need to fly to the northeast corner of Netherwing Ledge and position yourself on one of the floating rocks to intercept the [npc=23188] and recover 10 [item=32509].[/li][/ul][/div][pad][b][toggler id=Friendly hidden]Friendly[/toggler][/b]\n[div id=Friendly hidden]Mor\'ghor will award you with an [item=32694] to go with your new rank among the Dragonmaw.[ul][li][quest=11083] - [npc=23166] will task you with quelling the Murkblood Broken that are stationed deeper within the mines.[/li][li][quest=11081] - After finding [item=32726] in a [item=32724], you\'ll begin to reveal what\'s truly happening with the Murkblood in the mine.[/li][li][quest=11054] - [npc=23291] will have you fashion your very own [item=32680] for use in keeping the Dragonmaw peons in line and working at full efficiency.[/li][li][i][small](Daily)[/small][/i] [quest=11076] - The [npc=23149] will ask that you venture into the Netherwing mines and recover the cargo contained in mine carts randomly strewn among the interior of the mine.[/li][li][i][small](Daily)[/small][/i] [npc=23376] - One of the [npc=23376] will inform you that the creatures deeper in the mine are halting production and ask you to thin their numbers.[/li][li][i][small](Daily)[/small][/i] [quest=11055] - This humorous quest starts at Chief Overseer Mudlump after you bring him the required materials. You\'ll be able to fly around Netherwing Ledge and toss the Booterang at any [npc=23311] that can be found anywhere around the crystals of the ledge.[/li][/ul][/div][pad][b][toggler id=Honored hidden]Honored[/toggler][/b]\n[div id=Honored hidden]Mor\'ghor will award you with your new [item=32695], which is now usable anywhere as long as you\'re outside.[ul][li][quest=11063] - This six-part questline will have you in-flight following the other Dragonmaw masters of flight. They will all attempt to knock you off your mount with cleverly-placed air attacks, you must stay within vision range and on your mount until they land or you will fail and need to restart the quest. After defeating the last of the six riders, you\'ll be awarded a [item=32863], which functions exactly like a [item=25653]. The effects of the two trinkets do [b]not[/b] stack.[/li][li][quest=11089] - [npc=23427] will request a set of materials to fashion a special device to destroy his brother and hinder the Legion\'s advances from the Twilight Portal in western [zone=3518].[/li][li][i][small](Daily)[/small][/i] [quest=11086] - Mor\'ghor will send you to the Twilight Portal in Nagrand to kill 20 [url=?npcs&filter=na=deathshadow+-imp+-hound+-agent]Deathshadow Agents[/url]. Beware the overlords, they patrol most of the area and can pack quite a punch.[/li][/ul][/div][pad][b][toggler id=Revered hidden]Revered[/toggler][/b]\n[div id=Revered hidden]Mor\'ghor will award your final trinket upgrade, the [item=32864] after reaching revered.[ul][li]Kill Them All! ([quest=11094]/[quest=11099]) - Mor\'ghor will order you to begin the attack against your chosen faction\'s base of operations in Shadowmoon Valley. Obviously you\'re not going to actually allow the Dragonmaw to attack your allies, so report to the proper leader and unlock your final daily quest for Dragonmaw...[/li][li][i][small](Daily)[/small][/i] The Deadliest Trap Ever Laid ([quest=11097]/[quest=11101]) - Waves of Dragonmaw Skybreakers will attack after preparations are made. Bring allies, as this is a battle of attrition.[/li][/ul][/div][pad][b][toggler id=Exalted hidden]Exalted[/toggler][/b]\n[div id=Exalted hidden]After many days of work, finally the denouement of the Netherwing/Dragonmaw questline. Taskmaster Varkule will direct you to Mor\'ghor one last time, who will inform you that you will be promoted by [npc=22917] himself. Without spoiling the events that ensue, you will end up in Shattrath with your selection of Netherdrake epic mounts. You may choose one here for free, and if you decide on a different color later, you can speak with [npc=23489] back in the Dragonmaw Base Camp to buy another drake for 200 gold.[/div]',NULL),(8,1031,0,'The [b]Sha\'tari Skyguard[/b] are an air wing of the [faction=935] of [zone=3703], defending the capital from attackers in the hills as well as battling against the arakkoa of Terokk in the peaks of Skettis. The Skyguard has two outposts, one in the northern reaches of the Skethyl Mountains and one near [faction=1038]. Players start out at neutral standing with the Skyguard.\n\n[h3]Reputation[/h3]\n[b]Daily Quests[/b][ul][li][quest=11008] - [npc=23048] will grant you a pack of explosives to destroy the eggs that rest atop Skettis structures.[/li][li][quest=11085] - A [npc=23383] can be found atop certain structures, players will escort him out for reputation, gold, and a choice of either 2 [item=28100] or 2 [item=28101].[/li][li][quest=11065] - [npc=23335] will inform you that the Skyguard\'s bombing runs have taken a toll on their mounts and ask you to gather some more Aether Rays to supplement their scout force.[/li][li][quest=11010] - [npc=23120] asks you to destroy the ammo for the Legion\'s flak cannons so the Skyguard Scouts can continue their job.[/li][li][quest=11004] - After collecting 6 [item=32388], [npc=23042] will make a potion that will allow vision of the more powerful arakkoa, such as [npc=23066].\n[i][small]Note: World of Shadows is not a daily quest, but may be repeated as many times as necessary.[/small][/i][/li][/ul][b]Creatures[/b][ul][li][npc=21804] - 5 reputation, up to the end of Revered.[/li][li][url=?npcs&filter=na=skettis+-kaliri+-assassin;minle=70]All Skettis Arakkoa[/url] - 10 reputation, regardless of Skyguard standing.[/li][li][npc=23029] - 30 reputation, regardless of Skyguard standing.[/li][/ul]',NULL),(8,1038,0,'The [b]Ogri\'la[/b] are a faction of ogres in the [zone=3522], where their proximity to [item=32572] has allowed them to evolve past their brutish nature. They are currently fighting a war against both the Black Dragonflight and the Burning Legion, who seek the Apexis Crystals for their own purposes.\n\n[h3]Location[/h3]\nOgri\'la is situated near the western edge of Blade\'s Edge Mountains, between Forge Camp: Terror and Forge Camp: Wrath, just west of Sylvanaar. Ogri\'la is only accessible by flying mount/form. Another alternative is to have a reputation of honored or higher with [faction=1031]. But a player must have a flying mount to reach the Skyguard camp near Skettis.[pad]\n\n[h3]Reputation[/h3]\nReputation with Ogri\'la can only be gained via Quests, and there only repeatable quests are the available [url=?quests&filter=da=ja;cr=1;crs=1038;crv=0]daily quests[/url]. Thus, there is a cap on how much reputation a day a player can gain reputation with Ogri\'la, making it an \"ungrindable\" reputation.\n\n[b]Apexis Shards[/b]\n[item=32569] can be collected in a variety of ways. They can be looted from mobs, gathered from the environment, or they can be rewards from completed quests.[pad][b]Apexis Crystals[/b]\n[item=32572] are dropped from elite demons and dragons in Blade\'s Edge Mountains. In order to summon these mobs, 35 Apexis Shards are needed, and it is recommended that you have a 5 man group to defeat them.\n\n[b]Quests[/b]\nThere are a [url=?quests&filter=cr=1;crs=1038;crv=0]number of quests[/url] that a player can to do earn reputation with the Ogri\'la, as well as several [url=?quests&filter=da=ja;cr=1;crs=1038;crv=0]daily quests[/url]. Many of the daily quests will also grant reputation with the Sha\'tari Skyguard when they are first completed. \n\nIn order to access the main quests at Ogri\'la itself, a player must first complete the 5 group quests from [npc=22941].\n\n[h3]Depleted Items[/h3]\nA number of \"depleted\" items will sometimes drop from mobs. When combined with 50 Apexis Shards, the items [url=?search=Apexis+Crystal+Infusion]upgrade[/url], gaining stats and gem slots. Once the items are upgraded they become Bind on Equip, and can therefore be sold or traded to other players. One thing to note, however, is that although the depleted items may also have stats or effects, they cannot be equipped.',NULL);
/*!40000 ALTER TABLE `aowow_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_config`
--

LOCK TABLES `aowow_config` WRITE;
/*!40000 ALTER TABLE `aowow_config` DISABLE KEYS */;
INSERT INTO `aowow_config` VALUES ('sql_limit_search','500',1,129,'default: 500 - max results for search'),('sql_limit_default','300',1,129,'default: 300 - max results for listviews'),('sql_limit_quicksearch','10',1,129,'default: 10  - max results for suggestions'),('sql_limit_none','0',1,129,'default: 0 - unlimited results (i wouldn\'t change that mate)'),('ttl_rss','60',1,129,'default: 60 - time to live for RSS (in seconds)'),('name','Aowow Database Viewer (ADV)',1,136,' - website title'),('name_short','Aowow',1,136,' - feed title'),('board_url','http://www.wowhead.com/forums?board=',1,136,' - another halfbaked  javascript thing..'),('contact_email','feedback@aowow.org',1,136,' - displayed sender for auth-mails, ect'),('battlegroup','Pure Pwnage',1,136,' - pretend, we belong to a battlegroup to satisfy profiler-related Jscripts'),('debug','0',1,132,'default: 0 - disable cache, enable sql-errors, enable error_reporting'),('maintenance','1',1,132,'default: 0 - display brb gnomes and block access for non-staff'),('user_max_votes','50',1,129,'default: 50 - vote limit per day'),('force_ssl','0',1,132,'default: 0 - enforce SSL, if the server is behind a load balancer'),('locales','333',1,161,'default: 0x14D - allowed locales - 0:English, 2:French, 3:German, 6:Spanish, 8:Russian'),('screenshot_min_size','200',1,129,'default: 200 - minimum dimensions of uploaded screenshots in px (yes, it\'s square)'),('site_host','',1,136,' - points js to executable files'),('static_host','',1,136,' - points js to images & scripts'),('cache_decay','25200',2,129,'default: 60 * 60 * 7 - time to keep cache in seconds'),('cache_mode','1',2,161,'default: 1 - set cache method - 0:filecache, 1:memcached'),('cache_dir','',2,136,'default: cache/template - generated pages are saved here (requires CACHE_MODE: filecache)'),('acc_failed_auth_block','900',3,129,'default: 15 * 60 - how long an account is closed after exceeding FAILED_AUTH_COUNT (in seconds)'),('acc_failed_auth_count','5',3,129,'default: 5 - how often invalid passwords are tolerated'),('acc_allow_register','1',3,132,'default: 1 - allow/disallow account creation (requires AUTH_MODE: aowow)'),('acc_auth_mode','0',3,145,'default: 0 - source to auth against - 0:aowow, 1:TC auth-table, 2:external script'),('acc_create_save_decay','604800',3,129,'default: 604800 - time in wich an unconfirmed account cannot be overwritten by new registrations'),('acc_recovery_decay','300',3,129,'default: 300 - time to recover your account and new recovery requests are blocked'),('session_timeout_delay','3600',4,129,'default: 60 * 60 - non-permanent session times out in time() + X'),('session.gc_maxlifetime','604800',4,200,'default: 7*24*60*60 - lifetime of session data'),('session.gc_probability','1',4,200,'default: 0 - probability to remove session data on garbage collection'),('session.gc_divisor',100,4,200,'default: 100 - probability to remove session data on garbage collection'),('session_cache_dir','',4,136,'default:  - php sessions are saved here. Leave empty to use php default directory.'),('rep_req_upvote','125',5,129,'default: 125 - required reputation to upvote comments'),('rep_req_downvote','250',5,129,'default: 250 -  required reputation to downvote comments'),('rep_req_comment','75',5,129,'default: 75 - required reputation to write a comment / reply'),('rep_req_supervote','2500',5,129,'default: 2500 - required reputation for double vote effect'),('rep_req_votemore_base','2000',5,129,'default: 2000 - gains more votes past this threshold'),('rep_reward_register','100',5,129,'default: 100 - activated an account'),('rep_reward_upvoted','5',5,129,'default: 5 - comment received upvote'),('rep_reward_downvoted','0',5,129,'default: 0 - comment received downvote'),('rep_reward_good_report','10',5,129,'default: 10 - filed an accepted report'),('rep_reward_bad_report','0',5,129,'default: 0 - filed a rejected report'),('rep_reward_dailyvisit','5',5,129,'default: 5 - daily visit'),('rep_reward_user_warned','-50',5,129,'default: -50 - moderator imposed a warning'),('rep_reward_comment','1',5,129,'default: 1 - created a comment (not a reply) '),('rep_req_premium','25000',5,129,'default: 25000 - required reputation for premium status through reputation'),('rep_reward_upload','10',5,129,'default: 10 - suggested / uploaded video / screenshot was approved'),('rep_reward_article','100',5,129,'default: 100 - submitted an approved article/guide'),('rep_reward_user_suspended','-200',5,129,'default: -200 - moderator revoked rights'),('rep_req_votemore_add','250',5,129,'default: 250 - required reputation per additional vote past threshold'),('serialize_precision','5',0,65,' - some derelict code, probably unused'),('memory_limit','2048M',0,200,'default: 2048M - parsing spell.dbc is quite intense'),('default_charset','UTF-8',0,72,'default: UTF-8'),('analytics_user','',6,136,'default:  - enter your GA-user here to track site stats');
/*!40000 ALTER TABLE `aowow_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_dbversion`
--

LOCK TABLES `aowow_dbversion` WRITE;
/*!40000 ALTER TABLE `aowow_dbversion` DISABLE KEYS */;
INSERT INTO `aowow_dbversion` VALUES (1448204650,0,NULL,NULL);
/*!40000 ALTER TABLE `aowow_dbversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_home_featuredbox`
--

LOCK TABLES `aowow_home_featuredbox` WRITE;
/*!40000 ALTER TABLE `aowow_home_featuredbox` DISABLE KEYS */;
INSERT INTO `aowow_home_featuredbox` VALUES (1,NULL,0,1,0,'','[pad]Welcome to [b][span class=q5]AoWoW[/span][/b]!','[pad]Bienvenue à [b][span class=q5]AoWoW[/span][/b]!','[pad]Willkommen bei [b][span class=q5]AoWoW[/span][/b]!','','Добро[pad] пожаловать на [b][span class=q5]AoWoW[/span][/b]!'),(2,NULL,0,0,1,'STATIC_URL/images/logos/newsbox-explained.png','[ul]\n[li][i]just demoing the newsbox here..[/i][/li]\n[li][b][url=http://www.example.com]..with urls[/url][/b][/li]\n[li][b]..typeLinks [item=45533][/b][/li]\n[li][b]..also, over there to the right is an overlay-trigger =>[/b][/li]\n[/ul]\n\n[ul]\n[li][tooltip name=demotip]hey, it hints you stuff![/tooltip][b][span class=tip tooltip=demotip]..hover me[/span][/b][/li]\n[/ul]','','','','');
/*!40000 ALTER TABLE `aowow_home_featuredbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_home_featuredbox_overlay`
--

LOCK TABLES `aowow_home_featuredbox_overlay` WRITE;
/*!40000 ALTER TABLE `aowow_home_featuredbox_overlay` DISABLE KEYS */;
INSERT INTO `aowow_home_featuredbox_overlay` VALUES (2,405,100,'http://example.com','example overlay','','','','');
/*!40000 ALTER TABLE `aowow_home_featuredbox_overlay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_loot_link`
--

LOCK TABLES `aowow_loot_link` WRITE;
/*!40000 ALTER TABLE `aowow_loot_link` DISABLE KEYS */;
INSERT INTO `aowow_loot_link` VALUES (17537,185168),(18434,185169),(17536,185168),(18432,185169),(19218,184465),(21525,184849),(19710,184465),(21526,184849),(28234,190586),(-28234,193996),(27656,191349),(31561,193603),(26533,190663),(31217,193597),(16064,181366),(30603,193426),(16065,181366),(30601,193426),(30549,181366),(30600,193426),(16063,181366),(30602,193426),(28859,193905),(31734,193967),(32930,195046),(33909,195047),(32865,194313),(33147,194315),(33350,194957),(-33350,194958),(32845,194200),(32846,194201),(32906,194324),(33360,194325),(32871,194821),(33070,194822),(35119,195374),(35518,195375),(34928,195323),(35517,195324),(34705,195709),(36088,195710),(34702,195709),(36082,195710),(34701,195709),(36083,195710),(34657,195709),(36086,195710),(34703,195709),(36087,195710),(35572,195709),(36089,195710),(35569,195709),(36085,195710),(35571,195709),(36090,195710),(35570,195709),(36091,195710),(35617,195709),(36084,195710),(34441,195631),(34442,195632),(34443,195633),(-34443,195635),(34444,195631),(35740,195632),(35741,195633),(-35741,195635),(34445,195631),(35705,195632),(35706,195633),(-35706,195635),(34447,195631),(35683,195632),(35684,195633),(-35684,195635),(34448,195631),(35724,195632),(35725,195633),(-35725,195635),(34449,195631),(35689,195632),(35690,195633),(-35690,195635),(34450,195631),(35695,195632),(35696,195633),(-35696,195635),(34451,195631),(35671,195632),(35672,195633),(-35672,195635),(34453,195631),(35718,195632),(35719,195633),(-35719,195635),(34454,195631),(35711,195632),(35712,195633),(-35712,195635),(34455,195631),(35680,195632),(35681,195633),(-35681,195635),(34456,195631),(35708,195632),(35709,195633),(-35709,195635),(34458,195631),(35692,195632),(35693,195633),(-35693,195635),(34459,195631),(35686,195632),(35687,195633),(-35687,195635),(34460,195631),(35702,195632),(35703,195633),(-35703,195635),(34461,195631),(35743,195632),(35744,195633),(-35744,195635),(34463,195631),(35734,195632),(35735,195633),(-35735,195635),(34465,195631),(35746,195632),(35747,195633),(-35747,195635),(34466,195631),(35665,195632),(35666,195633),(-35666,195635),(34467,195631),(35662,195632),(35663,195633),(-35663,195635),(34468,195631),(35721,195632),(35722,195633),(-35722,195635),(34469,195631),(35714,195632),(35715,195633),(-35715,195635),(34470,195631),(35728,195632),(35729,195633),(-35729,195635),(34471,195631),(35668,195632),(35669,195633),(-35669,195635),(34472,195631),(35699,195632),(35700,195633),(-35700,195635),(34473,195631),(35674,195632),(35675,195633),(-35675,195635),(34474,195631),(35731,195632),(35732,195633),(-35732,195635),(34475,195631),(35737,195632),(35738,195633),(-35738,195635),(37226,201710),(-37226,202336),(36948,202178),(38157,202180),(38639,202177),(38640,202179),(36939,202178),(38156,202180),(38637,202177),(38638,202179);
/*!40000 ALTER TABLE `aowow_loot_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aowow_sourcestrings`
--

LOCK TABLES `aowow_sourcestrings` WRITE;
/*!40000 ALTER TABLE `aowow_sourcestrings` DISABLE KEYS */;
INSERT INTO `aowow_sourcestrings` VALUES (1,'Arena Season 1','Saison 1 des combats d\'arène','Arenasaison 1','Temporada de arena 1','Сезон арены 1'),(2,'Arena Season 2','Saison 2 des combats d\'arène','Arenasaison 2','Temporada de arena 2','Сезон арены 2'),(3,'Arena Season 3','Saison 3 des combats d\'arène','Arenasaison 3','Temporada de arena 3','Сезон арены 3'),(4,'Arena Season 4','Saison 4 des combats d\'arène','Arenasaison 4','Temporada de arena 4','Сезон арены 4'),(5,'Arena Season 5','Saison 5 des combats d\'arène','Arenasaison 5','Temporada de arena 5','Сезон арены 5'),(6,'Arena Season 6','Saison 6 des combats d\'arène','Arenasaison 6','Temporada de arena 6','Сезон арены 6'),(7,'Arena Season 7','Saison 7 des combats d\'arène','Arenasaison 7','Temporada de arena 7','Сезон арены 7'),(8,'Arena Season 8','Saison 8 des combats d\'arène','Arenasaison 8','Temporada de arena 8','Сезон арены 8'),(9,'2009 Arena Tournament','Tournoi 2009 des combats d\'arène','2009 Arena-Turnier','Torneo de arena 2009','Турнир арены 2009');
/*!40000 ALTER TABLE `aowow_sourcestrings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-10-31 13:22:30
