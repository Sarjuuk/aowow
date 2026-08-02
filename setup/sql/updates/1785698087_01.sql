DROP TABLE IF EXISTS `dbc_spell`;

TRUNCATE TABLE `aowow_spell`; -- it will be rebuild anyway so no point restructuring that with 60k rows present

ALTER TABLE `aowow_spell`
    MODIFY COLUMN `stanceMask` int(11) unsigned NOT NULL,
    MODIFY COLUMN `stanceMaskNot` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect1SpellClassMaskA` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect1SpellClassMaskB` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect1SpellClassMaskC` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect2SpellClassMaskA` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect2SpellClassMaskB` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect2SpellClassMaskC` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect3SpellClassMaskA` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect3SpellClassMaskB` int(11) unsigned NOT NULL,
    MODIFY COLUMN `effect3SpellClassMaskC` int(11) unsigned NOT NULL,
    MODIFY COLUMN `spellFamilyFlags1` int(11) unsigned NOT NULL,
    MODIFY COLUMN `spellFamilyFlags2` int(11) unsigned NOT NULL,
    MODIFY COLUMN `spellFamilyFlags3` int(11) unsigned NOT NULL
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' totemcategory spell');
