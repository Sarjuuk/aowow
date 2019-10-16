ALTER TABLE `aowow_home_featuredbox`
    CHANGE COLUMN `active` `startDate` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `editDate`,
    ADD COLUMN `endDate` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `startDate`;

ALTER TABLE `aowow_home_featuredbox`
    CHANGE COLUMN `bgImgUrl` `boxBG` VARCHAR(150) NULL DEFAULT NULL AFTER `extraWide`,
    ADD COLUMN `altHomeLogo` VARCHAR(150) NULL DEFAULT NULL AFTER `boxBG`,
    ADD COLUMN `altHeaderLogo` VARCHAR(150) NULL DEFAULT NULL AFTER `altHomeLogo`;
