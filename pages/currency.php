<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_CURRENCY, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $currency = new CurrencyList(array(['id', $_id]));
    if ($currency->error)
        $smarty->notFound(Lang::$game['skill']);

    $_cat       = $currency->getField('category');
    $_itemId    = $currency->getField('itemId');
    $_isSpecial = $_id == 103 || $_id == 104;               // honor && arena points are not handled as items

    /****************/
    /* Main Content */
    /****************/

    $pageData = array(
        'title'   => $currency->getField('name', true),
        'path'    => [0, 15],
        'relTabs' => [],
        'buttons' => array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true
        ),
        'page'    => array(
            'name' => $currency->getField('name', true),
            'icon' => $currency->getField('iconString'),
            'id'   => $_id
        ),
    );

    if ($_cat)
        $pageData['path'][] = $_cat;

    /**************/
    /* Extra Tabs */
    /**************/

    if (!$_isSpecial)
    {
        // tabs: this currency is contained in..
        $lootTabs = Util::getLootSource($_itemId);
        foreach ($lootTabs as $tab)
        {
            $pageData['relTabs'][] = array(
                'file'   => $tab[0],
                'data'   => $tab[1],
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'name'        => $tab[2],
                    'id'          => $tab[3],
                    'extraCols'   => $tab[4] ? '$['.implode(', ', array_unique($tab[4])).']' : null,
                    'hiddenCols'  => $tab[5] ? '$['.implode(', ', array_unique($tab[5])).']' : null,
                    'visibleCols' => $tab[6] ? '$'. json_encode(  array_unique($tab[6]))     : null
                ]
            );
        }

        // tab: sold by
        $itemObj = new ItemList(array(['id', $_itemId]));
        if ($vendors = @$itemObj->getExtendedCost()[$_itemId])
        {
            $soldBy = new CreatureList(array(['id', array_keys($vendors)]));
            if (!$soldBy->error)
            {
                $soldBy->addGlobalsToJscript($smarty, GLOBALINFO_SELF);
                $sbData = $soldBy->getListviewData();

                $extraCols = ['Listview.extraCols.stock', "Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", 'Listview.extraCols.cost'];

                $holidays = [];
                foreach ($sbData as $k => &$row)
                {
                    $currency = [];
                    $tokens   = [];
                    foreach ($vendors[$k] as $id => $qty)
                    {
                        if (is_string($id))
                            continue;

                        if ($id > 0)
                            $tokens[] = [$id, $qty];
                        else if ($id < 0)
                            $currency[] = [-$id, $qty];
                    }

                    if ($_ = $vendors[$k]['event'])
                    {
                        if (count($extraCols) == 3)             // not already pushed
                            $extraCols[] = 'Listview.extraCols.condition';

                        $holidays[$_] = 0;                      // applied as back ref.

                        $row['condition'] = array(
                            'type'   => TYPE_WORLDEVENT,
                            'typeId' => &$holidays[$_],
                            'status' => 1
                        );
                    }

                    $row['stock'] = $vendors[$k]['stock'];
                    $row['stack'] = $item->getField('buyCount');
                    $row['cost']  = array(
                        $item->getField('buyPrice'),
                        $currency ? $currency : null,
                        $tokens   ? $tokens   : null
                    );
                }

                if ($holidays)
                {
                    $hObj = new WorldEventList(array(['id', array_keys($holidays)]));
                    $hObj->addGlobalsToJscript($smarty);
                    foreach ($hObj->iterate() as $id => $tpl)
                    {
                        if ($_ = $tpl['holidayId'])
                            $holidays[$tpl['eventBak']] = $_;
                        else
                            $holidays[-$id] = $id;
                    }
                }

                $pageData['relTabs'][] = array(
                    'file'   => 'creature',
                    'data'   => $sbData,
                    'params' => [
                        'tabs'       => '$tabsRelated',
                        'name'       => '$LANG.tab_soldby',
                        'id'         => 'sold-by-npc',
                        'extraCols'  => '$['.implode(', ', $extraCols).']',
                        'hiddenCols' => "$['level', 'type']"
                    ]
                );
            }
        }
    }

    // tab: created by (spell) [for items its handled in Util::getLootSource()]
    if ($_id == 104)
    {
        $createdBy = new SpellList(array(['effect1Id', 45], ['effect2Id', 45], ['effect3Id', 45], 'OR']));
        if (!$createdBy->error)
        {
            if ($createdBy->hasSetFields(['reagent1']))
                $visCols = ['reagents'];

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $createdBy->getListviewData(),
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'name'        => '$LANG.tab_createdby',
                    'id'          => 'created-by',
                    'visibleCols' => isset($visCols) ? '$'.json_encode($visCols) : null
                ]
            );
        }
    }

    // tab: currency for
    if ($_id == 103)
        $w = 'iec.reqArenaPoints > 0';
    else if ($_id == 104)
        $w = 'iec.reqHonorPoints > 0';
    else
        $w = 'iec.reqItemId1 = '.$_itemId.' OR iec.reqItemId2 = '.$_itemId.' OR iec.reqItemId3 = '.$_itemId.' OR iec.reqItemId4 = '.$_itemId.' OR iec.reqItemId5 = '.$_itemId;

    $boughtBy = DB::Aowow()->selectCol('
        SELECT item FROM npc_vendor nv JOIN ?_itemExtendedCost iec ON iec.id = nv.extendedCost WHERE '.$w.'
        UNION
        SELECT item FROM game_event_npc_vendor genv JOIN ?_itemExtendedCost iec ON iec.id = genv.extendedCost WHERE '.$w
    );
    if ($boughtBy)
    {
        $boughtBy = new ItemList(array(['id', $boughtBy]));
        if (!$boughtBy->error)
        {
            $boughtBy->addGlobalsToJscript($smarty);

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $boughtBy->getListviewData(ITEMINFO_VENDOR, [TYPE_CURRENCY => $_id]),
                'params' => [
                    'tabs'      => '$tabsRelated',
                    'name'      => '$LANG.tab_currencyfor',
                    'id'        => 'currency-for',
                    'extraCols' => "$[Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack'), Listview.extraCols.cost]"
                ]
            );
        }
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// menuId 14: Skill    g_initPath()
//  tabId  0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => $pageData['title']." - ".Util::ucfirst(Lang::$game['skill']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_CURRENCY,
    'typeId' => $_id
));
$smarty->assign('redButtons', $pageData['buttons']);
$smarty->assign('community', CommunityContent::getAll(TYPE_CURRENCY, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('skill.tpl');

?>
