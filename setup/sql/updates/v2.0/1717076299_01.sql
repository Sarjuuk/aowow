ALTER TABLE aowow_config
    MODIFY COLUMN `cat` tinyint unsigned NOT NULL DEFAULT 0,
    MODIFY COLUMN `flags` smallint unsigned NOT NULL DEFAULT 0,
    ADD COLUMN `default` varchar(255) DEFAULT NULL AFTER `value`;

INSERT IGNORE INTO aowow_config VALUES
    ('rep_req_border_unco', 5000,  5000,  5, 129, 'required reputation for uncommon quality avatar border'),
    ('rep_req_border_rare', 10000, 10000, 5, 129, 'required reputation for rare quality avatar border'),
    ('rep_req_border_epic', 15000, 15000, 5, 129, 'required reputation for epic quality avatar border'),
    ('rep_req_border_lege', 25000, 25000, 5, 129, 'required reputation for legendary quality avatar border');

UPDATE aowow_config SET `default` = 'UTF-8' WHERE `key` = 'default_charset';
UPDATE aowow_config SET `comment` = 'website title' WHERE `key` = 'name';
UPDATE aowow_config SET `comment` = 'feed title' WHERE `key` = 'name_short';
UPDATE aowow_config SET `comment` = 'another halfbaked javascript thing..' WHERE `key` = 'board_url';
UPDATE aowow_config SET `comment` = 'displayed sender for auth-mails, ect' WHERE `key` = 'contact_email';
UPDATE aowow_config SET `comment` = 'pretend, we belong to a battlegroup to satisfy profiler-related javascripts' WHERE `key` = 'battlegroup';
UPDATE aowow_config SET `comment` = 'points js to executable files', `flags` = `flags` | 768 WHERE `key` = 'site_host';
UPDATE aowow_config SET `comment` = 'points js to images & scripts', `flags` = `flags` | 768 WHERE `key` = 'static_host';
UPDATE aowow_config SET `comment` = 'some derelict code, probably unused' WHERE `key` = 'serialize_precision';
UPDATE aowow_config SET `comment` = 'enter your GA-user here to track site stats' WHERE `key` = 'analytics_user';
UPDATE aowow_config SET `comment` = 'if auth mode is not self; link to external account creation' WHERE `key` = 'acc_ext_create_url';
UPDATE aowow_config SET `comment` = 'if auth mode is not self; link to external account recovery' WHERE `key` = 'acc_ext_recover_url';
UPDATE aowow_config SET `comment` = 'php sessions are saved here. Leave empty to use php default directory.' WHERE `key` = 'session_cache_dir';
UPDATE aowow_config SET `comment` = 'max results for search', `default` = '500' WHERE `key` = 'sql_limit_search';
UPDATE aowow_config SET `comment` = 'max results for listviews', `default` = '300' WHERE `key` = 'sql_limit_default';
UPDATE aowow_config SET `comment` = 'max results for suggestions', `default` = '10' WHERE `key` = 'sql_limit_quicksearch';
UPDATE aowow_config SET `comment` = 'unlimited results (i wouldn\'t change that mate)', `default` = '0' WHERE `key` = 'sql_limit_none';
UPDATE aowow_config SET `comment` = 'time to live for RSS (in seconds)', `default` = '60' WHERE `key` = 'ttl_rss';
UPDATE aowow_config SET `comment` = 'disable cache, enable error_reporting - 0:None, 1:Error, 2:Warning, 3:Info', `default` = '0', `flags` = 145 WHERE `key` = 'debug';
UPDATE aowow_config SET `comment` = 'display brb gnomes and block access for non-staff', `default` = '0' WHERE `key` = 'maintenance';
UPDATE aowow_config SET `comment` = 'vote limit per day', `default` = '50' WHERE `key` = 'user_max_votes';
UPDATE aowow_config SET `comment` = 'enforce SSL, if auto-detect fails', `default` = '0' WHERE `key` = 'force_ssl';
UPDATE aowow_config SET `comment` = 'allowed locales - 0:English, 2:French, 3:German, 4:Chinese, 6:Spanish, 8:Russian', `default` = '0x15D' WHERE `key` = 'locales';
UPDATE aowow_config SET `comment` = 'minimum dimensions of uploaded screenshots in px (yes, it\'s square)', `default` = '200' WHERE `key` = 'screenshot_min_size';
UPDATE aowow_config SET `comment` = 'time to keep cache in seconds', `default` = '60 * 60 * 7' WHERE `key` = 'cache_decay';
UPDATE aowow_config SET `comment` = 'set cache method - 0:filecache, 1:memcached', `default` = '1' WHERE `key` = 'cache_mode';
UPDATE aowow_config SET `comment` = 'generated pages are saved here (requires CACHE_MODE: filecache)', `default` = 'cache/template' WHERE `key` = 'cache_dir';
UPDATE aowow_config SET `comment` = 'how long an account is closed after exceeding FAILED_AUTH_COUNT (in seconds)', `default` = '15 * 60' WHERE `key` = 'acc_failed_auth_block';
UPDATE aowow_config SET `comment` = 'how often invalid passwords are tolerated', `default` = '5' WHERE `key` = 'acc_failed_auth_count';
UPDATE aowow_config SET `comment` = 'allow/disallow account creation (requires AUTH_MODE: aowow)', `default` = '1' WHERE `key` = 'acc_allow_register';
UPDATE aowow_config SET `comment` = 'source to auth against - 0:AoWoW, 1:TC auth-table, 2:External script (config/extAuth.php)', `default` = '0', `flags`= `flags`| 256 WHERE `key` = 'acc_auth_mode';
UPDATE aowow_config SET `comment` = 'time in wich an unconfirmed account cannot be overwritten by new registrations', `default` = '604800' WHERE `key` = 'acc_create_save_decay';
UPDATE aowow_config SET `comment` = 'time to recover your account and new recovery requests are blocked', `default` = '300' WHERE `key` = 'acc_recovery_decay';
UPDATE aowow_config SET `comment` = 'non-permanent session times out in time() + X', `default` = '60 * 60' WHERE `key` = 'session_timeout_delay';
UPDATE aowow_config SET `comment` = 'lifetime of session data', `default` = '7 * 24 * 60 * 60' WHERE `key` = 'session.gc_maxlifetime';
UPDATE aowow_config SET `comment` = 'probability to remove session data on garbage collection', `default` = '0' WHERE `key` = 'session.gc_probability';
UPDATE aowow_config SET `comment` = 'probability to remove session data on garbage collection', `default` = '100' WHERE `key` = 'session.gc_divisor';
UPDATE aowow_config SET `comment` = 'required reputation to upvote comments', `default` = '125' WHERE `key` = 'rep_req_upvote';
UPDATE aowow_config SET `comment` = 'required reputation to downvote comments', `default` = '250' WHERE `key` = 'rep_req_downvote';
UPDATE aowow_config SET `comment` = 'required reputation to write a comment', `default` = '75' WHERE `key` = 'rep_req_comment';
UPDATE aowow_config SET `comment` = 'required reputation to write a reply', `default` = '75' WHERE `key` = 'rep_req_reply';
UPDATE aowow_config SET `comment` = 'required reputation for double vote effect', `default` = '2500' WHERE `key` = 'rep_req_supervote';
UPDATE aowow_config SET `comment` = 'gains more votes past this threshold', `default` = '2000' WHERE `key` = 'rep_req_votemore_base';
UPDATE aowow_config SET `comment` = 'activated an account', `default` = '100' WHERE `key` = 'rep_reward_register';
UPDATE aowow_config SET `comment` = 'comment received upvote', `default` = '5' WHERE `key` = 'rep_reward_upvoted';
UPDATE aowow_config SET `comment` = 'comment received downvote', `default` = '0' WHERE `key` = 'rep_reward_downvoted';
UPDATE aowow_config SET `comment` = 'filed an accepted report', `default` = '10' WHERE `key` = 'rep_reward_good_report';
UPDATE aowow_config SET `comment` = 'filed a rejected report', `default` = '0' WHERE `key` = 'rep_reward_bad_report';
UPDATE aowow_config SET `comment` = 'daily visit', `default` = '5' WHERE `key` = 'rep_reward_dailyvisit';
UPDATE aowow_config SET `comment` = 'moderator imposed a warning', `default` = '-50' WHERE `key` = 'rep_reward_user_warned';
UPDATE aowow_config SET `comment` = 'created a comment (not a reply)', `default` = '1' WHERE `key` = 'rep_reward_comment';
UPDATE aowow_config SET `comment` = 'required reputation for premium status through reputation', `default` = '25000' WHERE `key` = 'rep_req_premium';
UPDATE aowow_config SET `comment` = 'suggested / uploaded video / screenshot was approved', `default` = '10' WHERE `key` = 'rep_reward_upload';
UPDATE aowow_config SET `comment` = 'submitted an approved article/guide', `default` = '100' WHERE `key` = 'rep_reward_article';
UPDATE aowow_config SET `comment` = 'moderator revoked rights', `default` = '-200' WHERE `key` = 'rep_reward_user_suspended';
UPDATE aowow_config SET `comment` = 'required reputation per additional vote past threshold', `default` = '250' WHERE `key` = 'rep_req_votemore_add';
UPDATE aowow_config SET `comment` = 'parsing spell.dbc is quite intense', `default` = '1500M' WHERE `key` = 'memory_limit';
UPDATE aowow_config SET `comment` = 'enable/disable profiler feature', `default` = '0', `flags`= `flags`| 256 WHERE `key` = 'profiler_enable';
UPDATE aowow_config SET `comment` = 'min. delay between queue cycles (in ms)', `default` = '3000' WHERE `key` = 'profiler_queue_delay';
UPDATE aowow_config SET `comment` = 'how often the javascript asks for for updates, when queued (in ms)', `default` = '5000' WHERE `key` = 'profiler_resync_ping';
UPDATE aowow_config SET `comment` = 'how often a character can be refreshed (in sec)', `default` = '1 * 60 * 60' WHERE `key` = 'profiler_resync_delay';
