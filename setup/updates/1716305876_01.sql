SET foreign_key_checks = 0;

ALTER TABLE `aowow_account_weightscales`
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`);

SET foreign_key_checks = 1;
