<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


$customData = array(
     1 => ['displayIdH' => 8571],
    15 => ['displayIdH' => 8571],
     5 => ['displayIdH' => 2289],
     8 => ['displayIdH' => 2289],
    14 => ['displayIdH' => 2289],
    27 => ['displayIdH' => 21244],
    29 => ['displayIdH' => 20872],
);
$reqDBC = ['spellshapeshiftform'];

function shapeshiftforms()
{
    DB::Aowow()->query('REPLACE INTO ?_shapeshiftforms SELECT * FROM dbc_spellshapeshiftform');

    return true;
}

?>
