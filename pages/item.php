<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 0: Item     g_initPath()
//  tabId 0: Database g_initHeader()
class ItemPage extends genericPage
{
    use DetailPage;

    protected $type          = TYPE_ITEM;
    protected $typeId        = 0;
    protected $tpl           = 'item';
    protected $path          = [0, 0];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $enhancedTT    = [];
    protected $js            = array(
        'swfobject.js',                                     // view in 3d, ok
        'profile.js',                                       // item upgrade search, also ok
        'filters.js'                                        // lolwut?
    );

    public function __construct($pageCall, $param)
    {
        parent::__construct($pageCall, $param);

        $conditions = [['i.id', intVal($param)]];

        $this->typeId = intVal($param);

        if ($this->mode == CACHE_TYPE_TOOLTIP)
        {
            // temp locale
            if (isset($_GET['domain']))
                Util::powerUseLocale($_GET['domain']);

            if (isset($_GET['rand']))
                $this->enhancedTT['r'] = $_GET['rand'];
            if (isset($_GET['ench']))
                $this->enhancedTT['e'] = $_GET['ench'];
            if (isset($_GET['gems']))
                $this->enhancedTT['g'] = explode(':', $_GET['gems']);
            if (isset($_GET['sock']))
                $this->enhancedTT['s'] = '';
        }
        else if ($this->mode == CACHE_TYPE_XML)
        {
            // temp locale
            if (isset($_GET['domain']))
                Util::powerUseLocale($_GET['domain']);

            // allow lookup by name for xml
            if (!is_numeric($param))
                $conditions = [['name_loc'.User::$localeId, utf8_encode(urldecode($param))]];
        }

        $this->subject = new ItemList($conditions);
        if ($this->subject->error)
            $this->notFound();

        if (!is_numeric($param))
            $this->typeId = $this->subject->id;

        $this->name = $this->subject->getField('name', true);

        if ($this->mode == CACHE_TYPE_PAGE)
        {
            $jsg = $this->subject->getJSGlobals(GLOBALINFO_EXTRA | GLOBALINFO_SELF, $extra);
            $this->extendGlobalData($jsg, $extra);
        }
    }

    protected function generatePath()
    {
        $_class    = $this->subject->getField('class');
        $_subClass = $this->subject->getField('subClass');

        if (in_array($_class, [ITEM_CLASS_REAGENT, ITEM_CLASS_GENERIC, ITEM_CLASS_PERMANENT]))
        {
            $this->path[] = ITEM_CLASS_MISC;                // misc.

            if ($_class == ITEM_CLASS_REAGENT)              // reagent
                $this->path[] = 1;
            else                                            // other
                $this->path[] = 4;
        }
        else
        {
            $this->path[] = $_class;

            if (!in_array($_class, [ITEM_CLASS_MONEY, ITEM_CLASS_QUEST, ITEM_CLASS_KEY]))
                $this->path[] = $_subClass;

            if ($_class == ITEM_CLASS_ARMOR && in_array($_subClass, [1, 2, 3, 4]))
            {
                if ($_ = $this->subject->getField('slot'));
                    $this->path[] = $_;
            }
            else if (($_class == ITEM_CLASS_CONSUMABLE && $_subClass == 2) || $_class == ITEM_CLASS_GLYPH)
                $this->path[] = $this->subject->getField('subSubClass');
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('item')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=weight-presets.zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $_flags     = $this->subject->getField('flags');
        $_slot      = $this->subject->getField('slot');
        $_class     = $this->subject->getField('class');
        $_subClass  = $this->subject->getField('subClass');
        $_bagFamily = $this->subject->getField('bagFamily');
        $_model     = $this->subject->getField('displayId');
        $_visSlots  = array(
            INVTYPE_HEAD,           INVTYPE_SHOULDERS,      INVTYPE_BODY,           INVTYPE_CHEST,          INVTYPE_WAIST,          INVTYPE_LEGS,           INVTYPE_FEET,           INVTYPE_WRISTS,
            INVTYPE_HANDS,          INVTYPE_WEAPON,         INVTYPE_SHIELD,         INVTYPE_RANGED,         INVTYPE_CLOAK,          INVTYPE_2HWEAPON,       INVTYPE_TABARD,         INVTYPE_ROBE,
            INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPONOFFHAND,  INVTYPE_HOLDABLE,       INVTYPE_THROWN,         INVTYPE_RANGEDRIGHT
        );

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // itemlevel
        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]) || $this->subject->getField('gemEnchantmentId'))
            $infobox[] = Lang::game('level').Lang::main('colon').$this->subject->getField('itemLevel');

        // account-wide
        if ($_flags & ITEM_FLAG_ACCOUNTBOUND)
            $infobox[] = Lang::item('accountWide');

        // side
        if ($si = $this->subject->json[$this->typeId]['side'])
            if ($si != 3)
                $infobox[] = Lang::main('side').Lang::main('colon').'[span class=icon-'.($si == 1 ? 'alliance' : 'horde').']'.Lang::game('si', $si).'[/span]';

        // consumable / not consumable
        if (!$_slot)
        {
            $hasUse = false;
            for ($i = 1; $i < 6; $i++)
            {
                if ($this->subject->getField('spellId'.$i) <= 0 || in_array($this->subject->getField('spellTrigger'.$i), [1, 2]))
                    continue;

                $hasUse = true;

                if ($this->subject->getField('spellCharges'.$i) >= 0)
                    continue;

                $tt = '[tooltip=tooltip_consumedonuse]'.Lang::item('consumable').'[/tooltip]';
                break;
            }

            if ($hasUse)
                $infobox[] = isset($tt) ? $tt : '[tooltip=tooltip_notconsumedonuse]'.Lang::item('nonConsumable').'[/tooltip]';
        }

        // related holiday
        if ($hId = $this->subject->getField('holidayId'))
            if ($hName = DB::Aowow()->selectRow('SELECT * FROM ?_holidays WHERE id = ?d', $hId))
                $infobox[] = Lang::game('eventShort').Lang::main('colon').'[url=?event='.$hId.']'.Util::localizedString($hName, 'name').'[/url]';

        // tool
        if ($tId = $this->subject->getField('totemCategory'))
            if ($tName = DB::Aowow()->selectRow('SELECT * FROM ?_totemcategory WHERE id = ?d', $tId))
                $infobox[] = Lang::item('tool').Lang::main('colon').'[url=?items&filter=cr=91;crs='.$tId.';crv=0]'.Util::localizedString($tName, 'name').'[/url]';

        // extendedCost
        if (!empty($this->subject->getExtendedCost([], $_reqRating)[$this->subject->id]))
        {
            $vendors  = $this->subject->getExtendedCost()[$this->subject->id];
            $each     = $this->subject->getField('stackable') > 1 ? '[color=q0] ('.Lang::item('each').')[/color]' : null;
            $handled  = [];
            $costList = [];
            foreach ($vendors as $npcId => $data)
            {
                $tokens   = [];
                $currency = [];

                if (!is_array($data))
                    continue;

                foreach ($data as $c => $qty)
                {
                    if (is_string($c))
                    {
                        unset($data[$c]);                   // unset miscData to prevent having two vendors /w the same cost being cached, because of different stock or rating-requirements
                        continue;
                    }

                    if ($c < 0)                             // currency items (and honor or arena)
                        $currency[] = -$c.','.$qty;
                    else if ($c > 0)                        // plain items (item1,count1,item2,count2,...)
                        $tokens[$c] = $c.','.$qty;
                }

                // display every cost-combination only once
                if (in_array(md5(serialize($data)), $handled))
                    continue;

                $handled[] = md5(serialize($data));

                $cost = isset($data[0]) ? '[money='.$data[0] : '[money';

                if ($tokens)
                    $cost .= ' items='.implode(',', $tokens);

                if ($currency)
                    $cost .= ' currency='.implode(',', $currency);

                $cost .= ']';

                $costList[] = $cost;
            }

            if (count($costList) == 1)
                $infobox[] = Lang::item('cost').Lang::main('colon').$costList[0].$each;
            else if (count($costList) > 1)
                $infobox[] = Lang::item('cost').$each.Lang::main('colon').'[ul][li]'.implode('[/li][li]', $costList).'[/li][/ul]';

            if ($_reqRating)
            {
                $res   = [];
                $i     = 0;
                $len   = 0;
                $parts = explode(' ', str_replace('<br>', ' ', sprintf(Lang::item('reqRating', $_reqRating[1]), $_reqRating[0])));
                foreach ($parts as $p)
                {
                    $res[$i][] = $p;
                    $len += (mb_strlen($p) + 1);

                    if ($len < 30)
                        continue;

                    $len = 0;
                    $i++;
                }
                foreach ($res as &$r)
                    $r = implode(' ', $r);

                $infobox[] = implode('[br]', $res);
            }
        }

        // repair cost
        if ($_ = $this->subject->getField('repairPrice'))
            $infobox[] = Lang::item('repairCost').Lang::main('colon').'[money='.$_.']';

        // avg auction buyout
        if (in_array($this->subject->getField('bonding'), [0, 2, 3]))
            if ($_ = Util::getBuyoutForItem($this->typeId))
                $infobox[] = '[tooltip=tooltip_buyoutprice]'.Lang::item('buyout.').'[/tooltip]'.Lang::main('colon').'[money='.$_.']'.$each;

        // avg money contained
        if ($_flags & ITEM_FLAG_OPENABLE)
            if ($_ = intVal(($this->subject->getField('minMoneyLoot') + $this->subject->getField('maxMoneyLoot')) / 2))
                $infobox[] = Lang::item('worth').Lang::main('colon').'[tooltip=tooltip_avgmoneycontained][money='.$_.'][/tooltip]';

        // if it goes into a slot it may be disenchanted
        if ($_slot && $_class != ITEM_CLASS_CONTAINER)
        {
            if ($this->subject->getField('disenchantId'))
            {
                $_ = $this->subject->getField('requiredDisenchantSkill');
                if ($_ < 1)                                 // these are some items, that never went live .. extremely rough emulation here
                    $_ = intVal($this->subject->getField('itemLevel') / 7.5) * 25;

                $infobox[] = Lang::item('disenchantable').'&nbsp;([tooltip=tooltip_reqenchanting]'.$_.'[/tooltip])';
            }
            else
                $infobox[] = Lang::item('cantDisenchant');
        }

        if (($_flags & ITEM_FLAG_MILLABLE) && $this->subject->getField('requiredSkill') == 773)
            $infobox[] = Lang::item('millable').'&nbsp;([tooltip=tooltip_reqinscription]'.$this->subject->getField('requiredSkillRank').'[/tooltip])';

        if (($_flags & ITEM_FLAG_PROSPECTABLE) && $this->subject->getField('requiredSkill') == 755)
            $infobox[] = Lang::item('prospectable').'&nbsp;([tooltip=tooltip_reqjewelcrafting]'.$this->subject->getField('requiredSkillRank').'[/tooltip])';

        if ($_flags & ITEM_FLAG_DEPRECATED)
            $infobox[] = '[tooltip=tooltip_deprecated]'.Lang::item('deprecated').'[/tooltip]';

        if ($_flags & ITEM_FLAG_NO_EQUIPCD)
            $infobox[] = '[tooltip=tooltip_noequipcooldown]'.Lang::item('noEquipCD').'[/tooltip]';

        if ($_flags & ITEM_FLAG_PARTYLOOT)
            $infobox[] = '[tooltip=tooltip_partyloot]'.Lang::item('partyLoot').'[/tooltip]';

        if ($_flags & ITEM_FLAG_REFUNDABLE)
            $infobox[] = '[tooltip=tooltip_refundable]'.Lang::item('refundable').'[/tooltip]';

        if ($_flags & ITEM_FLAG_SMARTLOOT)
            $infobox[] = '[tooltip=tooltip_smartloot]'.Lang::item('smartLoot').'[/tooltip]';

        if ($_flags & ITEM_FLAG_INDESTRUCTIBLE)
            $infobox[] = Lang::item('indestructible');

        if ($_flags & ITEM_FLAG_USABLE_ARENA)
            $infobox[] = Lang::item('useInArena');

        if ($_flags & ITEM_FLAG_USABLE_SHAPED)
            $infobox[] = Lang::item('useInShape');

        // cant roll need
        if ($this->subject->getField('flagsExtra') & 0x0100)
            $infobox[] = '[tooltip=tooltip_cannotrollneed]'.Lang::item('noNeedRoll').'[/tooltip]';

        // fits into keyring
        if ($_bagFamily & 0x0100)
            $infobox[] = Lang::item('atKeyring');

        /****************/
        /* Main Content */
        /****************/

        $_cu = in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) || $this->subject->getField('gemEnchantmentId');

        $this->headIcons  = [$this->subject->getField('iconString'), $this->subject->getField('stackable')];
        $this->infobox    = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->tooltip    = $this->subject->renderTooltip(true);
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['color' => 'ff'.Util::$rarityColorStings[$this->subject->getField('quality')], 'linkId' => 'item:'.$this->typeId.':0:0:0:0:0:0:0:0'],
            BUTTON_VIEW3D  => in_array($_slot, $_visSlots) && $_model ? ['displayId' => $this->subject->getField('displayId'), 'slot' => $_slot, 'type' => TYPE_ITEM, 'typeId' => $this->typeId] : false,
            BUTTON_COMPARE => $_cu,
            BUTTON_EQUIP   => in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]),
            BUTTON_UPGRADE => $_cu ? ['class' => $_class, 'slot' => $_slot] : false
        );

        // availablility
        $this->disabled = false;    // todo (med): get itemSources (which are not yet in DB :x) or

        // pageText
        if ($next = $this->subject->getField('pageTextId'))
        {
            $this->addJS('Book.js');
            $this->addCSS(['path' => 'Book.css']);

            while ($next)
            {
                $row = DB::World()->selectRow('SELECT *, Text as Text_loc0 FROM page_text pt LEFT JOIN locales_page_text lpt ON pt.ID = lpt.entry WHERE pt.ID = ?d', $next);
                $next = $row['NextPageID'];
                $this->pageText[] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
            }
        }

        // subItems
        $this->subject->initSubItems();
        if (!empty($this->subject->subItems[$this->typeId]))
        {
            uaSort($this->subject->subItems[$this->typeId], function($a, $b) { return strcmp($a['name'], $b['name']); });
            $this->subItems = array(
                'data'    => array_values($this->subject->subItems[$this->typeId]),
                'quality' => $this->subject->getField('quality')
            );

            // merge identical stats and names for normal users (e.g. spellPower of a specific school became generel spellPower with 3.0)
            if (!User::isInGroup(U_GROUP_EMPLOYEE))
            {
                for ($i = 1; $i < count($this->subItems['data']); $i++)
                {
                    $prev = &$this->subItems['data'][$i - 1];
                    $cur  = &$this->subItems['data'][$i];
                    if ($prev['jsonequip'] == $cur['jsonequip'] && $prev['name'] == $cur['name'])
                    {
                        $prev['chance'] += $cur['chance'];
                        array_splice($this->subItems['data'], $i , 1);
                        $i = 1;
                    }
                }
            }
        }

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_items WHERE alliance_id = ?d OR horde_id = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altItem = new ItemList(array(['id', abs($pendant)]));
            if (!$altItem->error)
            {
                $this->transfer = sprintf(
                    Lang::item('_transfer'),
                    $altItem->id,
                    $altItem->getField('quality'),
                    $altItem->getField('iconString'),
                    $altItem->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', 1) : Lang::game('si', 2)
                );
            }
        }

        /**************/
        /* Extra Tabs */
        /**************/

        // tabs: this item is contained in..
        $lootTabs  = new Loot();
        $createdBy = [];
        if ($lootTabs->getByItem($this->typeId))
        {
            $this->extendGlobalData($lootTabs->jsGlobals);

            foreach ($lootTabs->iterate() as $idx => $tab)
            {
                if (!$tab[1])
                    continue;

                if ($idx == 16)
                    $createdBy = array_column($tab[1], 'id');

                $this->lvTabs[] = array(
                    'file'   => $tab[0],
                    'data'   => $tab[1],
                    'params' => [
                        'name'        => $tab[2],
                        'id'          => $tab[3],
                        'extraCols'   => $tab[4] ? '$['.implode(', ', array_unique($tab[4])).']' : null,
                        'hiddenCols'  => $tab[5] ? '$ '.Util::toJSON( array_unique($tab[5]))     : null,
                        'visibleCols' => $tab[6] ? '$'. Util::toJSON( array_unique($tab[6]))     : null
                    ]
                );
            }
        }

        // tabs: this item contains..
        $sourceFor = array(
             [LOOT_ITEM,        $this->subject->id,                       '$LANG.tab_contains',      'contains',      ['Listview.extraCols.percent'], []                          , []],
             [LOOT_PROSPECTING, $this->subject->id,                       '$LANG.tab_prospecting',   'prospecting',   ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []],
             [LOOT_MILLING,     $this->subject->id,                       '$LANG.tab_milling',       'milling',       ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []],
             [LOOT_DISENCHANT,  $this->subject->getField('disenchantId'), '$LANG.tab_disenchanting', 'disenchanting', ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []]
        );

        $reqQuest = [];
        foreach ($sourceFor as $sf)
        {
            $lootTab = new Loot();
            if ($lootTab->getByContainer($sf[0], $sf[1]))
            {
                $this->extendGlobalData($lootTab->jsGlobals);
                $sf[4] = array_merge($sf[4], $lootTab->extraCols);

                foreach ($lootTab->iterate() as $lv)
                {
                    if (!$lv['quest'])
                        continue;

                    $sf[4] = array_merge($sf[4], ['Listview.extraCols.condition']);

                    $reqQuest[$lv['id']] = 0;

                    $lv['condition'][0][$this->typeId][] = [[CND_QUESTTAKEN, &$reqQuest[$lv['id']]]];
                }

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $lootTab->getResult(),
                    'params' => [
                        'name'        => $sf[2],
                        'id'          => $sf[3],
                        'extraCols'   => $sf[4] ? "$[".implode(', ', array_unique($sf[4]))."]" : null,
                        'hiddenCols'  => $sf[5] ? "$".Util::toJSON($sf[5]) : null,
                        'visibleCols' => $sf[6] ? '$'.Util::toJSON($sf[6]) : null
                    ]
                );
            }
        }

        if ($reqIds = array_keys($reqQuest))                // apply quest-conditions as back-reference
        {
            $conditions = array(
                'OR',
                ['reqSourceItemId1', $reqIds], ['reqSourceItemId2', $reqIds],
                ['reqSourceItemId3', $reqIds], ['reqSourceItemId4', $reqIds],
                ['reqItemId1', $reqIds], ['reqItemId2', $reqIds], ['reqItemId3', $reqIds],
                ['reqItemId4', $reqIds], ['reqItemId5', $reqIds], ['reqItemId6', $reqIds]
            );

            $reqQuests = new QuestList($conditions);
            $reqQuests->getJSGlobals(GLOBALINFO_SELF);

            foreach ($reqQuests->iterate() as $qId => $__)
            {
                if (empty($reqQuests->requires[$qId][TYPE_ITEM]))
                    continue;

                foreach ($reqIds as $rId)
                    if (in_array($rId, $reqQuests->requires[$qId][TYPE_ITEM]))
                        $reqQuest[$rId] = $reqQuests->id;
            }
        }

        // tab: container can contain
        if ($this->subject->getField('slots') > 0)
        {
            $contains = new ItemList(array(['bagFamily', $_bagFamily, '&'], ['slots', 1, '<'], CFG_SQL_LIMIT_NONE));
            if (!$contains->error)
            {
                $this->extendGlobalData($contains->getJSGlobals(GLOBALINFO_SELF));

                $hCols = ['side'];
                if (!$contains->hasSetFields(['slot']))
                    $hCols[] = 'slot';

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $contains->getListviewData(),
                    'params' => [
                        'name'       => '$LANG.tab_cancontain',
                        'id'         => 'can-contain',
                        'hiddenCols' => '$'.Util::toJSON($hCols)
                    ]
                );
            }
        }

        // tab: can be contained in (except keys)
        else if ($_bagFamily != 0x0100)
        {
            $contains = new ItemList(array(['bagFamily', $_bagFamily, '&'], ['slots', 0, '>'], CFG_SQL_LIMIT_NONE));
            if (!$contains->error)
            {
                $this->extendGlobalData($contains->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $contains->getListviewData(),
                    'params' => [
                        'name'       => '$LANG.tab_canbeplacedin',
                        'id'         => 'can-be-placed-in',
                        'hiddenCols' => "$['side']"
                    ]
                );
            }
        }

        // tab: criteria of
        $conditions = array(
            ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM, ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM, ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM, ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM]],
            ['ac.value1', $this->typeId]
        );

        $criteriaOf = new AchievementList($conditions);
        if (!$criteriaOf->error)
        {
                $this->extendGlobalData($criteriaOf->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

                $hCols = [];
                if (!$criteriaOf->hasSetFields(['reward_loc0']))
                    $hCols = ['rewards'];

                $this->lvTabs[] = array(
                    'file'   => 'achievement',
                    'data'   => $criteriaOf->getListviewData(),
                    'params' => [
                        'name'        => '$LANG.tab_criteriaof',
                        'id'          => 'criteria-of',
                        'visibleCols' => "$['category']",
                        'hiddenCols'  => '$'.Util::toJSON($hCols)
                    ]
                );
        }

        // tab: reagent for
        $conditions = array(
            'OR',
            ['reagent1', $this->typeId], ['reagent2', $this->typeId], ['reagent3', $this->typeId], ['reagent4', $this->typeId],
            ['reagent5', $this->typeId], ['reagent6', $this->typeId], ['reagent7', $this->typeId], ['reagent8', $this->typeId]
        );

        $reagent = new SpellList($conditions);
        if (!$reagent->error)
        {
            $this->extendGlobalData($reagent->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $this->lvTabs[] = array(
                'file'   => 'spell',
                'data'   => $reagent->getListviewData(),
                'params' => [
                    'name'        => '$LANG.tab_reagentfor',
                    'id'          => 'reagent-for',
                    'visibleCols' => "$['reagents']"
                ]
            );
        }

        // tab: unlocks (object or item)
        $lockIds = DB::Aowow()->selectCol(
            'SELECT id FROM ?_lock WHERE        (type1 = 1 AND properties1 = ?d) OR
            (type2 = 1 AND properties2 = ?d) OR (type3 = 1 AND properties3 = ?d) OR
            (type4 = 1 AND properties4 = ?d) OR (type5 = 1 AND properties5 = ?d)',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );

        if ($lockIds)
        {
            // objects
            $lockedObj = new GameObjectList(array(['lockId', $lockIds]));
            if (!$lockedObj->error)
            {
                $this->lvTabs[] = array(
                    'file'   => 'object',
                    'data'   => $lockedObj->getListviewData(),
                    'params' => [
                        'name' => '$LANG.tab_unlocks',
                        'id'   => 'unlocks-object'
                    ]
                );
            }

            // items (generally unused. It's the spell on the item, that unlocks stuff)
            $lockedItm = new ItemList(array(['lockId', $lockIds]));
            if (!$lockedItm->error)
            {
                $this->extendGlobalData($lockedItm->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $lockedItm->getListviewData(),
                    'params' => [
                        'name' => '$LANG.tab_unlocks',
                        'id'   => 'unlocks-item'
                    ]
                );
            }
        }

        // tab: see also
        $conditions = array(
            ['id', $this->typeId, '!'],
            [
                'OR',
                ['name_loc'.User::$localeId, $this->subject->getField('name', true)],
                [
                    'AND',
                    ['class',         $_class],
                    ['subClass',      $_subClass],
                    ['slot',          $_slot],
                    ['itemLevel',     $this->subject->getField('itemLevel') - 15, '>'],
                    ['itemLevel',     $this->subject->getField('itemLevel') + 15, '<'],
                    ['quality',       $this->subject->getField('quality')],
                    ['requiredClass', $this->subject->getField('requiredClass')]
                ]
            ]
        );

        $saItems = new ItemList($conditions);
        if (!$saItems->error)
        {
            $this->extendGlobalData($saItems->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = array(
                'file'   => 'item',
                'data'   => $saItems->getListviewData(),
                'params' => [
                    'name' => '$LANG.tab_seealso',
                    'id'   => 'see-also'
                ]
            );
        }

        // tab: starts (quest)
        if ($qId = $this->subject->getField('startQuest'))
        {
            $starts = new QuestList(array(['id', $qId]));
            if (!$starts->error)
            {
                $this->extendGlobalData($starts->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

                $this->lvTabs[] = array(
                    'file'   => 'quest',
                    'data'   => $starts->getListviewData(),
                    'params' => [
                        'name' => '$LANG.tab_starts',
                        'id'   => 'starts-quest'
                    ]
                );
            }
        }

        // tab: objective of (quest)
        $conditions = array(
            'OR',
            ['reqItemId1', $this->typeId], ['reqItemId2', $this->typeId], ['reqItemId3', $this->typeId],
            ['reqItemId4', $this->typeId], ['reqItemId5', $this->typeId], ['reqItemId6', $this->typeId]
        );
        $objective = new QuestList($conditions);
        if (!$objective->error)
        {
            $this->extendGlobalData($objective->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

            $this->lvTabs[] = array(
                'file'   => 'quest',
                'data'   => $objective->getListviewData(),
                'params' => [
                    'name' => '$LANG.tab_objectiveof',
                    'id'   => 'objective-of-quest'
                ]
            );
        }

        // tab: provided for (quest)
        $conditions = array(
            'OR', ['sourceItemId', $this->typeId],
            ['reqSourceItemId1', $this->typeId], ['reqSourceItemId2', $this->typeId],
            ['reqSourceItemId3', $this->typeId], ['reqSourceItemId4', $this->typeId]
        );
        $provided = new QuestList($conditions);
        if (!$provided->error)
        {
            $this->extendGlobalData($provided->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

            $this->lvTabs[] = array(
                'file'   => 'quest',
                'data'   => $provided->getListviewData(),
                'params' => [
                    'name' => '$LANG.tab_providedfor',
                    'id'   => 'provided-for-quest'
                ]
            );
        }

        // tab: same model as
        // todo (low): should also work for creatures summoned by item
        if (($model = $this->subject->getField('model')) && $_slot)
        {
            $sameModel = new ItemList(array(['model', $model], ['id', $this->typeId, '!'], ['slot', $_slot]));
            if (!$sameModel->error)
            {
                $this->extendGlobalData($sameModel->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs[] = array(
                    'file'   => 'genericmodel',
                    'data'   => $sameModel->getListviewData(ITEMINFO_MODEL),
                    'params' => [
                        'name'            => '$LANG.tab_samemodelas',
                        'id'              => 'same-model-as',
                        'genericlinktype' => 'item'
                    ]
                );
            }
        }

        // tab: sold by
        if (!empty($this->subject->getExtendedCost()[$this->subject->id]))
        {
            $vendors = $this->subject->getExtendedCost()[$this->subject->id];
            $soldBy  = new CreatureList(array(['id', array_keys($vendors)]));
            if (!$soldBy->error)
            {
                $sbData = $soldBy->getListviewData();
                $this->extendGlobalData($soldBy->getJSGlobals(GLOBALINFO_SELF));

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

                    if ($currency)
                        $this->extendGlobalIds(TYPE_CURRENCY, array_column($currency, 0));

                    if ($tokens)
                        $this->extendGlobalIds(TYPE_ITEM, array_column($tokens, 0));

                    $row['stock'] = $vendors[$k]['stock'];
                    $row['cost']  = [empty($vendors[$k][0]) ? 0 : $vendors[$k][0]];

                    if ($e = $vendors[$k]['event'])
                    {
                        if (count($extraCols) == 3)
                            $extraCols[] = 'Listview.extraCols.condition';

                        $this->extendGlobalIds(TYPE_WORLDEVENT, $e);
                        $row['condition'][0][$this->typeId][] = [[CND_ACTIVE_EVENT, $e]];
                    }

                    if ($currency || $tokens)               // fill idx:3 if required
                        $row['cost'][] = $currency;

                    if ($tokens)
                        $row['cost'][] = $tokens;

                    if ($x = $this->subject->getField('buyPrice'))
                        $row['buyprice'] = $x;

                    if ($x = $this->subject->getField('sellPrice'))
                        $row['sellprice'] = $x;

                    if ($x = $this->subject->getField('buyCount'))
                        $row['stack'] = $x;
                }


                $this->lvTabs[] = array(
                    'file'   => 'creature',
                    'data'   => $sbData,
                    'params' => [
                        'name'       => '$LANG.tab_soldby',
                        'id'         => 'sold-by-npc',
                        'extraCols'  => '$['.implode(', ', $extraCols).']',
                        'hiddenCols' => "$['level', 'type']"
                    ]
                );
            }
        }

        // tab: currency for
        // some minor trickery: get arenaPoints(43307) and honorPoints(43308) directly
        if ($this->typeId == 43307)
        {
            $n = '?items&filter=cr=145;crs=1;crv=0';
            $w = 'reqArenaPoints > 0';
        }
        else if ($this->typeId == 43308)
        {
            $n = '?items&filter=cr=144;crs=1;crv=0';
            $w = 'reqHonorPoints > 0';
        }
        else
        {
            $n = in_array($this->typeId, [42, 61, 81, 241, 121, 122, 123, 125, 126, 161, 201, 101, 102, 221, 301, 341]) ? '?items&filter=cr=158;crs='.$this->typeId.';crv=0' : null;
            $w = 'reqItemId1 = '.$this->typeId.' OR reqItemId2 = '.$this->typeId.' OR reqItemId3 = '.$this->typeId.' OR reqItemId4 = '.$this->typeId.' OR reqItemId5 = '.$this->typeId;
        }

        $xCosts   = DB::Aowow()->selectCol('SELECT id FROM ?_itemextendedcost WHERE '.$w);
        $boughtBy = $xCosts ? DB::World()->selectCol('SELECT item FROM npc_vendor WHERE extendedCost IN (?a) UNION SELECT item FROM game_event_npc_vendor WHERE extendedCost IN (?a)', $xCosts, $xCosts) : null;
        if ($boughtBy)
        {
            $boughtBy = new ItemList(array(['id', $boughtBy]));
            if (!$boughtBy->error)
            {
                if ($boughtBy->getMatches() <= CFG_SQL_LIMIT_DEFAULT)
                    $n = null;

                $iCur   = new CurrencyList(array(['itemId', $this->typeId]));
                $filter = $iCur->error ? [TYPE_ITEM => $this->typeId] : [TYPE_CURRENCY => $iCur->id];

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $boughtBy->getListviewData(ITEMINFO_VENDOR, $filter),
                    'params' => [
                        'name'      => '$LANG.tab_currencyfor',
                        'id'        => 'currency-for',
                        'extraCols' => "$[Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack'), Listview.extraCols.cost]",
                        'note'      => $n ? sprintf(Util::$filterResultString, $n) : null
                    ]
                );
                $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_ANY));
            }
        }

        // tab: teaches
        $ids = $indirect = [];
        for ($i = 1; $i < 6; $i++)
        {
            if ($this->subject->getField('spellTrigger'.$i) == 6)
                $ids[] = $this->subject->getField('spellId'.$i);
            else if ($this->subject->getField('spellTrigger'.$i) == 0 && $this->subject->getField('spellId'.$i) > 0)
                $indirect[] = $this->subject->getField('spellId'.$i);
        }

        // taught indirectly
        if ($indirect)
        {
            $indirectSpells = new SpellList(array(['id', $indirect]));
            foreach ($indirectSpells->iterate() as $__)
                if ($_ = $indirectSpells->canTeachSpell())
                    foreach ($_ as $idx)
                        $ids[] = $indirectSpells->getField('effect'.$idx.'TriggerSpell');

            $ids = array_merge($ids, Util::getTaughtSpells($indirect));
        }

        if ($ids)
        {
            $taughtSpells = new SpellList(array(['id', $ids]));
            if (!$taughtSpells->error)
            {
                $this->extendGlobalData($taughtSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $visCols = ['level', 'schools'];
                if ($taughtSpells->hasSetFields(['reagent1']))
                    $visCols[] = 'reagents';

                $this->lvTabs[] = array(
                    'file'   => 'spell',
                    'data'   => $taughtSpells->getListviewData(),
                    'params' => [
                        'name'        => '$LANG.tab_teaches',
                        'id'          => 'teaches',
                        'visibleCols' => '$'.Util::toJSON($visCols)
                    ]
                );
            }
        }

        // tab: Shared cooldown
        $cdCats = [];
        for ($i = 1; $i < 6; $i++)
            if ($this->subject->getField('spellId'.$i) > 0 && $this->subject->getField('spellCategory'.$i) > 0)
                $cdCats[] = $this->subject->getField('spellCategory'.$i);

        if ($cdCats)
        {
            $conditions = array(
                'OR',
                ['spellCategory1', $cdCats],
                ['spellCategory2', $cdCats],
                ['spellCategory3', $cdCats],
                ['spellCategory4', $cdCats],
                ['spellCategory5', $cdCats]
            );
            $cdItems = new ItemList($conditions);
            if (!$cdItems->error)
            {
                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $cdItems->getListviewData(),
                    'params' => [
                        'name' => '$LANG.tab_sharedcooldown',
                        'id'   => 'shared-cooldown'
                    ]
                );

                $this->extendGlobalData($cdItems->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // // todo - tab: taught by
        // use var $createdBy to find source of this spell
        // id: 'taught-by-X',
        // name: LANG.tab_taughtby
    }

    protected function generateTooltip($asError = false)
    {
        $itemString = $this->typeId;
        foreach ($this->enhancedTT as $k => $val)
            $itemString .= $k.(is_array($val) ? implode(',', $val) : $val);

        if ($asError)
            return '$WowheadPower.registerItem(\''.$itemString.'\', '.User::$localeId.', {})';

        $x  = '$WowheadPower.registerItem(\''.$itemString.'\', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($this->subject->getField('name', true))."',\n";
        $x .= "\tquality: ".$this->subject->getField('quality').",\n";
        $x .= "\ticon: '".urlencode($this->subject->getField('iconString'))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($this->subject->renderTooltip(false, 0, $this->enhancedTT))."'\n";
        $x .= "});";

        return $x;
    }

    protected function generateXML($asError = false)
    {
        $root = new SimpleXML('<aowow />');

        if ($asError)
            $root->addChild('error', 'Item not found!');
        else
        {
            // item root
            $xml = $root->addChild('item');
            $xml->addAttribute('id', $this->subject->id);

            // name
            $xml->addChild('name')->addCData($this->subject->getField('name', true));
            // itemlevel
            $xml->addChild('level', $this->subject->getField('itemLevel'));
            // quality
            $xml->addChild('quality', Lang::item('quality', $this->subject->getField('quality')))->addAttribute('id', $this->subject->getField('quality'));
            // class
            $x = Lang::item('cat', $this->subject->getField('class'));
            $xml->addChild('class')->addCData(is_array($x) ? $x[0] : $x)->addAttribute('id', $this->subject->getField('class'));
            // subclass
            $x = $this->subject->getField('class') == 2 ? Lang::spell('weaponSubClass') : Lang::item('cat', $this->subject->getField('class'), 1);
            $xml->addChild('subclass')->addCData(is_array($x) ? (is_array($x[$this->subject->getField('subClass')]) ? $x[$this->subject->getField('subClass')][0] : $x[$this->subject->getField('subClass')]) : null)->addAttribute('id', $this->subject->getField('subClass'));
            // icon + displayId
            $xml->addChild('icon', $this->subject->getField('iconString'))->addAttribute('displayId', $this->subject->getField('displayId'));
            // inventorySlot
            $xml->addChild('inventorySlot', Lang::item('inventoryType', $this->subject->getField('slot')))->addAttribute('id', $this->subject->getField('slot'));
            // tooltip
            $xml->addChild('htmlTooltip')->addCData($this->subject->renderTooltip());

            $this->subject->extendJsonStats();

            // json
            $fields = ["classs", "displayid", "dps", "id", "level", "name", "reqlevel", "slot", "slotbak", "source", "sourcemore", "speed", "subclass"];
            $json   = '';
            foreach ($fields as $f)
            {
                if (isset($this->subject->json[$this->subject->id][$f]))
                {
                    $_ = $this->subject->json[$this->subject->id][$f];
                    if ($f == 'name')
                        $_ = (7 - $this->subject->getField('quality')).$_;

                    $json .= ',"'.$f.'":'.$_;
                }
            }
            $xml->addChild('json')->addCData(substr($json, 1));

            // jsonEquip missing: avgbuyout, cooldown, source, sourcemore
            $json = '';
            if ($_ = $this->subject->getField('sellPrice'))          // sellprice
                $json .= ',"sellprice":'.$_;

            if ($_ = $this->subject->getField('requiredLevel'))      // reqlevel
                $json .= ',"reqlevel":'.$_;

            if ($_ = $this->subject->getField('requiredSkill'))      // reqskill
                $json .= ',"reqskill":'.$_;

            if ($_ = $this->subject->getField('requiredSkillRank'))  // reqskillrank
                $json .= ',"reqskillrank":'.$_;

            foreach ($this->subject->itemMods[$this->subject->id] as $mod => $qty)
                $json .= ',"'.$mod.'":'.$qty;

            foreach ($_ = $this->subject->json[$this->subject->id] as $name => $qty)
                if (in_array($name, Util::$itemFilter))
                    $json .= ',"'.$name.'":'.$qty;

            $xml->addChild('jsonEquip')->addCData(substr($json, 1));

            // jsonUse
            if ($onUse = $this->subject->getOnUseStats())
            {
                $j = '';
                foreach ($onUse as $idx => $qty)
                    $j .= ',"'.Util::$itemMods[$idx].'":'.$qty;

                $xml->addChild('jsonUse')->addCData(substr($j, 1));
            }

            // reagents
            $cnd = array(
                'OR',
                ['AND', ['effect1CreateItemId', $this->subject->id], ['OR', ['effect1Id', SpellList::$effects['itemCreate']], ['effect1AuraId', SpellList::$auras['itemCreate']]]],
                ['AND', ['effect2CreateItemId', $this->subject->id], ['OR', ['effect2Id', SpellList::$effects['itemCreate']], ['effect2AuraId', SpellList::$auras['itemCreate']]]],
                ['AND', ['effect3CreateItemId', $this->subject->id], ['OR', ['effect3Id', SpellList::$effects['itemCreate']], ['effect3AuraId', SpellList::$auras['itemCreate']]]],
            );

            $spellSource = new SpellList($cnd);
            if (!$spellSource->error)
            {
                $cbNode = $xml->addChild('createdBy');

                foreach ($spellSource->iterate() as $sId => $__)
                {
                    foreach ($spellSource->canCreateItem() as $idx)
                    {
                        if ($spellSource->getField('effect'.$idx.'CreateItemId') != $this->subject->id)
                            continue;

                        $splNode = $cbNode->addChild('spell');
                        $splNode->addAttribute('id', $sId);
                        $splNode->addAttribute('name', $spellSource->getField('name', true));
                        $splNode->addAttribute('icon', $this->subject->getField('iconString'));
                        $splNode->addAttribute('minCount', $spellSource->getField('effect'.$idx.'BasePoints') + 1);
                        $splNode->addAttribute('maxCount', $spellSource->getField('effect'.$idx.'BasePoints') + $spellSource->getField('effect'.$idx.'DieSides'));

                        foreach ($spellSource->getReagentsForCurrent() as $rId => $qty)
                        {
                            if ($reagent = $spellSource->relItems->getEntry($rId))
                            {
                                $rgtNode = $splNode->addChild('reagent');
                                $rgtNode->addAttribute('id', $rId);
                                $rgtNode->addAttribute('name', Util::localizedString($reagent, 'name'));
                                $rgtNode->addAttribute('quality', $reagent['quality']);
                                $rgtNode->addAttribute('icon', $reagent['iconString']);
                                $rgtNode->addAttribute('count', $qty[1]);
                            }
                        }

                        break;
                    }
                }
            }

            // link
            $xml->addChild('link', HOST_URL.'?item='.$this->subject->id);
        }

        return $root->asXML();
    }

    public function display($override = '')
    {
        if ($this->mode == CACHE_TYPE_TOOLTIP)
        {
            if (!$this->loadCache($tt))
            {
                $tt = $this->generateTooltip();
                $this->saveCache($tt);
            }

            header('Content-type: application/x-javascript; charset=utf-8');
            die($tt);
        }
        else if ($this->mode == CACHE_TYPE_XML)
        {
            if (!$this->loadCache($xml))
            {
                $xml = $this->generateXML();
                $this->saveCache($xml);
            }

            header('Content-type: text/xml; charset=utf-8');
            die($xml);
        }
        else
            return parent::display($override);
    }

    public function notFound()
    {
        if ($this->mode == CACHE_TYPE_TOOLTIP)
        {
            header('Content-type: application/x-javascript; charset=utf-8');
            echo $this->generateTooltip(true);
            exit();
        }
        else if ($this->mode == CACHE_TYPE_XML)
        {
            header('Content-type: text/xml; charset=utf-8');
            echo $this->generateXML(true);
            exit();
        }
        else
            return parent::notFound(Lang::game('item'), Lang::item('notFound'));
    }
}

?>
