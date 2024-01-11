<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // gatheres quasi-static data used in profiler: all available quests, achievements, titles, mounts, companions, factions, recipes
    // this script requires a fully set up database and is expected to be run last

    function profiler()
    {
        $success = true;
        $scripts = [];
        $exclusions = [];

        $exAdd = function ($type, $typeId, $groups, $comment = '') use(&$exclusions)
        {
            $k = $type.'-'.$typeId;

            if (!isset($exclusions[$k]))
                $exclusions[$k] = ['type' => $type, 'typeId' => $typeId, 'groups' => $groups, 'comment' => $comment];
            else
            {
                $exclusions[$k]['groups'] |= $groups;
                if ($comment)
                    $exclusions[$k]['comment'] .= '; '.$comment;
            }
        };


        $sumTotal = function(array &$sumArr, int $raceMask = -1, int $classMask= -1)
        {
            for ($i = 0; $i < RACE_MASK_ALL; $i++)
            {
                if (!((1 << $i) & $raceMask) || !((1 << $i) & RACE_MASK_ALL))
                    continue;

                for ($j = 0; $j < CLASS_MASK_ALL; $j++)
                {
                    if (!((1 << $j) & $classMask) || !((1 << $j) & CLASS_MASK_ALL))
                        continue;

                    if (!isset($sumArr[$i+1][$j+1]))
                        $sumArr[$i+1][$j+1] = 1;
                    else
                        $sumArr[$i+1][$j+1]++;
                }
            }
        };


        $spellFactions = DB::World()->selectCol('SELECT `alliance_id` AS ARRAY_KEY, 1 FROM player_factionchange_spells UNION SELECT `horde_id` AS ARRAY_KEY, 2 FROM player_factionchange_spells');

        /**********/
        /* Quests */
        /**********/
        $scripts[] = function() use ($exAdd, $sumTotal)
        {
            $success    = true;
            $questorder = [];
            $questtotal = [];
            $condition  = [
                CFG_SQL_LIMIT_NONE,
                'AND',
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW | CUSTOM_UNAVAILABLE | CUSTOM_DISABLED, '&'], 0],
                [['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY | QUEST_FLAG_REPEATABLE | QUEST_FLAG_AUTO_REWARDED, '&'], 0],
                [['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_DUNGEON_FINDER | QUEST_FLAG_SPECIAL_MONTHLY, '&'], 0]
            ];

            foreach (Game::$questClasses as $cat2 => $cat)
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
                    $sumTotal($questtotal[$cat2], $questz->getField('reqRaceMask') ?: -1, $questz->getField('reqClassMask') ?: -1);

                    switch ($questz->getField('reqSkillId'))
                    {
                        case 356:
                            $exAdd(Type::QUEST, $id, PR_EXCLUDE_GROUP_REQ_FISHING);
                            break;
                        case 202:
                            $exAdd(Type::QUEST, $id, PR_EXCLUDE_GROUP_REQ_ENGINEERING);
                            break;
                        case 197:
                            $exAdd(Type::QUEST, $id, PR_EXCLUDE_GROUP_REQ_TAILORING);
                            break;
                    }
                }

                $_ = [];
                $currencies = array_column($questz->rewards, Type::CURRENCY);
                foreach ($currencies as $curr)
                    foreach ($curr as $cId => $qty)
                        $_[] = $cId;

                $relCurr = new CurrencyList(array(['id', $_]));

                foreach (CLISetup::$localeIds as $l)
                {
                    set_time_limit(20);

                    User::useLocale($l);
                    Lang::load($l);

                    if (!$relCurr->error)
                    {
                        $buff = "var _ = g_gatheredcurrencies;\n";
                        foreach ($relCurr->getListviewData() as $id => $data)
                            $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                    }

                    $buff .= "var _ = g_quests;\n";
                    foreach ($questz->getListviewData() as $id => $data)
                        $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

                    if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-quests-'.$cat2, $buff))
                        $success = false;
                }
            }

            $buff  = "g_quest_catorder = ".Util::toJSON($questorder).";\n";
            $buff .= "g_quest_catorder_total = {};\n";
            foreach ($questtotal as $cat => $totals)
                $buff .= "g_quest_catorder_total[".$cat."] = ".Util::toJSON($totals).";\n";

            if (!CLISetup::writeFile('datasets/p-quests', $buff))
                $success = false;

            return $success;
        };

        /**********/
        /* Titles */
        /**********/
        $scripts[] = function() use ($exAdd)
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            );
            $titlez = new TitleList($condition);

            // get titles for exclusion
            foreach ($titlez->iterate() as $id => $__)
                if (empty($titlez->sources[$id][4]) && empty($titlez->sources[$id][12]))
                    $exAdd(Type::TITLE, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load($l);

                foreach ([0, 1] as $g)                      // gender
                {
                    $buff = "var _ = g_titles;\n";
                    foreach ($titlez->getListviewData() as $id => $data)
                    {
                        $data['name'] = Util::localizedString($titlez->getEntry($id), $g ? 'female' : 'male');
                        unset($data['namefemale']);
                        $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                    }

                    if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-titles-'.$g, $buff))
                        $success = false;
                }
            }

            return $success;
        };

        /**********/
        /* Mounts */
        /**********/
        $scripts[] = function() use ($exAdd, $spellFactions)
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                ['typeCat', -5],
                ['castTime', 0, '!']
            );
            $mountz = new SpellList($condition);

            $conditionSet = DB::World()->selectCol('SELECT SourceEntry AS ARRAY_KEY, ConditionValue1 FROM conditions WHERE SourceTypeOrReferenceId = ?d AND ConditionTypeOrReference = ?d AND SourceEntry IN (?a)', CND_SRC_SPELL, CND_SKILL, $mountz->getFoundIDs());

            // get mounts for exclusion
            foreach ($conditionSet as $mount => $skill)
            {
                if ($skill == 202)
                    $exAdd(Type::SPELL, $mount, PR_EXCLUDE_GROUP_REQ_ENGINEERING);
                else if ($skill == 197)
                    $exAdd(Type::SPELL, $mount, PR_EXCLUDE_GROUP_REQ_TAILORING);
            }

            foreach ($mountz->iterate() as $id => $_)
                if (!$mountz->getSources($__, $___))
                    $exAdd(Type::SPELL, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load($l);

                $buff = "var _ = g_spells;\n";
                foreach ($mountz->getListviewData(ITEMINFO_MODEL) as $id => $data)
                {
                    // two cases where the spell is unrestricted but the castitem has class restriction (too lazy to formulate ruleset)
                    if ($id == 66906)                       // Argent Charger
                        $data['reqclass'] = CLASS_PALADIN;
                    else if ($id == 54729)                  // Winged Steed of the Ebon Blade
                        $data['reqclass'] = CLASS_DEATHKNIGHT;

                    rsort($data['skill']);                  // riding (777) expected at pos 0

                    $data['side']    = $spellFactions[$id] ?? SIDE_BOTH;
                    $data['quality'] = $data['name'][0];
                    $data['name']    = mb_substr($data['name'], 1);
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                }

                if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-mounts', $buff))
                    $success = false;
            }

            return $success;
        };

        /**************/
        /* Companions */
        /**************/
        $scripts[] = function() use ($exAdd, $spellFactions)
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                ['typeCat', -6]
            );
            $companionz = new SpellList($condition);
            $legit      = DB::Aowow()->selectCol('SELECT `spellId2` FROM ?_items WHERE `class` = ?d AND `subClass` = ?d AND `spellId1` IN (?a) AND `spellId2` IN (?a)', ITEM_CLASS_MISC, 2, LEARN_SPELLS, $companionz->getFoundIDs());

            foreach ($companionz->iterate() as $id => $_)
                if (!$companionz->getSources($__, $___))
                    $exAdd(Type::SPELL, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load($l);

                $buff = "var _ = g_spells;\n";
                foreach ($companionz->getListviewData(ITEMINFO_MODEL) as $id => $data)
                {
                    if (!in_array($id, $legit))
                        continue;

                    $data['side']    = $spellFactions[$id] ?? SIDE_BOTH;
                    $data['quality'] = $data['name'][0];
                    $data['name']    = mb_substr($data['name'], 1);
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                }

                if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-companions', $buff))
                    $success = false;
            }

            return $success;
        };

        /************/
        /* Factions */
        /************/
        $scripts[] = function() use ($exAdd)
        {
            $success   = true;
            $condition = array(                             // todo (med): exclude non-gaining reputation-header
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0]
            );
            $factionz = new FactionList($condition);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load($l);

                $buff = "var _ = g_factions;\n";
                foreach ($factionz->getListviewData() as $id => $data)
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

                $buff .= "\ng_faction_order = [0, 469, 891, 1037, 1118, 67, 1052, 892, 936, 1117, 169, 980, 1097];\n";

                if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-factions', $buff))
                    $success = false;
            }

            return $success;
        };

        /***********/
        /* Recipes */
        /***********/
        $scripts[] = function() use ($exAdd, $spellFactions)
        {
            // special case: secondary skills are always requested, so put them in one single file (185, 129, 356); it also contains g_skill_order
            $skills  = array_merge(SKILLS_TRADE_PRIMARY, [[185, 129, 356]]);
            $success = true;
            $baseCnd = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                ['effect1Id', [6, 45, 57, 127, 33, 158, 99, 28, 95], '!'],  // aura, tradeSkill, Tracking, Prospecting, Decipher, Milling, Disenchant, Summon (Engineering), Skinning
                ['effect2Id', [118, 60], '!'],                              // not the skill itself
                ['OR', ['typeCat', 9], ['typeCat', 11]]
            );

            foreach ($skills as $s)
            {
                $file    = is_array($s) ? 'sec' : (string)$s;
                $cnd     = array_merge($baseCnd, [['skillLine1', $s]]);
                $recipez = new SpellList($cnd);
                $created = '';
                foreach ($recipez->iterate() as $id => $__)
                {
                    if (!$recipez->getSources($__, $___))
                        $exAdd(Type::SPELL, $id, PR_EXCLUDE_GROUP_UNAVAILABLE);

                    foreach ($recipez->canCreateItem() as $idx)
                    {
                        $id = $recipez->getField('effect'.$idx.'CreateItemId');
                        $created .= "g_items.add(".$id.", {'icon':'".$recipez->relItems->getEntry($id)['iconString']."'});\n";
                    }
                }

                foreach (CLISetup::$localeIds as $l)
                {
                    set_time_limit(10);

                    User::useLocale($l);
                    Lang::load($l);

                    $buff = '';
                    foreach ($recipez->getListviewData() as $id => $data)
                    {
                        $data['side'] = $spellFactions[$id] ?? SIDE_BOTH;
                        $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";
                    }

                    if (!$buff)
                    {
                        // this behaviour is intended, do not create an error
                        CLI::write('profiler - file datasets/'.User::$localeString.'/p-recipes-'.$file.' has no content => skipping', CLI::LOG_INFO);
                        continue;
                    }

                    $buff = $created."\nvar _ = g_spells;\n".$buff;

                    if (is_array($s))
                        $buff .= "\ng_skill_order = [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356];\n";

                    if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-recipes-'.$file, $buff))
                        $success = false;
                }
            }

            return $success;
        };

        /****************/
        /* Achievements */
        /****************/
        $scripts[] = function() use ($exAdd)
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                [['flags', 1, '&'], 0],                     // no statistics
            );
            $achievez = new AchievementList($condition);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load($l);

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

                if (!CLISetup::writeFile('datasets/'.User::$localeString.'/achievements', $buff))
                    $success = false;
            }

            return $success;
        };

        /******************/
        /* Quick Excludes */
        /******************/
        $scripts[] = function() use (&$exclusions)
        {
            set_time_limit(2);

            CLI::write('applying '.count($exclusions).' baseline exclusions');
            DB::Aowow()->query('DELETE FROM ?_profiler_excludes WHERE comment = ""');

            foreach ($exclusions as $ex)
                DB::Aowow()->query('REPLACE INTO ?_profiler_excludes (?#) VALUES (?a)', array_keys($ex), array_values($ex));

            // excludes; type => [excludeGroupBit => [typeIds]]
            $excludes = [];

            $exData = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, `typeId` AS ARRAY_KEY2, `groups` FROM ?_profiler_excludes');
            for ($i = 0; (1 << $i) < PR_EXCLUDE_GROUP_ANY; $i++)
                foreach ($exData as $type => $data)
                    if ($ids = array_keys(array_filter($data, function ($x) use ($i) { return $x & (1 << $i); } )))
                        $excludes[$type][$i + 1] = $ids;

            $buff = "g_excludes = ".Util::toJSON($excludes ?: (new Stdclass)).";\n";

            return CLISetup::writeFile('datasets/quick-excludes', $buff);
        };

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!CLISetup::writeDir('datasets/'.$dir))
                $success = false;

        // run scripts
        foreach ($scripts as $func)
            if (!$func())
                $success = false;

        return $success;
    }

?>
