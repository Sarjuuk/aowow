-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.6.16 - MySQL Community Server (GPL)
-- Server Betriebssystem:        Win32
-- HeidiSQL Version:             8.3.0.4834
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Struktur von Tabelle world.aowow_config
DROP TABLE IF EXISTS `aowow_config`;
CREATE TABLE IF NOT EXISTS `aowow_config` (
  `key` varchar(25) NOT NULL,
  `value` varchar(255) NOT NULL,
  `flags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportiere Daten aus Tabelle world.aowow_config: 44 rows
/*!40000 ALTER TABLE `aowow_config` DISABLE KEYS */;
INSERT INTO `aowow_config` (`key`, `value`, `flags`, `comment`) VALUES
	('sql_limit_search', '500', 129, 'default: 500 - max results for search'),
	('sql_limit_default', '300', 129, 'default: 300 - max results for listviews'),
	('sql_limit_quicksearch', '10', 129, 'default: 10  - max results for suggestions'),
	('sql_limit_none', '0', 129, 'default: 0 - unlimited results (i wouldn\'t change that mate)'),
	('ttl_rss', '60', 129, 'default: 60 - time to live for RSS (in seconds)'),
	('cache_decay', '25200', 129, 'default: 60 * 60 * 7 - time to keep cache in seconds'),
	('session_timeout_delay', '3600', 129, 'default: 60 * 60 - non-permanent session times out in time() + X'),
	('failed_auth_exclusion', '900', 129, 'default: 15 * 60 - how long an account is closed after exceeding failed_auth_count (in seconds)'),
	('failed_auth_count', '5', 129, 'default: 5 - how often invalid passwords are tolerated'),
	('name', 'Aowow Database Viewer (ADV)', 136, ' - website title'),
	('name_short', 'Aowow', 136, ' - feed title'),
	('board_url', 'http://www.wowhead.com/forums?board=', 136, ' - another halfbaked  javascript thing..'),
	('contact_email', 'feedback@aowow.org', 136, ' - displayed sender for auth-mails, ect'),
	('battlegroup', 'Pure Pwnage', 136, ' - pretend, we belong to a battlegroup to satisfy profiler-related Jscripts'),
	('allow_register', '0', 132, 'default: 1 - allow/disallow account creation (requires auth_mode 0)'),
	('debug', '1', 132, 'default: 0 - disable cache, enable sql-errors, enable error_reporting'),
	('maintenance', '0', 132, 'default: 0 - display brb gnomes and block access for non-staff'),
	('auth_mode', '0', 145, 'default: 0 - source to auth against - 0:aowow, 1:TC auth-table, 2:external script'),
	('rep_req_upvote', '125', 129, 'default: 125 - required reputation to upvote comments'),
	('rep_req_downvote', '250', 129, 'default: 250 -  required reputation to downvote comments'),
	('rep_req_comment', '75', 129, 'default: 75 - required reputation to write a comment / reply'),
	('rep_req_supervote', '2500', 129, 'default: 2500 - required reputation for double vote effect'),
	('rep_req_votemore_base', '2000', 129, 'default: 2000 - gains more votes past this threshold'),
	('rep_reward_register', '100', 129, 'default: 100 - activated an account'),
	('rep_reward_upvoted', '5', 129, 'default: 5 - comment received upvote'),
	('rep_reward_downvoted', '0', 129, 'default: 0 - comment received downvote'),
	('rep_reward_good_report', '10', 129, 'default: 10 - filed an accepted report'),
	('rep_reward_bad_report', '0', 129, 'default: 0 - filed a rejected report'),
	('rep_reward_dailyvisit', '5', 129, 'default: 5 - daily visit'),
	('rep_reward_user_warned', '-50', 129, 'default: -50 - moderator imposed a warning'),
	('rep_reward_comment', '1', 129, 'default: 1 - created a comment (not a reply) '),
	('rep_req_premium', '25000', 129, 'default: 25000 - required reputation for premium status through reputation'),
	('rep_reward_upload', '10', 129, 'default: 10 - suggested / uploaded video / screenshot was approved'),
	('rep_reward_article', '100', 129, 'default: 100 - submitted an approved article/guide'),
	('rep_reward_user_suspended', '-200', 129, 'default: -200 - moderator revoked rights'),
	('user_max_votes', '50', 129, 'default: 50 - vote limit per day'),
	('rep_req_votemore_add', '250', 129, 'default: 250 - required reputation per additional vote past threshold'),
	('force_ssl', '0', 148, 'default: 0 - enforce SSL, if the server is behind a load balancer'),
	('cache_mode', '1', 161, 'default: 1 - set cache method - 0:filecache, 1:memcached'),
	('locales', '333', 161, 'default: 0x14D - allowed locales - 0:English, 2:French, 3:German, 6:Spanish, 8:Russian'),
	('account_create_save_decay', '604800', 129, 'default: 604800 - time in wich an unconfirmed account cannot be overwritten by new registrations'),
	('account_recovery_decay', '300', 129, 'default: 300 - time to recover your account and new recovery requests are blocked'),
	('serialize_precision', '4', 65, ' - some derelict code, probably unused'),
	('screenshot_min_size', '200', 129, 'default: 200 - minimum dimensions of uploaded screenshots in px (yes, it\'s square)');
/*!40000 ALTER TABLE `aowow_config` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
