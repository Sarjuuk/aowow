ALTER TABLE `aowow_shapeshiftforms`
	ADD COLUMN `comment` VARCHAR(30) NULL AFTER `spellId8`,
	DROP COLUMN `name_loc0`,
	DROP COLUMN `name_loc2`,
	DROP COLUMN `name_loc3`,
	DROP COLUMN `name_loc6`,
	DROP COLUMN `name_loc8`;
