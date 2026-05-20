<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Quest extends DBType
{
    public readonly  int       $cuFlags;
    public readonly  LocString $name;
    public readonly  LocString $objectives;
    public readonly  LocString $details;
    public readonly  LocString $end;
    public readonly  LocString $offerReward;
    public readonly  LocString $requestItems;
    public readonly  LocString $completed;
    /** @var LocString[] $objectiveText - length: 4 custom objective texts */
    public readonly  array     $objectiveText;
    public readonly  int       $category1;
    public readonly  int       $category2;
    public readonly  int       $questType;
    public readonly  int       $level;
    public readonly  int       $minLevel;
    public readonly  int       $maxLevel;
    public readonly  int       $questSortId;
    public readonly  int       $questSortIdBak;
    public readonly  int       $questInfoId;
    public readonly  int       $suggestedPlayers;
    public readonly  int       $timeLimit;
    public readonly  int       $eventId;
    public readonly  int       $prevQuestId;
    public readonly  int       $nextQuestId;
    public readonly  int       $breadcrumbForQuestId;
    public readonly  int       $exclusiveGroup;
    public readonly  int       $nextQuestIdChain;
    public readonly  int       $flags;
    public readonly  int       $specialFlags;
    // granted on accept
    public readonly  int       $sourceItemId;
    public readonly  int       $sourceItemCount;
    public readonly  int       $sourceSpellId;
    // accept requirements
    public readonly  int       $reqClassMask;
    public readonly  int       $reqRaceMask;
    public readonly  int       $reqSkillId;
    public readonly  int       $reqSkillPoints;
    public readonly  int       $reqMinRepFaction;
    public readonly  int       $reqMaxRepFaction;
    public readonly  int       $reqMinRepValue;
    public readonly  int       $reqMaxRepValue;
    // rewards
    public readonly  int       $rewardXP;
    public readonly  int       $rewardOrReqMoney;
    public readonly  int       $rewardMoneyMaxLevel;
    public readonly  int       $rewardSpell;
    public readonly  int       $rewardSpellCast;
    public readonly  int       $rewardHonorPoints;
    public readonly  int       $rewardMailTemplateId;
    public readonly  int       $rewardMailDelay;
    public readonly  int       $rewardTitleId;
    public readonly  int       $rewardTalents;
    public readonly  int       $rewardArenaPoints;
    /** @var int[] $rewardItemId - length: 4 */
    public readonly  array     $rewardItemId;
    /** @var int[] $rewardItemCount - length: 4 */
    public readonly  array     $rewardItemCount;
    /** @var int[] $rewardChoiceItemId - length: 6 */
    public readonly  array     $rewardChoiceItemId;
    /** @var int[] $rewardChoiceItemCount - length: 6 */
    public readonly  array     $rewardChoiceItemCount;
    /** @var int[] $rewardFactionId - length: 5 */
    public readonly  array     $rewardFactionId;
    /** @var int[] $rewardFactionValue - length: 5 */
    public readonly  array     $rewardFactionValue;
    // completion requirements
    public readonly  int       $reqPlayerKills;
    /** @var int[] $reqNpcOrGo - length: 4; >0: required CreatureId; <0: required ObjectId */
    public readonly  array     $reqNpcOrGo;
    /** @var int[] $reqNpcOrGoCount - length: 4 */
    public readonly  array     $reqNpcOrGoCount;
    /** @var int[] $reqSourceItemId - length: 4 item drops available when quest is active*/
    public readonly  array     $reqSourceItemId;
    /** @var int[] $reqSourceItemCount - length: 4 */
    public readonly  array     $reqSourceItemCount;
    /** @var int[] $reqItemId - length: 6 required items */
    public readonly  array     $reqItemId;
    /** @var int[] $reqItemCount - length: 6 */
    public readonly  array     $reqItemCount;
    /** @var int[] $reqFactionId - length: 2 */
    public readonly  array     $reqFactionId;
    /** @var int[] $reqFactionValue - length: 2 */
    public readonly  array     $reqFactionValue;
    // ::quest_startend
    public readonly ?int       $method;
    // ::events
    public readonly ?int       $holidayId;

    public static int    $dbType    = Type::QUEST;
    public static string $brickFile = 'quest';
    public static string $dataTable = '::quests';

    private  array $rewardItems       = [];
    private  array $rewardCurrencies  = [];
    private ?array $currencyItemPairs = null;
    private  int   $repColFactionId;

    public const /* string */ QUERY_BASE = 'SELECT q.*, q.`id` AS ARRAY_KEY FROM ::quests q';
    public const /* array  */ QUERY_OPTS = array(
        'q'   => [],
        'nml' => ['j' => '::quests_search nml ON nml.`id` = q.`id` AND nml.`locale` = DB_LOC_I'],
        'rsc' => ['j' => '::spell rsc ON q.`rewardSpellCast` = rsc.`id`'], // limit rewardSpellCasts
        'qse' => ['j' => '::quests_startend qse ON q.`id` = qse.`questId`', 's' => ', qse.`method`'], // groupConcat..?
        'e'   => ['j' => ['::events e ON e.`id` = q.`eventId`', true], 's' => ', e.`holidayId`']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name          = new LocString($initData, 'name',         pruneFromSrc: true);
        $this->objectives    = new LocString($initData, 'objectives',   pruneFromSrc: true);
        $this->details       = new LocString($initData, 'details',      pruneFromSrc: true);
        $this->end           = new LocString($initData, 'end',          pruneFromSrc: true);
        $this->offerReward   = new LocString($initData, 'offerReward',  pruneFromSrc: true);
        $this->requestItems  = new LocString($initData, 'requestItems', pruneFromSrc: true);
        $this->completed     = new LocString($initData, 'completed',    pruneFromSrc: true);
        $this->objectiveText = array(
            new LocString($initData, 'objectiveText1', pruneFromSrc: true),
            new LocString($initData, 'objectiveText2', pruneFromSrc: true),
            new LocString($initData, 'objectiveText3', pruneFromSrc: true),
            new LocString($initData, 'objectiveText4', pruneFromSrc: true)
        );

        // category
        $cat2 = 0;
        foreach (Game::QUEST_CLASSES as $c2 => $c1)
        {
            if (!in_array($initData['questSortId'], $c1))
                continue;

            $cat2 = $c2;
            break;
        }

        $this->category1 = $initData['questSortId'];
        $this->category2 = $cat2;

        // requirements
        $this->reqItemId          = [$initData['reqItemId1'],          $initData['reqItemId2'],          $initData['reqItemId3'],          $initData['reqItemId4'],         $initData['reqItemId5'],         $initData['reqItemId6']];
        $this->reqItemCount       = [$initData['reqItemCount1'],       $initData['reqItemCount2'],       $initData['reqItemCount3'],       $initData['reqItemCount4'],      $initData['reqItemCount5'],      $initData['reqItemCount6']];
        $this->reqNpcOrGo         = [$initData['reqNpcOrGo1'],         $initData['reqNpcOrGo2'],         $initData['reqNpcOrGo3'],         $initData['reqNpcOrGo4']];
        $this->reqNpcOrGoCount    = [$initData['reqNpcOrGoCount1'],    $initData['reqNpcOrGoCount2'],    $initData['reqNpcOrGoCount3'],    $initData['reqNpcOrGoCount4']];
        $this->reqSourceItemId    = [$initData['reqSourceItemId1'],    $initData['reqSourceItemId2'],    $initData['reqSourceItemId3'],    $initData['reqSourceItemId4']];
        $this->reqSourceItemCount = [$initData['reqSourceItemCount1'], $initData['reqSourceItemCount2'], $initData['reqSourceItemCount3'], $initData['reqSourceItemCount4']];
        $this->reqFactionId       = [$initData['reqFactionId1'],       $initData['reqFactionId2']];
        $this->reqFactionValue    = [$initData['reqFactionValue1'],    $initData['reqFactionValue2']];

        // rewards
        $this->rewardItemId          = [$initData['rewardItemId1'],          $initData['rewardItemId2'],          $initData['rewardItemId3'],          $initData['rewardItemId4']];
        $this->rewardItemCount       = [$initData['rewardItemCount1'],       $initData['rewardItemCount2'],       $initData['rewardItemCount3'],       $initData['rewardItemCount4']];
        $this->rewardChoiceItemId    = [$initData['rewardChoiceItemId1'],    $initData['rewardChoiceItemId2'],    $initData['rewardChoiceItemId3'],    $initData['rewardChoiceItemId4'],    $initData['rewardChoiceItemId5'],    $initData['rewardChoiceItemId6']];
        $this->rewardChoiceItemCount = [$initData['rewardChoiceItemCount1'], $initData['rewardChoiceItemCount2'], $initData['rewardChoiceItemCount3'], $initData['rewardChoiceItemCount4'], $initData['rewardChoiceItemCount5'], $initData['rewardChoiceItemCount6']];
        $this->rewardFactionId       = [$initData['rewardFactionId1'],       $initData['rewardFactionId2'],       $initData['rewardFactionId3'],       $initData['rewardFactionId4'],       $initData['rewardFactionId5']];
        $this->rewardFactionValue    = [$initData['rewardFactionValue1'],    $initData['rewardFactionValue2'],    $initData['rewardFactionValue3'],    $initData['rewardFactionValue4'],    $initData['rewardFactionValue5']];

        // optionals
        $this->method    = $initData['method']    ?? null;
        $this->holidayId = $initData['holidayId'] ?? null;

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                case 'method':
                case 'holidayId':
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }

        // sort rewards into item + currency
        $currencyItemPairs ??= self::fetchCurrencyItemPairs();

        if ($_ = $initData['rewardTitleId'])
            $rewards[Type::TITLE][] = $_;

        if ($_ = $initData['rewardHonorPoints'])
            $this->rewardCurrencies[CURRENCY_HONOR_POINTS] = $_;
        if ($_ = $initData['rewardArenaPoints'])
            $this->rewardCurrencies[CURRENCY_ARENA_POINTS] = $_;

        foreach (array_filter($this->rewardItemId) as $i => $rewItem)
        {
            if (!($qty = $this->rewardItemCount[$i]))
                continue;

            if (($k = array_search($rewItem, $currencyItemPairs)) !== false)
                $this->rewardCurrencies[$currencyItemPairs[$k]] = ($this->rewardCurrencies[$currencyItemPairs[$k]] ?? 0) + $qty;
            else
                $this->rewardItems[$rewItem] = ($this->rewardItems[$rewItem] ?? 0) + $qty;
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0, int $reputationCol = 0) : array
    {
        $data = [];

        $data = array(
            'category'  => $this->category1,
            'category2' => $this->category2,
            'id'        => $this->id,
            'level'     => $this->level,
            'reqlevel'  => $this->minLevel,
            'name'      => UIText::unescapeUISequences($this->name, Lang::FMT_RAW),
            'side'      => ChrRace::sideFromMask($this->reqRaceMask),
            'wflags'    => 0x0,
            'xp'        => $this->rewardXP
        );

        foreach ($this->getRewardCurrencies() as $iId => $qty)
            $data['currencyrewards'][] = [$iId, $qty];

        foreach ($this->getRewardItems() as $iId => $qty)
            $data['itemrewards'][] = [$iId, $qty];

        foreach ($this->getRewardChoiceItems() as $iId => $qty)
            $data['itemchoices'][] = [$iId, $qty];

        if ($_ = $this->rewardTitleId)
            $data['titlereward'] = $_;

        if ($_ = $this->questInfoId)
            $data['type'] = $_;

        if ($_ = $this->reqClassMask)
            $data['reqclass'] = $_;

        if ($_ = ($this->reqRaceMask & ChrRace::MASK_ALL))
            if ((($_ & ChrRace::MASK_ALLIANCE) != ChrRace::MASK_ALLIANCE) && (($_ & ChrRace::MASK_HORDE) != ChrRace::MASK_HORDE))
                $data['reqrace'] = $_;

        if (($_ = $this->rewardOrReqMoney) > 0)
            $data['money'] = $_;

        // todo (med): also get disables
        if ($this->flags & QUEST_FLAG_UNAVAILABLE)
            $data['historical'] = true;

        // if ($this->isRepeatable())                       // dafuque..? says repeatable and is used as 'disabled'..?
            // $data['wflags'] |= QUEST_CU_REPEATABLE;
        if ($this->cuFlags & (CUSTOM_UNAVAILABLE | CUSTOM_DISABLED))
            $data['wflags'] |= QUEST_CU_REPEATABLE;

        if ($this->flags & QUEST_FLAG_DAILY)
        {
            $data['wflags'] |= QUEST_CU_DAILY;
            $data['daily'] = true;
        }

        if ($this->flags & QUEST_FLAG_WEEKLY)
        {
            $data['wflags'] |= QUEST_CU_WEEKLY;
            $data['weekly'] = true;
        }

        if ($this->isSeasonal())
            $data['wflags'] |= QUEST_CU_SEASONAL;

        if ($this->flags & QUEST_FLAG_TRACKING)             // not shown in log
            $data['wflags'] |= QUEST_CU_SKIP_LOG;

        if ($this->isAutoAccept())
            $data['wflags'] |= QUEST_CU_AUTO_ACCEPT;

        if ($this->flags & QUEST_FLAG_FLAGS_PVP)            // this flag is only displayed if auto-accept is also set. not sure why.
            $data['wflags'] |= QUEST_CU_PVP_ENABLED;

        $data['reprewards'] = [];
        foreach (array_filter($this->rewardFactionId) as $i => $factionId)
        {
            if ($amt = $this->rewardFactionValue[$i])
                $data['reprewards'][] = [$factionId, $amt];

            if ($reputationCol == $factionId)
                $data['reputation'] = $amt;
        }

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        if ($addMask & GLOBALINFO_REWARDS)
        {
            // items
            foreach ($this->getRewardItems() as $iId => $_)
                $data[Type::ITEM][$iId] = $iId;

            foreach ($this->getRewardChoiceItems() as $iId => $_)
                $data[Type::ITEM][$iId] = $iId;

            // spells
            if ($this->rewardSpell > 0)
                $data[Type::SPELL][$this->rewardSpell] = $this->rewardSpell;

            if ($this->rewardSpellCast > 0)
                $data[Type::SPELL][$this->rewardSpellCast] = $this->rewardSpellCast;

            // titles
            if ($this->rewardTitleId > 0)
                $data[Type::TITLE][$this->rewardTitleId] = $this->rewardTitleId;

            // currencies
            foreach ($this->getRewardCurrencies() as $cId => $_)
                $data[Type::CURRENCY][$cId] = $cId;
        }

        if ($addMask & GLOBALINFO_SELF)
        {
            $data[Type::QUEST][$this->id] = ['name' => $this->name];

            if ($this->flags & QUEST_FLAG_DAILY)
                $data[Type::QUEST][$this->id]['daily'] = true;

            if ($this->flags & QUEST_FLAG_WEEKLY)
                $data[Type::QUEST][$this->id]['weekly'] = true;
        }

        return $data;
    }

    public function getSourceData() : array
    {
        return array(
            "n"  => $this->name,
            "t"  => Type::QUEST,
            "ti" => $this->id,
            "c"  => $this->category1,
            "c2" => $this->category2
        );
    }

    public function renderTooltip() : ?string
    {
        $title = UIText::unescapeUISequences(Util::htmlEscape($this->name), Lang::FMT_HTML);

        $x = '';
        if ($level = min(0, $this->level))
        {
            $level = Lang::quest('questLevel', [$level]);

            if ($this->flags & QUEST_FLAG_DAILY)
                $level .= ' '.Lang::quest('daily');

            $x .= '<table><tr><td><table width="100%"><tr><td><b class="q">'.$title.'</b></td><th><b class="q0">'.$level.'</b></th></tr></table></td></tr></table>';
        }
        else
            $x .= '<table><tr><td><b class="q">'.$title.'</b></td></tr></table>';


        $x .= '<table><tr><td><br />'.$this->renderText('objectives', false);


        $xReq = '';
        for ($i = 0; $i < 4; $i++)
        {
            $ot  = $this->objectiveText[$i];
            $rng = $this->reqNpcOrGo[$i];
            $qty = $this->reqNpcOrGoCount[$i];

            if ($ot->isEmpty() && ($qty < 1 || !$rng))
                continue;

            $name = '';
            if (!$ot->isEmpty())
                $name = $ot;
            else if ($rng > 0)
                $name = Creature::getName($rng);
            else if ($rng < 0)
                $name = UIText::unescapeUISequences(Gameobject::getName(-$rng), Lang::FMT_HTML);

            $xReq .= '<br /> - '.($name ?: Util::ucFirst(Lang::game($rng > 0 ? 'npc' : 'object')).' #'.abs($rng)).($qty > 1 ? ' x '.$qty : '');
        }

        foreach ($this->reqItemId as $i => $ri)
        {
            if (($riQty = $this->reqItemCount[$i]) < 1)
                continue;

            $name = UIText::unescapeUISequences(ItemList::getName($ri), Lang::FMT_HTML) ?: Util::ucFirst(Lang::game('item')).' #'.$ri;

            $xReq .= '<br /> - '.$name.($riQty > 1 ? ' x '.$riQty : '');
        }

        if (!$this->end->isEmpty())
            $xReq .= '<br /> - '.$this->end;

        if (($_ = $this->rewardOrReqMoney) < 0)
            $xReq .= '<br /> - '.Lang::quest('money').Lang::main('colon').Util::formatMoney(abs($_));

        if ($xReq)
            $x .= '<br /><br /><span class="q">'.Lang::quest('requirements').Lang::main('colon').'</span>'.$xReq;

        $x .= '</td></tr></table>';

        return $x;
    }

    public function renderText(string $property = 'objectives', bool $jsEscaped = true) : string
    {
        if (!isset($this->{$property}) || !is_a($this->{$property}, LocString::class) || $this->{$property}->isEmpty())
            return '';

        $text = UIText::format($this->{$property}, Lang::FMT_HTML);

        return $jsEscaped ? Util::jsEscape($text) : $text;
    }

    public function getRequiredItems() : array
    {
        $result = [];
        foreach (array_filter($this->reqItemId) as $i => $v)
            if ($amt = $this->reqItemCount[$i])
                $result[$v] = ($result[$v] ?? 0) + $amt;

        return $result;
    }

    public function getRequiredReputation() : array
    {
        $result = [];
        foreach (array_filter($this->reqFactionId) as $i => $v)
            $result[$v] = ($result[$v] ?? 0) + $this->reqFactionValue[$i];

        return $result;
    }

    public function getRewardReputation() : array
    {
        $result = [];
        foreach (array_filter($this->rewardFactionId) as $i => $v)
            if ($amt = $this->rewardFactionValue[$i])
                $result[$v] = ($result[$v] ?? 0) + $amt;

        return $result;
    }

    public function getRewardChoiceItems() : array
    {
        $result = [];
        foreach (array_filter($this->rewardChoiceItemId) as $i => $v)
            if ($amt = $this->rewardChoiceItemCount[$i])
                $result[$v] = ($result[$v] ?? 0) + $amt;

        return $result;
    }

    public function getRewardCurrencies() : array
    {
        return $this->rewardCurrencies;
    }

    public function getRewardItems() : array
    {
        return $this->rewardItems;
    }

    public function isRepeatable() : bool
    {
        return $this->specialFlags & QUEST_FLAG_SPECIAL_REPEATABLE;
    }

    public function isDaily() : int
    {
        if ($this->flags & QUEST_FLAG_DAILY)
            return 1;

        if ($this->flags & QUEST_FLAG_WEEKLY)
            return 2;

        if ($this->specialFlags & QUEST_FLAG_SPECIAL_MONTHLY)
            return 3;

        return 0;
    }

    public function isAutoAccept() : bool
    {
        return $this->flags & QUEST_FLAG_AUTO_ACCEPT || $this->specialFlags & QUEST_FLAG_SPECIAL_AUTO_ACCEPT;
    }

    // by TC definition
    public function isSeasonal() : bool
    {
        return in_array($this->questSortIdBak, [-22, -284, -366, -369, -370, -376, -374]) && !$this->isRepeatable();
    }

    public function setCurrencyItems(array $currencyItems) : void
    {
        $this->currencyItemPairs = $currencyItems;
    }

    public static function fetchCurrencyItemPairs() : array
    {
        return DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `itemId` FROM ::currencies') ?: [];
    }
}

?>
