DROP TABLE IF EXISTS `aowow_loot_link`;
CREATE TABLE `aowow_loot_link` (
    `npcId` MEDIUMINT(8) NOT NULL,
    `objectId` MEDIUMINT(8) UNSIGNED NOT NULL,
    UNIQUE INDEX `npcId` (`npcId`),
    INDEX `objectId` (`objectId`)
) COLLATE='utf8_general_ci' ENGINE=MyISAM;

INSERT INTO `aowow_loot_link` VALUES
    -- if available HM-loot is show instead of regular loot (notably Ulduar)
    -- Classic
    -- no boss chests..?
    -- BC
    (17537, 185168), (18434, 185169),                       -- Ramparts NH/HC - Vazruden
    (17536, 185168), (18432, 185169),                       -- Ramparts NH/HC - Nazan
    (19218, 184465), (21525, 184849),                       -- The Mechanar NH/HC - Gatewatcher Gyro-Kill
    (19710, 184465), (21526, 184849),                       -- The Mechanar NH/HC - Gatewatcher Iron-Hand
    -- WotLK
    (28234, 190586), (-28234, 193996),                      -- Halls of Stone NH/HC - Tribunal of Ages
    (27656, 191349), (31561, 193603),                       -- Oculus NH/HC - Ley Guardian Eregos
    (26533, 190663), (31217, 193597),                       -- CoT Stratholme NH/HC - Mal' Ganis
    (16064, 181366), (30603, 193426),                       -- Naxxramas 10/25 - Thane Korth'azz
    (16065, 181366), (30601, 193426),                       -- Naxxramas 10/25 - Lady Blaumeux
    (30549, 181366), (30600, 193426),                       -- Naxxramas 10/25 - Baron Rivendare
    (16063, 181366), (30602, 193426),                       -- Naxxramas 10/25 - Sir Zeliek
    (28859, 193905), (31734, 193967),                       -- EoE 10/25 - Malygos
    (32930, 195046), (33909, 195047),                       -- Ulduar 10/25 - Kologarn
    (32865, 194313), (33147, 194315),                       -- Ulduar 10/25 - Thorim
    (33350, 194957), (-33350, 194958),                      -- Ulduar 10/25 - Mimiron
    (32845, 194200), (32846, 194201),                       -- Ulduar 10/25 - Hodir
    (32906, 194324), (33360, 194325),                       -- Ulduar 10/25 - Freya
    (32871, 194821), (33070, 194822),                       -- Ulduar 10/25 - Algalon
    (35119, 195374), (35518, 195375),                       -- ToC5 NH/HC - Eadric the Pure
    (34928, 195323), (35517, 195324),                       -- ToC5 NH/HC - Argent Confessor Paletress
    (34705, 195709), (36088, 195710),                       -- Toc5 NH/HC - Marshal Jacob Alerius
    (34702, 195709), (36082, 195710),                       -- Toc5 NH/HC - Ambrose Boltspark
    (34701, 195709), (36083, 195710),                       -- Toc5 NH/HC - Colosos
    (34657, 195709), (36086, 195710),                       -- Toc5 NH/HC - Jaelyne Evensong
    (34703, 195709), (36087, 195710),                       -- Toc5 NH/HC - Lana Stouthammer
    (35572, 195709), (36089, 195710),                       -- Toc5 NH/HC - Mokra the Skullcrusher
    (35569, 195709), (36085, 195710),                       -- Toc5 NH/HC - Eressea Dawnsinger
    (35571, 195709), (36090, 195710),                       -- Toc5 NH/HC - Runok Wildmane
    (35570, 195709), (36091, 195710),                       -- Toc5 NH/HC - Zul'tore
    (35617, 195709), (36084, 195710),                       -- Toc5 NH/HC - Deathstalker Visceri
    (34441, 195631), (34442, 195632),                       -- ToC25 10/25 NM - Vivienne Blackwhisper
    (34443, 195633), (-34443, 195635),                      -- ToC25 10/25 HC - Vivienne Blackwhisper
    (34444, 195631), (35740, 195632),                       -- ToC25 10/25 NM - Thrakgar
    (35741, 195633), (-35741, 195635),                      -- ToC25 10/25 HC - Thrakgar
    (34445, 195631), (35705, 195632),                       -- ToC25 10/25 NM - Liandra Suncaller
    (35706, 195633), (-35706, 195635),                      -- ToC25 10/25 HC - Liandra Suncaller
    (34447, 195631), (35683, 195632),                       -- ToC25 10/25 NM - Caiphus the Stern
    (35684, 195633), (-35684, 195635),                      -- ToC25 10/25 HC - Caiphus the Stern
    (34448, 195631), (35724, 195632),                       -- ToC25 10/25 NM - Ruj'kah
    (35725, 195633), (-35725, 195635),                      -- ToC25 10/25 HC - Ruj'kah
    (34449, 195631), (35689, 195632),                       -- ToC25 10/25 NM - Ginselle Blightslinger
    (35690, 195633), (-35690, 195635),                      -- ToC25 10/25 HC - Ginselle Blightslinger
    (34450, 195631), (35695, 195632),                       -- ToC25 10/25 NM - Harkzog
    (35696, 195633), (-35696, 195635),                      -- ToC25 10/25 HC - Harkzog
    (34451, 195631), (35671, 195632),                       -- ToC25 10/25 NM - Birana Stormhoof
    (35672, 195633), (-35672, 195635),                      -- ToC25 10/25 HC - Birana Stormhoof
    (34453, 195631), (35718, 195632),                       -- ToC25 10/25 NM - Narrhok Steelbreaker
    (35719, 195633), (-35719, 195635),                      -- ToC25 10/25 HC - Narrhok Steelbreaker
    (34454, 195631), (35711, 195632),                       -- ToC25 10/25 NM - Maz'dinah
    (35712, 195633), (-35712, 195635),                      -- ToC25 10/25 HC - Maz'dinah
    (34455, 195631), (35680, 195632),                       -- ToC25 10/25 NM - Broln Stouthorn
    (35681, 195633), (-35681, 195635),                      -- ToC25 10/25 HC - Broln Stouthorn
    (34456, 195631), (35708, 195632),                       -- ToC25 10/25 NM - Malithas Brightblade
    (35709, 195633), (-35709, 195635),                      -- ToC25 10/25 HC - Malithas Brightblade
    (34458, 195631), (35692, 195632),                       -- ToC25 10/25 NM - Gorgrim Shadowcleave
    (35693, 195633), (-35693, 195635),                      -- ToC25 10/25 HC - Gorgrim Shadowcleave
    (34459, 195631), (35686, 195632),                       -- ToC25 10/25 NM - Erin Misthoof
    (35687, 195633), (-35687, 195635),                      -- ToC25 10/25 HC - Erin Misthoof
    (34460, 195631), (35702, 195632),                       -- ToC25 10/25 NM - Kavina Grovesong
    (35703, 195633), (-35703, 195635),                      -- ToC25 10/25 HC - Kavina Grovesong
    (34461, 195631), (35743, 195632),                       -- ToC25 10/25 NM - Tyrius Duskblade
    (35744, 195633), (-35744, 195635),                      -- ToC25 10/25 HC - Tyrius Duskblade
    (34463, 195631), (35734, 195632),                       -- ToC25 10/25 NM - Shaabad
    (35735, 195633), (-35735, 195635),                      -- ToC25 10/25 HC - Shaabad
    (34465, 195631), (35746, 195632),                       -- ToC25 10/25 NM - Velanaa
    (35747, 195633), (-35747, 195635),                      -- ToC25 10/25 HC - Velanaa
    (34466, 195631), (35665, 195632),                       -- ToC25 10/25 NM - Anthar Forgemender
    (35666, 195633), (-35666, 195635),                      -- ToC25 10/25 HC - Anthar Forgemender
    (34467, 195631), (35662, 195632),                       -- ToC25 10/25 NM - Alyssia Moonstalker
    (35663, 195633), (-35663, 195635),                      -- ToC25 10/25 HC - Alyssia Moonstalker
    (34468, 195631), (35721, 195632),                       -- ToC25 10/25 NM - Noozle Whizzlestick
    (35722, 195633), (-35722, 195635),                      -- ToC25 10/25 HC - Noozle Whizzlestick
    (34469, 195631), (35714, 195632),                       -- ToC25 10/25 NM - Melador Valestrider
    (35715, 195633), (-35715, 195635),                      -- ToC25 10/25 HC - Melador Valestrider
    (34470, 195631), (35728, 195632),                       -- ToC25 10/25 NM - Saamul
    (35729, 195633), (-35729, 195635),                      -- ToC25 10/25 HC - Saamul
    (34471, 195631), (35668, 195632),                       -- ToC25 10/25 NM - Baelnor Lightbearer
    (35669, 195633), (-35669, 195635),                      -- ToC25 10/25 HC - Baelnor Lightbearer
    (34472, 195631), (35699, 195632),                       -- ToC25 10/25 NM - Irieth Shadowstep
    (35700, 195633), (-35700, 195635),                      -- ToC25 10/25 HC - Irieth Shadowstep
    (34473, 195631), (35674, 195632),                       -- ToC25 10/25 NM - Brienna Nightfell
    (35675, 195633), (-35675, 195635),                      -- ToC25 10/25 HC - Brienna Nightfell
    (34474, 195631), (35731, 195632),                       -- ToC25 10/25 NM - Serissa Grimdabbler
    (35732, 195633), (-35732, 195635),                      -- ToC25 10/25 HC - Serissa Grimdabbler
    (34475, 195631), (35737, 195632),                       -- ToC25 10/25 NM - Shocuul
    (35738, 195633), (-35738, 195635),                      -- ToC25 10/25 HC - Shocuul
    (37226, 201710), (-37226, 202336),                      -- HoR NH/HC - The Lich King
    (36948, 202178), (38157, 202180),                       -- ICC 10/25 NM - Muradin Bronzebread
    (38639, 202177), (38640, 202179),                       -- ICC 10/25 HC - Muradin Bronzebread
    (36939, 202178), (38156, 202180),                       -- ICC 10/25 NM - High Overlord Saurfang
    (38637, 202177), (38638, 202179);                       -- ICC 10/25 HC - High Overlord Saurfang
