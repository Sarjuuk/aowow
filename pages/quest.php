<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id   = intVal($pageParam);
$_path = [0, 3];

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_QUEST, $_id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_QUEST, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $quest = new QuestList(array(['id', $_id]));
        if ($quest->error)
            die('$WowheadPower.registerQuest(\''.$_id.'\', '.User::$localeId.', {})');

        $x = '$WowheadPower.registerQuest('.$_id.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($quest->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.': \''.$quest->renderTooltip()."'";
        if ($quest->isDaily())
            $x .= ",\n\tdaily: 1";
        $x .= "\n});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $quest = new QuestList(array(['id', $_id]));
    if ($quest->error)
        $smarty->notFound(Lang::$game['quest'], $_id);

    // recreate path
    $_path[] = $quest->getField('cat2');
    if ($_ = $quest->getField('cat1'))
        $_path[] = $_;

    $_name         = $quest->getField('name', true);
    $_level        = $quest->getField('level');
    $_minLevel     = $quest->getField('minLevel');
    $_maxLevel     = $quest->getField('maxLevel');
    $_flags        = $quest->getField('flags');
    $_specialFlags = $quest->getField('specialFlags');
    $_questMoney   = $quest->getField('rewardOrReqMoney');
    $_side         = Util::sideByRaceMask($quest->getField('reqRaceMask'));

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    // event (todo: assign eventData)
    if ($_ = $quest->getField('holidayId'))
    {
        (new WorldEventList(array(['h.id', $_])))->addGlobalsToJscript();
        $infobox[] = Lang::$game['eventShort'].Lang::$colon.'[event='.$_.']';
    }

    // level
    if ($_ = $_level)
        if ($_ > 0)
            $infobox[] = Lang::$game['level'].Lang::$colon.$_;

    // reqlevel
    if ($_ = $_minLevel)
    {
        $lvl =  $_;
        if ($_ = $_maxLevel)
            $lvl .= ' - '.$_;

        $infobox[] = sprintf(Lang::$game['reqLevel'], $lvl);
    }

    // loremaster (i dearly hope those flags cover every case...)
    if ($quest->getField('zoneOrSort') > 0 && !$quest->isRepeatable())
    {
        $conditions = array(
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE],
            ['ac.value1', $quest->getField('zoneOrSort')],
            ['a.faction', $_side, '&']
        );
        $loremaster = new AchievementList($conditions);
        switch ($loremaster->getMatches())
        {
            case 0:
                break;
            case 1:
                $loremaster->addGlobalsToJscript(GLOBALINFO_SELF);
                $infobox[] = Lang::$quest['loremaster'].Lang::$colon.'[achievement='.$loremaster->id.']';
                break;
            default:
                $loremaster->addGlobalsToJscript(GLOBALINFO_SELF);
                $lm = Lang::$quest['loremaster'].Lang::$colon.'[ul]';
                foreach($loremaster->iterate() as $id => $__)
                    $lm .= '[li][achievement='.$id.'][/li]';

                $infobox[] = $lm.'[/ul]';
                break;
        }
    }

    // type (maybe expand uppon?)
    $_ = [];
    if ($_flags & QUEST_FLAG_DAILY)
        $_[] = Lang::$quest['daily'];
    else if ($_flags & QUEST_FLAG_WEEKLY)
        $_[] = Lang::$quest['weekly'];
    else if ($_specialFlags & QUEST_FLAG_SPECIAL_MONTHLY)
        $_[] = Lang::$quest['monthly'];

    if ($t = $quest->getField('type'))
        $_[] = Lang::$quest['questInfo'][$t];

    if ($_)
        $infobox[] = Lang::$game['type'].Lang::$colon.implode(' ', $_);

    // side
    $_ = Lang::$main['side'].lang::$colon;
    switch ($_side)
    {
        case 3: $infobox[] = $_.Lang::$game['si'][3];                                           break;
        case 2: $infobox[] = $_.'[span class=icon-horde]'.Lang::$game['si'][2].'[/span]';       break;
        case 1: $infobox[] = $_.'[span class=icon-alliance]'.Lang::$game['si'][1].'[/span]';    break;
    }

    // races
    if ($_ = Lang::getRaceString($quest->getField('reqRaceMask'), $__, false, $n))
    {
        if ($n)
        {
            $t = $n == 1 ? Lang::$game['race'] : Lang::$game['races'];
            $infobox[] = Util::ucFirst($t).Lang::$colon.$_;
        }
    }

    // classes
    if ($_ = Lang::getClassString($quest->getField('reqClassMask'), false, $n))
    {
        $t = $n == 1 ? Lang::$game['class'] : Lang::$game['classes'];
        $infobox[] = Util::ucFirst($t).Lang::$colon.$_;
    }

    // profession / skill
    if ($_ = $quest->getField('reqSkillId'))
    {
        Util::$pageTemplate->extendGlobalIds(TYPE_SKILL, $_);
        $sk =  '[skill='.$_.']';
        if ($_ = $quest->getField('reqSkillPoints'))
            $sk .= ' ('.$_.')';

        $infobox[] = Lang::$quest['profession'].Lang::$colon.$sk;
    }

    // timer
    if ($_ = $quest->getField('timeLimit'))
        $infobox[] = Lang::$quest['timer'].Lang::$colon.Util::formatTime($_ * 1000);

    $startEnd = DB::Aowow()->select('SELECT * FROM ?_quests_startend WHERE questId = ?d', $_id);

    // start
    $start = '[icon name=quest_start'.($quest->isDaily() ? '_daily' : '').']'.lang::$event['start'].Lang::$colon.'[/icon]';
    $s     = [];
    foreach ($startEnd as $se)
    {
        if ($se['method'] & 0x1)
        {
            Util::$pageTemplate->extendGlobalIds($se['type'], $se['typeId']);
            $s[] = ($s ? '[span=invisible]'.$start.'[/span] ' : $start.' ') .'['.Util::$typeStrings[$se['type']].'='.$se['typeId'].']';
        }
    }

    if ($s)
        $infobox[] = implode('[br]', $s);

    // end
    $end = '[icon name=quest_end'.($quest->isDaily() ? '_daily' : '').']'.lang::$event['end'].Lang::$colon.'[/icon]';
    $e   = [];
    foreach ($startEnd as $se)
    {
        if ($se['method'] & 0x2)
        {
            Util::$pageTemplate->extendGlobalIds($se['type'], $se['typeId']);
            $e[] = ($e ? '[span=invisible]'.$end.'[/span] ' : $end.' ') . '['.Util::$typeStrings[$se['type']].'='.$se['typeId'].']';
        }
    }

    if ($e)
        $infobox[] = implode('[br]', $e);

    // Repeatable
    if ($_flags & QUEST_FLAG_REPEATABLE || $_specialFlags & QUEST_FLAG_SPECIAL_REPEATABLE)
        $infobox[] = Lang::$quest['repeatable'];

    // sharable | not sharable
    $infobox[] = $_flags & QUEST_FLAG_SHARABLE ? Lang::$quest['sharable'] : Lang::$quest['notSharable'];

    // Keeps you PvP flagged
    if ($quest->isPvPEnabled())
        $infobox[] = Lang::$quest['keepsPvpFlag'];

    // difficulty (todo (low): formula unclear. seems to be [minLevel,] -4, -2, (level), +3, +(9 to 15))
    if ($_level > 0)
    {
        $_ = [];
        if ($_minLevel && $_minLevel < $_level - 4)
            $_[] = '[color=q10]'.$_minLevel.'[/color]';     // red

        if (!$_minLevel || $_minLevel < $_level - 2)        // orange
            $_[] = '[color=r1]'.(!$_ && $_minLevel > $_level - 4 ? $_minLevel : $_level - 4).'[/color]';

        $_[] = '[color=r2]'.(!$_ && $_minLevel > $_level - 2 ? $_minLevel : $_level - 2).'[/color]';        // yellow
        $_[] = '[color=r3]'.($_level + 3).'[/color]';       // green
        $_[] = '[color=r4]'.($_level + 3 + ceil(12 * $_level / MAX_LEVEL)).'[/color]';                      // grey   (is about +/-1 level off)

        if ($_)
            $infobox[] = Lang::$game['difficulty'].Lang::$colon.implode('[small] &nbsp;[/small]', $_);
    }

    /**********/
    /* Series */
    /**********/

    $series = [];

    // Quest Chain (are there cases where quests go in parallel?)
    $chain = array(
        array(
            array(
                'side'    => $_side,
                'typeStr' => Util::$typeStrings[TYPE_QUEST],
                'typeId'  => $_id,
                'name'    => $_name,
                '_next'   => $quest->getField('nextQuestIdChain')
            )
        )
    );

    $_ = $chain[0][0];
	while ($_)
	{
		if ($_ = DB::Aowow()->selectRow('SELECT id AS typeId, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, reqRaceMask FROM ?_quests WHERE nextQuestIdChain = ?d', $_['typeId']))
        {
            $n = Util::localizedString($_, 'name');
            array_unshift($chain, array(
                array(
                    'side'    => Util::sideByRaceMask($_['reqRaceMask']),
                    'typeStr' => Util::$typeStrings[TYPE_QUEST],
                    'typeId'  => $_['typeId'],
                    'name'    => strlen($n) > 40 ? substr($n, 0, 40).'…' : $n
                )
            ));
		}
	}

	$_ = end($chain)[0];
	while($_)
	{
		if ($_ = DB::Aowow()->selectRow('SELECT id AS typeId, name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, reqRaceMask, nextQuestIdChain AS _next FROM ?_quests WHERE id = ?d', $_['_next']))
        {
            $n = Util::localizedString($_, 'name');
            array_push($chain, array(
                array(
                    'side'    => Util::sideByRaceMask($_['reqRaceMask']),
                    'typeStr' => Util::$typeStrings[TYPE_QUEST],
                    'typeId'  => $_['typeId'],
                    'name'    => strlen($n) > 40 ? substr($n, 0, 40).'…' : $n,
                    '_next'   => $_['_next'],
                )
            ));
		}
	}

    if (count($chain) > 1)
        $series[] = [$chain, null];


    // todo (low): sensibly merge te following lists into 'series'
    $listGen = function($cnd)
    {
        $chain = [];
        $list  = new QuestList($cnd);
        if ($list->error)
            return null;

        foreach ($list->iterate() as $id => $__)
        {
            $n = $list->getField('name', true);
            $chain[] = array(array(
                'side'    => Util::sideByRaceMask($list->getField('reqRaceMask')),
                'typeStr' => Util::$typeStrings[TYPE_QUEST],
                'typeId'  => $id,
                'name'    => strlen($n) > 40 ? substr($n, 0, 40).'…' : $n
            ));
        }

        return $chain;
    };

    $extraLists = array(
        array(                                              // Requires all of these quests (Quests that you must follow to get this quest)
            'reqQ',
            array(
                'OR',
                ['AND', ['nextQuestId', $_id], ['exclusiveGroup', 0, '<']],
                ['AND', ['id', $quest->getField('prevQuestId')], ['nextQuestIdChain', $_id, '!']]
            )
        ),
        array(                                              // Requires one of these quests (Requires one of the quests to choose from)
            'reqOneQ',
            [['exclusiveGroup', 0, '>'], ['nextQuestId', $_id]]
        ),
        array(                                              // Opens Quests (Quests that become available only after complete this quest (optionally only one))
            'opensQ',
            array(
                'OR',
                ['AND', ['prevQuestId', $_id], ['id', $quest->getField('nextQuestIdChain'), '!']],
                ['id', $quest->getField('nextQuestId')]
            )
        ),
        array(                                              // Closes Quests (Quests that become inaccessible after completing this quest)
            'closesQ',
            [['exclusiveGroup', 0, '!'], ['exclusiveGroup', $quest->getField('exclusiveGroup')], ['id', $_id, '!']]
        ),
        array(                                              // During the quest available these quests (Quests that are available only at run time this quest)
            'enablesQ',
            [['prevQuestId', -$_id]]
        ),
        array(                                              // Requires an active quest (Quests during the execution of which is available on the quest)
            'enabledByQ',
            [['id', -$quest->getField('prevQuestId')]]
        )
    );

    foreach ($extraLists as $el)
        if ($_ = $listGen($el[1]))
            $series[] = [$_, sprintf(Util::$dfnString, Lang::$quest[$el[0].'Desc'], Lang::$quest[$el[0]])];


    /*******************/
    /* Objectives List */
    /*******************/

    $objectiveList = [];

    $srcItemId = $quest->getField('sourceItemId');
    if ($srcItemId)
    {
        $item = new itemList(array(['id', $srcItemId]));
        if (!$item->error)
        {
            $item->addGlobalsToJscript();
            $objectiveList[] = array(
                'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                'id'        => $srcItemId,
                'name'      => $item->getField('name', true),
                'qty'       => $quest->getField('sourceItemCount'),
                'quality'   => $item->getField('quality'),
                'extraText' => '&nbsp;('.Lang::$quest['provided'].')'
            );
        }
    }

    if ($_ = $quest->getField('sourceSpellId'))
    {
        Util::$pageTemplate->extendGlobalIds(TYPE_SPELL, $_);
        $objectiveList[] = array(
            'typeStr'   => Util::$typeStrings[TYPE_SPELL],
            'id'        => $_,
            'name'      => SpellList::getName($_),
            'qty'       => 0,
            'quality'   => '',
            'extraText' => '&nbsp;('.Lang::$quest['provided'].')'
        );
    }

	for ($i = 1; $i < 5; $i++)
	{
        $id     = $quest->getField('reqNpcOrGo'.$i);
        $qty    = $quest->getField('reqNpcOrGoCount'.$i);
        $altTxt = $quest->getField('objectiveText'.$i, true);
		if (!$id || !$qty)
            continue;

		if ($id > 0)
        {
            $proxy = new CreatureList(['OR', ['killCredit1', (int)$id], ['killCredit2', (int)$id]]);
            if (!$proxy->error)
            {
                // todo (low): now do it properly this time!
                $proxyList  = '<a href="javascript:;" onclick="g_disclose($WH.ge(\'npcgroup-'.$id.'\'), this)" class="disclosure-off">'.($altTxt ? $altTxt : CreatureList::getName($id)).((($_specialFlags & QUEST_FLAG_SPECIAL_SPELLCAST) || $altTxt) ? '' : ' '.Lang::$achievement['slain']).'</a>'.($qty > 1 ? ' &nbsp;('.$qty.')' : null);
                $proxyList .= "<div id=\"npcgroup-".$id."\" style=\"display: none\"><div style=\"float: left\">\n<table class=\"iconlist\">\n";
                foreach ($proxy->iterate() as $pId => $__)
                    $proxyList .= '    <tr><th><ul><li><var>&nbsp;</var></li></ul></th><td><a href="?npc='.$pId.'">'.$proxy->getField('name', true)."</a></td></tr>\n";

                // re-add self
                $proxyList .= '    <tr><th><ul><li><var>&nbsp;</var></li></ul></th><td><a href="?npc='.$id.'">'.CreatureList::getName($id)."</a></td></tr>\n";

                $objectiveList[] = ['text' => $proxyList."</table>\n</div></div>"];
            }
            else
            {
                $objectiveList[] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_NPC],
                    'id'        => $id,
                    'name'      => $altTxt ? $altTxt : CreatureList::getName($id),
                    'qty'       => $qty,
                    'quality'   => '',
                    'extraText' => (($_specialFlags & QUEST_FLAG_SPECIAL_SPELLCAST) || $altTxt) ? '' : ' '.Lang::$achievement['slain']
                );
            }
        }
        else
        {
			$objectiveList[] = array(
                'typeStr'   => Util::$typeStrings[TYPE_OBJECT],
                'id'        => -$id,
                'name'      => $altTxt ? $altTxt : GameObjectList::getName(-$id),
                'qty'       => $qty,
                'quality'   => '',
                'extraText' => ''
            );
        }
    }

	for ($i = 1; $i < 7; $i++)
	{
        $id  = $quest->getField('reqItemId'.$i);
        $qty = $quest->getField('reqItemCount'.$i);
		if (!$id || !$qty || $id == $srcItemId)
            continue;

        $item = new itemList(array(['id', $id]));
        if (!$item->error)
        {
            $item->addGlobalsToJscript();
            $objectiveList[] = array(
                'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                'id'        => $id,
                'name'      => $item->getField('name', true),
                'qty'       => $qty,
                'quality'   => $item->getField('quality'),
                'extraText' => ''
            );
        }
	}

    for ($i = 1; $i < 3; $i++)
    {
        $id  = $quest->getField('reqFactionId'.$i);
        $val = $quest->getField('reqFactionValue'.$i);
        if (!$id)
            continue;

        $objectiveList[] = array(
            'typeStr'   => Util::$typeStrings[TYPE_FACTION],
            'id'        => $id,
            'name'      => FactionList::getName($id),
            'qty'       => '',
            'quality'   => '',
            'extraText' => '&nbsp;('.sprintf(Util::$dfnString, $val.' '.Lang::$achievement['points'], Lang::getReputationLevelForPoints($val)).')'
        );
    }

    if ($_questMoney < 0)
        $objectiveList[] = ['text' => Lang::$quest['reqMoney'].Lang::$colon.Util::formatMoney(abs($_questMoney))];

    if ($_ = $quest->getField('reqPlayerKills'))
        $objectiveList[] = ['text' => Lang::$quest['playerSlain'].'&nbsp;('.$_.')'];


    /**********/
    /* Mapper */
    /**********/

    /*
        TODO (GODDAMNIT): jeez..
    */

    // $startend + reqNpcOrGo[1-4]
    $map = [];

    /***********/
    /* Rewards */
    /***********/

    // rewards
    $rewards = [];

    $comp  = $quest->getField('rewardMoneyMaxLevel');
    $money = '';
    if ($_questMoney > 0)
        $money .= Util::formatMoney($_questMoney);
    if ($_questMoney > 0 && $comp > 0)
        $money .= '&nbsp;' . sprintf(Lang::$quest['expConvert'], Util::formatMoney($_questMoney + $comp), MAX_LEVEL);
    else if ($_questMoney <= 0 && $_questMoney + $comp > 0)
        $money .= sprintf(Lang::$quest['expConvert2'], Util::formatMoney($_questMoney + $comp), MAX_LEVEL);

    $rewards['money'] = $money;

    if ($c = @$quest->choices[$_id][TYPE_ITEM])
    {
        $choiceItems = new ItemList(array(['id', array_keys($c)]));
        if (!$choiceItems->error)
        {
            $choiceItems->addGlobalsToJscript();
            foreach ($choiceItems->Iterate() as $id => $__)
            {
                $rewards['choice'][] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                    'id'        => $id,
                    'name'      => $choiceItems->getField('name', true),
                    'quality'   => $choiceItems->getField('quality'),
                    'qty'       => $c[$id],
                    'globalStr' => 'g_items'
                );
            }
        }
    }

    $rewards['items'] = [];                                 // template requires initialization of this var
    if ($r = @$quest->rewards[$_id])
    {
        if (!empty($r[TYPE_ITEM]))
        {
            $rewItems = new ItemList(array(['id', array_keys($r[TYPE_ITEM])]));
            if (!$rewItems->error)
            {
                $rewItems->addGlobalsToJscript();
                foreach ($rewItems->Iterate() as $id => $__)
                {
                    $rewards['items'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_ITEM],
                        'id'        => $id,
                        'name'      => $rewItems->getField('name', true),
                        'quality'   => $rewItems->getField('quality'),
                        'qty'       => $r[TYPE_ITEM][$id],
                        'globalStr' => 'g_items'
                    );
                }
            }
        }

        if (!empty($r[TYPE_CURRENCY]))
        {
            $rewCurr = new CurrencyList(array(['id', array_keys($r[TYPE_CURRENCY])]));
            if (!$rewCurr->error)
            {
                $rewCurr->addGlobalsToJscript();
                foreach ($rewCurr->Iterate() as $id => $__)
                {
                    $rewards['items'][] = array(
                        'typeStr'   => Util::$typeStrings[TYPE_CURRENCY],
                        'id'        => $id,
                        'name'      => $rewCurr->getField('name', true),
                        'quality'   => 1,
                        'qty'       => $r[TYPE_CURRENCY][$id] * ($_side == 2 ? -1 : 1), // toggles the icon
                        'globalStr' => 'g_gatheredcurrencies'
                    );
                }
            }
        }
    }

    $displ = $quest->getField('rewardSpell');
    $cast  = $quest->getField('rewardSpellCast');
    if (!$cast && $displ)
    {
        $cast  = $displ;
        $displ = 0;
    }

    if ($cast || $displ)
    {
        $rewSpells = new SpellList(array(['id', [$displ, $cast]]));
        $rewSpells->addGlobalsToJscript();

        if (User::isInGroup(U_GROUP_STAFF))
        {
            $extra = null;
            if ($_ = $rewSpells->getEntry($displ))
                $extra = sprintf(Lang::$quest['spellDisplayed'], $displ, Util::localizedString($_, 'name'));

            if ($_ = $rewSpells->getEntry($cast))
            {
                $rewards['spells']['extra']  = $extra;
                $rewards['spells']['cast'][] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                    'id'        => $cast,
                    'name'      => Util::localizedString($_, 'name'),
                    'globalStr' => 'g_spells'
                );
            }
        }
        else // check if we have a learn spell
        {
            $teach = [];
            foreach ($rewSpells->iterate() as $id => $__)
                if ($_ = $rewSpells->canTeachSpell())
                    foreach ($_ as $idx)
                        $teach[$rewSpells->getField('effect'.$idx.'TriggerSpell')] = $id;

            if ($_ = $rewSpells->getEntry($displ))
            {
                $rewards['spells']['extra'] = null;
                $rewards['spells'][$teach ? 'learn' : 'cast'][] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                    'id'        => $displ,
                    'name'      => Util::localizedString($_, 'name'),
                    'globalStr' => 'g_spells'
                );
            }
            else if (($_ = $rewSpells->getEntry($cast)) && !$teach)
            {
                $rewards['spells']['extra']  = null;
                $rewards['spells']['cast'][] = array(
                    'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                    'id'        => $cast,
                    'name'      => Util::localizedString($_, 'name'),
                    'globalStr' => 'g_spells'
                );
            }
            else
            {
                $taught = new SpellList(array(['id', array_keys($teach)]));
                if (!$taught->error)
                {
                    $taught->addGlobalsToJscript();
                    $rewards['spells']['extra']  = null;
                    foreach ($taught->iterate() as $id => $__)
                    {
                        $rewards['spells']['learn'][] = array(
                            'typeStr'   => Util::$typeStrings[TYPE_SPELL],
                            'id'        => $id,
                            'name'      => $taught->getField('name', true),
                            'globalStr' => 'g_spells'
                        );
                    }
                }
            }
        }
    }

    // gains
    $gains = [];

    if ($_ = $quest->getField('rewardXP'))
        $gains['xp'] = $_;

    if ($_ = $quest->getField('rewardTalents'))
        $gains['tp'] = $_;


    for ($i = 1; $i < 6; $i++)
    {
        $fac = $quest->getField('rewardFactionId'.$i);
        $qty = $quest->getField('rewardFactionValue'.$i);
        if (!$fac || !$qty)
            continue;

        $gains['rep'][] = array(
            'qty'  => $qty,
            'id'   => $fac,
            'name' => FactionList::getName($fac)
        );
    }

    if ($_ = (new TitleList(array(['id', $quest->getField('rewardTitleId')])))->getHtmlizedName())
        $gains['title'] = sprintf(Lang::$quest['theTitle'], $_);

    // reward mail
    $mail     = [];
	$relTabs  = [];
    if ($_ = $quest->getField('rewardMailTemplateId'))
    {
        $delay  = $quest->getField('rewardMailDelay');
        $letter = DB::Aowow()->selectRow('SELECT * FROM ?_mailTemplate WHERE id = ?d', $_);

        $mail = array(
            'delay'   => $delay  ? sprintf(Lang::$quest['mailIn'], Util::formatTime($delay * 1000)) : null,
            'text'    => $letter ? Util::parseHtmlText(Util::localizedString($letter, 'text'))      : null,
            'subject' => Util::parseHtmlText(Util::localizedString($letter, 'subject'))
        );

        $extraCols = ['Listview.extraCols.percent'];
        if ($loot = Util::handleLoot(LOOT_MAIL, $_, User::isInGroup(U_GROUP_STAFF), $extraCols))
        {
            $relTabs[] = array(
                'file'   => 'item',
                'data'   => $loot,
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'name'        => '[Mail Attachments]',
                    'id'          => 'mail-attachments',
                    'extraCols'   => "$[".implode(', ', array_unique($extraCols))."]",
                    'hiddenCols'  => "$".json_encode(['side', 'slot', 'reqlevel'])
                ]
            );
        }
    }

    /****************/
    /* Main Content */
    /****************/

    // menuId 3: Quest    g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page'    => array(
            'title'         => $_name.' - '.Util::ucFirst(Lang::$game['quest']),
            'name'          => $_name,
            'path'          => json_encode($_path, JSON_NUMERIC_CHECK),
            'tab'           => 0,
            'type'          => TYPE_QUEST,
            'typeId'        => $_id,
            'objectives'    => $quest->parseText('objectives', false),
            'details'       => $quest->parseText('details', false),
            'offerReward'   => $quest->parseText('offerReward', false),
            'requestItems'  => $quest->parseText('requestItems', false),
            'completed'     => $quest->parseText('completed', false),
            'end'           => $quest->parseText('end', false),
            'suggestedPl'   => $quest->getField('suggestedPlayers'),
            'infobox'       => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
            'series'        => $series,
            'objectiveList' => $objectiveList,
            'rewards'       => $rewards,
            'gains'         => $gains,
            'mail'          => $mail,
            // 'map'    => array(
                // 'data' => ['zone' => $_id],
                // 'som'  => json_encode($som, JSON_NUMERIC_CHECK)
            // ),
            'reqJS'         => array(
                // $map ? STATIC_URL.'/js/Mapper.js' : null
            ),
            'reqCSS'        => array(
                ['path' => STATIC_URL.'/css/Book.css'],
                // $map ? ['path' => STATIC_URL.'/css/Mapper.css'] : null,
                // $map ? ['path' => STATIC_URL.'/css/Mapper_ie6.css', 'ieCond' => 'lte IE 6'] : null
            ),
            'redButtons'    => array(
                BUTTON_LINKS   => ['color' => 'ffffff00', 'linkId' => 'quest:'.$_id.':'.$_level.''],
                BUTTON_WOWHEAD => true
            )
        ),
        'relTabs' => $relTabs
    );

    if ($_flags & QUEST_FLAG_UNAVAILABLE || $quest->getField('cuFlags') & CUSTOM_EXCLUDE_FOR_LISTVIEW)
        $pageData['page']['unavailable'] = true;

    if ($_ = $quest->getField('reqMinRepFaction'))
    {
        $val = $quest->getField('reqMinRepValue');
        $pageData['page']['reqMinRep'] = sprintf(Lang::$quest['reqRepWith'], $_, FactionList::getName($_), Lang::$quest['reqRepMin'], sprintf(Util::$dfnString, $val.' '.Lang::$achievement['points'], Lang::getReputationLevelForPoints($val)));
    }

    if ($_ = $quest->getField('reqMaxRepFaction'))
    {
        $val = $quest->getField('reqMaxRepValue');
        $pageData['page']['reqMaxRep'] = sprintf(Lang::$quest['reqRepWith'], $_, FactionList::getName($_), Lang::$quest['reqRepMax'], sprintf(Util::$dfnString, $val.' '.Lang::$achievement['points'], Lang::getReputationLevelForPoints($val)));
    }


    /**************/
    /* Extra Tabs */
    /**************/

    // tab: see also
    $seeAlso = new QuestList(array(['name_loc'.User::$localeId, '%'.$_name.'%'], ['id', $_id, '!']));
    if (!$seeAlso->error)
    {
        $seeAlso->addGlobalsToJScript();
        $pageData['relTabs'][] = array(
            'file'   => 'quest',
            'data'   => $seeAlso->getListviewData(),
            'params' => [
                'tabs'       => '$tabsRelated',
                'name'       => '$LANG.tab_seealso',
                'id'         => 'see-also'
            ]
        );
    }

    // tab: criteria of
    $criteriaOf = new AchievementList(array(['ac.type', ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST], ['ac.value1', $_id]));
    if (!$criteriaOf->error)
    {
        $criteriaOf->addGlobalsToJScript();
        $pageData['relTabs'][] = array(
            'file'   => 'achievement',
            'data'   => $criteriaOf->getListviewData(),
            'params' => [
                'tabs'       => '$tabsRelated',
                'name'       => '$LANG.tab_criteriaof',
                'id'         => 'criteria-of'
            ]
        );
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_QUEST, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$item, Lang::$achievement, Lang::$npc, Lang::$quest, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('quest.tpl');

?>
