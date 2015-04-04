<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * spelldifficulty_dbc
*/

$customData = array(
);
$reqDBC = ['spelldifficulty'];

function spelldifficulty(array $ids = [])
{
    // has no unique keys..
    DB::Aowow()->query('TRUNCATE TABLE ?_spelldifficulty');

    DB::Aowow()->query('INSERT INTO ?_spelldifficulty SELECT * FROM dbc_spelldifficulty');

    $rows = DB::World()->select('SELECT spellid0, spellid1, spellid2, spellid3 FROM spelldifficulty_dbc');
    foreach ($rows as $r)
        DB::Aowow()->query('INSERT INTO ?_spelldifficulty VALUES (?a)', array_values($r));

    return true;
}

?>