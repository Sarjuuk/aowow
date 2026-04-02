<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideContainer extends DBTypeContainer
{
    public static int $dbType = Type::GUIDE;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        $ratings = GuideMgr::getRatings($this->getFoundIds());
        foreach ($this->iterate() as $id => $entry)
            $entry->setVoting($ratings[$id]['nvotes'] ?? 0, $ratings[$id]['rating'] ?? -1);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, GuideEntry> id => guide
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?GuideEntry
     */
    public function getEntry(null|string|int $key = null) : ?GuideEntry
    {
        return parent::getEntry($key);
    }
}

?>
