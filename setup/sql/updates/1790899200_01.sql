-- A09: shared, atomic password-work budgets. Apply with the deployment account before revision 61.
CREATE TABLE IF NOT EXISTS `aowow_account_password_budget` (
  `scope` varchar(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `subject` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `count` smallint unsigned NOT NULL DEFAULT 0,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`scope`, `subject`),
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
