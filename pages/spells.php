<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 1: Spell    g_initPath()
//  tabId 0: Database g_initHeader()
class SpellsPage extends GenericPage
{
    use TrListPage;

    protected $classPanel    = false;
    protected $glyphPanel    = false;

    protected $type          = Type::SPELL;
    protected $tpl           = 'spells';
    protected $path          = [0, 1];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/filters.js']];

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    protected $validCats     = array(
        -2  => array(                                       // Talents: Class => Skill
            1  => [ 26, 256, 257],
            2  => [594, 267, 184],
            3  => [ 50, 163,  51],
            4  => [253,  38,  39],
            5  => [613,  56,  78],
            6  => [770, 771, 772],
            7  => [375, 373, 374],
            8  => [237,   8,   6],
            9  => [355, 354, 593],
            11 => [574, 134, 573]
        ),
        -3  => [782, 270, 653, 210, 655, 211, 213, 209, 780, 787, 214, 212, 781, 763, 215, 654, 775, 764, 217, 767, 786, 236, 768, 783, 203, 788, 765, 218, 251, 766, 785, 656, 208, 784, 761, 189, 188, 205, 204],  // Pet Spells => Skill
        -4  => true,                                        // Racial Traits
        -5  => [1, 2, 3],                                   // Mounts [Ground, Flying, Misc]
        -6  => true,                                        // Companions
        -7  => [409, 410, 411],                             // PetTalents => TalentTabId
        -8  => true,                                        // NPC Abilities
        -9  => true,                                        // GM Abilities
        -11 => [6, 8, 10],                                  // Proficiencies [Weapon, Armor, Language]
        -13 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 11],             // Glyphs => Class (GlyphType via filter)
        0   => true,                                        // Uncategorized
        7   => array(                                       // Abilities: Class => Skill
            1  => [ 26, 256, 257],
            2  => [594, 267, 184],
            3  => [ 50, 163,  51],
            4  => [253,  38,  39],
            5  => [613,  56,  78],
            6  => [770, 771, 772, 776],
            7  => [375, 373, 374],
            8  => [237,   8,   6],
            9  => [355, 354, 593],
            11 => [574, 134, 573]
        ),
        9   => SKILLS_TRADE_SECONDARY,                      // Secondary Skills
        11  => array(                                       // Professions: Skill => Spell
            SKILL_ALCHEMY        => true,
            SKILL_BLACKSMITHING  => [9788, 9787, 17041, 17040, 17039],
            SKILL_ENCHANTING     => true,
            SKILL_ENGINEERING    => [20219, 20222],
            SKILL_HERBALISM      => true,
            SKILL_INSCRIPTION    => true,
            SKILL_JEWELCRAFTING  => true,
            SKILL_LEATHERWORKING => [10656, 10658, 10660],
            SKILL_MINING         => true,
            SKILL_SKINNING       => true,
            SKILL_TAILORING      => [26798, 26801, 26797],
        )
    );

    private   $shortFilter   = array(
        SKILL_FIRST_AID      => [ 6,  7],
        SKILL_BLACKSMITHING  => [ 2,  4],
        SKILL_LEATHERWORKING => [ 8,  1],
        SKILL_ALCHEMY        => [ 1,  6],
        SKILL_COOKING        => [ 3,  5],
        SKILL_MINING         => [ 9,  0],
        SKILL_TAILORING      => [10,  2],
        SKILL_ENGINEERING    => [ 5,  3],
        SKILL_ENCHANTING     => [ 4,  8],
        SKILL_FISHING        => [ 0,  9],
        SKILL_JEWELCRAFTING  => [ 7, 10],
        SKILL_INSCRIPTION    => [15,  0],
    );


    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->filterObj = new SpellListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);

        $this->name   = Util::ucFirst(Lang::game('spells'));
        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $conditions   = [];
        $visibleCols  = [];
        $hiddenCols   = [];
        $extraCols    = [];
        $tabData      = ['data' => []];

        // the next lengthy ~250 lines determine $conditions and lvParams
        if ($this->category)
        {
            switch ($this->category[0])
            {
                case -2:                                    // Character Talents
                    $this->classPanel = true;

                    array_push($visibleCols, 'singleclass', 'level', 'schools', 'tier');

                    $conditions[] = ['s.typeCat', -2];

                    // i will NOT redefine those class2skillId ... reusing
                    if (isset($this->category[2]))
                        $conditions[] = ['s.skillLine1', $this->category[2]];
                    else if (isset($this->category[1]))
                        $conditions[] = ['s.skillLine1', $this->validCats[-2][$this->category[1]]];

                    break;
                case -3:                                    // Pet Spells
                    array_push($visibleCols, 'level', 'schools');

                    $conditions[] = ['s.typeCat', -3];

                    if (isset($this->category[1]))
                    {
                        $xCond = null;
                        for ($i = -2; $i < 0; $i++)
                        {
                            foreach (Game::$skillLineMask[$i] as $idx => $pair)
                            {
                                if ($pair[1] == $this->category[1])
                                {
                                    $xCond = ['AND', ['s.skillLine1', $i], ['s.skillLine2OrMask', 1 << $idx, '&']];
                                    break;
                                }
                            }
                        }

                        $conditions[] = [
                            'OR',
                            $xCond,
                            ['s.skillLine1', $this->category[1]],
                            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->category[1]]]
                        ];
                    }
                    else
                    {
                        $conditions[] = [
                            'OR',
                            ['s.skillLine1', [-1, -2]],
                            ['s.skillLine1', $this->validCats[-3]],
                            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->validCats[-3]]]
                        ];
                    }

                    break;
                case -4:                                    // Racials
                    array_push($visibleCols, 'classes');

                    $conditions[] = ['s.typeCat', -4];

                    break;
                case -8:                                    // NPC-Spells
                case -9:                                    // GM Spells
                    array_push($visibleCols, 'level');
                case -5:                                    // Mounts
                    array_push($extraCols, "\$Listview.funcBox.createSimpleCol('speed', 'speed', '90px', 'speed')");

                    if (isset($this->category[1]))
                    {
                        switch ($this->category[1])
                        {
                            case 1:
                                $conditions[] = ['OR',
                                    ['AND', ['effect2AuraId', 32], ['effect3AuraId', 207, '!']],
                                    ['AND', ['effect3AuraId', 32], ['effect2AuraId', 207, '!']]
                                ];
                                break;
                            case 2:
                                $conditions[] = ['OR', ['effect2AuraId', 207], ['effect3AuraId', 207]];
                                break;
                            case 3:
                                $conditions[] = ['AND', ['effect2AuraId', 32, '!'], ['effect2AuraId', 207, '!'], ['effect3AuraId', 32, '!'],['effect3AuraId', 207, '!']];
                                break;
                        }
                    }
                case -6:                                    // Companions
                    $conditions[] = ['s.typeCat', $this->category[0]];

                    break;
                case -7:                                    // Pet Talents
                    array_push($visibleCols, 'level', 'tier');

                    $conditions[] = ['s.typeCat', -7];

                    if (isset($this->category[1]))
                    {
                        switch ($this->category[1])         // Spells can be used by multiple specs
                        {
                            case 409:                       // Tenacity
                                $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE1, '&'];
                                $url          = '?pets=1';
                                break;
                            case 410:                       // Cunning
                                $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE2, '&'];
                                $url          = '?pets=2';
                               break;
                            case 411:                       // Ferocity
                                $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE0, '&'];
                                $url          = '?pets=0';
                                break;
                        }

                        $tabData['note'] = '$$WH.sprintf(LANG.lvnote_pettalents, "'.$url.'")';
                    }

                    $tabData['_petTalents'] = 1;            // not conviced, this is correct, but .. it works

                    break;
                case -11:                                   // Proficiencies ... the subIds are actually SkillLineCategories
                    if (!isset($this->category[1]) || $this->category[1] != 10)
                        array_push($visibleCols, 'classes');

                        $conditions[] = ['s.typeCat', -11];

                    if (isset($this->category[1]))
                    {
                        if ($this->category[1] == 6)        // todo (med): we know Weapon(6) includes spell Shoot(3018), that has a mask; but really, ANY proficiency or petSkill should be in that mask so there is no need to differenciate
                            $conditions[] = ['OR', ['s.skillLine1', SpellList::$skillLines[$this->category[1]]], ['s.skillLine1', -3]];
                        else
                            $conditions[] = ['s.skillLine1', SpellList::$skillLines[$this->category[1]]];
                    }

                    break;
                case -13:                                   // Glyphs
                    $this->classPanel = true;
                    $this->glyphPanel = true;

                    array_push($visibleCols, 'singleclass', 'glyphtype');

                    $conditions[] = ['s.typeCat', -13];

                    if (isset($this->category[1]))
                        $conditions[] = ['s.reqClassMask', 1 << ($this->category[1] - 1), '&'];

                    break;
                case 7:                                     // Abilities
                    $this->classPanel = true;

                    array_push($visibleCols, 'level', 'singleclass', 'schools');

                    $conditions[] = ['s.typeCat', [7, -2]];
                    $conditions[] = [['s.cuFlags', (SPELL_CU_TRIGGERED | SPELL_CU_TALENT), '&'], 0];

                    // Runeforging listed multiple times, exclude from explicit skill-listing
                    // if (isset($this->category[1]) && $this->category[1] == 6 && isset($this->category[2]) && $this->category[2] != 776)
                        // $conditions[] = [['s.attributes0', 0x80, '&'], 0];
                    // else
                        // $conditions[] = [
                            // [['s.attributes0', 0x80, '&'], 0],      // ~SPELL_ATTR0_HIDDEN_CLIENTSIDE
                            // ['s.attributes0', 0x20, '&'],           // SPELL_ATTR0_TRADESPELL (DK: Runeforging)
                            // 'OR'
                        // ];

                    if (isset($this->category[2]))
                    {
                        $conditions[] = [
                            'OR',
                            ['s.skillLine1', $this->category[2]],
                            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->category[2]]]
                        ];

                    }
                    else if (isset($this->category[1]))
                    {
                        $conditions[] = [
                            'OR',
                            ['s.skillLine1', $this->validCats[7][$this->category[1]]],
                            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->validCats[7][$this->category[1]]]]
                        ];

                    }

                    break;
                case 9:                                     // Secondary Skills
                    array_push($visibleCols, 'source');

                    $conditions[] = ['s.typeCat', 9];

                    if (isset($this->category[1]))
                    {
                        $conditions[] = [
                            'OR',
                            ['s.skillLine1', $this->category[1]],
                            ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $this->category[1]]]
                        ];

                        if (!empty($this->shortFilter[$this->category[1]]))
                        {
                            $sf  = $this->shortFilter[$this->category[1]];
                            $txt = '';
                            if ($sf[0] && $sf[1])
                                $txt = sprintf(Lang::spell('relItems', 'crafted'), $sf[0]) . Lang::spell('relItems', 'link') . sprintf(Lang::spell('relItems', 'recipes'), $sf[1]);
                            else if ($sf[0])
                                $txt = sprintf(Lang::spell('relItems', 'crafted'), $sf[0]);
                            else if ($sf[1])
                                $txt = sprintf(Lang::spell('relItems', 'recipes'), $sf[1]);

                            $note = Lang::spell('cat', $this->category[0], $this->category[1]);
                            if (is_array($note))
                                $note = $note[0];

                            $tabData['note'] = sprintf(Lang::spell('relItems', 'base'), $txt, $note);
                            $tabData['sort'] = ['skill', 'name'];
                        }
                    }

                    break;
                case 11:                                    // Professions
                    array_push($visibleCols, 'source');

                    $conditions[] = ['s.typeCat', 11];

                    if (isset($this->category[2]))
                    {
                        if ($this->category[2] == 9787)     // general weaponsmithing
                            $conditions[] = ['s.reqSpellId', [9787, 17039, 17040, 17041]];
                        else
                            $conditions[] = ['s.reqSpellId', $this->category[2]];
                    }
                    else if (isset($this->category[1]))
                        $conditions[] = ['s.skillLine1', $this->category[1]];

                    if (isset($this->category[1]))
                    {
                        $conditions[] = ['s.skillLine1', $this->category[1]];

                        if (!empty($this->shortFilter[$this->category[1]]))
                        {
                            $sf  = $this->shortFilter[$this->category[1]];
                            $txt = '';
                            if ($sf[0] && $sf[1])
                                $txt = sprintf(Lang::spell('relItems', 'crafted'), $sf[0]) . Lang::spell('relItems', 'link') . sprintf(Lang::spell('relItems', 'recipes'), $sf[1]);
                            else if ($sf[0])
                                $txt = sprintf(Lang::spell('relItems', 'crafted'), $sf[0]);
                            else if ($sf[1])
                                $txt = sprintf(Lang::spell('relItems', 'recipes'), $sf[1]);

                            $note = Lang::spell('cat', $this->category[0], $this->category[1]);
                            if (is_array($note))
                                $note = $note[0];

                            $tabData['note'] = sprintf(Lang::spell('relItems', 'base'), $txt, $note);
                            $tabData['sort'] = ['skill', 'name'];
                        }
                    }

                    break;
                case 0:                                     // misc. Spells & triggered player abilities
                    array_push($visibleCols, 'level');

                    $conditions[] = [
                        'OR',
                        ['s.typeCat', 0],
                        ['AND', ['s.cuFlags', SPELL_CU_TRIGGERED, '&'], ['s.typeCat', [7, -2]]]
                    ];

                    break;
            }
        }

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $this->filterObj->evalCriteria();

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $spells = new SpellList($conditions, ['calcTotal' => true]);

        $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

        $lvData = $spells->getListviewData();

        // add speed-data for mounts
        if ($this->category && $this->category[0] == -5)
        {
            foreach ($spells->iterate() as $spellId => $__)
            {
                $lvData[$spellId]['speed'] = 0;

                if (in_array($spells->getField('effect2AuraId'), [32, 207, 58]))
                    $lvData[$spellId]['speed'] = $spells->getField('effect2BasePoints') + 1;
                if (in_array($spells->getField('effect3AuraId'), [32, 207, 58]))
                    $lvData[$spellId]['speed'] = max($lvData[$spellId]['speed'], $spells->getField('effect3BasePoints') + 1);

                if (!$lvData[$spellId]['speed'] && ($spells->getField('effect2AuraId') == 4 || $spells->getField('effect3AuraId') == 4))
                    $lvData[$spellId]['speed'] = '?';
                else
                    $lvData[$spellId]['speed'] = '+'.$lvData[$spellId]['speed'].'%';
            }
        }

        $tabData['data'] = array_values($lvData);

        if ($this->filterObj->fiExtraCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';
        else if ($extraCols)
            $tabData['extraCols'] = $extraCols;

        // add source to cols if explicitly searching for it
        if ($this->filterObj->getSetCriteria(9) && !in_array('source', $visibleCols))
            $visibleCols[] = 'source';

        // create note if search limit was exceeded; overwriting 'note' is intentional
        if ($spells->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_spellsfound', $spells->getMatches(), Cfg::get('SQL_LIMIT_DEFAULT'));
            $tabData['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $tabData['_errors'] = 1;


        $mask = $spells->hasSetFields('skillLines', 'trainingCost', 'reqClassMask', null, 'reagent1', 'reagent2', 'reagent3', 'reagent4', 'reagent5', 'reagent6', 'reagent7', 'reagent8');
        if (!($mask & 0x1) && $this->category && !in_array($this->category[0], [9, 11]))
            $hiddenCols[] = 'skill';
        if ($mask & 0x2)
            $visibleCols[] = 'trainingcost';
        if (($mask & 0x4) && !in_array('singleclass', $visibleCols))
            $visibleCols[] = 'classes';
        if ($mask & 0xFF0)
            $visibleCols[] = 'reagents';


        if ($visibleCols)
            $tabData['visibleCols'] = array_unique($visibleCols);

        if ($hiddenCols)
            $tabData['hiddenCols'] = array_unique($hiddenCols);

        $this->lvTabs[] = [SpellList::$brickFile, $tabData];
    }

    protected function postCache()
    {
        // sort for dropdown-menus
        Lang::sort('game', 'ra');
        Lang::sort('game', 'cl');
        Lang::sort('game', 'sc');
        Lang::sort('game', 'me');
    }

    protected function generateTitle()
    {
        $foo = [];
        $c = $this->category;                               // shothand
        if (isset($c[2]) && $c[0] == 11)
            array_unshift($foo, Lang::spell('cat', $c[0], $c[1], $c[2]));
        else if (isset($c[1]))
        {
            $_ = in_array($c[0], [-2, -13, 7]) ? Lang::game('cl') : Lang::spell('cat', $c[0]);
            array_unshift($foo, is_array($_[$c[1]]) ? $_[$c[1]][0] : $_[$c[1]]);
        }

        if (isset($c[0]) && count($foo) < 2)
        {
            $_ = Lang::spell('cat', $c[0]);
            array_unshift($foo, is_array($_) ? $_[0] : $_);
        }

        if (count($foo) < 2)
            array_unshift($foo, $this->name);

        foreach ($foo as $bar)
            array_unshift($this->title, $bar);
    }

    protected function generatePath()
    {
        foreach ($this->category as $c)
            $this->path[] = $c;

        $form = $this->filterObj->values;
        if (count($this->path) == 4 && $this->category[0] == -13 && count($form['gl']) == 1)
            $this->path[] = $form['gl'][0];
    }
}

?>
