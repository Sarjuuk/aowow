<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellFilter extends Filter
{
    private const /* int */ MAX_SPELL_EFFECT = 167;
    private const /* int */ MAX_SPELL_AURA   = 316;

    public const /* array */ ATTRIBUTES_CR = array(         // attrFieldId => [attrBit => cr, ...]; if cr < 0 ? filter is negated
        0 => array(
            SPELL_ATTR0_REQ_AMMO                      =>  48,
            SPELL_ATTR0_ON_NEXT_SWING                 =>  49,
            SPELL_ATTR0_PASSIVE                       =>  50,
            SPELL_ATTR0_HIDDEN_CLIENTSIDE             =>  51,
            SPELL_ATTR0_HIDE_IN_COMBAT_LOG            =>  84,
            SPELL_ATTR0_ON_NEXT_SWING_2               =>  52,
            SPELL_ATTR0_DAYTIME_ONLY                  =>  53,
            SPELL_ATTR0_NIGHT_ONLY                    =>  54,
            SPELL_ATTR0_INDOORS_ONLY                  =>  55,
            SPELL_ATTR0_OUTDOORS_ONLY                 =>  56,
            SPELL_ATTR0_NOT_SHAPESHIFT                => -31,
            SPELL_ATTR0_ONLY_STEALTHED                =>  38,
            SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION      =>  58,
            SPELL_ATTR0_STOP_ATTACK_TARGET            =>  59,
            SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK  =>  60,
            SPELL_ATTR0_CASTABLE_WHILE_DEAD           =>  61,
            SPELL_ATTR0_CASTABLE_WHILE_MOUNTED        =>  62,
            SPELL_ATTR0_DISABLED_WHILE_ACTIVE         =>  63,
            SPELL_ATTR0_NEGATIVE_1                    =>  69,
            SPELL_ATTR0_CASTABLE_WHILE_SITTING        =>  64,
            SPELL_ATTR0_CANT_USED_IN_COMBAT           => -33,
            SPELL_ATTR0_UNAFFECTED_BY_INVULNERABILITY =>  46,
            SPELL_ATTR0_CANT_CANCEL                   =>  57
        ),
        1 => array(
            SPELL_ATTR1_DRAIN_ALL_POWER             => 65,
            SPELL_ATTR1_CHANNELED_1                 => 27,  // general filter
            SPELL_ATTR1_NOT_BREAK_STEALTH           => 68,
            SPELL_ATTR1_CHANNELED_2                 => 66,  // attributes filter
            SPELL_ATTR1_CANT_BE_REFLECTED           => 67,  // WH - 69: all effects are harmful points here
            SPELL_ATTR1_CANT_TARGET_IN_COMBAT       => 70,
            SPELL_ATTR1_NO_THREAT                   => 71,
            SPELL_ATTR1_IS_PICKPOCKET               => 72,
            SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY    => 73,
            SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE => 47,
            SPELL_ATTR1_IS_FISHING                  => 74
        ),
        2 => array(
            SPELL_ATTR2_CANT_TARGET_TAPPED        =>  75,
            SPELL_ATTR2_PRESERVE_ENCHANT_IN_ARENA =>  76,
            SPELL_ATTR2_NOT_NEED_SHAPESHIFT       =>  77,
            SPELL_ATTR2_CANT_CRIT                 => -34,
            SPELL_ATTR2_FOOD_BUFF                 =>  78
        ),
        3 => array(
            SPELL_ATTR3_ONLY_TARGET_PLAYERS =>  79,
            SPELL_ATTR3_MAIN_HAND           =>  80,
            SPELL_ATTR3_BATTLEGROUND        =>  43,
            SPELL_ATTR3_NO_INITIAL_AGGRO    =>  81,
            SPELL_ATTR3_DEATH_PERSISTENT    =>  36,
            SPELL_ATTR3_IGNORE_HIT_RESULT   => -35,
            SPELL_ATTR3_REQ_WAND            =>  82,         // unused attribute
            SPELL_ATTR3_REQ_OFFHAND         =>  83
        ),
        4 => array(
            SPELL_ATTR4_FADES_WHILE_LOGGED_OUT =>  85,
            SPELL_ATTR4_NOT_STEALABLE          => -39,
            SPELL_ATTR4_NOT_USABLE_IN_ARENA    => -44,
            SPELL_ATTR4_USABLE_IN_ARENA        =>  44
        ),
        5 => array(
            SPELL_ATTR5_USABLE_WHILE_STUNNED    => 42,
            SPELL_ATTR5_SINGLE_TARGET_SPELL     => 86,
            SPELL_ATTR5_START_PERIODIC_AT_APPLY => 87,
            SPELL_ATTR5_USABLE_WHILE_FEARED     => 89,
            SPELL_ATTR5_USABLE_WHILE_CONFUSED   => 88
        ),
        6 => array(
            SPELL_ATTR6_ONLY_IN_ARENA        => 90,         // unused attribute
            SPELL_ATTR6_NOT_IN_RAID_INSTANCE => 91
        ),
        7 => array(
            SPELL_ATTR7_DISABLE_AURA_WHILE_DEAD => 92,      // aka Paladin Aura
            SPELL_ATTR7_SUMMON_PLAYER_TOTEM     => 93
        )
    );

    protected string $type = 'spells';

    protected static array $enums = array(
        9 => array(                                         // sources index
            1  => true,                                     // Any
            2  => false,                                    // None
            3  =>  SRC_CRAFTED,
            4  =>  SRC_DROP,
            6  =>  SRC_QUEST,
            7  =>  SRC_VENDOR,
            8  =>  SRC_TRAINER,
            9  =>  SRC_DISCOVERY,
            10 =>  SRC_TALENT
        ),
        22 => array(
            1 => true,                                      // Weapons
            2 => true,                                      // Armor
            3 => true,                                      // Armor Proficiencies
            4 => true,                                      // Armor Specializations
            5 => true                                       // Languages
        ),
        40 => array(                                        // damage class index
            1 => 0,                                         // none
            2 => 1,                                         // magic
            3 => 2,                                         // melee
            4 => 3                                          // ranged
        ),
        45 => array(                                        // power type index (in the future will be PowerType.db2/powerTypeEnum values, hardcoded in 335a)
          // 1 => ??,                                       // burning embers
          // 2 => ??,                                       // chi
          // 3 => ??,                                       // demonic fury
             4 => POWER_ENERGY,                             // energy
             5 => POWER_FOCUS,                              // focus
             6 => POWER_HEALTH,                             // health
          // 7 => ??,                                       // holy power
             8 => POWER_MANA,                               // mana
             9 => POWER_RAGE,                               // rage
            10 => POWER_RUNE,                               // runes
            11 => POWER_RUNIC_POWER,                        // runic power
         // 12 => ??,                                       // shadow orbs
         // 13 => ??,                                       // soul shard
            14 => POWER_HAPPINESS,                          // happiness        v custom v
            15 => -1,                                       // ammo
            16 => -41,                                      // pyrite
            17 => -61,                                      // steam pressure
            18 => -101,                                     // heat
            19 => -121,                                     // ooze
            20 => -141,                                     // blood power
            21 => -142                                      // wrath
        )
    );

    protected static array $genericFilter = array(
         1  => [parent::CR_CALLBACK,  'cbCost',                                                                                   ], // costAbs [op] [int]
         2  => [parent::CR_NUMERIC,   'powerCostPercent', NUM_CAST_INT                                                            ], // prcntbasemanarequired
         3  => [parent::CR_BOOLEAN,   'spellFocusObject'                                                                          ], // requiresnearbyobject
         4  => [parent::CR_NUMERIC,   'trainingcost',     NUM_CAST_INT                                                            ], // trainingcost
         5  => [parent::CR_BOOLEAN,   'reqSpellId'                                                                                ], // requiresprofspec
         8  => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_SCREENSHOT                                                   ], // hasscreenshots
         9  => [parent::CR_CALLBACK,  'cbSource',                                                                                 ], // source [enum]
        10  => [parent::CR_FLAG,      'cuFlags',          SPELL_CU_FIRST_RANK                                                     ], // firstrank
        11  => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_COMMENT                                                      ], // hascomments
        12  => [parent::CR_FLAG,      'cuFlags',          SPELL_CU_LAST_RANK                                                      ], // lastrank
        13  => [parent::CR_NUMERIC,   'rankNo',           NUM_CAST_INT                                                            ], // rankno
        14  => [parent::CR_NUMERIC,   'id',               NUM_CAST_INT,                            true                           ], // id
        15  => [parent::CR_STRING,    'ic.name',                                                                                  ], // icon
        17  => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_VIDEO                                                        ], // hasvideos
        19  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION                                    ], // scaling
        20  => [parent::CR_CALLBACK,  'cbReagents',                                                                               ], // has Reagents [yn]
        22  => [parent::CR_CALLBACK,  'cbProficiency',   null,                                     null                           ], // proficiencytype [proficiencytype]
        25  => [parent::CR_BOOLEAN,   'skillLevelYellow'                                                                          ], // rewardsskillups
        26  => [parent::CR_NUMSTRING, 'startRecoveryCategory', NUM_CAST_INT                                                       ], // gcd-cat [str]
        27  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CHANNELED_1,                 true                           ], // channeled [yn]
        28  => [parent::CR_NUMERIC,   'castTime',         NUM_CAST_FLOAT                                                          ], // casttime [num]
        29  => [parent::CR_CALLBACK,  'cbAuraNames',                                                                              ], // appliesaura [effectauranames]
        31  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes0',                           SPELL_ATTR0_NOT_SHAPESHIFT     ], // usablewhenshapeshifted [yn]
        33  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes0',                           SPELL_ATTR0_CANT_USED_IN_COMBAT], // combatcastable [yn]
        34  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes2',                           SPELL_ATTR2_CANT_CRIT          ], // chancetocrit [yn]
        35  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes3',                           SPELL_ATTR3_IGNORE_HIT_RESULT  ], // chancetomiss [yn]
        36  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_DEATH_PERSISTENT                                            ], // persiststhroughdeath [yn]
        38  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_ONLY_STEALTHED                                              ], // requiresstealth [yn]
        39  => [parent::CR_FLAG,      'attributes4',      SPELL_ATTR4_NOT_STEALABLE                                               ], // spellstealable [yn]
        40  => [parent::CR_ENUM,      'damageClass'                                                                               ], // damagetype [damagetype]
        41  => [parent::CR_FLAG,      'stanceMask',       (1 << (22 - 1))                                                         ], // requiresmetamorphosis [yn]
        42  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_STUNNED                                        ], // usablewhenstunned [yn]
        43  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_BATTLEGROUND                                                ], // usableinbgs [yn]
        44  => [parent::CR_FLAG,      'attributes4',      SPELL_ATTR4_USABLE_IN_ARENA                                             ], // usableinarenas [yn]
        45  => [parent::CR_ENUM,      'powerType'                                                                                 ], // resourcetype [resourcetype]
        46  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_UNAFFECTED_BY_INVULNERABILITY                               ], // disregardimmunity [yn]
        47  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE                                 ], // disregardschoolimmunity [yn]
        48  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_REQ_AMMO                                                    ], // reqrangedweapon [yn]
        49  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_ON_NEXT_SWING                                               ], // onnextswingplayers [yn]
        50  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_PASSIVE                                                     ], // passivespell [yn]
        51  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_DONT_DISPLAY_IN_AURA_BAR                                    ], // hiddenaura [yn]
        52  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_ON_NEXT_SWING_2                                             ], // onnextswingnpcs [yn]
        53  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_DAYTIME_ONLY                                                ], // daytimeonly [yn]
        54  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_NIGHT_ONLY                                                  ], // nighttimeonly [yn]
        55  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_INDOORS_ONLY                                                ], // indoorsonly [yn]
        56  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_OUTDOORS_ONLY                                               ], // outdoorsonly [yn]
        57  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CANT_CANCEL                                                 ], // uncancellableaura [yn]
        58  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION                                    ], // damagedependsonlevel [yn]
        59  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_STOP_ATTACK_TARGET                                          ], // stopsautoattack [yn]
        60  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK                                ], // cannotavoid [yn]
        61  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_DEAD                                         ], // usabledead [yn]
        62  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_MOUNTED                                      ], // usablemounted [yn]
        63  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_DISABLED_WHILE_ACTIVE                                       ], // delayedrecoverystarttime [yn]
        64  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_SITTING                                      ], // usablesitting [yn]
        65  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_DRAIN_ALL_POWER                                             ], // usesallpower [yn]
        66  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CHANNELED_2                                                 ], // channeled [yn]
        67  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CANT_BE_REFLECTED                                           ], // cannotreflect [yn]
        68  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_NOT_BREAK_STEALTH                                           ], // usablestealthed [yn]
        69  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_NEGATIVE_1                                                  ], // harmful [yn]  -  WH interprets attributes1 0x80 as "all effects are harmful", but it really is CANT_BE_REFLECTED. So here is an approximation.
        70  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CANT_TARGET_IN_COMBAT                                       ], // targetnotincombat [yn]
        71  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_NO_THREAT                                                   ], // nothreat [yn]
        72  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_IS_PICKPOCKET                                               ], // pickpocket [yn]
        73  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY                                    ], // dispelauraonimmunity [yn]
        74  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_IS_FISHING                                                  ], // reqfishingpole [yn]
        75  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_CANT_TARGET_TAPPED                                          ], // requntappedtarget [yn]
        76  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_PRESERVE_ENCHANT_IN_ARENA                                   ], // targetownitem [yn
        77  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_NOT_NEED_SHAPESHIFT                                         ], // doesntreqshapeshift [yn]
        78  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_FOOD_BUFF                                                   ], // foodbuff [yn]
        79  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_ONLY_TARGET_PLAYERS                                         ], // targetonlyplayer [yn]
        80  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_MAIN_HAND                                                   ], // reqmainhand [yn]
        81  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_NO_INITIAL_AGGRO                                            ], // doesntengagetarget [yn]
        82  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_REQ_WAND                                                    ], // reqwand [yn]
        83  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_REQ_OFFHAND                                                 ], // reqoffhand [yn]
        84  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_HIDE_IN_COMBAT_LOG                                          ], // nolog [yn]
        85  => [parent::CR_FLAG,      'attributes4',      SPELL_ATTR4_FADES_WHILE_LOGGED_OUT                                      ], // auratickswhileloggedout [yn]
        86  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_SINGLE_TARGET_SPELL                                         ], // onlyaffectsonetarget [yn]
        87  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_START_PERIODIC_AT_APPLY                                     ], // startstickingatapplication [yn]
        88  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_CONFUSED                                       ], // usableconfused [yn]
        89  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_FEARED                                         ], // usablefeared [yn]
        90  => [parent::CR_FLAG,      'attributes6',      SPELL_ATTR6_ONLY_IN_ARENA                                               ], // onlyarena [yn]
        91  => [parent::CR_FLAG,      'attributes6',      SPELL_ATTR6_NOT_IN_RAID_INSTANCE                                        ], // notinraid [yn]
        92  => [parent::CR_FLAG,      'attributes7',      SPELL_ATTR7_DISABLE_AURA_WHILE_DEAD                                     ], // paladinaura [yn]
        93  => [parent::CR_FLAG,      'attributes7',      SPELL_ATTR7_SUMMON_PLAYER_TOTEM                                         ], // totemspell [yn]
        95  => [parent::CR_CALLBACK,  'cbBandageSpell'                                                                            ], // bandagespell [yn]  -  was that an attribute at one point?
        96  => [parent::CR_STAFFFLAG, 'attributes0'                                                                               ], // flags1 [flags]
        97  => [parent::CR_STAFFFLAG, 'attributes1'                                                                               ], // flags2 [flags]
        98  => [parent::CR_STAFFFLAG, 'attributes2'                                                                               ], // flags3 [flags]
        99  => [parent::CR_STAFFFLAG, 'attributes3'                                                                               ], // flags4 [flags]
        100 => [parent::CR_STAFFFLAG, 'attributes4'                                                                               ], // flags5 [flags]
        101 => [parent::CR_STAFFFLAG, 'attributes5'                                                                               ], // flags6 [flags]
        102 => [parent::CR_STAFFFLAG, 'attributes6'                                                                               ], // flags7 [flags]
        103 => [parent::CR_STAFFFLAG, 'attributes7'                                                                               ], // flags8 [flags]
        104 => [parent::CR_STAFFFLAG, 'targets'                                                                                   ], // flags9 [flags]
        105 => [parent::CR_STAFFFLAG, 'stanceMaskNot'                                                                             ], // flags10 [flags]
        106 => [parent::CR_STAFFFLAG, 'spellFamilyFlags1'                                                                         ], // flags11 [flags]
        107 => [parent::CR_STAFFFLAG, 'spellFamilyFlags2'                                                                         ], // flags12 [flags]
        108 => [parent::CR_STAFFFLAG, 'spellFamilyFlags3'                                                                         ], // flags13 [flags]
        109 => [parent::CR_CALLBACK,  'cbEffectNames'                                                                             ], // effecttype [effecttype]
     // 110 => [parent::CR_NYI_PH,    null,               null,                                    null                           ], // scalingap [yn]  // unreasonably complex for now
     // 111 => [parent::CR_NYI_PH,    null,               null,                                    null                           ], // scalingsp [yn]  // unreasonably complex for now
        114 => [parent::CR_CALLBACK,  'cbReqFaction'                                                                              ], // requiresfaction [side]
        116 => [parent::CR_BOOLEAN,   'startRecoveryTime'                                                                         ], // onGlobalCooldown [yn]
        117 => [parent::CR_NUMERIC,   'sr.rangeMaxHostile', NUM_CAST_INT                                                          ], // maximumRange_stc [num]
        118 => [parent::CR_NUMERIC,   'sr.rangeMinHostile', NUM_CAST_INT                                                          ], // minimumRange_stc [num]
        120 => [parent::CR_CALLBACK,  'cbModifiesSpell'                                                                           ], // modifiesSpell_filter [str]
     // 121 => [parent::CR_NYI_PH,    null                                                                                        ], // inMyFavorites_stc [yn]
        129 => [parent::CR_CALLBACK,  'cbGivePower'                                                                               ], // givesResourceType_stc [resourcetype]
        200 => [parent::CR_CALLBACK,  'cbSecToMsec',        'recoveryTime'                                                        ], // cooldown [num] (custom)
        201 => [parent::CR_CALLBACK,  'cbSecToMsec',        'duration'                                                            ]  // duration [num] (custom)
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE,    [1, 201],                                          true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]], true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                               true ], // criteria values - only printable chars, no delimiters
        'na'    => [parent::V_NAME,     false,                                             false], // name / text - only printable chars, no delimiter
        'ex'    => [parent::V_EQUAL,    'on',                                              false], // extended name search
        'ma'    => [parent::V_EQUAL,    1,                                                 false], // match any / all filter
        'minle' => [parent::V_RANGE,    [0, 99],                                           false], // spell level min
        'maxle' => [parent::V_RANGE,    [0, 99],                                           false], // spell level max
        'minrs' => [parent::V_RANGE,    [0, 999],                                          false], // required skill level min
        'maxrs' => [parent::V_RANGE,    [0, 999],                                          false], // required skill level max
        'ra'    => [parent::V_LIST,     [[1, 8], 10, 11],                                  false], // races
        'cl'    => [parent::V_CALLBACK, 'cbClasses',                                       true ], // classes
        'gl'    => [parent::V_CALLBACK, 'cbGlyphs',                                        true ], // glyph type
        'sc'    => [parent::V_RANGE,    [0, 6],                                            true ], // magic schools
        'dt'    => [parent::V_LIST,     [[1, 6], 9],                                       false], // dispel types
        'me'    => [parent::V_RANGE,    [1, 31],                                           false]  // mechanics
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // string (extended)
        if ($_v['na'])
        {
            $f = [['na', ['nml.nName', 'nml.nBuff', 'nml.nDescription']]];
            if ($_v['ex'] != 'on')
                $f = [['na', 'nml.nName']];

            if ($_ = $this->buildMatchLookup($f))
                $parts[] = $_;
            else
            {
                $f = [['na', 'name_loc'.Lang::getLocale()->value], ['na', 'buff_loc'.Lang::getLocale()->value], ['na', 'description_loc'.Lang::getLocale()->value]];
                if ($_v['ex'] != 'on')
                    $f = [$f[0]];

                if ($_ = $this->buildLikeLookup($f))
                    $parts[] = $_;
            }
        }

        // spellLevel min                                   todo (low): talentSpells (typeCat -2) commonly have spellLevel 1 (and talentLevel >1) -> query is inaccurate
        if ($_v['minle'])
            $parts[] = ['spellLevel', $_v['minle'], '>='];

        // spellLevel max
        if ($_v['maxle'])
            $parts[] = ['spellLevel', $_v['maxle'], '<='];

        // skillLevel min
        if ($_v['minrs'])
            $parts[] = ['learnedAt', $_v['minrs'], '>='];

        // skillLevel max
        if ($_v['maxrs'])
            $parts[] = ['learnedAt', $_v['maxrs'], '<='];

        // race
        if ($_v['ra'])
            $parts[] = [DB::AND, [['reqRaceMask', ChrRace::MASK_ALL, '&'], ChrRace::MASK_ALL, '!'], ['reqRaceMask', $this->list2Mask([$_v['ra']]), '&']];

        // class [list]
        if ($_v['cl'])
            $parts[] = ['reqClassMask', $this->list2Mask($_v['cl']), '&'];

        // school [list]
        if ($_v['sc'])
            $parts[] = ['schoolMask', $this->list2Mask($_v['sc'], true), '&'];

        // glyph type [list]                                wonky, admittedly, but consult SPELL_CU_* in defines and it makes sense
        if ($_v['gl'])
            $parts[] = ['cuFlags', ($this->list2Mask($_v['gl']) << 6), '&'];

        // dispel type
        if ($_v['dt'])
            $parts[] = ['dispelType', $_v['dt']];

        // mechanic
        if ($_v['me'])
            $parts[] = [DB::OR, ['mechanic', $_v['me']], ['effect1Mechanic', $_v['me']], ['effect2Mechanic', $_v['me']], ['effect3Mechanic', $_v['me']]];

        return $parts;
    }

    protected function cbClasses(string &$val) : bool
    {
        if (!$this->parentCats || !in_array($this->parentCats[0], [-13, -2, 7]))
            return false;

        if (!Util::checkNumeric($val, NUM_CAST_INT))
            return false;

        $type  = parent::V_LIST;
        $valid = ChrClass::fromMask(ChrClass::MASK_ALL);

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbGlyphs(string &$val) : bool
    {
        if (!$this->parentCats || $this->parentCats[0] != -13)
            return false;

        if (!Util::checkNumeric($val, NUM_CAST_INT))
            return false;

        $type  = parent::V_LIST;
        $valid = [1, 2];

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbCost(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        return [DB::OR,
            [DB::AND, ['powerType', [POWER_RAGE, POWER_RUNIC_POWER]], ['powerCost', (10 * $crv), $crs]],
            [DB::AND, ['powerType', [POWER_RAGE, POWER_RUNIC_POWER], '!'], ['powerCost', $crv, $crs]]
        ];
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

        return null;
    }

    protected function cbReagents(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [DB::OR, ['reagent1', 0, '>'], ['reagent2', 0, '>'], ['reagent3', 0, '>'], ['reagent4', 0, '>'], ['reagent5', 0, '>'], ['reagent6', 0, '>'], ['reagent7', 0, '>'], ['reagent8', 0, '>']];
        else
            return [DB::AND, ['reagent1', 0], ['reagent2', 0], ['reagent3', 0], ['reagent4', 0], ['reagent5', 0], ['reagent6', 0], ['reagent7', 0], ['reagent8', 0]];
    }

    protected function cbAuraNames(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->checkInput(parent::V_RANGE, [1, self::MAX_SPELL_AURA], $crs))
            return null;

        return [DB::OR, ['effect1AuraId', $crs], ['effect2AuraId', $crs], ['effect3AuraId', $crs]];
    }

    protected function cbEffectNames(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->checkInput(parent::V_RANGE, [1, self::MAX_SPELL_EFFECT], $crs))
            return null;

        return [DB::OR, ['effect1Id', $crs], ['effect2Id', $crs], ['effect3Id', $crs]];
    }

    protected function cbInverseFlag(int $cr, int $crs, string $crv, string $field, int $flag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [[$field, $flag, '&'], 0];
        else
            return [$field, $flag, '&'];
    }

    protected function cbSpellstealable(int $cr, int $crs, string $crv, string $field, int $flag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [DB::AND, [[$field, $flag, '&'], 0], ['dispelType', SPELL_DAMAGE_CLASS_MAGIC]];
        else
            return [DB::OR, [$field, $flag, '&'], ['dispelType', SPELL_DAMAGE_CLASS_MAGIC, '!']];
    }

    protected function cbReqFaction(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // yes
            1 => ['reqRaceMask', 0, '!'],
            // alliance
            2 => [DB::AND, [['reqRaceMask', ChrRace::MASK_HORDE, '&'], 0], ['reqRaceMask', ChrRace::MASK_ALLIANCE, '&']],
            // horde
            3 => [DB::AND, [['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], 0], ['reqRaceMask', ChrRace::MASK_HORDE, '&']],
            // both
            4 => [DB::AND, ['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ['reqRaceMask', ChrRace::MASK_HORDE, '&']],
            // no
            5 => ['reqRaceMask', 0],
            default => null
        };
    }

    /* unused - for reference: attribute flag or item class mask */
    protected function cbEquippedWeapon(int $cr, int $crs, string $crv, int $mask, bool $useInvType) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        $field = $useInvType ? 'equippedItemInventoryTypeMask' : 'equippedItemSubClassMask';

        if ($crs)
            return [DB::AND, ['equippedItemClass', ITEM_CLASS_WEAPON], [$field, $mask, '&']];
        else
            return [DB::OR, ['equippedItemClass', ITEM_CLASS_WEAPON, '!'], [[$field, $mask, '&'], 0]];
    }

    /* unused - for reference: attribute flag or cooldown time constraint */
    protected function cbUsableInArena(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return  [DB::AND,
                        [['attributes4', SPELL_ATTR4_NOT_USABLE_IN_ARENA, '&'], 0],
                        [DB::OR, ['recoveryTime', 10 * MINUTE * 1000, '<='], ['attributes4', SPELL_ATTR4_USABLE_IN_ARENA, '&']]
                    ];
        else
            return  [DB::OR,
                        ['attributes4', SPELL_ATTR4_NOT_USABLE_IN_ARENA, '&'],
                        [DB::AND, ['recoveryTime', 10 * MINUTE * 1000, '>'], [['attributes4', SPELL_ATTR4_USABLE_IN_ARENA, '&'], 0]]
                    ];
    }

    protected function cbBandageSpell(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)                                           // match exact, not as flag
            return [DB::AND, ['attributes1', SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2 | SPELL_ATTR1_CHANNEL_TRACK_TARGET], ['effect1ImplicitTargetA', 21]];
        else
            return [DB::OR, ['attributes1', SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2 | SPELL_ATTR1_CHANNEL_TRACK_TARGET, '!'], ['effect1ImplicitTargetA', 21, '!']];
    }

    protected function cbProficiency(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $skill1Ids  = [];
        $skill2Mask = 0x0;

        switch($crs)
        {
            case 1:                                         // Weapons
                foreach (Game::$skillLineMask[-3] as $bit => $_)
                    $skill2Mask |= (1 << $bit);
                $skill1Ids = DB::Aowow()->selectCol('SELECT `id` FROM ::skillline WHERE `typeCat` = 6');
                break;
            case 2:                                         // Armor (Proficiencies + Specializations: so for us it's the same)
            case 3:                                         // Armor Proficiencies
                $skill1Ids = DB::Aowow()->selectCol('SELECT `id` FROM ::skillline WHERE `typeCat` = 8');
                break;
            case 4:                                         // Armor Specializations
                return [0];                                 // 4.x+ feature where using purely one type of armor increases your primary stat
            case 5:                                         // Languages
                $skill1Ids = DB::Aowow()->selectCol('SELECT `id` FROM ::skillline WHERE `typeCat` = 10');
                break;
        }

        if (!$skill1Ids)
            return [0];

        $cnd = ['skillLine1', $skill1Ids];
        if ($skill2Mask)
            $cnd = [DB::OR, $cnd, [DB::AND, ['skillLine1', -3], ['skillLine2OrMask', $skill2Mask, '&']]];

        return $cnd;
    }

    protected function cbGivePower(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[45][$crs]))
            return null;

        // wh only checks against SPELL_EFFECT_ENERGIZE as this effect got updated to handle any resource in the modern wow client
        // 335a has a separate effect for runes and we ignore hardcoded combo points
        $pt = self::$enums[45][$crs];
        if ($pt == POWER_RUNE)
            return [DB::OR, ['effect1Id', SPELL_EFFECT_ACTIVATE_RUNE], ['effect2Id', SPELL_EFFECT_ACTIVATE_RUNE], ['effect3Id', SPELL_EFFECT_ACTIVATE_RUNE]];
        else if ($pt >= 0)
            return [DB::OR,
                [DB::AND, ['effect1Id', SPELL_EFFECT_ENERGIZE], ['effect1MiscValue', $pt]],
                [DB::AND, ['effect2Id', SPELL_EFFECT_ENERGIZE], ['effect2MiscValue', $pt]],
                [DB::AND, ['effect3Id', SPELL_EFFECT_ENERGIZE], ['effect3MiscValue', $pt]]
            ];

        return [0];                                         // dont even try resolving powerDisplayCost or health shenanigans
    }

    // prompted for sec, stored as msec
    protected function cbSecToMsec(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_FLOAT) || !$this->int2Op($crs))
            return null;

        return [$field, $crv * 1000, $crs];
    }

    protected function cbModifiesSpell(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if (!($refSpell = DB::Aowow()->selectRow('SELECT `spellFamilyId` AS "0", `spellFamilyFlags1` AS "1", `spellFamilyFlags2` AS "2", `spellFamilyFlags3` AS "3" FROM ::spell WHERE `id` = %i', $crv)))
            return [0];

        [$fam, $m1, $m2, $m3] = $refSpell;

        return array(
            DB::OR,
            [DB::AND, ['s.effect1AuraId', SpellEntry::MOD_AURAS], ['spellFamilyId', $fam], [DB::OR, ['s.effect1SpellClassMaskA', $m1, '&'], ['s.effect1SpellClassMaskB', $m2, '&'], ['s.effect1SpellClassMaskC', $m3, '&']]],
            [DB::AND, ['s.effect2AuraId', SpellEntry::MOD_AURAS], ['spellFamilyId', $fam], [DB::OR, ['s.effect2SpellClassMaskA', $m1, '&'], ['s.effect2SpellClassMaskB', $m2, '&'], ['s.effect2SpellClassMaskC', $m3, '&']]],
            [DB::AND, ['s.effect3AuraId', SpellEntry::MOD_AURAS], ['spellFamilyId', $fam], [DB::OR, ['s.effect3SpellClassMaskA', $m1, '&'], ['s.effect3SpellClassMaskB', $m2, '&'], ['s.effect3SpellClassMaskC', $m3, '&']]]
        );
    }
}

?>
