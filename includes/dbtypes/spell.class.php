<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellList extends DBTypeList
{
    use listviewHelper, sourceHelper;

    public static  int      $type       = Type::SPELL;
    public static  string   $brickFile  = 'spell';
    public static  string   $dataTable  = '?_spell';
    public         array    $ranks      = [];
    public        ?ItemList $relItems   = null;
    public static  array    $skillLines = array(
         6 => [ 43,  44,  45,  46,  54,  55,  95, 118, 136, 160, 162, 172, 173, 176, 226, 228, 229, 473], // Weapons
         8 => [293, 413, 414, 415, 433],                                                                  // Armor
         9 => SKILLS_TRADE_SECONDARY,                                                                     // sec. Professions
        10 => [ 98, 109, 111, 113, 115, 137, 138, 139, 140, 141, 313, 315, 673, 759],                     // Languages
        11 => SKILLS_TRADE_PRIMARY                                                                        // prim. Professions
    );

    public const EFFECTS_SCALING_HEAL     = array( // as per Unit::SpellHealingBonusDone() calls in TC
        SPELL_EFFECT_HEAL,                  SPELL_EFFECT_HEAL_PCT,                          SPELL_EFFECT_HEAL_MECHANICAL,                   SPELL_EFFECT_HEALTH_LEECH
    );
    public const EFFECTS_SCALING_DAMAGE   = array( // as per Unit::SpellDamageBonusDone() calls in TC
        SPELL_EFFECT_SCHOOL_DAMAGE,         SPELL_EFFECT_HEALTH_LEECH,                      SPELL_EFFECT_POWER_BURN
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

    private        array $spellVars   = [];
    private        array $refSpells   = [];
    private        array $tools       = [];
    private        bool  $interactive = false;
    private        int   $charLevel   = MAX_LEVEL;
    private        array $scaling     = [];
    private        array $parsedText  = [];
    private static array $spellTypes  = array(
         6 => 1,
         8 => 2,
        10 => 4
    );

    protected string $queryBase = 'SELECT s.*, s.`id` AS ARRAY_KEY FROM ?_spell s';
    protected array  $queryOpts = array(
                        's'   => [['src', 'sr', 'ic', 'ica']],  //  6: Type::SPELL
                        'ic'  => ['j' => ['?_icons ic  ON ic.`id`  = s.`iconId`',    true], 's' => ', ic.`name` AS "iconString"'],
                        'ica' => ['j' => ['?_icons ica ON ica.`id` = s.`iconIdAlt`', true], 's' => ', ica.`name` AS "iconStringAlt"'],
                        'sr'  => ['j' => ['?_spellrange sr ON sr.`id` = s.`rangeId`'], 's' => ', sr.`rangeMinHostile`, sr.`rangeMinFriend`, sr.`rangeMaxHostile`, sr.`rangeMaxFriend`, sr.`name_loc0` AS "rangeText_loc0", sr.`name_loc2` AS "rangeText_loc2", sr.`name_loc3` AS "rangeText_loc3", sr.`name_loc4` AS "rangeText_loc4", sr.`name_loc6` AS "rangeText_loc6", sr.`name_loc8` AS "rangeText_loc8"'],
                        'src' => ['j' => ['?_source src ON `type` = 6 AND `typeId` = s.`id`', true], 's' => ', `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`, `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        if (isset($miscData['interactive']))
            $this->interactive = $miscData['interactive'];

        if (isset($miscData['charLevel']))
            $this->charLevel = $miscData['charLevel'];

        // post processing
        $foo = DB::World()->selectCol('SELECT `perfectItemType` FROM skill_perfect_item_template WHERE `spellId` IN (?a)', $this->getFoundIDs());
        foreach ($this->iterate() as &$_curTpl)
        {
            // required for globals
            if ($idx = $this->canCreateItem())
                foreach ($idx as $i)
                    $foo[] = (int)$_curTpl['effect'.$i.'CreateItemId'];

            for ($i = 1; $i <= 8; $i++)
                if ($_curTpl['reagent'.$i] > 0)
                    $foo[] = (int)$_curTpl['reagent'.$i];

            for ($i = 1; $i <= 2; $i++)
                if ($_curTpl['tool'.$i] > 0)
                    $foo[] = (int)$_curTpl['tool'.$i];

            // ranks
            $this->ranks[$this->id] = $this->getField('rank', true);

            // sources
            for ($i = 1; $i < 25; $i++)
            {
                if ($_ = $_curTpl['src'.$i])
                    $this->sources[$this->id][$i][] = $_;

                unset($_curTpl['src'.$i]);
            }

            // set full masks to 0
            $_curTpl['reqClassMask'] &= ChrClass::MASK_ALL;
            if ($_curTpl['reqClassMask'] == ChrClass::MASK_ALL)
                $_curTpl['reqClassMask'] = 0;

            $_curTpl['reqRaceMask'] &= ChrRace::MASK_ALL;
            if ($_curTpl['reqRaceMask'] == ChrRace::MASK_ALL)
                $_curTpl['reqRaceMask'] = 0;

            // unpack skillLines
            $_curTpl['skillLines'] = [];
            if ($_curTpl['skillLine1'] < 0)
            {
                foreach (Game::$skillLineMask[$_curTpl['skillLine1']] as $idx => [, $skillLineId])
                    if ($_curTpl['skillLine2OrMask'] & (1 << $idx))
                        $_curTpl['skillLines'][] = $skillLineId;
            }
            else if ($sec = $_curTpl['skillLine2OrMask'])
            {
                if ($this->id == 818)                       // and another hack .. basic Campfire (818) has deprecated skill Survival (142) as first skillLine
                    $_curTpl['skillLines'] = [$sec, $_curTpl['skillLine1']];
                else
                    $_curTpl['skillLines'] = [$_curTpl['skillLine1'], $sec];
            }
            else if ($prim = $_curTpl['skillLine1'])
                $_curTpl['skillLines'] = [$prim];

            unset($_curTpl['skillLine1']);
            unset($_curTpl['skillLine2OrMask']);

            // fix missing icons
            $_curTpl['iconString'] = $_curTpl['iconString'] ?: DEFAULT_ICON;

            $this->scaling[$this->id] = false;
        }

        if ($foo)
            $this->relItems = new ItemList(array(['i.id', array_unique($foo)]));
    }

    // required for item-comparison
    public function getStatGain() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = new StatsContainer();

            foreach ($this->canEnchantmentItem() as $i)
                $data[$this->id]->fromDB(Type::ENCHANTMENT, $this->curTpl['effect'.$i.'MiscValue']);

            // todo: should enchantments be included here...?
            $data[$this->id]->fromSpell($this->curTpl);
        }

        return $data;
    }

    public function getProfilerMods() : array
    {
        // weapon hand check: param: slot, class, subclass, value
        $whCheck = '$function() { var j, w = _inventory.getInventory()[%d]; if (!w[0] || !g_items[w[0]]) { return 0; } j = g_items[w[0]].jsonequip; return (j.classs == %d && (%d & (1 << (j.subclass)))) ? %d : 0; }';

        $data = [];                       // flat gains
        foreach ($this->getStatGain() as $id => $spellData)
        {
            $data[$id] = $spellData->toJson(STAT::FLAG_ITEM | STAT::FLAG_PROFILER, false);

            // apply weapon restrictions
            $this->getEntry($id);
            $class    = $this->getField('equippedItemClass');
            $subClass = $this->getField('equippedItemSubClassMask');
            $slot     = $subClass & 0x5000C ? 18 : 16;
            if ($class != ITEM_CLASS_WEAPON || !$subClass)
                continue;

            foreach ($data[$id] as $key => $amt)
                $data[$id][$key] = [1, 'functionOf', sprintf($whCheck, $slot, $class, $subClass, $amt)];
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

        foreach ($this->iterate() as $id => $__)
        {
            // Shaman - Spirit Weapons (16268) (parry is normaly stored in g_statistics)
            // i should recurse into SPELL_EFFECT_LEARN_SPELL and apply SPELL_EFFECT_PARRY from there
            if ($id == 16268)
            {
                $data[$id]['parrypct'] = [5, 'add'];
                continue;
            }

            if (!($this->getField('attributes0') & SPELL_ATTR0_PASSIVE))
                continue;

            for ($i = 1; $i < 4; $i++)
            {
                $pts      = $this->calculateAmountForCurrent($i)[1];
                $mv       = $this->getField('effect'.$i.'MiscValue');
                $mvB      = $this->getField('effect'.$i.'MiscValueB');
                $au       = $this->getField('effect'.$i.'AuraId');
                $class    = $this->getField('equippedItemClass');
                $subClass = $this->getField('equippedItemSubClassMask');


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
                            $data[$id]['armor'] = [$pts / 100, 'percentOf', ['armor', 0]];
                        else if ($mv)
                            $modXBySchool($data[$id], $mv, 'res', $pts);
                        break;
                    case SPELL_AURA_MOD_RESISTANCE_OF_STAT_PERCENT:
                        // Armor only if explicitly specified
                        if ($mv == (1 << SPELL_SCHOOL_NORMAL))
                            $data[$id]['armor'] = [$pts / 100, 'percentOf', $jsonStat($mvB)];
                        else if ($mv)
                            $modXBySchool($data[$id], $mv, 'res', [$pts / 100, 'percentOf', $jsonStat($mvB)]);
                        break;
                    case SPELL_AURA_MOD_TOTAL_STAT_PERCENTAGE:
                        if ($mv > -1)                       // one stat
                            $modXByStat($data[$id], $mv, null, $pts);
                        else if ($mv < 0)                   // all stats
                            for ($iMod = ITEM_MOD_AGILITY; $iMod <= ITEM_MOD_STAMINA; $iMod++)
                                if ($idx = Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $iMod))
                                    if ($key = Stat::getJsonString($idx))
                                        $data[$id][$key] = [$pts / 100, 'percentOf', $key];
                        break;
                    case SPELL_AURA_MOD_SPELL_DAMAGE_OF_STAT_PERCENT:
                        $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                        $modXBySchool($data[$id], $mv, 'spldmg', [$pts / 100, 'percentOf', $jsonStat($mvB)]);
                        break;
                    case SPELL_AURA_MOD_RANGED_ATTACK_POWER_OF_STAT_PERCENT:
                        $modXByStat($data[$id], $mv, 'rgdatkpwr', $pts);
                        break;
                    case SPELL_AURA_MOD_ATTACK_POWER_OF_STAT_PERCENT:
                        $modXByStat($data[$id], $mv, 'mleatkpwr', $pts);
                        break;
                    case SPELL_AURA_MOD_SPELL_HEALING_OF_STAT_PERCENT:
                        $modXByStat($data[$id], $mv, 'splheal', $pts);
                        break;
                    case SPELL_AURA_MOD_MANA_REGEN_FROM_STAT:
                        $modXByStat($data[$id], $mv, 'manargn', $pts);
                        break;
                    case SPELL_AURA_MOD_MANA_REGEN_INTERRUPT:
                        $data[$id]['icmanargn'] = [$pts / 100, 'percentOf', 'oocmanargn'];
                        break;
                    case SPELL_AURA_MOD_SPELL_CRIT_CHANCE:
                    case SPELL_AURA_MOD_SPELL_CRIT_CHANCE_SCHOOL:
                        $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                        $modXBySchool($data[$id], $mv, 'splcritstrkpct', [$pts, 'add']);
                        if (($mv & SPELL_MAGIC_SCHOOLS) == SPELL_MAGIC_SCHOOLS)
                            $data[$id]['splcritstrkpct'] = [$pts, 'add'];
                        break;
                    case SPELL_AURA_MOD_ATTACK_POWER_OF_ARMOR:
                        $data[$id]['mleatkpwr'] = [1 / $pts, 'percentOf', 'fullarmor'];
                        $data[$id]['rgdatkpwr'] = [1 / $pts, 'percentOf', 'fullarmor'];
                        break;
                    case SPELL_AURA_MOD_WEAPON_CRIT_PERCENT:
                        if ($class < 1 || ($class == ITEM_CLASS_WEAPON && ($subClass & 0x5000C)))
                            $data[$id]['rgdcritstrkpct'] = [1, 'functionOf', sprintf($whCheck, 18, $class, $subClass, $pts)];
                            // $data[$id]['rgdcritstrkpct'] = [$pts, 'add'];
                        if ($class < 1 || ($class == ITEM_CLASS_WEAPON && ($subClass & 0xA5F3)))
                            $data[$id]['mlecritstrkpct'] = [1, 'functionOf', sprintf($whCheck, 16, $class, $subClass, $pts)];
                            // $data[$id]['mlecritstrkpct'] = [$pts, 'add'];
                        break;
                    case SPELL_AURA_MOD_PARRY_PERCENT:
                        $data[$id]['parrypct'] = [$pts, 'add'];
                        break;
                    case SPELL_AURA_MOD_DODGE_PERCENT:
                        $data[$id]['dodgepct'] = [$pts, 'add'];
                        break;
                    case SPELL_AURA_MOD_BLOCK_PERCENT:
                        $data[$id]['blockpct'] = [$pts, 'add'];
                        break;
                    case SPELL_AURA_MOD_INCREASE_ENERGY_PERCENT:
                        if ($mv == POWER_HEALTH)
                            $data[$id]['health'] = [$pts / 100, 'percentOf', 'health'];
                        else if ($mv == POWER_ENERGY)
                            $data[$id]['energy'] = [$pts / 100, 'percentOf', 'energy'];
                        else if ($mv == POWER_MANA)
                            $data[$id]['mana']   = [$pts / 100, 'percentOf', 'mana'];
                        else if ($mv == POWER_RAGE)
                            $data[$id]['rage']   = [$pts / 100, 'percentOf', 'rage'];
                        else if ($mv == POWER_RUNIC_POWER)
                            $data[$id]['runic']  = [$pts / 100, 'percentOf', 'runic'];
                        break;
                    case SPELL_AURA_MOD_INCREASE_HEALTH_PERCENT:
                        $data[$id]['health'] = [$pts / 100, 'percentOf', 'health'];
                        break;
                    case SPELL_AURA_MOD_BASE_HEALTH_PCT:    // only Tauren - Endurance (20550) ... if you are looking for something elegant, look away!
                        $data[$id]['health'] = [$pts / 100, 'functionOf', '$(x) => g_statistics.combo[x.classs][x.level][5]'];
                        break;
                    case SPELL_AURA_MOD_SHIELD_BLOCKVALUE_PCT:
                        $data[$id]['block'] = [$pts / 100, 'percentOf', 'block'];
                        break;
                    case SPELL_AURA_MOD_CRIT_PCT:
                        $data[$id]['mlecritstrkpct'] = [$pts, 'add'];
                        $data[$id]['rgdcritstrkpct'] = [$pts, 'add'];
                        $data[$id]['splcritstrkpct'] = [$pts, 'add'];
                        break;
                    case SPELL_AURA_MOD_SPELL_DAMAGE_OF_ATTACK_POWER:
                        $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                        $modXBySchool($data[$id], $mv, 'spldmg', [$pts / 100, 'percentOf', 'mleatkpwr']);
                        break;
                    case SPELL_AURA_MOD_SPELL_HEALING_OF_ATTACK_POWER:
                        $data[$id]['splheal'] = [$pts / 100, 'percentOf', 'mleatkpwr'];
                        break;
                    case SPELL_AURA_MOD_ATTACK_POWER_PCT:   // ingame only melee..?
                        $data[$id]['mleatkpwr'] = [$pts / 100, 'percentOf', 'mleatkpwr'];
                        break;
                    case SPELL_AURA_MOD_HEALTH_REGEN_PERCENT:
                        $data[$id]['healthrgn'] = [$pts / 100, 'percentOf', 'healthrgn'];
                        break;
                }
            }
        }

        return $data;
    }

    // halper
    public function getReagentsForCurrent() : array
    {
        $data = [];

        for ($i = 1; $i <= 8; $i++)
            if ($this->curTpl['reagent'.$i] > 0 && $this->curTpl['reagentCount'.$i])
                $data[$this->curTpl['reagent'.$i]] = [$this->curTpl['reagent'.$i], $this->curTpl['reagentCount'.$i]];

        return $data;
    }

    public function getToolsForCurrent() : array
    {
        if ($this->tools)
            return $this->tools;

        $tools = [];
        for ($i = 1; $i <= 2; $i++)
        {
            // TotemCategory
            if ($_ = $this->curTpl['toolCategory'.$i])
            {
                $tc = DB::Aowow()->selectRow('SELECT * FROM ?_totemcategory WHERE `id` = ?d', $_);
                $tools[$i + 1] = array(
                    'id'   => $_,
                    'name' => Util::localizedString($tc, 'name'));
            }

            // Tools
            if (!$this->curTpl['tool'.$i])
                continue;

            foreach ($this->relItems->iterate() as $relId => $__)
            {
                if ($relId != $this->curTpl['tool'.$i])
                    continue;

                $tools[$i - 1] = array(
                    'itemId'  => $relId,
                    'name'    => $this->relItems->getField('name', true),
                    'quality' => $this->relItems->getField('quality')
                );

                break;
            }
        }

        $this->tools = array_reverse($tools);

        return $this->tools;
    }

    public function getModelInfo(int $spellId = 0, int $effIdx = 0) : array
    {
        $displays = $results = [];

        foreach ($this->iterate() as $id => $__)
        {
            if ($spellId && $spellId != $id)
                continue;

            for ($i = 1; $i < 4; $i++)
            {
                if ($spellId && $effIdx && $effIdx != $i)
                    continue;

                $effMV = $this->curTpl['effect'.$i.'MiscValue'];
                if (!$effMV)
                    continue;

                // GO Model from MiscVal
                if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_MODEL_OBJECT))
                {
                    if (isset($displays[Type::OBJECT][$id]))
                        $displays[Type::OBJECT][$id][0][] = $i;
                    else
                        $displays[Type::OBJECT][$id] = [[$i], $effMV];
                }
                // NPC Model from MiscVal
                else if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_MODEL_NPC) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::AURAS_MODEL_NPC))
                {
                    if (isset($displays[Type::NPC][$id]))
                        $displays[Type::NPC][$id][0][] = $i;
                    else
                        $displays[Type::NPC][$id] = [[$i], $effMV];
                }
                // Shapeshift
                else if ($this->curTpl['effect'.$i.'AuraId'] == SPELL_AURA_MOD_SHAPESHIFT)
                {
                    $subForms = array(
                        892  => [892,  29407, 29406, 29408, 29405],         // Cat - NE
                        8571 => [8571, 29410, 29411, 29412],                // Cat - Tauren
                        2281 => [2281, 29413, 29414, 29416, 29417],         // Bear - NE
                        2289 => [2289, 29415, 29418, 29419, 29420, 29421]   // Bear - Tauren
                    );

                    if ($st = DB::Aowow()->selectRow('SELECT *, `displayIdA` AS "model1", `displayIdH` AS "model2" FROM ?_shapeshiftforms WHERE `id` = ?d', $effMV))
                    {
                        foreach ([1, 2] as $j)
                            if (isset($subForms[$st['model'.$j]]))
                                $st['model'.$j] = $subForms[$st['model'.$j]][array_rand($subForms[$st['model'.$j]])];

                        $results[$id][$i] = array(
                            'type'         => Type::NPC,
                            'creatureType' => $st['creatureType'],
                            'displayId'    => $st['model2'] ? $st['model'.rand(1, 2)] : $st['model1'],
                            'displayName'  => Lang::game('st', $effMV)
                        );
                    }
                }
            }
        }

        if (!empty($displays[Type::NPC]))
        {
            $nModels = new CreatureList(array(['id', array_column($displays[Type::NPC], 1)]));
            foreach ($nModels->iterate() as $nId => $__)
            {
                foreach ($displays[Type::NPC] as $srcId => [$indizes, $npcId])
                {
                    if ($npcId == $nId)
                    {
                        foreach ($indizes as $idx)
                        {
                            $res = array(
                                'type'        => Type::NPC,
                                'typeId'      => $nId,
                                'displayId'   => $nModels->getRandomModelId(),
                                'displayName' => $nModels->getField('name', true)
                            );

                            if ($nModels->getField('humanoid'))
                                $res['humanoid'] = 1;

                            $results[$srcId][$idx] = $res;
                        }
                    }
                }
            }
        }

        if (!empty($displays[Type::OBJECT]))
        {
            $oModels = new GameObjectList(array(['id', array_column($displays[Type::OBJECT], 1)]));
            foreach ($oModels->iterate() as $oId => $__)
            {
                foreach ($displays[Type::OBJECT] as $srcId => [$indizes, $objId])
                {
                    if ($objId == $oId)
                    {
                        foreach ($indizes as $idx)
                        {
                            $results[$srcId][$idx] = array(
                                'type'        => Type::OBJECT,
                                'typeId'      => $oId,
                                'displayId'   => $oModels->getField('displayId'),
                                'displayName' => $oModels->getField('name', true)
                            );
                        }
                    }
                }
            }
        }

        if ($spellId && $effIdx)
            return $results[$spellId][$effIdx] ?? [];

        if ($spellId)
            return $results[$spellId] ?? [];

        return $results;
    }

    private function createRangesForCurrent() : string
    {
        if (!$this->curTpl['rangeMaxHostile'])
            return '';

        if ($this->curTpl['attributes3'] & SPELL_ATTR3_DONT_DISPLAY_RANGE)
            return '';

        // minRange exists; show as range
        if ($this->curTpl['rangeMinHostile'])
            return Lang::spell('range', [$this->curTpl['rangeMinHostile'].' - '.$this->curTpl['rangeMaxHostile']]);
        // friend and hostile differ; do color
        else if ($this->curTpl['rangeMaxHostile'] != $this->curTpl['rangeMaxFriend'])
            return Lang::spell('range', ['<span class="q10">'.$this->curTpl['rangeMaxHostile'].'</span> - <span class="q2">'.$this->curTpl['rangeMaxFriend']. '</span>']);
        // hardcode: "melee range"
        else if ($this->curTpl['rangeMaxHostile'] == 5)
            return Lang::spell('meleeRange');
        // hardcode "unlimited range"
        else if ($this->curTpl['rangeMaxHostile'] == 50000)
            return Lang::spell('unlimRange');
        // regular case
        else
            return Lang::spell('range', [$this->curTpl['rangeMaxHostile']]);
    }

    public function createPowerCostForCurrent() : string
    {
        $str = '';

        $pt   = $this->curTpl['powerType'];
        $pc   = $this->curTpl['powerCost'];
        $pcp  = $this->curTpl['powerCostPercent'];
        $pps  = $this->curTpl['powerPerSecond'];
        $pcpl = $this->curTpl['powerCostPerLevel'];

        // some potion effects have this set, but it's not displayed by client (or enforced by core)
        if ($pt == POWER_HAPPINESS)
            return '';

        if ($pt == POWER_RAGE || $pt == POWER_RUNIC_POWER)
            $pc /= 10;

        if ($pt == POWER_RUNE && ($rCost = ($this->curTpl['powerCostRunes'] & 0x333)))
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

    public function createCastTimeForCurrent(bool $short = true, bool $noInstant = true) : string
    {
        if (!$this->curTpl['castTime'] && $this->isChanneledSpell())
            return Lang::spell('channeled');
        // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only (todo (low): rule is imperfect)
        else if (!$this->curTpl['castTime'] && ($this->curTpl['damageClass'] != SPELL_DAMAGE_CLASS_MAGIC || $this->curTpl['attributes0'] & SPELL_ATTR0_ABILITY))
            return Lang::spell('instantPhys');
        // show instant only for player/pet/npc abilities (todo (low): unsure when really hidden (like talent-case))
        else if ($noInstant && !in_array($this->curTpl['typeCat'], [11, 7, -3, -6, -8, 0]) && !($this->curTpl['cuFlags'] & SPELL_CU_TALENTSPELL))
            return '';
        else
            return $short ? Lang::formatTime($this->curTpl['castTime'] * 1000, 'spell', 'castTime') : DateTime::formatTimeElapsedFloat($this->curTpl['castTime'] * 1000);
    }

    private function createCooldownForCurrent() : string
    {
        if ($this->curTpl['attributes6'] & SPELL_ATTR6_DONT_DISPLAY_COOLDOWN)
            return '';
        else if ($this->curTpl['recoveryTime'])
            return Lang::formatTime($this->curTpl['recoveryTime'], 'spell', 'cooldown');
        else if ($this->curTpl['recoveryCategory'])
            return Lang::formatTime($this->curTpl['recoveryCategory'], 'spell', 'cooldown');

        return '';
    }

    // formulae base from TC
    private function calculateAmountForCurrent(int $effIdx, int $nTicks = 1) : array
    {
        $level   = $this->charLevel;
        $maxBase = 0;
        $rppl    = $this->getField('effect'.$effIdx.'RealPointsPerLevel');
        $base    = $this->getField('effect'.$effIdx.'BasePoints');
        $add     = $this->getField('effect'.$effIdx.'DieSides');
        $maxLvl  = $this->getField('maxLevel');
        $baseLvl = $this->getField('baseLevel');

        if ($rppl)
        {
            if ($level > $maxLvl && $maxLvl > 0)
                $level = $maxLvl;
            else if ($level < $baseLvl)
                $level = $baseLvl;

            if (!$this->getField('atributes0') & SPELL_ATTR0_PASSIVE)
                $level -= $this->getField('spellLevel');

            $maxBase += (int)(($level - $baseLvl) * $rppl);
            $maxBase *= $nTicks;
        }

        $min = $nTicks * ($add ? $base + 1 : $base);
        $max = $nTicks * ($add + $base);

        return [
            $min + $maxBase,
            $max + $maxBase,
            $rppl ? '<!--ppl'.$baseLvl.':'.($maxLvl ?: $level).':'.$min.':'.($rppl * 100 * $nTicks).'-->' : null,
            $rppl ? '<!--ppl'.$baseLvl.':'.($maxLvl ?: $level).':'.$max.':'.($rppl * 100 * $nTicks).'-->' : null
        ];
    }

    public function canCreateItem() : array
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_ITEM_CREATE) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::AURAS_ITEM_CREATE))
                if ($this->curTpl['effect'.$i.'CreateItemId'] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function canTriggerSpell() : array
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_TRIGGER) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::AURAS_TRIGGER))
                if ($this->curTpl['effect'.$i.'AuraId'] == SPELL_AURA_DUMMY || $this->curTpl['effect'.$i.'TriggerSpell'] > 0 || ($this->curTpl['effect'.$i.'Id'] == SPELL_EFFECT_TITAN_GRIP && $this->curTpl['effect'.$i.'MiscValue'] > 0))
                    $idx[] = $i;

        return $idx;
    }

    public function canTeachSpell() : array
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_TEACH))
                if ($this->curTpl['effect'.$i.'TriggerSpell'] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function canEnchantmentItem() : array
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_ENCHANTMENT))
                $idx[] = $i;

        return $idx;
    }

    public function isChanneledSpell() : bool
    {
        return $this->curTpl['attributes1'] & (SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2);
    }

    public function isScalableHealingSpell() : bool
    {
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_SCALING_HEAL) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::AURAS_SCALING_HEAL))
                return true;

        return false;
    }

    public function isScalableDamagingSpell() : bool
    {
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::EFFECTS_SCALING_DAMAGE) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::AURAS_SCALING_DAMAGE))
                return true;

        return false;
    }

    public function periodicEffectsMask() : int
    {
        $effMask = 0x0;

        for ($i = 1; $i < 4; $i++)
            if ($this->curTpl['effect'.$i.'Periode'] > 0)
                $effMask |= 1 << ($i - 1);

        return $effMask;
    }

    // description-, buff-parsing component
    private function resolveEvaluation(string $formula) : string
    {
        // see Traits in javascript locales

        $PlayerName     = Lang::main('name');
        $pl    = $PL    = /* playerLevel set manually ? $this->charLevel : */ $this->interactive ? sprintf(Util::$dfnString, 'LANG.level', Lang::game('level')) : Lang::game('level');
        $ap    = $AP    = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.atkpwr[0]',    Lang::spell('traitShort', 'atkpwr'))    : Lang::spell('traitShort', 'atkpwr');
        $rap   = $RAP   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.rgdatkpwr[0]', Lang::spell('traitShort', 'rgdatkpwr')) : Lang::spell('traitShort', 'rgdatkpwr');
        $sp    = $SP    = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.splpwr[0]',    Lang::spell('traitShort', 'splpwr'))    : Lang::spell('traitShort', 'splpwr');
        $spa   = $SPA   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.arcsplpwr[0]', Lang::spell('traitShort', 'arcsplpwr')) : Lang::spell('traitShort', 'arcsplpwr');
        $spfi  = $SPFI  = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.firsplpwr[0]', Lang::spell('traitShort', 'firsplpwr')) : Lang::spell('traitShort', 'firsplpwr');
        $spfr  = $SPFR  = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.frosplpwr[0]', Lang::spell('traitShort', 'frosplpwr')) : Lang::spell('traitShort', 'frosplpwr');
        $sph   = $SPH   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.holsplpwr[0]', Lang::spell('traitShort', 'holsplpwr')) : Lang::spell('traitShort', 'holsplpwr');
        $spn   = $SPN   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.natsplpwr[0]', Lang::spell('traitShort', 'natsplpwr')) : Lang::spell('traitShort', 'natsplpwr');
        $sps   = $SPS   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.shasplpwr[0]', Lang::spell('traitShort', 'shasplpwr')) : Lang::spell('traitShort', 'shasplpwr');
        $bh    = $BH    = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.splheal[0]',   Lang::spell('traitShort', 'splheal'))   : Lang::spell('traitShort', 'splheal');
        $spi   = $SPI   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.spi[0]',       Lang::spell('traitShort', 'spi'))       : Lang::spell('traitShort', 'spi');
        $sta   = $STA   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.sta[0]',       Lang::spell('traitShort', 'sta'))       : Lang::spell('traitShort', 'sta');
        $str   = $STR   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.str[0]',       Lang::spell('traitShort', 'str'))       : Lang::spell('traitShort', 'str');
        $agi   = $AGI   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.agi[0]',       Lang::spell('traitShort', 'agi'))       : Lang::spell('traitShort', 'agi');
        $int   = $INT   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.int[0]',       Lang::spell('traitShort', 'int'))       : Lang::spell('traitShort', 'int');

        // only 'ron test spell', guess its %-dmg mod; no idea what bc2 might be
        $pa    = '<$PctArcane>';                            // %arcane
        $pfi   = '<$PctFire>';                              // %fire
        $pfr   = '<$PctFrost>';                             // %frost
        $ph    = '<$PctHoly>';                              // %holy
        $pn    = '<$PctNature>';                            // %nature
        $ps    = '<$PctShadow>';                            // %shadow
        $pbh   = '<$PctHeal>';                              // %heal
        $pbhd  = '<$PctHealDone>';                          // %heal done
        $bc2   = '<$bc2>';                                  // bc2

        $HND   = $hnd   = $this->interactive ? sprintf(Util::$dfnString, '[Hands required by weapon]', 'HND') : 'HND';    // todo (med): localize this one
        $MWS   = $mws   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.mlespeed[0]',    'MWS') : 'MWS';
        $mw             = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.dmgmin1[0]',     'mw')  : 'mw';
        $MW             = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.dmgmax1[0]',     'MW')  : 'MW';
        $mwb            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.mledmgmin[0]',   'mwb') : 'mwb';
        $MWB            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.mledmgmax[0]',   'MWB') : 'MWB';
        $rwb            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.rgddmgmin[0]',   'rwb') : 'rwb';
        $RWB            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.rgddmgmax[0]',   'RWB') : 'RWB';

        $cond  = $COND  = fn($a, $b, $c) => $a ? $b : $c;
        $eq    = $EQ    = fn($a, $b)     => $a == $b;
        $gt    = $GT    = fn($a, $b)     => $a > $b;
        $gte   = $GTE   = fn($a, $b)     => $a >= $b;
        $floor = $FLOOR = fn($a)         => floor($a);
        $max   = $MAX   = fn($a, $b)     => max($a, $b);
        $min   = $MIN   = fn($a, $b)     => min($a, $b);

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
                $cond  = $COND  = !$this->interactive ? 'COND'  : sprintf(Util::$dfnString, 'COND(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>, <span class=\'q1\'>c</span>)<br /> <span class=\'q1\'>a</span> ? <span class=\'q1\'>b</span> : <span class=\'q1\'>c</span>', 'COND');
                $eq    = $EQ    = !$this->interactive ? 'EQ'    : sprintf(Util::$dfnString, 'EQ(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> == <span class=\'q1\'>b</span>', 'EQ');
                $gt    = $GT    = !$this->interactive ? 'GT'    : sprintf(Util::$dfnString, 'GT(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> > <span class=\'q1\'>b</span>', 'GT');
                $gte   = $GTE   = !$this->interactive ? 'GTE'   : sprintf(Util::$dfnString, 'GTE(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> >= <span class=\'q1\'>b</span>', 'GTE');
                $floor = $FLOOR = !$this->interactive ? 'FLOOR' : sprintf(Util::$dfnString, 'FLOOR(<span class=\'q1\'>a</span>)', 'FLOOR');
                $min   = $MIN   = !$this->interactive ? 'MIN'   : sprintf(Util::$dfnString, 'MIN(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)', 'MIN');
                $max   = $MAX   = !$this->interactive ? 'MAX'   : sprintf(Util::$dfnString, 'MAX(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)', 'MAX');
                $pl    = $PL    = !$this->interactive ? 'PL'    : sprintf(Util::$dfnString, 'LANG.level', 'PL');

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

    // description-, buff-parsing component
    // a variables structure is pretty .. flexile. match in steps
    private function matchVariableString(string $varString, ?int &$len = 0) : array
    {
        $varParts = array(
            'op'     => null,
            'oparg'  => null,
            'lookup' => null,
            'var'    => null,
            'effIdx' => null,
            'switch' => null
        );

        // last value or gender -switch                 $[lg]ifText:elseText;
        if (preg_match('/^([lg])([^:]*:[^;]*);/i', $varString, $m))
        {
            $len                = strlen($m[0]);
            $varParts['var']    = $m[1];
            $varParts['switch'] = explode(':', $m[2]);
        }
        // basic variable ref (most common case)        $(refSpell)?(var)(effIdx)?
        else if (preg_match('/^(\d*)([a-z])([123]?)\b/i', $varString, $m))
        {
            $len                = strlen($m[0]);
            $varParts['lookup'] = $m[1];
            $varParts['var']    = $m[2];
            $varParts['effIdx'] = $m[3];
        }
        // variable ref /w formula                      $( (op) (oparg); )? (refSpell) ( (var) (effIdx) )   OR   $(refSpell) ( (op) (oparg); )? ( (var) (effIdx) )
        else if (preg_match('/^(([\+\-\*\/])(\d+);)?(\d*)(([\+\-\*\/])(\d+);)?([a-z])([123]?)\b/i', $varString, $m))
        {
            $len                = strlen($m[0]);
            $varParts['lookup'] = $m[4];
            $varParts['var']    = $m[8];
            $varParts['effIdx'] = $m[9];
            $varParts['op']     = $m[6] ?: $m[2];
            $varParts['oparg']  = $m[7] ?: $m[3];
        }
        // something .. else?
        else
            return [];

        return $varParts;
    }

    // description-, buff-parsing component
    private function resolveVariableString(array $varParts) : array
    {
        $signs  = ['+', '-', '/', '*', '%', '^'];

        foreach ($varParts as $k => $v)
            $$k = $v;

        // returns
        $minPoints    = null;
        $maxPoints    = null;
        $fmtStringMin = null;
        $fmtStringMax = null;
        $statId       = null;

        if (!$var)
            return [null, null, null, null, null];

        if (!$effIdx)                                       // if EffectIdx is omitted, assume EffectIdx: 1
            $effIdx = 1;

        // cache at least some lookups.. should be moved to single spellList :/
        if ($lookup && $lookup != $this->id && !isset($this->refSpells[$lookup]))
            $this->refSpells[$lookup] = new SpellList(array(['s.id', $lookup]));

        $srcSpell = $lookup && $lookup != $this->id ? $this->refSpells[$lookup] : $this;
        if ($srcSpell->error)
            return [null, null, null, null, null];

        switch ($var)
        {
            case 'a':                                       // EffectRadiusMin
            case 'A':                                       // EffectRadiusMax
                $base = $srcSpell->getField('effect'.$effIdx.'RadiusMax');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'b':                                       // PointsPerComboPoint
            case 'B':
                $base = $srcSpell->getField('effect'.$effIdx.'PointsPerComboPoint');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'd':                                       // SpellDuration
            case 'D':                                       // todo (med): min/max?; /w unit?
                $base = $srcSpell->getField('duration');

                $fmtStringMin = Lang::formatTime($srcSpell->getField('duration'), 'spell', 'duration');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base < 0 ? 0 : $base;
                break;
            case 'e':                                       // EffectValueMultiplier
            case 'E':
                $base = $srcSpell->getField('effect'.$effIdx.'ValueMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'f':                                       // EffectDamageMultiplier
            case 'F':
                $base = $srcSpell->getField('effect'.$effIdx.'DamageMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'g':                                       // boolean choice with casters gender as condition $gX:Y;
            case 'G':
                $fmtStringMin = '&lt;'.$switch[0].'/'.$switch[1].'&gt;';
                break;
            case 'h':                                       // ProcChance
            case 'H':
                $base = $srcSpell->getField('procChance');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'i':                                       // MaxAffectedTargets
            case 'I':
                $base = $srcSpell->getField('maxAffectedTargets');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'l':                                       // boolean choice with last value as condition $lX:Y;
            case 'L':
                // resolve later by backtracking
                $fmtStringMin = '$l'.$switch[0].':'.$switch[1].';';
                break;
            case 'm':                                       // BasePoints (minValue)
            case 'M':                                       // BasePoints (maxValue)
                [$min, $max, $modStrMin, $modStrMax] = $srcSpell->calculateAmountForCurrent($effIdx);

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
                        $this->scaling[$this->id] = true;
                // Aura end

                if ($stats)
                {
                    $fmtStringMin = '<!--rtg%s-->%s&nbsp;<small>(%s)</small>';
                    $statId = $stats[0];                    // could be multiple ratings in theory, but not expected to be
                }
                /*
                    todo: export to and solve formulas in javascript e.g.: spell 10187 - ${$42213m1*8*$<mult>} with $mult = ${${$?s31678[${1.05}][${${$?s31677[${1.04}][${${$?s31676[${1.03}][${${$?s31675[${1.02}][${${$?s31674[${1.01}][${1}]}}]}}]}}]}}]}*${$?s12953[${1.06}][${${$?s12952[${1.04}][${${$?s11151[${1.02}][${1}]}}]}}]}}
                    else if ($this->interactive && ($modStrMin || $modStrMax))
                    {
                        $this->scaling[$this->id] = true;
                        $fmtStringMin = $modStrMin.'%s';
                    }
                */
                $minPoints = ctype_lower($var) ? $min : $max;
                break;
            case 'n':                                       // ProcCharges
            case 'N':
                $base = $srcSpell->getField('procCharges');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
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

                [$min, $max, $modStrMin, $modStrMax] = $srcSpell->calculateAmountForCurrent($effIdx, intVal($duration / $periode));

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    eval("\$max = $max $op $oparg;");
                }

                if ($this->interactive && ($modStrMin || $modStrMax))
                {
                    $this->scaling[$this->id] = true;

                    $fmtStringMin = $modStrMin.'%s';
                    $fmtStringMax = $modStrMax.'%s';
                }

                $minPoints = $min;
                $maxPoints = $max;
                break;
            case 'q':                                       // EffectMiscValue
            case 'Q':
                $base = $srcSpell->getField('effect'.$effIdx.'MiscValue');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'r':                                       // SpellRange
            case 'R':
                $base = $srcSpell->getField('rangeMaxHostile');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 's':                                       // BasePoints (with variance)
            case 'S':
                [$min, $max, $modStrMin, $modStrMax] = $srcSpell->calculateAmountForCurrent($effIdx);
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
                        $this->scaling[$this->id] = true;
                // Aura end

                if ($stats)
                {
                    $fmtStringMin = '<!--rtg%s-->%s&nbsp;<small>(%s)</small>';
                    $statId = $stats[0];                    // could be multiple ratings in theory, but not expected to be
                }
                else if (($modStrMin || $modStrMax) && $this->interactive)
                {
                    $this->scaling[$this->id] = true;
                    $fmtStringMin = $modStrMin.'%s';
                    $fmtStringMax = $modStrMax.'%s';
                }

                $minPoints = $min;
                $maxPoints = $max;
                break;
            case 't':                                       // Periode
            case 'T':
                $base = $srcSpell->getField('effect'.$effIdx.'Periode') / 1000;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'u':                                       // StackCount
            case 'U':
                $base = $srcSpell->getField('stackAmount');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'v':                                       // MaxTargetLevel
            case 'V':
                $base = $srcSpell->getField('MaxTargetLevel');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'x':                                       // ChainTargetCount
            case 'X':
                $base = $srcSpell->getField('effect'.$effIdx.'ChainTarget');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $minPoints = $base;
                break;
            case 'z':                                       // HomeZone
                $fmtStringMin = Lang::spell('home');
                break;
        }

        // handle excessively precise floats
        if (is_float($minPoints))
            $minPoints = round($minPoints, 2);
        if (isset($maxPoints) && is_float($maxPoints))
            $maxPoints = round($maxPoints, 2);

        return [$minPoints, $maxPoints, $fmtStringMin, $fmtStringMax, $statId];
    }

    // description-, buff-parsing component
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
        $pos    = 0;                                        // continue strpos-search from this offset
        $str    = '';
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
            [$minPoints, , $fmtStringMin, , $statId] = $this->resolveVariableString($varParts);

            // time within formula -> rebase to seconds and omit timeUnit
            if (strtolower($varParts['var']) == 'd')
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

    // should probably used only once to create ?_spell. come to think of it, it yields the same results every time.. it absolutely has to!
    // although it seems to be pretty fast, even on those pesky test-spells with extra complex tooltips (Ron Test Spell X))
    public function parseText(string $type = 'description', int $level = MAX_LEVEL) : array
    {
        // oooo..kaaayy.. parsing text in 6 or 7 easy steps
        // we don't use the internal iterator here. This func has to be called for the individual template.
        // otherwise it will get a bit messy, when we iterate, while we iterate *yo dawg!*

        /*
            documentation .. sort of
            bracket use
                ${}.x - formulas; .x is optional; x:[0-9] .. max-precision of a floatpoint-result; default: 0
                $[]   - conditionals ... like $?condition[true][false]; alternative $?!(cond1|cond2)[true]$?cond3[elseTrue][false]; ?a40120: has aura 40120; ?s40120: knows spell 40120(?)
                $<>   - variables
                ()    - regular use for function-like calls

            variables in use .. caseSensitive

            game variables (optionally replace with textVars)
                $PlayerName - Cpt. Obvious
                $PL / $pl   - PlayerLevel
                $STR        - Strength Attribute (not seen)
                $AGI        - Agility Attribute (not seen)
                $STA        - Stamina Attribute (not seen)
                $INT        - Intellect Attribute (not seen)
                $SPI        - Spirit Attribute
                $AP         - Atkpwr
                $RAP        - RngAtkPwr
                $HND        - hands used by weapon (1H, 2H) => (1, 2)
                $MWS        - MainhandWeaponSpeed
                $mw / $MW   - MainhandWeaponDamage Min/Max
                $rwb / $RWB - RangedWeapon..Bonus? Min/Max
                $sp         - Spellpower
                $spa        - Spellpower Arcane
                $spfi       - Spellpower Fire
                $spfr       - Spellpower Frost
                $sph        - Spellpower Holy
                $spn        - Spellpower Nature
                $sps        - Spellpower Shadow
                $bh         - Bonus Healing
                $pa         - %-ArcaneDmg (as float)         // V seems broken
                $pfi        - %-FireDmg (as float)
                $pfr        - %-FrostDmg (as float)
                $ph         - %-HolyDmg (as float)
                $pn         - %-NatureDmg (as float)
                $ps         - %-ShadowDmg (as float)
                $pbh        - %-HealingBonus (as float)
                $pbhd       - %-Healing Done (as float)      // all above seem broken
                $bc2        - baseCritChance? always 3.25 (unsure)

            spell variables (the stuff we can actually parse) rounding... >5 up?
                $a          - SpellRadius; per EffectIdx
                $b          - PointsPerComboPoint; per EffectIdx
                $d / $D     - SpellDuration; appended timeShorthand; d/D maybe base/max duration?; interpret "0" as "until canceled"
                $e          - EffectValueMultiplier; per EffectIdx
                $f / $F     - EffectDamageMultiplier; per EffectIdx
                $g / $G     - Gender-Switch $Gmale:female;
                $h / $H     - ProcChance
                $i          - MaxAffectedTargets
                $l          - LastValue-Switch; last value as condition $Ltrue:false;
                $m / $M     - BasePoints; per EffectIdx; m/M +1/+effectDieSides
                $n          - ProcCharges
                $o          - TotalAmount (for periodic auras); per EffectIdx
                $q          - EffectMiscValue; per EffectIdx
                $r          - SpellRange (hostile)
                $s / $S     - BasePoints; per EffectIdx; as Range, if applicable
                $t / $T     - EffectPeriode; per EffectIdx
                $u          - StackAmount
                $v          - MaxTargetLevel
                $x          - MaxAffectedTargets
                $z          - no place like <Home>

            deviations from standard procedures
                division    - example: $/10;2687s1 => $2687s1/10
                            - also:    $61829/5;s1 => $61829s1/5

            functions in use .. caseInsensitive
                $cond(a, b, c) - like SQL, if A is met use B otherwise use C
                $eq(a, b)      - a == b
                $floor(a)      - floor()
                $gt(a, b)      - a > b
                $gte(a, b)     - a >= b
                $min(a, b)     - min()
                $max(a, b)     - max()
        */

        $this->charLevel   = $level;

        // step -1: already handled?
        if (isset($this->parsedText[$this->id][$type][Lang::getLocale()->value][$this->charLevel][(int)$this->interactive]))
            return $this->parsedText[$this->id][$type][Lang::getLocale()->value][$this->charLevel][(int)$this->interactive];

        // step 0: get text
        $data = $this->getField($type, true);
        if (empty($data) || $data == "[]")                  // empty tooltip shouldn't be displayed anyway
            return ['', [], false];

        // step 1: if the text is supplemented with text-variables, get and replace them
        if ($this->curTpl['spellDescriptionVariableId'] > 0)
        {
            if (empty($this->spellVars[$this->id]))
            {
                $spellVars = DB::Aowow()->SelectCell('SELECT `vars` FROM ?_spellvariables WHERE `id` = ?d', $this->curTpl['spellDescriptionVariableId']);
                $spellVars = explode("\n", $spellVars);
                foreach ($spellVars as $sv)
                    if (preg_match('/\$(\w*\d*)=(.*)/i', trim($sv), $matches))
                        $this->spellVars[$this->id][$matches[1]] = $matches[2];
            }

            // replace self-references
            $reset = true;
            while ($reset)
            {
                $reset = false;
                foreach ($this->spellVars[$this->id] as $k => $sv)
                {
                    if (preg_match('/\$<(\w*\d*)>/i', $sv, $matches))
                    {
                        $this->spellVars[$this->id][$k] = str_replace('$<'.$matches[1].'>', '${'.$this->spellVars[$this->id][$matches[1]].'}', $sv);
                        $reset = true;
                    }
                }
            }

            // finally, replace SpellDescVars
            foreach ($this->spellVars[$this->id] as $k => $sv)
                $data = str_replace('$<'.$k.'>', $sv, $data);
        }

        // step 2: resolving conditions
        // aura- or spell-conditions cant be resolved for our purposes, so force them to false for now (todo (low): strg+f "know" in aowowPower.js ^.^)

        /* sequences
           a) simple    - $?cond[A][B]                      // simple case of b)
           b) elseif    - $?cond[A]?cond[B]..[C]            // can probably be repeated as often as you wanted
           c) recursive - $?cond[A][$?cond[B][..]]          // can probably be stacked as deep as you wanted

           only case a) can be used for KNOW-parameter
       */

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
        $this->parsedText[$this->id][$type][Lang::getLocale()->value][$this->charLevel][(int)$this->interactive] = [$data, $relSpells, $this->scaling[$this->id]];

        return [$data, $relSpells, $this->scaling[$this->id]];
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

            if ($statId && Util::checkNumeric($formOutVal) && $this->interactive)
                $resolved = sprintf($formOutStr, $statId, abs($formOutVal), sprintf(Util::$setRatingLevelString, $this->charLevel, $statId, abs($formOutVal), Util::setRatingLevel($this->charLevel, $statId, abs($formOutVal))));
            else if ($statId && Util::checkNumeric($formOutVal))
                $resolved = sprintf($formOutStr, $statId, abs($formOutVal), Util::setRatingLevel($this->charLevel, $statId, abs($formOutVal)));
            else
                $resolved = sprintf($formOutStr, Util::checkNumeric($formOutVal) ? abs($formOutVal) : $formOutVal);

            $data = substr_replace($data, $resolved, $formStartPos, ($formCurPos - $formStartPos));
        }

        return $data;
    }

    private function handleVariables(string $data, bool $topLevel = false) : string
    {
        $pos = 0;                                           // continue strpos-search from this offset
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

            [$minPoints, $maxPoints, $fmtStringMin, $fmtStringMax, $statId] = $this->resolveVariableString($varParts);
            $resolved = is_numeric($minPoints) ? abs($minPoints) : $minPoints;
            if (isset($fmtStringMin))
            {
                if (isset($statId) && $this->interactive)
                    $resolved = sprintf($fmtStringMin, $statId, abs($minPoints), sprintf(Util::$setRatingLevelString, $this->charLevel, $statId, abs($minPoints), Util::setRatingLevel($this->charLevel, $statId, abs($minPoints))));
                else if (isset($statId))
                    $resolved = sprintf($fmtStringMin, $statId, abs($minPoints), Util::setRatingLevel($this->charLevel, $statId, abs($minPoints)));
                else
                    $resolved = sprintf($fmtStringMin, $resolved);
            }

            if (isset($maxPoints) && $minPoints != $maxPoints && !isset($statId))
            {
                $_ = is_numeric($maxPoints) ? abs($maxPoints) : $maxPoints;
                $resolved .= Lang::game('valueDelim');
                $resolved .= isset($fmtStringMax) ? sprintf($fmtStringMax, $_) : $_;
            }

            $str .= $resolved;
        }
        $str .= substr($data, $pos);
        $str = str_replace('#', '$', $str);                 // reset marker

        return $str;
    }

    private function handleConditions(string $data, array &$relSpells, bool $topLevel = false) : string
    {
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
                trigger_error('SpellList::handleConditions() - string contains unbalanced condition', E_USER_WARNING);
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

    public function renderBuff($level = MAX_LEVEL, $interactive = false, ?array &$buffSpells = []) : ?string
    {
        $buffSpells = [];

        if (!$this->curTpl)
            return null;

        // doesn't have a buff
        if (!$this->getField('buff', true))
            return null;

        $this->interactive = $interactive;
        $this->charLevel   = $level;

        $x = '<table><tr>';

        // spellName
        $x .= '<td><b class="q">'.$this->getField('name', true).'</b></td>';

        // dispelType (if applicable)
        if ($this->curTpl['dispelType'])
            if ($dispel = Lang::game('dt', $this->curTpl['dispelType']))
                $x .= '<th><b class="q">'.$dispel.'</b></th>';

        $x .= '</tr></table>';

        $x .= '<table><tr><td>';

        // parse Buff-Text
        [$buffTT, $buffSp, ] = $this->parseText('buff');

        $buffSpells = Util::parseHtmlText($buffSp);

        $x .= $buffTT.'<br />';

        // duration
        if ($this->curTpl['duration'] > 0 && !($this->curTpl['attributes5'] & SPELL_ATTR5_HIDE_DURATION))
            $x .= '<span class="q">'.Lang::formatTime($this->curTpl['duration'], 'spell', 'timeRemaining').'<span>';

        $x .= '</td></tr></table>';

        $min = $this->scaling[$this->id] ? ($this->getField('baseLevel') ?: 1) : 1;
        $max = $this->scaling[$this->id] ? MAX_LEVEL : 1;
        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':'.$min.':'.$max.':'.min($this->charLevel, $max).'-->';

        return $x;
    }

    public function renderTooltip(?int $level = MAX_LEVEL, ?bool $interactive = false, ?array &$ttSpells = []) : ?string
    {
        $ttSpells = [];

        if (!$this->curTpl)
            return null;

        $this->interactive = $interactive;
        $this->charLevel   = $level;

        // fetch needed texts
        $name  = $this->getField('name', true);
        $rank  = $this->getField('rank', true);
        $tools = $this->getToolsForCurrent();
        $cool  = $this->createCooldownForCurrent();
        $cast  = $this->createCastTimeForCurrent();
        $cost  = $this->createPowerCostForCurrent();
        $range = $this->createRangesForCurrent();

        [$desc, $spells, ] = $this->parseText('description');

        $ttSpells = Util::parseHtmlText($spells);

        // get reagents
        $reagents = $this->getReagentsForCurrent();
        foreach ($reagents as $k => $r)
        {
            if ($item = $this->relItems->getEntry($r[0]))
                $reagents[$k] += [2 => new LocString($item), 3 => true];
            else
                $reagents[$k] += [2 => 'Item #'.$r[0], 3 => false];
        }

        $reagents = array_reverse($reagents);

        // get stances
        $stances = '';
        if ($this->curTpl['stanceMask'] && !($this->curTpl['attributes2'] & SPELL_ATTR2_NOT_NEED_SHAPESHIFT))
            $stances = Lang::game('requires2').' '.Lang::getStances($this->curTpl['stanceMask']);

        // get item requirement (skip for professions)
        $reqItems = '';
        if ($this->curTpl['typeCat'] != 11)
        {
            $class    = $this->getField('equippedItemClass');
            $mask     = $this->getField('equippedItemSubClassMask');
            $reqItems = Lang::getRequiredItems($class, $mask);
        }

        // get created items (may need improvement)
        $createItem = '';
        if (in_array($this->curTpl['typeCat'], [9, 11]))    // only Professions
        {
            foreach ($this->canCreateItem() as $idx)
            {
                // has createItem Scroll of Enchantment
                if ($this->curTpl['effect'.$idx.'Id'] == SPELL_EFFECT_ENCHANT_ITEM)
                    continue;

                foreach ($this->relItems->iterate() as $cId => $__)
                {
                    if ($cId != $this->curTpl['effect'.$idx.'CreateItemId'])
                        continue;

                    $createItem = $this->relItems->renderTooltip(true, $this->id);
                    break 2;
                }
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

                if (!empty($tools))
                    $_ .= ', ';
                else
                    $_ .= '<br />';
            }

            $xTmp[] = $_.'</div>';
        }

        if ($reagents)
        {
            $_ = Lang::spell('reagents').':<br/><div class="indent q1">';
            while ([$iId, $qty, $text, $exists] = array_pop($reagents))
            {
                $_ .= $exists ? '<a href="?item='.$iId.'">'.$text.'</a>' : $text;
                if ($qty > 1)
                    $_ .= ' ('.$qty.')';

                $_ .= empty($reagents) ? '<br />' : ', ';
            }

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

        $min = $this->scaling[$this->id] ? ($this->getField('baseLevel') ?: 1) : 1;
        $max = $this->scaling[$this->id] ? ($this->getField('maxLevel') ?: MAX_LEVEL) : 1;
        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':'.$min.':'.$max.':'.min($this->charLevel, $max).'-->';

        return $x;
    }

    public function getTalentHeadForCurrent() : string
    {
        // power cost: pct over static
        $cost = $this->createPowerCostForCurrent();

        // ranges
        $range = $this->createRangesForCurrent();

        // cast times
        $cast = $this->createCastTimeForCurrent();

        // cooldown or categorycooldown
        $cool = $this->createCooldownForCurrent();

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

    public function getColorsForCurrent() : array
    {
        $gry = $this->curTpl['skillLevelGrey'];
        $ylw = $this->curTpl['skillLevelYellow'];
        $grn = (int)(($ylw + $gry) / 2);
        $org = $this->curTpl['learnedAt'];

        if ($ylw < $org)
            $ylw = 0;

        if ($grn < $org || $grn < $ylw)
            $grn = 0;

        if ($org >= $ylw || $org >= $grn || $org >= $gry)
            $org = 0;

        return $gry > 1 ? [$org, $ylw, $grn, $gry] : [];
    }

    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data = [];

        if ($addInfoMask & ITEMINFO_MODEL)
            $modelInfo = $this->getModelInfo();

        foreach ($this->iterate() as $__)
        {
            $quality = ($this->curTpl['cuFlags'] & SPELL_CU_QUALITY_MASK) >> 8;
            $talent  = $this->curTpl['cuFlags'] & (SPELL_CU_TALENT | SPELL_CU_TALENTSPELL) && $this->curTpl['spellLevel'] <= 1;

            $data[$this->id] = array(
                'id'           => $this->id,
                'name'         => ($quality ?: '@').$this->getField('name', true),
                'icon'         => $this->curTpl['iconStringAlt'] ?: $this->curTpl['iconString'],
                'level'        => $talent ? $this->curTpl['talentLevel'] : $this->curTpl['spellLevel'],
                'school'       => $this->curTpl['schoolMask'],
                'cat'          => $this->curTpl['typeCat'],
                'trainingcost' => $this->curTpl['trainingCost'],
                'skill'        => count($this->curTpl['skillLines']) > 4 ? array_merge(array_splice($this->curTpl['skillLines'], 0, 4), [-1]): $this->curTpl['skillLines'], // display max 4 skillLines (fills max three lines in listview)
                'reagents'     => array_values($this->getReagentsForCurrent()),
                'source'       => []
             // 'talentspec'   => $this->curTpl['skillLines'][0]      not used: g_chr_specs has the wrong structure for it; also setting .cat and .type does the same
            );

            // Sources
            if ($this->getSources($s, $sm))
            {
                $data[$this->id]['source'] = $s;
                if ($sm)
                    $data[$this->id]['sourcemore'] = $sm;
            }

            // Proficiencies
            if ($this->curTpl['typeCat'] == -11)
                foreach (self::$spellTypes as $cat => $type)
                    if (in_array($this->curTpl['skillLines'][0], self::$skillLines[$cat]))
                        $data[$this->id]['type'] = $type;

            // creates item
            foreach ($this->canCreateItem() as $idx)
            {
                $max = $this->curTpl['effect'.$idx.'DieSides'] + $this->curTpl['effect'.$idx.'BasePoints'];
                $min = $this->curTpl['effect'.$idx.'DieSides'] > 1 ? 1 : $max;

                $data[$this->id]['creates'] = [$this->curTpl['effect'.$idx.'CreateItemId'], $min, $max];
                break;
            }

            // Profession
            if (in_array($this->curTpl['typeCat'], [9, 11]))
            {
                if ($la = $this->curTpl['learnedAt'])
                    $data[$this->id]['learnedat'] = $la;
                else if (($la = $this->curTpl['reqSkillLevel']) > 1)
                    $data[$this->id]['learnedat'] = $la;

                $data[$this->id]['colors'] = $this->getColorsForCurrent();
            }

            // glyph
            if ($this->curTpl['typeCat'] == -13)
                $data[$this->id]['glyphtype'] = $this->curTpl['cuFlags'] & SPELL_CU_GLYPH_MAJOR ? 1 : 2;

            if ($r = $this->getField('rank', true))
                $data[$this->id]['rank'] = $r;

            if ($mask = $this->curTpl['reqClassMask'])
                $data[$this->id]['reqclass'] = $mask;

            if ($mask = $this->curTpl['reqRaceMask'])
                $data[$this->id]['reqrace'] = $mask;

            if ($addInfoMask & ITEMINFO_MODEL)
            {
                // may have multiple models set, in this case i've no idea what should be picked
                for ($i = 1; $i < 4; $i++)
                {
                    if (!empty($modelInfo[$this->id][$i]))
                    {
                        $data[$this->id]['npcId']       = $modelInfo[$this->id][$i]['typeId'];
                        $data[$this->id]['displayId']   = $modelInfo[$this->id][$i]['displayId'];
                        $data[$this->id]['displayName'] = $modelInfo[$this->id][$i]['displayName'];
                        break;
                    }
                }
            }
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_SELF, ?array &$extra = []) : array
    {
        $data  = [];

        if ($this->relItems && ($addMask & GLOBALINFO_RELATED))
            $data = $this->relItems->getJSGlobals();

        foreach ($this->iterate() as $id => $__)
        {
            if ($addMask & GLOBALINFO_RELATED)
            {
                foreach (ChrClass::fromMask($this->curTpl['reqClassMask']) as $cId)
                    $data[Type::CHR_CLASS][$cId] = $cId;

                foreach (ChrRace::fromMask($this->curTpl['reqRaceMask']) as $rId)
                    $data[Type::CHR_RACE][$rId] = $rId;

                // play sound effect
                for ($i = 1; $i < 4; $i++)
                    if ($this->getField('effect'.$i.'Id') == SPELL_EFFECT_PLAY_SOUND || $this->getField('effect'.$i.'Id') == SPELL_EFFECT_PLAY_MUSIC)
                        $data[Type::SOUND][$this->getField('effect'.$i.'MiscValue')] = $this->getField('effect'.$i.'MiscValue');
            }

            if ($addMask & GLOBALINFO_SELF)
            {
                $data[Type::SPELL][$id] = array(
                    'icon' => $this->curTpl['iconStringAlt'] ?: $this->curTpl['iconString'],
                    'name' => $this->getField('name', true)
                );

                if (($_ = $this->curTpl['typeCat']) && in_array($_, [-5, -6, 9, 11]))
                    $data[Type::SPELL][$id]['completion_category'] = $_;
            }

            if ($addMask & GLOBALINFO_EXTRA)
            {
                $buff = $this->renderBuff(MAX_LEVEL, true, $buffSpells);
                $tTip = $this->renderTooltip(MAX_LEVEL, true, $spells);

                foreach ($spells as $relId => $_)
                    if (empty($data[Type::SPELL][$relId]))
                        $data[Type::SPELL][$relId] = $relId;

                foreach ($buffSpells as $relId => $_)
                    if (empty($data[Type::SPELL][$relId]))
                        $data[Type::SPELL][$relId] = $relId;

                $extra[$id] = array(
                 // 'id'         => $id,
                    'tooltip'    => $tTip,
                    'buff'       => $buff ?: null,
                    'spells'     => $spells,
                    'buffspells' => $buffSpells ?: null
                );
            }
        }

        return $data;
    }

    // mostly similar to TC
    public function getCastingTimeForBonus(bool $asDOT = false) : int
    {
        $areaTargets = [7, 8, 15, 16, 20, 24, 30, 31, 33, 34, 37, 54, 56, 59, 104, 108];
        $castingTime = $this->IsChanneledSpell() ? $this->curTpl['duration'] : ($this->curTpl['castTime'] * 1000);

        if (!$castingTime)
            return 3500;

        if ($castingTime > 7000)
            $castingTime = 7000;

        if ($castingTime < 1500)
            $castingTime = 1500;

        if ($asDOT && !$this->isChanneledSpell())
            $castingTime = 3500;

        $overTime = 0;
        $nEffects = 0;
        $isDirect = false;
        $isArea   = false;

        for ($i = 1; $i <= 3; $i++)
        {
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellLIst::EFFECTS_DIRECT_SCALING))
                $isDirect = true;
            else if (in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::AURAS_PERIODIC_SCALING))
                if ($_ = $this->curTpl['duration'])
                    $overTime = $_;
            else if ($this->curTpl['effect'.$i.'AuraId'])
                $nEffects++;

            if (in_array($this->curTpl['effect'.$i.'ImplicitTargetA'], $areaTargets) || in_array($this->curTpl['effect'.$i.'ImplicitTargetB'], $areaTargets))
                $isArea = true;
        }

        // Combined Spells with Both Over Time and Direct Damage
        if ($overTime > 0 && $castingTime > 0 && $isDirect)
        {
            // mainly for DoTs which are 3500 here otherwise
            $originalCastTime = $this->curTpl['castTime'] * 1000;
            if ($this->curTpl['attributes0'] & SPELL_ATTR0_REQ_AMMO)
                $originalCastTime += 500;

            if ($originalCastTime > 7000)
                $originalCastTime = 7000;

            if ($originalCastTime < 1500)
                $originalCastTime = 1500;

            // Portion to Over Time
            $PtOT = ($overTime / 15000) / (($overTime / 15000) + ($originalCastTime / 3500));

            if ($asDOT)
                $castingTime = $castingTime * $PtOT;
            else if ($PtOT < 1)
                $castingTime  = $castingTime * (1 - $PtOT);
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

    public function getSourceData(int $id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($id && $id != $this->id)
                continue;

            $data[$this->id] = array(
                'n'    => $this->getField('name', true),
                't'    => Type::SPELL,
                'ti'   => $this->id,
                's'    => empty($this->curTpl['skillLines']) ? 0 : $this->curTpl['skillLines'][0],
                'c'    => $this->curTpl['typeCat'],
                'icon' => $this->curTpl['iconStringAlt'] ?: $this->curTpl['iconString'],
            );
        }

        return $data;
    }
}


class SpellListFilter extends Filter
{
    const MAX_SPELL_EFFECT = 167;
    const MAX_SPELL_AURA   = 316;

    public static array $attributesFilter = array(          // attrFieldId => [attrBit => cr, ...]; if cr < 0 ? filter is negated
        0 => array(
            SPELL_ATTR0_REQ_AMMO                      =>  48,
            SPELL_ATTR0_ON_NEXT_SWING                 =>  49,
            SPELL_ATTR0_PASSIVE                       =>  50,
            SPELL_ATTR0_HIDDEN_CLIENTSIDE             =>  51,
            SPELL_ATTR0_HIDE_IN_COMBAT_LOG            =>  84,
            SPELL_ATTR0_ON_NEXT_SWING_2               =>  52,
            SPELL_ATTR0_DAYTIME_ONLY                  =>  53,
            SPELL_ATTR0_NIGHT_ONLY                    =>  54,
            SPELL_ATTR0_INDOORS_ONLY                  =>  55,
            SPELL_ATTR0_OUTDOORS_ONLY                 =>  56,
            SPELL_ATTR0_NOT_SHAPESHIFT                => -31,
            SPELL_ATTR0_ONLY_STEALTHED                =>  38,
            SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION      =>  58,
            SPELL_ATTR0_STOP_ATTACK_TARGET            =>  59,
            SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK  =>  60,
            SPELL_ATTR0_CASTABLE_WHILE_DEAD           =>  61,
            SPELL_ATTR0_CASTABLE_WHILE_MOUNTED        =>  62,
            SPELL_ATTR0_DISABLED_WHILE_ACTIVE         =>  63,
            SPELL_ATTR0_NEGATIVE_1                    =>  69,
            SPELL_ATTR0_CASTABLE_WHILE_SITTING        =>  64,
            SPELL_ATTR0_CANT_USED_IN_COMBAT           => -33,
            SPELL_ATTR0_UNAFFECTED_BY_INVULNERABILITY =>  46,
            SPELL_ATTR0_CANT_CANCEL                   =>  57
        ),
        1 => array(
            SPELL_ATTR1_DRAIN_ALL_POWER             => 65,
            SPELL_ATTR1_CHANNELED_1                 => 27,  // general filter
            SPELL_ATTR1_NOT_BREAK_STEALTH           => 68,
            SPELL_ATTR1_CHANNELED_2                 => 66,  // attributes filter
            SPELL_ATTR1_CANT_BE_REFLECTED           => 67,  // WH - 69: all effects are harmful points here
            SPELL_ATTR1_CANT_TARGET_IN_COMBAT       => 70,
            SPELL_ATTR1_NO_THREAT                   => 71,
            SPELL_ATTR1_IS_PICKPOCKET               => 72,
            SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY    => 73,
            SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE => 47,
            SPELL_ATTR1_IS_FISHING                  => 74
        ),
        2 => array(
            SPELL_ATTR2_CANT_TARGET_TAPPED        =>  75,
            SPELL_ATTR2_PRESERVE_ENCHANT_IN_ARENA =>  76,
            SPELL_ATTR2_NOT_NEED_SHAPESHIFT       =>  77,
            SPELL_ATTR2_CANT_CRIT                 => -34,
            SPELL_ATTR2_FOOD_BUFF                 =>  78
        ),
        3 => array(
            SPELL_ATTR3_ONLY_TARGET_PLAYERS =>  79,
            SPELL_ATTR3_MAIN_HAND           =>  80,
            SPELL_ATTR3_BATTLEGROUND        =>  43,
            SPELL_ATTR3_NO_INITIAL_AGGRO    =>  81,
            SPELL_ATTR3_DEATH_PERSISTENT    =>  36,
            SPELL_ATTR3_IGNORE_HIT_RESULT   => -35,
            SPELL_ATTR3_REQ_WAND            =>  82,         // unused attribute
            SPELL_ATTR3_REQ_OFFHAND         =>  83
        ),
        4 => array(
            SPELL_ATTR4_FADES_WHILE_LOGGED_OUT =>  85,
            SPELL_ATTR4_NOT_STEALABLE          => -39,
            SPELL_ATTR4_NOT_USABLE_IN_ARENA    => -44,
            SPELL_ATTR4_USABLE_IN_ARENA        =>  44
        ),
        5 => array(
            SPELL_ATTR5_USABLE_WHILE_STUNNED    => 42,
            SPELL_ATTR5_SINGLE_TARGET_SPELL     => 86,
            SPELL_ATTR5_START_PERIODIC_AT_APPLY => 87,
            SPELL_ATTR5_USABLE_WHILE_FEARED     => 89,
            SPELL_ATTR5_USABLE_WHILE_CONFUSED   => 88
        ),
        6 => array(
            SPELL_ATTR6_ONLY_IN_ARENA        => 90,         // unused attribute
            SPELL_ATTR6_NOT_IN_RAID_INSTANCE => 91
        ),
        7 => array(
            SPELL_ATTR7_DISABLE_AURA_WHILE_DEAD => 92,      // aka Paladin Aura
            SPELL_ATTR7_SUMMON_PLAYER_TOTEM     => 93
        )
    );

    protected string $type  = 'spells';
    protected static array $enums = array(
        9 => array(                                         // sources index
            1  => true,                                     // Any
            2  => false,                                    // None
            3  =>  SRC_CRAFTED,
            4  =>  SRC_DROP,
            6  =>  SRC_QUEST,
            7  =>  SRC_VENDOR,
            8  =>  SRC_TRAINER,
            9  =>  SRC_DISCOVERY,
            10 =>  SRC_TALENT
        ),
        22 => array(
            1 => true,                                      // Weapons
            2 => true,                                      // Armor
            3 => true,                                      // Armor Proficiencies
            4 => true,                                      // Armor Specializations
            5 => true                                       // Languages
        ),
        40 => array(                                        // damage class index
            1 => 0,                                         // none
            2 => 1,                                         // magic
            3 => 2,                                         // melee
            4 => 3                                          // ranged
        ),
        45 => array(                                        // power type index
          // 1 => ??,                                       // burning embers
          // 2 => ??,                                       // chi
          // 3 => ??,                                       // demonic fury
             4 => POWER_ENERGY,                             // energy
             5 => POWER_FOCUS,                              // focus
             6 => POWER_HEALTH,                             // health
          // 7 => ??,                                       // holy power
             8 => POWER_MANA,                               // mana
             9 => POWER_RAGE,                               // rage
            10 => POWER_RUNE,                               // runes
            11 => POWER_RUNIC_POWER,                        // runic power
         // 12 => ??,                                       // shadow orbs
         // 13 => ??,                                       // soul shard
            14 => POWER_HAPPINESS,                          // happiness        v custom v
            15 => -1,                                       // ammo
            16 => -41,                                      // pyrite
            17 => -61,                                      // steam pressure
            18 => -101,                                     // heat
            19 => -121,                                     // ooze
            20 => -141,                                     // blood power
            21 => -142                                      // wrath
        )
    );

    protected static array $genericFilter = array(
         1  => [parent::CR_CALLBACK,  'cbCost',                                                                                   ], // costAbs [op] [int]
         2  => [parent::CR_NUMERIC,   'powerCostPercent', NUM_CAST_INT                                                            ], // prcntbasemanarequired
         3  => [parent::CR_BOOLEAN,   'spellFocusObject'                                                                          ], // requiresnearbyobject
         4  => [parent::CR_NUMERIC,   'trainingcost',     NUM_CAST_INT                                                            ], // trainingcost
         5  => [parent::CR_BOOLEAN,   'reqSpellId'                                                                                ], // requiresprofspec
         8  => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_SCREENSHOT                                                   ], // hasscreenshots
         9  => [parent::CR_CALLBACK,  'cbSource',                                                                                 ], // source [enum]
        10  => [parent::CR_FLAG,      'cuFlags',          SPELL_CU_FIRST_RANK                                                     ], // firstrank
        11  => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_COMMENT                                                      ], // hascomments
        12  => [parent::CR_FLAG,      'cuFlags',          SPELL_CU_LAST_RANK                                                      ], // lastrank
        13  => [parent::CR_NUMERIC,   'rankNo',           NUM_CAST_INT                                                            ], // rankno
        14  => [parent::CR_NUMERIC,   'id',               NUM_CAST_INT,                            true                           ], // id
        15  => [parent::CR_STRING,    'ic.name',                                                                                  ], // icon
        17  => [parent::CR_FLAG,      'cuFlags',          CUSTOM_HAS_VIDEO                                                        ], // hasvideos
        19  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION                                    ], // scaling
        20  => [parent::CR_CALLBACK,  'cbReagents',                                                                               ], // has Reagents [yn]
        22  => [parent::CR_CALLBACK,  'cbProficiency',   null,                                     null                           ], // proficiencytype [proficiencytype]
     // 26  => [parent::CR_NUMERIC,   'startRecoveryCategory', NUM_CAST_INT,                       false                          ], // gcd-cat
        25  => [parent::CR_BOOLEAN,   'skillLevelYellow'                                                                          ], // rewardsskillups
        27  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CHANNELED_1,                 true                           ], // channeled [yn]
        28  => [parent::CR_NUMERIC,   'castTime',         NUM_CAST_FLOAT                                                          ], // casttime [num]
        29  => [parent::CR_CALLBACK,  'cbAuraNames',                                                                              ], // appliesaura [effectauranames]
        31  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_NOT_SHAPESHIFT                                              ], // usablewhenshapeshifted [yn]
        33  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes0',                           SPELL_ATTR0_CANT_USED_IN_COMBAT], // combatcastable [yn]
        34  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes2',                           SPELL_ATTR2_CANT_CRIT          ], // chancetocrit [yn]
        35  => [parent::CR_CALLBACK,  'cbInverseFlag',    'attributes3',                           SPELL_ATTR3_IGNORE_HIT_RESULT  ], // chancetomiss [yn]
        36  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_DEATH_PERSISTENT                                            ], // persiststhroughdeath [yn]
        38  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_ONLY_STEALTHED                                              ], // requiresstealth [yn]
        39  => [parent::CR_FLAG,      'attributes4',      SPELL_ATTR4_NOT_STEALABLE                                               ], // spellstealable [yn]
        40  => [parent::CR_ENUM,      'damageClass'                                                                               ], // damagetype [damagetype]
        41  => [parent::CR_FLAG,      'stanceMask',       (1 << (22 - 1))                                                         ], // requiresmetamorphosis [yn]
        42  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_STUNNED                                        ], // usablewhenstunned [yn]
        44  => [parent::CR_FLAG,      'attributes4',      SPELL_ATTR4_USABLE_IN_ARENA                                             ], // usableinarenas [yn]
        45  => [parent::CR_ENUM,      'powerType'                                                                                 ], // resourcetype [resourcetype]
        46  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_UNAFFECTED_BY_INVULNERABILITY                               ], // disregardimmunity [yn]
        47  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE                                 ], // disregardschoolimmunity [yn]
        48  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_REQ_AMMO                                                    ], // reqrangedweapon [yn]
        49  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_ON_NEXT_SWING                                               ], // onnextswingplayers [yn]
        50  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_PASSIVE                                                     ], // passivespell [yn]
        51  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_DONT_DISPLAY_IN_AURA_BAR                                    ], // hiddenaura [yn]
        52  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_ON_NEXT_SWING_2                                             ], // onnextswingnpcs [yn]
        53  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_DAYTIME_ONLY                                                ], // daytimeonly [yn]
        54  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_NIGHT_ONLY                                                  ], // nighttimeonly [yn]
        55  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_INDOORS_ONLY                                                ], // indoorsonly [yn]
        56  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_OUTDOORS_ONLY                                               ], // outdoorsonly [yn]
        57  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CANT_CANCEL                                                 ], // uncancellableaura [yn]
        58  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION                                    ], // damagedependsonlevel [yn]
        59  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_STOP_ATTACK_TARGET                                          ], // stopsautoattack [yn]
        60  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK                                ], // cannotavoid [yn]
        61  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_DEAD                                         ], // usabledead [yn]
        62  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_MOUNTED                                      ], // usablemounted [yn]
        63  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_DISABLED_WHILE_ACTIVE                                       ], // delayedrecoverystarttime [yn]
        64  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_SITTING                                      ], // usablesitting [yn]
        65  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_DRAIN_ALL_POWER                                             ], // usesallpower [yn]
        66  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CHANNELED_2                                                 ], // channeled [yn]
        67  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CANT_BE_REFLECTED                                           ], // cannotreflect [yn]
        68  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_NOT_BREAK_STEALTH                                           ], // usablestealthed [yn]
        69  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_NEGATIVE_1                                                  ], // harmful [yn]  -  WH interprets attributes1 0x80 as "all effects are harmful", but it really is CANT_BE_REFLECTED. So here is an approximation.
        70  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_CANT_TARGET_IN_COMBAT                                       ], // targetnotincombat [yn]
        71  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_NO_THREAT                                                   ], // nothreat [yn]
        72  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_IS_PICKPOCKET                                               ], // pickpocket [yn]
        73  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY                                    ], // dispelauraonimmunity [yn]
        74  => [parent::CR_FLAG,      'attributes1',      SPELL_ATTR1_IS_FISHING                                                  ], // reqfishingpole [yn]
        75  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_CANT_TARGET_TAPPED                                          ], // requntappedtarget [yn]
        76  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_PRESERVE_ENCHANT_IN_ARENA                                   ], // targetownitem [yn
        77  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_NOT_NEED_SHAPESHIFT                                         ], // doesntreqshapeshift [yn]
        78  => [parent::CR_FLAG,      'attributes2',      SPELL_ATTR2_FOOD_BUFF                                                   ], // foodbuff [yn]
        79  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_ONLY_TARGET_PLAYERS                                         ], // targetonlyplayer [yn]
        80  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_MAIN_HAND                                                   ], // reqmainhand [yn]
        81  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_NO_INITIAL_AGGRO                                            ], // doesntengagetarget [yn]
        82  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_REQ_WAND                                                    ], // reqwand [yn]
        83  => [parent::CR_FLAG,      'attributes3',      SPELL_ATTR3_REQ_OFFHAND                                                 ], // reqoffhand [yn]
        84  => [parent::CR_FLAG,      'attributes0',      SPELL_ATTR0_HIDE_IN_COMBAT_LOG                                          ], // nolog [yn]
        85  => [parent::CR_FLAG,      'attributes4',      SPELL_ATTR4_FADES_WHILE_LOGGED_OUT                                      ], // auratickswhileloggedout [yn]
        86  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_SINGLE_TARGET_SPELL                                         ], // onlyaffectsonetarget [yn]
        87  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_START_PERIODIC_AT_APPLY                                     ], // startstickingatapplication [yn]
        88  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_CONFUSED                                       ], // usableconfused [yn]
        89  => [parent::CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_FEARED                                         ], // usablefeared [yn]
        90  => [parent::CR_FLAG,      'attributes6',      SPELL_ATTR6_ONLY_IN_ARENA                                               ], // onlyarena [yn]
        91  => [parent::CR_FLAG,      'attributes6',      SPELL_ATTR6_NOT_IN_RAID_INSTANCE                                        ], // notinraid [yn]
        92  => [parent::CR_FLAG,      'attributes7',      SPELL_ATTR7_DISABLE_AURA_WHILE_DEAD                                     ], // paladinaura [yn]
        93  => [parent::CR_FLAG,      'attributes7',      SPELL_ATTR7_SUMMON_PLAYER_TOTEM                                         ], // totemspell [yn]
        95  => [parent::CR_CALLBACK,  'cbBandageSpell'                                                                            ], // bandagespell [yn]  -  was that an attribute at one point?
        96  => [parent::CR_STAFFFLAG, 'attributes0'                                                                               ], // flags1 [flags]
        97  => [parent::CR_STAFFFLAG, 'attributes1'                                                                               ], // flags2 [flags]
        98  => [parent::CR_STAFFFLAG, 'attributes2'                                                                               ], // flags3 [flags]
        99  => [parent::CR_STAFFFLAG, 'attributes3'                                                                               ], // flags4 [flags]
        100 => [parent::CR_STAFFFLAG, 'attributes4'                                                                               ], // flags5 [flags]
        101 => [parent::CR_STAFFFLAG, 'attributes5'                                                                               ], // flags6 [flags]
        102 => [parent::CR_STAFFFLAG, 'attributes6'                                                                               ], // flags7 [flags]
        103 => [parent::CR_STAFFFLAG, 'attributes7'                                                                               ], // flags8 [flags]
        104 => [parent::CR_STAFFFLAG, 'targets'                                                                                   ], // flags9 [flags]
        105 => [parent::CR_STAFFFLAG, 'stanceMaskNot'                                                                             ], // flags10 [flags]
        106 => [parent::CR_STAFFFLAG, 'spellFamilyFlags1'                                                                         ], // flags11 [flags]
        107 => [parent::CR_STAFFFLAG, 'spellFamilyFlags2'                                                                         ], // flags12 [flags]
        108 => [parent::CR_STAFFFLAG, 'spellFamilyFlags3'                                                                         ], // flags13 [flags]
        109 => [parent::CR_CALLBACK,  'cbEffectNames',                                                                            ], // effecttype [effecttype]
     // 110 => [parent::CR_NYI_PH,    null,               null,                                    null                           ], // scalingap [yn]  // unreasonably complex for now
     // 111 => [parent::CR_NYI_PH,    null,               null,                                    null                           ], // scalingsp [yn]  // unreasonably complex for now
        114 => [parent::CR_CALLBACK,  'cbReqFaction'                                                                              ], // requiresfaction [side]
        116 => [parent::CR_BOOLEAN,   'startRecoveryTime'                                                                         ]  // onGlobalCooldown [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE,    [1, 116],                                          true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]], true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                               true ], // criteria values - only printable chars, no delimiters
        'na'    => [parent::V_REGEX,    parent::PATTERN_NAME,                              false], // name / text - only printable chars, no delimiter
        'ex'    => [parent::V_EQUAL,    'on',                                              false], // extended name search
        'ma'    => [parent::V_EQUAL,    1,                                                 false], // match any / all filter
        'minle' => [parent::V_RANGE,    [0, 99],                                           false], // spell level min
        'maxle' => [parent::V_RANGE,    [0, 99],                                           false], // spell level max
        'minrs' => [parent::V_RANGE,    [0, 999],                                          false], // required skill level min
        'maxrs' => [parent::V_RANGE,    [0, 999],                                          false], // required skill level max
        'ra'    => [parent::V_LIST,     [[1, 8], 10, 11],                                  false], // races
        'cl'    => [parent::V_CALLBACK, 'cbClasses',                                       true ], // classes
        'gl'    => [parent::V_CALLBACK, 'cbGlyphs',                                        true ], // glyph type
        'sc'    => [parent::V_RANGE,    [0, 6],                                            true ], // magic schools
        'dt'    => [parent::V_LIST,     [[1, 6], 9],                                       false], // dispel types
        'me'    => [parent::V_RANGE,    [1, 31],                                           false]  // mechanics
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        //string (extended)
        if ($_v['na'])
        {
            if ($_v['ex'] == 'on')
                if ($_ = $this->tokenizeString(['buff_loc'.Lang::getLocale()->value, 'description_loc'.Lang::getLocale()->value]))
                    $parts[] = $_;

            if ($_ = $this->buildMatchLookup(['name_loc'.Lang::getLocale()->value]))
            {
                if ($parts)
                    $parts[0][] = $_;
                else
                    $parts[] = $_;
            }
        }

        // spellLevel min                                   todo (low): talentSpells (typeCat -2) commonly have spellLevel 1 (and talentLevel >1) -> query is inaccurate
        if ($_v['minle'])
            $parts[] = ['spellLevel', $_v['minle'], '>='];

        // spellLevel max
        if ($_v['maxle'])
            $parts[] = ['spellLevel', $_v['maxle'], '<='];

        // skillLevel min
        if ($_v['minrs'])
            $parts[] = ['learnedAt', $_v['minrs'], '>='];

        // skillLevel max
        if ($_v['maxrs'])
            $parts[] = ['learnedAt', $_v['maxrs'], '<='];

        // race
        if ($_v['ra'])
            $parts[] = ['AND', [['reqRaceMask', ChrRace::MASK_ALL, '&'], ChrRace::MASK_ALL, '!'], ['reqRaceMask', $this->list2Mask([$_v['ra']]), '&']];

        // class [list]
        if ($_v['cl'])
            $parts[] = ['reqClassMask', $this->list2Mask($_v['cl']), '&'];

        // school [list]
        if ($_v['sc'])
            $parts[] = ['schoolMask', $this->list2Mask($_v['sc'], true), '&'];

        // glyph type [list]                                wonky, admittedly, but consult SPELL_CU_* in defines and it makes sense
        if ($_v['gl'])
            $parts[] = ['cuFlags', ($this->list2Mask($_v['gl']) << 6), '&'];

        // dispel type
        if ($_v['dt'])
            $parts[] = ['dispelType', $_v['dt']];

        // mechanic
        if ($_v['me'])
            $parts[] = ['OR', ['mechanic', $_v['me']], ['effect1Mechanic', $_v['me']], ['effect2Mechanic', $_v['me']], ['effect3Mechanic', $_v['me']]];

        return $parts;
    }

    protected function cbClasses(string &$val) : bool
    {
        if (!$this->parentCats || !in_array($this->parentCats[0], [-13, -2, 7]))
            return false;

        if (!Util::checkNumeric($val, NUM_CAST_INT))
            return false;

        $type  = parent::V_LIST;
        $valid = ChrClass::fromMask(ChrClass::MASK_ALL);

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbGlyphs(string &$val) : bool
    {
        if (!$this->parentCats || $this->parentCats[0] != -13)
            return false;

        if (!Util::checkNumeric($val, NUM_CAST_INT))
            return false;

        $type  = parent::V_LIST;
        $valid = [1, 2];

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbCost(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        return ['OR',
            ['AND', ['powerType', [POWER_RAGE, POWER_RUNIC_POWER]], ['powerCost', (10 * $crv), $crs]],
            ['AND', ['powerType', [POWER_RAGE, POWER_RUNIC_POWER], '!'], ['powerCost', $crv, $crs]]
        ];
    }

    protected function cbSource(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_int($_))                                     // specific
            return ['src.src'.$_, null, '!'];
        else if ($_)                                        // any
        {
            $foo = ['OR'];
            foreach (self::$enums[$cr] as $bar)
                if (is_int($bar))
                    $foo[] = ['src.src'.$bar, null, '!'];

            return $foo;
        }
        else                                                // none
            return ['src.typeId', null];

        return null;
    }

    protected function cbReagents(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return ['OR', ['reagent1', 0, '>'], ['reagent2', 0, '>'], ['reagent3', 0, '>'], ['reagent4', 0, '>'], ['reagent5', 0, '>'], ['reagent6', 0, '>'], ['reagent7', 0, '>'], ['reagent8', 0, '>']];
        else
            return ['AND', ['reagent1', 0], ['reagent2', 0], ['reagent3', 0], ['reagent4', 0], ['reagent5', 0], ['reagent6', 0], ['reagent7', 0], ['reagent8', 0]];
    }

    protected function cbAuraNames(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->checkInput(parent::V_RANGE, [1, self::MAX_SPELL_AURA], $crs))
            return null;

        return ['OR', ['effect1AuraId', $crs], ['effect2AuraId', $crs], ['effect3AuraId', $crs]];
    }

    protected function cbEffectNames(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->checkInput(parent::V_RANGE, [1, self::MAX_SPELL_EFFECT], $crs))
            return null;

        return ['OR', ['effect1Id', $crs], ['effect2Id', $crs], ['effect3Id', $crs]];
    }

    protected function cbInverseFlag(int $cr, int $crs, string $crv, string $field, int $flag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return [[$field, $flag, '&'], 0];
        else
            return [$field, $flag, '&'];
    }

    protected function cbSpellstealable(int $cr, int $crs, string $crv, string $field, int $flag) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return ['AND', [[$field, $flag, '&'], 0], ['dispelType', SPELL_DAMAGE_CLASS_MAGIC]];
        else
            return ['OR', [$field, $flag, '&'], ['dispelType', SPELL_DAMAGE_CLASS_MAGIC, '!']];
    }

    protected function cbReqFaction(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            // yes
            1 => ['reqRaceMask', 0, '!'],
            // alliance
            2 => ['AND', [['reqRaceMask', ChrRace::MASK_HORDE, '&'], 0], ['reqRaceMask', ChrRace::MASK_ALLIANCE, '&']],
            // horde
            3 => ['AND', [['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], 0], ['reqRaceMask', ChrRace::MASK_HORDE, '&']],
            // both
            4 => ['AND', ['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ['reqRaceMask', ChrRace::MASK_HORDE, '&']],
            // no
            5 => ['reqRaceMask', 0],
            default => null
        };
    }

    /* unused - for reference: attribute flag or item class mask */
    protected function cbEquippedWeapon(int $cr, int $crs, string $crv, int $mask, bool $useInvType) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        $field = $useInvType ? 'equippedItemInventoryTypeMask' : 'equippedItemSubClassMask';

        if ($crs)
            return ['AND', ['equippedItemClass', ITEM_CLASS_WEAPON], [$field, $mask, '&']];
        else
            return ['OR', ['equippedItemClass', ITEM_CLASS_WEAPON, '!'], [[$field, $mask, '&'], 0]];
    }

    /* unused - for reference: attribute flag or cooldown time constraint */
    protected function cbUsableInArena(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)
            return  ['AND',
                        [['attributes4', SPELL_ATTR4_NOT_USABLE_IN_ARENA, '&'], 0],
                        ['OR', ['recoveryTime', 10 * MINUTE * 1000, '<='], ['attributes4', SPELL_ATTR4_USABLE_IN_ARENA, '&']]
                    ];
        else
            return  ['OR',
                        ['attributes4', SPELL_ATTR4_NOT_USABLE_IN_ARENA, '&'],
                        ['AND', ['recoveryTime', 10 * MINUTE * 1000, '>'], [['attributes4', SPELL_ATTR4_USABLE_IN_ARENA, '&'], 0]]
                    ];
    }

    protected function cbBandageSpell(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($crs)                                           // match exact, not as flag
            return ['AND', ['attributes1', SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2 | SPELL_ATTR1_CHANNEL_TRACK_TARGET], ['effect1ImplicitTargetA', 21]];
        else
            return ['OR', ['attributes1', SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2 | SPELL_ATTR1_CHANNEL_TRACK_TARGET, '!'], ['effect1ImplicitTargetA', 21, '!']];
    }

    protected function cbProficiency(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $skill1Ids  = [];
        $skill2Mask = 0x0;

        switch($crs)
        {
            case 1:                                         // Weapons
                foreach (Game::$skillLineMask[-3] as $bit => $_)
                    $skill2Mask |= (1 << $bit);
                $skill1Ids = DB::Aowow()->selectCol('SELECT `id` FROM ?_skillline WHERE `typeCat` = 6');
                break;
            case 2:                                         // Armor (Proficiencies + Specializations: so for us it's the same)
            case 3:                                         // Armor Proficiencies
                $skill1Ids = DB::Aowow()->selectCol('SELECT `id` FROM ?_skillline WHERE `typeCat` = 8');
                break;
            case 4:                                         // Armor Specializations
                return [0];                                 // 4.x+ feature where using purely one type of armor increases your primary stat
            case 5:                                         // Languages
                $skill1Ids = DB::Aowow()->selectCol('SELECT `id` FROM ?_skillline WHERE `typeCat` = 10');
                break;
        }

        if (!$skill1Ids)
            return [0];

        $cnd = ['skillLine1', $skill1Ids];
        if ($skill2Mask)
            $cnd = ['OR', $cnd, ['AND', ['skillLine1', -3], ['skillLine2OrMask', $skill2Mask, '&']]];

        return $cnd;
    }
}

?>
