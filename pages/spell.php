<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 1: Spell    g_initPath()
//  tabId 0: Database g_initHeader()
class SpellPage extends GenericPage
{
    use TrDetailPage;

    protected $reagents      = [];
    protected $scaling       = [];
    protected $items         = [];
    protected $tools         = [];
    protected $effects       = [];
    protected $attributes    = [];
    protected $powerCost     = '';
    protected $castTime      = [];
    protected $level         = [];
    protected $rangeName     = '';
    protected $range         = '';
    protected $gcd           = '';
    protected $gcdCat        = '';                          // todo (low): nyi; find out how this works [n/a; normal; ..]
    protected $school        = '';
    protected $dispel        = '';
    protected $mechanic      = '';
    protected $stances       = '';
    protected $cooldown      = '';
    protected $duration      = '';

    protected $type          = Type::SPELL;
    protected $typeId        = 0;
    protected $tpl           = 'spell';
    protected $path          = [0, 1];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/swfobject.js']];

    protected $_get          = ['domain' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkDomain']];

    private   $difficulties  = [];
    private   $firstRank     = 0;
    private   $powerTpl      = '$WowheadPower.registerSpell(%d, %d, %s);';

    private static $modAuras = [SPELL_AURA_ADD_FLAT_MODIFIER,      SPELL_AURA_ADD_PCT_MODIFIER,                 SPELL_AURA_NO_REAGENT_USE,
                                SPELL_AURA_ABILITY_PERIODIC_CRIT,  SPELL_AURA_MOD_TARGET_ABILITY_ABSORB_SCHOOL, SPELL_AURA_ABILITY_IGNORE_AURASTATE,
                                SPELL_AURA_ALLOW_ONLY_ABILITY,     SPELL_AURA_IGNORE_MELEE_RESET,               SPELL_AURA_ABILITY_CONSUME_NO_AMMO,
                                SPELL_AURA_MOD_IGNORE_SHAPESHIFT,  SPELL_AURA_PERIODIC_HASTE,                   SPELL_AURA_OVERRIDE_CLASS_SCRIPTS,
                                SPELL_AURA_MOD_DAMAGE_FROM_CASTER, SPELL_AURA_ADD_TARGET_TRIGGER,            /* SPELL_AURA_DUMMY ? */];

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && $this->_get['domain'])
            Util::powerUseLocale($this->_get['domain']);

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
        $this->addScript([SC_JS_FILE, '?data=zones']);

        $_cat = $this->subject->getField('typeCat');

        $modelInfo = $this->subject->getModelInfo($this->typeId);

        $redButtons = array(
            BUTTON_VIEW3D  => false,
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => array(
                'linkColor' => 'ff71d5ff',
                'linkId'    => Type::getFileString(Type::SPELL).':'.$this->typeId,
                'linkName'  => $this->name,
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        // could have multiple models set, one per effect
        foreach ($modelInfo as $mI)
        {
            $redButtons[BUTTON_VIEW3D] = ['type' => $mI['type'], 'displayId' => $mI['displayId']];

            if (isset($mI['humanoid']))
            {
                $redButtons[BUTTON_VIEW3D]['typeId']   = $mI['typeId'];
                $redButtons[BUTTON_VIEW3D]['humanoid'] = 1;
            }

            break;
        }


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
        if ($_ = Lang::getRaceString($this->subject->getField('reqRaceMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_RACE, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('race') : Lang::game('races');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // classes
        if ($_ = Lang::getClassString($this->subject->getField('reqClassMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_CLASS, ...$jsg);
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
                    $infobox[] = Lang::game('requires2').' [spell='.$rSpell->id.']';
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
            $infobox[] = Lang::spell('trainingCost').Lang::main('colon').'[money='.$cost.']';

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // used in mode
        foreach ($this->difficulties as $n => $id)
            if ($id == $this->typeId)                       // "Mode" seems to be multilingual acceptable
                $infobox[] = 'Mode'.Lang::main('colon').Lang::game('modes', $n);

        // Creature Type from Aura: Shapeshift
        foreach ($modelInfo as $mI)
        {
            if (!isset($mI['creatureType']))
                continue;

            if ($mI['creatureType'] > 0)
                $infobox[] = Lang::game('type').Lang::main('colon').Lang::game('ct', $mI['creatureType']);

            break;
        }

        // spell script
        if (User::isInGroup(U_GROUP_STAFF))
            if ($_ = DB::World()->selectCell('SELECT ScriptName FROM spell_script_names WHERE ABS(spell_id) = ?d', $this->firstRank))
                $infobox[] = 'Script'.Lang::main('colon').$_;

        $infobox = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : '';

        // append glyph symbol if available
        $glyphId = 0;
        for ($i = 1; $i < 4; $i++)
            if ($this->subject->getField('effect'.$i.'Id') == SPELL_EFFECT_APPLY_GLYPH)
                $glyphId = $this->subject->getField('effect'.$i.'MiscValue');

        if ($_ = DB::Aowow()->selectCell('SELECT ic.name FROM ?_glyphproperties gp JOIN ?_icons ic ON gp.iconId = ic.id WHERE gp.spellId = ?d { OR gp.id = ?d }', $this->typeId, $glyphId ?: DBSIMPLE_SKIP))
            if (file_exists('static/images/wow/Interface/Spellbook/'.$_.'.png'))
                $infobox .= '[img src='.Cfg::get('STATIC_URL').'/images/wow/Interface/Spellbook/'.$_.'.png border=0 float=center margin=15]';


        /****************/
        /* Main Content */
        /****************/

        $this->reagents    = $this->createReagentList();
        $this->scaling     = $this->createScalingData();
        $this->items       = $this->createRequiredItems();
        $this->tools       = $this->createTools();
        $this->effects     = $this->createEffects();
        $this->attributes  = $this->createAttributesList();
        $this->powerCost   = $this->subject->createPowerCostForCurrent();
        $this->castTime    = $this->subject->createCastTimeForCurrent(false, false);
        $this->level       = $this->subject->getField('spellLevel');
        $this->rangeName   = $this->subject->getField('rangeText', true);
        $this->range       = $this->subject->getField('rangeMaxHostile');
        $this->gcd         = Util::formatTime($this->subject->getField('startRecoveryTime'));
        $this->school      = $this->fmtStaffTip(Lang::getMagicSchools($this->subject->getField('schoolMask')), Util::asHex($this->subject->getField('schoolMask')));
        $this->dispel      = $this->subject->getField('dispelType') ? Lang::game('dt', $this->subject->getField('dispelType')) : null;
        $this->mechanic    = $this->subject->getField('mechanic') ? Lang::game('me', $this->subject->getField('mechanic')) : null;
        $this->name        = $this->subject->getField('name', true);
        $this->headIcons   = [$this->subject->getField('iconString'), $this->subject->getField('stackAmount') ?: ($this->subject->getField('procCharges') > 1 ? $this->subject->getField('procCharges') : '')];
        $this->redButtons  = $redButtons;
        $this->infobox     = $infobox;

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

                if (!$abilities->hasSetFields('skillLines'))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = [SpellList::$brickFile, $tabData];

                $this->extendGlobalData($abilities->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: [$this] modifies
        $sub = [];
        $conditions = [
            ['s.typeCat', [-9], '!'],                       // GM (-9); also include uncategorized (0), NPC-Spell (-8)?; NPC includes totems, lightwell and others :/
            ['s.spellFamilyId', $this->subject->getField('spellFamilyId')],
            &$sub
        ];
        $modifiesData = [];
        $hideSkillCol = true;

        for ($i = 1; $i < 4; $i++)
        {
            if (!in_array($this->subject->getField('effect'.$i.'AuraId'), self::$modAuras))
                continue;

            $m1 = $this->subject->getField('effect'.$i.'SpellClassMaskA');
            $m2 = $this->subject->getField('effect'.$i.'SpellClassMaskB');
            $m3 = $this->subject->getField('effect'.$i.'SpellClassMaskC');

            if (!$m1 && !$m2 && !$m3)
                continue;

            $classSpells = $miscSpells = [];
            $this->effects[$i]['modifies'] = [&$classSpells, &$miscSpells];

            $sub = ['OR', ['s.spellFamilyFlags1', $m1, '&'], ['s.spellFamilyFlags2', $m2, '&'], ['s.spellFamilyFlags3', $m3, '&']];

            $modSpells = new SpellList($conditions);
            if (!$modSpells->error)
            {
                foreach ($modSpells->iterate() as $id => $__)
                {
                    if (in_array($modSpells->getField('typeCat'), [-2, 7]))
                        $classSpells[$id] = [$id, $modSpells->getField('name', true), 0, 0];
                    else
                        $miscSpells[$id]  = [$id, $modSpells->getField('name', true), 0, 0];
                }

                if ($classSpells)
                    foreach (DB::World()->select('SELECT spell_id AS ARRAY_KEY, first_spell_id AS "0", `rank` AS "1" FROM spell_ranks WHERE spell_id IN (?a)', array_keys($classSpells)) as $spellId => [$firstSpellId, $rank])
                    {
                        $classSpells[$firstSpellId][2] = min($classSpells[$firstSpellId][2] ?: $rank, $rank);
                        $classSpells[$firstSpellId][3] = max($classSpells[$firstSpellId][3], $rank);

                        if ($spellId != $firstSpellId)
                            unset($classSpells[$spellId]);
                    }

                $modifiesData += $modSpells->getListviewData();
                if ($modSpells->hasSetFields('skillLines'))
                    $hideSkillCol = false;

                $this->extendGlobalData($modSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }

            $classSpells = array_values($classSpells);
            $miscSpells  = array_values($miscSpells);

            unset($classSpells, $miscSpells);
        }

        if ($modifiesData)
        {
            $tabData = array(
                'data'        => array_values($modifiesData),
                'id'          => 'modifies',
                'name'        => '$LANG.tab_modifies',
                'visibleCols' => ['level'],
            );

            if ($hideSkillCol)
                $tabData['hiddenCols'] = ['skill'];

            $this->lvTabs[] = [SpellList::$brickFile, $tabData];
        }

        // tab: [$this is] modified by
        $sub = ['OR'];
        $conditions = [
            ['s.typeCat', [-9], '!'],                       // GM (-9); also include uncategorized (0), NPC-Spell (-8)?; NPC includes totems, lightwell and others :/
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
                ['s.effect'.$i.'AuraId', self::$modAuras],
                [
                    'OR',
                    ['s.effect'.$i.'SpellClassMaskA', $m1, '&'],
                    ['s.effect'.$i.'SpellClassMaskB', $m2, '&'],
                    ['s.effect'.$i.'SpellClassMaskC', $m3, '&']
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

                if (!$modsSpell->hasSetFields('skillLines'))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = [SpellList::$brickFile, $tabData];

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

            if (!$saSpells->hasSetFields('skillLines'))
                $tabData['hiddenCols'] = ['skill'];

            if (isset($saE))
                $tabData['extraCols'] = $saE;

            $this->lvTabs[] = [SpellList::$brickFile, $tabData];

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
                $this->lvTabs[] = [SpellList::$brickFile, array(
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
                ['AND', ['effect1AuraId', SPELL_AURA_OVERRIDE_SPELLS], ['effect1MiscValue', $so]],
                ['AND', ['effect2AuraId', SPELL_AURA_OVERRIDE_SPELLS], ['effect2MiscValue', $so]],
                ['AND', ['effect3AuraId', SPELL_AURA_OVERRIDE_SPELLS], ['effect3MiscValue', $so]]
            );
            $ubSpells = new SpellList($conditions);
            if (!$ubSpells->error)
            {
                $this->lvTabs[] = [SpellList::$brickFile, array(
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
            $this->lvTabs[] = [ItemsetList::$brickFile, array(
                'data' => array_values($ubSets->getListviewData()),
                'id'   => 'used-by-itemset',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubSets->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }

        // tab: used by - item
        $conditions = array(
            'OR',
            ['AND', ['spellTrigger1', SPELL_TRIGGER_LEARN, '!'], ['spellId1', $this->subject->id]],
            ['AND', ['spellTrigger2', SPELL_TRIGGER_LEARN, '!'], ['spellId2', $this->subject->id]],
            ['AND', ['spellTrigger3', SPELL_TRIGGER_LEARN, '!'], ['spellId3', $this->subject->id]],
            ['AND', ['spellTrigger4', SPELL_TRIGGER_LEARN, '!'], ['spellId4', $this->subject->id]],
            ['AND', ['spellTrigger5', SPELL_TRIGGER_LEARN, '!'], ['spellId5', $this->subject->id]]
        );

        $ubItems = new ItemList($conditions);
        if (!$ubItems->error)
        {
            $this->lvTabs[] = [ItemList::$brickFile, array(
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
        if (!empty($ubSAI[Type::OBJECT]))
            $conditions[] = ['id', $ubSAI[Type::OBJECT]];

        $ubObjects = new GameObjectList($conditions);
        if (!$ubObjects->error)
        {
            $this->lvTabs[] = [GameObjectList::$brickFile, array(
                'data' => array_values($ubObjects->getListviewData()),
                'id'   => 'used-by-object',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubObjects->getJSGlobals());
        }

        // tab: used by - areatrigger
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            if (!empty($ubSAI[Type::AREATRIGGER]))
            {
                $ubTriggers = new AreaTriggerList(array(['id', $ubSAI[Type::AREATRIGGER]]));
                if (!$ubTriggers->error)
                {
                    $this->lvTabs[] = [AreaTriggerList::$brickFile, array(
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
            $this->lvTabs[] = [AchievementList::$brickFile, array(
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
            $lvName      = '$LANG.tab_contains';

            if ($extraItem && $this->subject->canCreateItem())
            {
                $foo = $this->subject->relItems->getListviewData();

                for ($i = 1; $i < 4; $i++)
                {
                    if (($bar = $this->subject->getField('effect'.$i.'CreateItemId')) && isset($foo[$bar]))
                    {
                        $lvName = '$LANG.tab_bonusloot';
                        $lv[$bar] = $foo[$bar];
                        $lv[$bar]['percent']  = $extraItem['additionalCreateChance'];
                        $lv[$bar]['pctstack'] = $this->buildPctStack($extraItem['additionalCreateChance'] / 100, $extraItem['additionalMaxNum']);
                        if ($max = ($extraItem['additionalMaxNum'] - 1))
                            $lv[$bar]['stack'] = [1, $max];

                        if (Conditions::extendListviewRow($lv[$bar], Conditions::SRC_NONE, $this->typeId, [Conditions::SPELL, $extraItem['requiredSpecialization']]))
                        {
                            $this->extendGlobalIds(Type::SPELL, $extraItem['requiredSpecialization']);
                            $extraCols[] = '$Listview.extraCols.condition';
                        }

                        break;                              // skill_extra_item_template can only contain 1 item
                    }
                }
            }

            $this->lvTabs[] = [ItemList::$brickFile, array(
                'data'       => array_values($lv),
                'name'       => $lvName,
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

                        if (!$stacks->hasSetFields('skillLines'))
                            $sH = ['skill'];

                        $tabData = array(
                            'data'        => array_values($data),
                            'id'          => 'spell-group-stack',
                            'name'        => Lang::spell('stackGroup'),
                            'visibleCols' => ['stackRules']
                        );

                        if (isset($sH))
                            $tabData['hiddenCols'] = $sH;

                        $this->lvTabs[] = [SpellList::$brickFile, $tabData];

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

            $this->lvTabs[] = [SpellList::$brickFile, array(
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
            ['AND', ['OR', ['effect1Id', SpellList::EFFECTS_TRIGGER], ['effect1AuraId', SpellList::AURAS_TRIGGER]], ['effect1TriggerSpell', $this->subject->id]],
            ['AND', ['OR', ['effect2Id', SpellList::EFFECTS_TRIGGER], ['effect2AuraId', SpellList::AURAS_TRIGGER]], ['effect2TriggerSpell', $this->subject->id]],
            ['AND', ['OR', ['effect3Id', SpellList::EFFECTS_TRIGGER], ['effect3AuraId', SpellList::AURAS_TRIGGER]], ['effect3TriggerSpell', $this->subject->id]],
        );

        $trigger = new SpellList($conditions);
        if (!$trigger->error)
        {
            $this->lvTabs[] = [SpellList::$brickFile, array(
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
        if (!empty($ubSAI[Type::NPC]))
            $conditions[] = ['id', $ubSAI[Type::NPC]];

        if ($auras = DB::World()->selectCol('SELECT `entry` FROM creature_template_addon WHERE `auras` REGEXP ?', '\\b'.$this->typeId.'\\b'))
            $conditions[] = ['id', $auras];

        $ubCreature = new CreatureList($conditions);
        if (!$ubCreature->error)
        {
            $this->lvTabs[] = [CreatureList::$brickFile, array(
                'data' => array_values($ubCreature->getListviewData()),
                'id'   => 'used-by-npc',
                'name' => '$LANG.tab_usedby'
            )];

            $this->extendGlobalData($ubCreature->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: zone
        if ($areaSpells = DB::World()->select('SELECT `area` AS ARRAY_KEY, `aura_spell` AS "0", `quest_start` AS "1", `quest_end` AS "2", `quest_start_status` AS "3", `quest_end_status` AS "4", `racemask` AS "5", `gender` AS "6" FROM spell_area WHERE spell = ?d', $this->typeId))
        {
            $zones = new ZoneList(array(['id', array_keys($areaSpells)]));
            if (!$zones->error)
            {
                $lvZones = $zones->getListviewData();
                $this->extendGlobalData($zones->getJSGlobals());

                $resultLv = [];
                $parents  = [];
                foreach ($areaSpells as $areaId => $condition)
                {
                    if (empty($lvZones[$areaId]))
                        continue;

                    $row = $lvZones[$areaId];

                    // attach to lv row and evaluate after merging
                    $row['__condition'] = $condition;

                    // merge subzones, into one row, if: spell_area data is identical && parentZone is shared
                    if ($p = $zones->getEntry($areaId)['parentArea'])
                    {
                        $parents[] = $p;
                        $row['__parent'] = $p;
                        $row['subzones'] = [$areaId];
                    }
                    else
                        $row['__parent'] = 0;

                    $set = false;
                    foreach ($resultLv as &$v)
                    {
                        if ($v['__parent'] != $row['__parent'] && $v['id'] != $row['__parent'])
                            continue;

                        if ($v['__condition'] != $row['__condition'])
                            continue;

                        if (!$row['__parent'] && $v['id'] != $row['__parent'])
                            continue;

                        $set = true;
                        $v['subzones'][] = $row['id'];
                        break;
                    }

                    // add self as potential subzone; IF we are a parentZone without added children, we get filtered in JScript
                    if (!$set)
                    {
                        $row['subzones'] = [$row['id']];
                        $resultLv[] = $row;
                    }
                }

                // overwrite lvData with parent-lvData (condition and subzones are kept)
                if ($parents)
                {
                    $parents = (new ZoneList(array(['id', $parents])))->getListviewData();
                    foreach ($resultLv as &$_)
                        if (isset($parents[$_['__parent']]))
                            $_ = array_merge($_, $parents[$_['__parent']]);
                }

                $cnd = new Conditions();
                foreach ($resultLv as $idx => $lv)
                {
                    [$auraSpell, $questStart, $questEnd, $questStartState, $questEndState, $raceMask, $gender] = $lv['__condition'];

                    if ($auraSpell)
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $lv['id'], [$auraSpell > 0 ? Conditions::AURA : -Conditions::AURA, abs($auraSpell)]);

                    if ($questStart)
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $lv['id'], [Conditions::QUESTSTATE, $questStart, $questStartState]);

                    if ($questEnd && $questEnd != $questStart)
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $lv['id'], [Conditions::QUESTSTATE, $questEnd, $questEndState]);

                    if ($raceMask)
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $lv['id'], [Conditions::CHR_RACE, $raceMask]);

                    if ($gender != 2)                       // 2: both
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $lv['id'], [Conditions::GENDER, $gender]);

                    // remove temp storage from result
                    unset($resultLv[$idx]['__condition']);
                    unset($resultLv[$idx]['__parent']);
                }

                if ($cnd->toListviewColumn($resultLv, $extraCols))
                    $this->extendGlobalData($cnd->getJsGlobals());

                $tabData = ['data' => array_values($resultLv)];

                if ($extraCols)
                {
                    $tabData['extraCols']  = $extraCols;
                    $tabData['hiddenCols'] = ['instancetype'];
                }

                $this->lvTabs[] = [ZoneList::$brickFile, $tabData];
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

                if (!$teaches->hasSetFields('skillLines'))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs[] = [SpellList::$brickFile, $tabData];
            }
        }

        // tab: taught by npc
        if ($this->subject->getSources($s) && in_array(SRC_TRAINER, $s))
        {
            $trainers = DB::World()->select('
                SELECT  cdt.CreatureId AS ARRAY_KEY, ts.ReqSkillLine AS reqSkillId, ts.ReqSkillRank AS reqSkillValue, ts.ReqLevel AS reqLevel, ts.ReqAbility1 AS reqSpellId1, ts.reqAbility2 AS reqSpellId2
                FROM    creature_default_trainer cdt
                JOIN    trainer_spell ts ON ts.TrainerId = cdt.TrainerId
                WHERE   ts.SpellId = ?d',
                $this->typeId
            );

            if ($trainers)
            {
                $tbTrainer = new CreatureList(array(Cfg::get('SQL_LIMIT_NONE'), ['ct.id', array_keys($trainers)], ['s.guid', null, '!'], ['ct.npcflag', NPC_FLAG_TRAINER, '&']));
                if (!$tbTrainer->error)
                {
                    $this->extendGlobalData($tbTrainer->getJSGlobals());

                    $cnd   = new Conditions();
                    $skill = $this->subject->getField('skillLines');

                    foreach ($trainers as $tId => $train)
                    {
                        if ($_ = $train['reqLevel'])
                            $cnd->addExternalCondition(Conditions::SRC_NONE, $tId, [Conditions::LEVEL, $_, Conditions::OP_GT_E]);

                        if ($_ = $train['reqSkillId'])
                            if (count($skill) == 1 && $_ != $skill[0])
                                $cnd->addExternalCondition(Conditions::SRC_NONE, $tId, [Conditions::SKILL, $_, $train['reqSkillValue']]);

                        for ($i = 1; $i < 3; $i++)
                            if ($_ = $train['reqSpellId'.$i])
                                $cnd->addExternalCondition(Conditions::SRC_NONE, $tId, [Conditions::SPELL, $_]);
                    }

                    $lvData = $tbTrainer->getListviewData();
                    if ($cnd->toListviewColumn($lvData, $extraCols))
                        $this->extendGlobalData($cnd->getJsGlobals());

                    $lvTab = array(
                        'data' => array_values($lvData),
                        'id'   => 'taught-by-npc',
                        'name' => '$LANG.tab_taughtby',
                    );

                    if ($extraCols)
                        $lvTab['extraCols'] = $extraCols;

                    $this->lvTabs[] = [CreatureList::$brickFile, $lvTab];
                }
            }
        }

        // tab: taught by spell
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', SpellList::EFFECTS_TEACH], ['effect1TriggerSpell', $this->subject->id]],
            ['AND', ['effect2Id', SpellList::EFFECTS_TEACH], ['effect2TriggerSpell', $this->subject->id]],
            ['AND', ['effect3Id', SpellList::EFFECTS_TEACH], ['effect3TriggerSpell', $this->subject->id]],
        );

        $tbSpell = new SpellList($conditions);
        $tbsData = [];
        if (!$tbSpell->error)
        {
            $tbsData = $tbSpell->getListviewData();
            $this->lvTabs[] = [SpellList::$brickFile, array(
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
            $this->lvTabs[] = [QuestList::$brickFile, array(
                'data' => array_values($tbQuest->getListviewData()),
                'id'   => 'reward-from-quest',
                'name' => '$LANG.tab_rewardfrom'
            )];

            $this->extendGlobalData($tbQuest->getJSGlobals());
        }

        // tab: taught by item (i'd like to precheck $this->subject->sources, but there is no source:item only complicated crap like "drop" and "vendor")
        $conditions = array(
            'OR',
            ['AND', ['spellTrigger1', SPELL_TRIGGER_LEARN], ['spellId1', $this->subject->id]],
            ['AND', ['spellTrigger2', SPELL_TRIGGER_LEARN], ['spellId2', $this->subject->id]],
            ['AND', ['spellTrigger3', SPELL_TRIGGER_LEARN], ['spellId3', $this->subject->id]],
            ['AND', ['spellTrigger4', SPELL_TRIGGER_LEARN], ['spellId4', $this->subject->id]],
            ['AND', ['spellTrigger5', SPELL_TRIGGER_LEARN], ['spellId5', $this->subject->id]],
        );

        $tbItem = new ItemList($conditions);
        if (!$tbItem->error)
        {
            $this->lvTabs[] = [ItemList::$brickFile, array(
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
            $this->lvTabs[] = [EnchantmentList::$brickFile, array(
                'data' => array_values($enchList->getListviewData()),
                'name' => Util::ucFirst(Lang::game('enchantments'))
            ), 'enchantment'];

            $this->extendGlobalData($enchList->getJSGlobals());
        }

        // tab: sounds
        $data     = [];
        $seSounds = [];
        for ($i = 1; $i < 4; $i++)                          // sounds from screen effect
            if ($this->subject->getField('effect'.$i.'AuraId') == SPELL_AURA_SCREEN_EFFECT)
               $seSounds = DB::Aowow()->selectRow('SELECT `ambienceDay`, `ambienceNight`, `musicDay`, `musicNight` FROM ?_screeneffect_sounds WHERE `id` = ?d', $this->subject->getField('effect'.$i.'MiscValue'));

        $activitySounds = DB::Aowow()->selectRow('SELECT * FROM ?_spell_sounds WHERE id = ?d', $this->subject->getField('spellVisualId'));
        array_shift($activitySounds);                       // remove id-column
        if ($soundIDs = $activitySounds + $seSounds)
        {
            $sounds = new SoundList(array(['id', $soundIDs]));
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
                $this->lvTabs[] = [SoundList::$brickFile, $tabData];
            }
        }


        // find associated NPC, Item and merge results
        // taughtbypets (unused..?)
        // taughtbyquest (usually the spell casted as quest reward teaches something; exclude those seplls from taughtBySpell)
        // taughtbytrainers
        // taughtbyitem

        // tab: conditions
        $cnd = new Conditions();
        $cnd->getBySourceEntry($this->typeId, Conditions::SRC_SPELL_IMPLICIT_TARGET, Conditions::SRC_SPELL, Conditions::SRC_SPELL_CLICK_EVENT, Conditions::SRC_VEHICLE_SPELL, Conditions::SRC_SPELL_PROC)
            ->getByCondition(Type::SPELL, $this->typeId)
            ->prepare();
        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs[] = $tab;
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

    private function buildPctStack(float $baseChance, int $maxStack) : string
    {
        // note: pctStack does not contain absolute values but chances relative to the overall drop chance
        // e.g.: dropChance is 17% then [1 => 50, 2 => 25, 3 => 25] displays > Stack of 1: 8%; Stack of 2: 4%; Stack of 3: 4%
        $maxStack = $maxStack ?: 1;
        $pctStack = [];
        for ($i = 1; $i <= $maxStack; $i++)
        {
            $pctStack[$i] = (($baseChance ** $i) * 100) / $baseChance;

            // remove chance from previous stacks
            if ($i > 1)
                $pctStack[$i-1] -= $pctStack[$i];
        }

        // cleanup tiny fractions
        $pctStack = array_filter($pctStack, function($x) use ($baseChance) { return $x * $baseChance >= 0.01; });

        return json_encode($pctStack, JSON_NUMERIC_CHECK);
    }

    private function appendReagentItem(&$reagentResult, $_iId, $_qty, $_mult, $_level, $_path, $alreadyUsed)
    {
        if (in_array($_iId, $alreadyUsed))
            return false;

        $item = DB::Aowow()->selectRow(
           'SELECT    `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, i.`id`, ic.`name` AS `iconString`, `quality`
            FROM      ?_items i
            LEFT JOIN ?_icons ic ON ic.`id` = i.`iconId`
            WHERE     i.`id` = ?d',
            $_iId
        );

        if (!$item)
            return false;

        $this->extendGlobalIds(Type::ITEM, $item['id']);

        $_level++;

        $data = array(
            'type'    => Type::ITEM,
            'typeId'  => $item['id'],
            'typeStr' => Type::getFileString(Type::ITEM),
            'quality' => $item['quality'],
            'name'    => Util::localizedString($item, 'name'),
            'icon'    => $item['iconString'],
            'qty'     => $_qty * $_mult,
            'path'    => $_path.'.'.Type::ITEM.'-'.$item['id'],
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
        $spells = DB::Aowow()->select(
           'SELECT `reagent1`,      `reagent2`,      `reagent3`,      `reagent4`,      `reagent5`,      `reagent6`,      `reagent7`,      `reagent8`,
                   `reagentCount1`, `reagentCount2`, `reagentCount3`, `reagentCount4`, `reagentCount5`, `reagentCount6`, `reagentCount7`, `reagentCount8`,
                   `name_loc0`,     `name_loc2`,     `name_loc3`,     `name_loc4`,     `name_loc6`,     `name_loc8`,
                   `iconIdBak`,
                   s.`id` AS ARRAY_KEY, ic.`name` AS `iconString`
            FROM   ?_spell s
            JOIN   ?_icons ic ON s.`iconId` = ic.`id`
            WHERE  (`effect1CreateItemId` = ?d AND `effect1Id` = ?d)',// OR
                // (`effect2CreateItemId` = ?d AND `effect2Id` = ?d) OR
                // (`effect3CreateItemId` = ?d AND `effect3Id` = ?d)',
            $_iId, SPELL_EFFECT_CREATE_ITEM //, $_iId, SPELL_EFFECT_CREATE_ITEM, $_iId, SPELL_EFFECT_CREATE_ITEM
        );

        if (!$spells)
            return false;

        $didAppendSomething = false;
        foreach ($spells as $sId => $row)
        {
            if (in_array(-$sId, $alreadyUsed))
                continue;

            $this->extendGlobalIds(Type::SPELL, $sId);

            $data = array(
                'type'    => Type::SPELL,
                'typeId'  => $sId,
                'typeStr' => Type::getFileString(Type::SPELL),
                'name'    => Util::localizedString($row, 'name'),
                'icon'    => $row['iconString'],
                'qty'     => $_qty,
                'path'    => $_path.'.'.Type::SPELL.'-'.$sId,
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

                // handle edge case elemental crafting material: Mote of X + Crystalized X
                // on use items, that has require more reagents of itself
                if ($row['reagentCount'.$i] == 9 && ($row['iconIdBak'] == 140 || $row['iconIdBak'] == 1921))
                    $row['reagentCount'.$i]++;

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

    private function createReagentList() : array
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
                    'type'    => Type::ITEM,
                    'typeId'  => $iId,
                    'typeStr' => Type::getFileString(Type::ITEM),
                    'quality' => $this->subject->relItems->getField('quality'),
                    'name'    => $this->subject->relItems->getField('name', true),
                    'icon'    => $this->subject->relItems->getField('iconString'),
                    'qty'     => $reagents[$iId][1],
                    'path'    => Type::ITEM.'-'.$iId,       // id of the html-element
                    'level'   => 0                          // depths in array, used for indentation
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

    private function createScalingData() : array            // calculation mostly like seen in TC
    {
        $scaling = array_merge(
            array(
                'directSP' => -1,
                'dotSP'    => -1,
                'directAP' =>  0,
                'dotAP'    =>  0
            ),
            (array)DB::World()->selectRow('SELECT `direct_bonus` AS `directSP`, `dot_bonus` AS `dotSP`, `ap_bonus` AS `directAP`, `ap_dot_bonus` AS `dotAP` FROM spell_bonus_data WHERE `entry` = ?d', $this->firstRank)
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

    private function createRequiredItems() : string
    {
        // parse itemClass & itemSubClassMask
        $class    = $this->subject->getField('equippedItemClass');
        $subClass = $this->subject->getField('equippedItemSubClassMask');
        $invType  = $this->subject->getField('equippedItemInventoryTypeMask');

        if ($class <= 0)
            return '';

        $tip  = 'Class: '.$class.'<br />SubClass: '.Util::asHex($subClass);
        $text = Lang::getRequiredItems($class, $subClass, false);

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

            $tip  .= '<br />'.Lang::item('slot').Lang::main('colon').Util::asHex($invType);
            $text .= ' '.Lang::spell('_inSlot').Lang::main('colon').implode(', ', $_);
        }

        return $this->fmtStaffTip($text, $tip);
    }

    private function createTools() : array
    {
        $tools = $this->subject->getToolsForCurrent();

        // prepare Tools
        foreach ($tools as &$tool)
            $tool['url'] = isset($tool['itemId']) ? '?item='.$tool['itemId'] : '?items&filter=cr=91;crs='.$tool['id'].';crv=0';

        return $tools;
    }

    private function createEffects() : array
    {
        // proc data .. maybe use more information..?
        $procData = array(
            'chance'   => $this->subject->getField('procChance'),
            'cooldown' => 0
        );

        if ($sp = DB::World()->selectRow('SELECT IF(`ProcsPerMinute` > 0, -`ProcsPerMinute`, `Chance`) AS `chance`, `Cooldown` AS `cooldown` FROM `spell_proc` WHERE ABS(`SpellId`) = ?d', $this->firstRank))
        {
            $procData['chance']   = $sp['chance']   ?: $procData['chance'];
            $procData['cooldown'] = $sp['cooldown'] ?: $procData['cooldown'];
        }

        $effects  = [];
        $spellIdx = array_unique(array_merge($this->subject->canTriggerSpell(), $this->subject->canTeachSpell()));
        $itemIdx  = $this->subject->canCreateItem();
        $perfItem = DB::World()->selectRow('SELECT `perfectItemType` AS `itemId`, `requiredSpecialization` AS `reqSpellId`, `perfectCreateChance` AS `chance` FROM skill_perfect_item_template WHERE `spellId` = ?d', $this->typeId);

        // Iterate through all effects:
        for ($i = 1; $i < 4; $i++)
        {
            if ($this->subject->getField('effect'.$i.'Id') <= 0)
                continue;

            $effId   = $this->subject->getField('effect'.$i.'Id');
            $effMV   = $this->subject->getField('effect'.$i.'MiscValue');
            $effMVB  = $this->subject->getField('effect'.$i.'MiscValueB');
            $effBP   = $this->subject->getField('effect'.$i.'BasePoints');
            $effDS   = $this->subject->getField('effect'.$i.'DieSides');
            $effRPPL = $this->subject->getField('effect'.$i.'RealPointsPerLevel');
            $effAura = $this->subject->getField('effect'.$i.'AuraId');

            /* Effect Format
             *
             * EffectName<: AuraName>< (effMV)>< [effMVB]>
             * Value: A<, plus y per level>
             * Radius: Byd
             * Interval: Cms
             * Mechanic: D
             * Proc Chance: E% || (E procs per minute)
             * Cooldown: Fs
             * < formated markup >
             * < iconlist >
             * < icon >
             */

            $_nameEffect = $_nameAura = $_nameMV = $_nameMVB = $_markup = '';
            $_icon   = $_perfItem = $_footer = [];

            $_footer['value'] = [0, 0];
            $valueFmt = '%s';

            // Icons:
            // .. from item
            if (in_array($i, $itemIdx))
            {
                if ($itemId = $this->subject->getField('effect'.$i.'CreateItemId'))
                {
                    $itemEntry = $this->subject->relItems->getEntry($itemId);

                    $_icon = array(
                        'type'    => Type::ITEM,
                        'typeId'  => $itemId,
                        'name'    => $itemEntry ? $this->subject->relItems->getField('name', true) : '',
                        'quality' => $itemEntry ? $this->subject->relItems->getField('quality') : '',
                        'count'   => ($effBP + 1) . ($effDS > 1 ? '-' . ($effBP + $effDS) : '')
                    );
                }

                // perfect Items
                if ($perfItem && $this->subject->relItems->getEntry($perfItem['itemId']))
                {
                    $cndSpell = new SpellList(array(['id', $perfItem['reqSpellId']]));
                    if (!$cndSpell->error)
                    {
                        $_perfItem = array(
                            'spellId'   => $perfItem['reqSpellId'],
                            'spellName' => $cndSpell->getField('name', true),
                            'itemId'    => $perfItem['itemId'],
                            'itemName'  => $this->subject->relItems->getField('name', true),
                            'quality'   => $this->subject->relItems->getField('quality'),
                            'icon'      => $cndSpell->getField('iconString'),
                            'chance'    => $perfItem['chance']
                        );
                    }
                }
            }
            // .. from spell
            else if (in_array($i, $spellIdx) || $effId == SPELL_EFFECT_UNLEARN_SPECIALIZATION)
            {
                if ($effId == SPELL_EFFECT_TITAN_GRIP)
                    $triggeredSpell = $effMV;
                else
                    $triggeredSpell = $this->subject->getField('effect'.$i.'TriggerSpell');

                if ($triggeredSpell > 0)                    // Dummy Auras are probably scripted
                {
                    $trig = new SpellList(array(['s.id', (int)$triggeredSpell]));

                    $_icon = array(
                        'type'    => Type::SPELL,
                        'typeId'  => $triggeredSpell,
                        'name'    => $trig->error ? '' : $trig->getField('name', true),
                        'quality' => 0,
                        'count'   => 0
                    );

                    $this->extendGlobalData($trig->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                }
            }

            // small text under effect name and resolved links .. order of adding to _footer determines order of output.

            // cases where we dont want 'Value' to be displayed [min, max, staffTT]
            if (!in_array($i, $itemIdx) && !in_array($effAura, [SPELL_AURA_MOD_TAUNT, SPELL_AURA_MOD_STUN, SPELL_AURA_MOD_SHAPESHIFT, SPELL_AURA_MECHANIC_IMMUNITY]) && !in_array($effId, [SPELL_EFFECT_PLAY_MUSIC]) && ($effBP + $effDS) != 0)
                $_footer['value'] = [$effBP + ($effDS ? 1 : 0), $effBP + $effDS];

            if ($this->subject->getField('effect'.$i.'RadiusMax') > 0)
                $_footer['radius'] = Lang::spell('_radius').Lang::main('colon').$this->subject->getField('effect'.$i.'RadiusMax').' '.Lang::spell('_distUnit');;

            if ($this->subject->getField('effect'.$i.'Periode') > 0)
                $_footer['interval'] = Lang::spell('_interval').Lang::main('colon').Util::formatTime($this->subject->getField('effect'.$i.'Periode'));

            if ($_ = $this->subject->getField('effect'.$i.'Mechanic'))
                $_footer['mechanic'] = Lang::game('mechanic').Lang::main('colon').Lang::game('me', $_);

            if (in_array($i, $this->subject->canTriggerSpell()) && $procData['chance'] && $procData['chance'] < 100)
            {
                $_footer['proc']   = $procData['chance'] < 0 ? Lang::spell('ppm', [Lang::nf(-$procData['chance'], 1)]) : Lang::spell('procChance') . Lang::main('colon') . $procData['chance'] . '%';
                if ($procData['cooldown'])
                    $_footer['procCD'] = Lang::game('cooldown', [$procData['cooldown']]);
            }

            // Effect Name
            if ($_ = Lang::spell('effects', $effId))
                $_nameEffect = $this->fmtStaffTip($_, 'EffectId: '.$effId);
            else
                $_nameEffect = Lang::spell('unkEffect', [$effId]);

            // parse masks and indizes
            switch ($effId)
            {
                case SPELL_EFFECT_ENERGIZE_PCT:
                    $valueFmt = '%s%%';
                case SPELL_EFFECT_POWER_DRAIN:
                case SPELL_EFFECT_ENERGIZE:
                case SPELL_EFFECT_POWER_BURN:
                    if ($_ = Lang::spell('powerTypes', $effMV))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);

                    if ($effMV == POWER_RAGE || $effMV == POWER_RUNIC_POWER)
                        array_walk($_footer['value'], fn(&$x) => $x /= 10);
                    break;
                case SPELL_EFFECT_BIND:
                    if ($effMV <= 0)
                        $_nameMV = $this->fmtStaffTip(Lang::spell('currentArea'), 'MiscValue: '.$effMV);
                    else if ($_ = ZoneList::getName($effMV))
                        $_nameMV = '<a href="?zone='.$effMV.'">'.$_.'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('zone')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_QUEST_COMPLETE:
                case SPELL_EFFECT_CLEAR_QUEST:
                case SPELL_EFFECT_QUEST_FAIL:
                    if ($_ = QuestList::getName($effMV))
                        $_nameMV = '<a href="?quest='.$effMV.'">'.$_.'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('quest')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_SUMMON_PET:
                    $effMVB = 67;                           // TC uses hardcoded summon property 67
                    // DO NOT BREAK !
                case SPELL_EFFECT_SUMMON:
                    if (($sp = DB::Aowow()->selectRow('SELECT `control`, `slot` FROM ?_summonproperties WHERE `id` = ?d', $effMVB)))
                        $_nameMVB = $this->fmtStaffTip(Lang::spell('summonControl', $sp['control']).' &ndash; '.Lang::spell('summonSlot', $sp['slot']) , 'SummonProperty: '.$effMVB);
                    // DO NOT BREAK !
                case SPELL_EFFECT_SUMMON_DEMON:
                case SPELL_EFFECT_KILL_CREDIT:
                case SPELL_EFFECT_KILL_CREDIT2:
                    if ($_ = CreatureList::getName($effMV))
                        $_nameMV = '<a href="?npc='.$effMV.'">'.$_.'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('npc')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_OPEN_LOCK:
                    if ($effMV && ($_ = Lang::spell('lockType', $effMV)))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_ENCHANT_ITEM:
                case SPELL_EFFECT_ENCHANT_ITEM_TEMPORARY:
                case SPELL_EFFECT_ENCHANT_HELD_ITEM:
                case SPELL_EFFECT_ENCHANT_ITEM_PRISMATIC:
                    if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantment WHERE id = ?d', $effMV))
                        $_nameMV = '<a href="?enchantment='.$effMV.'" class="q2">'.Util::localizedString($_, 'name').'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('enchantment')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_DISPEL:
                case SPELL_EFFECT_STEAL_BENEFICIAL_BUFF:
                    if ($effMV && ($_ = Lang::game('dt', $effMV)))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_LANGUAGE:
                    if ($effMV && ($_ = Lang::game('languages', $effMV)))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_TRANS_DOOR:
                case SPELL_EFFECT_SUMMON_OBJECT_WILD:
                case SPELL_EFFECT_SUMMON_OBJECT_SLOT1:
                case SPELL_EFFECT_SUMMON_OBJECT_SLOT2:
                case SPELL_EFFECT_SUMMON_OBJECT_SLOT3:
                case SPELL_EFFECT_SUMMON_OBJECT_SLOT4:
                    if ($_ = GameobjectList::getName($effMV))
                        $_nameMV = '<a href="?object='.$effMV.'">'.$_.'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('object')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_ACTIVATE_OBJECT:
                    if ($_ = Lang::gameObject('actions', $effMV))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_APPLY_GLYPH:
                    if ($_ = DB::Aowow()->selectCell('SELECT `spellId` FROM ?_glyphproperties WHERE `id` = ?d', $effMV))
                    {
                        if ($_ = SpellList::getName($effMV))
                            $_nameMV = '<a href="?spell='.$effMV.'">'.$_.'</a>';
                        else
                            $_nameMV = Util::ucFirst(Lang::game('spell')).' #'.$effMV;
                    }
                    break;
                case SPELL_EFFECT_SKINNING:
                    $_ = '';
                    switch ($effMV)
                    {
                        case 0:  $_ = Lang::game('ct', 1).', '.Lang::game('ct', 2); break;    // Skinning > Beast, Dragonkin
                        case 1:                                                               // Herb gathering > Elemental
                        case 2:  $_ = Lang::game('ct', 4); break;                             // Mining > Elemental
                        case 3:  $_ = Lang::game('ct', 9); break;                             // Dismantling > Mechanic
                    }
                    if ($_)
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_DISPEL_MECHANIC:
                    if ($_ = Lang::game('me', $effMV))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_SKILL_STEP:
                case SPELL_EFFECT_SKILL:
                    if ($_ = SkillList::getName($effMV))
                        $_nameMV = '<a href="?skill='.$effMV.'">'.$_.'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('skill')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_ACTIVATE_RUNE:
                    if ($_ = Lang::spell('powerRunes', $effMV))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_PLAY_SOUND:
                case SPELL_EFFECT_PLAY_MUSIC:
                    if (DB::Aowow()->selectCell('SELECT 1 FROM ?_sounds WHERE `id` = ?d', $effMV))
                    {
                        $_markup = '[sound='.$effMV.']';
                        $effMV = 0;                         // prevent default display
                    }
                    else
                        $_nameMV = Util::ucFirst(Lang::game('sound')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_REPUTATION:
                    if ($_ = FactionList::getName($effMV))
                        $_nameMV = '<a href="?faction='.$effMV.'">'.$_.'</a>';
                    else
                        $_nameMV = Util::ucFirst(Lang::game('faction')).' #'.$effMV;

                    // apply custom reward rated
                    if ($cuRate = DB::World()->selectCell('SELECT `spell_rate` FROM reputation_reward_rate WHERE `spell_rate` <> 1 AND `faction` = ?d', $effMV))
                        $_footer['value'][2] = sprintf(Util::$dfnString, Lang::faction('customRewRate'), ' ('.(($cuRate < 1 ? '-' : '+').intVal(($cuRate - 1) * $_footer['value'][0])).')');
                    break;
                case SPELL_EFFECT_SEND_TAXI:
                    $_ = DB::Aowow()->selectRow(
                       'SELECT tn1.`name_loc0` AS `start_loc0`, tn1.name_loc?d AS start_loc?d, tn2.`name_loc0` AS `end_loc0`, tn2.name_loc?d AS end_loc?d
                        FROM   ?_taxipath tp
                        JOIN   ?_taxinodes tn1 ON tp.`startNodeId` = tn1.`id`
                        JOIN   ?_taxinodes tn2 ON tp.`endNodeId` = tn2.`id`
                        WHERE  tp.`id` = ?d',
                        User::$localeId, User::$localeId, User::$localeId, User::$localeId, $effMV
                    );
                    if ($_)
                        $_nameMV = $this->fmtStaffTip('<span class="breadcrumb-arrow">'.Util::localizedString($_, 'start').'</span>'.Util::localizedString($_, 'end'), 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_TITAN_GRIP:
                    $effMV = 0;                             // effMV is trigger spell and was handled earlier
                    break;
                case SPELL_EFFECT_CAST_BUTTON:
                    $_nameMV = $effMV;                      // has a valid 0 value
                    break;
                // Aura
                case SPELL_EFFECT_APPLY_AURA:
                case SPELL_EFFECT_PERSISTENT_AREA_AURA:
                case SPELL_EFFECT_APPLY_AREA_AURA_PARTY:
                case SPELL_EFFECT_APPLY_AREA_AURA_RAID:
                case SPELL_EFFECT_APPLY_AREA_AURA_PET:
                case SPELL_EFFECT_APPLY_AREA_AURA_FRIEND:
                case SPELL_EFFECT_APPLY_AREA_AURA_ENEMY:
                case SPELL_EFFECT_APPLY_AREA_AURA_OWNER:
                {
                    if ($effAura > 0 && ($_ = Lang::spell('auras', $effAura)))
                        $_nameAura = $this->fmtStaffTip($_, 'AuraId: '.$effAura);
                    else if ($effAura > 0)
                        $_nameAura = Lang::spell('unkAura', [$effAura]);

                    switch ($effAura)
                    {
                        case SPELL_AURA_MOD_STEALTH_DETECT:
                            if ($_ = Lang::spell('stealthType', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_INVISIBILITY:
                        case SPELL_AURA_MOD_INVISIBILITY_DETECT:
                            if ($_ = Lang::spell('invisibilityType', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_POWER_REGEN_PERCENT:
                        case SPELL_AURA_MOD_INCREASE_ENERGY_PERCENT:
                        case SPELL_AURA_OBS_MOD_POWER:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_PERIODIC_ENERGIZE:
                        case SPELL_AURA_MOD_INCREASE_ENERGY:
                        case SPELL_AURA_MOD_POWER_REGEN:
                            if ($_ = Lang::spell('powerTypes', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_PERCENT_STAT:
                        case SPELL_AURA_MOD_TOTAL_STAT_PERCENTAGE:
                        case SPELL_AURA_MOD_SPELL_HEALING_OF_STAT_PERCENT:
                        case SPELL_AURA_MOD_RANGED_ATTACK_POWER_OF_STAT_PERCENT:
                        case SPELL_AURA_MOD_ATTACK_POWER_OF_STAT_PERCENT:
                        case SPELL_AURA_MOD_MANA_REGEN_FROM_STAT:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_MOD_STAT:
                            if ($effMV < 0)
                                $_nameMV = $this->fmtStaffTip(Lang::main('all'), 'MiscValue: '.$effMV);
                            else if ($_ = Lang::game('stats', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_SHAPESHIFT:
                            if ($_ = Lang::game('st', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_EFFECT_IMMUNITY:
                            if ($_ = Lang::spell('effects', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_STATE_IMMUNITY:
                            if ($_ = Lang::spell('auras', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_DEBUFF_RESISTANCE:
                        case SPELL_AURA_MOD_AURA_DURATION_BY_DISPEL:
                        case SPELL_AURA_MOD_AURA_DURATION_BY_DISPEL_NOT_STACK:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_DISPEL_IMMUNITY:
                            if ($_ = Lang::game('dt', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_TRACK_CREATURES:
                            if ($_ = Lang::game('ct', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_TRACK_RESOURCES:
                            if ($_ = Lang::spell('lockType', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_LANGUAGE:
                            if ($_ = Lang::game('languages', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_MECHANIC_DAMAGE_TAKEN_PERCENT:
                        case SPELL_AURA_MOD_MECHANIC_RESISTANCE:
                        case SPELL_AURA_MECHANIC_DURATION_MOD:
                        case SPELL_AURA_MECHANIC_DURATION_MOD_NOT_STACK:
                        case SPELL_AURA_MOD_DAMAGE_DONE_FOR_MECHANIC:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_MECHANIC_IMMUNITY:
                            if ($_ = Lang::game('me', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MECHANIC_IMMUNITY_MASK:
                            $_ = [];
                            foreach (Lang::game('me') as $k => $str)
                                if ($k && ($effMV & (1 << $k - 1)))
                                    $_[] = $str;

                            if ($_ = implode(', ', $_))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.Util::asHex($effMV));
                            break;
                        case SPELL_AURA_MOD_SPELL_DAMAGE_OF_STAT_PERCENT:
                        case SPELL_AURA_MOD_RESISTANCE_OF_STAT_PERCENT:
                            if ($_ = Lang::game('stats', $effMVB))
                                $_nameMVB = $this->fmtStaffTip($_, 'MiscValueB: '.$effMVB);
                            // ! DO NOT BREAK !
                        case SPELL_AURA_MOD_POWER_COST_SCHOOL_PCT:
                        case SPELL_AURA_SPLIT_DAMAGE_PCT:
                        case SPELL_AURA_MOD_RESISTANCE_PCT:
                        case SPELL_AURA_MOD_HEALING_PCT:
                        case SPELL_AURA_MOD_BASE_RESISTANCE_PCT:
                        case SPELL_AURA_MOD_INCREASES_SPELL_PCT_TO_HIT:
                        case SPELL_AURA_MOD_HOT_PCT:
                        case SPELL_AURA_SHARE_DAMAGE_PCT:
                        case SPELL_AURA_MOD_THREAT:
                        case SPELL_AURA_MOD_DAMAGE_PERCENT_DONE:
                        case SPELL_AURA_MOD_DAMAGE_PERCENT_TAKEN:
                        case SPELL_AURA_MOD_HEALING_DONE_PERCENT:
                        case SPELL_AURA_MOD_WEAPON_CRIT_PERCENT:
                        case SPELL_AURA_MOD_CRITICAL_HEALING_AMOUNT:
                        case SPELL_AURA_MOD_SPELL_CRIT_CHANCE_SCHOOL:
                        case SPELL_AURA_REFLECT_SPELLS_SCHOOL:
                        case SPELL_AURA_REDUCE_PUSHBACK:
                        case SPELL_AURA_MOD_CRIT_DAMAGE_BONUS:
                        case SPELL_AURA_MOD_ATTACKER_SPELL_HIT_CHANCE:
                        case SPELL_AURA_MOD_TARGET_ABSORB_SCHOOL:
                        case SPELL_AURA_MOD_TARGET_ABILITY_ABSORB_SCHOOL:
                        case SPELL_AURA_MOD_AOE_DAMAGE_AVOIDANCE:
                        case SPELL_AURA_MOD_DAMAGE_FROM_CASTER:
                        case SPELL_AURA_MOD_CREATURE_AOE_DAMAGE_AVOIDANCE:
                        case SPELL_AURA_MOD_SPELL_DAMAGE_OF_ATTACK_POWER:
                        case SPELL_AURA_MOD_SPELL_HEALING_OF_ATTACK_POWER:
                        case SPELL_AURA_MOD_SPELL_DAMAGE_FROM_HEALING:        // ? Mod Spell & Healing Power by % of Int
                            $valueFmt = '%s%%';
                        case SPELL_AURA_SCHOOL_ABSORB:
                        case SPELL_AURA_MOD_DAMAGE_DONE:
                        case SPELL_AURA_MOD_DAMAGE_TAKEN:
                        case SPELL_AURA_MOD_RESISTANCE:
                        case SPELL_AURA_SCHOOL_IMMUNITY:
                        case SPELL_AURA_DAMAGE_IMMUNITY:
                        case SPELL_AURA_MOD_POWER_COST_SCHOOL:
                        case SPELL_AURA_MOD_BASE_RESISTANCE:
                        case SPELL_AURA_MANA_SHIELD:
                        case SPELL_AURA_MOD_HEALING:
                        case SPELL_AURA_MOD_TARGET_RESISTANCE:
                        case SPELL_AURA_MOD_HEALING_DONE:
                        case SPELL_AURA_MOD_RESISTANCE_EXCLUSIVE:
                        case SPELL_AURA_MOD_IMMUNE_AURA_APPLY_SCHOOL:         // ? Cancel Aura Buffer at % of Caster Health
                        case SPELL_AURA_MOD_IGNORE_TARGET_RESIST:
                        case SPELL_AURA_MOD_ATTACK_POWER_OF_ARMOR:            // ? Mod Attack Power by School Resistance
                        case SPELL_AURA_SCHOOL_HEAL_ABSORB:
                            if ($_ = Lang::getMagicSchools($effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.Util::asHex($effMV));
                            break;
                        case SPELL_AURA_MOD_SKILL:
                        case SPELL_AURA_MOD_SKILL_TALENT:
                            if ($_ = SkillList::getName($effMV))
                                $_nameMV = '<a href="?skill='.$effMV.'">'.$_.'</a>';
                            else
                                $_nameMV = Util::ucFirst(Lang::game('skill')).' #'.$effMV;
                            break;
                        case SPELL_AURA_ADD_PCT_MODIFIER:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_ADD_FLAT_MODIFIER:
                            if ($_ = Lang::spell('spellModOp', $effMV))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_MOD_RATING_FROM_STAT:
                            $valueFmt = '%s%%';
                            if ($_ = Lang::game('stats', $effMVB))
                                $_nameMVB = $this->fmtStaffTip($_, 'MiscValueB: '.$effMVB);
                            // DO NOT BREAK !
                        case SPELL_AURA_MOD_RATING:
                            foreach (Lang::spell('combatRatingMask') as $m => $str)
                            {
                                if ($effMV != $m)
                                    continue;
                                $_nameMV = $this->fmtStaffTip($str, 'MiscValue: '.Util::asHex($effMV));
                                break 2;
                            }

                            $_ = [];
                            foreach (Lang::spell('combatRating') as $k => $str)
                                if ((1 << $k) & $effMV)
                                    $_[] = $str;

                            if ($_ = implode(', ', $_))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.Util::asHex($effMV));
                            break;
                        case SPELL_AURA_MOD_DAMAGE_DONE_VERSUS:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_MOD_DAMAGE_DONE_CREATURE:
                        case SPELL_AURA_MOD_MELEE_ATTACK_POWER_VERSUS:
                        case SPELL_AURA_MOD_RANGED_ATTACK_POWER_VERSUS:
                        case SPELL_AURA_MOD_FLAT_SPELL_DAMAGE_VERSUS:
                            $_ = [];
                            foreach (Lang::game('ct') as $k => $str)
                                if ($k && ($effMV & (1 << $k - 1)))
                                    $_[] = $str;

                            if ($_ = implode(', ', $_))
                                $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.Util::asHex($effMV));
                            break;
                        case SPELL_AURA_CONVERT_RUNE:
                            $from = $effMV;
                            if ($_ = Lang::spell('powerRunes', $effMV))
                                $from = $_;

                            $to = $effMVB;
                            if ($_ = Lang::spell('powerRunes', $effMVB))
                                $to = $_;

                            $effMVB  = 0;                   // prevent default display
                            $_nameMV = $this->fmtStaffTip('<span class="breadcrumb-arrow">'.$from.'</span>', 'MiscValue: '.$effMV).$this->fmtStaffTip($to, 'MiscValueB: '.$effMVB);
                            break;
                        case SPELL_AURA_MOUNTED:
                        case SPELL_AURA_TRANSFORM:
                        case SPELL_AURA_CHANGE_MODEL_FOR_ALL_HUMANOIDS:
                        case SPELL_AURA_X_RAY:
                        case SPELL_AURA_MOD_FAKE_INEBRIATE:
                            if ($effMV && $_ = CreatureList::getName($effMV))
                                $_nameMV = '<a href="?npc='.$effMV.'">'.$_.'</a>';
                            else
                                $_nameMV = Util::ucFirst(Lang::game('npc')).' #'.$effMV;
                            break;
                        case SPELL_AURA_FORCE_REACTION:
                            $_footer['value'][0] = $this->fmtStaffTip(Lang::game('rep', $_footer['value'][0]), $_footer['value'][0]);
                            // DO NOT BREAK !
                        case SPELL_AURA_MOD_FACTION_REPUTATION_GAIN:
                            $valueFmt = '%s%%';
                            if ($_ = FactionList::getName($effMV))
                                $_nameMV = '<a href="?faction='.$effMV.'">'.$_.'</a>';
                            else
                                $_nameMV = Util::ucFirst(Lang::game('faction')).' #'.$effMV;
                            break;                          // also breaks for SPELL_AURA_FORCE_REACTION
                        case SPELL_AURA_OVERRIDE_SPELLS:
                            if ($so = DB::Aowow()->selectRow('SELECT `spellId1`, `spellId2`, `spellId3`, `spellId4`, `spellId5` FROM ?_spelloverride WHERE `id` = ?d', $effMV))
                            {
                                if ($so = array_filter($so))
                                {
                                    $this->extendGlobalData([Type::SPELL => $so]);
                                    $_markup = '[spell='.implode('], [spell=', $so).']';
                                    $effMV   = 0;           // prevent default display
                                }
                            }
                            break;
                        case SPELL_AURA_MOD_COMBAT_RESULT_CHANCE:
                            $valueFmt = '%s%%';
                        case SPELL_AURA_IGNORE_COMBAT_RESULT:
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
                                    trigger_error('unused case #'.$effMV.' found for aura #'.$effAura);
                            }

                            if ($what)
                                $_nameMV = $this->fmtStaffTip($what, 'MiscValue: '.$effMV);
                            break;
                        case SPELL_AURA_SCREEN_EFFECT:
                            if ($ses = DB::Aowow()->selectRow('SELECT `name`, `ambienceDay` AS "0", IF(`ambienceNight` <> `ambienceDay`, `ambienceNight`, 0) AS "1", `musicDay` AS "2", IF(`musicNight` <> `musicDay`, `musicNight`, 0) AS "3" FROM ?_screeneffect_sounds WHERE `id` = ?d', $effMV))
                            {
                                $_nameMV = $this->fmtStaffTip($ses['name'], 'MiscValue: '.$effMV);
                                for ($j = 0; $j < 4; $j++)
                                    if ($ses[$j])
                                        $_markup .= '[sound='.$ses[$j].']';
                            }
                            break;
                        case SPELL_AURA_MOD_HEALTH_REGEN_PERCENT:
                        case SPELL_AURA_OBS_MOD_HEALTH:
                        case SPELL_AURA_MOD_ARMOR_PENETRATION_PCT:
                        case SPELL_AURA_MOD_SCALE:
                        case SPELL_AURA_MOD_SCALE_2:
                        case SPELL_AURA_MOD_SPEED_ALWAYS:
                        case SPELL_AURA_MOD_SPEED_SLOW_ALL:
                        case SPELL_AURA_MOD_SPEED_NOT_STACK:
                        case SPELL_AURA_MOD_INCREASE_SPEED:
                        case SPELL_AURA_MOD_INCREASE_MOUNTED_SPEED:
                        case SPELL_AURA_MOD_DECREASE_SPEED:
                        case SPELL_AURA_MOD_INCREASE_SWIM_SPEED:
                        case SPELL_AURA_MOD_PARRY_PERCENT:
                        case SPELL_AURA_MOD_DODGE_PERCENT:
                        case SPELL_AURA_MOD_BLOCK_PERCENT:
                        case SPELL_AURA_MOD_BLOCK_CRIT_CHANCE:
                        case SPELL_AURA_MOD_HIT_CHANCE:
                        case SPELL_AURA_MOD_CRIT_PCT:
                        case SPELL_AURA_MOD_SPELL_HIT_CHANCE:
                        case SPELL_AURA_MOD_SPELL_CRIT_CHANCE:
                            $valueFmt = '%s%%';
                            break;
                    }
                    break;
                }
                case SPELL_EFFECT_RESURRECT:
                case SPELL_EFFECT_SPIRIT_HEAL:
                case SPELL_EFFECT_WEAPON_PERCENT_DAMAGE:
                case SPELL_EFFECT_DURABILITY_DAMAGE_PCT:
                case SPELL_EFFECT_MODIFY_THREAT_PERCENT:
                case SPELL_EFFECT_REDIRECT_THREAT:
                case SPELL_EFFECT_HEAL_PCT:
                    $valueFmt = '%s%%';
                    break;
            }

            if ($_footer['value'][1])
            {
                $buffer = Lang::spell('_value').Lang::main('colon').sprintf($valueFmt, $_footer['value'][0]);
                if ($_footer['value'][0] != $_footer['value'][1])
                    $buffer .= Lang::game('valueDelim').sprintf($valueFmt, $_footer['value'][1]);
                if ($effRPPL != 0)
                    $buffer .= sprintf(Lang::spell('costPerLevel'), sprintf($valueFmt, $effRPPL));
                if (isset($_footer['value'][2]))
                    $buffer .= $_footer['value'][2];

                $_footer['value'] = $buffer;
            }
            else
                unset($_footer['value']);

            $_header = $_nameEffect;

            if ($_nameAura)
                $_header .= Lang::main('colon').$_nameAura;

            if (strlen($_nameMV))
                $_header .= ' ('.$_nameMV.')';
            else if ($effMV)
                $_header .= ' ('.$effMV.')';

            if (strlen($_nameMVB))
                $_header .= ' ['.$_nameMVB.']';
            else if ($effMVB)
                $_header .= ' ['.$effMVB.']';

            $effects[$i] = array(
                'icon'        => $_icon,
                'perfectItem' => $_perfItem,
                'name'        => $_header,
                'footer'      => $_footer
            );
        }

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

        $cbEquippedWeapon = function($mask, $useInvType)
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
