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
     * @return \Generator<int, Itemset> id => itemset template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Itemset
     */
    public function getEntry(string|int $id) : ?Itemset
    {
        return parent::getEntry($id);
    }
}

?>
