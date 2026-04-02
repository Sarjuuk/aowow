<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharClassContainer extends DBTypeContainer
{
    public static int $dbType = Type::CHR_CLASS;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, CharClassEntry> id => character class template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?CharClassEntry
     */
    public function getEntry(null|string|int $key = null) : ?CharClassEntry
    {
        return parent::getEntry($key);
    }
}

?>
