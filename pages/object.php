<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id   = intVal($pageParam);
$_path = [0, 5];

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_OBJECT, $_id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_OBJECT, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $object = new GameObjectList(array(['id', $_id]));
        if ($object->error)
            die('$WowheadPower.registerObject('.$_id.', '.User::$localeId.', {});');

        $s = $object->getSpawns(true);

        $x  = '$WowheadPower.registerObject('.$_id.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($object->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($object->renderTooltip())."'\n";
        // $x .= "\tmap: ".($s ? '{zone: '.$s[0].', coords: {0:'.json_encode($s[1], JSON_NUMERIC_CHECK).'}' : '{}')."\n";
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $object = new GameObjectList(array(['id', $_id]));
    if ($object->error)
        $smarty->notFound(Lang::$game['gameObject'], $_id);

    $_path[] = $object->getField('typeCat');


    /*  NOTE

        much like items GOs should have difficulty versions of itself, that are spawned for bosses, but this data is mostly contained in scripts
        also certain chests/caches/ect should be linked to their boss mob

        all of this has to be done manually
    */

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // Event
    if ($_ = DB::Aowow()->selectRow('SELECT e.id, holidayId FROM ?_events e, game_event_gameobject geg, gameobject g WHERE e.id = ABS(geg.eventEntry) AND g.guid = geg.guid AND g.id = ?d', $_id))
    {
        if ($h = $_['holidayId'])
        {
            Util::$pageTemplate->extendGlobalIds(TYPE_WORLDEVENT, $_['id']);
            $infobox[] = Util::ucFirst(Lang::$game['eventShort']).Lang::$colon.'[event='.$h.']';
        }
    }

    // Reaction
    $_ = function ($r)
    {
        if ($r == 1)  return 2;
        if ($r == -1) return 10;
        return;
    };
    $infobox[] = Lang::$npc['react'].Lang::$colon.'[color=q'.$_($object->getField('A')).']A[/color] [color=q'.$_($object->getField('H')).']H[/color]';

    // reqSkill
    switch ($object->getField('typeCat'))
    {
        case -3:                                            // Herbalism
            $infobox[] = sprintf(Lang::$game['requires'], Lang::$spell['lockType'][2].' ('.$object->getField('reqSkill').')');
            break;
        case -4:                                            // Mining
            $infobox[] = sprintf(Lang::$game['requires'], Lang::$spell['lockType'][3].' ('.$object->getField('reqSkill').')');
            break;
        case -5:                                            // Lockpicking
            $infobox[] = sprintf(Lang::$game['requires'], Lang::$spell['lockType'][1].' ('.$object->getField('reqSkill').')');
            break;
        default:                                            // requires key .. maybe
        {
            $locks = Lang::getLocks($object->getField('lockId'));
            $l = '';
            foreach ($locks as $idx => $_)
            {
                if ($idx < 0)
                    continue;

                Util::$pageTemplate->extendGlobalIds(TYPE_ITEM, $idx);
                $l = Lang::$gameObject['key'].Lang::$colon.'[item='.$idx.']';
            }

            // if no propper item is found use a skill
            if ($locks)
                $infobox[] = $l ? $l : array_pop($locks);
        }
    }

    // linked trap
    if ($_ = $object->getField('linkedTrap'))
    {
        Util::$pageTemplate->extendGlobalIds(TYPE_OBJECT, $_);
        $infobox[] = Lang::$gameObject['trap'].Lang::$colon.'[object='.$_.']';
    }

    // trap for
    $trigger = new GameObjectList(array(['linkedTrap', $_id]));
    if (!$trigger->error)
    {
        $trigger->addGlobalsToJScript();
        $infobox[] = Lang::$gameObject['triggeredBy'].Lang::$colon.'[object='.$trigger->id.']';
    }

    // SpellFocus
    if ($_ = $object->getField('spellFocusId'))
        $infobox[] = '[tooltip name=focus]'.Lang::$gameObject['focusDesc'].'[/tooltip][span class=tip tooltip=focus]'.Lang::$gameObject['focus'].Lang::$colon.Util::localizedString(DB::Aowow()->selectRow('SELECT * FROM ?_spellFocusObject WHERE id = ?d', $_), 'name').'[/span]';

    // lootinfo: [min, max, restock]
    if (($_ = $object->getField('lootStack')) && $_[0])
    {
        $buff = Lang::$item['charges'].Lang::$colon.$_[0];
        if ($_[0] < $_[1])
            $buff .= Lang::$game['valueDelim'].$_[1];

        // since Veins don't have charges anymore, the timer is questionable
        $infobox[] = $_[2] > 1 ? '[tooltip name=restock][Recharges every '.Util::formatTime($_[2] * 1000).'][/tooltip][span class=tip tooltip=restock]'.$buff.'[/span]' : $buff;
    }

    // meeting stone [minLevel, maxLevel, zone]
    if ($object->getField('type') == OBJECT_MEETINGSTONE)
    {
        if ($_ = $object->getField('mStone'))
        {
            Util::$pageTemplate->extendGlobalIds(TYPE_ZONE, $_[2]);
            $m = Lang::$game['meetingStone'].Lang::$colon.'[zone='.$_[2].']';

            $l = $_[0];
            if ($_[0] > 1 && $_[1] > $_[0])
                $l .= Lang::$game['valueDelim'].min($_[1], MAX_LEVEL);

            $infobox[] = $l ? '[tooltip name=meetingstone]'.sprintf(Lang::$game['reqLevel'], $l).'[/tooltip][span class=tip tooltip=meetingstone]'.$m.'[/span]' : $m;
        }
    }

    // capture area [minPlayer, maxPlayer, minTime, maxTime, radius]
    if ($object->getField('type') == OBJECT_CAPTURE_POINT)
    {
        if ($_ = $object->getField('capture'))
        {
            $buff = Lang::$gameObject['capturePoint'];

            if ($_[2] > 1 || $_[0])
                $buff .= Lang::$colon.'[ul]';

            if ($_[2] > 1)
                $buff .= '[li]'.Lang::$game['duration'].Lang::$colon.($_[3] > $_[2] ? Util::FormatTime($_[3] * 1000, true).' - ' : null).Util::FormatTime($_[2] * 1000, true).'[/li]';

            if ($_[1])
                $buff .= '[li]'.Lang::$main['players'].Lang::$colon.$_[0].($_[1] > $_[0] ? ' - '.$_[1] : null).'[/li]';

            if ($_[4])
                $buff .= '[li]'.sprintf(Lang::$spell['range'], $_[4]).'[/li]';

            if ($_[2] > 1 || $_[0])
                $buff .= '[/ul]';
        }

        $infobox[] = $buff;
    }

    // AI
    if (User::isInGroup(U_GROUP_STAFF))
    {
        if ($_ = $object->getField('ScriptName'))
            $infobox[] = 'Script'.Lang::$colon.$_;
        else if ($_ = $object->getField('AIName'))
            $infobox[] = 'AI'.Lang::$colon.$_;
    }


    /****************/
    /* Main Content */
    /****************/

    // pageText
    $pageText = [];
    if ($next = $object->getField('pageTextId'))
    {
        while ($next)
        {
            $row = DB::Aowow()->selectRow('SELECT *, text as Text_loc0 FROM page_text pt LEFT JOIN locales_page_text lpt ON pt.entry = lpt.entry WHERE pt.entry = ?d', $next);
            $next = $row['next_page'];
            $pageText[] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
        }
    }

    // positions
    $positions = [];
/*
	$positions = position($object['entry'], 'gameobject');
	// Исправить type, чтобы подсвечивались event-овые объекты
	if ($object['position'])
		foreach ($object['position'] as $z => $zone)
			foreach ($zone['points'] as $p => $pos)
				if ($pos['type'] == 0 && ($events = event_find(array('object_guid' => $pos['guid']))))
				{
					$names = arraySelectKey(event_name($events), 'name');
					$object['position'][$z]['points'][$p]['type'] = 4;
					$object['position'][$z]['points'][$p]['events'] = implode(", ", $names);
				}
*/


    // consider phaseMasks

    // consider pooled spawns


    // menuId 5: Object   g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page' => array(
            'title'      => $object->getField('name', true).' - '.Util::ucFirst(Lang::$game['gameObject']),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'name'       => $object->getField('name', true),
            'tab'        => 0,
            'type'       => TYPE_OBJECT,
            'typeId'     => $_id,
            'infobox'    => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
            'pageText'   => $pageText,
            'positions'  => $positions,
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true,
                BUTTON_VIEW3D  => ['displayId' => $object->getField('displayId'), 'type' => TYPE_OBJECT, 'typeId' => $_id]
            ),
            'reqCSS' => array(
                $pageText ? ['path' => STATIC_URL.'/css/Book.css'] : null,
                // ['path' => STATIC_URL.'/css/Mapper.css'],
                // ['path' => STATIC_URL.'/css/Mapper_ie6.css', 'ieCond' => 'lte IE 6']
            ),
            'reqJS'  => array(
                $pageText ? STATIC_URL.'/js/Book.js' : null,
                // STATIC_URL.'/js/Mapper.js',
                STATIC_URL.'/js/swfobject.js'
            )
        ),
        'relTabs' => []
    );


    /**************/
    /* Extra Tabs */
    /**************/

    // tab: summoned by
    $conditions = array(
        'OR',
        ['AND', ['effect1Id', [50, 76, 104, 105, 106, 107]], ['effect1MiscValue', $_id]],
        ['AND', ['effect2Id', [50, 76, 104, 105, 106, 107]], ['effect2MiscValue', $_id]],
        ['AND', ['effect3Id', [50, 76, 104, 105, 106, 107]], ['effect3MiscValue', $_id]]
    );

    $summons = new SpellList($conditions);
    if (!$summons->error)
    {
        $summons->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);

        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $summons->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'summoned-by',
                'name' => '$LANG.tab_summonedby'
            ]
        );
    }

    // tab: related spells
    if ($_ = $object->getField('spells'))
    {
        $relSpells = new SpellList(array(['id', $_]));
        if (!$relSpells->error)
        {
            $relSpells->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);
            $data = $relSpells->getListviewData();

            foreach ($data as $relId => $d)
                $data[$relId]['trigger'] = array_search($relId, $_);

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $data,
                'params' => [
                    'tabs'       => '$tabsRelated',
                    'id'         => 'spells',
                    'name'       => '$LANG.tab_spells',
                    'hiddenCols' => "$['skill']",
                    'extraCols'  => "$[Listview.funcBox.createSimpleCol('trigger', 'Condition', '10%', 'trigger')]"
                ]
            );
        }
    }

    // tab: criteria of
    $acvs = new AchievementList(array(['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT, ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT]], ['ac.value1', $_id]));
    if (!$acvs->error)
    {
        $acvs->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);

        $pageData['relTabs'][] = array(
            'file'   => 'achievement',
            'data'   => $acvs->getListviewData(),
            'params' => [
                'tabs'       => '$tabsRelated',
                'id'         => 'criteria-of',
                'name'       => '$LANG.tab_criteriaof'
            ]
        );
    }

    // tab: starts quest
    // tab: ends quest
    $startEnd = new QuestList(array(['qse.type', TYPE_OBJECT], ['qse.typeId', $_id]));
    if (!$startEnd->error)
    {
        $startEnd->addGlobalsToJScript();
        $lvData = $startEnd->getListviewData();
        $_ = [[], []];

        foreach ($startEnd->iterate() as $id => $__)
        {
            $m = $startEnd->getField('method');
            if ($m & 0x1)
                $_[0][] = $lvData[$id];
            if ($m & 0x2)
                $_[1][] = $lvData[$id];
        }

        if ($_[0])
        {
            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $_[0],
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts'
                ]
            );
        }

        if ($_[1])
        {
            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $_[1],
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_ends',
                    'id'   => 'ends'
                ]
            );
        }
    }

    // tab: related quests
    if ($_ = $object->getField('reqQuest'))
    {
        $relQuest = new QuestList(array(['id', $_]));
        if (!$relQuest->error)
        {
            $relQuest->addGlobalsToJScript();

            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $relQuest->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_quests',
                    'id'   => 'quests'
                ]
            );
        }
    }

    // tab: contains
    $reqQuest = [];
    if ($_ = $object->getField('lootId'))
    {
        if ($itemLoot = Util::handleLoot(LOOT_GAMEOBJECT, $_, User::isInGroup(U_GROUP_STAFF), $extraCols))
        {
            $hiddenCols = ['source', 'side', 'slot', 'reqlevel'];
            foreach ($itemLoot as $l => $lv)
            {
                if (!empty($hiddenCols))
                    foreach ($hiddenCols as $k => $str)
                        if (!empty($lv[$str]))
                            unset($hiddenCols[$k]);

                if (!$lv['quest'])
                    continue;

                $extraCols[] = 'Listview.extraCols.condition';

                $reqQuest[$lv['id']] = 0;

                $itemLoot[$l]['condition'] = ['type' => TYPE_QUEST, 'typeId' => &$reqQuest[$lv['id']], 'status' => 1];
            }

            $extraCols[] = 'Listview.extraCols.percent';

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $itemLoot,
                'params' => [
                    'tabs'       => '$tabsRelated',
                    'name'       => '$LANG.tab_contains',
                    'id'         => 'contains',
                    'extraCols'  => "$[".implode(', ', array_unique($extraCols))."]",
                    'hiddenCols' => $hiddenCols ? '$'.json_encode(array_values($hiddenCols)) : null
                ]
            );
        }
    }

    if ($reqIds = array_keys($reqQuest))                    // apply quest-conditions as back-reference
    {
        $conditions = array(
            'OR',
            ['reqSourceItemId1', $reqIds], ['reqSourceItemId2', $reqIds],
            ['reqSourceItemId3', $reqIds], ['reqSourceItemId4', $reqIds],
            ['reqItemId1', $reqIds], ['reqItemId2', $reqIds], ['reqItemId3', $reqIds],
            ['reqItemId4', $reqIds], ['reqItemId5', $reqIds], ['reqItemId6', $reqIds]
        );

        $reqQuests = new QuestList($conditions);
        $reqQuests->addGlobalsToJscript();

        foreach ($reqQuests->iterate() as $qId => $__)
        {
            if (empty($reqQuests->requires[$qId][TYPE_ITEM]))
                continue;

            foreach ($reqIds as $rId)
                if (in_array($rId, $reqQuests->requires[$qId][TYPE_ITEM]))
                    $reqQuest[$rId] = $reqQuests->id;
        }
    }

    // tab: Same model as .. whats the fucking point..?
    $sameModel = new GameObjectList(array(['displayId', $object->getField('displayId')], ['id', $_id, '!']));
    if (!$sameModel->error)
    {
        $sameModel->addGlobalsToJScript();

        $pageData['relTabs'][] = array(
            'file'   => 'object',
            'data'   => $sameModel->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_samemodelas',
                'id'   => 'same-model-as'
            ]
        );
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_OBJECT, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$item, Lang::$gameObject, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('object.tpl');

?>
