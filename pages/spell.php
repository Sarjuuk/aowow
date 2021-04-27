<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 1: Spell    g_initPath()
//  tabId 0: Database g_initHeader()
class SpellPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_SPELL;
    protected $typeId        = 0;
    protected $tpl           = 'spell';
    protected $path          = [0, 1];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = ['swfobject.js'];

    private   $difficulties  = [];
    private   $firstRank     = 0;
    private   $powerTpl      = '$WowheadPower.registerSpell(%d, %d, %s);';

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new SpellList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('spell'), Lang::spell('notFound'));

        $jsg = $this->subject->getJSGlobals(GLOBALINFO_ANY, $extra);
        $this->extendGlobalData($jsg, $extra);

        $this->name = $this->subject->getField('name', true);

        // has difficulty versions of itself
        $this->difficulties = DB::Aowow()->selectRow(
            'SELECT normal10 AS "0", normal25 AS "1",
                    heroic10 AS "2", heroic25 AS "3"
             FROM   ?_spelldifficulty
             WHERE  normal10 = ?d OR normal25 = ?d OR
                    heroic10 = ?d OR heroic25 = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );

        // returns self or firstRank
        $this->firstRank = DB::Aowow()->selectCell(
           'SELECT      IF(s1.RankNo <> 1 AND s2.id, s2.id, s1.id)
            FROM        ?_spell s1
            LEFT JOIN   ?_spell s2
                ON      s1.SpellFamilyId     = s2.SpelLFamilyId AND     s1.SpellFamilyFlags1 = s2.SpelLFamilyFlags1 AND
                        s1.SpellFamilyFlags2 = s2.SpellFamilyFlags2 AND s1.SpellFamilyFlags3 = s2.SpellFamilyFlags3 AND
                        s1.name_loc0 = s2.name_loc0                 AND s2.RankNo = 1
            WHERE       s1.id = ?d',
            $this->typeId
        );
    }

    protected function generatePath()
    {
        $cat = $this->subject->getField('typeCat');
        $cf  = $this->subject->getField('cuFlags');

        $this->path[] = $cat;

        // reconstruct path
        switch ($cat)
        {
            case  -2:
            case   7:
            case -13:
                if ($cl = $this->subject->getField('reqClassMask'))
                    $this->path[] = log($cl, 2) + 1;
                else if ($cl = array_search($this->subject->getField('spellFamilyId'), Game::$class2SpellFamily))
                    $this->path[] = $cl;

                if ($cat == -13)
                    $this->path[] = ($cf & (SPELL_CU_GLYPH_MAJOR | SPELL_CU_GLYPH_MINOR)) >> 6;
                else
                    $this->path[] = $this->subject->getField('skillLines')[0];

                break;
            case   9:
            case  -3:
            case  11:
                $this->path[] = $this->subject->getField('skillLines')[0];

                if ($cat == 11)
                    if ($_ = $this->subject->getField('reqSpellId'))
                        $this->path[] = $_;

                break;
            case -11:
                foreach (SpellList::$skillLines as $line => $skills)
                    if (in_array($this->subject->getField('skillLines')[0], $skills))
                        $this->path[] = $line;
                break;
            case  -7:                                       // only spells unique in skillLineAbility will always point to the right skillLine :/
                if ($cf & SPELL_CU_PET_TALENT_TYPE0)
                    $this->path[] = 411;                    // Ferocity
                else if ($cf & SPELL_CU_PET_TALENT_TYPE1)
                    $this->path[] = 409;                    // Tenacity
                else if ($cf & SPELL_CU_PET_TALENT_TYPE2)
                    $this->path[] = 410;                    // Cunning
                break;
            case -5:
                if ($this->subject->getField('effect2AuraId') == 207 || $this->subject->getField('effect3AuraId') == 207)
                    $this->path[] = 2;                      // flying (also contains 32, so checked first)
                else if ($this->subject->getField('effect2AuraId') == 32 || $this->subject->getField('effect3AuraId') == 32)
                    $this->path[] = 1;                      // ground
                else
                    $this->path[] = 3;                      // misc
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('spell')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $_cat = $this->subject->getField('typeCat');

        $redButtons = array(
            BUTTON_VIEW3D  => false,
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => array(
                'linkColor' => 'ff71d5ff',
                'linkId'    => Util::$typeStrings[TYPE_SPELL].':'.$this->typeId,
                'linkName'  => $this->name,
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // level
        if (!in_array($_cat, [-5, -6]))                     // not mount or vanity pet
        {
            if ($_ = $this->subject->getField('talentLevel'))
                $infobox[] = (in_array($_cat, [-2, 7, -13]) ? sprintf(Lang::game('reqLevel'), $_) : Lang::game('level').Lang::main('colon').$_);
            else if ($_ = $this->subject->getField('spellLevel'))
                $infobox[] = (in_array($_cat, [-2, 7, -13]) ? sprintf(Lang::game('reqLevel'), $_) : Lang::game('level').Lang::main('colon').$_);
        }

        $jsg = [];
        // races
        if ($_ = Lang::getRaceString($this->subject->getField('reqRaceMask'), $jsg, false))
        {
            $this->extendGlobalIds(TYPE_RACE, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('race') : Lang::game('races');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // classes
        if ($_ = Lang::getClassString($this->subject->getField('reqClassMask'), $jsg, false))
        {
            $this->extendGlobalIds(TYPE_CLASS, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // spell focus
        if ($_ = $this->subject->getField('spellFocusObject'))
        {
            $bar = DB::Aowow()->selectRow('SELECT * FROM ?_spellfocusobject WHERE id = ?d', $_);
            $focus = new GameObjectList(array(['spellFocusId', $_], 1));
            $infobox[] = Lang::game('requires2').' '.($focus->error ? Util::localizedString($bar, 'name') : '[url=?object='.$focus->id.']'.Util::localizedString($bar, 'name').'[/url]');
        }

        // primary & secondary trades
        if (in_array($_cat, [9, 11]))
        {
            // skill
            if ($_ = $this->subject->getField('skillLines')[0])
            {
                $rSkill = new SkillList(array(['id', $_]));
                if (!$rSkill->error)
                {
                    $this->extendGlobalData($rSkill->getJSGlobals());

                    $bar = sprintf(Lang::game('requires'), '&nbsp;[skill='.$rSkill->id.']');
                    if ($_ = $this->subject->getField('learnedAt'))
                        $bar .= ' ('.$_.')';

                    $infobox[] = $bar;
                }
            }

            // specialization
            if ($_ = $this->subject->getField('reqSpellId'))
            {
                $rSpell = new SpellList(array(['id', $_]));
                if (!$rSpell->error)
                {
                    $this->extendGlobalData($rSpell->getJSGlobals());
                    $infobox[] = Lang::game('requires2').' [spell='.$rSpell->id.'][/li]';
                }
            }

            // difficulty
            if ($_ = $this->subject->getColorsForCurrent())
                $infobox[] = Lang::formatSkillBreakpoints($_);
        }

        // accquisition..   10: starter spell; 7: discovery
        if (isset($this->subject->sources[$this->subject->id][10]))
            $infobox[] = Lang::spell('starter');
        else if (isset($this->subject->sources[$this->subject->id][7]))
            $infobox[] = Lang::spell('discovered');

        // training cost
        if ($cost = $this->subject->getField('trainingCost'))
            $infobox[] = Lang::spell('trainingCost').Lang::main('colon').'[money='.$cost.'][/li]';

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(TYPE_ICON, $_);
        }

        // used in mode
        foreach ($this->difficulties as $n => $id)
            if ($id == $this->typeId)                       // "Mode" seems to be multilingual acceptable
                $infobox[] = 'Mode'.Lang::main('colon').Lang::game('modes', $n);

        $effects = $this->createEffects($infobox, $redButtons);

        // spell script
        if (User::isInGroup(U_GROUP_STAFF))
            if ($_ = DB::World()->selectCell('SELECT ScriptName FROM spell_script_names WHERE ABS(spell_id) = ?d', $this->firstRank))
                $infobox[] = 'Script'.Lang::main('colon').$_;

        $infobox = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : '';

        // append glyph symbol if available
        $glyphId = 0;
        for ($i = 1; $i < 4; $i++)
            if ($this->subject->getField('effect'.$i.'Id') == 74)
                $glyphId = $this->subject->getField('effect'.$i.'MiscValue');

        if ($_ = DB::Aowow()->selectCell('SELECT ic.name FROM ?_glyphproperties gp JOIN ?_icons ic ON gp.iconId = ic.id WHERE gp.spellId = ?d { OR gp.id = ?d }', $this->typeId, $glyphId ?: DBSIMPLE_SKIP))
            if (file_exists('static/images/wow/Interface/Spellbook/'.$_.'.png'))
                $infobox .= '[img src='.STATIC_URL.'/images/wow/Interface/Spellbook/'.$_.'.png border=0 float=center margin=15]';


        /****************/
        /* Main Content */
        /****************/

        $this->reagents    = $this->createReagentList();
        $this->scaling     = $this->createScalingData();
        $this->items       = $this->createRequiredItems();
        $this->tools       = $this->createTools();
        $this->effects     = $effects;
        $this->attributes  = $this->createAttributesList();
        $this->infobox     = $infobox;
        $this->powerCost   = $this->subject->createPowerCostForCurrent();
        $this->castTime    = $this->subject->createCastTimeForCurrent(false, false);
        $this->name        = $this->subject->getField('name', true);
        $this->headIcons   = [$this->subject->getField('iconString'), $this->subject->getField('stackAmount') ?: ($this->subject->getField('procCharges') > 1 ? $this->subject->getField('procCharges') : '')];
        $this->level       = $this->subject->getField('spellLevel');
        $this->rangeName   = $this->subject->getField('rangeText', true);
        $this->range       = $this->subject->getField('rangeMaxHostile');
        $this->gcd         = Util::formatTime($this->subject->getField('startRecoveryTime'));
        $this->gcdCat      = null;                          // todo (low): nyi; find out how this works [n/a; normal; ..]
        $this->school      = [Util::asHex($this->subject->getField('schoolMask')), Lang::getMagicSchools($this->subject->getField('schoolMask'))];
        $this->dispel      = $this->subject->getField('dispelType') ? Lang::game('dt', $this->subject->getField('dispelType')) : null;
        $this->mechanic    = $this->subject->getField('mechanic') ? Lang::game('me', $this->subject->getField('mechanic')) : null;
        $this->redButtons  = $redButtons;

        // minRange exists..  prepend
        if ($_ = $this->subject->getField('rangeMinHostile'))
            $this->range = $_.' - '.$this->range;

        if (!($this->subject->getField('attributes2') & 0x80000))
            $this->stances = Lang::getStances($this->subject->getField('stanceMask'));

        if (($_ = $this->subject->getField('recoveryTime')) && $_ > 0)
            $this->cooldown = Util::formatTime($_);
        else if (($_ = $this->subject->getField('recoveryCategory')) && $_ > 0)
            $this->cooldown = Util::formatTime($_);

        if (($_ = $this->subject->getField('duration')) && $_ > 0)
            $this->duration = Util::formatTime($_);

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_spells WHERE alliance_id = ?d OR horde_id = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altSpell = new SpellList(array(['id', abs($pendant)]));
            if (!$altSpell->error)
            {
                $this->transfer = sprintf(
                    Lang::spell('_transfer'),
                    $altSpell->id,
                    1,                                      // quality
                    $altSpell->getField('iconString'),
                    $altSpell->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', 1) : Lang::game('si', 2)
                );
            }
        }

        /**************/
        /* Extra Tabs */
        /**************/

        $j = [null, 'A', 'B', 'C'];

        $ubSAI = SmartAI::getOwnerOfSpellCast($this->typeId);

        // tab: abilities [of shapeshift form]
        for ($i = 1; $i < 4; $i++)
        {
            if ($this->subject->getField('effect'.$i.'AuraId') != 36)
                continue;

            $formSpells = DB::Aowow()->selectRow('SELECT spellId1, spellId2, spellId3, spellId4, spellId5, spellId6, spellId7, spellId8 FROM ?_shapeshiftforms WHERE id = ?d', $this->subject->getField('effect'.$i.'MiscValue'));
            if (!$formSpells)
                continue;

            $abilities = new SpellList(array(['id', $formSpells]));
            if (!$abilities->error)
            {
                $tabData = array(
                    'data'        => array_values($abilities->getListviewData()),
                    'id'          => 'controlledabilities',
                    'name'        => '$LANG.tab_controlledabilities',
                    'visibleCols' => ['level'],
                );

                if (!$abilities->hasSetFields(['skillLines']))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = ['spell', $tabData];

                $this->extendGlobalData($abilities->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: [$this] modifies
        $sub = ['OR'];
        $conditions = [
            ['s.typeCat', [0, -9, -8], '!'],                // uncategorized (0), GM (-9), NPC-Spell (-8); NPC includes totems, lightwell and others :/
            ['s.spellFamilyId', $this->subject->getField('spellFamilyId')],
            &$sub
        ];

        for ($i = 1; $i < 4; $i++)
        {
            // include dummy..? (4)
            if (!in_array($this->subject->getField('effect'.$i.'AuraId'), [107, 108, 256, 286, 195, 262, 263, 272, 274, 275, 316 /*, 4*/]))
                continue;

            $m1 = $this->subject->getField('effect1SpellClassMask'.$j[$i]);
            $m2 = $this->subject->getField('effect2SpellClassMask'.$j[$i]);
            $m3 = $this->subject->getField('effect3SpellClassMask'.$j[$i]);

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
                $tabData = array(
                    'data'        => array_values($modSpells->getListviewData()),
                    'id'          => 'modifies',
                    'name'        => '$LANG.tab_modifies',
                    'visibleCols' => ['level'],
                );

                if (!$modSpells->hasSetFields(['skillLines']))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = ['spell', $tabData];

                $this->extendGlobalData($modSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tab: [$this is] modified by
        $sub = ['OR'];
        $conditions = [
            ['s.spellFamilyId', $this->subject->getField('spellFamilyId')],
            &$sub
        ];

        for ($i = 1; $i < 4; $i++)
        {
            $m1 = $this->subject->getField('spellFamilyFlags1');
            $m2 = $this->subject->getField('spellFamilyFlags2');
            $m3 = $this->subject->getField('spellFamilyFlags3');

            if (!$m1 && !$m2 && !$m3)
                continue;

            $sub[] = array(
                'AND',
                ['s.effect'.$i.'AuraId', [107, 108, 256, 286, 195, 262, 263, 272, 274, 275, 316 /*, 4*/]],
                [
                    'OR',
                    ['s.effect1SpellClassMask'.$j[$i], $m1, '&'],
                    ['s.effect2SpellClassMask'.$j[$i], $m2, '&'],
                    ['s.effect3SpellClassMask'.$j[$i], $m3, '&']
                ]
            );
        }

        if (count($sub) > 1)
        {
            $modsSpell = new SpellList($conditions);
            if (!$modsSpell->error)
            {
                $tabData = array(
                    'data'        => array_values($modsSpell->getListviewData()),
                    'id'          => 'modified-by',
                    'name'        => '$LANG.tab_modifiedby',
                    'visibleCols' => ['level'],
                );

                if (!$modsSpell->hasSetFields(['skillLines']))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = ['spell', $tabData];

                $this->extendGlobalData($modsSpell->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tab: see also
        $conditions = array(
            ['s.schoolMask', $this->subject->getField('schoolMask')],
            ['s.effect1Id', $this->subject->getField('effect1Id')],
            ['s.effect2Id', $this->subject->getField('effect2Id')],
            ['s.effect3Id', $this->subject->getField('effect3Id')],
            ['s.id', $this->subject->id, '!'],
            ['s.name_loc'.User::$localeId, $this->subject->getField('name', true)]
        );

        $saSpells = new SpellList($conditions);
        if (!$saSpells->error)
        {
            $data = $saSpells->getListviewData();
            if ($this->difficulties)                        // needs a way to distinguish between dungeon and raid :x; creature using this -> map -> areaType?
            {
                $saE = ['$Listview.extraCols.mode'];

                foreach ($data as $id => &$d)
                {
                    $d['modes'] = ['mode' => 0];

                    if ($this->difficulties[0] == $id)      // b0001000
                    {
                        if (!$this->difficulties[2] && !$this->difficulties[3])
                            $d['modes']['mode'] |= 0x2;
                        else
                            $d['modes']['mode'] |= 0x8;
                    }

                    if ($this->difficulties[1] == $id)      // b0010000
                    {
                        if (!$this->difficulties[2] && !$this->difficulties[3])
                            $d['modes']['mode'] |= 0x1;
                        else
                            $d['modes']['mode'] |= 0x10;
                    }

                    if ($this->difficulties[2] == $id)      // b0100000
                        $d['modes']['mode'] |= 0x20;

                    if ($this->difficulties[3] == $id)      // b1000000
                        $d['modes']['mode'] |= 0x40;
                }
            }

            $tabData = array(
                'data'        => array_values($data),
                'id'          => 'see-also',
                'name'        => '$LANG.tab_seealso',
                'visibleCols' => ['level'],
            );

            if (!$saSpells->hasSetFields(['skillLines']))
                $tabData['hiddenCols'] = ['skill'];

            if (isset($saE))
                $tabData['extraCols'] = $saE;

            $this->lvTabs[] = ['spell', $tabData];

            $this->extendGlobalData($saSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }

        // tab: shared cooldown
        if ($this->subject->getField('recoveryCategory'))
        {
            $conditions = array(
                ['id', $this->typeId, '!'],
                ['category', $this->subject->getField('category')],
                ['recoveryCategory', 0, '>'],
            );

            // limit shared cooldowns to same player class for regulat users
            if (!User::isInGroup(U_GROUP_STAFF) && $this->subject->getField('spellFamilyId'))
                $conditions[] = ['spellFamilyId', $this->subject->getField('spellFamilyId')];

            $cdSpells = new SpellList($conditions);
            if (!$cdSpells->error)
            {
                $this->lvTabs[] = ['spell', array(
                    'data' => array_values($cdSpells->getListviewData()),
                    'name' => '$LANG.tab_sharedcooldown',
                    'id'   => 'shared-cooldown'
                )];

                $this->extendGlobalData($cdSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tab: used by - spell
        if ($so = DB::Aowow()->selectCell('SELECT id FROM ?_spelloverride WHERE spellId1 = ?d OR spellId2 = ?d OR spellId3 = ?d OR spellId4 = ?d OR spellId5 = ?d', $this->subject->id, $this->subject->id, $this->subject->id, $this->subject->id, $this->subject->id))
        {
            $conditions = array(
                'OR',
                ['AND', ['effect1AuraId', 293], ['effect1MiscValue', $so]],
                ['AND', ['effect2AuraId', 293], ['effect2MiscValue', $so]],
                ['AND', ['effect3AuraId', 293], ['effect3MiscValue', $so]]
            );
            $ubSpells = new SpellList($conditions);
            if (!$ubSpells->error)
            {
                $this->lvTabs[] = ['spell', array(
                    'data' => array_values($ubSpells->getListviewData()),
                    'id'   => 'used-by-spell',
                    'name' => '$LANG.tab_usedby'
                )];

                $this->extendGlobalData($ubSpells->getJSGlobals(GLOBALINFO_SELF));
            }
        }


        // tab: used by - itemset
        $conditions = array(
            'OR',
            ['spell1', $this->subject->id], ['spell2', $this->subject->id], ['spell3', $this->subject->id], ['spell4', $this->subject->id],
            ['spell5', $this->subject->id], ['spell6', $this->subject->id], ['spell7', $this->subject->id], ['spell8', $this->subject->id]
        );

        $ubSets = new ItemsetList($conditions);
        if (!$ubSets->error)
        {
            $this->lvTabs[] = ['itemset', array(
                'data' => array_values($ubSets->getListviewData()),
                'id'   => 'used-by-itemset',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubSets->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }

        // tab: used by - item
        $conditions = array(
            'OR',                  // 6: learn spell
            ['AND', ['spellTrigger1', 6, '!'], ['spellId1', $this->subject->id]],
            ['AND', ['spellTrigger2', 6, '!'], ['spellId2', $this->subject->id]],
            ['AND', ['spellTrigger3', 6, '!'], ['spellId3', $this->subject->id]],
            ['AND', ['spellTrigger4', 6, '!'], ['spellId4', $this->subject->id]],
            ['AND', ['spellTrigger5', 6, '!'], ['spellId5', $this->subject->id]]
        );

        $ubItems = new ItemList($conditions);
        if (!$ubItems->error)
        {
            $this->lvTabs[] = ['item', array(
                'data' => array_values($ubItems->getListviewData()),
                'id'   => 'used-by-item',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubItems->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: used by - object
        $conditions = array(
            'OR',
            ['onUseSpell', $this->subject->id], ['onSuccessSpell', $this->subject->id],
            ['auraSpell',  $this->subject->id], ['triggeredSpell', $this->subject->id]
        );
        if (!empty($ubSAI[TYPE_OBJECT]))
            $conditions[] = ['id', $ubSAI[TYPE_OBJECT]];

        $ubObjects = new GameObjectList($conditions);
        if (!$ubObjects->error)
        {
            $this->lvTabs[] = ['object', array(
                'data' => array_values($ubObjects->getListviewData()),
                'id'   => 'used-by-object',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubObjects->getJSGlobals());
        }

        // tab: used by - areatrigger
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            if (!empty($ubSAI[TYPE_AREATRIGGER]))
            {
                $ubTriggers = new AreaTriggerList(array(['id', $ubSAI[TYPE_AREATRIGGER]]));
                if (!$ubTriggers->error)
                {
                    $this->lvTabs[] = ['areatrigger', array(
                        'data' => array_values($ubTriggers->getListviewData()),
                        'id'   => 'used-by-areatrigger',
                        'name' => '$LANG.tab_usedby'
                    ), 'areatrigger'];
                }
            }
        }

        // tab: criteria of
        $conditions = array(
            ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET, ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2, ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL,
                         ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2,     ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL]
            ],
            ['ac.value1', $this->typeId]
        );
        $coAchievemnts = new AchievementList($conditions);
        if (!$coAchievemnts->error)
        {
            $this->lvTabs[] = ['achievement', array(
                'data' => array_values($coAchievemnts->getListviewData()),
                'id'   => 'criteria-of',
                'name' => '$LANG.tab_criteriaof'
            )];

            $this->extendGlobalData($coAchievemnts->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }

        // tab: contains
        // spell_loot_template & skill_extra_item_template
        $extraItem = DB::World()->selectRow('SELECT * FROM skill_extra_item_template WHERE spellid = ?d', $this->subject->id);
        $spellLoot = new Loot();

        if ($spellLoot->getByContainer(LOOT_SPELL, $this->subject->id) || $extraItem)
        {
            $this->extendGlobalData($spellLoot->jsGlobals);

            $lv = $spellLoot->getResult();
            $extraCols   = $spellLoot->extraCols;
            $extraCols[] = '$Listview.extraCols.percent';

            if ($extraItem && $this->subject->canCreateItem())
            {
                $foo = $this->subject->relItems->getListviewData();

                for ($i = 1; $i < 4; $i++)
                {
                    if (($bar = $this->subject->getField('effect'.$i.'CreateItemId')) && isset($foo[$bar]))
                    {
                        $lv[$bar] = $foo[$bar];
                        $lv[$bar]['percent'] = $extraItem['additionalCreateChance'];
                        $lv[$bar]['condition'][0][$this->typeId][] = [[CND_SPELL, $extraItem['requiredSpecialization']]];
                        $this->extendGlobalIds(TYPE_SPELL, $extraItem['requiredSpecialization']);
                        $extraCols[] = '$Listview.extraCols.condition';
                        if ($max = ($extraItem['additionalMaxNum'] - 1))
                            $lv[$bar]['stack'] = [1, $max];

                        break;                              // skill_extra_item_template can only contain 1 item
                    }
                }
            }

            $this->lvTabs[] = ['item', array(
                'data'       => array_values($lv),
                'name'       => '$LANG.tab_contains',
                'id'         => 'contains',
                'hiddenCols' => ['side', 'slot', 'source', 'reqlevel'],
                'extraCols'  => array_unique($extraCols)
            )];
        }

        // tab: exclusive with
        if ($this->firstRank) {
            $linkedSpells = DB::World()->selectCol(         // dont look too closely ..... please..?
               'SELECT      IF(sg2.spell_id < 0, sg2.id, sg2.spell_id) AS ARRAY_KEY, IF(sg2.spell_id < 0, sg2.spell_id, sr.stack_rule)
                FROM        spell_group sg1
                JOIN        spell_group sg2
                    ON      (sg1.id = sg2.id OR sg1.id = -sg2.spell_id) AND sg1.spell_id != sg2.spell_id
                LEFT JOIN   spell_group_stack_rules sr
                    ON      sg1.id = sr.group_id
                WHERE       sg1.spell_id = ?d',
                $this->firstRank
            );

            if ($linkedSpells)
            {
                $extraSpells = [];
                foreach ($linkedSpells as $k => $v)
                {
                    if ($v > 0)
                        continue;

                    $extraSpells += DB::World()->selectCol( // recursive case (recursive and regular ids are not mixed in a group)
                       'SELECT      sg2.spell_id AS ARRAY_KEY, sr.stack_rule
                        FROM        spell_group sg1
                        JOIN        spell_group sg2
                            ON      sg2.id = -sg1.spell_id AND sg2.spell_id != ?d
                        LEFT JOIN   spell_group_stack_rules sr
                            ON      sg1.id = sr.group_id
                        WHERE       sg1.id = ?d',
                        $this->firstRank,
                        $k
                    );

                    unset($linkedSpells[$k]);
                }

                // todo (high): fixme - querys have erronous edge-cases (see spell: 13218)
                if ($groups = $linkedSpells + $extraSpells)
                {
                    $stacks = new SpellList(array(['s.id', array_keys($groups)]));
                    if (!$stacks->error)
                    {
                        $data = $stacks->getListviewData();
                        foreach ($data as $k => $d)
                            $data[$k]['stackRule'] = $groups[$k];

                        if (!$stacks->hasSetFields(['skillLines']))
                            $sH = ['skill'];

                        $tabData = array(
                            'data'        => array_values($data),
                            'id'          => 'spell-group-stack',
                            'name'        => Lang::spell('stackGroup'),
                            'visibleCols' => ['stackRules']
                        );

                        if (isset($sH))
                            $tabData['hiddenCols'] = $sH;

                        $this->lvTabs[] = ['spell', $tabData];

                        $this->extendGlobalData($stacks->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                    }
                }
            }
        }

        // tab: linked with
        $rows = DB::World()->select('
            SELECT  spell_trigger AS `trigger`,
                    spell_effect AS effect,
                    type,
                    IF(ABS(spell_effect) = ?d, ABS(spell_trigger), ABS(spell_effect)) AS related
            FROM    spell_linked_spell
            WHERE   ABS(spell_effect) = ?d OR ABS(spell_trigger) = ?d',
            $this->typeId, $this->typeId, $this->typeId
        );

        $related = [];
        foreach ($rows as $row)
            $related[] = $row['related'];

        if ($related)
            $linked = new SpellList(array(['s.id', $related]));

        if (isset($linked) && !$linked->error)
        {
            $lv   = $linked->getListviewData();
            $data = [];

            foreach ($rows as $r)
            {
                foreach ($lv as $dk => $d)
                {
                    if ($r['related'] != $dk)
                        continue;

                    $lv[$dk]['linked'] = [$r['trigger'], $r['effect'], $r['type']];
                    $data[] = $lv[$dk];
                    break;
                }
            }

            $this->lvTabs[] = ['spell', array(
                'data'        => array_values($data),
                'id'          => 'spell-link',
                'name'        => Lang::spell('linkedWith'),
                'hiddenCols'  => ['skill', 'name'],
                'visibleCols' => ['linkedTrigger', 'linkedEffect']
            )];

            $this->extendGlobalData($linked->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }


        // tab: triggered by
        $conditions = array(
            'OR',
            ['AND', ['OR', ['effect1Id', SpellList::$effects['trigger']], ['effect1AuraId', SpellList::$auras['trigger']]], ['effect1TriggerSpell', $this->subject->id]],
            ['AND', ['OR', ['effect2Id', SpellList::$effects['trigger']], ['effect2AuraId', SpellList::$auras['trigger']]], ['effect2TriggerSpell', $this->subject->id]],
            ['AND', ['OR', ['effect3Id', SpellList::$effects['trigger']], ['effect3AuraId', SpellList::$auras['trigger']]], ['effect3TriggerSpell', $this->subject->id]],
        );

        $trigger = new SpellList($conditions);
        if (!$trigger->error)
        {
            $this->lvTabs[] = ['spell', array(
                'data' => array_values($trigger->getListviewData()),
                'id'   => 'triggered-by',
                'name' => '$LANG.tab_triggeredby'
            )];

            $this->extendGlobalData($trigger->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: used by - creature
        $conditions = array(
            'OR',
            ['spell1', $this->typeId], ['spell2', $this->typeId], ['spell3', $this->typeId], ['spell4', $this->typeId],
            ['spell5', $this->typeId], ['spell6', $this->typeId], ['spell7', $this->typeId], ['spell8', $this->typeId]
        );
        if (!empty($ubSAI[TYPE_NPC]))
            $conditions[] = ['id', $ubSAI[TYPE_NPC]];

        $ubCreature = new CreatureList($conditions);
        if (!$ubCreature->error)
        {
            $this->lvTabs[] = ['creature', array(
                'data' => array_values($ubCreature->getListviewData()),
                'id'   => 'used-by-npc',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubCreature->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: zone
        if ($areas = DB::World()->select('SELECT * FROM spell_area WHERE spell = ?d', $this->typeId))
        {
            $zones = new ZoneList(array(['id', array_column($areas, 'area')]));
            if (!$zones->error)
            {
                $lvZones = $zones->getListviewData();
                $this->extendGlobalData($zones->getJSGlobals());

                $lv      = [];
                $parents = [];
                $extra   = false;
                foreach ($areas as $a)
                {
                    if (empty($lvZones[$a['area']]))
                        continue;

                    $condition = [];
                    if ($a['aura_spell'])
                    {
                        $this->extendGlobalIds(TYPE_SPELL, abs($a['aura_spell']));
                        $condition[0][$this->typeId][] = [[$a['aura_spell'] >  0 ? CND_AURA : -CND_AURA, abs($a['aura_spell'])]];
                    }

                    if ($a['quest_start'])                  // status for quests needs work
                    {
                        $this->extendGlobalIds(TYPE_QUEST, $a['quest_start']);
                        $group = [];
                        for ($i = 0; $i < 7; $i++)
                        {
                            if (!($a['quest_start_status'] & (1 << $i)))
                                continue;

                            if ($i == 0)
                                $group[] = [CND_QUEST_NONE, $a['quest_start']];
                            else if ($i == 1)
                                $group[] = [CND_QUEST_COMPLETE, $a['quest_start']];
                            else if ($i == 3)
                                $group[] = [CND_QUESTTAKEN, $a['quest_start']];
                            else if ($i == 6)
                                $group[] = [CND_QUESTREWARDED, $a['quest_start']];
                        }

                        if ($group)
                            $condition[0][$this->typeId][] = $group;
                    }

                    if ($a['quest_end'] && $a['quest_end'] != $a['quest_start'])
                    {
                        $this->extendGlobalIds(TYPE_QUEST, $a['quest_end']);
                        $group = [];
                        for ($i = 0; $i < 7; $i++)
                        {
                            if (!($a['quest_end_status'] & (1 << $i)))
                                continue;

                            if ($i == 0)
                                $group[] = [-CND_QUEST_NONE, $a['quest_end']];
                            else if ($i == 1)
                                $group[] = [-CND_QUEST_COMPLETE, $a['quest_end']];
                            else if ($i == 3)
                                $group[] = [-CND_QUESTTAKEN, $a['quest_end']];
                            else if ($i == 6)
                                $group[] = [-CND_QUESTREWARDED, $a['quest_end']];
                        }

                        if ($group)
                            $condition[0][$this->typeId][] = $group;
                    }

                    if ($a['racemask'])
                    {
                        $foo = [];
                        for ($i = 0; $i < 11; $i++)
                            if ($a['racemask'] & (1 << $i))
                                $foo[] = $i + 1;

                        $this->extendGlobalIds(TYPE_RACE, ...$foo);
                        $condition[0][$this->typeId][] = [[CND_RACE, $a['racemask']]];
                    }

                    if ($a['gender'] != 2)                  // 2: both
                        $condition[0][$this->typeId][] = [[CND_GENDER, $a['gender'] + 1]];

                    $row = $lvZones[$a['area']];
                    if ($condition)
                    {
                        $extra = true;
                        $row   = array_merge($row, ['condition' => $condition]);
                    }

                    // merge subzones, into one row, if: conditions match && parentZone is shared
                    if ($p = $zones->getEntry($a['area'])['parentArea'])
                    {
                        $parents[] = $p;
                        $row['parentArea'] = $p;
                        $row['subzones']   = [$a['area']];
                    }
                    else
                        $row['parentArea'] = 0;

                    $set = false;
                    foreach ($lv as &$v)
                    {
                        if ($v['parentArea'] != $row['parentArea'] && $v['id'] != $row['parentArea'])
                            continue;

                        if (empty($v['condition']) xor empty($row['condition']))
                            continue;

                        if (!empty($row['condition']) && !empty($v['condition']) && $v['condition'] != $row['condition'])
                            continue;

                        if (!$row['parentArea'] && $v['id'] != $row['parentArea'])
                            continue;

                        $set = true;
                        $v['subzones'][] = $row['id'];
                        break;
                    }

                    // add self as potential subzone; IF we are a parentZone without added children, we get filtered in JScript
                    if (!$set)
                    {
                        $row['subzones'] = [$row['id']];
                        $lv[] = $row;
                    }
                }

                // overwrite lvData with parent-lvData (condition and subzones are kept)
                if ($parents)
                {
                    $parents = (new ZoneList(array(['id', $parents])))->getListviewData();
                    foreach ($lv as &$_)
                        if (isset($parents[$_['parentArea']]))
                            $_ = array_merge($_, $parents[$_['parentArea']]);
                }

                $tabData = ['data' => array_values($lv)];

                if ($extra)
                {
                    $tabData['extraCols']  = ['$Listview.extraCols.condition'];
                    $tabData['hiddenCols'] = ['instancetype'];
                }

                $this->lvTabs[] = ['zone', $tabData];
            }
        }

        // tab: teaches
        if ($ids = Game::getTaughtSpells($this->subject))
        {
            $teaches = new SpellList(array(['id', $ids]));
            if (!$teaches->error)
            {
                $this->extendGlobalData($teaches->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                $vis = ['level', 'schools'];

                foreach ($teaches->iterate() as $__)
                {
                    if (!$teaches->canCreateItem())
                        continue;

                    $vis[] = 'reagents';
                    break;
                }

                $tabData = array(
                    'data'        => array_values($teaches->getListviewData()),
                    'id'          => 'teaches-spell',
                    'name'        => '$LANG.tab_teaches',
                    'visibleCols' => $vis,
                );

                if (!$teaches->hasSetFields(['skillLines']))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = ['spell', $tabData];
            }
        }

        // tab: taught by npc (source:6 => trainer)
        if (!empty($this->subject->sources[$this->typeId][6]))
        {
            $src  = $this->subject->sources[$this->typeId][6];
            $list = [];
            if (count($src) == 1 && $src[0] == 1)           // multiple trainer
            {
                $list = DB::World()->selectCol('
                    SELECT  cdt.CreatureId
                    FROM    creature_default_trainer cdt
                    JOIN    trainer_spell ts ON ts.TrainerId = cdt.TrainerId
                    WHERE   ts.SpellId = ?d',
                    $this->typeId
                );
            }
            else if ($src)
                $list = array_values($src);

            if ($list)
            {
                $tbTrainer = new CreatureList(array(CFG_SQL_LIMIT_NONE, ['ct.id', $list], ['s.guid', null, '!'], ['ct.npcflag', 0x10, '&']));
                if (!$tbTrainer->error)
                {
                    $this->extendGlobalData($tbTrainer->getJSGlobals());
                    $this->lvTabs[] = ['creature', array(
                        'data' => array_values($tbTrainer->getListviewData()),
                        'id'   => 'taught-by-npc',
                        'name' => '$LANG.tab_taughtby',
                    )];
                }
            }
        }

        // tab: taught by spell
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', SpellList::$effects['teach']], ['effect1TriggerSpell', $this->subject->id]],
            ['AND', ['effect2Id', SpellList::$effects['teach']], ['effect2TriggerSpell', $this->subject->id]],
            ['AND', ['effect3Id', SpellList::$effects['teach']], ['effect3TriggerSpell', $this->subject->id]],
        );

        $tbSpell = new SpellList($conditions);
        $tbsData = [];
        if (!$tbSpell->error)
        {
            $tbsData = $tbSpell->getListviewData();
            $this->lvTabs[] = ['spell', array(
                'data' => array_values($tbsData),
                'id'   => 'taught-by-spell',
                'name' => '$LANG.tab_taughtby'
            )];

            $this->extendGlobalData($tbSpell->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: taught by quest
        $conditions = ['OR', ['sourceSpellId', $this->typeId], ['rewardSpell', $this->typeId]];
        if ($tbsData)
        {
            $conditions[] = ['rewardSpell', array_keys($tbsData)];
            if (User::isInGroup(U_GROUP_EMPLOYEE))
                $conditions[] = ['rewardSpellCast', array_keys($tbsData)];
        }
        if (User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = ['rewardSpellCast', $this->typeId];

        $tbQuest = new QuestList($conditions);
        if (!$tbQuest->error)
        {
            $this->lvTabs[] = ['quest', array(
                'data' => array_values($tbQuest->getListviewData()),
                'id'   => 'reward-from-quest',
                'name' => '$LANG.tab_rewardfrom'
            )];

            $this->extendGlobalData($tbQuest->getJSGlobals());
        }

        // tab: taught by item (i'd like to precheck $this->subject->sources, but there is no source:item only complicated crap like "drop" and "vendor")
        $conditions = array(
            'OR',
            ['AND', ['spellTrigger1', 6], ['spellId1', $this->subject->id]],
            ['AND', ['spellTrigger2', 6], ['spellId2', $this->subject->id]],
            ['AND', ['spellTrigger3', 6], ['spellId3', $this->subject->id]],
            ['AND', ['spellTrigger4', 6], ['spellId4', $this->subject->id]],
            ['AND', ['spellTrigger5', 6], ['spellId5', $this->subject->id]],
        );

        $tbItem = new ItemList($conditions);
        if (!$tbItem->error)
        {
            $this->lvTabs[] = ['item', array(
                'data' => array_values($tbItem->getListviewData()),
                'id'   => 'taught-by-item',
                'name' => '$LANG.tab_taughtby'
            )];

            $this->extendGlobalData($tbItem->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: enchantments
        $conditions = array(
            'OR',
            ['AND', ['type1', [1, 3, 7]], ['object1', $this->typeId]],
            ['AND', ['type2', [1, 3, 7]], ['object2', $this->typeId]],
            ['AND', ['type3', [1, 3, 7]], ['object3', $this->typeId]]
        );
        $enchList = new EnchantmentList($conditions);
        if (!$enchList->error)
        {
            $this->lvTabs[] = ['enchantment', array(
                'data' => array_values($enchList->getListviewData()),
                'name' => Util::ucFirst(Lang::game('enchantments'))
            ), 'enchantment'];

            $this->extendGlobalData($enchList->getJSGlobals());
        }

        // tab: sounds
        $activitySounds = DB::Aowow()->selectRow('SELECT * FROM ?_spell_sounds WHERE id = ?d', $this->subject->getField('spellVisualId'));
        array_shift($activitySounds);                       // remove id-column
        if ($activitySounds)
        {
            $sounds = new SoundList(array(['id', $activitySounds]));
            if (!$sounds->error)
            {
                $data = $sounds->getListviewData();
                foreach ($activitySounds as $activity => $id)
                    if (isset($data[$id]))
                        $data[$id]['activity'] = $activity; // no index, js wants a string :(

                $tabData = ['data' => array_values($data)];
                if ($activitySounds)
                    $tabData['visibleCols'] = ['activity'];

                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = ['sound', $tabData];
            }
        }


        // find associated NPC, Item and merge results
        // taughtbypets (unused..?)
        // taughtbyquest (usually the spell casted as quest reward teaches something; exclude those seplls from taughtBySpell)
        // taughtbytrainers
        // taughtbyitem

        // tab: conditions
        $sc = Util::getServerConditions([CND_SRC_SPELL_LOOT_TEMPLATE, CND_SRC_SPELL_IMPLICIT_TARGET, CND_SRC_SPELL, CND_SRC_SPELL_CLICK_EVENT, CND_SRC_VEHICLE_SPELL, CND_SRC_SPELL_PROC], null, $this->typeId);
        if (!empty($sc[0]))
        {
            $this->extendGlobalData($sc[1]);
            $tab = "<script type=\"text/javascript\">\n" .
                   "var markup = ConditionList.createTab(".Util::toJSON($sc[0]).");\n" .
                   "Markup.printHtml(markup, 'tab-conditions', { allow: Markup.CLASS_STAFF })" .
                   "</script>";

            $this->lvTabs[] = [null, array(
                'data'   => $tab,
                'id'   => 'conditions',
                'name' => '$LANG.requires'
            )];
        }
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            [$tooltip, $ttSpells] = $this->subject->renderTooltip();
            [$buff,    $bfSpells] = $this->subject->renderBuff();

            $power->{'name_'.User::$localeString}       = $this->subject->getField('name', true);
            $power->icon                                = rawurlencode($this->subject->getField('iconString', true, true));
            $power->{'tooltip_'.User::$localeString}    = $tooltip;
            $power->{'spells_'.User::$localeString}     = $ttSpells;
            $power->{'buff_'.User::$localeString}       = $buff;
            $power->{'buffspells_'.User::$localeString} = $bfSpells;
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    private function appendReagentItem(&$reagentResult, $_iId, $_qty, $_mult, $_level, $_path, $alreadyUsed)
    {
        if (in_array($_iId, $alreadyUsed))
            return false;

        $item = DB::Aowow()->selectRow('
            SELECT  name_loc0, name_loc2, name_loc3, name_loc6, name_loc8, i.id, ic.name AS iconString, quality,
            IF ( (spellId1 > 0 AND spellCharges1 < 0) OR
                 (spellId2 > 0 AND spellCharges2 < 0) OR
                 (spellId3 > 0 AND spellCharges3 < 0) OR
                 (spellId4 > 0 AND spellCharges4 < 0) OR
                 (spellId5 > 0 AND spellCharges5 < 0), 1, 0) AS consumed
            FROM    ?_items i
            LEFT JOIN ?_icons ic ON ic.id = i.iconId
            WHERE   i.id = ?d',
            $_iId
        );

        if (!$item)
            return false;

        $this->extendGlobalIds(TYPE_ITEM, $item['id']);

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

        if (!$this->appendReagentSpell($reagentResult, $item['id'], $data['qty'], $data['level'], $data['path'], $alreadyUsed))
            $reagentResult[$idx]['final'] = true;

        return true;
    }

    private function appendReagentSpell(&$reagentResult, $_iId, $_qty, $_level, $_path, $alreadyUsed)
    {
        $_level++;
        // assume that tradeSpells only use the first index  to create items, so this runs somewhat efficiently >.<
        $spells = DB::Aowow()->select('
            SELECT  reagent1,      reagent2,      reagent3,      reagent4,      reagent5,      reagent6,      reagent7,      reagent8,
                    reagentCount1, reagentCount2, reagentCount3, reagentCount4, reagentCount5, reagentCount6, reagentCount7, reagentCount8,
                    name_loc0,     name_loc2,     name_loc3,     name_loc6,     name_loc8,
                    s.id AS ARRAY_KEY, ic.name AS iconString
            FROM    ?_spell s
            JOIN    ?_icons ic ON s.iconId = ic.id
            WHERE   (effect1CreateItemId = ?d AND effect1Id = 24)',// OR
                    // (effect2CreateItemId = ?d AND effect2Id = 24) OR
                    // (effect3CreateItemId = ?d AND effect3Id = 24)',
            $_iId //, $_iId, $_iId
        );

        if (!$spells)
            return false;

        $didAppendSomething = false;
        foreach ($spells as $sId => $row)
        {
            if (in_array(-$sId, $alreadyUsed))
                continue;

            $this->extendGlobalIds(TYPE_SPELL, $sId);

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

                if ($this->appendReagentItem($reagentResult, $row['reagent'.$i], $row['reagentCount'.$i], $data['qty'], $data['level'], $data['path'], $_aU))
                {
                    $hasUnusedReagents  = true;
                    $didAppendSomething = true;
                }
            }

            if (!$hasUnusedReagents)                        // no reagents were added, remove spell from result set
                array_pop($reagentResult);
        }

        return $didAppendSomething;
    }

    private function createReagentList()
    {
        $reagentResult = [];
        $enhanced      = false;

        if ($reagents = $this->subject->getReagentsForCurrent())
        {
            foreach ($this->subject->relItems->iterate() as $iId => $__)
            {
                if (!in_array($iId, array_keys($reagents)))
                    continue;

                $data = array(
                    'type'    => TYPE_ITEM,
                    'typeId'  => $iId,
                    'typeStr' => Util::$typeStrings[TYPE_ITEM],
                    'quality' => $this->subject->relItems->getField('quality'),
                    'name'    => $this->subject->relItems->getField('name', true),
                    'icon'    => $this->subject->relItems->getField('iconString'),
                    'qty'     => $reagents[$iId][1],
                    'path'    => TYPE_ITEM.'-'.$iId,            // id of the html-element
                    'level'   => 0                              // depths in array, used for indentation
                );

                $idx = count($reagentResult);
                $reagentResult[] = $data;

                // start with self and current original item in usedEntries (spell < 0; item > 0)
                if ($this->appendReagentSpell($reagentResult, $iId, $data['qty'], 0, $data['path'], [-$this->typeId, $iId]))
                    $enhanced = true;
                else
                    $reagentResult[$idx]['final'] = true;
            }
        }

        // increment all indizes (by prepending null and removing it again)
        array_unshift($reagentResult, null);
        unset($reagentResult[0]);

        return [$enhanced, $reagentResult];
    }

    private function createScalingData()                    // calculation mostly like seen in TC
    {
        $scaling = array_merge(
            array(
                'directSP' => -1,
                'dotSP'    => -1,
                'directAP' =>  0,
                'dotAP'    =>  0
            ),
            (array)DB::World()->selectRow('SELECT direct_bonus AS directSP, dot_bonus AS dotSP, ap_bonus AS directAP, ap_dot_bonus AS dotAP FROM spell_bonus_data WHERE entry = ?d', $this->firstRank)
        );

        if (!$this->subject->isDamagingSpell() && !$this->subject->isHealingSpell())
            return $scaling;

        foreach ($scaling as $k => $v)
        {
            // only calculate for class/pet spells
            if ($v != -1 || !in_array($this->subject->getField('typeCat'), [-2, -3, -7, 7]))
                continue;


            // no known calculation for physical abilities
            if ($k == 'directAP' || $k == 'dotAP')
                continue;

            // dont use spellPower to scale physical Abilities
            if ($this->subject->getField('schoolMask') == 0x1 && ($k == 'directSP' || $k == 'dotSP'))
                continue;

            $isDOT = false;
            $pMask = $this->subject->periodicEffectsMask();

            if ($k == 'dotSP' || $k == 'dotAP')
            {
                if ($pMask)
                    $isDOT = true;
                else
                    continue;
            }
            else                                            // if all used effects are periodic, dont calculate direct component
            {
                $bar = true;
                for ($i = 1; $i < 4; $i++)
                {
                    if (!$this->subject->getField('effect'.$i.'Id'))
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
                $dotDuration = $this->subject->getField('duration');
                // 200% limit
                if ($dotDuration > 0)
                {
                    if ($dotDuration > 30000)
                        $dotDuration = 30000;
                    if (!$this->subject->isChanneledSpell())
                        $dotFactor = $dotDuration / 15000;
                }
            }

            // Distribute Damage over multiple effects, reduce by AoE
            $castingTime = $this->subject->getCastingTimeForBonus($isDOT);

            // 50% for damage and healing spells for leech spells from damage bonus and 0% from healing
            for ($j = 1; $j < 4; ++$j)
            {
                // SPELL_EFFECT_HEALTH_LEECH || SPELL_AURA_PERIODIC_LEECH
                if ($this->subject->getField('effectId'.$j) == 9 || $this->subject->getField('effect'.$j.'AuraId') == 53)
                {
                    $castingTime /= 2;
                    break;
                }
            }

            if ($this->subject->isHealingSpell())
                $castingTime *= 1.88;

            // SPELL_SCHOOL_MASK_NORMAL
            if ($this->subject->getField('schoolMask') != 0x1)
                $scaling[$k] = ($castingTime / 3500.0) * $dotFactor;
            else
                $scaling[$k] = 0;                           // would be 1 ($dotFactor), but we dont want it to be displayed
        }

        return $scaling;
    }

    private function createRequiredItems()
    {
        // parse itemClass & itemSubClassMask
        $class    = $this->subject->getField('equippedItemClass');
        $subClass = $this->subject->getField('equippedItemSubClassMask');
        $invType  = $this->subject->getField('equippedItemInventoryTypeMask');

        if ($class <= 0)
            return;

        $title = ['Class: '.$class, 'SubClass: '.Util::asHex($subClass)];
        $text  = Lang::getRequiredItems($class, $subClass, false);

        if ($invType)
        {
            // remap some duplicated strings            'Off Hand' and 'Shield' are never used simultaneously
            if ($invType & (1 << INVTYPE_ROBE))         // Robe => Chest
            {
                $invType &= ~(1 << INVTYPE_ROBE);
                $invType |=  (1 << INVTYPE_CHEST);
            }

            if ($invType & (1 << INVTYPE_RANGEDRIGHT))  // Ranged2 => Ranged
            {
                $invType &= ~(1 << INVTYPE_RANGEDRIGHT);
                $invType |=  (1 << INVTYPE_RANGED);
            }

            $_ = [];
            $strs = Lang::item('inventoryType');
            foreach ($strs as $k => $str)
                if ($invType & 1 << $k && $str)
                    $_[] = $str;

            $title[] = Lang::item('slot').Lang::main('colon').Util::asHex($invType);
            $text   .= ' '.Lang::spell('_inSlot').Lang::main('colon').implode(', ', $_);
        }

        return [$title, $text];
    }

    private function createTools()
    {
        $tools = $this->subject->getToolsForCurrent();

        // prepare Tools
        foreach ($tools as &$tool)
        {
            if (isset($tool['itemId']))                     // Tool
                $tool['url'] = '?item='.$tool['itemId'];
            else                                            // ToolCat
            {
                $tool['quality'] = ITEM_QUALITY_HEIRLOOM - ITEM_QUALITY_NORMAL;
                $tool['url']     = '?items&filter=cr=91;crs='.$tool['id'].';crv=0';
            }
        }

        return $tools;
    }

    private function createEffects(&$infobox, &$redButtons)
    {
        // proc data .. maybe use more information..?
        $procData = DB::World()->selectRow('SELECT IF(ratePerMinute  > 0, -ratePerMinute, Chance) AS chance, Cooldown AS cooldown FROM spell_proc WHERE ABS(SpellId) = ?d', $this->firstRank);
        if (!isset($procData['cooldown']))
            $procData['cooldown'] = 0;

        $effects  = [];
        $spellIdx = array_unique(array_merge($this->subject->canTriggerSpell(), $this->subject->canTeachSpell()));
        $itemIdx  = $this->subject->canCreateItem();
        $perfItem = DB::World()->selectRow('SELECT * FROM skill_perfect_item_template WHERE spellId = ?d', $this->typeId);

        // Iterate through all effects:
        for ($i = 1; $i < 4; $i++)
        {
            if ($this->subject->getField('effect'.$i.'Id') <= 0)
                continue;

            $effId   = (int)$this->subject->getField('effect'.$i.'Id');
            $effMV   = (int)$this->subject->getField('effect'.$i.'MiscValue');
            $effMVB  = (int)$this->subject->getField('effect'.$i.'MiscValueB');
            $effBP   = (int)$this->subject->getField('effect'.$i.'BasePoints');
            $effDS   = (int)$this->subject->getField('effect'.$i.'DieSides');
            $effRPPL =      $this->subject->getField('effect'.$i.'RealPointsPerLevel');
            $effAura = (int)$this->subject->getField('effect'.$i.'AuraId');
            $foo     = &$effects[];

            // Icons:
            // .. from item
            if (in_array($i, $itemIdx))
            {
                $_ = $this->subject->getField('effect'.$i.'CreateItemId');
                foreach ($this->subject->relItems->iterate() as $itemId => $__)
                {
                    if ($itemId != $_)
                        continue;

                    $foo['icon'] = array(
                        'id'      => $this->subject->relItems->id,
                        'name'    => $this->subject->relItems->getField('name', true),
                        'quality' => $this->subject->relItems->getField('quality'),
                        'count'   => $effDS + $effBP,
                        'icon'    => $this->subject->relItems->getField('iconString')
                    );

                    break;
                }

                // perfect Items
                if ($perfItem && $this->subject->relItems->getEntry($perfItem['perfectItemType']))
                {
                    $cndSpell = new SpellList(array(['id', $perfItem['requiredSpecialization']]));
                    if (!$cndSpell->error)
                    {
                        $foo['perfItem'] = array(
                            'icon'          => $cndSpell->getField('iconString'),
                            'quality'       => $this->subject->relItems->getField('quality'),
                            'cndSpellId'    => $perfItem['requiredSpecialization'],
                            'cndSpellName'  => $cndSpell->getField('name', true),
                            'chance'        => $perfItem['perfectCreateChance'],
                            'itemId'        => $perfItem['perfectItemType'],
                            'itemName'      => $this->subject->relItems->getField('name', true)
                        );
                    }
                }

                if ($effDS > 1)
                    $foo['icon']['count'] = "'".($effBP + 1).'-'.$foo['icon']['count']."'";
            }
            // .. from spell
            else if (in_array($i, $spellIdx) || in_array($effId, [133, 140, 141]))
            {
                if ($effId == 155)
                    $_ = $effMV;
                else
                    $_ = $this->subject->getField('effect'.$i.'TriggerSpell');

                $trig = new SpellList(array(['s.id', (int)$_]));

                $foo['icon'] = array(
                    'id'    => $_,
                    'name'  => $trig->error ? Util::ucFirst(Lang::game('spell')).' #'.$_ : $trig->getField('name', true),
                    'count' => 0
                );

                $this->extendGlobalData($trig->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }

            // Effect Name
            if ($_ = Lang::spell('effects', $effId))
                $foo['name'] = (User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'EffectId: '.$effId, $_) : Lang::spell('effects', $effId)).Lang::main('colon');
            else
                $foo['name'] = 'Unknow Effect (#'.$effId.')';

            if ($this->subject->getField('effect'.$i.'RadiusMax') > 0)
                $foo['radius'] = $this->subject->getField('effect'.$i.'RadiusMax');

            if (!in_array($i, $itemIdx))
                $foo['value'] = ($effDS && $effDS != 1 ? ($effBP + 1).Lang::game('valueDelim') : null).($effBP + $effDS);

            if ($effRPPL != 0)
                $foo['value'] = (isset($foo['value']) ? $foo['value'] : '0').sprintf(Lang::spell('costPerLevel'), $effRPPL);
            if ($this->subject->getField('effect'.$i.'Periode') > 0)
                $foo['interval'] = Util::formatTime($this->subject->getField('effect'.$i.'Periode'));

            if ($_ = $this->subject->getField('effect'.$i.'Mechanic'))
                $foo['mechanic'] = Lang::game('me', $_);

            if (in_array($i, $this->subject->canTriggerSpell()) && !empty($procData['chance']))
                $foo['procData'] = array(
                    $procData['chance'],
                    $procData['cooldown'] ? Util::formatTime($procData['cooldown'], true) : null
                );
            else if (in_array($i, $this->subject->canTriggerSpell()) && $this->subject->getField('procChance'))
                $foo['procData'] = array(
                    $this->subject->getField('procChance'),
                    $procData['cooldown'] ? Util::formatTime($procData['cooldown'], true) : null
                );

            // parse masks and indizes
            switch ($effId)
            {
                case 8:                                     // Power Drain
                case 30:                                    // Energize
                case 62:                                    // Power Burn
                case 137:                                   // Energize Pct
                    $_ = Lang::spell('powerTypes', $effMV);
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    if ($effMV == POWER_RAGE || $effMV == POWER_RUNIC_POWER)
                        $foo['value'] = ($effDS && $effDS != 1 ? (($effBP + 1) / 10).Lang::game('valueDelim') : null).(($effBP + $effDS) / 10);

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 11:                                    // Bind
                    if (!$effMV && User::isInGroup(U_GROUP_EMPLOYEE))
                        $foo['name'] .= sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, '(current zone)');
                    else if (!$effMV)
                        $foo['name'] .= '('.Lang::spell('currentArea').')';
                    else if ($_ = ZoneList::getName($effMV))
                        $foo['name'] .= '(<a href="?zone='.$effMV.'">'.$_.'</a>)';
                    else
                        $foo['name'] .= Util::ucFirst(Lang::game('zone')).' #'.$effMV;;
                    break;
                case 16:                                    // QuestComplete
                    if ($_ = QuestList::getName($effMV))
                        $foo['name'] .= '(<a href="?quest='.$effMV.'">'.$_.'</a>)';
                    else
                        $foo['name'] .= Util::ucFirst(Lang::game('quest')).' #'.$effMV;;
                    break;
                case 28:                                    // Summon
                case 56:                                    // Summon Pet
                case 90:                                    // Kill Credit
                case 112:                                   // Summon Demon
                case 134:                                   // Kill Credit2
                    if ($summon = $this->subject->getModelInfo($this->typeId, $i))
                        $redButtons[BUTTON_VIEW3D] = ['type' => TYPE_NPC, 'displayId' => $summon['displayId']];

                    $_ = Lang::game('npc').' #'.$effMV;
                    if ($n = CreatureList::getName($effMV))
                        $_ = ' (<a href="?npc='.$effMV.'">'.$n.'</a>)';

                    $foo['name'] .= $_;
                    break;
                case 33:                                    // Open Lock
                    $_ = $effMV ? Lang::spell('lockType', $effMV) : $effMV;
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 53:                                    // Enchant Item Perm
                case 54:                                    // Enchant Item Temp
                case 92:                                    // Enchant Held Item
                case 156:                                   // Enchant Item Prismatic
                    if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE id = ?d', $effMV))
                        $foo['name'] .= ' (<a href="?enchantment='.$effMV.'" class="q2">'.Util::localizedString($_, 'name').'</a>)';
                    else
                        $foo['name'] .= ' #'.$effMV;
                    break;
                case 38:                                    // Dispel [miscValue => Types]
                case 126:                                   // Steal Aura
                    $_ = Lang::game('dt', $effMV);
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 39:                                    // Learn Language
                    $_ = Lang::game('languages', $effMV);
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 50:                                    // Trans Door
                case 76:                                    // Summon Object (Wild)
                case 104:                                   // Summon Object (slot 1)
                case 105:                                   // Summon Object (slot 2)
                case 106:                                   // Summon Object (slot 3)
                case 107:                                   // Summon Object (slot 4)
                    if ($summon = $this->subject->getModelInfo($this->typeId, $i))
                        $redButtons[BUTTON_VIEW3D] = ['type' => TYPE_OBJECT, 'displayId' => $summon['displayId']];

                    $_ = Util::ucFirst(Lang::game('object')).' #'.$effMV;
                    if ($n = GameobjectList::getName($effMV))
                        $_ = ' (<a href="?object='.$effMV.'">'.$n.'</a>)';

                    $foo['name'] .= $_;
                    break;
                case 86:                                    // Activate Object
                    $_ = Lang::gameObject('actions', $effMV);
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 74:                                    // Apply Glyph
                    if ($_ = DB::Aowow()->selectCell('SELECT spellId FROM ?_glyphproperties WHERE id = ?d', $effMV))
                    {
                        if ($n = SpellList::getName($_))
                            $foo['name'] .= '(<a href="?spell='.$_.'">'.$n.'</a>)';
                        else
                            $foo['name'] .= Util::ucFirst(Lang::game('spell')).' #'.$effMV;
                    }
                    else
                        $foo['name'] .= ' #'.$effMV;;
                    break;
                case 95:                                    // Skinning
                    switch ($effMV)
                    {
                        case 0:  $_ = Lang::game('ct', 1).', '.Lang::game('ct', 2); break;    // Beast, Dragonkin
                        case 1:
                        case 2:  $_ = Lang::game('ct', 4); break;                             // Elemental (nature based, earth based)
                        case 3:  $_ = Lang::game('ct', 9); break;                             // Mechanic
                        default; $_ = '';
                    }
                    if (User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 108:                                   // Dispel Mechanic
                    $_ = Lang::game('me', $effMV);
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 44:                                    // Learn Skill Step
                case 118:                                   // Require Skill
                    if ($_ = SkillList::getName($effMV))
                        $foo['name'] .= '(<a href="?skill='.$effMV.'">'.$_.'</a>)';
                    else
                        $foo['name'] .= Util::ucFirst(Lang::game('skill')).' #'.$effMV;;
                    break;
                case 146:                                   // Activate Rune
                    $_ = Lang::spell('powerRunes', $effMV);
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $_ = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_);
                    else if (!$_)
                        $_ = $effMV;

                    $foo['name'] .= ' ('.$_.')';
                    break;
                case 131:                                   // Play Music
                case 132:                                   // Play Sound
                    $foo['markup'] = '[sound='.$effMV.']';
                    break;
                case 103:                                   // Reputation
                    $_ = Util::ucFirst(Lang::game('faction')).' #'.$effMV;
                    if ($n = FactionList::getName($effMV))
                        $_ = ' (<a href="?faction='.$effMV.'">'.$n.'</a>)';

                    // apply custom reward rated
                    if ($cuRate = DB::World()->selectCell('SELECT spell_rate FROM reputation_reward_rate WHERE spell_rate <> 1 && faction = ?d', $effMV))
                        $foo['value'] .= sprintf(Util::$dfnString, Lang::faction('customRewRate'), ' ('.(($cuRate < 1 ? '-' : '+').intVal(($cuRate - 1) * $foo['value'])).')');

                    $foo['name'] .= $_;

                    break;
                case 123:                                   // Send Taxi
                    $_ = DB::Aowow()->selectRow('
                        SELECT tn1.name_loc0 AS start_loc0, tn1.name_loc?d AS start_loc?d, tn2.name_loc0 AS end_loc0, tn2.name_loc?d AS end_loc?d
                        FROM ?_taxipath tp
                        JOIN ?_taxinodes tn1 ON tp.startNodeId = tn1.id
                        JOIN ?_taxinodes tn2 ON tp.endNodeId = tn2.id
                        WHERE tp.id = ?d',
                        User::$localeId, User::$localeId, User::$localeId, User::$localeId, $effMV
                    );
                    if ($_ && User::isInGroup(U_GROUP_EMPLOYEE))
                        $foo['name'] .= sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, ' (<span class="breadcrumb-arrow">'.Util::localizedString($_, 'start').'</span>'.Util::localizedString($_, 'end').')');
                    else if ($_)
                        $foo['name'] .= ' (<span class="breadcrumb-arrow">'.Util::localizedString($_, 'start').'</span>'.Util::localizedString($_, 'end').')';
                    else
                        $foo['name'] .= ' ('.$effMV.')';
                    break;
                default:
                {
                    if (($effMV || $effId == 97) && $effId != 155)
                        $foo['name'] .= ' ('.$effMV.')';

                    break;
                }
                // Aura
                case 6:                                     // Simple
                case 27:                                    // AA Persistent
                case 35:                                    // AA Party
                case 65:                                    // AA Raid
                case 119:                                   // AA Pet
                case 128:                                   // AA Friend
                case 129:                                   // AA Enemy
                case 143:                                   // AA Owner
                {
                    if ($effAura > 0 && ($aurName = Lang::spell('auras', $effAura)))
                    {
                        $foo['name'] .= User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'AuraId: '.$effAura, $aurName) : $aurName;

                        $bar = $effMV;
                        switch ($effAura)
                        {
                            case 17:                        // Mod Stealth Detection
                                if ($_ = Lang::spell('stealthType', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 19:                        // Mod Invisibility Detection
                                if ($_ = Lang::spell('invisibilityType', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 24:                        // Periodic Energize
                            case 21:                        // Obsolete Mod Power
                            case 35:                        // Mod Increase Power
                            case 85:                        // Mod Power Regeneration
                            case 110:                       // Mod Power Regeneration Pct
                            case 132:                       // Mod Increase Energy Percent
                                if ($_ = Lang::spell('powerTypes', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 29:                        // Mod Stat
                            case 80:                        // Mod Stat %
                            case 137:                       // Mod Total Stat %
                            case 175:                       // Mod Spell Healing Of Stat Percent
                            case 212:                       // Mod Ranged Attack Power Of Stat Percent
                            case 219:                       // Mod Mana Regeneration from Stat
                            case 268:                       // Mod Attack Power Of Stat Percent
                                $mask = $effMV == -1 ? 0x1F : 1 << $effMV;
                                $_ = [];
                                for ($j = 0; $j < 5; $j++)
                                    if ($mask & (1 << $j))
                                        $_[] = Lang::game('stats', $j);

                                if ($_ = implode(', ', $_));
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 36:                        // Shapeshift
                                if ($st = $this->subject->getModelInfo($this->typeId, $i))
                                {
                                    $redButtons[BUTTON_VIEW3D] = array(
                                        'type'      => TYPE_NPC,
                                        'displayId' => $st['displayId']
                                    );

                                    if ($st['creatureType'] > 0)
                                        $infobox[] = Lang::game('type').Lang::main('colon').Lang::game('ct', $st['creatureType']);

                                    if ($_ = $st['displayName'])
                                        $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;
                                }
                                break;
                            case 37:                        // Effect immunity
                                if ($_ = Lang::spell('effects', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 38:                        // Aura immunity
                                if ($_ = Lang::spell('auras', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 41:                        // Dispel Immunity
                            case 178:                       // Mod Debuff Resistance
                            case 245:                       // Mod Aura Duration By Dispel
                                if ($_ = Lang::game('dt', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 44:                        // Track Creature
                                if ($_ = Lang::game('ct', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 45:                        // Track Resource
                                if ($_ = Lang::spell('lockType', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 75:                        // Language
                                if ($_ = Lang::game('languages', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 77:                        // Mechanic Immunity
                            case 117:                       // Mod Mechanic Resistance
                            case 232:                       // Mod Mechanic Duration
                            case 234:                       // Mod Mechanic Duration (no stack)
                            case 255:                       // Mod Mechanic Damage Taken Pct
                            case 276:                       // Mod Mechanic Damage Done Percent
                                if ($_ = Lang::game('me', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').Util::asHex($effMV), $_) : $_;

                                break;
                            case 147:                       // Mechanic Immunity Mask
                                $_ = [];
                                foreach (Lang::game('me') as $k => $str)
                                    if ($k && ($effMV & (1 << $k - 1)))
                                        $_[] = $str;

                                if ($_ = implode(', ', $_))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').Util::asHex($effMV), $_) : $_;

                                break;
                            case 10:                        // Mod Threat
                            case 13:                        // Mod Damage Done
                            case 14:                        // Mod Damage Taken
                            case 22:                        // Mod Resistance
                            case 39:                        // School Immunity
                            case 40:                        // Damage Immunity
                            case 50:                        // Mod Critical Healing Amount
                            case 57:                        // Mod Spell Crit Chance
                            case 69:                        // School Absorb
                            case 71:                        // Mod Spell Crit Chance School
                            case 72:                        // Mod Power Cost School Percent
                            case 73:                        // Mod Power Cost School Flat
                            case 74:                        // Reflect Spell School
                            case 79:                        // Mod Damage Done Pct
                            case 81:                        // Split Damage Pct
                            case 83:                        // Mod Base Resistance
                            case 87:                        // Mod Damage Taken Pct
                            case 97:                        // Mana Shield
                            case 101:                       // Mod Resistance Pct
                            case 115:                       // Mod Healing Taken
                            case 118:                       // Mod Healing Taken Pct
                            case 123:                       // Mod Target Resistance
                            case 135:                       // Mod Healing Done
                            case 136:                       // Mod Healing Done Pct
                            case 142:                       // Mod Base Resistance Pct
                            case 143:                       // Mod Resistance Exclusive
                            case 149:                       // Reduce Pushback
                            case 163:                       // Mod Crit Damage Bonus
                            case 174:                       // Mod Spell Damage Of Stat Percent
                            case 182:                       // Mod Resistance Of Stat Percent
                            case 186:                       // Mod Attacker Spell Hit Chance
                            case 194:                       // Mod Target Absorb School
                            case 195:                       // Mod Target Ability Absorb School
                            case 199:                       // Mod Increases Spell Percent to Hit
                            case 229:                       // Mod AoE Damage Avoidance
                            case 271:                       // Mod Damage Percent Taken Form Caster
                            case 310:                       // Mod Creature AoE Damage Avoidance
                            case 237:                       // Mod Spell Damage Of Attack Power
                            case 238:                       // Mod Spell Healing Of Attack Power
                            case 242:                       // Mod Spell & Healing Power by % of Int
                            case 259:                       // Mod Periodic Healing Taken %
                            case 267:                       // Cancel Aura Buffer at % of Caster Health
                            case 269:                       // Ignore Target Resistance
                            case 285:                       // Mod Attack Power by School Resistance
                            case 300:                       // Share Damage %
                            case 301:                       // Mod Absorb School Healing
                                if ($_ = Lang::getMagicSchools($effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').Util::asHex($effMV), $_) : $_;

                                break;
                            case 30:                        // Mod Skill
                            case 98:                        // Mod Skill Value
                                if ($n = SkillList::getName($effMV))
                                    $bar = ' (<a href="?skill='.$effMV.'">'.$n.'</a>)';
                                else
                                    $bar = Lang::main('colon').Util::ucFirst(Lang::game('skill')).' #'.$effMV;;

                                break;
                            case 107:                       // Flat Modifier
                            case 108:                       // Pct Modifier
                                if ($_ = Lang::spell('spellModOp', $effMV))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $_) : $_;

                                break;
                            case 189:                       // Mod Rating
                            case 220:                       // Combat Rating From Stat
                                $_ = [];
                                foreach (Lang::spell('combatRating') as $k => $str)
                                    if ((1 << $k) & $effMV)
                                        $_[] = $str;

                                if ($_ = implode(', ', $_))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').Util::asHex($effMV), $_) : $_;

                                break;
                            case 168:                       // Mod Damage Done Versus
                            case 59:                        // Mod Damage Done Versus Creature
                            case 102:                       // Mod Melee Attack Power Versus
                            case 131:                       // Mod Ranged Attack Power Versus
                            case 180:                       // Mod Spell Damage Versus
                                $_ = [];
                                foreach (Lang::game('ct') as $k => $str)
                                    if ($k && ($effMV & (1 << $k - 1)))
                                        $_[] = $str;

                                if ($_ = implode(', ', $_))
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').Util::asHex($effMV), $_) : $_;

                                break;
                            case 249:                       // Convert Rune
                                $from = $effMV;
                                if ($_ = Lang::spell('powerRunes', $effMV))
                                    $from = $_;

                                $to = $effMVB;
                                if ($_ = Lang::spell('powerRunes', $effMVB))
                                    $to = $_;

                                if (User::isInGroup(U_GROUP_EMPLOYEE))
                                    $bar = sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $from).' => '.sprintf(Util::$dfnString, 'MiscValueB'.Lang::main('colon').$effMVB, $to);
                                else
                                    $bar = $from.' => '.$to;

                                $effMVB = 0;

                                break;
                            case 78:                        // Mounted
                            case 56:                        // Transform
                                if ($transform = $this->subject->getModelInfo($this->typeId, $i))
                                {
                                    $redButtons[BUTTON_VIEW3D] = ['type' => TYPE_NPC, 'displayId' => $transform['displayId']];
                                    $bar = $transform['typeId'] ? ' (<a href="?npc='.$transform['typeId'].'">'.$transform['displayName'].'</a>)' : ' (#0)';
                                }
                                else
                                    $bar = Lang::main('colon').Lang::game('npc').' #'.$effMV;;

                                break;
                            case 139:                       // Force Reaction
                                $foo['value'] = sprintf(Util::$dfnString, $foo['value'], Lang::game('rep', $foo['value']));
                                // DO NOT BREAK
                            case 190:                       // Mod Faction Reputation Gain
                                $n = FactionList::getName($effMV);
                                $bar          = ' ('.($n ? '<a href="?faction='.$effMV.'">'.$n.'</a>' : Util::ucFirst(Lang::game('faction')).' #'.$effMV).')';
                                break;                      // also breaks for 139
                            case 293:                       // Override Spells
                                if ($so = DB::Aowow()->selectRow('SELECT spellId1, spellId2, spellId3, spellId4, spellId5 FROM ?_spelloverride WHERE id = ?d', $effMV))
                                {
                                    $buff = [];
                                    for ($j = 1; $j < 6; $j++)
                                    {
                                        if ($x = $so['spellId'.$j])
                                        {
                                            $this->extendGlobalData([TYPE_SPELL => [$x]]);
                                            $buff[] = '[spell='.$x.']';
                                        }
                                    }
                                    $foo['markup'] = implode(', ', $buff);
                                }
                                break;
                            case 202:                       // Ignore Combat Result
                            case 248:                       // Mod Combat Result Chance
                                $what = '';
                                switch ($effMV)
                                {
                                    case 2:                 // Dodged
                                        $what = Lang::spell('combatRating', 2);
                                        break;
                                    case 3:                 // Blocked
                                        $what = Lang::spell('combatRating', 4);
                                        break;
                                    case 4:                 // Parried
                                        $what = Lang::spell('combatRating', 3);
                                        break;
                                    case 0;                 // Evaded
                                    case 1:                 // Missed
                                    case 5:                 // Glanced
                                    case 6:                 // Crited'ed..ed
                                    case 7:                 // Crushed
                                    case 8:                 // Regular
                                    default:
                                        trigger_error('hitero unused case #'.$effMV.' found for aura 202');
                                }

                                if ($what)
                                    $bar = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'MiscValue'.Lang::main('colon').$effMV, $what) : $what;

                                break;
                            case 233:                       // Change other Humanoid Display
                            case 273:                       // X-Ray
                            case 304:                       // Fake Inebriate
                                $bar = ' ('.Lang::game('npc').' #'.$effMV.')';
                                if ($n = CreatureList::getName($effMV))
                                    $bar = ' (<a href="?npc='.$effMV.'">'.$n.'</a>)';
                        }
                        $foo['name'] .= strstr($bar, 'href') || strstr($bar, '#') ? $bar : ($bar ? ' ('.$bar.')' : null);

                        if (in_array($effAura, [174, 220, 182]))
                            $foo['name'] .= ' ['.sprintf(Util::$dfnString, 'MiscValueB'.Lang::main('colon').$effMVB, Lang::game('stats', $effMVB)).']';
                        else if ($effMVB > 0)
                            $foo['name'] .= ' ['.$effMVB.']';

                    }
                    else if ($effAura > 0)
                        $foo['name'] .= 'Unknown Aura ('.$effAura.')';

                    break;
                }
            }

            // cases where we dont want 'Value' to be displayed
            if (in_array($effAura, [11, 12, 36, 77]) || in_array($effId, [132]) || empty($foo['value']))
                unset($foo['value']);
        }

        unset($foo);                                            // clear reference

        return $effects;
    }

    private function createAttributesList() : array
    {
        $cbBandageSpell = function()
        {
            return ($this->subject->getField('attributes1') & 0x00004044) && ($this->subject->getField('effect1ImplicitTargetA') == 21);
        };

        $cbInverseFlag = function($field, $flag)
        {
            return !($this->subject->getField($field) & $flag);
        };

        $cbEquippedWeapon = function ($mask, $useInvType)
        {
            $field = $useInvType ? 'equippedItemInventoryTypeMask' : 'equippedItemSubClassMask';

            return ($this->subject->getField('equippedItemClass') == ITEM_CLASS_WEAPON) && ($this->subject->getField($field) & $mask);
        };

        $cbSpellstealable = function($field, $flag)
        {
            return !($this->subject->getField($field) & $flag) && ($this->subject->getField('dispelType') == 1);
        };

        $list = [];
        $fi   = new SpellListFilter();
        foreach (Lang::spell('attributes') as $idx => $_)
        {
            if ($cr = $fi->getGenericFilter($idx))
            {
                if ($cr[0] == FILTER_CR_CALLBACK)
                {
                    if (!isset($cr[1]))
                        trigger_error('SpellDetailPage::createAttributesList - callback handler '.$cr[1].' not defined for IDX #'.$idx, E_USER_WARNING);
                    else if (${$cr[1]}($cr[2] ?? null, $cr[3] ?? null))
                        $list[] = $idx;
                }
                else if ($cr[0] == FILTER_CR_FLAG)
                {
                    if ($this->subject->getField($cr[1]) & $cr[2])
                        $list[] = $idx;
                }
                else
                    trigger_error('SpellDetailPage::createAttributesList - unhandled filter case #'.$cr[0].' for IDX #'.$idx, E_USER_WARNING);
            }
            else
                trigger_error('SpellDetailPage::createAttributesList - SpellAttrib IDX #'.$idx.' defined in Lang, but not set as filter', E_USER_WARNING);
        }

        return $list;
    }
}



?>
