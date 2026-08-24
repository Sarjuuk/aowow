-- undo sunken temple data
DELETE FROM aowow_setup_custom_data WHERE `command` = 'zones' AND `entry` IN (1477, 1417);
-- undo icc unused subzone linking (still has EXCLUDE_FOR_LISTVIEW set)
DELETE FROM aowow_setup_custom_data WHERE `command` = 'zones' AND `field` = 'parentAreaId' AND `value` = 4812;
-- undo Hellfire Citadel recategorization
DELETE FROM aowow_setup_custom_data WHERE `command` = 'quests' AND `field` = 'zoneOrSort' AND `entry` IN (9572, 9575, 11354, 9589, 9590, 9607, 9608, 11362, 9492, 9493, 9494, 9495, 9496, 9497, 9524, 9525, 11363, 11364);
INSERT INTO aowow_setup_custom_data VALUES
    ('zones', 1417, 'cuFlags', 1073741824, 'Sunken Temple [extra area on map 109] - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    ('zones',   22, 'cuFlags', 1073741824, 'Programmer Isle - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    ('zones',  151, 'cuFlags', 1073741824, 'Designer Island - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    ('zones', 3948, 'cuFlags', 1073741824, 'Brian and Pat Test - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    ('zones', 4019, 'cuFlags', 1073741824, 'Development Land - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    ('zones', 3605, 'cuFlags', 1073741824, 'Hyjal Past [extra area on map 560] - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    ('zones', 3535, 'cuFlags', 1073741824, 'Hellfire Citadel [extra area on map 540] - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
    -- move quests from generic Hellfire Citadel to...
    -- ...Hellfire Ramparts [3562]
    ('quests', 9572, 'zoneOrSort', 3562, 'Weaken the Ramparts - category Hellfire Citadel -> Hellfire Ramparts'),
    ('quests', 9575, 'zoneOrSort', 3562, 'Weaken the Ramparts - category Hellfire Citadel -> Hellfire Ramparts'),
    ('quests', 11354, 'zoneOrSort', 3562, "Wanted: Nazan's Riding Crop - category Hellfire Citadel -> Hellfire Ramparts"),
    -- ...The Blood Furnace [3713]
    ('quests', 9589, 'zoneOrSort', 3713, 'The Blood is Life - category Hellfire Citadel -> Blood Furnace'),
    ('quests', 9590, 'zoneOrSort', 3713, 'The Blood is Life - category Hellfire Citadel -> Blood Furnace'),
    ('quests', 9607, 'zoneOrSort', 3713, 'Heart of Rage - category Hellfire Citadel -> Blood Furnace'),
    ('quests', 9608, 'zoneOrSort', 3713, 'Heart of Rage - category Hellfire Citadel -> Blood Furnace'),
    ('quests', 11362, 'zoneOrSort', 3713, "Wanted: Keli'dan's Feathered Stave - category Hellfire Citadel -> Blood Furnace"),
    -- ...The Shattered Halls [3714]
    ('quests', 9492, 'zoneOrSort', 3714, 'Turning the Tide - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9493, 'zoneOrSort', 3714, 'Pride of the Fel Horde - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9494, 'zoneOrSort', 3714, 'Fel Embers - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9495, 'zoneOrSort', 3714, 'The Will of the Warchief - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9496, 'zoneOrSort', 3714, 'Pride of the Fel Horde - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9497, 'zoneOrSort', 3714, 'Emblem of the Fel Horde - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9524, 'zoneOrSort', 3714, 'Imprisoned in the Citadel - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 9525, 'zoneOrSort', 3714, 'Imprisoned in the Citadel - category Hellfire Citadel -> Shattered Halls'),
    ('quests', 11363, 'zoneOrSort', 3714, "Wanted: Bladefist's Seal - category Hellfire Citadel -> Shattered Halls"),
    ('quests', 11364, 'zoneOrSort', 3714, 'Wanted: Shattered Hand Centurions - category Hellfire Citadel -> Shattered Halls');

-- implement SpawnedByDefault
ALTER TABLE aowow_spawns
    MODIFY COLUMN `respawn` int signed NOT NULL DEFAULT 0;

-- rebuild spawns
UPDATE aowow_dbversion
    SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spawns quests');
