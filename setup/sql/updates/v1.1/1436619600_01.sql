ALTER TABLE `aowow_currencies`
    ADD COLUMN `cap` MEDIUMINT UNSIGNED NOT NULL AFTER `itemId`;

UPDATE `aowow_currencies` SET `cap` = 10000 WHERE `id` = 103;
UPDATE `aowow_currencies` SET `cap` = 75000 WHERE `id` = 104;
