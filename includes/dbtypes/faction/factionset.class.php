<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionSet extends DBTypeSet
{
    public static int $dbType = Type::FACTION;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, Faction> id => faction template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }
}

?>
