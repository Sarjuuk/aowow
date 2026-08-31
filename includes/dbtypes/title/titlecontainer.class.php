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
    public function getEntry(?int $key = null) : ?TitleEntry
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
            SRC_ACHIEVEMENT   => []
        );

        // collect ids
        foreach ($this->iterate() as $entry)
        {
            match ($entry->moreType)
            {
                Type::QUEST       => $srcBuff[SRC_QUEST][]       = $entry->moreTypeId,
                Type::ACHIEVEMENT => $srcBuff[SRC_ACHIEVEMENT][] = $entry->moreTypeId,
                default           => null
            };

            if ($entry->src12Ext)
                $srcBuff[SRC_ACHIEVEMENT][] = $entry->src12Ext;
        }

        // fill in the details
        if ($srcBuff[SRC_QUEST])
            $srcBuff[SRC_QUEST] = QuestEntry::getSourceMore(...$srcBuff[SRC_QUEST]);
        if ($srcBuff[SRC_ACHIEVEMENT])
            $srcBuff[SRC_ACHIEVEMENT] = AchievementEntry::getSourceMore(...$srcBuff[SRC_ACHIEVEMENT]);

        // fix faction alignment
        if ($srcBuff[SRC_ACHIEVEMENT])
            array_walk($srcBuff[SRC_ACHIEVEMENT], fn(&$x) => self::fixSide($x['s']));

        // apply collected data
        foreach ($this->iterate() as $entry)
        {
            if ($id = $entry->getRawSource(SRC_CUSTOM_STRING))
                $entry->source = [SRC_CUSTOM_STRING => [Lang::game('pvpSources', $id)]];
            else if ($entry->moreType == Type::QUEST && ($_ = $srcBuff[SRC_QUEST][$entry->moreTypeId ?? 0]))
                $entry->source = [SRC_QUEST => [$_]];
            else if ($entry->moreType == Type::ACHIEVEMENT && ($_ = array_intersect_key($srcBuff[SRC_ACHIEVEMENT], array_flip([$entry->moreTypeId, $entry->src12Ext]))))
                $entry->source = [SRC_ACHIEVEMENT => array_values($_)];
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
