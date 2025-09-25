DROP TABLE IF EXISTS `aowow_achievementcategory`;
CREATE TABLE `aowow_achievementcategory` (
  `id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `parentCat` smallint(5) unsigned NOT NULL DEFAULT '0',
  `parentCat2` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE aowow_dbversion SET `sql` = CONCAT(IFNULL(`sql`, ''), ' achievements');
