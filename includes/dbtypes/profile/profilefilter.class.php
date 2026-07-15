<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileFilter extends Filter
{
    use TrProfilerFilter;

    protected string $type          = 'profiles';
    protected static array $genericFilter = array(
         2 => [parent::CR_NUMERIC,  'gearscore',          NUM_CAST_INT                       ], // gearscore [num]
         3 => [parent::CR_CALLBACK, 'cbAchievs',          null,                 null         ], // achievementpoints [num]
         5 => [parent::CR_NUMERIC,  'talenttree1',        NUM_CAST_INT                       ], // talenttree1 [num]
         6 => [parent::CR_NUMERIC,  'talenttree2',        NUM_CAST_INT                       ], // talenttree2 [num]
         7 => [parent::CR_NUMERIC,  'talenttree3',        NUM_CAST_INT                       ], // talenttree3 [num]
         9 => [parent::CR_STRING,   'g.name'                                                 ], // guildname
        10 => [parent::CR_CALLBACK, 'cbHasGuildRank',     null,                 null         ], // guildrank
        12 => [parent::CR_CALLBACK, 'cbTeamName',         2,                    null         ], // teamname2v2
        15 => [parent::CR_CALLBACK, 'cbTeamName',         3,                    null         ], // teamname3v3
        18 => [parent::CR_CALLBACK, 'cbTeamName',         5,                    null         ], // teamname5v5
        13 => [parent::CR_CALLBACK, 'cbTeamRating',       2,                    null         ], // teamrtng2v2
        16 => [parent::CR_CALLBACK, 'cbTeamRating',       3,                    null         ], // teamrtng3v3
        19 => [parent::CR_CALLBACK, 'cbTeamRating',       5,                    null         ], // teamrtng5v5
        14 => [parent::CR_NYI_PH,   null,                 0 /* 2 */                          ], // teamcontrib2v2 [num]
        17 => [parent::CR_NYI_PH,   null,                 0 /* 3 */                          ], // teamcontrib3v3 [num]
        20 => [parent::CR_NYI_PH,   null,                 0 /* 5 */                          ], // teamcontrib5v5 [num]
        21 => [parent::CR_CALLBACK, 'cbEquippedItemProp', Type::ITEM,           'item'       ], // wearingitem [str]
        22 => [parent::CR_CALLBACK, 'cbEquippedItemProp', Type::ENCHANTMENT,    'permEnchant'], // wearingpermenchant [str]
        23 => [parent::CR_CALLBACK, 'cbCompletedAcv',     null,                 null         ], // completedachievement
        25 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_ALCHEMY,        null         ], // alchemy [num]
        26 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_BLACKSMITHING,  null         ], // blacksmithing [num]
        27 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_ENCHANTING,     null         ], // enchanting [num]
        28 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_ENGINEERING,    null         ], // engineering [num]
        29 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_HERBALISM,      null         ], // herbalism [num]
        30 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_INSCRIPTION,    null         ], // inscription [num]
        31 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_JEWELCRAFTING,  null         ], // jewelcrafting [num]
        32 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_LEATHERWORKING, null         ], // leatherworking [num]
        33 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_MINING,         null         ], // mining [num]
        34 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_SKINNING,       null         ], // skinning [num]
        35 => [parent::CR_CALLBACK, 'cbProfession',       SKILL_TAILORING,      null         ], // tailoring [num]
        36 => [parent::CR_CALLBACK, 'cbHasGuild',         null,                 null         ]  // hasguild [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE,    [1, 36],                                          true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 5000]], true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                              true ], // criteria values
        'ex'    => [parent::V_EQUAL,    'on',                                             false], // only match exact - must be defined before 'na' as it's test relies on 'ex's value
        'na'    => [parent::V_NAME,     true,                                             false], // name - only printable chars, no delimiter
        'ma'    => [parent::V_EQUAL,    1,                                                false], // match any / all filter
        'si'    => [parent::V_LIST,     [SIDE_ALLIANCE, SIDE_HORDE],                      false], // side
        'ra'    => [parent::V_LIST,     [[1, 8], 10, 11],                                 true ], // race
        'cl'    => [parent::V_LIST,     [[1, 9], 11],                                     true ], // class
        'minle' => [parent::V_RANGE,    [1, MAX_LEVEL],                                   false], // min level
        'maxle' => [parent::V_RANGE,    [1, MAX_LEVEL],                                   false], // max level
        'rg'    => [parent::V_CALLBACK, 'cbRegionCheck',                                  false], // region
        'bg'    => [parent::V_EQUAL,    null,                                             false], // battlegroup - unsued here, but var expected by template
        'sv'    => [parent::V_CALLBACK, 'cbServerCheck',                                  false]  // server
    );

    public bool  $useLocalList = false;
    public array $extraOpts    = [];

    /*  heads up!
        a couple of filters are too complex to be run against the characters database
        if they are selected, force useage of LocalProfileList
    */

    public function __construct(string|array $data, array $opts = [])
    {
        parent::__construct($data, $opts);

        if (!empty($this->values['cr']))
            if (array_intersect($this->values['cr'], [2, 5, 6, 7, 21, 22]))
                $this->useLocalList = true;
    }

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // region (rg), battlegroup (bg) and server (sv) are passed to ProflieList as miscData and handled there

        // table key differs between remote and local :<
        $k = $this->useLocalList ? 'p' : 'c';

        // name [str]
        if ($_v['na'])
        {
            // issue: the table is case sensitive. so we need to alter the tokens for multiple cases
            foreach (['inTokens', 'exTokens'] as $prop)
            {
                if (empty($this->{$prop}['na']))
                    continue;

                $this->{$prop}['na']  = array_map(Util::lower(...), $this->{$prop}['na']);
                $this->{$prop}['_na'] = array_map(Util::ucWords(...), $this->{$prop}['na']);
            };

            $parts[] = $this->buildLikeLookup([['na', $k.'.name'], ['_na', $k.'.name']], $_v['ex'] == 'on');
        }

        // side [list]
        if ($_v['si'] == SIDE_ALLIANCE)
            $parts[] = [$k.'.race', ChrRace::fromMask(ChrRace::MASK_ALLIANCE)];
        else if ($_v['si'] == SIDE_HORDE)
            $parts[] = [$k.'.race', ChrRace::fromMask(ChrRace::MASK_HORDE)];

        // race [list]
        if ($_v['ra'])
            $parts[] = [$k.'.race', $_v['ra']];

        // class [list]
        if ($_v['cl'])
            $parts[] = [$k.'.class', $_v['cl']];

        // min level [int]
        if ($_v['minle'])
            $parts[] = [$k.'.level', $_v['minle'], '>='];

        // max level [int]
        if ($_v['maxle'])
            $parts[] = [$k.'.level', $_v['maxle'], '<='];

        return $parts;
    }

    protected function cbProfession(int $cr, int $crs, string $crv, int $skillId) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $k   = 'sk_'.Util::createHash(12);
        $col = 'skill-'.$skillId;

        $this->fiExtraCols[$skillId] = $col;

        if ($this->useLocalList)
        {
            $this->extraOpts[$k] = array(
                'j' => [sprintf('::profiler_completion_skills %1$s ON `%1$s`.`id` = p.`id` AND `%1$s`.`skillId` = %2$d AND `%1$s`.`value` %3$s %4$d', $k, $skillId, $crs, $crv), true],
                's' => [', '.$k.'.`value` AS "'.$col.'"']
            );
            return [$k.'.skillId', null, '!'];
        }
        else
        {
            $this->extraOpts[$k] = array(
                'j' => [sprintf('character_skills %1$s ON `%1$s`.`guid` = c.`guid` AND `%1$s`.`skill` = %2$d AND `%1$s`.`value` %3$s %4$d', $k, $skillId, $crs, $crv), true],
                's' => [', '.$k.'.`value` AS "'.$col.'"']
            );
            return [$k.'.skill', null, '!'];
        }
    }

    protected function cbCompletedAcv(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if (!Type::validateIds(Type::ACHIEVEMENT, $crv))
            return null;

        $k = 'acv_'.Util::createHash(12);

        if ($this->useLocalList)
        {
            $this->extraOpts[$k] = ['j' => [sprintf('::profiler_completion_achievements %1$s ON `%1$s`.`id` = p.`id` AND `%1$s`.`achievementId` = %2$d', $k, $crv), true]];
            return [$k.'.achievementId', null, '!'];
        }
        else
        {
            $this->extraOpts[$k] = ['j' => [sprintf('character_achievement %1$s ON `%1$s`.`guid` = c.`guid` AND `%1$s`.`achievement` = %2$d', $k, $crv), true]];
            return [$k.'.achievement', null, '!'];
        }
    }

    protected function cbEquippedItemProp(int $cr, int $crs, string $crv, int $type, string $field) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if (!Type::validateIds($type, $crv))
            return null;

        $k = 'i_'.Util::createHash(12);

        $this->extraOpts[$k] = ['j' => [sprintf('::profiler_items %1$s ON `%1$s`.`id` = p.`id` AND `%1$s`.`%3$s` = %2$d', $k, $crv, $field), true]];
        return [$k.'.'.$field, null, '!'];
    }

    protected function cbHasGuild(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($this->useLocalList)
            return ['p.guild', null, $crs ? '!' : null];
        else
            return ['gm.guildId', null, $crs ? '!' : null];
    }

    protected function cbHasGuildRank(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        if ($this->useLocalList)
            return ['p.guildrank', $crv, $crs];
        else
            return ['gm.rank', $crv, $crs];
    }

    protected function cbTeamName(int $cr, int $crs, string $crv, int $size) : ?array
    {
        $n = preg_replace(parent::PATTERN_NAME, '', $crv);
        if ($this->tokenizeString($cr, $n))
            if ($_ = $this->buildLikeLookup([[$cr, 'at.name']]))
                return [DB::AND, ['at.type', $size], $_];

        return null;
    }

    protected function cbTeamRating(int $cr, int $crs, string $crv, int $size) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        return [DB::AND, ['at.type', $size], ['at.rating', $crv, $crs]];
    }

    protected function cbAchievs(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        if ($this->useLocalList)
            return ['p.achievementpoints', $crv, $crs];
        else
            return ['cap.counter', $crv, $crs];
    }
}

?>
