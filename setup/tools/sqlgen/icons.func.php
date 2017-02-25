<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


$customData = array(
);
$reqDBC = ['spellicon', 'itemdisplayinfo'];

function icons()
{
    $baseQuery = '
        REPLACE INTO
            ?_icons
                SELECT Id, LOWER(SUBSTRING_INDEX(iconPath, "\\\\", -1)) FROM dbc_spellicon
            UNION
                SELECT -Id, LOWER(inventoryIcon1) FROM dbc_itemdisplayinfo';

    DB::Aowow()->query($baseQuery);

    return true;
}

?>
