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
     * @return \Generator<int, Mail> id => mail template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Mail
     */
    public function getEntry(string|int $id) : ?Mail
    {
        return parent::getEntry($id);
    }
}

?>
