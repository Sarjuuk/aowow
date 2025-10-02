ALTER TABLE aowow_profiler_completion_quests
    ADD KEY `typeId` (`questId`);

ALTER TABLE aowow_profiler_completion_reputation
    ADD KEY `typeId` (`factionId`);

ALTER TABLE aowow_profiler_completion_spells
    ADD KEY `typeId` (`spellId`);

ALTER TABLE aowow_profiler_completion_titles
    ADD KEY `typeId` (`titleId`);
