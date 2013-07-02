<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class PetSetup extends PetList
{
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

    private static $addonInfo = array(                      // i could have sworn that was somewhere in dbc
        30 => [1, 0],
        31 => [1, 0],
        32 => [1, 0],
        33 => [1, 0],
        34 => [1, 0],
        37 => [2, 0],
        38 => [2, 1],
        39 => [2, 1],
        41 => [2, 1],
        42 => [2, 1],
        43 => [2, 1],
        44 => [2, 0],
        45 => [2, 1],
        46 => [2, 1]
    ),

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
        foreach (self::$classicMods as $pet => $mods)
            DB::Aowow()->query('UPDATE ?_pet SET armor = ?d, damage = ?d, health = ?d WHERE id = ?d', $mods[0], $mods[1], $mods[2], $pet);
    }

    function setupAddonInfo()
    {
        foreach (self::$addonInfo as $pet => $info)
            DB::Aowow()->query('UPDATE ?_pet SET expansion = ?d, exotic = ?d WHERE id = ?d', $info[0], $info[1], $pet);
    }
}

?>
