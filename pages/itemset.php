<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id  = intVal($pageParam);
$path = [0, 2];

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_ITEMSET, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $iSet = new ItemsetList(array(['id', $_id]));
    if ($iSet->error)
        $smarty->notFound(Lang::$game['itemset'], $_id);

    $_ta  = $iSet->getField('contentGroup');
    $_ty  = $iSet->getField('type');
    $_ev  = $iSet->getField('holidayId');
    $_sk  = $iSet->getField('skillId');
    $_cl  = $iSet->getField('classMask');
    $_lvl = $iSet->getField('reqLevel');
    $_na  = $iSet->getField('name', true);
    $_cnt = count($iSet->getField('pieces'));

    /***********/
    /* Infobox */
    /***********/

    $infobox = [];
    // unavailable (todo (low): set data)
    if ($iSet->getField('cuFlags') & CUSTOM_UNAVAILABLE)
        $infobox[] = Lang::$main['unavailable'];

    // holiday
    if ($_ev)
        $infobox[] = Lang::$game['eventShort'].Lang::$colon.'[url=?event='.$_ev.']'.WorldEventList::getName($_ev).'[/url]';

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
    if ($_cl)
    {
        $foo = [];
        for ($i = 0; $i < 11; $i++)
            if ($_cl & (1 << $i))
                $foo[] = (!fMod(count($foo) + 1, 3) ? '\n' : null) . '[class='.($i + 1).']';

        $t = count($foo) == 1 ? Lang::$game['class'] : Lang::$game['classes'];
        $infobox[] = Util::ucFirst($t).Lang::$colon.implode(', ', $foo);
    }

    // required level
    if ($_lvl)
        $infobox[] = sprintf(Lang::$game['reqLevel'], $_lvl);

    // type
    if ($_ty)
        $infobox[] = Lang::$game['type'].lang::$colon.Lang::$itemset['types'][$_ty];

    // tag
    if ($_ta)
        $infobox[] = Lang::$itemset['_tag'].Lang::$colon.'[url=?itemsets&filter=ta='.$_ta.']'.Lang::$itemset['notes'][$_ta].'[/url]';

    /****************/
    /* Main Content */
    /****************/

    // pieces + Summary
    $pieces  = [];
    $eqList  = [];
    $compare = [];

    if (!$iSet->pieceToSet)
        $cnd = [0];
    else
        $cnd = ['i.id', array_keys($iSet->pieceToSet)];

    $iList   = new ItemList(array($cnd));
    $data    = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);
    foreach ($iList->iterate() as $itemId => $__)
    {
        if (empty($data[$itemId]))
            continue;

        $slot = $iList->getField('slot');
        $disp = $iList->getField('displayId');
        if ($slot && $disp)
            $eqList[] = [$slot, $disp];

        $compare[] = $itemId;

        $pieces[] = array(
            'id'      => $itemId,
            'name'    => $iList->getField('name', true),
            'quality' => $iList->getField('quality'),
            'icon'    => $iList->getField('iconString'),
            'json'    => json_encode($data[$itemId], JSON_NUMERIC_CHECK)
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
    foreach ($setSpells->iterate() as $spellId => $__)
    {
        foreach ($spells as &$s)
        {
            if ($spellId != $s['id'])
                continue;

            $s['desc'] = $setSpells->parseText('description')[0];
        }
    }

    // path
    if ($_cl)
    {
        for ($i = 0; $i < 11; $i++)
        {
            if ($_cl & (1 << $i))
            {
                if ($_cl == (1 << $i))                      // only bit set, add path
                    $path[] = $i + 1;

                break;                                      // break anyway (cant set multiple classes)
            }
        }
    }

    $skill = '';
    if ($_sk)
    {
        $spellLink = sprintf('<a href="?spells=11.%s">%s</a> (%s)', $_sk, Lang::$spell['cat'][11][$_sk][0], $iSet->getField('skillLevel'));
        $skill = ' &ndash; <small><b>'.sprintf(Lang::$game['requires'], $spellLink).'</b></small>';
    }

    // menuId 2: Itemset  g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page'    => array(
            'name'        => $_na,                          // for page content
            'bonusExt'    => $skill,
            'description' => $_ta ? sprintf(Lang::$itemset['_desc'], $_na, Lang::$itemset['notes'][$_ta], $_cnt) : sprintf(Lang::$itemset['_descTagless'], $_na, $_cnt),
            'unavailable' => (bool)($iSet->getField('cuFlags') & CUSTOM_UNAVAILABLE),
            'infobox'     => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
            'title'       => $_na." - ".Util::ucfirst(Lang::$game['itemset']),
            'path'        => json_encode($path, JSON_NUMERIC_CHECK),
            'tab'         => 0,
            'type'        => TYPE_ITEMSET,
            'typeId'      => $_id,
            'reqJS'       => array(
                'static/js/Summary.js',
                'static/js/swfobject.js'
            ),
            'pieces'      => $pieces,
            'spells'      => $spells,
            'redButtons'  => array(
                BUTTON_WOWHEAD => $_id > 0,                 // bool only
                BUTTON_LINKS   => ['color' => '', 'linkId' => ''],
                BUTTON_VIEW3D  => ['type' => TYPE_ITEMSET, 'typeId' => $_id, 'equipList' => $eqList],
                BUTTON_COMPARE => ['eqList' => implode(':', $compare), 'qty' => $_cnt]
            ),
            'compare'     => array(
                'qty'   => $_cnt,
                'items' => $compare,
                'level' => $_lvl
            ),
        ),
        'relTabs' => []
    );

    $iSet->addGlobalsToJscript($smarty);

    /**************/
    /* Extra Tabs */
    /**************/

    // related sets (priority: 1: similar tag + class; 2: has event; 3: no tag + similar type, 4: similar type + profession)
    $rel = [];

    if ($_ta && count($path) == 3)
    {
        $rel[] = ['id', $_id, '!'];
        $rel[] = ['classMask', 1 << (end($path) - 1), '&'];
        $rel[] = ['contentGroup', (int)$_ta];
    }
    else if ($_ev)
    {
        $rel[] = ['id', $_id, '!'];
        $rel[] = ['holidayId', 0, '!'];
    }
    else if ($_sk)
    {
        $rel[] = ['id', $_id, '!'];
        $rel[] = ['contentGroup', 0];
        $rel[] = ['skillId', 0, '!'];
        $rel[] = ['type', $_ty];
    }
    else if (!$_ta && $_ty)
    {
        $rel[] = ['id', $_id, '!'];
        $rel[] = ['contentGroup', 0];
        $rel[] = ['type', $_ty];
        $rel[] = ['skillId', 0];
    }

    if ($rel)
    {
        $relSets = new ItemsetList($rel);
        if (!$relSets->error)
        {
            $pageData['relTabs'][] = array(
                'file'   => 'itemset',
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


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_ITEMSET, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$itemset, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('itemset.tpl');

?>
