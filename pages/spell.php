<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_SPELL, $_id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_SPELL, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $spell = new SpellList(array(['s.id', $_id]));
        if ($spell->error)
            die('$WowheadPower.registerSpell('.$_id.', '.User::$localeId.', {});');

        $x  = '$WowheadPower.registerSpell('.$_id.', '.User::$localeId.", {\n";
        $pt = [];
        if ($n = $spell->getField('name', true))
            $pt[] = "\tname_".User::$localeString.": '".Util::jsEscape($n)."'";
        if ($i = $spell->getField('iconString'))
            $pt[] = "\ticon: '".urlencode($i)."'";
        if ($tt = $spell->renderTooltip())
        {
            $pt[] = "\ttooltip_".User::$localeString.": '".Util::jsEscape($tt[0])."'";
            $pt[] = "\tspells_".User::$localeString.": ".json_encode($tt[1], JSON_UNESCAPED_UNICODE);
        }
        if ($btt = $spell->renderBuff())
        {
            $pt[] = "\tbuff_".User::$localeString.": '".Util::jsEscape($btt[0])."'";
            $pt[] = "\tbuffspells_".User::$localeString.": ".json_encode($btt[1], JSON_UNESCAPED_UNICODE);;
        }
        $x .= implode(",\n", $pt)."\n});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $spell = new SpellList(array(['s.id', $_id]));
    if ($spell->error)
        $smarty->notFound(Lang::$game['spell']);

    $spell->addGlobalsToJScript($smarty, GLOBALINFO_ANY);

    $_cat = $spell->getField('typeCat');
    $path = [0, 1, $_cat];
    $l    = [null, 'A', 'B', 'C'];

    // reconstruct path / title
    switch($_cat)
    {
        case  -2:
        case   7:
        case -13:
            $cl = $spell->getField('reqClassMask');
            $i   = 1;

            while ($cl > 0)
            {
                if ($cl & (1 << ($i - 1)))
                {
                    $path[] = $i;
                    break;
                }
                $i++;
            }

            if ($_cat == -13)
            {
                $path[] = ($spell->getField('cuFlags') & (SPELL_CU_GLYPH_MAJOR | SPELL_CU_GLYPH_MINOR)) >> 6;
                break;
            }
        case   9:
        case  -3:
        case  11:
            $path[] = $spell->getField('skillLines')[0];

            if ($_cat == 11)
                if ($_ = $spell->getField('reqSpellId'))
                    $path[] = $_;

            break;
        case -11:
            foreach (SpellList::$skillLines as $line => $skills)
                if (in_array($spell->getField('skillLines')[0], $skills))
                    $path[] = $line;
            break;
        case  -7:                                           // only spells unique in skillLineAbility will always point to the right skillLine :/
            $_ = $spell->getField('cuFlags');
            if ($_ & SPELL_CU_PET_TALENT_TYPE0)
                $path[] = 411;                              // Ferocity
            else if ($_ & SPELL_CU_PET_TALENT_TYPE1)
                $path[] = 409;                              // Tenacity
            else if ($_ & SPELL_CU_PET_TALENT_TYPE2)
                $path[] = 410;                              // Cunning
    }

    // has difficulty versions of itself
    $difficulties = DB::Aowow()->selectRow(
        'SELECT     normal10 AS "0",
                    normal25 AS "1",
                    heroic10 AS "2",
                    heroic25 AS "3"
         FROM       ?_spelldifficulty
         WHERE      normal10 = ?d OR
                    normal25 = ?d OR
                    heroic10 = ?d OR
                    heroic25 = ?d',
        $_id, $_id, $_id, $_id
    );

   // returns self or firstRank
   $firstRank = DB::Aowow()->selectCell(
       'SELECT      IF(s1.rankId <> 1 AND s2.id, s2.id, s1.id)
        FROM        ?_spell s1
        LEFT JOIN   ?_spell s2
            ON      s1.SpellFamilyId     = s2.SpelLFamilyId AND
                    s1.SpellFamilyFlags1 = s2.SpelLFamilyFlags1 AND
                    s1.SpellFamilyFlags2 = s2.SpellFamilyFlags2 AND
                    s1.SpellFamilyFlags3 = s2.SpellFamilyFlags3 AND
                    s1.name_loc0 = s2.name_loc0 AND
                    s2.RankId = 1
        WHERE       s1.id = ?d',
        $_id
    );

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];

    if (!in_array($_cat, [-5, -6]))                         // not mount or vanity pet
    {
        if ($_ = $spell->getField('talentLevel'))           // level
            $infobox[] = '[li]'.(in_array($_cat, [-2, 7, -13]) ? sprintf(Lang::$game['reqLevel'], $_) : Lang::$game['level'].Lang::$colon.$_).'[/li]';
        else if ($_ = $spell->getField('spellLevel'))
            $infobox[] = '[li]'.(in_array($_cat, [-2, 7, -13]) ? sprintf(Lang::$game['reqLevel'], $_) : Lang::$game['level'].Lang::$colon.$_).'[/li]';
    }

    if ($mask = $spell->getField('reqRaceMask'))            // race
    {
        $bar = [];
        for ($i = 0; $i < 11; $i++)
            if ($mask & (1 << $i))
                $bar[] = (!fMod(count($bar) + 1, 3) ? '\n' : null).'[race='.($i + 1).']';

        $t = count($bar) == 1 ? Lang::$game['race'] : Lang::$game['races'];
        $infobox[] = '[li]'.Util::ucFirst($t).Lang::$colon.implode(', ', $bar).'[/li]';
    }

    if ($mask = $spell->getField('reqClassMask'))           // class
    {
        $bar = [];
        for ($i = 0; $i < 11; $i++)
            if ($mask & (1 << $i))
                $bar[] = (!fMod(count($bar) + 1, 3) ? '\n' : null).'[class='.($i + 1).']';

        $t = count($bar) == 1 ? Lang::$game['class'] : Lang::$game['classes'];
        $infobox[] = '[li]'.Util::ucFirst($t).Lang::$colon.implode(', ', $bar).'[/li]';
    }

    if ($_ = $spell->getField('spellFocusObject'))          // spellFocus
    {
        $bar = DB::Aowow()->selectRow('SELECT * FROM ?_spellFocusObject WHERE id = ?d', $_);
        $focus = new GameObjectList(array(['type', 8], ['data0', $_], 1));
        $infobox[] = '[li]'.Lang::$game['requires2'].' '.($focus->error ? Util::localizedString($bar, 'name') : '[url=?object='.$focus->id.']'.Util::localizedString($bar, 'name').'[/url]').'[/li]';
    }

    if (in_array($_cat, [9, 11]))                           // primary & secondary trades
    {
        // skill
        if ($_ = $spell->getField('skillLines')[0])
        {
            $rSkill = new SkillList(array(['id', $_]));
            if (!$rSkill->error)
            {
                $rSkill->addGlobalsToJScript($smarty);

                $bar = sprintf(Lang::$game['requires'], '[skill='.$rSkill->id.']');
                if ($_ = $spell->getField('learnedAt'))
                    $bar .= ' ('.$_.')';

                $infobox[] = '[li]'.$bar.'[/li]';
            }
        }

        // specialization
        if ($_ = $spell->getField('reqSpellId'))
        {
            $rSpell = new SpellList(array(['id', $_]));
            if (!$rSpell->error)
            {
                $rSpell->addGlobalsToJScript($smarty);
                $infobox[] = '[li]'.Lang::$game['requires2'].' [spell='.$rSpell->id.'][/li]';
            }
        }

        // difficulty
        if ($_ = $spell->getColorsForCurrent())
        {
            $bar = [];
            for ($i = 0; $i < 4; $i++)
                if ($_[$i])
                    $bar[] = '[color=r'.($i + 1).']'.$_[$i].'[/color]';

            $infobox[] = '[li]'.Lang::$game['difficulty'].Lang::$colon.implode(' ', $bar).'[/li]';
        }
    }

    // accquisition..
    if ($_ = @$spell->sources[$spell->id])
    {
        if (array_key_exists(10, $_))                       // ..starter spell
            $infobox[] = '[li]'.Lang::$spell['starter'].'[/li]';
        else if (array_key_exists(7, $_))                   // ..discovery
            $infobox[] = '[li]'.Lang::$spell['discovered'].'[/li]';
    }

    // training cost
    if ($cost = DB::Aowow()->selectCell('SELECT spellcost FROM npc_trainer WHERE spell = ?d', $spell->id))
        $infobox[] = '[li]'.Lang::$spell['trainingCost'].Lang::$colon.'[money='.$cost.'][/li]';

    // used in mode
    foreach ($difficulties as $n => $id)
        if ($id == $_id)                                    // "Mode" seems to be multilingual acceptable
            $infobox[] = '[li]Mode'.Lang::$colon.Lang::$game['modes'][$n].'[/li]';


    /****************/
    /* Main Content */
    /****************/

    // chain reagents by method of accquisition
    $reagentResult     = [];
    $enhanced          = false;
    $reagents          = $spell->getReagentsForCurrent();
    $appendReagentItem = function(&$reagentResult, $_iId, $_qty, $_mult, $_level, $_path, $alreadyUsed) use (&$appendCreateSpell)
    {
        if (in_array($_iId, $alreadyUsed))
            return false;

        $item = DB::Aowow()->selectRow('
            SELECT  name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, id, iconString, quality,
            IF ( (spellId1 > 0 AND spellCharges1 < 0) OR
                 (spellId2 > 0 AND spellCharges2 < 0) OR
                 (spellId3 > 0 AND spellCharges3 < 0) OR
                 (spellId4 > 0 AND spellCharges4 < 0) OR
                 (spellId5 > 0 AND spellCharges5 < 0), 1, 0) AS consumed
            FROM    ?_items
            WHERE   id = ?d',
            $_iId
        );

        if (!$item)
            return false;

        Util::$pageTemplate->extendGlobalIds(TYPE_ITEM, $item['id']);

        $_level++;

        if ($item['consumed'])
            $_qty++;

        $data = array(
            'type'    => TYPE_ITEM,
            'typeId'  => $item['id'],
            'typeStr' => Util::$typeStrings[TYPE_ITEM],
            'quality' => $item['quality'],
            'name'    => Util::localizedString($item, 'name'),
            'icon'    => $item['iconString'],
            'qty'     => $_qty * $_mult,
            'path'    => $_path.'.'.TYPE_ITEM.'-'.$item['id'],
            'level'   => $_level
        );

        $idx = count($reagentResult);
        $reagentResult[] = $data;
        $alreadyUsed[]   = $item['id'];

        if (!$appendCreateSpell($reagentResult, $item['id'], $data['qty'], $data['level'], $data['path'], $alreadyUsed))
            $reagentResult[$idx]['final'] = true;

        return true;
    };
    $appendCreateSpell = function(&$reagentResult, $_iId, $_qty, $_level, $_path, $alreadyUsed) use (&$appendReagentItem)
    {
        $_level++;
        // when results are found executes in <10ms
        // when no results are found executes in ~0.35sec
        // dafuque?!
        // ""solution"": index effect1Id and effect1CreateItemId and pray, that tradeSpells only use the first index  >.<
        $spells = DB::Aowow()->select('
            SELECT  reagent1,      reagent2,      reagent3,      reagent4,      reagent5,      reagent6,      reagent7,      reagent8,
                    reagentCount1, reagentCount2, reagentCount3, reagentCount4, reagentCount5, reagentCount6, reagentCount7, reagentCount8,
                    name_loc0,     name_loc2,     name_loc3,     name_loc6,     name_loc8,
                    id AS ARRAY_KEY, iconString
            FROM    ?_spell
            WHERE   (effect1CreateItemId = ?d AND effect1Id = 24)',// OR
                    // (effect2CreateItemId = ?d AND effect2Id = 24) OR
                    // (effect3CreateItemId = ?d AND effect3Id = 24)',
            $_iId//, $_iId, $_iId
        );

        if (!$spells)
            return false;

        $didAppendSomething = false;
        foreach ($spells as $sId => $row)
        {
            if (in_array(-$sId, $alreadyUsed))
                continue;

            Util::$pageTemplate->extendGlobalIds(TYPE_SPELL, $sId);

            $data = array(
                'type'    => TYPE_SPELL,
                'typeId'  => $sId,
                'typeStr' => Util::$typeStrings[TYPE_SPELL],
                'name'    => Util::localizedString($row, 'name'),
                'icon'    => $row['iconString'],
                'qty'     => $_qty,
                'path'    => $_path.'.'.TYPE_SPELL.'-'.$sId,
                'level'   => $_level,
            );

            $reagentResult[] = $data;
            $_aU   = $alreadyUsed;
            $_aU[] = -$sId;

            $hasUnusedReagents = false;
            for ($i = 1; $i < 9; $i++)
            {
                if ($row['reagent'.$i] <= 0 || $row['reagentCount'.$i] <= 0)
                    continue;

                if ($appendReagentItem($reagentResult, $row['reagent'.$i], $row['reagentCount'.$i], $data['qty'], $data['level'], $data['path'], $_aU))
                {
                    $hasUnusedReagents  = true;
                    $didAppendSomething = true;
                }
            }

            if (!$hasUnusedReagents)                        // no reagents were added, remove spell from result set
                array_pop($reagentResult);
        }

        return $didAppendSomething;
    };

    if ($reagents)
    {

        foreach ($spell->relItems->iterate() as $iId => $__)
        {
            if (!in_array($iId, array_keys($reagents)))
                continue;

            $data = array(
                'type'    => TYPE_ITEM,
                'typeId'  => $iId,
                'typeStr' => Util::$typeStrings[TYPE_ITEM],
                'quality' => $spell->relItems->getField('quality'),
                'name'    => $spell->relItems->getField('name', true),
                'icon'    => $spell->relItems->getField('iconString'),
                'qty'     => $reagents[$iId][1],
                'path'    => TYPE_ITEM.'-'.$iId,            // id of the html-element
                'level'   => 0                              // depths in array, used for indentation
            );

            $idx = count($reagentResult);
            $reagentResult[] = $data;

            // start with self and current original item in usedEntries (spell < 0; item > 0)
            if ($appendCreateSpell($reagentResult, $iId, $data['qty'], 0, $data['path'], [-$_id, $iId]))
                $enhanced = true;
            else
                $reagentResult[$idx]['final'] = true;
        }
    }

    // increment all indizes (by prepending null and removing it again)
    array_unshift($reagentResult, null);
    unset($reagentResult[0]);

    $pageData = array(
        'title'   => $spell->getField('name', true),
        'path'    => json_encode($path, JSON_NUMERIC_CHECK),
        'infobox' => $infobox,
        'relTabs' => [],
        'buttons' => array(
            BUTTON_LINKS   => ['color' => 'ff71d5ff', 'linkId' => Util::$typeStrings[TYPE_SPELL].':'.$_id],
            BUTTON_VIEW3D  => false,
            BUTTON_WOWHEAD => true
        ),
        'page'    => array(
            'scaling'   => '',
            'powerCost' => $spell->createPowerCostForCurrent(),
            'castTime'  => $spell->createCastTimeForCurrent(false, false),
            'tools'     => $spell->getToolsForCurrent(),
            'reagents'  => [$enhanced, $reagentResult],
            'name'      => $spell->getField('name', true),
            'icon'      => $spell->getField('iconString'),
            'stack'     => $spell->getField('stackAmount'),
            'level'     => $spell->getField('spellLevel'),
            'rangeName' => $spell->getField('rangeText', true),
            'range'     => $spell->getField('rangeMaxHostile'),
            'gcd'       => Util::formatTime($spell->getField('startRecoveryTime')),
            'gcdCat'    => "[NYI]",
            'school'    => User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Util::asHex($spell->getField('schoolMask')), Lang::getMagicSchools($spell->getField('schoolMask'))) : Lang::getMagicSchools($spell->getField('schoolMask')),
            'dispel'    => Lang::$game['dt'][$spell->getField('dispelType')],
            'mechanic'  => Lang::$game['me'][$spell->getField('mechanic')],
        )
    );

    if ($spell->getField('attributes2') & 0x80000)
        $pageData['page']['stances'] = Lang::getStances($spell->getField('stanceMask'));

    if (($_ = $spell->getField('recoveryTime')) && $_ > 0)
        $pageData['page']['cooldown'] = Util::formatTime($_);

    if (($_ = $spell->getField('duration')) && $_ > 0)
        $pageData['page']['duration'] = Util::formatTime($_);

    // minRange exists..  prepend
    if ($_ = $spell->getField('rangeMinHostile'))
        $pageData['page']['range'] = $_.' - '.$pageData['page']['range'];

    // parse itemClass & itemSubClassMask
    $class    = $spell->getField('equippedItemClass');
    $subClass = $spell->getField('equippedItemSubClassMask');
    $invType  = $spell->getField('equippedItemInventoryTypeMask');

    if ($class > 0 && $subClass > 0)
    {
        $title = ['Class: '.$class, 'SubClass: '.Util::asHex($subClass)];
        $text  = Lang::getRequiredItems($class, $subClass, false);

        if ($invType)
        {
            // remap some duplicated strings            'Off Hand' and 'Shield' are never used simultaneously
            if ($invType & (1 << INVTYPE_ROBE))         // Robe => Chest
            {
                $invType &= ~(1 << INVTYPE_ROBE);
                $invType &=  (1 << INVTYPE_CHEST);
            }

            if ($invType & (1 << INVTYPE_RANGEDRIGHT))  // Ranged2 => Ranged
            {
                $invType &= ~(1 << INVTYPE_RANGEDRIGHT);
                $invType &=  (1 << INVTYPE_RANGED);
            }

            $_ = [];
            $strs = Lang::$item['inventoryType'];
            foreach ($strs as $k => $str)
                if ($invType & 1 << $k && $str)
                    $_[] = $str;

            $title[] = Lang::$item['slot'].Lang::$colon.Util::asHex($invType);
            $text   .= ' '.Lang::$spell['_inSlot'].Lang::$colon.implode(', ', $_);
        }

        $pageData['page']['items'] = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, implode(' | ', $title), $text) : $text;
    }

    // prepare Tools
    foreach ($pageData['page']['tools'] as $k => $tool)
    {
        if (isset($tool['itemId']))                         // Tool
            $pageData['page']['tools'][$k]['url'] = '?item='.$tool['itemId'];
        else                                                // ToolCat
        {
                $pageData['page']['tools'][$k]['quality'] = ITEM_QUALITY_HEIRLOOM - ITEM_QUALITY_NORMAL;
                $pageData['page']['tools'][$k]['url']     = '?items&filter=cr=91;crs='.$tool['id'].';crv=0';
        }
    }

    // spell scaling
    $scaling = array_merge(
        array(
            'directSP' => -1,
            'dotSP'    => -1,
            'directAP' =>  0,
            'dotAP'    =>  0
        ),
        (array)DB::Aowow()->selectRow('SELECT direct_bonus AS directSP, dot_bonus AS dotSP, ap_bonus AS directAP, ap_dot_bonus AS dotAP FROM spell_bonus_data WHERE entry = ?d', $firstRank)
    );

    foreach ($scaling as $k => $v)
    {
        // only calculate for class/pet spells
        if ($v != -1 || !in_array($spell->getField('typeCat'), [-2, -3, -7, 7]))
            continue;

        if (!$spell->isDamagingSpell() || $spell->isHealingSpell())
        {
            $scaling[$k] = 0;
            continue;
        }

        // no known calculation for physical abilities
        if ($k == 'directAP' || $k == 'dotAP')
            continue;

        // dont use spellPower to scale physical Abilities
        if ($spell->getField('schoolMask') == 0x1 && ($k == 'directSP' || $k == 'dotSP'))
            continue;

        $isDOT = false;
        $pMask = $spell->periodicEffectsMask();

        if ($k == 'dotSP' || $k == 'dotAP')
        {
            if ($pMask)
                $isDOT = true;
            else
                continue;
        }
        else                                                // if all used effects are periodic, dont calculate direct component
        {
            $bar = true;
            for ($i = 1; $i < 4; $i++)
            {
                if (!$spell->getField('effect'.$i.'Id'))
                    continue;

                if ($pMask & 1 << ($i - 1))
                    continue;

                $bar = false;
            }

            if ($bar)
                continue;
        }

        // Damage over Time spells bonus calculation
        $dotFactor = 1.0;
        if ($isDOT)
        {
            $dotDuration = $spell->getField('duration');
            // 200% limit
            if ($dotDuration > 0)
            {
                if ($dotDuration > 30000)
                    $dotDuration = 30000;
                if (!$spell->isChanneledSpell())
                    $dotFactor = $dotDuration / 15000;
            }
        }

        // Distribute Damage over multiple effects, reduce by AoE
        $castingTime = $spell->getCastingTimeForBonus($isDOT);

        // 50% for damage and healing spells for leech spells from damage bonus and 0% from healing
        for ($j = 1; $j < 4; ++$j)
        {
            //  SPELL_EFFECT_HEALTH_LEECH            || SPELL_AURA_PERIODIC_LEECH
            if ($spell->getField('effectId'.$j) == 9 || $spell->getField('effect'.$j.'AuraId') == 53)
            {
                $castingTime /= 2;
                break;
            }
        }

        if ($spell->isHealingSpell())
            $castingTime *= 1.88;

        if ($spell->getField('schoolMask') != 0x1)          // SPELL_SCHOOL_MASK_NORMAL
            $scaling[$k] = ($castingTime / 3500.0) * $dotFactor;
        else
            $scaling[$k] = 0;                               // would be 1 ($dotFactor), but we dont want it to be displayed
    }

    foreach ($scaling as $k => $v)
        if ($v > 0)
            $pageData['page']['scaling'] .= sprintf(Lang::$spell['scaling'][$k], $v * 100).'<br>';

    // proc data .. maybe use more information..?
    $procData = DB::Aowow()->selectRow('SELECT IF(ppmRate > 0, -ppmRate, customChance) AS chance, cooldown FROM world.spell_proc_event WHERE entry = ?d', $_id);
    if (empty($procData['chance']))
        $procData['chance'] = $spell->getField('procChance');

    if (!isset($procData['cooldown']))
        $procData['cooldown'] = 0;

    // Iterate through all effects:
    $pageData['page']['effect'] = [];

    $spellIdx = array_unique(array_merge($spell->canTriggerSpell(), $spell->canTeachSpell()));
    $itemIdx  = $spell->canCreateItem();

    for ($i = 1; $i < 4; $i++)
    {
        if ($spell->getField('effect'.$i.'Id') <= 0)
            continue;

        $effId   = (int)$spell->getField('effect'.$i.'Id');
        $effMV   = (int)$spell->getField('effect'.$i.'MiscValue');
        $effBP   = (int)$spell->getField('effect'.$i.'BasePoints');
        $effDS   = (int)$spell->getField('effect'.$i.'DieSides');
        $effRPPL =      $spell->getField('effect'.$i.'RealPointsPerLevel');
        $effAura = (int)$spell->getField('effect'.$i.'AuraId');
        $foo     = &$pageData['page']['effect'][];

        // Icons:
        // .. from item
        if (in_array($i, $itemIdx))
        {
            $_ = $spell->getField('effect'.$i.'CreateItemId');
            foreach ($spell->relItems->iterate() as $itemId => $__)
            {
                if ($itemId != $_)
                    continue;

                $foo['icon'] = array(
                    'id'      => $spell->relItems->id,
                    'name'    => $spell->relItems->getField('name', true),
                    'quality' => $spell->relItems->getField('quality'),
                    'count'   => $effDS + $effBP,
                    'icon'    => $spell->relItems->getField('iconString')
                );
            }

            if ($effDS > 1)
                $foo['icon']['count'] = "'".($effBP + 1).'-'.$foo['icon']['count']."'";
        }
        // .. from spell
        else if (in_array($i, $spellIdx))
        {
            $_ = $spell->getField('effect'.$i.'TriggerSpell');
            if (!$_)
                $_ = $spell->getField('effect'.$i.'MiscValue');

            $trig = new SpellList(array(['s.id', (int)$_]));

            $foo['icon'] = array(
                'id'    => $_,
                'name'  => $trig->error ? Util::ucFirst(Lang::$game['spell']).' #'.$_ : $trig->getField('name', true),
                'count' => 0
            );

            $trig->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
        }

        // Effect Name
        $foo['name'] = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, 'EffectId: '.$effId, Util::$spellEffectStrings[$effId]) : Util::$spellEffectStrings[$effId];

        if ($spell->getField('effect'.$i.'RadiusMax') > 0)
            $foo['radius'] = $spell->getField('effect'.$i.'RadiusMax');

        if (($effBP + $effDS) && !($itemIdx && $spell->relItems && !$spell->relItems->error) && (!in_array($i, $spellIdx) || in_array($effAura, [225, 227])))
            $foo['value'] = ($effDS != 1 ? ($effBP + 1).Lang::$game['valueDelim'] : null).($effBP + $effDS);

        if ($effRPPL != 0)
            $foo['value'] = (isset($foo['value']) ? $foo['value'] : '0').sprintf(Lang::$spell['costPerLevel'], $effRPPL);

        if ($spell->getField('effect'.$i.'Periode') > 0)
            $foo['interval'] = Util::formatTime($spell->getField('effect'.$i.'Periode'));

        if ($_ = $spell->getField('effect'.$i.'Mechanic'))
            $foo['mechanic'] = Lang::$game['me'][$_];

        if ($procData['chance'] && $procData['chance'] < 100)
            if (in_array($i, $spell->canTriggerSpell()))
                $foo['procData'] = array(
                    $procData['chance'],
                    $procData['cooldown'] ? Util::formatTime($procData['cooldown'] * 1000, true) : null
                );

        // parse masks and indizes
        switch ($effId)
        {
            case 8:                                         // Power Drain
            case 30:                                        // Energize
            case 137:                                       // Energize Pct
                $_ = @Lang::$spell['powerTypes'][$effMV];
                if ($_ && User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else if (!$_)
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 16:                                        // QuestComplete
                if ($_ = QuestList::getName($effMV))
                    $foo['name'] .= Lang::$colon.'(<a href="?quest='.$effMV.'">'.$_.'</a>)';
                else
                    $foo['name'] .= Lang::$colon.Util::ucFirst(Lang::$game['quest']).' #'.$effMV;;
                break;
            case 28:                                        // Summon
            case 75:                                        // Summon Totem
            case 87:                                        // Summon Totem (slot 1)
            case 88:                                        // Summon Totem (slot 2)
            case 89:                                        // Summon Totem (slot 3)
            case 90:                                        // Summon Totem (slot 4)
                $_ = Lang::$game['npc'].' #'.$effMV;
                $summon = new CreatureList(array(['ct.id', $effMV]));
                if (!$summon->error)
                {
                    $_ = '(<a href="?npc='.$effMV.'">'.$summon->getField('name', true).'</a>)';
                    $pageData['buttons'][BUTTON_VIEW3D] = ['type' => TYPE_NPC, 'displayId' => $summon->getRandomModelId()];
                }

                $foo['name'] .= Lang::$colon.$_;
                break;
            case 33:                                        // Open Lock
                $_ = @Lang::$spell['lockType'][$effMV];
                if ($_ && User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else if (!$_)
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 53:                                        // Enchant Item Perm
            case 54:                                        // Enchant Item Temp
            case 156:                                       // Enchant Item Prismatic
                if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_itemEnchantment WHERE id = ?d', $effMV))
                    $foo['name'] .= ' <span class="q2">'.Util::localizedString($_, 'text').'</span> ('.$effMV.')';
                else
                    $foo['name'] .= ' #'.$effMV;
                break;
            case 38:                                        // Dispel               [miscValue => Types]
            case 126:                                       // Steal Aura
                $_ = @Lang::$game['dt'][$effMV];
                if ($_ && User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else if (!$_)
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 39:                                        // Learn Language
                $_ = @Lang::$game['languages'][$effMV];
                if ($_ && User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else if (!$_)
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 50:                                        // Trans Door
            case 76:                                        // Summon Object (Wild)
            case 86:                                        // Activate Object
            case 104:                                       // Summon Object (slot 1)
            case 105:                                       // Summon Object (slot 2)
            case 106:                                       // Summon Object (slot 3)
            case 107:                                       // Summon Object (slot 4)
                // todo (low): create go/modelviewer-data
                $_ = Util::ucFirst(Lang::$game['gameObject']).' #'.$effMV;
                $n = GameObjectList::getName($effMV); // $summon = new GameObjectList(array(['go.id', $effMV]));
                if ($n/*!$summon->error*/)
                {
                    $_ = '(<a href="?object='.$effMV.'">'.$n/*$summon->getField('name', true)*/.'</a>)';
                    //$pageData['buttons'][BUTTON_VIEW3D] = ['type' => TYPE_NPC, 'displayId' => $summon->getRandomModelId()];
                }

                $foo['name'] .= Lang::$colon.$_;
                break;
            case 74:                                        // Apply Glyph
                if ($_ = DB::Aowow()->selectCell('SELECT spellId FROM ?_glyphProperties WHERE id = ?d', $effMV))
                {
                    if ($n = SpellList::getName($_))
                        $foo['name'] .= Lang::$colon.'(<a href="?spell='.$_.'">'.$n.'</a>)';
                    else
                        $foo['name'] .= Lang::$colon.Util::ucFirst(Lang::$game['spell']).' #'.$_;;
                }
                else
                    $foo['name'] .= ' #'.$effMV;;
                break;
            case 95:                                        // Skinning
                switch ($effMV)
                {
                    case 0:  $_ = Lang::$game['ct'][1].', '.Lang::$game['ct'][2]; break;    // Beast, Dragonkin
                    case 1:
                    case 2:  $_ = Lang::$game['ct'][4]; break;                              // Elemental (nature based, earth based)
                    case 3:  $_ = Lang::$game['ct'][9]; break;                              // Mechanic
                    default; $_ = '';
                }
                if (User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 108:                                       // Dispel Mechanic
                $_ = @Lang::$game['me'][$effMV];
                if ($_ && User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else if (!$_)
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 118:                                       // Require Skill
                if ($_ = SkillList::getName($effMV))
                    $foo['name'] .= Lang::$colon.'(<a href="?skill='.$effMV.'">'.$_.'</a>)';
                else
                    $foo['name'] .= Lang::$colon.Util::ucFirst(Lang::$game['skill']).' #'.$effMV;;
                break;
            case 146:                                       // Activate Rune
                $_ = @Lang::$spell['powerRunes'][$effMV];
                if ($_ && User::isInGroup(U_GROUP_STAFF))
                    $_ = sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_);
                else if (!$_)
                    $_ = $effMV;

                $foo['name'] .= ' ('.$_.')';
                break;
            case 123:                                       // Send Taxi - effMV is taxiPathId. We only use paths for flightmasters for now, so spell-triggered paths are not in the table
            default:
            {
                if (($effMV || $effId == 97) && $effId != 155)
                    $foo['name'] .= ' ('.$effMV.')';
            }
            // Aura
            case 6:                                         // Simple
            case 27:                                        // AA Persistent
            case 35:                                        // AA Party
            case 65:                                        // AA Raid
            case 119:                                       // AA Pet
            case 128:                                       // AA Friend
            case 129:                                       // AA Enemy
            case 143:                                       // AA Owner
            {
                if ($effAura > 0 && isset(Util::$spellAuraStrings[$effAura]))
                {
                    $foo['name'] .= User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, 'AuraId: '.$effAura, Lang::$colon.Util::$spellAuraStrings[$effAura]) : Lang::$colon.Util::$spellAuraStrings[$effAura];

                    $bar = $effMV;
                    switch ($effAura)
                    {
                        case 17:                            // Mod Stealth Detection
                            if ($_ = @Lang::$spell['stealthType'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 19:                            // Mod Invisibility Detection
                            if ($_ = @Lang::$spell['invisibilityType'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 24:                            // Periodic Energize
                        case 21:                            // Obsolete Mod Power
                        case 35:                            // Mod Increase Power
                        case 85:                            // Mod Power Regeneration
                        case 110:                           // Mod Power Regeneration Pct
                            if ($_ = @Lang::$spell['powerTypes'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 29:                            // Mod Stat
                        case 80:                            // Mod Stat %
                        case 137:                           // Mod Total Stat %
                        case 175:                           // Mod Spell Healing Of Stat Percent
                        case 212:                           // Mod Ranged Attack Power Of Stat Percent
                        case 219:                           // Mod Mana Regeneration from Stat
                        case 268:                           // Mod Attack Power Of Stat Percent
                            $mask = $effMV == -1 ? 0x1F : 1 << $effMV;
                            $_ = [];
                            for ($j = 0; $j < 5; $j++)
                                if ($mask & (1 << $j))
                                    $_[] = Lang::$game['stats'][$j];

                            if ($_ = implode(', ', $_));
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 36:                            // Shapeshift
                            if ($st = DB::Aowow()->selectRow('SELECT *, displayIdA as model1, displayIdH as model2 FROM ?_shapeshiftForms WHERE id = ?d', $effMV))
                            {
                                $pageData['buttons'][BUTTON_VIEW3D] = array(
                                    'type'      => TYPE_NPC,
                                    'displayId' => $st['model2'] ? $st['model'.rand(1, 2)]: $st['model1']
                                );

                                if ($st['creatureType'] > 0)
                                    $pageData['infobox'][] = '[li]'.Lang::$game['type'].Lang::$colon.Lang::$game['ct'][$st['creatureType']].'[/li]';

                                if ($_ = Util::localizedString($st, 'name'))
                                    $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;
                            }
                            break;
                        case 37:                            // Effect immunity
                            if ($_ = @Util::$spellEffectStrings[$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 38:                            // Aura immunity
                            if ($_ = @Util::$spellAuraStrings[$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 41:                            // Dispel Immunity
                        case 178:                           // Mod Debuff Resistance
                        case 245:                           // Mod Aura Duration By Dispel
                            if ($_ = @Lang::$game['dt'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 44:                            // Track Creature
                            if ($_ = @Lang::$game['ct'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 45:                            // Track Resource
                            if ($_ = @Lang::$spell['lockType'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 75:                            // Language
                            if ($_ = @Lang::$game['languages'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 77:                            // Mechanic Immunity
                        case 117:                           // Mod Mechanic Resistance
                        case 232:                           // Mod Mechanic Duration
                        case 234:                           // Mod Mechanic Duration (no stack)
                        case 255:                           // Mod Mechanic Damage Taken Pct
                        case 276:                           // Mod Mechanic Damage Done Percent
                            if ($_ = @Lang::$game['me'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.Util::asHex($effMV), $_) : $_;

                            break;
                        case 147:                           // Mechanic Immunity Mask
                            $_ = [];
                            foreach (Lang::$game['me'] as $k => $str)
                                if ($effMV & (1 << $k - 1))
                                    $_[] = $str;

                            if ($_ = implode(', ', $_))
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.Util::asHex($effMV), $_) : $_;

                            break;
                        case 10:                            // Mod Threat
                        case 13:                            // Mod Damage Done
                        case 14:                            // Mod Damage Taken
                        case 22:                            // Mod Resistance
                        case 39:                            // School Immunity
                        case 40:                            // Damage Immunity
                        case 57:                            // Mod Spell Crit Chance
                        case 69:                            // School Absorb
                        case 71:                            // Mod Spell Crit Chance School
                        case 72:                            // Mod Power Cost School Percent
                        case 73:                            // Mod Power Cost School Flat
                        case 74:                            // Reflect Spell School
                        case 79:                            // Mod Damage Done Pct
                        case 81:                            // Split Damage Pct
                        case 83:                            // Mod Base Resistance
                        case 87:                            // Mod Damage Taken Pct
                        case 97:                            // Mana Shield
                        case 101:                           // Mod Resistance Pct
                        case 115:                           // Mod Healing Taken
                        case 118:                           // Mod Healing Taken Pct
                        case 123:                           // Mod Target Resistance
                        case 135:                           // Mod Healing Done
                        case 136:                           // Mod Healing Done Pct
                        case 142:                           // Mod Base Resistance Pct
                        case 143:                           // Mod Resistance Exclusive
                        case 149:                           // Reduce Pushback
                        case 163:                           // Mod Crit Damage Bonus
                        case 174:                           // Mod Spell Damage Of Stat Percent
                        case 182:                           // Mod Resistance Of Stat Percent
                        case 186:                           // Mod Attacker Spell Hit Chance
                        case 194:                           // Mod Target Absorb School
                        case 195:                           // Mod Target Ability Absorb School
                        case 199:                           // Mod Increases Spell Percent to Hit
                        case 229:                           // Mod AoE Damage Avoidance
                        case 271:                           // Mod Damage Percent Taken Form Caster
                        case 310:                           // Mod Creature AoE Damage Avoidance
                            if ($_ = Lang::getMagicSchools($effMV))
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.Util::asHex($effMV), $_) : $_;

                            break;
                        case 30:                            // Mod Skill
                        case 98:                            // Mod Skill Value
                            if ($_ = SkillList::getName($effMV))
                                $bar = ' (<a href="?skill='.$effMV.'">'.SkillList::getName($effMV).'</a>)';
                            else
                                $bar = Lang::$colon.Util::ucFirst(Lang::$game['skill']).' #'.$effMV;;

                            break;
                        case 107:                           // Flat Modifier
                        case 108:                           // Pct Modifier
                            if ($_ = @Lang::$spell['spellModOp'][$effMV])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$effMV, $_) : $_;

                            break;
                        case 189:                           // Mod Rating
                        case 220:                           // Combat Rating From Stat
                            $_ = [];
                            foreach (Lang::$spell['combatRating'] as $k => $str)
                                if ((1 << $k) & $effMV)
                                    $_[] = $str;

                            if ($_ = implode(', ', $_))
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.Util::asHex($effMV), $_) : $_;

                            break;
                        case 168:                           // Mod Damage Done Versus
                        case 59:                            // Mod Damage Done Versus Creature
                            $_ = [];
                            foreach (Lang::$game['ct'] as $k => $str)
                                if ($effMV & (1 << $k - 1))
                                    $_[] = $str;

                            if ($_ = implode(', ', $_))
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.Util::asHex($effMV), $_) : $_;

                            break;
                        case 249:                           // Convert Rune
                            $x = $spell->getField('effect'.$i.'MiscValueB');
                            if ($_ = @Lang::$spell['powerRunes'][$x])
                                $bar = User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, Lang::$spell['_value'].Lang::$colon.$x, $_) : $_;

                            break;
                        case 78:                            // Mounted
                        case 56:                            // Transform
                        {
                            $transform = new CreatureList(array(['ct.id', $effMV]));
                            if (!$transform->error)
                            {
                                $pageData['view3D'] = $transform->getRandomModelId();
                                $pageData['buttons'][BUTTON_VIEW3D] = ['type' => TYPE_NPC, 'displayId' => $transform->getRandomModelId()];
                                $bar = ' (<a href="?npc='.$effMV.'">'.$transform->getField('name', true).'</a>)';
                            }
                            else
                                $bar = Lang::$colon.Lang::$game['npc'].' #'.$effMV;;

                            break;
                        }
                    }
                    $foo['name'] .= strstr($bar, 'href') || strstr($bar, '#') ? $bar : ($bar ? ' ('.$bar.')' : null);

                    if (in_array($effAura, [174, 220, 182]))
                        $foo['name'] .= ' ['.sprintf(Util::$dfnString, Lang::$game['stats'][$spell->getField('effect'.$i.'MiscValueB')], $spell->getField('effect'.$i.'MiscValueB')).']';
                    else if ($spell->getField('effect'.$i.'MiscValueB') > 0)
                        $foo['name'] .= ' ['.$spell->getField('effect'.$i.'MiscValueB').']';

                }
                else if ($effAura > 0)
                    $foo['name'] .= Lang::$colon.'Unknown Aura ('.$effAura.')';

                break;
            }
        }

        // cases where we dont want 'Value' to be displayed
        if (in_array($effAura, [11, 12, 36, 77]) || in_array($effId, []))
            unset($foo['value']);
    }

    $pageData['infobox'] = !empty($pageData['infobox']) ? '[ul]'.implode('', $pageData['infobox']).'[/ul]' : null;

    unset($foo);                                            // clear reference

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: modifies $this
    $sub = ['OR'];
    $conditions = [
        ['s.typeCat', [0, -9 /*, -8*/], '!'],               // uncategorized (0), GM (-9), NPC-Spell (-8); NPC includes totems, lightwell and others :/
        ['s.spellFamilyId', $spell->getField('spellFamilyId')],
        &$sub
    ];

    for ($i = 1; $i < 4; $i++)
    {
        // Flat Mods (107), Pct Mods (108), No Reagent Use (256) .. include dummy..? (4)
        if (!in_array($spell->getField('effect'.$i.'AuraId'), [107, 108, 256 /*, 4*/]))
            continue;

        $m1 = $spell->getField('effect1SpellClassMask'.$l[$i]);
        $m2 = $spell->getField('effect2SpellClassMask'.$l[$i]);
        $m3 = $spell->getField('effect3SpellClassMask'.$l[$i]);

        if (!$m1 && !$m2 && !$m3)
            continue;

        $sub[] = ['s.spellFamilyFlags1', $m1, '&'];
        $sub[] = ['s.spellFamilyFlags2', $m2, '&'];
        $sub[] = ['s.spellFamilyFlags3', $m3, '&'];
    }

    if (count($sub) > 1)
    {
        $modSpells = new SpellList($conditions);

        if (!$modSpells->error)
        {

            if (!$modSpells->hasSetFields(['skillLines']))
                $msH = "$['skill']";

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $modSpells->getListviewData(),
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'id'          => 'modifies',
                    'name'        => '$LANG.tab_modifies',
                    'visibleCols' => "$['level']",
                    'hiddenCols'  => isset($msH) ? $msH : null
                ]
            );

            $modSpells->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
        }
    }

    // tab: modified by $this
    $sub = ['OR'];
    $conditions = [
        ['s.spellFamilyId', $spell->getField('spellFamilyId')],
        &$sub
    ];

    for ($i = 1; $i < 4; $i++)
    {
        $m1 = $spell->getField('spellFamilyFlags1');
        $m2 = $spell->getField('spellFamilyFlags2');
        $m3 = $spell->getField('spellFamilyFlags3');

        if (!$m1 && !$m2 && !$m3)
            continue;

        $sub[] = array(
            'AND',
            ['s.effect'.$i.'AuraId', [107, 108, 256 /*, 4*/]],
            [
                'OR',
                ['s.effect1SpellClassMask'.$l[$i], $m1, '&'],
                ['s.effect2SpellClassMask'.$l[$i], $m2, '&'],
                ['s.effect3SpellClassMask'.$l[$i], $m3, '&']
            ]
        );
    }

    if (count($sub) > 1)
    {
        $modsSpell = new SpellList($conditions);
        if (!$modsSpell->error)
        {
            if (!$modsSpell->hasSetFields(['skillLines']))
                $mbH = "$['skill']";

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $modsSpell->getListviewData(),
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'id'          => 'modified-by',
                    'name'        => '$LANG.tab_modifiedby',
                    'visibleCols' => "$['level']",
                    'hiddenCols'  => isset($mbH) ? $mbH : null
                ]
            );

            $modsSpell->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
        }
    }

    // tab: see also
    $conditions = array(
        ['s.schoolMask', $spell->getField('schoolMask')],
        ['s.effect1Id', $spell->getField('effect1Id')],
        ['s.effect2Id', $spell->getField('effect2Id')],
        ['s.effect3Id', $spell->getField('effect3Id')],
        ['s.id', $spell->id, '!'],
        ['s.name_loc'.User::$localeId, $spell->getField('name', true)]
    );

    $saSpells = new SpellList($conditions);
    if (!$saSpells->error)
    {
        $data = $saSpells->getListviewData();
        if ($difficulties)                                  // needs a way to distinguish between dungeon and raid :x
        {
            $saE = '$[Listview.extraCols.mode]';

            foreach ($data as $id => $dat)
            {
                $data[$id]['modes'] = ['mode' => 0];

                if ($difficulties[0] == $id)       // b0001000
                {
                    if (!$difficulties[2] && !$difficulties[3])
                        $data[$id]['modes']['mode'] |= 0x2;
                    else
                        $data[$id]['modes']['mode'] |= 0x8;
                }

                if ($difficulties[1] == $id)       // b0010000
                {
                    if (!$difficulties[2] && !$difficulties[3])
                        $data[$id]['modes']['mode'] |= 0x1;
                    else
                        $data[$id]['modes']['mode'] |= 0x10;
                }

                if ($difficulties[2] == $id)       // b0100000
                    $data[$id]['modes']['mode'] |= 0x20;

                if ($difficulties[3] == $id)       // b1000000
                    $data[$id]['modes']['mode'] |= 0x40;
            }
        }

        if (!$saSpells->hasSetFields(['skillLines']))
            $saH = "$['skill']";

        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $data,
            'params' => [
                'tabs'        => '$tabsRelated',
                'id'          => 'see-also',
                'name'        => '$LANG.tab_seealso',
                'visibleCols' => "$['level']",
                'extraCols'   => isset($saE) ? $saE : null,
                'hiddenCols'  => isset($saH) ? $saH : null
            ]
        );

        $saSpells->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
    }

    // tab: used by - itemset
    $conditions = array(
        'OR',
        ['spell1', $spell->id], ['spell2', $spell->id], ['spell3', $spell->id], ['spell4', $spell->id],
        ['spell5', $spell->id], ['spell6', $spell->id], ['spell7', $spell->id], ['spell8', $spell->id]
    );

    $ubSets = new ItemsetList($conditions);
    if (!$ubSets->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'itemset',
            'data'   => $ubSets->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'used-by-itemset',
                'name' => '$LANG.tab_usedby'
            ]
        );

        $ubSets->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
    }

    // tab: used by - item
    $conditions = array(
        'OR',                  // 6: learn spell
        ['AND', ['spellTrigger1', 6, '!'], ['spellId1', $spell->id]],
        ['AND', ['spellTrigger2', 6, '!'], ['spellId2', $spell->id]],
        ['AND', ['spellTrigger3', 6, '!'], ['spellId3', $spell->id]],
        ['AND', ['spellTrigger4', 6, '!'], ['spellId4', $spell->id]],
        ['AND', ['spellTrigger5', 6, '!'], ['spellId5', $spell->id]]
    );

    $ubItems = new ItemList($conditions);
    if (!$ubItems->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'item',
            'data'   => $ubItems->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'used-by-item',
                'name' => '$LANG.tab_usedby'
            ]
        );

        $ubItems->addGlobalsToJScript($smarty, GLOBALINFO_SELF);
    }

    $conditions = array(
        ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET,     ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2,     ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL,
                     ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2,         ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL]
        ],
        ['ac.value1', $_id]
    );
    $coAchievemnts = new AchievementList($conditions);
    if (!$coAchievemnts->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'achievement',
            'data'   => $coAchievemnts->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'criteria-of',
                'name' => '$LANG.tab_criteriaof'
            ]
        );

        $coAchievemnts->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
    }

    // tab: contains
    // spell_loot_template & skill_extra_item_template
    $extraItem = DB::Aowow()->selectRow('SELECT * FROM skill_extra_item_template WHERE spellid = ?d', $spell->id);
    $spellLoot = Util::handleLoot(LOOT_SPELL, $spell->id, User::isInGroup(U_GROUP_STAFF), $extraCols);

    if ($extraItem || $spellLoot)
    {
        $extraCols[] = 'Listview.extraCols.percent';
        $lv = $spellLoot;

        if ($extraItem && $spell->canCreateItem())
        {
            $foo = $spell->relItems->getListviewData();

            for ($i = 1; $i < 4; $i++)
            {
                if (($bar = $spell->getField('effect'.$i.'CreateItemId')) && isset($foo[$bar]))
                {
                    $lv[$bar] = $foo[$bar];
                    $lv[$bar]['percent']   = $extraItem['additionalCreateChance'];
                    $lv[$bar]['condition'] = ['type' => TYPE_SPELL, 'typeId' => $extraItem['requiredSpecialization'], 'status' => 2];
                    $smarty->extendGlobalIds(TYPE_SPELL, $extraItem['requiredSpecialization']);

                    $extraCols[] = 'Listview.extraCols.condition';
                    if ($max = $extraItem['additionalMaxNum'])
                    {
                        $lv[$bar]['mincount'] = 1;
                        $lv[$bar]['maxcount'] = $max;
                    }

                    break;                                  // skill_extra_item_template can only contain 1 item
                }
            }
        }

        $pageData['relTabs'][] = array(
            'file'   => 'item',
            'data'   => $lv,
            'params' => [
                'tabs'       => '$tabsRelated',
                'name'       => '$LANG.tab_contains',
                'id'         => 'contains',
                'hiddenCols' => "$['side', 'slot', 'source', 'reqlevel']",
                'extraCols'  => "$[".implode(', ', $extraCols)."]"
            ]
        );
    }

    // tab: exclusive with
    if ($firstRank) {
        $linkedSpells = DB::Aowow()->selectCol(             // dont look too closely ..... please..?
           'SELECT      IF(sg2.spell_id < 0, sg2.id, sg2.spell_id) AS ARRAY_KEY, IF(sg2.spell_id < 0, sg2.spell_id, sr.stack_rule)
            FROM        spell_group sg1
            JOIN        spell_group sg2
                ON      (sg1.id = sg2.id OR sg1.id = -sg2.spell_id) AND sg1.spell_id != sg2.spell_id
            LEFT JOIN   spell_group_stack_rules sr
                ON      sg1.id = sr.group_id
            WHERE       sg1.spell_id = ?d',
            $firstRank
        );

        if ($linkedSpells)
        {
            $extraSpells = [];
            foreach ($linkedSpells as $k => $v)
            {
                if ($v > 0)
                    continue;

                $extraSpells += DB::Aowow()->selectCol(      // recursive case (recursive and regular ids are not mixed in a group)
                   'SELECT      sg2.spell_id AS ARRAY_KEY, sr.stack_rule
                    FROM        spell_group sg1
                    JOIN        spell_group sg2
                        ON      sg2.id = -sg1.spell_id AND sg2.spell_id != ?d
                    LEFT JOIN   spell_group_stack_rules sr
                        ON      sg1.id = sr.group_id
                    WHERE       sg1.id = ?d',
                    $firstRank,
                    $k
                );

                unset($linkedSpells[$k]);
            }

            $groups  = $linkedSpells + $extraSpells;
            $stacks = new SpellList(array(['s.id', array_keys($groups)]));

            if (!$stacks->error)
            {
                $data = $stacks->getListviewData();
                foreach ($data as $k => $d)
                    $data[$k]['stackRule'] = $groups[$k];

                if (!$stacks->hasSetFields(['skillLines']))
                    $sH = "$['skill']";

                $pageData['relTabs'][] = array(
                    'file'   => 'spell',
                    'data'   => $data,
                    'params' => [
                        'tabs'        => '$tabsRelated',
                        'id'          => 'spell-group-stack',
                        'name'        => 'Stack Group',     // todo (med): localize
                        'visibleCols' => "$['stackRules']",
                        'hiddenCols'  => isset($sH) ? $sH : null
                    ]
                );

                $stacks->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
            }
        }
    }

    // tab: linked with
    $rows = DB::Aowow()->select('
        SELECT  spell_trigger AS `trigger`,
                spell_effect AS effect,
                type,
                IF(ABS(spell_effect) = ?d, ABS(spell_trigger), ABS(spell_effect)) AS related
        FROM    spell_linked_spell
        WHERE   ABS(spell_effect) = ?d OR ABS(spell_trigger) = ?d',
        $_id, $_id, $_id
    );

    $related = [];
    foreach ($rows as $row)
        $related[] = $row['related'];

    if ($related)
        $linked = new SpellList(array(['s.id', $related]));

    if (isset($linked) && !$linked->error)
    {
        $lv = $linked->getListviewData();
        $data = [];

        foreach ($rows as $r)
        {
            foreach ($lv as $dk => $d)
            {
                if ($r['related'] == $dk)
                {
                    $lv[$dk]['linked'] = [$r['trigger'], $r['effect'], $r['type']];
                    $data[] = $lv[$dk];
                    break;
                }
            }
        }

        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $data,
            'params' => [
                'tabs'        => '$tabsRelated',
                'id'          => 'spell-link',
                'name'        => 'Linked with',             // todo (med): localize
                'hiddenCols'  => "$['skill', 'name']",
                'visibleCols' => "$['linkedTrigger', 'linkedEffect']"
            ]
        );

        $linked->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
    }


    // tab: triggered by
    $conditions = array(
        'OR',
        ['AND', ['OR', ['effect1Id', SpellList::$effects['trigger']], ['effect1AuraId', SpellList::$auras['trigger']]], ['effect1TriggerSpell', $spell->id]],
        ['AND', ['OR', ['effect2Id', SpellList::$effects['trigger']], ['effect2AuraId', SpellList::$auras['trigger']]], ['effect2TriggerSpell', $spell->id]],
        ['AND', ['OR', ['effect3Id', SpellList::$effects['trigger']], ['effect3AuraId', SpellList::$auras['trigger']]], ['effect3TriggerSpell', $spell->id]],
    );

    $trigger = new SpellList($conditions);
    if (!$trigger->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $trigger->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'triggered-by',
                'name' => '$LANG.tab_triggeredby'
            ]
        );

        $trigger->addGlobalsToJScript($smarty, GLOBALINFO_SELF);
    }

    // used by - creature
    // SMART_SCRIPT_TYPE_CREATURE = 0; SMART_ACTION_CAST = 11; SMART_ACTION_ADD_AURA = 75; SMART_ACTION_INVOKER_CAST = 85; SMART_ACTION_CROSS_CAST = 86
    $smart      = DB::Aowow()->selectCol('SELECT entryOrGUID FROM smart_scripts WHERE entryorguid > 0 AND source_type = 0 AND action_type IN (11, 75, 85, 86) AND action_param1 = ?d', $_id);
    $conditions = array(
        'OR',             ['id', $smart],
        ['spell1', $_id], ['spell2', $_id], ['spell3', $_id], ['spell4', $_id],
        ['spell5', $_id], ['spell6', $_id], ['spell7', $_id], ['spell8', $_id]
    );

    $ubCreature = new CreatureList($conditions);
    if (!$ubCreature->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'creature',
            'data'   => $ubCreature->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'used-by-npc',
                'name' => '$LANG.tab_usedby'
            ]
        );

        $ubCreature->addGlobalsToJScript($smarty, GLOBALINFO_SELF);
    }

    // tab: questreward
    $query = 'SELECT qt.id FROM quest_template qt JOIN ?_spell s ON s.id = qt.SourceSpellId OR s.id = qt.RewardSpellCast OR (s.id = qt.RewardSpell AND qt.RewardSpellCast = 0)
              WHERE (s.effect1Id IN (36, 57) AND effect1TriggerSpell = ?d) OR (s.effect2Id IN (36, 57) AND effect2TriggerSpell = ?d) OR (s.effect3Id IN (36, 57) AND effect3TriggerSpell = ?d)';

    if ($ids = DB::Aowow()->selectCol($query, $_id, $_id, $_id))
    {
        $tbQuest = new QuestList(array(['id', $ids]));
        if (!$tbQuest->error)
        {
            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $tbQuest->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'id'   => 'reward-from-quest',
                    'name' => '$LANG.tab_rewardfrom'
                ]
            );

            $tbQuest->addGlobalsToJScript($smarty);
        }
    }

    // tab: teaches
    if ($ids = Util::getTaughtSpells($spell))
    {
        $teaches = new SpellList(array(['id', $ids]));
        if (!$teaches->error)
        {
            $teaches->addGlobalsToJScript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
            $vis = ['level', 'schools'];
            $hid = [];
            if (!$teaches->hasSetFields(['skillLines']))
                $hid[] = 'skill';

            foreach ($teaches->iterate() as $__)
            {
                if (!$teaches->canCreateItem())
                    continue;

                $vis[] = 'reagents';
                break;
            }

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $teaches->getListviewData(),
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'id'          => 'teaches-spell',
                    'name'        => '$LANG.tab_teaches',
                    'visibleCols' => '$'.json_encode($vis),
                    'hiddenCols'  => $hid ? '$'.json_encode($hid) : null
                ]
            );
        }
    }

    // tab: taught by npc (source:6 => trainer)
    if (!empty($spell->sources[$_id]) && in_array(6, array_keys($spell->sources[$_id])))
    {
        $list = [];
        if (count($spell->sources[$_id][6]) == 1 && $spell->sources[$_id][6][0] == 0)   // multiple trainer
        {
            $tt = null;
            if (in_array($_cat, [9, 11]))                                               // Professions
                $tt = @Util::$trainerTemplates[TYPE_SKILL][$spell->getField('skillLines')[0]];
            else if ($_cat == 7 && $spell->getField('reqClassMask'))                    // Class Spells
            {
                $clId = log($spell->getField('reqClassMask'), 2) + 1 ;
                if (intVal($clId) == $clId)                                             // only one class was set, so float == int
                    $tt = @Util::$trainerTemplates[TYPE_CLASS][$clId];
            }

            if ($tt)
                $list = DB::Aowow()->selectCol('SELECT DISTINCT entry FROM npc_trainer WHERE spell IN (?a) AND entry < 200000', $tt);
            else
            {
                $mask = 0;
                foreach (Util::$skillLineMask[-3] as $idx => $pair)
                    if ($pair[1] == $_id)
                        $mask |= 1 << $idx;

                $list = DB::Aowow()->selectCol('
                    SELECT    IF(t1.entry > 200000, t2.entry, t1.entry)
                    FROM      npc_trainer t1
                    LEFT JOIN npc_trainer t2 ON t2.spell = -t1.entry
                    WHERE     t1.spell = ?d',
                    $_id
                );
            }
        }

        if ($list)
        {
            $tbTrainer = new CreatureList(array(0, ['ct.id', $list], ['ct.spawns', 0, '>'], ['ct.npcflag', 0x10, '&']));
            if (!$tbTrainer->error)
            {
                $tbTrainer->addGlobalsToJscript($smarty);
                $pageData['relTabs'][] = array(
                    'file'   => 'creature',
                    'data'   => $tbTrainer->getListviewData(),
                    'params' => array(
                        'tabs' => '$tabsRelated',
                        'id'   => 'taught-by-npc',
                        'name' => '$LANG.tab_taughtby',
                    )
                );
            }
        }
    }

    // tab: taught by spell
    $conditions = array(
        'OR',
        ['AND', ['effect1Id', SpellList::$effects['teach']], ['effect1TriggerSpell', $spell->id]],
        ['AND', ['effect2Id', SpellList::$effects['teach']], ['effect2TriggerSpell', $spell->id]],
        ['AND', ['effect3Id', SpellList::$effects['teach']], ['effect3TriggerSpell', $spell->id]],
    );

    $tbSpell = new SpellList($conditions);
    if (!$tbSpell->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $tbSpell->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'taught-by-spell',
                'name' => '$LANG.tab_taughtby'
            ]
        );

        $tbSpell->addGlobalsToJScript($smarty, GLOBALINFO_SELF);
    }

    // tab: taught by item (i'd like to precheck $spell->sources, but there is no source:item only complicated crap like "drop" and "vendor")
    $conditions = array(
        'OR',
        ['AND', ['spellTrigger1', 6], ['spellId1', $spell->id]],
        ['AND', ['spellTrigger2', 6], ['spellId2', $spell->id]],
        ['AND', ['spellTrigger3', 6], ['spellId3', $spell->id]],
        ['AND', ['spellTrigger4', 6], ['spellId4', $spell->id]],
        ['AND', ['spellTrigger5', 6], ['spellId5', $spell->id]],
    );

    $tbItem = new ItemList($conditions);
    if (!$tbItem->error)
    {
        $pageData['relTabs'][] = array(
            'file'   => 'item',
            'data'   => $tbItem->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'id'   => 'taught-by-item',
                'name' => '$LANG.tab_taughtby'
            ]
        );

        $tbItem->addGlobalsToJScript($smarty, GLOBALINFO_SELF);
    }

    // find associated NPC, Item and merge results
    // taughtbypets (unused..?)
    // taughtbyquest (usually the spell casted as quest reward teaches something; exclude those seplls from taughtBySpell)
    // taughtbytrainers
    // taughtbyitem


    /* NEW
        conditions
    */

    $smarty->saveCache($cacheKeyPage, $pageData);
}


// menuId 1: Spell    g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => $pageData['title'].' - '.Util::ucFirst(Lang::$game['spell']),
    'path'   => $pageData['path'],
    'tab'    => 0,
    'type'   => TYPE_SPELL,
    'typeId' => $_id,
    'reqJS'  => array(
        $pageData['buttons'][BUTTON_VIEW3D] ? 'template/js/swfobject.js' : null
    )
));
$smarty->assign('redButtons', $pageData['buttons']);
$smarty->assign('community', CommunityContent::getAll(TYPE_SPELL, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$spell, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('spell.tpl');

?>
