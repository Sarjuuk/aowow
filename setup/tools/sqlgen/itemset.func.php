<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

/*
    note: the virtual set-ids wont match the ones of wowhead
    since there are some unused itemsets and items flying around in a default database this script will create about 20 sets more than you'd expect.

    and i have no idea how to merge the prefixes/suffixes for wotlk-raidsets and arena-sets in gereral onto the name.. at least not for all locales and i'll be damned if i have to skip one
*/

SqlGen::register(new class extends SetupScript
{
    protected $command = 'itemset';

    protected $tblDependencyAowow = ['spell'];
    protected $tblDependencyTC    = ['item_template', 'game_event'];
    protected $dbcSourceFiles     = ['itemset'];

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

    // well .. fuck
    private $tagsByNamePart = array(
        17 => ['gladiator'],                                // "Arena Season 1 Set",
        19 => ['merciless'],                                // "Arena Season 2 Set",
        20 => ['vengeful'],                                 // "Arena Season 3 Set",
        22 => ['brutal'],                                   // "Arena Season 4 Set",
        24 => ['deadly', 'hateful', 'savage'],              // "Arena Season 5 Set",
        26 => ['furious'],                                  // "Arena Season 6 Set",
        28 => ['relentless'],                               // "Arena Season 7 Set",
        30 => ['wrathful']                                  // "Arena Season 8 Set",
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
                    case  0: $type =  8; break;             // 1H-Axe
                    case  4: $type =  9; break;             // 1H-Mace
                    case  7: $type = 10; break;             // 1H-Sword
                    case 13: $type =  7; break;             // Fist Weapon
                    case 15: $type =  5; break;             // Dagger
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
                            $type = $item['subclass'];   // 'armor' set by armor class
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
        if ($item['ItemLevel'] >= 120 && $item['AllowableClass'] && ($item['AllowableClass'] & CLASS_MASK_ALL) != CLASS_MASK_ALL)
            foreach ($this->tagsByNamePart as $tag => $strings)
                foreach ($strings as $str)
                    if (stripos($item['name'], $str) === 0)
                        return $tag;

        return 0;
    }

    private function getDataFromSet(array &$data, array $items) : void
    {
        $i = 1;
        foreach ($items as $item)
            $data['item'.$i++] = $item['entry'];

        $mask = CLASS_MASK_ALL;
        foreach ($items as $item)
            $mask &= $item['AllowableClass'];

        if ($mask != CLASS_MASK_ALL)
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
        if ($pairs = DB::World()->selectCol('SELECT holiday AS ARRAY_KEY, eventEntry FROM game_event WHERE holiday IN (?a)', array_values($this->setToHoliday)))
            foreach ($this->setToHoliday as &$hId)
                $hId = !empty($pairs[$hId]) ? $pairs[$hId] : 0;

        DB::Aowow()->query('TRUNCATE TABLE ?_itemset');

        $virtualId = 0;
        $sets      = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM dbc_itemset');

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


            /********************/
            /* calc statbonuses */
            /********************/

            $gains = $spells = $mods = [];

            for ($i = 1; $i < 9; $i++)
                if ($setData['spellId'.$i] > 0 && $setData['itemCount'.$i] > 0)
                    $spells[$i] = [$setData['spellId'.$i], $setData['itemCount'.$i]];

            $bonusSpells = new SpellList(array(['s.id', array_column($spells, 0)]));
            $mods = $bonusSpells->getStatGain();

            $spells = array_pad($spells, 8, [0, 0]);

            for ($i = 1; $i < 9; $i++)
                if ($setData['itemCount'.$i] > 0 && !empty($mods[$setData['spellId'.$i]]))
                    $gains[$setData['itemCount'.$i]] = $mods[$setData['spellId'.$i]];

            $row['bonusParsed'] = serialize($gains);
            foreach (array_column($spells, 0) as $idx => $spellId)
                $row['spell'.($idx+1)] = $spellId;
            foreach (array_column($spells, 1) as $idx => $nItems)
                $row['bonus'.($idx+1)] = $nItems;


            /**************************/
            /* get name & description */
            /**************************/

            $descText = [];

            foreach (Util::mask2bits(Cfg::get('LOCALES')) as $loc)
            {
                User::useLocale($loc);

                $row['name_loc'.$loc] = Util::localizedString($setData, 'name');

                foreach ($bonusSpells->iterate() as $__)
                {

                    if (!isset($descText[$loc]))
                        $descText[$loc] = '';

                    $descText[$loc] .= $bonusSpells->parseText()[0]."\n";
                }

                // strip rating blocks - e.g. <!--rtg19-->14&nbsp;<small>(<!--rtg%19-->0.30%&nbsp;@&nbsp;L<!--lvl-->80)</small>
                $row['bonusText_loc'.$loc] = preg_replace('/<!--rtg\d+-->(\d+)&nbsp.*?<\/small>/i', '\1', $descText[$loc]);
            }


            /****************************************/
            /* determine type and reuse from pieces */
            /****************************************/

            $pieces = DB::World()->select('SELECT entry, name, class, subclass, Quality, AllowableClass, ItemLevel, RequiredLevel, itemset, IF (Flags & ?d, 1, 0) AS heroic, IF(InventoryType = 15, 26, IF(InventoryType = 5, 20, InventoryType)) AS slot, entry AS ARRAY_KEY FROM item_template WHERE itemset = ?d', ITEM_FLAG_HEROIC, $setId);

            /*
                possible cases:
                1) unused entry (empty data)
                2) multiple itemsets from one dbc entry (arena sets + wotlk raid sets). no duplicate items per slot
                3) one itemset from one dbc entry (basic case). duplicate items per slot possible
            */

            if (count($pieces) < 2)
            {
                $row['cuFlags'] = CUSTOM_EXCLUDE_FOR_LISTVIEW;
                DB::Aowow()->query('REPLACE INTO ?_itemset (?#) VALUES (?a)', array_keys($row), array_values($row));
                CLI::write(' - item set #'.$setId.' has no associated items', CLI::LOG_INFO);
                continue;
            }

            $sorted = [];
            // sort available items by slot, sort slot by itemID ASC
            foreach ($pieces as $data)
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
                else if (DB::World()->selectCell('SELECT SUM(n) FROM (SELECT COUNT(1) AS n FROM npc_vendor WHERE item = ?d UNION SELECT COUNT(1) AS n FROM game_event_npc_vendor WHERE item = ?d) x', $data['entry'], $data['entry']))
                    $sorted[$k][$data['slot']] = $data;
            }

            foreach ($sorted as $i => $set)
            {
                ksort($set);

                $vRow = $row;
                $this->getDataFromSet($vRow, $set);

                // is virtual set
                if ($i)
                    $vRow['id'] = --$virtualId;

                DB::Aowow()->query('REPLACE INTO ?_itemset (?#) VALUES (?a)', array_keys($vRow), array_values($vRow));
            }
        }

        $this->reapplyCCFlags('itemset', Type::ITEMSET);

        return true;
    }
});
?>
