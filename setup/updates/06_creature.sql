ALTER TABLE `aowow_creature`
	ADD COLUMN `dmgMin` FLOAT UNSIGNED NOT NULL DEFAULT '0' AFTER `trainerRace`,
	ADD COLUMN `dmgMax` FLOAT UNSIGNED NOT NULL DEFAULT '0' AFTER `dmgMin`,
	ADD COLUMN `mleAtkPwrMin` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `dmgMax`,
	ADD COLUMN `mleAtkPwrMax` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `mleAtkPwrMin`,
	ADD COLUMN `rngAtkPwrMin` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `mleAtkPwrMax`,
	ADD COLUMN `rngAtkPwrMax` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `rngAtkPwrMin`
	ADD COLUMN `healthMin` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `aiName`
	ADD COLUMN `healthMax` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `healthMin`
	ADD COLUMN `manaMin` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `healthMax`
	ADD COLUMN `manaMax` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `manaMin`
	ADD COLUMN `armorMin` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER `manaMax`
	ADD COLUMN `armorMax` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER `armorMin`
;

-- merge creature_classlevelstats into ?_creature to be searchable
-- edit the table names to fit
-- for min stats
/*
UPDATE
	aowow.aowow_creature ac,
	world.creature_template ct,
	world.creature_classlevelstats cls
SET
   ac.healthMin    = (CASE ct.exp WHEN 0 THEN cls.basehp0 WHEN 1 THEN cls.basehp1 ELSE cls.basehp2 END) * ct.Health_mod,
   ac.manaMin      = cls.basemana  * ct.Mana_mod,
   ac.armorMin     = cls.basearmor * ct.Armor_mod,
   ac.rngAtkPwrMin = cls.rangedattackpower,
   ac.mleAtkPwrMin = cls.attackpower,
   ac.dmgMin       = (CASE ct.exp WHEN 0 THEN cls.damage_base WHEN 1 THEN cls.damage_exp1 ELSE cls.damage_exp2 END)
WHERE
   ac.id         = ct.entry AND
   ct.unit_class = cls.class AND
   ct.minlevel   = cls.level;

-- for max stats
UPDATE
	aowow.aowow_creature ac,
	world.creature_template ct,
	world.creature_classlevelstats cls
SET
   ac.healthMax    = (CASE ct.exp WHEN 0 THEN cls.basehp0 WHEN 1 THEN cls.basehp1 ELSE cls.basehp2 END) * ct.Health_mod,
   ac.manaMax      = cls.basemana  * ct.Mana_mod,
   ac.armorMax     = cls.basearmor * ct.Armor_mod,
   ac.rngAtkPwrMax = cls.rangedattackpower,
   ac.mleAtkPwrMax = cls.attackpower,
   ac.dmgMax       = (CASE ct.exp WHEN 0 THEN cls.damage_base WHEN 1 THEN cls.damage_exp1 ELSE cls.damage_exp2 END)
WHERE
   ac.id         = ct.entry AND
   ct.unit_class = cls.class AND
   ct.maxlevel   = cls.level;
*/
