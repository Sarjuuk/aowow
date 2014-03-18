<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id          = intVal($pageParam);
$_mask        = 1 << ($_id - 1);
$_path        = [0, 12, $_id];
$tcClassId    = [null, 8, 3, 1, 5, 4, 9, 6, 2, 7, null, 0]; // see TalentCalc.js
$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_CLASS, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $cl = new CharClassList(array(['id', $_id]));
    if ($cl->error)
        $smarty->notFound(Lang::$game['class'], $_id);

    /***********/
    /* Infobox */
    /***********/

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

    if ($roles)
        $infobox[] = (count($roles) > 1 ? Lang::$game['roles'] : Lang::$game['role']).Lang::$colon.implode(', ', $roles);

    // specs
    $specList = [];
    $skills = new SkillList(array(['id', $cl->getField('skills')]));
    $skills->addGlobalsToJscript($smarty);
    foreach ($skills->iterate() as $k => $__)
        $specList[$k] = '[icon name='.$skills->getField('iconString').'][url=?spells=7.'.$_id.'.'.$k.']'.$skills->getField('name', true).'[/url][/icon]';

    if ($specList)
        $infobox[] = Lang::$game['specs'].Lang::$colon.'[ul][li]'.implode('[/li][li]', $specList).'[/li][/ul]';

    /****************/
    /* Main Content */
    /****************/

    // menuId 12: Class    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array (
        'page'    => array(
            'title'      => $cl->getField('name', true).' - '.Util::ucFirst(Lang::$game['class']),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'        => 0,
            'type'       => TYPE_CLASS,
            'typeId'     => $_id,
            'reqJS'      => ['static/js/swfobject.js'],
            'name'       => $cl->getField('name', true),
            'expansion'  => Util::$expansionString[$cl->getField('expansion')],
            'infobox'    => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
            'headIcons'  => ['class_'.strtolower($cl->getField('fileString'))],
            'redButtons' => array(
                BUTTON_LINKS   => ['color' => '', 'linkId' => ''],
                BUTTON_WOWHEAD => true,
                BUTTON_TALENT  => ['href' => '?talent#'.Util::$tcEncoding[$tcClassId[$_id] * 3], 'pet' => false],
                BUTTON_FORUM   => false                         // doto (low): CFG_BOARD_URL + X
            )
        ),
        'relTabs' => [],
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
            ['s.skillLine1', $cl->getField('skills')],      // Abilities / Talents
            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $cl->getField('skills')]]
        ],
        [                                                   // last rank or unranked
            'OR',
            ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
            ['s.rankId', 0]
        ]
    );

    $genSpells = new SpellList($conditions);
    $genSpells->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $genSpells->getListviewData(),
        'params' => array(
            'id'              => 'spells',
            'name'            => '$LANG.tab_spells',
            'visibleCols'     => "$['level', 'schools', 'type', 'classes']",
            'hiddenCols'      => "$['reagents', 'skill']",
            'sort'            => "$['-level', 'type', 'name']",
            'tabs'            => '$tabsRelated',
            'computeDataFunc' => '$Listview.funcBox.initSpellFilter',
            'onAfterCreate'   => '$Listview.funcBox.addSpellIndicator'
        )
    );


    // Tab: Items (grouped)
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
            'note'            => sprintf(Util::$filterResultString, '?items&filter=cr=152;crs='.$_id.';crv=0'),
            '_truncated'      => 1
        )
    );

    // Tab: Quests
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

    // Tab: Itemsets
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

    // Tab: Trainer
    $conditions = array(
        ['npcflag', 0x30, '&'],                             // is trainer
        ['trainerType', 0],                                 // trains class spells
        ['trainerClass', $_id]
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

    // Tab: Races
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


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_CLASS, $_id));       // comments, screenshots, videos
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
