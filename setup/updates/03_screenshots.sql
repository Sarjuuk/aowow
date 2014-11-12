DROP TABLE IF EXISTS `aowow_screenshots`;
CREATE TABLE IF NOT EXISTS `aowow_screenshots` (
    `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
    `type` tinyint(4) unsigned NOT NULL,
    `typeId` mediumint(9) NOT NULL,
    `uploader` int(16) unsigned NOT NULL,
    `date` int(32) unsigned NOT NULL,
    `width` smallint(5) unsigned NOT NULL,
    `height` smallint(5) unsigned NOT NULL,
    `caption` varchar(250) DEFAULT NULL,
    `status` tinyint(3) unsigned NOT NULL COMMENT 'see defines.php - CC_FLAG_*',
    `approvedBy` int(16) unsigned DEFAULT NULL,
    `deletedBy` int(16) unsigned DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `type` (`type`,`typeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
