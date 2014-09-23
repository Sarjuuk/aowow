ALTER TABLE `aowow_account`
	ALTER `curIP` DROP DEFAULT,
	ALTER `prevIP` DROP DEFAULT;
ALTER TABLE `aowow_account`
	CHANGE COLUMN `curIP` `curIP` VARCHAR(45) NOT NULL AFTER `consecutiveVisits`,
	CHANGE COLUMN `prevIP` `prevIP` VARCHAR(45) NOT NULL AFTER `curIP`;

ALTER TABLE `aowow_account_bannedips`
    ALTER `ip` DROP DEFAULT;
ALTER TABLE `aowow_account_bannedips`
    CHANGE COLUMN `ip` `ip` VARCHAR(45) NOT NULL FIRST;
