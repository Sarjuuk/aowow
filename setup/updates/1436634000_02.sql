ALTER TABLE `aowow_items`
    ALTER `holidayId` DROP DEFAULT;
ALTER TABLE `aowow_items`
    CHANGE COLUMN `holidayId` `eventId` SMALLINT(5) UNSIGNED NOT NULL AFTER `itemLimitCategory`;

ALTER TABLE `aowow_itemset`
    ALTER `holidayId` DROP DEFAULT;
ALTER TABLE `aowow_itemset`
    CHANGE COLUMN `holidayId` `eventId` SMALLINT(5) UNSIGNED NOT NULL AFTER `contentGroup`;

ALTER TABLE `aowow_quests`
    ALTER `holidayId` DROP DEFAULT;
ALTER TABLE `aowow_quests`
    CHANGE COLUMN `holidayId` `eventId` SMALLINT(5) UNSIGNED NOT NULL AFTER `timeLimit`;

ALTER TABLE `aowow_titles`
    ALTER `holidayId` DROP DEFAULT;
ALTER TABLE `aowow_titles`
    CHANGE COLUMN `holidayId` `eventId` SMALLINT(5) UNSIGNED NOT NULL AFTER `src12Ext`;

ALTER TABLE `aowow_comments`
    ALTER `typeId` DROP DEFAULT;
ALTER TABLE `aowow_comments`
    CHANGE COLUMN `typeId` `typeId` INT(10) NOT NULL COMMENT 'ID Of Page' AFTER `type`;

-- ---------------
-- try to reconstruct CommunityContent for TYPE_WORLDEVENT (12)
-- ---------------
UPDATE `aowow_comments` c,    `aowow_events` e SET c.`typeId` = e.`id`    WHERE c.`type` = 12 AND c.`typeId` > 0 AND c.`typeId` = e.`holidayId`;
UPDATE `aowow_comments`                        SET   `typeId` = -`typeId` WHERE   `type` = 12 AND   `typeId` < 0;
UPDATE `aowow_screenshots` s, `aowow_events` e SET s.`typeId` = e.`id`    WHERE s.`type` = 12 AND s.`typeId` > 0 AND s.`typeId` = e.`holidayId`;
UPDATE `aowow_screenshots`                     SET   `typeId` = -`typeId` WHERE   `type` = 12 AND   `typeId` < 0;
UPDATE `aowow_videos` v,      `aowow_events` e SET v.`typeId` = e.`id`    WHERE v.`type` = 12 AND v.`typeId` > 0 AND v.`typeId` = e.`holidayId`;
UPDATE `aowow_videos`                          SET   `typeId` = -`typeId` WHERE   `type` = 12 AND   `typeId` < 0;

-- ---------------
-- drop not recoverable comments
-- ---------------
DELETE FROM `aowow_account_reputation` WHERE `action` IN (3, 4, 5) AND `sourceA` IN (
    SELECT x.`id` FROM (
        SELECT c2.id FROM `aowow_comments` c1 JOIN `aowow_comments` c2 ON c2.`replyTo` = c1.`id` WHERE c1.`type` = 12 AND c1.`typeId` = 0 UNION
        SELECT    id FROM `aowow_comments`                                                       WHERE    `type` = 12 AND    `typeId` = 0
    ) AS x
)

DELETE FROM `aowow_comments_rates` WHERE `commentId` IN (
    SELECT x.`id` FROM (
        SELECT c2.id FROM `aowow_comments` c1 JOIN `aowow_comments` c2 ON c2.`replyTo` = c1.`id` WHERE c1.`type` = 12 AND c1.`typeId` = 0 UNION
        SELECT    id FROM `aowow_comments`                                                       WHERE    `type` = 12 AND    `typeId` = 0
    ) AS x
)

DELETE FROM `aowow_comments` WHERE `id` IN (
    SELECT x.`id` FROM (
        SELECT c2.id FROM `aowow_comments` c1 JOIN `aowow_comments` c2 ON c2.`replyTo` = c1.`id` WHERE c1.`type` = 12 AND c1.`typeId` = 0 UNION
        SELECT    id FROM `aowow_comments`                                                       WHERE    `type` = 12 AND    `typeId` = 0
    ) AS x
)
