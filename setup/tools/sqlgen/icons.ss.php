<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    private const /* array */ HOLIDAY_ICONS = array(
        'calendar_winterveilstart',
        'calendar_noblegardenstart',
        'calendar_childrensweekstart',
        'calendar_fishingextravaganzastart',
        'calendar_harvestfestivalstart',
        'calendar_hallowsendstart',
        'calendar_lunarfestivalstart',
        'calendar_loveintheairstart',
        'calendar_midsummerstart',
        'calendar_brewfeststart',
        'calendar_darkmoonfaireelwynnstart',
        'calendar_darkmoonfairemulgorestart',
        'calendar_darkmoonfaireterokkarstart',
        'calendar_piratesdaystart',
        'calendar_wotlklaunchstart',
        'calendar_dayofthedeadstart',
        'calendar_fireworksstart'
    );

    protected $info = array(
        'icons' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Icons from dbc.']
    );

    protected $dbcSourceFiles = ['spellicon', 'itemdisplayinfo', 'creaturefamily'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_icons');
        DB::Aowow()->query('ALTER TABLE ?_icons AUTO_INCREMENT = 1');
        DB::Aowow()->query(
           'INSERT INTO ?_icons (`name`, `name_source`) SELECT REGEXP_REPLACE(x, "\\\\W", "-"), x FROM
            (
                (SELECT LOWER(SUBSTRING_INDEX(`iconPath`, "\\\\", -1))   AS x FROM dbc_spellicon       WHERE `iconPath` LIKE "%icons%") UNION
                (SELECT LOWER(`inventoryIcon1`)                          AS x FROM dbc_itemdisplayinfo WHERE `inventoryIcon1` <> "")    UNION
                (SELECT LOWER(SUBSTRING_INDEX(`iconString`, "\\\\", -1)) AS x FROM dbc_creaturefamily  WHERE `iconString` LIKE "%icons%")
            ) y GROUP BY x'
        );

        // invent class icons
        foreach (ChrClass::cases() as $cl)
            DB::Aowow()->query('INSERT INTO ?_icons (`name`, `name_source`) VALUES (?, ?)', 'class_'.$cl->json(), 'class_'.$cl->json());

        // invent race icons
        foreach (ChrRace::cases() as $ra)
        {
            if ($na = $ra->json())                          // unused races have no json
            {
                DB::Aowow()->query('INSERT INTO ?_icons (`name`, `name_source`) VALUES (?, ?)', 'race_'.$na.'_male',   'race_'.$na.'_male');
                DB::Aowow()->query('INSERT INTO ?_icons (`name`, `name_source`) VALUES (?, ?)', 'race_'.$na.'_female', 'race_'.$na.'_female');
            }
        }

        // halucinate holidays
        foreach (self::HOLIDAY_ICONS as $h)
            DB::Aowow()->query('INSERT INTO ?_icons (`name`, `name_source`) VALUES (?, ?)', $h, $h);

        $this->reapplyCCFlags('icons', Type::ICON);

        return true;
    }
});

?>
