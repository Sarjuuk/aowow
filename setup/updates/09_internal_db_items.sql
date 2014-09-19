UPDATE aowow_creature SET cuFlags = cuFlags | 0x40000000 WHERE
	name_loc0 like '%(%' OR
	name_loc0 like '%[%' OR
	name_loc0 like '%<%' OR
	name_loc0 like '%placeholder%' OR
	name_loc0 like '%DND%' OR
	name_loc0 like '%UNUSED%';

UPDATE aowow_currencies SET cuFlags = cuFlags | 0x40000000 WHERE
    id IN (1, 2, 4, 22, 141);

UPDATE aowow_skillline SET cuFlags = cuFlags | 0x40000000 WHERE
    id IN (769, 142, 148, 149, 150, 152, 155, 533, 553, 554, 713, 183);

UPDATE aowow_items SET cuFlags = cuFlags | 0x40000000 WHERE
	name_loc0 like '%[%' OR
	name_loc0 like '%(PH)%' OR
	name_loc0 like '%(DEPRECATED)%';
