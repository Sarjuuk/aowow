<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'source';

    protected $tblDependencyAowow = ['spell', 'achievement'];
    protected $tblDependencyTC    = ['playercreateinfo_skills', 'playercreateinfo_item', 'skill_discovery_template', 'achievement_reward', 'skill_perfect_item_template', 'item_template', 'gameobject_template', 'quest_template', 'quest_template_addon', 'creature_template', 'creature', 'trainer_spell', 'npc_vendor', 'game_event_npc_vendor', 'reference_loot_template', 'item_loot_template', 'creature_loot_template', 'gameobject_loot_template', 'mail_loot_template', 'disenchant_loot_template', 'fishing_loot_template', 'skinning_loot_template', 'milling_loot_template', 'prospecting_loot_template', 'pickpocketing_loot_template'];
    protected $dbcSourceFiles     = ['charstartoutfit', 'talent', 'spell', 'skilllineability', 'itemextendedcost', 'lock'];

    private function queryfy(array $data, string $query) : string
    {
        $buff = [];

        array_walk_recursive($data, function(&$item) {
            if ($item === null)
                $item = 'NULL';
        });

        foreach ($data as $d)
            $buff[] = '('.implode(', ', $d ?: 'NULL').')';

        return str_replace('[V]', implode(', ', $buff), $query);
    }

    private function pushBuffer(array &$buff, int $type, int $typeId, int $mType = 0, int $mTypeId = 0, int $src = 1) : void
    {
        $b = &$buff[$typeId];

        if (isset($b) && $b[3] === null)
            return;
        else if (isset($b) && $b[1] != $typeId)
        {
            $b[3]  = $b[4] = null;
            $b[2] |= $src;                                  // only quests atm
        }
        else if ($mType && $mTypeId)
            $b = [$type, $typeId, $src, $mType, $mTypeId];
        else
            $b = [$type, $typeId, $src, null, null];
    }

    private function taughtSpell(array $item) : int
    {
        # spelltrigger_X (0: onUse; 6: onLearnSpell)
        # spell: 483 & 55884 are learn spells

        // should not be able to teach spells (recipe || mount || vanityPet)
        if ($item['class'] != ITEM_CLASS_RECIPE && ($item['class'] != ITEM_CLASS_MISC || ($item['subclass'] != 2 && $item['subclass'] != 5)))
            return 0;

        // wotlk system
        if (($item['spellid_1'] == 483 || $item['spellid_1'] == 55884) && $item['spelltrigger_1'] == 0 && $item['spellid_2'] > 0 && $item['spelltrigger_2'] == 6)
            return $item['spellid_2'];

        // deprecated system
        if ($item['spellid_1'] != 483 && $item['spellid_1'] != 55884 && $item['spellid_1'] > 0 && $item['spelltrigger_1'] == 0)
            return $item['spellid_1'];

        return 0;
    }

    public function generate(array $ids = []) : bool
    {
        $insBasic = 'INSERT INTO ?_source (`type`, typeId, src?d)                       VALUES [V] ON DUPLICATE KEY UPDATE moreType = NULL, moreTypeId = NULL, src?d = VALUES(src?d)';
        $insMore  = 'INSERT INTO ?_source (`type`, typeId, src?d, moreType, moreTypeId) VALUES [V] ON DUPLICATE KEY UPDATE moreType = NULL, moreTypeId = NULL, src?d = VALUES(src?d)';
        $insSub   = 'INSERT INTO ?_source (`type`, typeId, src?d, moreType, moreTypeId) ?s         ON DUPLICATE KEY UPDATE moreType = NULL, moreTypeId = NULL, src?d = VALUES(src?d)';

        // cant update existing rows
        if ($ids)
            DB::Aowow()->query('DELETE FROM ?_source WHERE `type` = ?d AND typeId IN (?a)', $well, $wellll);
        else
            DB::Aowow()->query('TRUNCATE TABLE ?_source');


        /***************************/
        /* Item & inherited Spells */
        /***************************/

        CLI::write(' - Items & Spells [inherited]');
        # also everything from items that teach spells, is src of spell
        # todo: check if items have learn-spells (effect: 36)

        CLI::write('   * resolve ref-loot tree');
        $refLoot = DB::World()->select('
            SELECT
                rlt.Entry AS ARRAY_KEY,
                IF(Reference, -Reference, Item) AS ARRAY_KEY2,
                it.entry, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                COUNT(1) AS qty
            FROM
                reference_loot_template rlt
            LEFT JOIN
                item_template it ON rlt.Reference = 0 AND rlt.Item = it.entry
            GROUP BY
                ARRAY_KEY, ARRAY_KEY2
        ');

        $hasChanged = true;
        while ($hasChanged)
        {
            $hasChanged = false;
            foreach ($refLoot as $entry => &$refData)
            {
                foreach ($refData as $itemOrRef => $data)
                {
                    if ($itemOrRef > 0)
                        continue;

                    if (!empty($refLoot[-$itemOrRef]))
                    {
                        foreach ($refLoot[-$itemOrRef] AS $key => $data)
                        {
                            if (!empty($refData[$key]))
                                $refData[$key]['qty'] += $data['qty'];
                            else
                                $refLoot[-$itemOrRef][$key] = $data;
                        }
                    }

                    unset($refData[$itemOrRef]);
                    $hasChanged = true;
                }
            }
        }


        ###############
        #  1: Crafted #
        ###############
        CLI::write('   * #1  Crafted');

        $spellBuff  = [];
        $itemBuff   = [];
        $itemSpells = DB::Aowow()->selectCol('
            SELECT
                effect1CreateItemId AS ARRAY_KEY, s.id AS spell
            FROM
                dbc_spell s JOIN dbc_skilllineability sla ON s.id = sla.spellId
            WHERE
                effect1CreateItemId > 0 AND sla.skillLineId IN (?a)
            GROUP BY
                ARRAY_KEY',
            [164, 165, 171, 182, 186, 197, 202, 333, 393, 755, 773, 129, 185, 356, 762]
        );

        // assume unique craft spells per item
        if ($perfItems = DB::World()->selectCol('SELECT perfectItemType AS ARRAY_KEY, spellId AS spell FROM skill_perfect_item_template'))
            foreach ($perfItems AS $item => $spell)
                $itemSpells[$item] = $spell;

        $spellItems = DB::World()->select('SELECT entry AS ARRAY_KEY, class, subclass, spellid_1, spelltrigger_1, spellid_2, spelltrigger_2 FROM item_template WHERE entry IN (?a)', array_keys($itemSpells));

        foreach ($spellItems as $iId => $si)
        {
            if ($_ = $this->taughtSpell($si))
                $spellBuff[$_] = [TYPE_SPELL, $_, 1, TYPE_SPELL, $itemSpells[$iId]];

            $itemBuff[$iId] = [TYPE_ITEM, $iId, 1, TYPE_SPELL, $itemSpells[$iId]];
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore),  1, 1, 1);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore),  1, 1, 1);


        ############
        #  2: Drop #
        ############
        CLI::write('   * #2  Drop');

        $spellBuff    = [];
        $itemBuff     = [];
        $creatureLoot = DB::World()->select('
            SELECT
                IF(clt.Reference > 0, -clt.Reference, clt.Item) AS ARRAY_KEY,
                ct.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                count(1) AS qty
            FROM
                creature_loot_template clt
            JOIN
                creature_template ct ON clt.entry = ct.lootid
            LEFT JOIN
                item_template it ON it.entry = clt.Item AND clt.Reference <= 0
            WHERE
                ct.lootid > 0
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($creatureLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);
        }

        $objectOT   = [];
        $exclLocks  = DB::Aowow()->selectCol('SELECT id FROM dbc_lock WHERE properties1 IN (2, 3)');
        $objectLoot = DB::World()->select('
            SELECT
                IF(glt.Reference > 0, -glt.Reference, glt.Item) AS ARRAY_KEY,
                gt.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                count(1) AS qty
            FROM
                gameobject_loot_template glt
            JOIN
                gameobject_template gt ON glt.entry = gt.Data1
            LEFT JOIN
                item_template it ON it.entry = glt.Item AND glt.Reference <= 0
            WHERE
                `type` = 3 AND gt.Data1 > 0 AND gt.Data0 NOT IN (?a)
            GROUP BY
                ARRAY_KEY',
            $exclLocks
        );

        foreach ($objectLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

                    $objectOT[] = $iId;
                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

            $objectOT[] = $roi;
            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
        }

        $itemOT   = [];
        $itemLoot = DB::World()->select('
            SELECT
                IF(ilt.Reference > 0, -ilt.Reference, ilt.Item) AS ARRAY_KEY,
                itA.entry,
                itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2,
                count(1) AS qty
            FROM
                item_loot_template ilt
            JOIN
                item_template itA ON ilt.entry = itA.entry
            LEFT JOIN
                item_template itB ON itB.entry = ilt.Item AND ilt.Reference <= 0
            WHERE
                itA.flags & 0x4
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($itemLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);

                    $itemOT[] = $iId;
                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);

            $itemOT[] = $roi;
            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 2, 2, 2);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 2, 2, 2);

        DB::Aowow()->query('UPDATE ?_items SET cuFLags = cuFlags | ?d WHERE id IN (?a)', ITEM_CU_OT_ITEMLOOT,   $itemOT);
        DB::Aowow()->query('UPDATE ?_items SET cuFLags = cuFlags | ?d WHERE id IN (?a)', ITEM_CU_OT_OBJECTLOOT, $objectOT);


        ###########
        #  3: PvP # (Vendors w/ xCost Arena/Honor)
        ###########
        CLI::write('   * #3  PvP');

    // var g_sources_pvp = {
        // 1: 'Arena',
        // 2: 'Battleground',
        // 4: 'World'               basicly the tokens you get for openPvP .. manual data
    // };

        $spellBuff   = [];
        $itemBuff    = [];
        $xCostH      = DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqHonorPoints > 0 AND reqArenaPoints = 0');
        $xCostA      = DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqArenaPoints > 0');
        $vendorQuery = 'SELECT n.item AS ARRAY_KEY, SUM(n.qty) AS qty, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2 FROM (
                            SELECT item, COUNT(1) AS qty FROM npc_vendor WHERE ExtendedCost IN (?a) GROUP BY item
                            UNION
                            SELECT item, COUNT(1) AS qty FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid WHERE ExtendedCost IN (?a) GROUP BY item
                        ) n JOIN item_template it ON it.entry = n.item
                        GROUP BY item';

        foreach (DB::World()->select($vendorQuery, $xCostA, $xCostA) as $iId => $v)
        {
            if ($_ = $this->taughtSpell($v))
                $spellBuff[$_] = [TYPE_SPELL, $_, 1];

            $itemBuff[$iId] = [TYPE_ITEM, $iId, 1];
        }

        foreach (DB::World()->select($vendorQuery, $xCostH, $xCostH) as $iId => $v)
        {
            if ($_ = $this->taughtSpell($v))
                $spellBuff[$_] = [TYPE_SPELL, $_, 2];

            $itemBuff[$iId] = [TYPE_ITEM, $iId, 2];
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insBasic), 3, 3, 3);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insBasic), 3, 3, 3);


        #############
        #  4: Quest #
        #############
        CLI::write('   * #4  Quest');

        $spellBuff  = [];
        $itemBuff   = [];
        $quests     = DB::World()->select(
            'SELECT n.item AS ARRAY_KEY, n.ID AS quest, SUM(n.qty) AS qty, BIT_OR(n.side) AS side, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2 FROM (
                SELECT RewardChoiceItemID1 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID1 > 0 GROUP BY item UNION
                SELECT RewardChoiceItemID2 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID2 > 0 GROUP BY item UNION
                SELECT RewardChoiceItemID3 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID3 > 0 GROUP BY item UNION
                SELECT RewardChoiceItemID4 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID4 > 0 GROUP BY item UNION
                SELECT RewardChoiceItemID5 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID5 > 0 GROUP BY item UNION
                SELECT RewardChoiceItemID6 AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID6 > 0 GROUP BY item UNION
                SELECT RewardItem1         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardItem1         > 0 GROUP BY item UNION
                SELECT RewardItem2         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardItem2         > 0 GROUP BY item UNION
                SELECT RewardItem3         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardItem3         > 0 GROUP BY item UNION
                SELECT RewardItem4         AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardItem4         > 0 GROUP BY item UNION
                SELECT StartItem           AS item, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE StartItem           > 0 GROUP BY item
            ) n JOIN item_template it ON it.entry = n.item
            GROUP BY item'
        );
        foreach ($quests as $iId => $q)
        {
            if ($_ = $this->taughtSpell($q))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $q['qty'] > 1 ? 0 : TYPE_QUEST, $q['quest'], $q['side']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $q['qty'] > 1 ? 0 : TYPE_QUEST, $q['quest'], $q['side']);
        }

        $mailLoot = DB::World()->select('
            SELECT
                IF(mlt.Reference > 0, -mlt.Reference, mlt.Item) AS ARRAY_KEY,
                qt.ID AS entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                count(1) AS qty,
                BIT_OR(IF(qt.AllowableRaces & 0x2B2 AND !(qt.AllowableRaces & 0x44D), 2, IF(qt.AllowableRaces & 0x44D AND !(qt.AllowableRaces & 0x2B2), 1, 3))) AS side
            FROM
                mail_loot_template mlt
            JOIN
                quest_template_addon qta ON qta.RewardMailTemplateId = mlt.entry
            JOIN
                quest_template qt ON qt.ID = qta.ID
            LEFT JOIN
                item_template it ON it.entry = mlt.Item AND mlt.Reference <= 0
            WHERE
                qta.RewardMailTemplateId > 0
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($mailLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);

                    $itemOT[] = $iId;
                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);

            $itemOT[] = $roi;
            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 4, 4, 4);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 4, 4, 4);


        ##############
        #  5: Vendor # (w/o xCost Arena/Honor)
        ##############
        CLI::write('   * #5  Vendor');

        $spellBuff  = [];
        $itemBuff   = [];
        $xCostIds   = DB::Aowow()->selectCol('SELECT id FROM dbc_itemextendedcost WHERE reqHonorPoints <> 0 OR reqArenaPoints <> 0');
        $vendors    = DB::World()->select(
            'SELECT n.item AS ARRAY_KEY, n.npc, SUM(n.qty) AS qty, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2 FROM (
                SELECT item, entry AS npc, COUNT(1) AS qty FROM npc_vendor WHERE ExtendedCost NOT IN (?a) GROUP BY item
                UNION
                SELECT item, c.id AS npc, COUNT(1) AS qty FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid WHERE ExtendedCost NOT IN (?a) GROUP BY item
            ) n JOIN item_template it ON it.entry = n.item
            GROUP BY item',
            $xCostIds,
            $xCostIds
        );

        foreach ($vendors as $iId => $v)
        {
            if ($_ = $this->taughtSpell($v))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $v['qty'] > 1 ? 0 : TYPE_NPC, $v['npc']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $v['qty'] > 1 ? 0 : TYPE_NPC, $v['npc']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 5, 5, 5);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 5, 5, 5);


        ###############
        # 10: Starter #
        ###############
        CLI::write('   * #10 Starter');

        if ($pcii = DB::World()->select('SELECT ?d, itemid, 1 FROM playercreateinfo_item', TYPE_ITEM))
            DB::Aowow()->query($this->queryfy($pcii, $insBasic), 10, 10, 10);

        for ($i = 1; $i < 21; $i++)
            DB::Aowow()->query($insSub, 10, DB::Aowow()->subquery('SELECT ?d, item?d, 1, NULL AS m, NULL AS mt FROM dbc_charstartoutfit WHERE item?d > 0', TYPE_ITEM, $i, $i), 10, 10);


        ###################
        # 12: Achievement #
        ###################
        CLI::write('   * #12 Achievement');

        $spellBuff = [];
        $itemBuff  = [];
        $xItems    = DB::Aowow()->select('SELECT id AS entry, itemExtra AS ARRAY_KEY, COUNT(1) AS qty FROM ?_achievement WHERE itemExtra > 0 GROUP BY itemExtra');
        $rewItems  = DB::World()->select('
            SELECT
                src.item AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                COUNT(1) AS qty
            FROM (
                SELECT IFNULL(IF(mlt.Reference > 0, -mlt.Reference, mlt.Item), ar.ItemID) AS item, ar.ID AS entry
                FROM achievement_reward ar LEFT JOIN mail_loot_template mlt ON mlt.entry = ar.MailTemplateID
                WHERE ar.MailTemplateID > 0 OR ar.ItemID > 0
            ) src
            LEFT JOIN
                item_template it ON src.item = it.entry
            GROUP BY
                ARRAY_KEY
        ');

        $extraItems = DB::World()->select('SELECT entry AS ARRAY_KEY, class, subclass, spellid_1, spelltrigger_1, spellid_2, spelltrigger_2 FROM item_template WHERE entry IN (?a)', array_keys($xItems));
        foreach ($extraItems as $iId => $l)
        {
            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $xItems[$iId]['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $xItems[$iId]['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $xItems[$iId]['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $xItems[$iId]['entry']);
        }

        foreach ($rewItems as $iId => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 12, 12, 12);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 12, 12, 12);


        ####################
        # 15: Disenchanted #
        ####################
        CLI::write('   * #15 Disenchanted');

        $spellBuff = [];
        $itemBuff  = [];
        $deLoot    = DB::World()->select('
            SELECT
                IF(dlt.Reference > 0, -dlt.Reference, dlt.Item) AS ARRAY_KEY,
                itA.entry,
                itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2,
                count(1) AS qty
            FROM
                disenchant_loot_template dlt
            JOIN
                item_template itA ON dlt.entry = itA.DisenchantId
            LEFT JOIN
                item_template itB ON itB.entry = dlt.Item AND dlt.Reference <= 0
            WHERE
                itA.DisenchantId > 0
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($deLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 15, 15, 15);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 15, 15, 15);


        ##############
        # 16: Fished #
        ##############
        CLI::write('   * #16 Fished');

        $spellBuff = [];
        $itemBuff  = [];
        $fishLoot  = DB::World()->select('
            SELECT
                src.itemOrRef AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                count(1) AS qty
            FROM (
                SELECT 0 AS entry, IF(flt.Reference > 0, -flt.Reference, flt.Item) itemOrRef FROM fishing_loot_template flt UNION
                SELECT   gt.entry, IF(glt.Reference > 0, -glt.Reference, glt.Item) itemOrRef FROM gameobject_template   gt  JOIN gameobject_loot_template glt ON glt.entry = gt.Data1 WHERE `type` = 25 AND gt.Data1 > 0
            ) src
            LEFT JOIN
                item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($fishLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 16, 16, 16);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 16, 16, 16);


        ################
        # 17: Gathered #
        ################
        CLI::write('   * #17 Gathered');

        $spellBuff = [];
        $itemBuff  = [];
        $herbLocks = DB::Aowow()->selectCol('SELECT id FROM dbc_lock WHERE properties1 = 2');
        $herbLoot  = DB::World()->select('
            SELECT
                src.itemOrRef AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                count(1) AS qty,
                src.srcType
            FROM (
                SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef, ?d AS srcType FROM creature_template   ct JOIN skinning_loot_template   slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) AND ct.skinloot > 0 UNION
                SELECT gt.entry, IF(glt.Reference > 0, -glt.Reference, glt.Item) itemOrRef, ?d AS srcType FROM gameobject_template gt JOIN gameobject_loot_template glt ON glt.entry = gt.Data1    WHERE gt.`type` = 3 AND gt.Data1 > 0 AND Data0 IN (?a)
            ) src
            LEFT JOIN
                item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY
                ARRAY_KEY',
            TYPE_NPC, NPC_TYPEFLAG_HERBLOOT,
            TYPE_OBJECT, $herbLocks
        );

        foreach ($herbLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 17, 17, 17);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 17, 17, 17);


        ##############
        # 18: Milled #
        ##############
        CLI::write('   * #18 Milled');

        $spellBuff = [];
        $itemBuff  = [];
        $millLoot  = DB::World()->select('
            SELECT
                IF(mlt.Reference > 0, -mlt.Reference, mlt.Item) AS ARRAY_KEY,
                itA.entry,
                itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2,
                count(1) AS qty
            FROM
                milling_loot_template mlt
            JOIN
                item_template itA ON mlt.entry = itA.entry
            LEFT JOIN
                item_template itB ON itB.entry = mlt.Item AND mlt.Reference <= 0
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($millLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 18, 18, 18);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 18, 18, 18);


        #############
        # 19: Mined #
        #############
        CLI::write('   * #19 Mined');

        $spellBuff = [];
        $itemBuff  = [];
        $mineLocks = DB::Aowow()->selectCol('SELECT id FROM dbc_lock WHERE properties1 = 3');
        $mineLoot  = DB::World()->select('
            SELECT
                src.itemOrRef AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                count(1) AS qty,
                src.srcType
            FROM (
                SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef, ?d AS srcType FROM creature_template   ct JOIN skinning_loot_template   slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) AND ct.skinloot > 0 UNION
                SELECT gt.entry, IF(glt.Reference > 0, -glt.Reference, glt.Item) itemOrRef, ?d AS srcType FROM gameobject_template gt JOIN gameobject_loot_template glt ON glt.entry = gt.Data1    WHERE gt.`type` = 3 AND gt.Data1 > 0 AND Data0 IN (?a)
            ) src
            LEFT JOIN
                item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY
                ARRAY_KEY',
            TYPE_NPC, NPC_TYPEFLAG_MININGLOOT,
            TYPE_OBJECT, $mineLocks
        );

        foreach ($mineLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 19, 19, 19);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 19, 19, 19);


        ##################
        # 20: Prospected #
        ##################
        CLI::write('   * #20 Prospected');

        $spellBuff    = [];
        $itemBuff     = [];
        $prospectLoot = DB::World()->select('
            SELECT
                IF(plt.Reference > 0, -plt.Reference, plt.Item) AS ARRAY_KEY,
                itA.entry,
                itB.class, itB.subclass, itB.spellid_1, itB.spelltrigger_1, itB.spellid_2, itB.spelltrigger_2,
                count(1) AS qty
            FROM
                prospecting_loot_template plt
            JOIN
                item_template itA ON plt.entry = itA.entry
            LEFT JOIN
                item_template itB ON itB.entry = plt.Item AND plt.Reference <= 0
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($prospectLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 20, 20, 20);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 20, 20, 20);


        ##################
        # 21: Pickpocket #
        ##################
        CLI::write('   * #21 Pickpocket');

        $spellBuff = [];
        $itemBuff  = [];
        $theftLoot = DB::World()->select('
            SELECT
                src.itemOrRef AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                COUNT(1) AS qty
            FROM (
                SELECT ct.entry, IF(plt.Reference > 0, -plt.Reference, plt.Item) itemOrRef FROM creature_template ct JOIN pickpocketing_loot_template plt ON plt.entry = ct.pickpocketloot WHERE ct.pickpocketloot > 0
            ) src
            LEFT JOIN
                item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY
                ARRAY_KEY
        ');

        foreach ($theftLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 21, 21, 21);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 21, 21, 21);


        ################
        # 22: Salvaged #
        ################
        CLI::write('   * #22 Salvaged');

        $spellBuff   = [];
        $itemBuff    = [];
        $salvageLoot = DB::World()->select('
            SELECT
                src.itemOrRef AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                COUNT(1) AS qty
            FROM (
                SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef FROM creature_template ct JOIN skinning_loot_template slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) AND ct.skinloot > 0
            ) src
            LEFT JOIN
                item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY
                ARRAY_KEY',
            NPC_TYPEFLAG_ENGINEERLOOT
        );

        foreach ($salvageLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 22, 22, 22);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 22, 22, 22);


        ###############
        # 23: Skinned #
        ###############
        CLI::write('   * #23 Skinned');

        $spellBuff = [];
        $itemBuff  = [];
        $skinLoot  = DB::World()->select('
            SELECT
                src.itemOrRef AS ARRAY_KEY,
                src.entry,
                it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
                COUNT(1) AS qty
            FROM (
                SELECT ct.entry, IF(slt.Reference > 0, -slt.Reference, slt.Item) itemOrRef FROM creature_template ct JOIN skinning_loot_template slt ON slt.entry = ct.skinloot WHERE (type_flags & ?d) = 0 AND ct.skinloot > 0
            ) src
            LEFT JOIN
                item_template it ON src.itemOrRef > 0 AND src.itemOrRef = it.entry
            GROUP BY
                ARRAY_KEY',
            (NPC_TYPEFLAG_HERBLOOT | NPC_TYPEFLAG_MININGLOOT | NPC_TYPEFLAG_ENGINEERLOOT)
        );

        foreach ($skinLoot as $roi => $l)
        {
            if ($roi < 0 && !empty($refLoot[-$roi]))
            {
                foreach ($refLoot[-$roi] as $iId => $r)
                {
                    if ($_ = $this->taughtSpell($r))
                        $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

                    $this->pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
                }

                continue;
            }

            if ($_ = $this->taughtSpell($l))
                $this->pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

            $this->pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
        }

        if ($itemBuff)
            DB::Aowow()->query($this->queryfy($itemBuff, $insMore), 23, 23, 23);

        if ($spellBuff)
            DB::Aowow()->query($this->queryfy($spellBuff, $insMore), 23, 23, 23);


        // flagging aowow_items for source (note: this is not exact! creatures dropping items may not be spawnd, quests granting items may be disabled)
        DB::Aowow()->query('UPDATE ?_items SET cuFlags = cuFlags & ?d', ~CUSTOM_UNAVAILABLE);
        DB::Aowow()->query('UPDATE ?_items i LEFT JOIN ?_source s ON s.typeId = i.id AND s.type = ?d SET i.cuFlags = i.cuFlags | ?d WHERE s.typeId IS NULL', TYPE_ITEM, CUSTOM_UNAVAILABLE);

        /*********/
        /* Spell */
        /*********/

        CLI::write(' - Spells [original]');

        #  4: Quest
        CLI::write('   * #4  Quest');
        $quests = DB::World()->select('
            SELECT spell AS ARRAY_KEY, id, SUM(qty) AS qty, BIT_OR(side) AS side FROM (
                SELECT IF(RewardSpell = 0, RewardDisplaySpell, RewardSpell) AS spell, ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE IF(RewardSpell = 0, RewardDisplaySpell, RewardSpell) > 0 GROUP BY spell
                UNION
                SELECT qta.SourceSpellId AS spell, qt.ID, COUNT(1) AS qty, IF(AllowableRaces & 0x2B2 AND !(AllowableRaces & 0x44D), 2, IF(AllowableRaces & 0x44D AND !(AllowableRaces & 0x2B2), 1, 3)) AS side FROM quest_template qt JOIN quest_template_addon qta ON qta.ID = qt.ID WHERE qta.SourceSpellId > 0 GROUP BY spell
            ) t GROUP BY spell');

        if ($quests)
        {
            $qSpells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a) AND (effect1Id = 36 OR effect2Id = 36 OR effect3Id = 36)', array_keys($quests));
            $buff    = [];

            foreach ($qSpells as $sId => $spell)
                for ($i = 1; $i <= 3; $i++)
                    if ($spell['effect'.$i.'Id'] == 36)         // effect: learnSpell
                        $this->pushBuffer($buff, TYPE_SPELL, $spell['effect'.$i.'TriggerSpell'], $quests[$sId]['qty'] > 1 ? 0 : TYPE_QUEST, $quests[$sId]['qty'] > 1 ? 0 : $quests[$sId]['id'], $quests[$sId]['side']);

            DB::Aowow()->query($this->queryfy($buff, $insMore), 4, 4, 4);
        }

        #  6: Trainer
        CLI::write('   * #6  Trainer');
        if ($tNpcs = DB::World()->select('SELECT SpellID AS ARRAY_KEY, cdt.CreatureId AS entry, COUNT(1) AS qty FROM `trainer_spell` ts JOIN `creature_default_trainer` cdt ON cdt.TrainerId = ts.TrainerId GROUP BY ARRAY_KEY'))
        {
            $tSpells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a)', array_keys($tNpcs));
            $buff    = [];

            // todo (med): this skips some spells (e.g. riding)
            foreach ($tNpcs as $spellId => $npc)
            {
                if (!isset($tSpells[$spellId]))
                    continue;

                $effects   = $tSpells[$spellId];
                $trainerId = $npc['qty'] > 1 ? 0 : $npc['entry'];

                for ($i = 1; $i <= 3; $i++)
                    if ($effects['effect'.$i.'Id'] == 36)       // effect: learnSpell
                        $this->pushBuffer($buff, TYPE_SPELL, $effects['effect'.$i.'TriggerSpell'], $trainerId ? TYPE_NPC : 0, $trainerId);

                $this->pushBuffer($buff, TYPE_SPELL, $spellId, $trainerId ? TYPE_NPC : 0, $trainerId);
            }

            DB::Aowow()->query($this->queryfy($buff, $insMore), 6, 6, 6);
        }

        #  7: Discovery
        CLI::write('   * #7  Discovery');
        // 61756: Northrend Inscription Research (FAST QA VERSION);
        if ($disco = DB::World()->select('SELECT ?d, spellId, 1 FROM skill_discovery_template WHERE reqSpell <> ?d', TYPE_SPELL, 61756))
            DB::Aowow()->query($this->queryfy($disco, $insBasic), 7, 7, 7);

        #  9: Talent
        CLI::write('   * #9  Talent');
        $tSpells = DB::Aowow()->select('
            SELECT s.id AS ARRAY_KEY, s.effect1Id, s.effect2Id, s.effect3Id, s.effect1TriggerSpell, s.effect2TriggerSpell, s.effect3TriggerSpell
            FROM   dbc_talent t
            JOIN   dbc_spell s ON s.id = t.rank1
            WHERE  t.rank2 < 1 AND (t.talentSpell = 1 OR (s.effect1Id = 36 OR s.effect2Id = 36 OR s.effect3Id = 36))
        ');

        $n    = 0;
        $buff = [];
        while ($tSpells)
        {
            CLI::write('     - '.++$n.'. pass');

            $recurse = [];
            foreach ($tSpells as $tId => $spell)
            {
                for ($i = 1; $i <= 3; $i++)
                    if ($spell['effect'.$i.'Id'] == 36)            // effect: learnSpell
                        $recurse[$spell['effect'.$i.'TriggerSpell']] = $tId;

                if (array_search($tId, $recurse))
                    unset($tSpells[$tId]);
            }

            foreach ($tSpells as $tId => $__)
                $buff[$tId] = [TYPE_SPELL, $tId, 1];

            if (!$recurse)
                break;

            $tSpells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a)', array_keys($recurse));
        }

        DB::Aowow()->query($this->queryfy($buff, $insBasic), 9, 9, 9);

        # 10: Starter
        CLI::write('   * #10 Starter');
        /* acquireMethod
            ABILITY_LEARNED_ON_GET_PROFESSION_SKILL     = 1,        learnedAt = 1 && source10 = 1
            ABILITY_LEARNED_ON_GET_RACE_OR_CLASS_SKILL  = 2
        */

        $pcis = DB::World()->selectCol('SELECT DISTINCT skill FROM playercreateinfo_skills');
        $subSkills = DB::Aowow()->subquery('SELECT ?d, spellId, 1, NULL AS m, NULL AS mt FROM dbc_skilllineability WHERE {(skillLineId IN (?a) AND acquireMethod = 2) OR} (acquireMethod = 1 AND (reqSkillLevel = 1 OR skillLineId = 129)) GROUP BY spellId', TYPE_SPELL, $pcis ?: DBSIMPLE_SKIP);
        DB::Aowow()->query($insSub, 10, $subSkills, 10, 10);


        /**********/
        /* Titles */
        /**********/

        CLI::write(' - Titles');

        #  4: Quest
        CLI::write('   * #4  Quest');
        if ($quests = DB::World()->select('SELECT ?d, RewardTitle, 1, ?d, ID FROM quest_template WHERE RewardTitle > 0', TYPE_TITLE, TYPE_QUEST))
            DB::Aowow()->query($this->queryfy($quests, $insMore), 4, 4, 4);

        # 12: Achievement
        CLI::write('   * #12 Achievement');
        $sets = DB::World()->select('
            SELECT titleId AS ARRAY_KEY, MIN(ID) AS srcId, NULLIF(MAX(ID), MIN(ID)) AS altSrcId FROM (
                SELECT TitleA AS `titleId`, ID FROM achievement_reward WHERE TitleA <> 0
                UNION
                SELECT TitleH AS `titleId`, ID FROM achievement_reward WHERE TitleH <> 0
            ) AS x GROUP BY titleId'
        );
        foreach ($sets as $tId => $set)
        {
            DB::Aowow()->query($this->queryfy([[TYPE_TITLE, $tId, 1, TYPE_ACHIEVEMENT, $set['srcId']]], $insMore), 12, 12, 12);

            if ($set['altSrcId'])
                DB::Aowow()->query('UPDATE ?_titles SET src12Ext = ?d WHERE id = ?d', $set['altSrcId'], $tId);
        }

        # 13: Source-String
        CLI::write('   * #13 cuStrings');
        $src13 = [null, 42, 52, 71, 80, 157, 163, 167, 169, 177];
        foreach ($src13 as $src => $tId)
            if ($tId)
                DB::Aowow()->query($this->queryfy([[TYPE_TITLE, $tId, $src]], $insBasic), 13, 13, 13);

        return true;
    }
});

?>
