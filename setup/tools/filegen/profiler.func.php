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

        /**********/
        /* Quests */
        /**********/
        $scripts[] = function()
        {
            $success   = true;
            $condition = [
                CFG_SQL_LIMIT_NONE,
                'AND',
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW], 0],
                [['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY | QUEST_FLAG_REPEATABLE | QUEST_FLAG_AUTO_REWARDED, '&'], 0],
                [['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_DUNGEON_FINDER | QUEST_FLAG_SPECIAL_MONTHLY, '&'], 0]
            ];
            $questz = new QuestList($condition);

            $_ = [];
            $currencies = array_column($questz->rewards, TYPE_CURRENCY);
            foreach ($currencies as $curr)
                foreach ($curr as $cId => $qty)
                    $_[] = $cId;

            $relCurr = new CurrencyList(array(['id', $_]));

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(20);

                User::useLocale($l);
                Lang::load(Util::$localeStrings[$l]);

                $buff = "var _ = g_gatheredcurrencies;\n";
                foreach ($relCurr->getListviewData() as $id => $data)
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

                $buff .= "\n\nvar _ = g_quests;\n";
                foreach ($questz->getListviewData() as $id => $data)
                    $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

                $buff .= "\ng_quest_catorder = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];\n";

                if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-quests', $buff))
                    $success = false;
            }

            return $success;
        };

        /****************/
        /* Achievements */
        /****************/
        $scripts[] = function()
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
                Lang::load(Util::$localeStrings[$l]);

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

                if (!CLISetup::writeFile('datasets/'.User::$localeString.'/p-achievements', $buff))
                    $success = false;
            }

            return $success;
        };

        /**********/
        /* Titles */
        /**********/
        $scripts[] = function()
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            );
            $titlez = new TitleList($condition);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load(Util::$localeStrings[$l]);

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
        $scripts[] = function()
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                ['typeCat', -5]
            );
            $mountz = new SpellList($condition);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load(Util::$localeStrings[$l]);

                $buff = "var _ = g_spells;\n";
                foreach ($mountz->getListviewData(ITEMINFO_MODEL) as $id => $data)
                {
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
        $scripts[] = function()
        {
            $success   = true;
            $condition = array(
                CFG_SQL_LIMIT_NONE,
                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                ['typeCat', -6]
            );
            $companionz = new SpellList($condition);

            foreach (CLISetup::$localeIds as $l)
            {
                set_time_limit(5);

                User::useLocale($l);
                Lang::load(Util::$localeStrings[$l]);

                $buff = "var _ = g_spells;\n";
                foreach ($companionz->getListviewData(ITEMINFO_MODEL) as $id => $data)
                {
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
        $scripts[] = function()
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
                Lang::load(Util::$localeStrings[$l]);

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
        $scripts[] = function()
        {
            // special case: secondary skills are always requested, so put them in one single file (185, 129, 356); it also contains g_skill_order
            $skills  = [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, [185, 129, 356]];
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
                foreach ($recipez->iterate() as $__)
                {
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
                    Lang::load(Util::$localeStrings[$l]);

                    $buff = '';
                    foreach ($recipez->getListviewData() as $id => $data)
                        $buff .= '_['.$id.'] = '.Util::toJSON($data).";\n";

                    if (!$buff)
                    {
                        // this behaviour is intended, do not create an error
                        CLISetup::log('profiler - file datasets/'.User::$localeString.'/p-recipes-'.$file.' has no content => skipping', CLISetup::LOG_WARN);
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
