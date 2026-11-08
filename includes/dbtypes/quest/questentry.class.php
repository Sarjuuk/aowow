<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestEntry extends DBTypeEntry implements ISource, ITooltip
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
    public readonly  int       $rewardXPId;
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

    private static ?array $currencyItemPairs = null;        // load once for all quests

    private array $rewardItems      = [];
    private array $rewardCurrencies = [];
    private int   $repColFactionId;

    // client magic numbers
    private const array QUEST_GREEN_RANGE = [4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8];

    public const string QUERY_BASE = 'SELECT q.*, q.`id` AS ARRAY_KEY FROM ::quests q';
    public const array  QUERY_OPTS = array(
        'q'   => [],
        'nml' => ['j' => '::quests_search nml ON nml.`id` = q.`id` AND nml.`locale` = DB_LOC_I'],
        'rsc' => ['j' => '::spell rsc ON q.`rewardSpellCast` = rsc.`id`'], // limit rewardSpellCasts
        'qse' => ['j' => '::quests_startend qse ON q.`id` = qse.`questId`', 's' => ', qse.`method`'], // groupConcat..?
        'e'   => ['j' => ['::events e ON e.`id` = q.`eventId`', true], 's' => ', e.`holidayId`']
    );

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        parent::applyInitData($dbRow, $opts);

        $this->cuFlags       = $dbRow->cuFlags;
        $this->name          = new LocString($dbRow, 'name');
        $this->objectives    = new LocString($dbRow, 'objectives');
        $this->details       = new LocString($dbRow, 'details');
        $this->end           = new LocString($dbRow, 'end');
        $this->offerReward   = new LocString($dbRow, 'offerReward');
        $this->requestItems  = new LocString($dbRow, 'requestItems');
        $this->completed     = new LocString($dbRow, 'completed');
        $this->objectiveText = array(
            new LocString($dbRow, 'objectiveText1'),
            new LocString($dbRow, 'objectiveText2'),
            new LocString($dbRow, 'objectiveText3'),
            new LocString($dbRow, 'objectiveText4')
        );
        $this->questType            = $dbRow->questType;
        $this->level                = $dbRow->level;
        $this->minLevel             = $dbRow->minLevel;
        $this->maxLevel             = $dbRow->maxLevel;
        $this->questSortId          = $dbRow->questSortId;
        $this->questSortIdBak       = $dbRow->questSortIdBak;
        $this->questInfoId          = $dbRow->questInfoId;
        $this->suggestedPlayers     = $dbRow->suggestedPlayers;
        $this->timeLimit            = $dbRow->timeLimit;
        $this->eventId              = $dbRow->eventId;
        $this->prevQuestId          = $dbRow->prevQuestId;
        $this->nextQuestId          = $dbRow->nextQuestId;
        $this->breadcrumbForQuestId = $dbRow->breadcrumbForQuestId;
        $this->exclusiveGroup       = $dbRow->exclusiveGroup;
        $this->nextQuestIdChain     = $dbRow->nextQuestIdChain;
        $this->flags                = $dbRow->flags;
        $this->specialFlags         = $dbRow->specialFlags;

        $this->method    = $dbRow->method    ?? null;
        $this->holidayId = $dbRow->holidayId ?? null;

        $this->sourceItemId    = $dbRow->sourceItemId;
        $this->sourceItemCount = $dbRow->sourceItemCount;
        $this->sourceSpellId   = $dbRow->sourceSpellId;

        $this->reqClassMask     = $dbRow->reqClassMask;
        $this->reqRaceMask      = $dbRow->reqRaceMask;
        $this->reqSkillId       = $dbRow->reqSkillId;
        $this->reqSkillPoints   = $dbRow->reqSkillPoints;
        $this->reqMinRepFaction = $dbRow->reqMinRepFaction;
        $this->reqMaxRepFaction = $dbRow->reqMaxRepFaction;
        $this->reqMinRepValue   = $dbRow->reqMinRepValue;
        $this->reqMaxRepValue   = $dbRow->reqMaxRepValue;

        $this->rewardXPId            = $dbRow->rewardXPId;
        $this->rewardXP              = $dbRow->rewardXP;
        $this->rewardOrReqMoney      = $dbRow->rewardOrReqMoney;
        $this->rewardMoneyMaxLevel   = $dbRow->rewardMoneyMaxLevel;
        $this->rewardSpell           = $dbRow->rewardSpell;
        $this->rewardSpellCast       = $dbRow->rewardSpellCast;
        $this->rewardHonorPoints     = $dbRow->rewardHonorPoints;
        $this->rewardMailTemplateId  = $dbRow->rewardMailTemplateId;
        $this->rewardMailDelay       = $dbRow->rewardMailDelay;
        $this->rewardTitleId         = $dbRow->rewardTitleId;
        $this->rewardTalents         = $dbRow->rewardTalents;
        $this->rewardArenaPoints     = $dbRow->rewardArenaPoints;
        $this->rewardItemId          = [$dbRow->rewardItemId1,          $dbRow->rewardItemId2,          $dbRow->rewardItemId3,          $dbRow->rewardItemId4];
        $this->rewardItemCount       = [$dbRow->rewardItemCount1,       $dbRow->rewardItemCount2,       $dbRow->rewardItemCount3,       $dbRow->rewardItemCount4];
        $this->rewardChoiceItemId    = [$dbRow->rewardChoiceItemId1,    $dbRow->rewardChoiceItemId2,    $dbRow->rewardChoiceItemId3,    $dbRow->rewardChoiceItemId4,    $dbRow->rewardChoiceItemId5,    $dbRow->rewardChoiceItemId6];
        $this->rewardChoiceItemCount = [$dbRow->rewardChoiceItemCount1, $dbRow->rewardChoiceItemCount2, $dbRow->rewardChoiceItemCount3, $dbRow->rewardChoiceItemCount4, $dbRow->rewardChoiceItemCount5, $dbRow->rewardChoiceItemCount6];
        $this->rewardFactionId       = [$dbRow->rewardFactionId1,       $dbRow->rewardFactionId2,       $dbRow->rewardFactionId3,       $dbRow->rewardFactionId4,       $dbRow->rewardFactionId5];
        $this->rewardFactionValue    = [$dbRow->rewardFactionValue1,    $dbRow->rewardFactionValue2,    $dbRow->rewardFactionValue3,    $dbRow->rewardFactionValue4,    $dbRow->rewardFactionValue5];
        $this->reqPlayerKills        = $dbRow->reqPlayerKills;

        $this->reqItemId          = [$dbRow->reqItemId1,          $dbRow->reqItemId2,          $dbRow->reqItemId3,          $dbRow->reqItemId4,         $dbRow->reqItemId5,         $dbRow->reqItemId6];
        $this->reqItemCount       = [$dbRow->reqItemCount1,       $dbRow->reqItemCount2,       $dbRow->reqItemCount3,       $dbRow->reqItemCount4,      $dbRow->reqItemCount5,      $dbRow->reqItemCount6];
        $this->reqNpcOrGo         = [$dbRow->reqNpcOrGo1,         $dbRow->reqNpcOrGo2,         $dbRow->reqNpcOrGo3,         $dbRow->reqNpcOrGo4];
        $this->reqNpcOrGoCount    = [$dbRow->reqNpcOrGoCount1,    $dbRow->reqNpcOrGoCount2,    $dbRow->reqNpcOrGoCount3,    $dbRow->reqNpcOrGoCount4];
        $this->reqSourceItemId    = [$dbRow->reqSourceItemId1,    $dbRow->reqSourceItemId2,    $dbRow->reqSourceItemId3,    $dbRow->reqSourceItemId4];
        $this->reqSourceItemCount = [$dbRow->reqSourceItemCount1, $dbRow->reqSourceItemCount2, $dbRow->reqSourceItemCount3, $dbRow->reqSourceItemCount4];
        $this->reqFactionId       = [$dbRow->reqFactionId1,       $dbRow->reqFactionId2];
        $this->reqFactionValue    = [$dbRow->reqFactionValue1,    $dbRow->reqFactionValue2];

        // category
        $cat2 = 0;
        foreach (Game::QUEST_CLASSES as $c2 => $c1)
        {
            if (!in_array($dbRow->questSortId, $c1))
                continue;

            $cat2 = $c2;
            break;
        }
        $this->category1 = $dbRow->questSortId;
        $this->category2 = $cat2;

        // sort rewards into item + currency
        static::$currencyItemPairs ??= (DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `itemId` FROM ::currencies') ?: []);

        if ($_ = $dbRow->rewardTitleId)
            $rewards[Type::TITLE][] = $_;

        if ($_ = $dbRow->rewardHonorPoints)
            $this->rewardCurrencies[CURRENCY_HONOR_POINTS] = $_;
        if ($_ = $dbRow->rewardArenaPoints)
            $this->rewardCurrencies[CURRENCY_ARENA_POINTS] = $_;

        foreach (array_filter($this->rewardItemId) as $i => $rewItem)
        {
            if (!($qty = $this->rewardItemCount[$i]))
                continue;

            if (($k = array_search($rewItem, static::$currencyItemPairs)) !== false)
                $this->rewardCurrencies[$k] = ($this->rewardCurrencies[$k] ?? 0) + $qty;
            else
                $this->rewardItems[$rewItem] = ($this->rewardItems[$rewItem] ?? 0) + $qty;
        }

        return true;
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

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add quest to @return
     *  - GLOBALINFO_REWARDS - add rewarded items, spells, currencies and titles to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        $data[self::$dbType][$this->id] = ['name' => $this->name];

        if ($this->flags & QUEST_FLAG_DAILY)
            $data[self::$dbType][$this->id]['daily'] = true;

        if ($this->flags & QUEST_FLAG_WEEKLY)
            $data[self::$dbType][$this->id]['weekly'] = true;

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

        return $data;
    }

    public function getSourceData() : array
    {
        return array(
            'n'  => $this->name,
            't'  => Type::QUEST,
            'ti' => $this->id,
            'c'  => $this->category1,
            'c2' => $this->category2
        );
    }

    public function renderTooltip() : ?string
    {
        $title = UIText::unescapeUISequences(Util::htmlEscape($this->name), Lang::FMT_HTML);
        $text  =
        $level =
        $xReq  = '';

        if ($_ = $this->renderText('objectives', false))
            $text = '<br />'.$_;
        else if ($_ = $this->renderText('requestItems', false))
            $text = '<br />'.$_;
        else if ($_ = $this->renderText('offerReward', false))
            $text = '<br />'.$_;

        if ($level = min(0, $this->level))
        {
            $level = Lang::quest('questLevel', [$level]);

            if ($this->flags & QUEST_FLAG_DAILY)
                $level .= ' '.Lang::quest('daily');
        }

        for ($i = 0; $i < 4; $i++)
        {
            $ot  = $this->objectiveText[$i];
            $rng = $this->reqNpcOrGo[$i];
            $qty = $this->reqNpcOrGoCount[$i];

            if ($qty < 1 || !$rng)
                continue;

            $name = '';
            if (!$ot->isEmpty())
                $name = $ot;
            else if ($rng > 0)
                $name = CreatureEntry::getName($rng);
            else if ($rng < 0)
                $name = UIText::unescapeUISequences(GameobjectEntry::getName(-$rng), Lang::FMT_HTML);
            else
                $name = Util::ucFirst(Lang::game($rng > 0 ? 'npc' : 'object')).' #'.abs($rng);

            $xReq .= '<br /> - '.$name.($qty > 1 ? ' x '.$qty : '');
        }

        foreach ($this->reqItemId as $i => $ri)
        {
            if (($riQty = $this->reqItemCount[$i]) < 1)
                continue;

            $name = UIText::unescapeUISequences(ItemEntry::getName($ri), Lang::FMT_HTML) ?: Util::ucFirst(Lang::game('item')).' #'.$ri;

            $xReq .= '<br /> - '.$name.($riQty > 1 ? ' x '.$riQty : '');
        }

        if (!$this->end->isEmpty())
            $xReq .= '<br /> - '.$this->end;

        if (($_ = $this->rewardOrReqMoney) < 0)
            $xReq .= '<br /> - '.Lang::quest('money').Lang::main('colon').Util::formatMoney(abs($_));

        if ($xReq)
            $xReq .= '<br /><br /><span class="q">'.Lang::quest('requirements').'</span>'.$xReq;

        $tooltip = '';

        # Title #

        // level is non-standard
        if ($level)
            $tooltip .= '<table><tr><td><table width="100%"><tr><td><b class="q">'.$title.'</b></td><th><b class="q0">'.$level.'</b></th></tr></table></td></tr></table>';
        else
            $tooltip .= '<table><tr><td><b class="q">'.$title.'</b></td></tr></table>';

        # Description #

        // fallback to requestItems and offerReward texts is non-standard

        # Requirements #

        if ($text || $xReq)
            $tooltip .= '<table><tr><td>'.$text.$xReq.'</td></tr></table>';

        return $tooltip;
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

    public static function getSourceMore(int ...$ids) : array
    {
        $data = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `questSortId`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM %n WHERE `id` IN %in', static::$dataTable, $ids) ?: [];

        foreach ($data as $i => $d)
        {
            $cat2 = 0;
            foreach (Game::QUEST_CLASSES as $c2 => $c1)
            {
                if (!in_array($d['questSortId'], $c1))
                    continue;

                $cat2 = $c2;
                break;
            }

            $sm[$i] = array(
                'n'  => new LocString($d),
                't'  => Type::QUEST,
                'ti' => $i,
                'c'  => $d['questSortId'],
                'c2' => $cat2
            );
        }

        return $sm ?? [];
    }

    public function getDifficultyBreakpoints() : array
    {
        if (($lvl = $this->level) < 0)                      // -1: always at player level
            return [];

        return array(
            // red
            $this->minLevel < $lvl - 4 ?               $this->minLevel  : null,
            // orange
            $this->minLevel < $lvl - 2 ? max($lvl - 4, $this->minLevel) : null,
            // yellow
            $this->minLevel < $lvl + 3 ? max($lvl - 2, $this->minLevel) : null,
            // green
            $lvl + 3,
            // grey
            $lvl + 1 + self::QUEST_GREEN_RANGE[ max(19, intval($lvl / 5)) ]
        );
    }
}

?>
