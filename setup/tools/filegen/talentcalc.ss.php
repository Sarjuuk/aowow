<?php

namespace Aowow;

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
// j - array of modifier-arrays per rank for the Profiler
// tabs
// n - name of the tab
// t - array of talent-objects
// f - array:int [pets only] creatureFamilies in that category

CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'talentcalc' => [[], CLISetup::ARGV_PARAM, 'Compiles talent tree data to file for the talent calculator tool.']
    );

    protected $dbcSourceFiles = ['talenttab', 'talent', 'spell', 'creaturefamily', 'spellicon'];
    protected $setupAfter     = [['spell'], []];
    protected $requiredDirs   = ['datasets/'];
    protected $localized      = true;

    private $petFamIcons = [];
    private $tSpells     = null;
    private $spellMods   = [];

    public function generate() : bool
    {
        // target direcotries are missing
        if (!$this->success)
            return false;

        // my neighbour is noisy as fuck and my head hurts, so ..
        $this->petFamIcons = ['Ability_Druid_KingoftheJungle', 'Ability_Druid_DemoralizingRoar', 'Ability_EyeOfTheOwl']; // .. i've no idea where to fetch these from
        $this->spellMods   = (new SpellList(array(['typeCat', -2])))->getProfilerMods();

        $petIcons  = Util::toJSON(DB::Aowow()->SelectCol('SELECT `id` AS ARRAY_KEY, LOWER(SUBSTRING_INDEX(`iconString`, "\\\\", -1)) AS "iconString" FROM dbc_creaturefamily WHERE `petTalentType` IN (0, 1, 2)'));

        $tSpellIds = DB::Aowow()->selectCol('SELECT `rank1` FROM dbc_talent UNION SELECT `rank2` FROM dbc_talent UNION SELECT `rank3` FROM dbc_talent UNION SELECT `rank4` FROM dbc_talent UNION SELECT `rank5` FROM dbc_talent');
        $this->tSpells = new SpellList(array(['s.id', $tSpellIds]));

        foreach (CLISetup::$locales as $loc)
        {
            Lang::load($loc);

            // TalentCalc
            foreach (ChrClass::cases() as $class)
            {
                set_time_limit(20);

                $file   = 'datasets/'.$loc->json().'/talents-'.$class->value;
                $toFile = '$WowheadTalentCalculator.registerClass('.$class->value.', '.Util::toJSON($this->buildTree($class->toMask())).')';

                if (!CLISetup::writeFile($file, $toFile))
                    $this->success = false;
            }

            // PetCalc
            $toFile  = "var g_pet_icons = ".$petIcons.";\n\n";
            $toFile .= 'var g_pet_talents = '.Util::toJSON($this->buildTree(0)).';';
            $file    = 'datasets/'.$loc->json().'/pet-talents';

            if (!CLISetup::writeFile($file, $toFile))
                $this->success = false;
        }

        return $this->success;
    }

    private function buildTree(int $classMask) : array
    {
        $petCategories = [];

        // All "tabs" of a given class talent
        $tabs   = DB::Aowow()->select('SELECT * FROM dbc_talenttab WHERE `classMask` = ?d ORDER BY `tabNumber`, `creatureFamilyMask`', $classMask);
        $result = [];

        for ($tabIdx = 0; $tabIdx < count($tabs); $tabIdx++)
        {
            $talents = DB::Aowow()->select(
                'SELECT  t.id AS "tId", t.*, IF(t.rank5, 5, IF(t.rank4, 4, IF(t.rank3, 3, IF(t.rank2, 2, 1)))) AS "maxRank",
                         s.`name_loc0`, s.`name_loc2`, s.`name_loc3`, s.`name_loc4`, s.`name_loc6`, s.`name_loc8`,
                         LOWER(SUBSTRING_INDEX(si.`iconPath`, "\\\\", -1)) AS "iconString"
                FROM     dbc_talent t, dbc_spell s, dbc_spellicon si
                WHERE    si.`id` = s.`iconId` AND t.`tabId`= ?d AND s.`id` = t.`rank1`
                ORDER BY t.`row`, t.`column`, t.`id` ASC',
                $tabs[$tabIdx]['id']
            );

            $result[$tabIdx] = array(
                'n' => Util::localizedString($tabs[$tabIdx], 'name'),
                't' => []
            );

            if (!$classMask)
            {
                $petFamId                = log($tabs[$tabIdx]['creatureFamilyMask'], 2);
                $result[$tabIdx]['icon'] = $this->petFamIcons[$petFamId];
                $petCategories           = DB::Aowow()->SelectCol('SELECT `id` AS ARRAY_KEY, `categoryEnumID` FROM dbc_creaturefamily WHERE `petTalentType` = ?d', $petFamId);
                $result[$tabIdx]['f']    = array_keys($petCategories);
            }

            // talent dependencies go here
            $depLinks = [];
            $tNums    = [];

            for ($talentIdx = 0; $talentIdx < count($talents); $talentIdx++)
            {
                $tNums[$talents[$talentIdx]['tId']] = $talentIdx;

                $talent = array(
                    'i' => $talents[$talentIdx]['tId'],                             // talent id
                    'n' => Util::localizedString($talents[$talentIdx], 'name'),     // talent name
                    'm' => $talents[$talentIdx]['maxRank'],                         // maxRank
                    'd' => [],                                                      // [descriptions]
                    's' => [],                                                      // [spellIds]
                    'x' => $talents[$talentIdx]['column'],                          // col #
                    'y' => $talents[$talentIdx]['row'],                             // row #
                    'j' => []                                                       // [spellMods] that are applied when used in profiler
                //  'r' => []                                                       // [reqTalentId, reqRank] (can be omitted)
                //  't' => []                                                       // talentspell tooltip (can be omitted)
                //  'f' => []                                                       // [petFamilyIds] (can be omitted)
                );

                if ($classMask)
                    $talent['iconname'] = $talents[$talentIdx]['iconString'];

                for ($itr = 0; $itr <= ($talent['m'] - 1); $itr++)
                {
                    $spell = $talents[$talentIdx]['rank'.($itr + 1)];
                    if (!$this->tSpells->getEntry($spell))
                        continue;

                    $talent['d'][] = $this->tSpells->parseText()[0];
                    $talent['s'][] = $talents[$talentIdx]['rank'.($itr + 1)];

                    if ($classMask && isset($this->spellMods[$spell]))
                        if ($mod = $this->spellMods[$spell])
                            $talent['j'][] = $mod;

                    if ($talents[$talentIdx]['talentSpell'])
                        $talent['t'][] = $this->tSpells->getTalentHeadForCurrent();
                }

                foreach ($petCategories as $k => $v)
                {
                    // cant handle 64bit integer .. split
                    if ($v >= 32 && ((1 << ($v - 32)) & $talents[$talentIdx]['petCategory2']))
                        $talent['f'][] = $k;
                    else if ($v < 32 && ((1 << $v) & $talents[$talentIdx]['petCategory1']))
                        $talent['f'][] = $k;
                }

                if ($talents[$talentIdx]['reqTalent'])
                {
                    // we didn't encounter the required talent yet => create reference
                    if (!isset($tNums[$talents[$talentIdx]['reqTalent']]))
                        $depLinks[$talents[$talentIdx]['reqTalent']] = $talentIdx;

                    $talent['r'] = [$tNums[$talents[$talentIdx]['reqTalent']] ?? 0, $talents[$talentIdx]['reqRank'] + 1];
                }

                $result[$tabIdx]['t'][$talentIdx] = $talent;

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
    }
});

?>
