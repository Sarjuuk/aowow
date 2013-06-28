<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.community.php';

$id = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_SPELL, $id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_SPELL, $id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $spell = new SpellList(array(['s.id', $id]));
        if ($spell->error)
            die('$WowheadPower.registerSpell('.$id.', '.User::$localeId.', {});');

        $x  = '$WowheadPower.registerSpell('.$id.', '.User::$localeId.", {\n";
        $pt = [];
        if ($n = $spell->getField('name', true))
            $pt[] = "\tname_".User::$localeString.": '".Util::jsEscape($n)."'";
        if ($i = $spell->getField('iconString'))
            $pt[] = "\ticon: '".Util::jsEscape($i)."'";
        if ($t = $spell->renderTooltip($id))
            $pt[] = "\ttooltip_".User::$localeString.": '".Util::jsEscape($t)."'";
        if ($b = $spell->renderBuff($id))
            $pt[] = "\tbuff_".User::$localeString.": '".Util::jsEscape($b)."'";
        $x .= implode(",\n", $pt)."\n});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $spell = new SpellList(array(['s.id', $id]));
    if ($spell->error)
        $smarty->notFound(Lang::$game['spell']);

    $cat = $spell->getField('typeCat');

    $pageData['path'] = [0, 1, $cat];

    // reconstruct path / title
    switch($cat)
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
                    $pageData['path'][] = $i;
                    break;
                }
                $i++;
            }

            if ($cat == -13)
            {
                $pageData['path'][] = ($spell->getField('cuFlags') & (SPELL_CU_GLYPH_MAJOR | SPELL_CU_GLYPH_MINOR)) >> 6;
                break;
            }
        case   9:
        case  -3:
        case  11:
            $pageData['path'][] = $spell->getField('skillLines')[0];

            if ($cat == 11)
                if ($_ = $spell->getField('reqSpellId'))
                    $pageData['path'][] = $_;

            break;
        case -11:
            foreach (SpellList::$skillLines as $line => $skills)
                if (in_array($spell->getField('skillLines')[0], $skills))
                    $pageData['path'][] = $line;
            break;
        case  -7:                                           // only spells unique in skillLineAbility will always point to the right skillLine :/
            $_ = $spell->getField('cuFlags');
            if ($_ & SPELL_CU_PET_TALENT_TYPE0)
                $pageData['path'][] = 411;                  // Ferocity
            else if ($_ & SPELL_CU_PET_TALENT_TYPE1)
                $pageData['path'][] = 409;                  // Tenacity
            else if ($_ & SPELL_CU_PET_TALENT_TYPE2)
                $pageData['path'][] = 410;                  // Cunning
    }

    $spellArr = $pageData['page'] = $spell->getDetailPageData();

    // description
    $pageData['page']['info'] = $spell->renderTooltip(MAX_LEVEL, true);

    // buff
    $pageData['page']['buff'] = $spell->renderBuff(MAX_LEVEL, true);

    // infobox
    $infobox = [];

    if (!in_array($cat, [-5, -6]))                          // not mount or vanity pet
    {
        if ($_ = $spell->getField('talentLevel'))           // level
            $infobox[] = '[li]'.(in_array($cat, [-2, 7, -13]) ? sprintf(Lang::$game['reqLevel'], $_) : Lang::$game['level'].Lang::$colon.$_).'[/li]';
        else if ($_ = $spell->getField('spellLevel'))
            $infobox[] = '[li]'.(in_array($cat, [-2, 7, -13]) ? sprintf(Lang::$game['reqLevel'], $_) : Lang::$game['level'].Lang::$colon.$_).'[/li]';
    }

    if ($mask = $spell->getField('reqRaceMask'))            // race
    {
        $bar = [];
        for ($i = 0; $i < 11; $i++)
            if ($mask & (1 << $i))
                $bar[] = (!fMod(count($bar) + 1, 3) ? '\n' : null) . '[race='.($i + 1).']';

        $t = count($bar) == 1 ? Lang::$game['race'] : Lang::$game['races'];
        $infobox[] = '[li]'.Util::ucFirst($t).Lang::$colon.implode(', ', $bar).'[/li]';
    }

    if ($mask = $spell->getField('reqClassMask'))           // class
    {
        $bar = [];
        for ($i = 0; $i < 11; $i++)
            if ($mask & (1 << $i))
                $bar[] = (!fMod(count($bar) + 1, 3) ? '\n' : null) . '[class='.($i + 1).']';

        $t = count($bar) == 1 ? Lang::$game['class'] : Lang::$game['classes'];
        $infobox[] = '[li]'.Util::ucFirst($t).Lang::$colon.implode(', ', $bar).'[/li]';
    }

    if ($_ = $spell->getField('spellFocusObject'))          // spellFocus
    {
        $bar = DB::Aowow()->selectRow('SELECT * FROM ?_spellFocusObject WHERE id = ?d', $_);
        $infobox[] = '[li]'.Lang::$game['requires2'].' '.Util::localizedString($bar, 'name').'[/li]';
    }

    if (in_array($cat, [9, 11]))                            // primary & secondary trades
    {
        // skill
        $bar = SkillList::getName($spell->getField('skillLines')[0]);
        if ($_ = $spell->getField('learnedAt'))
            $bar .= ' ('.$_.')';

        $infobox[] = '[li]'.sprintf(Lang::$game['requires'], $bar).'[/li]';

        // specialization
        if ($_ = $spell->getField('reqSpellId'))
            $infobox[] = '[li]'.Lang::$game['requires2'].' '.SpellList::getName($_).'[/li]';

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

    // flag starter spell
    if (isset($spell->sources[$spell->id]) && array_key_exists(10, $spell->sources[$spell->id]))
        $infobox[] = '[li]'.Lang::$spell['starter'].'[/li]';

    // training cost
    if ($cost = DB::Aowow()->selectCell('SELECT spellcost FROM npc_trainer WHERE spell = ?d', $spell->id))
        $infobox[] = '[li]'.Lang::$spell['trainingCost'].Lang::$colon.'[money='.$cost.'][/li]';

    // title
    $pageData['title'] = [$spell->getField('name', true), Util::ucFirst(Lang::$game['spell'])];

    // js-globals
    $spell->addGlobalsToJScript($pageData);

    // prepare Tools
    foreach ($pageData['page']['tools'] as $k => $tool)
    {
        if (isset($tool['itemId']))                         // Tool
            $pageData['page']['tools'][$k]['url'] = '?item='.$tool;
        else                                                // ToolCat
        {
                $pageData['page']['tools'][$k]['quality'] = ITEM_QUALITY_HEIRLOOM - ITEM_QUALITY_NORMAL;
                $pageData['page']['tools'][$k]['url']     = '?items&filter=cr=91;crs='.$tool['id'].';crv=0';
        }
    }

    // prepare Reagents
    if ($pageData['page']['reagents'])
    {
        $_ = $pageData['page']['reagents'];
        $pageData['page']['reagents'] = [];

        while (!empty($_))
        {
            $spell->relItems->iterate();
            if (!in_array($spell->relItems->id, array_keys($_)))
                continue;

            $pageData['page']['reagents'][] = array(
                'name'    => $spell->relItems->getField('name', true),
                'quality' => $spell->relItems->getField('Quality'),
                'entry'   => $spell->relItems->id,
                'count'   => $_[$spell->relItems->id],
            );

            unset($_[$spell->relItems->id]);
        }
    }

    // Iterate through all effects:
    $pageData['page']['effect'] = [];
    $spell->reset();

    $pageData['view3D'] = 0;

    for ($i = 1; $i < 4; $i++)
    {
        if ($spell->getField('effect'.$i.'Id') <= 0)
            continue;

        $effId   = (int)$spell->getField('effect'.$i.'Id');
        $effMV   = (int)$spell->getField('effect'.$i.'MiscValue');
        $effBP   = (int)$spell->getField('effect'.$i.'BasePoints');
        $effDS   = (int)$spell->getField('effect'.$i.'DieSides');
        $effAura = (int)$spell->getField('effect'.$i.'AuraId');
        $foo     = &$pageData['page']['effect'][];

        // Icons:
        // .. from item
        if (in_array($effId, [24, 34, 59, 66]) || in_array($effAura, [86]))  // 24: createItem; 34: changeItem; 59: randomItem; 86: Channel Death Item
        {
            while ($spell->relItems->id != $spell->getField('effect'.$i.'CreateItemId'))
                $spell->relItems->iterate();

            $foo['icon'] = array(
                'id'      => $spell->relItems->id,
                'name'    => $spell->relItems->getField('name', true),
                'quality' => $spell->relItems->getField('Quality'),
                'count'   => $effDS + $effBP,
                'icon'    => $spell->relItems->getField('icon')
            );

            if ($effDS > 1)
                $foo['icon']['count'] = "'".($effBP + 1).'-'.$foo['icon']['count']."'";
        }
        // .. from spell
        else if (($_ = $spell->getField('effect'.$i.'TriggerSpell')) && $_ > 0)
        {
            $trig = new SpellList(array(['s.id', (int)$_]));

            $foo['icon'] = array(
                'id'    => $_,
                'name'  => $trig->getField('name', true),
                'icon'  => $trig->getField('iconString'),
                'count' => 0
            );

            $trig->addGlobalsToJScript($pageData);
        }

        // Effect Name
        $foo['name'] = '('.$effId.') '.Util::$spellEffectStrings[$effId];

        if ($spell->getField('effect'.$i.'RadiusMax') > 0)
            $foo['radius'] = $spell->getField('effect'.$i.'RadiusMax');

        if (($effBP + $effDS) && !$spell->getField('effect'.$i.'CreateItemId') && (!$spell->getField('effect'.$i.'TriggerSpell') || in_array($effAura, [225, 227])))
            $foo['value'] = ($effDS != 1 ? ($effBP + 1).Lang::$game['valueDelim'] : null).($effBP + $effDS);

        if($spell->getField('effect'.$i.'Periode') > 0)
            $foo['interval'] = $spell->getField('effect'.$i.'Periode') / 1000;

        // parse masks and indizes
        switch ($effId)
        {
            case 8:                                         // Power Drain
            case 30:                                        // Energize
            case 137:                                       // Energize Pct
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, @Lang::$spell['powerTypes'][$effMV], $effMV).')';
                break;
            case 16:                                        // QuestComplete
                $foo['name'] .= ': <a href="?quest='.$effMV.'">'.QuestList::getName($effMV).'</a> ('.$effMV.')';
                break;
            case 28:                                        // Summon
            case 75:                                        // Summon Totem
            case 87:                                        // Summon Totem (slot 1)
            case 88:                                        // Summon Totem (slot 2)
            case 89:                                        // Summon Totem (slot 3)
            case 90:                                        // Summon Totem (slot 4)
                $foo['name'] .= ': <a href="?npc='.$effMV.'">'.CreatureList::getName($effMV).'</a> ('.$effMV.')';
                break;
            case 33:                                        // open Lock
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, @Util::$lockType[$effMV], $effMV).')';
                break;
            case 53:                                        // Enchant Item Perm
            case 54:                                        // Enchant Item Temp
                $_ = DB::Aowow()->selectRow('SELECT * FROM ?_itemEnchantment WHERE id = ?d', $effMV);
                $foo['name'] .= ' <span class="q2">'.Util::localizedString($_, 'text').'</span> ('.$effMV.')';
                break;
            case 38:                                        // Dispel               [miscValue => Types]
            case 126:                                       // Steal Aura
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, @Lang::$game['dt'][$effMV], $effMV).')';
                break;
            case 39:                                        // Learn Language
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, @Lang::$game['languages'][$effMV], $effMV).')';
                break;
            case 50:                                        // Trans Door
            case 76:                                        // Summon Object (Wild)
            case 86:                                        // Activate Object
            case 104:                                       // Summon Object (slot 1)
            case 105:                                       // Summon Object (slot 2)
            case 106:                                       // Summon Object (slot 3)
            case 107:                                       // Summon Object (slot 4)
                $foo['name'] .= ': <a href="?object='.$effMV.'">'.GameObjectList::getName($effMV).'</a> ('.$effMV.')';
                break;
            case 74:                                        // Apply Glyph
                $_ = DB::Aowow()->selectCell('SELECT spellId FROM ?_glyphProperties WHERE id = ?d', $effMV);
                $foo['name'] .= ': <a href="?spell='.$_.'">'.SpellList::getName($_).'</a> ('.$effMV.')';
                break;
            case 95:                                        // Skinning
                // todo: sort this out - 0:skinning (corpse, beast), 1:hearb (GO), 2: ineral (GO), 3: engineer (corpse, mechanic)
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, 'NYI]', $effMV).')';
                break;
            case 108:                                       // Dispel Mechanic
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, @Lang::$game['me'][$effMV], $effMV).')';
                break;
            case 118:                                       // Require Skill
                $foo['name'] .= ': <a href="?skill='.$effMV.'">'.SkillList::getName($effMV).'</a> ('.$effMV.')';
                break;
            case 146:                                       // Activate Rune
                $foo['name'] .= ' ('.sprintf(Util::$dfnString, Lang::$spell['powerRunes'][$effMV], $effMV).')';
                break;
            case 155:                                       // Dual Wield 2H-Weapons
                $foo['name'] .= ': <a href="?spell='.$effMV.'">'.SpellList::getName($effMV).'</a> ('.$effMV.')';
                break;
            default:
            {
                if ($effMV || $effId == 97)
                    $foo['name'] .= ' ('.$effMV.')';
            }
            // Aura
            case 6:                     // Simple
            case 27:                    // AA Persistent
            case 35:                    // AA Party
            case 65:                    // AA Raid
            case 119:                   // AA Pet
            case 128:                   // AA Friend
            case 129:                   // AA Enemy
            case 143:                   // AA Owner
            {
                if ($effAura > 0 && isset(Util::$spellAuraStrings[$effAura]))
                {
                    $foo['name'] .= ' #'.$effAura;
                    switch ($effAura)
                    {
                        case 17:                            // Mod Stealth Detection
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Util::$stealthType[$effMV], $effMV).')';
                            break;
                        case 19:                            // Mod Invisibility Detection
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Util::$invisibilityType[$effMV], $effMV).')';
                            break;
                        case 24:                            // Periodic Energize
                        case 21:                            // Obsolete Mod Power
                        case 35:                            // Mod Increase Power
                        case 85:                            // Mod Power Regeneration
                        case 110:                           // Mod Power Regeneration Pct
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Lang::$spell['powerTypes'][$effMV], $effMV).')';
                            break;
                        case 29:                            // Mod Stat
                        case 80:                            // Mod Stat %
                        case 137:                           // Mod Total Stat %
                        case 175:                           // Mod Spell Healing Of Stat Percent
                        case 212:                           // Mod Ranged Attack Power Of Stat Percent
                        case 219:                           // Mod Mana Regeneration from Stat
                        case 268:                           // Mod Attack Power Of Stat Percent
                            $x = $effMV == -1 ? 0x1F : 1 << $effMV;
                            $bar = [];
                            for ($j = 0; $j < 5; $j++)
                                if ($x & (1 << $j))
                                    $bar[] = Lang::$game['stats'][$j];

                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, implode(', ', $bar), $effMV).')';
                            break;
                        case 36:                            // Shapeshift
                            $st = DB::Aowow()->selectRow('SELECT *, displayIdA as model1, displayIdH as model2 FROM ?_shapeshiftForms WHERE id = ?d', $effMV);

                            if ($st['creatureType'] > 0)
                                $infobox[] = '[li]'.Lang::$game['type'].Lang::$colon.Lang::$game['ct'][$st['creatureType']].'[/li]';

                            if (!$pageData['view3D'] && $st)
                                $pageData['view3D'] = $st['model2'] ? $st['model'.rand(1,2)]: $st['model1'];

                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, Util::localizedString($st, 'name'), $effMV).')';
                            break;
                        case 37:                            // Effect immunity
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Util::$spellEffectStrings[$effMV], $effMV).')';
                            break;
                        case 38:                            // Aura immunity
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Util::$spellAuraStrings[$effMV], $effMV).')';
                            break;
                        case 41:                            // Dispel Immunity
                        case 178:                           // Mod Debuff Resistance
                        case 245:                           // Mod Aura Duration By Dispel
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Lang::$game['dt'][$effMV], $effMV).')';
                            break;
                        case 44:                            // Track Creature
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Lang::$game['ct'][$effMV], $effMV).')';
                            break;
                        case 45:                            // Track Resource
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Util::$lockType[$effMV], $effMV).')';
                            break;
                        case 75:                            // Language
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Lang::$game['languages'][$effMV], $effMV).')';
                            break;
                        case 77:                            // Mechanic Immunity
                        case 117:                           // Mod Mechanic Resistance
                        case 232:                           // Mod Mechanic Duration
                        case 234:                           // Mod Mechanic Duration (no stack)
                        case 255:                           // Mod Mechanic Damage Taken Pct
                        case 276:                           // Mod Mechanic Damage Done Percent
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, @Lang::$game['me'][$effMV], $effMV).')';
                            break;
                        case 147:                           // mechanic Immunity Mask
                            $bar = [];
                            foreach (Lang::$game['me'] as $k => $str)
                                if ($effMV & (1 << $k - 1))
                                    $bar[] = $str;

                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, implode(', ', $bar), Util::asHex($effMV)).')';
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
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura];
                            if ($effMV)
                                 $foo['name'] .= ' ('.sprintf(Util::$dfnString, Lang::getMagicSchools($effMV), Util::asHex($effMV)).')';
                            break;
                        case 98:                            // Mod Skill Value
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' <a href="?skill='.$effMV.'">'.SkillList::getName($effMV).'</a> ('.$effMV.')';
                            break;
                        case 107:                           // Flat Modifier
                        case 108:                           // Pct Modifier
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, Util::$spellModOp[$effMV], $effMV).')';
                            break;
                        case 189:                           // Mod Rating
                        case 220:                           // Combat Rating From Stat
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, Util::$combatRating[log($effMV, 2)], Util::asHex($effMV)).')';
                            break;
                        case 168:                           // Mod Damage Done Versus
                        case 59:                            // Mod Damage Done Versus Creature
                            $bar = [];
                            foreach (Lang::$game['ct'] as $j => $str)
                                if ($effMV & (1 << $j - 1))
                                    $bar[] = $str;

                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, implode(', ', $bar), $effMV).')';
                            break;
                        case 249:                           // Convert Rune
                            $x = $spell->getField('effect'.$i.'MiscValueB');
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' ('.sprintf(Util::$dfnString, Lang::$spell['powerRunes'][$x], $x).')';
                            break;
                        case 78:                            // Mounted
                        case 56:                            // Transform
                        {
                            $transform = new CreatureList(array(['ct.entry', $effMV]));

                            if (!$pageData['view3D'] && $transform)
                                $pageData['view3D'] = $transform->getRandomModelId();

                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura].' <a href="?npc='.$effMV.'">'.$transform->getField('name', true).'</a> ('.$effMV.')';
                            break;
                        }
                        default:
                        {
                            $foo['name'] .= Lang::$colon.Util::$spellAuraStrings[$effAura];
                            if ($effMV > 0)
                                $foo['name'] .= ' ('.$effMV.')';
                        }
                    }

                    if (in_array($effAura, [174, 220, 182]))
                        $foo['name'] .= ' ['.sprintf(Util::$dfnString, Lang::$game['stats'][$spell->getField('effect'.$i.'MiscValueB')], $spell->getField('effect'.$i.'MiscValueB')).']';
                    else if ($spell->getField('effect'.$i.'MiscValueB') > 0)
                        $foo['name'] .= ' ['.$spell->getField('effect'.$i.'MiscValueB').']';

                }
                else if ($effAura > 0)
                    $foo['name'] .= ': Unknown Aura ('.$effAura.')';

                break;
            }
        }

        // cases where we dont want 'Value' to be displayed
        if (in_array($effAura, [11, 12, 36, 77]) || in_array($effId, []))
            unset($foo['value']);
    }

    $pageData['infobox'] = $infobox ? '[ul]'.implode('', $infobox).'[/ul]' : null;

    /*******
    * extra tabs
    *******/

    /* contains [Open Lock Item]

        effect.$i.Id = 59
        check spell_loot_template
        ['contains']
        reset icon..?
    *

    /* related spells
        at least 2 similar effects AND
        names identical AND
        ids not identical
    */
    $spellArr['seeAlso'] = [];

    /* source trainer
        first check source if not trainer :  break

        consult npc_trainer for details

        nyi: CreatureList
        ['taughtbynpc']
    */
    $spellArr['taughtByNpc'] = [];

    /* source item
        first check source if not item :  break

        spellid_1 = id OR spellid_1 = "LEAR_SPELL_GENERIC" AND spellid_2 = id
        spelltrigger_1/2 = 6

    */
    $spellArr['taughtbyitem'] = [];

    // check for taught by spells (Effect 36)
    // find associated NPC, Item and merge results
    // taughtbypets (unused..?)
    // taughtbyquest
    // taughtbytrainers
    // taughtbyitem

    /* used by npc
        first check cat if not npc-spell :  break

        stunt through the tables... >.<

    */
    $spellArr['usedbynpc'] = [];

    /* used by item


    */



    /* NEW
        is in stack-rule with X
        is linked with X
        scaling data
        conditions
        difficulty-versions
        spell_proc_data

    */


/*

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
        $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry']
    );
    if($usedbyitem)
    {
        $spellArr['usedbyitem'] = [];
        foreach($usedbyitem as $i => $row)
            $spellArr['usedbyitem'][] = iteminfo2($row, 0);
        unset($usedbyitem);
    }
*/
    // Используется наборами вещей:
    // $usedbyitemset = DB::Aowow()->select('
        // SELECT *
        // FROM ?_itemset
        // WHERE spell1 = ?d OR spell2 = ?d OR spell3 = ?d OR spell4 = ?d OR spell5 = ?d OR spell6 = ?d OR spell7 = ?d OR spell8 = ?d
        // ',
        // $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry'], $spellArr['entry']
    // );
    // if($usedbyitemset)
    // {
        // $spellArr['usedbyitemset'] = [];
        // foreach($usedbyitemset as $i => $row)
            // $spellArr['usedbyitemset'][] = itemsetinfo2($row);
        // unset($usedbyitemset);
    // }

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
        $spellArr['entry'], $spellArr['entry']
    );
    if($questreward)
    {
        $spellArr['questreward'] = [];
        foreach($questreward as $i => $row)
            $spellArr['questreward'][] = GetQuestInfo($row, 0xFFFFFF);
        unset($questreward);
    }
*/

    // Проверяем на пустые массивы
    // if(!$spellArr['taughtbyitem'])
        // unset($spellArr['taughtbyitem']);
    // if(!$spellArr['taughtbynpc'])
        // unset($spellArr['taughtbynpc']);

    // achievement criteria
    $rows = [];
    // DB::Aowow()->select('
            // SELECT a.id, a.faction, a.name_loc?d AS name, a.description_loc?d AS description, a.category, a.points, s.iconname, z.areatableID
            // FROM ?_spellicons s, ?_achievementcriteria c, ?_achievement a
            // LEFT JOIN (?_zones z) ON a.map != -1 AND a.map = z.mapID
            // WHERE
                // a.icon = s.id
                // AND a.id = c.refAchievement
                // AND c.type IN (?a)
                // AND c.value1 = ?d
            // GROUP BY a.id
            // ORDER BY a.name_loc?d
        // ',
        // User::$localeId,
        // User::$localeId,
        // array(
            // ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET, ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2,
            // ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL, ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2,
            // ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL
        // ),
        // $spellArr['entry'],
        // User::$localeId
    // );
    if($rows)
    {
        $spellArr['criteria_of'] = [];
        foreach($rows as $row)
        {
            allachievementsinfo2($spell->getField('id'));
            $spellArr['criteria_of'][] = achievementinfo2($row);
        }
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// menuId 1: Spell    g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => implode(" - ", $pageData['title']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_SPELL,
    'typeId' => $id,
    'reqJS'  => array (
            array('path' => 'template/js/swfobject.js')
    )
));

$smarty->assign('community', CommunityContent::getAll(TYPE_SPELL, $id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$spell, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// Mysql query execution statistics
$smarty->assign('mysql', DB::Aowow()->getStatistics());

// load the page
$smarty->display('spell.tpl');

?>
