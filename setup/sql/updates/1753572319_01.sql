ALTER TABLE `aowow_account`
    DROP INDEX `user`,
    CHANGE COLUMN `user` `login` varchar(64) NOT NULL DEFAULT '' COMMENT 'only used for login',
    CHANGE COLUMN `displayName` `username` varchar(64) NOT NULL COMMENT 'unique; used for for links and display',
    MODIFY COLUMN `email` varchar(64) DEFAULT NULL COMMENT 'unique; can be used for login if AUTH_SELF and can be NULL if not',
    MODIFY COLUMN `token` varchar(40) DEFAULT NULL COMMENT 'identification key for changes to account',
    ADD COLUMN `updateValue` varchar(128) DEFAULT NULL COMMENT 'temp store for new passHash / email' AFTER `token`,
    ADD CONSTRAINT `username` UNIQUE (`username`);

UPDATE `aowow_account`
    SET `email` = NULL WHERE `email` = '';

ALTER TABLE `aowow_account`
    ADD CONSTRAINT `email` UNIQUE (`email`);
