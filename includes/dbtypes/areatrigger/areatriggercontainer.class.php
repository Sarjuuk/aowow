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
     * @return \Generator<int, Areatrigger> id => areatrigger template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Areatrigger
     */
    public function getEntry(string|int $id) : ?Areatrigger
    {
        return parent::getEntry($id);
    }
}

?>
