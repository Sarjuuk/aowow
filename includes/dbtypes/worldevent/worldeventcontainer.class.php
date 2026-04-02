<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class WorldeventContainer extends DBTypeContainer
{
    public static int $dbType = Type::WORLDEVENT;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, WorldeventEntry> id => event template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?WorldeventEntry
     */
    public function getEntry(null|string|int $key = null) : ?WorldeventEntry
    {
        return parent::getEntry($key);
    }
}

?>
