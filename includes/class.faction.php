<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Faction
{
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name_loc0,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                ?_factions
            WHERE
                factionID = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function reactsAgainst($faction)
    {
        // see factionTemplate
        /*
            1: friendly
            0: neutral
            -1: hostile
        */
    }
    
}

?>
