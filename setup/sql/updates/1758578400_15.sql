DELETE FROM `aowow_config` WHERE `key` = 'acc_max_avatar_uploads';
INSERT INTO `aowow_config` (`key`, `value`, `default`, `cat`, `flags`, `comment`) VALUES
    ('acc_max_avatar_uploads', 10, 10, 3, 129, 'premium users may upload this many avatars');
