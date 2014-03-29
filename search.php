<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

/*
    if &json
        => search by compare or profiler
    else if &opensearch
        => suggestions when typing into searchboxes
        array:[
            str,        // search
            str[10],    // found
            [],         // unused
            [],         // unused
            [],         // unused
            [],         // unused
            [],         // unused
            str[10][4]  // type, typeId, param1 (4:quality, 3,6,9,10,17:icon, 5,11:faction), param2 (3:quality, 6:rank)
        ]
    else
        => listviews

todo    26: Listview - template: 'profile',     id: 'characters',    name: LANG.tab_characters,          visibleCols: ['race','classs','level','talents','gearscore','achievementpoints'],
        27: Profiles..?
        28: Guilds..?
        29: Arena Teams..?
*/

$type       = @intVal($_GET['type']);
$searchMask = 0x0;
$found      = [];
$cndBase    = ['AND'];
$maxResults = CFG_SQL_LIMIT_SEARCH;

$_wt        = isset($_GET['wt'])  ? explode(':', $_GET['wt'])  : null;
$_wtv       = isset($_GET['wtv']) ? explode(':', $_GET['wtv']) : null;
$_slots     = [];

$search     = urlDecode(trim($pageParam));
$query      = strtr($search, '?*', '_%');
$invalid    = [];
$include    = [];
$exclude    = [];

$parts = explode(' ', $query);
foreach ($parts as $p)
{
    if ($p[0] == '-')
    {
        if (strlen($p) < 4)
            $invalid[] = $p;
        else
            $exclude[] = substr($p, 1);
    }
    else
    {
        if (strlen($p) < 3)
            $invalid[] = $p;
        else
            $include[] = $p;
    }
}

$createLookup = function(array $fields = []) use($include, $exclude)
{
    // default to name-field
    if (!$fields)
        $fields[] = 'name_loc'.User::$localeId;

    $qry = [];
    foreach ($fields as $n => $f)
    {
        $sub = [];
        foreach ($include as $i)
            $sub[] = [$f, '%'.$i.'%'];

        foreach ($exclude as $x)
            $sub[] = [$f, '%'.$x.'%', '!'];

        // single cnd?
        if (count($sub) > 1)
            array_unshift($sub, 'AND');
        else
            $sub = $sub[0];

        $qry[] = $sub;
    }

    // single cnd?
    if (count($qry) > 1)
        array_unshift($qry, 'OR');
    else
        $qry = $qry[0];

    return $qry;
};

if (isset($_GET['json']))
{
    if ($_ = intVal($search))                               // allow for search by Id
        $query = $_;

    if (!empty($_GET['slots']))
    {
        $_slots = explode(':', $_GET['slots']);
        array_walk($_slots, function(&$v, $k) {
            $v = intVal($v);
        });

        $searchMask |= SEARCH_TYPE_JSON | 0x40;
    }
    else if ($type == TYPE_ITEMSET)
        $searchMask |= SEARCH_TYPE_JSON | 0x60;
    else if ($type == TYPE_ITEM)
        $searchMask |= SEARCH_TYPE_JSON | 0x40;
}
else if (isset($_GET['opensearch']))
{
    $maxResults  = CFG_SQL_LIMIT_QUICKSEARCH;
    $searchMask |= SEARCH_TYPE_OPEN | SEARCH_MASK_OPEN;
}
else
    $searchMask |= SEARCH_TYPE_REGULAR | SEARCH_MASK_ALL;

$cndBase[] = $maxResults;

// Exclude internal wow stuff
if (!User::isInGroup(U_GROUP_STAFF))
    $cndBase[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

$cacheKey = implode('_', [CACHETYPE_SEARCH, $searchMask, sha1($query), User::$localeId]);

// invalid conditions: not enough characters to search OR no types to search
if ((!$include || !($searchMask & SEARCH_MASK_ALL)) && !($searchMask & SEARCH_TYPE_JSON && intVal($search)))
{
    if ($searchMask & SEARCH_TYPE_REGULAR)
    {
        // empty queries go home
        if (!$query)
            header("Location:?");

        // insufficient queries throw an error
        $smarty->assign('lang', array_merge(Lang::$main, Lang::$search));
        $smarty->assign('found', []);
        $smarty->assign('search', $search);
        $smarty->assign('ignored', implode(', ', $invalid));

        $smarty->display('search.tpl');
        die();
    }
    else if ($searchMask & SEARCH_TYPE_OPEN)
    {
        header("Content-type: text/javascript");
        exit('["'.Util::jsEscape($search).'", []]');
    }
    else if (!$_wt || !$_wtv)                               // implicitly: SEARCH_TYPE_JSON
    {
        header("Content-type: text/javascript");
        exit ("[\"".Util::jsEscape($search)."\", [\n],[\n]]\n");
    }
}

if (!$smarty->loadCache($cacheKey, $found))
{
    // 1 Classes:
    if ($searchMask & 0x00000001)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $classes = new CharClassList($cnd);

        if ($data = $classes->getListviewData())
        {
            foreach ($classes->iterate() as $__)
                $data[$classes->id]['param1'] = '"class_'.strToLower($classes->getField('fileString')).'"';

            $found['class'] = array(
                'type'     => TYPE_CLASS,
                'appendix' => ' (Class)',
                'matches'  => $classes->getMatches(),
                'file'     => CharClassList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($classes->getMatches() > $maxResults)
            {
                // $found['class']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $classes->getMatches(), $maxResults);
                $found['class']['params']['_truncated'] = 1;
            }
        }
    }

    // 2 Races:
    if ($searchMask & 0x00000002)
    {
        /*  custom data :(
            faction:    dbc-data is internal -> doesn't work how to link to displayable faction..?
            leader:     29611 for human .. DAFUQ?!
            zone:       starting zone...
        */

        $cnd = array_merge($cndBase, [$createLookup()]);
        $races = new CharRaceList($cnd);

        if ($data = $races->getListviewData())
        {
            foreach ($races->iterate() as $__)
                $data[$races->id]['param1'] = '"race_'.strToLower($races->getField('fileString')).'_male"';

            $found['race'] = array(
                'type'     => TYPE_RACE,
                'appendix' => ' (Race)',
                'matches'  => $races->getMatches(),
                'file'     => CharRaceList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($races->getMatches() > $maxResults)
            {
                // $found['race']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $races->getMatches(), $maxResults);
                $found['race']['params']['_truncated'] = 1;
            }
        }
    }

    // 3 Titles:
    if ($searchMask & 0x00000004)
    {
        /*  custom data :(
            category:1,     // custom data .. FU!
            expansion:0,    // custom data .. FU FU!
            gender:3,       // again.. luckiely 136; 137 only
            side:2,         // hmm, derive from source..?
            source: {}      // g_sources .. holy cow.. that will cost some nerves
        */

        $cnd = array_merge($cndBase, [$createLookup(['male_loc'.User::$localeId, 'female_loc'.User::$localeId])]);
        $titles = new TitleList($cnd);

        if ($data = $titles->getListviewData())
        {
            foreach ($titles->iterate() as $id => $__)
                $data[$id]['param1'] = $titles->getField('side');

            $found['title'] = array(
                'type'     => TYPE_TITLE,
                'appendix' => ' (Title)',
                'matches'  => $titles->getMatches(),
                'file'     => TitleList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($titles->getMatches() > $maxResults)
            {
                // $found['title']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $titles->getMatches(), $maxResults);
                $found['title']['params']['_truncated'] = 1;
            }
        }
    }

    // 4 World Events:
    if ($searchMask & 0x00000008)
    {
        /*  custom data :(
            icons: data/interface/calendar/calendar_[a-z]start.blp
        */

        $cnd = array_merge($cndBase, array(
            'OR',
            $createLookup(['h.name_loc'.User::$localeId]),
            [
                'AND',
                $createLookup(['e.description']),
                ['e.holidayId', 0]
            ]
        ));
        $wEvents = new WorldEventList($cnd);

        if ($data  = $wEvents->getListviewData())
        {
            $wEvents->addGlobalsToJscript();

            foreach ($data as &$d)
            {
                $updated = WorldEventList::updateDates($d);
                unset($d['_date']);
                $d['startDate'] = $updated['start'] ? date(Util::$dateFormatInternal, $updated['start']) : false;
                $d['endDate']   = $updated['end']   ? date(Util::$dateFormatInternal, $updated['end'])   : false;
                $d['rec']       = $updated['rec'];
            }

            $found['event'] = array(
                'type'     => TYPE_WORLDEVENT,
                'appendix' => ' (World Event)',
                'matches'  => $wEvents->getMatches(),
                'file'     => WorldEventList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($wEvents->getMatches() > $maxResults)
            {
                // $found['event']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $wEvents->getMatches(), $maxResults);
                $found['event']['params']['_truncated'] = 1;
            }
        }
    }

    // 5 Currencies
    if ($searchMask & 0x0000010)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $money = new CurrencyList($cnd);

        if ($data = $money->getListviewData())
        {
            foreach ($money->iterate() as $__)
                $data[$money->id]['param1'] = '"'.strToLower($money->getField('iconString')).'"';

            $found['currency'] = array(
                'type'     => TYPE_CURRENCY,
                'appendix' => ' (Currency)',
                'matches'  => $money->getMatches(),
                'file'     => CurrencyList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($money->getMatches() > $maxResults)
            {
                $found['currency']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_currenciesfound', $money->getMatches(), $maxResults);
                $found['currency']['params']['_truncated'] = 1;
            }
        }
    }

    // 6 Itemsets
    if ($searchMask & 0x0000020)
    {
        // ['item1', 0, '!'],                               // remove empty sets from search, set in cuFlags
        $cnd = array_merge($cndBase, [is_int($query) ? ['id', $query] : $createLookup()]);
        $sets = new ItemsetList($cnd);

        if ($data = $sets->getListviewData())
        {
            $sets->addGlobalsToJScript(GLOBALINFO_SELF);

            foreach ($sets->iterate() as $__)
                $data[$sets->id]['param1'] = $sets->getField('quality');

            $found['itemset'] = array(
                'type'     => TYPE_ITEMSET,
                'appendix' => ' (Item Set)',
                'matches'  => $sets->getMatches(),
                'file'     => ItemsetList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs'],
                'pcsToSet' => $sets->pieceToSet
            );

            if ($sets->getMatches() > $maxResults)
            {
                $found['itemset']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_itemsetsfound', $sets->getMatches(), $maxResults);
                $found['itemset']['params']['_truncated'] = 1;
            }
        }
    }

    // 7 Items
    if ($searchMask & 0x0000040)
    {
        $miscData = [];
        $cndAdd   = empty($query) ? [] : (is_int($query) ? ['id', $query] : $createLookup());

        if (($searchMask & SEARCH_TYPE_JSON) && $type == TYPE_ITEMSET && isset($found['itemset']))
        {
            $cnd      = [['i.id', array_keys($found['itemset']['pcsToSet'])], CFG_SQL_LIMIT_NONE];
            $miscData = ['pcsToSet' => @$found['itemset']['pcsToSet']];
        }
        else if (($searchMask & SEARCH_TYPE_JSON) && ($type == TYPE_ITEM || $_slots))
        {
            $iCnd = [['i.class', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR]], $cndAdd];
            if ($_slots)
                $iCnd[] = ['slot', $_slots];

            $cnd   = array_merge($cndBase, [$iCnd]);
            $wData = ['wt' => $_wt, 'wtv' => $_wtv];

            $itemFilter = new ItemListFilter();
            if ($_ = $itemFilter->createConditionsForWeights($wData))
            {
                $miscData['extraOpts'] = $itemFilter->extraOpts;
                $cnd = array_merge($cnd, [$_]);
            }
        }
        else
            $cnd = array_merge($cndBase, [$cndAdd]);

        $items = new ItemList($cnd, $miscData);

        if ($data = $items->getListviewData($searchMask & SEARCH_TYPE_JSON ? (ITEMINFO_SUBITEMS | ITEMINFO_JSON) : 0))
        {
            $items->addGlobalsToJscript();

            foreach ($items->iterate() as $__)
            {
                $data[$items->id]['param1'] = '"'.$items->getField('iconString').'"';
                $data[$items->id]['param2'] = $items->getField('quality');

                if ($searchMask & SEARCH_TYPE_OPEN)
                    $data[$items->id]['name'] = substr($data[$items->id]['name'], 1);
            }

            $found['item'] = array(
                'type'     => TYPE_ITEM,
                'appendix' => ' (Item)',
                'matches'  => $items->getMatches(),
                'file'     => ItemList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($items->getMatches() > $maxResults)
            {
                $found['item']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_itemsfound', $items->getMatches(), $maxResults);
                $found['item']['params']['_truncated'] = 1;
            }
        }
    }

    // 8 Abilities (Player + Pet)
    if ($searchMask & 0x0000080)
    {
        $cnd = array_merge($cndBase, array(                 // hmm, inclued classMounts..?
            ['s.typeCat', [7, -2, -3]],
            [['s.cuFlags', (SPELL_CU_TRIGGERED | SPELL_CU_TALENT), '&'], 0],
            [['s.attributes0', 0x80, '&'], 0],
            $createLookup()
        ));
        $abilities = new SpellList($cnd);

        if ($data = $abilities->getListviewData())
        {
            $abilities->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);

            $vis = ['level', 'singleclass', 'schools'];
            if ($abilities->hasSetFields(['reagent1']))
                $vis[] = 'reagents';

            foreach ($abilities->iterate() as $__)
            {
                $data[$abilities->id]['param1'] = '"'.strToLower($abilities->getField('iconString')).'"';
                $data[$abilities->id]['param2'] = '"'.$abilities->ranks[$abilities->id].'"';
            }

            $found['ability'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Ability)',
                'matches'  => $abilities->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'abilities',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_abilities',
                    'visibleCols' => '$'.json_encode($vis)
                ]
            );

            if ($abilities->getMatches() > $maxResults)
            {
                $found['ability']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_abilitiesfound', $abilities->getMatches(), $maxResults);
                $found['ability']['params']['_truncated'] = 1;
            }
        }
    }

    // 9 Talents (Player + Pet)
    if ($searchMask & 0x0000100)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', [-7, -2]], $createLookup()]);
        $talents = new SpellList($cnd);

        if ($data = $talents->getListviewData())
        {
            $talents->addGlobalsToJscript();

            $vis = ['level', 'singleclass', 'schools'];
            if ($abilities->hasSetFields(['reagent1']))
                $vis[] = 'reagents';

            foreach ($talents->iterate() as $__)
            {
                $data[$talents->id]['param1'] = '"'.strToLower($talents->getField('iconString')).'"';
                $data[$talents->id]['param2'] = '"'.$talents->ranks[$talents->id].'"';
            }

            $found['talent'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Talent)',
                'matches'  => $talents->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'talents',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_talents',
                    'visibleCols' => '$'.json_encode($vis)
                ]
            );

            if ($talents->getMatches() > $maxResults)
            {
                $found['talent']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_talentsfound', $talents->getMatches(), $maxResults);
                $found['talent']['params']['_truncated'] = 1;
            }
        }
    }

    // 10 Glyphs
    if ($searchMask & 0x0000200)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', -13], $createLookup()]);
        $glyphs = new SpellList($cnd);

        if ($data = $glyphs->getListviewData())
        {
            $glyphs->addGlobalsToJScript(GLOBALINFO_SELF);

            foreach ($glyphs->iterate() as $__)
                $data[$glyphs->id]['param1'] = '"'.strToLower($glyphs->getField('iconString')).'"';

            $found['glyph'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Glyph)',
                'matches'  => $glyphs->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'glyphs',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_glyphs',
                    'visibleCols' => "$['singleclass', 'glyphtype']"
                ]
            );

            if ($glyphs->getMatches() > $maxResults)
            {
                $found['glyph']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_glyphsfound', $glyphs->getMatches(), $maxResults);
                $found['glyph']['params']['_truncated'] = 1;
            }
        }
    }

    // 11 Proficiencies
    if ($searchMask & 0x0000400)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', -11], $createLookup()]);
        $prof = new SpellList($cnd);

        if ($data = $prof->getListviewData())
        {
            $prof->addGlobalsToJScript(GLOBALINFO_SELF);

            foreach ($prof->iterate() as $__)
                $data[$prof->id]['param1'] = '"'.strToLower($prof->getField('iconString')).'"';

            $found['proficiency'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Proficiency)',
                'matches'  => $prof->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'proficiencies',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_proficiencies',
                    'visibleCols' => "$['classes']"
                ]
            );

            if ($prof->getMatches() > $maxResults)
            {
                $found['proficiency']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $prof->getMatches(), $maxResults);
                $found['proficiency']['params']['_truncated'] = 1;
            }
        }
    }

    // 12 Professions (Primary + Secondary)
    if ($searchMask & 0x0000800)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', [9, 11]], $createLookup()]);
        $prof = new SpellList($cnd);

        if ($data = $prof->getListviewData())
        {
            $prof->addGlobalsToJScript(GLOBALINFO_SELF | GLOBALINFO_RELATED);

            foreach ($prof->iterate() as $__)
                $data[$prof->id]['param1'] = '"'.strToLower($prof->getField('iconString')).'"';

            $found['profession'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Profession)',
                'matches'  => $prof->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'professions',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_professions',
                    'visibleCols' => "$['source', 'reagents']"
                ]
            );

            if ($prof->getMatches() > $maxResults)
            {
                $found['profession']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_professionfound', $prof->getMatches(), $maxResults);
                $found['profession']['params']['_truncated'] = 1;
            }
        }
    }

    // 13 Companions
    if ($searchMask & 0x0001000)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', -6], $createLookup()]);
        $vPets = new SpellList($cnd);

        if ($data = $vPets->getListviewData())
        {
            $vPets->addGlobalsToJscript();

            foreach ($vPets->iterate() as $__)
                $data[$vPets->id]['param1'] = '"'.strToLower($vPets->getField('iconString')).'"';

            $found['companion'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Companion)',
                'matches'  => $vPets->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'companions',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_companions',
                    'visibleCols' => "$['reagents']"
                ]
            );

            if ($vPets->getMatches() > $maxResults)
            {
                $found['companion']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_companionsfound', $vPets->getMatches(), $maxResults);
                $found['companion']['params']['_truncated'] = 1;
            }
        }
    }

    // 14 Mounts
    if ($searchMask & 0x0002000)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', -5], $createLookup()]);
        $mounts = new SpellList($cnd);

        if ($data = $mounts->getListviewData())
        {
            $mounts->addGlobalsToJScript(GLOBALINFO_SELF);

            foreach ($mounts->iterate() as $__)
                $data[$mounts->id]['param1'] = '"'.strToLower($mounts->getField('iconString')).'"';

            $found['mount'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Mount)',
                'matches'  => $mounts->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'   => 'mounts',
                    'tabs' => '$myTabs',
                    'name' => '$LANG.tab_mounts',
                ]
            );

            if ($mounts->getMatches() > $maxResults)
            {
                $found['mount']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_mountsfound', $mounts->getMatches(), $maxResults);
                $found['mount']['params']['_truncated'] = 1;
            }
        }
    }

    // 15 NPCs
    if ($searchMask & 0x0004000)
    {
        // [['cuFlags', MASKE, '&'], 0],     // todo (med): exclude trigger creatures and difficulty entries
        $cnd = array_merge($cndBase, [$createLookup()]);
        $npcs = new CreatureList($cnd);

        if ($data = $npcs->getListviewData())
        {
            $found['npc'] = array(
                'type'     => TYPE_NPC,
                'appendix' => ' (NPC)',
                'matches'  => $npcs->getMatches(),
                'file'     => CreatureList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'   => 'npcs',
                    'tabs' => '$myTabs',
                    'name' => '$LANG.tab_npcs',
                ]
            );

            if ($npcs->getMatches() > $maxResults)
            {
                $found['npc']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), $maxResults);
                $found['npc']['params']['_truncated'] = 1;
            }
        }
    }

    // 16 Quests
    if ($searchMask & 0x0008000)
    {
        //        [['cuFlags', MASK, '&'], 0],                                      // todo (med): identify disabled quests
        $cnd = array_merge($cndBase, [$createLookup()]);
        $quests = new QuestList($cnd);

        if ($data = $quests->getListviewData())
        {
            $quests->addGlobalsToJScript();

            $found['quest'] = array(
                'type'     => TYPE_QUEST,
                'appendix' => ' (Quest)',
                'matches'  => $quests->getMatches(),
                'file'     => QuestList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );
        }

        if ($quests->getMatches() > $maxResults)
        {
            $found['quest']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_questsfound', $quests->getMatches(), $maxResults);
            $found['quest']['params']['_truncated'] = 1;
        }
    }

    // 17 Achievements
    if ($searchMask & 0x0010000)
    {
        $cnd = array_merge($cndBase, [[['flags', ACHIEVEMENT_FLAG_COUNTER, '&'], 0], $createLookup()]);
        $acvs = new AchievementList($cnd);

        if ($data = $acvs->getListviewData())
        {
            $acvs->addGlobalsToJScript();

            foreach ($acvs->iterate() as $__)
                $data[$acvs->id]['param1'] = '"'.strToLower($acvs->getField('iconString')).'"';

            $found['achievement'] = array(
                'type'     => TYPE_ACHIEVEMENT,
                'appendix' => ' (Achievement)',
                'matches'  => $acvs->getMatches(),
                'file'     => AchievementList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs'        => '$myTabs',
                    'visibleCols' => "$['category']"
                ]
            );

            if ($acvs->getMatches() > $maxResults)
            {
                $found['achievement']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_achievementsfound', $acvs->getMatches(), $maxResults);
                $found['achievement']['params']['_truncated'] = 1;
            }
        }
    }

    // 18 Statistics
    if ($searchMask & 0x0020000)
    {
        $cnd = array_merge($cndBase, [['flags', ACHIEVEMENT_FLAG_COUNTER, '&'], $createLookup()]);
        $stats = new AchievementList($cnd);

        if ($data = $stats->getListviewData())
        {
            $stats->addGlobalsToJScript(GLOBALINFO_SELF);

            $found['statistic'] = array(
                'type'     => TYPE_ACHIEVEMENT,
                'matches'  => $stats->getMatches(),
                'file'     => AchievementList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs'        => '$myTabs',
                    'visibleCols' => "$['category']",
                    'hiddenCols'  => "$['side', 'points', 'rewards']",
                    'name'        => '$LANG.tab_statistics',
                    'id'          => 'statistics'
                ]
            );

            if ($stats->getMatches() > $maxResults)
            {
                $found['statistic']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_statisticsfound', $stats->getMatches(), $maxResults);
                $found['statistic']['params']['_truncated'] = 1;
            }
        }
    }

    // 19 Zones
    if ($searchMask & 0x0040000)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $zones = new ZoneList($cnd);

        if ($data = $zones->getListviewData())
        {
            $zones->addGlobalsToJScript();

            $found['zone'] = array(
                'type'     => TYPE_ZONE,
                'appendix' => ' (Zone)',
                'matches'  => $zones->getMatches(),
                'file'     => ZoneList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs' => '$myTabs'
                ]
            );

            if ($zones->getMatches() > $maxResults)
            {
                $found['zone']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_zonesfound', $zones->getMatches(), $maxResults);
                $found['zone']['params']['_truncated'] = 1;
            }
        }
    }

    // 20 Objects
    if ($searchMask & 0x0080000)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $objects = new GameObjectList($cnd);

        if ($data = $objects->getListviewData())
        {
            $objects->addGlobalsToJScript();

            $found['object'] = array(
                'type'     => TYPE_OBJECT,
                'appendix' => ' (Object)',
                'matches'  => $objects->getMatches(),
                'file'     => GameObjectList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs' => '$myTabs'
                ]
            );

            if ($objects->getMatches() > $maxResults)
            {
                $found['object']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_objectsfound', $objects->getMatches(), $maxResults);
                $found['object']['params']['_truncated'] = 1;
            }
        }
    }

    // 21 Factions
    if ($searchMask & 0x0100000)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $factions = new FactionList($cnd);

        if ($data = $factions->getListviewData())
        {
            $found['faction'] = array(
                'type'     => TYPE_FACTION,
                'appendix' => ' (Faction)',
                'matches'  => $factions->getMatches(),
                'file'     => FactionList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs' => '$myTabs'
                ]
            );

            if ($factions->getMatches() > $maxResults)
            {
                $found['faction']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_factionsfound', $factions->getMatches(), $maxResults);
                $found['faction']['params']['_truncated'] = 1;
            }
        }
    }

    // 22 Skills
    if ($searchMask & 0x0200000)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $skills = new SkillList($cnd);

        if ($data = $skills->getListviewData())
        {
            foreach ($skills->iterate() as $id => $__)
                $data[$id]['param1'] = '"'.$skills->getField('iconString').'"';

            $found['pet'] = array(
                'type'     => TYPE_SKILL,
                'appendix' => ' (Skill)',
                'matches'  => $skills->getMatches(),
                'file'     => SkillList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs' => '$myTabs'
                ]
            );

            if ($skills->getMatches() > $maxResults)
            {
                $found['pet']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_skillsfound', $skills->getMatches(), $maxResults);
                $found['pet']['params']['_truncated'] = 1;
            }
        }
    }

    // 23 Pets
    if ($searchMask & 0x0400000)
    {
        $cnd = array_merge($cndBase, [$createLookup()]);
        $pets = new PetList($cnd);

        if ($data = $pets->getListviewData())
        {
            foreach ($pets->iterate() as $__)
                $data[$pets->id]['param1'] = '"'.$pets->getField('iconString').'"';

            $found['pet'] = array(
                'type'     => TYPE_PET,
                'appendix' => ' (Pet)',
                'matches'  => $pets->getMatches(),
                'file'     => PetList::$brickFile,
                'data'     => $data,
                'params'   => ['tabs' => '$myTabs']
            );

            if ($pets->getMatches() > $maxResults)
            {
                $found['pet']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_petsfound', $pets->getMatches(), $maxResults);
                $found['pet']['params']['_truncated'] = 1;
            }
        }
    }

    // 24 NPCAbilities
    if ($searchMask & 0x0800000)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', -8], $createLookup()]);
        $npcAbilities = new SpellList($cnd);

        if ($data = $npcAbilities->getListviewData())
        {
            $npcAbilities->addGlobalsToJScript(GLOBALINFO_SELF);

            foreach ($npcAbilities->iterate() as $__)
                $data[$npcAbilities->id]['param1'] = '"'.strToLower($npcAbilities->getField('iconString')).'"';

            $found['npcSpell'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Spell)',
                'matches'  => $npcAbilities->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'id'          => 'npc-abilities',
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_npcabilities',
                    'visibleCols' => "$['level']",
                    'hiddenCols'  => "$['skill']"
                ]
            );

            if ($npcAbilities->getMatches() > $maxResults)
            {
                $found['npcSpell']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $npcAbilities->getMatches(), $maxResults);
                $found['npcSpell']['params']['_truncated'] = 1;
            }
        }
    }

    // 25 Spells (Misc + GM)
    if ($searchMask & 0x1000000)
    {
        $cnd = array_merge($cndBase, [['s.typeCat', [0, -9]], $createLookup()]);
        $misc = new SpellList($cnd);

        if ($data = $misc->getListviewData())
        {
            $misc->addGlobalsToJScript(GLOBALINFO_SELF);

            foreach ($misc->iterate() as $__)
                $data[$misc->id]['param1'] = '"'.strToLower($misc->getField('iconString')).'"';

            $found['spell'] = array(
                'type'     => TYPE_SPELL,
                'appendix' => ' (Spell)',
                'matches'  => $misc->getMatches(),
                'file'     => SpellList::$brickFile,
                'data'     => $data,
                'params'   => [
                    'tabs'        => '$myTabs',
                    'name'        => '$LANG.tab_uncategorizedspells',
                    'visibleCols' => "$['level']",
                    'hiddenCols'  => "$['skill']",
                ]
            );

            if ($misc->getMatches() > $maxResults)
            {
                $found['spell']['params']['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $misc->getMatches(), $maxResults);
                $found['spell']['params']['_truncated'] = 1;
            }
        }
    }

    // 26 Characters
    // if ($searchMask & 0x2000000)

    // 27 Guilds
    // if ($searchMask & 0x4000000)

    $smarty->saveCache($cacheKey, $found);
}

/*
    !note! dear reader, if you ever try to generate a string, that is to be evaled by JS, NEVER EVER terminate with a \n
    $totalHoursWasted +=2;
*/
if ($searchMask & SEARCH_TYPE_JSON)
{
    $outItems = '';
    $outSets  = '';

    if (isset($found['item']))
    {
        $items = [];

        foreach ($found['item']['data'] as $k => $v)
        {
            unset($v['param1']);
            unset($v['param2']);

            $items[] = json_encode($v, JSON_NUMERIC_CHECK);
        }

        $outItems = "\t".implode(",\n\t", $items)."\n";
    }

    if (isset($found['itemset']))
    {
        $sets = [];

        foreach ($found['itemset']['data'] as $k => $v)
        {
            $v['name'] = $v['quality'].$v['name'];

            unset($v['param1']);
            unset($v['quality']);
            if (!$v['heroic'])
                unset($v['heroic']);

            $sets[] = json_encode($v, JSON_NUMERIC_CHECK);
        }

        $outSets = "\t".implode(",\n\t", $sets)."\n";
    }

    header("Content-type: text/javascript");
    die ('["'.Util::jsEscape($search)."\", [\n".$outItems."],[\n".$outSets.']]');
}
else if ($searchMask & SEARCH_TYPE_OPEN)
{
    // this one is funny: we want 10 results, ideally equally distributed over each type
    $foundTotal = 0;
    $maxResults = 10;
    $names      = [];
    $info       = [];

    foreach ($found as $tmp)
        $foundTotal += $tmp['matches'];

    if (!$foundTotal)
        exit('["'.Util::jsEscape($search).'", []]');

    foreach ($found as $id => $set)
    {
        $max = max(1, (int)($maxResults * $set['matches'] / $foundTotal));
        $maxResults -= $max;

        for ($i = 0; $i < $max; $i++)
        {
            $data = array_shift($set['data']);
            if (!$data)
                break;

            $names[] = '"'.$data['name'].$set['appendix'].'"';
            $extra   = [$set['type'], $data['id']];

            if (isset($data['param1']))
                $extra[] = $data['param1'];

            if (isset($data['param2']))
                $extra[] = $data['param2'];

            $info[]  = '['.implode(', ', $extra).']';
        }

        if ($maxResults <= 0)
            break;
    }

    header("Content-type: text/javascript");
    die('["'.Util::jsEscape($search).'", ['.implode(', ', $names).'], [], [], [], [], [], ['.implode(', ', $info).']]');
}
else /* if ($searchMask & SEARCH_TYPE_REGULAR) */
{
    $foundTotal = 0;

    foreach ($found as $tmp)
        $foundTotal += count($tmp['data']);

    // only one match -> redirect to find
    if ($foundTotal == 1)
    {
        $_      = array_pop($found);
        $type   = Util::$typeStrings[$_['type']];
        $typeId = key($_['data']);

        header("Location: ?".$type.'='.$typeId);
        die();
    }


    // tabId 0: Database g_initHeader()
    $smarty->updatePageVars(array(
        'title' => $search.' - '.Lang::$search['search'],
        'tab'   => 0,
        'reqJS' => array(
            'static/js/swfobject.js'
        )
    ));
    $smarty->assign('lang', array_merge(Lang::$main, Lang::$search));
    $smarty->assign('found', $found);
    $smarty->assign('search', $search);
    $smarty->assign('ignored', implode(', ', $invalid));

    $smarty->display('search.tpl');
}

?>
