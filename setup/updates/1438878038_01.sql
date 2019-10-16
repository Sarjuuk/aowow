SET FOREIGN_KEY_CHECKS=0;

RENAME TABLE `aowow_news` TO `aowow_home_featuredbox`;
ALTER TABLE `aowow_home_featuredbox`
  ALTER `id` DROP DEFAULT,
  ALTER `active` DROP DEFAULT;
ALTER TABLE `aowow_home_featuredbox`
  ENGINE=InnoDB,
  CHANGE COLUMN `id` `id` smallint(5) unsigned NOT NULL FIRST,
  ADD COLUMN `editorId` int(10) unsigned NULL AFTER `id`,
  ADD COLUMN `editDate` int(10) unsigned NOT NULL AFTER `editorId`,
  CHANGE COLUMN `active` `active` tinyint(1) unsigned NOT NULL AFTER `editDate`,
  ADD CONSTRAINT `FK_acc_hFBox` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

RENAME TABLE `aowow_news_overlay` TO `aowow_home_featuredbox_overlay`;
ALTER TABLE `aowow_home_featuredbox_overlay`
  ALTER `newsId` DROP DEFAULT;
ALTER TABLE `aowow_home_featuredbox_overlay`
  ENGINE=InnoDB,
  CHANGE COLUMN `newsId` `featureId` smallint(5) unsigned NOT NULL FIRST,
  ADD CONSTRAINT `FK_home_featurebox` FOREIGN KEY (`featureId`) REFERENCES `aowow_home_featuredbox` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE `aowow_home_titles` (
  `id` smallint(5) unsigned NOT NULL,
  `editorId` int(10) unsigned NULL,
  `editDate` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `title_loc0` varchar(100) NOT NULL,
  `title_loc2` varchar(100) NOT NULL,
  `title_loc3` varchar(100) NOT NULL,
  `title_loc6` varchar(100) NOT NULL,
  `title_loc8` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `FK_acc_hTitles` (`editorId`),
  CONSTRAINT `FK_acc_hTitles` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `aowow_home_oneliner` (
  `id` smallint(5) unsigned NOT NULL,
  `editorId` int(10) unsigned NULL,
  `editDate` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `text_loc0` varchar(200) NOT NULL,
  `text_loc2` varchar(200) NOT NULL,
  `text_loc3` varchar(200) NOT NULL,
  `text_loc6` varchar(200) NOT NULL,
  `text_loc8` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `FK_acc_hOneliner` (`editorId`),
  CONSTRAINT `FK_acc_hOneliner` FOREIGN KEY (`editorId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
