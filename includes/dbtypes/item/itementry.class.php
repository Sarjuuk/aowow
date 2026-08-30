<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemEntry extends DBTypeEntry implements ISource, ITooltip
{
    use TrSourceHelper, TrVendorHelper;

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
    public readonly  int            $side;
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
    public readonly ?StatsContainer $itemStats;

    public static  int    $dbType          = Type::ITEM;
    public static  string $brickFile       = 'item';
    public static  string $dataTable       = '::items';
    public         array  $json            = [];
    public        ?array  $subItems        = null;

    // bulk assigned / load on demand
    private static ?int   $feralWeaponMask     = null;      // static, so if any Item needs this it is loaded for all items everywhere at once
    private        ?array $scalingDistribution = null;
    private        ?array $scalingValues       = null;
    private        ?array $randPropPointEntry  = null;

    private         int   $weightScore      = 0;            // only set/queried for when using stat weights in ItemFilter
    private         array $enhance          = [];           // tooltip/name enhancements

    private        ?array $randEnchantEntry = null;

    public const string QUERY_BASE = 'SELECT i.*, i.`block` AS "tplBlock", i.`armor` AS tplArmor, i.`dmgMin1` AS "tplDmgMin1", i.`dmgMax1` AS "tplDmgMax1", i.`id` AS ARRAY_KEY, i.`id` AS "id" FROM ::items i';
    public const array  QUERY_OPTS = array(                 // 3 => Type::ITEM
        'i'   => [['is', 'src', 'ic'], 'o' => 'i.`quality` DESC, i.`itemLevel` DESC'],
        'nml' => ['j' => ['::items_search `nml` ON `nml`.`id` = i.`id` AND nml.`locale` = DB_LOC_I']],
        'ic'  => ['j' => ['::icons        `ic`  ON  `ic`.`id` = `i`.`iconId`', true], 's' => ', ic.`name` AS "icon"'],
        'is'  => ['j' => ['::item_stats   `is`  ON  `is`.`type` = 3 AND `is`.`typeId` = `i`.`id`', true], 's' => ', `is`.*'],
        's'   => ['j' => ['::spell        `s`   ON   `s`.`effect1CreateItemId` = `i`.`id`', true], 'g' => 'i.`id`'],
        'e'   => ['j' => ['::events       `e`   ON   `e`.`id` = `i`.`eventId`', true], 's' => ', e.`holidayId`'],
        'iv'  => ['j' => ['::itemvisuals  `iv`  ON  `iv`.`id` = `i`.`itemVisualId`']],
        'src' => ['j' => ['::source       `src` ON `src`.`type` = 3 AND `src`.`typeId` = `i`.`id`', true], 's' => ', `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`, `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
    );

    /**
     * @param ?array    $extraOpts['enhance']      set by comparison tool or formated external links
     *  * e: int enchantmentId
     *  * s: bool (extraScoket (gloves, belt, bracers))
     *  * g: int[] itemIds - gems
     *  * r: int >0: randomPropId; <0: randomSuffixId
     */
    public function __construct(int|array $initData, array $extraOpts = [])
    {
        parent::__construct($initData, $extraOpts);

        // readdress itemset .. is wrong for virtual sets
        if (isset($extraOpts['pcsToSet'][$this->id]))
            $this->json['itemset'] = $extraOpts['pcsToSet'][$this->id];

        $this->enhance = ($extraOpts['enhance'] ?? []) + ['e' => 0, 's' => false, 'g' => [], 'r' => 0];
    }

    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);            // id

        $this->cuFlags               = $initData['cuFlags'];
        $this->name                  = new LocString($initData, 'name');
        $this->description           = new LocString($initData, 'description');
        $this->class                 = $initData['class'];
        $this->classBak              = $initData['classBak'];
        $this->subClass              = $initData['subClass'];
        $this->subClassBak           = $initData['subClassBak'];
        $this->soundOverrideSubclass = $initData['soundOverrideSubclass'];
        $this->subSubClass           = $initData['subSubClass'];
        $this->quality               = $initData['quality'];
        $this->flags                 = $initData['flags'];
        $this->flagsExtra            = $initData['flagsExtra'];
        $this->buyCount              = $initData['buyCount'];
        $this->buyPrice              = $initData['buyPrice'];
        $this->sellPrice             = $initData['sellPrice'];
        $this->repairPrice           = $initData['repairPrice'];
        $this->slot                  = $initData['slot'];
        $this->slotBak               = $initData['slotBak'];
        $this->itemLevel             = $initData['itemLevel'];
        $this->maxCount              = $initData['maxCount'];
        $this->stackable             = $initData['stackable'];
        $this->slots                 = $initData['slots'];
        $this->bonding               = $initData['bonding'];
        $this->pageTextId            = $initData['pageTextId'];
        $this->languageId            = $initData['languageId'];
        $this->startQuest            = $initData['startQuest'];
        $this->lockId                = $initData['lockId'];
        $this->material              = $initData['material'];
        $this->randomEnchant         = $initData['randomEnchant'];
        $this->itemset               = $initData['itemset'];
        $this->durability            = $initData['durability'];
        $this->bagFamily             = $initData['bagFamily'];
        $this->totemCategory         = $initData['totemCategory'];
        $this->duration              = $initData['duration'];
        $this->itemLimitCategory     = $initData['itemLimitCategory'];
        $this->scriptName            = $initData['scriptName'];
        $this->foodType              = $initData['foodType'];
        $this->flagsCustom           = $initData['flagsCustom'];

        $this->side = ($fe = $initData['flagsExtra'] & 0x3) ? SIDE_BOTH - $fe : ChrRace::sideFromMask($initData['requiredRace']);

        $this->disenchantId = $initData['disenchantId'];
        $this->minMoneyLoot = $initData['minMoneyLoot'];
        $this->maxMoneyLoot = $initData['maxMoneyLoot'];

        $this->iconId           = $initData['iconId'];
        $this->icon             = $initData['icon'] ?: DEFAULT_ICON;
        $this->displayId        = $initData['displayId'];
        $this->model            = $initData['model'];
        $this->spellVisualId    = $initData['spellVisualId'];
        $this->pickUpSoundId    = $initData['pickUpSoundId'];
        $this->dropDownSoundId  = $initData['dropDownSoundId'];
        $this->sheatheSoundId   = $initData['sheatheSoundId'];
        $this->unsheatheSoundId = $initData['unsheatheSoundId'];

        $this->requiredClass           = ($_ = $initData['requiredClass'] & ChrClass::MASK_ALL) == ChrClass::MASK_ALL ? 0 : $_;
        $this->requiredRace            = ($_ = $initData['requiredRace']  & ChrRace::MASK_ALL)  == ChrRace::MASK_ALL  ? 0 : $_;
        $this->requiredLevel           = $initData['requiredLevel'];
        $this->requiredSkill           = $initData['requiredSkill'];
        $this->requiredSkillRank       = $initData['requiredSkillRank'];
        $this->requiredSpell           = $initData['requiredSpell'];
        $this->requiredHonorRank       = $initData['requiredHonorRank'];
        $this->requiredCityRank        = $initData['requiredCityRank'];
        $this->requiredFaction         = $initData['requiredFaction'];
        $this->requiredFactionRank     = $initData['requiredFactionRank'];
        $this->requiredDisenchantSkill = $initData['requiredDisenchantSkill'];
        $this->area                    = $initData['area'];
        $this->map                     = $initData['map'];
        $this->eventId                 = $initData['eventId'];

        $this->socketColor      = [$initData['socketColor1'],   $initData['socketColor2'],   $initData['socketColor3']];
        $this->socketContent    = [$initData['socketContent1'], $initData['socketContent2'], $initData['socketContent3']];
        $this->socketBonus      = $initData['socketBonus'];
        $this->gemColorMask     = $initData['gemColorMask'];
        $this->gemEnchantmentId = $initData['gemEnchantmentId'];

        $this->scalingStatDistribution = $initData['scalingStatDistribution'];
        $this->scalingStatValue        = $initData['scalingStatValue'];

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

        $this->spells              = $spells;
        $this->cooldown            = $cooldown;
        $this->delay               = $initData['delay'];
        $this->armorDamageModifier = $initData['armorDamageModifier'];
        $this->resistance          = [null, $initData['resHoly'], $initData['resFire'], $initData['resNature'], $initData['resFrost'], $initData['resShadow'], $initData['resArcane']];
        $this->ammoType            = $initData['ammoType'];
        $this->rangedModRange      = $initData['rangedModRange'];

        if (isset($opts['skipStats']))
            $this->itemStats = null;
        else
            $this->itemStats = (new StatsContainer)->fromJson($initData, true);

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

        $this->initSources($initData);

        $this->initJsonStats();

        return true;
    }

    /**
     * @param int $addInfoMask
     * * `0x0001 - LISTVIEWINFO_ITEMEXTRA`: jsonStats (including spells) and subitems parsed
     * * `0x0002 - LISTVIEWINFO_SUBITEMS`: searched by comparison tool
     * * `0x0004 - LISTVIEWINFO_VENDOR`: costs-obj, when displayed as vendor inventory
     * * `0x0008 - LISTVIEWINFO_GEMS`: gem infos and gearscore
     * * `0x0010 - LISTVIEWINFO_MODEL`: sameModelAs-Tab
     */
    public function getListviewRow(int $addInfoMask = 0x0, ?array $miscData = null) : array
    {
        $data = [];

        if ($this->itemStats && ($addInfoMask & LISTVIEWINFO_ITEMEXTRA))
            Util::arraySumByKey($data, $this->itemStats->toJson(Stat::FLAG_ITEM, false));

        foreach ($this->json as $k => $v)
            $data[$k] = $v;

        // json vs listview quirk
        $data['name'] = $data['quality'].UIText::unescapeUISequences($this->name, Lang::FMT_RAW);
        unset($data['quality']);

        if (!empty($miscData['relEnchant']) && $this->randomEnchant)
        {
            if (($x = array_search($this->randomEnchant, array_column($miscData['relEnchant'], 'entry'))) !== false)
            {
                $data['rel']   = 'rand='.$miscData['relEnchant'][$x]['ench'];
                $data['name'] .= ' '.$miscData['relEnchant'][$x]['name'];
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
            $data['uniqEquip']   = ($this->flags & ITEM_FLAG_UNIQUE_EQUIPPABLE) ? 1 : 0;
            $data['socketLevel'] = 0;                       // required item level; not used with 3.3.5a?
        }

        $extCostOther = [];
        if ($addInfoMask & LISTVIEWINFO_VENDOR)
        {
            if ($vendors = $this->getExtendedCost($this->id, filter: $miscData))
            {
                // just use the first vendor (may have already been filtered via $miscData)
                foreach (reset($vendors) as $itr => $entry)
                {
                    $currency = [];
                    $tokens   = [];
                    $costArr  = [];

                    foreach ($entry as $k => $qty)
                    {
                        if (is_string($k))
                            continue;

                        if ($k > 0)
                            $tokens[] = [$k, $qty];
                        else if ($k < 0)
                            $currency[] = [-$k, $qty];
                    }

                    $costArr['stock']   = $entry['stock'];  // display as column in lv
                    $costArr['avail']   = $entry['stock'];  // display as number on icon
                    $costArr['cost']    = [$entry[0] ?? 0];
                    $costArr['restock'] = $entry['restock'];

                    if ($entry['event'])
                        if (Conditions::extendListviewRow($costArr, Conditions::SRC_NONE, $this->id, [Conditions::ACTIVE_EVENT, $entry['event']]))
                            $this->jsGlobals[Type::WORLDEVENT][$entry['event']] = $entry['event'];

                    if ($currency || $tokens)           // fill idx:3 if required
                        $costArr['cost'][] = $currency;

                    if ($tokens)
                        $costArr['cost'][] = $tokens;

                    if (!empty($entry['reqRating']))
                        $costArr['reqarenartng'] = $entry['reqRating'];

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

        if ($this->flags & ITEM_FLAG_HEROIC_TOOLTIP)
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

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add item to @return
     *  - GLOBALINFO_RELATED - add required races, classes and worldevents to @return
     *  - GLOBALINFO_EXTRA - writes tooltip and modifying spells to $extra
     * @param ?array{?string, \StdClass} $extra will contain item tooltip if enabled
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF, ?array &$extra = []) : array
    {
        $data[self::$dbType][$this->id] = array(
            'name'    => UIText::unescapeUISequences($this->name, Lang::FMT_RAW),
            'quality' => $this->quality,
            'icon'    => $this->icon
        );

        if ($this->class == ITEM_CLASS_RECIPE)
            $data[self::$dbType][$this->id]['completion_category'] = $this->class;
        else if ($this->class == ITEM_CLASS_MISC && in_array($this->subClass, [2, 5, -7]))
            $data[self::$dbType][$this->id]['completion_category'] = $this->class.'-'.$this->subClass;

        if ($addMask & GLOBALINFO_RELATED)
            $data += $this->jsGlobals;

        if ($addMask & GLOBALINFO_EXTRA)
        {
            $extra = [self::$dbType, $this->id, array(
             // 'id'      => $id,
                'tooltip' => $this->renderTooltip(true),
                'spells'  => new \StdClass                  // placeholder for knownSpells
            )];
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

    /**
     * @param   bool    $interactive  set to place javascript/anchors to manipulate level and ratings or link to filters (static tooltips vs popup tooltip)
     * @param   int     $subOf        tabled layout doesn't work if used as sub-tooltip in other item or spell tooltips; use line-break instead
     * @param  ?array   $scaleInfo    if $subOf, scaling info passed back up to parent tooltip
     * @return ?string  html rendered tooltip or null on error
     */
    public function renderTooltip(bool $interactive = false, int $subOf = 0, ?array &$scaleInfo = []) : ?string
    {
        $_name          = UIText::unescapeUISequences($this->getNameWithSuffix(), Lang::FMT_HTML);
        $_reqLvl        = $this->requiredLevel;
        $_quality       = $this->quality;
        $_flags         = $this->flags;
        $_class         = $this->classBak;
        $_subClass      = $this->subClassBak;
        $_slot          = $this->slot;
        $_itemLevel     = $this->itemLevel;

        $causesScaling  = false;

        ########################
        # prepare enhancements #
        ########################

        $extraSocket   = $this->enhance['s'] && in_array($_slot, [INVTYPE_WRISTS, INVTYPE_WAIST, INVTYPE_HANDS]);
        $enchantment   = $this->enhance['e'] ? new EnchantmentEntry($this->enhance['e']) : null;
        $sockGems      = [];
        $randEnchant   = '';

        if ($this->setRandomEnchantment())                  // applies $this->enhance['r']
        {
            for ($i = 1; $i < 6; $i++)
            {
                if ($this->randEnchantEntry['enchantId'.$i] <= 0)
                    continue;

                if ($this->randEnchantEntry['allocationPct'.$i] > 0)
                {
                    $amount = intVal($this->randEnchantEntry['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                    $randEnchant .= '<span>'.str_replace('$i', $amount, Util::localizedString($this->randEnchantEntry, 'name')).'</span><br />';
                }
                else
                    $randEnchant .= '<span>'.Util::localizedString($this->randEnchantEntry, 'name').'</span><br />';
            }
        }

        if ($this->enhance['g'])
            $sockGems = DB::Aowow()->selectAssoc(
               'SELECT it.`id` AS ARRAY_KEY, ic.`name` AS "iconString", ae.*, it.`gemColorMask` AS "colorMask"
                FROM   ::items it
                JOIN   ::itemenchantment ae ON ae.`id` = it.`gemEnchantmentId`
                JOIN   ::icons ic ON ic.`id` = it.`iconId`
                WHERE  it.`id` IN %in',
                $this->enhance['g']
            ) ?: [];


        ##############
        # build HTML #
        ##############

        // IMPORTAT: DO NOT REMOVE THE HTML-COMMENTS! THEY ARE REQUIRED TO UPDATE THE TOOLTIP CLIENTSIDE
        $x = '';

        // upper table: stats
        if (!$subOf)
            $x .= '<table><tr><td>';


        ####################
        # AddItemNameLines #
        ####################

        # Name #

        if ($subOf)
            $x .= '<span class="q'.$_quality.'"><a href="?item='.$this->id.'">'.$_name.'</a></span><br />';
        else
            $x .= '<b class="q'.$_quality.'">'.$_name.'</b><br />';

        # Heroic #

        if (($_flags & ITEM_FLAG_HEROIC_TOOLTIP) && $_quality == ITEM_QUALITY_EPIC)
            $x .= '<br /><span class="q2">'.Lang::item('heroic').'</span>';

        # Petition #

        if ($_flags & ITEM_FLAG_PETITION)
        {
            // AddItemPetitionLines
            // $x .= sprintf(PETITION_TITLE or GUILD_CHARTER_TITLE, petition->m_title) . \n
            // $x .= sprintf(PETITION_CREATOR or GUILD_CHARTER_CREATOR, owner->m_name) . \n
            // if (nSignatures)
            //     $x .= sprintf(PETITION_NUM_SIGNATURES, nSignatures) . \n

            $x .= '<span class="q2">'.Lang::item('signClick').'</span><br />';
         }


        ####################
        # AddItemBindLines #
        ####################

        # Required Area #

        if ($this->area)
            $x .= ZoneEntry::makeLink($this->area, cssClass: 'q1').'<br />';

        # Required Map #

        // note: 0 is eastern kingdoms but also 'empty db field'; luckily only northrend is in use
        if ($this->map && ($area = Lang::zoneFromMap($this->map)))
            $x .= '<span class="q1">'.$area.'</span><br />';

        # Conjured #

        if ($_flags & ITEM_FLAG_CONJURED)
            $x .= Lang::item('conjured').'<br />';

        # Bonding #

        if ($_class != ITEM_CLASS_MONEY)
        {
            if ($_flags & ITEM_FLAG_IS_BOUND_TO_ACCOUNT)
                $x .= '<!--bo-->'.Lang::item('bonding', 0).'<br />';
            else if ($this->bonding)
                $x .= '<!--bo-->'.Lang::item('bonding', $this->bonding).'<br />';

        # Item Limit #

            if ($_flags & ITEM_FLAG_UNIQUE_EQUIPPABLE)
                $x .= Lang::item('uniqueEquipped', 0).'<br />';
            else if ($this->maxCount)
                $x .= Lang::item('unique', (int)($this->maxCount > 1), [$this->maxCount]).'<br />';
            else if ($this->itemLimitCategory)
                if ($limit = DB::Aowow()->selectRow('SELECT * FROM ::itemlimitcategory WHERE `id` = %i', $this->itemLimitCategory))
                    $x .= Lang::item($limit['isGem'] ? 'uniqueEquipped' : 'unique', 2, [Util::localizedString($limit, 'name'), $limit['count']]).'<br />';
        }

        # Start Quest #

        if ($this->startQuest)
            $x .= '<a class="q1" href="?quest='.$this->startQuest.'">'.Lang::item('startQuest').'</a><br />';


        ####################
        # AddItemLockLines #
        ####################

        $_ = $encrypted = null;
        if ($locks = Lang::getLocks($this->lockId, $_, $encrypted, true))
        {
            $x .= '<span class="q0">'.Lang::item($encrypted ? 'encrypted' : 'locked');

            foreach ($locks as [$type, , $str, $miscInfo])
                $x .= '<br />'.match($type)
                {
                    LOCK_TYPE_ITEM  => Lang::item('lockedByItem',  [$str]),
                    LOCK_TYPE_SPELL => Lang::item('lockedBySpell', [$str]),
                    LOCK_TYPE_SKILL => Lang::item('reqSkillValue', [$str, $miscInfo ?: ($_itemLevel * 5)])
                };

            $x .= '</span><br />';
        }

        // if type == Type:SPELL && miscInfo; export them as reqgents
        // never seen that case in-game..?


        ####################
        # AddItemTypeLines #
        ####################

        // emulates flag from ItemSubclass.dbc
        static $displayFlags = array(
            ITEM_CLASS_CONTAINER  => [0, 1, 2, 3, 4, 5, 6, 7, 8],
            ITEM_CLASS_WEAPON     => [0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 13, 15, 16, 17, 18, 19, 20],
            ITEM_CLASS_ARMOR      => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            ITEM_CLASS_AMMUNITION => [1, 2, 3, 4],
            ITEM_CLASS_QUIVER     => [2, 3],
            ITEM_CLASS_MISC       => [5],
            ITEM_CLASS_GLYPH      => [1, 2, 3, 4, 5, 6, 7, 8, 9, 11],
        );

        if ($_slot == INVTYPE_BAG)
            $x .= Lang::item('containerSlots', [$this->slots, Lang::item('subClass', $_class, 1, $_subClass)]).'<br />';
        else if ($_class == ITEM_CLASS_GLYPH)
        {
            if ($gt = $this->subSubClass)
                $x .= '<span class="q9">'.Lang::item('glyphType', $gt).'</span><br />';
        }
        else
        {
            $leftStr = $rightStr = '';
            if ($_slot != INVTYPE_CLOAK && isset($displayFlags[$_class]) && in_array($_subClass, $displayFlags[$_class]))
                $rightStr = Lang::item('subClass', $_class, 1, $_subClass);

            if ($_class != ITEM_CLASS_AMMUNITION)
                $leftStr = Lang::exist('item', 'inventoryType', $_slot);
            else
                $leftStr = Lang::item('subClass', $_class, 0);

            if ($_class == ITEM_CLASS_ARMOR && !in_array($_subClass, [ITEM_SUBCLASS_MISC_ARMOR, ITEM_SUBCLASS_BUCKLER]))
                $rightStr = '<!--asc'.$_subClass.'-->'.$rightStr;

            if ($leftStr && $rightStr)
                $x .= '<table width="100%"><tr><td>'.$leftStr.'</td><th>'.$rightStr.'</th></tr></table>';
            else if ($leftStr)
                $x .= $leftStr.'<br />';
        }


        ######################
        # AddItemDamageLines #
        ######################

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
                $x .= '<table width="100%"><tr><td><!--dmg-->'.$dmg.'</td><th>'.Lang::item('speed').' <!--spd-->'.sprintf('%.2f', $speed).'</th></tr></table>';
            else
                $x .= '<!--dmg-->'.$dmg.'<br />';

            // secondary damage is set
            if (($this->dmg2[0] || $this->dmg2[1]) && $this->dmg2[0] != $this->dmg2[1])
                $x .= Lang::item('damage', 'range',  $sc2 ? 3 : 2, [$this->dmg2[0], $this->dmg2[1], $sc2 ? Lang::game('sc', $sc2) : '']).'<br />';
            else if ($this->dmg2[0])
                $x .= Lang::item('damage', 'single', $sc2 ? 3 : 2, [$this->dmg2[0],                 $sc2 ? Lang::game('sc', $sc2) : '']).'<br />';

            if ($_class == ITEM_CLASS_WEAPON)
                $x .= '<!--dps-->'.Lang::item('dps', [$dps]).'<br />';

            // old non-standard display: WH ca. 2010
            // if ($fap = $this->calculateFeralAP())
            //     $x .= '<span class="c11"><!--fap-->('.$fap.' '.Lang::item('fap').')</span><br />';
        }


        ############################
        # AddItemArmorAndStatLines #
        ############################

        if ($this->armor)
        {
            if ($this->armorDamageModifier > 0 && $interactive)
                $wrapper = '<span class="q2 tip" onmouseover="$WH.Tooltip.showAtCursor(event, $WH.sprintf(LANG.tooltip_armorbonus, '.$this->armorDamageModifier.'), 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><!--addamr'.$this->armorDamageModifier.'-->%s</span>';
            else if ($this->armorDamageModifier > 0)
                $wrapper = '<span class="q2"><!--addamr'.$this->armorDamageModifier.'-->%s</span>';
            else
                $wrapper = "%s";

            $x .= sprintf($wrapper, '<span><!--amr-->'.Lang::item('armor', [$this->armor]).'</span>').'<br />';
        }

        # Shield Block #

        // note: block value from field block and from field stats or parsed from itemSpells are displayed independently
        if ($this->block)
            $x .= '<span>'.Lang::item('block', [$this->block]).'</span><br />';

        # Primary Stats #

        $secondaryStats = [];

        foreach ($this->statType as $j => $type)
        {
            $qty = $this->statValue[$j];

            if (!$qty || $type <= 0)
                continue;

            switch ($statId = Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $type))
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
                default:                                    // rating with % for reqLevel - store for later
                    $secondaryStats[] = $this->formatRating($statId, $type, $qty, $interactive, $causesScaling);
            }
        }

        # Magic Resistances #

        $allEqual = count(array_unique($this->resistance)) === 1;

        // note that the lua strings allows for negative resistances while even the client stores them as uint32[]
        if ($allEqual && $this->resistance[0])
            $x .= Lang::item('resistAll', [ord('+'), $this->resistance[0]]).'<br />';
        else if (!$allEqual)
            foreach (array_filter($this->resistance) as $sc => $amt)
                $x .= Lang::item('resistSingle', [ord('+'), $amt, Lang::game('sc', $sc)]).'<br />';


        ######################
        # AddItemSocketLines #
        ######################

        # Perm. Enchantment #

        if ($enchantment && !$enchantment->error)
            $x .= '<span class="q2"><!--e-->'.$enchantment->name.'</span><br />';
        else
            $x .= '<!--e-->';                               // enchantment placeholder

        # Temp. Enchantment #

        // not displayed

        # Socket Enchantments #

        // zero fill empty sockets
        $sockCount  = $extraSocket ? 1 : 0;
        $sockCount += ($this->json['nsockets'] ?? 0);
        $sockEnh    = array_pad($this->enhance['g'], -$sockCount, 0);

        $hasMatch = 1;
        // fill native sockets
        foreach (array_filter($this->socketColor) as $j => $color)
        {
            $colorId   = intval(log($color, 2));

            $pop       = array_shift($sockEnh);
            $col       = $pop ? 1 : 0;
            $hasMatch &= $pop ? (($sockGems[$pop]['colorMask'] & $color) ? 1 : 0) : 0;
            $icon      = $pop ? sprintf('style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"', Cfg::get('STATIC_URL'), strtolower($sockGems[$pop]['iconString'])) : null;
            $text      = $pop ? Util::localizedString($sockGems[$pop], 'name') : Lang::item('socket', $colorId);

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs='.($colorId + 1).';crv=0" class="socket-'.Game::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-'.Game::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }

        // fill extra socket
        if ($extraSocket)
        {
            $pop  = array_shift($sockEnh);
            $col  = $pop ? 1 : 0;
            $icon = $pop ? sprintf('style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"', Cfg::get('STATIC_URL'), strtolower($sockGems[$pop]['icon'])) : null;
            $text = $pop ? Util::localizedString($sockGems[$pop], 'name') : Lang::item('socket', -1);

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs=5;crv=0" class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }
        else
            $x .= '<!--ps-->';                              // prismatic socket placeholder

        if ($this->socketBonus && ($sb = EnchantmentEntry::makeLink($this->socketBonus)))
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::item('socketBonus', [$sb]).'</span><br />';

        # Random Enchantment #

        // if random enchantment is set, prepend stats from it
        if ($this->randomEnchant && !$randEnchant)
            $x .= '<span class="q2">'.Lang::item('randEnchant').'</span><br />';
        else if ($randEnchant)
            $x .= $randEnchant.'<br />';


        ###################
        # AddItemGemLines #
        ###################

        if ($geId = $this->gemEnchantmentId)
        {
            $gemEnch = DB::Aowow()->selectRow('SELECT * FROM ::itemenchantment WHERE `id` = %i', $geId);
            $x .= '<span class="q1"><a href="?enchantment='.$geId.'">'.Util::localizedString($gemEnch, 'name').'</a></span><br />';

            // activation conditions for meta gems
            if (!empty($gemEnch['conditionId']))
                $x .= Game::getEnchantmentCondition($gemEnch['conditionId'], $interactive).'<br />';
        }


        ##########################
        # AddItemDurabilityLines #
        ##########################

        # Durability #

        if ($dur = $this->durability)
            $x .= Lang::item('durability', [$dur, $dur]).'<br />';

        # Duration #

        if ($dur = $this->duration)
        {
            if ($this->flagsCustom & 0x1)
                $rt = Lang::main('parensFmt', [Lang::formatTime(abs($dur) * 1000, 'item', 'duration'), $interactive ? sprintf(Util::$dfnString, 'LANG.tooltip_realduration', Lang::item('realTime')) : Lang::item('realTime')]);
            else
                $rt = Lang::formatTime(abs($dur) * 1000, 'item', 'duration');

            $x .= $rt.'<br />';
        }

        # Required Holiday #

        if ($this->eventId)
            $x .= Lang::item('reqSkill', [WorldeventEntry::makeLink($this->eventId, cssClass: 'q1')]).'<br />';


        #########################
        # AddItemRaceClassLines #
        #########################

        # Races Allowed #

        $jsg = [];
        if ($races = Lang::getRaceString($this->requiredRace, $jsg))
        {
            foreach ($jsg as $js)
                $this->jsGlobals[Type::CHR_RACE][$js] ??= $js;

            // note: combining races into a faction is non-standard
            $x .= Lang::game('races').Lang::main('colon').$races.'<br />';
        }

        # Classes Allowed #

        $jsg = [];
        if ($classes = Lang::getClassString($this->requiredClass, $jsg))
        {
            foreach ($jsg as $js)
                $this->jsGlobals[Type::CHR_CLASS][$js] ??= $js;

            $x .= Lang::game('classes').Lang::main('colon').$classes.'<br />';
        }

        # Race Class Only #

        // todo: find out how this is triggered
        // in theory RACE_CLASS_ONLY gets send when 0 or 1 requiredRaces and 0 or 1 requiredClasss are set


        #####################
        # AddItemLevelLines #
        #####################

        # Required Level #

        if (($_flags & ITEM_FLAG_IS_BOUND_TO_ACCOUNT) && $_quality == ITEM_QUALITY_HEIRLOOM)
            $x .= Lang::item('reqLevelRange', [1, MAX_LEVEL, ($interactive ? sprintf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL)]).'<br />';
        else if ($_reqLvl > 1)
            $x .= Lang::item('reqMinLevel', [$_reqLvl]).'<br />';

        # Item Level #

        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_REAGENT, ITEM_CLASS_AMMUNITION]))
            $x .= Lang::item('itemLevel', [$_itemLevel]).'<br />';


        ###########################
        # AddItemRequirementLines #
        ###########################

        # Required Skill #

        if ($this->requiredSkill)
        {
            // prospectable/millable lines get added if player has the skill
            if (!($_flags & (ITEM_FLAG_IS_PROSPECTABLE | ITEM_FLAG_IS_MILLABLE)))
            {
                $link = SkillEntry::makeLink($this->requiredSkill, cssClass: 'q1');
                if ($this->requiredSkillRank > 0)
                    $x .= Lang::item('reqSkillValue', [$link, $this->requiredSkillRank]).'<br />';
                else
                    $x .= Lang::item('reqSkill', [$link]).'<br />';
            }
        }

        # Required Spell #

        if ($this->requiredSpell)
            $x .= Lang::item('reqSkill', [SpellEntry::makeLink($this->requiredSpell, cssClass: 'q1')]).'<br />';

        # Required Honor Rank #

        // not used anymore
        if (($this->requiredHonorRank) && is_array($pvpRank = Lang::game('pvpRank', $this->requiredHonorRank)))
            $x .= Lang::item('reqSkill', [implode(' / ', $pvpRank)]).'<br />';

        # Required Arena Rating #

        $reqRating = null;
        if ($this->getExtendedCost($this->id, reqRating: $reqRating) && ([$rating, $bracket] = $reqRating) && $rating)
            $x .= Lang::item('reqRating', $bracket, [$rating]).'<br />';

        # Item Purchase Group #

        // not used -  There is only one purchasegroup and it's from a past arena season.

        # Required City Rank #

        // not used anymore
        if (($medal = $this->requiredCityRank))
            $x .= Lang::item('reqSkill', [$medal]).'<br />';

        # Required Reputation Faction #

        if ($reqFac = $this->requiredFaction)
            $x .= Lang::item('reqReputation', ['<a class="q1" href="?faction='.$reqFac.'">'.FactionEntry::getName($reqFac).'</a>', Lang::game('rep', $this->requiredFactionRank)]).'<br />';


        // upper table: done
        if (!$subOf)                                        // replace last <br /> if using table layout
            $x = substr_replace($x, '</td></tr></table><table><tr><td>', -6);
        // lower table (ratings, spells, ect)


        #########################
        # AddItemSpellStatLines #
        #########################

        # FERAL_DRUID_ITEM_AP #

        if ($fap = $this->calculateFeralAP())
            $x .= '<span class="q2">'.Lang::item('feralItemAP', [$fap]).'</span><br />';

        # Secondary Stats #

        foreach ($secondaryStats as $stat)
            $x .= '<span class="q2">'.$stat.'</span><br />';

        # Spellpower on scaling weapon #

        // handled separately by client. We put it on stat idx 10 (last) which achieves the same order.


        ############################
        # AddItemSpellTriggerLines #
        ############################

        $isRecipe = false;

        if ($this->spells && !($itemSpells = new SpellContainer(array(['id', array_column($this->spells, 0)])))->error)
        {
            foreach ($this->spells as $j => [$spellId, $trigger, $charges, $ppm, $cooldown, , $catgCooldown])
            {
                if (!($spellEntry = $itemSpells->getEntry($spellId)))
                    continue;

                $createSpell = null;
                $createsItem = false;
                $scaling     = false;

                if ($trigger == SPELL_TRIGGER_LEARN)
                {
                    $text        = UIText::format($this->description, Lang::FMT_HTML);
                    $isRecipe    = true;
                    $createSpell = $spellEntry;
                }
                else
                {
                    [$text, , $scaling] = $spellEntry->renderText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL);
                    if (!$text && !in_array($spellId, LEARN_SPELLS) && User::isInGroup(U_GROUP_EMPLOYEE))
                        $text = '<span style="opacity:.75">&lt;'.$spellEntry->name.'&gt;</span>';
                    else if (!$text)
                        continue;

                    if ($spellEntry->effectId[0] == SPELL_EFFECT_LEARN_SPELL)
                        $createSpell = new SpellEntry($spellEntry->effectTriggerSpell[0]);
                }

                if ($createSpell && !$createSpell->error && ($createSpell->effectId[0] == SPELL_EFFECT_CREATE_ITEM || $createSpell->effectId[0] == SPELL_EFFECT_CREATE_ITEM_2 || $createSpell->effectId[0] == SPELL_EFFECT_CREATE_RANDOM_ITEM) && $createSpell->effectCreateItemId[0])
                    $createsItem = true;

                if ($scaling)
                    $causesScaling = true;

                if ($interactive && $text)
                {
                    $link = '<a href="?spell='.$spellEntry->id.'">%s</a>';
                    $text = preg_replace_callback('/([^;]*)(&nbsp;<small>.*?<\/small>)([^&]*)/i', fn($m) => sprintf($link, $m[1]) . $m[2] . sprintf($link, $m[3]), $text, -1, $nMatches);

                    if (!$nMatches)
                        $text = sprintf($link, $text);
                }

                if ($cooldown < 0)
                {
                    if ($catgCooldown >= 0)
                        $cooldown = $catgCooldown;          // note: the client sets cd = 0 here. But i think TC applies the catgCD..?
                    else
                        $cooldown = max($spellEntry->recoveryTime, $spellEntry->recoveryCategory);
                }

                // note: client only displays cooldowns for SPELL_TRIGGER_USE; ppm is not displayed
                //       WH also does not display CDs < 5 sec.

                $extra = '';
                if ($cooldown >= 5000 && $trigger != SPELL_TRIGGER_EQUIP)
                {
                    $pt = DateTime::parse($cooldown);
                    if (count(array_filter($pt)) == 1)      // simple time: use simple method
                        $extra .= ' '.Lang::formatTime($cooldown, 'item', 'cooldown');
                    else                                    // build block with generic time
                        $extra .= ' '.Lang::item('cooldown', 0, [Lang::formatTime($cooldown, 'game', 'timeAbbrev', true)]);
                }

                if ($trigger == SPELL_TRIGGER_HIT && $ppm)
                    $extra .= ' '.Lang::spell('ppm', [$ppm]);

                # Trigger #

                // trigger 6 reuses ITEM_SPELL_TRIGGER_ONUSE
                if ($text)
                    $x .= '<span class="'.($createsItem ? 'q1' : 'q2').'">'.Lang::item('trigger', $trigger == 6 ? 0 : $trigger).$text.$extra.'</span><br />';

                # Charges #

                // omit ITEM_SPELL_CHARGES_NONE
                if ($charges && $charges != -1)
                    $x .= '<span class="q1">'.Lang::item('charges', [abs($charges)]).'</span><br />';

                # Create Item #

                if (!$createSpell || $createSpell->error)
                    continue;

                $craftItemScaleInfo = [];
                if (!($craftItem = new ItemEntry($createSpell->effectCreateItemId[0]))->error)
                    if ($itemTT = $craftItem->renderTooltip($interactive, $this->id, $craftItemScaleInfo))
                        $x .= '<div><br />'.$itemTT.'</div>';

                # Reagents #

                if ($reagents = $createSpell->getReagents())
                {
                    $rItems  = new ItemContainer(array(['id', array_keys($reagents)]));
                    $reqReag = [];

                    foreach ($reagents as [$iId, $qty])
                    {
                        if ($reagent = $rItems->getEntry($iId))
                            $_ = '<a href="?item='.$iId.'">'.$reagent->name.'</a>';
                        else
                            $_ = 'Item #'.$iId;

                        $reqReag[] = ($qty > 1) ? Lang::main('parensFmt', [$_, $qty]) : $_;
                    }

                    $x .= '<div class="q1 whtt-reagents"><br />'.Lang::item('reqSkill', [Lang::concat($reqReag, Lang::CONCAT_NONE)]).'</div>';
                }
            }
        }


        #########################
        # AddItemUseEnchantLine #
        #########################

        if ($enchantment && !$enchantment->error && $enchantment->type[0] == ENCHANTMENT_TYPE_USE_SPELL && ($spellId = $enchantment->object[0]) > 0)
        {
            if (!($spell = new SpellEntry($spellId))->error)
            {
                $useText = Lang::item('trigger', SPELL_TRIGGER_USE).$spell->renderText()[0];

                // ITEM_COOLDOWN_TOTAL
                if ($cd = max($spell->recoveryTime, $spell->recoveryCategory))
                {
                    $pt = DateTime::parse($cd);
                    if (count(array_filter($pt)) == 1)      // simple time: use simple method
                        $useText .= ' '.Lang::formatTime($cd, 'item', 'cooldown');
                    else                                    // build block with generic time
                        $useText .= ' '.Lang::item('cooldown', 0, [Lang::formatTime($cd, 'game', 'timeAbbrev', true)]);
                }

                // ITEM_SPELL_CHARGES
                if ($enchantment->charges && $enchantment->charges != -1)
                    $useText = Lang::main('parensFmt', [$useText, Lang::item('charges', [$enchantment->charges])]);

                // no need to link spell. This can only ever be displayed in tooltip popups.
                $x .= '<span class="q2">'.$useText.'</span><br />';
            }

            // error cases
            // ENCHANT_ITEM_MIN_SKILL / ENCHANT_ITEM_REQ_SKILL
            // ENCHANT_ITEM_REQ_LEVEL
        }


        ###################
        # AddItemSetLines #
        ###################

        $pieces = [];
        if ($this->itemset)
        {
            $condition = array(
                ['refSetId', $this->itemset],
             // ['quality',  $this->quality],
                ['minLevel', $_itemLevel, '<='],
                ['maxLevel', $_itemLevel, '>=']
            );

            if (!($itemset = new ItemsetContainer($condition))->error)
            {
                // handle special cases where:
                // > itemset has items of different qualities (handled by not limiting for quality in the initial query)
                // > itemset is virtual and multiple instances have the same itemLevel but not quality (filter below)
                $itemsetEntry = null;
                foreach ($itemset->iterate() as $id => $itemsetEntry)
                    if ($itemsetEntry->quality == $this->quality)
                        break;

                // note: in theory the names should come from world.item_set_names / item_set_names_locale

                $pieces = DB::Aowow()->selectAssoc(
                   'SELECT   b.`id` AS ARRAY_KEY, b.`name_loc0`, b.`name_loc2`, b.`name_loc3`, b.`name_loc4`, b.`name_loc6`, b.`name_loc8`, GROUP_CONCAT(a.`id` SEPARATOR ":") AS "equiv"
                    FROM     ::items a, ::items b
                    WHERE    a.`slotBak` = b.`slotBak` AND a.`itemset` = b.`itemset` AND b.`id` IN %in
                    GROUP BY b.`id`',
                    $itemsetEntry->items
                );

                # Title #

                $x .= '<br /><span class="q">'.Lang::item('setName', [Lang::makeLink(Type::ITEMSET, $itemsetEntry->id, $itemsetEntry->name, cssClass: 'q'), 0, count($pieces)]).'</span><br />';

                # Required Skill #

                if ($itemsetEntry->skillId)
                {
                    $link = SkillEntry::makeLink($itemsetEntry->skillId, cssClass: 'q1');
                    if ($itemsetEntry->skillLevel > 0)
                        $x .= Lang::item('reqSkillValue', [$link, $itemsetEntry->skillLevel]).'<br />';
                    else
                        $x .= Lang::item('reqSkill', [$link]).'<br />';
                }

                # Pieces #

                foreach ($pieces as $k => &$p)
                    $p = '<span><!--si'.$p['equiv'].'--><a href="?item='.$k.'">'.Util::localizedString($p, 'name').'</a></span>';

                $x .= '<div class="q0 indent">'.implode('<br />', $pieces).'</div>';

                # Boni #

                if ($spellIds = array_filter($itemsetEntry->spells))
                {
                    $setSpells = [];
                    if (!($boni = new SpellContainer(array(['id', $spellIds])))->error)
                    {
                        foreach ($spellIds as $idx => $spellId)
                        {
                            if (!($spellEntry = $boni->getEntry($spellId)))
                                continue;

                            [$tooltip, , $scaling] = $spellEntry->renderText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL);
                            if ($scaling && $interactive)
                                $causesScaling = true;

                            $setSpells[] = [$tooltip, $spellId, $itemsetEntry->boni[$idx]];
                        }
                    }

                    $x .= '<br /><span class="q0">';

                    usort($setSpells, fn($a, $b) => $a[2] <=> $b[2]);

                    foreach ($setSpells as $n => [$tooltip, $entry, $bonus])
                    {
                        $x .= '<span>'.Lang::item('setBonus', [$bonus, '<a href="?spell='.$entry.'">'.$tooltip.'</a>']).'</span>';
                        if (++$n < count($setSpells))
                            $x .= '<br />';
                    }

                    $x .= '</span><br />';
                }
            }
        }

        # ITEM_COOLDOWN_TIME # // not used here

        # Description #

        if (!$this->description->isEmpty() && !$isRecipe)
            $x .= '<span class="q">"'.UIText::format($this->description, Lang::FMT_HTML).'"</span><br />';


        #######################
        # AddItemCreatorLines #
        #######################

        // ITEM_WRAPPED_BY // not used here
        // ITEM_WRITTEN_BY // not used here
        // ITEM_CREATED_BY // not used here

        # ITEM_OPENABLE / ITEM_READABLE #


        if ($this->flags & (ITEM_FLAG_HAS_LOOT | ITEM_FLAG_IS_WRAPPER))
            $x .= '<span class="q2">'.Lang::item('openClick').'</span><br />';
        else if ($this->pageTextId)
            $x .= '<span class="q2">'.Lang::item('readClick').'</span><br />';

        # ITEM_SOCKETABLE #

        // unsure. WH does not do it
        // if ($sockCount)
        //     $x .= '<span class="q2">'.Lang::item('socketClick').'</span><br />';


        #########################
        # AddItemDisenchantLine #
        #########################

        // ITEM_DISENCHANT_MIN_SKILL // not used here
        // ITEM_DISENCHANT_ANY_SKILL // not used here
        // ITEM_DISENCHANT_NOT_DISENCHANTABLE // not used here


        #####################
        # AddItemTradeLines #
        #####################

        // REFUND_TIME_REMAINING // not used here
        // BIND_TRADE_TIME_REMAINING // not used here

        if (!$subOf)                                        // replace last <br /> if using table layout
            $x = substr_replace($x, '</td></tr></table>', -6);


        ####################
        # AddItemSellPrice #
        ####################

        # ITEM_UNSELLABLE #

        // WH omitted

        # AddMoney #

        if ($this->sellPrice)
            $x .= '<div class="q1 whtt-sellprice">'.Lang::item('sellPrice').Lang::main('colon').Util::formatMoney($this->sellPrice).'</div>';


        // tooltip scaling
        $scale = array(
            // itemId
            $this->id,
            // scaleMinLevel
            1,
            // scaleMaxLevel
            max($craftItemEnh['x'][2] ?? 1, $this->scalingDistribution['maxLevel'] ?? ($causesScaling ? MAX_LEVEL : 1)),
            // scaleCurLevel
            min($craftItemEnh['x'][3] ?? MAX_LEVEL, $this->scalingDistribution['maxLevel'] ?? ($_reqLvl ?: MAX_LEVEL)),
            // scaleDist (may be omitted)
            $craftItemEnh['x'][4] ?? $this->scalingStatDistribution,
            // scaleFlags (may be omitted)
            $craftItemEnh['x'][5] ?? $this->scalingStatValue
        );

        if ($subOf)
            $enhance['x'] = array_slice($scale, 2, null, true);
        else
            $x .= '<!--?'.implode(':', array_filter($scale)).'-->';

        return $x;
    }

    private function initScalingStats() : ?array
    {
        if (!($this->scalingDistribution ??= DB::Aowow()->selectRow('SELECT * FROM ::scalingstatdistribution WHERE `id` = %i', $this->scalingStatDistribution)))
            return null;

        $types  =
        $values =
        $dmg    = [];
        $armor  = 0;

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
            $range   = $this->isRangedWeapon() ? 0.3 : 0.2;
            $average = $extraDPS * $this->delay / 1000;

            $dmg = [floor((1 - $range) * $average), floor((1 + $range) * $average), SPELL_SCHOOL_NORMAL];
        }

        return [$types, $values, $armor, $dmg];
    }

    private function getSSDMod(string $type) : int
    {
        if (!($this->scalingValues ??= DB::Aowow()->selectRow('SELECT * FROM ::scalingstatvalues WHERE `id` = %i', $this->scalingDistribution['maxLevel'])))
            return 0;

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
            'side'        => $this->side,
            'gearscore'   => 0
        );

        // use non-zero values only
        $nullable = array_filter(array(
            'heroic'      => ($this->flags & ITEM_FLAG_HEROIC_TOOLTIP) >> 3,
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
            $json['gearscore'] = Util::getGemScore($nullable['level'] ?? 0, $this->quality, $this->requiredSkill == SKILL_JEWELCRAFTING, $this->id);

        // itemset set in __construct
        $this->json += $json + $nullable;
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

    public function setRandomEnchantment(?int $randProp = null) : bool
    {
        // is it available for this item? .. does it even exist?!
        if (empty($randProp ??= $this->enhance['r']) || !$this->randomEnchant)
            return false;

        return !!$this->randEnchantEntry ??= (function(int $prop)
        {
            if (DB::World()->selectCell('SELECT 1 FROM item_enchantment_template WHERE `entry` = %i AND `ench` = %i', abs($this->randomEnchant), abs($prop)))
                if ($_ = DB::Aowow()->selectRow('SELECT * FROM ::itemrandomenchant WHERE `id` = %i', $prop))
                    return $_;
            return [];
        })($randProp);
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
     * only used by item xml
     */
    public function getOnUseStats() : ?StatsContainer
    {
        if ($this->class != ITEM_CLASS_CONSUMABLE)
            return null;

        return $this->itemStats;
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
        if ($this->setRandomEnchantment())
            return Lang::item('itemSuffixTpl', [$this->name, Util::localizedString($this->randEnchantEntry, 'name')]);

        return $this->name;
    }

    public function extendJsonStats(?array $eStats = null) : void
    {
        if (!$this->socketBonus && !$this->gemEnchantmentId)
            return;

        $eStats ??= self::fetchEnchantmentStats($this->socketBonus, $this->gemEnchantmentId);

        // fetch and add socketbonusstats
        if ($this->socketBonus && ($_ = array_filter($eStats[$this->socketBonus] ??[])))
            $this->json['socketbonusstat'] = $_;

        // Item is a gem (don't confuse with sockets)
        if ($this->gemEnchantmentId && ($_ = array_filter($eStats[$this->gemEnchantmentId] ??[])))
            Util::arraySumByKey($this->json, $_);
    }


    public function initSubItems(?array $templates = null, ?array $randEnchants = null, ?EnchantmentContainer $enchEntries = null) : bool
    {
        if (!$this->randomEnchant || !is_null($this->subItems))
            return false;

        if (!$templates || $randEnchants || !$enchEntries)
            if (!([$templates, $randEnchants, $enchEntries] = self::fetchSubItemEnchantments($this->randomEnchant)))
                return false;

        if (empty($templates[$this->randomEnchant]))
            return false;

        $this->json['subitems'] = &$this->subItems;

        foreach ($templates[$this->randomEnchant] as $randomId => $template)
        {
            if (empty($randEnchants[$randomId]))
                continue;

            $randEnchant = $randEnchants[$randomId];
            $jsonEquip   = [];
            $jsonText    = [];

            for ($i = 1; $i < 6; $i++)
            {
                $enchId = $randEnchant['enchantId'.$i];
                $eEntry = $enchEntries->getEntry($enchId);
                if ($enchId <= 0 || !$eEntry)
                    continue;

                if ($randEnchant['allocationPct'.$i] > 0)   // RandomSuffix: scaling Enchantment; enchId < 0
                {
                    $qty   = intval($randEnchant['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                    $stats = array_fill_keys(array_keys($eEntry->getStatGain()), $qty);

                    $jsonText[$enchId] = str_replace('$i', $qty, $eEntry->name);
                    Util::arraySumByKey($jsonEquip, $stats);
                }
                else                                        // RandomProperty: static Enchantment; enchId > 0
                {
                    $jsonText[$enchId] = $eEntry->name;
                    Util::arraySumByKey($jsonEquip, $eEntry->getStatGain());
                }
            }

            $this->subItems[$randomId] = array(
                'name'        => Util::localizedString($randEnchant, 'name'),
                'enchantment' => $jsonText,
                'jsonequip'   => $jsonEquip,
                'chance'      => $template['chance']        // hmm, only needed for item detail page...
            );
        }

        return !!$this->subItems;
    }

    public static function fetchEnchantmentStats(int ...$eIds) : array
    {
        if ($eIds = array_filter($eIds))
            return DB::Aowow()->selectAssoc('SELECT *, `typeId` AS ARRAY_KEY FROM ::item_stats WHERE `type` = %i AND `typeId` IN %in', Type::ENCHANTMENT, $eIds) ?: [];
        return [];
    }

    /**
     * @param int[] $subItemIds item enchantment template entries
     * @return ?array{array<int, array<int, int>[]>[], array<int, array>[], EnchantmentContainer} [item enchantment template data, itemrandomenchantment dbc data, Enchamtnents]
     */
    public static function fetchSubItemEnchantments(int ...$subItemIds) : ?array
    {
        if (!$subItemIds)
            return null;

        // remember: id < 0: randomSuffix; id > 0: randomProperty
        $templates = DB::World()->selectAssoc(
           'SELECT CAST( `entry` AS SIGNED) AS ARRAY_KEY, CAST( `ench` AS SIGNED) AS ARRAY_KEY2, `chance` FROM item_enchantment_template WHERE `entry` IN %in UNION
            SELECT CAST(-`entry` AS SIGNED) AS ARRAY_KEY, CAST(-`ench` AS SIGNED) AS ARRAY_KEY2, `chance` FROM item_enchantment_template WHERE `entry` IN %in',
            array_map('abs', array_filter($subItemIds, fn($v) => $v > 0)) ?: [0],
            array_map('abs', array_filter($subItemIds, fn($v) => $v < 0)) ?: [0]
        );

        $randIds = [];
        foreach ($templates as $tpl)
            $randIds = array_merge($randIds, array_keys($tpl));

        if (!$randIds)
            return null;

        $randEnchants = DB::Aowow()->selectAssoc('SELECT *, `id` AS ARRAY_KEY FROM ::itemrandomenchant WHERE `id` IN %in', $randIds);
        $enchIds = array_unique(array_merge(
            array_column($randEnchants, 'enchantId1'),
            array_column($randEnchants, 'enchantId2'),
            array_column($randEnchants, 'enchantId3'),
            array_column($randEnchants, 'enchantId4'),
            array_column($randEnchants, 'enchantId5')
        ));

        return [$templates, $randEnchants, new EnchantmentContainer(array(['id', $enchIds]))];
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

    public static function getSourceMore(int ...$ids) : array
    {
        $data = DB::Aowow()->SelectAssoc(
           'SELECT    x.`id` AS ARRAY_KEY, x.`name_loc0`, x.`name_loc2`, x.`name_loc3`, x.`name_loc4`, x.`name_loc6`, x.`name_loc8`, x.`quality`, i.`name` AS "icon"
            FROM      %n x
            LEFT JOIN ::icons i ON i.id = x.iconId
            WHERE     x.`id` IN %in',
            static::$dataTable, $ids
        ) ?: [];

        foreach ($data as $i => $d)
            $sm[$i] = array(
                'n'    => new LocString($d),
                't'    => Type::ITEM,
                'ti'   => $i,
                'q'    => $d['quality'],
                'icon' => $d['icon'] ?: DEFAULT_ICON
            );

        return $sm ?? [];
    }
}

?>
