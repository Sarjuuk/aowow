DROP TABLE IF EXISTS `aowow_setup_custom_data`;

CREATE TABLE `aowow_setup_custom_data` (
  `command` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `entry` int NOT NULL DEFAULT '0' COMMENT 'typeId',
  `field` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `value` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  KEY `aowow_setup_custom_data_command_IDX` (`command`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('zones',2257,'cuFlags','0','Deeprun Tram - make visible'),
	 ('zones',2257,'category','0','Deeprun Tram -  Category: Eastern Kingdoms'),
	 ('zones',2257,'type','1','Deeprun Tram - Type: Transit'),
	 ('zones',3698,'expansion','1','Nagrand Arena - Addon: BC'),
	 ('zones',3702,'expansion','1','Blades Edge Arena - Addon: BC'),
	 ('zones',3968,'expansion','1','Ruins of Lordaeron Arena - Addon: BC'),
	 ('zones',4378,'expansion','1','Dalaran Arena - Addon: WotLK'),
	 ('zones',4406,'expansion','1','Ring of Valor Arena - Addon: WotLK'),
	 ('zones',2597,'maxPlayer','40','Alterac Valey - Players: 40 [battlemasterlist.dbc: 5]'),
	 ('zones',4710,'maxPlayer','40','Isle of Conquest - Players: 40 [battlemasterlist.dbc: 5]');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('zones',3849,'parentAreaId','3523','The Mechanar - Parent: Netherstorm [not set in map.dbc]'),
	 ('zones',3849,'parentX','87.3','The Mechanar - Entrance xPos'),
	 ('zones',3849,'parentY','51.1','The Mechanar - Entrance yPos'),
	 ('zones',3847,'parentAreaId','3523','The Botanica - Parent: Netherstorm [not set in map.dbc]'),
	 ('zones',3847,'parentX','71.7','The Botanica - Entrance xPos'),
	 ('zones',3847,'parentY','55.1','The Botanica - Entrance yPos'),
	 ('zones',3848,'parentAreaId','3523','The Arcatraz - Parent: Netherstorm [not set in map.dbc]'),
	 ('zones',3848,'parentX','74.3','The Arcatraz - Entrance xPos'),
	 ('zones',3848,'parentY','57.8','The Arcatraz - Entrance yPos'),
	 ('zones',3845,'parentAreaId','3523','Tempest Keep -  Parent: Netherstorm [not set in map.dbc]');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('zones',3845,'parentX','73.5','Tempest Keep  - Entrance xPos'),
	 ('zones',3845,'parentY','63.7','Tempest Keep  - Entrance yPos'),
	 ('zones',3456,'parentAreaId','65','Naxxramas -  Parent: Netherstorm [not set in map.dbc]'),
	 ('zones',3456,'parentX','87.3','Naxxramas - Entrance xPos'),
	 ('zones',3456,'parentY','87.3','Naxxramas - Entrance yPos'),
	 ('zones',4893,'parentAreaId','4812','The Frost Queen''s Lair - Parent: Icecrown Citadel'),
	 ('zones',4894,'parentAreaId','4812','Putricide''s Laboratory [..] - Parent: Icecrown Citadel'),
	 ('zones',4895,'parentAreaId','4812','The Crimson Hall - Parent: Icecrown Citadel'),
	 ('zones',4896,'parentAreaId','4812','The Frozen Throne - Parent: Icecrown Citadel'),
	 ('zones',4897,'parentAreaId','4812','The Sanctum of Blood - Parent: Icecrown Citadel');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('zones',4893,'cuFlags','1073741824','The Frost Queen''s Lair - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('zones',4894,'cuFlags','1073741824','Putricide''s Laboratory [..] - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('achievement',1956,'itemExtra','44738','Higher Learning - item rewarded through gossip'),
	 ('zones',4895,'cuFlags','1073741824','The Crimson Hall - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('titles',137,'gender','2','Matron - female'),
	 ('zones',4896,'cuFlags','1073741824','The Frozen Throne - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('zones',4897,'cuFlags','1073741824','The Sanctum of Blood - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('zones',4076,'cuFlags','1073741824','Reuse Me 7 - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('zones',207,'cuFlags','1073741824','The Great Sea - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('zones',208,'cuFlags','1073741824','Unused Ironcladcove - set: CUSTOM_EXCLUDE_FOR_LISTVIEW');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('zones',2817,'levelMin','74','Crystalsong Forest - missing lfgDungeons entry'),
	 ('zones',1477,'cuFlags','1073741824','The Temple of Atal''Hakkar - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('zones',41,'levelMin','50','Deadwind Pass - missing lfgDungeons entry'),
	 ('zones',41,'levelMax','60','Deadwind Pass - missing lfgDungeons entry'),
	 ('zones',2257,'levelMin','1','Deeprun Tram - missing lfgDungeons entry'),
	 ('zones',2257,'levelMax','80','Deeprun Tram - missing lfgDungeons entry'),
	 ('zones',4298,'category','0','Plaguelands: The Scarlet Enclave - Parent: Eastern Kingdoms'),
	 ('zones',4298,'levelMin','55','Plaguelands: The Scarlet Enclave - missing lfgDungeons entry'),
	 ('zones',4298,'levelMax','58','Plaguelands: The Scarlet Enclave - missing lfgDungeons entry'),
	 ('zones',493,'levelMin','15','Moonglade - missing lfgDungeons entry');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('zones',493,'levelMax','60','Moonglade - missing lfgDungeons entry'),
	 ('zones',2817,'levelMax','76','Crystalsong Forest - missing lfgDungeons entry'),
	 ('zones',4742,'levelMin','77','Hrothgar''s Landing - missing lfgDungeons entry'),
	 ('zones',4742,'levelMax','80','Hrothgar''s Landing - missing lfgDungeons entry'),
	 ('classes',8,'roles','4','Mage - rngDPS'),
	 ('classes',2,'roles','11','Paladin - mleDPS + Tank + Heal'),
	 ('classes',3,'roles','4','Hunter - rngDPS'),
	 ('classes',4,'roles','2','Rogue - mleDPS'),
	 ('classes',5,'roles','5','Priest - rngDPS + Heal'),
	 ('classes',6,'roles','10','Death Knight - mleDPS + Tank');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('classes',7,'roles','7','Shaman - mleDPS + rngDPS + Heal'),
	 ('classes',8,'roles','4','Mage - rngDPS'),
	 ('classes',8,'roles','4','Mage - rngDPS'),
	 ('classes',8,'roles','4','Mage - rngDPS'),
	 ('currencies',103,'cap','10000','Arena Points - cap'),
	 ('currencies',104,'cap','75000','Honor Points - cap'),
	 ('currencies',1,'cuFlags','1073741824','Currency Token Test Token 1 - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('currencies',2,'cuFlags','1073741824','Currency Token Test Token 2 - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('currencies',4,'cuFlags','1073741824','Currency Token Test Token 3 - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('currencies',22,'cuFlags','1073741824','Birmingham Test Item 3 - set: CUSTOM_EXCLUDE_FOR_LISTVIEW');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('currencies',141,'cuFlags','1073741824','zzzOLDDaily Quest Faction Token - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('currencies',1,'category','3','Currency Token Test Token 1 - category: unused'),
	 ('currencies',2,'category','3','Currency Token Test Token 2 - category: unused'),
	 ('currencies',4,'category','3','Currency Token Test Token 3 - category: unused'),
	 ('currencies',22,'category','3','Birmingham Test Item 3 - category: unused'),
	 ('currencies',141,'category','3','zzzOLDDaily Quest Faction Token - category: unused'),
	 ('factions',68,'qmNpcIds','33555','Undercity - set Quartermaster'),
	 ('factions',47,'qmNpcIds','33310','Ironforge - set Quartermaster'),
	 ('factions',69,'qmNpcIds','33653','Darnassus - set Quartermaster'),
	 ('factions',72,'qmNpcIds','33307','Stormwind - set Quartermaster');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('factions',76,'qmNpcIds','33553','Orgrimmar - set Quartermaster'),
	 ('factions',81,'qmNpcIds','33556','Thunder Bluff - set Quartermaster'),
	 ('factions',922,'qmNpcIds','16528','Tranquillien - set Quartermaster'),
	 ('factions',930,'qmNpcIds','33657','Exodar - set Quartermaster'),
	 ('factions',932,'qmNpcIds','19321','The Aldor - set Quartermaster'),
	 ('factions',933,'qmNpcIds','20242 23007','The Consortium - set Quartermaster'),
	 ('factions',935,'qmNpcIds','21432','The Sha''tar - set Quartermaster'),
	 ('factions',941,'qmNpcIds','20241','The Mag''har - set Quartermaster'),
	 ('factions',942,'qmNpcIds','17904','Cenarion Expedition - set Quartermaster'),
	 ('factions',946,'qmNpcIds','17657','Honor Hold - set Quartermaster');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('factions',947,'qmNpcIds','17585','Thrallmar - set Quartermaster'),
	 ('factions',970,'qmNpcIds','18382','Sporeggar - set Quartermaster'),
	 ('factions',978,'qmNpcIds','20240','Kurenai - set Quartermaster'),
	 ('factions',989,'qmNpcIds','21643','Keepers of Time - set Quartermaster'),
	 ('factions',1011,'qmNpcIds','21655','Lower City - set Quartermaster'),
	 ('factions',1012,'qmNpcIds','23159','Ashtongue Deathsworn - set Quartermaster'),
	 ('factions',1037,'qmNpcIds','32773 32564','Alliance Vanguard - set Quartermaster'),
	 ('factions',1038,'qmNpcIds','23428','Ogri''la - set Quartermaster'),
	 ('factions',1052,'qmNpcIds','32774 32565','Horde Expedition - set Quartermaster'),
	 ('factions',1073,'qmNpcIds','31916 32763','The Kalu''ak - set Quartermaster');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('factions',1090,'qmNpcIds','32287','Kirin Tor - set Quartermaster'),
	 ('factions',1091,'qmNpcIds','32533','The Wyrmrest Accord - set Quartermaster'),
	 ('factions',1094,'qmNpcIds','34881','The Silver Covenant - set Quartermaster'),
	 ('factions',1105,'qmNpcIds','31910','The Oracles - set Quartermaster'),
	 ('factions',1106,'qmNpcIds','30431','Argent Crusade - set Quartermaster'),
	 ('factions',1119,'qmNpcIds','32540','The Sons of Hodir - set Quartermaster'),
	 ('factions',1124,'qmNpcIds','34772','The Sunreavers - set Quartermaster'),
	 ('factions',1156,'qmNpcIds','37687','The Ashen Verdict - set Quartermaster'),
	 ('factions',1082,'cuFlags','1073741824','REUSE - set: CUSTOM_EXCLUDE_FOR_LISTVIEW'),
	 ('factions',952,'cuFlags','1073741824','Test Faction 3 - set: CUSTOM_EXCLUDE_FOR_LISTVIEW');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('titles',138,'gender','1','Patron - male'),
	 ('sounds',15407,'cat','10','UR_Algalon_Summon03 - is not an item pickup'),
	 ('shapeshiftforms',1,'displayIdH','8571','Cat Form - spellshapeshiftform.dbc missing displayId'),
	 ('shapeshiftforms',15,'displayIdH','8571','Creature - Cat - spellshapeshiftform.dbc missing displayId'),
	 ('shapeshiftforms',5,'displayIdH','2289','Bear Form - spellshapeshiftform.dbc missing displayId'),
	 ('shapeshiftforms',8,'displayIdH','2289','Dire Bear Form - spellshapeshiftform.dbc missing displayId'),
	 ('shapeshiftforms',14,'displayIdH','2289','Creature - Bear - spellshapeshiftform.dbc missing displayId'),
	 ('shapeshiftforms',27,'displayIdH','21244','Flight Form, Epic - spellshapeshiftform.dbc missing displayId'),
	 ('shapeshiftforms',29,'displayIdH','20872','Flight Form - spellshapeshiftform.dbc missing displayId'),
	 ('races',1,'leader','29611','Human - King Varian Wrynn');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('races',1,'factionId','72','Human - Stormwind'),
	 ('races',1,'startAreaId','12','Human - Elwynn Forest'),
	 ('races',2,'leader','4949','Orc - Thrall'),
	 ('races',2,'factionId','76','Orc - Orgrimmar'),
	 ('races',2,'startAreaId','14','Orc - Durotar'),
	 ('races',3,'leader','2784','Dwarf - King Magni Bronzebeard'),
	 ('races',3,'factionId','47','Dwarf - Ironforge'),
	 ('races',3,'startAreaId','1','Dwarf - Dun Morogh'),
	 ('races',4,'leader','7999','Night Elf - Tyrande Whisperwind'),
	 ('races',4,'factionId','69','Night Elf - Darnassus');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('races',4,'startAreaId','141','Night Elf - Teldrassil'),
	 ('races',5,'leader','10181','Undead - Lady Sylvanas Windrunner'),
	 ('races',5,'factionId','68','Undead - Undercity'),
	 ('races',5,'startAreaId','85','Undead - Tirisfal Glades'),
	 ('races',6,'leader','3057','Tauren - Cairne Bloodhoof'),
	 ('races',6,'factionId','81','Tauren - Thunder Bluff'),
	 ('races',6,'startAreaId','215','Tauren - Mulgore'),
	 ('races',7,'leader','7937','Gnome - High Tinker Mekkatorque'),
	 ('races',7,'factionId','54','Gnome - Gnomeregan Exiles'),
	 ('races',7,'startAreaId','1','Gnome - Dun Morogh');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('races',8,'leader','10540','Troll - Vol''jin'),
	 ('races',8,'factionId','530','Troll - Darkspear Trolls'),
	 ('races',8,'startAreaId','14','Troll - Durotar'),
	 ('races',10,'leader','16802','Blood Elf - Lor''themar Theron'),
	 ('races',10,'factionId','911','Blood Elf - Silvermoon City'),
	 ('races',10,'startAreaId','3430','Blood Elf - Eversong Woods'),
	 ('races',11,'leader','17468','Draenei - Prophet Velen'),
	 ('races',11,'factionId','930','Draenei - Exodar'),
	 ('races',11,'startAreaId','3524','Draenei - Azuremyst Isle'),
	 ('holidays',62,'iconString','inv_misc_missilelarge_red','Fireworks Spectacular');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('holidays',141,'iconString','calendar_winterveilstart','Feast of Winter Veil'),
	 ('holidays',181,'iconString','calendar_noblegardenstart','Noblegarden'),
	 ('holidays',201,'iconString','calendar_childrensweekstart','Children''s Week'),
	 ('holidays',283,'iconString','inv_jewelry_necklace_21','Call to Arms: Alterac Valley'),
	 ('holidays',284,'iconString','inv_misc_rune_07','Call to Arms: Warsong Gulch'),
	 ('holidays',285,'iconString','inv_jewelry_amulet_07','Call to Arms: Arathi Basin'),
	 ('holidays',301,'iconString','calendar_fishingextravaganzastart','Stranglethorn Fishing Extravaganza'),
	 ('holidays',321,'iconString','calendar_harvestfestivalstart','Harvest Festival'),
	 ('holidays',324,'iconString','calendar_hallowsendstart','Hallow''s End'),
	 ('holidays',327,'iconString','calendar_lunarfestivalstart','Lunar Festival');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('holidays',335,'iconString','calendar_loveintheairstart','Love is in the Air'),
	 ('holidays',341,'iconString','calendar_midsummerstart','Midsummer Fire Festival'),
	 ('holidays',353,'iconString','spell_nature_eyeofthestorm','Call to Arms: Eye of the Storm'),
	 ('holidays',372,'iconString','calendar_brewfeststart','Brewfest'),
	 ('holidays',374,'iconString','calendar_darkmoonfaireelwynnstart','Darkmoon Faire'),
	 ('holidays',375,'iconString','calendar_darkmoonfairemulgorestart','Darkmoon Faire'),
	 ('holidays',376,'iconString','calendar_darkmoonfaireterokkarstart','Darkmoon Faire'),
	 ('holidays',398,'iconString','calendar_piratesdaystart','Pirates'' Day'),
	 ('holidays',400,'iconString','achievement_bg_winsoa','Call to Arms: Strand of the Ancients'),
	 ('holidays',404,'iconString','calendar_harvestfestivalstart','Pilgrim''s Bounty');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('holidays',406,'iconString','achievement_boss_lichking','Wrath of the Lich King Launch'),
	 ('holidays',409,'iconString','calendar_dayofthedeadstart','Day of the Dead'),
	 ('holidays',420,'iconString','achievement_bg_winwsg','Call to Arms: Isle of Conquest'),
	 ('holidays',423,'iconString','calendar_loveintheairstart','Love is in the Air'),
	 ('holidays',424,'iconString','calendar_fishingextravaganzastart','Kalu''ak Fishing Derby'),
	 ('holidays',141,'achievementCatOrId','156','Feast of Winter Veil - Category: Feast of Winter Veil'),
	 ('holidays',181,'achievementCatOrId','159','Noblegarden - Category: Noblegarden'),
	 ('holidays',201,'achievementCatOrId','163','Children''s Week - Category: Children''s Week'),
	 ('holidays',324,'achievementCatOrId','158','Hallow''s End - Category: Hallow''s End'),
	 ('holidays',327,'achievementCatOrId','160','Lunar Festival - Category: Lunar Festival');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('holidays',341,'achievementCatOrId','161','Midsummer Fire Festival - Category: Midsummer Fire Festival'),
	 ('holidays',372,'achievementCatOrId','162','Brewfest - Category: Brewfest'),
	 ('holidays',398,'achievementCatOrId','-3457','Pirates'' Day - Achievement: The Captain''s Booty'),
	 ('holidays',404,'achievementCatOrId','14981','Pilgrim''s Bounty - Category: Pilgrim''s Bounty'),
	 ('holidays',409,'achievementCatOrId','-3456','Day of the Dead - Achievement: Dead Man''s Party'),
	 ('holidays',423,'achievementCatOrId','187','Love is in the Air - Category: Love is in the Air'),
	 ('holidays',324,'bossCreature','23682','Hallow''s End - Headless Horseman'),
	 ('holidays',327,'bossCreature','15467','Lunar Festival - Omen'),
	 ('holidays',341,'bossCreature','25740','Midsummer Fire Festival - Ahune'),
	 ('holidays',372,'bossCreature','23872','Brewfest - Coren Direbrew');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('holidays',423,'bossCreature','36296','Love is in the Air - Apothecary Hummel'),
	 ('skillline',197,'professionMask','512','Tailoring'),
	 ('skillline',186,'professionMask','256','Mining'),
	 ('skillline',165,'specializations','10656 10658 10660','Leatherworking'),
	 ('skillline',165,'recipeSubClass','1','Leatherworking'),
	 ('skillline',165,'professionMask','128','Leatherworking'),
	 ('skillline',755,'recipeSubClass','10','Jewelcrafting'),
	 ('skillline',755,'professionMask','64','Jewelcrafting'),
	 ('skillline',129,'recipeSubClass','7','First Aid'),
	 ('skillline',129,'professionMask','32','First Aid');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('skillline',202,'specializations','20219 20222','Engineering'),
	 ('skillline',202,'recipeSubClass','3','Engineering'),
	 ('skillline',202,'professionMask','16','Engineering'),
	 ('skillline',333,'recipeSubClass','8','Enchanting'),
	 ('skillline',333,'professionMask','8','Enchanting'),
	 ('skillline',185,'recipeSubClass','5','Cooking'),
	 ('skillline',185,'professionMask','4','Cooking'),
	 ('skillline',164,'specializations','9788 9787 17041 17040 17039','Blacksmithing'),
	 ('skillline',164,'recipeSubClass','4','Blacksmithing'),
	 ('skillline',164,'professionMask','2','Blacksmithing');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('skillline',171,'specializations','28677 28675 28672','Alchemy'),
	 ('skillline',171,'recipeSubClass','6','Alchemy'),
	 ('skillline',171,'professionMask','1','Alchemy'),
	 ('skillline',393,'professionMask','0','Skinning'),
	 ('skillline',197,'recipeSubClass','2','Tailoring'),
	 ('skillline',197,'specializations','26798 26801 26797','Tailoring'),
	 ('skillline',356,'professionMask','1024','Fishing'),
	 ('skillline',356,'recipeSubClass','9','Fishing'),
	 ('skillline',182,'professionMask','2048','Herbalism'),
	 ('skillline',773,'professionMask','4096','Inscription');
INSERT INTO aowow_setup_custom_data (command,entry,field,value,comment) VALUES
	 ('skillline',773,'recipeSubClass','11','Inscription'),
	 ('skillline',785,'name_loc0','Pet - Wasp','Pet - Wasp'),
	 ('skillline',781,'name_loc2','Familier - diablosaure exotique','Pet - Exotic Devlisaur'),
	 ('skillline',758,'name_loc6','Mascota: Evento - Control remoto','Pet - Event - Remote Control'),
	 ('skillline',758,'name_loc3','Tier - Ereignis Ferngesteuert','Pet - Event - Remote Control'),
	 ('skillline',758,'categoryId','7','Pet - Event - Remote Control - bring in line with other pets'),
	 ('skillline',788,'categoryId','7','Pet - Exotic Spirit Beast - bring in line with other pets'),
	 ('item',33147,'class','9','Formula: Enchant Cloak - Subtlety - Class: Recipes'),
	 ('item',33147,'subClass','8','Formula: Enchant Cloak - Subtlety - Subclass: Enchanting');