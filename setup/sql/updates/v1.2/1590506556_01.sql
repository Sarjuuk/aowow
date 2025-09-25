DROP TABLE IF EXISTS `aowow_spawns_override`;
CREATE TABLE `aowow_spawns_override` (
  `type` smallint(5) unsigned NOT NULL,
  `typeGuid` mediumint(9) NOT NULL,
  `areaId` mediumint(8) unsigned NOT NULL,
  `floor` mediumint(8) unsigned NOT NULL,
  `revision` tinyint(3) unsigned NOT NULL COMMENT 'Aowow revision, when this override was applied',
  PRIMARY KEY (`type`, `typeGuid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE aowow_dbversion SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spawns');
