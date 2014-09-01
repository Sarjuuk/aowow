<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellList extends BaseType
{
    use listviewHelper;

    public        $ranks       = [];
    public        $relItems    = null;
    public        $sources     = [];

    public static $type        = TYPE_SPELL;
    public static $brickFile   = 'spell';

    public static $skillLines  = array(
         6 => [ 43,  44,  45,  46,  54,  55,  95, 118, 136, 160, 162, 172, 173, 176, 226, 228, 229, 473], // Weapons
         8 => [293, 413, 414, 415, 433],                                                                  // Armor
         9 => [129, 185, 356, 762],                                                                       // sec. Professions
        10 => [ 98, 109, 111, 113, 115, 137, 138, 139, 140, 141, 313, 315, 673, 759],                     // Languages
        11 => [164, 165, 171, 182, 186, 197, 202, 333, 393, 755, 773]                                     // prim. Professions
    );

    public static $spellTypes  = array(
         6 => 1,
         8 => 2,
        10 => 4
    );

    public static $effects     = array(
        'heal'       => [ 0,  3,  10,  67,  75, 136                         ],  // <no effect>, Dummy, Heal, Heal Max Health, Heal Mechanical, Heal Percent
        'damage'     => [ 0,  2,   3,   9,  62                              ],  // <no effect>, Dummy, School Damage, Health Leech, Power Burn
        'itemCreate' => [24, 34,  59,  66, 157                              ],  // createItem, changeItem, randomItem, createManaGem, createItem2
        'trigger'    => [ 3, 32,  64, 101, 142, 148, 151, 152, 155, 160, 164],  // dummy, trigger missile, trigger spell, feed pet, force cast, force cast with value, unk, trigger spell 2, unk, dualwield 2H, unk, remove aura
        'teach'      => [36, 57, 133                                        ]   // learn spell, learn pet spell, unlearn specialization
    );
    public static $auras       = array(
        'heal'       => [ 4,  8, 62, 69,  97, 226                           ],  // Dummy, Periodic Heal, Periodic Health Funnel, School Absorb, Mana Shield, Periodic Dummy
        'damage'     => [ 3,  4, 15, 53,  89, 162, 226                      ],  // Periodic Damage, Dummy, Damage Shield, Periodic Health Leech, Periodic Damage Percent, Power Burn Mana, Periodic Dummy
        'itemCreate' => [86                                                 ],  // Channel Death Item
        'trigger'    => [ 4, 23, 42, 48, 109, 226, 227, 231, 236, 284       ],  // dummy; 23/227: periodic trigger spell (with value); 42/231: proc trigger spell (with value); 48: unk; 109: add target trigger; 226: periodic dummy; 236: control vehicle; 284: linked
        'teach'      => [                                                   ]
    );

    private       $spellVars   = [];
    private       $refSpells   = [];
    private       $tools       = [];
    private       $interactive = false;
    private       $charLevel   = MAX_LEVEL;

    protected     $queryBase   = 'SELECT *, id AS ARRAY_KEY FROM ?_spell s';

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        if ($this->error)
            return;

        // post processing
        $foo = [];
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
            if (!empty($_curTpl['source']))
            {
                $sources = explode(' ', $_curTpl['source']);
                foreach ($sources as $src)
                {
                    $src = explode(':', $src);
                    if ($src[0] != -3)                      // todo (high): sourcemore - implement after items
                        $this->sources[$this->id][$src[0]][] = $src[1];
                }
            }

            // set full masks to 0
            $_curTpl['reqClassMask'] &= CLASS_MASK_ALL;
            if ($_curTpl['reqClassMask'] == CLASS_MASK_ALL)
                $_curTpl['reqClassMask'] = 0;

            $_curTpl['reqRaceMask'] &= RACE_MASK_ALL;
            if ($_curTpl['reqRaceMask'] == RACE_MASK_ALL)
                $_curTpl['reqRaceMask'] = 0;

            // unpack skillLines
            $_curTpl['skillLines'] = [];
            if ($_curTpl['skillLine1'] < 0)
            {
                foreach (Util::$skillLineMask[$_curTpl['skillLine1']] as $idx => $pair)
                    if ($_curTpl['skillLine2OrMask'] & (1 << $idx))
                        $_curTpl['skillLines'][] = $pair[1];
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
        }

        if ($foo)
            $this->relItems = new ItemList(array(['i.id', array_unique($foo)], 0));
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT * FROM ?_spell WHERE id = ?d', $id );
        return Util::localizedString($n, 'name');
    }
    // end static use

    // required for itemSet-bonuses and socket-bonuses
    public function getStatGain()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $stats = [];

            for ($i = 1; $i <= 3; $i++)
            {
                $pts = $this->calculateAmountForCurrent($i)[1];
                $mv  = $this->curTpl['effect'.$i.'MiscValue'];
                $au  = $this->curTpl['effect'.$i.'AuraId'];

                // Enchant Item Permanent (53) / Temporary (54)
                if (in_array($this->curTpl['effect'.$i.'Id'], [53, 54]))
                {
                    if ($mv)
                        Util::arraySumByKey($stats, Util::parseItemEnchantment($mv, true));

                    continue;
                }

                switch ($au)
                {
                    case 29:                                // ModStat MiscVal:type
                    {
                        if ($mv < 0)                        // all stats
                        {
                            for ($j = 0; $j < 5; $j++)
                                @$stats[ITEM_MOD_AGILITY + $j] += $pts;
                        }
                        else                                // one stat
                            @$stats[ITEM_MOD_AGILITY + $mv] += $pts;

                        break;
                    }
                    case 34:                                // Increase Health
                    case 230:
                    case 250:
                    {
                        @$stats[ITEM_MOD_HEALTH] += $pts;
                        break;
                    }
                    case 13:                                // damage splpwr + physical (dmg & any)
                    {
                        // + weapon damage
                        if ($mv == (1 << SPELL_SCHOOL_NORMAL))
                        {
                            @$stats[ITEM_MOD_WEAPON_DMG] += $pts;
                            break;
                        }

                        // full magic mask, also counts towards healing
                        if ($mv == 0x7E)
                        {
                            @$stats[ITEM_MOD_SPELL_POWER] += $pts;
                            @$stats[ITEM_MOD_SPELL_DAMAGE_DONE] += $pts;
                        }
                        else
                        {
                            // HolySpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_HOLY))
                                @$stats[ITEM_MOD_HOLY_POWER] += $pts;

                            // FireSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_FIRE))
                                @$stats[ITEM_MOD_FIRE_POWER] += $pts;

                            // NatureSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_NATURE))
                                @$stats[ITEM_MOD_NATURE_POWER] += $pts;

                            // FrostSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_FROST))
                                @$stats[ITEM_MOD_FROST_POWER] += $pts;

                            // ShadowSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_SHADOW))
                                @$stats[ITEM_MOD_SHADOW_POWER] += $pts;

                            // ArcaneSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_ARCANE))
                                @$stats[ITEM_MOD_ARCANE_POWER] += $pts;
                        }

                        break;
                    }
                    case 135:                               // healing splpwr (healing & any) .. not as a mask..
                    {
                        @$stats[ITEM_MOD_SPELL_HEALING_DONE] += $pts;
                        break;
                    }
                    case 35:                                // ModPower - MiscVal:type see defined Powers only energy/mana in use
                    {
                        if ($mv == POWER_HEALTH)
                            @$stats[ITEM_MOD_HEALTH] += $pts;
                        if ($mv == POWER_ENERGY)
                            @$stats[ITEM_MOD_ENERGY] += $pts;
                        else if ($mv == POWER_MANA)
                            @$stats[ITEM_MOD_MANA] += $pts;
                        else if ($mv == POWER_RUNIC_POWER)
                            @$stats[ITEM_MOD_RUNIC_POWER] += $pts;

                        break;
                    }
                    case 189:                               // CombatRating MiscVal:ratingMask
                    case 220:
                        if ($mod = Util::itemModByRatingMask($mv))
                            @$stats[$mod] += $pts;
                        break;
                    case 143:                               // Resistance MiscVal:school
                    case 83:
                    case 22:
                        if ($mv == 1)                       // Armor only if explicitly specified
                        {
                            @$stats[ITEM_MOD_ARMOR] += $pts;
                            break;
                        }

                        if ($mv == 2)                       // holy-resistance ONLY if explicitly specified (shouldn't even exist...)
                        {
                            @$stats[ITEM_MOD_HOLY_RESISTANCE] += $pts;
                            break;
                        }

                        for ($j = 0; $j < 7; $j++)
                        {
                            if (($mv & (1 << $j)) == 0)
                                continue;

                            switch ($j)
                            {
                                case 2:
                                    @$stats[ITEM_MOD_FIRE_RESISTANCE] += $pts;
                                    break;
                                case 3:
                                    @$stats[ITEM_MOD_NATURE_RESISTANCE] += $pts;
                                    break;
                                case 4:
                                    @$stats[ITEM_MOD_FROST_RESISTANCE] += $pts;
                                    break;
                                case 5:
                                    @$stats[ITEM_MOD_SHADOW_RESISTANCE] += $pts;
                                    break;
                                case 6:
                                    @$stats[ITEM_MOD_ARCANE_RESISTANCE] += $pts;
                                    break;
                            }
                        }
                        break;
                    case 8:                                 // hp5
                    case 84:
                    case 161:
                        @$stats[ITEM_MOD_HEALTH_REGEN] += $pts;
                        break;
                    case 85:                                // mp5
                        @$stats[ITEM_MOD_MANA_REGENERATION] += $pts;
                        break;
                    case 99:                                // atkpwr
                        @$stats[ITEM_MOD_ATTACK_POWER] += $pts;
                        break;                              // ?carries over to rngatkpwr?
                    case 124:                               // rngatkpwr
                        @$stats[ITEM_MOD_RANGED_ATTACK_POWER] += $pts;
                        break;
                    case 158:                               // blockvalue
                        @$stats[ITEM_MOD_BLOCK_VALUE] += $pts;
                        break;
                    case 240:                               // ModExpertise
                        @$stats[ITEM_MOD_EXPERTISE_RATING] += $pts;
                        break;
                }
            }

            $data[$this->id] = $stats;
        }

        return $data;
    }

    public function getProfilerMods()
    {
        $data = $this->getStatGain();                       // flat gains

        foreach ($this->iterate() as $id => $__)
        {
            for ($i = 1; $i < 4; $i++)
            {
                $pts = $this->calculateAmountForCurrent($i)[1];
                $mv  = $this->curTpl['effect'.$i.'MiscValue'];
                $au  = $this->curTpl['effect'.$i.'AuraId'];

                /*  ISSUE!
                    mods formated like ['<statName>' => [<points>, 'percentOf', '<statName>']] are applied as multiplier and not
                    as a flat value (that is equal to the percentage, like they should be). So the stats-table won't show the actual deficit
                */

                switch ($this->curTpl['effect'.$i.'AuraId'])
                {
                    case 101:
                        $data[$id][] = ['armor' => [$pts / 100, 'percentOf', 'armor']];
                        break;
                    case 13:                                // damage done flat
                        // per magic school, omit physical
                        break;
                    case 30:                                // mod skill
                        // diff between character skills and trade skills
                        break;
                    case 36:                                // shapeshift
                }
            }
        }

        return $data;
    }

    // halper
    public function getReagentsForCurrent()
    {
        $data = [];

        for ($i = 1; $i <= 8; $i++)
            if ($this->curTpl['reagent'.$i] > 0 && $this->curTpl['reagentCount'.$i])
                $data[$this->curTpl['reagent'.$i]] = [$this->curTpl['reagent'.$i], $this->curTpl['reagentCount'.$i]];

        return $data;
    }

    public function getToolsForCurrent()
    {
        if ($this->tools)
            return $this->tools;

        $tools = [];
        for ($i = 1; $i <= 2; $i++)
        {
            // TotemCategory
            if ($_ = $this->curTpl['toolCategory'.$i])
            {
                $tc = DB::Aowow()->selectRow('SELECT * FROM ?_totemcategory WHERE id = ?d', $_);
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

    public function getModelInfo($targetId = 0)
    {
        $displays = [0 => []];
        foreach ($this->iterate() as $id => $__)
        {
            if ($targetId && $targetId != $id)
                continue;

            for ($i = 1; $i < 4; $i++)
            {
                $effMV = $this->curTpl['effect'.$i.'MiscValue'];
                if (!$effMV)
                    continue;

                // GO Model from MiscVal
                if (in_array($this->curTpl['effect'.$i.'Id'], [50, 76, 104, 105, 106, 107]))
                {
                    $displays[TYPE_OBJECT][$id] = $effMV;
                    break;
                }
                // NPC Model from MiscVal
                else if (in_array($this->curTpl['effect'.$i.'Id'], [28, 90]) || in_array($this->curTpl['effect'.$i.'AuraId'], [56, 78]))
                {
                    $displays[TYPE_NPC][$id] = $effMV;
                    break;
                }
                // Shapeshift
                else if ($this->curTpl['effect'.$i.'AuraId'] == 36)
                {
                    $subForms = array(
                        892  => [892,  29407, 29406, 29408, 29405],         // Cat - NE
                        8571 => [8571, 29410, 29411, 29412],                // Cat - Tauren
                        2281 => [2281, 29413, 29414, 29416, 29417],         // Bear - NE
                        2289 => [2289, 29415, 29418, 29419, 29420, 29421]   // Bear - Tauren
                    );

                    if ($st = DB::Aowow()->selectRow('SELECT *, displayIdA as model1, displayIdH as model2 FROM ?_shapeshiftforms WHERE id = ?d', $effMV))
                    {
                        foreach ([1, 2] as $i)
                            if (isset($subForms[$st['model'.$i]]))
                                $st['model'.$i] = $subForms[$st['model'.$i]][array_rand($subForms[$st['model'.$i]])];

                        $displays[0][$id] = array(
                            'npcId'        => 0,
                            'displayId'    => [$st['model1'], $st['model2']],
                            'creatureType' => $st['creatureType'],
                            'displayName'  => Util::localizedString($st, 'name')
                        );
                        break;
                    }
                }
            }
        }

        $results = $displays[0];

        if (!empty($displays[TYPE_NPC]))
        {
            $nModels = new CreatureList(array(['id', $displays[TYPE_NPC]]));
            foreach ($nModels->iterate() as $nId => $__)
            {
                $srcId = array_search($nId, $displays[TYPE_NPC]);
                $results[$srcId] = array(
                    'typeId'      => $nId,
                    'displayId'   => $nModels->getRandomModelId(),
                    'displayName' => $nModels->getField('name', true)
                );
            }
        }

        if (!empty($displays[TYPE_OBJECT]))
        {
            $oModels = new GameObjectList(array(['id', $displays[TYPE_OBJECT]]));
            foreach ($oModels->iterate() as $oId => $__)
            {
                $srcId = array_search($oId, $displays[TYPE_OBJECT]);
                $results[$srcId] = array(
                    'typeId'      => $oId,
                    'displayId'   => $oModels->getField('displayId'),
                    'displayName' => $oModels->getField('name', true)
                );
            }
        }

        return $targetId ? @$results[$targetId] : $results;
    }

    private function createRangesForCurrent()
    {
        if (!$this->curTpl['rangeMaxHostile'])
            return '';

        // minRange exists; show as range
        if ($this->curTpl['rangeMinHostile'])
            return sprintf(Lang::$spell['range'], $this->curTpl['rangeMinHostile'].' - '.$this->curTpl['rangeMaxHostile']);
        // friend and hostile differ; do color
        else if ($this->curTpl['rangeMaxHostile'] != $this->curTpl['rangeMaxFriend'])
            return sprintf(Lang::$spell['range'], '<span class="q10">'.$this->curTpl['rangeMaxHostile'].'</span> - <span class="q2">'.$this->curTpl['rangeMaxHostile']. '</span>');
        // hardcode: "melee range"
        else if ($this->curTpl['rangeMaxHostile'] == 5)
            return Lang::$spell['meleeRange'];
        // hardcode "unlimited range"
        else if ($this->curTpl['rangeMaxHostile'] == 50000)
            return Lang::$spell['unlimRange'];
        // regular case
        else
            return sprintf(Lang::$spell['range'], $this->curTpl['rangeMaxHostile']);
    }

    public function createPowerCostForCurrent()
    {
        $str = '';

        // check for custom PowerDisplay
        $pt = $this->curTpl['powerDisplayString'] ? $this->curTpl['powerDisplayString'] : $this->curTpl['powerType'];

        // power cost: pct over static
        if ($this->curTpl['powerCostPercent'] > 0)
            $str .= $this->curTpl['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$pt]));
        else if ($this->curTpl['powerCost'] > 0 || $this->curTpl['powerPerSecond'] > 0 || $this->curTpl['powerCostPerLevel'] > 0)
            $str .= ($pt == POWER_RAGE || $pt == POWER_RUNIC_POWER ? $this->curTpl['powerCost'] / 10 : $this->curTpl['powerCost']).' '.Util::ucFirst(Lang::$spell['powerTypes'][$pt]);
        else if ($rCost = ($this->curTpl['powerCostRunes'] & 0x333))
        {   // Blood 2|1 - Unholy 2|1 - Frost 2|1
            $runes = [];
            if ($_ = (($rCost & 0x300) >> 8))
                $runes[] = $_.' '.Lang::$spell['powerRunes'][2];
            if ($_ = (($rCost & 0x030) >> 4))
                $runes[] = $_.' '.Lang::$spell['powerRunes'][1];
            if ($_ =  ($rCost & 0x003))
                $runes[] = $_.' '.Lang::$spell['powerRunes'][0];

            $str .= implode(', ', $runes);
        }

        // append periodic cost
        if ($this->curTpl['powerPerSecond'] > 0)
            $str .= sprintf(Lang::$spell['costPerSec'], $this->curTpl['powerPerSecond']);

        // append level cost (todo (low): work in as scaling cost)
        if ($this->curTpl['powerCostPerLevel'] > 0)
            $str .= sprintf(Lang::$spell['costPerLevel'], $this->curTpl['powerCostPerLevel']);

        return $str;
    }

    public function createCastTimeForCurrent($short = true, $noInstant = true)
    {
        if ($this->curTpl['interruptFlagsChannel'])
            return Lang::$spell['channeled'];
        else if ($this->curTpl['castTime'] > 0)
            return $short ? sprintf(Lang::$spell['castIn'], $this->curTpl['castTime'] / 1000) : Util::formatTime($this->curTpl['castTime']);
        // show instant only for player/pet/npc abilities (todo (low): unsure when really hidden (like talent-case))
        else if ($noInstant && !in_array($this->curTpl['typeCat'], [11, 7, -3, -6, -8, 0]) && !($this->curTpl['cuFlags'] & SPELL_CU_TALENTSPELL))
            return '';
        // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only (todo (low): rule is imperfect)
        else if ($this->curTpl['damageClass'] != 1 || $this->curTpl['attributes0'] & 0x10)
            return Lang::$spell['instantPhys'];
        else                                                // instant cast
            return Lang::$spell['instantMagic'];
    }

    private function createCooldownForCurrent()
    {
        if ($this->curTpl['recoveryTime'])
            return sprintf(Lang::$game['cooldown'], Util::formatTime($this->curTpl['recoveryTime'], true));
        else if ($this->curTpl['recoveryCategory'])
            return sprintf(Lang::$game['cooldown'], Util::formatTime($this->curTpl['recoveryCategory'], true));
        else
            return '';
    }

    // formulae base from TC
    private function calculateAmountForCurrent($effIdx, $altTpl = null)
    {
        $ref     = $altTpl ? $altTpl : $this;
        $level   = $this->charLevel;
        $rppl    = $ref->getField('effect'.$effIdx.'RealPointsPerLevel');
        $base    = $ref->getField('effect'.$effIdx.'BasePoints');
        $add     = $ref->getField('effect'.$effIdx.'DieSides');
        $maxLvl  = $ref->getField('maxLevel');
        $baseLvl = $ref->getField('baseLevel');
        $scaling = $this->curTpl['attributes1'] & 0x200;    // never a referenced spell, ALWAYS $this; SPELL_ATTR1_MELEE_COMBAT_SPELL: 0x200

        if ($scaling)
        {
            if ($level > $maxLvl && $maxLvl > 0)
                $level = $maxLvl;
            else if ($level < $baseLvl)
                $level = $baseLvl;

            $level -= $ref->getField('spellLevel');
            $base  += (int)($level * $rppl);
        }

        return [
            $add ? $base + 1 : $base,
            $base + $add,
            $scaling ? '<!--ppl'.$baseLvl.':'.$maxLvl.':'.($base + max(1, $add)).':'.$rppl.'-->' : null,
            $scaling ? '<!--ppl'.$baseLvl.':'.$maxLvl.':'.($base + $add).':'.$rppl.'-->' : null
        ];
    }

    public function canCreateItem()
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::$effects['itemCreate']) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::$auras['itemCreate']))
                if ($this->curTpl['effect'.$i.'CreateItemId'] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function canTriggerSpell()
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::$effects['trigger']) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::$auras['trigger']))
                if ($this->curTpl['effect'.$i.'TriggerSpell'] > 0 || $this->curTpl['effect'.$i.'MiscValue'] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function canTeachSpell()
    {
        $idx = [];
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], SpellList::$effects['teach']) || in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::$auras['teach']))
                if ($this->curTpl['effect'.$i.'TriggerSpell'] > 0)
                    $idx[] = $i;

        return $idx;
    }

    public function isChanneledSpell()
    {
        return $this->curTpl['attributes1'] & 0x44;
    }

    public function isHealingSpell()
    {
        for ($i = 1; $i < 4; $i++)
            if (!in_array($this->curTpl['effect'.$i.'Id'], SpellList::$effects['heal']) && !in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::$auras['heal']))
                return false;

        return true;
    }

    public function isDamagingSpell()
    {
        for ($i = 1; $i < 4; $i++)
            if (!in_array($this->curTpl['effect'.$i.'Id'], SpellList::$effects['damage']) && !in_array($this->curTpl['effect'.$i.'AuraId'], SpellList::$auras['damage']))
                return false;

        return true;
    }

    public function periodicEffectsMask()
    {
        $effMask = 0x0;

        for ($i = 1; $i < 4; $i++)
            if ($this->curTpl['effect'.$i.'Periode'] > 0)
                $effMask |= 1 << ($i - 1);

        return $effMask;
    }

    // description-, buff-parsing component
    private function resolveEvaluation($formula)
    {
        // see Traits in javascript locales
        $pl    = $PL    = $this->charLevel;
        $PlayerName     = Lang::$main['name'];
        $ap    = $AP    = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.atkpwr[0]',    Lang::$spell['traitShort']['atkpwr'])    : Lang::$spell['traitShort']['atkpwr'];
        $rap   = $RAP   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.rgdatkpwr[0]', Lang::$spell['traitShort']['rgdatkpwr']) : Lang::$spell['traitShort']['rgdatkpwr'];
        $sp    = $SP    = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.splpwr[0]',    Lang::$spell['traitShort']['splpwr'])    : Lang::$spell['traitShort']['splpwr'];
        $spa   = $SPA   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.arcsplpwr[0]', Lang::$spell['traitShort']['arcsplpwr']) : Lang::$spell['traitShort']['arcsplpwr'];
        $spfi  = $SPFI  = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.firsplpwr[0]', Lang::$spell['traitShort']['firsplpwr']) : Lang::$spell['traitShort']['firsplpwr'];
        $spfr  = $SPFR  = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.frosplpwr[0]', Lang::$spell['traitShort']['frosplpwr']) : Lang::$spell['traitShort']['frosplpwr'];
        $sph   = $SPH   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.holsplpwr[0]', Lang::$spell['traitShort']['holsplpwr']) : Lang::$spell['traitShort']['holsplpwr'];
        $spn   = $SPN   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.natsplpwr[0]', Lang::$spell['traitShort']['natsplpwr']) : Lang::$spell['traitShort']['natsplpwr'];
        $sps   = $SPS   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.shasplpwr[0]', Lang::$spell['traitShort']['shasplpwr']) : Lang::$spell['traitShort']['shasplpwr'];
        $bh    = $BH    = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.splheal[0]',   Lang::$spell['traitShort']['splheal'])   : Lang::$spell['traitShort']['splheal'];

        $HND   = $hnd   = $this->interactive ? sprintf(Util::$dfnString, '[Hands required by weapon]', 'HND') : 'HND';    // todo (med): localize this one
        $MWS   = $mws   = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.mlespeed[0]',    'MWS') : 'MWS';
        $mw             = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.dmgmin1[0]',     'mw')  : 'mw';
        $MW             = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.dmgmax1[0]',     'MW')  : 'MW';
        $mwb            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.mledmgmin[0]',   'mwb') : 'mwb';
        $MWB            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.mledmgmax[0]',   'MWB') : 'MWB';
        $rwb            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.rgddmgmin[0]',   'rwb') : 'rwb';
        $RWB            = $this->interactive ? sprintf(Util::$dfnString, 'LANG.traits.rgddmgmax[0]',   'RWB') : 'RWB';

        $cond  = $COND  = function($a, $b, $c) { return $a ? $b : $c; };
        $eq    = $EQ    = function($a, $b)     { return $a == $b;     };
        $gt    = $GT    = function($a, $b)     { return $a > $b;      };
        $gte   = $GTE   = function($a, $b)     { return $a <= $b;     };
        $floor = $FLOOR = function($a)         { return floor($a);    };
        $max   = $MAX   = function($a, $b)     { return max($a, $b);  };
        $min   = $MIN   = function($a, $b)     { return min($a, $b);  };

        if (preg_match_all('/\$[a-z]+\b/i', $formula, $vars))
        {
            $evalable = true;

            foreach ($vars[0] as $var)                      // oh lord, forgive me this sin .. but is_callable seems to bug out and function_exists doesn't find lambda-functions >.<
            {
                $eval = eval('return '.$var.';');
                if (getType($eval) == 'object')
                    continue;
                else if (is_numeric($eval))
                    continue;

                $evalable = false;
                break;
            }

            if (!$evalable)
            {
                // can't eval constructs because of strings present. replace constructs with strings
                $cond  = $COND  = !$this->interactive ? 'COND'  : sprintf(Util::$dfnString, 'COND(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>, <span class=\'q1\'>c</span>)<br /> <span class=\'q1\'>a</span> ? <span class=\'q1\'>b</span> : <span class=\'q1\'>c</span>', 'COND');
                $eq    = $EQ    = !$this->interactive ? 'EQ'    : sprintf(Util::$dfnString, 'EQ(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> == <span class=\'q1\'>b</span>', 'EQ');
                $gt    = $GT    = !$this->interactive ? 'GT'    : sprintf(Util::$dfnString, 'GT(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> > <span class=\'q1\'>b</span>', 'GT');
                $gte   = $GTE   = !$this->interactive ? 'GTE'   : sprintf(Util::$dfnString, 'GTE(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> <= <span class=\'q1\'>b</span>', 'GT');
                $floor = $FLOOR = !$this->interactive ? 'FLOOR' : sprintf(Util::$dfnString, 'FLOOR(<span class=\'q1\'>a</span>)', 'FLOOR');
                $min   = $MIN   = !$this->interactive ? 'MIN'   : sprintf(Util::$dfnString, 'MIN(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)', 'MIN');
                $max   = $MAX   = !$this->interactive ? 'MAX'   : sprintf(Util::$dfnString, 'MAX(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)', 'MAX');
                $pl    = $PL    = !$this->interactive ? 'PL'    : sprintf(Util::$dfnString, 'LANG.level', 'PL');

                // note the " !
                return eval('return "'.$formula.'";');
            }
            else
                return eval('return '.$formula.';');
        }

        // since this function may be called recursively, there are cases, where the already evaluated string is tried to be evaled again, throwing parse errors
        if (strstr($formula, '</dfn>'))
            return $formula;

        // hm, minor eval-issue. eval doesnt understand two operators without a space between them (eg. spelll: 18126)
        $formula = preg_replace('/(\+|-|\*|\/)(\+|-|\*|\/)/i', '\1 \2', $formula);

        // there should not be any letters without a leading $
        return eval('return '.$formula.';');
    }

    // description-, buff-parsing component
    private function resolveVariableString($variable, &$usesScalingRating)
    {
        $signs  = ['+', '-', '/', '*', '%', '^'];

        $op     = $variable[2];
        $oparg  = $variable[3];
        $lookup = (int)$variable[4];
        $var    = $variable[6] ? $variable[6] : $variable[8];
        $effIdx = $variable[6] ? null         : $variable[9];
        $switch = $variable[7] ? explode(':', $variable[7]) : null;

        if (!$var)
            return;

        if (!$effIdx)                                       // if EffectIdx is omitted, assume EffectIdx: 1
            $effIdx = 1;

        // cache at least some lookups.. should be moved to single spellList :/
        if ($lookup && !isset($this->refSpells[$lookup]))
            $this->refSpells[$lookup] = new SpellList(array(['s.id', $lookup]));

        switch ($var)
        {
            case 'a':                                       // EffectRadiusMin
            case 'A':                                       // EffectRadiusMax
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'RadiusMax');
                else
                    $base = $this->getField('effect'.$effIdx.'RadiusMax');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'b':                                       // PointsPerComboPoint
            case 'B':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'PointsPerComboPoint');
                else
                    $base = $this->getField('effect'.$effIdx.'PointsPerComboPoint');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'd':                                       // SpellDuration
            case 'D':                                       // todo (med): min/max?; /w unit?
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('duration');
                else
                    $base = $this->getField('duration');

                if ($base <= 0)
                    return Lang::$spell['untilCanceled'];

                if ($op && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return explode(' ', Util::formatTime(abs($base), true));
            case 'e':                                       // EffectValueMultiplier
            case 'E':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'ValueMultiplier');
                else
                    $base = $this->getField('effect'.$effIdx.'ValueMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'f':                                       // EffectDamageMultiplier
            case 'F':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'DamageMultiplier');
                else
                    $base = $this->getField('effect'.$effIdx.'DamageMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'g':                                       // boolean choice with casters gender as condition $gX:Y;
            case 'G':
                return '&lt;'.$switch[0].'/'.$switch[1].'&gt;';
            case 'h':                                       // ProcChance
            case 'H':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('procChance');
                else
                    $base = $this->getField('procChance');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'i':                                       // MaxAffectedTargets
            case 'I':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('maxAffectedTargets');
                else
                    $base = $this->getField('maxAffectedTargets');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'l':                                       // boolean choice with last value as condition $lX:Y;
            case 'L':
                return '$l'.$switch[0].':'.$switch[1];      // resolve later by backtracking
            case 'm':                                       // BasePoints (minValue)
            case 'M':                                       // BasePoints (maxValue)
                if ($lookup)
                {
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'BasePoints');
                    $add  = $this->refSpells[$lookup]->getField('effect'.$effIdx.'DieSides');
                    $mv   = $this->refSpells[$lookup]->getField('effect'.$effIdx.'MiscValue');
                    $aura = $this->refSpells[$lookup]->getField('effect'.$effIdx.'AuraId');

                }
                else
                {
                    $base = $this->getField('effect'.$effIdx.'BasePoints');
                    $add  = $this->getField('effect'.$effIdx.'DieSides');
                    $mv   = $this->getField('effect'.$effIdx.'MiscValue');
                    $aura = $this->getField('effect'.$effIdx.'AuraId');
                }

                if (ctype_lower($var))
                    $add = 1;

                $base += $add;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                // Aura giving combat ratings
                $rType = 0;
                if ($aura == 189)
                    if ($rType = Util::itemModByRatingMask($mv))
                        $usesScalingRating = true;
                // Aura end

                if ($rType && $this->interactive && $aura == 189)
                    return '<!--rtg'.$rType.'-->'.abs($base).'&nbsp;<small>('.sprintf(Util::$setRatingLevelString, $this->charLevel, $rType, abs($base), Util::setRatingLevel($this->charLevel, $rType, abs($base))).')</small>';
                else if ($rType && $aura == 189)
                    return '<!--rtg'.$rType.'-->'.abs($base).'&nbsp;<small>('.Util::setRatingLevel($this->charLevel, $rType, abs($base)).')</small>';
                else
                    return $base;
            case 'n':                                       // ProcCharges
            case 'N':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('procCharges');
                else
                    $base = $this->getField('procCharges');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'o':                                       // TotalAmount for periodic auras (with variance)
            case 'O':
                if ($lookup)
                {
                    list($min, $max, $modStrMin, $modStrMax) = $this->calculateAmountForCurrent($effIdx, $this->refSpells[$lookup]);
                    $periode  = $this->refSpells[$lookup]->getField('effect'.$effIdx.'Periode');
                    $duration = $this->refSpells[$lookup]->getField('duration');
                }
                else
                {
                    list($min, $max, $modStrMin, $modStrMax) = $this->calculateAmountForCurrent($effIdx);
                    $periode  = $this->getField('effect'.$effIdx.'Periode');
                    $duration = $this->getField('duration');
                }

                if (!$periode)
                    $periode = 3000;

                $min  *= $duration / $periode;
                $max  *= $duration / $periode;
                $equal = $min == $max;

                if (in_array($op, $signs) && is_numeric($oparg))
                    if ($equal)
                        eval("\$min = $min $op $oparg;");

                if ($this->interactive)
                    return $modStrMin.$min . (!$equal ? Lang::$game['valueDelim'] . $modStrMax.$max : null);
                else
                    return $min . (!$equal ? Lang::$game['valueDelim'] . $max : null);
            case 'q':                                       // EffectMiscValue
            case 'Q':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'MiscValue');
                else
                    $base = $this->getField('effect'.$effIdx.'MiscValue');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'r':                                       // SpellRange
            case 'R':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('rangeMaxHostile');
                else
                    $base = $this->getField('rangeMaxHostile');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 's':                                       // BasePoints (with variance)
            case 'S':
                if ($lookup)
                {
                    list($min, $max, $modStrMin, $modStrMax) = $this->calculateAmountForCurrent($effIdx, $this->refSpells[$lookup]);
                    $mv   = $this->refSpells[$lookup]->getField('effect'.$effIdx.'MiscValue');
                    $aura = $this->refSpells[$lookup]->getField('effect'.$effIdx.'AuraId');
                }
                else
                {
                    list($min, $max, $modStrMin, $modStrMax) = $this->calculateAmountForCurrent($effIdx);
                    $mv   = $this->getField('effect'.$effIdx.'MiscValue');
                    $aura = $this->getField('effect'.$effIdx.'AuraId');
                }
                $equal = $min == $max;

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    if (!$equal)
                        eval("\$max = $max $op $oparg;");
                }

                // Aura giving combat ratings
                $rType = 0;
                if ($aura == 189)
                    if ($rType = Util::itemModByRatingMask($mv))
                        $usesScalingRating = true;
                // Aura end

                if ($rType && $equal && $this->interactive && $aura == 189)
                    return '<!--rtg'.$rType.'-->'.$min.'&nbsp;<small>('.sprintf(Util::$setRatingLevelString, $this->charLevel, $rType, $min, Util::setRatingLevel($this->charLevel, $rType, $min)).')</small>';
                else if ($rType && $equal && $aura == 189)
                    return '<!--rtg'.$rType.'-->'.$min.'&nbsp;<small>('.Util::setRatingLevel($this->charLevel, $rType, $min).')</small>';
                else if ($this->interactive && $aura == 189)
                    return $modStrMin.$min . (!$equal ? Lang::$game['valueDelim'] . $modStrMax.$max : null);
                else
                    return $min . (!$equal ? Lang::$game['valueDelim'] . $max : null);
            case 't':                                       // Periode
            case 'T':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'Periode') / 1000;
                else
                    $base = $this->getField('effect'.$effIdx.'Periode') / 1000;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'u':                                       // StackCount
            case 'U':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('stackAmount');
                else
                    $base = $this->getField('stackAmount');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'v':                                   // MaxTargetLevel
            case 'V':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('MaxTargetLevel');
                else
                    $base = $this->getField('MaxTargetLevel');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'x':                                   // ChainTargetCount
            case 'X':
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('effect'.$effIdx.'ChainTarget');
                else
                    $base = $this->getField('effect'.$effIdx.'ChainTarget');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return $base;
            case 'z':                                   // HomeZone
                return Lang::$spell['home'];
        }
    }

    // description-, buff-parsing component
    private function resolveFormulaString($formula, $precision = 0, &$scaling)
    {
        // step 1: formula unpacking redux
        while (($formStartPos = strpos($formula, '${')) !== false)
        {
            $formBrktCnt   = 0;
            $formPrecision = 0;
            $formCurPos    = $formStartPos;

            $formOutStr    = '';

            while ($formCurPos <= strlen($formula))
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

            if (@$formula[++$formCurPos] == '.')
            {
                $formPrecision = (int)$formula[++$formCurPos];
                ++$formCurPos;                              // for some odd reason the precision decimal survives if wo dont increment further..
            }

            $formOutStr = $this->resolveFormulaString($formOutStr, $formPrecision, $scaling);

            $formula = substr_replace($formula, $formOutStr, $formStartPos, ($formCurPos - $formStartPos));
        }

        // step 2: resolve variables
        $pos    = 0;                                        // continue strpos-search from this offset
        $str    = '';
        $suffix = '';
        while (($npos = strpos($formula, '$', $pos)) !== false)
        {
            if ($npos != $pos)
                $str .= substr($formula, $pos, $npos - $pos);

            $pos = $npos++;

            if ($formula[$pos] == '$')
                $pos++;

            if (!preg_match('/^(([\+\-\*\/])(\d+);)?(\d*)(([g])([\w\s]*:[\w\s]*);|([a-z])([123]?)\b)/i', substr($formula, $pos), $result))
            {
                $str .= '#';                                // mark as done, reset below
                continue;
            }
            $pos += strlen($result[0]);

            $var = $this->resolveVariableString($result, $scaling);
            if (is_array($var))
            {
                $str   .= $var[0];
                $suffix = ' '.$var[1];
            }
            else
                $str .= $var;
        }
        $str .= substr($formula, $pos);
        $str  = str_replace('#', '$', $str);                // reset marks

        // step 3: try to evaluate result
        $evaled = $this->resolveEvaluation($str);

        $return = is_numeric($evaled) ? number_format($evaled, $precision, '.', '') : $evaled;
        return $return.$suffix;
    }

    // should probably used only once to create ?_spell. come to think of it, it yields the same results every time.. it absolutely has to!
    // although it seems to be pretty fast, even on those pesky test-spells with extra complex tooltips (Ron Test Spell X))
    public function parseText($type = 'description', $level = MAX_LEVEL, $interactive = false, &$scaling = false)
    {
        // oooo..kaaayy.. parsing text in 6 or 7 easy steps
        // we don't use the internal iterator here. This func has to be called for the individual template.
        // otherwise it will get a bit messy, when we iterate, while we iterate *yo dawg!*

    /* documentation .. sort of
        bracket use
            ${}.x - formulas; .x is optional; x:[0-9] .. max-precision of a floatpoint-result; default: 0
            $[]   - conditionals ... like $?condition[true][false]; alternative $?!(cond1|cond2)[true]$?cond3[elseTrue][false]; ?a40120: has aura 40120; ?s40120: knows spell 40120(?)
            $<>   - variables
            ()    - regular use for function-like calls

        variables in use .. caseSensitive

        game variables (optionally replace with textVars)
            $PlayerName - Cpt. Obvious
            $PL / $pl   - PlayerLevel
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

        functions in use .. caseInsensitive
            $cond(a, b, c) - like SQL, if A is met use B otherwise use C
            $eq(a, b)      - a == b
            $floor(a)      - floor()
            $gt(a, b)      - a > b
            $gte(a, b)     - a >= b
            $min(a, b)     - min()
            $max(a, b)     - max()
    */

        $this->interactive = $interactive;
        $this->charLevel   = $level;

    // step 0: get text
        $data = $this->getField($type, true);
        if (empty($data) || $data == "[]")                  // empty tooltip shouldn't be displayed anyway
            return array("", []);

    // step 1: if the text is supplemented with text-variables, get and replace them
        if ($this->curTpl['spellDescriptionVariableId'] > 0)
        {
            if (empty($this->spellVars[$this->id]))
            {
                $spellVars = DB::Aowow()->SelectCell('SELECT vars FROM ?_spellvariables WHERE id = ?d', $this->curTpl['spellDescriptionVariableId']);
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
        // \1: full pattern match; \2: any sequence, that may include an aura/spell-ref; \3: any other sequence, between "?$" and "["
        while (preg_match('/\$\?(([\W\D]*[as]\d+)|([^\[]*))/i', $data, $matches))
        {
            $condBrktCnt = 0;
            $targetPart  = 3;                               // we usually want the second pair of brackets
            $curPart     = 0;                               // parts: $? 0 [ 1 ] 2 [ 3 ] 4
            $relSpells   = [];    // see spells_enus

            $condOutStr  = '';

            if (!empty($matches[3]))                        // we can do this! -> eval
            {
                $cnd = $this->resolveEvaluation($matches[3]);
                if ((is_numeric($cnd) || is_bool($cnd)) && $cnd) // only case, deviating from normal; positive result -> use [true]
                    $targetPart = 1;

                $condStartPos = strpos($data, $matches[3]) - 2;
                $condCurPos   = $condStartPos;

            }
/*
_[100].tooltip_enus = '<table><tr><td><b>Charge</b><br />8 - 25 yd range<table width="100%"><tr><td>Instant</td><th>20 sec cooldown</th></tr></table>Requires Warrior<br />Requires level 3</td></tr></table><table><tr><td><span class="q">Charge to an enemy, stunning it <!--sp58377:0--><!--sp58377-->for <!--sp103828:0-->1 sec<!--sp103828-->. Generates 20 Rage.</span></td></tr></table>';
_[100].buff_enus = '';
_[100].spells_enus = {"58377": [["", "and 2 additional nearby targets "]], "103828": [["1 sec", "3 sec and reducing movement speed by 50% for 15 sec"]]};
_[100].buffspells_enus = {};
Turns the Shaman into a Ghost Wolf, increasing speed by $s2%$?s59289[ and regenerating $59289s1% of your maximum health every 5 sec][].
Lasts 5 min. $?$gte($pl,68)[][Cannot be used on items level 138 and higher.]
*/

            else if (!empty($matches[2]))
            {
                $condStartPos = strpos($data, $matches[2]) - 2;
                $condCurPos   = $condStartPos;
            }
            else                                            // empty too? WTF?! GTFO!
                die('what a terrible failure');

            while ($condCurPos <= strlen($data))            // only hard-exit condition, we'll hit those breaks eventually^^
            {
                // we're through with this condition. replace with found result and continue
                if ($curPart == 4 || $condCurPos == strlen($data))
                {
                    $data = substr_replace($data, $condOutStr, $condStartPos, ($condCurPos - $condStartPos));
                    break;
                }

                $char = $data[$condCurPos];

                // advance position
                $condCurPos++;

                if ($char == '[')
                {
                    if (!$condBrktCnt)
                        $curPart++;

                    $condBrktCnt++;

                    if ($condBrktCnt == 1)
                        continue;
                }
                else if ($char == ']')
                {
                    if ($condBrktCnt == 1)
                        $curPart++;

                    $condBrktCnt--;

                    if (!$condBrktCnt)
                        continue;
                }

                // we got an elseif .. since they are self-containing we can just remove everything we've got up to here and restart the iteration
                if ($curPart == 2 && $char == '?')
                {
                    $replace = $targetPart == 1 ? $condOutStr.' $' : '$';
                    $data = substr_replace($data, $replace, $condStartPos, ($condCurPos - $condStartPos) - 1);
                    break;
                }

                if ($curPart == $targetPart)
                    $condOutStr .= $char;

            }
        }

    // step 3: unpack formulas ${ .. }.X
        // they are stacked recursively but should be balanced .. hf
        while (($formStartPos = strpos($data, '${')) !== false)
        {
            $formBrktCnt   = 0;
            $formPrecision = 0;
            $formCurPos    = $formStartPos;

            $formOutStr    = '';

            while ($formCurPos <= strlen($data))            // only hard-exit condition, we'll hit those breaks eventually^^
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
            $formOutStr = $this->resolveFormulaString($formOutStr, $formPrecision, $scaling);

            $data = substr_replace($data, $formOutStr, $formStartPos, ($formCurPos - $formStartPos));
        }

    // step 4: find and eliminate regular variables
        $pos = 0;                                           // continue strpos-search from this offset
        $str = '';
        while (($npos = strpos($data, '$', $pos)) !== false)
        {
            if ($npos != $pos)
                $str .= substr($data, $pos, $npos - $pos);

            $pos = $npos++;

            if ($data[$pos] == '$')
                $pos++;

            //            ( (op) (oparg); )? (refSpell) ( ([g]ifText:elseText; | (var) (effIdx) )
            if (!preg_match('/^(([\+\-\*\/])(\d+);)?(\d*)(([g])([\w\s]*:[\w\s]*);|([a-z])([123]?)\b)/i', substr($data, $pos), $result))
            {
                $str .= '#';                                // mark as done, reset below
                continue;
            }

            $pos += strlen($result[0]);

            $var = $this->resolveVariableString($result, $scaling);
            $resolved = is_array($var) ? $var[0] : $var;
            $str .= is_numeric($resolved) ? abs($resolved) : $resolved;
            if (is_array($var))
                $str .= ' '.$var[1];
        }
        $str .= substr($data, $pos);
        $str = str_replace('#', '$', $str);                 // reset marker

    // step 5: variable-dependant variable-text
        // special case $lONE:ELSE;
        // todo (low): russian uses THREE (wtf?! oO) cases ($l[singular]:[plural1]:[plural2]) .. explode() chooses always the first plural option :/
        while (preg_match('/([\d\.]+)([^\d]*)(\$l:*)([^:]*):([^;]*);/i', $str, $m))
            $str = str_ireplace($m[1].$m[2].$m[3].$m[4].':'.$m[5].';', $m[1].$m[2].($m[1] == 1 ? $m[4] : explode(':', $m[5])[0]), $str);

    // step 6: HTMLize
        // colors
        $str = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $str);

        // line endings
        $str = strtr($str, ["\r" => '', "\n" => '<br />']);

        return array($str, []/*$relSpells*/);
    }

    public function renderBuff($level = MAX_LEVEL, $interactive = false)
    {
        if (!$this->curTpl)
            return array();

        // doesn't have a buff
        if (!$this->getField('buff', true))
            return array();

        $this->interactive = $interactive;

        $x = '<table><tr>';

        // spellName
        $x .= '<td><b class="q">'.$this->getField('name', true).'</b></td>';

        // dispelType (if applicable)
        if ($dispel = Lang::$game['dt'][$this->curTpl['dispelType']])
            $x .= '<th><b class="q">'.$dispel.'</b></th>';

        $x .= '</tr></table>';

        $x .= '<table><tr><td>';

        // parse Buff-Text
        $btt = $this->parseText('buff', $level, $this->interactive, $scaling);
        $x .= $btt[0].'<br>';

        // duration
        if ($this->curTpl['duration'] > 0)
            $x .= '<span class="q">'.sprintf(Lang::$spell['remaining'], Util::formatTime($this->curTpl['duration'])).'<span>';

        $x .= '</td></tr></table>';

        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':1:'.($scaling ? MAX_LEVEL : 1).':'.$level.'-->';

        return array($x, $btt[1]);
    }

    public function renderTooltip($level = MAX_LEVEL, $interactive = false)
    {
        if (!$this->curTpl)
            return array();

        $this->interactive = $interactive;

        // fetch needed texts
        $name  = $this->getField('name', true);
        $rank  = $this->getField('rank', true);
        $desc  = $this->parseText('description', $level, $this->interactive, $scaling);
        $tools = $this->getToolsForCurrent();
        $cool  = $this->createCooldownForCurrent();
        $cast  = $this->createCastTimeForCurrent();
        $cost  = $this->createPowerCostForCurrent();
        $range = $this->createRangesForCurrent();

        // get reagents
        $reagents = $this->getReagentsForCurrent();
        foreach ($reagents as &$r)
            $r[2] = ItemList::getName($r[0]);

        $reagents = array_reverse($reagents);

        // get stances (check: SPELL_ATTR2_NOT_NEED_SHAPESHIFT)
        $stances = '';
        if ($this->curTpl['stanceMask'] && !($this->curTpl['attributes2'] & 0x80000))
            $stances = Lang::$game['requires2'].' '.Lang::getStances($this->curTpl['stanceMask']);

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
                if ($this->curTpl['effect'.$idx.'Id'] == 53)// Enchantment (has createItem Scroll of Enchantment)
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
            $_ = Lang::$spell['tools'].':<br/><div class="indent q1">';
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
            $_ = Lang::$spell['reagents'].':<br/><div class="indent q1">';
            while ($reagent = array_pop($reagents))
            {
                $_ .= '<a href="?item='.$reagent[0].'">'.$reagent[2].'</a>';
                if ($reagent[1] > 1)
                    $_ .= ' ('.$reagent[1].')';

                $_ .= empty($reagents) ? '<br />' : ', ';
            }

            $xTmp[] = $_.'</div>';
        }

        if ($reqItems)
            $xTmp[] = Lang::$game['requires2'].' '.$reqItems;

        if ($desc[0])
            $xTmp[] = '<span class="q">'.$desc[0].'</span>';

        if ($createItem)
            $xTmp[] = $createItem;

        if ($xTmp)
            $x .= '<table><tr><td>'.implode('<br />', $xTmp).'</td></tr></table>';

        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':1:'.($scaling ? MAX_LEVEL : 1).':'.$level.'-->';

        return array($x, $desc ? $desc[1] : null);
    }

    public function getTalentHeadForCurrent()
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

    public function getColorsForCurrent()
    {
        $gry = $this->curTpl['skillLevelGrey'];
        $ylw = $this->curTpl['skillLevelYellow'];
        $grn = (int)(($ylw + $gry) / 2);
        $org = $this->curTpl['learnedAt'];

        if ($ylw > 1)
            return [$org, $ylw, $grn, $gry];
    }

    public function getListviewData($addInfoMask = 0x0)
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
                'icon'         => $this->curTpl['iconStringAlt'] ? $this->curTpl['iconStringAlt'] : $this->curTpl['iconString'],
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
            if (!empty($this->sources[$this->id]) && $s = $this->sources[$this->id])
                $data[$this->id]['source'] = array_keys($s);

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
                if ($mi = @$modelInfo[$this->id])
                {
                    $data[$this->id]['npcId']       = $mi['typeId'];
                    $data[$this->id]['displayId']   = $mi['displayId'];
                    $data[$this->id]['displayName'] = $mi['displayName'];
                }
            }
        }

        return $data;
    }

    public function getJSGlobals($addMask = GLOBALINFO_SELF, &$extra = [])
    {
        $data  = [];

        if ($this->relItems && ($addMask & GLOBALINFO_RELATED))
            $data = $this->relItems->getJSGlobals();

        foreach ($this->iterate() as $id => $__)
        {
            if ($addMask & GLOBALINFO_RELATED)
            {
                if ($mask = $this->curTpl['reqClassMask'])
                    for ($i = 0; $i < 11; $i++)
                        if ($mask & (1 << $i))
                            $data[TYPE_CLASS][$i + 1] = $i + 1;

                if ($mask = $this->curTpl['reqRaceMask'])
                    for ($i = 0; $i < 11; $i++)
                        if ($mask & (1 << $i))
                            $data[TYPE_RACE][$i + 1] = $i + 1;
            }

            if ($addMask & GLOBALINFO_SELF)
            {
                $iconString = $this->curTpl['iconStringAlt'] ? 'iconStringAlt' : 'iconString';

                $data[TYPE_SPELL][$id] = array(
                    'icon' => $this->curTpl[$iconString],
                    'name' => $this->getField('name', true),
                );
            }

            if ($addMask & GLOBALINFO_EXTRA)
            {
/*
spells / buffspells = {
    "58377": [["", "and 2 additional nearby targets "]],
    "103828": [["stunning", "rooting"], ["1 sec", "4 sec and reducing movement speed by 50% for 15 sec"]]
};
*/
                $buff = $this->renderBuff(MAX_LEVEL, true);
                $tTip = $this->renderTooltip(MAX_LEVEL, true);

                $extra[$id] = array(
                    'id'         => $id,
                    'tooltip'    => $tTip[0],
                    'buff'       => @$buff[0],
                    'spells'     => $tTip[1],
                    'buffspells' => @$buff[1]
                );
            }
        }

        return $data;
    }

    // mostly similar to TC
    public function getCastingTimeForBonus($asDOT = false)
    {
        $areaTargets = [7, 8, 15, 16, 20, 24, 30, 31, 33, 34, 37, 54, 56, 59, 104, 108];
        $castingTime = $this->IsChanneledSpell() ? $this->curTpl['duration'] : $this->curTpl['castTime'];

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
            if (in_array($this->curTpl['effect'.$i.'Id'], [2, 7, 8, 9, 62, 67]))
                $isDirect = true;
            else if (in_array($this->curTpl['effect'.$i.'AuraId'], [3, 8, 53]))
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
            $originalCastTime = $this->curTpl['castTime'];
            if ($this->curTpl['attributes0'] & 0x2)         // requires Ammo
                $originalCastTime += 500;

            if ($originalCastTime > 7000)
                $originalCastTime = 7000;

            if ($originalCastTime < 1500)
                $originalCastTime = 1500;

            // Portion to Over Time
            $PtOT = ($overTime / 15000) / (($overTime / 15000) + (OriginalCastTime / 3500));

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

}


class SpellListFilter extends Filter
{
    // sources in filter and general use different indizes
    private $enums = array(
        9 => array(
            1  => true,                                     // Any
            2  => false,                                    // None
            3  =>  1,                                       // Crafted
            4  =>  2,                                       // Drop
            6  =>  4,                                       // Quest
            7  =>  5,                                       // Vendor
            8  =>  6,                                       // Trainer
            9  =>  7,                                       // Discovery
            10 =>  9                                        // Talent
        )
    );

    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
         2 => [FILTER_CR_NUMERIC, 'powerCostPercent',                         ],    // prcntbasemanarequired
         3 => [FILTER_CR_BOOLEAN, 'spellFocusObject'                          ],    // requiresnearbyobject
         4 => [FILTER_CR_NUMERIC, 'trainingcost'                              ],    // trainingcost
         5 => [FILTER_CR_BOOLEAN, 'reqSpellId'                                ],    // requiresprofspec
        10 => [FILTER_CR_FLAG,    'cuFlags',          SPELL_CU_FIRST_RANK     ],    // firstrank
        12 => [FILTER_CR_FLAG,    'cuFlags',          SPELL_CU_LAST_RANK      ],    // lastrank
        13 => [FILTER_CR_NUMERIC, 'rankId',                                   ],    // rankno
        14 => [FILTER_CR_NUMERIC, 'id',               null,               true],    // id
        15 => [FILTER_CR_STRING,  'iconString',                               ],    // icon
        19 => [FILTER_CR_FLAG,    'attributes0',      0x80000                 ],    // scaling
        25 => [FILTER_CR_BOOLEAN, 'skillLevelYellow'                          ],    // rewardsskillups
        11 => [FILTER_CR_FLAG,    'cuFlags',          CUSTOM_HAS_COMMENT      ],    // hascomments
         8 => [FILTER_CR_FLAG,    'cuFlags',          CUSTOM_HAS_SCREENSHOT   ],    // hasscreenshots
        17 => [FILTER_CR_FLAG,    'cuFlags',          CUSTOM_HAS_VIDEO        ],    // hasvideos
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            case 1:                                         // costAbs [op] [int]
                if (!$this->isSaneNumeric($cr[2]))
                    break;

                if (!$this->int2Op($cr[1]))
                    break;

                return ['OR', ['AND', ['powerType', [1, 6]], ['powerCost', (10 * $cr[2]), $cr[1]]], ['AND', ['powerType', [1, 6], '!'], ['powerCost', $cr[2], $cr[1]]]];
            case 9:                                         // Source [enum]
                $_ = @$this->enums[$cr[0]][$cr[1]];
                if ($_ !== null)
                {
                    if (is_bool($_))
                        return ['source', 0, ($_ ? '!' : null)];
                    else if (is_int($_))
                        return ['source', '%'.$_.':%'];
                }
                break;
            case 20:                                        // has Reagents [yn]
                if ($this->int2Bool($cr[1]))
                {
                    if ($cr[1])
                        return ['OR', ['reagent1', 0, '>'], ['reagent2', 0, '>'], ['reagent3', 0, '>'], ['reagent4', 0, '>'], ['reagent5', 0, '>'], ['reagent6', 0, '>'], ['reagent7', 0, '>'], ['reagent8', 0, '>']];
                    else
                        return ['AND', ['reagent1', 0], ['reagent2', 0], ['reagent3', 0], ['reagent4', 0], ['reagent5', 0], ['reagent6', 0], ['reagent7', 0], ['reagent8', 0]];
                }
        }

        unset($cr);
        $this->error = true;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        //string (extended)
        if (isset($_v['na']))
        {
            $_ = [];
            if (isset($_v['ex']) && $_v['ex'] == 'on')
                $_ = $this->modularizeString(['name_loc'.User::$localeId, 'buff_loc'.User::$localeId, 'description_loc'.User::$localeId]);
            else
                $_ = $this->modularizeString(['name_loc'.User::$localeId]);

            if ($_)
                $parts[] = $_;
        }

        // spellLevel min                                   todo (low): talentSpells (typeCat -2) commonly have spellLevel 1 (and talentLevel >1) -> query is inaccurate
        if (isset($_v['minle']))
        {
            if (is_int($_v['minle']) && $_v['minle'] > 0)
                $parts[] = ['spellLevel', $_v['minle'], '>='];
            else
                unset($_v['minle']);
        }

        // spellLevel max
        if (isset($_v['maxle']))
        {
            if (is_int($_v['maxle']) && $_v['maxle'] > 0)
                $parts[] = ['spellLevel', $_v['maxle'], '<='];
            else
                unset($_v['maxle']);
        }

        // skillLevel min
        if (isset($_v['minrs']))
        {
            if (is_int($_v['minrs']) && $_v['minrs'] > 0)
                $parts[] = ['learnedAt', $_v['minrs'], '>='];
            else
                unset($_v['minrs']);
        }

        // skillLevel max
        if (isset($_v['maxrs']))
        {
            if (is_int($_v['maxrs']) && $_v['maxrs'] > 0)
                $parts[] = ['learnedAt', $_v['maxrs'], '<='];
            else
                unset($_v['maxrs']);
        }

        // race
        if (isset($_v['ra']))
        {
            if (in_array($_v['ra'], [1, 2, 3, 4, 5, 6, 7, 8, 10, 11]))
                $parts[] = ['AND', [['reqRaceMask', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'], ['reqRaceMask', $this->list2Mask($_v['ra']), '&']];
            else
                unset($_v['ra']);
        }

        // class [list]
        if (isset($_v['cl']))
        {
            $_ = (array)$_v['cl'];
            if (!array_diff($_, [1, 2, 3, 4, 5, 6, 7, 8, 9, 11]))
                $parts[] = ['reqClassMask', $this->list2Mask($_), '&'];
            else
                unset($_v['cl']);
        }

        // school [list]
        if (isset($_v['sc']))
        {
            $_ = (array)$_v['sc'];
            if (!array_diff($_, [0, 1, 2, 3, 4, 5, 6]))
                $parts[] = ['schoolMask', $this->list2Mask($_, true), '&'];
            else
                unset($_v['sc']);
        }

        // glyph type [list]                                wonky, admittedly, but consult SPELL_CU_* in defines and it makes sense
        if (isset($_v['gl']))
        {
            if (in_array($_v['gl'], [1, 2]))
                $parts[] = ['cuFlags', ($this->list2Mask($_v['gl']) << 6), '&'];
            else
                unset($_v['gl']);
        }

        // dispel type
        if (isset($_v['dt']))
        {
            if (in_array($_v['dt'], [1, 2, 3, 4, 5, 6, 9]))
                $parts[] = ['dispelType', $_v['dt']];
            else
                unset($_v['dt']);
        }

        // mechanic
        if (isset($_v['me']))
        {
            if ($_v['me'] > 0 && $_v['me'] < 32)
                $parts[] = ['OR', ['mechanic', $_v['me']], ['effect1Mechanic', $_v['me']], ['effect2Mechanic', $_v['me']], ['effect3Mechanic', $_v['me']]];
            else
                unset($_v['me']);
        }

        return $parts;
    }
}

?>
