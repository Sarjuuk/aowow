<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// missing filter: "Available to Players"
class ItemsetFilter extends Filter
{
    protected string $type  = 'itemsets';
    protected static array $enums = array(
         6 => parent::ENUM_EVENT
    );

    protected static array $genericFilter = array(
         2 => [parent::CR_NUMERIC,  'id',          NUM_CAST_INT,         true], // id
         3 => [parent::CR_NUMERIC,  'npieces',     NUM_CAST_INT              ], // pieces
         4 => [parent::CR_STRING,   'bonusText',   STR_LOCALIZED             ], // bonustext
         5 => [parent::CR_BOOLEAN,  'heroic'                                 ], // heroic
         6 => [parent::CR_ENUM,     'e.holidayId', true,                 true], // relatedevent
         8 => [parent::CR_FLAG,     'cuFlags',     CUSTOM_HAS_COMMENT        ], // hascomments
         9 => [parent::CR_FLAG,     'cuFlags',     CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        10 => [parent::CR_FLAG,     'cuFlags',     CUSTOM_HAS_VIDEO          ], // hasvideos
        12 => [parent::CR_CALLBACK, 'cbAvaliable',                           ]  // available to players [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE, [2, 12],                                         true ], // criteria ids
        'crs'   => [parent::V_LIST,  [parent::ENUM_NONE, parent::ENUM_ANY, [0, 424]], true ], // criteria operators
        'crv'   => [parent::V_REGEX, parent::PATTERN_CRV,                             true ], // criteria values - only printable chars, no delimiters
        'na'    => [parent::V_NAME,  false,                                           false], // name / description - only printable chars, no delimiter
        'ma'    => [parent::V_EQUAL, 1,                                               false], // match any / all filter
        'qu'    => [parent::V_RANGE, [0, 7],                                          true ], // quality
        'ty'    => [parent::V_RANGE, [1, 12],                                         true ], // set type
        'minle' => [parent::V_RANGE, [0, 999],                                        false], // min item level
        'maxle' => [parent::V_RANGE, [0, 999],                                        false], // max itemlevel
        'minrl' => [parent::V_RANGE, [0, MAX_LEVEL],                                  false], // min required level
        'maxrl' => [parent::V_RANGE, [0, MAX_LEVEL],                                  false], // max required level
        'cl'    => [parent::V_LIST,  [[1, 9], 11],                                    false], // class
        'ta'    => [parent::V_RANGE, [1, 30],                                         false]  // tag / content group
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name [str]
        if ($_v['na'])
            if ($_ = $this->buildLikeLookup([['na', 'name_loc'.Lang::getLocale()->value]]))
                $parts[] = $_;

        // quality [enum]
        if ($_v['qu'])
            $parts[] = ['quality', $_v['qu']];

        // type [enum]
        if ($_v['ty'])
            $parts[] = ['type', $_v['ty']];

        // itemLevel min [int]
        if ($_v['minle'])
            $parts[] = ['minLevel', $_v['minle'], '>='];

        // itemLevel max [int]
        if ($_v['maxle'])
            $parts[] = ['maxLevel', $_v['maxle'], '<='];

        // reqLevel min [int]
        if ($_v['minrl'])
            $parts[] = ['minReqLevel', $_v['minrl'], '>='];

        // reqLevel max [int]
        if ($_v['maxrl'])
            $parts[] = ['maxReqLevel', $_v['maxrl'], '<='];

        // class [enum]
        if ($_v['cl'])
            $parts[] = ['classMask', $this->list2Mask([$_v['cl']]), '&'];

        // tag [enum]
        if ($_v['ta'])
            $parts[] = ['contentGroup', intVal($_v['ta'])];

        return $parts;
    }

    protected function cbAvaliable(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            1 => ['src.typeId', null, '!'],                 // Yes
            2 => ['src.typeId', null],                      // No
            default => null
        };
    }
}

?>
