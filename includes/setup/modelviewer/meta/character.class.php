<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * uses:
 */

class Character extends MetaJSON
{
    protected string $path = 'character';

    public function appendData(mixed $modelPath) : void
    {
        $this->Character = array(
            "RaceFlags"     => 4,
            "ChrModelFlags" => 0,
            "Race"          => 4,
            "Gender"        => 0,
            "ChrModelId"    => 7
        );
    }
}

?>
