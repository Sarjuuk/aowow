-- MySQL dump 10.13  Distrib 5.5.27, for Win32 (x86)
--
-- Host: localhost    Database: world
-- ------------------------------------------------------
-- Server version	5.5.27

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aowow_shapeshiftforms`
--

DROP TABLE IF EXISTS `aowow_shapeshiftforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aowow_shapeshiftforms` (
  `Id` bigint(20) NOT NULL,
  `name_loc0` text NOT NULL,
  `name_loc2` text NOT NULL,
  `name_loc3` text NOT NULL,
  `name_loc6` text NOT NULL,
  `name_loc8` text NOT NULL,
  `flags` bigint(20) NOT NULL,
  `creatureType` bigint(20) NOT NULL,
  `iconString` varchar(64) NOT NULL,
  `displayIdA` bigint(20) NOT NULL,
  `displayIdH` bigint(20) NOT NULL,
  `spellId1` bigint(20) NOT NULL,
  `spellId2` bigint(20) NOT NULL,
  `spellId3` bigint(20) NOT NULL,
  `spellId4` bigint(20) NOT NULL,
  `spellId5` bigint(20) NOT NULL,
  `spellId6` bigint(20) NOT NULL,
  `spellId7` bigint(20) NOT NULL,
  `spellId8` bigint(20) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aowow_shapeshiftforms`
--

LOCK TABLES `aowow_shapeshiftforms` WRITE;
/*!40000 ALTER TABLE `aowow_shapeshiftforms` DISABLE KEYS */;
INSERT INTO `aowow_shapeshiftforms` VALUES (1,'Cat Form','Forme de félin','Katzengestalt','Forma felina','Облик кошки',760,1,'Ability_Druid_CatFormAttack',892,8571,0,0,0,0,0,0,0,0),(2,'Tree of Life Form','Forme d\'arbre de vie','Baum des Lebens','Forma de árbol de vida','Облик Древа жизни',2257,4,'',864,0,0,0,0,0,0,0,0,0),(3,'Travel Form','Forme de voyage','Reisegestalt','Forma de viaje','Походный облик',216,1,'',918,0,0,0,0,0,0,0,0,0),(4,'Aquatic Form','Forme aquatique','Wassergestalt','Forma acuática','Водный облик',216,1,'',2428,0,0,0,0,0,0,0,0,0),(5,'Bear Form','Forme d’ours','Bärengestalt','Forma de oso','Облик медведя',728,1,'Ability_Druid_Swipe',2281,2289,0,0,0,0,0,0,0,0),(6,'Ambient','Ambiant','Umgebung','Ambiente','Фауна',0,0,'',328,0,0,0,0,0,0,0,0,0),(7,'Ghoul','Goule','Ghul','Necrófago','Вурдалак',9689,6,'',25527,0,6603,47468,47481,47482,47480,0,47484,47496),(8,'Dire Bear Form','Forme d’ours redoutable','Terrorbärengestalt','Forma de oso temible','Облик лютого медведя',728,1,'Ability_Druid_Swipe',2281,2289,0,0,0,0,0,0,0,0),(9,'Steve\'s Ghoul','Goule de Steve','Steves Ghul','Steve\'s Ghoul','Вурдалак Стива',128,-1,'',24994,0,0,0,0,0,0,0,0,0),(10,'Tharon\'ja Skeleton','Squelette de Tharon\'ja','Skelett von Tharon\'ja','Esqueleto Tharon\'ja','Скелет Тарон\'джа',1034,6,'',9784,0,50799,49613,49609,49617,0,0,0,0),(11,'Darkmoon - Test of Strength','Sombrelune - épreuve de force','Dunkelmond-Test der Stärke','Luna Negra - Prueba de fuerza','Ярмарка Новолуния - испытание силы',1226,-1,'',0,0,62022,62023,62024,62025,0,0,0,0),(12,'BLB Player','Joueur BLB','BLB Spieler','BLB Player','BLB Player',0,-1,'',0,0,44029,44752,44083,44010,44012,44817,0,0),(13,'Shadow Dance','Danse de l\'ombre','Schattentanz','Danza de las Sombras','Танец теней',1,-1,'',0,0,0,0,0,0,0,0,0,0),(14,'Creature - Bear','Créature - Ours','Kreatur - Bär','Criatura: oso','Существо: медведь',0,1,'',2281,0,0,0,0,0,0,0,0,0),(15,'Creature - Cat','Créature - Félin','Kreatur - Katze','Criatura: felino','Существо: кошка',0,1,'',892,0,0,0,0,0,0,0,0,0),(16,'Ghost Wolf','Loup fantôme','Geisterwolf','Lobo fantasmal','Призрачный волк',216,1,'',4613,0,0,0,0,0,0,0,0,0),(17,'Battle Stance','Posture de combat','Kampfhaltung','Actitud de batalla','Боевая стойка',7,-1,'',0,0,0,0,0,0,0,0,0,0),(18,'Defensive Stance','Posture défensive','Verteidigungshaltung','Actitud defensiva','Оборонительная стойка',7,-1,'',0,0,0,0,0,0,0,0,0,0),(19,'Berserker Stance','Posture berserker','Berserkerhaltung','Actitud rabiosa','Стойка берсерка',7,0,'',0,0,0,0,0,0,0,0,0,0),(20,'Test','Test','Test','Test','Тест',0,-1,'',0,0,6603,20577,0,0,0,0,0,0),(21,'Zombie','Zombie','Zombie','Zombi','Зомби',5150,6,'',26942,0,57596,51230,56560,43949,56528,0,0,0),(22,'Metamorphosis','Métamorphose','Metamorphose','Metamorfosis','Метаморфоза',216,3,'',25277,0,0,0,0,0,0,0,0,0),(25,'Undead','Mort-vivant','Untotes Wesen','No-muerto','Нежить',205,6,'',0,0,0,0,0,0,0,0,0,0),(26,'Frenzy','Frénésie','Raserei','Furia','Бешенство',216,1,'',17170,0,0,0,0,0,0,0,0,0),(27,'Flight Form, Epic','Forme de vol, épique','Epische Fluggestalt','Forma voladora, épica','Облик стремительной птицы',8,1,'',21243,21244,0,0,0,0,0,0,0,0),(28,'Shadowform','Forme d\'ombre','Schattengestalt','Forma de las Sombras','Облик Тьмы',9,-1,'',0,0,0,0,0,0,0,0,0,0),(29,'Flight Form','Forme de vol','Fluggestalt','Forma de vuelo','Облик птицы',8,1,'',20857,20872,0,0,0,0,0,0,0,0),(30,'Stealth','Camouflage','Verstohlenheit','Sigilo','Незаметность',1,0,'',0,0,0,0,0,0,0,0,0,0),(31,'Moonkin Form','Forme de sélénien','Mondkingestalt','Forma de lechúcico lunar','Облик лунного совуха',2753,-1,'',15374,15375,0,0,0,0,0,0,0,0),(32,'Spirit of Redemption','Esprit de rédemption','Geist der Erlösung','Espíritu redentor','Дух воздаяния',16640,-1,'',16031,0,0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `aowow_shapeshiftforms` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-06-28 21:48:34
