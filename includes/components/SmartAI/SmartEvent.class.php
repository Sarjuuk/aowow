<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// TrinityCore - SmartAI
class SmartEvent
{
    use SmartHelper;

    public const EVENT_UPDATE_IC               = 0;         // In combat.
    public const EVENT_UPDATE_OOC              = 1;         // Out of combat.
    public const EVENT_HEALTH_PCT              = 2;         // Health Percentage
    public const EVENT_MANA_PCT                = 3;         // Mana Percentage
    public const EVENT_AGGRO                   = 4;         // On Creature Aggro
    public const EVENT_KILL                    = 5;         // On Creature Kill
    public const EVENT_DEATH                   = 6;         // On Creature Death
    public const EVENT_EVADE                   = 7;         // On Creature Evade Attack
    public const EVENT_SPELLHIT                = 8;         // On Creature/Gameobject Spell Hit
    public const EVENT_RANGE                   = 9;         // On Target In Range
    public const EVENT_OOC_LOS                 = 10;        // On Target In Distance Out of Combat
    public const EVENT_RESPAWN                 = 11;        // On Creature/Gameobject Respawn
    public const EVENT_TARGET_HEALTH_PCT       = 12;        // [DEPRECATED] On Target Health Percentage
    public const EVENT_VICTIM_CASTING          = 13;        // On Target Casting Spell
    public const EVENT_FRIENDLY_HEALTH         = 14;        // [DEPRECATED] On Friendly Health Deficit
    public const EVENT_FRIENDLY_IS_CC          = 15;        //
    public const EVENT_FRIENDLY_MISSING_BUFF   = 16;        // On Friendly Lost Buff
    public const EVENT_SUMMONED_UNIT           = 17;        // On Creature/Gameobject Summoned Unit
    public const EVENT_TARGET_MANA_PCT         = 18;        // [DEPRECATED] On Target Mana Percentage
    public const EVENT_ACCEPTED_QUEST          = 19;        // On Target Accepted Quest
    public const EVENT_REWARD_QUEST            = 20;        // On Target Rewarded Quest
    public const EVENT_REACHED_HOME            = 21;        // On Creature Reached Home
    public const EVENT_RECEIVE_EMOTE           = 22;        // On Receive Emote.
    public const EVENT_HAS_AURA                = 23;        // On Creature Has Aura
    public const EVENT_TARGET_BUFFED           = 24;        // On Target Buffed With Spell
    public const EVENT_RESET                   = 25;        // After Combat, On Respawn or Spawn
    public const EVENT_IC_LOS                  = 26;        // On Target In Distance In Combat
    public const EVENT_PASSENGER_BOARDED       = 27;        //
    public const EVENT_PASSENGER_REMOVED       = 28;        //
    public const EVENT_CHARMED                 = 29;        // On Creature Charmed
    public const EVENT_CHARMED_TARGET          = 30;        // [DEPRECATED] On Target Charmed
    public const EVENT_SPELLHIT_TARGET         = 31;        // On Target Spell Hit
    public const EVENT_DAMAGED                 = 32;        // On Creature Damaged
    public const EVENT_DAMAGED_TARGET          = 33;        // On Target Damaged
    public const EVENT_MOVEMENTINFORM          = 34;        // WAYPOINT_MOTION_TYPE = 2,  POINT_MOTION_TYPE = 8
    public const EVENT_SUMMON_DESPAWNED        = 35;        // On Summoned Unit Despawned
    public const EVENT_CORPSE_REMOVED          = 36;        // On Creature Corpse Removed
    public const EVENT_AI_INIT                 = 37;        //
    public const EVENT_DATA_SET                = 38;        // On Creature/Gameobject Data Set, Can be used with SMART_ACTION_SET_DATA
    public const EVENT_WAYPOINT_START          = 39;        // [DEPRECATED] On Creature Waypoint ID Started
    public const EVENT_WAYPOINT_REACHED        = 40;        // On Creature Waypoint ID Reached
    public const EVENT_TRANSPORT_ADDPLAYER     = 41;        // [RESERVED]
    public const EVENT_TRANSPORT_ADDCREATURE   = 42;        // [RESERVED]
    public const EVENT_TRANSPORT_REMOVE_PLAYER = 43;        // [RESERVED]
    public const EVENT_TRANSPORT_RELOCATE      = 44;        // [RESERVED]
    public const EVENT_INSTANCE_PLAYER_ENTER   = 45;        // [RESERVED]
    public const EVENT_AREATRIGGER_ONTRIGGER   = 46;        //
    public const EVENT_QUEST_ACCEPTED          = 47;        // [RESERVED] On Target Quest Accepted
    public const EVENT_QUEST_OBJ_COMPLETION    = 48;        // [RESERVED] On Target Quest Objective Completed
    public const EVENT_QUEST_COMPLETION        = 49;        // [RESERVED] On Target Quest Completed
    public const EVENT_QUEST_REWARDED          = 50;        // [RESERVED] On Target Quest Rewarded
    public const EVENT_QUEST_FAIL              = 51;        // [RESERVED] On Target Quest Field
    public const EVENT_TEXT_OVER               = 52;        // On TEXT_OVER Event Triggered After SMART_ACTION_TALK
    public const EVENT_RECEIVE_HEAL            = 53;        // On Creature Received Healing
    public const EVENT_JUST_SUMMONED           = 54;        // On Creature Just spawned
    public const EVENT_WAYPOINT_PAUSED         = 55;        // On Creature Paused at Waypoint ID
    public const EVENT_WAYPOINT_RESUMED        = 56;        // On Creature Resumed after Waypoint ID
    public const EVENT_WAYPOINT_STOPPED        = 57;        // On Creature Stopped On Waypoint ID
    public const EVENT_WAYPOINT_ENDED          = 58;        // On Creature Waypoint Path Ended
    public const EVENT_TIMED_EVENT_TRIGGERED   = 59;        //
    public const EVENT_UPDATE                  = 60;        //
    public const EVENT_LINK                    = 61;        // Used to link together multiple events as a chain of events.
    public const EVENT_GOSSIP_SELECT           = 62;        // On gossip clicked (gossip_menu_option335).
    public const EVENT_JUST_CREATED            = 63;        //
    public const EVENT_GOSSIP_HELLO            = 64;        // On Right-Click Creature/Gameobject that have gossip enabled.
    public const EVENT_FOLLOW_COMPLETED        = 65;        //
    public const EVENT_EVENT_PHASE_CHANGE      = 66;        // [DEPRECATED] On event phase mask set
    public const EVENT_IS_BEHIND_TARGET        = 67;        // [DEPRECATED] On Creature is behind target.
    public const EVENT_GAME_EVENT_START        = 68;        // On game_event started.
    public const EVENT_GAME_EVENT_END          = 69;        // On game_event ended.
    public const EVENT_GO_LOOT_STATE_CHANGED   = 70;        //
    public const EVENT_GO_EVENT_INFORM         = 71;        //
    public const EVENT_ACTION_DONE             = 72;        //
    public const EVENT_ON_SPELLCLICK           = 73;        //
    public const EVENT_FRIENDLY_HEALTH_PCT     = 74;        //
    public const EVENT_DISTANCE_CREATURE       = 75;        // On creature guid OR any instance of creature entry is within distance.
    public const EVENT_DISTANCE_GAMEOBJECT     = 76;        // On gameobject guid OR any instance of gameobject entry is within distance.
    public const EVENT_COUNTER_SET             = 77;        // If the value of specified counterID is equal to a specified value
    public const EVENT_SCENE_START             = 78;        // [RESERVED] don't use on 3.3.5a
    public const EVENT_SCENE_TRIGGER           = 79;        // [RESERVED] don't use on 3.3.5a
    public const EVENT_SCENE_CANCEL            = 80;        // [RESERVED] don't use on 3.3.5a
    public const EVENT_SCENE_COMPLETE          = 81;        // [RESERVED] don't use on 3.3.5a
    public const EVENT_SUMMONED_UNIT_DIES      = 82;        //
    public const EVENT_ON_SPELL_CAST           = 83;        // On Spell::cast
    public const EVENT_ON_SPELL_FAILED         = 84;        // On Unit::InterruptSpell
    public const EVENT_ON_SPELL_START          = 85;        // On Spell::prapare
    public const EVENT_ON_DESPAWN              = 86;        // On before creature removed

    public const FLAG_NO_REPEAT        = 0x0001;
    public const FLAG_DIFFICULTY_0     = 0x0002;
    public const FLAG_DIFFICULTY_1     = 0x0004;
    public const FLAG_DIFFICULTY_2     = 0x0008;
    public const FLAG_DIFFICULTY_3     = 0x0010;
    public const FLAG_DEBUG_ONLY       = 0x0080;
    public const FLAG_NO_RESET         = 0x0100;
    public const FLAG_WHILE_CHARMED    = 0x0200;
    public const FLAG_ALL_DIFFICULTIES = self::FLAG_DIFFICULTY_0 | self::FLAG_DIFFICULTY_1 | self::FLAG_DIFFICULTY_2 | self::FLAG_DIFFICULTY_3;

    private const EVENT_CELL_TPL = '[tooltip name=e-#rowIdx#]%1$s[/tooltip][span tooltip=e-#rowIdx#]%2$s[/span]';

    private array $data = array(                          // param 1-5 - int > 0: type, array: [fn, newIdx, extraParam]; error class: int
        self::EVENT_UPDATE_IC               => [['numRange', 10, true],       null,                       ['numRange', -1, true], null,                   null, 0], // InitialMin, InitialMax, RepeatMin, RepeatMax
        self::EVENT_UPDATE_OOC              => [['numRange', 10, true],       null,                       ['numRange', -1, true], null,                   null, 0], // InitialMin, InitialMax, RepeatMin, RepeatMax
        self::EVENT_HEALTH_PCT              => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // HPMin%, HPMax%,  RepeatMin, RepeatMax
        self::EVENT_MANA_PCT                => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // ManaMin%, ManaMax%, RepeatMin, RepeatMax
        self::EVENT_AGGRO                   => [null,                         null,                       null,                   null,                   null, 0], // NONE
        self::EVENT_KILL                    => [['numRange', -1, true],       null,                       null,                   Type::NPC,              null, 0], // CooldownMin0, CooldownMax1, playerOnly2, else creature entry3
        self::EVENT_DEATH                   => [null,                         null,                       null,                   null,                   null, 0], // NONE
        self::EVENT_EVADE                   => [null,                         null,                       null,                   null,                   null, 0], // NONE
        self::EVENT_SPELLHIT                => [Type::SPELL,                  ['magicSchool', 10, false], ['numRange', -1, true], null,                   null, 0], // SpellID, School, CooldownMin, CooldownMax
        self::EVENT_RANGE                   => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // MinDist, MaxDist, RepeatMin, RepeatMax
        self::EVENT_OOC_LOS                 => [['hostilityMode', 10, false], null,                       ['numRange', -1, true], null,                   null, 0], // hostilityModes, MaxRange, CooldownMin, CooldownMax
        self::EVENT_RESPAWN                 => [null,                         null,                       Type::ZONE,             null,                   null, 0], // type, MapId, ZoneId
        self::EVENT_TARGET_HEALTH_PCT       => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_VICTIM_CASTING          => [['numRange', -1, true],       null,                       Type::SPELL,            null,                   null, 0], // RepeatMin, RepeatMax, spellid
        self::EVENT_FRIENDLY_HEALTH         => [null,                         null,                       ['numRange', -1, true], null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_FRIENDLY_IS_CC          => [null,                         ['numRange', -1, true],     null,                   null,                   null, 0], // Radius, RepeatMin, RepeatMax
        self::EVENT_FRIENDLY_MISSING_BUFF   => [Type::SPELL,                  null,                       ['numRange', -1, true], null,                   null, 0], // SpellId, Radius, RepeatMin, RepeatMax
        self::EVENT_SUMMONED_UNIT           => [Type::NPC,                    ['numRange', -1, true],     null,                   null,                   null, 0], // CreatureId(0 all), CooldownMin, CooldownMax
        self::EVENT_TARGET_MANA_PCT         => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_ACCEPTED_QUEST          => [Type::QUEST,                  ['numRange', -1, true],     null,                   null,                   null, 0], // QuestID (0 = any), CooldownMin, CooldownMax
        self::EVENT_REWARD_QUEST            => [Type::QUEST,                  ['numRange', -1, true],     null,                   null,                   null, 0], // QuestID (0 = any), CooldownMin, CooldownMax
        self::EVENT_REACHED_HOME            => [null,                         null,                       null,                   null,                   null, 0], // NONE
        self::EVENT_RECEIVE_EMOTE           => [Type::EMOTE,                  ['numRange', -1, true],     null,                   null,                   null, 0], // EmoteId, CooldownMin, CooldownMax, condition, val1, val2, val3
        self::EVENT_HAS_AURA                => [Type::SPELL,                  null,                       ['numRange', -1, true], null,                   null, 0], // Param1 = SpellID, Param2 = Stack amount, Param3/4 RepeatMin, RepeatMax
        self::EVENT_TARGET_BUFFED           => [Type::SPELL,                  null,                       ['numRange', -1, true], null,                   null, 0], // Param1 = SpellID, Param2 = Stack amount, Param3/4 RepeatMin, RepeatMax
        self::EVENT_RESET                   => [null,                         null,                       null,                   null,                   null, 0], // Called after combat, when the creature respawn and spawn.
        self::EVENT_IC_LOS                  => [['hostilityMode', 10, false], null,                       ['numRange', -1, true], null,                   null, 0], // hostilityModes, MaxRnage, CooldownMin, CooldownMax
        self::EVENT_PASSENGER_BOARDED       => [['numRange', -1, true],       null,                       null,                   null,                   null, 0], // CooldownMin, CooldownMax
        self::EVENT_PASSENGER_REMOVED       => [['numRange', -1, true],       null,                       null,                   null,                   null, 0], // CooldownMin, CooldownMax
        self::EVENT_CHARMED                 => [null,                         null,                       null,                   null,                   null, 0], // onRemove (0 - on apply, 1 - on remove)
        self::EVENT_CHARMED_TARGET          => [null,                         null,                       null,                   null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_SPELLHIT_TARGET         => [Type::SPELL,                  ['magicSchool', 10, false], ['numRange', -1, true], null,                   null, 0], // SpellID, School, CooldownMin, CooldownMax
        self::EVENT_DAMAGED                 => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // MinDmg, MaxDmg, CooldownMin, CooldownMax
        self::EVENT_DAMAGED_TARGET          => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // MinDmg, MaxDmg, CooldownMin, CooldownMax
        self::EVENT_MOVEMENTINFORM          => [['motionType', 10, false],    null,                       null,                   null,                   null, 0], // MovementType(any), PointID
        self::EVENT_SUMMON_DESPAWNED        => [Type::NPC,                    ['numRange', -1, true],     null,                   null,                   null, 0], // Entry, CooldownMin, CooldownMax
        self::EVENT_CORPSE_REMOVED          => [null,                         null,                       null,                   null,                   null, 0], // NONE
        self::EVENT_AI_INIT                 => [null,                         null,                       null,                   null,                   null, 0], // NONE
        self::EVENT_DATA_SET                => [null,                         null,                       ['numRange', -1, true], null,                   null, 0], // Id, Value, CooldownMin, CooldownMax
        self::EVENT_WAYPOINT_START          => [null,                         null,                       null,                   null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_WAYPOINT_REACHED        => [null,                         null,                       null,                   null,                   null, 0], // PointId(0any), pathID(0any)
        self::EVENT_TRANSPORT_ADDPLAYER     => [null,                         null,                       null,                   null,                   null, 2], // NONE
        self::EVENT_TRANSPORT_ADDCREATURE   => [null,                         null,                       null,                   null,                   null, 2], // Entry (0 any)
        self::EVENT_TRANSPORT_REMOVE_PLAYER => [null,                         null,                       null,                   null,                   null, 2], // NONE
        self::EVENT_TRANSPORT_RELOCATE      => [null,                         null,                       null,                   null,                   null, 2], // PointId
        self::EVENT_INSTANCE_PLAYER_ENTER   => [null,                         null,                       null,                   null,                   null, 2], // Team (0 any), CooldownMin, CooldownMax
        self::EVENT_AREATRIGGER_ONTRIGGER   => [Type::AREATRIGGER,            null,                       null,                   null,                   null, 0], // TriggerId(0 any)
        self::EVENT_QUEST_ACCEPTED          => [null,                         null,                       null,                   null,                   null, 2], // none
        self::EVENT_QUEST_OBJ_COMPLETION    => [null,                         null,                       null,                   null,                   null, 2], // none
        self::EVENT_QUEST_COMPLETION        => [null,                         null,                       null,                   null,                   null, 2], // none
        self::EVENT_QUEST_REWARDED          => [null,                         null,                       null,                   null,                   null, 2], // none
        self::EVENT_QUEST_FAIL              => [null,                         null,                       null,                   null,                   null, 2], // none
        self::EVENT_TEXT_OVER               => [null,                         Type::NPC,                  null,                   null,                   null, 0], // GroupId from creature_text,  creature entry who talks (0 any)
        self::EVENT_RECEIVE_HEAL            => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // MinHeal, MaxHeal, CooldownMin, CooldownMax
        self::EVENT_JUST_SUMMONED           => [null,                         null,                       null,                   null,                   null, 0], // none
        self::EVENT_WAYPOINT_PAUSED         => [null,                         null,                       null,                   null,                   null, 0], // PointId(0any), pathID(0any)
        self::EVENT_WAYPOINT_RESUMED        => [null,                         null,                       null,                   null,                   null, 0], // PointId(0any), pathID(0any)
        self::EVENT_WAYPOINT_STOPPED        => [null,                         null,                       null,                   null,                   null, 0], // PointId(0any), pathID(0any)
        self::EVENT_WAYPOINT_ENDED          => [null,                         null,                       null,                   null,                   null, 0], // PointId(0any), pathID(0any)
        self::EVENT_TIMED_EVENT_TRIGGERED   => [null,                         null,                       null,                   null,                   null, 0], // id
        self::EVENT_UPDATE                  => [['numRange', 10, true],       null,                       ['numRange', -1, true], null,                   null, 0], // InitialMin, InitialMax, RepeatMin, RepeatMax
        self::EVENT_LINK                    => [null,                         null,                       null,                   null,                   null, 0], // INTERNAL USAGE, no params, used to link together multiple events, does not use any extra resources to iterate event lists needlessly
        self::EVENT_GOSSIP_SELECT           => [null,                         null,                       null,                   null,                   null, 0], // menuID, actionID
        self::EVENT_JUST_CREATED            => [null,                         null,                       null,                   null,                   null, 0], // none
        self::EVENT_GOSSIP_HELLO            => [null,                         null,                       null,                   null,                   null, 0], // noReportUse (for GOs)
        self::EVENT_FOLLOW_COMPLETED        => [null,                         null,                       null,                   null,                   null, 0], // none
        self::EVENT_EVENT_PHASE_CHANGE      => [null,                         null,                       null,                   null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_IS_BEHIND_TARGET        => [['numRange', -1, true],       null,                       null,                   null,                   null, 1], // UNUSED, DO NOT REUSE
        self::EVENT_GAME_EVENT_START        => [Type::WORLDEVENT,             null,                       null,                   null,                   null, 0], // game_event.Entry
        self::EVENT_GAME_EVENT_END          => [Type::WORLDEVENT,             null,                       null,                   null,                   null, 0], // game_event.Entry
        self::EVENT_GO_LOOT_STATE_CHANGED   => [['lootState', 10, false],     null,                       null,                   null,                   null, 0], // go LootState
        self::EVENT_GO_EVENT_INFORM         => [null,                         null,                       null,                   null,                   null, 0], // eventId
        self::EVENT_ACTION_DONE             => [null,                         null,                       null,                   null,                   null, 0], // eventId (SharedDefines.EventId)
        self::EVENT_ON_SPELLCLICK           => [null,                         null,                       null,                   null,                   null, 0], // clicker (unit)
        self::EVENT_FRIENDLY_HEALTH_PCT     => [['numRange', 10, false],      null,                       ['numRange', -1, true], null,                   null, 0], // minHpPct, maxHpPct, repeatMin, repeatMax
        self::EVENT_DISTANCE_CREATURE       => [null,                         Type::NPC,                  null,                   ['numRange', -1, true], null, 0], // guid, entry, distance, repeat
        self::EVENT_DISTANCE_GAMEOBJECT     => [null,                         Type::OBJECT,               null,                   ['numRange', -1, true], null, 0], // guid, entry, distance, repeat
        self::EVENT_COUNTER_SET             => [null,                         null,                       ['numRange', -1, true], null,                   null, 0], // id, value, cooldownMin, cooldownMax
        self::EVENT_SCENE_START             => [null,                         null,                       null,                   null,                   null, 2], // don't use on 3.3.5a
        self::EVENT_SCENE_TRIGGER           => [null,                         null,                       null,                   null,                   null, 2], // don't use on 3.3.5a
        self::EVENT_SCENE_CANCEL            => [null,                         null,                       null,                   null,                   null, 2], // don't use on 3.3.5a
        self::EVENT_SCENE_COMPLETE          => [null,                         null,                       null,                   null,                   null, 2], // don't use on 3.3.5a
        self::EVENT_SUMMONED_UNIT_DIES      => [Type::NPC,                    ['numRange', -1, true],     null,                   null,                   null, 0], // CreatureId(0 all), CooldownMin, CooldownMax
        self::EVENT_ON_SPELL_CAST           => [Type::SPELL,                  ['numRange', -1, true],     null,                   null,                   null, 0], // SpellID, CooldownMin, CooldownMax
        self::EVENT_ON_SPELL_FAILED         => [Type::SPELL,                  ['numRange', -1, true],     null,                   null,                   null, 0], // SpellID, CooldownMin, CooldownMax
        self::EVENT_ON_SPELL_START          => [Type::SPELL,                  ['numRange', -1, true],     null,                   null,                   null, 0], // SpellID, CooldownMin, CooldownMax
        self::EVENT_ON_DESPAWN              => [null,                         null,                       null,                   null,                   null, 0]  // NONE
    );

    private array $jsGlobals = [];

    public function __construct(
        private int $id,
        public readonly int $type,
        public readonly int $phaseMask,
        public readonly int $chance,
        private int $flags,
        private array $param,
        private SmartAI &$smartAI)
    {
        // additional parameters
        Util::checkNumeric($this->param, NUM_CAST_INT);
        $this->param = array_pad($this->param, 15, '');
    }

    public function process() : array
    {
        $body   =
        $footer = '';

        $phases = Util::mask2bits($this->phaseMask, 1) ?: [0];
        $eventTT = Lang::smartAI('eventTT', array_merge([$this->type, $phases, $this->chance, $this->flags], $this->param));

        for ($i = 0; $i < 5; $i++)
        {
            $eParams = $this->data[$this->type];

            if (is_array($eParams[$i]))
            {
                [$fn, $idx, $extraParam] = $eParams[$i];

                if ($idx < 0)
                    $footer = $this->{$fn}($this->param[$i], $this->param[$i + 1], $extraParam);
                else
                    $this->param[$idx] = $this->{$fn}($this->param[$i], $this->param[$i + 1], $extraParam);
            }
            else if (is_int($eParams[$i]) && $this->param[$i])
                $this->jsGlobals[$eParams[$i]][$this->param[$i]] = $this->param[$i];
        }

        // non-generic cases
        switch ($this->type)
        {
            case self::EVENT_UPDATE_IC:                     // 0   -  In combat.
            case self::EVENT_UPDATE_OOC:                    // 1   -  Out of combat.
                if ($this->smartAI->srcType == SmartAI::SRC_TYPE_ACTIONLIST)
                    $this->param[11] = 1;
                // do not break;
            case self::EVENT_GOSSIP_HELLO:                  // 64  -  On Right-Click Creature/Gameobject that have gossip enabled.
                if ($this->smartAI->srcType == SmartAI::SRC_TYPE_OBJECT)
                    $footer = array(
                        $this->param[0] == 1,
                        $this->param[0] == 2,
                    );
                break;
            case self::EVENT_RESPAWN:                       // 11  -  On Creature/Gameobject Respawn in Zone/Map
                if ($this->param[0] == 1)                   // per map
                {
                    switch ($this->param[1])
                    {
                        case 0:   $this->param[10] = Lang::maps('EasternKingdoms'); break;
                        case 1:   $this->param[10] = Lang::maps('Kalimdor');        break;
                        case 530: $this->param[10] = Lang::maps('Outland');         break;
                        case 571: $this->param[10] = Lang::maps('Northrend');       break;
                        default:
                            if ($aId = DB::Aowow()->selectCell('SELECT `id` FROM ?_zones WHERE `mapId` = ?d', $this->param[1]))
                            {
                                $this->param[11] = $aId;
                                $this->jsGlobals[Type::ZONE][$aId] = $aId;
                            }
                            else
                                $this->param[11] = '[span class=q10]Unknown Map[/span] #'.$this->param[1];
                    };
                }
                else if ($this->param[0] == 2)              // per zone
                    $this->param[11] = $this->param[2];

                break;
            case self::EVENT_LINK:                          // 61  -  Used to link together multiple events as a chain of events.
                if ($links = DB::World()->selectCol('SELECT `id` FROM smart_scripts WHERE `link` = ?d AND `entryorguid` = ?d AND `source_type` = ?d', $this->id, $this->smartAI->entry, $this->smartAI->srcType))
                    $this->param[10] = LANG::concat($links, false, fn($x) => "#[b]".$x."[/b]");
                break;
            case self::EVENT_GOSSIP_SELECT:                 // 62  -  On gossip clicked (gossip_menu_option335).
                $gmo = DB::World()->selectRow(
                   'SELECT    gmo.`OptionText` AS "text_loc0" {, gmol.`OptionText` AS text_loc?d }
                    FROM      gossip_menu_option gmo
                    LEFT JOIN gossip_menu_option_locale gmol ON gmo.`MenuID` = gmol.`MenuID` AND gmo.`OptionID` = gmol.`OptionID` AND gmol.`Locale` = ?d
                    WHERE     gmo.`MenuId` = ?d AND gmo.`OptionID` = ?d',
                    Lang::getLocale() != WoWLocale::EN ? Lang::getLocale()->value : DBSIMPLE_SKIP,
                    Lang::getLocale()->json(),
                    $this->param[0], $this->param[1]
                );

                if ($gmo)
                    $this->param[10] = Util::jsEscape(Util::localizedString($gmo, 'text'));
                else
                    trigger_error('SmartAI::event - could not find gossip menu option for event #'.$this->type);
                break;
            case self::EVENT_DISTANCE_CREATURE:             // 75  -  On creature guid OR any instance of creature entry is within distance.
                if ($this->param[0])
                    if ($_ = $this->resolveGuid(Type::NPC, $this->param[0]))
                    {
                        $this->jsGlobals[Type::NPC][$this->param[0]] = $this->param[0];
                        $this->param[10] = $_;
                    }
                // do not break;
            case self::EVENT_DISTANCE_GAMEOBJECT:           // 76  -  On gameobject guid OR any instance of gameobject entry is within distance.
                if ($this->param[0] && !$this->param[10])
                {
                    if ($_ = $this->resolveGuid(Type::OBJECT, $this->param[0]))
                    {
                        $this->jsGlobals[Type::OBJECT][$this->param[0]] = $this->param[0];
                        $this->param[10] = $_;
                    }
                }
                else if ($this->param[1])
                    $this->param[10] = $this->param[1];
                else if (!$this->param[10])
                    trigger_error('SmartAI::event - entity for event #'.$this->type.' not defined');
                break;
            case self::EVENT_EVENT_PHASE_CHANGE:            // 66  -  On event phase mask set
                $this->param[10] = Lang::concat(Util::mask2bits($this->param[0]), false);
                break;
        }

        $this->smartAI->addJsGlobals($this->jsGlobals);

        $body = Lang::smartAI('events', $this->type, 0, $this->param) ?? Lang::smartAI('eventUNK', [$this->type]);
        if ($footer)
            $footer = Lang::smartAI('events', $this->type, 1, (array)$footer);

        // resolve conditionals
        $i = 0;
        while (strstr($body, ')?') && $i++ < 3)
            $body   = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):(([^;]*);*);/i', fn($m) => $m[1] ? $m[2] : $m[3], $body);

        $i = 0;
        while (strstr($footer, ')?') && $i++ < 3)
            $footer = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):(([^;]*);*);/i', fn($m) => $m[1] ? $m[2] : $m[3], $footer);

        if ($_ = $this->formatFlags())
            $footer = $_ . ($footer ? '; '.$footer : '');

        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            if ($eParams[5] == 1)
                $footer = '[span class=rep2]DEPRECATED[/span] ' . $footer;
            else if ($eParams[5] == 2)
                $footer = '[span class=rep0]RESERVED[/span] ' . $footer;
        }

        // wrap body in tooltip
        return [sprintf(self::EVENT_CELL_TPL, $eventTT, $body), $footer];
    }

    public function hasPhases() : bool
    {
        return $this->phaseMask == 0;
    }

    private function formatFlags() : string
    {
        $flags = $this->flags;

        if (($flags & self::FLAG_ALL_DIFFICULTIES) == self::FLAG_ALL_DIFFICULTIES)
            $flags &= ~self::FLAG_ALL_DIFFICULTIES;

        $ef = [];
        for ($i = 1; $i <= self::FLAG_WHILE_CHARMED; $i <<= 1)
            if ($flags & $i)
                if ($x = Lang::smartAI('eventFlags', $i))
                    $ef[] = $x;

        return Lang::concat($ef);
    }
}

?>
