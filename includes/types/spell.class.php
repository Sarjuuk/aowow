<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellList extends BaseType
{
    use listviewHelper;

    public          $ranks       = [];
    public          $relItems    = null;
    public          $sources     = [];

    public static   $type        = TYPE_SPELL;
    public static   $brickFile   = 'spell';
    public static   $dataTable   = '?_spell';

    public static   $skillLines  = array(
         6 => [ 43,  44,  45,  46,  54,  55,  95, 118, 136, 160, 162, 172, 173, 176, 226, 228, 229, 473], // Weapons
         8 => [293, 413, 414, 415, 433],                                                                  // Armor
         9 => [129, 185, 356, 762],                                                                       // sec. Professions
        10 => [ 98, 109, 111, 113, 115, 137, 138, 139, 140, 141, 313, 315, 673, 759],                     // Languages
        11 => [164, 165, 171, 182, 186, 197, 202, 333, 393, 755, 773]                                     // prim. Professions
    );

    public static   $spellTypes  = array(
         6 => 1,
         8 => 2,
        10 => 4
    );

    public static   $effects     = array(
        'heal'       => [ 0,/*3,*/10,  67,  75, 136                         ],  // <no effect>, /*Dummy*/, Heal, Heal Max Health, Heal Mechanical, Heal Percent
        'damage'     => [ 0,  2,   3,   9,  62                              ],  // <no effect>, Dummy, School Damage, Health Leech, Power Burn
        'itemCreate' => [24, 34,  59,  66, 157                              ],  // createItem, changeItem, randomItem, createManaGem, createItem2
        'trigger'    => [ 3, 32,  64, 101, 142, 148, 151, 152, 155, 160, 164],  // dummy, trigger missile, trigger spell, feed pet, force cast, force cast with value, unk, trigger spell 2, unk, dualwield 2H, unk, remove aura
        'teach'      => [36, 57, /*133*/                                    ]   // learn spell, learn pet spell, /*unlearn specialization*/
    );

    public static   $auras       = array(
        'heal'       => [ 4,  8, 62, 69,  97, 226                           ],  // Dummy, Periodic Heal, Periodic Health Funnel, School Absorb, Mana Shield, Periodic Dummy
        'damage'     => [ 3,  4, 15, 53,  89, 162, 226                      ],  // Periodic Damage, Dummy, Damage Shield, Periodic Health Leech, Periodic Damage Percent, Power Burn Mana, Periodic Dummy
        'itemCreate' => [86                                                 ],  // Channel Death Item
        'trigger'    => [ 4, 23, 42, 48, 109, 226, 227, 231, 236, 284       ],  // dummy; 23/227: periodic trigger spell (with value); 42/231: proc trigger spell (with value); 48: unk; 109: add target trigger; 226: periodic dummy; 236: control vehicle; 284: linked
        'teach'      => [                                                   ]
    );

    private         $spellVars   = [];
    private         $refSpells   = [];
    private         $tools       = [];
    private         $interactive = false;
    private         $charLevel   = MAX_LEVEL;

    protected       $queryBase   = 'SELECT s.*, s.id AS ARRAY_KEY FROM ?_spell s';
    protected       $queryOpts   = array(
                        's'   => [['src', 'sr', 'ic', 'ica']],  //  6: TYPE_SPELL
                        'ic'  => ['j' => ['?_icons ic  ON ic.id  = s.iconId',    true], 's' => ', ic.name AS iconString'],
                        'ica' => ['j' => ['?_icons ica ON ica.id = s.iconIdAlt', true], 's' => ', ica.name AS iconStringAlt'],
                        'sr'  => ['j' => ['?_spellrange sr ON sr.id = s.rangeId'], 's' => ', sr.rangeMinHostile, sr.rangeMinFriend, sr.rangeMaxHostile, sr.rangeMaxFriend, sr.name_loc0 AS rangeText_loc0, sr.name_loc2 AS rangeText_loc2, sr.name_loc3 AS rangeText_loc3, sr.name_loc4 AS rangeText_loc4, sr.name_loc6 AS rangeText_loc6, sr.name_loc8 AS rangeText_loc8'],
                        'src' => ['j' => ['?_source src ON type = 6 AND typeId = s.id', true], 's' => ', src1, src2, src3, src4, src5, src6, src7, src8, src9, src10, src11, src12, src13, src14, src15, src16, src17, src18, src19, src20, src21, src22, src23, src24']
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        if ($this->error)
            return;

        // post processing
        $foo = DB::World()->selectCol('SELECT perfectItemType FROM skill_perfect_item_template WHERE spellId IN (?a)', $this->getFoundIDs());
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
                foreach (Game::$skillLineMask[$_curTpl['skillLine1']] as $idx => $pair)
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

            if (!$_curTpl['iconString'])
                $_curTpl['iconString'] = 'inv_misc_questionmark';
        }

        if ($foo)
            $this->relItems = new ItemList(array(['i.id', array_unique($foo)], CFG_SQL_LIMIT_NONE));
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_spell WHERE id = ?d', $id );
        return Util::localizedString($n, 'name');
    }
    // end static use

    // required for item-comparison
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
                    if ($mv && ($json = DB::Aowow()->selectRow('SELECT * FROM ?_item_stats WHERE `type` = ?d AND `typeId` = ?d', TYPE_ENCHANTMENT, $mv)))
                    {
                        $mods = [];
                        foreach ($json as $str => $val)
                            if ($val && ($idx = array_search($str, Game::$itemMods)))
                                $mods[$idx] = $val;

                        if ($mods)
                            Util::arraySumByKey($stats, $mods);
                    }

                    continue;
                }

                switch ($au)
                {
                    case 29:                                // ModStat MiscVal:type
                        if ($mv < 0)                        // all stats
                        {
                            for ($iMod = ITEM_MOD_AGILITY; $iMod <= ITEM_MOD_STAMINA; $iMod++)
                                Util::arraySumByKey($stats, [$iMod => $pts]);
                        }
                        else if ($mv == STAT_STRENGTH)      // one stat
                            Util::arraySumByKey($stats, [ITEM_MOD_STRENGTH => $pts]);
                        else if ($mv == STAT_AGILITY)
                            Util::arraySumByKey($stats, [ITEM_MOD_AGILITY => $pts]);
                        else if ($mv == STAT_STAMINA)
                            Util::arraySumByKey($stats, [ITEM_MOD_STAMINA => $pts]);
                        else if ($mv == STAT_INTELLECT)
                            Util::arraySumByKey($stats, [ITEM_MOD_INTELLECT => $pts]);
                        else if ($mv == STAT_SPIRIT)
                            Util::arraySumByKey($stats, [ITEM_MOD_SPIRIT => $pts]);
                        else                                // one bullshit
                            trigger_error('AuraId 29 of spell #'.$this->id.' has wrong statId #'.$mv, E_USER_WARNING);

                        break;
                    case 34:                                // Increase Health
                    case 230:
                    case 250:
                        Util::arraySumByKey($stats, [ITEM_MOD_HEALTH => $pts]);
                        break;
                    case 13:                                // damage splpwr + physical (dmg & any)
                        // + weapon damage
                        if ($mv == (1 << SPELL_SCHOOL_NORMAL))
                        {
                            Util::arraySumByKey($stats, [ITEM_MOD_WEAPON_DMG => $pts]);
                            break;
                        }

                        // full magic mask, also counts towards healing
                        if ($mv == SPELL_MAGIC_SCHOOLS)
                        {
                            Util::arraySumByKey($stats, [ITEM_MOD_SPELL_POWER       => $pts]);
                            Util::arraySumByKey($stats, [ITEM_MOD_SPELL_DAMAGE_DONE => $pts]);
                        }
                        else
                        {
                            // HolySpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_HOLY))
                                Util::arraySumByKey($stats, [ITEM_MOD_HOLY_POWER   => $pts]);

                            // FireSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_FIRE))
                                Util::arraySumByKey($stats, [ITEM_MOD_FIRE_POWER   => $pts]);

                            // NatureSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_NATURE))
                                Util::arraySumByKey($stats, [ITEM_MOD_NATURE_POWER => $pts]);

                            // FrostSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_FROST))
                                Util::arraySumByKey($stats, [ITEM_MOD_FROST_POWER  => $pts]);

                            // ShadowSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_SHADOW))
                                Util::arraySumByKey($stats, [ITEM_MOD_SHADOW_POWER => $pts]);

                            // ArcaneSpellpower (deprecated; still used in randomproperties)
                            if ($mv & (1 << SPELL_SCHOOL_ARCANE))
                                Util::arraySumByKey($stats, [ITEM_MOD_ARCANE_POWER => $pts]);
                        }

                        break;
                    case 135:                               // healing splpwr (healing & any) .. not as a mask..
                        Util::arraySumByKey($stats, [ITEM_MOD_SPELL_HEALING_DONE => $pts]);
                        break;
                    case 35:                                // ModPower - MiscVal:type see defined Powers only energy/mana in use
                        if ($mv == POWER_HEALTH)
                            Util::arraySumByKey($stats, [ITEM_MOD_HEALTH      => $pts]);
                        else if ($mv == POWER_ENERGY)
                            Util::arraySumByKey($stats, [ITEM_MOD_ENERGY      => $pts]);
                        else if ($mv == POWER_RAGE)
                            Util::arraySumByKey($stats, [ITEM_MOD_RAGE        => $pts]);
                        else if ($mv == POWER_MANA)
                            Util::arraySumByKey($stats, [ITEM_MOD_MANA        => $pts]);
                        else if ($mv == POWER_RUNIC_POWER)
                            Util::arraySumByKey($stats, [ITEM_MOD_RUNIC_POWER => $pts]);

                        break;
                    case 189:                               // CombatRating MiscVal:ratingMask
                    case 220:
                        if ($mod = Game::itemModByRatingMask($mv))
                            Util::arraySumByKey($stats, [$mod => $pts]);
                        break;
                    case 143:                               // Resistance MiscVal:school
                    case 83:
                    case 22:
                        if ($mv == 1)                       // Armor only if explicitly specified
                        {
                            Util::arraySumByKey($stats, [ITEM_MOD_ARMOR => $pts]);
                            break;
                        }

                        if ($mv == 2)                       // holy-resistance ONLY if explicitly specified (shouldn't even exist...)
                        {
                            Util::arraySumByKey($stats, [ITEM_MOD_HOLY_RESISTANCE => $pts]);
                            break;
                        }

                        for ($j = 0; $j < 7; $j++)
                        {
                            if (($mv & (1 << $j)) == 0)
                                continue;

                            switch ($j)
                            {
                                case 2:
                                    Util::arraySumByKey($stats, [ITEM_MOD_FIRE_RESISTANCE   => $pts]);
                                    break;
                                case 3:
                                    Util::arraySumByKey($stats, [ITEM_MOD_NATURE_RESISTANCE => $pts]);
                                    break;
                                case 4:
                                    Util::arraySumByKey($stats, [ITEM_MOD_FROST_RESISTANCE  => $pts]);
                                    break;
                                case 5:
                                    Util::arraySumByKey($stats, [ITEM_MOD_SHADOW_RESISTANCE => $pts]);
                                    break;
                                case 6:
                                    Util::arraySumByKey($stats, [ITEM_MOD_ARCANE_RESISTANCE => $pts]);
                                    break;
                            }
                        }
                        break;
                    case 8:                                 // hp5
                    case 84:
                    case 161:
                        Util::arraySumByKey($stats, [ITEM_MOD_HEALTH_REGEN        => $pts]);
                        break;
                    case 85:                                // mp5
                        Util::arraySumByKey($stats, [ITEM_MOD_MANA_REGENERATION   => $pts]);
                        break;
                    case 99:                                // atkpwr
                        Util::arraySumByKey($stats, [ITEM_MOD_ATTACK_POWER        => $pts]);
                        break;                              // ?carries over to rngatkpwr?
                    case 124:                               // rngatkpwr
                        Util::arraySumByKey($stats, [ITEM_MOD_RANGED_ATTACK_POWER => $pts]);
                        break;
                    case 158:                               // blockvalue
                        Util::arraySumByKey($stats, [ITEM_MOD_BLOCK_VALUE         => $pts]);
                        break;
                    case 240:                               // ModExpertise
                        Util::arraySumByKey($stats, [ITEM_MOD_EXPERTISE_RATING    => $pts]);
                        break;
                    case 123:                               // Mod Target Resistance
                        if ($mv == 0x7C && $pts < 0)
                            Util::arraySumByKey($stats, [ITEM_MOD_SPELL_PENETRATION => -$pts]);
                        break;
                }
            }

            $data[$this->id] = $stats;
        }

        return $data;
    }

    public function getProfilerMods()
    {
        // weapon hand check: param: slot, class, subclass, value
        $whCheck = '$function() { var j, w = _inventory.getInventory()[%d]; if (!w[0] || !g_items[w[0]]) { return 0; } j = g_items[w[0]].jsonequip; return (j.classs == %d && (%d & (1 << (j.subclass)))) ? %d : 0; }';

        $data = $this->getStatGain();                       // flat gains
        foreach ($data as $id => &$spellData)
        {
            foreach ($spellData as $modId => $val)
            {
                if (!isset(Game::$itemMods[$modId]))
                    continue;

                if ($modId == ITEM_MOD_EXPERTISE_RATING)    // not a rating .. pure expertise
                    $spellData['exp'] = $val;
                else
                    $spellData[Game::$itemMods[$modId]] = $val;

                unset($spellData[$modId]);
            }

            // apply weapon restrictions
            $this->getEntry($id);
            $class    = $this->getField('equippedItemClass');
            $subClass = $this->getField('equippedItemSubClassMask');
            $slot     = $subClass & 0x5000C ? 18 : 16;
            if ($class != ITEM_CLASS_WEAPON || !$subClass)
                continue;

            foreach ($spellData as $json => $pts)
                $spellData[$json] = [1, 'functionOf', sprintf($whCheck, $slot, $class, $subClass, $pts)];
        }

        // 4 possible modifiers found
        // <statistic> => [0.15, 'functionOf', <funcName:int>]
        // <statistic> => [0.33, 'percentOf', <statistic>]
        // <statistic> => [123, 'add']
        // <statistic> => <value>  ...  as from getStatGain()

        $modXByStat = function (&$arr, $stat, $pts) use (&$mv)
        {
            if ($mv == STAT_STRENGTH)
                $arr[$stat ?: 'str'] = [$pts / 100, 'percentOf', 'str'];
            else if ($mv == STAT_AGILITY)
                $arr[$stat ?: 'agi'] = [$pts / 100, 'percentOf', 'agi'];
            else if ($mv == STAT_STAMINA)
                $arr[$stat ?: 'sta'] = [$pts / 100, 'percentOf', 'sta'];
            else if ($mv == STAT_INTELLECT)
                $arr[$stat ?: 'int'] = [$pts / 100, 'percentOf', 'int'];
            else if ($mv == STAT_SPIRIT)
                $arr[$stat ?: 'spi'] = [$pts / 100, 'percentOf', 'spi'];
        };

        $modXBySchool = function (&$arr, $stat, $val, $mask = null) use (&$mv)
        {
            if (($mask ?: $mv) & (1 << SPELL_SCHOOL_HOLY))
                $arr['hol'.$stat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'hol'.$stat];
            if (($mask ?: $mv) & (1 << SPELL_SCHOOL_FIRE))
                $arr['fir'.$stat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'fir'.$stat];
            if (($mask ?: $mv) & (1 << SPELL_SCHOOL_NATURE))
                $arr['nat'.$stat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'nat'.$stat];
            if (($mask ?: $mv) & (1 << SPELL_SCHOOL_FROST))
                $arr['fro'.$stat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'fro'.$stat];
            if (($mask ?: $mv) & (1 << SPELL_SCHOOL_SHADOW))
                $arr['sha'.$stat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'sha'.$stat];
            if (($mask ?: $mv) & (1 << SPELL_SCHOOL_ARCANE))
                $arr['arc'.$stat] = is_array($val) ? $val : [$val / 100, 'percentOf', 'arc'.$stat];
        };

        $jsonStat = function ($stat)
        {
            if ($stat == STAT_STRENGTH)
                return 'str';
            if ($stat == STAT_AGILITY)
                return 'agi';
            if ($stat == STAT_STAMINA)
                return 'sta';
            if ($stat == STAT_INTELLECT)
                return 'int';
            if ($stat == STAT_SPIRIT)
                return 'spi';
        };

        foreach ($this->iterate() as $id => $__)
        {
            // Priest: Spirit of Redemption is a spell but also a passive. *yaaayyyy*
            if (($this->getField('cuFlags') & SPELL_CU_TALENTSPELL) && $id != 20711)
                continue;

            // curious cases of OH MY FUCKING GOD WHY?!
            if ($id == 16268)                               // Shaman - Spirit Weapons (parry is normaly stored in g_statistics)
            {
                $data[$id]['parrypct'] = [5, 'add'];
                continue;
            }

            if ($id == 20550)                               // Tauren - Endurance (dependant on base health) ... if you are looking for something elegant, look away!
            {
                $data[$id]['health'] = [0.05, 'functionOf', '$function(p) { return g_statistics.combo[p.classs][p.level][5]; }'];
                continue;
            }

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
                    case 101:                               // Mod Resistance Percent
                    case 142:                               // Mod Base Resistance Percent
                        if ($mv == 1)                       // Armor only if explicitly specified only affects armor from equippment
                            $data[$id]['armor'] = [$pts / 100, 'percentOf', ['armor', 0]];
                        else if ($mv)
                            $modXBySchool($data[$id], 'res', $pts);
                        break;
                    case 182:                               // Mod Resistance Of Stat Percent
                        if ($mv == 1)                       // Armor only if explicitly specified
                            $data[$id]['armor'] = [$pts / 100, 'percentOf', $jsonStat($mvB)];
                        else if ($mv)
                            $modXBySchool($data[$id], 'res', [$pts / 100, 'percentOf', $jsonStat($mvB)]);
                        break;
                    case 137:                               // mod stat percent
                        if ($mv > -1)                       // one stat
                            $modXByStat($data[$id], null, $pts);
                        else if ($mv < 0)                   // all stats
                            for ($iMod = ITEM_MOD_AGILITY; $iMod <= ITEM_MOD_STAMINA; $iMod++)
                                $data[$id][Game::$itemMods[$iMod]] = [$pts / 100, 'percentOf', Game::$itemMods[$iMod]];
                        break;
                    case 174:                               // Mod Spell Damage Of Stat Percent
                        $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                        $modXBySchool($data[$id], 'spldmg', [$pts / 100, 'percentOf', $jsonStat($mvB)]);
                        break;
                    case 212:                               // Mod Ranged Attack Power Of Stat Percent
                        $modXByStat($data[$id], 'rgdatkpwr', $pts);
                        break;
                    case 268:                               // Mod Attack Power Of Stat Percent
                        $modXByStat($data[$id], 'mleatkpwr', $pts);
                        break;
                    case 175:                               // Mod Spell Healing Of Stat Percent
                        $modXByStat($data[$id], 'splheal', $pts);
                        break;
                    case 219:                               // Mod Mana Regeneration from Stat
                        $modXByStat($data[$id], 'manargn', $pts);
                        break;
                    case 134:                               // Mod Mana Regeneration Interrupt
                        $data[$id]['icmanargn'] = [$pts / 100, 'percentOf', 'oocmanargn'];
                        break;
                    case 57:                                // Mod Spell Crit Chance
                    case 71:                                // Mod Spell Crit Chance School
                        $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                        $modXBySchool($data[$id], 'splcritstrkpct', [$pts, 'add']);
                        if (($mv & SPELL_MAGIC_SCHOOLS) == SPELL_MAGIC_SCHOOLS)
                            $data[$id]['splcritstrkpct'] = [$pts, 'add'];
                        break;
                    case 285:                               // Mod Attack Power Of Armor
                        $data[$id]['mleatkpwr'] = [1 / $pts, 'percentOf', 'fullarmor'];
                        $data[$id]['rgdatkpwr'] = [1 / $pts, 'percentOf', 'fullarmor'];
                        break;
                    case 52:                                // Mod Physical Crit Percent
                        if ($class < 1 || ($class == ITEM_CLASS_WEAPON && ($subClass & 0x5000C)))
                            $data[$id]['rgdcritstrkpct'] = [1, 'functionOf', sprintf($whCheck, 18, $class, $subClass, $pts)];
                            // $data[$id]['rgdcritstrkpct'] = [$pts, 'add'];
                        if ($class < 1 || ($class == ITEM_CLASS_WEAPON && ($subClass & 0xA5F3)))
                            $data[$id]['mlecritstrkpct'] = [1, 'functionOf', sprintf($whCheck, 16, $class, $subClass, $pts)];
                            // $data[$id]['mlecritstrkpct'] = [$pts, 'add'];
                        break;
                    case 47:                                // Mod Parry Percent
                        $data[$id]['parrypct'] = [$pts, 'add'];
                        break;
                    case 49:                                // Mod Dodge Percent
                        $data[$id]['dodgepct'] = [$pts, 'add'];
                        break;
                    case 51:                                // Mod Block Percent
                        $data[$id]['blockpct'] = [$pts, 'add'];
                        break;
                    case 132:                               // Mod Increase Energy Percent
                        if ($mv == POWER_HEALTH)
                            $data[$id]['health'] = [$pts / 100, 'percentOf', 'health'];
                        else if ($mv == POWER_ENERGY)
                            $data[$id]['energy'] = [$pts / 100, 'percentOf', 'energy'];
                        else if ($mv == POWER_MANA)
                            $data[$id]['mana'] = [$pts / 100, 'percentOf', 'mana'];
                        else if ($mv == POWER_RAGE)
                            $data[$id]['rage'] = [$pts / 100, 'percentOf', 'rage'];
                        else if ($mv == POWER_RUNIC_POWER)
                            $data[$id]['runic'] = [$pts / 100, 'percentOf', 'runic'];
                        break;
                    case 133:                               // Mod Increase Health Percent
                        $data[$id]['health'] = [$pts / 100, 'percentOf', 'health'];
                        break;
                    case 150:                               // Mod Shield Blockvalue Percent
                        $data[$id]['block'] = [$pts / 100, 'percentOf', 'block'];
                        break;
                    case 290:                               // Mod Crit Percent
                        $data[$id]['mlecritstrkpct'] = [$pts, 'add'];
                        $data[$id]['rgdcritstrkpct'] = [$pts, 'add'];
                        $data[$id]['splcritstrkpct'] = [$pts, 'add'];
                        break;
                    case 237:                               // Mod Spell Damage Of Attack Power
                        $mv = $mv ?: SPELL_MAGIC_SCHOOLS;
                        $modXBySchool($data[$id], 'spldmg', [$pts / 100, 'percentOf', 'mleatkpwr']);
                        break;
                    case 238:                               // Mod Spell Healing Of Attack Power
                        $data[$id]['splheal'] = [$pts / 100, 'percentOf', 'mleatkpwr'];
                        break;
                    case 166:                               // Mod Attack Power Percent [ingmae only melee..?]
                        $data[$id]['mleatkpwr'] = [$pts / 100, 'percentOf', 'mleatkpwr'];
                        break;
                    case 88:                                // Mod Health Regeneration Percent
                        $data[$id]['healthrgn'] = [$pts / 100, 'percentOf', 'healthrgn'];
                        break;
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

    public function getModelInfo($spellId = 0, $effIdx = 0)
    {
        $displays = [0 => []];
        foreach ($this->iterate() as $id => $__)
        {
            if ($spellId && $spellId != $id)
                continue;

            for ($i = 1; $i < 4; $i++)
            {
                $effMV = $this->curTpl['effect'.$i.'MiscValue'];
                if (!$effMV)
                    continue;

                // GO Model from MiscVal
                if (in_array($this->curTpl['effect'.$i.'Id'], [50, 76, 104, 105, 106, 107]))
                {
                    if (isset($displays[TYPE_OBJECT][$id]))
                        $displays[TYPE_OBJECT][$id][0][] = $i;
                    else
                        $displays[TYPE_OBJECT][$id] = [[$i], $effMV];
                }
                // NPC Model from MiscVal
                else if (in_array($this->curTpl['effect'.$i.'Id'], [28, 90, 56, 112, 134]) || in_array($this->curTpl['effect'.$i.'AuraId'], [56, 78]))
                {
                    if (isset($displays[TYPE_NPC][$id]))
                        $displays[TYPE_NPC][$id][0][] = $i;
                    else
                        $displays[TYPE_NPC][$id] = [[$i], $effMV];
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
                        foreach ([1, 2] as $j)
                            if (isset($subForms[$st['model'.$j]]))
                                $st['model'.$j] = $subForms[$st['model'.$j]][array_rand($subForms[$st['model'.$j]])];

                        $displays[0][$id][$i] = array(
                            'typeId'       => 0,
                            'displayId'    => $st['model2'] ? $st['model'.rand(1, 2)] : $st['model1'],
                            'creatureType' => $st['creatureType'],
                            'displayName'  => Lang::game('st', $effMV)
                        );
                    }
                }
            }
        }

        $results = $displays[0];

        if (!empty($displays[TYPE_NPC]))
        {
            $nModels = new CreatureList(array(['id', array_column($displays[TYPE_NPC], 1)]));
            foreach ($nModels->iterate() as $nId => $__)
            {
                foreach ($displays[TYPE_NPC] as $srcId => [$indizes, $npcId])
                {
                    if ($npcId == $nId)
                    {
                        foreach ($indizes as $idx)
                        {
                            $results[$srcId][$idx] = array(
                                'typeId'      => $nId,
                                'displayId'   => $nModels->getRandomModelId(),
                                'displayName' => $nModels->getField('name', true)
                            );
                        }
                    }
                }
            }
        }

        if (!empty($displays[TYPE_OBJECT]))
        {
            $oModels = new GameObjectList(array(['id', array_column($displays[TYPE_OBJECT], 1)]));
            foreach ($oModels->iterate() as $oId => $__)
            {
                foreach ($displays[TYPE_OBJECT] as $srcId => [$indizes, $objId])
                {
                    if ($objId == $oId)
                    {
                        foreach ($indizes as $idx)
                        {
                            $results[$srcId][$idx] = array(
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
            return !empty($results[$spellId][$effIdx]) ? $results[$spellId][$effIdx] : 0;

        return $results;
    }

    private function createRangesForCurrent()
    {
        if (!$this->curTpl['rangeMaxHostile'])
            return '';

        // minRange exists; show as range
        if ($this->curTpl['rangeMinHostile'])
            return sprintf(Lang::spell('range'), $this->curTpl['rangeMinHostile'].' - '.$this->curTpl['rangeMaxHostile']);
        // friend and hostile differ; do color
        else if ($this->curTpl['rangeMaxHostile'] != $this->curTpl['rangeMaxFriend'])
            return sprintf(Lang::spell('range'), '<span class="q10">'.$this->curTpl['rangeMaxHostile'].'</span> - <span class="q2">'.$this->curTpl['rangeMaxFriend']. '</span>');
        // hardcode: "melee range"
        else if ($this->curTpl['rangeMaxHostile'] == 5)
            return Lang::spell('meleeRange');
        // hardcode "unlimited range"
        else if ($this->curTpl['rangeMaxHostile'] == 50000)
            return Lang::spell('unlimRange');
        // regular case
        else
            return sprintf(Lang::spell('range'), $this->curTpl['rangeMaxHostile']);
    }

    public function createPowerCostForCurrent()
    {
        $str = '';

        // check for custom PowerDisplay
        $pt = $this->curTpl['powerType'];

        if ($pt == POWER_RUNE && ($rCost = ($this->curTpl['powerCostRunes'] & 0x333)))
        {   // Frost 2|1 - Unholy 2|1 - Blood 2|1
            $runes = [];
            if ($_ = (($rCost & 0x300) >> 8))
                $runes[] = $_.' '.Lang::spell('powerRunes', 2);
            if ($_ = (($rCost & 0x030) >> 4))
                $runes[] = $_.' '.Lang::spell('powerRunes', 1);
            if ($_ =  ($rCost & 0x003))
                $runes[] = $_.' '.Lang::spell('powerRunes', 0);

            $str .= implode(', ', $runes);
        }
        else if ($this->curTpl['powerCostPercent'] > 0)     // power cost: pct over static
            $str .= $this->curTpl['powerCostPercent']."% ".sprintf(Lang::spell('pctCostOf'), mb_strtolower(Lang::spell('powerTypes', $pt)));
        else if ($this->curTpl['powerCost'] > 0 || $this->curTpl['powerPerSecond'] > 0 || $this->curTpl['powerCostPerLevel'] > 0)
            $str .= ($pt == POWER_RAGE || $pt == POWER_RUNIC_POWER ? $this->curTpl['powerCost'] / 10 : $this->curTpl['powerCost']).' '.Util::ucFirst(Lang::spell('powerTypes', $pt));

        // append periodic cost
        if ($this->curTpl['powerPerSecond'] > 0)
            $str .= sprintf(Lang::spell('costPerSec'), $this->curTpl['powerPerSecond']);

        // append level cost (todo (low): work in as scaling cost)
        if ($this->curTpl['powerCostPerLevel'] > 0)
            $str .= sprintf(Lang::spell('costPerLevel'), $this->curTpl['powerCostPerLevel']);

        return $str;
    }

    public function createCastTimeForCurrent($short = true, $noInstant = true)
    {
        if ($this->isChanneledSpell())
            return Lang::spell('channeled');
        else if ($this->curTpl['castTime'] > 0)
            return $short ? sprintf(Lang::spell('castIn'), $this->curTpl['castTime']) : Util::formatTime($this->curTpl['castTime'] * 1000);
        // show instant only for player/pet/npc abilities (todo (low): unsure when really hidden (like talent-case))
        else if ($noInstant && !in_array($this->curTpl['typeCat'], [11, 7, -3, -6, -8, 0]) && !($this->curTpl['cuFlags'] & SPELL_CU_TALENTSPELL))
            return '';
        // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only (todo (low): rule is imperfect)
        else if ($this->curTpl['damageClass'] != 1 || $this->curTpl['attributes0'] & SPELL_ATTR0_ABILITY)
            return Lang::spell('instantPhys');
        else                                                // instant cast
            return Lang::spell('instantMagic');
    }

    private function createCooldownForCurrent()
    {
        if ($this->curTpl['recoveryTime'])
            return sprintf(Lang::game('cooldown'), Util::formatTime($this->curTpl['recoveryTime'], true));
        else if ($this->curTpl['recoveryCategory'])
            return sprintf(Lang::game('cooldown'), Util::formatTime($this->curTpl['recoveryCategory'], true));
        else
            return '';
    }

    // formulae base from TC
    private function calculateAmountForCurrent($effIdx, $altTpl = null)
    {
        $ref     = $altTpl ?: $this;
        $level   = $this->charLevel;
        $rppl    = $ref->getField('effect'.$effIdx.'RealPointsPerLevel');
        $base    = $ref->getField('effect'.$effIdx.'BasePoints');
        $add     = $ref->getField('effect'.$effIdx.'DieSides');
        $maxLvl  = $ref->getField('maxLevel');
        $baseLvl = $ref->getField('baseLevel');

        /* when should level scaling be actively worked into tooltips?
        if ($rppl)
        {
            if ($level > $maxLvl && $maxLvl > 0)
                $level = $maxLvl;
            else if ($level < $baseLvl)
                $level = $baseLvl;

            if (!$ref->getField('atributes0') & SPELL_ATTR0_PASSIVE)
                $level -= $ref->getField('spellLevel');

            $base  += (int)($level * $rppl);
        }
        */

        return [
            $add ? $base + 1 : $base,
            $base + $add,
            $rppl ? '<!--ppl'.$baseLvl.':'.$maxLvl.':'.($base + max(1, $add)).':'.$rppl.'-->' : null,
            $rppl ? '<!--ppl'.$baseLvl.':'.$maxLvl.':'.($base + $add).':'.$rppl.'-->' : null
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
                if ($this->curTpl['effect'.$i.'TriggerSpell'] > 0 || ($this->curTpl['effect'.$i.'Id'] == 155 && $this->curTpl['effect'.$i.'MiscValue'] > 0))
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
        return $this->curTpl['attributes1'] & (SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2);
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

        $cond  = $COND  = function($a, $b, $c) { return $a ? $b : $c; };
        $eq    = $EQ    = function($a, $b)     { return $a == $b;     };
        $gt    = $GT    = function($a, $b)     { return $a > $b;      };
        $gte   = $GTE   = function($a, $b)     { return $a >= $b;     };
        $floor = $FLOOR = function($a)         { return floor($a);    };
        $max   = $MAX   = function($a, $b)     { return max($a, $b);  };
        $min   = $MIN   = function($a, $b)     { return min($a, $b);  };

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

                // note the " !
                return eval('return "'.$formula.'";');
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
        try {
            $res = eval('return '.$formula.';');
        } catch (Throwable $t) {
            echo "- WARNING - problem during parsing the spell formula";
            $res = 0;
        }

        return $res;
    }

    // description-, buff-parsing component
    // a variables structure is pretty .. flexile. match in steps
    private function matchVariableString($varString, &$len = 0)
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
    // returns [min, max, minFulltext, maxFulltext, ratingId]
    private function resolveVariableString($varParts, &$usesScalingRating)
    {
        $signs  = ['+', '-', '/', '*', '%', '^'];

        foreach ($varParts as $k => $v)
            $$k = $v;

        $result = [null];

        if (!$var)
            return $result;

        if (!$effIdx)                                       // if EffectIdx is omitted, assume EffectIdx: 1
            $effIdx = 1;

        // cache at least some lookups.. should be moved to single spellList :/
        if ($lookup && !isset($this->refSpells[$lookup]))
            $this->refSpells[$lookup] = new SpellList(array(['s.id', $lookup]));

        $srcSpell = $lookup ? $this->refSpells[$lookup] : $this;
        if ($srcSpell->error)
            return $result;

        switch ($var)
        {
            case 'a':                                       // EffectRadiusMin
            case 'A':                                       // EffectRadiusMax
                $base = $srcSpell->getField('effect'.$effIdx.'RadiusMax');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'b':                                       // PointsPerComboPoint
            case 'B':
                $base = $srcSpell->getField('effect'.$effIdx.'PointsPerComboPoint');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'd':                                       // SpellDuration
            case 'D':                                       // todo (med): min/max?; /w unit?
                $base = $srcSpell->getField('duration');

                if ($base <= 0)
                    $result[2] = Lang::spell('untilCanceled');
                else
                    $result[2] = Util::formatTime($base, true);

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base < 0 ? 0 : $base;
                break;
            case 'e':                                       // EffectValueMultiplier
            case 'E':
                $base = $srcSpell->getField('effect'.$effIdx.'ValueMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'f':                                       // EffectDamageMultiplier
            case 'F':
                $base = $srcSpell->getField('effect'.$effIdx.'DamageMultiplier');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'g':                                       // boolean choice with casters gender as condition $gX:Y;
            case 'G':
                $result[2] = '&lt;'.$switch[0].'/'.$switch[1].'&gt;';
                break;
            case 'h':                                       // ProcChance
            case 'H':
                $base = $srcSpell->getField('procChance');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'i':                                       // MaxAffectedTargets
            case 'I':
                $base = $srcSpell->getField('maxAffectedTargets');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'l':                                       // boolean choice with last value as condition $lX:Y;
            case 'L':
                // resolve later by backtracking
                $result[2] = '$l'.$switch[0].':'.$switch[1].';';
                break;
            case 'm':                                       // BasePoints (minValue)
            case 'M':                                       // BasePoints (maxValue)
                $base = $srcSpell->getField('effect'.$effIdx.'BasePoints');
                $add  = $srcSpell->getField('effect'.$effIdx.'DieSides');
                $mv   = $srcSpell->getField('effect'.$effIdx.'MiscValue');
                $aura = $srcSpell->getField('effect'.$effIdx.'AuraId');

                if (ctype_lower($var))
                    $add = 1;

                $base += $add;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                // Aura giving combat ratings
                $rType = 0;
                if ($aura == 189)
                    if ($rType = Game::itemModByRatingMask($mv))
                        $usesScalingRating = true;
                // Aura end

                if ($rType)
                {
                    $result[2] = '<!--rtg%s-->%s&nbsp;<small>(%s)</small>';
                    $result[4] = $rType;
                }

                $result[0] = $base;
                break;
            case 'n':                                       // ProcCharges
            case 'N':
                $base = $srcSpell->getField('procCharges');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'o':                                       // TotalAmount for periodic auras (with variance)
            case 'O':
                [$min, $max, $modStrMin, $modStrMax] = $this->calculateAmountForCurrent($effIdx, $srcSpell);
                $periode  = $srcSpell->getField('effect'.$effIdx.'Periode');
                $duration = $srcSpell->getField('duration');

                if (!$periode)
                {
                    // Mod Power Regeneration & Mod Health Regeneration have an implicit periode of 5sec
                    $aura = $srcSpell->getField('effect'.$effIdx.'AuraId');
                    if ($aura == 84 || $aura == 85)
                        $periode = 5000;
                    else
                        $periode = 3000;
                }

                $min  *= $duration / $periode;
                $max  *= $duration / $periode;

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    eval("\$max = $max $op $oparg;");
                }

                if ($this->interactive)
                {
                    $result[2] = $modStrMin.'%s';
                    $result[3] = $modStrMax.'%s';
                }

                $result[0] = $min;
                $result[1] = $max;
                break;
            case 'q':                                       // EffectMiscValue
            case 'Q':
                $base = $srcSpell->getField('effect'.$effIdx.'MiscValue');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'r':                                       // SpellRange
            case 'R':
                $base = $srcSpell->getField('rangeMaxHostile');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 's':                                       // BasePoints (with variance)
            case 'S':
                [$min, $max, $modStrMin, $modStrMax] = $this->calculateAmountForCurrent($effIdx, $srcSpell);
                $mv   = $srcSpell->getField('effect'.$effIdx.'MiscValue');
                $aura = $srcSpell->getField('effect'.$effIdx.'AuraId');

                if (in_array($op, $signs) && is_numeric($oparg))
                {
                    eval("\$min = $min $op $oparg;");
                    eval("\$max = $max $op $oparg;");
                }
                // Aura giving combat ratings
                $rType = 0;
                if ($aura == 189)
                    if ($rType = Game::itemModByRatingMask($mv))
                        $usesScalingRating = true;
                // Aura end

                if ($rType)
                {
                    $result[2] = '<!--rtg%s-->%s&nbsp;<small>(%s)</small>';
                    $result[4] = $rType;
                }
                else if ($aura == 189 && $this->interactive)
                {
                    $result[2] = $modStrMin.'%s';
                    $result[3] = $modStrMax.'%s';
                }

                $result[0] = $min;
                $result[1] = $max;
                break;
            case 't':                                       // Periode
            case 'T':
                $base = $srcSpell->getField('effect'.$effIdx.'Periode') / 1000;

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'u':                                       // StackCount
            case 'U':
                $base = $srcSpell->getField('stackAmount');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'v':                                   // MaxTargetLevel
            case 'V':
                $base = $srcSpell->getField('MaxTargetLevel');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'x':                                   // ChainTargetCount
            case 'X':
                $base = $srcSpell->getField('effect'.$effIdx.'ChainTarget');

                if (in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    eval("\$base = $base $op $oparg;");

                $result[0] = $base;
                break;
            case 'z':                                   // HomeZone
                $result[2] = Lang::spell('home');
                break;
        }

        // handle excessively precise floats
        if (is_float($result[0]))
            $result[0] = round($result[0], 2);
        if (isset($result[1]) && is_float($result[1]))
            $result[1] = round($result[1], 2);

        return $result;
    }

    // description-, buff-parsing component
    private function resolveFormulaString($formula, $precision = 0, &$scaling)
    {
        $fSuffix = '%s';
        $fRating = 0;

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

            [$formOutStr, $fSuffix, $fRating] = $this->resolveFormulaString($formOutStr, $formPrecision, $scaling);

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
            $var = $this->resolveVariableString($varParts, $scaling);

            // time within formula -> rebase to seconds and omit timeUnit
            if (strtolower($varParts['var']) == 'd')
            {
               $var[0] /= 1000;
               unset($var[2]);
            }

            $str .= $var[0];

            // overwrite eventually inherited strings
            if (isset($var[2]))
                $fSuffix = $var[2];

            // overwrite eventually inherited ratings
            if (isset($var[4]))
                $fRating = $var[4];
        }
        $str .= substr($formula, $pos);
        $str  = str_replace('#', '$', $str);                // reset marks

        // step 3: try to evaluate result
        $evaled = $this->resolveEvaluation($str);

        $return = is_numeric($evaled) ? Lang::nf($evaled, $precision, true) : $evaled;

        return [$return, $fSuffix, $fRating];
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

        $this->interactive = $interactive;
        $this->charLevel   = $level;

    // step 0: get text
        $data = $this->getField($type, true);
        if (empty($data) || $data == "[]")                  // empty tooltip shouldn't be displayed anyway
            return ['', []];

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

        /* sequences
           a) simple    - $?cond[A][B]                      // simple case of b)
           b) elseif    - $?cond[A]?cond[B]..[C]            // can probably be repeated as often as you wanted
           c) recursive - $?cond[A][$?cond[B][..]]          // can probably be stacked as deep as you wanted

           only case a) can be used for KNOW-parameter
       */

        $relSpells = [];
        $data = $this->handleConditions($data, $scaling, $relSpells, true);

    // step 3: unpack formulas ${ .. }.X
        $data = $this->handleFormulas($data, $scaling, true);

    // step 4: find and eliminate regular variables
        $data = $this->handleVariables($data, $scaling, true);

    // step 5: variable-dependant variable-text
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

        return [$data, $relSpells];
    }

    private function handleFormulas($data, &$scaling, $topLevel = false)
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
            [$formOutVal, $formOutStr, $ratingId] = $this->resolveFormulaString($formOutStr, $formPrecision ?: ($topLevel ? 0 : 10), $scaling);

            if ($ratingId && Util::checkNumeric($formOutVal) && $this->interactive)
                $resolved = sprintf($formOutStr, $ratingId, abs($formOutVal), sprintf(Util::$setRatingLevelString, $this->charLevel, $ratingId, abs($formOutVal), Util::setRatingLevel($this->charLevel, $ratingId, abs($formOutVal))));
            else if ($ratingId && Util::checkNumeric($formOutVal))
                $resolved = sprintf($formOutStr, $ratingId, abs($formOutVal), Util::setRatingLevel($this->charLevel, $ratingId, abs($formOutVal)));
            else
                $resolved = sprintf($formOutStr, Util::checkNumeric($formOutVal) ? abs($formOutVal) : $formOutVal);

            $data = substr_replace($data, $resolved, $formStartPos, ($formCurPos - $formStartPos));
        }

        return $data;
    }

    private function handleVariables($data, &$scaling, $topLevel = false)
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

            $var = $this->resolveVariableString($varParts, $scaling);
            $resolved = is_numeric($var[0]) ? abs($var[0]) : $var[0];
            if (isset($var[2]))
            {
                if (isset($var[4]) && $this->interactive)
                    $resolved = sprintf($var[2], $var[4], abs($var[0]), sprintf(Util::$setRatingLevelString, $this->charLevel, $var[4], abs($var[0]), Util::setRatingLevel($this->charLevel, $var[4], abs($var[0]))));
                else if (isset($var[4]))
                    $resolved = sprintf($var[2], $var[4], abs($var[0]), Util::setRatingLevel($this->charLevel, $var[4], abs($var[0])));
                else
                    $resolved = sprintf($var[2], $resolved);
            }

            if (isset($var[1]) && $var[0] != $var[1] && !isset($var[4]))
            {
                $_ = is_numeric($var[1]) ? abs($var[1]) : $var[1];
                $resolved .= Lang::game('valueDelim');
                $resolved .= isset($var[3]) ? sprintf($var[3], $_) : $_;
            }

            $str .= $resolved;
        }
        $str .= substr($data, $pos);
        $str = str_replace('#', '$', $str);                 // reset marker

        return $str;
    }

    private function handleConditions($data, &$scaling, &$relSpells, $topLevel = false)
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
                $condParts[$targetPart] = $this->handleConditions($condParts[$targetPart], $scaling, $relSpells);

            if ($know && $topLevel)
            {
                foreach ([1, 3] as $pos)
                {
                    if (strstr($condParts[$pos], '${'))
                        $condParts[$pos] = $this->handleFormulas($condParts[$pos], $scaling);

                    if (strstr($condParts[$pos], '$'))
                        $condParts[$pos] = $this->handleVariables($condParts[$pos], $scaling);
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

    public function renderBuff($level = MAX_LEVEL, $interactive = false)
    {
        if (!$this->curTpl)
            return ['', []];

        // doesn't have a buff
        if (!$this->getField('buff', true))
            return ['', []];

        $this->interactive = $interactive;

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
        $btt = $this->parseText('buff', $level, $this->interactive, $scaling);
        $x .= $btt[0].'<br>';

        // duration
        if ($this->curTpl['duration'] > 0)
            $x .= '<span class="q">'.sprintf(Lang::spell('remaining'), Util::formatTime($this->curTpl['duration'])).'<span>';

        $x .= '</td></tr></table>';

        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':1:'.($scaling ? MAX_LEVEL : 1).':'.$level.'-->';

        return [$x, $btt[1]];
    }

    public function renderTooltip($level = MAX_LEVEL, $interactive = false)
    {
        if (!$this->curTpl)
            return ['', []];

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
            $xTmp[] = Lang::game('requires2').' '.$reqItems;

        if ($desc[0])
            $xTmp[] = '<span class="q">'.$desc[0].'</span>';

        if ($createItem)
            $xTmp[] = $createItem;

        if ($xTmp)
            $x .= '<table><tr><td>'.implode('<br />', $xTmp).'</td></tr></table>';

        // scaling information - spellId:min:max:curr
        $x .= '<!--?'.$this->id.':1:'.($scaling ? MAX_LEVEL : 1).':'.$level.'-->';

        return [$x, $desc[1]];
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
            if (!empty($this->sources[$this->id]))
            {
                $data[$this->id]['source'] = array_keys($this->sources[$this->id]);
                if (!empty($this->sources[$this->id][3]))
                    $data[$this->id]['sourcemore'] = [['p' => $this->sources[$this->id][3][0]]];
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

                // play sound effect
                for ($i = 1; $i < 4; $i++)
                    if ($this->getField('effect'.$i.'Id') == 131 || $this->getField('effect'.$i.'Id') == 132)
                        $data[TYPE_SOUND][$this->getField('effect'.$i.'MiscValue')] = $this->getField('effect'.$i.'MiscValue');
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
                $buff = $this->renderBuff(MAX_LEVEL, true);
                $tTip = $this->renderTooltip(MAX_LEVEL, true);

                foreach ($tTip[1] as $relId => $_)
                    if (empty($data[TYPE_SPELL][$relId]))
                        $data[TYPE_SPELL][$relId] = $relId;

                foreach ($buff[1] as $relId => $_)
                    if (empty($data[TYPE_SPELL][$relId]))
                        $data[TYPE_SPELL][$relId] = $relId;

                $extra[$id] = array(
                    'id'         => $id,
                    'tooltip'    => $tTip[0],
                    'buff'       => !empty($buff[0]) ? $buff[0] : null,
                    'spells'     => $tTip[1],
                    'buffspells' => !empty($buff[1]) ? $buff[1] : null
                );
            }
        }

        return $data;
    }

    // mostly similar to TC
    public function getCastingTimeForBonus($asDOT = false)
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
            $originalCastTime = $this->curTpl['castTime'] * 1000;
            if ($this->curTpl['attributes0'] & SPELL_ATTR0_REQ_AMMO)
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

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'n'    => $this->getField('name', true),
                't'    => TYPE_SPELL,
                'ti'   => $this->id,
                's'    => empty($this->curTpl['skillLines']) ? 0 : $this->curTpl['skillLines'][0],
                'c'    => $this->curTpl['typeCat'],
                'icon' => $this->curTpl['iconStringAlt'] ? $this->curTpl['iconStringAlt'] : $this->curTpl['iconString'],
            );
        }

        return $data;
    }
}


class SpellListFilter extends Filter
{
    const MAX_SPELL_EFFECT = 167;
    const MAX_SPELL_AURA   = 316;

    protected $enums = array(
        9 => array(                                         // sources index
            1  => true,                                     // Any
            2  => false,                                    // None
            3  =>  1,                                       // Crafted
            4  =>  2,                                       // Drop
            6  =>  4,                                       // Quest
            7  =>  5,                                       // Vendor
            8  =>  6,                                       // Trainer
            9  =>  7,                                       // Discovery
            10 =>  9                                        // Talent
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

    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
         1  => [FILTER_CR_CALLBACK,  'cbCost',                                                                                   ], // costAbs [op] [int]
         2  => [FILTER_CR_NUMERIC,   'powerCostPercent', NUM_CAST_INT                                                            ], // prcntbasemanarequired
         3  => [FILTER_CR_BOOLEAN,   'spellFocusObject'                                                                          ], // requiresnearbyobject
         4  => [FILTER_CR_NUMERIC,   'trainingcost',     NUM_CAST_INT                                                            ], // trainingcost
         5  => [FILTER_CR_BOOLEAN,   'reqSpellId'                                                                                ], // requiresprofspec
         8  => [FILTER_CR_FLAG,      'cuFlags',          CUSTOM_HAS_SCREENSHOT                                                   ], // hasscreenshots
         9  => [FILTER_CR_CALLBACK,  'cbSource',                                                                                 ], // source [enum]
        10  => [FILTER_CR_FLAG,      'cuFlags',          SPELL_CU_FIRST_RANK                                                     ], // firstrank
        11  => [FILTER_CR_FLAG,      'cuFlags',          CUSTOM_HAS_COMMENT                                                      ], // hascomments
        12  => [FILTER_CR_FLAG,      'cuFlags',          SPELL_CU_LAST_RANK                                                      ], // lastrank
        13  => [FILTER_CR_NUMERIC,   'rankNo',           NUM_CAST_INT                                                            ], // rankno
        14  => [FILTER_CR_NUMERIC,   'id',               NUM_CAST_INT,                            true                           ], // id
        15  => [FILTER_CR_STRING,    'ic.name',                                                                                  ], // icon
        17  => [FILTER_CR_FLAG,      'cuFlags',          CUSTOM_HAS_VIDEO                                                        ], // hasvideos
        19  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION                                    ], // scaling
        20  => [FILTER_CR_CALLBACK,  'cbReagents',                                                                               ], // has Reagents [yn]
     // 22  => [FILTER_CR_NYI_PH,    null,               null,                                    null                           ], // proficiencytype [proficiencytype]  pointless
        25  => [FILTER_CR_BOOLEAN,   'skillLevelYellow'                                                                          ], // rewardsskillups
        27  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_CHANNELED_1,                 true                           ], // channeled [yn]
        28  => [FILTER_CR_NUMERIC,   'castTime',         NUM_CAST_FLOAT                                                          ], // casttime [num]
        29  => [FILTER_CR_CALLBACK,  'cbAuraNames',                                                                              ], // appliesaura [effectauranames]
     // 31  => [FILTER_CR_NYI_PH,    null,               null,                                    null                           ], // usablewhenshapeshifted [yn]  pointless
        33  => [FILTER_CR_CALLBACK,  'cbInverseFlag',    'attributes0',                           SPELL_ATTR0_CANT_USED_IN_COMBAT], // combatcastable [yn]
        34  => [FILTER_CR_CALLBACK,  'cbInverseFlag',    'attributes2',                           SPELL_ATTR2_CANT_CRIT          ], // chancetocrit [yn]
        35  => [FILTER_CR_CALLBACK,  'cbInverseFlag',    'attributes3',                           SPELL_ATTR3_IGNORE_HIT_RESULT  ], // chancetomiss [yn]
        36  => [FILTER_CR_FLAG,      'attributes3',      SPELL_ATTR3_DEATH_PERSISTENT                                            ], // persiststhroughdeath [yn]
        38  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_ONLY_STEALTHED                                              ], // requiresstealth [yn]
        39  => [FILTER_CR_CALLBACK,  'cbSpellstealable', 'attributes4',                           SPELL_ATTR4_NOT_STEALABLE      ], // spellstealable [yn]
        40  => [FILTER_CR_ENUM,      'damageClass'                                                                               ], // damagetype [damagetype]
        41  => [FILTER_CR_FLAG,      'stanceMask',       (1 << (22 - 1))                                                         ], // requiresmetamorphosis [yn]
        42  => [FILTER_CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_STUNNED                                        ], // usablewhenstunned [yn]
        44  => [FILTER_CR_CALLBACK,  'cbUsableInArena'                                                                           ], // usableinarenas [yn]
        45  => [FILTER_CR_ENUM,      'powerType'                                                                                 ], // resourcetype [resourcetype]
     // 46  => [FILTER_CR_NYI_PH,    null,               null,                                    null                           ], // disregardimmunity [yn]
        47  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE                                 ], // disregardschoolimmunity [yn]
        48  => [FILTER_CR_CALLBACK,  'cbEquippedWeapon', 0x0004000C,                              false                          ], // reqrangedweapon [yn]
        49  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_ON_NEXT_SWING                                               ], // onnextswingplayers [yn]
        50  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_PASSIVE                                                     ], // passivespell [yn]
        51  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_DONT_DISPLAY_IN_AURA_BAR                                    ], // hiddenaura [yn]
        52  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_ON_NEXT_SWING_2                                             ], // onnextswingnpcs [yn]
        53  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_DAYTIME_ONLY                                                ], // daytimeonly [yn]
        54  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_NIGHT_ONLY                                                  ], // nighttimeonly [yn]
        55  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_INDOORS_ONLY                                                ], // indoorsonly [yn]
        56  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_OUTDOORS_ONLY                                               ], // outdoorsonly [yn]
        57  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_CANT_CANCEL                                                 ], // uncancellableaura [yn]
        58  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION                                    ], // damagedependsonlevel [yn]
        59  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_STOP_ATTACK_TARGET                                          ], // stopsautoattack [yn]
        60  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK                                ], // cannotavoid [yn]
        61  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_DEAD                                         ], // usabledead [yn]
        62  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_MOUNTED                                      ], // usablemounted [yn]
        63  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_DISABLED_WHILE_ACTIVE                                       ], // delayedrecoverystarttime [yn]
        64  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_CASTABLE_WHILE_SITTING                                      ], // usablesitting [yn]
        65  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_DRAIN_ALL_POWER                                             ], // usesallpower [yn]
        66  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_CHANNELED_2,                  true                          ], // channeled [yn]
        67  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_CANT_BE_REFLECTED                                           ], // cannotreflect [yn]
        68  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_NOT_BREAK_STEALTH                                           ], // usablestealthed [yn]
        69  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_NEGATIVE_1                                                  ], // harmful [yn]
        70  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_CANT_TARGET_IN_COMBAT                                       ], // targetnotincombat [yn]
        71  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_NO_THREAT                                                   ], // nothreat [yn]
        72  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_IS_PICKPOCKET                                               ], // pickpocket [yn]
        73  => [FILTER_CR_FLAG,      'attributes1',      SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY                                    ], // dispelauraonimmunity [yn]
        74  => [FILTER_CR_CALLBACK,  'cbEquippedWeapon', 0x00100000,                              false                          ], // reqfishingpole [yn]
        75  => [FILTER_CR_FLAG,      'attributes2',      SPELL_ATTR2_CANT_TARGET_TAPPED                                          ], // requntappedtarget [yn]
     // 76  => [FILTER_CR_NYI_PH,    null,               null,                                    null                           ], // targetownitem [yn]  // the flag for this has to be somewhere....
        77  => [FILTER_CR_FLAG,      'attributes2',      SPELL_ATTR2_NOT_NEED_SHAPESHIFT                                         ], // doesntreqshapeshift [yn]
        78  => [FILTER_CR_FLAG,      'attributes2',      SPELL_ATTR2_FOOD_BUFF                                                   ], // foodbuff [yn]
        79  => [FILTER_CR_FLAG,      'attributes3',      SPELL_ATTR3_ONLY_TARGET_PLAYERS                                         ], // targetonlyplayer [yn]
        80  => [FILTER_CR_CALLBACK,  'cbEquippedWeapon', 1 << INVTYPE_WEAPONMAINHAND,             true                           ], // reqmainhand [yn]
        81  => [FILTER_CR_FLAG,      'attributes3',      SPELL_ATTR3_NO_INITIAL_AGGRO                                            ], // doesntengagetarget [yn]
        82  => [FILTER_CR_CALLBACK,  'cbEquippedWeapon', 0x00080000,                              false                          ], // reqwand [yn]
        83  => [FILTER_CR_CALLBACK,  'cbEquippedWeapon', 1 << INVTYPE_WEAPONOFFHAND,              true                           ], // reqoffhand [yn]
        84  => [FILTER_CR_FLAG,      'attributes0',      SPELL_ATTR0_HIDE_IN_COMBAT_LOG                                          ], // nolog [yn]
        85  => [FILTER_CR_FLAG,      'attributes4',      SPELL_ATTR4_FADES_WHILE_LOGGED_OUT                                      ], // auratickswhileloggedout [yn]
        87  => [FILTER_CR_FLAG,      'attributes5',      SPELL_ATTR5_START_PERIODIC_AT_APPLY                                     ], // startstickingatapplication [yn]
        88  => [FILTER_CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_CONFUSED                                       ], // usableconfused [yn]
        89  => [FILTER_CR_FLAG,      'attributes5',      SPELL_ATTR5_USABLE_WHILE_FEARED                                         ], // usablefeared [yn]
        90  => [FILTER_CR_FLAG,      'attributes6',      SPELL_ATTR6_ONLY_IN_ARENA                                               ], // onlyarena [yn]
        91  => [FILTER_CR_FLAG,      'attributes6',      SPELL_ATTR6_NOT_IN_RAID_INSTANCE                                        ], // notinraid [yn]
        92  => [FILTER_CR_FLAG,      'attributes7',      SPELL_ATTR7_REACTIVATE_AT_RESURRECT                                     ], // paladinaura [yn]
        93  => [FILTER_CR_FLAG,      'attributes7',      SPELL_ATTR7_SUMMON_PLAYER_TOTEM                                         ], // totemspell [yn]
        95  => [FILTER_CR_CALLBACK,  'cbBandageSpell'                                                                            ], // bandagespell [yn] ...don't ask
        96  => [FILTER_CR_STAFFFLAG, 'attributes0'                                                                               ], // flags1 [flags]
        97  => [FILTER_CR_STAFFFLAG, 'attributes1'                                                                               ], // flags2 [flags]
        98  => [FILTER_CR_STAFFFLAG, 'attributes2'                                                                               ], // flags3 [flags]
        99  => [FILTER_CR_STAFFFLAG, 'attributes3'                                                                               ], // flags4 [flags]
        100 => [FILTER_CR_STAFFFLAG, 'attributes4'                                                                               ], // flags5 [flags]
        101 => [FILTER_CR_STAFFFLAG, 'attributes5'                                                                               ], // flags6 [flags]
        102 => [FILTER_CR_STAFFFLAG, 'attributes6'                                                                               ], // flags7 [flags]
        103 => [FILTER_CR_STAFFFLAG, 'attributes7'                                                                               ], // flags8 [flags]
        104 => [FILTER_CR_STAFFFLAG, 'targets'                                                                                   ], // flags9 [flags]
        105 => [FILTER_CR_STAFFFLAG, 'stanceMaskNot'                                                                             ], // flags10 [flags]
        106 => [FILTER_CR_STAFFFLAG, 'spellFamilyFlags1'                                                                         ], // flags11 [flags]
        107 => [FILTER_CR_STAFFFLAG, 'spellFamilyFlags2'                                                                         ], // flags12 [flags]
        108 => [FILTER_CR_STAFFFLAG, 'spellFamilyFlags3'                                                                         ], // flags13 [flags]
        109 => [FILTER_CR_CALLBACK,  'cbEffectNames',                                                                            ], // effecttype [effecttype]
     // 110 => [FILTER_CR_NYI_PH,    null,               null,                                    null                           ], // scalingap [yn]  // unreasonably complex for now
     // 111 => [FILTER_CR_NYI_PH,    null,               null,                                    null                           ], // scalingsp [yn]  // unreasonably complex for now
        114 => [FILTER_CR_CALLBACK,  'cbReqFaction'                                                                              ], // requiresfaction [side]
        116 => [FILTER_CR_BOOLEAN,   'startRecoveryTime'                                                                         ]  // onGlobalCooldown [yn]
    );

    // fieldId => [checkType, checkValue[, fieldIsArray]]
    protected $inputFields = array(
        'cr'    => [FILTER_V_RANGE,    [1, 116],                                        true ], // criteria ids
        'crs'   => [FILTER_V_LIST,     [FILTER_ENUM_NONE, FILTER_ENUM_ANY, [0, 99999]], true ], // criteria operators
        'crv'   => [FILTER_V_REGEX,    '/[\p{C};:%\\\\]/ui',                            true ], // criteria values - only printable chars, no delimiters
        'na'    => [FILTER_V_REGEX,    '/[\p{C};%\\\\]/ui',                             false], // name / text - only printable chars, no delimiter
        'ex'    => [FILTER_V_EQUAL,    'on',                                            false], // extended name search
        'ma'    => [FILTER_V_EQUAL,    1,                                               false], // match any / all filter
        'minle' => [FILTER_V_RANGE,    [1, 99],                                         false], // spell level min
        'maxle' => [FILTER_V_RANGE,    [1, 99],                                         false], // spell level max
        'minrs' => [FILTER_V_RANGE,    [1, 999],                                        false], // required skill level min
        'maxrs' => [FILTER_V_RANGE,    [1, 999],                                        false], // required skill level max
        'ra'    => [FILTER_V_LIST,     [[1, 8], 10, 11],                                false], // races
        'cl'    => [FILTER_V_CALLBACK, 'cbClasses',                                     true ], // classes
        'gl'    => [FILTER_V_CALLBACK, 'cbGlyphs',                                      true ], // glyph type
        'sc'    => [FILTER_V_RANGE,    [0, 6],                                          true ], // magic schools
        'dt'    => [FILTER_V_LIST,     [[1, 6], 9],                                     false], // dispel types
        'me'    => [FILTER_V_RANGE,    [1, 31],                                         false]  // mechanics
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

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
            $parts[] = ['spellLevel', $_v['minle'], '>='];

        // spellLevel max
        if (isset($_v['maxle']))
            $parts[] = ['spellLevel', $_v['maxle'], '<='];

        // skillLevel min
        if (isset($_v['minrs']))
            $parts[] = ['learnedAt', $_v['minrs'], '>='];

        // skillLevel max
        if (isset($_v['maxrs']))
            $parts[] = ['learnedAt', $_v['maxrs'], '<='];

        // race
        if (isset($_v['ra']))
            $parts[] = ['AND', [['reqRaceMask', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'], ['reqRaceMask', $this->list2Mask([$_v['ra']]), '&']];

        // class [list]
        if (isset($_v['cl']))
            $parts[] = ['reqClassMask', $this->list2Mask($_v['cl']), '&'];

        // school [list]
        if (isset($_v['sc']))
            $parts[] = ['schoolMask', $this->list2Mask($_v['sc'], true), '&'];

        // glyph type [list]                                wonky, admittedly, but consult SPELL_CU_* in defines and it makes sense
        if (isset($_v['gl']))
            $parts[] = ['cuFlags', ($this->list2Mask($_v['gl']) << 6), '&'];

        // dispel type
        if (isset($_v['dt']))
            $parts[] = ['dispelType', $_v['dt']];

        // mechanic
        if (isset($_v['me']))
            $parts[] = ['OR', ['mechanic', $_v['me']], ['effect1Mechanic', $_v['me']], ['effect2Mechanic', $_v['me']], ['effect3Mechanic', $_v['me']]];

        return $parts;
    }

    public function getGenericFilter($cr)                   // access required by SpellDetailPage's SpellAttributes list
    {
        return $this->genericFilter[$cr] ?? [];
    }

    protected function cbClasses(&$val)
    {
        if (!$this->parentCats || !in_array($this->parentCats[0], [-13, -2, 7]))
            return false;

        if (!Util::checkNumeric($val, NUM_REQ_INT))
            return false;

        $type  = FILTER_V_LIST;
        $valid = [[1, 9], 11];

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbGlyphs(&$val)
    {
        if (!$this->parentCats || $this->parentCats[0] != -13)
            return false;

        if (!Util::checkNumeric($val, NUM_REQ_INT))
            return false;

        $type  = FILTER_V_LIST;
        $valid = [1, 2];

        return $this->checkInput($type, $valid, $val);
    }

    protected function cbCost($cr)
    {
        if (!Util::checkNumeric($cr[2], NUM_CAST_INT) || !$this->int2Op($cr[1]))
            return false;

        return ['OR',
            ['AND', ['powerType', [POWER_RAGE, POWER_RUNIC_POWER]], ['powerCost', (10 * $cr[2]), $cr[1]]],
            ['AND', ['powerType', [POWER_RAGE, POWER_RUNIC_POWER], '!'], ['powerCost', $cr[2], $cr[1]]]
        ];
    }

    protected function cbSource($cr)
    {
        if (!isset($this->enums[$cr[0]][$cr[1]]))
            return false;

        $_ = $this->enums[$cr[0]][$cr[1]];
        if (is_int($_))                         // specific
            return ['src.src'.$_, null, '!'];
        else if ($_)                            // any
            return ['OR', ['src.src1', null, '!'], ['src.src2', null, '!'], ['src.src4', null, '!'], ['src.src5', null, '!'], ['src.src6', null, '!'], ['src.src7', null, '!'], ['src.src9', null, '!'], ['src.src10', null, '!']];
        else if (!$_)                           // none
            return ['AND', ['src.src1', null], ['src.src2', null], ['src.src4', null], ['src.src5', null], ['src.src6', null], ['src.src7', null], ['src.src9', null], ['src.src10', null]];

        return false;
    }

    protected function cbReagents($cr)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        if ($cr[1])
            return ['OR', ['reagent1', 0, '>'], ['reagent2', 0, '>'], ['reagent3', 0, '>'], ['reagent4', 0, '>'], ['reagent5', 0, '>'], ['reagent6', 0, '>'], ['reagent7', 0, '>'], ['reagent8', 0, '>']];
        else
            return ['AND', ['reagent1', 0], ['reagent2', 0], ['reagent3', 0], ['reagent4', 0], ['reagent5', 0], ['reagent6', 0], ['reagent7', 0], ['reagent8', 0]];
    }

    protected function cbAuraNames($cr)
    {
        if (!Util::checkNumeric($cr[1], NUM_CAST_INT) || $cr[1] <= 0 || $cr[1] > self::MAX_SPELL_AURA)
            return false;

        return ['OR', ['effect1AuraId', $cr[1]], ['effect2AuraId', $cr[1]], ['effect3AuraId', $cr[1]]];
    }

    protected function cbEffectNames($cr)
    {
        if (!Util::checkNumeric($cr[1], NUM_CAST_INT) || $cr[1] <= 0 || $cr[1] > self::MAX_SPELL_EFFECT)
            return false;

        return ['OR', ['effect1Id', $cr[1]], ['effect2Id', $cr[1]], ['effect3Id', $cr[1]]];
    }

    protected function cbInverseFlag($cr, $field, $flag)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        if ($cr[1])
            return [[$field, $flag, '&'], 0];
        else
            return [$field, $flag, '&'];
    }

    protected function cbSpellstealable($cr, $field, $flag)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        if ($cr[1])
            return ['AND', [[$field, $flag, '&'], 0], ['dispelType', 1]];
        else
            return ['OR', [$field, $flag, '&'], ['dispelType', 1, '!']];
    }

    protected function cbReqFaction($cr)
    {
        switch ($cr[1])
        {
            case 1:                                         // yes
                return ['reqRaceMask', 0, '!'];
            case 2:                                         // alliance
                return ['AND', [['reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['reqRaceMask', RACE_MASK_ALLIANCE, '&']];
            case 3:                                         // horde
                return ['AND', [['reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['reqRaceMask', RACE_MASK_HORDE, '&']];
            case 4:                                         // both
                return ['AND', ['reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['reqRaceMask', RACE_MASK_HORDE, '&']];
            case 5:                                         // no
                return ['reqRaceMask', 0];
            default:
                return false;
        }
    }

    protected function cbEquippedWeapon($cr, $mask, $useInvType)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        $field = $useInvType ? 'equippedItemInventoryTypeMask' : 'equippedItemSubClassMask';

        if ($cr[1])
            return ['AND', ['equippedItemClass', ITEM_CLASS_WEAPON], [$field, $mask, '&']];
        else
            return ['OR', ['equippedItemClass', ITEM_CLASS_WEAPON, '!'], [[$field, $mask, '&'], 0]];
    }

    protected function cbUsableInArena($cr)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        if ($cr[1])
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

    protected function cbBandageSpell($cr)
    {
        if (!$this->int2Bool($cr[1]))
            return false;

        if ($cr[1])                                         // match exact, not as flag
            return ['AND', ['attributes1', SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2 | SPELL_ATTR1_CHANNEL_TRACK_TARGET], ['effect1ImplicitTargetA', 21]];
        else
            return ['OR', ['attributes1', SPELL_ATTR1_CHANNELED_1 | SPELL_ATTR1_CHANNELED_2 | SPELL_ATTR1_CHANNEL_TRACK_TARGET, '!'], ['effect1ImplicitTargetA', 21, '!']];
    }
}

?>
