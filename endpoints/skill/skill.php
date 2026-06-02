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

    private SkillEntry $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new SkillEntry($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('skill'), Lang::skill('notFound'));

        $this->h1 = $this->subject->name;

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_cat = $this->subject->typeCat;


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

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        // id
        $infobox[] = Lang::skill('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->iconId)
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.($this->subject->name)(Locale::EN).'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $this->headIcons  = [$this->subject->icon];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        if ($_ = $this->subject->description)
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
                [DB::OR, ['s.reagent1', 0, '>'], ['s.reagent2', 0, '>'], ['s.reagent3', 0, '>'], ['s.reagent4', 0, '>'], ['s.reagent5', 0, '>'], ['s.reagent6', 0, '>'], ['s.reagent7', 0, '>'], ['s.reagent8', 0, '>']],
                [DB::OR, ['s.skillLine1', $this->typeId], [DB::AND, ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->typeId]]]
            );

            $recipes = new SpellContainer($condition);      // also relevant for 3
            if (!$recipes->error)
            {
                $this->extendGlobalData($recipes->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $recipes->getListviewData(),
                    'id'          => 'recipes',
                    'name'        => '$LANG.tab_recipes',
                    'visibleCols' => ['reagents', 'source'],
                    'note'        =>  sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.$this->typeId.'&filter=cr=20;crs=1;crv=0')
                ), SpellEntry::$brickFile));
            }

            // tab: recipe Items [items] (Books)
            $filterRecipe = [null, SKILL_LEATHERWORKING, SKILL_TAILORING, SKILL_ENGINEERING, SKILL_BLACKSMITHING, SKILL_COOKING, SKILL_ALCHEMY, SKILL_FIRST_AID, SKILL_ENCHANTING, SKILL_FISHING, SKILL_JEWELCRAFTING, SKILL_INSCRIPTION, SKILL_MINING, SKILL_HERBALISM];
            $conditions   = array(
                ['requiredSkill', $this->typeId],
                ['class', ITEM_CLASS_RECIPE]
            );

            $recipeItems = new ItemContainer($conditions);
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

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemEntry::$brickFile));
            }

            // tab: crafted items [items]
            $created = [];
            foreach ($recipes->iterate() as $entry)
                foreach ($entry->canCreateItem() as $idx)
                    $created[] = $entry->effectCreateItemId[$idx];

            if ($created)
            {
                $created = new ItemContainer(array(['id', $created]));
                if (!$created->error)
                {
                    $this->extendGlobalData($created->getJSGlobals(GLOBALINFO_SELF));

                    $tabData = array(
                        'data' => $created->getListviewData(),
                        'id'   => 'crafted-items',
                        'name' => '$LANG.tab_crafteditems',
                    );

                    if (!is_null($_ = ItemFilter::getCriteriaIndex(86, $this->typeId)))
                        $tabData['note'] = sprintf(Util::$filterResultString, "?items&filter=cr=86;crs=".$_.";crv=0");

                    $this->lvTabs->addListviewTab(new Listview($tabData, ItemEntry::$brickFile));
                }
            }

            // tab: required by [item]
            $conditions = array(
                ['requiredSkill', $this->typeId],
                ['class', ITEM_CLASS_RECIPE, '!']
            );

            $reqBy = new ItemContainer($conditions);
            if (!$reqBy->error)
            {
                $this->extendGlobalData($reqBy->getJSGlobals(GLOBALINFO_SELF));

                $tabData = array(
                    'data' => $reqBy->getListviewData(),
                    'id'   => 'required-by',
                    'name' => '$LANG.tab_requiredby',
                );

                if (!is_null($_ = ItemFilter::getCriteriaIndex(99, $this->typeId)))
                    $tabData['note'] = sprintf(Util::$filterResultString, "?items&filter=cr=99:168;crs=".$_.":2;crv=0:0");

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemEntry::$brickFile));
            }

            // tab: required by [itemset]
            $reqBy = new ItemsetContainer(array(['skillId', $this->typeId]));
            if (!$reqBy->error)
            {
                $this->extendGlobalData($reqBy->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $reqBy->getListviewData(),
                    'id'   => 'required-by-set',
                    'name' => '$LANG.tab_requiredby'
                ), ItemsetEntry::$brickFile));
            }
        }

        // tab: modified by [spell]
        $conditions = array(
            DB::OR,
            [DB::AND, ['effect1AuraId', [SPELL_AURA_MOD_SKILL, SPELL_AURA_MOD_SKILL_TALENT]], ['effect1MiscValue', $this->typeId]],
            [DB::AND, ['effect2AuraId', [SPELL_AURA_MOD_SKILL, SPELL_AURA_MOD_SKILL_TALENT]], ['effect2MiscValue', $this->typeId]],
            [DB::AND, ['effect3AuraId', [SPELL_AURA_MOD_SKILL, SPELL_AURA_MOD_SKILL_TALENT]], ['effect3MiscValue', $this->typeId]]
        );
        $modBy = new SpellContainer($conditions);
        if (!$modBy->error)
        {
            $this->extendGlobalData($modBy->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'       => $modBy->getListviewData(),
                'id'         => 'modified-by',
                'name'       => '$LANG.tab_modifiedby',
                'hiddenCols' => ['skill'],
            ), SpellEntry::$brickFile));
        }

        // tab: spells [spells] (exclude first tab)
        $reqClass  = 0x0;
        $reqRace   = 0x0;
        $condition = array(
            [DB::AND, ['s.reagent1', 0], ['s.reagent2', 0], ['s.reagent3', 0], ['s.reagent4', 0], ['s.reagent5', 0], ['s.reagent6', 0], ['s.reagent7', 0], ['s.reagent8', 0]],
            [DB::OR,  ['s.skillLine1', $this->typeId], [DB::AND, ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->typeId]]]
        );

        foreach (Game::$skillLineMask as $line1 => $sets)
            foreach ($sets as $idx => [, $skillLineId])
                if ($skillLineId == $this->typeId)
                {
                    $condition[1][] = array(DB::AND, ['s.skillLine1', $line1], ['s.skillLine2OrMask', 1 << $idx, '&']);
                    break 2;
                }

        $spells = new SpellContainer($condition);
        if (!$spells->error)
        {
            foreach ($spells->iterate() as $entry)
            {
                $reqClass |= $entry->reqClassMask;
                $reqRace  |= $entry->reqRaceMask;
            }

            $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF));

            $tabData = array(
                'data'        => $spells->getListviewData(),
                'visibleCols' => ['source']
            );

            if ($this->typeId != 769)               // Internal
                $tabData['note'] = match ($_cat)
                {
                    -4      => sprintf(Util::$filterResultString, '?spells=-4'),
                     7      => sprintf(Util::$filterResultString, '?spells=7.'.ChrClass::fromMask($reqClass)[0].'.'.$this->typeId), // doesn't matter what spell; reqClass should be identical for all Class Spells
                     9, 11  => sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.$this->typeId),
                    default => null
                };

            $this->lvTabs->addListviewTab(new Listview($tabData, SpellEntry::$brickFile));
        }

        // tab: trainers [npcs]
        if (in_array($_cat, [-5, 6, 7, 8, 9, 11]))
        {
            $mask = 0;
            foreach (Game::$skillLineMask[-3] as $idx => [, $skillLineId])
                if ($skillLineId == $this->typeId)
                    $mask |= 1 << $idx;

            $spellIds = DB::Aowow()->selectCol(
               'SELECT `id` FROM ::spell WHERE %if', $mask, '(`skillLine1` = -3 AND `skillLine2OrMask` = %i) OR', $mask, '%end (`skillLine1` = %i OR (`skillLine1` > 0 AND `skillLine2OrMask` = %i))',
                $this->typeId,
                $this->typeId
            );

            $list = $spellIds ? DB::World()->selectCol('SELECT cdt.`CreatureId` FROM creature_default_trainer cdt JOIN trainer_spell ts ON ts.`TrainerId` = cdt.`TrainerId` WHERE ts.`SpellID` IN %in', $spellIds) : [];
            if ($list)
            {
                $trainer = new CreatureContainer(array(['ct.id', $list], ['s.guid', NULL, '!'], ['ct.npcflag', 0x10, '&']));

                if (!$trainer->error)
                {
                    $this->extendGlobalData($trainer->getJSGlobals());

                    $this->addDataLoader('zones');
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data' => $trainer->getListviewData(),
                        'id'   => 'trainer',
                        'name' => '$LANG.tab_trainers',
                    ), CreatureEntry::$brickFile));
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
            $quests = new QuestContainer(array(['questSortId', -$sort]));
            if (!$quests->error)
            {
                $this->extendGlobalData($quests->getJSGlobals());
                $this->lvTabs->addListviewTab(new Listview(['data' => $quests->getListviewData()], QuestEntry::$brickFile));
            }
        }

        // tab: related classes (apply classes from [spells])
        if ($class = ChrClass::fromMask($reqClass))
        {
            $classes = new CharClassContainer(array(['id', $class]));
            if (!$classes->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $classes->getListviewData()], CharClassEntry::$brickFile));
        }

        // tab: related races (apply races from [spells])
        if ($race = ChrRace::fromMask($reqRace))
        {
            $races = new CharRaceContainer(array(['id', $race]));
            if (!$races->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $races->getListviewData()], CharRaceEntry::$brickFile));
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::SKILL, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJSGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }
    }
}

?>
