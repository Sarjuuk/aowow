<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'icons' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Icons from dbc.']
    );

    protected $dbcSourceFiles = ['spellicon', 'itemdisplayinfo', 'creaturefamily'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_icons');
        DB::Aowow()->query('ALTER TABLE ?_icons AUTO_INCREMENT = 1');
        DB::Aowow()->query(
           'INSERT INTO ?_icons (`name`) SELECT x FROM
            (
                (SELECT LOWER(SUBSTRING_INDEX(iconPath, "\\\\", -1))   AS x FROM dbc_spellicon       WHERE iconPath LIKE "%icons%") UNION
                (SELECT LOWER(inventoryIcon1)                          AS x FROM dbc_itemdisplayinfo WHERE inventoryIcon1 <> "")    UNION
                (SELECT LOWER(SUBSTRING_INDEX(iconString, "\\\\", -1)) AS x FROM dbc_creaturefamily  WHERE iconString LIKE "%icons%")
            ) y GROUP BY x'
        );

        $this->reapplyCCFlags('icons', Type::ICON);

        return true;
    }
});

?>
