CREATE TABLE `aowow_account_favorites` (
  `userId` INT(11) UNSIGNED NOT NULL,
  `type` SMALLINT(5) UNSIGNED NOT NULL,
  `typeId` MEDIUMINT(8) UNSIGNED NOT NULL,
  UNIQUE INDEX `userId_type_typeId` (`userId`, `type`, `typeId`),
  INDEX `userId` (`userId`),
  CONSTRAINT `FK_acc_favorites` FOREIGN KEY (`userId`) REFERENCES `aowow_account` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
