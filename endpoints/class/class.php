<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ClassBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    private const TC_CLASS_IDS = [null, 8, 3, 1, 5, 4, 9, 6, 2, 7, null, 0]; // see TalentCalc.js

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'class';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 12];

    public  int     $type      = Type::CHR_CLASS;
    public  int     $typeId    = 0;
    public ?string  $expansion = null;

    private CharClassList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new CharClassList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('class'), Lang::chrClass('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->typeId;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('class')));


        /***********/
        /* Infobox */
        /***********/

        $cl = ChrClass::from($this->typeId);

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // hero class
        if ($this->subject->getField('flags') & 0x40)
            $infobox[] = '[tooltip=tooltip_heroclass]'.Lang::game('heroClass').'[/tooltip]';

        // resource
        if ($cl == ChrClass::DRUID)                         // special Druid case
            $infobox[] = Lang::game('resources').
            '[tooltip name=powertype1]'.Lang::game('st', 0).', '.Lang::game('st', 31).', '.Lang::game('st', 2).'[/tooltip][span class=tip tooltip=powertype1]'.Util::ucFirst(Lang::spell('powerTypes', POWER_MANA)).'[/span], '.
            '[tooltip name=powertype2]'.Lang::game('st', 5).', '.Lang::game('st', 8).'[/tooltip][span class=tip tooltip=powertype2]'.Util::ucFirst(Lang::spell('powerTypes', POWER_RAGE)).'[/span], '.
            '[tooltip name=powertype8]'.Lang::game('st', 1).'[/tooltip][span class=tip tooltip=powertype8]'.Util::ucFirst(Lang::spell('powerTypes', POWER_ENERGY)).'[/span]';
        else if ($cl == ChrClass::DEATHKNIGHT)              // special DK case
            $infobox[] = Lang::game('resources').'[span]'.Util::ucFirst(Lang::spell('powerTypes', POWER_RUNE)).', '.Util::ucFirst(Lang::spell('powerTypes', $this->subject->getField('powerType'))).'[/span]';
        else                                                // regular case
            $infobox[] = Lang::game('resource').'[span]'.Util::ucFirst(Lang::spell('powerTypes', $this->subject->getField('powerType'))).'[/span]';

        // roles
        $roles = [];
        for ($i = 0; $i < 4; $i++)
            if ($this->subject->getField('roles') & (1 << $i))
                $roles[] = (count($roles) == 2 ? "[br]" : '').Lang::game('_roles', $i);

        if ($roles)
            $infobox[] = (count($roles) > 1 ? Lang::game('roles') : Lang::game('role')).implode(', ', $roles);

        // specs
        $specList = [];
        $skills = new SkillList(array(['id', $this->subject->getField('skills')]));
        foreach ($skills->iterate() as $k => $__)
            $specList[$k] = '[icon name='.$skills->getField('iconString').'][url=?spells=7.'.$this->typeId.'.'.$k.']'.$skills->getField('name', true).'[/url][/icon]';

        if ($specList)
            $infobox[] = Lang::game('specs').'[ul][li]'.implode('[/li][li]', $specList).'[/li][/ul]';

        // id
        $infobox[] = Lang::chrClass('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => true,
            BUTTON_TALENT  => ['href' => '?talent#'.Util::$tcEncoding[self::TC_CLASS_IDS[$this->typeId] * 3], 'pet' => false],
            BUTTON_FORUM   => false                         // todo (low): Cfg::get('BOARD_URL') + X
        );

        if ($_ = $this->subject->getField('iconString'))
            $this->headIcons[] = $_;


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: spells (grouped)
        //     '$LANG.tab_armorproficiencies',
        //     '$LANG.tab_weaponskills',
        //     '$LANG.tab_glyphs',
        //     '$LANG.tab_abilities',
        //     '$LANG.tab_talents',
        $conditions = array(
            ['s.typeCat', [-13, -11, -2, 7]],
            [['s.cuFlags', (SPELL_CU_TRIGGERED | CUSTOM_EXCLUDE_FOR_LISTVIEW), '&'], 0],
            [
                'OR',
                // Glyphs, Proficiencies
                ['s.reqClassMask', $cl->toMask(), '&'],
                // Abilities / Talents
                ['s.skillLine1', $this->subject->getField('skills')],
                ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->subject->getField('skills')]]
            ],
            [                                               // last rank or unranked
                'OR',
                ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
                ['s.rankNo', 0]
            ]
        );

        $genSpells = new SpellList($conditions);
        if (!$genSpells->error)
        {
            $this->extendGlobalData($genSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'            => $genSpells->getListviewData(),
                'id'              => 'spells',
                'name'            => '$LANG.tab_spells',
                'visibleCols'     => ['level', 'schools', 'type', 'classes'],
                'hiddenCols'      => ['reagents', 'skill'],
                'sort'            => ['-level', 'type', 'name'],
                'computeDataFunc' => '$Listview.funcBox.initSpellFilter',
                'onAfterCreate'   => '$Listview.funcBox.addSpellIndicator'
            ), SpellList::$brickFile));
        }

        // tab: items (grouped)
        $conditions = array(
            ['requiredClass', 0, '>'],
            ['requiredClass', $cl->toMask(), '&'],
            [['requiredClass', ChrClass::MASK_ALL, '&'], ChrClass::MASK_ALL, '!'],
            ['itemset', 0]
        );

        $items = new ItemList($conditions);
        if (!$items->error)
        {
            $this->extendGlobalData($items->getJSGlobals());

            $hiddenCols = null;
            if ($items->hasDiffFields('requiredRace'))
                $hiddenCols = ['side'];

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'            => $items->getListviewData(),
                'id'              => 'items',
                'name'            => '$LANG.tab_items',
                'visibleCols'     => ['dps', 'armor', 'slot'],
                'hiddenCols'      => $hiddenCols,
                'computeDataFunc' => '$Listview.funcBox.initSubclassFilter',
                'onAfterCreate'   => '$Listview.funcBox.addSubclassIndicator',
                'note'            => sprintf(Util::$filterResultString, '?items&filter=cr=152;crs='.$this->typeId.';crv=0'),
                '_truncated'      => 1
            ), ItemList::$brickFile));
        }

        // tab: quests
        $conditions = array(
            ['reqClassMask', $cl->toMask(), '&'],
            [['reqClassMask', ChrClass::MASK_ALL, '&'], ChrClass::MASK_ALL, '!']
        );

        $quests = new QuestList($conditions);
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $quests->getListviewData(),
                'sort' => ['reqlevel', 'name']
            ), QuestList::$brickFile));
        }

        // tab: itemsets
        $sets = new ItemsetList(array(['classMask', $cl->toMask(), '&']));
        if (!$sets->error)
        {
            $this->extendGlobalData($sets->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'       => $sets->getListviewData(),
                'note'       => sprintf(Util::$filterResultString, '?itemsets&filter=cl='.$this->typeId),
                'hiddenCols' => ['classes'],
                'sort'       => ['-level', 'name']
            ), ItemsetList::$brickFile));
        }

        // tab: trainers
        $conditions = array(
            ['npcflag', NPC_FLAG_TRAINER | NPC_FLAG_CLASS_TRAINER, '&'],
            ['trainerType', 0],                             // trains class spells
            ['trainerRequirement', $this->typeId]
        );

        $trainer = new CreatureList($conditions);
        if (!$trainer->error)
        {
            $this->addDataLoader('zones');
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $trainer->getListviewData(),
                'id'   => 'trainers',
                'name' => '$LANG.tab_trainers'
            ), CreatureList::$brickFile));
        }

        // tab: races
        $races = new CharRaceList(array(['classMask', $cl->toMask(), '&']));
        if (!$races->error)
            $this->lvTabs->addListviewTab(new Listview(['data' => $races->getListviewData()], CharRaceList::$brickFile));

        // tab: criteria-of
        $conditions = array(
            'AND',
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_HK_CLASS],
            ['ac.value1', $this->typeId]
        );

        if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` IN (?a) AND `value1` = ?d', [ACHIEVEMENT_CRITERIA_DATA_TYPE_S_PLAYER_CLASS_RACE, ACHIEVEMENT_CRITERIA_DATA_TYPE_T_PLAYER_CLASS_RACE], $this->typeId))
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

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::CHR_CLASS, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }
}

?>
