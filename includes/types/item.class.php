<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemList extends BaseType
{
    use ListviewHelper;

    public static   $type       = TYPE_ITEM;
    public static   $brickFile  = 'item';
    public static   $dataTable  = '?_items';

    public          $json       = [];
    public          $itemMods   = [];
    public          $sources    = [];

    public          $rndEnchIds = [];
    public          $subItems   = [];

    private         $sourceMore = null;
    private         $ssd        = [];
    private         $vendors    = [];
    private         $jsGlobals  = [];                       // getExtendedCost creates some and has no access to template

    protected       $queryBase  = 'SELECT i.*, i.block AS tplBlock, i.id AS ARRAY_KEY, i.id AS id FROM ?_items i';
    protected       $queryOpts  = array(                    // 3 => TYPE_ITEM
                        'i'   => [['is', 'src', 'ic'], 'o' => 'i.quality DESC, i.itemLevel DESC'],
                        'ic'  => ['j' => ['?_icons      `ic`  ON `ic`.`id` = `i`.`iconId`', true], 's' => ', ic.name AS iconString'],
                        'is'  => ['j' => ['?_item_stats `is`  ON `is`.`type` = 3 AND `is`.`typeId` = `i`.`id`', true], 's' => ', `is`.*'],
                        's'   => ['j' => ['?_spell      `s`   ON `s`.`effect1CreateItemId` = `i`.`id`', true], 'g' => 'i.id'],
                        'e'   => ['j' => ['?_events     `e`   ON `e`.`id` = `i`.`eventId`', true], 's' => ', e.holidayId'],
                        'src' => ['j' => ['?_source     `src` ON `src`.`type` = 3 AND `src`.`typeId` = `i`.`id`', true], 's' => ', moreType, moreTypeId, src1, src2, src3, src4, src5, src6, src7, src8, src9, src10, src11, src12, src13, src14, src15, src16, src17, src18, src19, src20, src21, src22, src23, src24']
                    );

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        foreach ($this->iterate() as &$_curTpl)
        {
            // item is scaling; overwrite other values
            if ($_curTpl['scalingStatDistribution'] > 0 && $_curTpl['scalingStatValue'] > 0)
                $this->initScalingStats();

            $this->initJsonStats();

            // readdress itemset .. is wrong for virtual sets
            if ($miscData && isset($miscData['pcsToSet']) && isset($miscData['pcsToSet'][$this->id]))
                $this->json[$this->id]['itemset'] = $miscData['pcsToSet'][$this->id];

            // unify those pesky masks
            $_  = &$_curTpl['requiredClass'];
            $_ &= CLASS_MASK_ALL;
            if ($_ < 0 || $_ == CLASS_MASK_ALL)
                $_ = 0;
            unset($_);

            $_ = &$_curTpl['requiredRace'];
            $_ &= RACE_MASK_ALL;
            if ($_ < 0 || $_ == RACE_MASK_ALL)
                $_ = 0;
            unset($_);

            // sources
            for ($i = 1; $i < 25; $i++)
            {
                if ($_ = $_curTpl['src'.$i])
                    $this->sources[$this->id][$i][] = $_;

                unset($_curTpl['src'.$i]);
            }
        }
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->selectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_items WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }

    // todo (med): information will get lost if one vendor sells one item multiple times with different costs (e.g. for item 54637)
    //             wowhead seems to have had the same issues
    public function getExtendedCost($filter = [], &$reqRating = [])
    {
        if ($this->error)
            return [];

        if (empty($this->vendors))
        {
            $itemz      = [];
            $xCostData  = [];
            $rawEntries = DB::World()->select('
                SELECT   nv.item,       nv.entry,             0  AS eventId,   nv.maxcount,   nv.extendedCost FROM            npc_vendor   nv                                                                                                  WHERE {nv.entry IN (?a) AND} nv.item IN (?a)
                UNION
                SELECT genv.item, c.id AS `entry`, ge.eventEntry AS eventId, genv.maxcount, genv.extendedCost FROM game_event_npc_vendor genv LEFT JOIN game_event ge ON genv.eventEntry = ge.eventEntry JOIN creature c ON c.guid = genv.guid WHERE {c.id IN (?a) AND}   genv.item IN (?a)',
                empty($filter[TYPE_NPC]) || !is_array($filter[TYPE_NPC]) ? DBSIMPLE_SKIP : $filter[TYPE_NPC],
                array_keys($this->templates),
                empty($filter[TYPE_NPC]) || !is_array($filter[TYPE_NPC]) ? DBSIMPLE_SKIP : $filter[TYPE_NPC],
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
                $xCostData = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM ?_itemextendedcost WHERE id IN (?a)', $xCostData);

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

                        $data   = array(
                            'stock'      => $vInfo['maxcount'] ?: -1,
                            'event'      => $vInfo['eventId'],
                            'reqRating'  => $costs ? $costs['reqPersonalRating'] : 0,
                            'reqBracket' => $costs ? $costs['reqArenaSlot']      : 0
                        );

                        // hardcode arena(103) & honor(104)
                        if (!empty($costs['reqArenaPoints']))
                        {
                            $data[-103] = $costs['reqArenaPoints'];
                            $this->jsGlobals[TYPE_CURRENCY][103] = 103;
                        }

                        if (!empty($costs['reqHonorPoints']))
                        {
                            $data[-104] = $costs['reqHonorPoints'];
                            $this->jsGlobals[TYPE_CURRENCY][104] = 104;
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
                                        $this->jsGlobals[TYPE_ITEM][$k] = $k;
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
        $tok = !empty($filter[TYPE_ITEM])     ? $filter[TYPE_ITEM]     : null;
        $cur = !empty($filter[TYPE_CURRENCY]) ? $filter[TYPE_CURRENCY] : null;

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
                    // use highest total value
                    if (isset($data[$npcId]) && $costs['reqRating'] && (!$reqRating || $reqRating[0] < $costs['reqRating']))
                        $reqRating = [$costs['reqRating'], $costs['reqBracket']];
                }
            }

            if (empty($data))
                unset($result[$itemId]);
        }

        return $result;
    }

    public function getListviewData($addInfoMask = 0x0, $miscData = null)
    {
        /*
        * ITEMINFO_JSON     (0x01): itemMods (including spells) and subitems parsed
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
            $this->extendJsonStats();

        $extCosts = [];
        if ($addInfoMask & ITEMINFO_VENDOR)
            $extCosts = $this->getExtendedCost($miscData);

        $extCostOther = [];
        foreach ($this->iterate() as $__)
        {
            foreach ($this->json[$this->id] as $k => $v)
                $data[$this->id][$k] = $v;

            // json vs listview quirk
            $data[$this->id]['name'] = $data[$this->id]['quality'].$data[$this->id]['name'];
            unset($data[$this->id]['quality']);

            if ($addInfoMask & ITEMINFO_JSON)
            {
                foreach ($this->itemMods[$this->id] as $k => $v)
                    $data[$this->id][$k] = $v;

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

                        $costArr['stock'] = $entries['stock'];// display as column in lv
                        $costArr['avail'] = $entries['stock'];// display as number on icon
                        $costArr['cost']  = [empty($entries[0]) ? 0 : $entries[0]];

                        if ($entries['event'])
                        {
                            $this->jsGlobals[TYPE_WORLDEVENT][$entries['event']] = $entries['event'];
                            $costArr['condition'][0][$this->id][] = [[CND_ACTIVE_EVENT, $entries['event']]];
                        }

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
            if ($_ && $_ & RACE_MASK_ALLIANCE != RACE_MASK_ALLIANCE && $_ & RACE_MASK_HORDE != RACE_MASK_HORDE)
                $data[$this->id]['reqrace'] = $_;

            if ($_ = $this->curTpl['requiredClass'])
                $data[$this->id]['reqclass'] = $_;          // $data[$this->id]['classes'] ??

            if ($this->curTpl['flags'] & ITEM_FLAG_HEROIC)
                $data[$this->id]['heroic'] = true;

            if ($addInfoMask & ITEMINFO_MODEL)
                if ($_ = $this->getField('displayId'))
                    $data[$this->id]['displayid'] = $_;

            if ($this->getSources($s, $sm) && !($addInfoMask & ITEMINFO_MODEL))
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

    public function getJSGlobals($addMask = GLOBALINFO_SELF, &$extra = [])
    {
        $data = $addMask & GLOBALINFO_RELATED ? $this->jsGlobals : [];

        foreach ($this->iterate() as $id => $__)
        {
            if ($addMask & GLOBALINFO_SELF)
            {
                $data[TYPE_ITEM][$id] = array(
                    'name'    => $this->getField('name', true),
                    'quality' => $this->curTpl['quality'],
                    'icon'    => $this->curTpl['iconString']
                );
            }

            if ($addMask & GLOBALINFO_EXTRA)
            {
                $extra[$id] = array(
                    'id'      => $id,
                    'tooltip' => $this->renderTooltip(true),
                    'spells'  => new StdClass               // placeholder for knownSpells
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
    public function getField($field, $localized = false, $silent = false, $enhance = [])
    {
        $res = parent::getField($field, $localized, $silent);

        if ($field == 'name' && !empty($enhance['r']))
            if ($this->getRandEnchantForItem($enhance['r']))
                $res .= ' '.Util::localizedString($this->enhanceR, 'name');

        return $res;
    }

    public function renderTooltip($interactive = false, $subOf = 0, $enhance = [])
    {
        if ($this->error)
            return;

        $_name         = $this->getField('name', true);
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

                    $enchant = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE id = ?d', $this->enhanceR['enchantId'.$i]);
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
            $map = DB::Aowow()->selectRow('SELECT * FROM ?_zones WHERE mapId = ?d LIMIT 1', $_);
            $x .= '<br /><a href="?zone='.$_.'" class="q1">'.Util::localizedString($map, 'name').'</a>';
        }

        // requires area
        if ($this->curTpl['area'])
        {
            $area = DB::Aowow()->selectRow('SELECT * FROM ?_zones WHERE Id=?d LIMIT 1', $this->curTpl['area']);
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
            $limit = DB::Aowow()->selectRow("SELECT * FROM ?_itemlimitcategory WHERE id = ?", $this->curTpl['itemLimitCategory']);
            $x .= '<br />'.sprintf(Lang::item($limit['isGem'] ? 'uniqueEquipped' : 'unique', 2), Util::localizedString($limit, 'name'), $limit['count']);
        }

        // max duration
        if ($dur = $this->curTpl['duration'])
        {
            $rt = '';
            if ($this->curTpl['flagsCustom'] & 0x1)
                $rt = $interactive ? ' ('.sprintf(Util::$dfnString, 'LANG.tooltip_realduration', Lang::item('realTime')).')' : ' ('.Lang::item('realTime').')';

            $x .= "<br />".Lang::game('duration').Lang::main('colon').Util::formatTime(abs($dur) * 1000).$rt;
        }

        // required holiday
        if ($eId = $this->curTpl['eventId'])
            if ($hName = DB::Aowow()->selectRow('SELECT h.* FROM ?_holidays h JOIN ?_events e ON e.holidayId = h.id WHERE e.id = ?d', $eId))
                $x .= '<br />'.sprintf(Lang::game('requires'), '<a href="'.$eId.'" class="q1">'.Util::localizedString($hName, 'name').'</a>');

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
        $dmgmin = $this->curTpl['dmgMin1'] + $this->curTpl['dmgMin2'];
        $dmgmax = $this->curTpl['dmgMax1'] + $this->curTpl['dmgMax2'];
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
            if ($this->curTpl['dmgMin1'] == $this->curTpl['dmgMax1'])
                $dmg = sprintf(Lang::item('damage', 'single', $sc1 ? 1 : 0), $this->curTpl['dmgMin1'], $sc1 ? Lang::game('sc', $sc1) : null);
            else
                $dmg = sprintf(Lang::item('damage', 'range', $sc1 ? 1 : 0), $this->curTpl['dmgMin1'], $this->curTpl['dmgMax1'], $sc1 ? Lang::game('sc', $sc1) : null);

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
                $x .= '<!--dps-->'.sprintf(Lang::item('dps'), $dps).'<br />'; // do not use localized format here!

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

            $x .= '<span '.$spanI.'><!--addamr'.$this->curTpl['armorDamageModifier'].'--><span>'.Lang::item('armor', [$this->curTpl['armor']]).'</span></span><br />';
        }
        else if ($this->curTpl['armor'])
            $x .= '<span><!--amr-->'.Lang::item('armor', [$this->curTpl['armor']]).'</span><br />';

        // Block (note: block value from field block and from field stats or parsed from itemSpells are displayed independently)
        if ($this->curTpl['tplBlock'])
            $x .= '<span>'.sprintf(Lang::item('block'), $this->curTpl['tplBlock']).'</span><br />';

        // Item is a gem (don't mix with sockets)
        if ($geId = $this->curTpl['gemEnchantmentId'])
        {
            $gemEnch = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE id = ?d', $geId);
            $x .= '<span class="q1"><a href="?enchantment='.$geId.'">'.Util::localizedString($gemEnch, 'name').'</a></span><br />';

            // activation conditions for meta gems
            if (!empty($gemEnch['conditionId']))
            {
                if ($gemCnd = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantmentcondition WHERE id = ?d', $gemEnch['conditionId']))
                {
                    for ($i = 1; $i < 6; $i++)
                    {
                        if (!$gemCnd['color'.$i])
                            continue;

                        $vspfArgs = [];
                        switch ($gemCnd['comparator'.$i])
                        {
                            case 2:                         // requires less <color> than (<value> || <comparecolor>) gems
                            case 5:                         // requires at least <color> than (<value> || <comparecolor>) gems
                                $vspfArgs = [$gemCnd['value'.$i], Lang::item('gemColors', $gemCnd['color'.$i] - 1)];
                                break;
                            case 3:                         // requires more <color> than (<value> || <comparecolor>) gems
                                $vspfArgs = [Lang::item('gemColors', $gemCnd['color'.$i] - 1), Lang::item('gemColors', $gemCnd['cmpColor'.$i] - 1)];
                                break;
                            default:
                                continue 2;
                        }

                        $x .= '<span class="q0">'.Lang::achievement('reqNumCrt').' '.Lang::item('gemConditions', $gemCnd['comparator'.$i], $vspfArgs).'</span><br />';
                    }
                }
            }
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

            // base stat
            switch ($type)
            {
                case ITEM_MOD_MANA:
                case ITEM_MOD_HEALTH:
                    // $type += 1;                          // i think i fucked up somewhere mapping item_mods: offsets may be required somewhere
                case ITEM_MOD_AGILITY:
                case ITEM_MOD_STRENGTH:
                case ITEM_MOD_INTELLECT:
                case ITEM_MOD_SPIRIT:
                case ITEM_MOD_STAMINA:
                    $x .= '<span><!--stat'.$type.'-->'.($qty > 0 ? '+' : '-').abs($qty).' '.Lang::item('statType', $type).'</span><br />';
                    break;
                default:                                    // rating with % for reqLevel
                    $green[] = $this->parseRating($type, $qty, $interactive, $causesScaling);

            }
        }

        // magic resistances
        foreach (Game::$resistanceFields as $j => $rowName)
            if ($rowName && $this->curTpl[$rowName] != 0)
                $x .= '+'.$this->curTpl[$rowName].' '.Lang::game('resistances', $j).'<br />';

        // Enchantment
        if (isset($enhance['e']))
        {
            if ($enchText = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE id = ?', $enhance['e']))
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
            $gems = DB::Aowow()->select('
                SELECT it.id AS ARRAY_KEY, ic.name AS iconString, ae.*, it.gemColorMask AS colorMask
                FROM   ?_items it
                JOIN   ?_itemenchantment ae ON ae.id = it.gemEnchantmentId
                JOIN   ?_icons ic ON ic.id = it.iconId
                WHERE  it.id IN (?a)',
                $enhance['g']);
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
            $icon      = $pop ? sprintf(Util::$bgImagePath['tiny'], STATIC_URL, strtolower($gems[$pop]['iconString'])) : null;
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
            $icon = $pop ? sprintf(Util::$bgImagePath['tiny'], STATIC_URL, strtolower($gems[$pop]['iconString'])) : null;
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
            $sbonus = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE id = ?d', $_);
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::item('socketBonus', ['<a href="?enchantment='.$_.'">'.Util::localizedString($sbonus, 'name').'</a>']).'</span><br />';
        }

        // durability
        if ($dur = $this->curTpl['durability'])
            $x .= sprintf(Lang::item('durability'), $dur, $dur).'<br />';

        $jsg = [];
        // required classes
        if ($classes = Lang::getClassString($this->curTpl['requiredClass'], $jsg))
        {
            foreach ($jsg as $js)
                if (empty($this->jsGlobals[TYPE_CLASS][$js]))
                    $this->jsGlobals[TYPE_CLASS][$js] = $js;

            $x .= Lang::game('classes').Lang::main('colon').$classes.'<br />';
        }

        // required races
        if ($races = Lang::getRaceString($this->curTpl['requiredRace'], $jsg))
        {
            foreach ($jsg as $js)
                if (empty($this->jsGlobals[TYPE_RACE][$js]))
                    $this->jsGlobals[TYPE_RACE][$js] = $js;

            $x .= Lang::game('races').Lang::main('colon').$races.'<br />';
        }

        // required honorRank (not used anymore)
        if ($rhr = $this->curTpl['requiredHonorRank'])
            $x .= sprintf(Lang::game('requires'), Lang::game('pvpRank', $rhr)).'<br />';

        // required CityRank..?
        // what the f..

        // required level
        if (($_flags & ITEM_FLAG_ACCOUNTBOUND) && $_quality == ITEM_QUALITY_HEIRLOOM)
            $x .= sprintf(Lang::item('reqLevelRange'), 1, MAX_LEVEL, ($interactive ? sprintf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL)).'<br />';
        else if ($_reqLvl > 1)
            $x .= sprintf(Lang::item('reqMinLevel'), $_reqLvl).'<br />';

        // required arena team rating / personal rating / todo (low): sort out what kind of rating
        if (!empty($this->getExtendedCost([], $reqRating)[$this->id]) && $reqRating)
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
        if ($locks = Lang::getLocks($this->curTpl['lockId'], true))
            $x .= '<span class="q0">'.Lang::item('locked').'<br />'.implode('<br />', $locks).'</span><br />';
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
                    if ($cd >= 5000)
                        $extra[] = Lang::game('cooldown', [Util::formatTime($cd, true)]);
                    if ($this->curTpl['spellTrigger'.$j] == 2)
                        if ($ppm = $this->curTpl['spellppmRate'.$j])
                            $extra[] = Lang::spell('ppm', [$ppm]);

                    $itemSpellsAndTrigger[$this->curTpl['spellId'.$j]] = [$this->curTpl['spellTrigger'.$j], $extra ? ' ('.implode(', ', $extra).')' : ''];
                }
            }

            if ($itemSpellsAndTrigger)
            {
                $cooldown = '';

                $itemSpells = new SpellList(array(['s.id', array_keys($itemSpellsAndTrigger)]));
                foreach ($itemSpells->iterate() as $__)
                    if ($parsed = $itemSpells->parseText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL, false, $causesScaling)[0])
                    {
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
                if ($itemset->getMatches() > 1)
                {
                    foreach ($itemset->iterate() as $id => $__)
                    {
                        if ($itemset->getField('quality') == $this->curTpl['quality'])
                        {
                            $itemset->pieceToSet = array_filter($itemset->pieceToSet, function($x) use ($id) { return $id == $x; });
                            break;
                        }
                    }
                }

                $pieces = DB::Aowow()->select('
                    SELECT b.id AS ARRAY_KEY, b.name_loc0, b.name_loc2, b.name_loc3, b.name_loc4, b.name_loc6, b.name_loc8, GROUP_CONCAT(a.id SEPARATOR \':\') AS equiv
                    FROM   ?_items a, ?_items b
                    WHERE  a.slotBak = b.slotBak AND a.itemset = b.itemset AND b.id IN (?a)
                    GROUP BY b.id;',
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
                        $setSpells[] = array(
                            'tooltip' => $boni->parseText('description', $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL, false, $causesScaling)[0],
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
                    $x .= '<span class="q2">'.Lang::item('trigger', 0).' <a href="?spell='.$this->curTpl['spellId2'].'">'.$desc.'</a></span><br />';

                // recipe handling (some stray Techniques have subclass == 0), place at bottom of tooltipp
                if ($_class == ITEM_CLASS_RECIPE || $this->curTpl['bagFamily'] == 16)
                {
                    $craftItem  = new ItemList(array(['i.id', (int)$craftSpell->curTpl['effect1CreateItemId']]));
                    if (!$craftItem->error)
                    {
                        if ($itemTT = $craftItem->renderTooltip($interactive, $this->id))
                            $xCraft .= '<div><br />'.$itemTT.'</div>';

                        $reagentItems = [];
                        for ($i = 1; $i <= 8; $i++)
                            if ($rId = $craftSpell->getField('reagent'.$i))
                                $reagentItems[$rId] = $craftSpell->getField('reagentCount'.$i);

                        if (isset($xCraft) && $reagentItems)
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
        }

        // misc (no idea, how to organize the <br /> better)
        $xMisc = [];

        // itemset: pieces and boni
        if (isset($xSet))
            $xMisc[] = $xSet;

        // funny, yellow text at the bottom, omit if we have a recipe
        if ($this->curTpl['description_loc0'] && !$this->canTeachSpell())
            $xMisc[] = '<span class="q">"'.$this->getField('description', true).'"</span>';

        // readable
        if ($this->curTpl['pageTextId'])
            $xMisc[] = '<span class="q2">'.Lang::item('readClick').'</span>';

        // charges (i guess checking first spell is enough)
        if ($this->curTpl['spellCharges1'])
            $xMisc[] = '<span class="q1">'.Lang::item('charges', [abs($this->curTpl['spellCharges1'])]).'</span>';

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
            $link = [$subOf ? $subOf : $this->id, 1];       // itemId, scaleMinLevel
            if (isset($this->ssd[$this->id]))               // is heirloom
            {
                array_push($link,
                    $this->ssd[$this->id]['maxLevel'],      // scaleMaxLevel
                    $this->ssd[$this->id]['maxLevel'],      // scaleCurLevel
                    $this->curTpl['scalingStatDistribution'],  // scaleDist
                    $this->curTpl['scalingStatValue']       // scaleFlags
                );
            }
            else                                            // may still use level dependant ratings
            {
                array_push($link,
                    $causesScaling ? MAX_LEVEL : 1,         // scaleMaxLevel
                    $_reqLvl > 1 ? $_reqLvl : MAX_LEVEL     // scaleCurLevel
                );
            }
            $x .= '<!--?'.implode(':', $link).'-->';
        }

        return $x;
    }

    public function getRandEnchantForItem($randId)
    {
        // is it available for this item? .. does it even exist?!
        if (empty($this->enhanceR))
            if (DB::World()->selectCell('SELECT 1 FROM item_enchantment_template WHERE entry = ?d AND ench = ?d', abs($this->getField('randomEnchant')), abs($randId)))
                if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomenchant WHERE id = ?d', $randId))
                    $this->enhanceR = $_;

        return !empty($this->enhanceR);
    }

    // from Trinity
    public function generateEnchSuffixFactor()
    {
        $rpp = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomproppoints WHERE id = ?', $this->curTpl['itemLevel']);
        if (!$rpp)
            return 0;

        switch ($this->curTpl['slot'])
        {
            // Items of that type don`t have points
            case INVTYPE_NON_EQUIP:
            case INVTYPE_BAG:
            case INVTYPE_TABARD:
            case INVTYPE_AMMO:
            case INVTYPE_QUIVER:
            case INVTYPE_RELIC:
                return 0;
            // Select point coefficient
            case INVTYPE_HEAD:
            case INVTYPE_BODY:
            case INVTYPE_CHEST:
            case INVTYPE_LEGS:
            case INVTYPE_2HWEAPON:
            case INVTYPE_ROBE:
                $suffixFactor = 1;
                break;
            case INVTYPE_SHOULDERS:
            case INVTYPE_WAIST:
            case INVTYPE_FEET:
            case INVTYPE_HANDS:
            case INVTYPE_TRINKET:
                $suffixFactor = 2;
                break;
            case INVTYPE_NECK:
            case INVTYPE_WRISTS:
            case INVTYPE_FINGER:
            case INVTYPE_SHIELD:
            case INVTYPE_CLOAK:
            case INVTYPE_HOLDABLE:
                $suffixFactor = 3;
                break;
            case INVTYPE_WEAPON:
            case INVTYPE_WEAPONMAINHAND:
            case INVTYPE_WEAPONOFFHAND:
                $suffixFactor = 4;
                break;
            case INVTYPE_RANGED:
            case INVTYPE_THROWN:
            case INVTYPE_RANGEDRIGHT:
                $suffixFactor = 5;
                break;
            default:
                return 0;
        }

        // Select rare/epic modifier
        switch ($this->curTpl['quality'])
        {
            case ITEM_QUALITY_UNCOMMON:
                return $rpp['uncommon'.$suffixFactor] / 10000;
            case ITEM_QUALITY_RARE:
                return $rpp['rare'.$suffixFactor] / 10000;
            case ITEM_QUALITY_EPIC:
                return $rpp['epic'.$suffixFactor] / 10000;
            case ITEM_QUALITY_LEGENDARY:
            case ITEM_QUALITY_ARTIFACT:
                return 0;                                   // not have random properties
            default:
                break;
        }
        return 0;
    }

    public function extendJsonStats()
    {
        $enchantments = [];                                 // buffer Ids for lookup id => src; src>0: socketBonus; src<0: gemEnchant

        foreach ($this->iterate() as $__)
        {
            $this->itemMods[$this->id] = [];

            foreach (Game::$itemMods as $mod)
                if ($_ = floatVal($this->curTpl[$mod]))
                    Util::arraySumByKey($this->itemMods[$this->id], [$mod => $_]);

            // fetch and add socketbonusstats
            if (!empty($this->json[$this->id]['socketbonus']))
                $enchantments[$this->json[$this->id]['socketbonus']][] = $this->id;

            // Item is a gem (don't mix with sockets)
            if ($geId = $this->curTpl['gemEnchantmentId'])
                $enchantments[$geId][] = -$this->id;
        }

        if ($enchantments)
        {
            $eStats = DB::Aowow()->select('SELECT *, typeId AS ARRAY_KEY FROM ?_item_stats WHERE `type` = ?d AND typeId IN (?a)', TYPE_ENCHANTMENT, array_keys($enchantments));
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

    public function getOnUseStats()
    {
        $onUseStats = [];

        // convert Spells
        $useSpells = [];
        for ($h = 1; $h <= 5; $h++)
        {
            if ($this->curTpl['spellId'.$h] <= 0)
                continue;

            if ($this->curTpl['class'] != ITEM_CLASS_CONSUMABLE || $this->curTpl['spellTrigger'.$h])
                continue;

            $useSpells[] = $this->curTpl['spellId'.$h];
        }

        if ($useSpells)
        {
            $eqpSplList = new SpellList(array(['s.id', $useSpells]));
            foreach ($eqpSplList->getStatGain() as $stat)
                Util::arraySumByKey($onUseStats, $stat);
        }

        return $onUseStats;
    }

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'n'    => $this->getField('name', true),
                't'    => TYPE_ITEM,
                'ti'   => $this->id,
                'q'    => $this->curTpl['quality'],
             // 'p'    => PvP [NYI]
                'icon' => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    private function canTeachSpell()
    {
        // 483: learn recipe; 55884: learn mount/pet
        if (!in_array($this->curTpl['spellId1'], [483, 55884]))
            return false;

        // needs learnable spell
        if (!$this->curTpl['spellId2'])
            return false;

        return true;
    }

    private function getFeralAP()
    {
        // must be weapon
        if ($this->curTpl['class'] != ITEM_CLASS_WEAPON)
            return 0;

        // must be 2H weapon (2H-Mace, Polearm, Staff, ..Fishing Pole)
        if (!in_array($this->curTpl['subClass'], [5, 6, 10, 20]))
            return 0;

        // thats fucked up..
        if (!$this->curTpl['delay'])
            return 0;

        // must have enough damage
        $dps = ($this->curTpl['dmgMin1'] + $this->curTpl['dmgMin2'] + $this->curTpl['dmgMax1'] + $this->curTpl['dmgMax2']) / (2 * $this->curTpl['delay'] / 1000);
        if ($dps < 54.8)
            return 0;

        return round(($dps - 54.8) * 14, 0);
    }

    public function getSources(&$s, &$sm)
    {
        $s = $sm = null;
        if (empty($this->sources[$this->id]))
            return false;

        if ($this->sourceMore === null)
        {
            $buff = [];
            $this->sourceMore = [];

            foreach ($this->iterate() as $_curTpl)
                if ($_curTpl['moreType'] && $_curTpl['moreTypeId'])
                    $buff[$_curTpl['moreType']][] = $_curTpl['moreTypeId'];

            foreach ($buff as $type => $ids)
                $this->sourceMore[$type] = (new Util::$typeClasses[$type](array(['id', $ids])))->getSourceData();
        }

        $s = array_keys($this->sources[$this->id]);
        if ($this->curTpl['moreType'] && $this->curTpl['moreTypeId'] && !empty($this->sourceMore[$this->curTpl['moreType']][$this->curTpl['moreTypeId']]))
            $sm = [$this->sourceMore[$this->curTpl['moreType']][$this->curTpl['moreTypeId']]];
        else if (!empty($this->sources[$this->id][3]))
            $sm = [['p' => $this->sources[$this->id][3][0]]];

        return true;
    }

    private function parseRating($type, $value, $interactive = false, &$scaling = false)
    {
        // clamp level range
        $ssdLvl = isset($this->ssd[$this->id]) ? $this->ssd[$this->id]['maxLevel'] : 1;
        $reqLvl = $this->curTpl['requiredLevel'] > 1 ? $this->curTpl['requiredLevel'] : MAX_LEVEL;
        $level  = min(max($reqLvl, $ssdLvl), MAX_LEVEL);

         // unknown rating
        if (in_array($type, [2, 8, 9, 10, 11]) || $type > ITEM_MOD_BLOCK_VALUE || $type < 0)
        {
            if (User::isInGroup(U_GROUP_EMPLOYEE))
                return sprintf(Lang::item('statType', count(Lang::item('statType')) - 1), $type, $value);
            else
                return null;
        }
        // level independant Bonus
        else if (in_array($type, Game::$lvlIndepRating))
            return Lang::item('trigger', 1).str_replace('%d', '<!--rtg'.$type.'-->'.$value, Lang::item('statType', $type));
        // rating-Bonuses
        else
        {
            $scaling = true;

            if ($interactive)
                $js = '&nbsp;<small>('.sprintf(Util::$changeLevelString, Util::setRatingLevel($level, $type, $value)).')</small>';
            else
                $js = '&nbsp;<small>('.Util::setRatingLevel($level, $type, $value).')</small>';

            return Lang::item('trigger', 1).str_replace('%d', '<!--rtg'.$type.'-->'.$value.$js, Lang::item('statType', $type));
        }
    }

    private function getSSDMod($type)
    {
        $mask = $this->curTpl['scalingStatValue'];

        switch ($type)
        {
            case 'stats':   $mask &= 0x04001F;  break;
            case 'armor':   $mask &= 0xF001E0;  break;
            case 'dps'  :   $mask &= 0x007E00;  break;
            case 'spell':   $mask &= 0x008000;  break;
            case 'fap'  :   $mask &= 0x010000;  break;      // unused
            default:        $mask &= 0x0;
        }

        $field = null;
        for ($i = 0; $i < count(Util::$ssdMaskFields); $i++)
            if ($mask & (1 << $i))
                $field = Util::$ssdMaskFields[$i];

        return $field ? DB::Aowow()->selectCell('SELECT ?# FROM ?_scalingstatvalues WHERE id = ?d', $field, $this->ssd[$this->id]['maxLevel']) : 0;
    }

    private function initScalingStats()
    {
        $this->ssd[$this->id] = DB::Aowow()->selectRow('SELECT * FROM ?_scalingstatdistribution WHERE id = ?d', $this->curTpl['scalingStatDistribution']);

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

            $this->templates[$this->id]['dmgMin1'] = floor((1 - $range) * $average);
            $this->templates[$this->id]['dmgMax1'] = floor((1 + $range) * $average);
        }

        // apply Spell Power from ScalingStatValue if set
        if ($spellBonus = $this->getSSDMod('spell'))
        {
            $this->templates[$this->id]['statType10']  = ITEM_MOD_SPELL_POWER;
            $this->templates[$this->id]['statValue10'] = $spellBonus;
        }
    }

    public function initSubItems()
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
        $subItemTpls = DB::World()->select('
            SELECT CAST( entry as SIGNED) AS ARRAY_KEY, CAST( ench as SIGNED) AS ARRAY_KEY2, chance FROM item_enchantment_template WHERE entry IN (?a) UNION
            SELECT CAST(-entry as SIGNED) AS ARRAY_KEY, CAST(-ench as SIGNED) AS ARRAY_KEY2, chance FROM item_enchantment_template WHERE entry IN (?a)',
            array_keys(array_filter($subItemIds, function ($v) { return $v > 0; })) ?: [0],
            array_keys(array_filter($subItemIds, function ($v) { return $v < 0; })) ?: [0]
        );

        $randIds = [];
        foreach ($subItemTpls as $tpl)
            $randIds = array_merge($randIds, array_keys($tpl));

        if (!$randIds)
            return;

        $randEnchants = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM ?_itemrandomenchant WHERE id IN (?a)', $randIds);
        $enchIds = array_unique(array_merge(
            array_column($randEnchants, 'enchantId1'),
            array_column($randEnchants, 'enchantId2'),
            array_column($randEnchants, 'enchantId3'),
            array_column($randEnchants, 'enchantId4'),
            array_column($randEnchants, 'enchantId5')
        ));

        $enchants = new EnchantmentList(array(['id', $enchIds], CFG_SQL_LIMIT_NONE));
        foreach ($enchants->iterate() as $eId => $_)
        {
            $this->rndEnchIds[$eId] = array(
                'text'  => $enchants->getField('name', true),
                'stats' => $enchants->getStatGain(true)
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

    public function getScoreTotal($class = 0, $spec = [], $mhItem = 0, $ohItem = 0)
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
            else if ($j['gearscore'])
            {
                if ($j['slot'] == INVTYPE_RELIC)
                    $score += 20;

                $score += round($j['gearscore']);
            }
        }

        $score += array_sum(Util::fixWeaponScores($class, $spec, $mh, $oh));

        return $score;
    }

    private function initJsonStats()
    {
        $json = array(
            'id'          => $this->id,
            'name'        => $this->getField('name', true),
            'quality'     => ITEM_QUALITY_HEIRLOOM - $this->curTpl['quality'],
            'icon'        => $this->curTpl['iconString'],
            'classs'      => $this->curTpl['class'],
            'subclass'    => $this->curTpl['subClass'],
            'subsubclass' => $this->curTpl['subSubClass'],
            'heroic'      => ($this->curTpl['flags'] & 0x8) >> 3,
            'side'        => $this->curTpl['flagsExtra'] & 0x3 ? 3 - ($this->curTpl['flagsExtra'] & 0x3) : Game::sideByRaceMask($this->curTpl['requiredRace']),
            'slot'        => $this->curTpl['slot'],
            'slotbak'     => $this->curTpl['slotBak'],
            'level'       => $this->curTpl['itemLevel'],
            'reqlevel'    => $this->curTpl['requiredLevel'],
            'displayid'   => $this->curTpl['displayId'],
            // 'commondrop'  => 'true' / null               // set if the item is a loot-filler-item .. check common ref-templates..?
            'holres'      => $this->curTpl['resHoly'],
            'firres'      => $this->curTpl['resFire'],
            'natres'      => $this->curTpl['resNature'],
            'frores'      => $this->curTpl['resFrost'],
            'shares'      => $this->curTpl['resShadow'],
            'arcres'      => $this->curTpl['resArcane'],
            'armorbonus'  => max(0, intVal($this->curTpl['armorDamageModifier'])),
            'armor'       => $this->curTpl['armor'],
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

        if ($this->curTpl['class'] == ITEM_CLASS_WEAPON || $this->curTpl['class'] == ITEM_CLASS_AMMUNITION)
        {

            $json['dmgtype1'] = $this->curTpl['dmgType1'];
            $json['dmgmin1']  = $this->curTpl['dmgMin1'] + $this->curTpl['dmgMin2'];
            $json['dmgmax1']  = $this->curTpl['dmgMax1'] + $this->curTpl['dmgMax2'];
            $json['speed']    = number_format($this->curTpl['delay'] / 1000, 2);
            $json['dps']      = !floatVal($json['speed']) ? 0 : number_format(($json['dmgmin1'] + $json['dmgmax1']) / (2 * $json['speed']), 1);

            if (in_array($json['subclass'], [2, 3, 18, 19]))
            {
                $json['rgddmgmin'] = $json['dmgmin1'];
                $json['rgddmgmax'] = $json['dmgmax1'];
                $json['rgdspeed']  = $json['speed'];
                $json['rgddps']    = $json['dps'];
            }
            else if ($json['classs'] != ITEM_CLASS_AMMUNITION)
            {
                $json['mledmgmin'] = $json['dmgmin1'];
                $json['mledmgmax'] = $json['dmgmax1'];
                $json['mlespeed']  = $json['speed'];
                $json['mledps']    = $json['dps'];
            }

            if ($fap = $this->getFeralAP())
                $json['feratkpwr'] = $fap;
        }

        if ($this->curTpl['class'] == ITEM_CLASS_ARMOR || $this->curTpl['class'] == ITEM_CLASS_WEAPON)
            $json['gearscore'] = Util::getEquipmentScore($json['level'], $this->getField('quality'), $json['slot'], $json['nsockets']);
        else if ($this->curTpl['class'] == ITEM_CLASS_GEM)
            $json['gearscore'] = Util::getGemScore($json['level'], $this->getField('quality'), $this->getField('requiredSkill') == 755, $this->id);

        // clear zero-values afterwards
        foreach ($json as $k => $v)
            if (!$v && !in_array($k, ['classs', 'subclass', 'quality', 'side', 'gearscore']))
                unset($json[$k]);

        Util::checkNumeric($json);

        $this->json[$json['id']] = $json;
    }

    public function addRewardsToJScript(&$ref) { }
}


class ItemListFilter extends Filter
{
    private   $ubFilter      = [];                          // usable-by - limit weapon/armor selection per CharClass - itemClass => available itemsubclasses
    private   $extCostQuery  = 'SELECT item FROM npc_vendor            WHERE extendedCost IN (?a) UNION
                                SELECT item FROM game_event_npc_vendor WHERE extendedCost IN (?a)';
    private   $otFields      = [18 => 4, 68 => 15, 69 => 16, 70 => 17, 72 => 2, 73 => 19, 75 => 21, 76 => 23, 88 => 20, 92 => 5, 93 => 3, 143 => 18, 171 => 8, 172 => 12];

    public    $extraOpts     = [];                          // score for statWeights
    public    $wtCnd         = [];
    protected $enums         = array(
        99 => array(                                        // profession | recycled for 86, 87
            null, 171, 164, 185, 333, 202, 129, 755, 165, 186, 197, true, false, 356, 182, 773
        ),
        66 => array(                                        // profession specialization
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
        152 => array(                                       // class-specific
            null, 1, 2, 3, 4, 5, 6, 7, 8, 9, null, 11, true, false
        ),
        153 => array(                                       // race-specific
            null, 1, 2, 3, 4, 5, 6, 7, 8, null, 10, 11, true, false
        ),
        158 => array(                                       // currency
            32572, 32569, 29736, 44128, 20560, 20559, 29434, 37829, 23247, 44990, 24368, 52027, 52030, 43016, 41596, 34052, 45624, 49426, 40752, 47241, 40753, 29024,
            24245, 26045, 26044, 38425, 29735, 24579, 24581, 32897, 22484, 52026, 52029,  4291, 28558, 43228, 34664, 47242, 52025, 52028, 37836, 20558, 34597, 43589
        ),
        118 => array(                                       // tokens
            34853, 34854, 34855, 34856, 34857, 34858, 34848, 34851, 34852, 40625, 40626, 40627, 45632, 45633, 45634, 34169, 34186, 29754, 29753, 29755, 31089, 31091, 31090,
            40610, 40611, 40612, 30236, 30237, 30238, 45635, 45636, 45637, 34245, 34332, 34339, 34345, 40631, 40632, 40633, 45638, 45639, 45640, 34244, 34208, 34180, 34229,
            34350, 40628, 40629, 40630, 45641, 45642, 45643, 29757, 29758, 29756, 31092, 31094, 31093, 40613, 40614, 40615, 30239, 30240, 30241, 45644, 45645, 45646, 34342,
            34211, 34243, 29760, 29761, 29759, 31097, 31095, 31096, 40616, 40617, 40618, 30242, 30243, 30244, 45647, 45648, 45649, 34216, 29766, 29767, 29765, 31098, 31100,
            31099, 40619, 40620, 40621, 30245, 30246, 30247, 45650, 45651, 45652, 34167, 40634, 40635, 40636, 45653, 45654, 45655, 40637, 40638, 40639, 45656, 45657, 45658,
            34170, 34192, 29763, 29764, 29762, 31101, 31103, 31102, 30248, 30249, 30250, 47557, 47558, 47559, 34233, 34234, 34202, 34195, 34209, 40622, 40623, 40624, 34193,
            45659, 45660, 45661, 34212, 34351, 34215
        ),
        128 => array(                                       // source
             1 => true,                                     // Any
             2 => false,                                    // None
             3 => 1,                                        // Crafted
             4 => 2,                                        // Drop
             5 => 3,                                        // PvP
             6 => 4,                                        // Quest
             7 => 5,                                        // Vendor
             9 => 10,                                       // Starter
            10 => 11,                                       // Event
            11 => 12                                        // Achievement
        ),
        126 => array(                                       // Zones
            4494,   36, 2597, 3358,   45,  331, 3790, 4277,   16, 3524,    3, 3959,  719, 1584,   25, 1583, 2677, 3702, 3522,    4, 3525, 3537,   46, 1941,
            2918, 3905, 4024, 2817, 4395, 4378,  148,  393, 1657,   41, 2257,  405, 2557,   65, 4196,    1,   14,   10,   15,  139,   12, 3430, 3820,  361,
             357, 3433,  721,  394, 3923, 4416, 2917, 4272, 4820, 4264, 3483, 3562,  267,  495, 4742, 3606,  210, 4812, 1537, 4710, 4080, 3457,   38, 4131,
            3836, 3792, 2100, 2717,  493,  215, 3518, 3698, 3456, 3523, 2367, 2159, 1637, 4813, 4298, 2437,  722,  491,   44, 3429, 3968,  796, 2057,   51,
            3607, 3791, 3789,  209, 3520, 3703, 3711, 1377, 3487,  130, 3679,  406, 1519, 4384,   33, 2017, 1477, 4075,    8,  440,  141, 3428, 3519, 3848,
              17, 2366, 3840, 3713, 3847, 3775, 4100, 1581, 3557, 3845, 4500, 4809,   47, 3849, 4265, 4493, 4228, 3698, 4406, 3714, 3717, 3715,  717,   67,
            3716,  457, 4415,  400, 1638, 1216,   85, 4723, 4722, 1337, 4273,  490, 1497,  206, 1196, 4603, 718, 3277,    28,   40,   11, 4197,  618, 3521,
            3805,   66, 1176, 1977
        ),
        163 => array(                                       // enchantment mats
            34057, 22445, 11176, 34052, 11082, 34055, 16203, 10939, 11135, 11175, 22446, 16204, 34054, 14344, 11084, 11139, 22449, 11178,
            10998, 34056, 16202, 10938, 11134, 11174, 22447, 20725, 14343, 34053, 10978, 11138, 22448, 11177, 11083, 10940, 11137, 22450
        )
    );

    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
          2 => [FILTER_CR_CALLBACK,  'cbFieldHasVal',          'bonding',               1             ], // bindonpickup [yn]
          3 => [FILTER_CR_CALLBACK,  'cbFieldHasVal',          'bonding',               2             ], // bindonequip [yn]
          4 => [FILTER_CR_CALLBACK,  'cbFieldHasVal',          'bonding',               3             ], // bindonuse [yn]
          5 => [FILTER_CR_CALLBACK,  'cbFieldHasVal',          'bonding',               [4, 5]        ], // questitem [yn]
          6 => [FILTER_CR_CALLBACK,  'cbQuestRelation',        null,                    null          ], // startsquest [side]
          7 => [FILTER_CR_BOOLEAN,   'description_loc0',       true                                   ], // hasflavortext
          8 => [FILTER_CR_BOOLEAN,   'requiredDisenchantSkill'                                        ], // disenchantable
          9 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_CONJURED                     ], // conjureditem
         10 => [FILTER_CR_BOOLEAN,   'lockId'                                                         ], // locked
         11 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_OPENABLE                     ], // openable
         12 => [FILTER_CR_BOOLEAN,   'itemset'                                                        ], // partofset
         13 => [FILTER_CR_BOOLEAN,   'randomEnchant'                                                  ], // randomlyenchanted
         14 => [FILTER_CR_BOOLEAN,   'pageTextId'                                                     ], // readable
         15 => [FILTER_CR_CALLBACK,  'cbFieldHasVal',          'maxCount',              1             ], // unique [yn]
         16 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsin [zone]
         17 => [FILTER_CR_ENUM,      'requiredFaction'                                                ], // requiresrepwith
         18 => [FILTER_CR_CALLBACK,  'cbFactionQuestReward',   null,                    null          ], // rewardedbyfactionquest [side]
         20 => [FILTER_CR_NUMERIC,   'is.str',                 NUM_CAST_INT,            true          ], // str
         21 => [FILTER_CR_NUMERIC,   'is.agi',                 NUM_CAST_INT,            true          ], // agi
         22 => [FILTER_CR_NUMERIC,   'is.sta',                 NUM_CAST_INT,            true          ], // sta
         23 => [FILTER_CR_NUMERIC,   'is.int',                 NUM_CAST_INT,            true          ], // int
         24 => [FILTER_CR_NUMERIC,   'is.spi',                 NUM_CAST_INT,            true          ], // spi
         25 => [FILTER_CR_NUMERIC,   'is.arcres',              NUM_CAST_INT,            true          ], // arcres
         26 => [FILTER_CR_NUMERIC,   'is.firres',              NUM_CAST_INT,            true          ], // firres
         27 => [FILTER_CR_NUMERIC,   'is.natres',              NUM_CAST_INT,            true          ], // natres
         28 => [FILTER_CR_NUMERIC,   'is.frores',              NUM_CAST_INT,            true          ], // frores
         29 => [FILTER_CR_NUMERIC,   'is.shares',              NUM_CAST_INT,            true          ], // shares
         30 => [FILTER_CR_NUMERIC,   'is.holres',              NUM_CAST_INT,            true          ], // holres
         32 => [FILTER_CR_NUMERIC,   'is.dps',                 NUM_CAST_FLOAT,          true          ], // dps
         33 => [FILTER_CR_NUMERIC,   'is.dmgmin1',             NUM_CAST_INT,            true          ], // dmgmin1
         34 => [FILTER_CR_NUMERIC,   'is.dmgmax1',             NUM_CAST_INT,            true          ], // dmgmax1
         35 => [FILTER_CR_CALLBACK,  'cbDamageType',           null,                    null          ], // damagetype [enum]
         36 => [FILTER_CR_NUMERIC,   'is.speed',               NUM_CAST_FLOAT,          true          ], // speed
         37 => [FILTER_CR_NUMERIC,   'is.mleatkpwr',           NUM_CAST_INT,            true          ], // mleatkpwr
         38 => [FILTER_CR_NUMERIC,   'is.rgdatkpwr',           NUM_CAST_INT,            true          ], // rgdatkpwr
         39 => [FILTER_CR_NUMERIC,   'is.rgdhitrtng',          NUM_CAST_INT,            true          ], // rgdhitrtng
         40 => [FILTER_CR_NUMERIC,   'is.rgdcritstrkrtng',     NUM_CAST_INT,            true          ], // rgdcritstrkrtng
         41 => [FILTER_CR_NUMERIC,   'is.armor',               NUM_CAST_INT,            true          ], // armor
         42 => [FILTER_CR_NUMERIC,   'is.defrtng',             NUM_CAST_INT,            true          ], // defrtng
         43 => [FILTER_CR_NUMERIC,   'is.block',               NUM_CAST_INT,            true          ], // block
         44 => [FILTER_CR_NUMERIC,   'is.blockrtng',           NUM_CAST_INT,            true          ], // blockrtng
         45 => [FILTER_CR_NUMERIC,   'is.dodgertng',           NUM_CAST_INT,            true          ], // dodgertng
         46 => [FILTER_CR_NUMERIC,   'is.parryrtng',           NUM_CAST_INT,            true          ], // parryrtng
         48 => [FILTER_CR_NUMERIC,   'is.splhitrtng',          NUM_CAST_INT,            true          ], // splhitrtng
         49 => [FILTER_CR_NUMERIC,   'is.splcritstrkrtng',     NUM_CAST_INT,            true          ], // splcritstrkrtng
         50 => [FILTER_CR_NUMERIC,   'is.splheal',             NUM_CAST_INT,            true          ], // splheal
         51 => [FILTER_CR_NUMERIC,   'is.spldmg',              NUM_CAST_INT,            true          ], // spldmg
         52 => [FILTER_CR_NUMERIC,   'is.arcsplpwr',           NUM_CAST_INT,            true          ], // arcsplpwr
         53 => [FILTER_CR_NUMERIC,   'is.firsplpwr',           NUM_CAST_INT,            true          ], // firsplpwr
         54 => [FILTER_CR_NUMERIC,   'is.frosplpwr',           NUM_CAST_INT,            true          ], // frosplpwr
         55 => [FILTER_CR_NUMERIC,   'is.holsplpwr',           NUM_CAST_INT,            true          ], // holsplpwr
         56 => [FILTER_CR_NUMERIC,   'is.natsplpwr',           NUM_CAST_INT,            true          ], // natsplpwr
         57 => [FILTER_CR_NUMERIC,   'is.shasplpwr',           NUM_CAST_INT,            true          ], // shasplpwr
         59 => [FILTER_CR_NUMERIC,   'durability',             NUM_CAST_INT,            true          ], // dura
         60 => [FILTER_CR_NUMERIC,   'is.healthrgn',           NUM_CAST_INT,            true          ], // healthrgn
         61 => [FILTER_CR_NUMERIC,   'is.manargn',             NUM_CAST_INT,            true          ], // manargn
         62 => [FILTER_CR_CALLBACK,  'cbCooldown',             null,                    null          ], // cooldown [op] [int]
         63 => [FILTER_CR_NUMERIC,   'buyPrice',               NUM_CAST_INT,            true          ], // buyprice
         64 => [FILTER_CR_NUMERIC,   'sellPrice',              NUM_CAST_INT,            true          ], // sellprice
         65 => [FILTER_CR_CALLBACK,  'cbAvgMoneyContent',      null,                    null          ], // avgmoney [op] [int]
         66 => [FILTER_CR_ENUM,      'requiredSpell'                                                  ], // requiresprofspec
         68 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otdisenchanting [yn]
         69 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otfishing [yn]
         70 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otherbgathering [yn]
         71 => [FILTER_CR_FLAG,      'cuFlags',                ITEM_CU_OT_ITEMLOOT                    ], // otitemopening [yn]
         72 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otlooting [yn]
         73 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otmining [yn]
         74 => [FILTER_CR_FLAG,      'cuFlags',                ITEM_CU_OT_OBJECTLOOT                  ], // otobjectopening [yn]
         75 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otpickpocketing [yn]
         76 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otskinning [yn]
         77 => [FILTER_CR_NUMERIC,   'is.atkpwr',              NUM_CAST_INT,            true          ], // atkpwr
         78 => [FILTER_CR_NUMERIC,   'is.mlehastertng',        NUM_CAST_INT,            true          ], // mlehastertng
         79 => [FILTER_CR_NUMERIC,   'is.resirtng',            NUM_CAST_INT,            true          ], // resirtng
         80 => [FILTER_CR_CALLBACK,  'cbHasSockets',           null,                    null          ], // has sockets [enum]
         81 => [FILTER_CR_CALLBACK,  'cbFitsGemSlot',          null,                    null          ], // fits gem slot [enum]
         83 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_UNIQUEEQUIPPED               ], // uniqueequipped
         84 => [FILTER_CR_NUMERIC,   'is.mlecritstrkrtng',     NUM_CAST_INT,            true          ], // mlecritstrkrtng
         85 => [FILTER_CR_CALLBACK,  'cbObjectiveOfQuest',     null,                    null          ], // objectivequest [side]
         86 => [FILTER_CR_CALLBACK,  'cbCraftedByProf',        null,                    null          ], // craftedprof [enum]
         87 => [FILTER_CR_CALLBACK,  'cbReagentForAbility',    null,                    null          ], // reagentforability [enum]
         88 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otprospecting [yn]
         89 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_PROSPECTABLE                 ], // prospectable
         90 => [FILTER_CR_CALLBACK,  'cbAvgBuyout',            null,                    null          ], // avgbuyout [op] [int]
         91 => [FILTER_CR_ENUM,      'totemCategory'                                                  ], // tool
         92 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // soldbyvendor [yn]
         93 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otpvp [pvp]
         94 => [FILTER_CR_NUMERIC,   'is.splpen',              NUM_CAST_INT,            true          ], // splpen
         95 => [FILTER_CR_NUMERIC,   'is.mlehitrtng',          NUM_CAST_INT,            true          ], // mlehitrtng
         96 => [FILTER_CR_NUMERIC,   'is.critstrkrtng',        NUM_CAST_INT,            true          ], // critstrkrtng
         97 => [FILTER_CR_NUMERIC,   'is.feratkpwr',           NUM_CAST_INT,            true          ], // feratkpwr
         98 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_PARTYLOOT                    ], // partyloot
         99 => [FILTER_CR_ENUM,      'requiredSkill'                                                  ], // requiresprof
        100 => [FILTER_CR_NUMERIC,   'is.nsockets',            NUM_CAST_INT                           ], // nsockets
        101 => [FILTER_CR_NUMERIC,   'is.rgdhastertng',        NUM_CAST_INT,            true          ], // rgdhastertng
        102 => [FILTER_CR_NUMERIC,   'is.splhastertng',        NUM_CAST_INT,            true          ], // splhastertng
        103 => [FILTER_CR_NUMERIC,   'is.hastertng',           NUM_CAST_INT,            true          ], // hastertng
        104 => [FILTER_CR_STRING,    'description',            STR_LOCALIZED                          ], // flavortext
        105 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsinnormal [heroicdungeon-any]
        106 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsinheroic [heroicdungeon-any]
        107 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // effecttext [str]                 not yet parsed              ['effectsParsed_loc'.User::$localeId, $cr[2]]
        109 => [FILTER_CR_CALLBACK,  'cbArmorBonus',           null,                    null          ], // armorbonus [op] [int]
        111 => [FILTER_CR_NUMERIC,   'requiredSkillRank',      NUM_CAST_INT,            true          ], // reqskillrank
        113 => [FILTER_CR_FLAG,      'cuFlags',                CUSTOM_HAS_SCREENSHOT                  ], // hasscreenshots
        114 => [FILTER_CR_NUMERIC,   'is.armorpenrtng',        NUM_CAST_INT,            true          ], // armorpenrtng
        115 => [FILTER_CR_NUMERIC,   'is.health',              NUM_CAST_INT,            true          ], // health
        116 => [FILTER_CR_NUMERIC,   'is.mana',                NUM_CAST_INT,            true          ], // mana
        117 => [FILTER_CR_NUMERIC,   'is.exprtng',             NUM_CAST_INT,            true          ], // exprtng
        118 => [FILTER_CR_CALLBACK,  'cbPurchasableWith',      null,                    null          ], // purchasablewithitem [enum]
        119 => [FILTER_CR_NUMERIC,   'is.hitrtng',             NUM_CAST_INT,            true          ], // hitrtng
        123 => [FILTER_CR_NUMERIC,   'is.splpwr',              NUM_CAST_INT,            true          ], // splpwr
        124 => [FILTER_CR_CALLBACK,  'cbHasRandEnchant',       null,                    null          ], // randomenchants [str]
        125 => [FILTER_CR_CALLBACK,  'cbReqArenaRating',       null,                    null          ], // reqarenartng [op] [int]  todo (low): 'find out, why "IN (W, X, Y) AND IN (X, Y, Z)" doesn't result in "(X, Y)"
        126 => [FILTER_CR_CALLBACK,  'cbQuestRewardIn',        null,                    null          ], // rewardedbyquestin [zone-any]
        128 => [FILTER_CR_CALLBACK,  'cbSource',               null,                    null          ], // source [enum]
        129 => [FILTER_CR_CALLBACK,  'cbSoldByNPC',            null,                    null          ], // soldbynpc [str-small]
        130 => [FILTER_CR_FLAG,      'cuFlags',                CUSTOM_HAS_COMMENT                     ], // hascomments
        132 => [FILTER_CR_CALLBACK,  'cbGlyphType',            null,                    null          ], // glyphtype [enum]
        133 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_ACCOUNTBOUND                 ], // accountbound
        134 => [FILTER_CR_NUMERIC,   'is.mledps',              NUM_CAST_FLOAT,          true          ], // mledps
        135 => [FILTER_CR_NUMERIC,   'is.mledmgmin',           NUM_CAST_INT,            true          ], // mledmgmin
        136 => [FILTER_CR_NUMERIC,   'is.mledmgmax',           NUM_CAST_INT,            true          ], // mledmgmax
        137 => [FILTER_CR_NUMERIC,   'is.mlespeed',            NUM_CAST_FLOAT,          true          ], // mlespeed
        138 => [FILTER_CR_NUMERIC,   'is.rgddps',              NUM_CAST_FLOAT,          true          ], // rgddps
        139 => [FILTER_CR_NUMERIC,   'is.rgddmgmin',           NUM_CAST_INT,            true          ], // rgddmgmin
        140 => [FILTER_CR_NUMERIC,   'is.rgddmgmax',           NUM_CAST_INT,            true          ], // rgddmgmax
        141 => [FILTER_CR_NUMERIC,   'is.rgdspeed',            NUM_CAST_FLOAT,          true          ], // rgdspeed
        142 => [FILTER_CR_STRING,    'ic.name'                                                        ], // icon
        143 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otmilling [yn]
        144 => [FILTER_CR_CALLBACK,  'cbPvpPurchasable',       'reqHonorPoints',        null          ], // purchasablewithhonor [yn]
        145 => [FILTER_CR_CALLBACK,  'cbPvpPurchasable',       'reqHonorPoints',        null          ], // purchasablewitharena [yn]
        146 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_HEROIC                       ], // heroic
        147 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsinnormal10 [multimoderaid-any]
        148 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsinnormal25 [multimoderaid-any]
        149 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsinheroic10 [heroicraid-any]
        150 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // dropsinheroic25 [heroicraid-any]
        151 => [FILTER_CR_NUMERIC,   'id',                     NUM_CAST_INT,            true          ], // id
        152 => [FILTER_CR_CALLBACK,  'cbClassRaceSpec',        'requiredClass',         CLASS_MASK_ALL], // classspecific [enum]
        153 => [FILTER_CR_CALLBACK,  'cbClassRaceSpec',        'requiredRace',          RACE_MASK_ALL ], // racespecific [enum]
        154 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_REFUNDABLE                   ], // refundable
        155 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_ARENA                 ], // usableinarenas
        156 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_SHAPED                ], // usablewhenshapeshifted
        157 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_SMARTLOOT                    ], // smartloot
        158 => [FILTER_CR_CALLBACK,  'cbPurchasableWith',      null,                    null          ], // purchasablewithcurrency [enum]
        159 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_MILLABLE                     ], // millable
        160 => [FILTER_CR_NYI_PH,    null,                     1,                                     ], // relatedevent [enum]      like 169 .. crawl though npc_vendor and loot_templates of event-related spawns
        161 => [FILTER_CR_CALLBACK,  'cbAvailable',            null,                    null          ], // availabletoplayers [yn]
        162 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_DEPRECATED                   ], // deprecated
        163 => [FILTER_CR_CALLBACK,  'cbDisenchantsInto',      null,                    null          ], // disenchantsinto [disenchanting]
        165 => [FILTER_CR_NUMERIC,   'repairPrice',            NUM_CAST_INT,            true          ], // repaircost
        167 => [FILTER_CR_FLAG,      'cuFlags',                CUSTOM_HAS_VIDEO                       ], // hasvideos
        168 => [FILTER_CR_CALLBACK,  'cbFieldHasVal',          'spellId1',              [483, 55884]  ], // teachesspell [yn] - 483: learn recipe; 55884: learn mount/pet
        169 => [FILTER_CR_ENUM,      'e.holidayId'                                                    ], // requiresevent
        171 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // otredemption [yn]
        172 => [FILTER_CR_CALLBACK,  'cbObtainedBy',           null,                    null          ], // rewardedbyachievement [yn]
        176 => [FILTER_CR_STAFFFLAG, 'flags'                                                          ], // flags
        177 => [FILTER_CR_STAFFFLAG, 'flagsExtra'                                                     ], // flags2
    );

    // fieldId => [checkType, checkValue[, fieldIsArray]]
    protected $inputFields = array(
        'wt'    => [FILTER_V_CALLBACK, 'cbWeightKeyCheck',                              true ], // weight keys
        'wtv'   => [FILTER_V_RANGE,    [1, 999],                                        true ], // weight values
        'jc'    => [FILTER_V_LIST,     [1],                                             false], // use jewelcrafter gems for weight calculation
        'gm'    => [FILTER_V_LIST,     [2, 3, 4],                                       false], // gem rarity for weight calculation
        'cr'    => [FILTER_V_RANGE,    [1, 177],                                        true ], // criteria ids
        'crs'   => [FILTER_V_LIST,     [FILTER_ENUM_NONE, FILTER_ENUM_ANY, [0, 99999]], true ], // criteria operators
        'crv'   => [FILTER_V_REGEX,    '/[\p{C};:%\\\\]/ui',                            true ], // criteria values - only printable chars, no delimiters
        'upg'   => [FILTER_V_RANGE,    [1, 999999],                                     true ], // upgrade item ids
        'gb'    => [FILTER_V_LIST,     [0, 1, 2, 3],                                    false], // search result grouping
        'na'    => [FILTER_V_REGEX,    '/[\p{C};%\\\\]/ui',                             false], // name - only printable chars, no delimiter
        'ma'    => [FILTER_V_EQUAL,    1,                                               false], // match any / all filter
        'ub'    => [FILTER_V_LIST,     [[1, 9], 11],                                    false], // usable by classId
        'qu'    => [FILTER_V_RANGE,    [0, 7],                                          true ], // quality ids
        'ty'    => [FILTER_V_CALLBACK, 'cbTypeCheck',                                   true ], // item type - dynamic by current group
        'sl'    => [FILTER_V_CALLBACK, 'cbSlotCheck',                                   true ], // item slot - dynamic by current group
        'si'    => [FILTER_V_LIST,     [1, 2, 3, -1, -2],                               false], // side
        'minle' => [FILTER_V_RANGE,    [1, 999],                                        false], // item level min
        'maxle' => [FILTER_V_RANGE,    [1, 999],                                        false], // item level max
        'minrl' => [FILTER_V_RANGE,    [1, MAX_LEVEL],                                  false], // required level min
        'maxrl' => [FILTER_V_RANGE,    [1, MAX_LEVEL],                                  false]  // required level max
    );

    public function __construct($fromPOST = false, $opts = [])
    {
        $classes = new CharClassList();
        foreach ($classes->iterate() as $cId => $_tpl)
        {
            // preselect misc subclasses
            $this->ubFilter[$cId] = [ITEM_CLASS_WEAPON => [14], ITEM_CLASS_ARMOR => [0]];

            for ($i = 0; $i < 21; $i++)
                if ($_tpl['weaponTypeMask'] & (1 << $i))
                    $this->ubFilter[$cId][ITEM_CLASS_WEAPON][] = $i;

            for ($i = 0; $i < 11; $i++)
                if ($_tpl['armorTypeMask'] & (1 << $i))
                    $this->ubFilter[$cId][ITEM_CLASS_ARMOR][] = $i;
        }

        parent::__construct($fromPOST, $opts);
    }

    public function createConditionsForWeights()
    {
        if (empty($this->fiData['v']['wt']))
            return null;

        $this->wtCnd = [];
        $select = [];
        $wtSum  = 0;

        foreach ($this->fiData['v']['wt'] as $k => $v)
        {
            $str = Util::$itemFilter[$v];
            $qty = intVal($this->fiData['v']['wtv'][$k]);

            if ($str == 'rgdspeed')                     // dont need no duplicate column
                $str = 'speed';

            if ($str == 'mledps')                       // todo (med): unify rngdps and mledps to dps
                $str = 'dps';

            $select[]      = '(`is`.`'.$str.'` * '.$qty.')';
            $this->wtCnd[] = ['is.'.$str, 0, '>'];
            $wtSum        += $qty;
        }

        if (count($this->wtCnd) > 1)
            array_unshift($this->wtCnd, 'OR');
        else if (count($this->wtCnd) == 1)
            $this->wtCnd = $this->wtCnd[0];

        if ($select)
        {
            $this->extraOpts['is']['s'][] = ', IF(is.typeId IS NULL, 0, ('.implode(' + ', $select).') / '.$wtSum.') AS score';
            $this->extraOpts['is']['o'][] = 'score DESC';
            $this->extraOpts['i']['o'][]  = null;           // remove default ordering
        }
        else
            $this->extraOpts['is']['s'][] = ', 0 AS score'; // prevent errors

        return $this->wtCnd;
    }

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

        unset($cr);
        $this->error = true;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // weights
        if (!empty($_v['wt']) && !empty($_v['wtv']))
        {
            // gm  - gem quality (qualityId)
            // jc  - jc-gems included (bool)

            $parts[] = $this->createConditionsForWeights();

            foreach ($_v['wt'] as $_)
                $this->formData['extraCols'][] = $_;
        }

        // upgrade for [form only]
        if (isset($_v['upg']))
        {
            $_ = DB::Aowow()->selectCol('SELECT id as ARRAY_KEY, slot FROM ?_items WHERE class IN (2, 3, 4) AND id IN (?a)', (array)$_v['upg']);
            if ($_ === null)
            {
                unset($_v['upg']);
                unset($this->formData['form']['upg']);
            }
            else
            {
                $this->formData['form']['upg'] = $_;
                if ($_)
                    $parts[] = ['slot', $_];
            }
        }

        // group by [form only]
        if (isset($_v['gb']))
            $this->formData['form']['gb'] = $_v['gb'];

        // name
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name_loc'.User::$localeId]))
                $parts[] = $_;

        // usable-by (not excluded by requiredClass && armor or weapons match mask from ?_classes)
        if (isset($_v['ub']))
        {
            $parts[] = array(
                'AND',
                ['OR', ['requiredClass', 0], ['requiredClass', $this->list2Mask((array)$_v['ub']), '&']],
                [
                    'OR',
                    ['class', [2, 4], '!'],
                    ['AND', ['class', 2], ['subclassbak', $this->ubFilter[$_v['ub']][ITEM_CLASS_WEAPON]]],
                    ['AND', ['class', 4], ['subclassbak', $this->ubFilter[$_v['ub']][ITEM_CLASS_ARMOR]]]
                ]
            );
        }

        // quality [list]
        if (isset($_v['qu']))
            $parts[] = ['quality', $_v['qu']];

        // type
        if (isset($_v['ty']))
            $parts[] = ['subclass', $_v['ty']];

        // slot
        if (isset($_v['sl']))
            $parts[] = ['slot', $_v['sl']];

        // side
        if (isset($_v['si']))
        {
            $ex    = [['requiredRace', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'];
            $notEx = ['OR', ['requiredRace', 0], [['requiredRace', RACE_MASK_ALL, '&'], RACE_MASK_ALL]];

            switch ($_v['si'])
            {
                case  3:
                    $parts[] = $notEx;
                    break;
                case  2:
                    $parts[] = ['AND', [['flagsExtra', 0x3, '&'], [0, 1]],  ['OR', $notEx, ['requiredRace', RACE_MASK_HORDE, '&']]];
                    break;
                case -2:
                    $parts[] = ['OR',  [['flagsExtra', 0x3, '&'], 1],       ['AND', $ex,   ['requiredRace', RACE_MASK_HORDE, '&']]];
                    break;
                case  1:
                    $parts[] = ['AND', [['flagsExtra', 0x3, '&'], [0, 2]],  ['OR', $notEx, ['requiredRace', RACE_MASK_ALLIANCE, '&']]];
                    break;
                case -1:
                    $parts[] = ['OR',  [['flagsExtra', 0x3, '&'], 2],       ['AND', $ex,   ['requiredRace', RACE_MASK_ALLIANCE, '&']]];
                    break;
            }
        }

        // itemLevel min
        if (isset($_v['minle']))
            $parts[] = ['itemLevel', $_v['minle'], '>='];

        // itemLevel max
        if (isset($_v['maxle']))
            $parts[] = ['itemLevel', $_v['maxle'], '<='];

        // reqLevel min
        if (isset($_v['minrl']))
            $parts[] = ['requiredLevel', $_v['minrl'], '>='];

        // reqLevel max
        if (isset($_v['maxrl']))
            $parts[] = ['requiredLevel', $_v['maxrl'], '<='];

        return $parts;
    }

    protected function cbFactionQuestReward($cr)
    {
        if (!isset($this->otFields[$cr[0]]))
            return false;

        $field = 'src.src'.$this->otFields[$cr[0]];
        switch ($cr[1])
        {
            case 1:                                         // Yes
                return [$field, null, '!'];
            case 2:                                         // Alliance
                return [$field, 1];
            case 3:                                         // Horde
                return [$field, 2];
            case 4:                                         // Both
                return [$field, 3];
            case 5:                                         // No
                return [$field, null];
        }

        return false;
    }

    protected function cbAvailable($cr)
    {
        if ($this->int2Bool($cr[1]))
            return [['cuFlags', CUSTOM_UNAVAILABLE, '&'], 0, $cr[1] ? null : '!'];

        return false;
    }

    protected function cbHasSockets($cr)
    {
        switch ($cr[1])
        {
            case 5:                                         // Yes
                return ['is.nsockets', 0, '!'];
            case 6:                                         // No
                return ['is.nsockets', 0];
            case 1:                                         // Meta
            case 2:                                         // Red
            case 3:                                         // Yellow
            case 4:                                         // Blue
                $mask = 1 << ($cr[1] - 1);
                return ['OR', ['socketColor1', $mask], ['socketColor2', $mask], ['socketColor3', $mask]];
        }

        return false;
    }

    protected function cbFitsGemSlot($cr)
    {
        switch ($cr[1])
        {
            case 5:                                         // Yes
                return ['gemEnchantmentId', 0, '!'];
            case 6:                                         // No
                return ['gemEnchantmentId', 0];
            case 1:                                         // Meta
            case 2:                                         // Red
            case 3:                                         // Yellow
            case 4:                                         // Blue
                $mask = 1 << ($cr[1] - 1);
                return ['AND', ['gemEnchantmentId', 0, '!'], ['gemColorMask', $mask, '&']];
        }
    }

    protected function cbGlyphType($cr)
    {
        switch ($cr[1])
        {
            case 1:                                         // Major
            case 2:                                         // Minor
                return ['AND', ['class', 16], ['subSubClass', $cr[1]]];
        }

        return false;
    }

    protected function cbHasRandEnchant($cr)
    {
        $randIds = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, ABS(id) FROM ?_itemrandomenchant WHERE name_loc?d LIKE ?', User::$localeId, '%'.$cr[2].'%');
        $tplIds  = $randIds ? DB::World()->select('SELECT entry, ench FROM item_enchantment_template WHERE ench IN (?a)', $randIds) : [];
        foreach ($tplIds as $k => &$set)
            if (array_search($set['ench'], $randIds) < 0)
                $set['entry'] *= -1;

        if ($tplIds)
            return ['randomEnchant', array_column($tplIds, 'entry')];
        else
            return [0];                                     // no results aren't really input errors
    }

    protected function cbReqArenaRating($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        $this->formData['extraCols'][] = $cr[0];

        $costs = DB::Aowow()->selectCol('SELECT id FROM ?_itemextendedcost WHERE reqPersonalrating '.$cr[1].' '.$cr[2]);
        $items = DB::World()->selectCol($this->extCostQuery, $costs, $costs);
        return ['id', $items];
    }

    protected function cbClassRaceSpec($cr, $field, $mask)
    {
        if (!isset($this->enums[$cr[0]][$cr[1]]))
            return false;

        $_ = $this->enums[$cr[0]][$cr[1]];
        if (is_bool($_))
            return $_ ? ['AND', [[$field, $mask, '&'], $mask, '!'], [$field, 0, '>']] : ['OR', [[$field, $mask, '&'], $mask], [$field, 0]];
        else if (is_int($_))
            return ['AND', [[$field, $mask, '&'], $mask, '!'], [$field, 1 << ($_ - 1), '&']];

        return false;
    }

    protected function cbDamageType($cr)
    {
        if (!$this->checkInput(FILTER_V_RANGE, [0, 6], $cr[1]))
            return false;

        return ['OR', ['dmgType1', $cr[1]], ['dmgType2', $cr[1]]];
    }

    protected function cbArmorBonus($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_FLOAT) || !$this->int2Op($cr[1]))
            return false;

        $this->formData['extraCols'][] = $cr[0];
        return ['AND', ['armordamagemodifier', $cr[2], $cr[1]], ['class', ITEM_CLASS_ARMOR]];
    }

    protected function cbCraftedByProf($cr)
    {
        if (!isset($this->enums[99][$cr[1]]))
            return false;

        $_ = $this->enums[99][$cr[1]];
        if (is_bool($_))
            return ['src.src1', null, $_ ? '!' : null];
        else if (is_int($_))
            return ['s.skillLine1', $_];

        return false;
    }

    protected function cbQuestRewardIn($cr)
    {
        if (in_array($cr[1], $this->enums[$cr[0]]))
            return ['AND', ['src.src4', null, '!'], ['src.moreZoneId', $cr[1]]];
        else if ($cr[1] == FILTER_ENUM_ANY)
            return ['src.src4', null, '!'];                 // well, this seems a bit redundant..

        return false;
    }

    protected function cbPurchasableWith($cr)
    {
        if (in_array($cr[1], $this->enums[$cr[0]]))
            $_ = (array)$cr[1];
        else if ($cr[1] == FILTER_ENUM_ANY)
            $_ = $this->enums[$cr[0]];
        else
            return false;

        $costs = DB::Aowow()->selectCol(
            'SELECT id FROM ?_itemextendedcost WHERE reqItemId1 IN (?a) OR reqItemId2 IN (?a) OR reqItemId3 IN (?a) OR reqItemId4 IN (?a) OR reqItemId5 IN (?a)',
            $_, $_, $_, $_, $_
        );
        if ($items = DB::World()->selectCol($this->extCostQuery, $costs, $costs))
            return ['id', $items];
    }

    protected function cbSoldByNPC($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT))
            return false;

        if ($iIds = DB::World()->selectCol('SELECT item FROM npc_vendor WHERE entry = ?d UNION SELECT item FROM game_event_npc_vendor v JOIN creature c ON c.guid = v.guid WHERE c.id = ?d', $cr[2], $cr[2]))
            return ['i.id', $iIds];
        else
            return [0];
    }

    protected function cbAvgBuyout($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        foreach (Profiler::getRealms() as $rId => $__)
        {
            // todo: do something sensible..
            // // todo (med): get the avgbuyout into the listview
            // if ($_ = DB::Characters()->select('SELECT ii.itemEntry AS ARRAY_KEY, AVG(ah.buyoutprice / ii.count) AS buyout FROM auctionhouse ah JOIN item_instance ii ON ah.itemguid = ii.guid GROUP BY ii.itemEntry HAVING buyout '.$cr[1].' ?f', $c[1]))
                // return ['i.id', array_keys($_)];
            // else
                // return [0];
            return [1];
        }

        return [0];
    }

    protected function cbAvgMoneyContent($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        $this->formData['extraCols'][] = $cr[0];
        return ['AND', ['flags', ITEM_FLAG_OPENABLE, '&'], ['((minMoneyLoot + maxMoneyLoot) / 2)', $cr[2], $cr[1]]];
    }

    protected function cbCooldown($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        $cr[2] *= 1000;                                     // field supplied in milliseconds

        $this->formData['extraCols'][] = $cr[0];
        $this->extraOpts['is']['s'][]  = ', IF(spellCooldown1 > 1, spellCooldown1, IF(spellCooldown2 > 1, spellCooldown2, IF(spellCooldown3 > 1, spellCooldown3, IF(spellCooldown4 > 1, spellCooldown4, IF(spellCooldown5 > 1, spellCooldown5,))))) AS cooldown';

        return [
            'OR',
            ['AND', ['spellTrigger1', 0], ['spellId1', 0, '!'], ['spellCooldown1', 0, '>'], ['spellCooldown1', $cr[2], $cr[1]]],
            ['AND', ['spellTrigger2', 0], ['spellId2', 0, '!'], ['spellCooldown2', 0, '>'], ['spellCooldown2', $cr[2], $cr[1]]],
            ['AND', ['spellTrigger3', 0], ['spellId3', 0, '!'], ['spellCooldown3', 0, '>'], ['spellCooldown3', $cr[2], $cr[1]]],
            ['AND', ['spellTrigger4', 0], ['spellId4', 0, '!'], ['spellCooldown4', 0, '>'], ['spellCooldown4', $cr[2], $cr[1]]],
            ['AND', ['spellTrigger5', 0], ['spellId5', 0, '!'], ['spellCooldown5', 0, '>'], ['spellCooldown5', $cr[2], $cr[1]]],
        ];
    }

    protected function cbQuestRelation($cr)
    {
        switch ($cr[1])
        {
            case 1:                                         // any
                return ['startQuest', 0, '>'];
            case 2:                                         // exclude horde only
                return ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 2]];
            case 3:                                         // exclude alliance only
                return ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 1]];
            case 4:                                         // both
                return ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 0]];
            case 5:                                         // none
                return ['startQuest', 0];
        }

        return false;
    }

    protected function cbFieldHasVal($cr, $field, $val)
    {
        if ($this->int2Bool($cr[1]))
            return [$field, $val, $cr[1] ? null : '!'];

        return false;
    }

    protected function cbObtainedBy($cr, $field)
    {
        if ($this->int2Bool($cr[1]))
            return ['src.src'.$this->otFields[$cr[0]], null, $cr[1] ? '!' : null];

        return false;
    }

    protected function cbPvpPurchasable($cr, $field)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        $costs = DB::Aowow()->selectCol('SELECT id FROM ?_itemextendedcost WHERE ?# > 0', $field);
        if ($items = DB::World()->selectCol($this->extCostQuery, $costs, $costs))
            return ['id', $items, $cr[1] ? null : '!'];

        return false;
    }

    protected function cbDisenchantsInto($cr)
    {
        if (!Util::checkNumeric($cr[1], NUM_REQ_INT))
            return false;

        if (!in_array($cr[1], $this->enums[$cr[0]]))
            return false;

        $refResults = [];
        $newRefs = DB::World()->selectCol('SELECT entry FROM ?# WHERE item = ?d AND reference = 0', LOOT_REFERENCE, $cr[1]);
        while ($newRefs)
        {
            $refResults += $newRefs;
            $newRefs     = DB::World()->selectCol('SELECT entry FROM ?# WHERE reference IN (?a)', LOOT_REFERENCE, $newRefs);
        }

        $lootIds = DB::World()->selectCol('SELECT entry FROM ?# WHERE {reference IN (?a) OR }(reference = 0 AND item = ?d)', LOOT_DISENCHANT, $refResults ?: DBSIMPLE_SKIP, $cr[1]);

        return $lootIds ? ['disenchantId', $lootIds] : [0];
    }

    protected function cbObjectiveOfQuest($cr)
    {
        $w = '';
        switch ($cr[1])
        {
            case 1:                                 // Yes
            case 5:                                 // No
                $w = 1;
                return;
            case 2:                                 // Alliance
                $w = 'reqRaceMask & '.RACE_MASK_ALLIANCE.' AND (reqRaceMask & '.RACE_MASK_HORDE.') = 0';
                break;
            case 3:                                 // Horde
                $w = 'reqRaceMask & '.RACE_MASK_HORDE.' AND (reqRaceMask & '.RACE_MASK_ALLIANCE.') = 0';
                break;
            case 4:                                 // Both
                $w = '(reqRaceMask & '.RACE_MASK_ALLIANCE.' AND reqRaceMask & '.RACE_MASK_HORDE.') OR reqRaceMask = 0';
                break;
            default:
                return false;
        }

        $itemIds = DB::Aowow()->selectCol(sprintf('
            SELECT reqItemId1 FROM ?_quests WHERE %1$s UNION SELECT reqItemId2 FROM ?_quests WHERE %1$s UNION
            SELECT reqItemId3 FROM ?_quests WHERE %1$s UNION SELECT reqItemId4 FROM ?_quests WHERE %1$s UNION
            SELECT reqItemId5 FROM ?_quests WHERE %1$s UNION SELECT reqItemId6 FROM ?_quests WHERE %1$s',
            $w
        ));

        if ($itemIds)
            return ['id', $itemIds, $cr[1] == 5 ? '!' : null];

        return [0];
    }

    protected function cbReagentForAbility($cr)
    {
        if (!isset($this->enums[99][$cr[1]]))
            return false;

        $_ =  $this->enums[99][$cr[1]];
        if ($_ === null)
            return false;

        $ids    = [];
        $spells = DB::Aowow()->select(          // todo (med): hmm, selecting all using SpellList would exhaust 128MB of memory :x .. see, that we only select the fields that are really needed
            'SELECT reagent1, reagent2, reagent3, reagent4, reagent5, reagent6, reagent7, reagent8,
                    reagentCount1, reagentCount2, reagentCount3, reagentCount4, reagentCount5, reagentCount6, reagentCount7, reagentCount8
            FROM    ?_spell
            WHERE   skillLine1 IN (?a)',
            is_bool($_) ? array_filter($this->enums[99], "is_numeric") : $_
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

    protected function cbSource($cr)
    {
        if (!isset($this->enums[$cr[0]][$cr[1]]))
            return false;

        $_ = $this->enums[$cr[0]][$cr[1]];
        if (is_int($_))                                     // specific
            return ['src.src'.$_, null, '!'];
        else if ($_)                                        // any
        {
            $foo = ['OR'];
            foreach ($this->enums[$cr[0]] as $bar)
                if (is_int($bar))
                    $foo[] = ['src.src'.$bar, null, '!'];

            return $foo;
        }
        else                                                // none
        {
            $foo = ['AND'];
            foreach ($this->enums[$cr[0]] as $bar)
                if (is_int($bar))
                    $foo[] = ['src.src'.$bar, null];

            return $foo;
        }
    }

    protected function cbTypeCheck(&$v)
    {
        if (!$this->parentCats)
            return false;

        if (!Util::checkNumeric($v, NUM_REQ_INT))
            return false;

        $c = $this->parentCats;

        if (isset($c[2]) && is_array(Lang::item('cat', $c[0], 1, $c[1])))
            $catList = Lang::item('cat', $c[0], 1, $c[1], 1, $c[2]);
        else if (isset($c[1]) && is_array(Lang::item('cat', $c[0])))
            $catList = Lang::item('cat', $c[0], 1, $c[1]);
        else
            $catList = Lang::item('cat', $c[0]);

        // consumables - always
        if ($c[0] == 0)
            return in_array($v, array_keys(Lang::item('cat', 0, 1)));
        // weapons - only if parent
        else if ($c[0] == 2 && !isset($c[1]))
            return in_array($v, array_keys(Lang::spell('weaponSubClass')));
        // armor - only if parent
        else if ($c[0] == 4 && !isset($c[1]))
            return in_array($v, array_keys(Lang::item('cat', 4, 1)));
        // uh ... other stuff...
        else if (in_array($c[0], [1, 3, 7, 9, 15]) && !isset($c[1]))
            return in_array($v, array_keys($catList[1]));

        return false;
    }

    protected function cbSlotCheck(&$v)
    {
        if (!Util::checkNumeric($v, NUM_REQ_INT))
            return false;

        // todo (low): limit to concrete slots
        $sl = array_keys(Lang::item('inventoryType'));
        $c  = $this->parentCats;

        // no selection
        if (!isset($c[0]))
            return in_array($v, $sl);

        // consumables - any; perm / temp item enhancements
        else if ($c[0] == 0 && (!isset($c[1]) || in_array($c[1], [-3, 6])))
            return in_array($v, $sl);

        // weapons - always
        else if ($c[0] == 2)
            return in_array($v, $sl);

        // armor - any; any armor
        else if ($c[0] == 4 && (!isset($c[1]) || in_array($c[1], [1, 2, 3, 4])))
            return in_array($v, $sl);

        return false;
    }

    protected function cbWeightKeyCheck(&$v)
    {
        if (preg_match('/\W/i', $v))
            return false;

        return isset(Util::$itemFilter[$v]);
    }
}

?>
