UPDATE `aowow_config` SET `comment` = 'default: 75 - required reputation to write a comment' WHERE `key` = 'rep_req_comment';
REPLACE INTO `aowow_config` (`key`, `value`, `cat`, `flags`, `comment`) VALUES ('rep_req_reply', '75', 5, 129, 'default: 75 - required reputation to write a reply');
