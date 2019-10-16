ALTER TABLE `aowow_comments`
    DROP INDEX `id`,
    ADD PRIMARY KEY (`id`),
    ADD INDEX `type_typeId` (`type`, `typeId`);
