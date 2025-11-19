DROP TABLE IF EXISTS `aowow_objectdifficulty`;
CREATE TABLE `aowow_objectdifficulty` (
    `normal10` mediumint(8) unsigned NOT NULL,
    `normal25` mediumint(8) unsigned NOT NULL,
    `heroic10` mediumint(8) unsigned NOT NULL,
    `heroic25` mediumint(8) unsigned NOT NULL,
    `mapType` tinyint(3) unsigned NOT NULL,
    KEY `normal10` (`normal10`),
    KEY `normal25` (`normal25`),
    KEY `heroic10` (`heroic10`),
    KEY `heroic25` (`heroic25`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aowow_objectdifficulty` VALUES
    (181366, 193426, 0,      0     , 2), -- naxxramas: four horsemen chest
    (193905, 193967, 0,      0     , 2), -- eoe: alexstrasza's gift
    (194307, 194308, 194200, 194201, 2), -- ulduar: cache of winter
    (194312, 194314, 194313, 194315, 2), -- ulduar: cache of storms
    (194324, 194328, 194325, 194329, 2), -- ulduar: freya's gift +1 elder
    (194324, 194328, 194326, 194330, 2), -- ulduar: freya's gift +2 elder
    (194324, 194328, 194327, 194331, 2), -- ulduar: freya's gift +3 elder
    (194789, 194956, 194957, 194958, 2), -- ulduar: cache of innovation
    (194821, 194822, 0,      0     , 2), -- ulduar: gift of the observer
    (195046, 195047, 0,      0     , 2), -- ulduar: cache of living stone
    (195631, 195632, 195633, 195635, 2), -- toc25: champions' cache
    (202178, 202180, 202177, 202179, 2), -- icc: gunship armory (horde)
    (201873, 201874, 201872, 201875, 2), -- icc: gunship armory (alliance)
    (202239, 202240, 202238, 202241, 2), -- icc: deathbringer's cache
    (201959, 202339, 202338, 202340, 2), -- icc: cache of the dreamwalker
    (0,      0,      195668, 195672, 2), -- toc25: argent crusade tribute chest  1TL
    (0,      0,      195667, 195671, 2), -- toc25: argent crusade tribute chest 25TL
    (0,      0,      195666, 195670, 2), -- toc25: argent crusade tribute chest 45TL
    (0,      0,      195665, 195669, 2), -- toc25: argent crusade tribute chest 50TL
    (185168, 185169, 0,      0     , 1), -- hellfire ramparts: reinforced fel iron chest
    (184465, 184849, 0,      0     , 1), -- mechanar: cache of the legion
    (190586, 193996, 0,      0     , 1), -- halls of stone: tribunal chest
    (190663, 193597, 0,      0     , 1), -- cot - cos: dark runed chest
    (191349, 193603, 0,      0     , 1), -- oculus: cache of eregos
    (195709, 195710, 0,      0     , 1), -- toc5: champion's cache
    (195323, 195324, 0,      0     , 1), -- toc5: confessor's cache
    (195374, 195375, 0,      0     , 1), -- toc5: eadric's cache
    (201710, 202336, 0,      0     , 1); -- hor: captain's chest
