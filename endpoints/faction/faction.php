<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'faction';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 7];

    public int $type   = Type::FACTION;
    public int $typeId = 0;

    private FactionList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new FactionList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('faction'), Lang::faction('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('faction')));


        /**************/
        /* Page Title */
        /**************/

        if ($foo = $this->subject->getField('cat'))
        {
            if ($bar = $this->subject->getField('cat2'))
                $this->breadcrumb[] = $bar;

            $this->breadcrumb[] = $foo;
        }


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Quartermaster if any
        if ($ids = $this->subject->getField('qmNpcIds'))
        {
            $this->extendGlobalIds(Type::NPC, ...$ids);

            $qmStr = Lang::faction('quartermaster');

            if (count($ids) ==  1)
                $qmStr .= '[npc='.$ids[0].']';
            else if (count($ids) > 1)
            {
                $qmStr .= '[ul]';
                foreach ($ids as $id)
                    $qmStr .= '[li][npc='.$id.'][/li]';

                $qmStr .= '[/ul]';
            }

            $infobox[] = $qmStr;
        }

        // side if any
        if ($_ = $this->subject->getField('side'))
            $infobox[] = Lang::main('side').'[span class=icon-'.($_ == SIDE_ALLIANCE ? 'alliance' : 'horde').']'.Lang::game('si', $_).'[/span]';

        // id
        $infobox[] = Lang::faction('id') . $this->typeId;

        // profiler relateed (note that this is part of the cache. I don't think this is important enough to calc for every view)
        if (Cfg::get('PROFILER_ENABLE') && !($this->subject->getField('cuFlags') & CUSTOM_EXCLUDE_FOR_LISTVIEW))
        {
            $x = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_completion_reputation WHERE `exalted` = 1 AND `factionId` = ?d', $this->typeId);
            $y = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_profiles WHERE `custom` = 0 AND `stub` = 0');
            $infobox[] = Lang::profiler('attainedBy', [round(($x ?: 0) * 100 / ($y ?: 1))]);

            // completion row added by InfoboxMarkup
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)                                       // unsure if this should be tracked (needs data dump in User::getCompletion())
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', 0);


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        // Spillover Effects
        /* todo (low): also check on reputation_spillover_template (but its data is identical to calculation below
        $rst = DB::World()->selectRow('SELECT
            CONCAT_WS(" ", faction1, faction2, faction3, faction4) AS faction,
            CONCAT_WS(" ", rate_1,   rate_2,   rate_3,   rate_4)   AS rate,
            CONCAT_WS(" ", rank_1,   rank_2,   rank_3,   rank_4)   AS rank
            FROM reputation_spillover_template WHERE faction = ?d', $this->typeId);
        */


        $conditions = array(
            ['id', $this->typeId, '!'],                     // not self
            ['repIdx', -1, '!']                             // only gainable
        );

        if ($p = $this->subject->getField('parentFactionId')) // linked via parent
            $conditions[] = ['OR', ['id', $p], ['parentFactionId', $p]];
        else                                                // self as parent
            $conditions[] = ['parentFactionId', $this->typeId];

        $spillover = new FactionList($conditions);
        $this->extendGlobalData($spillover->getJSGlobals());

        $buff = '';
        foreach ($spillover->iterate() as $spillId => $__)
            if ($val = ($spillover->getField('spilloverRateIn') * $this->subject->getField('spilloverRateOut') * 100))
                $buff .= '[tr][td][faction='.$spillId.'][/td][td][span class=q'.($val > 0 ? '2]+' : '10]').$val.'%[/span][/td][td]'.Lang::game('rep', $spillover->getField('spilloverMaxRank')).'[/td][/tr]';

        if ($buff)
            $this->extraText = new Markup(
                '[h3 class=clear]'.Lang::faction('spillover').'[/h3][div margin=15px]'.Lang::faction('spilloverDesc').'[/div][table class=grid width=400px][tr][td width=150px][b]'.Util::ucFirst(Lang::game('faction')).'[/b][/td][td width=100px][b]'.Lang::spell('_value').'[/b][/td][td width=150px][b]'.Lang::faction('maxStanding').'[/b][/td][/tr]'.$buff.'[/table]',
                ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN],
                'text-generic'
            );

        // reward rates (ultimately this should be calculated into each reward display)
        if ($rates = DB::World()->selectRow('SELECT `quest_rate`, `quest_daily_rate`, `quest_weekly_rate`, `quest_monthly_rate`, `quest_repeatable_rate`, `creature_rate`, `spell_rate` FROM reputation_reward_rate WHERE `faction` = ?d', $this->typeId))
        {
            $buff = '';
            foreach ($rates as $k => $v)
            {
                if ($v == 1)
                    continue;

                $head = match ($k)
                {
                    'quest_rate'            => Lang::game('quests'),
                    'quest_daily_rate'      => Lang::game('quests').' ('.Lang::quest('daily').')',
                    'quest_weekly_rate'     => Lang::game('quests').' ('.Lang::quest('weekly').')',
                    'quest_monthly_rate'    => Lang::game('quests').' ('.Lang::quest('monthly').')',
                    'quest_repeatable_rate' => Lang::game('quests').' ('.Lang::quest('repeatable').')',
                    'creature_rate'         => Lang::game('npcs'),
                    'spell_rate'            => Lang::game('spells')
                };

                $buff .= '[tr][td]'.$head.Lang::main('colon').'[/td][td width=35px align=right][span class=q'.($v < 1 ? '10]' : '2]+').intVal(($v - 1) * 100).'%[/span][/td][/tr]';
            }

            if ($buff && $this->extraText)
                $this->extraText->append('[h3 class=clear]'.Lang::faction('customRewRate').'[/h3][table class=grid width=250px]'.$buff.'[/table]');
            else if ($buff)
                $this->extraText = new Markup('[h3 class=clear]'.Lang::faction('customRewRate').'[/h3][table class=grid width=250px]'.$buff.'[/table]', ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN], 'text-generic');
        }

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = ?d, `alliance_id`, -`horde_id`) FROM player_factionchange_reputations WHERE `alliance_id` = ?d OR `horde_id` = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altFac = new FactionList(array(['id', abs($pendant)]));
            if (!$altFac->error)
            {
                $this->transfer = Lang::faction('_transfer', array(
                    $altFac->id,
                    $altFac->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ));
            }
        }


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: items
        $items = new ItemList(array(Listview::DEFAULT_SIZE, ['requiredFaction', $this->typeId]), ['calcTotal' => true]);
        if (!$items->error)
        {
            $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF));

            $tabData = array(
                'data'      => $items->getListviewData(),
                'extraCols' => '$_',
                'sort'      => ['standing', 'name']
            );

            if ($items->getMatches() > Listview::DEFAULT_SIZE)
                if (!is_null(ItemListFilter::getCriteriaIndex(17, $this->typeId)))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=17;crs='.$this->typeId.';crv=0');

            $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile, 'itemStandingCol'));
        }

        // tab: creatures with onKill reputation
        // only if you can actually gain reputation by kills
        if ($this->subject->getField('reputationIndex') != -1)
        {
            // inherit siblings/children from $spillover
            $cRep = DB::World()->selectCol('SELECT DISTINCT `creature_id` AS ARRAY_KEY, `qty` FROM (
                    SELECT `creature_id`, `RewOnKillRepValue1` as "qty" FROM creature_onkill_reputation WHERE `RewOnKillRepValue1` > 0 AND (`RewOnKillRepFaction1` = ?d { OR (`RewOnKillRepFaction1` IN (?a) AND `IsTeamAward1` <> 0) } ) UNION
                    SELECT `creature_id`, `RewOnKillRepValue2` as "qty" FROM creature_onkill_reputation WHERE `RewOnKillRepValue2` > 0 AND (`RewOnKillRepFaction2` = ?d { OR (`RewOnKillRepFaction2` IN (?a) AND `IsTeamAward2` <> 0) } )
                ) x',
                $this->typeId, $spillover->getFoundIDs() ?: DBSIMPLE_SKIP,
                $this->typeId, $spillover->getFoundIDs() ?: DBSIMPLE_SKIP
            );

            if ($cRep)
            {
                $killCreatures = new CreatureList(array(Listview::DEFAULT_SIZE, ['id', array_keys($cRep)]), ['calcTotal' => true]);
                if (!$killCreatures->error)
                {
                    $data = $killCreatures->getListviewData();
                    foreach ($data as $id => &$d)
                        $d['reputation'] = $cRep[$id];

                    $tabData = array(
                        'data'      => $data,
                        'extraCols' => '$_',
                        'sort'      => ['-reputation', 'name']
                    );

                    if ($killCreatures->getMatches() > Listview::DEFAULT_SIZE)
                        if (!is_null(CreatureListFilter::getCriteriaIndex(42, $this->typeId)))
                            $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=42;crs='.$this->typeId.';crv=0');

                    $this->addDataLoader('zones');
                    $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile, 'npcRepCol'));
                }
            }
        }

        // tab: members
        if ($_ = $this->subject->getField('templateIds'))
        {
            $members = new CreatureList(array(Listview::DEFAULT_SIZE, ['faction', $_]), ['calcTotal' => true]);
            if (!$members->error)
            {
                $tabData = array(
                    'data' => $members->getListviewData(),
                    'id'   => 'member',
                    'name' => '$LANG.tab_members'
                );

                if ($members->getMatches() > Listview::DEFAULT_SIZE)
                    if (!is_null(CreatureListFilter::getCriteriaIndex(3, $this->typeId)))
                        $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=3;crs='.$this->typeId.';crv=0');

                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile));
            }
        }

        // tab: objects
        if ($_ = $this->subject->getField('templateIds'))
        {
            $objects = new GameObjectList(array(['faction', $_]));
            if (!$objects->error)
            {
                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(['data' => $objects->getListviewData()], GameObjectList::$brickFile));
            }
        }

        // tab: quests
        $conditions = array(
            'OR',
            Listview::DEFAULT_SIZE,
            ['AND', ['rewardFactionId1', $this->typeId], ['rewardFactionValue1', 0, '>']],
            ['AND', ['rewardFactionId2', $this->typeId], ['rewardFactionValue2', 0, '>']],
            ['AND', ['rewardFactionId3', $this->typeId], ['rewardFactionValue3', 0, '>']],
            ['AND', ['rewardFactionId4', $this->typeId], ['rewardFactionValue4', 0, '>']],
            ['AND', ['rewardFactionId5', $this->typeId], ['rewardFactionValue5', 0, '>']]
        );
        $quests = new QuestList($conditions, ['calcTotal' => true]);
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals(GLOBALINFO_ANY));

            $tabData = array(
                'data'      => $quests->getListviewData($this->typeId),
                'extraCols' => '$_'
            );

            if ($quests->getMatches() > Listview::DEFAULT_SIZE)
                if (!is_null(QuestListFilter::getCriteriaIndex(1, $this->typeId)))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?quests&filter=cr=1;crs='.$this->typeId.';crv=0');

            $this->lvTabs->addListviewTab(new Listview($tabData, QuestList::$brickFile, 'questRepCol'));
        }

        // tab: achievements
        $conditions = array(
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION],
            ['ac.value1', $this->typeId]
        );
        $acvs = new AchievementList($conditions);
        if (!$acvs->error)
        {
            $this->extendGlobalData($acvs->getJSGlobals(GLOBALINFO_ANY));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'        => $acvs->getListviewData(),
                'id'          => 'criteria-of',
                'name'        => '$LANG.tab_criteriaof',
                'visibleCols' => ['category']
            ), AchievementList::$brickFile));
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::FACTION, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }
}

?>
