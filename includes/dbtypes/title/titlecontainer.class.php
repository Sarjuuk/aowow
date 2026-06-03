<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TitleContainer extends DBTypeContainer
{
    public static int $dbType = Type::TITLE;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, TitleEntry> id => title template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?TitleEntry
     */
    public function getEntry(null|string|int $key = null) : ?TitleEntry
    {
        return parent::getEntry($key);
    }

    /** returns data portion of a listview js object */
    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data = [];

        $this->prepareSources();

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getListviewRow($addInfoMask);

        return $data;
    }

    public function hasAnySource() : bool
    {
        foreach ($this->iterate() as $entry)
            if ($entry->hasAnySource())
                return true;
        return false;
    }

    private function prepareSources() : void
    {
        $srcBuff = array(
            SRC_QUEST         => [],
            SRC_ACHIEVEMENT   => [],
            SRC_CUSTOM_STRING => []
        );

        // collect ids
        foreach ($this->iterate() as $entry)
            foreach (array_keys($srcBuff) as $srcKey)
                if (isset($entry->sources[$srcKey]))
                    $srcBuff[$srcKey] = array_merge($srcBuff[$srcKey], $entry->sources[$srcKey]);

        // fill in the details
        if ($srcBuff[SRC_QUEST])
            $srcBuff[SRC_QUEST] = (new QuestContainer(array(['id', $srcBuff[SRC_QUEST]])))->getSourceData();

        if ($srcBuff[SRC_ACHIEVEMENT])
            $srcBuff[SRC_ACHIEVEMENT] = (new AchievementContainer(array(['id', $srcBuff[SRC_ACHIEVEMENT]])))->getSourceData();

        // fix faction alignment
        if ($srcBuff[SRC_QUEST])
            array_walk($srcBuff[SRC_QUEST], fn(&$x) => self::fixSide($x['s']));
        if ($srcBuff[SRC_ACHIEVEMENT])
            array_walk($srcBuff[SRC_ACHIEVEMENT], fn(&$x) => self::fixSide($x['s']));


        // apply collected data
        foreach ($this->iterate() as $entry)
        {
            $tmp = [];
            if ($_ = array_intersect_key($srcBuff[SRC_QUEST], array_flip($entry->sources[SRC_QUEST] ?? [])))
                $tmp[SRC_QUEST] = $_;
            if ($_ = array_intersect_key($srcBuff[SRC_QUEST], array_flip($entry->sources[SRC_ACHIEVEMENT] ?? [])))
                $tmp[SRC_ACHIEVEMENT] = $_;
            if ($id = ($entry->sources[SRC_CUSTOM_STRING] ?? null)) // other source (only one item possible)
                $tmp[SRC_CUSTOM_STRING] = [Lang::game('pvpSources', $id)];

            $entry->source = $tmp;
        }
    }

    private static function fixSide(int &$side) : void      // thats weird.. and hopefully unique to titles
    {
        if ($side == SIDE_HORDE)                            // Horde
            $side = 0;
        else if ($side != SIDE_ALLIANCE)                    // Alliance
            $side = -1;                                     // Both
    }
}

?>
