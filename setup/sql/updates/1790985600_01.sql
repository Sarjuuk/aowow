-- A11: atomic owner-bound completion claims. Apply with the deployment account before revision 63.
CREATE TABLE IF NOT EXISTS `aowow_screenshot_uploads` (
  `uploadKey` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userIdOwner` int unsigned NOT NULL,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`uploadKey`),
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
