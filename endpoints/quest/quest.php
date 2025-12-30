<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'quest';
    protected  string $pageName   = 'quest';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 3];

    protected  array  $scripts    = [[SC_JS_FILE, 'js/ShowOnMap.js']];

    public  int         $type          = Type::QUEST;
    public  int         $typeId        = 0;
    public  array       $objectiveList = [];
    public ?IconElement $providedItem  = null;
    public  array       $mail          = [];
    public ?array       $gains         = null;              // why array|null ? because destructuring an array with less elements than expected is an error, destructuring null just returns false
    public ?array       $rewards       = null;              // so  " if ([$spells, $items, $choice, $money] = $this->rewards): " will either work or cleanly branch to else
    public  string      $objectives    = '';
    public  string      $details       = '';
    public  string      $offerReward   = '';
    public  string      $requestItems  = '';
    public  string      $completed     = '';
    public  string      $end           = '';
    public  int         $suggestedPl   = 1;
    public  bool        $unavailable   = false;

    private QuestList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new QuestList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('quest'), Lang::quest('notFound'));

        $this->h1 = Lang::unescapeUISequences(Util::htmlEscape($this->subject->getField('name', true)), Lang::FMT_HTML);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_HTML)
        );

        $_level        = $this->subject->getField('level');
        $_minLevel     = $this->subject->getField('minLevel');
        $_flags        = $this->subject->getField('flags');
        $_specialFlags = $this->subject->getField('specialFlags');
        $_side         = ChrRace::sideFromMask($this->subject->getField('reqRaceMask'));
        $hasCompletion = !($_flags & QUEST_FLAG_UNAVAILABLE || $this->subject->getField('cuFlags') & CUSTOM_EXCLUDE_FOR_LISTVIEW);


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('cat2');
        if ($cat = $this->subject->getField('cat1'))
        {
            foreach (Game::$questSubCats as $parent => $children)
                if (in_array($cat, $children))
                    $this->breadcrumb[] = $parent;

            $this->breadcrumb[] = $cat;
        }


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_RAW), Util::ucFirst(Lang::game('quest')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // event (todo: assign eventData)
        if ($_ = $this->subject->getField('eventId'))
        {
            $this->extendGlobalIds(Type::WORLDEVENT, $_);
            $infobox[] = Lang::game('eventShort', ['[event='.$_.']']);
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

            $infobox[] = Lang::game('reqLevel', [$lvl]);
        }

        // loremaster (i dearly hope those flags cover every case...)
        if ($this->subject->getField('questSortIdBak') > 0 && !$this->subject->isRepeatable())
        {
            $conditions = array(
                ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE],
                ['ac.value1', $this->subject->getField('questSortIdBak')],
                ['a.faction', $_side, '&']
            );
            $loremaster = new AchievementList($conditions);
            $this->extendGlobalData($loremaster->getJSGlobals(GLOBALINFO_SELF));

            switch (count($loremaster->getFoundIds()))
            {
                case 0:
                    break;
                case 1:
                    $infobox[] = Lang::quest('loremaster').'[achievement='.$loremaster->id.']';
                    break;
                default:
                    $lm = Lang::quest('loremaster').'[ul]';
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

        if ($t = $this->subject->getField('questInfoId'))
            $_[] = Lang::quest('questInfo', $t);

        if ($_)
            $infobox[] = Lang::game('type').implode(' ', $_);

        // side
        $infobox[] = Lang::main('side') . match ($this->subject->getField('faction'))
        {
            SIDE_ALLIANCE => '[span class=icon-alliance]'.Lang::game('si', SIDE_ALLIANCE).'[/span]',
            SIDE_HORDE    => '[span class=icon-horde]'.Lang::game('si', SIDE_HORDE).'[/span]',
            default       => Lang::game('si', SIDE_BOTH)    // 0, 3
        };

        // races
        $jsg = [];
        if ($_ = Lang::getRaceString($this->subject->getField('reqRaceMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_RACE, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('race') : Lang::game('races');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // classes
        $jsg = [];
        if ($_ = Lang::getClassString($this->subject->getField('reqClassMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_CLASS, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // profession / skill
        if ($_ = $this->subject->getField('reqSkillId'))
        {
            $this->extendGlobalIds(Type::SKILL, $_);
            $sk =  '[skill='.$_.']';
            if ($_ = $this->subject->getField('reqSkillPoints'))
                $sk .= ' ('.$_.')';

            $infobox[] = Lang::quest('profession').$sk;
        }

        // timer
        if ($_ = $this->subject->getField('timeLimit'))
            $infobox[] = Lang::quest('timer').DateTime::formatTimeElapsedFloat($_ * 1000);

        $startEnd = DB::Aowow()->select('SELECT * FROM ?_quests_startend WHERE `questId` = ?d', $this->typeId);

        // start
        $start = '[icon name=quest_start'.($this->subject->isRepeatable() ? '_daily' : '').']'.Lang::event('start').'[/icon]';
        $s     = [];
        foreach ($startEnd as $se)
        {
            if ($se['method'] & 0x1)
            {
                $this->extendGlobalIds($se['type'], $se['typeId']);
                $s[] = ($s ? '[span=invisible]'.$start.'[/span] ' : $start.' ') .'['.Type::getFileString($se['type']).'='.$se['typeId'].']';
            }
        }

        if ($s)
            $infobox[] = implode('[br]', $s);

        // end
        $end = '[icon name=quest_end'.($this->subject->isRepeatable() ? '_daily' : '').']'.Lang::event('end').'[/icon]';
        $e   = [];
        foreach ($startEnd as $se)
        {
            if ($se['method'] & 0x2)
            {
                $this->extendGlobalIds($se['type'], $se['typeId']);
                $e[] = ($e ? '[span=invisible]'.$end.'[/span] ' : $end.' ') . '['.Type::getFileString($se['type']).'='.$se['typeId'].']';
            }
        }

        if ($e)
            $infobox[] = implode('[br]', $e);

        // auto accept
        if ($_flags & QUEST_FLAG_AUTO_ACCEPT)
            $infobox[] = Lang::quest('autoaccept');

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
                $infobox[] = Lang::game('difficulty').implode('[small] &nbsp;[/small]', $_);
        }

        // id
        $infobox[] = Lang::quest('id') . $this->typeId;

        // profiler relateed (note that this is part of the cache. I don't think this is important enough to calc for every view)
        if (Cfg::get('PROFILER_ENABLE') && $hasCompletion)
        {
            $x = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_completion_quests WHERE `questId` = ?d', $this->typeId);
            $y = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_profiles WHERE `custom` = 0 AND `stub` = 0');
            $infobox[] = Lang::profiler('attainedBy', [round(($x ?: 0) * 100 / ($y ?: 1))]);

            // completion row added by InfoboxMarkup
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', $hasCompletion);


        /*******************/
        /* Objectives List */
        /*******************/

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

        if ($ids = array_filter(array_column($olItems, 0)))
        {
            $olItemData = new ItemList(array(['id', $ids]));
            $this->extendGlobalData($olItemData->getJSGlobals(GLOBALINFO_SELF));

            $providedRequired = false;
            foreach ($olItems as $i => [$itemId, $qty, $provided])
            {
                if (!$i || !$itemId)
                    continue;

                if ($provided)
                    $providedRequired = true;

                if (!$olItemData->getEntry($itemId))
                {
                    $this->objectiveList[] = [0, new IconElement(0, 0, Util::ucFirst(Lang::game('item')).' #'.$itemId, $qty > 1 ? $qty : '', size: IconElement::SIZE_SMALL, extraText: $provided ? Lang::quest('provided') : null)];
                    continue;
                }

                $this->objectiveList[] = [0, new IconElement(
                    Type::ITEM,
                    $itemId,
                    Lang::unescapeUISequences($olItemData->json[$itemId]['name'], Lang::FMT_HTML),
                    num: $qty > 1 ? $qty : '',
                    quality: 7 - $olItemData->json[$itemId]['quality'],
                    size: IconElement::SIZE_SMALL,
                    element: 'iconlist-icon',
                    extraText: $provided ? Lang::quest('provided') : null
                )];
            }

            // if providd item is not required by quest, list it below other requirements
            if (!$providedRequired && $olItems[0][0])
            {
                if (!$olItemData->getEntry($olItems[0][0]))
                    $this->providedItem = new IconElement(0, 0, Util::ucFirst(Lang::game('item')).' #'.$itemId, $olItems[0][1] > 1 ? $olItems[0][1] : '');
                else
                    $this->providedItem = new IconElement(
                        Type::ITEM,
                        $olItems[0][0],
                        Lang::unescapeUISequences($olItemData->json[$olItems[0][0]]['name'], Lang::FMT_HTML),
                        num: $olItems[0][1] > 1 ? $olItems[0][1] : '',
                        quality: 7 - $olItemData->json[$olItems[0][0]]['quality'],
                        size: IconElement::SIZE_SMALL,
                        element: 'iconlist-icon'
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

            foreach ($olNPCs as $i => [$qty, $altText, $proxies])
            {
                if (!$i)
                    continue;

                if ($proxies)                               // has proxies assigned, add yourself as another proxy
                {
                    $proxies[$i] = Util::localizedString($olNPCData->getEntry($i), 'name');

                    // split in two blocks for display
                    $proxies = array(
                        array_slice($proxies, 0,    ceil(count($proxies) / 2), true),
                        array_slice($proxies, ceil(count($proxies) / 2), null, true)
                    );

                    $this->objectiveList[] = [2, array(
                        'id'    => $i,
                        'text'  => ($altText ?: Util::localizedString($olNPCData->getEntry($i), 'name')) . ((($_specialFlags & QUEST_FLAG_SPECIAL_SPELLCAST) || $altText) ? '' : ' '.Lang::achievement('slain')),
                        'qty'   => $qty > 1 ? $qty : 0,
                        'proxy' => array_filter($proxies)
                    )];
                }
                else if (!$olNPCData->getEntry($i))
                    $this->objectiveList[] = [0, new IconElement(0, 0, Util::ucFirst(Lang::game('npc')).' #'.$i, $qty > 1 ? $qty : '')];
                else
                    $this->objectiveList[] = [0, new IconElement(
                        Type::NPC,
                        $i,
                        $altText ?: Util::localizedString($olNPCData->getEntry($i), 'name'),
                        $qty > 1 ? $qty : '',
                        size: IconElement::SIZE_SMALL,
                        element: 'iconlist-icon',
                        extraText: (($_specialFlags & QUEST_FLAG_SPECIAL_SPELLCAST) || $altText) ? '' : Lang::achievement('slain'),
                    )];
            }
        }

        // .. GO interactions
        if ($ids = array_keys($olGOs))
        {
            $olGOData = new GameObjectList(array(['id', $ids]));
            $this->extendGlobalData($olGOData->getJSGlobals(GLOBALINFO_SELF));

            foreach ($olGOs as $i => [$qty, $altText])
            {
                if (!$i)
                    continue;

                if (!$olGOData->getEntry($i))
                    $this->objectiveList[] = [0, new IconElement(0, 0, Util::ucFirst(Lang::game('object')).' #'.$i, $qty > 1 ? $qty : '', size: IconElement::SIZE_SMALL)];
                else
                    $this->objectiveList[] = [0, new IconElement(
                        Type::OBJECT,
                        $i,
                        $altText ?: Lang::unescapeUISequences(Util::localizedString($olGOData->getEntry($i), 'name'), Lang::FMT_HTML),
                        $qty > 1 ? $qty : '',
                        size: IconElement::SIZE_SMALL,
                        element: 'iconlist-icon',
                    )];
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

                $this->objectiveList[] = [0, new IconElement(
                    Type::FACTION,
                    $i,
                    Util::localizedString($olFactionsData->getEntry($i), 'name'),
                    size: IconElement::SIZE_SMALL,
                    element: 'iconlist-icon',
                    extraText: sprintf(Util::$dfnString, $val.' '.Lang::achievement('points'), '('.Lang::getReputationLevelForPoints($val).')')
                )];
            }
        }

        // granted spell
        if ($_ = $this->subject->getField('sourceSpellId'))
        {
            $this->extendGlobalIds(Type::SPELL, $_);
            $this->objectiveList[] = [0, new IconElement(Type::SPELL, $_, SpellList::getName($_), extraText: Lang::quest('provided'), element: 'iconlist-icon', size: IconElement::SIZE_SMALL)];
        }

        // required money
        if ($this->subject->getField('rewardOrReqMoney') < 0)
            $this->objectiveList[] = [1, Lang::quest('reqMoney', [Util::formatMoney(abs($this->subject->getField('rewardOrReqMoney')))])];

        // required pvp kills
        if ($_ = $this->subject->getField('reqPlayerKills'))
            $this->objectiveList[] = [1, Lang::quest('playerSlain', [$_])];


        /**********/
        /* Mapper */
        /**********/

        // gather points of interest
        $mapNPCs = $mapGOs = [];                            // [typeId, start|end|objective, startItemId]

        // todo (med): this double list creation very much sucks ...
        $getItemSource = function ($itemId, $method = 0) use (&$mapNPCs, &$mapGOs)
        {
            $lootTabs  = new LootByItem($itemId);
            if ($lootTabs->getByItem())
            {
                /*
                    todo (med): sanity check:
                        there are loot templates that are absolute tosh, containing hundreds of random items (e.g. Peacebloom for Quest "The Horde Needs Peacebloom!")
                        even without these .. consider quests like "A Donation of Runecloth" .. oh my .....
                        should we...
                        .. display only a maximum of sources?
                        .. filter sources for low drop chance?

                    for the moment:
                        if an item has >10 sources, only display sources with >80% chance
                        always filter sources with <5% chance
                */

                $nSources = 0;
                foreach ($lootTabs->iterate() as [$type, $data])
                    if ($type == 'creature' || $type == 'object')
                        $nSources += count(array_filter($data['data'], fn($x) => $x['percent'] >= 5.0));

                foreach ($lootTabs->iterate() as [$file, $tabData])
                {
                    if (!$tabData['data'])
                        continue;

                    foreach ($tabData['data'] as $data)
                    {
                        if ($data['percent'] < 5.0)
                            continue;

                        if ($nSources > 10 && $data['percent'] < 80.0)
                            continue;

                        switch ($file)
                        {
                            case 'npc':
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
            $vendors = DB::World()->selectCol(
               'SELECT  nv.`entry` FROM npc_vendor nv                                                               WHERE   nv.`item` = ?d UNION
                SELECT nv1.`entry` FROM npc_vendor nv1             JOIN npc_vendor nv2 ON -nv1.`item` = nv2.`entry` WHERE  nv2.`item` = ?d UNION
                SELECT   c.`id`    FROM game_event_npc_vendor genv JOIN creature   c   ON c.`guid` = genv.`guid`    WHERE genv.`item` = ?d',
                $itemId, $itemId, $itemId
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
            if ($se['type'] == Type::NPC)
                $mapNPCs[] = [$se['typeId'], $se['method'], 0];
            else if ($se['type'] == Type::OBJECT)
                $mapGOs[]  = [$se['typeId'], $se['method'], 0];
            else if ($se['type'] == Type::ITEM)
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
            if ($atir = DB::Aowow()->selectCol('SELECT `id` FROM ?_areatrigger WHERE `type` = ?d AND `quest` = ?d', AT_TYPE_OBJECTIVE, $this->typeId))
            {
                if ($atSpawns = DB::AoWoW()->select('SELECT `typeId` AS ARRAY_KEY, `posX`, `posY`, `floor`, `areaId` FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a)', Type::AREATRIGGER, $atir))
                {
                    if (User::isInGroup(U_GROUP_STAFF))
                        $endTextWrapper = '<a href="?areatrigger='.$atir[0].'">%s</a>';

                    foreach ($atSpawns as $atId => $atsp)
                    {
                        $atSpawn = array (
                                'type'      => User::isInGroup(U_GROUP_STAFF) ? Type::AREATRIGGER : -1,
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
            else if ($endSpell = new SpellList(array('OR', ['AND', ['effect1Id', SPELL_EFFECT_QUEST_COMPLETE], ['effect1MiscValue', $this->typeId]], ['AND', ['effect2Id', SPELL_EFFECT_QUEST_COMPLETE], ['effect2MiscValue', $this->typeId]], ['AND', ['effect3Id', SPELL_EFFECT_QUEST_COMPLETE], ['effect3MiscValue', $this->typeId]])))
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
            // sort zones by amount of mapper points most -> least
            $zoneOrder = [];
            foreach ($mObjectives as $zoneId => $data)
                $zoneOrder[$zoneId] = array_reduce($data['levels'], function($carry, $spawns) { foreach ($spawns as $s) { $carry += count($s['coords']); } return $carry; });

            arsort($zoneOrder);
            $zoneOrder = array_flip(array_keys($zoneOrder));

            $areas = new ZoneList(array(['id', array_keys($mObjectives)]));
            if (!$areas->error)
            {
                foreach ($areas->iterate() as $id => $__)
                {
                    // [zoneId, selectionPriority] - determines which map link is preselected. (highest index)
                    $mZones[$zoneOrder[$id]]  = [$id, count($zoneOrder) - $zoneOrder[$id]];
                    $mObjectives[$id]['zone'] = $areas->getField('name', true);
                }
            }

            ksort($mZones);
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

        if ($mObjectives)
        {
            $this->addDataLoader('zones');
            $this->map = array(
                array(                                      // Mapper
                    'parent'     => 'mapper-generic',
                    'objectives' => $mObjectives,
                    'zoneparent' => 'mapper-zone-generic',
                    'zones'      => $mZones,
                    'missing'    => count($mZones) > 1 || $hasStartEnd != 0x3 ? 1 : 0  // 0 if everything happens in one zone, else 1
                ),
                new \StdClass(),                            // mapperData
                null,                                       // ShowOnMap
                null                                        // foundIn
            );
        }


        /****************/
        /* Main Content */
        /****************/

        $this->series        = $this->createSeries($_side);
        $this->gains         = $this->createGains();
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
                'linkName'  => Util::jsEscape($this->subject->getField('name', true)),
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        if ($this->createMail($startEnd))
            $this->addScript([SC_CSS_FILE, 'css/Book.css']);

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = ?d, `alliance_id`, -`horde_id`) FROM player_factionchange_quests WHERE `alliance_id` = ?d OR `horde_id` = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altQuest = new QuestList(array(['id', abs($pendant)]));
            if (!$altQuest->error)
            {
                $this->transfer = Lang::quest('_transfer', array(
                    $altQuest->id,
                    $altQuest->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ));
            }
        }


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: see also
        $seeAlso = new QuestList(array(['name_loc'.Lang::getLocale()->value, '%'.Util::htmlEscape($this->subject->getField('name', true)).'%'], ['id', $this->typeId, '!']));
        if (!$seeAlso->error)
        {
            $this->extendGlobalData($seeAlso->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $seeAlso->getListviewData(),
                'name' => '$LANG.tab_seealso',
                'id'   => 'see-also'
            ), QuestList::$brickFile));
        }

        // tab: criteria of
        $criteriaOf = new AchievementList(array(['ac.type', ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST], ['ac.value1', $this->typeId]));
        if (!$criteriaOf->error)
        {
            $this->extendGlobalData($criteriaOf->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $criteriaOf->getListviewData(),
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            ), AchievementList::$brickFile));
        }

        // tab: spawning pool (for the swarm)
        if ($qp = DB::World()->selectCol('SELECT qpm2.`questId` FROM quest_pool_members qpm1 JOIN quest_pool_members qpm2 ON qpm1.`poolId` = qpm2.`poolId` WHERE qpm1.`questId` = ?d', $this->typeId))
        {
            $max = DB::World()->selectCell('SELECT `numActive` FROM quest_pool_template qpt JOIN quest_pool_members qpm ON qpm.`poolId` = qpt.`poolId` WHERE qpm.`questId` = ?d', $this->typeId);
            $pooledQuests = new QuestList(array(['id', $qp]));
            if (!$pooledQuests->error)
            {
                $this->extendGlobalData($pooledQuests->getJSGlobals());
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $pooledQuests->getListviewData(),
                    'name' => 'Quest Pool',
                    'id'   => 'quest-pool',
                    'note' => Lang::quest('questPoolDesc', [$max])
                ), QuestList::$brickFile));
            }
        }

        // tab: conditions
        $cnd = new Conditions();
        $cnd->getBySource([Conditions::SRC_QUEST_AVAILABLE, Conditions::SRC_QUEST_SHOW_MARK], entry: $this->typeId)
            ->getByCondition(Type::QUEST, $this->typeId)
            ->prepare();

        if ($_ = $this->subject->getField('reqMinRepFaction'))
            $cnd->addExternalCondition(Conditions::SRC_QUEST_AVAILABLE, '0:'.$this->typeId, [Conditions::REPUTATION_RANK, $_, 1 << Game::getReputationLevelForPoints($this->subject->getField('reqMinRepValue'))]);

        if ($_ = $this->subject->getField('reqMaxRepFaction'))
            $cnd->addExternalCondition(Conditions::SRC_QUEST_AVAILABLE, '0:'.$this->typeId, [-Conditions::REPUTATION_RANK, $_, 1 << Game::getReputationLevelForPoints($this->subject->getField('reqMaxRepValue'))]);

        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }

    private function createRewards(int $side) : ?array
    {
        $rewards = [[], [], [], ''];                        // [spells, items, choice, money]

        // moneyReward / maxLevelCompensation
        $comp       = $this->subject->getField('rewardMoneyMaxLevel');
        $questMoney = $this->subject->getField('rewardOrReqMoney');
        $realComp   = max($comp, $questMoney);
        if ($questMoney > 0)
        {
            $rewards[3] = Util::formatMoney($questMoney);
            if ($realComp > $questMoney)
                $rewards[3] .= '&nbsp;' . Lang::quest('expConvert', [Util::formatMoney($realComp), MAX_LEVEL]);
        }
        else if ($questMoney <= 0 && $realComp > 0)
            $rewards[3] = Lang::quest('expConvert2', [Util::formatMoney($realComp), MAX_LEVEL]);

        // itemChoices
        if (!empty($this->subject->choices[$this->typeId][Type::ITEM]))
        {
            $choices     = $this->subject->choices[$this->typeId][Type::ITEM];
            $choiceItems = new ItemList(array(['id', array_keys($choices)]));
            if (!$choiceItems->error)
            {
                $this->extendGlobalData($choiceItems->getJSGlobals());
                foreach ($choices as $id => $num)           // itr over $choices to preserve display order
                    if ($choiceItems->getEntry($id))
                        $rewards[2][] = new IconElement(
                            Type::ITEM,
                            $id,
                            Lang::unescapeUISequences($choiceItems->getField('name', true), Lang::FMT_HTML),
                            quality: $choiceItems->getField('quality'),
                            num: $num
                        );
            }
        }

        // itemRewards
        if (!empty($this->subject->rewards[$this->typeId][Type::ITEM]))
        {
            $reward   = $this->subject->rewards[$this->typeId][Type::ITEM];
            $rewItems = new ItemList(array(['id', array_keys($reward)]));
            if (!$rewItems->error)
            {
                $this->extendGlobalData($rewItems->getJSGlobals());
                foreach ($reward as $id => $num)            // itr over $reward to preserve display order
                    if ($rewItems->getEntry($id))
                        $rewards[1][] = new IconElement(
                            Type::ITEM,
                            $id,
                            Lang::unescapeUISequences($rewItems->getField('name', true), Lang::FMT_HTML),
                            quality: $rewItems->getField('quality'),
                            num: $num
                        );
            }
        }

        if (!empty($this->subject->rewards[$this->typeId][Type::CURRENCY]))
        {
            $currency = $this->subject->rewards[$this->typeId][Type::CURRENCY];
            $rewCurr  = new CurrencyList(array(['id', array_keys($currency)]));
            if (!$rewCurr->error)
            {
                $this->extendGlobalData($rewCurr->getJSGlobals());
                foreach ($rewCurr->iterate() as $id => $__)
                    $rewards[1][] = new IconElement(
                        Type::CURRENCY,
                        $id,
                        $rewCurr->getField('name', true),
                        quality: ITEM_QUALITY_NORMAL,
                        num: $currency[$id] * ($side == SIDE_HORDE ? -1 : 1), // toggles the icon
                    );
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
                    $extra = Lang::quest('spellDisplayed', [$displ, Util::localizedString($_, 'name')]);

                if ($_ = $rewSpells->getEntry($cast))
                    $rewards[0] = array(
                        'title' => Lang::quest('rewardAura'),
                        'cast'  => [new IconElement(Type::SPELL, $cast, Util::localizedString($_, 'name'))],
                        'extra' => $extra
                    );
            }
            else                                            // if it has effect:learnSpell display the taught spell instead
            {
                $teach = [];
                foreach ($rewSpells->iterate() as $id => $__)
                    if ($_ = $rewSpells->canTeachSpell())
                        foreach ($_ as $idx)
                            $teach[$rewSpells->getField('effect'.$idx.'TriggerSpell')] = $id;

                if ($teach)
                {
                    $taught = new SpellList(array(['id', array_keys($teach)]));
                    if (!$taught->error)
                    {
                        $this->extendGlobalData($taught->getJSGlobals());
                        $rewards[0] = ['cast' => [], 'extra' => null];

                        $isTradeSkill = 0;
                        foreach ($taught->iterate() as $id => $__)
                        {
                            $isTradeSkill |= array_intersect($taught->getField('skillLines'), array_merge(SKILLS_TRADE_PRIMARY, SKILLS_TRADE_SECONDARY)) ? 1 : 0;
                            $rewards[0]['cast'][] = new IconElement(Type::SPELL, $id, $taught->getField('name', true));
                        }

                        $rewards[0]['title'] = $isTradeSkill ? Lang::quest('rewardTradeSkill') : Lang::quest('rewardSpell');
                    }
                }
                else if (($_ = $rewSpells->getEntry($displ)) || ($_ = $rewSpells->getEntry($cast)))
                {
                    $rewards[0] = array(
                        'title' => Lang::quest('rewardAura'),
                        'cast'  => [new IconElement(Type::SPELL, $cast, Util::localizedString($_, 'name'))],
                        'extra' => null
                    );
                }
            }
        }

        if (!array_filter($rewards))
            return null;

        return $rewards;
    }

    private function createMail(array $startEnd) : bool
    {
        $rmtId = $this->subject->getField('rewardMailTemplateId');
        if (!$rmtId)
            return false;

        $delay  = $this->subject->getField('rewardMailDelay');
        $letter = DB::Aowow()->selectRow('SELECT * FROM ?_mails WHERE `id` = ?d', $rmtId);

        $this->mail = array(
            'attachments' => [],
            'text'        => $letter ? Util::parseHtmlText(Util::localizedString($letter, 'text')) : null,
            'subject'     => Util::parseHtmlText(Util::localizedString($letter, 'subject')),
            'header'      => array(
                $rmtId,
                null,
                $delay  ? Lang::mail('mailIn', [DateTime::formatTimeElapsed($delay * 1000)]) : null,
            )
        );

        $senderTypeId = 0;
        if ($_= DB::World()->selectCell('SELECT `RewardMailSenderEntry` FROM quest_mail_sender WHERE `QuestId` = ?d', $this->typeId))
            $senderTypeId = $_;
        else
            foreach ($startEnd as $se)
                if (($se['method'] & 0x2) && $se['type'] == Type::NPC)
                    $senderTypeId = $se['typeId'];

        if ($ti = CreatureList::getName($senderTypeId))
            $this->mail['header'][1] = Lang::mail('mailBy', [$senderTypeId, $ti]);

        // while mail attachemnts are handled as loot, it has no variance. Always 100% chance, always one item.
        $mailLoot = new LootByContainer();
        if ($mailLoot->getByContainer(Loot::MAIL, [$rmtId]))
        {
            $this->extendGlobalData($mailLoot->jsGlobals);
            foreach ($mailLoot->getResult() as $loot)
                $this->mail['attachments'][] = new IconElement(Type::ITEM, $loot['id'], substr($loot['name'], 1), $loot['stack'][0], quality: 7 - $loot['name'][0]);
        }

        return true;
    }

    private function createGains() : ?array
    {
        $gains = [];

        // xp
        $gains[0] = $this->subject->getField('rewardXP');

        // talent points
        $gains[3] = $this->subject->getField('rewardTalents');

        // title
        if ($tId = $this->subject->getField('rewardTitleId'))
            $gains[2] = [$tId, (new TitleList(array(['id', $tId])))->getHtmlizedName()];
        else
            $gains[2] = null;

        // reputation
        $repGains = [];
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

            if ($cuRates = DB::World()->selectRow('SELECT * FROM reputation_reward_rate WHERE `faction` = ?d', $fac))
            {
                if ($this->subject->isRepeatable())
                    $rep['qty'][1] = $rep['qty'][0] * ($cuRates['quest_repeatable_rate'] - 1);
                else
                    $rep['qty'][1] = $rep['qty'][0] * match ($this->subject->isDaily())
                    {
                        1       => $cuRates['quest_daily_rate'] - 1,
                        2       => $cuRates['quest_weekly_rate'] - 1,
                        3       => $cuRates['quest_monthly_rate'] - 1,
                        default => $cuRates['quest_rate'] - 1
                    };
            }

            if (User::isInGroup(U_GROUP_STAFF))
                $rep['qty'][1]  = $rep['qty'][0] . ($rep['qty'][1] ? $this->fmtStaffTip(($rep['qty'][1] > 0 ? '+' : '').$rep['qty'][1], Lang::faction('customRewRate')) : '');
            else
                $rep['qty'][1] += $rep['qty'][0];

            $repGains[] = $rep;
        }
        $gains[1] = $repGains;

        if (!array_filter($gains))
            return null;

        return $gains;
    }

    private function createSeries() : array
    {
        $series = [];

        $makeSeriesItem = function (array $questData) : array
        {
            return array(
                'side'    => ChrRace::sideFromMask($questData['reqRaceMask']),
                'typeStr' => Type::getFileString(Type::QUEST),
                'typeId'  => $questData['id'],
                'name'    => Util::htmlEscape(Lang::trimTextClean(Util::localizedString($questData, 'name'), 40)),
            );
        };

        // Assumption
        // a chain always ends in a single quest, but can have an arbitrary amount of quests leading into it.
        // so we fast forward to the last quest and go backwards from there.

        $lastQuestId = $this->subject->getField('nextQuestIdChain');
        while ($newLast = DB::Aowow()->selectCell('SELECT `nextQuestIdChain` FROM ?_quests WHERE `id` = ?d AND `id` <> `nextQuestIdChain`', $lastQuestId))
            $lastQuestId = $newLast;

        $end   = DB::Aowow()->selectRow('SELECT `id`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, `reqRaceMask` FROM ?_quests WHERE `id` = ?d', $lastQuestId ?: $this->typeId);
        $chain = array(array($makeSeriesItem($end)));       // series / step / quest

        $prevStepIds = [$lastQuestId ?: $this->typeId];
        while ($prevQuests = DB::Aowow()->select('SELECT `id`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, `reqRaceMask` FROM ?_quests WHERE `nextQuestIdChain` IN (?a) AND `id` <> `nextQuestIdChain`', $prevStepIds))
        {
            $step = [];
            foreach ($prevQuests as $pQuest)
                $step[$pQuest['id']] = $makeSeriesItem($pQuest);

            $prevStepIds = array_keys($step);
            $chain[]     = $step;
        }

        if (count($chain) > 1)
            $series[] = [array_reverse($chain), null];

        // todo (low): sensibly merge the following lists into 'series'
        $listGen = function($cnd) use ($makeSeriesItem)
        {
            $chain = [];
            $list  = new QuestList($cnd);
            if ($list->error)
                return null;

            foreach ($list->iterate() as $tpl)
                $chain[] = [$makeSeriesItem($tpl)];

            return $chain;
        };

        $extraLists = array(
            // Requires all of these quests (Quests that you must follow to get this quest)
            ['reqQ',       array('OR', ['AND', ['nextQuestId', $this->typeId], ['exclusiveGroup', 0, '<']], ['AND', ['id', $this->subject->getField('prevQuestId')], ['nextQuestIdChain', $this->typeId, '!']])],

            // Requires one of these quests (Requires one of the quests to choose from)
            ['reqOneQ',    array('OR', ['AND', ['exclusiveGroup', 0, '>='], ['nextQuestId', $this->typeId]], ['breadCrumbForQuestId', $this->typeId])],

            // Opens Quests (Quests that become available only after complete this quest (optionally only one))
            ['opensQ',     array('OR', ['AND', ['prevQuestId', $this->typeId], ['id', $this->subject->getField('nextQuestIdChain'), '!']], ['id', $this->subject->getField('nextQuestId')], ['id', $this->subject->getField('breadcrumbForQuestId')])],

            // Closes Quests (Quests that become inaccessible after completing this quest)
            ['closesQ',    array(['exclusiveGroup', 0, '>'], ['exclusiveGroup', $this->subject->getField('exclusiveGroup')], ['id', $this->typeId, '!'])],

            // During the quest available these quests (Quests that are available only at run time this quest)
            ['enablesQ',   array(['prevQuestId', -$this->typeId])],

            // Requires an active quest (Quests during the execution of which is available on the quest)
            ['enabledByQ', array(['id', -$this->subject->getField('prevQuestId')])]
        );

        foreach ($extraLists as [$section, $condition])
            if ($_ = $listGen($condition))
                $series[] = [$_, sprintf(Util::$dfnString, Lang::quest($section.'Desc'), Lang::quest($section))];

        return $series;
    }
}

?>
