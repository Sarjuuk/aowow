<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends BaseType
{
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                creature_template ct
            LEFT JOIN
                locales_creature lc
            ON
                lc.entry = ct.entry
            WHERE
                ct.entry = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData() { }
    public function addGlobalsToJScript(&$refs) { }
    public function addRewardsToJScript(&$refs) { }
    public function renderTooltip() { }

}

?>
