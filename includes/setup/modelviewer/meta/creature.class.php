<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * uses:
 */

class Creature extends MetaJSON
{
    protected string $path = 'creature';

    public function appendData(mixed $modelPath) : void
    {
    }
}

?>
