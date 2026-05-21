<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentFilter extends Filter
{
    protected string $type  = 'enchantments';
    protected static array $enums = array(
        3   => parent::ENUM_PROFESSION,                     // requiresprof
        200 => parent::ENUM_ITEM_VISUAL                     // itemvisual
    );

    protected static array $genericFilter = array(
          2 => [parent::CR_NUMERIC, 'id',                 NUM_CAST_INT,         true], // id
          3 => [parent::CR_ENUM,    'skillLine'                                     ], // requiresprof
          4 => [parent::CR_NUMERIC, 'skillLevel',         NUM_CAST_INT              ], // reqskillrank
          5 => [parent::CR_BOOLEAN, 'conditionId'                                   ], // hascondition
         10 => [parent::CR_FLAG,    'cuFlags',            CUSTOM_HAS_COMMENT        ], // hascomments
         11 => [parent::CR_FLAG,    'cuFlags',            CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
         12 => [parent::CR_FLAG,    'cuFlags',            CUSTOM_HAS_VIDEO          ], // hasvideos
         20 => [parent::CR_NUMERIC, 'is.str',             NUM_CAST_INT,         true], // str
         21 => [parent::CR_NUMERIC, 'is.agi',             NUM_CAST_INT,         true], // agi
         22 => [parent::CR_NUMERIC, 'is.sta',             NUM_CAST_INT,         true], // sta
         23 => [parent::CR_NUMERIC, 'is.int',             NUM_CAST_INT,         true], // int
         24 => [parent::CR_NUMERIC, 'is.spi',             NUM_CAST_INT,         true], // spi
         25 => [parent::CR_NUMERIC, 'is.arcres',          NUM_CAST_INT,         true], // arcres
         26 => [parent::CR_NUMERIC, 'is.firres',          NUM_CAST_INT,         true], // firres
         27 => [parent::CR_NUMERIC, 'is.natres',          NUM_CAST_INT,         true], // natres
         28 => [parent::CR_NUMERIC, 'is.frores',          NUM_CAST_INT,         true], // frores
         29 => [parent::CR_NUMERIC, 'is.shares',          NUM_CAST_INT,         true], // shares
         30 => [parent::CR_NUMERIC, 'is.holres',          NUM_CAST_INT,         true], // holres
         32 => [parent::CR_NUMERIC, 'is.dps',             NUM_CAST_FLOAT,       true], // dps
         34 => [parent::CR_NUMERIC, 'is.dmg',             NUM_CAST_FLOAT,       true], // dmg
         37 => [parent::CR_NUMERIC, 'is.mleatkpwr',       NUM_CAST_INT,         true], // mleatkpwr
         38 => [parent::CR_NUMERIC, 'is.rgdatkpwr',       NUM_CAST_INT,         true], // rgdatkpwr
         39 => [parent::CR_NUMERIC, 'is.rgdhitrtng',      NUM_CAST_INT,         true], // rgdhitrtng
         40 => [parent::CR_NUMERIC, 'is.rgdcritstrkrtng', NUM_CAST_INT,         true], // rgdcritstrkrtng
         41 => [parent::CR_NUMERIC, 'is.armor',           NUM_CAST_INT,         true], // armor
         42 => [parent::CR_NUMERIC, 'is.defrtng',         NUM_CAST_INT,         true], // defrtng
         43 => [parent::CR_NUMERIC, 'is.block',           NUM_CAST_INT,         true], // block
         44 => [parent::CR_NUMERIC, 'is.blockrtng',       NUM_CAST_INT,         true], // blockrtng
         45 => [parent::CR_NUMERIC, 'is.dodgertng',       NUM_CAST_INT,         true], // dodgertng
         46 => [parent::CR_NUMERIC, 'is.parryrtng',       NUM_CAST_INT,         true], // parryrtng
         48 => [parent::CR_NUMERIC, 'is.splhitrtng',      NUM_CAST_INT,         true], // splhitrtng
         49 => [parent::CR_NUMERIC, 'is.splcritstrkrtng', NUM_CAST_INT,         true], // splcritstrkrtng
         50 => [parent::CR_NUMERIC, 'is.splheal',         NUM_CAST_INT,         true], // splheal
         51 => [parent::CR_NUMERIC, 'is.spldmg',          NUM_CAST_INT,         true], // spldmg
         52 => [parent::CR_NUMERIC, 'is.arcsplpwr',       NUM_CAST_INT,         true], // arcsplpwr
         53 => [parent::CR_NUMERIC, 'is.firsplpwr',       NUM_CAST_INT,         true], // firsplpwr
         54 => [parent::CR_NUMERIC, 'is.frosplpwr',       NUM_CAST_INT,         true], // frosplpwr
         55 => [parent::CR_NUMERIC, 'is.holsplpwr',       NUM_CAST_INT,         true], // holsplpwr
         56 => [parent::CR_NUMERIC, 'is.natsplpwr',       NUM_CAST_INT,         true], // natsplpwr
         57 => [parent::CR_NUMERIC, 'is.shasplpwr',       NUM_CAST_INT,         true], // shasplpwr
         60 => [parent::CR_NUMERIC, 'is.healthrgn',       NUM_CAST_INT,         true], // healthrgn
         61 => [parent::CR_NUMERIC, 'is.manargn',         NUM_CAST_INT,         true], // manargn
         77 => [parent::CR_NUMERIC, 'is.atkpwr',          NUM_CAST_INT,         true], // atkpwr
         78 => [parent::CR_NUMERIC, 'is.mlehastertng',    NUM_CAST_INT,         true], // mlehastertng
         79 => [parent::CR_NUMERIC, 'is.resirtng',        NUM_CAST_INT,         true], // resirtng
         84 => [parent::CR_NUMERIC, 'is.mlecritstrkrtng', NUM_CAST_INT,         true], // mlecritstrkrtng
         94 => [parent::CR_NUMERIC, 'is.splpen',          NUM_CAST_INT,         true], // splpen
         95 => [parent::CR_NUMERIC, 'is.mlehitrtng',      NUM_CAST_INT,         true], // mlehitrtng
         96 => [parent::CR_NUMERIC, 'is.critstrkrtng',    NUM_CAST_INT,         true], // critstrkrtng
         97 => [parent::CR_NUMERIC, 'is.feratkpwr',       NUM_CAST_INT,         true], // feratkpwr
        101 => [parent::CR_NUMERIC, 'is.rgdhastertng',    NUM_CAST_INT,         true], // rgdhastertng
        102 => [parent::CR_NUMERIC, 'is.splhastertng',    NUM_CAST_INT,         true], // splhastertng
        103 => [parent::CR_NUMERIC, 'is.hastertng',       NUM_CAST_INT,         true], // hastertng
        114 => [parent::CR_NUMERIC, 'is.armorpenrtng',    NUM_CAST_INT,         true], // armorpenrtng
        115 => [parent::CR_NUMERIC, 'is.health',          NUM_CAST_INT,         true], // health
        116 => [parent::CR_NUMERIC, 'is.mana',            NUM_CAST_INT,         true], // mana
        117 => [parent::CR_NUMERIC, 'is.exprtng',         NUM_CAST_INT,         true], // exprtng
        119 => [parent::CR_NUMERIC, 'is.hitrtng',         NUM_CAST_INT,         true], // hitrtng
        123 => [parent::CR_NUMERIC, 'is.splpwr',          NUM_CAST_INT,         true], // splpwr
        200 => [parent::CR_CALLBACK,'cbHasItemVisual',    null,                 null]  // itemvisual (custom)
    );

    protected static array $inputFields = array(
        'cr'  => [parent::V_LIST,  [[2, 123], 200],                             true ], // criteria ids
        'crs' => [parent::V_LIST,  [[1, 215], self::ENUM_ANY, self::ENUM_NONE], true ], // criteria operators
        'crv' => [parent::V_REGEX, parent::PATTERN_INT,                         true ], // criteria values - only numerals
        'na'  => [parent::V_NAME,  false,                                       false], // name - only printable chars, no delimiter
        'ma'  => [parent::V_EQUAL, 1,                                           false], // match any / all filter
        'ty'  => [parent::V_RANGE, [1, 8],                                      true ]  // types
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        //string
        if ($_v['na'])
            if ($_ = $this->buildLikeLookup([['na', 'name_loc'.Lang::getLocale()->value]]))
                $parts[] = $_;

        // type
        if ($_v['ty'])
            $parts[] = [DB::OR, ['type1', $_v['ty']], ['type2', $_v['ty']], ['type3', $_v['ty']]];

        return $parts;
    }

    protected function cbHasItemVisual(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (in_array($crs, self::$enums[$cr]))
            return [DB::OR, ['iv.visualEffectId1', $crs], ['iv.visualEffectId2', $crs], ['iv.visualEffectId3', $crs], ['iv.visualEffectId4', $crs], ['iv.visualEffectId5', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return ['itemVisualId', 0, '!'];
        else if ($crs == parent::ENUM_NONE)
            return ['itemVisualId', 0];

        return null;
    }
}

?>
