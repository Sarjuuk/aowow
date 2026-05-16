<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencySet extends DBTypeSet
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
}

?>
