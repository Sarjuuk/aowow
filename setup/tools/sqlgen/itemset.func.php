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
    use TrCustomData;

    protected $command = 'itemset';

    protected $tblDependencyAowow = ['spell'];
    protected $tblDependencyTC    = ['item_template', 'game_event'];
    protected $dbcSourceFiles     = ['itemset'];

    private $customData = array(
        221 => ['item1' => 7948, 'item2' => 7949, 'item3' => 7950, 'item4' => 7951, 'item5' => 7952, 'item6' => 7953]
    );

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

    public function generate(array $ids = []) : bool
    {
        // find events associated with holidayIds
        if ($pairs = DB::World()->selectCol('SELECT holiday AS ARRAY_KEY, eventEntry FROM game_event WHERE holiday IN (?a)', array_values($this->setToHoliday)))
            foreach ($this->setToHoliday as &$hId)
                $hId = !empty($pairs[$hId]) ? $pairs[$hId] : 0;

        DB::Aowow()->query('TRUNCATE TABLE ?_itemset');

        $vIdx      = 0;
        $virtualId = 0;
        $sets      = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM dbc_itemset');
        foreach ($sets as $setId => $setData)
        {
            $spells    = $items  = $mods = $descText = $name   = $gains   = [];
            $max       = $reqLvl = $min  = $quality  = $heroic = $nPieces = [];
            $classMask = $type   = 0;
            $hasRing   = false;

            $holiday  = $this->setToHoliday[$setId] ?? 0;
            $canReuse = !$holiday;                          // can't reuse holiday-sets
            $slotList = [];
            $pieces   = DB::World()->select('SELECT *, IF(InventoryType = 15, 26, IF(InventoryType = 5, 20, InventoryType)) AS slot, entry AS ARRAY_KEY FROM item_template WHERE itemset = ?d AND (class <> 4 OR subclass NOT IN (1, 2, 3, 4) OR armor > 0 OR Quality = 1) ORDER BY itemLevel, subclass, slot ASC', $setId);

            /****************************************/
            /* determine type and reuse from pieces */
            /****************************************/

            // make the first vId always same as setId
            $firstPiece = reset($pieces);
            $tmp        = [$firstPiece['Quality'].$firstPiece['ItemLevel'] => $setId];

            // walk through all items associated with the set
            foreach ($pieces as $itemId => $piece)
            {
                $classMask |= ($piece['AllowableClass'] & CLASS_MASK_ALL);
                $key        = $piece['Quality'].str_pad($piece['ItemLevel'], 3, 0, STR_PAD_LEFT);

                if (!isset($tmp[$key]))
                    $tmp[$key] = --$vIdx;

                $vId = $tmp[$key];

                // check only actual armor in rare quality or higher (or inherits holiday)
                if ($piece['class'] != ITEM_CLASS_ARMOR || $piece['subclass'] == 0)
                    $canReuse = false;

                /* gather relevant stats for use */

                if (!isset($quality[$vId]) || $piece['Quality'] > $quality[$vId])
                    $quality[$vId] = $piece['Quality'];

                if ($piece['Flags'] & ITEM_FLAG_HEROIC)
                    $heroic[$vId] = true;

                if (!isset($reqLvl[$vId]) || $piece['RequiredLevel'] > $reqLvl[$vId])
                    $reqLvl[$vId] = $piece['RequiredLevel'];

                if (!isset($min[$vId]) || $piece['ItemLevel'] < $min[$vId])
                    $min[$vId] = $piece['ItemLevel'];

                if (!isset($max[$vId]) || $piece['ItemLevel'] > $max[$vId])
                    $max[$vId] = $piece['ItemLevel'];

                if (!isset($items[$vId][$piece['slot']]) || !$canReuse)
                {
                    if (!isset($nPieces[$vId]))
                        $nPieces[$vId] = 1;
                    else
                        $nPieces[$vId]++;
                }

                if (isset($items[$vId][$piece['slot']]))
                {
                    // not reusable -> insert anyway on unique keys
                    if (!$canReuse)
                        $items[$vId][$piece['slot'].$itemId] = $itemId;
                    else
                    {
                        CLI::write("set: ".$setId." ilvl: ".$piece['ItemLevel']." - conflict between item: ".$items[$vId][$piece['slot']]." and item: ".$itemId." choosing lower itemId", CLI::LOG_WARN);

                        if ($items[$vId][$piece['slot']] > $itemId)
                            $items[$vId][$piece['slot']] = $itemId;
                    }
                }
                else
                    $items[$vId][$piece['slot']] = $itemId;

                /* check for type */

                // skip cloaks, they mess with armor classes
                if ($piece['slot'] == 16)
                    continue;

                // skip event-sets
                if ($piece['Quality'] == 1)
                    continue;

                if ($piece['class'] == 2 && $piece['subclass'] == 0)
                    $type = 8;                              // 1H-Axe
                else if ($piece['class'] == 2 && $piece['subclass'] == 4)
                    $type = 9;                              // 1H-Mace
                else if ($piece['class'] == 2 && $piece['subclass'] == 7)
                    $type = 10;                             // 1H-Sword
                else if ($piece['class'] == 2 && $piece['subclass'] == 13)
                    $type = 7;                              // Fist Weapon
                else if ($piece['class'] == 2 && $piece['subclass'] == 15)
                    $type = 5;                              // Dagger

                if (!$type)
                {
                    if ($piece['class'] == 4 && $piece['slot'] == 12)
                        $type = 11;                         // trinket
                    else if ($piece['class'] == 4 && $piece['slot'] == 2)
                        $type = 12;                         // amulet
                    else if ($piece['class'] == 4 && $piece['subclass'] != 0)
                        $type = $piece['subclass'];         // 'armor' set

                    if ($piece['class'] == 4 && $piece['slot'] == 11)
                        $hasRing = true;                    // contains ring
                }
            }

            if ($hasRing && !$type)
                $type = 6;                                  // pure ring-set

            $isMultiSet  = false;
            $oldSlotMask = 0x0;
            foreach ($items as $subset)
            {
                $curSlotMask = 0x0;
                foreach ($subset as $slot => $item)
                    $curSlotMask |= (1 << $slot);

                if ($oldSlotMask && $oldSlotMask == $curSlotMask)
                {
                    $isMultiSet = true;
                    break;
                }

                $oldSlotMask = $curSlotMask;
            }

            if (!$isMultiSet || !$canReuse || $setId == 555)
            {
                $temp = [];
                foreach ($items as $subset)
                {
                    foreach ($subset as $slot => $item)
                    {
                        if (isset($temp[$slot]) && $temp[$slot] < $item)
                            CLI::write("set: ".$setId." - conflict between item: ".$item." and item: ".$temp[$slot]." choosing lower itemId", CLI::LOG_WARN);
                        else if ($slot == 13 || $slot = 11) // special case
                            $temp[] = $item;
                        else
                            $temp[$slot] = $item;
                    }
                }

                $items   = [$temp];
                $heroic  = [reset($heroic)];
                $nPieces = [count($temp)];
                $quality = [reset($quality)];
                $reqLvl  = [reset($reqLvl)];
                $max     = $max ? [max($max)] : [0];
                $min     = $min ? [min($min)] : [0];
            }

            foreach ($items as &$subsets)
                $subsets = array_pad($subsets, 10, 0);

            /********************/
            /* calc statbonuses */
            /********************/

            for ($i = 1; $i < 9; $i++)
                if ($setData['spellId'.$i] > 0 && $setData['itemCount'.$i] > 0)
                    $spells[] = [$setData['spellId'.$i], $setData['itemCount'.$i]];

            $bonusSpells = new SpellList(array(['s.id', array_column($spells, 0)]));
            $mods = $bonusSpells->getStatGain();

            $spells = array_pad($spells, 8, [0, 0]);

            for ($i = 1; $i < 9; $i++)
                if ($setData['itemCount'.$i] > 0 && !empty($mods[$setData['spellId'.$i]]))
                    $gains[$setData['itemCount'.$i]] = $mods[$setData['spellId'.$i]];

            /**************************/
            /* get name & description */
            /**************************/

            foreach (array_keys(array_filter(Util::$localeStrings)) as $loc)
            {
                User::useLocale($loc);

                $name[$loc] = Util::localizedString($setData, 'name');

                foreach ($bonusSpells->iterate() as $__)
                {
                    if (!isset($descText[$loc]))
                        $descText[$loc] = '';

                    $descText[$loc] .= $bonusSpells->parseText()[0]."\n";
                }

                // strip rating blocks - e.g. <!--rtg19-->14&nbsp;<small>(<!--rtg%19-->0.30%&nbsp;@&nbsp;L<!--lvl-->80)</small>
                $descText[$loc] = preg_replace('/<!--rtg\d+-->(\d+)&nbsp.*?<\/small>/i', '\1', $descText[$loc]);
            }

            /****************************/
            /* finalaize data and write */
            /****************************/

            foreach ($items as $vId => $vSet)
            {
                $note = 0;
                foreach ($this->tagsById as $tag => $sets)
                {
                    if (!in_array($setId, $sets))
                        continue;

                    $note = $tag;
                }

                if (!$note && $min > 120 && $classMask && $classMask != CLASS_MASK_ALL)
                {
                    foreach ($this->tagsByNamePart as $tag => $strings)
                    {
                        foreach ($strings as $str)
                        {
                            if (isset($pieces[reset($vSet)]) && stripos($pieces[reset($vSet)]['name'], $str) === 0)
                            {
                                $note = $tag;
                                break 2;
                            }
                        }
                    }
                }

                $row   = [];

                $row[] = $vId < 0 ? --$virtualId : $setId;
                $row[] = $setId;                            // refSetId
                $row[] = 0;                                 // cuFlags
                $row   = array_merge($row, $name, $vSet);
                foreach (array_column($spells, 0) as $spellId)
                    $row[] = $spellId;
                foreach (array_column($spells, 1) as $nItems)
                    $row[] = $nItems;
                $row   = array_merge($row, $descText);
                $row[] = serialize($gains);
                $row[] = $nPieces[$vId];
                $row[] = $min[$vId];
                $row[] = $max[$vId];
                $row[] = $reqLvl[$vId];
                $row[] = $classMask == CLASS_MASK_ALL ? 0 : $classMask;
                $row[] = !empty($heroic[$vId]) ? 1 : 0;
                $row[] = $quality[$vId];
                $row[] = $type;
                $row[] = $note;                             // contentGroup
                $row[] = $holiday;
                $row[] = $setData['reqSkillId'];
                $row[] = $setData['reqSkillLevel'];

                DB::Aowow()->query('REPLACE INTO ?_itemset VALUES (?a)', array_values($row));
            }
        }

        // hide empty sets
        DB::Aowow()->query('UPDATE ?_itemset SET cuFlags = cuFlags | ?d WHERE item1 = 0', CUSTOM_EXCLUDE_FOR_LISTVIEW);

        return true;
    }
});
?>
