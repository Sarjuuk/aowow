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
     * @return \Generator<int, Emote> id => emote template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Emote
     */
    public function getEntry(string|int $id) : ?Emote
    {
        return parent::getEntry($id);
    }
}

?>
