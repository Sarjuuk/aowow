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
    public function getEntry(?int $key = null) : ?SpellEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add spell to @return
     *  - GLOBALINFO_RELATED - add reagent items, created items, required classes, required races and played sounds to @return
     *  - GLOBALINFO_EXTRA - writes tooltip and modifying spells to $extra and adds modifying spells to @return
     * @param ?array{?string, ?array, ?string, ?array} $extra will contain item tooltip if enabled
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF, ?array &$extra = []) : array
    {
        return parent::getJSGlobals($addMask);
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
        $idBuff = $objBuff = $smBuff = [];
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
        {
            if ($addInfoMask & LISTVIEWINFO_MODEL)
                $objBuff[$type] = Type::newContainer($type, [['id', $ids]]);

            if (!($class = Type::getClassName($type)))
                continue;

            if (!is_a($class, ISource::class, true))
                continue;

            $smBuff[$type] = $class::getSourceMore(...$ids);
        }

        foreach ($this->iterate() as $id => $entry)
        {
            // Sources
            if ($entry->moreType && $entry->moreTypeId)
                $entry->prepareSourceMore($smBuff[$entry->moreType][$entry->moreTypeId] ?? []);

            $data[$id] = $entry->getListviewRow($addInfoMask);

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
