<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ObjectBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'object';
    protected  string $pageName   = 'object';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 5];

    public  int   $type    = Type::OBJECT;
    public  int   $typeId  = 0;
    public ?Book  $book    = null;
    public ?array $relBoss = null;

    private array $difficulties = [];
    private int   $mapType      = 0;

    private GameObjectList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new GameObjectList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('object'), Lang::gameObject('notFound'));

        $this->h1 = Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_HTML);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('typeCat');


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_RAW), Util::ucFirst(Lang::game('object')));


        /**********************/
        /* Determine Map Type */
        /**********************/

        if ($objectdifficulty = DB::Aowow()->select(        // has difficulty versions of itself
           'SELECT `normal10` AS "0", `normal25` AS "1",
                   `heroic10` AS "2", `heroic25` AS "3",
                   `mapType`  AS ARRAY_KEY
            FROM   ?_objectdifficulty
            WHERE  `normal10` = ?d OR `normal25` = ?d OR
                   `heroic10` = ?d OR `heroic25` = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId
        ))
        {
            $this->mapType      = key($objectdifficulty);
            $this->difficulties = array_pop($objectdifficulty);
        }
        else if ($maps = DB::Aowow()->selectCell('SELECT IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d', Type::OBJECT, $this->typeId))
        {
            $this->mapType = match ((int)DB::Aowow()->selectCell('SELECT `type` FROM ?_zones WHERE `id` = ?d', $maps))
            {
                // MAP_TYPE_DUNGEON,
                MAP_TYPE_DUNGEON_HC    => 1,
                // MAP_TYPE_RAID,
                MAP_TYPE_MMODE_RAID,
                MAP_TYPE_MMODE_RAID_HC => 2,
                default                => 0
            };
        }


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Event (ignore events, where the object only gets removed)
        if ($_ = DB::World()->selectCol('SELECT DISTINCT ge.`eventEntry` FROM game_event ge, game_event_gameobject geg, gameobject g WHERE ge.`eventEntry` = geg.`eventEntry` AND g.`guid` = geg.`guid` AND g.`id` = ?d', $this->typeId))
        {
            $this->extendGlobalIds(Type::WORLDEVENT, ...$_);
            $ev = [];
            foreach ($_ as $i => $e)
                $ev[] = ($i % 2 ? '[br]' : ' ') . '[event='.$e.']';

            $infobox[] = Lang::game('eventShort', [implode(',', $ev)]);
        }

        // Faction
        if ($_ = DB::Aowow()->selectCell('SELECT `factionId` FROM ?_factiontemplate WHERE `id` = ?d', $this->subject->getField('faction')))
        {
            $this->extendGlobalIds(Type::FACTION, $_);
            $infobox[] = Util::ucFirst(Lang::game('faction')).Lang::main('colon').'[faction='.$_.']';
        }

        // Reaction
        $color = fn (int $r) : string => match($r)
        {
             1      => 'q2',                                // q2  green
            -1      => 'q10',                               // q10 red
            default => 'q'                                  // q   yellow
        };
        $infobox[] = Lang::npc('react', ['[color='.$color($this->subject->getField('A')).']A[/color] [color='.$color($this->subject->getField('H')).']H[/color]']);

        // reqSkill +  difficulty
        switch ($this->subject->getField('typeCat'))
        {
            case -3:                                        // Herbalism
                $infobox[] = Lang::game('requires', [Lang::spell('lockType', 2).' ('.$this->subject->getField('reqSkill').')']);
                $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_HERBALISM, $this->subject->getField('reqSkill')));
                break;
            case -4:                                        // Mining
                $infobox[] = Lang::game('requires', [Lang::spell('lockType', 3).' ('.$this->subject->getField('reqSkill').')']);
                $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_MINING, $this->subject->getField('reqSkill')));
                break;
            case -5:                                        // Lockpicking
                $infobox[] = Lang::game('requires', [Lang::spell('lockType', 1).' ('.$this->subject->getField('reqSkill').')']);
                $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_LOCKPICKING, $this->subject->getField('reqSkill')));
                break;
            default:                                        // requires key .. maybe
            {
                $locks = Lang::getLocks($this->subject->getField('lockId'), $ids, true, Lang::FMT_MARKUP);
                $l = [];

                foreach ($ids as $type => $typeIds)
                    $this->extendGlobalIds($type, ...$typeIds);

                foreach ($locks as $idx => $str)
                {
                    if ($idx > 0)
                        $l[] = Lang::gameObject('key').Lang::main('colon').$str;
                    else if ($idx < 0)
                        $l[] = Lang::game('requires', [$str]);
                }

                if ($l)
                    $infobox[] = implode('[br]', $l);
            }
        }

        // linked trap
        if ($_ = $this->subject->getField('linkedTrap'))
        {
            $this->extendGlobalIds(Type::OBJECT, $_);
            $infobox[] = Lang::gameObject('trap').Lang::main('colon').'[object='.$_.']';
        }

        // trap for X (note: moved to lv-tabs)

        // SpellFocus
        if ($_ = $this->subject->getField('spellFocusId'))
        {
            if ($sfo = DB::Aowow()->selectRow('SELECT * FROM ?_spellfocusobject WHERE `id` = ?d', $_))
            {
                $n = Util::localizedString($sfo, 'name');
                if (!is_null(GameObjectListFilter::getCriteriaIndex(50, $_)))
                    $n = '[url=?objects&filter=cr=50;crs='.$_.';crv=0]'.$n.'[/url]';

                $infobox[] = '[tooltip name=focus]'.Lang::gameObject('focusDesc').'[/tooltip][span class=tip tooltip=focus]'.Lang::gameObject('focus').Lang::main('colon').$n.'[/span]';
            }
        }

        // lootinfo: [min, max, restock]
        if (([$min, $max, $restock] = $this->subject->getField('lootStack')) && $min)
        {
            $buff = Lang::spell('spellModOp', 4).Lang::main('colon').Util::createNumRange($min, $max);

            // ore veins don't have charges in 335a, but the functionality is still there
            $infobox[] = $restock > 1 ? '[tooltip name=restock]'.Lang::gameObject('restock', [DateTime::formatTimeElapsed($restock * 1000)]).'[/tooltip][span class=tip tooltip=restock]'.$buff.'[/span]' : $buff;
        }

        // meeting stone (only on type: OBJECT_MEETINGSTONE)
        if ([$minLevel, $maxLevel, $zone] = $this->subject->getField('mStone'))
        {
            $this->extendGlobalIds(Type::ZONE, $zone);
            $m = Lang::game('meetingStone').'[zone='.$zone.']';
            $l = Util::createNumRange($minLevel, min($maxLevel, MAX_LEVEL));

            $infobox[] = $l ? '[tooltip name=meetingstone]'.Lang::game('reqLevel', [$l]).'[/tooltip][span class=tip tooltip=meetingstone]'.$m.'[/span]' : $m;
        }

        // capture area (only on type: OBJECT_CAPTURE_POINT)
        if ([$minPlayer, $maxPlayer, $minTime, $maxTime, $radius] = $this->subject->getField('capture'))
        {
            $buff = Lang::gameObject('capturePoint');

            if ($minTime > 1 || $minPlayer || $radius)
                $buff .= Lang::main('colon').'[ul]';

            if ($minTime > 1)                               // sign shenannigans reverse the display order
                $buff .= '[li]'.Lang::game('duration').Lang::main('colon').Util::createNumRange(-$maxTime, -$minTime, fn: fn($x) => DateTime::formatTimeElapsed(-$x * 1000)).'[/li]';

            if ($minPlayer)
                $buff .= '[li]'.Lang::main('players').Lang::main('colon').Util::createNumRange($minPlayer, $maxPlayer).'[/li]';

            if ($radius)
                $buff .= '[li]'.Lang::spell('range', [$radius]).'[/li]';

            if ($minTime > 1 || $minPlayer || $radius)
                $buff .= '[/ul]';

            $infobox[] = $buff;
        }

        // id
        $infobox[] = Lang::gameObject('id') . $this->typeId;

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        // used in mode
        foreach ($this->difficulties as $n => $id)
            if ($id == $this->typeId)
                $infobox[] = Lang::game('mode').Lang::game('modes', $this->mapType, $n);

        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            $spawnData = DB::Aowow()->select('SELECT `guid` AS "0", `ScriptName` AS "1", `StringId` AS "2" FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d AND `ScriptName` IS NOT NULL ORDER BY `guid` ASC', Type::OBJECT, $this->typeId);

            // AI
            $scripts = null;
            if ($_ = $this->subject->getField('ScriptOrAI'))
                $scripts = ($_ == 'SmartGameObjectAI' ? 'AI' :  'Script').Lang::main('colon').$_;

            if ($moreAI = array_filter(array_column($spawnData, 1, 0)))
            {
                $scripts ??= 'Script'.Lang::main('colon').'…';
                $scripts   = '[toggler=hidden id=scriptName]'.$scripts.'[/toggler][div=hidden id=scriptName][ul]';
                foreach ($moreAI as $guid => $script)
                    $scripts .= sprintf('[li]GUID: %d - %s[/li]', $guid, $script);

                $scripts .= '[/ul][/div]';
            }

            if ($scripts)
                $infobox[] = $scripts;

            // StringId
            $stringIDs = null;
            if ($_ = $this->subject->getField('StringId'))
                $stringIDs = 'StringID'.Lang::main('colon').$_;

            if ($moreStrings = array_filter(array_column($spawnData, 2, 0)))
            {
                $stringIDs ??= 'StringID'.Lang::main('colon').'…';
                $stringIDs   = '[toggler=hidden id=stringId]'.$stringIDs.'[/toggler][div=hidden id=stringId][ul]';
                foreach ($moreStrings as $guid => $stringId)
                    $stringIDs .= sprintf('[li]GUID: %d - %s[/li]', $guid, $stringId);

                $stringIDs .= '[/ul][/div]';
            }
        }

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        // pageText / book
        if ($this->book = Game::getBook($this->subject->getField('pageTextId')))
            $this->addScript(
                [SC_JS_FILE,  'js/Book.js'],
                [SC_CSS_FILE, 'css/Book.css']
            );

        // get spawns and path
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $this->addDataLoader('zones');
            $this->map = array(
                ['parent' => 'mapper-generic'],             // Mapper
                $spawns,                                    // mapperData
                null,                                       // ShowOnMap
                [Lang::gameObject('foundIn')]               // foundIn
            );
            foreach ($spawns as $areaId => $_)
                $this->map[3][$areaId] = ZoneList::getName($areaId);
        }


        // todo (low): consider pooled spawns


        if ($ll = DB::Aowow()->selectRow('SELECT * FROM ?_loot_link WHERE `objectId` = ?d ORDER BY `priority` DESC LIMIT 1', $this->typeId))
        {
            // group encounter
            if ($ll['encounterId'])
                $this->relBoss = [$ll['npcId'], Lang::profiler('encounterNames', $ll['encounterId'])];
            // difficulty dummy
            else if ($c = DB::Aowow()->selectRow('SELECT `id`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM ?_creature WHERE `difficultyEntry1` = ?d OR `difficultyEntry2` = ?d OR `difficultyEntry3` = ?d', $ll['npcId'], $ll['npcId'], $ll['npcId']))
                $this->relBoss = [$c['id'], Util::localizedString($c, 'name')];
            // base creature
            else if ($c = DB::Aowow()->selectRow('SELECT `id`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM ?_creature WHERE `id` = ?d', $ll['npcId']))
                $this->relBoss = [$c['id'], Util::localizedString($c, 'name')];
        }

        // Smart AI
        $sai = null;
        if ($this->subject->getField('ScriptOrAI') == 'SmartGameObjectAI')
        {
            $sai = new SmartAI(SmartAI::SRC_TYPE_OBJECT, $this->typeId);
            if (!$sai->prepare())                           // no smartAI found .. check per guid
            {
                // at least one of many
                $guids = DB::World()->selectCol('SELECT `guid` FROM gameobject WHERE `id` = ?d', $this->typeId);
                while ($_ = array_pop($guids))
                {
                    $sai = new SmartAI(SmartAI::SRC_TYPE_OBJECT, -$_, ['title' => ' [small](for GUID: '.$_.')[/small]']);
                    if ($sai->prepare())
                        break;
                }
            }

            if ($sai->prepare())
            {
                $this->extendGlobalData($sai->getJSGlobals());
                $this->smartAI = $sai->getMarkup();
            }
            else
                trigger_error('Gameobject has `AIName`: SmartGameObjectAI set in template but no SmartAI defined.');
        }

        $this->redButtons  = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_VIEW3D  => ['displayId' => $this->subject->getField('displayId'), 'type' => Type::OBJECT, 'typeId' => $this->typeId]
        );


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: summoned by
        $summonEffects = array(
            SPELL_EFFECT_TRANS_DOOR,
            SPELL_EFFECT_SUMMON_OBJECT_WILD,
            SPELL_EFFECT_SUMMON_OBJECT_SLOT1,
            SPELL_EFFECT_SUMMON_OBJECT_SLOT2,
            SPELL_EFFECT_SUMMON_OBJECT_SLOT3,
            SPELL_EFFECT_SUMMON_OBJECT_SLOT4
        );
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', $summonEffects], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', $summonEffects], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', $summonEffects], ['effect3MiscValue', $this->typeId]]
        );

        $summons = new SpellList($conditions);
        if (!$summons->error)
        {
            $this->extendGlobalData($summons->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $summons->getListviewData(),
                'id'   => 'summoned-by',
                'name' => '$LANG.tab_summonedby'
            ), SpellList::$brickFile));
        }

        // tab: related spells
        if ($_ = $this->subject->getField('spells'))
        {
            $relSpells = new SpellList(array(['id', $_]));
            if (!$relSpells->error)
            {
                $this->extendGlobalData($relSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                $data = $relSpells->getListviewData();

                foreach ($data as $relId => $d)
                    $data[$relId]['trigger'] = array_search($relId, $_);

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'       => $data,
                    'id'         => 'spells',
                    'name'       => '$LANG.tab_spells',
                    'hiddenCols' => ['skill'],
                    'extraCols'  => ["\$Listview.funcBox.createSimpleCol('trigger', 'Condition', '10%', 'trigger')"]
                ), SpellList::$brickFile));
            }
        }

        // tab: criteria of
        $acvs = new AchievementList(array(['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT, ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT]], ['ac.value1', $this->typeId]));
        if (!$acvs->error)
        {
            $this->extendGlobalData($acvs->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $acvs->getListviewData(),
                'id'   => 'criteria-of',
                'name' => '$LANG.tab_criteriaof'
            ), AchievementList::$brickFile));
        }

        // tab: starts quest
        // tab: ends quest
        $startEnd = new QuestList(array(['qse.type', Type::OBJECT], ['qse.typeId', $this->typeId]));
        if (!$startEnd->error)
        {
            $this->extendGlobalData($startEnd->getJSGlobals());
            $lvData = $startEnd->getListviewData();
            $start  = $end = [];

            foreach ($startEnd->iterate() as $id => $__)
            {
                if ($startEnd->getField('method') & 0x1)
                    $start[] = $lvData[$id];
                if ($startEnd->getField('method') & 0x2)
                    $end[]   = $lvData[$id];
            }

            if ($start)
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $start,
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts'
                ), QuestList::$brickFile));

            if ($end)
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $end,
                    'name' => '$LANG.tab_ends',
                    'id'   => 'ends'
                ), QuestList::$brickFile));
        }

        // tab: related quests
        if ($_ = $this->subject->getField('reqQuest'))
        {
            $relQuest = new QuestList(array(['id', $_]));
            if (!$relQuest->error)
            {
                $this->extendGlobalData($relQuest->getJSGlobals());

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $relQuest->getListviewData(),
                    'name' => '$LANG.tab_quests',
                    'id'   => 'quests'
                ), QuestList::$brickFile));
            }
        }

        // tab: contains
        if ($_ = $this->subject->getField('lootId'))
        {
            // check if loot_link entry exists (only difficulty: 1)
            if ($npcId = DB::Aowow()->selectCell('SELECT `npcId` FROM ?_loot_link WHERE `objectId` = ?d AND `difficulty` = 1', $this->typeId))
            {
                // get id set of npc
                $lootEntries = DB::Aowow()->selectCol(
                   'SELECT    ll.`difficulty` AS ARRAY_KEY, o.`lootId`
                    FROM      ?_creature c
                    LEFT JOIN ?_loot_link ll ON ll.`npcId` IN (c.`id`, c.`difficultyEntry1`, c.`difficultyEntry2`, c.`difficultyEntry3`)
                    LEFT JOIN ?_objects o    ON o.`id` = ll.`objectId`
                    WHERE     c.`id` = ?d
                    ORDER BY  ll.`difficulty` ASC',
                    $npcId
                );

                if ($this->mapType == 2 || count($lootEntries) > 2) // always raid
                    $lootEntries = array_combine(array_map(fn($x) => 1 << (2 + $x), array_keys($lootEntries)), array_values($lootEntries));
                else if ($this->mapType == 1 || count($lootEntries) == 2) // dungeon or raid, assume dungeon
                    $lootEntries = array_combine(array_map(fn($x) => 1 << (2 - $x), array_keys($lootEntries)), array_values($lootEntries));
            }
            else
                $lootEntries = [4 => $_];

            $goLoot = new LootByContainer();
            if ($goLoot->getByContainer(Loot::GAMEOBJECT, $lootEntries))
            {
                $extraCols  = $goLoot->extraCols;
                array_push($extraCols, '$Listview.extraCols.count', '$Listview.extraCols.percent');
                if (count($lootEntries) > 1)
                    $extraCols[] = '$Listview.extraCols.mode';

                $hiddenCols = ['source', 'side', 'slot', 'reqlevel', 'count'];

                $this->extendGlobalData($goLoot->jsGlobals);
                $lootResult = $goLoot->getResult();

                foreach ($hiddenCols as $k => $str)
                {
                    if ($k == 1 && array_filter(array_column($lootResult, $str), fn ($x) => $x != SIDE_BOTH))
                        unset($hiddenCols[$k]);
                    else if ($k != 1 && !array_filter(array_column($lootResult, $str)))
                        unset($hiddenCols[$k]);
                }

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'            => $lootResult,
                    'id'              => 'contains',
                    'name'            => '$LANG.tab_contains',
                    'sort'            => ['-percent', 'name'],
                    'extraCols'       => array_unique($extraCols),
                    'hiddenCols'      => $hiddenCols ?: null,
                    'sort'            => ['-percent', 'name'],
                    '_totalCount'     => 10000,
                    'computeDataFunc' => '$Listview.funcBox.initLootTable',
                    'onAfterCreate'   => '$Listview.funcBox.addModeIndicator',
                ), ItemList::$brickFile));
            }
        }

        // tab: Spell Focus for
        if ($sfId = $this->subject->getField('spellFocusId'))
        {
            $focusSpells = new SpellList(array(Listview::DEFAULT_SIZE, ['spellFocusObject', $sfId]), ['calcTotal' => true]);
            if (!$focusSpells->error)
            {
                $tabData = array(
                    'data' => $focusSpells->getListviewData(),
                    'name' => Lang::gameObject('focus'),
                    'id'   => 'focus-for'
                );

                $this->extendGlobalData($focusSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                // create note if search limit was exceeded
                if ($focusSpells->getMatches() > Listview::DEFAULT_SIZE)
                {
                    $tabData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $focusSpells->getMatches(), Listview::DEFAULT_SIZE);
                    $tabData['_truncated'] = 1;
                }

                $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
            }
        }

        // tab: trap for X
        $trigger = new GameObjectList(array(['linkedTrap', $this->typeId]));
        if (!$trigger->error)
        {
            $this->extendGlobalData($trigger->getJSGlobals());

            $this->addDataLoader('zones');
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $trigger->getListviewData(),
                'name' => Lang::gameObject('triggeredBy'),
                'id'   => 'triggerd-by',
                'note' => sprintf(Util::$filterResultString, '?objects=6')
            ), GameObjectList::$brickFile));
        }

        // tab: see also
        if ($this->difficulties)
        {
            $conditions = array(
                'AND',
                ['id', $this->difficulties],
                ['id', $this->typeId, '!']
            );

            $saObjects = new GameObjectList($conditions);
            if (!$saObjects->error)
            {
                $data = $saObjects->getListviewData();
                if ($this->difficulties)
                {
                    $saE = ['$Listview.extraCols.mode'];

                    foreach ($data as $id => &$d)
                    {
                        if (($modeBit = array_search($id, $this->difficulties)) !== false)
                        {
                            if ($this->mapType)
                                $d['modes'] = ['mode' => 1 << ($modeBit + 3)];
                            else
                                $d['modes'] = ['mode' => 2 - $modeBit];
                        }
                        else
                            $d['modes'] = ['mode' => 0];
                    }
                }

                $tabData = array(
                    'data'        => $data,
                    'id'          => 'see-also',
                    'name'        => '$LANG.tab_seealso',
                    'visibleCols' => ['level'],
                );

                if (isset($saE))
                    $tabData['extraCols'] = $saE;

                $this->lvTabs->addListviewTab(new Listview($tabData, GameObjectList::$brickFile));
            }
        }

        // tab: Same model as
        $sameModel = new GameObjectList(array(['displayId', $this->subject->getField('displayId')], ['id', $this->typeId, '!']));
        if (!$sameModel->error)
        {
            $this->extendGlobalData($sameModel->getJSGlobals());

            $this->addDataLoader('zones');
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $sameModel->getListviewData(),
                'name' => '$LANG.tab_samemodelas',
                'id'   => 'same-model-as'
            ), GameObjectList::$brickFile));
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::OBJECT, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }
}

?>
