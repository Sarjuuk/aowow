<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 4: NPC      g_initPath()
//  tabId 0: Database g_initHeader()
class NpcPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_NPC;
    protected $typeId        = 0;
    protected $tpl           = 'npc';
    protected $path          = [0, 4];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = ['swfobject.js'];
    protected $css           = [['path' => 'Profiler.css']];

    private   $soundIds      = [];
    private   $powerTpl      = '$WowheadPower.registerNpc(%d, %d, %s);';

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new CreatureList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('npc'), Lang::npc('notFound'));

        $this->name    = Util::htmlEscape($this->subject->getField('name', true));
        $this->subname = Util::htmlEscape($this->subject->getField('subname', true));
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('type');

        if ($_ = $this->subject->getField('family'))
            $this->path[] = $_;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('npc')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $_typeFlags  = $this->subject->getField('typeFlags');
        $_altIds     = [];
        $_altNPCs    = null;
        $placeholder = null;
        $accessory   = [];

        // difficulty entries of self
        if ($this->subject->getField('cuFlags') & NPC_CU_DIFFICULTY_DUMMY)
            $placeholder = [$this->subject->getField('parentId'), $this->subject->getField('parent', true)];
        else
        {
            for ($i = 1; $i < 4; $i++)
                if ($_ = $this->subject->getField('difficultyEntry'.$i))
                    $_altIds[$_] = $i;

            if ($_altIds)
                $_altNPCs = new CreatureList(array(['id', array_keys($_altIds)]));
        }

        if ($_ = DB::World()->selectCol('SELECT DISTINCT entry FROM vehicle_template_accessory WHERE accessory_entry = ?d', $this->typeId))
        {
            $vehicles = new CreatureList(array(['id', $_]));
            foreach ($vehicles->iterate() as $id => $__)
                $accessory[] = [$id, $vehicles->getField('name', true)];
        }

        // try to determine, if it's spawned in a dungeon or raid (shaky at best, if spawned by script)
        $mapType = 0;
        if ($maps = DB::Aowow()->selectCol('SELECT DISTINCT areaId from ?_spawns WHERE type = ?d AND typeId = ?d', TYPE_NPC, $this->typeId))
        {
            if (count($maps) == 1)                          // should only exist in one instance
            {
                switch (DB::Aowow()->selectCell('SELECT `type` FROM ?_zones WHERE id = ?d', $maps[0]))
                {
                    case 2:
                    case 5: $mapType = 1; break;
                    case 3:
                    case 7:
                    case 8: $mapType = 2; break;
                }
            }
        }
        else if ($_altIds)                                  // not spawned, but has difficultyDummies
        {
            if (count($_altIds) > 1)                        // 3 or more version -> definitly raid (10/25 + hc)
                $mapType = 2;
            else                                            // 2 versions; may be Heroic (use this), but may also be 10/25-raid
                $mapType = 1;
        }





        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Event (ignore events, where the object only gets removed)
        if ($_ = DB::World()->selectCol('SELECT DISTINCT ge.eventEntry FROM game_event ge, game_event_creature gec, creature c WHERE ge.eventEntry = gec.eventEntry AND c.guid = gec.guid AND c.id = ?d', $this->typeId))
        {
            $this->extendGlobalIds(TYPE_WORLDEVENT, ...$_);
            $ev = [];
            foreach ($_ as $i => $e)
                $ev[] = ($i % 2 ? '[br]' : ' ') . '[event='.$e.']';

            $infobox[] = Util::ucFirst(Lang::game('eventShort')).Lang::main('colon').implode(',', $ev);
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

        $infobox[] = Lang::game('level').Lang::main('colon').$level;

        // Classification
        if ($_ = $this->subject->getField('rank'))          //  != NPC_RANK_NORMAL
        {
            $str = $this->subject->isBoss() ? '[span class=icon-boss]'.Lang::npc('rank', $_).'[/span]' : Lang::npc('rank', $_);
            $infobox[] = Lang::npc('classification').Lang::main('colon').$str;
        }

        // Reaction
        $_ = function ($r)
        {
            if ($r == 1)  return 2;
            if ($r == -1) return 10;
            return;
        };
        $infobox[] = Lang::npc('react').Lang::main('colon').'[color=q'.$_($this->subject->getField('A')).']A[/color] [color=q'.$_($this->subject->getField('H')).']H[/color]';

        // Faction
        $this->extendGlobalIds(TYPE_FACTION, $this->subject->getField('factionId'));
        $infobox[] = Util::ucFirst(Lang::game('faction')).Lang::main('colon').'[faction='.$this->subject->getField('factionId').']';

        // Tameable
        if ($_typeFlags & 0x1)
            if ($_ = $this->subject->getField('family'))
                $infobox[] = sprintf(Lang::npc('tameable'), '[url=pet='.$_.']'.Lang::game('fa', $_).'[/url]');

        // Wealth
        if ($_ = intVal(($this->subject->getField('minGold') + $this->subject->getField('maxGold')) / 2))
            $infobox[] = Lang::npc('worth').Lang::main('colon').'[tooltip=tooltip_avgmoneydropped][money='.$_.'][/tooltip]';

        // is Vehicle
        if ($this->subject->getField('vehicleId'))
            $infobox[] = Lang::npc('vehicle');

        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            // AI
            if ($_ = $this->subject->getField('scriptName'))
                $infobox[] = 'Script'.Lang::main('colon').$_;
            else if ($_ = $this->subject->getField('aiName'))
                $infobox[] = 'AI'.Lang::main('colon').$_;

            // Mechanic immune
            if ($immuneMask = $this->subject->getField('mechanicImmuneMask'))
            {
                $buff = [];
                for ($i = 0; $i < 31; $i++)
                    if ($immuneMask & (1 << $i))
                        $buff[] = (!fMod(count($buff), 3) ? "\n" : null).'[url=?spells&filter=me='.($i + 1).']'.Lang::game('me', $i + 1).'[/url]';

                $infobox[] = 'Not affected by mechanic'.Lang::main('colon').implode(', ', $buff);
            }

            // extra flags
            if ($flagsExtra = $this->subject->getField('flagsExtra'))
            {
                $buff = [];
                if ($flagsExtra & 0x000001)
                    $buff[] = 'Binds attacker to instance on death';
                if ($flagsExtra & 0x000002)
                    $buff[] = "[tooltip name=civilian]- does not aggro\n- death costs Honor[/tooltip][span class=tip tooltip=civilian]Civilian[/span]";
                if ($flagsExtra & 0x000004)
                    $buff[] = 'Cannot parry';
                if ($flagsExtra & 0x000008)
                    $buff[] = 'Has no parry haste';
                if ($flagsExtra & 0x000010)
                    $buff[] = 'Cannot block';
                if ($flagsExtra & 0x000020)
                    $buff[] = 'Cannot deal Crushing Blows';
                if ($flagsExtra & 0x000040)
                    $buff[] = 'Rewards no experience';
                if ($flagsExtra & 0x000080)
                    $buff[] = 'Trigger creature';
                if ($flagsExtra & 0x000100)
                    $buff[] = 'Immune to Taunt';
                if ($flagsExtra & 0x008000)
                    $buff[] = "[tooltip name=guard]- engages PvP attackers\n- ignores enemy stealth, invisibility and Feign Death[/tooltip][span class=tip tooltip=guard]Guard[/span]";
                if ($flagsExtra & 0x020000)
                    $buff[] = 'Cannot deal Critical Hits';
                if ($flagsExtra & 0x040000)
                    $buff[] = 'Attacker does not gain weapon skill';
                if ($flagsExtra & 0x080000)
                    $buff[] = 'Taunt has diminishing returns';
                if ($flagsExtra & 0x100000)
                    $buff[] = 'Is subject to diminishing returns';

                if ($buff)
                    $infobox[] = 'Extra Flags'.Lang::main('colon').'[ul][li]'.implode('[/li][li]', $buff).'[/li][/ul]';
            }

            // Mode dummy references
            if ($_altNPCs)
            {
                $this->extendGlobalData($_altNPCs->getJSGlobals());
                $buff = 'Difficulty Versions'.Lang::main('colon').'[ul]';
                foreach ($_altNPCs->iterate() as $id => $__)
                    $buff .= '[li][npc='.$id.'][/li]';
                $infobox[] = $buff.'[/ul]';
            }
        }

        // > Stats
        $stats   = [];
        $modes   = [];                                      // get difficulty versions if set
        $hint    = '[tooltip name=%3$s][table cellspacing=10][tr]%1s[/tr][/table][/tooltip][span class=tip tooltip=%3$s]%2s[/span]';
        $modeRow = '[tr][td]%s&nbsp;&nbsp;[/td][td]%s[/td][/tr]';
        // Health
        $health = $this->subject->getBaseStats('health');
        $stats['health'] = Util::ucFirst(Lang::spell('powerTypes', -2)).Lang::main('colon').($health[0] < $health[1] ? Lang::nf($health[0]).' - '.Lang::nf($health[1]) : Lang::nf($health[0]));

        // Mana (may be 0)
        $mana = $this->subject->getBaseStats('power');
        $stats['mana'] = $mana[0] ? Lang::spell('powerTypes', 0).Lang::main('colon').($mana[0] < $mana[1] ? Lang::nf($mana[0]).' - '.Lang::nf($mana[1]) : Lang::nf($mana[0])) : null;

        // Armor
        $armor = $this->subject->getBaseStats('armor');
        $stats['armor'] = Lang::npc('armor').Lang::main('colon').($armor[0] < $armor[1] ? Lang::nf($armor[0]).' - '.Lang::nf($armor[1]) : Lang::nf($armor[0]));

        // Resistances
        $resNames = [null, 'hol', 'fir', 'nat', 'fro', 'sha', 'arc'];
        $tmpRes   = [];
        $stats['resistance'] = '';
        foreach ($this->subject->getBaseStats('resistance') as $sc => $amt)
            if ($amt)
                $tmpRes[] = '[span class="moneyschool'.$resNames[$sc].'"]'.$amt.'[/span]';

        if ($tmpRes)
        {
            $stats['resistance'] = Lang::npc('resistances').Lang::main('colon');
            if (count($tmpRes > 3))
                $stats['resistance'] .= implode('&nbsp;', array_slice($tmpRes, 0, 3)).'[br]'.implode('&nbsp;', array_slice($tmpRes, 3));
            else
                $stats['resistance'] .= implode('&nbsp;', $tmpRes);
        }

        // Melee Damage
        $melee = $this->subject->getBaseStats('melee');
        if ($_ = $this->subject->getField('dmgSchool'))     // magic damage
            $stats['melee'] = Lang::npc('melee').Lang::main('colon').Lang::nf($melee[0]).' - '.Lang::nf($melee[1]).' ('.Lang::game('sc', $_).')';
        else                                                // phys. damage
            $stats['melee'] = Lang::npc('melee').Lang::main('colon').Lang::nf($melee[0]).' - '.Lang::nf($melee[1]);

        // Ranged Damage
        $ranged = $this->subject->getBaseStats('ranged');
        $stats['ranged'] = Lang::npc('ranged').Lang::main('colon').Lang::nf($ranged[0]).' - '.Lang::nf($ranged[1]);

        if (in_array($mapType, [1, 2]))                     // Dungeon or Raid
        {
            foreach ($_altIds as $id => $mode)
            {
                foreach ($_altNPCs->iterate() as $dId => $__)
                {
                    if ($dId != $id)
                        continue;

                    $m = Lang::npc('modes', $mapType, $mode);

                    // Health
                    $health = $_altNPCs->getBaseStats('health');
                    $modes['health'][] = sprintf($modeRow, $m, $health[0] < $health[1] ? Lang::nf($health[0]).' - '.Lang::nf($health[1]) : Lang::nf($health[0]));

                    // Mana (may be 0)
                    $mana = $_altNPCs->getBaseStats('power');
                    $modes['mana'][] = $mana[0] ? sprintf($modeRow, $m, $mana[0] < $mana[1] ? Lang::nf($mana[0]).' - '.Lang::nf($mana[1]) : Lang::nf($mana[0])) : null;

                    // Armor
                    $armor = $_altNPCs->getBaseStats('armor');
                    $modes['armor'][] = sprintf($modeRow, $m, $armor[0] < $armor[1] ? Lang::nf($armor[0]).' - '.Lang::nf($armor[1]) : Lang::nf($armor[0]));

                    // Resistances
                    $tmpRes = '';
                    foreach ($_altNPCs->getBaseStats('resistance') as $sc => $amt)
                        $tmpRes .= '[td]'.$amt.'[/td]';

                    if ($tmpRes)
                    {
                        if (!isset($modes['resistance']))   // init table head
                            $modes['resistance'][] = '[td][/td][td][span class="moneyschoolhol"]&nbsp;&nbsp;&nbsp;&nbsp;[/span][/td][td][span class="moneyschoolfir"]&nbsp;&nbsp;&nbsp;&nbsp;[/span][/td][td][span class="moneyschoolnat"]&nbsp;&nbsp;&nbsp;&nbsp;[/span][/td][td][span class="moneyschoolfro"]&nbsp;&nbsp;&nbsp;&nbsp;[/span][/td][td][span class="moneyschoolsha"]&nbsp;&nbsp;&nbsp;&nbsp;[/span][/td][td][span class="moneyschoolarc"][/span][/td]';

                        if (!$stats['resistance'])          // base creature has no resistance. -> display list item.
                            $stats['resistance'] = Lang::npc('resistances').Lang::main('colon').'…';

                        $modes['resistance'][] = '[td]'.$m.'&nbsp;&nbsp;&nbsp;&nbsp;[/td]'.$tmpRes;
                    }

                    // Melee Damage
                    $melee = $_altNPCs->getBaseStats('melee');
                    if ($_ = $_altNPCs->getField('dmgSchool'))  // magic damage
                        $modes['melee'][] = sprintf($modeRow, $m, Lang::nf($melee[0]).' - '.Lang::nf($melee[1]).' ('.Lang::game('sc', $_).')');
                    else                                        // phys. damage
                        $modes['melee'][] = sprintf($modeRow, $m, Lang::nf($melee[0]).' - '.Lang::nf($melee[1]));

                    // Ranged Damage
                    $ranged = $_altNPCs->getBaseStats('ranged');
                    $modes['ranged'][] = sprintf($modeRow, $m, Lang::nf($ranged[0]).' - '.Lang::nf($ranged[1]));
                }
            }
        }

        if ($modes)
            foreach ($stats as $k => $v)
                if ($v)
                    $stats[$k] = sprintf($hint, implode('[/tr][tr]', $modes[$k]), $v, $k);

        // < Stats
        if ($stats)
            $infobox[] = Lang::npc('stats').($modes ? ' ('.Lang::npc('modes', $mapType, 0).')' : null).Lang::main('colon').'[ul][li]'.implode('[/li][li]', $stats).'[/li][/ul]';


        /****************/
        /* Main Content */
        /****************/

        // get spawns and path
        $map = null;
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $map = ['data' => ['parent' => 'mapper-generic'], 'mapperData' => &$spawns];
            foreach ($spawns as $areaId => &$areaData)
                $map['extra'][$areaId] = ZoneList::getName($areaId);
        }

        // smart AI
        $sai = null;
        if ($this->subject->getField('aiName') == 'SmartAI')
        {
            $sai = new SmartAI(SAI_SRC_TYPE_CREATURE, $this->typeId, ['name' => $this->subject->getField('name', true)]);
            if (!$sai->prepare())                           // no smartAI found .. check per guid
            {
                // at least one of many
                $guids = DB::World()->selectCol('SELECT guid FROM creature WHERE id = ?d', $this->typeId);
                while ($_ = array_pop($guids))
                {
                    $sai = new SmartAI(SAI_SRC_TYPE_CREATURE, -$_, ['baseEntry' => $this->typeId, 'name' => $this->subject->getField('name', true), 'title' => ' [small](for GUID: '.$_.')[/small]']);
                    if ($sai->prepare())
                        break;
                }
            }

            if ($sai->prepare())
                $this->extendGlobalData($sai->getJSGlobals());
            else
                trigger_error('Creature has SmartAI set in template but no SmartAI defined.');
        }

        // consider pooled spawns
        $this->map          = $map;
        $this->infobox      = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
        $this->placeholder  = $placeholder;
        $this->accessory    = $accessory;
        $this->quotes       = $this->getQuotes();
        $this->reputation   = $this->getOnKillRep($_altIds, $mapType);
        $this->smartAI      = $sai ? $sai->getMarkdown() : null;
        $this->redButtons   = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_VIEW3D  => ['type' => TYPE_NPC, 'typeId' => $this->typeId, 'displayId' => $this->subject->getRandomModelId()]
        );

        if ($this->subject->getField('humanoid'))
            $this->redButtons[BUTTON_VIEW3D]['humanoid'] = 1;


        /**************/
        /* Extra Tabs */
        /**************/

        // tab: abilities / tab_controlledabilities (dep: VehicleId)
        $tplSpells   = [];
        $smartSpells = [];
        $conditions  = ['OR'];

        for ($i = 1; $i < 9; $i++)
            if ($_ = $this->subject->getField('spell'.$i))
                $tplSpells[] = $_;

        if ($tplSpells)
            $conditions[] = ['id', $tplSpells];

        if ($smartSpells = SmartAI::getSpellCastsForOwner($this->typeId, SAI_SRC_TYPE_CREATURE))
            $conditions[] = ['id', $smartSpells];

        // Pet-Abilities
        if ($_typeFlags & 0x1 && ($_ = $this->subject->getField('family')))
        {
            $skill = 0;
            $mask  = 0x0;
            foreach (Game::$skillLineMask[-1] as $idx => $pair)
            {
                if ($pair[0] != $_)
                    continue;

                $skill = $pair[1];
                $mask  = 1 << $idx;
                break;
            }
            $conditions[] = [
                'AND',
                ['s.typeCat', -3],
                [
                    'OR',
                    ['skillLine1', $skill],
                    ['AND', ['skillLine1', 0, '>'], ['skillLine2OrMask', $skill]],
                    ['AND', ['skillLine1', -1], ['skillLine2OrMask', $mask, '&']]
                ]
            ];
        }

        if (count($conditions) > 1)
        {
            $abilities = new SpellList($conditions);
            if (!$abilities->error)
            {
                $this->extendGlobalData($abilities->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                $controled = $abilities->getListviewData();
                $normal    = [];

                foreach ($controled as $id => $values)
                {
                    if (in_array($id, $smartSpells))
                    {
                        $normal[$id] = $values;
                        if (!in_array($id, $tplSpells))
                            unset($controled[$id]);
                    }
                }

                if ($normal)
                    $this->lvTabs[] = ['spell', array(
                        'data' => array_values($normal),
                        'name' => '$LANG.tab_abilities',
                        'id'   => 'abilities'
                    )];

                if ($controled)
                    $this->lvTabs[] = ['spell', array(
                        'data' => array_values($controled),
                        'name' => '$LANG.tab_controlledabilities',
                        'id'   => 'controlled-abilities'
                    )];
            }
        }

        // tab: summoned by [spell]
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', [28, 56, 112]], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', [28, 56, 112]], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', [28, 56, 112]], ['effect3MiscValue', $this->typeId]]
        );

        $sbSpell = new SpellList($conditions);
        if (!$sbSpell->error)
        {
            $this->extendGlobalData($sbSpell->getJSGlobals());

            $this->lvTabs[] = ['spell', array(
                'data' => array_values($sbSpell->getListviewData()),
                'name' => '$LANG.tab_summonedby',
                'id'   => 'summoned-by-spell'
            )];
        }

        // tab: summoned by [NPC]
        $sb = SmartAI::getOwnerOfNPCSummon($this->typeId);
        if (!empty($sb[TYPE_NPC]))
        {
            $sbNPC = new CreatureList(array(['id', $sb[TYPE_NPC]]));
            if (!$sbNPC->error)
            {
                $this->extendGlobalData($sbNPC->getJSGlobals());

                $this->lvTabs[] = ['creature', array(
                    'data' => array_values($sbNPC->getListviewData()),
                    'name' => '$LANG.tab_summonedby',
                    'id'   => 'summoned-by-npc'
                )];
            }
        }

        // tab: summoned by [Object]
        if (!empty($sb[TYPE_OBJECT]))
        {
            $sbGO = new GameObjectList(array(['id', $sb[TYPE_OBJECT]]));
            if (!$sbGO->error)
            {
                $this->extendGlobalData($sbGO->getJSGlobals());

                $this->lvTabs[] = ['object', array(
                    'data' => array_values($sbGO->getListviewData()),
                    'name' => '$LANG.tab_summonedby',
                    'id'   => 'summoned-by-object'
                )];
            }
        }

        // tab: teaches
        if ($this->subject->getField('npcflag') & NPC_FLAG_TRAINER)
        {
            $teachQuery = '
                SELECT  ts.SpellId AS ARRAY_KEY, ts.MoneyCost AS cost, ts.ReqSkillLine AS reqSkillId, ts.ReqSkillRank AS reqSkillValue, ts.ReqLevel AS reqLevel, ts.ReqAbility1 AS reqSpellId1, ts.reqAbility2 AS reqSpellId2
                FROM    trainer_spell ts
                JOIN    creature_default_trainer cdt ON cdt.TrainerId = ts.TrainerId
                WHERE   cdt.Creatureid = ?d
            ';

            if ($tSpells = DB::World()->select($teachQuery, $this->typeId))
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
                            if (count($data[$sId]['skill']) == 1 && $_ != $data[$sId]['skill'][0])
                            {
                                $this->extendGlobalIds(TYPE_SKILL, $_);
                                if (!isset($extra[0]))
                                    $extra[0] = '$Listview.extraCols.condition';

                                $data[$sId]['condition'][0][$this->typeId][] = [[CND_SKILL, $_, $train['reqSkillValue']]];
                            }
                        }

                        for ($i = 1; $i < 3; $i++)
                        {
                            if ($_ = $train['reqSpellId'.$i])
                            {
                                $this->extendGlobalIds(TYPE_SPELL, $_);
                                if (!isset($extra[0]))
                                    $extra[0] = '$Listview.extraCols.condition';

                                $data[$sId]['condition'][0][$this->typeId][] = [[CND_SPELL, $_]];
                            }
                        }

                        if ($_ = $train['reqLevel'])
                        {
                            if (!isset($extra[1]))
                                $extra[1] = "\$Listview.funcBox.createSimpleCol('reqLevel', LANG.tooltip_reqlevel, '7%', 'reqLevel')";

                            $data[$sId]['reqLevel'] = $_;
                        }

                        if ($_ = $train['cost'])
                            $data[$sId]['trainingcost'] = $_;
                    }

                    $tabData = array(
                        'data'        => array_values($data),
                        'name'        => '$LANG.tab_teaches',
                        'id'          => 'teaches',
                        'visibleCols' => ['trainingcost']
                    );

                    if ($extra)
                        $tabData['extraCols'] = array_values($extra);

                    $this->lvTabs[] = ['spell', $tabData];
                }
            }
            else
                trigger_error('NPC '.$this->typeId.' is flagged as trainer, but doesn\'t have any spells set', E_USER_WARNING);
        }

        // tab: sells
        if ($sells = DB::World()->selectCol('SELECT item FROM npc_vendor nv WHERE entry = ?d UNION SELECT item FROM game_event_npc_vendor genv JOIN creature c ON genv.guid = c.guid WHERE c.id = ?d', $this->typeId, $this->typeId))
        {
            $soldItems = new ItemList(array(['id', $sells]));
            if (!$soldItems->error)
            {
                $extraCols = ["\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost'];
                if ($soldItems->hasSetFields(['condition']))
                    $extraCols[] = '$Listview.extraCols.condition';

                $lvData = $soldItems->getListviewData(ITEMINFO_VENDOR, [TYPE_NPC => [$this->typeId]]);

                $sc = Util::getServerConditions(CND_SRC_NPC_VENDOR, $this->typeId);
                if (!empty($sc[0]))
                {
                    $this->extendGlobalData($sc[1]);

                    $extraCols[] = '$Listview.extraCols.condition';

                    foreach ($lvData as $id => &$row)
                        foreach ($sc[0] as $srcType => $cndData)
                        if (!empty($cndData[$id.':'.$this->typeId]))
                            $row['condition'][0][$id.':'.$this->typeId] = $cndData[$id.':'.$this->typeId];
                }

                $this->lvTabs[] = ['item', array(
                    'data'      => array_values($lvData),
                    'name'      => '$LANG.tab_sells',
                    'id'        => 'currency-for',
                    'extraCols' => array_unique($extraCols)
                )];

                $this->extendGlobalData($soldItems->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tabs: this creature contains..
        $skinTab = ['tab_skinning', 'skinning', SKILL_SKINNING];
        if ($_typeFlags & NPC_TYPEFLAG_HERBLOOT)
            $skinTab = ['tab_herbalism', 'herbalism', SKILL_HERBALISM];
        else if ($_typeFlags & NPC_TYPEFLAG_MININGLOOT)
            $skinTab = ['tab_mining', 'mining', SKILL_MINING];
        else if ($_typeFlags & NPC_TYPEFLAG_ENGINEERLOOT)
            $skinTab = ['tab_engineering', 'engineering', SKILL_ENGINEERING];

    /*
            extraCols: [Listview.extraCols.count, Listview.extraCols.percent, Listview.extraCols.mode],
            _totalCount: 22531,
            computeDataFunc: Listview.funcBox.initLootTable,
            onAfterCreate: Listview.funcBox.addModeIndicator,

            modes:{"mode":1,"1":{"count":4408,"outof":16013},"4":{"count":4408,"outof":22531}}
    */

        $sourceFor = array(
             [LOOT_CREATURE,   $this->subject->getField('lootId'),           '$LANG.tab_drops',         'drops',         []                          ],
             [LOOT_PICKPOCKET, $this->subject->getField('pickpocketLootId'), '$LANG.tab_pickpocketing', 'pickpocketing', ['side', 'slot', 'reqlevel']],
             [LOOT_SKINNING,   $this->subject->getField('skinLootId'),       '$LANG.'.$skinTab[0],      $skinTab[1],     ['side', 'slot', 'reqlevel']]
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
            $sourceFor[0][2] = $mapType == 1 ? $langref[-1] : $langref[1];
            foreach ($_altNPCs->iterate() as $id => $__)
            {
                $mode = ($_altIds[$id] + 1) * ($mapType == 1 ? -1 : 1);
                if ($lootGO = DB::Aowow()->selectRow('SELECT o.id, o.lootId, o.name_loc0, o.name_loc2, o.name_loc3, o.name_loc6, o.name_loc8 FROM ?_loot_link l JOIN ?_objects o ON o.id = l.objectId WHERE l.npcId = ?d', $id))
                    array_splice($sourceFor, 1, 0, [[LOOT_GAMEOBJECT, $lootGO['lootId'], $langref[$mode], 'drops-object-'.abs($mode), [], 'note' => '$$WH.sprintf(LANG.lvnote_npcobjectsource, '.$lootGO['id'].', "'.Util::localizedString($lootGO, 'name').'")']]);
                if ($lootId = $_altNPCs->getField('lootId'))
                    array_splice($sourceFor, 1, 0, [[LOOT_CREATURE,   $lootId,           $langref[$mode], 'drops-'.abs($mode), []]]);
            }
        }

        if ($lootGOs = DB::Aowow()->select('SELECT o.id, IF(npcId < 0, 1, 0) AS modeDummy, o.lootId, o.name_loc0, o.name_loc2, o.name_loc3, o.name_loc6, o.name_loc8 FROM ?_loot_link l JOIN ?_objects o ON o.id = l.objectId WHERE ABS(l.npcId) = ?d', $this->typeId))
            foreach ($lootGOs as $idx => $lgo)
                array_splice($sourceFor, 1, 0, [[LOOT_GAMEOBJECT, $lgo['lootId'], $mapType ? $langref[($mapType == 1 ? -1 : 1) + ($lgo['modeDummy'] ? 1 : 0)] : '$LANG.tab_drops', 'drops-object-'.$idx, [], 'note' => '$$WH.sprintf(LANG.lvnote_npcobjectsource, '.$lgo['id'].', "'.Util::localizedString($lgo, 'name').'")']]);

        $reqQuest = [];
        foreach ($sourceFor as $sf)
        {
            $creatureLoot = new Loot();
            if ($creatureLoot->getByContainer($sf[0], $sf[1]))
            {
                $extraCols   = $creatureLoot->extraCols;
                $extraCols[] = '$Listview.extraCols.percent';

                $this->extendGlobalData($creatureLoot->jsGlobals);

                foreach ($creatureLoot->iterate() as &$lv)
                {
                    if (!$lv['quest'])
                        continue;

                    $extraCols[] = '$Listview.extraCols.condition';
                    $reqQuest[$lv['id']] = 0;
                    $lv['condition'][0][$this->typeId][] = [[CND_QUESTTAKEN, &$reqQuest[$lv['id']]]];
                }

                $tabData = array(
                    'data'      => array_values($creatureLoot->getResult()),
                    'name'      => $sf[2],
                    'id'        => $sf[3],
                    'extraCols' => array_unique($extraCols),
                    'sort'      => ['-percent', 'name'],
                );

                if (!empty($sf['note']))
                    $tabData['note'] = $sf['note'];
                else if ($sf[0] == LOOT_SKINNING)
                    $tabData['note'] = '<b>'.Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill($skinTab[2], $this->subject->getField('maxLevel') * 5), true).'</b>';

                if ($sf[4])
                    $tabData['hiddenCols'] = $sf[4];

                $this->lvTabs[] = ['item', $tabData];
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

            $this->lvTabs[] = ['quest', array(
                'data' => array_values($objectiveOf->getListviewData()),
                'name' => '$LANG.tab_objectiveof',
                'id'   => 'objective-of'
            )];
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

            $this->lvTabs[] = ['achievement', array(
                'data' => array_values($crtOf->getListviewData()),
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            )];
        }

        // tab: passengers
        if ($_ = DB::World()->selectCol('SELECT accessory_entry AS ARRAY_KEY, GROUP_CONCAT(seat_id) FROM vehicle_template_accessory WHERE entry = ?d GROUP BY accessory_entry', $this->typeId))
        {
            $passengers = new CreatureList(array(['id', array_keys($_)]));
            if (!$passengers->error)
            {
                $data = $passengers->getListviewData();

                if (User::isInGroup(U_GROUP_STAFF))
                    foreach ($data as $id => &$d)
                        $d['seat'] = str_replace(',', ', ', $_[$id]);

                $this->extendGlobalData($passengers->getJSGlobals(GLOBALINFO_SELF));

                $tabData = array(
                    'data' => array_values($data),
                    'name' => Lang::npc('accessory'),
                    'id'   => 'accessory'
                );

                if (User::isInGroup(U_GROUP_STAFF))
                    $tabData['extraCols'] = ["\$Listview.funcBox.createSimpleCol('seat', '".Lang::npc('seat')."', '10%', 'seat')"];

                $this->lvTabs[] = ['creature', $tabData];
            }
        }

        /* tab sounds:
            * activity sounds => CreatureDisplayInfo.dbc => (CreatureModelData.dbc => ) CreatureSoundData.dbc
            * AI => smart_scripts
            * Dialogue VO => creature_text
            * onClick VO => CreatureDisplayInfo.dbc => NPCSounds.dbc
        */
        $this->soundIds = array_merge($this->soundIds, SmartAI::getSoundsPlayedForOwner($this->typeId, SAI_SRC_TYPE_CREATURE));

        // up to 4 possible displayIds .. for the love of things betwixt, just use the first!
        $activitySounds = DB::Aowow()->selectRow('SELECT * FROM ?_creature_sounds WHERE id = ?d', $this->subject->getField('displayId1'));
        array_shift($activitySounds);                       // remove id-column
        $this->soundIds = array_merge($this->soundIds, array_values($activitySounds));

        if ($this->soundIds)
        {
            $sounds = new SoundList(array(['id', $this->soundIds]));
            if (!$sounds->error)
            {
                $data = $sounds->getListviewData();
                foreach ($activitySounds as $activity => $id)
                    if (isset($data[$id]))
                        $data[$id]['activity'] = $activity; // no index, js wants a string :(

                $tabData = ['data' => array_values($data)];
                if ($activitySounds)
                    $tabData['visibleCols'] = ['activity'];

                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = ['sound', $tabData];
            }
        }
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString}    = $this->subject->getField('name', true);
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
            $power->map                              = $this->subject->getSpawns(SPAWNINFO_SHORT);
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    private function getRepForId($entries, &$spillover)
    {
        $rows  = DB::World()->select('
            SELECT creature_id AS npc, RewOnKillRepFaction1 AS faction, RewOnKillRepValue1 AS qty, MaxStanding1 AS maxRank, isTeamAward1 AS spillover
            FROM creature_onkill_reputation WHERE creature_id IN (?a) AND RewOnKillRepFaction1 > 0 UNION
            SELECT creature_id AS npc, RewOnKillRepFaction2 As faction, RewOnKillRepValue2 AS qty, MaxStanding2 AS maxRank, isTeamAward2 AS spillover
            FROM creature_onkill_reputation WHERE creature_id IN (?a) AND RewOnKillRepFaction2 > 0',
            (array)$entries, (array)$entries
        );

        $factions = new FactionList(array(['id', array_column($rows, 'faction')]));
        $result   = [];

        foreach ($rows as $row)
        {
            if (!$factions->getEntry($row['faction']))
                continue;

            $set = array(
                'id'   => $row['faction'],
                'qty'  => [$row['qty'], 0],
                'name' => $factions->getField('name', true),
                'npc'  => $row['npc'],
                'cap'  => $row['maxRank'] && $row['maxRank'] < REP_EXALTED ? Lang::game('rep', $row['maxRank']) : null
            );

            $cuRate = DB::World()->selectCell('SELECT creature_rate FROM reputation_reward_rate WHERE creature_rate <> 1 AND faction = ?d', $row['faction']);
            if ($cuRate !== null)
                $set['qty'][1] = $set['qty'][0] * ($cuRate - 1);

            if ($row['spillover'])
            {
                $spillover[$factions->getField('cat')] = array(
                    [ $set['qty'][0] / 2, $set['qty'][1] / 2 ],
                    $row['maxRank']
                );
                $set['spillover'] = $factions->getField('cat');
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
            $reputation[] = [Lang::npc('modes', 1, 0), $base];

        // difficulty dummys
        if ($dummyIds && ($mapType == 1 || $mapType == 2))
        {
            $alt = [];
            $rep = $this->getRepForId(array_keys($dummyIds), $spilledParents);

            // order by difficulty
            foreach ($rep as $r)
                $alt[$dummyIds[$r['npc']]][] = $r;

            // apply by difficulty
            foreach ($alt as $mode => $dat)
                $reputation[] = [Lang::npc('modes', $mapType, $mode), $dat];
        }

        // get spillover factions and apply
        if ($spilledParents)
        {
            $spilled = new FactionList(array(['parentFactionId', array_keys($spilledParents)]));

            foreach ($reputation as &$sets)
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
                            'cap'  => $spMax && $spMax < REP_EXALTED ? Lang::game('rep', $spMax) : null
                        );
                    }
                }
            }
        }

        return $reputation;
    }

    private function getQuotes()
    {
        [$quotes, $nQuotes, $soundIds] = Game::getQuotesForCreature($this->typeId, true, $this->subject->getField('name', true));

        if ($soundIds)
            $this->soundIds = array_merge($this->soundIds, $soundIds);

        return [$quotes, $nQuotes];
    }
}


?>
