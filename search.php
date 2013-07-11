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
        8:  Listview - template: 'spell',       id: 'abilities',     name: LANG.tab_abilities,
        9:  Listview - template: 'spell',       id: 'talents',       name: LANG.tab_talents,
        10: Listview - template: 'spell',       id: 'glyphs',        name: LANG.tab_glyphs,
        11: Listview - template: 'spell',       id: 'proficiencies', name: LANG.tab_proficiencies,
        12: Listview - template: 'spell',       id: 'professions',   name: LANG.tab_professions,
        13: Listview - template: 'spell',       id: 'companions',    name: LANG.tab_companions,
        14: Listview - template: 'spell',       id: 'mounts',        name: LANG.tab_mounts,
todo    15: Listview - template: 'npc',         id: 'npcs',          name: LANG.tab_npcs,
todo    16: Listview - template: 'quest',       id: 'quests',        name: LANG.tab_quests,
        17: Listview - template: 'achievement', id: 'achievements',  name: LANG.tab_achievements,
        18: Listview - template: 'achievement', id: 'statistics',    name: LANG.tab_statistics,
todo    19: Listview - template: 'zone',        id: 'zones',         name: LANG.tab_zones,
todo    20: Listview - template: 'object',      id: 'objects',       name: LANG.tab_objects,
todo    21: Listview - template: 'faction',     id: 'factions',      name: LANG.tab_factions,
todo    22: Listview - template: 'skill',       id: 'skills',        name: LANG.tab_skills,                                                         hiddenCols: ['reagents', 'skill'],
        23: Listview - template: 'pet',         id: 'pets',          name: LANG.tab_pets,
        24: Listview - template: 'spell',       id: 'npc-abilities', name: LANG.tab_npcabilities,
        25: Listview - template: 'spell',       id: 'spells',        name: LANG.tab_uncategorizedspells,
todo    26: Listview - template: 'profile',     id: 'characters',    name: LANG.tab_characters,          visibleCols: ['race','classs','level','talents','gearscore','achievementpoints'],
        27: Profiles..?
        28: Guilds..?
        29: Arena Teams..?
*/

$search      = urlDecode(trim($pageParam));
$query       = Util::sqlEscape(str_replace('?', '_', str_replace('*', '%', ($search))));
$type        = @intVal($_GET['type']);
$searchMask  = 0x0;
$found       = [];
$jsGlobals   = [];
$maxResults  = 500;                                         // todo: move to config

if (isset($_GET['json']))
{
    if ($type == TYPE_ITEMSET)
        $searchMask |= SEARCH_TYPE_JSON | 0x60;
    else if ($type == TYPE_ITEM)
        $searchMask |= SEARCH_TYPE_JSON | 0x40;
}
else if (isset($_GET['opensearch']))
{
    $maxResults = 10;
    $searchMask |= SEARCH_TYPE_OPEN | SEARCH_MASK_OPEN;
}
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
    $classes = new CharClassList(array(['name_loc'.User::$localeId, $query], $maxResults));

    if ($data = $classes->getListviewData())
    {
        while ($classes->iterate())
            $data[$classes->id]['param1'] = '"class_'.strToLower($classes->getField('fileString')).'"';

        $found['class'] = array(
            'type'     => TYPE_CLASS,
            'appendix' => ' (Class)',
            'matches'  => $classes->getMatches(),
            'file'     => 'class',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );

        if ($classes->getMatches() > $maxResults)
        {
            // $found['class']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_', $classes->getMatches(), $maxResults);
            $found['class']['params']['_truncated'] = 1;
        }
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

    $races = new CharRaceList(array(['name_loc'.User::$localeId, $query], $maxResults));

    if ($data = $races->getListviewData())
    {
        while ($races->iterate())
            $data[$races->id]['param1'] = '"race_'.strToLower($races->getField('fileString')).'_male"';

        $found['race'] = array(
            'type'     => TYPE_RACE,
            'appendix' => ' (Race)',
            'matches'  => $races->getMatches(),
            'file'     => 'race',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );

        if ($races->getMatches() > $maxResults)
        {
            // $found['race']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_', $races->getMatches(), $maxResults);
            $found['race']['params']['_truncated'] = 1;
        }
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
        ['female_loc'.User::$localeId, $query],
        $maxResults
    );

    $titles = new TitleList($conditions);

    if ($data = $titles->getListviewData())
    {
        $found['title'] = array(
            'type'     => TYPE_TITLE,
            'appendix' => ' (Title)',
            'matches'  => $titles->getMatches(),
            'file'     => 'title',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );

        if ($titles->getMatches() > $maxResults)
        {
            // $found['title']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_', $titles->getMatches(), $maxResults);
            $found['title']['params']['_truncated'] = 1;
        }
    }
}

// 4 World Events:
if ($searchMask & 0x8)
{
    /*  custom data :(
        icons: data/interface/calendar/calendar_[a-z]start.blp
    */

    $conditions = array(
        'OR',
        ['h.name_loc'.User::$localeId, $query],
        ['AND', ['e.description', $query], ['e.holidayId', 0]],
        $maxResults
    );

    $wEvents = new WorldEventList($conditions);

    if ($data  = $wEvents->getListviewData())
    {
        $wEvents->addGlobalsToJscript($smarty);

        foreach ($data as &$d)
        {
            $updated = WorldEventList::updateDates($d['startDate'], $d['endDate'], $d['rec']);
            $d['startDate'] = date(Util::$dateFormatLong, $updated['start']);
            $d['endDate']   = date(Util::$dateFormatLong, $updated['end']);
        }

        $found['event'] = array(
            'type'     => TYPE_WORLDEVENT,
            'appendix' => ' (World Event)',
            'matches'  => $wEvents->getMatches(),
            'file'     => 'event',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );

        if ($wEvents->getMatches() > $maxResults)
        {
            // $found['event']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_', $wEvents->getMatches(), $maxResults);
            $found['event']['params']['_truncated'] = 1;
        }
    }
}

// 5 Currencies
if ($searchMask & 0x10)
{
    $money = new CurrencyList(array($maxResults, ['name_loc'.User::$localeId, $query]));

    if ($data = $money->getListviewData())
    {
        while ($money->iterate())
            $data[$money->id]['param1'] = '"'.strToLower($money->getField('iconString')).'"';

        $found['currency'] = array(
            'type'     => TYPE_CURRENCY,
            'appendix' => ' (Currency)',
            'matches'  => $money->getMatches(),
            'file'     => 'currency',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );

        if ($money->getMatches() > $maxResults)
        {
            $found['currency']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_currenciesfound', $money->getMatches(), $maxResults);
            $found['currency']['params']['_truncated'] = 1;
        }
    }
}

// 6 Itemsets
if ($searchMask & 0x20)
{
    $conditions = array(
        ['item1', 0, '!'],                                  // remove empty sets from search
        ['name_loc'.User::$localeId, $query],
        $maxResults
    );

    $sets = new ItemsetList($conditions);

    if ($data = $sets->getListviewData())
    {
        $sets->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        while ($sets->iterate())
            $data[$sets->id]['param1'] = $sets->getField('quality');

        $found['itemset'] = array(
            'type'     => TYPE_ITEMSET,
            'appendix' => ' (Item Set)',
            'matches'  => $sets->getMatches(),
            'file'     => 'itemset',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs'],
            'pcsToSet' => $sets->pieceToSet
        );

        if ($sets->getMatches() > $maxResults)
        {
            $found['itemset']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_itemsetsfound', $sets->getMatches(), $maxResults);
            $found['itemset']['params']['_truncated'] = 1;
        }
    }
}

// 7 Items
if ($searchMask & 0x40)
{
    if (($searchMask & SEARCH_TYPE_JSON) && $type == TYPE_ITEMSET && isset($found['itemset']))
        $conditions = [['i.entry', array_keys($found['itemset']['pcsToSet'])], 0];
    else if (($searchMask & SEARCH_TYPE_JSON) && $type == TYPE_ITEM)
        $conditions = [['i.class', [2, 4]], [User::$localeId ? 'name_loc'.User::$localeId : 'name', $query], $AoWoWconf['sqlLimit']];
    else
        $conditions = [[User::$localeId ? 'name_loc'.User::$localeId : 'name', $query], $maxResults];

    $items = new ItemList($conditions, @$found['itemset']['pcsToSet']);

    if ($data = $items->getListviewData($searchMask & SEARCH_TYPE_JSON ? (ITEMINFO_SUBITEMS | ITEMINFO_JSON) : 0))
    {
        $items->addGlobalsToJscript($smarty);

        while ($items->iterate())
        {
            $data[$items->id]['param1'] = '"'.$items->getField('icon').'"';
            $data[$items->id]['param2'] = $items->getField('Quality');

            if ($searchMask & SEARCH_TYPE_OPEN)
                $data[$items->id]['name'] = substr($data[$items->id]['name'], 1);
        }

        $found['item'] = array(
            'type'     => TYPE_ITEM,
            'appendix' => ' (Item)',
            'matches'  => $items->getMatches(),
            'file'     => 'item',
            'data'     => $data,
            'params'   => ['tabs' => '$myTabs']
        );

        if ($items->getMatches() > $maxResults)
        {
            $found['item']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_itemsfound', $items->getMatches(), $maxResults);
            $found['item']['params']['_truncated'] = 1;
        }
    }
}

// 8 Abilities (Player + Pet)
if ($searchMask & 0x80)
{
    $conditions = array(                                    // hmm, inclued classMounts..?
        ['s.typeCat', [7, -2, -3]],
        [['s.cuFlags', (SPELL_CU_TRIGGERED | SPELL_CU_TALENT | SPELL_CU_EXCLUDE_CATEGORY_SEARCH), '&'], 0],
        [['s.attributes0', 0x80, '&'], 0],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $abilities = new SpellList($conditions);

    if ($data = $abilities->getListviewData())
    {
        $abilities->addGlobalsToJscript($smarty);

        $vis = ['level', 'singleclass', 'schools'];
        if ($abilities->hasSetFields(['reagent1']))
            $vis[] = 'reagents';

        while ($abilities->iterate())
        {
            $data[$abilities->id]['param1'] = '"'.strToLower($abilities->getField('iconString')).'"';
            $data[$abilities->id]['param2'] = '"'.$abilities->ranks[$abilities->id].'"';
        }

        $found['ability'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Ability)',
            'matches'  => $abilities->getMatches(),
            'file'     => 'spell',
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
            $found['ability']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_abilitiesfound', $abilities->getMatches(), $maxResults);
            $found['ability']['params']['_truncated'] = 1;
        }
    }
}

// 9 Talents (Player + Pet)
if ($searchMask & 0x100)
{
    $conditions = array(
        ['s.typeCat', [-7, -2]],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $talents = new SpellList($conditions);

    if ($data = $talents->getListviewData())
    {
        $talents->addGlobalsToJscript($smarty);

        $vis = ['level', 'singleclass', 'schools'];
        if ($abilities->hasSetFields(['reagent1']))
            $vis[] = 'reagents';

        while ($talents->iterate())
        {
            $data[$talents->id]['param1'] = '"'.strToLower($talents->getField('iconString')).'"';
            $data[$talents->id]['param2'] = '"'.$talents->ranks[$talents->id].'"';
        }

        $found['talent'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Talent)',
            'matches'  => $talents->getMatches(),
            'file'     => 'spell',
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
            $found['talent']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_talentsfound', $talents->getMatches(), $maxResults);
            $found['talent']['params']['_truncated'] = 1;
        }
    }
}

// 10 Glyphs
if ($searchMask & 0x200)
{
    $conditions = array(
        ['s.typeCat', -13],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $glyphs = new SpellList($conditions);

    if ($data = $glyphs->getListviewData())
    {
        $glyphs->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        while ($glyphs->iterate())
            $data[$glyphs->id]['param1'] = '"'.strToLower($glyphs->getField('iconString')).'"';

        $found['glyph'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Glyph)',
            'matches'  => $glyphs->getMatches(),
            'file'     => 'spell',
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
            $found['glyph']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_glyphsfound', $glyphs->getMatches(), $maxResults);
            $found['glyph']['params']['_truncated'] = 1;
        }
    }
}

// 11 Proficiencies
if ($searchMask & 0x400)
{
    $conditions = array(
        ['s.typeCat', -11],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $prof = new SpellList($conditions);

    if ($data = $prof->getListviewData())
    {
        $prof->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        while ($prof->iterate())
            $data[$prof->id]['param1'] = '"'.strToLower($prof->getField('iconString')).'"';

        $found['proficiency'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Proficiency)',
            'matches'  => $prof->getMatches(),
            'file'     => 'spell',
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
            $found['proficiency']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_spellsfound', $prof->getMatches(), $maxResults);
            $found['proficiency']['params']['_truncated'] = 1;
        }
    }
}

// 12 Professions (Primary + Secondary)
if ($searchMask & 0x800)
{
    $conditions = array(
        ['s.typeCat', [9, 11]],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $prof = new SpellList($conditions);

    if ($data = $prof->getListviewData())
    {
        $prof->addGlobalsToJscript($smarty);

        while ($prof->iterate())
            $data[$prof->id]['param1'] = '"'.strToLower($prof->getField('iconString')).'"';

        $found['profession'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Profession)',
            'matches'  => $prof->getMatches(),
            'file'     => 'spell',
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
            $found['profession']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_professionfound', $prof->getMatches(), $maxResults);
            $found['profession']['params']['_truncated'] = 1;
        }
    }
}

// 13 Companions
if ($searchMask & 0x1000)
{

    $conditions = array(
        ['s.typeCat', -6],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $vPets = new SpellList($conditions);

    if ($data = $vPets->getListviewData())
    {
        $vPets->addGlobalsToJscript($smarty);

        while ($vPets->iterate())
            $data[$vPets->id]['param1'] = '"'.strToLower($vPets->getField('iconString')).'"';

        $found['companion'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Companion)',
            'matches'  => $vPets->getMatches(),
            'file'     => 'spell',
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
            $found['companion']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_companionsfound', $vPets->getMatches(), $maxResults);
            $found['companion']['params']['_truncated'] = 1;
        }
    }
}

// 14 Mounts
if ($searchMask & 0x2000)
{
    $conditions = array(
        ['s.typeCat', -5],
        ['s.name_loc'.User::$localeId, $query],
        $maxResults
    );

    $mounts = new SpellList($conditions);

    if ($data = $mounts->getListviewData())
    {
        $mounts->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        while ($mounts->iterate())
            $data[$mounts->id]['param1'] = '"'.strToLower($mounts->getField('iconString')).'"';

        $found['mount'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Mount)',
            'matches'  => $mounts->getMatches(),
            'file'     => 'spell',
            'data'     => $data,
            'params'   => [
                'id'   => 'mounts',
                'tabs' => '$myTabs',
                'name' => '$LANG.tab_mounts',
            ]
        );

        if ($mounts->getMatches() > $maxResults)
        {
            $found['mount']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_mountsfound', $mounts->getMatches(), $maxResults);
            $found['mount']['params']['_truncated'] = 1;
        }
    }
}

// 15 NPCs
if ($searchMask & 0x4000)
{
    $conditions = array(
        [
            'OR',
            ['name_loc'.User::$localeId, $query],
            ['subname_loc'.User::$localeId, $query]
        ],
        // [['cuFlags', MASKE, '&'], 0],     // todo (med): exclude trigger creatures and difficulty entries
        $maxResults
    );

    $npcs = new CreatureList($conditions);

    if ($data = $npcs->getListviewData())
    {
        $found['npc'] = array(
            'type'     => TYPE_NPC,
            'appendix' => ' (NPC)',
            'matches'  => $npcs->getMatches(),
            'file'     => 'creature',
            'data'     => $data,
            'params'   => [
                'id'   => 'npcs',
                'tabs' => '$myTabs',
                'name' => '$LANG.tab_npcs',
            ]
        );

        if ($npcs->getMatches() > $maxResults)
        {
            $found['npc']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), $maxResults);
            $found['npc']['params']['_truncated'] = 1;
        }
    }
}

// 16 Quests
// if ($searchMask & 0x8000)

// 17 Achievements
if ($searchMask & 0x10000)
{
    $conditions = array(
        [['flags', ACHIEVEMENT_FLAG_COUNTER, '&'], 0],
        ['name_loc'.User::$localeId, $query],
        $maxResults
    );

    $acvs = new AchievementList($conditions);

    if ($data = $acvs->getListviewData())
    {
        $acvs->addGlobalsToJScript($smarty);

        while ($acvs->iterate())
            $data[$acvs->id]['param1'] = '"'.strToLower($acvs->getField('iconString')).'"';

        $found['achievement'] = array(
            'type'     => TYPE_ACHIEVEMENT,
            'appendix' => ' (Achievement)',
            'matches'  => $acvs->getMatches(),
            'file'     => 'achievement',
            'data'     => $data,
            'params'   => [
                'tabs'        => '$myTabs',
                'visibleCols' => "\$['category']"
            ]
        );

        if ($acvs->getMatches() > $maxResults)
        {
            $found['achievement']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_achievementsfound', $acvs->getMatches(), $maxResults);
            $found['achievement']['params']['_truncated'] = 1;
        }
    }
}

// 18 Statistics
if ($searchMask & 0x20000)
{
    $conditions = array(
        ['flags', ACHIEVEMENT_FLAG_COUNTER, '&'],
        ['name_loc'.User::$localeId, $query],
        $maxResults
    );

    $stats = new AchievementList($conditions);

    if ($data = $stats->getListviewData())
    {
        $stats->addGlobalsToJScript($smarty, GLOBALINFO_SELF);

        $found['statistic'] = array(
            'type'     => TYPE_ACHIEVEMENT,
            'matches'  => $stats->getMatches(),
            'file'     => 'achievement',
            'data'     => $data,
            'params'   => [
                'tabs'        => '$myTabs',
                'visibleCols' => "\$['category']",
                'hiddenCols'  => "\$['side', 'points', 'rewards']",
                'name'        => '$LANG.tab_statistics',
                'id'          => 'statistics'
            ]
        );

        if ($stats->getMatches() > $maxResults)
        {
            $found['statistic']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_statisticsfound', $stats->getMatches(), $maxResults);
            $found['statistic']['params']['_truncated'] = 1;
        }
    }
}

// 19 Zones
// if ($searchMask & 0x40000)

// 20 Objects
// if ($searchMask & 0x80000)

// 21 Factions
// if ($searchMask & 0x100000)

// 22 Skills
// if ($searchMask & 0x200000)

// 23 Pets
if ($searchMask & 0x400000)
{
    $pets = new PetList(array($maxResults, ['name_loc'.User::$localeId, $query]));

    if ($data = $pets->getListviewData())
    {
        while ($pets->iterate())
            $data[$pets->id]['param1'] = '"'.$pets->getField('iconString').'"';

        $found['pet'] = array(
            'type'     => TYPE_PET,
            'appendix' => ' (Pet)',
            'matches'  => $pets->getMatches(),
            'file'     => 'pet',
            'data'     => $data,
            'params'   => [
                'tabs' => '$myTabs',
            ]
        );

        if ($pets->getMatches() > $maxResults)
        {
            $found['pet']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_petsfound', $pets->getMatches(), $maxResults);
            $found['pet']['params']['_truncated'] = 1;
        }
    }
}

// 24 NPCAbilities
if ($searchMask & 0x800000)
{
    $conditions = array(
        ['s.name_loc'.User::$localeId, $query],
        ['s.typeCat', -8],
        $maxResults
    );

    $npcAbilities = new SpellList($conditions);

    if ($data = $npcAbilities->getListviewData())
    {
        $npcAbilities->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        while ($npcAbilities->iterate())
            $data[$npcAbilities->id]['param1'] = '"'.strToLower($npcAbilities->getField('iconString')).'"';

        $found['npcSpell'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Spell)',
            'matches'  => $npcAbilities->getMatches(),
            'file'     => 'spell',
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
            $found['npcSpell']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_spellsfound', $npcAbilities->getMatches(), $maxResults);
            $found['npcSpell']['params']['_truncated'] = 1;
        }
    }
}

// 25 Spells (Misc + GM)
if ($searchMask & 0x1000000)
{
    $conditions = array(
        ['s.name_loc'.User::$localeId, $query],
        ['OR', ['s.typeCat', [0, -9]], ['s.cuFlags', SPELL_CU_EXCLUDE_CATEGORY_SEARCH, '&']],
        $maxResults
    );

    $misc = new SpellList($conditions);

    if ($data = $misc->getListviewData())
    {
        $misc->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        while ($misc->iterate())
            $data[$misc->id]['param1'] = '"'.strToLower($misc->getField('iconString')).'"';

        $found['spell'] = array(
            'type'     => TYPE_SPELL,
            'appendix' => ' (Spell)',
            'matches'  => $misc->getMatches(),
            'file'     => 'spell',
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
            $found['spell']['params']['note'] = '$'.sprintf(Util::$narrowResultString, 'LANG.lvnote_spellsfound', $misc->getMatches(), $maxResults);
            $found['spell']['params']['_truncated'] = 1;
        }
    }
}

// 26 Characters
// if ($searchMask & 0x2000000)

// 27 Guilds
// if ($searchMask & 0x4000000)

/*
    !note! dear reader, if you ever try to generate a string, that is to be evaled by JS, NEVER EVER terminate with a \n
    $totalHoursWasted +=2;
*/
if ($searchMask & SEARCH_TYPE_JSON)
{
/*
    todo (med):
    &wt=21:134:20:170:77:117:119:96:103&wtv=100:96:41:41:39:32:32:31:29
    additional url-parameter 'wt':ratingId/statId; 'wtv':applyPct (dafault 0)
    search for items with these stats (name becomes optional)
    how the fuck should i modify item_template_addon to accomodate this search behaviour... x_X
    - is it possible to use space separated integers and not cause the search to take forever to execute
    - one column per mod is probably the most efficient way to search but a pain to look at .. there are at least 150 after all
*/
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
    die ('["'.Util::jsEscape($query)."\", [\n".$outItems."],[\n".$outSets.']]');
}
else if ($searchMask & SEARCH_TYPE_OPEN)
{
    // this one is funny: we want 10 results, ideally equally destributed over each type
    $foundTotal = 0;
    $maxResults = 10;
    $names      = [];
    $info       = [];

    foreach ($found as $tmp)
        $foundTotal += $tmp['matches'];

    if (!$foundTotal)
        exit('["'.Util::jsEscape($query).'", []]');

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
        $typeId = key(array_pop($_));

        header("Location: ?".$type.'='.$typeId);
        die();
    }

    $vars = array(
        'title' => $search.' - '.Lang::$search['search'],
        'tab'   => 0,                                       // tabId 0: Database for g_initHeader($tab)
        'reqJS' => [array('path' => 'template/js/swfobject.js', 'conditional' => false)]
    );

    $smarty->updatePageVars($vars);
    $smarty->assign('lang', array_merge(Lang::$main, Lang::$search));
    $smarty->assign('found', $found);
    $smarty->assign('lvData', $jsGlobals);
    $smarty->assign('search', $search);

    $smarty->display('search.tpl');
}

?>
