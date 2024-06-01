<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 8: Pets     g_initPath()
//  tabid 0: Database g_initHeader()
class PetPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = Type::PET;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 8];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/swfobject.js']];

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new PetList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('pet'), Lang::pet('notFound'));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('type');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('pet')));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=zones']);

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // level range
        $infobox[] = Lang::game('level').Lang::main('colon').$this->subject->getField('minLevel').' - '.$this->subject->getField('maxLevel');

        // exotic
        if ($this->subject->getField('exotic'))
            $infobox[] = '[url=?spell=53270]'.Lang::pet('exotic').'[/url]';

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        /****************/
        /* Main Content */
        /****************/

        $this->infobox    = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
        $this->headIcons  = [$this->subject->getField('iconString')];
        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_TALENT  => ['href' => '?petcalc#'.Util::$tcEncoding[(int)($this->typeId / 10)] . Util::$tcEncoding[(2 * ($this->typeId % 10) + ($this->subject->getField('exotic') ? 1 : 0))], 'pet' => true]
        );

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: tameable & gallery
        $condition = array(
            ['ct.type', 1],                                 // Beast
            ['ct.typeFlags', 0x1, '&'],                     // tameable
            ['ct.family', $this->typeId],                   // displayed petType
            [
                'OR',                                       // at least neutral to at least one faction
                ['ft.A', 1, '<'],
                ['ft.H', 1, '<']
            ]
        );
        $tng = new CreatureList($condition);

        $this->lvTabs[] = [CreatureList::$brickFile, array(
            'data'        => array_values($tng->getListviewData(NPCINFO_TAMEABLE)),
            'name'        => '$LANG.tab_tameable',
            'hiddenCols'  => ['type'],
            'visibleCols' => ['skin'],
            'note'        => sprintf(Util::$filterResultString, '?npcs=1&filter=fa=38'),
            'id'          => 'tameable'
        )];
        $this->lvTabs[] = ['model', array(
            'data'        => array_values($tng->getListviewData(NPCINFO_MODEL))
        )];

        // tab: diet
        $list = [];
        $mask = $this->subject->getField('foodMask');
        for ($i = 1; $i < 9; $i++)
            if ($mask & (1 << ($i - 1)))
                $list[] = $i;

        $food = new ItemList(array(['i.subClass', [5, 8]], ['i.FoodType', $list], Cfg::get('SQL_LIMIT_NONE')));
        $this->extendGlobalData($food->getJSGlobals());

        $this->lvTabs[] = [ItemList::$brickFile, array(
            'data'       => array_values($food->getListviewData()),
            'name'       => '$LANG.diet',
            'hiddenCols' => ['source', 'slot', 'side'],
            'sort'       => ['level'],
            'id'         => 'diet'
        )];

        // tab: spells
        $mask = 0x0;
        foreach (Game::$skillLineMask[-1] as $idx => $pair)
        {
            if ($pair[0] == $this->typeId)
            {
                $mask = 1 << $idx;
                break;
            }
        }
        $conditions = [
            ['s.typeCat', -3],                              // Pet-Ability
            [
                'OR',
                // match: first skillLine
                ['skillLine1', $this->subject->getField('skillLineId')],
                // match: second skillLine (if not mask)
                ['AND', ['skillLine1', 0, '>'], ['skillLine2OrMask', $this->subject->getField('skillLineId')]],
                // match: skillLineMask (if mask)
                ['AND', ['skillLine1', -1], ['skillLine2OrMask', $mask, '&']]
            ]
        ];

        $spells = new SpellList($conditions);
        $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF));

        $this->lvTabs[] = [SpellList::$brickFile, array(
            'data'        => array_values($spells->getListviewData()),
            'name'        => '$LANG.tab_abilities',
            'visibleCols' => ['schools', 'level'],
            'id'          => 'abilities'
        )];

        // tab: talents
        $conditions = array(
            ['s.typeCat', -7],
            [                                                   // last rank or unranked
                'OR',
                ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
                ['s.rankNo', 0]
            ]
        );

        switch ($this->subject->getField('type'))
        {
            case 0: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE0, '&']; break;
            case 1: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE1, '&']; break;
            case 2: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE2, '&']; break;
        }

        $talents = new SpellList($conditions);
        $this->extendGlobalData($talents->getJSGlobals(GLOBALINFO_SELF));

        $this->lvTabs[] = [SpellList::$brickFile, array(
            'data'        => array_values($talents->getListviewData()),
            'visibleCols' => ['tier', 'level'],
            'name'        => '$LANG.tab_talents',
            'id'          => 'talents',
            'sort'        => ['tier', 'name'],
            '_petTalents' => 1
        )];
    }
}

?>
