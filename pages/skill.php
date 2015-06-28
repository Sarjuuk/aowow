<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 14: Skill    g_initPath()
//  tabId  0: Database g_initHeader()
class SkillPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_SKILL;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 14];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    private   $cat           = 0;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new SkillList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('skill'), Lang::skill('notFound'));

        $this->name = $this->subject->getField('name', true);
        $this->cat  = $this->subject->getField('typeCat');
    }

    protected function generatePath()
    {
        $this->path[] = (in_array($this->cat, [9, 11]) || $this->typeId == 762) ? $this->typeId : $this->cat;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('skill')));
    }

    protected function generateContent()
    {
        /****************/
        /* Main Content */
        /****************/

        $this->headIcons  = [$this->subject->getField('iconString')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true
        );

        if ($_ = $this->subject->getField('description', true))
            $this->extraText = $_;

        /**************/
        /* Extra Tabs */
        /**************/

        if (in_array($this->cat, [-5, 9, 11]))
        {
            // tab: recipes [spells] (crafted)
            $condition = array(
                ['OR', ['s.reagent1', 0, '>'], ['s.reagent2', 0, '>'], ['s.reagent3', 0, '>'], ['s.reagent4', 0, '>'], ['s.reagent5', 0, '>'], ['s.reagent6', 0, '>'], ['s.reagent7', 0, '>'], ['s.reagent8', 0, '>']],
                ['OR', ['s.skillLine1', $this->typeId], ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->typeId]]],
                CFG_SQL_LIMIT_NONE
            );

            $recipes = new SpellList($condition);           // also relevant for 3
            if (!$recipes->error)
            {
                $this->extendGlobalData($recipes->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $this->lvTabs[] = array(
                    'file'   => 'spell',
                    'data'   => $recipes->getListviewData(),
                    'params' => array(
                        'id'          => 'recipes',
                        'name'        => '$LANG.tab_recipes',
                        'visibleCols' => "$['reagents', 'source']",
                        'note'        =>  sprintf(Util::$filterResultString, '?spells='.$this->cat.'.'.$this->typeId.'&filter=cr=20;crs=1;crv=0')
                    )
                );
            }

            // tab: recipe Items [items] (Books)
            $filterRecipe = [null, 165, 197, 202, 164, 185, 171, 129, 333, 356, 755, 773, 186, 182];
            $conditions   = array(
                ['requiredSkill', $this->typeId],
                ['class', ITEM_CLASS_RECIPE],
                CFG_SQL_LIMIT_NONE
            );

            $recipeItems = new ItemList($conditions);
            if (!$recipeItems->error)
            {
                $this->extendGlobalData($recipeItems->getJSGlobals(GLOBALINFO_SELF));

                if ($_ = array_search($this->typeId, $filterRecipe))
                    $_ = sprintf(Util::$filterResultString, "?items=9.".$_);

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $recipeItems->getListviewData(),
                    'params' => array(
                        'id'   => 'recipe-items',
                        'name' => '$LANG.tab_recipeitems',
                        'note' => $_
                    )
                );
            }

            // tab: crafted items [items]
            $filterItem = [null, 171, 164, 185, 333, 202, 129, 755, 165, 186, 197, null, null, 356, 182, 773];
            $created    = [];
            foreach ($recipes->iterate() as $__)
                if ($idx = $recipes->canCreateItem())
                    foreach ($idx as $i)
                        $created[] = $recipes->getField('effect'.$i.'CreateItemId');

            if ($created)
            {
                $created = new ItemList(array(['i.id', $created], CFG_SQL_LIMIT_NONE));
                if (!$created->error)
                {
                    $this->extendGlobalData($created->getJSGlobals(GLOBALINFO_SELF));

                    if ($_ = array_search($this->typeId, $filterItem))
                        $_ = sprintf(Util::$filterResultString, "?items&filter=cr=86;crs=".$_.";crv=0");

                    $this->lvTabs[] = array(
                        'file'   => 'item',
                        'data'   => $created->getListviewData(),
                        'params' => array(
                            'id'   => 'crafted-items',
                            'name' => '$LANG.tab_crafteditems',
                            'note' => $_
                        )
                    );
                }
            }

            // tab: required by [item]
            $conditions = array(
                ['requiredSkill', $this->typeId],
                ['class', ITEM_CLASS_RECIPE, '!'],
                CFG_SQL_LIMIT_NONE
            );

            $reqBy = new ItemList($conditions);
            if (!$reqBy->error)
            {
                $this->extendGlobalData($reqBy->getJSGlobals(GLOBALINFO_SELF));

                if ($_ = array_search($this->typeId, $filterItem))
                    $_ = sprintf(Util::$filterResultString, "?items&filter=cr=99:168;crs=".$_.":2;crv=0:0");

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $reqBy->getListviewData(),
                    'params' => array(
                        'id'   => 'required-by',
                        'name' => '$LANG.tab_requiredby',
                        'note' => $_
                    )
                );
            }

            // tab: required by [itemset]
            $conditions = array(
                ['skillId', $this->typeId],
                CFG_SQL_LIMIT_NONE
            );

            $reqBy = new ItemsetList($conditions);
            if (!$reqBy->error)
            {
                $this->extendGlobalData($reqBy->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs[] = array(
                    'file'   => 'itemset',
                    'data'   => $reqBy->getListviewData(),
                    'params' => array(
                        'id'   => 'required-by-set',
                        'name' => '$LANG.tab_requiredby'
                    )
                );
            }
        }

        // tab: spells [spells] (exclude first tab)
        $reqClass  = 0x0;
        $reqRace   = 0x0;
        $condition = array(
            ['AND', ['s.reagent1', 0], ['s.reagent2', 0], ['s.reagent3', 0], ['s.reagent4', 0], ['s.reagent5', 0], ['s.reagent6', 0], ['s.reagent7', 0], ['s.reagent8', 0]],
            ['OR',  ['s.skillLine1', $this->typeId], ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->typeId]]],
            CFG_SQL_LIMIT_NONE
        );

        foreach (Util::$skillLineMask as $line1 => $sets)
            foreach ($sets as $idx => $set)
                if ($set[1] == $this->typeId)
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

            $lv = array(
                'file'   => 'spell',
                'data'   => $spells->getListviewData(),
                'params' => ['visibleCols' => "$['source']"]
            );

            switch ($this->cat)
            {
                case -4:
                    $lv['params']['note'] = sprintf(Util::$filterResultString, '?spells=-4');
                    break;
                case  7:
                    if ($this->typeId != 769)               // Internal
                        $lv['params']['note'] = sprintf(Util::$filterResultString, '?spells='.$this->cat.'.'.(log($reqClass, 2) + 1).'.'.$this->typeId);  // doesn't matter what spell; reqClass should be identical for all Class Spells
                    break;
                case  9:
                case 11:
                    $lv['params']['note'] = sprintf(Util::$filterResultString, '?spells='.$this->cat.'.'.$this->typeId);
                    break;
            }

            $this->lvTabs[] = $lv;
        }

        // tab: trainers [npcs]
        if (in_array($this->cat, [-5, 6, 7, 8, 9, 11]))
        {
            $list = [];
            if (!empty(Util::$trainerTemplates[TYPE_SKILL][$this->typeId]))
                $list = DB::World()->selectCol('SELECT DISTINCT ID FROM npc_trainer WHERE SpellID IN (?a) AND ID < 200000', Util::$trainerTemplates[TYPE_SKILL][$this->typeId]);
            else
            {
                $mask = 0;
                foreach (Util::$skillLineMask[-3] as $idx => $pair)
                    if ($pair[1] == $this->typeId)
                        $mask |= 1 << $idx;

                $spellIds = DB::Aowow()->selectCol(
                    'SELECT id FROM ?_spell WHERE typeCat IN (-11, 9) AND (skillLine1 = ?d OR (skillLine1 > 0 AND skillLine2OrMask = ?d) {OR (skillLine1 = -3 AND skillLine2OrMask = ?d)})',
                    $this->typeId,
                    $this->typeId,
                    $mask ?: DBSIMPLE_SKIP
                );

                $list = $spellIds ? DB::World()->selectCol('
                    SELECT    IF(t1.ID > 200000, t2.ID, t1.ID)
                    FROM      npc_trainer t1
                    LEFT JOIN npc_trainer t2 ON t2.SpellID = -t1.ID
                    WHERE     t1.SpellID IN (?a)',
                    $spellIds
                ) : [];
            }

            if ($list)
            {
                $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

                $trainer = new CreatureList(array(CFG_SQL_LIMIT_NONE, ['ct.id', $list], ['s.guid', NULL, '!'], ['ct.npcflag', 0x10, '&']));

                if (!$trainer->error)
                {
                    $this->extendGlobalData($trainer->getJSGlobals());

                    $this->lvTabs[] = array(
                        'file'   => 'creature',
                        'data'   => $trainer->getListviewData(),
                        'params' => array(
                            'id'   => 'trainer',
                            'name' => '$LANG.tab_trainers',
                        )
                    );
                }
            }
        }

        // tab: quests [quests]
        if (in_array($this->cat, [9, 11]))                  // only for professions
        {
            $sort = 0;
            switch ($this->typeId)
            {
                case 182: $sort =  24; break;               // Herbalism
                case 356: $sort = 101; break;               // Fishing
                case 164: $sort = 121; break;               // Blacksmithing
                case 171: $sort = 181; break;               // Alchemy
                case 165: $sort = 182; break;               // Leatherworking
                case 202: $sort = 201; break;               // Engineering
                case 197: $sort = 264; break;               // Tailoring
                case 185: $sort = 304; break;               // Cooking
                case 129: $sort = 324; break;               // First Aid
                case 773: $sort = 371; break;               // Inscription
                case 755: $sort = 373; break;               // Jewelcrafting
            }

            if ($sort)
            {
                $quests = new QuestList(array(['questSortID', -$sort], CFG_SQL_LIMIT_NONE));
                if (!$quests->error)
                {
                    $this->extendGlobalData($quests->getJSGlobals());
                    $this->lvTabs[] = array(
                        'file'   => 'quest',
                        'data'   => $quests->getListviewData(),
                        'params' => []
                    );
                }
            }
        }

        // tab: related classes (apply classes from [spells])
        $class = [];
        for ($i = 0; $i < 11; $i++)
            if ($reqClass & (1 << $i))
                $class[] = $i + 1;

        if ($class)
        {
            $classes = new CharClassList(array(['id', $class]));
            if (!$classes->error)
            {
                $this->lvTabs[] = array(
                    'file'   => 'class',
                    'data'   => $classes->getListviewData(),
                    'params' => []
                );
            }
        }

        // tab: related races (apply races from [spells])
        $race = [];
        for ($i = 0; $i < 12; $i++)
            if ($reqRace & (1 << $i))
                $race[] = $i + 1;

        if ($race)
        {
            $races = new CharRaceList(array(['id', $race]));
            if (!$races->error)
            {
                $this->lvTabs[] = array(
                    'file'   => 'race',
                    'data'   => $races->getListviewData(),
                    'params' => []
                );
            }
        }
    }
}


?>
