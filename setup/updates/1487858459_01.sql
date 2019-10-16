-- TYPE_NPC:1
UPDATE aowow_creature a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 1 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_OBJECT:2
UPDATE aowow_objects a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 2 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_ITEM:3
UPDATE aowow_items a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 3 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_ITEMSET:4
UPDATE aowow_itemset a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 4 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_QUEST:5
UPDATE aowow_quests a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 5 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_SPELL:6
UPDATE aowow_spell a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 6 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_ZONE:7
UPDATE aowow_zones a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 7 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_FACTION:8
UPDATE aowow_factions a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 8 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_PET:9
UPDATE aowow_pet a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 9 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_ACHIEVEMENT:10
UPDATE aowow_achievement a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 10 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_TITLE:11
UPDATE aowow_titles a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 11 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_WORLDEVENT:12
UPDATE aowow_events a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 12 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_CLASS:13
UPDATE aowow_classes a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 13 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_RACE:14
UPDATE aowow_races a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 14 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_SKILL:15
UPDATE aowow_skillline a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 15 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_CURRENCY:17
UPDATE aowow_currencies a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 17 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_EMOTE:501
UPDATE aowow_emotes a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 501 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;

-- TYPE_ENCHANTMENT:502
UPDATE aowow_itemenchantment a
JOIN  (SELECT typeId, BIT_OR(`status`) AS `ccFlags` FROM aowow_screenshots WHERE `type` = 502 GROUP BY typeId) b ON a.id = b.typeId
SET    a.cuFlags = a.cuFlags | 0x02000000
WHERE  b.ccFlags & 0x8;
