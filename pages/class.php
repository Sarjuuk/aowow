<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 12: Class    g_initPath()
//  tabId  0: Database g_initHeader()
class ClassPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_CLASS;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 12];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = ['swfobject.js'];

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new CharClassList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('class'), Lang::chrClass('notFound'));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->typeId;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('class')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $infobox   = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));
        $_mask     = 1 << ($this->typeId - 1);
        $tcClassId = [null, 8, 3, 1, 5, 4, 9, 6, 2, 7, null, 0]; // see TalentCalc.js


        /***********/
        /* Infobox */
        /***********/

        // hero class
        if ($this->subject->getField('flags') & 0x40)
            $infobox[] = '[tooltip=tooltip_heroclass]'.Lang::game('heroClass').'[/tooltip]';

        // resource
        if ($this->typeId == 11)                                // special Druid case
            $infobox[] = Lang::game('resources').Lang::main('colon').
            '[tooltip name=powertype1]'.Lang::game('st', 0).', '.Lang::game('st', 31).', '.Lang::game('st', 2).'[/tooltip][span class=tip tooltip=powertype1]'.Util::ucFirst(Lang::spell('powerTypes', 0)).'[/span], '.
            '[tooltip name=powertype2]'.Lang::game('st', 5).', '.Lang::game('st', 8).'[/tooltip][span class=tip tooltip=powertype2]'.Util::ucFirst(Lang::spell('powerTypes', 1)).'[/span], '.
            '[tooltip name=powertype8]'.Lang::game('st', 1).'[/tooltip][span class=tip tooltip=powertype8]'.Util::ucFirst(Lang::spell('powerTypes', 3)).'[/span]';
        else if ($this->typeId == 6)                            // special DK case
            $infobox[] = Lang::game('resources').Lang::main('colon').'[span]'.Util::ucFirst(Lang::spell('powerTypes', 5)).', '.Util::ucFirst(Lang::spell('powerTypes', $this->subject->getField('powerType'))).'[/span]';
        else                                                    // regular case
            $infobox[] = Lang::game('resource').Lang::main('colon').'[span]'.Util::ucFirst(Lang::spell('powerTypes', $this->subject->getField('powerType'))).'[/span]';

        // roles
        $roles = [];
        for ($i = 0; $i < 4; $i++)
            if ($this->subject->getField('roles') & (1 << $i))
                $roles[] = (count($roles) == 2 ? "\n" : '').Lang::game('_roles', $i);

        if ($roles)
            $infobox[] = (count($roles) > 1 ? Lang::game('roles') : Lang::game('role')).Lang::main('colon').implode(', ', $roles);

        // specs
        $specList = [];
        $skills = new SkillList(array(['id', $this->subject->getField('skills')]));
        foreach ($skills->iterate() as $k => $__)
            $specList[$k] = '[icon name='.$skills->getField('iconString').'][url=?spells=7.'.$this->typeId.'.'.$k.']'.$skills->getField('name', true).'[/url][/icon]';

        if ($specList)
            $infobox[] = Lang::game('specs').Lang::main('colon').'[ul][li]'.implode('[/li][li]', $specList).'[/li][/ul]';


        /****************/
        /* Main Content */
        /****************/

        $this->infobox = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
        $this->expansion = Util::$expansionString[$this->subject->getField('expansion')];
        $this->headIcons = ['class_'.strtolower($this->subject->getField('fileString'))];
        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => true,
            BUTTON_TALENT  => ['href' => '?talent#'.Util::$tcEncoding[$tcClassId[$this->typeId] * 3], 'pet' => false],
            BUTTON_FORUM   => false                         // todo (low): CFG_BOARD_URL + X
        );


        /**************/
        /* Extra Tabs */
        /**************/

        // Tab: Spells (grouped)
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
                ['s.reqClassMask', $_mask, '&'],                // Glyphs, Proficiencies
                ['s.skillLine1', $this->subject->getField('skills')],      // Abilities / Talents
                ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->subject->getField('skills')]]
            ],
            [                                                   // last rank or unranked
                'OR',
                ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
                ['s.rankNo', 0]
            ],
            CFG_SQL_LIMIT_NONE
        );

        $genSpells = new SpellList($conditions);
        if (!$genSpells->error)
        {
            $this->extendGlobalData($genSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs[] = ['spell', array(
                'data'            => array_values($genSpells->getListviewData()),
                'id'              => 'spells',
                'name'            => '$LANG.tab_spells',
                'visibleCols'     => ['level', 'schools', 'type', 'classes'],
                'hiddenCols'      => ['reagents', 'skill'],
                'sort'            => ['-level', 'type', 'name'],
                'computeDataFunc' => '$Listview.funcBox.initSpellFilter',
                'onAfterCreate'   => '$Listview.funcBox.addSpellIndicator'
            )];
        }

        // Tab: Items (grouped)
        $conditions = array(
            ['requiredClass', 0, '>'],
            ['requiredClass', $_mask, '&'],
            [['requiredClass', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!'],
            ['itemset', 0],                                     // hmm, do or dont..?
            CFG_SQL_LIMIT_NONE
        );

        $items = new ItemList($conditions);
        if (!$items->error)
        {
            $this->extendGlobalData($items->getJSGlobals());

            $hiddenCols = null;
            if ($items->hasDiffFields(['requiredRace']))
                $hiddenCols = ['side'];

            $this->lvTabs[] = ['item', array(
                'data'            => array_values($items->getListviewData()),
                'id'              => 'items',
                'name'            => '$LANG.tab_items',
                'visibleCols'     => ['dps', 'armor', 'slot'],
                'hiddenCols'      => $hiddenCols,
                'computeDataFunc' => '$Listview.funcBox.initSubclassFilter',
                'onAfterCreate'   => '$Listview.funcBox.addSubclassIndicator',
                'note'            => sprintf(Util::$filterResultString, '?items&filter=cr=152;crs='.$this->typeId.';crv=0'),
                '_truncated'      => 1
            )];
        }

        // Tab: Quests
        $conditions = array(
            ['reqClassMask', $_mask, '&'],
            [['reqClassMask', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!']
        );

        $quests = new QuestList($conditions);
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals());

            $this->lvTabs[] = ['quest', array(
                'data' => array_values($quests->getListviewData()),
                'sort' => ['reqlevel', 'name']
            )];
        }

        // Tab: Itemsets
        $sets = new ItemsetList(array(['classMask', $_mask, '&']));
        if (!$sets->error)
        {
            $this->extendGlobalData($sets->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = ['itemset', array(
                'data'       => array_values($sets->getListviewData()),
                'note'       => sprintf(Util::$filterResultString, '?itemsets&filter=cl='.$this->typeId),
                'hiddenCols' => ['classes'],
                'sort'       => ['-level', 'name']
            )];
        }

        // Tab: Trainer
        $conditions = array(
            ['npcflag', 0x30, '&'],                             // is trainer
            ['trainerType', 0],                                 // trains class spells
            ['trainerRequirement', $this->typeId]
        );

        $trainer = new CreatureList($conditions);
        if (!$trainer->error)
        {
            $this->lvTabs[] = ['creature', array(
                'data' => array_values($trainer->getListviewData()),
                'id'   => 'trainers',
                'name' => '$LANG.tab_trainers'
            )];
        }

        // Tab: Races
        $races = new CharRaceList(array(['classMask', $_mask, '&']));
        if (!$races->error)
            $this->lvTabs[] = ['race', ['data' => array_values($races->getListviewData())]];
    }
}

?>
