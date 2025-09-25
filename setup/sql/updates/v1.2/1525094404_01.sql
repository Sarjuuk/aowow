UPDATE aowow_dbversion SET `sql` = CONCAT(IFNULL(`sql`, ''), ' quests'), `build` = CONCAT(IFNULL(`build`, ''), ' profiler');
