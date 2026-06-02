<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellContainer extends DBTypeContainer
{
    public static int $dbType = Type::SPELL;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, SpellEntry> id => spell template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?SpellEntry
     */
    public function getEntry(null|string|int $key = null) : ?SpellEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addInfoMask
     * * `0x0080 - LISTVIEWINFO_MODEL`:
     */
    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data = [];

        $ssf = [];
        if ($addInfoMask & LISTVIEWINFO_MODEL)
            $ssf = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `creatureType`, `displayIdA`, `displayIdH` FROM ::shapeshiftforms');

        // bulk source+model prep
        $idBuff = $objBuff = [];
        foreach ($this->iterate() as $id => $entry)
        {
            if ($addInfoMask & LISTVIEWINFO_MODEL)
            {
                if ($_ = $entry->getFirstCreatureMorphEntry())
                    $idBuff[Type::NPC][] = $_;
                if ($_ = $entry->getFirstObjectMorphEntry())
                    $idBuff[Type::OBJECT][] = $_;
            }

            if ($entry->moreType && $entry->moreTypeId)
                $idBuff[$entry->moreType][] = $entry->moreTypeId;
        }

        foreach ($idBuff as $type => $ids)
            $objBuff[$type] = Type::newContainer($type, [['id', $ids]]);

        foreach ($this->iterate() as $id => $entry)
        {
            $data[$id] = $entry->getListviewRow($addInfoMask);

            // Sources
            if ($entry->moreType && $entry->moreTypeId)
                $entry->prepareSourceMore($objBuff[$entry->moreType]->getEntry($entry->moreTypeId));

            if ([$s, $sm] = $entry->getSources())
            {
                $data[$this->id]['source'] = $s;
                if ($sm)
                    $data[$this->id]['sourcemore'] = $sm;
            }

            if ($addInfoMask & LISTVIEWINFO_MODEL)
            {
                $npcEntry = $objEntry = null;
                if ($_ = $entry->getFirstCreatureMorphEntry())
                    $npcEntry = $objBuff[Type::NPC]->getEntry($_);
                if ($_ = $entry->getFirstObjectMorphEntry())
                    $objEntry = $objBuff[Type::OBJECT]->getEntry($_);

                if ($modelInfo = $entry->getModelInfo($ssf, $npcEntry, $objEntry))
                {
                    $data[$this->id]['npcId']       = $modelInfo['typeId'];
                    $data[$this->id]['displayId']   = $modelInfo['displayId'];
                    $data[$this->id]['displayName'] = $modelInfo['displayName'];
                    break;
                }
            }
        }

        return $data;
    }

    public function getSourceData(int $_id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            if (!$_id || $id == $_id)
                $data[$id] = $entry->getSourceData();

        return $data;
    }

    public function getStatGain() : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getStatGain();

        return $data;
    }

    public function getProfilerMods() : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getProfilerMods();

        return $data;
    }
}

?>
