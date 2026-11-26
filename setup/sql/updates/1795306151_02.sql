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
-- type: zone (7) (instance entrances) was negative for all cases
UPDATE `aowow_spawns_override` SET `typeGuid` = ABS(`typeGuid`) WHERE `typeGuid` < 0 AND `type` = 7;
-- type: areatrigger (503) (teleporter destinations) depend on not yet determined vGUIDs
CREATE TABLE `aowow_spawns_override_DEPRECATED` LIKE `aowow_spawns_override`;
INSERT INTO `aowow_spawns_override_DEPRECATED` SELECT * FROM `aowow_spawns_override` WHERE `typeGUID` < 0;
DELETE FROM `aowow_spawns_override` WHERE `typeGuid` < 0;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spawns objects areatrigger');
