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
    public function getEntry(null|string|int $key = null) : ?EnchantmentEntry
    {
        return parent::getEntry($key);
    }
}

?>
