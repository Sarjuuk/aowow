ALTER TABLE aowow_creature
    ADD KEY `idx_loot` (`lootId`),
    ADD KEY `idx_pickpocketloot` (`pickpocketLootId`),
    ADD KEY `idx_skinloot` (`skinLootId`);
