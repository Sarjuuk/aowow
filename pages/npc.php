<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.community.php';

$id = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_NPC, $id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_NPC, $id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $npc = new CreatureList(array(['ct.id', $id]));
        if ($npc->error)
            die('$WowheadPower.registerNpc(\''.$id.'\', '.User::$localeId.', {})');

        $s = $npc->getSpawns(true);

        $x = '$WowheadPower.registerNpc('.$id.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($npc->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.': \''.$npc->renderTooltip()."',\n";
        $x .= "\tmap: ".($s ? '{zone: '.$s[0].', coords: {0:'.json_encode($s[1], JSON_NUMERIC_CHECK).'}' : '{}')."\n";
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $npc = new CreatureList(array(['ct.id', $id]));
    if ($npc->error)
        $smarty->notFound(Lang::$game['npc']);



    // not yet implemented -> chicken out
    $smarty->error();



    unset($npc);

    // Ищем NPC:
    $npc = array();
    $path = [0, 4, $npc['type']];

    $row = $DB->selectRow('
        SELECT
            ?#, c.entry, c.name,
            {
                l.name_loc'.$_SESSION['locale'].' as `name_loc`,
                l.subname_loc'.$_SESSION['locale'].' as `subname_loc`,
                ?,
            }
            f.name_loc'.$_SESSION['locale'].' as `faction-name`, ft.factionID as `factionID`,
            ((CASE exp WHEN 0 THEN mincls.basehp0 WHEN 1 THEN mincls.basehp1 WHEN 2 THEN mincls.basehp2 END)*Health_mod) AS minhealth,
            ((CASE exp WHEN 0 THEN maxcls.basehp0 WHEN 1 THEN maxcls.basehp1 WHEN 2 THEN maxcls.basehp2 END)*Health_mod) AS maxhealth,
            (mincls.basemana*Mana_mod) AS minmana,
            (maxcls.basemana*Mana_mod) AS maxmana,
            (maxcls.basearmor*Armor_mod) AS armor
        FROM ?_factiontemplate ft, ?_factions f, creature_template c
        LEFT JOIN creature_classlevelstats mincls ON mincls.level=minlevel AND mincls.class=unit_class
        LEFT JOIN creature_classlevelstats maxcls ON maxcls.level=maxlevel AND maxcls.class=unit_class
        {
            LEFT JOIN (locales_creature l)
            ON l.entry = c.entry AND ?
        }
        WHERE
            c.entry = ?
            AND ft.factiontemplateID = c.faction_A
            AND f.factionID = ft.factionID
        LIMIT 1
            ',
        $npc_cols[1],
        ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
        ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
        $id
    );

    if($row)
    {
        $npc = $row;
        $npc['name'] = localizedName($row);
        $npc['subname'] = localizedName($row, 'subname');
        if($npc['rank'] == 3)
        {
            $npc['minlevel'] = '??';
            $npc['maxlevel'] = '??';
        }
        $npc['mindmg'] = round(($row['mindmg'] + $row['attackpower']) * $row['dmg_multiplier']);
        $npc['maxdmg'] = round(($row['maxdmg'] + $row['attackpower']) * $row['dmg_multiplier']);

        $toDiv = array('minhealth', 'maxmana', 'minmana', 'maxhealth', 'armor', 'mindmg', 'maxdmg');
        // Разделяем на тысячи (ххххххххх => ххх,ххх,ххх)
        foreach($toDiv as $e)
            $npc[$e] = number_format($npc[$e]);

        $npc['rank'] = $smarty->get_config_vars('rank'.$npc['rank']);
        // faction_A = faction_H
        $npc['faction_num'] = $row['factionID'];
        $npc['faction'] = $row['faction-name'];
        // Деньги
        $money = ($row['mingold']+$row['maxgold']) / 2;
        $npc = array_merge($npc, money2coins($money));
        // Героик/нормал копия НПС
        if($npc['difficulty_entry_1'])
        {
            // это нормал НПС, ищем героика
            if($tmp = creatureinfo($npc['difficulty_entry_1']))
            {
                $npc['heroic'] = array(
                    'type'    => 0,
                    'entry'    => $tmp['entry'],
                    'name'    => str_replace(LOCALE_HEROIC, '', $tmp['name'])
                );

                unset($tmp);
            }
        }
        else
        {
            // А может быть героик НПС одним для нескольких нормалов?
            // считаем что нет
            $tmp = $DB->selectRow('
                    SELECT c.entry, c.name
                    {
                        , l.name_loc?d as `name_loc`
                    }
                    FROM creature_template c
                    {
                        LEFT JOIN (locales_creature l)
                        ON l.entry = c.entry AND ?
                    }
                    WHERE
                        c.difficulty_entry_1 = ?d
                    LIMIT 1
                ',
                ($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
                ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
                $npc['entry']
            );
            if($tmp)
            {
                $npc['heroic'] = array(
                    'type'    => 1,
                    'entry'    => $tmp['entry'],
                    'name'    => localizedName($tmp)
                );
                $npc['name'] = str_replace(' (1)', '', $npc['name']);
                $normal_entry = $tmp['entry'];
                unset($tmp);
            }
        }
        // Дроп
        $lootid=$row['lootid'];
        $skinid=$row['skinloot'];
        $pickpocketid=$row['pickpocketloot'];
        // Используемые спеллы
        $npc['ablities'] = array();
        $tmp = array();
        for($j=0;$j<=4;++$j)
        {
            if($row['spell'.$j] && !in_array($row['spell'.$j], $tmp))
            {
                $tmp[] = $row['spell'.$j];
                if($data = spellinfo($row['spell'.$j], 0))
                {
                    if($data['name'])
                        $npc['abilities'][] = $data;
                }
            }
        }
        for($j=1;$j<4;$j++)
        {
            $tmp2 = $DB->select('
                SELECT action?d_param1
                FROM creature_ai_scripts
                WHERE
                    creature_id=?d
                    AND action?d_type=11
                ',
                $j,
                $npc['entry'],
                $j
            );
            if($tmp2)
                foreach($tmp2 as $i=>$tmp3)
                    if(!in_array($tmp2[$i]['action'.$j.'_param1'], $tmp))
                    {
                        $tmp[] = $tmp2[$i]['action'.$j.'_param1'];
                        if($data = spellinfo($tmp2[$i]['action'.$j.'_param1'], 0))
                        {
                            if($data['name'])
                                $npc['abilities'][] = $data;
                        }
                    }
        }
        if(!$npc['ablities'])
            unset($npc['ablities']);

        // Обучает:
        // Если это пет со способностью:
        /* // Временно закомментировано
        $row = $DB->selectRow('
            SELECT Spell1, Spell2, Spell3, Spell4
            FROM petcreateinfo_spell
            WHERE
                entry=?d
            ',
            $npc['entry']
        );
        if($row)
        {
            $npc['teaches'] = array();
            for($j=1;$j<=4;$j++)
                if($row['Spell'.$j])
                    for($k=1;$k<=3;$k++)
                    {
                        $spellrow = $DB->selectRow('
                            SELECT ?#, spellID
                            FROM ?_spell, ?_spellicons
                            WHERE
                                spellID=(SELECT effect'.$k.'triggerspell FROM ?_spell WHERE spellID=?d AND (effect'.$k.'id IN (36,57)))
                                AND id=spellicon
                            LIMIT 1
                            ',
                            $spell_cols[2],
                            $row['Spell'.$j]
                        );
                        if($spellrow)
                        {
                            $num = count($npc['teaches']);
                            $npc['teaches'][$num] = array();
                            $npc['teaches'][$num] = spellinfo2($spellrow);
                        }
                    }
        }
        unset ($row);*/

        // Если это просто тренер
        $teachspells = $DB->select('
            SELECT ?#, spellID
            FROM npc_trainer, ?_spell, ?_spellicons
            WHERE
            (
            -entry IN (SELECT spell FROM npc_trainer WHERE entry = ?)
            OR (entry = ? AND npc_trainer.spell > 0)
            )
            AND spellID = npc_trainer.spell
            AND id=spellicon
            ',
            $spell_cols[2],
            $npc['entry'],
            $npc['entry']
        );
        if($teachspells)
        {
            if(!(IsSet($npc['teaches'])))
                $npc['teaches'] = array();
            foreach($teachspells as $teachspell)
            {
                        $num = count($npc['teaches']);
                        $npc['teaches'][$num] = array();
                        $npc['teaches'][$num] = spellinfo2($teachspell);
            }
        }
        unset ($teachspells);

        // Продает:
        $rows_s = $DB->select('
            SELECT ?#, i.entry, i.maxcount, n.`maxcount` as `drop-maxcount`, n.ExtendedCost
                {, l.name_loc?d AS `name_loc`}
            FROM npc_vendor n, ?_icons, item_template i
                {LEFT JOIN (locales_item l) ON l.entry=i.entry AND ?d}
            WHERE
                n.entry=?
                AND i.entry=n.item
                AND id=i.displayid
            ',
            $item_cols[2],
            ($_SESSION['locale'])? $_SESSION['locale']: DBSIMPLE_SKIP,
            ($_SESSION['locale'])? 1: DBSIMPLE_SKIP,
            $id
        );
        if($rows_s)
        {
            $npc['sells'] = array();
            foreach($rows_s as $numRow=>$row)
            {
                $npc['sells'][$numRow] = array();
                $npc['sells'][$numRow] = iteminfo2($row);
                $npc['sells'][$numRow]['maxcount'] = $row['drop-maxcount'];
                $npc['sells'][$numRow]['cost'] = array();
                if($row['ExtendedCost'])
                {
                    $extcost = $DB->selectRow('SELECT * FROM ?_item_extended_cost WHERE extendedcostID=?d LIMIT 1', abs($row['ExtendedCost']));
                    if($extcost['reqhonorpoints']>0)
                        $npc['sells'][$numRow]['cost']['honor'] = (($npc['A']==1)? 1: -1) * $extcost['reqhonorpoints'];
                    if($extcost['reqarenapoints']>0)
                        $npc['sells'][$numRow]['cost']['arena'] = $extcost['reqarenapoints'];
                    $npc['sells'][$numRow]['cost']['items'] = array();
                    for($j=1;$j<=5;$j++)
                        if(($extcost['reqitem'.$j]>0) and ($extcost['reqitemcount'.$j]>0))
                        {
                            allitemsinfo($extcost['reqitem'.$j], 0);
                            $npc['sells'][$numRow]['cost']['items'][] = array('item' => $extcost['reqitem'.$j], 'count' => $extcost['reqitemcount'.$j]);
                        }
                }
                if($row['BuyPrice']>0)
                    $npc['sells'][$numRow]['cost']['money'] = $row['BuyPrice'];
            }
            unset ($row);
            unset ($numRow);
            unset ($extcost);
        }
        unset ($rows_s);

        // Дроп
        if(!($npc['drop'] = loot('creature_loot_template', $lootid)))
            unset ($npc['drop']);

        // Кожа
        if(!($npc['skinning'] = loot('skinning_loot_template', $skinid)))
            unset ($npc['skinning']);

        // Воруеццо
        if(!($npc['pickpocketing'] = loot('pickpocketing_loot_template', $pickpocketid)))
            unset ($npc['pickpocketing']);

        // Начиниают квесты...
        $rows_qs = $DB->select('
                    SELECT q.?#
                FROM quest_template q
                LEFT JOIN creature_questrelation c on q.id = c.quest
                WHERE
                    c.id=?
                 ',
                $quest_cols[2],
                $id
            );
        if($rows_qs)
        {
            $npc['starts'] = array();
            foreach($rows_qs as $numRow=>$row) {
                $npc['starts'][] = GetQuestInfo($row, 0xFFFFFF);
            }
        }
        unset ($rows_qs);

        // Начиниают event-only квесты...
        $rows_qse = event_find(array('quest_creature_id' => $id));
        if($rows_qse)
        {
            if (!isset($npc['starts']))
                $npc['starts'] = array();
            foreach($rows_qse as $event)
                foreach($event['creatures_quests_id'] as $ids)
                    $npc['starts'][] = GetDBQuestInfo($ids['quest'], 0xFFFFFF);
        }
        unset ($rows_qse);

        // Заканчивают квесты...
$rows_qe = $DB->select('
        SELECT q.?#
        FROM quest_template q
        LEFT JOIN creature_involvedrelation c on q.id = c.quest
        WHERE
                c.id=?
        ',
        $quest_cols[2],
        $id
        );
        if($rows_qe)
        {
            $npc['ends'] = array();
            foreach($rows_qe as $numRow=>$row) {
                $npc['ends'][] = GetQuestInfo($row, 0xFFFFFF);
            }
        }
        unset ($rows_qe);

        // Необходимы для квеста..
        $rows_qo = $DB->select('
            SELECT ?#
            FROM quest_template
            WHERE
                RequiredNpcOrGo1=?
                OR RequiredNpcOrGo2=?
                OR RequiredNpcOrGo3=?
                OR RequiredNpcOrGo4=?
            ',
            $quest_cols[2],
            $id, $id, $id, $id
        );
        if($rows_qo)
        {
            $npc['objectiveof'] = array();
            foreach($rows_qo as $numRow=>$row)
                $npc['objectiveof'][] = GetQuestInfo($row, 0xFFFFFF);
        }
        unset ($rows_qo);

        // Цель критерии
        $rows = $DB->select('
                SELECT a.id, a.faction, a.name_loc?d AS name, a.description_loc?d AS description, a.category, a.points, s.iconname, z.areatableID
                FROM ?_spellicons s, ?_achievementcriteria c, ?_achievement a
                LEFT JOIN (?_zones z) ON a.map != -1 AND a.map = z.mapID
                WHERE
                    a.icon = s.id
                    AND a.id = c.refAchievement
                    AND c.type IN (?a)
                    AND c.value1 = ?d
                GROUP BY a.id
                ORDER BY a.name_loc?d
            ',
            $_SESSION['locale'],
            $_SESSION['locale'],
            array(ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE),
            $npc['entry'],
            $_SESSION['locale']
        );
        if($rows)
        {
            $npc['criteria_of'] = array();
            foreach($rows as $row)
            {
                allachievementsinfo2($row['id']);
                $npc['criteria_of'][] = achievementinfo2($row);
            }
        }

        // Положения созданий божих (для героик НПС не задана карта, юзаем из нормала):
        if($normal_entry)
            // мы - героик НПС, определяем позицию по нормалу
            $npc['position'] = position($normal_entry, 'creature', 2);
        else
            // мы - нормал НПС или НПС без сложности
            $npc['position'] = position($npc['entry'], 'creature', 1);

        // Исправить type, чтобы подсвечивались event-овые NPC
        if ($npc['position'])
            foreach ($npc['position'] as $z => $zone)
                foreach ($zone['points'] as $p => $pos)
                    if ($pos['type'] == 0 && ($events = event_find(array('creature_guid' => $pos['guid']))))
                    {
                        $names = array_select_key(event_name($events), 'name');
                        $npc['position'][$z]['points'][$p]['type'] = 4;
                        $npc['position'][$z]['points'][$p]['events'] = implode(", ", $names);
                    }

    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// menuId 4: Npc      g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'mapper' => true,
    'title'  => implode(" - ", $pageData['title']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_NPC,
    'typeId' => $id
));

$smarty->assign('community', CommunityContent::getAll(TYPE_NPC, $id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$npc, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('npc.tpl');

?>
