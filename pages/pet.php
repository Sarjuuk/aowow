<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_PET, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $pet = new PetList(array(['id', $_id]));
    if ($pet->error)
        $smarty->notFound(Lang::$game['pet'], $_id);

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // level range
    $infobox[] = Lang::$game['level'].Lang::$colon.$pet->getField('minLevel').' - '.$pet->getField('maxLevel');

    // exotic
    if ($pet->getField('exotic'))
        $infobox[] = '[url=?spell=53270]'.Lang::$pet['exotic'].'[/url]';

    /****************/
    /* Main Content */
    /****************/

    // menuId 8: Pets     g_initPath()
    //  tabid 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'infobox'    => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
            'headIcons'  => [$pet->getField('iconString')],
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true,
                BUTTON_TALENT  => ['href' => '?petcalc#'.Util::$tcEncoding[(int)($_id / 10)] . Util::$tcEncoding[(2 * ($_id % 10) + ($pet->getField('exotic') ? 1 : 0))], 'pet' => true]
            ),
            'name'       => $pet->getField('name', true),
            'id'         => $_id,
            'expansion'  => Util::$expansionString[$pet->getField('expansion')],
            'title'      => $pet->getField('name', true)." - ".Util::ucfirst(Lang::$game['pet']),
            'path'       => '[0, 8, '.$pet->getField('type').']',
            'tab'        => 0,
            'type'       => TYPE_PET,
            'typeId'     => $_id,
            'reqJS'      => ['static/js/swfobject.js']
        ),
        'relTabs'  => []
    );

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: tameable & gallery
    $condition = array(
        ['ct.type', 1],                                     // Beast
        ['ct.typeFlags', 0x1, '&'],                         // tameable
        ['ct.family', $_id],                                // displayed petType
        [
            'OR',                                           // at least neutral to at least one faction
            ['ft.A', 1, '<'],
            ['ft.H', 1, '<']
        ]
    );
    $tng = new CreatureList($condition);

    $pageData['relTabs'][] = array(
        'file'   => 'creature',
        'data'   => $tng->getListviewData(NPCINFO_TAMEABLE),
        'params' => array(
            'name'        => '$LANG.tab_tameable',
            'tabs'        => '$tabsRelated',
            'hiddenCols'  => "$['type']",
            'visibleCols' => "$['skin']",
            'note'        => sprintf(Util::$filterResultString, '?npcs=1&filter=fa=38'),
            'id'          => 'tameable'
        )
    );

    $pageData['relTabs'][] = array(
        'file'   => 'model',
        'data'   => $tng->getListviewData(NPCINFO_MODEL),
        'params' => array(
            'tabs' => '$tabsRelated'
        )
    );

    // tab: diet
    $list = [];
    $mask = $pet->getField('foodMask');
    for ($i = 1; $i < 7; $i++)
        if ($mask & (1 << ($i - 1)))
            $list[] = $i;

    $food = new ItemList(array(['i.subClass', [5, 8]], ['i.FoodType', $list]));
    $food->addGlobalsToJscript();

    $pageData['relTabs'][] = array(
        'file'   => 'item',
        'data'   => $food->getListviewData(),
        'params' => array(
            'name'       => '$LANG.diet',
            'tabs'       => '$tabsRelated',
            'hiddenCols' => "$['source', 'slot', 'side']",
            'sort'       => "$['level']",
            'id'         => 'diet'
        )
    );

    // tab: spells
    $mask = 0x0;
    foreach (Util::$skillLineMask[-1] as $idx => $pair)
    {
        if ($pair[0] == $_id)
        {
            $mask = 1 << $idx;
            break;
        }
    }
    $conditions = [
        ['s.typeCat', -3],                                  // Pet-Ability
        [
            'OR',
            ['skillLine1', $pet->getField('skillLineId')],  // match: first skillLine
            [
                'AND',                                      // match: second skillLine (if not mask)
                ['skillLine1', 0, '>'],
                ['skillLine2OrMask', $pet->getField('skillLineId')]
            ],
            [
                'AND',                                      // match: skillLineMask (if mask)
                ['skillLine1', -1],
                ['skillLine2OrMask', $mask, '&']
            ]
        ]
    ];

    $spells = new SpellList($conditions);
    $spells->addGlobalsToJScript(GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $spells->getListviewData(),
        'params' => array(
            'name'        => '$LANG.tab_abilities',
            'tabs'        => '$tabsRelated',
            'visibleCols' => "$['schools', 'level']",
            'id'          => 'abilities'
        )
    );

    // tab: talents
    $conditions = array(
        ['s.typeCat', -7],
        [                                                   // last rank or unranked
            'OR',
            ['s.cuFlags', SPELL_CU_LAST_RANK, '&'],
            ['s.rankId', 0]
        ]
    );

    switch($pet->getField('type'))
    {
        case 0: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE0, '&']; break;
        case 1: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE1, '&']; break;
        case 2: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE2, '&']; break;
    }

    $talents = new SpellList($conditions);
    $talents->addGlobalsToJScript(GLOBALINFO_SELF);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $talents->getListviewData(),
        'params' => array(
            'tabs'        => '$tabsRelated',
            'visibleCols' => "$['tier', 'level']",
            'name'        => '$LANG.tab_talents',
            'id'          => 'talents',
            'sort'        => "$['tier', 'name']",
            '_petTalents' => 1
        )
    );

    $smarty->saveCache($cacheKeyPage, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_PET, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
