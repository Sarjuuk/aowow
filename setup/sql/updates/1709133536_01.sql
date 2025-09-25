DELETE FROM `aowow_setup_custom_data` WHERE `command` = 'classes' AND `field` = 'roles';
INSERT INTO `aowow_setup_custom_data` VALUES
    ('classes',1,'roles','10','Warrior - rngDPS'),
    ('classes',2,'roles','11','Paladin - mleDPS + Tank + Heal'),
    ('classes',3,'roles','4','Hunter - rngDPS'),
    ('classes',4,'roles','2','Rogue - mleDPS'),
    ('classes',5,'roles','5','Priest - rngDPS + Heal'),
    ('classes',6,'roles','10','Death Knight - mleDPS + Tank'),
    ('classes',7,'roles','7','Shaman - mleDPS + rngDPS + Heal'),
    ('classes',8,'roles','4','Mage - rngDPS'),
    ('classes',9,'roles','4','Warlock - rngDPS'),
    ('classes',11,'roles','15','Druid - mleDPS + Tank + Heal + rngDPS');

UPDATE `aowow_dbversion` SET `sql` = CONCAT(IFNULL(`sql`, ''), ' classes');
