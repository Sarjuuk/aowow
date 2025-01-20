<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class Stat                                         // based on g_statToJson
{
    public const HEALTH                = 1;
    public const MANA                  = 2;                 // note: mana is on idx 0 from 5.0 onwards; idx 2 is empty
    public const AGILITY               = 3;
    public const STRENGTH              = 4;
    public const INTELLECT             = 5;
    public const SPIRIT                = 6;
    public const STAMINA               = 7;
    public const ENERGY                = 8;
    public const RAGE                  = 9;
    public const FOCUS                 = 10;
    public const RUNIC_POWER           = 11;
    public const DEFENSE_RTG           = 12;
    public const DODGE_RTG             = 13;
    public const PARRY_RTG             = 14;
    public const BLOCK_RTG             = 15;
    public const MELEE_HIT_RTG         = 16;
    public const RANGED_HIT_RTG        = 17;
    public const SPELL_HIT_RTG         = 18;
    public const MELEE_CRIT_RTG        = 19;
    public const RANGED_CRIT_RTG       = 20;
    public const SPELL_CRIT_RTG        = 21;
    public const MELEE_HIT_TAKEN_RTG   = 22;
    public const RANGED_HIT_TAKEN_RTG  = 23;
    public const SPELL_HIT_TAKEN_RTG   = 24;
    public const MELEE_CRIT_TAKEN_RTG  = 25;
    public const RANGED_CRIT_TAKEN_RTG = 26;
    public const SPELL_CRIT_TAKEN_RTG  = 27;
    public const MELEE_HASTE_RTG       = 28;
    public const RANGED_HASTE_RTG      = 29;
    public const SPELL_HASTE_RTG       = 30;
    public const HIT_RTG               = 31;
    public const CRIT_RTG              = 32;
    public const HIT_TAKEN_RTG         = 33;
    public const CRIT_TAKEN_RTG        = 34;
    public const RESILIENCE_RTG        = 35;
    public const HASTE_RTG             = 36;
    public const EXPERTISE_RTG         = 37;
    public const ATTACK_POWER          = 38;
    public const RANGED_ATTACK_POWER   = 39;
    public const FERAL_ATTACK_POWER    = 40;                // unused in wow-3.3.5
    public const HEALING_SPELL_POWER   = 41;                // deprecated
    public const DAMAGE_SPELL_POWER    = 42;                // deprecated
    public const MANA_REGENERATION     = 43;
    public const ARMOR_PENETRATION_RTG = 44;
    public const SPELL_POWER           = 45;
    public const HEALTH_REGENERATION   = 46;                // no differentiation between IC (item mods / spells) and OOC (from spirit) for Profiler
    public const SPELL_PENETRATION     = 47;
    public const BLOCK                 = 48;
 // public const MASTERY_RTG           = 49;                // not in wow-3.3.5
    public const ARMOR                 = 50;
    public const FIRE_RESISTANCE       = 51;
    public const FROST_RESISTANCE      = 52;
    public const HOLY_RESISTANCE       = 53;
    public const SHADOW_RESISTANCE     = 54;
    public const NATURE_RESISTANCE     = 55;
    public const ARCANE_RESISTANCE     = 56;
    public const FIRE_SPELL_POWER      = 57;
    public const FROST_SPELL_POWER     = 58;
    public const HOLY_SPELL_POWER      = 59;
    public const SHADOW_SPELL_POWER    = 60;
    public const NATURE_SPELL_POWER    = 61;
    public const ARCANE_SPELL_POWER    = 62;
    // v for stats lookups v
    public const WEAPON_DAMAGE         = 200;               // +weapon dmg from enchantments
    public const WEAPON_DAMAGE_TYPE    = 201;
    public const WEAPON_DAMAGE_MIN     = 202;
    public const WEAPON_DAMAGE_MAX     = 203;
    public const WEAPON_SPEED          = 204;
    public const WEAPON_DPS            = 205;               // also +weapon dps from enchantments (rockbiter)
    public const MELEE_DAMAGE_MIN      = 206;
    public const MELEE_DAMAGE_MAX      = 207;
    public const MELEE_SPEED           = 208;
    public const MELEE_DPS             = 209;
    public const RANGED_DAMAGE_MIN     = 210;
    public const RANGED_DAMAGE_MAX     = 211;
    public const RANGED_SPEED          = 212;
    public const RANGED_DPS            = 213;
    public const EXTRA_SOCKETS         = 214;
    public const ARMOR_BONUS           = 215;
    public const MELEE_ATTACK_POWER    = 216;
    // v only seen in profiler v
    public const EXPERTISE             = 500;
    public const ARMOR_PENETRATION_PCT = 501;
    public const MELEE_HIT_PCT         = 502;
    public const MELEE_CRIT_PCT        = 503;
    public const MELEE_HASTE_PCT       = 504;
    public const RANGED_HIT_PCT        = 505;
    public const RANGED_CRIT_PCT       = 506;
    public const RANGED_HASTE_PCT      = 507;
    public const SPELL_HIT_PCT         = 508;
    public const SPELL_CRIT_PCT        = 509;
    public const SPELL_HASTE_PCT       = 510;
    public const MANA_REGENERATION_SPI = 511;               // mp5 from spirit, excluding other sources
    public const MANA_REGENERATION_OC  = 512;               // mp5 out of combat, excluding other sources
    public const MANA_REGENERATION_IC  = 513;               // mp5 in combat, excluding other sources
    public const ARMOR_TOTAL           = 514;               // ARMOR + ARMOR_BONUS meta category .. can be skipped here like pet* stats?
    public const DEFENSE               = 515;
    public const DODGE_PCT             = 516;
    public const PARRY_PCT             = 517;
    public const BLOCK_PCT             = 518;
    public const RESILIENCE_PCT        = 519;

    public const FLAG_NONE        = 0x00;
    public const FLAG_ITEM        = 0x01;                   // found on items
    public const FLAG_SERVERSIDE  = 0x02;                   // not included in g_statToJson
    public const FLAG_PROFILER    = 0x04;                   // stat used in profiler only
    public const FLAG_LVL_SCALING = 0x08;                   // rating effectivenes scales with level
    public const FLAG_FLOAT_VALUE = 0x10;                   // not an int

    public const IDX_JSON_STR      = 0;
    public const IDX_ITEM_MOD      = 1;                     // granted by items
    public const IDX_COMBAT_RATING = 2;                     // granted by spells + enchantments
    public const IDX_FILTER_CR_ID  = 3;                     // also references listview cols
    public const IDX_FLAGS         = 4;

    private static /* array */ $data = array(
        self::HEALTH                => ['health',           ITEM_MOD_HEALTH,                   null,                  115, self::FLAG_ITEM],
        self::MANA                  => ['mana',             ITEM_MOD_MANA,                     null,                  116, self::FLAG_ITEM],
        self::AGILITY               => ['agi',              ITEM_MOD_AGILITY,                  null,                   21, self::FLAG_ITEM],
        self::STRENGTH              => ['str',              ITEM_MOD_STRENGTH,                 null,                   20, self::FLAG_ITEM],
        self::INTELLECT             => ['int',              ITEM_MOD_INTELLECT,                null,                   23, self::FLAG_ITEM],
        self::SPIRIT                => ['spi',              ITEM_MOD_SPIRIT,                   null,                   24, self::FLAG_ITEM],
        self::STAMINA               => ['sta',              ITEM_MOD_STAMINA,                  null,                   22, self::FLAG_ITEM],
        self::ENERGY                => ['energy',           null,                              null,                 null, self::FLAG_ITEM],
        self::RAGE                  => ['rage',             null,                              null,                 null, self::FLAG_ITEM],
        self::FOCUS                 => ['focus',            null,                              null,                 null, self::FLAG_ITEM],
        self::RUNIC_POWER           => ['runic',            null,                              null,                 null, self::FLAG_ITEM | self::FLAG_SERVERSIDE],
        self::DEFENSE_RTG           => ['defrtng',          ITEM_MOD_DEFENSE_SKILL_RATING,     CR_DEFENSE_SKILL,       42, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::DODGE_RTG             => ['dodgertng',        ITEM_MOD_DODGE_RATING,             CR_DODGE,               45, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::PARRY_RTG             => ['parryrtng',        ITEM_MOD_PARRY_RATING,             CR_PARRY,               46, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::BLOCK_RTG             => ['blockrtng',        ITEM_MOD_BLOCK_RATING,             CR_BLOCK,               44, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::MELEE_HIT_RTG         => ['mlehitrtng',       ITEM_MOD_HIT_MELEE_RATING,         CR_HIT_MELEE,           95, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::RANGED_HIT_RTG        => ['rgdhitrtng',       ITEM_MOD_HIT_RANGED_RATING,        CR_HIT_RANGED,          39, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::SPELL_HIT_RTG         => ['splhitrtng',       ITEM_MOD_HIT_SPELL_RATING,         CR_HIT_SPELL,           48, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::MELEE_CRIT_RTG        => ['mlecritstrkrtng',  ITEM_MOD_CRIT_MELEE_RATING,        CR_CRIT_MELEE,          84, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::RANGED_CRIT_RTG       => ['rgdcritstrkrtng',  ITEM_MOD_CRIT_RANGED_RATING,       CR_CRIT_RANGED,         40, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::SPELL_CRIT_RTG        => ['splcritstrkrtng',  ITEM_MOD_CRIT_SPELL_RATING,        CR_CRIT_SPELL,          49, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::MELEE_HIT_TAKEN_RTG   => ['_mlehitrtng',      ITEM_MOD_HIT_TAKEN_MELEE_RATING,   CR_HIT_TAKEN_MELEE,   null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::RANGED_HIT_TAKEN_RTG  => ['_rgdhitrtng',      ITEM_MOD_HIT_TAKEN_RANGED_RATING,  CR_HIT_TAKEN_RANGED,  null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::SPELL_HIT_TAKEN_RTG   => ['_splhitrtng',      ITEM_MOD_HIT_TAKEN_SPELL_RATING,   CR_HIT_TAKEN_SPELL,   null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::MELEE_CRIT_TAKEN_RTG  => ['_mlecritstrkrtng', ITEM_MOD_CRIT_TAKEN_MELEE_RATING,  CR_CRIT_TAKEN_MELEE,  null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::RANGED_CRIT_TAKEN_RTG => ['_rgdcritstrkrtng', ITEM_MOD_CRIT_TAKEN_RANGED_RATING, CR_CRIT_TAKEN_RANGED, null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::SPELL_CRIT_TAKEN_RTG  => ['_splcritstrkrtng', ITEM_MOD_CRIT_TAKEN_SPELL_RATING,  CR_CRIT_TAKEN_SPELL,  null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::MELEE_HASTE_RTG       => ['mlehastertng',     ITEM_MOD_HASTE_MELEE_RATING,       CR_HASTE_MELEE,         78, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::RANGED_HASTE_RTG      => ['rgdhastertng',     ITEM_MOD_HASTE_RANGED_RATING,      CR_HASTE_RANGED,       101, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::SPELL_HASTE_RTG       => ['splhastertng',     ITEM_MOD_HASTE_SPELL_RATING,       CR_HASTE_SPELL,        102, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::HIT_RTG               => ['hitrtng',          ITEM_MOD_HIT_RATING,              -CR_HIT_MELEE,          119, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::CRIT_RTG              => ['critstrkrtng',     ITEM_MOD_CRIT_RATING,             -CR_CRIT_MELEE,          96, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::HIT_TAKEN_RTG         => ['_hitrtng',         ITEM_MOD_HIT_TAKEN_RATING,        -CR_HIT_TAKEN_MELEE,   null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::CRIT_TAKEN_RTG        => ['_critstrkrtng',    ITEM_MOD_CRIT_TAKEN_RATING,       -CR_CRIT_TAKEN_MELEE,  null, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::RESILIENCE_RTG        => ['resirtng',         ITEM_MOD_RESILIENCE_RATING,       -CR_CRIT_TAKEN_MELEE,    79, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::HASTE_RTG             => ['hastertng',        ITEM_MOD_HASTE_RATING,            -CR_HASTE_MELEE,        103, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::EXPERTISE_RTG         => ['exprtng',          ITEM_MOD_EXPERTISE_RATING,         CR_EXPERTISE,          117, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::ATTACK_POWER          => ['atkpwr',           ITEM_MOD_ATTACK_POWER,             null,                   77, self::FLAG_ITEM],
        self::RANGED_ATTACK_POWER   => ['rgdatkpwr',        ITEM_MOD_RANGED_ATTACK_POWER,      null,                   38, self::FLAG_ITEM],
        self::FERAL_ATTACK_POWER    => ['feratkpwr',        ITEM_MOD_FERAL_ATTACK_POWER,       null,                   97, self::FLAG_ITEM],
        self::HEALING_SPELL_POWER   => ['splheal',          ITEM_MOD_SPELL_HEALING_DONE,       null,                   50, self::FLAG_ITEM],
        self::DAMAGE_SPELL_POWER    => ['spldmg',           ITEM_MOD_SPELL_DAMAGE_DONE,        null,                   51, self::FLAG_ITEM],
        self::MANA_REGENERATION     => ['manargn',          ITEM_MOD_MANA_REGENERATION,        null,                   61, self::FLAG_ITEM],
        self::ARMOR_PENETRATION_RTG => ['armorpenrtng',     ITEM_MOD_ARMOR_PENETRATION_RATING, CR_ARMOR_PENETRATION,  114, self::FLAG_ITEM | self::FLAG_LVL_SCALING],
        self::SPELL_POWER           => ['splpwr',           ITEM_MOD_SPELL_POWER,              null,                  123, self::FLAG_ITEM],
        self::HEALTH_REGENERATION   => ['healthrgn',        ITEM_MOD_HEALTH_REGEN,             null,                   60, self::FLAG_ITEM],
        self::SPELL_PENETRATION     => ['splpen',           ITEM_MOD_SPELL_PENETRATION,        null,                   94, self::FLAG_ITEM],
        self::BLOCK                 => ['block',            ITEM_MOD_BLOCK_VALUE,              null,                   43, self::FLAG_ITEM],
     // self::MASTERY_RTG           => ['mastrtng',         null,                              CR_MASTERY,           null, self::FLAG_NONE],
        self::ARMOR                 => ['armor',            null,                              null,                   41, self::FLAG_ITEM],
        self::FIRE_RESISTANCE       => ['firres',           null,                              null,                   26, self::FLAG_ITEM],
        self::FROST_RESISTANCE      => ['frores',           null,                              null,                   28, self::FLAG_ITEM],
        self::HOLY_RESISTANCE       => ['holres',           null,                              null,                   30, self::FLAG_ITEM],
        self::SHADOW_RESISTANCE     => ['shares',           null,                              null,                   29, self::FLAG_ITEM],
        self::NATURE_RESISTANCE     => ['natres',           null,                              null,                   27, self::FLAG_ITEM],
        self::ARCANE_RESISTANCE     => ['arcres',           null,                              null,                   25, self::FLAG_ITEM],
        self::FIRE_SPELL_POWER      => ['firsplpwr',        null,                              null,                   53, self::FLAG_ITEM],
        self::FROST_SPELL_POWER     => ['frosplpwr',        null,                              null,                   54, self::FLAG_ITEM],
        self::HOLY_SPELL_POWER      => ['holsplpwr',        null,                              null,                   55, self::FLAG_ITEM],
        self::SHADOW_SPELL_POWER    => ['shasplpwr',        null,                              null,                   57, self::FLAG_ITEM],
        self::NATURE_SPELL_POWER    => ['natsplpwr',        null,                              null,                   56, self::FLAG_ITEM],
        self::ARCANE_SPELL_POWER    => ['arcsplpwr',        null,                              null,                   52, self::FLAG_ITEM],
        // v not part of g_statToJson v
        self::WEAPON_DAMAGE         => ['dmg',              null,                              null,                 null, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE],
        self::WEAPON_DAMAGE_TYPE    => ['damagetype',       null,                              null,                   35, self::FLAG_SERVERSIDE],
        self::WEAPON_DAMAGE_MIN     => ['dmgmin1',          null,                              null,                   33, self::FLAG_SERVERSIDE],
        self::WEAPON_DAMAGE_MAX     => ['dmgmax1',          null,                              null,                   34, self::FLAG_SERVERSIDE],
        self::WEAPON_SPEED          => ['speed',            null,                              null,                   36, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE],
        self::WEAPON_DPS            => ['dps',              null,                              null,                   32, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE],
        self::MELEE_DAMAGE_MIN      => ['mledmgmin',        null,                              null,                  135, self::FLAG_SERVERSIDE],
        self::MELEE_DAMAGE_MAX      => ['mledmgmax',        null,                              null,                  136, self::FLAG_SERVERSIDE],
        self::MELEE_SPEED           => ['mlespeed',         null,                              null,                  137, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE],
        self::MELEE_DPS             => ['mledps',           null,                              null,                  134, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE | self::FLAG_PROFILER],
        self::RANGED_DAMAGE_MIN     => ['rgddmgmin',        null,                              null,                  139, self::FLAG_SERVERSIDE],
        self::RANGED_DAMAGE_MAX     => ['rgddmgmax',        null,                              null,                  140, self::FLAG_SERVERSIDE],
        self::RANGED_SPEED          => ['rgdspeed',         null,                              null,                  141, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE],
        self::RANGED_DPS            => ['rgddps',           null,                              null,                  138, self::FLAG_SERVERSIDE | self::FLAG_FLOAT_VALUE | self::FLAG_PROFILER],
        self::EXTRA_SOCKETS         => ['nsockets',         null,                              null,                  100, self::FLAG_SERVERSIDE],
        self::ARMOR_BONUS           => ['armorbonus',       null,                              null,                  109, self::FLAG_SERVERSIDE],
        self::MELEE_ATTACK_POWER    => ['mleatkpwr',        null,                              null,                   37, self::FLAG_SERVERSIDE | self::FLAG_PROFILER],
        // v Profiler only v
        self::EXPERTISE             => ['exp',              null,                              null,                 null, self::FLAG_PROFILER],
        self::ARMOR_PENETRATION_PCT => ['armorpenpct',      null,                              null,                 null, self::FLAG_PROFILER],
        self::MELEE_HIT_PCT         => ['mlehitpct',        null,                              null,                 null, self::FLAG_PROFILER],
        self::MELEE_CRIT_PCT        => ['mlecritstrkpct',   null,                              null,                 null, self::FLAG_PROFILER],
        self::MELEE_HASTE_PCT       => ['mlehastepct',      null,                              null,                 null, self::FLAG_PROFILER],
        self::RANGED_HIT_PCT        => ['rgdhitpct',        null,                              null,                 null, self::FLAG_PROFILER],
        self::RANGED_CRIT_PCT       => ['rgdcritstrkpct',   null,                              null,                 null, self::FLAG_PROFILER],
        self::RANGED_HASTE_PCT      => ['rgdhastepct',      null,                              null,                 null, self::FLAG_PROFILER],
        self::SPELL_HIT_PCT         => ['splhitpct',        null,                              null,                 null, self::FLAG_PROFILER],
        self::SPELL_CRIT_PCT        => ['splcritstrkpct',   null,                              null,                 null, self::FLAG_PROFILER],
        self::SPELL_HASTE_PCT       => ['splhastepct',      null,                              null,                 null, self::FLAG_PROFILER],
        self::MANA_REGENERATION_SPI => ['spimanargn',       null,                              null,                 null, self::FLAG_PROFILER],
        self::MANA_REGENERATION_OC  => ['oocmanargn',       null,                              null,                 null, self::FLAG_PROFILER],
        self::MANA_REGENERATION_IC  => ['icmanargn',        null,                              null,                 null, self::FLAG_PROFILER],
        self::ARMOR_TOTAL           => ['fullarmor',        null,                              null,                 null, self::FLAG_PROFILER],
        self::DEFENSE               => ['def',              null,                              null,                 null, self::FLAG_PROFILER],
        self::DODGE_PCT             => ['dodgepct',         null,                              null,                 null, self::FLAG_PROFILER],
        self::PARRY_PCT             => ['parrypct',         null,                              null,                 null, self::FLAG_PROFILER],
        self::BLOCK_PCT             => ['blockpct',         null,                              null,                 null, self::FLAG_PROFILER],
        self::RESILIENCE_PCT        => ['resipct',          null,                              null,                 null, self::FLAG_PROFILER]
    );

    /* Combat Rating needed for 1% effect at level 60 (Note: Shaman, Druid, Paladin and Death Knight have a /1.3 modifier on HASTE not set here)
     * Data taken from gtcombatratings.dbc for level 60 [idx % 100 = 59]
     * Corrections from gtoctclasscombatratingscalar.dbc with Warrior as base [idx = ratingId + 1]
     * Maybe create this data during setup, but then again it will never change for 3.3.5a
     */
    private static $crPerPctPoint = array(
        CR_WEAPON_SKILL          =>  2.50, CR_DEFENSE_SKILL        =>  1.50, CR_DODGE               => 13.80, CR_PARRY           => 13.80, CR_BLOCK             =>  5.00,
        CR_HIT_MELEE             => 10.00, CR_HIT_RANGED           => 10.00, CR_HIT_SPELL           =>  8.00, CR_CRIT_MELEE      => 14.00, CR_CRIT_RANGED       => 14.00,
        CR_CRIT_SPELL            => 14.00, CR_HIT_TAKEN_MELEE      => 10.00, CR_HIT_TAKEN_RANGED    => 10.00, CR_HIT_TAKEN_SPELL =>  8.00, CR_CRIT_TAKEN_MELEE  => 28.75,
        CR_CRIT_TAKEN_RANGED     => 28.75, CR_CRIT_TAKEN_SPELL     => 28.75, CR_HASTE_MELEE         => 10.00, CR_HASTE_RANGED    => 10.00, CR_HASTE_SPELL       => 10.00,
        CR_WEAPON_SKILL_MAINHAND =>  2.50, CR_WEAPON_SKILL_OFFHAND =>  2.50, CR_WEAPON_SKILL_RANGED =>  2.50, CR_EXPERTISE       =>  2.50, CR_ARMOR_PENETRATION =>  4.69512 / 1.1,
    );

    public static function isLevelIndependent(int $stat) : bool
    {
        if (!isset(self::$data[$stat]))
            return false;

        return !(self::$data[$stat][self::IDX_FLAGS] & self::FLAG_LVL_SCALING);
    }

    public static function getRatingPctFactor(int $stat) : float
    {
        // Note: this makes the weapon skill related combat ratings inaccessible. Is this relevant..?
        if (!isset(self::$data[$stat]) || self::$data[$stat][self::IDX_COMBAT_RATING] === null)
            return 0.0;

        // note: originally any CRIT_TAKEN_RTG stat was set to 0 in favor of RESILIENCE_RTG
        //       we keep the dbc value and just link RESILIENCE_RTG to CRIT_TAKEN_RTG
        // note2: the js expects some stats to be directly mapped to a combat rating that doesn't exist
        //        picked the next best one in this case and denoted it with a negative value in the $data dump
        return self::$crPerPctPoint[abs(self::$data[$stat][self::IDX_COMBAT_RATING])];
    }

    public static function getJsonString(int $stat) : string
    {
        if (!isset(self::$data[$stat]))
            return '';

        return self::$data[$stat][self::IDX_JSON_STR];
    }

    public static function getFilterCriteriumId(int $stat) : ?int
    {
        if (!isset(self::$data[$stat]))
            return null;

        return self::$data[$stat][self::IDX_FILTER_CR_ID];
    }

    public static function getFlags(int $stat) : int
    {
        if (!isset(self::$data[$stat]))
            return 0;

        return self::$data[$stat][self::IDX_FLAGS];
    }

    public static function getJsonStringsFor(int $flags = Stat::FLAG_NONE) : array
    {
        $x = [];
        foreach (self::$data as $k => [$s, , , , $f])
            if ($s && (!$flags || $flags & $f))
                $x[$k] = $s;

        return $x;
    }

    public static function getCombatRatingsFor(int $flags = Stat::FLAG_NONE) : array
    {
        $x = [];
        foreach (self::$data as $k => [, , $c, , $f])
            if ($c > 0 && (!$flags || $flags & $f))
                $x[$k] = $c;

        return $x;
    }

    public static function getFilterCriteriumIdFor(int $flags = Stat::FLAG_NONE) : array
    {
        $x = [];
        foreach (self::$data as $k => [, , , $cr, $f])
            if ($cr && (!$flags || $flags & $f))
                $x[$k] = $cr;

        return $x;
    }

    public static function getIndexFrom(int $idx, string $match) : int
    {
        $i = array_search($match, array_column(self::$data, $idx));
        if ($i === false)
            return 0;

        return array_keys(self::$data)[$i];
    }
}

class StatsContainer
{
    private $store = [];

    private $relSpells       = [];
    private $relEnchantments = [];

    public function __construct(array $relSpells = [], array $relEnchantments = [])
    {
        if ($relSpells)
            $this->relSpells = $relSpells;

        if ($relEnchantments)
            $this->relEnchantments = $relEnchantments;
    }

    /**********/
    /* Source */
    /**********/

    public function fromItem(array $item) : self
    {
        if (!$item)
            return $this;

        // convert itemMods to stats
        for ($i = 1; $i <= 10; $i++)
        {
            $mod = $item['statType'.$i];
            $val = $item['statValue'.$i];
            if (!$mod || !$val)
                continue;

            if ($idx = Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $mod))
                Util::arraySumByKey($this->store, [$idx => $val]);
        }

        // also occurs as seperate field (gets summed in calculation but not in tooltip)
        if ($item['tplBlock'])
            Util::arraySumByKey($this->store, [Stat::BLOCK => $item['tplBlock']]);

        // convert spells to stats
        for ($i = 1; $i <= 5; $i++)
            if (in_array($item['spellTrigger'.$i], [SPELL_TRIGGER_EQUIP, SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]))
                if ($relS = $this->relS($item['spellId'.$i]))
                    $this->fromSpell($relS);

        // for ITEM_CLASS_GEM get stats from enchantment
        if ($relE = $this->relE($item['gemEnchantmentId']))
            $this->fromEnchantment($relE);

        return $this;
    }

    public function fromEnchantment(array $enchantment) : self
    {
        if (!$enchantment)
            return $this;

        for ($i = 1; $i <= 3; $i++)
        {
            $type   = $enchantment['type'.$i];
            $object = $enchantment['object'.$i];
            $amount = $enchantment['amount'.$i];            // !CAUTION! scaling enchantments are initialized with "0" as amount. 0 is a valid amount!

            if ($type == ENCHANTMENT_TYPE_EQUIP_SPELL && ($relS = $this->relS($object)))
                $this->fromSpell($relS);
            else
                foreach ($this->convertEnchantment($type, $object) as $idx)
                    Util::arraySumByKey($this->store, [$idx => $amount]);
        }

        return $this;
    }

    public function fromSpell(array $spell) : self
    {
        if (!$spell)
            return $this;

        // if spells grant an equal, non-zero amount of SPELL_DAMAGE and SPELL_HEALING, combine them to SPELL_POWER
        // this probably does not affect enchantments
        $tmpStore = [];

        for ($i = 1; $i <= 3; $i++)
        {
            $eff  = $spell['effect'.$i.'Id'];
            $aura = $spell['effect'.$i.'AuraId'];
            $mVal = $spell['effect'.$i.'MiscValue'];
            $amt  = $spell['effect'.$i.'BasePoints'] + $spell['effect'.$i.'DieSides'];

            if (in_array($eff, SpellList::EFFECTS_ENCHANTMENT) && ($relE = $this->relE($mVal)))
                $this->fromEnchantment($relE);
            else
                foreach ($this->convertSpellEffect($aura, $mVal, $amt) as $idx)
                    Util::arraySumByKey($tmpStore, [$idx => $amt]);
        }

        if (!empty($tmpStore[Stat::HEALING_SPELL_POWER]) && !empty($tmpStore[Stat::DAMAGE_SPELL_POWER]) && $tmpStore[Stat::HEALING_SPELL_POWER] == $tmpStore[Stat::DAMAGE_SPELL_POWER])
        {
            Util::arraySumByKey($tmpStore, [Stat::SPELL_POWER => $tmpStore[Stat::HEALING_SPELL_POWER]]);
            unset($tmpStore[Stat::HEALING_SPELL_POWER]);
            unset($tmpStore[Stat::DAMAGE_SPELL_POWER]);
        }

        Util::arraySumByKey($this->store, $tmpStore);

        return $this;
    }

    public function fromJson(array &$json, bool $pruneFromSrc = false) : self
    {
        if (!$json)
            return $this;

        foreach (Stat::getJsonStringsFor() as $idx => $key)
        {
            if (isset($json[$key]))                         // 0 is a valid amount!
            {
                $float = Stat::getFlags($idx) & Stat::FLAG_FLOAT_VALUE;

                if (Util::checkNumeric($json[$key], $float ? NUM_CAST_FLOAT : NUM_CAST_INT))
                    Util::arraySumByKey($this->store, [$idx => $json[$key]]);
            }

            if ($pruneFromSrc)
                unset($json[$key]);
        }

        return $this;
    }

    public function fromDB(int $type, int $typeId, int $fieldFlags = Stat::FLAG_NONE) : self
    {
        foreach (DB::Aowow()->selectRow('SELECT (?#) FROM ?_item_stats WHERE `type` = ?d AND `typeId` = ?d', Stat::getJsonStringsFor($fieldFlags ?: (Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE)), $type, $typeId) as $key => $amt)
        {
            if ($amt === null)
                continue;

            $idx   = Stat::getIndexFrom(Stat::IDX_JSON_STR, $key);
            $float = Stat::getFlags($idx) & Stat::FLAG_FLOAT_VALUE;

            if (Util::checkNumeric($amt, $float ? NUM_CAST_FLOAT : NUM_CAST_INT))
                Util::arraySumByKey($this->store, [$idx => $amt]);
        }

        return $this;
    }

    public function fromContainer(StatsContainer ...$container) : self
    {
        foreach ($container as $c)
            Util::arraySumByKey($this->store, $c->toRaw());

        return $this;
    }


    /**********/
    /* Output */
    /**********/

    public function toJson(int $outFlags = Stat::FLAG_NONE) : array
    {
        $out = [];
        foreach ($this->store as $stat => $amt)
            if (!$outFlags || (Stat::getFlags($stat) & $outFlags))
                $out[Stat::getJsonString($stat)] = $amt;

        return $out;
    }

    public function toRaw() : array
    {
        return $this->store;
    }


    /****************/
    /* internal use */
    /****************/

    private function relE(int $enchantmentId) : array
    {
        if ($enchantmentId <= 0 || !isset($this->relEnchantments[$enchantmentId]))
            return [];

        return $this->relEnchantments[$enchantmentId];
    }

    private function relS(int $spellId) : array
    {
        if ($spellId <= 0 || !isset($this->relSpells[$spellId]))
            return [];

        return $this->relSpells[$spellId];
    }

    private static function convertEnchantment(int $type, int $object) : array
    {
        switch ($type)
        {
            case ENCHANTMENT_TYPE_PRISMATIC_SOCKET:
                return [Stat::EXTRA_SOCKETS];
            case ENCHANTMENT_TYPE_DAMAGE:
                return [Stat::WEAPON_DAMAGE];
            case ENCHANTMENT_TYPE_TOTEM:
                return [Stat::WEAPON_DPS];
            case ENCHANTMENT_TYPE_STAT:                     // ITEM_MOD_*
                return [Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $object)];
            case ENCHANTMENT_TYPE_RESISTANCE:
                if ($object == SPELL_SCHOOL_NORMAL)
                    return [Stat::ARMOR];
                if ($object == SPELL_SCHOOL_HOLY)
                    return [Stat::HOLY_RESISTANCE];
                if ($object == SPELL_SCHOOL_FIRE)
                    return [Stat::FIRE_RESISTANCE];
                if ($object == SPELL_SCHOOL_NATURE)
                    return [Stat::NATURE_RESISTANCE];
                if ($object == SPELL_SCHOOL_FROST)
                    return [Stat::FROST_RESISTANCE];
                if ($object == SPELL_SCHOOL_SHADOW)
                    return [Stat::SHADOW_RESISTANCE];
                if ($object == SPELL_SCHOOL_ARCANE)
                    return [Stat::ARCANE_RESISTANCE];

                return [];
            case ENCHANTMENT_TYPE_EQUIP_SPELL:              // handled one level up
            case ENCHANTMENT_TYPE_COMBAT_SPELL:             // we do not average effects, so skip
            case ENCHANTMENT_TYPE_USE_SPELL:
            default:
                return [];
        }

        return [];
    }

    public static function convertCombatRating(int $mask) : array
    {
        $hitMask = (1 << CR_HIT_MELEE) | (1 << CR_HIT_RANGED) | (1 << CR_HIT_SPELL);
        if (($mask & $hitMask) == $hitMask)
            return [Stat::HIT_RTG];                         // generic hit rating

        $critMask = (1 << CR_CRIT_MELEE) | (1 << CR_CRIT_RANGED) | (1 << CR_CRIT_SPELL);
        if (($mask & $critMask) == $critMask)
            return [Stat::CRIT_RTG];                        // generic crit rating


        $takentMask = (1 << CR_CRIT_TAKEN_MELEE) | (1 << CR_CRIT_TAKEN_RANGED) | (1 << CR_CRIT_TAKEN_SPELL);
        if (($mask & $takentMask) == $takentMask)
            return [Stat::RESILIENCE_RTG];                  // resilience

        $result = [];                                       // there really shouldn't be multiple ratings in that mask besides the cases above, but who knows..
        foreach (Stat::getCombatRatingsFor() as $stat => $cr)
            if ($mask & (1 << $cr))
                $result[] = $stat;

        return $result;
    }

    private static function convertSpellEffect(int $auraId, int $miscValue, int &$amount) : array
    {
        $stats = [];

        switch ($auraId)
        {
            case SPELL_AURA_MOD_STAT:
                if ($miscValue < 0)                        // all stats
                    return [Stat::AGILITY, Stat::STRENGTH, Stat::INTELLECT, Stat::SPIRIT, Stat::STAMINA];
                if ($miscValue == STAT_STRENGTH)           // one stat
                    return [Stat::STRENGTH];
                if ($miscValue == STAT_AGILITY)
                    return [Stat::AGILITY];
                if ($miscValue == STAT_STAMINA)
                    return [Stat::STAMINA];
                if ($miscValue == STAT_INTELLECT)
                    return [Stat::INTELLECT];
                if ($miscValue == STAT_SPIRIT)
                    return [Stat::SPIRIT];

                return [];                                  // one bullshit
            case SPELL_AURA_MOD_INCREASE_HEALTH:
            case SPELL_AURA_MOD_INCREASE_HEALTH_NONSTACK:
            case SPELL_AURA_MOD_INCREASE_HEALTH_2:
                return [Stat::HEALTH];
            case SPELL_AURA_MOD_DAMAGE_DONE:
                // + weapon damage
                if ($miscValue == (1 << SPELL_SCHOOL_NORMAL))
                    return [Stat::WEAPON_DAMAGE];

                // full magic mask
                if ($miscValue == SPELL_MAGIC_SCHOOLS)
                    return [Stat::DAMAGE_SPELL_POWER];

                // HolySpellpower (deprecated; still used in randomproperties)
                if ($miscValue & (1 << SPELL_SCHOOL_HOLY))
                    $stats[] = Stat::HOLY_SPELL_POWER;

                // FireSpellpower (deprecated; still used in randomproperties)
                if ($miscValue & (1 << SPELL_SCHOOL_FIRE))
                    $stats[] = Stat::FIRE_SPELL_POWER;

                // NatureSpellpower (deprecated; still used in randomproperties)
                if ($miscValue & (1 << SPELL_SCHOOL_NATURE))
                    $stats[] = Stat::NATURE_SPELL_POWER;

                // FrostSpellpower (deprecated; still used in randomproperties)
                if ($miscValue & (1 << SPELL_SCHOOL_FROST))
                    $stats[] = Stat::FROST_SPELL_POWER;

                // ShadowSpellpower (deprecated; still used in randomproperties)
                if ($miscValue & (1 << SPELL_SCHOOL_SHADOW))
                    $stats[] = Stat::SHADOW_SPELL_POWER;

                // ArcaneSpellpower (deprecated; still used in randomproperties)
                if ($miscValue & (1 << SPELL_SCHOOL_ARCANE))
                    $stats[] = Stat::ARCANE_SPELL_POWER;

                return $stats;
            case SPELL_AURA_MOD_HEALING_DONE:               // not as a mask..
                return [Stat::HEALING_SPELL_POWER];
            case SPELL_AURA_MOD_INCREASE_ENERGY:            // MiscVal:type see defined Powers only energy/mana in use
                if ($miscValue == POWER_ENERGY)
                    return [Stat::ENERGY];
                if ($miscValue == POWER_RAGE)
                    return [Stat::RAGE];
                if ($miscValue == POWER_MANA)
                    return [Stat::MANA];
                if ($miscValue == POWER_RUNIC_POWER)
                    return [Stat::RUNIC_POWER];

                return [];
            case SPELL_AURA_MOD_RATING:
            case SPELL_AURA_MOD_RATING_FROM_STAT:
                if ($stat = self::convertCombatRating($miscValue))
                    return $stat;

                return [];
            case SPELL_AURA_MOD_RESISTANCE_EXCLUSIVE:
            case SPELL_AURA_MOD_BASE_RESISTANCE:
            case SPELL_AURA_MOD_RESISTANCE:
                // Armor only if explicitly specified
                if ($miscValue == (1 << SPELL_SCHOOL_NORMAL))
                    return [Stat::ARMOR];

                // Holy resistance only if explicitly specified (should it even exist...?)
                if ($miscValue == (1 << SPELL_SCHOOL_HOLY))
                    return [Stat::HOLY_RESISTANCE];

                if ($miscValue & (1 << SPELL_SCHOOL_FIRE))
                    $stats[] = Stat::FIRE_RESISTANCE;
                if ($miscValue & (1 << SPELL_SCHOOL_NATURE))
                    $stats[] = Stat::NATURE_RESISTANCE;
                if ($miscValue & (1 << SPELL_SCHOOL_FROST))
                    $stats[] = Stat::FROST_RESISTANCE;
                if ($miscValue & (1 << SPELL_SCHOOL_SHADOW))
                    $stats[] = Stat::SHADOW_RESISTANCE;
                if ($miscValue & (1 << SPELL_SCHOOL_ARCANE))
                    $stats[] = Stat::ARCANE_RESISTANCE;

                return $stats;
            case SPELL_AURA_PERIODIC_HEAL:                  // hp5
            case SPELL_AURA_MOD_REGEN:
            case SPELL_AURA_MOD_HEALTH_REGEN_IN_COMBAT:
                return [Stat::HEALTH_REGENERATION];
            case SPELL_AURA_MOD_POWER_REGEN:                // mp5
                return [Stat::MANA_REGENERATION];
            case SPELL_AURA_MOD_ATTACK_POWER:
                return [Stat::ATTACK_POWER/*, Stat::RANGED_ATTACK_POWER*/];
            case SPELL_AURA_MOD_RANGED_ATTACK_POWER:
                return [Stat::RANGED_ATTACK_POWER];
            case SPELL_AURA_MOD_SHIELD_BLOCKVALUE:
                return [Stat::BLOCK];
            case SPELL_AURA_MOD_EXPERTISE:
                return [Stat::EXPERTISE];
            case SPELL_AURA_MOD_TARGET_RESISTANCE:
                $amount = abs($amount);                     // functionally negative, but we work with the absolute amount
                if ($miscValue == 0x7C)                     // SPELL_MAGIC_SCHOOLS & ~SPELL_SCHOOL_HOLY
                    return [Stat::SPELL_PENETRATION];
        }

        return [];
    }
}

?>
