<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'profiler'      => [[   ], CLISetup::ARGV_PARAM,    'Generates data dumps and completion exclusion filters for the profiler tool.'],
        'quests'        => [['1'], CLISetup::ARGV_OPTIONAL, '...available quests by category'],
        'titles'        => [['2'], CLISetup::ARGV_OPTIONAL, '...available titles by gender'],
        'mounts'        => [['3'], CLISetup::ARGV_OPTIONAL, '...available mounts'],
        'companions'    => [['4'], CLISetup::ARGV_OPTIONAL, '...available companions'],
        'factions'      => [['5'], CLISetup::ARGV_OPTIONAL, '...available factions'],
        'recipes'       => [['6'], CLISetup::ARGV_OPTIONAL, '...available recipes by skill'],
        'achievements'  => [['7'], CLISetup::ARGV_OPTIONAL, '...available achievements'],
        'quickexcludes' => [['9'], CLISetup::ARGV_OPTIONAL, '...unobtainable items, mutually exclusive recipes, factions, etc.'],
    );

    protected $localized       = true;
    protected $requiredDirs    = ['datasets/'];
    protected $worldDependency = ['player_factionchange_spells', 'conditions'];
    protected $setupAfter      = [['quests', 'quests_startend', 'items', 'currencies', 'titles', 'spell', 'factions', 'achievement'], []];

    private $spellFactions = [];
    private $exclusions    = [];
    private $opts          = [];

    public function generate() : bool
    {
        $anyOpt = array_keys($this->info);
        $this->opts = CLISetup::getOpt(...$anyOpt);

        $this->opts = array_filter($this->opts);
        if (!$this->opts)                                   // none were set -> use default (all)
            $this->opts = array_fill_keys(array_slice($anyOpt, 1), true);


        $this->spellFactions = DB::World()->selectCol('SELECT `alliance_id` AS ARRAY_KEY, 1 FROM player_factionchange_spells UNION SELECT `horde_id` AS ARRAY_KEY, 2 FROM player_factionchange_spells');


        foreach ($this->opts as $fn => $_)
            $this->$fn();

        return $this->success;
    }

    private function quests() : void
    {
        $questorder = [];
        $questtotal = [];
        $condition  = [
            'AND',
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW | CUSTOM_UNAVAILABLE | CUSTOM_DISABLED, '&'], 0],
            [['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY | QUEST_FLAG_REPEATABLE | QUEST_FLAG_AUTO_REWARDED, '&'], 0],
            [['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_DUNGEON_FINDER | QUEST_FLAG_SPECIAL_MONTHLY, '&'], 0]
        ];

        foreach (Game::QUEST_CLASSES as $cat2 => $cat)
        {
            if ($cat2 < 0)
                continue;

            $cond = array_merge($condition, [['zoneOrSort', $cat]]);
            $questz = new QuestList($cond);
            if ($questz->error)
                continue;

            $questorder[] = $cat2;
            $questtotal[$cat2] = [];

            // get quests for exclusion
            foreach ($questz->iterate() as $id => $__)
            {
                $this->sumTotal($questtotal[$cat2], $questz->getField('reqRaceMask') ?: -1, $questz->getField('reqClassMask') ?: -1);
                if ($skillEx = $this->getExcludeForSkill($questz->getField('reqSkillId')))
                    $this->addExclusion(Type::QUEST, $id, $skillEx);
            }

            $_ = [];
            $currencies = array_column($questz->rewards, Type::CURRENCY);
            foreach ($currencies as $curr)
                foreach ($curr as $cId => $qty)
                    $_[] = $cId;

            $relCurr = new CurrencyList(array(['id', $_]));

            foreach (CLISetup::$locales as $loc)
            {
                set_time_limit(20);

                Lang::load($loc);

                if (!$relCurr->error)
                {
                    $buff = "var _ = g_gatheredcurrencies;\n";
                    foreach ($relCurr->getListviewData() as $id => $data)
                        $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                }

                $buff .= "var _ = g_quests;\n";
                foreach ($questz->getListviewData() as $id => $data)
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

                if (!CLISetup::writeFile('datasets/'.$loc->json().'/p-quests-'.$cat2, $buff))
                    $this->success = false;
            }
        }

        $buff  = "g_quest_catorder = ".Util::toJSON($questorder).";\n";
        $buff .= "g_quest_catorder_total = {};\n";
        foreach ($questtotal as $cat => $totals)
            $buff .= "g_quest_catorder_total[".$cat."] = ".Util::toJSON($totals).";\n";

        if (!CLISetup::writeFile('datasets/p-quests', $buff))
            $this->success = false;
    }

    private function titles(): void
    {
        $titlez = new TitleList(array([['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0]));

        // get titles for exclusion
        foreach ($titlez->iterate() as $id => $__)
            if (empty($titlez->sources[$id][SRC_QUEST]) && empty($titlez->sources[$id][SRC_ACHIEVEMENT]))
                $this->addExclusion(Type::TITLE, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

        foreach (CLISetup::$locales as $loc)
        {
            set_time_limit(5);

            Lang::load($loc);

            foreach ([GENDER_MALE, GENDER_FEMALE] as $g)
            {
                $buff = "var _ = g_titles;\n";
                foreach ($titlez->getListviewData() as $id => $data)
                {
                    $data['name'] = Util::localizedString($titlez->getEntry($id), $g ? 'female' : 'male');
                    unset($data['namefemale']);
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                }

                if (!CLISetup::writeFile('datasets/'.$loc->json().'/p-titles-'.$g, $buff))
                    $this->success = false;
            }
        }
    }

    private function mounts() : void
    {
        $condition = array(
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            ['typeCat', -5],
            ['castTime', 0, '!']
        );
        $mountz = new SpellList($condition);

        $conditionSet = DB::World()->selectCol('SELECT `SourceEntry` AS ARRAY_KEY, `ConditionValue1` FROM conditions WHERE `SourceTypeOrReferenceId` = ?d AND `ConditionTypeOrReference` = ?d AND `SourceEntry` IN (?a)', Conditions::SRC_SPELL, Conditions::SKILL, $mountz->getFoundIDs());

        // get mounts for exclusion
        foreach ($conditionSet as $mount => $skill)
            if ($skillEx = $this->getExcludeForSkill($skill))
                $this->addExclusion(Type::SPELL, $mount, $skillEx);

        foreach ($mountz->iterate() as $id => $_)
            if (!$mountz->getSources())
                $this->addExclusion(Type::SPELL, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

        foreach (CLISetup::$locales as $loc)
        {
            set_time_limit(5);

            Lang::load($loc);

            $buff = "var _ = g_spells;\n";
            foreach ($mountz->getListviewData(ITEMINFO_MODEL) as $id => $data)
            {
                // two cases where the spell is unrestricted but the castitem has class restriction (too lazy to formulate ruleset)
                if ($id == 66906)                       // Argent Charger
                    $data['reqclass'] = ChrClass::PALADIN->toMask();
                else if ($id == 54729)                  // Winged Steed of the Ebon Blade
                    $data['reqclass'] = ChrClass::DEATHKNIGHT->toMask();

                rsort($data['skill']);                  // riding (777) expected at pos 0

                $data['side']    = $this->spellFactions[$id] ?? SIDE_BOTH;
                $data['quality'] = $data['name'][0];
                $data['name']    = mb_substr($data['name'], 1);
                $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
            }

            if (!CLISetup::writeFile('datasets/'.$loc->json().'/p-mounts', $buff))
                $this->success = false;
        }
    }

    private function companions() : void
    {
        $condition = array(
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            ['typeCat', -6]
        );
        $companionz = new SpellList($condition);
        $legit      = DB::Aowow()->selectCol('SELECT `spellId2` FROM ?_items WHERE `class` = ?d AND `subClass` = ?d AND `spellId1` IN (?a) AND `spellId2` IN (?a)', ITEM_CLASS_MISC, 2, LEARN_SPELLS, $companionz->getFoundIDs());

        foreach ($companionz->iterate() as $id => $_)
            if (!$companionz->getSources())
                $this->addExclusion(Type::SPELL, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

        foreach (CLISetup::$locales as $loc)
        {
            set_time_limit(5);

            Lang::load($loc);

            $buff = "var _ = g_spells;\n";
            foreach ($companionz->getListviewData(ITEMINFO_MODEL) as $id => $data)
            {
                if (!in_array($id, $legit))
                    continue;

                $data['side']    = $this->spellFactions[$id] ?? SIDE_BOTH;
                $data['quality'] = $data['name'][0];
                $data['name']    = mb_substr($data['name'], 1);
                $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
            }

            if (!CLISetup::writeFile('datasets/'.$loc->json().'/p-companions', $buff))
                $this->success = false;
        }
    }

    private function factions() : void
    {
        $factionz = new FactionList(array([['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0]));

        foreach (CLISetup::$locales as $loc)
        {
            set_time_limit(5);

            Lang::load($loc);

            $buff = "var _ = g_factions;\n";
            foreach ($factionz->getListviewData() as $id => $data)
                $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

            $buff .= "\ng_faction_order = [0, 469, 891, 1037, 1118, 67, 1052, 892, 936, 1117, 169, 980, 1097];\n";

            if (!CLISetup::writeFile('datasets/'.$loc->json().'/p-factions', $buff))
                $this->success = false;
        }
    }

    private function recipes() : void
    {
        // special case: secondary skills are always requested, so put them in one single file (185, 129, 356); it also contains g_skill_order
        $skills  = array(
            SKILL_ALCHEMY,      SKILL_BLACKSMITHING,    SKILL_ENCHANTING,       SKILL_ENGINEERING,  SKILL_HERBALISM,
            SKILL_INSCRIPTION,  SKILL_JEWELCRAFTING,    SKILL_LEATHERWORKING,   SKILL_MINING,       SKILL_SKINNING,
            SKILL_TAILORING,    [SKILL_COOKING,         SKILL_FIRST_AID,        SKILL_FISHING]
        );

        $baseCnd = array(
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            //                                                                                          Inscryption                                                            Engineering
            ['effect1Id', [SPELL_EFFECT_APPLY_AURA, SPELL_EFFECT_TRADE_SKILL, SPELL_EFFECT_PROSPECTING, SPELL_EFFECT_OPEN_LOCK, SPELL_EFFECT_MILLING, SPELL_EFFECT_DISENCHANT, SPELL_EFFECT_SUMMON, SPELL_EFFECT_SKINNING], '!'],
            // not the skill itself
            ['effect2Id', [SPELL_EFFECT_SKILL, SPELL_EFFECT_PROFICIENCY], '!'],
            ['OR', ['typeCat', 9], ['typeCat', 11]]
        );

        foreach ($skills as $s)
        {
            $file    = is_array($s) ? 'sec' : (string)$s;
            $cnd     = array_merge($baseCnd, [['skillLine1', $s]]);
            $recipez = new SpellList($cnd);
            $created = '';
            foreach ($recipez->iterate() as $id => $_)
            {
                if (!$recipez->getSources())
                    $this->addExclusion(Type::SPELL, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

                foreach ($recipez->canCreateItem() as $idx)
                {
                    $id = $recipez->getField('effect'.$idx.'CreateItemId');
                    $created .= "g_items.add(".$id.", {'icon':'".$recipez->relItems->getEntry($id)['iconString']."'});\n";
                }
            }

            foreach (CLISetup::$locales as $loc)
            {
                set_time_limit(10);

                Lang::load($loc);

                $buff = '';
                foreach ($recipez->getListviewData() as $id => $data)
                {
                    $data['side'] = $this->spellFactions[$id] ?? SIDE_BOTH;
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                }

                if (!$buff)
                {
                    // this behaviour is intended, do not create an error
                    CLI::write('[profiler] - file datasets/'.$loc->json().'/p-recipes-'.$file.' has no content => skipping', CLI::LOG_INFO);
                    continue;
                }

                $buff = $created."\nvar _ = g_spells;\n".$buff;

                if (is_array($s))
                    $buff .= "\ng_skill_order = [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356];\n";

                if (!CLISetup::writeFile('datasets/'.$loc->json().'/p-recipes-'.$file, $buff))
                    $this->success = false;
            }
        }
    }

    private function achievements() : void
    {
        $condition = array(
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            [['flags', 1, '&'], 0],                     // no statistics
        );
        $achievez = new AchievementList($condition);

        foreach (CLISetup::$locales as $loc)
        {
            set_time_limit(5);

            Lang::load($loc);

            $sumPoints = 0;
            $buff      = "var _ = g_achievements;\n";
            foreach ($achievez->getListviewData(ACHIEVEMENTINFO_PROFILE) as $id => $data)
            {
                $sumPoints += $data['points'];
                $buff      .= '_['.$id.'] = '.Util::toJSON($data).";\n";
            }

            // categories to sort by
            $buff .= "\ng_achievement_catorder = [92, 14863, 97, 169, 170, 171, 172, 14802, 14804, 14803, 14801, 95, 161, 156, 165, 14806, 14921, 96, 201, 160, 14923, 14808, 14805, 14778, 14865, 14777, 14779, 155, 14862, 14861, 14864, 14866, 158, 162, 14780, 168, 14881, 187, 14901, 163, 14922, 159, 14941, 14961, 14962, 14981, 15003, 15002, 15001, 15041, 15042, 81]";
            // sum points
            $buff .= "\ng_achievement_points = [".$sumPoints."];\n";

            if (!CLISetup::writeFile('datasets/'.$loc->json().'/achievements', $buff))
                $this->success = false;
        }
    }

    private function quickexcludes() : void
    {
        set_time_limit(2);

        CLI::write('[profiler] applying '.count($this->exclusions).' baseline exclusions');
        DB::Aowow()->query('DELETE FROM ?_profiler_excludes WHERE `comment` = ""');

        foreach ($this->exclusions as $ex)
            DB::Aowow()->query('REPLACE INTO ?_profiler_excludes (?#) VALUES (?a)', array_keys($ex), array_values($ex));

        // excludes; type => [excludeGroupBit => [typeIds]]
        $excludes = [];

        $exData = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, `typeId` AS ARRAY_KEY2, `groups` FROM ?_profiler_excludes');
        for ($i = 0; (1 << $i) < PR_EXCLUDE_GROUP_ANY; $i++)
            foreach ($exData as $type => $data)
                if ($ids = array_keys(array_filter($data, fn($x) => $x & (1 << $i))))
                    $excludes[$type][$i + 1] = $ids;

        $buff = "g_excludes = ".Util::toJSON($excludes ?: (new \StdClass)).";\n";

        if (!CLISetup::writeFile('datasets/quick-excludes', $buff))
            $this->success = false;
    }

    private function getExcludeForSkill(int $skillId) : int
    {
        return match ($skillId)
        {
            SKILL_FISHING     => PR_EXCLUDE_GROUP_REQ_FISHING,
            SKILL_ENGINEERING => PR_EXCLUDE_GROUP_REQ_ENGINEERING,
            SKILL_TAILORING   => PR_EXCLUDE_GROUP_REQ_TAILORING,
            default           => 0
        };
    }

    private function addExclusion(int $type, int $typeId, int $groups, string $comment = '') : void
    {
        $k = $type.'-'.$typeId;

        if (!isset($this->exclusions[$k]))
            $this->exclusions[$k] = ['type' => $type, 'typeId' => $typeId, 'groups' => $groups, 'comment' => $comment];
        else
        {
            $this->exclusions[$k]['groups'] |= $groups;
            if ($comment)
                $this->exclusions[$k]['comment'] .= '; '.$comment;
        }
    }

    private function sumTotal(array &$sumArr, int $raceMask = -1, int $classMask= -1) : void
    {
        foreach (ChrRace::cases() as $ra)
        {
            if (!$ra->matches($raceMask))
                continue;

            foreach (ChrClass::cases() as $cl)
            {
                if (!$cl->matches($classMask))
                    continue;

                if (!isset($sumArr[$ra->value][$cl->value]))
                    $sumArr[$ra->value][$cl->value] = 1;
                else
                    $sumArr[$ra->value][$cl->value]++;
            }
        }
    }
});

?>
