ALTER TABLE `aowow_dbversion`
  ADD COLUMN `sql` TEXT NOT NULL AFTER `part`,
  ADD COLUMN `build` TEXT NOT NULL AFTER `sql`;
