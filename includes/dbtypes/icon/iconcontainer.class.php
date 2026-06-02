<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconContainer extends DBTypeContainer
{
    public static int $dbType = Type::ICON;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if (!$this->getFoundIDs())
            return;

        $iconCounts = IconEntry::fetchIconCounts(...$this->getFoundIds());

        foreach ($this->iterate() as $id => $entry)
            $entry->setIconCounts($iconCounts[$id] ?? []);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, IconEntry> id => icon entry
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?IconEntry
     */
    public function getEntry(null|string|int $key = null) : ?IconEntry
    {
        return parent::getEntry($key);
    }
}

?>
