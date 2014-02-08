<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id       = intVal($pageParam);
$_path     = [0, 4];
$_altIds   = [];
$_altNPCs  = null;

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_NPC, $_id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_NPC, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $npc = new CreatureList(array(['ct.id', $_id]));
        if ($npc->error)
            die('$WowheadPower.registerNpc('.$_id.', '.User::$localeId.', {})');

        $s = $npc->getSpawns(true);

        $x  = '$WowheadPower.registerNpc('.$_id.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($npc->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($npc->renderTooltip())."',\n";
        // $x .= "\tmap: ".($s ? '{zone: '.$s[0].', coords: {0:'.json_encode($s[1], JSON_NUMERIC_CHECK).'}' : '{}')."\n";
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $npc = new CreatureList(array(['ct.id', $_id]));
    if ($npc->error)
        $smarty->notFound(Lang::$game['npc'], $_id);

    // reconstruct path
    $_path[] = $npc->getField('type');

    $_typeFlags = $npc->getField('typeFlags');
    $_name      = $npc->getField('name', true);

    if ($_ = $npc->getField('family'))
        $_path[] = $_;

    $position = null;

    // difficulty entrys of self
    if ($npc->getField('cuFlags') & NPC_CU_DIFFICULTY_DUMMY)
    {
        // find and create link to regular creature
        $regNPC = new CreatureList(array(['OR', ['difficultyEntry1', $_id], ['difficultyEntry2', $_id], ['difficultyEntry3', $_id]]));
        $position = [$regNPC->id, $regNPC->getField('name', true)];
    }
    else
    {
        for ($i = 1; $i < 4; $i++)
            if ($_ = $npc->getField('difficultyEntry'.$i))
                $_altIds[$_] = $i;

        if ($_altIds)
            $_altNPCs = new CreatureList(array(['id', array_keys($_altIds)]));
    }

    // map mode
    $mapType = 0;
    $maps = DB::Aowow()->selectCol('SELECT DISTINCT map from creature WHERE id = ?d', $_id);
    if (count($maps) == 1)                                   // should only exist in one instance
    {
        $map = new ZoneList(array(1, ['mapId', $maps[0]]));
        // $mapType = $map->getField('areaType');
    }

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // Level
    if ($npc->getField('rank') != NPC_RANK_BOSS)
    {
        $level  = $npc->getField('minLevel');
        $maxLvl = $npc->getField('maxLevel');
        if ($level < $maxLvl)
            $level .= ' - '.$maxLvl;
    }
    else                                                    // Boss Level
        $level = '??';

    $infobox[] = Lang::$game['level'].Lang::$colon.$level;

    // Classification
    if ($_ = $npc->getField('rank'))                        //  != NPC_RANK_NORMAL
    {
        $str = $_typeFlags & 0x4 ? '[span class=boss-icon]'.Lang::$npc['rank'][$_].'[/span]' : Lang::$npc['rank'][$_];
        $infobox[] = Lang::$npc['classification'].Lang::$colon.$str;
    }

    // Reaction
    $_ = function ($r)
    {
        if ($r == 1)  return 2;
        if ($r == -1) return 10;
        return;
    };
    $infobox[] = Lang::$npc['react'].Lang::$colon.'[color=q'.$_($npc->getField('A')).']A[/color] [color=q'.$_($npc->getField('H')).']H[/color]';

    // Faction
    Util::$pageTemplate->extendGlobalIds(TYPE_FACTION, $npc->getField('factionId'));
    $infobox[] = Util::ucFirst(Lang::$game['faction']).Lang::$colon.'[faction='.$npc->getField('factionId').']';

    // Wealth
    if ($_ = intVal(($npc->getField('minGold') + $npc->getField('maxGold')) / 2))
        $infobox[] = Lang::$npc['worth'].Lang::$colon.'[tooltip=tooltip_avgmoneydropped][money='.$_.'][/tooltip]';

    // AI
    if (User::isInGroup(U_GROUP_STAFF))
    {
        if ($_ = $npc->getField('aiName'))
            $infobox[] = 'AI'.Lang::$colon.$_;
        else if ($_ = $npc->getField('scriptName'))
            $infobox[] = 'Script'.Lang::$colon.$_;
    }

    $_nf = function ($num) { return number_format($num, 0, '', '.'); };

    // Health
    $health    = $npc->getField('healthMin');
    $maxHealth = $npc->getField('healthMax');
    $health    = $health < $maxHealth ? $_nf($health).' - '.$_nf($maxHealth) : $_nf($health);

    $modes = [];
    $tipp  = '[tooltip name=healthModes][table cellspacing=10][tr]%s[/tr][/table][/tooltip][span class=tip tooltip=healthModes]%s[/span]';
    if ($mapType == 1 || $mapType == 2)                     // Dungeon or Raid
    {
        foreach ($_altIds as $mode => $id)
        {
            foreach ($_altNPCs->iterate() as $dId => $__)
            {
                if ($dId != $id)
                    continue;

                $hp    = $_altNPCs->getField('healthMin');
                $hpMax = $_altNPCs->getField('healthMax');
                $hp    = $hp < $hpMax ? $_nf($hp).' - '.$_nf($hpMax) : $_nf($hp);

                $modes[] = '[tr][td]'.Lang::$npc['modes'][$mapType][$mode].'&nbsp;&nbsp;[/td][td]'.$hp.'[/td][/tr]';
                break;
            }
        }

        if ($modes)
            $health = Lang::$spell['powerTypes'][-2].' ('.Lang::$npc['modes'][$mapType][0].')'.Lang::$colon.$health;
    }

    if ($modes)
        $infobox[] = sprintf($tipp, implode('[/tr][tr]', $modes), $health);
    else
        $infobox[] = Lang::$spell['powerTypes'][-2].Lang::$colon.$health;

    // Mana
    $mana    = $npc->getField('manaMin');
    $maxMana = $npc->getField('manaMax');
    if ($maxMana)
    {
        $mana      = $mana < $maxMana ? $_nf($mana).' - '.$_nf($maxMana) : $_nf($mana);
        $infobox[] = Lang::$spell['powerTypes'][0].Lang::$colon.$mana;
    }


/*
        if damage
            <li><div>{#Damage#}: {$npc.mindmg} - {$npc.maxdmg}</div></li>

        if armor
            <li><div>{#Armor#}: {$npc.armor}</div></li>
*/


    /****************/
    /* Main Content */
    /****************/

    // reputations (by mode)
    $spilledParents = [];
    $reputation     = [];
    $_repFunc       = function ($entries, &$spillover)
    {
        $q = 'SELECT f.id, f.parentFactionId, cor.creature_id AS npc,
                  IF(f.id = RewOnKillRepFaction1, RewOnKillRepValue1, RewOnKillRepValue2) AS qty,
                  IF(f.id = RewOnKillRepFaction1, MaxStanding1, MaxStanding2)             AS maxRank,
                  IF(f.id = RewOnKillRepFaction1, isTeamAward1, isTeamAward2)             AS spillover
              FROM aowow_factions f JOIN creature_onkill_reputation cor ON f.Id = cor.RewOnKillRepFaction1 OR f.Id = cor.RewOnKillRepFaction2 WHERE cor.creature_id IN (?a)';

        $result  = [];
        $repData = DB::Aowow()->select($q, (array)$entries);

        foreach ($repData as $_)
        {
            $set = array(
                'id'   => $_['id'],
                'qty'  => $_['qty'],
                'name' => FactionList::getName($_['id']),   // << this sucks .. maybe format this whole table with markdown and add name via globals?
                'npc'  => $_['npc'],
                'cap'  => $_['maxRank'] && $_['maxRank'] < REP_EXALTED ? Lang::$game['rep'][$_['maxRank']] : null
            );

            if ($_['spillover'])
            {
                $spillover[$_['parentFactionId']] = [intVal($_['qty'] / 2), $_['maxRank']];
                $set['spillover'] = $_['parentFactionId'];
            }

            $result[] = $set;
        }

        return $result;
    };

    // base NPC
    if ($base = $_repFunc($_id, $spilledParents))
        $reputation[] = [Lang::$npc['modes'][1][0], $base];

    // difficulty dummys
    if ($_altIds)
    {
        $alt = [];
        $rep = $_repFunc(array_keys($_altIds), $spilledParents);

        // order by difficulty
        foreach ($rep as $r)
            $alt[$_altIds[$r['npc']]][] = $r;

        // apply by difficulty
        foreach ($alt as $mode => $dat)
            $reputation[] = [Lang::$npc['modes'][$mapType][$mode], $dat];
    }

    // get spillover factions and apply
    if ($spilledParents)
    {
        $spilled = new FactionList(array(['parentFactionId', array_keys($spilledParents)]));

        foreach($reputation as &$sets)
        {
            foreach ($sets[1] as &$row)
            {
                if (empty($row['spillover']))
                    continue;

                foreach ($spilled->iterate() as $spId => $__)
                {
                    // find parent
                    if ($spilled->getField('parentFactionId') != $row['spillover'])
                        continue;

                    // don't readd parent
                    if ($row['id'] == $spId)
                        continue;

                    $spMax = $spilledParents[$row['spillover']][1];

                    $sets[1][] = array(
                        'id'   => $spId,
                        'qty'  => $spilledParents[$row['spillover']][0],
                        'name' => $spilled->getField('name', true),
                        'cap'  => $spMax && $spMax < REP_EXALTED ? Lang::$game['rep'][$spMax] : null
                    );
                }
            }
        }
    }

    // Quotes
    $quotes = [];
    if ($texts = DB::Aowow()->select('SELECT ct.*, ct.groupid AS ARRAY_KEY, ct.id as ARRAY_KEY2, lct.text_loc2, lct.text_loc3, lct.text_loc6, lct.text_loc8 FROM creature_text ct LEFT JOIN locales_creature_text lct ON ct.entry = lct.entry AND ct.groupid = lct.groupid AND ct.id = lct.id WHERE ct.entry = ?d', $_id))
    {
        $nQuotes = 0;
        foreach ($texts as $text)
        {
            $group = [];
            foreach ($text as $t)
            {
                // fixup .. either set %s for emotes or dont >.<
                $text = Util::localizedString($t, 'text');
                if (in_array($t['type'], [2, 3, 16, 41]) && strpos($text, '%s') === false)
                    $text = '%s '.$text;

                $line = array(
                    'type' => 2,                               // [type: 0, 12] say: yellow-ish
                    'lang'  => !empty($t['language']) ? Lang::$game['languages'][$t['language']] : null,
                    'text'  => sprintf(Util::parseHtmlText(htmlentities($text)), $_name),
                );

                switch ($t['type'])
                {
                    case  1:                                    // yell:
                    case 14: $line['type'] = 1; break;          // - dark red
                    case  2:                                    // emote:
                    case 16:                                    // "
                    case  3:                                    // boss emote:
                    case 41: $line['type'] = 4; break;          // - orange
                    case  4:                                    // whisper:
                    case 15:                                    // "
                    case  5:                                    // boss whisper:
                    case 42: $line['type'] = 3; break;          // - pink-ish
                }

                $nQuotes++;
                $group[] = $line;
            }
            $quotes[] = $group;
        }
        $quotes = [$quotes, $nQuotes];
    }


    // get spawns and such


    // menuId 4: NPC      g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'name'         => $_name,
            'subname'      => $npc->getField('subname', true),
            'infobox'      => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
            'difficultyPH' => isset($difficultyPH) ? $difficultyPH : null,
            // 'mapper' => true,
            'position'     => $position,
            'quotes'       => $quotes,
            'reputation'   => $reputation,
            'title'        => $_name.' - '.Util::ucFirst(Lang::$game['npc']),
            'path'         => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'          => 0,
            'type'         => TYPE_NPC,
            'typeId'       => $_id,
            'reqJS'        => ['template/js/swfobject.js'],
            'redButtons'   => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true,
                BUTTON_VIEW3D  => ['type' => TYPE_NPC, 'typeId' => $_id, 'displayId' => $npc->getRandomModelId()]
            )
        ),
        'relTabs' => []
    );

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: SAI
        // hmm, how should this loot like

    // tab: abilities
        // for spell in template and smartScripts if set

    // tab: teaches
        // pet spells, class spells, trade spells

    // tab: sells
    if ($sells = DB::Aowow()->selectCol('SELECT item FROM npc_vendor nv  WHERE entry = ?d UNION SELECT item FROM game_event_npc_vendor genv JOIN creature c ON genv.guid = c.guid WHERE c.id = ?d', $_id, $_id))
    {
        $soldItems = new ItemList(array(['id', $sells]));
        if (!$soldItems->error)
        {
            $soldItems->addGlobalsToJscript(Util::$pageTemplate);

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $soldItems->getListviewData(ITEMINFO_VENDOR, [TYPE_NPC => $_id]),
                'params' => [
                    'tabs'      => '$tabsRelated',
                    'name'      => '$LANG.tab_sells',
                    'id'        => 'currency-for',
                    'extraCols' => "$[Listview.extraCols.condition, Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack'), Listview.extraCols.cost]"
                ]
            );
        }
    }

    // tabs: this creature contains..
    $skinTab = ['tab_skinning', 'skinned-from'];
    if ($_typeFlags & NPC_TYPEFLAG_HERBLOOT)
        $skinTab = ['tab_gatheredfromnpc', 'gathered-from-npc'];
    else if ($_typeFlags & NPC_TYPEFLAG_MININGLOOT)
        $skinTab = ['tab_minedfromnpc', 'mined-from-npc'];
    else if ($_typeFlags & NPC_TYPEFLAG_ENGINEERLOOT)
        $skinTab = ['tab_salvagedfrom', 'salvaged-from-npc'];

/*
		extraCols: [Listview.extraCols.count, Listview.extraCols.percent, Listview.extraCols.mode],
		_totalCount: 22531,
		computeDataFunc: Listview.funcBox.initLootTable,
		onAfterCreate: Listview.funcBox.addModeIndicator,

        modes:{"mode":1,"1":{"count":4408,"outof":16013},"4":{"count":4408,"outof":22531}}
*/

    $sourceFor = array(
         [LOOT_CREATURE,    $npc->getField('lootId'),           '$LANG.tab_drops',         'drops',         ['Listview.extraCols.percent'], []                          , []],
         [LOOT_PICKPOCKET,  $npc->getField('pickpocketLootId'), '$LANG.tab_pickpocketing', 'pickpocketing', ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []],
         [LOOT_SKINNING,    $npc->getField('skinLootId'),       '$LANG.'.$skinTab[0],      $skinTab[1],     ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []]
    );

    // temp: manually add loot for difficulty-versions
    $langref = array(
        "-2" => '$LANG.tab_heroic',
        "-1" => '$LANG.tab_normal',
           1 => '$$WH.sprintf(LANG.tab_normalX, 10)',
           2 => '$$WH.sprintf(LANG.tab_normalX, 25)',
           3 => '$$WH.sprintf(LANG.tab_heroicX, 10)',
           4 => '$$WH.sprintf(LANG.tab_heroicX, 25)'
    );

    if ($_altIds)
    {
        $sourceFor[0][2] = $langref[1];
        foreach ($_altNPCs->iterate() as $id => $__)
        {
            $mode = $_altIds[$id];
            array_splice($sourceFor, 1, 0, [[LOOT_CREATURE, $_altNPCs->getField('lootId'), $langref[$mode + 1], 'drops-'.$mode, ['Listview.extraCols.percent'], [], []]]);
        }
    }

    $reqQuest = [];
    foreach ($sourceFor as $sf)
    {
        if ($itemLoot = Util::handleLoot($sf[0], $sf[1], User::isInGroup(U_GROUP_STAFF), $sf[4]))
        {
            foreach ($itemLoot as $l => $lv)
            {
                if (!$lv['quest'])
                    continue;

                $sf[4][] = 'Listview.extraCols.condition';

                $reqQuest[$lv['id']] = 0;

                $itemLoot[$l]['condition'] = ['type' => TYPE_QUEST, 'typeId' => &$reqQuest[$lv['id']], 'status' => 1];
            }

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $itemLoot,
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'name'        => $sf[2],
                    'id'          => $sf[3],
                    'extraCols'   => $sf[4] ? "$[".implode(', ', array_unique($sf[4]))."]" : null,
                    'hiddenCols'  => $sf[5] ? "$".json_encode($sf[5]) : null,
                    'visibleCols' => $sf[6] ? '$'.json_encode($sf[6]) : null,
                    'sort'        => "$['-percent', 'name']",
                ]
            );
        }
    }

    if ($reqIds = array_keys($reqQuest))                    // apply quest-conditions as back-reference
    {
        $conditions = array(
            'OR',
            ['requiredSourceItemId1', $reqIds], ['requiredSourceItemId2', $reqIds],
            ['requiredSourceItemId3', $reqIds], ['requiredSourceItemId4', $reqIds],
            ['requiredItemId1', $reqIds], ['requiredItemId2', $reqIds], ['requiredItemId3', $reqIds],
            ['requiredItemId4', $reqIds], ['requiredItemId5', $reqIds], ['requiredItemId6', $reqIds]
        );

        $reqQuests = new QuestList($conditions);
        $reqQuests->addGlobalsToJscript($smarty);

        foreach ($reqQuests->iterate() as $qId => $__)
        {
            if (empty($reqQuests->requires[$qId][TYPE_ITEM]))
                continue;

            foreach ($reqIds as $rId)
                if (in_array($rId, $reqQuests->requires[$qId][TYPE_ITEM]))
                    $reqQuest[$rId] = $reqQuests->id;
        }
    }

    // tab: starts quest (questrelation)
    if ($starts = DB::Aowow()->selectCol('SELECT quest FROM creature_questrelation WHERE id = ?d', $_id))
    {
        $started = new QuestList(array(['id', $starts]));
        if (!$started->error)
        {
            $started->addGlobalsToJScript(Util::$pageTemplate);

            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $started->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts'
                ]
            );
        }
    }

    // tab: ends quest (involvedrelation)
    if ($ends = DB::Aowow()->selectCol('SELECT quest FROM creature_involvedrelation WHERE id = ?d', $_id))
    {
        $ended = new QuestList(array(['id', $ends]));
        if (!$ended->error)
        {
            $ended->addGlobalsToJScript(Util::$pageTemplate);

            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $ended->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_ends',
                    'id'   => 'ends'
                ]
            );
        }
    }

    // tab: objective of quest
    $conditions = array(
        'OR',
        ['AND', ['RequiredNpcOrGo1', $_id], ['RequiredNpcOrGoCount1', 0, '>']],
        ['AND', ['RequiredNpcOrGo2', $_id], ['RequiredNpcOrGoCount2', 0, '>']],
        ['AND', ['RequiredNpcOrGo3', $_id], ['RequiredNpcOrGoCount3', 0, '>']],
        ['AND', ['RequiredNpcOrGo4', $_id], ['RequiredNpcOrGoCount4', 0, '>']],
    );

    $objectiveOf = new QuestList($conditions);
    if (!$objectiveOf->error)
    {
        $objectiveOf->addGlobalsToJScript(Util::$pageTemplate);

        $pageData['relTabs'][] = array(
            'file'   => 'quest',
            'data'   => $objectiveOf->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_objectiveof',
                'id'   => 'objective-of'
            ]
        );
    }

    // tab: criteria of [ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE_TYPE have no data set to check for]
    $conditions = array(
        ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE, ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE]],
        ['ac.value1', $_id]
    );

    $crtOf = new AchievementList($conditions);
    if (!$crtOf->error)
    {
        $crtOf->addGlobalsToJScript(Util::$pageTemplate);

        $pageData['relTabs'][] = array(
            'file'   => 'achievement',
            'data'   => $crtOf->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            ]
        );
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_NPC, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$npc, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('npc.tpl');

?>
