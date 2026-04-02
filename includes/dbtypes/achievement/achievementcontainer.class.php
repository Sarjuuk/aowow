<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementContainer extends DBTypeContainer
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
    public function getEntry(?int $key = null) : ?AchievementEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add achievement to @return
     *  - GLOBALINFO_REWARDS - add rewarded items and titles to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF) : array
    {
        return parent::getJSGlobals($addMask);
    }

}

?>
