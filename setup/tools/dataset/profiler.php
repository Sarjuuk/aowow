<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // gatheres quasi-static data used in profiler: all available quests, achievements, titles, mounts, companions, factions, recipes
    // this script requires a fully set up database and is expected to be run last

    $locales = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    $sides   = [1, 2];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets/'.$dir))
            mkdir('datasets/'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br><br>\n\n";

    /**********/
    /* Quests */
    /**********/
    {
        $cnd = [
            CFG_SQL_LIMIT_NONE,
            'AND',
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW], 0],
            [['flags', QUEST_FLAG_DAILY | QUEST_FLAG_WEEKLY | QUEST_FLAG_REPEATABLE | QUEST_FLAG_AUTO_REWARDED, '&'], 0],
            [['specialFlags', QUEST_FLAG_SPECIAL_REPEATABLE | QUEST_FLAG_SPECIAL_DUNGEON_FINDER | QUEST_FLAG_SPECIAL_MONTHLY, '&'], 0]
        ];
        $questz = new QuestList($cnd);

        $_ = [];
        $currencies = array_column($questz->rewards, TYPE_CURRENCY);
        foreach ($currencies as $curr)
            foreach ($curr as $cId => $qty)
                $_[] = $cId;

        $relCurr = new CurrencyList(array(['id', $_]));

        foreach ($locales as $l)
        {
            set_time_limit(20);

            User::useLocale($l);
            Lang::load(Util::$localeStrings[$l]);
            $handle = fOpen('datasets/'.User::$localeString.'/p-quests', "w");
            if (!$handle)
                die('could not create quests file '.$l);

            $buff = "var _ = g_gatheredcurrencies;\n";
            foreach($relCurr->getListviewData() as $id => $data)
                $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";

            $buff .= "\n\nvar _ = g_quests;\n";
            foreach($questz->getListviewData() as $id => $data)
                $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";

            $buff .= "\ng_quest_catorder = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];\n";

            fWrite($handle, $buff);
            fClose($handle);

            echo "done quests loc: ".$l." in ".Util::execTime()."<br>\n";
        }

        echo "<br>\n";
    }

    /****************/
    /* Achievements */
    /****************/
    {
        set_time_limit(10);

        $cnd = array(
            CFG_SQL_LIMIT_NONE,
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            [['flags', 1, '&'], 0],                             // no statistics
        );
        $achievez = new AchievementList($cnd);
        foreach ($locales as $l)
        {
            User::useLocale($l);
            Lang::load(Util::$localeStrings[$l]);
            $handle = fOpen('datasets/'.User::$localeString.'/p-achievements', "w");
            if (!$handle)
                die('could not create achievements file '.$l);

            $sumPoints = 0;
            $buff      = "var _ = g_achievements;\n";
            foreach ($achievez->getListviewData(ACHIEVEMENTINFO_PROFILE) as $id => $data)
            {
                $sumPoints += $data['points'];
                $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";
            }

            // categories to sort by
            $buff .= "\ng_achievement_catorder = [92, 14863, 97, 169, 170, 171, 172, 14802, 14804, 14803, 14801, 95, 161, 156, 165, 14806, 14921, 96, 201, 160, 14923, 14808, 14805, 14778, 14865, 14777, 14779, 155, 14862, 14861, 14864, 14866, 158, 162, 14780, 168, 14881, 187, 14901, 163, 14922, 159, 14941, 14961, 14962, 14981, 15003, 15002, 15001, 15041, 15042, 81]";
            // sum points
            $buff .= "\ng_achievement_points = [".$sumPoints."];\n";

            fWrite($handle, $buff);
            fClose($handle);

            echo "done achievements loc: ".$l." in ".Util::execTime()."<br>\n";
        }

        echo "<br>\n";
    }

    /**********/
    /* Titles */
    /**********/
    {
        set_time_limit(10);

        $cnd = array(
            CFG_SQL_LIMIT_NONE,
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
        );
        $titlez = new TitleList($cnd);
        foreach ($locales as $l)
        {
            foreach ([0, 1] as $g)                          // gender
            {
                User::useLocale($l);
                Lang::load(Util::$localeStrings[$l]);
                $handle = fOpen('datasets/'.User::$localeString.'/p-titles-'.$g, "w");
                if (!$handle)
                    die('could not create titles file '.$l.' '.$g);

                $buff = "var _ = g_titles;\n";
                foreach ($titlez->getListviewData() as $id => $data)
                {
                    $data['name'] = Util::localizedString($titlez->getEntry($id), $g ? 'female' : 'male');
                    unset($data['namefemale']);
                    $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";
                }
                fWrite($handle, $buff);
                fClose($handle);

                echo "done titles loc: ".$l.", gender: ".$g." in ".Util::execTime()."<br>\n";
            }
        }

        echo "<br>\n";
    }

    /**********/
    /* Mounts */
    /**********/
    {
        set_time_limit(10);

        $cnd = array(
            CFG_SQL_LIMIT_NONE,
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            ['typeCat', -5]
        );
        $mountz = new SpellList($cnd);
        foreach ($locales as $l)
        {
            User::useLocale($l);
            Lang::load(Util::$localeStrings[$l]);
            $handle = fOpen('datasets/'.User::$localeString.'/p-mounts', "w");
            if (!$handle)
                die('could not create mounts file '.$l);

            $buff = "var _ = g_spells;\n";
            foreach ($mountz->getListviewData(ITEMINFO_MODEL) as $id => $data)
            {
                $data['quality'] = $data['name'][0];
                $data['name']    = substr($data['name'], 1);
                $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";
            }

            fWrite($handle, $buff);
            fClose($handle);

            echo "done mounts loc: ".$l." in ".Util::execTime()."<br>\n";
        }

        echo "<br>\n";
    }

    /**************/
    /* Companions */
    /**************/
    {
        set_time_limit(10);

        $cnd = array(
            CFG_SQL_LIMIT_NONE,
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            ['typeCat', -6]
        );
        $companionz = new SpellList($cnd);
        foreach ($locales as $l)
        {
            User::useLocale($l);
            Lang::load(Util::$localeStrings[$l]);
            $handle = fOpen('datasets/'.User::$localeString.'/p-companions', "w");
            if (!$handle)
                die('could not create companions file '.$l);

            $buff = "var _ = g_spells;\n";
            foreach ($companionz->getListviewData(ITEMINFO_MODEL) as $id => $data)
            {
                $data['quality'] = $data['name'][0];
                $data['name']    = substr($data['name'], 1);
                $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";
            }

            fWrite($handle, $buff);
            fClose($handle);

            echo "done companions loc: ".$l." in ".Util::execTime()."<br>\n";
        }

        echo "<br>\n";
    }

    /************/
    /* Factions */
    /************/
    {
        set_time_limit(10);

        // todo (med): exclude non-gaining reputation-header
        $cnd = array(
            CFG_SQL_LIMIT_NONE,
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0]
        );
        $factionz = new FactionList($cnd);
        foreach ($locales as $l)
        {
            User::useLocale($l);
            Lang::load(Util::$localeStrings[$l]);
            $handle = fOpen('datasets/'.User::$localeString.'/p-factions', "w");
            if (!$handle)
                die('could not create factions file '.$l);

            $buff = "var _ = g_factions;\n";
            foreach ($factionz->getListviewData() as $id => $data)
                $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";

            $buff .= "\ng_faction_order = [0, 469, 891, 1037, 1118, 67, 1052, 892, 936, 1117, 169, 980, 1097];\n";

            fWrite($handle, $buff);
            fClose($handle);

            echo "done factions loc: ".$l." in ".Util::execTime()."<br>\n";
        }

        echo "<br>\n";
    }

    /***********/
    /* Recipes */
    /***********/
    {
        // special case: secondary skills are always requested, so put them in one single file (185, 129, 356); also it contains g_skill_order
        $skills = [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, [185, 129, 356]];
        $baseCnd = array(
            CFG_SQL_LIMIT_NONE,
            [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
            ['effect1Id', [6, 45, 57, 127, 33, 158, 99, 28, 95], '!'],  // aura, tradeSkill, Tracking, Prospecting, Decipher, Milling, Disenchant, Summon (Engineering), Skinning
            ['effect2Id', [118, 60], '!'],                              // not the skill itself
            ['OR', ['typeCat', 9], ['typeCat', 11]]
        );
        foreach ($skills as $s)
        {
            set_time_limit(20);

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

            foreach ($locales as $l)
            {
                User::useLocale($l);
                Lang::load(Util::$localeStrings[$l]);
                $buff = '';
                foreach ($recipez->getListviewData() as $id => $data)
                    $buff .= '_['.$id.'] = '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK).";\n";

                if (!$buff)
                {
                    echo " - file: ".$file." has no content => skipping<br>\n";
                    continue;
                }

                $handle = fOpen('datasets/'.User::$localeString.'/p-recipes-'.$file, "w");
                if (!$handle)
                    die('could not create '.$file.' file '.$l);

                $buff = $created."\nvar _ = g_spells;\n".$buff;

                if (is_array($s))
                    $buff .= "\ng_skill_order = [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356];\n";

                fWrite($handle, $buff);
                fClose($handle);

                echo "done ".$file." loc: ".$l." in ".Util::execTime()."<br>\n";
            }
        }

        echo "<br>\n";
    }

    echo "<br>\nall done";

    Lang::load(Util::$localeStrings[LOCALE_EN]);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);
?>
