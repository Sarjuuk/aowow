<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'currencies';

    protected $tblDependencyAowow = ['icons'];
    protected $tblDependencyTC    = ['item_template', 'item_template_locale'];
    protected $dbcSourceFiles     = ['itemdisplayinfo', 'currencytypes'];

    public function generate(array $ids = []) : bool
    {
        if (!$ids)
            DB::Aowow()->query('REPLACE INTO ?_currencies (`id`, `category`, `itemId`) SELECT `id`, LEAST(`category`, 41), `itemId` FROM dbc_currencytypes');

        $moneyItems = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `itemId` FROM dbc_currencytypes{ WHERE `id` IN (?a)}', $ids ?: DBSIMPLE_SKIP);

        // apply names & cap
        $moneyNames = DB::World()->select('
            SELECT
                it.entry AS ARRAY_KEY,
                it.name AS name_loc0, IFNULL(itl2.Name, "") AS name_loc2, IFNULL(itl3.Name, "") AS name_loc3, IFNULL(itl4.Name, "") AS name_loc4, IFNULL(itl6.Name, "") AS name_loc6, IFNULL(itl8.Name, "") AS name_loc8,
                it.maxCount AS cap
            FROM
                item_template it
            LEFT JOIN
                item_template_locale itl2 ON it.entry = itl2.ID AND itl2.locale = "frFR"
            LEFT JOIN
                item_template_locale itl3 ON it.entry = itl3.ID AND itl3.locale = "deDE"
            LEFT JOIN
                item_template_locale itl4 ON it.entry = itl4.ID AND itl4.locale = "zhCN"
            LEFT JOIN
                item_template_locale itl6 ON it.entry = itl6.ID AND itl6.locale = "esES"
            LEFT JOIN
                item_template_locale itl8 ON it.entry = itl8.ID AND itl8.locale = "ruRU"
            WHERE
                it.entry IN (?a)',
            $moneyItems);

        foreach ($moneyItems as $cId => $itemId)
        {
            if (!empty($moneyNames[$itemId]))
                $strings = $moneyNames[$itemId];
            else
            {
                CLI::write('item #'.$itemId.' referenced by currency #'.$cId.' not in item_template', CLI::LOG_WARN);
                $strings = ['name_loc0' => 'Item #'.$itemId.' not in DB', 'iconId' => 0, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3];
            }

            DB::Aowow()->query('UPDATE ?_currencies SET ?a WHERE itemId = ?d', $strings, $itemId);
        }

        // apply icons
        $displayIds  = DB::World()->selectCol('SELECT entry AS ARRAY_KEY, displayid FROM item_template WHERE entry IN (?a)', $moneyItems);
        foreach ($displayIds as $itemId => $iconId)
            DB::Aowow()->query('
                UPDATE
                    ?_currencies c,
                    ?_icons i,
                    dbc_itemdisplayinfo idi
                SET
                    c.iconId = i.id
                WHERE
                    i.name = LOWER(idi.inventoryIcon1) AND
                    idi.id = ?d AND
                    c.itemId = ?d
            ', $iconId, $itemId);

        $this->reapplyCCFlags('currencies', Type::CURRENCY);

        return true;
    }
});

?>
