<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilePurgeResponse extends TextResponse
{
    protected bool $requiresLogin = true;

    /*  params
            id: <prId>
            data: <mode>                [string, tabName]
        return
            null
    */
    protected function generate() : void { }                // removes completion data (as uploaded by the wowhead client) Just fail silently if someone triggers this manually
}

?>
