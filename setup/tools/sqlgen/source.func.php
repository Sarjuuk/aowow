<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'source';

    protected $tblDependancyAowow = ['spell', 'achievement', 'items', 'itemset', 'spawns', 'creature', 'zones', 'titles'];
    protected $tblDependancyTC    = ['playercreateinfo_skills', 'playercreateinfo_item', 'skill_discovery_template', 'achievement_reward', 'skill_perfect_item_template', 'item_template', 'gameobject_template', 'quest_template', 'quest_template_addon', 'creature_template', 'creature', 'npc_trainer', 'npc_vendor', 'game_event_npc_vendor', 'reference_loot_template', 'item_loot_template', 'creature_loot_template', 'gameobject_loot_template', 'mail_loot_template', 'disenchant_loot_template', 'fishing_loot_template', 'skinning_loot_template', 'milling_loot_template', 'prospecting_loot_template', 'pickpocketing_loot_template'];
    protected $dbcSourceFiles     = ['charstartoutfit', 'talent', 'spell', 'skilllineability', 'itemextendedcost', 'lock'];

    private $srcBuffer = [];
    private $refLoot   = [];
    private $dummyNPCs = [];

    private const PVP_MONEY        = [26045, 24581, 24579, 43589, 37836]; // Nagrand, Hellfire Pen. H, Hellfire Pen. A, Wintergrasp, Grizzly Hills
    private const COMMON_THRESHOLD = 100;

    public function generate(array $ids = []) : bool
    {
        /*********************************/
        /* resolve difficulty dummy NPCS */
        /*********************************/

        $this->dummyNPCs = DB::Aowow()->select(
           'SELECT difficultyEntry1 AS ARRAY_KEY, 2 AS "0", `id` AS "1" FROM ?_creature WHERE difficultyEntry1 > 0 UNION
            SELECT difficultyEntry2 AS ARRAY_KEY, 4 AS "0", `id` AS "1" FROM ?_creature WHERE difficultyEntry2 > 0 UNION
            SELECT difficultyEntry3 AS ARRAY_KEY, 8 AS "0", `id` AS "1" FROM ?_creature WHERE difficultyEntry3 > 0'
        );
        // todo: do the same for GOs

        CLI::write(' - resolving ref-loot tree', CLI::LOG_BLANK, true, true);
        $this->refLoot = DB::World()->select(
           'SELECT    rlt.Entry AS ARRAY_KEY, IF(Reference, -Reference, Item) AS ARRAY_KEY2, it.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2
            FROM      reference_loot_template rlt
            LEFT JOIN item_template it ON rlt.Reference = 0 AND rlt.Item = it.entry
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

        CLI::write(' - Items & Spells [inherited]');
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

        CLI::write(' - Spells [original]');

        $this->spellQuest();                                #  4: Quest     #
        $this->spellTrainer();                              #  6: Trainer   #
        $this->spellDiscovery();                            #  7: Discovery #
        $this->spellTalent();                               #  9: Talent    #
        $this->spellStarter();                              # 10: Starter   #

        /**********/
        /* Titles */
        /**********/

        CLI::write(' - Titles');

        $this->titleQuest();                                #  4: Quest         #
        $this->titleAchievement();                          # 12: Achievement   #
        $this->titleCustomString();                         # 13: Source-String #

        /************/
        /* Itemsets */
        /************/

        CLI::write(' - Itemsets');

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
                    CLI::write(' - Inserting... (['.$type.'] '.$j.' / '.$sum.')', CLI::LOG_BLANK, true, true);

                if (!($j % 300))
                    $this->insert($rows);
            }

            if ($rows)                                      // insert leftovers
                $this->insert($rows);
        }

        CLI::write(' - Inserting... (done)');

        // flagging aowow_items for source (note: this is not exact! creatures dropping items may not be spawnd, quests granting items may be disabled)
        DB::Aowow()->query('UPDATE ?_items SET cuFlags = cuFlags & ?d', ~CUSTOM_UNAVAILABLE);
        DB::Aowow()->query('UPDATE ?_items i LEFT JOIN ?_source s ON s.typeId = i.id AND s.type = ?d SET i.cuFlags = i.cuFlags | ?d WHERE s.typeId IS NULL', Type::ITEM, CUSTOM_UNAVAILABLE);

        return true;
    }

    private function pushBuffer(int $type, int $typeId, int $srcId, int $srcBit = 1, int $mType = 0, int $mTypeId = 0, int $mZoneId = 0, int $mMask = 0x0, int $qty = 1) : void
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
        # spelltrigger_X (0: onUse; 6: onLearnSpell)

        // should not be able to teach spells (recipe || mount || vanityPet)
        if ($item['class'] != ITEM_CLASS_RECIPE && ($item['class'] != ITEM_CLASS_MISC || ($item['subclass'] != 2 && $item['subclass'] != 5)))
            return 0;

        // wotlk system
        if (in_array($item['spellid_1'], LEARN_SPELLS) && $item['spelltrigger_1'] == 0 && $item['spellid_2'] > 0 && $item['spelltrigger_2'] == 6)
            return $item['spellid_2'];

        // deprecated system
        if (!in_array($item['spellid_1'], LEARN_SPELLS) && $item['spellid_1'] > 0 && $item['spelltrigger_1'] == 0)
            return $item['spellid_1'];

        return 0;
    }

    private function itemCrafted() : void
    {
        CLI::write('   * #1  Crafted', CLI::LOG_BLANK, true, true);

        $itemSpells = DB::Aowow()->selectCol(
           'SELECT   effect1CreateItemId AS ARRAY_KEY, s.id
            FROM     dbc_spell s
            JOIN     dbc_skilllineability sla ON s.id = sla.spellId
            WHERE    effect1CreateItemId > 0 AND sla.skillLineId IN (?a)
            GROUP BY ARRAY_KEY',
            [SKILL_BLACKSMITHING, SKILL_LEATHERWORKING, SKILL_ALCHEMY, SKILL_HERBALISM, SKILL_MINING, SKILL_TAILORING, SKILL_ENGINEERING, SKILL_ENCHANTING, SKILL_SKINNING, SKILL_JEWELCRAFTING, SKILL_INSCRIPTION, SKILL_FIRST_AID, SKILL_COOKING, SKILL_FISHING]
        );
        // assume unique craft spells per item
        $perfectItems = DB::World()->selectCol('SELECT perfectItemType AS ARRAY_KEY, spellId AS spell FROM skill_perfect_item_template');
        foreach ($perfectItems AS $item => $spell)
            $itemSpells[$item] = $spell;

        $spellItems = DB::World()->select('SELECT entry AS ARRAY_KEY, class, subclass, spellid_1, spelltrigger_1, spellid_2, spelltrigger_2 FROM item_template WHERE entry IN (?a)', array_keys($itemSpells));
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

        CLI::write('   * #2  Drop [NPC]', CLI::LOG_BLANK, true, true);

        $creatureLoot = DB::World()->select(
           'SELECT    IF(clt.Reference > 0, -clt.Reference, clt.Item) AS refOrItem, ct.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty
            FROM      creature_loot_template clt
            JOIN      creature_template ct ON clt.entry = ct.lootid
            LEFT JOIN item_template it ON it.entry = clt.Item AND clt.Reference <= 0
            WHERE     ct.lootid > 0
            GROUP BY  refOrItem, ct.entry'
        );

        $spawns = DB::Aowow()->select('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT s.areaId) > 1, 0, s.areaId) AS areaId, z.type FROM ?_spawns s JOIN ?_zones z ON z.id = s.areaId WHERE s.`type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($creatureLoot, 'entry')));
        $bosses = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, IF(cuFlags & ?d, 1, IF(typeFlags & 0x4 AND `rank` > 0, 1, 0)) FROM ?_creature WHERE id IN (?a)', NPC_CU_INSTANCE_BOSS, array_filter(array_column($creatureLoot, 'entry')));

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

            if (isset($spawns[$entry]))
            {
                switch ($spawns[$entry]['type'])
                {
                    case MAP_TYPE_DUNGEON_HC:
                        $mMask |= SRC_FLAG_DUNGEON_DROP; break;
                    case MAP_TYPE_MMODE_RAID:
                    case MAP_TYPE_MMODE_RAID_HC:
                        $mMask |= SRC_FLAG_RAID_DROP; break;
                }

                $zoneId = $spawns[$entry]['areaId'];
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
                CLI::write(' - unresolved ref-loot ref: ' . $roi, CLI::LOG_WARN);
                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, $mMask, $l['qty']);

            $this->pushBuffer(Type::ITEM, $roi, SRC_DROP, $mode, $l['qty'] > 1 ? 0 : Type::NPC, $entry, $zoneId, $mMask, $l['qty']);
        }

        CLI::write('   * #2  Drop [Object]', CLI::LOG_BLANK, true, true);

        $objectLoot = DB::World()->select(
           'SELECT    IF(glt.Reference > 0, -glt.Reference, glt.Item) AS refOrItem, gt.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty
            FROM      gameobject_loot_template glt
            JOIN      gameobject_template gt ON glt.entry = gt.Data1
            LEFT JOIN item_template it ON it.entry = glt.Item AND glt.Reference <= 0
            WHERE     `type` = 3 AND gt.Data1 > 0 AND gt.Data0 NOT IN (?a)
            GROUP BY  refOrItem, gt.entry',
            DB::Aowow()->selectCol('SELECT id FROM dbc_lock WHERE properties1 IN (2, 3)')
        );

        $spawns = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::OBJECT, array_column($objectLoot, 'entry'));

        // todo: difficulty entrys for boss chests
        foreach ($objectLoot as $l)
        {
            $roi    = $l['refOrItem'];
            $zoneId = $spawns[$l['entry']] ?? 0;
            $mMask  = 0x0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);

                    $objectOT[] = $iId;
                    $this->pushBuffer(Type::ITEM, $iId, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);

            $objectOT[] = $roi;
            $this->pushBuffer(Type::ITEM, $roi, SRC_DROP, 1, $l['qty'] > 1 ? 0 : Type::OBJECT, $l['entry'], $zoneId, $mMask, $l['qty']);
        }

        CLI::write('   * #2  Drop [Item]', CLI::LOG_BLANK, true, true);

        $itemLoot = DB::World()->select(
           'SELECT    IF(ilt.Reference > 0, -ilt.Reference, ilt.Item) AS ARRAY_KEY, itA.entry, itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2, COUNT(1) AS qty
            FROM      item_loot_template ilt
            JOIN      item_template itA ON ilt.entry = itA.entry
            LEFT JOIN item_template itB ON itB.entry = ilt.Item AND ilt.Reference <= 0
            WHERE     itA.flags & 0x4
            GROUP BY  ARRAY_KEY'
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

        DB::Aowow()->query('UPDATE ?_items SET cuFLags = cuFlags | ?d WHERE id IN (?a)', ITEM_CU_OT_ITEMLOOT,   $itemOT);
        DB::Aowow()->query('UPDATE ?_items SET cuFLags = cuFlags | ?d WHERE id IN (?a)', ITEM_CU_OT_OBJECTLOOT, $objectOT);
    }

    private function itemPvP() : void
    {
        CLI::write('   * #3  PvP', CLI::LOG_BLANK, true, true);

        $subSrcByXCost = array(
            SRC_SUB_PVP_BG    => DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqArenaPoints = 0 AND reqHonorPoints > 0'),
            SRC_SUB_PVP_ARENA => DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqArenaPoints > 0'),
            SRC_SUB_PVP_WORLD => DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqItemId1 IN (?a) OR reqItemId2 IN (?a) OR reqItemId3 IN (?a) OR reqItemId4 IN (?a) OR reqItemId5 IN (?a)', self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY)
        );
        $vendorQuery =
           'SELECT   n.item, SUM(n.qty) AS qty, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2
            FROM     (SELECT item, COUNT(1) AS qty FROM npc_vendor                                                       WHERE ExtendedCost IN (?a) GROUP BY item UNION
                      SELECT item, COUNT(1) AS qty FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid WHERE ExtendedCost IN (?a) GROUP BY item) n
            JOIN     item_template it ON it.entry = n.item
            GROUP BY item';

        foreach ($subSrcByXCost as $subSrc => $xCost)
        {
            foreach (DB::World()->select($vendorQuery, $xCost, $xCost) as $v)
            {
                if ($_ = $this->taughtSpell($v))
                    $this->pushBuffer(Type::SPELL, $_, SRC_PVP, $subSrc);

                $this->pushBuffer(Type::ITEM, $v['item'], SRC_PVP, $subSrc);
            }
        }
    }

    private function itemQuest() : void
    {
        CLI::write('   * #4  Quest', CLI::LOG_BLANK, true, true);

        $quests = DB::World()->select(
           'SELECT n.item AS ARRAY_KEY, n.ID AS quest, SUM(n.qty) AS qty, BIT_OR(n.side) AS side, IF(COUNT(DISTINCT `zone`) > 1, 0, `zone`) AS "zone", it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2
            FROM   (SELECT RewardChoiceItemID1 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardChoiceItemID1 > 0 GROUP BY item UNION
                    SELECT RewardChoiceItemID2 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardChoiceItemID2 > 0 GROUP BY item UNION
                    SELECT RewardChoiceItemID3 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardChoiceItemID3 > 0 GROUP BY item UNION
                    SELECT RewardChoiceItemID4 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardChoiceItemID4 > 0 GROUP BY item UNION
                    SELECT RewardChoiceItemID5 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardChoiceItemID5 > 0 GROUP BY item UNION
                    SELECT RewardChoiceItemID6 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardChoiceItemID6 > 0 GROUP BY item UNION
                    SELECT RewardItem1         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardItem1         > 0 GROUP BY item UNION
                    SELECT RewardItem2         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardItem2         > 0 GROUP BY item UNION
                    SELECT RewardItem3         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardItem3         > 0 GROUP BY item UNION
                    SELECT RewardItem4         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE RewardItem4         > 0 GROUP BY item UNION
                    SELECT StartItem           AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone" FROM quest_template WHERE StartItem           > 0 GROUP BY item) n
            JOIN   item_template it ON it.entry = n.item
            GROUP BY item'
        );

        $areaParent = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, parentArea FROM ?_zones WHERE id IN (?a) AND parentArea > 0', array_filter(array_column($quests, 'zone')));

        foreach ($quests as $iId => $q)
        {
            if ($q['zone'] < 0)                             // remove questSort
                $q['zone'] = 0;                             // todo: do not use questSort for zoneId, but .. questender? (starter can be item)

            if ($_ = $this->taughtSpell($q))
                $this->pushBuffer(Type::SPELL, $_, SRC_QUEST, $q['side'], $q['qty'] > 1 ? 0 : Type::QUEST, $q['quest'], $areaParent[$q['zone']] ?? $q['zone']);

            $this->pushBuffer(Type::ITEM, $iId, SRC_QUEST, $q['side'], $q['qty'] > 1 ? 0 : Type::QUEST, $q['quest'], $areaParent[$q['zone']] ?? $q['zone']);
        }

        $mailLoot = DB::World()->select(
           'SELECT    IF(mlt.Reference > 0, -mlt.Reference, mlt.Item) AS ARRAY_KEY, qt.ID AS entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty, IF(COUNT(DISTINCT QuestSortID) > 1, 0, GREATEST(QuestSortID, 0)) AS "zone", BIT_OR(IF(qt.AllowableRaces & 0x2B2 AND !(qt.AllowableRaces & 0x44D), 2, IF(qt.AllowableRaces & 0x44D AND !(qt.AllowableRaces & 0x2B2), 1, 3))) AS side
            FROM      mail_loot_template mlt
            JOIN      quest_template_addon qta ON qta.RewardMailTemplateId = mlt.entry
            JOIN      quest_template qt ON qt.ID = qta.ID
            LEFT JOIN item_template it ON it.entry = mlt.Item AND mlt.Reference <= 0
            WHERE     qta.RewardMailTemplateId > 0
            GROUP BY  ARRAY_KEY
        ');

        $areaParent = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, parentArea FROM ?_zones WHERE id IN (?a) AND parentArea > 0', array_filter(array_column($mailLoot, 'zone')));

        foreach ($mailLoot as $roi => $l)
        {
            if ($l['zone'] < 0)                             // remove questSort
                $l['zone'] = 0;

            if ($roi < 0 && !empty($this->refLoot[-$roi]))
            {
                foreach ($this->refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer(Type::SPELL, $_, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? $l['zone']);

                    $itemOT[] = $iId;
                    $this->pushBuffer(Type::ITEM, $iId, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? $l['zone']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer(Type::SPELL, $_, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? $l['zone']);

            $itemOT[] = $roi;
            $this->pushBuffer(Type::ITEM, $roi, SRC_QUEST, $l['side'], $l['qty'] > 1 ? 0 : Type::QUEST, $l['entry'], $areaParent[$l['zone']] ?? $l['zone']);
        }
    }

    private function itemVendor() : void
    {
        CLI::write('   * #5  Vendor', CLI::LOG_BLANK, true, true);

        $xCostIds = DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqHonorPoints <> 0 OR reqArenaPoints <> 0 OR reqItemId1 IN (?a) OR reqItemId2 IN (?a) OR reqItemId3 IN (?a) OR reqItemId4 IN (?a) OR reqItemId5 IN (?a)', self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY, self::PVP_MONEY);
        $vendors  = DB::World()->select(
           'SELECT   n.item, n.npc, SUM(n.qty) AS qty, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2
            FROM     (SELECT item, entry AS npc, COUNT(1) AS qty FROM npc_vendor                                                       WHERE ExtendedCost NOT IN (?a) GROUP BY item, npc UNION
                      SELECT item,  c.id AS npc, COUNT(1) AS qty FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid WHERE ExtendedCost NOT IN (?a) GROUP BY item, npc) n
            JOIN     item_template it ON it.entry = n.item
            GROUP BY item, npc',
            $xCostIds, $xCostIds
        );

        $spawns = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_column($vendors, 'npc'));

        foreach ($vendors as $v)
        {
            if ($_ = $this->taughtSpell($v))
                $this->pushBuffer(Type::SPELL, $_, SRC_VENDOR, 1, $v['qty'] > 1 ? 0 : Type::NPC, $v['npc'], $spawns[$v['npc']] ?? 0, 0, $v['qty']);

            $this->pushBuffer(Type::ITEM, $v['item'], SRC_VENDOR, 1, $v['qty'] > 1 ? 0 : Type::NPC, $v['npc'], $spawns[$v['npc']] ?? 0, 0, $v['qty']);
        }
    }

    private function itemStarter() : void
    {
        CLI::write('   * #10 Starter', CLI::LOG_BLANK, true, true);

        if ($pcii = DB::World()->selectCol('SELECT DISTINCT itemid FROM playercreateinfo_item'))
            foreach ($pcii as $item)
                $this->pushBuffer(Type::ITEM, $item, SRC_STARTER);

        for ($i = 1; $i < 21; $i++)
            if ($cso = DB::Aowow()->selectCol('SELECT item?d FROM dbc_charstartoutfit WHERE item?d > 0', $i, $i))
                foreach ($cso as $item)
                    $this->pushBuffer(Type::ITEM, $item, SRC_STARTER);
    }

    private function itemAchievement() : void
    {
        CLI::write('   * #12 Achievement', CLI::LOG_BLANK, true, true);

        $xItems   = DB::Aowow()->select('SELECT id AS entry, itemExtra AS ARRAY_KEY, COUNT(1) AS qty FROM ?_achievement WHERE itemExtra > 0 GROUP BY itemExtra');

        $rewItems  = DB::World()->select(
           'SELECT     src.item AS ARRAY_KEY, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty
            FROM      (SELECT    IFNULL(IF(mlt.Reference > 0, -mlt.Reference, mlt.Item), ar.ItemID) AS item, ar.ID AS entry
                       FROM      achievement_reward ar
                       LEFT JOIN mail_loot_template mlt ON mlt.entry = ar.MailTemplateID
                       WHERE     ar.MailTemplateID > 0 OR ar.ItemID > 0) src
            LEFT JOIN item_template it ON src.item = it.entry
            GROUP BY  ARRAY_KEY'
        );

        if (empty($xItems))
            CLI::write('    SourceGen: itemAchievement() - Reward items are unexpectedly empty.', CLI::LOG_WARN);
        else
        {
            $extraItems = DB::World()->select('SELECT entry AS ARRAY_KEY, class, subclass, spellid_1, spelltrigger_1, spellid_2, spelltrigger_2 FROM item_template WHERE entry IN (?a)', array_keys($xItems));
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
        CLI::write('   * #15 Disenchanted', CLI::LOG_BLANK, true, true);

        $deLoot = DB::World()->select(
           'SELECT    IF(dlt.Reference > 0, -dlt.Reference, dlt.Item) AS refOrItem, itA.entry, itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2, COUNT(1) AS qty
            FROM      disenchant_loot_template dlt
            JOIN      item_template itA ON dlt.entry = itA.DisenchantId
            LEFT JOIN item_template itB ON itB.entry = dlt.Item AND dlt.Reference <= 0
            WHERE     itA.DisenchantId > 0
            GROUP BY  refOrItem, itA.entry'
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
        CLI::write('   * #16 Fished', CLI::LOG_BLANK, true, true);

        $fishLoot = DB::World()->select(
           'SELECT    src.itemOrRef AS refOrItem, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty, IF(COUNT(DISTINCT `zone`) > 2, 0, MAX(`zone`)) AS "zone"
            FROM      (SELECT 0 AS entry, IF(flt.Reference > 0, -flt.Reference, flt.Item) AS itemOrRef, entry AS "zone" FROM fishing_loot_template flt UNION
                       SELECT   gt.entry, IF(glt.Reference > 0, -glt.Reference, glt.Item) AS itemOrRef,     0 AS "zone" FROM gameobject_template   gt  JOIN gameobject_loot_template glt ON glt.entry = gt.Data1 WHERE `type` = 25 AND gt.Data1 > 0) src
            LEFT JOIN item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY  refOrItem, src.entry'
        );

        if (!$fishLoot)
        {
            CLI::write('    SourceGen: itemFishing() - fishing_loot_template empty and gameobject_template contained no fishing nodes (type:25).', CLI::LOG_WARN);
            return;
        }

        $goSpawns   = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::OBJECT, array_filter(array_column($fishLoot, 'entry')));
        $areaParent = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, parentArea FROM ?_zones WHERE id IN (?a) AND parentArea > 0', array_filter(array_column($fishLoot, 'zone')));

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
        CLI::write('   * #17 Gathered', CLI::LOG_BLANK, true, true);

        $herbLoot = DB::World()->select(
           'SELECT    src.itemOrRef AS refOrItem, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty, src.srcType
            FROM      (SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef, ?d AS srcType FROM creature_template   ct JOIN skinning_loot_template   slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) AND ct.skinloot > 0 UNION
                       SELECT gt.entry, IF(glt.Reference > 0, -glt.Reference, glt.Item) itemOrRef, ?d AS srcType FROM gameobject_template gt JOIN gameobject_loot_template glt ON glt.entry = gt.Data1    WHERE gt.`type` = 3 AND gt.Data1 > 0 AND gt.Data0 IN (?a)) src
            LEFT JOIN item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY  refOrItem, src.entry',
            Type::NPC, NPC_TYPEFLAG_HERBLOOT,
            Type::OBJECT, DB::Aowow()->selectCol('SELECT id FROM dbc_lock WHERE properties1 = 2')
        );

        if (!$herbLoot)
        {
            CLI::write('    SourceGen: itemGathering() - skinning_loot_template/creature_template contained no loot/herb-lootable creature and gameobject_template contained no herbs (type:3).', CLI::LOG_WARN);
            return;
        }

        $spawns[Type::OBJECT] = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `typeId`', Type::OBJECT, array_column(array_filter($herbLoot, function($x) { return $x['srcType'] == Type::OBJECT; }), 'entry'));
        $spawns[Type::NPC]    = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `typeId`', Type::NPC,    array_column(array_filter($herbLoot, function($x) { return $x['srcType'] == Type::NPC; }), 'entry'));

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
        CLI::write('   * #18 Milled', CLI::LOG_BLANK, true, true);

        $millLoot  = DB::World()->select(
           'SELECT    IF(mlt.Reference > 0, -mlt.Reference, mlt.Item) AS refOrItem, itA.entry, itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2, COUNT(1) AS qty
            FROM      milling_loot_template mlt
            JOIN      item_template itA ON mlt.entry = itA.entry
            LEFT JOIN item_template itB ON itB.entry = mlt.Item AND mlt.Reference <= 0
            GROUP BY  refOrItem, itA.entry'
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
        CLI::write('   * #19 Mined', CLI::LOG_BLANK, true, true);

        $mineLoot = DB::World()->select(
           'SELECT      src.itemOrRef AS refOrItem, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty, src.srcType
            FROM        (SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef, ?d AS srcType FROM creature_template   ct JOIN skinning_loot_template   slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) AND ct.skinloot > 0 UNION
                         SELECT gt.entry, IF(glt.Reference > 0, -glt.Reference, glt.Item) itemOrRef, ?d AS srcType FROM gameobject_template gt JOIN gameobject_loot_template glt ON glt.entry = gt.Data1    WHERE gt.`type` = 3 AND gt.Data1 > 0 AND gt.Data0 IN (?a)) src
            LEFT JOIN   item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY    refOrItem, src.entry',
            Type::NPC, NPC_TYPEFLAG_MININGLOOT,
            Type::OBJECT, DB::Aowow()->selectCol('SELECT id FROM dbc_lock WHERE properties1 = 3')
        );

        if (!$mineLoot)
        {
            CLI::write('    SourceGen: itemMining() - skinning_loot_template/creature_template contained no loot/mining-lootable creature and gameobject_template contained no nodes (type:3).', CLI::LOG_WARN);
            return;
        }

        $spawns[Type::OBJECT] = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::OBJECT, array_column(array_filter($mineLoot, function($x) { return $x['srcType'] == Type::OBJECT; }), 'entry'));
        $spawns[Type::NPC]    = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC,    array_column(array_filter($mineLoot, function($x) { return $x['srcType'] == Type::NPC; }), 'entry'));

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
        CLI::write('   * #20 Prospected', CLI::LOG_BLANK, true, true);

        $prospectLoot = DB::World()->select(
           'SELECT    IF(plt.Reference > 0, -plt.Reference, plt.Item) AS refOrItem, itA.entry, itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2, COUNT(1) AS qty
            FROM      prospecting_loot_template plt
            JOIN      item_template itA ON plt.entry = itA.entry
            LEFT JOIN item_template itB ON itB.entry = plt.Item AND plt.Reference <= 0
            GROUP BY  refOrItem, itA.entry'
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
        CLI::write('   * #21 Pickpocket', CLI::LOG_BLANK, true, true);

        $theftLoot = DB::World()->select(
           'SELECT    src.itemOrRef AS refOrItem, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty
            FROM      (SELECT ct.entry, IF(plt.Reference > 0, -plt.Reference, plt.Item) itemOrRef FROM creature_template ct JOIN pickpocketing_loot_template plt ON plt.entry = ct.pickpocketloot WHERE ct.pickpocketloot > 0) src
            LEFT JOIN item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY  refOrItem, src.entry'
        );

        if (!$theftLoot)
        {
            CLI::write('    SourceGen: itemPickpocketing() - pickpocketing_loot_template/creature_template contained no loot/generous creature.', CLI::LOG_WARN);
            return;
        }

        $spawns = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($theftLoot, 'entry')));

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
        CLI::write('   * #22 Salvaged', CLI::LOG_BLANK, true, true);

        $salvageLoot = DB::World()->select(
           'SELECT    src.itemOrRef AS refOrItem, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty
            FROM      (SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef FROM creature_template ct JOIN skinning_loot_template slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) AND ct.skinloot > 0) src
            LEFT JOIN item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY  refOrItem, src.entry',
            NPC_TYPEFLAG_ENGINEERLOOT
        );

        if (!$salvageLoot)
        {
            CLI::write('    SourceGen: itemSalvaging() - skinning_loot_template/creature_template contained no loot/salvageable creature.', CLI::LOG_WARN);
            return;
        }

        $spawns = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($salvageLoot, 'entry')));

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
        CLI::write('   * #23 Skinned', CLI::LOG_BLANK, true, true);

        $skinLoot  = DB::World()->select(
           'SELECT    src.itemOrRef AS refOrItem, src.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2, COUNT(1) AS qty
            FROM      (SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef FROM creature_template ct JOIN skinning_loot_template slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) = 0 AND ct.skinloot > 0 AND ct.type <> 13) src
            LEFT JOIN item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY  refOrItem, src.entry',
            (NPC_TYPEFLAG_HERBLOOT | NPC_TYPEFLAG_MININGLOOT | NPC_TYPEFLAG_ENGINEERLOOT)
        );

        if (!$skinLoot)
        {
            CLI::write('    SourceGen: itemSkinning() - skinning_loot_template/creature_template contained no loot/skinable beast.', CLI::LOG_WARN);
            return;
        }

        $spawns = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(COUNT(DISTINCT areaId) > 1, 0, areaId) FROM ?_spawns WHERE `type` = ?d AND `typeId`IN (?a) GROUP BY `typeId`', Type::NPC, array_filter(array_column($skinLoot, 'entry')));

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
        CLI::write('   * #4  Quest', CLI::LOG_BLANK, true, true);

        $quests = DB::World()->select(
           'SELECT   spell AS ARRAY_KEY, id, SUM(qty) AS qty, BIT_OR(side) AS side, IF(COUNT(DISTINCT `zone`) > 1, 0, `zone`) AS "zone"
            FROM     (SELECT IF(RewardSpell = 0, RewardDisplaySpell, RewardSpell) AS spell,    ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, GREATEST(QuestSortID, 0) AS "zone" FROM quest_template                                                     WHERE IF(RewardSpell = 0, RewardDisplaySpell, RewardSpell) > 0 GROUP BY spell UNION
                      SELECT                                    qta.SourceSpellId AS spell, qt.ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, GREATEST(QuestSortID, 0) AS "zone" FROM quest_template qt JOIN quest_template_addon qta ON qta.ID = qt.ID  WHERE qta.SourceSpellId > 0                                    GROUP BY spell) t
            GROUP BY spell'
        );

        if (!$quests)
        {
            CLI::write('    SourceGen: spellQuest() - quest_template contained no spell rewards or grants.', CLI::LOG_WARN);
            return;
        }

        $areaParent = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, parentArea FROM ?_zones WHERE id IN (?a) AND parentArea > 0', array_filter(array_column($quests, 'zone')));
        $qSpells    = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a) AND (effect1Id = 36 OR effect2Id = 36 OR effect3Id = 36)', array_keys($quests));

        foreach ($qSpells as $sId => $spell)
            for ($i = 1; $i <= 3; $i++)
                if ($spell['effect'.$i.'Id'] == SPELL_EFFECT_LEARN_SPELL)
                    $this->pushBuffer(Type::SPELL, $spell['effect'.$i.'TriggerSpell'], SRC_QUEST, $quests[$sId]['side'], $quests[$sId]['qty'] > 1 ? 0 : Type::QUEST, $quests[$sId]['id'], $areaParent[$quests[$sId]['zone']] ?? $quests[$sId]['zone']);
    }

    private function spellTrainer() : void
    {
        CLI::write('   * #6  Trainer', CLI::LOG_BLANK, true, true);

        $tNpcs = DB::World()->select('SELECT SpellID AS ARRAY_KEY, cdt.CreatureId AS entry, COUNT(1) AS qty FROM `trainer_spell` ts JOIN `creature_default_trainer` cdt ON cdt.TrainerId = ts.TrainerId GROUP BY ARRAY_KEY');

        if (!$tNpcs)
        {
            CLI::write('    SourceGen: spelltrainer() - creature_default_trainer contained no spell.', CLI::LOG_WARN);
            return;
        }

        // note: for consistency you could check for boss dummys and get the zone where the trainer resides, but seriously. Whats wrong with you‽

        $tSpells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a)', array_keys($tNpcs));

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
        CLI::write('   * #7  Discovery', CLI::LOG_BLANK, true, true);

        // 61756: Northrend Inscription Research (FAST QA VERSION);
        if ($disco = DB::World()->selectCol('SELECT spellId FROM skill_discovery_template WHERE reqSpell <> ?d', 61756))
            foreach ($disco as $d)
                $this->pushBuffer(Type::SPELL, $d, SRC_DISCOVERY);
    }

    private function spellTalent() : void
    {
        CLI::write('   * #9  Talent', CLI::LOG_BLANK, true, true);

        $tSpells = DB::Aowow()->select(
           'SELECT s.id AS ARRAY_KEY, s.effect1Id, s.effect2Id, s.effect3Id, s.effect1TriggerSpell, s.effect2TriggerSpell, s.effect3TriggerSpell
            FROM   dbc_talent t
            JOIN   dbc_spell s ON s.id = t.rank1
            WHERE  t.rank2 < 1 AND (t.talentSpell = 1 OR (s.effect1Id = 36 OR s.effect2Id = 36 OR s.effect3Id = 36))'
        );

        $n = 0;
        while ($tSpells)
        {
            CLI::write('     - '.++$n.'. pass', CLI::LOG_BLANK, true, true);

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

            $tSpells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a)', array_keys($recurse));
        }
    }

    private function spellStarter() : void
    {
        /* acquireMethod
            ABILITY_LEARNED_ON_GET_PROFESSION_SKILL     = 1,        learnedAt = 1 && source10 = 1
            ABILITY_LEARNED_ON_GET_RACE_OR_CLASS_SKILL  = 2
        */
        CLI::write('   * #10 Starter', CLI::LOG_BLANK, true, true);

        $pcis = DB::World()->selectCol('SELECT DISTINCT skill FROM playercreateinfo_skills');
        $subSkills = DB::Aowow()->selectCol('SELECT spellId FROM dbc_skilllineability WHERE {(skillLineId IN (?a) AND acquireMethod = 2) OR} (acquireMethod = 1 AND (reqSkillLevel = 1 OR skillLineId = 129)) GROUP BY spellId', $pcis ?: DBSIMPLE_SKIP);
        foreach ($subSkills as $s)
            $this->pushBuffer(Type::SPELL, $s, SRC_STARTER);
    }

    private function titleQuest() : void
    {
        CLI::write('   * #4  Quest', CLI::LOG_BLANK, true, true);

        $quests = DB::World()->select(
           'SELECT   RewardTitle AS ARRAY_KEY, id AS id, SUM(qty) AS qty, BIT_OR(side) AS side, IF(COUNT(DISTINCT `zone`) > 1, 0, `zone`) AS "zone"
            FROM     (SELECT RewardTitle, ID, 1 AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side, GREATEST(QuestSortID, 0) AS "zone" FROM quest_template WHERE RewardTitle > 0) q
            GROUP BY RewardTitle'
        );

        if (!$quests)
        {
            CLI::write('    SourceGen: titleQuest() - quest_template contained no title rewards.', CLI::LOG_WARN);
            return;
        }

        $areaParent = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, parentArea FROM ?_zones WHERE id IN (?a) AND parentArea > 0', array_filter(array_column($quests, 'zone')));

        foreach ($quests as $titleId => $q)
            $this->pushBuffer(Type::TITLE, $titleId, SRC_QUEST, $q['side'], $q['qty'] > 1 ? 0 : Type::QUEST, $q['id'], $areaParent[$q['zone']] ?? $q['zone']);
    }

    private function titleAchievement() : void
    {
        CLI::write('   * #12 Achievement', CLI::LOG_BLANK, true, true);

        $sets = DB::World()->select(
           'SELECT   titleId AS ARRAY_KEY, MIN(ID) AS srcId, NULLIF(MAX(ID), MIN(ID)) AS altSrcId
            FROM     (SELECT TitleA as `titleId`, ID FROM achievement_reward WHERE TitleA <> 0 UNION
                      SELECT TitleH AS `titleId`, ID FROM achievement_reward WHERE TitleH <> 0) AS x
            GROUP BY titleId'
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
        CLI::write('   * #13 cuStrings', CLI::LOG_BLANK, true, true);

        foreach (Lang::game('pvpSources') as $src => $__)
            $this->pushBuffer(Type::TITLE, $src, SRC_CUSTOM_STRING, $src);
    }

    private function itemset() : void
    {
        // every item in ?_itemset needs a source. if so merge fields. if not it's not available.

        $sets = DB::Aowow()->select('SELECT id AS ARRAY_KEY, contentGroup, item1, item2, item3, item4, item5, item6, item7, item8, item9, item10 FROM ?_itemset');

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
