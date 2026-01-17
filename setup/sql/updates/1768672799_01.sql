ALTER TABLE `aowow_creature` DROP INDEX `idx_name4`;
ALTER TABLE `aowow_items` DROP INDEX `idx_name4`;
ALTER TABLE `aowow_objects` DROP INDEX `idx_name4`;
ALTER TABLE `aowow_quests` DROP INDEX `idx_name4`;
ALTER TABLE `aowow_spell` DROP INDEX `idx_name4`;

SET SESSION innodb_ft_enable_stopword = OFF;

OPTIMIZE TABLE `aowow_spell`;
OPTIMIZE TABLE `aowow_quests`;
OPTIMIZE TABLE `aowow_creature`;
OPTIMIZE TABLE `aowow_items`;
OPTIMIZE TABLE `aowow_objects`;

REPLACE INTO `aowow_config` VALUES
    ('logographic_ft_search', '0', '0', 1, 0x484, 'enables fulltext search for logographic languages (CN, KR, TW). The database MUST support this (i.e. MySQL implements ngram)');
