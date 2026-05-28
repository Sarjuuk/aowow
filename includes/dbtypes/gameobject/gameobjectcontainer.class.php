<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameobjectContainer extends DBTypeContainer
{
    use TrSpawns;

    public static int $dbType = Type::OBJECT;

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, Gameobject> id => go template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Gameobject
     */
    public function getEntry(string|int $id) : ?Gameobject
    {
        return parent::getEntry($id);
    }

    /** returns data portion of a listview js object */
    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data = [];
        $location = self::createZoneSpawns($this);

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getListviewRow($addInfoMask, $location[$id] ?? null);

        return $data;
    }
}

?>
