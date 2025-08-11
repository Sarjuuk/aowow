<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ObjectBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_PAGE;

    protected  string $template   = 'object';
    protected  string $pageName   = 'object';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 5];

    public  int   $type    = Type::OBJECT;
    public  int   $typeId  = 0;
    public ?Book  $book    = null;
    public ?array $relBoss = null;

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
            if ($sfo = DB::Aowow()->selectRow('SELECT * FROM ?_spellfocusobject WHERE `id` = ?d', $_))
                $infobox[] = '[tooltip name=focus]'.Lang::gameObject('focusDesc').'[/tooltip][span class=tip tooltip=focus]'.Lang::gameObject('focus').Lang::main('colon').Util::localizedString($sfo, 'name').'[/span]';

        // lootinfo: [min, max, restock]
        if ($this->subject->getField('lootStack'))
        {
            [$min, $max, $restock] = $this->subject->getField('lootStack');
            $buff = Lang::spell('spellModOp', 4).Lang::main('colon').$min;
            if ($min < $max)
                $buff .= Lang::game('valueDelim').$max;

            // since Veins don't have charges anymore, the timer is questionable
            $infobox[] = $restock > 1 ? '[tooltip name=restock]'.Lang::gameObject('restock', [Util::formatTime($restock * 1000)]).'[/tooltip][span class=tip tooltip=restock]'.$buff.'[/span]' : $buff;
        }

        // meeting stone [minLevel, maxLevel, zone]
        if ($this->subject->getField('type') == OBJECT_MEETINGSTONE && $this->subject->getField('mStone'))
        {
            [$minLevel, $maxLevel, $zone] = $this->subject->getField('mStone');

            $this->extendGlobalIds(Type::ZONE, $zone);
            $m = Lang::game('meetingStone').'[zone='.$zone.']';

            $l = $minLevel;
            if ($minLevel > 1 && $maxLevel > $minLevel)
                $l .= Lang::game('valueDelim').min($maxLevel, MAX_LEVEL);

            $infobox[] = $l ? '[tooltip name=meetingstone]'.Lang::game('reqLevel', [$l]).'[/tooltip][span class=tip tooltip=meetingstone]'.$m.'[/span]' : $m;
        }

        // capture area
        if ($this->subject->getField('type') == OBJECT_CAPTURE_POINT && $this->subject->getField('capture'))
        {
            [$minPlayer, $maxPlayer, $minTime, $maxTime, $radius] = $this->subject->getField('capture');

            $buff = Lang::gameObject('capturePoint');

            if ($minTime > 1 || $minPlayer || $radius)
                $buff .= Lang::main('colon').'[ul]';

            if ($minTime > 1)
                $buff .= '[li]'.Lang::game('duration').Lang::main('colon').($maxTime > $minTime ? Util::FormatTime($maxTime * 1000, true).' - ' : '').Util::FormatTime($minTime * 1000, true).'[/li]';

            if ($minPlayer)
                $buff .= '[li]'.Lang::main('players').Lang::main('colon').$minPlayer.($maxPlayer > $minPlayer ? ' - '.$maxPlayer : '').'[/li]';

            if ($radius)
                $buff .= '[li]'.Lang::spell('range', [$radius]).'[/li]';

            if ($minTime > 1 || $minPlayer || $radius)
                $buff .= '[/ul]';

            $infobox[] = $buff;
        }

        // AI
        if (User::isInGroup(U_GROUP_EMPLOYEE))
            if ($_ = $this->subject->getField('ScriptOrAI'))
                $infobox[] = ($_ == 'SmartGameObjectAI' ? 'AI' :  'Script').Lang::main('colon').$_;

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
                trigger_error('Gameobject has AIName set in template but no SmartAI defined.');
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
            $goLoot = new Loot();
            if ($goLoot->getByContainer(LOOT_GAMEOBJECT, $_))
            {
                $extraCols   = $goLoot->extraCols;
                $extraCols[] = '$Listview.extraCols.percent';
                $hiddenCols  = ['source', 'side', 'slot', 'reqlevel'];

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
                    'data'       => $lootResult,
                    'id'         => 'contains',
                    'name'       => '$LANG.tab_contains',
                    'sort'       => ['-percent', 'name'],
                    'extraCols'  => array_unique($extraCols),
                    'hiddenCols' => $hiddenCols ?: null
                ), ItemList::$brickFile));
            }
        }

        // tab: Spell Focus for
        if ($sfId = $this->subject->getField('spellFocusId'))
        {
            $focusSpells = new SpellList(array(['spellFocusObject', $sfId]), ['calcTotal' => true]);
            if (!$focusSpells->error)
            {
                $tabData = array(
                    'data' => $focusSpells->getListviewData(),
                    'name' => Lang::gameObject('focus'),
                    'id'   => 'focus-for'
                );

                $this->extendGlobalData($focusSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                // create note if search limit was exceeded
                if ($focusSpells->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
                {
                    $tabData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $focusSpells->getMatches(), Cfg::get('SQL_LIMIT_DEFAULT'));
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
