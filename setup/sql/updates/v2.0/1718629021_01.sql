DROP TABLE IF EXISTS aowow_areatrigger;
CREATE TABLE aowow_areatrigger (
    `id` int unsigned NOT NULL,
    `cuFlags` int unsigned NOT NULL DEFAULT 0 COMMENT 'see defines.php for flags',
    `type` smallint unsigned NOT NULL,
    `mapId` smallint unsigned NOT NULL COMMENT 'world pos. from dbc',
    `posX` float NOT NULL COMMENT 'world pos. from dbc',
    `posY` float NOT NULL COMMENT 'world pos. from dbc',
    `orientation` float NOT NULL,
    `name` varchar(100) NULL DEFAULT NULL,
    `quest` mediumint unsigned NULL DEFAULT NULL,
    `teleportA` smallint unsigned NULL DEFAULT NULL,
    `teleportX` float NULL DEFAULT NULL,
    `teleportY` float NULL DEFAULT NULL,
    `teleportO` float NULL DEFAULT NULL,
    `teleportF` tinyint unsigned NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `quest` (`quest`),
    INDEX `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE='utf8mb4_general_ci' ;
