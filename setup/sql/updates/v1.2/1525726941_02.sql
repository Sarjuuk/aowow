-- drop deprecated dbc data
DROP TABLE IF EXISTS `dbc_achievement_category`;
DROP TABLE IF EXISTS `dbc_achievement_criteria`;
DROP TABLE IF EXISTS `dbc_achievement`;
DROP TABLE IF EXISTS `dbc_areatable`;
DROP TABLE IF EXISTS `dbc_chartitles`;
DROP TABLE IF EXISTS `dbc_chrclasses`;
DROP TABLE IF EXISTS `dbc_creaturefamily`;
DROP TABLE IF EXISTS `dbc_emotestexxtdata`;
DROP TABLE IF EXISTS `dbc_faction`;
DROP TABLE IF EXISTS `dbc_holidaydescriptions`;
DROP TABLE IF EXISTS `dbc_holidaynames`;
DROP TABLE IF EXISTS `dbc_itemlimitcategory`;
DROP TABLE IF EXISTS `dbc_itemrandomproperties`;
DROP TABLE IF EXISTS `dbc_itemrandomsuffix`;
DROP TABLE IF EXISTS `dbc_itemset`;
DROP TABLE IF EXISTS `dbc_lfgdungeons`;
DROP TABLE IF EXISTS `dbc_mailtemplate`;
DROP TABLE IF EXISTS `dbc_map`;
DROP TABLE IF EXISTS `dbc_skillline`;
DROP TABLE IF EXISTS `dbc_spell`;
DROP TABLE IF EXISTS `dbc_spellfocusobject`;
DROP TABLE IF EXISTS `dbc_spellitemenchantment`;
DROP TABLE IF EXISTS `dbc_spellrange`;
DROP TABLE IF EXISTS `dbc_spellshapeshiftform`;
DROP TABLE IF EXISTS `dbc_talenttab`;
DROP TABLE IF EXISTS `dbc_taxinodes`;
DROP TABLE IF EXISTS `dbc_totemcategory`;

-- update config
UPDATE `aowow_config` SET `comment` = 'default: 0x15D - allowed locales - 0:English, 2:French, 3:German, 4:Chinese, 6:Spanish, 8:Russian' WHERE `key` = 'locales';

-- rebuild affected files
UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' achievementcategory achievementcriteria itemenchantment itemlimitcategory mailtemplate spellfocusobject spellrange totemcategory classes factions holidays itemrandomenchant races shapeshiftforms skillline emotes achievement creature currencies objects pet quests spell taxi titles items zones itemset'), `build` = CONCAT(IFNULL(`build`, ''), ' complexImg locales statistics talentCalc pets glyphs itemsets enchants gems profiler');
