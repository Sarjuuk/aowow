<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cats       = Util::extractURLParams($pageParam);
$path       = [0, 0];
$title      = [Lang::$game['items']];
$filter     = ['panel1' => false, 'panel2' => false];
$filterHash = !empty($_GET['filter']) ? '#'.sha1(serialize($_GET['filter'])) : null;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_ITEM, -1, implode('.', $cats).$filterHash, User::$localeId]);
$validCats  = array(                                        // if > 0 class => subclass
     2 => [15, 13, 0, 4, 7, 6, 10, 1, 5, 8, 2, 18, 3, 16, 19, 20, 14],
     4 => array(
         0 => true,
         1 => [1, 3, 5, 6, 7, 8, 9, 10],
         2 => [1, 3, 5, 6, 7, 8, 9, 10],
         3 => [1, 3, 5, 6, 7, 8, 9, 10],
         4 => [1, 3, 5, 6, 7, 8, 9, 10],
         6 => true,
         7 => true,
         8 => true,
         9 => true,
        10 => true,
        -2 => true,                                         // Rings
        -3 => true,                                         // Amulets
        -4 => true,                                         // Trinkets
        -5 => true,                                         // Off-hand Frills
        -6 => true,                                         // Cloaks
        -7 => true,                                         // Tabards
        -8 => true                                          // Shirts
    ),
     1 => [0, 1, 2, 3, 4, 5, 6, 7, 8],
     0 => array(
         7 => true,
         0 => true,
         2 => [1, 2],                                       // elixirs: [Battle, Guardian]
         3 => true,
         5 => true,
         6 => true,                                         // Item Enhancements (Permanent)
        -3 => true,                                         // Item Enhancements (Temporary)
         1 => true,
         4 => true,
         8 => true
    ),
    16 => array(                                            // Glyphs by class: [major, minor]
        1  => [1, 2],
        2  => [1, 2],
        3  => [1, 2],
        4  => [1, 2],
        5  => [1, 2],
        6  => [1, 2],
        7  => [1, 2],
        8  => [1, 2],
        9  => [1, 2],
        11 => [1, 2]
    ),
     7 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
     6 => [2, 3],
    11 => [2, 3],
     9 => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],       // some of the books to propper professions
     3 => [0, 1, 2, 3, 4, 5, 6, 7, 8],
    15 => [-2, -7, 0, 1, 2, 3, 4, 5],                       // -2: armor tokes, -7: flying mounts               fuck it .. need major overhaul
    10 => true,
    12 => true,                                             // todo: contains enchantments
    13 => true
);

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    $conditions  = [];
    $visibleCols = [];
    $hiddenCols  = [];

    if ($cats)
        $path = array_merge($path, $cats);

    // display available submenu and slot, if applicable
    $type = $slot = [[], null];
    if (!$cats)
    {
        $slot = [Lang::$item['inventoryType'], null];
        asort($slot[0]);
    }
    else
    {
        if (isset($cats[2]))
            $catList = Lang::$item['cat'][$cats[0]][1][$cats[1]][1][$cats[2]];
        else if (isset($cats[1]))
            $catList = Lang::$item['cat'][$cats[0]][1][$cats[1]];
        else
            $catList = Lang::$item['cat'][$cats[0]];

        array_unshift($title, is_array($catList) ? $catList[0] : $catList);

        switch ($cats[0])
        {
            case 0:
                if (!isset($cats[1]))
                    $type = [Lang::$item['cat'][0][1], null];

                if (!isset($cats[1]) || in_array($cats[1], [6, -3]))
                {
                    $slot = [Lang::$item['inventoryType'], 0x63EFEA];
                    asort($slot[0]);
                }
                break;
            case 2:
                if (!isset($cats[1]))
                    $type = [Lang::$spell['weaponSubClass'], null];

                $slot = [Lang::$item['inventoryType'], 0x262A000];
                asort($slot[0]);
                break;
            case 4:
                if (!isset($cats[1]))
                {
                    $slot = [Lang::$item['inventoryType'], 0x10895FFE];
                    $type = [Lang::$item['cat'][4][1], null];
                }
                else if (in_array($cats[1], [1, 2, 3, 4]))
                    $slot = [Lang::$item['inventoryType'], 0x7EA];

                asort($slot[0]);
                break;
            case 16:
                if (!isset($cats[2]))
                    $visibleCols[] = 'glyph';
            case 1:
                if ($cats[0] == 1)
                    $visibleCols[] = 'slots';
            case 3:
                if (!isset($cats[1]))
                    asort($catList[1]);
            case 7:
            case 9:
                $hiddenCols[] = 'slot';
            case 15:
                if (!isset($cats[1]))
                    $type = [$catList[1], null];

                break;
        }
    }

    foreach ($type[0] as $k => $str)
        if ($str && (!$type[1] || ($type[1] & (1 << $k))))
            $filter['type'][$k] = $str;

    foreach ($slot[0] as $k => $str)
        if ($str && (!$slot[1] || ($slot[1] & (1 << $k))))
            $filter['slot'][$k] = $str;

    if (isset($filter['slot'][INVTYPE_SHIELD]))             // "Off Hand" => "Shield"
        $filter['slot'][INVTYPE_SHIELD] = Lang::$item['armorSubClass'][6];

    $itemFilter = new ItemListFilter();

    // recreate form selection
    $filter = array_merge($itemFilter->getForm('form'), $filter);
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['fi']    =  $itemFilter->getForm();

    $xCols = $itemFilter->getForm('extraCols', true);

    // if slot-dropdown is available && Armor && $path points to Armor-Class
    if (count($path) == 4 && $cats[0] == 4 && isset($filter['sl']) && !is_array($filter['sl']))
        $path[] = $filter['sl'];

    $infoMask = ITEMINFO_JSON;
    if (array_intersect([63, 64, 125], $xCols))             // 63:buyPrice; 64:sellPrice; 125:reqarenartng
        $infoMask |= ITEMINFO_VENDOR;


    // menuId 0: Item     g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page'   => array(
            'title'  => implode(' - ', $title),
            'path'   => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'    => 0,
            'subCat' => $pageParam !== null ? '='.$pageParam : '',
            'reqJS'  => array(
                STATIC_URL.'/js/filters.js',
                STATIC_URL.'/js/swfobject.js',
                '?data=weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']
            )
        ),
        'lv'     => array(
            'tabs'      => [],
            'isGrouped' => false
        )
    );


    /*
        set conditions
    */

    if (isset($cats[0]))
        $conditions[] = ['i.class', $cats[0]];
    if (isset($cats[1]))
        $conditions[] = ['i.subClass', $cats[1]];
    if (isset($cats[2]))
        $conditions[] = ['i.subSubClass', $cats[2]];

    if ($_ = $itemFilter->getConditions())
        $conditions[] = $_;


    /*
        shared parameter between all possible lv-tabs
    */

    $sharedLvParams = [];
    $upgItemData    = [];
    if (!empty($filter['fi']['extraCols']))
    {
        $gem  = empty($filter['gm']) ? 0 : $filter['gm'];
        $cost = array_intersect([63], $xCols) ? 1 : 0;
        $sharedLvParams['extraCols'] = '$fi_getExtraCols(fi_extraCols, '.$gem.', '.$cost.')';
    }

    if (!empty($filter['fi']['setWeights']))
    {
        if (!empty($filter['gm']))
        {
            $sharedLvParams['computeDataFunc'] = '$fi_scoreSockets';

            $q    = intVal($filter['gm']);
            $mask = 14;
            $cnd  = [10, ['class', ITEM_CLASS_GEM], ['gemColorMask', &$mask, '&'], ['quality', &$q]];
            if (!isset($filter['jc']))
                $cnd[] = ['itemLimitCategory', 0];          // Jeweler's Gems

            If ($itemFilter->wtCnd)
                $cnd[] = $itemFilter->wtCnd;

            $anyColor = new ItemList($cnd, ['extraOpts' => $itemFilter->extraOpts]);
            if (!$anyColor->error)
            {
                $anyColor->addGlobalsToJScript();
                $pageData['page']['gemScores'][0] = array_values($anyColor->getListviewData(ITEMINFO_GEM));
            }

            for ($i = 0; $i < 4; $i++)
            {
                $mask = 1 << $i;
                $q    = !$i ? 3 : intVal($filter['gm']);    // meta gems are always included.. ($q is backReferenced)
                $byColor = new ItemList($cnd, ['extraOpts' => $itemFilter->extraOpts]);
                if (!$byColor->error)
                {
                    $byColor->addGlobalsToJScript();
                    $pageData['page']['gemScores'][$mask] = array_values($byColor->getListviewData(ITEMINFO_GEM));
                }
            }

            $pageData['page']['gemScores'] = json_encode($pageData['page']['gemScores'], JSON_NUMERIC_CHECK);
        }

        $sharedLvParams['onBeforeCreate'] = '$fi_initWeightedListview';
        $sharedLvParams['onAfterCreate']  = '$fi_addUpgradeIndicator';
        $sharedLvParams['sort']           = "$['-score', 'name']";

        array_push($hiddenCols, 'type', 'source');
    }

    if ($itemFilter->error)
        $sharedLvParams['_errors'] = '$1';

    if (!empty($filter['upg']) && !empty($filter['fi']['setWeights']))
    {
                                                                          // v poke it to use item_stats
        $upgItems = new ItemList(array(['id', array_keys($filter['upg'])], ['is.id', null, '!']), ['extraOpts' => $itemFilter->extraOpts]);
        if (!$upgItems->error)
        {
            $upgItems->addGlobalsToJScript();
            $upgItemData = $upgItems->getListviewData($infoMask);
        }
    }

    /*
        group by

        cases that make sense:
        no upgItems             -> everything goes
         1 upgItems             OR
         N upgItems (same slot) -> gb:none   - disabled
                                -> gb:slot   - limited to slot of the upgItems (in theory weapons create a tab for each weapon type)
                                -> gb:level  - upgItems is added to all tabs
                                -> gb:source - upgItems is added to all tabs
         N upgItems (random)    -> gb:none   - disabled
                                -> gb:slot   - only slots existing within the upgItems; match upgItems to slot
                                -> gb:level  - disabled
                                -> gb:source - disabled
    */
    $availableSlots = array(
        ITEM_CLASS_ARMOR  => [INVTYPE_HEAD, INVTYPE_NECK, INVTYPE_SHOULDERS, INVTYPE_CHEST, INVTYPE_WAIST, INVTYPE_LEGS, INVTYPE_FEET, INVTYPE_WRISTS, INVTYPE_HANDS, INVTYPE_FINGER, INVTYPE_TRINKET, INVTYPE_SHIELD, INVTYPE_CLOAK],
        ITEM_CLASS_WEAPON => [INVTYPE_WEAPON, INVTYPE_RANGED, INVTYPE_2HWEAPON, INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPONOFFHAND, INVTYPE_THROWN, INVTYPE_HOLDABLE]
    );
    $sourcesGlobalToItem  = [1 => 3, 2 => 4, 3 => 5, 4 => 6, 5 => 7, 6 => 8];
    $groups     = [];
    $nameSource = [];
    $gbField    = '';
    $extraOpts  = [];
    $singleSlot = true;
    $maxResults = CFG_SQL_LIMIT_DEFAULT;

    if ($upgItemData)
    {
        // check if upItems cover multiple slots
        $ref = reset($filter['upg']);
        foreach ($filter['upg'] as $slot)
        {
            if ($slot == $ref)
                continue;

            $singleSlot = false;
            break;
        }

        if ($singleSlot && empty($filter['gb']))            // enforce group by slot
            $filter['gb'] = 1;
        else if (!$singleSlot)                              // multiples can only be grouped by slot
        {
            $filter['gb'] = 1;
            $maxResults   = 25;
            $sharedLvParams['customFilter'] = '$fi_filterUpgradeListview';
        }
    }

    switch (@$filter['gb'])
    {
        // slot: (try to limit the lookups by class grouping and intersecting with preselected slots)
        // if intersect yields an empty array no lookups will occur
        case 1:
        case 3:             // todo(med): source .. well .. no, not at the moment .. the database doesn't event have a field for that, so reroute to slots
            if (isset($cats[0]) && $cats[0] == ITEM_CLASS_ARMOR)
                $groups = isset($filter['sl']) ? array_intersect($availableSlots[ITEM_CLASS_ARMOR], (array)$filter['sl']) : $availableSlots[ITEM_CLASS_ARMOR];
            else if (isset($cats[0]) && $cats[0] == ITEM_CLASS_WEAPON)
                $groups = isset($filter['sl']) ? array_intersect($availableSlots[ITEM_CLASS_WEAPON], (array)$filter['sl']) : $availableSlots[ITEM_CLASS_WEAPON];
            else
            {
                $groups = array_merge($availableSlots[ITEM_CLASS_ARMOR], $availableSlots[ITEM_CLASS_WEAPON]);
                if (isset($filter['sl']))
                    $groups = array_intersect($groups, (array)$filter['sl']);
            }

            if ($groups)
            {
                $nameSource = Lang::$item['inventoryType'];
                $pageData['lv']['isGrouped'] = true;
                $gbField = 'slot';
            }

            break;
        // itemlevel: first, try to find 10 level steps within range (if given) as tabs
        case 2:
            // ohkayy, maybe i need to rethink this
            $filterOpts = $itemFilter->extraOpts;
            $filterOpts['is']['o'] = [null];
            $extraOpts = array_merge($filterOpts, ['i'  => ['g' => ['itemlevel'], 'o' => ['itemlevel DESC']]]);

            $levelRef = new ItemList(array_merge($conditions, [10]), ['extraOpts' => $extraOpts]);

            foreach ($levelRef->iterate() as $_)
            {
                $l = $levelRef->getField('itemLevel');
                $groups[] = $l;
                $nameSource[$l] = Lang::$game['level'].' '.$l;
            }

            if ($groups)
            {
                $l = -end($groups);
                $groups[] = $l;                             // push last value as negativ to signal misc group after this level
                $extraOpts = ['i' => ['o' => ['itemlevel DESC']]];
                $nameSource[$l] = Lang::$item['tabOther'];
                $pageData['lv']['isGrouped'] = true;
                $gbField = 'itemlevel';
            }

            break;
        case 3:
            $groups = array_filter(array_keys(Lang::$game['sources']));
            array_walk($groups, function (&$v, $k) {
                $v = $v.':';
            });

            $nameSource = Lang::$game['sources'];
            $pageData['lv']['isGrouped'] = true;
            $gbField = 'source';

            break;
        // none
        default:
            $groups[0] = null;
    }

    foreach ($groups as $group)
    {
        $finalCnd = $gbField ? array_merge($conditions, [[$gbField, abs($group), $group > 0 ? null : '<'], $maxResults]) : $conditions;

        $items = new ItemList($finalCnd, ['extraOpts' => array_merge($extraOpts, $itemFilter->extraOpts)]);
        $items->addGlobalsToJscript();

        if ($items->error)
            continue;

        $tab = array(
            'data'   => $items->getListviewData($infoMask),
            'params' => $sharedLvParams
        );

        $upg = [];
        if ($upgItemData)
        {
            if ($gbField == 'slot')                         // match upgradeItem to slot
            {
                $upg = array_keys(array_filter($filter['upg'], function ($v) use ($group) {
                    return $v == $group;
                }));

                foreach ($upg as $uId)
                    $tab['data'][$uId] = $upgItemData[$uId];

                if ($upg)
                    $tab['params']['_upgradeIds'] = '$'.json_encode($upg, JSON_NUMERIC_CHECK);
            }
            else if ($gbField)
            {
                $upg = array_keys($filter['upg']);
                $tab['params']['_upgradeIds'] = '$'.json_encode($upg, JSON_NUMERIC_CHECK);
                foreach ($upgItemData as $uId => $data)     // using numeric keys => cant use array_merge
                    $tab['data'][$uId] = $data;
            }
        }

        if ($gbField)
        {
            $tab['params']['id']   = $group > 0 ? $gbField.'-'.$group : 'other';
            $tab['params']['name'] = $nameSource[$group];
            $tab['params']['tabs'] = '$tabsGroups';
        }

        if (!empty($filter['fi']['setWeights']))
            if ($items->hasSetFields(['armor']))
                $visibleCols[] = 'armor';

        // create note if search limit was exceeded; overwriting 'note' is intentional
        if ($items->getMatches() > $maxResults && count($groups) > 1)
        {
            $tab['params']['_truncated'] = 1;

            $addCr = [];
            if ($gbField == 'slot')
            {
                $note = 'lvnote_viewmoreslot';
                $override = ['sl' => $group, 'gb' => ''];
            }
            else if ($gbField == 'itemlevel')
            {
                $note = 'lvnote_viewmorelevel';
                if ($group > 0)
                    $override = ['minle' => $group, 'maxle' => $group, 'gb' => ''];
                else
                    $override = ['maxle' => abs($group) - 1, 'gb' => ''];
            }
            else if ($gbField == 'source')
            {
                if ($_ = @$sourcesGlobalToItem[$group])
                {
                    $note  = 'lvnote_viewmoresource';
                    $addCr = ['cr' => 128, 'crs' => $_, 'crv' => 0];
                }

                $override = ['gb' => ''];
            }

            if ($upg)
                $override['upg'] = implode(':', $upg);

            $cls = isset($cats[0]) ? '='.$cats[0] : '';
            $filterUrl = $itemFilter->urlize($override, $addCr);

            if ($note)
                $tab['params']['note'] = '$$WH.sprintf(LANG.'.$note.', \''.$cls.'\', \''.$filterUrl.'\')';
        }
        else if ($items->getMatches() > $maxResults)
        {
            $tab['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsfound', $items->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $tab['params']['_truncated'] = 1;
        }

        if ($hiddenCols)
            $tab['params']['hiddenCols'] = '$'.json_encode($hiddenCols);

        if ($visibleCols)
            $tab['params']['visibleCols'] = '$'.json_encode($visibleCols);

        if ($gbField)
            $tab['params']['hideCount'] = '$1';

        $pageData['lv']['tabs'][] = $tab;
    }

    if (isset($filter['upg']))
        $filter['upg'] = implode(':', array_keys($filter['upg']));

    // whoops, we have no data? create emergency content
    if (!$pageData['lv']['tabs'])
    {
        $pageData['lv']['isGrouped'] = false;
        $pageData['lv']['tabs'][] = ['data' => [], 'params' => []];
    }

    $smarty->saveCache($cacheKey, $pageData, $filter);
}


// sort for dropdown-menus
asort(Lang::$game['ra']);
asort(Lang::$game['cl']);

$smarty->updatePageVars($pageData['page']);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$item, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['lv']);

// load the page
$smarty->display('items.tpl');

?>
