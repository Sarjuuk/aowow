ALTER TABLE `aowow_profiler_profiles`
    ADD INDEX idx_race (`race`),
    ADD INDEX idx_class (`class`),
    ADD INDEX idx_level (`level`),
    ADD INDEX idx_guildrank (`guildrank`),
    ADD INDEX idx_gearscore (`gearscore`),
    ADD INDEX idx_achievementpoints (`achievementpoints`),
    ADD INDEX idx_talenttree1 (`talenttree1`),
    ADD INDEX idx_talenttree2 (`talenttree2`),
    ADD INDEX idx_talenttree3 (`talenttree3`)
;

ALTER TABLE aowow_profiler_completion_skills
    ADD INDEX idx_value (`value`)
;

ALTER TABLE aowow_profiler_arena_team
    ADD INDEX idx_type (`type`),
    ADD INDEX idx_rating (`rating`)
;
