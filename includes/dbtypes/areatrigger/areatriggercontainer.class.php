<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreatriggerContainer extends DBTypeContainer
{
    public static int $dbType = Type::AREATRIGGER;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, AreatriggerEntry> id => areatrigger template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?AreatriggerEntry
     */
    public function getEntry(null|string|int $key = null) : ?AreatriggerEntry
    {
        return parent::getEntry($key);
    }
}

?>
