<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 0: Item     g_initPath()
//  tabId 0: Database g_initHeader()
class ItemsPage extends GenericPage
{
    use TrListPage;

    protected $forceTabs     = false;
    protected $gemScores     = [];

    protected $type          = Type::ITEM;
    protected $tpl           = 'items';
    protected $path          = [0, 0];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/filters.js'], [SC_JS_FILE, 'js/swfobject.js']];

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

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
         9 => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
         3 => [0, 1, 2, 3, 4, 5, 6, 7, 8],
        15 => [-2, -7, 0, 1, 2, 3, 4, 5],                   // -2: armor tokes, -7: flying mounts
        10 => true,
        12 => true,
        13 => true
    );

    private $filterOpts      = [];
    private $sharedLV        = array(                       // common listview components across all tabs
        'hiddenCols'  => [],
        'visibleCols' => [],
        'extraCols'   => []
    );

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        $this->filterObj = new ItemListFilter(false, ['parentCats' => $this->category]);

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('items'));
        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=weight-presets']);

        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        /*******************/
        /* evaluate filter */
        /*******************/

        // recreate form selection (must be evaluated first via getConditions())
        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['init' => 'items'];

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        $xCols = $this->filterObj->getExtraCols();
        if ($xCols)
            $this->filter['initData']['ec'] = $xCols;

        if ($x = $this->filterObj->getSetWeights())
            $this->filter['initData']['sw'] = $x;

        $menu = $this->createExtraMenus();
        foreach ($menu['type'][0] as $k => $str)
            if ($str && (!$menu['type'][1] || ($menu['type'][1] & (1 << $k))))
                $this->filter['type'][$k] = $str;

        foreach ($menu['slot'][0] as $k => $str)
            if ($str && (!$menu['slot'][1] || ($menu['slot'][1] & (1 << $k))))
                $this->filter['slot'][$k] = $str;

        if (isset($this->filter['slot'][INVTYPE_SHIELD]))   // "Off Hand" => "Shield"
            $this->filter['slot'][INVTYPE_SHIELD] = Lang::item('armorSubClass', 6);


        $infoMask = ITEMINFO_JSON;
        if (array_intersect([63, 64, 125], $xCols))         // 63:buyPrice; 64:sellPrice; 125:reqarenartng
            $infoMask |= ITEMINFO_VENDOR;

        if ($xCols)
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
        if (!empty($this->filter['upg']) && !empty($this->filterObj->getSetWeights()))
        {
            $upgItems = new ItemList(array(['id', array_keys($this->filter['upg'])]), ['extraOpts' => $this->filterObj->extraOpts]);
            if (!$upgItems->error)
            {
                $upgItemData = $upgItems->getListviewData($infoMask);
                $this->extendGlobalData($upgItems->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
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
        $grouping   = isset($this->filter['gb']) ? $this->filter['gb'] : null;
        $extraOpts  = [];
        $maxResults = CFG_SQL_LIMIT_DEFAULT;

        switch ($grouping)
        {
            // slot: (try to limit the lookups by class grouping and intersecting with preselected slots)
            // if intersect yields an empty array no lookups will occur
            case 1:
                if (isset($this->category[0]) && $this->category[0] == ITEM_CLASS_ARMOR)
                    $groups = $availableSlots[ITEM_CLASS_ARMOR];
                else if (isset($this->category[0]) && $this->category[0] == ITEM_CLASS_WEAPON)
                    $groups = $availableSlots[ITEM_CLASS_WEAPON];
                else
                    $groups = array_merge($availableSlots[ITEM_CLASS_ARMOR], $availableSlots[ITEM_CLASS_WEAPON]);

                if (isset($this->filter['sl']))             // skip lookups for unselected slots
                    $groups = array_intersect($groups, (array)$this->filter['sl']);

                if (!empty($this->filter['upg']))           // skip lookups for slots we dont have items to upgrade for
                    $groups = array_intersect($groups, (array)$this->filter['upg']);

                if ($groups)
                {
                    $nameSource = Lang::item('inventoryType');
                    $this->forceTabs = true;
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
                    $nameSource[$l] = Lang::game('level').' '.$l;
                }

                if ($groups)
                {
                    $l = -end($groups);
                    $groups[] = $l;                         // push last value as negativ to signal misc group after $this level
                    $extraOpts = ['i' => ['o' => ['itemlevel DESC']]];
                    $nameSource[$l] = Lang::item('tabOther');
                    $this->forceTabs = true;
                }

                break;
            case 3:                                         // source
                $groups = [1, 2, 3, 4, 5, 10, 11, 12, 0];
                $nameSource = Lang::game('sources');
                $this->forceTabs = true;

                break;
            // none
            default:
                $grouping  = 0;
                $groups[0] = null;
        }

        /*****************************/
        /* create lv-tabs for groups */
        /*****************************/

        foreach ($groups as $group)
        {
            switch ($grouping)
            {
                case 1:
                    $finalCnd = array_merge($conditions, [['slot', $group], $maxResults]);
                    break;
                case 2:
                    $finalCnd = array_merge($conditions, [['itemlevel', abs($group), $group > 0 ? null : '<'], $maxResults]);
                    break;
                case 3:
                    $finalCnd = array_merge($conditions, [$group ? ['src.src'.$group, null, '!'] : ['src.typeId', null], $maxResults]);
                    break;
                default:
                    $finalCnd = $conditions;
            }

            $items = new ItemList($finalCnd, ['extraOpts' => array_merge($extraOpts, $this->filterObj->extraOpts)]);

            if ($items->error)
                continue;

            $tabData = array_merge(
                ['data' => $items->getListviewData($infoMask)],
                $this->sharedLV
            );
            $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $upg = [];
            if ($upgItemData)
            {
                if ($grouping == 1)                         // slot: match upgradeItem to slot
                {
                    $upg = array_keys(array_filter($this->filter['upg'], function ($v) use ($group) {
                        return $v == $group;
                    }));

                    foreach ($upg as $uId)
                        $tabData['data'][$uId] = $upgItemData[$uId];

                    if ($upg)
                        $tabData['_upgradeIds'] = $upg;
                }
                else if ($grouping)
                {
                    $upg = array_keys($this->filter['upg']);
                    $tabData['_upgradeIds'] = $upg;
                    foreach ($upgItemData as $uId => $data) // using numeric keys => cant use array_merge
                        $tabData['data'][$uId] = $data;
                }
            }

            if ($grouping)
            {
                switch ($grouping)
                {
                    case 1:
                        $tabData['id'] = 'slot-'.$group;
                        break;
                    case 2:
                        $tabData['id'] = $group > 0 ? 'level-'.$group : 'other';
                        break;
                    case 3:
                        $tabData['id'] = $group ? 'source-'.$group : 'unknown';
                        break;
                }

                $tabData['name'] = $nameSource[$group];
                $tabData['tabs'] = '$tabsGroups';
            }

            if (!empty($this->filterObj->getSetWeights()))
                if ($items->hasSetFields(['armor']))
                    $tabData['visibleCols'][] = 'armor';

            // create note if search limit was exceeded; overwriting 'note' is intentional
            if ($items->getMatches() > $maxResults && count($groups) > 1)
            {
                $tabData['_truncated'] = 1;

                $cls      = isset($this->category[0]) ? '='.$this->category[0] : '';
                $override = ['gb' => ''];
                if ($upg)
                    $override['upg'] = implode(':', $upg);

                switch ($grouping)
                {
                    case 1:
                        $override['sl'] = $group;
                        $tabData['note'] = '$$WH.sprintf(LANG.lvnote_viewmoreslot, \''.$cls.'\', \''.$this->filterObj->getFilterString($override).'\')';
                        break;
                    case 2:
                        if ($group > 0)
                        {
                            $override['minle'] = $group;
                            $override['maxle'] = $group;
                        }
                        else
                            $override['maxle'] = abs($group) - 1;

                        $tabData['note'] = '$$WH.sprintf(LANG.lvnote_viewmorelevel, \''.$cls.'\', \''.$this->filterObj->getFilterString($override).'\')';
                        break;
                    case 3:
                        if ($_ = [null, 3, 4, 5, 6, 7, 9, 10, 11][$group])
                            $tabData['note'] = '$$WH.sprintf(LANG.lvnote_viewmoresource, \''.$cls.'\', \''.$this->filterObj->getFilterString($override, ['cr' => 128, 'crs' => $_, 'crv' => 0]).'\')';

                        break;
                }
            }
            else if ($items->getMatches() > $maxResults)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsfound', $items->getMatches(), CFG_SQL_LIMIT_DEFAULT);
                $tabData['_truncated'] = 1;
            }

            foreach ($tabData as $k => $p)
                if (!$p && $k != 'data')
                    unset($tabData[$k]);

            if ($grouping)
                $tabData['hideCount'] = 1;

            $tabData['data'] = array_values($tabData['data']);

            $this->lvTabs[] = [ItemList::$brickFile, $tabData];
        }

        // reformat for use in template
        if (!empty($this->filter['upg']))
            $this->filter['upg'] = implode(':', array_keys($this->filter['upg']));

        // whoops, we have no data? create emergency content
        if (empty($this->lvTabs))
        {
            $this->forceTabs = false;
            $this->lvTabs[]  = [ItemList::$brickFile, ['data' => []]];
        }
    }

    protected function postCache()
    {
        // sort for dropdown-menus
        Lang::sort('game', 'ra');
        Lang::sort('game', 'cl');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        if (!$this->category)
            return;

        if (isset($this->category[2]) && is_array(Lang::item('cat', $this->category[0], 1, $this->category[1])))
            $tPart = Lang::item('cat', $this->category[0], 1, $this->category[1], 1, $this->category[2]);
        else if (isset($this->category[1]) && is_array(Lang::item('cat', $this->category[0])))
            $tPart = Lang::item('cat', $this->category[0], 1, $this->category[1]);
        else if ($this->category[0] == 0 && isset($this->filter['ty']) && !is_array($this->filter['ty']))
            $tPart = Lang::item('cat', 0, 1, $this->filter['ty']);
        else
            $tPart = Lang::item('cat', $this->category[0]);

        array_unshift($this->title, is_array($tPart) ? $tPart[0] : $tPart);
    }

    protected function generatePath()
    {
        foreach ($this->category as $c)
            $this->path[] = $c;

        // if slot-dropdown is available && Armor && $path points to Armor-Class
        $form = $this->filterObj->getForm();
        if (count($this->path) == 4 && $this->category[0] == 4 && isset($form['sl']) && count($form['sl']) == 1)
            $this->path[] = $form['sl'][0];
        else if (isset($this->category[0]) && $this->category[0] == 0 && isset($form['ty']) && count($form['ty']) == 1)
            $this->path[] = $form['ty'][0];
    }

    // fetch best possible gems for chosen weights
    private function createGemScores()
    {
        $gemScores = [];

        if (empty($this->filterObj->getSetWeights()))
            return [];

        if (!empty($this->filter['gm']))
        {
            $this->sharedLV['computeDataFunc'] = '$fi_scoreSockets';

            $q    = intVal($this->filter['gm']);
            $mask = 0xE;
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
                $q    = !$i ? ITEM_QUALITY_RARE : intVal($this->filter['gm']);    // meta gems are always included.. ($q is backReferenced)
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
        $this->sharedLV['sort']           = ['-score', 'name'];

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
            $menu['slot'] = [Lang::item('inventoryType'), null];
            asort($menu['slot'][0]);
        }
        else
        {
            if (isset($this->category[2]) && is_array(Lang::item('cat', $this->category[0], 1, $this->category[1])))
                $catList = Lang::item('cat', $this->category[0], 1, $this->category[1], 1, $this->category[2]);
            else if (isset($this->category[1]) && is_array(Lang::item('cat', $this->category[0])))
                $catList = Lang::item('cat', $this->category[0], 1, $this->category[1]);
            else
                $catList = Lang::item('cat', $this->category[0]);

            switch ($this->category[0])
            {
                case 0:
                    $menu['type'] = [Lang::item('cat', 0, 1), null];

                    if (!isset($this->category[1]) || in_array($this->category[1], [6, -3]))
                    {
                        $menu['slot'] = [Lang::item('inventoryType'), 0x63EFEA];
                        asort($menu['slot'][0]);
                    }
                    break;
                case 2:
                    if (!isset($this->category[1]))
                        $menu['type'] = [Lang::spell('weaponSubClass'), null];

                    $menu['slot'] = [Lang::item('inventoryType'), 0x262A000];
                    asort($menu['slot'][0]);
                    break;
                case 4:
                    if (!isset($this->category[1]))
                    {
                        $menu['slot'] = [Lang::item('inventoryType'), 0x10895FFE];
                        $menu['type'] = [Lang::item('cat', 4, 1), null];
                    }
                    else if (in_array($this->category[1], [1, 2, 3, 4]))
                        $menu['slot'] = [Lang::item('inventoryType'), 0x7EA];

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
