-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.6.14 - MySQL Community Server (GPL)
-- Server Betriebssystem:        Win32
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Datenbank Struktur für world
CREATE DATABASE IF NOT EXISTS `world` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `world`;


-- Exportiere Struktur von Tabelle world.aowow_config
CREATE TABLE IF NOT EXISTS `aowow_config` (
  `key` varchar(25) NOT NULL,
  `intValue` mediumint(9) DEFAULT NULL,
  `strValue` varchar(255) DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportiere Daten aus Tabelle world.aowow_config: 16 rows
DELETE FROM `aowow_config`;
/*!40000 ALTER TABLE `aowow_config` DISABLE KEYS */;
INSERT INTO `aowow_config` (`key`, `intValue`, `strValue`, `comment`) VALUES
	('sql_limit_search', 500, NULL, 'default: 500 - Limit of some SQL queries'),
	('sql_limit_default', 300, NULL, 'default: 300 - Limit of some SQL queries'),
	('sql_limit_quicksearch', 15, NULL, 'default: 10  - Limit of some SQL queries'),
	('sql_limit_none', 0, NULL, 'default: 0 - Limit of some SQL queries (yes, i\'m lazy)'),
	('ttl_rss', 60, NULL, 'default: 60 - time to live for RSS'),
	('cache_decay', 604800, NULL, 'default: 60 * 60 * 7 - Time to keep cache in seconds'),
	('session_timeout_delay', 3600, NULL, 'default: 60 * 60 - non-permanent session times out in time() + X'),
	('failed_auth_exclusion', 900, NULL, 'default: 15 * 60 - how long an account is closed after exceeding failed_auth_count'),
	('failed_auth_count', 5, NULL, 'default: 5 - how often invalid passwords are tolerated'),
	('name', NULL, 'Aowow Database Viewer (ADV)', 'website title'),
	('shortname', NULL, 'Aowow', 'feed title'),
	('boardurl', NULL, 'http://www.wowhead.com/forums?board=', 'a javascript thing..'),
	('contact_email', NULL, 'feedback@aowow.org', 'ah well...'),
	('battlegroup', NULL, 'Pure Pwnage', 'pretend, we belong to a battlegroup to satisfy profiler-related Jscripts; region can be determined from realmlist.timezone'),
	('allow_register', 1, NULL, 'default: 1 - Allow account creating'),
	('debug', 1, NULL, 'default: 0 - Disable cache, show smarty console panel, enable sql-errors'),
	('maintenance', 0, NULL, 'default: 0 - brb gnomes say hi');
/*!40000 ALTER TABLE `aowow_config` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
