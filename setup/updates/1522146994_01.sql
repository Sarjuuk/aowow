DELETE FROM `aowow_config` WHERE `key` IN ('profiler_queue', 'profiler_enable');
INSERT INTO `aowow_config` VALUES ('profiler_enable', '0', 7, 132, 'default: 0 - enable/disable profiler feature');
