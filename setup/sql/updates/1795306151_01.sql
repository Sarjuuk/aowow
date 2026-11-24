--  creature spawns, vehicle accessories, summon groups, SAI summons
DROP TABLE IF EXISTS `aowow_creature_spawns`;
CREATE TABLE `aowow_creature_spawns` (
  `guid` int NOT NULL COMMENT '< 0: virtual (not spawned by default)',
  `typeId` int unsigned NOT NULL,
  `flags` int unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint unsigned NOT NULL,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  `respawn` mediumint NOT NULL DEFAULT 0 COMMENT 'in seconds',
  `spawnMask` tinyint unsigned NOT NULL DEFAULT 0,
  `phaseMask` smallint unsigned NOT NULL DEFAULT 0,
  `pathId` int unsigned NOT NULL DEFAULT 0,
  `ScriptName` varchar(64) DEFAULT NULL,
  `StringId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`guid`),
  KEY `idx_type` (`typeId`),
  KEY `idx_area` (`areaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- gameobject spawns, SAI summons
DROP TABLE IF EXISTS `aowow_objects_spawns`;
CREATE TABLE `aowow_objects_spawns` (
  `guid` int NOT NULL COMMENT '< 0: virtual (not spawned by default)',
  `typeId` int unsigned NOT NULL,
  `flags` int unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint unsigned NOT NULL,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  `respawn` mediumint NOT NULL DEFAULT 0 COMMENT 'in seconds',
  `spawnMask` tinyint unsigned NOT NULL DEFAULT 0,
  `phaseMask` smallint unsigned NOT NULL DEFAULT 0,
  `pathId` int unsigned NOT NULL DEFAULT 0,
  `ScriptName` varchar(64) DEFAULT NULL,
  `StringId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`guid`),
  KEY `idx_type` (`typeId`),
  KEY `idx_area` (`areaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- areatrigger spawns
DROP TABLE IF EXISTS `aowow_areatrigger_spawns`;
CREATE TABLE `aowow_areatrigger_spawns` (
  `guid` int NOT NULL COMMENT '< 0: teleporter endpoint',
  `typeId` int unsigned NOT NULL,
  `flags` int unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint unsigned NOT NULL,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  PRIMARY KEY (`guid`),
  KEY `idx_type` (`typeId`),
  KEY `idx_area` (`areaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- soundemitter spawns
DROP TABLE IF EXISTS `aowow_sounds_spawns`;
CREATE TABLE `aowow_sounds_spawns` (
  `guid` int unsigned NOT NULL COMMENT 'SoundEmitter.dbc/id',
  `typeId` int unsigned NOT NULL COMMENT 'SoundEmitter.dbc/soundId',
  `flags` int unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint unsigned NOT NULL,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  PRIMARY KEY (`guid`),
  KEY `idx_type` (`typeId`),
  KEY `idx_area` (`areaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- instance entrances
DROP TABLE IF EXISTS `aowow_zones_spawns`;
CREATE TABLE `aowow_zones_spawns` (
  `guid` int unsigned NOT NULL,
  `typeId` int GENERATED ALWAYS AS (`guid`) VIRTUAL,
  `flags` int unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint unsigned NOT NULL,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  PRIMARY KEY (`guid`),
  KEY `idx_type` (`typeId`),
  KEY `idx_area` (`areaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- creature waypoint path nodes
DROP TABLE IF EXISTS `aowow_creature_waypoints`;
CREATE TABLE `aowow_creature_waypoints` (
  `pathId` int unsigned NOT NULL,
  `point` smallint unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint NOT NULL DEFAULT -1,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  `wait` int unsigned NOT NULL DEFAULT 0,
  `action` int unsigned NOT NULL,
  `actionChance` smallint unsigned NOT NULL,
  PRIMARY KEY (`pathId`,`point`),
  KEY `idx_map` (`mapId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- mo transport path nodes
DROP TABLE IF EXISTS `aowow_objects_waypoints`;
CREATE TABLE `aowow_objects_waypoints` (
  `pathId` int unsigned NOT NULL,
  `point` smallint unsigned NOT NULL,
  `areaId` smallint unsigned NOT NULL,
  `areaX` float unsigned NOT NULL,
  `areaY` float unsigned NOT NULL,
  `floor` tinyint NOT NULL DEFAULT -1,
  `mapId` smallint unsigned NOT NULL,
  `mapX` float signed NOT NULL,
  `mapY` float signed NOT NULL,
  `wait` int unsigned NOT NULL DEFAULT 0,
  `arrivalEventId` smallint unsigned NOT NULL,
  `departureEventId` smallint unsigned NOT NULL,
  PRIMARY KEY (`pathId`,`point`),
  KEY `idx_map` (`mapId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
