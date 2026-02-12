ALTER TABLE `aowow_creature` ADD FULLTEXT `idx_ft_name4` (`name_loc4`) WITH PARSER ngram;
ALTER TABLE `aowow_items` ADD FULLTEXT `idx_ft_name4` (`name_loc4`) WITH PARSER ngram;
ALTER TABLE `aowow_objects` ADD FULLTEXT `idx_ft_name4` (`name_loc4`) WITH PARSER ngram;
ALTER TABLE `aowow_quests` ADD FULLTEXT `idx_ft_name4` (`name_loc4`) WITH PARSER ngram;
ALTER TABLE `aowow_spell` ADD FULLTEXT `idx_ft_name4` (`name_loc4`) WITH PARSER ngram;
