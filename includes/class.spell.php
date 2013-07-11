<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class SpellList extends BaseType
{
    use listviewHelper;

    public        $tooltips    = [];
    public        $buffs       = [];
    public        $ranks       = [];
    public        $relItems    = null;
    public        $sources     = [];

    public static $type        = TYPE_SPELL;
    public static $skillLines  = array(
         6 => [43, 44, 45, 46, 54, 55, 95, 118, 136, 160, 162, 172, 173, 176, 226, 228, 229, 473],  // Weapons
         8 => [293, 413, 414, 415, 433],                                                            // Armor
         9 => [129, 185, 356, 762],                                                                 // sec. Professions
        10 => [98, 109, 111, 113, 115, 137, 138, 139, 140, 141, 313, 315, 673, 759],                // Languages
        11 => [164, 165, 171, 182, 186, 197, 202, 333, 393, 755, 773]                               // prim. Professions
    );

    public static $spellTypes  = array(
         6 => 1,
         8 => 2,
        10 => 4
    );

    private       $spellVars   = [];
    private       $refSpells   = [];
    private       $tools       = [];
    private       $interactive = false;
    private       $charLevel   = MAX_LEVEL;

    protected     $setupQuery  = 'SELECT *, id AS ARRAY_KEY FROM ?_spell s WHERE [filter] [cond]';
    protected     $matchQuery  = 'SELECT COUNT(*) FROM ?_spell s WHERE [filter] [cond]';

    public function __construct($conditions, $applyFilter = false)
    {
        parent::__construct($conditions, $applyFilter);

        if ($this->error)
            return;

        // post processing
        $foo = [];
        while ($this->iterate())
        {
            // required for globals
            for ($i = 1; $i <= 3; $i++)
            {
                if ($this->canCreateItem())
                    $foo[] = (int)$this->curTpl['effect'.$i.'CreateItemId'];
            }

            for ($i = 1; $i <= 8; $i++)
                if ($this->curTpl['reagent'.$i] > 0)
                    $foo[] = (int)$this->curTpl['reagent'.$i];

            for ($i = 1; $i <= 2; $i++)
                if ($this->curTpl['tool'.$i] > 0)
                    $foo[] = (int)$this->curTpl['tool'.$i];

            // ranks
            $this->ranks[$this->id] = Util::localizedString($this->curTpl, 'rank');

            // sources
            if (!empty($this->curTpl['source']))
            {
                $sources = explode(' ', $this->curTpl['source']);
                foreach ($sources as $src)
                {
                    $src = explode(':', $src);
                    if ($src[0] != -3)                      // todo (high): sourcemore - implement after items
                        $this->sources[$this->id][$src[0]][] = $src[1];
                }
            }

            // unpack skillLines
            $this->curTpl['skillLines'] = [];
            if ($this->curTpl['skillLine1'] < 0)
            {
                foreach (Util::$skillLineMask[$this->curTpl['skillLine1']] as $idx => $pair)
                    if ($this->curTpl['skillLine2OrMask'] & (1 << $idx))
                        $this->curTpl['skillLines'][] = $pair[1];
            }
            else if ($sec = $this->curTpl['skillLine2OrMask'])
            {
                if ($this->id == 818)                       // and another hack .. basic Campfire (818) has deprecated skill Survival (142) as first skillLine
                    $this->curTpl['skillLines'] = [$sec, $this->curTpl['skillLine1']];
                else
                    $this->curTpl['skillLines'] = [$this->curTpl['skillLine1'], $sec];
            }
            else if ($prim = $this->curTpl['skillLine1'])
                $this->curTpl['skillLines'] = [$prim];

            unset($this->curTpl['skillLine1']);
            unset($this->curTpl['skillLine2OrMask']);
            $this->templates[$this->id] = $this->curTpl;

        }

        if ($foo)
            $this->relItems = new ItemList(array(['i.entry', array_unique($foo)], 0));

        $this->reset();                                     // restore 'iterator'
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

        while ($this->iterate())
        {
            $stats = [];

            for ($i = 1; $i <= 3; $i++)
            {
                if (!in_array($this->curTpl["effect".$i."AuraId"], [8, 13, 22, 29, 34, 35, 83, 84, 85, 99, 124, 135, 143, 158, 161, 189, 230, 235, 240, 250]))
                    continue;

                $mv = $this->curTpl["effect".$i."MiscValue"];
                $bp = $this->curTpl["effect".$i."BasePoints"] + 1;

                switch ($this->curTpl["effect".$i."AuraId"])
                {
                    case 29:                                // ModStat MiscVal:type
                    {
                        if ($mv < 0)                        // all stats
                        {
                            for ($j = 0; $j < 5; $j++)
                                @$stats[ITEM_MOD_AGILITY + $j] += $bp;
                        }
                        else                                // one stat
                            @$stats[ITEM_MOD_AGILITY + $mv] += $bp;

                        break;
                    }
                    case 34:                                // Increase Health
                    case 230:
                    case 250:
                    {
                        @$stats[ITEM_MOD_HEALTH] += $bp;
                        break;
                    }
                    case 13:                                // damage splpwr + physical (dmg & any)
                    {
                        // + weapon damage
                        if ($mv == (1 << SPELL_SCHOOL_NORMAL))
                        {
                            @$stats[ITEM_MOD_WEAPON_DMG] += $bp;
                            break;
                        }

                        // full magic mask, also counts towards healing
                        if ($mv == 0x7E)
                        {
                            @$stats[ITEM_MOD_SPELL_POWER] += $bp;
                            @$stats[ITEM_MOD_SPELL_DAMAGE_DONE] += $bp;
                        }
                        else
                        {
                            // HolySpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_HOLY))
                                @$stats[ITEM_MOD_HOLY_POWER] += $bp;

                            // FireSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_FIRE))
                                @$stats[ITEM_MOD_FIRE_POWER] += $bp;

                            // NatureSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_NATURE))
                                @$stats[ITEM_MOD_NATURE_POWER] += $bp;

                            // FrostSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_FROST))
                                @$stats[ITEM_MOD_FROST_POWER] += $bp;

                            // ShadowSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_SHADOW))
                                @$stats[ITEM_MOD_SHADOW_POWER] += $bp;

                            // ArcaneSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_ARCANE))
                                @$stats[ITEM_MOD_ARCANE_POWER] += $bp;
                        }

                        break;
                    }
                    case 135:                               // healing splpwr (healing & any) .. not as a mask..
                    {
                        @$stats[ITEM_MOD_SPELL_HEALING_DONE] += $bp;

                        break;
                    }
                    case 35:                                // ModPower - MiscVal:type see defined Powers only energy/mana in use
                    {
                        if ($mv == POWER_HEALTH)
                            @$stats[ITEM_MOD_HEALTH] += $bp;
                        if ($mv == POWER_ENERGY)
                            @$stats[ITEM_MOD_ENERGY] += $bp;
                        else if ($mv == POWER_MANA)
                            @$stats[ITEM_MOD_MANA] += $bp;
                        else if ($mv == POWER_RUNIC_POWER)
                            @$stats[ITEM_MOD_RUNIC_POWER] += $bp;

                        break;
                    }
                    case 189:                               // CombatRating MiscVal:ratingMask
                        // special case: resilience -  consists of 3 ratings strung together. MOD_CRIT_TAKEN_MELEE|RANGED|SPELL (14,15,16)
                        if (($mv & 0x1C000) == 0x1C000)
                            @$stats[ITEM_MOD_RESILIENCE_RATING] += $bp;

                        for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                        {
                            if (!Util::$combatRatingToItemMod[$j])
                                continue;

                            if (($mv & (1 << $j)) == 0)
                                continue;

                            @$stats[Util::$combatRatingToItemMod[$j]] += $bp;
                        }
                        break;
                    case 143:                               // Resistance MiscVal:school
                    case 83:
                    case 22:
                        if ($mv == 1)                       // Armor only if explicitly specified
                        {
                            @$stats[ITEM_MOD_ARMOR] += $bp;
                            break;
                        }

                        if ($mv == 2)                       // holy-resistance ONLY if explicitly specified (shouldn't even exist...)
                        {
                            @$stats[ITEM_MOD_HOLY_RESISTANCE] += $bp;
                            break;
                        }

                        for ($j = 0; $j < 7; $j++)
                        {
                            if (($mv & (1 << $j)) == 0)
                                continue;

                            switch ($j)
                            {
                                case 2:
                                    @$stats[ITEM_MOD_FIRE_RESISTANCE] += $bp;
                                    break;
                                case 3:
                                    @$stats[ITEM_MOD_NATURE_RESISTANCE] += $bp;
                                    break;
                                case 4:
                                    @$stats[ITEM_MOD_FROST_RESISTANCE] += $bp;
                                    break;
                                case 5:
                                    @$stats[ITEM_MOD_SHADOW_RESISTANCE] += $bp;
                                    break;
                                case 6:
                                    @$stats[ITEM_MOD_ARCANE_RESISTANCE] += $bp;
                                    break;
                            }
                        }
                        break;
                    case 8:                                 // hp5
                    case 84:
                    case 161:
                        @$stats[ITEM_MOD_HEALTH_REGEN] += $bp;
                        break;
                    case 85:                                // mp5
                        @$stats[ITEM_MOD_MANA_REGENERATION] += $bp;
                        break;
                    case 99:                                // atkpwr
                        @$stats[ITEM_MOD_ATTACK_POWER] += $bp;
                        break;                              // ?carries over to rngatkpwr?
                    case 124:                               // rngatkpwr
                        @$stats[ITEM_MOD_RANGED_ATTACK_POWER] += $bp;
                        break;
                    case 158:                               // blockvalue
                        @$stats[ITEM_MOD_BLOCK_VALUE] += $bp;
                        break;
                    case 240:                               // ModExpertise
                        @$stats[ITEM_MOD_EXPERTISE_RATING] += $bp;
                        break;
                }
            }

            $data[$this->id] = $stats;
        }

        return $data;
    }

    // halper
    private function getToolsForCurrent()
    {
        if ($this->tools)
            return $this->tools;

        $tools = [];
        for ($i = 1; $i <= 2; $i++)
        {
            // Tools
            if ($_ = $this->curTpl['tool'.$i])
            {
                while ($this->relItems->id != $_)
                    $this->relItems->iterate();

                $tools[$i-1] = array(
                    'itemId'  => $_,
                    'name'    => $this->relItems->getField('name', true),
                    'quality' => $this->relItems->getField('quality')
                );
            }

            // TotemCategory
            if ($_ = $this->curTpl['toolCategory'.$i])
            {
                $tc = DB::Aowow()->selectRow('SELECT * FROM ?_totemcategory WHERE id = ?d', $_);
                $tools[$i+1] = array(
                    'id' => $_,
                    'name' => Util::localizedString($tc, 'name'));
            }
        }

        $this->tools = array_reverse($tools);

        return $this->tools;
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

    private function createPowerCostForCurrent()
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
                $runes[] = $_." ".Lang::$spell['powerRunes'][2];
            if ($_ = (($rCost & 0x030) >> 4))
                $runes[] = $_." ".Lang::$spell['powerRunes'][1];
            if ($_ = ($rCost & 0x003))
                $runes[] = $_." ".Lang::$spell['powerRunes'][0];

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

    private function createCastTimeForCurrent($short = true, $noInstant = true)
    {
        if ($this->curTpl['interruptFlagsChannel'])
            return Lang::$spell['channeled'];
        else if ($this->curTpl['castTime'] > 0)
            return $short ? sprintf(Lang::$spell['castIn'], $this->curTpl['castTime'] / 1000) : Util::formatTime($this->curTpl['castTime']);
        // show instant only for player/pet/npc abilities (todo (low): unsure when really hidden (like talent-case))
        else if ($noInstant && !in_array($this->curTpl['typeCat'], [11, 7, -3, -8, 0]) && !($this->curTpl['cuFlags'] & SPELL_CU_TALENTSPELL))
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

        if ($this->curTpl['attributes1'] & 0x200)           // never a referenced spell, ALWAYS $this; SPELL_ATTR1_MELEE_COMBAT_SPELL: 0x200
        {
            if ($level > $maxLvl && $maxLvl > 0)
                $level = $maxLvl;
            else if ($level < $baseLvl)
                $level = $baseLvl;

            $level -= $ref->getField('spellLevel');
            $base  += (int)($level * $rppl);
        }

        switch ($add)                                       // roll in a range <1;EffectDieSides> as of patch 3.3.3
        {
            case 0:
            case 1:                                         // range 1..1
                return [
                    $base + $add,
                    $base + $add
                ];
            default:
                return [
                    $base + 1,
                    $base + $add
                ];
        }
    }

    public function canCreateItem()
    {
        // 24: createItem; 34: changeItem; 59: randomItem; 66: createManaGem; 157: createitem2; 86: channelDeathItem
        for ($i = 1; $i < 4; $i++)
            if (in_array($this->curTpl['effect'.$i.'Id'], [24, 34, 59, 66, 157]) || $this->curTpl['effect'.$i.'AuraId'] == 86)
                return true;

        return false;
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
        $floor = $FLOOR = function($a)         { return floor($a);    };

        if (preg_match_all('/\$[a-z]+\b/i', $formula, $vars))
        {
            $evalable = true;

            foreach ($vars[0] as $var)                      // oh lord, forgive me this sin .. but is_callable seems to bug out and function_exists doesn't find lambda-functions >.<
            {
                if (@eval('return getType('.$var.');') != 'object')
                {
                    $evalable = false;
                    break;
                }
            }

            if (!$evalable)
            {
                // can't eval constructs because of strings present. replace constructs with strings
                $cond  = $COND  = !$this->interactive ? 'COND'  : sprintf(Util::$dfnString, 'COND(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>, <span class=\'q1\'>c</span>)<br /> <span class=\'q1\'>a</span> ? <span class=\'q1\'>b</span> : <span class=\'q1\'>c</span>', 'COND');
                $eq    = $EQ    = !$this->interactive ? 'EQ'    : sprintf(Util::$dfnString, 'EQ(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> == <span class=\'q1\'>b</span>', 'EQ');
                $gt    = $GT    = !$this->interactive ? 'GT'    : sprintf(Util::$dfnString, 'GT(<span class=\'q1\'>a</span>, <span class=\'q1\'>b</span>)<br /> <span class=\'q1\'>a</span> > <span class=\'q1\'>b</span>', 'GT');
                $floor = $FLOOR = !$this->interactive ? 'FLOOR' : sprintf(Util::$dfnString, 'FLOOR(<span class=\'q1\'>a</span>)', 'FLOOR');
                $pl    = $PL    = !$this->interactive ? 'PL'    : sprintf(Util::$dfnString, 'LANG.level', 'PL');

                // note the " !
                return eval('return "'.$formula.'";');
            }
            else
                return eval('return '.$formula.';');
        }

        // hm, minor eval-issue. eval doesnt understand two operators without a space between them (eg. spelll: 18126)
        $formula = preg_replace('/(\+|-|\*|\/)(\+|-|\*|\/)/i', '\1 \2', $formula);

        // there should not be any letters without a leading $
        return eval('return '.$formula.';');
    }

    // description-, buff-parsing component
    private function resolveVariableString($variable)
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

                if ($base < 0)
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
                {
                    for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                    {
                        if (!Util::$combatRatingToItemMod[$j])
                            continue;

                        if (($mv & (1 << $j)) == 0)
                            continue;

                        $rType = Util::$combatRatingToItemMod[$j];
                        break;
                    }
                }
                // Aura end

                if ($rType && $this->interactive)
                    return '<!--rtg'.$rType.'-->'.abs($base).'&nbsp;<small>('.sprintf(Util::$setRatingLevelString, $this->charLevel, $rType, abs($base), Util::setRatingLevel($this->charLevel, $rType, abs($base))).')</small>';
                else if ($rType)
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
                    list($min, $max) = $this->calculateAmountForCurrent($effIdx, $this->refSpells[$lookup]);
                    $periode  = $this->refSpells[$lookup]->getField('effect'.$effIdx.'Periode');
                    $duration = $this->refSpells[$lookup]->getField('duration');
                }
                else
                {
                    list($min, $max) = $this->calculateAmountForCurrent($effIdx);
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
                    list($min, $max) = $this->calculateAmountForCurrent($effIdx, $this->refSpells[$lookup]);
                    $mv   = $this->refSpells[$lookup]->getField('effect'.$effIdx.'MiscValue');
                    $aura = $this->refSpells[$lookup]->getField('effect'.$effIdx.'AuraId');
                }
                else
                {
                    list($min, $max) = $this->calculateAmountForCurrent($effIdx);
                    $mv   = $this->getField('effect'.$effIdx.'MiscValue');
                    $aura = $this->getField('effect'.$effIdx.'AuraId');
                }
                $equal = $min == $max;

                if (in_array($op, $signs) && is_numeric($oparg))
                    if ($equal)
                        eval("\$min = $min $op $oparg;");

                // Aura giving combat ratings
                $rType = 0;
                if ($aura == 189)
                {
                    for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                    {
                        if (!Util::$combatRatingToItemMod[$j])
                            continue;

                        if (($mv & (1 << $j)) == 0)
                            continue;

                        $rType = Util::$combatRatingToItemMod[$j];
                        break;
                    }
                }
                // Aura end

                if ($rType && $equal && $this->interactive)
                    return '<!--rtg'.$rType.'-->'.$min.'&nbsp;<small>('.sprintf(Util::$setRatingLevelString, $this->charLevel, $rType, $min, Util::setRatingLevel($this->charLevel, $rType, $min)).')</small>';
                else if ($rType && $equal)
                    return '<!--rtg'.$rType.'-->'.$min.'&nbsp;<small>('.Util::setRatingLevel($this->charLevel, $rType, $min).')</small>';
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
    private function resolveFormulaString($formula, $precision = 0)
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

            $formOutStr = $this->resolveFormulaString($formOutStr, $formPrecision);

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

            $var = $this->resolveVariableString($result);
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
    public function parseText($type = 'description', $level = MAX_LEVEL, $interactive = false)
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
            $l          - LastValue-Switch; last value as condiition $Ltrue:false;
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
    */

    $this->interactive = $interactive;
    $this->charLevel   = $level;

    // step 0: get text
        $data = Util::localizedString($this->curTpl, $type);
        if (empty($data) || $data == "[]")                  // empty tooltip shouldn't be displayed anyway
            return null;

    // step 1: if the text is supplemented with text-variables, get and replace them
        if (empty($this->spellVars[$this->id]) && $this->curTpl['spellDescriptionVariableId'] > 0)
        {
            $spellVars = DB::Aowow()->SelectCell('SELECT vars FROM ?_spellVariables WHERE id = ?d', $this->curTpl['spellDescriptionVariableId']);
            $spellVars = explode("\n", $spellVars);
            foreach ($spellVars as $sv)
                if (preg_match('/\$(\w*\d*)=(.*)/i', trim($sv), $matches))
                    $this->spellVars[$this->id][$matches[1]] = $matches[2];

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

            $condOutStr  = '';

            if (!empty($matches[3]))                        // we can do this! -> eval
            {
                $cnd = eval('return ('.$matches[3].');');
                if (is_numeric($cnd) && $cnd)               // only case, deviating from normal; positive result -> use [true]
                    $targetPart = 1;

                $condStartPos = strpos($data, $matches[3]) - 2;
                $condCurPos   = $condStartPos;

            }
            else if (!empty($matches[2]))                   // aura/spell-condition .. use false; TODO (low): catch cases and port "know"-param for tooltips from 5.0
            {                                               // tooltip_enus: Charge to an enemy, stunning it <!--sp58377:0--><!--sp58377-->for <!--sp103828:0-->1 sec<!--sp103828-->.; spells_enus: {"58377": [["", "and 2 additional nearby targets "]], "103828": [["1 sec", "3 sec"]]};
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
            $formOutStr = $this->resolveFormulaString($formOutStr, $formPrecision);

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

            $var = $this->resolveVariableString($result);
            $resolved = is_array($var) ? $var[0] : $var;
            $str .= is_numeric($resolved) ? abs($resolved) : $resolved;
            if (is_array($var))
                $str .= ' '.$var[1];
        }
        $str .= substr($data, $pos);
        $str = str_replace('#', '$', $str);                 // reset marker

    // step 5: variable-depentant variable-text
        // special case $lONE:ELSE;
        // todo (low): russian uses THREE (wtf?! oO) cases ($l[singular]:[plural1]:[plural2]) .. explode() chooses always the first plural option :/
        while (preg_match('/([\d\.]+)([^\d]*)(\$l:*)([^:]*):([^;]*);/i', $str, $m))
            $str = str_ireplace($m[1].$m[2].$m[3].$m[4].':'.$m[5].';', $m[1].$m[2].($m[1] == 1 ? $m[4] : explode(':', $m[5])[0]), $str);

    // step 6: HTMLize
        // colors
        $str = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $str);

        // line endings
        $str = strtr($str, ["\r" => '', "\n" => '<br />']);

        return $str;
    }

    public function renderBuff($level = MAX_LEVEL, $interactive = false)
    {
        if (!$this->curTpl)
            return null;

        if (isset($this->buffs[$this->id]))
            return $this->buffs[$this->id];

        // doesn't have a buff
        if (!Util::localizedString($this->curTpl, 'buff'))
            return '';

        $this->interactive = $interactive;

        $x = '<table><tr>';

        // spellName
        $x .= '<td><b class="q">'.Util::localizedString($this->curTpl, 'name').'</b></td>';

        // dispelType (if applicable)
        if ($dispel = Lang::$game['dt'][$this->curTpl['dispelType']])
            $x .= '<th><b class="q">'.$dispel.'</b></th>';

        $x .= '</tr></table>';

        $x .= '<table><tr><td>';

        // parse Buff-Text
        $x .= $this->parseText('buff', $level, $this->interactive).'<br>';

        // duration
        if ($this->curTpl['duration'] > 0)
            $x .= '<span class="q">'.sprintf(Lang::$spell['remaining'], Util::formatTime($this->curTpl['duration'])).'<span>';

        $x .= '</td></tr></table>';

        $this->buffs[$this->id] = $x;

        return $x;
    }

    public function renderTooltip($level = MAX_LEVEL, $interactive = false)
    {
        if (!$this->curTpl)
            return null;

        if (isset($this->tooltips[$this->id]))
            return $this->tooltips[$this->id];

        $this->interactive = $interactive;

        // fetch needed texts
        $name  = $this->getField('name', true);
        $rank  = $this->getField('rank', true);
        $desc  = $this->parseText('description', $level, $this->interactive);
        $tools = $this->getToolsForCurrent();
        $cool  = $this->createCooldownForCurrent();
        $cast  = $this->createCastTimeForCurrent();
        $cost  = $this->createPowerCostForCurrent();
        $range = $this->createRangesForCurrent();

        // get reagents
        $reagents = [];
        for ($j = 1; $j <= 8; $j++)
        {
            if($this->curTpl['reagent'.$j] <= 0)
                continue;

            $reagents[] = array(
                'id' => $this->curTpl['reagent'.$j],
                'name' => ItemList::getName($this->curTpl['reagent'.$j]),
                'count' => $this->curTpl['reagentCount'.$j]          // if < 0 : require, but don't use
            );
        }
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
        if ($this->curTpl['typeCat'] == 11)                 // only Professions
        {
            for ($i = 1; $i <= 3; $i++)
            {
                if ($this->curTpl['effect'.$i.'Id'] == 53)  // Enchantment (has createItem Scroll of Enchantment)
                    continue;

                if ($cId = $this->curTpl['effect'.$i.'CreateItemId'])
                {
                    $createItem = (new ItemList(array(['i.entry', (int)$cId])))->renderTooltip([], true, true);
                    break;
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
                $_ .= '<a href="?item='.$reagent['id'].'">'.$reagent['name'].'</a>';
                if ($reagent['count'] > 1)
                    $_ .= ' ('.$reagent['count'].')';

                if(!empty($reagents))
                    $_ .= ', ';
                else
                    $_ .= '<br />';
            }

            $xTmp[] = $_.'</div>';
        }

        if ($reqItems)
            $xTmp[] = Lang::$game['requires2'].' '.$reqItems;

        if ($desc)
            $xTmp[] = '<span class="q">'.$desc.'</span>';

        if ($createItem)
            $xTmp[] = '<br />'.$createItem;

        if ($tools || $reagents || $reqItems || $desc || $createItem)
            $x .= '<table><tr><td>'.implode('<br />', $xTmp).'</td></tr></table>';

        $this->tooltips[$this->id] = $x;

        return $x;
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
        {
            return [
                $org < $ylw ? $org : 0,
                $org < $grn ? $ylw : 0,
                $org < $gry ? $grn : 0,
                $gry
            ];
        }
    }

    public function getListviewData()
    {
        $data = [];
        while ($this->iterate())
        {
            $quality = ($this->curTpl['cuFlags'] & SPELL_CU_QUALITY_MASK) >> 8;
            $talent  = $this->curTpl['cuFlags'] & (SPELL_CU_TALENT | SPELL_CU_TALENTSPELL) && $this->curTpl['spellLevel'] <= 1;

            $data[$this->id] = array(
                'id'           => $this->id,
                'quality'      => $quality ? $quality : '@',
                'name'         => $this->getField('name', true),
                'icon'         => $this->curTpl['iconStringAlt'] ? $this->curTpl['iconStringAlt'] : $this->curTpl['iconString'],
                'level'        => $talent ? $this->curTpl['talentLevel'] : $this->curTpl['spellLevel'],
                'school'       => $this->curTpl['schoolMask'],
                'cat'          => $this->curTpl['typeCat'],
                'trainingcost' => $this->curTpl['trainingCost'],
                'skill'        => count($this->curTpl['skillLines']) > 4 ? array_merge(array_splice($this->curTpl['skillLines'], 0, 4), [-1]): $this->curTpl['skillLines'], // display max 4 skillLines (fills max three lines in listview)
                'reagents'     => [],
                'source'       => []
            );

            // Sources
            if (!empty($this->sources[$this->id]) && $s = $this->sources[$this->id])
                $data[$this->id]['source'] = json_encode(array_keys($s), JSON_NUMERIC_CHECK);

            // Proficiencies
            if ($this->curTpl['typeCat'] == -11)
                foreach (self::$spellTypes as $cat => $type)
                    if (in_array($this->curTpl['skillLines'][0], self::$skillLines[$cat]))
                        $data[$this->id]['type'] = $type;

            // creates item
            for ($i = 1; $i <= 3; $i++)
            {
                if ($this->curTpl['effect'.$i.'Id'] != 24)
                    continue;

                $max = $this->curTpl['effect'.$i.'DieSides'] + $this->curTpl['effect'.$i.'BasePoints'];
                $min = $this->curTpl['effect'.$i.'DieSides'] > 1 ? 1 : $max;

                $data[$this->id]['creates'] = [$this->curTpl['effect'.$i.'CreateItemId'], $min, $max];
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

            // reagents
            for ($i = 1; $i <= 8; $i++)
                if ($this->curTpl['reagent'.$i] > 0 && $this->curTpl['reagentCount'.$i] > 0)
                    $data[$this->id]['reagents'][] = [$this->curTpl['reagent'.$i], $this->curTpl['reagentCount'.$i]];

            // glyph
            if ($this->curTpl['typeCat'] == -13)
                $data[$this->id]['glyphtype'] = $this->curTpl['cuFlags'] & SPELL_CU_GLYPH_MAJOR ? 1 : 2;

            if ($r = Util::localizedString($this->curTpl, 'rank'))
                $data[$this->id]['rank'] = $r;

            if (!empty($this->curTpl['reqClassMask']))
            {
                $mask = $this->curTpl['reqClassMask'] & CLASS_MASK_ALL;
                if ($mask && $mask != CLASS_MASK_ALL)
                    $data[$this->id]['reqclass'] = $mask;
            }

            if (!empty($this->curTpl['reqRaceMask']))
            {
                $mask = $this->curTpl['reqRaceMask'] & RACE_MASK_ALL;
                if ($mask && $mask != RACE_MASK_ALL)
                    $data[$this->id]['reqrace'] = $mask;
            }
        }

        return $data;
    }

    public function getDetailPageData()
    {
        $result = array(
            'id'        => $this->id,
            'name'      => $this->getField('name', true),
            'icon'      => $this->curTpl['iconString'],
            'stack'     => $this->curTpl['stackAmount'],
            'powerCost' => $this->createPowerCostForCurrent(),
            'level'     => $this->curTpl['spellLevel'],
            'rangeName' => $this->getField('rangeText', true),
            'range'     => $this->curTpl['rangeMaxHostile'],
            'castTime'  => $this->createCastTimeForCurrent(false, false),
            'cooldown'  => $this->curTpl['recoveryTime'] > 0 ? Util::formatTime($this->curTpl['recoveryTime']) : '<span class="q0">'.Lang::$main['n_a'].'</span>',
            'gcd'       => Util::formatTime($this->curTpl['startRecoveryTime']),
            'gcdCat'    => "[NYI]",
            'duration'  => $this->curTpl['duration'] > 0 ? Util::formatTime($this->curTpl['duration']) : '<span class="q0">'.Lang::$main['n_a'].'</span>',
            'school'    => sprintf(Util::$dfnString, Util::asHex($this->getField('schoolMask')), Lang::getMagicSchools($this->getField('schoolMask'))),
            'dispel'    => isset(Lang::$game['dt'][$this->curTpl['dispelType']]) ? Lang::$game['dt'][$this->curTpl['dispelType']] : '<span class="q0">'.Lang::$main['n_a'].'</span>',
            'mechanic'  => isset(Lang::$game['me'][$this->curTpl['mechanic']]) ? Lang::$game['me'][$this->curTpl['mechanic']] : '<span class="q0">'.Lang::$main['n_a'].'</span>',
            'stances'   => $this->curTpl['attributes2'] & 0x80000 ? '' : Lang::getStances($this->curTpl['stanceMask']),
            'tools'     => $this->getToolsForCurrent(),
            'reagents'  => [],
            'items'     => []
        );

        // minRange exists..  prepend
        if ($_ = $this->curTpl['rangeMinHostile'])
            $result['range'] = $_.' - '.$result['range'];

        // fill reagents
        for ($i = 1; $i < 9; $i++)
            if ($this->curTpl['reagent'.$i] > 0 && $this->curTpl['reagentCount'.$i] > 0)
                $result['reagents'][$this->curTpl['reagent'.$i]] = $this->curTpl['reagentCount'.$i];

        // parse itemClass & itemSubClassMask
        $class    = $this->getField('equippedItemClass');
        $subClass = $this->getField('equippedItemSubClassMask');

        if ($class > 0 && $subClass > 0)
        {
            $title = ['CLASS: '.$class, 'SUBCLASS: '.Util::asHex($subClass)];
            $text  = Lang::getRequiredItems($class, $subClass, false);

            if ($invType = $this->getField('equippedItemInventoryTypeMask'))
            {
                // remove some duplicated strings if both are used
                if (($invType & 0x100020) == 0x100020)             // Chest and Robe set
                    $invType &= ~0x20;

                if (($invType & 0x404000) == 0x404000)             // Off-hand and Shield set
                    $invType &= ~0x4000;

                if (($invType & 0x4008000) == 0x4008000)           // Ranged and Ranged (right) set
                    $invType &= ~0x8000;

                $_ = [];
                $strs = Lang::$item['inventoryType'];
                foreach ($strs as $k => $str)
                    if ($invType & 1 << $k && $str)
                        $_[] = $str;

                $title[] = 'INVENTORYTYPE: '.Util::asHex($invType);
                $text   .= ' '.Lang::$spell['_inSlot'].': '.implode(', ', $_);
            }

            $result['items'] = sprintf(Util::$dfnString, implode(' - ', $title), $text);
        }

        return $result;
    }

    public function addGlobalsToJScript(&$template, $addMask = GLOBALINFO_ANY)
    {
        if ($this->relItems && ($addMask & GLOBALINFO_RELATED))
        {
            $this->relItems->reset();
            $this->relItems->addGlobalsToJscript($template);
        }

        while ($this->iterate())
        {
            if ($addMask & GLOBALINFO_RELATED)
            {
                if ($mask = $this->curTpl['reqClassMask'])
                    for ($i = 0; $i < 11; $i++)
                        if ($mask & (1 << $i))
                            $template->extendGlobalIds(TYPE_CLASS, $i + 1);

                if ($mask = $this->curTpl['reqRaceMask'])
                    for ($i = 0; $i < 11; $i++)
                        if ($mask & (1 << $i))
                            $template->extendGlobalIds(TYPE_RACE, $i + 1);
            }

            if ($addMask & GLOBALINFO_SELF)
            {
                $iconString = $this->curTpl['iconStringAlt'] ? 'iconStringAlt' : 'iconString';

                $template->extendGlobalData(self::$type, [$this->id => array(
                    'icon' => $this->curTpl[$iconString],
                    'name' => $this->getField('name', true),
                )]);
            }
        }
    }

}

?>
