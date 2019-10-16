-- alterations

ALTER TABLE `aowow_spell`
    ADD COLUMN `spellVisualId` smallint(5) unsigned NOT NULL AFTER `rankNo`;

ALTER TABLE `aowow_items`
    ADD COLUMN `spellVisualId` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `displayId`,
    ADD COLUMN `material` tinyint(3) NOT NULL DEFAULT '0' AFTER `lockId`,
    ADD COLUMN `soundOverrideSubclass` tinyint(3) NOT NULL AFTER `subClassBak`,
    ADD COLUMN `pickUpSoundId` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `maxMoneyLoot`,
    ADD COLUMN `dropDownSoundId` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `pickUpSoundId`,
    ADD COLUMN `sheatheSoundId` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `dropDownSoundId`,
    ADD COLUMN `unsheatheSoundId` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `sheatheSoundId`;


-- additions

REPLACE INTO `aowow_articles` (`type`, `typeId`, `locale`, `article`, `quickInfo`)
    VALUES (19, -1000, 0, 'Here you can set up a playlist of sounds and music. \n\nJust click the "Add" button near an audio control, then return to this page to listen to the list you\'ve created.', NULL);


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `aowow_items_sounds`;
CREATE TABLE `aowow_items_sounds` (
    `soundId` smallint(5) unsigned NOT NULL,
    `subClassMask` mediumint(8) unsigned NOT NULL,
    PRIMARY KEY (`soundId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='actually .. its only weapon related sounds in here';

DROP TABLE IF EXISTS `aowow_zones_sounds`;
CREATE TABLE `aowow_zones_sounds` (
    `id` smallint(5) unsigned NOT NULL,
    `ambienceDay` smallint(5) unsigned NOT NULL,
    `ambienceNight` smallint(5) unsigned NOT NULL,
    `musicDay` smallint(5) unsigned NOT NULL,
    `musicNight` smallint(5) unsigned NOT NULL,
    `intro` smallint(5) unsigned NOT NULL,
    `worldStateId` smallint(5) unsigned NOT NULL,
    `worldStateValue` smallint(6) NOT NULL,
    INDEX `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `aowow_creature_sounds`;
CREATE TABLE IF NOT EXISTS `aowow_creature_sounds` (
    `id` smallint(5) unsigned NOT NULL COMMENT 'CreatureDisplayInfo.dbc/id',
    `greeting` smallint(5) unsigned NOT NULL,
    `farewell` smallint(5) unsigned NOT NULL,
    `angry` smallint(5) unsigned NOT NULL,
    `exertion` smallint(5) unsigned NOT NULL,
    `exertioncritical` smallint(5) unsigned NOT NULL,
    `injury` smallint(5) unsigned NOT NULL,
    `injurycritical` smallint(5) unsigned NOT NULL,
    `death` smallint(5) unsigned NOT NULL,
    `stun` smallint(5) unsigned NOT NULL,
    `stand` smallint(5) unsigned NOT NULL,
    `footstep` smallint(5) unsigned NOT NULL,
    `aggro` smallint(5) unsigned NOT NULL,
    `wingflap` smallint(5) unsigned NOT NULL,
    `wingglide` smallint(5) unsigned NOT NULL,
    `alert` smallint(5) unsigned NOT NULL,
    `fidget` smallint(5) unsigned NOT NULL,
    `customattack` smallint(5) unsigned NOT NULL,
    `loop` smallint(5) unsigned NOT NULL,
    `jumpstart` smallint(5) unsigned NOT NULL,
    `jumpend` smallint(5) unsigned NOT NULL,
    `petattack` smallint(5) unsigned NOT NULL,
    `petorder` smallint(5) unsigned NOT NULL,
    `petdismiss` smallint(5) unsigned NOT NULL,
    `birth` smallint(5) unsigned NOT NULL,
    `spellcast` smallint(5) unsigned NOT NULL,
    `submerge` smallint(5) unsigned NOT NULL,
    `submerged` smallint(5) unsigned NOT NULL,
    `transform` smallint(5) unsigned NOT NULL,
    `transformanimated` smallint(5) unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='!ATTENTION!\r\nthe primary key of this table is NOT a creatureId, but displayId\r\n\r\ncolumn names from LANG.sound_activities';

DROP TABLE IF EXISTS `aowow_emotes_sounds`;
CREATE TABLE IF NOT EXISTS `aowow_emotes_sounds` (
    `emoteId` smallint(5) unsigned NOT NULL,
    `raceId` tinyint(3) unsigned NOT NULL,
    `gender` tinyint(1) unsigned NOT NULL,
    `soundId` smallint(5) unsigned NOT NULL,
    UNIQUE KEY `emoteId_raceId_gender_soundId` (`emoteId`,`raceId`,`gender`,`soundId`),
    KEY `emoteId` (`emoteId`),
    KEY `raceId` (`raceId`),
    KEY `soundId` (`soundId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `aowow_races_sounds`;
CREATE TABLE IF NOT EXISTS `aowow_races_sounds` (
    `raceId` tinyint(3) unsigned NOT NULL,
    `soundId` smallint(5) unsigned NOT NULL,
    `gender` tinyint(1) unsigned NOT NULL,
    UNIQUE KEY `race_soundId_gender` (`raceId`,`soundId`,`gender`),
    KEY `race` (`raceId`),
    KEY `soundId` (`soundId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `aowow_sounds`;
CREATE TABLE IF NOT EXISTS `aowow_sounds` (
    `id` smallint(5) unsigned NOT NULL,
    `cat` tinyint(3) unsigned NOT NULL,
    `name` varchar(100) NOT NULL,
    `cuFlags` int(10) unsigned NOT NULL,
    `soundFile1` smallint(5) unsigned DEFAULT NULL,
    `soundFile2` smallint(5) unsigned DEFAULT NULL,
    `soundFile3` smallint(5) unsigned DEFAULT NULL,
    `soundFile4` smallint(5) unsigned DEFAULT NULL,
    `soundFile5` smallint(5) unsigned DEFAULT NULL,
    `soundFile6` smallint(5) unsigned DEFAULT NULL,
    `soundFile7` smallint(5) unsigned DEFAULT NULL,
    `soundFile8` smallint(5) unsigned DEFAULT NULL,
    `soundFile9` smallint(5) unsigned DEFAULT NULL,
    `soundFile10` smallint(5) unsigned DEFAULT NULL,
    `flags` mediumint(8) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `cat` (`cat`),
    KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `aowow_sounds_files`;
CREATE TABLE IF NOT EXISTS `aowow_sounds_files` (
    `id` smallint(6) NOT NULL COMMENT '<0 not found in client files',
    `file` varchar(75) NOT NULL,
    `path` varchar(75) NOT NULL COMMENT 'in client',
    `type` tinyint(1) unsigned NOT NULL COMMENT '1: ogg; 2: mp3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `aowow_spell_sounds`;
CREATE TABLE IF NOT EXISTS `aowow_spell_sounds` (
    `id` smallint(5) unsigned NOT NULL COMMENT 'SpellVisual.dbc/id',
    `animation` smallint(5) unsigned NOT NULL,
    `ready` smallint(5) unsigned NOT NULL,
    `precast` smallint(5) unsigned NOT NULL,
    `cast` smallint(5) unsigned NOT NULL,
    `impact` smallint(5) unsigned NOT NULL,
    `state` smallint(5) unsigned NOT NULL,
    `statedone` smallint(5) unsigned NOT NULL,
    `channel` smallint(5) unsigned NOT NULL,
    `casterimpact` smallint(5) unsigned NOT NULL,
    `targetimpact` smallint(5) unsigned NOT NULL,
    `castertargeting` smallint(5) unsigned NOT NULL,
    `missiletargeting` smallint(5) unsigned NOT NULL,
    `instantarea` smallint(5) unsigned NOT NULL,
    `persistentarea` smallint(5) unsigned NOT NULL,
    `casterstate` smallint(5) unsigned NOT NULL,
    `targetstate` smallint(5) unsigned NOT NULL,
    `missile` smallint(5) unsigned NOT NULL COMMENT 'not predicted by js',
    `impactarea` smallint(5) unsigned NOT NULL COMMENT 'not predicted by js',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='!ATTENTION!\r\nthe primary key of this table is NOT a spellId, but spellVisualId\r\n\r\ncolumn names from LANG.sound_activities';

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

UPDATE aowow_dbversion SET  `build` = CONCAT(IFNULL(`build`, ''), ' soundfiles'), `sql` = CONCAT(IFNULL(`sql`, ''), ' spell creature sounds spawns');
