ALTER TABLE `aowow_creature`
    ADD INDEX `difficultyEntry1` (`difficultyEntry1`),
    ADD INDEX `difficultyEntry2` (`difficultyEntry2`),
    ADD INDEX `difficultyEntry3` (`difficultyEntry3`);

UPDATE aowow_items i, aowow_spell s SET i.class = 0, i.subClass = 6 WHERE s.Id = i.spellId1 AND s.effect1Id = 53 AND i.classBak = 12;
UPDATE aowow_items SET class    = 12 WHERE classBak = 15 AND startQuest <> 0 AND name_loc0 NOT LIKE "sayge\'s fortune%";
UPDATE aowow_items SET subClass =  3 WHERE classBak = 15 AND subClassBak = 0 AND holidayId <> 0;
UPDATE aowow_items SET subClass = 11 WHERE classBak =  9 AND subClassBak = 0 AND requiredSkill = 773;
UPDATE aowow_items SET subClass =  9 WHERE classBak =  9 AND subClassBak = 0 AND requiredSkill = 356;
UPDATE aowow_items SET subClass = 12 WHERE classBak =  9 AND subClassBak = 0 AND requiredSkill = 186;
UPDATE aowow_items SET subClass =  5 WHERE classBak =  9 AND subClassBak = 0 AND requiredSkill = 185;
UPDATE aowow_items SET subClass =  6 WHERE classBak =  9 AND subClassBak = 0 AND requiredSkill = 171;
