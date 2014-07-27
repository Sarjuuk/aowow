<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemList extends BaseType
{
    use ListviewHelper;

    public static $type       = TYPE_ITEM;
    public static $brickFile  = 'item';

    public        $tooltip    = [];
    public        $json       = [];
    public        $itemMods   = [];

    public        $rndEnchIds = [];
    public        $subItems   = [];

    private       $ssd        = [];
    private       $vendors    = [];
    private       $jsGlobals  = [];                         // getExtendedCost creates some and has no access to template

    protected     $queryBase  = 'SELECT i.*, `is`.*, i.id AS id, i.id AS ARRAY_KEY FROM ?_items i';
    protected     $queryOpts  = array(
                      'is'  => ['j' => ['?_item_stats AS `is` ON `is`.`id` = `i`.`id`', true]],
                      's'   => ['j' => ['?_spell AS `s` ON s.effect1CreateItemId = i.id', true], 'g' => 'i.id'],
                      'i'   => [['is'], 'o' => 'i.quality DESC, i.itemLevel DESC']
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
            $_ = &$_curTpl['requiredClass'];
            if ($_ < 0 || ($_ & CLASS_MASK_ALL) == CLASS_MASK_ALL)
                $_ = 0;

            $_ = &$_curTpl['requiredRace'];
            if ($_ < 0 || ($_ & RACE_MASK_ALL) == RACE_MASK_ALL)
                $_ = 0;
        }
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->selectRow('
            SELECT
                name_loc0, name_loc2, name_loc3, name_loc6, name_loc8
            FROM
                ?_items
            WHERE
                id = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    // todo (med): information will get lost if one vendor sells one item multiple times with different costs (e.g. for item 54637)
    //             wowhead seems to have had the same issues
    public function getExtendedCost($filter = [], &$reqRating = 0)
    {
        if (empty($this->vendors))
        {
            $ids = array_keys($this->templates);
            $itemz = DB::Aowow()->select('
                SELECT   nv.item AS ARRAY_KEY1, nv.entry AS ARRAY_KEY2,               0 AS eventId,   nv.maxcount, iec.* FROM            npc_vendor   nv                                       LEFT JOIN ?_itemextendedcost iec ON   nv.extendedCost = iec.id                                                   WHERE {nv.entry IN (?a) AND} nv.item IN (?a)
                UNION
                SELECT genv.item AS ARRAY_KEY1,     c.id AS ARRAY_KEY2, genv.eventEntry AS eventId, genv.maxcount, iec.* FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid LEFT JOIN ?_itemextendedcost iec ON genv.extendedCost = iec.id {JOIN creature c ON c.guid = genv.guid AND 1= ?d} WHERE {c.id IN (?a) AND}   genv.item IN (?a)',
                empty($filter[TYPE_NPC]) || !is_array($filter[TYPE_NPC]) ? DBSIMPLE_SKIP : $filter[TYPE_NPC],
                $ids,
                empty($filter[TYPE_NPC]) || !is_array($filter[TYPE_NPC]) ? DBSIMPLE_SKIP : 1,
                empty($filter[TYPE_NPC]) || !is_array($filter[TYPE_NPC]) ? DBSIMPLE_SKIP : $filter[TYPE_NPC],
                $ids
            );

            $cItems = [];
            foreach ($itemz as $k => $vendors)
            {
                foreach ($vendors as $l => $costs)
                {
                    $data = array(
                        'stock'  => $costs['maxcount'] ? $costs['maxcount'] : -1,
                        'event'  => $costs['eventId'],
                        'reqRtg' => $costs['reqPersonalRating']
                    );

                    if ($_ = $this->getField('buyPrice'))   // somewhat nonsense.. is identical for all vendors (obviously)
                        $data[0] = $_;

                    // hardcode arena(103) & honor(104)
                    if ($_ = @$costs['reqArenaPoints'])
                    {
                        $data[-103] = $_;
                        $this->jsGlobals[TYPE_CURRENCY][103] = 103;
                    }

                    if ($_ = @$costs['reqHonorPoints'])
                    {
                        $data[-104] = $_;
                        $this->jsGlobals[TYPE_CURRENCY][104] = 104;
                    }

                    for ($i = 1; $i < 6; $i++)
                    {
                        if (($_ = @$costs['reqItemId'.$i]) && $costs['itemCount'.$i] > 0)
                        {
                            $data[$_] = $costs['itemCount'.$i];
                            $cItems[] = $_;
                        }
                    }

                    $vendors[$l] = $data;
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

                foreach ($itemz as $id => $vendors)
                {
                    foreach ($vendors as $l => $costs)
                    {
                        foreach ($costs as $k => $v)
                        {
                            if (in_array($k, $cItems))
                            {
                                $found = false;
                                foreach ($moneyItems->iterate() as $__)
                                {
                                    if ($moneyItems->getField('itemId') == $k)
                                    {
                                        unset($costs[$k]);
                                        $costs[-$moneyItems->id] = $v;
                                        $found = true;
                                        break;
                                    }
                                }

                                if (!$found)
                                    $this->jsGlobals[TYPE_ITEM][$k] = $k;
                            }
                        }
                        $vendors[$l] = $costs;
                    }
                    $itemz[$id] = $vendors;
                }
            }

            $this->vendors = $itemz;
        }

        $result = $this->vendors;

        // apply filter if given
        $tok = @$filter[TYPE_ITEM];
        $cur = @$filter[TYPE_CURRENCY];

        foreach ($result as $itemId => &$data)
        {
            $reqRating = 0;
            foreach ($data as $npcId => $costs)
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
                // note: how to distinguish between brackets .. or team/pers-rating?
                if (isset($data[$npcId]) && ($reqRating < $costs['reqRtg']))
                    $reqRating = $costs['reqRtg'];
            }

            if ($reqRating)
                $data['reqRating'] = $reqRating;

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

        // random item is random
        if ($addInfoMask & ITEMINFO_SUBITEMS)
            $this->initSubItems();

        if ($addInfoMask & ITEMINFO_JSON)
            $this->extendJsonStats();

        $data = [];
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
                // todo (med): dont use first result; search for the right one
                if ($cost = @reset($this->getExtendedCost($miscData)[$this->id]))
                {
                    $currency = [];
                    $tokens   = [];
                    foreach ($cost as $k => $qty)
                    {
                        if (is_string($k))
                            continue;

                        if ($k > 0)
                            $tokens[] = [$k, $qty];
                        else if ($k < 0)
                            $currency[] = [-$k, $qty];
                    }

                    $data[$this->id]['stock'] = $cost['stock']; // display as column in lv
                    $data[$this->id]['avail'] = $cost['stock']; // display as number on icon
                    $data[$this->id]['cost']  = [$this->getField('buyPrice')];

                    if ($e = $cost['event'])
                    {
                        $this->jsGlobals[TYPE_WORLDEVENT][$e] = $e;
                        $data[$this->id]['condition'] = array(
                            'type'   => TYPE_WORLDEVENT,
                            'typeId' => -$e,
                            'status' => 1
                        );
                    }

                    if ($currency || $tokens)               // fill idx:3 if required
                        $data[$this->id]['cost'][] = $currency;

                    if ($tokens)
                        $data[$this->id]['cost'][] = $tokens;

                    if ($_ = @$this->getExtendedCost($miscData)[$this->id]['reqRating'])
                        $data[$this->id]['reqarenartng'] = $_;
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
        }

        /* even more complicated crap
            "source":[5],"sourcemore":[{"n":"Commander Oxheart","t":1,"ti":64606,"z":5842}],
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
    public function renderTooltip($interactive = false, $subOf = 0, $enhance = [])
    {
        if ($this->error)
            return;

        if (!empty($this->tooltip[$this->id]))
            return $this->tooltip[$this->id];

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
            if ($rndEnch = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomenchant WHERE Id = ?d', $enhance['r']))
            {
                $_name      .= ' '.Util::localizedString($rndEnch, 'name');
                $randEnchant = '';

                for ($i = 1; $i < 6; $i++)
                {
                    if ($rndEnch['enchantId'.$i] <= 0)
                        continue;

                    $enchant = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?d', $rndEnch['enchantId'.$i]);
                    if ($rndEnch['allocationPct'.$i] > 0)
                    {
                        $amount = intVal($rndEnch['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                        $randEnchant .= '<span>'.str_replace('$i', $amount, Util::localizedString($enchant, 'text')).'</span><br />';
                    }
                    else
                        $randEnchant .= '<span>'.Util::localizedString($enchant, 'text').'</span><br />';
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
            $x .= '<br /><span class="q2">'.Lang::$item['heroic'].'</span>';

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
            $x .= '<br />'.Lang::$item['conjured'];

        // bonding
        if ($_flags & ITEM_FLAG_ACCOUNTBOUND)
            $x .= '<br /><!--bo-->'.Lang::$item['bonding'][0];
        else if ($this->curTpl['bonding'])
            $x .= '<br /><!--bo-->'.Lang::$item['bonding'][$this->curTpl['bonding']];

        // unique || unique-equipped || unique-limited
        if ($this->curTpl['maxCount'] > 0)
        {
            $x .= '<br />'.Lang::$item['unique'];

            if ($this->curTpl['maxCount'] > 1)
                $x .= ' ('.$this->curTpl['maxCount'].')';
        }
        else if ($_flags & ITEM_FLAG_UNIQUEEQUIPPED)
            $x .= '<br />'.Lang::$item['uniqueEquipped'];
        else if ($this->curTpl['itemLimitCategory'])
        {
            $limit = DB::Aowow()->selectRow("SELECT * FROM ?_itemlimitcategory WHERE id = ?", $this->curTpl['itemLimitCategory']);
            $x .= '<br />'.($limit['isGem'] ? Lang::$item['uniqueEquipped'] : Lang::$item['unique']).Lang::$main['colon'].Util::localizedString($limit, 'name').' ('.$limit['count'].')';
        }

        // max duration
        if ($dur = $this->curTpl['duration'])
            $x .= "<br />".Lang::$game['duration'].Lang::$main['colon'].Util::formatTime(abs($dur) * 1000).($this->curTpl['flagsCustom'] & 0x1 ? ' ('.Lang::$item['realTime'].')' : null);

        // required holiday
        if ($hId = $this->curTpl['holidayId'])
        {
            $hDay = DB::Aowow()->selectRow("SELECT * FROM ?_holidays WHERE id = ?", $hId);
            $x .= '<br />'.sprintf(Lang::$game['requires'], '<a href="'.$hId.'" class="q1">'.Util::localizedString($hDay, 'name').'</a>');
        }

        // item begins a quest
        if ($this->curTpl['startQuest'])
            $x .= '<br /><a class="q1" href="?quest='.$this->curTpl['startQuest'].'">'.Lang::$item['startQuest'].'</a>';

        // containerType (slotCount)
        if ($this->curTpl['slots'] > 0)
        {
            $fam = log($this->curTpl['bagFamily'], 2) + 1;
            // word order differs <_<
            if (in_array(User::$localeId, [LOCALE_FR, LOCALE_ES, LOCALE_RU]))
                $x .= '<br />'.sprintf(Lang::$item['bagSlotString'], Lang::$item['bagFamily'][$fam], $this->curTpl['slots']);
            else
                $x .= '<br />'.sprintf(Lang::$item['bagSlotString'], $this->curTpl['slots'], Lang::$item['bagFamily'][$fam]);
        }

        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]))
        {
            $x .= '<table width="100%"><tr>';

            // Class
            $x .= '<td>'.Lang::$item['inventoryType'][$_slot].'</td>';

            // Subclass
            if ($_class == ITEM_CLASS_ARMOR && $_subClass > 0)
                $x .= '<th><!--asc'.$_subClass.'-->'.Lang::$item['armorSubClass'][$_subClass].'</th>';
            else if ($_class == ITEM_CLASS_WEAPON)
                $x .= '<th>'.Lang::$item['weaponSubClass'][$_subClass].'</th>';
            else if ($_class == ITEM_CLASS_AMMUNITION)
                $x .= '<th>'.Lang::$item['projectileSubClass'][$_subClass].'</th>';

            $x .= '</tr></table>';
        }
        else if ($_slot && $_class != ITEM_CLASS_CONTAINER) // yes, slot can occur on random items and is then also displayed <_< .. excluding Bags >_>
            $x .= '<br />'.Lang::$item['inventoryType'][$_slot].'<br />';
        else
            $x .= '<br />';

        // Weapon/Ammunition Stats                          (not limited to weapons (see item:1700))
        $speed   = $this->curTpl['delay'] / 1000;
        $dmgmin1 = $this->curTpl['dmgMin1'] + $this->curTpl['dmgMin2'];
        $dmgmax1 = $this->curTpl['dmgMax1'] + $this->curTpl['dmgMax2'];
        $dps     = $speed ? ($dmgmin1 + $dmgmax1) / (2 * $speed) : 0;

        if ($_class == ITEM_CLASS_AMMUNITION && $dmgmin1 && $dmgmax1)
            $x .= Lang::$item['addsDps'].' '.number_format(($dmgmin1 + $dmgmax1) / 2, 1).' '.Lang::$item['dps2'].'<br />';
        else if ($dps)
        {
            if ($_class == ITEM_CLASS_WEAPON)
            {
                $x .= '<table width="100%"><tr>';
                $x .= '<td><!--dmg-->'.sprintf($this->curTpl['dmgType1'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->curTpl['dmgMin1'].' - '.$this->curTpl['dmgMax1'], Lang::$game['sc'][$this->curTpl['dmgType1']]).'</td>';
                $x .= '<th>'.Lang::$item['speed'].' <!--spd-->'.number_format($speed, 2).'</th>';
                $x .= '</tr></table>';
            }
            else
                $x .= '<!--dmg-->'.sprintf($this->curTpl['dmgType1'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->curTpl['dmgMin1'].' - '.$this->curTpl['dmgMax1'], Lang::$game['sc'][$this->curTpl['dmgType1']]).'<br />';

            // secondary damage is set
            if ($this->curTpl['dmgMin2'])
                $x .= '+'.sprintf($this->curTpl['dmgType2'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->curTpl['dmgMin2'].' - '.$this->curTpl['dmgMax2'], Lang::$game['sc'][$this->curTpl['dmgType2']]).'<br />';

            if ($_class == ITEM_CLASS_WEAPON)
                $x .= '<!--dps-->('.number_format($dps, 1).' '.Lang::$item['dps'].')<br />';

            // display FeralAttackPower if set
            if ($fap = $this->getFeralAP())
                $x .= '<span class="c11"><!--fap-->('.$fap.' '.Lang::$item['fap'].')</span><br />';
        }

        // Armor
        if ($_class == ITEM_CLASS_ARMOR && $this->curTpl['armorDamageModifier'] > 0)
        {
            $spanI = 'class="q2"';
            if ($interactive)
                $spanI = 'class="q2 tip" onmouseover="$WH.Tooltip.showAtCursor(event, $WH.sprintf(LANG.tooltip_armorbonus, '.$this->curTpl['armorDamageModifier'].'), 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"';

            $x .= '<span '.$spanI.'><!--addamr'.$this->curTpl['armorDamageModifier'].'--><span>'.sprintf(Lang::$item['armor'], intVal($this->curTpl['armor'] + $this->curTpl['armorDamageModifier'])).'</span></span><br />';
        }
        else if (($this->curTpl['armor'] + $this->curTpl['armorDamageModifier']) > 0)
            $x .= '<span><!--amr-->'.sprintf(Lang::$item['armor'], intVal($this->curTpl['armor'] + $this->curTpl['armorDamageModifier'])).'</span><br />';

        // Block
        if ($this->curTpl['block'])
            $x .= '<span>'.sprintf(Lang::$item['block'], $this->curTpl['block']).'</span><br />';

        // Item is a gem (don't mix with sockets)
        if ($geId = $this->curTpl['gemEnchantmentId'])
        {
            $gemText = DB::Aowow()->selectRow('SELECT * FROM ?_itemEnchantment WHERE id = ?d', $geId);
            $x .= Util::localizedString($gemText, 'text').'<br />';
        }

        // Random Enchantment - if random enchantment is set, prepend stats from it
        if ($this->curTpl['randomEnchant'] && !isset($enhance['r']))
            $x .= '<span class="q2">'.Lang::$item['randEnchant'].'</span><br />';
        else if (isset($enhance['r']))
            $x .= $randEnchant;

        // itemMods (display stats and save ratings for later use)
        for ($j = 1; $j <= 10; $j++)
        {
            $type = $this->curTpl['statType'.$j];
            $qty  = $this->curTpl['statValue'.$j];

            if (!$qty || $type <= 0)
                continue;

            // base stat
            if ($type >= ITEM_MOD_AGILITY && $type <= ITEM_MOD_STAMINA)
                $x .= '<span><!--stat'.$type.'-->'.($qty > 0 ? '+' : '-').abs($qty).' '.Lang::$item['statType'][$type].'</span><br />';
            else                                            // rating with % for reqLevel
                $green[] = $this->parseRating($type, $qty, $interactive, $causesScaling);
        }

        // magic resistances
        foreach (Util::$resistanceFields as $j => $rowName)
            if ($rowName && $this->curTpl[$rowName] != 0)
                $x .= '+'.$this->curTpl[$rowName].' '.Lang::$game['resistances'][$j].'<br />';

        // Enchantment
        if (isset($enhance['e']))
        {
            if ($enchText = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?', $enhance['e']))
                $x .= '<span class="q2"><!--e-->'.Util::localizedString($enchText, 'text').'</span><br />';
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
            $gems = DB::Aowow()->select('SELECT i.id AS ARRAY_KEY, i.iconString, ae.*, i.gemColorMask AS colorMask FROM ?_items i JOIN ?_itemenchantment ae ON ae.id = i.gemEnchantmentId WHERE i.id IN (?a)', $enhance['g']);
            foreach ($enhance['g'] as $k => $v)
                if (!in_array($v, array_keys($gems)))
                    unset($enhance['g'][$k]);
        }
        else
            $enhance['g'] = [];

        // zero fill empty sockets
        $sockCount = $this->curTpl['socketColor1'] + $this->curTpl['socketColor2'] + $this->curTpl['socketColor3'] + (isset($enhance['s']) ? 1 : 0);
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
            $text      = $pop ? Util::localizedString($gems[$pop], 'text') : Lang::$item['socket'][$colorId];

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs='.($colorId + 1).';crv=0" class="socket-'.Util::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-'.Util::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }

        // fill extra socket
        if (isset($enhance['s']))
        {
            $pop  = array_pop($enhance['g']);
            $col  = $pop ? 1 : 0;
            $icon = $pop ? sprintf(Util::$bgImagePath['tiny'], STATIC_URL, strtolower($gems[$pop]['iconString'])) : null;
            $text = $pop ? Util::localizedString($gems[$pop], 'text') : Lang::$item['socket'][-1];

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs=5;crv=0" class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }
        else                                                // prismatic socket placeholder
            $x .= '<!--ps-->';

        if ($this->curTpl['socketBonus'])
        {
            $sbonus = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?d', $this->curTpl['socketBonus']);
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::$item['socketBonus'].Lang::$main['colon'].Util::localizedString($sbonus, 'text').'</span><br />';
        }

        // durability
        if ($dur = $this->curTpl['durability'])
            $x .= Lang::$item['durability'].' '.$dur.' / '.$dur.'<br />';

        // required classes
        if ($classes = Lang::getClassString($this->curTpl['requiredClass'], $jsg, $__))
        {
            foreach ($jsg as $js)
                if (empty($this->jsGlobals[TYPE_CLASS][$js]))
                    $this->jsGlobals[TYPE_CLASS][$js] = $js;

            $x .= Lang::$game['classes'].Lang::$main['colon'].$classes.'<br />';
        }

        // required races
        if ($races = Lang::getRaceString($this->curTpl['requiredRace'], $__, $jsg, $__))
        {
            foreach ($jsg as $js)
                if (empty($this->jsGlobals[TYPE_RACE][$js]))
                    $this->jsGlobals[TYPE_RACE][$js] = $js;

            if ($races != Lang::$game['ra'][0])             // not "both", but display combinations like: troll, dwarf
                $x .= Lang::$game['races'].Lang::$main['colon'].$races.'<br />';
        }

        // required honorRank (not used anymore)
        if ($rhr = $this->curTpl['requiredHonorRank'])
            $x .= sprintf(Lang::$game['requires'], Lang::$game['pvpRank'][$rhr]).'<br />';

        // required CityRank..?
        // what the f..

        // required level
        if (($_flags & ITEM_FLAG_ACCOUNTBOUND) && $_quality == ITEM_QUALITY_HEIRLOOM)
            $x .= sprintf(Lang::$game['reqLevelHlm'], ' 1'.Lang::$game['valueDelim'].MAX_LEVEL.' ('.($interactive ? sprintf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL).')').'<br />';
        else if ($_reqLvl > 1)
            $x .= sprintf(Lang::$game['reqLevel'], $_reqLvl).'<br />';

        // required arena team rating / personal rating / todo (low): sort out what kind of rating
        if (@$this->getExtendedCost([], $reqRating)[$this->id] && $reqRating)
            $x .= sprintf(Lang::$item['reqRating'], $reqRating).'<br />';

        // item level
        if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON]))
            $x .= Lang::$item['itemLevel'].' '.$this->curTpl['itemLevel'].'<br />';

        // required skill
        if ($reqSkill = $this->curTpl['requiredSkill'])
        {
            $_ = '<a class="q1" href="?skill='.$reqSkill.'">'.SkillList::getName($reqSkill).'</a>';
            if ($this->curTpl['requiredSkillRank'] > 0)
                $_ .= ' ('.$this->curTpl['requiredSkillRank'].')';

            $x .= sprintf(Lang::$game['requires'], $_).'<br />';
        }

        // required spell
        if ($reqSpell = $this->curTpl['requiredSpell'])
            $x .= Lang::$game['requires2'].' <a class="q1" href="?spell='.$reqSpell.'">'.SpellList::getName($reqSpell).'</a><br />';

        // required reputation w/ faction
        if ($reqFac = $this->curTpl['requiredFaction'])
            $x .= sprintf(Lang::$game['requires'], '<a class="q1" href=?faction="'.$reqFac.'">'.FactionList::getName($reqFac).'</a> - '.Lang::$game['rep'][$this->curTpl['requiredFactionRank']]).'<br />';

        // locked or openable
        if ($locks = Lang::getLocks($this->curTpl['lockId'], true))
            $x .= '<span class="q0">'.Lang::$item['locked'].'<br />'.implode('<br />', $locks).'</span><br />';
        else if ($this->curTpl['flags'] & ITEM_FLAG_OPENABLE)
            $x .= '<span class="q2">'.Lang::$item['openClick'].'</span><br />';

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

                    $cd = $cd < 5000 ? null : ' ('.sprintf(Lang::$game['cooldown'], Util::formatTime($cd)).')';

                    $itemSpellsAndTrigger[$this->curTpl['spellId'.$j]] = [$this->curTpl['spellTrigger'.$j], $cd];
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
                            $parsed = preg_replace_callback('/^(.*)(&nbsp;<small>.*<\/small>)(.*)$/i', function($m) use($link) {
                                    $m[1] = sprintf($link, $m[1]);
                                    $m[3] = sprintf($link, $m[3]);
                                    return $m[1].$m[2].$m[3];
                                }, $parsed, -1, $nMatches
                            );

                            if (!$nMatches)
                                $parsed = sprintF($link, $parsed);
                        }

                        $green[] = Lang::$item['trigger'][$itemSpellsAndTrigger[$itemSpells->id][0]].$parsed.$itemSpellsAndTrigger[$itemSpells->id][1];
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
        $condition = ['OR', ['item1', $this->id], ['item2', $this->id], ['item3', $this->id], ['item4', $this->id], ['item5', $this->id], ['item6', $this->id], ['item7', $this->id], ['item8', $this->id], ['item9', $this->id], ['item10', $this->id]];
        $itemset = new ItemsetList($condition);

        if (!$itemset->error)
        {
            $pieces = DB::Aowow()->select('
                SELECT b.id AS ARRAY_KEY, b.name_loc0, b.name_loc2, b.name_loc3, b.name_loc6, b.name_loc8, GROUP_CONCAT(a.id SEPARATOR \':\') AS equiv
                FROM   aowow_items a, aowow_items b
                WHERE  a.slotBak = b.slotBak AND a.itemset = b.itemset AND b.id IN (?a)
                GROUP BY b.id;',
                array_keys($itemset->pieceToSet)
            );

            foreach ($pieces as $k => &$p)
                $p = '<span><!--si'.$p['equiv'].'--><a href="?item='.$k.'">'.Util::localizedString($p, 'name').'</a></span>';

            $xSet = '<br /><span class="q"><a href="?itemset='.$itemset->id.'" class="q">'.$itemset->getField('name', true).'</a> (0/'.count($pieces).')</span>';

            if ($skId = $itemset->getField('skillId'))      // bonus requires skill to activate
            {
                $xSet .= '<br />'.sprintf(Lang::$game['requires'], '<a href="?skills='.$skId.'" class="q1">'.SkillList::getName($skId).'</a>');

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
                $xSet .= '<span>('.$setSpells[$i]['bonus'].') '.Lang::$item['set'].': <a href="?spell='.$setSpells[$i]['entry'].'">'.$setSpells[$i]['tooltip'].'</a></span>';
                if ($i < count($setSpells) - 1)
                    $xSet .= '<br />';
            }
            $xSet .= '</span>';
        }

        // recipes, vanity pets, mounts
        if ($this->canTeachSpell())
        {
            $craftSpell = new SpellList(array(['s.id', intVal($this->curTpl['spellId2'])]));
            if (!$craftSpell->error)
            {
                $xCraft = '';
                if ($desc = $this->getField('description', true))
                    $x .= '<span class="q2">'.Lang::$item['trigger'][0].' <a href="?spell='.$this->curTpl['spellId2'].'">'.$desc.'</a></span><br />';

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

                            $xCraft .= '<div class="q1 whtt-reagents"><br />'.Lang::$game['requires2'].' '.implode(', ', $reqReag).'</div>';
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
            $xMisc[] = '<span class="q2">'.Lang::$item['readClick'].'</span>';

        // charges (i guess checking first spell is enough (single charges not shown))
        if ($this->curTpl['spellCharges1'] > 1 || $this->curTpl['spellCharges1'] < -1)
            $xMisc[] = '<span class="q1">'.abs($this->curTpl['spellCharges1']).' '.Lang::$item['charges'].'</span>';

        // list required reagents
        if (isset($xCraft))
            $xMisc[] = $xCraft;

        if ($xMisc)
            $x .= implode('<br />', $xMisc);

        if ($sp = $this->curTpl['sellPrice'])
            $x .= '<div class="q1 whtt-sellprice">'.Lang::$item['sellPrice'].Lang::$main['colon'].Util::formatMoney($sp).'</div>';

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

        $this->tooltip[$this->id] = $x;

        return $this->tooltip[$this->id];
    }

    // from Trinity
    public function generateEnchSuffixFactor()
    {
        $rpp = DB::Aowow()->selectRow('SELECT * FROM ?_itemRandomPropPoints WHERE Id = ?', $this->curTpl['itemLevel']);
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

            foreach (Util::$itemMods as $mod)
                if (!empty($this->curTpl[$mod]))
                    @$this->itemMods[$this->id][$mod] += $this->curTpl[$mod];

            // fetch and add socketbonusstats
            if (@$this->json[$this->id]['socketbonus'] > 0)
                $enchantments[$this->json[$this->id]['socketbonus']][] = $this->id;


            // Item is a gem (don't mix with sockets)
            if ($geId = $this->curTpl['gemEnchantmentId'])
                $enchantments[$geId][] = -$this->id;
        }

        if ($enchantments)
        {
            $parsed = Util::parseItemEnchantment(array_keys($enchantments));

            // and merge enchantments back
            foreach ($parsed as $eId => $stats)
            {
                foreach ($enchantments[$eId] as $item)
                {
                    if ($item > 0)                          // apply socketBonus
                        $this->json[$item]['socketbonusstat'] = $stats;
                    else /* if ($item < 0) */
                        foreach ($stats as $mod => $qty)    // apply gemEnchantment
                            @$this->json[-$item][$mod] += $qty;
                }
            }
        }

        foreach ($this->json as $item => $json)
            foreach ($json as $k => $v)
                if (!$v && !in_array($k, ['classs', 'subclass', 'quality', 'side']))
                    unset($this->json[$item][$k]);

        Util::checkNumeric($this->json);
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
                foreach ($stat as $mId => $qty)
                    @$onUseStats[$mId] += $qty;
        }

        return $onUseStats;
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

    private function parseRating($type, $value, $interactive = false, &$scaling = false)
    {
        // clamp level range
        $ssdLvl = isset($this->ssd[$this->id]) ? $this->ssd[$this->id]['maxLevel'] : 1;
        $reqLvl = $this->curTpl['requiredLevel'] > 1 ? $this->curTpl['requiredLevel'] : MAX_LEVEL;
        $level  = min(max($reqLvl, $ssdLvl), MAX_LEVEL);

        if (!Lang::$item['statType'][$type])                // unknown rating
            return sprintf(Lang::$item['statType'][count(Lang::$item['statType']) - 1], $type, $value);
        else if (in_array($type, Util::$lvlIndepRating))    // level independant Bonus
            return Lang::$item['trigger'][1].str_replace('%d', '<!--rtg'.$type.'-->'.$value, Lang::$item['statType'][$type]);
        else                                                // rating-Bonuses
        {
            $scaling = true;

            if ($interactive)
                $js = '&nbsp;<small>('.sprintf(Util::$changeLevelString, Util::setRatingLevel($level, $type, $value)).')</small>';
            else
                $js = "&nbsp;<small>(".Util::setRatingLevel($level, $type, $value).")</small>";

            return Lang::$item['trigger'][1].str_replace('%d', '<!--rtg'.$type.'-->'.$value.$js, Lang::$item['statType'][$type]);
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

        return $field ? DB::Aowow()->selectCell("SELECT ?# FROM ?_scalingstatvalues WHERE charLevel = ?", $field, $this->ssd[$this->id]['maxLevel']) : 0;
    }

    private function initScalingStats()
    {
        $this->ssd[$this->id] = DB::Aowow()->selectRow("SELECT * FROM ?_scalingstatdistribution WHERE id = ?", $this->curTpl['scalingStatDistribution']);

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

        // if set dpsMod in ScalingStatValue use it for min (70% from average), max (130% from average) damage
        if ($extraDPS = $this->getSSDMod('dps'))            // dmg_x2 not used for heirlooms
        {
            $average = $extraDPS * $this->curTpl['delay'] / 1000;
            $this->templates[$this->id]['dmgMin1'] = number_format(0.7 * $average);
            $this->templates[$this->id]['dmgMax1'] = number_format(1.3 * $average);
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

        $ire = DB::Aowow()->select('
            SELECT  i.id AS ARRAY_KEY_1, ire.id AS ARRAY_KEY_2, iet.chance, ire.*
            FROM    aowow_items i
            JOIN    item_enchantment_template iet ON iet.entry = ABS(i.randomEnchant)
            JOIN    aowow_itemRandomEnchant ire ON IF(i.randomEnchant > 0, ire.id = iet.ench, ire.id = -iet.ench)
            WHERE   i.id IN (?a)',
            array_keys($this->templates)
        );

        foreach ($ire as $mstItem => $subItem)
        {
            foreach ($subItem as $subId => $data)
            {
                $jsonEquip = [];
                $jsonText  = [];
                $enchIds   = [];

                for ($i = 1; $i < 6; $i++)
                {
                    $enchId = $data['enchantId'.$i];
                    if ($enchId <= 0)
                        continue;

                    if (isset($this->rndEnchIds[$enchId]))
                        continue;

                    $enchIds[] = $enchId;
                }

                foreach (Util::parseItemEnchantment($enchIds, false, $misc) as $eId => $stats)
                {
                    $this->rndEnchIds[$eId] = array(
                        'text'  => $misc[$eId]['name'],
                        'stats' => $stats
                    );
                }

                for ($i = 1; $i < 6; $i++)
                {
                    $enchId = $data['enchantId'.$i];
                    if ($enchId <= 0)
                        continue;

                    if ($data['allocationPct'.$i] > 0)      // RandomSuffix: scaling Enchantment; enchId < 0
                    {
                        $qty   = intVal($data['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                        $stats = array_fill_keys(array_keys($this->rndEnchIds[$enchId]['stats']), $qty);

                        $jsonText[] = str_replace('$i', $qty, $this->rndEnchIds[$enchId]['text']);
                        Util::arraySumByKey($jsonEquip, $stats);
                    }
                    else                                    // RandomProperty: static Enchantment; enchId > 0
                    {
                        $jsonText[] = $this->rndEnchIds[$enchId]['text'];
                        Util::arraySumByKey($jsonEquip, $this->rndEnchIds[$enchId]['stats']);
                    }
                }

                $this->subItems[$mstItem][$subId] = array(
                    'name'          => Util::localizedString($data, 'name'),
                    'enchantment'   => implode(', ', $jsonText),
                    'jsonequip'     => $jsonEquip,
                    'chance'        => $data['chance']      // hmm, only needed for item detail page...
                );
            }

            $this->json[$mstItem]['subitems'] = $this->subItems[$mstItem];
        }
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
            'side'        => $this->curTpl['flagsExtra'] & 0x3 ? 3 - ($this->curTpl['flagsExtra'] & 0x3) : Util::sideByRaceMask($this->curTpl['requiredRace']),
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
            'armorbonus'  => $this->curTpl['armorDamageModifier'],
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

        if ($this->curTpl['armorDamageModifier'] > 0)
            $json['armor'] += $this->curTpl['armorDamageModifier'];

        // clear zero-values afterwards
        foreach ($json as $k => $v)
            if (!$v && !in_array($k, ['classs', 'subclass', 'quality', 'side']))
                unset($json[$k]);

        Util::checkNumeric($json);

        $this->json[$json['id']] = $json;
    }

    public function addRewardsToJScript(&$ref) { }
}


class ItemListFilter extends Filter
{
    private   $ubFilter      = [];                          // usable-by - limit weapon/armor selection per CharClass - itemClass => available itemsubclasses
    private   $extCostQuery  = 'SELECT item FROM npc_vendor nv              JOIN ?_itemExtendedCost iec ON iec.id =   nv.extendedCost WHERE %s UNION
                                SELECT item FROM game_event_npc_vendor genv JOIN ?_itemExtendedCost iec ON iec.id = genv.extendedCost WHERE %1$s';

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
            11 => [ 9788,  9787, 17041, 17040, 17039, 20219, 20222, 10656, 10658, 10660, 26798, 26801, 26797],      // i know, i know .. lazy as fuck
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
        )
    );
    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
          9 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_CONJURED           ], // conjureditem
         11 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_OPENABLE           ], // openable
         83 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_UNIQUEEQUIPPED     ], // uniqueequipped
         89 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_PROSPECTABLE       ], // prospectable
         98 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_PARTYLOOT          ], // partyloot
        133 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_ACCOUNTBOUND       ], // accountbound
        146 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_HEROIC             ], // heroic
        154 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_REFUNDABLE         ], // refundable
        155 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_ARENA       ], // usableinarenas
        156 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_USABLE_SHAPED      ], // usablewhenshapeshifted
        157 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_SMARTLOOT          ], // smartloot
        159 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_MILLABLE           ], // millable
        162 => [FILTER_CR_FLAG,      'flags',                  ITEM_FLAG_DEPRECATED         ], // deprecated
        151 => [FILTER_CR_NUMERIC,   'id',                     null,                    true], // id
        100 => [FILTER_CR_NUMERIC,   'is.nsockets'                                          ], // nsockets
        111 => [FILTER_CR_NUMERIC,   'requiredSkillRank',      null,                    true], // reqskillrank
         99 => [FILTER_CR_ENUM,      'requiredSkill'                                        ], // requiresprof
         66 => [FILTER_CR_ENUM,      'requiredSpell'                                        ], // requiresprofspec
         17 => [FILTER_CR_ENUM,      'requiredFaction'                                      ], // requiresrepwith
        169 => [FILTER_CR_ENUM,      'holidayId'                                            ], // requiresevent
         21 => [FILTER_CR_NUMERIC,   'is.agi',                 null,                    true], // agi
         23 => [FILTER_CR_NUMERIC,   'is.int',                 null,                    true], // int
         22 => [FILTER_CR_NUMERIC,   'is.sta',                 null,                    true], // sta
         24 => [FILTER_CR_NUMERIC,   'is.spi',                 null,                    true], // spi
         20 => [FILTER_CR_NUMERIC,   'is.str',                 null,                    true], // str
        115 => [FILTER_CR_NUMERIC,   'is.health',              null,                    true], // health
        116 => [FILTER_CR_NUMERIC,   'is.mana',                null,                    true], // mana
         60 => [FILTER_CR_NUMERIC,   'is.healthrgn',           null,                    true], // healthrgn
         61 => [FILTER_CR_NUMERIC,   'is.manargn',             null,                    true], // manargn
         41 => [FILTER_CR_NUMERIC,   'is.armor'   ,            null,                    true], // armor
         44 => [FILTER_CR_NUMERIC,   'is.blockrtng',           null,                    true], // blockrtng
         43 => [FILTER_CR_NUMERIC,   'is.block',               null,                    true], // block
         42 => [FILTER_CR_NUMERIC,   'is.defrtng',             null,                    true], // defrtng
         45 => [FILTER_CR_NUMERIC,   'is.dodgertng',           null,                    true], // dodgertng
         46 => [FILTER_CR_NUMERIC,   'is.parryrtng',           null,                    true], // parryrtng
         79 => [FILTER_CR_NUMERIC,   'is.resirtng',            null,                    true], // resirtng
         77 => [FILTER_CR_NUMERIC,   'is.atkpwr',              null,                    true], // atkpwr
         97 => [FILTER_CR_NUMERIC,   'is.feratkpwr',           null,                    true], // feratkpwr
        114 => [FILTER_CR_NUMERIC,   'is.armorpenrtng',        null,                    true], // armorpenrtng
         96 => [FILTER_CR_NUMERIC,   'is.critstrkrtng',        null,                    true], // critstrkrtng
        117 => [FILTER_CR_NUMERIC,   'is.exprtng',             null,                    true], // exprtng
        103 => [FILTER_CR_NUMERIC,   'is.hastertng',           null,                    true], // hastertng
        119 => [FILTER_CR_NUMERIC,   'is.hitrtng',             null,                    true], // hitrtng
         94 => [FILTER_CR_NUMERIC,   'is.splpen',              null,                    true], // splpen
        123 => [FILTER_CR_NUMERIC,   'is.splpwr',              null,                    true], // splpwr
         52 => [FILTER_CR_NUMERIC,   'is.arcsplpwr',           null,                    true], // arcsplpwr
         53 => [FILTER_CR_NUMERIC,   'is.firsplpwr',           null,                    true], // firsplpwr
         54 => [FILTER_CR_NUMERIC,   'is.frosplpwr',           null,                    true], // frosplpwr
         55 => [FILTER_CR_NUMERIC,   'is.holsplpwr',           null,                    true], // holsplpwr
         56 => [FILTER_CR_NUMERIC,   'is.natsplpwr',           null,                    true], // natsplpwr
         57 => [FILTER_CR_NUMERIC,   'is.shasplpwr',           null,                    true], // shasplpwr
         32 => [FILTER_CR_NUMERIC,   'is.dps',                 true,                    true], // dps
         33 => [FILTER_CR_NUMERIC,   'is.dmgmin1',             null,                    true], // dmgmin1
         34 => [FILTER_CR_NUMERIC,   'is.dmgmax1',             null,                    true], // dmgmax1
         36 => [FILTER_CR_NUMERIC,   'is.speed',               true,                    true], // speed
        134 => [FILTER_CR_NUMERIC,   'is.mledps',              true,                    true], // mledps
        135 => [FILTER_CR_NUMERIC,   'is.mledmgmin',           null,                    true], // mledmgmin
        136 => [FILTER_CR_NUMERIC,   'is.mledmgmax',           null,                    true], // mledmgmax
        137 => [FILTER_CR_NUMERIC,   'is.mlespeed',            true,                    true], // mlespeed
        138 => [FILTER_CR_NUMERIC,   'is.rgddps',              true,                    true], // rgddps
        139 => [FILTER_CR_NUMERIC,   'is.rgddmgmin',           null,                    true], // rgddmgmin
        140 => [FILTER_CR_NUMERIC,   'is.rgddmgmax',           null,                    true], // rgddmgmax
        141 => [FILTER_CR_NUMERIC,   'is.rgdspeed',            true,                    true], // rgdspeed
         25 => [FILTER_CR_NUMERIC,   'is.arcres',              null,                    true], // arcres
         26 => [FILTER_CR_NUMERIC,   'is.firres',              null,                    true], // firres
         28 => [FILTER_CR_NUMERIC,   'is.frores',              null,                    true], // frores
         30 => [FILTER_CR_NUMERIC,   'is.holres',              null,                    true], // holres
         27 => [FILTER_CR_NUMERIC,   'is.natres',              null,                    true], // natres
         29 => [FILTER_CR_NUMERIC,   'is.shares',              null,                    true], // shares
         37 => [FILTER_CR_NUMERIC,   'is.mleatkpwr',           null,                    true], // mleatkpwr
         84 => [FILTER_CR_NUMERIC,   'is.mlecritstrkrtng',     null,                    true], // mlecritstrkrtng
         78 => [FILTER_CR_NUMERIC,   'is.mlehastertng',        null,                    true], // mlehastertng
         95 => [FILTER_CR_NUMERIC,   'is.mlehitrtng',          null,                    true], // mlehitrtng
         38 => [FILTER_CR_NUMERIC,   'is.rgdatkpwr',           null,                    true], // rgdatkpwr
         40 => [FILTER_CR_NUMERIC,   'is.rgdcritstrkrtng',     null,                    true], // rgdcritstrkrtng
        101 => [FILTER_CR_NUMERIC,   'is.rgdhastertng',        null,                    true], // rgdhastertng
         39 => [FILTER_CR_NUMERIC,   'is.rgdhitrtng',          null,                    true], // rgdhitrtng
         49 => [FILTER_CR_NUMERIC,   'is.splcritstrkrtng',     null,                    true], // splcritstrkrtng
        102 => [FILTER_CR_NUMERIC,   'is.splhastertng',        null,                    true], // splhastertng
         48 => [FILTER_CR_NUMERIC,   'is.splhitrtng',          null,                    true], // splhitrtng
         51 => [FILTER_CR_NUMERIC,   'is.spldmg',              null,                    true], // spldmg
         50 => [FILTER_CR_NUMERIC,   'is.splheal',             null,                    true], // splheal
          8 => [FILTER_CR_BOOLEAN,   'requiredDisenchantSkill'                              ], // disenchantable
         10 => [FILTER_CR_BOOLEAN,   'lockId'                                               ], // locked
         59 => [FILTER_CR_NUMERIC,   'durability',             null,                    true], // dura
        104 => [FILTER_CR_STRING,    'description',            true                         ], // flavortext
          7 => [FILTER_CR_BOOLEAN,   'description_loc0',       true                         ], // hasflavortext
        142 => [FILTER_CR_STRING,    'iconString',                                          ], // icon
         12 => [FILTER_CR_BOOLEAN,   'itemset',                                             ], // partofset
         13 => [FILTER_CR_BOOLEAN,   'randomEnchant',                                       ], // randomlyenchanted
         14 => [FILTER_CR_BOOLEAN,   'pageTextId',                                          ], // readable
         63 => [FILTER_CR_NUMERIC,   'buyPrice',               null,                    true], // buyprice
         64 => [FILTER_CR_NUMERIC,   'sellPrice',              null,                    true], // sellprice
        165 => [FILTER_CR_NUMERIC,   'repairPrice',            null,                    true], // repaircost
         91 => [FILTER_CR_ENUM,      'totemCategory'                                        ], // tool
        176 => [FILTER_CR_STAFFFLAG, 'flags'                                                ], // flags
        177 => [FILTER_CR_STAFFFLAG, 'flagsExtra'                                           ], // flags2
    );

    public function __construct()
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

        parent::__construct();
    }

    public function createConditionsForWeights(&$data)
    {
        if (!$data['wt'] || !$data['wtv'] || count($data['wt']) != count($data['wtv']))
            return null;

        $this->wtCnd = [];
        $select = [];
        $wtSum  = 0;

        foreach ($data['wt'] as $k => $v)
        {
            @$str = Util::$itemFilter[$v];
            $qty  = intVal($data['wtv'][$k]);

            if ($str && $qty)
            {
                if ($str == 'rgdspeed')                     // dont need no duplicate column
                    $str = 'speed';

                if ($str == 'mledps')                       // todo (med): unify rngdps and mledps to dps
                    $str = 'dps';

                $select[]      = '(`is`.`'.$str.'` * '.$qty.')';
                $this->wtCnd[] = ['is.'.$str, 0, '>'];
                $wtSum        += $qty;
            }
            else                                            // well look at that.. erronous indizes or zero-weights
            {
                unset($data['wt'][$k]);
                unset($data['wtv'][$k]);
            }
        }

        if (count($this->wtCnd) > 1)
            array_unshift($this->wtCnd, 'OR');
        else if (count($this->wtCnd) == 1)
            $this->wtCnd = $this->wtCnd[0];

        if ($select)
        {
            $this->extraOpts['is']['s'][] = ', IF(is.id IS NULL, 0, ('.implode(' + ', $select).') / '.$wtSum.') AS score';
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
        {
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            case 2:                                         // bindonpickup [yn]
                if ($this->int2Bool($cr[1]))
                    return ['bonding', 1, $cr[1] ? null : '!'];
                break;
            case 3:                                         // bindonequip [yn]
                if ($this->int2Bool($cr[1]))
                    return ['bonding', 2, $cr[1] ? null : '!'];
                break;
            case 4:                                         // bindonuse [yn]
                if ($this->int2Bool($cr[1]))
                    return ['bonding', 3, $cr[1] ? null : '!'];
                break;
            case 5:                                         // questitem [yn]
                if ($this->int2Bool($cr[1]))
                    return ['bonding', [4, 5], $cr[1] ? null : '!'];
                break;
            case 168:                                       // teachesspell [yn]        483: learn recipe; 55884: learn mount/pet
                if ($this->int2Bool($cr[1]))
                    return ['spellId1', [483, 55884], $cr[1] ? null : '!'];
                break;
            case 15:                                        // unique [yn]
                if ($this->int2Bool($cr[1]))
                    return ['maxCount', 1, $cr[1] ? null : '!'];
                break;
            case 161:                                        // availabletoplayers [yn]
                if ($this->int2Bool($cr[1]))
                    return [['cuFlags', CUSTOM_UNAVAILABLE, '&'], 0, $cr[1] ? null : '!'];
                break;
            case 80:                                        // has sockets [enum]
                switch ($cr[1])
                {
                    case 5:                                 // Yes
                        return ['is.nsockets', 0, '!'];
                    case 6:                                 // No
                        return ['is.nsockets', 0];
                    case 1:                                 // Meta
                    case 2:                                 // Red
                    case 3:                                 // Yellow
                    case 4:                                 // Blue
                        $mask = 1 << ($cr[1] - 1);
                        return ['OR', ['socketColor1', $mask], ['socketColor2', $mask], ['socketColor3', $mask]];
                }
                break;
            case 81:                                        // fits gem slot [enum]
                switch ($cr[1])
                {
                    case 5:                                 // Yes
                        return ['gemEnchantmentId', 0, '!'];
                    case 6:                                 // No
                        return ['gemEnchantmentId', 0];
                    case 1:                                 // Meta
                    case 2:                                 // Red
                    case 3:                                 // Yellow
                    case 4:                                 // Blue
                        $mask = 1 << ($cr[1] - 1);
                        return ['AND', ['gemEnchantmentId', 0, '!'], ['gemColorMask', $mask, '&']];
                }
                break;
            case 107:                                       // effecttext [str]                 not yet parsed              ['effectsParsed_loc'.User::$localeId, $cr[2]]
/* todo */      return [1];
            case 132:                                       // glyphtype [enum]                 susubclass not yet set
                switch ($cr[1])
                {
                    case 1:                                 // Major
                    case 2:                                 // Minor
                        return ['AND', ['class', 16], ['subSubClass', $cr[1]]];
                }
                break;
            case 124:                                       // randomenchants [str]
                // joining this in one step results in hell ..  so .. two steps
                // todo (low): in _theory_ Filter::modularizeString() should also be applied here
                $randIds = DB::Aowow()->selectCol('SELECT IF (ire.id > 0, iet.entry, -iet.entry) FROM item_enchantment_template iet JOIN ?_itemrandomenchant ire ON ABS(ire.id) = iet.ench WHERE ire.name_loc'.User::$localeId.' LIKE ?', '%'.$cr[2].'%');

                if ($randIds)
                    return ['randomEnchant', $randIds];
                else
                    return [0];                             // no results aren't really input errors
            case 125:                                       // reqarenartng [op] [int]  todo (low): find out, why "IN (W, X, Y) AND IN (X, Y, Z)" doesn't result in "(X, Y)"
                if (!$this->isSaneNumeric($cr[2]) || !$this->int2Op($cr[1]))
                    break;

                $this->formData['extraCols'][] = $cr[0];
                $query = sprintf($this->extCostQuery, 'iec.reqPersonalrating '.$cr[1].' '.$cr[2]);
                return ['id', DB::Aowow()->selectCol($query)];
            case 160:                                       // relatedevent [enum]      like 169 .. crawl though npc_vendor and loot_templates of event-related spawns
/* todo */      return [1];
            case 152:                                       // classspecific [enum]
                $_ = @$this->enums[$cr[0]][$cr[1]];
                if ($_ !== null)
                {
                    if (is_bool($_))
                        return $_ ? ['AND', [['requiredClass', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!'], ['requiredClass', 0, '>']] : ['OR', [['requiredClass', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL], ['requiredClass', 0]];
                    else if (is_int($_))
                        return ['AND', [['requiredClass', CLASS_MASK_ALL, '&'], CLASS_MASK_ALL, '!'], ['requiredClass', 1 << ($_ - 1), '&']];
                }
                break;
            case 153:                                       // racespecific [enum]
                $_ = @$this->enums[$cr[0]][$cr[1]];
                if ($_ !== null)
                {
                    if (is_bool($_))
                        return $_ ? ['AND', [['requiredRace', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'], ['requiredRace', 0, '>']] : ['OR', [['requiredRace', RACE_MASK_ALL, '&'], RACE_MASK_ALL], ['requiredRace', 0]];
                    else if (is_int($_))
                        return ['AND', [['requiredRace', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'], ['requiredRace', 1 << ($_ - 1), '&']];
                }
                break;
            case 35:                                        // damagetype [enum]
                if (!$this->isSaneNumeric($cr[1]) || $cr[1] > 6 || $cr[1] < 0)
                    break;

                return ['OR', ['dmgType1', $cr[1]], ['dmgType2', $cr[1]]];
            case 109:                                       // armorbonus [op] [int]
                if (!$this->isSaneNumeric($cr[2], false) || !$this->int2Op($cr[1]))
                    break;

                $this->formData['extraCols'][] = $cr[0];
                return ['AND', ['armordamagemodifier', $cr[2], $cr[1]], ['class', ITEM_CLASS_ARMOR]];
            case 86:                                        // craftedprof [enum]
                $_ = @$this->enums[99][$cr[1]];             // recycled enum
                if (is_bool($_))
                    return ['i.source', '1:', $_ ? null : '!'];
                else if (is_int($_))
                    return ['s.skillLine1', $_];

                break;
            case 16:                                        // dropsin [zone]
/* todo */      return [1];
            case 105:                                       // dropsinnormal [heroicdungeon-any]
/* todo */      return [1];
            case 106:                                       // dropsinheroic [heroicdungeon-any]
/* todo */      return [1];
            case 147:                                       // dropsinnormal10 [multimoderaid-any]
/* todo */      return [1];
            case 148:                                       // dropsinnormal25 [multimoderaid-any]
/* todo */      return [1];
            case 149:                                       // dropsinheroic10 [heroicraid-any]
/* todo */      return [1];
            case 150:                                       // dropsinheroic25 [heroicraid-any]
/* todo */      return [1];
            case 68:                                        // otdisenchanting [yn]
/* todo */      return [1];
            case 69:                                        // otfishing [yn]
/* todo */      return [1];
            case 70:                                        // otherbgathering [yn]
/* todo */      return [1];
            case 71:                                        // otitemopening [yn]
/* todo */      return [1];
            case 72:                                        // otlooting [yn]
/* todo */      return [1];
            case 143:                                       // otmilling [yn]
/* todo */      return [1];
            case 73:                                        // otmining [yn]
/* todo */      return [1];
            case 74:                                        // otobjectopening [yn]
/* todo */      return [1];
            case 75:                                        // otpickpocketing [yn]
/* todo */      return [1];
            case 88:                                        // otprospecting [yn]
/* todo */      return [1];
            case 93:                                        // otpvp [pvp]
/* todo */      return [1];
            case 171:                                       // otredemption [yn]
/* todo */      return [1];
            case 76:                                        // otskinning [yn]
/* todo */      return [1];
            case 158:                                       // purchasablewithcurrency [enum]
            case 118:                                       // purchasablewithitem [enum]
                if (in_array($cr[1], $this->enums[$cr[0]]))
                    $_ = (array)$cr[1];
                else if ($cr[1] == FILTER_ENUM_ANY)
                    $_ = $this->enums[$cr[0]];
                else
                    break;

                $query = sprintf($this->extCostQuery, 'iec.reqItemId1 IN (?a) OR iec.reqItemId2 IN (?a) OR iec.reqItemId3 IN (?a) OR iec.reqItemId4 IN (?a) OR iec.reqItemId5 IN (?a)');
                if ($foo = DB::Aowow()->selectCol($query, $_, $_, $_, $_, $_, $_, $_, $_, $_, $_))
                    return ['id', $foo];

                break;
            case 144:                                       // purchasablewithhonor [yn]
                if ($this->int2Bool($cr[1]))
                {
                    $query = sprintf($this->extCostQuery, 'iec.reqHonorPoints > 0');
                    if ($foo = DB::Aowow()->selectCol($query))
                        return ['id', $foo, $cr[1] ? null : '!'];
                }
                break;
            case 145:                                       // purchasablewitharena [yn]
                if ($this->int2Bool($cr[1]))
                {
                    $query = sprintf($this->extCostQuery, 'iec.reqArenaPoints > 0');
                    if ($foo = DB::Aowow()->selectCol($query))
                        return ['id', $foo, $cr[1] ? null : '!'];
                }
                break;
            case 18:                                        // rewardedbyfactionquest [side]
/* todo */      return [1];
            case 126:                                       // rewardedbyquestin [zone-any]
/* todo */      return [1];
            case 172:                                       // rewardedbyachievement [yn]
/* todo */      return [1];
            case 92:                                        // soldbyvendor [yn]
/* todo */      return [1];
            case 129:                                       // soldbynpc [str-small]
/* todo */      return [1];
            case 90:                                        // avgbuyout [op] [int]
/* todo */      return [1];
            case 65:                                        // avgmoney [op] [int]
                if (!$this->isSaneNumeric($cr[2]) || !$this->int2Op($cr[1]))
                    break;

                $this->formData['extraCols'][] = $cr[0];
                return ['AND', ['flags', ITEM_FLAG_OPENABLE, '&'], ['((minMoneyLoot + maxMoneyLoot) / 2)', $cr[2], $cr[1]]];
            case 62:                                        // cooldown [op] [int]                      fuck it .. too complex atm
                if (!$this->isSaneNumeric($cr[2]) || !$this->int2Op($cr[1]))
                    break;

                $this->formData['extraCols'][] = $cr[0];
/* todo */      return [1];
            case 163:                                       // disenchantsinto [disenchanting]
                if (!$this->isSaneNumeric($cr[1]))
                    break;
            // 35
/* todo */      return [1];
            case 85:                                        // objectivequest [side]
/* todo */      return [1];
            case 87:                                        // reagentforability [enum]
                $_ = @$this->enums[99][$cr[1]];             // recycled enum
                if ($_ !== null)
                {
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
                break;
            case 6:                                         // startsquest [side]
                switch ($cr[1])
                {
                    case 1:                                 // any
                        return ['startQuest', 0, '>'];
                    case 2:                                 // exclude horde only
                        return ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 2]];
                    case 3:                                 // exclude alliance only
                        return ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 1]];
                    case 4:                                 // both
                        return ['AND', ['startQuest', 0, '>'], [['flagsExtra', 0x3, '&'], 0]];
                    case 5:                                 // none
                        return ['startQuest', 0];
                }
                break;
            case 130:                                       // hascomments [yn]
                break;
            case 113:                                       // hasscreenshots [yn]
                break;
            case 167:                                       // hasvideos [yn]
                break;
        }

        unset($cr);
        $this->error = 1;
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

            // they MAY be strings if only one weight is set
            $_v['wt']  = (array)$_v['wt'];
            $_v['wtv'] = (array)$_v['wtv'];

            $parts[] = $this->createConditionsForWeights($_v);

            foreach ($_v['wt'] as $_)
                $this->formData['extraCols'][] = $_;

            $this->formData['setWeights'] = [$_v['wt'], $_v['wtv']];
        }

        // upgrade for [form only]
        if (isset($_v['upg']))
        {
            // valid item?
            if (!is_int($_v['upg']) && !is_array($_v['upg']))
            {
                unset($this->formData['form']['upg']);
                unset($_v['upg']);
            }
            else
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
        }

        // group by [form only]
        if (isset($_v['gb']))
        {
            // valid item?
            if (is_int($_v['gb']) && $_v['gb'] >= 0 && $_v['gb'] < 4)
                $this->formData['form']['gb'] = $_v['gb'];
            else
                unset($_v['gb']);
        }

        // name
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name_loc'.User::$localeId]))
                $parts[] = $_;

        // usable-by (not excluded by requiredClass && armor or weapons match mask from ?_classes)
        if (isset($_v['ub']))
        {
            if (in_array($_v['ub'], [1, 2, 3, 4, 5, 6, 7, 8, 9, 11]))
            {
                $parts[] = array(
                    'AND',
                    ['OR', ['requiredClass', 0], ['requiredClass', $this->list2Mask($_v['ub']), '&']],
                    [
                        'OR',
                        ['class', [2, 4], '!'],
                        ['AND', ['class', 2], ['subclassbak', $this->ubFilter[$_v['ub']][ITEM_CLASS_WEAPON]]],
                        ['AND', ['class', 4], ['subclassbak', $this->ubFilter[$_v['ub']][ITEM_CLASS_ARMOR]]]
                    ]
                );
            }
            else
                unset($_v['ub']);
        }

        // quality [list]
        if (isset($_v['qu']))
        {
            $_ = (array)$_v['qu'];
            if (!array_diff($_, array_keys(Util::$rarityColorStings)))
                $parts[] = ['quality', $_];
            else
                unset($_v['qu']);
        }

        // type
        if (isset($_v['ty']))
        {
            // should be contextual to 'class'
            $_ = (array)$_v['ty'];
            $parts[] = ['subclass', $_];
        }

        // slot
        if (isset($_v['sl']))
        {
            // should be contextual
            $_ = (array)$_v['sl'];
            $parts[] = ['slot', $_];
        }

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
                default:
                    unset($_v['si']);
            }
        }

        // itemLevel min
        if (isset($_v['minle']))
        {
            if (is_int($_v['minle']) && $_v['minle'] > 0)
                $parts[] = ['itemLevel', $_v['minle'], '>='];
            else
                unset($_v['minle']);
        }

        // itemLevel max
        if (isset($_v['maxle']))
        {
            if (is_int($_v['maxle']) && $_v['maxle'] > 0)
                $parts[] = ['itemLevel', $_v['maxle'], '<='];
            else
                unset($_v['maxle']);
        }

        // reqLevel min
        if (isset($_v['minrl']))
        {
            if (is_int($_v['minrl']) && $_v['minrl'] > 0)
                $parts[] = ['requiredLevel', $_v['minrl'], '>='];
            else
                unset($_v['minrl']);
        }

        // reqLevel max
        if (isset($_v['maxrl']))
        {
            if (is_int($_v['maxrl']) && $_v['maxrl'] > 0)
                $parts[] = ['requiredLevel', $_v['maxrl'], '<='];
            else
                unset($_v['maxrl']);
        }

        return $parts;
    }
}

?>
