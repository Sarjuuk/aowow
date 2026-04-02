<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionContainer extends DBTypeContainer
{
    public static int $dbType = Type::FACTION;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, FactionEntry> id => faction template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?FactionEntry
     */
    public function getEntry(null|string|int $key = null) : ?FactionEntry
    {
        return parent::getEntry($key);
    }
}

?>
