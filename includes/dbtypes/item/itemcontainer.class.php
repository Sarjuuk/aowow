<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemContainer extends DBTypeContainer
{
    use TrVendorHelper;

    public static int $dbType = Type::ITEM;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, ItemEntry> id => item entry
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?ItemEntry
     */
    public function getEntry(null|string|int $key = null) : ?ItemEntry
    {
        return parent::getEntry($key);
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
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF, array &$extra = []) : array
    {
        $data = [];

        foreach ($this->iterate() as $entry)
            Util::mergeJsGlobals($data, $entry->getJSGlobal($addMask, $extra));

        // from TrVendorHelper
        if ($addMask & GLOBALINFO_RELATED)
            Util::mergeJsGlobals($data, $this->jsGlobals);

        return $data;
    }

    /**
     * @param int $addInfoMask
     * * `0x0001 - LISTVIEWINFO_ITEMEXTRA`: jsonStats (including spells) and subitems parsed
     * * `0x0002 - LISTVIEWINFO_SUBITEMS`: searched by comparison
     * * `0x0004 - LISTVIEWINFO_VENDOR`: costs-obj, when displayed as vendor
     * * `0x0008 - LISTVIEWINFO_GEMS`: gem infos and score
     * * `0x0010 - LISTVIEWINFO_MODEL`: sameModelAs-Tab
     */
    public function getListviewData(int $addInfoMask = 0x0, ?array $miscData = null) : array
    {
        // bulk source+model prep
        $idBuff = $smBuff = $data = $subItemIds = $eIds = $eStats = [];
        foreach ($this->iterate() as $id => $entry)
        {
            if ($entry->moreType && $entry->moreTypeId)
                $idBuff[$entry->moreType][] = $entry->moreTypeId;

            if ($addInfoMask & LISTVIEWINFO_SUBITEMS)
                if ($_ = $entry->randomEnchant)
                    $subItemIds[] = $_;

            if ($addInfoMask & LISTVIEWINFO_ITEMEXTRA)
            {
                if ($_ = $entry->socketBonus)
                    $eIds[] = $_;
                if ($_ = $entry->gemEnchantmentId)
                    $eIds[] = $_;
            }
        }

        foreach ($idBuff as $type => $ids)
        {
            if (!($class = Type::getClassName($type)))
                continue;

            if (!is_a($class, ISource::class, true))
                continue;

            $smBuff[$type] = $class::getSourceMore(...$ids);
        }

        $templates = $randEnchants = $enchEntries = null;
        if ($addInfoMask & LISTVIEWINFO_SUBITEMS)
            [$templates, $randEnchants, $enchEntries] = ItemEntry::fetchSubItemEnchantments(...$subItemIds);

        if ($addInfoMask & LISTVIEWINFO_VENDOR)
            $this->getVendorData($this->getFoundIds());

        if ($addInfoMask & LISTVIEWINFO_ITEMEXTRA)
            $eStats = ItemEntry::fetchEnchantmentStats(...$eIds);

        foreach ($this->iterate() as $id => $entry)
        {
            if ($addInfoMask & LISTVIEWINFO_ITEMEXTRA)
                $entry->extendJsonStats($eStats);

            // random item is random
            if ($addInfoMask & LISTVIEWINFO_SUBITEMS)
                $entry->initSubItems($templates, $randEnchants, $enchEntries);

            // Sources
            if ($entry->moreType && $entry->moreTypeId)
                $entry->prepareSourceMore($smBuff[$entry->moreType][$entry->moreTypeId] ?? []);

            // Vendors
            if ($v = ($this->vendors[$id] ?? null))
                $entry->setVendors([$id => $v]);

            $data[$id] = $entry->getListviewRow($addInfoMask, $miscData);
        }

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
}

?>
