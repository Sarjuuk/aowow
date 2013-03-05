<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

Util::execTime(true);

/*
    if &json
        => search by compare or profiler
    else if &opensearch
        => suche aus der suchbox oben rechts, bzw Startseite
        array:[
            str,        // search
            str[10],    // found
            [],         // unused
            [],         // unused
            [],         // unused
            [],         // unused
            [],         // unused
            str[10][4]  // type, typeId, param1 (4:quality, 3,6,9,10,17:icon, 5:faction), param2 (3:quality, 6:rank)
        ]
    else
        1:  Listview - template: 'classs',      id: 'classes',       name: LANG.tab_classes,
        2:  Listview - template: 'race',        id: 'races',         name: LANG.tab_races,
        3:  Listview - template: 'title',       id: 'titles',        name: LANG.tab_titles,
        4:  Listview - template: 'holiday',     id: 'holidays',      name: LANG.tab_holidays,
        5:  Listview - template: 'currency',    id: 'currencies',    name: LANG.tab_currencies,
        6:  Listview - template: 'itemset',     id: 'itemsets',      name: LANG.tab_itemsets,
        7:  Listview - template: 'item',        id: 'items',         name: LANG.tab_items,
        8:  Listview - template: 'spell',       id: 'abilities',     name: LANG.tab_abilities,           visibleCols: ['level', 'schools'],
        9:  Listview - template: 'spell',       id: 'talents',       name: LANG.tab_talents,             visibleCols: ['level', 'schools'],         hiddenCols: ['reagents'],
        10: Listview - template: 'spell',       id: 'glyphs',        name: LANG.tab_glyphs,              visibleCols: ['singleclass', 'glyphtype'], hiddenCols: ['reagents', 'skill', 'level'],
        11: Listview - template: 'spell',       id: 'proficiencies', name: LANG.tab_proficiencies,       visibleCols: ['classes'],                  hiddenCols: ['reagents', 'skill'],
        12: Listview - template: 'spell',       id: 'professions',   name: LANG.tab_professions,         visibleCols: ['source'],
        13: Listview - template: 'spell',       id: 'companions',    name: LANG.tab_companions,          visibleCols: ['reagents'],                 hiddenCols: ['level', 'skill'],
        14: Listview - template: 'spell',       id: 'mounts',        name: LANG.tab_mounts,              visibleCols: ['reagents'],                 hiddenCols: ['level', 'skill'],
        15: Listview - template: 'npc',         id: 'npcs',          name: LANG.tab_npcs,
        16: Listview - template: 'quest',       id: 'quests',        name: LANG.tab_quests,
        17: Listview - template: 'achievement', id: 'achievements',  name: LANG.tab_achievements,        visibleCols: ['category'],
        18: Listview - template: 'zone',        id: 'zones',         name: LANG.tab_zones,
        19: Listview - template: 'object',      id: 'objects',       name: LANG.tab_objects,
        20: Listview - template: 'faction',     id: 'factions',      name: LANG.tab_factions,
        21: Listview - template: 'skill',       id: 'skills',        name: LANG.tab_skills,                                                         hiddenCols: ['reagents', 'skill'],
        22: Listview - template: 'pet',         id: 'pets',          name: LANG.tab_pets,
        23: Listview - template: 'spell',       id: 'npc-abilities', name: LANG.tab_npcabilities,        visibleCols: ['level'],                    hiddenCols: ['reagents', 'skill'],
        24: Listview - template: 'spell',       id: 'spells',        name: LANG.tab_uncategorizedspells, visibleCols: ['level'],                    hiddenCols: ['reagents', 'skill'],
        25: Listview - template: 'profile',     id: 'characters',    name: LANG.tab_characters,          visibleCols: ['race','classs','level','talents','gearscore','achievementpoints'],
        26: Guilds..?
*/

$search      = urlDecode(trim($pageParam));
$query       = Util::sqlEscape(str_replace('?', '_', str_replace('*', '%', ($search))));
$type        = @intVal($_GET['type']);
$searchMask  = 0x0;
$found       = [];
$jsGlobals   = [];

if (isset($_GET['json']))
{
    if ($type == TYPE_ITEMSET)
        $searchMask |= SEARCH_TYPE_JSON | 0x60;
    else if ($type == TYPE_ITEM)
        $searchMask |= SEARCH_TYPE_JSON | 0x40;
}
else if (isset($_GET['opensearch']))
    $searchMask |= SEARCH_TYPE_OPEN | SEARCH_MASK_OPEN;
else
    $searchMask |= SEARCH_TYPE_REGULAR | SEARCH_MASK_ALL;

$cacheKey = implode('_', [CACHETYPE_SEARCH, $searchMask, sha1($query), User::$localeId]);

// invalid conditions: not enough characters to search OR no types to search
if (strlen($query) < 3 || !($searchMask & SEARCH_MASK_ALL))
{
    if ($searchMask & SEARCH_TYPE_REGULAR)
    {
        // empty queries go home
        if (!$query)
            header("Location:?");

        // insufficient queries throw an error
        $smarty->assign('lang', array_merge(Lang::$main, Lang::$search));
        $smarty->assign('found', []);
        $smarty->assign('mysql', DB::Aowow()->getStatistics());
        $smarty->assign('search', $query);

        $smarty->display('search.tpl');
        die();
    }
    else if ($searchMask & SEARCH_TYPE_OPEN)
    {
        header("Content-type: text/javascript");
        exit('["'.Util::jsEscape($query).'", []]');
    }
    else /* if ($searchMask & SEARCH_TYPE_JSON) */
    {
        header("Content-type: text/javascript");
        exit ("[\"".Util::jsEscape($query)."\", [\n],[\n]]\n");
    }
}

// 1 Classes:
if ($searchMask & 0x1)
{
    /*  custom data :(
        armor:     build manually - ItemSubClassMask (+Shield +Relics)
        weapon:    build manually - ItemSubClassMask
        roles:     build manually - 1:heal; 2:mleDPS; 4:rngDPS; 8:tank
    */
    $classes = new CharClassList(array(['name_loc'.User::$localeId, $query]));

    if ($data = $classes->getListviewData())
    {
        while ($classes->iterate())
            $data[$classes->Id]['param1'] = '"class_'.strToLower($classes->getField('fileString')).'"';

        $found['class'] = array(
            'type'     => TYPE_CLASS,
            'appendix' => ' (Class)',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );
    }
}

// 2 Races:
if ($searchMask & 0x2)
{
    /*  custom data :(
        faction:    dbc-data is internal -> doesn't work how to link to displayable faction..?
        leader:     29611 for human .. DAFUQ?!
        zone:       starting zone...
    */

    $races = new CharRaceList(array(['name_loc'.User::$localeId, $query]));

    if ($data = $races->getListviewData())
    {
        while ($races->iterate())
            $data[$races->Id]['param1'] = '"race_'.strToLower($races->getField('fileString')).'_male"';

        $found['race'] = array(
            'type'     => TYPE_RACE,
            'appendix' => ' (Race)',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );
    }
}

// 3 Titles:
if ($searchMask & 0x4)
{
    /*  custom data :(
        category:1,     // custom data .. FU!
        expansion:0,    // custom data .. FU FU!
        gender:3,       // again.. luckiely 136; 137 only
        side:2,         // hmm, derive from source..?
        source: {}      // g_sources .. holy cow.. that will cost some nerves
    */

    $sources = array(
        4  => [],                                           // Quest
        12 => [],                                           // Achievement
        13 => []                                            // DB-Text
    );

    $conditions = array(
        'OR',
        ['male_loc'.User::$localeId, $query],
        ['female_loc'.User::$localeId, $query]
    );

    $titles = new TitleList($conditions);
    $data   = $titles->getListviewData();

    if (!empty($data))
    {
        $found['title'] = array(
            'type'     => TYPE_TITLE,
            'appendix' => ' (Title)',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );
    }
}

// 4 World Events:
// if ($searchMask & 0x8)
{
    // /*  custom data :(
    // */

    // $wEvents = new WorldEventList(array(['name_loc'.User::$localeId, $query]));

    // if (!empty($data))
    // {
        // $found[] = array(
            // 'type'     => TYPE_WORLDEVENT,
            // 'appendix' => ' (World Event)',
            // 'data'     => $data
        // );
    // }
}

// 5 Currencies
if ($searchMask & 0x10)
{
    $money = new CurrencyList(array(['name_loc'.User::$localeId, $query]));

    if ($data = $money->getListviewData())
    {
        while ($money->iterate())
            $data[$money->Id]['param1'] = strToLower($money->getField('iconString'));

        $found['currency'] = array(
            'type'     => TYPE_CURRENCY,
            'appendix' => ' (Currency)',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );
    }
}

// 6 Itemsets
if ($searchMask & 0x20)
{
    $sets = new ItemsetList(array(['name_loc'.User::$localeId, $query]));
    $sets->addGlobalsToJscript($jsGlobals);

    if ($data = $sets->getListviewData())
    {
        while ($sets->iterate())
            $data[$sets->Id]['param1'] = $sets->getField('quality');

        $found['itemset'] = array(
            'type'     => TYPE_ITEMSET,
            'appendix' => ' (Item Set)',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs'],
            'pieces'   => $sets->pieces
        );
    }
}

// 7 Items
if ($searchMask & 0x40)
{
    if (($searchMask & SEARCH_TYPE_JSON) && $type == TYPE_ITEMSET && $found['itemset']['pieces'])
        $conditions = [['i.class', [2, 4]], ['i.entry', $foundItems]];
    else
        $conditions = [[User::$localeId ? 'name_loc'.User::$localeId : 'name', $query], 0];
    $items = new ItemList($conditions);
    $items->addGlobalsToJscript($jsGlobals);

    if ($data = $items->getListviewData($searchMask & SEARCH_TYPE_JSON ? ITEMINFO_SUBITEMS : 0))
    {
        while ($items->iterate())
        {
            $data[$items->Id]['param1'] = '"'.$items->getField('icon').'"';
            $data[$items->Id]['param2'] = $items->getField('Quality');
        }

        $found['item'] = array(
            'type'     => TYPE_ITEM,
            'appendix' => ' (Item)',
            'params'   => ['tabs' => '$myTabs'],
            'data'     => $data,
        );
    }
}

// 8 Abilities
// if ($searchMask & 0x80)

// 9 Talents
// if ($searchMask & 0x100)

// 10 Glyphs
// if ($searchMask & 0x200)

// 11 Proficiencies
// if ($searchMask & 0x400)

// 12 Professions
// if ($searchMask & 0x800)

// 13 Companions
// if ($searchMask & 0x1000)

// 14 Mounts
// if ($searchMask & 0x2000)

// 15 NPCs
// if ($searchMask & 0x4000)

// 16 Quests
// if ($searchMask & 0x8000)

// 17 Achievements
if ($searchMask & 0x10000)
{
    $acvs = new AchievementList(array(['name_loc'.User::$localeId, $query]));

    if ($data = $acvs->getListviewData())
    {
        $acvs->addGlobalsToJScript($jsGlobals);
        $acvs->addRewardsToJScript($jsGlobals);

        $found['achievement'] = array(
            'type'     => TYPE_ACHIEVEMENT,
            'appendix' => ' (Achievement)',
            'data'     => $data,
            'params'   => [
                'tabs'        => '$myTabs',
                'visibleCols' => '$[\'category\']'
            ]
        );
    }
}

// 18 Zones
// if ($searchMask & 0x20000)

// 19 Objects
// if ($searchMask & 0x40000)

// 20 Factions
// if ($searchMask & 0x80000)

// 21 Skills
// if ($searchMask & 0x100000)

// 22 Pets
// if ($searchMask & 0x200000)

// 23 NPCAbilities
// if ($searchMask & 0x400000)

// 24 Spells (Misc)
// if ($searchMask & 0x800000)

// 25 Characters
// if ($searchMask & 0x1000000)

// 26 Guilds
// if ($searchMask & 0x2000000)


if ($searchMask & SEARCH_TYPE_JSON)
{
    header("Content-type: text/javascript");
    echo "/* not yet supported */\n";
    exit ("[\"".Util::jsEscape($query)."\", [\n],[\n]]\n");
}
else if ($searchMask & SEARCH_TYPE_OPEN)
{
    // this one is funny: we want 10 results, ideally equally destributed over each type
    $foundTotal = 0;
    $maxResults = 10;
    $names      = [];
    $info       = [];

    foreach ($found as $tmp)
        $foundTotal += count($tmp['data']);

    if (!$foundTotal)
        exit('["'.Util::jsEscape($query).'", []]');

    foreach ($found as $id => $set)
    {
        $max = max(1, round($maxResults * count($set['data']) / $foundTotal));
        $maxResults -= $max;

        for ($i = 0; $i < $max; $i++)
        {
            $data = array_pop($set['data']);
            if (!$data)
                break;

            $names[] = '"'.$data['name'].$set['appendix'].'"';
            $extra   = [$set['type'], $data['Id']];

            if (isset($data['param1']))
                $extra[] = $data['param1'];

            if (isset($data['param2']))
                $extra[] = $data['param2'];

            $info[]  = '['.implode(', ', $extra).']';
        }
    }

    header("Content-type: text/javascript");
    die('["'.Util::jsEscape($search).'", ['.implode(', ', $names).'], [], [], [], [], [], ['.implode(', ', $info).']]');
}
else /* if ($searchMask & SEARCH_TYPE_REGULAR) */
{
    $foundTotal = 0;

    foreach ($found as $tmp)
        $foundTotal += count($tmp['data']);

    // // only one match -> redirect to find
    // if ($foundTotal == 1)
    // {
        // header("Location: ?".Util::$typeStrings[$found[0]['type']].'='.$found[0]['data'][0]['Id']);
        // die();
    // }

    $vars = array(
        'title'     => $search.' - '.Lang::$search['search'],
        'tab'       => 0                                    // tabId 0: Database for g_initHeader($tab)
    );

    $smarty->updatePageVars($vars);
    $smarty->assign('lang', array_merge(Lang::$main, Lang::$search));
	$smarty->assign('found', $found);
    $smarty->assign('data', $jsGlobals);
    $smarty->assign('search', $search);
    $smarty->assign('mysql', DB::Aowow()->getStatistics());
    $smarty->assign('util', new Util);                      // just for debugging / optimizing

    $smarty->display('search.tpl');
}

?>
