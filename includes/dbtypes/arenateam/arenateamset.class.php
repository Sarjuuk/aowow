<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenaTeamSet extends DBTypeSet implements IListview
{
    public static int $dbType = Type::ARENA_TEAM;

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, ArenaTeam> key => arena team template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?ArenaTeam
     */
    public function getEntry(string|int $id) : ?ArenaTeam
    {
        return parent::getEntry($id);
    }
}

?>
