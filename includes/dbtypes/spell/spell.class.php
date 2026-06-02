<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Spell extends DBType
{
    use TrSourceHelper;

    public const /* int */ INTERACTIVE_NONE     = 0;
    public const /* int */ INTERACTIVE_EMBEDDED = 1;        // parse combat ratings to %
    public const /* int */ INTERACTIVE_FULL     = 2;        // additionaly allow links and hover tooltips

    public readonly  int       $cuFlags;
    public readonly  LocString $name;
    public readonly  LocString $rank;
    public readonly  LocString $buff;
    public readonly  LocString $description;
    public readonly  int       $category;
    public readonly  int       $dispelType;
    public readonly  int       $mechanic;
    /** @var int[] $attributes - length: 8  [Base, Ex, ExB, ExC, ExD, ExE, ExF, ExG] */
    public readonly  array     $attributes;
    public readonly  int       $typeCat;
 // public readonly  int       $targets;
    public readonly  int       $spellFocusObject;
    public readonly  int       $stackAmount;
 // public readonly  int       $rankNo;
    public readonly  int       $spellVisualId;
    public readonly  int       $maxTargetLevel;
    public readonly  int       $maxAffectedTargets;
    public readonly  int       $damageClass;
    /** @var int[] $skillLines - length: variable */
    public readonly  array     $skillLines;
    public readonly  int       $learnedAt;
    public readonly  int       $skillLevelGrey;
    public readonly  int       $skillLevelYellow;
    public readonly  int       $trainingCost;
    public readonly  int       $schoolMask;
    public readonly  int       $spellDescriptionVariableId;

    public readonly  float     $castTime;

    public readonly  int       $casterAuraSpell;
    public readonly  int       $casterAuraSpellNot;
    public readonly  int       $targetAuraSpell;
    public readonly  int       $targetAuraSpellNot;

    public readonly  int       $recoveryTime;
    public readonly  int       $recoveryCategory;
    public readonly  int       $startRecoveryTime;
    public readonly  int       $startRecoveryCategory;

    public readonly  int       $procChance;
    public readonly  int       $procCharges;
    public readonly  float     $procCustom;
    public readonly  int       $procCooldown;

    public readonly  int       $maxLevel;
    public readonly  int       $baseLevel;
    public readonly  int       $spellLevel;
    public readonly  int       $talentLevel;

    public readonly  int       $duration;

    public readonly  int       $powerType;
    public readonly  int       $powerCost;
    public readonly  int       $powerCostPerLevel;
    public readonly  int       $powerCostPercent;
    public readonly  int       $powerPerSecond;
 // public readonly  int       $powerPerSecondPerLevel;
 // public readonly  int       $powerGainRunicPower;
    public readonly  int       $powerCostRunes;

    public readonly  int       $rangeMaxHostile;
    public readonly  int       $rangeMinHostile;
    public readonly  int       $rangeMinFriend;
    public readonly  int       $rangeMaxFriend;
    public readonly  LocString $rangeText;

    /** @var int[] $tool - length: 2 */
    public readonly  array     $tool;
    /** @var int[] $toolCategory - length: 2 */
    public readonly  array     $toolCategory;
    /** @var int[] $reagent - length: 8 */
    public readonly  array     $reagent;
    /** @var int[] $reagentCount - length: 8 */
    public readonly  array     $reagentCount;

    public readonly  int       $iconId;
    public readonly  string    $icon;
 // public readonly  int       $iconIdBak;
    public readonly ?int       $iconIdAlt;
    public readonly ?string    $iconAlt;

    public readonly  int       $equippedItemClass;
    public readonly  int       $equippedItemSubClassMask;
    public readonly  int       $equippedItemInventoryTypeMask;
    public readonly  int       $reqRaceMask;
    public readonly  int       $reqClassMask;
    public readonly  int       $reqSpellId;
    public readonly  int       $reqSkillLevel;
    public readonly  int       $stanceMask;
 // public readonly  int       $stanceMaskNot;

    /** @var int[] $effectId - length: 3 */
    public readonly  array     $effectId;
    /** @var int[] $effectDieSides - length: 3 */
    public readonly  array     $effectDieSides;
    /** @var float[] $effectRealPointsPerLevel - length: 3 */
    public readonly  array     $effectRealPointsPerLevel;
    /** @var int[] $effectBasePoints - length: 3 */
    public readonly  array     $effectBasePoints;
    /** @var int[] $effectMechanic - length: 3 */
    public readonly  array     $effectMechanic;
    /** @var int[] $effectImplicitTargetA - length: 3 */
    public readonly  array     $effectImplicitTargetA;
    /** @var int[] $effectImplicitTargetB - length: 3 */
    public readonly  array     $effectImplicitTargetB;
 // /** @var int[] $effectRadiusMin - length: 3 */
 // public readonly  array     $effectRadiusMin;
    /** @var int[] $effectRadiusMax - length: 3 */
    public readonly  array     $effectRadiusMax;
    /** @var int[] $effectAuraId - length: 3 */
    public readonly  array     $effectAuraId;
    /** @var int[] $effectPeriode - length: 3 */
    public readonly  array     $effectPeriode;
    /** @var int[] $effecChainTarget - length: 3 */
    public readonly  array     $effecChainTarget;
    /** @var int[] $effectCreateItemId - length: 3 */
    public readonly  array     $effectCreateItemId;
    /** @var int[] $effectMiscValue - length: 3 */
    public readonly  array     $effectMiscValue;
    /** @var int[] $effectMiscValueB - length: 3 */
    public readonly  array     $effectMiscValueB;
    /** @var int[] $effectTriggerSpell - length: 3 */
    public readonly  array     $effectTriggerSpell;
    /** @var int[] $effectPointsPerComboPoint - length: 3 */
    public readonly  array     $effectPointsPerComboPoint;
    /** @var int[] $effectSpellClassMaskA - length: 3 */
    public readonly  array     $effectSpellClassMaskA;
    /** @var int[] $effectSpellClassMaskB - length: 3 */
    public readonly  array     $effectSpellClassMaskB;
    /** @var int[] $effectSpellClassMaskC - length: 3 */
    public readonly  array     $effectSpellClassMaskC;
    /** @var float[] $effectValueMultiplier - length: 3 */
    public readonly  array     $effectValueMultiplier;
    /** @var float[] $effectDamageMultiplier - length: 3 */
    public readonly  array     $effectDamageMultiplier;
    /** @var float[] $effectBonusMultiplier - length: 3 */
    public readonly  array     $effectBonusMultiplier;

    public readonly  int       $spellFamilyId;
    /** @var int[] $spellFamilyFlags - length: 3 */
    public readonly  array     $spellFamilyFlags;

    public static  int      $dbType     = Type::SPELL;
    public static  string   $brickFile  = 'spell';
    public static  string   $dataTable  = '::spell';

    public const /* array */ SKILLLINE_CATEGORY = array(
         6 => [ 43,  44,  45,  46,  54,  55,  95, 118, 136, 160, 162, 172, 173, 176, 226, 228, 229, 473], // Weapons
         8 => [293, 413, 414, 415, 433],                                                                  // Armor
         9 => SKILLS_TRADE_SECONDARY,                                                                     // sec. Professions
        10 => [ 98, 109, 111, 113, 115, 137, 138, 139, 140, 141, 313, 315, 673, 759],                     // Languages
        11 => SKILLS_TRADE_PRIMARY                                                                        // prim. Professions
    );

    public const /* array */ MOD_AURAS = array(
        SPELL_AURA_ADD_FLAT_MODIFIER,      SPELL_AURA_ADD_PCT_MODIFIER,                 SPELL_AURA_NO_REAGENT_USE,
        SPELL_AURA_ABILITY_PERIODIC_CRIT,  SPELL_AURA_MOD_TARGET_ABILITY_ABSORB_SCHOOL, SPELL_AURA_ABILITY_IGNORE_AURASTATE,
        SPELL_AURA_ALLOW_ONLY_ABILITY,     SPELL_AURA_IGNORE_MELEE_RESET,               SPELL_AURA_ABILITY_CONSUME_NO_AMMO,
        SPELL_AURA_MOD_IGNORE_SHAPESHIFT,  SPELL_AURA_PERIODIC_HASTE,                   SPELL_AURA_OVERRIDE_CLASS_SCRIPTS,
        SPELL_AURA_MOD_DAMAGE_FROM_CASTER, SPELL_AURA_ADD_TARGET_TRIGGER,               SPELL_AURA_IGNORE_COMBAT_RESULT,     /* SPELL_AURA_DUMMY ? */
    );

    private const /* array */ PROFICIENCY_CATEGORY = array(
         6 => 1,
         8 => 2,
        10 => 4
    );

    public const EFFECTS_SCALING_HEAL     = array( // as per Unit::SpellHealingBonusDone() calls in TC
        SPELL_EFFECT_HEAL,                  SPELL_EFFECT_HEAL_PCT,                          SPELL_EFFECT_HEAL_MECHANICAL,                   SPELL_EFFECT_HEALTH_LEECH
    );
    public const EFFECTS_SCALING_DAMAGE   = array( // as per Unit::SpellDamageBonusDone() calls in TC
        SPELL_EFFECT_SCHOOL_DAMAGE,         SPELL_EFFECT_HEALTH_LEECH,                      SPELL_EFFECT_POWER_BURN
    );
    public const EFFECTS_LDC_SCALING      = array(
        SPELL_EFFECT_SCHOOL_DAMAGE,         SPELL_EFFECT_DUMMY,                             SPELL_EFFECT_POWER_DRAIN,                       SPELL_EFFECT_HEALTH_LEECH,                      SPELL_EFFECT_HEAL,
        SPELL_EFFECT_WEAPON_DAMAGE,         SPELL_EFFECT_POWER_BURN,                        SPELL_EFFECT_SCRIPT_EFFECT,                     SPELL_EFFECT_NORMALIZED_WEAPON_DMG,             SPELL_EFFECT_FORCE_CAST_WITH_VALUE,
        SPELL_EFFECT_TRIGGER_SPELL_WITH_VALUE,                                              SPELL_EFFECT_TRIGGER_MISSILE_SPELL_WITH_VALUE
    );
    public const EFFECTS_ITEM_CREATE      = array(
        SPELL_EFFECT_CREATE_ITEM,           SPELL_EFFECT_SUMMON_CHANGE_ITEM,                SPELL_EFFECT_CREATE_RANDOM_ITEM,                SPELL_EFFECT_CREATE_MANA_GEM,                   SPELL_EFFECT_CREATE_ITEM_2
    );
    public const EFFECTS_TRIGGER          = array(
        SPELL_EFFECT_DUMMY,                 SPELL_EFFECT_TRIGGER_MISSILE,                   SPELL_EFFECT_TRIGGER_SPELL,                     SPELL_EFFECT_FEED_PET,                          SPELL_EFFECT_FORCE_CAST,
        SPELL_EFFECT_FORCE_CAST_WITH_VALUE, SPELL_EFFECT_TRIGGER_SPELL_WITH_VALUE,          SPELL_EFFECT_TRIGGER_MISSILE_SPELL_WITH_VALUE,  SPELL_EFFECT_TRIGGER_SPELL_2,                   SPELL_EFFECT_SUMMON_RAF_FRIEND,
        SPELL_EFFECT_TITAN_GRIP,            SPELL_EFFECT_FORCE_CAST_2,                      SPELL_EFFECT_REMOVE_AURA
    );
    public const EFFECTS_TEACH            = array(
        SPELL_EFFECT_LEARN_SPELL,           SPELL_EFFECT_LEARN_PET_SPELL                    /*SPELL_EFFECT_UNLEARN_SPECIALIZATION*/
    );
    public const EFFECTS_MODEL_OBJECT     = array(
        SPELL_EFFECT_TRANS_DOOR,            SPELL_EFFECT_SUMMON_OBJECT_WILD,                SPELL_EFFECT_SUMMON_OBJECT_SLOT1,               SPELL_EFFECT_SUMMON_OBJECT_SLOT2,               SPELL_EFFECT_SUMMON_OBJECT_SLOT3,
        SPELL_EFFECT_SUMMON_OBJECT_SLOT4
    );
    public const EFFECTS_MODEL_NPC        = array(
        SPELL_EFFECT_SUMMON,                SPELL_EFFECT_SUMMON_PET,                        SPELL_EFFECT_SUMMON_DEMON,                      SPELL_EFFECT_KILL_CREDIT,                       SPELL_EFFECT_KILL_CREDIT2
    );
    public const EFFECTS_DIRECT_SCALING   = array( // as per Unit::GetCastingTimeForBonus()
        SPELL_EFFECT_SCHOOL_DAMAGE,         SPELL_EFFECT_ENVIRONMENTAL_DAMAGE,              SPELL_EFFECT_POWER_DRAIN,                       SPELL_EFFECT_HEALTH_LEECH,                      SPELL_EFFECT_POWER_BURN,
        SPELL_EFFECT_HEAL
    );
    public const EFFECTS_ENCHANTMENT      = array(
        SPELL_EFFECT_ENCHANT_ITEM,          SPELL_EFFECT_ENCHANT_ITEM_TEMPORARY,            SPELL_EFFECT_ENCHANT_HELD_ITEM,                 SPELL_EFFECT_ENCHANT_ITEM_PRISMATIC
    );

    public const AURAS_SCALING_HEAL       = array( // as per Unit::SpellHealingBonusDone() calls in TC (SPELL_AURA_SCHOOL_ABSORB + SPELL_AURA_MANA_SHIELD priest/mage cases are scripted)
        SPELL_AURA_PERIODIC_HEAL,           SPELL_AURA_PERIODIC_LEECH,                      SPELL_AURA_OBS_MOD_HEALTH
    );
    public const AURAS_SCALING_DAMAGE     = array( // as per Unit::SpellDamageBonusDone() calls in TC
        SPELL_AURA_PERIODIC_DAMAGE,         SPELL_AURA_PERIODIC_LEECH,                      SPELL_AURA_DAMAGE_SHIELD,                       SPELL_AURA_PROC_TRIGGER_DAMAGE
    );
    public const AURAS_LDC_SCALING      = array(
        SPELL_AURA_PERIODIC_DAMAGE,         SPELL_AURA_DUMMY,                               SPELL_AURA_PERIODIC_HEAL,                       SPELL_AURA_DAMAGE_SHIELD,                       SPELL_AURA_PROC_TRIGGER_DAMAGE,
        SPELL_AURA_PERIODIC_LEECH,          SPELL_AURA_PERIODIC_MANA_LEECH,                 SPELL_AURA_SCHOOL_ABSORB,                       SPELL_AURA_PERIODIC_TRIGGER_SPELL_WITH_VALUE
    );
    public const AURAS_ITEM_CREATE        = array(
        SPELL_AURA_CHANNEL_DEATH_ITEM
    );
    public const AURAS_TRIGGER            = array(
        SPELL_AURA_DUMMY,                   SPELL_AURA_PERIODIC_TRIGGER_SPELL,              SPELL_AURA_PROC_TRIGGER_SPELL,                  SPELL_AURA_PERIODIC_TRIGGER_SPELL_FROM_CLIENT,  SPELL_AURA_ADD_TARGET_TRIGGER,
        SPELL_AURA_PERIODIC_DUMMY,          SPELL_AURA_PERIODIC_TRIGGER_SPELL_WITH_VALUE,   SPELL_AURA_PROC_TRIGGER_SPELL_WITH_VALUE,       SPELL_AURA_CONTROL_VEHICLE,                     SPELL_AURA_LINKED
    );
    public const AURAS_MODEL_NPC          = array(
        SPELL_AURA_TRANSFORM,               SPELL_AURA_MOUNTED,                             SPELL_AURA_CHANGE_MODEL_FOR_ALL_HUMANOIDS,      SPELL_AURA_X_RAY,
        SPELL_AURA_MOD_FAKE_INEBRIATE
    );
    public const AURAS_PERIODIC_SCALING   = array( // as per Unit::GetCastingTimeForBonus()
        SPELL_AURA_PERIODIC_DAMAGE,         SPELL_AURA_PERIODIC_HEAL,                       SPELL_AURA_PERIODIC_LEECH
    );

    // spell text rendering
    private        array $descriptionVariables = [];
    private        array $parsedText           = [];
    private        array $refSpells            = [];
    private        int   $interactive          = self::INTERACTIVE_EMBEDDED;
    private        int   $charLevel            = MAX_LEVEL;
    private        bool  $isScaling            = false;

    public const /* string */ QUERY_BASE = 'SELECT s.*, s.`id` AS ARRAY_KEY FROM ::spell s';
    public const /* array  */ QUERY_OPTS = array(
        's'   => [['src', 'sr', 'ic', 'ica']],              //  6: Type::SPELL
        'nml' => ['j' => ['::spell_search nml ON nml.`id` = s.`id` AND nml.`locale` = DB_LOC_I']],
        'ic'  => ['j' => ['::icons ic  ON ic.`id`  = s.`iconId`',    true], 's' => ', ic.`name` AS "icon"'],
        'ica' => ['j' => ['::icons ica ON ica.`id` = s.`iconIdAlt`', true], 's' => ', ica.`name` AS "iconAlt"'],
        'sr'  => ['j' => ['::spellrange sr ON sr.`id` = s.`rangeId`'], 's' => ', sr.`rangeMinHostile`, sr.`rangeMinFriend`, sr.`rangeMaxHostile`, sr.`rangeMaxFriend`, sr.`name_loc0` AS "rangeText_loc0", sr.`name_loc2` AS "rangeText_loc2", sr.`name_loc3` AS "rangeText_loc3", sr.`name_loc4` AS "rangeText_loc4", sr.`name_loc6` AS "rangeText_loc6", sr.`name_loc8` AS "rangeText_loc8"'],
        'src' => ['j' => ['::source src ON `type` = 6 AND `typeId` = s.`id`', true], 's' => ', `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`, `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
    );

    public function __construct(int|array $initData, array $extraOpts = [])
    {
        parent::__construct($initData, $extraOpts);

        if (isset($extraOpts['interactive']))
            $this->interactive = $extraOpts['interactive'];

        if (isset($extraOpts['charLevel']))
            $this->charLevel = $extraOpts['charLevel'];
    }

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->initSources($initData);

        $this->name        = new LocString($initData, 'name',        pruneFromSrc: true);
        $this->rank        = new LocString($initData, 'rank',        pruneFromSrc: true);
        $this->buff        = new LocString($initData, 'buff',        pruneFromSrc: true);
        $this->description = new LocString($initData, 'description', pruneFromSrc: true);
        $this->rangeText   = new LocString($initData, 'rangeText',   pruneFromSrc: true);

        // unpack skillLines
        $sl = [];
        if ($initData['skillLine1'] < 0)
        {
            foreach (Game::$skillLineMask[$initData['skillLine1']] as $idx => [, $skillLineId])
                if ($initData['skillLine2OrMask'] & (1 << $idx))
                    $sl[] = $skillLineId;
        }
        else
        {
            // note: for Basic Campfire (818) the deprecated skill Survival (142) is shown before Cooking
            if ($_ = $initData['skillLine1'])
                $sl[] = $_;
            if ($_ = $initData['skillLine2OrMask'])
                $sl[] = $_;
        }

        $this->skillLines = $sl;

        // attributes
        $this->attributes = [$initData['attributes0'], $initData['attributes1'], $initData['attributes2'], $initData['attributes3'], $initData['attributes4'], $initData['attributes5'], $initData['attributes6'], $initData['attributes7']];

        // tools
        $this->tool         = [$initData['tool1'],         $initData['tool2']];
        $this->toolCategory = [$initData['toolCategory1'], $initData['toolCategory2']];

        // reagents
        $this->reagent      = [$initData['reagent1'],     $initData['reagent2'],     $initData['reagent3'],     $initData['reagent4'],     $initData['reagent5'],     $initData['reagent6'],     $initData['reagent7'],     $initData['reagent8']];
        $this->reagentCount = [$initData['reagentCount1'],$initData['reagentCount2'],$initData['reagentCount3'],$initData['reagentCount4'],$initData['reagentCount5'],$initData['reagentCount6'],$initData['reagentCount7'],$initData['reagentCount8']];

        // spellFamilyFlags
        $this->spellFamilyFlags = [$initData['spellFamilyFlags1'], $initData['spellFamilyFlags2'], $initData['spellFamilyFlags3']];

        $effBuff = [];
        foreach ($initData as $k => $v)
        {
            // effects (collect)
            if (preg_match('/effect([123])([a-z]+)/i', $k, $m))
            {
                $effBuff['effect'.$m[2]][$m[1] - 1] = $v;
                continue;
            }

            switch ($k)
            {
                case 'skillLine1':                          // unpacked earlier
                case 'skillLine2OrMask':
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'icon':                                // fix missing icons
                    $this->$k = $initData['icon'] ?: DEFAULT_ICON;
                    break;
                case 'reqClassMask':                        // prepare required classes
                    $this->$k = ($_ = $initData['reqClassMask'] & ChrClass::MASK_ALL) == ChrClass::MASK_ALL ? 0 : $_;
                    break;
                case 'reqRaceMask':                         // prepare required races
                    $this->$k = ($_ = $initData['reqRaceMask'] & ChrRace::MASK_ALL) == ChrRace::MASK_ALL ? 0 : $_;
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }

        // effects (apply)
        foreach ($effBuff as $k => $v)
            if (property_exists($this, $k))
                $this->$k = $v;
    }

    /**
     * @param int $addInfoMask
     * * `0x0080 - LISTVIEWINFO_MODEL`:
     */
    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = [];

        $quality = ($this->cuFlags & SPELL_CU_QUALITY_MASK) >> 8;
        $talent  = $this->cuFlags & (SPELL_CU_TALENT | SPELL_CU_TALENTSPELL) && $this->spellLevel <= 1;

        $data = array(
            'id'           => $this->id,
            'name'         => ($quality ?: '@').$this->name,
            'icon'         => $this->iconAlt ?: $this->icon,
            'level'        => $talent ? $this->talentLevel : $this->spellLevel,
            'school'       => $this->schoolMask,
            'cat'          => $this->typeCat,
            'trainingcost' => $this->trainingCost,
            'skill'        => count($this->skillLines) > 4 ? array_splice($this->skillLines, 3, replacement: -1) : $this->skillLines, // display max 4 skillLines (fills max three lines in listview)
            'reagents'     => array_values($this->getReagents()),
            'source'       => []
         // 'talentspec'   => $this->skillLines[0]      not used: g_chr_specs has the wrong structure for it; also setting .cat and .type does the same
        );

        // Proficiencies
        if ($this->typeCat == -11)
            foreach (self::PROFICIENCY_CATEGORY as $cat => $type)
                if (in_array($this->skillLines[0], self::SKILLLINE_CATEGORY[$cat]))
                    $data['type'] = $type;

        // creates item
        foreach ($this->canCreateItem() as $idx)
        {
            $max = $this->effectDieSides[$idx] + $this->effectBasePoints[$idx];
            $min = $this->effectDieSides[$idx] > 1 ? 1 : $max;

            $data['creates'] = [$this->effectCreateItemId[$idx], $min, $max];
            break;
        }

        // Profession
        if (in_array($this->typeCat, [9, 11]))
        {
            if ($la = $this->learnedAt)
                $data['learnedat'] = $la;
            else if (($la = $this->reqSkillLevel) > 1)
                $data['learnedat'] = $la;

            $data['colors'] = $this->getSkillBreakpoints();
        }

        // glyph
        if ($this->typeCat == -13)
            $data['glyphtype'] = $this->cuFlags & SPELL_CU_GLYPH_MAJOR ? 1 : 2;

        if (!$this->rank->isEmpty())
            $data['rank'] = $this->rank;

        if ($mask = $this->reqClassMask)
            $data['reqclass'] = $mask;

        if ($mask = $this->reqRaceMask)
            $data['reqrace'] = $mask;

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF, ?array &$extra = []) : array
    {
        $data  = [];

        if ($addMask & GLOBALINFO_RELATED)
        {
            // reagents
            foreach ($this->getReagents() as $iId => $_)
                $data[Type::ITEM][$iId] = $iId;

            // created items
            foreach ($this->canCreateItem() as $effIdx)
                $data[Type::ITEM][$this->effectCreateItemId[$effIdx]] = $this->effectCreateItemId[$effIdx];
        }

        if ($addMask & GLOBALINFO_RELATED)
        {
            foreach (ChrClass::fromMask($this->reqClassMask) as $cId)
                $data[Type::CHR_CLASS][$cId] = $cId;

            foreach (ChrRace::fromMask($this->reqRaceMask) as $rId)
                $data[Type::CHR_RACE][$rId] = $rId;

            // play sound effect
            for ($i = 0; $i < 3; $i++)
                if ($this->effectId[$i] == SPELL_EFFECT_PLAY_SOUND || $this->effectId[$i] == SPELL_EFFECT_PLAY_MUSIC)
                    $data[Type::SOUND][$this->effectMiscValue[$i]] = $this->effectMiscValue[$i];
        }

        if ($addMask & GLOBALINFO_SELF)
        {
            $data[Type::SPELL][$this->id] = array(
                'icon' => $this->iconAlt ?: $this->icon,
                'name' => $this->name
            );

            if (in_array($this->typeCat, [-5, -6, 9, 11]))
                $data[Type::SPELL][$this->id]['completion_category'] = $this->typeCat;
        }

        if ($addMask & GLOBALINFO_EXTRA)
        {
            $spells = $buffSpells = [];

            $buff = $this->renderBuff(MAX_LEVEL, buffSpells: $buffSpells);
            $tTip = $this->renderTooltip(MAX_LEVEL, ttSpells: $spells);

            foreach ($spells as $relId => $_)
                $data[Type::SPELL][$relId] ??= $relId;

            foreach ($buffSpells as $relId => $_)
                $data[Type::SPELL][$relId] ??= $relId;

            $extra[$this->id] = array(
             // 'id'         => $this->id,
                'tooltip'    => $tTip,
                'buff'       => $buff ?: null,
                'spells'     => $spells,
                'buffspells' => $buffSpells ?: null
            );
        }

        return $data;
    }

    public function getSourceData() : array
    {
        return array(
            'n'    => $this->name,
            't'    => Type::SPELL,
            'ti'   => $this->id,
            's'    => $this->skillLines[0] ?? 0,
            'c'    => $this->typeCat,
            'icon' => $this->iconAlt ?: $this->icon,
        );
    }

    public function renderTooltip(int $level = MAX_LEVEL, int $interactive = self::INTERACTIVE_EMBEDDED, array &$ttSpells = []) : ?string
    {
        $this->interactive = $interactive;
        $this->charLevel   = $level;

        // fetch needed texts
        $name  = $this->name;
        $rank  = $this->rank;
        $tools = $this->getTools();
        $cool  = $this->createCooldown();
        $cast  = $this->createCastTime();
        $cost  = $this->createPowerCost();
        $range = $this->createRanges();

        [$desc, $spells, ] = $this->renderText('description');

        array_walk_recursive($ttSpells, fn(&$x) => $x = UIText::format($x, Lang::FMT_HTML));

        // get reagents
        $reagents = $this->getReagents();
        foreach ($reagents as $k => [$rItemId, ])
        {
            if ($rName = Item::getName($rItemId))
                $reagents[$k] += [2 => '<a href="?item='.$rItemId.'">'.$rName.'</a>', 3 => true];
            else
                $reagents[$k] += [2 => 'Item #'.$rItemId, 3 => false];
        }

        $reagents = array_reverse($reagents);

        // get stances
        $stances = '';
        if ($this->stanceMask && !($this->attributes[2] & SPELL_ATTR2_NOT_NEED_SHAPESHIFT))
            $stances = Lang::game('requires2').' '.Lang::getStances($this->stanceMask);

        // get item requirement (skip for professions)
        $reqItems = '';
        if ($this->typeCat != 11)
        {
            $class    = $this->equippedItemClass;
            $mask     = $this->equippedItemSubClassMask;
            $reqItems = Lang::getRequiredItems($class, $mask);
        }

        // get created items (may need improvement)
        $createItem = '';
        if (in_array($this->typeCat, [9, 11]))              // only professions
        {
            foreach ($this->canCreateItem() as $idx)
            {
                // $ciEntry = new Item($this->effectCreateItemId[$idx]);
                $ciEntry = new ItemList(array(['id', $this->effectCreateItemId[$idx]]));
                if ($ciEntry->error)
                    continue;

                // should only create one item
                $createItem = $ciEntry->renderTooltip(true, $this->id);
                break;
            }
        }

        $x = '';
        $x .= '<table><tr><td>';

        // name & rank
        if ($rank)
            $x .= '<table width="100%"><tr><td><b>'.$name.'</b></td><th><b class="q0">'.$rank.'</b></th></tr></table>';
        else
            $x .= '<b>'.$name.'</b><br />';

        // powerCost & ranges
        if ($range && $cost)
            $x .= '<table width="100%"><tr><td>'.$cost.'</td><th>'.$range.'</th></tr></table>';
        else if ($cost || $range)
            $x .= $range.$cost.'<br />';

        // castTime & cooldown
        if ($cast && $cool)                                 // tabled layout
        {
            $x .= '<table width="100%">';
            $x .= '<tr><td>'.$cast.'</td><th>'.$cool.'</th></tr>';
            if ($stances)
                $x.= '<tr><td colspan="2">'.$stances.'</td></tr>';

            $x .= '</table>';
        }
        else if ($cast || $cool)                            // line-break layout
        {
            $x .= $cast.$cool;

            if ($stances)
                $x .= '<br />'.$stances;
        }

        $x .= '</td></tr></table>';

        $xTmp = [];

        if ($tools)
        {
            $_ = Lang::spell('tools').':<br/><div class="indent q1">';
            while ($tool = array_pop($tools))
            {
                if (isset($tool['itemId']))
                    $_ .= '<a href="?item='.$tool['itemId'].'">'.$tool['name'].'</a>';
                else if (isset($tool['id']))
                    $_ .= '<a href="?items&filter=cr=91;crs='.$tool['id'].';crv=0">'.$tool['name'].'</a>';
                else
                    $_ .= $tool['name'];

                $_ .= empty($tools) ? '<br />' : Lang::main('comma');
            }

            $xTmp[] = $_.'</div>';
        }

        if ($reagents)
        {
            $_  = Lang::spell('reagents').':<br/><div class="indent q1">';
            $_ .= Lang::concat($reagents, Lang::CONCAT_NONE, fn($x) => $x[1] > 1 ? Lang::main('parensFmt', [$x[2], $x[1]]) : $x[2]); // [itemId, qty, text]
            $_ .= '<br />';

            $xTmp[] = $_.'</div>';
        }

        if ($reqItems)
            $xTmp[] = Lang::game('requires2').' '.$reqItems;

        if ($desc)
            $xTmp[] = '<span class="q">'.$desc.'</span>';

        if ($createItem)
            $xTmp[] = $createItem;

        if ($xTmp)
            $x .= '<table><tr><td>'.implode('<br />', $xTmp).'</td></tr></table>';

        // scaling information - spellId:min:max:curr[:scalingDistribution:ScalingFlags]
        $scalingInfo = array(
            $this->id,
            $this->isScaling ? ($this->baseLevel ?: 1) : 1,
            $this->isScaling ? ($this->maxLevel ?: MAX_LEVEL) : 1
        );

        if ($this->attributes[0] & SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION)
        {
            $scalingInfo[] = $this->spellLevel ?: 1;
            $scalingInfo[] = 1;                             // in 4.x+ proper scaling information; for us just to flag a npc spell as level damage scaling
            $scalingInfo[] = 1;
        }
        else
            $scalingInfo[] = min($this->charLevel, $scalingInfo[2]);

        $x .= '<!--?'.implode(':', $scalingInfo).'-->';

        return $x;
    }

    public function renderBuff(int $level = MAX_LEVEL, int $interactive = self::INTERACTIVE_EMBEDDED, array &$buffSpells = []) : ?string
    {
        // doesn't have a buff
        if ($this->buff->isEmpty())
            return null;

        $this->interactive = $interactive;
        $this->charLevel   = $level;

        $x = '<table><tr>';

        // spellName
        $x .= '<td><b class="q">'.$this->name.'</b></td>';

        // dispelType (if applicable)
        if ($this->dispelType)
            if ($dispel = Lang::game('dt', $this->dispelType))
                $x .= '<th><b class="q">'.$dispel.'</b></th>';

        $x .= '</tr></table>';

        $x .= '<table><tr><td>';

        // parse Buff-Text
        [$buffTT, $buffSpells, ] = $this->renderText('buff');

        array_walk_recursive($buffSpells, fn(&$x) => $x = UIText::format($x, Lang::FMT_HTML));

        $x .= $buffTT.'<br />';

        // duration
        if ($this->duration > 0 && !($this->attributes[5] & SPELL_ATTR5_HIDE_DURATION))
            $x .= '<span class="q">'.Lang::formatTime($this->duration, 'spell', 'timeRemaining').'<span>';

        $x .= '</td></tr></table>';

        $min = $this->isScaling ? ($this->baseLevel ?: 1) : 1;
        $max = $this->isScaling ? MAX_LEVEL : 1;
        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':'.$min.':'.$max.':'.min($this->charLevel, $max).'-->';

        return $x;
    }

    // required for item-comparison
    public function getStatGain() : StatsContainer
    {
        $data = new StatsContainer();

        foreach ($this->canEnchantmentItem() as $i)
            $data->fromDB(Type::ENCHANTMENT, $this->effectMiscValue[$i]);

        // todo: should enchantments be included here...?
        $data->fromSpell($this->curTpl);

        return $data;
    }

    public function getProfilerMods() : array
    {
        // weapon hand check: param: slot, class, subclass, value
        $whCheck = '$function() { var j, w = _inventory.getInventory()[%d]; if (!w[0] || !g_items[w[0]]) { return 0; } j = g_items[w[0]].jsonequip; return (j.classs == %d && (%d & (1 << (j.subclass)))) ? %d : 0; }';

        $data = [];                                         // flat gains
        foreach ($this->getStatGain() as $spellData)
        {
            $data = $spellData->toJson(STAT::FLAG_ITEM | STAT::FLAG_PROFILER, false);

            // apply weapon restrictions
            $class    = $this->equippedItemClass;
            $subClass = $this->equippedItemSubClassMask;
            $slot     = $subClass & 0x5000C ? 18 : 16;
            if ($class != ITEM_CLASS_WEAPON || !$subClass)
                continue;

            foreach ($data as $key => $amt)
                $data[$key] = [1, 'functionOf', sprintf($whCheck, $slot, $class, $subClass, $amt)];
        }

        // 4 possible modifiers found
        // <statistic> => [0.15, 'functionOf', <funcName:int>]
        // <statistic> => [0.33, 'percentOf', <statistic>]
        // <statistic> => [123, 'add']
        // <statistic> => <value>  ...  as from getStatGain()

        $modXByStat = function (array &$arr, int $srcStat, ?string $destStat, int $pts) : void
        {
            match ($srcStat)
            {
                STAT_STRENGTH  => $arr[$destStat ?: 'str'] = [$pts / 100, 'percentOf', 'str'],
                STAT_AGILITY   => $arr[$destStat ?: 'agi'] = [$pts / 100, 'percentOf', 'agi'],
                STAT_STAMINA   => $arr[$destStat ?: 'sta'] = [$pts / 100, 'percentOf', 'sta'],
                STAT_INTELLECT => $arr[$destStat ?: 'int'] = [$pts / 100, 'percentOf', 'int'],
                STAT_SPIRIT    => $arr[$destStat ?: 'spi'] = [$pts / 100, 'percentOf', 'spi']
            };
        };

        $modXBySchool = function (array &$arr, int $srcStat, string $destStat, array|int $val) : void
        {
            if ($srcStat & (1 << SPELL_SCHOOL_HOLY))
                $arr['hol'.$destStat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'hol'.$destStat];
            if ($srcStat & (1 << SPELL_SCHOOL_FIRE))
                $arr['fir'.$destStat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'fir'.$destStat];
            if ($srcStat & (1 << SPELL_SCHOOL_NATURE))
                $arr['nat'.$destStat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'nat'.$destStat];
            if ($srcStat & (1 << SPELL_SCHOOL_FROST))
                $arr['fro'.$destStat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'fro'.$destStat];
            if ($srcStat & (1 << SPELL_SCHOOL_SHADOW))
                $arr['sha'.$destStat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'sha'.$destStat];
            if ($srcStat & (1 << SPELL_SCHOOL_ARCANE))
                $arr['arc'.$destStat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'arc'.$destStat];
        };

        $jsonStat = function (int $statId) : string
        {
            return match ($statId)
            {
                STAT_STRENGTH  => Stat::getJsonString(Stat::STRENGTH),
                STAT_AGILITY   => Stat::getJsonString(Stat::AGILITY),
                STAT_STAMINA   => Stat::getJsonString(Stat::STAMINA),
                STAT_INTELLECT => Stat::getJsonString(Stat::INTELLECT),
                STAT_SPIRIT    => Stat::getJsonString(Stat::SPIRIT)
            };
        };

        // Shaman - Spirit Weapons (16268) (parry is normaly stored in g_statistics)
        // i should recurse into SPELL_EFFECT_LEARN_SPELL and apply SPELL_EFFECT_PARRY from there
        if ($this->id == 16268)
        {
            $data['parrypct'] = [5, 'add'];
            return $data;
        }

        if (!($this->attributes[0] & SPELL_ATTR0_PASSIVE))
            return $data;

        for ($i = 0; $i < 3; $i++)
        {
            $pts      = $this->calculateAmount($i)[1];
            $mv       = $this->effectMiscValue[$i];
            $mvB      = $this->effectMiscValueB[$i];
            $au       = $this->effectAuraId[$i];
            $class    = $this->equippedItemClass;
            $subClass = $this->equippedItemSubClassMask;


            /*  ISSUE!
                mods formated like ['<statName>' => [<points>, 'percentOf', '<statName>']] are applied as multiplier and not
                as a flat value (that is equal to the percentage, like they should be). So the stats-table won't show the actual deficit
            */

            switch ($au)
            {
                case SPELL_AURA_MOD_RESISTANCE_PCT:
                case SPELL_AURA_MOD_BASE_RESISTANCE_PCT:
                    // Armor only if explicitly specified only affects armor from equippment
                    if ($mv == (1 << SPELL_SCHOOL_NORMAL))
                        $data['armor'] = [$pts / 100, 'percentOf', ['armor', 0]];
                    else if ($mv)
                        $modXBySchool($data, $mv, 'res', $pts);
                    break;
                case SPELL_AURA_MOD_RESISTANCE_OF_STAT_PERCENT:
                    // Armor only if explicitly specified
                    if ($mv == (1 << SPELL_SCHOOL_NORMAL))
                        $data['armor'] = [$pts / 100, 'percentOf', $jsonStat($mvB)];
                    else if ($mv)
                        $modXBySchool($data, $mv, 'res', [$pts / 100, 'percentOf', $jsonStat($mvB)]);
                    break;
                case SPELL_AURA_MOD_TOTAL_STAT_PERCENTAGE:
                    if ($mv > -1)                       // one stat
                        $modXByStat($data, $mv, null, $pts);
                    else if ($mv < 0)                   // all stats
                        for ($iMod = ITEM_MOD_AGILITY; $iMod <= ITEM_MOD_STAMINA; $iMod++)
                            if ($idx = Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $iMod))
                                if ($key = Stat::getJsonString($idx))
                                    $data[$key] = [$pts / 100, 'percentOf', $key];
                    break;
                case SPELL_AURA_MOD_SPELL_DAMAGE_OF_STAT_PERCENT:
                    $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                    $modXBySchool($data, $mv, 'spldmg', [$pts / 100, 'percentOf', $jsonStat($mvB)]);
                    break;
                case SPELL_AURA_MOD_RANGED_ATTACK_POWER_OF_STAT_PERCENT:
                    $modXByStat($data, $mv, 'rgdatkpwr', $pts);
                    break;
                case SPELL_AURA_MOD_ATTACK_POWER_OF_STAT_PERCENT:
                    $modXByStat($data, $mv, 'mleatkpwr', $pts);
                    break;
                case SPELL_AURA_MOD_SPELL_HEALING_OF_STAT_PERCENT:
                    $modXByStat($data, $mv, 'splheal', $pts);
                    break;
                case SPELL_AURA_MOD_MANA_REGEN_FROM_STAT:
                    $modXByStat($data, $mv, 'manargn', $pts);
                    break;
                case SPELL_AURA_MOD_MANA_REGEN_INTERRUPT:
                    $data['icmanargn'] = [$pts / 100, 'percentOf', 'oocmanargn'];
                    break;
                case SPELL_AURA_MOD_SPELL_CRIT_CHANCE:
                case SPELL_AURA_MOD_SPELL_CRIT_CHANCE_SCHOOL:
                    $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                    $modXBySchool($data, $mv, 'splcritstrkpct', [$pts, 'add']);
                    if (($mv & SPELL_MAGIC_SCHOOLS) == SPELL_MAGIC_SCHOOLS)
                        $data['splcritstrkpct'] = [$pts, 'add'];
                    break;
                case SPELL_AURA_MOD_ATTACK_POWER_OF_ARMOR:
                    $data['mleatkpwr'] = [1 / $pts, 'percentOf', 'fullarmor'];
                    $data['rgdatkpwr'] = [1 / $pts, 'percentOf', 'fullarmor'];
                    break;
                case SPELL_AURA_MOD_WEAPON_CRIT_PERCENT:
                    if ($class < 1 || ($class == ITEM_CLASS_WEAPON && ($subClass & 0x5000C)))
                        $data['rgdcritstrkpct'] = [1, 'functionOf', sprintf($whCheck, 18, $class, $subClass, $pts)];
                        // $data['rgdcritstrkpct'] = [$pts, 'add'];
                    if ($class < 1 || ($class == ITEM_CLASS_WEAPON && ($subClass & 0xA5F3)))
                        $data['mlecritstrkpct'] = [1, 'functionOf', sprintf($whCheck, 16, $class, $subClass, $pts)];
                        // $data['mlecritstrkpct'] = [$pts, 'add'];
                    break;
                case SPELL_AURA_MOD_PARRY_PERCENT:
                    $data['parrypct'] = [$pts, 'add'];
                    break;
                case SPELL_AURA_MOD_DODGE_PERCENT:
                    $data['dodgepct'] = [$pts, 'add'];
                    break;
                case SPELL_AURA_MOD_BLOCK_PERCENT:
                    $data['blockpct'] = [$pts, 'add'];
                    break;
                case SPELL_AURA_MOD_INCREASE_ENERGY_PERCENT:
                    if ($mv == POWER_HEALTH)
                        $data['health'] = [$pts / 100, 'percentOf', 'health'];
                    else if ($mv == POWER_ENERGY)
                        $data['energy'] = [$pts / 100, 'percentOf', 'energy'];
                    else if ($mv == POWER_MANA)
                        $data['mana']   = [$pts / 100, 'percentOf', 'mana'];
                    else if ($mv == POWER_RAGE)
                        $data['rage']   = [$pts / 100, 'percentOf', 'rage'];
                    else if ($mv == POWER_RUNIC_POWER)
                        $data['runic']  = [$pts / 100, 'percentOf', 'runic'];
                    break;
                case SPELL_AURA_MOD_INCREASE_HEALTH_PERCENT:
                    $data['health'] = [$pts / 100, 'percentOf', 'health'];
                    break;
                case SPELL_AURA_MOD_BASE_HEALTH_PCT:    // only Tauren - Endurance (20550) ... if you are looking for something elegant, look away!
                    $data['health'] = [$pts / 100, 'functionOf', '$(x) => g_statistics.combo[x.classs][x.level][5]'];
                    break;
                case SPELL_AURA_MOD_SHIELD_BLOCKVALUE_PCT:
                    $data['block'] = [$pts / 100, 'percentOf', 'block'];
                    break;
                case SPELL_AURA_MOD_CRIT_PCT:
                    $data['mlecritstrkpct'] = [$pts, 'add'];
                    $data['rgdcritstrkpct'] = [$pts, 'add'];
                    $data['splcritstrkpct'] = [$pts, 'add'];
                    break;
                case SPELL_AURA_MOD_SPELL_DAMAGE_OF_ATTACK_POWER:
                    $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                    $modXBySchool($data, $mv, 'spldmg', [$pts / 100, 'percentOf', 'mleatkpwr']);
                    break;
                case SPELL_AURA_MOD_SPELL_HEALING_OF_ATTACK_POWER:
                    $data['splheal'] = [$pts / 100, 'percentOf', 'mleatkpwr'];
                    break;
                case SPELL_AURA_MOD_ATTACK_POWER_PCT:   // ingame only melee..?
                    $data['mleatkpwr'] = [$pts / 100, 'percentOf', 'mleatkpwr'];
                    break;
                case SPELL_AURA_MOD_HEALTH_REGEN_PERCENT:
                    $data['healthrgn'] = [$pts / 100, 'percentOf', 'healthrgn'];
                    break;
            }
        }

        return $data;
    }

    public function getReagents() : array
    {
        $data = [];

        for ($i = 0; $i < 8; $i++)
            if ($this->reagent[$i] > 0 && $this->reagentCount[$i])
                $data[$this->reagent[$i]] = [$this->reagent[$i], $this->reagentCount[$i]];

        return $data;
    }

    // should not be called more than once (result was previously cached)
    public function getTools() : array
    {
        $tools = [];

        for ($i = 0; $i < 2; $i++)
        {
            // TotemCategory
            if ($_ = $this->toolCategory[$i])
            {
                $tc = DB::Aowow()->selectRow('SELECT * FROM ::totemcategory WHERE `id` = %i', $_);
                $tools[$i + 1] = array(
                    'id'   => $_,
                    'name' => Util::localizedString($tc, 'name'));
            }

            // Tools
            if (!$this->tool[$i])
                continue;

            $name = ItemList::getName($this->tool[$i], $quality);

            $tools[$i - 1] = array(
                'itemId'  => $this->tool[$i],
                'name'    => $name ?: 'Item #'.$this->tool[$i],
                'quality' => $name ?  $quality : '@'
            );
        }

        return array_reverse($tools);
    }

    public function getModelInfo(?array $ssfRef = null, ?Creature $npcRef = null, ?GameObjectList $objectRef = null) : array
    {
        // try NPC model from MiscVal
        if ($_ = $this->getFirstCreatureMorphEntry())
        {
            $npcRef ??= new Creature($_);
            if (!$npcRef->error)
            {
                $res = array(
                    'type'        => Type::NPC,
                    'typeId'      => $_,
                    'displayId'   => $npcRef->getRandomDisplayId(),
                    'displayName' => $npcRef->name
                );

                if ($npcRef->humanoid)
                    $res['humanoid'] = 1;

                return $res;
            }
        }

        // try GO model from MiscVal
        if ($_ = $this->getFirstObjectMorphEntry())
        {
            $objectRef ??= new GameObjectList(array(['id', $_]));
            if ($objectRef->getEntry($_))
            {
                return array(
                    'type'        => Type::OBJECT,
                    'typeId'      => $_,
                    'displayId'   => $objectRef->getField('displayId'),
                    'displayName' => $objectRef->getField('name', true)
                );
            }
        }

        // try Shapeshiftform
        for ($i = 0; $i < 3; $i++)
        {
            if ($this->effectMiscValue[$i] <= 0)
                continue;

            if ($this->effectAuraId[$i] != SPELL_AURA_MOD_SHAPESHIFT)
                continue;

            $subForms = array(
                892  => [892,  29407, 29406, 29408, 29405],         // Cat - NE
                8571 => [8571, 29410, 29411, 29412],                // Cat - Tauren
                2281 => [2281, 29413, 29414, 29416, 29417],         // Bear - NE
                2289 => [2289, 29415, 29418, 29419, 29420, 29421]   // Bear - Tauren
            );

            $ssfRef ??= DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `creatureType`, `displayIdA`, `displayIdH` FROM ::shapeshiftforms');

            if ($ssf = ($ssfRef[$this->effectMiscValue[$i]] ?? null))
            {
                $models = [];
                foreach (['A', 'H'] as $j)
                {
                    $sub = $subForms[$ssf['displayId'.$j]] ?? [$ssf['displayId'.$j]];
                    $models[] = $sub[rand(0, count($sub) - 1)];
                }

                return array(
                    'type'         => Type::NPC,
                    'creatureType' => $ssf['creatureType'],
                    'displayId'    => $models[rand(0, count($models) - 1)],
                    'displayName'  => Lang::game('st', $this->effectMiscValue[$i])
                );
            }
        }

        return [];
    }

    private function createRanges() : string
    {
        if (!$this->rangeMaxHostile)
            return '';

        if ($this->attributes[3] & SPELL_ATTR3_DONT_DISPLAY_RANGE)
            return '';

        // hardcode: "melee range"
        if ($this->rangeMaxHostile == 5)
            return Lang::spell('meleeRange');

        // hardcode "unlimited range"
        if ($this->rangeMaxHostile == 50000)
            return Lang::spell('unlimRange');

        // minRange exists; show as range
        if ($this->rangeMinHostile)
            return Lang::spell('range', [$this->rangeMinHostile.' - '.$this->rangeMaxHostile]);

        // friend and hostile differ; do color
        if ($this->rangeMaxHostile != $this->rangeMaxFriend)
            return Lang::spell('range', ['<span class="q10">'.$this->rangeMaxHostile.'</span> - <span class="q2">'.$this->rangeMaxFriend. '</span>']);

        // regular case
        return Lang::spell('range', [$this->rangeMaxHostile]);
    }

    public function createPowerCost() : string
    {
        $str = '';

        $pt   = $this->powerType;
        $pc   = $this->powerCost;
        $pcp  = $this->powerCostPercent;
        $pps  = $this->powerPerSecond;
        $pcpl = $this->powerCostPerLevel;

        // some potion effects have this set, but it's not displayed by client (or enforced by core)
        if ($pt == POWER_HAPPINESS)
            return '';

        if ($pt == POWER_RAGE || $pt == POWER_RUNIC_POWER)
            $pc /= 10;

        if ($pt == POWER_RUNE && ($rCost = ($this->powerCostRunes & 0x333)))
        {   // Blood 2|1 - Unholy 2|1 - Frost 2|1
            $runes = [];

            for ($i = 0; $i < 3; $i++)
            {
                if ($rCost & 0x3)
                    $runes[] = Lang::spell('powerCostRunes', $i, [$rCost & 0x3]);

                $rCost >>= 4;
            }

            $str .= implode(' ', $runes);
        }
        else if ($pcp > 0)                                  // power cost: pct over static
            $str .= $pcp."% ".Lang::spell('pctCostOf', [mb_strtolower(Lang::spell('powerTypes', $pt))]);
        else if ($pc > 0 || $pps > 0 || $pcpl > 0)
        {
            if ($this->attributes[0] & SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION)
                $str .= '<!--pts'.$this->baseLevel.':'.$pc.'-->';

            if (Lang::exist('spell', 'powerCost', $pt))
                $str .= Lang::spell('powerCost', $pt,   intVal($pps > 0), [$pc, $pps]);
            else
                $str .= Lang::spell('powerDisplayCost', intVal($pps > 0), [$pc, Lang::spell('powerTypes', $pt), $pps]);
        }

        // append level cost (todo (low): work in as scaling cost)
        if ($pcpl > 0)
            $str .= Lang::spell('costPerLevel', [$pcpl]);

        return $str;
    }

    public function createCastTime(bool $short = true, bool $noInstant = true) : string
    {
        if (!$this->castTime && $this->isChanneledSpell())
            return Lang::spell('channeled');

        // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only (todo (low): rule is imperfect)
        if (!$this->castTime && ($this->damageClass != SPELL_DAMAGE_CLASS_MAGIC || $this->attributes[0] & SPELL_ATTR0_ABILITY))
            return Lang::spell('instantPhys');

        // show instant only for player/pet/npc abilities (todo (low): unsure when really hidden (like talent-case))
        if ($noInstant && !in_array($this->typeCat, [11, 7, -3, -6, -8, 0]) && !($this->cuFlags & SPELL_CU_TALENTSPELL))
            return '';

        if ($short)
            return Lang::formatTime($this->castTime * 1000, 'spell', 'castTime');

        return DateTime::formatTimeElapsedFloat($this->castTime * 1000);
    }

    private function createCooldown() : string
    {
        if ($this->attributes[6] & SPELL_ATTR6_DONT_DISPLAY_COOLDOWN)
            return '';

        if ($this->recoveryTime)
            return Lang::formatTime($this->recoveryTime, 'spell', 'cooldown');

        if ($this->recoveryCategory)
            return Lang::formatTime($this->recoveryCategory, 'spell', 'cooldown');

        return '';
    }

    // formulae base from TC
    private function calculateAmount(int $effIdx, int $nTicks = 1) : array
    {
        $level    = $this->charLevel;
        $maxBase  = 0;
        $rppl     = $this->effectRealPointsPerLevel[$effIdx];
        $base     = $this->effectBasePoints[$effIdx];
        $add      = $this->effectDieSides[$effIdx];
        $maxLvl   = $this->maxLevel;
        $baseLvl  = $this->baseLevel;
        $spellLvl = $this->spellLevel;
        $LDSEffs  = $this->canLevelDamageScale();
        $modMin   =
        $modMax   = null;

        if ($rppl)
        {
            if ($level > $maxLvl && $maxLvl > 0)
                $level = $maxLvl;
            else if ($level < $baseLvl)
                $level = $baseLvl;

            if (!$this->attributes[0] & SPELL_ATTR0_PASSIVE)
                $level -= $spellLvl;

            $maxBase += (int)(($level - $baseLvl) * $rppl);
            $maxBase *= $nTicks;
        }

        $min = $nTicks * ($add ? $base + 1 : $base);
        $max = $nTicks * ($add + $base);

        if ($rppl)
        {
            $modMin = '<!--ppl'.$baseLvl.':'.($maxLvl ?: $level).':'.$min.':'.($rppl * 100 * $nTicks).'-->';
            $modMax = '<!--ppl'.$baseLvl.':'.($maxLvl ?: $level).':'.$max.':'.($rppl * 100 * $nTicks).'-->';
        }
        else if ($this->attributes[0] & SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION && in_array($effIdx, $LDSEffs) && $spellLvl)
        {
            $modMin = '<!--pts'.$spellLvl.':'.abs($min).'-->';
            $modMax = '<!--pts'.$spellLvl.':'.abs($max).'-->';
        }

        return [$min + $maxBase, $max + $maxBase, $modMin, $modMax];
    }

    public function getTalentHead() : string
    {
        // power cost: pct over static
        $cost = $this->createPowerCost();

        // ranges
        $range = $this->createRanges();

        // cast times
        $cast = $this->createCastTime();

        // cooldown or categorycooldown
        $cool = $this->createCooldown();

        // assemble parts
        // upper: cost :: range
        // lower: time :: cooldown
        $x = '';

        // upper
        if ($cost && $range)
            $x .= '<table width="100%"><tr><td>'.$cost.'</td><th>'.$range.'</th></tr></table>';
        else
            $x .= $cost.$range;

        if (($cost xor $range) && ($cast xor $cool))
            $x .= '<br />';

        // lower
        if ($cast && $cool)
            $x .= '<table width="100%"><tr><td>'.$cast.'</td><th>'.$cool.'</th></tr></table>';
        else
            $x .= $cast.$cool;

        return $x;
    }

    public function getSkillBreakpoints() : array
    {
        $gry = $this->skillLevelGrey;
        $ylw = $this->skillLevelYellow;
        $grn = (int)(($ylw + $gry) / 2);
        $org = $this->learnedAt;

        if ($ylw < $org)
            $ylw = 0;

        if ($grn < $org || $grn < $ylw)
            $grn = 0;

        if ($org >= $ylw || $org >= $grn || $org >= $gry)
            $org = 0;

        return $gry > 1 ? [$org, $ylw, $grn, $gry] : [];
    }

    // mostly similar to TC
    public function getCastingTimeForBonus(bool $asDOT = false) : int
    {
        $areaTargets = [7, 8, 15, 16, 20, 24, 30, 31, 33, 34, 37, 54, 56, 59, 104, 108];
        $castingTime = $this->IsChanneledSpell() ? $this->duration : ($this->castTime * 1000);

        if (!$castingTime)
            return 3500;

        $castingTime = clamp($castingTime, 1500, 7000);

        if ($asDOT && !$this->isChanneledSpell())
            $castingTime = 3500;

        $overTime = 0;
        $nEffects = 0;
        $isDirect = false;
        $isArea   = false;

        for ($i = 0; $i < 3; $i++)
        {
            if (in_array($this->effectId[$i], Spell::EFFECTS_DIRECT_SCALING))
                $isDirect = true;
            else if (in_array($this->effectAuraId[$i], Spell::AURAS_PERIODIC_SCALING))
            {
                if ($_ = $this->duration)
                    $overTime = $_;
            }
            else if ($this->effectAuraId[$i])
                $nEffects++;

            if (in_array($this->effectImplicitTargetA[$i], $areaTargets) || in_array($this->effectImplicitTargetB[$i], $areaTargets))
                $isArea = true;
        }

        // Combined Spells with Both Over Time and Direct Damage
        if ($overTime > 0 && $castingTime > 0 && $isDirect)
        {
            // mainly for DoTs which are 3500 here otherwise
            $originalCastTime = $this->castTime * 1000;
            if ($this->attributes[0] & SPELL_ATTR0_REQ_AMMO)
                $originalCastTime += 500;

            $originalCastTime = clamp($originalCastTime, 1500, 7000);

            // Portion to Over Time
            $PtOT = ($overTime / 15000) / (($overTime / 15000) + ($originalCastTime / 3500));

            if ($asDOT)
                $castingTime = $castingTime * $PtOT;
            else if ($PtOT < 1)
                $castingTime = $castingTime * (1 - $PtOT);
            else
                $castingTime = 0;
        }

        // Area Effect Spells receive only half of bonus
        if ($isArea)
            $castingTime /= 2;

        // -5% of total per any additional effect
        $castingTime -= ($nEffects * 175);
        if ($castingTime < 0)
            $castingTime = 0;

        return $castingTime;
    }

    // at most one model per spell expected
    public function getFirstObjectMorphEntry() : ?int
    {
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_MODEL_OBJECT))
                if ($this->effectMiscValue[$i] > 0)
                    return $this->effectMiscValue[$i];

        return null;
    }

    // at most one model per spell expected
    public function getFirstCreatureMorphEntry() : ?int
    {
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_MODEL_NPC) || in_array($this->effectAuraId[$i], Spell::AURAS_MODEL_NPC))
                if ($this->effectMiscValue[$i] > 0)
                    return $this->effectMiscValue[$i];

        return null;
    }

    public function canCreateItem() : array
    {
        $idx = [];
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_ITEM_CREATE) || in_array($this->effectAuraId[$i], Spell::AURAS_ITEM_CREATE))
                if ($this->effectCreateItemId[$i] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function canTriggerSpell() : array
    {
        $idx = [];
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_TRIGGER) || in_array($this->effectAuraId[$i], Spell::AURAS_TRIGGER))
                if ($this->effectAuraId[$i] == SPELL_AURA_DUMMY || $this->effectTriggerSpell[$i] > 0 || ($this->effectId[$i] == SPELL_EFFECT_TITAN_GRIP && $this->effectMiscValue[$i] > 0))
                    $idx[] = $i;

        return $idx;
    }

    public function canTeachSpell() : array
    {
        $idx = [];
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_TEACH))
                if ($this->effectTriggerSpell[$i] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function canEnchantmentItem() : array
    {
        $idx = [];
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_ENCHANTMENT))
                $idx[] = $i;

        return $idx;
    }

    public function canLevelDamageScale() : array
    {
        $idx = [];
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_LDC_SCALING) || in_array($this->effectAuraId[$i], Spell::AURAS_LDC_SCALING))
                $idx[] = $i;

        return $idx;
    }

    public function isChanneledSpell() : bool
    {
        return $this->attributes[1] & (SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2);
    }

    public function isScalableHealingSpell() : bool
    {
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_SCALING_HEAL) || in_array($this->effectAuraId[$i], Spell::AURAS_SCALING_HEAL))
                return true;

        return false;
    }

    public function isScalableDamagingSpell() : bool
    {
        for ($i = 0; $i < 3; $i++)
            if (in_array($this->effectId[$i], Spell::EFFECTS_SCALING_DAMAGE) || in_array($this->effectAuraId[$i], Spell::AURAS_SCALING_DAMAGE))
                return true;

        return false;
    }

    public function periodicEffectsMask() : int
    {
        $effMask = 0x0;

        for ($i = 0; $i < 3; $i++)
            if ($this->effectPeriode[$i] > 0)
                $effMask |= 1 << ($i - 1);

        return $effMask;
    }


    /**********************/
    /* SpellText renderer */
    /**********************/

    // oooo..kaaayy.. parsing text in 6 or 7 easy steps
    public function renderText(string $property = 'description', int $level = MAX_LEVEL, ?int $interactive = null) : array
    {
        /*
            documentation .. sort of .. updated with https://docs.google.com/spreadsheets/d/1B8clv9zgnXij_spRhRArj7MdRotl0QpD3AjcQ-CCPFA
            generally everything is case insensitive (unless stated otherwise)

            precedence:
                ${}.x - formulas; .x is optional; x:[0-9] .. max-precision of a floatpoint-result; default: 0
                ()    - regular use for function-like calls
                $<>   - variables
                $?[]  - conditionals ... like $?condition[true][false];

            operations:
                $abs(a)            -
                $ceil(a)           -
                $floor(a)          -
                $min(a, b)         -
                $max(a, b)         -
                $gt(a, b)          - a > b
                $lt(a, b)          - a < b
                $gte(a, b)         - a >= b
                $lte(a, b)         - a <= b
                $eq(a, b)          - a == b
                $cond(a, b, c)     - a ? b : c
                $clamp(a, low, hi) -

            variables: (rounding... >5 up?)
                $str        - Strength Attribute
                $agi        - Agility Attribute
                $sta        - Stamina Attribute
                $int        - Intellect Attribute
                $spi        - Spirit Attribute
                $m|M[1-3]   - BasePoints + (m:1 / M:DieSides) (updated with spell mods)
                $a[1-3]     - Spell_C_GetSpellRadius
                $d          - Spell_C_GetSpellDuration; appended timeShorthand; interpret "0" as "until canceled"
                $c          - Spell_C_GetManaCost
                $p          - Spell_C_GetManaCostPerSecond
                $x[1-3]     - m_effectChainTargets; (aka MaxAffectedTargets)
                $t[1-3]     - m_effectAuraPeriod
                $h          - m_procChance
                $n          - m_procCharges
                $b[1-3]     - m_effectPointsPerCombo
                $u          - m_cumulativeAura; (aka StackAmount)
                $v          - m_maxTargetLevel
                $e[1-3]     - m_effectAmplitude; (aka EffectValueMultiplier)
                $i          - m_maxTargets; (aka MaxAffectedTargets)
                $f[1-3]     - m_effectChainAmplitude; (aka DamageMultiplier)
                $q[1-3]     - m_effectMiscValue
                $ap / $AP   - atkpwr (base / total) (UNIT_FIELD_ATTACK_POWER / += UNIT_FIELD_ATTACK_POWER_MODS[0] + UNIT_FIELD_ATTACK_POWER_MODS[1] * (UNIT_FIELD_ATTACK_POWER_MULTIPLIER + 1.0))
                $rap / $RAP - rngatkpwr (base / total) (UNIT_FIELD_RANGED_ATTACK_POWER / += UNIT_FIELD_RANGED_ATTACK_POWER_MODS[0] + UNIT_FIELD_RANGED_ATTACK_POWER_MODS[1] * (UNIT_FIELD_RANGED_ATTACK_POWER_MULTIPLIER + 1.0))
                $mwb / $MWB - raw equipped mainhand weapon damage (min / max)
                $owb / $OWB - raw equipped offhand weapon damage (min / max)
                $rwb / $RWB - raw equipped ranged weapon damage (min / max)
                $mw / $MW   - effective mainhand weapon damage (min / max) (as per UNIT_FIELD_*)
                $ow / $OW   - effective offhand weapon damage (min / max) (as per UNIT_FIELD_*)
                $rw / $RW   - effective ranged weapon damage (min / max) (as per UNIT_FIELD_*)
                $ar         - armor value
                $mws        - effective mainhand weapon swing time (as per UNIT_FIELD_*)
                $ows        - effective offhand weapon swing time (as per UNIT_FIELD_*)
                $rws        - effective ranged weapon swing time (as per UNIT_FIELD_*)
                $pl         - PlayerLevel (UNIT_FIELD_LEVEL)
                $hnd        - CGUnit_C__IsTwoHanded ? 2.0 : 1.0;
                $sp         - GetModDamageDonePos
                $sph        - > Holy
                $spfi       - > Fire
                $spn        - > Nature
                $spfr       - > Frost
                $sps        - > Shadow
                $spa        - > Arcane
                $bh         - Bonus Healing
                $ph         - GetModDamageDonePct PHYSICAL + HOLY (as float; tested as broken?)
                $pfi        - > + FIRE (as float; tested as broken?)
                $pn         - > + NATURE (as float; tested as broken?)
                $pfr        - > + FROST (as float; tested as broken?)
                $ps         - > + SHADOW (as float; tested as broken?)
                $pa         - > + ARCANE (as float; tested as broken?)
                $pbh        - %-HealingBonus (as float; tested as broken?)
                $pbhd       - %-Healing Done (as float; tested as broken?)
                $bc[1-3]    - m_effectBonusCoefficient; (aka BonusMultiplier)

            replacements
                $g          - QuestParserGenderConditional $Gmale:female;
                $l          - SpellParserPluralConditional LastValue-Switch; last value as condition $ltrue:false;
                $o[1-3]     - TotalAmount (for periodic auras)
                $s[1-3]     - BasePoints + DieSides (static value); (display as Range, if applicable?)
                $r[1-3]     - SpellRange (hostile)
                $z          - GetHomebindAreaId

            not mentioned in google sheet but still present
                $PlayerName - <caster name>

            deviations from standard procedures
                division    - example: $/10;2687s1 => $2687s1/10
                            - also:    $61829/5;s1 => $61829s1/5
        */

        // required by meta tag generator: page wants interactive elements, but meta tags shouldn't contain interactive strings. Both are sourced from the same spell instance
        if (is_int($interactive))
            $this->interactive = $interactive;

        $this->charLevel = $level;

        // step -1: already handled?
        if (isset($this->parsedText[$property][Lang::getLocale()->value][$this->charLevel][$this->interactive]))
            return $this->parsedText[$property][Lang::getLocale()->value][$this->charLevel][$this->interactive];

        // step 0: get text
        $data = match($property)
        {
            'name',
            'buff',
            'description' => (string)$this->$property,
            default       => null
        };

        if (!isset($this->$property) || !is_a($this->{$property}, LocString::class) || $this->{$property}->isEmpty())
            return ['', [], false];

        $data = (string)$this->{$property};

        // step 1: if the text is supplemented with text-variables, get and replace them
        if ($this->prepareDescriptionVariables())
            foreach ($this->descriptionVariables as $k => $sv)
                $data = str_replace('$<'.$k.'>', $sv, $data);

        // step 2: resolving conditions
        // aura- or spell-conditions cant be resolved for our purposes, so force them to false for now (todo (low): strg+f "know" in aowowPower.js ^.^)

        $relSpells = [];
        $data = $this->handleConditions($data, $relSpells, true);

        // step 3: unpack formulas ${ .. }.X
        $data = $this->handleFormulas($data, true);

        // step 4: find and eliminate regular variables
        $data = $this->handleVariables($data, true);

        // step 5: variable-dependent variable-text
        // special case $lONE:ELSE[:ELSE2]; or $|ONE:ELSE[:ELSE2];
        while (preg_match('/([\d\.]+)([^\d]*)(\$[l|]:*)([^:]*):([^;]*);/i', $data, $m))
        {
            $plurals = explode(':', $m[5]);
            $replace = '';

            if (count($plurals) == 2)                       // special case: ruRU
            {
                switch (substr($m[1], -1))                  // check last digit of number
                {
                    case 1:
                        // but not 11 (teen number)
                        if (!in_array($m[1], [11]))
                        {
                            $replace = $m[4];
                            break;
                        }
                    case 2:
                    case 3:
                    case 4:
                        // but not 12, 13, 14 (teen number) [11 is passthrough]
                        if (!in_array($m[1], [11, 12, 13, 14]))
                        {
                            $replace = $plurals[0];
                            break;
                        }
                        break;
                    default:
                        $replace = $plurals[1];
                }

            }
            else
                $replace = ($m[1] == 1 ? $m[4] : $plurals[0]);

            $data = str_ireplace($m[1].$m[2].$m[3].$m[4].':'.$m[5].';', $m[1].$m[2].$replace, $data);
        }

        // step 6: HTMLize
        // colors
        $data = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $data);

        // line endings
        $data = strtr($data, ["\r" => '', "\n" => '<br />']);

        // cache result
        $this->parsedText[$property][Lang::getLocale()->value][$this->charLevel][$this->interactive] = [$data, $relSpells, $this->isScaling];

        return [$data, $relSpells, $this->isScaling];
    }

    private function dfnText(string $tooltip, string $text) : string
    {
        if ($this->interactive < self::INTERACTIVE_EMBEDDED)
            return $text;

        return sprintf(Util::$dfnString, $tooltip, $text);
    }

    private function prepareDescriptionVariables() : bool
    {
        if ($this->spellDescriptionVariableId <= 0)
            return false;

        if ($this->descriptionVariables)
            return true;

        $spellVars = DB::Aowow()->SelectCell('SELECT `vars` FROM ::spellvariables WHERE `id` = %i', $this->spellDescriptionVariableId);
        foreach (explode("\n", $spellVars) as $sv)
            if (preg_match('/\$(\w*\d*)=(.*)/i', trim($sv), $m))
                $this->descriptionVariables[$m[1]] = $m[2];

        // replace self-references
        do
        {
            $finished = true;
            foreach ($this->descriptionVariables as $k => $sv)
            {
                if (!preg_match('/\$<(\w*\d*)>/i', $sv, $m))
                    continue;

                $this->descriptionVariables[$k] = str_replace('$<'.$m[1].'>', '${'.$this->descriptionVariables[$m[1]].'}', $sv);
                $finished = false;
            }
        }
        while (!$finished);

        return true;
    }

    private function resolveEvaluation(string $formula) : string
    {
        // see Traits in javascript locales

        $PlayerName     = Lang::main('name');
        $pl    = $PL    = /* playerLevel set manually ? $this->charLevel : */ $this->dfnText('LANG.level', Lang::game('level'));
        $ap    = $AP    = $this->dfnText('LANG.traits.atkpwr[0]',    Lang::spell('traitShort', 'atkpwr'));
        $rap   = $RAP   = $this->dfnText('LANG.traits.rgdatkpwr[0]', Lang::spell('traitShort', 'rgdatkpwr'));
        $sp    = $SP    = $this->dfnText('LANG.traits.splpwr[0]',    Lang::spell('traitShort', 'splpwr'));
        $spa   = $SPA   = $this->dfnText('LANG.traits.arcsplpwr[0]', Lang::spell('traitShort', 'arcsplpwr'));
        $spfi  = $SPFI  = $this->dfnText('LANG.traits.firsplpwr[0]', Lang::spell('traitShort', 'firsplpwr'));
        $spfr  = $SPFR  = $this->dfnText('LANG.traits.frosplpwr[0]', Lang::spell('traitShort', 'frosplpwr'));
        $sph   = $SPH   = $this->dfnText('LANG.traits.holsplpwr[0]', Lang::spell('traitShort', 'holsplpwr'));
        $spn   = $SPN   = $this->dfnText('LANG.traits.natsplpwr[0]', Lang::spell('traitShort', 'natsplpwr'));
        $sps   = $SPS   = $this->dfnText('LANG.traits.shasplpwr[0]', Lang::spell('traitShort', 'shasplpwr'));
        $bh    = $BH    = $this->dfnText('LANG.traits.splheal[0]',   Lang::spell('traitShort', 'splheal'));
        $spi   = $SPI   = $this->dfnText('LANG.traits.spi[0]',       Lang::spell('traitShort', 'spi'));
        $sta   = $STA   = $this->dfnText('LANG.traits.sta[0]',       Lang::spell('traitShort', 'sta'));
        $str   = $STR   = $this->dfnText('LANG.traits.str[0]',       Lang::spell('traitShort', 'str'));
        $agi   = $AGI   = $this->dfnText('LANG.traits.agi[0]',       Lang::spell('traitShort', 'agi'));
        $int   = $INT   = $this->dfnText('LANG.traits.int[0]',       Lang::spell('traitShort', 'int'));
        $ar    = $AR    = $this->dfnText('LANG.traits.armor[0]',     Lang::item('cat', ITEM_CLASS_ARMOR, 0));

        $ph    = $PH    = $this->dfnText('Pct Holy Damage',            'PH');
        $pfi   = $PFI   = $this->dfnText('Pct Fire Damage',            'PFI');
        $pn    = $PN    = $this->dfnText('Pct Nature Damage',          'PN');
        $pfr   = $PFR   = $this->dfnText('Pct Frost Damage',           'PFR');
        $ps    = $PS    = $this->dfnText('Pct Shadow Damage',          'PS');
        $pa    = $PA    = $this->dfnText('Pct Arcane Damage',          'PA');
        $pbh   = $PBH   = $this->dfnText('LANG.traits.splheal[0]',     'PBH');
        $pbhd  = $PBHD  = $this->dfnText('Pct Healing Done',           'PBHD');

        $HND   = $hnd   = $this->dfnText('[Hands required by weapon]', 'HND'); // todo (med): localize this one
        $MWS   = $mws   = $this->dfnText('LANG.traits.mlespeed[0]',    'MWS');
        $OWS   = $ows   = $this->dfnText('LANG.traits.mlespeed[0]',    'OWS'); // todo (med): denote offhand
        $RWS   = $rws   = $this->dfnText('LANG.traits.rgdspeed[0]',    'RWS');
        // final character dmg
        $mw             = $this->dfnText('LANG.traits.mledmgmin[0]',   'mw');
        $MW             = $this->dfnText('LANG.traits.mledmgmax[0]',   'MW');
        $ow             = $this->dfnText('LANG.traits.mledmgmin[0]',   'ow'); // todo (med): denote offhand
        $OW             = $this->dfnText('LANG.traits.mledmgmax[0]',   'OW'); // todo (med): denote offhand
        $rw             = $this->dfnText('LANG.traits.rgddmgmin[0]',   'rw');
        $RW             = $this->dfnText('LANG.traits.rgddmgmax[0]',   'RW');
        // raw weapon dmg
        $mwb            = $this->dfnText('LANG.traits.mledmgmin[0]',   'mwb');
        $MWB            = $this->dfnText('LANG.traits.mledmgmax[0]',   'MWB');
        $owb            = $this->dfnText('LANG.traits.mledmgmin[0]',   'owb'); // todo (med): denote offhand
        $OWB            = $this->dfnText('LANG.traits.mledmgmax[0]',   'OWB'); // todo (med): denote offhand
        $rwb            = $this->dfnText('LANG.traits.rgddmgmin[0]',   'rwb');
        $RWB            = $this->dfnText('LANG.traits.rgddmgmax[0]',   'RWB');

        $abs   = $ABS   = fn($a)         => abs($a);
        $ceil  = $CEIL  = fn($a)         => ceil($a);
        $floor = $FLOOR = fn($a)         => floor($a);
        $min   = $MIN   = fn($a, $b)     => min($a, $b);
        $max   = $MAX   = fn($a, $b)     => max($a, $b);
        $gt    = $GT    = fn($a, $b)     => $a > $b;
        $lt    = $LT    = fn($a, $b)     => $a < $b;
        $gte   = $GTE   = fn($a, $b)     => $a >= $b;
        $lte   = $LTE   = fn($a, $b)     => $a <= $b;
        $eq    = $EQ    = fn($a, $b)     => $a == $b;
        $cond  = $COND  = fn($a, $b, $c) => $a ? $b : $c;
        $clamp = $CLAMP = fn($a, $b, $c) => clamp($a, $b, $c);

        if (preg_match_all('/\$\w+\b/i', $formula, $vars))
        {

            $evalable = true;

            foreach ($vars[0] as $var)                      // oh lord, forgive me this sin .. but is_callable seems to bug out and function_exists doesn't find lambda-functions >.<
            {
                $var = substr($var, 1);

                if (isset($$var))
                {
                    $eval = eval('return @$'.$var.';');     // attention: error suppression active here (will be logged anyway)
                    if (getType($eval) == 'object')
                        continue;
                    else if (is_numeric($eval))
                        continue;
                }
                else
                    $$var = '<UNK: $'.$var.'>';

                $evalable = false;
                break;
            }

            if (!$evalable)
            {
                // can't eval constructs because of strings present. replace constructs with strings
                $abs   = $ABS   = $this->dfnText('ABS(<span class=\'q1\'>a</span>)', 'ABS');
                $ceil  = $CEIL  = $this->dfnText('CEIL(<span class=\'q1\'>a</span>)', 'CEIL');
                $floor = $FLOOR = $this->dfnText('FLOOR(<span class=\'q1\'>a</span>)', 'FLOOR');
                $min   = $MIN   = $this->dfnText('MIN(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)', 'MIN');
                $max   = $MAX   = $this->dfnText('MAX(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)', 'MAX');
                $cond  = $COND  = $this->dfnText('COND(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>, <span class=\'q1\'>c</span>)<br /> <span class=\'q1\'>a</span> ? <span class=\'q1\'>b</span> : <span class=\'q1\'>c</span>', 'COND');
                $gt    = $GT    = $this->dfnText('GT(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> > <span class=\'q1\'>b</span>', 'GT');
                $lt    = $LT    = $this->dfnText('LT(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> < <span class=\'q1\'>b</span>', 'LT');
                $gte   = $GTE   = $this->dfnText('GTE(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> >= <span class=\'q1\'>b</span>', 'GTE');
                $lte   = $LTE   = $this->dfnText('LTE(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> <= <span class=\'q1\'>b</span>', 'LTE');
                $eq    = $EQ    = $this->dfnText('EQ(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> == <span class=\'q1\'>b</span>', 'EQ');
                $cond  = $COND  = $this->dfnText('COND(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>, <span class=\'q1\'>c</span>)<br /> <span class=\'q1\'>a</span> ? <span class=\'q1\'>b</span> : <span class=\'q1\'>c</span>', 'COND');
                $clamp = $CLAMP = $this->dfnText('CLAMP(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>, <span class=\'q1\'>c</span>)', 'CLAMP');
             // $pl    = $PL    = $this->dfnText('LANG.level', 'PL');

                // space out operators for better readability
                $formula = preg_replace('/(\+|-|\*|\/)/', ' \1 ', $formula);

                // note the " !
                return eval('return "('.$formula.')";');
            }
            else
                return eval('return '.$formula.';');
        }

        // since this function may be called recursively, there are cases, where the already evaluated string is tried to be evaled again, throwing parse errors
        // todo (med): also quit, if we replaced vars with non-interactive text
        if (strstr($formula, '</dfn>') || strstr($formula, '<!--'))
            return $formula;

        // hm, minor eval-issue. eval doesnt understand two operators without a space between them (eg. spelll: 18126)
        $formula = preg_replace('/(\+|-|\*|\/)(\+|-|\*|\/)/i', '\1 \2', $formula);

        // there should not be any letters without a leading $
        try { $formula =  eval('return '.$formula.';'); }
        // but there can be if we are non-interactive
        catch (\Throwable $e) { }

        return $formula;
    }

    // a variables structure is pretty .. flexile. match in steps
    private function matchVariableString(string $varString, ?int &$len = 0) : ?array
    {
        $op     = null;
        $oparg  = null;
        $lookup = null;
        $var    = null;
        $effIdx = null;
        $switch = null;

        // last value or gender -switch                 $[lg]ifText:elseText;
        if (preg_match('/^([lg])([^:]*:[^;]*);/i', $varString, $m, PREG_UNMATCHED_AS_NULL))
        {
            $len    = strlen($m[0]);
            $var    = $m[1];
            $switch = explode(':', $m[2]);
        }
        // basic variable ref (most common case)        $(refSpell)?(var)(effIdx)?
        else if (preg_match('/^(\d+)?([a-z]|bc)([123])?\b/i', $varString, $m, PREG_UNMATCHED_AS_NULL))
        {
            $len    = strlen($m[0]);
            $lookup = $m[1];
            $var    = $m[2];
            $effIdx = $m[3];
        }
        // variable ref /w formula                      $( (op) (oparg); )? (refSpell) ( (var) (effIdx) )   OR   $(refSpell) ( (op) (oparg); )? ( (var) (effIdx) )
        else if (preg_match('/^(([\+\-\*\/])(\d+);)?(\d+)?(([\+\-\*\/])(\d+);)?([a-z]|bc)([123])?\b/i', $varString, $m, PREG_UNMATCHED_AS_NULL))
        {
            $len    = strlen($m[0]);
            $lookup = $m[4];
            $var    = $m[8];
            $effIdx = $m[9];
            $op     = $m[6] ?: $m[2];
            $oparg  = $m[7] ?: $m[3];
        }
        // something .. else?
        else
            return null;

        return [$op, $oparg, $lookup, $var, $effIdx, $switch];
    }

    private function resolveVariableString(?string $op, ?string $oparg, ?int $lookup, ?string $var, ?int $effIdx, ?array $switch) : array
    {
        $signs  = ['+', '-', '/', '*', '%', '^'];

        // returns
        $minPoints    = null;
        $maxPoints    = null;
        $fmtStringMin = null;
        $fmtStringMax = null;
        $statId       = null;

        if (!$var)
            return [null, null, null, null, null];

        $effIdx ??= 1;                                      // if EffectIdx is omitted, assume EffectIdx: 1

        // cache at least some lookups.. should be moved to single spellList :/
        if ($lookup && $lookup != $this->id && !isset($this->refSpells[$lookup]))
            $this->refSpells[$lookup] = new Spell($lookup);

        $srcSpell = $lookup && $lookup != $this->id ? $this->refSpells[$lookup] : $this;
        if ($srcSpell->error)
            return [null, null, null, null, null];

        switch ($var)
        {
            case 'z':                                       // homeAreaId
                $fmtStringMin = Lang::spell('home');
                break;
            case 'g':                                       // boolean choice with casters gender as condition $gX:Y;
            case 'G':
                $fmtStringMin = '&lt;'.$switch[0].'/'.$switch[1].'&gt;';
                break;
            // resolve later by backtracking
            case 'l':                                       // boolean choice with last value as condition $lX:Y;
            case 'L':
                $fmtStringMin = '$l'.$switch[0].':'.$switch[1].';';
                break;
            // simple cases
            case 'a':                                       // EffectRadiusMin
            case 'A':                                       // EffectRadiusMax
                $base ??= $srcSpell->getField('effect'.$effIdx.'RadiusMax');
            case 'b':                                       // PointsPerComboPoint
            case 'B':
                $base ??= $srcSpell->getField('effect'.$effIdx.'PointsPerComboPoint');
            case 'c':                                       // powerCost
            case 'C':
                $base ??= $srcSpell->getField('powerCost');
            case 'e':                                       // EffectValueMultiplier
            case 'E':
                $base ??= $srcSpell->getField('effect'.$effIdx.'ValueMultiplier');
            case 'f':                                       // EffectDamageMultiplier
            case 'F':
                $base ??= $srcSpell->getField('effect'.$effIdx.'DamageMultiplier');
            case 'h':                                       // ProcChance
            case 'H':
                $base ??= $srcSpell->getField('procChance');
            case 'i':                                       // MaxAffectedTargets
            case 'I':
                $base ??= $srcSpell->getField('maxAffectedTargets');
            case 'n':                                       // ProcCharges
            case 'N':
                $base ??= $srcSpell->getField('procCharges');
            case 'p':                                       // powerCostPercent
            case 'P':
                $base ??= $srcSpell->getField('powerCostPercent');
            case 'q':                                       // EffectMiscValue
            case 'Q':
                $base ??= $srcSpell->getField('effect'.$effIdx.'MiscValue');
            case 'r':                                       // SpellRange
            case 'R':
                $base ??= $srcSpell->getField('rangeMaxHostile');
            case 't':                                       // Periode
            case 'T':
                $base ??= $srcSpell->getField('effect'.$effIdx.'Periode') / 1000;
            case 'u':                                       // StackCount
            case 'U':
                $base ??= $srcSpell->getField('stackAmount');
            case 'v':                                       // MaxTargetLevel
            case 'V':
                $base ??= $srcSpell->getField('MaxTargetLevel');
            case 'x':                                       // ChainTargetCount
            case 'X':
                $base ??= $srcSpell->getField('effect'.$effIdx.'ChainTarget');
            case 'bc':                                      // BonusMultiplier
            case 'BC':
                $base ??= $srcSpell->getField('effect'.$effIdx.'BonusMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'd':                                       // SpellDuration
            case 'D':                                       // todo (med): min/max?; /w unit?
                $base = $srcSpell->getField('duration');

                $fmtStringMin = Lang::formatTime($base, 'spell', 'duration');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = max(0, $base);
                break;
            case 'm':                                       // BasePoints (minValue)
            case 'M':                                       // BasePoints (maxValue)
                [$min, $max, $modStrMin, $modStrMax] = $srcSpell->calculateAmount($effIdx);

                $mv   = $srcSpell->getField('effect'.$effIdx.'MiscValue');
                $aura = $srcSpell->getField('effect'.$effIdx.'AuraId');

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    eval("\$max = $max $op $oparg;");
                }

                // Aura giving combat ratings
                $stats = [];
                if ($aura == SPELL_AURA_MOD_RATING)
                    if ($stats = StatsContainer::convertCombatRating($mv))
                        $this->isScaling = true;
                // Aura end

                if ($stats && $this->interactive >= self::INTERACTIVE_EMBEDDED)
                {
                    $fmtStringMin = '<!--rtg%s-->%s&nbsp;<small>(%s)</small>';
                    $statId = $stats[0];                    // could be multiple ratings in theory, but not expected to be
                }
                /*
                    todo: export to and solve formulas in javascript e.g.: spell 10187 - ${$42213m1*8*$<mult>} with $mult = ${${$?s31678[${1.05}][${${$?s31677[${1.04}][${${$?s31676[${1.03}][${${$?s31675[${1.02}][${${$?s31674[${1.01}][${1}]}}]}}]}}]}}]}*${$?s12953[${1.06}][${${$?s12952[${1.04}][${${$?s11151[${1.02}][${1}]}}]}}]}}
                    else if ($this->interactive == self::INTERACTIVE_FULL && ($modStrMin || $modStrMax))
                    {
                        $this->isScaling = true;
                        $fmtStringMin = $modStrMin.'%s';
                    }
                */
                $minPoints = ctype_lower($var) ? $min : $max;
                break;
            case 'o':                                       // TotalAmount for periodic auras (with variance)
            case 'O':
                $periode  = $srcSpell->getField('effect'.$effIdx.'Periode');
                $duration = $srcSpell->getField('duration');

                if (!$periode)
                {
                    // Mod Power Regeneration & Mod Health Regeneration have an implicit periode of 5sec
                    $aura = $srcSpell->getField('effect'.$effIdx.'AuraId');
                    if ($aura == SPELL_AURA_MOD_REGEN || $aura == SPELL_AURA_MOD_POWER_REGEN)
                        $periode = 5000;
                    else
                        $periode = 3000;
                }

                [$min, $max, $modStrMin, $modStrMax] = $srcSpell->calculateAmount($effIdx, intVal($duration / $periode));

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    eval("\$max = $max $op $oparg;");
                }

                if ($this->interactive >= self::INTERACTIVE_EMBEDDED && ($modStrMin || $modStrMax))
                {
                    $this->isScaling = true;

                    $fmtStringMin = $modStrMin.'%s';
                    $fmtStringMax = $modStrMax.'%s';
                }

                $minPoints = $min;
                $maxPoints = $max;
                break;
            case 's':                                       // BasePoints (with variance)
            case 'S':
                [$min, $max, $modStrMin, $modStrMax] = $srcSpell->calculateAmount($effIdx);
                $mv   = $srcSpell->getField('effect'.$effIdx.'MiscValue');
                $aura = $srcSpell->getField('effect'.$effIdx.'AuraId');

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    eval("\$max = $max $op $oparg;");
                }
                // Aura giving combat ratings
                $stats = [];
                if ($aura == SPELL_AURA_MOD_RATING)
                    if ($stats = StatsContainer::convertCombatRating($mv))
                        $this->isScaling = true;
                // Aura end

                if ($stats && $this->interactive >= self::INTERACTIVE_EMBEDDED)
                {
                    $fmtStringMin = '<!--rtg%s-->%s&nbsp;<small>(%s)</small>';
                    $statId = $stats[0];                    // could be multiple ratings in theory, but not expected to be
                }
                else if (($modStrMin || $modStrMax) && $this->interactive == self::INTERACTIVE_FULL)
                {
                    $this->isScaling = true;
                    $fmtStringMin = $modStrMin.'%s';
                    $fmtStringMax = $modStrMax.'%s';
                }

                $minPoints = $min;
                $maxPoints = $max;
                break;
        }

        // handle excessively precise floats
        if (is_float($minPoints))
            $minPoints = round($minPoints, 2);
        if (is_float($maxPoints))
            $maxPoints = round($maxPoints, 2);

        return [$minPoints, $maxPoints, $fmtStringMin, $fmtStringMax, $statId];
    }

    private function resolveFormulaString(string $formula, int $precision = 0) : array
    {
        $fSuffix = '%s';
        $fStat   = 0;

        // step 1: formula unpacking redux
        while (($formStartPos = strpos($formula, '${')) !== false)
        {
            $formBrktCnt   = 0;
            $formPrecision = 0;
            $formCurPos    = $formStartPos;

            $formOutStr    = '';

            while ($formCurPos < strlen($formula))
            {
                $char = $formula[$formCurPos];

                if ($char == '}')
                    $formBrktCnt--;

                if ($formBrktCnt)
                    $formOutStr .= $char;

                if ($char == '{')
                    $formBrktCnt++;

                if (!$formBrktCnt && $formCurPos != $formStartPos)
                    break;

                $formCurPos++;
            }

            if (isset($formula[++$formCurPos]) && $formula[$formCurPos] == '.')
            {
                $formPrecision = (int)$formula[++$formCurPos];
                ++$formCurPos;                              // for some odd reason the precision decimal survives if we dont increment further..
            }

            [$formOutStr, $fSuffix, $fStat] = $this->resolveFormulaString($formOutStr, $formPrecision);

            $formula = substr_replace($formula, $formOutStr, $formStartPos, ($formCurPos - $formStartPos));
        }

        // note: broken tooltip on this one
        // ${58644m1/-10} gets matched as a formula (ok), 58644m1 has no $ prefixed (not ok)
        // the client scraps the m1 and prints -5864
        if ($this->id == 58644)
            $formula = '$'.$formula;

        // step 2: resolve variables
        $pos = $len = 0;                                    // continue strpos-search from this offset
        $str = '';
        while (($npos = strpos($formula, '$', $pos)) !== false)
        {
            if ($npos != $pos)
                $str .= substr($formula, $pos, $npos - $pos);

            $pos = $npos++;

            if ($formula[$pos] == '$')
                $pos++;

            $varParts = $this->matchVariableString(substr($formula, $pos), $len);
            if (!$varParts)
            {
                $str .= '#';                                // mark as done, reset below
                continue;
            }

            $pos += $len;

            // we are resolving a formula -> omit ranges
            [$minPoints, , $fmtStringMin, , $statId] = $this->resolveVariableString(...$varParts);

            // time within formula -> rebase to seconds and omit timeUnit
            if (strtolower($varParts[3]) == 'd')            // old $varParts['var']
            {
               $minPoints /= 1000;
               unset($fmtStringMin);
            }

            $str .= $minPoints;

            // overwrite eventually inherited strings
            if (isset($fmtStringMin))
                $fSuffix = $fmtStringMin;

            // overwrite eventually inherited stat
            if (isset($statId))
                $fStat = $statId;
        }
        $str .= substr($formula, $pos);
        $str  = str_replace('#', '$', $str);                // reset markers

        // step 3: try to evaluate result
        $evaled = $this->resolveEvaluation($str);

        $return = is_numeric($evaled) ? round($evaled, $precision) : $evaled;

        return [$return, $fSuffix, $fStat];
    }

    private function handleFormulas(string $data, bool $topLevel = false) : string
    {
        // they are stacked recursively but should be balanced .. hf
        while (($formStartPos = strpos($data, '${')) !== false)
        {
            $formBrktCnt   = 0;
            $formPrecision = 0;
            $formCurPos    = $formStartPos;

            $formOutStr    = '';

            while ($formCurPos < strlen($data))             // only hard-exit condition, we'll hit those breaks eventually^^
            {
                $char = $data[$formCurPos];

                if ($char == '}')
                    $formBrktCnt--;

                if ($formBrktCnt)
                    $formOutStr .= $char;

                if ($char == '{')
                    $formBrktCnt++;

                if (!$formBrktCnt && $formCurPos != $formStartPos)
                    break;

                // advance position
                $formCurPos++;
            }

            $formCurPos++;

            // check for precision-modifiers
            if ($formCurPos + 1 < strlen($data) && $data[$formCurPos] == '.' && is_numeric($data[$formCurPos + 1]))
            {
                $formPrecision = $data[$formCurPos + 1];
                $formCurPos += 2;
            }
            [$formOutVal, $formOutStr, $statId] = $this->resolveFormulaString($formOutStr, $formPrecision ?: ($topLevel ? 0 : 10));

            if ($statId && Util::checkNumeric($formOutVal))
                $resolved = sprintf($formOutStr, $statId, abs($formOutVal), Util::setRatingLevel($this->charLevel, $statId, abs($formOutVal), $this->interactive == self::INTERACTIVE_FULL));
            else
                $resolved = sprintf($formOutStr, Util::checkNumeric($formOutVal) ? abs($formOutVal) : $formOutVal);

            $data = substr_replace($data, $resolved, $formStartPos, ($formCurPos - $formStartPos));
        }

        return $data;
    }

    private function handleVariables(string $data, bool $topLevel = false) : string
    {
        $pos = $len = 0;                                    // continue strpos-search from this offset
        $str = '';
        while (($npos = strpos($data, '$', $pos)) !== false)
        {
            if ($npos != $pos)
                $str .= substr($data, $pos, $npos - $pos);

            $pos = $npos++;

            if ($data[$pos] == '$')
                $pos++;

            $varParts = $this->matchVariableString(substr($data, $pos), $len);
            if (!$varParts)
            {
                $str .= '#';                                // mark as done, reset below
                continue;
            }

            $pos += $len;

            [$minPoints, $maxPoints, $fmtStringMin, $fmtStringMax, $statId] = $this->resolveVariableString(...$varParts);
            $resolved = is_numeric($minPoints) ? abs($minPoints) : $minPoints;
            if (isset($fmtStringMin))
            {
                if (isset($statId))
                    $resolved = sprintf($fmtStringMin, $statId, abs($minPoints), Util::setRatingLevel($this->charLevel, $statId, abs($minPoints), $this->interactive == self::INTERACTIVE_FULL));
                else
                    $resolved = sprintf($fmtStringMin, $resolved);
            }

            if (isset($maxPoints) && $minPoints != $maxPoints && !isset($statId))
                $str .= Lang::spell('pointsSpread', [$resolved, sprintf($fmtStringMax ?? '%s', abs($maxPoints))]);
            else
                $str .= $resolved;
        }
        $str .= substr($data, $pos);
        $str = str_replace('#', '$', $str);                 // reset marker

        return $str;
    }

    private function handleConditions(string $data, array &$relSpells, bool $topLevel = false) : string
    {
        /*
         * a) simple    - $?cond[A][B]                      // simple case of b)
         * b) elseif    - $?cond[A]?cond[B]..[C]            // can probably be repeated as often as you wanted
         * c) recursive - $?cond[A][$?cond[B][..]]          // can probably be stacked as deep as you wanted
         * d) bonkers   - $?!(cond1&!(cond2|cond3))[true]$?cond4[elseTrue][false];
         *
         * conditionals are /[as]\d+/i where:
         *  a: CGUnit_C::HasAuraBySpellId
         *  s: CGUnit_C::IsSpellKnown
         *
         * only case a) is currently used for KNOW-parameter
         */

        while (($condStartPos = strpos($data, '$?')) !== false)
        {
            $condBrktCnt = 0;
            $condCurPos  = $condStartPos + 2;               // after the '$?'
            $targetPart  = 3;                               // we usually want the second pair of brackets
            $curPart     = 0;                               // parts: $? 0 [ 1 ] 2 [ 3 ] 4 ...
            $condParts   = [];
            $isLastElse  = false;

            while ($condCurPos < strlen($data))             // only hard-exit condition, we'll hit those breaks eventually^^
            {
                $char = $data[$condCurPos];

                // advance position
                $condCurPos++;

                if ($char == '[')
                {
                    $condBrktCnt++;

                    if ($condBrktCnt == 1)
                        $curPart++;

                    // previously there was no condition -> last else
                    if ($condBrktCnt == 1)
                        if (($curPart && ($curPart % 2)) && (!isset($condParts[$curPart - 1]) || empty(trim($condParts[$curPart - 1]))))
                            $isLastElse = true;

                    if (empty($condParts[$curPart]))
                        continue;
                }

                if (empty($condParts[$curPart]))
                    $condParts[$curPart] = $char;
                else
                    $condParts[$curPart] .= $char;

                if ($char == ']')
                {
                    $condBrktCnt--;

                    if (!$condBrktCnt)
                    {
                        $condParts[$curPart] = substr($condParts[$curPart], 0, -1);
                        $curPart++;
                    }

                    if ($condBrktCnt)
                        continue;

                    if ($isLastElse)
                        break;
                }
            }

            // this should be 0 if all went well
            if ($condBrktCnt > 0)
            {
                trigger_error('Spell::handleConditions() - string contains unbalanced condition', E_USER_WARNING);
                $condParts[3] = $condParts[3] ?? '';
            }

            // check if it is know-compatible
            $know = 0;
            if (preg_match('/\(?(\!?)[as](\d+)\)?$/i', $condParts[0], $m))
            {
                if (!strstr($condParts[1], '$?'))
                    if (!strstr($condParts[3], '$?'))
                        if (!isset($condParts[5]))
                            $know = $m[2];

                // found a negation -> switching condition target
                if ($m[1] == '!')
                    $targetPart = 1;
            }
            // if not, what part of the condition should be used?
            else if (preg_match('/(([\W\D]*[as]\d+)|([^\[]*))/i', $condParts[0], $m) && !empty($m[3]))
            {
                $cnd = $this->resolveEvaluation($m[3]);
                if ((is_numeric($cnd) || is_bool($cnd)) && $cnd) // only case, deviating from normal; positive result -> use [true]
                    $targetPart = 1;
            }

            // recursive conditions
            if (strstr($condParts[$targetPart], '$?'))
                $condParts[$targetPart] = $this->handleConditions($condParts[$targetPart], $relSpells);

            if ($know && $topLevel)
            {
                foreach ([1, 3] as $pos)
                {
                    if (strstr($condParts[$pos], '${'))
                        $condParts[$pos] = $this->handleFormulas($condParts[$pos]);

                    if (strstr($condParts[$pos], '$'))
                        $condParts[$pos] = $this->handleVariables($condParts[$pos]);
                }

                // false condition first
                if (!isset($relSpells[$know]))
                    $relSpells[$know] = [];

                $relSpells[$know][] = [$condParts[3], $condParts[1]];

                $data = substr_replace($data, '<!--sp'.$know.':0-->'.$condParts[$targetPart].'<!--sp'.$know.'-->', $condStartPos, ($condCurPos - $condStartPos));
            }
            else
                $data = substr_replace($data, $condParts[$targetPart], $condStartPos, ($condCurPos - $condStartPos));
        }

        return $data;
    }
}

?>
