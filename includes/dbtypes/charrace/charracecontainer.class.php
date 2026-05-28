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
     * @return \Generator<int, CharRace> id => character race template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?CharRace
     */
    public function getEntry(string|int $id) : ?CharRace
    {
        return parent::getEntry($id);
    }
}

?>
