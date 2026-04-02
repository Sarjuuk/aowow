<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UserContainer extends DBTypeContainer
{
    public static int $dbType = Type::USER;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, UserEntry> id => aowow user
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?UserEntry
     */
    public function getEntry(null|string|int $key = null) : ?UserEntry
    {
        return parent::getEntry($key);
    }
}

?>
