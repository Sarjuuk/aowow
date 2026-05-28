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
     * @return \Generator<int, Currency> id => currency template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Currency
     */
    public function getEntry(string|int $id) : ?Currency
    {
        return parent::getEntry($id);
    }
}

?>
