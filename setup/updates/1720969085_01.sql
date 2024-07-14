ALTER TABLE `aowow_errors`
    DROP COLUMN IF EXISTS `post`,
    ADD COLUMN `post` text NOT NULL AFTER `query`;
