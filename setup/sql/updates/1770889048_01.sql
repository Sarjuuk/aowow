ALTER TABLE aowow_items
  ADD COLUMN `effects_loc0` text DEFAULT NULL AFTER `flagsCustom`,
  ADD COLUMN `effects_loc2` text DEFAULT NULL AFTER `effects_loc0`,
  ADD COLUMN `effects_loc3` text DEFAULT NULL AFTER `effects_loc2`,
  ADD COLUMN `effects_loc4` text DEFAULT NULL AFTER `effects_loc3`,
  ADD COLUMN `effects_loc6` text DEFAULT NULL AFTER `effects_loc4`,
  ADD COLUMN `effects_loc8` text DEFAULT NULL AFTER `effects_loc6`;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' items');
