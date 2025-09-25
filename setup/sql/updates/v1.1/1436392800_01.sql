ALTER TABLE `aowow_config`
    ADD COLUMN `cat` TINYINT(3) UNSIGNED NOT NULL DEFAULT '5' AFTER `value`;

INSERT IGNORE INTO `aowow_config` (`key`, `value`, `cat`, `flags`, `comment`) VALUES
    ('cache_dir', '', 1, 136, 'default: cache/template - generated pages are saved here (requires CACHE_MODE: filecache)'),
    ('session.gc_maxlifetime', '604800', 3, 200, 'default: 7*24*60*60 - lifetime of session data'),
    ('session.gc_probability', '0', 3, 200, 'default: 0 - probability to remove session data on garbage collection'),
    ('session_cache_dir', '', 3, 136, 'default:  - php sessions are saved here. Leave empty to use php default directory.');

UPDATE `aowow_config` SET `key` = 'acc_failed_auth_block' WHERE `key` = 'failed_auth_exclusion';
UPDATE `aowow_config` SET `key` = 'acc_failed_auth_count' WHERE `key` = 'failed_auth_count';
UPDATE `aowow_config` SET `key` = 'acc_allow_register'    WHERE `key` = 'allow_register';
UPDATE `aowow_config` SET `key` = 'acc_auth_mode'         WHERE `key` = 'auth_mode';
UPDATE `aowow_config` SET `key` = 'acc_create_save_decay' WHERE `key` = 'account_create_save_decay';
UPDATE `aowow_config` SET `key` = 'acc_recovery_decay'    WHERE `key` = 'account_recovery_decay';
