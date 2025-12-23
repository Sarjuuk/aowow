<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType   = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template    = 'item';
    protected  string $pageName    = 'item';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 0];

    protected  array  $scripts     = array(
        [SC_JS_FILE, 'js/profile.js'],
        [SC_JS_FILE, 'js/filters.js']
    );

    public  int    $type        = Type::ITEM;
    public  int    $typeId      = 0;
    public  bool   $unavailable = false;
    public ?Book   $book        = null;
    public ?array  $subItems    = null;
    public  array  $tooltip     = [];

    private ItemList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new ItemList(array(['i.id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('item'), Lang::item('notFound'));

        $jsg = $this->subject->getJSGlobals(GLOBALINFO_EXTRA | GLOBALINFO_SELF, $extra);
        $this->extendGlobalData($jsg, $extra);

        $this->h1 = Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_HTML);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_flags     = $this->subject->getField('flags');
        $_slot      = $this->subject->getField('slot');
        $_class     = $this->subject->getField('class');
        $_subClass  = $this->subject->getField('subClass');
        $_bagFamily = $this->subject->getField('bagFamily');
        $_displayId = $this->subject->getField('displayId');
        $_ilvl      = $this->subject->getField('itemLevel');


        /*************/
        /* Menu Path */
        /*************/

        if ($path = $this->followBreadcrumbPath())
            array_push($this->breadcrumb, ...$path);


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_RAW), Util::ucFirst(Lang::game('item')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // itemlevel
        if ($_ilvl && in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION, ITEM_CLASS_GEM]))
            $infobox[] = Lang::game('level').Lang::main('colon').$_ilvl;

        // account-wide
        if ($_flags & ITEM_FLAG_ACCOUNTBOUND)
            $infobox[] = Lang::item('accountWide');

        // side
        if ($si = $this->subject->json[$this->typeId]['side'])
            $infobox[] = Lang::main('side') . match ($si)
            {
                SIDE_ALLIANCE => '[span class=icon-alliance]'.Lang::game('si', SIDE_ALLIANCE).'[/span]',
                SIDE_HORDE    => '[span class=icon-horde]'.Lang::game('si', SIDE_HORDE).'[/span]',
                SIDE_BOTH     => Lang::game('si', SIDE_BOTH)
            };

        // id
        $infobox[] = Lang::item('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // consumable / not consumable
        if (!$_slot)
        {
            $hasUse = false;
            for ($i = 1; $i < 6; $i++)
            {
                if ($this->subject->getField('spellId'.$i) <= 0 || in_array($this->subject->getField('spellTrigger'.$i), [SPELL_TRIGGER_EQUIP, SPELL_TRIGGER_HIT]))
                    continue;

                $hasUse = true;

                if ($this->subject->getField('spellCharges'.$i) >= 0)
                    continue;

                $tt = '[tooltip=tooltip_consumedonuse]'.Lang::item('consumable').'[/tooltip]';
                break;
            }

            if ($hasUse)
                $infobox[] = $tt ?? '[tooltip=tooltip_notconsumedonuse]'.Lang::item('nonConsumable').'[/tooltip]';
        }

        // related holiday
        if ($eId = $this->subject->getField('eventId'))
        {
            $this->extendGlobalIds(Type::WORLDEVENT, $eId);
            $infobox[] = Lang::game('eventShort', ['[event='.$eId.']']);
        }

        // tool
        if ($tId = $this->subject->getField('totemCategory'))
            if ($tName = DB::Aowow()->selectRow('SELECT * FROM ?_totemcategory WHERE `id` = ?d', $tId))
                $infobox[] = Lang::item('tool').'[url=?items&filter=cr=91;crs='.$tId.';crv=0]'.Util::localizedString($tName, 'name').'[/url]';

        // extendedCost
        if (!empty($this->subject->getExtendedCost([], $_reqRating)[$this->typeId]))
        {
            $vendors  = $this->subject->getExtendedCost()[$this->typeId];
            $stack    = $this->subject->getField('buyCount');
            $divisor  = $stack;
            $each     = '';
            $handled  = [];
            $costList = [];
            foreach ($vendors as $npcId => $entries)
            {
                foreach ($entries as $data)
                {
                    $tokens   = [];
                    $currency = [];

                    if (!is_array($data))
                        continue;

                    foreach ($data as $c => $qty)
                    {
                        if (is_string($c))
                        {
                            unset($data[$c]);               // unset miscData to prevent having two vendors /w the same cost being cached, because of different stock or rating-requirements
                            continue;
                        }

                        if ($c < 0)                         // currency items (and honor or arena)
                        {
                            if (is_float($qty / $stack))
                                $divisor = 1;

                            $currency[] = [-$c, $qty];
                            $this->extendGlobalIds(Type::CURRENCY, -$c);
                        }
                        else if ($c > 0)                    // plain items (item1,count1,item2,count2,...)
                        {
                            if (is_float($qty / $stack))
                                $divisor = 1;

                            $tokens[] = [$c, $qty];
                            $this->extendGlobalIds(Type::ITEM, $c);
                        }
                    }

                    // display every cost-combination only once
                    $hash = md5(serialize($data));
                    if (in_array($hash, $handled))
                        continue;

                    $handled[] = $hash;

                    if (isset($data[0]))
                    {
                        if (is_float($data[0] / $stack))
                            $divisor = 1;

                        $cost = '[money='.($data[0] / $divisor);
                    }
                    else
                        $cost = '[money';

                    $stringify = fn(&$x) => $x = $x[0] . ',' . ($x[1] / $divisor);

                    if ($tokens)
                    {
                        array_walk($tokens, $stringify);
                        $cost .= ' items='.implode(',', $tokens);
                    }

                    if ($currency)
                    {
                        array_walk($currency, $stringify);
                        $cost .= ' currency='.implode(',', $currency);
                    }

                    $cost .= ']';

                    $costList[] = $cost;
                }
            }

            if ($stack > 1 && $divisor > 1)
                $each = '[color=q0] ('.Lang::item('each').')[/color]';
            else if ($stack > 1)
                $each = '[color=q0] ('.$stack.')[/color]';

            if (count($costList) == 1)
                $infobox[] = Lang::item('cost').Lang::main('colon').$costList[0].$each;
            else if (count($costList) > 1)
                $infobox[] = Lang::item('cost').$each.Lang::main('colon').'[ul][li]'.implode('[/li][li]', $costList).'[/li][/ul]';

            if ($_reqRating && $_reqRating[0])
            {
                $text = str_replace('<br />', ' ', Lang::item('reqRating', $_reqRating[1], [$_reqRating[0]]));
                $infobox[] = Lang::breakTextClean($text, 30, Lang::FMT_MARKUP);
            }
        }

        // repair cost
        if ($_ = $this->subject->getField('repairPrice'))
            $infobox[] = Lang::item('repairCost').'[money='.$_.']';

        // avg auction buyout
        if (in_array($this->subject->getField('bonding'), [0, 2, 3]))
            if ($_ = Profiler::getBuyoutForItem($this->typeId))
                $infobox[] = '[tooltip=tooltip_buyoutprice]'.Lang::item('buyout.').'[/tooltip]'.Lang::main('colon').'[money='.$_.']'.$each;

        // avg money contained
        if ($_flags & ITEM_FLAG_OPENABLE)
            if ($_ = intVal(($this->subject->getField('minMoneyLoot') + $this->subject->getField('maxMoneyLoot')) / 2))
                $infobox[] = Lang::item('worth').'[tooltip=tooltip_avgmoneycontained][money='.$_.'][/tooltip]';

        // if it goes into a slot it may be disenchanted
        if ($_slot && $_class != ITEM_CLASS_CONTAINER)
        {
            if ($this->subject->getField('disenchantId'))
            {
                $_ = $this->subject->getField('requiredDisenchantSkill');
                if ($_ < 1)                                 // these are some items, that never went live .. extremely rough emulation here
                    $_ = intVal($_ilvl / 7.5) * 25;

                $infobox[] = Lang::item('disenchantable').'&nbsp;([tooltip=tooltip_reqenchanting]'.$_.'[/tooltip])';
            }
            else
                $infobox[] = Lang::item('cantDisenchant');
        }

        if (($_flags & ITEM_FLAG_MILLABLE) && $this->subject->getField('requiredSkill') == SKILL_INSCRIPTION)
        {
            $infobox[] = Lang::item('millable').'&nbsp;([tooltip=tooltip_reqinscription]'.$this->subject->getField('requiredSkillRank').'[/tooltip])';
            $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_INSCRIPTION, $this->subject->getField('requiredSkillRank')));
        }

        if (($_flags & ITEM_FLAG_PROSPECTABLE) && $this->subject->getField('requiredSkill') == SKILL_JEWELCRAFTING)
        {
            $infobox[] = Lang::item('prospectable').'&nbsp;([tooltip=tooltip_reqjewelcrafting]'.$this->subject->getField('requiredSkillRank').'[/tooltip])';
            $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_JEWELCRAFTING, $this->subject->getField('requiredSkillRank')));
        }

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

        // completion row added by InfoboxMarkup

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        $hasCompletion = !($this->subject->getField('cuFlags') & CUSTOM_EXCLUDE_FOR_LISTVIEW) && ($_class == ITEM_CLASS_RECIPE || ($_class == ITEM_CLASS_MISC && in_array($_subClass, [2, 5, -7])));
        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', $hasCompletion);


        /****************/
        /* Main Content */
        /****************/

        if ($canBeWeighted = in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR, ITEM_CLASS_GEM]))
            $this->addDataLoader('weight-presets');

        // pageText
        if ($this->book = Game::getBook($this->subject->getField('pageTextId')))
            $this->addScript(
                [SC_JS_FILE,  'js/Book.js'],
                [SC_CSS_FILE, 'css/Book.css']
            );

        $this->tooltip    = [$this->subject->getField('iconString'), $this->subject->getField('stackable'), false];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_VIEW3D  => $this->subject->isDisplayable() ? ['displayId' => $_displayId, 'slot' => $_slot, 'type' => Type::ITEM, 'typeId' => $this->typeId] : false,
            BUTTON_COMPARE => $canBeWeighted,
            BUTTON_EQUIP   => in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]),
            BUTTON_UPGRADE => $canBeWeighted ? ['class' => $_class, 'slot' => $_slot] : false,
            BUTTON_LINKS   => array(
                'linkColor' => 'ff'.Game::$rarityColorStings[$this->subject->getField('quality')],
                'linkId'    => 'item:'.$this->typeId.':0:0:0:0:0:0:0:0',
                'linkName'  => Lang::unescapeUISequences($this->subject->getField('name', true), Lang::FMT_RAW),
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        // availablility
        $this->unavailable = !!($this->subject->getField('cuFlags') & CUSTOM_UNAVAILABLE);

        // subItems
        $this->subject->initSubItems();
        if (!empty($this->subject->subItems[$this->typeId]))
        {
            uaSort($this->subject->subItems[$this->typeId], fn($a, $b) => $a['name'] <=> $b['name']);
            $this->subItems = array(
                'data'    => array_values($this->subject->subItems[$this->typeId]),
                'randIds' => array_keys($this->subject->subItems[$this->typeId]),
                'quality' => $this->subject->getField('quality')
            );

            // merge identical stats and names for normal users (e.g. spellPower of a specific school became general spellPower with 3.0)
            // see: https://web.archive.org/web/20101118041612/wowhead.com/item=11946
            // stats should also be merged if only the keys are the same, resulting in "+(8 - 9) Spirit" etc.

            if (!User::isInGroup(U_GROUP_EMPLOYEE))
            {
                for ($i = 1; $i < count($this->subItems['data']); $i++)
                {
                    $prev = &$this->subItems['data'][$i - 1];
                    $cur  = &$this->subItems['data'][$i];
                    if ($prev['jsonequip'] == $cur['jsonequip'] && $prev['name'] == $cur['name'])
                    {
                        $prev['chance'] += $cur['chance'];
                        array_splice($this->subItems['data'], $i, 1);
                        array_splice($this->subItems['randIds'], $i, 1);
                        $i = 1;
                    }
                }
            }
        }

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = ?d, `alliance_id`, -`horde_id`) FROM player_factionchange_items WHERE `alliance_id` = ?d OR `horde_id` = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altItem = new ItemList(array(['id', abs($pendant)]));
            if (!$altItem->error)
            {
                $this->transfer = Lang::item('_transfer', [
                    $altItem->id,
                    $altItem->getField('quality'),
                    $altItem->getField('iconString'),
                    $altItem->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ]);
            }
        }


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: createdBy (perfect item specific)
        if ($perfItem = DB::World()->select('SELECT *, `spellId` AS ARRAY_KEY FROM skill_perfect_item_template WHERE `perfectItemType` = ?d', $this->typeId))
        {
            $perfSpells = new SpellList(array(['id', array_column($perfItem, 'spellId')]));
            if (!$perfSpells->error)
            {
                $lvData = $perfSpells->getListviewData();
                $this->extendGlobalData($perfSpells->getJSGlobals(GLOBALINFO_RELATED));

                foreach ($lvData as $sId => &$data)
                {
                    $data['percent'] = $perfItem[$sId]['perfectCreateChance'];
                    if (Conditions::extendListviewRow($data, Conditions::SRC_NONE, $this->typeId, [Conditions::SPELL, $perfItem[$sId]['requiredSpecialization']]))
                        $this->extendGlobalIDs(Type::SPELL, $perfItem[$sId]['requiredSpecialization']);
                }

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'      => $lvData,
                    'name'      => '$LANG.tab_createdby',
                    'id'        => 'created-by',            // should by exclusive with created-by from spell_loot
                    'extraCols' => ['$Listview.extraCols.percent', '$Listview.extraCols.condition']
                ), SpellList::$brickFile));
            }
        }

        // tabs: this item is contained in..
        $lootTabs  = new LootByItem($this->typeId);
        $createdBy = [];
        if ($lootTabs->getByItem())
        {
            $this->extendGlobalData($lootTabs->jsGlobals);

            foreach ($lootTabs->iterate() as $idx => [$template, $tabData])
            {
                if (!$tabData['data'])
                    continue;

                if ($idx == LootByItem::SPELL_CREATED)
                    $createdBy = array_column($tabData['data'], 'id');

                if ($idx == LootByItem::ITEM_DISENCHANTED)
                    $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=163;crs='.$this->typeId.';crv=0');

                if ($idx == LootByItem::NPC_DROPPED && $this->subject->getSources($s, $sm) && $s[0] == SRC_DROP && isset($sm[0]['dd']))
                    $tabData['note'] = match($sm[0]['dd'])
                    {
                        -1      => '$LANG.lvnote_itemdropsinnormalonly',
                        -2      => '$LANG.lvnote_itemdropsinheroiconly',
                        -3      => '$LANG.lvnote_itemdropsinnormalheroic',
                         1      => '$LANG.lvnote_itemdropsinnormal10only',
                         2      => '$LANG.lvnote_itemdropsinnormal25only',
                         3      => '$LANG.lvnote_itemdropsinheroic10only',
                         4      => '$LANG.lvnote_itemdropsinheroic25only',
                        default => null
                    };

                if ($idx == LootByItem::OBJECT_FISHED && !$this->map)
                {
                    $nodeIds  = array_map(fn($x) => $x['id'], $tabData['data']);
                    $fishedIn = new GameObjectList(array(['id', $nodeIds]));
                    if (!$fishedIn->error)
                    {
                        // show mapper for fishing locations
                        if ($nodeSpawns = $fishedIn->getSpawns(SPAWNINFO_FULL, true, true, true, true))
                        {
                            $this->map = array(
                                ['parent' => 'mapper-generic'], // Mapper
                                $nodeSpawns,                    // mapperData
                                null,                           // ShowOnMap
                                [Lang::item('fishedIn')],       // foundIn
                                Lang::item('fishingLoc')        // title
                            );
                            foreach ($nodeSpawns as $areaId => $_)
                                $this->map[3][$areaId] = ZoneList::getName($areaId);
                        }
                    }
                }

                if ($template == 'npc' || $template == 'object')
                    $this->addDataLoader('zones');

                $this->lvTabs->addListviewTab(new Listview($tabData, $template));
            }
        }

        // tabs: this item contains..
        $sourceFor = array(
            [Loot::ITEM,        $this->typeId,                            '$LANG.tab_contains',      'contains',      ['$Listview.extraCols.percent'], []                          ],
            [Loot::PROSPECTING, $this->typeId,                            '$LANG.tab_prospecting',   'prospecting',   ['$Listview.extraCols.percent'], ['side', 'slot', 'reqlevel']],
            [Loot::MILLING,     $this->typeId,                            '$LANG.tab_milling',       'milling',       ['$Listview.extraCols.percent'], ['side', 'slot', 'reqlevel']],
            [Loot::DISENCHANT,  $this->subject->getField('disenchantId'), '$LANG.tab_disenchanting', 'disenchanting', ['$Listview.extraCols.percent'], ['side', 'slot', 'reqlevel']]
        );

        foreach ($sourceFor as [$lootTemplate, $lootId, $tabName, $tabId, $extraCols, $hiddenCols])
        {
            $lootTab = new LootByContainer();
            if ($lootTab->getByContainer($lootTemplate, [$lootId]))
            {
                $this->extendGlobalData($lootTab->jsGlobals);
                $extraCols = array_merge($extraCols, $lootTab->extraCols);

                $tabData = array(
                    'data'            => $lootTab->getResult(),
                    'name'            => $tabName,
                    'id'              => $tabId,
                    'computeDataFunc' => '$Listview.funcBox.initLootTable'
                );

                if ($extraCols)
                    $tabData['extraCols']  = array_values(array_unique($extraCols));

                if ($hiddenCols)
                    $tabData['hiddenCols'] = array_unique($hiddenCols);

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
            }
        }

        // append spell loot mimicking item opening
        if ($this->subject->getField('spellTrigger1') === SPELL_TRIGGER_USE && ($s = $this->subject->getField('spellId1')))
        {
            if (($spellLoot = new LootByContainer())->getByContainer(Loot::SPELL, [$s]))
            {
                $this->extendGlobalData($spellLoot->jsGlobals);

                $makeNew = true;
                foreach ($this->lvTabs->iterate() as $k => $tab)
                {
                    if ($tab->getId() != 'contains')
                        continue;

                    $tab->appendData($spellLoot->getResult());
                    $makeNew = false;
                    break;
                }

                if ($makeNew)
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'            => $spellLoot->getResult(),
                        'name'            => '$LANG.tab_contains',
                        'id'              => 'contains',
                        'computeDataFunc' => '$Listview.funcBox.initLootTable',
                        'extraCols'       => array_merge(['$Listview.extraCols.percent'], $spellLoot->extraCols)
                    ), ItemList::$brickFile));
            }
        }

        // tab: container can contain
        if ($this->subject->getField('slots') > 0)
        {
            $contains = new ItemList(array(['bagFamily', $_bagFamily, '&'], ['slots', 1, '<'], Cfg::get('SQL_LIMIT_NONE')));
            if (!$contains->error)
            {
                $this->extendGlobalData($contains->getJSGlobals(GLOBALINFO_SELF));

                $hCols = ['side'];
                if (!$contains->hasSetFields('slot'))
                    $hCols[] = 'slot';

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'       => $contains->getListviewData(),
                    'name'       => '$LANG.tab_cancontain',
                    'id'         => 'can-contain',
                    'hiddenCols' => $hCols
                ), ItemList::$brickFile));
            }
        }

        // tab: can be contained in (except keys)
        else if ($_bagFamily != 0x0100)
        {
            $contains = new ItemList(array(['bagFamily', $_bagFamily, '&'], ['slots', 0, '>'], Cfg::get('SQL_LIMIT_NONE')));
            if (!$contains->error)
            {
                $this->extendGlobalData($contains->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'       => $contains->getListviewData(),
                    'name'       => '$LANG.tab_canbeplacedin',
                    'id'         => 'can-be-placed-in',
                    'hiddenCols' => ['side']
                ), ItemList::$brickFile));
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

            $tabData = array(
                'data'        => $criteriaOf->getListviewData(),
                'name'        => '$LANG.tab_criteriaof',
                'id'          => 'criteria-of',
                'visibleCols' => ['category']
            );

            if (!$criteriaOf->hasSetFields('reward_loc0'))
                $tabData['hiddenCols'] = ['rewards'];

            $this->lvTabs->addListviewTab(new Listview($tabData, AchievementList::$brickFile));
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

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'        => $reagent->getListviewData(),
                'name'        => '$LANG.tab_reagentfor',
                'id'          => 'reagent-for',
                'visibleCols' => ['reagents']
            ), SpellList::$brickFile));
        }

        // tab: unlocks (object or item)
        $lockIds = DB::Aowow()->selectCol(
           'SELECT `id` FROM ?_lock WHERE            (`type1` = ?d AND `properties1` = ?d) OR
            (`type2` = ?d AND `properties2` = ?d) OR (`type3` = ?d AND `properties3` = ?d) OR
            (`type4` = ?d AND `properties4` = ?d) OR (`type5` = ?d AND `properties5` = ?d)',
            LOCK_TYPE_ITEM, $this->typeId, LOCK_TYPE_ITEM, $this->typeId,
            LOCK_TYPE_ITEM, $this->typeId, LOCK_TYPE_ITEM, $this->typeId,
            LOCK_TYPE_ITEM, $this->typeId
        );

        if ($lockIds)
        {
            // objects
            $lockedObj = new GameObjectList(array(['lockId', $lockIds]));
            if (!$lockedObj->error)
            {
                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $lockedObj->getListviewData(),
                    'name' => '$LANG.tab_unlocks',
                    'id'   => 'unlocks-object',
                ), GameObjectList::$brickFile));
            }

            // items (generally unused. It's the spell on the item, that unlocks stuff)
            $lockedItm = new ItemList(array(['lockId', $lockIds]));
            if (!$lockedItm->error)
            {
                $this->extendGlobalData($lockedItm->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $lockedItm->getListviewData(),
                    'name' => '$LANG.tab_unlocks',
                    'id'   => 'unlocks-item'
                ), ItemList::$brickFile));
            }
        }

        // tab: starts (quest)
        if ($qId = $this->subject->getField('startQuest'))
        {
            $starts = new QuestList(array(['id', $qId]));
            if (!$starts->error)
            {
                $this->extendGlobalData($starts->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $starts->getListviewData(),
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts-quest'
                ), QuestList::$brickFile));
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

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $objective->getListviewData(),
                'name' => '$LANG.tab_objectiveof',
                'id'   => 'objective-of-quest'
            ), QuestList::$brickFile));
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

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $provided->getListviewData(),
                'name' => '$LANG.tab_providedfor',
                'id'   => 'provided-for-quest'
            ), QuestList::$brickFile));
        }

        // tab: sold by
        if (!empty($this->subject->getExtendedCost()[$this->typeId]))
        {
            $vendors = $this->subject->getExtendedCost()[$this->typeId];
            $soldBy  = new CreatureList(array(['id', array_keys($vendors)]));
            if (!$soldBy->error)
            {
                // show mapper for vendors
                if ($vendorSpawns = $soldBy->getSpawns(SPAWNINFO_FULL, true, true, true, true))
                {
                    $this->map = array(
                        ['parent' => 'mapper-generic'],     // Mapper
                        $vendorSpawns,                      // mapperData
                        null,                               // ShowOnMap
                        [Lang::item('purchasedIn')],        // foundIn
                        Lang::item('vendorLoc')             // title
                    );
                    foreach ($vendorSpawns as $areaId => $_)
                        $this->map[3][$areaId] = ZoneList::getName($areaId);
                }

                $sbData = $soldBy->getListviewData();
                $this->extendGlobalData($soldBy->getJSGlobals(GLOBALINFO_SELF));

                $extraCols = ['$Listview.extraCols.stock', "\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost'];

                $cnd = new Conditions();
                $cnd->getBySource(Conditions::SRC_NPC_VENDOR, entry: $this->typeId)->prepare();
                foreach ($sbData as $k => &$row)
                {
                    $currency = [];
                    $tokens   = [];

                    // note: can only display one entry per row, so only use first entry of each vendor
                    foreach ($vendors[$k][0] as $id => $qty)
                    {
                        if (is_string($id))
                            continue;

                        if ($id > 0)
                            $tokens[] = [$id, $qty];
                        else if ($id < 0)
                            $currency[] = [-$id, $qty];
                    }

                    $row['stock'] = $vendors[$k][0]['stock'];
                    $row['cost']  = [empty($vendors[$k][0][0]) ? 0 : $vendors[$k][0][0]];

                    if ($e = $vendors[$k][0]['event'])
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $k.':'.$this->typeId, [Conditions::ACTIVE_EVENT, $e]);

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

                if ($cnd->toListviewColumn($sbData, $extraCols, 'id', $this->typeId))
                    $this->extendGlobalData($cnd->getJsGlobals());

                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'       => $sbData,
                    'name'       => '$LANG.tab_soldby',
                    'id'         => 'sold-by-npc',
                    'extraCols'  => $extraCols,
                    'hiddenCols' => ['level', 'type']
                ), CreatureList::$brickFile));
            }
        }

        // tab: currency for
        // some minor trickery: get arenaPoints(43307) and honorPoints(43308) directly
        $n = $w = null;
        if ($this->typeId == 43307)
        {
            $n = '?items&filter=cr=145;crs=1;crv=0';
            $w = '`reqArenaPoints` > 0';
        }
        else if ($this->typeId == 43308)
        {
            $n = '?items&filter=cr=144;crs=1;crv=0';
            $w = '`reqHonorPoints` > 0';
        }
        else
            $w = '`reqItemId1` = '.$this->typeId.' OR `reqItemId2` = '.$this->typeId.' OR `reqItemId3` = '.$this->typeId.' OR `reqItemId4` = '.$this->typeId.' OR `reqItemId5` = '.$this->typeId;

        if (!$n && !is_null(ItemListFilter::getCriteriaIndex(158, $this->typeId)))
            $n = '?items&filter=cr=158;crs='.$this->typeId.';crv=0';

        $xCosts   = DB::Aowow()->selectCol('SELECT `id` FROM ?_itemextendedcost WHERE '.$w);
        $boughtBy = $xCosts ? DB::World()->selectCol('SELECT `item` FROM npc_vendor WHERE `extendedCost` IN (?a) UNION SELECT `item` FROM game_event_npc_vendor WHERE `extendedCost` IN (?a)', $xCosts, $xCosts) : null;
        if ($boughtBy)
        {
            $boughtBy = new ItemList(array(['id', $boughtBy]));
            if (!$boughtBy->error)
            {
                $iCur   = new CurrencyList(array(['itemId', $this->typeId]));
                $filter = $iCur->error ? [Type::ITEM => $this->typeId] : [Type::CURRENCY => $iCur->id];

                $tabData = array(
                    'data'      => $boughtBy->getListviewData(ITEMINFO_VENDOR, $filter),
                    'name'      => '$LANG.tab_currencyfor',
                    'id'        => 'currency-for',
                    'extraCols' => ["\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost']
                );

                if ($n)
                    $tabData['note'] = sprintf(Util::$filterResultString, $n);

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));

                $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tab: teaches
        $ids = $indirect = [];
        for ($i = 1; $i < 6; $i++)
        {
            if ($this->subject->getField('spellTrigger'.$i) == SPELL_TRIGGER_LEARN)
                $ids[] = $this->subject->getField('spellId'.$i);
            else if ($this->subject->getField('spellTrigger'.$i) == SPELL_TRIGGER_USE && $this->subject->getField('spellId'.$i) > 0)
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

            $ids = array_merge($ids, Game::getTaughtSpells($indirect));
        }

        if ($ids)
        {
            $taughtSpells = new SpellList(array(['id', $ids]));
            if (!$taughtSpells->error)
            {
                $this->extendGlobalData($taughtSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $visCols = ['level', 'schools'];
                if ($taughtSpells->hasSetFields('reagent1', 'reagent2', 'reagent3', 'reagent4', 'reagent5', 'reagent6', 'reagent7', 'reagent8'))
                    $visCols[] = 'reagents';

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $taughtSpells->getListviewData(),
                    'name'        => '$LANG.tab_teaches',
                    'id'          => 'teaches',
                    'visibleCols' => $visCols
                ), SpellList::$brickFile));
            }
        }

        // tab: see also
        $conditions = array(
            ['id', $this->typeId, '!'],
            [
                'OR',
                ['name_loc'.Lang::getLocale()->value, $this->subject->getField('name', true)],
                [
                    'AND',
                    ['class',         $_class],
                    ['subClass',      $_subClass],
                    ['slot',          $_slot],
                    ['itemLevel',     $_ilvl - 15, '>'],
                    ['itemLevel',     $_ilvl + 15, '<'],
                    ['quality',       $this->subject->getField('quality')],
                    ['requiredClass', $this->subject->getField('requiredClass') ?: -1]  // todo: fix db data in setup and not on fetch
                ]
            ]
        );

        if ($_ = $this->subject->getField('itemset'))
            $conditions[1][] = ['AND', ['slot', $_slot], ['itemset', $_]];

        $saItems = new ItemList($conditions);
        if (!$saItems->error)
        {
            $this->extendGlobalData($saItems->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $saItems->getListviewData(),
                'name' => '$LANG.tab_seealso',
                'id'   => 'see-also'
            ), ItemList::$brickFile));
        }

        // tab: same model as
        // todo (low): should also work for creatures summoned by item
        if (($model = $this->subject->getField('model')) && $_slot)
        {
            $sameModel = new ItemList(array(['model', $model], ['id', $this->typeId, '!'], ['slot', $_slot]));
            if (!$sameModel->error)
            {
                $this->extendGlobalData($sameModel->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'            => $sameModel->getListviewData(ITEMINFO_MODEL),
                    'name'            => '$LANG.tab_samemodelas',
                    'id'              => 'same-model-as',
                    'genericlinktype' => 'item'
                ), 'genericmodel'));
            }
        }

        // tab: Shared cooldown
        $cdCats    = [];
        $useSpells = [];
        for ($i = 1; $i < 6; $i++)
        {
            // as defined on item
            if ($this->subject->getField('spellId'.$i) > 0 && $this->subject->getField('spellCategory'.$i) > 0)
                $cdCats[] = $this->subject->getField('spellCategory'.$i);

            // as defined in spell
            if ($this->subject->getField('spellId'.$i) > 0)
                $useSpells[] = $this->subject->getField('spellId'.$i);
        }
        if ($useSpells)
            if ($_ = DB::Aowow()->selectCol('SELECT `category` FROM ?_spell WHERE `id` IN (?a) AND `recoveryCategory` > 0', $useSpells))
                $cdCats += $_;

        if ($cdCats)
        {
            $conditions = array(
                ['id', $this->typeId, '!'],
                [
                    'OR',
                    ['spellCategory1', $cdCats],
                    ['spellCategory2', $cdCats],
                    ['spellCategory3', $cdCats],
                    ['spellCategory4', $cdCats],
                    ['spellCategory5', $cdCats],
                ]
            );

            if ($spellsByCat = DB::Aowow()->selectCol('SELECT `id` FROM ?_spell WHERE `category` IN (?a)', $cdCats))
                for ($i = 1; $i < 6; $i++)
                    $conditions[1][] = ['spellId'.$i, $spellsByCat];

            $cdItems = new ItemList($conditions);
            if (!$cdItems->error)
            {
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $cdItems->getListviewData(),
                    'name' => '$LANG.tab_sharedcooldown',
                    'id'   => 'shared-cooldown'
                ), ItemList::$brickFile));

                $this->extendGlobalData($cdItems->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: sounds
        $soundIds = [];
        if ($_class == ITEM_CLASS_WEAPON)
        {
            $scm = (1 << $_subClass);
            if ($this->subject->getField('soundOverrideSubclass') > 0)
                $scm = (1 << $this->subject->getField('soundOverrideSubclass'));

            $soundIds = DB::Aowow()->selectCol('SELECT `soundId` FROM ?_items_sounds WHERE `subClassMask` & ?d', $scm);
        }

        $fields = ['pickUpSoundId', 'dropDownSoundId', 'sheatheSoundId', 'unsheatheSoundId'];
        foreach ($fields as $f)
            if ($x = $this->subject->getField($f))
                $soundIds[] = $x;

        if ($x = $this->subject->getField('spellVisualId'))
        {
            if ($spellSounds = DB::Aowow()->selectRow('SELECT * FROM ?_spell_sounds WHERE `id` = ?d', $x))
            {
                array_shift($spellSounds);                  // bye 'id'-field
                foreach ($spellSounds as $ss)
                    if ($ss)
                        $soundIds[] = $ss;
            }
        }

        if ($soundIds)
        {
            $sounds = new SoundList(array(['id', $soundIds]));
            if (!$sounds->error)
            {
                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview(['data' => $sounds->getListviewData()], SoundList::$brickFile));
            }
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::ITEM, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }


        // // todo - tab: taught by
        // use var $createdBy to find source of this spell
        // id: 'taught-by-X',
        // name: LANG.tab_taughtby

        parent::generate();
    }

    private function followBreadcrumbPath() : array
    {
        $c    = $this->subject->getField('class');
        $sc   = $this->subject->getField('subClass');
        $ssc  = $this->subject->getField('subSubClass');
        $slot = $this->subject->getField('slot');

        if ($c == ITEM_CLASS_REAGENT)
            return [ITEM_CLASS_MISC, 1];                    // misc > reagents

        if ($c == ITEM_CLASS_GENERIC || $c == ITEM_CLASS_PERMANENT)
            return [ITEM_CLASS_MISC, 4];                    // misc > other

        // depths: 1
        $path = [$c];

        if (in_array($c, [ITEM_CLASS_MONEY, ITEM_CLASS_QUEST, ITEM_CLASS_KEY]))
            return $path;

        // depths: 2
        $path[] = $sc;

        // maybe depths: 3
        if ($this->subject->isBodyArmor() && $slot)
            $path[] = $slot;
        else if (($c == ITEM_CLASS_CONSUMABLE && $sc == ITEM_SUBCLASS_ELIXIR) || $c == ITEM_CLASS_GLYPH)
            $path[] = $ssc;

        return $path;
    }
}

?>
