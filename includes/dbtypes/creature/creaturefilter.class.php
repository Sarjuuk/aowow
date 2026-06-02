<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureFilter extends Filter
{
    protected string $type  = 'npcs';
    protected static array $enums = array(
         3 => parent::ENUM_FACTION,                         // faction
         6 => parent::ENUM_ZONE,                            // foundin
        42 => parent::ENUM_FACTION,                         // increasesrepwith
        43 => parent::ENUM_FACTION,                         // decreasesrepwith
        38 => parent::ENUM_EVENT                            // relatedevent
    );

    protected static array $genericFilter = array(
         1 => [parent::CR_CALLBACK, 'cbHealthMana',      'healthMax',                       'healthMin'], // health [num]
         2 => [parent::CR_CALLBACK, 'cbHealthMana',      'manaMin',                         'manaMax'  ], // mana [num]
         3 => [parent::CR_CALLBACK, 'cbFaction',         null,                               null      ], // faction [enum]
         5 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_REPAIRER                             ], // canrepair
         6 => [parent::CR_ENUM,     's.areaId',          false,                              true      ], // foundin
         7 => [parent::CR_CALLBACK, 'cbQuestRelation',   'startsQuests',                     0x1       ], // startsquest [enum]
         8 => [parent::CR_CALLBACK, 'cbQuestRelation',   'endsQuests',                       0x2       ], // endsquest [enum]
         9 => [parent::CR_BOOLEAN,  'lootId',                                                          ], // lootable
        10 => [parent::CR_CALLBACK, 'cbRegularSkinLoot', NPC_TYPEFLAG_SPECIALLOOT                      ], // skinnable [yn]
        11 => [parent::CR_BOOLEAN,  'pickpocketLootId',                                                ], // pickpocketable
        12 => [parent::CR_CALLBACK, 'cbMoneyDrop',       null,                               null      ], // averagemoneydropped [op] [int]
        15 => [parent::CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_SKIN_WITH_HERBALISM,   null      ], // gatherable [yn]
        16 => [parent::CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_SKIN_WITH_MINING,      null      ], // minable [yn]
        18 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_AUCTIONEER                           ], // auctioneer
        19 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_BANKER                               ], // banker
        20 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_BATTLEMASTER                         ], // battlemaster
        21 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_FLIGHT_MASTER                        ], // flightmaster
        22 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_GUILD_MASTER                         ], // guildmaster
        23 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_INNKEEPER                            ], // innkeeper
        24 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_CLASS_TRAINER                        ], // talentunlearner
        25 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_GUILD_MASTER                         ], // tabardvendor
        27 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_STABLE_MASTER                        ], // stablemaster
        28 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_TRAINER                              ], // trainer
        29 => [parent::CR_FLAG,     'npcflag',           NPC_FLAG_VENDOR                               ], // vendor
        31 => [parent::CR_FLAG,     'cuFlags',           CUSTOM_HAS_SCREENSHOT                         ], // hasscreenshots
        32 => [parent::CR_FLAG,     'cuFlags',           NPC_CU_INSTANCE_BOSS                          ], // instanceboss
        33 => [parent::CR_FLAG,     'cuFlags',           CUSTOM_HAS_COMMENT                            ], // hascomments
        34 => [parent::CR_NUMSTRING,'modelId',           NUM_CAST_INT                                  ], // usemodel [str]
        35 => [parent::CR_STRING,   'textureString'                                                    ], // useskin [str]
        37 => [parent::CR_NUMERIC,  'id',                NUM_CAST_INT,                       true      ], // id
        38 => [parent::CR_CALLBACK, 'cbRelEvent',        null,                               null      ], // relatedevent [enum]
        40 => [parent::CR_FLAG,     'cuFlags',           CUSTOM_HAS_VIDEO                              ], // hasvideos
        41 => [parent::CR_CALLBACK, 'cbHasLocation'                                                    ], // haslocation [yn] [staff]
        42 => [parent::CR_CALLBACK, 'cbReputation',      1,                                  null      ], // increasesrepwith [enum]
        43 => [parent::CR_CALLBACK, 'cbReputation',      5,                                  null      ], // decreasesrepwith [enum]
        44 => [parent::CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_SKIN_WITH_ENGINEERING, null      ]  // salvageable [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_LIST,     [[1, 3],[5, 12], 15, 16, [18, 25], [27, 29], [31, 35], 37, 38, [40, 44]], true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 9999]],                         true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                                                      true ], // criteria values - only printable chars, no delimiter
        'na'    => [parent::V_NAME,     false,                                                                    false], // name / subname - only printable chars, no delimiter
        'ex'    => [parent::V_EQUAL,    'on',                                                                     false], // also match subname
        'ma'    => [parent::V_EQUAL,    1,                                                                        false], // match any / all filter
        'fa'    => [parent::V_CALLBACK, 'cbPetFamily',                                                            true ], // pet family [list]  -  cat[0] == 1
        'minle' => [parent::V_RANGE,    [0, 99],                                                                  false], // min level [int]
        'maxle' => [parent::V_RANGE,    [0, 99],                                                                  false], // max level [int]
        'cl'    => [parent::V_RANGE,    [0, 4],                                                                   true ], // classification [list]
        'ra'    => [parent::V_LIST,     [-1, 0, 1],                                                               false], // react alliance [int]
        'rh'    => [parent::V_LIST,     [-1, 0, 1],                                                               false]  // react horde [int]
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name [str]
        if ($_v['na'])
        {
            $f = [['na', ['nml.nName', 'nml.nSubname']]];
            if ($_v['ex'] != 'on')
                $f = [['na', 'nml.nName']];

            if ($_ = $this->buildMatchLookup($f))
                $parts[] = $_;
            else
            {
                $f = [['na', 'name_loc'.Lang::getLocale()->value], ['na', 'subname_loc'.Lang::getLocale()->value]];
                if ($_v['ex'] != 'on')
                    $f = [$f[0]];

                if ($_ = $this->buildLikeLookup($f))
                    $parts[] = $_;
            }
        }

        // pet family [list]
        if ($_v['fa'])
            $parts[] = ['family', $_v['fa']];

        // creatureLevel min [int]
        if ($_v['minle'])
            $parts[] = ['minLevel', $_v['minle'], '>='];

        // creatureLevel max [int]
        if ($_v['maxle'])
            $parts[] = ['maxLevel', $_v['maxle'], '<='];

        // classification [list]
        if ($_v['cl'])
            $parts[] = ['rank', $_v['cl']];

        // react Alliance [int]
        if (!is_null($_v['ra']))
            $parts[] = ['ft.A', $_v['ra']];

        // react Horde [int]
        if (!is_null($_v['rh']))
            $parts[] = ['ft.H', $_v['rh']];

        return $parts;
    }

    protected function cbPetFamily(string &$val) : bool
    {
        if (!$this->parentCats || $this->parentCats[0] != 1)
            return false;

        if (!Util::checkNumeric($val, NUM_CAST_INT))
            return false;

        $type  = parent::V_LIST;
        $valid = [[1, 9], 11, 12, 20, 21, [24, 27], [30, 35], [37, 39], [41, 46]];

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbRelEvent(int $cr, int $crs, string $crv) : ?array
    {
        if ($crs == parent::ENUM_ANY)
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ::events WHERE `holidayId` <> 0'))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_creature WHERE `eventEntry` IN %in', $eventIds))
                    return ['s.guid', $cGuids];

            return [0];
        }
        else if ($crs == parent::ENUM_NONE)
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ::events WHERE `holidayId` <> 0'))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_creature WHERE `eventEntry` IN %in', $eventIds))
                    return [DB::OR, ['s.guid', $cGuids, '!'], ['s.guid', null]];

            return [0];
        }
        else if (in_array($crs, self::$enums[$cr]))
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ::events WHERE `holidayId` = %i', $crs))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT `guid` FROM `game_event_creature` WHERE `eventEntry` IN %in', $eventIds))
                    return ['s.guid', $cGuids];

            return [0];
        }

        return null;
    }

    protected function cbMoneyDrop(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        return [DB::AND, ['((minGold + maxGold) / 2)', $crv, $crs]];
    }

    protected function cbQuestRelation(int $cr, int $crs, string $crv, string $field, int $val) : ?array
    {
        switch ($crs)
        {
            case 1:                                 // any
                return [DB::AND, ['qse.method', $val, '&'], ['qse.questId', null, '!']];
            case 2:                                 // alliance
                return [DB::AND, ['qse.method', $val, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', ChrRace::MASK_HORDE, '&'], 0], ['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&']];
            case 3:                                 // horde
                return [DB::AND, ['qse.method', $val, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', ChrRace::MASK_HORDE, '&']];
            case 4:                                 // both
                return [DB::AND, ['qse.method', $val, '&'], ['qse.questId', null, '!'], [DB::OR, [DB::AND, ['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ['qt.reqRaceMask', ChrRace::MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
            case 5:                                 // none
                $this->extraOpts['ct']['h'][] = $field.' = 0';
                return [1];
        }

        return null;
    }

    protected function cbHealthMana(int $cr, int $crs, string $crv, string $minField, string $maxField) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        // remap OP for this special case
        switch ($crs)
        {
            case '=':                               // min > max is totally possible
                $this->extraOpts['ct']['h'][] = $minField.' = '.$maxField.' AND '.$minField.' = '.$crv;
                break;
            case '>':
            case '>=':
            case '<':
            case '<=':
                $this->extraOpts['ct']['h'][] = 'IF('.$minField.' > '.$maxField.', '.$maxField.', '.$minField.') '.$crs.' '.$crv;
                break;
        }


        return [1];                                 // always true, use post-filter
    }

    protected function cbSpecialSkinLoot(int $cr, int $crs, string $crv, int $typeFlag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;


        if ($crs)
            return [DB::AND, ['skinLootId', 0, '>'], ['typeFlags', $typeFlag, '&']];
        else
            return [DB::OR, ['skinLootId', 0], [['typeFlags', $typeFlag, '&'], 0]];
    }

    protected function cbRegularSkinLoot(int $cr, int $crs, string $crv, int $typeFlag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [DB::AND, ['skinLootId', 0, '>'], [['typeFlags', $typeFlag, '&'], 0]];
        else
            return [DB::OR, ['skinLootId', 0], ['typeFlags', $typeFlag, '&']];
    }

    protected function cbReputation(int $cr, int $crs, string $crv, int $op) : ?array
    {
        if (!in_array($crs, self::$enums[$cr]))
            return null;

        if (!$this->int2Op($op))
            return null;

        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ::factions WHERE `id` = %i', $crs))
            $this->fiReputationCols[] = [$crs, Util::localizedString($_, 'name')];

        if ($cIds = DB::World()->selectCol('SELECT `creature_id` FROM creature_onkill_reputation WHERE (`RewOnKillRepFaction1` = %i AND `RewOnKillRepValue1` '.$op.' 0) OR (`RewOnKillRepFaction2` = %i AND `RewOnKillRepValue2` '.$op.' 0)', $crs, $crs))
            return ['id', $cIds];
        else
            return [0];
    }

    protected function cbFaction(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (!in_array($crs, self::$enums[$cr]))
            return null;

        $facTpls = [];
        $facs    = new FactionContainer(array(DB::OR, ['parentFactionId', $crs], ['id', $crs]));
        foreach ($facs->iterate() as $entry)
            $facTpls = array_merge($facTpls, $entry->templateIds);

        return $facTpls ? ['faction', $facTpls] : [0];
    }

    protected function cbHasLocation(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        return ['s.typeId', null, $crs ? '!' : null];
    }
}

?>
