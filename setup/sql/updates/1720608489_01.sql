DROP TABLE IF EXISTS dbc_spell;

ALTER TABLE `aowow_spell`
    DROP COLUMN `effect1SpellClassMaskA`,
    DROP COLUMN `effect2SpellClassMaskA`,
    DROP COLUMN `effect3SpellClassMaskA`,
    DROP COLUMN `effect1SpellClassMaskB`,
    DROP COLUMN `effect2SpellClassMaskB`,
    DROP COLUMN `effect3SpellClassMaskB`,
    DROP COLUMN `effect1SpellClassMaskC`,
    DROP COLUMN `effect2SpellClassMaskC`,
    DROP COLUMN `effect3SpellClassMaskC`;

ALTER TABLE `aowow_spell`
    ADD COLUMN `effect1SpellClassMaskA` int NOT NULL AFTER `effect3PointsPerComboPoint`,
    ADD COLUMN `effect1SpellClassMaskB` int NOT NULL AFTER `effect1SpellClassMaskA`,
    ADD COLUMN `effect1SpellClassMaskC` int NOT NULL AFTER `effect1SpellClassMaskB`,
    ADD COLUMN `effect2SpellClassMaskA` int NOT NULL AFTER `effect1SpellClassMaskC`,
    ADD COLUMN `effect2SpellClassMaskB` int NOT NULL AFTER `effect2SpellClassMaskA`,
    ADD COLUMN `effect2SpellClassMaskC` int NOT NULL AFTER `effect2SpellClassMaskB`,
    ADD COLUMN `effect3SpellClassMaskA` int NOT NULL AFTER `effect2SpellClassMaskC`,
    ADD COLUMN `effect3SpellClassMaskB` int NOT NULL AFTER `effect3SpellClassMaskA`,
    ADD COLUMN `effect3SpellClassMaskC` int NOT NULL AFTER `effect3SpellClassMaskB`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spell');
