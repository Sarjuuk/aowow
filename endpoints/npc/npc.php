<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class NpcBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'npc';
    protected  string $pageName   = 'npc';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 4];

    protected  array  $scripts    = [[SC_CSS_FILE, 'css/Profiler.css']];

    public  int    $type        = Type::NPC;
    public  int    $typeId      = 0;
    public  array  $placeholder = [];
    public  array  $accessory   = [];
    public ?array  $quotes      = null;
    public  array  $reputation  = [];
    public  string $subname     = '';

    private  CreatureList $subject;
    private ?CreatureList $altNPCs  = null;
    private  array        $soundIds = [];

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new CreatureList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('npc'), Lang::npc('notFound'));

        $this->h1      = Util::htmlEscape($this->subject->getField('name', true));
        $this->subname = $this->subject->getField('subname', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->subject->getField('name', true)
        );

        $_typeFlags  = $this->subject->getField('typeFlags');
        $_altIds     = [];


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('type');
        if ($_ = $this->subject->getField('family'))
            $this->breadcrumb[] = $_;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->subject->getField('name', true), mb_strtoupper(Lang::game('npc')));


        /***********************/
        /* Difficulty versions */
        /***********************/

        if ($this->subject->getField('cuFlags') & NPC_CU_DIFFICULTY_DUMMY)
            $this->placeholder = [$this->subject->getField('parentId'), $this->subject->getField('parent', true)];
        else
        {
            for ($i = 1; $i < 4; $i++)
                if ($_ = $this->subject->getField('difficultyEntry'.$i))
                    $_altIds[$_] = $i;

            if ($_altIds)
                $this->altNPCs = new CreatureList(array(['id', array_keys($_altIds)]));
        }

        if ($_ = DB::World()->selectCol('SELECT DISTINCT `entry` FROM vehicle_template_accessory WHERE `accessory_entry` = ?d', $this->typeId))
        {
            $vehicles = new CreatureList(array(['id', $_]));
            foreach ($vehicles->iterate() as $id => $__)
                $this->accessory[] = [$id, $vehicles->getField('name', true)];
        }


        /**********************/
        /* Determine Map Type */
        /**********************/

        $mapType = 0;
        if ($maps = DB::Aowow()->selectCell('SELECT IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d', Type::NPC, $this->typeId))
        {
            $mapType = match ((int)DB::Aowow()->selectCell('SELECT `type` FROM ?_zones WHERE `id` = ?d', $maps[0]))
            {
                // MAP_TYPE_DUNGEON,
                MAP_TYPE_DUNGEON_HC    => 1,
                // MAP_TYPE_RAID,
                MAP_TYPE_MMODE_RAID,
                MAP_TYPE_MMODE_RAID_HC => 2,
                default                => 0
            };
        }
        // npc is difficulty dummy: get max difficulty from parent npc
        if ($this->placeholder && ($mt = DB::Aowow()->selectCell('SELECT IF(`difficultyEntry1` = ?d, 1, 2) FROM ?_creature WHERE `difficultyEntry1` = ?d OR `difficultyEntry2` = ?d OR `difficultyEntry3` = ?d', $this->typeId, $this->typeId, $this->typeId, $this->typeId)))
            $mapType = max($mapType, $mt);
        // npc has difficulty dummys: 2+ dummies -> definitely raid (10/25 + hc); 1 dummy -> may be heroic (used here), but may also be 10/25-raid
        if ($_altIds)
            $mapType = max($mapType, count($_altIds) > 1 ? 2 : 1);
        // for event encounters a single npc may be reused over multiple difficulties but have different chests assigned
        if ($d = DB::Aowow()->selectCell('SELECT MAX(`difficulty`) FROM ?_loot_link WHERE `npcId` IN (?a)', array_merge($_altIds, [$this->typeId])))
            $mapType = max($mapType, $d > 2 ? 2 : 1);


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // Event (ignore events, where the object only gets removed)
        if ($_ = DB::World()->selectCol('SELECT DISTINCT ge.`eventEntry` FROM game_event ge, game_event_creature gec, creature c WHERE ge.`eventEntry` = gec.`eventEntry` AND c.`guid` = gec.`guid` AND c.`id` = ?d', $this->typeId))
        {
            $this->extendGlobalIds(Type::WORLDEVENT, ...$_);
            $ev = [];
            foreach ($_ as $i => $e)
                $ev[] = ($i % 2 ? '[br]' : ' ') . '[event='.$e.']';

            $infobox[] = Lang::game('eventShort', [implode(',', $ev)]);
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
            $infobox[] = Lang::npc('classification', [$str]);
        }

        // Reaction
        $color = fn (int $r) : string => match($r)
        {
             1      => 'q2',                                // q2  green
            -1      => 'q10',                               // q10 red
            default => 'q'                                  // q   yellow
        };
        $infobox[] = Lang::npc('react', ['[color='.$color($this->subject->getField('A')).']A[/color] [color='.$color($this->subject->getField('H')).']H[/color]']);

        // Faction
        $this->extendGlobalIds(Type::FACTION, $this->subject->getField('factionId'));
        $infobox[] = Util::ucFirst(Lang::game('faction')).Lang::main('colon').'[faction='.$this->subject->getField('factionId').']';

        // Tameable
        if ($_typeFlags & NPC_TYPEFLAG_TAMEABLE)
            if ($_ = $this->subject->getField('family'))
                $infobox[] = Lang::npc('tameable', ['[url=pet='.$_.']'.Lang::game('fa', $_).'[/url]']);

        // Wealth
        if ($_ = intVal(($this->subject->getField('minGold') + $this->subject->getField('maxGold')) / 2))
            $infobox[] = Lang::npc('worth', ['[tooltip=tooltip_avgmoneydropped][money='.$_.'][/tooltip]']);

        // is Vehicle
        if ($this->subject->getField('vehicleId'))
            $infobox[] = Lang::npc('vehicle');

        // is visible as ghost (redundant to extraFlags)
        if ($this->subject->getField('npcflag') & (NPC_FLAG_SPIRIT_HEALER | NPC_FLAG_SPIRIT_GUIDE))
            $infobox[] = Lang::npc('extraFlags', CREATURE_FLAG_EXTRA_GHOST_VISIBILITY);

        // id
        $infobox[] = Lang::npc('id') . $this->typeId;

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

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
                        $buff[] = (!fMod(count($buff), 3) ? "\n" : '').'[url=?spells&filter=me='.($i + 1).']'.Lang::game('me', $i + 1).'[/url]';

                $infobox[] = Lang::npc('mechanicimmune', [implode(', ', $buff)]);
            }

            // extra flags
            if ($flagsExtra = $this->subject->getField('flagsExtra'))
            {
                $buff = [];
                foreach (Lang::npc('extraFlags') as $idx => $str)
                    if ($flagsExtra & $idx)
                        $buff[] = $str;

                if ($buff)
                    $infobox[] = Lang::npc('_extraFlags').'[ul][li]'.implode('[/li][li]', $buff).'[/li][/ul]';
            }

            // Mode dummy references
            if ($this->altNPCs)
            {
                $this->extendGlobalData($this->altNPCs->getJSGlobals());
                $buff = Lang::npc('versions').'[ul]';
                foreach ($this->altNPCs->iterate() as $id => $__)
                    $buff .= '[li][npc='.$id.'][/li]';
                $infobox[] = $buff.'[/ul]';
            }
        }

        if ($stats = $this->getCreatureStats($mapType, $_altIds))
            $infobox[] = Lang::npc('stats').($_altIds ? ' ('.Lang::game('modes', $mapType, 0).')' : '').Lang::main('colon').'[ul][li]'.implode('[/li][li]', $stats).'[/li][/ul]';

        if ($infobox)
        {
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');
            $this->extendGlobalData($this->infobox->getJsGlobals());
        }


        /****************/
        /* Main Content */
        /****************/

        // get spawns and path
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $this->addDataLoader('zones');
            $this->map = array(
                ['parent' => 'mapper-generic'],             // Mapper
                $spawns,                                    // mapperData
                null,                                       // ShowOnMap
                [Lang::npc('foundIn')]                      // foundIn
            );
            foreach ($spawns as $areaId => $_)
                $this->map[3][$areaId] = ZoneList::getName($areaId);
        }

        // smart AI
        $sai = null;
        if ($this->subject->getField('aiName') == 'SmartAI')
        {
            $sai = new SmartAI(SmartAI::SRC_TYPE_CREATURE, $this->typeId);
            if (!$sai->prepare())                           // no smartAI found .. check per guid
            {
                // at least one of many
                $guids = DB::World()->selectCol('SELECT `guid` FROM creature WHERE `id` = ?d', $this->typeId);
                while ($_ = array_pop($guids))
                {
                    $sai = new SmartAI(SmartAI::SRC_TYPE_CREATURE, -$_, ['baseEntry' => $this->typeId, 'title' => ' [small](for GUID: '.$_.')[/small]']);
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
                trigger_error('Creature has SmartAI set in template but no SmartAI defined.');
        }

        // consider pooled spawns
        $this->quotes       = $this->getQuotes();
        $this->reputation   = $this->getOnKillRep($_altIds, $mapType);
        $this->redButtons   = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_VIEW3D  => ['type' => Type::NPC, 'typeId' => $this->typeId, 'displayId' => $this->subject->getRandomModelId()]
        );

        if ($this->subject->getField('humanoid'))
            $this->redButtons[BUTTON_VIEW3D]['humanoid'] = 1;


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: abilities / tab_controlledabilities (dep: VehicleId)
        $tplSpells  = [];
        $genSpells  = [];
        $spellClick = [];
        $conditions = ['OR'];

        for ($i = 1; $i < 9; $i++)
            if ($_ = $this->subject->getField('spell'.$i))
                $tplSpells[] = $_;

        if ($tplSpells)
            $conditions[] = ['id', $tplSpells];

        if ($smartSpells = SmartAI::getSpellCastsForOwner($this->typeId, SmartAI::SRC_TYPE_CREATURE))
            $genSpells = $smartSpells;

        if ($auras = DB::World()->selectCell('SELECT `auras` FROM creature_template_addon WHERE `entry` = ?d', $this->typeId))
        {
            $auras = preg_replace('/[^\d ]/', ' ', $auras);  // remove erroneous chars from string
            $genSpells = array_merge($genSpells, array_filter(explode(' ', $auras)));
        }

        if ($genSpells)
            $conditions[] = ['id', $genSpells];

        if ($spellClick = DB::World()->select('SELECT `spell_id` AS ARRAY_KEY, `cast_flags` AS "0", `user_type` AS "1" FROM npc_spellclick_spells WHERE `npc_entry` = ?d', $this->typeId))
        {
            $genSpells = array_merge($genSpells, array_keys($spellClick));
            $conditions[] = ['id', array_keys($spellClick)];
        }

        // Pet-Abilities
        if (($_typeFlags & NPC_TYPEFLAG_TAMEABLE) && ($_ = $this->subject->getField('family')))
        {
            $skill = 0;
            $mask  = 0x0;
            foreach (Game::$skillLineMask[-1] as $idx => [$familyId, $skillLineId])
            {
                if ($familyId != $_)
                    continue;

                $skill = $skillLineId;
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
                    if (isset($spellClick[$id]))
                        $values['spellclick'] = $spellClick[$id];

                    if (in_array($id, $genSpells))
                    {
                        $normal[$id] = $values;
                        if (!in_array($id, $tplSpells))
                            unset($controled[$id]);
                    }
                }

                $cnd = new Conditions();
                $cnd->getBySource(Conditions::SRC_VEHICLE_SPELL, group: $this->typeId)->prepare();
                if ($cnd->toListviewColumn($controled, $extraCols, $this->typeId, 'id'))
                    $this->extendGlobalData($cnd->getJsGlobals());

                if ($normal)
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data' => $normal,
                        'name' => '$LANG.tab_abilities',
                        'id'   => 'abilities'
                    ), SpellList::$brickFile));

                if ($controled)
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'      => $controled,
                        'name'      => '$LANG.tab_controlledabilities',
                        'id'        => 'controlled-abilities',
                        'extraCols' => $extraCols ?: null
                    ), SpellList::$brickFile));
            }
        }

        // tab: summoned by [spell]
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', [SPELL_EFFECT_SUMMON, SPELL_EFFECT_SUMMON_PET, SPELL_EFFECT_SUMMON_DEMON]], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', [SPELL_EFFECT_SUMMON, SPELL_EFFECT_SUMMON_PET, SPELL_EFFECT_SUMMON_DEMON]], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', [SPELL_EFFECT_SUMMON, SPELL_EFFECT_SUMMON_PET, SPELL_EFFECT_SUMMON_DEMON]], ['effect3MiscValue', $this->typeId]]
        );

        $sbSpell = new SpellList($conditions);
        if (!$sbSpell->error)
        {
            $this->extendGlobalData($sbSpell->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $sbSpell->getListviewData(),
                'name' => '$LANG.tab_summonedby',
                'id'   => 'summoned-by-spell'
            ), SpellList::$brickFile));
        }

        // tab: summoned by [NPC]
        $sb = SmartAI::getOwnerOfNPCSummon($this->typeId);
        if (!empty($sb[Type::NPC]))
        {
            $sbNPC = new CreatureList(array(['id', $sb[Type::NPC]]));
            if (!$sbNPC->error)
            {
                $this->extendGlobalData($sbNPC->getJSGlobals());

                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $sbNPC->getListviewData(),
                    'name' => '$LANG.tab_summonedby',
                    'id'   => 'summoned-by-npc'
                ), CreatureList::$brickFile));
            }
        }

        // tab: summoned by [Object]
        if (!empty($sb[Type::OBJECT]))
        {
            $sbGO = new GameObjectList(array(['id', $sb[Type::OBJECT]]));
            if (!$sbGO->error)
            {
                $this->extendGlobalData($sbGO->getJSGlobals());

                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $sbGO->getListviewData(),
                    'name' => '$LANG.tab_summonedby',
                    'id'   => 'summoned-by-object'
                ), GameObjectList::$brickFile));
            }
        }

        // tab: teaches
        if ($this->subject->getField('npcflag') & NPC_FLAG_TRAINER)
        {
            $teachQuery =
               'SELECT ts.`SpellId` AS ARRAY_KEY, ts.`MoneyCost` AS "cost", ts.`ReqSkillLine` AS "reqSkillId", ts.`ReqSkillRank` AS "reqSkillValue", ts.`ReqLevel` AS "reqLevel", ts.`ReqAbility1` AS "reqSpellId1", ts.`reqAbility2` AS "reqSpellId2"
                FROM   trainer_spell ts
                JOIN   creature_default_trainer cdt ON cdt.`TrainerId` = ts.`TrainerId`
                WHERE  cdt.`Creatureid` = ?d';

            if ($tSpells = DB::World()->select($teachQuery, $this->typeId))
            {
                $teaches = new SpellList(array(['id', array_keys($tSpells)]));
                if (!$teaches->error)
                {
                    $this->extendGlobalData($teaches->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                    $data = $teaches->getListviewData();

                    $extraCols = [];
                    $cnd = new Conditions();
                    foreach ($tSpells as $sId => $train)
                    {
                        if (empty($data[$sId]))
                            continue;

                        if ($_ = $train['reqSkillId'])
                            if (count($data[$sId]['skill']) == 1 && $_ != $data[$sId]['skill'][0])
                                $cnd->addExternalCondition(Conditions::SRC_NONE, $sId, [Conditions::SKILL, $_, $train['reqSkillValue']]);

                        for ($i = 1; $i < 3; $i++)
                            if ($_ = $train['reqSpellId'.$i])
                                $cnd->addExternalCondition(Conditions::SRC_NONE, $sId, [Conditions::SPELL, $_]);

                        if ($_ = $train['reqLevel'])
                        {
                            if (!isset($extraCols[1]))
                                $extraCols[1] = "\$Listview.funcBox.createSimpleCol('reqLevel', LANG.tooltip_reqlevel, '7%', 'reqLevel')";

                            $data[$sId]['reqLevel'] = $_;
                        }

                        if ($_ = $train['cost'])
                            $data[$sId]['trainingcost'] = $_;
                    }

                    if ($cnd->toListviewColumn($data, $extraCols))
                        $this->extendGlobalData($cnd->getJsGlobals());

                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'        => $data,
                        'name'        => '$LANG.tab_teaches',
                        'id'          => 'teaches',
                        'visibleCols' => ['trainingcost'],
                        'extraCols'   => $extraCols ?: null
                    ), SpellList::$brickFile));
                }
            }
            else
                trigger_error('NPC '.$this->typeId.' is flagged as trainer, but doesn\'t have any spells set', E_USER_WARNING);
        }

        // tab: sells
        if ($sells = DB::World()->selectCol(
           'SELECT   nv.`item` FROM npc_vendor nv                                                               WHERE   nv.`entry` = ?d UNION
            SELECT  nv1.`item` FROM npc_vendor nv1             JOIN npc_vendor nv2 ON -nv1.`entry` = nv2.`item` WHERE  nv2.`entry` = ?d UNION
            SELECT genv.`item` FROM game_event_npc_vendor genv JOIN creature   c   ON genv.`guid`  =   c.`guid` WHERE    c.`id`    = ?d',
            $this->typeId, $this->typeId, $this->typeId)
        )
        {
            $soldItems = new ItemList(array(['id', $sells]));
            if (!$soldItems->error)
            {
                $colAddIn  = '';
                $extraCols = ["\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost'];

                $lvData = $soldItems->getListviewData(ITEMINFO_VENDOR, [Type::NPC => [$this->typeId]]);

                if (array_column($lvData, 'condition'))
                    $extraCols[] = '$Listview.extraCols.condition';

                if (array_filter(array_column($lvData, 'restock')))
                {
                    $extraCols[] = '$_';
                    $colAddIn = 'vendorRestockCol';
                }

                $cnd = new Conditions();
                if ($cnd->getBySource(Conditions::SRC_NPC_VENDOR, group: $this->typeId)->prepare())
                {
                    $this->extendGlobalData($cnd->getJsGlobals());
                    $cnd->toListviewColumn($lvData, $extraCols, $this->typeId, 'id');
                }

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'      => $lvData,
                    'name'      => '$LANG.tab_sells',
                    'id'        => 'currency-for',
                    'extraCols' => array_unique($extraCols)
                ), ItemList::$brickFile, $colAddIn));

                $this->extendGlobalData($soldItems->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tabs: this creature contains..
        if ($this->subject->isGatherable())
            $skinTab = ['$LANG.tab_herbalism',   'herbalism',   SKILL_HERBALISM];
        else if ($this->subject->isMineable())
            $skinTab = ['$LANG.tab_mining',      'mining',      SKILL_MINING];
        else if ($this->subject->isSalvageable())
            $skinTab = ['$LANG.tab_engineering', 'engineering', SKILL_ENGINEERING];
        else
            $skinTab = ['$LANG.tab_skinning',    'skinning',    SKILL_SKINNING];

        $sourceFor = array(
            0 => [Loot::CREATURE,   [4 => $this->subject->getField('lootId')],           '$LANG.tab_drops',         'drops',         [                          ], ''],
            1 => [Loot::GAMEOBJECT, [],                                                  '$LANG.tab_drops',         'drops-object',  [                          ], ''],
            2 => [Loot::PICKPOCKET, [4 => $this->subject->getField('pickpocketLootId')], '$LANG.tab_pickpocketing', 'pickpocketing', ['side', 'slot', 'reqlevel'], ''],
            3 => [Loot::SKINNING,   [4 => $this->subject->getField('skinLootId')],       $skinTab[0],               $skinTab[1],     ['side', 'slot', 'reqlevel'], '']
        );

        /* loot tabs to sub tabs
         *   (1 << 0) => '$LANG.tab_heroic',
         *   (1 << 1) => '$LANG.tab_normal',
         *   (1 << 2) => '$LANG.tab_drops',
         *   (1 << 3) => '$$WH.sprintf(LANG.tab_normalX, 10)',
         *   (1 << 4) => '$$WH.sprintf(LANG.tab_normalX, 25)',
         *   (1 << 5) => '$$WH.sprintf(LANG.tab_heroicX, 10)',
         *   (1 << 6) => '$$WH.sprintf(LANG.tab_heroicX, 25)'
         */

        $getBit = function(int $type, int $difficulty) : int
        {
            if ($type == 1)                                 // dungeon
                return 1 << (2 - $difficulty);
            if ($type == 2)                                 // raid
                return 1 << (2 + $difficulty);
            return 4;                                       // generic case
        };

        foreach (DB::Aowow()->select('SELECT l.`difficulty` AS ARRAY_KEY, o.`id`, o.`lootId`, o.`name_loc0`, o.`name_loc2`, o.`name_loc3`, o.`name_loc4`, o.`name_loc6`, o.`name_loc8` FROM ?_loot_link l JOIN ?_objects o ON o.`id` = l.`objectId` WHERE l.`npcId` = ?d ORDER BY `difficulty` ASC', $this->typeId) as $difficulty => $lgo)
        {
            $sourceFor[1][1][$getBit($mapType, $difficulty)] = $lgo['lootId'];
            $sourceFor[1][5] = $sourceFor[1][5] ?: '$$WH.sprintf(LANG.lvnote_npcobjectsource, '.$lgo['id'].', "'.Util::localizedString($lgo, 'name').'")';
        }

        if ($_altIds)
        {
            if ($mapType == 1)                              // map generic loot to dungeon NH
            {
                $sourceFor[0][1] = [2 => $sourceFor[0][1][4]];
                $sourceFor[2][1] = [2 => $sourceFor[2][1][4]];
                $sourceFor[3][1] = [2 => $sourceFor[3][1][4]];
            }
            if ($mapType == 2)                              // map generic loot to raid 10NH
            {
                $sourceFor[0][1] = [8 => $sourceFor[0][1][4]];
                $sourceFor[2][1] = [8 => $sourceFor[2][1][4]];
                $sourceFor[3][1] = [8 => $sourceFor[3][1][4]];
            }

            foreach ($this->altNPCs->iterate() as $id => $__)
            {
                foreach (DB::Aowow()->select('SELECT l.`difficulty` AS ARRAY_KEY, o.`id`, o.`lootId`, o.`name_loc0`, o.`name_loc2`, o.`name_loc3`, o.`name_loc4`, o.`name_loc6`, o.`name_loc8` FROM ?_loot_link l JOIN ?_objects o ON o.`id` = l.`objectId` WHERE l.`npcId` = ?d ORDER BY `difficulty` ASC', $id) as $difficulty => $lgo)
                {
                    $sourceFor[1][1][$getBit($mapType, $difficulty)] = $lgo['lootId'];
                    $sourceFor[1][5] = $sourceFor[1][5] ?: '$$WH.sprintf(LANG.lvnote_npcobjectsource, '.$lgo['id'].', "'.Util::localizedString($lgo, 'name').'")';
                }

                if ($lootId = $this->altNPCs->getField('lootId'))
                    $sourceFor[0][1][$getBit($mapType, $_altIds[$id] + 1)] = $lootId;
                if ($lootId = $this->altNPCs->getField('pickpocketLootId'))
                    $sourceFor[2][1][$getBit($mapType, $_altIds[$id] + 1)] = $lootId;
                if ($lootId = $this->altNPCs->getField('skinLootId'))
                    $sourceFor[3][1][$getBit($mapType, $_altIds[$id] + 1)] = $lootId;
            }
        }

        foreach ($sourceFor as [$lootTpl, $lootEntries, $tabName, $tabId, $hiddenCols, $note])
        {
            $creatureLoot = new LootByContainer();
            if ($creatureLoot->getByContainer($lootTpl, $lootEntries))
            {
                $extraCols   = $creatureLoot->extraCols;
                array_push($extraCols, '$Listview.extraCols.count', '$Listview.extraCols.percent');
                if (count($lootEntries) > 1)
                    $extraCols[] = '$Listview.extraCols.mode';

                $hiddenCols[] = 'count';

                $this->extendGlobalData($creatureLoot->jsGlobals);

                $tabData = array(
                    'data'            => $creatureLoot->getResult(),
                    'id'              => $tabId,
                    'name'            => $tabName,
                    'extraCols'       => array_unique($extraCols),
                    'hiddenCols'      => $hiddenCols ?: null,
                    'sort'            => ['-percent', 'name'],
                    '_totalCount'     => 10000,
                    'computeDataFunc' => '$Listview.funcBox.initLootTable',
                    'onAfterCreate'   => '$Listview.funcBox.addModeIndicator',
                );

                if ($note)
                    $tabData['note'] = $note;
                else if ($lootTpl == Loot::SKINNING)
                    $tabData['note'] = '<b>'.Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill($skinTab[2], $this->subject->getField('maxLevel') * 5), Lang::FMT_HTML).'</b>';

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
            }
        }

        // tab: starts quest
        // tab: ends quest
        $startEnd = new QuestList(array(['qse.type', Type::NPC], ['qse.typeId', $this->typeId]));
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

        // tab: objective of quest
        $conditions = array(
            'OR',
            ['AND', ['reqNpcOrGo1', [$this->typeId]], ['reqNpcOrGoCount1', 0, '>']],
            ['AND', ['reqNpcOrGo2', [$this->typeId]], ['reqNpcOrGoCount2', 0, '>']],
            ['AND', ['reqNpcOrGo3', [$this->typeId]], ['reqNpcOrGoCount3', 0, '>']],
            ['AND', ['reqNpcOrGo4', [$this->typeId]], ['reqNpcOrGoCount4', 0, '>']]
        );
        foreach ([1, 2] as $i)
            if (($_ = $this->subject->getField('KillCredit'.$i)) > 0)
                for ($j = 1; $j < 5; $j++)
                    $conditions[$j][1][1][] = $_;

        $objectiveOf = new QuestList($conditions);
        if (!$objectiveOf->error)
        {
            $this->extendGlobalData($objectiveOf->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $objectiveOf->getListviewData(),
                'name' => '$LANG.tab_objectiveof',
                'id'   => 'objective-of'
            ), QuestList::$brickFile));
        }

        // tab: criteria of [ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE_TYPE have no data set to check for]
        $conditions = array(
            'AND',
            ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE, ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE]],
            ['ac.value1', $this->typeId]
        );

        if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` = ?d AND `value1` = ?d', ACHIEVEMENT_CRITERIA_DATA_TYPE_T_CREATURE, $this->typeId))
            $conditions = ['OR', $conditions, ['ac.id', $extraCrt]];

        $crtOf = new AchievementList($conditions);
        if (!$crtOf->error)
        {
            $this->extendGlobalData($crtOf->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $crtOf->getListviewData(),
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            ), AchievementList::$brickFile));
        }

        // tab: passengers
        if ($_ = DB::World()->selectCol('SELECT `accessory_entry` AS ARRAY_KEY, GROUP_CONCAT(`seat_id` SEPARATOR ", ") FROM vehicle_template_accessory WHERE `entry` = ?d GROUP BY `accessory_entry`', $this->typeId))
        {
            $passengers = new CreatureList(array(['id', array_keys($_)]));
            if (!$passengers->error)
            {
                $data = $passengers->getListviewData();

                if (User::isInGroup(U_GROUP_STAFF))
                    foreach ($data as $id => &$d)
                        $d['seat'] = $_[$id];

                $this->extendGlobalData($passengers->getJSGlobals(GLOBALINFO_SELF));

                $tabData = array(
                    'data' => $data,
                    'name' => Lang::npc('accessory'),
                    'id'   => 'accessory'
                );

                if (User::isInGroup(U_GROUP_STAFF))
                    $tabData['extraCols'] = ["\$Listview.funcBox.createSimpleCol('seat', '".Lang::npc('seat')."', '10%', 'seat')"];

                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile));
            }
        }

        /* tab sounds:
            * activity sounds => CreatureDisplayInfo.dbc => (CreatureModelData.dbc => ) CreatureSoundData.dbc
            * AI => smart_scripts
            * Dialogue VO => creature_text
            * onClick VO => CreatureDisplayInfo.dbc => NPCSounds.dbc
        */
        $this->soundIds = array_merge($this->soundIds, SmartAI::getSoundsPlayedForOwner($this->typeId, SmartAI::SRC_TYPE_CREATURE));

        // up to 4 possible displayIds .. for the love of things betwixt, just use the first!
        $activitySounds = DB::Aowow()->selectRow('SELECT * FROM ?_creature_sounds WHERE `id` = ?d', $this->subject->getField('displayId1'));
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

                    $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'        => $data,
                        'visibleCols' => $activitySounds ? 'activity' : null
                    ), SoundList::$brickFile));
            }
        }

        // tab: conditions
        $cnd = new Conditions();
        $cnd->getBySource(Conditions::SRC_CREATURE_TEMPLATE_VEHICLE, entry: $this->typeId)
            ->getBySource(Conditions::SRC_SPELL_CLICK_EVENT, group: $this->typeId)
            ->getByCondition(Type::NPC, $this->typeId)
            ->prepare();
        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }

    private function getRepForId(array $entries, array &$spillover) : array
    {
        $rows  = DB::World()->select(
           'SELECT `creature_id` AS "npc", `RewOnKillRepFaction1` AS "faction", `RewOnKillRepValue1` AS "qty", `MaxStanding1` AS "maxRank", `isTeamAward1` AS "spillover"
            FROM   creature_onkill_reputation WHERE `creature_id` IN (?a) AND `RewOnKillRepFaction1` > 0 UNION
            SELECT `creature_id` AS "npc", `RewOnKillRepFaction2` AS "faction", `RewOnKillRepValue2` AS "qty", `MaxStanding2` AS "maxRank", `isTeamAward2` AS "spillover"
            FROM   creature_onkill_reputation WHERE `creature_id` IN (?a) AND `RewOnKillRepFaction2` > 0',
            $entries, $entries
        );

        $factions = new FactionList(array(['id', array_column($rows, 'faction')]));
        $result   = [];

        foreach ($rows as $row)
        {
            if (!$factions->getEntry($row['faction']))
                continue;

            $set = array(
                $row['faction'],                            // factionId
                [$row['qty'], 0],                           // qty
                $factions->getField('name', true),          // name
                $row['maxRank'] && $row['maxRank'] < REP_EXALTED ? Lang::game('rep', $row['maxRank']) : null, // cap
                $row['npc'],                                // npcId
                0                                           // spilloverCat
            );

            $cuRate = DB::World()->selectCell('SELECT `creature_rate` FROM reputation_reward_rate WHERE `creature_rate` <> 1 AND `faction` = ?d', $row['faction']);
            if ($cuRate && User::isInGroup(U_GROUP_EMPLOYEE))
                $set[1][1] = $set[1][0] . sprintf(Util::$dfnString, Lang::faction('customRewRate'), ($set[1][0] > 0 ? '+' : '').($set[1][0] * ($cuRate - 1)));
            else if ($cuRate)
                $set[1][1] = $set[1][0] * $cuRate;

            if ($row['spillover'])
            {
                $spill = [[$set[1][0] / 2, 0], $row['maxRank']];
                if ($cuRate && User::isInGroup(U_GROUP_EMPLOYEE))
                    $spill[0][1] = $spill[0][0] . sprintf(Util::$dfnString, Lang::faction('customRewRate'), ($set[1][0] > 0 ? '+' : '').($spill[0][0] * ($cuRate - 1) * 0.5));
                else if ($cuRate)
                    $spill[0][1] = $set[1][1] / 2;

                $spillover[$factions->getField('cat')] = $spill;
                $set[5] = $factions->getField('cat');       // set spillover
            }

            $result[] = $set;
        }

        return $result;
    }

    private function getOnKillRep(array $dummyIds, int $mapType) : array
    {
        $spilledParents = [];
        $reputation     = [];

        // base NPC
        if ($base = $this->getRepForId([$this->typeId], $spilledParents))
            $reputation[] = [Lang::game('modes', 1, 0), $base];

        // difficulty dummys
        if ($dummyIds && ($mapType == 1 || $mapType == 2))
        {
            $alt = [];
            $rep = $this->getRepForId(array_keys($dummyIds), $spilledParents);

            // order by difficulty
            foreach ($rep as $i => [, , , , $npcId])
                $alt[$dummyIds[$npcId]][] = $rep[$i];

            // apply by difficulty
            foreach ($alt as $mode => $dat)
                $reputation[] = [Lang::game('modes', $mapType, $mode), $dat];
        }

        // get spillover factions and apply
        if ($spilledParents)
        {
            $spilled = new FactionList(array(['parentFactionId', array_keys($spilledParents)]));

            foreach ($reputation as $i => [, $data])
            {
                foreach ($data as [$factionId, , , , , $spillover])
                {
                    if (!$spillover)
                        continue;

                    foreach ($spilled->iterate() as $spId => $__)
                    {
                        // find parent
                        if ($spilled->getField('parentFactionId') != $spillover)
                            continue;

                        // don't readd parent
                        if ($factionId == $spId)
                            continue;

                        $spMax = $spilledParents[$spillover][1];

                        $reputation[$i][1][] = array(
                            $spId,
                            $spilledParents[$spillover][0],
                            $spilled->getField('name', true),
                            $spMax && $spMax < REP_EXALTED ? Lang::game('rep', $spMax) : null
                        );
                    }
                }
            }
        }

        return $reputation;
    }

    private function getQuotes() : ?array
    {
        [$quotes, $nQuotes, $soundIds] = Game::getQuotesForCreature($this->typeId, true, $this->subject->getField('name', true));

        if ($soundIds)
            $this->soundIds = array_merge($this->soundIds, $soundIds);

        return $quotes ? [$quotes, $nQuotes] : null;
    }

    private function getCreatureStats(int $mapType, array $altIds) : array
    {
        $stats   = [];
        $modes   = [];                                      // get difficulty versions if set
        $hint    = '[tooltip name=%3$s][table cellspacing=10][tr]%1s[/tr][/table][/tooltip][span class=tip tooltip=%3$s]%2s[/span]';
        $modeRow = '[tr][td]%s&nbsp;&nbsp;[/td][td]%s[/td][/tr]';
        // Health
        $health = $this->subject->getBaseStats('health');
        $stats['health'] = Util::ucFirst(Lang::spell('powerTypes', -2)).Lang::main('colon').($health[0] < $health[1] ? Lang::nf($health[0]).' - '.Lang::nf($health[1]) : Lang::nf($health[0]));

        // Mana (may be 0)
        $mana = $this->subject->getBaseStats('power');
        $stats['mana'] = $mana[0] ? Lang::spell('powerTypes', 0).Lang::main('colon').($mana[0] < $mana[1] ? Lang::nf($mana[0]).' - '.Lang::nf($mana[1]) : Lang::nf($mana[0])) : '';

        // Armor
        $armor = $this->subject->getBaseStats('armor');
        $stats['armor'] = Lang::npc('armor').($armor[0] < $armor[1] ? Lang::nf($armor[0]).' - '.Lang::nf($armor[1]) : Lang::nf($armor[0]));

        // Resistances
        $resNames = [null, 'hol', 'fir', 'nat', 'fro', 'sha', 'arc'];
        $tmpRes   = [];
        $res      = $this->subject->getBaseStats('resistance'); // $sc => $amt
        $stats['resistance'] = '';
        foreach ($resNames as $idx => $sc)
        {
            if (!$sc)
                continue;

            if ((1 << $idx) & $this->subject->getField('schoolImmuneMask'))
                $tmpRes[] = '[tooltip=tooltip_immune][span class="tip moneyschool'.$sc.'"]∞[/span][/tooltip]';
            else if ($res[$idx])
                $tmpRes[] = '[span class="moneyschool'.$sc.'"]'.$res[$idx].'[/span]';
        }

        if ($tmpRes)
        {
            $stats['resistance'] = Lang::npc('resistances').'[br]';
            if (count($tmpRes) > 3)
                $stats['resistance'] .= implode('&nbsp;', array_slice($tmpRes, 0, 3)).'[br]'.implode('&nbsp;', array_slice($tmpRes, 3));
            else
                $stats['resistance'] .= implode('&nbsp;', $tmpRes);
        }

        // Melee Damage
        $melee = $this->subject->getBaseStats('melee');
        if ($_ = $this->subject->getField('dmgSchool'))     // magic damage
            $stats['melee'] = Lang::npc('melee').Lang::nf($melee[0]).' - '.Lang::nf($melee[1]).' ('.Lang::game('sc', $_).')';
        else                                                // phys. damage
            $stats['melee'] = Lang::npc('melee').Lang::nf($melee[0]).' - '.Lang::nf($melee[1]);

        // Ranged Damage
        $ranged = $this->subject->getBaseStats('ranged');
        $stats['ranged'] = Lang::npc('ranged').Lang::nf($ranged[0]).' - '.Lang::nf($ranged[1]);

        foreach ($altIds as $id => $mode)
        {
            if (!$this->altNPCs->getEntry($id))
                continue;

            $m = Lang::game('modes', $mapType, $mode);

            // Health
            $health = $this->altNPCs->getBaseStats('health');
            $modes['health'][] = sprintf($modeRow, $m, $health[0] < $health[1] ? Lang::nf($health[0]).' - '.Lang::nf($health[1]) : Lang::nf($health[0]));

            // Mana (may be 0)
            $mana = $this->altNPCs->getBaseStats('power');
            $modes['mana'][] = $mana[0] ? sprintf($modeRow, $m, $mana[0] < $mana[1] ? Lang::nf($mana[0]).' - '.Lang::nf($mana[1]) : Lang::nf($mana[0])) : null;

            // Armor
            $armor = $this->altNPCs->getBaseStats('armor');
            $modes['armor'][] = sprintf($modeRow, $m, $armor[0] < $armor[1] ? Lang::nf($armor[0]).' - '.Lang::nf($armor[1]) : Lang::nf($armor[0]));

            // Resistances
            if (array_filter($this->altNPCs->getBaseStats('resistance')))
            {
                if (!isset($modes['resistance']))           // init table head
                    $modes['resistance'][] = '[td][/td][td][span class="moneyschoolhol" style="margin: 0px 5px"][/span][/td][td][span class="moneyschoolfir" style="margin: 0px 5px"][/span][/td][td][span class="moneyschoolnat" style="margin: 0px 5px"][/span][/td][td][span class="moneyschoolfro" style="margin: 0px 5px"][/span][/td][td][span class="moneyschoolsha" style="margin: 0px 5px"][/span][/td][td][span class="moneyschoolarc" style="margin: 0px 5px"][/span][/td]';

                if (!$stats['resistance'])                  // base creature has no resistance. -> display list item.
                    $stats['resistance'] = Lang::npc('resistances').'…';

                $tmpRes = '';
                $res    = $this->altNPCs->getBaseStats('resistance');
                foreach ($resNames as $idx => $sc)
                {
                    if (!$sc)
                        continue;

                    if ((1 << $idx) & $this->altNPCs->getField('schoolImmuneMask'))
                        $tmpRes .= '[td][span style="margin: 0px 5px"]∞[/span][/td]';
                    else if ($res[$idx])
                        $tmpRes .= '[td][span style="margin: 0px 5px"]'.$res[$idx].'[/span][/td]';
                }

                $modes['resistance'][] = '[td]'.$m.'&nbsp;&nbsp;&nbsp;&nbsp;[/td]'.$tmpRes;
            }

            // Melee Damage
            $melee = $this->altNPCs->getBaseStats('melee');
            if ($_ = $this->altNPCs->getField('dmgSchool')) // magic damage
                $modes['melee'][] = sprintf($modeRow, $m, Lang::nf($melee[0]).' - '.Lang::nf($melee[1]).' ('.Lang::game('sc', $_).')');
            else                                            // phys. damage
                $modes['melee'][] = sprintf($modeRow, $m, Lang::nf($melee[0]).' - '.Lang::nf($melee[1]));

            // Ranged Damage
            $ranged = $this->altNPCs->getBaseStats('ranged');
            $modes['ranged'][] = sprintf($modeRow, $m, Lang::nf($ranged[0]).' - '.Lang::nf($ranged[1]));
        }

        // todo: resistances can be present/missing in either $stats or $modes
        // should be handled separately..?

        if ($modes)
            foreach ($stats as $k => $v)
                if ($v)
                    $stats[$k] = isset($modes[$k]) ? sprintf($hint, implode('[/tr][tr]', $modes[$k]), $v, $k) : $v;

        return $stats;
    }
}


?>
