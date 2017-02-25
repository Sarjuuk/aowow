REPLACE INTO `aowow_articles` (`type`, `typeId`, `locale`, `article`, `quickInfo`)
    VALUES (19, -1000, 0, 'Here you can set up a playlist of sounds and music. \n\nJust click the "Add" button near an audio control, then return to this page to listen to the list you\'ve created.', NULL);


CREATE TABLE `aowow_sounds` (
    `id` MEDIUMINT(8) UNSIGNED NOT NULL,
    `cat` TINYINT(3) UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
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

CREATE TABLE `aowow_races_sounds` (
    `race` TINYINT UNSIGNED NOT NULL,
    `soundId` SMALLINT UNSIGNED NOT NULL,
    `gender` TINYINT(1) UNSIGNED NOT NULL,
    INDEX `race` (`race`),
    INDEX `soundId` (`soundId`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;


CREATE TABLE `aowow_emotes_sounds` (
    `emoteId` SMALLINT(5) UNSIGNED NOT NULL,
    `raceId` TINYINT(3) UNSIGNED NOT NULL,
    `gender` TINYINT(1) UNSIGNED NOT NULL,
    `soundId` MEDIUMINT(8) UNSIGNED NOT NULL,
    UNIQUE INDEX `emoteId_raceId_gender_soundId` (`emoteId`, `raceId`, `gender`, `soundId`),
    INDEX `emoteId` (`emoteId`),
    INDEX `raceId` (`raceId`),
    INDEX `soundId` (`soundId`)
)
COLLATE='utf8_general_ci' ENGINE=InnoDB;


UPDATE aowow_races SET factionId = 69 WHERE id = 4; -- was 96 *sigh*
