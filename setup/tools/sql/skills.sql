/*
 * Skills
 */

CREATE TABLE world.aowow_skillLine LIKE dbc.skillLine;
INSERT world.aowow_skillLine SELECT * FROM dbc.skillLine;
ALTER TABLE `aowow_skillline`
    ADD COLUMN `typeCat`  bigint(20) NOT NULL AFTER `Id`,
    CHANGE COLUMN `nameEN` `name_loc0`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `skillCostId`,
    CHANGE COLUMN `nameFR` `name_loc2`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name_loc0`,
    CHANGE COLUMN `nameDE` `name_loc3`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name_loc2`,
    CHANGE COLUMN `nameES` `name_loc6`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name_loc3`,
    CHANGE COLUMN `nameRU` `name_loc8`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name_loc6`,
    CHANGE COLUMN `descriptionEN` `description_loc0`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name_loc8`,
    CHANGE COLUMN `descriptionFR` `description_loc2`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description_loc0`,
    CHANGE COLUMN `descriptionDE` `description_loc3`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description_loc2`,
    CHANGE COLUMN `descriptionES` `description_loc6`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description_loc3`,
    CHANGE COLUMN `descriptionRU` `description_loc8`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description_loc6`,
    ADD COLUMN `iconString`  varchar(40) NOT NULL AFTER `spellIconId`,
    CHANGE COLUMN `verbEN` `verb_loc0`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `iconString`,
    CHANGE COLUMN `verbFR` `verb_loc2`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verb_loc0`,
    CHANGE COLUMN `verbDE` `verb_loc3`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verb_loc2`,
    CHANGE COLUMN `verbES` `verb_loc6`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verb_loc3`,
    CHANGE COLUMN `verbRU` `verb_loc8`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verb_loc6`,
    ADD COLUMN `professionMask`  bigint(20) NOT NULL AFTER `canLink`,
    ADD COLUMN `recipeSubClass`  bigint(20) NOT NULL AFTER `professionMask`,
    ADD COLUMN `specializations`  text NOT NULL COMMENT 'spcae-separated spellIds' AFTER `recipeSubClass`;

-- manual data for professions
UPDATE aowow_skillLine SET professionMask = 0                                                                          WHERE id = 393;
UPDATE aowow_skillLine SET professionMask = 1,    recipeSubClass = 6,  specializations = '28677 28675 28672'           WHERE id = 171;
UPDATE aowow_skillLine SET professionMask = 2,    recipeSubClass = 4,  specializations = '9788 9787 17041 17040 17039' WHERE id = 164;
UPDATE aowow_skillLine SET professionMask = 4,    recipeSubClass = 5                                                   WHERE id = 185;
UPDATE aowow_skillLine SET professionMask = 8,    recipeSubClass = 8                                                   WHERE id = 333;
UPDATE aowow_skillLine SET professionMask = 16,   recipeSubClass = 3,  specializations = '20219 20222'                 WHERE id = 202;
UPDATE aowow_skillLine SET professionMask = 32,   recipeSubClass = 7                                                   WHERE id = 129;
UPDATE aowow_skillLine SET professionMask = 64,   recipeSubClass = 10                                                  WHERE id = 755;
UPDATE aowow_skillLine SET professionMask = 128,  recipeSubClass = 1,  specializations = '10656 10658 10660'           WHERE id = 165;
UPDATE aowow_skillLine SET professionMask = 256                                                                        WHERE id = 186;
UPDATE aowow_skillLine SET professionMask = 512,  recipeSubClass = 2,  specializations = '26798 26801 26797'           WHERE id = 197;
UPDATE aowow_skillLine SET professionMask = 1024, recipeSubClass = 9                                                   WHERE id = 356;
UPDATE aowow_skillLine SET professionMask = 2048                                                                       WHERE id = 182;
UPDATE aowow_skillLine SET professionMask = 4096, recipeSubClass = 11                                                  WHERE id = 773;

-- fixups
UPDATE aowow_skillLine SET spellIconId = 736 WHERE id = 393;                                                                                -- skinning has generic icon
UPDATE aowow_skillLine SET spellIconId = 936 WHERE id = 633;                                                                                -- lockpicking has generic icon
UPDATE aowow_skillLine SET name_loc0 = 'Pet - Wasp' WHERE id = 785;                                                                         -- the naming in general is fubar inconsistent
UPDATE aowow_skillLine SET name_loc2 = 'Familier - diablosaure exotique' WHERE id = 781;
UPDATE aowow_skillLine SET name_loc6 = 'Mascota: Evento - Control remoto', name_loc3 = 'Tier - Ereignis Ferngesteuert' WHERE id = 758;
UPDATE aowow_skillLine SET name_loc8 = REPLACE(name_loc8, ' - ', ': ') WHERE categoryId = 7;
UPDATE aowow_skillLine SET categoryId = 7 WHERE id IN (758, 788);                                                                           -- spirit beast listed under Attributes; remote controled pet listed under bogus

-- iconstrings
UPDATE aowow_skillLine sl, dbc.spell s, dbc.skillLineAbility sla SET sl.spellIconId = s.spellIconId WHERE (s.effectId1 IN (25, 26, 40) OR s.effectId2 = 60) AND sla.spellId = s.id AND sl.id = sla.skillLineId;
UPDATE aowow_skillLine sl, dbc.spellIcon si SET sl.iconString = SUBSTRING_INDEX(si.string, '\\', -1) WHERE sl.spellIconId = si.id;
UPDATE aowow_skillLine SET iconString = 'inv_misc_questionmark' WHERE spellIconId = 0;

-- categorization
UPDATE aowow_skillLine SET typeCat = -5 WHERE id = 777 OR (categoryId = 9 AND id NOT IN (356, 129, 185, 142, 155));
UPDATE aowow_skillLine SET typeCat = -4 WHERE categoryId = 9 AND name_loc0 LIKE '%racial%';
UPDATE aowow_skillLine SET typeCat = -6 WHERE id = 778 OR (categoryId = 7 AND name_loc0 LIKE '%pet%');
UPDATE aowow_skillLine SET typeCat = categoryId WHERE typeCat = 0;
