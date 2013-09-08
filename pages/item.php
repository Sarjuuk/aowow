<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


if (isset($_GET['xml']))
    die('unsupported, as i do not see the point');

require 'includes/class.community.php';

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

    $pageData = array(
        'infobox'  => [],
        'relTabs'  => [],
        'tooltip'  => $item->renderTooltip([], false),
        'page'     => $item->getDetailPageData(),
        'path'     => [0, 0, $item->getField('classs'), $item->getField('subClass')],
        'title'    => [Lang::$game['item'], $item->getField('name', true)],
        'pagetext' => false,            // Books
        'buttons'  => in_array($item->getField('class'), [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR]),
    );


/*
    <table class="infobox">
        <tr><th>{#Quick_Facts#}</th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <ul>
                {* Уровень вещи *}
                {if $item.level}<li><div>{#level#}: {$item.level}</div></li>{/if}
                {* Стоимость вещи *}
                {if $item.buygold or $item.buysilver or $item.buycopper}
                    <li><div>
                        {#Buy_for#}:
                        {if $item.buygold}<span class="moneygold">{$item.buygold}</span>{/if}
                        {if $item.buysilver}<span class="moneysilver">{$item.buysilver}</span>{/if}
                        {if $item.buycopper}<span class="moneycopper">{$item.buycopper}</span>{/if}
                    </div></li>
                {/if}
                {if $item.sellgold or $item.sellsilver or $item.sellcopper}
                    <li><div>
                        {#Sells_for#}:
                        {if $item.sellgold}<span class="moneygold">{$item.sellgold}</span>{/if}
                        {if $item.sellsilver}<span class="moneysilver">{$item.sellsilver}</span>{/if}
                        {if $item.sellcopper}<span class="moneycopper">{$item.sellcopper}</span>{/if}
                    </div></li>
                {/if}
                {if isset($item.disenchantskill)}<li><div>{#Disenchantable#} (<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_reqenchanting, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$item.disenchantskill}</span>)</div></li>{/if}
                {if isset($item.key)}<li><div>{#Can_be_placed_in_the_keyring#}</div></li>{/if}
            </ul>
        </td></tr>
    </table>
*/

    /********/
    /* TABS */
    /********/

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
    'book'   => $pageData['pagetext'] ? true : false,
    'title'  => implode(" - ", $pageData['title']),
    'path'   => json_encode($pageData['path'], JSON_NUMERIC_CHECK),
    'tab'    => 0,
    'type'   => TYPE_ITEM,
    'typeId' => $_id,
    'reqJS'  => array(
        'template/js/swfobject.js',
        'template/js/profile.js',
        'template/js/filters.js',
        '?data=weight-presets'
    )
));
$smarty->assign('community', CommunityContent::getAll(TYPE_ITEM, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$item, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('item.tpl');

?>
