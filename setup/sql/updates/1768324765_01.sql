ALTER TABLE `aowow_taxinodes`
  CHANGE COLUMN `posX` `mapX` float unsigned NOT NULL,
  CHANGE COLUMN `posY` `mapY` float unsigned NOT NULL,
  ADD COLUMN `areaId` smallint(5) unsigned NOT NULL AFTER `mapY`,
  ADD COLUMN `areaX` float unsigned NOT NULL AFTER `areaId`,
  ADD COLUMN `areaY` float unsigned NOT NULL AFTER `areaX`
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' taxi');
