<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 3: Quest    g_initPath()
//  tabId 0: Database g_initHeader()
class QuestPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_QUEST;
    protected $typeId        = 0;
    protected $tpl           = 'quest';
    protected $path          = [0, 3];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $css           = [['path' => 'Book.css']];
    protected $js            = ['ShowOnMap.js'];

    private   $powerTpl      = '$WowheadPower.registerQuest(%d, %d, %s);';

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new QuestList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('quest'), Lang::quest('notFound'));

        // may contain htmlesque tags
        $this->name = Util::htmlEscape($this->subject->getField('name', true));
    }

    protected function generatePath()
    {
        // recreate path
        $this->path[] = $this->subject->getField('cat2');
        if ($_ = $this->subject->getField('cat1'))
            $this->path[] = $_;
    }

    protected function generateTitle()
    {
        // page title already escaped
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('quest')));
    }

    protected function generateContent()
    {
        $_level        = $this->subject->getField('level');
        $_minLevel     = $this->subject->getField('minLevel');
        $_flags        = $this->subject->getField('flags');
        $_specialFlags = $this->subject->getField('specialFlags');
        $_side         = Game::sideByRaceMask($this->subject->getField('reqRaceMask'));

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // event (todo: assign eventData)
        if ($_ = $this->subject->getField('eventId'))
        {
            $this->extendGlobalIds(TYPE_WORLDEVENT, $_);
            $infobox[] = Lang::game('eventShort').Lang::main('colon').'[event='.$_.']';
        }

        // level
        if ($_level > 0)
            $infobox[] = Lang::game('level').Lang::main('colon').$_level;

        // reqlevel
        if ($_minLevel)
        {
            $lvl = $_minLevel;
            if ($_ = $this->subject->getField('maxLevel'))
                $lvl .= ' - '.$_;

            $infobox[] = sprintf(Lang::game('reqLevel'), $lvl);
        }

        // loremaster (i dearly hope those flags cover every case...)
        if ($this->subject->getField('zoneOrSortBak') > 0 && !$this->subject->isRepeatable())
        {
            $conditions = array(
                ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE],
                ['ac.value1', $this->subject->getField('zoneOrSortBak')],
                ['a.faction', $_side, '&']
            );
            $loremaster = new AchievementList($conditions);
            $this->extendGlobalData($loremaster->getJSGlobals(GLOBALINFO_SELF));

            switch ($loremaster->getMatches())
            {
                case 0:
                    break;
                case 1:
                    $infobox[] = Lang::quest('loremaster').Lang::main('colon').'[achievement='.$loremaster->id.']';
                    break;
                default:
                    $lm = Lang::quest('loremaster').Lang::main('colon').'[ul]';
                    foreach ($loremaster->iterate() as $id => $__)
                        $lm .= '[li][achievement='.$id.'][/li]';

                    $infobox[] = $lm.'[/ul]';
                    break;
            }
        }

        // type (maybe expand uppon?)
        $_ = [];
        if ($_flags & QUEST_FLAG_DAILY)
            $_[] = '[tooltip=tooltip_dailyquest]'.Lang::quest('daily').'[/tooltip]';
        else if ($_flags & QUEST_FLAG_WEEKLY)
            $_[] = Lang::quest('weekly');
        else if ($_specialFlags & QUEST_FLAG_SPECIAL_MONTHLY)
            $_[] = Lang::quest('monthly');

        if ($t = $this->subject->getField('type'))
            $_[] = Lang::quest('questInfo', $t);

        if ($_)
            $infobox[] = Lang::game('type').Lang::main('colon').implode(' ', $_);

        // side
        $_ = Lang::main('side').Lang::main('colon');
        switch ($_side)
        {
            case 3: $infobox[] = $_.Lang::game('si', 3);                                        break;
            case 2: $infobox[] = $_.'[span class=icon-horde]'.Lang::game('si', 2).'[/span]';    break;
            case 1: $infobox[] = $_.'[span class=icon-alliance]'.Lang::game('si', 1).'[/span]'; break;
        }

        $jsg = [];
        // races
        if ($_ = Lang::getRaceString($this->subject->getField('reqRaceMask'), $jsg, false))
        {
            $this->extendGlobalIds(TYPE_RACE, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('race') : Lang::game('races');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // classes
        if ($_ = Lang::getClassString($this->subject->getField('reqClassMask'), $jsg, false))
        {
            $this->extendGlobalIds(TYPE_CLASS, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // profession / skill
        if ($_ = $this->subject->getField('reqSkillId'))
        {
            $this->extendGlobalIds(TYPE_SKILL, $_);
            $sk =  '[skill='.$_.']';
            if ($_ = $this->subject->getField('reqSkillPoints'))
                $sk .= ' ('.$_.')';

            $infobox[] = Lang::quest('profession').Lang::main('colon').$sk;
        }

        // timer
        if ($_ = $this->subject->getField('timeLimit'))
            $infobox[] = Lang::quest('timer').Lang::main('colon').Util::formatTime($_ * 1000);

        $startEnd = DB::Aowow()->select('SELECT * FROM ?_quests_startend WHERE questId = ?d', $this->typeId);

        // start
        $start = '[icon name=quest_start'.($this->subject->isRepeatable() ? '_daily' : '').']'.Lang::event('start').Lang::main('colon').'[/icon]';
        $s     = [];
        foreach ($startEnd as $se)
        {
            if ($se['method'] & 0x1)
            {
                $this->extendGlobalIds($se['type'], $se['typeId']);
                $s[] = ($s ? '[span=invisible]'.$start.'[/span] ' : $start.' ') .'['.Util::$typeStrings[$se['type']].'='.$se['typeId'].']';
            }
        }

        if ($s)
            $infobox[] = implode('[br]', $s);

        // end
        $end = '[icon name=quest_end'.($this->subject->isRepeatable() ? '_daily' : '').']'.Lang::event('end').Lang::main('colon').'[/icon]';
        $e   = [];
        foreach ($startEnd as $se)
        {
            if ($se['method'] & 0x2)
            {
                $this->extendGlobalIds($se['type'], $se['typeId']);
                $e[] = ($e ? '[span=invisible]'.$end.'[/span] ' : $end.' ') . '['.Util::$typeStrings[$se['type']].'='.$se['typeId'].']';
            }
        }

        if ($e)
            $infobox[] = implode('[br]', $e);

        // Repeatable
        if ($this->subject->isRepeatable())
            $infobox[] = Lang::quest('repeatable');

        // sharable | not sharable
        $infobox[] = $_flags & QUEST_FLAG_SHARABLE ? Lang::quest('sharable') : Lang::quest('notSharable');

        // Keeps you PvP flagged
        if ($this->subject->isPvPEnabled())
            $infobox[] = Lang::quest('keepsPvpFlag');

        // difficulty (todo (low): formula unclear. seems to be [minLevel,] -4, -2, (level), +3, +(9 to 15))
        if ($_level > 0)
        {
            $_ = [];

            // red
            if ($_minLevel && $_minLevel < $_level - 4)
                $_[] = '[color=q10]'.$_minLevel.'[/color]';

            // orange
            if (!$_minLevel || $_minLevel < $_level - 2)
                $_[] = '[color=r1]'.(!$_ && $_minLevel > $_level - 4 ? $_minLevel : $_level - 4).'[/color]';

            // yellow
            $_[] = '[color=r2]'.(!$_ && $_minLevel > $_level - 2 ? $_minLevel : $_level - 2).'[/color]';

            // green
            $_[] = '[color=r3]'.($_level + 3).'[/color]';

            // grey (is about +/-1 level off)
            $_[] = '[color=r4]'.($_level + 3 + ceil(12 * $_level / MAX_LEVEL)).'[/color]';

            if ($_)
                $infobox[] = Lang::game('difficulty').Lang::main('colon').implode('[small] &nbsp;[/small]', $_);
        }

        $this->infobox = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';

        /**********/
        /* Series */
        /**********/

        // Quest Chain (are there cases where quests go in parallel?)
        $chain = array(
            array(
                array(
                    'side'    => $_side,
                    'typeStr' => Util::$typeStrings[TYPE_QUEST],
                    'typeId'  => $this->typeId,
                    'name'    => $this->name,
                    '_next'   => $this->subject->getField('nextQuestIdChain')
                )
            )
        );

        $_ = $chain[0][0];
        while ($_)
        {
            if ($_ = DB::Aowow()->selectRow('SELECT id AS typeId, IF(id = nextQuestIdChain, 1, 0) AS error, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, reqRaceMask FROM ?_quests WHERE nextQuestIdChain = ?d', $_['typeId']))
            {
                if ($_['error'])
                {
                    trigger_error('Quest '.$_['typeId'].' is in a chain with itself');
                    break;
                }

                $n = Util::localizedString($_, 'name');
                array_unshift($chain, array(
                    array(
                        'side'    => Game::sideByRaceMask($_['reqRaceMask']),
                        'typeStr' => Util::$typeStrings[TYPE_QUEST],
                        'typeId'  => $_['typeId'],
                        'name'    => Util::htmlEscape(Lang::trimTextClean($n, 40)),
                    )
                ));
            }
        }

        $_ = end($chain)[0];
        while ($_)
        {
            if ($_ = DB::Aowow()->selectRow('SELECT id AS typeId, IF(id = nextQuestIdChain, 1, 0) AS error, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, reqRaceMask, nextQuestIdChain AS _next FROM ?_quests WHERE id = ?d', $_['_next']))
            {
                if ($_['error'])                                // error already triggered
                    break;

                $n = Util::localizedString($_, 'name');
                array_push($chain, array(
                    array(
                        'side'    => Game::sideByRaceMask($_['reqRaceMask']),
                        'typeStr' => Util::$typeStrings[TYPE_QUEST],
                        'typeId'  => $_['typeId'],
                        'name'    => Util::htmlEscape(Lang::trimTextClean($n, 40)),
                        '_next'   => $_['_next'],
                    )
                ));
            }
        }

        if (count($chain) > 1)
            $this->series[] = [$chain, null];


        // todo (low): sensibly merge the following lists into 'series'
        $listGen = function($cnd)
        {
            $chain = [];
            $list  = new QuestList($cnd);
            if ($list->error)
                return null;

            foreach ($list->iterate() as $id => $__)
            {
                $n = $list->getField('name', true);
                $chain[] = array(array(
                    'side'    => Game::sideByRaceMask($list->getField('reqRaceMask')),
                    'typeStr' => Util::$typeStrings[TYPE_QUEST],
                    'typeId'  => $id,
                    'name'    => Util::htmlEscape(Lang::trimTextClean($n, 40))
                ));
            }

            return $chain;
        };

        $extraLists = array(
            // Requires all of these quests (Quests that you must follow to get this quest)
            ['reqQ',       array('OR', ['AND', ['nextQuestId', $this->typeId], ['exclusiveGroup', 0, '<']], ['AND', ['id', $this->subject->getField('prevQuestId')], ['nextQuestIdChain', $this->typeId, '!']])],

            // Requires one of these quests (Requires one of the quests to choose from)
            ['reqOneQ',    array('OR', ['AND', ['exclusiveGroup', 0, '>'], ['nextQuestId', $this->typeId]], ['breadCrumbForQuestId', $this->typeId])],

            // Opens Quests (Quests that become available only after complete this quest (optionally only one))
            ['opensQ',     array('OR', ['AND', ['prevQuestId', $this->typeId], ['id', $this->subject->getField('nextQuestIdChain'), '!']], ['id', $this->subject->getField('nextQuestId')], ['id', $this->subject->getField('breadcrumbForQuestId')])],

            // Closes Quests (Quests that become inaccessible after completing this quest)
            ['closesQ',    array(['exclusiveGroup', 0, '>'], ['exclusiveGroup', $this->subject->getField('exclusiveGroup')], ['id', $this->typeId, '!'])],

            // During the quest available these quests (Quests that are available only at run time this quest)
            ['enablesQ',   array(['prevQuestId', -$this->typeId])],

            // Requires an active quest (Quests during the execution of which is available on the quest)
            ['enabledByQ', array(['id', -$this->subject->getField('prevQuestId')])]
        );

        foreach ($extraLists as $el)
            if ($_ = $listGen($el[1]))
                $this->series[] = [$_, sprintf(Util::$dfnString, Lang::quest($el[0].'Desc'), Lang::quest($el[0]))];

        /*******************/
        /* Objectives List */
        /*******************/

        $this->objectiveList = [];
        $this->providedItem  = [];

        // gather ids for lookup
        $olItems    = $olNPCs    = $olGOs    = $olFactions = [];
        $olItemData = $olNPCData = $olGOData = null;

        // items
        $olItems[0] = array(                                // srcItem on idx:0
            $this->subject->getField('sourceItemId'),
            $this->subject->getField('sourceItemCount'),
            false
        );

        for ($i = 1; $i < 7; $i++)                          // reqItem in idx:1-6
        {
            $id  = $this->subject->getField('reqItemId'.$i);
            $qty = $this->subject->getField('reqItemCount'.$i);
            if (!$id || !$qty)
                continue;

            $olItems[$i] = [$id, $qty, $id == $olItems[0][0]];
        }

        if ($ids = array_column($olItems, 0))
        {
            $olItemData = new ItemList(array(['id', $ids]));
            $this->extendGlobalData($olItemData->getJSGlobals(GLOBALINFO_SELF));

            $providedRequired = false;
            foreach ($olItems as $i => [$itemId, $qty, $provided])
            {
                if (!$i || !$itemId || !in_array($itemId, $olItemData->getFoundIDs()))
                    continue;

                if ($provided)
                    $providedRequired = true;

                $this->objectiveList[] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                    'id'        => $itemId,
                    'name'      => $olItemData->json[$itemId]['name'],
                    'qty'       => $qty > 1 ? $qty : 0,
                    'quality'   => 7 - $olItemData->json[$itemId]['quality'],
                    'extraText' => $provided ? '&nbsp;('.Lang::quest('provided').')' : ''
                );
            }

            // if providd item is not required by quest, list it below other requirements
            if (!$providedRequired && $olItems[0][0] && in_array($olItems[0][0], $olItemData->getFoundIDs()))
            {
                $this->providedItem = array(
                    'id'        => $olItems[0][0],
                    'name'      => $olItemData->json[$olItems[0][0]]['name'],
                    'qty'       => $olItems[0][1] > 1 ? $olItems[0][1] : 0,
                    'quality'   => 7 - $olItemData->json[$olItems[0][0]]['quality']
                );
            }
        }

        // creature or GO...
        for ($i = 1; $i < 5; $i++)
        {
            $id     = $this->subject->getField('reqNpcOrGo'.$i);
            $qty    = $this->subject->getField('reqNpcOrGoCount'.$i);
            $altTxt = $this->subject->getField('objectiveText'.$i, true);
            if ($id > 0 && $qty)
                $olNPCs[$id] = [$qty, $altTxt, []];
            else if ($id < 0 && $qty)
                $olGOs[-$id] = [$qty, $altTxt];
        }

        // .. creature kills
        if ($ids = array_keys($olNPCs))
        {
            $olNPCData = new CreatureList(array('OR', ['id', $ids], ['killCredit1', $ids], ['killCredit2', $ids]));
            $this->extendGlobalData($olNPCData->getJSGlobals(GLOBALINFO_SELF));

            // create proxy-references
            foreach ($olNPCData->iterate() as $id => $__)
            {
                if ($p = $olNPCData->getField('KillCredit1'))
                    if (isset($olNPCs[$p]))
                        $olNPCs[$p][2][$id] = $olNPCData->getField('name', true);

                if ($p = $olNPCData->getField('KillCredit2'))
                    if (isset($olNPCs[$p]))
                        $olNPCs[$p][2][$id] = $olNPCData->getField('name', true);
            }

            foreach ($olNPCs as $i => $pair)
            {
                if (!$i || !in_array($i, $olNPCData->getFoundIDs()))
                    continue;

                $ol = array(
                    'typeStr'   => Util::$typeStrings[TYPE_NPC],
                    'id'        => $i,
                    'name'      => $pair[1] ?: Util::localizedString($olNPCData->getEntry($i), 'name'),
                    'qty'       => $pair[0] > 1 ? $pair[0] : 0,
                    'extraText' => (($_specialFlags & QUEST_FLAG_SPECIAL_SPELLCAST) || $pair[1]) ? '' : ' '.Lang::achievement('slain'),
                    'proxy'     => $pair[2]
                );

                if ($pair[2])                               // has proxies assigned, add yourself as another proxy
                    $ol['proxy'][$i] = Util::localizedString($olNPCData->getEntry($i), 'name');

                $this->objectiveList[] = $ol;
            }
        }

        // .. GO interactions
        if ($ids = array_keys($olGOs))
        {
            $olGOData = new GameObjectList(array(['id', $ids]));
            $this->extendGlobalData($olGOData->getJSGlobals(GLOBALINFO_SELF));

            foreach ($olGOs as $i => $pair)
            {
                if (!$i || !in_array($i, $olGOData->getFoundIDs()))
                    continue;

                $this->objectiveList[] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_OBJECT],
                    'id'        => $i,
                    'name'      => $pair[1] ?: Util::localizedString($olGOData->getEntry($i), 'name'),
                    'qty'       => $pair[0] > 1 ? $pair[0] : 0,
                    'extraText' => ''
                );
            }
        }

        // reputation required
        for ($i = 1; $i < 3; $i++)
        {
            $id  = $this->subject->getField('reqFactionId'.$i);
            $val = $this->subject->getField('reqFactionValue'.$i);
            if (!$id)
                continue;

            $olFactions[$id] = $val;
        }

        if ($ids = array_keys($olFactions))
        {
            $olFactionsData = new FactionList(array(['id', $ids]));
            $this->extendGlobalData($olFactionsData->getJSGlobals(GLOBALINFO_SELF));

            foreach ($olFactions as $i => $val)
            {
                if (!$i || !in_array($i, $olFactionsData->getFoundIDs()))
                    continue;

                $this->objectiveList[] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_FACTION],
                    'id'        => $i,
                    'name'      => Util::localizedString($olFactionsData->getEntry($i), 'name'),
                    'qty'       => sprintf(Util::$dfnString, $val.' '.Lang::achievement('points'), Lang::getReputationLevelForPoints($val)),
                    'extraText' => ''
                );
            }
        }

        // granted spell
        if ($_ = $this->subject->getField('sourceSpellId'))
        {
            $this->extendGlobalIds(TYPE_SPELL, $_);
            $this->objectiveList[] = array(
                'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                'id'        => $_,
                'name'      => SpellList::getName($_),
                'qty'       => 0,
                'extraText' => '&nbsp;('.Lang::quest('provided').')'
            );
        }

        // required money
        if ($this->subject->getField('rewardOrReqMoney') < 0)
            $this->objectiveList[] = ['text' => Lang::quest('reqMoney').Lang::main('colon').Util::formatMoney(abs($this->subject->getField('rewardOrReqMoney')))];

        // required pvp kills
        if ($_ = $this->subject->getField('reqPlayerKills'))
            $this->objectiveList[] = ['text' => Lang::quest('playerSlain').'&nbsp;('.$_.')'];

        /**********/
        /* Mapper */
        /**********/

        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        // gather points of interest
        $mapNPCs = $mapGOs = [];                            // [typeId, start|end|objective, startItemId]

        // todo (med): this double list creation very much sucks ...
        $getItemSource = function ($itemId, $method = 0) use (&$mapNPCs, &$mapGOs)
        {
            $lootTabs  = new Loot();
            if ($lootTabs->getByItem($itemId))
            {
                /*
                    todo (med): sanity check:
                        there are loot templates that are absolute tosh, containing hundrets of random items (e.g. Peacebloom for Quest "The Horde Needs Peacebloom!")
                        even without these .. consider quests like "A Donation of Runecloth" .. oh my .....
                        should we...
                        .. display only a maximum of sources?
                        .. filter sources for low drop chance?

                    for the moment:
                        if an item has >10 sources, only display sources with >80% chance
                        always filter sources with <1% chance
                */

                $nSources = 0;
                foreach ($lootTabs->iterate() as [$type, $data])
                    if ($type == 'creature' || $type == 'object')
                        $nSources += count(array_filter($data['data'], function($val) { return $val['percent'] >= 1.0; }));

                foreach ($lootTabs->iterate() as $idx => [$file, $tabData])
                {
                    if (!$tabData['data'])
                        continue;

                    foreach ($tabData['data'] as $data)
                    {
                        if ($data['percent'] < 1.0)
                            continue;

                        if ($nSources > 10 && $data['percent'] < 80.0)
                            continue;

                        switch ($file)
                        {
                            case 'creature':
                                $mapNPCs[] = [$data['id'], $method, $itemId];
                                break;
                            case 'object':
                                $mapGOs[]  = [$data['id'], $method, $itemId];
                                break;
                            default:
                                break;
                        }
                    }
                }
            }

            // also there's vendors...
            // dear god, if you are one of the types who puts queststarter-items in container-items, in conatiner-items, in container-items, in container-GOs .. you should kill yourself by killing yourself!
            // so yeah .. no recursion checking
            $vendors = DB::World()->selectCol('
                SELECT nv.entry FROM npc_vendor nv WHERE nv.item = ?d UNION
                SELECT c.id FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid WHERE genv.item = ?d',
                $itemId, $itemId
            );
            foreach ($vendors as $v)
                $mapNPCs[] = [$v, $method, $itemId];
        };

        $addObjectiveSpawns = function (array $spawns, callable $processing) use (&$mObjectives)
        {
            foreach ($spawns as $zoneId => $zoneData)
            {
                if (!isset($mObjectives[$zoneId]))
                    $mObjectives[$zoneId] = array(
                        'zone'     => 'Zone #'.$zoneId,
                        'mappable' => 1,
                        'levels'   => []
                    );

                foreach ($zoneData as $floor => $floorData)
                {
                    if (!isset($mObjectives[$zoneId]['levels'][$floor]))
                        $mObjectives[$zoneId]['levels'][$floor] = [];

                    foreach ($floorData as $objId => $objData)
                        $mObjectives[$zoneId]['levels'][$floor][] = $processing($objId, $objData);
                }
            }
       };


        // POI: start + end
        foreach ($startEnd as $se)
        {
            if ($se['type'] == TYPE_NPC)
                $mapNPCs[] = [$se['typeId'], $se['method'], 0];
            else if ($se['type'] == TYPE_OBJECT)
                $mapGOs[]  = [$se['typeId'], $se['method'], 0];
            else if ($se['type'] == TYPE_ITEM)
                $getItemSource($se['typeId'], $se['method']);
        }

        $itemObjectives = [];
        $mObjectives    = [];
        $mZones         = [];
        $objectiveIdx   = 0;

        // POI objectives
        // also map olItems to objectiveIdx so every container gets the same pin color
        foreach ($olItems as $i => [$itemId, $qty, $provided])
        {
            if (!$provided && $itemId)
            {
                $itemObjectives[$itemId] = $objectiveIdx++;
                $getItemSource($itemId);
            }
        }

        // PSA: 'redundant' data is on purpose (e.g. creature required for kill, also dropps item required to collect)

        // external events
        $endTextWrapper = '%s';
        if ($_specialFlags & QUEST_FLAG_SPECIAL_EXT_COMPLETE)
        {
            // areatrigger
            if ($atir = DB::Aowow()->selectCol('SELECT id FROM ?_areatrigger WHERE type = ?d AND quest = ?d', AT_TYPE_OBJECTIVE, $this->typeId))
            {
                if ($atSpawns = DB::AoWoW()->select('SELECT typeId AS ARRAY_KEY, posX, posY, floor, areaId FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a)', TYPE_AREATRIGGER, $atir))
                {
                    foreach ($atSpawns as $atId => $atsp)
                    {
                        $atSpawn = array (
                                'type'      => User::isInGroup(U_GROUP_STAFF) ? TYPE_AREATRIGGER : -1,
                                'id'        => $atId,
                                'point'     => 'requirement',
                                'name'      => $this->subject->parseText('end', false),
                                'coord'     => [$atsp['posX'], $atsp['posY']],
                                'coords'    => [[$atsp['posX'], $atsp['posY']]],
                                'objective' => $objectiveIdx++
                            );

                        if (isset($mObjectives[$atsp['areaId']]['levels'][$atsp['floor']]))
                        {
                            $mObjectives[$atsp['areaId']]['levels'][$atsp['floor']][] = $atSpawn;
                            continue;
                        }

                        $mObjectives[$atsp['areaId']] = array(
                            'zone'     => 'Zone #'.$atsp['areaId'],
                            'mappable' => 1,
                            'levels'   => [$atsp['floor'] => [$atSpawn]]
                        );
                    }
                }
            }
            // complete-spell
            else if ($endSpell = new SpellList(array('OR', ['AND', ['effect1Id', 16], ['effect1MiscValue', $this->typeId]], ['AND', ['effect2Id', 16], ['effect2MiscValue', $this->typeId]], ['AND', ['effect3Id', 16], ['effect3MiscValue', $this->typeId]])))
                if (!$endSpell->error)
                    $endTextWrapper = '<a href="?spell='.$endSpell->id.'">%s</a>';
        }

        // ..adding creature kill requirements
        if ($olNPCData && !$olNPCData->error)
        {
            $spawns = $olNPCData->getSpawns(SPAWNINFO_QUEST);
            $addObjectiveSpawns($spawns, function ($npcId, $npcData) use ($olNPCs, &$objectiveIdx)
            {
                $npcData['point'] = 'requirement';          // always requirement
                foreach ($olNPCs as $proxyNpcId => $npc)
                {
                    if ($npc[1] && $npcId == $proxyNpcId)  // overwrite creature name with quest specific text, if set.
                        $npcData['name'] = $npc[1];

                    if (!empty($npc[2][$npcId]))
                        $npcData['objective'] = $proxyNpcId;
                }

                if (!$npcData['objective'])
                    $npcData['objective'] = $objectiveIdx++;

                return $npcData;
            });
        }

        // ..adding object interaction requirements
        if ($olGOData && !$olGOData->error)
        {
            $spawns = $olGOData->getSpawns(SPAWNINFO_QUEST);
            $addObjectiveSpawns($spawns, function ($goId, $goData) use ($olGOs, &$objectiveIdx)
            {
                foreach ($olGOs as $_goId => $go)
                {
                    if ($go[1] && $goId == $_goId)          // overwrite object name with quest specific text, if set.
                    {
                        $goData['name'] = $go[1];
                        break;
                    }
                }

                $goData['point']     = 'requirement';       // always requirement
                $goData['objective'] = $objectiveIdx++;
                return $goData;
            });
        }

        // .. adding npc from: droping queststart item; dropping item needed to collect; starting quest; ending quest
        if ($mapNPCs)
        {
            $npcs = new CreatureList(array(['id', array_column($mapNPCs, 0)]));
            if (!$npcs->error)
            {
                $startEndDupe = [];                         // if quest starter/ender is the same creature, we need to add it twice
                $spawns       = $npcs->getSpawns(SPAWNINFO_QUEST);
                $addObjectiveSpawns($spawns, function ($npcId, $npcData) use ($mapNPCs, &$startEndDupe, $itemObjectives)
                {
                    foreach ($mapNPCs as $mn)
                    {
                        if ($mn[0] != $npcId)
                            continue;

                        if ($mn[2])                         // source for itemId
                            $npcData['item'] = ItemList::getName($mn[2]);

                        switch ($mn[1])                     // method
                        {
                            case 1:                         // quest start
                                $npcData['point'] = $mn[2] ? 'sourcestart' : 'start';
                                break;
                            case 2:                         // quest end (sourceend doesn't actually make sense .. oh well....)
                                $npcData['point'] = $mn[2] ? 'sourceend' : 'end';
                                break;
                            case 3:                         // quest start & end
                                $npcData['point'] = $mn[2] ? 'sourcestart' : 'start';
                                $startEndDupe = $npcData;
                                $startEndDupe['point'] = $mn[2] ? 'sourceend' : 'end';
                                break;
                            default:                        // just something to kill for quest
                                $npcData['point'] = $mn[2] ? 'sourcerequirement' : 'requirement';
                                if ($mn[2] && !empty($itemObjectives[$mn[2]]))
                                    $npcData['objective'] = $itemObjectives[$mn[2]];
                        }
                    }

                    return $npcData;
                });

                if ($startEndDupe)
                    foreach ($spawns as $zoneId => $zoneData)
                        foreach ($zoneData as $floor => $floorData)
                            foreach ($floorData as $objId => $objData)
                                if ($objId == $startEndDupe['id'])
                                {
                                    $mObjectives[$zoneId]['levels'][$floor][] = $startEndDupe;
                                    break 3;
                                }
            }
        }

        // .. adding go from: containing queststart item; containing item needed to collect; starting quest; ending quest
        if ($mapGOs)
        {
            $gos = new GameObjectList(array(['id', array_column($mapGOs, 0)]));
            if (!$gos->error)
            {
                $startEndDupe = [];                         // if quest starter/ender is the same object, we need to add it twice
                $spawns       = $gos->getSpawns(SPAWNINFO_QUEST);
                $addObjectiveSpawns($spawns, function ($goId, $goData) use ($mapGOs, &$startEndDupe, $itemObjectives)
                {
                    foreach ($mapGOs as $mgo)
                    {
                        if ($mgo[0] != $goId)
                            continue;

                        if ($mgo[2])                        // source for itemId
                            $goData['item'] = ItemList::getName($mgo[2]);

                        switch ($mgo[1])                    // method
                        {
                            case 1:                         // quest start
                                $goData['point'] = $mgo[2] ? 'sourcestart' : 'start';
                                break;
                            case 2:                         // quest end (sourceend doesn't actually make sense .. oh well....)
                                $goData['point'] = $mgo[2] ? 'sourceend' : 'end';
                                break;
                            case 3:                         // quest start & end
                                $goData['point'] = $mgo[2] ? 'sourcestart' : 'start';
                                $startEndDupe = $goData;
                                $startEndDupe['point'] = $mgo[2] ? 'sourceend' : 'end';
                                break;
                            default:                        // just something to kill for quest
                                $goData['point'] = $mgo[2] ? 'sourcerequirement' : 'requirement';
                                if ($mgo[2] && !empty($itemObjectives[$mgo[2]]))
                                    $goData['objective'] = $itemObjectives[$mgo[2]];
                        }
                    }

                    return $goData;
                });

                if ($startEndDupe)
                    foreach ($spawns as $zoneId => $zoneData)
                        foreach ($zoneData as $floor => $floorData)
                            foreach ($floorData as $objId => $objData)
                                if ($objId == $startEndDupe['id'])
                                {
                                    $mObjectives[$zoneId]['levels'][$floor][] = $startEndDupe;
                                    break 3;
                                }
            }
        }

        // ..process zone data
        if ($mObjectives)
        {
            $areas = new ZoneList(array(['id', array_keys($mObjectives)]));
            if (!$areas->error)
            {
                $someIDX = 0;                               // todo (low): UNK value ... map priority, floor, mapId..? values seen: 0 - 3; doesn't seem to affect anything
                foreach ($areas->iterate() as $id => $__)
                {
                    $mObjectives[$id]['zone'] = $areas->getField('name', true);
                    $mZones[] = [$id, ++$someIDX];
                }
            }
        }

        // has start & end?
        $hasStartEnd = 0x0;
        foreach ($mObjectives as $levels)
        {
            foreach ($levels['levels'] as $floor)
            {
                foreach ($floor as $entry)
                {
                    if ($entry['point'] == 'start' || $entry['point'] == 'sourcestart')
                        $hasStartEnd |= 0x1;
                    else if ($entry['point'] == 'end' || $entry['point'] == 'sourceend')
                        $hasStartEnd |= 0x2;
                }
            }
        }

        $this->map = $mObjectives ? array(
            'mapperData' => [],                             // always empty
            'data'       => array(
                'parent'     => 'mapper-generic',
                'objectives' => $mObjectives,
                'zoneparent' => 'mapper-zone-generic',
                'zones'      => $mZones,
                'missing'    => count($mZones) > 1 || $hasStartEnd != 0x3 ? 1 : 0  // 0 if everything happens in one zone, else 1
            )
        ) : null;


        /****************/
        /* Main Content */
        /****************/

        $this->gains         = $this->createGains();
        $this->mail          = $this->createMail($startEnd);
        $this->rewards       = $this->createRewards($_side);
        $this->objectives    = $this->subject->parseText('objectives', false);
        $this->details       = $this->subject->parseText('details', false);
        $this->offerReward   = $this->subject->parseText('offerReward', false);
        $this->requestItems  = $this->subject->parseText('requestItems', false);
        $this->completed     = $this->subject->parseText('completed', false);
        $this->end           = sprintf($endTextWrapper, $this->subject->parseText('end', false));
        $this->suggestedPl   = $this->subject->getField('suggestedPlayers');
        $this->unavailable   = $_flags & QUEST_FLAG_UNAVAILABLE || $this->subject->getField('cuFlags') & CUSTOM_EXCLUDE_FOR_LISTVIEW;
        $this->redButtons    = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => array(
                'linkColor' => 'ffffff00',
                'linkId'    => 'quest:'.$this->typeId.':'.$_level,
                'linkName'  => $this->name,
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_quests WHERE alliance_id = ?d OR horde_id = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altQuest = new QuestList(array(['id', abs($pendant)]));
            if (!$altQuest->error)
            {
                $this->transfer = sprintf(
                    Lang::quest('_transfer'),
                    $altQuest->id,
                    $altQuest->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', 1) : Lang::game('si', 2)
                );
            }
        }

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: see also
        $seeAlso = new QuestList(array(['name_loc'.User::$localeId, '%'.$this->name.'%'], ['id', $this->typeId, '!']));
        if (!$seeAlso->error)
        {
            $this->extendGlobalData($seeAlso->getJSGlobals());
            $this->lvTabs[] = ['quest', array(
                'data' => array_values($seeAlso->getListviewData()),
                'name' => '$LANG.tab_seealso',
                'id'   => 'see-also'
            )];
        }

        // tab: criteria of
        $criteriaOf = new AchievementList(array(['ac.type', ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST], ['ac.value1', $this->typeId]));
        if (!$criteriaOf->error)
        {
            $this->extendGlobalData($criteriaOf->getJSGlobals());
            $this->lvTabs[] = ['achievement', array(
                'data' => array_values($criteriaOf->getListviewData()),
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            )];
        }

        // tab: spawning pool (for the swarm)
        if ($qp = DB::World()->selectCol('SELECT qpm2.questId FROM quest_pool_members qpm1 JOIN quest_pool_members qpm2 ON qpm1.poolId = qpm2.poolId WHERE qpm1.questId = ?d', $this->typeId))
        {
            $max = DB::World()->selectCell('SELECT numActive FROM quest_pool_template qpt JOIN quest_pool_members qpm ON qpm.poolId = qpt.poolId WHERE qpm.questId = ?d', $this->typeId);
            $pooledQuests = new QuestList(array(['id', $qp]));
            if (!$pooledQuests->error)
            {
                $this->extendGlobalData($pooledQuests->getJSGlobals());
                $this->lvTabs[] = ['quest', array(
                    'data' => array_values($pooledQuests->getListviewData()),
                    'name' => 'Quest Pool',
                    'id'   => 'quest-pool',
                    'note' => Lang::quest('questPoolDesc', [$max])
                )];
            }
        }

        // tab: conditions
        $cnd = [];
        if ($_ = $this->subject->getField('reqMinRepFaction'))
        {
            $cnd[CND_SRC_QUEST_ACCEPT][$this->typeId][0][] = [CND_REPUTATION_RANK, $_, 1 << Game::getReputationLevelForPoints($this->subject->getField('reqMinRepValue'))];
            $this->extendGlobalIds(TYPE_FACTION, $_);
        }

        if ($_ = $this->subject->getField('reqMaxRepFaction'))
        {
            $cnd[CND_SRC_QUEST_ACCEPT][$this->typeId][0][] = [-CND_REPUTATION_RANK, $_, 1 << Game::getReputationLevelForPoints($this->subject->getField('reqMaxRepValue'))];
            $this->extendGlobalIds(TYPE_FACTION, $_);
        }

        $_ = Util::getServerConditions([CND_SRC_QUEST_ACCEPT, CND_SRC_QUEST_SHOW_MARK], null, $this->typeId);
        if (!empty($_[0]))
        {
            // awkward merger
            if (isset($_[0][CND_SRC_QUEST_ACCEPT][$this->typeId][0]))
            {
                if (isset($cnd[CND_SRC_QUEST_ACCEPT][$this->typeId][0]))
                    $cnd[CND_SRC_QUEST_ACCEPT][$this->typeId][0] = array_merge($cnd[CND_SRC_QUEST_ACCEPT][$this->typeId][0], $_[0][CND_SRC_QUEST_ACCEPT][$this->typeId][0]);
                else
                    $cnd[CND_SRC_QUEST_ACCEPT] = $_[0][CND_SRC_QUEST_ACCEPT];
            }

            if (isset($_[0][CND_SRC_QUEST_SHOW_MARK]))
                $cnd[CND_SRC_QUEST_SHOW_MARK] = $_[0][CND_SRC_QUEST_SHOW_MARK];

            $this->extendGlobalData($_[1]);
        }

        if ($cnd)
        {
            $tab = "<script type=\"text/javascript\">\n" .
                   "var markup = ConditionList.createTab(".Util::toJSON($cnd).");\n" .
                   "Markup.printHtml(markup, 'tab-conditions', { allow: Markup.CLASS_STAFF })" .
                   "</script>";

            $this->lvTabs[] = [null, array(
                'data'   => $tab,
                'id'   => 'conditions',
                'name' => '$LANG.requires'
            )];
        }
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString}    = $this->subject->getField('name', true);
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
            if ($this->subject->isDaily())
                $power->daily = 1;
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    private function createRewards($side)
    {
        $rewards = [];

        // moneyReward / maxLevelCompensation
        $comp       = $this->subject->getField('rewardMoneyMaxLevel');
        $questMoney = $this->subject->getField('rewardOrReqMoney');
        $realComp   = max($comp, $questMoney);
        if ($questMoney > 0)
        {
            $rewards['money'] = Util::formatMoney($questMoney);
            if ($realComp > $questMoney)
                $rewards['money'] .= '&nbsp;' . sprintf(Lang::quest('expConvert'), Util::formatMoney($realComp), MAX_LEVEL);
        }
        else if ($questMoney <= 0 && $realComp > 0)
            $rewards['money'] = sprintf(Lang::quest('expConvert2'), Util::formatMoney($realComp), MAX_LEVEL);

        // itemChoices
        if (!empty($this->subject->choices[$this->typeId][TYPE_ITEM]))
        {
            $c           = $this->subject->choices[$this->typeId][TYPE_ITEM];
            $choiceItems = new ItemList(array(['id', array_keys($c)]));
            if (!$choiceItems->error)
            {
                $this->extendGlobalData($choiceItems->getJSGlobals());
                foreach ($choiceItems->Iterate() as $id => $__)
                {
                    $rewards['choice'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                        'id'        => $id,
                        'name'      => $choiceItems->getField('name', true),
                        'quality'   => $choiceItems->getField('quality'),
                        'qty'       => $c[$id],
                        'globalStr' => 'g_items'
                    );
                }
            }
        }

        // itemRewards
        if (!empty($this->subject->rewards[$this->typeId][TYPE_ITEM]))
        {
            $ri       = $this->subject->rewards[$this->typeId][TYPE_ITEM];
            $rewItems = new ItemList(array(['id', array_keys($ri)]));
            if (!$rewItems->error)
            {
                $this->extendGlobalData($rewItems->getJSGlobals());
                foreach ($rewItems->Iterate() as $id => $__)
                {
                    $rewards['items'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                        'id'        => $id,
                        'name'      => $rewItems->getField('name', true),
                        'quality'   => $rewItems->getField('quality'),
                        'qty'       => $ri[$id],
                        'globalStr' => 'g_items'
                    );
                }
            }
        }

        if (!empty($this->subject->rewards[$this->typeId][TYPE_CURRENCY]))
        {
            $rc      = $this->subject->rewards[$this->typeId][TYPE_CURRENCY];
            $rewCurr = new CurrencyList(array(['id', array_keys($rc)]));
            if (!$rewCurr->error)
            {
                $this->extendGlobalData($rewCurr->getJSGlobals());
                foreach ($rewCurr->Iterate() as $id => $__)
                {
                    $rewards['items'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_CURRENCY],
                        'id'        => $id,
                        'name'      => $rewCurr->getField('name', true),
                        'quality'   => 1,
                        'qty'       => $rc[$id] * ($side == 2 ? -1 : 1), // toggles the icon
                        'globalStr' => 'g_gatheredcurrencies'
                    );
                }
            }
        }

        // spellRewards
        $displ = $this->subject->getField('rewardSpell');
        $cast  = $this->subject->getField('rewardSpellCast');
        if ($cast <= 0 && $displ > 0)
        {
            $cast  = $displ;
            $displ = 0;
        }

        if ($cast > 0 || $displ > 0)
        {
            $rewSpells = new SpellList(array(['id', [$displ, $cast]]));
            $this->extendGlobalData($rewSpells->getJSGlobals());

            if (User::isInGroup(U_GROUP_EMPLOYEE))          // accurately display, what spell is what
            {
                $extra = null;
                if ($_ = $rewSpells->getEntry($displ))
                    $extra = sprintf(Lang::quest('spellDisplayed'), $displ, Util::localizedString($_, 'name'));

                if ($_ = $rewSpells->getEntry($cast))
                {
                    $rewards['spells']['extra']  = $extra;
                    $rewards['spells']['cast'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                        'id'        => $cast,
                        'name'      => Util::localizedString($_, 'name'),
                        'globalStr' => 'g_spells'
                    );
                }
            }
            else                                            // if it has effect:learnSpell display the taught spell instead
            {
                $teach = [];
                foreach ($rewSpells->iterate() as $id => $__)
                    if ($_ = $rewSpells->canTeachSpell())
                        foreach ($_ as $idx)
                            $teach[$rewSpells->getField('effect'.$idx.'TriggerSpell')] = $id;

                if ($_ = $rewSpells->getEntry($displ))
                {
                    $rewards['spells']['extra'] = null;
                    $rewards['spells'][$teach ? 'learn' : 'cast'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                        'id'        => $displ,
                        'name'      => Util::localizedString($_, 'name'),
                        'globalStr' => 'g_spells'
                    );
                }
                else if (($_ = $rewSpells->getEntry($cast)) && !$teach)
                {
                    $rewards['spells']['extra']  = null;
                    $rewards['spells']['cast'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                        'id'        => $cast,
                        'name'      => Util::localizedString($_, 'name'),
                        'globalStr' => 'g_spells'
                    );
                }
                else
                {
                    $taught = new SpellList(array(['id', array_keys($teach)]));
                    if (!$taught->error)
                    {
                        $this->extendGlobalData($taught->getJSGlobals());
                        $rewards['spells']['extra']  = null;
                        foreach ($taught->iterate() as $id => $__)
                        {
                            $rewards['spells']['learn'][] = array(
                                'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                                'id'        => $id,
                                'name'      => $taught->getField('name', true),
                                'globalStr' => 'g_spells'
                            );
                        }
                    }
                }
            }
        }

        return $rewards;
    }

    private function createMail($startEnd)
    {
        $mail = [];

        if ($rmtId = $this->subject->getField('rewardMailTemplateId'))
        {
            $delay  = $this->subject->getField('rewardMailDelay');
            $letter = DB::Aowow()->selectRow('SELECT * FROM ?_mails WHERE id = ?d', $rmtId);

            $mail = array(
                'id'          => $rmtId,
                'delay'       => $delay  ? sprintf(Lang::mail('mailIn'), Util::formatTime($delay * 1000)) : null,
                'sender'      => null,
                'attachments' => [],
                'text'        => $letter ? Util::parseHtmlText(Util::localizedString($letter, 'text'))     : null,
                'subject'     => Util::parseHtmlText(Util::localizedString($letter, 'subject'))
            );

            $senderTypeId = 0;
            if ($_= DB::World()->selectCell('SELECT RewardMailSenderEntry FROM quest_mail_sender WHERE QuestId = ?d', $this->typeId))
                $senderTypeId = $_;
            else
                foreach ($startEnd as $se)
                    if (($se['method'] & 0x2) && $se['type'] == TYPE_NPC)
                        $senderTypeId = $se['typeId'];

            if ($ti = CreatureList::getName($senderTypeId))
                $mail['sender'] = sprintf(Lang::mail('mailBy'), $senderTypeId, $ti);

            // while mail attachemnts are handled as loot, it has no variance. Always 100% chance, always one item.
            $mailLoot = new Loot();
            if ($mailLoot->getByContainer(LOOT_MAIL, $rmtId))
            {
                $this->extendGlobalData($mailLoot->jsGlobals);
                foreach ($mailLoot->getResult() as $loot)
                {
                    $mail['attachments'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                        'id'        => $loot['id'],
                        'name'      => substr($loot['name'], 1),
                        'quality'   => 7 - $loot['name'][0],
                        'qty'       => $loot['stack'][0],
                        'globalStr' => 'g_items'
                    );
                }
            }
        }

        return $mail;
    }

    private function createGains()
    {
        $gains = [];

        // xp
        if ($_ = $this->subject->getField('rewardXP'))
            $gains['xp'] = $_;

        // talent points
        if ($_ = $this->subject->getField('rewardTalents'))
            $gains['tp'] = $_;

        // reputation
        for ($i = 1; $i < 6; $i++)
        {
            $fac = $this->subject->getField('rewardFactionId'.$i);
            $qty = $this->subject->getField('rewardFactionValue'.$i);
            if (!$fac || !$qty)
                continue;

            $rep = array(
                'qty'  => [$qty, 0],
                'id'   => $fac,
                'name' => FactionList::getName($fac)
            );

            if ($cuRates = DB::World()->selectRow('SELECT * FROM reputation_reward_rate WHERE faction = ?d', $fac))
            {
                if ($dailyType = $this->subject->isDaily())
                {
                    if ($dailyType == 1 && $cuRates['quest_daily_rate'] != 1.0)
                        $rep['qty'][1] = $rep['qty'][0] * ($cuRates['quest_daily_rate'] - 1);
                    else if ($dailyType == 2 && $cuRates['quest_weekly_rate'] != 1.0)
                        $rep['qty'][1] = $rep['qty'][0] * ($cuRates['quest_weekly_rate'] - 1);
                    else if ($dailyType == 3 && $cuRates['quest_monthly_rate'] != 1.0)
                        $rep['qty'][1] = $rep['qty'][0] * ($cuRates['quest_monthly_rate'] - 1);
                }
                else if ($this->subject->isRepeatable() && $cuRates['quest_repeatable_rate'] != 1.0)
                    $rep['qty'][1] = $rep['qty'][0] * ($cuRates['quest_repeatable_rate'] - 1);
                else if ($cuRates['quest_rate'] != 1.0)
                    $rep['qty'][1] = $rep['qty'][0] * ($cuRates['quest_rate'] - 1);
            }

            $gains['rep'][] = $rep;
        }

        // title
        if ($_ = (new TitleList(array(['id', $this->subject->getField('rewardTitleId')])))->getHtmlizedName())
            $gains['title'] = $_;

        return $gains;
    }
}

?>
