<?php

namespace Aowow;

use Override;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemContainer extends DBTypeContainer
{
    public static int $dbType = Type::ITEM;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, Item> id => item entry
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Item
     */
    public function getEntry(string|int $id) : ?Item
    {
        return parent::getEntry($id);
    }

    /**
     * @param int $addInfoMask
     * * `0x0001 - LISTVIEWINFO_ITEMEXTRA`: jsonStats (including spells) and subitems parsed
     * * `0x0002 - LISTVIEWINFO_SUBITEMS`: searched by comparison
     * * `0x0004 - LISTVIEWINFO_VENDOR`: costs-obj, when displayed as vendor
     * * `0x0008 - LISTVIEWINFO_GEMS`: gem infos and score
     * * `0x0010 - LISTVIEWINFO_MODEL`: sameModelAs-Tab
     */
    public function getListviewData(int $addInfoMask = 0x0): array
    {
        // random item is random
        if ($addInfoMask & LISTVIEWINFO_SUBITEMS)
            $this->initSubItems();

        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getListviewRow($addInfoMask);

        return $data;
    }


    /**
     * assumes that the ItemContainer .. well .. contains only the items currently equipped on the character
     *
     * @param  int   $class  character class id
     * @param  array $spec   talent distribution
     * @param  int   $mhItem mainhand item id
     * @param  int   $ohItem offhand item id
     * @return int           total gearscore value respective to a player class/spec
     */
    public function calcGearscoreTotal(int $class = 0, array $spec = [], int $mhItem = 0, int $ohItem = 0) : int
    {
        $score    = 0.0;
        $mh = $oh = [];

        foreach ($this->iterate() as $id => $entry)
        {
            if ($id == $mhItem && $class && $spec)
            {
                $mh = $entry->json;
                continue;
            }

            if ($id == $ohItem && $class && $spec)
            {
                $oh = $entry->json;
                continue;
            }

            if ($entry->slot == INVTYPE_RELIC)
                $score += 20;
            else
                $score += $entry->json['gearscore'] ?? 0;
        }

        if ($class && $spec)
            $score += array_sum(Util::fixWeaponScores($class, $spec, $mh, $oh));

        return round($score);
    }

    public function extendJsonStats() : void
    {
        $enchantments = [];                                 // buffer Ids for lookup id => src; src>0: socketBonus; src<0: gemEnchant

        foreach ($this->iterate() as $__)
        {
            // fetch and add socketbonusstats
            if (!empty($this->json[$this->id]['socketbonus']))
                $enchantments[$this->json[$this->id]['socketbonus']][] = $this->id;

            // Item is a gem (don't confuse with sockets)
            if ($geId = $this->curTpl['gemEnchantmentId'])
                $enchantments[$geId][] = -$this->id;
        }

        if ($enchantments)
        {
            $eStats = DB::Aowow()->selectAssoc('SELECT *, `typeId` AS ARRAY_KEY FROM ::item_stats WHERE `type` = %i AND `typeId` IN %in', Type::ENCHANTMENT, array_keys($enchantments));

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
    }

    // todo (med): information will get lost if one vendor sells one item multiple times with different costs (e.g. for item 54637)
    //             wowhead seems to have had the same issue
    public function getExtendedCost(?array $filter = [], int $targetItem = 0, ?array &$reqRating = null) : array
    {
        if ($this->error)
            return [];

        // apply filter if given
        $tok = $filter[Type::ITEM]     ?? null;
        $cur = $filter[Type::CURRENCY] ?? null;
        $res = [];

        // still needed..?
        // $idx = $this->id;

        foreach ($this->getVendorData($targetItem) as $itemId => $vendors)
        {
            foreach ($vendors as $npcId => $costEntries)
            {
                foreach ($costEntries as $cost)
                {
                    // bought with specific token or currency
                    if ($tok && empty($cost[$tok]))
                        continue;
                    if ($cur && empty($cost[-$cur]))
                        continue;

                    // use lowest total value for arena rating
                    if ($cost['reqRating'])
                        if (!$reqRating || $reqRating[0] > $cost['reqRating'])
                            $reqRating = [$cost['reqRating'], $cost['reqBracket']];

                    $res[$itemId][$npcId] ??= [];
                    $res[$itemId][$npcId] = $cost;
                }
            }
        }

        // restore internal index;
        // $this->getEntry($idx);

        return $res;
    }

    public function getVendorData(int $targetItem = 0) : array
    {
        if (!is_null($this->vendors))
            return $targetItem ? array_filter($this->vendors, fn($x) => $x == $targetItem, ARRAY_FILTER_USE_KEY) : $this->vendors;

        $itemz      = [];
        $xCostData  = [];
        $rawEntries = DB::World()->selectAssoc(
           'SELECT  nv.`item`,            nv.`entry`,              0  AS "eventId",    nv.`maxcount`,   nv.`extendedCost`,   nv.`incrtime`
            FROM    npc_vendor nv
            WHERE   nv.`item` IN %in
                UNION
            SELECT  nv2.`item`,          nv1.`entry`,              0  AS "eventId",   nv2.`maxcount`,  nv2.`extendedCost`,  nv2.`incrtime`
            FROM    npc_vendor   nv1
            JOIN    npc_vendor   nv2 ON -nv1.`item` = nv2.`entry`
            WHERE   nv2.`item` IN %in
                UNION
            SELECT    genv.`item`, c.`id` AS "entry", ge.`eventEntry` AS "eventId",  genv.`maxcount`, genv.`extendedCost`, genv.`incrtime`
            FROM      game_event_npc_vendor genv
            LEFT JOIN game_event ge ON genv.`eventEntry` = ge.`eventEntry`
            JOIN      creature c ON c.`guid` = genv.`guid`
            WHERE     genv.`item` IN %in',
            $this->getFoundIds(), $this->getFoundIds(), $this->getFoundIds()
        );

        if ($xCostIds = array_filter(array_column($rawEntries, 'extendedCost')))
            $xCostData = DB::Aowow()->selectAssoc('SELECT *, `id` AS ARRAY_KEY FROM ::itemextendedcost WHERE `id` IN %in', $xCostIds);

        if (array_filter(array_column($xCostData, 'reqArenaPoints')))
            $this->jsGlobals[Type::CURRENCY][CURRENCY_ARENA_POINTS] = CURRENCY_ARENA_POINTS;
        if (array_filter(array_column($xCostData, 'reqHonorPoints')))
            $this->jsGlobals[Type::CURRENCY][CURRENCY_HONOR_POINTS] = CURRENCY_HONOR_POINTS;

        $xCostItemIds = [];
        for ($i = 1; $i < 6; $i++)
            $xCostItemIds = array_merge(array_column($xCostData, 'reqItemId'.$i));

        foreach ($rawEntries as $vendor)
        {
            $xCost = $xCostData[$vendor['extendedCost']] ?? [];
            $data = array(
                'stock'      => $vendor['maxcount'] ?: -1,
                'event'      => $vendor['eventId'],
                'restock'    => $vendor['incrtime'],
                'reqRating'  => $xCost['reqPersonalRating'] ?? 0,
                'reqBracket' => $xCost['reqArenaSlot']      ?? 0
            );

            // hardcode arena) & honor
            if (!empty($xCost['reqArenaPoints']))
                $data[-CURRENCY_ARENA_POINTS] = $xCost['reqArenaPoints'];

            if (!empty($xCost['reqHonorPoints']))
                $data[-CURRENCY_HONOR_POINTS] = $xCost['reqHonorPoints'];

            for ($i = 1; $i < 6; $i++)
                if ($xCost && $xCost['reqItemId'.$i] > 0 && $xCost['itemCount'.$i] > 0)
                    $data[$xCost['reqItemId'.$i]] = $xCost['itemCount'.$i];

            $entry = $this->getEntry($vendor['item']);

            // no extended cost or additional gold required
            if (!$xCost || $entry->flagsExtra & 0x04)
                if ($_ = $entry->buyPrice)
                    $data[0] = $_;

            $itemz[$vendor['item']][$vendor['entry']] ??= [];
            $itemz[$vendor['item']][$vendor['entry']][] = $data;
        }

        // convert items to currency if possible
        if ($xCostItemIds)
        {
            $moneyItems = new CurrencyContainer(array(['itemId', $xCostItemIds]));
            Util::mergeJsGlobals($this->jsGlobals, $moneyItems->getJSGlobals());

            foreach ($itemz as &$vendors)
            {
                foreach ($vendors as &$costData)
                {
                    foreach ($costData as &$cost)
                    {
                        foreach ($cost as $k => $v)
                        {
                            // skip gold cost and string keys
                            if (!in_array($k, $xCostItemIds))
                                continue;

                            if ($currency = $moneyItems->getEntry($k))
                            {
                                unset($cost[$k]);
                                $cost[-$currency->id] = $v;
                            }
                            else
                                $this->jsGlobals[Type::ITEM][$k] = $k;
                        }
                    }
                }
            }
            unset($vendors, $costData, $cost);
        }

        $this->vendors = $itemz;

        return $onlySelf ? array_filter($this->vendors, fn($x) => $x == $this->id, ARRAY_FILTER_USE_KEY) : $this->vendors;
    }

    private function initSubItems() : void
    {
        $subItemIds = [];
        foreach ($this->iterate() as $entry)
            if ($_ = $entry->randomEnchant)
                $subItemIds[abs($_)] = $_;

        if (!$subItemIds)
            return;

        // remember: id < 0: randomSuffix; id > 0: randomProperty
        $subItemTpls = DB::World()->selectAssoc(
           'SELECT CAST( `entry` AS SIGNED) AS ARRAY_KEY, CAST( `ench` AS SIGNED) AS ARRAY_KEY2, `chance` FROM item_enchantment_template WHERE `entry` IN %in UNION
            SELECT CAST(-`entry` AS SIGNED) AS ARRAY_KEY, CAST(-`ench` AS SIGNED) AS ARRAY_KEY2, `chance` FROM item_enchantment_template WHERE `entry` IN %in',
            array_keys(array_filter($subItemIds, fn($v) => $v > 0)) ?: [0],
            array_keys(array_filter($subItemIds, fn($v) => $v < 0)) ?: [0]
        );

        $randIds = [];
        foreach ($subItemTpls as $tpl)
            $randIds = array_merge($randIds, array_keys($tpl));

        if (!$randIds)
            return;

        $randEnchants = DB::Aowow()->selectAssoc('SELECT *, `id` AS ARRAY_KEY FROM ::itemrandomenchant WHERE `id` IN %in', $randIds);
        $enchIds = array_unique(array_merge(
            array_column($randEnchants, 'enchantId1'),
            array_column($randEnchants, 'enchantId2'),
            array_column($randEnchants, 'enchantId3'),
            array_column($randEnchants, 'enchantId4'),
            array_column($randEnchants, 'enchantId5')
        ));

        $enchants = new EnchantmentContainer(array(['id', $enchIds]));
        foreach ($enchants->iterate() as $eId => $entry)
        {
            $this->subItemEnchants[$eId] = array(
                'text'  => $entry->name,
                'stats' => $entry->getStatGain()
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
                    if ($enchId <= 0 || empty($this->subItemEnchants[$enchId]))
                        continue;

                    if ($data['allocationPct'.$i] > 0)      // RandomSuffix: scaling Enchantment; enchId < 0
                    {
                        $qty   = intVal($data['allocationPct'.$i] * $this->generateEnchSuffixFactor());
                        $stats = array_fill_keys(array_keys($this->subItemEnchants[$enchId]['stats']), $qty);

                        $jsonText[$enchId] = str_replace('$i', $qty, $this->subItemEnchants[$enchId]['text']);
                        Util::arraySumByKey($jsonEquip, $stats);
                    }
                    else                                    // RandomProperty: static Enchantment; enchId > 0
                    {
                        $jsonText[$enchId] = $this->subItemEnchants[$enchId]['text'];
                        Util::arraySumByKey($jsonEquip, $this->subItemEnchants[$enchId]['stats']);
                    }
                }

                $this->subItems[$mstItem][$subId] = array(
                    'name'          => Util::localizedString($data, 'name'),
                    'enchantment'   => $jsonText,
                    'jsonequip'     => $jsonEquip,
                    'chance'        => $data['chance']      // hmm, only needed for item detail page...
                );
            }

            $this->json[$mstItem]['subitems'] = $this->subItems[$mstItem] ?? null;
        }
    }
}

?>
