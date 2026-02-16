<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * uses:
 * 'Model' -> GameObjectDisplayInfo.db2/FileDataId // is path/string in 335a
 */

class GameObject extends MetaJSON
{
    protected string $path = 'object';

    public function appendData(mixed $modelPath) : void
    {
        $this->Model = $modelPath;
    }
}

?>
