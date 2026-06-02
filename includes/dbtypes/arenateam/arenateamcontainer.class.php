<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenateamContainer extends DBTypeContainer implements IListview
{
    public static int $dbType = Type::ARENA_TEAM;

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, ArenateamEntry> key => arena team template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?ArenateamEntry
     */
    public function getEntry(null|string|int $key = null) : ?ArenateamEntry
    {
        return parent::getEntry($key);
    }
}

?>
