<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// Create 'statistics'-file in datasets
CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'statistics' => [[], CLISetup::ARGV_PARAM, 'Compiles player stats into file for the character profiler tool.']
    );

    protected $worldDependency = ['player_levelstats', 'player_classlevelstats'];
    protected $dbcSourceFiles  = ['gtchancetomeleecrit', 'gtchancetomeleecritbase', 'gtchancetospellcrit', 'gtchancetospellcritbase', 'gtoctregenhp', 'gtregenmpperspt', 'gtregenhpperspt'];
    protected $requiredDirs    = ['datasets/'];

    public function generate() : bool
    {
        $sub = ['classs', 'race', 'combo', 'level', 'skills'];
        $out = [];

        foreach ($sub as $s)
        {
            if ($out[$s] = $this->$s())
                continue;

            CLI::write('[statistics] generator '.$s.'() returned empty', CLI::LOG_WARN);
            $this->success = false;
        }

        $toFile = 'g_statistics = '.preg_replace('/"\$([^$"]+)"/', '\1', Util::toJSON($out)).';';

        if (!CLISetup::writeFile('datasets/statistics', $toFile))
            $this->success = false;

        return $this->success;
    }

    // constants and mods taken from TrinityCore (Player.cpp, StatSystem.cpp)
    private function classs() : array
    {
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
            classMod1   applies mod directly       only one class having something worth mentioning: DK
            classMod2   applies mod directly       so what were they originally used for..?
        */

        $dataz = array(
             1 => [[-20, 2, 0, 3], [-10, 0, 1, 1], null, 0.9560,  3.6640,  88.129021, 5,  47.003525, 5, [], []],
             2 => [[-20, 2, 0, 3], [-10, 0, 1, 0], null, 0.9560,  3.4943,  88.129021, 5,  47.003525, 5, [], []],
             3 => [[-20, 1, 1, 2], [-10, 0, 1, 2], null, 0.9880, -4.0873, 145.560408, 5, 145.560408, 0, [], []],
             4 => [[-20, 1, 1, 2], [-10, 0, 1, 1], null, 0.9880,  2.0957, 145.560408, 5, 145.560408, 0, [], []],
             5 => [[-10, 1, 0, 0], [-10, 0, 1, 0], null, 0.9830,  3.4178, 150.375940, 0,   0.0,      0, [], []],
             6 => [[-20, 2, 0, 3], [-10, 0, 1, 0], null, 0.9560,  3.6640,  88.129021, 5,  47.003525, 0, [], ['parryrtng' => [0.25, 'percentOf', 'str']]],  // Forcefull Deflection (49410)
             7 => [[-20, 1, 1, 2], [-10, 0, 1, 0], null, 0.9880,  2.1080, 145.560408, 0, 145.560408, 5, [], []],
             8 => [[-10, 1, 0, 0], [-10, 0, 1, 0], null, 0.9830,  3.6587, 150.375940, 0,   0.0,      0, [], []],
             9 => [[-10, 1, 0, 0], [-10, 0, 1, 0], null, 0.9830,  2.4211, 150.375940, 0,   0.0,      0, [], []],
            11 => [[-20, 2, 0, 0], [-10, 0, 1, 0], null, 0.9720,  5.6097, 116.890707, 0,   0.0,      0, [], []]
        );

        foreach ($dataz as $class => &$data)
            $data[2] = array_values(DB::Aowow()->selectRow('SELECT mle.chance*100 cMle, spl.chance*100 cSpl FROM dbc_gtchancetomeleecritbase mle, dbc_gtchancetospellcritbase spl WHERE mle.idx = spl.idx AND mle.idx = ?d', $class - 1));

        return $dataz;
    }

    // { str, agi, sta, int, spi, raceMod1, raceMod2 }
    private function race() : array
    {
        $raceData = DB::World()->select('SELECT `race` AS ARRAY_KEY, MIN(`str`), MIN(`agi`), MIN(`sta`), MIN(`inte`), MIN(`spi`) FROM player_levelstats WHERE `level` = 1 GROUP BY `race` ORDER BY `race` ASC');
        foreach ($raceData as &$rd)
            $rd = array_values($rd + [[], []]);

        $racials = new SpellList(array(['typeCat', -4], ['reqClassMask', 0]));
        $allMods = $racials->getProfilerMods();
        foreach ($allMods as $spellId => $mods)
        {
            if (!$mods)
                continue;

            // if there is ever a case where a racial is shared between races i don't want to know about it!
            $raceId = log($racials->getEntry($spellId)['reqRaceMask'], 2) + 1;
            if (!isset($raceData[$raceId]))
                continue;

            foreach ($mods as $jsonStat => $mod)
            {
                if (empty($raceData[$raceId][5][$jsonStat]))
                    $raceData[$raceId][5][$jsonStat] = $mod;
                else
                    $raceData[$raceId][6][$jsonStat] = $mod;
            }
        }

        return $raceData;
    }

    // TrinityCore claims, DodgePerAgi per level and class can be constituted from critPerAgi (and level (and class))
    // who am i to argue
    // rebase stats to a specific race. chosen human as all stats are 20 and tauren for hunter, shaman and druid
    // level:{ str, agi, sta, int, spi, hp, mana, mleCrt%Agi, splCrt%Int, dodge%Agi, HealthRegenModToBaseStat, HealthRegenModToBonusStat }
    private function combo() : array
    {
        $result = [];
        $critToDodge = array(
            1 => 0.85/1.15,   2 => 1.00/1.15,   3 => 1.11/1.15,
            4 => 2.00/1.15,   5 => 1.00/1.15,   6 => 0.85/1.15,
            7 => 1.60/1.15,   8 => 1.00/1.15,   9 => 0.97/1.15,   11 => 2.00/1.15
        );

        foreach ($critToDodge as $class => $mod)
        {
            // humans can't be hunter, shaman, druids (use tauren here)
            if (in_array($class, [3, 7, 11]))
                $offset = array_values(DB::World()->selectRow('SELECT MIN(`str`), MIN(`agi`), MIN(`sta`), MIN(`inte`), MIN(`spi`) FROM player_levelstats WHERE `level` = 1 AND `race` = 6'));
            else
                $offset = array_values(DB::World()->selectRow('SELECT MIN(`str`), MIN(`agi`), MIN(`sta`), MIN(`inte`), MIN(`spi`) FROM player_levelstats WHERE `level` = 1 AND `race` = 1'));

            $gtData = DB::Aowow()->select(
               'SELECT mlecrt.idx - ?d AS ARRAY_KEY, mlecrt.chance * 100, splcrt.chance * 100, mlecrt.chance * 100 * ?f, baseHP5.ratio * 1, extraHP5.ratio * 1
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

            $rows = DB::World()->select(
               'SELECT   pls.level AS ARRAY_KEY,
                         pls.str - ?d, pls.agi - ?d, pls.sta - ?d, pls.inte - ?d, pls.spi - ?d,
                         pcls.basehp, IF(pcls.basemana <> 0, pcls.basemana, 100)
                FROM     player_levelstats pls
                JOIN     player_classlevelstats pcls ON pls.level = pcls.level AND pls.class = pcls.class
                WHERE    pls.race = ?d AND
                         pls.class = ?d
                ORDER BY pls.level ASC',
                $offset[0], $offset[1], $offset[2], $offset[3], $offset[4],
                in_array($class, [3, 7, 11]) ? 6 : 1,
                $class
            );

            $result[$class] = [];
            foreach ($rows as $lvl => $row)
                $result[$class][$lvl] = array_values(array_merge($row, $gtData[$lvl]));
        }

        return $result;
    }

    // base mana regeneration per level
    // identical across classes (just use one, that acutally has mana (offset: 100))
    // content of gtRegenMPPerSpt.dbc
    private function level() : array
    {
        return DB::Aowow()->selectCol('SELECT idx-99 AS ARRAY_KEY, ratio FROM dbc_gtregenmpperspt WHERE idx >= 100 AND idx < 100 + ?d', MAX_LEVEL);
    }

    // profession perks ... too lazy to formulate a search algorithm for two occurences
    private function skills() : array
    {
        // DB::Aowow()->select(
        //    'SELECT sk.id AS "skillId", sla.reqSkillLevel, s.effect1AuraId AS "auraId", s.effect1MiscValue, s.effect1BasePoints + s.effect1DieSides AS "qty"
        //     FROM   dbc_skilllineability sla
        //     JOIN   dbc_skillline sk ON sk.id = sla.skilllineid
        //     JOIN   dbc_spell s ON s.id = sla.spellId
        //     WHERE  sla.acquiremethod = 1 AND             // learn on skillup
        //            sk.categoryId = 11 AND                // primary profession
        //            s.effect1Id = 6 AND                   // ApplyAura
        //            s.effect1AuraId IN (29, 189)          // ModStatFlat, ModRating (need more?)
        //     ');

        return array(
            SKILL_MINING => array(                          // mining / toughness
                 75 => ['sta' =>  3],
                150 => ['sta' =>  5],
                225 => ['sta' =>  7],
                300 => ['sta' => 10],
                375 => ['sta' => 30],
                450 => ['sta' => 60],
            ),
            SKILL_SKINNING => array(                        // skinning / master of anatomy
                 75 => ['critstrkrtng' =>  3],
                150 => ['critstrkrtng' =>  6],
                225 => ['critstrkrtng' =>  9],
                300 => ['critstrkrtng' => 12],
                375 => ['critstrkrtng' => 20],
                450 => ['critstrkrtng' => 40],
            )
        );
    }
});

?>
