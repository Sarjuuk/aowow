DELETE FROM `aowow_config` WHERE `key` = 'screenshot_min_size';
INSERT INTO `aowow_config` (`key`, `value`, `default`, `cat`, `flags`, `comment`) VALUES
    ('screenshot_min_size', 200, 200, 1, 1153, "minimum dimensions of uploaded screenshots in px (yes, it's square, no it cant go below 200)");
