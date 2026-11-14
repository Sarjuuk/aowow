ALTER TABLE `aowow_quests`
    ADD COLUMN `rewardXPId` tinyint(3) unsigned NOT NULL DEFAULT 0 AFTER `sourceSpellId`;

UPDATE `aowow_dbversion` SET
    `sql`   = CONCAT(IFNULL(`sql`, ''),   ' quests'),
    `build` = CONCAT(IFNULL(`build`, ''), ' globaljs');
