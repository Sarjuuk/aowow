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
    public function getEntry(?int $key = null) : ?PetEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add pet to @return
     *  - GLOBALINFO_RELATED - add pet spells to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF) : array
    {
        return parent::getJSGlobals($addMask);
    }
}
?>
