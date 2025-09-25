-- update video storage
ALTER TABLE `aowow_videos`
    ADD COLUMN `pos` tinyint unsigned NOT NULL AFTER `videoId`,
    ADD COLUMN `url` varchar(64) NOT NULL COMMENT 'preview thumb' AFTER `pos`,
    ADD COLUMN `width` smallint unsigned NOT NULL AFTER `url`,
    ADD COLUMN `height` smallint unsigned NOT NULL AFTER `width`,
    ADD COLUMN `name` varchar(64) DEFAULT NULL AFTER `height`,
    MODIFY COLUMN `caption` varchar(200) DEFAULT NULL;
