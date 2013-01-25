<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.spell.php';
require 'includes/class.item.php';
// require 'includes/allnpcs.php';
// require 'includes/allquests.php';
// require 'includes/class.community.php';                  // not needed .. yet
// require 'includes/class.achievement.php';

$id    = intVal($pageParam);
$spell = new Spell($id);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_SPELL, $id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_SPELL, $id, -1, User::$localeId]);

if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale($_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $spell = new Spell($id);
        if (!$spell->template)
            die('$WowheadPower.registerSpell(\''.$id.'\', '.User::$localeId.', {})');

        $x = '$WowheadPower.registerSpell('.$id.', '.User::$localeId.",{\n";
        if ($n = Util::localizedString($spell->template, 'spellname'))
            $x .= "\tname_".User::$localeString.": '".Util::jsEscape($n)."',\n";
        if ($i = $spell->template['iconString'])
            $x .= "\ticon: '".Util::jsEscape($i)."',\n";
        if ($spell->getTooltip())
            $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($spell->tooltip)."'";
        if ($spell->getBuff())
            $x .= ",\n\tbuff_".User::$localeString.": '".Util::jsEscape($spell->buff)."'\n";
        $x .= '});';

        $smarty->saveCache($cacheKeyTooltip, $x);
    }
    die($x);
}


// v there be dragons v


if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    unset($spell);

    // Spelldata
    if ($spellObj = new Spell($id))
    {
        $row = $spellObj->template; // equivalent to 5 layers of panzertape

        // Номер спелла
        $spell['entry'] = $row['Id'];
        // Имя спелла
        $spell['name'] = Util::localizedString($row, 'name');
        // Иконка спелла
        //$spell['icon'] = $row['iconname'];
        // Стакается до
        $spell['stack'] = $row['stackAmount'];
        // Затраты маны на сспелл
        // check for custom PowerDisplay
        $pt = $row['powerDisplayString'] ? $row['powerDisplayString'] : $row['powerType'];
        if ($row['powerCostPercent'] > 0)
            $spell['manacost'] = $row['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$pt]))."<br />";
        else if ($row['powerCost'] > 0)
            $spell['manacost'] = ($pt == 1 ? $row['powerCost'] / 10 : $row['powerCost']).' '.Lang::$spell['powerTypes'][$pt].'<br />';
        // Уровень спелла
        $spell['level'] = $row['spellLevel'];
        // Дальность
        // TODO: переделать дальность для новых колонок
        $spell['range'] = '';
        if(($row['rangeMinHostile'] != $row['rangeMaxHostile']) && ($row['rangeMinHostile'] != 0))
            $spell['range'] = $row['rangeMinHostile'].'-';
        $spell['range'] .= $row['rangeMaxHostile'];
        $spell['rangename'] = Util::localizedString($row, 'rangeText');
        // Время каста
        if($row['castTime'] > 0)
            $spell['casttime'] = fmt_time($row['castTime']);
        else if($row['interruptFlagsChannel'])
            $spell['casttime'] = Lang::$spell['channeled'];
        else
            $spell['casttime'] = Lang::$spell['instant'];
        // Cooldown
        if($row['recoveryTime'] > 0)
            $spell['cooldown'] = fmt_time($row['recoveryTime']);
        // Время действия спелла
        if($row['duration'] > 0)
            $spell['duration'] = fmt_time($row['duration']);
        else
            $spell['duration'] ='<span class="q0">n/a</span>';
        // Школа
         $spell['school'] = Lang::getMagicSchools($row['schoolMask']);
        // Диспелл
        $spell['dispel'] = $row['dispelType'];
        // Механика
        $spell['mechanic'] = $row['mechanic'];

        // Информация о спелле
        $spell['info'] = $spellObj->parseText('description');

        // Инструменты
        $spell['tools'] = array();
        $i=0;
        for ($j=1;$j<=2;$j++)
        {
            if($row['tool'.$j])
            {
                $tool_row = allitemsinfo($row['tool'.$j], 0);
                $spell['tools'][$i] = array(
                    'name'        => $tool_row['name'],
                    'quality'    => $tool_row['quality'],
                    'entry'        => $row['tool'.$j],
                );
                $i++;
            }
        }

        // Реагенты
        $spell['reagents'] = array();
        $i=0;
        for ($j=1;$j<=8;$j++)
        {
            if($row['reagent'.$j])
            {
                $reagent_row = allitemsinfo($row['reagent'.$j], 0);
                $spell['reagents'][$i] = array(
                    'name'        => $reagent_row['name'],
                    'quality'    => $reagent_row['quality'],
                    'entry'        => $row['reagent'.$j],
                    'count'        => $row['reagentcount'.$j],
                );
                $i++;
            }
        }

        $spell['stances'] = Lang::getStances($row['stanceMask']);

        // Btt - Buff TollTip
        if ($buff = $spellObj->getBuff())
            $spell['btt'] = $buff;

        // Iterate through all effects:
        $i=0;
        $spell['effect'] = array();
        for ($j=1;$j<=3;$j++)
        {
            if($row['effect'.$j.'Id'] > 0)
            {
                // Название эффекта
                $spell['effect'][$i]['name'] = '('.$row['effect'.$j.'Id'].') '.Util::$spellEffectStrings[$row['effect'.$j.'Id']];
                // Доп информация в имени
                if($row['effect'.$j.'MiscValue'])
                {
                    switch ($row['effect'.$j.'Id'])
                    {
                        // Если эффект - создание обекта, создаем информацию о нём
                        case 50: // "Summon Object"                // 103 spells, OK
                        case 76: // "Summon Object (Wild)"        // 173 spells, OK
                        //case 86: // "Activate Object"            // 175 spells; wrong GOs, tiny ID; skipping
                        case 104: // "Summon Object (slot 1)"    // 24 spells - traps, OK
                        //case 105: // "Summon Object (slot 2)"    // 2 spells: 22996, 23005; wrong GOs; skipping
                        //case 106: // "Summon Object (slot 3)"    // 0 spells; skipping
                        //case 107: // "Summon Object (slot 4)"    // 0 spells; skipping
                        {
                            $spell['effect'][$i]['object'] = array();
                            $spell['effect'][$i]['object']['entry'] = $row['effect'.$j.'MiscValue'];
                            $spell['effect'][$i]['object']['name'] = DB::Aowow()->selectCell("SELECT name FROM gameobject_template WHERE entry = ? LIMIT 1", $spell['effect'][$i]['object']['entry']).' ('.$spell['effect'][$i]['object']['entry'].')';
                            break;
                        }
                        // скиллы
                        case 118: // "Require Skill"
                        {
                            $spell['effect'][$i]['name'] .= ' ('.DB::Aowow()->selectCell('SELECT name_loc'.User::$localeId.' as name FROM ?_skill WHERE skillID = ? LIMIT 1', $row['effect'.$j.'MiscValue']).')';
                            break;
                        }
                        // ауры
                        case 6:
                        {
                            break;
                        }
                        // тотемы
                        case 75: // "Summon Totem"
                        case 87: // "Summon Totem (slot 1)"
                        case 88: // "Summon Totem (slot 2)"
                        case 89: // "Summon Totem (slot 3)"
                        case 90: // "Summon Totem (slot 4)"
                        {
                            $spell['effect'][$i]['name'] .= ' (<a href="?npc='.$row['effect'.$j.'MiscValue'].'">'.$row['effect'.$j.'MiscValue'].'</a>)';
                            break;
                        }
                        default:
                        {
                            $spell['effect'][$i]['name'] .= ' ('.$row['effect'.$j.'MiscValue'].')';
                        }
                    }
                }
                // Если просто урон школой - добавляем подпись школы
                if($row['effect'.$j.'Id'] == 2 && $spell['school'])
                    $spell['effect'][$i]['name'] .= ' ('.$spell['school'].')';
                // Радиус действия эффекта
                if($row['effect'.$j.'RadiusMax'])
                    $spell['effect'][$i]['radius'] = $row['effect'.$j.'RadiusMax'];
                // Значение спелла (урон)
                if($row['effect'.$j.'BasePoints'] && !$row['effect'.$j.'CreateItemId'])
                    $spell['effect'][$i]['value'] = $row['effect'.$j.'BasePoints'] + 1;
                // Интервал действия спелла
                if($row['effect'.$j.'Periode'] > 0)
                    $spell['effect'][$i]['interval'] = $row['effect'.$j.'Periode'] / 1000;
                // Название ауры:
                if($row['effect'.$j.'AuraId'] > 0 && IsSet($spell_aura_names[$row['effect'.$j.'AuraId']]))
                {
                    $spell['effect'][$i]['name'] .= ' #'.$row['effect'.$j.'AuraId'];
                    switch ($row['effect'.$j.'AuraId'])
                    {
                        case 78: // "Mounted" - приписываем ссылку на нпс
                        case 56: // "Transform"
                        {
                            $spell['effect'][$i]['name'] .= ': '.$spell_aura_names[$row['effect'.$j.'AuraId']].' (<a href="?npc='.$row['effect'.$j.'MiscValue'].'">'.$row['effect'.$j.'MiscValue'].'</a>)';
                            break;
                        }
                        default:
                        {
                            $spell['effect'][$i]['name'] .= ': '.$spell_aura_names[$row['effect'.$j.'AuraId']];
                            if($row['effect'.$j.'MiscValue'] > 0)
                                $spell['effect'][$i]['name'] .= ' ('.$row['effect'.$j.'MiscValue'].')';
                        }
                    }
                }
                elseif($row['effect'.$j.'AuraId'] > 0)
                    $spell['effect'][$i]['name'] .= ': Unknown Aura ('.$row['effect'.$j.'AuraId'].')';
                // Создает вещь:
                if($row['effect'.$j.'Id'] == 24)
                {
                    $spell['effect'][$i]['item'] = array();
                    $spell['effect'][$i]['item']['entry'] = $row['effect'.$j.'CreateItemId'];
                    $tmpRow = allitemsinfo($spell['effect'][$i]['item']['entry'], 0);
                    $spell['effect'][$i]['item']['name'] = $tmpRow['name'];
                    $spell['effect'][$i]['item']['quality'] = $tmpRow['quality'];
                    $spell['effect'][$i]['item']['count'] = $row['effect'.$j.'BasePoints'] + 1;
                    // Иконка итема, если спелл создает этот итем
                    if(!IsSet($spell['icon']))
                        $spell['icon'] = $tmpRow['iconname'];
                }
                // Создает спелл
                if($row['effect'.$j.'TriggerSpell'] > 0)
                {
                    $spell['effect'][$i]['spell'] = array();
                    $spell['effect'][$i]['spell']['entry'] = $row['effect'.$j.'TriggerSpell'];
                    $spell['effect'][$i]['spell']['name'] = DB::Aowow()->selectCell('SELECT spellname_loc'.User::$localeId.' FROM ?_spell WHERE spellID = ?d LIMIT 1', $spell['effect'][$i]['spell']['entry']);
                    allspellsinfo($spell['effect'][$i]['spell']['entry']);
                }
                $i++;
            }
        }

        // Ð§Ñ‚Ð¾ Ð»ÑƒÑ‚Ð¸Ñ‚ÑÑ Ð¸Ð· ÑÑ‚Ð¾Ð³Ð¾ ÑÐ¿ÐµÐ»Ð»Ð° (Ð´Ð»Ñ effect_id=59 /* Open Lock Item */)
        if(($row['effect1Id'] == 59 || $row['effect2Id'] == 59 || $row['effect3Id'] == 59) &&
           !($spell['contains'] = loot('spell_loot_template', $spell['entry'])))
            unset($spell['contains']);

        if(!IsSet($spell['icon']))
            $spell['icon'] = $row['iconString'];

        // Спеллы с таким же названием
/*
        $seealso = DB::Aowow()->select('
            SELECT *
            FROM ?_spell
            WHERE
                name_loc'.User::$localeId.' = ?
                AND Id <> ?d
                AND (
                            (effect1Id = ?d AND effect1Id!=0)
                            OR (effect2Id = ?d AND effect2Id!=0)
                            OR (effect3Id = ?d AND effect3Id!=0)
                        )
            ',
            $spell['name'],
            $spell['entry'],
            $row['effect1Id'],
            $row['effect2Id'],
            $row['effect3Id']
        );
        if($seealso)
        {
            $spell['seealso'] = array();
            foreach($seealso as $i => $row)
                $spell['seealso'][] = spellinfo2($row);
            unset($seealso);
        }
*/
        // Кто обучает этому спеллу
        $spell['taughtbynpc'] = array();
        // Список тренеров, обучающих нужному спеллу
/*
        $trainers = DB::Aowow()->selectCol('SELECT entry FROM npc_trainer WHERE spell = ?d', $spell['entry']);
        if($trainers)
        {
            $taughtbytrainers = DB::Aowow()->select('
                SELECT ?#, c.entry
                { , name_loc?d AS name_loc, subname_loc'.User::$localeId.' AS subname_loc }
                FROM ?_factiontemplate, creature_template c
                { LEFT JOIN (locales_creature l) ON c.entry = l.entry AND ? }
                WHERE
                    c.entry IN (?a)
                    AND factiontemplateID=faction_A
                ',
                $npc_cols[0],
                (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
                (User::$localeId>0)? 1: DBSIMPLE_SKIP,
                $trainers
            );
            if($taughtbytrainers)
            {
                foreach($taughtbytrainers as $i=>$npcrow)
                    $spell['taughtbynpc'][] = creatureinfo2($npcrow);
                unset($taughtbytrainers);
            }
        }
*/
        // Список книг/рецептов, просто обучающих спеллу
        $spell['taughtbyitem'] = array();
/*
        $taughtbyitem = DB::Aowow()->select('
            SELECT ?#, c.entry
            { , name_loc?d AS name_loc }
            FROM ?_icons, item_template c
            { LEFT JOIN (locales_item l) ON c.entry = l.entry AND ? }
            WHERE
                (spellid_2 = ?d AND spelltrigger_2 = 6)
                AND id=displayid
            ',
            $item_cols[2],
            (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
            (User::$localeId>0)? 1: DBSIMPLE_SKIP,
            $spell['entry']
        );
        if($taughtbyitem)
        {
            foreach($taughtbyitem as $i=>$itemrow)
                $spell['taughtbyitem'][] = iteminfo2($itemrow, 0);
            unset($taughtbyitem);
        }
*/
        // Список спеллов, обучающих этому спеллу:
        $taughtbyspells = DB::Aowow()->selectCol('
            SELECT Id
            FROM ?_spell
            WHERE
                (effect1TriggerSpell = ?d AND effect1id IN (57, 36))
                OR (effect2TriggerSpell = ?d AND effect2id IN (57, 36))
                OR (effect3TriggerSpell = ?d AND effect3id IN (57, 36))
            ',
            $spell['entry'], $spell['entry'], $spell['entry']
        );

        if($taughtbyspells)
        {
            // Список петов, кастующих спелл, обучающий нужному спеллу
            /*
            $taughtbypets = DB::Aowow()->select('
                SELECT ?#, c.entry
                { , name_loc?d AS name_loc, subname_loc'.User::$localeId.' AS subname_loc }
                FROM ?_factiontemplate, creature_template c
                { LEFT JOIN (locales_creature l) ON c.entry = l.entry AND ? }
                WHERE
                    c.entry IN (SELECT entry FROM petcreateinfo_spell WHERE (Spell1 IN (?a)) OR (Spell2 IN (?a)) OR (Spell3 IN (?a)) OR (Spell4 IN (?a)))
                    AND factiontemplateID=faction_A
                ',
                $npc_cols[0],
                (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
                (User::$localeId>0)? 1: DBSIMPLE_SKIP,
                $taughtbyspells, $taughtbyspells, $taughtbyspells, $taughtbyspells
            );
            // Перебираем этих петов
            if($taughtbypets)
            {
                foreach($taughtbypets as $i=>$petrow)
                    $spell['taughtbynpc'][] = creatureinfo2($petrow);
                unset($taughtbypets);
            }
            */

            // Список квестов, наградой за которые является спелл, обучающий нужному спеллу
/*
            $taughtbyquest = DB::Aowow()->select('
                SELECT c.?#
                { , Title_loc?d AS Title_loc }
                FROM quest_template c
                { LEFT JOIN (locales_quest l) ON c.entry = l.entry AND ? }
                WHERE
                    RewSpell IN (?a) OR RewSpellCast IN (?a)
                ',
                $quest_cols[2],
                (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
                (User::$localeId>0)? 1: DBSIMPLE_SKIP,
                $taughtbyspells, $taughtbyspells
            );
            if($taughtbyquest)
            {
                $spell['taughtbyquest'] = array();
                foreach($taughtbyquest as $i=>$questrow)
                    $spell['taughtbyquest'][] = GetQuestInfo($questrow, 0xFFFFFF);
                unset($taughtbyquest);
            }
*/
            // Список НПЦ, кастующих нужный спелл, бла-бла-бла
/*
            $taughtbytrainers = DB::Aowow()->select('
                SELECT ?#, c.entry
                { , name_loc?d AS name_loc, subname_loc'.User::$localeId.' AS subname_loc }
                FROM ?_factiontemplate, creature_template c
                { LEFT JOIN (locales_creature l) ON c.entry = l.entry AND ? }
                WHERE
                    c.entry IN (SELECT entry FROM npc_trainer WHERE spell in (?a))
                    AND factiontemplateID=faction_A
                ',
                $npc_cols[0],
                (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
                (User::$localeId>0)? 1: DBSIMPLE_SKIP,
                $taughtbyspells
            );
            if($taughtbytrainers)
            {
                foreach($taughtbytrainers as $i=>$npcrow)
                    $spell['taughtbynpc'][] = creatureinfo2($npcrow);
                unset($taughtbytrainers);
            }
*/
            // Список книг, кастующих спелл, обучающий нужному спеллу
/*
            $taughtbyitem = DB::Aowow()->select('
                SELECT ?#, c.entry
                { , name_loc?d AS name_loc }
                FROM ?_icons, item_template c
                { LEFT JOIN (locales_item l) ON c.entry = l.entry AND ? }
                WHERE
                    ((spellid_1 IN (?a))
                    OR (spellid_2 IN (?a))
                    OR (spellid_3 IN (?a))
                    OR (spellid_4 IN (?a))
                    OR (spellid_5 IN (?a)))
                    AND id=displayid
                ',
                $item_cols[2],
                (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
                (User::$localeId>0)? 1: DBSIMPLE_SKIP,
                $taughtbyspells, $taughtbyspells, $taughtbyspells, $taughtbyspells, $taughtbyspells
            );
            if($taughtbyitem)
            {
                foreach($taughtbyitem as $i=>$itemrow)
                    $spell['taughtbyitem'][] = iteminfo2($itemrow, 0);
                unset($taughtbyitem);
            }
*/
        }

        // Используется NPC:
/*
        $usedbynpc = DB::Aowow()->select('
            SELECT ?#, c.entry
            { , name_loc?d AS name_loc, subname_loc'.User::$localeId.' AS subname_loc }
            FROM ?_factiontemplate, creature_template c
            { LEFT JOIN (locales_creature l) ON c.entry = l.entry AND ? }
            WHERE
                (spell1 = ?d
                OR spell2 = ?d
                OR spell3 = ?d
                OR spell4 = ?d)
                AND factiontemplateID=faction_A
            ',
            $npc_cols[0],
            (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
            (User::$localeId>0)? 1: DBSIMPLE_SKIP,
            $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry']
        );
        if($usedbynpc)
        {
            $spell['usedbynpc'] = array();
            foreach($usedbynpc as $i=>$row)
                $spell['usedbynpc'][] = creatureinfo2($row);
            unset($usedbynpc);
        }
*/
        // Используется вещями:
/*
        $usedbyitem = DB::Aowow()->select('
            SELECT ?#, c.entry
            { , name_loc?d AS name_loc }
            FROM ?_icons, item_template c
            { LEFT JOIN (locales_item l) ON c.entry = l.entry AND ? }
            WHERE
                (spellid_1 = ?d OR (spellid_2 = ?d AND spelltrigger_2!=6) OR spellid_3 = ?d OR spellid_4 = ?d OR spellid_5 = ?d)
                AND id=displayID
            ',
            $item_cols[2],
            (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
            (User::$localeId>0)? 1: DBSIMPLE_SKIP,
            $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry']
        );
        if($usedbyitem)
        {
            $spell['usedbyitem'] = array();
            foreach($usedbyitem as $i => $row)
                $spell['usedbyitem'][] = iteminfo2($row, 0);
            unset($usedbyitem);
        }
*/
        // Используется наборами вещей:
        $usedbyitemset = DB::Aowow()->select('
            SELECT *
            FROM ?_itemset
            WHERE spell1 = ?d OR spell2 = ?d OR spell3 = ?d OR spell4 = ?d OR spell5 = ?d OR spell6 = ?d OR spell7 = ?d OR spell8 = ?d
            ',
            $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry'], $spell['entry']
        );
        if($usedbyitemset)
        {
            $spell['usedbyitemset'] = array();
            foreach($usedbyitemset as $i => $row)
                $spell['usedbyitemset'][] = itemsetinfo2($row);
            unset($usedbyitemset);
        }

        // Спелл - награда за квест
/*
        $questreward = DB::Aowow()->select('
            SELECT c.?#
            { , Title_loc?d AS Title_loc }
            FROM quest_template c
            { LEFT JOIN (locales_quest l) ON c.entry = l.entry AND ? }
            WHERE
                RewSpell = ?d
                OR RewSpellCast = ?d
            ',
            $quest_cols[2],
            (User::$localeId>0)? User::$localeId: DBSIMPLE_SKIP,
            (User::$localeId>0)? 1: DBSIMPLE_SKIP,
            $spell['entry'], $spell['entry']
        );
        if($questreward)
        {
            $spell['questreward'] = array();
            foreach($questreward as $i => $row)
                $spell['questreward'][] = GetQuestInfo($row, 0xFFFFFF);
            unset($questreward);
        }
*/

        // Проверяем на пустые массивы
        if(!$spell['taughtbyitem'])
            unset($spell['taughtbyitem']);
        if(!$spell['taughtbynpc'])
            unset($spell['taughtbynpc']);

        // Цель критерии
        $rows = DB::Aowow()->select('
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
            User::$localeId,
            User::$localeId,
            array(
                ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET, ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2,
                ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL, ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2,
                ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL
            ),
            $spell['entry'],
            User::$localeId
        );
        if($rows)
        {
            $spell['criteria_of'] = array();
            foreach($rows as $row)
            {
                allachievementsinfo2($row['id']);
                $spell['criteria_of'][] = achievementinfo2($row);
            }
        }

        $smarty->saveCache($cacheKeyPage, $pageData);
    }
    else
    {
        $smarty->updatePageVars(array(
            'subject'   => ucfirst(Lang::$game['spell']),
            'id'        => $id,
            'notFound'  => sprintf(Lang::$main['pageNotFound'], Lang::$game['spell']),
        ));

        $smarty->assign('lang', Lang::$main);
        $smarty->display('404.tpl');

        exit();
    }

}

$smarty->updatePageVars(array(
    'title'     => implode(" - ", $title),
    // 'path'      => "[".implode(", ", $path)."]",
    'path'      => "[0, 1]",
    'tab'       => 0,                                       // for g_initHeader($tab)
    'type'      => 6,                                       // 6:Spell
    'typeid'    => $id,
));
// comments, screenshots, videos
// $smarty->assign('community', CommunityContent::getAll(9, $id));
$smarty->assign('lang', array_merge(Lang::$main, Lang::$spell));
$smarty->assign('data', $pageData);
$smarty->assign('spell', $spell);

// Mysql query execution statistics
$smarty->assign('mysql', DB::Aowow()->getStatistics());

// load the page
$smarty->display('spell.tpl');

?>
