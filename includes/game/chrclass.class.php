<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


enum ChrClass : int
{
    case WARRIOR     = 1;
    case PALADIN     = 2;
    case HUNTER      = 3;
    case ROGUE       = 4;
    case PRIEST      = 5;
    case DEATHKNIGHT = 6;
    case SHAMAN      = 7;
    case MAGE        = 8;
    case WARLOCK     = 9;
    case DRUID       = 11;

    public const MASK_ALL    = 0x5FF;

    public function matches(int $classMask) : bool
    {
        return !$classMask || $this->value & $classMask;
    }

    public function toMask() : int
    {
        return 1 << ($this->value - 1);
    }

    public static function fromMask(int $classMask = self::MASK_ALL) : array
    {
        $x = [];
        foreach (self::cases() as $cl)
            if ($cl->toMask() & $classMask)
                $x[] = $cl->value;

        return $x;
    }

    public function json() : string
    {
        return match ($this)
        {
            self::WARRIOR     => 'warrior',
            self::PALADIN     => 'paladin',
            self::HUNTER      => 'hunter',
            self::ROGUE       => 'rogue',
            self::PRIEST      => 'priest',
            self::DEATHKNIGHT => 'deathknight',
            self::SHAMAN      => 'shaman',
            self::MAGE        => 'mage',
            self::WARLOCK     => 'warlock',
            self::DRUID       => 'druid'
        };
    }

    public function spellFamily() : int
    {
        return match ($this)
        {
            self::WARRIOR     => SPELLFAMILY_WARRIOR,
            self::PALADIN     => SPELLFAMILY_PALADIN,
            self::HUNTER      => SPELLFAMILY_HUNTER,
            self::ROGUE       => SPELLFAMILY_ROGUE,
            self::PRIEST      => SPELLFAMILY_PRIEST,
            self::DEATHKNIGHT => SPELLFAMILY_DEATHKNIGHT,
            self::SHAMAN      => SPELLFAMILY_SHAMAN,
            self::MAGE        => SPELLFAMILY_MAGE,
            self::WARLOCK     => SPELLFAMILY_WARLOCK,
            self::DRUID       => SPELLFAMILY_DRUID
        };
    }
}

?>
