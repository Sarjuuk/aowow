<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class WorldEvent extends BaseType
{

    public static function getName($id)
    {
        $row = DB::Aowow()->SelectRow('SELECT * FROM ?_holidays WHERE Id = ?d', intVal($id));

        return Util::localizedString($row, 'name');
    }
}


?>