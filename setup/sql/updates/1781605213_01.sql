ALTER TABLE `aowow_spell`
    ADD COLUMN `casterAuraSpell` mediumint(8) unsigned NOT NULL AFTER `spellFocusObject`,
    ADD COLUMN `targetAuraSpell` mediumint(8) unsigned NOT NULL AFTER `casterAuraSpell`,
    ADD COLUMN `casterAuraSpellNot` mediumint(8) unsigned NOT NULL AFTER `targetAuraSpell`,
    ADD COLUMN `targetAuraSpellNot` mediumint(8) unsigned NOT NULL AFTER `casterAuraSpellNot`;
