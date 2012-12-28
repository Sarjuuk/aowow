<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


class Lang
{
    public static $main;
    public static $search;
    public static $game;
    public static $filter;
    public static $error;

    public static $account;
    public static $achievement;
    public static $compare;
    public static $item;
    public static $maps;
    public static $spell;
    public static $talent;
    public static $zone;

    public static function load($loc)
    {
        if (@(include('localization/locale_'.$loc.'.php')) !== 1)
            die('File for localization '.$loc.' not found.');

        foreach ($lang as $k => $v)
            self::$$k = $v;
    }

    // todo: expand
    public static function getInfoBoxForFlags($flags)
    {
        $tmp = array();

        if ($flags & CUSTOM_DISABLED)
            $tmp[] = '<span class="tip" onmouseover="Tooltip.showAtCursor(event, \''.self::$main['disabledHint'].'\', 0, 0, \'q\')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">'.self::$main['disabled'].'</span>';

        if ($flags & CUSTOM_SERVERSIDE)
            $tmp[] = '<span class="tip" onmouseover="Tooltip.showAtCursor(event, \''.self::$main['serversideHint'].'\', 0, 0, \'q\')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">'.self::$main['serverside'].'</span>';

        return $tmp;
    }

    public static function getReputationLevelForPoints($pts)
    {
        if ($pts >= 41999)
            return self::$game['rep'][REP_EXALTED];
        else if ($pts >= 20999)
            return self::$game['rep'][REP_REVERED];
        else if ($pts >= 8999)
            return self::$game['rep'][REP_HONORED];
        else if ($pts >= 2999)
            return self::$game['rep'][REP_FRIENDLY];
        else /* if ($pts >= 1) */
            return self::$game['rep'][REP_NEUTRAL];
    }

    public static function getStances($stanceMask)
    {
        $stanceMask &= 0x1F84F213E;                         // clamp to available stances/forms..

        $tmp = array();
        $i   = 1;

        while ($stanceMask)
        {
            if ($stanceMask & (1 << ($i - 1)))
            {
                $tmp[] = self::$game['st'][$i];
                $stanceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getMagicSchools($schoolMask)
    {
        $schoolMask &= 0x7F;                                // clamp to available schools..

        $tmp = array();
        $i   = 1;

        while ($schoolMask)
        {
            if ($schoolMask & (1 << ($i - 1)))
            {
                $tmp[] = self::$game['sc'][$i];
                $schoolMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getClassString($classMask)
    {
        $classMask &= CLASS_MASK_ALL;                       // clamp to available classes..

        if ($classMask == CLASS_MASK_ALL)                   // available to all classes
            return false;

        if (!$classMask)                                    // no restrictions left
            return false;

        $tmp = array();
        $i   = 1;

        while ($classMask)
        {
            if ($classMask & (1 << ($i - 1)))
            {
                $tmp[] = '<a href="?class='.$i.'" class="c'.$i.'">'.self::$game['cl'][$i].'</a>';
                $classMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getRaceString($raceMask)
    {
        $raceMask &= RACE_MASK_ALL;                         // clamp to available races..

        if ($raceMask == RACE_MASK_ALL)                     // available to all races (we don't display 'both factions')
            return false;

        if (!$raceMask)                                     // no restrictions left (we don't display 'both factions')
            return false;

        $tmp  = array();
        $side = 0;
        $i    = 1;

        if (!$raceMask)
            return array('side' => 3, 'name' => self::$game['ra'][3]);

        if ($ra = Util::factionByRaceMask($raceMask))
            return array('side' => $ra, 'name' => self::$game['ra'][-$ra]);

        if ($raceMask & (RACE_ORC | RACE_UNDEAD | RACE_TAUREN | RACE_TROLL | RACE_BLOODELF))
            $side |= 2;

        if ($raceMask & (RACE_HUMAN | RACE_DWARF | RACE_NIGHTELF | RACE_GNOME | RACE_DRAENEI))
            $side |= 1;

        while ($raceMask)
        {
            if ($raceMask & (1 << ($i - 1)))
            {
                $tmp[] = '<a href="?race='.$i.'" class="q1">'.self::$game['ra'][$i].'</a>';
                $raceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return array ('side' => $side, 'name' => implode(', ', $tmp));
    }
}

class Util
{
    public static $resistanceFields         = array(
        null,           'holy_res',     'fire_res',     'nature_res',   'frost_res',    'shadow_res',   'arcane_res'
    );

    private static $rarityColorStings       = array(        // zero-indexed
        '9d9d9d',       'ffffff',       '1eff00',       '0070dd',       'a335ee',       'ff8000',       'e5cc80',       'e6cc80'
    );

    public static $localeStrings            = array(        // zero-indexed
        'enus',         null,           'frfr',         'dede',         null,           null,           'eses',         null,           'ruru'
    );

    private static $typeStrings             = array(        // zero-indexed
        null,           'npc',          'object',       'item',         'itemset',      'quest',        'spell',        'zone',         'faction',
        'pet',          'achievement',  'title',        'event',        'class',        'race',         'skill',        null,           'currency'
    );

    public static $combatRatingToItemMod    = array(        // zero-indexed
        null,           12,             13,             14,             15,             16,             17,             18,             19,
        20,             21,             null,           null,           null,           null,           null,           null,           28,
        29,             30,             null,           null,           null,           37,             44
    );

    public static $gtCombatRatings          = array(
        12 => 1.5,      13 => 12,       14 => 15,       15 => 5,        16 => 10,       17 => 10,       18 => 8,        19 => 14,       20 => 14,
        21 => 14,       22 => 10,       23 => 10,       24 => 0,        25 => 0,        26 => 0,        27 => 0,        28 => 10,       29 => 10,
        30 => 10,       31 => 10,       32 => 14,       33 => 0,        34 => 0,        35 => 25,       36 => 10,       37 => 2.5,      44 => 3.756097412109376
    );

    public static $lvlIndepRating           = array(        // rating doesn't scale with level
        ITEM_MOD_MANA,                  ITEM_MOD_HEALTH,                ITEM_MOD_ATTACK_POWER,          ITEM_MOD_MANA_REGENERATION,     ITEM_MOD_SPELL_POWER,
        ITEM_MOD_HEALTH_REGEN,          ITEM_MOD_SPELL_PENETRATION,     ITEM_MOD_BLOCK_VALUE
    );

    public static $sockets                  = array(        // jsStyle Strings
        'meta',                         'red',                          'yellow',                       'blue'
    );

    public static $itemMods                 = array(        // zero-indexed; "mastrtng": unused mastery; _[a-z] => taken mods..
        'dmg',              'mana',             'health',           'agi',              'str',              'int',              'spi',
        'sta',              'energy',           'rage',             'focus',            'runicpwr',         'defrtng',          'dodgertng',
        'parryrtng',        'blockrtng',        'mlehitrtng',       'rgdhitrtng',       'splhitrtng',       'mlecritstrkrtng',  'rgdcritstrkrtng',
        'splcritstrkrtng',  '_mlehitrtng',      '_rgdhitrtng',      '_splhitrtng',      '_mlecritstrkrtng', '_rgdcritstrkrtng', '_splcritstrkrtng',
        'mlehastertng',     'rgdhastertng',     'splhastertng',     'hitrtng',          'critstrkrtng',     '_hitrtng',         '_critstrkrtng',
        'resirtng',         'hastertng',        'exprtng',          'atkpwr',           'rgdatkpwr',        'feratkpwr',        'splheal',
        'spldmg',           'manargn',          'armorpenrtng',     'splpwr',           'healthrgn',        'splpen',           'block',                                          // ITEM_MOD_BLOCK_VALUE
        'mastrtng',         'armor',            'firres',           'frores',           'holres',           'shares',           'natres',
        'arcres',           'firsplpwr',        'frosplpwr',        'holsplpwr',        'shasplpwr',        'natsplpwr',        'arcsplpwr'
    );

    public static $ssdMaskFields            = array(
        'shoulderMultiplier',           'trinketMultiplier',            'weaponMultiplier',             'primBudged',
        'rangedMultiplier',             'clothShoulderArmor',           'leatherShoulderArmor',         'mailShoulderArmor',
        'plateShoulderArmor',           'weaponDPS1H',                  'weaponDPS2H',                  'casterDPS1H',
        'casterDPS2H',                  'rangedDPS',                    'wandDPS',                      'spellPower',
        null,                           null,                           'tertBudged',                   'clothCloakArmor',
        'clothChestArmor',              'leatherChestArmor',            'mailChestArmor',               'plateChestArmor'
    );

    public static $changeLevelString        = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_staticTooltipLevelClick(this, null, 0)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><!--lvl-->%s</a>';

    public static $bgImagePath              = array (
        'tiny'   => 'style="background-image: url(/images/icons/tiny/%s.gif)"',
        'small'  => 'style="background-image: url(/images/icons/small/%s.jpg)"',
        'medium' => 'style="background-image: url(/images/icons/medium/%s.jpg)"',
        'large'  => 'style="background-image: url(/images/icons/large/%s.jpg)"',
    );

    private static $execTime = 0.0;

    public static function execTime($set = false)
    {
        if ($set)
        {
            self::$execTime = microTime(true);
            return;
        }

        $newTime        = microTime(true);
        $tDiff          = $newTime - self::$execTime;
        self::$execTime = $newTime;

        return self::formatTime($tDiff * 1000, true);
    }

    public static function colorByRarity($idx)
    {
        if (!isset(self::$rarityColorStings))
            $idx = 1;

        return self::$rarityColorStings($idx);
    }

    public static function formatMoney($qty)
    {
        $money = '';

        if ($qty >= 10000)
        {
            $g = floor($qty / 10000);
            $money .= '<span class="moneygold">'.$g.'</span> ';
            $qty -= $g * 10000;
        }

        if ($qty >= 100)
        {
            $s = floor($qty / 100);
            $money .= '<span class="moneysilver">'.$s.'</span> ';
            $qty -= $s * 100;
        }

        if ($qty > 0)
            $money .= '<span class="moneycopper">'.$qty.'</span>';

        return $money;
    }

    public static function parseTime($sec)
    {
        $time = array();

        if ($sec >= 3600 * 24)
        {
            $time['d'] = floor($sec / 3600 / 24);
            $sec -= $time['d'] * 3600 * 24;
        }

        if ($sec >= 3600)
        {
            $time['h'] = floor($sec / 3600);
            $sec -= $time['h'] * 3600;
        }

        if ($sec >= 60)
        {
            $time['m'] = floor($sec / 60);
            $sec -= $time['m'] * 60;
        }

        if ($sec > 0)
        {
            $time['s'] = (int)$sec;
            $sec -= $time['s'];
        }

        if (($sec * 1000) % 1000)
            $time['ms'] = (int)($sec * 1000);

        return $time;
    }

    public static function formatTime($base, $short = false)
    {
        $s = self::parseTime($base / 1000);
        $fmt = array();

        if ($short)
        {
            if (isset($s['d']))
                return round($s['d'])." ".Lang::$main['daysAbbr'];
            if (isset($s['h']))
                return round($s['h'])." ".Lang::$main['hoursAbbr'];
            if (isset($s['m']))
                return round($s['m'])." ".Lang::$main['minutesAbbr'];
            if (isset($s['s']))
                return round($s['s'] + @$s['ms'] / 1000, 2)." ".Lang::$main['secondsAbbr'];
            if (isset($s['ms']))
                return $s['ms']." ".Lang::$main['millisecsAbbr'];
        }
        else
        {
            if (isset($s['d']))
                $fmt[] = $s['d']." ".Lang::$main['days'];
            if (isset($s['h']))
                $fmt[] = $s['h']." ".Lang::$main['hours'];
            if (isset($s['m']))
                $fmt[] = $s['m']." ".Lang::$main['minutes'];
            if (isset($s['s']))
                $fmt[] = $s['s']." ".Lang::$main['seconds'];
            if (isset($s['ms']))
                $fmt[] = $s['ms']." ".Lang::$main['millisecs'];
        }

        return implode(' ', $fmt);
    }

    public static function factionByRaceMask($race)
    {
        switch ($race)
        {
            case '0':       return 3;                       // Any
            case '1791':    return 3;                       // Any
            case '690':     return 2;                       // Horde
            case '1101':    return 1;                       // Alliance
            default:        return 0;
        }
    }

    private static function db_conform_array_callback(&$item, $key)
    {
        $item = Util::sqlEscape($item);
    }

    public static function sqlEscape($string)
    {
        if (!is_array($string))
            return mysql_real_escape_string(trim($string));

        array_walk($string, 'Util::db_conform_array_callback');
        return $string;
    }

    public static function jsEscape($string)
    {
        return strtr(trim($string), array(
            '\\' => '\\\\',
            "'"  => "\\'",
            '"'  => '\\"',
            "\r" => '\\r',
            "\n" => '\\n',
            '</' => '<\/',
        ));
    }

    public static function localizedString($data, $field)
    {
        // default back to enUS if localization unavailable

        // default case: selected locale available
        if (!empty($data[$field.'_loc'.User::$localeId]))
            return $data[$field.'_loc'.User::$localeId];

        // locale not enUS; aowow-type localization available
        else if (User::$localeId != LOCALE_EN && isset($data[$field.'_loc0']) && !empty($data[$field.'_loc0']))
            return  '['.$data[$field.'_loc0'].']';

        // locale not enUS; TC localization; add brackets
        else if (User::$localeId != LOCALE_EN && isset($data[$field]) && !empty($data[$field]))
            return '['.$data[$field].']';

        // locale enUS; TC localization; return normal
        else if (User::$localeId == LOCALE_EN && isset($data[$field]) && !empty($data[$field]))
            return $data[$field];

        // nothing to find; be empty
        else
            return '';
    }

    public static function extractURLParams($str)
    {
        $arr = explode('.', $str);

        foreach ($arr as $i => $a)
            if (!is_numeric($a))
                $arr[$i] = null;

        return $arr;
    }

    // for item and spells
    public static function setRatingLevel($level, $type, $val)
    {
        if (in_array($type, array(ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_PARRY_RATING, ITEM_MOD_BLOCK_RATING)) && $level < 34)
            $level = 34;

        if (!isset(Util::$gtCombatRatings[$type]))
            $result = 0;

        else if ($level > 70)
            $c = 82 / 52 * pow(131 / 63, ($level - 70) / 10);
        else if ($level > 60)
            $c = 82 / (262 - 3 * $level);
        else if ($level > 10)
            $c = ($level - 8) / 52;
        else
            $c = 2 / 52;

        $result = number_format($val / Util::$gtCombatRatings[$type] / $c, 2);

        if (!in_array($type, array(ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_EXPERTISE_RATING)))
            $result .= '%';

        return sprintf(Lang::$item['ratingString'], '<!--rtg%'.$type.'-->' . $result, '<!--lvl-->' . $level);
    }

    public static function powerUseLocale($domain)
    {
        foreach (Util::$localeStrings as $k => $v)
        {
            if (strstr($v, $domain))
            {
                User::useLocale($k);
                Lang::load(User::$localeString);
                return;
            }
        }

        if ($domain == 'www')
        {
            /* todo: dont .. should use locale given by inclusion of aowowPower .. should be fixed in aowowPower.js */
            User::useLocale(LOCALE_EN);
            Lang::load(User::$localeString);
        }
    }

    // EnchantmentTypes
    // 0 => (dnd stuff; ignore)
    // 1 => proc spell from ObjectX (amountX == procChance?; ignore)
    // 2 => +AmountX damage
    // 3 => Spells form ObjectX (amountX == procChance?)
    // 4 => +AmountX resistance for ObjectX School
    // 5 => +AmountX for Statistic by type of ObjectX
    // 6 => Rockbiter AmountX as Damage (ignore)
    // 7 => Engineering gadgets
    // 8 => Extra Sockets AmountX as socketCount (ignore)
    public static function parseItemEnchantment($enchant, $amountOverride = null)
    {
        if (!$enchant || empty($enchant))
            return false;

        $jsonStats = array();
        for ($h = 1; $h <= 3; $h++)
        {
            if (isset($amountOverride))                     // itemSuffixes have dynamic amount
                $enchant['amount'.$h] = $amountOverride;

            switch ($enchant['type'.$h])
            {
                case 2:
                    @$jsonStats[2] += $enchant['amount'.$h];
                    break;
                case 3:
                case 7:
                    $spl  = new Spell($enchant['object'.$h]);
                    $gain = $spl->getStatGain();
                    foreach ($gain as $k => $v)             // array_merge screws up somehow...
                        @$jsonStats[$k] += $v;
                    break;
                case 4:
                    switch ($enchant['object'.$h])
                    {
                        case 0:                             // Physical
                            @$jsonStats[50] += $enchant['amount'.$h];
                            break;
                        case 1:                             // Holy
                            @$jsonStats[53] += $enchant['amount'.$h];
                            break;
                        case 2:                             // Fire
                            @$jsonStats[51] += $enchant['amount'.$h];
                            break;
                        case 3:                             // Nature
                            @$jsonStats[55] += $enchant['amount'.$h];
                            break;
                        case 4:                             // Frost
                            @$jsonStats[52] += $enchant['amount'.$h];
                            break;
                        case 5:                             // Shadow
                            @$jsonStats[54] += $enchant['amount'.$h];
                            break;
                        case 6:                             // Arcane
                            @$jsonStats[56] += $enchant['amount'.$h];
                            break;
                    }
                    break;
                case 5:
                    @$jsonStats[$enchant['object'.$h]] += $enchant['amount'.$h];
                    break;
            }
        }

        // check if we use these mods
        $return = array();
        foreach ($jsonStats as $k => $v)
        {
            if ($str = Util::$itemMods[$k])
                $return[$str] = $v;
        }

        return $return;
    }
}

?>
