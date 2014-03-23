<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id       = intVal($pageParam);
$_path     = [0, 0];
$_visSlots = array(
    INVTYPE_HEAD,           INVTYPE_SHOULDERS,      INVTYPE_BODY,           INVTYPE_CHEST,          INVTYPE_WAIST,          INVTYPE_LEGS,           INVTYPE_FEET,           INVTYPE_WRISTS,
    INVTYPE_HANDS,          INVTYPE_WEAPON,         INVTYPE_SHIELD,         INVTYPE_RANGED,         INVTYPE_CLOAK,          INVTYPE_2HWEAPON,       INVTYPE_TABARD,         INVTYPE_ROBE,
    INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPONOFFHAND,  INVTYPE_HOLDABLE,       INVTYPE_THROWN,         INVTYPE_RANGEDRIGHT
);

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


// xml object
if (isset($_GET['xml']))
{
    header('Content-type: text/xml; charsetUTF-8');

    $cacheKeyXML = implode('_', [CACHETYPE_XML, TYPE_ITEM, $_id, -1, User::$localeId]);

    if (!$smarty->loadCache($cacheKeyXML, $root))
    {
        $root = new SimpleXML('<aowow />');

        if (!$_id)
        {
            $str = DB::Aowow()->escape(urlDecode($pageParam));
            $str = substr($str, 1, -1);                     // escape adds '
            $cnd = array(['name_loc'.User::$localeId, $str]);
        }
        else
            $cnd = array(['i.id', $_id]);

        $item = new ItemList($cnd);
        if ($item->error)
            $root->addChild('error', 'Item not found!');
        else
        {
            // item root
            $xml = $root->addChild('item');
            $xml->addAttribute('id', $item->id);

            // name
            $xml->addChild('name')->addCData($item->getField('name', true));
            // itemlevel
            $xml->addChild('level', $item->getField('itemLevel'));
            // quality
            $xml->addChild('quality', Lang::$item['quality'][$item->getField('quality')])->addAttribute('id', $item->getField('quality'));
            // class
            $x = Lang::$item['cat'][$item->getField('class')];
            $xml->addChild('class')->addCData(is_array($x) ? $x[0] : $x)->addAttribute('id', $item->getField('class'));
            // subclass
            $x = $item->getField('class') == 2 ? Lang::$spell['weaponSubClass'] : Lang::$item['cat'][$item->getField('class')][1];
            $xml->addChild('subclass')->addCData(is_array($x) ? (is_array($x[$item->getField('subClass')]) ? $x[$item->getField('subClass')][0] : $x[$item->getField('subClass')]) : null)->addAttribute('id', $item->getField('subClass'));
            // icon + displayId
            $xml->addChild('icon', $item->getField('iconString'))->addAttribute('displayId', $item->getField('displayId'));
            // inventorySlot
            $xml->addChild('inventorySlot', Lang::$item['inventoryType'][$item->getField('slot')])->addAttribute('id', $item->getField('slot'));
            // tooltip
            $xml->addChild('htmlTooltip')->addCData($item->renderTooltip());

            $onUse  = $item->extendJsonStats();

            // json
            $fields = ["classs", "displayid", "dps", "id", "level", "name", "reqlevel", "slot", "slotbak", "source", "sourcemore", "speed", "subclass"];
            $json   = '';
            foreach ($fields as $f)
            {
                if (($_ = @$item->json[$item->id][$f]) !== null)
                {
                    $_ = $f == 'name' ? (7 - $item->getField('quality')).$_ : $_;
                    $json .= ',"'.$f.'":'.$_;
                }
            }
            $xml->addChild('json')->addCData(substr($json, 1));

            // jsonEquip missing: avgbuyout, cooldown
            $json = '';
            if ($_ = $item->getField('sellPrice'))          // sellprice
                $json .= ',"sellprice":'.$_;

            if ($_ = $item->getField('requiredLevel'))      // reqlevel
                $json .= ',"reqlevel":'.$_;

            if ($_ = $item->getField('requiredSkill'))      // reqskill
                $json .= ',"reqskill":'.$_;

            if ($_ = $item->getField('requiredSkillRank'))  // reqskillrank
                $json .= ',"reqskillrank":'.$_;

            foreach (Util::$itemMods as $idx => $str)
                if ($_ = @$item->itemMods[$item->id][$idx])
                    $json .= ',"'.$str.'":'.$_;

            foreach ($_ = $item->json[$item->id] as $name => $qty)
                if (in_array($name, Util::$itemFilter))
                    $json .= ',"'.$name.'":'.$qty;

            $xml->addChild('jsonEquip')->addCData(substr($json, 1));

            // jsonUse
            if ($onUse)
            {
                $j = '';
                foreach ($onUse as $idx => $qty)
                    $j .= ',"'.Util::$itemMods[$idx].'":'.$qty;

                $xml->addChild('jsonUse')->addCdata(substr($j, 1));
            }

            // reagents
            $cnd = array(
                'OR',
                ['AND', ['effect1CreateItemId', $item->id], ['OR', ['effect1Id', SpellList::$effects['itemCreate']], ['effect1AuraId', SpellList::$auras['itemCreate']]]],
                ['AND', ['effect2CreateItemId', $item->id], ['OR', ['effect2Id', SpellList::$effects['itemCreate']], ['effect2AuraId', SpellList::$auras['itemCreate']]]],
                ['AND', ['effect3CreateItemId', $item->id], ['OR', ['effect3Id', SpellList::$effects['itemCreate']], ['effect3AuraId', SpellList::$auras['itemCreate']]]],
            );

            $spellSource = new SpellList($cnd);
            if (!$spellSource->error)
            {
                $cbNode = $xml->addChild('createdBy');

                foreach ($spellSource->iterate() as $sId => $__)
                {
                    foreach ($spellSource->canCreateItem() as $idx)
                    {
                        if ($spellSource->getField('effect'.$idx.'CreateItemId') != $item->id)
                            continue;

                        $splNode = $cbNode->addChild('spell');
                        $splNode->addAttribute('id', $sId);
                        $splNode->addAttribute('name', $spellSource->getField('name', true));
                        $splNode->addAttribute('icon', $item->getField('iconString'));
                        $splNode->addAttribute('minCount', $spellSource->getField('effect'.$idx.'BasePoints') + 1);
                        $splNode->addAttribute('maxCount', $spellSource->getField('effect'.$idx.'BasePoints') + $spellSource->getField('effect'.$idx.'DieSides'));

                        foreach ($spellSource->getReagentsForCurrent() as $rId => $qty)
                        {
                            if ($reagent = $spellSource->relItems->getEntry($rId))
                            {
                                $rgtNode = $splNode->addChild('reagent');
                                $rgtNode->addAttribute('id', $rId);
                                $rgtNode->addAttribute('name', Util::localizedString($reagent, 'name'));
                                $rgtNode->addAttribute('quality', $reagent['quality']);
                                $rgtNode->addAttribute('icon', $reagent['iconString']);
                                $rgtNode->addAttribute('count', $qty[1]);
                            }
                        }

                        break;
                    }
                }
            }

            // link
            $xml->addChild('link', HOST_URL.'?item='.$item->id);
        }

        $smarty->saveCache($cacheKeyXML, $root);
    }

    die($root->asXML());
}


// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $item = new ItemList(array(['i.id', $_id]));
    if ($item->error)
        $smarty->notFound(Lang::$game['item'], $_id);

    $item->addGlobalsToJscript($smarty, GLOBALINFO_EXTRA | GLOBALINFO_SELF);

    $_flags     = $item->getField('flags');
    $_slot      = $item->getField('slot');
    $_class     = $item->getField('class');
    $_subClass  = $item->getField('subClass');
    $_bagFamily = $item->getField('bagFamily');

    /***********/
    /* Infobox */
    /***********/

    $quickInfo = Lang::getInfoBoxForFlags($item->getField('cuFlags'));

    if (in_array($_class, [ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON, ITEM_CLASS_AMMUNITION]) || $item->getField('gemEnchantmentId')) // itemlevel
        $quickInfo[] = Lang::$game['level'].Lang::$colon.$item->getField('itemLevel');

    if ($_flags & ITEM_FLAG_ACCOUNTBOUND)                   // account-wide
        $quickInfo[] = Lang::$item['accountWide'];

    if ($si = $item->json[$_id]['side'])                    // side
        if ($si != 3)
            $quickInfo[] = Lang::$main['side'].Lang::$colon.'[span class=icon-'.($si == 1 ? 'alliance' : 'horde').']'.Lang::$game['si'][$si].'[/span]';

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

            $tt = '[tooltip=tooltip_consumedonuse]'.Lang::$item['consumable'].'[/tooltip]';
            break;
        }

        if ($hasUse)
            $quickInfo[] = isset($tt) ? $tt : '[tooltip=tooltip_notconsumedonuse]'.Lang::$item['nonConsumable'].'[/tooltip]';
    }

    if ($hId = $item->getField('holidayId'))
        if ($hName = DB::Aowow()->selectRow('SELECT * FROM ?_holidays WHERE id = ?d', $hId))
            $quickInfo[] = Lang::$game['eventShort'].Lang::$colon.'[url=?event='.$hId.']'.Util::localizedString($hName, 'name').'[/url]';

    if ($tId = $item->getField('totemCategory'))
        if ($tName = DB::Aowow()->selectRow('SELECT * FROM ?_totemCategory WHERE id = ?d', $tId))
            $quickInfo[] = Lang::$item['tool'].Lang::$colon.'[url=?items&filter=cr=91;crs='.$tId.';crv=0]'.Util::localizedString($tName, 'name').'[/url]';

    $each = $item->getField('stackable') > 1 ? '[color=q0] ('.Lang::$item['each'].')[/color]' : null;

    if ($_ = @$item->getExtendedCost([], $_reqRating)[$item->id])
    {
        $handled  = [];
        $costList = [];
        foreach ($_ as $npcId => $data)
        {
            $tokens   = [];
            $currency = [];
            foreach ($data as $c => $qty)
            {
                if (is_string($c))
                {
                    unset($data[$c]);                       // unset miscData to prevent having two vendors /w the same cost being cached, because of different stock or rating-requirements
                    continue;
                }

                if ($c < 0)                                 // currency items (and honor or arena)
                    $currency[] = -$c.','.$qty;
                else if ($c > 0)                            // plain items (item1,count1,item2,count2,...)
                    $tokens[$c] = $c.','.$qty;
            }

            if (in_array(md5(serialize($data)), $handled))  // display every cost-combination only once
                continue;

            $handled[] = md5(serialize($data));

            $cost = isset($data[0]) ? '[money='.$data[0] : '[money';

            if ($tokens)
                $cost .= ' items='.implode(',', $tokens);

            if ($currency)
                $cost .= ' currency='.implode(',', $currency);

            $cost .= ']';

            $costList[] = $cost;
        }

        if (count($costList) == 1)
            $quickInfo[] = Lang::$item['cost'].Lang::$colon.$costList[0].$each;
        else if (count($costList) > 1)
            $quickInfo[] = Lang::$item['cost'].$each.Lang::$colon.'[ul][li]'.implode('[/li][li]', $costList).'[/li][/ul]';

        if ($_reqRating)
            $quickInfo[] = sprintf(Lang::$item['reqRating'], $_reqRating);
    }

    if ($_ = $item->getField('repairPrice'))
        $quickInfo[] = Lang::$item['repairCost'].Lang::$colon.'[money='.$_.']';

    if (in_array($item->getField('bonding'), [0, 2, 3]))    // avg auction buyout
        if ($_ = Util::getBuyoutForItem($_id))
            $quickInfo[] = '[tooltip=tooltip_buyoutprice]'.Lang::$item['buyout.'].'[/tooltip]'.Lang::$colon.'[money='.$_.']'.$each;

    if ($_flags & ITEM_FLAG_OPENABLE)                       // avg money contained
        if ($_ = intVal(($item->getField('minMoneyLoot') + $item->getField('maxMoneyLoot')) / 2))
            $quickInfo[] = Lang::$item['worth'].Lang::$colon.'[tooltip=tooltip_avgmoneycontained][money='.$_.'][/tooltip]';

    if ($_slot && $_class != ITEM_CLASS_CONTAINER)          // if it goes into a slot it may be disenchanted
    {
        if ($item->getField('disenchantId'))
        {
            $_ = $item->getField('requiredDisenchantSkill');
            if ($_ < 1)                                     // these are some items, that never went live .. extremely rough emulation here
                $_ = intVal($item->getField('itemLevel') / 7.5) * 25;

            $quickInfo[] = Lang::$item['disenchantable'].'&nbsp;([tooltip=tooltip_reqenchanting]'.$_.'[/tooltip])';
        }
        else
            $quickInfo[] = Lang::$item['cantDisenchant'];
    }

    if (($_flags & ITEM_FLAG_MILLABLE) && $item->getField('requiredSkill') == 773)
        $quickInfo[] = Lang::$item['millable'].'&nbsp;([tooltip=tooltip_reqinscription]'.$item->getField('requiredSkillRank').'[/tooltip])';

    if (($_flags & ITEM_FLAG_PROSPECTABLE) && $item->getField('requiredSkill') == 755)
        $quickInfo[] = Lang::$item['prospectable'].'&nbsp;([tooltip=tooltip_reqjewelcrafting]'.$item->getField('requiredSkillRank').'[/tooltip])';

    if ($_flags & ITEM_FLAG_DEPRECATED)
        $quickInfo[] = '[tooltip=tooltip_deprecated]'.Lang::$item['deprecated'].'[/tooltip]';

    if ($_flags & ITEM_FLAG_NO_EQUIPCD)
        $quickInfo[] = '[tooltip=tooltip_noequipcooldown]'.Lang::$item['noEquipCD'].'[/tooltip]';

    if ($_flags & ITEM_FLAG_PARTYLOOT)
        $quickInfo[] = '[tooltip=tooltip_partyloot]'.Lang::$item['partyLoot'].'[/tooltip]';

    if ($_flags & ITEM_FLAG_REFUNDABLE)
        $quickInfo[] = '[tooltip=tooltip_refundable]'.Lang::$item['refundable'].'[/tooltip]';

    if ($_flags & ITEM_FLAG_SMARTLOOT)
        $quickInfo[] = '[tooltip=tooltip_smartloot]'.Lang::$item['smartLoot'].'[/tooltip]';

    if ($_flags & ITEM_FLAG_INDESTRUCTIBLE)
        $quickInfo[] = Lang::$item['indestructible'];

    if ($_flags & ITEM_FLAG_USABLE_ARENA)
        $quickInfo[] = Lang::$item['useInArena'];

    if ($_flags & ITEM_FLAG_USABLE_SHAPED)
        $quickInfo[] = Lang::$item['useInShape'];

    if ($item->getField('flagsExtra') & 0x0100)             // cant roll need
        $quickInfo[] = '[tooltip=tooltip_cannotrollneed]'.Lang::$item['noNeedRoll'].'[/tooltip]';

    if ($_bagFamily & 0x0100)                               // fits into keyring
        $quickInfo[] = Lang::$item['atKeyring'];


    /****************/
    /* Main Content */
    /****************/

    $cmpUpg = in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) || $item->getField('gemEnchantmentId');
    $view3D = in_array($_slot, $_visSlots) && $item->getField('displayId');

    // path
    if (in_array($_class, [5, 8, 14]))
    {
        $_path[] = 15;                                      // misc.

        if ($_class == 5)                                   // reagent
            $_path[] = 1;
        else
            $_path[] = 4;                                   // other
    }
    else
    {
        $_path[] = $_class;

        if (!in_array($_class, [ITEM_CLASS_MONEY, ITEM_CLASS_QUEST, ITEM_CLASS_KEY]))
            $_path[] = $_subClass;

        if ($_class == ITEM_CLASS_ARMOR && in_array($_subClass, [1, 2, 3, 4]))
        {
            if ($_ = $_slot);
                $_path[] = $_;
        }
        else if (($_class == ITEM_CLASS_CONSUMABLE && $_subClass == 2) || $_class == ITEM_CLASS_GLYPH)
            $_path[] = $item->getField('subSubClass');
    }

    // pageText
    $pageText = [];
    if ($next = $item->getField('pageTextId'))
    {
        while ($next)
        {
            $row = DB::Aowow()->selectRow('SELECT *, text as Text_loc0 FROM page_text pt LEFT JOIN locales_page_text lpt ON pt.entry = lpt.entry WHERE pt.entry = ?d', $next);
            $next = $row['next_page'];
            $pageText[] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
        }
    }

    // menuId 0: Item     g_initPath()
    //  tabId 0: Database g_initHeader()
    $pageData = array(
        'page'     => array(
            'quality'    => $item->getField('quality'),
            'headIcons'  => [$item->getField('iconString'), $item->getField('stackable')],
            'name'       => $item->getField('name', true),
            'infobox'    => $quickInfo ? '[ul][li]'.implode('[/li][li]', $quickInfo).'[/li][/ul]' : null,
            'tooltip'    => $item->renderTooltip([], true),
            'path'       => json_encode($_path, JSON_NUMERIC_CHECK),
            'title'      => $item->getField('name', true).' - '.Util::ucFirst(Lang::$game['item']),
            'pageText'   => $pageText,
            'tab'        => 0,
            'type'       => TYPE_ITEM,
            'typeId'     => $_id,
            'reqJS'      => array(
                $pageText ? 'static/js/Book.js' : null,
                'static/js/swfobject.js',
                'static/js/profile.js',
                'static/js/filters.js',
                '?data=weight-presets'
            ),
            'reqCSS'     => array(
                $pageText ? ['path' => 'static/css/Book.css'] : null,
            ),
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => ['color' => 'ff'.Util::$rarityColorStings[$item->getField('quality')], 'linkId' => 'item:'.$_id.':0:0:0:0:0:0:0:0'],
                BUTTON_VIEW3D  => $view3D ? ['displayId' => $item->getField('displayId'), 'slot' => $_slot, 'type' => TYPE_ITEM, 'typeId' => $_id] : false,
                BUTTON_COMPARE => $cmpUpg,                      // bool required
                BUTTON_EQUIP   => $cmpUpg,
                BUTTON_UPGRADE => $cmpUpg ? ['class' => $_class, 'slot' => $_slot] : false
            ),
        ),
        'relTabs'  => []
    );

    // subItems
    $item->initSubItems();
    if (!empty($item->subItems[$_id]))
    {
        uaSort($item->subItems[$_id], function($a, $b) { return strcmp($a['name'], $b['name']); });
        $pageData['page']['subItems'] = array_values($item->subItems[$_id]);

        // merge identical stats and names for normal users (e.g. spellPower of a specific school became generel spellPower with 3.0)
        if (!User::isInGroup(U_GROUP_STAFF))
        {
            for ($i = 1; $i < count($pageData['page']['subItems']); $i++)
            {
                $prev = &$pageData['page']['subItems'][$i - 1];
                $cur  = &$pageData['page']['subItems'][$i];
                if ($prev['jsonequip'] == $cur['jsonequip'] && $prev['name'] == $cur['name'])
                {
                    $prev['chance'] += $cur['chance'];
                    array_splice($pageData['page']['subItems'], $i , 1);
                    $i = 1;
                }
            }
        }
    }

    // factionchange-equivalent
    if ($pendant = DB::Aowow()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_items WHERE alliance_id = ?d OR horde_id = ?d', $_id, $_id, $_id))
    {
        $altItem = new ItemList(array(['id', abs($pendant)]));
        if (!$altItem->error)
        {
            $pageData['page']['transfer'] = sprintf(
                Lang::$item['_transfer'],
                $altItem->id,
                $altItem->getField('quality'),
                $altItem->getField('iconString'),
                $altItem->getField('name', true),
                $pendant > 0 ? 'alliance' : 'horde',
                $pendant > 0 ? Lang::$game['si'][1] : Lang::$game['si'][2]
            );
        }
    }

    /**************/
    /* Extra Tabs */
    /**************/

    // tabs: this item is contained in..
    $lootTabs = Util::getLootSource($_id);
    foreach ($lootTabs as $tab)
    {
        $pageData['relTabs'][] = array(
            'file'   => $tab[0],
            'data'   => $tab[1],
            'params' => [
                'tabs'        => '$tabsRelated',
                'name'        => $tab[2],
                'id'          => $tab[3],
                'extraCols'   => $tab[4] ? '$['.implode(', ', array_unique($tab[4])).']' : null,
                'hiddenCols'  => $tab[5] ? '$['.implode(', ', array_unique($tab[5])).']' : null,
                'visibleCols' => $tab[6] ? '$'. json_encode(  array_unique($tab[6]))     : null
            ]
        );
    }

    // tabs: this item contains..
    $sourceFor = array(
         [LOOT_ITEM,        $item->id,                       '$LANG.tab_contains',      'contains',      ['Listview.extraCols.percent'], []                          , []],
         [LOOT_PROSPECTING, $item->id,                       '$LANG.tab_prospecting',   'prospecting',   ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []],
         [LOOT_MILLING,     $item->id,                       '$LANG.tab_milling',       'milling',       ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []],
         [LOOT_DISENCHANT,  $item->getField('disenchantId'), '$LANG.tab_disenchanting', 'disenchanting', ['Listview.extraCols.percent'], ['side', 'slot', 'reqlevel'], []]
    );

    $reqQuest = [];
    foreach ($sourceFor as $sf)
    {
        if ($itemLoot = Util::handleLoot($sf[0], $sf[1], User::isInGroup(U_GROUP_STAFF), $sf[4]))
        {
            foreach ($itemLoot as $l => $lv)
            {
                if (!$lv['quest'])
                    continue;

                $sf[4][] = 'Listview.extraCols.condition';

                $reqQuest[$lv['id']] = 0;

                $itemLoot[$l]['condition'] = ['type' => TYPE_QUEST, 'typeId' => &$reqQuest[$lv['id']], 'status' => 1];
            }

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $itemLoot,
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'name'        => $sf[2],
                    'id'          => $sf[3],
                    'extraCols'   => $sf[4] ? "$[".implode(', ', array_unique($sf[4]))."]" : null,
                    'hiddenCols'  => $sf[5] ? "$".json_encode($sf[5]) : null,
                    'visibleCols' => $sf[6] ? '$'.json_encode($sf[6]) : null
                ]
            );
        }
    }

    if ($reqIds = array_keys($reqQuest))                    // apply quest-conditions as back-reference
    {
        $conditions = array(
            'OR',
            ['requiredSourceItemId1', $reqIds], ['requiredSourceItemId2', $reqIds],
            ['requiredSourceItemId3', $reqIds], ['requiredSourceItemId4', $reqIds],
            ['requiredItemId1', $reqIds], ['requiredItemId2', $reqIds], ['requiredItemId3', $reqIds],
            ['requiredItemId4', $reqIds], ['requiredItemId5', $reqIds], ['requiredItemId6', $reqIds]
        );

        $reqQuests = new QuestList($conditions);
        $reqQuests->addGlobalsToJscript($smarty);

        foreach ($reqQuests->iterate() as $qId => $__)
        {
            if (empty($reqQuests->requires[$qId][TYPE_ITEM]))
                continue;

            foreach ($reqIds as $rId)
                if (in_array($rId, $reqQuests->requires[$qId][TYPE_ITEM]))
                    $reqQuest[$rId] = $reqQuests->id;
        }
    }

    // tab: container can contain
    if ($item->getField('slots') > 0)
    {
        $contains = new ItemList(array(['bagFamily', $_bagFamily, '&'], ['slots', 1, '<'], 0));
        if (!$contains->error)
        {
            $contains->addGlobalsToJscript($smarty);

            $hCols = ['side'];
            if (!$contains->hasSetFields(['slot']))
                $hCols[] = 'slot';

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $contains->getListviewData(),
                'params' => [
                    'tabs'       => '$tabsRelated',
                    'name'       => '$LANG.tab_cancontain',
                    'id'         => 'can-contain',
                    'hiddenCols' => '$'.json_encode($hCols)
                ]
            );
        }
    }

    // tab: can be contained in (except keys)
    else if ($_bagFamily != 0x0100)
    {
        $contains = new ItemList(array(['bagFamily', $_bagFamily, '&'], ['slots', 0, '>'], 0));
        if (!$contains->error)
        {
            $contains->addGlobalsToJscript($smarty);

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $contains->getListviewData(),
                'params' => [
                    'tabs'       => '$tabsRelated',
                    'name'       => '$LANG.tab_canbeplacedin',
                    'id'         => 'can-be-placed-in',
                    'hiddenCols' => "$['side']"
                ]
            );
        }
    }

    // tab: criteria of
    $conditions = array(
        ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM, ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM, ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM, ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM]],
        ['ac.value1', $_id]
    );

    $criteriaOf = new AchievementList($conditions);
    if (!$criteriaOf->error)
    {
            $criteriaOf->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_REWARDS);

            $hCols = [];
            if (!$criteriaOf->hasSetFields(['rewardIds']))
                $hCols = ['rewards'];

            $pageData['relTabs'][] = array(
                'file'   => 'achievement',
                'data'   => $criteriaOf->getListviewData(),
                'params' => [
                    'tabs'        => '$tabsRelated',
                    'name'        => '$LANG.tab_criteriaof',
                    'id'          => 'criteria-of',
                    'visibleCols' => "$['category']",
                    'hiddenCols'  => '$'.json_encode($hCols)
                ]
            );
    }

    // tab: reagent for
    $conditions = array(
        'OR',
        ['reagent1', $_id], ['reagent2', $_id], ['reagent3', $_id], ['reagent4', $_id],
        ['reagent5', $_id], ['reagent6', $_id], ['reagent7', $_id], ['reagent8', $_id]
    );

    $reagent = new SpellList($conditions);
    if (!$reagent->error)
    {
        $reagent->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);

        $pageData['relTabs'][] = array(
            'file'   => 'spell',
            'data'   => $reagent->getListviewData(),
            'params' => [
                'tabs'        => '$tabsRelated',
                'name'        => '$LANG.tab_reagentfor',
                'id'          => 'reagent-for',
                'visibleCols' => "$['reagents']"
            ]
        );
    }

    // tab: unlocks (object or item)
    $lockIds = DB::Aowow()->selectCol(
        'SELECT id FROM ?_lock WHERE
        (type1 = 1 AND properties1 = ?d) OR
        (type2 = 1 AND properties2 = ?d) OR
        (type3 = 1 AND properties3 = ?d) OR
        (type4 = 1 AND properties4 = ?d) OR
        (type5 = 1 AND properties5 = ?d)',
        $_id, $_id, $_id, $_id, $_id
    );

    if ($lockIds)
    {
        // objects
        $lockedObj = new GameObjectList(array(['lockId', $lockIds]));
        if (!$lockedObj->error)
        {
            $pageData['relTabs'][] = array(
                'file'   => 'object',
                'data'   => $lockedObj->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_unlocks',
                    'id'   => 'unlocks-object'
                ]
            );
        }

        // items (generally unused. It's the spell on the item, that unlocks stuff)
        $lockedItm = new ItemList(array(['lockId', $lockIds]));
        if (!$lockedItm->error)
        {
            $lockedItm->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $lockedItm->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_unlocks',
                    'id'   => 'unlocks-item'
                ]
            );
        }
    }

    // tab: see also
    $conditions = array(
        ['id', $_id, '!'],
        [
            'OR',
            ['name_loc'.User::$localeId, $item->getField('name', true)],
            [
                'AND',
                ['class',         $_class],
                ['subClass',      $_subClass],
                ['slot',          $_slot],
                ['itemLevel',     $item->getField('itemLevel') - 15, '>'],
                ['itemLevel',     $item->getField('itemLevel') + 15, '<'],
                ['quality',       $item->getField('quality')],
                ['requiredClass', $item->getField('requiredClass')]
            ]
        ]
    );

    $saItems = new ItemList($conditions);
    if (!$saItems->error)
    {
        $saItems->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

        $pageData['relTabs'][] = array(
            'file'   => 'item',
            'data'   => $saItems->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_seealso',
                'id'   => 'see-also'
            ]
        );
    }

    // tab: starts (quest)
    if ($qId = $item->getField('startQuest'))
    {
        $starts = new QuestList(array(['qt.id', $qId]));
        if (!$starts->error)
        {
            $starts->addGlobalsToJscript($smarty);

            $pageData['relTabs'][] = array(
                'file'   => 'quest',
                'data'   => $starts->getListviewData(),
                'params' => [
                    'tabs' => '$tabsRelated',
                    'name' => '$LANG.tab_starts',
                    'id'   => 'starts-quest'
                ]
            );
        }
    }

    // tab: objective of (quest)
    $conditions = array(
        'OR',
        ['requiredItemId1', $_id], ['requiredItemId2', $_id], ['requiredItemId3', $_id],
        ['requiredItemId4', $_id], ['requiredItemId5', $_id], ['requiredItemId6', $_id]
    );
    $objective = new QuestList($conditions);
    if (!$objective->error)
    {
        $objective->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_REWARDS);

        $pageData['relTabs'][] = array(
            'file'   => 'quest',
            'data'   => $objective->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_objectiveof',
                'id'   => 'objective-of-quest'
            ]
        );
    }

    // tab: provided for (quest)
    $conditions = array(
        'OR', ['sourceItemId', $_id],
        ['requiredSourceItemId1', $_id], ['requiredSourceItemId2', $_id],
        ['requiredSourceItemId3', $_id], ['requiredSourceItemId4', $_id]
    );
    $provided = new QuestList($conditions);
    if (!$provided->error)
    {
        $provided->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_REWARDS);

        $pageData['relTabs'][] = array(
            'file'   => 'quest',
            'data'   => $provided->getListviewData(),
            'params' => [
                'tabs' => '$tabsRelated',
                'name' => '$LANG.tab_providedfor',
                'id'   => 'provided-for-quest'
            ]
        );
    }

    // tab: same model as
    // todo (low): should also work for creatures summoned by item
    if (($model = $item->getField('model')) && $_slot)
    {
        $sameModel = new ItemList(array(['model', $model], ['id', $_id, '!'], ['slot', $_slot]));
        if (!$sameModel->error)
        {
            $sameModel->addGlobalsToJscript($smarty, GLOBALINFO_SELF);

            $pageData['relTabs'][] = array(
                'file'   => 'genericmodel',
                'data'   => $sameModel->getListviewData(ITEMINFO_MODEL),
                'params' => [
                    'tabs'            => '$tabsRelated',
                    'name'            => '$LANG.tab_samemodelas',
                    'id'              => 'same-model-as',
                    'genericlinktype' => 'item'
                ]
            );
        }
    }

    // tab: sold by
    if ($vendors = @$item->getExtendedCost([], $_reqRating)[$item->id])
    {
        $soldBy = new CreatureList(array(['id', array_keys($vendors)]));
        if (!$soldBy->error)
        {
            $soldBy->addGlobalsToJscript($smarty, GLOBALINFO_SELF);
            $sbData = $soldBy->getListviewData();

            $extraCols = ['Listview.extraCols.stock', "Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", 'Listview.extraCols.cost'];

            $holidays = [];
            foreach ($sbData as $k => &$row)
            {
                $currency = [];
                $tokens   = [];
                foreach ($vendors[$k] as $id => $qty)
                {
                    if (is_string($id))
                        continue;

                    if ($id > 0)
                        $tokens[] = [$id, $qty];
                    else if ($id < 0)
                        $currency[] = [-$id, $qty];
                }

                $row['stock'] = $vendors[$k]['stock'];
                $row['cost']  = [$item->getField('buyPrice')];

                if ($e = $vendors[$k]['event'])
                {
                    if (count($extraCols) == 3)
                        $extraCols[] = 'Listview.extraCols.condition';

                    Util::$pageTemplate->extendGlobalIds(TYPE_WORLDEVENT, $e);
                    $row['condition'] = array(
                        'type'   => TYPE_WORLDEVENT,
                        'typeId' => -$e,
                        'status' => 1
                    );
                }

                if ($currency || $tokens)                   // fill idx:3 if required
                    $row['cost'][] = $currency;

                if ($tokens)
                    $row['cost'][] = $tokens;

                if ($x = $item->getField('buyPrice'))
                    $row['buyprice'] = $x;

                if ($x = $item->getField('sellPrice'))
                    $row['sellprice'] = $x;

                if ($x = $item->getField('buyCount'))
                    $row['stack'] = $x;
            }


            $pageData['relTabs'][] = array(
                'file'   => 'creature',
                'data'   => $sbData,
                'params' => [
                    'tabs'       => '$tabsRelated',
                    'name'       => '$LANG.tab_soldby',
                    'id'         => 'sold-by-npc',
                    'extraCols'  => '$['.implode(', ', $extraCols).']',
                    'hiddenCols' => "$['level', 'type']"
                ]
            );
        }
    }

    // tab: currency for
    // some minor trickery: get arenaPoints(43307) and honorPoints(43308) directly
    if ($_id == 43307)
        $w = 'iec.reqArenaPoints > 0';
    else if ($_id == 43308)
        $w = 'iec.reqHonorPoints > 0';
    else
        $w = 'iec.reqItemId1 = '.$_id.' OR iec.reqItemId2 = '.$_id.' OR iec.reqItemId3 = '.$_id.' OR iec.reqItemId4 = '.$_id.' OR iec.reqItemId5 = '.$_id;

    $boughtBy = DB::Aowow()->selectCol('
        SELECT item FROM npc_vendor nv JOIN ?_itemExtendedCost iec ON iec.id = nv.extendedCost WHERE '.$w.'
        UNION
        SELECT item FROM game_event_npc_vendor genv JOIN ?_itemExtendedCost iec ON iec.id = genv.extendedCost WHERE '.$w
    );
    if ($boughtBy)
    {
        $boughtBy = new ItemList(array(['id', $boughtBy]));
        if (!$boughtBy->error)
        {
            $boughtBy->addGlobalsToJscript($smarty);

            $iCur   = new CurrencyList(array(['itemId', $_id]));
            $filter = $iCur->error ? [TYPE_ITEM => $_id] : [TYPE_CURRENCY => $iCur->id];

            $pageData['relTabs'][] = array(
                'file'   => 'item',
                'data'   => $boughtBy->getListviewData(ITEMINFO_VENDOR, $filter),
                'params' => [
                    'tabs'      => '$tabsRelated',
                    'name'      => '$LANG.tab_currencyfor',
                    'id'        => 'currency-for',
                    'extraCols' => "$[Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack'), Listview.extraCols.cost]"
                ]
            );
        }
    }

    // tab: teaches
    $ids = $indirect = [];
    for ($i = 1; $i < 6; $i++)
    {
        if ($item->getField('spellTrigger'.$i) == 6)
            $ids[] = $item->getField('spellId'.$i);
        else if ($item->getField('spellTrigger'.$i) == 0 && $item->getField('spellId'.$i) > 0)
            $indirect[] = $item->getField('spellId'.$i);
    }

    // taught indirectly
    if ($indirect)
    {
        $indirectSpells = new SpellList(array(['id', $indirect]));
        foreach ($indirectSpells->iterate() as $__)
            if ($_ = $indirectSpells->canTeachSpell())
                foreach ($_ as $idx)
                    $ids[] = $indirectSpells->getField('effect'.$idx.'TriggerSpell');

        $ids = array_merge($ids, Util::getTaughtSpells($indirect));
    }

    if ($ids)
    {
        $taughtSpells = new SpellList(array(['id', $ids]));
        if (!$taughtSpells->error)
        {
            $taughtSpells->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);

            $visCols = ['level', 'schools'];
            if ($taughtSpells->hasSetFields(['reagent1']))
                $visCols[] = 'reagents';

            $pageData['relTabs'][] = array(
                'file'   => 'spell',
                'data'   => $taughtSpells->getListviewData(),
                'params' => [
                    'tabs'       => '$tabsRelated',
                    'name'       => '$LANG.tab_teaches',
                    'id'         => 'teaches',
                    'visibleCols'  => '$'.json_encode($visCols),
                ]
            );
        }
    }

    // taught by (req. workaround over the spell taught)

    // Shared cooldown

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_ITEM, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$item, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('item.tpl');

?>
