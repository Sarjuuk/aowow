ALTER TABLE `aowow_quests`
	ADD COLUMN `breadcrumbForQuestId` MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER `nextQuestId`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' quests');
