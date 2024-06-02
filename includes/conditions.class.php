<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// TrinityCore - Condition System

class Conditions
{
    // enum TypeID
    private const TYPEID_OBJECT        = 0;
    private const TYPEID_ITEM          = 1;
    private const TYPEID_CONTAINER     = 2;
    private const TYPEID_UNIT          = 3;
    private const TYPEID_PLAYER        = 4;
    private const TYPEID_GAMEOBJECT    = 5;
    private const TYPEID_DYNAMICOBJECT = 6;
    private const TYPEID_CORPSE        = 7;

    public const OP_E    = 0;                               // ==
    public const OP_GT   = 1;                               // >
    public const OP_LT   = 2;                               // <
    public const OP_GT_E = 3;                               // >=
    public const OP_LT_E = 4;                               // <=
                                                            // Group,      Entry,     Id
    public const SRC_NONE                        = 0;       // null,       null,      null - use when adding external conditions
    public const SRC_CREATURE_LOOT_TEMPLATE      = 1;       // tplEntry,   itemId,    null
    public const SRC_DISENCHANT_LOOT_TEMPLATE    = 2;       // tplEntry,   itemId,    null
    public const SRC_FISHING_LOOT_TEMPLATE       = 3;       // tplEntry,   itemId,    null
    public const SRC_GAMEOBJECT_LOOT_TEMPLATE    = 4;       // tplEntry,   itemId,    null
    public const SRC_ITEM_LOOT_TEMPLATE          = 5;       // tplEntry,   itemId,    null
    public const SRC_MAIL_LOOT_TEMPLATE          = 6;       // tplEntry,   itemId,    null
    public const SRC_MILLING_LOOT_TEMPLATE       = 7;       // tplEntry,   itemId,    null
    public const SRC_PICKPOCKETING_LOOT_TEMPLATE = 8;       // tplEntry,   itemId,    null
    public const SRC_PROSPECTING_LOOT_TEMPLATE   = 9;       // tplEntry,   itemId,    null
    public const SRC_REFERENCE_LOOT_TEMPLATE     = 10;      // tplEntry,   itemId,    null
    public const SRC_SKINNING_LOOT_TEMPLATE      = 11;      // tplEntry,   itemId,    null
    public const SRC_SPELL_LOOT_TEMPLATE         = 12;      // tplEntry,   itemId,    null
    public const SRC_SPELL_IMPLICIT_TARGET       = 13;      // effectMask, spellId,   null
    public const SRC_GOSSIP_MENU                 = 14;      // menuId,     textId,    null
    public const SRC_GOSSIP_MENU_OPTION          = 15;      // menuId,     optionId,  null
    public const SRC_CREATURE_TEMPLATE_VEHICLE   = 16;      // npcId,      null,      null
    public const SRC_SPELL                       = 17;      // null,       spellId,   null
    public const SRC_SPELL_CLICK_EVENT           = 18;      // npcId,      spellId,   null
    public const SRC_QUEST_AVAILABLE             = 19;      // null,       questId,   null
    public const SRC_QUEST_SHOW_MARK             = 20;      // null,       questId,   null - ⚠️ unused as of 01.05.2024
    public const SRC_VEHICLE_SPELL               = 21;      // npcId,      spellId,   null
    public const SRC_SMART_EVENT                 = 22;      // id,         entryGuid, srcType
    public const SRC_NPC_VENDOR                  = 23;      // npcId,      itemId,    null
    public const SRC_SPELL_PROC                  = 24;      // null,       spellId,   null
//  public const SRC_SPELL_TERRAIN_SWAP          = 25;      //                             - ❌ reserved for TC master
//  public const SRC_SPELL_PHASE                 = 26;      //                             - ❌ reserved for TC master
//  public const SRC_SPELL_GRAVEYARD             = 27;      //                             - ❌ reserved for TC master
//  public const SRC_SPELL_AREATRIGGER           = 28;      //                             - ❌ reserved for TC master
//  public const SRC_SPELL_CONVERSATION_LINE     = 29;      //                             - ❌ reserved for TC master
    public const SRC_AREATRIGGER_CLIENT          = 30;      // null,       atId,      null
//  public const SRC_SPELL_TRAINER_SPELL         = 31;      //                             - ❌ reserved for TC master
//  public const SRC_SPELL_OBJECT_VISIBILITY     = 32;      //                             - ❌ reserved for TC master
//  public const SRC_SPELL_SPAWN_GROUP           = 33;      //                             - ❌ reserved for TC master

    public const NONE                     = 0;              // always true:             NULL,           NULL,           NULL
    public const AURA                     = 1;              // aura is applied:         spellId,        effIdx,         NULL
    public const ITEM                     = 2;              // owns item:               itemId,         count,          includeBank?
    public const ITEM_EQUIPPED            = 3;              // has item equipped:       itemId,         NULL,           NULL
    public const ZONEID                   = 4;              // is in zone:              areaId,         NULL,           NULL
    public const REPUTATION_RANK          = 5;              // reputation status:       factionId,      rankMask,       NULL
    public const TEAM                     = 6;              // is on team:              teamId,         NULL,           NULL
    public const SKILL                    = 7;              // has skill:               skillId,        value,          NULL
    public const QUESTREWARDED            = 8;              // has finished quest:      questId,        NULL,           NULL
    public const QUESTTAKEN               = 9;              // has accepted quest:      questId,        NULL,           NULL
    public const DRUNKENSTATE             = 10;             // has drunken status:      stateId,        NULL,           NULL
    public const WORLD_STATE              = 11;             // world var == value:      worldStateId,   value,          NULL
    public const ACTIVE_EVENT             = 12;             // world event is active:   eventId,        NULL,           NULL
    public const INSTANCE_INFO            = 13;             // instance var == data:    entry           data,           type
    public const QUEST_NONE               = 14;             // never seen quest:        questId,        NULL,           NULL
    public const CHR_CLASS                = 15;             // belongs to classes:      classMask,      NULL,           NULL
    public const CHR_RACE                 = 16;             // belongs to races:        raceMask,       NULL,           NULL
    public const ACHIEVEMENT              = 17;             // obtained achievement:    achievementId,  NULL,           NULL
    public const TITLE                    = 18;             // obtained title:          titleId,        NULL,           NULL
    public const SPAWNMASK                = 19;             //                          spawnMask,      NULL,           NULL
    public const GENDER                   = 20;             // has gender:              genderId,       NULL,           NULL
    public const UNIT_STATE               = 21;             // unit has state:          unitState,      NULL,           NULL
    public const MAPID                    = 22;             // is on map:               mapId,          NULL,           NULL
    public const AREAID                   = 23;             // is in area:              areaId,         NULL,           NULL
    public const CREATURE_TYPE            = 24;             // creature is of type:     creaturetypeId, NULL,           NULL
    public const SPELL                    = 25;             // knows spell:             spellId,        NULL,           NULL
    public const PHASEMASK                = 26;             // is in phase:             phaseMask,      NULL,           NULL
    public const LEVEL                    = 27;             // player level is..:       level,          comparator,     NULL
    public const QUEST_COMPLETE           = 28;             // has completed quest:     questId,        NULL,           NULL
    public const NEAR_CREATURE            = 29;             // is near creature:        creatureId,     dist,           includeCorpse?
    public const NEAR_GAMEOBJECT          = 30;             // is near gameObject:      gameObjectId,   dist,           NULL
    public const OBJECT_ENTRY_GUID        = 31;             // target is ???:           objectType,     id,             guid
    public const TYPE_MASK                = 32;             // target matches type:     typeMask,       NULL,           NULL
    public const RELATION_TO              = 33;             //                          Cond.Target,    relation,       NULL
    public const REACTION_TO              = 34;             //                          Cond.Target,    rankMask,       NULL
    public const DISTANCE_TO              = 35;             // distance to target       Cond.Target,    dist,           comparator
    public const ALIVE                    = 36;             // target is alive:         NULL,           NULL,           NULL
    public const HP_VAL                   = 37;             // targets absolute health: amount,         comparator,     NULL
    public const HP_PCT                   = 38;             // targets relative health: amount,         comparator,     NULL
    public const REALM_ACHIEVEMENT        = 39;             // realmfirst was achieved: achievementId,  NULL,           NULL
    public const IN_WATER                 = 40;             // unit is swimming:        NULL,           NULL,           NULL
//  public const TERRAIN_SWAP             = 41;             // ❌ reserved for TC master
    public const STAND_STATE              = 42;             //                          stateType,      state,          NULL
    public const DAILY_QUEST_DONE         = 43;             // repeatable quest done:   questId,        NULL,           NULL
    public const CHARMED                  = 44;             // unit is charmed:         NULL,           NULL,           NULL
    public const PET_TYPE                 = 45;             // player has pet of type:  petType,        NULL,           NULL
    public const TAXI                     = 46;             // player is on taxi:       NULL,           NULL,           NULL
    public const QUESTSTATE               = 47;             //                          questId,        stateMask,      NULL
    public const QUEST_OBJECTIVE_PROGRESS = 48;             //                          questId,        objectiveIdx,   count
    public const DIFFICULTY_ID            = 49;             // map has difficulty id:   difficulty,     NULL,           NULL
    public const GAMEMASTER               = 50;             // player is GM:            canBeGM?,       NULL,           NULL
//  public const OBJECT_ENTRY_GUID_MASTER = 51;             // ❌ reserved for TC master
//  public const TYPE_MASK_MASTER         = 52;             // ❌ reserved for TC master
//  public const BATTLE_PET_COUNT         = 53;             // ❌ reserved for TC master
//  public const SCENARIO_STEP            = 54;             // ❌ reserved for TC master
//  public const SCENE_IN_PROGRESS        = 55;             // ❌ reserved for TC master
//  public const PLAYER_CONDITION         = 56;             // ❌ reserved for TC master

    private const IDX_SRC_GROUP = 0;
    private const IDX_SRC_ENTRY = 1;
    private const IDX_SRC_ID    = 2;
    private const IDX_SRC_FN    = 3;

    private static $source = array(           // [Group, Entry, Id]
        self::SRC_NONE                        => [null,         null,        null, null],
        self::SRC_CREATURE_LOOT_TEMPLATE      => [Type::NPC,    Type::ITEM,  null, 'lootIdToNpc'],
        self::SRC_DISENCHANT_LOOT_TEMPLATE    => [Type::ITEM,   Type::ITEM,  null, 'disenchantIdToItem'],
        self::SRC_FISHING_LOOT_TEMPLATE       => [Type::ZONE,   Type::ITEM,  null, null],
        self::SRC_GAMEOBJECT_LOOT_TEMPLATE    => [Type::OBJECT, Type::ITEM,  null, 'lootIdToGObject'],
        self::SRC_ITEM_LOOT_TEMPLATE          => [Type::ITEM,   Type::ITEM,  null, null],
        self::SRC_MAIL_LOOT_TEMPLATE          => [Type::QUEST,  Type::ITEM,  null, 'RewardTemplateToQuest'],
        self::SRC_MILLING_LOOT_TEMPLATE       => [Type::ITEM,   Type::ITEM,  null, null],
        self::SRC_PICKPOCKETING_LOOT_TEMPLATE => [Type::NPC,    Type::ITEM,  null, 'PickpocketLootToNpc'],
        self::SRC_PROSPECTING_LOOT_TEMPLATE   => [Type::ITEM,   Type::ITEM,  null, null],
        self::SRC_REFERENCE_LOOT_TEMPLATE     => [null,         Type::ITEM,  null, null],
        self::SRC_SKINNING_LOOT_TEMPLATE      => [Type::NPC,    Type::ITEM,  null, 'SkinLootToNpc'],
        self::SRC_SPELL_LOOT_TEMPLATE         => [Type::SPELL,  Type::ITEM,  null, null],
        self::SRC_SPELL_IMPLICIT_TARGET       => [true,         Type::SPELL, null, null],
        self::SRC_GOSSIP_MENU                 => [true,         true,        null, null],
        self::SRC_GOSSIP_MENU_OPTION          => [true,         true,        null, null],
        self::SRC_CREATURE_TEMPLATE_VEHICLE   => [null,         Type::NPC,   null, null],
        self::SRC_SPELL                       => [null,         Type::SPELL, null, null],
        self::SRC_SPELL_CLICK_EVENT           => [Type::NPC,    Type::SPELL, null, null],
        self::SRC_QUEST_AVAILABLE             => [null,         Type::QUEST, null, null],
        self::SRC_QUEST_SHOW_MARK             => [null,         Type::QUEST, null, null],
        self::SRC_VEHICLE_SPELL               => [Type::NPC,    Type::SPELL, null, null],
        self::SRC_SMART_EVENT                 => [true,         true,        true, null],
        self::SRC_NPC_VENDOR                  => [Type::NPC,    Type::ITEM,  null, null],
        self::SRC_SPELL_PROC                  => [null,         Type::SPELL, null, null],
        self::SRC_AREATRIGGER_CLIENT          => [null,         true,        null, null]
    );

    private const IDX_CND_VAL1 = 0;
    private const IDX_CND_VAL2 = 1;
    private const IDX_CND_VAL3 = 2;
    private const IDX_CND_FN   = 3;

    private static $conditions = array(// [Value1, Value2, Value3, handlerFn]
        self::NONE                     => [null,              null, null, null],
        self::AURA                     => [Type::SPELL,       null, null, null],
        self::ITEM                     => [Type::ITEM,        true, true, null],
        self::ITEM_EQUIPPED            => [Type::ITEM,        null, null, null],
        self::ZONEID                   => [Type::ZONE,        null, null, null],
        self::REPUTATION_RANK          => [Type::FACTION,     true, null, null],
        self::TEAM                     => [true,              null, null, 'factionToSide'],
        self::SKILL                    => [Type::SKILL,       true, null, null],
        self::QUESTREWARDED            => [Type::QUEST,       null, null, null],
        self::QUESTTAKEN               => [Type::QUEST,       null, null, null],
        self::DRUNKENSTATE             => [true,              null, null, null],
        self::WORLD_STATE              => [true,              true, null, null],
        self::ACTIVE_EVENT             => [Type::WORLDEVENT,  null, null, null],
        self::INSTANCE_INFO            => [true,              true, true, null],
        self::QUEST_NONE               => [Type::QUEST,       null, null, null],
        self::CHR_CLASS                => [Type::CHR_CLASS,   null, null, 'maskToBits'],
        self::CHR_RACE                 => [Type::CHR_RACE,    null, null, 'maskToBits'],
        self::ACHIEVEMENT              => [Type::ACHIEVEMENT, null, null, null],
        self::TITLE                    => [Type::TITLE,       null, null, null],
        self::SPAWNMASK                => [true,              null, null, null],
        self::GENDER                   => [true,              null, null, null],
        self::UNIT_STATE               => [true,              null, null, null],
        self::MAPID                    => [true,              true, null, 'mapToZone'],
        self::AREAID                   => [Type::ZONE,        null, null, null],
        self::CREATURE_TYPE            => [true,              null, null, null],
        self::SPELL                    => [Type::SPELL,       null, null, null],
        self::PHASEMASK                => [true,              null, null, null],
        self::LEVEL                    => [true,              true, null, null],
        self::QUEST_COMPLETE           => [Type::QUEST,       null, null, null],
        self::NEAR_CREATURE            => [Type::NPC,         true, true, null],
        self::NEAR_GAMEOBJECT          => [Type::OBJECT,      true, true, null],
        self::OBJECT_ENTRY_GUID        => [true,              true, true, 'typeidToId'],
        self::TYPE_MASK                => [true,              null, null, null],
        self::RELATION_TO              => [true,              true, null, null],
        self::REACTION_TO              => [true,              true, null, null],
        self::DISTANCE_TO              => [true,              true, true, null],
        self::ALIVE                    => [null,              null, null, null],
        self::HP_VAL                   => [true,              true, null, null],
        self::HP_PCT                   => [true,              true, null, null],
        self::REALM_ACHIEVEMENT        => [Type::ACHIEVEMENT, null, null, null],
        self::IN_WATER                 => [null,              null, null, null],
        self::STAND_STATE              => [true,              true, null, null],
        self::DAILY_QUEST_DONE         => [Type::QUEST,       null, null, null],
        self::CHARMED                  => [null,              null, null, null],
        self::PET_TYPE                 => [true,              null, null, null],
        self::TAXI                     => [null,              null, null, null],
        self::QUESTSTATE               => [Type::QUEST,       true, null, null],
        self::QUEST_OBJECTIVE_PROGRESS => [Type::QUEST,       true, true, null],
        self::DIFFICULTY_ID            => [true,              null, null, null],
        self::GAMEMASTER               => [true,              null, null, null]
    );

    private $jsGlobals   = [];
    private $rows        = [];
    private $result      = [];
    private $resultExtra = [];


    /******/
    /* IN */
    /******/

    public function getBySourceEntry(int $entry, int ...$srcType) : bool
    {
        $this->rows = DB::World()->select(
            'SELECT   `SourceTypeOrReferenceId`, `SourceEntry`, `SourceGroup`, `SourceId`, `ElseGroup`,
                      `ConditionTypeOrReference`, `ConditionTarget`, `ConditionValue1`, `ConditionValue2`, `ConditionValue3`, `NegativeCondition`
             FROM     conditions
             WHERE    `SourceTypeOrReferenceId` IN (?a) AND `SourceEntry` = ?d
             ORDER BY `SourceTypeOrReferenceId`, `SourceEntry`, `SourceGroup`, `ElseGroup` ASC',
            $srcType, $entry
        );

        return $this->fromSource();
    }

    public function getBySourceGroup(int $group, int ...$srcType) : bool
    {
        $this->rows = DB::World()->select(
            'SELECT   `SourceTypeOrReferenceId`, `SourceEntry`, `SourceGroup`, `SourceId`, `ElseGroup`,
                      `ConditionTypeOrReference`, `ConditionTarget`, `ConditionValue1`, `ConditionValue2`, `ConditionValue3`, `NegativeCondition`
             FROM     conditions
             WHERE    `SourceTypeOrReferenceId` IN (?a) AND `SourceGroup` = ?d
             ORDER BY `SourceTypeOrReferenceId`, `SourceEntry`, `SourceGroup`, `ElseGroup` ASC',
            $srcType, $group
        );

        return $this->fromSource();
    }

    public function getByCondition(int $type, int $typeId/* , int ...$conditionIds */) : bool
    {
        $lookups = [];                                      // can only be in val1 for now
        foreach (self::$conditions as $cId => [$cVal1, , , ])
            if ($type === $cVal1 /* && (!$conditionIds || in_array($cId, $conditionIds)) */ )
                $lookups[] = sprintf("(c2.`ConditionTypeOrReference` = %d AND c2.`ConditionValue1` = %d)", $cId, $typeId);

        if (!$lookups)
            return false;

        $this->rows = DB::World()->select(sprintf(
            'SELECT   c1.`SourceTypeOrReferenceId`, c1.`SourceEntry`, c1.`SourceGroup`, c1.`SourceId`, c1.`ElseGroup`,
                      c1.`ConditionTypeOrReference`, c1.`ConditionTarget`, c1.`ConditionValue1`, c1.`ConditionValue2`, c1.`ConditionValue3`, c1.`NegativeCondition`
             FROM     conditions c1
             JOIN     conditions c2 ON c1.SourceTypeOrReferenceId = c2.SourceTypeOrReferenceId AND c1.SourceEntry = c2.SourceEntry AND c1.SourceGroup = c2.SourceGroup AND c1.SourceId = c2.SourceId
             WHERE    %s
             ORDER BY `SourceTypeOrReferenceId`, `SourceEntry`, `SourceGroup`, `ElseGroup` ASC'
        , implode(' OR ', $lookups)));

        return $this->fromSource();
    }

    public function addExternalCondition(int $srcType, string $groupKey, array $condition, bool $orGroup = false) : void
    {
        if (!isset(self::$source[$srcType]))
            return;

        [$cId, $cVal1, $cVal2, $cVal3] = array_pad($condition, 5, 0);
        if (!isset(self::$conditions[abs($cId)]))
            return;

        while (substr_count($groupKey, ':') < 3)
            $groupKey .= ':0';                              // pad with missing srcEntry, SrcId, cndTarget to group key

        if ($c = $this->prepareCondition($cId, $cVal1, $cVal2, $cVal3))
        {
            if ($orGroup)
                $this->result[$srcType][$groupKey][] = [$c];
            else if (!isset($this->result[$srcType][$groupKey][0]))
                $this->result[$srcType][$groupKey][0] = [$c];
            else
                $this->result[$srcType][$groupKey][0][] = $c;
        }
    }


    /*******/
    /* OUT */
    /*******/

    public function toListviewTab(string $id = 'conditions', string $name = '') : array
    {
        if (!$this->result)
            return [];

        $out  = [];
        $nCnd = 0;
        foreach ($this->result as $srcType => $srcData)
        {
            foreach ($srcData as $grpKey => $grpData)
            {
                if (!isset($this->resultExtra[$srcType][$grpKey]))
                {
                    $nCnd++;
                    $out[$srcType][$grpKey] = $grpData;
                }
                else
                {
                    $nCnd += count($this->resultExtra[$srcType][$grpKey]);
                    foreach ($this->resultExtra[$srcType][$grpKey] as $extraGrp)
                        $out[$srcType][$extraGrp] = $grpData;
                }
            }
        }

        $data = "<script type=\"text/javascript\">\n" .
                "    var markup = ConditionList.createTab(".Util::toJSON($out).");\n" .
                "    Markup.printHtml(markup, 'tab-".$id."', { allow: Markup.CLASS_STAFF })\n" .
                "</script>";

        $tab = array(
            'data' => $data,
            'id'   => $id,
            'name' => ($name ?: '$LANG.tab_conditions') . '+" ('.$nCnd.')"'
        );

        return [null, $tab];
    }

    public function toListviewColumn(array &$lvRows, ?array &$extraCols = [], int $srcEntry = 0) : bool
    {
        if (!$this->result)
            return false;

        $success = false;
        foreach ($lvRows as $key => &$row)
        {
            $key = ($row['id'] ?? $key).':'.$srcEntry;      // loot rows don't have an 'id' while being generated, but they have a usable $key
            while (substr_count($key, ':') < 3)             // pad with missing srcEntry, SrcId, cndTarget to group key
                $key .= ':0';

            foreach ($this->result as $cndData)
            {
                if (empty($cndData[$key]))
                    continue;

                $row['condition'][self::SRC_NONE][$key] = $cndData[$key];
                $success = true;
            }
        }

        if ($success)
            $extraCols[] = '$Listview.extraCols.condition';

        return $success;
    }

    public function getJsGlobals() : array
    {
        return $this->jsGlobals;
    }


    /*********/
    /* Other */
    /*********/

    public static function lootTableToConditionSource(string $lootTable) : int
    {
        switch ($lootTable)
        {
            case LOOT_FISHING:     return self::SRC_FISHING_LOOT_TEMPLATE;
            case LOOT_CREATURE:    return self::SRC_CREATURE_LOOT_TEMPLATE;
            case LOOT_GAMEOBJECT:  return self::SRC_GAMEOBJECT_LOOT_TEMPLATE;
            case LOOT_ITEM:        return self::SRC_ITEM_LOOT_TEMPLATE;
            case LOOT_DISENCHANT:  return self::SRC_DISENCHANT_LOOT_TEMPLATE;
            case LOOT_PROSPECTING: return self::SRC_PROSPECTING_LOOT_TEMPLATE;
            case LOOT_MILLING:     return self::SRC_MILLING_LOOT_TEMPLATE;
            case LOOT_PICKPOCKET:  return self::SRC_PICKPOCKETING_LOOT_TEMPLATE;
            case LOOT_SKINNING:    return self::SRC_SKINNING_LOOT_TEMPLATE;
            case LOOT_MAIL:        return self::SRC_MAIL_LOOT_TEMPLATE;
            case LOOT_SPELL:       return self::SRC_SPELL_LOOT_TEMPLATE;
            case LOOT_REFERENCE:   return self::SRC_REFERENCE_LOOT_TEMPLATE;
            default:               return self::SRC_NONE;
        }
    }

    public static function extendListviewRow(array &$lvRow, int $srcType, int $groupKey, array $condition) : bool
    {
        if (!isset(self::$source[$srcType]))
            return false;

        [$cId, $cVal1, $cVal2, $cVal3] = array_pad($condition, 5, 0);
        if (!isset(self::$conditions[abs($cId)]))
            return false;

        while (substr_count($groupKey, ':') < 3)
            $groupKey .= ':0';                              // pad with missing srcEntry, SrcId, cndTarget to group key

        if ($c = (new self())->prepareCondition($cId, $cVal1, $cVal2, $cVal3))
            $lvRow['condition'][$srcType][$groupKey][] = [$c];

        return true;
    }

    private function fromSource() : bool
    {
        // itr over rows and prep data
        if (!$this->rows)
            return !empty($this->result);                   // respect previously added externalCnd

        foreach ($this->rows as $r)
        {
            if (!isset(self::$source[$r['SourceTypeOrReferenceId']]))
            {
                trigger_error('Conditions: skipping condition with unknown SourceTypeOrReferenceId #'.$r['SourceTypeOrReferenceId'], E_USER_WARNING);
                continue;
            }

            if (!isset(self::$conditions[$r['ConditionTypeOrReference']]))
            {
                trigger_error('Conditions: skipping condition with unknown ConditionTypeOrReference #'.$r['ConditionTypeOrReference'], E_USER_WARNING);
                continue;
            }

            [$sType, $sGroup, $sEntry, $sId, $cTarget] = $this->prepareSource($r['SourceTypeOrReferenceId'], $r['SourceGroup'], $r['SourceEntry'], $r['SourceId'], $r['ConditionTarget']);
            if ($sType === null)
                continue;

            $cnd = $this->prepareCondition(
                $r['NegativeCondition'] ? -$r['ConditionTypeOrReference'] : $r['ConditionTypeOrReference'],
                $r['ConditionValue1'],
                $r['ConditionValue2'],
                $r['ConditionValue3']
            );
            if (!$cnd)
                continue;

            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            $this->result[$r['SourceTypeOrReferenceId']] [$group] [$r['ElseGroup']] [] = $cnd;
        }

        return true;
    }

    private function prepareSource(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : array
    {
        // only one entry in array expected
        if ($fn = self::$source[$sType][self::IDX_SRC_FN])
            if (!$this->$fn($sType, $sGroup, $sEntry, $sId, $cTarget))
                return [null, null, null, null, null];

        [$grp, $entry, $id, $_] = self::$source[$sType];
        if (is_int($grp))
            $this->jsGlobals[$grp][$sGroup] = $sGroup;
        if (is_int($entry))
            $this->jsGlobals[$entry][$sEntry] = $sEntry;
    //  Note: sourceId currently has no typed content
    //  if (is_int($id))
    //      $this->jsGlobals[$id][$sId] = $sId;

        // more checks? not all sources can retarget
        $cTarget = min(1, max(0, $cTarget));

        return [$sType, $sGroup, $sEntry, $sId, $cTarget];
    }

    private function prepareCondition($cId, $cVal1, $cVal2, $cVal3) : array
    {
        if ($fn = self::$conditions[abs($cId)][self::IDX_CND_FN])
            if (!$this->$fn(abs($cId), $cVal1, $cVal2, $cVal3))
                return [];

        $result = [$cId];

        for ($i = 0; $i < 3; $i++)
        {
            $field = self::$conditions[abs($cId)][$i];

            if (is_int($field))
                $this->jsGlobals[$field][${'cVal'.($i+1)}] = ${'cVal'.($i+1)};
            if ($field)
                $result[] = ${'cVal'.($i+1)};               // variable amount of condition values
        }

        return $result;
    }

    private function factionToSide($cId, &$cVal1, $cVal2, $cVal3) : bool
    {
        if ($cVal1 == 469)
            $cVal1 = SIDE_ALLIANCE;
        else if ($cVal1 == 67)
            $cVal1 = SIDE_HORDE;
        else
            $cVal1 = SIDE_BOTH;

        return true;
    }

    private function mapToZone($cId, &$cVal1, &$cVal2, $cVal3) : bool
    {
        // use g_zone_categories id
        if ($cVal1 == 530)                                  // outland
            $cVal1 = 8;
        else if ($cVal1 == 571)                             // northrend
            $cVal1 = 10;
        else if ($cVal1 == 0 || $cVal1 == 1)                // eastern kingdoms / kalimdor
            ;                                               // cVal alrady correct - NOP
        else if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_zones WHERE `mapId` = ?d AND `parentArea` = 0 AND (`cuFlags` & ?d) = 0', $cVal1, CUSTOM_EXCLUDE_FOR_LISTVIEW))
        {
            // remap for instanced area - do not use List (pointless overhead)
            $this->jsGlobals[Type::ZONE][$id] = $id;
            $cVal2 = $id;
            $cVal1 = 0;
        }
        else
        {
            trigger_error('Conditions - CONDITION_MAPID has invalid mapId #'.$cVal1, E_USER_WARNING);
            return false;
        }

        return true;
    }

    private function maskToBits($cId, &$cVal1, $cVal2, $cVal3) : bool
    {
        if ($cId == self::CHR_CLASS)
        {
            $cVal1 &= CLASS_MASK_ALL;
            foreach (Util::mask2bits($cVal1, 1) as $cId)
                $this->jsGlobals[Type::CHR_CLASS][$cId] = $cId;
        }

        if ($cId == self::CHR_RACE)
        {
            $cVal1 &= RACE_MASK_ALL;
            foreach (Util::mask2bits($cVal1, 1) as $rId)
                $this->jsGlobals[Type::CHR_RACE][$rId] = $rId;
        }

        return true;
    }

    private function typeidToId($cId, $cVal1, &$cVal2, &$cVal3) : bool
    {
        if ($cVal1 == self::TYPEID_UNIT)
        {
            if ($cVal3 && ($_ = DB::Aowow()->selectCell('SELECT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` = ?d', Type::NPC, $cVal3)))
                $cVal2 = intVal($_);

            if ($cVal2)
                $this->jsGlobals[Type::NPC][$cVal2] = $cVal2;
        }
        else if ($cVal1 == self::TYPEID_GAMEOBJECT)
        {
            if ($cVal3 && ($_ = DB::Aowow()->selectCell('SELECT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` = ?d', Type::OBJECT, $cVal3)))
                $cVal2 = intVal($_);

            if ($cVal2)
                $this->jsGlobals[Type::OBJECT][$cVal2] = $cVal2;
        }
        else                                                // Player or Corpse .. no guid
            $cVal2 = $cVal3 = 0;

        // maybe prepare other types?
        return true;
    }

    private function lootIdToNpc(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : bool
    {
        if (!$sGroup)
        {
            trigger_error('Conditions::lootToNpc - skipping reference to creature_loot_template entry 0', E_USER_WARNING);
            return false;
        }

        if ($npcs = DB::Aowow()->selectCol('SELECT `id` FROM ?_creature WHERE `lootId` = ?d', $sGroup))
        {
            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            foreach ($npcs as $npcId)
            {
                $this->jsGlobals[Type::NPC][$npcId] = $npcId;
                $this->resultExtra[$sType][$group][] = $npcId . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            }

            return true;
        }

        trigger_error('Conditions::lootToNpc - creature_loot_template #'.$sGroup.' unreferenced?', E_USER_WARNING);
        return false;
    }

    private function disenchantIdToItem(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : bool
    {
        if (!$sGroup)
        {
            trigger_error('Conditions::disenchantIdToItem - skipping reference to disenchant_loot_template entry 0', E_USER_WARNING);
            return false;
        }

        if ($items = DB::Aowow()->selectCol('SELECT `id` FROM ?_items WHERE `disenchantId` = ?d', $sGroup))
        {
            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            foreach ($items as $itemId)
            {
                $this->jsGlobals[Type::ITEM][$itemId] = $itemId;
                $this->resultExtra[$sType][$group][] = $itemId . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            }

            return true;
        }

        trigger_error('Conditions::disenchantIdToItem - disenchant_loot_template #'.$sGroup.' unreferenced?', E_USER_WARNING);
        return false;
    }

    private function lootIdToGObject(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : bool
    {
        if (!$sGroup)
        {
            trigger_error('Conditions::lootIdToGObject - skipping reference to gameobject_loot_template entry 0', E_USER_WARNING);
            return false;
        }

        if ($gos = DB::Aowow()->selectCol('SELECT `id` FROM ?_objects WHERE `lootId` = ?d', $sGroup))
        {
            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            foreach ($gos as $goId)
            {
                $this->jsGlobals[Type::OBJECT][$goId] = $goId;
                $this->resultExtra[$sType][$group][] = $goId . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            }

            return true;
        }

        trigger_error('Conditions::lootIdToGObject - gameobject_loot_template #'.$sGroup.' unreferenced?', E_USER_WARNING);
        return false;
    }

    private function RewardTemplateToQuest(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : bool
    {
        if (!$sGroup)
        {
            trigger_error('Conditions::RewardTemplateToQuest - skipping reference to mail_loot_template entry 0', E_USER_WARNING);
            return false;
        }

        if ($quests = DB::Aowow()->selectCol('SELECT `id` FROM ?_quests WHERE `rewardMailTemplateId` = ?d', $sGroup))
        {
            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            foreach ($quests as $questId)
            {
                $this->jsGlobals[Type::QUEST][$questId] = $questId;
                $this->resultExtra[$sType][$group][] = $questId . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            }

            return true;
        }

        trigger_error('Conditions::RewardTemplateToQuest - mail_loot_template #'.$sGroup.' unreferenced?', E_USER_WARNING);
        return false;
    }

    private function PickpocketLootToNpc(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : bool
    {
        if (!$sGroup)
        {
            trigger_error('Conditions::PickpocketLootToNpc - skipping reference to pickpocketing_loot_template entry 0', E_USER_WARNING);
            return false;
        }

        if ($npcs = DB::Aowow()->selectCol('SELECT `id` FROM ?_creature WHERE `pickpocketLootId` = ?d', $sGroup))
        {
            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            foreach ($npcs as $npcId)
            {
                $this->jsGlobals[Type::NPC][$npcId] = $npcId;
                $this->resultExtra[$sType][$group][] = $npcId . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            }

            return true;
        }

        trigger_error('Conditions::PickpocketLootToNpc - pickpocketing_loot_template #'.$sGroup.' unreferenced?', E_USER_WARNING);
        return false;
    }

    private function SkinLootToNpc(int $sType, int $sGroup, int $sEntry, int $sId, int $cTarget) : bool
    {
        if (!$sGroup)
        {
            trigger_error('Conditions::SkinLootToNpc - skipping reference to skinning_loot_template entry 0', E_USER_WARNING);
            return false;
        }

        if ($npcs = DB::Aowow()->selectCol('SELECT `id` FROM ?_creature WHERE `skinLootId` = ?d', $sGroup))
        {
            $group = $sGroup . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            foreach ($npcs as $npcId)
            {
                $this->jsGlobals[Type::NPC][$npcId] = $npcId;
                $this->resultExtra[$sType][$group][] = $npcId . ':' . $sEntry . ':' . $sId . ':' . $cTarget;
            }

            return true;
        }

        trigger_error('Conditions::SkinLootToNpc - skinning_loot_template #'.$sGroup.' unreferenced?', E_USER_WARNING);
        return false;
    }
}

?>
