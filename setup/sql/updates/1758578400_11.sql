ALTER TABLE `aowow_account`
    ADD COLUMN `debug` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'show ids in lists user option' AFTER `userGroups`,
    MODIFY COLUMN `description` text NOT NULL DEFAULT '';
