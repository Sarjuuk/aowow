-- set on_set_fn check
UPDATE `aowow_config` SET `flags` = `flags` | 1024 WHERE `key` = 'cache_mode';
