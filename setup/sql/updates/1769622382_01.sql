ALTER TABLE `aowow_icons` ADD KEY "idx_sourcename" (`name_source`);

UPDATE `aowow_dbversion` SET
    `sql`   = CONCAT(IFNULL(`sql`, ''), ' achievement currencies glyphproperties holidays icons items pet skillline spell'),
    `build` = CONCAT(IFNULL(`build`, ''), ' enchants gems glyphs talenticons')
;
