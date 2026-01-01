<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::ITEM;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'items';
    protected  string $pageName    = 'items';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 0];

    protected  array  $dataLoader  = ['weight-presets'];
    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = array(                   // if > 0 class => subclass
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

    public array $gemScores = [];
    public array $typeList  = [];                           // rightPanel - content by context
    public array $slotList  = [];                           // rightPanel - INV_TYPE_Xs

    private array $filterOpts = [];
    private array $sharedLV   = array(                      // common listview components across all tabs
        'hiddenCols'  => [],
        'visibleCols' => [],
        'extraCols'   => []
    );

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new ItemListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
        if ($this->filter->shouldReload)
        {
            $_SESSION['error']['fi'] = $this->filter::class;
            $get = $this->filter->buildGETParam();
            $this->forward('?' . $this->pageName . $this->subCat . ($get ? '&filter=' . $get : ''));
        }
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('items'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;


        /*******************/
        /* evaluate filter */
        /*******************/

        $fiForm = $this->filter->values;

        $xCols = $this->filter->fiExtraCols;

        $infoMask = ITEMINFO_JSON;
        if (array_intersect([63, 64, 125], $xCols))         // 63:buyPrice; 64:sellPrice; 125:reqarenartng
            $infoMask |= ITEMINFO_VENDOR;

        if ($xCols)
            $this->sharedLV['extraCols'] = '$fi_getExtraCols(fi_extraCols, '.($fiForm['gm'] ?? 0).', '.(array_intersect([63], $xCols) ? 1 : 0).')';

        $this->createExtraMenus();                          // right side panels in search form


        /*************/
        /* Menu Path */
        /*************/

        foreach ($this->category as $c)
            $this->breadcrumb[] = $c;

        // if slot-dropdown is available && Armor && $path points to Armor-Class
        if (count($this->breadcrumb) == 4 && $this->category[0] == 4 && count($fiForm['sl']) == 1)
            $this->breadcrumb[] = $fiForm['sl'][0];
        else if (isset($this->category[0]) && $this->category[0] == 0 && count($fiForm['ty']) == 1)
            $this->breadcrumb[] = $fiForm['ty'][0];


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);

        if ($this->category)
        {
            if (isset($this->category[2]) && is_array(Lang::item('cat', $this->category[0], 1, $this->category[1])))
                $tPart = Lang::item('cat', $this->category[0], 1, $this->category[1], 1, $this->category[2]);
            else if (isset($this->category[1]) && is_array(Lang::item('cat', $this->category[0])))
                $tPart = Lang::item('cat', $this->category[0], 1, $this->category[1]);
            else if ($this->category[0] == 0 && count($fiForm['ty']) == 1)
                $tPart = Lang::item('cat', 0, 1, $fiForm['ty'][0]);
            else
                $tPart = Lang::item('cat', $this->category[0]);

            array_unshift($this->title, is_array($tPart) ? $tPart[0] : $tPart);
        }


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

        $this->createGemScores($fiForm['gm'] ?? 0);


        /*************************/
        /* handle upgrade search */
        /*************************/

        $upgItemData = [];
        if ($this->filter->upgrades && $this->filter->fiSetWeights)
        {
            $upgItems = new ItemList(array(['id', array_keys($this->filter->upgrades)]), ['extraOpts' => $this->filter->extraOpts]);
            if (!$upgItems->error)
            {
                $upgItemData = $upgItems->getListviewData($infoMask);
                $this->extendGlobalData($upgItems->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        if ($upgItemData)                                   // check if upgItems cover multiple slots
        {
            $singleSlot = true;
            $ref = reset($this->filter->upgrades);
            foreach ($this->filter->upgrades as $slot)
            {
                if ($slot == $ref)
                    continue;

                $singleSlot = false;
                break;
            }

            if ($singleSlot && empty($fiForm['gb']))        // enforce group by slot
                $fiForm['gb'] = ItemListFilter::GROUP_BY_SLOT;
            else if (!$singleSlot)                          // multiples can only be grouped by slot
            {
                $fiForm['gb'] = ItemListFilter::GROUP_BY_SLOT;
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
        $grouping   = $fiForm['gb'] ?? ItemListFilter::GROUP_BY_NONE;
        $extraOpts  = [];
        $maxResults = Listview::DEFAULT_SIZE;
        $forceTabs  = false;
        $tabs       = [];

        switch ($grouping)
        {
            // slot: (try to limit the lookups by class grouping and intersecting with preselected slots)
            // if intersect yields an empty array no lookups will occur
            case ItemListFilter::GROUP_BY_SLOT:
                if (isset($this->category[0]) && $this->category[0] == ITEM_CLASS_ARMOR)
                    $groups = $availableSlots[ITEM_CLASS_ARMOR];
                else if (isset($this->category[0]) && $this->category[0] == ITEM_CLASS_WEAPON)
                    $groups = $availableSlots[ITEM_CLASS_WEAPON];
                else
                    $groups = array_merge($availableSlots[ITEM_CLASS_ARMOR], $availableSlots[ITEM_CLASS_WEAPON]);

                if ($fiForm['sl'])                          // skip lookups for unselected slots
                    $groups = array_intersect($groups, $fiForm['sl']);

                if ($this->filter->upgrades)                // skip lookups for slots we dont have items to upgrade for
                    $groups = array_intersect($groups, $this->filter->upgrades);

                if ($groups)
                {
                    $nameSource = Lang::item('inventoryType');
                    $forceTabs = true;
                }

                break;
            case ItemListFilter::GROUP_BY_LEVEL:            // itemlevel: first, try to find 10 level steps within range (if given) as tabs
                // ohkayy, maybe i need to rethink $this
                $this->filterOpts = $this->filter->extraOpts;
                $this->filterOpts['is']['o'] = [null];      // remove 'order by' from ?_item_stats
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
                    $forceTabs = true;
                }

                break;
            case ItemListFilter::GROUP_BY_SOURCE:           // source
                $groups = [1, 2, 3, 4, 5, 10, 11, 12, 0];
                $nameSource = Lang::game('sources');
                $forceTabs = true;

                break;
            // none
            default:
                $grouping  = ItemListFilter::GROUP_BY_NONE;
                $groups[0] = null;
        }

        // write back 'gb' to filter
        if ($grouping)
            $this->filter->values['gb'] = $grouping;


        /*****************************/
        /* create lv-tabs for groups */
        /*****************************/

        foreach ($groups as $group)
        {
            $finalCnd = match ($grouping)
            {
                ItemListFilter::GROUP_BY_SLOT   => array_merge($conditions, [['slot', $group], $maxResults]),
                ItemListFilter::GROUP_BY_LEVEL  => array_merge($conditions, [['itemlevel', abs($group), $group > 0 ? null : '<'], $maxResults]),
                ItemListFilter::GROUP_BY_SOURCE => array_merge($conditions, [$group ? ['src.src'.$group, null, '!'] : ['src.typeId', null], $maxResults]),
                default                         => $conditions
            };

            $items = new ItemList($finalCnd, ['extraOpts' => array_merge($extraOpts, $this->filter->extraOpts), 'calcTotal' => true]);

            if ($items->error)
                continue;

            // if sold by vendor; append cost column
            if ($this->filter->getSetCriteria(92) && is_array($this->sharedLV['extraCols']))
            {
                $this->sharedLV['extraCols']['cost'] = '$Listview.extraCols.cost';
                $data = $items->getListviewData($infoMask | ITEMINFO_VENDOR);
            }
            else
                $data = $items->getListviewData($infoMask);

            $tabData = array_merge(
                ['data' => $data],
                $this->sharedLV
            );
            $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $upg = [];
            if ($upgItemData)
            {
                // slot: match upgradeItem to slot
                if ($grouping == ItemListFilter::GROUP_BY_SLOT)
                {
                    $upg = array_keys(array_filter($this->filter->upgrades, fn($x) => $x == $group));

                    foreach ($upg as $uId)
                        $tabData['data'][$uId] = $upgItemData[$uId];

                    if ($upg)
                        $tabData['_upgradeIds'] = $upg;
                }
                else if ($grouping)
                {
                    $upg = array_keys($this->filter->upgrades);
                    $tabData['_upgradeIds'] = $upg;
                    foreach ($upgItemData as $uId => $data) // using numeric keys => cant use array_merge
                        $tabData['data'][$uId] = $data;
                }
            }

            if ($grouping)
            {
                $tabData['id'] = match ($grouping)
                {
                    ItemListFilter::GROUP_BY_SLOT   => 'slot-'.$group,
                    ItemListFilter::GROUP_BY_LEVEL  => $group > 0 ? 'level-'.$group : 'other',
                    ItemListFilter::GROUP_BY_SOURCE => $group ? 'source-'.$group : 'unknown'
                };

                $tabData['name'] = $nameSource[$group];
                $tabData['tabs'] = '$tabsGroups';
            }

            if ($this->filter->fiSetWeights)
                if ($items->hasSetFields('tplArmor'))
                    $tabData['visibleCols'][] = 'armor';

            // create note if search limit was exceeded; overwriting 'note' is intentional
            if ($items->getMatches() > $maxResults && count($groups) > 1)
            {
                $tabData['_truncated'] = 1;

                $catg     = isset($this->category[0]) ? '='.$this->category[0] : '';
                $override = ['gb' => ''];
                if ($upg)
                    $override['upg'] = $upg;

                switch ($grouping)
                {
                    case ItemListFilter::GROUP_BY_SLOT:
                        $override['sl'] = $group;
                        $tabData['note'] = '$$WH.sprintf(LANG.lvnote_viewmoreslot, \''.$catg.'\', \''.$this->filter->buildGETParam($override).'\')';
                        break;
                    case ItemListFilter::GROUP_BY_LEVEL:
                        if ($group > 0)
                        {
                            $override['minle'] = $group;
                            $override['maxle'] = $group;
                        }
                        else
                            $override['maxle'] = abs($group) - 1;

                        $tabData['note'] = '$$WH.sprintf(LANG.lvnote_viewmorelevel, \''.$catg.'\', \''.$this->filter->buildGETParam($override).'\')';
                        break;
                    case ItemListFilter::GROUP_BY_SOURCE:
                        if ($_ = [null, 3, 4, 5, 6, 7, 9, 10, 11][$group])
                            $tabData['note'] = '$$WH.sprintf(LANG.lvnote_viewmoresource, \''.$catg.'\', \''.$this->filter->buildGETParam($override, ['cr' => 128, 'crs' => $_, 'crv' => 0]).'\')';

                        break;
                }
            }
            else if ($items->getMatches() > $maxResults)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsfound', $items->getMatches(), Listview::DEFAULT_SIZE);
                $tabData['_truncated'] = 1;
            }

            // inherited from >sharedLV, may be empty
            if (!$tabData['hiddenCols'])
                unset($tabData['hiddenCols']);
            if (!$tabData['visibleCols'])
                unset($tabData['visibleCols']);
            if (!$tabData['extraCols'])
                unset($tabData['extraCols']);

            if ($grouping)
                $tabData['hideCount'] = 1;

            $tabs[] = $tabData;
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsGroups', $forceTabs && $tabs);

        // whoops, we have no data? create emergency content
        if (!count($tabs))
            $tabs[] = ['data' => []];

        foreach ($tabs as $t)
            $this->lvTabs->addListviewTab(new Listview($t, ItemList::$brickFile));

        $this->redButtons[BUTTON_WOWHEAD] = true;
        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        parent::generate();

        $this->setOnCacheLoaded([self::class, 'onBeforeDisplay']);
    }

    // fetch best possible gems for chosen weights
    private function createGemScores(int $gemQuality) : void
    {
        if (!$this->filter->fiSetWeights)
            return;

        $this->sharedLV['onBeforeCreate'] = '$fi_initWeightedListview';
        $this->sharedLV['onAfterCreate']  = '$fi_addUpgradeIndicator';
        $this->sharedLV['sort']           = ['-score', 'name'];

        array_push($this->sharedLV['hiddenCols'], 'type', 'source');

        if (!$gemQuality)
            return;

        $this->sharedLV['computeDataFunc'] = '$fi_scoreSockets';

        $q    = intVal($gemQuality);
        $mask = 0xE;
        $cnd  = [10, ['class', ITEM_CLASS_GEM], ['gemColorMask', &$mask, '&'], ['quality', &$q]];
        if (!isset($fiForm['jc']))
            $cnd[] = ['itemLimitCategory', 0];              // Jeweler's Gems

        if ($this->filter->wtCnd)
            $cnd[] = $this->filter->wtCnd;

        $anyColor = new ItemList($cnd, ['extraOpts' => $this->filter->extraOpts]);
        if (!$anyColor->error)
        {
            $this->extendGlobalData($anyColor->getJSGlobals());
            $this->gemScores[0] = array_values($anyColor->getListviewData(ITEMINFO_GEM));
        }

        for ($i = 0; $i < 4; $i++)
        {
            $mask = 1 << $i;
            $q    = !$i ? ITEM_QUALITY_RARE : intVal($gemQuality);    // meta gems are always included.. ($q is backReferenced)
            $byColor = new ItemList($cnd, ['extraOpts' => $this->filter->extraOpts]);
            if (!$byColor->error)
            {
                $this->extendGlobalData($byColor->getJSGlobals());
                $this->gemScores[$mask] = array_values($byColor->getListviewData(ITEMINFO_GEM));
            }
        }
    }

    // display available submenus 'type' and 'slot', if applicable
    private function createExtraMenus() : void
    {
        $typeData = $slotData = [];
        $typeMask = $slotMask = 0x0;

        if (!$this->category)
        {
            $slotData = Lang::item('inventoryType');
            asort($slotData);
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
                    $typeData = Lang::item('cat', 0, 1);

                    if (!isset($this->category[1]) || in_array($this->category[1], [6, -3]))
                    {
                        $slotData = Lang::item('inventoryType');
                        $slotMask = 0x63EFEA;
                        asort($slotData);
                    }
                    break;
                case 2:
                    if (!isset($this->category[1]))
                        $typeData = Lang::spell('weaponSubClass');

                    $slotData = Lang::item('inventoryType');
                    $slotMask = 0x262A000;
                    asort($slotData);
                    break;
                case 4:
                    if (!isset($this->category[1]))
                    {
                        $slotData = Lang::item('inventoryType');
                        $slotMask = 0x10895FFE;
                        $typeData = Lang::item('cat', 4, 1);
                    }
                    else if (in_array($this->category[1], [1, 2, 3, 4]))
                    {
                        $slotData = Lang::item('inventoryType');
                        $slotMask = 0x7EA;
                    }
                    asort($slotData);
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
                        $typeData = $catList[1];

                    break;
            }
        }

        foreach ($typeData as $k => $str)
            if ($str && (!$typeMask || ($typeMask & (1 << $k))))
                $this->typeList[$k] = is_array($str) ? $str[0] : $str;

        foreach ($slotData as $k => $str)                   // "Off Hand" => "Shield"
            if ($str && (!$slotMask || ($slotMask & (1 << $k))))
                $this->slotList[$k] = $k == INVTYPE_SHIELD ? Lang::item('armorSubClass', 6) : $str;
    }

    protected static function onBeforeDisplay() : void
    {
        // sort for dropdown-menus
        Lang::sort('game', 'ra');
        Lang::sort('game', 'cl');
    }
}

?>
