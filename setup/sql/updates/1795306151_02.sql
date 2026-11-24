DROP TABLE IF EXISTS `dbc_taxipathnode`;
DROP TABLE IF EXISTS `aowow_soundemitters`;
DROP TABLE IF EXISTS `aowow_spawns`;

ALTER TABLE `aowow_areatrigger`
    DROP COLUMN `mapId`,
    DROP COLUMN `posX`,
    DROP COLUMN `posY`
;

ALTER TABLE `aowow_zones`
    DROP COLUMN `parentMapId`,
    DROP COLUMN `parentX`,
    DROP COLUMN `parentY`
;

-- indexing changed for virtual guids
DELETE FROM `aowow_spawns_override` WHERE `typeGuid` < 0;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spawns objects areatrigger');
