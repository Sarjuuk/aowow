DROP TABLE IF EXISTS `aowow_talents`;
CREATE TABLE `aowow_talents` (
  `id` smallint(5) unsigned NOT NULL,
  `class` tinyint(3) unsigned NOT NULL,
  `tab` tinyint(3) unsigned NOT NULL,
  `row` tinyint(3) unsigned NOT NULL,
  `col` tinyint(3) unsigned NOT NULL,
  `spell` mediumint(8) unsigned NOT NULL,
  `rank` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`, `rank`),
  INDEX `spell` (`spell`),
  INDEX `class` (`class`)
) ENGINE=MyISAM;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(`sql`, ' talents');
