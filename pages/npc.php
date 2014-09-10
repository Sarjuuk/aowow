<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 4: NPC      g_initPath()
//  tabId 0: Database g_initHeader()
class NpcPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_NPC;
    protected $typeId        = 0;
    protected $tpl           = 'npc';
    protected $path          = [0, 4];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = array(
        'swfobject.js',
        // 'Mapper.js'
    );
    protected $css           = array(
        // ['path' => 'Mapper.css']
    );

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new CreatureList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::$game['npc']);

        $this->name    = $this->subject->getField('name', true);
        $this->subname = $this->subject->getField('subname', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('type');

        if ($_ = $this->subject->getField('family'))
            $this->path[] = $_;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::$game['npc']));
    }

    protected function generateContent()
    {
        $_typeFlags = $this->subject->getField('typeFlags');
        $_altIds    = [];
        $_altNPCs   = null;
        $position   = null;

        // difficulty entries of self
        if ($this->subject->getField('cuFlags') & NPC_CU_DIFFICULTY_DUMMY)
        {
            // find and create link to regular creature
            $regNPC = new CreatureList(array(['OR', ['difficultyEntry1', $this->typeId], ['difficultyEntry2', $this->typeId], ['difficultyEntry3', $this->typeId]]));
            $position = [$regNPC->id, $regNPC->getField('name', true)];
        }
        else
        {
            for ($i = 1; $i < 4; $i++)
                if ($_ = $this->subject->getField('difficultyEntry'.$i))
                    $_altIds[$_] = $i;

            if ($_altIds)
                $_altNPCs = new CreatureList(array(['id', array_keys($_altIds)]));
        }

        // hmm, this won't do if the creature is spawned by event/script
        $mapType = 2;                                       // should be 0, tmp-override until Zones
        // $maps = DB::Aowow()->selectCol('SELECT DISTINCT map from creature WHERE id = ?d', $this->typeId);
        // if (count($maps) == 1)                              // should only exist in one instance
        // {
            // $map = new ZoneList(array(1, ['mapId', $maps[0]], ['parentArea', 0]));
            // $mapType = $map->getField('areaType');
        // }

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Event
        if ($_ = DB::Aowow()->selectRow('SELECT e.id, holidayId FROM ?_events e, game_event_creature gec, creature c WHERE e.id = ABS(gec.eventEntry) AND c.guid = gec.guid AND c.id = ?d', $this->typeId))
        {
            if ($h = $_['holidayId'])
            {
                $this->extendGlobalIds(TYPE_WORLDEVENT, $_['id']);
                $infobox[] = Util::ucFirst(Lang::$game['eventShort']).Lang::$main['colon'].'[event='.$h.']';
            }
        }

        // Level
        if ($this->subject->getField('rank') != NPC_RANK_BOSS)
        {
            $level  = $this->subject->getField('minLevel');
            $maxLvl = $this->subject->getField('maxLevel');
            if ($level < $maxLvl)
                $level .= ' - '.$maxLvl;
        }
        else                                                // Boss Level
            $level = '??';

        $infobox[] = Lang::$game['level'].Lang::$main['colon'].$level;

        // Classification
        if ($_ = $this->subject->getField('rank'))          //  != NPC_RANK_NORMAL
        {
            $str = $_typeFlags & 0x4 ? '[span class=icon-boss]'.Lang::$npc['rank'][$_].'[/span]' : Lang::$npc['rank'][$_];
            $infobox[] = Lang::$npc['classification'].Lang::$main['colon'].$str;
        }

        // Reaction
        $_ = function ($r)
        {
            if ($r == 1)  return 2;
            if ($r == -1) return 10;
            return;
        };
        $infobox[] = Lang::$npc['react'].Lang::$main['colon'].'[color=q'.$_($this->subject->getField('A')).']A[/color] [color=q'.$_($this->subject->getField('H')).']H[/color]';

        // Faction
        $this->extendGlobalIds(TYPE_FACTION, $this->subject->getField('factionId'));
        $infobox[] = Util::ucFirst(Lang::$game['faction']).Lang::$main['colon'].'[faction='.$this->subject->getField('factionId').']';

        // Wealth
        if ($_ = intVal(($this->subject->getField('minGold') + $this->subject->getField('maxGold')) / 2))
            $infobox[] = Lang::$npc['worth'].Lang::$main['colon'].'[tooltip=tooltip_avgmoneydropped][money='.$_.'][/tooltip]';

        // is Vehicle
        if ($this->subject->getField('vehicleId'))
            $infobox[] = Lang::$npc['vehicle'];

        // AI
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            if ($_ = $this->subject->getField('scriptName'))
                $infobox[] = 'Script'.Lang::$main['colon'].$_;
            else if ($_ = $this->subject->getField('aiName'))
                $infobox[] = 'AI'.Lang::$main['colon'].$_;
        }

        // > Stats
        $_nf     = function ($num) { return number_format($num, 0, '', '.'); };
        $stats   = [];
        $modes   = [];                                      // get difficulty versions if set
        $hint    = '[tooltip name=%3$s][table cellspacing=10][tr]%1s[/tr][/table][/tooltip][span class=tip tooltip=%3$s]%2s[/span]';
        $modeRow = '[tr][td]%s&nbsp;&nbsp;[/td][td]%s[/td][/tr]';
        // Health
        $health = $this->subject->getBaseStats('health');
        $stats['health'] = Util::ucFirst(Lang::$spell['powerTypes'][-2]).Lang::$main['colon'].($health[0] < $health[1] ? $_nf($health[0]).' - '.$_nf($health[1]) : $_nf($health[0]));

        // Mana (may be 0)
        $mana = $this->subject->getBaseStats('power');
        $stats['mana'] = $mana[0] ? Lang::$spell['powerTypes'][0].Lang::$main['colon'].($mana[0] < $mana[1] ? $_nf($mana[0]).' - '.$_nf($mana[1]) : $_nf($mana[0])) : null;

        // Armor
        $armor = $this->subject->getBaseStats('armor');
        $stats['armor'] = Lang::$npc['armor'].Lang::$main['colon'].($armor[0] < $armor[1] ? $_nf($armor[0]).' - '.$_nf($armor[1]) : $_nf($armor[0]));

        // Melee Damage
        $melee = $this->subject->getBaseStats('melee');
        if ($_ = $this->subject->getField('dmgSchool'))     // magic damage
            $stats['melee'] = Lang::$npc['melee'].Lang::$main['colon'].$_nf($melee[0]).' - '.$_nf($melee[1]).' ('.Lang::$game['sc'][$_].')';
        else                                                // phys. damage
            $stats['melee'] = Lang::$npc['melee'].Lang::$main['colon'].$_nf($melee[0]).' - '.$_nf($melee[1]);

        // Ranged Damage
        $ranged = $this->subject->getBaseStats('ranged');
        $stats['ranged'] = Lang::$npc['ranged'].Lang::$main['colon'].$_nf($ranged[0]).' - '.$_nf($ranged[1]);

        if (in_array($mapType, [1, 2]))                     // Dungeon or Raid
        {
            foreach ($_altIds as $id => $mode)
            {
                foreach ($_altNPCs->iterate() as $dId => $__)
                {
                    if ($dId != $id)
                        continue;

                    $m = Lang::$npc['modes'][$mapType][$mode];

                    // Health
                    $health = $_altNPCs->getBaseStats('health');
                    $modes['health'][] = sprintf($modeRow, $m, $health[0] < $health[1] ? $_nf($health[0]).' - '.$_nf($health[1]) : $_nf($health[0]));

                    // Mana (may be 0)
                    $mana = $_altNPCs->getBaseStats('power');
                    $modes['mana'][] = $mana[0] ? sprintf($modeRow, $m, $mana[0] < $mana[1] ? $_nf($mana[0]).' - '.$_nf($mana[1]) : $_nf($mana[0])) : null;

                    // Armor
                    $armor = $_altNPCs->getBaseStats('armor');
                    $modes['armor'][] = sprintf($modeRow, $m, $armor[0] < $armor[1] ? $_nf($armor[0]).' - '.$_nf($armor[1]) : $_nf($armor[0]));

                    // Melee Damage
                    $melee = $_altNPCs->getBaseStats('melee');
                    if ($_ = $_altNPCs->getField('dmgSchool'))  // magic damage
                        $modes['melee'][] = sprintf($modeRow, $m, $_nf($melee[0]).' - '.$_nf($melee[1]).' ('.Lang::$game['sc'][$_].')');
                    else                                        // phys. damage
                        $modes['melee'][] = sprintf($modeRow, $m, $_nf($melee[0]).' - '.$_nf($melee[1]));

                    // Ranged Damage
                    $ranged = $_altNPCs->getBaseStats('ranged');
                    $modes['ranged'][] = sprintf($modeRow, $m, $_nf($ranged[0]).' - '.$_nf($ranged[1]));
                }
            }
        }

        if ($modes)
            foreach ($stats as $k => $v)
                if ($v)
                    $stats[$k] = sprintf($hint, implode('[/tr][tr]', $modes[$k]), $v, $k);

        // < Stats
        if ($stats)
            $infobox[] = Lang::$npc['stats'].($modes ? ' ('.Lang::$npc['modes'][$mapType][0].')' : null).Lang::$main['colon'].'[ul][li]'.implode('[/li][li]', $stats).'[/li][/ul]';

        /****************/
        /* Main Content */
        /****************/

        // get spawns and such

        // consider phaseMasks

        // consider pooled spawns

        // $this->mapper = true,
        $this->infobox      = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
        $this->position     = $position;
        $this->quotes       = $this->getQuotes();
        $this->reputation   = $this->getOnKillRep($_altIds, $mapType);
        $this->redButtons   = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true,
            BUTTON_VIEW3D  => ['type' => TYPE_NPC, 'typeId' => $this->typeId, 'displayId' => $this->subject->getRandomModelId()]
        );

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: SAI
            // hmm, how should this loot like

        // tab: abilities / tab_controlledabilities (dep: VehicleId)
        // SMART_SCRIPT_TYPE_CREATURE = 0; SMART_ACTION_CAST = 11; SMART_ACTION_ADD_AURA = 75; SMART_ACTION_INVOKER_CAST = 85; SMART_ACTION_CROSS_CAST = 86
        $smartSpells = DB::Aowow()->selectCol('SELECT action_param1 FROM smart_scripts WHERE source_type = 0 AND action_type IN (11, 75, 85, 86) AND entryOrGUID = ?d', $this->typeId);
        $tplSpells   = [];
        $conditions  = ['OR'];

        for ($i = 1; $i < 9; $i++)
            if ($_ = $this->subject->getField('spell'.$i))
                $tplSpells[] = $_;

        if ($tplSpells)
            $conditions[] = ['id', $tplSpells];

        if ($smartSpells)
            $conditions[] = ['id', $smartSpells];

        if ($tplSpells || $smartSpells)
        {
            $abilities = new SpellList($conditions);
            if (!$abilities->error)
            {
                $this->extendGlobalData($abilities->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                $normal    = $abilities->getListviewData();
                $controled = [];

                if ($this->subject->getField('vehicleId'))  // not quite right. All seats should be checked for allowed-to-cast-flag-something
                {
                    foreach ($normal as $id => $values)
                    {
                        if (in_array($id, $smartSpells))
                            continue;

                        $controled[$id] = $values;
                        unset($normal[$id]);
                    }
                }

                if ($normal)
                    $this->lvTabs[] = array(
                        'file'   => 'spell',
                        'data'   => $normal,
                        'params' => array(
                            'name' => '$LANG.tab_abilities',
                            'id'   => 'abilities'
                        )
                    );

                if ($controled)
                    $this->lvTabs[] = array(
                        'file'   => 'spell',
                        'data'   => $controled,
                        'params' => array(
                            'name' => '$LANG.tab_controlledabilities',
                            'id'   => 'controlled-abilities'
                        )
                    );
            }
        }

        // tab: summoned by
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', 28], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', 28], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', 28], ['effect3MiscValue', $this->typeId]]
        );

        $summoned = new SpellList($conditions);
        if (!$summoned->error)
        {
            $this->extendGlobalData($summoned->getJSGlobals());

            $this->lvTabs[] = array(
                'file'   => 'spell',
                'data'   => $summoned->getListviewData(),
                'params' => array(
                    'name'      => '$LANG.tab_summonedby',
                    'id'        => 'summoned-by'
                )
            );
        }

        // tab: teaches
        if ($this->subject->getField('npcflag') & NPC_FLAG_TRAINER)
        {
            $teachQuery = '
                SELECT    IFNULL(t2.spell, t1.spell) AS ARRAY_KEY,
                          IFNULL(t2.spellcost, t1.spellcost) AS cost,
                          IFNULL(t2.reqskill, t1.reqskill) AS reqSkillId,
                          IFNULL(t2.reqskillvalue, t1.reqskillvalue) AS reqSkillValue,
                          IFNULL(t2.reqlevel, t1.reqlevel) AS reqLevel
                FROM      npc_trainer t1
                LEFT JOIN npc_trainer t2 ON t2.entry = IF(t1.spell < 0, -t1.spell, null)
                WHERE     t1.entry = ?d
            ';

            if ($tSpells = DB::Aowow()->select($teachQuery, $this->typeId))
            {
                $teaches = new SpellList(array(['id', array_keys($tSpells)]));
                if (!$teaches->error)
                {
                    $this->extendGlobalData($teaches->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                    $data = $teaches->getListviewData();

                    $extra = [];
                    foreach ($tSpells as $sId => $train)
                    {
                        if (empty($data[$sId]))
                            continue;

                        if ($_ = $train['reqSkillId'])
                        {
                            $this->extendGlobalIds(TYPE_SKILL, $_);
                            if (!isset($extra[0]))
                                $extra[0] = 'Listview.extraCols.condition';

                            $data[$sId]['condition'][] = ['type' => TYPE_SKILL, 'typeId' => $_, 'status' => 1, 'reqSkillLvl' => $train['reqSkillValue']];
                        }

                        if ($_ = $train['reqLevel'])
                        {
                            if (!isset($extra[1]))
                                $extra[1] = "Listview.funcBox.createSimpleCol('reqLevel', LANG.tooltip_reqlevel, '7%', 'reqLevel')";

                            $data[$sId]['reqLevel'] = $_;
                        }

                        if ($_ = $train['cost'])
                            $data[$sId]['trainingcost'] = $_;
                    }

                    $this->lvTabs[] = array(
                        'file'   => 'spell',
                        'data'   => $data,
                        'params' => array(
                            'name'        => '$LANG.tab_teaches',
                            'id'          => 'teaches',
                            'visibleCols' => "$['trainingcost']",
                            'extraCols'   => $extra ? '$['.implode(', ', $extra).']' : null
                        )
                    );
                }
            }
            else
                Util::addNote(U_GROUP_EMPLOYEE, 'NPC '.$this->typeId.' is flagged as trainer, but doesn\'t have any spells set');
        }

        // tab: sells
        if ($sells = DB::Aowow()->selectCol('SELECT item FROM npc_vendor nv  WHERE entry = ?d UNION SELECT item FROM game_event_npc_vendor genv JOIN creature c ON genv.guid = c.guid WHERE c.id = ?d', $this->typeId, $this->typeId))
        {
            $soldItems = new ItemList(array(['id', $sells]));
            if (!$soldItems->error)
            {
                $extraCols = ["Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", 'Listview.extraCols.cost'];
                if ($soldItems->hasSetFields(['condition']))
                    $extraCols[] = 'Listview.extraCols.condition';

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $soldItems->getListviewData(ITEMINFO_VENDOR, [TYPE_NPC => $this->typeId]),
                    'params' => array(
                        'name'      => '$LANG.tab_sells',
                        'id'        => 'currency-for',
                        'extraCols' => '$['.implode(', ', $extraCols).']'
                    )
                );

                $this->extendGlobalData($soldItems->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tabs: this creature contains..
        $skinTab = ['tab_skinning', 'skinned-from'];
        if ($_typeFlags & NPC_TYPEFLAG_HERBLOOT)
            $skinTab = ['tab_gatheredfromnpc', 'gathered-from-npc'];
        else if ($_typeFlags & NPC_TYPEFLAG_MININGLOOT)
            $skinTab = ['tab_minedfromnpc', 'mined-from-npc'];
        else if ($_typeFlags & NPC_TYPEFLAG_ENGINEERLOOT)
            $skinTab = ['tab_salvagedfrom', 'salvaged-from-npc'];

    /*
            extraCols: [Listview.extraCols.count, Listview.extraCols.percent, Listview.extraCols.mode],
            _totalCount: 22531,
            computeDataFunc: Listview.funcBox.initLootTable,
            onAfterCreate: Listview.funcBox.addModeIndicator,

            modes:{"mode":1,"1":{"count":4408,"outof":16013},"4":{"count":4408,"outof":22531}}
    */

        $sourceFor = array(
             [LOOT_CREATURE,    $this->subject->getField('lootId'),           '$LANG.tab_drops',         'drops',         ['Listview.extraCols.percent'], []                          , []],
             [LOOT_PICKPOCKET,  $this->subject->getField('pickpocketLootId'), '$LANG.tab_pickpocketing', 'pickpocketing', ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []],
             [LOOT_SKINNING,    $this->subject->getField('skinLootId'),       '$LANG.'.$skinTab[0],      $skinTab[1],     ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []]
        );

        // temp: manually add loot for difficulty-versions
        $langref = array(
            "-2" => '$LANG.tab_heroic',
            "-1" => '$LANG.tab_normal',
               1 => '$$WH.sprintf(LANG.tab_normalX, 10)',
               2 => '$$WH.sprintf(LANG.tab_normalX, 25)',
               3 => '$$WH.sprintf(LANG.tab_heroicX, 10)',
               4 => '$$WH.sprintf(LANG.tab_heroicX, 25)'
        );

        if ($_altIds)
        {
            $sourceFor[0][2] = $langref[1];
            foreach ($_altNPCs->iterate() as $id => $__)
            {
                $mode = $_altIds[$id];
                array_splice($sourceFor, 1, 0, [[LOOT_CREATURE, $_altNPCs->getField('lootId'), $langref[$mode + 1], 'drops-'.$mode, ['Listview.extraCols.percent'], [], []]]);
            }
        }

        $reqQuest = [];
        foreach ($sourceFor as $sf)
        {
            $creatureLoot = new Loot();
            if ($creatureLoot->getByContainer($sf[0], $sf[1]))
            {
                if ($_ = $creatureLoot->extraCols)
                    $sf[4] = array_merge($sf[4], $_);

                $this->extendGlobalData($creatureLoot->jsGlobals);

                foreach ($creatureLoot->iterate() as &$lv)
                {
                    if (!$lv['quest'])
                        continue;

                    $sf[4][] = 'Listview.extraCols.condition';
                    $reqQuest[$lv['id']] = 0;
                    $lv['condition'][] = ['type' => TYPE_QUEST, 'typeId' => &$reqQuest[$lv['id']], 'status' => 1];
                }

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $creatureLoot->getResult(),
                    'params' => array(
                        'name'        => $sf[2],
                        'id'          => $sf[3],
                        'extraCols'   => $sf[4] ? "$[".implode(', ', array_unique($sf[4]))."]" : null,
                        'hiddenCols'  => $sf[5] ? "$".json_encode($sf[5]) : null,
                        'visibleCols' => $sf[6] ? '$'.json_encode($sf[6]) : null,
                        'sort'        => "$['-percent', 'name']",
                    )
                );
            }
        }

        if ($reqIds = array_keys($reqQuest))                // apply quest-conditions as back-reference
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

        // tab: starts quest
        // tab: ends quest
        $startEnd = new QuestList(array(['qse.type', TYPE_NPC], ['qse.typeId', $this->typeId]));
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
            {
                $this->lvTabs[] = array(
                    'file'   => 'quest',
                    'data'   => $_[0],
                    'params' => array(
                        'name' => '$LANG.tab_starts',
                        'id'   => 'starts'
                    )
                );
            }

            if ($_[1])
            {
                $this->lvTabs[] = array(
                    'file'   => 'quest',
                    'data'   => $_[1],
                    'params' => array(
                        'name' => '$LANG.tab_ends',
                        'id'   => 'ends'
                    )
                );
            }
        }

        // tab: objective of quest
        $conditions = array(
            'OR',
            ['AND', ['reqNpcOrGo1', $this->typeId], ['reqNpcOrGoCount1', 0, '>']],
            ['AND', ['reqNpcOrGo2', $this->typeId], ['reqNpcOrGoCount2', 0, '>']],
            ['AND', ['reqNpcOrGo3', $this->typeId], ['reqNpcOrGoCount3', 0, '>']],
            ['AND', ['reqNpcOrGo4', $this->typeId], ['reqNpcOrGoCount4', 0, '>']],
        );

        $objectiveOf = new QuestList($conditions);
        if (!$objectiveOf->error)
        {
            $this->extendGlobalData($objectiveOf->getJSGlobals());

            $this->lvTabs[] = array(
                'file'   => 'quest',
                'data'   => $objectiveOf->getListviewData(),
                'params' => array(
                    'name' => '$LANG.tab_objectiveof',
                    'id'   => 'objective-of'
                )
            );
        }

        // tab: criteria of [ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE_TYPE have no data set to check for]
        $conditions = array(
            ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE, ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE]],
            ['ac.value1', $this->typeId]
        );

        $crtOf = new AchievementList($conditions);
        if (!$crtOf->error)
        {
            $this->extendGlobalData($crtOf->getJSGlobals());

            $this->lvTabs[] = array(
                'file'   => 'achievement',
                'data'   => $crtOf->getListviewData(),
                'params' => array(
                    'name' => '$LANG.tab_criteriaof',
                    'id'   => 'criteria-of'
                )
            );
        }
    }

    protected function generateTooltip($asError = false)
    {
        if ($asError)
            return '$WowheadPower.registerNpc('.$this->typeId.', '.User::$localeId.', {})';

        $s = $this->subject->getSpawns(true);

        $x  = '$WowheadPower.registerNpc('.$this->typeId.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($this->subject->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($this->subject->renderTooltip())."',\n";
        // $x .= "\tmap: ".($s ? '{zone: '.$s[0].', coords: {0:'.json_encode($s[1], JSON_NUMERIC_CHECK).'}}' : '{}')."\n";
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

    public function notFound($typeStr)
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::notFound($typeStr);

        header('Content-type: application/x-javascript; charset=utf-8');
        echo $this->generateTooltip(true);
        exit();
    }

    private function getRepForId($entries, &$spillover)
    {
        $result = [];
        $q = 'SELECT f.id, f.parentFactionId, cor.creature_id AS npc,
                  IF(f.id = RewOnKillRepFaction1, RewOnKillRepValue1, RewOnKillRepValue2) AS qty,
                  IF(f.id = RewOnKillRepFaction1, MaxStanding1, MaxStanding2)             AS maxRank,
                  IF(f.id = RewOnKillRepFaction1, isTeamAward1, isTeamAward2)             AS spillover
              FROM ?_factions f JOIN creature_onkill_reputation cor ON f.Id = cor.RewOnKillRepFaction1 OR f.Id = cor.RewOnKillRepFaction2 WHERE cor.creature_id IN (?a)';

        foreach (DB::Aowow()->select($q, (array)$entries) as $_)
        {
            $set = array(
                'id'   => $_['id'],
                'qty'  => $_['qty'],
                'name' => FactionList::getName($_['id']),   // << this sucks .. maybe format this whole table with markdown and add name via globals?
                'npc'  => $_['npc'],
                'cap'  => $_['maxRank'] && $_['maxRank'] < REP_EXALTED ? Lang::$game['rep'][$_['maxRank']] : null
            );

            if ($_['spillover'])
            {
                $spillover[$_['parentFactionId']] = [intVal($_['qty'] / 2), $_['maxRank']];
                $set['spillover'] = $_['parentFactionId'];
            }

            $result[] = $set;
        }

        return $result;
    }

    private function getOnKillRep($dummyIds, $mapType)
    {
        $spilledParents = [];
        $reputation     = [];

        // base NPC
        if ($base = $this->getRepForId($this->typeId, $spilledParents))
            $reputation[] = [Lang::$npc['modes'][1][0], $base];

        // difficulty dummys
        if ($dummyIds)
        {
            $alt = [];
            $rep = $this->getRepForId(array_keys($dummyIds), $spilledParents);

            // order by difficulty
            foreach ($rep as $r)
                $alt[$dummyIds[$r['npc']]][] = $r;

            // apply by difficulty
            foreach ($alt as $mode => $dat)
                $reputation[] = [Lang::$npc['modes'][$mapType][$mode], $dat];
        }

        // get spillover factions and apply
        if ($spilledParents)
        {
            $spilled = new FactionList(array(['parentFactionId', array_keys($spilledParents)]));

            foreach($reputation as &$sets)
            {
                foreach ($sets[1] as &$row)
                {
                    if (empty($row['spillover']))
                        continue;

                    foreach ($spilled->iterate() as $spId => $__)
                    {
                        // find parent
                        if ($spilled->getField('parentFactionId') != $row['spillover'])
                            continue;

                        // don't readd parent
                        if ($row['id'] == $spId)
                            continue;

                        $spMax = $spilledParents[$row['spillover']][1];

                        $sets[1][] = array(
                            'id'   => $spId,
                            'qty'  => $spilledParents[$row['spillover']][0],
                            'name' => $spilled->getField('name', true),
                            'cap'  => $spMax && $spMax < REP_EXALTED ? Lang::$game['rep'][$spMax] : null
                        );
                    }
                }
            }
        }

        return $reputation;
    }

    private function getQuotes()
    {
        $nQuotes    = 0;
        $quotes     = [];
        $quoteQuery = '
            SELECT
                ct.groupid AS ARRAY_KEY, ct.id as ARRAY_KEY2, ct.`type`,
                IFNULL(bct.`Language`, ct.`language`) AS lang,
                IFNULL(bct.MaleText, IFNULL(bct.FemaleText, ct.`text`)) AS text_loc0,
                IFNULL(lbct.MaleText_loc2, IFNULL(lbct.FemaleText_loc2, lct.text_loc2)) AS text_loc2,
                IFNULL(lbct.MaleText_loc3, IFNULL(lbct.FemaleText_loc3, lct.text_loc3)) AS text_loc3,
                IFNULL(lbct.MaleText_loc6, IFNULL(lbct.FemaleText_loc6, lct.text_loc6)) AS text_loc6,
                IFNULL(lbct.MaleText_loc8, IFNULL(lbct.FemaleText_loc8, lct.text_loc8)) AS text_loc8
            FROM
                creature_text ct
            LEFT JOIN
                locales_creature_text lct ON ct.entry = lct.entry AND ct.groupid = lct.groupid AND ct.id = lct.id
            LEFT JOIN
                broadcast_text bct ON ct.BroadcastTextId = bct.ID
            LEFT JOIN
                locales_broadcast_text lbct ON ct.BroadcastTextId = lbct.ID
            WHERE
                ct.entry = ?d';

        foreach (DB::Aowow()->select($quoteQuery, $this->typeId) as $text)
        {
            $group = [];
            foreach ($text as $t)
            {
                // fixup .. either set %s for emotes or dont >.<
                $text = Util::localizedString($t, 'text');
                if (in_array($t['type'], [2, 16]) && strpos($text, '%s') === false)
                    $text = '%s '.$text;

                $line = array(
                    'type' => 2,                            // [type: 0, 12] say: yellow-ish
                    'lang'  => !empty($t['language']) ? Lang::$game['languages'][$t['language']] : null,
                    'text'  => sprintf(Util::parseHtmlText(htmlentities($text)), $this->name),
                );

                switch ($t['type'])
                {
                    case  1:                                // yell:
                    case 14: $line['type'] = 1; break;      // - dark red
                    case  2:                                // emote:
                    case 16:                                // "
                    case  3:                                // boss emote:
                    case 41: $line['type'] = 4; break;      // - orange
                    case  4:                                // whisper:
                    case 15:                                // "
                    case  5:                                // boss whisper:
                    case 42: $line['type'] = 3; break;      // - pink-ish
                }

                $nQuotes++;
                $group[] = $line;
            }
            $quotes[] = $group;
        }

        return [$quotes, $nQuotes];
    }
}


?>
