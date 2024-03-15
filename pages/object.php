<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Object   g_initPath()
//  tabId 0: Database g_initHeader()
class ObjectPage extends GenericPage
{
    use TrDetailPage;

    protected $pageText      = [];
    protected $relBoss       = null;

    protected $type          = Type::OBJECT;
    protected $typeId        = 0;
    protected $tpl           = 'object';
    protected $path          = [0, 5];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/swfobject.js']];

    protected $_get          = ['domain' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkDomain']];

    private   $powerTpl      = '$WowheadPower.registerObject(%d, %d, %s);';

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && $this->_get['domain'])
            Util::powerUseLocale($this->_get['domain']);

        $this->typeId = intVal($id);

        $this->subject = new GameObjectList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('object'), Lang::gameObject('notFound'));

        $this->name = Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_HTML);
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('typeCat');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_RAW), Util::ucFirst(Lang::game('object')));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=zones']);

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Event (ignore events, where the object only gets removed)
        if ($_ = DB::World()->selectCol('SELECT DISTINCT ge.eventEntry FROM game_event ge, game_event_gameobject geg, gameobject g WHERE ge.eventEntry = geg.eventEntry AND g.guid = geg.guid AND g.id = ?d', $this->typeId))
        {
            $this->extendGlobalIds(Type::WORLDEVENT, ...$_);
            $ev = [];
            foreach ($_ as $i => $e)
                $ev[] = ($i % 2 ? '[br]' : ' ') . '[event='.$e.']';

            $infobox[] = Util::ucFirst(Lang::game('eventShort')).Lang::main('colon').implode(',', $ev);
        }

        // Faction
        if ($_ = DB::Aowow()->selectCell('SELECT factionId FROM ?_factiontemplate WHERE id = ?d', $this->subject->getField('faction')))
        {
            $this->extendGlobalIds(Type::FACTION, $_);
            $infobox[] = Util::ucFirst(Lang::game('faction')).Lang::main('colon').'[faction='.$_.']';
        }

        // Reaction
        $_ = function ($r)
        {
            if ($r == 1)  return 2;                         // q2  green
            if ($r == -1) return 10;                        // q10 red
            return;                                         // q   yellow
        };
        $infobox[] = Lang::npc('react').Lang::main('colon').'[color=q'.$_($this->subject->getField('A')).']A[/color] [color=q'.$_($this->subject->getField('H')).']H[/color]';

        // reqSkill +  difficulty
        switch ($this->subject->getField('typeCat'))
        {
            case -3:                                            // Herbalism
                $infobox[] = sprintf(Lang::game('requires'), Lang::spell('lockType', 2).' ('.$this->subject->getField('reqSkill').')');
                $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_HERBALISM, $this->subject->getField('reqSkill')));
                break;
            case -4:                                            // Mining
                $infobox[] = sprintf(Lang::game('requires'), Lang::spell('lockType', 3).' ('.$this->subject->getField('reqSkill').')');
                $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_MINING, $this->subject->getField('reqSkill')));
                break;
            case -5:                                            // Lockpicking
                $infobox[] = sprintf(Lang::game('requires'), Lang::spell('lockType', 1).' ('.$this->subject->getField('reqSkill').')');
                $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_LOCKPICKING, $this->subject->getField('reqSkill')));
                break;
            default:                                            // requires key .. maybe
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
                        $l[] = sprintf(Lang::game('requires'), $str);
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
            if ($sfo = DB::Aowow()->selectRow('SELECT * FROM ?_spellfocusobject WHERE id = ?d', $_))
                $infobox[] = '[tooltip name=focus]'.Lang::gameObject('focusDesc').'[/tooltip][span class=tip tooltip=focus]'.Lang::gameObject('focus').Lang::main('colon').Util::localizedString($sfo, 'name').'[/span]';

        // lootinfo: [min, max, restock]
        if (($_ = $this->subject->getField('lootStack')) && $_[0])
        {
            $buff = Lang::spell('spellModOp', 4).Lang::main('colon').$_[0];
            if ($_[0] < $_[1])
                $buff .= Lang::game('valueDelim').$_[1];

            // since Veins don't have charges anymore, the timer is questionable
            $infobox[] = $_[2] > 1 ? '[tooltip name=restock]'.sprintf(Lang::gameObject('restock'), Util::formatTime($_[2] * 1000)).'[/tooltip][span class=tip tooltip=restock]'.$buff.'[/span]' : $buff;
        }

        // meeting stone [minLevel, maxLevel, zone]
        if ($this->subject->getField('type') == OBJECT_MEETINGSTONE)
        {
            if ($_ = $this->subject->getField('mStone'))
            {
                $this->extendGlobalIds(Type::ZONE, $_[2]);
                $m = Lang::game('meetingStone').Lang::main('colon').'[zone='.$_[2].']';

                $l = $_[0];
                if ($_[0] > 1 && $_[1] > $_[0])
                    $l .= Lang::game('valueDelim').min($_[1], MAX_LEVEL);

                $infobox[] = $l ? '[tooltip name=meetingstone]'.sprintf(Lang::game('reqLevel'), $l).'[/tooltip][span class=tip tooltip=meetingstone]'.$m.'[/span]' : $m;
            }
        }

        // capture area [minPlayer, maxPlayer, minTime, maxTime, radius]
        if ($this->subject->getField('type') == OBJECT_CAPTURE_POINT)
        {
            if ($_ = $this->subject->getField('capture'))
            {
                $buff = Lang::gameObject('capturePoint');

                if ($_[2] > 1 || $_[0])
                    $buff .= Lang::main('colon').'[ul]';

                if ($_[2] > 1)
                    $buff .= '[li]'.Lang::game('duration').Lang::main('colon').($_[3] > $_[2] ? Util::FormatTime($_[3] * 1000, true).' - ' : null).Util::FormatTime($_[2] * 1000, true).'[/li]';

                if ($_[1])
                    $buff .= '[li]'.Lang::main('players').Lang::main('colon').$_[0].($_[1] > $_[0] ? ' - '.$_[1] : null).'[/li]';

                if ($_[4])
                    $buff .= '[li]'.sprintf(Lang::spell('range'), $_[4]).'[/li]';

                if ($_[2] > 1 || $_[0])
                    $buff .= '[/ul]';
            }

            $infobox[] = $buff;
        }

        // AI
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            if ($_ = $this->subject->getField('ScriptOrAI'))
            {
                if ($_ == 'SmartGameObjectAI')
                    $infobox[] = 'AI'.Lang::main('colon').$_;
                else
                    $infobox[] = 'Script'.Lang::main('colon').$_;
            }
        }


        /****************/
        /* Main Content */
        /****************/

        // pageText
        if ($this->pageText = Game::getPageText($this->subject->getField('pageTextId')))
            $this->addScript(
                [SC_JS_FILE,  'js/Book.js'],
                [SC_CSS_FILE, 'css/Book.css']
            );

        // get spawns and path
        $map = null;
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $map = ['data' => ['parent' => 'mapper-generic'], 'mapperData' => &$spawns];
            foreach ($spawns as $areaId => &$areaData)
                $map['extra'][$areaId] = ZoneList::getName($areaId);
        }


        // todo (low): consider pooled spawns


        $relBoss = null;
        if ($ll = DB::Aowow()->selectRow('SELECT * FROM ?_loot_link WHERE objectId = ?d ORDER BY priority DESC LIMIT 1', $this->typeId))
        {
            // group encounter
            if ($ll['encounterId'])
                $relBoss = [$ll['npcId'], Lang::profiler('encounterNames', $ll['encounterId'])];
            // difficulty dummy
            else if ($c = DB::Aowow()->selectRow('SELECT id, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_creature WHERE difficultyEntry1 = ?d OR difficultyEntry2 = ?d OR difficultyEntry3 = ?d', abs($ll['npcId']), abs($ll['npcId']), abs($ll['npcId'])))
                $relBoss = [$c['id'], Util::localizedString($c, 'name')];
            // base creature
            else if ($c = DB::Aowow()->selectRow('SELECT id, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_creature WHERE id = ?d', abs($ll['npcId'])))
                $relBoss = [$c['id'], Util::localizedString($c, 'name')];
        }

        // smart AI
        $sai = null;
        if ($this->subject->getField('ScriptOrAI') == 'SmartGameObjectAI')
        {
            $sai = new SmartAI(SAI_SRC_TYPE_OBJECT, $this->typeId, ['name' => $this->subject->getField('name', true)]);
            if (!$sai->prepare())                           // no smartAI found .. check per guid
            {
                // at least one of many
                $guids = DB::World()->selectCol('SELECT guid FROM gameobject WHERE id = ?d LIMIT 1', $this->typeId);
                while ($_ = array_pop($guids))
                {
                    $sai = new SmartAI(SAI_SRC_TYPE_OBJECT, -$_, ['name' => $this->subject->getField('name', true), 'title' => ' [small](for GUID: '.$_.')[/small]']);
                    if ($sai->prepare())
                        break;
                }
            }

            if ($sai->prepare())
                $this->extendGlobalData($sai->getJSGlobals());
            else
                trigger_error('Gameobject has AIName set in template but no SmartAI defined.');
        }

        $this->map         = $map;
        $this->infobox     = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->relBoss     = $relBoss;
        $this->smartAI     = $sai ? $sai->getMarkdown() : null;
        $this->redButtons  = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_VIEW3D  => ['displayId' => $this->subject->getField('displayId'), 'type' => Type::OBJECT, 'typeId' => $this->typeId]
        );


        /**************/
        /* Extra Tabs */
        /**************/

        // tab: summoned by
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', [50, 76, 104, 105, 106, 107]], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', [50, 76, 104, 105, 106, 107]], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', [50, 76, 104, 105, 106, 107]], ['effect3MiscValue', $this->typeId]]
        );

        $summons = new SpellList($conditions);
        if (!$summons->error)
        {
            $this->extendGlobalData($summons->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs[] = [SpellList::$brickFile, array(
                'data' => array_values($summons->getListviewData()),
                'id'   => 'summoned-by',
                'name' => '$LANG.tab_summonedby'
            )];
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

                $this->lvTabs[] = [SpellList::$brickFile, array(
                    'data'       => array_values($data),
                    'id'         => 'spells',
                    'name'       => '$LANG.tab_spells',
                    'hiddenCols' => ['skill'],
                    'extraCols'  => ["\$Listview.funcBox.createSimpleCol('trigger', 'Condition', '10%', 'trigger')"]
                )];
            }
        }

        // tab: criteria of
        $acvs = new AchievementList(array(['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT, ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT]], ['ac.value1', $this->typeId]));
        if (!$acvs->error)
        {
            $this->extendGlobalData($acvs->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs[] = [AchievementList::$brickFile, array(
                'data' => array_values($acvs->getListviewData()),
                'id'   => 'criteria-of',
                'name' => '$LANG.tab_criteriaof'
            )];
        }

        // tab: starts quest
        // tab: ends quest
        $startEnd = new QuestList(array(['qse.type', Type::OBJECT], ['qse.typeId', $this->typeId]));
        if (!$startEnd->error)
        {
            $this->extendGlobalData($startEnd->getJSGlobals());
            $lvData = $startEnd->getListviewData();
            $_ = [[], []];

            foreach ($startEnd->iterate() as $id => $__)
            {
                $m = $startEnd->getField('method');
                if ($m & 0x1)
                    $_[0][] = $lvData[$id];
                if ($m & 0x2)
                    $_[1][] = $lvData[$id];
            }

            if ($_[0])
                $this->lvTabs[] = [QuestList::$brickFile, array(
                    'data' => array_values($_[0]),
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts'
                )];

            if ($_[1])
                $this->lvTabs[] = [QuestList::$brickFile, array(
                    'data' => array_values($_[1]),
                    'name' => '$LANG.tab_ends',
                    'id'   => 'ends'
                )];
        }

        // tab: related quests
        if ($_ = $this->subject->getField('reqQuest'))
        {
            $relQuest = new QuestList(array(['id', $_]));
            if (!$relQuest->error)
            {
                $this->extendGlobalData($relQuest->getJSGlobals());

                $this->lvTabs[] = [QuestList::$brickFile, array(
                    'data' => array_values($relQuest->getListviewData()),
                    'name' => '$LANG.tab_quests',
                    'id'   => 'quests'
                )];
            }
        }

        // tab: contains
        $reqQuest = [];
        if ($_ = $this->subject->getField('lootId'))
        {
            $goLoot = new Loot();
            if ($goLoot->getByContainer(LOOT_GAMEOBJECT, $_))
            {
                $extraCols   = $goLoot->extraCols;
                $extraCols[] = '$Listview.extraCols.percent';
                $hiddenCols  = ['source', 'side', 'slot', 'reqlevel'];

                $this->extendGlobalData($goLoot->jsGlobals);

                foreach ($goLoot->iterate() as &$lv)
                {
                    if (!empty($hiddenCols))
                        foreach ($hiddenCols as $k => $str)
                            if (!empty($lv[$str]))
                                unset($hiddenCols[$k]);

                    if (!$lv['quest'])
                        continue;

                    $extraCols[] = '$Listview.extraCols.condition';
                    $reqQuest[$lv['id']] = 0;
                    $lv['condition'][0][$this->typeId][] = [[CND_QUESTTAKEN, &$reqQuest[$lv['id']]]];
                }

                $tabData = array(
                    'data'      => array_values($goLoot->getResult()),
                    'id'        => 'contains',
                    'name'      => '$LANG.tab_contains',
                    'sort'      => ['-percent', 'name'],
                    'extraCols' => array_unique($extraCols)
                );

                if ($hiddenCols)
                    $tabData['hiddenCols'] = $hiddenCols;

                $this->lvTabs[] = [ItemList::$brickFile, $tabData];
            }
        }

        if ($reqIds = array_keys($reqQuest))                    // apply quest-conditions as back-reference
        {
            $conditions = array(
                'OR',
                ['reqSourceItemId1', $reqIds], ['reqSourceItemId2', $reqIds],
                ['reqSourceItemId3', $reqIds], ['reqSourceItemId4', $reqIds],
                ['reqItemId1', $reqIds], ['reqItemId2', $reqIds], ['reqItemId3', $reqIds],
                ['reqItemId4', $reqIds], ['reqItemId5', $reqIds], ['reqItemId6', $reqIds]
            );

            $reqQuests = new QuestList($conditions);
            $this->extendGlobalData($reqQuests->getJSGlobals());

            foreach ($reqQuests->iterate() as $qId => $__)
            {
                if (empty($reqQuests->requires[$qId][Type::ITEM]))
                    continue;

                foreach ($reqIds as $rId)
                    if (in_array($rId, $reqQuests->requires[$qId][Type::ITEM]))
                        $reqQuest[$rId] = $reqQuests->id;
            }
        }

        // tab: Spell Focus for
        if ($sfId = $this->subject->getField('spellFocusId'))
        {
            $focusSpells = new SpellList(array(['spellFocusObject', $sfId]));
            if (!$focusSpells->error)
            {
                $tabData = array(
                    'data' => array_values($focusSpells->getListviewData()),
                    'name' => Lang::gameObject('focus'),
                    'id'   => 'focus-for'
                );

                $this->extendGlobalData($focusSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                // create note if search limit was exceeded
                if ($focusSpells->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                {
                    $tabData['note']  = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $focusSpells->getMatches(), CFG_SQL_LIMIT_DEFAULT);
                    $tabData['_truncated'] = 1;
                }

                $this->lvTabs[] = [SpellList::$brickFile, $tabData];
            }
        }

        // tab: trap for X
        $trigger = new GameObjectList(array(['linkedTrap', $this->typeId]));
        if (!$trigger->error)
        {
            $this->extendGlobalData($trigger->getJSGlobals());

            $this->lvTabs[] = [GameObjectList::$brickFile, array(
                'data' => array_values($trigger->getListviewData()),
                'name' => Lang::gameObject('triggeredBy'),
                'id'   => 'triggerd-by',
                'note' => sprintf(Util::$filterResultString, '?objects=6')
            )];
        }

        // tab: Same model as .. whats the fucking point..?
        $sameModel = new GameObjectList(array(['displayId', $this->subject->getField('displayId')], ['id', $this->typeId, '!']));
        if (!$sameModel->error)
        {
            $this->extendGlobalData($sameModel->getJSGlobals());

            $this->lvTabs[] = [GameObjectList::$brickFile, array(
                'data' => array_values($sameModel->getListviewData()),
                'name' => '$LANG.tab_samemodelas',
                'id'   => 'same-model-as'
            )];
        }
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString}    = Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_RAW);
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
            $power->map                              = $this->subject->getSpawns(SPAWNINFO_SHORT);
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }
}

?>
