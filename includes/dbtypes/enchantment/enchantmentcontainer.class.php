<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentContainer extends DBTypeContainer
{
    public static int $dbType = Type::ENCHANTMENT;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        // bulk apply related spells
        $spellIds = [];
        foreach ($this->iterate() as $entry)
            $spellIds = array_merge($spellIds, array_column($entry->spells, 0));

        if (!$spellIds)
            return;

        $relSpells = new SpellContainer(array(['id', $spellIds]));
        if ($relSpells->error)
            return;

        foreach ($this->iterate() as $entry)
            if ($spellIds = array_column($entry->spells, 0))
                $entry->setRelSpells(...$relSpells->export(...$spellIds));
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, EnchantmentEntry> id => enchantment template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?EnchantmentEntry
     */
    public function getEntry(?int $key = null) : ?EnchantmentEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add enchantment to @return
     *  - GLOBALINFO_RELATED - add applied and triggered spells to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF) : array
    {
        return parent::getJSGlobals($addMask);
    }
}

?>
