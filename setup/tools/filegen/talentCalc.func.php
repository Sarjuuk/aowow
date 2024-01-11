<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // talents
    // i - int talentId (id of aowow_talent)
    // n - str name (spellname of aowow_spell for spellID = rank1)
    // m - int number of ranks (6+ are empty)
    // s - array:int spells to ranks (rank1, rank2, ..., rank5 of aowow_talent)
    // d - array:str description of spells
    // x - int column (col from aowow_talent)
    // y - int row (row of aowow_talent)
    // r - array:int on what the talent depends on: "r:[u, v]", u - nth talent in tree, v - required rank of u
    // f - array:int [pets only] creatureFamilies, that use this spell
    // t - array:str if the talent teaches a spell, this is the upper tooltip-table containing castTime, cost, cooldown
    // j - array of modifier-arrays per rank for the Profiler [nyi]
    // tabs
    // n - name of the tab
    // t - array of talent-objects
    // f - array:int [pets only] creatureFamilies in that category

    // builds talent-tree-data for the talent-calculator
    // this script requires the following dbc-files to be available
    $reqDBC = ['talenttab', 'talent', 'spell', 'creaturefamily', 'spellicon'];

    function talentCalc()
    {
        $success   = true;
        $spellMods = (new SpellList(array(['typeCat', -2], CFG_SQL_LIMIT_NONE)))->getProfilerMods();

        $buildTree = function ($class) use (&$petFamIcons, &$tSpells, $spellMods)
        {
            $petCategories = [];

            $mask = $class ? 1 << ($class - 1) : 0;

            // All "tabs" of a given class talent
            $tabs   = DB::Aowow()->select('SELECT * FROM dbc_talenttab WHERE classMask = ?d ORDER BY `tabNumber`, `creatureFamilyMask`', $mask);
            $result = [];

            for ($tabIdx = 0; $tabIdx < count($tabs); $tabIdx++)
            {
                $talents = DB::Aowow()->select('SELECT t.id AS tId, t.*, s.name_loc0, s.name_loc2, s.name_loc3, s.name_loc4, s.name_loc6, s.name_loc8, LOWER(SUBSTRING_INDEX(si.iconPath, "\\\\", -1)) AS iconString FROM dbc_talent t, dbc_spell s, dbc_spellicon si WHERE si.`id` = s.`iconId` AND t.`tabId`= ?d AND s.`id` = t.`rank1` ORDER  by t.`row`, t.`column`', $tabs[$tabIdx]['id']);
                $result[$tabIdx] = array(
                    'n' => Util::localizedString($tabs[$tabIdx], 'name'),
                    't' => []
                );

                if (!$class)
                {
                    $petFamId           = log($tabs[$tabIdx]['creatureFamilyMask'], 2);
                    $result[$tabIdx]['icon'] = $petFamIcons[$petFamId];
                    $petCategories      = DB::Aowow()->SelectCol('SELECT id AS ARRAY_KEY, categoryEnumID FROM dbc_creaturefamily WHERE petTalentType = ?d', $petFamId);
                    $result[$tabIdx]['f']    = array_keys($petCategories);
                }

                // talent dependencies go here
                $depLinks = [];
                $tNums    = [];

                for ($talentIdx = 0; $talentIdx < count($talents); $talentIdx++)
                {
                    $tNums[$talents[$talentIdx]['tId']] = $talentIdx;

                    $d    = [];
                    $s    = [];
                    $i    = $talents[$talentIdx]['tId'];
                    $n    = Util::localizedString($talents[$talentIdx], 'name');
                    $x    = $talents[$talentIdx]['column'];
                    $y    = $talents[$talentIdx]['row'];
                    $r    = null;
                    $t    = [];
                    $j    = [];
                    $icon = $talents[$talentIdx]['iconString'];
                    $m    = $talents[$talentIdx]['rank2'] == 0 ? 1 : (
                                $talents[$talentIdx]['rank3'] == 0 ? 2 : (
                                    $talents[$talentIdx]['rank4'] == 0 ? 3 : (
                                        $talents[$talentIdx]['rank5'] == 0 ? 4 : 5
                                    )
                                )
                            );

                    // duplet handling
                    $f = [];
                    foreach ($petCategories as $k => $v)
                    {
                        // cant handle 64bit integer .. split
                        if ($v >= 32 && ((1 << ($v - 32)) & $talents[$talentIdx]['petCategory2']))
                            $f[] = $k;
                        else if ($v < 32 && ((1 << $v) & $talents[$talentIdx]['petCategory1']))
                            $f[] = $k;
                    }

                    for ($itr = 0; $itr <= ($m - 1); $itr++)
                    {
                        if (!$tSpells->getEntry($talents[$talentIdx]['rank'.($itr + 1)]))
                            continue;

                        $d[] = $tSpells->parseText()[0];
                        $s[] = $talents[$talentIdx]['rank'.($itr + 1)];
                        if (isset($spellMods[$talents[$talentIdx]['rank'.($itr + 1)]]))
                            $j[] = $spellMods[$talents[$talentIdx]['rank'.($itr + 1)]];
                        else
                            $j[] = null;

                        if ($talents[$talentIdx]['talentSpell'])
                            $t[] = $tSpells->getTalentHeadForCurrent();
                    }

                    if ($talents[$talentIdx]['reqTalent'])
                    {
                        // we didn't encounter the required talent yet => create reference
                        if (!isset($tNums[$talents[$talentIdx]['reqTalent']]))
                            $depLinks[$talents[$talentIdx]['reqTalent']] = $talentIdx;

                        $r = [$tNums[$talents[$talentIdx]['reqTalent']] ?? 0, $talents[$talentIdx]['reqRank'] + 1];
                    }

                    $result[$tabIdx]['t'][$talentIdx] = array(
                        'i' => $i,
                        'n' => $n,
                        'm' => $m,
                        'd' => $d,
                        's' => $s,
                        'x' => $x,
                        'y' => $y,
                        'j' => $j
                    );

                    if (isset($r))
                        $result[$tabIdx]['t'][$talentIdx]['r'] = $r;

                    if (!empty($t))
                        $result[$tabIdx]['t'][$talentIdx]['t'] = $t;

                    if (!empty($f))
                        $result[$tabIdx]['t'][$talentIdx]['f'] = $f;

                    if ($class)
                        $result[$tabIdx]['t'][$talentIdx]['iconname'] = $icon;

                    // If this talent is a reference, add it to the array of talent dependencies
                    if (isset($depLinks[$talents[$talentIdx]['tId']]))
                    {
                        $result[$tabIdx]['t'][$depLinks[$talents[$talentIdx]['tId']]]['r'][0] = $talentIdx;
                        unset($depLinks[$talents[$talentIdx]['tId']]);
                    }
                }

                // Remove all dependencies for which the talent has not been found
                foreach ($depLinks as $dep_link)
                    unset($result[$tabIdx]['t'][$dep_link]['r']);
            }

            return $result;
        };

        // my neighbour is noisy as fuck and my head hurts, so ..
        $petFamIcons = ['Ability_Druid_KingoftheJungle', 'Ability_Druid_DemoralizingRoar', 'Ability_EyeOfTheOwl']; // .. i've no idea where to fetch these from
        $classes     = [CLASS_WARRIOR, CLASS_PALADIN, CLASS_HUNTER, CLASS_ROGUE, CLASS_PRIEST, CLASS_DEATHKNIGHT, CLASS_SHAMAN, CLASS_MAGE, CLASS_WARLOCK, CLASS_DRUID];
        $petIcons    = '';

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!CLISetup::writeDir('datasets/'.$dir))
                $success = false;

        $tSpellIds = DB::Aowow()->selectCol('SELECT rank1 FROM dbc_talent UNION SELECT rank2 FROM dbc_talent UNION SELECT rank3 FROM dbc_talent UNION SELECT rank4 FROM dbc_talent UNION SELECT rank5 FROM dbc_talent');
        $tSpells   = new SpellList(array(['s.id', $tSpellIds], CFG_SQL_LIMIT_NONE));

        foreach (CLISetup::$localeIds as $lId)
        {
            User::useLocale($lId);
            Lang::load($lId);

            // TalentCalc
            foreach ($classes as $cMask)
            {
                set_time_limit(20);

                $cId    = log($cMask, 2) + 1;
                $file   = 'datasets/'.User::$localeString.'/talents-'.$cId;
                $toFile = '$WowheadTalentCalculator.registerClass('.$cId.', '.Util::toJSON($buildTree($cId)).')';

                if (!CLISetup::writeFile($file, $toFile))
                    $success = false;
            }

            // PetCalc
            if (empty($petIcons))
            {
                $pets = DB::Aowow()->SelectCol('SELECT id AS ARRAY_KEY, LOWER(SUBSTRING_INDEX(iconString, "\\\\", -1)) AS iconString FROM dbc_creaturefamily WHERE petTalentType IN (0, 1, 2)');
                $petIcons = Util::toJSON($pets);
            }

            $toFile  = "var g_pet_icons = ".$petIcons.";\n\n";
            $toFile .= 'var g_pet_talents = '.Util::toJSON($buildTree(0)).';';
            $file    = 'datasets/'.User::$localeString.'/pet-talents';

            if (!CLISetup::writeFile($file, $toFile))
                $success = false;
        }

        return $success;
    }
?>
