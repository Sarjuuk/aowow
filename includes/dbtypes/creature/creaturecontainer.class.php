<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureContainer extends DBTypeContainer
{
    use TrSpawns;

    public static int $dbType = Type::NPC;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, Creature> id => creature template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?Creature creature template
     */
    public function getEntry(int|string $id) : ?Creature
    {
        return parent::getEntry($id);
    }

    /**
     * @param int $addInfoMask
     * * `0x0010 - LISTVIEWINFO_MODEL`:
     * * `0x0020 - LISTVIEWINFO_TAMEABLE`: include texture
     * * `0x0040 - LISTVIEWINFO_REPUTATION`: include repreward
     */
    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data   =
        $rewRep = [];

        if (!$this->getFoundIDs())
            return [];

        $location = self::createZoneSpawns($this);

        if ($addInfoMask & LISTVIEWINFO_REPUTATION)
            $rewRep = DB::World()->selectCol(
               'SELECT `creature_id` AS ARRAY_KEY, `RewOnKillRepFaction1` AS ARRAY_KEY2, `RewOnKillRepValue1` FROM creature_onkill_reputation WHERE `creature_id` IN %in AND `RewOnKillRepFaction1` > 0 UNION
                SELECT `creature_id` AS ARRAY_KEY, `RewOnKillRepFaction2` AS ARRAY_KEY2, `RewOnKillRepValue2` FROM creature_onkill_reputation WHERE `creature_id` IN %in AND `RewOnKillRepFaction2` > 0',
                $this->getFoundIDs(), $this->getFoundIDs()
            );

        foreach ($this->iterate() as $id => $entry)
        {
            if ($addInfoMask & LISTVIEWINFO_MODEL)
            {
                $row = $entry->getModelListviewRow();

                if (isset($data[$row['skin']]))
                {
                    $data[$row['skin']]['minLevel'] = min($data[$row['skin']]['minLevel'], $row['minLevel']);
                    $data[$row['skin']]['maxLevel'] = max($data[$row['skin']]['maxLevel'], $row['maxLevel']);
                    $data[$row['skin']]['count']++;
                }
                else
                    $data[$row['skin']] = $row;
            }
            else
            {
                $data[$id] = $entry->getListviewRow($addInfoMask, $location[$id] ?? null);

                if ($addInfoMask & LISTVIEWINFO_REPUTATION)
                    foreach ($rewRep[$this->id] ?? [] as $fac => $val)
                        $data[$id]['reprewards'][] = [$fac, $val];
            }
        }

        ksort($data);

        return $data;
    }
}

?>
