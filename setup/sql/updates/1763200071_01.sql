UPDATE `aowow_pet` SET `expansion` = 1 WHERE `id` IN (30, 31, 32, 33, 34);
UPDATE `aowow_pet` SET `expansion` = 2 WHERE `id` IN (37, 38, 39, 41, 42, 43, 44, 45, 46);

DELETE FROM `aowow_setup_custom_data` WHERE `command` = 'pet' AND `field` = 'expansion';
INSERT INTO `aowow_setup_custom_data` VALUES
    ('pet', 30, 'expansion', 1, 'Pet - Dragonhawk: BC'),
    ('pet', 31, 'expansion', 1, 'Pet - Ravager: BC'),
    ('pet', 32, 'expansion', 1, 'Pet - Warp Stalker: BC'),
    ('pet', 33, 'expansion', 1, 'Pet - Sporebat: BC'),
    ('pet', 34, 'expansion', 1, 'Pet - Nether Ray: BC'),
    ('pet', 37, 'expansion', 2, 'Pet - Moth: WotLK'),
    ('pet', 38, 'expansion', 2, 'Pet - Chimaera: WotLK'),
    ('pet', 39, 'expansion', 2, 'Pet - Devilsaur: WotLK'),
    ('pet', 41, 'expansion', 2, 'Pet - Silithid: WotLK'),
    ('pet', 42, 'expansion', 2, 'Pet - Worm: WotLK'),
    ('pet', 43, 'expansion', 2, 'Pet - Rhino: WotLK'),
    ('pet', 44, 'expansion', 2, 'Pet - Wasp: WotLK'),
    ('pet', 45, 'expansion', 2, 'Pet - Core Hound: WotLK'),
    ('pet', 46, 'expansion', 2, 'Pet - Spirit Beast: WotLK')
;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' pet');
