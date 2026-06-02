<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestContainer extends DBTypeContainer
{
    public static int $dbType = Type::QUEST;

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        $rewards = Quest::fetchCurrencyItemPairs();

        foreach ($this->iterate() as $entry)
            $entry->setCurrencyItems($rewards);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, Quest> id => quest template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Quest
     */
    public function getEntry(string|int $id) : ?Quest
    {
        return parent::getEntry($id);
    }

    /** returns data portion of a listview js object */
    public function getListviewData(int $addInfoMask = 0x0, int $reputationCol = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getListviewRow($addInfoMask, $reputationCol);

        return $data;
    }

    public function getSOMData(int $side = SIDE_BOTH) : array
    {
        $data   = [];
        $series = DB::Aowow()->selectAssoc(
           'SELECT cur.`id` AS ARRAY_KEY, IF(prev.`id` OR cur.`nextQuestIdChain`, 1, 0) AS "series", IF(prev.`id` IS NULL AND cur.`nextQuestIdChain`, 1, 0) AS "first" FROM ::quests cur LEFT JOIN ::quests prev ON prev.`nextQuestIdChain` = cur.`id` WHERE cur.`id` IN %in',
            $this->getFoundIds()
        );

        foreach ($this->iterate() as $id => $entry)
        {
            if (!(ChrRace::sideFromMask($entry->reqRaceMask) & $side))
                continue;

            $data[$id] = array(
                'level'     => $entry->level < 0 ? MAX_LEVEL : $entry->level,
                'name'      => $entry->name,
                'category'  => $entry->category1,
                'category2' => $entry->category2
            ) + $series[$id];

            if ($entry->isDaily())
                $data[$id]['daily'] = 1;
        }

        return $data;
    }
}

?>
