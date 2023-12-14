<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


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
    reference_loot_template         entry           many <- many        *_loot_template         reference
*/

class Loot
{
    public  $jsGlobals     = [];
    public  $extraCols     = [];

    private $entry         = 0;                             // depending on the lookup itemId oder templateId
    private $results       = [];
    private $chanceMods    = [];
    private $lootTemplates = array(
        LOOT_REFERENCE,                                     // internal
        LOOT_ITEM,                                          // item
        LOOT_DISENCHANT,                                    // item
        LOOT_PROSPECTING,                                   // item
        LOOT_MILLING,                                       // item
        LOOT_CREATURE,                                      // npc
        LOOT_PICKPOCKET,                                    // npc
        LOOT_SKINNING,                                      // npc (see its flags for mining, herbing, salvaging or actual skinning)
        LOOT_FISHING,                                       // zone
        LOOT_GAMEOBJECT,                                    // object (see its lockType for mining, herbing, fishing or generic looting)
        LOOT_MAIL,                                          // quest + achievement
        LOOT_SPELL                                          // spell
    );

    public function &iterate() : iterable
    {
        reset($this->results);

        foreach ($this->results as $k => $__)
            yield $k => $this->results[$k];
    }

    public function getResult() : array
    {
        return $this->results;
    }

    private function createStack(array $l) : string         // issue: TC always has an equal distribution between min/max
    {
        if (empty($l['min']) || empty($l['max']) || $l['max'] <= $l['min'])
            return '';

        $stack = [];
        for ($i = $l['min']; $i <= $l['max']; $i++)
            $stack[$i] = round(100 / (1 + $l['max'] - $l['min']), 3);

        // yes, it wants a string .. how weired is that..
        return json_encode($stack, JSON_NUMERIC_CHECK);     // do not replace with Util::toJSON !
    }

    private function storeJSGlobals(array $data) :  void
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

    private function calcChance(array $refs, array $parents = []) : array
    {
        $retData = [];
        $retKeys = [];

        foreach ($refs as $rId => $ref)
        {
            // check for possible database inconsistencies
            if (!$ref['chance'] && !$ref['isGrouped'])
                trigger_error('Loot by Item: Ungrouped Item/Ref '.$ref['item'].' has 0% chance assigned!', E_USER_WARNING);

            if ($ref['isGrouped'] && $ref['sumChance'] > 100)
                trigger_error('Loot by Item: Group with Item/Ref '.$ref['item'].' has '.number_format($ref['sumChance'], 2).'% total chance! Some items cannot drop!', E_USER_WARNING);

            if ($ref['isGrouped'] && $ref['sumChance'] >= 100 && !$ref['chance'])
                trigger_error('Loot by Item: Item/Ref '.$ref['item'].' with adaptive chance cannot drop. Group already at 100%!', E_USER_WARNING);

            $chance = abs($ref['chance'] ?: (100 - $ref['sumChance']) / $ref['nZeroItems']) / 100;

            // apply inherited chanceMods
            if (isset($this->chanceMods[$ref['item']]))
            {
                $chance *= $this->chanceMods[$ref['item']][0];
                $chance  = 1 - pow(1 - $chance, $this->chanceMods[$ref['item']][1]);
            }

            // save chance for parent-ref
            $this->chanceMods[$rId] = [$chance, $ref['multiplier']];

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
    }

    private function getByContainerRecursive(string $tableName, int $lootId, array &$handledRefs, int $groupId = 0, float $baseChance = 1.0) : ?array
    {
        $loot     = [];
        $rawItems = [];

        if (!$tableName || !$lootId)
            return null;

        $rows = DB::World()->select('SELECT * FROM ?# WHERE entry = ?d{ AND groupid = ?d}', $tableName, $lootId, $groupId ?: DBSIMPLE_SKIP);
        if (!$rows)
            return null;

        $groupChances = [];
        $nGroupEquals = [];
        foreach ($rows as $entry)
        {
            $set = array(
                'quest'         => $entry['QuestRequired'],
                'group'         => $entry['GroupId'],
                'parentRef'     => $tableName == LOOT_REFERENCE ? $lootId : 0,
                'realChanceMod' => $baseChance,
                'groupChance'   => 0
            );

            // if ($entry['LootMode'] > 1)
            // {
                $buff = [];
                for ($i = 0; $i < 8; $i++)
                    if ($entry['LootMode'] & (1 << $i))
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

            if ($entry['Reference'])
            {
                // bandaid.. remove when propperly handling lootmodes
                if (!in_array($entry['Reference'], $handledRefs))
                {                                                                                                   // todo (high): find out, why i used this in the first place. (don't do drugs, kids)
                    [$data, $raw] = self::getByContainerRecursive(LOOT_REFERENCE, $entry['Reference'], $handledRefs, /*$entry['GroupId'],*/ 0, $entry['Chance'] / 100);

                    $handledRefs[] = $entry['Reference'];

                    $loot     = array_merge($loot, $data);
                    $rawItems = array_merge($rawItems, $raw);
                }
                $set['reference']  = $entry['Reference'];
                $set['multiplier'] = $entry['MaxCount'];
            }
            else
            {
                $rawItems[]     = $entry['Item'];
                $set['content'] = $entry['Item'];
                $set['min']     = $entry['MinCount'];
                $set['max']     = $entry['MaxCount'];
            }

            if (!isset($groupChances[$entry['GroupId']]))
            {
                $groupChances[$entry['GroupId']] = 0;
                $nGroupEquals[$entry['GroupId']] = 0;
            }

            if ($set['quest'] || !$set['group'])
                $set['groupChance'] = $entry['Chance'];
            else if ($entry['GroupId'] && !$entry['Chance'])
            {
                $nGroupEquals[$entry['GroupId']]++;
                $set['groupChance'] = &$groupChances[$entry['GroupId']];
            }
            else if ($entry['GroupId'] && $entry['Chance'])
            {
                $set['groupChance'] = $entry['Chance'];

                if (!$entry['Reference'])
                {
                    if (empty($groupChances[$entry['GroupId']]))
                        $groupChances[$entry['GroupId']] = 0;

                    $groupChances[$entry['GroupId']] += $entry['Chance'];
                }
            }
            else                                            // shouldn't have happened
            {
                trigger_error('Unhandled case in calculating chance for item '.$entry['Item'].'!', E_USER_WARNING);
                continue;
            }

            $loot[] = $set;
        }

        foreach (array_keys($nGroupEquals) as $k)
        {
            $sum = $groupChances[$k];
            if (!$sum)
                $sum = 0;
            else if ($sum >= 100.01)
            {
                trigger_error('Loot entry '.$lootId.' / group '.$k.' has a total chance of '.number_format($sum, 2).'%. Some items cannot drop!', E_USER_WARNING);
                $sum = 100;
            }
            // is applied as backReference to items with 0-chance
            $groupChances[$k] = (100 - $sum) / ($nGroupEquals[$k] ?: 1);
        }

        return [$loot, array_unique($rawItems)];
    }

    public function getByContainer(string $table, int $entry): bool
    {
        $this->entry = intVal($entry);

        if (!in_array($table, $this->lootTemplates) || !$this->entry)
            return false;

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

            if ($_ = $loot['parentRef'])
                $base['reference'] = $_;

            if ($_ = self::createStack($loot))
                $base['pctstack'] = $_;

            if (empty($loot['reference']))                  // regular drop
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
                    'id'    => $loot['reference'],
                    'name'  => '@REFERENCE: '.$loot['reference'],
                    'icon'  => 'trade_engineering',
                    'stack' => [$loot['multiplier'], $loot['multiplier']]
                );
                $this->results[] = array_merge($base, $data);

                $this->jsGlobals[Type::ITEM][$loot['reference']] = $data;
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
                    $val = isset($foo[$field]) ? $foo[$field] : 0;
                    if (!isset($base[$idx]))
                        $base[$idx] = $val;
                    else if ($base[$idx] != $val)
                        $set |= 1 << $idx;
                }

                if ($set == (pow(2, count($fields)) - 1))
                    break;
            }

            $this->extraCols[] = "\$Listview.funcBox.createSimpleCol('group', 'Group', '7%', 'group')";
            foreach ($fields as $idx => $field)
                if ($set & (1 << $idx))
                    $this->extraCols[] = "\$Listview.funcBox.createSimpleCol('".$field."', '".Util::ucFirst($field)."', '7%', '".$field."')";
        }

        return true;
    }

    public function getByItem(int $entry, int $maxResults = CFG_SQL_LIMIT_DEFAULT, array $lootTableList = []) : bool
    {
        $this->entry = intVal($entry);

        if (!$this->entry)
            return false;

        //  [fileName, tabData, tabName, tabId, extraCols, hiddenCols, visibleCols]
        $tabsFinal  = array(
            [Type::ITEM,        [], '$LANG.tab_containedin',      'contained-in-item',       [], [], []],
            [Type::ITEM,        [], '$LANG.tab_disenchantedfrom', 'disenchanted-from',       [], [], []],
            [Type::ITEM,        [], '$LANG.tab_prospectedfrom',   'prospected-from',         [], [], []],
            [Type::ITEM,        [], '$LANG.tab_milledfrom',       'milled-from',             [], [], []],
            [Type::NPC,         [], '$LANG.tab_droppedby',        'dropped-by',              [], [], []],
            [Type::NPC,         [], '$LANG.tab_pickpocketedfrom', 'pickpocketed-from',       [], [], []],
            [Type::NPC,         [], '$LANG.tab_skinnedfrom',      'skinned-from',            [], [], []],
            [Type::NPC,         [], '$LANG.tab_minedfromnpc',     'mined-from-npc',          [], [], []],
            [Type::NPC,         [], '$LANG.tab_salvagedfrom',     'salvaged-from',           [], [], []],
            [Type::NPC,         [], '$LANG.tab_gatheredfromnpc',  'gathered-from-npc',       [], [], []],
            [Type::QUEST,       [], '$LANG.tab_rewardfrom',       'reward-from-quest',       [], [], []],
            [Type::ZONE,        [], '$LANG.tab_fishedin',         'fished-in-zone',          [], [], []],
            [Type::OBJECT,      [], '$LANG.tab_containedin',      'contained-in-object',     [], [], []],
            [Type::OBJECT,      [], '$LANG.tab_minedfrom',        'mined-from-object',       [], [], []],
            [Type::OBJECT,      [], '$LANG.tab_gatheredfrom',     'gathered-from-object',    [], [], []],
            [Type::OBJECT,      [], '$LANG.tab_fishedin',         'fished-in-object',        [], [], []],
            [Type::SPELL,       [], '$LANG.tab_createdby',        'created-by',              [], [], []],
            [Type::ACHIEVEMENT, [], '$LANG.tab_rewardfrom',       'reward-from-achievement', [], [], []]
        );
        $refResults = [];
        $query      =   'SELECT
                            lt1.entry AS ARRAY_KEY,
                            IF(lt1.reference = 0, lt1.item, lt1.reference) AS item,
                            lt1.chance,
                            SUM(IF(lt2.chance = 0, 1, 0)) AS nZeroItems,
                            SUM(IF(lt2.reference = 0, lt2.chance, 0)) AS sumChance,
                            IF(lt1.groupid > 0, 1, 0) AS isGrouped,
                            IF(lt1.reference = 0, lt1.mincount, 1) AS min,
                            IF(lt1.reference = 0, lt1.maxcount, 1) AS max,
                            IF(lt1.reference > 0, lt1.maxcount, 1) AS multiplier
                        FROM
                            ?# lt1
                        LEFT JOIN
                            ?# lt2 ON lt1.entry = lt2.entry AND lt1.groupid = lt2.groupid
                        WHERE
                            %s
                        GROUP BY lt2.entry, lt2.groupid';

        /*
            get references containing the item
        */
        $newRefs = DB::World()->select(
            sprintf($query, 'lt1.item = ?d AND lt1.reference = 0'),
            LOOT_REFERENCE, LOOT_REFERENCE,
            $this->entry
        );

        while ($newRefs)
        {
            $curRefs = $newRefs;
            $newRefs = DB::World()->select(
                sprintf($query, 'lt1.reference IN (?a)'),
                LOOT_REFERENCE, LOOT_REFERENCE,
                array_keys($curRefs)
            );

            $refResults += $this->calcChance($curRefs, array_column($newRefs, 'item'));
        }

        /*
            search the real loot-templates for the itemId and gathered refds
        */
        for ($i = 1; $i < count($this->lootTemplates); $i++)
        {
            if ($lootTableList && !in_array($this->lootTemplates[$i], $lootTableList))
                continue;

            $result = $this->calcChance(DB::World()->select(
                sprintf($query, '{lt1.reference IN (?a) OR }(lt1.reference = 0 AND lt1.item = ?d)'),
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
                        continue 2;

                    $srcObj = new GameObjectList(array(['lootId', $ids]));
                    if ($srcObj->error)
                        continue 2;

                    $srcData = $srcObj->getListviewData();

                    foreach ($srcObj->iterate() as $curTpl)
                    {
                        switch ($curTpl['typeCat'])
                        {
                            case 25: $tabId = 15; break;    // fishing node
                            case -3: $tabId = 14; break;    // herb
                            case -4: $tabId = 13; break;    // vein
                            default: $tabId = 12; break;    // general chest loot
                        }

                        $tabsFinal[$tabId][1][] = array_merge($srcData[$srcObj->id], $result[$srcObj->getField('lootId')]);
                        $tabsFinal[$tabId][4][] = '$Listview.extraCols.percent';
                        if ($tabId != 15)
                            $tabsFinal[$tabId][6][] = 'skill';
                    }
                    continue 2;
                case LOOT_MAIL:
                    // quest part
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

                    // achievement part
                    $conditions = array(['itemExtra', $this->entry]);
                    if ($ar = DB::World()->selectCol('SELECT ID FROM achievement_reward WHERE ItemID = ?d{ OR MailTemplateID IN (?a)}', $this->entry, $ids ?: DBSIMPLE_SKIP))
                        array_push($conditions, ['id', $ar], 'OR');

                    $srcObj = new AchievementList($conditions);
                    if (!$srcObj->error)
                    {
                        self::storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
                        $srcData = $srcObj->getListviewData();

                        foreach ($srcObj->iterate() as $_)
                            $tabsFinal[17][1][] = array_merge($srcData[$srcObj->id], empty($result[$srcObj->id]) ? ['percent' => -1] : $result[$srcObj->id]);

                        $tabsFinal[17][5][] = 'rewards';
                        $tabsFinal[17][6][] = 'category';
                    }
                    continue 2;
                case LOOT_SPELL:
                    $conditions = array(
                        'OR',
                        ['AND', ['effect1CreateItemId', $this->entry], ['OR', ['effect1Id', SpellList::EFFECTS_ITEM_CREATE], ['effect1AuraId', SpellList::AURAS_ITEM_CREATE]]],
                        ['AND', ['effect2CreateItemId', $this->entry], ['OR', ['effect2Id', SpellList::EFFECTS_ITEM_CREATE], ['effect2AuraId', SpellList::AURAS_ITEM_CREATE]]],
                        ['AND', ['effect3CreateItemId', $this->entry], ['OR', ['effect3Id', SpellList::EFFECTS_ITEM_CREATE], ['effect3AuraId', SpellList::AURAS_ITEM_CREATE]]],
                    );
                    if ($ids)
                        $conditions[] = ['id', $ids];

                    $srcObj = new SpellList($conditions);
                    if (!$srcObj->error)
                    {
                        self::storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
                        $srcData = $srcObj->getListviewData();

                        if (!empty($result))
                            $tabsFinal[16][4][] = '$Listview.extraCols.percent';

                        if ($srcObj->hasSetFields(['reagent1']))
                            $tabsFinal[16][6][] = 'reagents';

                        foreach ($srcObj->iterate() as $_)
                            $tabsFinal[16][1][] = array_merge($srcData[$srcObj->id], empty($result[$srcObj->id]) ? ['percent' => -1] : $result[$srcObj->id]);
                    }
                    continue 2;
            }

            if (!$ids)
                continue;

            $parentData = [];
            switch ($tabsFinal[abs($tabId)][0])
            {
                case TYPE::NPC:                             // new CreatureList
                    if ($baseIds = DB::Aowow()->selectCol(
                       'SELECT `difficultyEntry1` AS ARRAY_KEY, `id` FROM ?_creature WHERE difficultyEntry1 IN (?a) UNION
                        SELECT `difficultyEntry2` AS ARRAY_KEY, `id` FROM ?_creature WHERE difficultyEntry2 IN (?a) UNION
                        SELECT `difficultyEntry3` AS ARRAY_KEY, `id` FROM ?_creature WHERE difficultyEntry3 IN (?a)',
                        $ids, $ids, $ids))
                        {
                            $parentObj = new CreatureList(array(['id', $baseIds]));
                            if (!$parentObj->error)
                            {
                                self::storeJSGlobals($parentObj->getJSGlobals());
                                $parentData = $parentObj->getListviewData();
                                $ids = array_diff($ids, $baseIds);
                            }
                        }

                case Type::ITEM:                            // new ItemList
                case Type::ZONE:                            // new ZoneList
                    $srcObj = Type::newList($tabsFinal[abs($tabId)][0], array([$field, $ids]));
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

                            if (($p = $srcObj->getField('parentId')) && ($d = $parentData[$p] ?? null))
                                $tabsFinal[$tabId][1][] = array_merge($d,                    $result[$srcObj->getField($field)]);
                            else
                                $tabsFinal[$tabId][1][] = array_merge($srcData[$srcObj->id], $result[$srcObj->getField($field)]);

                            $tabsFinal[$tabId][4][] = '$Listview.extraCols.percent';
                        }
                    }
                    break;
            }
        }

        foreach ($tabsFinal as $tabId => $data)
        {
            $tabData = array(
                'data' => $data[1],
                'name' => $data[2],
                'id'   => $data[3]
            );

            if ($data[4])
                $tabData['extraCols'] = array_unique($data[4]);

            if ($data[5])
                $tabData['hiddenCols'] = array_unique($data[5]);

            if ($data[6])
                $tabData['visibleCols'] = array_unique($data[6]);

            $this->results[$tabId] = [Type::getFileString($data[0]), $tabData];
        }

        return true;
    }
}

?>
