<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// really should be part of events.func.php, but applying the custom data prevents this for now
CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    private const /* array */ CUSTOM_ICONS = array(
         62 => 'calendar_fireworksstart',                   // has no texture in dbc but exists as blp
        283 => 'inv_jewelry_necklace_21',
        284 => 'inv_misc_rune_07',
        285 => 'inv_jewelry_amulet_07',
        353 => 'spell_nature_eyeofthestorm',
        400 => 'achievement_bg_winsoa',
        420 => 'achievement_bg_winwsg'
    );

    protected $info = array(
        'holidays' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Event from dbc.']
    );

    protected $setupAfter     = [['icons'], []];
    protected $dbcSourceFiles = ['holidays', 'holidaydescriptions', 'holidaynames'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_holidays');
        DB::Aowow()->query(
           'INSERT INTO ?_holidays (`id`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, `description_loc0`, `description_loc2`, `description_loc3`, `description_loc4`, `description_loc6`, `description_loc8`, `looping`, `scheduleType`, `textureString`)
            SELECT      h.`id`, n.`name_loc0`, n.`name_loc2`, n.`name_loc3`, n.`name_loc4`, n.`name_loc6`, n.`name_loc8`, d.`description_loc0`, d.`description_loc2`, d.`description_loc3`, d.`description_loc4`, d.`description_loc6`, d.`description_loc8`, h.`looping`, h.`scheduleType`, h.`textureString`
            FROM        dbc_holidays h
            LEFT JOIN   dbc_holidaynames n ON n.`id` = h.`nameId`
            LEFT JOIN   dbc_holidaydescriptions d ON d.`id` = h.`descriptionId`'
        );

        // set derived icons
        DB::Aowow()->query('UPDATE ?_holidays h, ?_icons i SET h.`iconId` = i.`id` WHERE i.`name_source` LIKE CONCAT(LOWER(h.`textureString`), "%") AND h.`textureString` <> ""');

        // set custom icons
        foreach (self::CUSTOM_ICONS as $hId => $iconString)
            DB::Aowow()->query('UPDATE ?_holidays h SET h.`iconId` = (SELECT i.`id` FROM ?_icons i WHERE `name_source` = ?) WHERE `id` = ?d', $iconString, $hId);

        return true;
    }
});

?>
