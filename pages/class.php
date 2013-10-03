<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id         = intVal($pageParam);
$_mask       = 1 << ($_id - 1);
$_path       = [0, 12, $_id];
$tcClassId   = [null, 8, 3, 1, 5, 4, 9, 6, 2, 7, null, 0]; // see TalentCalc.js
$classSkills = array(
     1 => [ 26, 256, 257],
     2 => [594, 267, 184],
     3 => [ 50, 163,  51],
     4 => [253,  38,  39],
     5 => [613,  56,  78],
     6 => [770, 771, 772, 776],
     7 => [375, 373, 374],
     8 => [237,   8,   6],
     9 => [355, 354, 593],
    11 => [574, 134, 573]
);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_CLASS, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $cl = new CharClassList(array(['id', $_id]));
    if ($cl->error)
        $smarty->notFound(Lang::$game['class']);

    $infobox = [];
    // hero class
    if ($cl->getField('hero'))
        $infobox[] = '[tooltip=tooltip_heroclass]'.Lang::$game['heroClass'].'[/tooltip]';

    // resource
    if ($_id == 11)                                         // special Druid case
        $infobox[] = Lang::$game['resources'].Lang::$colon.
        '[tooltip name=powertype1]'.Lang::$game['st'][0].', '.Lang::$game['st'][31].', '.Lang::$game['st'][2].'[/tooltip][span class=tip tooltip=powertype1]'.Util::ucFirst(Lang::$spell['powerTypes'][0]).'[/span], '.
        '[tooltip name=powertype2]'.Lang::$game['st'][5].', '.Lang::$game['st'][8].'[/tooltip][span class=tip tooltip=powertype2]'.Util::ucFirst(Lang::$spell['powerTypes'][1]).'[/span], '.
        '[tooltip name=powertype8]'.Lang::$game['st'][1].'[/tooltip][span class=tip tooltip=powertype8]'.Util::ucFirst(Lang::$spell['powerTypes'][3]).'[/span]';
    else if ($_id == 6)                                     // special DK case
        $infobox[] = Lang::$game['resources'].Lang::$colon.'[span]'.Util::ucFirst(Lang::$spell['powerTypes'][5]).', '.Util::ucFirst(Lang::$spell['powerTypes'][$cl->getField('powerType')]).'[/span]';
    else                                                    // regular case
        $infobox[] = Lang::$game['resource'].Lang::$colon.'[span]'.Util::ucFirst(Lang::$spell['powerTypes'][$cl->getField('powerType')]).'[/span]';

    // roles
    $roles = [];
    for ($i = 0; $i < 4; $i++)
        if ($cl->getField('roles') & (1 << $i))
            $roles[] = (count($roles) == 2 ? '\n' : '').Lang::$game['_roles'][$i];

    $infobox[] = (count($roles) > 1 ? Lang::$game['roles'] : Lang::$game['role']).Lang::$colon.implode(', ', $roles);

    // specs
    $specList = [];
    $skills = new SkillList(array(['id', $classSkills[$_id]]));
    $skills->addGlobalsToJscript($smarty);
    foreach ($skills->iterate() as $k => $__)
        $specList[$k] = '[icon name='.$skills->getField('iconString').'][url=?spells=7.'.$_id.'.'.$k.']'.$skills->getField('name', true).'[/url][/icon]';

    $infobox[] = Lang::$game['specs'].Lang::$colon.'[ul][li]'.implode('[/li][li]', $specList).'[/li][/ul]';

    $pageData = array (
        'title'      => $cl->getField('name', true).' - '.Util::ucFirst(Lang::$game['class']),
        'path'       => $_path,
        'infobox'    => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
        'relTabs'    => [],
        'page'       => array(
            'name'       => $cl->getField('name', true),
            'talentCalc' => Util::$tcEncoding[$tcClassId[$_id] * 3],
            'icon'       => 'class_'.strtolower($cl->getField('fileString')),
            'expansion'  => Util::$expansionString[$cl->getField('expansion')]
        )
    );

/*
    note!
        newer listviews support subTabs - i.e.:
        Spells => Abilities, Talents, Glyphs, Proficiencies
        Items  => grouping by subclass
*/
    // Quests
    $conditions = array(
        ['RequiredClasses', $_mask, '&'],
        [['RequiredClasses', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!']
    );

    $quests = new QuestList($conditions);
    $quests->addGlobalsToJscript($smarty);

    $pageData['relTabs'][] = array(
        'file'   => 'quest',
        'data'   => $quests->getListviewData(),
        'params' => array(
            'sort' => "$['reqlevel', 'name']",
            'tabs' => '$tabsRelated'
        )
    );


    // Items
    $conditions = array(
        ['requiredClass', 0, '>'],
        ['requiredClass', $_mask, '&'],
        [['requiredClass', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!'],
        ['itemset', 0],                                     // hmm, do or dont..?
        0
    );

    $items = new ItemList($conditions);
    $items->addGlobalsToJscript($smarty);

    if (!$items->hasDiffFields(['requiredRace']))
        $hidden = "$['side']";

    $pageData['relTabs'][] = array(
        'file'   => 'item',
        'data'   => $items->getListviewData(),
        'params' => array(
            'id'              => 'items',
            'name'            => '$LANG.tab_items',
            'tabs'            => '$tabsRelated',
            'visibleCols'     => "$['dps', 'armor', 'slot']",
            'hiddenCols'      => isset($hidden) ? $hidden : null,
            'computeDataFunc' => '$Listview.funcBox.initSubclassFilter',
            'onAfterCreate'   => '$Listview.funcBox.addSubclassIndicator',
            'note'            => sprintf(Util::$filterResultString, '?items&filter=cr=152;crs=4;crv=0'),
            '_truncated'      => 1
        )
    );



    // Itemsets
    $sets = new ItemsetList(array(['classMask', $_mask, '&']));
    $sets->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'itemset',
        'data' => $sets->getListviewData(),
        'params' => array(
            'note'       => sprintf(Util::$filterResultString, '?itemsets&filter=cl='.$_id),
            'hiddenCols' => "$['classes']",
            'sort'       => "$['-level', 'name']",
            'tabs'       => '$tabsRelated'
        )
    );


    // Trainer
    $conditions = array(
        ['npcflag', 0x30, '&'],                             // is trainer
        ['trainer_type', 0],                                // trains class spells
        ['trainer_class', $_id]
    );

    $trainer = new CreatureList($conditions);

    $pageData['relTabs'][] = array(
        'file'   => 'creature',
        'data'   => $trainer->getListviewData(),
        'params' => array(
            'id'   => 'trainers',
            'name' => '$LANG.tab_trainers',
            'tabs' => '$tabsRelated'
        )
    );


    // Armor Proficiencies
    $conditions = array(
        ['s.typeCat', -11],
        ['s.skillLine1', SpellList::$skillLines[8]],
        ['s.reqClassMask', $_mask, '&']
    );

    $armorProf = new SpellList($conditions);
    $armorProf->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $armorProf->getListviewData(),
        'params' => array(
            'id'          => 'armor-proficiencies',
            'name'        => '$LANG.tab_armorproficiencies',
            'visibleCols' => "$['type', 'classes']",
            'hiddenCols'  => "$['reagents', 'skill']",
            'tabs'        => '$tabsRelated'
        )
    );


    // Weapon Proficiencies
    $conditions = array(
        ['s.typeCat', -11],
        ['OR', ['s.skillLine1', SpellList::$skillLines[6]], ['s.skillLine1', -3]],
        ['s.reqClassMask', $_mask, '&']
    );

    $weaponProf = new SpellList($conditions);
    $weaponProf->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $weaponProf->getListviewData(),
        'params' => array(
            'id'          => 'weapon-skills',
            'name'        => '$LANG.tab_weaponskills',
            'visibleCols' => "$['type', 'classes']",
            'hiddenCols'  => "$['reagents', 'skill']",
            'tabs'        => '$tabsRelated'
        )
    );


    // Glyphs
    $conditions = array(
        ['s.typeCat', -13],
        ['s.reqClassMask', $_mask, '&']
    );

    $glyphs = new SpellList($conditions);
    $glyphs->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $glyphs->getListviewData(),
        'params' => array(
            'id'          => 'glyphs',
            'name'        => '$LANG.tab_glyphs',
            'visibleCols' => "$['type']",
            'hiddenCols'  => "$['reagents']",
            'tabs'        => '$tabsRelated'
        )
    );


    // Abilities
    $conditions = array(
        ['s.typeCat', [7, -2]],
        [['s.cuFlags', (SPELL_CU_TALENTSPELL | SPELL_CU_TALENT | SPELL_CU_TRIGGERED | SPELL_CU_EXCLUDE_CATEGORY_SEARCH), '&'], 0],
        [                                                   // select class by skillLine
            'OR',
            ['s.skillLine1', $classSkills[$_id]],
            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $classSkills[$_id]]]
        ],
        [                                                   // last rank or unranked
            'OR',
            ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
            ['s.rankId', 0]
        ]
    );

    $abilities = new SpellList($conditions);
    $abilities->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $abilities->getListviewData(),
        'params' => array(
            'id'          => 'abilities',
            'name'        => '$LANG.tab_abilities',
            'note'        => sprintf(Util::$filterResultString, '?spells=7.'.$_id),
            'visibleCols' => "$['level', 'schools']",
            'sort'        => "$['skill', 'name']",
            'tabs'        => '$tabsRelated'
        )
    );


    // Talents
    $conditions = array(
        ['s.typeCat', -2],
        ['cuFlags', (SPELL_CU_TALENT | SPELL_CU_TALENTSPELL), '&'],
        [                                                   // select class by skillLine
            'OR',
            ['s.skillLine1', $classSkills[$_id]],
            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $classSkills[$_id]]]
        ],
        [                                                   // last rank or unranked
            'OR',
            ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
            ['s.rankId', 0]
        ]
    );

    $talents = new SpellList($conditions);
    $talents->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $talents->getListviewData(),
        'params' => array(
            'id'          => "talents",
            'name'        => '$LANG.tab_talents',
            'note'        => sprintf(Util::$filterResultString, '?spells=-2.'.$_id),
            'visibleCols' => "$['level', 'schools', 'tier']",
            'sort'        => "$['skill', 'tier']",
            'tabs'        => '$tabsRelated'
        )
    );


    // Races
    $races = new CharRaceList(array(['classMask', $_mask, '&']));

    $pageData['relTabs'][] = array(
        'file'   => 'race',
        'data'   => $races->getListviewData(),
        'params' => array(
            'tabs' => '$tabsRelated'
        )
    );

    $smarty->saveCache($cacheKeyPage, $pageData);
}


// menuId 12: Class    g_initPath()
//  tabId  0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'    => $pageData['title'],
    'path'     => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'      => 0,
    'type'     => TYPE_CLASS,
    'typeId'   => $_id,
    // 'boardUrl' => '',                                    //$GLOBALS['AoWoWconf']['boardUrl'] + X,
    'reqJS'    => array(
        'template/js/swfobject.js'
    )
));
$smarty->assign('community', CommunityContent::getAll(TYPE_CLASS, $_id));       // comments, screenshots, videos
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('class.tpl');

?>
