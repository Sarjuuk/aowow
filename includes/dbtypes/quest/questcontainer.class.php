<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestContainer extends DBTypeContainer
{
    public static int $dbType = Type::QUEST;

    private ?array $somData = null;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, QuestEntry> id => quest template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?QuestEntry
     */
    public function getEntry(?int $key = null) : ?QuestEntry
    {
        return parent::getEntry($key);
    }

    /** returns data portion of a listview js object */
    public function getListviewData(int $addInfoMask = 0x0, int $reputationCol = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getListviewRow($addInfoMask, $reputationCol);

        return $data;
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add quest to @return
     *  - GLOBALINFO_REWARDS - add rewarded items, spells, currencies and titles to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF) : array
    {
        return parent::getJSGlobals($addMask);
    }

    public function getSOMData(array $questIds, int $side = SIDE_BOTH) : array
    {
        $this->somData ??= (function()
        {
            $data   = [];
            $series = DB::Aowow()->selectAssoc(
            'SELECT cur.`id` AS ARRAY_KEY, IF(prev.`id` OR cur.`nextQuestIdChain`, 1, 0) AS "series", IF(prev.`id` IS NULL AND cur.`nextQuestIdChain`, 1, 0) AS "first" FROM ::quests cur LEFT JOIN ::quests prev ON prev.`nextQuestIdChain` = cur.`id` WHERE cur.`id` IN %in',
                $this->getFoundIds()
            );

            foreach ($this->iterate() as $id => $entry)
            {
                $data[$id] = array(
                    'level'     => $entry->level < 0 ? MAX_LEVEL : $entry->level,
                    'name'      => $entry->name,
                    'category'  => $entry->category1,
                    'category2' => $entry->category2,
                    '__side'    => ChrRace::sideFromMask($entry->reqRaceMask)
                ) + $series[$id];

                if ($entry->isDaily())
                    $data[$id]['daily'] = 1;
            }

            return $data;
        })();

        $data = array_intersect_key($this->somData, array_flip($questIds));

        if ($side != SIDE_BOTH)
            $data = array_filter($data, fn($x) => $x['__side'] & $side);

        foreach ($data as &$d)
            unset($d['__side']);

        return $data;
    }
}

?>
