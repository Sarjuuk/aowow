<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PetBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'pet';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 8];

    public  int    $type       = Type::PET;
    public  int    $typeId     = 0;
    public ?string $expansion  = null;

    private PetList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new PetList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('pet'), Lang::pet('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('type');


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('pet')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // level range
        $infobox[] = Lang::game('level').Lang::main('colon').$this->subject->getField('minLevel').' - '.$this->subject->getField('maxLevel');

        // exotic
        if ($this->subject->getField('exotic'))
            $infobox[] = '[url=?spell=53270]'.Lang::pet('exotic').'[/url]';

        // id
        $infobox[] = Lang::pet('id') . $this->typeId;

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

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: tameable & gallery
        $condition = array(
            ['ct.type', 1],                                 // Beast
            ['ct.typeFlags', NPC_TYPEFLAG_TAMEABLE, '&'],
            ['ct.family', $this->typeId],                   // displayed petType
            [
                'OR',                                       // at least neutral to at least one faction
                ['ft.A', 1, '<'],
                ['ft.H', 1, '<']
            ]
        );
        $tng = new CreatureList($condition);

        $this->addDataLoader('zones');
        $this->lvTabs->addListviewTab(new Listview(array(
            'data'        => $tng->getListviewData(NPCINFO_TAMEABLE),
            'name'        => '$LANG.tab_tameable',
            'hiddenCols'  => ['type'],
            'visibleCols' => ['skin'],
            'note'        => sprintf(Util::$filterResultString, '?npcs=1&filter=fa=38'),
            'id'          => 'tameable'
        ), CreatureList::$brickFile));

        $this->lvTabs->addListviewTab(new Listview(['data' => $tng->getListviewData(NPCINFO_MODEL)], 'model'));

        // tab: diet
        $list = [];
        $mask = $this->subject->getField('foodMask');
        for ($i = 1; $i < 9; $i++)
            if ($mask & (1 << ($i - 1)))
                $list[] = $i;

        $food = new ItemList(array(['i.subClass', [ITEM_SUBCLASS_FOOD, ITEM_SUBCLASS_MISC_CONSUMABLE]], ['i.FoodType', $list]));
        $this->extendGlobalData($food->getJSGlobals());

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'       => $food->getListviewData(),
            'name'       => '$LANG.diet',
            'hiddenCols' => ['source', 'slot', 'side'],
            'sort'       => ['level'],
            'id'         => 'diet'
        ), ItemList::$brickFile));

        // tab: spells
        $mask = 0x0;
        foreach (Game::$skillLineMask[-1] as $idx => [$familyId,])
        {
            if ($familyId == $this->typeId)
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

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'        => $spells->getListviewData(),
            'name'        => '$LANG.tab_abilities',
            'visibleCols' => ['schools', 'level'],
            'id'          => 'abilities'
        ), SpellList::$brickFile));

        // tab: talents
        $conditions = array(
            ['s.typeCat', -7],
            [                                                   // last rank or unranked
                'OR',
                ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
                ['s.rankNo', 0]
            ]
        );

        $conditions[] = match($this->subject->getField('type'))
        {
            PET_TALENT_TYPE_FEROCITY => ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE0, '&'],
            PET_TALENT_TYPE_TENACITY => ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE1, '&'],
            PET_TALENT_TYPE_CUNNING  => ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE2, '&']
        };

        $talents = new SpellList($conditions);
        $this->extendGlobalData($talents->getJSGlobals(GLOBALINFO_SELF));

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'        => $talents->getListviewData(),
            'visibleCols' => ['tier', 'level'],
            'name'        => '$LANG.tab_talents',
            'id'          => 'talents',
            'sort'        => ['tier', 'name'],
            '_petTalents' => 1
        ), SpellList::$brickFile));

        parent::generate();
    }
}

?>
