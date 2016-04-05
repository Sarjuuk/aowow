<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Object   g_initPath()
//  tabId 0: Database g_initHeader()
class ObjectPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_OBJECT;
    protected $typeId        = 0;
    protected $tpl           = 'object';
    protected $path          = [0, 5];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = ['swfobject.js'];

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new GameObjectList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound();

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('typeCat');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('object')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Event (ignore events, where the object only gets removed)
        if ($_ = DB::World()->selectCol('SELECT DISTINCT ge.eventEntry FROM game_event ge, game_event_gameobject geg, gameobject g WHERE ge.eventEntry = geg.eventEntry AND g.guid = geg.guid AND g.id = ?d', $this->typeId))
        {
            $this->extendGlobalIds(TYPE_WORLDEVENT, $_);
            $ev = [];
            foreach ($_ as $i => $e)
                $ev[] = ($i % 2 ? '[br]' : ' ') . '[event='.$e.']';

            $infobox[] = Util::ucFirst(Lang::game('eventShort')).Lang::main('colon').implode(',', $ev);
        }

        // Reaction
        $_ = function ($r)
        {
            if ($r == 1)  return 2;
            if ($r == -1) return 10;
            return;
        };
        $infobox[] = Lang::npc('react').Lang::main('colon').'[color=q'.$_($this->subject->getField('A')).']A[/color] [color=q'.$_($this->subject->getField('H')).']H[/color]';

        // reqSkill
        switch ($this->subject->getField('typeCat'))
        {
            case -3:                                            // Herbalism
                $infobox[] = sprintf(Lang::game('requires'), Lang::spell('lockType', 2).' ('.$this->subject->getField('reqSkill').')');
                break;
            case -4:                                            // Mining
                $infobox[] = sprintf(Lang::game('requires'), Lang::spell('lockType', 3).' ('.$this->subject->getField('reqSkill').')');
                break;
            case -5:                                            // Lockpicking
                $infobox[] = sprintf(Lang::game('requires'), Lang::spell('lockType', 1).' ('.$this->subject->getField('reqSkill').')');
                break;
            default:                                            // requires key .. maybe
            {
                $locks = Lang::getLocks($this->subject->getField('lockId'));
                $l = '';
                foreach ($locks as $idx => $_)
                {
                    if ($idx < 0)
                        continue;

                    $this->extendGlobalIds(TYPE_ITEM, $idx);
                    $l = Lang::gameObject('key').Lang::main('colon').'[item='.$idx.']';
                }

                // if no propper item is found use a skill
                if ($locks)
                    $infobox[] = $l ? $l : array_pop($locks);
            }
        }

        // linked trap
        if ($_ = $this->subject->getField('linkedTrap'))
        {
            $this->extendGlobalIds(TYPE_OBJECT, $_);
            $infobox[] = Lang::gameObject('trap').Lang::main('colon').'[object='.$_.']';
        }

        // trap for
        $trigger = new GameObjectList(array(['linkedTrap', $this->typeId]));
        if (!$trigger->error)
        {
            $this->extendGlobalData($trigger->getJSGlobals());
            $infobox[] = Lang::gameObject('triggeredBy').Lang::main('colon').'[object='.$trigger->id.']';
        }

        // SpellFocus
        if ($_ = $this->subject->getField('spellFocusId'))
            if ($sfo = DB::Aowow()->selectRow('SELECT * FROM ?_spellfocusobject WHERE id = ?d', $_))
                $infobox[] = '[tooltip name=focus]'.Lang::gameObject('focusDesc').'[/tooltip][span class=tip tooltip=focus]'.Lang::gameObject('focus').Lang::main('colon').Util::localizedString($sfo, 'name').'[/span]';

        // lootinfo: [min, max, restock]
        if (($_ = $this->subject->getField('lootStack')) && $_[0])
        {
            $buff = Lang::item('charges').Lang::main('colon').$_[0];
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
                $this->extendGlobalIds(TYPE_ZONE, $_[2]);
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
            if ($_ = $this->subject->getField('ScriptName'))
                $infobox[] = 'Script'.Lang::main('colon').$_;
            else if ($_ = $this->subject->getField('AIName'))
                $infobox[] = 'AI'.Lang::main('colon').$_;
        }


        /****************/
        /* Main Content */
        /****************/

        // pageText
        $pageText = [];
        if ($next = $this->subject->getField('pageTextId'))
        {
            while ($next)
            {
                if ($row = DB::World()->selectRow('SELECT *, Text as Text_loc0 FROM page_text pt LEFT JOIN locales_page_text lpt ON pt.ID = lpt.entry WHERE pt.ID = ?d', $next))
                {
                    $next = $row['NextPageID'];
                    $pageText[] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
                }
                else
                {
                    trigger_error('Referenced PageTextId #'.$next.' is not in DB', E_USER_WARNING);
                    break;
                }
            }
        }

        // add conditional js & css
        if ($pageText)
        {
            $this->addCSS(['path' => 'Book.css']);
            $this->addJS('Book.js');
        }

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
        if ($_ = DB::Aowow()->selectCell('SELECT ABS(npcId) FROM ?_loot_link WHERE objectId = ?d', $this->typeId))
        {
            // difficulty dummy
            if ($c = DB::Aowow()->selectRow('SELECT id, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_creature WHERE difficultyEntry1 = ?d OR difficultyEntry2 = ?d OR difficultyEntry3 = ?d', $_, $_, $_))
                $relBoss = [$c['id'], Util::localizedString($c, 'name')];
            // base creature
            else if ($c = DB::Aowow()->selectRow('SELECT id, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_creature WHERE id = ?d', $_))
                $relBoss = [$c['id'], Util::localizedString($c, 'name')];
        }

        $this->infobox     = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->pageText    = $pageText;
        $this->map         = $map;
        $this->relBoss     = $relBoss;
        $this->redButtons  = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true,
            BUTTON_VIEW3D  => ['displayId' => $this->subject->getField('displayId'), 'type' => TYPE_OBJECT, 'typeId' => $this->typeId]
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

            $this->lvTabs[] = ['spell', array(
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

                $this->lvTabs[] = ['spell', array(
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

            $this->lvTabs[] = ['achievement', array(
                'data' => array_values($acvs->getListviewData()),
                'id'   => 'criteria-of',
                'name' => '$LANG.tab_criteriaof'
            )];
        }

        // tab: starts quest
        // tab: ends quest
        $startEnd = new QuestList(array(['qse.type', TYPE_OBJECT], ['qse.typeId', $this->typeId]));
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
                $this->lvTabs[] = ['quest', array(
                    'data' => array_values($_[0]),
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts'
                )];

            if ($_[1])
                $this->lvTabs[] = ['quest', array(
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

                $this->lvTabs[] = ['quest', array(
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
                $extraCols[] = 'Listview.extraCols.percent';
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

                    $extraCols[] = 'Listview.extraCols.condition';
                    $reqQuest[$lv['id']] = 0;
                    $lv['condition'][0][$this->typeId][] = [[CND_QUESTTAKEN, &$reqQuest[$lv['id']]]];
                }

                $tabData = array(
                    'data'      => array_values($goLoot->getResult()),
                    'id'        => 'contains',
                    'name'      => '$LANG.tab_contains',
                    'sort'      => ['-percent', 'name'],
                    'extraCols' => ['$Listview.extraCols.percent']
                );

                if ($hiddenCols)
                    $tabData['hiddenCols'] = $hiddenCols;

                $this->lvTabs[] = ['item', $tabData];
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
                if (empty($reqQuests->requires[$qId][TYPE_ITEM]))
                    continue;

                foreach ($reqIds as $rId)
                    if (in_array($rId, $reqQuests->requires[$qId][TYPE_ITEM]))
                        $reqQuest[$rId] = $reqQuests->id;
            }
        }

        // tab: Same model as .. whats the fucking point..?
        $sameModel = new GameObjectList(array(['displayId', $this->subject->getField('displayId')], ['id', $this->typeId, '!']));
        if (!$sameModel->error)
        {
            $this->extendGlobalData($sameModel->getJSGlobals());

            $this->lvTabs[] = ['object', array(
                'data' => array_values($sameModel->getListviewData()),
                'name' => '$LANG.tab_samemodelas',
                'id'   => 'same-model-as'
            )];
        }
    }

    protected function generateTooltip($asError = false)
    {
        if ($asError)
            return '$WowheadPower.registerObject('.$this->typeId.', '.User::$localeId.', {});';

        $s = $this->subject->getSpawns(SPAWNINFO_SHORT);

        $x  = '$WowheadPower.registerObject('.$this->typeId.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($this->subject->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($this->subject->renderTooltip())."',\n";
        $x .= "\tmap: ".($s ? "{zone: ".$s[0].", coords: {".$s[1].":".Util::toJSON($s[2])."}}" : '{}')."\n";
        $x .= "});";

        return $x;
    }

    public function display($override = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::display($override);

        if (!$this->loadCache($tt))
        {
            $tt = $this->generateTooltip();
            $this->saveCache($tt);
        }

        header('Content-type: application/x-javascript; charset=utf-8');
        die($tt);
    }

    public function notFound($title = '', $msg = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::notFound($title ?: Lang::game('object'), $msg ?: Lang::gameObject('notFound'));

        header('Content-type: application/x-javascript; charset=utf-8');
        echo $this->generateTooltip(true);
        exit();
    }
}

?>
