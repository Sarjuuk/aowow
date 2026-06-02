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
}

?>
