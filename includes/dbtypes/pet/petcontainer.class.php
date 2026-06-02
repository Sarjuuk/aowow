<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PetContainer extends DBTypeContainer
{
    public static int $dbType = Type::PET;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, PetEntry> id => pet template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?PetEntry
     */
    public function getEntry(null|string|int $key = null) : ?PetEntry
    {
        return parent::getEntry($key);
    }
}

?>
