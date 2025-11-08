ALTER TABLE aowow_profiler_profiles
    ADD COLUMN `custom` tinyint(1) DEFAULT 0 COMMENT 'custom profile' AFTER `cuFlags`,
    ADD COLUMN `stub` tinyint(1) DEFAULT 0 COMMENT 'character stub needs resync' AFTER `custom`,
    ADD COLUMN `deleted` tinyint(1) DEFAULT 0 COMMENT 'only on custom profiles' AFTER `stub`,
    ADD KEY `idx_custom` (`custom`),
    ADD KEY `idx_stub` (`stub`),
    ADD KEY `idx_deleted` (`deleted`)
;

ALTER TABLE aowow_profiler_arena_team
    ADD COLUMN `stub` tinyint(1) DEFAULT 0 COMMENT 'arena team stub needs resync' AFTER `cuFlags`,
    ADD KEY `idx_stub` (`stub`)
;

ALTER TABLE aowow_profiler_guild
    ADD COLUMN `stub` tinyint(1) DEFAULT 0 COMMENT 'guild stub needs resync' AFTER `cuFlags`,
    ADD KEY `idx_stub` (`stub`)
;
