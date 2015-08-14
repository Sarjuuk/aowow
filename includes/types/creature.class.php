<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends BaseType
{
    use spawnHelper;

    public static $type      = TYPE_NPC;
    public static $brickFile = 'creature';

    protected     $queryBase = 'SELECT ct.*, ct.id AS ARRAY_KEY FROM ?_creature ct';
    public        $queryOpts = array(
                        'ct'     => [['ft', 'qse', 'dct1', 'dct2', 'dct3'], 's' => ', IFNULL(dct1.id, IFNULL(dct2.id, IFNULL(dct3.id, 0))) AS parentId, IFNULL(dct1.name_loc0, IFNULL(dct2.name_loc0, IFNULL(dct3.name_loc0, ""))) AS parent_loc0, IFNULL(dct1.name_loc2, IFNULL(dct2.name_loc2, IFNULL(dct3.name_loc2, ""))) AS parent_loc2, IFNULL(dct1.name_loc3, IFNULL(dct2.name_loc3, IFNULL(dct3.name_loc3, ""))) AS parent_loc3, IFNULL(dct1.name_loc6, IFNULL(dct2.name_loc6, IFNULL(dct3.name_loc6, ""))) AS parent_loc6, IFNULL(dct1.name_loc8, IFNULL(dct2.name_loc8, IFNULL(dct3.name_loc8, ""))) AS parent_loc8, IF(dct1.difficultyEntry1 = ct.id, 1, IF(dct2.difficultyEntry2 = ct.id, 2, IF(dct3.difficultyEntry3 = ct.id, 3, 0))) AS difficultyMode'],
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
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_creature WHERE id = ?d', $id);
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
        $x .= '<tr><td><b class="q">'.$this->getField('name', true).'</b></td></tr>';

        if ($sn = $this->getField('subname', true))
            $x .= '<tr><td>'.$sn.'</td></tr>';

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
        // totems use hardcoded models, tauren model is base
        $totems = array(                                    // tauren => [orc, dwarf(?!), troll, tauren, draenei]
            4589 => [30758, 30754, 30762, 4589, 19074],     // fire
            4588 => [30757, 30753, 30761, 4588, 19073],     // earth
            4587 => [30759, 30755, 30763, 4587, 19075],     // water
            4590 => [30756, 30736, 30760, 4590, 19071],     // air
        );

        $data = [];

        for ($i = 1; $i < 5; $i++)
            if ($_ = $this->curTpl['displayId'.$i])
                $data[] = $_;

        if (count($data) == 1 && in_array($data[0], array_keys($totems)))
            $data = $totems[$data[0]];

        return !$data ? 0 : $data[array_rand($data)];
    }

    public function getBaseStats($type)
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
            default:
                return [0, 0];
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

        if ($addInfoMask & NPCINFO_REP)
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
            $data[TYPE_NPC][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'n'  => $this->getField('parentId') ? $this->getField('parent', true) : $this->getField('name', true),
                't'  => TYPE_NPC,
                'ti' => $this->getField('parentId') ?: $this->id,
             // 'bd' => (int)($this->curTpl['cuFlags'] & NPC_CU_INSTANCE_BOSS || ($this->curTpl['typeFlags'] & 0x4 && $this->curTpl['rank']))
             // 'z'   where am i spawned
             // 'dd'   DungeonDifficulty requires 'z'
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
        3 => array( 469, 1037, 1106,  529, 1012,   87,   21,  910,  609,  942,  909,  530,   69,  577,  930, 1068, 1104,  729,  369,   92,   54,  946,   67, 1052,  749,
                     47,  989, 1090, 1098,  978, 1011,   93, 1015, 1038,   76,  470,  349, 1031, 1077,  809,  911,  890,  970,  169,  730,   72,   70,  932, 1156,  933,
                    510, 1126, 1067, 1073,  509,  941, 1105,  990,  934,  935, 1094, 1119, 1124, 1064,  967, 1091,   59,  947,   81,  576,  922,   68, 1050, 1085,  889,
                    589,  270)
    );

    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
         5 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_REPAIRER          ], // canrepair
         6 => [FILTER_CR_ENUM,    's.areaId',         null                       ], // foundin
         9 => [FILTER_CR_BOOLEAN, 'lootId',                                      ], // lootable
        11 => [FILTER_CR_BOOLEAN, 'pickpocketLootId',                            ], // pickpocketable
        18 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_AUCTIONEER        ], // auctioneer
        19 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_BANKER            ], // banker
        20 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_BATTLEMASTER      ], // battlemaster
        21 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_FLIGHT_MASTER     ], // flightmaster
        22 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_GUILD_MASTER      ], // guildmaster
        23 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_INNKEEPER         ], // innkeeper
        24 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_CLASS_TRAINER     ], // talentunlearner
        25 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_GUILD_MASTER      ], // tabardvendor
        27 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_STABLE_MASTER     ], // stablemaster
        28 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_TRAINER           ], // trainer
        29 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_VENDOR            ], // vendor
        19 => [FILTER_CR_FLAG,    'npcflag',          NPC_FLAG_BANKER            ], // banker
        37 => [FILTER_CR_NUMERIC, 'id',               null,                  true], // id
        35 => [FILTER_CR_STRING,  'textureString'                                ], // useskin
        32 => [FILTER_CR_FLAG,    'cuFlags',          NPC_CU_INSTANCE_BOSS       ], // instanceboss
        33 => [FILTER_CR_FLAG,    'cuFlags',          CUSTOM_HAS_COMMENT         ], // hascomments
        31 => [FILTER_CR_FLAG,    'cuFlags',          CUSTOM_HAS_SCREENSHOT      ], // hasscreenshots
        40 => [FILTER_CR_FLAG,    'cuFlags',          CUSTOM_HAS_VIDEO           ], // hasvideos
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            case 1:                                         // health [num]
                if (!$this->isSaneNumeric($cr[2]) || !$this->int2Op($cr[1]))
                    break;

                // remap OP for this special case
                switch ($cr[1])
                {
                    case '=':                               // min > max is totally possible
                        $this->extraOpts['ct']['h'][] = 'healthMin = healthMax AND healthMin = '.$cr[2];
                        break;
                    case '>':
                        $this->extraOpts['ct']['h'][] = 'IF(healthMin > healthMax, healthMax, healthMin) > '.$cr[2];
                        break;
                    case '>=':
                        $this->extraOpts['ct']['h'][] = 'IF(healthMin > healthMax, healthMax, healthMin) >= '.$cr[2];
                        break;
                    case '<':
                        $this->extraOpts['ct']['h'][] = 'IF(healthMin > healthMax, healthMin, healthMax) < '.$cr[2];
                        break;
                    case '<=':
                        $this->extraOpts['ct']['h'][] = 'IF(healthMin > healthMax, healthMin, healthMax) <= '.$cr[2];
                        break;
                }
                return [1];                                 // always true, use post-filter
            case 2:                                         // mana [num]
                if (!$this->isSaneNumeric($cr[2]) || !$this->int2Op($cr[1]))
                    break;

                // remap OP for this special case
                switch ($cr[1])
                {
                    case '=':
                        $this->extraOpts['ct']['h'][] = 'manaMin = manaMax AND manaMin = '.$cr[2];
                        break;
                    case '>':
                        $this->extraOpts['ct']['h'][] = 'IF(manaMin > manaMax, manaMin, manaMax) > '.$cr[2];
                        break;
                    case '>=':
                        $this->extraOpts['ct']['h'][] = 'IF(manaMin > manaMax, manaMin, manaMax) >= '.$cr[2];
                        break;
                    case '<':
                        $this->extraOpts['ct']['h'][] = 'IF(manaMin > manaMax, manaMax, manaMin) < '.$cr[2];
                        break;
                    case '<=':
                        $this->extraOpts['ct']['h'][] = 'IF(manaMin > manaMax, manaMax, manaMin) <= '.$cr[2];
                        break;
                }
                return [1];                                 // always true, use post-filter
            case 7:                                         // startsquest [enum]
                switch ($cr[1])
                {
                    case 1:                                 // any
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!']];
                    case 2:                                 // alliance
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&']];
                    case 3:                                 // horde
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']];
                    case 4:                                 // both
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
                    case 5:                                 // none
                        $this->extraOpts['ct']['h'][] = 'startsQuests = 0';
                        return [1];
                }
                break;
            case 8:                                         // endsquest [enum]
                switch ($cr[1])
                {
                    case 1:                                 // any
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!']];
                    case 2:                                 // alliance
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&']];
                    case 3:                                 // horde
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']];
                    case 4:                                 // both
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
                    case 5:                                 // none
                        $this->extraOpts['ct']['h'][] = 'endsQuests = 0';
                        return [1];
                }
                break;
            case 3:                                         // faction [enum]
                if (in_array($cr[1], $this->enums[$cr[0]]))
                {
                    $facTpls = [];
                    $facs = new FactionList(array('OR', ['parentFactionId', $cr[1]], ['id', $cr[1]]));
                    foreach ($facs->iterate() as $__)
                        $facTpls = array_merge($facTpls, $facs->getField('templateIds'));

                    if (!$facTpls)
                        return [0];

                    return ['faction', $facTpls];
                }
                break;
            case 38;                                        // relatedevent
                if (!$this->isSaneNumeric($cr[1]))
                    break;

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
                else if ($cr[1])
                {
                    $eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId = ?d', $cr[1]);
                    $cGuids   = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_creature WHERE eventEntry IN (?a)', $eventIds);
                    return ['s.guid', $cGuids];
                }

                break;
            case 42:                                        // increasesrepwith [enum]
                if (in_array($cr[1], $this->enums[3]))      // reuse
                {
                    if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_factions WHERE id = ?d', $cr[1]))
                        $this->formData['reputationCols'][] = [$cr[1], Util::localizedString($_, 'name')];

                    if ($cIds = DB::World()->selectCol('SELECT creature_id FROM creature_onkill_reputation WHERE (RewOnKillRepFaction1 = ?d AND RewOnKillRepValue1 > 0) OR (RewOnKillRepFaction2 = ?d AND RewOnKillRepValue2 > 0)', $cr[1], $cr[1]))
                        return ['id', $cIds];
                    else
                        return [0];
                }

                break;
            case 43:                                        // decreasesrepwith [enum]
                if (in_array($cr[1], $this->enums[3]))      // reuse
                {
                    if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_factions WHERE id = ?d', $cr[1]))
                        $this->formData['reputationCols'][] = [$cr[1], Util::localizedString($_, 'name')];

                    if ($cIds = DB::World()->selectCol('SELECT creature_id FROM creature_onkill_reputation WHERE (RewOnKillRepFaction1 = ?d AND RewOnKillRepValue1 < 0) OR (RewOnKillRepFaction2 = ?d AND RewOnKillRepValue2 < 0)', $cr[1], $cr[1]))
                        return ['id', $cIds];
                    else
                        return [0];
                }

                break;
            case 12:                                        // averagemoneydropped [op] [int]
                if (!$this->isSaneNumeric($cr[2]) || !$this->int2Op($cr[1]))
                    break;

                return ['AND', ['((minGold + maxGold) / 2)', $cr[2], $cr[1]]];
            case 15:                                        // gatherable [yn]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['AND', ['skinLootId', 0, '>'], ['typeFlags', NPC_TYPEFLAG_HERBLOOT, '&']];
                    else
                        return ['OR', ['skinLootId', 0], [['typeFlags', NPC_TYPEFLAG_HERBLOOT, '&'], 0]];
                }
                break;
            case 44:                                        // salvageable [yn]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['AND', ['skinLootId', 0, '>'], ['typeFlags', NPC_TYPEFLAG_ENGINEERLOOT, '&']];
                    else
                        return ['OR', ['skinLootId', 0], [['typeFlags', NPC_TYPEFLAG_ENGINEERLOOT, '&'], 0]];
                }
                break;
            case 16:                                        // minable [yn]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['AND', ['skinLootId', 0, '>'], ['typeFlags', NPC_TYPEFLAG_MININGLOOT, '&']];
                    else
                        return ['OR', ['skinLootId', 0], [['typeFlags', NPC_TYPEFLAG_MININGLOOT, '&'], 0]];
                }
                break;
            case 10:                                        // skinnable [yn]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['AND', ['skinLootId', 0, '>'], [['typeFlags', NPC_TYPEFLAG_SPECIALLOOT, '&'], 0]];
                    else
                        return ['OR', ['skinLootId', 0], [['typeFlags', NPC_TYPEFLAG_SPECIALLOOT, '&'], 0, '!']];
                }
                break;
            case 34:                                        // usemodel [str]          // displayId -> id:creatureDisplayInfo.dbc/model -> id:cratureModelData.dbc/modelPath
            case 41:                                        // haslocation [yn] [staff]
/* todo */      return [1];
        }

        unset($cr);
        $this->error = true;
        return [1];
    }

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
        {
            $_ = (array)$_v['fa'];
            if (!array_diff($_, [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 20, 21, 24, 25, 26, 27, 30, 31, 32, 33, 34, 35, 37, 38, 39, 41, 42, 43, 44, 45, 46]))
                $parts[] = ['family', $_];
            else
                unset($_v['cl']);
        }

        // creatureLevel min [int]
        if (isset($_v['minle']))
        {
            if (is_int($_v['minle']) && $_v['minle'] > 0)
                $parts[] = ['minLevel', $_v['minle'], '>='];
            else
                unset($_v['minle']);
        }

        // creatureLevel max [int]
        if (isset($_v['maxle']))
        {
            if (is_int($_v['maxle']) && $_v['maxle'] > 0)
                $parts[] = ['maxLevel', $_v['maxle'], '<='];
            else
                unset($_v['maxle']);
        }

        // classification [list]
        if (isset($_v['cl']))
        {
            $_ = (array)$_v['cl'];
            if (!array_diff($_, [0, 1, 2, 3, 4]))
                $parts[] = ['rank', $_];
            else
                unset($_v['cl']);
        }

        // react Alliance [int]
        if (isset($_v['ra']))
        {
            $_ = (int)$_v['ra'];
            if (in_array($_, [-1, 0, 1]))
                $parts[] = ['ft.A', $_];
            else
                unset($_v['ra']);
        }

        // react Horde [int]
        if (isset($_v['rh']))
        {
            $_ = (int)$_v['rh'];
            if (in_array($_, [-1, 0, 1]))
                $parts[] = ['ft.H', $_];
            else
                unset($_v['rh']);
        }

        return $parts;
    }

}

?>
