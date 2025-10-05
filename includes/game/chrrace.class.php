<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


enum ChrRace : int
{
    case HUMAN              = 1;
    case ORC                = 2;
    case DWARF              = 3;
    case NIGHTELF           = 4;
    case UNDEAD             = 5;
    case TAUREN             = 6;
    case GNOME              = 7;
    case TROLL              = 8;
    case GOBLIN             = 9;
    case BLOODELF           = 10;
    case DRAENEI            = 11;
    case FEL_ORC            = 12;
    case NAGA               = 13;
    case BROKEN             = 14;
    case SKELETON           = 15;
    case VRYKUL             = 16;
    case TUSKARR            = 17;
    case FOREST_TROLL       = 18;
    case TAUNKA             = 19;
    case NORTHREND_SKELETON = 20;
    case ICE_TROLL          = 21;

    public const MASK_ALLIANCE = 0x44D;                     // HUMAN, DWARF, NIGHTELF, GNOME, DRAENEI
    public const MASK_HORDE    = 0x2B2;                     // ORC, UNDEAD, TAUREN, TROLL, BLOODELF
    public const MASK_ALL      = self::MASK_ALLIANCE | self::MASK_HORDE;

    public function matches(int $raceMask) : bool
    {
        return !$raceMask || $this->value & $raceMask;
    }

    public function toMask() : int
    {
        return 1 << ($this->value - 1);
    }

    public function isAlliance() : bool
    {
        return $this->toMask() & self::MASK_ALLIANCE;
    }

    public function isHorde() : bool
    {
        return $this->toMask() & self::MASK_HORDE;
    }

    public function getSide() : int
    {
        if ($this->isHorde() && $this->isAlliance())
            return SIDE_BOTH;
        else if ($this->isHorde())
            return SIDE_HORDE;
        else if ($this->isAlliance())
            return SIDE_ALLIANCE;
        else
            return SIDE_NONE;
    }

    public function getTeam() : int
    {
        if ($this->isHorde() && $this->isAlliance())
            return TEAM_NEUTRAL;
        else if ($this->isHorde())
            return TEAM_HORDE;
        else if ($this->isAlliance())
            return TEAM_ALLIANCE;
        else
            return TEAM_NEUTRAL;
    }

    public function json() : string
    {
        return match ($this)
        {
            self::HUMAN    => 'human',
            self::ORC      => 'orc',
            self::DWARF    => 'dwarf',
            self::NIGHTELF => 'nightelf',
            self::UNDEAD   => 'undead',
            self::TAUREN   => 'tauren',
            self::GNOME    => 'gnome',
            self::TROLL    => 'troll',
            self::BLOODELF => 'bloodelf',
            self::DRAENEI  => 'draenei',
            default        => ''
        };
    }

    public static function fromMask(int $raceMask = self::MASK_ALL) : array
    {
        $x = [];
        foreach (self::cases() as $cl)
            if ($cl->toMask() & $raceMask)
                $x[] = $cl->value;

        return $x;
    }

    public static function sideFromMask(int $raceMask) : int
    {
        // Any
        if (!$raceMask || ($raceMask & self::MASK_ALL) == self::MASK_ALL)
            return SIDE_BOTH;

        // Horde
        if ($raceMask & self::MASK_HORDE && !($raceMask & self::MASK_ALLIANCE))
            return SIDE_HORDE;

        // Alliance
        if ($raceMask & self::MASK_ALLIANCE && !($raceMask & self::MASK_HORDE))
            return SIDE_ALLIANCE;

        return SIDE_BOTH;
    }

    public static function teamFromMask(int $raceMask) : int
    {
        // Any
        if (!$raceMask || ($raceMask & self::MASK_ALL) == self::MASK_ALL)
            return TEAM_NEUTRAL;

        // Horde
        if ($raceMask & self::MASK_HORDE && !($raceMask & self::MASK_ALLIANCE))
            return TEAM_HORDE;

        // Alliance
        if ($raceMask & self::MASK_ALLIANCE && !($raceMask & self::MASK_HORDE))
            return TEAM_ALLIANCE;

        return TEAM_NEUTRAL;
    }
}

?>
