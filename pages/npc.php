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

    $mapType = 0;
    if (count($_altIds) > 1)                                // temp until zones..
        $mapType = 2;
    else if (count($_altIds) == 1)
        $mapType = 1;

    // map mode
    // $maps = DB::Aowow()->selectCol('SELECT DISTINCT map from creature WHERE id = ?d', $_id);
    // if (count($maps) == 1)                                   // should only exist in one instance
    // {
        // $map = new ZoneList(array(1, ['mapId', $maps[0]]));
        // $mapType = $map->getField('areaType');       //NYI
    // }

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

    // is Vehicle
    if ($npc->getField('vehicleId'))
        $infobox[] = Lang::$npc['vehicle'];

    // AI
    if (User::isInGroup(U_GROUP_STAFF))
    {
        if ($_ = $npc->getField('scriptName'))
            $infobox[] = 'Script'.Lang::$colon.$_;
        else if ($_ = $npc->getField('aiName'))
            $infobox[] = 'AI'.Lang::$colon.$_;
    }

    // > Stats
    $_nf     = function ($num) { return number_format($num, 0, '', '.'); };
    $stats   = [];
    $modes   = [];                                          // get difficulty versions if set
    $hint    = '[tooltip name=%3$s][table cellspacing=10][tr]%1s[/tr][/table][/tooltip][span class=tip tooltip=%3$s]%2s[/span]';
    $modeRow = '[tr][td]%s&nbsp;&nbsp;[/td][td]%s[/td][/tr]';
    // Health
    $health = $npc->getBaseStats('health');
    $stats['health'] = Util::ucFirst(Lang::$spell['powerTypes'][-2]).Lang::$colon.($health[0] < $health[1] ? $_nf($health[0]).' - '.$_nf($health[1]) : $_nf($health[0]));

    // Mana (may be 0)
    $mana = $npc->getBaseStats('power');
    $stats['mana'] = $mana[0] ? Lang::$spell['powerTypes'][0].Lang::$colon.($mana[0] < $mana[1] ? $_nf($mana[0]).' - '.$_nf($mana[1]) : $_nf($mana[0])) : null;

    // Armor
    $armor = $npc->getBaseStats('armor');
    $stats['armor'] = Lang::$npc['armor'].Lang::$colon.($armor[0] < $armor[1] ? $_nf($armor[0]).' - '.$_nf($armor[1]) : $_nf($armor[0]));

    // Melee Damage
    $melee = $npc->getBaseStats('melee');
    if ($_ = $npc->getField('dmgSchool'))                   // magic damage
        $stats['melee'] = Lang::$npc['melee'].Lang::$colon.$_nf($melee[0]).' - '.$_nf($melee[1]).' ('.Lang::$game['sc'][$_].')';
    else                                                    // phys. damage
        $stats['melee'] = Lang::$npc['melee'].Lang::$colon.$_nf($melee[0]).' - '.$_nf($melee[1]);

    // Ranged Damage
    $ranged = $npc->getBaseStats('ranged');
    $stats['ranged'] = Lang::$npc['ranged'].Lang::$colon.$_nf($ranged[0]).' - '.$_nf($ranged[1]);

    if ($mapType == 1 || $mapType == 2)                     // Dungeon or Raid
    {
        foreach ($_altIds as $id => $mode)
        {
            foreach ($_altNPCs->iterate() as $dId => $__)
            {
                if ($dId != $id)
                    continue;

                $m = Lang::$npc['modes'][$mapType][$mode];

                // Health
                $health = $_altNPCs->getBaseStats('health');
                $modes['health'][] = sprintf($modeRow, $m, $health[0] < $health[1] ? $_nf($health[0]).' - '.$_nf($health[1]) : $_nf($health[0]));

                // Mana (may be 0)
                $mana = $_altNPCs->getBaseStats('power');
                $modes['mana'][] = $mana[0] ? sprintf($modeRow, $m, $mana[0] < $mana[1] ? $_nf($mana[0]).' - '.$_nf($mana[1]) : $_nf($mana[0])) : null;

                // Armor
                $armor = $_altNPCs->getBaseStats('armor');
                $modes['armor'][] = sprintf($modeRow, $m, $armor[0] < $armor[1] ? $_nf($armor[0]).' - '.$_nf($armor[1]) : $_nf($armor[0]));

                // Melee Damage
                $melee = $_altNPCs->getBaseStats('melee');
                if ($_ = $_altNPCs->getField('dmgSchool'))  // magic damage
                    $modes['melee'][] = sprintf($modeRow, $m, $_nf($melee[0]).' - '.$_nf($melee[1]).' ('.Lang::$game['sc'][$_].')');
                else                                        // phys. damage
                    $modes['melee'][] = sprintf($modeRow, $m, $_nf($melee[0]).' - '.$_nf($melee[1]));

                // Ranged Damage
                $ranged = $_altNPCs->getBaseStats('ranged');
                $modes['ranged'][] = sprintf($modeRow, $m, $_nf($ranged[0]).' - '.$_nf($ranged[1]));
            }
        }
    }

    if ($modes)
        foreach ($stats as $k => $v)
            if ($v)
                $stats[$k] = sprintf($hint, implode('[/tr][tr]', $modes[$k]), $v, $k);

    // < Stats
    if ($stats)
        $infobox[] = Lang::$npc['stats'].($modes ? ' ('.Lang::$npc['modes'][$mapType][0].')' : null).Lang::$colon.'[ul][li]'.implode('[/li][li]', $stats).'[/li][/ul]';


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
                if (in_array($t['type'], [2, 16]) && strpos($text, '%s') === false)
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

    // tab: abilities / tab_controlledabilities (dep: VehicleId)
    // SMART_SCRIPT_TYPE_CREATURE = 0; SMART_ACTION_CAST = 11; SMART_ACTION_ADD_AURA = 75; SMART_ACTION_INVOKER_CAST = 85; SMART_ACTION_CROSS_CAST = 86
    $smartSpells = DB::Aowow()->selectCol('SELECT action_param1 FROM smart_scripts WHERE source_type = 0 AND action_type IN (11, 75, 85, 86) AND entryOrGUID = ?d', $_id);
    $tplSpells   = [];
    $conditions  = [['id', $smartSpells]];

    for ($i = 1; $i < 9; $i++)
        if ($_ = $npc->getField('spell'.$i))
            $tplSpells[] = $_;

    if ($tplSpells)
    {
        $conditions[] = ['id', $tplSpells];
        $conditions[] = 'OR';
    }

    if ($tplSpells || $smartSpells)
    {
        $abilities = new SpellList($conditions);
        if (!$abilities->error)
        {
            $abilities->addGlobalsToJScript(Util::$pageTemplate, GLOBALINFO_SELF | GLOBALINFO_RELATED);
            $normal    = $abilities->getListviewData();
            $controled = [];

            if ($npc->getField('vehicleId'))                    // not quite right. All seats should be checked for allowed-to-cast-flag-something
            {
                foreach ($normal as $id => $values)
                {
                    if (in_array($id, $smartSpells))
                        continue;

                    $controled[$id] = $values;
                    unset($normal[$id]);
                }
            }

            if ($normal)
                $pageData['relTabs'][] = array(
                    'file'   => 'spell',
                    'data'   => $normal,
                    'params' => [
                        'tabs'        => '$tabsRelated',
                        'name'        => '$LANG.tab_abilities',
                        'id'          => 'abilities'
                    ]
                );

            if ($controled)
                $pageData['relTabs'][] = array(
                    'file'   => 'spell',
                    'data'   => $controled,
                    'params' => [
                        'tabs'        => '$tabsRelated',
                        'name'        => '$LANG.tab_controlledabilities',
                        'id'          => 'controlled-abilities'
                    ]
                );
        }
    }

    // tab: summoned by
    $conditions = array(
        'OR',
        ['AND', ['effect1Id', 28], ['effect1MiscValue', $_id]],
        ['AND', ['effect2Id', 28], ['effect2MiscValue', $_id]],
        ['AND', ['effect3Id', 28], ['effect3MiscValue', $_id]]
    );

    $summoned = new SpellList($conditions);
    if (!$summoned->error)
    {
        $summoned->addGlobalsToJscript(Util::$pageTemplate);

        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $summoned->getListviewData(),
            'params' => [
                'tabs'      => '$tabsRelated',
                'name'      => '$LANG.tab_summonedby',
                'id'        => 'summoned-by'
            ]
        );
    }


    // tab: teaches
    if ($npc->getField('npcflag') & NPC_FLAG_TRAINER)
    {
        $teachQuery = 'SELECT IFNULL(t2.spell, t1.spell) AS ARRAY_KEY,                      IFNULL(t2.spellcost, t1.spellcost) AS cost,  IFNULL(t2.reqskill, t1.reqskill) AS reqSkillId,
                                               IFNULL(t2.reqskillvalue, t1.reqskillvalue) AS reqSkillValue,  IFNULL(t2.reqlevel, t1.reqlevel) AS reqLevel
                                        FROM npc_trainer t1 LEFT JOIN npc_trainer t2 ON t2.entry = IF(t1.spell < 0, -t1.spell, null) WHERE t1.entry = ?d';

        if ($tSpells = DB::Aowow()->select($teachQuery, $_id))
        {
            $teaches = new SpellList(array(['id', array_keys($tSpells)]));
            if (!$teaches->error)
            {
                $teaches->addGlobalsToJscript(Util::$pageTemplate, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $data = $teaches->getListviewData();

                $extra = [];
                foreach ($tSpells as $sId => $train)
                {
                    if (empty($data[$sId]))
                        continue;

                    if ($_ = $train['reqSkillId'])
                    {
                        Util::$pageTemplate->extendGlobalIds(TYPE_SKILL, $_);
                        if (!isset($extra[0]))
                            $extra[0] = 'Listview.extraCols.condition';

                        $data[$sId]['condition'] = ['type' => TYPE_SKILL, 'typeId' => $_, 'status' => 1, 'reqSkillLvl' => $train['reqSkillValue']];
                    }

                    if ($_ = $train['reqLevel'])
                    {
                        if (!isset($extra[1]))
                            $extra[1] = "Listview.funcBox.createSimpleCol('reqLevel', LANG.tooltip_reqlevel, '7%', 'reqLevel')";

                        $data[$sId]['reqLevel'] = $_;
                    }

                    if ($_ = $train['cost'])
                        $data[$sId]['trainingcost'] = $_;
                }

                $pageData['relTabs'][] = array(
                    'file'   => 'spell',
                    'data'   => $data,
                    'params' => [
                        'tabs'        => '$tabsRelated',
                        'name'        => '$LANG.tab_teaches',
                        'id'          => 'teaches',
                        'visibleCols' => "$['trainingcost']",
                        'extraCols'   => $extra ? '$['.implode(', ', $extra).']' : null
                    ]
                );
            }
        }
        else
            Util::$pageTemplate->internalNotice(U_GROUP_EMPLOYEE, 'NPC '.$_id.' is flagged as trainer, but doesn\'t have any spells set');
    }

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

    // tab: starts quest
    $starts = new QuestList(array(['npcStart.id', $_id]));
    if (!$starts->error)
    {
        $starts->addGlobalsToJScript(Util::$pageTemplate);

        $pageData['relTabs'][] = array(
            'file'   => 'quest',
            'data'   => $starts->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_starts',
                'id'   => 'starts'
            ]
        );
    }

    // tab: ends quest
    $ends = new QuestList(array(['npcEnd.id', $_id]));
    if (!$ends->error)
    {
        $ends->addGlobalsToJScript(Util::$pageTemplate);

        $pageData['relTabs'][] = array(
            'file'   => 'quest',
            'data'   => $ends->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_ends',
                'id'   => 'ends'
            ]
        );
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
