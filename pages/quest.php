<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.community.php';

$id = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_QUEST, $id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_QUEST, $id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $quest = new QuestList(array(['qt.id', $id]));
        if ($quest->error)
            die('$WowheadPower.registerQuest(\''.$id.'\', '.User::$localeId.', {})');

        $x = '$WowheadPower.registerQuest('.$id.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($quest->getField('Title', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.': \''.$quest->renderTooltip()."'\n";            // daily: 1 ... not used in wowheadPower => omitted here
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $quest = new QuestList(array(['qt.id', $id]));
    if ($quest->error)
        $smarty->notFound(Lang::$game['quest']);



    // not yet implemented -> chicken out
    $smarty->error();



	unset($quest);

	// Основная инфа
	$quest = GetDBQuestInfo($id, 0xFFFFFF);
    $path = [0, 3]; // TODO

	/*              ЦЕПОЧКА КВЕСТОВ              */
	// Добавляем сам квест в цепочку
	$quest['series'] = array(
		array(
			'Id' => $quest['Id'],
			'Title' => $quest['Title'],
			'NextQuestIdChain' => $quest['NextQuestIdChain']
			)
	);
	// Квесты в цепочке до этого квеста
	$tmp = $quest['series'][0];
	while($tmp)
	{
		$tmp = $DB->selectRow('
			SELECT q.Id, q.Title
				{, l.Title_loc?d as Title_loc}
			FROM quest_template q
				{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?d}
			WHERE q.NextQuestIdChain=?d
			LIMIT 1
			',
			($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
			($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
			$quest['series'][0]['Id']
		);
		if($tmp)
		{
			$tmp['Title'] = localizedName($tmp, 'Title');
			array_unshift($quest['series'], $tmp);
		}
	}
	// Квесты в цепочке после этого квеста
	$tmp = end($quest['series']);
	while($tmp)
	{
		$tmp = $DB->selectRow('
			SELECT q.Id, q.Title, q.NextQuestIdChain
				{, l.Title_loc?d as Title_loc}
			FROM quest_template q
				{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
			WHERE q.Id=?d
			LIMIT 1
			',
			($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
			($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
			$quest['series'][count($quest['series'])-1]['NextQuestIdChain']
		);
		if($tmp)
		{
			$tmp['Title'] = localizedName($tmp, 'Title');
			array_push($quest['series'], $tmp);
		}
	}
	unset($tmp);
	if(count($quest['series'])<=1)
		unset($quest['series']);


	/*              ДРУГИЕ КВЕСТЫ              */
	// (после их нахождения проверяем их тайтлы на наличие локализации)


	// Квесты, которые необходимо выполнить, что бы получить этот квест
	if(!$quest['req'] = $DB->select('
				SELECT q.Id, q.Title, q.NextQuestIdChain
					{, l.Title_loc?d as Title_loc}
				FROM quest_template q
					{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
				WHERE
					(q.NextQuestId=?d AND q.ExclusiveGroup<0)
					OR (q.Id=?d AND q.NextQuestIdChain<>?d)
				LIMIT 20',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP, ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$quest['Id'], $quest['PrevQuestId'], $quest['Id']
				)
		)
			unset($quest['req']);
		else
			$questItems[] = 'req';

	// Квесты, которые становятся доступными, только после того как выполнен этот квест (необязательно только он)
	if(!$quest['open'] = $DB->select('
				SELECT q.Id, q.Title
					{, l.Title_loc?d as Title_loc}
				FROM quest_template q
					{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
				WHERE
					(q.PrevQuestId=?d AND q.Id<>?d)
					OR q.Id=?d
				LIMIT 20',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP, ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$quest['Id'], $quest['NextQuestIdChain'], $quest['NextQuestId']
				)
		)
			unset($quest['open']);
		else
			$questItems[] = 'open';

	// Квесты, которые становятся недоступными после выполнения этого квеста
	if($quest['ExclusiveGroup']>0)
		if(!$quest['closes'] = $DB->select('
				SELECT q.Id, q.Title
					{, l.Title_loc?d as Title_loc}
				FROM quest_template q
					{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
				WHERE
					q.ExclusiveGroup=?d AND q.Id<>?d
				LIMIT 20
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP, ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$quest['ExclusiveGroup'], $quest['Id']
				)
		)
			unset($quest['closes']);
		else
			$questItems[] = 'closes';

	// Требует выполнения одного из квестов, на выбор:
	if(!$quest['reqone'] = $DB->select('
				SELECT q.Id, q.Title
					{, l.Title_loc?d as Title_loc}
				FROM quest_template q
					{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
				WHERE
					q.ExclusiveGroup>0 AND q.NextQuestId=?d
				LIMIT 20
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP, ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$quest['Id']
				)
		)
			unset($quest['reqone']);
		else
			$questItems[] = 'reqone';

	// Квесты, которые доступны, только во время выполнения этого квеста
	if(!$quest['enables'] = $DB->select('
				SELECT q.Id, q.Title
					{, l.Title_loc?d as Title_loc}
				FROM quest_template q
					{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
				WHERE q.PrevQuestId=?d
				LIMIT 20
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP, ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				-$quest['Id']
				)
		)
			unset($quest['enables']);
		else
			$questItems[] = 'enables';

	// Квесты, во время выполнения которых доступен этот квест
	if($quest['PrevQuestId']<0)
		if(!$quest['enabledby'] = $DB->select('
				SELECT q.Id, q.Title
					{, l.Title_loc?d as Title_loc}
				FROM quest_template q
					{LEFT JOIN (locales_quest l) ON l.Id=q.Id AND ?}
				WHERE q.Id=?d
				LIMIT 20
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP, ($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				-$quest['PrevQuestId']
				)
		)
			unset($quest['enabledby']);
		else
			$questItems[] = 'enabledby';

	// Теперь локализуем все тайтлы квестов
	if($questItems)
		foreach($questItems as $item)
			foreach($quest[$item] as $i => $x)
				$quest[$item][$i]['Title'] = localizedName($quest[$item][$i], 'Title');



	/*             НАГРАДЫ И ТРЕБОВАНИЯ             */

	if($quest['RequiredSkillPoints']>0 && $quest['SkillOrClassMask']>0)
	{
		// Требуемый уровень скилла, что бы получить квест
		/*
		$skills = array(
			-264 => 197,	// Tailoring
			-182 => 165,	// Leatherworking
			-24 => 182,		// Herbalism
			-101 => 356,	// Fishing
			-324 =>	129,	// First Aid
			-201 => 202,	// Engineering
			-304 => 185,	// Cooking
			-121 => 164,	// Blacksmithing
			-181 => 171		// Alchemy
		);
		*/

		// TODO: skill localization
		$quest['reqskill'] = array(
			'name' => $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_skill WHERE skillID=?d LIMIT 1',$quest['SkillOrClassMask']),
			'value' => $quest['RequiredSkillPoints']
		);
	}
	elseif($quest['SkillOrClassMask']<0)
	{
		$s = array();
		foreach($classes as $i => $class)
			if (intval(-$quest['SkillOrClassMask'])==$i)
				$s[] = $class;

		if (!count($s) == 0)
			// Требуемый класс, что бы получить квест
			$quest['reqclass'] = implode(", ", $s);
	}

	// Требуемые отношения с фракциями, что бы начать квест
	if($quest['RequiredMinRepFaction'])
		$quest['RequiredMinRep'] = array(
			'name' => $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_factions WHERE factionID=?d LIMIT 1', $quest['RequiredMinRepFaction']),
			'entry' => $quest['RequiredMinRepFaction'],
			'value' => reputations($quest['RequiredMinRepValue'])
		);
	if($quest['RequiredMaxRepFaction'])
		$quest['RequiredMaxRep'] = array(
			'name' => $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_factions WHERE factionID=?d LIMIT 1', $quest['RequiredMaxRepFaction']),
			'entry' => $quest['RequiredMaxRepFaction'],
			'value' => reputations($quest['RequiredMaxRepValue'])
		);

	// Спеллы не требуют локализации, их инфа берется из базы
	// Хранить в базе все локализации - задачка на будующее

	// Спелл, кастуемый на игрока в начале квеста
	if($quest['SourceSpell'])
	{
		$tmp = $DB->selectRow('
			SELECT ?#, s.spellname_loc'.$_SESSION['locale'].'
			FROM ?_spell s, ?_spellicons si
			WHERE
				s.spellID=?d
				AND si.id=s.spellicon
			LIMIT 1',
			$spell_cols[0],
			$quest['SourceSpell']
		);
		if($tmp)
		{
			$quest['SourceSpell'] = array(
				'name' => $tmp['spellname_loc'.$_SESSION['locale']],
				'entry' => $tmp['spellID']);
			allspellsinfo2($tmp);
		}
		unset($tmp);
	}

	// Итем, выдаваемый игроку в начале квеста
	if($quest['SourceItemId'])
	{
		$quest['SourceItemId'] = iteminfo($quest['SourceItemId']);
		$quest['SourceItemId']['SourceItemCount'] = $quest['SourceItemCount'];
	}

	// Дополнительная информация о квесте (флаги, повторяемость, скрипты)
	$quest['flagsdetails'] = GetFlagsDetails($quest);
	if (!$quest['flagsdetails'])
		unset($quest['flagsdetails']);

	// Спелл, кастуемый на игрока в награду за выполнение
	if($quest['RewardSpellCast']>0 || $quest['RewardSpell']>0)
	{
		$tmp = $DB->SelectRow('
			SELECT ?#, s.spellname_loc'.$_SESSION['locale'].'
			FROM ?_spell s, ?_spellicons si
			WHERE
				s.spellID=?d
				AND si.id=s.spellicon
			LIMIT 1',
			$spell_cols[0],
			$quest['RewardSpell']>0?$quest['RewardSpell']:$quest['RewardSpellCast']
		);
		if($tmp)
		{
			$quest['spellreward'] = array(
				'name' => $tmp['spellname_loc'.$_SESSION['locale']],
				'entry' => $tmp['spellID'],
				'realentry' => $quest['RewardSpellCast']>0 ? $quest['RewardSpellCast'] : $quest['RewardSpell']);
			allspellsinfo2($tmp);
		}
		unset($tmp);
	}

	// Создания, необходимые для квеста
	//$quest['creaturereqs'] = array();
	//$quest['objectreqs'] = array();
	$quest['coreqs'] = array();
	for($i=0;$i<=4;++$i)
	{
		//echo $quest['ReqCreatureOrGOCount'.$i].'<br />';
		if($quest['RequiredNpcOrGo'.$i] != 0 && $quest['RequiredNpcOrGoCount'.$i] != 0)
		{
			if($quest['RequiredNpcOrGo'.$i] > 0)
			{
				// Необходимо какое-либо взамодействие с созданием
				$quest['coreqs'][$i] = array_merge(
					creatureinfo($quest['RequiredNpcOrGo'.$i]),
					array('req_type' => 'npc')
				);
			}
			else
			{
				// необходимо какое-то взаимодействие с объектом
				$quest['coreqs'][$i] = array_merge(
					objectinfo(-$quest['RequiredNpcOrGo'.$i]),
					array('req_type' => 'object')
				);
			}
			// Количество
			$quest['coreqs'][$i]['count'] = $quest['RequiredNpcOrGoCount'.$i];
			// Спелл
			if($quest['RequiredSpellCast'.$i])
				$quest['coreqs'][$i]['spell'] = array(
					'name' => $DB->selectCell('SELECT spellname_loc'.$_SESSION['locale'].' FROM ?_spell WHERE spellid=?d LIMIT 1', $quest['RequiredSpellCast'.$i]),
					'entry' => $quest['RequiredSpellCast'.$i]
				);
		}
	}
	if(!$quest['coreqs'])
		unset($quest['coreqs']);

	// Вещи, необходимые для квеста
	$quest['itemreqs'] = array();
	for($i=0;$i<=4;++$i)
	{
		if($quest['RequiredItemId'.$i]!=0 && $quest['RequiredItemCount'.$i]!=0)
			$quest['itemreqs'][] = array_merge(iteminfo($quest['RequiredItemId'.$i]), array('count' => $quest['RequiredItemCount'.$i]));
	}
	if(!$quest['itemreqs'])
		unset($quest['itemreqs']);

	// Фракции необходимые для квеста
	if($quest['RepObjectiveFaction']>0)
	{
		$quest['factionreq'] = array(
			'name' => $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_factions WHERE factionID=?d LIMIT 1', $quest['RepObjectiveFaction']),
			'entry' => $quest['RepObjectiveFaction'],
			'value' => reputations($quest['RepObjectiveValue'])
		);
	}

	/* КВЕСТГИВЕРЫ И КВЕСТТЕЙКЕРЫ */

	// КВЕСТГИВЕРЫ
	// НПС
	$rows = $DB->select('
		SELECT c.entry, c.name, A, H
			{, l.name_loc?d AS name_loc}
		FROM creature_questrelation q, ?_factiontemplate, creature_template c
			{LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ?}
		WHERE
			q.quest=?d
			AND c.entry=q.id
			AND factiontemplateID=c.faction_A
		',
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$quest['Id']
	);
	if($rows)
	{
		foreach($rows as $tmp)
		{
			$tmp['name'] = localizedName($tmp);
			if($tmp['A'] == -1 && $tmp['H'] == 1)
				$tmp['side'] = 'horde';
			elseif($tmp['A'] == 1 && $tmp['H'] == -1)
				$tmp['side'] = 'alliance';
			$quest['start'][] = array_merge($tmp, array('type' => 'npc'));
		}
	}
	unset($rows);

	// НПС-ивентовые
	$rows = event_find(array('quest_id' => $quest['Id']));
	if ($rows)
	{
		foreach ($rows as $event)
			foreach ($event['creatures_quests_id'] as $ids)
				if ($ids['quest'] == $quest['Id'])
				{
					$tmp = creatureinfo($ids['creature']);
					if($tmp['react'] == '-1,1')
						$tmp['side'] = 'horde';
					elseif($tmp['react'] == '1,-1')
						$tmp['side'] = 'alliance';
					$tmp['type'] = 'npc';
					$tmp['event'] = $event['entry'];
					$quest['start'][] = $tmp;
				}
	}
	unset($rows);

	// ГО
	$rows = $DB->select('
		SELECT g.entry, g.name
			{, l.name_loc?d AS name_loc}
		FROM gameobject_questrelation q, gameobject_template g
			{LEFT JOIN (locales_gameobject l) ON l.entry = g.entry AND ?}
		WHERE
			q.quest=?d
			AND g.entry=q.id
		',
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$quest['Id']
	);
	if($rows)
	{
		foreach($rows as $tmp)
		{
			$tmp['name'] = localizedName($tmp);
			$quest['start'][] = array_merge($tmp, array('type' => 'object'));
		}
	}
	unset($rows);

	// итем
	$rows = $DB->select('
		SELECT i.name, i.entry, i.quality, LOWER(a.iconname) AS iconname
			{, l.name_loc?d AS name_loc}
		FROM ?_icons a, item_template i
			{LEFT JOIN (locales_item l) ON l.entry=i.entry AND ?}
		WHERE
			startquest = ?d
			AND id = displayid
		',
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$quest['Id']
	);
	if($rows)
	{
		foreach($rows as $tmp)
		{
			$tmp['name'] = localizedName($tmp);
			$quest['start'][] = array_merge($tmp, array('type' => 'item'));
		}
	}
	unset($rows);

	// КВЕСТТЕЙКЕРЫ
	// НПС
	$rows = $DB->select('
		SELECT c.entry, c.name, A, H
			{, l.name_loc?d AS name_loc}
		FROM creature_involvedrelation q, ?_factiontemplate, creature_template c
			{LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ?}
		WHERE
			q.quest=?d
			AND c.entry=q.id
			AND factiontemplateID=c.faction_A
		',
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$quest['Id']
	);
	if($rows)
	{
		foreach($rows as $tmp)
		{
			$tmp['name'] = localizedName($tmp);
			if($tmp['A'] == -1 && $tmp['H'] == 1)
				$tmp['side'] = 'horde';
			elseif($tmp['A'] == 1 && $tmp['H'] == -1)
				$tmp['side'] = 'alliance';
			$quest['end'][] = array_merge($tmp, array('type' => 'npc'));
		}
	}
	unset($rows);

	// ГО
	$rows = $DB->select('
		SELECT g.entry, g.name
			{, l.name_loc?d AS name_loc}
		FROM gameobject_involvedrelation q, gameobject_template g
			{LEFT JOIN (locales_gameobject l) ON l.entry = g.entry AND ?}
		WHERE
			q.quest=?d
			AND g.entry=q.id
		',
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$quest['Id']
	);
	if($rows)
	{
		foreach($rows as $tmp)
		{
			$tmp['name'] = localizedName($tmp);
			$quest['end'][] = array_merge($tmp, array('type' => 'object'));
		}
	}
	unset($rows);

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
		array(ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST),
		$quest['Id'],
		$_SESSION['locale']
	);
	if($rows)
	{
		$quest['criteria_of'] = array();
		foreach($rows as $row)
		{
			allachievementsinfo2($row['id']);
			$quest['criteria_of'][] = achievementinfo2($row);
		}
	}

	// Награды и благодарности, присылаемые почтой
	if ($quest['RewardMailTemplateId'])
	{
		if(!($quest['mailrewards'] = loot('mail_loot_template', $quest['RewardMailTemplateId'])))
			unset ($quest['mailrewards']);
	}
	if ($quest['RewardMailDelay'])
		$quest['maildelay'] = sec_to_time($quest['RewardMailDelay']);

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// menuId 3: Quest    g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => implode(" - ", $pageData['title']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_QUEST,
    'typeId' => $id
));

$smarty->assign('community', CommunityContent::getAll(TYPE_QUEST, $id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$quest, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('quest.tpl');

?>
