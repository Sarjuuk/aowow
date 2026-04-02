<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementFilter extends Filter
{
    protected string $type  = 'achievements';
    protected static array $enums = array(
         4 => parent::ENUM_ZONE,                            // location
        11 => array(
              327 => 160,                                   // Lunar Festival
              423 => 187,                                   // Love is in the Air
              181 => 159,                                   // Noblegarden
              201 => 163,                                   // Children's Week
              341 => 161,                                   // Midsummer Fire Festival
              372 => 162,                                   // Brewfest
              324 => 158,                                   // Hallow's End
              404 => 14981,                                 // Pilgrim's Bounty
              141 => 156,                                   // Feast of Winter Veil
              409 => -3456,                                 // Day of the Dead
              398 => -3457,                                 // Pirates' Day
              parent::ENUM_ANY  => true,
              parent::ENUM_NONE => false,
              283 => -1,                                    // valid events without achievements
              285 => -1,   353 => -1,   420 => -1,
              400 => -1,   284 => -1,   374 => -1,
              321 => -1,   424 => -1,   301 => -1
        )
    );

    protected static array $genericFilter = array(
         2 => [parent::CR_BOOLEAN,   'reward_loc0', true                             ], // givesreward
         3 => [parent::CR_STRING,    'reward',      STR_LOCALIZED                    ], // rewardtext
         4 => [parent::CR_NYI_PH,    null,          1,                               ], // location [enum]
         5 => [parent::CR_CALLBACK,  'cbSeries',    ACHIEVEMENT_CU_FIRST_SERIES, null], // first in series [yn]
         6 => [parent::CR_CALLBACK,  'cbSeries',    ACHIEVEMENT_CU_LAST_SERIES,  null], // last in series [yn]
         7 => [parent::CR_BOOLEAN,   'chainId',                                      ], // partseries
         9 => [parent::CR_NUMERIC,   'id',          NUM_CAST_INT,                true], // id
        10 => [parent::CR_STRING,    'ic.name',                                      ], // icon
        11 => [parent::CR_CALLBACK,  'cbRelEvent', null,                         null], // related event [enum]
        14 => [parent::CR_FLAG,      'cuFlags',     CUSTOM_HAS_COMMENT               ], // hascomments
        15 => [parent::CR_FLAG,      'cuFlags',     CUSTOM_HAS_SCREENSHOT            ], // hasscreenshots
        16 => [parent::CR_FLAG,      'cuFlags',     CUSTOM_HAS_VIDEO                 ], // hasvideos
        18 => [parent::CR_STAFFFLAG, 'flags',                                        ]  // flags
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE, [2, 18],                                                             true ], // criteria ids
        'crs'   => [parent::V_LIST,  [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]],                   true ], // criteria operators
        'crv'   => [parent::V_REGEX, parent::PATTERN_CRV,                                                 true ], // criteria values - only printable chars, no delimiters
        'na'    => [parent::V_NAME,  false,                                                               false], // name / description - only printable chars, no delimiter
        'ex'    => [parent::V_EQUAL, 'on',                                                                false], // extended name search
        'ma'    => [parent::V_EQUAL, 1,                                                                   false], // match any / all filter
        'si'    => [parent::V_LIST,  [SIDE_ALLIANCE, SIDE_HORDE, SIDE_BOTH, -SIDE_ALLIANCE, -SIDE_HORDE], false], // side
        'minpt' => [parent::V_RANGE, [1, 99],                                                             false], // required level min
        'maxpt' => [parent::V_RANGE, [1, 99],                                                             false]  // required level max
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name ex: +description, +rewards
        if ($_v['na'])
        {
            $_ = [];
            if ($_v['ex'] == 'on')
                $_ = $this->buildLikeLookup([['na', 'name_loc'.Lang::getLocale()->value], ['na', 'reward_loc'.Lang::getLocale()->value], ['na', 'description_loc'.Lang::getLocale()->value]]);
            else
                $_ = $this->buildLikeLookup([['na', 'name_loc'.Lang::getLocale()->value]]);

            if ($_)
                $parts[] = $_;
        }

        // points min
        if ($_v['minpt'])
            $parts[] = ['points', $_v['minpt'],  '>='];

        // points max
        if ($_v['maxpt'])
            $parts[] = ['points', $_v['maxpt'],  '<='];

        // faction (side)
        if ($_v['si'])
        {
            $parts[] = match ($_v['si'])
            {
                -SIDE_ALLIANCE,                             // equals faction
                -SIDE_HORDE     => ['faction', -$_v['si']],
                 SIDE_ALLIANCE,                             // includes faction
                 SIDE_HORDE,
                 SIDE_BOTH      => ['faction', $_v['si'], '&']
            };
        }

        return $parts;
    }

    protected function cbRelEvent(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_int($_))
            return ($_ > 0) ? ['category', $_] : ['id', abs($_)];
        else
        {
            $ids = array_filter(self::$enums[$cr], fn($x) => is_int($x) && $x > 0);

            return ['category', $ids, $_ ? null : '!'];
        }

        return null;
    }

    protected function cbSeries(int $cr, int $crs, string $crv, int $seriesFlag) : ?array
    {
        if ($this->int2Bool($crs))
            return $crs ? [DB::AND, ['chainId', 0, '!'], ['cuFlags', $seriesFlag, '&']] : [DB::AND, ['chainId', 0, '!'], [['cuFlags', $seriesFlag, '&'], 0]];

        return null;
    }
}

?>
