ALTER TABLE `aowow_achievement`
    ADD COLUMN `name_loc4` VARCHAR(86) NOT NULL AFTER `name_loc3`,
    ADD COLUMN `description_loc4` TEXT NOT NULL AFTER `description_loc3`,
    ADD COLUMN `reward_loc4` VARCHAR(92) NOT NULL AFTER `reward_loc3`;

ALTER TABLE `aowow_achievementcategory`
    ADD COLUMN `name_loc4` VARCHAR(255) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_achievementcriteria`
    ADD COLUMN `name_loc4` VARCHAR(128) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_announcements`
    ADD COLUMN `text_loc4` TEXT NOT NULL AFTER `text_loc3`;

ALTER TABLE `aowow_classes`
    ADD COLUMN `name_loc4` VARCHAR(128) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_creature`
    ADD COLUMN `name_loc4` VARCHAR(100) NULL DEFAULT NULL AFTER `name_loc3`,
    ADD COLUMN `subname_loc4` VARCHAR(100) NULL DEFAULT NULL AFTER `subname_loc3`;

ALTER TABLE `aowow_currencies`
    ADD COLUMN `name_loc4` VARCHAR(64) NOT NULL AFTER `name_loc3`,
    ADD COLUMN `description_loc4` VARCHAR(256) NOT NULL AFTER `description_loc3`;

ALTER TABLE `aowow_emotes`
    ADD COLUMN `target_loc4` VARCHAR(95) NULL DEFAULT NULL AFTER `target_loc3`,
    ADD COLUMN `noTarget_loc4` VARCHAR(85) NULL DEFAULT NULL AFTER `noTarget_loc3`,
    ADD COLUMN `self_loc4` VARCHAR(85) NULL DEFAULT NULL AFTER `self_loc3`;

ALTER TABLE `aowow_factions`
    ADD COLUMN `name_loc4` VARCHAR(40) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_holidays`
    ADD COLUMN `name_loc4` VARCHAR(36) NOT NULL AFTER `name_loc3`,
    ADD COLUMN `description_loc4` TEXT NULL AFTER `description_loc3`;

ALTER TABLE `aowow_home_featuredbox`
    ADD COLUMN `text_loc4` TEXT NOT NULL AFTER `text_loc3`;

ALTER TABLE `aowow_home_featuredbox_overlay`
    ADD COLUMN `title_loc4` VARCHAR(100) NOT NULL AFTER `title_loc3`;

ALTER TABLE `aowow_home_oneliner`
    ADD COLUMN `text_loc4` VARCHAR(200) NOT NULL AFTER `text_loc3`;

ALTER TABLE `aowow_itemenchantment`
    ADD COLUMN `name_loc4` VARCHAR(100) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_itemlimitcategory`
    ADD COLUMN `name_loc4` VARCHAR(34) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_itemrandomenchant`
    ADD COLUMN `name_loc4` VARCHAR(250) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_items`
    ADD COLUMN `name_loc4` VARCHAR(127) NULL DEFAULT NULL AFTER `name_loc3`,
    ADD COLUMN `description_loc4` VARCHAR(255) NULL DEFAULT NULL AFTER `description_loc3`;

ALTER TABLE `aowow_itemset`
    ADD COLUMN `name_loc4` VARCHAR(255) NOT NULL AFTER `name_loc3`,
    ADD COLUMN `bonusText_loc4` VARCHAR(256) NOT NULL AFTER `bonusText_loc3`;

ALTER TABLE `aowow_mailtemplate`
    ADD COLUMN `subject_loc4` VARCHAR(128) NOT NULL AFTER `subject_loc3`,
    ADD COLUMN `text_loc4` TEXT NOT NULL AFTER `text_loc3`;

ALTER TABLE `aowow_objects`
    ADD COLUMN `name_loc4` VARCHAR(100) NULL DEFAULT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_pet`
    ADD COLUMN `name_loc4` VARCHAR(64) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_quests`
    ADD COLUMN `name_loc4` TEXT NULL AFTER `name_loc3`,
    ADD COLUMN `objectives_loc4` TEXT NULL AFTER `objectives_loc3`,
    ADD COLUMN `details_loc4` TEXT NULL AFTER `details_loc3`,
    ADD COLUMN `end_loc4` TEXT NULL AFTER `end_loc3`,
    ADD COLUMN `offerReward_loc4` TEXT NULL AFTER `offerReward_loc3`,
    ADD COLUMN `requestItems_loc4` TEXT NULL AFTER `requestItems_loc3`,
    ADD COLUMN `completed_loc4` TEXT NULL AFTER `completed_loc3`,
    ADD COLUMN `objectiveText1_loc4` TEXT NULL AFTER `objectiveText1_loc3`,
    ADD COLUMN `objectiveText2_loc4` TEXT NULL AFTER `objectiveText2_loc3`,
    ADD COLUMN `objectiveText3_loc4` TEXT NULL AFTER `objectiveText3_loc3`,
    ADD COLUMN `objectiveText4_loc4` TEXT NULL AFTER `objectiveText4_loc3`;

ALTER TABLE `aowow_races`
    ADD COLUMN `name_loc4` VARCHAR(64) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_skillline`
    ADD COLUMN `name_loc4` VARCHAR(64) NOT NULL AFTER `name_loc3`,
    ADD COLUMN `description_loc4` TEXT NOT NULL AFTER `description_loc3`;

ALTER TABLE `aowow_sourcestrings`
    ADD COLUMN `source_loc4` VARCHAR(128) NOT NULL AFTER `source_loc3`;

ALTER TABLE `aowow_spell`
    ADD COLUMN `name_loc4` VARCHAR(85) NOT NULL AFTER `name_loc3`,
    ADD COLUMN `rank_loc4` VARCHAR(22) NOT NULL AFTER `rank_loc3`,
    ADD COLUMN `description_loc4` TEXT NOT NULL AFTER `description_loc3`,
    ADD COLUMN `buff_loc4` TEXT NOT NULL AFTER `buff_loc3`;

ALTER TABLE `aowow_spellfocusobject`
    ADD COLUMN `name_loc4` VARCHAR(95) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_spellrange`
    ADD COLUMN `name_loc4` VARCHAR(27) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_taxinodes`
    ADD COLUMN `name_loc4` VARCHAR(55) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_titles`
    ADD COLUMN `male_loc4` VARCHAR(37) NOT NULL AFTER `male_loc3`,
    ADD COLUMN `female_loc4` VARCHAR(39) NOT NULL AFTER `female_loc3`;

ALTER TABLE `aowow_totemcategory`
    ADD COLUMN `name_loc4` VARCHAR(31) NOT NULL AFTER `name_loc3`;

ALTER TABLE `aowow_zones`
    ADD COLUMN `name_loc4` VARCHAR(120) NOT NULL AFTER `name_loc3`;

