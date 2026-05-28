<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconSet extends DBTypeSet
{
    public static int $dbType = Type::ICON;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if (!$this->getFoundIDs())
            return;

        $iconCounts = Icon::fetchIconCounts(...$this->getFoundIds());

        foreach ($this->iterate() as $id => $entry)
            $entry->setIconCounts($iconCounts[$id] ?? []);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, Icon> id => icon entry
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Icon
     */
    public function getEntry(string|int $id) : ?Icon
    {
        return parent::getEntry($id);
    }
}

?>
