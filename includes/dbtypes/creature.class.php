<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends DBTypeList
{
    use spawnHelper;

    public static int    $type      = Type::NPC;
    public static string $brickFile = 'npc';
    public static string $dataTable = '?_creature';

    protected string $queryBase = 'SELECT ct.*, ct.`id` AS ARRAY_KEY FROM ?_creature ct';
    public    array  $queryOpts = array(
                        'ct'   => [['ft', 'qse', 'dct1', 'dct2', 'dct3'], 's' => ', IFNULL(dct1.`id`, IFNULL(dct2.`id`, IFNULL(dct3.`id`, 0))) AS "parentId", IFNULL(dct1.`name_loc0`, IFNULL(dct2.`name_loc0`, IFNULL(dct3.`name_loc0`, ""))) AS "parent_loc0", IFNULL(dct1.`name_loc2`, IFNULL(dct2.`name_loc2`, IFNULL(dct3.`name_loc2`, ""))) AS "parent_loc2", IFNULL(dct1.`name_loc3`, IFNULL(dct2.`name_loc3`, IFNULL(dct3.`name_loc3`, ""))) AS "parent_loc3", IFNULL(dct1.`name_loc4`, IFNULL(dct2.`name_loc4`, IFNULL(dct3.`name_loc4`, ""))) AS "parent_loc4", IFNULL(dct1.`name_loc6`, IFNULL(dct2.`name_loc6`, IFNULL(dct3.`name_loc6`, ""))) AS "parent_loc6", IFNULL(dct1.name_loc8, IFNULL(dct2.`name_loc8`, IFNULL(dct3.`name_loc8`, ""))) AS "parent_loc8", IF(dct1.`difficultyEntry1` = ct.`id`, 1, IF(dct2.`difficultyEntry2` = ct.`id`, 2, IF(dct3.`difficultyEntry3` = ct.`id`, 3, 0))) AS "difficultyMode"'],
                        'dct1' => ['j' => ['?_creature dct1 ON ct.`cuFlags` & 0x02 AND dct1.`difficultyEntry1` = ct.`id`', true]],
                        'dct2' => ['j' => ['?_creature dct2 ON ct.`cuFlags` & 0x02 AND dct2.`difficultyEntry2` = ct.`id`', true]],
                        'dct3' => ['j' => ['?_creature dct3 ON ct.`cuFlags` & 0x02 AND dct3.`difficultyEntry3` = ct.`id`', true]],
                        'ft'   => ['j' => '?_factiontemplate ft ON ft.`id` = ct.`faction`', 's' => ', ft.`factionId`, IFNULL(ft.`A`, 0) AS "A", IFNULL(ft.`H`, 0) AS "H"'],
                        'qse'  => ['j' => ['?_quests_startend qse ON qse.`type` = 1 AND qse.`typeId` = ct.id', true], 's' => ', IF(MIN(qse.`method`) = 1 OR MAX(qse.`method`) = 3, 1, 0) AS "startsQuests", IF(MIN(qse.`method`) = 2 OR MAX(qse.`method`) = 3, 1, 0) AS "endsQuests"', 'g' => 'ct.`id`'],
                        'qt'   => ['j' => '?_quests qt ON qse.`questId` = qt.`id`'],
                        's'    => ['j' => ['?_spawns s ON s.`type` = 1 AND s.`typeId` = ct.`id`', true]]
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        foreach ($this->iterate() as $_id => &$curTpl)
        {
            // check for attackspeeds
            if (!$curTpl['atkSpeed'])
                $curTpl['atkSpeed'] = 2.0;
            else
                $curTpl['atkSpeed'] /= 1000;

            if (!$curTpl['rngAtkSpeed'])
                $curTpl['rngAtkSpeed'] = 2.0;
            else
                $curTpl['rngAtkSpeed'] /= 1000;
        }
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

        $level = '??';
        $type  = $this->curTpl['type'];
        $row3  = [Lang::game('level')];
        $fam   = $this->curTpl['family'];

        if (!($this->curTpl['typeFlags'] & NPC_TYPEFLAG_BOSS_MOB))
        {
            $level = $this->curTpl['minLevel'];
            if ($level != $this->curTpl['maxLevel'])
                $level .= ' - '.$this->curTpl['maxLevel'];
        }
        else
            $level = '??';

        $row3[] = $level;

        if ($type)
            $row3[] = Lang::game('ct', $type);

        if ($_ = Lang::npc('rank', $this->curTpl['rank']))
            $row3[] = '('.$_.')';

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.Util::htmlEscape($this->getField('name', true)).'</b></td></tr>';

        if ($sn = $this->getField('subname', true))
            $x .= '<tr><td>'.Util::htmlEscape($sn).'</td></tr>';

        $x .= '<tr><td>'.implode(' ', $row3).'</td></tr>';

        if ($type == 1 && $fam)                             // 1: Beast
            $x .= '<tr><td>'.Lang::game('fa', $fam).'</td></tr>';

        $fac = new FactionList(array([['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0], ['id', (int)$this->getField('factionId')]));
        if (!$fac->error)
            $x .= '<tr><td>'.$fac->getField('name', true).'</td></tr>';

        $x .= '</table>';

        return $x;
    }

    public function getRandomModelId() : int
    {
        // dwarf?? [null, 30754, 30753, 30755, 30736]
        // totems use hardcoded models, tauren model is base
        $totems = [null, 4589, 4588, 4587, 4590];           // slot => modelId
        $data   = [];

        for ($i = 1; $i < 5; $i++)
            if ($_ = $this->curTpl['displayId'.$i])
                $data[] = $_;

        if (count($data) == 1 && ($slotId = array_search($data[0], $totems)))
            $data = DB::World()->selectCol('SELECT `DisplayId` FROM player_totem_model WHERE `TotemSlot` = ?d', $slotId);

        return !$data ? 0 : $data[array_rand($data)];
    }

    public function getBaseStats(string $type) : array
    {
        // i'm aware of the BaseVariance/RangedVariance fields ... i'm just totaly unsure about the whole damage calculation
        switch ($type)
        {
            case 'health':
                $hMin = $this->getField('healthMin');
                $hMax = $this->getField('healthMax');
                return [$hMin, $hMax];
            case 'power':
                $mMin = $this->getField('manaMin');
                $mMax = $this->getField('manaMax');
                return [$mMin, $mMax];
            case 'armor':
                $aMin = $this->getField('armorMin');
                $aMax = $this->getField('armorMax');
                return [$aMin, $aMax];
            case 'melee':
                $mleMin = ($this->getField('dmgMin')       + ($this->getField('mleAtkPwrMin') / 14)) * $this->getField('dmgMultiplier') * $this->getField('atkSpeed');
                $mleMax = ($this->getField('dmgMax') * 1.5 + ($this->getField('mleAtkPwrMax') / 14)) * $this->getField('dmgMultiplier') * $this->getField('atkSpeed');
                return [$mleMin, $mleMax];
            case 'ranged':
                $rngMin = ($this->getField('dmgMin')       + ($this->getField('rngAtkPwrMin') / 14)) * $this->getField('dmgMultiplier') * $this->getField('rngAtkSpeed');
                $rngMax = ($this->getField('dmgMax') * 1.5 + ($this->getField('rngAtkPwrMax') / 14)) * $this->getField('dmgMultiplier') * $this->getField('rngAtkSpeed');
                return [$rngMin, $rngMax];
            case 'resistance':
                $r = [];
                for ($i = SPELL_SCHOOL_HOLY; $i < SPELL_SCHOOL_ARCANE+1; $i++)
                    $r[$i] = $this->getField('resistance'.$i);

                return $r;
            default:
                return [];
        }
    }

    public function isBoss() : bool
    {
        return ($this->curTpl['cuFlags'] & NPC_CU_INSTANCE_BOSS) || ($this->curTpl['typeFlags'] & NPC_TYPEFLAG_BOSS_MOB && $this->curTpl['rank']);
    }

    public function isMineable() : bool
    {
        return $this->curTpl['skinLootId'] && ($this->curTpl['typeFlags'] & NPC_TYPEFLAG_SKIN_WITH_MINING);
    }

    public function isGatherable() : bool
    {
        return $this->curTpl['skinLootId'] && ($this->curTpl['typeFlags'] & NPC_TYPEFLAG_SKIN_WITH_HERBALISM);
    }

    public function isSalvageable() : bool
    {
        return $this->curTpl['skinLootId'] && ($this->curTpl['typeFlags'] & NPC_TYPEFLAG_SKIN_WITH_ENGINEERING);
    }

    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        /* looks like this data differs per occasion
        *
        * NPCINFO_TAMEABLE (0x1): include texture & react
        * NPCINFO_MODEL    (0x2):
        * NPCINFO_REP      (0x4): include repreward
        */

        $data   = [];
        $rewRep = [];

        if ($addInfoMask & NPCINFO_REP && $this->getFoundIDs())
        {
            $rewRep = DB::World()->selectCol(
               'SELECT `creature_id` AS ARRAY_KEY, `RewOnKillRepFaction1` AS ARRAY_KEY2, `RewOnKillRepValue1` FROM creature_onkill_reputation WHERE `creature_id` IN (?a) AND `RewOnKillRepFaction1` > 0 UNION
                SELECT `creature_id` AS ARRAY_KEY, `RewOnKillRepFaction2` AS ARRAY_KEY2, `RewOnKillRepValue2` FROM creature_onkill_reputation WHERE `creature_id` IN (?a) AND `RewOnKillRepFaction2` > 0',
                $this->getFoundIDs(),
                $this->getFoundIDs()
            );
        }


        foreach ($this->iterate() as $__)
        {
            if ($addInfoMask & NPCINFO_MODEL)
            {
                $texStr = strtolower($this->curTpl['textureString']);

                if (isset($data[$texStr]))
                {
                    if ($data[$texStr]['minLevel'] > $this->curTpl['minLevel'])
                        $data[$texStr]['minLevel'] = $this->curTpl['minLevel'];

                    if ($data[$texStr]['maxLevel'] < $this->curTpl['maxLevel'])
                        $data[$texStr]['maxLevel'] = $this->curTpl['maxLevel'];

                    $data[$texStr]['count']++;
                }
                else
                    $data[$texStr] = array(
                        'family'    => $this->curTpl['family'],
                        'minLevel'  => $this->curTpl['minLevel'],
                        'maxLevel'  => $this->curTpl['maxLevel'],
                        'modelId'   => $this->curTpl['modelId'],
                        'displayId' => $this->curTpl['displayId1'],
                        'skin'      => $texStr,
                        'count'     => 1
                    );
            }
            else
            {
                $data[$this->id] = array(
                    'family'         => $this->curTpl['family'],
                    'minlevel'       => $this->curTpl['minLevel'],
                    'maxlevel'       => $this->curTpl['maxLevel'],
                    'id'             => $this->id,
                    'boss'           => $this->isBoss() ? 1 : 0,
                    'classification' => $this->curTpl['rank'],
                    'location'       => $this->getSpawns(SPAWNINFO_ZONES),
                    'name'           => $this->getField('name', true),
                    'type'           => $this->curTpl['type'],
                    'react'          => [$this->curTpl['A'], $this->curTpl['H']],
                );


                if ($this->getField('startsQuests'))
                    $data[$this->id]['hasQuests'] = 1;

                if ($_ = $this->getField('subname', true))
                    $data[$this->id]['tag'] = $_;

                if ($addInfoMask & NPCINFO_TAMEABLE)        // only first skin of first model ... we're omitting potentially 11 skins here .. but the lv accepts only one .. w/e
                    $data[$this->id]['skin'] = $this->curTpl['textureString'];

                if ($addInfoMask & NPCINFO_REP)
                {
                    $data[$this->id]['reprewards'] = [];
                    if ($rewRep[$this->id])
                        foreach ($rewRep[$this->id] as $fac => $val)
                            $data[$this->id]['reprewards'][] = [$fac, $val];
                }
            }
        }

        ksort($data);
        return $data;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::NPC][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function getSourceData(int $id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($id && $id != $this->id)
                continue;

            $data[$this->id] = array(
                'n'  => $this->getField('parentId') ? $this->getField('parent', true) : $this->getField('name', true),
                't'  => Type::NPC,
                'ti' => $this->getField('parentId') ?: $this->id
            );
        }

        return $data;
    }
}


class CreatureListFilter extends Filter
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
        34 => [parent::CR_STRING,   'modelId',           STR_MATCH_EXACT | STR_ALLOW_SHORT             ], // usemodel [str] (wants int in string fmt <_<)
        35 => [parent::CR_STRING,   'textureString'                                                    ], // useskin [str]
        37 => [parent::CR_NUMERIC,  'id',                NUM_CAST_INT,                       true      ], // id
        38 => [parent::CR_CALLBACK, 'cbRelEvent',        null,                               null      ], // relatedevent [enum]
        40 => [parent::CR_FLAG,     'cuFlags',           CUSTOM_HAS_VIDEO                              ], // hasvideos
        41 => [parent::CR_NYI_PH,   1,                   null                                          ], // haslocation [yn] [staff]
        42 => [parent::CR_CALLBACK, 'cbReputation',      '>',                                null      ], // increasesrepwith [enum]
        43 => [parent::CR_CALLBACK, 'cbReputation',      '<',                                null      ], // decreasesrepwith [enum]
        44 => [parent::CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_SKIN_WITH_ENGINEERING, null      ]  // salvageable [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_LIST,     [[1, 3],[5, 12], 15, 16, [18, 25], [27, 29], [31, 35], 37, 38, [40, 44]], true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 9999]],                         true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                                                      true ], // criteria values - only printable chars, no delimiter
        'na'    => [parent::V_REGEX,    parent::PATTERN_NAME,                                                     false], // name / subname - only printable chars, no delimiter
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
            if ($_v['ex'] == 'on')
                if ($_ = $this->tokenizeString(['subname_loc'.Lang::getLocale()->value]))
                    $parts[] = $_;

            if ($_ = $this->buildMatchLookup(['name_loc'.Lang::getLocale()->value]))
            {
                if ($parts)
                    $parts = ['OR', $_, ...$parts];
                else
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
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_events WHERE `holidayId` <> 0'))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_creature WHERE `eventEntry` IN (?a)', $eventIds))
                    return ['s.guid', $cGuids];

            return [0];
        }
        else if ($crs == parent::ENUM_NONE)
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_events WHERE `holidayId` <> 0'))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_creature WHERE `eventEntry` IN (?a)', $eventIds))
                    return ['s.guid', $cGuids, '!'];

            return [0];
        }
        else if (in_array($crs, self::$enums[$cr]))
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_events WHERE `holidayId` = ?d', $crs))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT `guid` FROM `game_event_creature` WHERE `eventEntry` IN (?a)', $eventIds))
                    return ['s.guid', $cGuids];

            return [0];
        }

        return null;
    }

    protected function cbMoneyDrop(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        return ['AND', ['((minGold + maxGold) / 2)', $crv, $crs]];
    }

    protected function cbQuestRelation(int $cr, int $crs, string $crv, $field, $val) : ?array
    {
        switch ($crs)
        {
            case 1:                                 // any
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!']];
            case 2:                                 // alliance
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', ChrRace::MASK_HORDE, '&'], 0], ['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&']];
            case 3:                                 // horde
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', ChrRace::MASK_HORDE, '&']];
            case 4:                                 // both
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ['qt.reqRaceMask', ChrRace::MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
            case 5:                                 // none
                $this->extraOpts['ct']['h'][] = $field.' = 0';
                return [1];
        }

        return null;
    }

    protected function cbHealthMana(int $cr, int $crs, string $crv, $minField, $maxField) : ?array
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

    protected function cbSpecialSkinLoot(int $cr, int $crs, string $crv, $typeFlag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;


        if ($crs)
            return ['AND', ['skinLootId', 0, '>'], ['typeFlags', $typeFlag, '&']];
        else
            return ['OR', ['skinLootId', 0], [['typeFlags', $typeFlag, '&'], 0]];
    }

    protected function cbRegularSkinLoot(int $cr, int $crs, string $crv, $typeFlag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return ['AND', ['skinLootId', 0, '>'], [['typeFlags', $typeFlag, '&'], 0]];
        else
            return ['OR', ['skinLootId', 0], ['typeFlags', $typeFlag, '&']];
    }

    protected function cbReputation(int $cr, int $crs, string $crv, $op) : ?array
    {
        if (!in_array($crs, self::$enums[$cr]))
            return null;

        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_factions WHERE `id` = ?d', $crs))
            $this->fiReputationCols[] = [$crs, Util::localizedString($_, 'name')];

        if ($cIds = DB::World()->selectCol('SELECT `creature_id` FROM creature_onkill_reputation WHERE (`RewOnKillRepFaction1` = ?d AND `RewOnKillRepValue1` '.$op.' 0) OR (`RewOnKillRepFaction2` = ?d AND `RewOnKillRepValue2` '.$op.' 0)', $crs, $crs))
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
        $facs    = new FactionList(array('OR', ['parentFactionId', $crs], ['id', $crs]));
        foreach ($facs->iterate() as $__)
            $facTpls = array_merge($facTpls, $facs->getField('templateIds'));

        return $facTpls ? ['faction', $facTpls] : [0];
    }
}

?>
