<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    /* deps:
     * player_classlevelstats
     * player_levelstats
    */

    // Create 'statistics'-file in datasets
    // this script requires the following dbcs to be available
    $reqDBC = ['gtchancetomeleecrit', 'gtchancetomeleecritbase', 'gtchancetospellcrit', 'gtchancetospellcritbase', 'gtoctregenhp', 'gtregenmpperspt', 'gtregenhpperspt'];

    function statistics()
    {
        $classs = function()
        {
            // constants and mods taken from TrinityCore (Player.cpp, StatSystem.cpp)

            /*  content per Index
                mleatkpwr[base, strMultiplier, agiMultiplier, levelMultiplier]
                rngatkpwr[base, strMultiplier, agiMultiplier, levelMultiplier]
                baseCritPct[phys, spell]
                diminishingConstant
                baseDodgePct
                DodgeCap
                baseParryPct
                ParryCap
                baseBlockPct
                directMod1   applies mod directly       only one class having something worth mentioning: DK
                directMod2   applies mod directly       so what were they originally used for..?
            */

            $dataz = array(
                 1 => [[-20, 2, 0, 3], [-10, 0, 1, 1], null, 0.9560,  3.6640,  88.129021, 5,  47.003525, 5, 0, 0],
                 2 => [[-20, 2, 0, 3], [-10, 0, 1, 0], null, 0.9560,  3.4943,  88.129021, 5,  47.003525, 5, 0, 0],
                 3 => [[-20, 1, 1, 2], [-10, 0, 2, 2], null, 0.9880, -4.0873, 145.560408, 5, 145.560408, 0, 0, 0],
                 4 => [[-20, 1, 1, 2], [-10, 0, 1, 1], null, 0.9880,  2.0957, 145.560408, 5, 145.560408, 0, 0, 0],
                 5 => [[-10, 1, 0, 0], [-10, 0, 1, 0], null, 0.9830,  3.4178, 150.375940, 0,   0.0,      0, 0, 0],
                 6 => [[-20, 2, 0, 3], [-10, 0, 1, 0], null, 0.9560,  3.6640,  88.129021, 5,  47.003525, 0, 0, ['parryrtng' => [0.25, 'percentOf', 'str']]],  // Forcefull Deflection (49410)
                 7 => [[-20, 1, 1, 2], [-10, 0, 1, 0], null, 0.9880,  2.1080, 145.560408, 0, 145.560408, 5, 0, 0],
                 8 => [[-10, 1, 0, 0], [-10, 0, 1, 0], null, 0.9830,  3.6587, 150.375940, 0,   0.0,      0, 0, 0],
                 9 => [[-10, 1, 0, 0], [-10, 0, 1, 0], null, 0.9830,  2.4211, 150.375940, 0,   0.0,      0, 0, 0],
                11 => [[-20, 2, 0, 0], [-10, 0, 1, 0], null, 0.9720,  5.6097, 116.890707, 0,   0.0,      0, 0, 0]
            );

            foreach ($dataz as $class => &$data)
                $data[2] = array_values(DB::Aowow()->selectRow('SELECT mle.chance*100 cMle, spl.chance*100 cSpl FROM dbc_gtchancetomeleecritbase mle, dbc_gtchancetospellcritbase spl WHERE mle.idx = spl.idx AND mle.idx = ?d', $class - 1));

            return $dataz;
        };

        $race = function()
        {
            // { str, agi, sta, int, spi, hp, mana, directMod1, directMod2 }

            return array(
                 1 => [20, 20, 20, 20, 20, 0, ['spi' => [0.05, 'percentOf', 'spi']]],                                                                   // The Human Spirit (20598)
                 2 => [23, 17, 22, 17, 23, 0, 0],
                 3 => [22, 16, 23, 19, 19, 0, 0],
                 4 => [17, 25, 19, 20, 20, 0, 0],
                 5 => [19, 18, 21, 18, 25, 0, 0],
                 6 => [25, 15, 22, 15, 22, 0, ['health' => [0.05, 'functionOf', '$function(p) { return g_statistics.combo[p.classs][p.level][5]; }']]], // Endurance (20550) ... if you are looking for something elegant, look away!
                 7 => [15, 23, 19, 24, 20, 0, ['int' => [0.05, 'percentOf', 'int']]],                                                                   // Expansive Mind (20591)
                 8 => [21, 22, 21, 16, 21, 0, ['healthrgn' => [0.1, 'percentOf', 'healthrgn']]],                                                        // Regeneration (20555)
                10 => [17, 22, 18, 24, 19, 0, 0],
                11 => [21, 17, 19, 21, 22, 0, 0]                                                                                                        // ['mlehitpct' => [1, 'add'], 'splhitpct' => [1, 'add'], 'rgdhitpct' => [1, 'add']]    // Heroic Presence (6562, 28878) (not actually shown..?)
            );
        };

        $combo = function()
        {
            $result = [];
            $critToDodge = array(
                1 => 0.85/1.15,   2 => 1.00/1.15,   3 => 1.11/1.15,
                4 => 2.00/1.15,   5 => 1.00/1.15,   6 => 0.85/1.15,
                7 => 1.60/1.15,   8 => 1.00/1.15,   9 => 0.97/1.15,   11 => 2.00/1.15
            );

            // TrinityCore claims, DodgePerAgi per level and class can be constituted from critPerAgi (and level (and class))
            // who am i to argue
            // rebase stats to a specific race. chosen human as all stats are 20
            // level:{ str, agi, sta, int, spi, hp, mana, mleCrt%Agi, splCrt%Int, dodge%Agi, HealthRegenModToBaseStat, HealthRegenModToBonusStat }

            foreach ($critToDodge as $class => $mod)
            {
                // humans can't be hunter, shaman, druids (use tauren here)
                if (in_array($class, [3, 7, 11]))
                    $offset = [25, 15, 22, 15, 22];
                else
                    $offset = [20, 20, 20, 20, 20];

                $gtData = DB::Aowow()->select('
                    SELECT mlecrt.idx - ?d AS ARRAY_KEY, mlecrt.chance * 100, splcrt.chance * 100, mlecrt.chance * 100 * ?f, baseHP5.ratio * 1, extraHP5.ratio * 1
                    FROM   dbc_gtchancetomeleecrit mlecrt
                    JOIN   dbc_gtchancetospellcrit splcrt ON splcrt.idx   = mlecrt.idx
                    JOIN   dbc_gtoctregenhp baseHP5       ON baseHP5.idx  = mlecrt.idx
                    JOIN   dbc_gtregenhpperspt extraHP5   ON extraHP5.idx = mlecrt.idx
                    WHERE  mlecrt.idx BETWEEN ?d AND ?d',
                    (($class - 1) * 100) - 1,                                   // class-offset
                    $mod,
                    (($class - 1) * 100) + 0,                                   // lvl 1
                    (($class - 1) * 100) + 79                                   // lvl 80
                );

                $rows = DB::World()->select('
                    SELECT
                        pls.level AS ARRAY_KEY,
                        pls.str - ?d, pls.agi - ?d, pls.sta - ?d, pls.inte - ?d, pls.spi - ?d,
                        pcls.basehp, IF(pcls.basemana <> 0, pcls.basemana, 100)
                    FROM
                        player_levelstats pls
                    JOIN
                        player_classlevelstats pcls ON pls.level = pcls.level AND pls.class = pcls.class
                    WHERE
                        pls.race = ?d AND pls.class = ?d ORDER BY pls.level ASC',
                    $offset[0], $offset[1], $offset[2], $offset[3], $offset[4],
                    in_array($class, [3, 7, 11]) ? 6 : 1,
                    $class
                );

                $result[$class] = [];
                foreach ($rows as $lvl => $row)
                    $result[$class][$lvl] = array_values(array_merge($row, $gtData[$lvl]));
            }

            return $result;
        };

        $level = function()
        {
            // base mana regeneration per level
            // identical across classes (just use one, that acutally has mana (offset: 100))
            // content of gtRegenMPPerSpt.dbc

            return DB::Aowow()->selectCol('SELECT idx-99 AS ARRAY_KEY, ratio FROM dbc_gtregenmpperspt WHERE idx >= 100 AND idx < 100 + ?d', MAX_LEVEL);
        };

        $skills = function()
        {
            // profession perks (skinning => +crit, mining => +stam) and maybe some others;      skillId:{rankNo:someJSON, ..}?

            return [];
        };

        // todo:                                         x
        $sub     = ['classs', 'race', 'combo', 'level', 'skills'];
        $out     = [];
        $success = true;

        foreach ($sub as $s)
        {
            $res = $$s();
            $out[$s] = $res;
            if (!$res)
                CLISetup::log('statistics - generator $'.$s.'() returned empty', CLISetup::LOG_WARN);
        }

        $toFile = 'g_statistics = '.preg_replace('/"\$([^$"]+)"/', '\1', Util::toJSON($out)).';';

        if (!CLISetup::writeFile('datasets/statistics', $toFile))
            $success = false;

        return $success;
    }

?>