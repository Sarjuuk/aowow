ALTER TABLE `aowow_user_ratings`
    DROP KEY `FK_acc_co_rate_user`,
    DROP FOREIGN KEY `FK_userId`,
    DROP PRIMARY KEY;

ALTER TABLE `aowow_user_ratings` MODIFY `userId` int unsigned NULL;

ALTER TABLE `aowow_user_ratings`
    ADD UNIQUE KEY (`type`,`entry`,`userId`),
    ADD KEY `FK_acc_co_rate_user` (`userId`),
    ADD CONSTRAINT FK_userId FOREIGN KEY (`userId`) REFERENCES aowow_account(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
