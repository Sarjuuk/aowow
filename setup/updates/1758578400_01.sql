DELETE FROM `aowow_config` WHERE `key` IN ('rep_req_ext_links', 'gtag_measurement_id');
INSERT INTO `aowow_config` (`key`, `value`, `default`, `cat`, `flags`, `comment`) VALUES
    ('rep_req_ext_links', 150, 150, 5, 129, 'required reputation to link to external sites'),
    ('gtag_measurement_id', '', NULL, 6, 136, 'Enter your Google Tag measurement ID here to track site stats');

UPDATE `aowow_config` SET `key` = 'ua_measurement_key', `comment` = '[DEPRECATED ?] Enter your Google Universal Analytics key here to track site stats' WHERE `key` = 'analytics_user';
