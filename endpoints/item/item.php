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

    private ItemEntry $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new ItemEntry($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('item'), Lang::item('notFound'));

        $this->h1 = UIText::unescapeUISequences($this->subject->name, Lang::FMT_HTML);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_flags     = $this->subject->flags;
        $_slot      = $this->subject->slot;
        $_class     = $this->subject->class;
        $_subClass  = $this->subject->subClass;
        $_bagFamily = $this->subject->bagFamily;
        $_displayId = $this->subject->displayId;
        $_ilvl      = $this->subject->itemLevel;


        /*************/
        /* Menu Path */
        /*************/

        if ($path = $this->followBreadcrumbPath())
            array_push($this->breadcrumb, ...$path);


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, UIText::unescapeUISequences($this->subject->name, Lang::FMT_RAW), Util::ucFirst(Lang::game('item')));


        /***********/
        /* Infobox */
        /***********/


        $hasCompletion = !($this->subject->cuFlags & CUSTOM_EXCLUDE_FOR_LISTVIEW) && ($_class == ITEM_CLASS_RECIPE || ($_class == ITEM_CLASS_MISC && in_array($_subClass, [2, 5, -7])));
        if ($infobox = $this->createInfobox())
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', $hasCompletion);


        // infobox queries for extendedCost, which adds more jsGlobals
        $jsg = $this->subject->getJSGlobal(GLOBALINFO_RELATED | GLOBALINFO_EXTRA, $extra);
        $this->extendGlobalData($jsg, $extra);


        /****************/
        /* Main Content */
        /****************/

        if ($canBeWeighted = in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR, ITEM_CLASS_GEM]))
            $this->addDataLoader('weight-presets');

        // pageText
        if ($this->book = Game::getBook($this->subject->pageTextId))
            $this->addScript(
                [SC_JS_FILE,  'js/Book.js'],
                [SC_CSS_FILE, 'css/Book.css']
            );

        $this->tooltip    = [$this->subject->icon, $this->subject->stackable, false];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_VIEW3D  => $this->subject->isDisplayable() ? ['displayId' => $_displayId, 'slot' => $_slot, 'type' => Type::ITEM, 'typeId' => $this->typeId] : false,
            BUTTON_COMPARE => $canBeWeighted,
            BUTTON_EQUIP   => in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) && User::getCharacters(),
            BUTTON_UPGRADE => $canBeWeighted ? ['class' => $_class, 'slot' => $_slot] : false,
            BUTTON_LINKS   => array(
                'linkColor' => 'ff'.Game::$rarityColorStings[$this->subject->quality],
                'linkId'    => 'item:'.$this->typeId.':0:0:0:0:0:0:0:0',
                'linkName'  => UIText::unescapeUISequences($this->subject->name, Lang::FMT_RAW),
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        // availablility
        $this->unavailable = !!($this->subject->cuFlags & CUSTOM_UNAVAILABLE);

        // subItems
        if ($this->subject->initSubItems())
        {
            uaSort($this->subject->subItems, fn($a, $b) => $a['name'] <=> $b['name']);
            $this->subItems = array(
                'data'    => array_values($this->subject->subItems),
                'randIds' => array_keys($this->subject->subItems),
                'quality' => $this->subject->quality
            );

            // merge identical stats and names for normal users (e.g. spellPower of a specific school became general spellPower with 3.0)
            // see: https://web.archive.org/web/20101118041612/wowhead.com/item=11946
            // stats should also be merged if only the keys are the same, resulting in "+(8 - 9) Spirit" etc.
            // but since we are linking to enchantments, which WH does not, we wont do the merge

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
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = %i, `alliance_id`, -`horde_id`) FROM player_factionchange_items WHERE `alliance_id` = %i OR `horde_id` = %i', $this->typeId, $this->typeId, $this->typeId))
        {
            $altItem = new ItemEntry(abs($pendant));
            if (!$altItem->error)
            {
                $this->transfer = Lang::item('_transfer', [
                    $altItem->id,
                    $altItem->quality,
                    $altItem->icon,
                    $altItem->name,
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ]);
            }
        }


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // is contained in..
       [$tabContainedInItem,   $tabDisenchantedFrom,     $tabProspectedFrom,     $tabMilledFrom,
        $tabDroppedBy,         $tabPickpocketedFrom,     $tabSkinnedFrom,        $tabMinedFromNpc,
        $tabSalvagedFrom,      $tabGatheredFromNpc,      $tabRewardFromQuest,    $tabFishedInZone,
        $tabContainedInObject, $tabMinedFromObject,      $tabGatheredFromObject, $tabFishedInObject,
        $tabCreatedBy,         $tabRewardFromAchievement] = $this->tabContainedIn();

        // unlocks
       [$tabUnlocksObject, $tabUnlocksItem] = $this->tabUnlocks();

        // call order determines order of tabs on the page
        if ($tabDroppedBy)                                  // <start> > dropped-by > created-by-spell
            $this->lvTabs->addListviewTab($tabDroppedBy);

        if ($tabCreatedBy)                                  // dropped-by < created-by-spell > taught-by-X
            $this->lvTabs->addListviewTab($tabCreatedBy);
        else if ($tab = $this->tabCreatedBy())              // custom - perfect item specific
            $this->lvTabs->addListviewTab($tab);

        foreach ($this->tabTaughtBy() as $tab)              // created-by-spell < taught-by-X > reward-from-quest
            $this->lvTabs->addListviewTab($tab);            // [item, quest, npc]

        if ($tabRewardFromQuest)                            // taught-by-X < reward-from-quest > criteria-of
            $this->lvTabs->addListviewTab($tabRewardFromQuest);

        if ($tabRewardFromAchievement)                      // <start> < reward-from-achievement > teaches
            $this->lvTabs->addListviewTab($tabRewardFromAchievement);

        if ($tab = $this->tabSoldByNpc())                   // reward-from-quest < sold-by > contained-in-item
            $this->lvTabs->addListviewTab($tab);

        if ($tabContainedInItem)                            // taught-by-X < contained-in-item > contained-in-object
            $this->lvTabs->addListviewTab($tabContainedInItem);

        if ($tabContainedInObject)                          // contained-in-item < contained-in-object > fished-in-X
            $this->lvTabs->addListviewTab($tabContainedInObject);

        if ($tabFishedInZone)                               // contained-in-object < fished-in-X > pickpocketed-from
            $this->lvTabs->addListviewTab($tabFishedInZone);

        if ($tabFishedInObject)
            $this->lvTabs->addListviewTab($tabFishedInObject);

        if ($tabPickpocketedFrom)                           // fished-in-X < pickpocketed-from > reagent-for
            $this->lvTabs->addListviewTab($tabPickpocketedFrom);

        if ($tabSkinnedFrom)
            $this->lvTabs->addListviewTab($tabSkinnedFrom);

        if ($tabDisenchantedFrom)
            $this->lvTabs->addListviewTab($tabDisenchantedFrom);

        if ($tabProspectedFrom)
            $this->lvTabs->addListviewTab($tabProspectedFrom);

        if ($tabMilledFrom)
            $this->lvTabs->addListviewTab($tabMilledFrom);

        if ($tabMinedFromNpc)
            $this->lvTabs->addListviewTab($tabMinedFromNpc);

        if ($tabSalvagedFrom)
            $this->lvTabs->addListviewTab($tabSalvagedFrom);

        if ($tabGatheredFromNpc)
            $this->lvTabs->addListviewTab($tabGatheredFromNpc);

        if ($tabMinedFromObject)
            $this->lvTabs->addListviewTab($tabMinedFromObject);

        if ($tabGatheredFromObject)
            $this->lvTabs->addListviewTab($tabGatheredFromObject);

        if ($tab = $this->tabContains(Loot::ITEM, $this->typeId, '$LANG.tab_contains', 'contains', ['$Listview.extraCols.percent']))
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabContains(Loot::PROSPECTING, $this->typeId, '$LANG.tab_prospecting', 'prospecting', ['$Listview.extraCols.percent'], ['side', 'slot', 'reqlevel']))
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabContains(Loot::MILLING, $this->typeId, '$LANG.tab_milling', 'milling', ['$Listview.extraCols.percent'], ['side', 'slot', 'reqlevel']))
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabContains(Loot::DISENCHANT, $this->subject->disenchantId, '$LANG.tab_disenchanting', 'disenchanting', ['$Listview.extraCols.percent'], ['side', 'slot', 'reqlevel']))
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabContainsSpell())               // custom tab: contains - but we append spell loot mimicking item opening
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabCanContain())                  // container can contain
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabReagentFor())                  // fished-in < reagent-for > objective-of
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabToolFor())                     // reagent-for < tool-for > can-be-placed-in
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabObjectiveOfQuest())            // reagent-for < objective-of > criteria-of
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabCanBePlacedIn())               // reagent-for < can-be-placed-in > see-also
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabCriteriaOf())                  // objective-of < criteria-of > shared-cooldown
            $this->lvTabs->addListviewTab($tab);

        if ($tabUnlocksObject)
            $this->lvTabs->addListviewTab($tabUnlocksObject);

        if ($tabUnlocksItem)
            $this->lvTabs->addListviewTab($tabUnlocksItem);

        if ($tab = $this->tabStartsQuest())                 //
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabProvidedForQuest())            //
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabCurrencyFor())                 //
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabTeaches())                     //
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabSameModelAs())                 // todo (low): should also work for creatures summoned by item
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabSharedCooldown())              // contained-in-object < shared-cooldown > see-also
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabSounds())                      //
            $this->lvTabs->addListviewTab($tab);

        // tab: transmog-with

        // tab: outfits

        if ($tab = $this->tabSeeAlso())                     // shared-cooldown < see-also > <end>
            $this->lvTabs->addListviewTab($tab);

        if ($tab = $this->tabConditionFor())                // custom
            $this->lvTabs->addDataTab(...$tab);


        parent::generate();
    }

    private function followBreadcrumbPath() : array
    {
        $c    = $this->subject->class;
        $sc   = $this->subject->subClass;
        $ssc  = $this->subject->subSubClass;
        $slot = $this->subject->slot;

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

    private function createInfobox() : array
    {
        $_flags     = $this->subject->flags;
        $_slot      = $this->subject->slot;
        $_class     = $this->subject->class;
        $_subClass  = $this->subject->subClass;
        $_bagFamily = $this->subject->bagFamily;
        $_displayId = $this->subject->displayId;
        $_ilvl      = $this->subject->itemLevel;

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        // itemlevel
        if ($_ilvl && in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION, ITEM_CLASS_GEM]))
            $infobox[] = Lang::game('level').Lang::main('colon').$_ilvl;

        // account-wide
        if ($_flags & ITEM_FLAG_ACCOUNTBOUND)
            $infobox[] = Lang::item('accountWide');

        // side
        if ($si = $this->subject->side)
            $infobox[] = Lang::main('side') . match ($si)
            {
                SIDE_ALLIANCE => '[span class=icon-alliance]'.Lang::game('si', SIDE_ALLIANCE).'[/span]',
                SIDE_HORDE    => '[span class=icon-horde]'.Lang::game('si', SIDE_HORDE).'[/span]',
                SIDE_BOTH     => Lang::game('si', SIDE_BOTH)
            };

        // id
        $infobox[] = Lang::item('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->iconId)
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // consumable / not consumable
        if (!$_slot)
        {
            $hasUse = false;
            foreach ($this->subject->spells as $idx => [ , $trigger, $charges, , , , ])
            {
                if ($trigger == SPELL_TRIGGER_EQUIP ||  $trigger ==  SPELL_TRIGGER_HIT)
                    continue;

                $hasUse = true;

                if ($charges >= 0)
                    continue;

                $tt = '[tooltip=tooltip_consumedonuse]'.Lang::item('consumable').'[/tooltip]';
                break;
            }

            if ($hasUse)
                $infobox[] = $tt ?? '[tooltip=tooltip_notconsumedonuse]'.Lang::item('nonConsumable').'[/tooltip]';
        }

        // related holiday
        if ($eId = $this->subject->eventId)
        {
            $this->extendGlobalIds(Type::WORLDEVENT, $eId);
            $infobox[] = Lang::game('eventShort', ['[event='.$eId.']']);
        }

        // tool
        if ($tId = $this->subject->totemCategory)
            if ($tName = DB::Aowow()->selectRow('SELECT * FROM ::totemcategory WHERE `id` = %i', $tId))
                $infobox[] = Lang::item('tool').'[url=?items&filter=cr=91;crs='.$tId.';crv=0]'.Util::localizedString($tName, 'name').'[/url]';

        // extendedCost
        $reqRating = null;
        if ($vendors = $this->subject->getExtendedCost($this->typeId, reqRating: $reqRating))
        {
            $stack    = $this->subject->buyCount;
            $divisor  = $stack;
            $each     = '';
            $handled  = [];
            $costList = [];
            foreach ($vendors as $items)
            {
                foreach ($items as $data)
                {
                    $tokens   = [];
                    $currency = [];

                    foreach ($data as $c => $qty)
                    {
                        if (is_string($c))
                            continue;

                        if (is_float($qty / $stack))
                            $divisor = 1;

                        if ($c < 0)                         // currency items (and honor or arena)
                            $currency[] = [-$c, $qty];
                        else if ($c > 0)                    // plain items (item1,count1,item2,count2,...)
                            $tokens[] = [$c, $qty];
                    }

                    // display every cost-combination only once
                    $final = [$data[0] ?? 0, $currency, $tokens];
                    if (in_array($final, $handled))
                        continue;

                    $handled[] = $final;

                    if (isset($data[0]))
                    {
                        if (is_float($data[0] / $stack))
                            $divisor = 1;

                        $cost = '[money='.($data[0] / $divisor);
                    }
                    else
                        $cost = '[money';

                    if ($tokens)
                        $cost .= ' items='.array_reduce($tokens, fn($out, $x) => $out .= ($out ? ',' : '') . $x[0] . ',' . ($x[1] / $divisor));
                    if ($currency)
                        $cost .= ' currency='.array_reduce($currency, fn($out, $x) => $out .= ($out ? ',' : '') . $x[0] . ',' . ($x[1] / $divisor));

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

            if ([$rating, $bracket] = $reqRating)
            {
                $text = str_replace('<br />', ' ', Lang::item('reqRating', $rating, [$bracket]));
                $infobox[] = Lang::breakTextClean($text, 30, Lang::FMT_MARKUP);
            }
        }

        // repair cost
        if ($_ = $this->subject->repairPrice)
            $infobox[] = Lang::item('repairCost').'[money='.$_.']';

        // avg auction buyout
        if (in_array($this->subject->bonding, [0, 2, 3]))
            if ($_ = Profiler::getBuyoutForItem($this->typeId))
                $infobox[] = '[tooltip=tooltip_buyoutprice]'.Lang::item('buyout.').'[/tooltip]'.Lang::main('colon').'[money='.$_.']'.$each;

        // avg money contained
        if ($_flags & ITEM_FLAG_OPENABLE)
            if ($_ = intVal(($this->subject->minMoneyLoot + $this->subject->maxMoneyLoot) / 2))
                $infobox[] = Lang::item('worth').'[tooltip=tooltip_avgmoneycontained][money='.$_.'][/tooltip]';

        // if it goes into a slot it may be disenchanted
        if ($_slot && $_class != ITEM_CLASS_CONTAINER)
        {
            if ($this->subject->disenchantId)
            {
                $_ = $this->subject->requiredDisenchantSkill;
                if ($_ < 1)                                 // these are some items, that never went live .. extremely rough emulation here
                    $_ = intVal($_ilvl / 7.5) * 25;

                $infobox[] = Lang::item('disenchantable').'&nbsp;([tooltip=tooltip_reqenchanting]'.$_.'[/tooltip])';
            }
            else
                $infobox[] = Lang::item('cantDisenchant');
        }

        if (($_flags & ITEM_FLAG_MILLABLE) && $this->subject->requiredSkill == SKILL_INSCRIPTION)
        {
            $infobox[] = Lang::item('millable').'&nbsp;([tooltip=tooltip_reqinscription]'.$this->subject->requiredSkillRank.'[/tooltip])';
            $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_INSCRIPTION, $this->subject->requiredSkillRank));
        }

        if (($_flags & ITEM_FLAG_PROSPECTABLE) && $this->subject->requiredSkill == SKILL_JEWELCRAFTING)
        {
            $infobox[] = Lang::item('prospectable').'&nbsp;([tooltip=tooltip_reqjewelcrafting]'.$this->subject->requiredSkillRank.'[/tooltip])';
            $infobox[] = Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_JEWELCRAFTING, $this->subject->requiredSkillRank));
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
        if ($this->subject->flagsExtra & 0x0100)
            $infobox[] = '[tooltip=tooltip_cannotrollneed]'.Lang::item('noNeedRoll').'[/tooltip]';

        // fits into keyring
        if ($_bagFamily & 0x0100)
            $infobox[] = Lang::item('atKeyring');

        // completion row added by InfoboxMarkup

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.($this->subject->name)(Locale::EN).'[/copy][/li]';

        return $infobox;
    }

    private function tabUnlocks() : array
    {
        $listviews = [null, null];
        $lockIds   = DB::Aowow()->selectCol(
           'SELECT `id` FROM ::lock WHERE            (`type1` = %i AND `properties1` = %i) OR
            (`type2` = %i AND `properties2` = %i) OR (`type3` = %i AND `properties3` = %i) OR
            (`type4` = %i AND `properties4` = %i) OR (`type5` = %i AND `properties5` = %i)',
            LOCK_TYPE_ITEM, $this->typeId, LOCK_TYPE_ITEM, $this->typeId,
            LOCK_TYPE_ITEM, $this->typeId, LOCK_TYPE_ITEM, $this->typeId,
            LOCK_TYPE_ITEM, $this->typeId
        );

        if (!$lockIds)
            return [null, null];

        // objects
        if (!($lockedObj = new GameobjectContainer(array(['lockId', $lockIds])))->error)
        {
            $this->addDataLoader('zones');

            $listviews[0] = new Listview(array(
                'data' => $lockedObj->getListviewData(),
                'name' => '$LANG.tab_unlocks',
                'id'   => 'unlocks-object',
            ), GameobjectEntry::$brickFile);
        }

        // items (generally unused. It's the spell on the item, that unlocks stuff)
        if (!($lockedItm = new ItemContainer(array(['lockId', $lockIds])))->error)
        {
            $this->extendGlobalData($lockedItm->getJSGlobals());

            $listviews[1] = new Listview(array(
                'data' => $lockedItm->getListviewData(),
                'name' => '$LANG.tab_unlocks',
                'id'   => 'unlocks-item'
            ), ItemEntry::$brickFile);
        }

        return $listviews;
    }

    private function tabContainedIn() : array
    {
        $lootTabs  = new LootByItem($this->typeId);
        $listviews = array_fill(0, 18, null);               // empty tabs not contained in iterator, so init as null
        if (!$lootTabs->getByItem())
            return [];

        $this->extendGlobalData($lootTabs->jsGlobals);

        foreach ($lootTabs->iterate() as $idx => [$template, $tabData])
        {
            if ($idx == LootByItem::ITEM_DISENCHANTED)
                $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=163;crs='.$this->typeId.';crv=0');

            if ($idx == LootByItem::NPC_DROPPED)
            {
                if (([$s, $sm] = $this->subject->getSources()) && $s[0] == SRC_DROP && isset($sm[0]['dd']))
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
            }

            if ($idx == LootByItem::OBJECT_FISHED && !$this->map)
            {
                $nodeIds  = array_map(fn($x) => $x['id'], $tabData['data']);
                $fishedIn = new GameobjectContainer(array(['id', $nodeIds]));
                if (!$fishedIn->error)
                {
                    // show mapper for fishing locations
                    if ($nodeSpawns = self::createFullSpawns($fishedIn, true, true, true, true))
                    {
                        $this->map = array(
                            ['parent' => 'mapper-generic'], // Mapper
                            $nodeSpawns,                    // mapperData
                            null,                           // ShowOnMap
                            [Lang::item('fishedIn')],       // foundIn
                            Lang::item('fishingLoc')        // title
                        );
                        foreach ($nodeSpawns as $areaId => $_)
                            $this->map[3][$areaId] = ZoneEntry::getName($areaId);
                    }
                }
            }

            if ($template == 'npc' || $template == 'object')
                $this->addDataLoader('zones');

            $listviews[$idx] = new Listview($tabData, $template);
        }

        return $listviews;
    }

    private function tabContains(string $lootTemplate, int $lootId, string $tabName, string $tabId, array $extraCols, array $hiddenCols = []) : ?Listview
    {
        $lootTab = new LootByContainer($lootTemplate, $lootId);
        if (!$lootTab->formatListview())
            return null;

        $this->extendGlobalData($lootTab->jsGlobals);

        $tabData = array(
            'data'            => $lootTab->getResult(),
            'name'            => $tabName,
            'id'              => $tabId,
            'computeDataFunc' => '$Listview.funcBox.initLootTable'
        );

        if ($extraCols = array_merge($extraCols, $lootTab->extraCols))
            $tabData['extraCols'] = array_unique($extraCols);

        if ($hiddenCols)
            $tabData['hiddenCols'] = array_unique($hiddenCols);

        return new Listview($tabData, ItemEntry::$brickFile);
    }

    private function tabContainsSpell() : ?Listview
    {
        if (!isset($this->subject->spells[0]) || $this->subject->spells[0][1] !== SPELL_TRIGGER_USE || !($s = $this->subject->spells[0][0]))
            return null;

        if (!($spellLoot = new LootByContainer(Loot::SPELL, $s))->formatListview())
            return null;

        $this->extendGlobalData($spellLoot->jsGlobals);

        if (($tab = $this->lvTabs->find(id: 'contains')) instanceof Listview)
        {
            $tab->appendData($spellLoot->getResult());
            return null;
        }

        return new Listview(array(
            'data'            => $spellLoot->getResult(),
            'name'            => '$LANG.tab_contains',
            'id'              => 'contains',
            'computeDataFunc' => '$Listview.funcBox.initLootTable',
            'extraCols'       => array_merge(['$Listview.extraCols.percent'], $spellLoot->extraCols)
        ), ItemEntry::$brickFile);
    }

    private function tabCreatedBy() : ?Listview
    {
        if (!($perfItem = DB::World()->selectAssoc('SELECT *, `spellId` AS ARRAY_KEY FROM skill_perfect_item_template WHERE `perfectItemType` = %i', $this->typeId)))
            return null;

        if (($perfSpells = new SpellContainer(array(['id', array_column($perfItem, 'spellId')])))->error)
            return null;

        $lvData = $perfSpells->getListviewData();
        $this->extendGlobalData($perfSpells->getJSGlobals(GLOBALINFO_RELATED));

        foreach ($lvData as $sId => &$data)
        {
            $data['percent'] = $perfItem[$sId]['perfectCreateChance'];
            if (Conditions::extendListviewRow($data, Conditions::SRC_NONE, $this->typeId, [Conditions::SPELL, $perfItem[$sId]['requiredSpecialization']]))
                $this->extendGlobalIDs(Type::SPELL, $perfItem[$sId]['requiredSpecialization']);
        }

        return new Listview(array(
            'data'      => $lvData,
            'name'      => '$LANG.tab_createdby',
            'id'        => 'created-by',            // should by exclusive with created-by from spell_loot
            'extraCols' => ['$Listview.extraCols.percent', '$Listview.extraCols.condition']
        ), SpellEntry::$brickFile);
    }

    private function tabTaughtBy() : array
    {
        $listviews = [];

        // step 1a - find spells that create this item
        $directSpells = DB::Aowow()->selectCol('SELECT `id` FROM ::spell WHERE %or',
            [
                [DB::AND, [['`effect1Id` IN %in', [SPELL_EFFECT_CREATE_ITEM, SPELL_EFFECT_CREATE_ITEM_2]], ['`effect1CreateItemId` = %i', $this->typeId]]],
                [DB::AND, [['`effect2Id` IN %in', [SPELL_EFFECT_CREATE_ITEM, SPELL_EFFECT_CREATE_ITEM_2]], ['`effect2CreateItemId` = %i', $this->typeId]]],
                [DB::AND, [['`effect3Id` IN %in', [SPELL_EFFECT_CREATE_ITEM, SPELL_EFFECT_CREATE_ITEM_2]], ['`effect3CreateItemId` = %i', $this->typeId]]]
            ]
        );

        if (!$directSpells)
            return [];

        // step 1b - find spells that teach found spells
        $indirectSpells = DB::Aowow()->selectCol('SELECT `id` FROM ::spell WHERE %and',
            [
                [DB::AND, [['`effect1Id` IN %in', [SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_PET_SPELL]], ['`effect1TriggerSpell` = %i', $this->typeId]]],
                [DB::AND, [['`effect2Id` IN %in', [SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_PET_SPELL]], ['`effect2TriggerSpell` = %i', $this->typeId]]],
                [DB::AND, [['`effect3Id` IN %in', [SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_PET_SPELL]], ['`effect3TriggerSpell` = %i', $this->typeId]]]
            ]
        ) ?: [];

        // taught by item
        $conditions = array(
            DB::OR,
            [DB::AND, ['spellTrigger1', SPELL_TRIGGER_LEARN], ['spellId1', $directSpells]],
            [DB::AND, ['spellTrigger2', SPELL_TRIGGER_LEARN], ['spellId2', $directSpells]],
            [DB::AND, ['spellTrigger3', SPELL_TRIGGER_LEARN], ['spellId3', $directSpells]]
        );

        if ($indirectSpells)
            array_push($conditions,
                [DB::AND, ['spellTrigger1', SPELL_TRIGGER_USE], ['spellId2', $indirectSpells]],
                [DB::AND, ['spellTrigger1', SPELL_TRIGGER_USE], ['spellId2', $indirectSpells]],
                [DB::AND, ['spellTrigger1', SPELL_TRIGGER_USE], ['spellId2', $indirectSpells]]
            );

        if (!($tbItems = new ItemContainer($conditions))->error)
        {
            $this->extendGlobalData($tbItems->getJSGlobals());

            $listviews[1] = new Listview(array(
                'data' => $tbItems->getListviewData(),
                'id'   => 'taught-by-item',
                'name' => '$LANG.tab_taughtby',
            ), ItemEntry::$brickFile);
        }

        // taught by quest
        $conditions = [DB::OR, ['rewardSpell', $directSpells]];
        if ($indirectSpells)
            $conditions[] = ['rewardSpellCast', $indirectSpells];

        if (!($tbQuests = new QuestContainer($conditions))->error)
        {
            $this->extendGlobalData($tbQuests->getJSGlobals());

            $listviews[2] = new Listview(array(
                'data' => $tbQuests->getListviewData(),
                'id'   => 'taught-by-quest',
                'name' => '$LANG.tab_taughtby',
            ), QuestEntry::$brickFile);
        }

        // taught by npc (trainer)
        $trainers = DB::World()->selectAssoc(
           'SELECT  cdt.`CreatureId` AS ARRAY_KEY, ts.`ReqSkillLine` AS "reqSkillId", ts.`ReqSkillRank` AS "reqSkillValue", ts.`ReqLevel` AS "reqLevel", ts.`ReqAbility1` AS "reqSpellId1", ts.`reqAbility2` AS "reqSpellId2"
            FROM    creature_default_trainer cdt
            JOIN    trainer_spell ts ON ts.`TrainerId` = cdt.`TrainerId`
            WHERE   ts.`SpellId` IN %in',
            array_merge($directSpells, $indirectSpells)
        );

        if ($trainers && !($tbTrainer = new CreatureContainer(array(['ct.id', array_keys($trainers)], ['s.guid', null, '!'], ['ct.npcflag', NPC_FLAG_TRAINER, '&'])))->error)
        {
            $this->addDataLoader('zones');

            $this->extendGlobalData($tbTrainer->getJSGlobals());

            $cnd = new Conditions();

            foreach ($trainers as $tId => $train)
            {
                if ($_ = $train['reqLevel'])
                    $cnd->addExternalCondition(Conditions::SRC_NONE, $tId, [Conditions::LEVEL, $_, Conditions::OP_GT_E]);

                if ($_ = $train['reqSkillId'])
                    $cnd->addExternalCondition(Conditions::SRC_NONE, $tId, [Conditions::SKILL, $_, $train['reqSkillValue']]);

                for ($i = 1; $i < 3; $i++)
                    if ($_ = $train['reqSpellId'.$i])
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $tId, [Conditions::SPELL, $_]);
            }

            $lvData = $tbTrainer->getListviewData();
            $extraCols = [];
            if ($cnd->toListviewColumn($lvData, $extraCols))
                $this->extendGlobalData($cnd->getJSGlobals());

            $tabData = array(
                'data' => $lvData,
                'id'   => 'taught-by-npc',
                'name' => '$LANG.tab_taughtby',
            );

            if ($extraCols)
                $tabData['extraCols'] = $extraCols;

            $listviews[0] = new Listview($tabData, CreatureEntry::$brickFile);
        }

        // taught by spell ? (everything left over)

        return $listviews;
    }

    private function tabCanContain() : ?Listview
    {
        if ($this->subject->slots <= 0)
            return null;

        if (($contains = new ItemContainer(array(['bagFamily', $this->subject->bagFamily, '&'], ['slots', 1, '<'])))->error)
            return null;

        $this->extendGlobalData($contains->getJSGlobals());

        $hCols = ['side'];
        if (!$contains->hasSetFields('slot'))
            $hCols[] = 'slot';

        return new Listview(array(
            'data'       => $contains->getListviewData(),
            'name'       => '$LANG.tab_cancontain',
            'id'         => 'can-contain',
            'hiddenCols' => $hCols
        ), ItemEntry::$brickFile);
    }

    private function tabCanBePlacedIn() : ?Listview
    {
        if (($bf = $this->subject->bagFamily) == 0x0100)
            return null;

        if (($contains = new ItemContainer(array(['bagFamily', $bf, '&'], ['slots', 0, '>'])))->error)
            return null;

        $this->extendGlobalData($contains->getJSGlobals());

        return new Listview(array(
            'data'       => $contains->getListviewData(),
            'name'       => '$LANG.tab_canbeplacedin',
            'id'         => 'can-be-placed-in',
            'hiddenCols' => ['side']
        ), ItemEntry::$brickFile);
    }

    private function tabCriteriaOf() : ?Listview
    {
        $conditions = array(
            ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM, ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM, ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM, ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM]],
            ['ac.value1', $this->typeId]
        );

        if (($criteriaOf = new AchievementContainer($conditions))->error)
            return null;

        $this->extendGlobalData($criteriaOf->getJSGlobals(GLOBALINFO_REWARDS));

        return new Listview(array(
            'data'        => $criteriaOf->getListviewData(),
            'name'        => '$LANG.tab_criteriaof',
            'id'          => 'criteria-of',
            'visibleCols' => ['category'],
            'hiddenCols'  => $criteriaOf->hasSetFields('reward_loc'.Lang::getLocale()->value) ? null : ['rewards']
        ), AchievementEntry::$brickFile);
    }

    private function tabReagentFor() : ?Listview
    {
        $conditions = array(
            DB::OR,
            ['reagent1', $this->typeId], ['reagent2', $this->typeId], ['reagent3', $this->typeId], ['reagent4', $this->typeId],
            ['reagent5', $this->typeId], ['reagent6', $this->typeId], ['reagent7', $this->typeId], ['reagent8', $this->typeId]
        );

        if (($reagent = new SpellContainer($conditions))->error)
            return null;

        $this->extendGlobalData($reagent->getJSGlobals(GLOBALINFO_RELATED));

        return new Listview(array(
            'data'        => $reagent->getListviewData(),
            'name'        => '$LANG.tab_reagentfor',
            'id'          => 'reagent-for',
            'visibleCols' => ['reagents']
        ), SpellEntry::$brickFile);
    }

    private function tabStartsQuest() : ?Listview
    {
        if (!($qId = $this->subject->startQuest))
            return null;

        if (($starts = new QuestEntry($qId))->error)
            return null;

        $this->extendGlobalData($starts->getJSGlobal(GLOBALINFO_REWARDS));

        return new Listview(array(
            'data' => [$starts->getListviewRow()],
            'name' => '$LANG.tab_starts',
            'id'   => 'starts-quest'
        ), QuestEntry::$brickFile);
    }

    private function tabToolFor() : ?Listview
    {
        if (!($toolCatg = $this->subject->totemCategory))
            return null;

        // find tools fully satisfied by given tool within the same category
        if (!($toolCatgs = DB::Aowow()->selectCol('SELECT b.`id` FROM ::totemcategory a JOIN ::totemcategory b ON a.`category` = b.`category` AND (b.`categorymask` & ~a.`categorymask`) = 0 WHERE a.`id` = %i', $toolCatg)))
            return null;

        if (($toolSpells = new SpellContainer(array(DB::OR, ['toolCategory1', $toolCatgs], ['toolCategory2', $toolCatgs])))->error)
            return null;

        $this->extendGlobalData($toolSpells->getJSGlobals(GLOBALINFO_RELATED));

        return new Listview(array(
            'data' => $toolSpells->getListviewData(),
            'name' => '$LANG.tab_toolfor',
            'id'   => 'tool-for'
        ), SpellEntry::$brickFile);
    }

    private function tabObjectiveOfQuest() : ?Listview
    {
        $conditions = array(
            DB::OR,
            ['reqItemId1', $this->typeId], ['reqItemId2', $this->typeId], ['reqItemId3', $this->typeId],
            ['reqItemId4', $this->typeId], ['reqItemId5', $this->typeId], ['reqItemId6', $this->typeId]
        );

        if (($objective = new QuestContainer($conditions))->error)
            return null;

        $this->extendGlobalData($objective->getJSGlobals(GLOBALINFO_REWARDS));

        return new Listview(array(
            'data' => $objective->getListviewData(),
            'name' => '$LANG.tab_objectiveof',
            'id'   => 'objective-of-quest'
        ), QuestEntry::$brickFile);
    }

    private function tabProvidedForQuest() : ?Listview
    {
        $conditions = array(
            DB::OR,
            ['sourceItemId', $this->typeId],
            ['reqSourceItemId1', $this->typeId], ['reqSourceItemId2', $this->typeId],
            ['reqSourceItemId3', $this->typeId], ['reqSourceItemId4', $this->typeId]
        );

        if (($provided = new QuestContainer($conditions))->error)
            return null;

        $this->extendGlobalData($provided->getJSGlobals(GLOBALINFO_REWARDS));

        return new Listview(array(
            'data' => $provided->getListviewData(),
            'name' => '$LANG.tab_providedfor',
            'id'   => 'provided-for-quest'
        ), QuestEntry::$brickFile);
    }

    private function tabSoldByNpc() : ?Listview
    {
        if (!($vendorData = $this->subject->getVendorData([$this->typeId])))
            return null;

        $vendors = array_pop($vendorData);
        if (($soldBy  = new CreatureContainer(array(['id', array_keys($vendors)])))->error)
            return null;

        // show mapper for vendors
        if ($vendorSpawns = self::createFullSpawns($soldBy, true, true, true, true))
        {
            $this->map = array(
                ['parent' => 'mapper-generic'],     // Mapper
                $vendorSpawns,                      // mapperData
                null,                               // ShowOnMap
                [Lang::item('purchasedIn')],        // foundIn
                Lang::item('vendorLoc')             // title
            );
            foreach ($vendorSpawns as $areaId => $_)
                $this->map[3][$areaId] = ZoneEntry::getName($areaId);
        }

        $sbData = $soldBy->getListviewData();
        $this->extendGlobalData($soldBy->getJSGlobals());
        $this->addDataLoader('zones');

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
            $row['cost']  = [$vendors[$k][0][0] ?? 0];

            if ($e = $vendors[$k][0]['event'])
                $cnd->addExternalCondition(Conditions::SRC_NONE, $k.':'.$this->typeId, [Conditions::ACTIVE_EVENT, $e]);

            if ($currency || $tokens)               // fill idx:3 if required
                $row['cost'][] = $currency;

            if ($tokens)
                $row['cost'][] = $tokens;

            if ($x = $this->subject->buyPrice)
                $row['buyprice'] = $x;

            if ($x = $this->subject->sellPrice)
                $row['sellprice'] = $x;

            if ($x = $this->subject->buyCount)
                $row['stack'] = $x;
        }

        if ($cnd->toListviewColumn($sbData, $extraCols, 'id', $this->typeId))
            $this->extendGlobalData($cnd->getJSGlobals());

        return new Listview(array(
            'data'       => $sbData,
            'name'       => '$LANG.tab_soldby',
            'id'         => 'sold-by-npc',
            'extraCols'  => $extraCols,
            'hiddenCols' => ['level', 'type']
        ), CreatureEntry::$brickFile);
    }

    private function tabCurrencyFor() : ?Listview
    {
        // some minor trickery: get arenaPoints(43307) and honorPoints(43308) directly
        [$where, $note] = match($this->typeId)
        {
            43307   => [['`reqArenaPoints` > 0'], '?items&filter=cr=145;crs=1;crv=0'],
            43308   => [['`reqHonorPoints` > 0'], '?items&filter=cr=144;crs=1;crv=0'],
            default => [[['`reqItemId1` = %i', $this->typeId], ['`reqItemId2` = %i', $this->typeId], ['`reqItemId3` = %i', $this->typeId], ['`reqItemId4` = %i', $this->typeId], ['`reqItemId5` = %i', $this->typeId]], null]
        };

        if (!$note && !is_null(ItemFilter::getCriteriaIndex(158, $this->typeId)))
            $note = '?items&filter=cr=158;crs='.$this->typeId.';crv=0';

        if (!($xCostIds = DB::Aowow()->selectCol('SELECT `id` FROM ::itemextendedcost WHERE %or', $where)))
            return null;

        if (!($vendorIds = DB::World()->selectCol('SELECT `item` FROM npc_vendor WHERE `extendedCost` IN %in UNION SELECT `item` FROM game_event_npc_vendor WHERE `extendedCost` IN %in', $xCostIds, $xCostIds)))
            return null;

        if (($boughtBy = new ItemContainer(array(['id', $vendorIds])))->error)
            return null;

        if ($iCur = DB::Aowow()->selectCell('SELECT `id` FROM ::currencies WHERE `itemId` = %i', $this->typeId))
            $filter = [Type::ITEM => $this->typeId];
        else
            $filter = [Type::CURRENCY => $iCur->id];

        $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_RELATED));

        return new Listview(array(
            'data'      => $boughtBy->getListviewData(LISTVIEWINFO_VENDOR, $filter),
            'name'      => '$LANG.tab_currencyfor',
            'id'        => 'currency-for',
            'extraCols' => ["\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost'],
            'note'      => $note ? sprintf(Util::$filterResultString, $note) : null
        ), ItemEntry::$brickFile);
    }

    private function tabTeaches() : ?Listview
    {
        $ids = $indirect = [];
        foreach ($this->subject->spells as $idx => [$spellId, $trigger, , , , , ])
        {
            if ($trigger == SPELL_TRIGGER_LEARN)
                $ids[] = $spellId;
            else if ($trigger == SPELL_TRIGGER_USE && $spellId > 0)
                $indirect[] = $spellId;
        }

        // taught indirectly
        if ($indirect)
        {
            $indirectSpells = new SpellContainer(array(['id', $indirect]));
            foreach ($indirectSpells->iterate() as $spellEntry)
                $ids = array_merge($ids, array_intersect_key($spellEntry->effectTriggerSpell, $spellEntry->canTeachSpell()));

            $ids = array_merge($ids, Game::getTaughtSpells(...$indirect));
        }

        if (!$ids)
            return null;

        if (($taughtSpells = new SpellContainer(array(['id', $ids])))->error)
            return null;

        $this->extendGlobalData($taughtSpells->getJSGlobals(GLOBALINFO_RELATED));

        $visCols = ['level', 'schools'];
        if ($taughtSpells->hasSetFields('reagent'))
            $visCols[] = 'reagents';

        return new Listview(array(
            'data'        => $taughtSpells->getListviewData(),
            'name'        => '$LANG.tab_teaches',
            'id'          => 'teaches',
            'visibleCols' => $visCols
        ), SpellEntry::$brickFile);
    }

    private function tabSeeAlso() : ?Listview
    {
        $conditions = array(
            Listview::DEFAULT_SIZE,
            ['id', $this->typeId, '!'],
            ['class',     $this->subject->class],
            ['subClass',  $this->subject->subClass],
            ['slot',      $this->subject->slot],
            ['itemLevel', $this->subject->itemLevel - 15, '>'],
            ['itemLevel', $this->subject->itemLevel + 15, '<'],
            ['quality',   $this->subject->quality]
        );

        // additional filtering by stat archetype
        $stats = $this->subject->itemStats;
        // tank (not dodge)
        if ($stats->get(Stat::DEFENSE_RTG) || $stats->get(Stat::PARRY_RTG) || $stats->get(Stat::BLOCK_RTG))
            $conditions[] = [DB::OR, ['is.defrtng', 0, '!'], ['is.parryrtng', 0, '!'], ['is.blockrtng', 0, '!']];
        // dps: agi
        else if ($stats->get(Stat::AGILITY))
            $conditions[] = ['is.agi', 0, '!'];
        // dps: str
        else if ($stats->get(Stat::STRENGTH))
            $conditions[] = ['is.str', 0, '!'];
        // caster (spi)
        else if ($stats->get(Stat::SPELL_POWER) && !$stats->get(Stat::SPIRIT))
            $conditions[] = [DB::AND, ['is.splpwr', 0, '!'], ['is.spi', 0]];
        // caster (int)
        else if ($stats->get(Stat::SPELL_POWER) && $stats->get(Stat::SPIRIT))
            $conditions[] = [DB::AND, ['is.splpwr', 0, '!'], ['is.spi', 0, '!']];

        // by name part - separate object, the OR condition blows up query execution time
        $tokens = [];
        foreach (explode(' ', $this->subject->name) as $raw)
            if (([, $fulltext, $ex] = DBTypeFilter::transformToken($raw)) && !$ex)
                foreach ($fulltext as $ft)
                    $tokens[] = '+' . $ft  . '*';

        $lvData = [];
        if ($tokens && !($byName = new ItemContainer(array(['nml.nName', $tokens, 'MATCH'])))->error)
        {
            $this->extendGlobalData($byName->getJSGlobals());
            $lvData += $byName->getListviewData();
        }

        if ($this->subject->class != ITEM_CLASS_QUEST) // generally unrelated stuff
            if (!($saItems = new ItemContainer($conditions))->error)
            {
                $this->extendGlobalData($saItems->getJSGlobals());
                $lvData += $saItems->getListviewData();
            }

        if (!$lvData)
            return null;

        return new Listview(array(
            'data' => $lvData,
            'name' => '$LANG.tab_seealso',
            'id'   => 'see-also'
        ), ItemEntry::$brickFile);
    }

    private function tabSameModelAs() : ?Listview
    {
        if (!($slot = $this->subject->slot))
            return null;

        if (!($model = $this->subject->model))
            return null;

        if (($sameModel = new ItemContainer(array(['model', $model], ['id', $this->typeId, '!'], ['slot', $slot])))->error)
            return null;

        $this->extendGlobalData($sameModel->getJSGlobals());

        return new Listview(array(
            'data'            => $sameModel->getListviewData(LISTVIEWINFO_MODEL),
            'name'            => '$LANG.tab_samemodelas',
            'id'              => 'same-model-as',
            'genericlinktype' => 'item'
        ));
    }

    private function tabSharedCooldown() : ?Listview
    {
        $cdCats    = [];
        $useSpells = [];
        foreach ($this->subject->spells as $idx => [$spellId , , , , , $category, ])
        {
            // as defined on item
            if ($category > 0)
                $cdCats[] = $category;

            // as defined in spell
            $useSpells[] = $spellId;
        }

        if ($useSpells && ($_ = DB::Aowow()->selectCol('SELECT `category` FROM ::spell WHERE `id` IN %in AND `recoveryCategory` > 0 AND `category` <> 0', $useSpells)))
            $cdCats += $_;

        if (!$cdCats)
            return null;

        $conditions = array(
            ['id', $this->typeId, '!'],
            [
                DB::OR,
                ['spellCategory1', $cdCats], ['spellCategory2', $cdCats],
                ['spellCategory3', $cdCats], ['spellCategory4', $cdCats],
                ['spellCategory5', $cdCats]
            ]
        );

        if ($spellsByCat = DB::Aowow()->selectCol('SELECT `id` FROM ::spell WHERE `category` IN %in', $cdCats))
            for ($i = 1; $i < 6; $i++)
                $conditions[1][] = ['spellId'.$i, $spellsByCat];

        if (($cdItems = new ItemContainer($conditions))->error)
            return null;

        $this->extendGlobalData($cdItems->getJSGlobals());

        return new Listview(array(
            'data' => $cdItems->getListviewData(),
            'name' => '$LANG.tab_sharedcooldown',
            'id'   => 'shared-cooldown'
        ), ItemEntry::$brickFile);
    }

    private function tabSounds() : ?Listview
    {
        $soundIds = [];
        if ($this->subject->class == ITEM_CLASS_WEAPON)
        {
            if ($this->subject->soundOverrideSubclass > 0)
                $scm = (1 << $this->subject->soundOverrideSubclass);
            else
                $scm = (1 << $this->subject->subClass);

            $soundIds = DB::Aowow()->selectCol('SELECT `soundId` FROM ::items_sounds WHERE `subClassMask` & %i', $scm);
        }

        $fields = ['pickUpSoundId', 'dropDownSoundId', 'sheatheSoundId', 'unsheatheSoundId'];
        foreach ($fields as $f)
            if ($x = $this->subject->{$f})
                $soundIds[] = $x;

        if ($x = $this->subject->spellVisualId)
        {
            if ($spellSounds = DB::Aowow()->selectRow('SELECT * FROM ::spell_sounds WHERE `id` = %i', $x))
            {
                array_shift($spellSounds);                  // bye 'id'-field
                foreach ($spellSounds as $ss)
                    if ($ss)
                        $soundIds[] = $ss;
            }
        }

        if (!$soundIds)
            return null;

        if (($sounds = new SoundContainer(array(['id', $soundIds])))->error)
            return null;

        $this->extendGlobalData($sounds->getJSGlobals());

        return new Listview(['data' => $sounds->getListviewData()], SoundEntry::$brickFile);
    }

    private function tabConditionFor() : ?array
    {
        $cnd = new Conditions();
        $cnd->getByCondition(Type::ITEM, $this->typeId)->prepare();
        if (!($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for')))
            return null;

        $this->extendGlobalData($cnd->getJSGlobals());
        return $tab;
    }

    protected function generateMetadata(bool $useArticle = true) : void
    {
        $this->metaTags[] = ['property' => 'og:title', 'content' => $this->h1];
        $this->metaTags[] = ['property' => 'og:type',  'content' => 'article'];

        $keywords     = [$this->h1, Lang::game('items')];
        $_class       = $this->subject->class;
        $_subClass    = $this->subject->subClass;
        $_subSubClass = $this->subject->subSubClass;
        $_itemLevel   = $this->subject->itemLevel;
        $_slot        = $this->subject->slot;
        $_quality     = $this->subject->quality;

        if ($_subSubClass)
        {
            if ($_class == ITEM_CLASS_GLYPH)
            {
                $keywords[] = Lang::item('cat', $_class, 1, $_subClass);
                $keywords[] = Lang::item('glyphType', $_subSubClass);
            }
            else
            {
                $keywords[] = Lang::item('cat', $_class, 0);
                $keywords[] = Lang::item('cat', $_class, 1, $_subClass, 1, $_subSubClass);
            }
        }
        else
        {
            $cl = Lang::item('cat', $_class);
            if (is_array($cl))
            {
                $keywords[] = $cl[0];
                $keywords[] = current((array)Lang::item('cat', $_class, 1, $_subClass));
            }
            else
                $keywords[] = $cl;
        }

        array_unshift($this->metaTags, ['name' => 'keywords', 'content' => [...$keywords, ...Lang::meta('tags', 'generic')]]);

        $desc = '';
        // ..by cat
        switch ($_class)
        {
            case ITEM_CLASS_CONTAINER:
            case ITEM_CLASS_QUIVER:
                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_CONTAINER, [$this->h1, $this->subject->slots, Lang::item('subClass', $_class, $_subClass)]);
                break;
            case ITEM_CLASS_WEAPON:
                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_WEAPON, [Lang::item('quality', $_quality), Lang::item('inventoryType', $_slot).' '.Lang::item('subClass', $_class, $_subClass), $_itemLevel]);
                break;
            case ITEM_CLASS_GEM:
                $lvl = '';
                if ($this->subject->gemEnchantmentId)
                    $lvl = Lang::quest('questLevel', [$_itemLevel]).' ';

                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_GEM, [$lvl, Lang::item('quality', $_quality), Lang::item('_gemColors', $_subClass)]);
                break;
            case ITEM_CLASS_ARMOR:
                $asc  = $_subClass < 0 ? Lang::item('cat', ITEM_CLASS_ARMOR, 1, $_subClass) : Lang::item('subClass', $_class, $_subClass).' '.Lang::item('cat', ITEM_CLASS_ARMOR, 0);
                $desc = Lang::meta('itemCatDesc', 4, [Lang::item('quality', $_quality), $asc, $_itemLevel, Lang::item('inventoryType', $_slot)]);
                break;
            case ITEM_CLASS_KEY:
                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_KEY, [$this->h1]);
                break;
            case ITEM_CLASS_GLYPH:
                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_GLYPH, [Lang::game('gl', $_subSubClass), Lang::game('cl', $_subClass)]);
                break;
            case ITEM_CLASS_AMMUNITION:
                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_AMMUNITION, [$this->h1, $this->subject->json['dps']]);
                break;
            case ITEM_CLASS_TRADEGOOD:
                if ($_subClass != 2)                        // treat explosives as consumables
                {
                    $desc = Lang::meta('itemCatDesc', ITEM_CLASS_TRADEGOOD, [$this->h1]);
                    break;
                }
            default:
                $lvl = '';
                if (in_array($_class, [ITEM_CLASS_CONSUMABLE, ITEM_CLASS_TRADEGOOD]))
                    $lvl = Lang::quest('questLevel', [$_itemLevel]).' ';

                $type = Lang::item('cat', $_class);
                if (is_array($type) && isset($type[1][$_subClass]))
                {
                    $type = $type[1][$_subClass];
                    if (is_array($type) && isset($type[1][$_subSubClass]))
                        $type = $type[1][$_subSubClass];
                }

                $desc = Lang::meta('itemCatDesc', ITEM_CLASS_CONSUMABLE, [$this->h1, $lvl, is_array($type) ? $type[0] : $type]);
        }

        // source.. (glyph sources are an appendix to description)
        if ($_class != ITEM_CLASS_GLYPH && ([$s, $sm] = $this->subject->getSources()))
        {
            if ($sm && isset($sm[0]['n']) && Lang::exist('meta', 'itemSourceMore', $s[0])) // test for name; sm can also declare a zone drops
                $desc .= ' '.Lang::meta('itemSourceMore', $s[0], [$sm[0]['n']]);
            else if ($s = array_intersect($s, [SRC_CRAFTED, SRC_DROP, SRC_PVP, SRC_QUEST, SRC_VENDOR, SRC_REDEMPTION, SRC_STARTER, SRC_ACHIEVEMENT, SRC_DISENCHANTMENT, SRC_FISHING, SRC_GATHERING, SRC_MILLING, SRC_MINING, SRC_PROSPECTING, SRC_PICKPOCKETING, SRC_SALVAGING, SRC_SKINNING]))
                $desc .= ' '.Lang::meta('itemSource', 0, [Lang::concat($s, callback: fn($x) => lang::meta('itemSource', 1, $x))]);
        }

        // category..
        $desc .= ' '.Lang::meta('inCategory', [end($keywords)]);

        // requires...
        $req = [];
        // ..class
        if ($rc = ($this->subject->requiredClass & ChrClass::MASK_ALL))
            if ($_ = Lang::concat(ChrClass::fromMask($rc), Lang::CONCAT_OR, fn($x) => Lang::game('cl', $x)))
                $req[] = $_;

        // ..race
        if ($rr = ($this->subject->requiredRace) & ChrRace::MASK_ALL)
        {
            if ($rr == ChrRace::MASK_ALLIANCE)
                $req[] = Lang::game('ra', -1);
            else if ($rr == ChrRace::MASK_HORDE)
                $req[] = Lang::game('ra', -2);
            else if ($rr && $rr != ChrRace::MASK_ALL)
                if ($_ = Lang::concat(ChrRace::fromMask($rr), Lang::CONCAT_OR, fn($x) => Lang::game('ra', $x)))
                    $req[] = $_;
        }

        // ..honor rank
        if ($rhr = $this->subject->requiredHonorRank)
            $req[] = Lang::game('pvpRank', $rhr);

        // ..skill
        if ($rs = $this->subject->requiredSkill)
        {
            $b = SkillEntry::getName($rs);
            if (($rsr = $this->subject->requiredSkillRank) > 0)
                $b .= ' ('.$rsr.')';

            $req[] = $b;
        }

        // ..spell
        if ($rs = $this->subject->requiredSpell)
            $req[] = SpellEntry::getName($rs);

        // ..reputation /w
        if ($rf = $this->subject->requiredFaction)
            $req[] = FactionEntry::getName($rf).' - '.Lang::game('rep', $this->subject->requiredFactionRank);

        if ($req)
            $desc .= ' '.Lang::game('requires', [Lang::concat($req)]);

        $this->buildBasicMetadata($desc, $this->subject->icon);

        $this->buildLdJson();
    }
}

?>
