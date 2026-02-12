<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemList extends DBTypeList
{
    use ListviewHelper, sourceHelper;

    public static int    $type       = Type::ITEM;
    public static string $brickFile  = 'item';
    public static string $dataTable  = '?_items';
    public        array  $json       = [];
    public        array  $jsonStats  = [];
    public        array  $rndEnchIds = [];
    public        array  $subItems   = [];

    private array $randPropPoints = [];
    private array $ssd            = [];
    private array $vendors        = [];
    private array $jsGlobals      = [];                     // getExtendedCost creates some and has no access to template
    private array $enhanceR       = [];
    private array $relEnchant     = [];

    protected string $queryBase  = 'SELECT i.*, i.`block` AS "tplBlock", i.`armor` AS tplArmor, i.`dmgMin1` AS "tplDmgMin1", i.`dmgMax1` AS "tplDmgMax1", i.`id` AS ARRAY_KEY, i.`id` AS "id" FROM ?_items i';
    protected array  $queryOpts  = array(                   // 3 => Type::ITEM
                        'i'   => [['is', 'src', 'ic'], 'o' => 'i.`quality` DESC, i.`itemLevel` DESC'],
                        'ic'  => ['j' => ['?_icons      `ic`  ON `ic`.`id` = `i`.`iconId`', true], 's' => ', ic.`name` AS "iconString"'],
                        'is'  => ['j' => ['?_item_stats `is`  ON `is`.`type` = 3 AND `is`.`typeId` = `i`.`id`', true], 's' => ', `is`.*'],
                        's'   => ['j' => ['?_spell      `s`   ON `s`.`effect1CreateItemId` = `i`.`id`', true], 'g' => 'i.`id`'],
                        'e'   => ['j' => ['?_events     `e`   ON `e`.`id` = `i`.`eventId`', true], 's' => ', e.`holidayId`'],
                        'src' => ['j' => ['?_source     `src` ON `src`.`type` = 3 AND `src`.`typeId` = `i`.`id`', true], 's' => ', `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`, `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        foreach ($this->iterate() as &$_curTpl)
        {
            // item is scaling; overwrite other values
            if ($_curTpl['scalingStatDistribution'] > 0 && $_curTpl['scalingStatValue'] > 0)
                $this->initScalingStats();

            // fix missing icons
            $_curTpl['iconString'] = $_curTpl['iconString'] ?: DEFAULT_ICON;

            // from json to json .. the gentle fuckups of legacy code integration
            $this->initJsonStats();
            $this->jsonStats[$this->id] = (new StatsContainer())->fromJson($_curTpl, true)->toJson(Stat::FLAG_ITEM /* | Stat::FLAG_SERVERSIDE */, false);

            if ($miscData)
            {
                // readdress itemset .. is wrong for virtual sets
                if (isset($miscData['pcsToSet']) && isset($miscData['pcsToSet'][$this->id]))
                    $this->json[$this->id]['itemset'] = $miscData['pcsToSet'][$this->id];

                // additional rel attribute for listview rows
                if (isset($miscData['extraOpts']['relEnchant']))
                    $this->relEnchant = $miscData['extraOpts']['relEnchant'];
            }

            // sources
            for ($i = 1; $i < 25; $i++)
            {
                if ($_ = $_curTpl['src'.$i])
                    $this->sources[$this->id][$i][] = $_;

                unset($_curTpl['src'.$i]);
            }
        }
    }

    // todo (med): information will get lost if one vendor sells one item multiple times with different costs (e.g. for item 54637)
    //             wowhead seems to have had the same issues
    public function getExtendedCost(?array $filter = [], ?array &$reqRating = []) : array
    {
        if ($this->error)
            return [];

        $idx = $this->id;

        if (empty($this->vendors))
        {
            $itemz      = [];
            $xCostData  = [];
            $rawEntries = DB::World()->select(
               'SELECT   nv.`item`,        nv.`entry`,              0  AS "eventId",    nv.`maxcount`,   nv.`extendedCost`,   nv.`incrtime`
                FROM    npc_vendor nv
                WHERE { nv.`entry` IN (?a) AND } nv.`item` IN (?a)
                  UNION
                SELECT   nv2.`item`,      nv1.`entry`,              0  AS "eventId",   nv2.`maxcount`,  nv2.`extendedCost`,  nv2.`incrtime`
                FROM    npc_vendor   nv1
                JOIN    npc_vendor   nv2 ON -nv1.`item` = nv2.`entry` { AND nv1.`entry` IN (?a) }
                WHERE   nv2.`item` IN (?a)
                  UNION
                SELECT genv.`item`, c.`id` AS "entry", ge.`eventEntry` AS "eventId",  genv.`maxcount`, genv.`extendedCost`, genv.`incrtime`
                FROM      game_event_npc_vendor genv
                LEFT JOIN game_event ge ON genv.`eventEntry` = ge.`eventEntry`
                JOIN      creature c ON c.`guid` = genv.`guid`
                WHERE   { c.`id` IN (?a) AND } genv.`item` IN (?a)',
                empty($filter[Type::NPC]) || !is_array($filter[Type::NPC]) ? DBSIMPLE_SKIP : $filter[Type::NPC],
                array_keys($this->templates),
                empty($filter[Type::NPC]) || !is_array($filter[Type::NPC]) ? DBSIMPLE_SKIP : $filter[Type::NPC],
                array_keys($this->templates),
                empty($filter[Type::NPC]) || !is_array($filter[Type::NPC]) ? DBSIMPLE_SKIP : $filter[Type::NPC],
                array_keys($this->templates)
            );

            foreach ($rawEntries as $costEntry)
            {
                if ($costEntry['extendedCost'])
                    $xCostData[] = $costEntry['extendedCost'];

                if (!isset($itemz[$costEntry['item']][$costEntry['entry']]))
                    $itemz[$costEntry['item']][$costEntry['entry']] = [$costEntry];
                else
                    $itemz[$costEntry['item']][$costEntry['entry']][] = $costEntry;
            }

            if ($xCostData)
                $xCostData = DB::Aowow()->select('SELECT *, `id` AS ARRAY_KEY FROM ?_itemextendedcost WHERE `id` IN (?a)', $xCostData);

            $cItems = [];
            foreach ($itemz as $k => $vendors)
            {
                foreach ($vendors as $l => $vendor)
                {
                    foreach ($vendor as $m => $vInfo)
                    {
                        $costs = [];
                        if (!empty($xCostData[$vInfo['extendedCost']]))
                            $costs = $xCostData[$vInfo['extendedCost']];

                        $data = array(
                            'stock'      => $vInfo['maxcount'] ?: -1,
                            'event'      => $vInfo['eventId'],
                            'restock'    => $vInfo['incrtime'],
                            'reqRating'  => $costs ? $costs['reqPersonalRating'] : 0,
                            'reqBracket' => $costs ? $costs['reqArenaSlot']      : 0
                        );

                        // hardcode arena) & honor
                        if (!empty($costs['reqArenaPoints']))
                        {
                            $data[-103] = $costs['reqArenaPoints'];
                            $this->jsGlobals[Type::CURRENCY][CURRENCY_ARENA_POINTS] = CURRENCY_ARENA_POINTS;
                        }

                        if (!empty($costs['reqHonorPoints']))
                        {
                            $data[-104] = $costs['reqHonorPoints'];
                            $this->jsGlobals[Type::CURRENCY][CURRENCY_HONOR_POINTS] = CURRENCY_HONOR_POINTS;
                        }

                        for ($i = 1; $i < 6; $i++)
                        {
                            if (!empty($costs['reqItemId'.$i]) && $costs['itemCount'.$i] > 0)
                            {
                                $data[$costs['reqItemId'.$i]] = $costs['itemCount'.$i];
                                $cItems[] = $costs['reqItemId'.$i];
                            }
                        }

                        // no extended cost or additional gold required
                        if (!$costs || $this->getField('flagsExtra') & 0x04)
                        {
                            $this->getEntry($k);
                            if ($_ = $this->getField('buyPrice'))
                                $data[0] = $_;
                        }

                        $vendor[$m] = $data;
                    }
                    $vendors[$l] = $vendor;
                }

                $itemz[$k] = $vendors;
            }

            // convert items to currency if possible
            if ($cItems)
            {
                $moneyItems = new CurrencyList(array(['itemId', $cItems]));
                foreach ($moneyItems->getJSGlobals() as $type => $jsData)
                    foreach ($jsData as $k => $v)
                        $this->jsGlobals[$type][$k] = $v;

                foreach ($itemz as $itemId => $vendors)
                {
                    foreach ($vendors as $npcId => $costData)
                    {
                        foreach ($costData as $itr => $cost)
                        {
                            foreach ($cost as $k => $v)
                            {
                                if (in_array($k, $cItems))
                                {
                                    $found = false;
                                    foreach ($moneyItems->iterate() as $__)
                                    {
                                        if ($moneyItems->getField('itemId') == $k)
                                        {
                                            unset($cost[$k]);
                                            $cost[-$moneyItems->id] = $v;
                                            $found = true;
                                            break;
                                        }
                                    }

                                    if (!$found)
                                        $this->jsGlobals[Type::ITEM][$k] = $k;
                                }
                            }
                            $costData[$itr] = $cost;
                        }
                        $vendors[$npcId] = $costData;
                    }
                    $itemz[$itemId] = $vendors;
                }
            }

            $this->vendors = $itemz;
        }

        $result = $this->vendors;

        // apply filter if given
        $tok = !empty($filter[Type::ITEM])     ? $filter[Type::ITEM]     : null;
        $cur = !empty($filter[Type::CURRENCY]) ? $filter[Type::CURRENCY] : null;

        foreach ($result as $itemId => &$data)
        {
            $reqRating = [];
            foreach ($data as $npcId => $entries)
            {
                foreach ($entries as $costs)
                {
                    if ($tok || $cur)                           // bought with specific token or currency
                    {
                        $valid = false;
                        foreach ($costs as $k => $qty)
                        {
                            if ((!$tok || $k == $tok) && (!$cur || $k == -$cur))
                            {
                                $valid = true;
                                break;
                            }
                        }

                        if (!$valid)
                            unset($data[$npcId]);
                    }

                    // reqRating ins't really a cost .. so pass it by ref instead of return
                    // data was invalid and deleted or some source doesn't require arena rating
                    if (!isset($data[$npcId]) || ($reqRating && !$reqRating[0]))
                        continue;

                    // use lowest total value
                    if (!$costs['reqRating'])
                        $reqRating = [0, 2];
                    else if ($costs['reqRating'] && (!$reqRating || $reqRating[0] > $costs['reqRating']))
                        $reqRating = [$costs['reqRating'], $costs['reqBracket']];
                }
            }

            if (empty($data))
                unset($result[$itemId]);
        }

        // restore internal index;
        $this->getEntry($idx);

        return $result;
    }

    public function getListviewData(int $addInfoMask = 0x0, ?array $miscData = null) : array
    {
        /*
        * ITEMINFO_JSON     (0x01): jsonStats (including spells) and subitems parsed
        * ITEMINFO_SUBITEMS (0x02): searched by comparison
        * ITEMINFO_VENDOR   (0x04): costs-obj, when displayed as vendor
        * ITEMINFO_GEM      (0x10): gem infos and score
        * ITEMINFO_MODEL    (0x20): sameModelAs-Tab
        */

        $data = [];

        // random item is random
        if ($addInfoMask & ITEMINFO_SUBITEMS)
            $this->initSubItems();

        if ($addInfoMask & ITEMINFO_JSON)
        {
            $this->extendJsonStats();
            Util::arraySumByKey($data, $this->jsonStats);
        }

        $extCosts = [];
        if ($addInfoMask & ITEMINFO_VENDOR)
            $extCosts = $this->getExtendedCost($miscData);

        $extCostOther = [];
        foreach ($this->iterate() as $__)
        {
            foreach ($this->json[$this->id] as $k => $v)
                $data[$this->id][$k] = $v;

            // json vs listview quirk
            $data[$this->id]['name'] = $data[$this->id]['quality'].Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_RAW);
            unset($data[$this->id]['quality']);

            if (!empty($this->relEnchant) && $this->curTpl['randomEnchant'])
            {
                if (($x = array_search($this->curTpl['randomEnchant'], array_column($this->relEnchant, 'entry'))) !== false)
                {
                    $data[$this->id]['rel']   = 'rand='.$this->relEnchant[$x]['ench'];
                    $data[$this->id]['name'] .= ' '.$this->relEnchant[$x]['name'];
                }
            }

            if ($addInfoMask & ITEMINFO_JSON)
            {
                if ($_ = intVal(($this->curTpl['minMoneyLoot'] + $this->curTpl['maxMoneyLoot']) / 2))
                    $data[$this->id]['avgmoney'] = $_;

                if ($_ = $this->curTpl['repairPrice'])
                    $data[$this->id]['repaircost'] = $_;
            }

            if ($addInfoMask & (ITEMINFO_JSON | ITEMINFO_GEM))
                if (isset($this->curTpl['score']))
                    $data[$this->id]['score'] = $this->curTpl['score'];

            if ($addInfoMask & ITEMINFO_GEM)
            {
                $data[$this->id]['uniqEquip']   = ($this->curTpl['flags'] & ITEM_FLAG_UNIQUEEQUIPPED) ? 1 : 0;
                $data[$this->id]['socketLevel'] = 0;        // not used with wotlk
            }

            if ($addInfoMask & ITEMINFO_VENDOR)
            {
                // just use the first results
                // todo (med): dont use first vendor; search for the right one
                if (!empty($extCosts[$this->id]))
                {
                    $cost = reset($extCosts[$this->id]);
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
                            $extCostOther[$this->id][] = $costArr;
                        else
                            $data[$this->id] = array_merge($data[$this->id], $costArr);
                    }
                }

                if ($x = $this->curTpl['buyPrice'])
                    $data[$this->id]['buyprice'] = $x;

                if ($x = $this->curTpl['sellPrice'])
                    $data[$this->id]['sellprice'] = $x;

                if ($x = $this->curTpl['buyCount'])
                    $data[$this->id]['stack'] = $x;
            }

            if ($this->curTpl['class'] == ITEM_CLASS_GLYPH)
                $data[$this->id]['glyph'] = $this->curTpl['subSubClass'];

            if ($x = $this->curTpl['requiredSkill'])
                $data[$this->id]['reqskill'] = $x;

            if ($x = $this->curTpl['requiredSkillRank'])
                $data[$this->id]['reqskillrank'] = $x;

            if ($x = $this->curTpl['requiredSpell'])
                $data[$this->id]['reqspell'] = $x;

            if ($x = $this->curTpl['requiredFaction'])
                $data[$this->id]['reqfaction'] = $x;

            if ($x = $this->curTpl['requiredFactionRank'])
            {
                $data[$this->id]['reqrep']   = $x;
                $data[$this->id]['standing'] = $x;          // used in /faction item-listing
            }

            if ($x = $this->curTpl['slots'])
                $data[$this->id]['nslots'] = $x;

            $_ = $this->curTpl['requiredRace'];
            if (ChrRace::sideFromMask($_) != SIDE_BOTH)
                $data[$this->id]['reqrace'] = $_;

            if ($_ = $this->curTpl['requiredClass'])
                $data[$this->id]['reqclass'] = $_;          // $data[$this->id]['classes'] ??

            if ($this->curTpl['flags'] & ITEM_FLAG_HEROIC)
                $data[$this->id]['heroic'] = true;

            if ($addInfoMask & ITEMINFO_MODEL)
                if ($_ = $this->getField('displayId'))
                    $data[$this->id]['displayid'] = $_;

            if ($this->getSources($s, $sm))
            {
                $data[$this->id]['source'] = $s;
                if ($sm)
                    $data[$this->id]['sourcemore'] = $sm;
            }

            if (!empty($this->curTpl['cooldown']))
                $data[$this->id]['cooldown'] = $this->curTpl['cooldown'] / 1000;
        }

        foreach ($extCostOther as $itemId => $duplicates)
            foreach ($duplicates as $d)
                $data[] = array_merge($data[$itemId], $d);  // we dont really use keys on data, but this may cause errors in future

        /* even more complicated crap
            modelviewer {type:X, displayid:Y, slot:z} .. not sure, when to set
        */

        return $data;
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_SELF, ?array &$extra = []) : array
    {
        $data = $addMask & GLOBALINFO_RELATED ? $this->jsGlobals : [];

        foreach ($this->iterate() as $id => $__)
        {
            if ($addMask & GLOBALINFO_SELF)
            {
                $data[Type::ITEM][$id] = array(
                    'name'    => Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_RAW),
                    'quality' => $this->curTpl['quality'],
                    'icon'    => $this->curTpl['iconString']
                );

                if ($this->curTpl['class'] == ITEM_CLASS_RECIPE)
                    $data[Type::ITEM][$id]['completion_category'] = $this->curTpl['class'];
                else if ($this->curTpl['class'] == ITEM_CLASS_MISC && in_array($this->curTpl['subClass'], [2, 5, -7]))
                    $data[Type::ITEM][$id]['completion_category'] = $this->curTpl['class'].'-'.$this->curTpl['subClass'];
            }

            if ($addMask & GLOBALINFO_EXTRA)
            {
                $extra[$id] = array(
                 // 'id'      => $id,
                    'tooltip' => $this->renderTooltip(true),
                    'spells'  => new \StdClass              // placeholder for knownSpells
                );
            }
        }

        return $data;
    }

    /*
        enhance (set by comparison tool or formated external links)
            ench: enchantmentId
            sock: bool (extraScoket (gloves, belt))
            gems: array (:-separated itemIds)
            rand: >0: randomPropId; <0: randomSuffixId
        interactive (set to place javascript/anchors to manipulate level and ratings or link to filters (static tooltips vs popup tooltip))
        subOf (tabled layout doesn't work if used as sub-tooltip in other item or spell tooltips; use line-break instead)
    */
    public function getField(string $field, bool $localized = false, bool $silent = false, ?array $enhance = []) : mixed
    {
        $res = parent::getField($field, $localized, $silent);

        if ($field == 'name' && !empty($enhance['r']))
            if ($this->getRandEnchantForItem($enhance['r']))
                $res .= ' '.Util::localizedString($this->enhanceR, 'name');

        return $res;
    }

    public function renderTooltip(bool $interactive = false, int $subOf = 0, ?array $enhance = []) : ?string
    {
        if ($this->error)
            return null;

        $_name         = Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_HTML);
        $_reqLvl       = $this->curTpl['requiredLevel'];
        $_quality      = $this->curTpl['quality'];
        $_flags        = $this->curTpl['flags'];
        $_class        = $this->curTpl['class'];
        $_subClass     = $this->curTpl['subClass'];
        $_slot         = $this->curTpl['slot'];
        $causesScaling = false;

        if (!empty($enhance['r']))
        {
            if ($this->getRandEnchantForItem($enhance['r']))
            {
                $_name      .= ' '.Util::localizedString($this->enhanceR, 'name');
                $randEnchant = '';

                for ($i = 1; $i < 6; $i++)
                {
                    if ($this->enhanceR['enchantId'.$i] <= 0)
                        continue;

                    $enchant = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE `id` = ?d', $this->enhanceR['enchantId'.$i]);
                    if ($this->enhanceR['allocationPct'.$i] > 0)
                    {
                        $amount = intVal($this->enhanceR['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                        $randEnchant .= '<span>'.str_replace('$i', $amount, Util::localizedString($enchant, 'name')).'</span><br />';
                    }
                    else
                        $randEnchant .= '<span>'.Util::localizedString($enchant, 'name').'</span><br />';
                }
            }
            else
                unset($enhance['r']);
        }

        if (isset($enhance['s']) && !in_array($_slot, [INVTYPE_WRISTS, INVTYPE_WAIST, INVTYPE_HANDS]))
            unset($enhance['s']);

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

        // requires map (todo: reparse ?_zones for non-conflicting data; generate Link to zone)
        if ($_ = $this->curTpl['map'])
        {
            $map = DB::Aowow()->selectRow('SELECT * FROM ?_zones WHERE `mapId` = ?d LIMIT 1', $_);
            $x .= '<br /><a href="?zone='.$_.'" class="q1">'.Util::localizedString($map, 'name').'</a>';
        }

        // requires area
        if ($this->curTpl['area'])
        {
            $area = DB::Aowow()->selectRow('SELECT * FROM ?_zones WHERE `id` = ?d LIMIT 1', $this->curTpl['area']);
            $x .= '<br />'.Util::localizedString($area, 'name');
        }

        // conjured
        if ($_flags & ITEM_FLAG_CONJURED)
            $x .= '<br />'.Lang::item('conjured');

        // bonding
        if ($_flags & ITEM_FLAG_ACCOUNTBOUND)
            $x .= '<br /><!--bo-->'.Lang::item('bonding', 0);
        else if ($this->curTpl['bonding'])
            $x .= '<br /><!--bo-->'.Lang::item('bonding', $this->curTpl['bonding']);

        // unique || unique-equipped || unique-limited
        if ($this->curTpl['maxCount'] == 1)
            $x .= '<br />'.Lang::item('unique', 0);
        // not for currency tokens
        else if ($this->curTpl['maxCount'] && $this->curTpl['bagFamily'] != 8192)
            $x .= '<br />'.sprintf(Lang::item('unique', 1), $this->curTpl['maxCount']);
        else if ($_flags & ITEM_FLAG_UNIQUEEQUIPPED)
            $x .= '<br />'.Lang::item('uniqueEquipped', 0);
        else if ($this->curTpl['itemLimitCategory'])
        {
            $limit = DB::Aowow()->selectRow("SELECT * FROM ?_itemlimitcategory WHERE `id` = ?", $this->curTpl['itemLimitCategory']);
            $x .= '<br />'.sprintf(Lang::item($limit['isGem'] ? 'uniqueEquipped' : 'unique', 2), Util::localizedString($limit, 'name'), $limit['count']);
        }

        // required holiday
        if ($eId = $this->curTpl['eventId'])
            if ($hName = DB::Aowow()->selectRow('SELECT h.* FROM ?_holidays h JOIN ?_events e ON e.`holidayId` = h.`id` WHERE e.`id` = ?d', $eId))
                $x .= '<br />'.sprintf(Lang::game('requires'), '<a href="?event='.$eId.'" class="q1">'.Util::localizedString($hName, 'name').'</a>');

        // item begins a quest
        if ($this->curTpl['startQuest'])
            $x .= '<br /><a class="q1" href="?quest='.$this->curTpl['startQuest'].'">'.Lang::item('startQuest').'</a>';

        // containerType (slotCount)
        if ($this->curTpl['slots'] > 0)
        {
            $fam = $this->curTpl['bagFamily'] ? log($this->curTpl['bagFamily'], 2) + 1 : 0;
            $x .= '<br />'.Lang::item('bagSlotString', [$this->curTpl['slots'], Lang::item('bagFamily', $fam)]);
        }

        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]))
        {
            $x .= '<table width="100%"><tr>';

            // Class
            if ($_slot)
                $x .= '<td>'.Lang::item('inventoryType', $_slot).'</td>';

            // Subclass
            if ($_class == ITEM_CLASS_ARMOR && $_subClass > 0)
                $x .= '<th><!--asc'.$_subClass.'-->'.Lang::item('armorSubClass', $_subClass).'</th>';
            else if ($_class == ITEM_CLASS_WEAPON)
                $x .= '<th>'.Lang::item('weaponSubClass', $_subClass).'</th>';
            else if ($_class == ITEM_CLASS_AMMUNITION)
                $x .= '<th>'.Lang::item('projectileSubClass', $_subClass).'</th>';

            $x .= '</tr></table>';
        }
        else if ($_slot && $_class != ITEM_CLASS_CONTAINER) // yes, slot can occur on random items and is then also displayed <_< .. excluding Bags >_>
            $x .= '<br />'.Lang::item('inventoryType', $_slot).'<br />';
        else
            $x .= '<br />';

        // Weapon/Ammunition Stats                          (not limited to weapons (see item:1700))
        $speed  = $this->curTpl['delay'] / 1000;
        $sc1    = $this->curTpl['dmgType1'];
        $sc2    = $this->curTpl['dmgType2'];
        $dmgmin = $this->curTpl['tplDmgMin1'] + $this->curTpl['dmgMin2'];
        $dmgmax = $this->curTpl['tplDmgMax1'] + $this->curTpl['dmgMax2'];
        $dps    = $speed ? ($dmgmin + $dmgmax) / (2 * $speed) : 0;

        if ($_class == ITEM_CLASS_AMMUNITION && $dmgmin && $dmgmax)
        {
            if ($sc1)
                $x .= sprintf(Lang::item('damage', 'ammo', 1), ($dmgmin + $dmgmax) / 2, Lang::game('sc', $sc1)).'<br />';
            else
                $x .= sprintf(Lang::item('damage', 'ammo', 0), ($dmgmin + $dmgmax) / 2).'<br />';
        }
        else if ($dps)
        {
            if ($this->curTpl['tplDmgMin1'] == $this->curTpl['tplDmgMax1'])
                $dmg = sprintf(Lang::item('damage', 'single', $sc1 ? 1 : 0), $this->curTpl['tplDmgMin1'], $sc1 ? Lang::game('sc', $sc1) : null);
            else
                $dmg = sprintf(Lang::item('damage', 'range', $sc1 ? 1 : 0), $this->curTpl['tplDmgMin1'], $this->curTpl['tplDmgMax1'], $sc1 ? Lang::game('sc', $sc1) : null);

            if ($_class == ITEM_CLASS_WEAPON)               // do not use localized format here!
                $x .= '<table width="100%"><tr><td><!--dmg-->'.$dmg.'</td><th>'.Lang::item('speed').' <!--spd-->'.number_format($speed, 2).'</th></tr></table>';
            else
                $x .= '<!--dmg-->'.$dmg.'<br />';

            // secondary damage is set
            if (($this->curTpl['dmgMin2'] || $this->curTpl['dmgMax2']) && $this->curTpl['dmgMin2'] != $this->curTpl['dmgMax2'])
                $x .= sprintf(Lang::item('damage', 'range', $sc2 ? 3 : 2), $this->curTpl['dmgMin2'], $this->curTpl['dmgMax2'], $sc2 ? Lang::game('sc', $sc2) : null).'<br />';
            else if ($this->curTpl['dmgMin2'])
                $x .= sprintf(Lang::item('damage', 'single', $sc2 ? 3 : 2), $this->curTpl['dmgMin2'], $sc2 ? Lang::game('sc', $sc2) : null).'<br />';

            if ($_class == ITEM_CLASS_WEAPON)
                $x .= '<!--dps-->'.Lang::item('dps', [$dps]).'<br />';

            // display FeralAttackPower if set
            if ($fap = $this->getFeralAP())
                $x .= '<span class="c11"><!--fap-->('.$fap.' '.Lang::item('fap').')</span><br />';
        }

        // Armor
        if ($_class == ITEM_CLASS_ARMOR && $this->curTpl['armorDamageModifier'] > 0)
        {
            $spanI = 'class="q2"';
            if ($interactive)
                $spanI = 'class="q2 tip" onmouseover="$WH.Tooltip.showAtCursor(event, $WH.sprintf(LANG.tooltip_armorbonus, '.$this->curTpl['armorDamageModifier'].'), 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"';

            $x .= '<span '.$spanI.'><!--addamr'.$this->curTpl['armorDamageModifier'].'--><span>'.Lang::item('armor', [$this->curTpl['tplArmor']]).'</span></span><br />';
        }
        else if ($this->curTpl['tplArmor'])
            $x .= '<span><!--amr-->'.Lang::item('armor', [$this->curTpl['tplArmor']]).'</span><br />';

        // Block (note: block value from field block and from field stats or parsed from itemSpells are displayed independently)
        if ($this->curTpl['tplBlock'])
            $x .= '<span>'.sprintf(Lang::item('block'), $this->curTpl['tplBlock']).'</span><br />';

        // Item is a gem (don't mix with sockets)
        if ($geId = $this->curTpl['gemEnchantmentId'])
        {
            $gemEnch = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE `id` = ?d', $geId);
            $x .= '<span class="q1"><a href="?enchantment='.$geId.'">'.Util::localizedString($gemEnch, 'name').'</a></span><br />';

            // activation conditions for meta gems
            if (!empty($gemEnch['conditionId']))
                $x .= Game::getEnchantmentCondition($gemEnch['conditionId'], $interactive);
        }

        // Random Enchantment - if random enchantment is set, prepend stats from it
        if ($this->curTpl['randomEnchant'] && empty($enhance['r']))
            $x .= '<span class="q2">'.Lang::item('randEnchant').'</span><br />';
        else if (!empty($enhance['r']))
            $x .= $randEnchant;

        // itemMods (display stats and save ratings for later use)
        for ($j = 1; $j <= 10; $j++)
        {
            $type = $this->curTpl['statType'.$j];
            $qty  = $this->curTpl['statValue'.$j];

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
        foreach (Game::$resistanceFields as $j => $rowName)
            if ($rowName && $this->curTpl[$rowName] != 0)
                $x .= '+'.$this->curTpl[$rowName].' '.Lang::game('resistances', $j).'<br />';

        // Enchantment
        if (isset($enhance['e']))
        {
            if ($enchText = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE `id` = ?', $enhance['e']))
                $x .= '<span class="q2"><!--e-->'.Util::localizedString($enchText, 'name').'</span><br />';
            else
            {
                unset($enhance['e']);
                $x .= '<!--e-->';
            }
        }
        else                                                // enchantment placeholder
            $x .= '<!--e-->';

        // Sockets w/ Gems
        if (!empty($enhance['g']))
        {
            $gems = DB::Aowow()->select(
               'SELECT it.`id` AS ARRAY_KEY, ic.`name` AS "iconString", ae.*, it.`gemColorMask` AS "colorMask"
                FROM   ?_items it
                JOIN   ?_itemenchantment ae ON ae.`id` = it.`gemEnchantmentId`
                JOIN   ?_icons ic ON ic.`id` = it.`iconId`
                WHERE  it.`id` IN (?a)',
                $enhance['g']
            );

            foreach ($enhance['g'] as $k => $v)
                if ($v && !in_array($v, array_keys($gems))) // 0 is valid
                    unset($enhance['g'][$k]);
        }
        else
            $enhance['g'] = [];

        // zero fill empty sockets
        $sockCount = isset($enhance['s']) ? 1 : 0;
        if (!empty($this->json[$this->id]['nsockets']))
            $sockCount += $this->json[$this->id]['nsockets'];

        while ($sockCount > count($enhance['g']))
            $enhance['g'][] = 0;

        $enhance['g'] = array_reverse($enhance['g']);

        $hasMatch = 1;
        // fill native sockets
        for ($j = 1; $j <= 3; $j++)
        {
            if (!$this->curTpl['socketColor'.$j])
                continue;

            for ($i = 0; $i < 4; $i++)
                if (($this->curTpl['socketColor'.$j] & (1 << $i)))
                    $colorId = $i;

            $pop       = array_pop($enhance['g']);
            $col       = $pop ? 1 : 0;
            $hasMatch &= $pop ? (($gems[$pop]['colorMask'] & (1 << $colorId)) ? 1 : 0) : 0;
            $icon      = $pop ? sprintf('style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"', Cfg::get('STATIC_URL'), strtolower($gems[$pop]['iconString'])) : null;
            $text      = $pop ? Util::localizedString($gems[$pop], 'name') : Lang::item('socket', $colorId);

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs='.($colorId + 1).';crv=0" class="socket-'.Game::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-'.Game::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }

        // fill extra socket
        if (isset($enhance['s']))
        {
            $pop  = array_pop($enhance['g']);
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

        if ($_ = $this->curTpl['socketBonus'])
        {
            $sbonus = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE `id` = ?d', $_);
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::item('socketBonus', ['<a href="?enchantment='.$_.'">'.Util::localizedString($sbonus, 'name').'</a>']).'</span><br />';
        }

        // durability
        if ($dur = $this->curTpl['durability'])
            $x .= sprintf(Lang::item('durability'), $dur, $dur).'<br />';

        // max duration
        if ($dur = $this->curTpl['duration'])
        {
            $rt = '';
            if ($this->curTpl['flagsCustom'] & 0x1)
                $rt = $interactive ? ' ('.sprintf(Util::$dfnString, 'LANG.tooltip_realduration', Lang::item('realTime')).')' : ' ('.Lang::item('realTime').')';

            $x .= Lang::formatTime(abs($dur) * 1000, 'item', 'duration').$rt."<br />";
        }

        // required classes
        $jsg = [];
        if ($classes = Lang::getClassString($this->curTpl['requiredClass'], $jsg))
        {
            foreach ($jsg as $js)
                $this->jsGlobals[Type::CHR_CLASS][$js] ??= $js;

            $x .= Lang::game('classes').Lang::main('colon').$classes.'<br />';
        }

        // required races
        $jsg = [];
        if ($races = Lang::getRaceString($this->curTpl['requiredRace'], $jsg))
        {
            foreach ($jsg as $js)
                $this->jsGlobals[Type::CHR_RACE][$js] ??= $js;

            $x .= Lang::game('races').Lang::main('colon').$races.'<br />';
        }

        // required honorRank (not used anymore)
        if ($rhr = $this->curTpl['requiredHonorRank'])
            $x .= Lang::game('requires', [implode(' / ', Lang::game('pvpRank', $rhr))]).'<br />';

        // required CityRank..?
        // what the f..

        // required level
        if (($_flags & ITEM_FLAG_ACCOUNTBOUND) && $_quality == ITEM_QUALITY_HEIRLOOM)
            $x .= sprintf(Lang::item('reqLevelRange'), 1, MAX_LEVEL, ($interactive ? sprintf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL)).'<br />';
        else if ($_reqLvl > 1)
            $x .= sprintf(Lang::item('reqMinLevel'), $_reqLvl).'<br />';

        // required arena team rating / personal rating / todo (low): sort out what kind of rating
        if (!empty($this->getExtendedCost([], $reqRating)[$this->id]) && $reqRating && $reqRating[0])
            $x .= sprintf(Lang::item('reqRating', $reqRating[1]), $reqRating[0]).'<br />';

        // item level
        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON]))
            $x .= sprintf(Lang::item('itemLevel'), $this->curTpl['itemLevel']).'<br />';

        // required skill
        if ($reqSkill = $this->curTpl['requiredSkill'])
        {
            $_ = '<a class="q1" href="?skill='.$reqSkill.'">'.SkillList::getName($reqSkill).'</a>';
            if ($this->curTpl['requiredSkillRank'] > 0)
                $_ .= ' ('.$this->curTpl['requiredSkillRank'].')';

            $x .= sprintf(Lang::game('requires'), $_).'<br />';
        }

        // required spell
        if ($reqSpell = $this->curTpl['requiredSpell'])
            $x .= Lang::game('requires2').' <a class="q1" href="?spell='.$reqSpell.'">'.SpellList::getName($reqSpell).'</a><br />';

        // required reputation w/ faction
        if ($reqFac = $this->curTpl['requiredFaction'])
            $x .= sprintf(Lang::game('requires'), '<a class="q1" href="?faction='.$reqFac.'">'.FactionList::getName($reqFac).'</a> - '.Lang::game('rep', $this->curTpl['requiredFactionRank'])).'<br />';

        // locked or openable
        if ($locks = Lang::getLocks($this->curTpl['lockId'], $arr, true))
            $x .= '<span class="q0">'.Lang::item('locked').'<br />'.implode('<br />', array_map(fn($x) => Lang::game('requires', [$x]), $locks)).'</span><br />';
        else if ($this->curTpl['flags'] & ITEM_FLAG_OPENABLE)
            $x .= '<span class="q2">'.Lang::item('openClick').'</span><br />';

        // upper table: done
        if (!$subOf)
            $x .= '</td></tr></table>';

        // spells on item
        if (!$this->canTeachSpell())
        {
            $itemSpellsAndTrigger = [];
            for ($j = 1; $j <= 5; $j++)
            {
                if ($this->curTpl['spellId'.$j] > 0)
                {
                    $cd = $this->curTpl['spellCooldown'.$j];
                    if ($cd < $this->curTpl['spellCategoryCooldown'.$j])
                        $cd = $this->curTpl['spellCategoryCooldown'.$j];

                    $extra = [];
                    if ($cd >= 5000 && $this->curTpl['spellTrigger'.$j] != SPELL_TRIGGER_EQUIP)
                    {
                        $pt = DateTime::parse($cd);
                        if (count(array_filter($pt)) == 1)  // simple time: use simple method
                            $extra[] = Lang::formatTime($cd, 'item', 'cooldown');
                        else                                // build block with generic time
                            $extra[] = Lang::item('cooldown', 0, [Lang::formatTime($cd, 'game', 'timeAbbrev', true)]);
                    }
                    if ($this->curTpl['spellTrigger'.$j] == SPELL_TRIGGER_HIT)
                        if ($ppm = $this->curTpl['spellppmRate'.$j])
                            $extra[] = Lang::spell('ppm', [$ppm]);

                    $itemSpellsAndTrigger[$this->curTpl['spellId'.$j]] = [$this->curTpl['spellTrigger'.$j], $extra ? ' '.implode(', ', $extra) : ''];
                }
            }

            if ($itemSpellsAndTrigger)
            {
                $itemSpells = new SpellList(array(['s.id', array_keys($itemSpellsAndTrigger)]));
                foreach ($itemSpells->iterate() as $sId => $__)
                {
                    [$parsed, $_, $scaling] = $itemSpells->parseText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL);
                    if (!$parsed && User::isInGroup(U_GROUP_EMPLOYEE))
                        $parsed = '<span style="opacity:.75">&lt;'.$itemSpells->getField('name', true, true).'&gt;</span>';
                    else if (!$parsed)
                        continue;

                    if ($scaling)
                        $causesScaling = true;

                    if ($interactive)
                    {
                        $link   = '<a href="?spell='.$itemSpells->id.'">%s</a>';
                        $parsed = preg_replace_callback('/([^;]*)(&nbsp;<small>.*?<\/small>)([^&]*)/i', function($m) use($link) {
                                $m[1] = $m[1] ? sprintf($link, $m[1]) : '';
                                $m[3] = $m[3] ? sprintf($link, $m[3]) : '';
                                return $m[1].$m[2].$m[3];
                            }, $parsed, -1, $nMatches
                        );

                        if (!$nMatches)
                            $parsed = sprintf($link, $parsed);
                    }

                    $green[] = Lang::item('trigger', $itemSpellsAndTrigger[$itemSpells->id][0]).$parsed.$itemSpellsAndTrigger[$itemSpells->id][1];
                }
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
        if ($setId = $this->getField('itemset'))
        {
            $condition = [
                ['refSetId', $setId],
             // ['quality',  $this->curTpl['quality']],
                ['minLevel', $this->curTpl['itemLevel'], '<='],
                ['maxLevel', $this->curTpl['itemLevel'], '>=']
            ];

            $itemset = new ItemsetList($condition);
            if (!$itemset->error && $itemset->pieceToSet)
            {
                // handle special cases where:
                // > itemset has items of different qualities (handled by not limiting for this in the initial query)
                // > itemset is virtual and multiple instances have the same itemLevel but not quality (filter below)
                foreach ($itemset->iterate() as $id => $__)
                {
                    if ($itemset->getField('quality') == $this->curTpl['quality'])
                    {
                        $itemset->pieceToSet = array_filter($itemset->pieceToSet, function($x) use ($id) { return $id == $x; });
                        break;
                    }
                }

                $pieces = DB::Aowow()->select(
                   'SELECT   b.`id` AS ARRAY_KEY, b.`name_loc0`, b.`name_loc2`, b.`name_loc3`, b.`name_loc4`, b.`name_loc6`, b.`name_loc8`, GROUP_CONCAT(a.`id` SEPARATOR ":") AS "equiv"
                    FROM     ?_items a, ?_items b
                    WHERE    a.`slotBak` = b.`slotBak` AND a.`itemset` = b.`itemset` AND b.`id` IN (?a)
                    GROUP BY b.`id`',
                    array_keys($itemset->pieceToSet)
                );

                foreach ($pieces as $k => &$p)
                    $p = '<span><!--si'.$p['equiv'].'--><a href="?item='.$k.'">'.Util::localizedString($p, 'name').'</a></span>';

                $xSet = '<br /><span class="q">'.Lang::item('setName', ['<a href="?itemset='.$itemset->id.'" class="q">'.$itemset->getField('name', true).'</a>', 0, count($pieces)]).'</span>';

                if ($skId = $itemset->getField('skillId'))  // bonus requires skill to activate
                {
                    $xSet .= '<br />'.sprintf(Lang::game('requires'), '<a href="?skills='.$skId.'" class="q1">'.SkillList::getName($skId).'</a>');

                    if ($_ = $itemset->getField('skillLevel'))
                        $xSet .= ' ('.$_.')';

                    $xSet .= '<br />';
                }

                // list pieces
                $xSet .= '<div class="q0 indent">'.implode('<br />', $pieces).'</div><br />';

                // get bonuses
                $setSpellsAndIdx = [];
                for ($j = 1; $j <= 8; $j++)
                    if ($_ = $itemset->getField('spell'.$j))
                        $setSpellsAndIdx[$_] = $j;

                $setSpells = [];
                if ($setSpellsAndIdx)
                {
                    $boni = new SpellList(array(['s.id', array_keys($setSpellsAndIdx)]));
                    foreach ($boni->iterate() as $__)
                    {
                        [$parsed, $_, $scaling] = $boni->parseText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL);
                        if ($scaling && $interactive)
                            $causesScaling = true;

                        $setSpells[] = array(
                            'tooltip' => $parsed,
                            'entry'   => $itemset->getField('spell'.$setSpellsAndIdx[$boni->id]),
                            'bonus'   => $itemset->getField('bonus'.$setSpellsAndIdx[$boni->id])
                        );
                    }
                }

                // sort and list bonuses
                $xSet .= '<span class="q0">';
                for ($i = 0; $i < count($setSpells); $i++)
                {
                    for ($j = $i; $j < count($setSpells); $j++)
                    {
                        if ($setSpells[$j]['bonus'] >= $setSpells[$i]['bonus'])
                            continue;

                        $tmp = $setSpells[$i];
                        $setSpells[$i] = $setSpells[$j];
                        $setSpells[$j] = $tmp;
                    }
                    $xSet .= '<span>'.Lang::item('setBonus', [$setSpells[$i]['bonus'], '<a href="?spell='.$setSpells[$i]['entry'].'">'.$setSpells[$i]['tooltip'].'</a>']).'</span>';
                    if ($i < count($setSpells) - 1)
                        $xSet .= '<br />';
                }
                $xSet .= '</span>';
            }
        }

        // recipes, vanity pets, mounts
        if ($this->canTeachSpell())
        {
            $craftSpell = new SpellList(array(['s.id', intVal($this->curTpl['spellId2'])]));
            if (!$craftSpell->error)
            {
                $xCraft = '';
                if ($desc = $this->getField('description', true))
                    $x .= '<span class="q2">'.Lang::item('trigger', SPELL_TRIGGER_USE).' <a href="?spell='.$this->curTpl['spellId2'].'">'.$desc.'</a></span><br />';

                // recipe handling (some stray Techniques have subclass == 0), place at bottom of tooltipp
                if ($_class == ITEM_CLASS_RECIPE || $this->curTpl['bagFamily'] == 16)
                {
                    if ($craftSpell->canCreateItem())
                    {
                        $craftItem  = new ItemList(array(['i.id', (int)$craftSpell->curTpl['effect1CreateItemId']]));
                        if (!$craftItem->error)
                            if ($itemTT = $craftItem->renderTooltip($interactive, $this->id))
                                $xCraft .= '<div><br />'.$itemTT.'</div>';
                    }

                    $reagentItems = [];
                    for ($i = 1; $i <= 8; $i++)
                        if ($rId = $craftSpell->getField('reagent'.$i))
                            $reagentItems[$rId] = $craftSpell->getField('reagentCount'.$i);

                    if ($reagentItems)
                    {
                        $reagents = new ItemList(array(['i.id', array_keys($reagentItems)]));
                        $reqReag  = [];

                        foreach ($reagents->iterate() as $__)
                            $reqReag[] = '<a href="?item='.$reagents->id.'">'.$reagents->getField('name', true).'</a> ('.$reagentItems[$reagents->id].')';

                        $xCraft .= '<div class="q1 whtt-reagents"><br />'.Lang::game('requires2').' '.implode(', ', $reqReag).'</div>';
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
        if ($this->curTpl['description_loc0'] && !$this->canTeachSpell())
            $xMisc[] = '<span class="q">"'.Util::parseHtmlText($this->getField('description', true), false).'"</span>';

        // readable
        if ($this->curTpl['pageTextId'])
            $xMisc[] = '<span class="q2">'.Lang::item('readClick').'</span>';

        // charges
        for ($i = 1; $i < 6; $i++)
        {
            if (in_array($this->curTpl['spellTrigger'.$i], [SPELL_TRIGGER_USE, SPELL_TRIGGER_SOULSTONE, SPELL_TRIGGER_USE_NODELAY, SPELL_TRIGGER_LEARN]) && $this->curTpl['spellCharges'.$i])
            {
                $xMisc[] = '<span class="q1">'.Lang::item('charges', [abs($this->curTpl['spellCharges'.$i])]).'</span>';
                break;
            }
        }

        // list required reagents
        if (isset($xCraft))
            $xMisc[] = $xCraft;

        if ($xMisc)
            $x .= implode('<br />', $xMisc);

        if ($sp = $this->curTpl['sellPrice'])
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
            $x .= ':' . ($this->ssd[$itemId]['maxLevel'] ?? ($causesScaling ? MAX_LEVEL : 1));
            // scaleCurLevel
            $x .= ':' . ($this->ssd[$itemId]['maxLevel'] ?? ($_reqLvl ?: MAX_LEVEL));
            // scaleDist
            if ($this->curTpl['scalingStatDistribution'])
                $x .= ':' . $this->curTpl['scalingStatDistribution'];
            // scaleFlags
            if ($this->curTpl['scalingStatValue'])
                $x .= ':' . $this->curTpl['scalingStatValue'];
            $x .= '-->';
        }

        return $x;
    }

    public function getRandEnchantForItem(int $randId) : bool
    {
        // is it available for this item? .. does it even exist?!
        if (empty($this->enhanceR))
            if (DB::World()->selectCell('SELECT 1 FROM item_enchantment_template WHERE `entry` = ?d AND `ench` = ?d', abs($this->getField('randomEnchant')), abs($randId)))
                if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomenchant WHERE `id` = ?d', $randId))
                    $this->enhanceR = $_;

        return !empty($this->enhanceR);
    }

    // from Trinity
    public function generateEnchSuffixFactor() : float
    {
        if (empty($this->randPropPoints[$this->curTpl['itemLevel']]))
            $this->randPropPoints[$this->curTpl['itemLevel']] = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomproppoints WHERE `id` = ?', $this->curTpl['itemLevel']);

        $rpp = &$this->randPropPoints[$this->curTpl['itemLevel']];

        if (!$rpp)
            return 0.0;

        $fieldIdx = match((int)$this->curTpl['slot'])
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
        return match((int)$this->curTpl['quality'])
        {
            ITEM_QUALITY_UNCOMMON => $rpp['uncommon'.$fieldIdx] / 10000,
            ITEM_QUALITY_RARE     => $rpp['rare'.$fieldIdx]     / 10000,
            ITEM_QUALITY_EPIC     => $rpp['epic'.$fieldIdx]     / 10000,
            default               => 0.0                    // qualities that don't have random properties
        };
    }

    public function extendJsonStats() : void
    {
        $enchantments = [];                                 // buffer Ids for lookup id => src; src>0: socketBonus; src<0: gemEnchant

        foreach ($this->iterate() as $__)
        {
            // fetch and add socketbonusstats
            if (!empty($this->json[$this->id]['socketbonus']))
                $enchantments[$this->json[$this->id]['socketbonus']][] = $this->id;

            // Item is a gem (don't mix with sockets)
            if ($geId = $this->curTpl['gemEnchantmentId'])
                $enchantments[$geId][] = -$this->id;
        }

        if ($enchantments)
        {
            $eStats = DB::Aowow()->select('SELECT *, `typeId` AS ARRAY_KEY FROM ?_item_stats WHERE `type` = ?d AND `typeId` IN (?a)', Type::ENCHANTMENT, array_keys($enchantments));
            Util::checkNumeric($eStats);

            // and merge enchantments back
            foreach ($enchantments as $eId => $items)
            {
                if (empty($eStats[$eId]))
                    continue;

                foreach ($items as $item)
                {
                    if ($item > 0)                          // apply socketBonus
                        $this->json[$item]['socketbonusstat'] = array_filter($eStats[$eId]);
                    else /* if ($item < 0) */               // apply gemEnchantment
                        Util::arraySumByKey($this->json[-$item], array_filter($eStats[$eId]));
                }
            }
        }

        foreach ($this->json as $item => $json)
            foreach ($json as $k => $v)
                if (!$v && !in_array($k, ['classs', 'subclass', 'quality', 'side', 'gearscore']))
                    unset($this->json[$item][$k]);
    }

    public function getOnUseStats() : ?StatsContainer
    {
        if ($this->curTpl['class'] != ITEM_CLASS_CONSUMABLE)
            return null;

        $onUseStats = new StatsContainer();

        // convert Spells
        for ($h = 1; $h <= 5; $h++)
        {
            if ($this->curTpl['spellId'.$h] <= 0)
                continue;

            if ($this->curTpl['spellTrigger'.$h] != SPELL_TRIGGER_USE)
                continue;

            if ($spell = DB::Aowow()->selectRow(
               'SELECT `effect1Id`, `effect1TriggerSpell`, `effect1AuraId`, `effect1MiscValue`, `effect1BasePoints`, `effect1DieSides`,
                       `effect2Id`, `effect2TriggerSpell`, `effect2AuraId`, `effect2MiscValue`, `effect2BasePoints`, `effect2DieSides`,
                       `effect3Id`, `effect3TriggerSpell`, `effect3AuraId`, `effect3MiscValue`, `effect3BasePoints`, `effect3DieSides`
                FROM   ?_spell
                WHERE  `id` = ?d',
                $this->curTpl['spellId'.$h]
            ))
                $onUseStats->fromSpell($spell);
        }

        return $onUseStats;
    }

    public function getSourceData(int $id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($id && $id != $this->id)
                continue;

            $data[$this->id] = array(
                'n'    => $this->getField('name', true),
                't'    => Type::ITEM,
                'ti'   => $this->id,
                'q'    => $this->curTpl['quality'],
             // 'p'    => PvP [NYI]
                'icon' => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    private function canTeachSpell() : bool
    {
        if (!in_array($this->curTpl['spellId1'], LEARN_SPELLS))
            return false;

        // needs learnable spell
        if (!$this->curTpl['spellId2'])
            return false;

        return true;
    }

    private function getFeralAP() : float
    {
        // must be weapon
        if ($this->curTpl['class'] != ITEM_CLASS_WEAPON)
            return 0.0;

        // thats fucked up..
        if (!$this->curTpl['delay'])
            return 0.0;

        // must have enough damage
        $dps = ($this->curTpl['tplDmgMin1'] + $this->curTpl['dmgMin2'] + $this->curTpl['tplDmgMax1'] + $this->curTpl['dmgMax2']) / (2 * $this->curTpl['delay'] / 1000);
        if ($dps <= 54.8)
            return 0.0;

        $subClasses = [ITEM_SUBCLASS_MISC_WEAPON];
        $weaponTypeMask = DB::Aowow()->selectCell('SELECT `weaponTypeMask` FROM ?_classes WHERE `id` = ?d', ChrClass::DRUID->value);
        if ($weaponTypeMask)
            for ($i = 0; $i < 21; $i++)
                if ($weaponTypeMask & (1 << $i))
                    $subClasses[] = $i;

        // cannot be used by druids
        if (!in_array($this->curTpl['subClass'], $subClasses))
            return 0.0;

        return round(($dps - 54.8) * 14);
    }

    public function isRangedWeapon() : bool
    {
        if ($this->curTpl['class'] != ITEM_CLASS_WEAPON)
            return false;

        return in_array($this->curTpl['subClassBak'], [ITEM_SUBCLASS_BOW, ITEM_SUBCLASS_GUN, ITEM_SUBCLASS_THROWN, ITEM_SUBCLASS_CROSSBOW, ITEM_SUBCLASS_WAND]);
    }

    public function isBodyArmor() : bool
    {
        if ($this->curTpl['class'] != ITEM_CLASS_ARMOR)
            return false;

        return in_array($this->curTpl['subClassBak'], [ITEM_SUBCLASS_CLOTH_ARMOR, ITEM_SUBCLASS_LEATHER_ARMOR, ITEM_SUBCLASS_MAIL_ARMOR, ITEM_SUBCLASS_PLATE_ARMOR]);
    }

    public function isDisplayable() : bool
    {
        if (!$this->curTpl['displayId'])
            return false;

        return in_array($this->curTpl['slot'], array(
            INVTYPE_HEAD,           INVTYPE_SHOULDERS,      INVTYPE_BODY,           INVTYPE_CHEST,          INVTYPE_WAIST,          INVTYPE_LEGS,           INVTYPE_FEET,           INVTYPE_WRISTS,
            INVTYPE_HANDS,          INVTYPE_WEAPON,         INVTYPE_SHIELD,         INVTYPE_RANGED,         INVTYPE_CLOAK,          INVTYPE_2HWEAPON,       INVTYPE_TABARD,         INVTYPE_ROBE,
            INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPONOFFHAND,  INVTYPE_HOLDABLE,       INVTYPE_THROWN,         INVTYPE_RANGEDRIGHT));
    }

    private function formatRating(int $statId, int $itemMod, int $qty, bool $interactive = false, bool &$scaling = false) : string
    {
        // clamp level range
        $ssdLvl = isset($this->ssd[$this->id]) ? $this->ssd[$this->id]['maxLevel'] : 1;
        $reqLvl = $this->curTpl['requiredLevel'] > 1 ? $this->curTpl['requiredLevel'] : MAX_LEVEL;
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

    private function getSSDMod(string $type) : int
    {
        $mask = $this->curTpl['scalingStatValue'];

        $mask &= match ($type)
        {
            'stats' => 0x04001F,
            'armor' => 0xF001E0,
            'dps'   => 0x007E00,
            'spell' => 0x008000,
            'fap'   => 0x010000,                            // unused
            default => 0x0
        };

        $field = null;
        for ($i = 0; $i < count(Util::$ssdMaskFields); $i++)
            if ($mask & (1 << $i))
                $field = Util::$ssdMaskFields[$i];

        return $field ? DB::Aowow()->selectCell('SELECT ?# FROM ?_scalingstatvalues WHERE `id` = ?d', $field, $this->ssd[$this->id]['maxLevel']) : 0;
    }

    private function initScalingStats() : void
    {
        $this->ssd[$this->id] = DB::Aowow()->selectRow('SELECT * FROM ?_scalingstatdistribution WHERE `id` = ?d', $this->curTpl['scalingStatDistribution']);

        if (!$this->ssd[$this->id])
            return;

        // stats and ratings
        for ($i = 1; $i <= 10; $i++)
        {
            if ($this->ssd[$this->id]['statMod'.$i] <= 0)
            {
                $this->templates[$this->id]['statType'.$i]  = 0;
                $this->templates[$this->id]['statValue'.$i] = 0;
            }
            else
            {
                $this->templates[$this->id]['statType'.$i]  = $this->ssd[$this->id]['statMod'.$i];
                $this->templates[$this->id]['statValue'.$i] = intVal(($this->getSSDMod('stats') * $this->ssd[$this->id]['modifier'.$i]) / 10000);
            }
        }

        // armor: only replace if set
        if ($ssvArmor = $this->getSSDMod('armor'))
            $this->templates[$this->id]['armor'] = $ssvArmor;

        // if set dpsMod in ScalingStatValue use it for min/max damage
        // mle: 20% range / rgd: 30% range
        if ($extraDPS = $this->getSSDMod('dps'))            // dmg_x2 not used for heirlooms
        {
            $range   = isset($this->json[$this->id]['rgddps']) ? 0.3 : 0.2;
            $average = $extraDPS * $this->curTpl['delay'] / 1000;

            $this->templates[$this->id]['tplDmgMin1'] = floor((1 - $range) * $average);
            $this->templates[$this->id]['tplDmgMax1'] = floor((1 + $range) * $average);
        }

        // apply Spell Power from ScalingStatValue if set
        if ($spellBonus = $this->getSSDMod('spell'))
        {
            $this->templates[$this->id]['statType10']  = ITEM_MOD_SPELL_POWER;
            $this->templates[$this->id]['statValue10'] = $spellBonus;
        }
    }

    public function initSubItems() : void
    {
        if (!array_keys($this->templates))
            return;

        $subItemIds = [];
        foreach ($this->iterate() as $__)
            if ($_ = $this->getField('randomEnchant'))
                $subItemIds[abs($_)] = $_;

        if (!$subItemIds)
            return;

        // remember: id < 0: randomSuffix; id > 0: randomProperty
        $subItemTpls = DB::World()->select(
           'SELECT CAST( `entry` AS SIGNED) AS ARRAY_KEY, CAST( `ench` AS SIGNED) AS ARRAY_KEY2, `chance` FROM item_enchantment_template WHERE `entry` IN (?a) UNION
            SELECT CAST(-`entry` AS SIGNED) AS ARRAY_KEY, CAST(-`ench` AS SIGNED) AS ARRAY_KEY2, `chance` FROM item_enchantment_template WHERE `entry` IN (?a)',
            array_keys(array_filter($subItemIds, fn($v) => $v > 0)) ?: [0],
            array_keys(array_filter($subItemIds, fn($v) => $v < 0)) ?: [0]
        );

        $randIds = [];
        foreach ($subItemTpls as $tpl)
            $randIds = array_merge($randIds, array_keys($tpl));

        if (!$randIds)
            return;

        $randEnchants = DB::Aowow()->select('SELECT *, `id` AS ARRAY_KEY FROM ?_itemrandomenchant WHERE `id` IN (?a)', $randIds);
        $enchIds = array_unique(array_merge(
            array_column($randEnchants, 'enchantId1'),
            array_column($randEnchants, 'enchantId2'),
            array_column($randEnchants, 'enchantId3'),
            array_column($randEnchants, 'enchantId4'),
            array_column($randEnchants, 'enchantId5')
        ));

        $enchants = new EnchantmentList(array(['id', $enchIds]));
        foreach ($enchants->iterate() as $eId => $_)
        {
            $this->rndEnchIds[$eId] = array(
                'text'  => $enchants->getField('name', true),
                'stats' => $enchants->getStatGainForCurrent()
            );
        }

        foreach ($this->iterate() as $mstItem => $__)
        {
            if (!$this->getField('randomEnchant'))
                continue;

            if (empty($subItemTpls[$this->getField('randomEnchant')]))
                continue;

            foreach ($subItemTpls[$this->getField('randomEnchant')] as $subId => $data)
            {
                if (empty($randEnchants[$subId]))
                    continue;

                $data      = array_merge($randEnchants[$subId], $data);
                $jsonEquip = [];
                $jsonText  = [];

                for ($i = 1; $i < 6; $i++)
                {
                    $enchId = $data['enchantId'.$i];
                    if ($enchId <= 0 || empty($this->rndEnchIds[$enchId]))
                        continue;

                    if ($data['allocationPct'.$i] > 0)      // RandomSuffix: scaling Enchantment; enchId < 0
                    {
                        $qty   = intVal($data['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                        $stats = array_fill_keys(array_keys($this->rndEnchIds[$enchId]['stats']), $qty);

                        $jsonText[$enchId] = str_replace('$i', $qty, $this->rndEnchIds[$enchId]['text']);
                        Util::arraySumByKey($jsonEquip, $stats);
                    }
                    else                                    // RandomProperty: static Enchantment; enchId > 0
                    {
                        $jsonText[$enchId] = $this->rndEnchIds[$enchId]['text'];
                        Util::arraySumByKey($jsonEquip, $this->rndEnchIds[$enchId]['stats']);
                    }
                }

                $this->subItems[$mstItem][$subId] = array(
                    'name'          => Util::localizedString($data, 'name'),
                    'enchantment'   => $jsonText,
                    'jsonequip'     => $jsonEquip,
                    'chance'        => $data['chance']      // hmm, only needed for item detail page...
                );
            }

            if (!empty($this->subItems[$mstItem]))
                $this->json[$mstItem]['subitems'] = $this->subItems[$mstItem];
        }
    }

    public function getScoreTotal(int $class = 0, array $spec = [], int $mhItem = 0, int $ohItem = 0) : int
    {
        if (!$class || !$spec)
            return array_sum(array_column($this->json, 'gearscore'));

        $score    = 0.0;
        $mh = $oh = [];

        foreach ($this->json as $j)
        {
            if ($j['id'] == $mhItem)
                $mh = $j;
            else if ($j['id'] == $ohItem)
                $oh = $j;
            else if (!empty($j['gearscore']))
            {
                if ($j['slot'] == INVTYPE_RELIC)
                    $score += 20;

                $score += round($j['gearscore']);
            }
        }

        $score += array_sum(Util::fixWeaponScores($class, $spec, $mh, $oh));

        return $score;
    }

    private function initJsonStats() : void
    {
        $class    = $this->curTpl['class'];
        $subclass = $this->curTpl['subClass'];

        $json = array(
            'id'          => $this->id,
            'quality'     => ITEM_QUALITY_HEIRLOOM - $this->curTpl['quality'],
            'classs'      => $class,
            'subclass'    => $subclass,
            'subsubclass' => $this->curTpl['subSubClass'],
            'heroic'      => ($this->curTpl['flags'] & ITEM_FLAG_HEROIC) >> 3,
            'side'        => $this->curTpl['flagsExtra'] & 0x3 ? SIDE_BOTH - ($this->curTpl['flagsExtra'] & 0x3) : ChrRace::sideFromMask($this->curTpl['requiredRace']),
            'slot'        => $this->curTpl['slot'],
            'slotbak'     => $this->curTpl['slotBak'],
            'level'       => $this->curTpl['itemLevel'],
            'reqlevel'    => $this->curTpl['requiredLevel'],
            'displayid'   => $this->curTpl['displayId'],
            'holres'      => $this->curTpl['resHoly'],
            'firres'      => $this->curTpl['resFire'],
            'natres'      => $this->curTpl['resNature'],
            'frores'      => $this->curTpl['resFrost'],
            'shares'      => $this->curTpl['resShadow'],
            'arcres'      => $this->curTpl['resArcane'],
            'armorbonus'  => $class != ITEM_CLASS_ARMOR ? 0 : max(0, intVal($this->curTpl['armorDamageModifier'])),
            'armor'       => $this->curTpl['tplArmor'],
            'dura'        => $this->curTpl['durability'],
            'itemset'     => $this->curTpl['itemset'],
            'socket1'     => $this->curTpl['socketColor1'],
            'socket2'     => $this->curTpl['socketColor2'],
            'socket3'     => $this->curTpl['socketColor3'],
            'nsockets'    => ($this->curTpl['socketColor1'] > 0 ? 1 : 0) + ($this->curTpl['socketColor2'] > 0 ? 1 : 0) + ($this->curTpl['socketColor3'] > 0 ? 1 : 0),
            'socketbonus' => $this->curTpl['socketBonus'],
            'scadist'     => $this->curTpl['scalingStatDistribution'],
            'scaflags'    => $this->curTpl['scalingStatValue']
        );

        $json = array_map('intval', $json);

        $json['name'] = $this->getField('name', true);
        $json['icon'] = $this->curTpl['iconString'];

        if ($class == ITEM_CLASS_AMMUNITION)
            $json['dps'] = round(($this->curTpl['tplDmgMin1'] + $this->curTpl['dmgMin2'] + $this->curTpl['tplDmgMax1'] + $this->curTpl['dmgMax2']) / 2, 2);
        else if ($class == ITEM_CLASS_WEAPON)
        {
            $json['dmgtype1'] = (int)$this->curTpl['dmgType1'];
            $json['dmgmin1']  = (int)($this->curTpl['tplDmgMin1'] + $this->curTpl['dmgMin2']);
            $json['dmgmax1']  = (int)($this->curTpl['tplDmgMax1'] + $this->curTpl['dmgMax2']);
            $json['speed']    = round($this->curTpl['delay'] / 1000, 2);
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

            if ($fap = $this->getFeralAP())
                $json['feratkpwr'] = $fap;
        }

        if ($class == ITEM_CLASS_ARMOR || $class == ITEM_CLASS_WEAPON)
            $json['gearscore'] = Util::getEquipmentScore($json['level'], $this->getField('quality'), $json['slot'], $json['nsockets']);
        else if ($class == ITEM_CLASS_GEM)
            $json['gearscore'] = Util::getGemScore($json['level'], $this->getField('quality'), $this->getField('requiredSkill') == SKILL_JEWELCRAFTING, $this->id);

        // clear zero-values afterwards
        foreach ($json as $k => $v)
            if (!$v && !in_array($k, ['classs', 'subclass', 'quality', 'side', 'gearscore']))
                unset($json[$k]);

        $this->json[$json['id']] = $json;
    }
}


class ItemListFilter extends Filter
{
    public const /* int */ GROUP_BY_NONE   = 0;
    public const /* int */ GROUP_BY_SLOT   = 1;
    public const /* int */ GROUP_BY_LEVEL  = 2;
    public const /* int */ GROUP_BY_SOURCE = 3;

    private array  $ubFilter     = [];                      // usable-by - limit weapon/armor selection per CharClass - itemClass => available itemsubclasses
    private string $extCostQuery = 'SELECT `item` FROM npc_vendor            WHERE `extendedCost` IN (?a) UNION
                                    SELECT `item` FROM game_event_npc_vendor WHERE `extendedCost` IN (?a)';

    protected string $type  = 'items';
    protected static array $enums = array(
         16 => parent::ENUM_ZONE,                           // drops in zone
         17 => parent::ENUM_FACTION,                        // requiresrepwith
         99 => parent::ENUM_PROFESSION,                     // requiresprof
         86 => parent::ENUM_PROFESSION,                     // craftedprof
         87 => parent::ENUM_PROFESSION,                     // reagentforability
        105 => parent::ENUM_HEROICDUNGEON,                  // drops in nh dungeon
        106 => parent::ENUM_HEROICDUNGEON,                  // drops in hc dungeon
        126 => parent::ENUM_ZONE,                           // rewardedbyquestin
        147 => parent::ENUM_MULTIMODERAID,                  // drops in nh raid 10
        148 => parent::ENUM_MULTIMODERAID,                  // drops in nh raid 25
        149 => parent::ENUM_HEROICRAID,                     // drops in hc raid 10
        150 => parent::ENUM_HEROICRAID,                     // drops in hc raid 25
        152 => parent::ENUM_CLASSS,                         // class-specific
        153 => parent::ENUM_RACE,                           // race-specific
        160 => parent::ENUM_EVENT,                          // relatedevent
        169 => parent::ENUM_EVENT,                          // requiresevent
        158 => parent::ENUM_CURRENCY,                       // purchasablewithcurrency
        118 => array(                                       // itemcurrency
            52027, 52030, 52026, 52029, 52025, 52028, 47242, 47557, 47558, 47559, 45632, 45633, 45634, 45635, 45636, 45637, 45638, 45639, 45640, 45641,
            45642, 45643, 45644, 45645, 45646, 45647, 45648, 45649, 45650, 45651, 45652, 45653, 45654, 45655, 45656, 45657, 45658, 45659, 45660, 45661,
            40625, 40626, 40627, 40610, 40611, 40612, 40631, 40632, 40633, 40628, 40629, 40630, 40613, 40614, 40615, 40616, 40617, 40618, 40619, 40620,
            40621, 40634, 40635, 40636, 40637, 40638, 40639, 40622, 40623, 40624, 34853, 34854, 34855, 34856, 34857, 34858, 34848, 34851, 34852, 31089,
            31091, 31090, 31092, 31094, 31093, 31097, 31095, 31096, 31098, 31100, 31099, 31101, 31103, 31102, 30236, 30237, 30238, 30239, 30240, 30241,
            30242, 30243, 30244, 30245, 30246, 30247, 30248, 30249, 30250, 29754, 29753, 29755, 29757, 29758, 29756, 29760, 29761, 29759, 29766, 29767,
            29765, 29763, 29764, 29762, 34169, 34186, 34245, 34332, 34339, 34345, 34244, 34208, 34180, 34229, 34350, 34342, 34211, 34243, 34216, 34167,
            34170, 34192, 34233, 34234, 34202, 34195, 34209, 34193, 34212, 34351, 34215
        ),
        163 => array(                                       // enchantment mats
            34057, 22445, 11176, 34052, 11082, 34055, 16203, 10939, 11135, 11175, 22446, 16204, 34054, 14344, 11084, 11139, 22449, 11178, 10998, 34056,
            16202, 10938, 11134, 11174, 22447, 20725, 14343, 34053, 10978, 11138, 22448, 11177, 11083, 10940, 11137, 22450
        ),
         91 => array(                                       // tool
                3,    14,   162,   168,   141,     2,     4,   169,   161,    15,   167,    81,    21,   165,    12,    62,    10,   101,   189,     6,
               63,    41,     8,     7,   190,     9,   166,   121,     5
        ),
         66 => array(                                       // profession specialization
            1 => -1,
            2 => [ 9788,  9787, 17041, 17040, 17039                                                        ],
            3 => -1,
            4 => -1,
            5 => [20219, 20222                                                                             ],
            6 => -1,
            7 => -1,
            8 => [10656, 10658, 10660                                                                      ],
            9 => -1,
           10 => [26798, 26801, 26797                                                                      ],
           11 => [ 9788,  9787, 17041, 17040, 17039, 20219, 20222, 10656, 10658, 10660, 26798, 26801, 26797],  // i know, i know .. lazy as fuck
           12 => false,
           13 => -1,
           14 => -1,
           15 => -1
       ),
       128 => array(                                        // source
             1 => true,                                     // Any
             2 => false,                                    // None
             3 => SRC_CRAFTED,
             4 => SRC_DROP,
             5 => SRC_PVP,
             6 => SRC_QUEST,
             7 => SRC_VENDOR,
             9 => SRC_STARTER,
            10 => SRC_EVENT,
            11 => SRC_ACHIEVEMENT,
            12 => SRC_FISHING
        )
    );

    protected static array $genericFilter = array(
          2 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               1                 ], // bindonpickup [yn]
          3 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               2                 ], // bindonequip [yn]
          4 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               3                 ], // bindonuse [yn]
          5 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'bonding',               [4, 5]            ], // questitem [yn]
          6 => [parent::CR_CALLBACK,  'cbQuestRelation',        null,                    null              ], // startsquest [side]
          7 => [parent::CR_BOOLEAN,   'description_loc0',       true                                       ], // hasflavortext
          8 => [parent::CR_BOOLEAN,   'requiredDisenchantSkill'                                            ], // disenchantable
          9 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_CONJURED                         ], // conjureditem
         10 => [parent::CR_BOOLEAN,   'lockId'                                                             ], // locked
         11 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_OPENABLE                         ], // openable
         12 => [parent::CR_BOOLEAN,   'itemset'                                                            ], // partofset
         13 => [parent::CR_BOOLEAN,   'randomEnchant'                                                      ], // randomlyenchanted
         14 => [parent::CR_BOOLEAN,   'pageTextId'                                                         ], // readable
         15 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'maxCount',              1                 ], // unique [yn]
         16 => [parent::CR_CALLBACK,  'cbDropsInZone',          null,                    null              ], // dropsin [zone]
         17 => [parent::CR_ENUM,      'requiredFaction',        true,                    true              ], // requiresrepwith
         18 => [parent::CR_CALLBACK,  'cbFactionQuestReward',   null,                    null              ], // rewardedbyfactionquest [side]
         20 => [parent::CR_NUMERIC,   'is.str',                 NUM_CAST_INT,            true              ], // str
         21 => [parent::CR_NUMERIC,   'is.agi',                 NUM_CAST_INT,            true              ], // agi
         22 => [parent::CR_NUMERIC,   'is.sta',                 NUM_CAST_INT,            true              ], // sta
         23 => [parent::CR_NUMERIC,   'is.int',                 NUM_CAST_INT,            true              ], // int
         24 => [parent::CR_NUMERIC,   'is.spi',                 NUM_CAST_INT,            true              ], // spi
         25 => [parent::CR_NUMERIC,   'is.arcres',              NUM_CAST_INT,            true              ], // arcres
         26 => [parent::CR_NUMERIC,   'is.firres',              NUM_CAST_INT,            true              ], // firres
         27 => [parent::CR_NUMERIC,   'is.natres',              NUM_CAST_INT,            true              ], // natres
         28 => [parent::CR_NUMERIC,   'is.frores',              NUM_CAST_INT,            true              ], // frores
         29 => [parent::CR_NUMERIC,   'is.shares',              NUM_CAST_INT,            true              ], // shares
         30 => [parent::CR_NUMERIC,   'is.holres',              NUM_CAST_INT,            true              ], // holres
         32 => [parent::CR_NUMERIC,   'is.dps',                 NUM_CAST_FLOAT,          true              ], // dps
         33 => [parent::CR_NUMERIC,   'is.dmgmin1',             NUM_CAST_INT,            true              ], // dmgmin1
         34 => [parent::CR_NUMERIC,   'is.dmgmax1',             NUM_CAST_INT,            true              ], // dmgmax1
         35 => [parent::CR_CALLBACK,  'cbDamageType',           null,                    null              ], // damagetype [enum]
         36 => [parent::CR_NUMERIC,   'is.speed',               NUM_CAST_FLOAT,          true              ], // speed
         37 => [parent::CR_NUMERIC,   'is.mleatkpwr',           NUM_CAST_INT,            true              ], // mleatkpwr
         38 => [parent::CR_NUMERIC,   'is.rgdatkpwr',           NUM_CAST_INT,            true              ], // rgdatkpwr
         39 => [parent::CR_NUMERIC,   'is.rgdhitrtng',          NUM_CAST_INT,            true              ], // rgdhitrtng
         40 => [parent::CR_NUMERIC,   'is.rgdcritstrkrtng',     NUM_CAST_INT,            true              ], // rgdcritstrkrtng
         41 => [parent::CR_NUMERIC,   'is.armor',               NUM_CAST_INT,            true              ], // armor
         42 => [parent::CR_NUMERIC,   'is.defrtng',             NUM_CAST_INT,            true              ], // defrtng
         43 => [parent::CR_NUMERIC,   'is.block',               NUM_CAST_INT,            true              ], // block
         44 => [parent::CR_NUMERIC,   'is.blockrtng',           NUM_CAST_INT,            true              ], // blockrtng
         45 => [parent::CR_NUMERIC,   'is.dodgertng',           NUM_CAST_INT,            true              ], // dodgertng
         46 => [parent::CR_NUMERIC,   'is.parryrtng',           NUM_CAST_INT,            true              ], // parryrtng
         48 => [parent::CR_NUMERIC,   'is.splhitrtng',          NUM_CAST_INT,            true              ], // splhitrtng
         49 => [parent::CR_NUMERIC,   'is.splcritstrkrtng',     NUM_CAST_INT,            true              ], // splcritstrkrtng
         50 => [parent::CR_NUMERIC,   'is.splheal',             NUM_CAST_INT,            true              ], // splheal
         51 => [parent::CR_NUMERIC,   'is.spldmg',              NUM_CAST_INT,            true              ], // spldmg
         52 => [parent::CR_NUMERIC,   'is.arcsplpwr',           NUM_CAST_INT,            true              ], // arcsplpwr
         53 => [parent::CR_NUMERIC,   'is.firsplpwr',           NUM_CAST_INT,            true              ], // firsplpwr
         54 => [parent::CR_NUMERIC,   'is.frosplpwr',           NUM_CAST_INT,            true              ], // frosplpwr
         55 => [parent::CR_NUMERIC,   'is.holsplpwr',           NUM_CAST_INT,            true              ], // holsplpwr
         56 => [parent::CR_NUMERIC,   'is.natsplpwr',           NUM_CAST_INT,            true              ], // natsplpwr
         57 => [parent::CR_NUMERIC,   'is.shasplpwr',           NUM_CAST_INT,            true              ], // shasplpwr
         59 => [parent::CR_NUMERIC,   'durability',             NUM_CAST_INT,            true              ], // dura
         60 => [parent::CR_NUMERIC,   'is.healthrgn',           NUM_CAST_INT,            true              ], // healthrgn
         61 => [parent::CR_NUMERIC,   'is.manargn',             NUM_CAST_INT,            true              ], // manargn
         62 => [parent::CR_CALLBACK,  'cbCooldown',             null,                    null              ], // cooldown [op] [int]
         63 => [parent::CR_NUMERIC,   'buyPrice',               NUM_CAST_INT,            true              ], // buyprice
         64 => [parent::CR_NUMERIC,   'sellPrice',              NUM_CAST_INT,            true              ], // sellprice
         65 => [parent::CR_CALLBACK,  'cbAvgMoneyContent',      null,                    null              ], // avgmoney [op] [int]
         66 => [parent::CR_ENUM,      'requiredSpell'                                                      ], // requiresprofspec
         68 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_DISENCHANTMENT,      null              ], // otdisenchanting [yn]
         69 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_FISHING,             null              ], // otfishing [yn]
         70 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_GATHERING,           null              ], // otherbgathering [yn]
         71 => [parent::CR_FLAG,      'cuFlags',                ITEM_CU_OT_ITEMLOOT                        ], // otitemopening [yn]
         72 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_DROP,                null              ], // otlooting [yn]
         73 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_MINING,              null              ], // otmining [yn]
         74 => [parent::CR_FLAG,      'cuFlags',                ITEM_CU_OT_OBJECTLOOT                      ], // otobjectopening [yn]
         75 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_PICKPOCKETING,       null              ], // otpickpocketing [yn]
         76 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_SKINNING,            null              ], // otskinning [yn]
         77 => [parent::CR_NUMERIC,   'is.atkpwr',              NUM_CAST_INT,            true              ], // atkpwr
         78 => [parent::CR_NUMERIC,   'is.mlehastertng',        NUM_CAST_INT,            true              ], // mlehastertng
         79 => [parent::CR_NUMERIC,   'is.resirtng',            NUM_CAST_INT,            true              ], // resirtng
         80 => [parent::CR_CALLBACK,  'cbHasSockets',           null,                    null              ], // has sockets [enum]
         81 => [parent::CR_CALLBACK,  'cbFitsGemSlot',          null,                    null              ], // fits gem slot [enum]
         83 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_UNIQUEEQUIPPED                   ], // uniqueequipped
         84 => [parent::CR_NUMERIC,   'is.mlecritstrkrtng',     NUM_CAST_INT,            true              ], // mlecritstrkrtng
         85 => [parent::CR_CALLBACK,  'cbObjectiveOfQuest',     null,                    null              ], // objectivequest [side]
         86 => [parent::CR_CALLBACK,  'cbCraftedByProf',        null,                    null              ], // craftedprof [enum]
         87 => [parent::CR_CALLBACK,  'cbReagentForAbility',    null,                    null              ], // reagentforability [enum]
         88 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_PROSPECTING,         null              ], // otprospecting [yn]
         89 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_PROSPECTABLE                     ], // prospectable
         90 => [parent::CR_CALLBACK,  'cbAvgBuyout',            null,                    null              ], // avgbuyout [op] [int]
         91 => [parent::CR_ENUM,      'totemCategory',          false,                   true              ], // tool
         92 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_VENDOR,              null              ], // soldbyvendor [yn]
         93 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_PVP,                 null              ], // otpvp [pvp]
         94 => [parent::CR_NUMERIC,   'is.splpen',              NUM_CAST_INT,            true              ], // splpen
         95 => [parent::CR_NUMERIC,   'is.mlehitrtng',          NUM_CAST_INT,            true              ], // mlehitrtng
         96 => [parent::CR_NUMERIC,   'is.critstrkrtng',        NUM_CAST_INT,            true              ], // critstrkrtng
         97 => [parent::CR_NUMERIC,   'is.feratkpwr',           NUM_CAST_INT,            true              ], // feratkpwr
         98 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_PARTYLOOT                        ], // partyloot
         99 => [parent::CR_ENUM,      'requiredSkill'                                                      ], // requiresprof
        100 => [parent::CR_NUMERIC,   'is.nsockets',            NUM_CAST_INT                               ], // nsockets
        101 => [parent::CR_NUMERIC,   'is.rgdhastertng',        NUM_CAST_INT,            true              ], // rgdhastertng
        102 => [parent::CR_NUMERIC,   'is.splhastertng',        NUM_CAST_INT,            true              ], // splhastertng
        103 => [parent::CR_NUMERIC,   'is.hastertng',           NUM_CAST_INT,            true              ], // hastertng
        104 => [parent::CR_STRING,    'description',            STR_LOCALIZED                              ], // flavortext
        105 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_DUNGEON_DROP,   1                 ], // dropsinnormal [heroicdungeon-any]
        106 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_DUNGEON_DROP,   2                 ], // dropsinheroic [heroicdungeon-any]
        107 => [parent::CR_STRING,    'effects',                STR_LOCALIZED                              ], // effecttext [str]
        109 => [parent::CR_CALLBACK,  'cbArmorBonus',           null,                    null              ], // armorbonus [op] [int]
        111 => [parent::CR_NUMERIC,   'requiredSkillRank',      NUM_CAST_INT,            true              ], // reqskillrank
        113 => [parent::CR_FLAG,      'cuFlags',                CUSTOM_HAS_SCREENSHOT                      ], // hasscreenshots
        114 => [parent::CR_NUMERIC,   'is.armorpenrtng',        NUM_CAST_INT,            true              ], // armorpenrtng
        115 => [parent::CR_NUMERIC,   'is.health',              NUM_CAST_INT,            true              ], // health
        116 => [parent::CR_NUMERIC,   'is.mana',                NUM_CAST_INT,            true              ], // mana
        117 => [parent::CR_NUMERIC,   'is.exprtng',             NUM_CAST_INT,            true              ], // exprtng
        118 => [parent::CR_CALLBACK,  'cbPurchasableWith',      null,                    null              ], // purchasablewithitem [enum]
        119 => [parent::CR_NUMERIC,   'is.hitrtng',             NUM_CAST_INT,            true              ], // hitrtng
        123 => [parent::CR_NUMERIC,   'is.splpwr',              NUM_CAST_INT,            true              ], // splpwr
        124 => [parent::CR_CALLBACK,  'cbHasRandEnchant',       null,                    null              ], // randomenchants [str]
        125 => [parent::CR_CALLBACK,  'cbReqArenaRating',       null,                    null              ], // reqarenartng [op] [int]  todo (low): 'find out, why "IN (W, X, Y) AND IN (X, Y, Z)" doesn't result in "(X, Y)"
        126 => [parent::CR_CALLBACK,  'cbQuestRewardIn',        null,                    null              ], // rewardedbyquestin [zone-any]
        128 => [parent::CR_CALLBACK,  'cbSource',               null,                    null              ], // source [enum]
        129 => [parent::CR_CALLBACK,  'cbSoldByNPC',            null,                    null              ], // soldbynpc [str-small]
        130 => [parent::CR_FLAG,      'cuFlags',                CUSTOM_HAS_COMMENT                         ], // hascomments
        132 => [parent::CR_CALLBACK,  'cbGlyphType',            null,                    null              ], // glyphtype [enum]
        133 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_ACCOUNTBOUND                     ], // accountbound
        134 => [parent::CR_NUMERIC,   'is.mledps',              NUM_CAST_FLOAT,          true              ], // mledps
        135 => [parent::CR_NUMERIC,   'is.mledmgmin',           NUM_CAST_INT,            true              ], // mledmgmin
        136 => [parent::CR_NUMERIC,   'is.mledmgmax',           NUM_CAST_INT,            true              ], // mledmgmax
        137 => [parent::CR_NUMERIC,   'is.mlespeed',            NUM_CAST_FLOAT,          true              ], // mlespeed
        138 => [parent::CR_NUMERIC,   'is.rgddps',              NUM_CAST_FLOAT,          true              ], // rgddps
        139 => [parent::CR_NUMERIC,   'is.rgddmgmin',           NUM_CAST_INT,            true              ], // rgddmgmin
        140 => [parent::CR_NUMERIC,   'is.rgddmgmax',           NUM_CAST_INT,            true              ], // rgddmgmax
        141 => [parent::CR_NUMERIC,   'is.rgdspeed',            NUM_CAST_FLOAT,          true              ], // rgdspeed
        142 => [parent::CR_STRING,    'ic.name'                                                            ], // icon
        143 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_MILLING,             null              ], // otmilling [yn]
        144 => [parent::CR_CALLBACK,  'cbPvpPurchasable',       'reqHonorPoints',        null              ], // purchasablewithhonor [yn]
        145 => [parent::CR_CALLBACK,  'cbPvpPurchasable',       'reqArenaPoints',        null              ], // purchasablewitharena [yn]
        146 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_HEROIC                           ], // heroic
        147 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      1,                ], // dropsinnormal10 [multimoderaid-any]
        148 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      2,                ], // dropsinnormal25 [multimoderaid-any]
        149 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      4,                ], // dropsinheroic10 [heroicraid-any]
        150 => [parent::CR_CALLBACK,  'cbDropsInInstance',      SRC_FLAG_RAID_DROP,      8,                ], // dropsinheroic25 [heroicraid-any]
        151 => [parent::CR_NUMERIC,   'id',                     NUM_CAST_INT,            true              ], // id
        152 => [parent::CR_CALLBACK,  'cbClassRaceSpec',        'requiredClass'                            ], // classspecific [enum]
        153 => [parent::CR_CALLBACK,  'cbClassRaceSpec',        'requiredRace'                             ], // racespecific [enum]
        154 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_REFUNDABLE                       ], // refundable
        155 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_ARENA                     ], // usableinarenas
        156 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_SHAPED                    ], // usablewhenshapeshifted
        157 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_SMARTLOOT                        ], // smartloot
        158 => [parent::CR_CALLBACK,  'cbPurchasableWith',      null,                    null              ], // purchasablewithcurrency [enum]
        159 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_MILLABLE                         ], // millable
        160 => [parent::CR_NYI_PH,    null,                     1,                                         ], // relatedevent [enum]      like 169 .. crawl though npc_vendor and loot_templates of event-related spawns
        161 => [parent::CR_CALLBACK,  'cbAvailable',            null,                    null              ], // availabletoplayers [yn]
        162 => [parent::CR_FLAG,      'flags',                  ITEM_FLAG_DEPRECATED                       ], // deprecated
        163 => [parent::CR_CALLBACK,  'cbDisenchantsInto',      null,                    null              ], // disenchantsinto [disenchanting]
        165 => [parent::CR_NUMERIC,   'repairPrice',            NUM_CAST_INT,            true              ], // repaircost
        167 => [parent::CR_FLAG,      'cuFlags',                CUSTOM_HAS_VIDEO                           ], // hasvideos
        168 => [parent::CR_CALLBACK,  'cbFieldHasVal',          'spellId1',              LEARN_SPELLS      ], // teachesspell [yn]
        169 => [parent::CR_ENUM,      'e.holidayId',            true,                    true              ], // requiresevent
        171 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_REDEMPTION,          null              ], // otredemption [yn]
        172 => [parent::CR_CALLBACK,  'cbObtainedBy',           SRC_ACHIEVEMENT,         null              ], // rewardedbyachievement [yn]
        176 => [parent::CR_STAFFFLAG, 'flags'                                                              ], // flags
        177 => [parent::CR_STAFFFLAG, 'flagsExtra'                                                         ], // flags2
    );

    protected static array $inputFields   = array(
        'wt'    => [parent::V_CALLBACK, 'cbWeightKeyCheck',                                                  true ], // weight keys
        'wtv'   => [parent::V_RANGE,    [1, 999],                                                            true ], // weight values
        'jc'    => [parent::V_LIST,     [1],                                                                 false], // use jewelcrafter gems for weight calculation
        'gm'    => [parent::V_LIST,     [2, 3, 4],                                                           false], // gem rarity for weight calculation
        'cr'    => [parent::V_RANGE,    [1, 177],                                                            true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]],                   true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                                                 true ], // criteria values - only printable chars, no delimiters
        'upg'   => [parent::V_REGEX,    '/[^\d:]/ui',                                                        true ], // upgrade item ids
        'gb'    => [parent::V_LIST,     [0, 1, 2, 3],                                                        false], // search result grouping
        'na'    => [parent::V_NAME,     false,                                                               false], // name - only printable chars, no delimiter
        'ma'    => [parent::V_EQUAL,    1,                                                                   false], // match any / all filter
        'ub'    => [parent::V_LIST,     [[1, 9], 11],                                                        false], // usable by classId
        'qu'    => [parent::V_RANGE,    [0, 7],                                                              true ], // quality ids
        'ty'    => [parent::V_CALLBACK, 'cbTypeCheck',                                                       true ], // item type - dynamic by current group
        'sl'    => [parent::V_CALLBACK, 'cbSlotCheck',                                                       true ], // item slot - dynamic by current group
        'si'    => [parent::V_LIST,     [-SIDE_HORDE, -SIDE_ALLIANCE, SIDE_ALLIANCE, SIDE_HORDE, SIDE_BOTH], false], // side
        'minle' => [parent::V_RANGE,    [0, 999],                                                            false], // item level min
        'maxle' => [parent::V_RANGE,    [0, 999],                                                            false], // item level max
        'minrl' => [parent::V_RANGE,    [0, MAX_LEVEL],                                                      false], // required level min
        'maxrl' => [parent::V_RANGE,    [0, MAX_LEVEL],                                                      false]  // required level max
    );

    public array $extraOpts = [];                           // score for statWeights
    public array $wtCnd     = [];

    public function createConditionsForWeights() : array
    {
        if (empty($this->values['wt']))
            return [];

        $this->wtCnd = [];
        $select = [];
        $wtSum  = 0;

        foreach ($this->values['wt'] as $k => $v)
        {
            if ($str = Stat::getWeightJson($v))
            {
                $qty = intVal($this->values['wtv'][$k]);

                $select[]      = '(IFNULL(`is`.`'.$str.'`, 0) * '.$qty.')';
                $this->wtCnd[] = ['is.'.$str, 0, '>'];
                $wtSum        += $qty;
            }
        }

        if (count($this->wtCnd) > 1)
            array_unshift($this->wtCnd, 'OR');
        else if (count($this->wtCnd) == 1)
            $this->wtCnd = $this->wtCnd[0];

        if ($select)
        {
            $this->extraOpts['is']['s'][] = ', IF(`is`.`typeId` IS NULL, 0, ('.implode(' + ', $select).') / '.$wtSum.') AS "score"';
            $this->extraOpts['is']['o'][] = 'score DESC';
            $this->extraOpts['i']['o'][]  = null;           // remove default ordering
        }
        else
            $this->extraOpts['is']['s'][] = ', 0 AS "score"'; // prevent errors

        return $this->wtCnd;
    }

    public function getConditions() : array
    {
        if (!$this->ubFilter)
        {
            $classes = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `weaponTypeMask` AS "0", `armorTypeMask` AS "1" FROM ?_classes');
            foreach ($classes as $cId => [$weaponTypeMask, $armorTypeMask])
            {
                // preselect misc subclasses
                $this->ubFilter[$cId] = [ITEM_CLASS_WEAPON => [ITEM_SUBCLASS_MISC_WEAPON], ITEM_CLASS_ARMOR => [ITEM_SUBCLASS_MISC_ARMOR]];

                for ($i = 0; $i < 21; $i++)
                    if ($weaponTypeMask & (1 << $i))
                        $this->ubFilter[$cId][ITEM_CLASS_WEAPON][] = $i;

                for ($i = 0; $i < 11; $i++)
                    if ($armorTypeMask & (1 << $i))
                        $this->ubFilter[$cId][ITEM_CLASS_ARMOR][] = $i;
            }
        }

        return parent::getConditions();
    }

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // weights
        if ($_v['wt'] && $_v['wtv'])
        {
            // gm - gem quality (qualityId)
            // jc - jc-gems included (bool)

            if ($_ = $this->createConditionsForWeights())
                $parts[] = $_;

            foreach ($_v['wt'] as $_)
                $this->fiExtraCols[] = $_;
        }

        // upgrade for [form only]
        if ($_v['upg'])
        {
            if ($this->upgrades = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `slot` FROM ?_items WHERE `class` IN (?a) AND `id` IN (?a)', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR], $_v['upg']))
                $parts[] = ['slot', $this->upgrades];
            else
                $_v['upg'] = null;
        }

        // name
        if ($_v['na'])
            if ($_ = $this->buildMatchLookup(['na' => 'name_loc'.Lang::getLocale()->value]))
                $parts[] = $_;

        // usable-by (not excluded by requiredClass && armor or weapons match mask from ?_classes)
        if ($_v['ub'])
        {
            $parts[] = array(
                'AND',
                ['OR', ['requiredClass', 0], ['requiredClass', $this->list2Mask((array)$_v['ub']), '&']],
                [
                    'OR',
                    ['class', [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR], '!'],
                    ['AND', ['class', ITEM_CLASS_WEAPON], ['subclassbak', $this->ubFilter[$_v['ub']][ITEM_CLASS_WEAPON]]],
                    ['AND', ['class', ITEM_CLASS_ARMOR],  ['subclassbak', $this->ubFilter[$_v['ub']][ITEM_CLASS_ARMOR]]]
                ]
            );
        }

        // quality [list]
        if ($_v['qu'])
            $parts[] = ['quality', $_v['qu']];

        // type [list]
        if ($_v['ty'])
            $parts[] = ['subclass', $_v['ty']];

        // slot [list]
        if ($_v['sl'])
            $parts[] = ['slot', $_v['sl']];

        // side
        if ($_v['si'])
        {
            $parts[] = match ($_v['si'])
            {
                // in theory an item could be requiring orc|nightelf etc. and would then be SIDE_BOTH without cleanly fitting the filters below, but in that case; WTF are you doing?!
                SIDE_BOTH     => ['AND', [['flagsExtra', 0x3, '&'], [0, 3]], ['requiredRace', 0]],
               -SIDE_HORDE    => ['OR',  [['flagsExtra', 0x3, '&'], 1],      ['requiredRace', ChrRace::MASK_HORDE, '&']],
               -SIDE_ALLIANCE => ['OR',  [['flagsExtra', 0x3, '&'], 2],      ['requiredRace', ChrRace::MASK_ALLIANCE, '&']],
                SIDE_HORDE    => ['AND', [['flagsExtra', 0x3, '&'], [0, 1]], ['OR',  ['requiredRace', 0], ['requiredRace', ChrRace::MASK_HORDE, '&']]],
                SIDE_ALLIANCE => ['AND', [['flagsExtra', 0x3, '&'], [0, 2]], ['OR',  ['requiredRace', 0], ['requiredRace', ChrRace::MASK_ALLIANCE, '&']]],
            };
        }

        // itemLevel min
        if ($_v['minle'])
            $parts[] = ['itemLevel', $_v['minle'], '>='];

        // itemLevel max
        if ($_v['maxle'])
            $parts[] = ['itemLevel', $_v['maxle'], '<='];

        // reqLevel min
        if ($_v['minrl'])
            $parts[] = ['requiredLevel', $_v['minrl'], '>='];

        // reqLevel max
        if ($_v['maxrl'])
            $parts[] = ['requiredLevel', $_v['maxrl'], '<='];

        return $parts;
    }

    protected function cbFactionQuestReward(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            1 => ['src.src4', null, '!'],                   // Yes
            2 => ['src.src4', SIDE_ALLIANCE],               // Alliance
            3 => ['src.src4', SIDE_HORDE],                  // Horde
            4 => ['src.src4', SIDE_BOTH],                   // Both
            5 => ['src.src4', null],                        // No
            default => null
        };
    }

    protected function cbAvailable(int $cr, int $crs, string $crv) : ?array
    {
        if ($this->int2Bool($crs))
            return [['cuFlags', CUSTOM_UNAVAILABLE, '&'], 0, $crs ? null : '!'];

        return null;
    }

    protected function cbHasSockets(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // Meta, Red, Yellow, Blue
            1, 2, 3, 4 => ['OR', ['socketColor1', 1 << ($crs - 1)], ['socketColor2', 1 << ($crs - 1)], ['socketColor3', 1 << ($crs - 1)]],
            5 => ['is.nsockets', 0, '!'],                   // Yes
            6 => ['is.nsockets', 0],                        // No
            default => null
        };
    }

    protected function cbFitsGemSlot(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // Meta, Red, Yellow, Blue
            1, 2, 3, 4 => ['AND', ['gemEnchantmentId', 0, '!'], ['gemColorMask', 1 << ($crs - 1), '&']],
            5 => ['gemEnchantmentId', 0, '!'],              // Yes
            6 => ['gemEnchantmentId', 0],                   // No
            default => null
        };
    }

    protected function cbGlyphType(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // major, minor
            1, 2 => ['AND', ['class', ITEM_CLASS_GLYPH], ['subSubClass', $crs]],
            default => null
        };
    }

    protected function cbHasRandEnchant(int $cr, int $crs, string $crv) : ?array
    {
        $n = preg_replace(parent::PATTERN_NAME, '', $crv);
        if (!$this->tokenizeString($cr, $n))
            return null;

        $parts = [];
        foreach ($this->inTokens[$cr] ?? [] as $tok)
            $parts[] = sprintf('name_loc%d LIKE "%%%s%%"', Lang::getLocale()->value, mysqli_real_escape_string(DB::Aowow()->link, $tok));
        foreach ($this->exTokens[$cr] ?? [] as $tok)
            $parts[] = sprintf('name_loc%d NOT LIKE "%%%s%%"', Lang::getLocale()->value, mysqli_real_escape_string(DB::Aowow()->link, $tok));

        $randIds = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, ABS(`id`) AS `id`, name_loc?d, `name_loc0` FROM ?_itemrandomenchant WHERE '.implode(' AND ', $parts), Lang::getLocale()->value);
        $tplIds  = $randIds ? DB::World()->select('SELECT `entry`, `ench` FROM item_enchantment_template WHERE `ench` IN (?a)', array_column($randIds, 'id')) : [];
        foreach ($tplIds as &$set)
        {
            $z = array_column($randIds, 'id');
            $x = array_search($set['ench'], $z);
            if (isset($randIds[-$z[$x]]))
            {
                $set['entry'] *= -1;
                $set['ench']  *= -1;
            }

            $set['name'] = Util::localizedString($randIds[$set['ench']], 'name', true);
        }

        // only enhance search results if enchantment by name is unique (implies only one enchantment per item is available)
        if (count(array_unique(array_column($randIds, 'name_loc0'))) == 1)
            $this->extraOpts['relEnchant'] = $tplIds;

        if ($tplIds)
            return ['randomEnchant', array_column($tplIds, 'entry')];
        else
            return [0];                                     // no results aren't really input errors
    }

    protected function cbReqArenaRating(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $this->fiExtraCols[] = $cr;

        $items = [0];
        if ($costs = DB::Aowow()->selectCol('SELECT `id` FROM ?_itemextendedcost WHERE `reqPersonalrating` '.$crs.' '.$crv))
            $items = DB::World()->selectCol($this->extCostQuery, $costs, $costs);

        return ['id', $items];
    }

    protected function cbClassRaceSpec(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_bool($_))
            return $_ ? [$field, 0, '>'] : [$field, 0];
        else if (is_int($_))
            return [$field, 1 << ($_ - 1), '&'];

        return null;
    }

    protected function cbDamageType(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->checkInput(parent::V_RANGE, [SPELL_SCHOOL_NORMAL, SPELL_SCHOOL_ARCANE], $crs))
            return null;

        return ['OR', ['dmgType1', $crs], ['dmgType2', $crs]];
    }

    protected function cbArmorBonus(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_FLOAT) || !$this->int2Op($crs))
            return null;

        $this->fiExtraCols[] = $cr;
        return ['AND', ['armordamagemodifier', $crv, $crs], ['class', ITEM_CLASS_ARMOR]];
    }

    protected function cbCraftedByProf(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_bool($_))
            return ['src.src1', null, $_ ? '!' : null];
        else if (is_int($_))
            return ['s.skillLine1', $_];

        return null;
    }

    protected function cbQuestRewardIn(int $cr, int $crs, string $crv) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            return ['AND', ['src.src4', null, '!'], ['src.moreZoneId', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return ['src.src4', null, '!'];                 // well, this seems a bit redundant..

        return null;
    }

    protected function cbDropsInZone(int $cr, int $crs, string $crv) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            return ['AND', ['src.src2', null, '!'], ['src.moreZoneId', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return ['src.src2', null, '!'];                 // well, this seems a bit redundant..

        return null;
    }

    protected function cbDropsInInstance(int $cr, int $crs, string $crv, int $moreFlag, int $modeBit) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            return ['AND', ['src.src2', $modeBit, '&'], ['src.moreMask', $moreFlag, '&'], ['src.moreZoneId', $crs]];
        else if ($crs == parent::ENUM_ANY)
            return ['AND', ['src.src2', $modeBit, '&'], ['src.moreMask', $moreFlag, '&']];

        return null;
    }

    protected function cbPurchasableWith(int $cr, int $crs, string $crv) : ?array
    {
        if (in_array($crs, self::$enums[$cr]))
            $_ = (array)$crs;
        else if ($crs == parent::ENUM_ANY)
            $_ = self::$enums[$cr];
        else
            return null;

        $costs = DB::Aowow()->selectCol(
           'SELECT `id` FROM ?_itemextendedcost WHERE `reqItemId1` IN (?a) OR `reqItemId2` IN (?a) OR `reqItemId3` IN (?a) OR `reqItemId4` IN (?a) OR `reqItemId5` IN (?a)',
            $_, $_, $_, $_, $_
        );
        if ($items = DB::World()->selectCol($this->extCostQuery, $costs, $costs))
            return ['id', $items];

        return null;
    }

    protected function cbSoldByNPC(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if ($iIds = DB::World()->selectCol('SELECT `item` FROM npc_vendor WHERE `entry` = ?d UNION SELECT `item` FROM game_event_npc_vendor v JOIN creature c ON c.`guid` = v.`guid` WHERE c.`id` = ?d', $crv, $crv))
            return ['i.id', $iIds];
        else
            return [0];
    }

    protected function cbAvgBuyout(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        foreach (Profiler::getRealms() as $rId => $__)
        {
            // todo: do something sensible..
            // // todo (med): get the avgbuyout into the listview
            // if ($_ = DB::Characters()->select('SELECT ii.itemEntry AS ARRAY_KEY, AVG(ah.buyoutprice / ii.count) AS buyout FROM auctionhouse ah JOIN item_instance ii ON ah.itemguid = ii.guid GROUP BY ii.itemEntry HAVING buyout '.$crs.' ?f', $c[1]))
                // return ['i.id', array_keys($_)];
            // else
                // return [0];
            return [1];
        }

        return [0];
    }

    protected function cbAvgMoneyContent(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $this->fiExtraCols[] = $cr;
        return ['AND', ['flags', ITEM_FLAG_OPENABLE, '&'], ['((minMoneyLoot + maxMoneyLoot) / 2)', $crv, $crs]];
    }

    protected function cbCooldown(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $crv *= 1000;                                       // field supplied in milliseconds

        $this->fiExtraCols[] = $cr;
        $this->extraOpts['is']['s'][] = ', GREATEST(`spellCooldown1`, `spellCooldown2`, `spellCooldown3`, `spellCooldown4`, `spellCooldown5`) AS "cooldown"';

        return [
            'OR',
            ['AND', ['spellTrigger1', SPELL_TRIGGER_USE], ['spellId1', 0, '!'], ['spellCooldown1', 0, '>'], ['spellCooldown1', $crv, $crs]],
            ['AND', ['spellTrigger2', SPELL_TRIGGER_USE], ['spellId2', 0, '!'], ['spellCooldown2', 0, '>'], ['spellCooldown2', $crv, $crs]],
            ['AND', ['spellTrigger3', SPELL_TRIGGER_USE], ['spellId3', 0, '!'], ['spellCooldown3', 0, '>'], ['spellCooldown3', $crv, $crs]],
            ['AND', ['spellTrigger4', SPELL_TRIGGER_USE], ['spellId4', 0, '!'], ['spellCooldown4', 0, '>'], ['spellCooldown4', $crv, $crs]],
            ['AND', ['spellTrigger5', SPELL_TRIGGER_USE], ['spellId5', 0, '!'], ['spellCooldown5', 0, '>'], ['spellCooldown5', $crv, $crs]],
        ];
    }

    protected function cbQuestRelation(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // any
            1 => ['startQuest', 0, '>'],
            // exclude horde only
            2 => ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], SIDE_HORDE]],
            // exclude alliance only
            3 => ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], SIDE_ALLIANCE]],
            // both
            4 => ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 0]],
            // none
            5 => ['startQuest', 0],
            default => null
        };
    }

    protected function cbFieldHasVal(int $cr, int $crs, string $crv, string $field, mixed $val) : ?array
    {
        if ($this->int2Bool($crs))
            return [$field, $val, $crs ? null : '!'];

        return null;
    }

    protected function cbObtainedBy(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if ($this->int2Bool($crs))
            return ['src.src'.$field, null, $crs ? '!' : null];

        return null;
    }

    protected function cbPvpPurchasable(int $cr, int $crs, string $crv, string $field) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        $costs = DB::Aowow()->selectCol('SELECT `id` FROM ?_itemextendedcost WHERE ?# > 0', $field);
        if ($items = DB::World()->selectCol($this->extCostQuery, $costs, $costs))
            return ['id', $items, $crs ? null : '!'];

        return null;
    }

    protected function cbDisenchantsInto(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crs, NUM_CAST_INT))
            return null;

        if (!in_array($crs, self::$enums[$cr]))
            return null;

        $refResults = [];
        $newRefs = DB::World()->selectCol('SELECT `entry` FROM ?# WHERE `item` = ?d AND `reference` = 0', Loot::REFERENCE, $crs);
        while ($newRefs)
        {
            $refResults += $newRefs;
            $newRefs     = DB::World()->selectCol('SELECT `entry` FROM ?# WHERE `reference` IN (?a)', Loot::REFERENCE, $newRefs);
        }

        $lootIds = DB::World()->selectCol('SELECT `entry` FROM ?# WHERE {`reference` IN (?a) OR }(`reference` = 0 AND `item` = ?d)', Loot::DISENCHANT, $refResults ?: DBSIMPLE_SKIP, $crs);

        return $lootIds ? ['disenchantId', $lootIds] : [0];
    }

    protected function cbObjectiveOfQuest(int $cr, int $crs, string $crv) : ?array
    {
        $w = match ($crs)
        {
            1, 5    => 1,                                                                                                                // Yes / No
            2       =>  '`reqRaceMask` & '.ChrRace::MASK_ALLIANCE.' AND (`reqRaceMask` & '.ChrRace::MASK_HORDE.') = 0',                  // Alliance
            3       =>  '`reqRaceMask` & '.ChrRace::MASK_HORDE.'    AND (`reqRaceMask` & '.ChrRace::MASK_ALLIANCE.') = 0',               // Horde
            4       => '(`reqRaceMask` & '.ChrRace::MASK_ALLIANCE.' AND  `reqRaceMask` & '.ChrRace::MASK_HORDE.') OR `reqRaceMask` = 0', // Both
            default => null
        };

        $itemIds = DB::Aowow()->selectCol(sprintf(
           'SELECT `reqItemId1` FROM ?_quests WHERE %1$s UNION SELECT `reqItemId2` FROM ?_quests WHERE %1$s UNION
            SELECT `reqItemId3` FROM ?_quests WHERE %1$s UNION SELECT `reqItemId4` FROM ?_quests WHERE %1$s UNION
            SELECT `reqItemId5` FROM ?_quests WHERE %1$s UNION SELECT `reqItemId6` FROM ?_quests WHERE %1$s',
            $w
        ));

        if ($itemIds)
            return ['id', $itemIds, $crs == 5 ? '!' : null];

        return [0];
    }

    protected function cbReagentForAbility(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if ($_ === null)
            return null;

        $ids    = [];
        $spells = DB::Aowow()->select(                      // todo (med): hmm, selecting all using SpellList would exhaust 128MB of memory :x .. see, that we only select the fields that are really needed
           'SELECT `reagent1`,      `reagent2`,      `reagent3`,      `reagent4`,      `reagent5`,      `reagent6`,      `reagent7`,      `reagent8`,
                   `reagentCount1`, `reagentCount2`, `reagentCount3`, `reagentCount4`, `reagentCount5`, `reagentCount6`, `reagentCount7`, `reagentCount8`
            FROM   ?_spell
            WHERE  `skillLine1` IN (?a)',
            is_bool($_) ? array_filter(self::$enums[99], "is_numeric") : $_
        );
        foreach ($spells as $spell)
            for ($i = 1; $i < 9; $i++)
                if ($spell['reagent'.$i] > 0 && $spell['reagentCount'.$i] > 0)
                    $ids[] = $spell['reagent'.$i];

        if (empty($ids))
            return [0];
        else if ($_)
            return ['id', $ids];
        else
            return ['id', $ids, '!'];
    }

    protected function cbSource(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_int($_))                                     // specific
            return ['src.src'.$_, null, '!'];
        else if ($_)                                        // any
        {
            $foo = ['OR'];
            foreach (self::$enums[$cr] as $bar)
                if (is_int($bar))
                    $foo[] = ['src.src'.$bar, null, '!'];

            return $foo;
        }
        else                                                // none
            return ['src.typeId', null];
    }

    protected function cbTypeCheck(string &$v) : bool
    {
        if (!$this->parentCats)
            return false;

        if (!Util::checkNumeric($v, NUM_CAST_INT))
            return false;

        $c = $this->parentCats;

        if (isset($c[2]) && is_array(Lang::item('cat', $c[0], 1, $c[1])))
            $catList = Lang::item('cat', $c[0], 1, $c[1], 1, $c[2]);
        else if (isset($c[1]) && is_array(Lang::item('cat', $c[0])))
            $catList = Lang::item('cat', $c[0], 1, $c[1]);
        else
            $catList = Lang::item('cat', $c[0]);

        // consumables - always
        if ($c[0] == ITEM_CLASS_CONSUMABLE)
            return in_array($v, array_keys(Lang::item('cat', 0, 1)));
        // weapons - only if parent
        else if ($c[0] == ITEM_CLASS_WEAPON && !isset($c[1]))
            return in_array($v, array_keys(Lang::spell('weaponSubClass')));
        // armor - only if parent
        else if ($c[0] == ITEM_CLASS_ARMOR && !isset($c[1]))
            return in_array($v, array_keys(Lang::item('cat', ITEM_CLASS_ARMOR, 1)));
        // uh ... other stuff...
        else if (!isset($c[1]) && in_array($c[0], [ITEM_CLASS_CONTAINER, ITEM_CLASS_GEM, ITEM_CLASS_TRADEGOOD, ITEM_CLASS_RECIPE, ITEM_CLASS_MISC]))
            return in_array($v, array_keys($catList[1]));

        return false;
    }

    protected function cbSlotCheck(string &$v) : bool
    {
        if (!Util::checkNumeric($v, NUM_CAST_INT))
            return false;

        // todo (low): limit to concrete slots
        $sl = array_keys(Lang::item('inventoryType'));
        $c  = $this->parentCats;

        // no selection
        if (!isset($c[0]))
            return in_array($v, $sl);

        // consumables - any; perm / temp item enhancements
        else if ($c[0] == ITEM_CLASS_CONSUMABLE && (!isset($c[1]) || in_array($c[1], [-3, 6])))
            return in_array($v, $sl);

        // weapons - always
        else if ($c[0] == ITEM_CLASS_WEAPON)
            return in_array($v, $sl);

        // armor - any; any armor
        else if ($c[0] == ITEM_CLASS_ARMOR && (!isset($c[1]) || in_array($c[1], [ITEM_SUBCLASS_CLOTH_ARMOR, ITEM_SUBCLASS_LEATHER_ARMOR, ITEM_SUBCLASS_MAIL_ARMOR, ITEM_SUBCLASS_PLATE_ARMOR])))
            return in_array($v, $sl);

        return false;
    }

    protected function cbWeightKeyCheck(string &$v) : bool
    {
        if (preg_match('/\W/i', $v))
            return false;

        return Stat::getIndexFrom(Stat::IDX_FILTER_CR_ID, $v) > 0;
    }
}

?>
