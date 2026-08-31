<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MailContainer extends DBTypeContainer
{
    public static int $dbType = Type::MAIL;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, MailEntry> id => mail template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?MailEntry
     */
    public function getEntry(?int $key = null) : ?MailEntry
    {
        return parent::getEntry($key);
    }
}

?>
