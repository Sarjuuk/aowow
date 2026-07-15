<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemEntry extends DBTypeEntry
{
    use TrSourceHelper;

    public readonly  int            $cuFlags;
    public readonly  LocString      $name;
    public readonly  LocString      $description;
    public readonly  int            $class;
    public readonly  int            $classBak;
    public readonly  int            $subClass;
    public readonly  int            $subClassBak;
    public readonly  int            $soundOverrideSubclass;
    public readonly  int            $subSubClass;
    public readonly  int            $quality;
    public readonly  int            $flags;
    public readonly  int            $flagsExtra;
    public readonly  int            $buyCount;
    public readonly  int            $buyPrice;
    public readonly  int            $sellPrice;
    public readonly  int            $repairPrice;
    public readonly  int            $slot;
    public readonly  int            $slotBak;
    public readonly  int            $itemLevel;
    public readonly  int            $maxCount;
    public readonly ?int            $stackable;
    public readonly  int            $slots;
    public readonly  int            $bonding;
    public readonly  int            $pageTextId;
    public readonly  int            $languageId;
    public readonly  int            $startQuest;
    public readonly  int            $lockId;
    public readonly  int            $material;
    public readonly  int            $randomEnchant;
    public readonly  int            $itemset;
    public readonly  int            $durability;
    public readonly  int            $bagFamily;
    public readonly  int            $totemCategory;
    public readonly  int            $duration;
    public readonly  int            $itemLimitCategory;
    public readonly  string         $scriptName;
    public readonly  int            $foodType;
    public readonly  int            $flagsCustom;
    // loot
    public readonly  int            $disenchantId;
    public readonly  int            $minMoneyLoot;
    public readonly  int            $maxMoneyLoot;
    // display
    public readonly  int            $iconId;
    public readonly  string         $icon;
    public readonly  int            $displayId;
    public readonly  string         $model;
    public readonly  int            $spellVisualId;
    public readonly  int            $pickUpSoundId;
    public readonly  int            $dropDownSoundId;
    public readonly  int            $sheatheSoundId;
    public readonly  int            $unsheatheSoundId;
    // requirements
    public readonly  int            $requiredClass;
    public readonly  int            $requiredRace;
    public readonly  int            $requiredLevel;
    public readonly  int            $requiredSkill;
    public readonly  int            $requiredSkillRank;
    public readonly  int            $requiredSpell;
    public readonly  int            $requiredHonorRank;
    public readonly  int            $requiredCityRank;
    public readonly  int            $requiredFaction;
    public readonly  int            $requiredFactionRank;
    public readonly  int            $requiredDisenchantSkill;
    public readonly  int            $area;
    public readonly  int            $map;
    public readonly  int            $eventId;
    // stats
    /** @var int[] $statType - length: 10 */
    public readonly  array          $statType;
    /** @var int[] $statValue - length: 10 */
    public readonly  array          $statValue;
    public readonly  int            $scalingStatDistribution;
    public readonly  int            $scalingStatValue;
    /** @var array{float, float, int} $dmg1 - [min, max, type] */
    public readonly  array          $dmg1;
    /** @var array{float, float, int} $dmg2 - [min, max, type] */
    public readonly  array          $dmg2;
    public readonly  int            $delay;
    public readonly  int            $armor;
    public readonly  float          $armorDamageModifier;
    public readonly  int            $block;
    /** @var int[] $res - [holy, fire, nature, frost, shadow, arcane] */
    public readonly  array          $resistance;
    public readonly  int            $ammoType;
    public readonly  float          $rangedModRange;
    /** @var int[][] $spells - length: 5 [spellid, trigger, charges, ppm, cooldown, category, categoryCooldown] */
    public readonly  array          $spells;
    /** @var int $cooldown - meta: max of all proc/onUse spell cooldowns */
    public readonly  int            $cooldown;
    /** @var int[] $socketColor - length: 3 */
    public readonly  array          $socketColor;
    /** @var int[] $socketContent - length: 3 */
    public readonly  array          $socketContent;
    public readonly  int            $socketBonus;
    public readonly  int            $gemColorMask;
    public readonly  int            $gemEnchantmentId;
    /** @var StatsContainer $itemStats importet from ::item_stats table */
    public readonly  StatsContainer $itemStats;

    public static  int    $dbType          = Type::ITEM;
    public static  string $brickFile       = 'item';
    public static  string $dataTable       = '::items';
    public         array  $json            = [];
    public         array  $subItems        = [];

    // bulk assigned / load on demand
    private static ?int   $feralWeaponMask     = null;      // static, so if any Item needs this it is loaded for all items everywhere at once
    private         array $scalingDistribution = [];
    private         array $scalingValues       = [];
    private        ?array $randPropPointEntry  = null;

    private         int   $weightScore      = 0;            // only set/queried for when using stat weights in ItemFilter
    private         array $enhance          = [];           // tooltip/name enhancements
    private         array $subItemEnchants  = [];           // todo should be in ItemContainer..?

    private         array $vendors          = [];
    private         array $jsGlobals        = [];           // getExtendedCost creates some and has no access to template
    private        ?array $randEnchantEntry = null;
    private         array $relEnchant       = [];

    public const /* string */ QUERY_BASE  = 'SELECT i.*, i.`block` AS "tplBlock", i.`armor` AS tplArmor, i.`dmgMin1` AS "tplDmgMin1", i.`dmgMax1` AS "tplDmgMax1", i.`id` AS ARRAY_KEY, i.`id` AS "id" FROM ::items i';
    public const /* array  */ QUERY_OPTS  = array(          // 3 => Type::ITEM
        'i'   => [['is', 'src', 'ic'], 'o' => 'i.`quality` DESC, i.`itemLevel` DESC'],
        'nml' => ['j' => ['::items_search nml ON nml.`id` = i.`id` AND nml.`locale` = DB_LOC_I']],
        'ic'  => ['j' => ['::icons      `ic`  ON `ic`.`id` = `i`.`iconId`', true], 's' => ', ic.`name` AS "icon"'],
        'is'  => ['j' => ['::item_stats `is`  ON `is`.`type` = 3 AND `is`.`typeId` = `i`.`id`', true], 's' => ', `is`.*'],
        's'   => ['j' => ['::spell      `s`   ON `s`.`effect1CreateItemId` = `i`.`id`', true], 'g' => 'i.`id`'],
        'e'   => ['j' => ['::events     `e`   ON `e`.`id` = `i`.`eventId`', true], 's' => ', e.`holidayId`'],
        'iv'  => ['j' => ['::itemvisuals `iv` ON `iv`.`id` = `i`.`itemVisualId`']],
        'src' => ['j' => ['::source     `src` ON `src`.`type` = 3 AND `src`.`typeId` = `i`.`id`', true], 's' => ', `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`, `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
    );

    public function __construct(int|array $initData, array $extraOpts = [])
    {
        parent::__construct($initData, $extraOpts);

        // readdress itemset .. is wrong for virtual sets
        if (isset($miscData['pcsToSet']))
            $this->json['itemset'] = $miscData['pcsToSet'];

        // additional rel attribute for listview rows
        if (isset($miscData['extraOpts']['relEnchant']))
            $this->relEnchant = $miscData['extraOpts']['relEnchant'];

        /*
         *  enhance (set by comparison tool or formated external links)
         *      ench: enchantmentId
         *      sock: bool (extraScoket (gloves, belt))
         *      gems: array (:-separated itemIds)
         *      rand: >0: randomPropId; <0: randomSuffixId
         */
        if (isset($miscData['extraOpts']['enhance']))
            $this->enhance = $miscData['extraOpts']['enhance'];
    }

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->initSources($initData);

        $this->name        = new LocString($initData, 'name',        pruneFromSrc: true);
        $this->description = new LocString($initData, 'description', pruneFromSrc: true);

        // structure spell info and determine item cooldown
        $spells   = [];
        $cooldown = 0;
        for ($i = 1; $i < 6; $i++)
        {
            if (!$initData['spellId'.$i])
                continue;

            if (in_array($initData['spellTrigger'.$i], [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY, SPELL_TRIGGER_HIT]))
                $cooldown = max($cooldown, $initData['spellCooldown'.$i], $initData['spellCategoryCooldown'.$i]);

            $spells[$i - 1] = array(
                $initData['spellId'.$i],
                $initData['spellTrigger'.$i],
                $initData['spellCharges'.$i] ?? 0,          // can be null from db
                $initData['spellppmRate'.$i],
                $initData['spellCooldown'.$i],
                $initData['spellCategory'.$i],
                $initData['spellCategoryCooldown'.$i]
            );
        }

        $this->spells        = $spells;
        $this->cooldown      = $cooldown;

        $this->resistance    = [null, $initData['resHoly'], $initData['resFire'], $initData['resNature'], $initData['resFrost'], $initData['resShadow'], $initData['resArcane']];

        $this->socketColor   = [$initData['socketColor1'],   $initData['socketColor2'],   $initData['socketColor3']];
        $this->socketContent = [$initData['socketContent1'], $initData['socketContent2'], $initData['socketContent3']];

        // from json to json .. the gentle fuckups of legacy code integration
        $this->itemStats = (new StatsContainer)->fromJson($initData, true);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'icon':                                // fix missing icons
                    $this->$k = $v ?: DEFAULT_ICON;
                    break;
                case 'requiredClass':                       // prepare required classes
                    $this->$k = ($_ = $v & ChrClass::MASK_ALL) == ChrClass::MASK_ALL ? 0 : $_;
                    break;
                case 'requiredRace':                        // prepare required races
                    $this->$k = ($_ = $v & ChrRace::MASK_ALL) == ChrRace::MASK_ALL ? 0 : $_;
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }

        // item is scaling; overwrite other values
        if ($initData['scalingStatDistribution'] > 0 && $initData['scalingStatValue'] > 0)
            [$statTypes, $statValues, $armor, $dmg] = $this->initScalingStats();

        // stats
        $this->statType  = $statTypes  ?? [$initData['statType1'],  $initData['statType2'],  $initData['statType3'],  $initData['statType4'],  $initData['statType5'],  $initData['statType6'],  $initData['statType7'],  $initData['statType8'],  $initData['statType9'],  $initData['statType10']];
        $this->statValue = $statValues ?? [$initData['statValue1'], $initData['statValue2'], $initData['statValue3'], $initData['statValue4'], $initData['statValue5'], $initData['statValue6'], $initData['statValue7'], $initData['statValue8'], $initData['statValue9'], $initData['statValue10']];

        // restore stats from table naming conflict
        $this->block = $initData['tplBlock'];
        $this->armor = $armor ?? $initData['tplArmor'];
        $this->dmg1  = $dmg ?? [$initData['tplDmgMin1'], $initData['tplDmgMax1'], $initData['dmgType1']];
        $this->dmg2  =         [$initData['dmgMin2'],    $initData['dmgMax2'],    $initData['dmgType2']];

        $this->initJsonStats();
    }

    /**
     * @param int $addInfoMask
     * * `0x0001 - LISTVIEWINFO_ITEMEXTRA`: jsonStats (including spells) and subitems parsed
     * * `0x0002 - LISTVIEWINFO_SUBITEMS`: searched by comparison
     * * `0x0004 - LISTVIEWINFO_VENDOR`: costs-obj, when displayed as vendor
     * * `0x0008 - LISTVIEWINFO_GEMS`: gem infos and score
     * * `0x0010 - LISTVIEWINFO_MODEL`: sameModelAs-Tab
     */
    public function getListviewRow(int $addInfoMask = 0x0, ?array $miscData = null) : array
    {
        $data = [];

        // random item is random
        if ($addInfoMask & LISTVIEWINFO_SUBITEMS)
            $this->initSubItems();

        if ($addInfoMask & LISTVIEWINFO_ITEMEXTRA)
        {
            $this->extendJsonStats();
            Util::arraySumByKey($data, $this->itemStats->toJson(Stat::FLAG_ITEM, false));
        }

        $extCosts = [];
        if ($addInfoMask & LISTVIEWINFO_VENDOR)
            $extCosts = $this->getExtendedCost(filter: $miscData);

        $extCostOther = [];
        foreach ($this->json as $k => $v)
            $data[$k] = $v;

        // json vs listview quirk
        $data['name'] = $data['quality'].UIText::unescapeUISequences($this->name, Lang::FMT_RAW);
        unset($data['quality']);

        if (!empty($this->relEnchant) && $this->randomEnchant)
        {
            if (($x = array_search($this->randomEnchant, array_column($this->relEnchant, 'entry'))) !== false)
            {
                $data['rel']   = 'rand='.$this->relEnchant[$x]['ench'];
                $data['name'] .= ' '.$this->relEnchant[$x]['name'];
            }
        }

        if ($addInfoMask & LISTVIEWINFO_ITEMEXTRA)
        {
            if ($_ = intVal(($this->minMoneyLoot + $this->maxMoneyLoot) / 2))
                $data['avgmoney'] = $_;

            if ($_ = $this->repairPrice)
                $data['repaircost'] = $_;
        }

        if ($addInfoMask & (LISTVIEWINFO_ITEMEXTRA | LISTVIEWINFO_GEMS) && $this->weightScore)
            $data['score'] = $this->weightScore;

        if ($addInfoMask & LISTVIEWINFO_GEMS)
        {
            $data['uniqEquip']   = ($this->flags & ITEM_FLAG_UNIQUEEQUIPPED) ? 1 : 0;
            $data['socketLevel'] = 0;        // not used with wotlk
        }

        if ($addInfoMask & LISTVIEWINFO_VENDOR)
        {
            // just use the first results
            // todo (med): dont use first vendor; search for the right one
            if (!empty($extCosts))
            {
                $cost = reset($extCosts);
                foreach ($cost as $itr => $entries)
                {
                    $currency = [];
                    $tokens   = [];
                    $costArr  = [];

                    foreach ($entries as $k => $qty)
                    {
                        if (is_string($k))
                            continue;

                        if ($k > 0)
                            $tokens[] = [$k, $qty];
                        else if ($k < 0)
                            $currency[] = [-$k, $qty];
                    }

                    $costArr['stock']   = $entries['stock'];// display as column in lv
                    $costArr['avail']   = $entries['stock'];// display as number on icon
                    $costArr['cost']    = [empty($entries[0]) ? 0 : $entries[0]];
                    $costArr['restock'] = $entries['restock'];

                    if ($entries['event'])
                        if (Conditions::extendListviewRow($costArr, Conditions::SRC_NONE, $this->id, [Conditions::ACTIVE_EVENT, $entries['event']]))
                            $this->jsGlobals[Type::WORLDEVENT][$entries['event']] = $entries['event'];

                    if ($currency || $tokens)           // fill idx:3 if required
                        $costArr['cost'][] = $currency;

                    if ($tokens)
                        $costArr['cost'][] = $tokens;

                    if (!empty($entries['reqRating']))
                        $costArr['reqarenartng'] = $entries['reqRating'];

                    if ($itr > 0)
                        $extCostOther[] = $costArr;
                    else
                        $data = array_merge($data, $costArr);
                }
            }

            if ($x = $this->buyPrice)
                $data['buyprice'] = $x;

            if ($x = $this->sellPrice)
                $data['sellprice'] = $x;

            if ($x = $this->buyCount)
                $data['stack'] = $x;
        }

        if ($this->class == ITEM_CLASS_GLYPH)
            $data['glyph'] = $this->subSubClass;

        if ($x = $this->requiredSkill)
            $data['reqskill'] = $x;

        if ($x = $this->requiredSkillRank)
            $data['reqskillrank'] = $x;

        if ($x = $this->requiredSpell)
            $data['reqspell'] = $x;

        if ($x = $this->requiredFaction)
            $data['reqfaction'] = $x;

        if ($x = $this->requiredFactionRank)
        {
            $data['reqrep']   = $x;
            $data['standing'] = $x;          // used in /faction item-listing
        }

        if ($x = $this->slots)
            $data['nslots'] = $x;

        $_ = $this->requiredRace;
        if (ChrRace::sideFromMask($_) != SIDE_BOTH)
            $data['reqrace'] = $_;

        if ($_ = $this->requiredClass)
            $data['reqclass'] = $_;          // $data['classes'] ??

        if ($this->flags & ITEM_FLAG_HEROIC)
            $data['heroic'] = true;

        if ($addInfoMask & LISTVIEWINFO_MODEL && $this->displayId)
            $data['displayid'] = $this->displayId;

        if ([$s, $sm] = $this->getSources())
        {
            $data['source'] = $s;
            if ($sm)
                $data['sourcemore'] = $sm;
        }

        if ($this->cooldown)
            $data['cooldown'] = $this->cooldown / 1000;

        foreach ($extCostOther as $itemId => $duplicates)
            foreach ($duplicates as $d)
                $data[] = array_merge($data[$itemId], $d);  // we dont really use keys on data, but this may cause errors in future

        /* even more complicated crap
            modelviewer {type:X, displayid:Y, slot:z} .. not sure, when to set
        */

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF, ?array &$extra = []) : array
    {
        $data = $addMask & GLOBALINFO_RELATED ? $this->jsGlobals : [];

        if ($addMask & GLOBALINFO_SELF)
        {
            $data[Type::ITEM][$this->id] = array(
                'name'    => UIText::unescapeUISequences($this->name, Lang::FMT_RAW),
                'quality' => $this->quality,
                'icon'    => $this->icon
            );

            if ($this->class == ITEM_CLASS_RECIPE)
                $data[Type::ITEM][$this->id]['completion_category'] = $this->class;
            else if ($this->class == ITEM_CLASS_MISC && in_array($this->subClass, [2, 5, -7]))
                $data[Type::ITEM][$this->id]['completion_category'] = $this->class.'-'.$this->subClass;
        }

        if ($addMask & GLOBALINFO_EXTRA)
        {
            $extra = array(
             // 'id'      => $id,
                'tooltip' => $this->renderTooltip(true),
                'spells'  => new \StdClass                  // placeholder for knownSpells
            );
        }

        return $data;
    }

    public function getSourceData() : array
    {
        return array(
            'n'    => $this->name,
            't'    => Type::ITEM,
            'ti'   => $this->id,
            'q'    => $this->quality,
            'icon' => $this->icon
        );
    }

    public function renderTooltip(bool $interactive = false, int $subOf = 0) : ?string
    {
        $_name          = UIText::unescapeUISequences($this->name, Lang::FMT_HTML);
        $_reqLvl        = $this->requiredLevel;
        $_quality       = $this->quality;
        $_flags         = $this->flags;
        $_class         = $this->classBak;
        $_subClass      = $this->subClassBak;
        $_slot          = $this->slot;
        $causesScaling  = false;
        $randPropertyId = $this->enhance['r'] ?? 0;
        $enchantmentId  = $this->enhance['e'] ?? 0;
        $extraSocket    = ($this->enhance['s'] ?? false) && in_array($_slot, [INVTYPE_WRISTS, INVTYPE_WAIST, INVTYPE_HANDS]);
        $gemItemIds     = $this->enhance['g'] ?? [];

        // zero fill empty sockets and fix order
        array_pad($gemItemIds, ($this->json['nsockets'] ?? 0) + ($extraSocket ? 1 : 0), 0);
        $gemItemIds = array_reverse($gemItemIds);

        if ($randPropertyId)
        {
            if ($this->fetchRandomEnchantment())
            {
                $_name      .= ' '.Util::localizedString($this->randEnchantEntry, 'name');
                $randEnchant = '';

                for ($i = 1; $i < 6; $i++)
                {
                    if ($this->randEnchantEntry['enchantId'.$i] <= 0)
                        continue;

                    $eName = EnchantmentEntry::getName($this->randEnchantEntry['enchantId'.$i]);
                    if ($this->randEnchantEntry['allocationPct'.$i] > 0)
                    {
                        $amount = intVal($this->randEnchantEntry['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                        $randEnchant .= '<span>'.str_replace('$i', $amount, $eName).'</span><br />';
                    }
                    else
                        $randEnchant .= '<span>'.$eName.'</span><br />';
                }
            }
            else
                $randPropertyId = 0;
        }

        // IMPORTAT: DO NOT REMOVE THE HTML-COMMENTS! THEY ARE REQUIRED TO UPDATE THE TOOLTIP CLIENTSIDE
        $x = '';

        // upper table: stats
        if (!$subOf)
            $x .= '<table><tr><td>';

        // name; quality
        if ($subOf)
            $x .= '<span class="q'.$_quality.'"><a href="?item='.$this->id.'">'.$_name.'</a></span>';
        else
            $x .= '<b class="q'.$_quality.'">'.$_name.'</b>';

        // heroic tag
        if (($_flags & ITEM_FLAG_HEROIC) && $_quality == ITEM_QUALITY_EPIC)
            $x .= '<br /><span class="q2">'.Lang::item('heroic').'</span>';

        // requires map (todo: reparse :zones for non-conflicting data; generate Link to zone)
        if ($_ = $this->map)
        {
            $map = DB::Aowow()->selectRow('SELECT * FROM ::zones WHERE `mapId` = %i LIMIT 1', $_);
            $x .= '<br /><a href="?zone='.$_.'" class="q1">'.Util::localizedString($map, 'name').'</a>';
        }

        // requires area
        if ($this->area)
        {
            $area = DB::Aowow()->selectRow('SELECT * FROM ::zones WHERE `id` = %i LIMIT 1', $this->area);
            $x .= '<br />'.Util::localizedString($area, 'name');
        }

        // conjured
        if ($_flags & ITEM_FLAG_CONJURED)
            $x .= '<br />'.Lang::item('conjured');

        // bonding
        if ($_flags & ITEM_FLAG_ACCOUNTBOUND)
            $x .= '<br /><!--bo-->'.Lang::item('bonding', 0);
        else if ($this->bonding)
            $x .= '<br /><!--bo-->'.Lang::item('bonding', $this->bonding);

        // unique || unique-equipped || unique-limited
        if ($this->maxCount == 1)
            $x .= '<br />'.Lang::item('unique', 0);
        // not for currency tokens
        else if ($this->maxCount && $this->bagFamily != 8192)
            $x .= '<br />'.Lang::item('unique', 1, [$this->maxCount]);
        else if ($_flags & ITEM_FLAG_UNIQUEEQUIPPED)
            $x .= '<br />'.Lang::item('uniqueEquipped', 0);
        else if ($this->itemLimitCategory)
        {
            $limit = DB::Aowow()->selectRow('SELECT * FROM ::itemlimitcategory WHERE `id` = %i', $this->itemLimitCategory);
            $x .= '<br />'.Lang::item($limit['isGem'] ? 'uniqueEquipped' : 'unique', 2, [Util::localizedString($limit, 'name'), $limit['count']]);
        }

        // required holiday
        if ($eId = $this->eventId)
            if ($hName = DB::Aowow()->selectRow('SELECT h.* FROM ::holidays h JOIN ::events e ON e.`holidayId` = h.`id` WHERE e.`id` = %i', $eId))
                $x .= '<br />'.Lang::game('requires', ['<a href="?event='.$eId.'" class="q1">'.Util::localizedString($hName, 'name').'</a>']);

        // item begins a quest
        if ($this->startQuest)
            $x .= '<br /><a class="q1" href="?quest='.$this->startQuest.'">'.Lang::item('startQuest').'</a>';

        // class + subclass
        $itemclass = [];
        if ($_slot)                                         // yes, slot can occur on random items and is then also displayed
            $itemclass[] = Lang::item('inventoryType', $_slot);

        // subclass (should be based solely on (ItemSubclass.dbc/displayFlags & 0x1) == 0, but functionally results in this block)
        if ($_class == ITEM_CLASS_ARMOR && !in_array($_subClass, [ITEM_SUBCLASS_MISC_ARMOR, ITEM_SUBCLASS_BUCKLER]))
            $itemclass[] = '<!--asc'.$_subClass.'-->'.Lang::item('subClass', $_class, $_subClass);
        else if (($_class == ITEM_CLASS_CONTAINER || $_class == ITEM_CLASS_QUIVER) && $this->slots > 0) // invType ins not displayed for containers for some reason
            $itemclass[0] = Lang::item('containerSlots', [$this->slots, Lang::item('subClass', $_class, $_subClass)]);
        else if (($_class == ITEM_CLASS_WEAPON     && !in_array($_subClass, [ITEM_SUBCLASS_OBSOLETE, ITEM_SUBCLASS_1H_EXOTIC, ITEM_SUBCLASS_2H_EXOTIC, ITEM_SUBCLASS_MISC_WEAPON])) ||
                 ($_class == ITEM_CLASS_AMMUNITION && !in_array($_subClass, [0])) || // wand (obsolete)
                 ($_class == ITEM_CLASS_QUIVER     && !in_array($_subClass, [0, 1]))) // quiver (obsolete) + quiver (obsolete)
              /* ($_class == ITEM_CLASS_GLYPH)) flags demand subclass is shown but in-game they are missing..? */
            $itemclass[] = Lang::item('subClass', $_class, $_subClass);

        if (count($itemclass) == 2)
            $x .= '<table width="100%"><tr><td>'.$itemclass[0].'</td><th>'.$itemclass[1].'</th></tr></table>';
        else if (count($itemclass) == 1)
            $x .= '<br />'.$itemclass[0].'<br />';
        else
            $x .= '<br />';

        // Weapon/Ammunition Stats                          (not limited to weapons (see item:1700))
        $speed  = $this->delay / 1000;
        $sc1    = $this->dmg1[2];
        $sc2    = $this->dmg2[2];
        $dmgmin = $this->dmg1[0] + $this->dmg2[0];
        $dmgmax = $this->dmg1[1] + $this->dmg2[1];
        $dps    = $speed ? ($dmgmin + $dmgmax) / (2 * $speed) : 0;

        if ($_class == ITEM_CLASS_AMMUNITION && $dmgmin && $dmgmax)
        {
            if ($sc1)
                $x .= Lang::item('damage', 'ammo', 1, [($dmgmin + $dmgmax) / 2, Lang::game('sc', $sc1)]).'<br />';
            else
                $x .= Lang::item('damage', 'ammo', 0, [($dmgmin + $dmgmax) / 2]).'<br />';
        }
        else if ($dps)
        {
            if ($this->dmg1[0] == $this->dmg1[1])
                $dmg = Lang::item('damage', 'single', $sc1 ? 1 : 0, [$this->dmg1[0],                 $sc1 ? Lang::game('sc', $sc1) : '']);
            else
                $dmg = Lang::item('damage', 'range',  $sc1 ? 1 : 0, [$this->dmg1[0], $this->dmg1[1], $sc1 ? Lang::game('sc', $sc1) : '']);

            if ($_class == ITEM_CLASS_WEAPON)               // do not use localized format here!
                $x .= '<table width="100%"><tr><td><!--dmg-->'.$dmg.'</td><th>'.Lang::item('speed').' <!--spd-->'.number_format($speed, 2).'</th></tr></table>';
            else
                $x .= '<!--dmg-->'.$dmg.'<br />';

            // secondary damage is set
            if (($this->dmg2[0] || $this->dmg2[1]) && $this->dmg2[0] != $this->dmg2[1])
                $x .= Lang::item('damage', 'range',  $sc2 ? 3 : 2, [$this->dmg2[0], $this->dmg2[1], $sc2 ? Lang::game('sc', $sc2) : '']).'<br />';
            else if ($this->dmg2[0])
                $x .= Lang::item('damage', 'single', $sc2 ? 3 : 2, [$this->dmg2[0],                 $sc2 ? Lang::game('sc', $sc2) : '']).'<br />';

            if ($_class == ITEM_CLASS_WEAPON)
                $x .= '<!--dps-->'.Lang::item('dps', [$dps]).'<br />';

            // display FeralAttackPower if set
            if ($fap = $this->calculateFeralAP())
                $x .= '<span class="c11"><!--fap-->('.$fap.' '.Lang::item('fap').')</span><br />';
        }

        // Armor
        if ($_class == ITEM_CLASS_ARMOR && $this->armorDamageModifier > 0)
        {
            $spanI = 'class="q2"';
            if ($interactive)
                $spanI = 'class="q2 tip" onmouseover="$WH.Tooltip.showAtCursor(event, $WH.sprintf(LANG.tooltip_armorbonus, '.$this->armorDamageModifier.'), 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"';

            $x .= '<span '.$spanI.'><!--addamr'.$this->armorDamageModifier.'--><span>'.Lang::item('armor', [$this->armor]).'</span></span><br />';
        }
        else if ($this->armor)
            $x .= '<span><!--amr-->'.Lang::item('armor', [$this->armor]).'</span><br />';

        // Block (note: block value from field block and from field stats or parsed from itemSpells are displayed independently)
        if ($this->block)
            $x .= '<span>'.Lang::item('block', [$this->block]).'</span><br />';

        // Item is a gem (don't mix with sockets)
        if ($geId = $this->gemEnchantmentId)
        {
            $gemEnch = DB::Aowow()->selectRow('SELECT * FROM ::itemenchantment WHERE `id` = %i', $geId);
            $x .= '<span class="q1"><a href="?enchantment='.$geId.'">'.Util::localizedString($gemEnch, 'name').'</a></span><br />';

            // activation conditions for meta gems
            if (!empty($gemEnch['conditionId']))
                $x .= Game::getEnchantmentCondition($gemEnch['conditionId'], $interactive);
        }

        // Random Enchantment - if random enchantment is set, prepend stats from it
        if ($this->randomEnchant && !$randPropertyId)
            $x .= '<span class="q2">'.Lang::item('randEnchant').'</span><br />';
        else if ($randPropertyId)
            $x .= $randEnchant;

        // itemMods (display stats and save ratings for later use)
        foreach ($this->statType as $j => $type)
        {
            $qty = $this->statValue[$j];

            if (!$qty || $type <= 0)
                continue;

            $statId = Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $type);

            // base stat
            switch ($statId)
            {
                case Stat::MANA:
                case Stat::HEALTH:
                case Stat::AGILITY:
                case Stat::STRENGTH:
                case Stat::INTELLECT:
                case Stat::SPIRIT:
                case Stat::STAMINA:
             // case Stat::ARMOR:                           // unused by 335a client, still set in item_template
             // case Stat::FIRE_RESISTANCE:
             // case Stat::FROST_RESISTANCE:
             // case Stat::HOLY_RESISTANCE:
             // case Stat::SHADOW_RESISTANCE:
             // case Stat::NATURE_RESISTANCE:
             // case Stat::ARCANE_RESISTANCE:
                    $x .= '<span><!--stat'.$statId.'-->'.Lang::item('statType', $type, [ord($qty > 0 ? '+' : '-'), abs($qty)]).'</span><br />';
                    break;
                default:                                    // rating with % for reqLevel
                    $green[] = $this->formatRating($statId, $type, $qty, $interactive, $causesScaling);
            }
        }

        // magic resistances
        foreach (array_filter($this->resistance) as $sc => $amt)
            $x .= sprintf('%+d %s<br />', $amt, Lang::game('resistances', $sc));

        // Enchantment
        if ($enchantmentId && ($eName = EnchantmentEntry::getName($enchantmentId)))
            $x .= '<span class="q2"><!--e-->'.$eName.'</span><br />';
        else                                                // enchantment placeholder
            $x .= '<!--e-->';

        // Sockets w/ Gems
        if ($gemItemIds)
        {
            $gems = DB::Aowow()->selectAssoc(
               'SELECT it.`id` AS ARRAY_KEY, ic.`name` AS "icon", ie.*, it.`gemColorMask` AS "colorMask"
                FROM   ::items it
                JOIN   ::itemenchantment ae ON ie.`id` = it.`gemEnchantmentId`
                JOIN   ::icons ic ON ic.`id` = it.`iconId`
                WHERE  it.`id` IN %in',
                $gemItemIds
            );

            foreach ($gemItemIds as $k => $v)
                if ($v && !in_array($v, array_keys($gems))) // 0 is valid
                    $gemItemIds[$k] = 0;
        }

        $hasMatch = 1;
        // fill native sockets
        foreach (array_filter($this->socketColor) as $j => $color)
        {
            $colorId   = intval(log($color, 2));
            $pop       = array_pop($gemItemIds);
            $col       = $pop ? 1 : 0;
            $hasMatch &= $pop ? (($gems[$pop]['colorMask'] & $color) ? 1 : 0) : 0;
            $icon      = $pop ? sprintf('style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"', Cfg::get('STATIC_URL'), strtolower($gems[$pop]['iconString'])) : null;
            $text      = $pop ? Util::localizedString($gems[$pop], 'name') : Lang::item('socket', $colorId);

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs='.($colorId + 1).';crv=0" class="socket-'.Game::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-'.Game::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }

        // fill extra socket
        if ($extraSocket)
        {
            $pop  = array_pop($gemItemIds);
            $col  = $pop ? 1 : 0;
            $icon = $pop ? sprintf('style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"', Cfg::get('STATIC_URL'), strtolower($gems[$pop]['iconString'])) : null;
            $text = $pop ? Util::localizedString($gems[$pop], 'name') : Lang::item('socket', -1);

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs=5;crv=0" class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }
        else                                                // prismatic socket placeholder
            $x .= '<!--ps-->';

        if ($_ = $this->socketBonus)
        {
            $sbonus = DB::Aowow()->selectRow('SELECT * FROM ::itemenchantment WHERE `id` = %i', $_);
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::item('socketBonus', ['<a href="?enchantment='.$_.'">'.Util::localizedString($sbonus, 'name').'</a>']).'</span><br />';
        }

        // durability
        if ($dur = $this->durability)
            $x .= Lang::item('durability', [$dur]).'<br />';

        // max duration
        if ($dur = $this->duration)
        {
            if ($this->flagsCustom & 0x1)
                $rt = Lang::main('parensFmt', [Lang::formatTime(abs($dur) * 1000, 'item', 'duration'), $interactive ? sprintf(Util::$dfnString, 'LANG.tooltip_realduration', Lang::item('realTime')) : Lang::item('realTime')]);
            else
                $rt = Lang::formatTime(abs($dur) * 1000, 'item', 'duration');

            $x .= $rt.'<br />';
        }

        // glyph type
        if ($_class == ITEM_CLASS_GLYPH && $this->subSubClass)
            $x .= '<span class="q9">'.Lang::item('glyphType', $this->subSubClass).'</span><br />';

        // required classes
        $jsg = [];
        if ($classes = Lang::getClassString($this->requiredClass, $jsg))
        {
            foreach ($jsg as $js)
                $this->jsGlobals[Type::CHR_CLASS][$js] ??= $js;

            $x .= Lang::game('classes').Lang::main('colon').$classes.'<br />';
        }

        // required races
        $jsg = [];
        if ($races = Lang::getRaceString($this->requiredRace, $jsg))
        {
            foreach ($jsg as $js)
                $this->jsGlobals[Type::CHR_RACE][$js] ??= $js;

            $x .= Lang::game('races').Lang::main('colon').$races.'<br />';
        }

        // required honorRank (not used anymore)
        if ($this->requiredHonorRank && ($rank = Lang::exist('game', 'pvpRank', $this->requiredHonorRank)))
            $x .= Lang::game('requires', [implode(' / ', $rank)]).'<br />';

        // required CityRank..?
        // what the f..

        // required level
        if (($_flags & ITEM_FLAG_ACCOUNTBOUND) && $_quality == ITEM_QUALITY_HEIRLOOM)
            $x .= Lang::item('reqLevelRange', [1, MAX_LEVEL, ($interactive ? sprintf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL)]).'<br />';
        else if ($_reqLvl > 1)
            $x .= Lang::item('reqMinLevel', [$_reqLvl]).'<br />';

        trigger_error('item extended cost fail', E_USER_WARNING);
        // required arena team rating / personal rating / todo (low): where is team rating requirement stored?
        // if ($this->getExtendedCost(reqRating: $reqRating, forItem: $this->id) && [$rating, $bracket] = $reqRating)
            // $x .= Lang::item('reqRating', $rating, [$bracket]).'<br />';

        // item level
        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON]))
            $x .= Lang::item('itemLevel', [$this->itemLevel]).'<br />';

        // required skill
        if ($reqSkill = $this->requiredSkill)
        {
            $_ = '<a class="q1" href="?skill='.$reqSkill.'">'.SkillEntry::getName($reqSkill).'</a>';
            if ($this->requiredSkillRank > 0)
                $_ = Lang::main('parensFmt', [$_, $this->requiredSkillRank]);

            $x .= Lang::game('requires', [$_]).'<br />';
        }

        // required spell
        if ($reqSpell = $this->requiredSpell)
            $x .= Lang::game('requires2').' <a class="q1" href="?spell='.$reqSpell.'">'.SpellEntry::getName($reqSpell).'</a><br />';

        // required reputation w/ faction
        if ($reqFac = $this->requiredFaction)
            $x .= Lang::game('requires', ['<a class="q1" href="?faction='.$reqFac.'">'.FactionEntry::getName($reqFac).'</a> - '.Lang::game('rep', $this->requiredFactionRank)]).'<br />';

        // locked or openable
        if ($locks = Lang::getLocks($this->lockId, $arr, true))
            $x .= '<span class="q0">'.Lang::item('locked').'<br />'.implode('<br />', array_map(fn($x) => Lang::game('requires', [$x]), $locks)).'</span><br />';
        else if ($this->flags & ITEM_FLAG_OPENABLE)
            $x .= '<span class="q2">'.Lang::item('openClick').'</span><br />';

        // upper table: done
        if (!$subOf)
            $x .= '</td></tr></table>';

        // spells on item
        if (!$this->canTeachSpell() && $this->spells)
        {
            $itemSpells = new SpellContainer(array(['id', array_column($this->spells, 0)]));
            foreach ($this->spells as $j => [$spellId, $trigger, , $ppm, $cooldown, , $catgCooldown])
            {
                if (!($spellEntry = $itemSpells->getEntry($spellId)))
                    continue;

                [$parsed, $_, $scaling] = $spellEntry->renderText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL);
                if (!$parsed && User::isInGroup(U_GROUP_EMPLOYEE))
                    $parsed = '<span style="opacity:.75">&lt;'.$spellEntry->name.'&gt;</span>';
                else if (!$parsed)
                    continue;

                if ($scaling)
                    $causesScaling = true;

                if ($interactive)
                {
                    $link   = '<a href="?spell='.$spellEntry->id.'">%s</a>';
                    $parsed = preg_replace_callback('/([^;]*)(&nbsp;<small>.*?<\/small>)([^&]*)/i', function($m) use($link) {
                            $m[1] = $m[1] ? sprintf($link, $m[1]) : '';
                            $m[3] = $m[3] ? sprintf($link, $m[3]) : '';
                            return $m[1].$m[2].$m[3];
                        }, $parsed, -1, $nMatches
                    );

                    if (!$nMatches)
                        $parsed = sprintf($link, $parsed);
                }

                $cd    = max($cooldown, $catgCooldown);
                $extra = [];
                if ($cd >= 5000 && $trigger != SPELL_TRIGGER_EQUIP)
                {
                    $pt = DateTime::parse($cd);
                    if (count(array_filter($pt)) == 1)  // simple time: use simple method
                        $extra[] = Lang::formatTime($cd, 'item', 'cooldown');
                    else                                // build block with generic time
                        $extra[] = Lang::item('cooldown', 0, [Lang::formatTime($cd, 'game', 'timeAbbrev', true)]);
                }
                if ($trigger == SPELL_TRIGGER_HIT && $ppm)
                    $extra[] = Lang::spell('ppm', [$ppm]);

                $green[] = Lang::item('trigger', $trigger).$parsed.($extra ? ' '.implode(', ', $extra) : '');
            }
        }

        // lower table (ratings, spells, ect)
        if (!$subOf)
            $x .= '<table><tr><td>';

        if (isset($green))
            foreach ($green as $j => $bonus)
                if ($bonus)
                    $x .= '<span class="q2">'.$bonus.'</span><br />';

        // Item Set
        $pieces  = [];
        if ($this->itemset)
        {
            $condition = [
                ['refSetId', $this->itemset],
             // ['quality',  $this->quality],
                ['minLevel', $this->itemLevel, '<='],
                ['maxLevel', $this->itemLevel, '>=']
            ];

            $itemset = new ItemsetContainer($condition);
            if (!$itemset->error && $itemset->pieceToSet)
            {
                // handle special cases where:
                // > itemset has items of different qualities (handled by not limiting for quality in the initial query)
                // > itemset is virtual and multiple instances have the same itemLevel but not quality (filter below)
                $itemsetEntry = null;
                foreach ($itemset->iterate() as $id => $itemsetEntry)
                {
                    if ($itemsetEntry->quality != $this->quality)
                        continue;

                    $itemsetEntry->pieceToSet = array_filter($itemsetEntry->pieceToSet, fn($x) => $id == $x);
                    break;
                }

                $pieces = DB::Aowow()->selectAssoc(
                   'SELECT   b.`id` AS ARRAY_KEY, b.`name_loc0`, b.`name_loc2`, b.`name_loc3`, b.`name_loc4`, b.`name_loc6`, b.`name_loc8`, GROUP_CONCAT(a.`id` SEPARATOR ":") AS "equiv"
                    FROM     ::items a, ::items b
                    WHERE    a.`slotBak` = b.`slotBak` AND a.`itemset` = b.`itemset` AND b.`id` IN %in
                    GROUP BY b.`id`',
                    array_keys($itemsetEntry->pieceToSet)
                );

                foreach ($pieces as $k => &$p)
                    $p = '<span><!--si'.$p['equiv'].'--><a href="?item='.$k.'">'.Util::localizedString($p, 'name').'</a></span>';

                $xSet = '<br /><span class="q">'.Lang::item('setName', ['<a href="?itemset='.$itemsetEntry->id.'" class="q">'.$itemsetEntry->name.'</a>', 0, count($pieces)]).'</span>';

                if ($skId = $itemsetEntry->skillId)  // bonus requires skill to activate
                {
                    $xSet .= '<br />'.Lang::game('requires', ['<a href="?skills='.$skId.'" class="q1">'.SkillEntry::getName($skId).'</a>']);

                    if ($_ = $itemsetEntry->skillLevel)
                        $xSet = Lang::main('parensFmt', [$xSet, $_]);

                    $xSet .= '<br />';
                }

                // list pieces
                $xSet .= '<div class="q0 indent">'.implode('<br />', $pieces).'</div><br />';

                // get bonuses
                $setSpells = [];
                if ($setSpells = array_filter($itemsetEntry->spells))
                {
                    $boni = new SpellContainer(array(['s.id', $setSpells]));
                    foreach ($setSpells as $idx => $spellId)
                    {
                        if (!($spellEntry = $boni->getEntry($spellId)))
                            continue;

                        [$parsed, , $scaling] = $spellEntry->renderText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL);
                        if ($scaling && $interactive)
                            $causesScaling = true;

                        $setSpells[] = array(
                            'tooltip' => $parsed,
                            'entry'   => $spellId,
                            'bonus'   => $itemsetEntry->boni[$idx]
                        );
                    }
                }

                // sort and list bonuses
                uasort($setSpells, fn(array $a, array $b) => $a['bonus'] <=> $b['bonus']);
                $xSet .= '<span class="q0">';
                foreach ($setSpells as $i => ['tooltip' => $desc, 'entry' => $id, 'bonus' => $bonus])
                {
                    $xSet .= '<span>'.Lang::item('setBonus', [$bonus, '<a href="?spell='.$id.'">'.$desc.'</a>']).'</span>';
                    if (++$i < count($setSpells))
                        $xSet .= '<br />';
                }
                $xSet .= '</span>';
            }
        }

        // recipes, vanity pets, mounts
        if ($this->canTeachSpell())
        {
            $craftSpell = new SpellEntry($this->spells[1][0]); // ...eeehh
            if (!$craftSpell->error)
            {
                $xCraft = '';
                if ($desc = $this->description)
                    $x .= '<span class="q2">'.Lang::item('trigger', SPELL_TRIGGER_USE).' <a href="?spell='.$craftSpell->id.'">'.$desc.'</a></span><br />';

                // recipe handling (some stray Techniques have subclass == 0), place at bottom of tooltipp
                if ($_class == ITEM_CLASS_RECIPE || $this->bagFamily == 16)
                {
                    if ($craftSpell->canCreateItem())
                    {
                        $craftItem  = new ItemEntry($craftSpell->effectCreateItemId[0]);
                        if (!$craftItem->error)
                            if ($itemTT = $craftItem->renderTooltip($interactive, $this->id))
                                $xCraft .= '<div><br />'.$itemTT.'</div>';
                    }

                    if ($_ = array_filter($craftSpell->reagent))
                    {
                        $reagents = new ItemContainer(array(['i.id', $_]));
                        $reqReag  = [];

                        foreach ($craftSpell->reagent as $i => $rId)
                        {
                            if (!$rId || !($qty = $craftSpell->reagentCount[$i]))
                                continue;

                            if (!($reagent = $reagents->getEntry($rId)))
                                continue;

                            $reqReag[] = '<a href="?item='.$reagent->id.'">'.$reagent->name.'</a> ('.$qty.')';
                            $reqReag[] = Lang::main('parensFmt', ['<a href="?item='.$reagent->id.'">'.$reagent->name.'</a>', $qty]);
                        }

                        $xCraft .= '<div class="q1 whtt-reagents"><br />'.Lang::game('requires2').' '.implode(Lang::main('comma'), $reqReag).'</div>';
                    }
                }
            }
        }

        // misc (no idea, how to organize the <br /> better)
        $xMisc = [];

        // itemset: pieces and boni
        if (isset($xSet))
            $xMisc[] = $xSet;

        // funny, yellow text at the bottom, omit if we have a recipe
        if (!$this->description->isEmpty() && !$this->canTeachSpell())
            $xMisc[] = '<span class="q">"'.UIText::format($this->description, Lang::FMT_HTML).'"</span>';

        // readable
        if ($this->pageTextId)
            $xMisc[] = '<span class="q2">'.Lang::item('readClick').'</span>';

        // charges (negative amount indicates item destruction when used up)
        foreach ($this->spells as $i => [, $trigger, $charges, , , , ])
        {
            if (!in_array($trigger, [SPELL_TRIGGER_USE, SPELL_TRIGGER_SOULSTONE, SPELL_TRIGGER_USE_NODELAY, SPELL_TRIGGER_LEARN]) || abs($charges) <= 1)
                continue;

            $xMisc[] = '<span class="q1">'.Lang::item('charges', [abs($charges)]).'</span>';
            break;
        }

        // list required reagents
        if (isset($xCraft))
            $xMisc[] = $xCraft;

        if ($xMisc)
            $x .= implode('<br />', $xMisc);

        if ($sp = $this->sellPrice)
            $x .= '<div class="q1 whtt-sellprice">'.Lang::item('sellPrice').Lang::main('colon').Util::formatMoney($sp).'</div>';

        if (!$subOf)
            $x .= '</td></tr></table>';

        // tooltip scaling
        if (!isset($xCraft))
        {
            $itemId = $subOf ?: $this->id;

            $x .= '<!--?';
            // itemId
            $x .= $itemId;
            // scaleMinLevel
            $x .= ':1';
            // scaleMaxLevel
            $x .= ':' . ($this->scalingDistribution['maxLevel'] ?? ($causesScaling ? MAX_LEVEL : 1));
            // scaleCurLevel
            $x .= ':' . ($this->scalingDistribution['maxLevel'] ?? ($_reqLvl ?: MAX_LEVEL));
            // scaleDist
            if ($this->scalingStatDistribution)
                $x .= ':' . $this->scalingStatDistribution;
            // scaleFlags
            if ($this->scalingStatValue)
                $x .= ':' . $this->scalingStatValue;
            $x .= '-->';
        }

        return $x;
    }

    private function initScalingStats() : ?array
    {
        $this->scalingDistribution ??= DB::Aowow()->selectRow('SELECT * FROM ::scalingstatdistribution WHERE `id` = %i', $this->scalingStatDistribution);

        $types  =
        $values =
        $dmg    = [];
        $armor  = 0;

        if (!$this->scalingDistribution)
            return null;

        // stats and ratings
        for ($i = 1; $i <= 10; $i++)
        {
            if ($this->scalingDistribution['statMod'.$i] <= 0)
            {
                $types[$i]  = 0;
                $values[$i] = 0;
            }
            else
            {
                $types[$i]  = $this->scalingDistribution['statMod'.$i];
                $values[$i] = intVal(($this->getSSDMod('stats') * $this->scalingDistribution['modifier'.$i]) / 10000);
            }
        }

        // apply Spell Power from ScalingStatValue if set
        if ($spellBonus = $this->getSSDMod('spell'))
        {
            $types[10]  = ITEM_MOD_SPELL_POWER;
            $values[10] = $spellBonus;
        }

        // armor: only replace if set
        if ($ssvArmor = $this->getSSDMod('armor'))
            $armor = $ssvArmor;

        // if set dpsMod in ScalingStatValue use it for min/max damage
        // mle: 20% range / rgd: 30% range
        if ($extraDPS = $this->getSSDMod('dps'))            // dmg_x2 not used for heirlooms
        {
            $range   = isset($this->json['rgddps']) ? 0.3 : 0.2;
            $average = $extraDPS * $this->delay / 1000;

            $dmg = [floor((1 - $range) * $average), floor((1 + $range) * $average), SPELL_SCHOOL_NORMAL];
        }

        return [$types, $values, $armor, $dmg];
    }

    private function getSSDMod(string $type) : int
    {
        if (!$this->scalingDistribution)
            return 0;

        $this->scalingValues ??= DB::Aowow()->selectRow('SELECT * FROM ::scalingstatvalues WHERE `id` = %i', $this->scalingDistribution['maxLevel']);

        $mask  = $this->scalingStatValue;
        $mask &= match ($type)
        {
            'stats' => 0x04001F,
            'armor' => 0xF001E0,
            'dps'   => 0x007E00,
            'spell' => 0x008000,
            'fap'   => 0x010000,                            // unused
            default => 0x0
        };

        if ($bits = Util::mask2bits($mask))
            if ($field = Util::$ssdMaskFields[current($bits)])
                return $this->scalingValues[$field] ?? 0;

        return 0;
    }

    private function initJsonStats() : void
    {
        // always use
        $json = array(
            'id'          => $this->id,
            'name'        => $this->name,
            'icon'        => $this->icon,
            'quality'     => ITEM_QUALITY_HEIRLOOM - $this->quality,
            'classs'      => $this->class,
            'subclass'    => $this->subClass,
            'subsubclass' => $this->subSubClass,
            'side'        => $this->flagsExtra & 0x3 ? SIDE_BOTH - ($this->flagsExtra & 0x3) : ChrRace::sideFromMask($this->requiredRace),
            'gearscore'   => 0
        );

        // use non-zero values only
        $nullable = array_filter(array(
            'heroic'      => ($this->flags & ITEM_FLAG_HEROIC) >> 3,
            'slot'        => $this->slot,
            'slotbak'     => $this->slotBak,
            'level'       => $this->itemLevel,
            'reqlevel'    => $this->requiredLevel,
            'displayid'   => $this->displayId,
            'holres'      => $this->resistance[SPELL_SCHOOL_HOLY],
            'firres'      => $this->resistance[SPELL_SCHOOL_FIRE],
            'natres'      => $this->resistance[SPELL_SCHOOL_NATURE],
            'frores'      => $this->resistance[SPELL_SCHOOL_FROST],
            'shares'      => $this->resistance[SPELL_SCHOOL_SHADOW],
            'arcres'      => $this->resistance[SPELL_SCHOOL_ARCANE],
            'armorbonus'  => $this->class != ITEM_CLASS_ARMOR ? 0 : max(0, intVal($this->armorDamageModifier)),
            'armor'       => $this->armor,
            'dura'        => $this->durability,
            'itemset'     => $this->itemset,
            'socket1'     => $this->socketColor[0],
            'socket2'     => $this->socketColor[1],
            'socket3'     => $this->socketColor[2],
            'nsockets'    => count(array_filter($this->socketColor)),
            'socketbonus' => $this->socketBonus,
            'scadist'     => $this->scalingStatDistribution,
            'scaflags'    => $this->scalingStatValue
        ));

        if ($this->class == ITEM_CLASS_AMMUNITION)
            $json['dps'] = round(($this->dmg1[0] + $this->dmg2[0] + $this->dmg1[1] + $this->dmg2[1]) / 2, 2);
        else if ($this->class == ITEM_CLASS_WEAPON)
        {
            $json['dmgtype1'] = $this->dmg1[2];
            $json['dmgmin1']  = $this->dmg1[0] + $this->dmg2[0];
            $json['dmgmax1']  = $this->dmg1[1] + $this->dmg2[1];
            $json['speed']    = round($this->delay / 1000, 2);
            $json['dps']      = $json['speed'] ? round(($json['dmgmin1'] + $json['dmgmax1']) / (2 * $json['speed']), 1) : 0.0;

            if ($this->isRangedWeapon())
            {
                $json['rgddmgmin'] = $json['dmgmin1'];
                $json['rgddmgmax'] = $json['dmgmax1'];
                $json['rgdspeed']  = $json['speed'];
                $json['rgddps']    = $json['dps'];
            }
            else
            {
                $json['mledmgmin'] = $json['dmgmin1'];
                $json['mledmgmax'] = $json['dmgmax1'];
                $json['mlespeed']  = $json['speed'];
                $json['mledps']    = $json['dps'];
            }

            if ($fap = $this->calculateFeralAP())
                $json['feratkpwr'] = $fap;
        }

        if ($this->class == ITEM_CLASS_ARMOR || $this->class == ITEM_CLASS_WEAPON)
            $json['gearscore'] = Util::getEquipmentScore($this->itemLevel, $this->quality, $this->slot, $nullable['nsockets'] ?? 0);
        else if ($this->class == ITEM_CLASS_GEM)
            $json['gearscore'] = Util::getGemScore($nullable['level'], $this->quality, $this->requiredSkill == SKILL_JEWELCRAFTING, $this->id);

        $this->json = $json + $nullable;
    }

    private function calculateFeralAP() : float
    {
        // must be weapon
        if ($this->class != ITEM_CLASS_WEAPON)
            return 0.0;

        // thats fucked up..
        if (!$this->delay)
            return 0.0;

        // must have enough damage
        if (($dps = round((($this->dmg1[0] + $this->dmg2[0] + $this->dmg1[1] + $this->dmg2[1]) / (2 * $this->delay / 1000)) - 54.8)) <= 0)
            return 0.0;

        // test druid usability
        if ($this->subClass == ITEM_SUBCLASS_MISC_WEAPON)
            return $dps * 14;

        if (static::$feralWeaponMask ??= DB::Aowow()->selectCell('SELECT `weaponTypeMask` FROM ::classes WHERE `id` = %i', ChrClass::DRUID->value))
            if ((1 << $this->subClass) & static::$feralWeaponMask)
                return $dps * 14;

        return 0.0;
    }

    // from Trinity
    public function generateEnchSuffixFactor() : float
    {
        $this->randPropPointEntry ??= DB::Aowow()->selectRow('SELECT * FROM ::itemrandomproppoints WHERE `id` = %s', $this->itemLevel);

        if (!$this->randPropPointEntry)
            return 0.0;

        $fieldIdx = match($this->slot)
        {
            INVTYPE_HEAD,
            INVTYPE_BODY,
            INVTYPE_CHEST,
            INVTYPE_LEGS,
            INVTYPE_2HWEAPON,
            INVTYPE_ROBE            => 1,
            INVTYPE_SHOULDERS,
            INVTYPE_WAIST,
            INVTYPE_FEET,
            INVTYPE_HANDS,
            INVTYPE_TRINKET         => 2,
            INVTYPE_NECK,
            INVTYPE_WRISTS,
            INVTYPE_FINGER,
            INVTYPE_SHIELD,
            INVTYPE_CLOAK,
            INVTYPE_HOLDABLE        => 3,
            INVTYPE_WEAPON,
            INVTYPE_WEAPONMAINHAND,
            INVTYPE_WEAPONOFFHAND   => 4,
            INVTYPE_RANGED,
            INVTYPE_THROWN,
            INVTYPE_RANGEDRIGHT     => 5,
            default                 => 0                    // inv types that don`t have points
        };

        if (!$fieldIdx)
            return 0.0;

        // Select rare/epic modifier
        return match($this->quality)
        {
            ITEM_QUALITY_UNCOMMON => $this->randPropPointEntry['uncommon'.$fieldIdx] / 10000,
            ITEM_QUALITY_RARE     => $this->randPropPointEntry['rare'.$fieldIdx]     / 10000,
            ITEM_QUALITY_EPIC     => $this->randPropPointEntry['epic'.$fieldIdx]     / 10000,
            default               => 0.0                    // qualities that don't have random properties
        };
    }

    public function fetchRandomEnchantment() : bool
    {
        // is it available for this item? .. does it even exist?!
        if ($this->randEnchantEntry !== null)
            return !empty($this->randEnchantEntry);

        if (empty($this->enhance['r']) || !$this->randomEnchant)
            return false;

        if (DB::World()->selectCell('SELECT 1 FROM item_enchantment_template WHERE `entry` = %i AND `ench` = %i', abs($this->randomEnchant), abs($this->enhance['r'])))
            if ($_ = DB::Aowow()->selectRow('SELECT * FROM ::itemrandomenchant WHERE `id` = %i', $this->enhance['r']))
                $this->randEnchantEntry = $_;

        return !empty($this->randEnchantEntry);
    }

    private function formatRating(int $statId, int $itemMod, int $qty, bool $interactive = false, bool &$scaling = false) : string
    {
        // clamp level range
        $ssdLvl = $this->scalingDistribution['maxLevel'] ?? 1;
        $reqLvl = $this->requiredLevel > 1 ? $this->requiredLevel : MAX_LEVEL;
        $level  = min(max($reqLvl, $ssdLvl), MAX_LEVEL);

        // unknown rating
        if (!$statId)
        {
            if (User::isInGroup(U_GROUP_EMPLOYEE))
                return Lang::item('statType', count(Lang::item('statType')) - 1, [$itemMod, $qty]);
            else
                return '';
        }

        // level independent Bonus
        if (Stat::isLevelIndependent($statId))
            return Lang::item('trigger', SPELL_TRIGGER_EQUIP).str_replace('%d', '<!--rtg'.$statId.'-->'.$qty, Lang::item('statType', $itemMod));

        // rating-Bonuses
        $scaling = true;

        if ($interactive)
            $js = '&nbsp;<small>('.sprintf(Util::$changeLevelString, Util::setRatingLevel($level, $statId, $qty)).')</small>';
        else
            $js = '&nbsp;<small>('.Util::setRatingLevel($level, $statId, $qty).')</small>';

        return Lang::item('trigger', SPELL_TRIGGER_EQUIP).str_replace('%d', '<!--rtg'.$statId.'-->'.$qty.$js, Lang::item('statType', $itemMod));
    }

    /**
     * only for item xml
     */
    public function getOnUseStats() : ?StatsContainer
    {
        if ($this->class != ITEM_CLASS_CONSUMABLE)
            return null;

        return $this->itemStats;

        // why was i recalculating the stats instead of pulling them from DB..?

        $onUseStats = new StatsContainer();

        if (!($spellIds = array_column(array_filter($this->spells, fn($x) => $x[1] == SPELL_TRIGGER_USE && $x[0] > 0), 0)))
            return null;

        $spells = DB::Aowow()->select(
            'SELECT `id` AS ARRAY_KEY,
                    `effect1Id`, `effect1TriggerSpell`, `effect1AuraId`, `effect1MiscValue`, `effect1BasePoints`, `effect1DieSides`,
                    `effect2Id`, `effect2TriggerSpell`, `effect2AuraId`, `effect2MiscValue`, `effect2BasePoints`, `effect2DieSides`,
                    `effect3Id`, `effect3TriggerSpell`, `effect3AuraId`, `effect3MiscValue`, `effect3BasePoints`, `effect3DieSides`
            FROM   ::spell
            WHERE  `id` IN %in',
            $spellIds
        );

        if (!$spells)
            return null;

        foreach ($spells as $spell)
            $onUseStats->fromSpell($spell);

        return $onUseStats;
    }

    public function isRangedWeapon() : bool
    {
        if ($this->class != ITEM_CLASS_WEAPON)
            return false;

        return in_array($this->subClassBak, [ITEM_SUBCLASS_BOW, ITEM_SUBCLASS_GUN, ITEM_SUBCLASS_THROWN, ITEM_SUBCLASS_CROSSBOW, ITEM_SUBCLASS_WAND]);
    }

    public function isBodyArmor() : bool
    {
        if ($this->class != ITEM_CLASS_ARMOR)
            return false;

        return in_array($this->subClassBak, [ITEM_SUBCLASS_CLOTH_ARMOR, ITEM_SUBCLASS_LEATHER_ARMOR, ITEM_SUBCLASS_MAIL_ARMOR, ITEM_SUBCLASS_PLATE_ARMOR]);
    }

    public function isDisplayable() : bool
    {
        if (!$this->displayId)
            return false;

        return in_array($this->slot, array(
            INVTYPE_HEAD,           INVTYPE_SHOULDERS,      INVTYPE_BODY,           INVTYPE_CHEST,          INVTYPE_WAIST,          INVTYPE_LEGS,           INVTYPE_FEET,           INVTYPE_WRISTS,
            INVTYPE_HANDS,          INVTYPE_WEAPON,         INVTYPE_SHIELD,         INVTYPE_RANGED,         INVTYPE_CLOAK,          INVTYPE_2HWEAPON,       INVTYPE_TABARD,         INVTYPE_ROBE,
            INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPONOFFHAND,  INVTYPE_HOLDABLE,       INVTYPE_THROWN,         INVTYPE_RANGEDRIGHT
        ));
    }

    private function canTeachSpell() : bool
    {
        if (!$this->spells)
            return false;

        if (!([$spellId, , , , , , ] = $this->spells[0]))
            return false;

        if (!in_array($spellId, LEARN_SPELLS))
            return false;

        // needs learnable spell
        if (!$this->spells[1][0])
            return false;

        return true;
    }

    public function getNameWithSuffix() : string
    {
        $out = $this->name;
        if ($this->fetchRandomEnchantment())
            $out .= ' '.Util::localizedString($this->randEnchantEntry, 'name');

        return $out;
    }

    public function getVendorData(int $targetItem = 0) : array
    {
        trigger_error('getVendorData() compatibility placeholder! REPLACE ME!');
        return [];
    }

    public function extendJsonStats() : void
    {
        trigger_error('extendJsonStats() compatibility placeholder! REPLACE ME!');
    }

    public function getExtendedCost(?array $filter = [], int $targetItem = 0, ?array &$reqRating = null) : array
    {
        trigger_error('getExtendedCost() compatibility placeholder! REPLACE ME!');
        return [];
    }

    public function initSubItems() : void
    {
        trigger_error('initSubItems() compatibility placeholder! REPLACE ME!');
    }

    public static function getName(int $id, ?int &$quality = null) : ?LocString
    {
        if (!$id)
            return null;

        if ($n = DB::Aowow()->SelectRow('SELECT `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, `quality` FROM %n WHERE `id` = %i', static::$dataTable, $id))
        {
            $quality = $n['quality'];
            return new LocString($n);
        }
        return null;
    }
}

?>
