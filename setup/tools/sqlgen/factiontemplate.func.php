<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'factiontemplate';

    protected $dbcSourceFiles = ['factiontemplate'];

    public function generate(array $ids = []) : bool
    {
        $query = '
            REPLACE INTO
                ?_factiontemplate
            SELECT
                id,
                factionId,
                IF(friendFactionId1 = 1 OR friendFactionId2 = 1 OR friendFactionId3 = 1 OR friendFactionId4 = 1 OR friendlyMask & 0x3,
                    1,
                    IF(enemyFactionId1 = 1 OR enemyFactionId2 = 1 OR enemyFactionId3 = 1 OR enemyFactionId4 = 1 OR hostileMask & 0x3, -1, 0)
                ),
                IF(friendFactionId1 = 2 OR friendFactionId2 = 2 OR friendFactionId3 = 2 OR friendFactionId4 = 2 OR friendlyMask & 0x5,
                    1,
                    IF(enemyFactionId1 = 2 OR enemyFactionId2 = 2 OR enemyFactionId3 = 2 OR enemyFactionId4 = 2 OR hostileMask & 0x5, -1, 0)
                )
            FROM
                dbc_factiontemplate';

        DB::Aowow()->query($query);

        return true;
    }
});

?>
