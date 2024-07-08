SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `aowow_profiler_completion_quests`;
CREATE TABLE `aowow_profiler_completion_quests` (
`id` int unsigned NOT NULL,
`questId` mediumint unsigned NOT NULL,
KEY `id` (`id`),
CONSTRAINT `FK_pr_completion_quests` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_profiler_completion_skills`;
CREATE TABLE `aowow_profiler_completion_skills` (
`id` int unsigned NOT NULL,
`skillId` smallint unsigned NOT NULL,
`value` smallint unsigned DEFAULT NULL,
`max` smallint unsigned DEFAULT NULL,
KEY `id` (`id`),
KEY `typeId` (`skillId`),
CONSTRAINT `FK_pr_completion_skills` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_profiler_completion_reputation`;
CREATE TABLE `aowow_profiler_completion_reputation` (
`id` int unsigned NOT NULL,
`factionId` smallint unsigned NOT NULL,
`standing` mediumint DEFAULT NULL,
KEY `id` (`id`),
CONSTRAINT `FK_pr_completion_reputation` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_profiler_completion_titles`;
CREATE TABLE `aowow_profiler_completion_titles` (
`id` int unsigned NOT NULL,
`titleId` tinyint unsigned NOT NULL,
KEY `id` (`id`),
CONSTRAINT `FK_pr_completion_titles` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_profiler_completion_achievements`;
CREATE TABLE `aowow_profiler_completion_achievements` (
`id` int unsigned NOT NULL,
`achievementId` smallint unsigned NOT NULL,
`date` int unsigned DEFAULT NULL,
KEY `id` (`id`),
KEY `typeId` (`achievementId`),
CONSTRAINT `FK_pr_completion_achievements` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_profiler_completion_statistics`;
CREATE TABLE `aowow_profiler_completion_statistics` (
`id` int unsigned NOT NULL,
`achievementId` smallint NOT NULL,
`date` int unsigned DEFAULT NULL,
`counter` smallint unsigned DEFAULT NULL,  -- could be values of INT size, but surely not for bosskill counters, right? ... RIGHT!?
KEY `id` (`id`),
KEY `typeId` (`achievementId`),
CONSTRAINT `FK_pr_completion_statistics` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `aowow_profiler_completion_spells`;
CREATE TABLE `aowow_profiler_completion_spells` (
`id` int unsigned NOT NULL,
`spellId` mediumint unsigned NOT NULL,
KEY `id` (`id`),
CONSTRAINT `FK_pr_completion_spells` FOREIGN KEY (`id`) REFERENCES `aowow_profiler_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- force profiles to be updated
UPDATE `aowow_profiler_profiles` SET `lastUpdated` = 0;

DROP TABLE IF EXISTS `aowow_profiler_completion`;

SET FOREIGN_KEY_CHECKS=1;
