REPLACE INTO `aowow_articles` (`type`, `typeId`, `locale`, `article`, `quickInfo`)
    VALUES (19, -1000, 0, 'Here you can set up a playlist of sounds and music. \n\nJust click the "Add" button near an audio control, then return to this page to listen to the list you\'ve created.', NULL);

DROP TABLE IF EXISTS `aowow_sounds`;
CREATE TABLE `aowow_sounds` (
    `id` MEDIUMINT(8) UNSIGNED NOT NULL,
    `cat` TINYINT(3) UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `cuFlags` INT(10) UNSIGNED NOT NULL,
    `soundFile1` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile2` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile3` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile4` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile5` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile6` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile7` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile8` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile9` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `soundFile10` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `flags` MEDIUMINT(8) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `cat` (`cat`),
    INDEX `name` (`name`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

DROP TABLE IF EXISTS `aowow_sounds_files`;
CREATE TABLE `aowow_sounds_files` (
    `id` MEDIUMINT(8) NOT NULL COMMENT '<0 not found in client files',
    `file` VARCHAR(50) NOT NULL,
    `path` VARCHAR(100) NOT NULL COMMENT 'in client',
    `type` TINYINT(1) UNSIGNED NOT NULL COMMENT '1: ogg; 2: mp3',
    PRIMARY KEY (`id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

ALTER TABLE `aowow_zones`
    ADD COLUMN `soundAmbiDay` MEDIUMINT(8) UNSIGNED NOT NULL AFTER `parentY`,
    ADD COLUMN `soundAmbiNight` MEDIUMINT(8) UNSIGNED NOT NULL AFTER `soundAmbiDay`,
    ADD COLUMN `soundMusicDay` MEDIUMINT(8) UNSIGNED NOT NULL AFTER `soundAmbiNight`,
    ADD COLUMN `soundMusicNight` MEDIUMINT(8) UNSIGNED NOT NULL AFTER `soundMusicDay`,
    ADD COLUMN `soundIntro` MEDIUMINT(8) UNSIGNED NOT NULL AFTER `soundMusicNight`;

DROP TABLE IF EXISTS `aowow_races_sounds`;
CREATE TABLE `aowow_races_sounds` (
    `race` TINYINT UNSIGNED NOT NULL,
    `soundId` SMALLINT UNSIGNED NOT NULL,
    `gender` TINYINT(1) UNSIGNED NOT NULL,
    INDEX `race` (`race`),
    INDEX `soundId` (`soundId`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;


DROP TABLE IF EXISTS `aowow_emotes_sounds`;
CREATE TABLE `aowow_emotes_sounds` (
    `emoteId` SMALLINT(5) UNSIGNED NOT NULL,
    `raceId` TINYINT(3) UNSIGNED NOT NULL,
    `gender` TINYINT(1) UNSIGNED NOT NULL,
    `soundId` MEDIUMINT(8) UNSIGNED NOT NULL,
    UNIQUE INDEX `emoteId_raceId_gender_soundId` (`emoteId`, `raceId`, `gender`, `soundId`),
    INDEX `emoteId` (`emoteId`),
    INDEX `raceId` (`raceId`),
    INDEX `soundId` (`soundId`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;


DROP TABLE IF EXISTS `aowow_creature_sounds`;
CREATE TABLE `aowow_creature_sounds` (
    `id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'CreatureDisplayInfo.dbc/id',
    `greeting` MEDIUMINT(8) UNSIGNED NOT NULL,
    `farewell` MEDIUMINT(8) UNSIGNED NOT NULL,
    `angry` MEDIUMINT(8) UNSIGNED NOT NULL,
    `exertion` MEDIUMINT(8) UNSIGNED NOT NULL,
    `exertioncritical` MEDIUMINT(8) UNSIGNED NOT NULL,
    `injury` MEDIUMINT(8) UNSIGNED NOT NULL,
    `injurycritical` MEDIUMINT(8) UNSIGNED NOT NULL,
    `death` MEDIUMINT(8) UNSIGNED NOT NULL,
    `stun` MEDIUMINT(8) UNSIGNED NOT NULL,
    `stand` MEDIUMINT(8) UNSIGNED NOT NULL,
    `footstep` MEDIUMINT(8) UNSIGNED NOT NULL,
    `aggro` MEDIUMINT(8) UNSIGNED NOT NULL,
    `wingflap` MEDIUMINT(8) UNSIGNED NOT NULL,
    `wingglide` MEDIUMINT(8) UNSIGNED NOT NULL,
    `alert` MEDIUMINT(8) UNSIGNED NOT NULL,
    `fidget` MEDIUMINT(8) UNSIGNED NOT NULL,
    `customattack` MEDIUMINT(8) UNSIGNED NOT NULL,
    `loop` MEDIUMINT(8) UNSIGNED NOT NULL,
    `jumpstart` MEDIUMINT(8) UNSIGNED NOT NULL,
    `jumpend` MEDIUMINT(8) UNSIGNED NOT NULL,
    `petattack` MEDIUMINT(8) UNSIGNED NOT NULL,
    `petorder` MEDIUMINT(8) UNSIGNED NOT NULL,
    `petdismiss` MEDIUMINT(8) UNSIGNED NOT NULL,
    `birth` MEDIUMINT(8) UNSIGNED NOT NULL,
    `spellcast` MEDIUMINT(8) UNSIGNED NOT NULL,
    `submerge` MEDIUMINT(8) UNSIGNED NOT NULL,
    `submerged` MEDIUMINT(8) UNSIGNED NOT NULL,
    `transform` MEDIUMINT(8) UNSIGNED NOT NULL,
    `transformanimated` MEDIUMINT(8) UNSIGNED NOT NULL
) COMMENT='!ATTENTION!\r\nthe primary key of this table is NOT a creatureId, but displayId\r\n\r\ncolumn names from LANG.sound_activities' COLLATE='utf8_general_ci' ENGINE=InnoDB;
