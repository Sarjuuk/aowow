<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id   = intVal($pageParam);
$_path = [0, 14];

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_SKILL, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $skill = new SkillList(array(['id', $_id]));
    if ($skill->error)
        $smarty->notFound(Lang::$game['skill'], $_id);

    $_cat = $skill->getField('typeCat');

    $_path[] = (in_array($_cat, [9, 11]) || $_id == 762) ? $_id : $_cat;

    /****************/
    /* Main Content */
    /****************/

    // menuId 14: Skill    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page'    => array(
            'title'      => $skill->getField('name', true)." - ".Util::ucfirst(Lang::$game['skill']),
            'name'       => $skill->getField('name', true),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'        => 0,
            'type'       => TYPE_SKILL,
            'typeId'     => $_id,
            'headIcons'  => [$skill->getField('iconString')],
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true
            )
        ),
        'relTabs' => []
    );

    /**************/
    /* Extra Tabs */
    /**************/

    if (in_array($_cat, [-5, 9, 11]))
    {
        // tab: recipes [spells] (crafted)
        $condition = array(
            ['OR', ['s.reagent1', 0, '>'], ['s.reagent2', 0, '>'], ['s.reagent3', 0, '>'], ['s.reagent4', 0, '>'], ['s.reagent5', 0, '>'], ['s.reagent6', 0, '>'], ['s.reagent7', 0, '>'], ['s.reagent8', 0, '>']],
            ['OR', ['s.skillLine1', $_id], ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $_id]]],
            0
        );

        $recipes = new SpellList($condition);               // also relevant for 3
        if (!$recipes->error)
        {
            $recipes->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $recipes->getListviewData(),
                'params' => array(
                    'tabs'        => '$tabsRelated',
                    'id'          => 'recipes',
                    'name'        => '$LANG.tab_recipes',
                    'visibleCols' => "$['reagents', 'source']",
                    'note'        =>  sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.$_id.'&filter=cr=20;crs=1;crv=0')
                )
            );
        }

        // tab: recipe Items [items] (Books)
        $filterRecipe = [null, 165, 197, 202, 164, 185, 171, 129, 333, 356, 755, 773, 186, 182];
        $conditions   = array(
            ['requiredSkill', $_id],
            ['class', ITEM_CLASS_RECIPE],
            0
        );

        $recipeItems = new ItemList($conditions);
        if (!$recipeItems->error)
        {
            $recipeItems->addGlobalsToJScript(GLOBALINFO_SELF);

            if ($_ = array_search($_id, $filterRecipe))
                $_ = sprintf(Util::$filterResultString, "?items=9.".$_);

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $recipeItems->getListviewData(),
                'params' => array(
                    'id'   => 'recipe-items',
                    'name' => '$LANG.tab_recipeitems',
                    'tabs' => '$tabsRelated',
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
            $created = new ItemList(array(['i.id', $created], 0));
            if (!$created->error)
            {
                $created->addGlobalsToJScript(GLOBALINFO_SELF);

                if ($_ = array_search($_id, $filterItem))
                    $_ = sprintf(Util::$filterResultString, "?items&filter=cr=86;crs=".$_.";crv=0");

                $pageData['relTabs'][] = array(
                    'file'   => 'item',
                    'data'   => $created->getListviewData(),
                    'params' => array(
                        'id'   => 'crafted-items',
                        'name' => '$LANG.tab_crafteditems',
                        'tabs' => '$tabsRelated',
                        'note' => $_
                    )
                );
            }
        }

        // tab: required by [item]
        $conditions = array(
            ['requiredSkill', $_id],
            ['class', ITEM_CLASS_RECIPE, '!'],
            0
        );

        $reqBy = new ItemList($conditions);
        if (!$reqBy->error)
        {
            $reqBy->addGlobalsToJScript(GLOBALINFO_SELF);

            if ($_ = array_search($_id, $filterItem))
                $_ = sprintf(Util::$filterResultString, "?items&filter=cr=99:168;crs=".$_.":2;crv=0:0");

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $reqBy->getListviewData(),
                'params' => array(
                    'id'   => 'required-by',
                    'name' => '$LANG.tab_requiredby',
                    'tabs' => '$tabsRelated',
                    'note' => $_
                )
            );
        }

        // tab: required by [itemset]
        $conditions = array(
            ['skillId', $_id],
            0
        );

        $reqBy = new ItemsetList($conditions);
        if (!$reqBy->error)
        {
            $reqBy->addGlobalsToJScript(GLOBALINFO_SELF);

            $pageData['relTabs'][] = array(
                'file'   => 'itemset',
                'data'   => $reqBy->getListviewData(),
                'params' => array(
                    'id'   => 'required-by-set',
                    'name' => '$LANG.tab_requiredby',
                    'tabs' => '$tabsRelated'
                )
            );
        }
    }

    // tab: spells [spells] (exclude first tab)
    $reqClass  = 0x0;
    $reqRace   = 0x0;
    $condition = array(
        ['AND', ['s.reagent1', 0], ['s.reagent2', 0], ['s.reagent3', 0], ['s.reagent4', 0], ['s.reagent5', 0], ['s.reagent6', 0], ['s.reagent7', 0], ['s.reagent8', 0]],
        ['OR',  ['s.skillLine1', $_id], ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $_id]]],
        0
    );

    foreach (Util::$skillLineMask as $line1 => $sets)
        foreach ($sets as $idx => $set)
            if ($set[1] == $_id)
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

        $spells->addGlobalsToJScript(GLOBALINFO_SELF);

        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $spells->getListviewData(),
            'params' => array(
                'tabs'        => '$tabsRelated',
                'visibleCols' => "$['source']"
            )
        );

        switch ($_cat)
        {
            case -4:
                $pageData['spells']['params']['note'] = sprintf(Util::$filterResultString, '?spells=-4');
                break;
            case  7:
                if ($_id != 769)                            // Internal
                    $pageData['spells']['params']['note'] = sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.(log($reqClass, 2) + 1).'.'.$_id);  // doesn't matter what spell; reqClass should be identical for all Class Spells
                break;
            case  9:
            case 11:
                $pageData['spells']['params']['note'] = sprintf(Util::$filterResultString, '?spells='.$_cat.'.'.$_id);
                break;

        }
    }

    // tab: trainers [npcs]
    if (in_array($_cat, [-5, 6, 7, 8, 9, 11]))
    {
        $list = [];
        if (@$tt = Util::$trainerTemplates[TYPE_SKILL][$_id])
            $list = DB::Aowow()->selectCol('SELECT DISTINCT entry FROM npc_trainer WHERE spell IN (?a) AND entry < 200000', $tt);
        else
        {
            $mask = 0;
            foreach (Util::$skillLineMask[-3] as $idx => $pair)
                if ($pair[1] == $_id)
                    $mask |= 1 << $idx;

            $list = DB::Aowow()->selectCol('
                SELECT    IF(t1.entry > 200000, t2.entry, t1.entry)
                FROM      npc_trainer t1
                JOIN      aowow_spell s  ON s.id = t1.spell
                LEFT JOIN npc_trainer t2 ON t2.spell = -t1.entry
                WHERE     s.typeCat IN (-11, 9) AND (s.skillLine1 = ?d OR (s.skillLine1 > 0 AND s.skillLine2OrMask = ?d) '.($mask ? ' OR (s.skilllIne1 = -3 AND s.skillLine2OrMask = '.$mask.')' : null).')',
                $_id,
                $_id
            );
        }

        if ($list)
        {
            $trainer = new CreatureList(array(0, ['ct.id', $list], ['ct.spawns', 0, '>'], ['ct.npcflag', 0x10, '&']));

            if (!$trainer->error)
            {
                $trainer->addGlobalsToJscript();

                $pageData['relTabs'][] = array(
                    'file'   => 'creature',
                    'data'   => $trainer->getListviewData(),
                    'params' => array(
                        'tabs' => '$tabsRelated',
                        'id'   => 'trainer',
                        'name' => '$LANG.tab_trainers',
                    )
                );
            }
        }
    }

    // tab: quests [quests]
    if (in_array($_cat, [9, 11]))                           // only for professions
    {
        $sort = 0;
        switch ($_id)
        {
            case 182: $sort =  24; break;                   // Herbalism
            case 356: $sort = 101; break;                   // Fishing
            case 164: $sort = 121; break;                   // Blacksmithing
            case 171: $sort = 181; break;                   // Alchemy
            case 165: $sort = 182; break;                   // Leatherworking
            case 202: $sort = 201; break;                   // Engineering
            case 197: $sort = 264; break;                   // Tailoring
            case 185: $sort = 304; break;                   // Cooking
            case 129: $sort = 324; break;                   // First Aid
            case 773: $sort = 371; break;                   // Inscription
            case 755: $sort = 373; break;                   // Jewelcrafting
        }

        if ($sort)
        {
            $quests = new QuestList(array(['zoneOrSort', -$sort], 0));
            if (!$quests->error)
            {
                $quests->addGlobalsToJScript();
                $pageData['relTabs'][] = array(
                    'file'   => 'quest',
                    'data'   => $quests->getListviewData(),
                    'params' => array(
                        'tabs' => '$tabsRelated',
                    )
                );
            }
        }
    }

    // tab: related classes (apply classes from [itemset])
    $class = [];
    for ($i = 0; $i < 11; $i++)
        if ($reqClass & (1 << $i))
            $class[] = $i + 1;

    if ($class)
    {
        $classes = new CharClassList(array(['id', $class]));
        if (!$classes->error)
        {
            $pageData['relTabs'][] = array(
                'file'   => 'class',
                'data'   => $classes->getListviewData(),
                'params' => array(
                    'tabs' => '$tabsRelated',
                )
            );
        }
    }

    // tab: related races (apply races from [itemset])
    $race = [];
    for ($i = 0; $i < 12; $i++)
        if ($reqRace & (1 << $i))
            $race[] = $i + 1;

    if ($race)
    {
        $races = new CharRaceList(array(['id', $race]));
        if (!$races->error)
        {
            $pageData['relTabs'][] = array(
                'file'   => 'race',
                'data'   => $races->getListviewData(),
                'params' => array(
                    'tabs' => '$tabsRelated',
                )
            );
        }
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_SKILL, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
