DROP TABLE IF EXISTS `aowow_declinedword`;
DROP TABLE IF EXISTS `aowow_declinedwordcases`;

CREATE TABLE `aowow_declinedword` (
    `id` SMALLINT(5) UNSIGNED NOT NULL,
    `word` VARCHAR(127) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `aowow_declinedwordcases` (
    `wordId` SMALLINT(5) UNSIGNED NOT NULL,
    `caseIdx` TINYINT(1) UNSIGNED NOT NULL,
    `word` VARCHAR(131) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`wordId`, `caseIdx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' declinedwords');
