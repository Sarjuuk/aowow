<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundContainer extends DBTypeContainer
{
    public static int $dbType = Type::SOUND;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        $ids = [];
        foreach ($this->iterate() as $entry)
            $ids = array_merge($ids, $entry->fileIds);

        $soundFiles = SoundEntry::fetchSoundFiles(...$ids);
        foreach ($this->iterate() as $entry)
            $entry->setSoundFiles($soundFiles);
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, SoundEntry> id => sound template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?SoundEntry
     */
    public function getEntry(null|string|int $key = null) : ?SoundEntry
    {
        return parent::getEntry($key);
    }
}

?>
