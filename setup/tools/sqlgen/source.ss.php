<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'source' => [[], CLISetup::ARGV_PARAM, 'Compiles source data for type: Item, Spell & Titles from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['charstartoutfit', 'talent', 'spell', 'skilllineability', 'itemextendedcost', 'lock'];
    protected $worldDependency = ['playercreateinfo_skills', 'playercreateinfo_item', 'skill_discovery_template', 'achievement_reward', 'skill_perfect_item_template', 'item_template', 'gameobject_template', 'quest_template', 'quest_template_addon', 'creature_template', 'creature', 'creature_default_trainer', 'trainer_spell', 'npc_vendor', 'game_event_npc_vendor', 'reference_loot_template', 'item_loot_template', 'creature_loot_template', 'gameobject_loot_template', 'mail_loot_template', 'disenchant_loot_template', 'fishing_loot_template', 'skinning_loot_template', 'milling_loot_template', 'prospecting_loot_template', 'pickpocketing_loot_template'];
    protected $setupAfter      = [['spell', 'achievement', 'items', 'itemset', 'spawns', 'creature', 'zones', 'titles'], []];

    private array $srcBuffer = [];
    private array $refLoot   = [];
    private array $dummyNPCs = [];
    private array $dummyGOs  = [];
    private array $disables  = [];

    private const /* array */ PVP_MONEY        = [26045, 24581, 24579, 43589, 37836]; // Nagrand, Hellfire Pen. H, Hellfire Pen. A, Wintergrasp, Grizzly Hills
    private const /* int */   COMMON_THRESHOLD = 100;

    public function generate(array $ids = []) : bool
    {
        /*********************************/
        /* resolve difficulty dummy NPCS */
        /*********************************/

        $this->dummyNPCs = DB::Aowow()->select(
           'SELECT `difficultyEntry1` AS ARRAY_KEY, 2 AS "0", `id` AS "1" FROM ?_creature WHERE `difficultyEntry1` > 0 UNION
            SELECT `difficultyEntry2` AS ARRAY_KEY, 4 AS "0", `id` AS "1" FROM ?_creature WHERE `difficultyEntry2` > 0 UNION
            SELECT `difficultyEntry3` AS ARRAY_KEY, 8 AS "0", `id` AS "1" FROM ?_creature WHERE `difficultyEntry3` > 0'
        );

        $this->dummyGOs = DB::Aowow()->select(
           'SELECT    l1.`objectId` AS ARRAY_KEY, BIT_OR(l1.`difficulty`) AS "0", IFNULL(l2.`npcId`, l1.`npcId`) AS "1"
            FROM      ?_loot_link l1
            LEFT JOIN ?_loot_link l2 ON l1.`objectId` = l2.`objectId` AND l2.`priority` = 1
            GROUP BY l1.`objectid`'
        );

        $this->disables = DB::World()->selectCol(
           'SELECT IF(`sourceType`, ?d, ?d) AS ARRAY_KEY, `entry` AS ARRAY_KEY2, `entry`
            FROM  disables
            WHERE (`sourceType` = 0 AND (`flags` & 0xF)) OR `sourceType` = 1',
            Type::QUEST, Type::SPELL
        );

        CLI::write('[source] - resolving ref-loot tree', CLI::LOG_BLANK, true, true);
        $this->refLoot = DB::World()->select(
           'SELECT    rlt.`Entry` AS ARRAY_KEY, IF(`Reference`, -`Reference`, `Item`) AS ARRAY_KEY2, it.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`
            FROM      reference_loot_template rlt
            LEFT JOIN item_template it ON rlt.`Reference` = 0 AND rlt.`Item` = it.`entry`
            GROUP BY  ARRAY_KEY, ARRAY_KEY2'
        );

        $hasChanged = true;
        while ($hasChanged)
        {
            $hasChanged = false;
            foreach ($this->refLoot as $entry => $refData)
            {
                foreach ($refData as $itemOrRef => $data)
                {
                    if ($itemOrRef > 0)
                        continue;

                    if (empty($this->refLoot[-$itemOrRef]))
                        continue;

                    foreach ($this->refLoot[-$itemOrRef] AS $key => $data)
                        $this->refLoot[$entry][$key] = $data;

                    unset($this->refLoot[$entry][$itemOrRef]);
                    $hasChanged = true;
                }
            }
        }

        DB::Aowow()->query('TRUNCATE ?_source');

        /***************************/
        /* Item & inherited Spells */
        /***************************/

        CLI::write('[source] - Items & Spells [inherited]');
        # also everything from items that teach spells, is src of spell

        $this->itemCrafted();                               #  1: Crafted      #
        $this->itemDrop();                                  #  2: Drop         #
        $this->itemPvP();                                   #  3: PvP          # (Vendors w/ xCost Arena/Honor)
        $this->itemQuest();                                 #  4: Quest        #
        $this->itemVendor();                                #  5: Vendor       # (w/o xCost Arena/Honor)
        $this->itemStarter();                               # 10: Starter      #
        $this->itemAchievement();                           # 12: Achievement  #
        $this->itemDisenchantment();                        # 15: Disenchanted #
        $this->itemFishing();                               # 16: Fished       #
        $this->itemGathering();                             # 17: Gathered     #
        $this->itemMilling();                               # 18: Milled       #
        $this->itemMining();                                # 19: Mined        #
        $this->itemProspecting();                           # 20: Prospected   #
        $this->itemPickpocketing();                         # 21: Pickpocket   #
        $this->itemSalvaging();                             # 22: Salvaged     #
        $this->itemSkinning();                              # 23: Skinned      #

        /*********/
        /* Spell */
        /*********/

        CLI::write('[source] - Spells [original]');

        $this->spellQuest();                                #  4: Quest     #
        $this->spellTrainer();                              #  6: Trainer   #
        $this->spellDiscovery();                            #  7: Discovery #
        $this->spellTalent();                               #  9: Talent    #
        $this->spellStarter();                              # 10: Starter   #

        /**********/
        /* Titles */
        /**********/

        CLI::write('[source] - Titles');

        $this->titleQuest();                                #  4: Quest         #
        $this->titleAchievement();                          # 12: Achievement   #
        $this->titleCustomString();                         # 13: Source-String #

        /************/
        /* Itemsets */
        /************/

        CLI::write('[source] - Itemsets');

        $this->itemset();                                   # Meta category .. inherit from items #


        $t = new Timer(500);
        foreach ($this->srcBuffer as $type => $data)
        {
            $j    = 0;
            $rows = [];
            $sum  = count($data);
            foreach ($data as $d)
            {
                $rows[++$j] = array_slice($d, 0, 6);
                for ($i = 1; $i < 25; $i++)
                    $rows[$j][] = $d[6][$i] ?? 0;

                if ($d[7] > self::COMMON_THRESHOLD)
                    $rows[$j][5] |= SRC_FLAG_COMMON;

                if ($t->update())
                    CLI::write('[source] - Inserting... (['.$type.'] '.$j.' / '.$sum.')', CLI::LOG_BLANK, true, true);

                if (!($j % 300))
                    $this->insert($rows);
            }

            if ($rows)                                      // insert leftovers
                $this->insert($rows);
        }

        CLI::write('[source] - Inserting... (done)');

        // generally treat all items generated by spell as being available. The spells may be triggered in convoluted ways but usually _are_ in use.
        $itemSpellSource = DB::Aowow()->selectCol(
           'SELECT x.`itemId` FROM (
            SELECT `effect1CreateItemId` AS "itemId", s.`id` FROM dbc_spell s WHERE `effect1CreateItemId` > 0 AND (`effect1Id` IN (?a) OR `effect1AuraId` IN (?a)) UNION ALL
            SELECT `effect2CreateItemId` AS "itemId", s.`id` FROM dbc_spell s WHERE `effect2CreateItemId` > 0 AND (`effect2Id` IN (?a) OR `effect2AuraId` IN (?a)) UNION ALL
            SELECT `effect3CreateItemId` AS "itemId", s.`id` FROM dbc_spell s WHERE `effect3CreateItemId` > 0 AND (`effect3Id` IN (?a) OR `effect3AuraId` IN (?a)) ) AS x
          { WHERE  x.`id` NOT IN (?a) }',
            SpellList::EFFECTS_ITEM_CREATE, SpellList::AURAS_ITEM_CREATE,
            SpellList::EFFECTS_ITEM_CREATE, SpellList::AURAS_ITEM_CREATE,
            SpellList::EFFECTS_ITEM_CREATE, SpellList::AURAS_ITEM_CREATE,
            !empty($this->disables[Type::SPELL]) ? array_values($this->disables[Type::SPELL]) : DBSIMPLE_SKIP
        );

        // flagging aowow_items for source (note: this is not exact! creatures dropping items may not be spawned, etc.)
        DB::Aowow()->query('UPDATE ?_items SET `cuFlags` = `cuFlags` & ?d', ~CUSTOM_UNAVAILABLE);
        DB::Aowow()->query(
           'UPDATE    ?_items i
            LEFT JOIN ?_source s ON s.`typeId` = i.`id` AND s.`type` = ?d
            SET       i.`cuFlags` = i.`cuFlags` | ?d
            WHERE     (s.`typeId` IS NULL AND i.`id` NOT IN (?a)) OR i.`quality` = ?d',
            Type::ITEM, CUSTOM_UNAVAILABLE, $itemSpellSource, ITEM_QUALITY_ARTIFACT
        );

        return true;
    }

    private function pushBuffer(int $type, int $typeId, int $srcId, int $srcBit = 1, int $mType = 0, int $mTypeId = 0, ?int $mZoneId = null, int $mMask = 0x0, int $qty = 1) : void
    {
        if (!isset($this->srcBuffer[$type]))
            $this->srcBuffer[$type] = [];

        if (!isset($this->srcBuffer[$type][$typeId]))
        {
            $this->srcBuffer[$type][$typeId] = [$type, $typeId, $mType ?: null, $mTypeId && $mType ? $mTypeId : null, $mZoneId, $mMask, [$srcId => $srcBit], $qty];
            return;
        }

        $b = &$this->srcBuffer[$type][$typeId];

        if ($mType != $b[2] || $mTypeId != $b[3])
            $b[2] = $b[3] = null;

        if ($mZoneId && $b[4] === null)
            $b[4] = $mZoneId;
        else if ($mZoneId && $b[4] && $mZoneId != $b[4])
            $b[4] = 0;

        $b[5] = ($b[5] ?? 0) & $mMask;                      // only bossdrop for now .. remove flag if regular source is available
        $b[6][$srcId] = ($b[6][$srcId] ?? 0) | $srcBit;     // SIDE_X for quests, modeMask for drops, subSrc for pvp, else: 1
        $b[7] += $qty;
    }

    private function insert(array &$rows) : void
    {
        if (!$rows)
            return;

        $str = '';
        foreach ($rows as &$r)
        {
            array_walk($r, function(&$x) {$x = (int)$x === 0 ? 'NULL' : $x; });
            $str .= '(' . implode(', ', $r) . '),';
        }

        $rows = [];
        DB::Aowow()->query('INSERT INTO ?_source VALUES '. substr($str, 0, -1));
    }

    private function taughtSpell(array $item) : int
    {
        // should not be able to teach spells (recipe || mount || vanityPet)
        if ($item['class'] != ITEM_CLASS_RECIPE && ($item['class'] != ITEM_CLASS_MISC || ($item['subclass'] != 2 && $item['subclass'] != 5)))
            return 0;

        // wotlk system
        if (in_array($item['spellid_1'], LEARN_SPELLS) && $item['spelltrigger_1'] == SPELL_TRIGGER_USE && $item['spellid_2'] > 0 && $item['spelltrigger_2'] == SPELL_TRIGGER_LEARN)
            return $item['spellid_2'];

        // deprecated system
        if (!in_array($item['spellid_1'], LEARN_SPELLS) && $item['spelltrigger_1'] == SPELL_TRIGGER_USE && $item['spellid_1'] > 0)
            return $item['spellid_1'];

        return 0;
    }

    private function itemCrafted() : void
    {
        CLI::write('[source] * #1  Crafted', CLI::LOG_BLANK, true, true);

        $itemSpells = DB::Aowow()->selectCol(
           'SELECT   `effect1CreateItemId` AS ARRAY_KEY, s.`id`
            FROM     dbc_spell s
            JOIN     dbc_skilllineability sla ON s.`id` = sla.`spellId`
            WHERE    `effect1CreateItemId` > 0 AND sla.`skillLineId` IN (?a)
            GROUP BY ARRAY_KEY',
            array_merge(SKILLS_TRADE_PRIMARY, [SKILL_FIRST_AID, SKILL_COOKING, SKILL_FISHING])
        );

        // assume unique craft spells per item
        $perfectItems = DB::World()->selectCol('SELECT `perfectItemType` AS ARRAY_KEY, `spellId` AS "spell" FROM skill_perfect_item_template');
        foreach ($perfectItems AS $item => $spell)
            $itemSpells[$item] = $spell;

        $itemSpells = array_filter($itemSpells, fn($x) => empty($this->disables[Type::SPELL][$x]));

        $spellItems = DB::World()->select('SELECT `entry` AS ARRAY_KEY, `class`, `subclass`, `spellid_1`, `spelltrigger_1`, `spellid_2`, `spelltrigger_2` FROM item_template WHERE `entry` IN (?a)', array_keys($itemSpells));
        foreach ($spellItems as $iId => $si)
        {
            if ($_ = $this->taughtSpell($si))
                $this->pushBuffer(Type::SPELL, $_, SRC_CRAFTED, 1, Type::SPELL, $itemSpells[$iId]);

            $this->pushBuffer(Type::ITEM, $iId, SRC_CRAFTED, 1, Type::SPELL, $itemSpells[$iId]);
        }
    }

    private function itemDrop() : void
    {
        $objectOT = [];
        $itemOT   = [];

        CLI::write('[source] * #2  Drop [NPC]', CLI::LOG_BLANK, true, true);

        $creatureLoot = DB::World()->select(
           'SELECT    IF(clt.`Reference` > 0, -clt.`Reference`, clt.`Item`) AS "refOrItem", ct.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      creature_loot_template clt
            JOIN      creature_template ct ON clt.`entry` = ct.`lootid`
            LEFT JOIN item_template it ON it.`entry` = clt.`Item` AND clt.`Reference` <= 0
            WHERE     ct.`lootid` > 0
            GROUP BY  `refOrItem`, ct.`entry`'
        );

        $npcSpawns = DB::Aowow()->select('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT s.`areaId`) > 1, 0, s.`areaId`) AS "areaId", z.`type` FROM ?_spawns s JOIN ?_zones z ON z.`id` = s.`areaId` WHERE s.`type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_merge(array_column($this->dummyGOs, 1), array_filter(array_column($creatureLoot, 'entry'))));
        $bosses    = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, IF(`cuFlags` & ?d, 1, IF(`typeFlags` & 0x4 AND `rank` > 0, 1, 0)) FROM ?_creature WHERE `id` IN (?a)', NPC_CU_INSTANCE_BOSS, array_filter(array_column($creatureLoot, 'entry')));

        foreach ($creatureLoot as $l)
        {
            $roi    = $l['refOrItem'];
            $entry  = $l['entry'];
            $mode   = 1;
            $zoneId = 0;
            $mMask  = 0x0;
            if (isset($this->dummyNPCs[$l['entry']]))
                [$mode, $entry] = $this->dummyNPCs[$l['entry']];

            if (isset($bosses[$entry]) && $bosses[$entry])  // can be empty...?
                $mMask |= SRC_FLAG_BOSSDROP;

            if (isset($npcSpawns[$entry]))
            {
                switch ($npcSpawns[$entry]['type'])
                {
                    case MAP_TYPE_DUNGEON_HC:
                        $mMask |= SRC_FLAG_DUNGEON_DROP; break;
                    case MAP_TYPE_MMODE_RAID:
                    case MAP_TYPE_MMODE_RAID_HC:
                        $mMask |= SRC_FLAG_RAID_DROP; break;
                }

                $zoneId = $npcSpawns[$entry]['areaId'];
            }

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, $mMask, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, $mMask, $l['qty']);
                }

                continue;
            }
            else if ($roi < 0)
            {
                CLI::write('[source] - unresolved ref-loot ref: ' . $roi, CLI::LOG_WARN);
                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, $mMask, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, $mMask, $l['qty']);
        }

        CLI::write('[source] * #2  Drop [Object]', CLI::LOG_BLANK, true, true);

        $objectLoot = DB::World()->select(
           'SELECT    IF(glt.`Reference` > 0, -glt.`Reference`, glt.`Item`) AS "refOrItem", gt.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      gameobject_loot_template glt
            JOIN      gameobject_template gt ON glt.`entry` = gt.`data1`
            LEFT JOIN item_template it ON it.`entry` = glt.`Item` AND glt.`Reference` <= 0
            WHERE     `type` = ?d AND gt.`data1` > 0 AND gt.`data0` NOT IN (?a)
            GROUP BY  `refOrItem`, gt.`entry`',
            OBJECT_CHEST,
            DB::Aowow()->selectCol('SELECT `id` FROM dbc_lock WHERE `properties1` IN (?a)', [LOCK_PROPERTY_HERBALISM, LOCK_PROPERTY_MINING])
        );

        $goSpawns = DB::Aowow()->select('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT s.`areaId`) > 1, 0, s.`areaId`) AS "areaId", z.`type` FROM ?_spawns s JOIN ?_zones z ON z.`id` = s.`areaId` WHERE s.`type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::OBJECT, array_filter(array_column($objectLoot, 'entry')));

        foreach ($objectLoot as $l)
        {
            $roi    = $l['refOrItem'];
            $entry  = $l['entry'];
            $mode   = 1 | ($this->dummyGOs[$entry][0] ?? 0);
            $zoneId = 0;
            $mMask  = 0x0;
            $spawn  = [];

            if (isset($this->dummyGOs[$entry]))             // we know these are all boss drops
                $mMask |= SRC_FLAG_BOSSDROP;

            if (isset($goSpawns[$entry]))
                $spawn = $goSpawns[$entry];
            else if (isset($this->dummyGOs[$entry]) && isset($npcSpawns[$this->dummyGOs[$entry][1]]))
                $spawn = $npcSpawns[$this->dummyGOs[$entry][1]];

            if ($spawn)
            {
                switch ($spawn['type'])
                {
                    case MAP_TYPE_DUNGEON_HC:
                        $mMask |= SRC_FLAG_DUNGEON_DROP; break;
                    case MAP_TYPE_MMODE_RAID:
                    case MAP_TYPE_MMODE_RAID_HC:
                        $mMask |= SRC_FLAG_RAID_DROP; break;
                }

                $zoneId = $spawn['areaId'];
            }

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);

                    $objectOT[] = $iId;
                    $this->pushBuffer(Type::ITEM, $iId, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);

            $objectOT[] = $roi;
            $this->pushBuffer(Type::ITEM, $roi, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);
        }

        CLI::write('[source] * #2  Drop [Item]', CLI::LOG_BLANK, true, true);

        $itemLoot = DB::World()->select(
           'SELECT    IF(ilt.`Reference` > 0, -ilt.`Reference`, ilt.`Item`) AS ARRAY_KEY, itA.`entry`, itB.`class`, itB.`subclass`, itB.`spellid_1`, itB.`spelltrigger_1`, itB.`spellid_2`, itB.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      item_loot_template ilt
            JOIN      item_template itA ON ilt.`entry` = itA.`entry`
            LEFT JOIN item_template itB ON itB.`entry` = ilt.`Item` AND ilt.`Reference` <= 0
            WHERE     itA.`flags` & ?d
            GROUP BY  ARRAY_KEY',
            ITEM_FLAG_OPENABLE
        );

        foreach ($itemLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::ITEM, $l['entry'], 0, $l['qty']);

                    $itemOT[] = $iId;
                    $this->pushBuffer(Type::ITEM, $iId, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::ITEM, $l['entry'], 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::ITEM, $l['entry'], 0, $l['qty']);

            $itemOT[] = $roi;
            $this->pushBuffer(Type::ITEM, $roi, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::ITEM, $l['entry'], 0, $l['qty']);
        }

        DB::Aowow()->query('UPDATE ?_items SET `cuFLags` = `cuFlags` | ?d WHERE `id` IN (?a)', ITEM_CU_OT_ITEMLOOT,   $itemOT);
        DB::Aowow()->query('UPDATE ?_items SET `cuFLags` = `cuFlags` | ?d WHERE `id` IN (?a)', ITEM_CU_OT_OBJECTLOOT, $objectOT);
    }

    private function itemPvP() : void
    {
        CLI::write('[source] * #3  PvP', CLI::LOG_BLANK, true, true);

        $subSrcByXCost = array(
            SRC_SUB_PVP_BG    => DB::Aowow()->selectCol('SELECT `id` FROM dbc_itemextendedcost WHERE `reqArenaPoints` = 0 AND `reqHonorPoints` > 0'),
            SRC_SUB_PVP_ARENA => DB::Aowow()->selectCol('SELECT `id` FROM dbc_itemextendedcost WHERE `reqArenaPoints` > 0'),
            SRC_SUB_PVP_WORLD => DB::Aowow()->selectCol('SELECT `id` FROM dbc_itemextendedcost WHERE `reqItemId1` IN (?a) OR `reqItemId2` IN (?a) OR `reqItemId3` IN (?a) OR `reqItemId4` IN (?a) OR `reqItemId5` IN (?a)', self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY)
        );
        $vendorQuery =
           'SELECT   n.`item`, SUM(n.`qty`) AS "qty", it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`
            FROM     (SELECT     `item`, COUNT(1) AS "qty" FROM npc_vendor                                                                                     WHERE     `ExtendedCost` IN (?a) AND       `item` > 0 GROUP BY `item` UNION
                      SELECT nv2.`item`, COUNT(1) AS "qty" FROM npc_vendor nv1             JOIN npc_vendor nv2 ON nv2.`entry` = -nv1.`item` AND nv1.`item` < 0 WHERE nv1.`ExtendedCost` IN (?a) AND `nv1`.`item` < 0 GROUP BY `item` UNION
                      SELECT     `item`, COUNT(1) AS "qty" FROM game_event_npc_vendor genv JOIN creature c     ON   c.`guid`  = genv.`guid`                    WHERE     `ExtendedCost` IN (?a)                      GROUP BY `item`) n
            JOIN     item_template it ON it.`entry` = n.`item`
            GROUP BY `item`';

        foreach ($subSrcByXCost as $subSrc => $xCost)
        {
            foreach (DB::World()->select($vendorQuery, $xCost, $xCost, $xCost) as $v)
            {
                if ($_ = $this->taughtSpell($v))
                    $this->pushBuffer(Type::SPELL, $_, SRC_PVP, $subSrc);

                $this->pushBuffer(Type::ITEM, $v['item'], SRC_PVP, $subSrc);
            }
        }
    }

    private function itemQuest() : void
    {
        CLI::write('[source] * #4  Quest', CLI::LOG_BLANK, true, true);

        $quests = DB::World()->select(
           'SELECT   n.`item` AS ARRAY_KEY, n.`ID` AS "quest", SUM(n.`qty`) AS "qty", BIT_OR(n.`side`) AS "side", IF(COUNT(DISTINCT `zone`) > 1, 0, `zone`) AS "zone", it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`
            FROM    (SELECT `RewardChoiceItemID1` AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardChoiceItemID1` > 0 UNION ALL
                     SELECT `RewardChoiceItemID2` AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardChoiceItemID2` > 0 UNION ALL
                     SELECT `RewardChoiceItemID3` AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardChoiceItemID3` > 0 UNION ALL
                     SELECT `RewardChoiceItemID4` AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardChoiceItemID4` > 0 UNION ALL
                     SELECT `RewardChoiceItemID5` AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardChoiceItemID5` > 0 UNION ALL
                     SELECT `RewardChoiceItemID6` AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardChoiceItemID6` > 0 UNION ALL
                     SELECT `RewardItem1`         AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardItem1`         > 0 UNION ALL
                     SELECT `RewardItem2`         AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardItem2`         > 0 UNION ALL
                     SELECT `RewardItem3`         AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardItem3`         > 0 UNION ALL
                     SELECT `RewardItem4`         AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardItem4`         > 0 UNION ALL
                     SELECT `StartItem`           AS "item", `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `StartItem`           > 0) n
            JOIN     item_template it ON it.`entry` = n.`item`
          { WHERE    n.`ID` NOT IN (?a) }
            GROUP BY `item`',
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            !empty($this->disables[Type::QUEST]) ? array_values($this->disables[Type::QUEST]) : DBSIMPLE_SKIP
        );
        $areaParent = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `parentArea` FROM ?_zones WHERE `id` IN (?a) AND `parentArea` > 0', array_filter(array_column($quests, 'zone')));

        foreach ($quests as $iId => $q)
        {
            if ($q['zone'] < 0)                             // remove questSort
                $q['zone'] = 0;                             // todo: do not use questSort for zoneId, but .. questender? (starter can be item)

            if ($_ = $this->taughtSpell($q))
                $this->pushBuffer(Type::SPELL, $_, SRC_QUEST, $q['side'], $q['qty'] > 1 ? 0 : Type::QUEST, $q['quest'], $areaParent[$q['zone']] ?? Game::$questSortFix[$q['zone']] ?? $q['zone']);

            $this->pushBuffer(Type::ITEM, $iId, SRC_QUEST, $q['side'], $q['qty'] > 1 ? 0 : Type::QUEST, $q['quest'], $areaParent[$q['zone']] ?? Game::$questSortFix[$q['zone']] ?? $q['zone']);
        }

        $mailLoot = DB::World()->select(
           'SELECT    IF(mlt.`Reference` > 0, -mlt.`Reference`, mlt.`Item`) AS ARRAY_KEY, qt.`Id` AS "entry", it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty", IF(COUNT(DISTINCT `QuestSortID`) > 1, 0, GREATEST(`QuestSortID`, 0)) AS "zone", BIT_OR(IF(qt.`AllowableRaces` & ?d AND NOT (qt.`AllowableRaces` & ?d), ?d, IF(qt.`AllowableRaces` & ?d AND NOT (qt.`AllowableRaces` & ?d), ?d, ?d))) AS "side"
            FROM      mail_loot_template mlt
            JOIN      quest_template_addon qta ON qta.`RewardMailTemplateId` = mlt.`entry`
            JOIN      quest_template qt ON qt.`ID` = qta.`ID`
            LEFT JOIN item_template it ON it.`entry` = mlt.`Item` AND mlt.`Reference` <= 0
            WHERE     qta.`RewardMailTemplateId` > 0
            GROUP BY  ARRAY_KEY',
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH
        );

        $areaParent = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `parentArea` FROM ?_zones WHERE `id` IN (?a) AND `parentArea` > 0', array_filter(array_column($mailLoot, 'zone')));

        foreach ($mailLoot as $roi => $l)
        {
            if ($l['zone'] < 0)                             // remove questSort
                $l['zone'] = 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? Game::$questSortFix[$l['zone']] ?? $l['zone']);

                    $itemOT[] = $iId;
                    $this->pushBuffer(Type::ITEM, $iId, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? Game::$questSortFix[$l['zone']] ?? $l['zone']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? Game::$questSortFix[$l['zone']] ?? $l['zone']);

            $itemOT[] = $roi;
            $this->pushBuffer(Type::ITEM, $roi, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? Game::$questSortFix[$l['zone']] ?? $l['zone']);
        }
    }

    private function itemVendor() : void
    {
        CLI::write('[source] * #5  Vendor', CLI::LOG_BLANK, true, true);

        $xCostIds = DB::Aowow()->selectCol('SELECT `id` FROM dbc_itemextendedcost WHERE `reqHonorPoints` <> 0 OR `reqArenaPoints` <> 0 OR `reqItemId1` IN (?a) OR `reqItemId2` IN (?a) OR `reqItemId3` IN (?a) OR `reqItemId4` IN (?a) OR `reqItemId5` IN (?a)', self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY);
        $vendors  = DB::World()->select(
           'SELECT   n.`item`, n.`npc`, SUM(n.`qty`) AS "qty", it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`
            FROM     (SELECT     `item`,     `entry` AS "npc", COUNT(1) AS "qty" FROM npc_vendor                                                                                      WHERE     `ExtendedCost` NOT IN (?a) AND       `item` > 0 GROUP BY `item`, `npc` UNION
                      SELECT nv2.`item`, nv1.`entry` AS "npc", COUNT(1) AS "qty" FROM npc_vendor nv1              JOIN npc_vendor nv2 ON nv2.`entry` = -nv1.`item` AND nv1.`item` < 0 WHERE nv1.`ExtendedCost` NOT IN (?a) AND `nv1`.`item` < 0 GROUP BY `item`, `npc` UNION
                      SELECT     `item`,   c.`id`    AS "npc", COUNT(1) AS "qty" FROM game_event_npc_vendor genv  JOIN creature c     ON c.`guid` = genv.`guid`                       WHERE     `ExtendedCost` NOT IN (?a)                      GROUP BY `item`, `npc`) n
            JOIN     item_template it ON it.`entry` = n.`item`
            GROUP BY `item`, `npc`',
            $xCostIds, $xCostIds, $xCostIds
        );

        $spawns = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_column($vendors, 'npc'));

        foreach ($vendors as $v)
        {
            if ($_ = $this->taughtSpell($v))
                $this->pushBuffer(Type::SPELL, $_, SRC_VENDOR, 1, $v['qty'] > 1 ? 0 : Type::NPC, $v['npc'], $spawns[$v['npc']] ?? 0, 0, $v['qty']);

            $this->pushBuffer(Type::ITEM, $v['item'], SRC_VENDOR, 1, $v['qty'] > 1 ? 0 : Type::NPC, $v['npc'], $spawns[$v['npc']] ?? 0, 0, $v['qty']);
        }
    }

    private function itemStarter() : void
    {
        CLI::write('[source] * #10 Starter', CLI::LOG_BLANK, true, true);

        if ($pcii = DB::World()->selectCol('SELECT DISTINCT `itemid` FROM playercreateinfo_item'))
            foreach ($pcii as $item)
                $this->pushBuffer(Type::ITEM, $item, SRC_STARTER);

        for ($i = 1; $i < 21; $i++)
            if ($cso = DB::Aowow()->selectCol('SELECT ?# FROM dbc_charstartoutfit WHERE ?# > 0', 'item'.$i, 'item'.$i))
                foreach ($cso as $item)
                    $this->pushBuffer(Type::ITEM, $item, SRC_STARTER);
    }

    private function itemAchievement() : void
    {
        CLI::write('[source] * #12 Achievement', CLI::LOG_BLANK, true, true);

        $xItems   = DB::Aowow()->select('SELECT `id` AS "entry", `itemExtra` AS ARRAY_KEY, COUNT(1) AS "qty" FROM ?_achievement WHERE `itemExtra` > 0 GROUP BY `itemExtra`');
        $rewItems = DB::World()->select(
           'SELECT    src.`item` AS ARRAY_KEY, src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      (SELECT    IFNULL(IF(mlt.`Reference` > 0, -mlt.`Reference`, mlt.`Item`), ar.`ItemID`) AS "item", ar.`ID` AS "entry"
                       FROM      achievement_reward ar
                       LEFT JOIN mail_loot_template mlt ON mlt.`entry` = ar.`MailTemplateID`
                       WHERE     ar.`MailTemplateID` > 0 OR ar.`ItemID` > 0) src
            LEFT JOIN item_template it ON src.`item` = it.`entry`
            GROUP BY  ARRAY_KEY'
        );

        if (empty($xItems))
            CLI::write('[source] itemAchievement() - Reward items are unexpectedly empty.', CLI::LOG_WARN);
        else
        {
            $extraItems = DB::World()->select('SELECT `entry` AS ARRAY_KEY, `class`, `subclass`, `spellid_1`, `spelltrigger_1`, `spellid_2`, `spelltrigger_2` FROM item_template WHERE `entry` IN (?a)', array_keys($xItems));
            foreach ($extraItems as $iId => $l)
            {
                if ($_ = $this->taughtSpell($l))
                    $this->pushBuffer(Type::SPELL, $_, SRC_ACHIEVEMENT, 1, $xItems[$iId]['qty'] > 1 ? 0 : Type::ACHIEVEMENT, $xItems[$iId]['entry']);

                $this->pushBuffer(Type::ITEM, $iId, SRC_ACHIEVEMENT, 1, $xItems[$iId]['qty'] > 1 ? 0 : Type::ACHIEVEMENT, $xItems[$iId]['entry']);
            }
        }

        foreach ($rewItems as $iId => $l)
        {
            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_ACHIEVEMENT, 1, $l['qty'] > 1 ? 0 : Type::ACHIEVEMENT, $l['entry']);

            $this->pushBuffer(Type::ITEM, $iId, SRC_ACHIEVEMENT, 1, $l['qty'] > 1 ? 0 : Type::ACHIEVEMENT, $l['entry']);
        }
    }

    private function itemDisenchantment() : void
    {
        CLI::write('[source] * #15 Disenchanted', CLI::LOG_BLANK, true, true);

        $deLoot = DB::World()->select(
           'SELECT    IF(dlt.`Reference` > 0, -dlt.`Reference`, dlt.`Item`) AS "refOrItem", itA.`entry`, itB.`class`, itB.`subclass`, itB.`spellid_1`, itB.`spelltrigger_1`, itB.`spellid_2`, itB.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      disenchant_loot_template dlt
            JOIN      item_template itA ON dlt.`entry` = itA.`DisenchantId`
            LEFT JOIN item_template itB ON itB.`entry` = dlt.`Item` AND dlt.`Reference` <= 0
            WHERE     itA.`DisenchantId` > 0
            GROUP BY  `refOrItem`, itA.`entry`'
        );

        foreach ($deLoot as $l)
        {
            $roi = $l['refOrItem'];

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_DISENCHANTMENT);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_DISENCHANTMENT);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_DISENCHANTMENT);

            $this->pushBuffer(Type::ITEM, $roi, SRC_DISENCHANTMENT);
        }
    }

    private function itemFishing() : void
    {
        CLI::write('[source] * #16 Fished', CLI::LOG_BLANK, true, true);

        $fishLoot = DB::World()->select(
           'SELECT    src.`itemOrRef` AS "refOrItem", src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty", IF(COUNT(DISTINCT `zone`) > 2, 0, MAX(`zone`)) AS "zone"
            FROM      (SELECT 0 AS "entry", IF(flt.`Reference` > 0, -flt.`Reference`, flt.`Item`) AS "itemOrRef", `entry` AS "zone" FROM fishing_loot_template flt UNION
                       SELECT   gt.`entry`, IF(glt.`Reference` > 0, -glt.`Reference`, glt.`Item`) AS "itemOrRef",       0 AS "zone" FROM gameobject_template   gt  JOIN gameobject_loot_template glt ON glt.`entry` = gt.`data1` WHERE `type` = ?d AND gt.`data1` > 0) src
            LEFT JOIN item_template it ON src.`itemOrRef` > 0 AND src.`itemOrRef` = it.`entry`
            GROUP BY  `refOrItem`, src.`entry`',
            OBJECT_FISHINGHOLE
        );

        if (!$fishLoot)
        {
            CLI::write('[source] itemFishing() - fishing_loot_template empty and gameobject_template contained no fishing nodes (type:25).', CLI::LOG_WARN);
            return;
        }

        $goSpawns   = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::OBJECT, array_filter(array_column($fishLoot, 'entry')));
        $areaParent = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `parentArea` FROM ?_zones WHERE `id` IN (?a) AND `parentArea` > 0', array_filter(array_column($fishLoot, 'zone')));

        foreach ($fishLoot as $l)
        {
            $roi       = $l['refOrItem'];
            $l['zone'] = $areaParent[$l['zone']] ?? $l['zone'];
            $zoneId    = $goSpawns[$l['entry']]  ?? 0;
            if ($l['zone'] != $zoneId)
                $zoneId = 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_FISHING, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, 0, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_FISHING, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_FISHING, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, 0, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_FISHING, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, 0, $l['qty']);
        }
    }

    private function itemGathering() : void
    {
        CLI::write('[source] * #17 Gathered', CLI::LOG_BLANK, true, true);

        $herbLoot = DB::World()->select(
           'SELECT    src.`itemOrRef` AS "refOrItem", src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty", src.`srcType`
            FROM      (SELECT ct.`entry`, IF(slt.`Reference` > 0, -slt.`Reference`, slt.`Item`) `itemOrRef`, ?d AS "srcType" FROM creature_template   ct JOIN skinning_loot_template   slt ON slt.`entry` = ct.`skinloot` WHERE (`type_flags` & ?d) AND ct.`skinloot` > 0 UNION
                       SELECT gt.`entry`, IF(glt.`Reference` > 0, -glt.`Reference`, glt.`Item`) `itemOrRef`, ?d AS "srcType" FROM gameobject_template gt JOIN gameobject_loot_template glt ON glt.`entry` = gt.`data1`    WHERE gt.`type` = ?d AND gt.`data1` > 0 AND gt.`data0` IN (?a)) src
            LEFT JOIN item_template it ON src.itemOrRef > 0 AND src.`itemOrRef` = it.`entry`
            GROUP BY  `refOrItem`, src.`entry`',
            Type::NPC, NPC_TYPEFLAG_SKIN_WITH_HERBALISM,
            Type::OBJECT, OBJECT_CHEST, DB::Aowow()->selectCol('SELECT `id` FROM dbc_lock WHERE `properties1` = ?d', LOCK_PROPERTY_HERBALISM)
        );

        if (!$herbLoot)
        {
            CLI::write('[source] itemGathering() - skinning_loot_template/creature_template contained no loot/herb-lootable creature and gameobject_template contained no herbs (type:3).', CLI::LOG_WARN);
            return;
        }

        $spawns[Type::OBJECT] = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `typeId`', Type::OBJECT, array_column(array_filter($herbLoot, function($x) { return $x['srcType'] == Type::OBJECT; }), 'entry'));
        $spawns[Type::NPC]    = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `typeId`', Type::NPC,    array_column(array_filter($herbLoot, function($x) { return $x['srcType'] == Type::NPC; }), 'entry'));

        foreach ($herbLoot as $l)
        {
            $roi   = $l['refOrItem'];
            $entry = $l['entry'];
            $mode  = 1;
            if (isset($this->dummyNPCs[$l['entry']]) && $l['srcType'] == Type::NPC)
                [$mode, $entry] = $this->dummyNPCs[$l['entry']];

            $zoneId = $spawns[$l['srcType']][$l['entry']] ?? 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_GATHERING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_GATHERING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_GATHERING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_GATHERING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);
        }
    }

    private function itemMilling() : void
    {
        CLI::write('[source] * #18 Milled', CLI::LOG_BLANK, true, true);

        $millLoot  = DB::World()->select(
           'SELECT    IF(mlt.`Reference` > 0, -mlt.`Reference`, mlt.`Item`) AS "refOrItem", itA.`entry`, itB.`class`, itB.`subclass`, itB.`spellid_1`, itB.`spelltrigger_1`, itB.`spellid_2`, itB.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      milling_loot_template mlt
            JOIN      item_template itA ON mlt.`entry` = itA.`entry`
            LEFT JOIN item_template itB ON itB.`entry` = mlt.`Item` AND mlt.`Reference` <= 0
            GROUP BY  `refOrItem`, itA.`entry`'
        );

        foreach ($millLoot as $l)
        {
            $roi = $l['refOrItem'];

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_MILLING);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_MILLING);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_MILLING);

            $this->pushBuffer(Type::ITEM, $roi, SRC_MILLING);
        }
    }

    private function itemMining() : void
    {
        CLI::write('[source] * #19 Mined', CLI::LOG_BLANK, true, true);

        $mineLoot = DB::World()->select(
           'SELECT      src.`itemOrRef` AS "refOrItem", src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty", src.`srcType`
            FROM        (SELECT ct.`entry`, IF(slt.`Reference` > 0, -slt.`Reference`, slt.`Item`) `itemOrRef`, ?d AS "srcType" FROM creature_template   ct JOIN skinning_loot_template   slt ON slt.`entry` = ct.`skinloot` WHERE (`type_flags` & ?d) AND ct.`skinloot` > 0 UNION
                         SELECT gt.`entry`, IF(glt.`Reference` > 0, -glt.`Reference`, glt.`Item`) `itemOrRef`, ?d AS "srcType" FROM gameobject_template gt JOIN gameobject_loot_template glt ON glt.`entry` = gt.`data1`    WHERE gt.`type` = ?d AND gt.`data1` > 0 AND gt.`data0` IN (?a)) src
            LEFT JOIN   item_template it ON src.itemOrRef > 0 AND src.`itemOrRef` = it.`entry`
            GROUP BY    `refOrItem`, src.`entry`',
            Type::NPC, NPC_TYPEFLAG_SKIN_WITH_MINING,
            Type::OBJECT, OBJECT_CHEST, DB::Aowow()->selectCol('SELECT `id` FROM dbc_lock WHERE `properties1` = ?d', LOCK_PROPERTY_MINING)
        );

        if (!$mineLoot)
        {
            CLI::write('[source] itemMining() - skinning_loot_template/creature_template contained no loot/mining-lootable creature and gameobject_template contained no nodes (type:3).', CLI::LOG_WARN);
            return;
        }

        $spawns[Type::OBJECT] = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::OBJECT, array_column(array_filter($mineLoot, function($x) { return $x['srcType'] == Type::OBJECT; }), 'entry'));
        $spawns[Type::NPC]    = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC,    array_column(array_filter($mineLoot, function($x) { return $x['srcType'] == Type::NPC; }), 'entry'));

        foreach ($mineLoot as $l)
        {
            $roi   = $l['refOrItem'];
            $entry = $l['entry'];
            $mode  = 1;
            if (isset($this->dummyNPCs[$l['entry']]) && $l['srcType'] == Type::NPC)
                [$mode, $entry] = $this->dummyNPCs[$l['entry']];

            $zoneId = $spawns[$l['srcType']][$l['entry']] ?? 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_MINING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_MINING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_MINING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_MINING, $mode, $l['qty'] > 1 ? 0 : $l['srcType'], $entry, $zoneId, 0, $l['qty']);
        }
    }

    private function itemProspecting() : void
    {
        CLI::write('[source] * #20 Prospected', CLI::LOG_BLANK, true, true);

        $prospectLoot = DB::World()->select(
           'SELECT    IF(plt.`Reference` > 0, -plt.`Reference`, plt.`Item`) AS "refOrItem", itA.`entry`, itB.`class`, itB.`subclass`, itB.`spellid_1`, itB.`spelltrigger_1`, itB.`spellid_2`, itB.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      prospecting_loot_template plt
            JOIN      item_template itA ON plt.`entry` = itA.`entry`
            LEFT JOIN item_template itB ON itB.`entry` = plt.`Item` AND plt.`Reference` <= 0
            GROUP BY  `refOrItem`, itA.`entry`'
        );

        foreach ($prospectLoot as $roi => $l)
        {
            $roi = $l['refOrItem'];

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_PROSPECTING);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_PROSPECTING);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_PROSPECTING);

            $this->pushBuffer(Type::ITEM, $roi, SRC_PROSPECTING);
        }
    }

    private function itemPickpocketing() : void
    {
        CLI::write('[source] * #21 Pickpocket', CLI::LOG_BLANK, true, true);

        $theftLoot = DB::World()->select(
           'SELECT    src.`itemOrRef` AS "refOrItem", src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      (SELECT ct.`entry`, IF(plt.`Reference` > 0, -plt.`Reference`, plt.`Item`) `itemOrRef` FROM creature_template ct JOIN pickpocketing_loot_template plt ON plt.`entry` = ct.`pickpocketloot` WHERE ct.`pickpocketloot` > 0) src
            LEFT JOIN item_template it ON src.`itemOrRef` > 0 AND src.`itemOrRef` = it.`entry`
            GROUP BY  `refOrItem`, src.`entry`'
        );

        if (!$theftLoot)
        {
            CLI::write('[source] itemPickpocketing() - pickpocketing_loot_template/creature_template contained no loot/generous creature.', CLI::LOG_WARN);
            return;
        }

        $spawns = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($theftLoot, 'entry')));

        foreach ($theftLoot as $l)
        {
            $roi   = $l['refOrItem'];
            $entry = $l['entry'];
            $mode  = 1;
            if (isset($this->dummyNPCs[$l['entry']]))
                [$mode, $entry] = $this->dummyNPCs[$l['entry']];

            $zoneId = $spawns[$l['entry']] ?? 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_PICKPOCKETING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_PICKPOCKETING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_PICKPOCKETING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_PICKPOCKETING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);
        }
    }

    private function itemSalvaging() : void
    {
        CLI::write('[source] * #22 Salvaged', CLI::LOG_BLANK, true, true);

        $salvageLoot = DB::World()->select(
           'SELECT    src.`itemOrRef` AS "refOrItem", src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      (SELECT ct.`entry`, IF(slt.`Reference` > 0, -slt.`Reference`, slt.`Item`) `itemOrRef` FROM creature_template ct JOIN skinning_loot_template slt ON slt.`entry` = ct.`skinloot` WHERE (`type_flags` & ?d) AND ct.`skinloot` > 0) src
            LEFT JOIN item_template it ON src.`itemOrRef` > 0 AND src.`itemOrRef` = it.`entry`
            GROUP BY  `refOrItem`, src.`entry`',
            NPC_TYPEFLAG_SKIN_WITH_ENGINEERING
        );

        if (!$salvageLoot)
        {
            CLI::write('[source] itemSalvaging() - skinning_loot_template/creature_template contained no loot/salvageable creature.', CLI::LOG_WARN);
            return;
        }

        $spawns = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($salvageLoot, 'entry')));

        foreach ($salvageLoot as $l)
        {
            $roi   = $l['refOrItem'];
            $entry = $l['entry'];
            $mode  = 1;
            if (isset($this->dummyNPCs[$l['entry']]))
                [$mode, $entry] = $this->dummyNPCs[$l['entry']];

            $zoneId = $spawns[$l['entry']] ?? 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_SALVAGING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_SALVAGING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_SALVAGING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_SALVAGING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);
        }
    }

    private function itemSkinning() : void
    {
        CLI::write('[source] * #23 Skinned', CLI::LOG_BLANK, true, true);

        $skinLoot  = DB::World()->select(
           'SELECT    src.`itemOrRef` AS "refOrItem", src.`entry`, it.`class`, it.`subclass`, it.`spellid_1`, it.`spelltrigger_1`, it.`spellid_2`, it.`spelltrigger_2`, COUNT(1) AS "qty"
            FROM      (SELECT ct.`entry`, IF(slt.`Reference` > 0, -slt.`Reference`, slt.`Item`) `itemOrRef` FROM creature_template ct JOIN skinning_loot_template slt ON slt.`entry` = ct.`skinloot` WHERE (`type_flags` & ?d) = 0 AND ct.`skinloot` > 0 AND ct.`type` <> 13) src
            LEFT JOIN item_template it ON src.`itemOrRef` > 0 AND src.`itemOrRef` = it.`entry`
            GROUP BY  `refOrItem`, src.`entry`',
            NPC_TYPEFLAG_SPECIALLOOT
        );

        if (!$skinLoot)
        {
            CLI::write('[source] itemSkinning() - skinning_loot_template/creature_template contained no loot/skinable beast.', CLI::LOG_WARN);
            return;
        }

        $spawns = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(COUNT(DISTINCT `areaId`) > 1, 0, `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($skinLoot, 'entry')));

        foreach ($skinLoot as $l)
        {
            $roi   = $l['refOrItem'];
            $entry = $l['entry'];
            $mode  = 1;
            if (isset($this->dummyNPCs[$l['entry']]))
                [$mode, $entry] = $this->dummyNPCs[$l['entry']];

            $zoneId = $spawns[$l['entry']] ?? 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_SKINNING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);

                    $this->pushBuffer(Type::ITEM, $iId, SRC_SKINNING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_SKINNING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_SKINNING, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, 0, $l['qty']);
        }
    }

    private function spellQuest() : void
    {
        CLI::write('[source] * #4  Quest', CLI::LOG_BLANK, true, true);

        $quests = DB::World()->select(
           'SELECT   `spell` AS ARRAY_KEY, `ID` AS "id", SUM(`qty`) AS "qty", BIT_OR(`side`) AS "side", IF(COUNT(DISTINCT `zone`) > 1, 0, `zone`) AS "zone"
            FROM     (SELECT IF(`RewardSpell` = 0, `RewardDisplaySpell`, `RewardSpell`) AS "spell",    `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template                                                    WHERE IF(`RewardSpell` = 0, `RewardDisplaySpell`, `RewardSpell`) > 0 UNION ALL
                      SELECT                                        qta.`SourceSpellId` AS "spell", qt.`ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template qt JOIN quest_template_addon qta ON qta.ID = qt.ID WHERE qta.`SourceSpellId` > 0                                       ) t
          { WHERE    t.`ID` NOT IN (?a) }
            GROUP BY `spell`',
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            !empty($this->disables[Type::QUEST]) ? array_values($this->disables[Type::QUEST]) : DBSIMPLE_SKIP
        );

        if (!$quests)
        {
            CLI::write('[source] spellQuest() - quest_template contained no spell rewards or grants.', CLI::LOG_WARN);
            return;
        }

        $areaParent = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `parentArea` FROM ?_zones WHERE `id` IN (?a) AND `parentArea` > 0', array_filter(array_column($quests, 'zone')));
        $qSpells    = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `effect1Id`, `effect2Id`, `effect3Id`, `effect1TriggerSpell`, `effect2TriggerSpell`, `effect3TriggerSpell` FROM dbc_spell WHERE `id` IN (?a) AND (`effect1Id` = ?d OR `effect2Id` = ?d OR `effect3Id` = ?d)',
            array_keys($quests), SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_SPELL
        );

        foreach ($qSpells as $sId => $spell)
            for ($i = 1; $i <= 3; $i++)
                if ($spell['effect'.$i.'Id'] == SPELL_EFFECT_LEARN_SPELL)
                    $this->pushBuffer(Type::SPELL, $spell['effect'.$i.'TriggerSpell'], SRC_QUEST, $quests[$sId]['side'], $quests[$sId]['qty'] > 1 ? 0 : Type::QUEST, $quests[$sId]['id'], $areaParent[$quests[$sId]['zone']] ?? Game::$questSortFix[$quests[$sId]['zone']] ?? $quests[$sId]['zone']);
    }

    private function spellTrainer() : void
    {
        CLI::write('[source] * #6  Trainer', CLI::LOG_BLANK, true, true);

        $tNpcs = DB::World()->select('SELECT `SpellID` AS ARRAY_KEY, cdt.`CreatureId` AS "entry", COUNT(1) AS "qty" FROM trainer_spell ts JOIN creature_default_trainer cdt ON cdt.`TrainerId` = ts.`TrainerId` GROUP BY ARRAY_KEY');
        if (!$tNpcs)
        {
            CLI::write('[source] spelltrainer() - trainer_spell contained no spell or creature_default_trainer no trainer.', CLI::LOG_WARN);
            return;
        }

        // note: for consistency you could check for boss dummys and get the zone where the trainer resides, but seriously. Whats wrong with you‽

        $tSpells = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `effect1Id`, `effect2Id`, `effect3Id`, `effect1TriggerSpell`, `effect2TriggerSpell`, `effect3TriggerSpell` FROM dbc_spell WHERE `id` IN (?a)', array_keys($tNpcs));

        // todo (med): this skips some spells (e.g. riding)
        foreach ($tNpcs as $spellId => $npc)
        {
            if (!isset($tSpells[$spellId]))
                continue;

            $effects   = $tSpells[$spellId];
            $trainerId = $npc['qty'] > 1 ? 0 : $npc['entry'];

            $triggered = false;
            for ($i = 1; $i <= 3; $i++)
            {
                if ($effects['effect'.$i.'Id'] != SPELL_EFFECT_LEARN_SPELL)
                    continue;

                $triggered = true;
                $this->pushBuffer(Type::SPELL, $effects['effect'.$i.'TriggerSpell'], SRC_TRAINER, 1, $trainerId ? Type::NPC : 0, $trainerId);
            }

            if (!$triggered)
                $this->pushBuffer(Type::SPELL, $spellId, SRC_TRAINER, 1, $trainerId ? Type::NPC : 0, $trainerId);
        }
    }

    private function spellDiscovery() : void
    {
        CLI::write('[source] * #7  Discovery', CLI::LOG_BLANK, true, true);

        // 61756: Northrend Inscription Research (FAST QA VERSION);
        if ($disco = DB::World()->selectCol('SELECT `spellId` FROM skill_discovery_template WHERE `reqSpell` <> ?d', 61756))
            foreach ($disco as $d)
                $this->pushBuffer(Type::SPELL, $d, SRC_DISCOVERY);
    }

    private function spellTalent() : void
    {
        CLI::write('[source] * #9  Talent', CLI::LOG_BLANK, true, true);

        $tSpells = DB::Aowow()->select(
           'SELECT s.`id` AS ARRAY_KEY, s.`effect1Id`, s.`effect2Id`, s.`effect3Id`, s.`effect1TriggerSpell`, s.`effect2TriggerSpell`, s.`effect3TriggerSpell`
            FROM   dbc_talent t
            JOIN   dbc_spell s ON s.`id` = t.`rank1`
            WHERE  t.`rank2` < 1 AND (t.`talentSpell` = 1 OR (s.`effect1Id` = ?d OR s.`effect2Id` = ?d OR s.`effect3Id` = ?d))',
            SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_SPELL, SPELL_EFFECT_LEARN_SPELL
        );

        $n = 0;
        while ($tSpells)
        {
            CLI::write('[source]   - '.++$n.'. pass', CLI::LOG_BLANK, true, true);

            $recurse = [];
            foreach ($tSpells as $tId => $spell)
            {
                for ($i = 1; $i <= 3; $i++)
                    if ($spell['effect'.$i.'Id'] == SPELL_EFFECT_LEARN_SPELL)
                        $recurse[$spell['effect'.$i.'TriggerSpell']] = $tId;

                if (array_search($tId, $recurse))
                    unset($tSpells[$tId]);
            }

            foreach ($tSpells as $tId => $__)
                $this->pushBuffer(Type::SPELL, $tId, SRC_TALENT);

            if (!$recurse)
                break;

            $tSpells = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `effect1Id`, `effect2Id`, `effect3Id`, `effect1TriggerSpell`, `effect2TriggerSpell`, `effect3TriggerSpell` FROM dbc_spell WHERE `id` IN (?a)', array_keys($recurse));
        }
    }

    private function spellStarter() : void
    {
        /* acquireMethod
            ABILITY_LEARNED_ON_GET_PROFESSION_SKILL     = 1,        learnedAt = 1 && source10 = 1
            ABILITY_LEARNED_ON_GET_RACE_OR_CLASS_SKILL  = 2
        */
        CLI::write('[source] * #10 Starter', CLI::LOG_BLANK, true, true);

        $pcis = DB::World()->selectCol('SELECT DISTINCT `skill` FROM playercreateinfo_skills');
        $subSkills = DB::Aowow()->selectCol('SELECT `spellId` FROM dbc_skilllineability WHERE {(`skillLineId` IN (?a) AND `acquireMethod` = 2) OR} (`acquireMethod` = 1 AND (`reqSkillLevel` = 1 OR `skillLineId` = ?d)) GROUP BY `spellId`', $pcis ?: DBSIMPLE_SKIP, SKILL_FIRST_AID);
        foreach ($subSkills as $s)
            $this->pushBuffer(Type::SPELL, $s, SRC_STARTER);
    }

    private function titleQuest() : void
    {
        CLI::write('[source] * #4  Quest', CLI::LOG_BLANK, true, true);

        $quests = DB::World()->select(
           'SELECT   `RewardTitle` AS ARRAY_KEY, `ID` AS "id", SUM(`qty`) AS "qty", BIT_OR(`side`) AS "side", IF(COUNT(DISTINCT `zone`) > 1, 0, `zone`) AS "zone"
            FROM     (SELECT `RewardTitle`, `ID`, 1 AS "qty", IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, IF(`AllowableRaces` & ?d AND NOT (`AllowableRaces` & ?d), ?d, ?d)) AS "side", GREATEST(`QuestSortID`, 0) AS "zone" FROM quest_template WHERE `RewardTitle` > 0) q
          { WHERE    q.`Id` NOT IN (?a) }
            GROUP BY `RewardTitle`',
            ChrRace::MASK_HORDE, ChrRace::MASK_ALLIANCE, SIDE_HORDE, ChrRace::MASK_ALLIANCE, ChrRace::MASK_HORDE, SIDE_ALLIANCE, SIDE_BOTH,
            !empty($this->disables[Type::QUEST]) ? array_values($this->disables[Type::QUEST]) : DBSIMPLE_SKIP
        );

        if (!$quests)
        {
            CLI::write('[source] titleQuest() - quest_template contained no title rewards.', CLI::LOG_WARN);
            return;
        }

        $areaParent = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `parentArea` FROM ?_zones WHERE `id` IN (?a) AND parentArea > 0', array_filter(array_column($quests, 'zone')));

        foreach ($quests as $titleId => $q)
            $this->pushBuffer(Type::TITLE, $titleId, SRC_QUEST, $q['side'], $q['qty'] > 1 ? 0 : Type::QUEST, $q['id'], $areaParent[$q['zone']] ?? Game::$questSortFix[$q['zone']] ?? $q['zone']);
    }

    private function titleAchievement() : void
    {
        CLI::write('[source] * #12 Achievement', CLI::LOG_BLANK, true, true);

        $sets = DB::World()->select(
           'SELECT   `titleId` AS ARRAY_KEY, MIN(`ID`) AS "srcId", NULLIF(MAX(`ID`), MIN(`ID`)) AS "altSrcId"
            FROM     (SELECT `TitleA` AS "titleId", `ID` FROM achievement_reward WHERE `TitleA` <> 0 UNION
                      SELECT `TitleH` AS "titleId", `ID` FROM achievement_reward WHERE `TitleH` <> 0) AS x
            GROUP BY `titleId`'
        );

        foreach ($sets as $tId => $set)
        {
            $this->pushBuffer(Type::TITLE, $tId, SRC_ACHIEVEMENT, 1, Type::ACHIEVEMENT, $set['srcId']);

            if ($set['altSrcId'])
                DB::Aowow()->query('UPDATE ?_titles SET src12Ext = ?d WHERE id = ?d', $set['altSrcId'], $tId);
        }
    }

    private function titleCustomString() : void
    {
        CLI::write('[source] * #13 cuStrings', CLI::LOG_BLANK, true, true);

        foreach (Lang::game('pvpSources') as $src => $__)
            $this->pushBuffer(Type::TITLE, $src, SRC_CUSTOM_STRING, $src);
    }

    private function itemset() : void
    {
        // every item in ?_itemset needs a source. if so merge fields. if not it's not available.

        $sets = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `contentGroup`, `item1`, `item2`, `item3`, `item4`, `item5`, `item6`, `item7`, `item8`, `item9`, `item10` FROM ?_itemset');

        $metaSrc = [];
        foreach ($sets as $id => $set)
        {
            $available = true;
            for ($i = 1; $i < 11; $i++)
            {
                if (!$set['item'.$i])
                    continue;

                if (!isset($this->srcBuffer[Type::ITEM][$set['item'.$i]]))
                {
                    // Todo: remove HACK - neck in Zul'Gurub set ist created by spell from onUse item trigger
                    if ($set['contentGroup'] == 11)
                        continue;

                    $available = false;
                    break;
                }

                $metaSrc[$id] = ($metaSrc[$id] ?? []) + $this->srcBuffer[Type::ITEM][$set['item'.$i]][6];
            }

            if (!$available)
                unset($metaSrc[$id]);
        }

        foreach($metaSrc as $id => $bits)
            foreach ($bits as $src => $bit)
                $this->pushBuffer(Type::ITEMSET, $id, $src, $bit);
    }
});

?>
