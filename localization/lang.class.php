<?php

class Lang
{
    private static $timeUnits;
    private static $lang;
    private static $main;
    private static $account;
    private static $user;
    private static $game;
    private static $maps;
    private static $profiler;
    private static $screenshot;
    private static $privileges;
    private static $smartAI;
    private static $unit;

    // types
    private static $achievement;
    private static $areatrigger;
    private static $chrClass;
    private static $currency;
    private static $event;
    private static $faction;
    private static $gameObject;
    private static $icon;
    private static $item;
    private static $itemset;
    private static $mail;
    private static $npc;
    private static $pet;
    private static $quest;
    private static $race;
    private static $skill;
    private static $sound;
    private static $spell;
    private static $title;
    private static $zone;
    private static $guide;

    private static $emote;
    private static $enchantment;

    private static $locales = array(
        LOCALE_EN => 'English',
        LOCALE_FR => 'Français',
        LOCALE_DE => 'Deutsch',
        LOCALE_CN => '简体中文',
        LOCALE_ES => 'Español',
        LOCALE_RU => 'Русский'
    );

    public const FMT_RAW    = 0;
    public const FMT_HTML   = 1;
    public const FMT_MARKUP = 2;

    public static function load(string $loc) : void
    {
        if (!file_exists('localization/locale_'.$loc.'.php'))
            die('File for localization '.strToUpper($loc).' not found.');
        else
            require 'localization/locale_'.$loc.'.php';

        foreach ($lang as $k => $v)
            self::$$k = $v;

        // *cough* .. reuse-hacks (because copy-pastaing text for 5 locales sucks)
        self::$item['cat'][2] = [self::$item['cat'][2], self::$spell['weaponSubClass']];
        self::$item['cat'][2][1][14] .= ' ('.self::$item['cat'][2][0].')';
        self::$main['moreTitles']['privilege'] = self::$privileges['_privileges'];
    }

    public static function __callStatic(string $prop, array $args) // : ?string|array
    {
        $vspfArgs = [];
        foreach ($args as $i => $arg)
        {
            if (!is_array($arg))
                continue;

            $vspfArgs = $arg;
            unset($args[$i]);
        }

        if ($x = self::exist($prop, ...$args))
            return self::vspf($x, $vspfArgs);

        $dbt  = debug_backtrace()[0];
        $file = explode(DIRECTORY_SEPARATOR, $dbt['file']);
        trigger_error('Lang - undefined property Lang::$'.$prop.'[\''.implode('\'][\'', $args).'\'], called in '.array_pop($file).':'.$dbt['line'], E_USER_WARNING);
    }

    public static function exist(string $prop, ...$args)
    {
        if (!isset(self::$$prop))
            return null;

        $ref = self::$$prop;
        foreach ($args as $a)
        {
            if (!isset($ref[$a]))
                return null;

            $ref = $ref[$a];
        }

        return $ref;
    }

    public static function concat(array $args, bool $useAnd = true, ?callable $callback = null) : string
    {
        $b = '';
        $i = 0;
        $n = count($args);
        foreach ($args as $k => $arg)
        {
            if (is_callable($callback))
                $b .= $callback($arg, $k);
            else
                $b .= $arg;

            if ($n > 1 && $i < ($n - 2))
                $b .= ', ';
            else if ($n > 1 && $i == $n - 2)
                $b .= self::main($useAnd ? 'and' : 'or');

            $i++;
        }

        return $b;
    }

    // truncate string after X chars. If X is inside a word truncate behind it.
    public static function trimTextClean(string $text, int $len = 100) : string
    {
        // remove line breaks
        $text = strtr($text, ["\n" => ' ', "\r" => ' ']);

        // limit whitespaces to one at a time
        $text = preg_replace('/\s+/', ' ', trim($text));

        if ($len <= 0 || mb_strlen($text) <= $len)
            return $text;

        $n = 0;
        $b = [];
        $parts = explode(' ', $text);
        while ($n < $len && $parts)
        {
            $_   = array_shift($parts);
            $n  += mb_strlen($_);
            $b[] = $_;
        }

        return implode(' ', $b).'…';
    }

    // add line breaks to string after X chars. If X is inside a word break behind it.
    public static function breakTextClean(string $text, int $len = 30, int $fmt = self::FMT_HTML) : string
    {
        // remove line breaks
        $text = strtr($text, ["\n" => ' ', "\r" => ' ']);

        // limit whitespaces to one at a time
        $text = preg_replace('/\s+/', ' ', trim($text));

        if ($len <= 0 || mb_strlen($text) <= $len)
            return $text;

        $row = [];
        $i   = 0;
        $n   = 0;
        foreach (explode(' ', $text) as $p)
        {
            $row[$i][] = $p;
            $n += (mb_strlen($p) + 1);

            if ($n < $len)
                continue;

            $n = 0;
            $i++;
        }
        foreach ($row as &$r)
            $r = implode(' ', $r);

        switch ($fmt)
        {
            case self::FMT_HTML:   $separator = '<br />'; break;
            case self::FMT_MARKUP: $separator = '[br]';   break;
            case self::FMT_RAW:
            default:               $separator = "\n";     break;
        }

        return implode($separator, $row);
    }

    public static function sort(string $prop, string $group, int $method = SORT_NATURAL) : void
    {

        if (!isset(self::$$prop))
        {
            trigger_error('Lang::sort - tried to use undefined property Lang::$'.$prop, E_USER_WARNING);
            return;
        }

        $var = &self::$$prop;
        if (!isset($var[$group]))
        {
            trigger_error('Lang::sort - tried to use undefined property Lang::$'.$prop.'[\''.$group.'\']', E_USER_WARNING);
            return;
        }

        asort($var[$group], $method);
    }

    // todo: expand
    public static function getInfoBoxForFlags(int $flags) : array
    {
        $tmp = [];

        if ($flags & CUSTOM_DISABLED)
            $tmp[] = '[tooltip name=disabledHint]'.Util::jsEscape(self::main('disabledHint')).'[/tooltip][span class=tip tooltip=disabledHint]'.Util::jsEscape(self::main('disabled')).'[/span]';

        if ($flags & CUSTOM_SERVERSIDE)
            $tmp[] = '[tooltip name=serversideHint]'.Util::jsEscape(self::main('serversideHint')).'[/tooltip][span class=tip tooltip=serversideHint]'.Util::jsEscape(self::main('serverside')).'[/span]';

        if ($flags & CUSTOM_UNAVAILABLE)
            $tmp[] = self::main('unavailable');

        if ($flags & CUSTOM_EXCLUDE_FOR_LISTVIEW && User::isInGroup(U_GROUP_STAFF))
            $tmp[] = '[tooltip name=excludedHint]This entry is excluded from lists and is not searchable.[/tooltip][span tooltip=excludedHint class="tip q10"]Hidden[/span]';

        return $tmp;
    }

    public static function getLocks(int $lockId, ?array &$ids = [], bool $interactive = false, int $fmt = self::FMT_HTML) : array
    {
        $locks = [];
        $ids   = [];
        $lock  = DB::Aowow()->selectRow('SELECT * FROM ?_lock WHERE id = ?d', $lockId);
        if (!$lock)
            return $locks;

        for ($i = 1; $i <= 5; $i++)
        {
            $prop = $lock['properties'.$i];
            $rank = $lock['reqSkill'.$i];
            $name = '';

            if ($lock['type'.$i] == LOCK_TYPE_ITEM)
            {
                $name = ItemList::getName($prop);
                if (!$name)
                    continue;

                if ($fmt == self::FMT_HTML)
                    $name = $interactive ? '<a class="q1" href="?item='.$prop.'">'.$name.'</a>' : '<span class="q1">'.$name.'</span>';
                else if ($interactive && $fmt == self::FMT_MARKUP)
                {
                    $name = '[item='.$prop.']';
                    $ids[Type::ITEM][] = $prop;
                }
                else
                    $name = $prop;

            }
            else if ($lock['type'.$i] == LOCK_TYPE_SKILL)
            {
                $name = self::spell('lockType', $prop);
                if (!$name)
                    continue;

                // skills
                if (in_array($prop, [1, 2, 3, 20]))
                {
                    $skills = array(
                        1 => SKILL_LOCKPICKING,
                        2 => SKILL_HERBALISM,
                        3 => SKILL_MINING,
                       20 => SKILL_INSCRIPTION
                    );

                    if ($fmt == self::FMT_HTML)
                        $name = $interactive ? '<a href="?skill='.$skills[$prop].'">'.$name.'</a>' : '<span class="q1">'.$name.'</span>';
                    else if ($interactive && $fmt == self::FMT_MARKUP)
                    {
                        $name = '[skill='.$skills[$prop].']';
                        $ids[Type::SKILL][] = $skills[$prop];
                    }
                    else
                        $name = $skills[$prop];

                    if ($rank > 0)
                        $name .= ' ('.$rank.')';
                }
                // Lockpicking
                else if ($prop == 4)
                {
                    if ($fmt == self::FMT_HTML)
                        $name = $interactive ? '<a href="?spell=1842">'.$name.'</a>' : '<span class="q1">'.$name.'</span>';
                    else if ($interactive && $fmt == self::FMT_MARKUP)
                    {
                        $name = '[spell=1842]';
                        $ids[Type::SPELL][] = 1842;
                    }
                    // else $name = $name
                }
                // exclude unusual stuff
                else if (User::isInGroup(U_GROUP_STAFF))
                {
                    if ($rank > 0)
                        $name .= ' ('.$rank.')';
                }
                else
                    continue;
            }
            else
                continue;

            $locks[$lock['type'.$i] == LOCK_TYPE_ITEM ? $prop : -$prop] = $name;
        }

        return $locks;
    }

    public static function getReputationLevelForPoints(int $pts) : string
    {
        return self::game('rep', Game::getReputationLevelForPoints($pts));
    }

    public static function getRequiredItems(int $class, int $mask, bool $short = true) : string
    {
        if (!in_array($class, [ITEM_CLASS_MISC, ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON]))
            return '';

        // not checking weapon / armor here. It's highly unlikely that they overlap
        if ($short)
        {
            // misc - Mounts
            if ($class == ITEM_CLASS_MISC)
                return '';

            // all basic armor classes
            if ($class == ITEM_CLASS_ARMOR && ($mask & 0x1E) == 0x1E)
                return '';

            // all weapon classes
            if ($class == ITEM_CLASS_WEAPON && ($mask & 0x1DE5FF) == 0x1DE5FF)
                return '';

            foreach (self::spell('subClassMasks') as $m => $str)
                if ($mask == $m)
                    return $str;
        }

        if ($class == ITEM_CLASS_MISC)                      // yeah hardcoded.. sue me!
            return self::spell('cat', -5, 0);

        $tmp  = [];
        $strs = self::spell($class == ITEM_CLASS_ARMOR ? 'armorSubClass' : 'weaponSubClass');
        foreach ($strs as $k => $str)
            if ($mask & (1 << $k) && $str)
                $tmp[] = $str;

        if (!$tmp && $class == ITEM_CLASS_ARMOR)
            return self::spell('cat', -11, 8);
        else if (!$tmp && $class == ITEM_CLASS_WEAPON)
            return self::spell('cat', -11, 6);
        else
            return implode(', ', $tmp);
    }

    public static function getStances(int $stanceMask) : string
    {
        $stanceMask &= 0xFF37F6FF;                          // clamp to available stances/forms..

        $tmp = [];
        $i   = 1;

        while ($stanceMask)
        {
            if ($stanceMask & (1 << ($i - 1)))
            {
                $tmp[] = self::game('st', $i);
                $stanceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getMagicSchools(int $schoolMask) : string
    {
        $schoolMask &= SPELL_ALL_SCHOOLS;                   // clamp to available schools..
        $tmp = [];
        $i   = 0;

        while ($schoolMask)
        {
            if ($schoolMask & (1 << $i))
            {
                $tmp[] = self::game('sc', $i);
                $schoolMask &= ~(1 << $i);
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getClassString(int $classMask, array &$ids = [], int $fmt = self::FMT_HTML) : string
    {
        $classMask &= CLASS_MASK_ALL;                       // clamp to available classes..

        if ($classMask == CLASS_MASK_ALL)                   // available to all classes
            return '';

        $tmp  = [];
        $i    = 1;

        switch ($fmt)
        {
            case self::FMT_HTML:
                $base = '<a href="?class=%1$d" class="c%1$d">%2$s</a>';
                $br   = '';
                break;
            case self::FMT_MARKUP:
                $base = '[class=%1$d]';
                $br   = '[br]';
                break;
            case self::FMT_RAW:
            default:
                $base = '%2$s';
                $br   = '';
        }

        while ($classMask)
        {
            if ($classMask & (1 << ($i - 1)))
            {
                $tmp[$i]    = (!fMod(count($tmp) + 1, 3) ? $br : null).sprintf($base, $i, self::game('cl', $i));
                $classMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        $ids = array_keys($tmp);

        return implode(', ', $tmp);
    }

    public static function getRaceString(int $raceMask, array &$ids = [], int $fmt = self::FMT_HTML) : string
    {
        $raceMask &= RACE_MASK_ALL;                         // clamp to available races..

        if ($raceMask == RACE_MASK_ALL)                     // available to all races (we don't display 'both factions')
            return '';

        if (!$raceMask)
            return '';

        $tmp  = [];
        $i    = 1;

        switch ($fmt)
        {
            case self::FMT_HTML:
                $base = '<a href="?race=%1$d" class="q1">%2$s</a>';
                $br   = '';
                break;
            case self::FMT_MARKUP:
                $base = '[race=%1$d]';
                $br   = '[br]';
                break;
            case self::FMT_RAW:
            default:
                $base = '%2$s';
                $br   = '';
        }

        if ($raceMask == RACE_MASK_HORDE)
            return self::game('ra', -2);

        if ($raceMask == RACE_MASK_ALLIANCE)
            return self::game('ra', -1);

        while ($raceMask)
        {
            if ($raceMask & (1 << ($i - 1)))
            {
                $tmp[$i]   = (!fMod(count($tmp) + 1, 3) ? $br : null).sprintf($base, $i, self::game('ra', $i));
                $raceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        $ids = array_keys($tmp);

        return implode(', ', $tmp);
    }

    public static function formatSkillBreakpoints(array $bp, int $fmt = self::FMT_MARKUP) : string
    {
        $tmp = self::game('difficulty').self::main('colon');

        switch ($fmt)
        {
            case self::FMT_HTML:   $base = '<span class="r%1$d">%2$s</span> '; break;
            case self::FMT_MARKUP: $base = '[color=r%1$d]%2$s[/color] '; break;
            case self::FMT_RAW:
            default:               $base = '%2$s '; break;
        }

        for ($i = 0; $i < 4; $i++)
            if (!empty($bp[$i]))
                $tmp .= sprintf($base, $i + 1, $bp[$i]);

        return trim($tmp);
    }

    public static function nf(float $number, int $decimals = 0, bool $no1k = false) : string
    {
        return number_format($number, $decimals, self::main('nfSeparators', 1), $no1k ? '' : self::main('nfSeparators', 0));
    }

    public static function typeName(int $type) : string
    {
        return Util::ucFirst(self::game(Type::getFileString($type)));
    }

    public static function formatTime(int $msec, string $prop = 'game', string $src = 'timeAbbrev', bool $concat = false) : string
    {
        if ($msec < 0)
            $msec = 0;

        $time   = Util::parseTime($msec);                   // [$ms, $s, $m, $h, $d]
        $mult   = [0, 1000, 60, 60, 24];
        $total  = 0;
        $ref    = [];
        $result = [];

        if (is_array(self::$$prop[$src]))
            $ref = &self::$$prop[$src];
        else
        {
            trigger_error('Lang::formatTime - tried to access undefined property Lang::$'.$prop, E_USER_WARNING);
            return '';
        }

        if (!$msec)
            return self::vspf($ref[0], [0]);

        if ($concat)
        {
            for ($i = 4; $i > 0; $i--)
            {
                $total += $time[$i];
                if (isset($ref[$i]) && ($total || ($i == 1 && !$result)))
                {
                    $result[] = self::vspf($ref[$i], [$total]);
                    $total = 0;
                }
                else
                    $total *= $mult[$i];
            }

            return implode(', ', $result);
        }

        for ($i = 4; $i > 0; $i--)
        {
            $total += $time[$i];
            if (isset($ref[$i]) && ($total || $i == 1))
                return self::vspf($ref[$i], [$total + ($time[$i-1] ?? 0) / $mult[$i]]);
            else
                $total *= $mult[$i];
        }

        return '';
    }

    private static function vspf(/* array|string */ $var, array $args = []) // : array|string
    {
        if (is_array($var))
        {
            foreach ($var as &$v)
                $v == self::vspf($v, $args);

            return $var;
        }

        if ($args)
            $var = vsprintf($var, $args);

        // line break
        // |n
        $var = str_replace('|n', '<br />', $var);

        // color
        // |c<aarrggbb><word>|r
        $var = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $var);

        // icon
        // |T<imgPath>:0:0:0:-1|t   -   not used, skip if found
        $var = preg_replace('/\|T[^\|]+\|t/', '', $var);

        // hyperlink
        // |H<hyperlinkStruct>|h<name>|h    -   not used, truncate structure if found
        $var = preg_replace('/\|H[^\|]+\|h([^\|]+)\|h/', '$1', $var);

        // french preposition : de
        // |2 <word>
        $var = preg_replace_callback('/\|2\s(\w)/i', function ($m) {
            if (in_array(strtolower($m[1]), ['a', 'e', 'h', 'i', 'o', 'u']))
                return "d'".$m[1];
            else
                return 'de '.$m[1];
        }, $var);

        // russian word cunjugation thingy
        // |3-<number>(<word>)
        $var = preg_replace_callback('/\|3-(\d)\(([^\)]+)\)/i', function ($m) {
            switch ($m[0])
            {
                case 1:                                     // seen cases
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                default:                                    // passthrough .. unk case
                    return $m[1];
            }

        }, $var);

        // numeric switch
        // <number> |4<singular>:<plural>[:<plural2>];
        $var = preg_replace_callback('/([\d\.\,]+)([^\d]*)\|4([^:]*):([^;]*);/i', function ($m) {
            $plurals = explode(':', $m[4]);
            $result  = '';

            if (count($plurals) == 2)                       // special case: ruRU
            {
                switch (substr($m[1], -1))                  // check last digit of number
                {
                    case 1:
                        // but not 11 (teen number)
                        if (!in_array($m[1], [11]))
                        {
                            $result = $m[3];
                            break;
                        }
                    case 2:
                    case 3:
                    case 4:
                        // but not 12, 13, 14 (teen number) [11 is passthrough]
                        if (!in_array($m[1], [11, 12, 13, 14]))
                        {
                            $result = $plurals[0];
                            break;
                        }
                        break;
                    default:
                        $result = $plurals[1];
                }
            }
            else
                $result = ($m[1] == 1 ? $m[3] : $plurals[0]);

            return $m[1].$m[2].$result;
        }, $var);

        return $var;
    }
}

?>
