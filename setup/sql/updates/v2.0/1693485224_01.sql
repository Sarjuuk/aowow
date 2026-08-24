DROP TABLE IF EXISTS `aowow_screeneffect_sounds`;
CREATE TABLE `aowow_screeneffect_sounds` (
    `id` SMALLINT(5) unsigned NOT NULL,
    `name` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL,
    `ambienceDay` SMALLINT(5) unsigned NOT NULL,
    `ambienceNight` SMALLINT(5) unsigned NOT NULL,
    `musicDay` SMALLINT(5) unsigned NOT NULL,
    `musicNight` SMALLINT(5) unsigned NOT NULL,
    KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' sounds');
