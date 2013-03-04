<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class Item extends BaseType
{
    public    $name       = '';
    public    $tooltip    = '';
    public    $json       = [];
    public    $itemMods   = [];
    private   $ssd        = null;

    protected $setupQuery = 'SELECT * FROM item_template i LEFT JOIN ?_item_template_addon iX ON i.entry = iX.id LEFT JOIN locales_item l ON i.entry = l.entry WHERE i.entry = ?d';

    public function __construct($data)
    {
        parent::__construct($data);

        // post processing
        $this->name = Util::localizedString($this->template, 'name');

        // item is scaling; overwrite other values
        if ($this->template['ScalingStatDistribution'] > 0 && $this->template['ScalingStatValue'] > 0)
        {
            $this->ssd = DB::Aowow()->selectRow("SELECT * FROM ?_scalingstatdistribution WHERE id = ?", $this->template['ScalingStatDistribution']);

            // stats and ratings
            for ($i = 1; $i <= 10; $i++)
            {
                if ($this->ssd['statMod'.$i] <= 0)
                {
                    $this->template['stat_type'.$i] = 0;
                    $this->template['stat_value'.$i] = 0;
                }
                else
                {
                    $this->template['stat_type'.$i] = $this->ssd['statMod'.$i];
                    $this->template['stat_value'.$i] = intVal(($this->getSSDMod('stats') * $this->ssd['modifier'.$i]) / 10000);
                }
            }

            // armor: only replace if set
            if ($ssvArmor = $this->getSSDMod('armor'))
                $this->template['armor'] = $ssvArmor;

            // If set dpsMod in ScalingStatValue use it for min (70% from average), max (130% from average) damage
            if ($extraDPS = $this->getSSDMod('dps'))
            {
                $average = $extraDPS * $this->template['delay'] / 1000;
                $this->template['dmg_min1'] = number_format(0.7 * $average);   // dmg_2 not used for heirlooms
                $this->template['dmg_max1'] = number_format(1.3 * $average);
            }

            // Apply Spell Power from ScalingStatValue if set
            if ($spellBonus = $this->getSSDMod('spell'))
            {
                $this->template['stat_type10'] = ITEM_MOD_SPELL_POWER;
                $this->template['stat_value10'] = $spellBonus;
            }
        }

        // create json values; zero-values are filtered later
        $this->json['id']                = $this->Id;
        $this->json['name']              = (ITEM_QUALITY_HEIRLOOM - $this->template['Quality']).$this->name;
        $this->json['icon']              = $this->template['icon'];
        $this->json['classs']            = $this->template['class'];
        $this->json['subclass']          = $this->template['subclass'];
        $this->json['slot']              = $this->template['InventoryType'];
        $this->json['slotbak']           = $this->template['InventoryType'];
        $this->json['level']             = $this->template['ItemLevel'];
        $this->json['reqlevel']          = $this->template['RequiredLevel'];
        $this->json['displayid']         = $this->template['displayid'];
        $this->json['commondrop']        = ($this->template['RandomProperty'] > 0 || $this->template['RandomSuffix'] > 0) ? 'true' : null; // string required :(
        $this->json['holres']            = $this->template['holy_res'];
        $this->json['firres']            = $this->template['fire_res'];
        $this->json['natres']            = $this->template['nature_res'];
        $this->json['frores']            = $this->template['frost_res'];
        $this->json['shares']            = $this->template['shadow_res'];
        $this->json['arcres']            = $this->template['arcane_res'];
        $this->json['armorbonus']        = $this->template['ArmorDamageModifier'];
        $this->json['armor']             = $this->template['armor'];
        $this->json['itemset']           = $this->template['itemset'];
        $this->json['socket1']           = $this->template['socketColor_1'];
        $this->json['socket2']           = $this->template['socketColor_2'];
        $this->json['socket3']           = $this->template['socketColor_3'];
        $this->json['nsockets']          = ($this->json['socket1'] > 0 ? 1 : 0) + ($this->json['socket2'] > 0 ? 1 : 0) + ($this->json['socket3'] > 0 ? 1 : 0);
        $this->json['socketbonus']       = $this->template['socketBonus'];
        $this->json['scadist']           = $this->template['ScalingStatDistribution'];
        $this->json['scaflags']          = $this->template['ScalingStatValue'];
        if ($this->template['class'] == ITEM_CLASS_WEAPON || $this->template['class'] == ITEM_CLASS_AMMUNITION)
        {
            $this->json['dmgtype1']      = $this->template['dmg_type1'];
            $this->json['dmgmin1']       = $this->template['dmg_min1'] + $this->template['dmg_min2'];
            $this->json['dmgmax1']       = $this->template['dmg_max1'] + $this->template['dmg_max2'];
            $this->json['dps']           = !$this->template['delay'] ? 0 : number_format(($this->json['dmgmin1'] + $this->json['dmgmax1']) / (2 * $this->template['delay'] / 1000), 1);
            $this->json['speed']         = number_format($this->template['delay'] / 1000, 2);

            if (in_array($this->json['subclass'], [2, 3, 18, 19]))
            {
                $this->json['rgddmgmin'] = $this->json['dmgmin1'];
                $this->json['rgddmgmax'] = $this->json['dmgmax1'];
                $this->json['rgdspeed']  = $this->json['speed'];
                $this->json['rgddps']    = $this->json['dps'];
            }
            else if ($this->template['class'] != ITEM_CLASS_AMMUNITION)
            {
                $this->json['mledmgmin'] = $this->json['dmgmin1'];
                $this->json['mledmgmax'] = $this->json['dmgmax1'];
                $this->json['mlespeed']  = $this->json['speed'];
                $this->json['mledps']    = $this->json['dps'];
            }

            if ($this->json['classs'] == ITEM_CLASS_WEAPON && in_array($this->json['subclass'], [5, 6, 10]) && $this->json['dps'] > 54.8)
                $this->json['feratkpwr'] = max(0, round((($this->json['dmgmin1'] + $this->json['dmgmax1']) / (2 * $this->template['delay'] / 1000) - 54.8) * 14, 0));
        }
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->selectRow('
            SELECT
                t.name,
                l.*
            FROM
                item_template t,
                locales_item l
            WHERE
                t.entry = ?d AND
                t.entry = l.entry',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public static function getEquivalentSetPieces($id)
    {
        return DB::Aowow()->selectCol('
            SELECT
                a.entry
            FROM
                item_template a,
                item_template b
            WHERE
                b.entry = ?d AND
                a.InventoryType = b.InventoryType AND
                a.itemset = b.itemset',
            $id
        );
    }
    // end static use

    public function getListviewData()
    {
        return array(
            'id'       => $this->Id,
            'name'     => $this->name,
        );
    }

    public function addGlobalsToJScript(&$gItems)
    {
        $gItems[$this->Id] = array(
            'name'    => $this->name,
            'quality' => $this->template['Quality'],
            'icon'    => $this->template['icon'],
        );
    }

    /*
        enhance (set by comparison tool or formatet external links)
            ench: enchantmentId
            sock: bool (extraScoket (gloves, belt))
            gems: array (:-separated itemIds)
            rand: >0: randomPropId; <0: randomSuffixId
        interactive (set to place javascript/anchors to manipulate level and ratings or link to filters (static tooltips vs popup tooltip))
    */
    public function createTooltip($enhance = [], $interactive = false)
    {
        if (!empty($this->tooltip))
            return $this->tooltip;

        if (isset($enhance['rand']))
        {
            $rndEnch = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomenchant WHERE Id = ?d', $enhance['rand']);
            $this->name .= ' '.Util::localizedString($rndEnch, 'name');
            $randEnchant['stats'] = '';

            for ($i = 1; $i < 6; $i++)
            {
                if ($rndEnch['enchantId'.$i] <= 0)
                    continue;

                $enchant = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?d', $rndEnch['enchantId'.$i]);
                if ($rndEnch['allocationPct'.$i] > 0)
                {
                    $amount = intVal($rndEnch['allocationPct'.$i] * $this->generateEnchSuffixFactor() / 10000);
                    $randEnchant['stats'] .= '<span>'.str_replace('$i', $amount, Util::localizedString($enchant, 'text')).'</span><br />';
                }
                else
                    $randEnchant['stats'] .= '<span>'.Util::localizedString($enchant, 'text').'</span><br />';
            }
        }

        // IMPORTAT: DO NOT REMOVE THE HTML-COMMENTS! THEY ARE REQUIRED TO UPDATE THE TOOLTIP CLIENTSIDE
        $x = '';

        // upper table: stats
        $x .= '<table><tr><td>';

        // name; quality
        $x .= '<b class="q'.$this->template['Quality'].'">'.$this->name.'</b>';

        // heroic tag
        if (($this->template['Flags'] & ITEM_FLAG_HEROIC) && $this->template['Quality'] == ITEM_QUALITY_EPIC)
            $x .= '<br /><span class="q2">'.Lang::$item['heroic'].'</span>';

        // requires map (todo: reparse ?_zones for non-conflicting data; generate Link to zone)
        if ($this->template['Map'])
        {
            $map = DB::Aowow()->selectRow('SELECT * FROM ?_zones WHERE mapid=?d LIMIT 1', $this->template['Map']);
            $x .= '<br />'.Util::localizedString($map, 'name');
        }

        // requires area
        if ($this->template['area'])
        {
            $area = DB::Aowow()->selectRow('SELECT * FROM ?_areatable WHERE Id=?d LIMIT 1', $this->template['area']);
            $x .= '<br />'.Util::localizedString($area, 'name');
        }

        // conjured
        if ($this->template['Flags'] & ITEM_FLAG_CONJURED)
            $x .= '<br />'.Lang::$game['conjured'];

        // bonding
        if (($this->template['Flags'] & ITEM_FLAG_ACCOUNTBOUND) && $this->template['Quality'] == ITEM_QUALITY_HEIRLOOM)
            $x .= '<br /><!--bo-->'.Lang::$item['bonding'][0];
        else if ($this->template['bonding'])
            $x .= '<br /><!--bo-->'.Lang::$item['bonding'][$this->template['bonding']];

        // unique || unique-equipped || unique-limited
        if ($this->template['maxcount'] == 1)
            $x .= '<br />'.Lang::$item['unique'];
        else if ($this->template['Flags'] & ITEM_FLAG_UNIQUEEQUIPPED)
            $x .= '<br />'.Lang::$item['uniqueEquipped'];
        else if ($this->template['ItemLimitCategory'])
        {
            $limit = DB::Aowow()->selectRow("SELECT * FROM ?_itemlimitcategory WHERE id = ?", $this->template['ItemLimitCategory']);
            $x .= '<br />'.($limit['isGem'] ? Lang::$item['uniqueEquipped'] : Lang::$item['unique']).': '.Util::localizedString($limit, 'name').' ('.$limit['count'].')';
        }

        // max duration
        if ($this->template['duration'] > 0)
            $x .= "<br />".Lang::$item['duration'] . ' '. Util::formatTime($this->template['duration'] * 1000) . ($this->template['duration'] < 0 ? ' ('.Lang::$game['realTime'].')' : null);

        // required holiday
        if ($this->template['HolidayId'])
        {
            $hDay = DB::Aowow()->selectRow("SELECT * FROM ?_holidays WHERE id = ?", $this->template['HolidayId']);
            $x .= '<br />'.Lang::$game['requires'].' <a href="'.$this->template['HolidayId'].'">'.Util::localizedString($hDay, 'name').'</a>';
        }

        // maxcount
        if ($this->template['maxcount'] > 1)
            $x .= ' ('.$this->template['maxcount'].')';

        // item begins a quest
        if ($this->template['startquest'])
            $x .= '<br /><a class="q1" href="?quest='.$this->template['startquest'].'">'.Lang::$item['startQuest'].'</a>';

        // containerType (slotCount)
        if ($this->template['ContainerSlots'] > 1)
        {
            // word order differs <_<
            if (in_array(User::$localeId, [LOCALE_FR, LOCALE_ES, LOCALE_RU]))
                $x .= '<br />'.sprintf(Lang::$item['bagSlotString'], Lang::$item['bagFamily'][$this->template['BagFamily']], $this->template['ContainerSlots']);
            else
                $x .= '<br />'.sprintf(Lang::$item['bagSlotString'], $this->template['ContainerSlots'], Lang::$item['bagFamily'][$this->template['BagFamily']]);
        }

        if (in_array($this->template['class'], [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]))
        {
            $x .= '<table width="100%"><tr>';

            // Class
            $x .= '<td>'.Lang::$item['inventoryType'][$this->template['InventoryType']].'</td>';

            // Subclass
            if ($this->template['class'] == ITEM_CLASS_ARMOR && $this->template['subclass'] > 0)
                $x .= '<th><!--asc'.$this->template['subclass'].'-->'.Lang::$item['armorSubclass'][$this->template['subclass']].'</th>';
            else if ($this->template['class'] == ITEM_CLASS_WEAPON)
                $x .= '<th>'.Lang::$item['weaponSubClass'][$this->template['subclass']].'</th>';
            else if ($this->template['class'] == ITEM_CLASS_AMMUNITION)
                $x .= '<th>'.Lang::$item['projectileSubClass'][$this->template['subclass']].'</th>';

            $x .= '</tr></table>';
        }
        else
            $x .= '<br />';

        // Weapon Stats
        if (isset($this->json['speed']))
        {
            // regular weapon
            if ($this->template['class'] != ITEM_CLASS_AMMUNITION)
            {
                $x .= '<table width="100%"><tr>';
                $x .= '<td><!--dmg-->'.sprintf($this->template['dmg_type1'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->template['dmg_min1'].' - '.$this->template['dmg_max1'], Lang::$game['sc'][$this->template['dmg_type1']]).'</td>';
                $x .= '<th>'.Lang::$item['speed'].' <!--spd-->'.$this->json['speed'].'</th>';
                $x .= '</tr></table>';

                // secondary damage is set
                if ($this->template['dmg_min2'])
                    $x .= '+'.sprintf($this->template['dmg_type2'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->template['dmg_min2'].' - '.$this->template['dmg_max2'], Lang::$game['sc'][$this->template['dmg_type2']]).'<br />';

                $x .= '<!--dps-->('.$this->json['dps'].' '.Lang::$item['dps'].')<br />';

                // display FeralAttackPower if set
                if (isset($this->json['feratkpwr']))
                    $x .= '<span class="c11"><!--fap-->('.$this->json['feratkpwr'].' '.Lang::$item['fap'].')</span><br />';
            }
            // ammunition
            else
                $x .= Lang::$item['addsDps'].' '.number_format(($this->json['dmgmin1'] + $this->json['dmgmax1']) / 2, 1).' '.Lang::$item['dps2'].'<br />';
        }

        // Armor
        if ($this->template['class'] == ITEM_CLASS_ARMOR && $this->template['ArmorDamageModifier'] > 0)
            $x .= '<span class="q2"><!--addamr'.$this->template['ArmorDamageModifier'].'--><span>'.($this->template['armor'] + $this->template['ArmorDamageModifier']).' '.Lang::$item['armor'].'</span></span><br />';
        else if ($this->template['armor'])
            $x .= '<span><!--amr-->'.$this->template['armor'].' '.Lang::$item['armor'].'</span><br />';

        // Block
        if ($this->template['block'])
            $x .= '<span>'.$this->template['block'].' '.Lang::$item['block'].'</span><br />';

        // Random Enchantment
        if (($this->template['RandomProperty'] || $this->template['RandomSuffix']) && !isset($enhance['rand']))
            $x .= '<span class="q2">'.Lang::$item['randEnchant'].'</span><br />';

        // Item is a gem (don't mix with sockets)
        if ($this->template['GemProperties'])
        {
            $gemText = DB::Aowow()->selectRow('SELECT e.* FROM ?_itemenchantment e, ?_gemproperties p WHERE (p.Id = ?d and e.Id = p.itemenchantmentID)', $this->template['GemProperties']);
            $x .= Util::localizedString($gemText, 'text').'<br />';
        }

        // if random enchantment is set, prepend stats from it
        if (isset($enhance['rand']))
            $x .= $randEnchant['stats'];

        // itemMods (display stats and save ratings for later use)
        for ($j = 1; $j <= 10; $j++)
        {
            $type = $this->template['stat_type'.$j];
            $qty  = $this->template['stat_value'.$j];

            if (!$qty || $type <= 0)
                continue;

            // base stat
            if ($type >= ITEM_MOD_AGILITY && $type <= ITEM_MOD_STAMINA)
                $x .= '<span><!--stat'.$type.'-->+'.$qty.' '.Lang::$item['statType'][$type].'</span><br />';
            else                                            // rating with % for reqLevel
                $green[] = $this->parseRating($type, $qty, $this->template['RequiredLevel'], $interactive);
        }

        // magic resistances
        foreach (Util::$resistanceFields as $j => $rowName)
            if ($rowName && $this->template[$rowName] != 0)
                $x .= '+'.$this->template[$rowName].' '.Lang::$game['resistances'][$j].'<br />';

        // Enchantment
        if (isset($enhance['ench']))
        {
            $enchText = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?', $enhance['ench']);
            $x .= '<span class="q2"><!--e-->'.Util::localizedString($enchText, 'text').'</span><br />';
        }
        else                                                // enchantment placeholder
            $x .= '<!--e-->';

        // Sockets w/ Gems
        if (isset($enhance['gems']))
        {
            $gems = DB::Aowow()->select('
                SELECT
                    it.entry AS ARRAY_KEY,
                    ia.icon,
                    ae.*,
                    colorMask
                FROM
                    item_template it
                JOIN
                    ?_item_template_addon ia ON ia.id = it.entry
                JOIN
                    ?_gemproperties ag ON ag.Id = it.GemProperties
                JOIN
                    ?_itemenchantment ae ON ae.Id = ag.itemEnchantmentID
                WHERE
                    it.entry IN (?a)',
                $enhance['gems']
            );
        }
        else
            $enhance['gems'] = [];

        // zero fill empty sockets
        $sockCount = $this->json['nsockets'] + (isset($enhance['sock']) ? 1 : 0);
        while ($sockCount > count($enhance['gems']))
            $enhance['gems'][] = 0;

        $enhance['gems'] = array_reverse($enhance['gems']);

        $hasMatch = 1;
        // fill native sockets
        for ($j = 1; $j <= 3; $j++)
        {
            if (!$this->template['socketColor_'.$j])
                continue;

            for ($i = 0; $i < 4; $i++)
                if (($this->template['socketColor_'.$j] & (1 << $i)))
                    $colorId = $i;

            $pop       = array_pop($enhance['gems']);
            $col       = $pop ? 1 : 0;
            $hasMatch &= $pop ? (($gems[$pop]['colorMask'] & (1 << $colorId)) ? 1 : 0) : 0;
            $icon      = $pop ? sprintf(Util::$bgImagePath['tiny'], strtolower($gems[$pop]['icon'])) : null;
            $text      = $pop ? Util::localizedString($gems[$pop], 'text') : Lang::$item['socket'][$colorId];

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs='.(1 << $colorId).';crv=0" class="socket-'.Util::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-'.Util::$sockets[$colorId].' q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }

        // fill extra socket
        if (isset($enhance['sock']))
        {
            $pop  = array_pop($enhance['gems']);
            $col  = $pop ? 1 : 0;
            $icon = $pop ? sprintf(Util::$bgImagePath['tiny'], strtolower($gems[$pop]['icon'])) : null;
            $text = $pop ? Util::localizedString($gems[$pop], 'text') : Lang::$item['socket'][-1];

            if ($interactive)
                $x .= '<a href="?items=3&amp;filter=cr=81;crs=5;crv=0" class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</a><br />';
            else
                $x .= '<span class="socket-prismatic q'.$col.'" '.$icon.'>'.$text.'</span><br />';
        }
        else                                                // prismatic socket placeholder
            $x .= '<!--ps-->';

        if ($this->template['socketBonus'])
        {
            $sbonus = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?d', $this->template['socketBonus']);
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::$item['socketBonus'].': '.Util::localizedString($sbonus, 'text').'</span><br />';
        }

        // durability
        if ($this->template['MaxDurability'])
            $x .= Lang::$item['durability'].' '.$this->template['MaxDurability'].' / '.$this->template['MaxDurability'].'<br />';

        // required classes
        if ($classes = Lang::getClassString($this->template['AllowableClass']))
            $x .= Lang::$game['classes'].': '.$classes.'<br />';

        // required races
        if ($races = Lang::getRaceString($this->template['AllowableRace']))
            $x .= Lang::$game['races'].': '.$races['name'].'<br />';

        // required honorRank (not used anymore)
        if ($this->template['requiredhonorrank'])
            $x .= Lang::$game['requires'].': '.Lang::$game['pvpRank'][$this->template['requiredhonorrank']].'<br />';

        // required CityRank..?
        // what the f..

        // required level
        if (($this->template['Flags'] & ITEM_FLAG_ACCOUNTBOUND) && $this->template['Quality'] == ITEM_QUALITY_HEIRLOOM)
            $x .= sprintf(Lang::$game['reqLevelHlm'], ' 1'.Lang::$game['valueDelim'].MAX_LEVEL.' ('.($interactive ? printf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL).')').'<br />';
        else if ($this->template['RequiredLevel'] > 1)
            $x .= sprintf(Lang::$game['reqLevel'], $this->template['RequiredLevel']).'<br />';

        // item level
        $x .= Lang::$item['itemLevel'].' '.$this->template['ItemLevel'];

        // required skill
        if ($this->template['RequiredSkill'])
        {
            $skillText = DB::Aowow()->selectRow('SELECT * FROM ?_skill WHERE skillID = ?d', $this->template['RequiredSkill']);
            $x .= '<br />'.Lang::$game['requires'].' <a class="q1" href="?skill='.$this->template['RequiredSkill'].'">'.Util::localizedString($skillText, 'name').'</a>';
            if ($this->template['RequiredSkillRank'])
                $x .= ' ('.$this->template['RequiredSkillRank'].')';
        }

        // required spell
        if ($this->template['requiredspell'])
            $x .= '<br />'.Lang::$game['requires'].' <a class="q1" href="?spell='.$this->template['requiredspell'].'">'.Spell::getName($this->template['requiredspell']).'</a>';

        // required reputation w/ faction
        if ($this->template['RequiredReputationFaction'])
            $x .= '<br />'.Lang::$game['requires'].' <a class="q1" href=?faction="'.$this->template['RequiredReputationFaction'].'">'.Faction::getName($this->template['RequiredReputationFaction']).'</a> - '.Lang::$game['rep'][$this->template['RequiredReputationRank']];

        // locked
        if ($this->template['lockid'])
        {
            $lock = DB::Aowow()->selectRow('
                SELECT
                    *
                FROM
                    ?_lock
                WHERE
                    lockID = ?d',
                $this->template['lockid']
            );
            // only use first useful entry
            for ($j = 1; $j <= 5; $j++)
            {
                if ($lock['type'.$j] == 1)                  // opened by item
                {
                    $l = Lang::$game['requires'].' <a class="q1" href="?item='.$lock['lockproperties'.$j].'">'.Item::getName($lock['lockproperties'.$j]).'</a>';
                    break;
                }
                else if ($lock['type'.$j] == 2)             // opened by skill
                {
                    $lockText = DB::Aowow()->selectRow('SELECT ?# FROM ?_locktype WHERE id = ?d', $lock['lockproperties'.$j]);
                    $l = Lang::$game['requires'].' '.Util::localizedString($lockText, 'name').' ('.$lock['requiredskill'.$j].')';
                    break;
                }
            }
            $x .= '<br /><span class="q0">'.Lang::$item['locked'].'<br />'.$l.'</span>';
        }

        // upper table: done
        $x .= '</td></tr></table>';

        // spells on item
        for ($j = 1; $j <= 5; $j++)
        {
            // todo: complete Class SpellList and fetch from List
            if ($this->template['spellid_'.$j] > 0)
            {
                $itemSpell = new Spell($this->template['spellid_'.$j]);
                if ($parsed = $itemSpell->parseText('description', $this->template['RequiredLevel']))
                    $green[] = Lang::$item['trigger'][$this->template['spelltrigger_'.$j]] . $parsed;
            }
        }

        // lower table (ratings, spells, ect)
        $x .= '<table><tr><td>';
        if (isset($green))
            foreach ($green as $j => $bonus)
                if ($bonus)
                    $x .= '<span class="q2">'.$bonus.'</span><br />';

        // recipe handling (some stray Techniques have subclass == 0)
        if ($this->template['class'] == ITEM_CLASS_RECIPE && ($this->template['subclass'] == 1 || $this->template['BagFamily'] = 16))
        {
            // todo: aaaand another one for optimization
            $craftSpell   = new Spell($this->template['spellid_2']);
            $craftItem    = new Item($craftSpell->template["effect1CreateItemId"]);
            $reagentItems = [];

            for ($i = 1; $i <= 8; $i++)
                if ($craftSpell->template["reagent".$i])
                    $reagentItems[$craftSpell->template["reagent".$i]] = $craftSpell->template["reagentCount".$i];

            $reagents = new ItemList(array(['i.entry', array_keys($reagentItems)]));
            $reqReag  = [];

            foreach ($reagents->container as $r)
                $reqReag[] = '<a href="?item='.$r->Id.'">'.$r->name.'</a> ('.$reagentItems[$r->Id].')';

            $x .= '<span class="q2">'.Lang::$item['trigger'][0].' <a href="?spell='.$this->template['spellid_2'].'">'.Util::localizedString($this->template, 'description').'</a></span>';
            if (isset($craftItem->Id))
                $x .= '<div><br />'.$craftItem->createTooltip(null, $interactive).'</div><br />';
        }

        // Item Set
        $tmpX    = '';
        $pieces  = [];
        $itemset = DB::Aowow()->selectRow('
            SELECT
                *
            FROM
                ?_itemset
            WHERE
                (item1=?d or item2=?d or item3=?d or item4=?d or item5=?d or item6=?d or item7=?d or item8=?d or item9=?d or item10=?d)',
            $this->Id, $this->Id, $this->Id, $this->Id, $this->Id, $this->Id, $this->Id, $this->Id, $this->Id, $this->Id
        );

        if ($itemset)
        {
            $num = 0;                                       // piece counter
            for ($i = 1; $i <= 10; $i++)
            {
                if ($itemset['item'.$i] <= 0)
                    continue;

                $num++;
                $equivalents = Item::getEquivalentSetPieces($itemset['item'.$i]);
                $pieces[] = '<span><!--si'.implode(':', $equivalents).'--><a href="?item='.$itemset['item'.$i].'">'.Item::getName($itemset['item'.$i]).'</a></span>';
            }
            $tmpX .= implode('<br />', $pieces);

            $x .= '<br /><span class="q"><a href="?itemset='.$itemset['Id'].'" class="q">'.Util::localizedString($itemset, 'name').'</a> (0/'.$num.')</span>';

            if ($itemset['skillID'])                        // bonus requires skill to activate
            {
                $name = DB::Aowow()->selectRow('SELECT * FROM ?_skill WHERE skillID=?d', $itemset['skillID']);
                $x .= '<br />'.Lang::$game['requires'].' <a href="?skills='.$itemset['skillID'].'" class="q1">'.Util::localizedString($name, 'name').'</a>';
                if ($itemset['skillLevel'])
                    $x .= ' ('.$itemset['skillLevel'].')';
                $x .= '<br />';
            }

            // list pieces
            $x .= '<div class="q0 indent">'.$tmpX.'</div><br />';

            // get bonuses
            $num = 0;
            for ($j = 1; $j <= 8; $j++)
            {
                if ($itemset['spell'.$j] <= 0)
                    continue;

                // todo: get from static prop?
                $bonus = new Spell($itemset['spell'.$j]);
                $itemset['spells'][$num]['tooltip'] = $bonus->parseText('description', $this->template['RequiredLevel']);
                $itemset['spells'][$num]['entry']   = $itemset['spell'.$j];
                $itemset['spells'][$num]['bonus']   = $itemset['bonus'.$j];
                $num++;
            }

            // sort and list bonuses
            $x .= '<span class="q0">';
            for ($i = 0; $i < $num; $i++)
            {
                for ($j = $i; $j <= $num - 1; $j++)
                {
                    if($itemset['spells'][$j]['bonus'] >= $itemset['spells'][$i]['bonus'])
                        continue;

                    $tmp = $itemset['spells'][$i];
                    $itemset['spells'][$i] = $itemset['spells'][$j];
                    $itemset['spells'][$j] = $tmp;
                }
                $x .= '<span>('.$itemset['spells'][$i]['bonus'].') '.Lang::$item['set'].': <a href="?spell='.$itemset['spells'][$i]['entry'].'">'.$itemset['spells'][$i]['tooltip'].'</a></span>';
                if ($i < $num - 1)
                    $x .= '<br />';
            }
            $x .= '</span>';
        }

        // funny, yellow text at the bottom
        if ($this->template['description'])
            $x .= '<span class="q">"'.Util::localizedString($this->template, 'description').'"</span>';

        // readable
        if ($this->template['PageText'])
            $x .= '<br /><span class="q2">'.Lang::$item['readClick'].'</span>';

        // charges (i guess checking first spell is enough (single charges not shown))
        if ($this->template['spellcharges_1'] > 1)
            $x .= '<br /><span class="q1">'.$this->template['spellcharges_1'].' '.Lang::$item['charges'].'</span>';

        // list required reagents
        if (!empty($reqReag))
            $x .= '<span class="q1">'.Lang::$game['requires']." ".implode(", ", $reqReag).'</span>';
        $x .= '</td></tr></table>';

        if ($this->template['SellPrice'])
            $x .= '<span class="q1">'.Lang::$item['sellPrice'].": ".Util::formatMoney($this->template['SellPrice']).'</span>';

        // heirloom tooltip scaling
        if ($this->ssd)
        {
            $link = array(
                $this->Id,                                  // itemId
                1,                                          // scaleMinLevel
                $this->ssd['maxLevel'],                     // scaleMaxLevel
                $this->ssd['maxLevel'],                     // scaleCurLevel
                $this->template['ScalingStatDistribution'], // scaleDist
                $this->template['ScalingStatValue'],        // scaleFlags
            );
            $x .= '<!--?'.implode(':', $link).'-->';
        }
        else
            $x .= '<!--?'.$this->Id.':1:'.MAX_LEVEL.':'.MAX_LEVEL.'-->';

        $this->tooltip = $x;

        return $this->tooltip;
    }

    private function parseRating($type, $value, $level, $interactive = false)
    {
        $level = min(max($level, 1), MAX_LEVEL);            // clamp level range

        if (!Lang::$item['statType'][$type])                // unknown rating
            return sprintf(Lang::$item['statType'][count(Lang::$item['statType']) - 1], $type, $value);
        else if (in_array($type, Util::$lvlIndepRating))    // level independant Bonus
            return Lang::$item['trigger'][1] . str_replace('%d', '<!--rtg'.$type.'-->'.$value, Lang::$item['statType'][$type]);
        else                                                // rating-Bonuses
        {
            // old
            // $js = '&nbsp;<small>(<a href="javascript:;" onmousedown="return false" onclick="g_setRatingLevel(this,'.$level.','.$type.','.$value.')">';
            // $js .= Util::setRatingLevel($level, $type, $value);
            // $js .= '</a>)</small>';
            if ($interactive)
                $js = '&nbsp;<small>('.printf(Util::$changeLevelString, Util::setRatingLevel($level, $type, $value)).')</a>)</small>';
            else
                $js = "&nbsp;<small>(".Util::setRatingLevel($level, $type, $value).")</small>";

            return Lang::$item['trigger'][1].str_replace('%d', '<!--rtg'.$type.'-->'.$value.$js, Lang::$item['statType'][$type]);
        }
    }

    private function getSSDMod($type)
    {
        $mask = $this->template['ScalingStatValue'];

        switch ($type)
        {
            case 'stats':   $mask &= 0x04001F;      break;
            case 'armor':   $mask &= 0xF001E0;      break;
            case 'dps'  :   $mask &= 0x007E00;      break;
            case 'spell':   $mask &= 0x008000;      break;
            default:        $mask &= 0x0;
        }

        $field = null;
        for ($i = 0; $i < count(Util::$ssdMaskFields); $i++)
            if ($mask & (1 << $i))
                $field = Util::$ssdMaskFields[$i];

        return $field ? DB::Aowow()->selectCell("SELECT ?# FROM ?_scalingstatvalues WHERE charLevel = ?", $field, $this->ssd['maxLevel']) : 0;
    }

    // from Trinity
    public function generateEnchSuffixFactor()
    {
        $rpp = DB::Aowow()->selectRow('SELECT * FROM ?_itemRandomPropPoints WHERE Id = ?', $this->template['ItemLevel']);
        if (!$rpp)
            return 0;

        switch ($this->template['InventoryType'])
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
        switch ($this->template['Quality'])
        {
            case ITEM_QUALITY_UNCOMMON:
                return $rpp['uncommon'.$suffixFactor];
            case ITEM_QUALITY_RARE:
                return $rpp['rare'.$suffixFactor];
            case ITEM_QUALITY_EPIC:
                return $rpp['epic'.$suffixFactor];
            case ITEM_QUALITY_LEGENDARY:
            case ITEM_QUALITY_ARTIFACT:
                return 0;                                    // not have random properties
            default:
                break;
        }
        return 0;
    }

    public function getJsonStats($pieceAssoc = NULL)
    {
        // convert ItemMods
        for ($h = 1; $h <= 10; $h++)
        {
            if (!$this->template['stat_type'.$h])
                continue;

            @$this->itemMods[$this->template['stat_type'.$h]] += $this->template['stat_value'.$h];
        }

        // convert Spells
        for ($h = 1; $h <= 5; $h++)
        {
            // only onEquip
            if ($this->template['spelltrigger_'.$h] != 1)
                continue;

            if ($this->template['spellid_'.$h] <= 0)
                continue;

            $spl   = new Spell($this->template['spellid_'.$h]);
            $stats = $spl->getStatGain();
            foreach ($stats as $mId => $qty)
                @$this->itemMods[$mId] += $qty;
        }

        // fetch and add socketbonusstats
        if ($this->json['socketbonus'] > 0)
        {
            $enh = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?;', $this->json['socketbonus']);
            $this->json['socketbonusstat'] = [];
            $socketbonusstat = Util::parseItemEnchantment($enh);
            foreach ($socketbonusstat as $k => $v)
                $this->json['socketbonusstat'][] = '"'.$k.'":'.$v;

            $this->json['socketbonusstat'] = "{".implode(',', $this->json['socketbonusstat'])."}";
        }

        // readdress itemset .. is wrong for virtual sets
        if ($pieceAssoc)
            $this->json['itemset'] = $pieceAssoc[$this->Id];

        // gather random Enchantments
        if ($this->json['commondrop'])
        {
            $randId    = $this->template['RandomProperty'] > 0 ? $this->template['RandomProperty'] : $this->template['RandomSuffix'];
            $randomIds = DB::Aowow()->selectCol('SELECT ench FROM item_enchantment_template WHERE entry = ?d', $randId);
            if (!$randomIds)
                return null;

            if ($this->template['RandomSuffix'] > 0)
            {
                array_walk($randomIds, function($val, $key) use(&$randomIds) {
                    $randomIds[$key] = -$val;
                });
            }

            $subItems = DB::Aowow()->select('SELECT *, Id AS ARRAY_KEY FROM ?_itemRandomEnchant WHERE Id IN (?a)', $randomIds);

            foreach ($subItems as $k => $sI)
            {
                $jsonEquip = [];
                $jsonText  = [];
                for ($i = 1; $i < 6; $i++)
                {
                    if ($sI['enchantId'.$i] <= 0)
                        continue;

                    $enchant = DB::Aowow()->selectRow('SELECT *, Id AS ARRAY_KEY FROM ?_itemenchantment WHERE Id = ?d', $sI['enchantId'.$i]);
                    if (!$enchant)
                        continue;

                    if ($sI['allocationPct'.$i] > 0)        // RandomSuffix: scaling Enchantment; enchId < 0
                    {
                        $amount     = intVal($sI['allocationPct'.$i] * $this->generateEnchSuffixFactor() / 10000);
                        $jsonEquip  = array_merge($jsonEquip, Util::parseItemEnchantment($enchant, $amount));
                        $jsonText[] = str_replace('$i', $amount, Util::localizedString($enchant, 'text'));
                    }
                    else                                    // RandomProperty: static Enchantment; enchId > 0
                    {
                        $jsonText[] = Util::localizedString($enchant, 'text');
                        $jsonEquip  = array_merge($jsonEquip, Util::parseItemEnchantment($enchant));
                    }
                }

                $subItems[$k] = array(
                    'name'          => Util::localizedString($sI, 'name'),
                    'enchantment'   => implode(', ', $jsonText),
                    'jsonequip'     => $jsonEquip
                );
            }

            $this->json['subitems'] = json_encode($subItems, JSON_FORCE_OBJECT);
        }

        foreach ($this->json as $key => $value)
        {
            if (!isset($value) || $value === "false")
            {
                unset($this->json[$key]);
                continue;
            }

            if (!in_array($key, array('class', 'subclass')) && $value === "0")
            {
                unset($this->json[$key]);
                continue;
            }
        }
    }
}



class ItemList extends BaseTypeList
{
    protected $setupQuery = 'SELECT *, i.entry AS ARRAY_KEY FROM item_template i LEFT JOIN ?_item_template_addon iX ON i.entry = iX.id LEFT JOIN locales_item l ON  i.entry = l.entry WHERE [filter] [cond] GROUP BY i.entry ORDER BY i.Quality DESC';

    public function __construct($conditions)
    {
        // may be called without filtering
        if (class_exists('ItemFilter'))
        {
            $this->filter = new ItemFilter();
            if (($fiData = $this->filter->init()) === false)
                return;
        }

        parent::__construct($conditions);
    }
}

?>
