<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

/*  Types
    Type 1 => NPC
    Type 2 => GameObject
    Type 3 => Items
    Type 4 => Item Sets
    Type 5 => Quests
    Type 6 => Spells
    Type 7 => Zones
    Type 8 => Factions
    Type 9 => Pets
    Type 10 => Achievement
    Type 11 => Title
    Type 12 => Event
    Type 13 => Class
    Type 14 => Race
    Type 15 => Skill
    Type 17 => Currency
*/

// Ajax can't handle debug, force to false
$AoWoWconf['debug'] = false;

header("Content-type: text/javascript");

// Receives requests from at least 3 characters (although vovhede and 1 character)
$_query = Util::sqlEscape($_GET['search']);
$_type  = isset($_GET['type']) ? (1 << intVal($_GET['type'])) : 0xFFFF;

if (strlen($_query) < 3)
	exit('["", []]');

echo "[\"".str_replace('"', '\"', $_query)."\", [\n";

// Item Comparison search
$foundItems = [];
$foundSets = [];
$pieceAssoc = [];
if ($_type & 0x10) {

    $rows = DB::Aowow()->Select('
        SELECT
            id,
            refSetId as idbak,
            CONCAT(7 - quality, ?#) as name,
            minlevel,
            maxlevel,
            contentGroup as note,
            type,
            IF(heroic=1, "true", "false") as heroic,
            classMask as reqclass,
            item1, item2, item3, item4, item5,
            item6, item7, item8, item9, item10
        FROM
            ?_itemset
        WHERE
            ?# LIKE ?s;',
        'name_loc'.User::$localeId,
        'name_loc'.User::$localeId,
        '%'.$_query.'%'
    );

    // parse items, create class-array

    foreach ($rows as $row)
    {
        $row['pieces'] = [];
        for ($i=1; $i<=10; $i++)
        {
            if ($row['item'.$i])
            {
                $foundItems[] = $row['item'.$i];
                $row['pieces'][] = $row['item'.$i];
                $pieceAssoc[$row['item'.$i]] = $row['id'];
                unset($row['item'.$i]);
            }
        }
        $row['classes'] = [];
        for ($i = 1; $i < 12; $i++)
            if ($row['reqclass'] & (1 << $i))
                $row['classes'][] = $i + 1;

        unset($row['classMask']);
        $foundSets[] = $row;
    }
}
if ($_type & 0x18) {                                         // 3 | 4
    $conditions = array(
        array('i.class', [2, 4]),
        empty($foundItems) ? array(User::$localeId ? 'name_loc'.User::$localeId : 'name', $_query) : array('i.entry', $foundItems)
    );
    $iList = new ItemList($conditions);

    $items = [];
    foreach ($iList->container as $id => $item)
    {
        $item->getJsonStats($pieceAssoc);

        $stats = [];
        foreach ($item->json as $k => $v)
        {
            if (!$v && $k != 'classs' && $k != 'subclass')
                continue;

            $stats[] = is_numeric($v) || $v[0] == "{" ? '"'.$k.'":'.$v.'' : '"'.$k.'":"'.$v.'"';
        }

        foreach ($item->itemMods as $k => $v)
            $stats[] = '"'.Util::$itemMods[$k].'":'.$v.'';

        $items[$id] =  "\t{".implode(',', $stats)."}";
    }
    echo implode(",\n", $items)."\n],[\n";

    $i = 0;
    foreach ($foundSets as $single)
    {
        $set = [];
        foreach ($single as $key => $value)
        {
            if ((is_numeric($value) && $value == 0) || $value === "false")
                continue;

            if (is_array($value))
                $value = "[".implode(',',$value)."]";

            $set[] = is_numeric($value) || $value[0] == "[" ? '"'.$key.'":'.str_replace('"', '\"', $value).'' : '"'.$key.'":"'.str_replace('"', '\"', $value).'"';
        }
        echo "\t{".implode(',', $set)."}";
        echo ($i < count($foundSets) - 1) ? ",\n" : "\n";
        $i++;
    }
echo "]]";
exit();
}
/*
// Ищем вещи:

$rows = $DB->select('
	SELECT i.entry, ?# as name, a.iconname, i.quality
	FROM ?_icons a, item_template i{, ?# l}
	WHERE
		?# LIKE ?
		AND a.id = i.displayid
		{ AND i.entry = l.?# }
	ORDER BY i.quality DESC, ?#
	LIMIT 3
	',
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId,	// SELECT
	User::$localeId == 0 ? DBSIMPLE_SKIP : 'locales_item',			// FROM
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId,	// WHERE1
	$_query,
	User::$localeId == 0 ? DBSIMPLE_SKIP : 'entry',					// WHERE2
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId	// ORDER
);

foreach($rows as $i => $row)
	$found[$row['name'].' (Item)'] = array(
		'type'		=> 3,
		'entry'		=> $row['entry'],
		'iconname'	=> $row['iconname'],
		'quality'	=> $row['quality']
	);

// Ищем объекты:
$rows = $DB->select('
	SELECT entry, ?# as name
	FROM ?#
	WHERE ?# LIKE ?
	ORDER BY ?#
	LIMIT 3
	',
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId,			// SELECT
	User::$localeId == 0 ? 'gameobject_template' : 'locales_gameobject',	// FROM
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId,			// WHERE1
	$_query,
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId			// ORDER
);

foreach($rows as $i => $row)
	$found[$row['name'].' (Object)'] = array(
		'type' => 2,
		'entry'=>$row['entry'],
	);

// Ищем квесты:
$rows = $DB->select('
	SELECT q.entry, ?# as Title, q.RequiredRaces
	FROM quest_template q {, ?# l}
	WHERE
		(?# LIKE ?)
		{AND (q.entry=l.?#)}
	ORDER BY ?#
	LIMIT 3
	',
	User::$localeId == 0 ? 'Title' : 'Title_loc'.User::$localeId,	// SELECT
	User::$localeId == 0 ? DBSIMPLE_SKIP : 'locales_quest',				// FROM
	User::$localeId == 0 ? 'Title' : 'Title_loc'.User::$localeId,	// WHERE1
	$_query,
	User::$localeId == 0 ? DBSIMPLE_SKIP : 'entry',						// WHERE2
	User::$localeId == 0 ? 'Title' : 'Title_loc'.User::$localeId	// ORDER
);

foreach($rows as $i => $row)
	$found[$row['Title'].' (Quest)'] = array(
		'type' => 5,
		'entry'=> $row['entry'],
		'side' => factionByRaceMask($row['RequiredRaces'])
	);

// Ищем creature:
$rows = $DB->select('
	SELECT entry, ?# as name
	FROM ?#
	WHERE ?# LIKE ?
	ORDER BY ?#
	LIMIT 3
	',
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId,		// SELECT
	User::$localeId == 0 ? 'creature_template' : 'locales_creature',	// FROM
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId,		// WHERE1
	$_query,
	User::$localeId == 0 ? 'name' : 'name_loc'.User::$localeId		// ORDER
);

foreach($rows as $i => $row)
	$found[$row['name'].' (NPC)'] = array(
		'type' => 1,
		'entry' => $row['entry']
	);

// Если ничего не найдено...
if(!isset($found))
{
	echo ']]';
	exit;
}

//ksort($found);

$found = array_slice($found, 0, 10);

$i=0;
foreach($found as $name => $fitem)
{
	echo '"'.str_replace('"', '\"', $name).'"';
	if($i<count($found)-1)
		echo ', ';
	$i++;
}

echo '], [], [], [], [], [], [';

$i=0;
foreach($found as $name => $fitem)
{
	echo '['.$fitem['type'].', '.$fitem['entry'];
	if(isset($fitem['iconname'])) echo ', "'.$fitem['iconname'].'"';
	if(isset($fitem['quality'])) echo ", ".$fitem['quality'];
	if(isset($fitem['side'])) echo ", ".$fitem['side'];
	echo ']';
	if($i<count($found)-1)
		echo ', ';
	$i++;
}

echo ']]';
*/
?>