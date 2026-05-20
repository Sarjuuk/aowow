<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharClassSet extends DBTypeSet
{
    public static int $dbType = Type::CHR_CLASS;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, CharClass> id => character class template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?CharClass
     */
    public function getEntry(string|int $id) : ?CharClass
    {
        return parent::getEntry($id);
    }
}

?>
