<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyContainer extends DBTypeContainer
{
    public static int $dbType = Type::CURRENCY;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, CurrencyEntry> id => currency template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?CurrencyEntry
     */
    public function getEntry(null|string|int $key = null) : ?CurrencyEntry
    {
        return parent::getEntry($key);
    }
}

?>
