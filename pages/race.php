<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id   = intVal($pageParam);
$_mask = 1 << ($_id - 1);
$_path = [0, 13, $_id];

$mountVendors = array(                                      // race => [starter, argent tournament]
    null,
    [384,   33307],
    [3362,  33553],
    [1261,  33310],
    [4730,  33653],
    [4731,  33555],
    [3685,  33556],
    [7955,  33650],
    [7952,  33554],
    null,
    [16264, 33557],
    [17584, 33657]
);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_RACE, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $race = new CharRaceList(array(['id', $_id]));          // should this be limited to playable races..?
    if ($race->error)
        $smarty->notFound(Lang::$game['race'], $_id);

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];                                          // unfortunately is all of this custom data :/

    // side
    if ($_ = $race->getField('side'))
        $infobox[] = Lang::$main['side'].Lang::$colon.'[span class='.($_ == 2 ? 'horde' : 'alliance').'-icon]'.Lang::$game['si'][$_].'[/span]';

    // faction
    if ($_ = $race->getField('factionId'))
    {
        $smarty->extendGlobalIds(TYPE_FACTION, $_);
        $infobox[] = Util::ucFirst(Lang::$game['faction']).Lang::$colon.'[faction='.$_.']';
    }

    // leader
    if ($_ = $race->getField('leader'))
    {
        $smarty->extendGlobalIds(TYPE_NPC, $_);
        $infobox[] = Lang::$class['racialLeader'].Lang::$colon.'[npc='.$_.']';
    }

    // start area
    if ($_ = $race->getField('startAreaId'))
    {
        $smarty->extendGlobalIds(TYPE_ZONE, $_);
        $infobox[] = Lang::$class['startZone'].Lang::$colon.'[zone='.$_.']';
    }

    /****************/
    /* Main Content */
    /****************/

    // menuId 13: Race     g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array (
        'page'       => array(
            'name'       => $race->getField('name', true),
            'expansion'  => Util::$expansionString[$race->getField('expansion')],
            'title'      => $race->getField('name', true).' - '.Util::ucFirst(Lang::$game['race']),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'        => 0,
            'type'       => TYPE_RACE,
            'typeId'     => $_id,
            'infobox'    => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
            'headIcons'  => array(
                'race_'.strtolower($race->getField('fileString')).'_male',
                'race_'.strtolower($race->getField('fileString')).'_female'
            ),
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true
            )
        ),
        'relTabs'    => []
    );

    /**************/
    /* Extra Tabs */
    /**************/

    // Classes
    $classes = new CharClassList(array(['racemask', $_mask, '&']));
    $classes->addGlobalsToJscript($smarty);

    $pageData['relTabs'][] = array(
        'file'   => 'class',
        'data'   => $classes->getListviewData(),
        'params' => array(
            'tabs' => '$tabsRelated'
        )
    );

    // Tongues
    $conditions = array(
        ['typeCat', -11],                                   // proficiencies
        ['reqRaceMask', $_mask, '&']                        // only languages are race-restricted
    );

    $tongues = new SpellList($conditions);
    $tongues->addGlobalsToJscript($smarty);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $tongues->getListviewData(),
        'params' => array(
            'id'          => 'languages',
            'name'        => '$LANG.tab_languages',
            'hiddenCols'  => "$['reagents']",
            'tabs'        => '$tabsRelated'
        )
    );

    // Racials
    $conditions = array(
        ['typeCat', -4],                                    // racial traits
        ['reqRaceMask', $_mask, '&']
    );

    $racials = new SpellList($conditions);
    $racials->addGlobalsToJscript($smarty);

    $pageData['relTabs'][] = array(
        'file'   => 'spell',
        'data'   => $racials->getListviewData(),
        'params' => array(
            'id'          => 'racial-traits',
            'name'        => '$LANG.tab_racialtraits',
            'hiddenCols'  => "$['reagents']",
            'tabs'        => '$tabsRelated'
        )
    );

    // Quests
    $conditions = array(
        ['RequiredRaces', $_mask, '&'],
        [['RequiredRaces', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'],
        [['RequiredRaces', RACE_MASK_HORDE, '&'], RACE_MASK_HORDE, '!'],
        [['RequiredRaces', RACE_MASK_ALLIANCE, '&'], RACE_MASK_ALLIANCE, '!']
    );

    $quests = new QuestList($conditions);
    $quests->addGlobalsToJscript($smarty);

    $pageData['relTabs'][] = array(
        'file'   => 'quest',
        'data'   => $quests->getListviewData(),
        'params' => array(
            'tabs' => '$tabsRelated'
        )
    );

    // Mounts
    // ok, this sucks, but i rather hardcode the trainer, than fetch items by namepart
    $items = isset($mountVendors[$_id]) ? DB::Aowow()->selectCol('SELECT item FROM npc_vendor WHERE entry IN (?a)', $mountVendors[$_id]) : 0;

    $conditions = array(
        ['i.id', $items],
        ['i.class', ITEM_CLASS_MISC],
        ['i.subClass', 5],                                  // mounts
    );

    $mounts = new ItemList($conditions);
    $mounts->addGlobalsToJscript($smarty);

    $pageData['relTabs'][] = array(
        'file'   => 'item',
        'data'   => $mounts->getListviewData(),
        'params' => array(
            'id'         => 'mounts',
            'name'       => '$LANG.tab_mounts',
            'tabs'       => '$tabsRelated',
            'hiddenCols' => "$['slot', 'type']"
        )
    );

    $smarty->saveCache($cacheKeyPage, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_RACE, $_id));       // comments, screenshots, videos
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
