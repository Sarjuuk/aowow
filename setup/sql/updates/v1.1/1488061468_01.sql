UPDATE aowow_races SET factionId = 69 WHERE id = 4;

UPDATE aowow_creature SET cuFlags = cuFlags | 0x40000000 WHERE
    name_loc0 LIKE '%[%' OR
    name_loc0 LIKE '%(%' OR
    name_loc0 LIKE '%visual%' OR
    name_loc0 LIKE '%trigger%' OR
    name_loc0 LIKE '%credit%' OR
    name_loc0 LIKE '%marker%';
