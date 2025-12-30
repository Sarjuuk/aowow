<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'skill';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 14];

    public int $type   = Type::SKILL;
    public int $typeId = 0;

    private SkillList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new SkillList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('skill'), Lang::skill('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_cat = $this->subject->getField('typeCat');


        /*************/
        /* Menu Path */
        /*************/

        if (in_array($this->typeId, SKILLS_TRADE_PRIMARY) || in_array($this->typeId, SKILLS_TRADE_SECONDARY))
            $this->breadcrumb[] = $this->typeId;
        else
            $this->breadcrumb[] = $_cat;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('skill')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // id
        $infobox[] = Lang::skill('id') . $this->typeId;

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
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        if ($_ = $this->subject->getField('description', true))
            $this->extraText = new Markup($_, ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN], 'text-generic');


        /**************/
        /* Extra Tabs */
        /**************/

        parent::generate();

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        if (in_array($_cat, [-5, 9, 11]))
        {
            // tab: recipes [spells] (crafted)
            $condition = array(
                ['OR', ['s.reagent1', 0, '>'], ['s.reagent2', 0, '>'], ['s.reagent3', 0, '>'], ['s.reagent4', 0, '>'], ['s.reagent5', 0, '>'], ['s.reagent6', 0, '>'], ['s.reagent7', 0, '>'], ['s.reagent8', 0, '>']],
                ['OR', ['s.skillLine1', $this->typeId], ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->typeId]]]
            );

            $recipes = new SpellList($condition);           // also relevant for 3
            if (!$recipes->error)
            {
                $this->extendGlobalData($recipes->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $recipes->getListviewData(),
                    'id'          => 'recipes',
                    'name'        => '$LANG.tab_recipes',
                    'visibleCols' => ['reagents', 'source'],
                    'note'        =>  sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.$this->typeId.'&filter=cr=20;crs=1;crv=0')
                ), SpellList::$brickFile));
            }

            // tab: recipe Items [items] (Books)
            $filterRecipe = [null, SKILL_LEATHERWORKING, SKILL_TAILORING, SKILL_ENGINEERING, SKILL_BLACKSMITHING, SKILL_COOKING, SKILL_ALCHEMY, SKILL_FIRST_AID, SKILL_ENCHANTING, SKILL_FISHING, SKILL_JEWELCRAFTING, SKILL_INSCRIPTION, SKILL_MINING, SKILL_HERBALISM];
            $conditions   = array(
                ['requiredSkill', $this->typeId],
                ['class', ITEM_CLASS_RECIPE]
            );

            $recipeItems = new ItemList($conditions);
            if (!$recipeItems->error)
            {
                $this->extendGlobalData($recipeItems->getJSGlobals(GLOBALINFO_SELF));

                $tabData = array(
                    'data' => $recipeItems->getListviewData(),
                    'id'   => 'recipe-items',
                    'name' => '$LANG.tab_recipeitems',
                );

                if ($_ = array_search($this->typeId, $filterRecipe))
                    $tabData['note'] = sprintf(Util::$filterResultString, "?items=9.".$_);

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
            }

            // tab: crafted items [items]
            $created = [];
            foreach ($recipes->iterate() as $__)
                if ($idx = $recipes->canCreateItem())
                    foreach ($idx as $i)
                        $created[] = $recipes->getField('effect'.$i.'CreateItemId');

            if ($created)
            {
                $created = new ItemList(array(['i.id', $created]));
                if (!$created->error)
                {
                    $this->extendGlobalData($created->getJSGlobals(GLOBALINFO_SELF));

                    $tabData = array(
                        'data' => $created->getListviewData(),
                        'id'   => 'crafted-items',
                        'name' => '$LANG.tab_crafteditems',
                    );

                    if (!is_null($_ = ItemListFilter::getCriteriaIndex(86, $this->typeId)))
                        $tabData['note'] = sprintf(Util::$filterResultString, "?items&filter=cr=86;crs=".$_.";crv=0");

                    $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
                }
            }

            // tab: required by [item]
            $conditions = array(
                ['requiredSkill', $this->typeId],
                ['class', ITEM_CLASS_RECIPE, '!']
            );

            $reqBy = new ItemList($conditions);
            if (!$reqBy->error)
            {
                $this->extendGlobalData($reqBy->getJSGlobals(GLOBALINFO_SELF));

                $tabData = array(
                    'data' => $reqBy->getListviewData(),
                    'id'   => 'required-by',
                    'name' => '$LANG.tab_requiredby',
                );

                if (!is_null($_ = ItemListFilter::getCriteriaIndex(99, $this->typeId)))
                    $tabData['note'] = sprintf(Util::$filterResultString, "?items&filter=cr=99:168;crs=".$_.":2;crv=0:0");

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
            }

            // tab: required by [itemset]
            $reqBy = new ItemsetList(array(['skillId', $this->typeId]));
            if (!$reqBy->error)
            {
                $this->extendGlobalData($reqBy->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $reqBy->getListviewData(),
                    'id'   => 'required-by-set',
                    'name' => '$LANG.tab_requiredby'
                ), ItemsetList::$brickFile));
            }
        }

        // tab: modified by [spell]
        $conditions = array(
            'OR',
            ['AND', ['effect1AuraId', [SPELL_AURA_MOD_SKILL, SPELL_AURA_MOD_SKILL_TALENT]], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2AuraId', [SPELL_AURA_MOD_SKILL, SPELL_AURA_MOD_SKILL_TALENT]], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3AuraId', [SPELL_AURA_MOD_SKILL, SPELL_AURA_MOD_SKILL_TALENT]], ['effect3MiscValue', $this->typeId]]
        );
        $modBy = new SpellList($conditions);
        if (!$modBy->error)
        {
            $this->extendGlobalData($modBy->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'       => $modBy->getListviewData(),
                'id'         => 'modified-by',
                'name'       => '$LANG.tab_modifiedby',
                'hiddenCols' => ['skill'],
            ), SpellList::$brickFile));
        }

        // tab: spells [spells] (exclude first tab)
        $reqClass  = 0x0;
        $reqRace   = 0x0;
        $condition = array(
            ['AND', ['s.reagent1', 0], ['s.reagent2', 0], ['s.reagent3', 0], ['s.reagent4', 0], ['s.reagent5', 0], ['s.reagent6', 0], ['s.reagent7', 0], ['s.reagent8', 0]],
            ['OR',  ['s.skillLine1', $this->typeId], ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->typeId]]]
        );

        foreach (Game::$skillLineMask as $line1 => $sets)
            foreach ($sets as $idx => [, $skillLineId])
                if ($skillLineId == $this->typeId)
                {
                    $condition[1][] = array('AND', ['s.skillLine1', $line1], ['s.skillLine2OrMask', 1 << $idx, '&']);
                    break 2;
                }

        $spells = new SpellList($condition);
        if (!$spells->error)
        {
            foreach ($spells->iterate() as $__)
            {
                $reqClass |= $spells->getField('reqClassMask');
                $reqRace  |= $spells->getField('reqRaceMask');
            }

            $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF));

            $tabData = array(
                'data'        => $spells->getListviewData(),
                'visibleCols' => ['source']
            );

            if ($this->typeId != 769)               // Internal
                $tabData['note'] = match ($_cat)
                {
                    -4, 7   => sprintf(Util::$filterResultString, '?spells=-4'),
                     7      => sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.ChrClass::fromMask($reqClass)[0].'.'.$this->typeId),  // doesn't matter what spell; reqClass should be identical for all Class Spells
                     9, 11  => sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.$this->typeId),
                    default => null
                };

            $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
        }

        // tab: trainers [npcs]
        if (in_array($_cat, [-5, 6, 7, 8, 9, 11]))
        {
            $mask = 0;
            foreach (Game::$skillLineMask[-3] as $idx => [, $skillLineId])
                if ($skillLineId == $this->typeId)
                    $mask |= 1 << $idx;

            $spellIds = DB::Aowow()->selectCol(
               'SELECT `id` FROM ?_spell WHERE (`skillLine1` = ?d OR (`skillLine1` > 0 AND `skillLine2OrMask` = ?d) {OR (`skillLine1` = -3 AND `skillLine2OrMask` = ?d)})',
                $this->typeId,
                $this->typeId,
                $mask ?: DBSIMPLE_SKIP
            );

            $list = $spellIds ? DB::World()->selectCol('SELECT cdt.`CreatureId` FROM creature_default_trainer cdt JOIN trainer_spell ts ON ts.`TrainerId` = cdt.`TrainerId` WHERE ts.`SpellID` IN (?a)', $spellIds) : [];
            if ($list)
            {
                $trainer = new CreatureList(array(['ct.id', $list], ['s.guid', NULL, '!'], ['ct.npcflag', 0x10, '&']));

                if (!$trainer->error)
                {
                    $this->extendGlobalData($trainer->getJSGlobals());

                    $this->addDataLoader('zones');
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data' => $trainer->getListviewData(),
                        'id'   => 'trainer',
                        'name' => '$LANG.tab_trainers',
                    ), CreatureList::$brickFile));
                }
            }
        }

        // tab: quests [quests]
        // only for professions
        $sort = match ($this->typeId)
        {
            SKILL_HERBALISM      =>  24,
            SKILL_FISHING        => 101,
            SKILL_BLACKSMITHING  => 121,
            SKILL_ALCHEMY        => 181,
            SKILL_LEATHERWORKING => 182,
            SKILL_ENGINEERING    => 201,
            SKILL_TAILORING      => 264,
            SKILL_COOKING        => 304,
            SKILL_FIRST_AID      => 324,
            SKILL_INSCRIPTION    => 371,
            SKILL_JEWELCRAFTING  => 373,
            default              => 0
        };

        if ($sort)
        {
            $quests = new QuestList(array(['questSortId', -$sort]));
            if (!$quests->error)
            {
                $this->extendGlobalData($quests->getJSGlobals());
                $this->lvTabs->addListviewTab(new Listview(['data' => $quests->getListviewData()], QuestList::$brickFile));
            }
        }

        // tab: related classes (apply classes from [spells])
        if ($class = ChrClass::fromMask($reqClass))
        {
            $classes = new CharClassList(array(['id', $class]));
            if (!$classes->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $classes->getListviewData()], CharClassList::$brickFile));
        }

        // tab: related races (apply races from [spells])
        if ($race = ChrRace::fromMask($reqRace))
        {
            $races = new CharRaceList(array(['id', $race]));
            if (!$races->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $races->getListviewData()], CharRaceList::$brickFile));
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::SKILL, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }
    }
}

?>
