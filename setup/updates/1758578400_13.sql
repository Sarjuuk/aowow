ALTER TABLE `aowow_account`
    ADD COLUMN `renameCooldown` int unsigned NOT NULL DEFAULT 0 COMMENT 'timestamp when rename is available again' AFTER `updateValue`;
