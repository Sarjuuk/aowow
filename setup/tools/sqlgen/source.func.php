<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 *  reference_loot_template
 *  item_loot_template
 *  creature_loot_template
 *  gameobject_loot_template
 *  mail_loot_template
 *  disenchant_loot_template
 *  fishing_loot_template
 *  skinning_loot_template
 *  milling_loot_template
 *  prospecting_loot_template
 *  pickpocketing_loot_template

 *  item_template
 *  creature_template
 *  gameobject_template
 *  quest_template

 *  npc_trainer
 *  npc_vendor
 *  game_event_npc_vendor
 *  creature

 *  playercreateinfo_item
 *  playercreateinfo_spell
 *  achievement_reward
 *  skill_discovery_template
*/

$customData = array(
);
$reqDBC = ['charstartoutfit', 'talent', 'spell', 'skilllineability', 'itemextendedcost', 'lock'];

function source(array $ids = [])
{
    $insBasic = 'INSERT INTO ?_source (`type`, typeId, src?d)                       VALUES [V] ON DUPLICATE KEY UPDATE moreType = NULL, moreTypeId = NULL, src?d = VALUES(src?d)';
    $insMore  = 'INSERT INTO ?_source (`type`, typeId, src?d, moreType, moreTypeId) VALUES [V] ON DUPLICATE KEY UPDATE moreType = NULL, moreTypeId = NULL, src?d = VALUES(src?d)';
    $insSub   = 'INSERT INTO ?_source (`type`, typeId, src?d, moreType, moreTypeId) ?s         ON DUPLICATE KEY UPDATE moreType = NULL, moreTypeId = NULL, src?d = VALUES(src?d)';

    // please note, this workes without checks, because we do not use strings here
    function queryfy($ph, array $data, $query)
    {
        $buff = [];

        array_walk_recursive($data, function(&$item) {
            if ($item === null)
                $item = 'NULL';
        });

        foreach ($data as $d)
            $buff[] = '('.implode(', ', $d ?: 'NULL').')';

        return str_replace($ph, implode(', ', $buff), $query);
    }

    function pushBuffer(&$buff, $type, $typeId, $mType = 0, $mTypeId = 0, $src = 1)
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

    function taughtSpell($item)
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

    // cant update existing rows
    if ($ids)
        DB::Aowow()->query('DELETE FROM ?_source WHERE `type` = ?d AND typeId IN (?a)', $well, $wellll);
    else
        DB::Aowow()->query('TRUNCATE TABLE ?_source');


    /***************************/
    /* Item & inherited Spells */
    /***************************/

    CLISetup::log(' - Items & Spells [inherited]');
    # also everything from items that teach spells, is src of spell
    # todo: check if items have learn-spells (effect: 36)

    CLISetup::log('   * resolve ref-loot tree');
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
    CLISetup::log('   * #1  Crafted');

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
    $spellItems = DB::World()->select('SELECT entry AS ARRAY_KEY, class, subclass, spellid_1, spelltrigger_1, spellid_2, spelltrigger_2 FROM item_template WHERE entry IN (?a)', array_keys($itemSpells));

    foreach ($spellItems as $iId => $si)
    {
        if ($_ = taughtSpell($si))
            $spellBuff[$_] = [TYPE_SPELL, $_, 1, TYPE_SPELL, $itemSpells[$iId]];

        $itemBuff[$iId] = [TYPE_ITEM, $iId, 1, TYPE_SPELL, $itemSpells[$iId]];
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore),  1, 1, 1);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore),  1, 1, 1);


    ############
    #  2: Drop #
    ############
    CLISetup::log('   * #2  Drop');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry'] /*, $lootmode */);
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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

                $objectOT[] = $iId;
                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

        $objectOT[] = $roi;
        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);

                $itemOT[] = $iId;
                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);

        $itemOT[] = $roi;
        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_ITEM, $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 2, 2, 2);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 2, 2, 2);

    DB::Aowow()->query('UPDATE ?_items SET cuFLags = cuFlags | ?d WHERE id IN (?a)', ITEM_CU_OT_ITEMLOOT,   $itemOT);
    DB::Aowow()->query('UPDATE ?_items SET cuFLags = cuFlags | ?d WHERE id IN (?a)', ITEM_CU_OT_OBJECTLOOT, $objectOT);


    ###########
    #  3: PvP # (Vendors w/ xCost Arena/Honor)
    ###########
    CLISetup::log('   * #3  PvP');

// var g_sources_pvp = {
    // 1: 'Arena',
    // 2: 'Battleground',
    // 4: 'World'               basicly the tokens you get for openPvP .. manual data
// };

    $spellBuff   = [];
    $itemBuff    = [];
    $xCostH      = DB::Aowow()->selectCol('SELECT Id FROM dbc_itemextendedcost WHERE reqHonorPoints > 0 AND reqArenaPoints = 0');
    $xCostA      = DB::Aowow()->selectCol('SELECT Id FROM dbc_itemextendedcost WHERE reqArenaPoints > 0');
    $vendorQuery = 'SELECT n.item AS ARRAY_KEY, SUM(n.qty) AS qty, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2 FROM (
                        SELECT item, COUNT(1) AS qty FROM npc_vendor WHERE ExtendedCost IN (?a) GROUP BY item
                        UNION
                        SELECT item, COUNT(1) AS qty FROM game_event_npc_vendor genv JOIN creature c ON c.guid = genv.guid WHERE ExtendedCost IN (?a) GROUP BY item
                    ) n JOIN item_template it ON it.entry = n.item
                    GROUP BY item';

    foreach (DB::World()->select($vendorQuery, $xCostA, $xCostA) as $iId => $v)
    {
        if ($_ = taughtSpell($v))
            $spellBuff[$_] = [TYPE_SPELL, $_, 1];

        $itemBuff[$iId] = [TYPE_ITEM, $iId, 1];
    }

    foreach (DB::World()->select($vendorQuery, $xCostH, $xCostH) as $iId => $v)
    {
        if ($_ = taughtSpell($v))
            $spellBuff[$_] = [TYPE_SPELL, $_, 2];

        $itemBuff[$iId] = [TYPE_ITEM, $iId, 2];
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insBasic), 3, 3, 3);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insBasic), 3, 3, 3);


    #############
    #  4: Quest #
    #############
    CLISetup::log('   * #4  Quest');

    $spellBuff  = [];
    $itemBuff   = [];
    $quests     = DB::World()->select(
        'SELECT n.item AS ARRAY_KEY, n.ID AS quest, SUM(n.qty) AS qty, BIT_OR(n.side) AS side, it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2 FROM (
            SELECT RewardChoiceItemID1 AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE RewardChoiceItemID1 > 0 GROUP BY item UNION
            SELECT RewardChoiceItemID2 AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardChoiceItemID2 > 0 GROUP BY item UNION
            SELECT RewardChoiceItemID3 AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardChoiceItemID3 > 0 GROUP BY item UNION
            SELECT RewardChoiceItemID4 AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardChoiceItemID4 > 0 GROUP BY item UNION
            SELECT RewardChoiceItemID5 AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardChoiceItemID5 > 0 GROUP BY item UNION
            SELECT RewardChoiceItemID6 AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardChoiceItemID6 > 0 GROUP BY item UNION
            SELECT RewardItem1         AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardItem1         > 0 GROUP BY item UNION
            SELECT RewardItem2         AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardItem2         > 0 GROUP BY item UNION
            SELECT RewardItem3         AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardItem3         > 0 GROUP BY item UNION
            SELECT RewardItem4         AS item, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE rewardItem4         > 0 GROUP BY item
        ) n JOIN item_template it ON it.entry = n.item
        GROUP BY item'
    );
    foreach ($quests as $iId => $q)
    {
        if ($_ = taughtSpell($q))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $q['qty'] > 1 ? 0 : TYPE_QUEST, $q['quest'], $q['side']);

        pushBuffer($itemBuff, TYPE_ITEM, $iId, $q['qty'] > 1 ? 0 : TYPE_QUEST, $q['quest'], $q['side']);
    }

    $mailLoot = DB::World()->select('
        SELECT
            IF(mlt.Reference > 0, -mlt.Reference, mlt.Item) AS ARRAY_KEY,
            qt.ID AS entry,
            it.class, it.subclass, it.spellid_1, it.spelltrigger_1, it.spellid_2, it.spelltrigger_2,
            count(1) AS qty,
            BIT_OR(IF(qt.RequiredRaces & 0x2B2 AND !(qt.RequiredRaces & 0x44D), 2, IF(qt.RequiredRaces & 0x44D AND !(qt.RequiredRaces & 0x2B2), 1, 3))) AS side
        FROM
            mail_loot_template mlt
        JOIN
            quest_template qt ON qt.RewardMailTemplateId = mlt.entry
        LEFT JOIN
            item_template it ON it.entry = mlt.Item AND mlt.Reference <= 0
        WHERE
            qt.RewardMailTemplateId > 0
        GROUP BY
            ARRAY_KEY
    ');

    foreach ($mailLoot as $roi => $l)
    {
        if ($roi < 0 && !empty($refLoot[-$roi]))
        {
            foreach ($refLoot[-$roi] as $iId => $r)
            {
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);

                $itemOT[] = $iId;
                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);

        $itemOT[] = $roi;
        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_QUEST, $l['entry'], $l['side']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 4, 4, 4);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 4, 4, 4);


    ##############
    #  5: Vendor # (w/o xCost Arena/Honor)
    ##############
    CLISetup::log('   * #5  Vendor');

    $spellBuff  = [];
    $itemBuff   = [];
    $xCostIds   = DB::Aowow()->selectCol('SELECT Id FROM dbc_itemextendedcost WHERE reqHonorPoints <> 0 OR reqArenaPoints <> 0');
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
        if ($_ = taughtSpell($v))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $v['qty'] > 1 ? 0 : TYPE_NPC, $v['npc']);

        pushBuffer($itemBuff, TYPE_ITEM, $iId, $v['qty'] > 1 ? 0 : TYPE_NPC, $v['npc']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 5, 5, 5);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 5, 5, 5);


    ###############
    # 10: Starter #
    ###############
    CLISetup::log('   * #10 Starter');

    if ($pcii = DB::World()->select('SELECT ?d, itemid, 1 FROM playercreateinfo_item', TYPE_ITEM))
        DB::Aowow()->query(queryfy('[V]', $pcii, $insBasic), 10, 10, 10);

    for ($i = 1; $i < 21; $i++)
        DB::Aowow()->query($insSub, 10, DB::Aowow()->subquery('SELECT ?d, item?d, 1, NULL AS m, NULL AS mt FROM dbc_charstartoutfit WHERE item?d > 0', TYPE_ITEM, $i, $i), 10, 10);


    ###################
    # 12: Achievement #
    ###################
    CLISetup::log('   * #12 Achievement');

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
            SELECT IFNULL(IF(mlt.Reference > 0, -mlt.Reference, mlt.Item), ar.item) AS item, ar.entry
            FROM achievement_reward ar LEFT JOIN mail_loot_template mlt ON mlt.entry = ar.mailTemplate
            WHERE ar.mailTemplate > 0 OR ar.item > 0
        ) src
        LEFT JOIN
            item_template it ON src.item = it.entry
        GROUP BY
            ARRAY_KEY
    ');

    $extraItems = DB::World()->select('SELECT entry AS ARRAY_KEY, class, subclass, spellid_1, spelltrigger_1, spellid_2, spelltrigger_2 FROM item_template WHERE entry IN (?a)', array_keys($xItems));
    foreach ($extraItems as $iId => $l)
    {
        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $xItems[$iId]['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $xItems[$iId]['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $iId, $xItems[$iId]['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $xItems[$iId]['entry']);
    }

    foreach ($rewItems as $iId => $l)
    {
        if ($roi < 0 && !empty($refLoot[-$roi]))
        {
            foreach ($refLoot[-$roi] as $iId => $r)
            {
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $iId, $l['qty'] > 1 ? 0 : TYPE_ACHIEVEMENT, $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 12, 12, 12);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 12, 12, 12);


    ####################
    # 15: Disenchanted #
    ####################
    CLISetup::log('   * #15 Disenchanted');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_);

                pushBuffer($itemBuff, TYPE_ITEM, $iId);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_);

        pushBuffer($itemBuff, TYPE_ITEM, $roi);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 15, 15, 15);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 15, 15, 15);


    ##############
    # 16: Fished #
    ##############
    CLISetup::log('   * #16 Fished');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_OBJECT, $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 16, 16, 16);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 16, 16, 16);


    ################
    # 17: Gathered #
    ################
    CLISetup::log('   * #17 Gathered');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 17, 17, 17);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 17, 17, 17);


    ##############
    # 18: Milled #
    ##############
    CLISetup::log('   * #18 Milled');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_);

                pushBuffer($itemBuff, TYPE_ITEM, $iId);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_);

        pushBuffer($itemBuff, TYPE_ITEM, $roi);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 18, 18, 18);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 18, 18, 18);


    #############
    # 19: Mined #
    #############
    CLISetup::log('   * #19 Mined');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : $l['srcType'], $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 19, 19, 19);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 19, 19, 19);


    ##################
    # 20: Prospected #
    ##################
    CLISetup::log('   * #20 Prospected');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_);

                pushBuffer($itemBuff, TYPE_ITEM, $iId);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_);

        pushBuffer($itemBuff, TYPE_ITEM, $roi);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 20, 20, 20);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 20, 20, 20);


    ##################
    # 21: Pickpocket #
    ##################
    CLISetup::log('   * #21 Pickpocket');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 21, 21, 21);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 21, 21, 21);


    ################
    # 22: Salvaged #
    ################
    CLISetup::log('   * #22 Salvaged');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 22, 22, 22);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 22, 22, 22);

    ###############
    # 23: Skinned #
    ###############
    CLISetup::log('   * #23 Skinned');

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
                if ($_ = taughtSpell($r))
                    pushBuffer($spellBuff, TYPE_SPELL, $_, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

                pushBuffer($itemBuff, TYPE_ITEM, $iId, $r['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
            }

            continue;
        }

        if ($_ = taughtSpell($l))
            pushBuffer($spellBuff, TYPE_SPELL, $_, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);

        pushBuffer($itemBuff, TYPE_ITEM, $roi, $l['qty'] > 1 ? 0 : TYPE_NPC, $l['entry']);
    }

    if ($itemBuff)
        DB::Aowow()->query(queryfy('[V]', $itemBuff, $insMore), 23, 23, 23);

    if ($spellBuff)
        DB::Aowow()->query(queryfy('[V]', $spellBuff, $insMore), 23, 23, 23);



    /*********/
    /* Spell */
    /*********/

    CLISetup::log(' - Spells [original]');

    #  4: Quest
    CLISetup::log('   * #4  Quest');
    $quests = DB::World()->select('
        SELECT spell AS ARRAY_KEY, id, SUM(qty) AS qty, BIT_OR(side) AS side FROM (
            SELECT IF(rewardSpellCast = 0, rewardSpell, rewardSpellCast) AS spell, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE IF(rewardSpellCast = 0, rewardSpell, rewardSpellCast) > 0 GROUP BY spell
            UNION
            SELECT SourceSpellId AS spell, ID, COUNT(1) AS qty, IF(RequiredRaces & 0x2B2 AND !(RequiredRaces & 0x44D), 2, IF(RequiredRaces & 0x44D AND !(RequiredRaces & 0x2B2), 1, 3)) AS side FROM quest_template WHERE SourceSpellId > 0 GROUP BY spell
        ) t GROUP BY spell');

    if ($quests)
    {
        $qSpells = DB::Aowow()->select('SELECT id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE id IN (?a) AND (effect1Id = 36 OR effect2Id = 36 OR effect3Id = 36)', array_keys($quests));
        $buff    = [];

        foreach ($qSpells as $sId => $spell)
        {
            for ($i = 1; $i <= 3; $i++)
            {
                if ($spell['effect'.$i.'Id'] != 36)         // effect: learnSpell
                    continue;

                pushBuffer($buff, TYPE_SPELL, $spell['effect'.$i.'TriggerSpell'], $quests[$sId]['qty'] > 1 ? 0 : TYPE_QUEST, $quests[$sId]['qty'] > 1 ? 0 : $quests[$sId]['id'], $quests[$sId]['side']);
            }
        }

        DB::Aowow()->query(queryfy('[V]', $buff, $insMore), 4, 4, 4);
    }

    #  6: Trainer
    CLISetup::log('   * #6  Trainer');
    if ($tNpcs = DB::World()->select('SELECT SpellID AS ARRAY_KEY, ID, COUNT(1) AS qty FROM npc_trainer WHERE SpellID > 0 GROUP BY ARRAY_KEY'))
    {
        $tSpells = DB::Aowow()->select('SELECT Id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE Id IN (?a)', array_keys($tNpcs));
        $buff    = [];

        // todo (med): this skips some spells (e.g. riding)
        foreach ($tNpcs as $spellId => $npc)
        {
            if (!isset($tSpells[$spellId]))
                continue;

            $effects   = $tSpells[$spellId];
            $trainerId = $npc['ID'] > 200000 || $npc['qty'] > 1 ? null : $npc['ID'];

            $triggered = false;
            for ($i = 1; $i <= 3; $i++)
            {
                if ($effects['effect'.$i.'Id'] != 36)       // effect: learnSpell
                    continue;

                $triggered = true;
                pushBuffer($buff, TYPE_SPELL, $effects['effect'.$i.'TriggerSpell'], $trainerId ? TYPE_NPC : 0, $trainerId);
            }

            if (!$triggered)
                pushBuffer($buff, TYPE_SPELL, $spellId, $trainerId ? TYPE_NPC : 0, $trainerId);
        }

        DB::Aowow()->query(queryfy('[V]', $buff, $insMore), 6, 6, 6);
    }

    #  7: Discovery
    CLISetup::log('   * #7  Discovery');
    // 61756: Northrend Inscription Research (FAST QA VERSION);
    if ($disco = DB::World()->select('SELECT ?d, spellId, 1 FROM skill_discovery_template WHERE reqSpell <> ?d', TYPE_SPELL, 61756))
        DB::Aowow()->query(queryfy('[V]', $disco, $insBasic), 7, 7, 7);

    #  9: Talent
    CLISetup::log('   * #9  Talent');
    $tSpells = DB::Aowow()->select('
        SELECT s.Id AS ARRAY_KEY, s.effect1Id, s.effect2Id, s.effect3Id, s.effect1TriggerSpell, s.effect2TriggerSpell, s.effect3TriggerSpell
        FROM   dbc_talent t
        JOIN   dbc_spell s ON s.Id = t.rank1
        WHERE  t.rank2 < 1 AND (t.talentSpell = 1 OR (s.effect1Id = 36 OR s.effect2Id = 36 OR s.effect3Id = 36))
    ');

    $n    = 0;
    $buff = [];
    while ($tSpells)
    {
        CLISetup::log('     - '.++$n.'. pass');

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

        $tSpells = DB::Aowow()->select('SELECT Id AS ARRAY_KEY, effect1Id, effect2Id, effect3Id, effect1TriggerSpell, effect2TriggerSpell, effect3TriggerSpell FROM dbc_spell WHERE Id IN (?a)', array_keys($recurse));
    }

    DB::Aowow()->query(queryfy('[V]', $buff, $insBasic), 9, 9, 9);

    # 10: Starter
    CLISetup::log('   * #10 Starter');
    /* acquireMethod
        ABILITY_LEARNED_ON_GET_PROFESSION_SKILL     = 1,        learnedAt = 1 && source10 = 1
        ABILITY_LEARNED_ON_GET_RACE_OR_CLASS_SKILL  = 2         not used for now
    */

    $subSkills = DB::Aowow()->subquery('SELECT ?d, spellId, 1, NULL AS m, NULL AS mt FROM dbc_skilllineability WHERE acquireMethod = 1 AND (reqSkillLevel = 1 OR skillLineId = 129) GROUP BY spellId', TYPE_SPELL);
    DB::Aowow()->query($insSub, 10, $subSkills, 10, 10);

    if ($pcis = DB::World()->select('SELECT ?d, Spell, 1 FROM playercreateinfo_spell', TYPE_SPELL))
        DB::Aowow()->query(queryfy('[V]', $pcis, $insBasic), 10, 10, 10);


    /**********/
    /* Titles */
    /**********/

    CLISetup::log(' - Titles');

    #  4: Quest
    CLISetup::log('   * #4  Quest');
    if ($quests = DB::World()->select('SELECT ?d, RewardTitle, 1, ?d, ID FROM quest_template WHERE RewardTitle > 0', TYPE_TITLE, TYPE_QUEST))
        DB::Aowow()->query(queryfy('[V]', $quests, $insMore), 4, 4, 4);

    # 12: Achievement
    CLISetup::log('   * #12 Achievement');
    $sets = DB::World()->select('
        SELECT ?d, IF (title_A <> 0, title_A, title_H) AS title, 1, ?d, entry FROM achievement_reward WHERE title_A <> 0 OR title_H <> 0 GROUP BY title
        UNION
        SELECT ?d, title_H AS title, 1, ?d, entry FROM achievement_reward WHERE title_A <> title_H AND title_A <> 0 AND title_H <> 0',
        TYPE_TITLE, TYPE_ACHIEVEMENT,
        TYPE_TITLE, TYPE_ACHIEVEMENT
    );
    if ($sets)
        DB::Aowow()->query(queryfy('[V]', $sets, $insMore), 12, 12, 12);

    # 13: Source-String
    CLISetup::log('   * #13 cuStrings');
    $src13 = [null, 42, 52, 71, 80, 157, 163, 167, 169, 177];
    foreach ($src13 as $src => $tId)
        if ($tId)
            DB::Aowow()->query(queryfy('[V]', [[TYPE_TITLE, $tId, $src]], $insBasic), 13, 13, 13);

    return true;
}

?>
