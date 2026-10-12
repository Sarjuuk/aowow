DELETE FROM `aowow_setup_custom_data` WHERE `command` = 'spell' AND `entry` IN (16253, 16298);
INSERT INTO `aowow_setup_custom_data` VALUES
    ('spell',16253,'typeCat','0','set obsolete shaman talent as uncategorized'),
    ('spell',16298,'typeCat','0','set obsolete shaman talent as uncategorized');

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' spell');
