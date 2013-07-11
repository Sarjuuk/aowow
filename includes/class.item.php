<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class ItemList extends BaseType
{
    public static $type       = TYPE_ITEM;

    public        $tooltip    = '';
    public        $json       = [];
    public        $itemMods   = [];

    public        $rndEnchIds = [];
    public        $subItems   = [];

    private       $ssd        = [];

    protected     $setupQuery = 'SELECT *, i.entry AS ARRAY_KEY FROM item_template i LEFT JOIN ?_item_template_addon iX ON i.entry = iX.id LEFT JOIN locales_item l ON i.entry = l.entry WHERE [filter] [cond] ORDER BY i.Quality DESC';
    protected     $matchQuery = 'SELECT COUNT(1) FROM item_template i LEFT JOIN ?_item_template_addon iX ON i.entry = iX.id LEFT JOIN locales_item l ON i.entry = l.entry WHERE [filter] [cond]';

    public function __construct($conditions, $pieceToSet = null)
    {
        parent::__construct($conditions);

        while ($this->iterate())
        {
            // item is scaling; overwrite other values
            if ($this->curTpl['ScalingStatDistribution'] > 0 && $this->curTpl['ScalingStatValue'] > 0)
                $this->initScalingStats();

            $this->initJsonStats();

            // readdress itemset .. is wrong for virtual sets
            if ($pieceToSet && isset($pieceToSet[$this->id]))
                $this->json[$this->id]['itemset'] = $pieceToSet[$this->id];
        }

        $this->reset();                                     // restore 'iterator'
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

    public function getListviewData($addInfoMask = 0x0)
    {
        /* looks like this data differs per occasion
        *
        * maybe split in groups, like:
        * ITEMINFO_JSON     (0x1): itemMods (including spells) and subitems parsed
        * ITEMINFO_SUBITEMS (0x2): searched by comparison
        * ITEMINFO_VENDOR   (0x4): costs-obj, when displayed as vendor
        * ITEMINFO_LOOT     (0x8): count, stack, pctstack, modes when displaying loot
        */

        $data = [];
        while ($this->iterate())
        {
            // random item is random
            if ($this->curTpl['RandomProperty'] > 0 || $this->curTpl['RandomSuffix'] > 0)
                if ($addInfoMask & ITEMINFO_SUBITEMS)
                    $this->initSubItems();

            if ($addInfoMask & ITEMINFO_JSON)
                $this->extendJsonStats();

            $data[$this->id] = $this->json[$this->id];

            // json vs listview quirk
            $data[$this->id]['name'] = $data[$this->id]['quality'].$data[$this->id]['name'];
            unset($data[$this->id]['quality']);

            if (isset($this->itemMods[$this->id]))          // due to ITEMINFO_JSON
                foreach ($this->itemMods[$this->id] as $k => $v)
                    $data[$this->id][Util::$itemMods[$k]] = $v;

            if ($addInfoMask & ITEMINFO_VENDOR)
            {
                if ($x = $this->curTpl['BuyPrice'])
                    $data[$this->id]['buyprice'] = $x;

                if ($x = $this->curTpl['SellPrice'])
                    $data[$this->id]['sellprice'] = $x;
            }

            // complicated data
            if ($x = $this->curTpl['RequiredSkill'])
                $data[$this->id]['reqskill'] = $x;

            if ($x = $this->curTpl['RequiredSkillRank'])
                $data[$this->id]['reqskillrank'] = $x;

            if ($x = $this->curTpl['requiredspell'])
                $data[$this->id]['reqspell'] = $x;

            if ($x = $this->curTpl['RequiredReputationFaction'])
                $data[$this->id]['reqfaction'] = $x;

            if ($x = $this->curTpl['RequiredReputationRank'])
                $data[$this->id]['reqrep'] = $x;

            if ($x = $this->curTpl['ContainerSlots'])
                $data[$this->id]['nslots'] = $x;


            if (!in_array($this->curTpl['AllowableRace'], [-1, 0]) && $this->curTpl['AllowableRace'] & RACE_MASK_ALL != RACE_MASK_ALL &&
                $this->curTpl['AllowableRace'] & RACE_MASK_ALLIANCE != RACE_MASK_ALLIANCE && $this->curTpl['AllowableRace'] & RACE_MASK_HORDE != RACE_MASK_HORDE)
                $data[$this->id]['reqrace'] = $this->curTpl['AllowableRace'];

            if (!in_array($this->curTpl['AllowableClass'], [-1, 0]) && $this->curTpl['AllowableClass'] & CLASS_MASK_ALL != CLASS_MASK_ALL)
                $data[$this->id]['reqclass'] = $this->curTpl['AllowableClass'];  // $data[$this->id]['classes'] ??
        }

        /* even more complicated crap
            "source":[5],
            "sourcemore":[{"z":3703}],

            {"source":[5],"sourcemore":[{"n":"Commander Oxheart","t":1,"ti":64606,"z":5842}],

            cost:[]     format unk 0:copper, 1:[items]? 2, 3, 4, 5
            stack       [min, max]  // when looting
            avail       unk
            rel         unk
            glyph       major | minor (as id)
            modelviewer

        */

        return $data;
    }

    public function addGlobalsToJscript(&$template, $addMask = 0)
    {
        while ($this->iterate())
        {
            $template->extendGlobalData(self::$type, [$this->id => array(
                'name'    => $this->getField('name', true),
                'quality' => $this->curTpl['Quality'],
                'icon'    => $this->curTpl['icon']
            )]);
        }
    }

    /*
        enhance (set by comparison tool or formated external links)
            ench: enchantmentId
            sock: bool (extraScoket (gloves, belt))
            gems: array (:-separated itemIds)
            rand: >0: randomPropId; <0: randomSuffixId
        interactive (set to place javascript/anchors to manipulate level and ratings or link to filters (static tooltips vs popup tooltip))
        subT (tabled layout doesn't work if used as sub-tooltip in other item or spell tooltips; use line-break instead)
    */
    public function renderTooltip($enhance = [], $interactive = false, $subT = false)
    {
        if ($this->error)
            return;

        $name = $this->getField('name', true);

        if (!empty($this->tooltip[$this->id]))
            return $this->tooltip[$this->id];

        if (!empty($enhance['rand']))
        {
            $rndEnch = DB::Aowow()->selectRow('SELECT * FROM ?_itemrandomenchant WHERE Id = ?d', $enhance['rand']);
            $name   .= ' '.Util::localizedString($rndEnch, 'name');
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
        if (!$subT)
            $x .= '<table><tr><td>';

        // name; quality
        if ($subT)
            $x .= '<span class="q'.$this->curTpl['Quality'].'"><a href="?item='.$this->id.'">'.$name.'</a></span>';
        else
            $x .= '<b class="q'.$this->curTpl['Quality'].'">'.$name.'</b>';

        // heroic tag
        if (($this->curTpl['Flags'] & ITEM_FLAG_HEROIC) && $this->curTpl['Quality'] == ITEM_QUALITY_EPIC)
            $x .= '<br /><span class="q2">'.Lang::$item['heroic'].'</span>';

        // requires map (todo: reparse ?_zones for non-conflicting data; generate Link to zone)
        if ($this->curTpl['Map'])
        {
            $map = DB::Aowow()->selectRow('SELECT * FROM ?_zones WHERE mapId=?d LIMIT 1', $this->curTpl['Map']);
            $x .= '<br />'.Util::localizedString($map, 'name');
        }

        // requires area
        if ($this->curTpl['area'])
        {
            $area = DB::Aowow()->selectRow('SELECT * FROM ?_areatable WHERE Id=?d LIMIT 1', $this->curTpl['area']);
            $x .= '<br />'.Util::localizedString($area, 'name');
        }

        // conjured
        if ($this->curTpl['Flags'] & ITEM_FLAG_CONJURED)
            $x .= '<br />'.Lang::$game['conjured'];

        // bonding
        if (($this->curTpl['Flags'] & ITEM_FLAG_ACCOUNTBOUND) && $this->curTpl['Quality'] == ITEM_QUALITY_HEIRLOOM)
            $x .= '<br /><!--bo-->'.Lang::$item['bonding'][0];
        else if ($this->curTpl['bonding'])
            $x .= '<br /><!--bo-->'.Lang::$item['bonding'][$this->curTpl['bonding']];

        // unique || unique-equipped || unique-limited
        if ($this->curTpl['maxcount'] == 1)
            $x .= '<br />'.Lang::$item['unique'];
        else if ($this->curTpl['Flags'] & ITEM_FLAG_UNIQUEEQUIPPED)
            $x .= '<br />'.Lang::$item['uniqueEquipped'];
        else if ($this->curTpl['ItemLimitCategory'])
        {
            $limit = DB::Aowow()->selectRow("SELECT * FROM ?_itemlimitcategory WHERE id = ?", $this->curTpl['ItemLimitCategory']);
            $x .= '<br />'.($limit['isGem'] ? Lang::$item['uniqueEquipped'] : Lang::$item['unique']).Lang::$colon.Util::localizedString($limit, 'name').' ('.$limit['count'].')';
        }

        // max duration
        if ($this->curTpl['duration'] > 0)
            $x .= "<br />".Lang::$game['duration'] . ' '. Util::formatTime($this->curTpl['duration'] * 1000) . ($this->curTpl['duration'] < 0 ? ' ('.Lang::$game['realTime'].')' : null);

        // required holiday
        if ($this->curTpl['HolidayId'])
        {
            $hDay = DB::Aowow()->selectRow("SELECT * FROM ?_holidays WHERE id = ?", $this->curTpl['HolidayId']);
            $x .= '<br />'.sprintf(Lang::$game['requires'], '<a href="'.$this->curTpl['HolidayId'].'">'.Util::localizedString($hDay, 'name').'</a>');
        }

        // maxcount
        if ($this->curTpl['maxcount'] > 1)
            $x .= ' ('.$this->curTpl['maxcount'].')';

        // item begins a quest
        if ($this->curTpl['startquest'])
            $x .= '<br /><a class="q1" href="?quest='.$this->curTpl['startquest'].'">'.Lang::$item['startQuest'].'</a>';

        // containerType (slotCount)
        if ($this->curTpl['ContainerSlots'] > 1)
        {
            // word order differs <_<
            if (in_array(User::$localeId, [LOCALE_FR, LOCALE_ES, LOCALE_RU]))
                $x .= '<br />'.sprintf(Lang::$item['bagSlotString'], Lang::$item['bagFamily'][$this->curTpl['BagFamily']], $this->curTpl['ContainerSlots']);
            else
                $x .= '<br />'.sprintf(Lang::$item['bagSlotString'], $this->curTpl['ContainerSlots'], Lang::$item['bagFamily'][$this->curTpl['BagFamily']]);
        }

        if (in_array($this->curTpl['class'], [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]))
        {
            $x .= '<table width="100%"><tr>';

            // Class
            $x .= '<td>'.Lang::$item['inventoryType'][$this->curTpl['InventoryType']].'</td>';

            // Subclass
            if ($this->curTpl['class'] == ITEM_CLASS_ARMOR && $this->curTpl['subclass'] > 0)
                $x .= '<th><!--asc'.$this->curTpl['subclass'].'-->'.Lang::$item['armorSubClass'][$this->curTpl['subclass']].'</th>';
            else if ($this->curTpl['class'] == ITEM_CLASS_WEAPON)
                $x .= '<th>'.Lang::$item['weaponSubClass'][$this->curTpl['subclass']].'</th>';
            else if ($this->curTpl['class'] == ITEM_CLASS_AMMUNITION)
                $x .= '<th>'.Lang::$item['projectileSubClass'][$this->curTpl['subclass']].'</th>';

            $x .= '</tr></table>';
        }
        else
            $x .= '<br />';

        // Weapon/Ammunition Stats
        if (in_array($this->curTpl['class'], [ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]))
        {
            $speed   = $this->curTpl['delay'] / 1000;
            $dmgmin1 = $this->curTpl['dmg_min1'] + $this->curTpl['dmg_min2'];
            $dmgmax1 = $this->curTpl['dmg_max1'] + $this->curTpl['dmg_max2'];
            $dps     = $speed ? ($dmgmin1 + $dmgmax1) / (2 * $speed) : 0;

            // regular weapon
            if ($this->curTpl['class'] == ITEM_CLASS_WEAPON)
            {
                $x .= '<table width="100%"><tr>';
                $x .= '<td><!--dmg-->'.sprintf($this->curTpl['dmg_type1'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->curTpl['dmg_min1'].' - '.$this->curTpl['dmg_max1'], Lang::$game['sc'][$this->curTpl['dmg_type1']]).'</td>';
                $x .= '<th>'.Lang::$item['speed'].' <!--spd-->'.number_format($speed, 2).'</th>';
                $x .= '</tr></table>';

                // secondary damage is set
                if ($this->curTpl['dmg_min2'])
                    $x .= '+'.sprintf($this->curTpl['dmg_type2'] ? Lang::$item['damageMagic'] : Lang::$item['damagePhys'], $this->curTpl['dmg_min2'].' - '.$this->curTpl['dmg_max2'], Lang::$game['sc'][$this->curTpl['dmg_type2']]).'<br />';

                $x .= '<!--dps-->('.number_format($dps, 1).' '.Lang::$item['dps'].')<br />';

                // display FeralAttackPower if set
                if (in_array($this->curTpl['subclass'], [5, 6, 10]) && $dps > 54.8)
                    $x .= '<span class="c11"><!--fap-->('.round(($dps - 54.8) * 14, 0).' '.Lang::$item['fap'].')</span><br />';
            }
            // ammunition
            else
                $x .= Lang::$item['addsDps'].' '.number_format(($dmgmin1 + $dmgmax1) / 2, 1).' '.Lang::$item['dps2'].'<br />';
        }

        // Armor
        if ($this->curTpl['class'] == ITEM_CLASS_ARMOR && $this->curTpl['ArmorDamageModifier'] > 0)
            $x .= '<span class="q2"><!--addamr'.$this->curTpl['ArmorDamageModifier'].'--><span>'.sprintf(Lang::$item['armor'], $this->curTpl['armor'] + $this->curTpl['ArmorDamageModifier']).'</span></span><br />';
        else if ($this->curTpl['armor'])
            $x .= '<span><!--amr-->'.sprintf(Lang::$item['armor'], $this->curTpl['armor']).'</span><br />';

        // Block
        if ($this->curTpl['block'])
            $x .= '<span>'.sprintf(Lang::$item['block'], $this->curTpl['block']).'</span><br />';

        // Item is a gem (don't mix with sockets)
        if ($this->curTpl['GemProperties'])
        {
            $gemText = DB::Aowow()->selectRow('SELECT e.* FROM ?_itemenchantment e, ?_gemproperties p WHERE (p.Id = ?d and e.Id = p.itemenchantmentID)', $this->curTpl['GemProperties']);
            $x .= Util::localizedString($gemText, 'text').'<br />';
        }

        // Random Enchantment - if random enchantment is set, prepend stats from it
        if (($this->curTpl['RandomProperty'] || $this->curTpl['RandomSuffix']) && !isset($enhance['rand']))
            $x .= '<span class="q2">'.Lang::$item['randEnchant'].'</span><br />';
        else if (isset($enhance['rand']))
            $x .= $randEnchant['stats'];

        // itemMods (display stats and save ratings for later use)
        for ($j = 1; $j <= 10; $j++)
        {
            $type = $this->curTpl['stat_type'.$j];
            $qty  = $this->curTpl['stat_value'.$j];

            if (!$qty || $type <= 0)
                continue;

            // base stat
            if ($type >= ITEM_MOD_AGILITY && $type <= ITEM_MOD_STAMINA)
                $x .= '<span><!--stat'.$type.'-->+'.$qty.' '.Lang::$item['statType'][$type].'</span><br />';
            else                                            // rating with % for reqLevel
                $green[] = $this->parseRating($type, $qty, $interactive);
        }

        // magic resistances
        foreach (Util::$resistanceFields as $j => $rowName)
            if ($rowName && $this->curTpl[$rowName] != 0)
                $x .= '+'.$this->curTpl[$rowName].' '.Lang::$game['resistances'][$j].'<br />';

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
        $sockCount = $this->curTpl['socketColor_1'] + $this->curTpl['socketColor_2'] + $this->curTpl['socketColor_3'] + (isset($enhance['sock']) ? 1 : 0);
        while ($sockCount > count($enhance['gems']))
            $enhance['gems'][] = 0;

        $enhance['gems'] = array_reverse($enhance['gems']);

        $hasMatch = 1;
        // fill native sockets
        for ($j = 1; $j <= 3; $j++)
        {
            if (!$this->curTpl['socketColor_'.$j])
                continue;

            for ($i = 0; $i < 4; $i++)
                if (($this->curTpl['socketColor_'.$j] & (1 << $i)))
                    $colorId = $i;

            $pop       = array_pop($enhance['gems']);
            $col       = $pop ? 1 : 0;
            $hasMatch &= $pop ? (($gems[$pop]['colorMask'] & (1 << $colorId)) ? 1 : 0) : 0;
            $icon      = $pop ? sprintf(Util::$bgImagePath['tiny'], STATIC_URL, strtolower($gems[$pop]['icon'])) : null;
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
            $icon = $pop ? sprintf(Util::$bgImagePath['tiny'], STATIC_URL, strtolower($gems[$pop]['icon'])) : null;
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
            $x .= '<span class="q'.($hasMatch ? '2' : '0').'">'.Lang::$item['socketBonus'].Lang::$colon.Util::localizedString($sbonus, 'text').'</span><br />';
        }

        // durability
        if ($this->curTpl['MaxDurability'])
            $x .= Lang::$item['durability'].' '.$this->curTpl['MaxDurability'].' / '.$this->curTpl['MaxDurability'].'<br />';

        // required classes
        if ($classes = Lang::getClassString($this->curTpl['AllowableClass']))
            $x .= Lang::$game['classes'].Lang::$colon.$classes.'<br />';

        // required races
        if ($races = Lang::getRaceString($this->curTpl['AllowableRace']))
            $x .= Lang::$game['races'].Lang::$colon.$races['name'].'<br />';

        // required honorRank (not used anymore)
        if ($this->curTpl['requiredhonorrank'])
            $x .= sprintf(Lang::$game['requires'], Lang::$game['pvpRank'][$this->curTpl['requiredhonorrank']]).'<br />';

        // required CityRank..?
        // what the f..

        // required level
        if (($this->curTpl['Flags'] & ITEM_FLAG_ACCOUNTBOUND) && $this->curTpl['Quality'] == ITEM_QUALITY_HEIRLOOM)
            $x .= sprintf(Lang::$game['reqLevelHlm'], ' 1'.Lang::$game['valueDelim'].MAX_LEVEL.' ('.($interactive ? printf(Util::$changeLevelString, MAX_LEVEL) : '<!--lvl-->'.MAX_LEVEL).')').'<br />';
        else if ($this->curTpl['RequiredLevel'] > 1)
            $x .= sprintf(Lang::$game['reqLevel'], $this->curTpl['RequiredLevel']).'<br />';

        // item level
        $x .= Lang::$item['itemLevel'].' '.$this->curTpl['ItemLevel'];

        // required skill
        if ($this->curTpl['RequiredSkill'])
        {
            $_ = '<a class="q1" href="?skill='.$this->curTpl['RequiredSkill'].'">'.SkillList::getName($this->curTpl['RequiredSkill']).'</a>';
            if ($this->curTpl['RequiredSkillRank'])
                $_ .= ' ('.$this->curTpl['RequiredSkillRank'].')';

            $x .= '<br />'.sprintf(Lang::$game['requires'], $_);
        }

        // required spell
        if ($this->curTpl['requiredspell'])
            $x .= '<br />'.Lang::$game['requires2'].' <a class="q1" href="?spell='.$this->curTpl['requiredspell'].'">'.SpellList::getName($this->curTpl['requiredspell']).'</a>';

        // required reputation w/ faction
        if ($this->curTpl['RequiredReputationFaction'])
            $x .= '<br />'.sprintf(Lang::$game['requires'], '<a class="q1" href=?faction="'.$this->curTpl['RequiredReputationFaction'].'">'.Faction::getName($this->curTpl['RequiredReputationFaction']).'</a> - '.Lang::$game['rep'][$this->curTpl['RequiredReputationRank']]);

        // locked
        if ($lId = $this->curTpl['lockid'])
            if ($locks = Lang::getLocks($lId))
                $x .= '<br /><span class="q0">'.Lang::$item['locked'].'<br />'.implode('<br />', $locks).'</span>';

        // upper table: done
        if (!$subT)
            $x .= '</td></tr></table>';
        else
            $x .= '<br>';

        // spells on item
        $itemSpellsAndTrigger = [];
        for ($j = 1; $j <= 5; $j++)
            if ($this->curTpl['spellid_'.$j] > 0)
                $itemSpellsAndTrigger[$this->curTpl['spellid_'.$j]] = $this->curTpl['spelltrigger_'.$j];

        if ($itemSpellsAndTrigger)
        {
            $itemSpells = new SpellList(array(['s.id', array_keys($itemSpellsAndTrigger)]));
            while ($itemSpells->iterate())
                if ($parsed = $itemSpells->parseText('description', $this->curTpl['RequiredLevel']))
                    $green[] = Lang::$item['trigger'][$itemSpellsAndTrigger[$itemSpells->id]] . ($interactive ? '<a href="?spell='.$itemSpells->id.'">'.$parsed.'</a>' : $parsed);
        }

        // lower table (ratings, spells, ect)
        if (!$subT)
            $x .= '<table><tr><td>';

        if (isset($green))
            foreach ($green as $j => $bonus)
                if ($bonus)
                    $x .= '<span class="q2">'.$bonus.'</span><br />';

        // Item Set
        $pieces  = [];
        $itemset = DB::Aowow()->selectRow('
            SELECT
                *
            FROM
                ?_itemset
            WHERE
                (item1=?d or item2=?d or item3=?d or item4=?d or item5=?d or item6=?d or item7=?d or item8=?d or item9=?d or item10=?d)',
            $this->id, $this->id, $this->id, $this->id, $this->id, $this->id, $this->id, $this->id, $this->id, $this->id
        );

        if ($itemset)
        {
            $num = 0;                                       // piece counter
            for ($i = 1; $i <= 10; $i++)
            {
                if ($itemset['item'.$i] <= 0)
                    continue;

                $num++;
                $equivalents = ItemList::getEquivalentSetPieces($itemset['item'.$i]);
                $pieces[]    = '<span><!--si'.implode(':', $equivalents).'--><a href="?item='.$itemset['item'.$i].'">'.ItemList::getName($itemset['item'.$i]).'</a></span>';
            }

            $xSet = '<br /><span class="q"><a href="?itemset='.$itemset['id'].'" class="q">'.Util::localizedString($itemset, 'name').'</a> (0/'.$num.')</span>';

            if ($itemset['skillId'])                        // bonus requires skill to activate
            {
                $name  = DB::Aowow()->selectRow('SELECT * FROM ?_skillline WHERE Id=?d', $itemset['skillId']);
                $xSet .= '<br />'.sprintf(Lang::$game['requires'], '<a href="?skills='.$itemset['skillId'].'" class="q1">'.Util::localizedString($name, 'name').'</a>');

                if ($itemset['skillLevel'])
                    $xSet .= ' ('.$itemset['skillLevel'].')';

                $xSet .= '<br />';
            }

            // list pieces
            $xSet .= '<div class="q0 indent">'.implode('<br />', $pieces).'</div><br />';

            // get bonuses
            $setSpellsAndIdx = [];
            for ($j = 1; $j <= 8; $j++)
                if ($itemset['spell'.$j] > 0)
                    $setSpellsAndIdx[$itemset['spell'.$j]] = $j;

            // todo: get from static prop?
            if ($setSpellsAndIdx)
            {
                $boni = new SpellList(array(['s.id', array_keys($setSpellsAndIdx)]));
                while ($boni->iterate())
                {
                    $itemset['spells'][] = array(
                        'tooltip' => $boni->parseText('description', $this->curTpl['RequiredLevel']),
                        'entry'   => $itemset['spell'.$setSpellsAndIdx[$boni->id]],
                        'bonus'   => $itemset['bonus'.$setSpellsAndIdx[$boni->id]]
                    );
                }
            }

            // sort and list bonuses
            $xSet .= '<span class="q0">';
            for ($i = 0; $i < count($itemset['spells']); $i++)
            {
                for ($j = $i; $j < count($itemset['spells']); $j++)
                {
                    if($itemset['spells'][$j]['bonus'] >= $itemset['spells'][$i]['bonus'])
                        continue;

                    $tmp = $itemset['spells'][$i];
                    $itemset['spells'][$i] = $itemset['spells'][$j];
                    $itemset['spells'][$j] = $tmp;
                }
                $xSet .= '<span>('.$itemset['spells'][$i]['bonus'].') '.Lang::$item['set'].': <a href="?spell='.$itemset['spells'][$i]['entry'].'">'.$itemset['spells'][$i]['tooltip'].'</a></span>';
                if ($i < count($itemset['spells']) - 1)
                    $xSet .= '<br />';
            }
            $xSet .= '</span>';
        }

        // recipe handling (some stray Techniques have subclass == 0), place at bottom of tooltipp
        if ($this->curTpl['class'] == ITEM_CLASS_RECIPE && ($this->curTpl['subclass'] || $this->curTpl['BagFamily'] == 16))
        {
            $craftSpell   = new SpellList(array(['s.id', (int)$this->curTpl['spellid_2']]));
            $craftItem    = new ItemList(array(['i.entry', (int)$craftSpell->curTpl["effect1CreateItemId"]]));
            $reagentItems = [];

            for ($i = 1; $i <= 8; $i++)
                if ($rId = $craftSpell->getField('reagent'.$i))
                    $reagentItems[$rId] = $craftSpell->getField('reagentCount'.$i);

            $reagents = new ItemList(array(['i.entry', array_keys($reagentItems)]));
            $reqReag  = [];

            $x .= '<span class="q2">'.Lang::$item['trigger'][0].' <a href="?spell='.$this->curTpl['spellid_2'].'">'.Util::localizedString($this->curTpl, 'description').'</a></span><br />';

            $xCraft = '<div><br />'.$craftItem->renderTooltip(null, $interactive).'</div><br />';

            while ($reagents->iterate())
                $reqReag[] = '<a href="?item='.$reagents->id.'">'.$reagents->getField('name', true).'</a> ('.$reagentItems[$reagents->id].')';

            $xCraft .= '<span class="q1">'.Lang::$game['requires2']." ".implode(", ", $reqReag).'</span>';

        }

        // misc (no idea, how to organize the <br /> better)
        $xMisc = [];

        // itemset: pieces and boni
        if (isset($xSet))
            $xMisc[] = $xSet;

        // funny, yellow text at the bottom, omit if we have a recipe
        if ($this->curTpl['description'] && !isset($xCraft))
            $xMisc[] = '<span class="q">"'.Util::localizedString($this->curTpl, 'description').'"</span>';

        // readable
        if ($this->curTpl['PageText'])
            $xMisc[] = '<span class="q2">'.Lang::$item['readClick'].'</span>';

        // charges (i guess checking first spell is enough (single charges not shown))
        if ($this->curTpl['spellcharges_1'] > 1)
            $xMisc[] = '<span class="q1">'.$this->curTpl['spellcharges_1'].' '.Lang::$item['charges'].'</span>';

        if ($this->curTpl['SellPrice'])
            $xMisc[] = '<span class="q1">'.Lang::$item['sellPrice'].Lang::$colon.Util::formatMoney($this->curTpl['SellPrice']).'</span>';

        // list required reagents
        if (isset($xCraft))
            $xMisc[] = $xCraft;

        if ($xMisc)
            $x .= implode('<br />', $xMisc);

        if (!$subT)
            $x .= '</td></tr></table>';

        // heirloom tooltip scaling
        if (isset($this->ssd[$this->id]))
        {
            $link = array(
                $this->id,                                  // itemId
                1,                                          // scaleMinLevel
                $this->ssd[$this->id]['maxLevel'],          // scaleMaxLevel
                $this->ssd[$this->id]['maxLevel'],          // scaleCurLevel
                $this->curTpl['ScalingStatDistribution'],   // scaleDist
                $this->curTpl['ScalingStatValue'],          // scaleFlags
            );
            $x .= '<!--?'.implode(':', $link).'-->';
        }
        else
            $x .= '<!--?'.$this->id.':1:'.MAX_LEVEL.':'.MAX_LEVEL.'-->';

        $this->tooltip[$this->id] = $x;

        return $this->tooltip[$this->id];
    }

    // from Trinity
    public function generateEnchSuffixFactor()
    {
        $rpp = DB::Aowow()->selectRow('SELECT * FROM ?_itemRandomPropPoints WHERE Id = ?', $this->curTpl['ItemLevel']);
        if (!$rpp)
            return 0;

        switch ($this->curTpl['InventoryType'])
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
        switch ($this->curTpl['Quality'])
        {
            case ITEM_QUALITY_UNCOMMON:
                return $rpp['uncommon'.$suffixFactor];
            case ITEM_QUALITY_RARE:
                return $rpp['rare'.$suffixFactor];
            case ITEM_QUALITY_EPIC:
                return $rpp['epic'.$suffixFactor];
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
        // convert ItemMods
        for ($h = 1; $h <= 10; $h++)
        {
            $mod = $this->curTpl['stat_type'.$h];
            $val = $this->curTpl['stat_value'.$h];
            if (!$mod ||!$val)
                continue;

            if ($mod == ITEM_MOD_ATTACK_POWER)
                @$this->itemMods[$this->id][ITEM_MOD_RANGED_ATTACK_POWER] += $val;

            @$this->itemMods[$this->id][$mod] += $val;
        }

        // convert Spells
        $equipSpells = [];
        for ($h = 1; $h <= 5; $h++)
        {
            // only onEquip
            if ($this->curTpl['spelltrigger_'.$h] != 1)
                continue;

            if ($this->curTpl['spellid_'.$h] <= 0)
                continue;

            $equipSpells[] = $this->curTpl['spellid_'.$h];
        }

        if ($equipSpells)
        {
            $eqpSplList = new SpellList(array(['s.id', $equipSpells]));
            $stats      = $eqpSplList->getStatGain();

            foreach ($stats as $stat)
                foreach ($stat as $mId => $qty)
                    @$this->itemMods[$this->id][$mId] += $qty;
        }

        // fetch and add socketbonusstats
        if (@$this->json[$this->id]['socketbonus'] > 0)
            if ($enh = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE Id = ?;', $this->json[$this->id]['socketbonus']))
                $this->json[$this->id]['socketbonusstat'] = Util::parseItemEnchantment($enh);

        // gather random Enchantments
        // todo (high): extremly high sql-load
        if (@$this->json[$this->id]['commondrop'] && isset($this->subItems[$this->id]))
        {
            foreach ($this->subItems[$this->id] as $k => $sI)
            {
                $jsonEquip = [];
                $jsonText  = [];

                for ($i = 1; $i < 6; $i++)
                {
                    if ($sI['enchantId'.$i] <= 0)
                        continue;

                    if (!$this->rndEnchIds[$sI['enchantId'.$i]])
                        continue;

                    $eData = $this->rndEnchIds[$sI['enchantId'.$i]];

                    if ($sI['allocationPct'.$i] > 0)        // RandomSuffix: scaling Enchantment; enchId < 0
                    {
                        $amount     = intVal($sI['allocationPct'.$i] * $this->generateEnchSuffixFactor() / 10000);
                        $jsonEquip  = array_merge($jsonEquip, Util::parseItemEnchantment($eData, $amount));
                        $jsonText[] = str_replace('$i', $amount, Util::localizedString($eData, 'text'));
                    }
                    else                                    // RandomProperty: static Enchantment; enchId > 0
                    {
                        $jsonText[] = Util::localizedString($eData, 'text');
                        $jsonEquip  = array_merge($jsonEquip, Util::parseItemEnchantment($eData));
                    }
                }

                $this->subItems[$this->id][$k] = array(
                    'name'          => Util::localizedString($sI, 'name'),
                    'enchantment'   => implode(', ', $jsonText),
                    'jsonequip'     => $jsonEquip
                );
            }

            $this->json[$this->id]['subitems'] = $this->subItems[$this->id];
        }

        foreach ($this->json[$this->id] as $k => $v)
            if (!isset($v) || $v === "false" || (!in_array($k, ['classs', 'subclass', 'quality']) && $v == "0"))
                unset($this->json[$this->id][$k]);
    }

    private function parseRating($type, $value, $interactive = false)
    {
        // clamp level range
        $ssdLvl = isset($this->ssd[$this->id]) ? $this->ssd[$this->id]['maxLevel'] : 1;
        $level  = min(max($this->curTpl['RequiredLevel'], $ssdLvl), MAX_LEVEL);

        if (!Lang::$item['statType'][$type])                // unknown rating
            return sprintf(Lang::$item['statType'][count(Lang::$item['statType']) - 1], $type, $value);
        else if (in_array($type, Util::$lvlIndepRating))    // level independant Bonus
            return Lang::$item['trigger'][1] . str_replace('%d', '<!--rtg'.$type.'-->'.$value, Lang::$item['statType'][$type]);
        else                                                // rating-Bonuses
        {
            if ($interactive)
                $js = '&nbsp;<small>('.sprintf(Util::$changeLevelString, Util::setRatingLevel($level, $type, $value)).')</a>)</small>';
            else
                $js = "&nbsp;<small>(".Util::setRatingLevel($level, $type, $value).")</small>";

            return Lang::$item['trigger'][1].str_replace('%d', '<!--rtg'.$type.'-->'.$value.$js, Lang::$item['statType'][$type]);
        }
    }

    private function getSSDMod($type)
    {
        $mask = $this->curTpl['ScalingStatValue'];

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

        return $field ? DB::Aowow()->selectCell("SELECT ?# FROM ?_scalingstatvalues WHERE charLevel = ?", $field, $this->ssd[$this->id]['maxLevel']) : 0;
    }

    private function initScalingStats()
    {
        $this->ssd[$this->id] = DB::Aowow()->selectRow("SELECT * FROM ?_scalingstatdistribution WHERE id = ?", $this->curTpl['ScalingStatDistribution']);

        // stats and ratings
        for ($i = 1; $i <= 10; $i++)
        {
            if ($this->ssd[$this->id]['statMod'.$i] <= 0)
            {
                $this->templates[$this->id]['stat_type'.$i]  = 0;
                $this->templates[$this->id]['stat_value'.$i] = 0;
            }
            else
            {
                $this->templates[$this->id]['stat_type'.$i]  = $this->ssd[$this->id]['statMod'.$i];
                $this->templates[$this->id]['stat_value'.$i] = intVal(($this->getSSDMod('stats') * $this->ssd[$this->id]['modifier'.$i]) / 10000);
            }
        }

        // armor: only replace if set
        if ($ssvArmor = $this->getSSDMod('armor'))
            $this->templates[$this->id]['armor'] = $ssvArmor;

        // if set dpsMod in ScalingStatValue use it for min (70% from average), max (130% from average) damage
        if ($extraDPS = $this->getSSDMod('dps'))            // dmg_x2 not used for heirlooms
        {
            $average = $extraDPS * $this->curTpl['delay'] / 1000;
            $this->templates[$this->id]['dmg_min1'] = number_format(0.7 * $average);
            $this->templates[$this->id]['dmg_max1'] = number_format(1.3 * $average);
        }

        // apply Spell Power from ScalingStatValue if set
        if ($spellBonus = $this->getSSDMod('spell'))
        {
            $this->templates[$this->id]['stat_type10']  = ITEM_MOD_SPELL_POWER;
            $this->templates[$this->id]['stat_value10'] = $spellBonus;
        }
    }

    private function initSubItems()
    {
        $randId = $this->curTpl['RandomProperty'] > 0 ? $this->curTpl['RandomProperty'] : $this->curTpl['RandomSuffix'];
        if ($randomIds = DB::Aowow()->selectCol('SELECT ench FROM item_enchantment_template WHERE entry = ?d', $randId))
        {
            if ($this->curTpl['RandomSuffix'] > 0)
                array_walk($randomIds, function($val, $key) use(&$randomIds) {
                    $randomIds[$key] = -$val;
                });

            $this->subItems[$this->id] = DB::Aowow()->select('SELECT *, Id AS ARRAY_KEY FROM ?_itemRandomEnchant WHERE Id IN (?a)', $randomIds);

            // subitems may share enchantmentIds
            foreach ($this->subItems[$this->id] as $sI)
                for ($i = 1; $i < 6; $i++)
                    if (!isset($this->rndEnchIds[$sI['enchantId'.$i]]) && $sI['enchantId'.$i])
                        if ($enchant = DB::Aowow()->selectRow('SELECT *, Id AS ARRAY_KEY FROM ?_itemenchantment WHERE Id = ?d', $sI['enchantId'.$i]))
                            $this->rndEnchIds[$enchant['id']] = $enchant;
        }
    }

    private function initJsonStats()
    {
        $json = array(
            'id'          => $this->id,
            'name'        => $this->getField('name', true),
            'quality'     => ITEM_QUALITY_HEIRLOOM - $this->curTpl['Quality'],
            'icon'        => $this->curTpl['icon'],
            'classs'      => $this->curTpl['class'],
            'subclass'    => $this->curTpl['subclass'],
         // 'subsubclass' => $this->curTpl['subsubclass'],
            'heroic'      => (string)($this->curTpl['Flags'] & 0x8),
            'side'        => Util::sideByRaceMask($this->curTpl['AllowableRace']), // check for FlagsExtra? 0:both; 1: Horde; 2:Alliance
            'slot'        => $this->curTpl['InventoryType'] == 26 ? 15 : $this->curTpl['InventoryType'] == 20 ? 5 : $this->curTpl['InventoryType'],
            'slotbak'     => $this->curTpl['InventoryType'],
            'level'       => $this->curTpl['ItemLevel'],
            'reqlevel'    => $this->curTpl['RequiredLevel'],
            'displayid'   => $this->curTpl['displayid'],
            'commondrop'  => ($this->curTpl['RandomProperty'] > 0 || $this->curTpl['RandomSuffix'] > 0) ? 'true' : null, // string required :(
            'holres'      => $this->curTpl['holy_res'],
            'firres'      => $this->curTpl['fire_res'],
            'natres'      => $this->curTpl['nature_res'],
            'frores'      => $this->curTpl['frost_res'],
            'shares'      => $this->curTpl['shadow_res'],
            'arcres'      => $this->curTpl['arcane_res'],
            'armorbonus'  => $this->curTpl['ArmorDamageModifier'],
            'armor'       => $this->curTpl['armor'],
            'dura'        => $this->curTpl['MaxDurability'],
            'itemset'     => $this->curTpl['itemset'],
            'socket1'     => $this->curTpl['socketColor_1'],
            'socket2'     => $this->curTpl['socketColor_2'],
            'socket3'     => $this->curTpl['socketColor_3'],
            'nsockets'    => ($this->curTpl['socketColor_1'] > 0 ? 1 : 0) + ($this->curTpl['socketColor_2'] > 0 ? 1 : 0) + ($this->curTpl['socketColor_3'] > 0 ? 1 : 0),
            'socketbonus' => $this->curTpl['socketBonus'],
            'scadist'     => $this->curTpl['ScalingStatDistribution'],
            'scaflags'    => $this->curTpl['ScalingStatValue']
        );

        if ($this->curTpl['class'] == ITEM_CLASS_WEAPON || $this->curTpl['class'] == ITEM_CLASS_AMMUNITION)
        {

            $json['dmgtype1'] = $this->curTpl['dmg_type1'];
            $json['dmgmin1']  = $this->curTpl['dmg_min1'] + $this->curTpl['dmg_min2'];
            $json['dmgmax1']  = $this->curTpl['dmg_max1'] + $this->curTpl['dmg_max2'];
            $json['dps']      = !$this->curTpl['delay'] ? 0 : number_format(($json['dmgmin1'] + $json['dmgmax1']) / (2 * $this->curTpl['delay'] / 1000), 1);
            $json['speed']    = number_format($this->curTpl['delay'] / 1000, 2);

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

            if ($json['classs'] == ITEM_CLASS_WEAPON && in_array($json['subclass'], [5, 6, 10]) && $json['dps'] > 54.8)
                $json['feratkpwr'] = max(0, round((($json['dmgmin1'] + $json['dmgmax1']) / (2 * $this->curTpl['delay'] / 1000) - 54.8) * 14, 0));
        }

        // clear zero-values afterwards
        foreach ($json as $k => $v)
            if (!isset($v) || $v === "false" || (!in_array($k, ['classs', 'subclass', 'quality']) && $v == "0"))
                unset($json[$k]);

        $this->json[$json['id']] = $json;
    }

    public function addRewardsToJScript(&$ref) { }
}


/*
teaches
    $teaches = array();
    for($j=1;$j<=4;$j++)
        if($Row['spellid_'.$j]==483)
            $teaches[] = spellinfo($Row['spellid_'.($j+1)]);
    if($teaches)
    {
        $item['teaches'] = $teaches;
        unset($teaches);
        unset($spellrow);
    }

unlocks
    $locks_row = $DB->selectCol('
        SELECT lockID
        FROM ?_lock
        WHERE
            (type1=1 AND lockproperties1=?d) OR
            (type2=1 AND lockproperties2=?d) OR
            (type3=1 AND lockproperties3=?d) OR
            (type4=1 AND lockproperties4=?d) OR
            (type5=1 AND lockproperties5=?d)
        ',
        $item['entry'], $item['entry'], $item['entry'], $item['entry'], $item['entry']
    );
    if($locks_row)
    {
        // ??????? ??????? ? ????? ????? ?????:
        $item['unlocks'] = $DB->select('
            SELECT ?#
            FROM gameobject_template
            WHERE
                (
                    ((type IN (?a)) AND (data0 IN (?a)))
                OR
                    ((type IN (?a)) AND (data0 IN (?a)))
                )
            ',
            $object_cols[0],
            array(GAMEOBJECT_TYPE_QUESTGIVER, GAMEOBJECT_TYPE_CHEST, GAMEOBJECT_TYPE_TRAP, GAMEOBJECT_TYPE_GOOBER, GAMEOBJECT_TYPE_CAMERA, GAMEOBJECT_TYPE_FLAGSTAND, GAMEOBJECT_TYPE_FLAGDROP),
            $locks_row,
            array(GAMEOBJECT_TYPE_DOOR, GAMEOBJECT_TYPE_BUTTON),
            $locks_row
        );
        if(!$item['unlocks'])
            unset($item['unlocks']);
*/

?>
