ALTER TABLE `aowow_factions`
    ADD COLUMN `baseRepRaceMask1` SMALLINT(5) UNSIGNED NOT NULL AFTER `repIdx`,
    ADD COLUMN `baseRepRaceMask2` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepRaceMask1`,
    ADD COLUMN `baseRepRaceMask3` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepRaceMask2`,
    ADD COLUMN `baseRepRaceMask4` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepRaceMask3`,
    ADD COLUMN `baseRepClassMask1` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepRaceMask4`,
    ADD COLUMN `baseRepClassMask2` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepClassMask1`,
    ADD COLUMN `baseRepClassMask3` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepClassMask2`,
    ADD COLUMN `baseRepClassMask4` SMALLINT(5) UNSIGNED NOT NULL AFTER `baseRepClassMask3`,
    ADD COLUMN `baseRepValue1` MEDIUMINT NOT NULL AFTER `baseRepClassMask4`,
    ADD COLUMN `baseRepValue2` MEDIUMINT NOT NULL AFTER `baseRepValue1`,
    ADD COLUMN `baseRepValue3` MEDIUMINT NOT NULL AFTER `baseRepValue2`,
    ADD COLUMN `baseRepValue4` MEDIUMINT NOT NULL AFTER `baseRepValue3`;
