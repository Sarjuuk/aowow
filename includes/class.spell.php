<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class SpellList extends BaseType
{
    public    $tooltips   = [];
    public    $buffs      = [];

    private   $spellVars  = [];
    private   $refSpells  = [];

    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_spell WHERE [filter] [cond] GROUP BY Id ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_spell WHERE [filter] [cond]';

    public function __construct($conditions)
    {
        parent::__construct($conditions);

        if ($this->error)
            return;

        // post processing
        $itemIcons = [];

        // if the spell creates an item use the itemIcon instead
        while ($this->iterate())
            if ($this->curTpl['effect1CreateItemId'])
                $itemIcons[(int)$this->curTpl['effect1CreateItemId']] = $this->id;

        if ($itemIcons)
        {
            $itemList = new ItemList(array(['i.entry', array_keys($itemIcons)]));
            while ($itemList->iterate())
                $this->templates[$itemIcons[$itemList->id]]['createItemString'] = $itemList->getField('icon');
        }

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
        $stats = [];

        while ($this->iterate())
        {
            for ($i = 1; $i <= 3; $i++)
            {
                if (!in_array($this->curTpl["effect".$i."AuraId"], [13, 22, 29, 34, 35, 83, 84, 85, 99, 124, 135, 143, 158, 161, 189, 230, 235, 240, 250]))
                    continue;

                $mv = $this->curTpl["effect".$i."MiscValue"];
                $bp = $this->curTpl["effect".$i."BasePoints"] + 1;

                switch ($this->curTpl["effect".$i."AuraId"])
                {
                    case 29:                                    // ModStat MiscVal:type
                    {
                        if ($mv < 0)                            // all stats
                        {
                            for ($j = 0; $j < 5; $j++)
                                @$stats[ITEM_MOD_AGILITY + $j] += $bp;
                        }
                        else                                    // one stat
                            @$stats[ITEM_MOD_AGILITY + $mv] += $bp;

                        break;
                    }
                    case 34:                                    // Increase Health
                    case 230:
                    case 250:
                    {
                        @$stats[ITEM_MOD_HEALTH] += $bp;
                        break;
                    }
                    case 13:                                    // damage splpwr + physical (dmg & any)
                    {
                        if ($mv == 1)                           // + weapon damage
                        {
                            @$stats[ITEM_MOD_WEAPON_DMG] += $bp;
                            break;
                        }

                        if ($mv == 0x7E)                        // full magic mask, also counts towards healing
                        {
                            @$stats[ITEM_MOD_SPELL_POWER] += $bp;
                            @$stats[ITEM_MOD_SPELL_DAMAGE_DONE] += $bp;
                        }
                        else
                        {
                            if ($mv & (1 << 1))                 // HolySpellpower (deprecated; still used in randomproperties)
                                @$stats[ITEM_MOD_HOLY_POWER] += $bp;

                            if ($mv & (1 << 2))                 // FireSpellpower (deprecated; still used in randomproperties)
                                @$stats[ITEM_MOD_FIRE_POWER] += $bp;

                            if ($mv & (1 << 3))                 // NatureSpellpower (deprecated; still used in randomproperties)
                                @$stats[ITEM_MOD_NATURE_POWER] += $bp;

                            if ($mv & (1 << 4))                 // FrostSpellpower (deprecated; still used in randomproperties)
                                @$stats[ITEM_MOD_FROST_POWER] += $bp;

                            if ($mv & (1 << 5))                 // ShadowSpellpower (deprecated; still used in randomproperties)
                                @$stats[ITEM_MOD_SHADOW_POWER] += $bp;

                            if ($mv & (1 << 6))                 // ArcaneSpellpower (deprecated; still used in randomproperties)
                                @$stats[ITEM_MOD_ARCANE_POWER] += $bp;
                        }

                        break;
                    }
                    case 135:                                   // healing splpwr (healing & any) .. not as a mask..
                    {
                        @$stats[ITEM_MOD_SPELL_POWER] += $bp;
                        @$stats[ITEM_MOD_SPELL_HEALING_DONE] += $bp;

                        break;
                    }
                    case 35:                                    // ModPower - MiscVal:type see defined Powers only energy/mana in use
                    {
                        if ($mv == -2)
                            @$stats[ITEM_MOD_HEALTH] += $bp;
                        if ($mv == 3)
                            @$stats[ITEM_MOD_ENERGY] += $bp;
                        else if ($mv == 0)
                            @$stats[ITEM_MOD_MANA] += $bp;
                        else if ($mv == 6)
                            @$stats[ITEM_MOD_RUNIC_POWER] += $bp;

                        break;
                    }
                    case 189:                                   // CombatRating MiscVal:ratingMask
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
                    case 143:                                   // Resistance MiscVal:school
                    case 83:
                    case 22:
                        if ($mv == 1)                           // Armor only if explixitly specified
                        {
                            @$stats[ITEM_MOD_ARMOR] += $bp;
                            break;
                        }

                        if ($mv == 2)                           // holy-resistance ONLY if explicitly specified (shouldn't even exist...)
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
                    case 84:                                    // hp5
                    case 161:
                        @$stats[ITEM_MOD_HEALTH_REGEN] += $bp;
                        break;
                    case 85:                                    // mp5
                        @$stats[ITEM_MOD_MANA_REGENERATION] += $bp;
                        break;
                    case 99:                                    // atkpwr
                        @$stats[ITEM_MOD_ATTACK_POWER] += $bp;
                        break;                                  // ?carries over to rngatkpwr?
                    case 124:                                   // rngatkpwr
                        @$stats[ITEM_MOD_RANGED_ATTACK_POWER] += $bp;
                        break;
                    case 158:                                   // blockvalue
                        @$stats[ITEM_MOD_BLOCK_VALUE] += $bp;
                        break;
                    case 240:                                   // ModExpertise
                        @$stats[ITEM_MOD_EXPERTISE_RATING] += $bp;
                        break;
                }
            }
        }

        return $stats;
    }

    // description-, buff-parsing component
    private function resolveEvaluation($formula, $level)
    {
        // todo: define unresolvable texts like AP, MHW, ect

        $pl    = $PL    = $level;
        $PlayerName     = Lang::$main['name'];
        $cond  = $COND  = function($a, $b, $c) { return $a ? $b : $c; };
        $eq    = $EQ    = function($a, $b)     { return $a == $b;     };
        $gt    = $GT    = function($a, $b)     { return $a > $b;      };
        $floor = $FLOOR = function($a)         { return floor($a);    };

        if (preg_match_all('/\$[a-z]+\b/i', $formula, $vars))
        {
            foreach ($vars[0] as $var)                      // oh lord, forgive me this sin .. but is_callable seems to bug out and function_exists doesn't find lambda-functions >.<
                if (@eval('return getType('.$var.');') != 'object')
                    return $formula;

            return eval('return '.$formula.';');
        }

        // there should not be any letters without a leading $
        return eval('return '.$formula.';');
    }

    // description-, buff-parsing component
    private function resolveVariableString($variable, $level)
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
            $this->refSpells[$lookup] = new SpellList(array(['id', $lookup]));

        switch ($var)
        {
            case 'a':                                       // EffectRadiusMin
            case 'A':                                       // EffectRadiusMax (ToDo)
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
/* where the heck is this var...?
            case 'c':                                       // BasePoints (raw)
                if ($lookup > 0 && $exprData[0])
                    $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                else
                    $spell = $this->curTpl;

                $base = $spell['effect'.$exprData[0].'BasePoints'] + 1;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                // Aura giving combat ratings (click-interactive)
                $rType = 0;
                if ($spell['effect'.$exprData[0].'AuraId'] == 189)
                {
                    $mv = $spell['effect'.$exprData[0].'MiscValue'];
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

                if ($rType)
                    $str .= '<!--rtg'.$rType.'-->'.$base."&nbsp;<small>(".Util::setRatingLevel($level, $rType, $base).")</small>";
                else
                    $str .= $base;

                $lastvalue = $base;
                break;
*/
            case 'd':                                       // SpellDuration
            case 'D':                                       // todo: min/max?
                if ($lookup)
                    $base = $this->refSpells[$lookup]->getField('duration');
                else
                    $base = $this->getField('duration');

                if ($base < 0)
                    return Lang::$spell['untilCanceled'];

                if ($op && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                return Util::formatTime(abs($base), true);
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
                return '&gt;'.$switch[0].'/'.$switch[1].'&lt;';
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
                    $base = $this->refSpells[$lookup]->getField('targets');
                else
                    $base = $this->getField('targets');

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

                if ($rType)
                    return '<!--rtg'.$rType.'-->'.abs($base)."&nbsp;<small>(".Util::setRatingLevel($level, $rType, abs($base)).")</small>";
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
                    $base     = $this->refSpells[$lookup]->getField('effect'.$effIdx.'BasePoints');
                    $add      = $this->refSpells[$lookup]->getField('effect'.$effIdx.'DieSides');
                    $periode  = $this->refSpells[$lookup]->getField('effect'.$effIdx.'Periode');
                    $duration = $this->refSpells[$lookup]->getField('duration');
                }
                else
                {
                    $base     = $this->getField('effect'.$effIdx.'BasePoints');
                    $add      = $this->getField('effect'.$effIdx.'DieSides');
                    $periode  = $this->getField('effect'.$effIdx.'Periode');
                    $duration = $this->getField('duration');
                }

                if (!$periode)
                    $periode = 3000;

                $tick  = $duration / $periode;
                $min   = abs($base + 1) * $tick;
                $max   = abs($base + $add) * $tick;
                $equal = $min == $max;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
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

                $min   = abs($base + 1);
                $max   = abs($base + $add);
                $equal = $min == $max;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
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

                if ($rType && $equal)
                    return '<!--rtg'.$rType.'-->'.$min."&nbsp;<small>(".Util::setRatingLevel($level, $rType, $min).")</small>";
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
    private function resolveFormulaString($formula, $precision = 0, $level)
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

            $formOutStr = $this->resolveFormulaString($formOutStr, $formPrecision, $level);

            $formula = substr_replace($formula, $formOutStr, $formStartPos, ($formCurPos - $formStartPos));
        }

        // step 2: resolve variables
        $pos = 0;                                           // continue strpos-search from this offset
        $str = '';
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
            $str .= $this->resolveVariableString($result, $level);
        }
        $str .= substr($formula, $pos);
        $str  = str_replace('#', '$', $str);                // reset marks

        // step 3: try to evaluate result
        $evaled = $this->resolveEvaluation($str, $level);

        return (float)$evaled ? number_format($evaled, $precision) : $evaled;
    }

    // should probably used only once to create ?_spell. come to think of it, it yields the same results every time.. it absolutely has to!
    // although it seems to be pretty fast, even on those pesky test-spells with extra complex tooltips (Ron Test Spell X))
    public function parseText($type = 'description', $level = MAX_LEVEL)
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
            $c          - todo: not found but in use below.. wtf ?!
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
        // aura- or spell-conditions cant be resolved for our purposes, so force them to false for now (todo: strg+f "know" in aowowPower.js ^.^)
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
            else if (!empty($matches[2]))                   // aura/spell-condition .. use false; TODO (low priority) catch cases and port "know"-param for tooltips from 5.0
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

            // check for precision-modifiers .. yes, the precrements fullfill a role!
            if ($formCurPos + 2 < strlen($data) && $data[++$formCurPos] == '.')
                if ($prec = $data[++$formCurPos])
                {
                    $formPrecision = (int)$prec;
                    ++$formCurPos;                          // for some odd reason the precision decimal survives if wo dont increment further..
                }

            $formOutStr = $this->resolveFormulaString($formOutStr, $formPrecision, $level);

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

            $resolved = $this->resolveVariableString($result, $level);
            $str .= intVal($resolved) ? abs($resolved) : resolved;
        }
        $str .= substr($data, $pos);
        $str = str_replace('#', '$', $str);                 // reset marks

    // step 5: variable-depentant variable-text
        // special case $lONE:ELSE;
        while (preg_match('/([\d\.]+) \$l([\w\s]*):([\w\s]*);/i', $str, $m))
            $str = str_replace($m[1].' $l'.$m[2].':'.$m[3].';', $m[1].' '.($m[1] == 1 ? $m[2] : $m[3]), $str);

    // step 6: HTMLize
        // colors
        $str = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $str);

        // line endings
        $str = strtr($str, ["\r" => '', "\n" => '<br />']);

        return $str;
    }

    public function renderBuff($Id = 0)
    {
        while ($this->iterate())
        {
            if ($Id && $this->id != $Id)
                continue;

            // doesn't have a buff
            if (!Util::localizedString($this->curTpl, 'buff'))
                return '';

            $x = '<table><tr>';

            // spellName
            $x .= '<td><b class="q">'.Util::localizedString($this->curTpl, 'name').'</b></td>';

            // dispelType (if applicable)
            if ($dispel = Lang::$game['di'][$this->curTpl['dispelType']])
                $x .= '<th><b class="q">'.$dispel.'</b></th>';

            $x .= '</tr></table>';

            $x .= '<table><tr><td>';

            // parse Buff-Text
            $x .= $this->parseText('buff').'<br>';

            // duration
            if ($this->curTpl['duration'] > 0)
                $x .= '<span class="q">'.sprintf(Lang::$spell['remaining'], Util::formatTime($this->curTpl['duration'])).'<span>';

            $x .= '</td></tr></table>';

            $this->buffs[$this->id] = $x;
        }

        return $Id ? $this->buffs[$Id] : true;
    }

    public function renderTooltip($Id = 0)
    {
        while ($this->iterate())
        {
            if ($Id && $this->id != $Id)
                continue;

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

            // get tools
            $tools = [];
            for ($i = 1; $i <= 2; $i++)
            {
                // Tools
                if ($this->curTpl['tool'.$i])
                    $tools[$i-1] = array('itemId' => $this->curTpl['tool'.$i], 'name' => ItemList::getName($this->curTpl['tool'.$i]));

                // TotemCategory
                if ($this->curTpl['toolCategory'.$i])
                {
                    $tc = DB::Aowow()->selectRow('SELECT * FROM aowow_totemcategory WHERE id = ?d', $this->curTpl['toolCategory'.$i]);
                    $tools[$i+1] = array('categoryMask' => $tc['categoryMask'], 'name' => Util::localizedString($tc, 'name'));
                }
            }
            $tools = array_reverse($tools);

            // get description
            $desc = $this->parseText('description');

            $reqWrapper  = $this->curTpl['rangeMaxHostile'] && ($this->curTpl['powerCost'] > 0 || $this->curTpl['powerCostPercent'] > 0);
            $reqWrapper2 = $reagents ||$tools || $desc;

            $x = '';
            $x .= '<table><tr><td>';

            $rankText = Util::localizedString($this->curTpl, 'rank');

            if (!empty($rankText))
                $x .= '<table width="100%"><tr><td>';

            // name
            $x .= '<b>'.$this->names[$this->id].'</b>';

            // rank
            if (!empty($rankText))
                $x .= '<br /></td><th><b class="q0">'.$rankText.'</b></th></tr></table>';


            if ($reqWrapper)
                $x .= '<table width="100%"><tr><td>';

            // check for custom PowerDisplay
            $pt = $this->curTpl['powerDisplayString'] ? $this->curTpl['powerDisplayString'] : $this->curTpl['powerType'];

            // power cost: pct over static
            if ($this->curTpl['powerCostPercent'] > 0)
                $x .= $this->curTpl['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$pt]));
            else if ($this->curTpl['powerCost'] > 0 || $this->curTpl['powerPerSecond'] > 0 || $this->curTpl['powerCostPerLevel'] > 0)
                $x .= ($pt == 1 ? $this->curTpl['powerCost'] / 10 : $this->curTpl['powerCost']).' '.ucFirst(Lang::$spell['powerTypes'][$pt]);

            // append periodic cost
            if ($this->curTpl['powerPerSecond'] > 0)
                $x .= sprintf(Lang::$spell['costPerSec'], $this->curTpl['powerPerSecond']);

            // append level cost
            if ($this->curTpl['powerCostPerLevel'] > 0)
                $x .= sprintf(Lang::$spell['costPerLevel'], $this->curTpl['powerCostPerLevel']);

            $x .= '<br />';

            if ($reqWrapper)
                $x .= '</td><th>';

            // ranges
            if ($this->curTpl['rangeMaxHostile'])
            {
                // minRange exists; show as range
                if ($this->curTpl['rangeMinHostile'])
                    $x .= sprintf(Lang::$spell['range'], $this->curTpl['rangeMinHostile'].' - '.$this->curTpl['rangeMaxHostile']).'<br />';
                // friend and hostile differ; do color
                else if ($this->curTpl['rangeMaxHostile'] != $this->curTpl['rangeMaxFriend'])
                    $x .= sprintf(Lang::$spell['range'], '<span class="q10">'.$this->curTpl['rangeMaxHostile'].'</span> - <span class="q2">'.$this->curTpl['rangeMaxHostile']. '</span>').'<br />';
                // hardcode: "melee range"
                else if ($this->curTpl['rangeMaxHostile'] == 5)
                    $x .= Lang::$spell['meleeRange'].'<br />';
                // regular case
                else
                    $x .= sprintf(Lang::$spell['range'], $this->curTpl['rangeMaxHostile']).'<br />';
            }

            if ($reqWrapper)
                $x .= '</th></tr></table>';

            $x .= '<table width="100%"><tr><td>';

            // cast times
            if ($this->curTpl['interruptFlagsChannel'])
                $x .= Lang::$spell['channeled'];
            else if ($this->curTpl['castTime'])
                $x .= sprintf(Lang::$spell['castIn'], $this->curTpl['castTime'] / 1000);
            else if ($this->curTpl['attributes0'] & 0x10)       // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only
                $x .= Lang::$spell['instantPhys'];
            else                                                // instant cast
                $x .= Lang::$spell['instantMagic'];

            $x .= '</td>';

            // cooldown or categorycooldown
            if ($this->curTpl['recoveryTime'])
                $x.= '<th>'.sprintf(Lang::$game['cooldown'], Util::formatTime($this->curTpl['recoveryTime'], true)).'</th>';
            else if ($this->curTpl['recoveryCategory'])
                $x.= '<th>'.sprintf(Lang::$game['cooldown'], Util::formatTime($this->curTpl['recoveryCategory'], true)).'</th>';

            $x .= '</tr>';

            if ($this->curTpl['stanceMask'])
                $x.= '<tr><td colspan="2">'.Lang::$game['requires'].' '.Lang::getStances($this->curTpl['stanceMask']).'</td></tr>';

            $x .= '</table>';
            $x .= '</td></tr></table>';

            if ($reqWrapper2)
                $x .= '<table>';

            if ($tools)
            {
                $x .= '<tr><td>';
                $x .= Lang::$spell['tools'].':<br/><div class="indent q1">';
                while ($tool = array_pop($tools))
                {
                    if (isset($tool['itemId']))
                        $x .= '<a href="?item='.$tool['itemId'].'">'.$tool['name'].'</a>';
                    else if (isset($tool['totemCategory']))
                        $x .= '<a href="?items&filter=cr=91;crs='.$tool['totemCategory'].';crv=0=">'.$tool['name'].'</a>';
                    else
                        $x .= $tool['name'];

                    if (!empty($tools))
                        $x .= ', ';
                    else
                        $x .= '<br />';
                }
                $x .= '</div></td></tr>';
            }

            if ($reagents)
            {
                $x .= '<tr><td>';
                $x .= Lang::$spell['reagents'].':<br/><div class="indent q1">';
                while ($reagent = array_pop($reagents))
                {
                    $x .= '<a href="?item='.$reagent['id'].'">'.$reagent['name'].'</a>';
                    if ($reagent['count'] > 1)
                        $x .= ' ('.$reagent['count'].')';

                    if(!empty($reagents))
                        $x .= ', ';
                    else
                        $x .= '<br />';
                }
                $x .= '</div></td></tr>';
            }

            if($desc && $desc <> '_empty_')
                $x .= '<tr><td><span class="q">'.$desc.'</span></td></tr>';

            if ($reqWrapper2)
                $x .= "</table>";

            // append created items (may need improvement)
            for ($i = 1; $i <= 3; $i++)
            {
                if ($cId = $this->curTpl['effect'.$i.'CreateItemId'])
                {
                    $x .= '<br />'.(new ItemList(array(['i.entry', (int)$cId])))->renderTooltip();
                    break;
                }
            }

            $this->tooltips[$this->id] = $x;
        }

        return $Id ? $this->tooltips[$Id] : true;
    }

    public function getTalentHeadForCurrent()
    {
        // upper: cost :: range
        // lower: time :: cooldown
        $x = '';

        // power cost: pct over static
        $cost = '';

        if ($this->curTpl['powerCostPercent'] > 0)
            $cost .= $this->curTpl['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$this->curTpl['powerType']]));
        else if ($this->curTpl['powerCost'] > 0 || $this->curTpl['powerPerSecond'] > 0 || $this->curTpl['powerCostPerLevel'] > 0)
            $cost .= ($this->curTpl['powerType'] == 1 ? $this->curTpl['powerCost'] / 10 : $this->curTpl['powerCost']).' '.ucFirst(Lang::$spell['powerTypes'][$this->curTpl['powerType']]);

        // append periodic cost
        if ($this->curTpl['powerPerSecond'] > 0)
            $cost .= sprintf(Lang::$spell['costPerSec'], $this->curTpl['powerPerSecond']);

        // append level cost
        if ($this->curTpl['powerCostPerLevel'] > 0)
            $cost .= sprintf(Lang::$spell['costPerLevel'], $this->curTpl['powerCostPerLevel']);

        // ranges
        $range = '';

        if ($this->curTpl['rangeMaxHostile'])
        {
            // minRange exists; show as range
            if ($this->curTpl['rangeMinHostile'])
                $range .= sprintf(Lang::$spell['range'], $this->curTpl['rangeMinHostile'].' - '.$this->curTpl['rangeMaxHostile']);
            // friend and hostile differ; do color
            else if ($this->curTpl['rangeMaxHostile'] != $this->curTpl['rangeMaxFriend'])
                $range .= sprintf(Lang::$spell['range'], '<span class="q10">'.$this->curTpl['rangeMaxHostile'].'</span> - <span class="q2">'.$this->curTpl['rangeMaxHostile']. '</span>');
            // hardcode: "melee range"
            else if ($this->curTpl['rangeMaxHostile'] == 5)
                $range .= Lang::$spell['meleeRange'];
            // regular case
            else
                $range .= sprintf(Lang::$spell['range'], $this->curTpl['rangeMaxHostile']);
        }

        // cast times
        $time = '';

        if ($this->curTpl['interruptFlagsChannel'])
            $time .= Lang::$spell['channeled'];
        else if ($this->curTpl['castTime'])
            $time .= sprintf(Lang::$spell['castIn'], $this->curTpl['castTime'] / 1000);
        else if ($this->curTpl['attributes0'] & 0x10)       // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only
            $time .= Lang::$spell['instantPhys'];
        else                                                // instant cast
            $time .= Lang::$spell['instantMagic'];

        // cooldown or categorycooldown
        $cool = '';

        if ($this->curTpl['recoveryTime'])
            $cool.= sprintf(Lang::$game['cooldown'], Util::formatTime($this->curTpl['recoveryTime'], true)).'</th>';
        else if ($this->curTpl['recoveryCategory'])
            $cool.= sprintf(Lang::$game['cooldown'], Util::formatTime($this->curTpl['recoveryCategory'], true)).'</th>';


        // assemble parts

        // upper
        if ($cost && $range)
            $x .= '<table width="100%"><tr><td>'.$cost.'</td><th>'.$range.'</th></tr></table>';
        else if ($cost)
            $x .= $cost;
        else if ($range)
            $x .= $range;

        if (($cost xor $range) && ($time xor $cool))
            $x .= '<br />';

        // lower
        if ($time && $cool)
            $x .= '<table width="100%"><tr><td>'.$time.'</td><th>'.$cool.'</th></tr></table>';
        else if ($time)
            $x .= $time;
        else if ($cool)
            $x .= $cool;

        return $x;
    }

    public function getListviewData()
    {
        // this is going to be .. ""fun""

        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'name'  => $this->names[$this->id],
                'icon'  => $this->curTpl['iconString'],
                'level' => $this->curTpl['baseLevel'],
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        if (!isset($refs['gSpells']))
            $refs['gSpells'] = [];

        while ($this->iterate())
        {
            $iconString = isset($this->curTpl['createItemString']) ? 'createItemString' : 'iconString';

            $refs['gSpells'][$this->id] = array(
                'icon' => $this->curTpl[$iconString],
                'name' => $this->names[$this->id],
            );
        }
    }

    public function addRewardsToJScript(&$refs) { }
}

?>
