<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$ids = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, skillLine1 FROM dbc.creatureFamily WHERE petTalentType <> -1');

foreach ($ids as $family => $skillLine)
{
    $rows = DB::Aowow()->select('SELECT MAX(s.id) as Id, IF(t.id, 1, 0) AS isTalent FROM dbc.spell s JOIN dbc.skillLineAbility sla ON sla.spellId = s.id LEFT JOIN dbc.talent t ON t.rank1 = s.id WHERE (s.attributes0 & 0x40) = 0 AND sla.skillLineId = ?d GROUP BY s.nameEN', $skillLine);
    $i = 1;
    foreach ($rows as $row)
    {
        if ($row['isTalent'])
            continue;

        DB::Aowow()->query('UPDATE ?_pet SET spellId'.$i.' = ?d WHERE id = ?d', $row['Id'], $family);
        $i++;
    }
}

echo 'done';

?>
