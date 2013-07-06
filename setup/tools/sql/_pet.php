<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class PetSetup extends PetList
{
    private static $setup = array(
        'CREATE TABLE `aowow_pet` (
            `id`  int(11) NOT NULL ,
            `category`  mediumint(8) NOT NULL ,
            `minLevel`  smallint(6) NOT NULL ,
            `maxLevel`  smallint(6) NOT NULL ,
            `foodMask`  int(11) NOT NULL ,
            `type`  tinyint(4) NOT NULL ,
            `exotic`  tinyint(4) NOT NULL ,
            `expansion`  tinyint(4) NOT NULL ,
            `name_loc0`  varchar(64) NOT NULL ,
            `name_loc2`  varchar(64) NOT NULL ,
            `name_loc3`  varchar(64) NOT NULL ,
            `name_loc6`  varchar(64) NOT NULL ,
            `name_loc8`  varchar(64) NOT NULL ,
            `iconString`  varchar(128) NOT NULL ,
            `skillLineId`  mediumint(9) NOT NULL ,
            `spellId1`  mediumint(9) NOT NULL ,
            `spellId2`  mediumint(9) NOT NULL ,
            `spellId3`  mediumint(9) NOT NULL ,
            `spellId4`  mediumint(9) NOT NULL ,
            `armor`  mediumint(9) NOT NULL ,
            `damage`  mediumint(9) NOT NULL ,
            `health`  mediumint(9) NOT NULL ,
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci ENGINE=MyISAM',

        'INSERT INTO aowow_pet SELECT
            f.id,
            categoryEnumId,
            min(ct.minlevel),
            max(ct.maxlevel),
            itemPetFoodMask,
            petTalentType,
            IF(ct.type_flags & 0x10000, 1, 0),              -- exotic
            0,                                              -- expansion (static data :/)
            nameEN, nameFR, nameDE, nameES, nameRU,
            SUBSTRING_INDEX(iconFile, '\\', -1),
            skillLine1,
            0, 0, 0, 0,                                     -- spells
            0, 0, 0                                         -- mods (from "Tamed Pet Passive (DND)")
        FROM
            dbc.creatureFamily f
        LEFT JOIN
            ?_creature ct ON
            f.id = ct.family
        JOIN
            world.creature c ON                             -- check if it is spawned (for min/max level)
            ct.id = c.id
        WHERE
            pettalentType <> -1 AND
            ct.type_flags & 0x1
        GROUP BY
            f.id;
        ',

        'UPDATE aowow_pet SET expansion = 1 WHERE id IN (30, 31, 32, 33, 34)',
        'UPDATE aowow_pet SET expansion = 2 WHERE id IN (37, 38, 39, 41, 42, 43, 44, 45, 46)'
    );

    private static $classicMods = array(                    // [Armor, Damage, Health] (see related "Tamed Pet Passive (DND)" spells per family. All values are set to +5% in wotlk)
         1 => [  5,   0,   0],                              // Wolf
         2 => [  0,  10,  -2],                              // Cat
         3 => [  0,   7,   0],                              // Spider
         4 => [  5,  -9,   8],                              // Bear
         5 => [  9, -10,   4],                              // Boar
         6 => [ 10,   0,  -5],                              // Crocolisk
         7 => [  5,   0,   0],                              // Carrion bird
         8 => [ 13,  -5,  -4],                              // Crab
         9 => [  0,   2,   4],                              // Gorilla
        11 => [  3,  10,  -5],                              // Raptor
        12 => [  0,   0,   5],                              // Tallstrider
        20 => [ 10,  -6,   0],                              // Scorpid
        21 => [ 13, -10,   0],                              // Turtle
        24 => [  0,   7,   0],                              // Bat
        25 => [  5,   0,   0],                              // Hyena
        26 => [  0,   7,   0],                              // Bord of Prey (Owl)
        27 => [  0,   7,   0],                              // Wind serpent
        30 => [  0,   0,   0],                              // Dragonhawk
        31 => [  5,  10,  -7],                              // Ravager
        32 => [  5,  -6,   0],                              // Warp stalker
        33 => [  0,   0,   0],                              // Sporebat
        34 => [-10,   3,  10],                              // Nether ray
        35 => [  0,   0,   0]                               // Serpent
    );

    public function __construct($params)
    {
        foreach ($this->setup as $query)
            DB::Aowow()->query($query);
    }

    function setupPetSpells()
    {
        $ids = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, skillLine1 FROM dbc.creatureFamily WHERE petTalentType <> -1');

        foreach ($ids as $family => $skillLine)
        {
            $rows = DB::Aowow()->select('SELECT MAX(s.id) as Id, IF(t.id, 1, 0) AS isTalent FROM dbc.spell s JOIN dbc.skillLineAbility sla ON sla.spellId = s.id LEFT JOIN dbc.talent t ON t.rank1 = s.id WHERE (s.attributes0 & 0x40) = 0 AND sla.skillLineId = ?d GROUP BY s.nameEN', $skillLine);
            $i = 1;
            foreach ($rows as $row)
            {
                if ($row['isTalent'])
                    continue;

                DB::Aowow()->query('UPDATE ?_pet SET spellId'.$i.' = ?d WHERE id = ?d', $row['Id'], $family);
                $i++;
            }
        }

        echo 'done';
    }

    function setupClassicMods()
    {
        foreach ($this->classicMods as $pet => $mods)
            DB::Aowow()->query('UPDATE ?_pet SET armor = ?d, damage = ?d, health = ?d WHERE id = ?d', $mods[0], $mods[1], $mods[2], $pet);
    }
}

?>
