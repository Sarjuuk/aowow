DROP TABLE IF EXISTS `aowow_mailtemplate`;
DROP TABLE IF EXISTS `aowow_mails`;

CREATE TABLE `aowow_mails` (
  `id` smallint(5) NOT NULL,
  `subject_loc0` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_loc2` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_loc3` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_loc4` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_loc6` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_loc8` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_loc0` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_loc2` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_loc3` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_loc4` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_loc6` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_loc8` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE aowow_dbversion SET `sql` = CONCAT(IFNULL(`sql`, ''), ' mails');
