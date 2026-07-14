<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteSet extends DBTypeSet
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
}

?>
