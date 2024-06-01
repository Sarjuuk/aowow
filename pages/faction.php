<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 7: Faction  g_initPath()
//  tabId 0: Database g_initHeader()
class FactionPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = Type::FACTION;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 7];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new FactionList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('faction'), Lang::faction('notFound'));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        if ($foo = $this->subject->getField('cat'))
        {
            if ($bar = $this->subject->getField('cat2'))
                $this->path[] = $bar;

            $this->path[] = $foo;
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('faction')));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=zones']);

        /***********/
        /* Infobox */
        /***********/
        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Quartermaster if any
        if ($ids = $this->subject->getField('qmNpcIds'))
        {
            $this->extendGlobalIds(Type::NPC, ...$ids);

            $qmStr = Lang::faction('quartermaster').Lang::main('colon');

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
            $infobox[] = Lang::main('side').Lang::main('colon').'[span class=icon-'.($_ == 1 ? 'alliance' : 'horde').']'.Lang::game('si', $_).'[/span]';

        /****************/
        /* Main Content */
        /****************/

        $this->extraText  = '';
        $this->infobox    = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
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
            $this->extraText .= '[h3 class=clear]'.Lang::faction('spillover').'[/h3][div margin=15px]'.Lang::faction('spilloverDesc').'[/div][table class=grid width=400px][tr][td width=150px][b]'.Util::ucFirst(Lang::game('faction')).'[/b][/td][td width=100px][b]'.Lang::spell('_value').'[/b][/td][td width=150px][b]'.Lang::faction('maxStanding').'[/b][/td][/tr]'.$buff.'[/table]';


        // reward rates (ultimately this should be calculated into each reward display)
        if ($rates = DB::World()->selectRow('SELECT * FROM reputation_reward_rate WHERE faction = ?d', $this->typeId))
        {
            $buff = '';
            foreach ($rates as $k => $v)
            {
                if ($v == 1)
                    continue;

                switch ($k)
                {
                    case 'quest_rate':            $buff .= '[tr][td]'.Lang::game('quests')                                   .Lang::main('colon').'[/td]'; break;
                    case 'quest_daily_rate':      $buff .= '[tr][td]'.Lang::game('quests').' ('.Lang::quest('daily').')'     .Lang::main('colon').'[/td]'; break;
                    case 'quest_weekly_rate':     $buff .= '[tr][td]'.Lang::game('quests').' ('.Lang::quest('weekly').')'    .Lang::main('colon').'[/td]'; break;
                    case 'quest_monthly_rate':    $buff .= '[tr][td]'.Lang::game('quests').' ('.Lang::quest('monthly').')'   .Lang::main('colon').'[/td]'; break;
                    case 'quest_repeatable_rate': $buff .= '[tr][td]'.Lang::game('quests').' ('.Lang::quest('repeatable').')'.Lang::main('colon').'[/td]'; break;
                    case 'creature_rate':         $buff .= '[tr][td]'.Lang::game('npcs')                                     .Lang::main('colon').'[/td]'; break;
                    case 'spell_rate':            $buff .= '[tr][td]'.Lang::game('spells')                                   .Lang::main('colon').'[/td]'; break;
                    default:
                        continue 2;
                }

                $buff .= '[td width=35px align=right][span class=q'.($v < 1 ? '10]' : '2]+').intVal(($v - 1) * 100).'%[/span][/td][/tr]';
            }

            if ($buff)
                $this->extraText .= '[h3 class=clear]'.Lang::faction('customRewRate').'[/h3][table]'.$buff.'[/table]';
        }

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_reputations WHERE alliance_id = ?d OR horde_id = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altFac = new FactionList(array(['id', abs($pendant)]));
            if (!$altFac->error)
            {
                $this->transfer = sprintf(
                    Lang::faction('_transfer'),
                    $altFac->id,
                    $altFac->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', 1) : Lang::game('si', 2)
                );
            }
        }

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: items
        $items = new ItemList(array(['requiredFaction', $this->typeId]));
        if (!$items->error)
        {
            $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF));

            $tabData = array(
                'data'      => array_values($items->getListviewData()),
                'extraCols' => '$_',
                'sort'      => ['standing', 'name']
            );

            if ($items->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
                $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=17;crs='.$this->typeId.';crv=0');

            $this->lvTabs[] = [ItemList::$brickFile, $tabData, 'itemStandingCol'];
        }

        // tab: creatures with onKill reputation
        if ($this->subject->getField('reputationIndex') != -1)        // only if you can actually gain reputation by kills
        {
            // inherit siblings/children from $spillover
            $cRep = DB::World()->selectCol('SELECT DISTINCT creature_id AS ARRAY_KEY, qty FROM (
                    SELECT creature_id, RewOnKillRepValue1 as qty FROM creature_onkill_reputation WHERE RewOnKillRepValue1 > 0 AND (RewOnKillRepFaction1 = ?d{ OR (RewOnKillRepFaction1 IN (?a) AND IsTeamAward1 <> 0)}) UNION
                    SELECT creature_id, RewOnKillRepValue2 as qty FROM creature_onkill_reputation WHERE RewOnKillRepValue2 > 0 AND (RewOnKillRepFaction2 = ?d{ OR (RewOnKillRepFaction2 IN (?a) AND IsTeamAward2 <> 0)})
                ) x',
                $this->typeId, $spillover->getFoundIDs() ?: DBSIMPLE_SKIP,
                $this->typeId, $spillover->getFoundIDs() ?: DBSIMPLE_SKIP
            );

            if ($cRep)
            {
                $killCreatures = new CreatureList(array(['id', array_keys($cRep)]));
                if (!$killCreatures->error)
                {
                    $data = $killCreatures->getListviewData();
                    foreach ($data as $id => &$d)
                        $d['reputation'] = $cRep[$id];

                    $tabData = array(
                        'data'      => array_values($data),
                        'extraCols' => '$_',
                        'sort'      => ['-reputation', 'name']
                    );

                    if ($killCreatures->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
                        $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=42;crs='.$this->typeId.';crv=0');

                    $this->lvTabs[] = [CreatureList::$brickFile, $tabData, 'npcRepCol'];
                }
            }
        }

        // tab: members
        if ($_ = $this->subject->getField('templateIds'))
        {
            $members = new CreatureList(array(['faction', $_]));
            if (!$members->error)
            {
                $tabData = array(
                    'data' => array_values($members->getListviewData()),
                    'id'   => 'member',
                    'name' => '$LANG.tab_members'
                );

                if ($members->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=3;crs='.$this->typeId.';crv=0');

                $this->lvTabs[] = [CreatureList::$brickFile, $tabData];
            }
        }

        // tab: objects
        if ($_ = $this->subject->getField('templateIds'))
        {
            $objects = new GameObjectList(array(['faction', $_]));
            if (!$objects->error)
                $this->lvTabs[] = [GameObjectList::$brickFile, ['data' => array_values($objects->getListviewData())]];
        }

        // tab: quests
        $conditions = array(
            ['AND', ['rewardFactionId1', $this->typeId], ['rewardFactionValue1', 0, '>']],
            ['AND', ['rewardFactionId2', $this->typeId], ['rewardFactionValue2', 0, '>']],
            ['AND', ['rewardFactionId3', $this->typeId], ['rewardFactionValue3', 0, '>']],
            ['AND', ['rewardFactionId4', $this->typeId], ['rewardFactionValue4', 0, '>']],
            ['AND', ['rewardFactionId5', $this->typeId], ['rewardFactionValue5', 0, '>']],
            'OR'
        );
        $quests = new QuestList($conditions);
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals(GLOBALINFO_ANY));

            $tabData = array(
                'data'      => array_values($quests->getListviewData($this->typeId)),
                'extraCols' => '$_'
            );

            if ($quests->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
                $tabData['note'] = sprintf(Util::$filterResultString, '?quests&filter=cr=1;crs='.$this->typeId.';crv=0');

            $this->lvTabs[] = [QuestList::$brickFile, $tabData, 'questRepCol'];
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

            $this->lvTabs[] = [AchievementList::$brickFile, array(
                'data'        => array_values($acvs->getListviewData()),
                'id'          => 'criteria-of',
                'name'        => '$LANG.tab_criteriaof',
                'visibleCols' => ['category']
            )];
        }
    }
}

?>
