<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');



SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'holidays';

    protected $dbcSourceFiles = ['holidays', 'holidaydescriptions', 'holidaynames'];

    public function generate(array $ids = []) : bool
    {
        $query = '
            REPLACE INTO
                ?_holidays (id, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, description_loc0, description_loc2, description_loc3, description_loc4, description_loc6, description_loc8, looping, scheduleType, textureString)
            SELECT
                h.id, n.name_loc0, n.name_loc2, n.name_loc3, n.name_loc4, n.name_loc6, n.name_loc8, d.description_loc0, d.description_loc2, d.description_loc3, d.description_loc4, d.description_loc6, d.description_loc8, h.looping, h.scheduleType, h.textureString
            FROM
                dbc_holidays h
            LEFT JOIN
                dbc_holidaynames n ON n.id = h.nameId
            LEFT JOIN
                dbc_holidaydescriptions d ON d.id = h.descriptionId';

        DB::Aowow()->query($query);

        return true;
    }
});

?>
