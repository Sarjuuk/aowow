CREATE TABLE IF NOT EXISTS `aowow_contribution_budget` (
  `owner` int unsigned NOT NULL,
  `bucket` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `used` bigint unsigned NOT NULL DEFAULT 0,
  `expires` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`owner`, `bucket`),
  KEY `expires` (`expires`)
) ENGINE=InnoDB;

ALTER TABLE `aowow_errors` ADD KEY `retention_date` (`date`);
ALTER TABLE `aowow_comments` ADD KEY `comment_page` (`type`, `typeId`, `replyTo`, `date`, `id`), ADD KEY `reply_page` (`replyTo`, `type`, `typeId`, `date`, `id`);

UPDATE `aowow_dbversion` SET `build` = CONCAT_WS(' ', `build`, 'globaljs');
