<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharRaceContainer extends DBTypeContainer
{
    public static int $dbType = Type::CHR_RACE;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, CharRaceEntry> id => character race template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?CharRaceEntry
     */
    public function getEntry(null|string|int $key = null) : ?CharRaceEntry
    {
        return parent::getEntry($key);
    }
}

?>
