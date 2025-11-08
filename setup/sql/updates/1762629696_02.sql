UPDATE aowow_profiler_profiles SET `deleted` = 1 WHERE `cuFlags` & 4;
UPDATE aowow_profiler_profiles SET `custom`  = 1 WHERE `cuFlags` & 8;
UPDATE aowow_profiler_profiles SET `stub`    = 1 WHERE `cuFlags` & 16;
UPDATE aowow_profiler_profiles SET `cuFlags` = `cuFlags` & ~(4 | 8 | 16);

UPDATE aowow_profiler_arena_team SET `stub`    = 1 WHERE `cuFlags` & 16;
UPDATE aowow_profiler_arena_team SET `cuFlags` = `cuFlags` & ~16;

UPDATE aowow_profiler_guild SET `stub`    = 1 WHERE `cuFlags` & 16;
UPDATE aowow_profiler_guild SET `cuFlags` = `cuFlags` & ~16;
