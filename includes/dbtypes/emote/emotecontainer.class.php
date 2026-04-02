<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteContainer extends DBTypeContainer
{
    public static int $dbType = Type::EMOTE;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, EmoteEntry> id => emote template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?EmoteEntry
     */
    public function getEntry(null|string|int $key = null) : ?EmoteEntry
    {
        return parent::getEntry($key);
    }
}

?>
