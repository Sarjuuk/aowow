ALTER TABLE aowow_profiler_completion_reputation
    ADD COLUMN `exalted` tinyint(1) GENERATED ALWAYS AS (`standing` >= 42000) STORED AFTER `standing`,
    ADD KEY idx_exalted (`exalted`)
;
