-- ***************************
-- * change engine to InnoDB *
-- * unify userId-fields     *
-- ***************************

ALTER TABLE `aowow_account`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT;

ALTER TABLE `aowow_account_banned`
    ALTER `userId` DROP DEFAULT,
    ALTER `staffId` DROP DEFAULT;
ALTER TABLE `aowow_account_banned`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT,
    CHANGE COLUMN `userId` `userId` INT(10) UNSIGNED NOT NULL COMMENT 'affected accountId' AFTER `id`,
    CHANGE COLUMN `staffId` `staffId` INT(10) UNSIGNED NOT NULL COMMENT 'executive accountId' AFTER `userId`;

ALTER TABLE `aowow_account_cookies`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT;

ALTER TABLE `aowow_account_reputation`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT;

ALTER TABLE `aowow_account_weightscales`
    ALTER `account` DROP DEFAULT;
ALTER TABLE `aowow_account_weightscales`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT,
    CHANGE COLUMN `account` `userId` INT(10) UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `aowow_screenshots`
    ALTER `uploader` DROP DEFAULT;
ALTER TABLE `aowow_screenshots`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT,
    CHANGE COLUMN `uploader` `userIdOwner` INT(10) UNSIGNED NULL AFTER `typeId`,
    CHANGE COLUMN `approvedBy` `userIdApprove` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `status`,
    CHANGE COLUMN `deletedBy` `userIdDelete` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `userIdApprove`;

ALTER TABLE `aowow_videos`
    ALTER `uploader` DROP DEFAULT;
ALTER TABLE `aowow_videos`
    ENGINE=InnoDB,
    ROW_FORMAT=COMPACT,
    CHANGE COLUMN `uploader` `userIdOwner` INT(10) UNSIGNED NULL AFTER `typeId`,
    CHANGE COLUMN `approvedBy` `userIdApprove` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `status`,
    ADD COLUMN `userIdeDelete` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `userIdApprove`;

-- **********************
-- * apply foreign keys *
-- **********************

ALTER TABLE aowow_account_cookies ADD CONSTRAINT FK_acc_cookies FOREIGN KEY (userId) REFERENCES aowow_account(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
;
ALTER TABLE aowow_account_banned ADD CONSTRAINT FK_acc_banned FOREIGN KEY (userId) REFERENCES aowow_account(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
;
ALTER TABLE aowow_account_reputation ADD CONSTRAINT FK_acc_rep FOREIGN KEY (userId) REFERENCES aowow_account(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
;
ALTER TABLE aowow_account_weightscales ADD CONSTRAINT FK_acc_weights FOREIGN KEY (userId) REFERENCES aowow_account(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
;
ALTER TABLE aowow_screenshots ADD CONSTRAINT FK_acc_ss FOREIGN KEY (userIdOwner) REFERENCES aowow_account(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
;
ALTER TABLE aowow_videos ADD CONSTRAINT FK_acc_vi FOREIGN KEY (userIdOwner) REFERENCES aowow_account(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
;
