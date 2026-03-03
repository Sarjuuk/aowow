DROP TABLE IF EXISTS `aowow_quests_search`;
CREATE TABLE `aowow_quests_search` (
  `id` mediumint(8) unsigned NOT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `nName` varchar(100) DEFAULT NULL,
  `nObjectives` text DEFAULT NULL,
  `nDetails` text DEFAULT NULL,
  PRIMARY KEY (`id`, `locale`),
  FULLTEXT `idx_ft_na` (`nName`),
  FULLTEXT `idx_ft_na_ex` (`nName`, `nObjectives`, `nDetails`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_objects_search`;
CREATE TABLE `aowow_objects_search` (
  `id` mediumint(8) unsigned NOT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `nName` varchar(127) DEFAULT NULL,
  PRIMARY KEY (`id`, `locale`),
  FULLTEXT `idx_ft_na` (`nName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_items_search`;
CREATE TABLE `aowow_items_search` (
  `id` mediumint(8) unsigned NOT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `nName` varchar(127) DEFAULT NULL,
  `nDescription` varchar(255) DEFAULT NULL,
  `nEffects` text DEFAULT NULL,
  PRIMARY KEY (`id`, `locale`),
  FULLTEXT `idx_ft_na` (`nName`),
  FULLTEXT `idx_ft_description` (`nDescription`),
  FULLTEXT `idx_ft_effects` (`nEffects`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_creature_search`;
CREATE TABLE `aowow_creature_search` (
  `id` mediumint(8) unsigned NOT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `nName` varchar(100) DEFAULT NULL,
  `nSubname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`, `locale`),
  FULLTEXT `idx_ft_na` (`nName`),
  FULLTEXT `idx_ft_na_ex` (`nName`, `nSubname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_spell_search`;
CREATE TABLE `aowow_spell_search` (
  `id` mediumint(8) unsigned NOT NULL,
  `locale` tinyint(3) unsigned NOT NULL,
  `nName` varchar(185) DEFAULT NULL,
  `nDescription` text DEFAULT NULL,
  `nBuff` text DEFAULT NULL,
  PRIMARY KEY (`id`, `locale`),
  FULLTEXT `idx_ft_na` (`nName`),
  FULLTEXT `idx_ft_na_ex` (`nName`, `nDescription`, `nBuff`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
