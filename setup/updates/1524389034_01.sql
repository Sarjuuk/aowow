REPLACE INTO aowow_profiler_excludes (`type`, `typeId`, `groups`, `comment`) VALUES
    (6, 46197, 2, 'X-51 Nether-Rocket - TCG loot'),
    (6, 46199, 2, 'X-51 Nether-Rocket X-TREME - TCG loot'),
    (6, 75614, 1, 'Celestial Steed - unavailable'),
    (6, 26656, 1, 'Black Qiraji Battle Tank - unavailable'),
    (6, 43899, 1, 'Brewfest Ram - unavailable'),
    (6, 58983, 8, 'Big Blizzard Bear - promotion'),
    (6, 49193, 1, 'Vengeful Nether Drake - unavailable'),
    (6, 58615, 1, 'Brutal Nether Drake - unavailable'),
    (6, 64927, 1, 'Deadly Gladiator\'s Frost Wyrm - unavailable'),
    (6, 65439, 1, 'Furious Gladiator\'s Frost Wyrm - unavailable'),
    (6, 67336, 1, 'Relentless Gladiator\'s Frost Wyrm - unavailable'),
    (6, 71810, 1, 'Wrathful Gladiator\'s Frost Wyrm - unavailable'),
    (11, 122, 1, 'RealmFirst Kel\'T Title - unavailable'),
    (11, 159, 1, 'RealmFirst Algalon Title - unavailable'),
    (11, 120, 1, 'RealmFirst Maly Title - unavailable'),
    (11, 170, 1, 'RealmFirst TotGC Title - unavailable'),
    (11, 139, 1, 'RealmFirst Sarth Title - unavailable'),
    (11, 158, 1, 'RealmFirst Yogg Title - unavailable'),
    (6, 40405, 16, 'Lucky - wrong region'),
    (6, 45174, 16, 'Golden Pig - wrong region'),
    (6, 67527, 16, 'Onyx Panther - wrong region'),
    (6, 28505, 8, 'Poley - promotion'),
    (6, 45175, 16, 'Silver Pig - wrong region'),
    (6, 28487, 1, 'Terky - unavailable'),
    (6, 23531, 16, 'Tiny Green Dragon - wrong region'),
    (6, 23530, 16, 'Tiny Red Dragon - wrong region'),
    (8, 70, 1024, 'Syndicate - max rank is neutral'),
    (6, 48408, 16, 'Essence of Competition - wrong region');

DELETE FROM aowow_profiler_excludes WHERE `type` = 6 AND `typeId` IN (66122, 66123, 66124, 61309, 61451, 75596);

UPDATE `aowow_dbversion` SET `build` = CONCAT(IFNULL(`build`, ''), ' enchants profiler');
