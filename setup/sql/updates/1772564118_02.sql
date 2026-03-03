ALTER TABLE `aowow_creature`
    DROP INDEX `idx_ft_name0`,
    DROP INDEX `idx_ft_name2`,
    DROP INDEX `idx_ft_name3`,
    DROP INDEX `idx_ft_name6`,
    DROP INDEX `idx_ft_name8`;

ALTER TABLE `aowow_objects`
    DROP INDEX `idx_ft_name0`,
    DROP INDEX `idx_ft_name2`,
    DROP INDEX `idx_ft_name3`,
    DROP INDEX `idx_ft_name6`,
    DROP INDEX `idx_ft_name8`;

ALTER TABLE `aowow_quests`
    DROP INDEX `idx_ft_name0`,
    DROP INDEX `idx_ft_name2`,
    DROP INDEX `idx_ft_name3`,
    DROP INDEX `idx_ft_name6`,
    DROP INDEX `idx_ft_name8`;

ALTER TABLE `aowow_spell`
    DROP INDEX `idx_ft_name0`,
    DROP INDEX `idx_ft_name2`,
    DROP INDEX `idx_ft_name3`,
    DROP INDEX `idx_ft_name6`,
    DROP INDEX `idx_ft_name8`;

ALTER TABLE `aowow_items`
    DROP COLUMN `effects_loc0`,
    DROP COLUMN `effects_loc2`,
    DROP COLUMN `effects_loc3`,
    DROP COLUMN `effects_loc4`,
    DROP COLUMN `effects_loc6`,
    DROP COLUMN `effects_loc8`,
    DROP INDEX `idx_ft_name0`,
    DROP INDEX `idx_ft_name2`,
    DROP INDEX `idx_ft_name3`,
    DROP INDEX `idx_ft_name6`,
    DROP INDEX `idx_ft_name8`;
