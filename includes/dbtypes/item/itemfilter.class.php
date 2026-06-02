<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemFilter extends Filter
{
    public const /* int */ GROUP_BY_NONE   = 0;
    public const /* int */ GROUP_BY_SLOT   = 1;
    public const /* int */ GROUP_BY_LEVEL  = 2;
    public const /* int */ GROUP_BY_SOURCE = 3;

    private array  $ubFilter     = [];                      // usable-by - limit weapon/armor selection per CharClass - itemClass => available itemsubclasses
    private string $extCostQuery = 'SELECT `item` FROM npc_vendor            WHERE `extendedCost` IN %in UNION
                                    SELECT `item` FROM game_event_npc_vendor WHERE `extendedCost` IN %in';

    protected string $type  = 'items';
    protected static array $enums = array(
         16 => parent::ENUM_ZONE,                           // drops in zone
         17 => parent::ENUM_FACTION,                        // requiresrepwith
         99 => parent::ENUM_PROFESSION,                     // requiresprof
         86 => parent::ENUM_PROFESSION,                     // craftedprof
         87 => parent::ENUM_PROFESSION,                     // reagentforability
        105 => parent::ENUM_HEROICDUNGEON,                  // drops in nh dungeon
        106 => parent::ENUM_HEROICDUNGEON,                  // drops in hc dungeon
        126 => parent::ENUM_ZONE,                           // rewardedbyquestin
        147 => parent::ENUM_MULTIMODERAID,                  // drops in nh raid 10
        148 => parent::ENUM_MULTIMODERAID,                  // drops in nh raid 25
        149 => parent::ENUM_HEROICRAID,                     // drops in hc raid 10
        150 => parent::ENUM_HEROICRAID,                     // drops in hc raid 25
        152 => parent::ENUM_CLASSS,                         // class-specific
        153 => parent::ENUM_RACE,                           // race-specific
        160 => parent::ENUM_EVENT,                          // relatedevent
        169 => parent::ENUM_EVENT,                          // requiresevent
        158 => parent::ENUM_CURRENCY,                       // purchasablewithcurrency
        118 => array(                                       // itemcurrency
            52027, 52030, 52026, 52029, 52025, 52028, 47242, 47557, 47558, 47559, 45632, 45633, 45634, 45635, 45636, 45637, 45638, 45639, 45640, 45641,
            45642, 45643, 45644, 45645, 45646, 45647, 45648, 45649, 45650, 45651, 45652, 45653, 45654, 45655, 45656, 45657, 45658, 45659, 45660, 45661,
            40625, 40626, 40627, 40610, 40611, 40612, 40631, 40632, 40633, 40628, 40629, 40630, 40613, 40614, 40615, 40616, 40617, 40618, 40619, 40620,
            40621, 40634, 40635, 40636, 40637, 40638, 40639, 40622, 40623, 40624, 34853, 34854, 34855, 34856, 34857, 34858, 34848, 34851, 34852, 31089,
            31091, 31090, 31092, 31094, 31093, 31097, 31095, 31096, 31098, 31100, 31099, 31101, 31103, 31102, 30236, 30237, 30238, 30239, 30240, 30241,
            30242, 30243, 30244, 30245, 30246, 30247, 30248, 30249, 30250, 29754, 29753, 29755, 29757, 29758, 29756, 29760, 29761, 29759, 29766, 29767,
            29765, 29763, 29764, 29762, 34169, 34186, 34245, 34332, 34339, 34345, 34244, 34208, 34180, 34229, 34350, 34342, 34211, 34243, 34216, 34167,
            34170, 34192, 34233, 34234, 34202, 34195, 34209, 34193, 34212, 34351, 34215
        ),
        163 => array(                                       // enchantment mats
            34057, 22445, 11176, 34052, 11082, 34055, 16203, 10939, 11135, 11175, 22446, 16204, 34054, 14344, 11084, 11139, 22449, 11178, 10998, 34056,
            16202, 10938, 11134, 11174, 22447, 20725, 14343, 34053, 10978, 11138, 22448, 11177, 11083, 10940, 11137, 22450
        ),
         91 => array(                                       // tool
                3,    14,   162,   168,   141,     2,     4,   169,   161,    15,   167,    81,    21,   165,    12,    62,    10,   101,   189,     6,
               63,    41,     8,     7,   190,     9,   166,   121,     5
        ),
         66 => array(                                       // profession specialization
            1 => -1,
            2 => [ 9788,  9787, 17041, 17040, 17039                                                        ],
            3 => -1,
            4 => -1,
            5 => [20219, 20222                                                                             ],
            6 => -1,
            7 => -1,
            8 => [10656, 10658, 10660                                                                      ],
            9 => -1,
           10 => [26798, 26801, 26797                                                                      ],
           11 => [ 9788,  9787, 17041, 17040, 17039, 20219, 20222, 10656, 10658, 10660, 26798, 26801, 26797],  // i know, i know .. lazy as fuck
           12 => false,
           13 => -1,
           14 => -1,
           15 => -1
       ),
       128 => array(                                        // source
             1 => true,                                     // Any
             2 => false,                                    // None
             3 => SRC_CRAFTED,
             4 => SRC_DROP,
             5 => SRC_PVP,
             6 => SRC_QUEST,
             7 => SRC_VENDOR,
             9 => SRC_STARTER,
            10 => SRC_EVENT,
            11 => SRC_ACHIEVEMENT,
            12 => SRC_FISHING
        ),
        200 => parent::ENUM_ITEM_VISUAL
    );

    protected static array $genericFilter = array(
          2 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               1                 ], // bindonpickup [yn]
          3 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               2                 ], // bindonequip [yn]
          4 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               3                 ], // bindonuse [yn]
          5 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               [4, 5]            ], // questitem [yn]
          6 => [parent::CR_CALLBACK,  'cbQuestRelation',        null,                    null              ], // startsquest [side]
          7 => [parent::CR_BOOLEAN,   'description_loc0',       true                                       ], // hasflavortext
          8 => [parent::CR_BOOLEAN,   'requiredDisenchantSkill'                                            ], // disenchantable
          9 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_CONJURED                         ], // conjureditem
         10 => [parent::CR_BOOLEAN,   'lockId'                                                             ], // locked
         11 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_OPENABLE                         ], // openable
         12 => [parent::CR_BOOLEAN,   'itemset'                                                            ], // partofset
         13 => [parent::CR_BOOLEAN,   'randomEnchant'                                                      ], // randomlyenchanted
         14 => [parent::CR_BOOLEAN,   'pageTextId'                                                         ], // readable
         15 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'maxCount',              1                 ], // unique [yn]
         16 => [parent::CR_CALLBACK,  'cbDropsInZone',          null,                    null              ], // dropsin [zone]
         17 => [parent::CR_ENUM,      'requiredFaction',        true,                    true              ], // requiresrepwith
         18 => [parent::CR_CALLBACK,  'cbFactionQuestReward',   null,                    null              ], // rewardedbyfactionquest [side]
         20 => [parent::CR_NUMERIC,   'is.str',                 NUM_CAST_INT,            true              ], // str
         21 => [parent::CR_NUMERIC,   'is.agi',                 NUM_CAST_INT,            true              ], // agi
         22 => [parent::CR_NUMERIC,   'is.sta',                 NUM_CAST_INT,            true              ], // sta
         23 => [parent::CR_NUMERIC,   'is.int',                 NUM_CAST_INT,            true              ], // int
         24 => [parent::CR_NUMERIC,   'is.spi',                 NUM_CAST_INT,            true              ], // spi
         25 => [parent::CR_NUMERIC,   'is.arcres',              NUM_CAST_INT,            true              ], // arcres
         26 => [parent::CR_NUMERIC,   'is.firres',              NUM_CAST_INT,            true              ], // firres
         27 => [parent::CR_NUMERIC,   'is.natres',              NUM_CAST_INT,            true              ], // natres
         28 => [parent::CR_NUMERIC,   'is.frores',              NUM_CAST_INT,            true              ], // frores
         29 => [parent::CR_NUMERIC,   'is.shares',              NUM_CAST_INT,            true              ], // shares
         30 => [parent::CR_NUMERIC,   'is.holres',              NUM_CAST_INT,            true              ], // holres
         32 => [parent::CR_NUMERIC,   'is.dps',                 NUM_CAST_FLOAT,          true              ], // dps
         33 => [parent::CR_NUMERIC,   'is.dmgmin1',             NUM_CAST_INT,            true              ], // dmgmin1
         34 => [parent::CR_NUMERIC,   'is.dmgmax1',             NUM_CAST_INT,            true              ], // dmgmax1
         35 => [parent::CR_CALLBACK,  'cbDamageType',           null,                    null              ], // damagetype [enum]
         36 => [parent::CR_NUMERIC,   'is.speed',               NUM_CAST_FLOAT,          true              ], // speed
         37 => [parent::CR_NUMERIC,   'is.mleatkpwr',           NUM_CAST_INT,            true              ], // mleatkpwr
         38 => [parent::CR_NUMERIC,   'is.rgdatkpwr',           NUM_CAST_INT,            true              ], // rgdatkpwr
         39 => [parent::CR_NUMERIC,   'is.rgdhitrtng',          NUM_CAST_INT,            true              ], // rgdhitrtng
         40 => [parent::CR_NUMERIC,   'is.rgdcritstrkrtng',     NUM_CAST_INT,            true              ], // rgdcritstrkrtng
         41 => [parent::CR_NUMERIC,   'is.armor',               NUM_CAST_INT,            true              ], // armor
         42 => [parent::CR_NUMERIC,   'is.defrtng',             NUM_CAST_INT,            true              ], // defrtng
         43 => [parent::CR_NUMERIC,   'is.block',               NUM_CAST_INT,            true              ], // block
         44 => [parent::CR_NUMERIC,   'is.blockrtng',           NUM_CAST_INT,            true              ], // blockrtng
         45 => [parent::CR_NUMERIC,   'is.dodgertng',           NUM_CAST_INT,            true              ], // dodgertng
         46 => [parent::CR_NUMERIC,   'is.parryrtng',           NUM_CAST_INT,            true              ], // parryrtng
         48 => [parent::CR_NUMERIC,   'is.splhitrtng',          NUM_CAST_INT,            true              ], // splhitrtng
         49 => [parent::CR_NUMERIC,   'is.splcritstrkrtng',     NUM_CAST_INT,            true              ], // splcritstrkrtng
         50 => [parent::CR_NUMERIC,   'is.splheal',             NUM_CAST_INT,            true              ], // splheal
         51 => [parent::CR_NUMERIC,   'is.spldmg',              NUM_CAST_INT,            true              ], // spldmg
         52 => [parent::CR_NUMERIC,   'is.arcsplpwr',           NUM_CAST_INT,            true              ], // arcsplpwr
         53 => [parent::CR_NUMERIC,   'is.firsplpwr',           NUM_CAST_INT,            true              ], // firsplpwr
         54 => [parent::CR_NUMERIC,   'is.frosplpwr',           NUM_CAST_INT,            true              ], // frosplpwr
         55 => [parent::CR_NUMERIC,   'is.holsplpwr',           NUM_CAST_INT,            true              ], // holsplpwr
         56 => [parent::CR_NUMERIC,   'is.natsplpwr',           NUM_CAST_INT,            true              ], // natsplpwr
         57 => [parent::CR_NUMERIC,   'is.shasplpwr',           NUM_CAST_INT,            true              ], // shasplpwr
         59 => [parent::CR_NUMERIC,   'durability',             NUM_CAST_INT,            true              ], // dura
         60 => [parent::CR_NUMERIC,   'is.healthrgn',           NUM_CAST_INT,            true              ], // healthrgn
         61 => [parent::CR_NUMERIC,   'is.manargn',             NUM_CAST_INT,            true              ], // manargn
         62 => [parent::CR_CALLBACK,  'cbCooldown',             null,                    null              ], // cooldown [op] [int]
         63 => [parent::CR_NUMERIC,   'buyPrice',               NUM_CAST_INT,            true              ], // buyprice
         64 => [parent::CR_NUMERIC,   'sellPrice',              NUM_CAST_INT,            true              ], // sellprice
         65 => [parent::CR_CALLBACK,  'cbAvgMoneyContent',      null,                    null              ], // avgmoney [op] [int]
         66 => [parent::CR_ENUM,      'requiredSpell'                                                      ], // requiresprofspec
         68 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_DISENCHANTMENT,      null              ], // otdisenchanting [yn]
         69 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_FISHING,             null              ], // otfishing [yn]
         70 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_GATHERING,           null              ], // otherbgathering [yn]
         71 => [parent::CR_FLAG,      'cuFlags',                ITEM_CU_OT_ITEMLOOT                        ], // otitemopening [yn]
         72 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_DROP,                null              ], // otlooting [yn]
         73 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_MINING,              null              ], // otmining [yn]
         74 => [parent::CR_FLAG,      'cuFlags',                ITEM_CU_OT_OBJECTLOOT                      ], // otobjectopening [yn]
         75 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_PICKPOCKETING,       null              ], // otpickpocketing [yn]
         76 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_SKINNING,            null              ], // otskinning [yn]
         77 => [parent::CR_NUMERIC,   'is.atkpwr',              NUM_CAST_INT,            true              ], // atkpwr
         78 => [parent::CR_NUMERIC,   'is.mlehastertng',        NUM_CAST_INT,            true              ], // mlehastertng
         79 => [parent::CR_NUMERIC,   'is.resirtng',            NUM_CAST_INT,            true              ], // resirtng
         80 => [parent::CR_CALLBACK,  'cbHasSockets',           null,                    null              ], // has sockets [enum]
         81 => [parent::CR_CALLBACK,  'cbFitsGemSlot',          null,                    null              ], // fits gem slot [enum]
         83 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_UNIQUEEQUIPPED                   ], // uniqueequipped
         84 => [parent::CR_NUMERIC,   'is.mlecritstrkrtng',     NUM_CAST_INT,            true              ], // mlecritstrkrtng
         85 => [parent::CR_CALLBACK,  'cbObjectiveOfQuest',     null,                    null              ], // objectivequest [side]
         86 => [parent::CR_CALLBACK,  'cbCraftedByProf',        null,                    null              ], // craftedprof [enum]
         87 => [parent::CR_CALLBACK,  'cbReagentForAbility',    null,                    null              ], // reagentforability [enum]
         88 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_PROSPECTING,         null              ], // otprospecting [yn]
         89 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_PROSPECTABLE                     ], // prospectable
         90 => [parent::CR_CALLBACK,  'cbAvgBuyout',            null,                    null              ], // avgbuyout [op] [int]
         91 => [parent::CR_ENUM,      'totemCategory',          false,                   true              ], // tool
         92 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_VENDOR,              null              ], // soldbyvendor [yn]
         93 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_PVP,                 null              ], // otpvp [pvp]
         94 => [parent::CR_NUMERIC,   'is.splpen',              NUM_CAST_INT,            true              ], // splpen
         95 => [parent::CR_NUMERIC,   'is.mlehitrtng',          NUM_CAST_INT,            true              ], // mlehitrtng
         96 => [parent::CR_NUMERIC,   'is.critstrkrtng',        NUM_CAST_INT,            true              ], // critstrkrtng
         97 => [parent::CR_NUMERIC,   'is.feratkpwr',           NUM_CAST_INT,            true              ], // feratkpwr
         98 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_PARTYLOOT                        ], // partyloot
         99 => [parent::CR_ENUM,      'requiredSkill'                                                      ], // requiresprof
        100 => [parent::CR_NUMERIC,   'is.nsockets',            NUM_CAST_INT                               ], // nsockets
        101 => [parent::CR_NUMERIC,   'is.rgdhastertng',        NUM_CAST_INT,            true              ], // rgdhastertng
        102 => [parent::CR_NUMERIC,   'is.splhastertng',        NUM_CAST_INT,            true              ], // splhastertng
        103 => [parent::CR_NUMERIC,   'is.hastertng',           NUM_CAST_INT,            true              ], // hastertng
        104 => [parent::CR_STRING,    'description',            STR_LOCALIZED,           'nml.nDescription'], // flavortext
        105 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_DUNGEON_DROP,   1                 ], // dropsinnormal [heroicdungeon-any]
        106 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_DUNGEON_DROP,   2                 ], // dropsinheroic [heroicdungeon-any]
        107 => [parent::CR_STRING,    '',                       STR_LOCALIZED,           'nml.nEffects'    ], // effecttext [str]
        109 => [parent::CR_CALLBACK,  'cbArmorBonus',           null,                    null              ], // armorbonus [op] [int]
        111 => [parent::CR_NUMERIC,   'requiredSkillRank',      NUM_CAST_INT,            true              ], // reqskillrank
        113 => [parent::CR_FLAG,      'cuFlags',                CUSTOM_HAS_SCREENSHOT                      ], // hasscreenshots
        114 => [parent::CR_NUMERIC,   'is.armorpenrtng',        NUM_CAST_INT,            true              ], // armorpenrtng
        115 => [parent::CR_NUMERIC,   'is.health',              NUM_CAST_INT,            true              ], // health
        116 => [parent::CR_NUMERIC,   'is.mana',                NUM_CAST_INT,            true              ], // mana
        117 => [parent::CR_NUMERIC,   'is.exprtng',             NUM_CAST_INT,            true              ], // exprtng
        118 => [parent::CR_CALLBACK,  'cbPurchasableWith',      null,                    null              ], // purchasablewithitem [enum]
        119 => [parent::CR_NUMERIC,   'is.hitrtng',             NUM_CAST_INT,            true              ], // hitrtng
        123 => [parent::CR_NUMERIC,   'is.splpwr',              NUM_CAST_INT,            true              ], // splpwr
        124 => [parent::CR_CALLBACK,  'cbHasRandEnchant',       null,                    null              ], // randomenchants [str]
        125 => [parent::CR_CALLBACK,  'cbReqArenaRating',       null,                    null              ], // reqarenartng [op] [int]  todo (low): 'find out, why "IN (W, X, Y) AND IN (X, Y, Z)" doesn't result in "(X, Y)"
        126 => [parent::CR_CALLBACK,  'cbQuestRewardIn',        null,                    null              ], // rewardedbyquestin [zone-any]
        128 => [parent::CR_CALLBACK,  'cbSource',               null,                    null              ], // source [enum]
        129 => [parent::CR_CALLBACK,  'cbSoldByNPC',            null,                    null              ], // soldbynpc [str-small]
        130 => [parent::CR_FLAG,      'cuFlags',                CUSTOM_HAS_COMMENT                         ], // hascomments
        132 => [parent::CR_CALLBACK,  'cbGlyphType',            null,                    null              ], // glyphtype [enum]
        133 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_ACCOUNTBOUND                     ], // accountbound
        134 => [parent::CR_NUMERIC,   'is.mledps',              NUM_CAST_FLOAT,          true              ], // mledps
        135 => [parent::CR_NUMERIC,   'is.mledmgmin',           NUM_CAST_INT,            true              ], // mledmgmin
        136 => [parent::CR_NUMERIC,   'is.mledmgmax',           NUM_CAST_INT,            true              ], // mledmgmax
        137 => [parent::CR_NUMERIC,   'is.mlespeed',            NUM_CAST_FLOAT,          true              ], // mlespeed
        138 => [parent::CR_NUMERIC,   'is.rgddps',              NUM_CAST_FLOAT,          true              ], // rgddps
        139 => [parent::CR_NUMERIC,   'is.rgddmgmin',           NUM_CAST_INT,            true              ], // rgddmgmin
        140 => [parent::CR_NUMERIC,   'is.rgddmgmax',           NUM_CAST_INT,            true              ], // rgddmgmax
        141 => [parent::CR_NUMERIC,   'is.rgdspeed',            NUM_CAST_FLOAT,          true              ], // rgdspeed
        142 => [parent::CR_STRING,    'ic.name'                                                            ], // icon
        143 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_MILLING,             null              ], // otmilling [yn]
        144 => [parent::CR_CALLBACK,  'cbPvpPurchasable',       'reqHonorPoints',        null              ], // purchasablewithhonor [yn]
        145 => [parent::CR_CALLBACK,  'cbPvpPurchasable',       'reqArenaPoints',        null              ], // purchasablewitharena [yn]
        146 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_HEROIC                           ], // heroic
        147 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      1,                ], // dropsinnormal10 [multimoderaid-any]
        148 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      2,                ], // dropsinnormal25 [multimoderaid-any]
        149 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      4,                ], // dropsinheroic10 [heroicraid-any]
        150 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      8,                ], // dropsinheroic25 [heroicraid-any]
        151 => [parent::CR_NUMERIC,   'id',                     NUM_CAST_INT,            true              ], // id
        152 => [parent::CR_CALLBACK,  'cbClassRaceSpec',        'requiredClass'                            ], // classspecific [enum]
        153 => [parent::CR_CALLBACK,  'cbClassRaceSpec',        'requiredRace'                             ], // racespecific [enum]
        154 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_REFUNDABLE                       ], // refundable
        155 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_ARENA                     ], // usableinarenas
        156 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_SHAPED                    ], // usablewhenshapeshifted
        157 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_SMARTLOOT                        ], // smartloot
        158 => [parent::CR_CALLBACK,  'cbPurchasableWith',      null,                    null              ], // purchasablewithcurrency [enum]
        159 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_MILLABLE                         ], // millable
        160 => [parent::CR_NYI_PH,    null,                     1,                                         ], // relatedevent [enum]      like 169 .. crawl though npc_vendor and loot_templates of event-related spawns
        161 => [parent::CR_CALLBACK,  'cbAvailable',            null,                    null              ], // availabletoplayers [yn]
        162 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_DEPRECATED                       ], // deprecated
        163 => [parent::CR_CALLBACK,  'cbDisenchantsInto',      null,                    null              ], // disenchantsinto [disenchanting]
        165 => [parent::CR_NUMERIC,   'repairPrice',            NUM_CAST_INT,            true              ], // repaircost
        167 => [parent::CR_FLAG,      'cuFlags',                CUSTOM_HAS_VIDEO                           ], // hasvideos
        168 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'spellId1',              LEARN_SPELLS      ], // teachesspell [yn]
        169 => [parent::CR_ENUM,      'e.holidayId',            true,                    true              ], // requiresevent
        171 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_REDEMPTION,          null              ], // otredemption [yn]
        172 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_ACHIEVEMENT,         null              ], // rewardedbyachievement [yn]
        176 => [parent::CR_STAFFFLAG, 'flags'                                                              ], // flags
        177 => [parent::CR_STAFFFLAG, 'flagsExtra'                                                         ], // flags2
        200 => [parent::CR_CALLBACK,  'cbHasItemVisual',        null,                    null              ]  // itemvisual [enum] (custom)
     // 201 => [parent::CR_CALLBACK,  'cbHasSpellVisual',       null,                    null              ]  // spellvisual [str] (custom) - unused for now, looks like it's really only shooting/throwing animations for ranged weapons
    );

    protected static array $inputFields   = array(
        'wt'    => [parent::V_CALLBACK, 'cbWeightKeyCheck',                                                  true ], // weight keys
        'wtv'   => [parent::V_RANGE,    [1, 999],                                                            true ], // weight values
        'jc'    => [parent::V_LIST,     [1],                                                                 false], // use jewelcrafter gems for weight calculation
        'gm'    => [parent::V_LIST,     [2, 3, 4],                                                           false], // gem rarity for weight calculation
        'cr'    => [parent::V_LIST,     [[1, 177], 200],                                                     true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]],                   true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                                                 true ], // criteria values - only printable chars, no delimiters
        'upg'   => [parent::V_REGEX,    '/[^\d:]/ui',                                                        true ], // upgrade item ids
        'gb'    => [parent::V_LIST,     [0, 1, 2, 3],                                                        false], // search result grouping
        'na'    => [parent::V_NAME,     false,                                                               false], // name - only printable chars, no delimiter
        'ma'    => [parent::V_EQUAL,    1,                                                                   false], // match any / all filter
        'ub'    => [parent::V_LIST,     [[1, 9], 11],                                                        false], // usable by classId
        'qu'    => [parent::V_RANGE,    [0, 7],                                                              true ], // quality ids
        'ty'    => [parent::V_CALLBACK, 'cbTypeCheck',                                                       true ], // item type - dynamic by current group
        'sl'    => [parent::V_CALLBACK, 'cbSlotCheck',                                                       true ], // item slot - dynamic by current group
        'si'    => [parent::V_LIST,     [-SIDE_HORDE, -SIDE_ALLIANCE, SIDE_ALLIANCE, SIDE_HORDE, SIDE_BOTH], false], // side
        'minle' => [parent::V_RANGE,    [0, 999],                                                            false], // item level min
        'maxle' => [parent::V_RANGE,    [0, 999],                                                            false], // item level max
        'minrl' => [parent::V_RANGE,    [0, MAX_LEVEL],                                                      false], // required level min
        'maxrl' => [parent::V_RANGE,    [0, MAX_LEVEL],                                                      false]  // required level max
    );

    public array $extraOpts = [];                           // score for statWeights
    public array $wtCnd     = [];

    public function createConditionsForWeights() : array
    {
        if (empty($this->values['wt']))
            return [];

        $this->wtCnd = [];
        $select = [];
        $wtSum  = 0;

        foreach ($this->values['wt'] as $k => $v)
        {
            if ($str = Stat::getWeightJson($v))
            {
                $qty = intVal($this->values['wtv'][$k]);

                $select[]      = '(IFNULL(`is`.`'.$str.'`, 0) * '.$qty.')';
                $this->wtCnd[] = ['is.'.$str, 0, '>'];
                $wtSum        += $qty;
            }
        }

        if (count($this->wtCnd) > 1)
            array_unshift($this->wtCnd, DB::OR);
        else if (count($this->wtCnd) == 1)
            $this->wtCnd = $this->wtCnd[0];

        if ($select)
        {
            $this->extraOpts['is']['s'][] = ', IF(`is`.`typeId` IS NULL, 0, ('.implode(' + ', $select).') / '.$wtSum.') AS "weightScore"';
            $this->extraOpts['is']['o'][] = 'weightScore DESC';
            $this->extraOpts['i']['o'][]  = null;           // remove default ordering
        }
        else
            $this->extraOpts['is']['s'][] = ', 0 AS "weightScore"'; // prevent errors

        return $this->wtCnd;
    }

    public function getConditions() : array
    {
        if (!$this->ubFilter)
        {
            $classes = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `weaponTypeMask` AS "0", `armorTypeMask` AS "1" FROM ::classes');
            foreach ($classes as $cId => [$weaponTypeMask, $armorTypeMask])
                $this->ubFilter[$cId] = array(              // preselect misc subclasses
                    ITEM_CLASS_WEAPON => Util::mask2bits($weaponTypeMask) + [ITEM_SUBCLASS_MISC_WEAPON => ITEM_SUBCLASS_MISC_WEAPON],
                    ITEM_CLASS_ARMOR  => Util::mask2bits($armorTypeMask)  + [ITEM_SUBCLASS_MISC_ARMOR  => ITEM_SUBCLASS_MISC_ARMOR]
                );
        }

        return parent::getConditions();
    }

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // weights [list]
        if ($_v['wt'] && $_v['wtv'])
        {
            // gm - gem quality (qualityId)
            // jc - jc-gems included (bool)

            if ($_ = $this->createConditionsForWeights())
                $parts[] = $_;

            foreach ($_v['wt'] as $_)
                $this->fiExtraCols[] = $_;
        }

        // upgrade for [list]
        if ($_v['upg'])
        {
            if ($this->upgrades = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `slot` FROM ::items WHERE `class` IN %in AND `id` IN %in', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR], $_v['upg']))
                $parts[] = ['slot', $this->upgrades];
            else
                $_v['upg'] = null;
        }

        // name
        if ($_v['na'])
        {
            if ($_ = $this->buildMatchLookup([['na', 'nml.nName']]))
                $parts[] = $_;
            else if ($_ = $this->buildLikeLookup([['na', 'name_loc'.Lang::getLocale()->value]]))
                $parts[] = $_;
        }

        // usable-by (not excluded by requiredClass && armor or weapons match mask from ::classes)
        if ($_v['ub'])
        {
            $parts[] = array(
                DB::AND,
                [DB::OR, ['requiredClass', 0], ['requiredClass', $this->list2Mask((array)$_v['ub']), '&']],
                [
                    DB::OR,
                    ['class', [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR], '!'],
                    [DB::AND, ['class', ITEM_CLASS_WEAPON], ['subClassBak', $this->ubFilter[$_v['ub']][ITEM_CLASS_WEAPON]]],
                    [DB::AND, ['class', ITEM_CLASS_ARMOR],  ['subClassBak', $this->ubFilter[$_v['ub']][ITEM_CLASS_ARMOR]]]
                ]
            );
        }

        // quality [list]
        if ($_v['qu'])
            $parts[] = ['quality', $_v['qu']];

        // type [list]
        if ($_v['ty'])
            $parts[] = ['subclass', $_v['ty']];

        // slot [list]
        if ($_v['sl'])
            $parts[] = ['slot', $_v['sl']];

        // side
        if ($_v['si'])
        {
            $parts[] = match ($_v['si'])
            {
                SIDE_BOTH     => [DB::OR,  [['flagsExtra', 0x3, '&'], [0, 3]], ['requiredRace', 0]],
               -SIDE_HORDE    => [DB::OR,  [['flagsExtra', 0x3, '&'], 1],      ['requiredRace', ChrRace::MASK_HORDE, '&']],
               -SIDE_ALLIANCE => [DB::OR,  [['flagsExtra', 0x3, '&'], 2],      ['requiredRace', ChrRace::MASK_ALLIANCE, '&']],
                SIDE_HORDE    => [DB::AND, [['flagsExtra', 0x3, '&'], [0, 1]], [DB::OR, ['requiredRace', 0], ['requiredRace', ChrRace::MASK_HORDE, '&']]],
                SIDE_ALLIANCE => [DB::AND, [['flagsExtra', 0x3, '&'], [0, 2]], [DB::OR, ['requiredRace', 0], ['requiredRace', ChrRace::MASK_ALLIANCE, '&']]],
            };
        }

        // itemLevel min
        if ($_v['minle'])
            $parts[] = ['itemLevel', $_v['minle'], '>='];

        // itemLevel max
        if ($_v['maxle'])
            $parts[] = ['itemLevel', $_v['maxle'], '<='];

        // reqLevel min
        if ($_v['minrl'])
            $parts[] = ['requiredLevel', $_v['minrl'], '>='];

        // reqLevel max
        if ($_v['maxrl'])
            $parts[] = ['requiredLevel', $_v['maxrl'], '<='];

        return $parts;
    }

    protected function cbFactionQuestReward(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            1 => ['src.src4', null, '!'],                   // Yes
            2 => ['src.src4', SIDE_ALLIANCE],               // Alliance
            3 => ['src.src4', SIDE_HORDE],                  // Horde
            4 => ['src.src4', SIDE_BOTH],                   // Both
            5 => ['src.src4', null],                        // No
            default => null
        };
    }

    protected function cbAvailable(int $cr, int $crs, string $crv) : ?array
    {
        if ($this->int2Bool($crs))
            return [['cuFlags', CUSTOM_UNAVAILABLE, '&'], 0, $crs ? null : '!'];

        return null;
    }

    protected function cbHasSockets(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // Meta, Red, Yellow, Blue
            1, 2, 3, 4 => [DB::OR, ['socketColor1', 1 << ($crs - 1)], ['socketColor2', 1 << ($crs - 1)], ['socketColor3', 1 << ($crs - 1)]],
            5 => ['is.nsockets', 0, '!'],                   // Yes
            6 => ['is.nsockets', 0],                        // No
            default => null
        };
    }

    protected function cbFitsGemSlot(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // Meta, Red, Yellow, Blue
            1, 2, 3, 4 => [DB::AND, ['gemEnchantmentId', 0, '!'], ['gemColorMask', 1 << ($crs - 1), '&']],
            5 => ['gemEnchantmentId', 0, '!'],              // Yes
            6 => ['gemEnchantmentId', 0],                   // No
            default => null
        };
    }

    protected function cbGlyphType(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // major, minor
            1, 2 => [DB::AND, ['class', ITEM_CLASS_GLYPH], ['subSubClass', $crs]],
            default => null
        };
    }

    protected function cbHasRandEnchant(int $cr, int $crs, string $crv) : ?array
    {
        $n = preg_replace(parent::PATTERN_NAME, '', $crv);
        if (!$this->tokenizeString($cr, $n))
            return null;

        $where = [];
        foreach ($this->inTokens[$cr] ?? [] as $tok)
            $where[] = ['name_loc%i LIKE %~like~', Lang::getLocale()->value, $tok];
        foreach ($this->exTokens[$cr] ?? [] as $tok)
            $where[] = ['name_loc%i NOT LIKE %~like~', Lang::getLocale()->value, $tok];

        $randIds = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, ABS(`id`) AS `id`, name_loc%i, `name_loc0` FROM ::itemrandomenchant WHERE %and', Lang::getLocale()->value, $where);
        $tplIds  = $randIds ? DB::World()->selectAssoc('SELECT `entry`, `ench` FROM item_enchantment_template WHERE `ench` IN %in', array_column($randIds, 'id')) : [];
        foreach ($tplIds as &$set)
        {
            $z = array_column($randIds, 'id');
            $x = array_search($set['ench'], $z);
            if (isset($randIds[-$z[$x]]))
            {
                $set['entry'] *= -1;
                $set['ench']  *= -1;
            }

            $set['name'] = Util::localizedString($randIds[$set['ench']], 'name', true);
        }

        // only enhance search results if enchantment by name is unique (implies only one enchantment per item is available)
        if (count(array_unique(array_column($randIds, 'name_loc'. Lang::getLocale()->value))) == 1 && reset($randIds)['name_loc'.Lang::getLocale()->value])
            $this->extraOpts['relEnchant'] = $tplIds;
        // try EN fallback
        else if (count(array_unique(array_column($randIds, 'name_loc0'))) == 1)
            $this->extraOpts['relEnchant'] = $tplIds;

        if ($tplIds)
            return ['randomEnchant', array_column($tplIds, 'entry')];
        else
            return [0];                                     // no results aren't really input errors
    }

    protected function cbReqArenaRating(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $this->fiExtraCols[] = $cr;

        $items = [0];
        if ($costs = DB::Aowow()->selectCol('SELECT `id` FROM ::itemextendedcost WHERE `reqPersonalrating` %SQL %i', $crs, $crv))
            $items = DB::World()->selectCol($this->extCostQuery, $costs, $costs);

        return ['id', $items];
    }

    protected function cbClassRaceSpec(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_bool($_))
            return $_ ? [$field, 0, '>'] : [$field, 0];
        else if (is_int($_))
            return [$field, 1 << ($_ - 1), '&'];

        return null;
    }

    protected function cbDamageType(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->checkInput(parent::V_RANGE, [SPELL_SCHOOL_NORMAL, SPELL_SCHOOL_ARCANE], $crs))
            return null;

        return [DB::OR, ['dmgType1', $crs], ['dmgType2', $crs]];
    }

    protected function cbArmorBonus(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_FLOAT) || !$this->int2Op($crs))
            return null;

        $this->fiExtraCols[] = $cr;
        return [DB::AND, ['armordamagemodifier', $crv, $crs], ['class', ITEM_CLASS_ARMOR]];
    }

    protected function cbCraftedByProf(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_bool($_))
            return ['src.src1', null, $_ ? '!' : null];
        else if (is_int($_))
            return ['s.skillLine1', $_];

        return null;
    }

    protected function cbQuestRewardIn(int $cr, int $crs, string $crv) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            return [DB::AND, ['src.src4', null, '!'], ['src.moreZoneId', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return ['src.src4', null, '!'];                 // well, this seems a bit redundant..

        return null;
    }

    protected function cbDropsInZone(int $cr, int $crs, string $crv) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            return [DB::AND, ['src.src2', null, '!'], ['src.moreZoneId', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return ['src.src2', null, '!'];                 // well, this seems a bit redundant..

        return null;
    }

    protected function cbDropsInInstance(int $cr, int $crs, string $crv, int $moreFlag, int $modeBit) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            return [DB::AND, ['src.src2', $modeBit, '&'], ['src.moreMask', $moreFlag, '&'], ['src.moreZoneId', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return [DB::AND, ['src.src2', $modeBit, '&'], ['src.moreMask', $moreFlag, '&']];

        return null;
    }

    protected function cbPurchasableWith(int $cr, int $crs, string $crv) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            $_ = (array)$crs;
        else if ($crs == parent::ENUM_ANY)
            $_ = self::$enums[$cr];
        else
            return null;

        $costs = DB::Aowow()->selectCol(
           'SELECT `id` FROM ::itemextendedcost WHERE `reqItemId1` IN %in OR `reqItemId2` IN %in OR `reqItemId3` IN %in OR `reqItemId4` IN %in OR `reqItemId5` IN %in',
            $_, $_, $_, $_, $_
        );
        if ($items = DB::World()->selectCol($this->extCostQuery, $costs, $costs))
            return ['id', $items];

        return null;
    }

    protected function cbSoldByNPC(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if ($iIds = DB::World()->selectCol('SELECT `item` FROM npc_vendor WHERE `entry` = %i UNION SELECT `item` FROM game_event_npc_vendor v JOIN creature c ON c.`guid` = v.`guid` WHERE c.`id` = %i', $crv, $crv))
            return ['i.id', $iIds];
        else
            return [0];
    }

    protected function cbAvgBuyout(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        foreach (Profiler::getRealms() as $rId => $__)
        {
            // todo: do something sensible..
            // // todo (med): get the avgbuyout into the listview
            // if ($_ = DB::Characters()->selectAssoc('SELECT ii.itemEntry AS ARRAY_KEY, AVG(ah.buyoutprice / ii.count) AS buyout FROM auctionhouse ah JOIN item_instance ii ON ah.itemguid = ii.guid GROUP BY ii.itemEntry HAVING buyout '.$crs.' %f', $c[1]))
                // return ['i.id', array_keys($_)];
            // else
                // return [0];
            return [1];
        }

        return [0];
    }

    protected function cbAvgMoneyContent(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $this->fiExtraCols[] = $cr;
        return [DB::AND, ['flags', ITEM_FLAG_OPENABLE, '&'], ['((minMoneyLoot + maxMoneyLoot) / 2)', $crv, $crs]];
    }

    protected function cbCooldown(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $crv *= 1000;                                       // field supplied in milliseconds

        $this->fiExtraCols[] = $cr;
        $this->extraOpts['is']['s'][] = ', GREATEST(`spellCooldown1`, `spellCooldown2`, `spellCooldown3`, `spellCooldown4`, `spellCooldown5`) AS "cooldown"';

        return [
            DB::OR,
            [DB::AND, ['spellTrigger1', SPELL_TRIGGER_USE], ['spellId1', 0, '!'], ['spellCooldown1', 0, '>'], ['spellCooldown1', $crv, $crs]],
            [DB::AND, ['spellTrigger2', SPELL_TRIGGER_USE], ['spellId2', 0, '!'], ['spellCooldown2', 0, '>'], ['spellCooldown2', $crv, $crs]],
            [DB::AND, ['spellTrigger3', SPELL_TRIGGER_USE], ['spellId3', 0, '!'], ['spellCooldown3', 0, '>'], ['spellCooldown3', $crv, $crs]],
            [DB::AND, ['spellTrigger4', SPELL_TRIGGER_USE], ['spellId4', 0, '!'], ['spellCooldown4', 0, '>'], ['spellCooldown4', $crv, $crs]],
            [DB::AND, ['spellTrigger5', SPELL_TRIGGER_USE], ['spellId5', 0, '!'], ['spellCooldown5', 0, '>'], ['spellCooldown5', $crv, $crs]],
        ];
    }

    protected function cbQuestRelation(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // any
            1 => ['startQuest', 0, '>'],
            // exclude horde only
            2 => [DB::AND, ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], SIDE_HORDE]],
            // exclude alliance only
            3 => [DB::AND, ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], SIDE_ALLIANCE]],
            // both
            4 => [DB::AND, ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 0]],
            // none
            5 => ['startQuest', 0],
            default => null
        };
    }

    protected function cbFieldHasVal(int $cr, int $crs, string $crv, string $field, mixed $val) : ?array
    {
        if ($this->int2Bool($crs))
            return [$field, $val, $crs ? null : '!'];

        return null;
    }

    protected function cbObtainedBy(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if ($this->int2Bool($crs))
            return ['src.src'.$field, null, $crs ? '!' : null];

        return null;
    }

    protected function cbHasItemVisual(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (in_array($crs, self::$enums[$cr]))              // limit to weapons, as other items have visuals assigned, that will not be displayed by client
            return [DB::AND, ['class', ITEM_CLASS_WEAPON], [DB::OR, ['iv.visualEffectId1', $crs], ['iv.visualEffectId2', $crs], ['iv.visualEffectId3', $crs], ['iv.visualEffectId4', $crs], ['iv.visualEffectId5', $crs]]];
        else if ($crs == parent::ENUM_ANY)
            return [DB::AND, ['class', ITEM_CLASS_WEAPON], ['itemVisualId', 0, '!']];
        else if ($crs == parent::ENUM_NONE)
            return [DB::AND, ['class', ITEM_CLASS_WEAPON], ['itemVisualId', 0]];

        return null;
    }

    protected function cbPvpPurchasable(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        $costs = DB::Aowow()->selectCol('SELECT `id` FROM ::itemextendedcost WHERE %n > 0', $field);
        if ($items = DB::World()->selectCol($this->extCostQuery, $costs, $costs))
            return ['id', $items, $crs ? null : '!'];

        return null;
    }

    protected function cbDisenchantsInto(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (!in_array($crs, self::$enums[$cr]))
            return null;

        $refResults = [];
        $newRefs = DB::World()->selectCol('SELECT `entry` FROM %n WHERE `item` = %i AND `reference` = 0', Loot::REFERENCE, $crs);
        while ($newRefs)
        {
            $refResults += $newRefs;
            $newRefs     = DB::World()->selectCol('SELECT `entry` FROM %n WHERE `reference` IN %in', Loot::REFERENCE, $newRefs);
        }

        $lootIds = DB::World()->selectCol('SELECT `entry` FROM %n', Loot::DISENCHANT, 'WHERE %if', $refResults, '`reference` IN %in OR', $refResults, '%end (`reference` = 0 AND `item` = %i)', $crs);

        return $lootIds ? ['disenchantId', $lootIds] : [0];
    }

    protected function cbObjectiveOfQuest(int $cr, int $crs, string $crv) : ?array
    {
        $where = match ($crs)
        {
            // Yes / No
            1, 5    => [1],
            // Alliance
            2       => [['`reqRaceMask` & %i', ChrRace::MASK_ALLIANCE], ['(`reqRaceMask` & %i) = 0', ChrRace::MASK_HORDE]],
            // Horde
            3       => [['`reqRaceMask` & %i', ChrRace::MASK_HORDE],    ['(`reqRaceMask` & %i) = 0', ChrRace::MASK_ALLIANCE]],
            // Both
            4       => [[DB::OR,  [['`reqRaceMask` = 0'], [DB::AND, [['`reqRaceMask` & %i', ChrRace::MASK_ALLIANCE], ['`reqRaceMask` & %i', ChrRace::MASK_HORDE]]]]]],
            default => null
        };

        if (!$where)
            return [0];

        $itemIds = DB::Aowow()->selectCol(
           'SELECT `reqItemId1` FROM ::quests WHERE %and UNION SELECT `reqItemId2` FROM ::quests WHERE %and UNION
            SELECT `reqItemId3` FROM ::quests WHERE %and UNION SELECT `reqItemId4` FROM ::quests WHERE %and UNION
            SELECT `reqItemId5` FROM ::quests WHERE %and UNION SELECT `reqItemId6` FROM ::quests WHERE %and',
            $where, $where, $where, $where, $where, $where
        );

        if ($itemIds)
            return ['id', $itemIds, $crs == 5 ? '!' : null];

        return [0];
    }

    protected function cbReagentForAbility(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if ($_ === null)
            return null;

        $ids    = [];
        $spells = DB::Aowow()->selectAssoc(                      // todo (med): hmm, selecting all using SpellEntry would exhaust 128MB of memory :x .. see, that we only select the fields that are really needed
           'SELECT `reagent1`,      `reagent2`,      `reagent3`,      `reagent4`,      `reagent5`,      `reagent6`,      `reagent7`,      `reagent8`,
                   `reagentCount1`, `reagentCount2`, `reagentCount3`, `reagentCount4`, `reagentCount5`, `reagentCount6`, `reagentCount7`, `reagentCount8`
            FROM   ::spell
            WHERE  `skillLine1` IN %in',
            is_bool($_) ? array_filter(self::$enums[99], "is_numeric") : $_
        );
        foreach ($spells as $spell)
            for ($i = 1; $i < 9; $i++)
                if ($spell['reagent'.$i] > 0 && $spell['reagentCount'.$i] > 0)
                    $ids[] = $spell['reagent'.$i];

        if (empty($ids))
            return [0];
        else if ($_)
            return ['id', $ids];
        else
            return ['id', $ids, '!'];
    }

    protected function cbSource(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_int($_))                                     // specific
            return ['src.src'.$_, null, '!'];
        else if ($_)                                        // any
        {
            $foo = [DB::OR];
            foreach (self::$enums[$cr] as $bar)
                if (is_int($bar))
                    $foo[] = ['src.src'.$bar, null, '!'];

            return $foo;
        }
        else                                                // none
            return ['src.typeId', null];
    }

    protected function cbTypeCheck(string &$v) : bool
    {
        if (!$this->parentCats)
            return false;

        if (!Util::checkNumeric($v, NUM_CAST_INT))
            return false;

        $c = $this->parentCats;

        if (isset($c[2]) && is_array(Lang::item('cat', $c[0], 1, $c[1])))
            $catList = Lang::item('cat', $c[0], 1, $c[1], 1, $c[2]);
        else if (isset($c[1]) && is_array(Lang::item('cat', $c[0])))
            $catList = Lang::item('cat', $c[0], 1, $c[1]);
        else
            $catList = Lang::item('cat', $c[0]);

        // consumables - always
        if ($c[0] == ITEM_CLASS_CONSUMABLE)
            return in_array($v, array_keys(Lang::item('cat', 0, 1)));
        // weapons - only if parent
        else if ($c[0] == ITEM_CLASS_WEAPON && !isset($c[1]))
            return in_array($v, array_keys(Lang::spell('weaponSubClass')));
        // armor - only if parent
        else if ($c[0] == ITEM_CLASS_ARMOR && !isset($c[1]))
            return in_array($v, array_keys(Lang::item('cat', ITEM_CLASS_ARMOR, 1)));
        // uh ... other stuff...
        else if (!isset($c[1]) && in_array($c[0], [ITEM_CLASS_CONTAINER, ITEM_CLASS_GEM, ITEM_CLASS_TRADEGOOD, ITEM_CLASS_RECIPE, ITEM_CLASS_MISC]))
            return in_array($v, array_keys($catList[1]));

        return false;
    }

    protected function cbSlotCheck(string &$v) : bool
    {
        if (!Util::checkNumeric($v, NUM_CAST_INT))
            return false;

        // todo (low): limit to concrete slots
        $sl = array_keys(Lang::item('inventoryType'));
        $c  = $this->parentCats;

        // no selection
        if (!isset($c[0]))
            return in_array($v, $sl);

        // consumables - any; perm / temp item enhancements
        else if ($c[0] == ITEM_CLASS_CONSUMABLE && (!isset($c[1]) || in_array($c[1], [-3, 6])))
            return in_array($v, $sl);

        // weapons - always
        else if ($c[0] == ITEM_CLASS_WEAPON)
            return in_array($v, $sl);

        // armor - any; any armor
        else if ($c[0] == ITEM_CLASS_ARMOR && (!isset($c[1]) || in_array($c[1], [ITEM_SUBCLASS_CLOTH_ARMOR, ITEM_SUBCLASS_LEATHER_ARMOR, ITEM_SUBCLASS_MAIL_ARMOR, ITEM_SUBCLASS_PLATE_ARMOR])))
            return in_array($v, $sl);

        return false;
    }

    protected function cbWeightKeyCheck(string &$v) : bool
    {
        if (preg_match('/\W/i', $v))
            return false;

        return Stat::getIndexFrom(Stat::IDX_FILTER_CR_ID, $v) > 0;
    }
}

?>
