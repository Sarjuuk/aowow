DROP TABLE IF EXISTS `aowow_areatrigger`;
CREATE TABLE `aowow_areatrigger` (
  `id` int unsigned NOT NULL,
  `cuFlags` int unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
  `type` smallint unsigned NOT NULL,
  `mapId` smallint unsigned NOT NULL COMMENT 'world pos. from dbc',
  `posX` float NOT NULL COMMENT 'world pos. from dbc',
  `posY` float NOT NULL COMMENT 'world pos. from dbc',
  `orientation` float NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quest` mediumint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quest` (`quest`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `aowow_zones`
    CHANGE COLUMN `parentAreaId` `parentMapId` smallint unsigned NOT NULL;

DELETE FROM aowow_setup_custom_data WHERE
    `command` = 'zones' AND
    `entry` IN (3456, 3845, 3847, 3848, 3849) AND
    `field` IN ('parentAreaId', 'parentX', 'parentY');

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' areatrigger zones spawns');
