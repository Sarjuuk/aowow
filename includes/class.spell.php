<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class Spell extends BaseType
{
    public    $tooltip    = '';
    public    $buff       = '';

    protected $setupQuery = 'SELECT * FROM ?_spell WHERE Id = ?d';

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT * FROM ?_spell WHERE id = ?d', $id );
        return Util::localizedString($n, 'name');
    }
    // end static use

    // required for item-sets-bonuses and socket-bonuses
    public function getStatGain()
    {
        $stats = [];
        for ($i = 1; $i <= 3; $i++)
        {
            if (!in_array($this->template["effect".$i."AuraId"], [13, 22, 29, 34, 35, 83, 84, 85, 99, 124, 135, 143, 158, 161, 189, 230, 235, 240, 250]))
                continue;

            $mv = $this->template["effect".$i."MiscValue"];
            $bp = $this->template["effect".$i."BasePoints"] + 1;

            switch ($this->template["effect".$i."AuraId"])
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
        return $stats;
    }

    // TODO: optimize, complete, not go stark raving mad
    public function parseText($type = 'description', $level = MAX_LEVEL)
    {
        $lastduration = array('durationBase' => $this->template['duration']);

        $signs = array('+', '-', '/', '*', '%', '^');

        $data = Util::localizedString($this->template, $type);
        if (empty($data) || $data =="[]")                   // empty tooltip shouldn't be displayed anyway
            return null;

        // line endings
        $data = strtr($data, array("\r" => '', "\n" => '<br />'));

        // colors
        $data = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $data);

        $pos = 0;
        $str = '';
        while(false!==($npos=strpos($data, '$', $pos)))
        {
            if($npos!=$pos)
                $str .= substr($data, $pos, $npos-$pos);
            $pos = $npos+1;

            if('$' == substr($data, $pos, 1))
            {
                $str .= '$';
                $pos++;
                continue;
            }

            if(!preg_match('/^((([+\-\/*])(\d+);)?(\d*)(?:([lg].*?:.*?);|(\w\d*)))/', substr($data, $pos), $result))
                continue;

            if(empty($exprData[0]))
                $exprData[0] = 1;

            $op = $result[3];
            $oparg = $result[4];
            $lookup = $result[5];
            $var = $result[6] ? $result[6] : $result[7];
            $pos += strlen($result[0]);

            if(!$var)
                continue;

            $exprType = strtolower(substr($var, 0, 1));
            $exprData = explode(':', substr($var, 1));
            switch($exprType)
            {
                case 'r':
                    // if(!IsSet($this->template['rangeMaxHostile']))
                        // $this->template = array_merge($this->template, DB::Aowow()->selectRow('SELECT * FROM ?_spellrange WHERE rangeID=? LIMIT 1', $this->template['rangeID']));

                    $base = $this->template['rangeMaxHostile'];

                    if($op && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= $base;
                    break;
                case 'z':
                    $str .= htmlspecialchars('<Home>');
                    break;
                case 'c': ###
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    // Aura giving combat ratings
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
                case 's': ###
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue, effect'.$exprData[0].'DieSides FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    if(!$exprData[0]) $exprData[0]=1;
                        @$base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    // Aura giving combat ratings
                    $rType = 0;
                    if (@$spell['effect'.$exprData[0].'AuraId'] == 189)
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

                    if ($rType && $spell['effect'.$exprData[0].'DieSides'] <= 1)
                        $str .= '<!--rtg'.$rType.'-->'.abs($base)."&nbsp;<small>(".Util::setRatingLevel($level, $rType, abs($base)).")</small>";
                    else
                        @$str .= abs($base).($spell['effect'.$exprData[0].'DieSides'] > 1 ? Lang::$game['valueDelim'].abs(($base+$spell['effect'.$exprData[0].'DieSides'])) : '');

                    $lastvalue = $base;
                    break;
                case 'o':
                    if($lookup > 0 && $exprData[0])
                    {
                        $spell = DB::Aowow()->selectRow('SELECT duration, effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'Periode, effect'.$exprData[0].'DieSides FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                        // $lastduration = DB::Aowow()->selectRow('SELECT * FROM ?_spellduration WHERE durationID=? LIMIT 1', $spell['durationID']);
                    }
                    else
                        $spell = $this->template;

                    if(!$exprData[0]) $exprData[0] = 1;
                    $base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if($spell['effect'.$exprData[0].'Periode'] <= 0) $spell['effect'.$exprData[0].'Periode'] = 5000;

                    $str .= (($spell['duration'] / $spell['effect'.$exprData[0].'Periode']) * abs($base).($spell['effect'.$exprData[0].'DieSides'] > 1 ? '-'.abs(($base+$spell['effect'.$exprData[0].'DieSides'])) : ''));
                    break;
                case 't':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT * FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    if(!$exprData[0]) $exprData[0]=1;
                        $base = $spell['effect'.$exprData[0].'Periode']/1000;

                    // TODO!!
                    if($base==0)    $base=1;
                    // !!TODO

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'm': ###
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    // TODO!!
                    if(!$exprData[0]) $exprData[0] = 1;

                    $base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    // Aura giving combat ratings
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
                        $str .= '<!--rtg'.$rType.'-->'.abs($base)."&nbsp;<small>(".Util::setRatingLevel($level, $rType, abs($base)).")</small>";
                    else
                        $str .= abs($base);

                    $lastvalue = $base;
                    break;
                case 'x':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'ChainTarget FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect'.$exprData[0].'ChainTarget'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'q':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    if(!($exprData[0]))
                        $exprData[0]=1;
                    $base = $spell['effect'.$exprData[0].'MiscValue'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'a':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect1RadiusMax, effect2RadiusMax, effect3RadiusMax FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $exprData[0] = 1; // TODO
                    $radius = $this->template['effect'.$exprData[0].'RadiusMax'];
                    $base = $radius;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'h':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT procChance FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['procChance'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'f':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT dmg_multiplier'.$exprData[0].' FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['dmg_multiplier'.$exprData[0]];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'n':
                    if($lookup > 0)
                        $spell = DB::Aowow()->selectRow('SELECT procCharges FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['procCharges'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'd':
                    if($lookup > 0)
                    {
                        $spell = DB::Aowow()->selectRow('SELECT duration as durationBase FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                        @$base = ($spell['durationBase'] > 0 ? $spell['durationBase'] + 1 : 0);
                    }
                    else
                        $base = ($lastduration['durationBase'] > 0 ? $lastduration['durationBase'] : 0);

                    if($op && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= Util::formatTime($base, true);
                    break;
                case 'i':
                    // $base = $this->template['spellTargets'];
                    $base = $this->template['targets'];

                    if($op && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= $base;
                    break;
                case 'e':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect_'.$exprData[0].'_proc_value FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect_'.$exprData[0].'_proc_value'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    $str .= $base;
                    $lastvalue = $base;
                    break;
                case 'v':
                    $base = $spell['affected_target_level'];

                    if($op && $oparg > 0 && $base > 0)
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= $base;
                    break;
                case 'u':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=?d LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    // $base = $spell['effect_'.$exprData[0].'_misc_value'];
                    if(isset($spell['effect'.$exprData[0].'MiscValue']))
                        $base = $spell['effect'.$exprData[0].'MiscValue'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'b': // only used at one spell (14179) should be 20, column 110/111/112?)
                    if($lookup > 0)
                        $spell = DB::Aowow()->selectRow('SELECT effect_'.$exprData[0].'_proc_chance FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect'.$exprData[0].'PointsPerComboPoint'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'l':
                    if($lastvalue > 1)
                        $str .= $exprData[1];
                    else
                        $str .= $exprData[0];
                    break;
                case 'g':
                    $str .= $exprData[0];
                    break;
                default:
                    $str .= "[{$var} ($op::$oparg::$lookup::$exprData[0])]";
            }
        }
        $str .= substr($data, $pos);
        $str = @preg_replace_callback("|\{([^\}]+)\}|", create_function('$matches', 'return eval("return abs(".$matches[1].");");'), $str);

        return $str;
    }

    public function getBuff()
    {
        // doesn't have a buff
        if (!Util::localizedString($this->template, 'buff'))
            return '';

        $x = '<table><tr>';

        // spellName
        $x .= '<td><b class="q">'.Util::localizedString($this->template, 'name').'</b></td>';

        // dispelType (if applicable)
        if ($dispel = Lang::$game['di'][$this->template['dispelType']])
            $x .= '<th><b class="q">'.$dispel.'</b></th>';

        $x .= '</tr></table>';

        $x .= '<table><tr><td>';

        // parse Buff-Text
        $x .= $this->parseText('buff').'<br>';

        // duration
        if ($this->template['duration'])
            $x .= '<span class="q">'.sprintf(Lang::$spell['remaining'], Util::formatTime($this->template['duration'])).'<span>';

        $x .= '</td></tr></table>';

        $this->buff = $x;

        return $this->buff;
    }

    public function getTooltip()
    {
        // get reagents
        $reagents = array();
        for ($j = 1; $j <= 8; $j++)
        {
            if($this->template['reagent'.$j] <= 0)
                continue;

            $reagents[] = array(
                'id' => $this->template['reagent'.$j],
                'name' => Item::getName($this->template['reagent'.$j]),
                'count' => $this->template['reagentCount'.$j]          // if < 0 : require, but don't use
            );
        }
        $reagents = array_reverse($reagents);

        // get tools
        $tools = array();
        for ($i = 1; $i <= 2; $i++)
        {
            // Tools
            if ($this->template['tool'.$i])
                $tools[$i-1] = array('itemId' => $this->template['tool'.$i], 'name' => Item::getName($this->template['tool'.$i]));

            // TotemCategory
            if ($this->template['toolCategory'.$i])
            {
                $tc = DB::Aowow()->selectRow('SELECT * FROM aowow_totemcategory WHERE id = ?d', $this->template['toolCategory'.$i]);
                $tools[$i+1] = array('categoryMask' => $tc['categoryMask'], 'name' => Util::localizedString($tc, 'name'));
            }
        }
        $tools = array_reverse($tools);

        // get description
        $desc = $this->parseText('description');

        $reqWrapper  = $this->template['rangeMaxHostile'] && ($this->template['powerCost'] > 0 || $this->template['powerCostPercent'] > 0);
        $reqWrapper2 = $reagents ||$tools || $desc;

        $x = '';
        $x .= '<table><tr><td>';

        $rankText = Util::localizedString($this->template, 'rank');

        if (!empty($rankText))
            $x .= '<table width="100%"><tr><td>';

        // name
        $x .= '<b>'.Util::localizedString($this->template, 'name').'</b>';

        // rank
        if (!empty($rankText))
            $x .= '<br /></td><th><b class="q0">'.$rankText.'</b></th></tr></table>';


        if ($reqWrapper)
            $x .= '<table width="100%"><tr><td>';

        // check for custom PowerDisplay
        $pt = $this->template['powerDisplayString'] ? $this->template['powerDisplayString'] : $this->template['powerType'];

        // power cost: pct over static
        if ($this->template['powerCostPercent'] > 0)
            $x .= $this->template['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$pt]));
        else if ($this->template['powerCost'] > 0 || $this->template['powerPerSecond'] > 0 || $this->template['powerCostPerLevel'] > 0)
            $x .= ($pt == 1 ? $this->template['powerCost'] / 10 : $this->template['powerCost']).' '.ucFirst(Lang::$spell['powerTypes'][$pt]);

        // append periodic cost
        if ($this->template['powerPerSecond'] > 0)
            $x .= sprintf(Lang::$spell['costPerSec'], $this->template['powerPerSecond']);

        // append level cost
        if ($this->template['powerCostPerLevel'] > 0)
            $x .= sprintf(Lang::$spell['costPerLevel'], $this->template['powerCostPerLevel']);

        $x .= '<br />';

        if ($reqWrapper)
            $x .= '</td><th>';

        // ranges
        if ($this->template['rangeMaxHostile'])
        {
            // minRange exists; show as range
            if ($this->template['rangeMinHostile'])
                $x .= sprintf(Lang::$spell['range'], $this->template['rangeMinHostile'].' - '.$this->template['rangeMaxHostile']).'<br />';
            // friend and hostile differ; do color
            else if ($this->template['rangeMaxHostile'] != $this->template['rangeMaxFriend'])
                $x .= sprintf(Lang::$spell['range'], '<span class="q10">'.$this->template['rangeMaxHostile'].'</span> - <span class="q2">'.$this->template['rangeMaxHostile']. '</span>').'<br />';
            // hardcode: "melee range"
            else if ($this->template['rangeMaxHostile'] == 5)
                $x .= Lang::$spell['meleeRange'].'<br />';
            // regular case
            else
                $x .= sprintf(Lang::$spell['range'], $this->template['rangeMaxHostile']).'<br />';
        }

        if ($reqWrapper)
            $x .= '</th></tr></table>';

        $x .= '<table width="100%"><tr><td>';

        // cast times
        if ($this->template['interruptFlagsChannel'])
            $x .= Lang::$spell['channeled'];
        else if ($this->template['castTime'])
            $x .= sprintf(Lang::$spell['castIn'], $this->template['castTime'] / 1000);
        else if ($this->template['attributes0'] & 0x10)     // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only
            $x .= Lang::$spell['instantPhys'];
        else                                                // instant cast
            $x .= Lang::$spell['instantMagic'];

        $x .= '</td>';

        // cooldown or categorycooldown
        if ($this->template['recoveryTime'])
            $x.= '<th>'.sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryTime'], true)).'</th>';
        else if ($this->template['recoveryCategory'])
            $x.= '<th>'.sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryCategory'], true)).'</th>';

        $x .= '</tr>';

        if ($this->template['stanceMask'])
            $x.= '<tr><td colspan="2">'.Lang::$game['requires'].' '.Lang::getStances($this->template['stanceMask']).'</td></tr>';

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

        $this->tooltip = $x;

        return $this->tooltip;
    }

    public function getTalentHead()
    {
        // upper: cost :: range
        // lower: time :: cool
        $x = '';

        // power cost: pct over static
        $cost = '';

        if ($this->template['powerCostPercent'] > 0)
            $cost .= $this->template['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$this->template['powerType']]));
        else if ($this->template['powerCost'] > 0 || $this->template['powerPerSecond'] > 0 || $this->template['powerCostPerLevel'] > 0)
            $cost .= ($this->template['powerType'] == 1 ? $this->template['powerCost'] / 10 : $this->template['powerCost']).' '.ucFirst(Lang::$spell['powerTypes'][$this->template['powerType']]);

        // append periodic cost
        if ($this->template['powerPerSecond'] > 0)
            $cost .= sprintf(Lang::$spell['costPerSec'], $this->template['powerPerSecond']);

        // append level cost
        if ($this->template['powerCostPerLevel'] > 0)
            $cost .= sprintf(Lang::$spell['costPerLevel'], $this->template['powerCostPerLevel']);

        // ranges
        $range = '';

        if ($this->template['rangeMaxHostile'])
        {
            // minRange exists; show as range
            if ($this->template['rangeMinHostile'])
                $range .= sprintf(Lang::$spell['range'], $this->template['rangeMinHostile'].' - '.$this->template['rangeMaxHostile']);
            // friend and hostile differ; do color
            else if ($this->template['rangeMaxHostile'] != $this->template['rangeMaxFriend'])
                $range .= sprintf(Lang::$spell['range'], '<span class="q10">'.$this->template['rangeMaxHostile'].'</span> - <span class="q2">'.$this->template['rangeMaxHostile']. '</span>');
            // hardcode: "melee range"
            else if ($this->template['rangeMaxHostile'] == 5)
                $range .= Lang::$spell['meleeRange'];
            // regular case
            else
                $range .= sprintf(Lang::$spell['range'], $this->template['rangeMaxHostile']);
        }

        // cast times
        $time = '';

        if ($this->template['interruptFlagsChannel'])
            $time .= Lang::$spell['channeled'];
        else if ($this->template['castTime'])
            $time .= sprintf(Lang::$spell['castIn'], $this->template['castTime'] / 1000);
        else if ($this->template['attributes0'] & 0x10)     // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only
            $time .= Lang::$spell['instantPhys'];
        else                                                // instant cast
            $time .= Lang::$spell['instantMagic'];

        // cooldown or categorycooldown
        $cool = '';

        if ($this->template['recoveryTime'])
            $cool.= sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryTime'], true)).'</th>';
        else if ($this->template['recoveryCategory'])
            $cool.= sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryCategory'], true)).'</th>';


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
        return array(
            'id'       => $this->Id,
            'name'     => Util::localizedString($this->template, 'name'),
        );
    }

    public function addGlobalsToJScript(&$gSpells)
    {
        // if the spell creates an item use the itemIcon instead
        if ($this->template['effect1CreateItemId'])
        {
            $item = new Item($this->template['effect1CreateItemId']);
            $iconString = $item->template['icon'];
        }
        else
            $iconString = $this->template['iconString'];

        $gSpells[$this->Id] = array(
            'icon' => $iconString,
            'name' => Util::localizedString($this->template, 'name'),
        );
    }
}



class SpellList extends BaseTypeList
{
    protected $setupQuery = 'SELECT *, Id AS ARRAY_KEY FROM ?_spell WHERE [filter] [cond] GROUP BY id';

    public function __construct($conditions)
    {
        // may be called without filtering
        if (class_exists('SpellFilter'))
        {
            $this->filter = new SpellFilter();
            if (($fiData = $this->filter->init()) === false)
                return;
        }

        parent::__construct($conditions);
    }
}

?>
