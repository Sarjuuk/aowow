INSERT INTO aowow_config (`key`, `intValue`, `comment`) VALUES
    ('account_create_save_decay', 604800, 'default: 604800 - [1 week] time in wich an unconfirmed account cannot be overwritten by new registrations'),
    ('account_recovery_decay', 300, 'default: 300 - [5 min] time to recover your account and new recovery requets are blocked');