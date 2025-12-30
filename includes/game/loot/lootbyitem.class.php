<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LootByItem extends Loot
{
    public const /* int */ ITEM_CONTAINED     = 0;
    public const /* int */ ITEM_DISENCHANTED  = 1;
    public const /* int */ ITEM_PROSPECTED    = 2;
    public const /* int */ ITEM_MILLED        = 3;
    public const /* int */ NPC_DROPPED        = 4;
    public const /* int */ NPC_PICKPOCKETED   = 5;
    public const /* int */ NPC_SKINNED        = 6;
    public const /* int */ NPC_MINED          = 7;
    public const /* int */ NPC_SALVAGED       = 8;
    public const /* int */ NPC_GATHERED       = 9;
    public const /* int */ QUEST_REWARD       = 10;
    public const /* int */ ZONE_FISHED        = 11;
    public const /* int */ OBJECT_CONTAINED   = 12;
    public const /* int */ OBJECT_MINED       = 13;
    public const /* int */ OBJECT_GATHERED    = 14;
    public const /* int */ OBJECT_FISHED      = 15;
    public const /* int */ SPELL_CREATED      = 16;
    public const /* int */ ACHIEVEMENT_REWARD = 17;

    private array  $chanceMods    = [];
    private array  $listviewTabs  = array(                  // order here determines tab order on page
    //  [fileName, tabData, tabName, tabId, extraCols, hiddenCols, visibleCols]
        self::NPC_DROPPED        => [Type::NPC,         [], '$LANG.tab_droppedby',        'dropped-by',              [], [], []],
        self::QUEST_REWARD       => [Type::QUEST,       [], '$LANG.tab_rewardfrom',       'reward-from-quest',       [], [], []],
        self::ITEM_CONTAINED     => [Type::ITEM,        [], '$LANG.tab_containedin',      'contained-in-item',       [], [], []],
        self::OBJECT_CONTAINED   => [Type::OBJECT,      [], '$LANG.tab_containedin',      'contained-in-object',     [], [], []],
        self::NPC_PICKPOCKETED   => [Type::NPC,         [], '$LANG.tab_pickpocketedfrom', 'pickpocketed-from',       [], [], []],
        self::NPC_SKINNED        => [Type::NPC,         [], '$LANG.tab_skinnedfrom',      'skinned-from',            [], [], []],
        self::ITEM_DISENCHANTED  => [Type::ITEM,        [], '$LANG.tab_disenchantedfrom', 'disenchanted-from',       [], [], []],
        self::ITEM_PROSPECTED    => [Type::ITEM,        [], '$LANG.tab_prospectedfrom',   'prospected-from',         [], [], []],
        self::ITEM_MILLED        => [Type::ITEM,        [], '$LANG.tab_milledfrom',       'milled-from',             [], [], []],
        self::NPC_MINED          => [Type::NPC,         [], '$LANG.tab_minedfromnpc',     'mined-from-npc',          [], [], []],
        self::NPC_SALVAGED       => [Type::NPC,         [], '$LANG.tab_salvagedfrom',     'salvaged-from',           [], [], []],
        self::NPC_GATHERED       => [Type::NPC,         [], '$LANG.tab_gatheredfromnpc',  'gathered-from-npc',       [], [], []],
        self::OBJECT_MINED       => [Type::OBJECT,      [], '$LANG.tab_minedfrom',        'mined-from-object',       [], [], []],
        self::OBJECT_GATHERED    => [Type::OBJECT,      [], '$LANG.tab_gatheredfrom',     'gathered-from-object',    [], [], []],
        self::ZONE_FISHED        => [Type::ZONE,        [], '$LANG.tab_fishedin',         'fished-in-zone',          [], [], []],
        self::OBJECT_FISHED      => [Type::OBJECT,      [], '$LANG.tab_fishedin',         'fished-in-object',        [], [], []],
        self::SPELL_CREATED      => [Type::SPELL,       [], '$LANG.tab_createdby',        'created-by',              [], [], []],
        self::ACHIEVEMENT_REWARD => [Type::ACHIEVEMENT, [], '$LANG.tab_rewardfrom',       'reward-from-achievement', [], [], []]
    );
    private string $queryTemplate =
       'SELECT    lt1.`entry`                                          AS ARRAY_KEY,
                  IF(lt1.`reference` = 0, lt1.`item`, lt1.`reference`) AS "item",
                  lt1.`chance`                                         AS "chance",
                  SUM(IF(lt2.`chance` = 0, 1, 0))                      AS "nZeroItems",
                  SUM(IF(lt2.`reference` = 0, lt2.`chance`, 0))        AS "sumChance",
                  IF(lt1.`groupid` > 0, 1, 0)                          AS "isGrouped",
                  IF(lt1.`reference` = 0, lt1.`mincount`, 1)           AS "min",
                  IF(lt1.`reference` = 0, lt1.`maxcount`, 1)           AS "max",
                  IF(lt1.`reference` > 0, lt1.`maxcount`, 1)           AS "multiplier"
        FROM      ?# lt1
        LEFT JOIN ?# lt2 ON lt1.`entry` = lt2.`entry` AND lt1.`groupid` = lt2.`groupid`
        WHERE     %s
        GROUP BY  lt2.`entry`, lt2.`groupid`';

    /**
     * @param  int $entry   item id to find loot container for
     * @return void
     */
    public function __construct(private int $entry)
    {

    }

    /**
     * iterate over result set
     *
     * @return iterable [tabIdx => [lvTemplate, lvData]]
     */
    public function &iterate() : \Generator
    {
        reset($this->results);

        foreach ($this->results as $k => [, $tabData])
            if ($tabData['data'])                           // only yield tabs with content
                yield $k => $this->results[$k];
    }

    /**
     * calculate chance and stack info and apply to loot rows
     *
     * @param  array $refs      loot rows to apply chance + stack info to
     * @param  array $parents   [optional] ref loot ids this call is derived from
     * @return array [entry => stack+chance-info]
     */
    private function calcChance(array $refs, array $parents = []) : array
    {
        $result = [];

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
            if (in_array($rId, $parents))
                continue;

            $result[$rId] = array(
                'percent' => $chance,
                'stack'   => [$ref['min'], $ref['max']],
                'count'   => 1                          // ..and one for the sort script
            );

            if ($_ = self::buildStack($ref['min'], $ref['max']))
                $result[$rId]['pctstack'] = $_;
        }

        // sort by % DESC
        uasort($result, fn($a, $b) => $b['percent'] <=> $a['percent']);

        return $result;
    }

    /**
     * fetch loot container for item provided to __construct
     *
     * @param  int   $maxResults    [optional] SQL_LIMIT override
     * @param  array $lootTableList [optional] limit lookup to provided loot template table names
     * @return bool  success
     */
    public function getByItem(int $maxResults = Listview::DEFAULT_SIZE, array $lootTableList = []) : bool
    {
        if (!$this->entry)
            return false;

        $refResults = [];

        /*
            get references containing the item
        */
        $newRefs = DB::World()->select(
            sprintf($this->queryTemplate, 'lt1.`item` = ?d AND lt1.`reference` = 0'),
            Loot::REFERENCE, Loot::REFERENCE,
            $this->entry
        );

        /*
        i'm currently not seeing a reasonable way to blend this into creature/gobject/etc tabs as one entity may drop the same item multiple times, with and without conditions.
        if ($newRefs)
        {
            $cnd = new Conditions();
            if ($cnd->getBySource(Conditions::SRC_REFERENCE_LOOT_TEMPLATE, entry: $this->entry))
                if ($cnd->toListviewColumn($newRefs, $x, $this->entry))
                    $this->storejsGlobals($cnd->getJsGlobals());
        }
        */

        while ($newRefs)
        {
            $curRefs = $newRefs;
            $newRefs = DB::World()->select(
                sprintf($this->queryTemplate, 'lt1.`reference` IN (?a)'),
                Loot::REFERENCE, Loot::REFERENCE,
                array_keys($curRefs)
            );

            $refResults += $this->calcChance($curRefs, array_column($newRefs, 'item'));
        }

        /*
            search the real loot-templates for the itemId and gathered refs
        */
        foreach (self::TEMPLATES as $lootTemplate)
        {
            if ($lootTableList && !in_array($lootTemplate, $lootTableList))
                continue;

            if ($lootTemplate == Loot::REFERENCE)
                continue;

            $result = $this->calcChance(DB::World()->select(
                sprintf($this->queryTemplate, '{lt1.`reference` IN (?a) OR }(lt1.`reference` = 0 AND lt1.`item` = ?d)'),
                $lootTemplate, $lootTemplate,
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

            // cap fetched entries to the sql-limit to guarantee that the highest chance items get selected first
            // screws with GO-loot and skinning-loot as these templates are shared for several tabs (fish, herb, ore) and (herb, ore, leather)
            $ids = array_slice(array_keys($result), 0, $maxResults);

            // fill ListviewTabs
            match ($lootTemplate)
            {
                Loot::GAMEOBJECT  => $this->handleObjectLoot( $ids, $result),
                Loot::MAIL        => $this->handleMailLoot(   $ids, $result),
                Loot::SPELL       => $this->handleSpellLoot(  $ids, $result),
                Loot::CREATURE    => $this->handleNpcLoot(    $ids, $result, self::NPC_DROPPED,       'lootId'),
                Loot::PICKPOCKET  => $this->handleNpcLoot(    $ids, $result, self::NPC_PICKPOCKETED,  'pickpocketLootId'),
                Loot::SKINNING    => $this->handleNpcLoot(    $ids, $result, self::NPC_SKINNED,       'skinLootId'),    // tabId < 0: assigned real id later
                Loot::PROSPECTING => $this->handleGenericLoot($ids, $result, self::ITEM_PROSPECTED,   'id'),
                Loot::MILLING     => $this->handleGenericLoot($ids, $result, self::ITEM_MILLED,       'id'),
                Loot::ITEM        => $this->handleGenericLoot($ids, $result, self::ITEM_CONTAINED,    'id'),
                Loot::DISENCHANT  => $this->handleGenericLoot($ids, $result, self::ITEM_DISENCHANTED, 'disenchantId'),
                Loot::FISHING     => $this->handleGenericLoot($ids, $result, self::ZONE_FISHED,       'id')            // subAreas are currently ignored
            };
        }

        // finalize tabs
        foreach ($this->listviewTabs as $idx => [$type, $data, $name, $id, $extraCols, $hiddenCols, $visibleCols])
        {
            $tabData = array(
                'data' => $data,
                'name' => $name,
                'id'   => $id
            );

            if ($extraCols)
                $tabData['extraCols'] = array_unique($extraCols);

            if ($hiddenCols)
                $tabData['hiddenCols'] = array_unique($hiddenCols);

            if ($visibleCols)
                $tabData['visibleCols'] = array_unique($visibleCols);

            $this->results[$idx] = [Type::getFileString($type), $tabData];
        }

        return true;
    }

    private function handleGenericLoot(array $ids, array $result, int $tabId, string $dbField) : bool
    {
        if (!$ids)
            return false;

        [$type, &$data, , , &$extraCols, ,] = $this->listviewTabs[$tabId];

        $srcObj = Type::newList($type, [[$dbField, $ids]]);
        if (!$srcObj || $srcObj->error)
            return false;

        $srcData = $srcObj->getListviewData();
        $this->storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

        $extraCols[] = '$Listview.extraCols.percent';

        foreach ($srcObj->iterate() as $__)
            $data[] = array_merge($srcData[$srcObj->id], $result[$srcObj->getField($dbField)]);

        return true;
    }

    private function handleNpcLoot(array $ids, array $result, int $tabId, string $dbField) : bool
    {
        if (!$ids)
            return false;

        if ($baseIds = DB::Aowow()->selectCol(
           'SELECT `difficultyEntry1` AS ARRAY_KEY, `id` FROM ?_creature WHERE `difficultyEntry1` IN (?a) UNION
            SELECT `difficultyEntry2` AS ARRAY_KEY, `id` FROM ?_creature WHERE `difficultyEntry2` IN (?a) UNION
            SELECT `difficultyEntry3` AS ARRAY_KEY, `id` FROM ?_creature WHERE `difficultyEntry3` IN (?a)',
            $ids, $ids, $ids
        ))
        {
            $parentObj = new CreatureList(array(['id', $baseIds]));
            if (!$parentObj->error)
            {
                $this->storeJSGlobals($parentObj->getJSGlobals());
                $parentData = $parentObj->getListviewData();
                $ids = array_diff($ids, $baseIds);
            }
        }

        $npc = new CreatureList(array([$dbField, $ids]));
        if ($npc->error)
            return false;

        $srcData = $npc->getListviewData();
        $this->storeJSGlobals($npc->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        [, &$data, , , &$extraCols, ,] = $this->listviewTabs[$tabId];

        foreach ($npc->iterate() as $__)
        {
            if ($tabId == self::NPC_SKINNED)
            {
                if ($npc->isMineable())
                    $tabId = self::NPC_MINED;
                else if ($npc->isGatherable())
                    $tabId = self::NPC_GATHERED;
                else if ($npc->isSalvageable())
                    $tabId = self::NPC_SALVAGED;
            }

            $p = $npc->getField('parentId');

            $data[]      = array_merge($parentData[$p] ?? $srcData[$npc->id], $result[$npc->getField($dbField)]);
            $extraCols[] = '$Listview.extraCols.percent';
        }

        return true;
    }

    private function handleSpellLoot(array $ids, array $result) : bool
    {
        $conditions = array(
            'OR',
            ['AND', ['effect1CreateItemId', $this->entry], ['OR', ['effect1Id', SpellList::EFFECTS_ITEM_CREATE], ['effect1AuraId', SpellList::AURAS_ITEM_CREATE]]],
            ['AND', ['effect2CreateItemId', $this->entry], ['OR', ['effect2Id', SpellList::EFFECTS_ITEM_CREATE], ['effect2AuraId', SpellList::AURAS_ITEM_CREATE]]],
            ['AND', ['effect3CreateItemId', $this->entry], ['OR', ['effect3Id', SpellList::EFFECTS_ITEM_CREATE], ['effect3AuraId', SpellList::AURAS_ITEM_CREATE]]]
        );
        if ($ids)
            $conditions[] = ['id', $ids];

        $srcObj = new SpellList($conditions);
        if ($srcObj->error)
            return false;

        $this->storeJSGlobals($srcObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
        [, &$data, , , &$extraCols, , &$visibleCols] = $this->listviewTabs[self::SPELL_CREATED];

        if (!empty($result))
            $extraCols[] = '$Listview.extraCols.percent';

        if ($srcObj->hasSetFields('reagent1', 'reagent2', 'reagent3', 'reagent4', 'reagent5', 'reagent6', 'reagent7', 'reagent8'))
            $visibleCols[] = 'reagents';

        foreach ($srcObj->getListviewData() as $id => $row)
            $data[] = array_merge($row, $result[$id] ?? ['percent' => -1]);

        return true;
    }

    private function handleMailLoot(array $ids, array $result) : bool
    {
        // quest part
        $conditions = array('OR',
            ['rewardChoiceItemId1', $this->entry], ['rewardChoiceItemId2', $this->entry], ['rewardChoiceItemId3', $this->entry], ['rewardChoiceItemId4', $this->entry], ['rewardChoiceItemId5', $this->entry],
            ['rewardChoiceItemId6', $this->entry], ['rewardItemId1',       $this->entry], ['rewardItemId2',       $this->entry], ['rewardItemId3',       $this->entry], ['rewardItemId4',       $this->entry]
        );
        if ($ids)
            $conditions[] = ['rewardMailTemplateId', $ids];

        $quests = new QuestList($conditions);
        if (!$quests->error)
        {
            $this->storeJSGlobals($quests->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
            [, &$qData, , , , , ] = $this->listviewTabs[self::QUEST_REWARD];

            foreach ($quests->getListviewData() as $id => $row)
                $qData[] = array_merge($row, $result[$id] ?? ['percent' => -1]);
        }

        // achievement part
        $conditions = array(['itemExtra', $this->entry]);
        if ($ar = DB::World()->selectCol('SELECT `ID` FROM achievement_reward WHERE `ItemID` = ?d{ OR `MailTemplateID` IN (?a)}', $this->entry, $ids ?: DBSIMPLE_SKIP))
            array_push($conditions, ['id', $ar], 'OR');

        $achievements = new AchievementList($conditions);
        if (!$achievements->error)
        {
            $this->storeJSGlobals($achievements->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
            [, &$aData, , , , &$hiddenCols, &$visibleCols] = $this->listviewTabs[self::ACHIEVEMENT_REWARD];

            foreach ($achievements->getListviewData() as $id => $row)
                $aData[] = array_merge($row, $result[$id] ?? ['percent' => -1]);

            $hiddenCols[]  = 'rewards';
            $visibleCols[] = 'category';
        }

        return !$quests->error || !$achievements->error;
    }

    private function handleObjectLoot(array $ids, array $result) : bool
    {
        if (!$ids)
            return false;

        $srcObj = new GameObjectList(array(['lootId', $ids]));
        if ($srcObj->error)
            return false;

        foreach ($srcObj->getListviewData() as $id => $row)
        {
            $tabId = match($row['type'])
            {
                25      => self::OBJECT_FISHED,             // fishing node
                -3      => self::OBJECT_GATHERED,           // herb
                -4      => self::OBJECT_MINED,              // vein
                default => self::OBJECT_CONTAINED           // general chest loot
            };

            [, &$tabData, , , &$extraCols, , &$visibleCols] = $this->listviewTabs[$tabId];

            $tabData[]   = array_merge($row, $result[$srcObj->getEntry($id)['lootId']]);
            $extraCols[] = '$Listview.extraCols.percent';
            if ($tabId != 15)
                $visibleCols[] = 'skill';
        }

        return true;
    }
}

?>
