<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


if (isset($_GET['xml']))
    die('unsupported, as i do not see the point');

require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_ITEM, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    $enh        = [];
    $itemString = $_id;

    if (isset($_GET['rand']))
    {
        $enh['rand'] = $_GET['rand'];
        $itemString .= 'r'.$_GET['rand'];
    }
    if (isset($_GET['ench']))
    {
        $enh['ench'] = $_GET['ench'];
        $itemString .= 'e'.$_GET['ench'];
    }
    if (isset($_GET['gems']))
    {
        $enh['gems'] = explode(':', $_GET['gems']);
        $itemString .= 'g'.str_replace(':', ',', $_GET['gems']);
    }
    if (isset($_GET['sock']))
    {
        $enh['sock'] = $_GET['sock'];
        $itemString .= 's';
    }

    $cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_ITEM, str_replace(':', ',', $itemString), -1, User::$localeId]);

    // output json for tooltips
    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $item = new ItemList(array(['i.id', $_id]));
        if ($item->error)
            die('$WowheadPower.registerItem(\''.$itemString.'\', '.User::$localeId.', {})');

        $item->renderTooltip($enh);
        $x .= '$WowheadPower.registerItem(\''.$itemString.'\', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($item->getField('name', true))."',\n";
        $x .= "\tquality: ".$item->getField('quality').",\n";
        $x .= "\ticon: '".urlencode($item->getField('iconString'))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($item->tooltip[$_id])."'\n";
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }
    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $item))
{
    $item = new ItemList(array(['i.id', $_id]));
    if ($item->error)
        $smarty->notFound(Lang::$game['item']);

    $item->addGlobalsToJscript($smarty, GLOBALINFO_EXTRA | GLOBALINFO_SELF);

    $_flags    = $item->getField('flags');
    $_slot     = $item->getField('slot');
    $_subClass = $item->getField('subClass');
    $_class    = $item->getField('class');

    /***********/
    /* Infobox */
    /***********/

    $quickInfo = Lang::getInfoBoxForFlags($item->getField('cuFlags'));

    if ($_slot)                                             // itemlevel
        $quickInfo[] = Lang::$game['level'].Lang::$colon.$item->getField('itemLevel');

    if ($_flags & ITEM_FLAG_ACCOUNTBOUND )                  // account-wide
        $quickInfo[] = Lang::$item['accountWide'];

    if ($si = $item->json[$_id]['side'])                    // side
        if ($si != 3)
            $quickInfo[] = Lang::$main['side'].Lang::$colon.'[span class='.($si == 1 ? 'alliance' : 'horde').'-icon]'.Lang::$game['si'][$si].'[/span]';

    // consumable / not consumable
    if (!$_slot)
    {
        $hasUse = false;
        for ($i = 1; $i < 6; $i++)
        {
            if ($item->getField('spellId'.$i) <= 0 || in_array($item->getField('spellTrigger'.$i), [1, 2]))
                continue;

            $hasUse = true;

            if ($item->getField('spellCharges'.$i) >= 0)
                continue;

            $tt = '[tooltip=tooltip_consumedonuse]'.Lang::$item['consumable'].'[/tooltip]';          // 2:Consommable    3:Verbrauchbar   6:Consumible   8:Расходуется
            break;
        }

        if ($hasUse)
            $quickInfo[] = isset($tt) ? $tt : '[tooltip=tooltip_notconsumedonuse]'.Lang::$item['nonConsumable'].'[/tooltip]';
    }

    if ($hId = $item->getField('holidayId'))                                                        // 3:Werkzeug   6:Herramienta   8:Инструмент   2:Outil
        if ($hName = DB::Aowow()->selectRow('SELECT * FROM ?_holidays WHERE id = ?d', $hId))
            $quickInfo[] = Lang::$game['eventShort'].Lang::$colon.'[url=?event='.$hId.']'.Util::localizedString($hName, 'name').'[/url]';

    if ($tId = $item->getField('totemCategory'))                                                    // 3:Werkzeug   6:Herramienta   8:Инструмент   2:Outil
        if ($tName = DB::Aowow()->selectRow('SELECT * FROM ?_totemCategory WHERE id = ?d', $tId))
            $quickInfo[] = Lang::$item['tool'].Lang::$colon.'[url=?items&filter=cr=91;crs='.$tId.';crv=0]'.Util::localizedString($tName, 'name').'[/url]';

    $cost = '';
    if ($_ = $item->getField('buyPrice'))
        $cost .= '[money='.$_.']';

    if ($_ = $item->getExtendedCost())
        foreach ($_ as $c => $qty)
            $cost .= '[currency='.$c.' amount='.$qty.']';

    if ($cost)
        $quickInfo[] = Lang::$item['cost'].Lang::$colon.$cost.'[color=q0] ('.Lang::$item['each'].')[/color]'; // 2:Coût   3:Preis   6:Coste    8:Цена

    if ($_ = $item->getField('repairPrice'))                                                        // 3:Reparaturkosten    8:Цена починки  2:Cout de réparation    6:Coste de reparación
        $quickInfo[] = Lang::$item['repairCost'].Lang::$colon.'[money='.$_.']';

    if (in_array($item->getField('bonding'), [0, 2, 3]))    // avg auction buyout
        if ($_ = Util::getBuyoutForItem($_id))
            $quickInfo[] = '[tooltip=tooltip_buyoutprice]'.Lang::$item['buyout.'].'[/tooltip]'.Lang::$colon.'[money='.$_.'][color=q0] ('.Lang::$item['each'].')[/color]';

    if ($_flags & ITEM_FLAG_OPENABLE)                       // avg money contained                  // 2:Vaut   8:Деньги    6:Valor     3:Wert
        if ($_ = intVal(($item->getField('minMoneyLoot') + $item->getField('maxMoneyLoot')) / 2))
            $quickInfo[] = Lang::$item['worth'].Lang::$colon.'[tooltip=tooltip_avgmoneycontained][money='.$_.'][/tooltip]';

    if ($_slot)                                             // if it goes into a slot it may be disenchanted
    {
        if ($item->getField('disenchantId'))
        {
            $_ = $item->getField('requiredDisenchantSkill');
            if ($_ < 1)                                     // these are some items, that never went live .. extremely rough emulation here
                $_ = intVal($item->getField('itemLevel') / 7.5) * 25;

            $quickInfo[] = Lang::$item['disenchantable'].'&nbsp;([tooltip=tooltip_reqenchanting]'.$_.'[/tooltip])';        // 35002
        }
        else
            $quickInfo[] = Lang::$item['cantDisenchant'];   // 27978
    }

    if (($_flags & ITEM_FLAG_MILLABLE) && $item->getField('requiredSkill') == 773)
        $quickInfo[] = Lang::$item['millable'].'&nbsp;([tooltip=tooltip_reqinscription]'.$item->getField('requiredSkillRank').'[/tooltip])';    // 8:Можно растолочь    2:Pilable   6:Se puede moler    3:Mahlbar

    if (($_flags & ITEM_FLAG_PROSPECTABLE) && $item->getField('requiredSkill') == 755)
        $quickInfo[] = Lang::$item['prospectable'].'&nbsp;([tooltip=tooltip_reqjewelcrafting]'.$item->getField('requiredSkillRank').'[/tooltip])';  // 3:Sondierbar 8:Просеиваемое  2:Prospectable  6:Prospectable

    if ($_flags & ITEM_FLAG_DEPRECATED)
        $quickInfo[] = '[tooltip=tooltip_deprecated]'.Lang::$item['deprecated'].'[/tooltip]';       // 3:Nicht benutzt   6:Depreciado      8:Устарело     2:Désuet

    if ($_flags & ITEM_FLAG_NO_EQUIPCD)
        $quickInfo[] = '[tooltip=tooltip_noequipcooldown]'.Lang::$item['noEquipCD'].'[/tooltip]';   // 3:Keine Anlegabklingzeit    6:No tiene tiempo de reutilización al equipar      8:Нет отката при надевании     2:Aucun temps de recharge lorsqu'équipé

    if ($_flags & ITEM_FLAG_PARTYLOOT)
        $quickInfo[] = '[tooltip=tooltip_partyloot]'.Lang::$item['partyLoot'].'[/tooltip]';         // 3:Gruppenloot    6:Despojo de grupo      8:Добыча группы     2:Butin de groupe

    if ($_flags & ITEM_FLAG_REFUNDABLE)
        $quickInfo[] = '[tooltip=tooltip_refundable]'.Lang::$item['refundable'].'[/tooltip]';       // 3:Rückzahlbar    6:Se puede devolver      8:Подлежит возврату     2:Remboursable

    if ($_flags & ITEM_FLAG_SMARTLOOT)
        $quickInfo[] = '[tooltip=tooltip_smartloot]'.Lang::$item['smartLoot'].'[/tooltip]';         // 3:Intelligente Beuteverteilung    6:Botín inteligente      8:Умное распределение добычи     2:Butin intelligent

    if ($_flags & ITEM_FLAG_INDESTRUCTIBLE)
        $quickInfo[] = Lang::$item['indestructible'];                                               // 3:Kann nicht zerstört werden   6:No puede ser destruido      8:Невозможно выбросить     2:Ne peut être détruit

    if ($_flags & ITEM_FLAG_USABLE_ARENA)
        $quickInfo[] = Lang::$item['useInArena'];                                                   // 3: Benutzbar in Arenen   2:Utilisable en Aréna    6:Se puede usar en arenas      8:Используется на аренах

    if ($_flags & ITEM_FLAG_USABLE_SHAPED)
        $quickInfo[] = Lang::$item['useInShape'];                                                   // 2:Utilisable lorsque transformé  3:Benutzbar in Gestaltwandlung   6:Se puede usar con cambio de forma    8:Используется в формах

    if ($item->getField('flagsExtra') & 0x0100)             // cant roll need
        $quickInfo[] = '[tooltip=tooltip_cannotrollneed]'.Lang::$item['noNeedRoll'].'[/tooltip]';   // 3:Kann nicht für Bedarf werfen   6:No se puede hacer una tirada por Necesidad    2:Ne peut pas faire un jet de Besoin    8:Нельзя говорить "Мне это нужно"

    if ($item->getField('bagFamily') & 0x0100)              // fits into keyring
        $quickInfo[] = Lang::$item['atKeyring'];                                                    // 2:(Va dans le trousseau de clés) 8:(Может быть помещён в связку для ключей) 6:(Se puede poner en el llavero) 3:(Passt in den Schlüsselbund)


    /****************/
    /* Main Content */
    /****************/

    $pageData = array(
        'infobox'  => $quickInfo ? '[ul][li]'.implode('[/li][li]', $quickInfo).'[/li][/ul]' : null,
        'relTabs'  => [],
        'tooltip'  => $item->renderTooltip([], true),
        'path'     => [0, 0],
        'title'    => [$item->getField('name', true), Util::ucFirst(Lang::$game['item'])],
        'pageText' => [],
        'buttons'  => in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR]),
        'page'     => array(
            'color'     => Util::$rarityColorStings[$item->getField('quality')],
            'quality'   => $item->getField('quality'),
            'icon'      => $item->getField('iconString'),
            'name'      => $item->getField('name', true),
            'displayId' => $item->getField('displayId'),
            'slot'      => $_slot,
            'stack'     => $item->getField('stackable'),
            'class'     => $_class
        )
    );

    // path
    if (in_array($_class, [5, 8, 14]))
    {
        $pageData['path'][] = 15;                           // misc.

        if ($_class == 5)                                   // reagent
            $pageData['path'][] = 1;
        else
            $pageData['path'][] = 4;                        // other
    }
    else
    {
        $pageData['path'][] = $_class;

        if (!in_array($_class, [ITEM_CLASS_MONEY, ITEM_CLASS_QUEST, ITEM_CLASS_KEY]))
            $pageData['path'][] = $_subClass;

        if ($_class == ITEM_CLASS_ARMOR && in_array($_subClass, [1, 2, 3, 4]))
        {
            if ($_ = $_slot);
                $pageData['path'][] = $_;
        }
        else if (($_class == ITEM_CLASS_CONSUMABLE && $_subClass == 2) || $_class == ITEM_CLASS_GLYPH)
            $pageData['path'][] = $item->getField('subSubClass');
    }

    // pageText
    if ($next = $item->getField('pageTextId'))
    {
        while ($next)
        {
            $row = DB::Aowow()->selectRow('SELECT *, text as Text_loc0 FROM page_text pt LEFT JOIN locales_page_text lpt ON pt.entry = lpt.entry WHERE pt.entry = ?d', $next);
            $next = $row['next_page'];
            $pageData['pageText'][] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
        }
    }

    // subItems
    $item->initSubItems();
    if (!empty($item->subItems[$_id]))
    {
        uaSort($item->subItems[$_id], function($a, $b) { return strcmp($a['name'], $b['name']); });
        $pageData['page']['subItems'] = array_values($item->subItems[$_id]);
    }

    // factionchange-equivalent
    $pendant = DB::Aowow()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_items WHERE alliance_id = ?d OR horde_id = ?d', $_id, $_id, $_id);
    if ($pendant)
    {
        $altItem = new ItemList(array(['id', abs($pendant)]));      // todo (med): include this item in tab: "see also"
        if (!$altItem->error)
        {
            $pageData['page']['transfer'] = array(
                'id'        => $altItem->id,
                'quality'   => $altItem->getField('quality'),
                'icon'      => $altItem->getField('iconString'),
                'name'      => $altItem->getField('name', true),
                'facInt'    => $pendant > 0 ? 'alliance' : 'horde',
                'facName'   => $pendant > 0 ? Lang::$game['si'][1] : Lang::$game['si'][2]
            );
        }
    }

/*
    /**************/
    /* Extra Tabs */
    /**************/

    // dropped by creature

    // GO-loot
    // diff between gathered from / mined from / contained in
    // foreach($rows as $row)
    // {
        // // Залежи руды
        // if($row['lockproperties1'] == LOCK_PROPERTIES_MINING)
            // $item['minedfromobject'][] = array_merge(objectinfo2($row), $drop);
        // // Собирается с трав
        // elseif($row['lockproperties1'] == LOCK_PROPERTIES_HERBALISM)
            // $item['gatheredfromobject'][] = array_merge(objectinfo2($row), $drop);
        // // Сундуки
        // else
            // $item['containedinobject'][] = array_merge(objectinfo2($row), $drop);
    // }

    // sold by [consult itemExtendedCost]

    // Objective of (quest)

    // provided for (quest)

    // reward of (quest)

    // reward of (quest) [from mail-loot]

    // contained in (item) [item_loot]

    // contains [item_loot]
    $itemLoot = Util::handleLoot(LOOT_ITEM, $item->id, $smarty, User::isInGroup(U_GROUP_STAFF));
    if ($itemLoot)
    {
        $extraCols = ['Listview.extraCols.percent'];

        if (User::isInGroup(U_GROUP_STAFF))
        {
            $extraCols[] = "Listview.funcBox.createSimpleCol('group', 'group', '10%', 'group')";
            $extraCols[] = "Listview.funcBox.createSimpleCol('mode', LANG.compose_mode, '10%', 'mode')";
            $extraCols[] = "Listview.funcBox.createSimpleCol('reference', LANG.finpcs.seploot + ' ' + LANG.button_link, '10%', 'reference')";
        }

        $pageData['relTabs'][] = array(
            'file'   => 'item',
            'data'   => $itemLoot,
            'params' => [
                'tabs'       => '$tabsRelated',
                'name'       => '$LANG.tab_contains',
                'id'         => 'contains',
                'hiddenCols' => "$['side', 'slot', 'source', 'reqlevel']",
                'extraCols'  => "$[".implode(', ', $extraCols)."]",
            ]
        );
    }

    // pickpocketed from

    // skinning_loot skinned from & salvaged from

    // prospecting & prospected from
    // milling & milled from

    // disentchanting from & to

    // can be placed in
    // if($item['BagFamily'] == 256)
    // {
        // // Если это ключ
        // $item['key'] = true;
    // }

    // reagent for

    // created by [spell]

    // fished in

    // currency for

    // [spell_loot_template] ehh...

    // criteria of
    // array(ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM, ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM, ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM, ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM),

    // teaches

    // Same model as

    // unlocks
        // $locks_row = $DB->selectCol('
        // SELECT lockID
        // FROM ?_lock
        // WHERE
            // (type1=1 AND lockproperties1=?d) OR
            // (type2=1 AND lockproperties2=?d) OR
            // (type3=1 AND lockproperties3=?d) OR
            // (type4=1 AND lockproperties4=?d) OR
            // (type5=1 AND lockproperties5=?d)
        // ',
        // $item['entry'], $item['entry'], $item['entry'], $item['entry'], $item['entry']
    // );

    $smarty->saveCache($cacheKeyPage, $pageData);
}

// menuId 0: Item     g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
    'title'  => implode(" - ", $pageData['title']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_ITEM,
    'typeId' => $_id,
    'reqJS'  => array(
        $pageData['pageText'] ? 'template/js/Book.js' : null,
        'template/js/swfobject.js',
        'template/js/profile.js',
        'template/js/filters.js',
        '?data=weight-presets'
    ),
    'reqCSS' => array(
        $pageData['pageText'] ? ['path' => 'template/css/Book.css'] : null,
    )
));
$smarty->assign('community', CommunityContent::getAll(TYPE_ITEM, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$item, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('item.tpl');

?>
