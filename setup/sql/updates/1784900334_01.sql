ALTER TABLE aowow_items
    DROP KEY `items_index`,
    DROP KEY `iconId`,
    ADD KEY `idx_class` (`class`),
    ADD KEY `idx_subclass` (`subClass`),
    ADD KEY `idx_icon` (`iconId`)
;

ALTER TABLE aowow_quests
    ADD KEY `idx_reqitem1` (`reqItemId1`),
    ADD KEY `idx_reqitem2` (`reqItemId2`),
    ADD KEY `idx_reqitem3` (`reqItemId3`),
    ADD KEY `idx_reqitem4` (`reqItemId4`),
    ADD KEY `idx_reqitem5` (`reqItemId5`),
    ADD KEY `idx_reqitem6` (`reqItemId6`),
    ADD KEY `idx_sourceitem` (`sourceItemId`),
    ADD KEY `idx_reqsourceitem1` (`reqSourceItemId1`),
    ADD KEY `idx_reqsourceitem2` (`reqSourceItemId2`),
    ADD KEY `idx_reqsourceitem3` (`reqSourceItemId3`),
    ADD KEY `idx_reqsourceitem4` (`reqSourceItemId4`)
;

ALTER TABLE aowow_spell
    DROP KEY `category`,
    ADD KEY `idx_typecat` (`typeCat`),
    ADD KEY `idx_category` (`category`),
    ADD KEY `idx_toolcategory1` (`toolCategory1`),
    ADD KEY `idx_toolcategory2` (`toolCategory2`)
;
