ALTER TABLE `aowow_announcements` ADD COLUMN `status`  tinyint(4) NOT NULL COMMENT '0:disabled; 1:enabled; 2:deleted' AFTER `status`;
ALTER TABLE `aowow_announcements` ADD COLUMN `mode`    tinyint(4) NOT NULL COMMENT '0:pageTop; 1:contentTop'          AFTER `status`;
UPDATE `aowow_announcements` SET
    `status` = ((`flags` & 0xF0) >> 4),
    `mode` = (`flags` & 0xF)
WHERE 1;
UPDATE `aowow_announcements` SET
     `text_loc0` = IF(`text_loc0` <> '', CONCAT('$', `text_loc0`), ''),
     `text_loc2` = IF(`text_loc2` <> '', CONCAT('$', `text_loc2`), ''),
     `text_loc3` = IF(`text_loc3` <> '', CONCAT('$', `text_loc3`), ''),
     `text_loc6` = IF(`text_loc6` <> '', CONCAT('$', `text_loc6`), ''),
     `text_loc8` = IF(`text_loc8` <> '', CONCAT('$', `text_loc8`), '')
WHERE `flags` & 0xF00;
ALTER TABLE `aowow_announcements` DROP COLUMN `flags`;