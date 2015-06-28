<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestList extends BaseType
{
    public static   $type      = TYPE_QUEST;
    public static   $brickFile = 'quest';

    public          $requires  = [];
    public          $rewards   = [];
    public          $choices   = [];

    protected       $queryBase = 'SELECT q.*, q.id AS ARRAY_KEY FROM ?_quests q';
    protected       $queryOpts = array(
                        'q'   => [],
                        'rsc' => ['j' => '?_spell rsc ON q.rewardSpellCast = rsc.id'],      // limit rewardSpellCasts
                        'qse' => ['j' => '?_quests_startend qse ON q.id = qse.questId', 's' => ', qse.method'],    // groupConcat..?
                    );

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        // i don't like this very much
        $currencies = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, itemId FROM ?_currencies');

        // post processing
        foreach ($this->iterate() as $id => &$_curTpl)
        {
            $_curTpl['cat1'] = $_curTpl['questSortID'];      // should probably be in a method...
            $_curTpl['cat2'] = 0;

            foreach (Util::$questClasses as $k => $arr)
            {
                if (in_array($_curTpl['cat1'], $arr))
                {
                    $_curTpl['cat2'] = $k;
                    break;
                }
            }

            // store requirements
            $requires = [];
            for ($i = 1; $i < 7; $i++)
            {
                if ($_ = $_curTpl['reqItemId'.$i])
                    $requires[TYPE_ITEM][] = $_;

                if ($i > 4)
                    continue;

                if ($_curTpl['reqNpcOrGo'.$i] > 0)
                    $requires[TYPE_NPC][] = $_curTpl['reqNpcOrGo'.$i];
                else if ($_curTpl['reqNpcOrGo'.$i] < 0)
                    $requires[TYPE_OBJECT][] = -$_curTpl['reqNpcOrGo'.$i];

                if ($_ = $_curTpl['reqSourceItemId'.$i])
                    $requires[TYPE_ITEM][] = $_;
            }
            if ($requires)
                $this->requires[$id] = $requires;

            // store rewards
            $rewards = [];
            $choices = [];

            if ($_ = $_curTpl['rewardTitleId'])
                $rewards[TYPE_TITLE][] = $_;

            if ($_ = $_curTpl['rewardHonorPoints'])
                $rewards[TYPE_CURRENCY][104] = $_;

            if ($_ = $_curTpl['rewardArenaPoints'])
                $rewards[TYPE_CURRENCY][103] = $_;

            for ($i = 1; $i < 7; $i++)
            {
                if ($_ = $_curTpl['rewardChoiceItemId'.$i])
                    $choices[TYPE_ITEM][$_] = $_curTpl['rewardChoiceItemCount'.$i];

                if ($i > 5)
                    continue;

                if ($_ = $_curTpl['rewardFactionId'.$i])
                    $rewards[TYPE_FACTION][$_] = $_curTpl['rewardFactionValue'.$i];

                if ($i > 4)
                    continue;

                if ($_ = $_curTpl['rewardItemId'.$i])
                {
                    $qty = $_curTpl['rewardItemCount'.$i];
                    if (in_array($_, $currencies))
                        $rewards[TYPE_CURRENCY][array_search($_, $currencies)] = $qty;
                    else
                        $rewards[TYPE_ITEM][$_] = $qty;
                }
            }
            if ($rewards)
                $this->rewards[$id] = $rewards;

            if ($choices)
                $this->choices[$id] = $choices;
        }
    }

    // static use START
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_quests WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }
    // static use END

    public function isRepeatable()
    {
        return $this->curTpl['flags'] & QUEST_FLAG_REPEATABLE || $this->curTpl['specialFlags'] & QUEST_FLAG_SPECIAL_REPEATABLE;
    }

    public function isDaily($strict = false)
    {
        if ($strict)
            return $this->curTpl['flags'] & QUEST_FLAG_DAILY;
        else
            return $this->curTpl['flags'] & (QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY) || $this->curTpl['specialFlags'] & QUEST_FLAG_SPECIAL_MONTHLY;
    }

    // using reqPlayerKills and rewardHonor as a crutch .. has TC this even implemented..?
    public function isPvPEnabled()
    {
        return $this->curTpl['reqPlayerKills'] || $this->curTpl['rewardHonorPoints'] || $this->curTpl['rewardArenaPoints'];
    }

    // by TC definition
    public function isSeasonal()
    {
        return in_array($this->getField('zoneOrSortBak'), [-22, -284, -366, -369, -370, -376, -374]) && !$this->isRepeatable();
    }

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                "n"  => $this->getField('name', true),
                "t"  => TYPE_QUEST,
                "ti" => $this->id,
                "c"  => $this->curTpl['cat1'],
                "c2" => $this->curTpl['cat2']
            );
        }

        return $data;
    }

    public function getSOMData($side = SIDE_BOTH)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if (!(Util::sideByRaceMask($this->curTpl['reqRaceMask']) & $side))
                continue;

            list($series, $first) = DB::Aowow()->SelectRow(
                'SELECT IF(prev.id OR cur.nextQuestIdChain, 1, 0) AS "0", IF(prev.id IS NULL AND cur.nextQuestIdChain, 1, 0) AS "1" FROM ?_quests cur LEFT JOIN ?_quests prev ON prev.nextQuestIdChain = cur.id WHERE cur.id = ?d',
                $this->id
            );

            $data[$this->id] = array(
                'level'     => $this->curTpl['level'] < 0 ? MAX_LEVEL : $this->curTpl['level'],
                'name'      => $this->getField('name', true),
                'category'  => $this->curTpl['cat1'],
                'category2' => $this->curTpl['cat2'],
                'series'    => $series,
                'first'     => $first
            );

            if ($this->isDaily())
                $data[$this->id]['daily'] = 1;
        }

        return $data;
    }

    public function getListviewData($extraFactionId = 0)    // i should formulate a propper parameter..
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'category'  => $this->curTpl['cat1'],
                'category2' => $this->curTpl['cat2'],
                'id'        => $this->id,
                'level'     => $this->curTpl['level'],
                'reqlevel'  => $this->curTpl['minLevel'],
                'name'      => $this->getField('name', true),
                'side'      => Util::sideByRaceMask($this->curTpl['reqRaceMask']),
                'wflags'    => 0x0,
                'xp'        => $this->curTpl['rewardXP']
            );

            if (!empty($this->rewards[$this->id][TYPE_CURRENCY]))
                foreach ($this->rewards[$this->id][TYPE_CURRENCY] as $iId => $qty)
                    $data[$this->id]['currencyrewards'][] = [$iId, $qty];

            if (!empty($this->rewards[$this->id][TYPE_ITEM]))
                foreach ($this->rewards[$this->id][TYPE_ITEM] as $iId => $qty)
                    $data[$this->id]['itemrewards'][] = [$iId, $qty];

            if (!empty($this->choices[$this->id][TYPE_ITEM]))
                foreach ($this->choices[$this->id][TYPE_ITEM] as $iId => $qty)
                    $data[$this->id]['itemchoices'][] = [$iId, $qty];

            if ($_ = $this->curTpl['rewardTitleId'])
                $data[$this->id]['titlereward'] = $_;

            if ($_ = $this->curTpl['type'])
                $data[$this->id]['type'] = $_;

            if ($_ = $this->curTpl['reqClassMask'])
                $data[$this->id]['reqclass'] = $_;

            if ($_ = ($this->curTpl['reqRaceMask'] & RACE_MASK_ALL))
                if ((($_ & RACE_MASK_ALLIANCE) != RACE_MASK_ALLIANCE) && (($_ & RACE_MASK_HORDE) != RACE_MASK_HORDE))
                    $data[$this->id]['reqrace'] = $_;

            if ($_ = $this->curTpl['rewardOrReqMoney'])
                if ($_ > 0)
                    $data[$this->id]['money'] = $_;

            // todo (med): also get disables
            if ($this->curTpl['flags'] & QUEST_FLAG_UNAVAILABLE)
                $data[$this->id]['historical'] = true;

            // if ($this->isRepeatable())       // dafuque..? says repeatable and is used as 'disabled'..?
                // $data[$this->id]['wflags'] |= QUEST_CU_REPEATABLE;

            if ($this->curTpl['flags'] & QUEST_FLAG_DAILY)
            {
                $data[$this->id]['wflags'] |= QUEST_CU_DAILY;
                $data[$this->id]['daily'] = true;
            }

            if ($this->curTpl['flags'] & QUEST_FLAG_WEEKLY)
            {
                $data[$this->id]['wflags'] |= QUEST_CU_WEEKLY;
                $data[$this->id]['weekly'] = true;
            }

            if ($this->isSeasonal())
                $data[$this->id]['wflags'] |= QUEST_CU_SEASONAL;

            if ($this->curTpl['flags'] & QUEST_FLAG_AUTO_REWARDED)  // not shown in log
                $data[$this->id]['wflags'] |= QUEST_CU_SKIP_LOG;

            if ($this->curTpl['flags'] & QUEST_FLAG_AUTO_ACCEPT)    // self-explanatory
                $data[$this->id]['wflags'] |= QUEST_CU_AUTO_ACCEPT;

            if ($this->isPvPEnabled())                              // not sure why this flag also requires auto-accept to be set
                $data[$this->id]['wflags'] |= (QUEST_CU_AUTO_ACCEPT | QUEST_CU_PVP_ENABLED);

            $data[$this->id]['reprewards'] = [];
            for ($i = 1; $i < 6; $i++)
            {
                $foo = $this->curTpl['rewardFactionId'.$i];
                $bar = $this->curTpl['rewardFactionValue'.$i];
                if ($foo && $bar)
                {
                    $data[$this->id]['reprewards'][] = [$foo, $bar];

                    if ($extraFactionId == $foo)
                        $data[$this->id]['reputation'] = $bar;
                }
            }
        }

        return $data;
    }

    public function parseText($type = 'objectives', $jsEscaped = true)
    {
        $text = $this->getField($type, true);
        if (!$text)
            return '';

        $text = Util::parseHtmlText($text);

        if ($jsEscaped)
            $text = Util::jsEscape($text);

        return $text;
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return null;

        $title = Util::jsEscape($this->getField('name', true));
        $level = $this->curTpl['level'];
        if ($level < 0)
            $level = 0;

        $x = '';
        if ($level)
        {
            $level = sprintf(Lang::quest('questLevel'), $level);

            if ($this->curTpl['flags'] & QUEST_FLAG_DAILY)  // daily
                $level .= ' '.Lang::quest('daily');

            $x .= '<table><tr><td><table width="100%"><tr><td><b class="q">'.$title.'</b></td><th><b class="q0">'.$level.'</b></th></tr></table></td></tr></table>';
        }
        else
            $x .= '<table><tr><td><b class="q">'.$title.'</b></td></tr></table>';


        $x .= '<table><tr><td><br />'.$this->parseText('objectives');


        $xReq = '';
        for ($i = 1; $i < 5; $i++)
        {
            $ot     = $this->getField('objectiveText'.$i, true);
            $rng    = $this->curTpl['reqNpcOrGo'.$i];
            $rngQty = $this->curTpl['reqNpcOrGoCount'.$i];

            if ($rngQty < 1 && (!$rng || $ot))
                continue;

            if ($ot)
                $name = $ot;
            else
                $name = $rng > 0 ? CreatureList::getName($rng) : GameObjectList::getName(-$rng);

            $xReq .= '<br /> - '.Util::jsEscape($name).($rngQty > 1 ? ' x '.$rngQty : null);
        }

        for ($i = 1; $i < 7; $i++)
        {
            $ri    = $this->curTpl['reqItemId'.$i];
            $riQty = $this->curTpl['reqItemCount'.$i];

            if (!$ri || $riQty < 1)
                continue;

            $xReq .= '<br /> - '.Util::jsEscape(ItemList::getName($ri)).($riQty > 1 ? ' x '.$riQty : null);
        }

        if ($et = $this->getField('end', true))
            $xReq .= '<br /> - '.Util::jsEscape($et);

        if ($_ = $this->getField('rewardOrReqMoney'))
            if ($_ < 0)
                $xReq .= '<br /> - '.Lang::quest('money').Lang::main('colon').Util::formatMoney(abs($_));

        if ($xReq)
            $x .= '<br /><br /><span class="q">'.Lang::quest('requirements').Lang::main('colon').'</span>'.$xReq;

        $x .= '</td></tr></table>';

        return $x;
    }

    public function getJSGlobals($addMask = GLOBALINFO_ANY)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($addMask & GLOBALINFO_REWARDS)
            {
                // items
                for ($i = 1; $i < 5; $i++)
                    if ($this->curTpl['rewardItemId'.$i] > 0)
                        $data[TYPE_ITEM][$this->curTpl['rewardItemId'.$i]] = $this->curTpl['rewardItemId'.$i];

                for ($i = 1; $i < 7; $i++)
                    if ($this->curTpl['rewardChoiceItemId'.$i] > 0)
                        $data[TYPE_ITEM][$this->curTpl['rewardChoiceItemId'.$i]] = $this->curTpl['rewardChoiceItemId'.$i];

                // spells
                if ($this->curTpl['rewardSpell'] > 0)
                    $data[TYPE_SPELL][$this->curTpl['rewardSpell']] = $this->curTpl['rewardSpell'];

                if ($this->curTpl['rewardSpellCast'] > 0)
                    $data[TYPE_SPELL][$this->curTpl['rewardSpellCast']] = $this->curTpl['rewardSpellCast'];

                // titles
                if ($this->curTpl['rewardTitleId'] > 0)
                    $data[TYPE_TITLE][$this->curTpl['rewardTitleId']] = $this->curTpl['rewardTitleId'];

                // currencies
                if (!empty($this->rewards[$this->id][TYPE_CURRENCY]))
                {
                    $_ = $this->rewards[$this->id][TYPE_CURRENCY];
                    $data[TYPE_CURRENCY] = array_combine(array_keys($_), array_keys($_));
                }
            }

            if ($addMask & GLOBALINFO_SELF)
                $data[TYPE_QUEST][$this->id] = ['name' => $this->getField('name', true)];
        }

        return $data;
    }
}


class QuestListFilter extends Filter
{
    public    $extraOpts     = [];
    protected $enums         = array(                       // massive enums could be put here, if you want to restrict inputs further to be valid IDs instead of just integers
        37 => [null, 1, 2, 3, 4, 5, 6, 7, 8,    9, null, 11, true, false],
        38 => [null, 1, 2, 3, 4, 5, 6, 7, 8, null,   10, 11, true, false],
    );
    protected $genericFilter = array(
        27 => [FILTER_CR_FLAG,      'flags',            QUEST_FLAG_DAILY          ], // daily
        28 => [FILTER_CR_FLAG,      'flags',            QUEST_FLAG_WEEKLY         ], // weekly
        29 => [FILTER_CR_FLAG,      'flags',            QUEST_FLAG_REPEATABLE     ], // repeatable
        30 => [FILTER_CR_NUMERIC,   'id',               null,                 true], // id
         5 => [FILTER_CR_FLAG,      'flags',            QUEST_FLAG_SHARABLE       ], // sharable
        11 => [FILTER_CR_NUMERIC,   'suggestedPlayers',                           ], // suggestedplayers
         6 => [FILTER_CR_NUMERIC,   'timeLimit',                                  ], // timer
        42 => [FILTER_CR_STAFFFLAG, 'flags',                                      ], // flags
        45 => [FILTER_CR_BOOLEAN,   'rewardTitleId',                              ], // titlerewarded
         2 => [FILTER_CR_NUMERIC,   'rewardXP',                                   ], // experiencegained
         3 => [FILTER_CR_NUMERIC,   'rewardOrReqMoney',                           ], // moneyrewarded
        33 => [FILTER_CR_ENUM,      'holidayId',                                  ], // relatedevent
        25 => [FILTER_CR_FLAG,      'cuFlags',          CUSTOM_HAS_COMMENT        ], // hascomments
        18 => [FILTER_CR_FLAG,      'cuFlags',          CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        36 => [FILTER_CR_FLAG,      'cuFlags',          CUSTOM_HAS_VIDEO          ], // hasvideos
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
            case  1:                                        // increasesrepwith
                if ($this->isSaneNumeric($cr[1]) && $cr[1] > 0)
                {
                    return [
                        'OR',
                        ['AND', ['rewardFactionId1', $cr[1]], ['rewardFactionValue1', 0, '>']],
                        ['AND', ['rewardFactionId2', $cr[1]], ['rewardFactionValue2', 0, '>']],
                        ['AND', ['rewardFactionId3', $cr[1]], ['rewardFactionValue3', 0, '>']],
                        ['AND', ['rewardFactionId4', $cr[1]], ['rewardFactionValue4', 0, '>']],
                        ['AND', ['rewardFactionId5', $cr[1]], ['rewardFactionValue5', 0, '>']]
                    ];
                }
                break;
            case 10:                                        // decreasesrepwith
                if ($this->isSaneNumeric($cr[1]) && $cr[1] > 0)
                {
                    return [
                        'OR',
                        ['AND', ['rewardFactionId1', $cr[1]], ['rewardFactionValue1', 0, '<']],
                        ['AND', ['rewardFactionId2', $cr[1]], ['rewardFactionValue2', 0, '<']],
                        ['AND', ['rewardFactionId3', $cr[1]], ['rewardFactionValue3', 0, '<']],
                        ['AND', ['rewardFactionId4', $cr[1]], ['rewardFactionValue4', 0, '<']],
                        ['AND', ['rewardFactionId5', $cr[1]], ['rewardFactionValue5', 0, '<']]
                    ];
                }
                break;
            case 43:                                        // currencyrewarded
                if ($this->isSaneNumeric($cr[1]) && $cr[1] > 0)
                {
                    return [
                        'OR',
                        ['rewardItemId1', $cr[1]], ['rewardItemId2', $cr[1]], ['rewardItemId3', $cr[1]], ['rewardItemId4', $cr[1]],
                        ['rewardChoiceItemId1', $cr[1]], ['rewardChoiceItemId2', $cr[1]], ['rewardChoiceItemId3', $cr[1]], ['rewardChoiceItemId4', $cr[1]], ['rewardChoiceItemId5', $cr[1]], ['rewardChoiceItemId6', $cr[1]]
                    ];
                }
                break;
            case 34:                                        // availabletoplayers
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['AND', [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0], [['flags', QUEST_FLAG_UNAVAILABLE, '&'], 0]];
                    else
                        return ['OR', ['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], ['flags', QUEST_FLAG_UNAVAILABLE, '&']];
                }
                break;
            case 23:                                        // itemchoices [op] [int]
                if (!$this->isSaneNumeric($cr[2], false) || !$this->int2Op($cr[1]))
                    break;

                $this->extraOpts['q']['s'][] = ', (IF(rewardChoiceItemId1, 1, 0) + IF(rewardChoiceItemId2, 1, 0) + IF(rewardChoiceItemId3, 1, 0) + IF(rewardChoiceItemId4, 1, 0) + IF(rewardChoiceItemId5, 1, 0) + IF(rewardChoiceItemId6, 1, 0)) as numChoices';
                $this->extraOpts['q']['h'][] = 'numChoices '.$cr[1].' '.$cr[2];
                return [1];
            case 22:                                        // itemrewards [op] [int]
                if (!$this->isSaneNumeric($cr[2], false) || !$this->int2Op($cr[1]))
                    break;

                $this->extraOpts['q']['s'][] = ', (IF(rewardItemId1, 1, 0) + IF(rewardItemId2, 1, 0) + IF(rewardItemId3, 1, 0) + IF(rewardItemId4, 1, 0)) as numRewards';
                $this->extraOpts['q']['h'][] = 'numRewards '.$cr[1].' '.$cr[2];
                return [1];
            case 44:                                        // countsforloremaster_stc [bool]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['AND', ['questSortID', 0, '>'], [['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY | QUEST_FLAG_REPEATABLE , '&'], 0], [['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_MONTHLY , '&'], 0]];
                    else
                        return ['OR', ['questSortID', 0, '<'], ['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY | QUEST_FLAG_REPEATABLE , '&'], ['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_MONTHLY , '&']];;
                }

                break;
            case  4:                                        // spellrewarded [bool]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['OR', ['sourceSpellId', 0, '>'], ['rewardSpell', 0, '>'], ['rsc.effect1Id', SpellList::$effects['teach']], ['rsc.effect2Id', SpellList::$effects['teach']], ['rsc.effect3Id', SpellList::$effects['teach']]];
                    else
                        return ['AND', ['sourceSpellId', 0], ['rewardSpell', 0], ['rewardSpellCast', 0]];
                }
                break;
            case  9:                                        // objectiveearnrepwith [enum]
                $_ = intVal($cr[1]);
                if ($_ > 0)
                    return ['OR', ['reqFactionId1', $_], ['reqFactionId2', $_]];
                else if ($cr[1] == FILTER_ENUM_ANY)         // any
                    return ['OR', ['reqFactionId1', 0, '>'], ['reqFactionId2', 0, '>']];
                else if ($cr[1] == FILTER_ENUM_NONE)        // none
                    return ['AND', ['reqFactionId1', 0], ['reqFactionId2', 0]];

                break;
            case 37:                                        // classspecific [enum]
                $_ = isset($this->enums[$cr[0]][$cr[1]]) ? $this->enums[$cr[0]][$cr[1]] : null;
                if ($_ !== null)
                {
                    if ($_ === true)
                        return ['AND', ['reqClassMask', 0, '!'], [['reqClassMask', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!']];
                    else if ($_ === false)
                        return ['OR', ['reqClassMask', 0], [['reqClassMask', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL]];
                    else if (is_int($_))
                        return ['AND', ['reqClassMask', (1 << ($_ - 1)), '&'], [['reqClassMask', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!']];
                }
                break;
            case 38:                                        // racespecific [enum]
                $_ = isset($this->enums[$cr[0]][$cr[1]]) ? $this->enums[$cr[0]][$cr[1]] : null;
                if ($_ !== null)
                {
                    if ($_ === true)
                        return ['AND', ['reqRaceMask', 0, '!'], [['reqRaceMask', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'], [['reqRaceMask', RACE_MASK_ALLIANCE, '&'], RACE_MASK_ALLIANCE, '!'], [['reqRaceMask', RACE_MASK_HORDE, '&'], RACE_MASK_HORDE, '!']];
                    else if ($_ === false)
                        return ['OR', ['reqRaceMask', 0], ['reqRaceMask', RACE_MASK_ALL], ['reqRaceMask', RACE_MASK_ALLIANCE], ['reqRaceMask', RACE_MASK_HORDE]];
                    else if (is_int($_))
                        return ['AND', ['reqRaceMask', (1 << ($_ - 1)), '&'], [['reqRaceMask', RACE_MASK_ALLIANCE, '&'], RACE_MASK_ALLIANCE, '!'], [['reqRaceMask', RACE_MASK_HORDE, '&'], RACE_MASK_HORDE, '!']];
                }
                break;
            case 19:                                        // startsfrom [enum]
                switch ($cr[1])
                {
                    case 1:                                 // npc
                        return ['AND', ['qse.type', TYPE_NPC], ['qse.method', 0x1, '&']];
                    case 2:                                 // object
                        return ['AND', ['qse.type', TYPE_OBJECT], ['qse.method', 0x1, '&']];
                    case 3:                                 // item
                        return ['AND', ['qse.type', TYPE_ITEM], ['qse.method', 0x1, '&']];
                }
                break;
            case 21:                                        // endsat [enum]
                switch ($cr[1])
                {
                    case 1:                                 // npc
                        return ['AND', ['qse.type', TYPE_NPC], ['qse.method', 0x2, '&']];
                    case 2:                                 // object
                        return ['AND', ['qse.type', TYPE_OBJECT], ['qse.method', 0x2, '&']];
                }
                break;
            case 24:                                        // lacksstartend [bool]
                $missing = DB::Aowow()->selectCol('SELECT questId, max(method) a, min(method) b FROM ?_quests_startend GROUP BY questId HAVING (a | b) <> 3');
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['id', $missing];
                    else
                        return ['id', $missing, '!'];
                }
                break;
            case  7:                                        // firstquestseries
            case 15:                                        // lastquestseries
            case 16:                                        // partseries
/* todo */      return [1];                                 // self-joining eats substential amounts of time: should restructure that and also incorporate reqQ and openQ cases from infobox
            default:
                break;
        }

        unset($cr);
        $this->error = 1;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // name
        if (isset($_v['na']))
        {
            $_ = [];
            if (isset($_v['ex']) && $_v['ex'] == 'on')
                $_ = $this->modularizeString(['name_loc'.User::$localeId, 'objectives_loc'.User::$localeId, 'details_loc'.User::$localeId]);
            else
                $_ = $this->modularizeString(['name_loc'.User::$localeId]);

            if ($_)
                $parts[] = $_;
        }

        // level min
        if (isset($_v['minle']))
        {
            if (is_int($_v['minle']) && $_v['minle'] > 0)
                $parts[] = ['level', $_v['minle'], '>='];   // not considering quests that are always at player level (-1)
            else
                unset($_v['minle']);
        }

        // level max
        if (isset($_v['maxle']))
        {
            if (is_int($_v['maxle']) && $_v['maxle'] > 0)
                $parts[] = ['level', $_v['maxle'], '<='];
            else
                unset($_v['maxle']);
        }

        // reqLevel min
        if (isset($_v['minrl']))
        {
            if (is_int($_v['minrl']) && $_v['minrl'] > 0)
                $parts[] = ['minLevel', $_v['minrl'], '>='];// ignoring maxLevel
            else
                unset($_v['minrl']);
        }

        // reqLevel max
        if (isset($_v['maxrl']))
        {
            if (is_int($_v['maxrl']) && $_v['maxrl'] > 0)
                $parts[] = ['minLevel', $_v['maxrl'], '<='];// ignoring maxLevel
            else
                unset($_v['maxrl']);
        }

        // side
        if (isset($_v['si']))
        {
            $ex    = [['reqRaceMask', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'];
            $notEx = ['OR', ['reqRaceMask', 0], [['reqRaceMask', RACE_MASK_ALL, '&'], RACE_MASK_ALL]];

            switch ($_v['si'])
            {
                case  3:
                    $parts[] = $notEx;
                    break;
                case  2:
                    $parts[] = ['OR', $notEx, ['reqRaceMask', RACE_MASK_HORDE, '&']];
                    break;
                case -2:
                    $parts[] = ['AND', $ex,   ['reqRaceMask', RACE_MASK_HORDE, '&']];
                    break;
                case  1:
                    $parts[] = ['OR', $notEx, ['reqRaceMask', RACE_MASK_ALLIANCE, '&']];
                    break;
                case -1:
                    $parts[] = ['AND', $ex,   ['reqRaceMask', RACE_MASK_ALLIANCE, '&']];
                    break;
                default:
                    unset($_v['si']);
            }
        }

        // type [list]
        if (isset($_v['ty']))
        {
            $_ = (array)$_v['ty'];
            if (!array_diff($_, [0, 1, 21, 41, 62, 81, 82, 83, 84, 85, 88, 89]))
                $parts[] = ['type', $_];
            else
                unset($_v['ty']);
        }

        return $parts;
    }
}


?>
