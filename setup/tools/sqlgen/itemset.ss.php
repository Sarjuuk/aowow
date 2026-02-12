<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

/*
    note: the virtual set-ids wont match the ones of wowhead
    since there are some unused itemsets and items flying around in a default database this script will create about 20 sets more than you'd expect.

    and i have no idea how to merge the prefixes/suffixes for wotlk-raidsets and arena-sets in gereral onto the name.. at least not for all locales and i'll be damned if i have to skip one
*/

CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'itemset' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Itemset from dbc and world db.']
    );

    protected $dbcSourceFiles     = ['itemset'];
    protected $worldDependency    = ['item_template', 'game_event'];
    protected $setupAfter         = [['spell'], []];

    private $setToHoliday = array (
        761 => 141,                                         // Winterveil
        762 => 372,                                         // Brewfest
        785 => 341,                                         // Midsummer
        812 => 181,                                         // Noblegarden
    );

    // tags where refId == virtualId
    // in pve sets are not recycled beyond the contentGroup
    private $tagsById = array(
        // "Dungeon Set 1"
        1  => [181, 182, 183, 184, 185, 186, 187, 188, 189],
        // "Dungeon Set 2"
        2  => [511, 512, 513, 514, 515, 516, 517, 518, 519],
        // "Tier 1 Raid Set"
        3  => [201, 202, 203, 204, 205, 206, 207, 208, 209],
        // "Tier 2 Raid Set"
        4  => [210, 211, 212, 213, 214, 215, 216, 217, 218],
        // "Tier 3 Raid Set"
        5  => [521, 523, 524, 525, 526, 527, 528, 529, 530],
        // "Level 60 PvP Rare Set"
        6  => [522, 537, 538, 539, 540, 541, 542, 543, 544, 545, 546, 547, 548, 549, 550, 551, 697, 718],
        // "Level 60 PvP Rare Set (Old)"
        7  => [281, 282, 301, 341, 342, 343, 344, 345, 346, 347, 348, 361, 362, 381, 382, 401],
        // "Level 60 PvP Epic Set"
        8  => [383, 384, 386, 387, 388, 389, 390, 391, 392, 393, 394, 395, 396, 397, 398, 402, 717, 698],
        // "Ruins of Ahn'Qiraj Set"
        9  => [494, 495, 498, 500, 502, 504, 506, 508, 510],
        // "Temple of Ahn'Qiraj Set"
        10 => [493, 496, 497, 499, 501, 503, 505, 507, 509],
        // "Zul'Gurub Set"
        11 => [477, 480, 474, 475, 476, 478, 479, 481, 482],
        // "Tier 4 Raid Set"
        12 => [621, 624, 625, 626, 631, 632, 633, 638, 639, 640, 645, 648, 651, 654, 655, 663, 664],
        // "Tier 5 Raid Set",
        13 => [622, 627, 628, 629, 634, 635, 636, 641, 642, 643, 646, 649, 652, 656, 657, 665, 666],
        // "Dungeon Set 3",
        14 => [620, 623, 630, 637, 644, 647, 650, 653, 658, 659, 660, 661, 662],
        // "Arathi Basin Set",
        15 => [467, 468, 469, 470, 471, 472, 473, 483, 484, 485, 486, 487, 488, 908],
        // "Level 70 PvP Rare Set",
        16 => [587, 588, 589, 590, 591, 592, 593, 594, 595, 596, 597, 598, 599, 600, 601, 602, 603, 604, 605, 606, 607, 608, 609, 610, 688, 689, 691, 692, 693, 694, 695, 696],
        // "Tier 6 Raid Set",
        18 => [668, 669, 670, 671, 672, 673, 674, 675, 676, 677, 678, 679, 680, 681, 682, 683, 684],
        // "Level 70 PvP Rare Set 2",
        21 => [738, 739, 740, 741, 742, 743, 744, 745, 746, 747, 748, 749, 750, 751, 752],
        // "Tier 7 Raid Set",
        23 => [795, 805, 804, 803, 802, 801, 800, 799, 798, 797, 796, 794, 793, 792, 791, 790, 789, 788, 787],
        // "Tier 8 Raid Set",
        25 => [838, 837, 836, 835, 834, 833, 832, 831, 830, 829, 828, 827, 826, 825, 824, 823, 822, 821, 820],
        // "Tier 9 Raid Set",
        27 => [880, 879, 878, 877, 876, 875, 874, 873, 872, 871, 870, 869, 868, 867, 866, 865, 864, 863, 862, 861, 860, 859, 858, 857, 856, 855, 854, 853, 852, 851, 850, 849, 848, 847, 846, 845, 844, 843],
        // "Tier 10 Raid Set",
        29 => [901, 900, 899, 898, 897, 896, 895, 894, 893, 892, 891, 890, 889, 888, 887, 886, 885, 884, 883]
    );

    // in pvp we hope nobody fucked with the item level
    private $tagsByItemlevel = array(
        123 => 17,                                          // "Arena Season 1 Set"
        136 => 19,                                          // "Arena Season 2 Set"
        146 => 20,                                          // "Arena Season 3 Set"
        159 => 22,                                          // "Arena Season 4 Set"
        200 => 24,                                          // "Arena Season 5 Set"
        213 => 24,                                          //
        232 => 26,                                          // "Arena Season 6 Set"
        251 => 28,                                          // "Arena Season 7 Set"
        270 => 30                                           // "Arena Season 8 Set"
    );

    private function getSetType(array $items) : int
    {
        $type    = 0;
        $hasRing = $hasNeck = false;

        foreach ($items as $slot => $item)
        {
            if ($type)
                break;

            // skip cloaks, they mess with armor classes
            if ($slot == INVTYPE_CLOAK)
                continue;

            // skip event-sets
            if ($item['Quality'] == ITEM_QUALITY_NORMAL)
                continue;

            if ($item['class'] == ITEM_CLASS_WEAPON)
            {
                switch ($item['subclass'])
                {
                    case ITEM_SUBCLASS_1H_AXE:      $type =  8; break;
                    case ITEM_SUBCLASS_1H_MACE:     $type =  9; break;
                    case ITEM_SUBCLASS_1H_SWORD:    $type = 10; break;
                    case ITEM_SUBCLASS_FIST_WEAPON: $type =  7; break;
                    case ITEM_SUBCLASS_DAGGER:      $type =  5; break;
                }
            }
            else if ($item['class'] == ITEM_CLASS_ARMOR)
            {
                switch (abs($slot))
                {
                    case INVTYPE_NECK:    $hasNeck = true; break;
                    case INVTYPE_FINGER:  $hasRing = true; break;
                    case INVTYPE_TRINKET: $type    =   11; break;
                    default:
                        if ($item['subclass'] != 0)
                            $type = $item['subclass'];      // 'armor' set by armor class
                }
            }
        }

        if ($hasNeck && !$type)
            $type = 12;                                     // Neck
        else if ($hasRing && !$type)
            $type = 6;                                      // Ring

        return $type;
    }

    private function getContentGroup(array $item) : int
    {
        // try conventional set
        foreach ($this->tagsById as $tag => $setIds)
            if (in_array($item['itemset'], $setIds))
                return $tag;

        // try arena set
        if ($item['AllowableClass'] && ($item['AllowableClass'] & ChrClass::MASK_ALL) != ChrClass::MASK_ALL)
            return $this->tagsByItemlevel[$item['ItemLevel']] ?? 0;

        return 0;
    }

    private function getDataFromSet(array &$data, array $items) : void
    {
        $i = 1;
        foreach ($items as $item)
            $data['item'.$i++] = $item['entry'];

        $mask = ChrClass::MASK_ALL;
        foreach ($items as $item)
            $mask &= $item['AllowableClass'];

        if ($mask != ChrClass::MASK_ALL)
            $data['classMask'] = $mask;

        $iLvl = array_column($items, 'ItemLevel');
        $data['minLevel'] = min($iLvl);
        $data['maxLevel'] = max($iLvl);
        $data['npieces']  = count($items);

        $data['reqLevel'] = max(array_column($items, 'RequiredLevel'));
        $data['quality']  = max(array_column($items, 'Quality'));
        $data['heroic']   = max(array_column($items, 'heroic'));

        $data['type']         = $this->getSetType($items);
        $data['contentGroup'] = reset($items)['note'];
    }

    public function generate(array $ids = []) : bool
    {
        // find events associated with holidayIds
        if ($pairs = DB::World()->selectCol('SELECT `holiday` AS ARRAY_KEY, `eventEntry` FROM game_event WHERE `holiday` IN (?a)', array_values($this->setToHoliday)))
            foreach ($this->setToHoliday as &$hId)
                $hId = !empty($pairs[$hId]) ? $pairs[$hId] : 0;

        DB::Aowow()->query('TRUNCATE TABLE ?_itemset');

        $virtualId = 0;
        $sets      = DB::Aowow()->select('SELECT *, `id` AS ARRAY_KEY FROM dbc_itemset');
        $spells    = array_merge(
            array_column($sets, 'spellId1'), array_column($sets, 'spellId2'), array_column($sets, 'spellId3'),
            array_column($sets, 'spellId4'), array_column($sets, 'spellId5'), array_column($sets, 'spellId6'),
            array_column($sets, 'spellId7'), array_column($sets, 'spellId8'), array_column($sets, 'spellId9')
        );

        $bonusSpells = new SpellList(array(['s.id', array_unique($spells)]), ['interactive' => SpellList::INTERACTIVE_NONE]);

        $pieces = DB::World()->select('SELECT `itemset` AS ARRAY_KEY, `entry` AS ARRAY_KEY2, `entry`, `name`, `class`, `subclass`, `Quality`, `AllowableClass`, `ItemLevel`, `RequiredLevel`, `itemset`, IF (`Flags` & ?d, 1, 0) AS "heroic", IF(`InventoryType` = 15, 26, IF(`InventoryType` = 5, 20, `InventoryType`)) AS "slot" FROM item_template WHERE `itemset` > 0', ITEM_FLAG_HEROIC);

        foreach ($sets as $setId => $setData)
        {
            $row = array(
                'id'       => $setId,                       // replace /w --$virtualId if necessary
                'refSetId' => $setId,
                'eventId'  => $this->setToHoliday[$setId] ?? 0
            );

            if ($setData['reqSkillId'])
                $row['skillId'] = $setData['reqSkillId'];
            if ($setData['reqSkillLevel'])
                $row['skillLevel'] = $setData['reqSkillLevel'];

            $j = 1;
            for ($i = 1; $i < 9; $i++)
            {
                if ($setData['spellId'.$i] <= 0 || $setData['itemCount'.$i] <= 0)
                    continue;

                $row['spell'.$j] = $setData['spellId'.$i];
                $row['bonus'.$j] = $setData['itemCount'.$i];
                $j++;
            }


            /**************************/
            /* get name & description */
            /**************************/

            $descText = [];

            foreach (CLISetup::$locales as $locId => $loc)
            {
                Lang::load($loc);

                $row['name_loc'.$locId] = Util::localizedString($setData, 'name');

                for ($i = 1; $i < 9; $i++)
                {
                    if (!$setData['spellId'.$i] || !$bonusSpells->getEntry($setData['spellId'.$i]))
                        continue;

                    if (!isset($descText[$locId]))
                        $descText[$locId] = '';

                    $descText[$locId] .= $bonusSpells->parseText()[0]."\n";
                }

                $row['bonusText_loc'.$locId] = $descText[$locId];
            }


            /****************************************/
            /* determine type and reuse from pieces */
            /****************************************/

            /*
                possible cases:
                1) unused entry (empty data)
                2) multiple itemsets from one dbc entry (arena sets + wotlk raid sets). no duplicate items per slot
                3) one itemset from one dbc entry (basic case). duplicate items per slot possible
            */

            if (count($pieces[$setId] ?? []) < 2)
            {
                $row['cuFlags'] = CUSTOM_EXCLUDE_FOR_LISTVIEW;
                DB::Aowow()->query('INSERT INTO ?_itemset (?#) VALUES (?a)', array_keys($row), array_values($row));
                CLI::write('[item set] '.str_pad('['.$setId.']', 7).CLI::bold($setData['name_loc0']).' has no associated items', CLI::LOG_INFO);
                continue;
            }

            $sorted = [];
            // sort available items by slot, sort slot by itemID ASC
            foreach ($pieces[$setId] as $data)
            {
                $data['note'] = $this->getContentGroup($data);
                if (in_array($data['note'], [23, 25, 27, 29, 17, 19, 20, 22, 24, 26, 28, 30]))
                    $k = $data['ItemLevel'].'-'.$data['Quality'];
                else
                    $k = 0;

                // create new
                if (!isset($sorted[$k][$data['slot']]))
                    $sorted[$k][$data['slot']] = $data;
                // can have two
                else if (in_array($data['slot'], [INVTYPE_WEAPON, INVTYPE_FINGER, INVTYPE_TRINKET]))
                    $sorted[$k][-$data['slot']] = $data;
                // slot confict. If item is being sold, replace old item (imperfect solution :/)
                else if (DB::World()->selectCell('SELECT SUM(`n`) FROM (SELECT COUNT(1) AS "n" FROM npc_vendor WHERE `item` = ?d UNION SELECT COUNT(1) AS "n" FROM game_event_npc_vendor WHERE `item` = ?d) x', $data['entry'], $data['entry']))
                    $sorted[$k][$data['slot']] = $data;
            }

            $i = 0;
            foreach ($sorted as $set)
            {
                ksort($set);

                $vRow = $row;
                $this->getDataFromSet($vRow, $set);

                // only assign a virtual setId to followup sets or the ItemSummary tool can't find the setboni
                // is this the correct way..?
                if ($i++)
                    $vRow['id'] = --$virtualId;

                DB::Aowow()->query('INSERT INTO ?_itemset (?#) VALUES (?a)', array_keys($vRow), array_values($vRow));
            }
        }

        $this->reapplyCCFlags('itemset', Type::ITEMSET);

        return true;
    }
});
?>
