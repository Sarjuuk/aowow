<?php

namespace Aowow;

use stdClass;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LootByContainer extends Loot
{
    public array $extraCols = [];

    private array $knownRefs     = [];                      // known ref loot results (can be reused)

    /**
     * @return array found loot result
     */
    public function getResult() : array
    {
        return $this->results;
    }

    /**
     * recurse through reference loot while applying modifiers from parent container
     *
     * @param string $tableName     a known loot template table name
     * @param int    $lootId        a loot template entry
     * @param int    $groupId       [optional] limit result to provided loot group
     * @param float  $baseChance    [optional] chance multiplier passed down from parent container
     * @return array [[<array>lootRows], [<int>itemIds]]
     */
    private function getByContainerRecursive(string $tableName, int $lootId, int $groupId = 0, float $baseChance = 1.0) : array
    {
        $loot     = [];
        $rawItems = [];

        if (!$tableName || !$lootId)
            return [null, null];

        $rows = DB::World()->select('SELECT * FROM ?# WHERE entry = ?d{ AND groupid = ?d}', $tableName, $lootId, $groupId ?: DBSIMPLE_SKIP);
        if (!$rows)
            return [null, null];

        $groupChances = [];
        $nGroupEquals = [];
        $cnd = new Conditions();
        foreach ($rows as $entry)
        {
            $set = array(
                'quest'         => $entry['QuestRequired'],
                'group'         => $entry['GroupId'],
                'parentRef'     => $tableName == self::REFERENCE ? $lootId : 0,
                'realChanceMod' => $baseChance,
                'groupChance'   => 0
            );

            if ($entry['QuestRequired'])
                foreach (DB::Aowow()->selectCol('SELECT id FROM ?_quests WHERE (`reqSourceItemId1` = ?d OR `reqSourceItemId2` = ?d OR `reqSourceItemId3` = ?d OR `reqSourceItemId4` = ?d OR `reqItemId1` = ?d OR `reqItemId2` = ?d OR `reqItemId3` = ?d OR `reqItemId4` = ?d OR `reqItemId5` = ?d OR `reqItemId6` = ?d) AND (`cuFlags` & ?d) = 0',
                    $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], $entry['Item'], CUSTOM_EXCLUDE_FOR_LISTVIEW | CUSTOM_UNAVAILABLE) as $questId)
                    $cnd->addExternalCondition(Conditions::lootTableToConditionSource($tableName), $lootId . ':' . $entry['Item'], [Conditions::QUESTTAKEN, $questId], true);

            // TC 'mode' (dynamic loot modifier)
            $buff = [];
            for ($i = 0; $i < 8; $i++)
                if ($entry['LootMode'] & (1 << $i))
                    $buff[] = $i + 1;

            $set['mode'] = implode(', ', $buff);

            if ($entry['Reference'])
            {
                if (!in_array($entry['Reference'], $this->knownRefs))
                    $this->knownRefs[$entry['Reference']] = $this->getByContainerRecursive(self::REFERENCE, $entry['Reference'], 0, $entry['Chance'] / 100);

                [$data, $raw] = $this->knownRefs[$entry['Reference']];

                $loot     = array_merge($loot, $data);
                $rawItems = array_merge($rawItems, $raw);

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

        if ($cnd->getBySource(Conditions::lootTableToConditionSource($tableName), group: $lootId)->prepare())
        {
            $this->storeJSGlobals($cnd->getJsGlobals());
            $cnd->toListviewColumn($loot, $this->extraCols, $lootId, 'content');
        }

        return [$loot, array_unique($rawItems)];
    }

    /**
     * fetch loot for given loot container and optionally merge multiple container while adding mode info.
     * If difficultyBit is 0, no merge will occur
     *
     * @param  string $table        a known loote template table name
     * @param  array  $lootEntries  array of [difficultyBit => entry].
     * @return bool   success and found loot
     */
    public function getByContainer(string $table, array $lootEntries): bool
    {
        if (!in_array($table, self::TEMPLATES))
            return false;

        foreach ($lootEntries as $modeBit => $entry)
        {
            if (!$entry)
                continue;

            [$lootRows, $itemIds] = $this->getByContainerRecursive($table, $entry);
            if (!$lootRows)
                continue;

            $items = new ItemList(array(['i.id', $itemIds]));
            $this->storeJSGlobals($items->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            $itemRows = $items->getListviewData();

            // assign listview LV rows to loot rows, not the other way round! The same item may be contained multiple times
            foreach ($lootRows as $loot)
            {
                $count = ceil($loot['groupChance'] * $loot['realChanceMod'] * 100);

                /* on modes...
                 * modes.mode is the (masked) sum of all modes where this item has been seen
                 * modes.mode &  1 dungeon normal
                 * modes.mode &  2 dungeon heroic
                 * modes.mode &  4 generic case (never included in mask for instanced creatures/gos or always === 4 for non-instanced creatures/gos)
                 * modes.mode &  8 raid 10 nh
                 * modes.mode & 16 raid 25 nh
                 * modes.mode & 32 raid 10 hc
                 * modes.mode & 64 raid 25 hc
                 *
                 * modes[4] is _always_ included and is the sum total over all modes:
                 * ex: modes:{"mode":1,"1":{"count":4408,"outof":16013},"4":{"count":4408,"outof":22531}}
                 */
                if ($modeBit)
                {
                    $modes = array(                             // emulate 'percent' with precision: 2
                        'mode'   => $modeBit,
                        $modeBit => ['count' => $count, 'outof' => 10000]
                    );
                    if ($modeBit != 4)
                        $modes[4] = $modes[$modeBit];


                    // unsure: force display as noteworthy
                    // if (!empty($loot['content']) && !empty($itemRows[$loot['content']]) && $itemRows[$loot['content']]['name'][0] == 7 - ITEM_QUALITY_POOR)
                    //     $modes['mode'] = 4;
                    // else if ($count < 100)                      // chance < 1%
                    //     $modes['mode'] = 4;


                    // existing result row; merge modes and move on
                    if (!is_null($k = array_find_key($this->results, function($x) use ($loot) {
                        if (!empty($loot['reference']))
                            return $x['id'] == $loot['reference'] && $x['mode'] == $loot['mode'] && $x['group'] == $loot['group'] && $x['stack'] == [$loot['multiplier'], $loot['multiplier']];
                        else
                            return $x['id'] == $loot['content']   && $x['mode'] == $loot['mode'] && $x['group'] == $loot['group'];
                    })))
                    {
                        $this->results[$k]['modes']['mode']    |= $modes['mode'];
                        $this->results[$k]['modes'][$modeBit]   = $modes[$modeBit];
                        $this->results[$k]['modes'][4]['count'] = max($modes[4]['count'], $this->results[$k]['modes'][4]['count']);

                        continue;
                    }
                }

                $base = array(
                    'count'     => $count,
                    'outof'     => 10000,
                    'group'     => $loot['group'],
                    'quest'     => $loot['quest'],
                    'mode'      => $loot['mode'] ?: null,   // dyn loot mode
                    'modes'     => $modes ?? null,          // difficulties
                    'reference' => $loot['parentRef'] ?: null,
                    'condition' => $loot['condition'] ?? null,
                    'pctstack'  => self::buildStack($loot['min'] ?? 0, $loot['max'] ?? 0)
                );

                $base = array_filter($base, fn($x) => $x !== null);

                if (empty($loot['reference']))              // regular drop
                {
                    if ($itemRow = $itemRows[$loot['content']] ?? null)
                    {
                        $extra = ['stack' => [$loot['min'], $loot['max']]];

                        // unsure if correct - tag item as trash if chance < 1% and tagged as having many sources
                        if ($base['count'] < 100 && $items->getEntry($loot['content'])['moreMask'] & SRC_FLAG_COMMON)
                            $extra['commondrop'] = 1;

                        if (!User::isInGroup(U_GROUP_EMPLOYEE))
                        {
                            if (!isset($this->results[$loot['content']]))
                                $this->results[$loot['content']] = array_merge($itemRow, $base, $extra);
                            else
                                $this->results[$loot['content']]['count'] += $base['count'];
                        }
                        else
                            $this->results[] = array_merge($itemRow, $base, $extra);
                    }
                    else
                        trigger_error('Item #'.$loot['content'].' referenced by loot does not exist!', E_USER_WARNING);
                }
                else if (User::isInGroup(U_GROUP_EMPLOYEE))     // create dummy for ref-drop
                {
                    $data = array(
                        'id'         => $loot['reference'],
                        'name'       => '@REFERENCE: '.$loot['reference'],
                        'icon'       => 'trade_engineering',
                        'stack'      => [$loot['multiplier'], $loot['multiplier']],
                        'commondrop' => 1
                    );
                    $this->results[] = array_merge($base, $data);

                    $this->jsGlobals[Type::ITEM][$loot['reference']] = $data;
                }
            }
        }

        // move excessive % to extra loot
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
        {
            foreach ($this->results as &$_)
            {
                // remember 'count' is always relative to a base of 10000
                if ($_['count'] <= 10000)
                    continue;

                while ($_['count'] > 20000)
                {
                    $_['stack'][0]++;
                    $_['stack'][1]++;
                    $_['count'] -= 10000;
                }

                $_['stack'][1]++;
                $_['count'] = 10000;
            }
        }
        else
        {
            $fields = [['mode', 'Dyn. Mode'], ['reference', 'Reference']];
            $base   = [];
            $set    = 0;
            foreach ($this->results as $foo)
            {
                foreach ($fields as $idx => [$field, $title])
                {
                    $val = $foo[$field] ?? 0;
                    if (!isset($base[$idx]))
                        $base[$idx] = $val;
                    else if ($base[$idx] != $val)
                        $set |= 1 << $idx;
                }

                if ($set == (pow(2, count($fields)) - 1))
                    break;
            }

            $this->extraCols[] = "\$Listview.funcBox.createSimpleCol('group', 'Group', '7%', 'group')";
            foreach ($fields as $idx => [$field, $title])
                if ($set & (1 << $idx))
                    $this->extraCols[] = "\$Listview.funcBox.createSimpleCol('".$field."', '".$title."', '7%', '".$field."')";
        }

        return !empty($this->results);
    }
}

?>
