<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// TrinityCore - SmartAI
class SmartTarget
{
    use SmartHelper;

    public const TARGET_NONE                   = 0;         //  None.
    public const TARGET_SELF                   = 1;         //  Self cast.
    public const TARGET_VICTIM                 = 2;         //  Our current target. (ie: highest aggro)
    public const TARGET_HOSTILE_SECOND_AGGRO   = 3;         //  Second highest aggro.
    public const TARGET_HOSTILE_LAST_AGGRO     = 4;         //  Dead last on aggro.
    public const TARGET_HOSTILE_RANDOM         = 5;         //  Just any random target on our threat list.
    public const TARGET_HOSTILE_RANDOM_NOT_TOP = 6;         //  Any random target except top threat.
    public const TARGET_ACTION_INVOKER         = 7;         //  Unit who caused this Event to occur.
    public const TARGET_POSITION               = 8;         //  Use xyz from event params.
    public const TARGET_CREATURE_RANGE         = 9;         //  (Random?) creature with specified ID within specified range.
    public const TARGET_CREATURE_GUID          = 10;        //  Creature with specified GUID.
    public const TARGET_CREATURE_DISTANCE      = 11;        //  Creature with specified ID within distance. (Different from #9?)
    public const TARGET_STORED                 = 12;        //  Uses pre-stored target(list)
    public const TARGET_GAMEOBJECT_RANGE       = 13;        //  (Random?) object with specified ID within specified range.
    public const TARGET_GAMEOBJECT_GUID        = 14;        //  Object with specified GUID.
    public const TARGET_GAMEOBJECT_DISTANCE    = 15;        //  Object with specified ID within distance. (Different from #13?)
    public const TARGET_INVOKER_PARTY          = 16;        //  Invoker's party members
    public const TARGET_PLAYER_RANGE           = 17;        //  (Random?) player within specified range.
    public const TARGET_PLAYER_DISTANCE        = 18;        //  (Random?) player within specified distance. (Different from #17?)
    public const TARGET_CLOSEST_CREATURE       = 19;        //  Closest creature with specified ID within specified range.
    public const TARGET_CLOSEST_GAMEOBJECT     = 20;        //  Closest object with specified ID within specified range.
    public const TARGET_CLOSEST_PLAYER         = 21;        //  Closest player within specified range.
    public const TARGET_ACTION_INVOKER_VEHICLE = 22;        //  Unit's vehicle who caused this Event to occur
    public const TARGET_OWNER_OR_SUMMONER      = 23;        //  Unit's owner or summoner
    public const TARGET_THREAT_LIST            = 24;        //  All units on creature's threat list
    public const TARGET_CLOSEST_ENEMY          = 25;        //  Any attackable target (creature or player) within maxDist
    public const TARGET_CLOSEST_FRIENDLY       = 26;        //  Any friendly unit (creature, player or pet) within maxDist
    public const TARGET_LOOT_RECIPIENTS        = 27;        //  All tagging players
    public const TARGET_FARTHEST               = 28;        //  Farthest unit on the threat list
    public const TARGET_VEHICLE_PASSENGER      = 29;        //  Vehicle can target unit in given seat
    public const TARGET_CLOSEST_UNSPAWNED_GO   = 30;        //  entry(0any), maxDist

    private const TARGET_TPL = '[tooltip name=t-#rowIdx#]%1$s[/tooltip][span class=tip tooltip=t-#rowIdx#]%2$s[/span]';

    private array $targets = array(
        self::TARGET_NONE                   => [null,                    null,                    null, null], // NONE
        self::TARGET_SELF                   => [null,                    null,                    null, null], // Self cast
        self::TARGET_VICTIM                 => [null,                    null,                    null, null], // Our current target (ie: highest aggro)
        self::TARGET_HOSTILE_SECOND_AGGRO   => [null,                    null,                    null, null], // Second highest aggro, maxdist, playerOnly, powerType + 1
        self::TARGET_HOSTILE_LAST_AGGRO     => [null,                    null,                    null, null], // Dead last on aggro, maxdist, playerOnly, powerType + 1
        self::TARGET_HOSTILE_RANDOM         => [null,                    null,                    null, null], // Just any random target on our threat list, maxdist, playerOnly, powerType + 1
        self::TARGET_HOSTILE_RANDOM_NOT_TOP => [null,                    null,                    null, null], // Any random target except top threat, maxdist, playerOnly, powerType + 1
        self::TARGET_ACTION_INVOKER         => [null,                    null,                    null, null], // Unit who caused this Event to occur
        self::TARGET_POSITION               => [null,                    null,                    null, null], // use xyz from event params
        self::TARGET_CREATURE_RANGE         => [Type::NPC,               ['numRange', 10, false], null, null], // CreatureEntry(0any), minDist, maxDist
        self::TARGET_CREATURE_GUID          => [null,                    Type::NPC,               null, null], // guid, entry
        self::TARGET_CREATURE_DISTANCE      => [Type::NPC,               null,                    null, null], // CreatureEntry(0any), maxDist
        self::TARGET_STORED                 => [null,                    null,                    null, null], // id, uses pre-stored target(list)
        self::TARGET_GAMEOBJECT_RANGE       => [Type::OBJECT,            ['numRange', 10, false], null, null], // entry(0any), min, max
        self::TARGET_GAMEOBJECT_GUID        => [null,                    Type::OBJECT,            null, null], // guid, entry
        self::TARGET_GAMEOBJECT_DISTANCE    => [Type::OBJECT,            null,                    null, null], // entry(0any), maxDist
        self::TARGET_INVOKER_PARTY          => [null,                    null,                    null, null], // invoker's party members
        self::TARGET_PLAYER_RANGE           => [['numRange', 10, false], null,                    null, null], // min, max
        self::TARGET_PLAYER_DISTANCE        => [null,                    null,                    null, null], // maxDist
        self::TARGET_CLOSEST_CREATURE       => [Type::NPC,               null,                    null, null], // CreatureEntry(0any), maxDist, dead?
        self::TARGET_CLOSEST_GAMEOBJECT     => [Type::OBJECT,            null,                    null, null], // entry(0any), maxDist
        self::TARGET_CLOSEST_PLAYER         => [null,                    null,                    null, null], // maxDist
        self::TARGET_ACTION_INVOKER_VEHICLE => [null,                    null,                    null, null], // Unit's vehicle who caused this Event to occur
        self::TARGET_OWNER_OR_SUMMONER      => [null,                    null,                    null, null], // Unit's owner or summoner, Use Owner/Charmer of this unit
        self::TARGET_THREAT_LIST            => [null,                    null,                    null, null], // All units on creature's threat list, maxdist
        self::TARGET_CLOSEST_ENEMY          => [null,                    null,                    null, null], // maxDist, playerOnly
        self::TARGET_CLOSEST_FRIENDLY       => [null,                    null,                    null, null], // maxDist, playerOnly
        self::TARGET_LOOT_RECIPIENTS        => [null,                    null,                    null, null], // all players that have tagged this creature (for kill credit)
        self::TARGET_FARTHEST               => [null,                    null,                    null, null], // maxDist, playerOnly, isInLos
        self::TARGET_VEHICLE_PASSENGER      => [null,                    null,                    null, null], // seatMask (0 - all seats)
        self::TARGET_CLOSEST_UNSPAWNED_GO   => [Type::OBJECT,            null,                    null, null]  // entry(0any), maxDist
    );

    private array $jsGlobals = [];

    public function __construct(
        private int $id,
        public readonly int $type,
        private array $param,
        private array $worldPos,
        private SmartAI &$smartAI)
    {
        // additional parameters
        Util::checkNumeric($this->param, NUM_CAST_INT);
        Util::checkNumeric($this->worldPos, NUM_CAST_FLOAT);
        $this->param    = array_pad($this->param, 15, '');
        $this->worldPos = array_pad($this->worldPos, 4, 0.0);
    }

    public function process() : string
    {
        $target  = '';

        $targetTT = Lang::smartAI('targetTT', array_merge([$this->type], $this->param, $this->worldPos));

        for ($i = 0; $i < 4; $i++)
        {
            $tParams = $this->targets[$this->type];

            if (is_array($tParams[$i]))
            {
                [$fn, $idx, $extraParam] = $tParams[$i];

                $this->param[$idx] = $this->{$fn}($this->param[$i], $this->param[$i + 1], $extraParam);
            }
            else if (is_int($tParams[$i]) && $this->param[$i])
                $this->jsGlobals[$tParams[$i]][$this->param[$i]] = $this->param[$i];
        }

        // non-generic cases
        switch ($this->type)
        {
            case self::TARGET_HOSTILE_SECOND_AGGRO:
            case self::TARGET_HOSTILE_LAST_AGGRO:
            case self::TARGET_HOSTILE_RANDOM:
            case self::TARGET_HOSTILE_RANDOM_NOT_TOP:
                if ($this->param[2])
                    $this->param[10] = Lang::spell('powerTypes', $this->param[2] - 1);
                break;
            case self::TARGET_VEHICLE_PASSENGER:
                if ($this->param[0])
                    $this->param[10] = Lang::concat(Util::mask2bits($this->param[0]));
                break;
            case self::TARGET_CREATURE_GUID:
                if ($_ = $this->resolveGuid(Type::NPC, $this->param[0]))
                {
                    $this->jsGlobals[Type::NPC][$_] = $_;
                    $this->param[10] = $_;
                }
                break;
            case self::TARGET_GAMEOBJECT_GUID:
                if ($_ = $this->resolveGuid(Type::OBJECT, $this->param[0]))
                {
                    $this->jsGlobals[Type::OBJECT][$_] = $_;
                    $this->param[10] = $_;
                }
                break;
        }

        $this->smartAI->addJsGlobals($this->jsGlobals);

        $target = Lang::smartAI('targets', $this->type, $this->param) ?? Lang::smartAI('targetUNK', [$this->type]);

        // resolve conditionals
        $i = 0;
        while (strstr($target, ')?') && $i++ < 3)
            $target = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):(([^;]*);*);/i', fn($m) => $m[1] ? $m[2] : $m[3], $target);

        // wrap in tooltip (suspend action-tooltip)
        return '[/span]'.sprintf(self::TARGET_TPL, $targetTT, $target).'[span tooltip=a-#rowIdx#]';
    }

    public function getWorldPos() : array
    {
        return $this->worldPos;
    }

    // not really feasable. Too many target types can be players or creatures, depending on context
    public function getTalkSource(bool &$playerSrc = false) : int
    {
        if ($this->type == SmartTarget::TARGET_CLOSEST_PLAYER)
            $playerSrc = true;

        return match ($this->type)
        {
            SmartTarget::TARGET_CREATURE_GUID => $this->resolveGuid(Type::NPC, $this->param[0]) ?? 0,
            SmartTarget::TARGET_CREATURE_RANGE,
            SmartTarget::TARGET_CREATURE_DISTANCE,
            SmartTarget::TARGET_CLOSEST_CREATURE => $this->param[0],
            SmartTarget::TARGET_CLOSEST_PLAYER,
            SmartTarget::TARGET_SELF => $this->smartAI->getEntry(),
            default => $this->smartAI->getEntry()
        };
    }
}

?>
