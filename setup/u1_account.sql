-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.6.16 - MySQL Community Server (GPL)
-- Server Betriebssystem:        Win32
-- HeidiSQL Version:             8.3.0.4792
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Struktur von Tabelle world.aowow_account
DROP TABLE IF EXISTS `aowow_account`;
CREATE TABLE IF NOT EXISTS `aowow_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `extId` int(10) unsigned NOT NULL COMMENT 'external user id',
  `user` varchar(64) NOT NULL COMMENT 'login',
  `passHash` varchar(128) NOT NULL,
  `displayName` varchar(64) NOT NULL COMMENT 'nickname',
  `email` varchar(64) NOT NULL,
  `joinDate` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `allowExpire` tinyint(1) unsigned NOT NULL,
  `curIP` varchar(15) NOT NULL,
  `prevIP` varchar(15) NOT NULL,
  `curLogin` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `prevLogin` int(10) unsigned NOT NULL,
  `locale` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0,2,3,6,8',
  `userGroups` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'bitmask',
  `avatar` varchar(16) NOT NULL COMMENT 'icon-string for internal or id for upload',
  `description` text NOT NULL COMMENT 'markdown formated',
  `userPerms` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'bool isAdmin',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'flag, see defines',
  `statusTimer` int(10) unsigned NOT NULL DEFAULT '0',
  `token` varchar(40) NOT NULL COMMENT 'creation & recovery',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Daten Export vom Benutzer nicht ausgewählt


-- Exportiere Struktur von Tabelle world.aowow_account_banned
DROP TABLE IF EXISTS `aowow_account_banned`;
CREATE TABLE IF NOT EXISTS `aowow_account_banned` (
  `id` int(16) unsigned NOT NULL,
  `userId` int(11) unsigned NOT NULL COMMENT 'affected accountId',
  `staffId` int(11) unsigned NOT NULL COMMENT 'executive accountId',
  `typeMask` tinyint(4) unsigned NOT NULL COMMENT 'ACC_BAN_*',
  `start` int(10) unsigned NOT NULL COMMENT 'unixtime',
  `end` int(10) unsigned NOT NULL COMMENT 'automatic unban @ unixtime',
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Daten aus Tabelle world.aowow_config: 18 rows
DELETE FROM `aowow_config`;
/*!40000 ALTER TABLE `aowow_config` DISABLE KEYS */;
INSERT INTO `aowow_config` (`key`, `intValue`, `strValue`, `comment`) VALUES
	('sql_limit_search', 500, NULL, 'default: 500 - Limit of some SQL queries'),
	('sql_limit_default', 300, NULL, 'default: 300 - Limit of some SQL queries'),
	('sql_limit_quicksearch', 10, NULL, 'default: 10  - Limit of some SQL queries'),
	('sql_limit_none', 0, NULL, 'default: 0 - Limit of some SQL queries (yes, i\'m lazy)'),
	('ttl_rss', 60, NULL, 'default: 60 - time to live for RSS'),
	('cache_decay', 604800, NULL, 'default: 60 * 60 * 7 - Time to keep cache in seconds'),
	('session_timeout_delay', 3600, NULL, 'default: 60 * 60 - non-permanent session times out in time() + X'),
	('failed_auth_exclusion', 900, NULL, 'default: 15 * 60 - how long an account is closed after exceeding failed_auth_count'),
	('failed_auth_count', 5, NULL, 'default: 5 - how often invalid passwords are tolerated'),
	('name', NULL, 'Aowow Database Viewer (ADV)', 'website title'),
	('name_short', NULL, 'Aowow', 'feed title'),
	('board_url', NULL, 'http://www.wowhead.com/forums?board=', 'a javascript thing..'),
	('contact_email', NULL, 'feedback@aowow.org', 'ah well...'),
	('battlegroup', NULL, 'Pure Pwnage', 'pretend, we belong to a battlegroup to satisfy profiler-related Jscripts; region can be determined from realmlist.timezone'),
	('allow_register', 1, NULL, 'default: 1 - Allow/disallow account creation (requires auth_mode 0)'),
	('debug', 0, NULL, 'default: 0 - Disable cache, enable sql-errors, enable error_reporting'),
	('maintenance', 0, NULL, 'default: 0 - brb gnomes'),
	('auth_mode', 0, NULL, 'default: 0 - 0:aowow, 1:wow-auth, 2:external');

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
