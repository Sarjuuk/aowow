<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 7: Faction  g_initPath()
//  tabId 0: Database g_initHeader()
class FactionPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_FACTION;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 7];
    protected $tabId         = 0;
    protected $mode          = CACHETYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new FactionList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $smarty->notFound(Lang::$game['faction']);

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
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::$game['faction']));
    }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/
        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Quartermaster if any
        if ($ids = $this->subject->getField('qmNpcIds'))
        {
            $this->extendGlobalIds(TYPE_NPC, $ids);

            $qmStr = Lang::$faction['quartermaster'].Lang::$main['colon'];

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
            $infobox[] = Lang::$main['side'].Lang::$main['colon'].'[span class=icon-'.($_ == 1 ? 'alliance' : 'horde').']'.Lang::$game['si'][$_].'[/span]';

        /****************/
        /* Main Content */
        /****************/

        $this->extraText  = '';
        $this->infobox    = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true
        );

        // Spillover Effects
        /* todo (low): also check on reputation_spillover_template (but its data is identical to calculation below
        $rst = DB::Aowow()->selectRow('SELECT
            CONCAT_WS(" ", faction1, faction2, faction3, faction4) AS faction,
            CONCAT_WS(" ", rate_1,   rate_2,   rate_3,   rate_4)   AS rate,
            CONCAT_WS(" ", rank_1,   rank_2,   rank_3,   rank_4)   AS rank
            FROM reputation_spillover_template WHERE faction = ?d', $this->typeId);
        */


        $conditions = array(
            ['id', $this->typeId, '!'],                              // not self
            ['reputationIndex', -1, '!']                    // only gainable
        );

        if ($p = $this->subject->getField('parentFactionId'))     // linked via parent
            $conditions[] = ['OR', ['id', $p], ['parentFactionId', $p]];
        else
            $conditions[] = ['parentFactionId', $this->typeId];      // self as parent

        $spillover = new FactionList($conditions);
        $this->extendGlobalData($spillover->getJSGlobals());

        $buff = '';
        foreach ($spillover->iterate() as $spillId => $__)
            if ($val = ($spillover->getField('spilloverRateIn') * $this->subject->getField('spilloverRateOut') * 100))
                $buff .= '[tr][td][faction='.$spillId.'][/td][td][span class=q'.($val > 0 ? '2]+' : '10]').$val.'%[/span][/td][td]'.Lang::$game['rep'][$spillover->getField('spilloverMaxRank')].'[/td][/tr]';

        if ($buff)
            $this->extraText .= '[h3 class=clear]'.Lang::$faction['spillover'].'[/h3][div margin=15px]'.Lang::$faction['spilloverDesc'].'[/div][table class=grid width=400px][tr][td width=150px][b]'.Util::ucFirst(Lang::$game['faction']).'[/b][/td][td width=100px][b]'.Lang::$spell['_value'].'[/b][/td][td width=150px][b]'.Lang::$faction['maxStanding'].'[/b][/td][/tr]'.$buff.'[/table]';


        // reward rates
        if ($rates = DB::Aowow()->selectRow('SELECT * FROM reputation_reward_rate WHERE faction = ?d', $this->typeId))
        {
            $buff = '';
            foreach ($rates as $k => $v)
            {
                if ($v == 1)
                    continue;

                switch ($k)
                {
                    case 'quest_rate':          $buff .= '[tr][td]'.Lang::$game['quests'].Lang::$main['colon'].'[/td]';                                  break;
                    case 'quest_daily_rate':    $buff .= '[tr][td]'.Lang::$game['quests'].' ('.Lang::$quest['daily'].')'.Lang::$main['colon'].'[/td]';   break;
                    case 'quest_weekly_rate':   $buff .= '[tr][td]'.Lang::$game['quests'].' ('.Lang::$quest['weekly'].')'.Lang::$main['colon'].'[/td]';  break;
                    case 'quest_monthly_rate':  $buff .= '[tr][td]'.Lang::$game['quests'].' ('.Lang::$quest['monthly'].')'.Lang::$main['colon'].'[/td]'; break;
                    case 'creature_rate':       $buff .= '[tr][td]'.Lang::$game['npcs'].Lang::$main['colon'].'[/td]';                                    break;
                    case 'spell_rate':          $buff .= '[tr][td]'.Lang::$game['spells'].Lang::$main['colon'].'[/td]';                                  break;
                }

                $buff .= '[td width=30px align=right]x'.number_format($v, 1).'[/td][/tr]';
            }

            if ($buff)
                $this->extraText .= '[h3 class=clear][Custom Reward Rate][/h3][table]'.$buff.'[/table]';
        }

        // todo (low): create pendant from player_factionchange_reputations

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: items
        $items = new ItemList(array(['requiredFaction', $this->typeId]));
        if (!$items->error)
        {
            $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF));

            $tab = array(
                'file'    => 'item',
                'data'    => $items->getListviewData(),
                'showRep' => true,
                'params'  => array(
                    'extraCols' => '$_',
                    'sort'      => "$['standing', 'name']"
                )
            );

            if ($items->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $tab['params']['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=17;crs='.$this->typeId.';crv=0');

            $this->lvTabs[] = $tab;
        }

        // tab: creatures with onKill reputation
        if ($this->subject->getField('reputationIndex') != -1)        // only if you can actually gain reputation by kills
        {
            $cIds = DB::Aowow()->selectCol('SELECT DISTINCT cor.creature_id FROM creature_onkill_reputation cor, ?_factions f WHERE
                (cor.RewOnKillRepValue1 > 0 AND (cor.RewOnKillRepFaction1 = ?d OR (cor.RewOnKillRepFaction1 = f.id AND f.parentFactionId = ?d AND cor.IsTeamAward1 <> 0))) OR
                (cor.RewOnKillRepValue2 > 0 AND (cor.RewOnKillRepFaction2 = ?d OR (cor.RewOnKillRepFaction2 = f.id AND f.parentFactionId = ?d AND cor.IsTeamAward2 <> 0)))',
                $this->typeId, $this->subject->getField('parentFactionId'),
                $this->typeId, $this->subject->getField('parentFactionId')
            );

            if ($cIds)
            {
                $killCreatures = new CreatureList(array(['id', $cIds]));
                if (!$killCreatures->error)
                {
                    $tab = array(
                        'file'    => 'creature',
                        'data'    => $killCreatures->getListviewData(),
                        'showRep' => true,
                        'params'  => []
                    );

                    if ($killCreatures->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                        $tab['params']['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=42;crs='.$this->typeId.';crv=0');

                    $this->lvTabs[] = $tab;
                }
            }
        }

        // tab: members
        if ($_ = $this->subject->getField('templateIds'))
        {
            $members = new CreatureList(array(['faction', $_]));
            if (!$members->error)
            {
                $tab = array(
                    'file'    => 'creature',
                    'data'    => $members->getListviewData(),
                    'showRep' => true,
                    'params'  => array(
                        'id'   => 'member',
                        'name' => '$LANG.tab_members'
                    )
                );

                if ($members->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                    $tab['params']['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=3;crs='.$this->typeId.';crv=0');

                $this->lvTabs[] = $tab;
            }
        }

        // tab: objects
        if ($_ = $this->subject->getField('templateIds'))
        {
            $objects = new GameObjectList(array(['faction', $_]));
            if (!$objects->error)
            {
                $this->lvTabs[] = array(
                    'file'    => 'object',
                    'data'    => $objects->getListviewData(),
                    'params'  => []
                );
            }
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

            $tab = array(
                'file'    => 'quest',
                'data'    => $quests->getListviewData($this->typeId),
                'showRep' => true,
                'params'  => ['extraCols' => '$_']
            );

            if ($quests->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $tab['params']['note'] = sprintf(Util::$filterResultString, '?quests&filter=cr=1;crs='.$this->typeId.';crv=0');

            $this->lvTabs[] = $tab;
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

            $this->lvTabs[] = array(
                'file'   => 'achievement',
                'data'   => $acvs->getListviewData(),
                'params' => array(
                    'id'          => 'criteria-of',
                    'name'        => '$LANG.tab_criteriaof',
                    'visibleCols' => "$['category']"
                )
            );
        }
    }
}

?>
