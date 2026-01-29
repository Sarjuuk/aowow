<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    private const MOD_AURAS = [SPELL_AURA_ADD_FLAT_MODIFIER,      SPELL_AURA_ADD_PCT_MODIFIER,                 SPELL_AURA_NO_REAGENT_USE,
                               SPELL_AURA_ABILITY_PERIODIC_CRIT,  SPELL_AURA_MOD_TARGET_ABILITY_ABSORB_SCHOOL, SPELL_AURA_ABILITY_IGNORE_AURASTATE,
                               SPELL_AURA_ALLOW_ONLY_ABILITY,     SPELL_AURA_IGNORE_MELEE_RESET,               SPELL_AURA_ABILITY_CONSUME_NO_AMMO,
                               SPELL_AURA_MOD_IGNORE_SHAPESHIFT,  SPELL_AURA_PERIODIC_HASTE,                   SPELL_AURA_OVERRIDE_CLASS_SCRIPTS,
                               SPELL_AURA_MOD_DAMAGE_FROM_CASTER, SPELL_AURA_ADD_TARGET_TRIGGER,               SPELL_AURA_IGNORE_COMBAT_RESULT,     /* SPELL_AURA_DUMMY ? */];

    protected  int    $cacheType   = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template    = 'spell';
    protected  string $pageName    = 'spell';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 1];

    public  int    $type       = Type::SPELL;
    public  int    $typeId     = 0;
    public  array  $reagents   = [false, null];
    public  string $items      = '';
    public  array  $tools      = [];
    public  array  $effects    = [];
    public  array  $attributes = [];
    public  string $powerCost  = '';
    public  string $castTime   = '';
    public  string $level      = '';
    public  string $rangeName  = '';
    public  string $range      = '';
    public  string $gcd        = '';
    public  string $gcdCat     = '';
    public  string $school     = '';
    public ?string $dispel     = null;
    public ?string $mechanic   = null;
    public  string $stances    = '';
    public  string $cooldown   = '';
    public  string $duration   = '';
    public  array  $tooltip    = [];

    private SpellList $subject;
    private int       $firstRank    = 0;
    private array     $modelInfo    = [];
    private array     $difficulties = [];
    private int       $mapType      = 0;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new SpellList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('spell'), Lang::spell('notFound'));

        if ($jsg = $this->subject->getJSGlobals(GLOBALINFO_ANY, $extra))
            $this->extendGlobalData($jsg, $extra);

        $this->modelInfo     = $this->subject->getModelInfo($this->typeId);
        if ($spelldifficulty = DB::Aowow()->select(         // has difficulty versions of itself
            'SELECT `normal10` AS "0", `normal25` AS "1",
                    `heroic10` AS "2", `heroic25` AS "3",
                    `mapType`  AS ARRAY_KEY
             FROM   ?_spelldifficulty
             WHERE  `normal10` = ?d OR `normal25` = ?d OR
                    `heroic10` = ?d OR `heroic25` = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId
        ))
        {
            $this->mapType      = key($spelldifficulty);
            $this->difficulties = array_pop($spelldifficulty);
        }

        // returns self or firstRank
        if ($fr = DB::World()->selectCell('SELECT `first_spell_id` FROM spell_ranks WHERE `spell_id` = ?d', $this->typeId))
            $this->firstRank = $fr;
        else
            $this->firstRank = DB::Aowow()->selectCell(
               'SELECT      IF(s1.`RankNo` <> 1 AND s2.`id`, s2.`id`, s1.`id`)
                FROM        ?_spell s1
                LEFT JOIN   ?_spell s2
                    ON      s1.`SpellFamilyId`     = s2.`SpelLFamilyId`     AND s1.`SpellFamilyFlags1` = s2.`SpelLFamilyFlags1` AND
                            s1.`SpellFamilyFlags2` = s2.`SpellFamilyFlags2` AND s1.`SpellFamilyFlags3` = s2.`SpellFamilyFlags3` AND
                            s1.`name_loc0` = s2.`name_loc0`                 AND s2.`RankNo` = 1
                WHERE       s1.`id` = ?d',
                $this->typeId
            );

        $this->h1 = Util::htmlEscape($this->subject->getField('name', true));

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->subject->getField('name', true)
        );


        /*************/
        /* Menu Path */
        /*************/

        $this->generatePath();


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('spell')));


        /***********/
        /* Infobox */
        /***********/

        $this->createInfobox();


        /***************/
        /* Red Buttons */
        /***************/

        $this->redButtons = array(
            BUTTON_VIEW3D  => false,
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => array(
                'linkColor' => 'ff71d5ff',
                'linkId'    => Type::getFileString(Type::SPELL).':'.$this->typeId,
                'linkName'  => $this->subject->getField('name', true),
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );

        // could have multiple models set, one per effect
        foreach ($this->modelInfo as $mI)
        {
            $this->redButtons[BUTTON_VIEW3D] = ['type' => $mI['type'], 'displayId' => $mI['displayId']];

            if (isset($mI['humanoid']))
            {
                $this->redButtons[BUTTON_VIEW3D]['typeId']   = $mI['typeId'];
                $this->redButtons[BUTTON_VIEW3D]['humanoid'] = 1;
            }

            break;
        }


        /*******************/
        /* Reagent Listing */
        /*******************/

        $this->createReagentList();


        /******************/
        /* Required Items */
        /******************/

        $this->createRequiredItems();


        /*************************/
        /* Required Tools/Totems */
        /*************************/

        // prepare Tools
        foreach ($this->subject->getToolsForCurrent() as $tool)
            $this->tools[] = new IconElement(
                Type::ITEM,
                $tool['itemId'] ?? 0,
                $tool['name'],
                quality: ITEM_QUALITY_NORMAL,
                size: IconElement::SIZE_SMALL,
                url: !isset($tool['itemId']) ? '?items&filter=cr=91;crs='.$tool['id'].';crv=0' : '',
                element: 'iconlist-icon'
            );


        /**********************/
        /* Spell Effect Block */
        /**********************/

        $this->createEffects();


        /**************************************************/
        /* Spell Attributes listing and SpellFilter links */
        /**************************************************/

        $this->createAttributesList();


        /*************************/
        /* Factionchange pendant */
        /*************************/

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = ?d, `alliance_id`, -`horde_id`) FROM player_factionchange_spells WHERE `alliance_id` = ?d OR `horde_id` = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altSpell = new SpellList(array(['id', abs($pendant)]));
            if (!$altSpell->error)
            {
                $this->transfer = Lang::spell('_transfer', array(
                    $altSpell->id,
                    ITEM_QUALITY_NORMAL,
                    $altSpell->getField('iconString'),
                    $altSpell->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ));
            }
        }


        /****************/
        /* Main Content */
        /****************/

        $this->powerCost = $this->subject->createPowerCostForCurrent();
        $this->castTime  = $this->subject->createCastTimeForCurrent(false, false);
        $this->level     = $this->subject->getField('spellLevel');
        $this->rangeName = $this->subject->getField('rangeText', true);
        $this->gcd       = DateTime::formatTimeElapsedFloat($this->subject->getField('startRecoveryTime'));
        $this->school    = $this->fmtStaffTip(Lang::getMagicSchools($this->subject->getField('schoolMask')), Util::asHex($this->subject->getField('schoolMask')));
        $this->dispel    = $this->subject->getField('dispelType') ? Lang::game('dt', $this->subject->getField('dispelType')) : null;
        $this->mechanic  = $this->subject->getField('mechanic') ? Lang::game('me', $this->subject->getField('mechanic')) : null;
        $this->tooltip   = array(
            $this->subject->getField('iconString'),
            $this->subject->getField('stackAmount') ?: ($this->subject->getField('procCharges') > 1 ? $this->subject->getField('procCharges') : 0),
            $this->subject->getField('buff', true, true) ? 1 : 0
        );
        $this->gcdCat    = match((int)$this->subject->getField('startRecoveryCategory'))
        {
            133     => Lang::spell('normal'),
            330,                                            // Mounts
            1156,                                           // Heart of the Phoenix
            1159,                                           // Ignis Grab and Slag Pot
            1164,                                           // Kessel Run Elek
            1173,                                           // Birmingham Test Spells
            1178,                                           // Stealth (Druid Cat, Rogue, Hunter Cat Pets) + Charge (Warrior)
            1244    => Lang::spell('special'),              // Argent Tournament Vehcile Jousting Abilities
            default => ''                                   // n/a
        };

        $this->range = $this->subject->getField('rangeMaxHostile');
        if ($_ = $this->subject->getField('rangeMinHostile'))
            $this->range = $_.' - '.$this->range;

        if (!($this->subject->getField('attributes2') & SPELL_ATTR2_NOT_NEED_SHAPESHIFT))
            $this->stances = Lang::getStances($this->subject->getField('stanceMask'));

        if (($_ = $this->subject->getField('recoveryTime')) && $_ > 0)
            $this->cooldown = DateTime::formatTimeElapsedFloat($_);
        else if (($_ = $this->subject->getField('recoveryCategory')) && $_ > 0)
            $this->cooldown = DateTime::formatTimeElapsedFloat($_);

        if (($_ = $this->subject->getField('duration')) && $_ > 0)
            $this->duration = DateTime::formatTimeElapsedFloat($_);


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        $ubSAI = SmartAI::getOwnerOfSpellCast($this->typeId);

        // tab: abilities [of shapeshift form]
        $formSpells = [];
        for ($i = 1; $i < 4; $i++)
            if ($this->subject->getField('effect'.$i.'AuraId') == SPELL_AURA_MOD_SHAPESHIFT)
                if ($_ = DB::Aowow()->selectRow('SELECT `spellId1`, `spellId2`, `spellId3`, `spellId4`, `spellId5`, `spellId6`, `spellId7`, `spellId8` FROM ?_shapeshiftforms WHERE `id` = ?d', $this->subject->getField('effect'.$i.'MiscValue')))
                    $formSpells = array_merge($formSpells, $_);

        if ($formSpells)
        {
            $abilities = new SpellList(array(['id', $formSpells]));
            if (!$abilities->error)
            {
                $tabData = array(
                    'data'        => $abilities->getListviewData(),
                    'id'          => 'controlledabilities',
                    'name'        => '$LANG.tab_controlledabilities',
                    'visibleCols' => ['level'],
                );

                if (!$abilities->hasSetFields('skillLines'))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));

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
            if (!in_array($this->subject->getField('effect'.$i.'AuraId'), self::MOD_AURAS))
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
                        $classSpells[$id] = [new IconElement(Type::SPELL, $id, $modSpells->getField('name', true), size: IconElement::SIZE_SMALL), []];
                    else
                        $miscSpells[$id]  = [new IconElement(Type::SPELL, $id, $modSpells->getField('name', true), size: IconElement::SIZE_SMALL), []];
                }

                if ($classSpells)
                {
                    foreach (DB::World()->select('SELECT `spell_id` AS ARRAY_KEY, `first_spell_id` AS "0", `rank` AS "1" FROM spell_ranks WHERE `spell_id` IN (?a)', array_keys($classSpells)) as $spellId => [$firstSpellId, $rank])
                    {
                        $classSpells[$firstSpellId][1][0] = min($classSpells[$firstSpellId][1][0] ?? $rank, $rank);
                        $classSpells[$firstSpellId][1][1] = max($classSpells[$firstSpellId][1][1] ?? $rank, $rank);

                        if ($spellId != $firstSpellId)
                            unset($classSpells[$spellId]);
                    }

                    array_walk($classSpells, function(&$x) {
                        if ($x[1] && $x[1][0] == $x[1][1])  // only one rank => unset
                            $x[1] = null;
                    });
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
                'data'        => $modifiesData,
                'id'          => 'modifies',
                'name'        => '$LANG.tab_modifies',
                'visibleCols' => ['level'],
            );

            if ($hideSkillCol)
                $tabData['hiddenCols'] = ['skill'];

            $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
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
                ['s.effect'.$i.'AuraId', self::MOD_AURAS],
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
                    'data'        => $modsSpell->getListviewData(),
                    'id'          => 'modified-by',
                    'name'        => '$LANG.tab_modifiedby',
                    'visibleCols' => ['level'],
                );

                if (!$modsSpell->hasSetFields('skillLines'))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));

                $this->extendGlobalData($modsSpell->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tab: see also
        $conditions = array(
            ['s.schoolMask', $this->subject->getField('schoolMask')],
            ['s.effect1Id', $this->subject->getField('effect1Id')],
            ['s.effect2Id', $this->subject->getField('effect2Id')],
            ['s.effect3Id', $this->subject->getField('effect3Id')],
            ['s.id', $this->typeId, '!'],
            ['s.name_loc'.Lang::getLocale()->value, $this->subject->getField('name', true)]
        );

        if ($this->difficulties)
            $conditions = ['OR', ['AND', ...$conditions], ['AND', ['s.id', $this->difficulties], ['s.id', $this->typeId, '!']]];

        $saSpells = new SpellList($conditions);
        if (!$saSpells->error)
        {
            $data = $saSpells->getListviewData();
            if ($this->difficulties)
            {
                $saE = ['$Listview.extraCols.mode'];

                foreach ($data as $id => &$d)
                {
                    if (($modeBit = array_search($id, $this->difficulties)) !== false)
                    {
                        if ($this->mapType)
                            $d['modes'] = ['mode' => 1 << ($modeBit + 3)];
                        else
                            $d['modes'] = ['mode' => 2 - $modeBit];
                    }
                    else
                        $d['modes'] = ['mode' => 0];
                }
            }

            $tabData = array(
                'data'        => $data,
                'id'          => 'see-also',
                'name'        => '$LANG.tab_seealso',
                'visibleCols' => ['level'],
            );

            if (!$saSpells->hasSetFields('skillLines'))
                $tabData['hiddenCols'] = ['skill'];

            if (isset($saE))
                $tabData['extraCols'] = $saE;

            $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));

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
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $cdSpells->getListviewData(),
                    'name' => '$LANG.tab_sharedcooldown',
                    'id'   => 'shared-cooldown'
                ), SpellList::$brickFile));

                $this->extendGlobalData($cdSpells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        // tab: glyphs
        if ($gpIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_glyphproperties WHERE `spellId` = ?d', $this->typeId))
        {
            $conditions = array(
                'OR',
                ['AND', ['effect1Id', SPELL_EFFECT_APPLY_GLYPH], ['effect1MiscValue', $gpIds]],
                ['AND', ['effect2Id', SPELL_EFFECT_APPLY_GLYPH], ['effect2MiscValue', $gpIds]],
                ['AND', ['effect3Id', SPELL_EFFECT_APPLY_GLYPH], ['effect3MiscValue', $gpIds]]
            );
            $glyphSpells = new SpellList($conditions);
            if (!$glyphSpells->error)
            {
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $glyphSpells->getListviewData(),
                    'visibleCols' => ['singleclass', 'glyphtype'],
                    'id'          => 'glyphs',
                    'name'        => '$LANG.tab_glyphs'
                ), SpellList::$brickFile));

                $this->extendGlobalData($glyphSpells->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: used by - spell
        if ($so = DB::Aowow()->selectCell('SELECT `id` FROM ?_spelloverride WHERE `spellId1` = ?d OR `spellId2` = ?d OR `spellId3` = ?d OR `spellId4` = ?d OR `spellId5` = ?d', $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId))
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
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $ubSpells->getListviewData(),
                    'id'   => 'used-by-spell',
                    'name' => '$LANG.tab_usedby'
                ), SpellList::$brickFile));

                $this->extendGlobalData($ubSpells->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: used by - itemset
        $conditions = array(
            'OR',
            ['spell1', $this->typeId], ['spell2', $this->typeId], ['spell3', $this->typeId], ['spell4', $this->typeId],
            ['spell5', $this->typeId], ['spell6', $this->typeId], ['spell7', $this->typeId], ['spell8', $this->typeId]
        );

        $ubSets = new ItemsetList($conditions);
        if (!$ubSets->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubSets->getListviewData(),
                'id'   => 'used-by-itemset',
                'name' => '$LANG.tab_usedby'
            ), ItemsetList::$brickFile));

            $this->extendGlobalData($ubSets->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }

        // tab: used by - item
        $conditions = array(
            'OR',
            ['AND', ['spellTrigger1', SPELL_TRIGGER_LEARN, '!'], ['spellId1', $this->typeId]],
            ['AND', ['spellTrigger2', SPELL_TRIGGER_LEARN, '!'], ['spellId2', $this->typeId]],
            ['AND', ['spellTrigger3', SPELL_TRIGGER_LEARN, '!'], ['spellId3', $this->typeId]],
            ['AND', ['spellTrigger4', SPELL_TRIGGER_LEARN, '!'], ['spellId4', $this->typeId]],
            ['AND', ['spellTrigger5', SPELL_TRIGGER_LEARN, '!'], ['spellId5', $this->typeId]]
        );

        $ubItems = new ItemList($conditions);
        if (!$ubItems->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubItems->getListviewData(),
                'id'   => 'used-by-item',
                'name' => '$LANG.tab_usedby'
            ), ItemList::$brickFile));

            $this->extendGlobalData($ubItems->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: used by - object
        $conditions = array(
            'OR',
            ['onUseSpell', $this->typeId], ['onSuccessSpell', $this->typeId],
            ['auraSpell',  $this->typeId], ['triggeredSpell', $this->typeId]
        );
        if (!empty($ubSAI[Type::OBJECT]))
            $conditions[] = ['id', $ubSAI[Type::OBJECT]];

        $ubObjects = new GameObjectList($conditions);
        if (!$ubObjects->error)
        {
            $this->addDataLoader('zones');
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubObjects->getListviewData(),
                'id'   => 'used-by-object',
                'name' => '$LANG.tab_usedby'
            ), GameObjectList::$brickFile));

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
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data' => $ubTriggers->getListviewData(),
                        'id'   => 'used-by-areatrigger',
                        'name' => '$LANG.tab_usedby'
                    ), AreaTriggerList::$brickFile, 'areatrigger'));
                }
            }
        }

        // tab: criteria of
        $conditions = array(
            'AND',
            ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET, ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2, ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL,
                         ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2,     ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL]
            ],
            ['ac.value1', $this->typeId]
        );

        if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` IN (?a) AND `value1` = ?d', [ACHIEVEMENT_CRITERIA_DATA_TYPE_S_AURA, ACHIEVEMENT_CRITERIA_DATA_TYPE_T_AURA], $this->typeId))
            $conditions = ['OR', $conditions, ['ac.id', $extraCrt]];

        $coAchievemnts = new AchievementList($conditions);
        if (!$coAchievemnts->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $coAchievemnts->getListviewData(),
                'id'   => 'criteria-of',
                'name' => '$LANG.tab_criteriaof'
            ), AchievementList::$brickFile));

            $this->extendGlobalData($coAchievemnts->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }

        // tab: contains
        // spell_loot_template
        $spellLoot = new LootByContainer();
        if ($spellLoot->getByContainer(Loot::SPELL, [$this->typeId]))
        {
            $this->extendGlobalData($spellLoot->jsGlobals);

            $extraCols   = $spellLoot->extraCols;
            $extraCols[] = '$Listview.extraCols.percent';

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'            => $spellLoot->getResult(),
                'name'            => '$LANG.tab_contains',
                'id'              => 'contains',
                'hiddenCols'      => ['side', 'slot', 'source', 'reqlevel'],
                'extraCols'       => array_unique($extraCols),
                'computeDataFunc' => '$Listview.funcBox.initLootTable'
            ), ItemList::$brickFile));
        }

        // tab: bonus loot
        if ($extraItemData = DB::World()->select('SELECT `spellId` AS ARRAY_KEY, `additionalCreateChance` AS "0", `additionalMaxNum` AS "1" FROM skill_extra_item_template WHERE `requiredSpecialization` = ?d', $this->typeId))
        {
            $extraSpells = new SpellList(array(['id', array_keys($extraItemData)]));
            if (!$extraSpells->error)
            {
                $this->extendGlobalData($extraSpells->getJSGlobals(GLOBALINFO_RELATED));
                $lvItems = $extraSpells->getListviewData();

                foreach ($lvItems as $iId => $data)
                {
                    [$chance, $maxItr] = $extraItemData[$iId];

                    $lvItems[$iId]['count']       = 1;      // expected by js or the pct-col becomes unsortable
                    $lvItems[$iId]['percent']     = $chance;
                    $lvItems[$iId]['pctstack']    = $this->buildPctStack($chance / 100, $maxItr, $data['creates'][1]);
                    $lvItems[$iId]['creates'][2] *= $maxItr;
                }

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'       => $lvItems,
                    'name'       => '$LANG.tab_bonusloot',
                    'id'         => 'bonusloot',
                    'hiddenCols' => ['side', 'reqlevel'],
                    'extraCols'  => ['$Listview.extraCols.percent']
                ), SpellList::$brickFile));
            }
        }

        // tab: exclusive with
        if ($this->firstRank && DB::World()->selectCell('SELECT 1 FROM spell_group WHERE `spell_id` = ?d', $this->firstRank))
        {
            $groups = DB::World()->selectCol('SELECT `id` AS ARRAY_KEY, `spell_id` AS ARRAY_KEY2, `spell_id` FROM spell_group');
            // unpack recursion
            foreach ($groups as $i => $group)
            {
                foreach ($group as $j => $g)
                {
                    if ($g > 0)
                        continue;

                    foreach ($groups[-$g] ?? [] as $new)
                        $groups[$i][] = $new;

                    unset($group[$j]);
                }
            }

            // find ourselves
            if ($filtered = array_filter($groups, fn($x) => in_array($this->firstRank, $x)))
            {
                // get rule set
                $rules = DB::World()->selectCol('SELECT `group_id` AS ARRAY_KEY, `stack_rule` FROM spell_group_stack_rules WHERE `group_id` IN (?a)', array_keys($filtered));

                // only use groups that have rules set
                if ($filtered = array_intersect_key($filtered, $rules))
                {
                    $cnd = ['OR'];
                    foreach ($filtered as $gr)
                        $cnd[] = ['s.id', $gr];

                    $stacks = new SpellList($cnd);
                    if (!$stacks->error)
                    {
                        $lvData = $stacks->getListviewData();
                        $this->extendGlobalData($stacks->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                        if (!$stacks->hasSetFields('skillLines'))
                            $sH = ['skill'];

                        foreach ($filtered as $gId => $spellIds)
                        {
                            $data = [];
                            foreach ($spellIds as $id)
                                if (isset($lvData[$id]) && $id != $this->firstRank)
                                    $data[] = array_merge($lvData[$id], ['stackRule' => $rules[$gId]]);

                            if (!$data)
                                continue;

                            $tabData = array(
                                'data'        => $data,
                                'id'          => 'spell-group-stack-'.$gId,
                                'name'        => Lang::spell('stackGroup'),
                                'visibleCols' => ['stackRules']
                            );

                            if (isset($sH))
                                $tabData['hiddenCols'] = $sH;

                            $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
                        }
                    }
                }
            }
        }

        // tab: linked with
        $rows = DB::World()->select(
           'SELECT  `spell_trigger` AS "trigger", `spell_effect` AS "effect", `type`, IF(ABS(`spell_effect`) = ?d, ABS(`spell_trigger`), ABS(`spell_effect`)) AS "related"
            FROM    spell_linked_spell
            WHERE   ABS(`spell_effect`) = ?d OR ABS(`spell_trigger`) = ?d',
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

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'        => $data,
                'id'          => 'spell-link',
                'name'        => Lang::spell('linkedWith'),
                'hiddenCols'  => ['skill', 'name'],
                'visibleCols' => ['linkedTrigger', 'linkedEffect']
            ), SpellList::$brickFile));

            $this->extendGlobalData($linked->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        }


        // tab: triggered by
        $conditions = array(
            'OR',
            ['AND', ['OR', ['effect1Id', SpellList::EFFECTS_TRIGGER], ['effect1AuraId', SpellList::AURAS_TRIGGER]], ['effect1TriggerSpell', $this->typeId]],
            ['AND', ['OR', ['effect2Id', SpellList::EFFECTS_TRIGGER], ['effect2AuraId', SpellList::AURAS_TRIGGER]], ['effect2TriggerSpell', $this->typeId]],
            ['AND', ['OR', ['effect3Id', SpellList::EFFECTS_TRIGGER], ['effect3AuraId', SpellList::AURAS_TRIGGER]], ['effect3TriggerSpell', $this->typeId]],
        );

        $trigger = new SpellList($conditions);
        if (!$trigger->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $trigger->getListviewData(),
                'id'   => 'triggered-by',
                'name' => '$LANG.tab_triggeredby'
            ), SpellList::$brickFile));

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

        if ($spellClick = DB::World()->selectCol('SELECT `npc_entry` FROM npc_spellclick_spells WHERE `spell_id` = ?d', $this->typeId))
            $conditions[] = ['id', $spellClick];

        $ubCreature = new CreatureList($conditions);
        if (!$ubCreature->error)
        {
            $this->addDataLoader('zones');
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubCreature->getListviewData(),
                'id'   => 'used-by-npc',
                'name' => '$LANG.tab_usedby'
            ), CreatureList::$brickFile));

            $this->extendGlobalData($ubCreature->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: zone
        if ($areaSpells = DB::World()->select('SELECT `area` AS ARRAY_KEY, `aura_spell` AS "0", `quest_start` AS "1", `quest_end` AS "2", `quest_start_status` AS "3", `quest_end_status` AS "4", `racemask` AS "5", `gender` AS "6" FROM spell_area WHERE `spell` = ?d', $this->typeId))
        {
            $zones = new ZoneList(array(['id', array_keys($areaSpells)]));
            if (!$zones->error)
            {
                $lvZones = $zones->getListviewData();
                $this->extendGlobalData($zones->getJSGlobals());

                $resultLv  = [];
                $parents   = [];
                $extraCols = [];
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

                $tabData = ['data' => $resultLv];

                if ($extraCols)
                {
                    $tabData['extraCols']  = $extraCols;
                    $tabData['hiddenCols'] = ['instancetype'];
                }

                $this->lvTabs->addListviewTab(new Listview($tabData, ZoneList::$brickFile));
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
                    'data'        => $teaches->getListviewData(),
                    'id'          => 'teaches-spell',
                    'name'        => '$LANG.tab_teaches',
                    'visibleCols' => $vis,
                );

                if (!$teaches->hasSetFields('skillLines'))
                    $tabData['hiddenCols'] = ['skill'];

                $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
            }
        }

        // tab: taught by npc
        if ($this->subject->getRawSource(SRC_TRAINER))
        {
            $trainers = DB::World()->select(
               'SELECT  cdt.`CreatureId` AS ARRAY_KEY, ts.`ReqSkillLine` AS "reqSkillId", ts.`ReqSkillRank` AS "reqSkillValue", ts.`ReqLevel` AS "reqLevel", ts.`ReqAbility1` AS "reqSpellId1", ts.`reqAbility2` AS "reqSpellId2"
                FROM    creature_default_trainer cdt
                JOIN    trainer_spell ts ON ts.`TrainerId` = cdt.`TrainerId`
                WHERE   ts.`SpellId` = ?d',
                $this->typeId
            );

            if ($trainers)
            {
                $tbTrainer = new CreatureList(array(['ct.id', array_keys($trainers)], ['s.guid', null, '!'], ['ct.npcflag', NPC_FLAG_TRAINER, '&']));
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
                    $extraCols = [];
                    if ($cnd->toListviewColumn($lvData, $extraCols))
                        $this->extendGlobalData($cnd->getJsGlobals());

                    $tabData = array(
                        'data' => $lvData,
                        'id'   => 'taught-by-npc',
                        'name' => '$LANG.tab_taughtby',
                    );

                    if ($extraCols)
                        $tabData['extraCols'] = $extraCols;

                    $this->addDataLoader('zones');
                    $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile));
                }
            }
        }

        // tab: taught by spell
        $conditions = array(
            'OR',
            ['AND', ['effect1Id', SpellList::EFFECTS_TEACH], ['effect1TriggerSpell', $this->typeId]],
            ['AND', ['effect2Id', SpellList::EFFECTS_TEACH], ['effect2TriggerSpell', $this->typeId]],
            ['AND', ['effect3Id', SpellList::EFFECTS_TEACH], ['effect3TriggerSpell', $this->typeId]],
        );

        $tbSpell = new SpellList($conditions);
        $tbsData = [];
        if (!$tbSpell->error)
        {
            $tbsData = $tbSpell->getFoundIDs();
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $tbSpell->getListviewData(),
                'id'   => 'taught-by-spell',
                'name' => '$LANG.tab_taughtby'
            ), SpellList::$brickFile));

            $this->extendGlobalData($tbSpell->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: taught by quest
        $conditions = array(
            'OR',
            ['sourceSpellId',   $this->typeId],
            ['rewardSpell',     $this->typeId],
            ['rewardSpellCast', $this->typeId]
        );
        if ($tbsData)
            array_push($conditions, ['rewardSpell', $tbsData], ['rewardSpellCast', $tbsData]);

        $tbQuest = new QuestList($conditions);
        if (!$tbQuest->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $tbQuest->getListviewData(),
                'id'   => 'reward-from-quest',
                'name' => '$LANG.tab_rewardfrom'
            ), QuestList::$brickFile));

            $this->extendGlobalData($tbQuest->getJSGlobals());
        }

        // tab: taught by item (i'd like to precheck $this->subject->sources, but there is no source:item only complicated crap like "drop" and "vendor")
        $conditions = array(
            'OR',
            ['AND', ['spellTrigger1', SPELL_TRIGGER_LEARN], ['spellId1', $this->typeId]],
            ['AND', ['spellTrigger2', SPELL_TRIGGER_LEARN], ['spellId2', $this->typeId]],
            ['AND', ['spellTrigger3', SPELL_TRIGGER_LEARN], ['spellId3', $this->typeId]],
            ['AND', ['spellTrigger4', SPELL_TRIGGER_LEARN], ['spellId4', $this->typeId]],
            ['AND', ['spellTrigger5', SPELL_TRIGGER_LEARN], ['spellId5', $this->typeId]],
        );

        $tbItem = new ItemList($conditions);
        if (!$tbItem->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $tbItem->getListviewData(),
                'id'   => 'taught-by-item',
                'name' => '$LANG.tab_taughtby'
            ), ItemList::$brickFile));

            $this->extendGlobalData($tbItem->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: enchantments
        $conditions = array(
            'OR',
            ['AND', ['type1', [ENCHANTMENT_TYPE_COMBAT_SPELL, ENCHANTMENT_TYPE_EQUIP_SPELL, ENCHANTMENT_TYPE_USE_SPELL]], ['object1', $this->typeId]],
            ['AND', ['type2', [ENCHANTMENT_TYPE_COMBAT_SPELL, ENCHANTMENT_TYPE_EQUIP_SPELL, ENCHANTMENT_TYPE_USE_SPELL]], ['object2', $this->typeId]],
            ['AND', ['type3', [ENCHANTMENT_TYPE_COMBAT_SPELL, ENCHANTMENT_TYPE_EQUIP_SPELL, ENCHANTMENT_TYPE_USE_SPELL]], ['object3', $this->typeId]]
        );
        $enchList = new EnchantmentList($conditions);
        if (!$enchList->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $enchList->getListviewData(),
                'name' => Util::ucFirst(Lang::game('enchantments'))
            ), EnchantmentList::$brickFile, 'enchantment'));

            $this->extendGlobalData($enchList->getJSGlobals());
        }

        // tab: sounds
        $data     = [];
        $seSounds = [];
        for ($i = 1; $i < 4; $i++)                          // sounds from screen effect
            if ($this->subject->getField('effect'.$i.'AuraId') == SPELL_AURA_SCREEN_EFFECT)
               $seSounds = DB::Aowow()->selectRow('SELECT `ambienceDay`, `ambienceNight`, `musicDay`, `musicNight` FROM ?_screeneffect_sounds WHERE `id` = ?d', $this->subject->getField('effect'.$i.'MiscValue'));

        $activitySounds = DB::Aowow()->selectRow('SELECT * FROM ?_spell_sounds WHERE `id` = ?d', $this->subject->getField('spellVisualId'));
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

                $tabData = ['data' => $data];
                if ($activitySounds)
                    $tabData['visibleCols'] = ['activity'];

                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview($tabData, SoundList::$brickFile));
            }
        }

        // tab: unlocks (object or item)
        $lockIds = DB::Aowow()->selectCol(
           'SELECT `id` FROM ?_lock WHERE            (`type1` = ?d AND `properties1` = ?d) OR
            (`type2` = ?d AND `properties2` = ?d) OR (`type3` = ?d AND `properties3` = ?d) OR
            (`type4` = ?d AND `properties4` = ?d) OR (`type5` = ?d AND `properties5` = ?d)',
            LOCK_TYPE_SPELL, $this->typeId, LOCK_TYPE_SPELL, $this->typeId,
            LOCK_TYPE_SPELL, $this->typeId, LOCK_TYPE_SPELL, $this->typeId,
            LOCK_TYPE_SPELL, $this->typeId
        );

        // we know this spell effect is only in use on index 1
        if ($this->subject->getField('effect1Id') == SPELL_EFFECT_OPEN_LOCK && ($lockId = $this->subject->getField('effect1MiscValue')))
            $lockIds += DB::Aowow()->selectCol(
               'SELECT `id` FROM ?_lock WHERE            (`type1` = ?d AND `properties1` = ?d) OR
                (`type2` = ?d AND `properties2` = ?d) OR (`type3` = ?d AND `properties3` = ?d) OR
                (`type4` = ?d AND `properties4` = ?d) OR (`type5` = ?d AND `properties5` = ?d)',
                LOCK_TYPE_SKILL, $lockId, LOCK_TYPE_SKILL, $lockId,
                LOCK_TYPE_SKILL, $lockId, LOCK_TYPE_SKILL, $lockId,
                LOCK_TYPE_SKILL, $lockId
            );

        if ($lockIds)
        {
            // objects
            $lockedObj = new GameObjectList(array(['lockId', $lockIds]));
            if (!$lockedObj->error)
            {
                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $lockedObj->getListviewData(),
                    'name'        => '$LANG.tab_unlocks',
                    'id'          => 'unlocks-object',
                    'visibleCols' => $lockedObj->hasSetFields('reqSkill') ? ['skill'] : null
                ), GameObjectList::$brickFile));
            }

            $lockedItm = new ItemList(array(['lockId', $lockIds]));
            if (!$lockedItm->error)
            {
                $this->extendGlobalData($lockedItm->getJSGlobals(GLOBALINFO_SELF));

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $lockedItm->getListviewData(),
                    'name' => '$LANG.tab_unlocks',
                    'id'   => 'unlocks-item'
                ), ItemList::$brickFile));
            }
        }

        // find associated NPC, Item and merge results
        // taughtbypets (unused..?)
        // taughtbyquest (usually the spell casted as quest reward teaches something; exclude those seplls from taughtBySpell)
        // taughtbytrainers
        // taughtbyitem

        // tab: conditions
        $cnd = new Conditions();
        $cnd->getBySource([Conditions::SRC_SPELL_IMPLICIT_TARGET, Conditions::SRC_SPELL, Conditions::SRC_SPELL_CLICK_EVENT, Conditions::SRC_VEHICLE_SPELL, Conditions::SRC_SPELL_PROC], entry: $this->typeId)
            ->getByCondition(Type::SPELL, $this->typeId)
            ->prepare();
        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }


    /******************************************/
    /* SpellLoot recursive dropchance builder */
    /******************************************/

    private function buildPctStack(float $baseChance, int $maxStack, int $baseCount = 1) : string
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

        // cleanup rounding errors
        $pctStack = array_map(fn($x) => round($x, 3), $pctStack);

        // cleanup tiny fractions
        $pctStack = array_filter($pctStack, fn($x) => ($x * $baseChance) >= 0.01);

        if ($baseCount > 1)
            $pctStack = array_combine(array_map(fn($x) => $x * $baseCount, array_keys($pctStack)), $pctStack);

        return json_encode($pctStack, JSON_NUMERIC_CHECK);  // do not replace with Util::toJSON !
    }


    /**********************************/
    /* recursive reagent list builder */
    /**********************************/

    private function appendReagentItem(array &$reagentResult, int $itemId, int $qty, int $mult, int $level, string $path, array $alreadyUsed, int $fromSpell = 0) : bool
    {
        if (in_array($itemId, $alreadyUsed))
            return false;

        $item = DB::Aowow()->selectRow(
           'SELECT    `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, i.`id`, ic.`name` AS `iconString`, `quality`, `spellId1`, `spellCharges1`
            FROM      ?_items i
            LEFT JOIN ?_icons ic ON ic.`id` = i.`iconId`
            WHERE     i.`id` = ?d',
            $itemId
        );

        if (!$item)
            return false;

        $this->extendGlobalIds(Type::ITEM, $item['id']);

        // the spell calling this is also on the item and triggering it destroys the item
        // so effectively we need one more. (see elemental particles and enchantment essences)
        if ($fromSpell && $fromSpell == $item['spellId1'] && $item['spellCharges1'] == -1)
            $qty++;

        $level++;

        $data = array(
            'path'    => $path.'.'.Type::ITEM.'-'.$item['id'],
            'level'   => $level,
            'final'   => false,
            'typeStr' => Type::getFileString(Type::ITEM),
            'icon'    => new IconElement(Type::ITEM, $item['id'], Util::localizedString($item, 'name'), $qty * $mult, '', $item['quality'], IconElement::SIZE_SMALL, align: 'right', element: 'iconlist-icon')
        );

        $idx = count($reagentResult);
        $reagentResult[] = $data;
        $alreadyUsed[]   = $item['id'];

        if (!$this->appendReagentSpell($reagentResult, $item['id'], $qty * $mult, $data['level'], $data['path'], $alreadyUsed))
            $reagentResult[$idx]['final'] = true;

        return true;
    }

    private function appendReagentSpell(array &$reagentResult, int $itemId, int $qty, int $level, string $path, array $alreadyUsed) : bool
    {
        $level++;
        // assume that tradeSpells only use the first index to create items, so this runs somewhat efficiently >.<
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
            $itemId, SPELL_EFFECT_CREATE_ITEM //, $itemId, SPELL_EFFECT_CREATE_ITEM, $itemId, SPELL_EFFECT_CREATE_ITEM
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
                'path'    => $path.'.'.Type::SPELL.'-'.$sId,
                'level'   => $level,
                'final'   => false,
                'typeStr' => Type::getFileString(Type::SPELL),
                'icon'    => new IconElement(Type::SPELL, $sId, Util::localizedString($row, 'name'), $qty, size: IconElement::SIZE_SMALL, align: 'right', element: 'iconlist-icon')
            );

            $reagentResult[] = $data;
            $_aU   = $alreadyUsed;
            $_aU[] = -$sId;

            $hasUnusedReagents = false;
            for ($i = 1; $i < 9; $i++)
            {
                if ($row['reagent'.$i] <= 0 || $row['reagentCount'.$i] <= 0)
                    continue;

                if ($this->appendReagentItem($reagentResult, $row['reagent'.$i], $row['reagentCount'.$i], $qty, $data['level'], $data['path'], $_aU, $sId))
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

    private function createReagentList() : void
    {
        $reagentResult = [];
        $enhanced      = false;
        $reagents      = $this->subject->getReagentsForCurrent();

        if (!$reagents)
            return;

        foreach ($reagents as [$iId, $num])
        {
            $relItem = $this->subject->relItems->getEntry($iId);

            $data = array(
                'path'    => Type::ITEM.'-'.$iId,           // id of the html-element
                'level'   => 0,                             // depths in array, used for indentation
                'final'   => false,
                'typeStr' => Type::getFileString(Type::ITEM),
                'icon'    => new IconElement(
                    Type::ITEM,
                    is_null($relItem) ? 0 : $iId,
                    is_null($relItem) ? 'Item #'.$iId : $this->subject->relItems->getField('name', true),
                    $num,
                    quality: $relItem['quality'] ?? 'q',
                    size: IconElement::SIZE_SMALL,
                    link: !is_null($relItem),
                    align: 'right',
                    element: 'iconlist-icon'
                )
            );

            $idx = count($reagentResult);
            $reagentResult[] = $data;

            // start with self and current original item in usedEntries (spell < 0; item > 0)
            if ($this->appendReagentSpell($reagentResult, $iId, $reagents[$iId][1], 0, $data['path'], [-$this->typeId, $iId]))
                $enhanced = true;
            else
                $reagentResult[$idx]['final'] = true;
        }

        // increment all indizes (by prepending null and removing it again)
        array_unshift($reagentResult, null);
        unset($reagentResult[0]);

        $this->reagents = [$enhanced, $reagentResult];
    }

    private function calculateEffectScaling() : array       // calculation mostly like seen in TC
    {
        if ($this->subject->getField('attributes3') & SPELL_ATTR3_NO_DONE_BONUS)
            return [0, 0, 0, 0];

        if (!$this->subject->isScalableDamagingSpell() && !$this->subject->isScalableHealingSpell())
            return [0, 0, 0, 0];

        $scaling = [0, 0, 0, 0];
        $pMask   = $this->subject->periodicEffectsMask();
        $allDoTs = true;

        for ($i = 1; $i < 4; $i++)
        {
            if (!$this->subject->getField('effect'.$i.'Id'))
                continue;

            if ($pMask & 1 << ($i - 1))
            {
                $scaling[1] = $this->subject->getField('effect'.$i.'BonusMultiplier');
                continue;
            }
            else if ($this->subject->getField('damageClass') == SPELL_DAMAGE_CLASS_MAGIC)
                $scaling[0] = $this->subject->getField('effect'.$i.'BonusMultiplier');

            $allDoTs = false;
        }

        if ($s = DB::World()->selectRow('SELECT `direct_bonus` AS "0", `dot_bonus` AS "1", `ap_bonus` AS "2", `ap_dot_bonus` AS "3" FROM spell_bonus_data WHERE `entry` = ?d', $this->firstRank))
            $scaling = $s;

        if (!in_array($this->subject->getField('typeCat'), [-2, -3, -7, 7]) || $this->subject->getField('damageClass') == SPELL_DAMAGE_CLASS_NONE)
            return array_map(fn($x) => $x < 0 ? 0 : $x, $scaling);

        foreach ($scaling as $k => $v)
        {
            // recalculate if spell_bonus_data says so
            if ($v != -1)
                continue;

            // no known calculation for physical abilities
            if (in_array($k, [2, 3]))                       // [direct AP, DoT AP]
                continue;

            // dont use spellPower to scale physical Abilities
            if ($this->subject->getField('schoolMask') == (1 << SPELL_SCHOOL_NORMAL) && in_array($k, [0, 1]))
                continue;

            $isDOT = false;
            if (in_array($k, [1, 3]))                       // [DoT SP, DoT AP]
            {
                if ($pMask)
                    $isDOT = true;
                else
                    continue;
            }
            else if ($allDoTs)                              // if all used effects are periodic, dont calculate direct component
                continue;

            // damage over time spells bonus calculation
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

            // distribute damage over multiple effects, reduce by AoE
            $castingTime = $this->subject->getCastingTimeForBonus($isDOT);

            // 50% for damage and healing spells for leech spells from damage bonus and 0% from healing
            for ($j = 1; $j < 4; ++$j)
            {
                if ($this->subject->getField('effectId'.$j) == SPELL_EFFECT_HEALTH_LEECH || $this->subject->getField('effect'.$j.'AuraId') == SPELL_AURA_PERIODIC_LEECH)
                {
                    $castingTime /= 2;
                    break;
                }
            }

            if ($this->subject->isScalableHealingSpell())
                $castingTime *= 1.88;

            // SPELL_SCHOOL_MASK_NORMAL
            if ($this->subject->getField('schoolMask') != (1 << SPELL_SCHOOL_NORMAL))
                $scaling[$k] = ($castingTime / 3500.0) * $dotFactor;
            else
                $scaling[$k] = 0;                           // would be 1 ($dotFactor), but we dont want it to be displayed
        }

        return array_map(fn($x) => $x < 0 ? 0 : $x, $scaling);
    }

    private function createRequiredItems() : void
    {
        // parse itemClass & itemSubClassMask
        $class    = $this->subject->getField('equippedItemClass');
        $subClass = $this->subject->getField('equippedItemSubClassMask');
        $invType  = $this->subject->getField('equippedItemInventoryTypeMask');

        if ($class <= 0)
            return;

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
            $text .= ' '.Lang::spell('_inSlot').implode(', ', $_);
        }

        $this->items = $this->fmtStaffTip($text, $tip);
    }

    private function createEffects() : void
    {
        // proc data .. maybe use more information..?
        $procData = array(
            'chance'   => $this->subject->getField('procChance'),
            'cooldown' => 0
        );

        if ($sp = DB::World()->selectRow('SELECT IF(`ProcsPerMinute` > 0, -`ProcsPerMinute`, `Chance`) AS "chance", `Cooldown` AS "cooldown" FROM `spell_proc` WHERE ABS(`SpellId`) = ?d', $this->firstRank))
        {
            $procData['chance']   = $sp['chance']   ?: $procData['chance'];
            $procData['cooldown'] = $sp['cooldown'] ?: $procData['cooldown'];
        }

        $effects  = [];
        $spellIdx = array_unique(array_merge($this->subject->canTriggerSpell(), $this->subject->canTeachSpell()));
        $itemIdx  = $this->subject->canCreateItem();
        $perfItem = DB::World()->selectRow('SELECT `perfectItemType` AS "itemId", `requiredSpecialization` AS "reqSpellId", `perfectCreateChance` AS "chance" FROM skill_perfect_item_template WHERE `spellId` = ?d', $this->typeId);
        $scaling  = $this->calculateEffectScaling();

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
            $effPPCP = $this->subject->getField('effect'.$i.'PointsPerComboPoint');
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
            $_icon = $_perfItem = $_footer = [];

            $_footer['value'] = [0, 0];
            $valueFmt = '%s';

            // Icons:
            // .. from item
            if (in_array($i, $itemIdx))
            {
                if ($itemId = $this->subject->getField('effect'.$i.'CreateItemId'))
                {
                    $itemEntry = $this->subject->relItems->getEntry($itemId);

                    $_icon = new IconElement(
                        Type::ITEM,
                        $itemId,
                        $itemEntry ? $this->subject->relItems->getField('name', true) : Util::ucFirst(Lang::game('item')).' #'.$itemId,
                        $this->createNumRange($effBP, $effDS),
                        quality: $itemEntry ? $this->subject->relItems->getField('quality') : '',
                        link: !empty($itemEntry)
                    );
                }

                // perfect Items
                if ($perfItem && $this->subject->relItems->getEntry($perfItem['itemId']))
                {
                    $cndSpell = new SpellList(array(['id', $perfItem['reqSpellId']]));
                    if (!$cndSpell->error)
                    {
                        $_perfItem = array(
                            'spellId'   => $cndSpell->id,
                            'spellName' => $cndSpell->getField('name', true),
                            'icon'      => $cndSpell->getField('iconString'),
                            'chance'    => $perfItem['chance'],
                            'item'      => new IconElement(
                                Type::ITEM,
                                $perfItem['itemId'],
                                $this->subject->relItems->getField('name', true),
                                quality: $this->subject->relItems->getField('quality')
                            )
                        );
                    }
                }
                else if ($extraItem = DB::World()->selectRow('SELECT * FROM skill_extra_item_template WHERE `spellid` = ?d', $this->typeId))
                {
                    $cndSpell = new SpellList(array(['id', $extraItem['requiredSpecialization']]));
                    if (!$cndSpell->error)
                    {
                        $_perfItem = array(
                            'spellId'   => $cndSpell->id,
                            'spellName' => $cndSpell->getField('name', true),
                            'icon'      => $cndSpell->getField('iconString'),
                            'chance'    => $extraItem['additionalCreateChance'],
                            'item'      => new IconElement(
                                Type::ITEM,
                                $this->subject->relItems->id,
                                $this->subject->relItems->getField('name', true),
                                num: '+'.$this->createNumRange($effBP, $effDS, $extraItem['additionalMaxNum']),
                                quality: $this->subject->relItems->getField('quality')
                            )
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

                    $_icon = new IconElement(
                        Type::SPELL,
                        $triggeredSpell,
                        $trig->error ? Util::ucFirst(Lang::game('spell')).' #'.$triggeredSpell : $trig->getField('name', true),
                        link: !$trig->error
                    );

                    $this->extendGlobalData($trig->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                }
            }

            // small text under effect name and resolved links .. order of adding to _footer determines order of output.

            // cases where we dont want 'Value' to be displayed [min, max, staffTT]
            if (!in_array($i, $itemIdx) && !in_array($effAura, [SPELL_AURA_MOD_TAUNT, SPELL_AURA_MOD_STUN, SPELL_AURA_MOD_SHAPESHIFT, SPELL_AURA_MECHANIC_IMMUNITY]) && !in_array($effId, [SPELL_EFFECT_PLAY_MUSIC]) && ($effBP + $effDS) != 0)
                $_footer['value'] = [$effBP + ($effDS ? 1 : 0), $effBP + $effDS];

            if ($this->subject->getField('effect'.$i.'RadiusMax') > 0)
                $_footer['radius'] = Lang::spell('_radius').$this->subject->getField('effect'.$i.'RadiusMax').' '.Lang::spell('_distUnit');

            if ($this->subject->getField('effect'.$i.'Periode') > 0)
                $_footer['interval'] = Lang::spell('_interval').DateTime::formatTimeElapsedFloat($this->subject->getField('effect'.$i.'Periode'));

            if ($_ = $this->subject->getField('effect'.$i.'Mechanic'))
                $_footer['mechanic'] = Lang::game('mechanic').Lang::main('colon').Lang::game('me', $_);

            if (in_array($i, $this->subject->canTriggerSpell()) && $procData['chance'] && $procData['chance'] < 100)
            {
                $_footer['proc']   = $procData['chance'] < 0 ? Lang::spell('ppm', [-$procData['chance']]) : Lang::spell('procChance', [$procData['chance']]);
                if ($procData['cooldown'])
                    $_footer['procCD'] = Lang::game('cooldown', [DateTime::formatTimeElapsed($procData['cooldown'])]);
            }

            // Effect Name
            if ($_ = Lang::spell('effects', $effId))
                $_nameEffect = '<a href="?spells&filter=cr=109;crs='.$effId.';crv=0">'.$this->fmtStaffTip($_, 'EffectId: '.$effId).'</a>';
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
                    else if ($a = ZoneList::makeLink($effMV))
                        $_nameMV = $a;
                    else
                        $_nameMV = Util::ucFirst(Lang::game('zone')).' #'.$effMV;
                    break;
                case SPELL_EFFECT_QUEST_COMPLETE:
                case SPELL_EFFECT_CLEAR_QUEST:
                case SPELL_EFFECT_QUEST_FAIL:
                    if ($a = QuestList::makeLink($effMV))
                        $_nameMV = $a;
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
                    if ($a = CreatureList::makeLink($effMV))
                        $_nameMV = $a;
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
                    if ($a = EnchantmentList::makeLink($effMV, cssClass: 'q2'))
                        $_nameMV = $a;
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
                    if ($a = GameobjectList::makeLink($effMV))
                        $_nameMV = $a;
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
                        if ($a = SpellList::makeLink($_))
                            $_nameMV = $a;
                        else
                            $_nameMV = Util::ucFirst(Lang::game('spell')).' #'.$_;
                    }
                    break;
                case SPELL_EFFECT_SKINNING:
                    $_ = match ($effMV)
                    {
                        0       => Lang::game('ct', 1).', '.Lang::game('ct', 2), // Skinning > Beast, Dragonkin
                        1, 2    => Lang::game('ct', 4),                          // Gathering, Mining > Elemental
                        3       => Lang::game('ct', 9),                          // Dismantling > Mechanic
                        default => ''
                    };
                    if ($_)
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_DISPEL_MECHANIC:
                    if ($_ = Lang::game('me', $effMV))
                        $_nameMV = $this->fmtStaffTip($_, 'MiscValue: '.$effMV);
                    break;
                case SPELL_EFFECT_SKILL_STEP:
                case SPELL_EFFECT_SKILL:
                    if ($a = SkillList::makeLink($effMV))
                        $_nameMV = $a;
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
                    if ($a = FactionList::makeLink($effMV))
                        $_nameMV = $a;
                    else
                        $_nameMV = Util::ucFirst(Lang::game('faction')).' #'.$effMV;

                    // apply custom reward rated
                    if ($cuRate = DB::World()->selectCell('SELECT `spell_rate` FROM reputation_reward_rate WHERE `spell_rate` <> 1 AND `faction` = ?d', $effMV))
                        $_footer['value'][2] = sprintf(Util::$dfnString, Lang::faction('customRewRate'), ' ('.(($cuRate < 1 ? '-' : '+').intVal(($cuRate - 1) * $_footer['value'][0])).')');
                    break;
                case SPELL_EFFECT_SEND_TAXI:
                    $_ = DB::Aowow()->selectRow(
                       'SELECT tn1.`areaId` AS "startAreaId", tn1.`areaX` AS "startPosX", tn1.`areaY` AS "startPosY", tn1.`name_loc0` AS `start_loc0`, tn1.name_loc?d AS start_loc?d,
                               tn2.`areaId` AS "endAreaId",   tn2.`areaX` AS "endPosX",   tn2.`areaY` AS "endPosY",   tn2.`name_loc0` AS `end_loc0`,   tn2.name_loc?d AS end_loc?d
                        FROM   ?_taxipath tp
                        JOIN   ?_taxinodes tn1 ON tp.`startNodeId` = tn1.`id`
                        JOIN   ?_taxinodes tn2 ON tp.`endNodeId` = tn2.`id`
                        WHERE  tp.`id` = ?d',
                        Lang::getLocale()->value, Lang::getLocale()->value, Lang::getLocale()->value, Lang::getLocale()->value, $effMV
                    );
                    if ($_)
                    {
                        $start = Util::localizedString($_, 'start');
                        if ($_['startAreaId'])
                            $start = sprintf('<a href="?maps=%d:%03d%03d">%s</a>', $_['startAreaId'], $_['startPosX'] * 10, $_['startPosY'] * 10, $start);
                        $end = Util::localizedString($_, 'end');
                        if ($_['endAreaId'])
                            $end = sprintf('<a href="?maps=%d:%03d%03d">%s</a>', $_['endAreaId'], $_['endPosX'] * 10, $_['endPosY'] * 10, $end);

                        $_nameMV = $this->fmtStaffTip('<span class="breadcrumb-arrow">'.$start.'</span>'.$end, 'MiscValue: '.$effMV);
                    }
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
                        $_nameAura = '<a href="?spells&filter=cr=29;crs='.$effAura.';crv=0">'.$this->fmtStaffTip($_, 'AuraId: '.$effAura).'</a>';
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

                            if ($effMV == POWER_RAGE || $effMV == POWER_RUNIC_POWER)
                                array_walk($_footer['value'], fn(&$x) => $x /= 10);
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
                        case SPELL_AURA_COMPREHEND_LANGUAGE:
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
                        case SPELL_AURA_MOD_SKILL:                            // temp
                        case SPELL_AURA_MOD_SKILL_TALENT:                     // perm
                            $valueFmt = '%+d';
                            if ($a = SkillList::makeLink($effMV))
                                $_nameMV = $a;
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
                            if ($effMV && $a = CreatureList::makeLink($effMV))
                                $_nameMV = $a;
                            else
                                $_nameMV = Util::ucFirst(Lang::game('npc')).' #'.$effMV;
                            break;
                        case SPELL_AURA_FORCE_REACTION:
                            $_footer['value'][1] = $this->fmtStaffTip(Lang::game('rep', $_footer['value'][1]), $_footer['value'][1]);
                            $_footer['value'][0] = null;    // disable range here as the string replacement will fail the comparison at the end
                            // DO NOT BREAK !
                        case SPELL_AURA_MOD_FACTION_REPUTATION_GAIN:
                            if ($effAura == SPELL_AURA_MOD_FACTION_REPUTATION_GAIN)
                                $valueFmt = '%s%%';
                            if ($a = FactionList::makeLink($effMV))
                                $_nameMV = $a;
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
                            $what = match ($effMV)
                            {
                                2 => Lang::spell('combatRating', 2),    // Dodged
                                3 => Lang::spell('combatRating', 4),    // Blocked
                                4 => Lang::spell('combatRating', 3),    // Parried
                                default => ''                           // Evaded(0) Missed(1) Glanced(5) Crited'ed..ed(6) Crushed(7) Regular(8)
                            };

                            if ($what)
                                $_nameMV = $this->fmtStaffTip($what, 'MiscValue: '.$effMV);
                            else
                                trigger_error('unused case #'.$effMV.' found for aura #'.$effAura);
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
                        case SPELL_AURA_MOD_INCREASE_HEALTH_PERCENT:
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
                        case SPELL_AURA_MOD_MELEE_RANGED_HASTE:
                        case SPELL_AURA_MOD_CASTING_SPEED_NOT_STACK:
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
                    $buffer .= Lang::spell('costPerLevel', [sprintf($valueFmt, $effRPPL)]);
                if ($effPPCP != 0)
                    $buffer .= Lang::spell('pointsPerCP', [sprintf($valueFmt, $effPPCP)]);
                if (isset($_footer['value'][2]))
                    $buffer .= $_footer['value'][2];

                if (in_array($effId, SpellList::EFFECTS_SCALING_DAMAGE))
                {
                    if ($scaling[2])
                        $buffer .= Lang::spell('apMod', [$scaling[2]]);
                    if ($scaling[0])
                        $buffer .= Lang::spell('spMod', [$scaling[0]]);
                }
                if (in_array($effAura, SpellList::AURAS_SCALING_DAMAGE))
                {
                    if ($scaling[3])
                        $buffer .= Lang::spell('apMod', [$scaling[3]]);
                    if ($scaling[1])
                        $buffer .= Lang::spell('spMod', [$scaling[1]]);
                }

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
                'footer'      => $_footer,
                'markup'      => $_markup,
                'modifies'    => []                         // may be set later
            );
        }

        $this->effects = $effects;
    }

    private function createAttributesList() : void
    {
        $list = [];
        for ($i = 0; $i < 8; $i++)
        {
            $attributes = $this->subject->getField('attributes'.$i);
            for ($j = 1; $j <= (1 << 31); $j <<= 1)
            {
                if (!($attributes & $j))
                    continue;

                $listItem = Lang::spell('attributes'.$i, $j);
                if (!$listItem && User::isInGroup(U_GROUP_STAFF))
                    $listItem = '<span class="q0">Unknown SpellAttribute'.$i.'</span>';
                else if (!$listItem)
                    continue;

                if ($crId = (SpellListFilter::$attributesFilter[$i][$j] ?? 0))
                    $listItem = sprintf('<a href="?spells&filter=cr=%2$d;crs=%3$d;crv=0">%1$s</a>', $listItem, abs($crId), $crId > 0 ? 1 : 2);

                $list[] = $this->fmtStaffTip($listItem, 'Attributes'.$i.': '.Util::asHex($j));
            }
        }

        $this->attributes = $list;
    }

    private function createNumRange(int $bp, int $ds, int $mult = 1) : string
    {
        return Util::createNumRange($bp + 1, ($bp + $ds) * $mult, '-');
    }

    private function generatePath()
    {
        $cat = $this->subject->getField('typeCat');
        $cf  = $this->subject->getField('cuFlags');
        $sl  = $this->subject->getField('skillLines');

        $this->breadcrumb[] = $cat;

        // reconstruct path
        switch ($cat)
        {
            case  -2:
            case   7:
            case -13:
                if ($cl = $this->subject->getField('reqClassMask'))
                    $this->breadcrumb[] = ChrClass::fromMask($cl)[0];
                else if ($sf = $this->subject->getField('spellFamilyId'))
                    foreach (ChrClass::cases() as $cl)
                        if ($cl->spellFamily() == $sf)
                        {
                            $this->breadcrumb[] = $cl->value;
                            break;
                        }

                if ($cat == -13)
                    $this->breadcrumb[] = ($cf & (SPELL_CU_GLYPH_MAJOR | SPELL_CU_GLYPH_MINOR)) >> 6;
                else if ($sl)
                    $this->breadcrumb[] = $sl[0];

                break;
            case   9:
            case  -3:
            case  11:
                if ($sl)
                    $this->breadcrumb[] = $sl[0];

                if ($cat == 11)
                    if ($_ = $this->subject->getField('reqSpellId'))
                        $this->breadcrumb[] = $_;

                break;
            case -11:
                foreach (SpellList::$skillLines as $line => $skills)
                    if (in_array($sl[0] ?? [], $skills))
                        $this->breadcrumb[] = $line;
                break;
            case  -7:                                       // only spells unique in skillLineAbility will always point to the right skillLine :/
                if ($cf & SPELL_CU_PET_TALENT_TYPE0)
                    $this->breadcrumb[] = 411;              // Ferocity
                else if ($cf & SPELL_CU_PET_TALENT_TYPE1)
                    $this->breadcrumb[] = 409;              // Tenacity
                else if ($cf & SPELL_CU_PET_TALENT_TYPE2)
                    $this->breadcrumb[] = 410;              // Cunning
                break;
            case -5:
                if ($this->subject->getField('effect2AuraId') == SPELL_AURA_MOD_INCREASE_MOUNTED_FLIGHT_SPEED ||
                    $this->subject->getField('effect3AuraId') == SPELL_AURA_MOD_INCREASE_MOUNTED_FLIGHT_SPEED)
                    $this->breadcrumb[] = 2;                // flying (also contains SPELL_AURA_MOD_INCREASE_MOUNTED_SPEED, so checked first)
                else if ($this->subject->getField('effect2AuraId') == SPELL_AURA_MOD_INCREASE_MOUNTED_SPEED ||
                         $this->subject->getField('effect3AuraId') == SPELL_AURA_MOD_INCREASE_MOUNTED_SPEED)
                    $this->breadcrumb[] = 1;                // ground
                else
                    $this->breadcrumb[] = 3;                // misc
        }
    }

    private function createInfobox() : void
    {
        $infobox       = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));
        $typeCat       = $this->subject->getField('typeCat');
        $hasCompletion = in_array($typeCat, [-5, -6]) && !($this->subject->getField('cuFlags') & CUSTOM_EXCLUDE_FOR_LISTVIEW);

        // level
        if (!in_array($typeCat, [-5, -6]))                  // not mount or vanity pet
        {
            if ($_ = $this->subject->getField('talentLevel'))
                $infobox[] = (in_array($typeCat, [-2, 7, -13]) ? Lang::game('reqLevel', [$_]) : Lang::game('level').Lang::main('colon').$_);
            else if ($_ = $this->subject->getField('spellLevel'))
                $infobox[] = (in_array($typeCat, [-2, 7, -13]) ? Lang::game('reqLevel', [$_]) : Lang::game('level').Lang::main('colon').$_);
        }

        // races
        $jsg = [];
        if ($_ = Lang::getRaceString($this->subject->getField('reqRaceMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_RACE, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('race') : Lang::game('races');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // classes
        $jsg = [];
        if ($_ = Lang::getClassString($this->subject->getField('reqClassMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_CLASS, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$_;
        }

        // spell focus
        if ($_ = $this->subject->getField('spellFocusObject'))
        {
            if ($sfObj = DB::Aowow()->selectRow('SELECT * FROM ?_spellfocusobject WHERE `id` = ?d', $_))
            {
                $n = Util::localizedString($sfObj, 'name');
                if (!is_null(GameObjectListFilter::getCriteriaIndex(50, $_)))
                    $n = '[url=?objects&filter=cr=50;crs='.$_.';crv=0]'.$n.'[/url]';
                else if ($objId = DB::Aowow()->selectCell('SELECT `id` FROM ?_objects WHERE `spellFocusId` = ?d', $_))
                    $n = '[url=?object='.$objId.']'.$n.'[/url]';

                $infobox[] =  Lang::game('requires2').' '.$n;
            }
        }

        // primary & secondary trades
        if (in_array($typeCat, [9, 11]))
        {
            // skill
            if ($_ = $this->subject->getField('skillLines'))
            {
                $this->extendGlobalIds(Type::SKILL, $_[0]);

                $bar = Lang::game('requires', ['&nbsp;[skill='.$_[0].']']);
                if ($_ = $this->subject->getField('learnedAt'))
                    $bar .= ' ('.$_.')';

                $infobox[] = $bar;
            }

            // specialization
            if ($_ = $this->subject->getField('reqSpellId'))
            {
                $this->extendGlobalIds(Type::SPELL, $_);
                $infobox[] = Lang::game('requires2').' [spell='.$_.']';
            }

            // difficulty
            if ($_ = $this->subject->getColorsForCurrent())
                $infobox[] = Lang::formatSkillBreakpoints($_);
        }

        // accquisition..   10: starter spell; 7: discovery
        if ($this->subject->getRawSource(SRC_STARTER))
            $infobox[] = Lang::spell('starter');
        else if ($this->subject->getRawSource(SRC_DISCOVERY))
            $infobox[] = Lang::spell('discovered');

        // training cost
        if ($cost = $this->subject->getField('trainingCost'))
            $infobox[] = Lang::spell('trainingCost').'[money='.$cost.']';

        // id
        $infobox[] = Lang::spell('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // profiler relateed (note that this is part of the cache. I don't think this is important enough to calc for every view)
        if (Cfg::get('PROFILER_ENABLE') && $hasCompletion)
        {
            $x = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_completion_spells WHERE `spellId` = ?d', $this->typeId);
            $y = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_profiles WHERE `custom` = 0 AND `stub` = 0');
            $infobox[] = Lang::profiler('attainedBy', [round(($x ?: 0) * 100 / ($y ?: 1))]);

            // completion row added by InfoboxMarkup
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        // used in mode
        foreach ($this->difficulties as $n => $id)
            if ($id == $this->typeId)
                $infobox[] = Lang::game('mode').Lang::game('modes', $this->mapType, $n);

        // Creature Type from Aura: Shapeshift
        foreach ($this->modelInfo as $mI)
        {
            if (!isset($mI['creatureType']))
                continue;

            if ($mI['creatureType'] > 0)
                $infobox[] = Lang::game('type').Lang::game('ct', $mI['creatureType']);

            break;
        }

        // spell script
        if (User::isInGroup(U_GROUP_STAFF))
            if ($_ = DB::World()->selectCell('SELECT `ScriptName` FROM spell_script_names WHERE ABS(`spell_id`) = ?d', $this->firstRank))
                $infobox[] = 'Script'.Lang::main('colon').$_;


        $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', $hasCompletion);

        // append glyph symbol if available
        $glyphId = 0;
        for ($i = 1; $i < 4; $i++)
            if ($this->subject->getField('effect'.$i.'Id') == SPELL_EFFECT_APPLY_GLYPH)
                $glyphId = $this->subject->getField('effect'.$i.'MiscValue');

        if ($_ = DB::Aowow()->selectCell('SELECT ic.`name` FROM ?_glyphproperties gp JOIN ?_icons ic ON gp.`iconId` = ic.`id` WHERE gp.`spellId` = ?d { OR gp.`id` = ?d }', $this->typeId, $glyphId ?: DBSIMPLE_SKIP))
            if (file_exists('static/images/wow/Interface/Spellbook/'.$_.'.png'))
                $this->infobox->append('[img src='.Cfg::get('STATIC_URL').'/images/wow/Interface/Spellbook/'.$_.'.png border=0 float=center margin=15]');
    }
}

?>
