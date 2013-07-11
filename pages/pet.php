<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.community.php';

$id      = intVal($pageParam);
$petCalc = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789';


$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_PET, $id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $pet = new PetList(array(['id', $id]));
    if ($pet->error)
        $smarty->notFound(Lang::$game['pet']);

    // $pet->addGlobalsToJscript($smarty);
    $pet->reset();

    $infobox = [];

    // level range
    $infobox[] = '[li]'.Lang::$game['level'].Lang::$colon.$pet->getField('minLevel').' - '.$pet->getField('maxLevel').'[/li]';

    // exotic
    if ($pet->getField('exotic'))
        $infobox[] = '[li][url=?spell=53270]'.Lang::$pet['exotic'].'[/url][/li]';

    $pageData = array(
        'title' => $pet->getField('name', true),
        'path'  => '[0, 8, '.$pet->getField('type').']',
        'page' => array(
            'petCalc'   => $petCalc[(int)($id / 10)] . $petCalc[(2 * ($id % 10) + ($pet->getField('exotic') ? 1 : 0))],
            'name'      => $pet->getField('name', true),
            'id'        => $id,
            'icon'      => $pet->getField('iconString'),
            'expansion' => Util::$expansionString[$pet->getField('expansion')]
        ),
        'infobox' => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
    );

    // tameable & gallery
    $condition = array(
        ['ct.type', 1],                                     // Beast
        ['ct.type_flags', 0x1, '&'],                        // tameable
        ['ct.family', $id],                                 // displayed petType
        [
            'OR',                                           // at least neutral to at least one faction
            ['ft.A', 1, '<'],
            ['ft.H', 1, '<']
        ]
    );
    $tng = new CreatureList($condition);

    $pageData['tameable'] = array(
        'data'   => $tng->getListviewData(NPCINFO_TAMEABLE),
        'params' => [
            'name'        => '$LANG.tab_tameable',
            'tabs'        => '$tabsRelated',
            'hiddenCols'  => "$['type']",
            'visibleCols' => "$['skin']",
            'note'        => '$sprintf(LANG.lvnote_filterresults, \'?npcs=1&filter=fa=38\')',
            'id'          => 'tameable'
        ]
    );

    $pageData['gallery'] = array(
        'data'   => $tng->getListviewData(NPCINFO_MODEL),
        'params' => [
            'tabs'       => '$tabsRelated'
        ]
    );

    // diet
    $list = [];
    $mask = $pet->getField('foodMask');
    for ($i = 1; $i < 7; $i++)
        if ($mask & (1 << ($i - 1)))
            $list[] = $i;

    $food = new ItemList(array(['i.subClass', [5, 8]], ['i.FoodType', $list]));
    $food->addGlobalsToJscript($smarty);

    $pageData['diet'] = array(
        'data'   => $food->getListviewData(),
        'params' => [
            'name'       => '$LANG.diet',
            'tabs'       => '$tabsRelated',
            'hiddenCols' => "$['source', 'slot', 'side']",
            'sort'       => "$['level']",
            'id'         => 'diet'
        ]
    );

    // spells
    $mask = 0x0;
    foreach (Util::$skillLineMask[-1] as $idx => $pair)
    {
        if ($pair[0] == $id)
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
    $spells->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['abilities'] = array(
        'data'   => $spells->getListviewData(),
        'params' => [
            'name'        => '$LANG.tab_abilities',
            'tabs'        => '$tabsRelated',
            'visibleCols' => "$['schools', 'level']",
            'id'          => 'abilities'
        ]
    );

    // talents
    $conditions = [['s.typeCat', -7]];
    switch($pet->getField('type'))
    {
        case 0: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE0, '&']; break;
        case 1: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE1, '&']; break;
        case 2: $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE2, '&']; break;
    }

    $talents = new SpellList($conditions);
    $talents->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    $pageData['talents'] = array(
        'data'   => $talents->getListviewData(),
        'params' => [
            'tabs'        => '$tabsRelated',
            'visibleCols' => "$['tier', 'level']",
            'name'        => '$LANG.tab_talents',
            'id'          => 'talents',
            'sort'        => "$['tier', 'name']",
            '_petTalents' => 1
        ]
    );

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars(array(
    'title'  => $pageData['title']." - ".Util::ucfirst(Lang::$game['pet']),
    'path'   => $pageData['path'],
    'tab'    => 0,                                          // for g_initHeader($tab)
    'type'   => TYPE_PET,                                   // 9:Pets
    'typeId' => $id,
    'reqJS'  => array(
        array('path' => 'template/js/swfobject.js')
    )
));


$smarty->assign('community', CommunityContent::getAll(TYPE_PET, $id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game));
$smarty->assign('lvData', $pageData);
$smarty->display('pet.tpl');

?>
