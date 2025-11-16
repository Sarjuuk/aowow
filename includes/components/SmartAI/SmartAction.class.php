<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// TrinityCore - SmartAI
class SmartAction
{
    use SmartHelper;

    public const ACTION_NONE                               = 0;   //  Do nothing
    public const ACTION_TALK                               = 1;   //  Param2 in Milliseconds.
    public const ACTION_SET_FACTION                        = 2;   //  Sets faction to creature.
    public const ACTION_MORPH_TO_ENTRY_OR_MODEL            = 3;   //  Take DisplayID of creature (param1) OR Turn to DisplayID (param2) OR Both = 0 for Demorph
    public const ACTION_SOUND                              = 4;   //  TextRange = 0 only sends sound to self, TextRange = 1 sends sound to everyone in visibility range
    public const ACTION_PLAY_EMOTE                         = 5;   //  Play Emote
    public const ACTION_FAIL_QUEST                         = 6;   //  Fail Quest of Target
    public const ACTION_OFFER_QUEST                        = 7;   //  Add Quest to Target
    public const ACTION_SET_REACT_STATE                    = 8;   //  React State. Can be Passive (0), Defensive (1), Aggressive (2), Assist (3).
    public const ACTION_ACTIVATE_GOBJECT                   = 9;   //  Activate Object
    public const ACTION_RANDOM_EMOTE                       = 10;  //  Play Random Emote
    public const ACTION_CAST                               = 11;  //  Cast Spell ID at Target
    public const ACTION_SUMMON_CREATURE                    = 12;  //  Summon Unit
    public const ACTION_THREAT_SINGLE_PCT                  = 13;  //  Change Threat Percentage for Single Target
    public const ACTION_THREAT_ALL_PCT                     = 14;  //  Change Threat Percentage for All Enemies
    public const ACTION_CALL_AREAEXPLOREDOREVENTHAPPENS    = 15;  //
    public const ACTION_SET_INGAME_PHASE_ID                = 16;  //  [RESERVED] For 4.3.4 + only
    public const ACTION_SET_EMOTE_STATE                    = 17;  //  Play Emote Continuously
    public const ACTION_SET_UNIT_FLAG                      = 18;  //  [DEPRECATED] Can set Multi-able flags at once
    public const ACTION_REMOVE_UNIT_FLAG                   = 19;  //  [DEPRECATED] Can Remove Multi-able flags at once
    public const ACTION_AUTO_ATTACK                        = 20;  //  Stop or Continue Automatic Attack.
    public const ACTION_ALLOW_COMBAT_MOVEMENT              = 21;  //  Allow or Disable Combat Movement
    public const ACTION_SET_EVENT_PHASE                    = 22;  //
    public const ACTION_INC_EVENT_PHASE                    = 23;  //  Set param1 OR param2 (not both). Value 0 has no effect.
    public const ACTION_EVADE                              = 24;  //  Evade Incoming Attack
    public const ACTION_FLEE_FOR_ASSIST                    = 25;  //  If you want the fleeing NPC to say '%s attempts to run away in fear' on flee, use 1 on param1. 0 for no message.
    public const ACTION_CALL_GROUPEVENTHAPPENS             = 26;  //
    public const ACTION_COMBAT_STOP                        = 27;  //
    public const ACTION_REMOVEAURASFROMSPELL               = 28;  //  0 removes all auras
    public const ACTION_FOLLOW                             = 29;  //  Follow Target
    public const ACTION_RANDOM_PHASE                       = 30;  //
    public const ACTION_RANDOM_PHASE_RANGE                 = 31;  //
    public const ACTION_RESET_GOBJECT                      = 32;  //  Reset Gameobject
    public const ACTION_CALL_KILLEDMONSTER                 = 33;  //  This is the ID from quest_template.RequiredNpcOrGo
    public const ACTION_SET_INST_DATA                      = 34;  //  Set Instance Data
    public const ACTION_SET_INST_DATA64                    = 35;  //  Set Instance Data uint64
    public const ACTION_UPDATE_TEMPLATE                    = 36;  //  Updates creature_template to given entry
    public const ACTION_DIE                                = 37;  //  Kill Target
    public const ACTION_SET_IN_COMBAT_WITH_ZONE            = 38;  //
    public const ACTION_CALL_FOR_HELP                      = 39;  //  If you want the NPC to say '%s calls for help!'. Use 1 on param1, 0 for no message.
    public const ACTION_SET_SHEATH                         = 40;  //
    public const ACTION_FORCE_DESPAWN                      = 41;  //  Despawn Target after param1 in Milliseconds. If you want to set respawn time set param2 in seconds.
    public const ACTION_SET_INVINCIBILITY_HP_LEVEL         = 42;  //  If you use both params, only percent will be used.
    public const ACTION_MOUNT_TO_ENTRY_OR_MODEL            = 43;  //  Mount to Creature Entry (param1) OR Mount to Creature Display (param2) Or both = 0 for Unmount
    public const ACTION_SET_INGAME_PHASE_MASK              = 44;  //
    public const ACTION_SET_DATA                           = 45;  //  Set Data For Target, can be used with SMART_EVENT_DATA_SET
    public const ACTION_ATTACK_STOP                        = 46;  //
    public const ACTION_SET_VISIBILITY                     = 47;  //  Makes creature Visible = 1  or  Invisible = 0
    public const ACTION_SET_ACTIVE                         = 48;  //
    public const ACTION_ATTACK_START                       = 49;  //  Allows basic melee swings to creature.
    public const ACTION_SUMMON_GO                          = 50;  //  Spawns Gameobject, use target_type to set spawn position.
    public const ACTION_KILL_UNIT                          = 51;  //  Kills Creature.
    public const ACTION_ACTIVATE_TAXI                      = 52;  //  Sends player to flight path. You have to be close to Flight Master, which gives Taxi ID you need.
    public const ACTION_WP_START                           = 53;  //  Creature starts Waypoint Movement. Use waypoint_data table to create movement.
    public const ACTION_WP_PAUSE                           = 54;  //  Creature pauses its Waypoint Movement for given time.
    public const ACTION_WP_STOP                            = 55;  //  Creature stops its Waypoint Movement.
    public const ACTION_ADD_ITEM                           = 56;  //  Adds item(s) to player.
    public const ACTION_REMOVE_ITEM                        = 57;  //  Removes item(s) from player.
    public const ACTION_INSTALL_AI_TEMPLATE                = 58;  //  [DEPRECATED]
    public const ACTION_SET_RUN                            = 59;  //
    public const ACTION_SET_DISABLE_GRAVITY                = 60;  //  Only works for creatures with inhabit air.
    public const ACTION_SET_SWIM                           = 61;  //  [DEPRECATED]
    public const ACTION_TELEPORT                           = 62;  //  Continue this action with the TARGET_TYPE column. Use any target_type (except 0), and use target_x, target_y, target_z, target_o as the coordinates
    public const ACTION_SET_COUNTER                        = 63;  //
    public const ACTION_STORE_TARGET_LIST                  = 64;  //
    public const ACTION_WP_RESUME                          = 65;  //  Creature continues in its Waypoint Movement.
    public const ACTION_SET_ORIENTATION                    = 66;  //
    public const ACTION_CREATE_TIMED_EVENT                 = 67;  //
    public const ACTION_PLAYMOVIE                          = 68;  //
    public const ACTION_MOVE_TO_POS                        = 69;  //  PointId is called by SMART_EVENT_MOVEMENTINFORM. Continue this action with the TARGET_TYPE column. Use any target_type, and use target_x, target_y, target_z, target_o as the coordinates
    public const ACTION_ENABLE_TEMP_GOBJ                   = 70;  //  param1 = duration
    public const ACTION_EQUIP                              = 71;  //  only slots with mask set will be sent to client, bits are 1, 2, 4, leaving mask 0 is defaulted to mask 7 (send all), Slots1-3 are only used if no Param1 is set
    public const ACTION_CLOSE_GOSSIP                       = 72;  //  Closes gossip window.
    public const ACTION_TRIGGER_TIMED_EVENT                = 73;  //
    public const ACTION_REMOVE_TIMED_EVENT                 = 74;  //
    public const ACTION_ADD_AURA                           = 75;  //  [DEPRECATED] Adds aura to player(s). Use target_type 17 to make AoE aura.
    public const ACTION_OVERRIDE_SCRIPT_BASE_OBJECT        = 76;  //  [DEPRECATED] WARNING: CAN CRASH CORE, do not use if you dont know what you are doing
    public const ACTION_RESET_SCRIPT_BASE_OBJECT           = 77;  //  [DEPRECATED]
    public const ACTION_CALL_SCRIPT_RESET                  = 78;  //
    public const ACTION_SET_RANGED_MOVEMENT                = 79;  //  Sets movement to follow at a specific range to the target.
    public const ACTION_CALL_TIMED_ACTIONLIST              = 80;  //
    public const ACTION_SET_NPC_FLAG                       = 81;  //
    public const ACTION_ADD_NPC_FLAG                       = 82;  //
    public const ACTION_REMOVE_NPC_FLAG                    = 83;  //
    public const ACTION_SIMPLE_TALK                        = 84;  //  Makes a player say text. SMART_EVENT_TEXT_OVER is not triggered and whispers can not be used.
    public const ACTION_SELF_CAST                          = 85;  //  spellID, castFlags
    public const ACTION_CROSS_CAST                         = 86;  //  This action is used to make selected caster (in CasterTargetType) to cast spell. Actual target is entered in target_type as normally.
    public const ACTION_CALL_RANDOM_TIMED_ACTIONLIST       = 87;  //  Will select one entry from the ones provided. 0 is ignored.
    public const ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST = 88;  //  0 is ignored.
    public const ACTION_RANDOM_MOVE                        = 89;  //  Creature moves to random position in given radius.
    public const ACTION_SET_UNIT_FIELD_BYTES_1             = 90;  //
    public const ACTION_REMOVE_UNIT_FIELD_BYTES_1          = 91;  //
    public const ACTION_INTERRUPT_SPELL                    = 92;  //  This action allows you to interrupt the current spell being cast. If you do not set the spellId, the core will find the current spell depending on the withDelay and the withInstant values.
    public const ACTION_SEND_GO_CUSTOM_ANIM                = 93;  //  [DEPRECATED] oldFlag = newFlag
    public const ACTION_SET_DYNAMIC_FLAG                   = 94;  //  [DEPRECATED] oldFlag |= newFlag
    public const ACTION_ADD_DYNAMIC_FLAG                   = 95;  //  [DEPRECATED] oldFlag &= ~newFlag
    public const ACTION_REMOVE_DYNAMIC_FLAG                = 96;  //  [DEPRECATED]
    public const ACTION_JUMP_TO_POS                        = 97;  //
    public const ACTION_SEND_GOSSIP_MENU                   = 98;  //  Can be used together with 'SMART_EVENT_GOSSIP_HELLO' to set custom gossip.
    public const ACTION_GO_SET_LOOT_STATE                  = 99;  //
    public const ACTION_SEND_TARGET_TO_TARGET              = 100; //  Send targets previously stored with SMART_ACTION_STORE_TARGET, to another npc/go, the other npc/go can then access them as if it was its own stored list
    public const ACTION_SET_HOME_POS                       = 101; //  Use with SMART_TARGET_SELF or SMART_TARGET_POSITION
    public const ACTION_SET_HEALTH_REGEN                   = 102; //  Sets the current creatures health regen on or off.
    public const ACTION_SET_ROOT                           = 103; //  Enables or disables creature movement
    public const ACTION_SET_GO_FLAG                        = 104; //  [DEPRECATED] oldFlag = newFlag
    public const ACTION_ADD_GO_FLAG                        = 105; //  [DEPRECATED] oldFlag |= newFlag
    public const ACTION_REMOVE_GO_FLAG                     = 106; //  [DEPRECATED] oldFlag &= ~newFlag
    public const ACTION_SUMMON_CREATURE_GROUP              = 107; //  Use creature_summon_groups table. SAI target has no effect, use 0
    public const ACTION_SET_POWER                          = 108; //
    public const ACTION_ADD_POWER                          = 109; //
    public const ACTION_REMOVE_POWER                       = 110; //
    public const ACTION_GAME_EVENT_STOP                    = 111; //
    public const ACTION_GAME_EVENT_START                   = 112; //
    public const ACTION_START_CLOSEST_WAYPOINT             = 113; //  Make target follow closest waypoint to its location
    public const ACTION_MOVE_OFFSET                        = 114; //  Use  target_x,  target_y,  target_z With target_type=1
    public const ACTION_RANDOM_SOUND                       = 115; //
    public const ACTION_SET_CORPSE_DELAY                   = 116; //
    public const ACTION_DISABLE_EVADE                      = 117; //
    public const ACTION_GO_SET_GO_STATE                    = 118; //
    public const ACTION_SET_CAN_FLY                        = 119; //  [DEPRECATED]
    public const ACTION_REMOVE_AURAS_BY_TYPE               = 120; //  [DEPRECATED]
    public const ACTION_SET_SIGHT_DIST                     = 121; //  [DEPRECATED]
    public const ACTION_FLEE                               = 122; //  [DEPRECATED]
    public const ACTION_ADD_THREAT                         = 123; //
    public const ACTION_LOAD_EQUIPMENT                     = 124; //
    public const ACTION_TRIGGER_RANDOM_TIMED_EVENT         = 125; //
    public const ACTION_REMOVE_ALL_GAMEOBJECTS             = 126; //  [DEPRECATED]
    public const ACTION_PAUSE_MOVEMENT                     = 127; //  MovementSlot (default = 0, active = 1, controlled = 2), PauseTime (ms), Force
    public const ACTION_PLAY_ANIMKIT                       = 128; //  [RESERVED] don't use on 3.3.5a
    public const ACTION_SCENE_PLAY                         = 129; //  [RESERVED] don't use on 3.3.5a
    public const ACTION_SCENE_CANCEL                       = 130; //  [RESERVED] don't use on 3.3.5a
    public const ACTION_SPAWN_SPAWNGROUP                   = 131; //
    public const ACTION_DESPAWN_SPAWNGROUP                 = 132; //
    public const ACTION_RESPAWN_BY_SPAWNID                 = 133; //  type, typeGuid - Use to respawn npcs and gobs, the target in this case is always=1 and only a single unit could be a target via the spawnId (action_param1, action_param2)
    public const ACTION_INVOKER_CAST                       = 134; //  spellID, castFlags
    public const ACTION_PLAY_CINEMATIC                     = 135; //  cinematic
    public const ACTION_SET_MOVEMENT_SPEED                 = 136; //  movementType, speedInteger, speedFraction
    public const ACTION_PLAY_SPELL_VISUAL_KIT              = 137; //  [RESERVED] spellVisualKitId
    public const ACTION_OVERRIDE_LIGHT                     = 138; //  zoneId, areaLightId, overrideLightID, transitionMilliseconds
    public const ACTION_OVERRIDE_WEATHER                   = 139; //  zoneId, weatherId, intensity
    public const ACTION_SET_AI_ANIM_KIT                    = 140; //  [RESERVED]
    public const ACTION_SET_HOVER                          = 141; //  Enable/Disable hover for target units.
    public const ACTION_SET_HEALTH_PCT                     = 142; //  Set current health percentage of target units.
    public const ACTION_CREATE_CONVERSATION                = 143; //  [RESERVED]
    public const ACTION_SET_IMMUNE_PC                      = 144; //  Enable/Disable immunity to players of target units.
    public const ACTION_SET_IMMUNE_NPC                     = 145; //  Enable/Disable immunity to creatures of target units.
    public const ACTION_SET_UNINTERACTIBLE                 = 146; //  Make/Reset target units uninteractible.
    public const ACTION_ACTIVATE_GAMEOBJECT                = 147; //  Activate target gameobjects, using given action.
    public const ACTION_ADD_TO_STORED_TARGET_LIST          = 148; //  Add selected targets to varID for later use.
    public const ACTION_BECOME_PERSONAL_CLONE_FOR_PLAYER   = 149; //  [RESERVED]
    public const ACTION_TRIGGER_GAME_EVENT                 = 150; //  [RESERVED]
    public const ACTION_DO_ACTION                          = 151; //  [RESERVED]

    public const ACTION_ALL_SPELLCASTS         = [self::ACTION_CAST, self::ACTION_ADD_AURA, self::ACTION_INVOKER_CAST, self::ACTION_SELF_CAST, self::ACTION_CROSS_CAST];
    public const ACTION_ALL_TIMED_ACTION_LISTS = [self::ACTION_CALL_TIMED_ACTIONLIST, self::ACTION_CALL_RANDOM_TIMED_ACTIONLIST, self::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST];

    private const ACTION_CELL_TPL = '[tooltip name=a-#rowIdx#]%1$s[/tooltip][span tooltip=a-#rowIdx#]%2$s[/span]';
    private const TAL_TAB_ANCHOR  = '[url=#sai-actionlist-%1$d onclick=TalTabClick(%1$d)]#%1$d[/url]';

    private array $data = array(
        self::ACTION_NONE                               => [null, null, null, null, null, null, 0],  // No action
        self::ACTION_TALK                               => [null, ['formatTime', -1, true], null, null, null, null, 0],  // groupID from creature_text, duration to wait before TEXT_OVER event is triggered, useTalkTarget (0/1) - use target as talk target
        self::ACTION_SET_FACTION                        => [null, null, null, null, null, null, 0],  // FactionId (or 0 for default)
        self::ACTION_MORPH_TO_ENTRY_OR_MODEL            => [Type::NPC, null, null, null, null, null, 0],  // Creature_template entry(param1) OR ModelId (param2) (or 0 for both to demorph)
        self::ACTION_SOUND                              => [Type::SOUND, null, null, null, null, null, 0],  // SoundId, onlySelf
        self::ACTION_PLAY_EMOTE                         => [null, null, null, null, null, null, 0],  // EmoteId
        self::ACTION_FAIL_QUEST                         => [Type::QUEST, null, null, null, null, null, 0],  // QuestID
        self::ACTION_OFFER_QUEST                        => [Type::QUEST, null, null, null, null, null, 0],  // QuestID, directAdd
        self::ACTION_SET_REACT_STATE                    => [['reactState', 10, false], null, null, null, null, null, 0],  // state
        self::ACTION_ACTIVATE_GOBJECT                   => [null, null, null, null, null, null, 0],  //
        self::ACTION_RANDOM_EMOTE                       => [null, null, null, null, null, null, 0],  // EmoteId1, EmoteId2, EmoteId3...
        self::ACTION_CAST                               => [Type::SPELL, ['castFlags', -1, false], null, null, null, null, 0],  // SpellId, CastFlags, TriggeredFlags
        self::ACTION_SUMMON_CREATURE                    => [Type::NPC, ['summonType', -1, false], ['formatTime', 10, true], null, null, null, 0],  // CreatureID, summonType, duration in ms, attackInvoker, flags(SmartActionSummonCreatureFlags)
        self::ACTION_THREAT_SINGLE_PCT                  => [null, null, null, null, null, null, 0],  // Threat%
        self::ACTION_THREAT_ALL_PCT                     => [null, null, null, null, null, null, 0],  // Threat%
        self::ACTION_CALL_AREAEXPLOREDOREVENTHAPPENS    => [Type::QUEST, null, null, null, null, null, 0],  // QuestID
        self::ACTION_SET_INGAME_PHASE_ID                => [null, null, null, null, null, null, 2],  // used on 4.3.4 and higher scripts
        self::ACTION_SET_EMOTE_STATE                    => [null, null, null, null, null, null, 0],  // emoteID
        self::ACTION_SET_UNIT_FLAG                      => [['unitFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_REMOVE_UNIT_FLAG                   => [['unitFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_AUTO_ATTACK                        => [null, null, null, null, null, null, 0],  // AllowAttackState (0 = stop attack, anything else means continue attacking)
        self::ACTION_ALLOW_COMBAT_MOVEMENT              => [null, null, null, null, null, null, 0],  // AllowCombatMovement (0 = stop combat based movement, anything else continue attacking)
        self::ACTION_SET_EVENT_PHASE                    => [null, null, null, null, null, null, 0],  // Phase
        self::ACTION_INC_EVENT_PHASE                    => [null, null, null, null, null, null, 0],  // Value (may be negative to decrement phase, should not be 0)
        self::ACTION_EVADE                              => [null, null, null, null, null, null, 0],  // toRespawnPosition (0 = Move to RespawnPosition, 1 = Move to last stored home position)
        self::ACTION_FLEE_FOR_ASSIST                    => [null, null, null, null, null, null, 0],  // With Emote
        self::ACTION_CALL_GROUPEVENTHAPPENS             => [Type::QUEST, null, null, null, null, null, 0],  // QuestID
        self::ACTION_COMBAT_STOP                        => [null, null, null, null, null, null, 0],  //
        self::ACTION_REMOVEAURASFROMSPELL               => [Type::SPELL, null, null, null, null, null, 0],  // Spellid (0 removes all auras), charges (0 removes aura)
        self::ACTION_FOLLOW                             => [null, null, null, null, null, null, 0],  // Distance (0 = default), Angle (0 = default), EndCreatureEntry, credit, creditType (0monsterkill, 1event)
        self::ACTION_RANDOM_PHASE                       => [null, null, null, null, null, null, 0],  // PhaseId1, PhaseId2, PhaseId3...
        self::ACTION_RANDOM_PHASE_RANGE                 => [null, null, null, null, null, null, 0],  // PhaseMin, PhaseMax
        self::ACTION_RESET_GOBJECT                      => [null, null, null, null, null, null, 0],  //
        self::ACTION_CALL_KILLEDMONSTER                 => [Type::NPC, null, null, null, null, null, 0],  // CreatureId,
        self::ACTION_SET_INST_DATA                      => [null, null, null, null, null, null, 0],  // Field, Data, Type (0 = SetData, 1 = SetBossState)
        self::ACTION_SET_INST_DATA64                    => [null, null, null, null, null, null, 0],  // Field,
        self::ACTION_UPDATE_TEMPLATE                    => [Type::NPC, null, null, null, null, null, 0],  // Entry
        self::ACTION_DIE                                => [null, null, null, null, null, null, 0],  // No Params
        self::ACTION_SET_IN_COMBAT_WITH_ZONE            => [null, null, null, null, null, null, 0],  // No Params
        self::ACTION_CALL_FOR_HELP                      => [null, null, null, null, null, null, 0],  // Radius, With Emote
        self::ACTION_SET_SHEATH                         => [['sheathState', 10, false], null, null, null, null, null, 0],  // Sheath (0-unarmed, 1-melee, 2-ranged)
        self::ACTION_FORCE_DESPAWN                      => [['formatTime', 10, true], ['formatTime', 11, false], null, null, null, null, 0],  // timer
        self::ACTION_SET_INVINCIBILITY_HP_LEVEL         => [null, null, null, null, null, null, 0],  // MinHpValue(+pct, -flat)
        self::ACTION_MOUNT_TO_ENTRY_OR_MODEL            => [Type::NPC, null, null, null, null, null, 0],  // Creature_template entry(param1) OR ModelId (param2) (or 0 for both to dismount)
        self::ACTION_SET_INGAME_PHASE_MASK              => [null, null, null, null, null, null, 0],  // mask
        self::ACTION_SET_DATA                           => [null, null, null, null, null, null, 0],  // Field, Data (only creature @todo)
        self::ACTION_ATTACK_STOP                        => [null, null, null, null, null, null, 0],  //
        self::ACTION_SET_VISIBILITY                     => [null, null, null, null, null, null, 0],  // on/off
        self::ACTION_SET_ACTIVE                         => [null, null, null, null, null, null, 0],  // on/off
        self::ACTION_ATTACK_START                       => [null, null, null, null, null, null, 0],  //
        self::ACTION_SUMMON_GO                          => [Type::OBJECT, ['formatTime', 10, false], null, null, null, null, 0],  // GameObjectID, DespawnTime in s
        self::ACTION_KILL_UNIT                          => [null, null, null, null, null, null, 0],  //
        self::ACTION_ACTIVATE_TAXI                      => [null, null, null, null, null, null, 0],  // TaxiID
        self::ACTION_WP_START                           => [null, null, null, Type::QUEST, ['formatTime', 10, true], ['reactState', 11, false], 0],  // run/walk, pathID, canRepeat, quest, despawntime
        self::ACTION_WP_PAUSE                           => [['formatTime', 10, true], null, null, null, null, null, 0],  // time
        self::ACTION_WP_STOP                            => [['formatTime', 10, true], Type::QUEST, null, null, null, null, 0],  // despawnTime, quest, fail?
        self::ACTION_ADD_ITEM                           => [Type::ITEM, null, null, null, null, null, 0],  // itemID, count
        self::ACTION_REMOVE_ITEM                        => [Type::ITEM, null, null, null, null, null, 0],  // itemID, count
        self::ACTION_INSTALL_AI_TEMPLATE                => [['aiTemplate', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_SET_RUN                            => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_SET_DISABLE_GRAVITY                => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_SET_SWIM                           => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_TELEPORT                           => [null, null, null, null, null, null, 0],  // mapID,
        self::ACTION_SET_COUNTER                        => [null, null, null, null, null, null, 0],  // id, value, reset (0/1)
        self::ACTION_STORE_TARGET_LIST                  => [null, null, null, null, null, null, 0],  // varID,
        self::ACTION_WP_RESUME                          => [null, null, null, null, null, null, 0],  // none
        self::ACTION_SET_ORIENTATION                    => [null, null, null, null, null, null, 0],  //
        self::ACTION_CREATE_TIMED_EVENT                 => [null, ['numRange', 10, true], null, ['numRange', -1, true], null, null, 0],  // id, InitialMin, InitialMax, RepeatMin(only if it repeats), RepeatMax(only if it repeats), chance
        self::ACTION_PLAYMOVIE                          => [null, null, null, null, null, null, 0],  // entry
        self::ACTION_MOVE_TO_POS                        => [null, null, null, null, null, null, 0],  // PointId, transport, disablePathfinding, ContactDistance
        self::ACTION_ENABLE_TEMP_GOBJ                   => [['formatTime', 10, false], null, null, null, null, null, 0],  // despawnTimer (sec)
        self::ACTION_EQUIP                              => [null, null, Type::ITEM, Type::ITEM, Type::ITEM, null, 0],  // entry, slotmask slot1, slot2, slot3   , only slots with mask set will be sent to client, bits are 1, 2, 4, leaving mask 0 is defaulted to mask 7 (send all), slots1-3 are only used if no entry is set
        self::ACTION_CLOSE_GOSSIP                       => [null, null, null, null, null, null, 0],  // none
        self::ACTION_TRIGGER_TIMED_EVENT                => [null, null, null, null, null, null, 0],  // id(>1)
        self::ACTION_REMOVE_TIMED_EVENT                 => [null, null, null, null, null, null, 0],  // id(>1)
        self::ACTION_ADD_AURA                           => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_OVERRIDE_SCRIPT_BASE_OBJECT        => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_RESET_SCRIPT_BASE_OBJECT           => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_CALL_SCRIPT_RESET                  => [null, null, null, null, null, null, 0],  // none
        self::ACTION_SET_RANGED_MOVEMENT                => [null, null, null, null, null, null, 0],  // Distance, angle
        self::ACTION_CALL_TIMED_ACTIONLIST              => [null, null, null, null, null, null, 0],  // ID (overwrites already running actionlist), stop after combat?(0/1), timer update type(0-OOC, 1-IC, 2-ALWAYS)
        self::ACTION_SET_NPC_FLAG                       => [['npcFlags', 10, false], null, null, null, null, null, 0],  // Flags
        self::ACTION_ADD_NPC_FLAG                       => [['npcFlags', 10, false], null, null, null, null, null, 0],  // Flags
        self::ACTION_REMOVE_NPC_FLAG                    => [['npcFlags', 10, false], null, null, null, null, null, 0],  // Flags
        self::ACTION_SIMPLE_TALK                        => [null, null, null, null, null, null, 0],  // groupID, can be used to make players say groupID, Text_over event is not triggered, whisper can not be used (Target units will say the text)
        self::ACTION_SELF_CAST                          => [Type::SPELL, ['castFlags', -1, false], null, null, null, null, 0],  // spellID, castFlags
        self::ACTION_CROSS_CAST                         => [Type::SPELL, ['castFlags', -1, false], null, null, null, null, 0],  // spellID, castFlags, CasterTargetType, CasterTarget param1, CasterTarget param2, CasterTarget param3, ( + the origonal target fields as Destination target),   CasterTargets will cast spellID on all Targets (use with caution if targeting multiple * multiple units)
        self::ACTION_CALL_RANDOM_TIMED_ACTIONLIST       => [null, null, null, null, null, null, 0],  // script9 ids 1-9
        self::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST => [null, null, null, null, null, null, 0],  // script9 id min, max
        self::ACTION_RANDOM_MOVE                        => [null, null, null, null, null, null, 0],  // maxDist
        self::ACTION_SET_UNIT_FIELD_BYTES_1             => [['unitFieldBytes1', 10, false], null, null, null, null, null, 0],  // bytes, target
        self::ACTION_REMOVE_UNIT_FIELD_BYTES_1          => [['unitFieldBytes1', 10, false], null, null, null, null, null, 0],  // bytes, target
        self::ACTION_INTERRUPT_SPELL                    => [null, Type::SPELL, null, null, null, null, 0],  //
        self::ACTION_SEND_GO_CUSTOM_ANIM                => [['dynFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_SET_DYNAMIC_FLAG                   => [['dynFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_ADD_DYNAMIC_FLAG                   => [['dynFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_REMOVE_DYNAMIC_FLAG                => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_JUMP_TO_POS                        => [null, null, null, null, null, null, 0],  // speedXY, speedZ, targetX, targetY, targetZ
        self::ACTION_SEND_GOSSIP_MENU                   => [null, null, null, null, null, null, 0],  // menuId, optionId
        self::ACTION_GO_SET_LOOT_STATE                  => [['lootState', 10, false], null, null, null, null, null, 0],  // state
        self::ACTION_SEND_TARGET_TO_TARGET              => [null, null, null, null, null, null, 0],  // id
        self::ACTION_SET_HOME_POS                       => [null, null, null, null, null, null, 0],  // none
        self::ACTION_SET_HEALTH_REGEN                   => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_SET_ROOT                           => [null, null, null, null, null, null, 0],  // off/on
        self::ACTION_SET_GO_FLAG                        => [['goFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_ADD_GO_FLAG                        => [['goFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_REMOVE_GO_FLAG                     => [['goFlags', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_SUMMON_CREATURE_GROUP              => [null, null, null, null, null, null, 0],  // Group, attackInvoker
        self::ACTION_SET_POWER                          => [['powerType', 10, false], null, null, null, null, null, 0],  // PowerType, newPower
        self::ACTION_ADD_POWER                          => [['powerType', 10, false], null, null, null, null, null, 0],  // PowerType, newPower
        self::ACTION_REMOVE_POWER                       => [['powerType', 10, false], null, null, null, null, null, 0],  // PowerType, newPower
        self::ACTION_GAME_EVENT_STOP                    => [Type::WORLDEVENT, null, null, null, null, null, 0],  // GameEventId
        self::ACTION_GAME_EVENT_START                   => [Type::WORLDEVENT, null, null, null, null, null, 0],  // GameEventId
        self::ACTION_START_CLOSEST_WAYPOINT             => [null, null, null, null, null, null, 0],  // wp1, wp2, wp3, wp4, wp5, wp6, wp7
        self::ACTION_MOVE_OFFSET                        => [null, null, null, null, null, null, 0],  //
        self::ACTION_RANDOM_SOUND                       => [Type::SOUND, Type::SOUND, Type::SOUND, Type::SOUND, null, null, 0],  // soundId1, soundId2, soundId3, soundId4, soundId5, onlySelf
        self::ACTION_SET_CORPSE_DELAY                   => [['formatTime', 10, false], null, null, null, null, null, 0],  // timer
        self::ACTION_DISABLE_EVADE                      => [null, null, null, null, null, null, 0],  // 0/1 (1 = disabled, 0 = enabled)
        self::ACTION_GO_SET_GO_STATE                    => [null, null, null, null, null, null, 0],  // state
        self::ACTION_SET_CAN_FLY                        => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_REMOVE_AURAS_BY_TYPE               => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_SET_SIGHT_DIST                     => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_FLEE                               => [['formatTime', 10, false], null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_ADD_THREAT                         => [null, null, null, null, null, null, 0],  // +threat, -threat
        self::ACTION_LOAD_EQUIPMENT                     => [null, null, null, null, null, null, 0],  // id
        self::ACTION_TRIGGER_RANDOM_TIMED_EVENT         => [['numRange', 10, false], null, null, null, null, null, 0],  // id min range, id max range
        self::ACTION_REMOVE_ALL_GAMEOBJECTS             => [null, null, null, null, null, null, 1],  // UNUSED, DO NOT REUSE
        self::ACTION_PAUSE_MOVEMENT                     => [null, ['formatTime', 10, true], null, null, null, null, 0],  // MovementSlot (default = 0, active = 1, controlled = 2), PauseTime (ms), Force
        self::ACTION_PLAY_ANIMKIT                       => [null, null, null, null, null, null, 2],  // don't use on 3.3.5a
        self::ACTION_SCENE_PLAY                         => [null, null, null, null, null, null, 2],  // don't use on 3.3.5a
        self::ACTION_SCENE_CANCEL                       => [null, null, null, null, null, null, 2],  // don't use on 3.3.5a
        self::ACTION_SPAWN_SPAWNGROUP                   => [null, null, null, ['spawnFlags', 11, false], null, null, 0],  // Group ID, min secs, max secs, spawnflags
        self::ACTION_DESPAWN_SPAWNGROUP                 => [null, null, null, ['spawnFlags', 11, false], null, null, 0],  // Group ID, min secs, max secs, spawnflags
        self::ACTION_RESPAWN_BY_SPAWNID                 => [null, null, null, null, null, null, 0],  // spawnType, spawnId
        self::ACTION_INVOKER_CAST                       => [Type::SPELL, ['castFlags', -1, false], null, null, null, null, 0],  // spellID, castFlags
        self::ACTION_PLAY_CINEMATIC                     => [null, null, null, null, null, null, 0],  // entry, cinematic
        self::ACTION_SET_MOVEMENT_SPEED                 => [null, null, null, null, null, null, 0],  // movementType, speedInteger, speedFraction
        self::ACTION_PLAY_SPELL_VISUAL_KIT              => [null, null, null, null, null, null, 2],  // spellVisualKitId (RESERVED, PENDING CHERRYPICK)
        self::ACTION_OVERRIDE_LIGHT                     => [Type::ZONE, null, null, ['formatTime', -1, true], null, null, 0],  // zoneId, overrideLightID, transitionMilliseconds
        self::ACTION_OVERRIDE_WEATHER                   => [Type::ZONE, ['weatherState', 10, false], null, null, null, null, 0],  // zoneId, weatherId, intensity
        self::ACTION_SET_AI_ANIM_KIT                    => [null, null, null, null, null, null, 2],  // DEPRECATED, DO REUSE (it was never used in any branch, treat as free action id)
        self::ACTION_SET_HOVER                          => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_SET_HEALTH_PCT                     => [null, null, null, null, null, null, 0],  // percent
        self::ACTION_CREATE_CONVERSATION                => [null, null, null, null, null, null, 2],  // don't use on 3.3.5a
        self::ACTION_SET_IMMUNE_PC                      => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_SET_IMMUNE_NPC                     => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_SET_UNINTERACTIBLE                 => [null, null, null, null, null, null, 0],  // 0/1
        self::ACTION_ACTIVATE_GAMEOBJECT                => [null, null, null, null, null, null, 0],  // GameObjectActions
        self::ACTION_ADD_TO_STORED_TARGET_LIST          => [null, null, null, null, null, null, 0],  // varID
        self::ACTION_BECOME_PERSONAL_CLONE_FOR_PLAYER   => [null, null, null, null, null, null, 2],  // don't use on 3.3.5a
        self::ACTION_TRIGGER_GAME_EVENT                 => [null, null, null, null, null, null, 2],  // eventId, useSaiTargetAsGameEventSource (RESERVED, PENDING CHERRYPICK)
        self::ACTION_DO_ACTION                          => [null, null, null, null, null, null, 2]   // actionId (RESERVED, PENDING CHERRYPICK)
    );

    private array $jsGlobals = [];
    private ?array $summons  = null;

    public function __construct(
        private int $id,
        public readonly int $type,
        private array $param,
        private SmartAI &$smartAI)
    {
        // init additional parameters
        Util::checkNumeric($this->param, NUM_CAST_INT);
        $this->param = array_pad($this->param, 15, '');
    }

    public function process() : array
    {
        $body   =
        $footer = '';

        $actionTT = Lang::smartAI('actionTT', array_merge([$this->type], $this->param));

        for ($i = 0; $i < 5; $i++)
        {
            $aParams = $this->data[$this->type];

            if (is_array($aParams[$i]))
            {
                [$fn, $idx, $extraParam] = $aParams[$i];

                if ($idx < 0)
                    $footer = $this->{$fn}($this->param[$i], $this->param[$i + 1], $extraParam);
                else
                    $this->param[$idx] = $this->{$fn}($this->param[$i], $this->param[$i + 1], $extraParam);
            }
            else if (is_int($aParams[$i]) && $this->param[$i])
                $this->jsGlobals[$aParams[$i]][$this->param[$i]] = $this->param[$i];
        }

        // non-generic cases
        switch ($this->type)
        {
            case self::ACTION_FLEE_FOR_ASSIST:              // 25 -> none
            case self::ACTION_CALL_FOR_HELP:                // 39 -> self
                if ($this->param[0])
                    $footer = $this->param;
                break;
            case self::ACTION_INTERRUPT_SPELL:              // 92 -> self
                if (!$this->param[1])
                    $footer = $this->param;
                break;
            case self::ACTION_UPDATE_TEMPLATE:              // 36
            case self::ACTION_SET_CORPSE_DELAY:             // 116
                if ($this->param[1])
                    $footer = $this->param;
                break;
            case self::ACTION_PAUSE_MOVEMENT:               // 127 -> any target [ye, not gonna resolve this nonsense]
            case self::ACTION_REMOVEAURASFROMSPELL:         // 28 -> any target
            case self::ACTION_SOUND:                        // 4 -> self [param3 set in DB but not used in core?]
            case self::ACTION_SUMMON_GO:                    // 50 -> self, world coords
            case self::ACTION_MOVE_TO_POS:                  // 69 -> any target
                if ($this->param[2])
                    $footer = $this->param;
                break;
            case self::ACTION_WP_START:                     // 53 -> any .. why tho?
                if ($this->param[2] || $this->param[5])
                    $footer = $this->param;
                break;
            case self::ACTION_PLAY_EMOTE:                   // 5 -> any target
            case self::ACTION_SET_EMOTE_STATE:              // 17 -> any target
                if ($this->param[0])
                {
                    $this->param[0] *= -1;                  // handle creature emote
                    $this->jsGlobals[Type::EMOTE][$this->param[0]] = $this->param[0];
                }
                break;
            case self::ACTION_RANDOM_EMOTE:                 // 10 -> any target
                $buff = [];
                for ($i = 0; $i < 6; $i++)
                {
                    if (empty($this->param[$i]))
                        continue;

                    $this->param[$i] *= -1;                 // handle creature emote
                    $buff[] = '[emote='.$this->param[$i].']';
                    $this->jsGlobals[Type::EMOTE][$this->param[$i]] = $this->param[$i];
                }
                $this->param[10] = Lang::concat($buff, Lang::CONCAT_OR);
                break;
            case self::ACTION_SET_FACTION:                  // 2 -> any target
                if ($this->param[0])
                {
                    $this->param[10] = DB::Aowow()->selectCell('SELECT `factionId` FROM ?_factiontemplate WHERE `id` = ?d', $this->param[0]);
                    $this->jsGlobals[Type::FACTION][$this->param[10]] = $this->param[10];
                }
                break;
            case self::ACTION_MORPH_TO_ENTRY_OR_MODEL:      // 3 -> self
            case self::ACTION_MOUNT_TO_ENTRY_OR_MODEL:      // 43 -> self
                if (!$this->param[0] && !$this->param[1])
                    $this->param[10] = 1;
                break;
            case self::ACTION_THREAT_SINGLE_PCT:            // 13 -> victim
            case self::ACTION_THREAT_ALL_PCT:               // 14 -> self
            case self::ACTION_ADD_THREAT:                   // 123 -> any target
                $this->param[10] = $this->param[0] - $this->param[1];
                break;
            case self::ACTION_FOLLOW:                       // 29 -> any target
                if ($this->param[1])
                {
                    $this->param[10] = Util::O2Deg($this->param[1])[0];
                    $footer = $this->param;
                }
                if ($this->param[3])
                {
                    if ($this->param[4])
                    {
                        $this->jsGlobals[Type::QUEST][$this->param[3]] = $this->param[3];
                        $this->param[11] = 1;
                    }
                    else
                    {
                        $this->jsGlobals[Type::NPC][$this->param[3]] = $this->param[3];
                        $this->param[12] = 1;
                    }
                }
                break;
            case self::ACTION_RANDOM_PHASE:                 // 30 -> self
                $buff = [];
                for ($i = 0; $i < 7; $i++)
                    if ($_ = $this->param[$i])
                        $buff[] = $_;

                $this->param[10] = Lang::concat($buff);
                break;
            case self::ACTION_ACTIVATE_TAXI:                // 52 -> invoker
                $nodes = DB::Aowow()->selectRow(
                   'SELECT tn1.`name_loc0` AS "start_loc0", tn1.name_loc?d AS start_loc?d, tn2.`name_loc0` AS "end_loc0", tn2.name_loc?d AS end_loc?d
                    FROM   ?_taxipath tp
                    JOIN   ?_taxinodes tn1 ON tp.`startNodeId` = tn1.`id`
                    JOIN   ?_taxinodes tn2 ON tp.`endNodeId` = tn2.`id`
                    WHERE  tp.`id` = ?d',
                    Lang::getLocale()->value, Lang::getLocale()->value, Lang::getLocale()->value, Lang::getLocale()->value, $this->param[0]
                );
                $this->param[10] = Util::localizedString($nodes, 'start');
                $this->param[11] = Util::localizedString($nodes, 'end');
                break;
            case self::ACTION_SET_INGAME_PHASE_MASK:        // 44 -> any target
                if ($this->param[0])
                    $this->param[10] = Lang::concat(Util::mask2bits($this->param[0]));
                break;
            case self::ACTION_TELEPORT:                     // 62 -> invoker
                [$x, $y, $z, $o] = $this->smartAI->getTarget()->getWorldPos();
                // try from areatrigger setup data
                if ($this->smartAI->teleportTargetArea)
                    $this->param[10] = $this->smartAI->teleportTargetArea;
                // try calc from SmartTarget data
                else if ($pos = WorldPosition::toZonePos($this->param[0], $x, $y))
                {
                    $this->param[10] = $pos[0]['areaId'];
                    $this->param[11] = str_pad($pos[0]['posX'] * 10, 3, '0', STR_PAD_LEFT).str_pad($pos[0]['posY'] * 10, 3, '0', STR_PAD_LEFT);
                }
                // maybe the mapId is an instane map
                else if ($areaId = DB::Aowow()->selectCell('SELECT `id` FROM ?_zones WHERE `mapId` = ?d', $this->param[0]))
                    $this->param[10] = $areaId;
                // ...whelp
                else
                    trigger_error('SmartAction::process - could not resolve teleport target: map:'.$this->param[0].' x:'.$x.' y:'.$y);

                if ($this->param[10])
                    $this->jsGlobals[Type::ZONE][$this->param[10]] = $this->param[10];
                break;
            case self::ACTION_SET_ORIENTATION:              // 66 -> any target
                if ($this->smartAI->getTarget()->type == SmartTarget::TARGET_POSITION)
                    $this->param[10] = Util::O2Deg($this->smartAI->getTarget()->getWorldPos()[3])[1];
                else if ($this->smartAI->getTarget()->type != SmartTarget::TARGET_SELF)
                    $this->param[10] = '#target#';
                break;
            case self::ACTION_EQUIP:                        // 71 -> any
                $equip = [];

                if ($this->param[0])
                {
                    $slots = $this->param[1] ? Util::mask2bits($this->param[1], 1) : [1, 2, 3];
                    $items = DB::World()->selectRow('SELECT `ItemID1`, `ItemID2`, `ItemID3` FROM creature_equip_template WHERE `CreatureID` = ?d AND `ID` = ?d', $this->smartAI->getEntry(), $this->param[0]);

                    foreach ($slots as $s)
                        if ($_ = $items['ItemID'.$s])
                            $equip[] = $_;
                }
                else if ($this->param[2] || $this->param[3] || $this->param[4])
                {
                    if ($_ = $this->param[2])
                        $equip[] = $_;
                    if ($_ = $this->param[3])
                        $equip[] = $_;
                    if ($_ = $this->param[4])
                        $equip[] = $_;
                }

                if ($equip)
                {
                    $this->param[10] = Lang::concat($equip, callback: fn($x) => '[item='.$x.']');
                    $footer = true;

                    foreach ($equip as $_)
                        $this->jsGlobals[Type::ITEM][$_] = $_;
                }
                break;
            case self::ACTION_LOAD_EQUIPMENT:               // 124 -> any target
                $buff = [];
                if ($this->param[0])
                {
                    $items = DB::World()->selectRow('SELECT `ItemID1`, `ItemID2`, `ItemID3` FROM creature_equip_template WHERE `CreatureID` = ?d AND `ID` = ?d', $this->smartAI->getEntry(), $this->param[0]);
                    foreach ($items as $i)
                    {
                        if (!$i)
                            continue;

                        $this->jsGlobals[Type::ITEM][$i] = $i;
                        $buff[] = '[item='.$i.']';
                    }
                }
                else if (!$this->param[1])
                    trigger_error('SmartAI::action - action #124 (SmartAction::ACTION_LOAD_EQIPMENT) is malformed');

                $this->param[10] = Lang::concat($buff);
                $footer = true;
                break;
            case self::ACTION_CALL_TIMED_ACTIONLIST:        // 80 -> any target
                $this->param[10] = match ($this->param[1])
                {
                    0, 1, 2 => Lang::smartAI('saiUpdate', $this->param[1]),
                    default => Lang::smartAI('saiUpdateUNK', [$this->param[1]])
                };

                $tal = new SmartAI(SmartAI::SRC_TYPE_ACTIONLIST, $this->param[0], ['baseEntry' => $this->smartAI->getEntry()]);
                $tal->prepare();

                $this->smartAI->css .= $tal->css;

                Util::mergeJsGlobals($this->jsGlobals, $tal->getJSGlobals());

                foreach ($tal->getTabs() as $guid => $tt)
                    $this->smartAI->addTab($guid, $tt);

                break;
            case self::ACTION_CALL_KILLEDMONSTER:           // 33: Note: If target is SMART_TARGET_NONE (0) or SMART_TARGET_SELF (1), the kill is credited to all players eligible for loot from this creature.
                if ($this->smartAI->getTarget()->type == SmartTarget::TARGET_SELF || $this->smartAI->getTarget()->type == SmartTarget::TARGET_NONE)
                    $this->param[10] = (new SmartTarget($this->id, SmartTarget::TARGET_LOOT_RECIPIENTS, [], [], $this->smartAI))->process();
                break;
            case self::ACTION_CROSS_CAST:                   // 86 -> entity by TargetingBlock(param3, param4, param5, param6) cross cast spell <param1> at any target
                $this->param[10] = (new SmartTarget($this->id, $this->param[2], [$this->param[3], $this->param[4], $this->param[5]], [], $this->smartAI))->process();
                break;
            case self::ACTION_CALL_RANDOM_TIMED_ACTIONLIST: // 87 -> self
                $talBuff = [];
                for ($i = 0; $i < 6; $i++)
                {
                    if (!$this->param[$i])
                        continue;

                    $talBuff[] = sprintf(self::TAL_TAB_ANCHOR, $this->param[$i]);

                    $tal = new SmartAI(SmartAI::SRC_TYPE_ACTIONLIST, $this->param[$i], ['baseEntry' => $this->smartAI->getEntry()]);
                    $tal->prepare();

                    $this->smartAI->css .= $tal->css;

                    Util::mergeJsGlobals($this->jsGlobals, $tal->getJSGlobals());

                    foreach ($tal->getTabs() as $guid => $tt)
                        $this->smartAI->addTab($guid, $tt);
                }
                $this->param[10] = Lang::concat($talBuff, Lang::CONCAT_OR);
                break;
            case self::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST:// 88 -> self
                $talBuff = [];
                for ($i = $this->param[0]; $i <= $this->param[1]; $i++)
                {
                    $talBuff[] = sprintf(self::TAL_TAB_ANCHOR, $i);

                    $tal = new SmartAI(SmartAI::SRC_TYPE_ACTIONLIST, $i, ['baseEntry' => $this->smartAI->getEntry()]);
                    $tal->prepare();

                    $this->smartAI->css .= $tal->css;

                    Util::mergeJsGlobals($this->jsGlobals, $tal->getJSGlobals());

                    foreach ($tal->getTabs() as $guid => $tt)
                        $this->smartAI->addTab($guid, $tt);
                }
                $this->param[10] = Lang::concat($talBuff, Lang::CONCAT_OR);
                break;
            case self::ACTION_SET_HOME_POS:                 // 101 -> self
                if ($this->smartAI->getTarget()?->type == Smarttarget::TARGET_SELF)
                    $this->param[10] = 1;
                // do not break;
            case self::ACTION_JUMP_TO_POS:                  // 97 -> self
            case self::ACTION_MOVE_OFFSET:                  // 114 -> self
                array_splice($this->param, 11, replacement: $this->smartAI->getTarget()->getWorldPos());
                break;
            case self::ACTION_SUMMON_CREATURE_GROUP:        // 107 -> untargeted
                if ($this->summons === null)
                    $this->summons = DB::World()->selectCol('SELECT `groupId` AS ARRAY_KEY, `entry` AS ARRAY_KEY2, COUNT(*) AS "n" FROM creature_summon_groups WHERE `summonerId` = ?d GROUP BY `groupId`, `entry`', $this->smartAI->getEntry());

                $buff = [];
                if (!empty($this->summons[$this->param[0]]))
                {
                    foreach ($this->summons[$this->param[0]] as $id => $n)
                    {
                        $this->jsGlobals[Type::NPC][$id] = $id;
                        $buff[] = $n.'x [npc='.$id.']';
                    }
                }

                if ($buff)
                    $this->param[10] = Lang::concat($buff);
                break;
            case self::ACTION_START_CLOSEST_WAYPOINT:       // 113 -> any target
                $this->param[10] = Lang::concat(array_filter($this->param), Lang::CONCAT_OR, fn($x) => '#[b]'.$x.'[/b]');
                break;
            case self::ACTION_RANDOM_SOUND:                 // 115 -> self
                for ($i = 0; $i < 4; $i++)
                {
                    if ($x = $this->param[$i])
                    {
                        $this->jsGlobals[Type::SOUND][$x] = $x;
                        $this->param[10] .= '[sound='.$x.']';
                    }
                }

                if ($this->param[5])
                    $footer = true;
                break;
            case self::ACTION_GO_SET_GO_STATE:              // 118 -> ???
                $this->param[10] = match ($this->param[0])
                {
                    0, 1, 2 => Lang::smartAI('GOStates', $this->param[0]),
                    default => Lang::smartAI('GOStateUNK', [$this->param[0]])
                };
                break;
            case self::ACTION_REMOVE_AURAS_BY_TYPE:         // 120 -> any target
                $this->param[10] = Lang::spell('auras', $this->param[0]);
                break;
            case self::ACTION_SPAWN_SPAWNGROUP:             // 131
            case self::ACTION_DESPAWN_SPAWNGROUP:           // 132
                $this->param[10] = Util::jsEscape(DB::World()->selectCell('SELECT `GroupName` FROM spawn_group_template WHERE `groupId` = ?d', $this->param[0]));
                $entities = DB::World()->select('SELECT `spawnType` AS "0", `spawnId` AS "1" FROM spawn_group WHERE `groupId` = ?d',  $this->param[0]);

                $n = 5;
                $buff = [];
                foreach ($entities as [$spawnType, $guid])
                {
                    $type = Type::NPC;
                    if ($spawnType == 1)
                        $type == Type::OBJECT;

                    if ($_ = $this->resolveGuid($type, $guid))
                    {
                        $this->jsGlobals[$type][$_] = $_;
                        $buff[] = '['.Type::getFileString($type).'='.$_.'][small class=q0] (GUID: '.$guid.')[/small]';
                    }
                    else
                        $buff[] = Lang::smartAI('entityUNK').'[small class=q0] (GUID: '.$guid.')[/small]';

                    if (!--$n)
                        break;
                }

                if (count($entities) > 5)
                    $buff[] = '+'.(count($entities) - 5).'…';

                $this->param[12] = '[ul][li]'.implode('[/li][li]', $buff).'[/li][/ul]';

                // i'd like this stored in $data but numRange can only handle msec
                if ($time = $this->numRange($this->param[1] * 1000, $this->param[2] * 1000, true))
                    $footer = [$time];
                break;
            case self::ACTION_RESPAWN_BY_SPAWNID:           // 133
                $type = Type::NPC;
                if ($this->param[0] == 1)
                    $type == Type::OBJECT;

                if ($_ = $this->resolveGuid($type, $this->param[1]))
                {
                    $this->param[10] = '['.Type::getFileString($type).'='.$_.']';
                    $this->jsGlobals[$type][$_] = $_;
                }
                else
                    $this->param[10] = Lang::smartAI('entityUNK');
                break;
            case self::ACTION_SET_MOVEMENT_SPEED:           // 136
                $this->param[10] = $this->param[1] + $this->param[2] / pow(10, floor(log10($this->param[2] ?: 1.0) + 1));  // i know string concatenation is a thing. don't @ me!
                break;
            case self::ACTION_TALK:                         // 1 -> any target
                $talkTarget = $this->param[2];
            case self::ACTION_SIMPLE_TALK:                  // 84 -> any target
                $playerSrc = false;
                if ($npcId = $this->smartAI->getTarget()->getTalkSource($playerSrc))
                {
                    if ($quotes = $this->smartAI->getQuote($npcId, $this->param[0], $npcSrc))
                    {
                        foreach ($quotes as ['text' => $text])
                        {
                            $talkTarget = ($talkTarget ?? true) ? Lang::game('target') : $npcSrc;
                            $this->param[10] .= sprintf($text, $playerSrc ? Lang::main('thePlayer') : $npcSrc, $talkTarget);
                        }
                    }
                }
                else
                    trigger_error('SmartAI::action - could not determine talk source for action #'.$this->type);
                break;
        }

        $this->smartAI->addJsGlobals($this->jsGlobals);

        $body = Lang::smartAI('actions', $this->type, 0, $this->param) ?? Lang::smartAI('actionUNK', [$this->type]);
        if ($footer)
            $footer = Lang::smartAI('actions', $this->type, 1, (array)$footer);

        // resolve conditionals
        $i = 0;
        while (strstr($body, ')?') && $i++ < 3)
            $body   = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):(([^;]*);*);/i', fn($m) => $m[1] ? $m[2] : $m[3], $body);

        $i = 0;
        while (strstr($footer, ')?') && $i++ < 3)
            $footer = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):(([^;]*);*);/i', fn($m) => $m[1] ? $m[2] : $m[3], $footer);

        // wrap body in tooltip
        return [sprintf(self::ACTION_CELL_TPL, $actionTT, $body), $footer];
    }
}

?>
