<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


class SimpleXML extends SimpleXMLElement
{
    public function addCData($str)
    {
        $node = dom_import_simplexml($this);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($str));

        return $this;
    }
}

class Util
{
    const FILE_ACCESS = 0755;

    public static $resistanceFields         = array(
        null,           'resHoly',      'resFire',      'resNature',    'resFrost',     'resShadow',    'resArcane'
    );

    public static $rarityColorStings        = array(        // zero-indexed
        '9d9d9d',       'ffffff',       '1eff00',       '0070dd',       'a335ee',       'ff8000',       'e5cc80',       'e6cc80'
    );

    public static $localeStrings            = array(        // zero-indexed
        'enus',         null,           'frfr',         'dede',         null,           null,           'eses',         null,           'ruru'
    );

    public static $subDomains               = array(
        'www',          null,           'fr',           'de',           null,           null,           'es',           null,           'ru'
    );

    public static $typeClasses              = array(
        null,               'CreatureList',     'GameObjectList',   'ItemList',         'ItemsetList',      'QuestList',        'SpellList',
        'ZoneList',         'FactionList',      'PetList',          'AchievementList',  'TitleList',        'WorldEventList',   'CharClassList',
        'CharRaceList',     'SkillList',        null,               'CurrencyList',
        TYPE_EMOTE       => 'EmoteList',
        TYPE_ENCHANTMENT => 'EnchantmentList'
    );

    public static $typeStrings              = array(        // zero-indexed
        null,           'npc',          'object',       'item',         'itemset',      'quest',        'spell',        'zone',         'faction',
        'pet',          'achievement',  'title',        'event',        'class',        'race',         'skill',        null,           'currency',
        TYPE_USER        => 'user',
        TYPE_EMOTE       => 'emote',
        TYPE_ENCHANTMENT => 'enchantment'
    );

    public static $combatRatingToItemMod    = array(        // zero-indexed idx:CR; val:Mod
        null,           12,             13,             14,             15,             16,             17,             18,             19,
        20,             21,             22,             23,             24,             25,             26,             27,             28,
        29,             30,             null,           null,           null,           37,             44
    );

    # todo (high): find a sensible way to write data here on setup
    public static $gtCombatRatings          = array(
        12 => 1.5,      13 => 13.8,     14 => 13.8,     15 => 5,        16 => 10,       17 => 10,       18 => 8,        19 => 14,       20 => 14,
        21 => 14,       22 => 10,       23 => 10,       24 => 8,        25 => 0,        26 => 0,        27 => 0,        28 => 10,       29 => 10,
        30 => 10,       31 => 10,       32 => 14,       33 => 0,        34 => 0,        35 => 28.75,    36 => 10,       37 => 2.5,      44 => 4.268292513760655
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

    public static $itemFilter               = array(
         20 => 'str',                21 => 'agi',                23 => 'int',                22 => 'sta',                24 => 'spi',                25 => 'arcres',             26 => 'firres',             27 => 'natres',
         28 => 'frores',             29 => 'shares',             30 => 'holres',             37 => 'mleatkpwr',          32 => 'dps',                35 => 'damagetype',         33 => 'dmgmin1',            34 => 'dmgmax1',
         36 => 'speed',              38 => 'rgdatkpwr',          39 => 'rgdhitrtng',         40 => 'rgdcritstrkrtng',    41 => 'armor',              44 => 'blockrtng',          43 => 'block',              42 => 'defrtng',
         45 => 'dodgertng',          46 => 'parryrtng',          48 => 'splhitrtng',         49 => 'splcritstrkrtng',    50 => 'splheal',            51 => 'spldmg',             52 => 'arcsplpwr',          53 => 'firsplpwr',
         54 => 'frosplpwr',          55 => 'holsplpwr',          56 => 'natsplpwr',          60 => 'healthrgn',          61 => 'manargn',            57 => 'shasplpwr',          77 => 'atkpwr',             78 => 'mlehastertng',
         79 => 'resirtng',           84 => 'mlecritstrkrtng',    94 => 'splpen',             95 => 'mlehitrtng',         96 => 'critstrkrtng',       97 => 'feratkpwr',         100 => 'nsockets',          101 => 'rgdhastertng',
        102 => 'splhastertng',      103 => 'hastertng',         114 => 'armorpenrtng',      115 => 'health',            116 => 'mana',              117 => 'exprtng',           119 => 'hitrtng',           123 => 'splpwr',
        134 => 'mledps',            135 => 'mledmgmin',         136 => 'mledmgmax',         137 => 'mlespeed',          138 => 'rgddps',            139 => 'rgddmgmin',         140 => 'rgddmgmax',         141 => 'rgdspeed'
    );

    public static $ssdMaskFields            = array(
        'shoulderMultiplier',           'trinketMultiplier',            'weaponMultiplier',             'primBudged',
        'rangedMultiplier',             'clothShoulderArmor',           'leatherShoulderArmor',         'mailShoulderArmor',
        'plateShoulderArmor',           'weaponDPS1H',                  'weaponDPS2H',                  'casterDPS1H',
        'casterDPS2H',                  'rangedDPS',                    'wandDPS',                      'spellPower',
        null,                           null,                           'tertBudged',                   'clothCloakArmor',
        'clothChestArmor',              'leatherChestArmor',            'mailChestArmor',               'plateChestArmor'
    );

    public static $weightScales             = array(
        'agi',             'int',             'sta',          'spi',          'str',       'health',          'mana',         'healthrgn', 'manargn',
        'armor',           'blockrtng',       'block',        'defrtng',      'dodgertng', 'parryrtng',       'resirtng',
        'atkpwr',          'feratkpwr',       'armorpenrtng', 'critstrkrtng', 'exprtng',   'hastertng',       'hitrtng',      'splpen',
        'splpwr',          'arcsplpwr',       'firsplpwr',    'frosplpwr',    'holsplpwr', 'natsplpwr',       'shasplpwr',
        'dmg',             'mledps',          'rgddps',       'mledmgmin',    'rgddmgmin', 'mledmgmax',       'rgddmgmax',    'mlespeed',  'rgdspeed',
        'arcres',          'firres',          'frores',       'holres',       'natres',    'shares',
        'mleatkpwr',       'mlecritstrkrtng', 'mlehastertng', 'mlehitrtng',   'rgdatkpwr', 'rgdcritstrkrtng', 'rgdhastertng', 'rgdhitrtng',
        'splcritstrkrtng', 'splhastertng',    'splhitrtng',   'spldmg',       'splheal',
        'nsockets'
    );

    public static $dateFormatInternal       = "Y/m/d H:i:s";

    public static $changeLevelString        = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_staticTooltipLevelClick(this, null, 0)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><!--lvl-->%s</a>';

    public static $setRatingLevelString     = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_setRatingLevel(this, %s, %s, %s)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">%s</a>';

    public static $filterResultString       = '$$WH.sprintf(LANG.lvnote_filterresults, \'%s\')';
    public static $tryFilteringString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryNarrowingString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_trynarrowing';
    public static $setCriteriaString        = "fi_setCriteria(%s, %s, %s);\n";

    public static $dfnString                = '<dfn title="%s" class="w">%s</dfn>';

    public static $mapSelectorString        = '<a href="javascript:;" onclick="myMapper.update({zone: %d}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">%s</a>&nbsp;(%d)';

    public static $expansionString          = array(        // 3 & 4 unused .. obviously
        null,           'bc',           'wotlk',            'cata',                'mop'
    );

    public static $class2SpellFamily        = array(
    //  null    Warrior Paladin Hunter  Rogue   Priest  DK      Shaman  Mage    Warlock null    Druid
        null,   4,      10,     9,      8,      6,      15,     11,     3,      5,      null,   7
    );

    // todo: translate and move to Lang
    public static $spellEffectStrings       = array(
          0 => 'None',
          1 => 'Instakill',
          2 => 'School Damage',
          3 => 'Dummy',
          4 => 'Portal Teleport',
          5 => 'Teleport Units',
          6 => 'Apply Aura',
          7 => 'Environmental Damage',
          8 => 'Power Drain',
          9 => 'Health Leech',
         10 => 'Heal',
         11 => 'Bind',
         12 => 'Portal',
         13 => 'Ritual Base',
         14 => 'Ritual Specialize',
         15 => 'Ritual Activate Portal',
         16 => 'Quest Complete',
         17 => 'Weapon Damage NoSchool',
         18 => 'Resurrect',
         19 => 'Add Extra Attacks',
         20 => 'Dodge',
         21 => 'Evade',
         22 => 'Parry',
         23 => 'Block',
         24 => 'Create Item',
         25 => 'Can Use Weapon',
         26 => 'Defense',
         27 => 'Persistent Area Aura',
         28 => 'Summon',
         29 => 'Leap',
         30 => 'Energize',
         31 => 'Weapon Damage Percent',
         32 => 'Trigger Missile',
         33 => 'Open Lock',
         34 => 'Summon Change Item',
         35 => 'Apply Area Aura Party',
         36 => 'Learn Spell',
         37 => 'Spell Defense',
         38 => 'Dispel',
         39 => 'Language',
         40 => 'Dual Wield',
         41 => 'Jump',
         42 => 'Jump Dest',
         43 => 'Teleport Units Face Caster',
         44 => 'Skill Step',
         45 => 'Add Honor',
         46 => 'Spawn',
         47 => 'Trade Skill',
         48 => 'Stealth',
         49 => 'Detect',
         50 => 'Trans Door',
         51 => 'Force Critical Hit',
         52 => 'Guarantee Hit',
         53 => 'Enchant Item Permanent',
         54 => 'Enchant Item Temporary',
         55 => 'Tame Creature',
         56 => 'Summon Pet',
         57 => 'Learn Pet Spell',
         58 => 'Weapon Damage Flat',
         59 => 'Create Random Item',
         60 => 'Proficiency',
         61 => 'Send Event',
         62 => 'Power Burn',
         63 => 'Threat',
         64 => 'Trigger Spell',
         65 => 'Apply Area Aura Raid',
         66 => 'Create Mana Gem',
         67 => 'Heal Max Health',
         68 => 'Interrupt Cast',
         69 => 'Distract',
         70 => 'Pull',
         71 => 'Pickpocket',
         72 => 'Add Farsight',
         73 => 'Untrain Talents',
         74 => 'Apply Glyph',
         75 => 'Heal Mechanical',
         76 => 'Summon Object Wild',
         77 => 'Script Effect',
         78 => 'Attack',
         79 => 'Sanctuary',
         80 => 'Add Combo Points',
         81 => 'Create House',
         82 => 'Bind Sight',
         83 => 'Duel',
         84 => 'Stuck',
         85 => 'Summon Player',
         86 => 'Activate Object',
         87 => 'WMO Damage',
         88 => 'WMO Repair',
         89 => 'WMO Change',
         90 => 'Kill Credit',
         91 => 'Threat All',
         92 => 'Enchant Held Item',
         93 => 'Force Deselect',
         94 => 'Self Resurrect',
         95 => 'Skinning',
         96 => 'Charge',
         97 => 'Cast Button',
         98 => 'Knock Back',
         99 => 'Disenchant',
        100 => 'Inebriate',
        101 => 'Feed Pet',
        102 => 'Dismiss Pet',
        103 => 'Reputation',
        104 => 'Summon Object Slot1',
        105 => 'Summon Object Slot2',
        106 => 'Summon Object Slot3',
        107 => 'Summon Object Slot4',
        108 => 'Dispel Mechanic',
        109 => 'Summon Dead Pet',
        110 => 'Destroy All Totems',
        111 => 'Durability Damage',
        112 => 'Summon Demon',
        113 => 'Resurrect Flat',
        114 => 'Attack Me',
        115 => 'Durability Damage Percent',
        116 => 'Skin Player Corpse',
        117 => 'Spirit Heal',
        118 => 'Skill',
        119 => 'Apply Area Aura Pet',
        120 => 'Teleport Graveyard',
        121 => 'Weapon Damage Normalized',
        122 => 'Unknown Effect',
        123 => 'Send Taxi',
        124 => 'Pull Towards',
        125 => 'Modify Threat Percent',
        126 => 'Steal Beneficial Buff',
        127 => 'Prospecting',
        128 => 'Apply Area Aura Friend',
        129 => 'Apply Area Aura Enemy',
        130 => 'Redirect Threat',
        131 => 'Unknown Effect',
        132 => 'Play Music',
        133 => 'Unlearn Specialization',
        134 => 'Kill Credit2',
        135 => 'Call Pet',
        136 => 'Heal Percent',
        137 => 'Energize Percent',
        138 => 'Leap Back',
        139 => 'Clear Quest',
        140 => 'Force Cast',
        141 => 'Force Cast With Value',
        142 => 'Trigger Spell With Value',
        143 => 'Apply Area Aura Owner',
        144 => 'Knock Back Dest',
        145 => 'Pull Towards Dest',
        146 => 'Activate Rune',
        147 => 'Quest Fail',
        148 => 'Unknown Effect',
        149 => 'Charge Dest',
        150 => 'Quest Start',
        151 => 'Trigger Spell 2',
        152 => 'Unknown Effect',
        153 => 'Create Tamed Pet',
        154 => 'Discover Taxi',
        155 => 'Dual Wield 2H Weapons',
        156 => 'Enchant Item Prismatic',
        157 => 'Create Item 2',
        158 => 'Milling',
        159 => 'Allow Rename Pet',
        160 => 'Unknown Effect',
        161 => 'Talent Spec Count',
        162 => 'Talent Spec Select',
        163 => 'Unknown Effect',
        164 => 'Remove Aura'
    );

    public static $spellAuraStrings         = array(
        0 => 'None',
        1 => 'Bind Sight',
        2 => 'Mod Possess',
        3 => 'Periodic Damage',
        4 => 'Dummy',
        5 => 'Mod Confuse',
        6 => 'Mod Charm',
        7 => 'Mod Fear',
        8 => 'Periodic Heal',
        9 => 'Mod Attack Speed',
        10 => 'Mod Threat',
        11 => 'Taunt',
        12 => 'Stun',
        13 => 'Mod Damage Done Flat',
        14 => 'Mod Damage Taken Flat',
        15 => 'Damage Shield',
        16 => 'Mod Stealth',
        17 => 'Mod Stealth Detection',
        18 => 'Mod Invisibility',
        19 => 'Mod Invisibility Detection',
        20 => 'Mod Health Percent',
        21 => 'Mod Power Percent',
        22 => 'Mod Resistance Flat',
        23 => 'Periodic Trigger Spell',
        24 => 'Periodic Energize',
        25 => 'Pacify',
        26 => 'Root',
        27 => 'Silence',
        28 => 'Reflect Spells',
        29 => 'Mod Stat Flat',
        30 => 'Mod Skill',
        31 => 'Mod Increase Speed',
        32 => 'Mod Increase Mounted Speed',
        33 => 'Mod Decrease Speed',
        34 => 'Mod Increase Health',
        35 => 'Mod Increase Power',
        36 => 'Shapeshift',
        37 => 'Spell Effect Immunity',
        38 => 'Spell Aura Immunity',
        39 => 'School Immunity',
        40 => 'Damage Immunity',
        41 => 'Dispel Immunity',
        42 => 'Proc Trigger Spell',
        43 => 'Proc Trigger Damage',
        44 => 'Track Creatures',
        45 => 'Track Resources',
        46 => 'Mod Parry Skill',
        47 => 'Mod Parry Percent',
        48 => 'Unknown Aura',
        49 => 'Mod Dodge Percent',
        50 => 'Mod Critical Healing Amount',
        51 => 'Mod Block Percent',
        52 => 'Mod Physical Crit Percent',
        53 => 'Periodic Health Leech',
        54 => 'Mod Hit Chance',
        55 => 'Mod Spell Hit Chance',
        56 => 'Transform',
        57 => 'Mod Spell Crit Chance',
        58 => 'Mod Increase Swim Speed',
        59 => 'Mod Damage Done Versus Creature',
        60 => 'Pacify Silence',
        61 => 'Mod Scale',
        62 => 'Periodic Health Funnel',
        63 => 'Periodic Mana Funnel',
        64 => 'Periodic Mana Leech',
        65 => 'Mod Casting Speed (not stacking)',
        66 => 'Feign Death',
        67 => 'Disarm',
        68 => 'Stalked',
        69 => 'School Absorb',
        70 => 'Extra Attacks',
        71 => 'Mod Spell Crit Chance School',
        72 => 'Mod Power Cost School Percent',
        73 => 'Mod Power Cost School Flat',
        74 => 'Reflect Spells School',
        75 => 'Language',
        76 => 'Far Sight',
        77 => 'Mechanic Immunity',
        78 => 'Mounted',
        79 => 'Mod Damage Done Percent',
        80 => 'Mod Stat Percent',
        81 => 'Split Damage Percent',
        82 => 'Water Breathing',
        83 => 'Mod Base Resistance Flat',
        84 => 'Mod Health Regeneration',
        85 => 'Mod Power Regeneration',
        86 => 'Channel Death Item',
        87 => 'Mod Damage Taken Percent',
        88 => 'Mod Health Regeneration Percent',
        89 => 'Periodic Damage Percent',
        90 => 'Mod Resist Chance',
        91 => 'Mod Detect Range',
        92 => 'Prevent Fleeing',
        93 => 'Unattackable',
        94 => 'Interrupt Regeneration',
        95 => 'Ghost',
        96 => 'Spell Magnet',
        97 => 'Mana Shield',
        98 => 'Mod Skill Value',
        99 => 'Mod Attack Power',
        100 => 'Auras Visible',
        101 => 'Mod Resistance Percent',
        102 => 'Mod Melee Attack Power Versus',
        103 => 'Mod Total Threat',
        104 => 'Water Walk',
        105 => 'Feather Fall',
        106 => 'Hover',
        107 => 'Add Flat Modifier',
        108 => 'Add Percent Modifier',
        109 => 'Add Target Trigger',
        110 => 'Mod Power Regeneration Percent',
        111 => 'Add Caster Hit Trigger',
        112 => 'Override Class Scripts',
        113 => 'Mod Ranged Damage Taken Flat',
        114 => 'Mod Ranged Damage Taken Percent',
        115 => 'Mod Healing',
        116 => 'Mod Regeneration During Combat',
        117 => 'Mod Mechanic Resistance',
        118 => 'Mod Healing Taken Percent',
        119 => 'Share Pet Tracking',
        120 => 'Untrackable',
        121 => 'Empathy',
        122 => 'Mod Offhand Damage Percent',
        123 => 'Mod Target Resistance',
        124 => 'Mod Ranged Attack Power',
        125 => 'Mod Melee Damage Taken Flat',
        126 => 'Mod Melee Damage Taken Percent',
        127 => 'Ranged Attack Power Attacker Bonus',
        128 => 'Possess Pet',
        129 => 'Mod Speed Always',
        130 => 'Mod Mounted Speed Always',
        131 => 'Mod Ranged Attack Power Versus',
        132 => 'Mod Increase Energy Percent',
        133 => 'Mod Increase Health Percent',
        134 => 'Mod Mana Regeneration Interrupt',
        135 => 'Mod Healing Done Flat',
        136 => 'Mod Healing Done Percent',
        137 => 'Mod Total Stat Percentage',
        138 => 'Mod Melee Haste',
        139 => 'Force Reaction',
        140 => 'Mod Ranged Haste',
        141 => 'Mod Ranged Ammo Haste',
        142 => 'Mod Base Resistance Percent',
        143 => 'Mod Resistance Exclusive',
        144 => 'Safe Fall',
        145 => 'Mod Pet Talent Points',
        146 => 'Allow Tame Pet Type',
        147 => 'Mechanic Immunity Mask',
        148 => 'Retain Combo Points',
        149 => 'Reduce Pushback',
        150 => 'Mod Shield Blockvalue Percent',
        151 => 'Track Stealthed',
        152 => 'Mod Detected Range',
        153 => 'Split Damage Flat',
        154 => 'Mod Stealth Level',
        155 => 'Mod Water Breathing',
        156 => 'Mod Reputation Gain',
        157 => 'Pet Damage Multi',
        158 => 'Mod Shield Blockvalue',
        159 => 'No PvP Credit',
        160 => 'Mod AoE Avoidance',
        161 => 'Mod Health Regeneration In Combat',
        162 => 'Power Burn Mana',
        163 => 'Mod Crit Damage Bonus',
        164 => 'Unknown Aura',
        165 => 'Melee Attack Power Attacker Bonus',
        166 => 'Mod Attack Power Percent',
        167 => 'Mod Ranged Attack Power Percent',
        168 => 'Mod Damage Done Versus',
        169 => 'Mod Crit Percent Versus',
        170 => 'Change Model',
        171 => 'Mod Speed (not stacking)',
        172 => 'Mod Mounted Speed (not stacking)',
        173 => 'Unknown Aura',
        174 => 'Mod Spell Damage Of Stat Percent',
        175 => 'Mod Spell Healing Of Stat Percent',
        176 => 'Spirit Of Redemption',
        177 => 'AoE Charm',
        178 => 'Mod Debuff Resistance',
        179 => 'Mod Attacker Spell Crit Chance',
        180 => 'Mod Spell Damage Versus',
        181 => 'Unknown Aura',
        182 => 'Mod Resistance Of Stat Percent',
        183 => 'Mod Critical Threat',
        184 => 'Mod Attacker Melee Hit Chance',
        185 => 'Mod Attacker Ranged Hit Chance',
        186 => 'Mod Attacker Spell Hit Chance',
        187 => 'Mod Attacker Melee Crit Chance',
        188 => 'Mod Attacker Ranged Crit Chance',
        189 => 'Mod Rating',
        190 => 'Mod Faction Reputation Gain',
        191 => 'Use Normal Movement Speed',
        192 => 'Mod Melee Ranged Haste',
        193 => 'Mod Haste',
        194 => 'Mod Target Absorb School',
        195 => 'Mod Target Ability Absorb School',
        196 => 'Mod Cooldown',
        197 => 'Mod Attacker Spell And Weapon Crit Chance',
        198 => 'Unknown Aura',
        199 => 'Mod Increases Spell Percent to Hit',
        200 => 'Mod XP Percent',
        201 => 'Fly',
        202 => 'Ignore Combat Result',
        203 => 'Mod Attacker Melee Crit Damage',
        204 => 'Mod Attacker Ranged Crit Damage',
        205 => 'Mod School Crit Damage Taken',
        206 => 'Mod Increase Vehicle Flight Speed',
        207 => 'Mod Increase Mounted Flight Speed',
        208 => 'Mod Increase Flight Speed',
        209 => 'Mod Mounted Flight Speed Always',
        210 => 'Mod Vehicle Speed Always',
        211 => 'Mod Flight Speed (not stacking)',
        212 => 'Mod Ranged Attack Power Of Stat Percent',
        213 => 'Mod Rage from Damage Dealt',
        214 => 'Tamed Pet Passive',
        215 => 'Arena Preparation',
        216 => 'Haste Spells',
        217 => 'Killing Spree',
        218 => 'Haste Ranged',
        219 => 'Mod Mana Regeneration from Stat',
        220 => 'Mod Rating from Stat',
        221 => 'Ignore Threat',
        222 => 'Unknown Aura',
        223 => 'Raid Proc from Charge',
        224 => 'Unknown Aura',
        225 => 'Raid Proc from Charge With Value',
        226 => 'Periodic Dummy',
        227 => 'Periodic Trigger Spell With Value',
        228 => 'Detect Stealth',
        229 => 'Mod AoE Damage Avoidance',
        230 => 'Mod Increase Health',
        231 => 'Proc Trigger Spell With Value',
        232 => 'Mod Mechanic Duration',
        233 => 'Mod Display Model',
        234 => 'Mod Mechanic Duration (not stacking)',
        235 => 'Mod Dispel Resist',
        236 => 'Control Vehicle',
        237 => 'Mod Spell Damage Of Attack Power',
        238 => 'Mod Spell Healing Of Attack Power',
        239 => 'Mod Scale 2',
        240 => 'Mod Expertise',
        241 => 'Force Move Forward',
        242 => 'Mod Spell Damage from Healing',
        243 => 'Mod Faction',
        244 => 'Comprehend Language',
        245 => 'Mod Aura Duration By Dispel',
        246 => 'Mod Aura Duration By Dispel (not stacking)',
        247 => 'Clone Caster',
        248 => 'Mod Combat Result Chance',
        249 => 'Convert Rune',
        250 => 'Mod Increase Health 2',
        251 => 'Mod Enemy Dodge',
        252 => 'Mod Speed Slow All',
        253 => 'Mod Block Crit Chance',
        254 => 'Mod Disarm Offhand',
        255 => 'Mod Mechanic Damage Taken Percent',
        256 => 'No Reagent Use',
        257 => 'Mod Target Resist By Spell Class',
        258 => 'Mod Spell Visual',
        259 => 'Mod HoT Percent',
        260 => 'Screen Effect',
        261 => 'Phase',
        262 => 'Ability Ignore Aurastate',
        263 => 'Allow Only Ability',
        264 => 'Unknown Aura',
        265 => 'Unknown Aura',
        266 => 'Unknown Aura',
        267 => 'Mod Immune Aura Apply School',
        268 => 'Mod Attack Power Of Stat Percent',
        269 => 'Mod Ignore Target Resist',
        270 => 'Mod Ability Ignore Target Resist',
        271 => 'Mod Damage Taken Percent From Caster',
        272 => 'Ignore Melee Reset',
        273 => 'X Ray',
        274 => 'Ability Consume No Ammo',
        275 => 'Mod Ignore Shapeshift',
        276 => 'Mod Mechanic Damage Done Percent',
        277 => 'Mod Max Affected Targets',
        278 => 'Mod Disarm Ranged',
        279 => 'Initialize Images',
        280 => 'Mod Armor Penetration Percent',
        281 => 'Mod Honor Gain Percent',
        282 => 'Mod Base Health Percent',
        283 => 'Mod Healing Received',
        284 => 'Linked',
        285 => 'Mod Attack Power Of Armor',
        286 => 'Ability Periodic Crit',
        287 => 'Deflect Spells',
        288 => 'Ignore Hit Direction',
        289 => 'Unknown Aura',
        290 => 'Mod Crit Percent',
        291 => 'Mod XP Quest Percent',
        292 => 'Open Stable',
        293 => 'Override Spells',
        294 => 'Prevent Power Regeneration',
        295 => 'Unknown Aura',
        296 => 'Set Vehicle Id',
        297 => 'Block Spell Family',
        298 => 'Strangulate',
        299 => 'Unknown Aura',
        300 => 'Share Damage Percent',
        301 => 'School Heal Absorb',
        302 => 'Unknown Aura',
        303 => 'Mod Damage Done Versus Aurastate',
        304 => 'Mod Fake Inebriate',
        305 => 'Mod Minimum Speed',
        306 => 'Unknown Aura',
        307 => 'Heal Absorb Test',
        308 => 'Hunter Trap',
        309 => 'Unknown Aura',
        310 => 'Mod Creature AoE Damage Avoidance',
        311 => 'Unknown Aura',
        312 => 'Unknown Aura',
        313 => 'Unknown Aura',
        314 => 'Prevent Ressurection',
        315 => 'Underwater Walking',
        316 => 'Periodic Haste'
    );

    public static $bgImagePath              = array (
        'tiny'   => 'style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"',
        'small'  => 'style="background-image: url(%s/images/wow/icons/small/%s.jpg)"',
        'medium' => 'style="background-image: url(%s/images/wow/icons/medium/%s.jpg)"',
        'large'  => 'style="background-image: url(%s/images/wow/icons/large/%s.jpg)"',
    );

    public static $configCats               = array(
        'Other', 'Site', 'Caching', 'Account', 'Session', 'Site Reputation', 'Google Analytics'
    );

    public static $tcEncoding               = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789';
    public static $wowheadLink              = '';
    private static $notes                   = [];

    public static function addNote($uGroupMask, $str)
    {
        self::$notes[] = [$uGroupMask, $str];
    }

    public static function getNotes()
    {
        $notes = [];

        foreach (self::$notes as $data)
            if (!$data[0] || User::isInGroup($data[0]))
                $notes[] = $data[1];

        return $notes;
    }

    private static $execTime = 0.0;

    public static function execTime($set = false)
    {
        if ($set)
        {
            self::$execTime = microTime(true);
            return;
        }

        if (!self::$execTime)
            return;

        $newTime        = microTime(true);
        $tDiff          = $newTime - self::$execTime;
        self::$execTime = $newTime;

        return self::formatTime($tDiff * 1000, true);
    }

    public static function getBuyoutForItem($itemId)
    {
        if (!$itemId)
            return 0;

        // try, when having filled char-DB at hand
        // return DB::Characters()->selectCell('SELECT SUM(a.buyoutprice) / SUM(ii.count) FROM auctionhouse a JOIN item_instance ii ON ii.guid = a.itemguid WHERE ii.itemEntry = ?d', $itemId);
        return 0;
    }

    public static function formatMoney($qty)
    {
        $money = '';

        if ($qty >= 10000)
        {
            $g = floor($qty / 10000);
            $money .= '<span class="moneygold">'.$g.'</span> ';
            $qty -= $g * 10000;
        }

        if ($qty >= 100)
        {
            $s = floor($qty / 100);
            $money .= '<span class="moneysilver">'.$s.'</span> ';
            $qty -= $s * 100;
        }

        if ($qty > 0)
            $money .= '<span class="moneycopper">'.$qty.'</span>';

        return $money;
    }

    public static function parseTime($sec)
    {
        $time = ['d' => 0, 'h' => 0, 'm' => 0, 's' => 0, 'ms' => 0];

        if ($sec >= 3600 * 24)
        {
            $time['d'] = floor($sec / 3600 / 24);
            $sec -= $time['d'] * 3600 * 24;
        }

        if ($sec >= 3600)
        {
            $time['h'] = floor($sec / 3600);
            $sec -= $time['h'] * 3600;
        }

        if ($sec >= 60)
        {
            $time['m'] = floor($sec / 60);
            $sec -= $time['m'] * 60;
        }

        if ($sec > 0)
        {
            $time['s'] = (int)$sec;
            $sec -= $time['s'];
        }

        if (($sec * 1000) % 1000)
            $time['ms'] = (int)($sec * 1000);

        return $time;
    }

    public static function formatTime($base, $short = false)
    {
        $s = self::parseTime($base / 1000);
        $fmt = [];

        if ($short)
        {
            if ($_ = round($s['d'] / 364))
                return $_." ".Lang::timeUnits('ab', 0);
            if ($_ = round($s['d'] / 30))
                return $_." ".Lang::timeUnits('ab', 1);
            if ($_ = round($s['d'] / 7))
                return $_." ".Lang::timeUnits('ab', 2);
            if ($_ = round($s['d']))
                return $_." ".Lang::timeUnits('ab', 3);
            if ($_ = round($s['h']))
                return $_." ".Lang::timeUnits('ab', 4);
            if ($_ = round($s['m']))
                return $_." ".Lang::timeUnits('ab', 5);
            if ($_ = round($s['s'] + $s['ms'] / 1000, 2))
                return $_." ".Lang::timeUnits('ab', 6);
            if ($s['ms'])
                return $s['ms']." ".Lang::timeUnits('ab', 7);

            return '0 '.Lang::timeUnits('ab', 6);
        }
        else
        {
            $_ = $s['d'] + $s['h'] / 24;
            if ($_ > 1 && !($_ % 364))                      // whole years
                return round(($s['d'] + $s['h'] / 24) / 364, 2)." ".Lang::timeUnits($s['d'] / 364 == 1 && !$s['h'] ? 'sg' : 'pl', 0);
            if ($_ > 1 && !($_ % 30))                       // whole month
                return round(($s['d'] + $s['h'] / 24) /  30, 2)." ".Lang::timeUnits($s['d'] /  30 == 1 && !$s['h'] ? 'sg' : 'pl', 1);
            if ($_ > 1 && !($_ % 7))                        // whole weeks
                return round(($s['d'] + $s['h'] / 24) /   7, 2)." ".Lang::timeUnits($s['d'] /   7 == 1 && !$s['h'] ? 'sg' : 'pl', 2);
            if ($s['d'])
                return round($s['d'] + $s['h']  /   24, 2)." ".Lang::timeUnits($s['d'] == 1 && !$s['h']  ? 'sg' : 'pl', 3);
            if ($s['h'])
                return round($s['h'] + $s['m']  /   60, 2)." ".Lang::timeUnits($s['h'] == 1 && !$s['m']  ? 'sg' : 'pl', 4);
            if ($s['m'])
                return round($s['m'] + $s['s']  /   60, 2)." ".Lang::timeUnits($s['m'] == 1 && !$s['s']  ? 'sg' : 'pl', 5);
            if ($s['s'])
                return round($s['s'] + $s['ms'] / 1000, 2)." ".Lang::timeUnits($s['s'] == 1 && !$s['ms'] ? 'sg' : 'pl', 6);
            if ($s['ms'])
                return $s['ms']." ".Lang::timeUnits($s['ms'] == 1 ? 'sg' : 'pl', 7);

            return '0 '.Lang::timeUnits('pl', 6);
        }
    }

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

    // pageText for Books (Item or GO) and questText
    public static function parseHtmlText($text)
    {
        if (stristr($text, '<HTML>'))                       // text is basically a html-document with weird linebreak-syntax
        {
            $pairs = array(
                '<HTML>'    => '',
                '</HTML>'   => '',
                '<BODY>'    => '',
                '</BODY>'   => '',
                '<BR></BR>' => '<br />'
            );

            // html may contain 'Pictures' and FlavorImages and "stuff"
            $text = preg_replace_callback(
                '/src="([^"]+)"/i',
                function ($m) { return 'src="'.STATIC_URL.'/images/wow/'.strtr($m[1], ['\\' => '/']).'.png"'; },
                strtr($text, $pairs)
            );
        }
        else
            $text = strtr($text, ["\n" => '<br />', "\r" => '']);

        $from = array(
            '/\|T([\w]+\\\)*([^\.]+)\.blp:\d+\|t/ui',       // images (force size to tiny)                      |T<fullPath>:<size>|t
            '/\|c(\w{6})\w{2}([^\|]+)\|r/ui',               // color                                            |c<RRGGBBAA><text>|r
            '/\$g\s*([^:;]+)\s*:\s*([^:;]+)\s*(:?[^:;]*);/ui',// directed gender-reference                      $g:<male>:<female>:<refVariable>
            '/\$t([^;]+);/ui',                              // nonsense, that the client apparently ignores
            '/\|\d\-?\d?\((\$\w)\)/ui',                     // and another modifier for something russian       |3-6($r)
            '/<([^\"=\/>]+\s[^\"=\/>]+)>/ui',               // emotes (workaround: at least one whitespace and never " or = between brackets)
            '/\$(\d+)w/ui'                                  // worldState(?)-ref found on some pageTexts        $1234w
        );

        $to = array(
            '<span class="icontiny" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/\2.gif)">',
            '<span style="color: #\1">\2</span>',
            '&lt;\1/\2&gt;',
            '',
            '\1',
            '&lt;\1&gt;',
            '<span class="q0">WorldState #\1</span>'
        );

        $text = preg_replace($from, $to, $text);

        $pairs = array(
            '$c' => '&lt;'.Lang::game('class').'&gt;',
            '$C' => '&lt;'.Lang::game('class').'&gt;',
            '$r' => '&lt;'.Lang::game('race').'&gt;',
            '$R' => '&lt;'.Lang::game('race').'&gt;',
            '$n' => '&lt;'.Lang::main('name').'&gt;',
            '$N' => '&lt;'.Lang::main('name').'&gt;',
            '$b' => '<br />',
            '$B' => '<br />',
            '|n' => ''                                      // what .. the fuck .. another type of line terminator? (only in spanish though)
        );

        return strtr($text, $pairs);
    }

    public static function asHex($val)
    {
        $_ = decHex($val);
        while (fMod(strLen($_), 4))                         // in 4-blocks
            $_ = '0'.$_;

        return '0x'.strToUpper($_);
    }

    public static function asBin($val)
    {
        $_ = decBin($val);
        while (fMod(strLen($_), 4))                         // in 4-blocks
            $_ = '0'.$_;

        return 'b'.strToUpper($_);
    }

    public static function htmlEscape($data)
    {
        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::htmlEscape($v);

            return $data;
        }
        else
            return htmlspecialchars(trim($data), ENT_QUOTES, 'utf-8');
    }

    public static function jsEscape($data)
    {
        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::jsEscape($v);

            return $data;
        }
        else
            return strtr(trim($data), array(
                '\\' => '\\\\',
                "'"  => "\\'",
                '"'  => '\\"',
                "\r" => '\\r',
                "\n" => '\\n'
            ));
    }

    // default back to enUS if localization unavailable
    public static function localizedString($data, $field, $silent = false)
    {
        // default case: selected locale available
        if (!empty($data[$field.'_loc'.User::$localeId]))
            return $data[$field.'_loc'.User::$localeId];

        // locale not enUS; aowow-type localization available; add brackets if not silent
        else if (User::$localeId != LOCALE_EN && !empty($data[$field.'_loc0']))
            return $silent ? $data[$field.'_loc0'] : '['.$data[$field.'_loc0'].']';

        // locale not enUS; TC localization; add brackets if not silent
        else if (User::$localeId != LOCALE_EN && !empty($data[$field]))
            return $silent ? $data[$field] : '['.$data[$field].']';

        // locale enUS; TC localization; return normal
        else if (User::$localeId == LOCALE_EN && !empty($data[$field]))
            return $data[$field];

        // nothing to find; be empty
        else
            return '';
    }

    // for item and spells
    public static function setRatingLevel($level, $type, $val)
    {
        if (in_array($type, [ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_DODGE_RATING, ITEM_MOD_PARRY_RATING, ITEM_MOD_BLOCK_RATING, ITEM_MOD_RESILIENCE_RATING]) && $level < 34)
            $level = 34;

        if (!isset(Util::$gtCombatRatings[$type]))
            $result = 0;
        else
        {
            if ($level > 70)
                $c = 82 / 52 * pow(131 / 63, ($level - 70) / 10);
            else if ($level > 60)
                $c = 82 / (262 - 3 * $level);
            else if ($level > 10)
                $c = ($level - 8) / 52;
            else
                $c = 2 / 52;

            // do not use localized number format here!
            $result = number_format($val / Util::$gtCombatRatings[$type] / $c, 2);
        }

        if (!in_array($type, array(ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_EXPERTISE_RATING)))
            $result .= '%';

        return sprintf(Lang::item('ratingString'), '<!--rtg%'.$type.'-->'.$result, '<!--lvl-->'.$level);
    }

    public static function powerUseLocale($domain = 'www')
    {
        foreach (Util::$localeStrings as $k => $v)
        {
            if (strstr($v, $domain))
            {
                User::useLocale($k);
                Lang::load(User::$localeString);
                return;
            }
        }

        if ($domain == 'www')
        {
            User::useLocale(LOCALE_EN);
            Lang::load(User::$localeString);
        }
    }

    // default ucFirst doesn't convert UTF-8 chars
    public static function ucFirst($str)
    {
        $len   = mb_strlen($str) - 1;
        $first = mb_substr($str, 0, 1);
        $rest  = mb_substr($str, 1, $len);

        return mb_strtoupper($first).$rest;
    }

    public static function ucWords($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE);
    }

    public static function lower($str)
    {
        return mb_strtolower($str);
    }

    // note: valid integer > 32bit are returned as float
    public static function checkNumeric(&$data)
    {
        if ($data === null)
            return false;
        else if (!is_array($data))
        {
            $data = trim($data);

            if (is_numeric($data))
            {
                $data += 0;
                return true;
            }
            else if (preg_match('/^\d*,\d+$/', $data))
            {
                $data = floatVal(strtr($data, ',', '.'));
                return true;
            }

            return false;
        }

        array_walk($data, function(&$item, $key) {
            self::checkNumeric($item);
        });

        return false;                                       // always false for passed arrays
    }

    public static function arraySumByKey(&$ref)
    {
        $nArgs = func_num_args();
        if (!is_array($ref) || $nArgs < 2)
            return;

        for ($i = 1; $i < $nArgs; $i++)
        {
            $arr = func_get_arg($i);
            if (!is_array($arr))
                continue;

            foreach ($arr as $k => $v)
            {
                if (!isset($ref[$k]))
                    $ref[$k] = 0;

                $ref[$k] += $v;
            }
        }
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

    public static function urlize($str)
    {
        $search  = ['<', '>', ' / ', "'", '(', ')'];
        $replace = ['&lt;', '&gt;', '-', '', '', ''];
        $str = str_replace($search, $replace, $str);

        $accents = array(
            "ß" => "ss",
            "á" => "a", "ä" => "a", "à" => "a", "â" => "a",
            "è" => "e", "ê" => "e", "é" => "e", "ë" => "e",
            "í" => "i", "î" => "i", "ì" => "i", "ï" => "i",
            "ñ" => "n",
            "ò" => "o", "ó" => "o", "ö" => "o", "ô" => "o",
            "ú" => "u", "ü" => "u", "û" => "u", "ù" => "u",
            "œ" => "oe",
            "Á" => "A", "Ä" => "A", "À" => "A", "Â" => "A",
            "È" => "E", "Ê" => "E", "É" => "E", "Ë" => "E",
            "Í" => "I", "Î" => "I", "Ì" => "I", "Ï" => "I",
            "Ñ" => "N",
            "Ò" => "O", "Ó" => "O", "Ö" => "O", "Ô" => "O",
            "Ú" => "U", "Ü" => "U", "Û" => "U", "Ù" => "U",
            "œ" => "Oe"
        );
        $str = strtr($str, $accents);
        $str = trim($str);
        $str = preg_replace('/[^a-z0-9]/i', '-', $str);

        $str = str_replace('--', '-', $str);
        $str = str_replace('--', '-', $str);

        $str = rtrim($str, '-');
        $str = strtolower($str);

        return $str;
    }

    public static function isValidEmail($email)
    {
        return preg_match('/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i', $email);
    }

    public static function loadStaticFile($file, &$result, $localized = false)
    {
        $success = true;
        if ($localized)
        {
            if (file_exists('datasets/'.User::$localeString.'/'.$file))
                $result .= file_get_contents('datasets/'.User::$localeString.'/'.$file);
            else if (file_exists('datasets/enus/'.$file))
                $result .= file_get_contents('datasets/enus/'.$file);
            else
                $success = false;
        }
        else
        {
            if (file_exists('datasets/'.$file))
                $result .= file_get_contents('datasets/'.$file);
            else
                $success = false;
        }

        return $success;
    }

    public static function createHash($length = 40)         // just some random numbers for unsafe identifictaion purpose
    {
        static $seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $hash = '';

        for ($i = 0; $i < $length; $i++)
            $hash .= substr($seed, mt_rand(0, 61), 1);

        return $hash;
    }

    public static function mergeJsGlobals(&$master)
    {
        $args = func_get_args();
        if (count($args) < 2)                               // insufficient args
            return false;

        if (!is_array($master))
            $master = [];

        for ($i = 1; $i < count($args); $i++)               // skip first (master) entry
        {
            foreach ($args[$i] as $type => $data)
            {
                // bad data or empty
                if (empty(Util::$typeStrings[$type]) || !is_array($data) || !$data)
                    continue;

                if (!isset($master[$type]))
                    $master[$type] = [];

                foreach ($data as $k => $d)
                {
                    if (!isset($master[$type][$k]))         // int: id, yet to look up
                        $master[$type][$k] = $d;
                    else if (is_array($d))                  // array: already fetched data (overwrite old value if set)
                        $master[$type][$k] = $d;
                    // else                                 // id overwrites data .. do not want
                }
            }
        }

        return true;
    }

    public static function gainSiteReputation($user, $action, $miscData = [])
    {
        if (!$user || !$action)
            return false;

        $x = [];

        switch ($action)
        {
            case SITEREP_ACTION_REGISTER:
                $x['amount'] = CFG_REP_REWARD_REGISTER;
                break;
            case SITEREP_ACTION_DAILYVISIT:
                $x['sourceA'] = time();
                $x['amount']  = CFG_REP_REWARD_DAILYVISIT;
                break;
            case SITEREP_ACTION_COMMENT:
                if (empty($miscData['id']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['amount']  = CFG_REP_REWARD_COMMENT;
                break;
            case SITEREP_ACTION_UPVOTED:
            case SITEREP_ACTION_DOWNVOTED:
                if (empty($miscData['id']) || empty($miscData['voterId']))
                    return false;

                DB::Aowow()->query(                         // delete old votes the user has cast
                    'DELETE FROM ?_account_reputation WHERE sourceA = ?d AND sourceB = ?d AND userId = ?d AND action IN (?a)',
                    $miscData['id'],
                    $miscData['voterId'],
                    $user,
                    [SITEREP_ACTION_UPVOTED, SITEREP_ACTION_DOWNVOTED]
                );

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['sourceB'] = $miscData['voterId'];
                $x['amount']  = $action == SITEREP_ACTION_UPVOTED ? CFG_REP_REWARD_UPVOTED : CFG_REP_REWARD_DOWNVOTED;
                break;
            case SITEREP_ACTION_UPLOAD:
                if (empty($miscData['id']) || empty($miscData['what']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // screenshotId or videoId
                $x['sourceB'] = $miscData['what'];          // screenshot:1 or video:NYD
                $x['amount']  = CFG_REP_REWARD_UPLOAD;
                break;
            case SITEREP_ACTION_GOOD_REPORT:                // NYI
            case SITEREP_ACTION_BAD_REPORT:
                if (empty($miscData['id']))                 // reportId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_GOOD_REPORT ? CFG_REP_REWARD_GOOD_REPORT : CFG_REP_REWARD_BAD_REPORT;
                break;
            case SITEREP_ACTION_ARTICLE:                    // NYI
                if (empty($miscData['id']))                 // reportId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = CFG_REP_REWARD_ARTICLE;
                break;
            case SITEREP_ACTION_USER_WARNED:                // NYI
            case SITEREP_ACTION_USER_SUSPENDED:
                if (empty($miscData['id']))                 // banId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_USER_WARNED ? CFG_REP_REWARD_USER_WARNED : CFG_REP_REWARD_USER_SUSPENDED;
                break;
        }

        $x = array_merge($x, array(
            'userId' => $user,
            'action' => $action,
            'date'   => !empty($miscData['date']) ? $miscData['date'] : time()
        ));

        return DB::Aowow()->query('INSERT IGNORE INTO ?_account_reputation (?#) VALUES (?a)', array_keys($x), array_values($x));
    }

    // TYPE => tableName; when handling comments, screenshots or videos
    public static function getCCTableParent($type)
    {
        // only filtrable types; others don't care about being flagged for having CommunityContent
        switch ($type)
        {
            case TYPE_ACHIEVEMENT:  return '?_achievement';
            case TYPE_SPELL:        return '?_spell';
            case TYPE_OBJECT:       return '?_objects';
            case TYPE_ITEM:         return '?_items';
            case TYPE_ITEMSET:      return '?_itemset';
            case TYPE_NPC:          return '?_creature';
            case TYPE_QUEST:        return '?_quests';
            default:                return null;
        }
    }

    public static function getServerConditions($srcType, $srcGroup = null, $srcEntry = null)
    {
        if (!$srcGroup && !$srcEntry)
            return [];

        $result    = [];
        $jsGlobals = [];

        $conditions = DB::World()->select(
            'SELECT  SourceTypeOrReferenceId, SourceEntry, SourceGroup, ElseGroup,
                     ConditionTypeOrReference, ConditionTarget, ConditionValue1, ConditionValue2, ConditionValue3, NegativeCondition
            FROM     conditions
            WHERE    SourceTypeOrReferenceId IN (?a) AND ?# = ?d
            ORDER BY SourceTypeOrReferenceId, SourceEntry, SourceGroup, ElseGroup ASC',
            is_array($srcType) ? $srcType : [$srcType],
            $srcGroup ? 'SourceGroup' : 'SourceEntry',
            $srcGroup ?: $srcEntry
        );

        foreach ($conditions as $c)
        {
            switch ($c['SourceTypeOrReferenceId'])
            {
                case CND_SRC_SPELL_CLICK_EVENT:             // 18
                case CND_SRC_VEHICLE_SPELL:                 // 21
                case CND_SRC_NPC_VENDOR:                    // 23
                    $jsGlobals[TYPE_NPC][] = $c['SourceGroup'];
                    break;
            }

            switch ($c['ConditionTypeOrReference'])
            {
                case CND_AURA:                              // 1
                    $c['ConditionValue2'] = NULL;           // do not use his param
                case CND_SPELL:                             // 25
                    $jsGlobals[TYPE_SPELL][] = $c['ConditionValue1'];
                    break;
                case CND_ITEM:                              // 2
                    $c['ConditionValue3'] = NULL;           // do not use his param
                case CND_ITEM_EQUIPPED:                     // 3
                    $jsGlobals[TYPE_ITEM][] = $c['ConditionValue1'];
                    break;
                case CND_MAPID:                             // 22 - break down to area or remap for use with g_zone_categories
                    switch ($c['ConditionValue1'])
                    {
                        case 530:                           // outland
                            $c['ConditionValue1'] = 8;
                            break;
                        case 571:                           // northrend
                            $c['ConditionValue1'] = 10;
                            break;
                        case 0:                             // old world is fine
                        case 1:
                            break;
                        default:                            // remap for area
                            $cnd = array(
                                ['mapId', (int)$c['ConditionValue1']],
                                ['parentArea', 0],          // not child zones
                                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                                1                           // only one result
                            );
                            $zone = new ZoneList($cnd);
                            if (!$zone->error)
                            {
                                $jsGlobals[TYPE_ZONE][] = $zone->getField('id');
                                $c['ConditionTypeOrReference'] = CND_ZONEID;
                                $c['ConditionValue1'] = $zone->getField('id');
                                break;
                            }
                            else
                                continue;
                    }
                case CND_ZONEID:                            // 4
                case CND_AREAID:                            // 23
                    $jsGlobals[TYPE_ZONE][] = $c['ConditionValue1'];
                    break;
                case CND_REPUTATION_RANK:                   // 5
                    $jsGlobals[TYPE_FACTION][] = $c['ConditionValue1'];
                    break;
                case CND_SKILL:                             // 7
                    $jsGlobals[TYPE_SKILL][] = $c['ConditionValue1'];
                    break;
                case CND_QUESTREWARDED:                     // 8
                case CND_QUESTTAKEN:                        // 9
                case CND_QUEST_NONE:                        // 14
                case CND_QUEST_COMPLETE:                    // 28
                    $jsGlobals[TYPE_QUEST][] = $c['ConditionValue1'];
                    break;
                case CND_ACTIVE_EVENT:                      // 12
                    $jsGlobals[TYPE_WORLDEVENT][] = $c['ConditionValue1'];
                    break;
                case CND_ACHIEVEMENT:                       // 17
                    $jsGlobals[TYPE_ACHIEVEMENT][] = $c['ConditionValue1'];
                    break;
                case CND_TITLE:                             // 18
                    $jsGlobals[TYPE_TITLE][] = $c['ConditionValue1'];
                    break;
                case CND_NEAR_CREATURE:                     // 29
                    $jsGlobals[TYPE_NPC][] = $c['ConditionValue1'];
                    break;
                case CND_NEAR_GAMEOBJECT:                   // 30
                    $jsGlobals[TYPE_OBJECT][] = $c['ConditionValue1'];
                    break;
                case CND_CLASS:                             // 15
                    for ($i = 0; $i < 11; $i++)
                        if ($c['ConditionValue1'] & (1 << $i))
                            $jsGlobals[TYPE_CLASS][] = $i + 1;
                    break;
                case CND_RACE:                              // 16
                    for ($i = 0; $i < 11; $i++)
                        if ($c['ConditionValue1'] & (1 << $i))
                            $jsGlobals[TYPE_RACE][] = $i + 1;
                    break;
                case CND_OBJECT_ENTRY:                      // 31
                    if ($c['ConditionValue1'] == 3)
                        $jsGlobals[TYPE_NPC][] = $c['ConditionValue2'];
                    else if ($c['ConditionValue1'] == 5)
                        $jsGlobals[TYPE_OBJECT][] = $c['ConditionValue2'];
                    break;
                case CND_TEAM:                              // 6
                    if ($c['ConditionValue1'] == 469)       // Alliance
                        $c['ConditionValue1'] = 1;
                    else if ($c['ConditionValue1'] == 67)   // Horde
                        $c['ConditionValue1'] = 2;
                    else
                        continue;
            }

            $res = [$c['NegativeCondition'] ? -$c['ConditionTypeOrReference'] : $c['ConditionTypeOrReference']];
            foreach ([1, 2, 3] as $i)
                if (($_ = $c['ConditionValue'.$i]) || $c['ConditionTypeOrReference'] = CND_DISTANCE_TO)
                    $res[] = $_;

            $group = $c['SourceEntry'];
            if (!in_array($c['SourceTypeOrReferenceId'], [CND_SRC_CREATURE_TEMPLATE_VEHICLE, CND_SRC_SPELL, CND_SRC_QUEST_ACCEPT, CND_SRC_QUEST_SHOW_MARK, CND_SRC_SPELL_PROC]))
                $group = $c['SourceEntry'] . ':' . $c['SourceGroup'];

            $result[$c['SourceTypeOrReferenceId']] [$group] [$c['ElseGroup']] [] = $res;
        }

        return [$result, $jsGlobals];
    }

    public static function sendNoCacheHeader()
    {
        header('Expires: Sat, 01 Jan 2000 01:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }

    public static function toJSON($data, $forceFlags = 0)
    {
        $flags = $forceFlags ?: (JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        if (CFG_DEBUG && !$forceFlags)
            $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($data, $flags);

        // handle strings prefixed with $ as js-variables
        // literal: match everything (lazy) between first pair of unescaped double quotes. First character must be $.
        $json = preg_replace_callback('/(?<!\\\\)"\$(.+?)(?<!\\\\)"/i', function($m) { return str_replace('\"', '"', $m[1]); }, $json);

        return $json;
    }

    public static function checkOrCreateDirectory($path)
    {
        // remove multiple slashes
        $path = preg_replace('|/+|', '/', $path);

        if (!is_dir($path) && !@mkdir($path, self::FILE_ACCESS, true))
            trigger_error('Could not create directory: '.$path, E_USER_ERROR);
        else if (!is_writable($path) && !@chmod($path, self::FILE_ACCESS))
            trigger_error('Cannot write into directory: '.$path, E_USER_ERROR);
        else
            return true;

        return false;
    }

    private static $realms = [];
    public static function getRealms()
    {
        if (DB::isConnectable(DB_AUTH) && !self::$realms)
        {
            self::$realms = DB::Auth()->select('SELECT id AS ARRAY_KEY, name, IF(timezone IN (8, 9, 10, 11, 12), "eu", "us") AS region FROM realmlist WHERE allowedSecurityLevel = 0 AND gamebuild = ?d', WOW_BUILD);
            foreach (self::$realms as $rId => $rData)
            {
                if (DB::isConnectable(DB_CHARACTERS . $rId))
                    continue;

                unset(self::$realms[$rId]);
                trigger_error('Realm #'.$rId.' ('.$rData['name'].') has no connection info set.', E_USER_NOTICE);
            }
        }

        return self::$realms;
    }
}

?>
