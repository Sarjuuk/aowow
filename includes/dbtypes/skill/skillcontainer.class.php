<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillContainer extends DBTypeContainer
{
    public static int $dbType = Type::SKILL;

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, SkillEntry> id => skill template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?SkillEntry
     */
    public function getEntry(null|string|int $key = null) : ?SkillEntry
    {
        return parent::getEntry($key);
    }
}

?>
