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

    $_flags     = $item->getField('flags');
    $_slot      = $item->getField('slot');
    $_subClass  = $item->getField('subClass');
    $_class     = $item->getField('class');
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
    $cost = '';
    if ($_ = $item->getField('buyPrice'))
        $cost .= '[money='.$_.']';

    if ($_ = $item->getExtendedCost())
        foreach ($_ as $c => $qty)
            $cost .= '[currency='.$c.' amount='.$qty.']';

    if ($cost)
        $quickInfo[] = Lang::$item['cost'].Lang::$colon.$cost.$each;

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

    $pageData = array(
        'infobox'  => $quickInfo ? '[ul][li]'.implode('[/li][li]', $quickInfo).'[/li][/ul]' : null,
        'relTabs'  => [],
        'tooltip'  => $item->renderTooltip([], true),
        'path'     => [0, 0],
        'title'    => [$item->getField('name', true), Util::ucFirst(Lang::$game['item'])],
        'pageText' => [],
        'buttons'  => in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) || $item->getField('gemEnchantmentId'),
        'page'     => array(
            'color'     => Util::$rarityColorStings[$item->getField('quality')],
            'quality'   => $item->getField('quality'),
            'icon'      => $item->getField('iconString'),
            'name'      => $item->getField('name', true),
            'displayId' => in_array($_class, [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) && $item->getField('displayId') ? $item->getField('displayId') : null,
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

        // merge identical stats and names for normal users (e.g. spellPower of a specific school became generel spellPower with 3.0)
        if (!User::isInGroup(U_GROUP_STAFF))
        {
            for ($i = 1; $i < count($pageData['page']['subItems']); $i++)
            {
                $prev = &$pageData['page']['subItems'][$i-1];
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

    // tabs: this item is contained in..
    $sourceTabs = array(
    //   0 => refLoot
         1 => ['item',     '$LANG.tab_containedin',      'contained-in-item',    [], [], []],
         2 => ['item',     '$LANG.tab_disenchantedfrom', 'disenchanted-from',    [], [], []],
         3 => ['item',     '$LANG.tab_prospectedfrom',   'prospected-from',      [], [], []],
         4 => ['item',     '$LANG.tab_milledfrom',       'milled-from',          [], [], []],
         5 => ['creature', '$LANG.tab_droppedby',        'dropped-by',           [], [], []],
         6 => ['creature', '$LANG.tab_pickpocketedfrom', 'pickpocketed-from',    [], [], []],
         7 => ['creature', '$LANG.tab_skinnedfrom',      'skinned-from',         [], [], []],
         8 => ['creature', '$LANG.tab_minedfromnpc',     'mined-from-npc',       [], [], []],
         9 => ['creature', '$LANG.tab_salvagedfrom',     'salvaged-from',        [], [], []],
        10 => ['creature', '$LANG.tab_gatheredfromnpc',  'gathered-from-npc',    [], [], []],
        11 => ['quest',    '$LANG.tab_rewardfrom',       'reward-from-quest',    [], [], []],
        12 => ['zone',     '$LANG.tab_fishedin',         'fished-in-zone',       [], [], []],
        13 => ['object',   '$LANG.tab_containedin',      'contained-in-object',  [], [], []],
        14 => ['object',   '$LANG.tab_minedfrom',        'mined-from-object',    [], [], []],
        15 => ['object',   '$LANG.tab_gatheredfrom',     'gathered-from-object', [], [], []],
        16 => ['object',   '$LANG.tab_fishedin',         'fished-in-object',     [], [], []],
        17 => ['spell',    '$LANG.tab_createdby',        'created-by',           [], [], []]
    );

    $data      = [];
    $questLoot = [];
    $spellLoot = [];
    $sources   = Util::getLootSource($_id);
    foreach ($sources as $lootTpl => $lootData)
    {
        // cap fetched entries to the sql-limit to guarantee, that the highest chance items get selected first
        $ids = array_slice(array_keys($lootData), 0, $AoWoWconf['sqlLimit']);

        switch ($lootTpl)
        {
            case LOOT_CREATURE:
                $srcType = new CreatureList(array(['ct.lootId', $ids]));
                $srcType->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $_)
                    $data[5][] = array_merge($srcData[$srcType->id], $lootData[$srcType->getField('lootId')]);

                $sourceTabs[5][3][] = 'Listview.extraCols.percent';
                break;
            case LOOT_PICKPOCKET:
                $srcType = new CreatureList(array(['ct.pickpocketLootId', $ids]));
                $srcType->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $_)
                    $data[6][] = array_merge($srcData[$srcType->id], $lootData[$srcType->getField('pickpocketLootId')]);

                $sourceTabs[6][3][] = 'Listview.extraCols.percent';
                break;
            case LOOT_SKINNING:
                $srcType = new CreatureList(array(['ct.skinLootId', $ids]));
                $srcType->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $curTpl)
                {
                    $tabId = 7;                             // general case (skinning)
                    if ($curTpl['type_flags'] & NPC_TYPEFLAG_HERBLOOT)
                        $tabId = 10;
                    else if ($curTpl['type_flags'] & NPC_TYPEFLAG_ENGINEERLOOT)
                        $tabId = 9;
                    else if ($curTpl['type_flags'] & NPC_TYPEFLAG_MININGLOOT)
                        $tabId = 8;

                    $data[$tabId][] = array_merge($srcData[$srcType->id], $lootData[$srcType->getField('skinLootId')]);
                    $sourceTabs[$tabId][3][] = 'Listview.extraCols.percent';
                }

                break;
            case LOOT_FISHING:
                // subAreas are currently ignored
                $srcType = new ZoneList(array(['id', $ids]));
                $srcType->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $_)
                    $data[12][] = array_merge($srcData[$srcType->id], $lootData[$srcType->id]);

                $sourceTabs[12][3][] = 'Listview.extraCols.percent';
                break;
            case LOOT_GAMEOBJECT:
                $srcType = new GameObjectList(array(['type', [OBJECT_CHEST, OBJECT_FISHINGHOLE]], ['data1', $ids]));
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $curTpl)
                {
                    $tabId = 13;                            // general chest loot
                    if ($curTpl['type'] == -4)              // vein
                        $tabId = 14;
                    else if ($curTpl['type'] == -3)         // herb
                        $tabId = 15;
                    else if ($curTpl['type'] == 25)         // fishing node
                        $tabId = 16;

                    $data[$tabId][] = array_merge($srcData[$srcType->id], $lootData[$srcType->getField('lootId')]);
                    $sourceTabs[$tabId][3][] = 'Listview.extraCols.percent';
                    $sourceTabs[$tabId][5][] = 'skill';     // conflicts a bit with fishing nodes (no real requirement)
                }
                break;
            case LOOT_PROSPECTING:
                $sourceTab = 3;
            case LOOT_MILLING:
                if (!isset($sourceTab))
                    $sourceTab = 4;
            case LOOT_ITEM:
                if (!isset($sourceTab))
                    $sourceTab = 1;

                $srcType = new ItemList(array(['i.id', $ids]));
                $srcType->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $_)
                    $data[$sourceTab][] = array_merge($srcData[$srcType->id], $lootData[$srcType->id]);

                $sourceTabs[$sourceTab][3][] = 'Listview.extraCols.percent';
                break;
            case LOOT_DISENCHANT:
                $srcType = new ItemList(array(['i.disenchantId', $ids]));
                $srcType->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
                $srcData = $srcType->getListviewData();

                foreach ($srcType->iterate() as $_)
                    $data[2][] = array_merge($srcData[$srcType->id], $lootData[$srcType->getField('disenchantId')]);

                $sourceTabs[2][3][] = 'Listview.extraCols.percent';
                break;
            case LOOT_QUEST:
                // merge regular quest rewards into quest_mail_loot_template results
                $questLoot = $ids;
                break;
            case LOOT_SPELL:
                // merge with "created by [spell]"
                $spellLoot = $ids;
                break;
        }
    }

    // merge quest rewards with quest_mail_loot
    $conditions = array(
        'OR',
        ['RewardChoiceItemId1', $_id], ['RewardChoiceItemId2', $_id], ['RewardChoiceItemId3', $_id], ['RewardChoiceItemId4', $_id], ['RewardChoiceItemId5', $_id],
        ['RewardChoiceItemId6', $_id], ['RewardItemId1', $_id],       ['RewardItemId2', $_id],       ['RewardItemId3', $_id],       ['RewardItemId4', $_id],
    );

    if ($questLoot)
        $conditions[] = ['qt.RewardMailTemplateId', $questLoot];

    $questLoot = new QuestList($conditions);
    if (!$questLoot->error)
    {
        $questLoot->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_REWARDS);
        $data[11] = $questLoot->getListviewData();
    }

    // merge spell_loot with "created by [spell]"
    $conditions = ['OR', ['effect1CreateitemId', $_id], ['effect2CreateitemId', $_id], ['effect3CreateitemId', $_id]];
    if ($spellLoot)
        $conditions[] = ['id', $spellLoot];

    $spellLoot = new SpellList($conditions);
    if (!$spellLoot->error)
    {
        $spellLoot->addGlobalsToJscript($smarty, GLOBALINFO_SELF | GLOBALINFO_RELATED);
        $spellData = $spellLoot->getListviewData();

        if (!empty($sources[LOOT_SPELL]))
            $sourceTabs[17][3][] = 'Listview.extraCols.percent';

        if ($spellLoot->hasSetFields(['reagent1']))
            $sourceTabs[17][5][] = 'reagents';

        foreach ($spellLoot->iterate() as $_)
        {
            if (!empty($sources[LOOT_SPELL][$spellLoot->id]))
                $data[17][] = array_merge($spellData[$spellLoot->id], $sources[LOOT_SPELL][$spellLoot->id]);
            else
                $data[17][] = array_merge($spellData[$spellLoot->id], ['percent' => -1]);
        }
    }

    foreach ($sourceTabs as $k => $tab)
    {
        if (empty($data[$k]))
            continue;

        $pageData['relTabs'][] = array(
            'file'   => $tab[0],
            'data'   => $data[$k],
            'params' => [
                'tabs'        => '$tabsRelated',
                'name'        => $tab[1],
                'id'          => $tab[2],
                'extraCols'   => $tab[3] ? '$['.implode(', ', array_unique($tab[3])).']' : null,
                'hiddenCols'  => $tab[4] ? '$['.implode(', ', array_unique($tab[4])).']' : null,
                'visibleCols' => $tab[5] ? '$'.json_encode($tab[5]) : null
            ]
        );
    }

    // tabs: this item contains
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
        $conditions = array(
            'OR',
            ['AND', ['data0', $lockIds], ['type', [OBJECT_QUESTGIVER, OBJECT_CHEST, OBJECT_TRAP, OBJECT_GOOBER, OBJECT_CAMERA, OBJECT_FLAGSTAND, OBJECT_FLAGDROP]]],
            ['AND', ['data1', $lockIds], ['type', [OBJECT_DOOR, OBJECT_BUTTON]]]
        );

        $lockedObj = new GameObjectList($conditions);
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
    $saItems = new ItemList(array(['id', $_id, '!'], ['name_loc'.User::$localeId, $item->getField('name', true)]));
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

    // tab: starts (quest) [omited, because the questlink IS ALREADY in the item tooltip]

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
    if ($model = $item->getField('model'))
    {
        $sameModel = new ItemList(array(['model', $model], ['id', $_id, '!']));
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

    // sold by [consult itemExtendedCost]

    // currency for

    // teaches

    // Shared cooldown

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
