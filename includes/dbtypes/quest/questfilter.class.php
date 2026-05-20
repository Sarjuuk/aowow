<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestFilter extends Filter
{
    protected string $type  = 'quests';
    protected static array $enums = array(
        37 => parent::ENUM_CLASSS,                          // classspecific
        38 => parent::ENUM_RACE,                            // racespecific
         9 => parent::ENUM_FACTION,                         // objectiveearnrepwith
        33 => parent::ENUM_EVENT,                           // relatedevent
        43 => parent::ENUM_CURRENCY,                        // currencyrewarded
         1 => parent::ENUM_FACTION,                         // increasesrepwith
        10 => parent::ENUM_FACTION                          // decreasesrepwith
    );

    protected static array $genericFilter = array(
         1 => [parent::CR_CALLBACK,  'cbReputation',     '>',                     null], // increasesrepwith
         2 => [parent::CR_NUMERIC,   'rewardXP',         NUM_CAST_INT                 ], // experiencegained
         3 => [parent::CR_NUMERIC,   'rewardOrReqMoney', NUM_CAST_INT                 ], // moneyrewarded
         4 => [parent::CR_CALLBACK,  'cbSpellRewards',   null,                    null], // spellrewarded [yn]
         5 => [parent::CR_FLAG,      'flags',            QUEST_FLAG_SHARABLE          ], // sharable
         6 => [parent::CR_NUMERIC,   'timeLimit',        NUM_CAST_INT                 ], // timer
         7 => [parent::CR_FLAG,      'cuFlags',          QUEST_CU_FIRST_SERIES        ], // firstquestseries
         9 => [parent::CR_CALLBACK,  'cbEarnReputation', null,                    null], // objectiveearnrepwith [enum]
        10 => [parent::CR_CALLBACK,  'cbReputation',     '<',                     null], // decreasesrepwith
        11 => [parent::CR_NUMERIC,   'suggestedPlayers', NUM_CAST_INT                 ], // suggestedplayers
        15 => [parent::CR_FLAG,      'cuFlags',          QUEST_CU_LAST_SERIES         ], // lastquestseries
        16 => [parent::CR_FLAG,      'cuFlags',          QUEST_CU_PART_OF_SERIES      ], // partseries
        18 => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_SCREENSHOT        ], // hasscreenshots
        19 => [parent::CR_CALLBACK,  'cbQuestRelation',  0x1,                     null], // startsfrom [enum]
        21 => [parent::CR_CALLBACK,  'cbQuestRelation',  0x2,                     null], // endsat [enum]
        22 => [parent::CR_CALLBACK,  'cbItemRewards',    null,                    null], // itemrewards [op] [int]
        23 => [parent::CR_CALLBACK,  'cbItemChoices',    null,                    null], // itemchoices [op] [int]
        24 => [parent::CR_CALLBACK,  'cbLacksStartEnd',  null,                    null], // lacksstartend [yn]
        25 => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_COMMENT           ], // hascomments
        27 => [parent::CR_FLAG,      'flags',            QUEST_FLAG_DAILY             ], // daily
        28 => [parent::CR_FLAG,      'flags',            QUEST_FLAG_WEEKLY            ], // weekly
        29 => [parent::CR_FLAG,      'specialFlags',     QUEST_FLAG_SPECIAL_REPEATABLE], // repeatable
        30 => [parent::CR_NUMERIC,   'id',               NUM_CAST_INT,            true], // id
        33 => [parent::CR_ENUM,      'e.holidayId',      true,                    true], // relatedevent
        34 => [parent::CR_CALLBACK,  'cbAvailable',      null,                    null], // availabletoplayers [yn]
        36 => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_VIDEO             ], // hasvideos
        37 => [parent::CR_CALLBACK,  'cbClassSpec',      null,                    null], // classspecific [enum]
        38 => [parent::CR_CALLBACK,  'cbRaceSpec',       null,                    null], // racespecific [enum]
        42 => [parent::CR_STAFFFLAG, 'flags'                                          ], // flags
        43 => [parent::CR_CALLBACK,  'cbCurrencyReward', null,                    null], // currencyrewarded [enum]
        44 => [parent::CR_CALLBACK,  'cbLoremaster',     null,                    null], // countsforloremaster_stc [yn]
        45 => [parent::CR_BOOLEAN,   'rewardTitleId'                                  ], // titlerewarded
        47 => [parent::CR_FLAG,      'flags',            QUEST_FLAG_FLAGS_PVP         ]  // setspvpflag
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE, [1, 47],                                                             true ], // criteria ids
        'crs'   => [parent::V_LIST,  [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]],                   true ], // criteria operators
        'crv'   => [parent::V_REGEX, parent::PATTERN_INT,                                                 true ], // criteria values - only numerals
        'na'    => [parent::V_NAME,  false,                                                               false], // name / text - only printable chars, no delimiter
        'ex'    => [parent::V_EQUAL, 'on',                                                                false], // also match subname
        'ma'    => [parent::V_EQUAL, 1,                                                                   false], // match any / all filter
        'minle' => [parent::V_RANGE, [0, 99],                                                             false], // min quest level
        'maxle' => [parent::V_RANGE, [0, 99],                                                             false], // max quest level
        'minrl' => [parent::V_RANGE, [0, 99],                                                             false], // min required level
        'maxrl' => [parent::V_RANGE, [0, 99],                                                             false], // max required level
        'si'    => [parent::V_LIST,  [-SIDE_HORDE, -SIDE_ALLIANCE, SIDE_ALLIANCE, SIDE_HORDE, SIDE_BOTH], false], // side
        'ty'    => [parent::V_LIST,  [0, 1, 21, 41, 62, [81, 85], 88, 89],                                true ]  // type
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // name
        if ($_v['na'])
        {
            $f = [['na', ['nml.nName', 'nml.nObjectives', 'nml.nDetails']]];
            if ($_v['ex'] != 'on')
                $f = [['na', 'nml.nName']];

            if ($_ = $this->buildMatchLookup($f))
                $parts[] = $_;
            else
            {
                $f = [['na', 'name_loc'.Lang::getLocale()->value], ['na', 'objectives_loc'.Lang::getLocale()->value], ['na', 'details_loc'.Lang::getLocale()->value]];
                if ($_v['ex'] != 'on')
                    $f = [$f[0]];

                if ($_ = $this->buildLikeLookup($f))
                    $parts[] = $_;
            }
        }

        // level min
        if ($_v['minle'])
            $parts[] = ['level', $_v['minle'], '>='];       // not considering quests that are always at player level (-1)

        // level max
        if ($_v['maxle'])
            $parts[] = ['level', $_v['maxle'], '<='];

        // reqLevel min
        if ($_v['minrl'])
            $parts[] = ['minLevel', $_v['minrl'], '>='];    // ignoring maxLevel

        // reqLevel max
        if ($_v['maxrl'])
            $parts[] = ['minLevel', $_v['maxrl'], '<='];    // ignoring maxLevel

        // side
        if ($_v['si'])
        {
            $excl = [['reqRaceMask', ChrRace::MASK_ALL, '&'], ChrRace::MASK_ALL, '!'];
            $incl = [DB::OR, ['reqRaceMask', 0], [['reqRaceMask', ChrRace::MASK_ALL, '&'], ChrRace::MASK_ALL]];

            $parts[] = match ($_v['si'])
            {
                 SIDE_BOTH     => $incl,
                 SIDE_HORDE    => [DB::OR,  $incl, ['reqRaceMask', ChrRace::MASK_HORDE, '&']],
                -SIDE_HORDE    => [DB::AND, $excl, ['reqRaceMask', ChrRace::MASK_HORDE, '&']],
                 SIDE_ALLIANCE => [DB::OR,  $incl, ['reqRaceMask', ChrRace::MASK_ALLIANCE, '&']],
                -SIDE_ALLIANCE => [DB::AND, $excl, ['reqRaceMask', ChrRace::MASK_ALLIANCE, '&']]
            };
        }

        // questInfoId [list]
        if ($_v['ty'])
            $parts[] = ['questInfoId', $_v['ty']];

        return $parts;
    }

    protected function cbReputation(int $cr, int $crs, string $crv, string $sign) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (!in_array($crs, self::$enums[$cr]))
            return null;

        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ::factions WHERE `id` = %i', $crs))
            $this->fiReputationCols[] = [$crs, Util::localizedString($_, 'name')];

        return [
            DB::OR,
            [DB::AND, ['rewardFactionId1', $crs], ['rewardFactionValue1', 0, $sign]],
            [DB::AND, ['rewardFactionId2', $crs], ['rewardFactionValue2', 0, $sign]],
            [DB::AND, ['rewardFactionId3', $crs], ['rewardFactionValue3', 0, $sign]],
            [DB::AND, ['rewardFactionId4', $crs], ['rewardFactionValue4', 0, $sign]],
            [DB::AND, ['rewardFactionId5', $crs], ['rewardFactionValue5', 0, $sign]]
        ];
    }

    protected function cbQuestRelation(int $cr, int $crs, string $crv, int $flags) : ?array
    {
        return match ($crs)
        {
            Type::NPC,
            Type::OBJECT,
            Type::ITEM   => [DB::AND, ['qse.type', $crs], ['qse.method', $flags, '&']],
            default      => null
        };
    }

    protected function cbCurrencyReward(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (!in_array($crs, self::$enums[$cr]))
            return null;

        return [
            DB::OR,
            ['rewardItemId1', $crs], ['rewardItemId2', $crs], ['rewardItemId3', $crs], ['rewardItemId4', $crs],
            ['rewardChoiceItemId1', $crs], ['rewardChoiceItemId2', $crs], ['rewardChoiceItemId3', $crs], ['rewardChoiceItemId4', $crs], ['rewardChoiceItemId5', $crs], ['rewardChoiceItemId6', $crs]
        ];
    }

    protected function cbAvailable(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [['cuFlags', CUSTOM_UNAVAILABLE | CUSTOM_DISABLED, '&'], 0];
        else
            return ['cuFlags', CUSTOM_UNAVAILABLE | CUSTOM_DISABLED, '&'];
    }

    protected function cbItemChoices(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $this->extraOpts['q']['s'][] = ', (IF(`rewardChoiceItemId1`, 1, 0) + IF(`rewardChoiceItemId2`, 1, 0) + IF(`rewardChoiceItemId3`, 1, 0) + IF(`rewardChoiceItemId4`, 1, 0) + IF(`rewardChoiceItemId5`, 1, 0) + IF(`rewardChoiceItemId6`, 1, 0)) AS "numChoices"';
        $this->extraOpts['q']['h'][] = '`numChoices` '.$crs.' '.$crv;
        return [1];
    }

    protected function cbItemRewards(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $this->extraOpts['q']['s'][] = ', (IF(`rewardItemId1`, 1, 0) + IF(`rewardItemId2`, 1, 0) + IF(`rewardItemId3`, 1, 0) + IF(`rewardItemId4`, 1, 0)) AS "numRewards"';
        $this->extraOpts['q']['h'][] = '`numRewards` '.$crs.' '.$crv;
        return [1];
    }

    protected function cbLoremaster(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [DB::AND, ['questSortId', 0, '>'], [['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY, '&'], 0], [['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_MONTHLY, '&'], 0]];
        else
            return [DB::OR, ['questSortId', 0, '<'], ['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY, '&'], ['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_MONTHLY, '&']];
    }

    protected function cbSpellRewards(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [DB::OR, ['sourceSpellId', 0, '>'], ['rewardSpell', 0, '>'], ['rsc.effect1Id', SpellEntry::EFFECTS_TEACH], ['rsc.effect2Id', SpellEntry::EFFECTS_TEACH], ['rsc.effect3Id', SpellEntry::EFFECTS_TEACH]];
        else
            return [DB::AND, ['sourceSpellId', 0], ['rewardSpell', 0], ['rewardSpellCast', 0]];
    }

    protected function cbEarnReputation(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if ($crs == parent::ENUM_ANY)
            return [DB::OR, ['reqFactionId1', 0, '>'], ['reqFactionId2', 0, '>']];
        else if ($crs == parent::ENUM_NONE)
            return [DB::AND, ['reqFactionId1', 0], ['reqFactionId2', 0]];
        else if (in_array($crs, self::$enums[$cr]))
            return [DB::OR, ['reqFactionId1', $crs], ['reqFactionId2', $crs]];

        return null;
    }

    protected function cbClassSpec(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if ($_ === true)
            return [DB::AND, ['reqClassMask', 0, '!'], [['reqClassMask', ChrClass::MASK_ALL, '&'], ChrClass::MASK_ALL, '!']];
        else if ($_ === false)
            return [DB::OR, ['reqClassMask', 0], [['reqClassMask', ChrClass::MASK_ALL, '&'], ChrClass::MASK_ALL]];
        else if (is_int($_))
            return [DB::AND, ['reqClassMask', ChrClass::from($_)->toMask(), '&'], [['reqClassMask', ChrClass::MASK_ALL, '&'], ChrClass::MASK_ALL, '!']];

        return null;
    }

    protected function cbRaceSpec(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if ($_ === true)
            return [DB::AND, ['reqRaceMask', 0, '!'], [['reqRaceMask', ChrRace::MASK_ALL, '&'], ChrRace::MASK_ALL, '!'], [['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ChrRace::MASK_ALLIANCE, '!'], [['reqRaceMask', ChrRace::MASK_HORDE, '&'], ChrRace::MASK_HORDE, '!']];
        else if ($_ === false)
            return [DB::OR, ['reqRaceMask', 0], ['reqRaceMask', ChrRace::MASK_ALL], ['reqRaceMask', ChrRace::MASK_ALLIANCE], ['reqRaceMask', ChrRace::MASK_HORDE]];
        else if (is_int($_))
            return [DB::AND, ['reqRaceMask', ChrRace::from($_)->toMask(), '&'], [['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ChrRace::MASK_ALLIANCE, '!'], [['reqRaceMask', ChrRace::MASK_HORDE, '&'], ChrRace::MASK_HORDE, '!']];

        return null;
    }

    protected function cbLacksStartEnd(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        $missing = DB::Aowow()->selectCol('SELECT `questId`, BIT_OR(`method`) AS "se" FROM ::quests_startend GROUP BY `questId` HAVING "se" <> 3');
        if ($crs)
            return ['id', $missing];
        else
            return ['id', $missing, '!'];
    }
}

?>
