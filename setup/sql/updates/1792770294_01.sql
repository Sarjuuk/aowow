UPDATE `aowow_articles` a JOIN `aowow_events` e ON e.`holidayId` = 327 SET a.`article` = REPLACE(a.`article`, 'event=327]', CONCAT('event=', e.`id`, ']')) WHERE a.`type` = 300 AND a.`url` = 'lunar-festival';
UPDATE `aowow_articles` a JOIN `aowow_events` e ON e.`holidayId` = 181 SET a.`article` = REPLACE(a.`article`, 'event=181]', CONCAT('event=', e.`id`, ']')) WHERE a.`type` = 300 AND a.`url` = 'noblegarden';
UPDATE `aowow_articles` a JOIN `aowow_events` e ON e.`holidayId` = 423 SET a.`article` = REPLACE(a.`article`, 'event=335]', CONCAT('event=', e.`id`, ']')) WHERE a.`type` = 300 AND a.`url` = 'love-is-in-the-air';
UPDATE `aowow_articles` a JOIN `aowow_events` e ON e.`holidayId` = 372 SET a.`article` = REPLACE(a.`article`, 'event=372]', CONCAT('event=', e.`id`, ']')) WHERE a.`type` = 300 AND a.`url` = 'love-is-in-the-air';

UPDATE `aowow_articles` a JOIN `aowow_events` e ON e.`holidayId` = 341 SET a.`article` = REPLACE(a.`article`, 'event=341]', CONCAT('event=', e.`id`, ']')) WHERE a.`type` = 300 AND a.`url` = 'midsummer-fire-festival';
UPDATE `aowow_articles` a                                              SET a.`article` = REPLACE(a.`article`, 'search&q=', 'search=')                      WHERE a.`type` = 300 AND a.`url` = 'midsummer-fire-festival';

UPDATE `aowow_articles` a JOIN `aowow_events` e ON e.`holidayId` = 141 SET a.`article` = REPLACE(a.`article`, 'event=141]', CONCAT('event=', e.`id`, ']')) WHERE a.`type` = 300 AND a.`url` = 'winter-veil';

-- 'midsummer-fire-festival' and 'childrens-week' have switched ids
DELETE FROM `aowow_articles` WHERE `type` = 300 AND `typeId` = 0;
UPDATE `aowow_articles` SET `typeId` = 0 WHERE `type` = 300 AND `typeId` = 4 AND `url` = 'midsummer-fire-festival';
UPDATE `aowow_articles` SET `typeId` = 4 WHERE `type` = 300 AND `typeId` = 5 AND `url` = 'childrens-week';
UPDATE `aowow_articles` SET `typeId` = 5 WHERE `type` = 300 AND `typeId` = 0 AND `url` = 'midsummer-fire-festival';
