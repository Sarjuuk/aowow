<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameObjectList extends BaseType
{
    public static   $type      = TYPE_OBJECT;

    protected       $queryBase = 'SELECT *, go.entry AS ARRAY_KEY FROM gameobject_template go';
    protected       $queryOpts = array(
                        'go' => [['lg']],
                        'lg' => ['j' => ['locales_gameobject lq ON go.entry = lq.entry', true]]
                    );

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
                gameobject_template gt
            LEFT JOIN
                locales_gameobject lg
            ON
                lg.entry = gt.entry
            WHERE
                gt.entry = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData() { }
    public function addGlobalsToJScript(&$template, $addMask = 0) { }
    public function renderTooltip() { }

}

?>
