<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * item_template
 * locales_item
*/

// hide test tokens and move them to unused
$customData = array(
      1 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3],
      2 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3],
      4 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3],
     22 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3],
    141 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3],
    103 => ['cap' => 10000],                                // Arena Points
    104 => ['cap' => 75000]                                 // Honor Points
);
$reqDBC = ['itemdisplayinfo', 'currencytypes'];

function currencies(array $ids = [])
{
    if (!$ids)
        DB::Aowow()->query('REPLACE INTO ?_currencies (id, category, itemId) SELECT Id, category, itemId FROM dbc_currencytypes');

    $moneyItems = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, itemId FROM dbc_currencytypes{ WHERE id IN (?a)}', $ids ?: DBSIMPLE_SKIP);

    // apply names & cap
    $moneyNames = DB::World()->select('SELECT it.entry AS ARRAY_KEY, name AS name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, it.maxCount AS cap FROM item_template it LEFT JOIN locales_item li ON li.entry = it.entry WHERE it.entry IN (?a)', $moneyItems);
    foreach ($moneyItems as $cId => $itemId)
    {
        if (!empty($moneyNames[$itemId]))
            $strings = $moneyNames[$itemId];
        else
        {
            CLISetup::log('item #'.$itemId.' required by currency #'.$cId.' not in item_template', CLISetup::LOG_WARN);
            $strings = ['name_loc0' => 'Item #'.$itemId.' not in DB', 'iconId' => -1240, 'cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW, 'category' => 3];
        }

        DB::Aowow()->query('UPDATE ?_currencies SET ?a WHERE itemId = ?d', $strings, $itemId);
    }

    // apply icons
    $displayIds  = DB::World()->selectCol('SELECT entry AS ARRAY_KEY, displayid FROM item_template WHERE entry IN (?a)', $moneyItems);
    foreach ($displayIds as $itemId => $iconId)
        DB::Aowow()->query('UPDATE ?_currencies SET iconId = ?d WHERE itemId = ?d', -$iconId, $itemId);

    return true;
}

?>
