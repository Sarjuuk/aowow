--
-- Table structure for table `spell_learn_spell`
--

DROP TABLE IF EXISTS `spell_learn_spell`;
CREATE TABLE `spell_learn_spell` (
  `entry` smallint(5) unsigned NOT NULL DEFAULT '0',
  `SpellID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`entry`,`SpellID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Item System';

--
-- Data for table `spell_learn_spell`
--

INSERT INTO `spell_learn_spell` VALUES (53428,53341,1),(53428,53343,1),(17002,24867,0),(24866,24864,0),(33872,47179,0),(33873,47180,0),(33943,34090,1),(58984,21009,1);
