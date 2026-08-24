ALTER TABLE `aowow_comments`
    MODIFY COLUMN `type` smallint unsigned NOT NULL DEFAULT 0 COMMENT 'Type of Page',
    MODIFY COLUMN `typeId` mediumint NOT NULL DEFAULT 0 COMMENT 'ID Of Page';
