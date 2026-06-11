<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementContainer extends DBTypeContainer implements IListview, ISource
{
    use TrSourceHelper;

    public static int $dbType = Type::ACHIEVEMENT;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        $rewards = AchievementEntry::fetchRewards(...$this->getFoundIds());

        foreach ($this->iterate() as $id => $entry)
            $entry->setRewards($rewards[$id] ?? []);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, AchievementEntry> id => achievement template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?AchievementEntry
     */
    public function getEntry(null|string|int $key = null) : ?AchievementEntry
    {
        return parent::getEntry($key);
    }

    public function getSourceData(int $_id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            if (!$_id || $id == $_id)
                $data[$id] = $entry->getSourceData();

        return $data;
    }
}

?>
