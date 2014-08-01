<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


/*  from TC wiki
    fishing_loot_template           no relation     entry is linked with ID of the fishing zone or area
    creature_loot_template          entry           many <- many        creature_template       lootid
    gameobject_loot_template        entry           many <- many        gameobject_template     data1           Only GO type 3 (CHEST) or 25 (FISHINGHOLE)
    item_loot_template              entry           many <- one         item_template           entry
    disenchant_loot_template        entry           many <- many        item_template           DisenchantID
    prospecting_loot_template       entry           many <- one         item_template           entry
    milling_loot_template           entry           many <- one         item_template           entry
    pickpocketing_loot_template     entry           many <- many        creature_template       pickpocketloot
    skinning_loot_template          entry           many <- many        creature_template       skinloot        Can also store minable/herbable items gathered from creatures
    quest_mail_loot_template        entry                               quest_template          RewMailTemplateId
    reference_loot_template         entry           many <- many        _loot_template          -mincountOrRef  In case of negative mincountOrRef
*/

class Loot
{
    public  $jsGlobals     = [];
    public  $extraCols     = [];

    private $entry         = 0;                             // depending on the lookup itemId oder templateId
    private $results       = [];
    private $lootTemplates = array(
        LOOT_REFERENCE,                                     // internal
        LOOT_ITEM,                                          // item
        LOOT_DISENCHANT,                                    // item
        LOOT_PROSPECTING,                                   // item
        LOOT_MILLING,                                       // item
        LOOT_CREATURE,                                      // npc
        LOOT_PICKPOCKET,                                    // npc
        LOOT_SKINNING,                                      // npc (see its flags for mining, herbing or actual skinning)
        LOOT_FISHING,                                       // zone
        LOOT_GAMEOBJECT,                                    // object
        LOOT_MAIL,                                          // quest || achievement
        LOOT_SPELL                                          // spell
    );

    public function &iterate()
    {
        reset($this->results);

        while (list($k, $__) = each($this->results))
            yield $this->results[$k];
    }

    public function getResult()
    {
        return $this->results;
    }

    private function createStack($l)                        // issue: TC always has an equal distribution between min/max
    {
        if (empty($l['min']) || empty($l['max']) || $l['max'] <= $l['min'])
            return null;

        $stack = [];
        for ($i = $l['min']; $i <= $l['max']; $i++)
            $stack[$i] = round(100 / (1 + $l['max'] - $l['min']), 3);

        // yes, it wants a string .. how weired is that..
        return json_encode($stack, JSON_NUMERIC_CHECK);
    }

    private function storeJSGlobals($data)
    {
        foreach ($data as $type => $jsData)
        {
            foreach ($jsData as $k => $v)
            {
                // was already set at some point with full data
                if (isset($this->jsGlobals[$type][$k]) && is_array($this->jsGlobals[$type][$k]))
                    continue;

                $this->jsGlobals[$type][$k] = $v;
            }
        }
    }

    private function getByContainerRecursive($tableName, $lootId, &$handledRefs, $groupId = 0, $baseChance = 1.0)
    {
        $loot     = [];
        $rawItems = [];

        if (!$tableName || !$lootId)
            return null;

        $rows = DB::Aowow()->select('SELECT * FROM ?# WHERE entry = ?d{ AND groupid = ?d}', $tableName, abs($lootId), $groupId ?: DBSIMPLE_SKIP);
        if (!$rows)
            return null;

        $groupChances = [];
        $nGroupEquals = [];
        foreach ($rows as $entry)
        {
            $set = array(
                'quest'         => $entry['ChanceOrQuestChance'] < 0,
                'group'         => $entry['groupid'],
                'reference'     => $lootId < 0 ? abs($lootId) : 0,
                'realChanceMod' => $baseChance
            );

            // if ($entry['lootmode'] > 1)
            // {
                $buff = [];
                for ($i = 0; $i < 8; $i++)
                    if ($entry['lootmode'] & (1 << $i))
                        $buff[] = $i + 1;

                $set['mode'] = implode(', ', $buff);
            // }
            // else
                // $set['mode'] = 0;

            /*
                modes:{"mode":8,"4":{"count":7173,"outof":17619},"8":{"count":7173,"outof":10684}}
                ignore lootmodes from sharedDefines.h use different creatures/GOs from each template
                modes.mode = b6543210
                              ||||||'dungeon heroic
                              |||||'dungeon normal
                              ||||'<empty>
                              |||'10man normal
                              ||'25man normal
                              |'10man heroic
                              '25man heroic
            */

            if ($entry['mincountOrRef'] < 0)
            {
                // bandaid.. remove when propperly handling lootmodes
                if (!in_array($entry['mincountOrRef'], $handledRefs))
                {                                                                                                   // todo (high): find out, why i used this in the first place. (don't do drugs, kids)
                    list($data, $raw) = self::getByContainerRecursive(LOOT_REFERENCE, $entry['mincountOrRef'], $handledRefs, /*$entry['groupid'],*/ 0, abs($entry['ChanceOrQuestChance'] / 100));

                    $handledRefs[] = $entry['mincountOrRef'];

                    $loot     = array_merge($loot, $data);
                    $rawItems = array_merge($rawItems, $raw);
                }

                $set['content']    = $entry['mincountOrRef'];
                $set['multiplier'] = $entry['maxcount'];
            }
            else
            {
                $rawItems[]     = $entry['item'];
                $set['content'] = $entry['item'];
                $set['min']     = $entry['mincountOrRef'];
                $set['max']     = $entry['maxcount'];
            }

            if (!isset($groupChances[$entry['groupid']]))
            {
                $groupChances[$entry['groupid']] = 0;
                $nGroupEquals[$entry['groupid']] = 0;
            }

            if ($set['quest'] || !$set['group'])
                $set['groupChance'] = abs($entry['ChanceOrQuestChance']);
            else if ($entry['groupid'] && !$entry['ChanceOrQuestChance'])
            {
                $nGroupEquals[$entry['groupid']]++;
                $set['groupChance'] = &$groupChances[$entry['groupid']];
            }
            else if ($entry['groupid'] && $entry['ChanceOrQuestChance'])
            {
                @$groupChances[$entry['groupid']] += $entry['ChanceOrQuestChance'];
                $set['groupChance'] = abs($entry['ChanceOrQuestChance']);
            }
            else                                            // shouldn't have happened
            {
                Util::addNote(U_GROUP_EMPLOYEE, 'Loot::getByContainerRecursive: unhandled case in calculating chance for item '.$entry['item'].'!');
                continue;
            }

            $loot[] = $set;
        }

        foreach (array_keys($nGroupEquals) as $k)
        {
            $sum = $groupChances[$k];
            if (!$sum)
                $sum = 0;
            else if ($sum > 100)
            {
                Util::addNote(U_GROUP_EMPLOYEE, 'Loot::getByContainerRecursive: entry '.$lootId.' / group '.$k.' has a total chance of '.number_format($sum, 2).'%. Some items cannot drop!');
                $sum = 100;
            }

            $cnt = empty($nGroupEquals[$k]) ? 1 : $nGroupEquals[$k];

            $groupChances[$k] = (100 - $sum) / $cnt;        // is applied as backReference to items with 0-chance
        }

        return [$loot, array_unique($rawItems)];
    }

    public function getByContainer($table, $entry)
    {
        $this->entry = intVal($entry);

        if (!in_array($table, $this->lootTemplates) || !$this->entry)
            return null;

        /*
            todo (high): implement conditions on loot (and conditions in general)

        also

            // if (is_array($this->entry) && in_array($table, [LOOT_CREATURE, LOOT_GAMEOBJECT])
                // iterate over the 4 available difficulties and assign modes


            modes:{"mode":1,"1":{"count":4408,"outof":16013},"4":{"count":4408,"outof":22531}}
        */
        $handledRefs = [];
        $struct = self::getByContainerRecursive($table, $this->entry, $handledRefs);
        if (!$struct)
            return false;

        $items = new ItemList(array(['i.id', $struct[1]], CFG_SQL_LIMIT_NONE));
        $this->jsGlobals = $items->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED);
        $foo = $items->getListviewData();

        // assign listview LV rows to loot rows, not the other way round! The same item may be contained multiple times
        foreach ($struct[0] as $loot)
        {
            $base = array(
                'percent' => round($loot['groupChance'] * $loot['realChanceMod'], 3),
                'group'   => $loot['group'],
                'quest'   => $loot['quest'],
                'count'   => 1                              // satisfies the sort-script
            );

            if ($_ = $loot['mode'])
                $base['mode'] = $_;

            if ($_ = $loot['reference'])
                $base['reference'] = $_;

            if ($_ = self::createStack($loot))
                $base['pctstack'] = $_;

            if ($loot['content'] > 0)                       // regular drop
            {
                if (!User::isInGroup(U_GROUP_EMPLOYEE))
                {
                    if (!isset($this->results[$loot['content']]))
                        $this->results[$loot['content']] = array_merge($foo[$loot['content']], $base, ['stack' => [$loot['min'], $loot['max']]]);
                    else
                        $this->results[$loot['content']]['percent'] += $base['percent'];
                }
                else                                        // in case of limited trash loot, check if $foo[<itemId>] exists
                    $this->results[] = array_merge($foo[$loot['content']], $base, ['stack' => [$loot['min'], $loot['max']]]);
            }
            else if (User::isInGroup(U_GROUP_EMPLOYEE))     // create dummy for ref-drop
            {
                $data = array(
                    'id'    => $loot['content'],
                    'name'  => '@REFERENCE: '.abs($loot['content']),
                    'icon'  => 'trade_engineering',
                    'stack' => [$loot['multiplier'], $loot['multiplier']]
                );
                $this->results[] = array_merge($base, $data);

                $this->jsGlobals[TYPE_ITEM][$loot['content']] = $data;
            }
        }

        // move excessive % to extra loot
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
        {
            foreach ($this->results as &$_)
            {
                if ($_['percent'] <= 100)
                    continue;

                while ($_['percent'] > 200)
                {
                    $_['stack'][0]++;
                    $_['stack'][1]++;
                    $_['percent'] -= 100;
                }

                $_['stack'][1]++;
                $_['percent'] = 100;
            }
        }
        else
        {
            $fields = ['mode', 'reference'];
            $base   = [];
            $set    = 0;
            foreach ($this->results as $foo)
            {
                foreach ($fields as $idx => $field)
                {
                    if (!isset($base[$idx]))
                        $base[$idx] = @$foo[$field];
                    else if ($base[$idx] != @$foo[$field])
                        $set |= 1 << $idx;
                }

                if ($set == (pow(2, count($fields)) - 1))
                    break;
            }

            $this->extraCols[] = "Listview.funcBox.createSimpleCol('group', 'Group', '7%', 'group')";
            foreach ($fields as $idx => $field)
                if ($set & (1 << $idx))
                    $this->extraCols[] = "Listview.funcBox.createSimpleCol('".$field."', '".Util::ucFirst($field)."', '7%', '".$field."')";
        }

        return true;
    }

    public function getByItem($entry, $maxResults = CFG_SQL_LIMIT_DEFAULT)
    {
        $this->entry = intVal($entry);

        if (!$this->entry)
            return false;

        //  [fileName, tabData, tabName, tabId, extraCols, hiddenCols, visibleCols]
        $tabsFinal  = array(
            ['item',        [], '$LANG.tab_containedin',      'contained-in-item',      [], [], []],
            ['item',        [], '$LANG.tab_disenchantedfrom', 'disenchanted-from',      [], [], []],
            ['item',        [], '$LANG.tab_prospectedfrom',   'prospected-from',        [], [], []],
            ['item',        [], '$LANG.tab_milledfrom',       'milled-from',            [], [], []],
            ['creature',    [], '$LANG.tab_droppedby',        'dropped-by',             [], [], []],
            ['creature',    [], '$LANG.tab_pickpocketedfrom', 'pickpocketed-from',      [], [], []],
            ['creature',    [], '$LANG.tab_skinnedfrom',      'skinned-from',           [], [], []],
            ['creature',    [], '$LANG.tab_minedfromnpc',     'mined-from-npc',         [], [], []],
            ['creature',    [], '$LANG.tab_salvagedfrom',     'salvaged-from',          [], [], []],
            ['creature',    [], '$LANG.tab_gatheredfromnpc',  'gathered-from-npc',      [], [], []],
            ['quest',       [], '$LANG.tab_rewardfrom',       'reward-from-quest',      [], [], []],
            ['zone',        [], '$LANG.tab_fishedin',         'fished-in-zone',         [], [], []],
            ['object',      [], '$LANG.tab_containedin',      'contained-in-object',    [], [], []],
            ['object',      [], '$LANG.tab_minedfrom',        'mined-from-object',      [], [], []],
            ['object',      [], '$LANG.tab_gatheredfrom',     'gathered-from-object',   [], [], []],
            ['object',      [], '$LANG.tab_fishedin',         'fished-in-object',       [], [], []],
            ['spell',       [], '$LANG.tab_createdby',        'created-by',             [], [], []],
            ['achievement', [], '$LANG.tab_rewardfrom',       'reward-from-achievemnt', [], [], []]
        );
        $refResults = [];
        $chanceMods = [];
        $query      =   'SELECT
                           -lt1.entry AS ARRAY_KEY,
                            IF(lt1.mincountOrRef > 0, lt1.item, lt1.mincountOrRef) AS item,
                            lt1.ChanceOrQuestChance AS chance,
                            SUM(IF(lt2.ChanceOrQuestChance = 0, 1, 0)) AS nZeroItems,
                            SUM(IF(lt2.ChanceOrQuestChance > 0, lt2.ChanceOrQuestChance, 0)) AS sumChance,
                            IF(lt1.groupid > 0, 1, 0) AS isGrouped,
                            IF(lt1.mincountOrRef > 0, lt1.mincountOrRef, 1) AS min,
                            IF(lt1.mincountOrRef > 0, lt1.maxcount, 1) AS max,
                            IF(lt1.mincountOrRef < 0, lt1.maxcount, 1) AS multiplier
                        FROM
                            ?# lt1
                        LEFT JOIN
                            ?# lt2 ON lt1.entry = lt2.entry AND lt1.groupid = lt2.groupid
                        WHERE
                            %s
                        GROUP BY lt2.entry';

        $calcChance = function ($refs, $parents = []) use (&$chanceMods)
        {
            $retData = [];
            $retKeys = [];

            foreach ($refs as $rId => $ref)
            {
                // check for possible database inconsistencies
                if (!$ref['chance'] && !$ref['isGrouped'])
                    Util::addNote(U_GROUP_EMPLOYEE, 'Loot by Item: ungrouped Item/Ref '.$ref['item'].' has 0% chance assigned!');

                if ($ref['isGrouped'] && $ref['sumChance'] > 100)
                    Util::addNote(U_GROUP_EMPLOYEE, 'Loot by Item: group with Item/Ref '.$ref['item'].' has '.number_format($ref['sumChance'], 2).'% total chance! Some items cannot drop!');

                if ($ref['isGrouped'] && $ref['sumChance'] == 100 && !$ref['chance'])
                    Util::addNote(U_GROUP_EMPLOYEE, 'Loot by Item: Item/Ref '.$ref['item'].' with adaptive chance cannot drop. Group already at 100%!');

                $chance = abs($ref['chance'] ?: (100 - $ref['sumChance']) / $ref['nZeroItems']) / 100;

                // apply inherited chanceMods
                if (isset($chanceMods[$ref['item']]))
                {
                    $chance *= $chanceMods[$ref['item']][0];
                    $chance  = 1 - pow(1 - $chance, $chanceMods[$ref['item']][1]);
                }

                // save chance for parent-ref
                $chanceMods[$rId] = [$chance, $ref['multiplier']];

                // refTemplate doesn't point to a new ref -> we are done
                if (!in_array($rId, $parents))
                {
                    $data = array(
                        'percent' => $chance,
                        'stack'   => [$ref['min'], $ref['max']],
                        'count'   => 1                      // ..and one for the sort script
                    );

                    if ($_ = self::createStack($ref))
                        $data['pctstack'] = $_;

                    // sort highest chances first
                    $i = 0;
                    for (; $i < count($retData); $i++)
                        if ($retData[$i]['percent'] < $data['percent'])
                            break;

                    array_splice($retData, $i, 0, [$data]);
                    array_splice($retKeys, $i, 0, [$rId]);
                }
            }

            return array_combine($retKeys, $retData);
        };

        /*
            get references containing the item
        */
        $newRefs = DB::Aowow()->select(
            sprintf($query, 'lt1.item = ?d AND lt1.mincountOrRef > 0'),
            LOOT_REFERENCE, LOOT_REFERENCE,
            $this->entry
        );

        while ($newRefs)
        {
            $curRefs = $newRefs;
            $newRefs = DB::Aowow()->select(
                sprintf($query, 'lt1.mincountOrRef IN (?a)'),
                LOOT_REFERENCE, LOOT_REFERENCE,
                array_keys($curRefs)
            );

            $refResults += $calcChance($curRefs, array_column($newRefs, 'item'));
        }

        /*
            search the real loot-templates for the itemId and gathered refds
        */
        for ($i = 1; $i < count($this->lootTemplates); $i++)
        {
            $result = $calcChance(DB::Aowow()->select(
                sprintf($query, '{lt1.mincountOrRef IN (?a) OR }(lt1.mincountOrRef > 0 AND lt1.item = ?d)'),
                $this->lootTemplates[$i], $this->lootTemplates[$i],
                $refResults ? array_keys($refResults) : DBSIMPLE_SKIP,
                $this->entry
            ));

            // do not skip here if $result is empty. Additional loot for spells and quest is added separately

            // format for actual use
            foreach ($result as $k => $v)
            {
                unset($result[$k]);
                $v['percent'] = round($v['percent'] * 100, 3);
                $result[abs($k)] = $v;
            }

            // cap fetched entries to the sql-limit to guarantee, that the highest chance items get selected first
            // screws with GO-loot and skinnig-loot as these templates are shared for several tabs (fish, herb, ore) (herb, ore, leather)
            $ids = array_slice(array_keys($result), 0, $maxResults);

            switch ($this->lootTemplates[$i])
            {
                case LOOT_CREATURE:     $field = 'lootId';              $tabId =  4;    break;
                case LOOT_PICKPOCKET:   $field = 'pickpocketLootId';    $tabId =  5;    break;
                case LOOT_SKINNING:     $field = 'skinLootId';          $tabId = -6;    break;      // assigned later
                case LOOT_PROSPECTING:  $field = 'id';                  $tabId =  2;    break;
                case LOOT_MILLING:      $field = 'id';                  $tabId =  3;    break;
                case LOOT_ITEM:         $field = 'id';                  $tabId =  0;    break;
                case LOOT_DISENCHANT:   $field = 'disenchantId';        $tabId =  1;    break;
                case LOOT_FISHING:      $field = 'id';                  $tabId = 11;    break;      // subAreas are currently ignored
                case LOOT_GAMEOBJECT:
                    if (!$ids)
                        break;

                    $srcObj = new GameObjectList(array(['lootId', $ids]));
                    if ($srcObj->error)
                        break;

                    $srcData = $srcObj->getListviewData();

                    foreach ($srcObj->iterate() as $curTpl)
                    {
                        switch ($curTpl['type'])
                        {
                            case 25: $tabId = 15; break;    // fishing node
                            case -3: $tabId = 14; break;    // herb
                            case -4: $tabId = 13; break;    // vein
                            default: $tabId = 12; break;    // general chest loot
                        }

                        $tabsFinal[$tabId][1][] = array_merge($srcData[$srcObj->id], $result[$srcObj->getField('lootId')]);
                        $tabsFinal[$tabId][4][] = 'Listview.extraCols.percent';
                        if ($tabId != 15)
                            $tabsFinal[$tabId][6][] = 'skill';
                    }
                    break;
                case LOOT_MAIL:
                    $conditions = array(['rewardChoiceItemId1', $this->entry], ['rewardChoiceItemId2', $this->entry], ['rewardChoiceItemId3', $this->entry], ['rewardChoiceItemId4', $this->entry], ['rewardChoiceItemId5', $this->entry],
                                        ['rewardChoiceItemId6', $this->entry], ['rewardItemId1', $this->entry],       ['rewardItemId2', $this->entry],       ['rewardItemId3', $this->entry],       ['rewardItemId4', $this->entry],
                                        'OR');
                    if ($ids)
                        $conditions[] = ['rewardMailTemplateId', $ids];

                    $srcObj = new QuestList($conditions);
                    if (!$srcObj->error)
                    {
                        self::storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
                        $srcData = $srcObj->getListviewData();

                        foreach ($srcObj->iterate() as $_)
                            $tabsFinal[10][1][] = array_merge($srcData[$srcObj->id], empty($result[$srcObj->id]) ? ['percent' => -1] : $result[$srcObj->id]);
                    }

                    /*
                        todo: search for achievements here
                        $tabsFinal[17]
                    */

                    break;
                case LOOT_SPELL:
                    $conditions = ['OR', ['effect1CreateItemId', $this->entry], ['effect2CreateItemId', $this->entry], ['effect3CreateItemId', $this->entry]];
                    if ($ids)
                        $conditions[] = ['id', $ids];

                    $srcObj = new SpellList($conditions);
                    if (!$srcObj->error)
                    {
                        self::storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                        $srcData = $srcObj->getListviewData();

                        if (!empty($result))
                            $tabsFinal[16][4][] = 'Listview.extraCols.percent';

                        if ($srcObj->hasSetFields(['reagent1']))
                            $tabsFinal[16][6][] = 'reagents';

                        foreach ($srcObj->iterate() as $_)
                            $tabsFinal[16][1][] = array_merge($srcData[$srcObj->id], empty($result[$srcObj->id]) ? ['percent' => -1] : $result[$srcObj->id]);
                    }
                    break;
            }

            if (!$ids)
                continue;

            switch ($tabsFinal[abs($tabId)][0])
            {
                case 'creature':                            // new CreatureList
                case 'item':                                // new ItemList
                case 'zone':                                // new ZoneList
                    $oName  = ucFirst($tabsFinal[abs($tabId)][0]).'List';
                    $srcObj = new $oName(array([$field, $ids]));
                    if (!$srcObj->error)
                    {
                        $srcData = $srcObj->getListviewData();
                        self::storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                        foreach ($srcObj->iterate() as $curTpl)
                        {
                            if ($tabId < 0 && $curTpl['typeFlags'] & NPC_TYPEFLAG_HERBLOOT)
                                $tabId = 9;
                            else if ($tabId < 0 && $curTpl['typeFlags'] & NPC_TYPEFLAG_ENGINEERLOOT)
                                $tabId = 8;
                            else if ($tabId < 0 && $curTpl['typeFlags'] & NPC_TYPEFLAG_MININGLOOT)
                                $tabId = 7;
                            else if ($tabId < 0)
                                $tabId = abs($tabId);       // general case (skinning)

                            $tabsFinal[$tabId][1][] = array_merge($srcData[$srcObj->id], $result[$srcObj->getField($field)]);
                            $tabsFinal[$tabId][4][] = 'Listview.extraCols.percent';
                        }
                    }
                    break;
            }
        }

        $this->results = $tabsFinal;

        return true;
    }
}

?>