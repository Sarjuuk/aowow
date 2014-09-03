<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 0: Item     g_initPath()
//  tabId 0: Database g_initHeader()
class ItemsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_ITEM;
    protected $tpl           = 'items';
    protected $path          = [0, 0];
    protected $tabId         = 0;
    protected $mode          = CACHETYPE_PAGE;
    protected $js            = ['filters.js', 'swfobject.js'];
    protected $validCats     = array(                       // if > 0 class => subclass
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
            -2 => true,                                     // Rings
            -3 => true,                                     // Amulets
            -4 => true,                                     // Trinkets
            -5 => true,                                     // Off-hand Frills
            -6 => true,                                     // Cloaks
            -7 => true,                                     // Tabards
            -8 => true                                      // Shirts
        ),
         1 => [0, 1, 2, 3, 4, 5, 6, 7, 8],
         0 => array(
             7 => true,
             0 => true,
             2 => [1, 2],                                   // Elixirs: [Battle, Guardian]
             3 => true,
             5 => true,
             6 => true,                                     // Item Enhancements (Permanent)
            -3 => true,                                     // Item Enhancements (Temporary)
             1 => true,
             4 => true,
             8 => true
        ),
        16 => array(                                        // Glyphs by class: [major, minor]
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
         9 => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],   // some of the books to propper professions
         3 => [0, 1, 2, 3, 4, 5, 6, 7, 8],
        15 => [-2, -7, 0, 1, 2, 3, 4, 5],                   // -2: armor tokes, -7: flying mounts               fuck it .. need major overhaul
        10 => true,
        12 => true,                                         // todo: contains enchantments
        13 => true
    );

    private $sharedLV        = array(                       // common listview components across all tabs
        'hiddenCols'  => [],
        'visibleCols' => [],
        'extraCols'   => []
    );

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new ItemListFilter();
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::$game['items']);
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $this->addJS('?data=weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $conditions = [];

        /*******************/
        /* evaluate filter */
        /*******************/

        // recreate form selection (must be evaluated first via getConditions())
        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $this->filter = array_merge($this->filterObj->getForm('form'), $this->filter);
        $this->filter['query'] = @$_GET['filter'] ?: NULL;
        $this->filter['fi']    =  $this->filterObj->getForm();

        $menu = $this->createExtraMenus();

        foreach ($menu['type'][0] as $k => $str)
            if ($str && (!$menu['type'][1] || ($menu['type'][1] & (1 << $k))))
                $this->filter['type'][$k] = $str;

        foreach ($menu['slot'][0] as $k => $str)
            if ($str && (!$menu['slot'][1] || ($menu['slot'][1] & (1 << $k))))
                $this->filter['slot'][$k] = $str;

        if (isset($this->filter['slot'][INVTYPE_SHIELD]))   // "Off Hand" => "Shield"
            $this->filter['slot'][INVTYPE_SHIELD] = Lang::$item['armorSubClass'][6];

        $xCols = $this->filterObj->getForm('extraCols', true);

        $infoMask = ITEMINFO_JSON;
        if (array_intersect([63, 64, 125], $xCols))         // 63:buyPrice; 64:sellPrice; 125:reqarenartng
            $infoMask |= ITEMINFO_VENDOR;

        if (!empty($this->filter['fi']['extraCols']))
            $this->sharedLV['extraCols'] = '$fi_getExtraCols(fi_extraCols, '.(isset($this->filter['gm']) ? $this->filter['gm'] : 0).', '.(array_intersect([63], $xCols) ? 1 : 0).')';

        if ($this->filterObj->error)
            $this->sharedLV['_errors'] = '$1';

        /******************/
        /* set conditions */
        /******************/

        if (isset($this->category[0]))
            $conditions[] = ['i.class', $this->category[0]];
        if (isset($this->category[1]))
            $conditions[] = ['i.subClass', $this->category[1]];
        if (isset($this->category[2]))
            $conditions[] = ['i.subSubClass', $this->category[2]];

        /***********************/
        /* handle auto-gemming */
        /***********************/

        $this->gemScores = $this->createGemScores();

        /*************************/
        /* handle upgrade search */
        /*************************/

        $upgItemData = [];
        if (!empty($this->filter['upg']) && !empty($this->filter['fi']['setWeights']))
        {
            $upgItems = new ItemList(array(['id', array_keys($this->filter['upg'])]), ['extraOpts' => $this->filterObj->extraOpts]);
            if (!$upgItems->error)
            {
                $this->extendGlobalData($upgItems->getJSGlobals());
                $upgItemData = $upgItems->getListviewData($infoMask);
            }
        }

        if ($upgItemData)                                   // check if upItems cover multiple slots
        {
            $singleSlot = true;
            $ref = reset($this->filter['upg']);
            foreach ($this->filter['upg'] as $slot)
            {
                if ($slot == $ref)
                    continue;

                $singleSlot = false;
                break;
            }

            if ($singleSlot && empty($this->filter['gb']))  // enforce group by slot
                $this->filter['gb'] = 1;
            else if (!$singleSlot)                          // multiples can only be grouped by slot
            {
                $this->filter['gb'] = 1;
                $maxResults = 25;
                $this->sharedLV['customFilter'] = '$fi_filterUpgradeListview';
            }
        }

        /********************************************************************************************************************************/
        /* group by                                                                                                                     */
        /*                                                                                                                              */
        /* cases that make sense:                                                                                                       */
        /* no upgItems             -> everything goes                                                                                   */
        /*  1 upgItems             OR                                                                                                   */
        /*  N upgItems (same slot) -> gb:none   - disabled                                                                              */
        /*                         -> gb:slot   - limited to slot of the upgItems (in theory weapons create a tab for each weapon type) */
        /*                         -> gb:level  - upgItems is added to all tabs                                                         */
        /*                         -> gb:source - upgItems is added to all tabs                                                         */
        /*  N upgItems (random)    -> gb:none   - disabled                                                                              */
        /*                         -> gb:slot   - only slots existing within the upgItems; match upgItems to slot                       */
        /*                         -> gb:level  - disabled                                                                              */
        /*                         -> gb:source - disabled                                                                              */
        /********************************************************************************************************************************/

        $availableSlots = array(
            ITEM_CLASS_ARMOR  => [INVTYPE_HEAD, INVTYPE_NECK, INVTYPE_SHOULDERS, INVTYPE_CHEST, INVTYPE_WAIST, INVTYPE_LEGS, INVTYPE_FEET, INVTYPE_WRISTS, INVTYPE_HANDS, INVTYPE_FINGER, INVTYPE_TRINKET, INVTYPE_SHIELD, INVTYPE_CLOAK],
            ITEM_CLASS_WEAPON => [INVTYPE_WEAPON, INVTYPE_RANGED, INVTYPE_2HWEAPON, INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPONOFFHAND, INVTYPE_THROWN, INVTYPE_HOLDABLE]
        );
        $groups     = [];
        $nameSource = [];
        $gbField    = '';
        $extraOpts  = [];
        $maxResults = CFG_SQL_LIMIT_DEFAULT;

        switch (@$this->filter['gb'])
        {
            // slot: (try to limit the lookups by class grouping and intersecting with preselected slots)
            // if intersect yields an empty array no lookups will occur
            case 3:                                         // todo(med): source .. well .. no, not at the moment .. the database doesn't event have a field for that, so reroute to slots
            case 1:
                if (isset($this->category[0]) && $this->category[0] == ITEM_CLASS_ARMOR)
                    $groups = $availableSlots[ITEM_CLASS_ARMOR];
                else if (isset($this->category[0]) && $this->category[0] == ITEM_CLASS_WEAPON)
                    $groups = $availableSlots[ITEM_CLASS_WEAPON];
                else
                    $groups = array_merge($availableSlots[ITEM_CLASS_ARMOR], $availableSlots[ITEM_CLASS_WEAPON]);

                if (isset($this->filter['sl']))             // skip lookups for unselected slots
                    $groups = array_intersect($groups, (array)$this->filter['sl']);

                if (isset($this->filter['upg']))            // skip lookups for slots we dont have items to upgrade for
                    $groups = array_intersect($groups, (array)$this->filter['upg']);

                if ($groups)
                {
                    $nameSource = Lang::$item['inventoryType'];
                    $this->forceTabs = true;
                    $gbField = 'slot';
                }

                break;
            case 2:                                         // itemlevel: first, try to find 10 level steps within range (if given) as tabs
                // ohkayy, maybe i need to rethink $this
                $this->filterOpts = $this->filterObj->extraOpts;
                $this->filterOpts['is']['o'] = [null];      // remove 'order by' from itemStats
                $extraOpts = array_merge($this->filterOpts, ['i'  => ['g' => ['itemlevel'], 'o' => ['itemlevel DESC']]]);

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
                    $groups[] = $l;                         // push last value as negativ to signal misc group after $this level
                    $extraOpts = ['i' => ['o' => ['itemlevel DESC']]];
                    $nameSource[$l] = Lang::$item['tabOther'];
                    $this->forceTabs = true;
                    $gbField = 'itemlevel';
                }

                break;
            case 3:
                $groups = array_filter(array_keys(Lang::$game['sources']));
                array_walk($groups, function (&$v, $k) {
                    $v = $v.':';
                });

                $nameSource = Lang::$game['sources'];
                $this->forceTabs = true;
                $gbField = 'source';

                break;
            // none
            default:
                $groups[0] = null;
        }

        /*****************************/
        /* create lv-tabs for groups */
        /*****************************/

        foreach ($groups as $group)
        {
            $finalCnd = $gbField ? array_merge($conditions, [[$gbField, abs($group), $group > 0 ? null : '<'], $maxResults]) : $conditions;

            $items = new ItemList($finalCnd, ['extraOpts' => array_merge($extraOpts, $this->filterObj->extraOpts)]);

            if ($items->error)
                continue;

            $this->extendGlobalData($items->getJSGlobals());
            $tab = array(
                'file'   => 'item',
                'data'   => $items->getListviewData($infoMask),
                'params' => $this->sharedLV
            );

            $upg = [];
            if ($upgItemData)
            {
                if ($gbField == 'slot')                     // match upgradeItem to slot
                {
                    $upg = array_keys(array_filter($this->filter['upg'], function ($v) use ($group) {
                        return $v == $group;
                    }));

                    foreach ($upg as $uId)
                        $tab['data'][$uId] = $upgItemData[$uId];

                    if ($upg)
                        $tab['params']['_upgradeIds'] = '$'.json_encode($upg, JSON_NUMERIC_CHECK);
                }
                else if ($gbField)
                {
                    $upg = array_keys($this->filter['upg']);
                    $tab['params']['_upgradeIds'] = '$'.json_encode($upg, JSON_NUMERIC_CHECK);
                    foreach ($upgItemData as $uId => $data) // using numeric keys => cant use array_merge
                        $tab['data'][$uId] = $data;
                }
            }

            if ($gbField)
            {
                $tab['params']['id']   = $group > 0 ? $gbField.'-'.$group : 'other';
                $tab['params']['name'] = $nameSource[$group];
                $tab['params']['tabs'] = '$tabsGroups';
            }

            if (!empty($this->filter['fi']['setWeights']))
                if ($items->hasSetFields(['armor']))
                    $tab['params']['visibleCols'][] = 'armor';

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
                    if ($_ = @[null, 3, 4, 5, 6, 7, 8][$group])
                    {
                        $note  = 'lvnote_viewmoresource';
                        $addCr = ['cr' => 128, 'crs' => $_, 'crv' => 0];
                    }

                    $override = ['gb' => ''];
                }

                if ($upg)
                    $override['upg'] = implode(':', $upg);

                $cls = isset($this->category[0]) ? '='.$this->category[0] : '';
                $this->filterUrl = $this->filterObj->urlize($override, $addCr);

                if ($note)
                    $tab['params']['note'] = '$$WH.sprintf(LANG.'.$note.', \''.$cls.'\', \''.$this->filterUrl.'\')';
            }
            else if ($items->getMatches() > $maxResults)
            {
                $tab['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsfound', $items->getMatches(), CFG_SQL_LIMIT_DEFAULT);
                $tab['params']['_truncated'] = 1;
            }

            if (!empty($tab['params']['hiddenCols']))
                $tab['params']['hiddenCols'] = '$'.json_encode($tab['params']['hiddenCols']);

            if (!empty($tab['params']['visibleCols']))
                $tab['params']['visibleCols'] = '$'.json_encode($tab['params']['visibleCols']);

            foreach ($tab['params'] as $k => $p)
                if (!$p)
                    unset($tab['params'][$k]);

            if ($gbField)
                $tab['params']['hideCount'] = '$1';

            $this->lvTabs[] = $tab;
        }

        // reformat for use in template
        if (isset($this->filter['upg']))
            $this->filter['upg'] = implode(':', array_keys($this->filter['upg']));

        // whoops, we have no data? create emergency content
        if (empty($this->lvTabs))
            $this->lvTabs[] = ['file' => 'item', 'data' => [], 'params' => []];

        // sort for dropdown-menus
        asort(Lang::$game['ra']);
        asort(Lang::$game['cl']);
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        if (!$this->category)
            return;

        if (isset($this->category[2]))
            $tPart = Lang::$item['cat'][$this->category[0]][1][$this->category[1]][1][$this->category[2]];
        else if (isset($this->category[1]))
            $tPart = Lang::$item['cat'][$this->category[0]][1][$this->category[1]];
        else
            $tPart = Lang::$item['cat'][$this->category[0]];

        array_unshift($this->title, is_array($tPart) ? $tPart[0] : $tPart);
    }

    protected function generatePath()
    {
        foreach ($this->category as $c)
            $this->path[] = $c;

    // if slot-dropdown is available && Armor && $path points to Armor-Class
    $form = $this->filterObj->getForm('form');
    if (count($this->path) == 4 && $this->category[0] == 4 && isset($form['sl']) && !is_array($form['sl']))
        $this->path[] = $form['sl'];
    }

    // fetch best possible gems for chosen weights
    private function createGemScores()
    {
        $gemScores = [];

        if (empty($this->filter['fi']['setWeights']))
            return [];

        if (!empty($this->filter['gm']))
        {
            $this->sharedLV['computeDataFunc'] = '$fi_scoreSockets';

            $q    = intVal($this->filter['gm']);
            $mask = 14;
            $cnd  = [10, ['class', ITEM_CLASS_GEM], ['gemColorMask', &$mask, '&'], ['quality', &$q]];
            if (!isset($this->filter['jc']))
                $cnd[] = ['itemLimitCategory', 0];          // Jeweler's Gems

            If ($this->filterObj->wtCnd)
                $cnd[] = $this->filterObj->wtCnd;

            $anyColor = new ItemList($cnd, ['extraOpts' => $this->filterObj->extraOpts]);
            if (!$anyColor->error)
            {
                $this->extendGlobalData($anyColor->getJSGlobals());
                $gemScores[0] = array_values($anyColor->getListviewData(ITEMINFO_GEM));
            }

            for ($i = 0; $i < 4; $i++)
            {
                $mask = 1 << $i;
                $q    = !$i ? 3 : intVal($this->filter['gm']);    // meta gems are always included.. ($q is backReferenced)
                $byColor = new ItemList($cnd, ['extraOpts' => $this->filterObj->extraOpts]);
                if (!$byColor->error)
                {
                    $this->extendGlobalData($byColor->getJSGlobals());
                    $gemScores[$mask] = array_values($byColor->getListviewData(ITEMINFO_GEM));
                }
            }
        }

        $this->sharedLV['onBeforeCreate'] = '$fi_initWeightedListview';
        $this->sharedLV['onAfterCreate']  = '$fi_addUpgradeIndicator';
        $this->sharedLV['sort']           = "$['-score', 'name']";

        array_push($this->sharedLV['hiddenCols'], 'type', 'source');

        return $gemScores;
    }

    // display available submenus 'type' and 'slot', if applicable
    private function createExtraMenus()
    {
        $menu = array(
            'type' => [[], null],
            'slot' => [[], null]
        );

        if (!$this->category)
        {
            $menu['slot'] = [Lang::$item['inventoryType'], null];
            asort($menu['slot'][0]);
        }
        else
        {
            if (isset($this->category[2]))
                $catList = Lang::$item['cat'][$this->category[0]][1][$this->category[1]][1][$this->category[2]];
            else if (isset($this->category[1]))
                $catList = Lang::$item['cat'][$this->category[0]][1][$this->category[1]];
            else
                $catList = Lang::$item['cat'][$this->category[0]];

            switch ($this->category[0])
            {
                case 0:
                    if (!isset($this->category[1]))
                        $menu['type'] = [Lang::$item['cat'][0][1], null];

                    if (!isset($this->category[1]) || in_array($this->category[1], [6, -3]))
                    {
                        $menu['slot'] = [Lang::$item['inventoryType'], 0x63EFEA];
                        asort($menu['slot'][0]);
                    }
                    break;
                case 2:
                    if (!isset($this->category[1]))
                        $menu['type'] = [Lang::$spell['weaponSubClass'], null];

                    $menu['slot'] = [Lang::$item['inventoryType'], 0x262A000];
                    asort($menu['slot'][0]);
                    break;
                case 4:
                    if (!isset($this->category[1]))
                    {
                        $menu['slot'] = [Lang::$item['inventoryType'], 0x10895FFE];
                        $menu['type'] = [Lang::$item['cat'][4][1], null];
                    }
                    else if (in_array($this->category[1], [1, 2, 3, 4]))
                        $menu['slot'] = [Lang::$item['inventoryType'], 0x7EA];

                    asort($menu['slot'][0]);
                    break;
                case 16:
                    if (!isset($this->category[2]))
                        $this->sharedLV['visibleCols'][] = 'glyph';
                case 1:
                    if ($this->category[0] == 1)
                        $this->sharedLV['visibleCols'][] = 'slots';
                case 3:
                    if (!isset($this->category[1]))
                        asort($catList[1]);
                case 7:
                case 9:
                    $this->sharedLV['hiddenCols'][] = 'slot';
                case 15:
                    if (!isset($this->category[1]))
                        $menu['type'] = [$catList[1], null];

                    break;
            }
        }

        return $menu;
    }
}

?>