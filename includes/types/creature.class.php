<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends BaseType
{
    use spawnHelper;

    public static   $type      = Type::NPC;
    public static   $brickFile = 'npc';
    public static   $dataTable = '?_creature';

    protected       $queryBase = 'SELECT ct.*, ct.id AS ARRAY_KEY FROM ?_creature ct';
    public          $queryOpts = array(
                        'ct'     => [['ft', 'qse', 'dct1', 'dct2', 'dct3'], 's' => ', IFNULL(dct1.id, IFNULL(dct2.id, IFNULL(dct3.id, 0))) AS parentId, IFNULL(dct1.name_loc0, IFNULL(dct2.name_loc0, IFNULL(dct3.name_loc0, ""))) AS parent_loc0, IFNULL(dct1.name_loc2, IFNULL(dct2.name_loc2, IFNULL(dct3.name_loc2, ""))) AS parent_loc2, IFNULL(dct1.name_loc3, IFNULL(dct2.name_loc3, IFNULL(dct3.name_loc3, ""))) AS parent_loc3, IFNULL(dct1.name_loc4, IFNULL(dct2.name_loc4, IFNULL(dct3.name_loc4, ""))) AS parent_loc4, IFNULL(dct1.name_loc6, IFNULL(dct2.name_loc6, IFNULL(dct3.name_loc6, ""))) AS parent_loc6, IFNULL(dct1.name_loc8, IFNULL(dct2.name_loc8, IFNULL(dct3.name_loc8, ""))) AS parent_loc8, IF(dct1.difficultyEntry1 = ct.id, 1, IF(dct2.difficultyEntry2 = ct.id, 2, IF(dct3.difficultyEntry3 = ct.id, 3, 0))) AS difficultyMode'],
                        'dct1'   => ['j' => ['?_creature dct1 ON ct.cuFlags & 0x02 AND dct1.difficultyEntry1 = ct.id', true]],
                        'dct2'   => ['j' => ['?_creature dct2 ON ct.cuFlags & 0x02 AND dct2.difficultyEntry2 = ct.id', true]],
                        'dct3'   => ['j' => ['?_creature dct3 ON ct.cuFlags & 0x02 AND dct3.difficultyEntry3 = ct.id', true]],
                        'ft'     => ['j' => '?_factiontemplate ft ON ft.id = ct.faction', 's' => ', ft.A, ft.H, ft.factionId'],
                        'qse'    => ['j' => ['?_quests_startend qse ON qse.type = 1 AND qse.typeId = ct.id', true], 's' => ', IF(min(qse.method) = 1 OR max(qse.method) = 3, 1, 0) AS startsQuests, IF(min(qse.method) = 2 OR max(qse.method) = 3, 1, 0) AS endsQuests', 'g' => 'ct.id'],
                        'qt'     => ['j' => '?_quests qt ON qse.questId = qt.id'],
                        's'      => ['j' => ['?_spawns s ON s.type = 1 AND s.typeId = ct.id', true]]
                    );

    public function __construct($conditions = [], $miscData = null)
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

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_creature WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return null;

        $level = '??';
        $type  = $this->curTpl['type'];
        $row3  = [Lang::game('level')];
        $fam   = $this->curTpl['family'];

        if (!($this->curTpl['typeFlags'] & 0x4))
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

    public function getRandomModelId()
    {
        // dwarf?? [null, 30754, 30753, 30755, 30736]
        // totems use hardcoded models, tauren model is base
        $totems = [null, 4589, 4588, 4587, 4590];           // slot => modelId
        $data   = [];

        for ($i = 1; $i < 5; $i++)
            if ($_ = $this->curTpl['displayId'.$i])
                $data[] = $_;

        if (count($data) == 1 && ($slotId = array_search($data[0], $totems)))
            $data = DB::World()->selectCol('SELECT DisplayId FROM player_totem_model WHERE TotemSlot = ?d', $slotId);

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

    public function isBoss()
    {
        return ($this->curTpl['cuFlags'] & NPC_CU_INSTANCE_BOSS) || ($this->curTpl['typeFlags'] & 0x4 && $this->curTpl['rank']);
    }

    public function getListviewData($addInfoMask = 0x0)
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
            $rewRep = DB::World()->selectCol('
                SELECT creature_id AS ARRAY_KEY, RewOnKillRepFaction1 AS ARRAY_KEY2, RewOnKillRepValue1 FROM creature_onkill_reputation WHERE creature_id IN (?a) AND RewOnKillRepFaction1 > 0 UNION
                SELECT creature_id AS ARRAY_KEY, RewOnKillRepFaction2 AS ARRAY_KEY2, RewOnKillRepValue2 FROM creature_onkill_reputation WHERE creature_id IN (?a) AND RewOnKillRepFaction2 > 0',
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

    public function getJSGlobals($addMask = 0)
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

    public function addRewardsToJScript(&$refs) { }


}


class CreatureListFilter extends Filter
{
    public    $extraOpts     = null;
    protected $enums         = array(
         3 => parent::ENUM_FACTION,                         // faction
         6 => parent::ENUM_ZONE,                            // foundin
        42 => parent::ENUM_FACTION,                         // increasesrepwith
        43 => parent::ENUM_FACTION,                         // decreasesrepwith
        38 => parent::ENUM_EVENT                            // relatedevent
    );

    protected $genericFilter = array(
         1 => [FILTER_CR_CALLBACK, 'cbHealthMana',      'healthMax',               'healthMin'], // health [num]
         2 => [FILTER_CR_CALLBACK, 'cbHealthMana',      'manaMin',                 'manaMax'  ], // mana [num]
         3 => [FILTER_CR_CALLBACK, 'cbFaction',         null,                      null       ], // faction [enum]
         5 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_REPAIRER                     ], // canrepair
         6 => [FILTER_CR_ENUM,     's.areaId',          false,                     true       ], // foundin
         7 => [FILTER_CR_CALLBACK, 'cbQuestRelation',   'startsQuests',            0x1        ], // startsquest [enum]
         8 => [FILTER_CR_CALLBACK, 'cbQuestRelation',   'endsQuests',              0x2        ], // endsquest [enum]
         9 => [FILTER_CR_BOOLEAN,  'lootId',                                                  ], // lootable
        10 => [FILTER_CR_CALLBACK, 'cbRegularSkinLoot', NPC_TYPEFLAG_SPECIALLOOT              ], // skinnable [yn]
        11 => [FILTER_CR_BOOLEAN,  'pickpocketLootId',                                        ], // pickpocketable
        12 => [FILTER_CR_CALLBACK, 'cbMoneyDrop',       null,                      null       ], // averagemoneydropped [op] [int]
        15 => [FILTER_CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_HERBLOOT,     null       ], // gatherable [yn]
        16 => [FILTER_CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_MININGLOOT,   null       ], // minable [yn]
        18 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_AUCTIONEER                   ], // auctioneer
        19 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_BANKER                       ], // banker
        20 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_BATTLEMASTER                 ], // battlemaster
        21 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_FLIGHT_MASTER                ], // flightmaster
        22 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_GUILD_MASTER                 ], // guildmaster
        23 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_INNKEEPER                    ], // innkeeper
        24 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_CLASS_TRAINER                ], // talentunlearner
        25 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_GUILD_MASTER                 ], // tabardvendor
        27 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_STABLE_MASTER                ], // stablemaster
        28 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_TRAINER                      ], // trainer
        29 => [FILTER_CR_FLAG,     'npcflag',           NPC_FLAG_VENDOR                       ], // vendor
        31 => [FILTER_CR_FLAG,     'cuFlags',           CUSTOM_HAS_SCREENSHOT                 ], // hasscreenshots
        32 => [FILTER_CR_FLAG,     'cuFlags',           NPC_CU_INSTANCE_BOSS                  ], // instanceboss
        33 => [FILTER_CR_FLAG,     'cuFlags',           CUSTOM_HAS_COMMENT                    ], // hascomments
        34 => [FILTER_CR_STRING,   'modelId',           STR_MATCH_EXACT | STR_ALLOW_SHORT     ], // usemodel [str] (wants int in string fmt <_<)
        35 => [FILTER_CR_STRING,   'textureString'                                            ], // useskin [str]
        37 => [FILTER_CR_NUMERIC,  'id',                NUM_CAST_INT,              true       ], // id
        38 => [FILTER_CR_CALLBACK, 'cbRelEvent',        null,                      null       ], // relatedevent [enum]
        40 => [FILTER_CR_FLAG,     'cuFlags',           CUSTOM_HAS_VIDEO                      ], // hasvideos
        41 => [FILTER_CR_NYI_PH,   1,                   null                                  ], // haslocation [yn] [staff]
        42 => [FILTER_CR_CALLBACK, 'cbReputation',      '>',                       null       ], // increasesrepwith [enum]
        43 => [FILTER_CR_CALLBACK, 'cbReputation',      '<',                       null       ], // decreasesrepwith [enum]
        44 => [FILTER_CR_CALLBACK, 'cbSpecialSkinLoot', NPC_TYPEFLAG_ENGINEERLOOT, null       ]  // salvageable [yn]
    );

    protected $inputFields = array(
        'cr'    => [FILTER_V_LIST,     [[1, 3],[5, 12], 15, 16, [18, 25], [27, 29], [31, 35], 37, 38, [40, 44]], true ], // criteria ids
        'crs'   => [FILTER_V_LIST,     [FILTER_ENUM_NONE, FILTER_ENUM_ANY, [0, 9999]],                           true ], // criteria operators
        'crv'   => [FILTER_V_REGEX,    parent::PATTERN_CRV,                                                      true ], // criteria values - only printable chars, no delimiter
        'na'    => [FILTER_V_REGEX,    parent::PATTERN_NAME,                                                     false], // name / subname - only printable chars, no delimiter
        'ex'    => [FILTER_V_EQUAL,    'on',                                                                     false], // also match subname
        'ma'    => [FILTER_V_EQUAL,    1,                                                                        false], // match any / all filter
        'fa'    => [FILTER_V_CALLBACK, 'cbPetFamily',                                                            true ], // pet family [list]  -  cat[0] == 1
        'minle' => [FILTER_V_RANGE,    [1, 99],                                                                  false], // min level [int]
        'maxle' => [FILTER_V_RANGE,    [1, 99],                                                                  false], // max level [int]
        'cl'    => [FILTER_V_RANGE,    [0, 4],                                                                   true ], // classification [list]
        'ra'    => [FILTER_V_LIST,     [-1, 0, 1],                                                               false], // react alliance [int]
        'rh'    => [FILTER_V_LIST,     [-1, 0, 1],                                                               false]  // react horde [int]
    );

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        // name [str]
        if (isset($_v['na']))
        {
            $_ = [];
            if (isset($_v['ex']) && $_v['ex'] == 'on')
                $_ = $this->modularizeString(['name_loc'.User::$localeId, 'subname_loc'.User::$localeId]);
            else
                $_ = $this->modularizeString(['name_loc'.User::$localeId]);

            if ($_)
                $parts[] = $_;
        }

        // pet family [list]
        if (isset($_v['fa']))
            $parts[] = ['family', $_v['fa']];

        // creatureLevel min [int]
        if (isset($_v['minle']))
            $parts[] = ['minLevel', $_v['minle'], '>='];

        // creatureLevel max [int]
        if (isset($_v['maxle']))
            $parts[] = ['maxLevel', $_v['maxle'], '<='];

        // classification [list]
        if (isset($_v['cl']))
            $parts[] = ['rank', $_v['cl']];

        // react Alliance [int]
        if (isset($_v['ra']))
            $parts[] = ['ft.A', $_v['ra']];

        // react Horde [int]
        if (isset($_v['rh']))
            $parts[] = ['ft.H', $_v['rh']];

        return $parts;
    }

    protected function cbPetFamily(&$val)
    {
        if (!$this->parentCats || $this->parentCats[0] != 1)
            return false;

        if (!Util::checkNumeric($val, NUM_REQ_INT))
            return false;

        $type  = FILTER_V_LIST;
        $valid = [[1, 9], 11, 12, 20, 21, [24, 27], [30, 35], [37, 39], [41, 46]];

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbRelEvent($cr)
    {
        if ($cr[1] == FILTER_ENUM_ANY)
        {
            $eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId <> 0');
            $cGuids   = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_creature WHERE eventEntry IN (?a)', $eventIds);
            return ['s.guid', $cGuids];
        }
        else if ($cr[1] == FILTER_ENUM_NONE)
        {
            $eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId <> 0');
            $cGuids   = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_creature WHERE eventEntry IN (?a)', $eventIds);
            return ['s.guid', $cGuids, '!'];
        }
        else if (in_array($cr[1], $this->enums[$cr[0]]))
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId = ?d', $cr[1]))
                if ($cGuids   = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_creature WHERE eventEntry IN (?a)', $eventIds))
                    return ['s.guid', $cGuids];

            return [0];
        }

        return false;
    }

    protected function cbMoneyDrop($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        return ['AND', ['((minGold + maxGold) / 2)', $cr[2], $cr[1]]];
    }

    protected function cbQuestRelation($cr, $field, $val)
    {
        switch ($cr[1])
        {
            case 1:                                 // any
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!']];
            case 2:                                 // alliance
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&']];
            case 3:                                 // horde
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']];
            case 4:                                 // both
                return ['AND', ['qse.method', $val, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
            case 5:                                 // none
                $this->extraOpts['ct']['h'][] = $field.' = 0';
                return [1];
        }

        return false;
    }

    protected function cbHealthMana($cr, $minField, $maxField)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        // remap OP for this special case
        switch ($cr[1])
        {
            case '=':                               // min > max is totally possible
                $this->extraOpts['ct']['h'][] = $minField.' = '.$maxField.' AND '.$minField.' = '.$cr[2];
                break;
            case '>':
            case '>=':
            case '<':
            case '<=':
                $this->extraOpts['ct']['h'][] = 'IF('.$minField.' > '.$maxField.', '.$maxField.', '.$minField.') '.$cr[1].' '.$cr[2];
                break;
        }


        return [1];                                 // always true, use post-filter
    }

    protected function cbSpecialSkinLoot($cr, $typeFlag)
    {
        if (!$this->int2Bool($cr[1]))
            return false;


        if ($cr[1])
            return ['AND', ['skinLootId', 0, '>'], ['typeFlags', $typeFlag, '&']];
        else
            return ['OR', ['skinLootId', 0], [['typeFlags', $typeFlag, '&'], 0]];
    }

    protected function cbRegularSkinLoot($cr, $typeFlag)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        if ($cr[1])
            return ['AND', ['skinLootId', 0, '>'], [['typeFlags', $typeFlag, '&'], 0]];
        else
            return ['OR', ['skinLootId', 0], ['typeFlags', $typeFlag, '&']];
    }

    protected function cbReputation($cr, $op)
    {
        if (!in_array($cr[1], $this->enums[$cr[0]]))
            return false;

        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_factions WHERE id = ?d', $cr[1]))
            $this->formData['reputationCols'][] = [$cr[1], Util::localizedString($_, 'name')];

        if ($cIds = DB::World()->selectCol('SELECT creature_id FROM creature_onkill_reputation WHERE (RewOnKillRepFaction1 = ?d AND RewOnKillRepValue1 '.$op.' 0) OR (RewOnKillRepFaction2 = ?d AND RewOnKillRepValue2 '.$op.' 0)', $cr[1], $cr[1]))
            return ['id', $cIds];
        else
            return [0];
    }

    protected function cbFaction($cr)
    {
        if (!Util::checkNumeric($cr[1], NUM_REQ_INT))
            return false;

        if (!in_array($cr[1], $this->enums[$cr[0]]))
            return false;


        $facTpls = [];
        $facs    = new FactionList(array('OR', ['parentFactionId', $cr[1]], ['id', $cr[1]]));
        foreach ($facs->iterate() as $__)
            $facTpls = array_merge($facTpls, $facs->getField('templateIds'));

        return $facTpls ? ['faction', $facTpls] : [0];
    }
}

?>
