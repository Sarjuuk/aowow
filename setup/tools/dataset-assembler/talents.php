<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // builds talent-tree-data for the talent-calculator
    // this script requires the following dbc-files to be parsed and available
    // Talent, TalentTab, Spell, CreatureFamily

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
    // tabs
    // n - name of the tab
    // t - array of talent-objects
    // f - array:int [pets only] creatureFamilies in that category

    function buildTree($class)
    {
        global $petFamIcons;    // h8!
        $petCategories = [];

        $mask = $class ? 1 << ($class - 1) : 0;

        // All "tabs" of a given class talent
        $tabs = DB::Aowow()->select('
            SELECT
                *
            FROM
                ?_talenttab
            WHERE
                classes = ?d
            ORDER BY
                `order`, `pets`',
            $mask
        );

        $result = [];

        for ($l = 0; $l < count($tabs); $l++)
        {
            $talents = DB::Aowow()->select('
                    SELECT
                        t.id AS tId,
                        t.*,
                        s.*
                    FROM
                        ?_talent t,
                        ?_spell s
                    WHERE
                        t.`tab`= ?d AND
                        s.`Id` = t.`rank1`
                    ORDER by t.`row`, t.`col`
                ',
                $tabs[$l]['id']
            );

            $result[$l] = array(
                'n' => Util::localizedString($tabs[$l], 'name'),
                't' => []
            );

            if (!$class)
            {
                $petFamId           = log($tabs[$l]['pets'], 2);
                $result[$l]['icon'] = $petFamIcons[$petFamId];
                $petCategories      = DB::Aowow()->SelectCol('SELECT Id AS ARRAY_KEY, category FROM ?_pet WHERE type = ?d', $petFamId);
                $result[$l]['f']    = array_keys($petCategories);
            }

            // talent dependencies go here
            $depLinks = [];
            $tNums    = [];

            for($j = 0; $j < count($talents); $j++)
            {
                $tNums[$talents[$j]['tId']] = $j;

                $d    = [];
                $s    = [];
                $i    = $talents[$j]['tId'];
                $n    = Util::localizedString($talents[$j], 'name');
                $x    = $talents[$j]['col'];
                $y    = $talents[$j]['row'];
                $r    = null;
                $t    = [];
                $icon = $talents[$j]['iconString'];
                $m    = $talents[$j]['rank2'] == 0 ? 1 : (
                            $talents[$j]['rank3'] == 0 ? 2 : (
                                $talents[$j]['rank4'] == 0 ? 3 : (
                                    $talents[$j]['rank5'] == 0 ? 4 : 5
                                )
                            )
                        );

                // duplet handling
                $f = [];
                foreach ($petCategories as $k => $v)
                {
                    // cant handle 64bit integer .. split
                    if ($v >= 32 && ((1 << ($v - 32)) & $talents[$j]['petmask2']))
                        $f[] = $k;
                    else if ($v < 32 && ((1 << $v) & $talents[$j]['petmask']))
                        $f[] = $k;
                }

                for ($k = 0; $k <= ($m - 1); $k++)
                {
                    $tSpell = new SpellList(array(['s.id', (int)$talents[$j]['rank'.($k + 1)]]));
                    $d[] = $tSpell->parseText();
                    $s[] = $talents[$j]['rank'.($k + 1)];

                    if ($talents[$j]['isSpell'])
                        $t[] = $tSpell->getTalentHeadForCurrent();
                }

                if ($talents[$j]['dependsOn'])
                {
                    // we didn't encounter the required talent yet => create reference
                    if (!isset($tNums[$talents[$j]['dependsOn']]))
                        $depLinks[$talents[$j]['dependsOn']] = $j;

                    $r = [$tNums[$talents[$j]['dependsOn']], $talents[$j]['dependsOnRank'] + 1];
                }

                $result[$l]['t'][$j] = array(
                    'i' => $i,
                    'n' => $n,
                    'm' => $m,
                    'd' => $d,
                    's' => $s,
                    'x' => $x,
                    'y' => $y,
                );

                if (isset($r))
                    $result[$l]['t'][$j]['r'] = $r;

                if (!empty($t))
                    $result[$l]['t'][$j]['t'] = $t;

                if (!empty($f))
                    $result[$l]['t'][$j]['f'] = $f;

                if ($class)
                    $result[$l]['t'][$j]['iconname'] = $icon;

                // If this talent is a reference, add it to the array of talent dependencies
                if (isset($depLinks[$talents[$j]['tId']]))
                {
                    $result[$l]['t'][$depLinks[$talents[$j]['tId']]]['r'][0] = $j;
                    unset($depLinks[$talents[$j]['tId']]);
                }
            }

            // Remove all dependencies for which the talent has not been found
            foreach ($depLinks as $dep_link)
                unset($result[$l]['t'][$dep_link]['r']);
        }

        return $result;
    }

    $classes       = [CLASS_WARRIOR, CLASS_PALADIN, CLASS_HUNTER, CLASS_ROGUE, CLASS_PRIEST, CLASS_DEATHKNIGHT, CLASS_SHAMAN, CLASS_MAGE, CLASS_WARLOCK, CLASS_DRUID];
    $locales       = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    // my neighbour is noisy as fuck and my head hurts, so ..
    $petFamIcons   = ['Ability_Druid_KingoftheJungle', 'Ability_Druid_DemoralizingRoar', 'Ability_EyeOfTheOwl']; // .. i've no idea where to fetch these from
    $petIcons      = '';

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);

        // TalentCalc
        foreach ($classes as $cMask)
        {
            $cId    = log($cMask, 2) + 1;
            $file   = 'datasets\\'.User::$localeString.'\\talents-'.$cId;
            $toFile = '$WowheadTalentCalculator.registerClass('.$cId.', '.json_encode(buildTree($cId), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK).')';

            $handle = fOpen($file, "w");
            fWrite($handle, $toFile);
            fClose($handle);

            echo "done class: ".$cId." loc: ".$lId." in ".Util::execTime()."<br>\n";
        }

        // PetCalc
        if (empty($petIcons))
        {
            $pets = DB::Aowow()->SelectCol('SELECT Id AS ARRAY_KEY, iconString FROM ?_pet');
            $petIcons = json_encode($pets, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        }

        $toFile  = "var g_pet_icons = ".$petIcons."\n\n";
        $toFile .= 'var g_pet_talents = '.json_encode(buildTree(0), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        $file    = 'datasets\\'.User::$localeString.'\\pet-talents';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done pets loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    User::useLocale(LOCALE_EN);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);

?>
