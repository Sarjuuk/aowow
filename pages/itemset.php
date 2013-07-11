<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.community.php';

$id   = intVal($pageParam);
$path = [0, 2];

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_ITEMSET, $id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $iSet = new ItemsetList(array(['id', $id]));
    if ($iSet->error)
        $smarty->notFound(Lang::$game['itemset']);

    $iSet->reset();

    $ta   = $iSet->getField('contentGroup');
    $ty   = $iSet->getField('type');
    $ev   = $iSet->getField('holidayId');
    $sk   = $iSet->getField('skillId');
    $mask = $iSet->getField('classMask');
    $rLvl = $iSet->getField('reqLevel');
    $name = $iSet->getField('name', true);
    $cnt  = count($iSet->getField('pieces'));
    $unav = $iSet->getField('cuFlags') & CUSTOM_UNAVAILABLE;

    $infobox = [];
    // unavailable (todo (low): set data)
    if ($unav)
        $infobox[] = Lang::$main['unavailable'];

    // holiday
    if ($ev)
        $infobox[] = Lang::$game['eventShort'].Lang::$colon.'[url=?event='.$ev.']'.WorldEventList::getName($ev).'[/url]';

    // itemLevel
    if ($min = $iSet->getField('minLevel'))
    {
        $foo = Lang::$game['level'].Lang::$colon.$min;
        $max = $iSet->getField('maxLevel');

        if ($min < $max)
            $foo .= ' - '.$max;

        $infobox[] = $foo;
    }

    // class
    if ($mask)
    {
        $foo = [];
        for ($i = 0; $i < 11; $i++)
            if ($mask & (1 << $i))
                $foo[] = (!fMod(count($foo) + 1, 3) ? '\n' : null) . '[class='.($i + 1).']';

        $t = count($foo) == 1 ? Lang::$game['class'] : Lang::$game['classes'];
        $infobox[] = Util::ucFirst($t).Lang::$colon.implode(', ', $foo);
    }

    // required level
    if ($rLvl)
        $infobox[] = sprintf(Lang::$game['reqLevel'], $rLvl);

    // type
    if ($ty)
        $infobox[] = Lang::$game['type'].lang::$colon.Lang::$itemset['types'][$ty];

    // tag
    if ($ta)
        $infobox[] = Lang::$itemset['_tag'].Lang::$colon.'[url=?itemsets&filter=ta='.$ta.']'.Lang::$itemset['notes'][$ta].'[/url]';

    // pieces + Summary
    $pieces  = [];
    $eqList  = [];
    $compare = [];
    $iList = new ItemList(array(['i.entry', array_keys($iSet->pieceToSet)]));
    $data  = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);
    foreach ($data as $iId => $item)
    {
        while ($iList->id != $iId)
            $iList->iterate();

        $slot = $iList->getField('InventoryType');
        $disp = $iList->getField('displayid');
        if ($slot && $disp)
            $eqList[] = [$slot, $disp];

        $compare[] = $iId;

        $pieces[] = array(
            'id'      => $iId,
            'name'    => $iList->getField('name', true),
            'quality' => $iList->getField('Quality'),
            'icon'    => $iList->getField('icon'),
            'json'    => json_encode($item, JSON_NUMERIC_CHECK)
        );
    }

    // spells
    $foo    = [];
    $spells = [];
    for ($i = 1; $i < 9; $i++)
    {
        $spl = $iSet->getField('spell'.$i);
        $qty = $iSet->getField('bonus'.$i);

        if ($spl && $qty)
        {
            $foo[]    = $spl;
            $spells[] = array(                              // cant use spell as index, would change order
                'id'    => $spl,
                'bonus' => $qty,
                'desc'  => ''
            );
        }
    }

    // sort by required pieces ASC
    usort($spells, function($a, $b) {
        if ($a['bonus'] == $b['bonus'])
            return 0;

        return ($a['bonus'] > $b['bonus']) ? 1 : -1;
    });

    $setSpells = new SpellList(array(['s.id', $foo]));
    foreach ($spells as &$s)
    {
        while ($setSpells->id != $s['id'])
            $setSpells->iterate();

        $s['desc'] = $setSpells->parseText('description');
    }

    // path
    if ($mask)
    {
        for ($i = 0; $i < 11; $i++)
        {
            if ($mask & (1 << $i))
            {
                if ($mask == (1 << $i))                     // only bit set, add path
                    $path[] = $i + 1;

                break;                                      // break anyway (cant set multiple classes)
            }
        }
    }

    $skill = '';
    if ($sk)
    {
        // todo (med): kill this Lang::monstrosity with Skills
        $spellLink = sprintf('<a href="?spells=11.%s">%s</a> (%s)', $sk, Lang::$spell['cat'][11][$sk][0], $iSet->getField('skillLevel'));
        $skill = ' &ndash; <small><b>'.sprintf(Lang::$game['requires'], $spellLink).'</b></small>';
    }

    $pageData = array(
        'title'   => $name,                                 // for header
        'path'    => $path,
        'infobox' => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
        'pieces'  => $pieces,
        'spells'  => $spells,
        'view3D'  => json_encode($eqList, JSON_NUMERIC_CHECK),
        'compare' => array(
            'qty'   => $cnt,
            'items' => $compare,
            'level' => $rLvl
        ),
        'page'    => array(
            'name'        => $name,                         // for page content
            'id'          => $id,
            'bonusExt'    => $skill,
            'description' => $ta ? sprintf(Lang::$itemset['_desc'], $name, Lang::$itemset['notes'][$ta], $cnt) : sprintf(Lang::$itemset['_descTagless'], $name, $cnt),
            'unavailable' => $unav
        )
    );

    $iSet->addGlobalsToJscript($smarty, GLOBALINFO_RELATED);

    // related sets (priority: 1: similar tag + class; 2: has event; 3: no tag + similar type, 4: similar type + profession)
    $rel = [];

    if ($ta && count($path) == 3)
    {
        $rel[] = ['id', $id, '!'];
        $rel[] = ['classMask', 1 << (end($path) - 1), '&'];
        $rel[] = ['contentGroup', (int)$ta];
    }
    else if ($ev)
    {
        $rel[] = ['id', $id, '!'];
        $rel[] = ['holidayId', 0, '!'];
    }
    else if ($sk)
    {
        $rel[] = ['id', $id, '!'];
        $rel[] = ['contentGroup', 0];
        $rel[] = ['skillId', 0, '!'];
        $rel[] = ['type', $ty];
    }
    else if (!$ta && $ty)
    {
        $rel[] = ['id', $id, '!'];
        $rel[] = ['contentGroup', 0];
        $rel[] = ['type', $ty];
        $rel[] = ['skillId', 0];
    }

    if ($rel)
    {
        $relSets = new ItemsetList($rel);
        if (!$relSets->error)
        {
            $pageData['related'] = array(
                'data'   => $relSets->getListviewData(),
                'params' => array(
                    'id'   => 'see-also',
                    'name' => '$LANG.tab_seealso',
                    'tabs' => '$tabsRelated'
                )
            );

            $mask = $relSets->hasDiffFields(['classMask']);
            if (!$mask)
                $pageData['related']['params']['hiddenCols'] = "$['classes']";

            $relSets->addGlobalsToJscript($smarty);
        }
    }

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars(array(
	'title'  => $pageData['title']." - ".Util::ucfirst(Lang::$game['itemset']),
	'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
	'tab'    => 0,                                          // for g_initHeader($tab)
	'type'   => TYPE_ITEMSET,                               // 4:Itemset
	'typeId' => $id,
    'reqJS'  => array(
        array('path' => 'template/js/Summary.js',   'conditional' => false),
        array('path' => 'template/js/swfobject.js', 'conditional' => false)
    )
));


$smarty->assign('community', CommunityContent::getAll(TYPE_ITEMSET, $id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$itemset));
$smarty->assign('lvData', $pageData);
$smarty->display('itemset.tpl');

?>
