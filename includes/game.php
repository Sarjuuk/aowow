<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Game
{
    public static $resistanceFields         = array(
        null,           'resHoly',      'resFire',      'resNature',    'resFrost',     'resShadow',    'resArcane'
    );

    public static $rarityColorStings        = array(        // zero-indexed
        '9d9d9d',       'ffffff',       '1eff00',       '0070dd',       'a335ee',       'ff8000',       'e5cc80',       'e6cc80'
    );

    private static $combatRatingToItemMod    = array(        // zero-indexed idx:CR; val:Mod
        null,           12,             13,             14,             15,             16,             17,             18,             19,
        20,             21,             22,             23,             24,             25,             26,             27,             28,
        29,             30,             null,           null,           null,           37,             44
    );

    public static $lvlIndepRating           = array(        // rating doesn't scale with level
        ITEM_MOD_MANA,                  ITEM_MOD_HEALTH,                ITEM_MOD_ATTACK_POWER,          ITEM_MOD_MANA_REGENERATION,     ITEM_MOD_SPELL_POWER,
        ITEM_MOD_HEALTH_REGEN,          ITEM_MOD_SPELL_PENETRATION,     ITEM_MOD_BLOCK_VALUE
    );

    public static $questClasses             = array(        // taken from old aowow: 2 & 3 partially point to pointless mini-areas in front of dungeons
        -2 =>  [    0],
         0 =>  [    1,     3,     4,     8,    10,    11,    12,    25,   28,   33,   36,   38,   40,   41,   44,   45,   46,   47,   51,   85,  130,  139,  267,  279, 1497, 1519, 1537, 2257, 3430, 3433, 3487, 4080, 4298],
         1 =>  [   14,    15,    16,    17,   141,   148,   215,   331,  357,  361,  400,  405,  406,  440,  490,  493,  618, 1216, 1377, 1637, 1638, 1657, 3524, 3525, 3557],
/*todo*/ 2 =>  [  133,   206,   209,   491,   717,   718,   719,   722,  796,  978, 1196, 1337, 1417, 1581, 1583, 1584, 1941, 2017, 2057, 2100, 2366, 2367, 2437, 2557, 3477, 3562, 3713, 3714, 3715, 3716, 3717, 3789, 3790, 3791, 3792, 3845, 3846, 3847, 3849, 3905, 4095, 4100, 4120, 4196, 4228, 4264, 4272, 4375, 4415, 4494, 4723],
/*todo*/ 3 =>  [ 1977,  2159,  2562,  2677,  2717,  3428,  3429,  3456, 3606, 3805, 3836, 3840, 3842, 4273, 4500, 4722, 4812],
         4 =>  [ -372,  -263,  -262,  -261,  -162,  -161,  -141,   -82,  -81,  -61],
         5 =>  [ -373,  -371,  -324,  -304,  -264,  -201,  -182,  -181, -121, -101,  -24],
         6 =>  [  -25,  2597,  3277,  3358,  3820,  4384,  4710],
         7 =>  [-1010,  -368,  -367,  -365,  -344,  -241,    -1],
         8 =>  [ 3483,  3518,  3519,  3520,  3521,  3522,  3523,  3679, 3703],                                      // Skettis is no parent
         9 =>  [-1006, -1005, -1003, -1002, -1001,  -376,  -375,  -374, -370, -369, -366, -364, -284,  -41,  -22],  // 22: seasonal, 284: special => not in the actual menu
        10 =>  [   65,    66,    67,   210,   394,   495,  3537,  3711, 4024, 4197, 4395, 4742]                     // Coldara is no parent
    );

    /*  why:
        Because petSkills (and ranged weapon skills) are the only ones with more than two skillLines attached. Because Left Joining ?_spell with ?_skillLineability  causes more trouble than it has uses.
        Because this is more or less the only reaonable way to fit all that information into one database field, so..
        .. the indizes of this array are bits of skillLine2OrMask in ?_spell if skillLineId1 is negative
    */
    public static $skillLineMask            = array(        // idx => [familyId, skillLineId]
        -1 => array(                                        // Pets (Hunter)
            [ 1, 208],          [ 2, 209],          [ 3, 203],          [ 4, 210],          [ 5, 211],          [ 6, 212],          [ 7, 213],  // Wolf,       Cat,          Spider,       Bear,        Boar,      Crocolisk,    Carrion Bird
            [ 8, 214],          [ 9, 215],          [11, 217],          [12, 218],          [20, 236],          [21, 251],          [24, 653],  // Crab,       Gorilla,      Raptor,       Tallstrider, Scorpid,   Turtle,       Bat
            [25, 654],          [26, 655],          [27, 656],          [30, 763],          [31, 767],          [32, 766],          [33, 765],  // Hyena,      Bird of Prey, Wind Serpent, Dragonhawk,  Ravager,   Warp Stalker, Sporebat
            [34, 764],          [35, 768],          [37, 775],          [38, 780],          [39, 781],          [41, 783],          [42, 784],  // Nether Ray, Serpent,      Moth,         Chimaera,    Devilsaur, Silithid,     Worm
            [43, 786],          [44, 785],          [45, 787],          [46, 788]                                                               // Rhino,      Wasp,         Core Hound,   Spirit Beast
        ),
        -2 => array(                                        // Pets (Warlock)
            [15, 189],          [16, 204],          [17, 205],          [19, 207],          [23, 188],          [29, 761]                       // Felhunter,  Voidwalker,   Succubus,     Doomguard,   Imp,       Felguard
        ),
        -3 => array(                                        // Ranged Weapons
            [null, 45],         [null, 46],         [null, 226]                                                                                 // Bow,         Gun,         Crossbow
        )
    );

    public static $trainerTemplates         = array(        // TYPE => Id => templateList
        TYPE_CLASS => array(
              1 => [-200001, -200002],                      // Warrior
              2 => [-200003, -200004, -200020, -200021],    // Paladin
              3 => [-200013, -200014],                      // Hunter
              4 => [-200015, -200016],                      // Rogue
              5 => [-200011, -200012],                      // Priest
              6 => [-200019],                               // DK
              7 => [-200017, -200018],                      // Shaman (HighlevelAlly Id missing..?)
              8 => [-200007, -200008],                      // Mage
              9 => [-200009, -200010],                      // Warlock
             11 => [-200005, -200006]                       // Druid
        ),
        TYPE_SKILL => array(
            171 => [-201001, -201002, -201003],             // Alchemy
            164 => [-201004, -201005, -201006, -201007, -201008],// Blacksmithing
            333 => [-201009, -201010, -201011],             // Enchanting
            202 => [-201012, -201013, -201014, -201015, -201016, -201017], // Engineering
            182 => [-201018, -201019, -201020],             // Herbalism
            773 => [-201021, -201022, -201023],             // Inscription
            755 => [-201024, -201025, -201026],             // Jewelcrafting
            165 => [-201027, -201028, -201029, -201030, -201031, -201032], // Leatherworking
            186 => [-201033, -201034, -201035],             // Mining
            393 => [-201036, -201037, -201038],             // Skinning
            197 => [-201039, -201040, -201041, -201042],    // Tailoring
            356 => [-202001, -202002, -202003],             // Fishing
            185 => [-202004, -202005, -202006],             // Cooking
            129 => [-202007, -202008, -202009],             // First Aid
            762 => [-202010, -202011, -202012]              // Riding
        )
    );

    public static $sockets                  = array(        // jsStyle Strings
        'meta',                         'red',                          'yellow',                       'blue'
    );

    public static $itemMods                 = array(        // zero-indexed; "mastrtng": unused mastery; _[a-z] => taken mods..
        'dmg',              'mana',             'health',           'agi',              'str',              'int',              'spi',
        'sta',              'energy',           'rage',             'focus',            'runicpwr',         'defrtng',          'dodgertng',
        'parryrtng',        'blockrtng',        'mlehitrtng',       'rgdhitrtng',       'splhitrtng',       'mlecritstrkrtng',  'rgdcritstrkrtng',
        'splcritstrkrtng',  '_mlehitrtng',      '_rgdhitrtng',      '_splhitrtng',      '_mlecritstrkrtng', '_rgdcritstrkrtng', '_splcritstrkrtng',
        'mlehastertng',     'rgdhastertng',     'splhastertng',     'hitrtng',          'critstrkrtng',     '_hitrtng',         '_critstrkrtng',
        'resirtng',         'hastertng',        'exprtng',          'atkpwr',           'rgdatkpwr',        'feratkpwr',        'splheal',
        'spldmg',           'manargn',          'armorpenrtng',     'splpwr',           'healthrgn',        'splpen',           'block',                                          // ITEM_MOD_BLOCK_VALUE
        'mastrtng',         'armor',            'firres',           'frores',           'holres',           'shares',           'natres',
        'arcres',           'firsplpwr',        'frosplpwr',        'holsplpwr',        'shasplpwr',        'natsplpwr',        'arcsplpwr'
    );

    public static $class2SpellFamily        = array(
    //  null    Warrior Paladin Hunter  Rogue   Priest  DK      Shaman  Mage    Warlock null    Druid
        null,   4,      10,     9,      8,      6,      15,     11,     3,      5,      null,   7
    );

    public static function itemModByRatingMask($mask)
    {
        if (($mask & 0x1C000) == 0x1C000)                   // special case resilience
            return ITEM_MOD_RESILIENCE_RATING;

        if (($mask & 0x00E0) == 0x00E0)                     // special case hit rating
            return ITEM_MOD_HIT_RATING;

        for ($j = 0; $j < count(self::$combatRatingToItemMod); $j++)
        {
            if (!self::$combatRatingToItemMod[$j])
                continue;

            if (!($mask & (1 << $j)))
                continue;

            return self::$combatRatingToItemMod[$j];
        }

        return 0;
    }

    public static function sideByRaceMask($race)
    {
        // Any
        if (!$race || ($race & RACE_MASK_ALL) == RACE_MASK_ALL)
            return SIDE_BOTH;

        // Horde
        if ($race & RACE_MASK_HORDE && !($race & RACE_MASK_ALLIANCE))
            return SIDE_HORDE;

        // Alliance
        if ($race & RACE_MASK_ALLIANCE && !($race & RACE_MASK_HORDE))
            return SIDE_ALLIANCE;

        return SIDE_BOTH;
    }

    public static function getReputationLevelForPoints($pts)
    {
        if ($pts >= 41999)
            return REP_EXALTED;
        else if ($pts >= 20999)
            return REP_REVERED;
        else if ($pts >= 8999)
            return REP_HONORED;
        else if ($pts >= 2999)
            return REP_FRIENDLY;
        else if ($pts >= 0)
            return REP_NEUTRAL;
        else if ($pts >= -3000)
            return REP_UNFRIENDLY;
        else if ($pts >= -6000)
            return REP_HOSTILE;
        else
            return REP_HATED;
    }

    public static function getTaughtSpells(&$spell)
    {
        $extraIds = [-1];                                    // init with -1 to prevent empty-array errors
        $lookup   = [-1];
        switch (gettype($spell))
        {
            case 'object':
                if (get_class($spell) != 'SpellList')
                    return [];

                $lookup[] = $spell->id;
                foreach ($spell->canTeachSpell() as $idx)
                    $extraIds[] = $spell->getField('effect'.$idx.'TriggerSpell');

                break;
            case 'integer':
                $lookup[] = $spell;
                break;
            case 'array':
                $lookup = $spell;
                break;
            default:
                return [];
        }

        // note: omits required spell and chance in skill_discovery_template
        $data = array_merge(
            DB::World()->selectCol('SELECT spellId FROM spell_learn_spell WHERE entry IN (?a)', $lookup),
            DB::World()->selectCol('SELECT spellId FROM skill_discovery_template WHERE reqSpell IN (?a)', $lookup),
            $extraIds
        );

        // return list of integers, not strings
        array_walk($data, function (&$v, $k) {
            $v = intVal($v);
        });

        return $data;
    }

    public static function getPageText($ptId)
    {
        $pages = [];
        while ($ptId)
        {
            if ($row = DB::World()->selectRow('SELECT ptl.Text AS Text_loc?d, pt.* FROM page_text pt LEFT JOIN page_text_locale ptl ON pt.ID = ptl.ID AND locale = ? WHERE pt.ID = ?d', User::$localeId, User::$localeString, $ptId))
            {
                $ptId = $row['NextPageID'];
                $pages[] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
            }
            else
            {
                trigger_error('Referenced PageTextId #'.$ptId.' is not in DB', E_USER_WARNING);
                break;
            }
        }

        return $pages;
    }

}

?>
