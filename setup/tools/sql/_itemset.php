<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    this will probably screw the order of your set-pieces and will not result in the same virtualIds like wowhead, but
    they are years beyond our content anyway, so what gives...

    since there are some unused itemsets and and items flying around in a default database this script will create about 20 sets more than you'd expect.

    and i have no idea how to merge the prefixes/suffixes for wotlk-raidsets and arena-sets in gereral onto the name.. at least not for all locales and i'll be damned if i have to skip one
*/

/*
    ALTER TABLE `aowow_itemset` ADD COLUMN `npieces`  tinyint(3) NOT NULL AFTER `bonusParsed`;
    UPDATE `aowow_itemset`SET npieces = (
        IF(item1 > 0, 1, 0) + IF(item2 > 0, 1, 0) + IF(item3 > 0, 1, 0) + IF(item4 > 0, 1, 0) + IF(item5 > 0, 1, 0) +
        IF(item6 > 0, 1, 0) + IF(item7 > 0, 1, 0) + IF(item8 > 0, 1, 0) + IF(item9 > 0, 1, 0) + IF(item10 > 0, 1, 0)
    );
*/

// script terminates self unexpectedly.. i don't know why..; split calls via ajax
if (!isset($_GET['setId']))
{
    $setIds = DB::Aowow()->selectCol('SELECT id FROM dbc.itemset');
    DB::Aowow()->query('TRUNCATE aowow_itemset');


?>

<html>
    <head>
        <script type="text/javascript">
            var
                httpRequest,
                setIds = <?=json_encode($setIds, JSON_NUMERIC_CHECK);?>,
                url    = document.URL,
                active = false;

            function ge(el) {
                return document.getElementById(el);
            }

            function ce(str) {
                return document.createElement(str);
            }

            Element.prototype.ae = function(el) {
                this.appendChild(el);
            }

            function makeRequest(url) {
                httpRequest.open('GET', url);
                httpRequest.send();
            }

            function toggle() {
                active = !active;
                ge('toggle').value = active ? 'Stop' : 'Start';

                if (active)
                    run();
            }

            function run() {
                ge('count').innerHTML = setIds.length;

                if (id = setIds.shift()) {
                    if (active)
                        makeRequest(url + '&setId=' + id);

                    ge('dummy').scrollIntoView();
                }
                else
                    alert('we\'re done!');
            };

            function print() {
                if (httpRequest.readyState === 4) {
                    if (httpRequest.status === 200) {

                        var
                            o = ge('out'),
                            p = ce('pre');

                        p.innerHTML = httpRequest.responseText;
                        o.appendChild(p);

                        if (active)
                            run();
                    }
                    else {
                        alert('There was a problem with the request, canceling...');
                    }
                }
            }

            if (window.XMLHttpRequest) { // Mozilla, Safari, ...
                httpRequest = new XMLHttpRequest();
            }
            else if (window.ActiveXObject) { // IE
                try {
                    httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
                }
                catch (e) {
                    try {
                        httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    catch (e) {}
                }
            }

            if (!httpRequest) {
                alert('Giving up :( Cannot create an XMLHTTP instance');
            }
            else {
                httpRequest.onreadystatechange = print;
            }

            window.onload = function() {
                ge('max').innerHTML = setIds.length;
                ge('count').innerHTML = setIds.length;

                ge('toggle').onclick = toggle;
            };
        </script>
    </head>
    <body>
        <div id="out"></div>
        <span id="dummy" />
        <br><br>
        <pre style="position:fixed; right:50px; bottom:10px; margin:5px"><input type="button" id="toggle" value="start" />&nbsp;<span id="count"></span> / <span id="max"></span></pre>
    </body>
</html>

<?php

    die();
}

$locales      = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
$query        = [];
$virtualId    = 0;
$setToHoliday = array (
    761 => 141,                                             // Winterveil
    762 => 372,                                             // Brewfest
    785 => 341,                                             // Midsummer
    812 => 181,                                             // Noblegarden
);

// tags where refId == virtualId
// in pve sets are not recycled beyond the contentGroup
$tagsById = array(
    1  => [181,182,183,184,185,186,187,188,189],                                                    // "Dungeon Set 1"
    2  => [511,512,513,514,515,516,517,518,519],                                                    // "Dungeon Set 2"
    3  => [201,202,203,204,205,206,207,208,209],                                                    // "Tier 1 Raid Set"
    4  => [210,211,212,213,214,215,216,217,218],                                                    // "Tier 2 Raid Set"
    5  => [521,523,524,525,526,527,528,529,530],                                                    // "Tier 3 Raid Set"
    6  => [522,537,538,539,540,541,542,543,544,545,546,547,548,549,550,551,697,718],                // "Level 60 PvP Rare Set"
    7  => [281,282,301,341,342,343,344,345,346,347,348,361,362,381,382,401],                        // "Level 60 PvP Rare Set (Old)"
    8  => [383,384,386,387,388,389,390,391,392,393,394,395,396,397,398,402,717,698],                // "Level 60 PvP Epic Set"
    9  => [494,495,498,500,502,504,506,508,510],                                                    // "Ruins of Ahn'Qiraj Set"
    10 => [493,496,497,499,501,503,505,507,509],                                                    // "Temple of Ahn'Qiraj Set"
    11 => [477,480,474,475,476,478,479,481,482],                                                    // "Zul'Gurub Set"
    12 => [621,624,625,626,631,632,633,638,639,640,645,648,651,654,655,663,664],                    // "Tier 4 Raid Set"
    13 => [622,627,628,629,634,635,636,641,642,643,646,649,652,656,657,665,666],                    // "Tier 5 Raid Set",
    14 => [620,623,630,637,644,647,650,653,658,659,660,661,662],                                    // "Dungeon Set 3",
    15 => [467,468,469,470,471,472,473,483,484,485,486,487,488, 908],                               // "Arathi Basin Set",
    16 => [587,588,589,590,591,592,593,594,595,596,597,598,599,600,601,602,603,604,605,606,607,608,609,610,688,689,691,692,693,694,695,696],                            // "Level 70 PvP Rare Set",
    18 => [668,669,670,671,672,673,674,675,676,677,678,679,680,681,682,683,684],                    // "Tier 6 Raid Set",
    21 => [738,739,740,741,742,743,744,745,746,747,748,749,750,751,752],                            // "Level 70 PvP Rare Set 2",
    23 => [795,805,804,803,802,801,800,799,798,797,796,794,793,792,791,790,789,788,787],            // "Tier 7 Raid Set",
    25 => [838,837,836,835,834,833,832,831,830,829,828,827,826,825,824,823,822,821,820],            // "Tier 8 Raid Set",
    27 => [880,879,878,877,876,875,874,873,872,871,870,869,868,867,866,865,864,863,862,861,860,859,858,857,856,855,854,853,852,851,850,849,848,847,846,845,844,843],    // "Tier 9 Raid Set",
    29 => [901,900,899,898,897,896,895,894,893,892,891,890,889,888,887,886,885,884,883]             // "Tier 10 Raid Set",
);

// well .. fuck
$tagsByNamePart = array(
    17 => ['gladiator'],                                    // "Arena Season 1 Set",
    19 => ['merciless'],                                    // "Arena Season 2 Set",
    20 => ['vengeful'],                                     // "Arena Season 3 Set",
    22 => ['brutal'],                                       // "Arena Season 4 Set",
    24 => ['deadly', 'hateful', 'savage'],                  // "Arena Season 5 Set",
    26 => ['furious'],                                      // "Arena Season 6 Set",
    28 => ['relentless'],                                   // "Arena Season 7 Set",
    30 => ['wrathful']                                      // "Arena Season 8 Set",
);

$setId = intVal($_GET['setId']);
if (!$setId)
    die($_GET['setId']." is not a valid Id");

$_ = DB::Aowow()->selectCell('SELECT MIN(id) FROM aowow_itemset');

if ($_ < 0)
    $virtualId = $_;

$set = DB::Aowow()->selectRow('SELECT *, id AS ARRAY_KEY FROM dbc.itemset WHERE id = ?d', $setId);
$id = $set['Id'];

$holiday = isset($setToHoliday[$id]) ? $setToHoliday[$id] : 0;

$pieces = DB::Aowow()->select('SELECT *, entry AS ARRAY_KEY FROM world.item_template WHERE itemset = ?d ORDER BY itemLevel ASC', $id);

$type = $classMask = 0;
$hasRing = false;
foreach ($pieces as $piece)
{
    $classMask |= $piece['AllowableClass'];

    // skip cloaks, they mess with armor classes
    if ($piece['InventoryType'] == 16)
        continue;

    // skip event-sets
    if ($piece['Quality'] == 1)
        continue;

    if ($piece['class'] == 2 && $piece['subclass'] == 0)                    // 1H-Axe
        $type = 8;
    else if ($piece['class'] == 2 && $piece['subclass'] == 4)               // 1H-Mace
        $type = 9;
    else if ($piece['class'] == 2 && $piece['subclass'] == 7)               // 1H-Sword
        $type = 10;
    else if ($piece['class'] == 2 && $piece['subclass'] == 13)              // Fist Weapon
        $type = 7;
    else if ($piece['class'] == 2 && $piece['subclass'] == 15)              // Dagger
        $type = 5;

    if ($type)
        break;

    if ($piece['class'] == 4 && $piece['InventoryType'] == 12)              // trinket
        $type = 11;
    else if ($piece['class'] == 4 && $piece['InventoryType'] == 2)          // amulet
        $type = 12;
    else if ($piece['class'] == 4 && $piece['subclass'] != 0)               // 'armor' set
        $type = $piece['subclass'];

    if ($piece['class'] == 4 && $piece['InventoryType'] == 11)              // contains ring
        $hasRing = true;
}

$classMask &= CLASS_MASK_ALL;                       // clamp to available classes..

if (!$classMask || $classMask == CLASS_MASK_ALL)    // available to all classes
    $classMask = 0;

if ($hasRing && !$type)
    $type = 6;                                                              // pure ring-set

$spells = $mods = $descText = $name = $gains = [];

// if slots conflict, group by itemlevel
// assume, that all items in multiSets have the same Itemlevel per group
// they don't .. consider quality!
$base     = 0;
$multiSet = false;
foreach ($pieces as $pId => $piece)
{
    // only armor and not a trinket, ring or neck, rare or higher
    if ($piece['class'] != 4 || $piece['subclass'] == 0 || $piece['Quality'] < 3)
        continue;

    if (!$base)
    {
        $base = $piece['InventoryType'];
        continue;
    }

    if ($base == $piece['InventoryType'])
    {
        $multiSet = true;
        break;
    }
}

for ($i = 1; $i < 9; $i++)
    if ($set['spellId'.$i] > 0)
        $spells[] = (int)$set['spellId'.$i];

$bonusSpells = new SpellList(array(['s.id', $spells]));

$mods = $bonusSpells->getStatGain();

for ($i = 1; $i < 9; $i++)
    if ($set['itemCount'.$i] > 0)
        $gains[$set['itemCount'.$i]] = $mods[$set['spellId'.$i]];

foreach ($locales as $loc)
{
    User::useLocale($loc);

    $name[$loc] = '"'.Util::sqlEscape(Util::localizedString($set, 'name')).'"';

    foreach ($bonusSpells->iterate() as $__)
        @$descText[$loc] .= $bonusSpells->parseText()[0]."\n";

    $descText[$loc] = '"'.Util::sqlEscape($descText[$loc]).'"';
}

$items      = [];
$max = $req = $min = $quality = 0;
if ($holiday || !$multiSet)
{
    $row = [];

    $heroic = false;
    foreach ($pieces as $pId => $piece)
    {
        if ($piece['Quality'] > $quality)
            $quality = $piece['Quality'];

        if ($piece['Flags'] & ITEM_FLAG_HEROIC)
            $heroic = true;

        if ($piece['RequiredLevel'] > $req)
            $req = $piece['RequiredLevel'];

        if (!$min || $piece['ItemLevel'] < $min)
            $min = $piece['ItemLevel'];

        if ($piece['ItemLevel'] > $max)
            $max = $piece['ItemLevel'];

        if (isset($items[$piece['InventoryType']]))
        {
            if ($piece['class'] != 4 || $piece['subclass'] == 0)    // jewelry, insert anyway
                $items[$piece['InventoryType'].$pId] = $pId;
            else
                echo "<pre>set: ".$id." - conflict between item: ".$items[$piece['InventoryType']]." and item: ".$pId." choosing lower Id</pre>" ;
        }
        else
            $items[$piece['InventoryType']] = $pId;
    }

    while (count($items) < 10)
        $items[] = 0;

    $note = 0;
    foreach ($tagsById as $tag => $sets)
    {
        if (in_array($id, $sets))
        {
            $note = $tag;
            break;
        }
    }

    $row[] = $id;
    $row[] = $id;         //refSetId
    $row = array_merge($row, $name, $items);
    for ($i = 1; $i < 9; $i++)
        $row[] = $set['spellId'.$i];
    for ($i = 1; $i < 9; $i++)
        $row[] = $set['itemCount'.$i];
    $row = array_merge($row, $descText);
    $row[] = !empty($gains) ? '"'.serialize($gains).'"' : '';
    $row[] = $min;
    $row[] = $max;
    $row[] = $req;
    $row[] = $classMask;
    $row[] = $heroic ? 1 : 0;
    $row[] = $quality;
    $row[] = $type;
    $row[] = $note;             // contentGroup
    $row[] = $holiday;
    $row[] = $set['reqSkillLineId'];
    $row[] = $set['reqSkillLevel'];

    $query[] = '('.implode(', ', $row).')';
    echo "<pre>set: ".$id." done</pre>";
}
else
{
    $min = $max = $req = $heroic = $tmp = $vIds = [];

    // map virtual Ids to itemlevel
    $j = 0;
    foreach ($pieces as $pId => $piece)
    {
        $key = $piece['Quality'].$piece['ItemLevel'];

        if (!isset($tmp[$key]))
            $tmp[$key] = $j++;

        $vId = $tmp[$key];

        if ($piece['Flags'] & ITEM_FLAG_HEROIC)
            $heroic[$vId] = 1;

        if (!isset($req[$vId]) || $piece['RequiredLevel'] > $req[$vId])
            $req[$vId] = $piece['RequiredLevel'];

        if (!isset($min[$vId]) || $piece['ItemLevel'] < $min[$vId])
            $min[$vId] = $piece['ItemLevel'];

        if (!isset($max[$vId]) || $piece['ItemLevel'] > $max[$vId])
            $max[$vId] = $piece['ItemLevel'];

        if (isset($items[$vId][$piece['InventoryType']]))
        {
            echo "<pre>set: ".$id." ilvl: ".$piece['ItemLevel']." - conflict between item: ".$items[$vId][$piece['InventoryType']]." and item: ".$pId." choosing lower Id</pre>" ;
            if ($items[$vId][$piece['InventoryType']] > $pId)
                $items[$vId][$piece['InventoryType']] = $pId;
        }
        else
            $items[$vId][$piece['InventoryType']] = $pId;
    }

    foreach ($items as &$list)
        while(count($list) < 10)
            $list[] = 0;

    $rId = $id;
    foreach ($items as $vId => $vSet)
    {

        if ($vId)
        {
            $id = --$virtualId;
            $vIds[] = $id;
        }

        $quality = 0;
        foreach ($vSet as $itm)
            if ($itm && $pieces[$itm]['Quality'] > $quality)
                $quality = $piece['Quality'];

        $note = 0;
        foreach ($tagsById as $tag => $sets)
        {
            if (in_array($rId, $sets))
            {
                $note = $tag;
                break;
            }
        }

        if (!$note)
        {
            foreach($tagsByNamePart as $tag => $strings)
            {
                foreach ($strings as $str)
                {
                    if (@stripos($pieces[reset($vSet)]['name'], $str) === 0)
                    {
                        $note = $tag;
                        break 2;
                    }
                }
            }
        }

        $row = [];

        $row[] = $id;
        $row[] = $rId;         //refSetId
        $row = array_merge($row, $name, $vSet);
        for ($i = 1; $i < 9; $i++)
            $row[] = $set['spellId'.$i];
        for ($i = 1; $i < 9; $i++)
            $row[] = $set['itemCount'.$i];
        $row = array_merge($row, $descText);
        $row[] = !empty($gains) ? "'".serialize($gains)."'" : '';
        $row[] = $min[$vId];
        $row[] = $max[$vId];
        $row[] = $req[$vId];
        $row[] = $classMask;
        $row[] = isset($heroic[$vId]) ? 1 : 0;
        $row[] = $quality;
        $row[] = $type;
        $row[] = $note;             // contentGroup
        $row[] = $holiday;
        $row[] = $set['reqSkillLineId'];
        $row[] = $set['reqSkillLevel'];

        $query[] = '('.implode(', ', $row).')';
    }
    echo "<pre>set: ".$rId." done ".($vIds ? "as (".$rId.", ".implode(', ', $vIds).")" : null)."</pre>";
}

DB::Aowow()->query('REPLACE INTO aowow_itemset VALUES '.implode(', ', $query));

?>
