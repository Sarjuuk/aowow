<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LootByContainer extends Loot
{
    public array $extraCols = [];

    private array $knownRefs = [];                          // known ref loot results (can be reused)
    private array $rawLoot   = [];
    private array $itemIds   = [];

    /**
     * fetch loot for given loot container and resolve references.
     *
     * @param  string $table        a known loote template table name
     * @param  int[]  $lootEntries  array of loot template entries.
     */
    public function __construct(string $table, int ...$lootEntries)
    {
        foreach (array_unique($lootEntries) as $entry)
        {
            if (!$entry)
                continue;

            if (!($lootRows = $this->resolveTemplate($table, $entry)))
                continue;

            $this->rawLoot[$entry] = [];

            foreach ($lootRows as $loot)
            {
                $this->rawLoot[$entry][] = array(
                    'count'      => ceil($loot['groupChance'] * $loot['realChanceMod'] * 100),
                    'group'      => $loot['group'],
                    'mode'       => $loot['mode'] ?: null,   // dyn loot mode
                    'parentRef'  => $loot['parentRef'] ?? null,
                    'reference'  => $loot['reference'] ?? null,
                    'condition'  => $loot['condition'] ?? null,
                    'content'    => $loot['content'] ?? null,
                    'min'        => $loot['min'] ?? null,
                    'max'        => $loot['max'] ?? null,
                    'multiplier' => $loot['multiplier'] ?? null,
                );
            }
        }
    }

    /**
     * @return array listview formatted loot result
     */
    public function getResult() : array
    {
        return $this->results;
    }

    /**
     * @param int $lootId [optional] only for this loot entry
     * @return array resolved loot templates
     */
    public function getRaw(int $lootId = 0) : array
    {
        if ($lootId)
            return $this->rawLoot[$lootId] ?? [];
        return $this->rawLoot;
    }

    /**
     * @return array item ids in loot
     */
    public function getItems() : array
    {
        return $this->itemIds;
    }

    /**
     * recurse through reference loot while applying modifiers from parent template
     *
     * @param string        $tableName  a known loot template table name
     * @param int           $lootId     a loot template entry
     * @param float         $baseChance [optional] chance multiplier passed down from parent template
     * @return null|array[]             lootRows
     */
    private function resolveTemplate(string $tableName, int $lootId, float $baseChance = 1.0) : ?array
    {
        if (!$tableName || !$lootId)
            return null;

        if (!($rows = DB::World()->selectAssoc('SELECT * FROM %n WHERE `entry` = %i', $tableName, $lootId)))
            return null;

        $loot = [];

        $groupChances = [];
        $nGroupEquals = [];
        $cnd = new Conditions();
        foreach ($rows as $entry)
        {
            $set = array(
                'group'         => $entry['GroupId'],
                'parentRef'     => $tableName == self::REFERENCE ? $lootId : 0,
                'realChanceMod' => $baseChance,
                'groupChance'   => 0
            );

            $where = [['(`cuFlags` & %i) = 0', CUSTOM_EXCLUDE_FOR_LISTVIEW | CUSTOM_UNAVAILABLE], [DB::OR, []]];
            for ($i = 1; $i < 5; $i++)
                $where[1][1][] = ["`reqSourceItemId$i` = %i", $entry['Item']];
            for ($i = 1; $i < 7; $i++)
                $where[1][1][] = ["`reqItemId$i` = %i", $entry['Item']];

            if ($entry['QuestRequired'] && ($quests = DB::Aowow()->selectCol('SELECT `id` FROM ::quests WHERE %and', $where)))
                foreach ($quests as $questId)
                    $cnd->addExternalCondition(Conditions::lootTableToConditionSource($tableName), $lootId . ':' . $entry['Item'], [Conditions::QUESTTAKEN, $questId], true);

            // TC 'mode' (dynamic loot modifier)
            $buff = [];
            for ($i = 0; $i < 8; $i++)
                if ($entry['LootMode'] & (1 << $i))
                    $buff[] = $i + 1;

            $set['mode'] = implode(', ', $buff);

            if ($entry['Reference'])
            {
                if ($data = ($this->knownRefs[$entry['Reference']] ??= $this->resolveTemplate(self::REFERENCE, $entry['Reference'], $entry['Chance'] / 100)))
                    $loot = array_merge($loot, $data);

                $set['reference']  = $entry['Reference'];
                $set['multiplier'] = $entry['MaxCount'];
            }
            else
            {
                $this->itemIds[] = $entry['Item'];

                $set['content'] = $entry['Item'];
                $set['min']     = $entry['MinCount'];
                $set['max']     = $entry['MaxCount'];
            }

            if ($entry['QuestRequired'] || !$entry['GroupId'])
                $set['groupChance'] = $entry['Chance'];
            else // if ($entry['GroupId'])
            {
                $groupChances[$entry['GroupId']] ??= 0;
                $nGroupEquals[$entry['GroupId']] ??= 0;

                if (!$entry['Chance'])
                {
                    $nGroupEquals[$entry['GroupId']]++;
                    $set['groupChance'] = &$groupChances[$entry['GroupId']];
                }
                else
                {
                    $set['groupChance'] = $entry['Chance'];

                    if (!$entry['Reference'])
                        $groupChances[$entry['GroupId']] += $entry['Chance'];
                }
            }

            $loot[] = $set;
        }

        foreach ($nGroupEquals as $grp => $n)
        {
            $sum = $groupChances[$grp];
            if ($sum >= 100.01)
            {
                trigger_error('Loot entry '.$lootId.' / group '.$grp.' has a total chance of '.number_format($sum, 2).'%. Some items cannot drop!', E_USER_WARNING);
                $sum = 100;
            }
            // is applied as backReference to items with 0-chance
            $groupChances[$grp] = (100 - $sum) / ($n ?: 1);
        }

        if ($cnd->getBySource(Conditions::lootTableToConditionSource($tableName), group: $lootId)->prepare())
        {
            $this->storeJSGlobals($cnd->getJSGlobals());
            $cnd->toListviewColumn($loot, $this->extraCols, $lootId, 'content');
        }

        return $loot;
    }

    /**
     * format loot for listview display and optionally merge multiple container while adding mode info.
     *
     * @param  array<int, int>  $difficultyEntries  array of [difficulty bit => loot entry].
     * @return array                                listview rows
     */
    public function formatListview(array $difficultyEntries = []) : array
    {
        $items = new ItemContainer(array(['id', $this->itemIds]));
        $this->storeJSGlobals($items->getJSGlobals(GLOBALINFO_RELATED));
        $itemRows = $items->getListviewData();

        foreach ($this->rawLoot as $entry => $lootRows)
        {
            // assign listview LV rows to loot rows, not the other way round! The same item may be contained multiple times
            foreach ($lootRows as $loot)
            {
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
                $modes = [];
                if (($modeBit = array_search($entry, $difficultyEntries)) !== false)
                {
                    $modes = array(                             // emulate 'percent' with precision: 2
                        'mode'   => $modeBit,
                        $modeBit => ['count' => $loot['count'], 'outof' => 10000]
                    );
                    if ($modeBit != 4)
                        $modes[4] = $modes[$modeBit];

                    // existing result row; merge modes and move on
                    if (!is_null($k = array_find_key($this->results, function($x) use ($loot) {
                        if (!empty($loot['reference']))
                            return $x['id'] == $loot['reference'] && $x['mode'] == $loot['mode'] && ($x['condition'] ?? '') == $loot['condition'] && $x['stack'] == [$loot['multiplier'], $loot['multiplier']];
                        else
                            return $x['id'] == $loot['content']   && $x['mode'] == $loot['mode'] && ($x['condition'] ?? '') == $loot['condition'];
                    })))
                    {
                        $this->results[$k]['modes']['mode']    |= $modes['mode'];
                        $this->results[$k]['modes'][$modeBit]   = $modes[$modeBit];
                        $this->results[$k]['modes'][4]['count'] = max($modes[4]['count'], $this->results[$k]['modes'][4]['count']);

                        if (!is_int(strpos($this->results[$k]['group'], $loot['group'])))
                            $this->results[$k]['group'] .= ', '.$loot['group'];

                        continue;
                    }
                }

                $base = array(
                    'count' => $loot['count'],
                    'outof' => 10000,
                    'group' => $loot['group']
                );

                if ($modes)                                 // difficulties
                    $base['modes'] = $modes;
                if ($loot['mode'])                          // dyn loot mode
                    $base['mode'] = $loot['mode'];
                if ($loot['parentRef'])
                    $base['reference'] = $loot['parentRef'];
                if ($loot['condition'])
                    $base['condition'] = $loot['condition'];

                if (empty($loot['reference']))              // regular drop
                {
                    if ($itemRow = $itemRows[$loot['content']] ?? null)
                    {
                        $extra = ['stack' => [$loot['min'], $loot['max']]];

                        // unsure if correct - tag item as trash if chance < 1% and tagged as having many sources
                        if ($base['count'] < 100 && $items->getEntry($loot['content'])->moreMask & SRC_FLAG_COMMON)
                            $extra['commondrop'] = 1;

                        // staff or unmergable - separate loot rows
                        if (User::isInGroup(U_GROUP_EMPLOYEE) || is_null($k = array_find_key($this->results, fn($x) => $x['id'] == $loot['content'] && ($x['condition'] ?? '') == $loot['condition'])))
                            $this->results[] = array_merge($itemRow, $base, $extra);
                        // merge w/o difficulty modes
                        else
                        {
                            $row = &$this->results[$k];
                            if (!is_int(strpos($row['group'], $loot['group'])))
                                $row['group'] .= ', '.$loot['group'];

                            // move excessive % to extra loot
                            if ($row['count'] + $base['count'] == 20000)
                            {
                                $row['stack'][0]++;
                                $row['stack'][1]++;
                                $row['count'] = 10000;
                            }
                            else if (($row['count'] += ($base['count'] + ($row['fraction'] ?? 0))) > 10000)
                            {
                                $row['stack'][1] += intval($row['count'] / 10000);
                                $row['fraction']  = $row['count'] % 10000;
                                $row['count']     = 10000;
                            }

                            unset($row);
                        }
                    }
                    else
                        trigger_error('Item #'.$loot['content'].' referenced by loot does not exist!', E_USER_WARNING);
                }
                else if (User::isInGroup(U_GROUP_EMPLOYEE)) // create dummy for ref-drop
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

        if (User::isInGroup(U_GROUP_EMPLOYEE))
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

        foreach ($this->results as &$row)
        {
            $row['pctstack'] = self::buildStack($row['stack'][0], $row['stack'][1], ($row['fraction'] ?? 0) / 100);
            unset($row['fraction']);
        }

        return $this->results;
    }
}

?>
