<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ZoneContainer extends DBTypeContainer
{
    public static int $dbType = Type::ZONE;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, ZoneEntry> id => zone template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?ZoneEntry
     */
    public function getEntry(null|string|int $key = null) : ?ZoneEntry
    {
        return parent::getEntry($key);
    }
}

?>
