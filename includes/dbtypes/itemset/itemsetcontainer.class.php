<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetContainer extends DBTypeContainer
{
    public static int $dbType = Type::ITEMSET;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, ItemsetEntry> id => itemset template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?ItemsetEntry
     */
    public function getEntry(null|string|int $key = null) : ?ItemsetEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add itemset and contained items to @return
     *  - GLOBALINFO_RELATED - add required classes to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF) : array
    {
        return parent::getJSGlobals($addMask);
    }
}

?>
