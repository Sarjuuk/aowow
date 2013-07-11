<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.filter.php';

$cats       = Util::extractURLParams($pageParam);
$path       = [0, 1];
$title      = [Lang::$game['spells']];                      // display max 2 cats, remove this base if nesecary
$filter     = ['classPanel' => false, 'glyphPanel' => false];
$filterHash = !empty($_GET['filter']) ? '#'.sha1(serialize($_GET['filter'])) : null;
$cacheKey   = implode('_', [CACHETYPE_PAGE, TYPE_SPELL, -1, implode('.', $cats).$filterHash, User::$localeId]);
$validCats  = array(
    -2  => array(                                           // Talents: Class => Skill
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
    -4  => true,                                            // Racial Traits
    -5  => true,                                            // Mounts
    -6  => true,                                            // Companions
    -7  => [409, 410, 411],                                 // PetTalents => TalentTabId
    -8  => true,                                            // NPC Abilities
    -9  => true,                                            // GM Abilities
    -11 => [6, 8, 10],                                      // Proficiencies [Weapon, Armor, Language]
    -13 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 11],                 // Glyphs => Class (GlyphType via filter)
    0   => true,                                            // Uncategorized
    7   => array(                                           // Abilities: Class => Skill
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
    9   => [129, 185, 356, 762],                            // Secondary Skills
    11  => array(                                           // Professions: Skill => Spell
        171 => true,
        164 => [9788, 9787, 17041, 17040, 17039],
        333 => true,
        202 => [20219, 20222],
        182 => true,
        773 => true,
        755 => true,
        165 => [10656, 10658, 10660],
        186 => true,
        393 => true,
        197 => [26798, 26801, 26797],
    )
);
$shortFilter = array(
    129 => [ 6,  7],                                        // First Aid
    164 => [ 2,  4],                                        // Blacksmithing
    165 => [ 8,  1],                                        // Leatherworking
    171 => [ 1,  6],                                        // Alchemy
    185 => [ 3,  5],                                        // Cooking
    186 => [ 9,  0],                                        // Mining
    197 => [10,  2],                                        // Tailoring
    202 => [ 5,  3],                                        // Engineering
    333 => [ 4,  8],                                        // Enchanting
    356 => [ 0,  9],                                        // Fishing
    755 => [ 7, 10],                                        // Jewelcrafting
    773 => [15,  0],                                        // Inscription
);

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

$path = array_merge($path, $cats);

if (isset($cats))
{
    if (isset($cats[1]))
        array_pop($title);

    $x = @Lang::$spell['cat'][$cats[0]];
    if (is_array($x))
    {
        if (is_array($x[0]))
            array_unshift($title, $x[0][0]);
        else
            array_unshift($title, $x[0]);
    }
    else if ($x !== null)
        array_unshift($title, $x);
}

if (!$smarty->loadCache($cacheKey, $pageData, $filter))
{
    $conditions  = [];
    $visibleCols = [];
    $hiddenCols  = [];

    switch($cats[0])
    {
        case -2:                                            // Character Talents
            $filter['classPanel'] = true;

            array_push($visibleCols, 'singleclass', 'level', 'schools', 'tier');

            $conditions[] = ['s.typeCat', -2];

            if (isset($cats[1]))
                array_unshift($title, Lang::$game['cl'][$cats[1]]);

            if (isset($cats[1]) && empty($cats[2]))         // i will NOT redefine those class2skillId ... reusing
                $conditions[] = ['s.skillLine1', $validCats[-2][$cats[1]]];
            else if (isset($cats[1]))
                $conditions[] = ['s.skillLine1', $cats[2]];

            break;
        case -3:                                            // Pet Spells
            array_push($visibleCols, 'level', 'schools');

            $conditions[] = ['s.typeCat', -3];

            if (isset($cats[1]))
            {
                $xCond = null;
                for ($i = -2; $i < 0; $i++)
                {
                    foreach (Util::$skillLineMask[$i] as $idx => $pair)
                    {
                        if ($pair[1] == $cats[1])
                        {
                            $xCond = ['AND', ['s.skillLine1', $i], ['s.skillLine2OrMask', 1 << $idx, '&']];
                            break;
                        }
                    }
                }

                $conditions[] = [
                    'OR',
                    $xCond,
                    ['s.skillLine1', $cats[1]],
                    ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $cats[1]]]
                ];

                array_unshift($title, Lang::$spell['cat'][-3][$cats[1]]);
            }
            else
            {
                $conditions[] = [
                    'OR',
                    ['s.skillLine1', [-1, -2]],
                    ['s.skillLine1', $validCats[-3]],
                    ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $validCats[-3]]]
                ];
            }

            break;
        case -4:                                            // Racials
            array_push($visibleCols, 'classes');

            $conditions[] = ['s.typeCat', -4];

            break;
        case -8:                                            // NPC-Spells
        case -9:                                            // GM Spells
            array_push($visibleCols, 'level');
        case -5:                                            // Mounts
        case -6:                                            // Companions
            $conditions[] = ['s.typeCat', $cats[0]];

            break;
        case -7:                                            // Pet Talents
            array_push($visibleCols, 'level', 'tier');

            $conditions[] = ['s.typeCat', -7];

            if (isset($cats[1]))
            {
                array_unshift($title, Lang::$spell['cat'][-7][$cats[1]]);

                switch($cats[1])                            // Spells can be used by multiple specs
                {
                    case 409:                               // Tenacity
                        $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE1, '&'];
                        $url          = '?pets=1';
                        break;
                    case 410:                               // Cunning
                        $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE2, '&'];
                        $url          = '?pets=2';
                       break;
                    case 411:                               // Ferocity
                        $conditions[] = ['s.cuFlags', SPELL_CU_PET_TALENT_TYPE0, '&'];
                        $url          = '?pets=0';
                        break;
                }

                $pageData['params']['note'] = '$sprintf(LANG.lvnote_pettalents, "'.$url.'")';
            }

            $pageData['params']['_petTalents'] = 1;         // not conviced, this is correct, but .. it works

            break;
        case -11:                                           // Proficiencies ... the subIds are actually SkillLineCategories
            if (!isset($cats[1]) || $cats[1] != 10)
                array_push($visibleCols, 'classes');

                $conditions[] = ['s.typeCat', -11];

            if (isset($cats[1]))
            {
                if ($cats[1] == 6)                          // todo (med): we know Weapon(6) includes spell Shoot(3018), that has a mask; but really, ANY proficiency or petSkill should be in that mask so there is no need to differenciate
                    $conditions[] = ['OR', ['s.skillLine1', SpellList::$skillLines[$cats[1]]], ['s.skillLine1', -3]];
                else
                    $conditions[] = ['s.skillLine1', SpellList::$skillLines[$cats[1]]];

                array_unshift($title, Lang::$spell['cat'][-11][$cats[1]]);
            }

            break;
        case -13:                                           // Glyphs
            $filter['classPanel'] = true;
            $filter['glyphPanel'] = true;

            array_push($visibleCols, 'singleclass', 'glyphtype');

            $conditions[] = ['s.typeCat', -13];

            if (isset($cats[1]))
            {
                array_unshift($title, Lang::$game['cl'][$cats[1]]);
                $conditions[] = ['s.reqClassMask', 1 << ($cats[1] - 1), '&'];
            }

            break;
        case 7:                                             // Abilities
            $filter['classPanel'] = true;

            array_push($visibleCols, 'level', 'singleclass', 'schools');

            if (isset($cats[1]))
                array_unshift($title, Lang::$game['cl'][$cats[1]]);

            $conditions[] = ['s.typeCat', [7, -2]];
            $conditions[] = [['s.cuFlags', (SPELL_CU_TRIGGERED | SPELL_CU_TALENT | SPELL_CU_EXCLUDE_CATEGORY_SEARCH), '&'], 0];

            // Runeforging listed multiple times, exclude from explicit skill-listing
            // if (isset($cats[1]) && $cats[1] == 6 && isset($cats[2]) && $cats[2] != 776)
                // $conditions[] = [['s.attributes0', 0x80, '&'], 0];
            // else
                // $conditions[] = [
                    // [['s.attributes0', 0x80, '&'], 0],      // ~SPELL_ATTR0_HIDDEN_CLIENTSIDE
                    // ['s.attributes0', 0x20, '&'],           // SPELL_ATTR0_TRADESPELL (DK: Runeforging)
                    // 'OR'
                // ];

            if (isset($cats[2]))
            {
                $conditions[] = [
                    'OR',
                    ['s.skillLine1', $cats[2]],
                    ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $cats[2]]]
                ];

            }
            else if (isset($cats[1]))
            {
                $conditions[] = [
                    'OR',
                    ['s.skillLine1', $validCats[7][$cats[1]]],
                    ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $validCats[7][$cats[1]]]]
                ];

            }

            break;
        case 9:                                             // Secondary Skills
            array_push($visibleCols, 'source');

            $conditions[] = ['s.typeCat', 9];

            if (isset($cats[1]))
            {
                array_unshift($title, Lang::$spell['cat'][9][$cats[1]]);

                $conditions[] = [
                    'OR',
                    ['s.skillLine1', $cats[1]],
                    ['AND', ['s.skillLine1', 0, '>'], ['s.skillLine2OrMask', $cats[1]]]
                ];

                if ($sf = @$shortFilter[$cats[1]])
                {
                    $txt = '';
                    if ($sf[0] && $sf[1])
                        $txt = sprintf(Lang::$spell['relItems']['crafted'], $sf[0]) . Lang::$spell['relItems']['link'] . sprintf(Lang::$spell['relItems']['recipes'], $sf[1]);
                    else if ($sf[0])
                        $txt = sprintf(Lang::$spell['relItems']['crafted'], $sf[0]);
                    else if ($sf[1])
                        $txt = sprintf(Lang::$spell['relItems']['recipes'], $sf[1]);

                    $note = Lang::$spell['cat'][$cats[0]][$cats[1]];
                    if (is_array($note))
                        $note = $note[0];

                    $pageData['params']['note'] = sprintf(Lang::$spell['relItems']['base'], $txt, $note);
                    $pageData['params']['sort'] = "$['skill', 'name']";
                }
            }

            break;
        case 11:                                            // Professions
            array_push($visibleCols, 'source');

            $conditions[] = ['s.typeCat', 11];

            if (isset($cats[2]))
            {
                array_unshift($title, Lang::$spell['cat'][11][$cats[1]][$cats[2]]);

                if ($cats[2] == 9787)                       // general weaponsmithing
                    $conditions[] = ['s.reqSpellId', [9787, 17039, 17040, 17041]];
                else
                    $conditions[] = ['s.reqSpellId', $cats[2]];
            }
            else if (isset($cats[1]))
            {
                $x = Lang::$spell['cat'][11][$cats[1]];
                if (is_array($x))
                    array_unshift($title, $x[0]);
                else
                    array_unshift($title, $x);
                $conditions[] = ['s.skillLine1', $cats[1]];
            }

            if (isset($cats[1]))
            {
                $conditions[] = ['s.skillLine1', $cats[1]];

                if ($sf = @$shortFilter[$cats[1]])
                {
                    $txt = '';
                    if ($sf[0] && $sf[1])
                        $txt = sprintf(Lang::$spell['relItems']['crafted'], $sf[0]) . Lang::$spell['relItems']['link'] . sprintf(Lang::$spell['relItems']['recipes'], $sf[1]);
                    else if ($sf[0])
                        $txt = sprintf(Lang::$spell['relItems']['crafted'], $sf[0]);
                    else if ($sf[1])
                        $txt = sprintf(Lang::$spell['relItems']['recipes'], $sf[1]);

                    $note = Lang::$spell['cat'][$cats[0]][$cats[1]];
                    if (is_array($note))
                        $note = $note[0];

                    $pageData['params']['note'] = sprintf(Lang::$spell['relItems']['base'], $txt, $note);
                    $pageData['params']['sort'] = "$['skill', 'name']";
                }
            }

            break;
        case 0:                                             // misc. Spells
            array_push($visibleCols, 'level');

            if ($cats[0] !== null)                          // !any Spell (php loose comparison: (null == 0) is true)
            {
                $conditions[] = 'OR';
                $conditions[] = ['s.typeCat', 0];
                $conditions[] = ['s.cuFlags', SPELL_CU_EXCLUDE_CATEGORY_SEARCH, '&'];

                break;
            }
    }

    $spells = new SpellList($conditions, true);

    $pageData['data'] = $spells->getListviewData();

    $spells->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

    // create note if search limit was exceeded; overwriting 'note' is intentional
    if ($spells->getMatches() > $AoWoWconf['sqlLimit'])
    {
        $pageData['params']['note'] = '$'.sprintf(Util::$filterResultString, 'LANG.lvnote_spellsfound', $spells->getMatches(), $AoWoWconf['sqlLimit']);
        $pageData['params']['_truncated'] = 1;
    }

    if ($spells->filterGetError())
        $pageData['params']['_errors'] = '$1';

    $mask = $spells->hasSetFields(['reagent1', 'skillLines', 'trainingCost']);

    if ($mask & 0x1)
        $visibleCols[] = 'reagents';
    if (!($mask & 0x2) && $cats[0] != 9 && $cats[0] != 11)
        $hiddenCols[] = 'skill';
    if (($mask & 0x4) || $spells->getField('trainingCost'))
        $visibleCols[] = 'trainingcost';

    if ($visibleCols)
        $pageData['params']['visibleCols'] = '$'.json_encode($visibleCols);

    if ($hiddenCols)
        $pageData['params']['hiddenCols'] = '$'.json_encode($hiddenCols);

    // recreate form selection
    $filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
    $filter['setCr'] = $spells->filterGetSetCriteria();
    $filter = array_merge($spells->filterGetForm(), $filter);

    $smarty->saveCache($cacheKey, $pageData, $filter);
}

if (isset($filter['gl']) && !is_array($filter['gl']))
{
    while (count($path) < 4)
        $path[] = 0;

    $path[] = $filter['gl'];
}

$page = array(
    'tab'       => 0,                                       // for g_initHeader($tab)
    'subCat'    => $pageParam !== null ? '='.$pageParam : '',
    'title'     => implode(" - ", $title),
    'path'      => "[".implode(", ", $path)."]",
    'reqJS'     => array(
                       array('path' => 'template/js/filters.js', 'conditional' => false),
                   ),
);

// sort for dropdown-menus
asort(Lang::$game['ra']);
asort(Lang::$game['cl']);
asort(Lang::$game['sc']);
asort(Lang::$game['me']);
Lang::$game['race'] = Util::ucFirst(Lang::$game['race']);

$smarty->updatePageVars($page);
$smarty->assign('filter', $filter);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$achievement));
$smarty->assign('lvData', $pageData);
$smarty->display('spells.tpl');

?>
