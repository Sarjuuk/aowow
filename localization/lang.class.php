<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

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
    private static $video;
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

    private static ?Locale $locale = null;

    public const FMT_RAW    = 0;
    public const FMT_HTML   = 1;
    public const FMT_MARKUP = 2;

    public const CONCAT_NONE = 0;
    public const CONCAT_AND  = 1;
    public const CONCAT_OR   = 2;

    public static function load(Locale $loc) : void
    {
        if (self::$locale == $loc)
            return;

        if (!file_exists('localization/locale_'.$loc->json().'.php'))
            die('File for locale '.$loc->name.' not found.');
        else
            require 'localization/locale_'.$loc->json().'.php';

        foreach ($lang as $k => $v)
            self::$$k = $v;

        // *cough* .. reuse-hacks (because copy-pastaing text for 5 locales sucks)
        self::$item['cat'][2][1] = self::$spell['weaponSubClass'];
        self::$item['cat'][2][1][14] = self::main('parensFmt', [self::$item['cat'][2][1][14], self::$item['cat'][2][0]]);
        self::$main['moreTitles']['privilege'] = self::$privileges['_privileges'];

        self::$locale = $loc;
    }

    public static function getLocale() : Locale
    {
        return self::$locale;
    }

    public static function __callStatic(string $prop, ?array $args = []) : string|array|null
    {
        $vspfArgs = [];
        foreach ($args as $i => $arg)
        {
            if (!is_array($arg))
                continue;

            $vspfArgs = $arg;
            unset($args[$i]);
        }

        if (($x = self::exist($prop, ...$args)) !== null)
            return self::vspf($x, $vspfArgs);

        $dbt  = debug_backtrace()[0];
        $file = explode(DIRECTORY_SEPARATOR, $dbt['file']);
        trigger_error('Lang - undefined property Lang::$'.$prop.'[\''.implode('\'][\'', $args).'\'], called in '.array_pop($file).':'.$dbt['line'], E_USER_WARNING);

        return null;
    }

    public static function exist(string $prop, string ...$args) : string|array|null
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

    public static function concat(array $args, int $concat = self::CONCAT_AND, ?callable $callback = null) : string
    {
        $buff       = '';
        $callback ??= fn($x) => $x;

        reset($args);

        if (count($args) < 2)
            return $args ? $callback(current($args), key($args)) : '';

        do
        {
            $item = $callback(current($args), key($args));
            $arg  = next($args);

            if (!$buff)
                $buff .= $item;
            else if ($arg !== false || $concat == self::CONCAT_NONE)
                $buff .= self::main('comma').$item;
            else if ($concat == self::CONCAT_AND)
                $buff .= self::main('and').$item;
            else
                $buff .= self::main('or').$item;
        }
        while ($arg !== false);

        return $buff;
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

        $separator = match ($fmt)
        {
            self::FMT_HTML   => '<br />',
            self::FMT_MARKUP => '[br]',
            self::FMT_RAW    => "\n",
            default          => "\n"
        };

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
    public static function getInfoBoxForFlags(int $cuFlags) : array
    {
        $tmp = [];

        if ($cuFlags & CUSTOM_DISABLED)
            $tmp[] = '[tooltip name=disabledHint]'.self::main('disabledHint').'[/tooltip][span class=tip tooltip=disabledHint]'.self::main('disabled').'[/span]';

        if ($cuFlags & CUSTOM_SERVERSIDE)
            $tmp[] = '[tooltip name=serversideHint]'.self::main('serversideHint').'[/tooltip][span class=tip tooltip=serversideHint]'.self::main('serverside').'[/span]';

        if ($cuFlags & CUSTOM_UNAVAILABLE)
            $tmp[] = self::main('unavailable');

        if ($cuFlags & CUSTOM_EXCLUDE_FOR_LISTVIEW && User::isInGroup(U_GROUP_STAFF))
            $tmp[] = '[tooltip name=excludedHint]This entry is excluded from lists and is not searchable.[/tooltip][span tooltip=excludedHint class="tip q10"]Hidden[/span]';

        return $tmp;
    }

    public static function getLocks(int $lockId, ?array &$ids = [], bool $interactive = false, int $fmt = self::FMT_HTML) : array
    {
        $locks = [];
        $ids   = [];
        $lock  = DB::Aowow()->selectRow('SELECT * FROM ::lock WHERE `id` = %i', $lockId);
        if (!$lock)
            return $locks;

        for ($i = 1; $i <= 5; $i++)
        {
            $prop = $lock['properties'.$i];
            $rank = $lock['reqSkill'.$i];
            $name = '';

            switch ($lock['type'.$i])
            {
                case LOCK_TYPE_ITEM:
                    $name = ItemList::getName($prop);
                    if (!$name)
                        continue 2;

                    if ($fmt == self::FMT_HTML)
                        $name = $interactive ? '<a class="q1" href="?item='.$prop.'">'.$name.'</a>' : '<span class="q1">'.$name.'</span>';
                    else if ($interactive && $fmt == self::FMT_MARKUP)
                    {
                        $name = '[item='.$prop.']';
                        $ids[Type::ITEM][] = $prop;
                    }

                    break;
                case LOCK_TYPE_SKILL:
                    $name = self::spell('lockType', $prop);
                    if (!$name)
                        continue 2;

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
                            $name = SkillList::getName($prop);

                        if ($rank > 0)
                            $name = self::main('parensFmt', [$name, $rank]);
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
                    }
                    // exclude unusual stuff
                    else if (User::isInGroup(U_GROUP_STAFF))
                    {
                        if ($rank > 0)
                            $name = self::main('parensFmt', [$name, $rank]);
                    }
                    else
                        continue 2;
                    break;
                case LOCK_TYPE_SPELL:
                    $name = SpellList::getName($prop);
                    if (!$name)
                        continue 2;

                    if ($fmt == self::FMT_HTML)
                        $name = $interactive ? '<a class="q1" href="?spell='.$prop.'">'.$name.'</a>' : '<span class="q1">'.$name.'</span>';
                    else if ($interactive && $fmt == self::FMT_MARKUP)
                    {
                        $name = '[spell='.$prop.']';
                        $ids[Type::SPELL][] = $prop;
                    }

                    break;
                default:
                    continue 2;
            }

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

        $strs = self::spell($class == ITEM_CLASS_ARMOR ? 'armorSubClass' : 'weaponSubClass');
        $tmp  = array_intersect_key($strs, Util::mask2bits($mask));

        if (!$tmp && $class == ITEM_CLASS_ARMOR)
            return self::spell('cat', -11, 8);
        else if (!$tmp && $class == ITEM_CLASS_WEAPON)
            return self::spell('cat', -11, 6);
        else
            return implode(self::main('comma'), $tmp);
    }

    public static function getStances(int $stanceMask) : string
    {
        $stanceMask &= 0xFF37F6FF;                          // clamp to available stances/forms..

        return implode(self::main('comma'), array_intersect_key(self::game('st'), Util::mask2bits($stanceMask, 1)));
    }

    public static function getMagicSchools(int $schoolMask, bool $short = false) : string
    {
        $schoolMask &= SPELL_ALL_SCHOOLS;                   // clamp to available schools..

        if ($short && $schoolMask == SPELL_ALL_SCHOOLS)
            return self::main('all');

        if ($short && $schoolMask == SPELL_MAGIC_SCHOOLS)
            return self::main('parensFmt', [self::main('all'), self::game('dt', 1)]);;

        return implode(self::main('comma'), array_intersect_key(self::game('sc'), Util::mask2bits($schoolMask)));
    }

    public static function getClassString(int $classMask, array &$ids = [], int $fmt = self::FMT_HTML) : string
    {
        $classMask &= ChrClass::MASK_ALL;                   // clamp to available classes..

        if (!$classMask || $classMask == ChrClass::MASK_ALL)// available to all classes
            return '';

        [$base, $br] = match ($fmt)
        {
            self::FMT_HTML   => ['<a href="?class=%1$d" class="c%1$d">%2$s</a>', ''],
            self::FMT_MARKUP => ['[class=%1$d]', '[br]'],
            self::FMT_RAW    => ['%2$s', ''],
            default          => ['%2$s', '']
        };

        $tmp = [];
        foreach (ChrClass::fromMask($classMask) as $c)
            $tmp[$c] = (!fMod(count($tmp) + 1, 3) ? $br : '').sprintf($base, $c, self::game('cl', $c));

        $ids = array_keys($tmp);

        return implode(self::main('comma'), $tmp);
    }

    public static function getRaceString(int $raceMask, array &$ids = [], int $fmt = self::FMT_HTML) : string
    {
        $raceMask &= ChrRace::MASK_ALL;                     // clamp to available races..

        if (!$raceMask || $raceMask == ChrRace::MASK_ALL)   // available to all races (we don't display 'both factions')
            return '';

        if ($raceMask == ChrRace::MASK_HORDE)
            return self::game('ra', -2);

        if ($raceMask == ChrRace::MASK_ALLIANCE)
            return self::game('ra', -1);

        [$base, $br] = match ($fmt)
        {
            self::FMT_HTML   => ['<a href="?race=%1$d" class="q1">%2$s</a>', ''],
            self::FMT_MARKUP => ['[race=%1$d]', '[br]'],
            self::FMT_RAW    => ['%2$s', ''],
            default          => ['%2$s', '']
        };

        $tmp = [];
        foreach (ChrRace::fromMask($raceMask) as $r)
            $tmp[$r] = (!fMod(count($tmp) + 1, 3) ? $br : '').sprintf($base, $r, self::game('ra', $r));

        $ids = array_keys($tmp);

        return implode(self::main('comma'), $tmp);
    }

    public static function formatSkillBreakpoints(array $bp, int $fmt = self::FMT_MARKUP) : string
    {
        $tmp = self::game('difficulty');

        $base = match ($fmt)
        {
            self::FMT_HTML   => '<span class="r%1$d">%2$s</span> ',
            self::FMT_MARKUP => '[color=r%1$d]%2$s[/color] ',
            self::FMT_RAW    => '%2$s ',
            default          => '%2$s '
        };

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

        $time   = DateTime::parse($msec);                   // [$ms, $s, $m, $h, $d]
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

        for ($i = 4; $i > 0; $i--)
        {
            $total += $time[$i];
            if (isset($ref[$i]) && ($total || ($i == 1 && !$result)))
            {
                if (!$concat)
                    return self::vspf($ref[$i], [$total + ($time[$i-1] ?? 0) / $mult[$i]]);

                $result[] = self::vspf($ref[$i], [$total]);
                $total = 0;
            }
            else
                $total *= $mult[$i];
        }

        return implode(self::main('comma'), $result);
    }

    private static function vspf(null|array|string $var, array $args = []) : null|array|string
    {
        if (is_array($var))
        {
            foreach ($var as &$v)
                $v = self::vspf($v, $args);

            return $var;
        }

        if (!$var)                                          // may be null or empty. Handled differently depending on context
            return $var;

        $var = Cfg::applyToString($var);

        if ($args)
            $var = vsprintf($var, $args);

        return UIText::unescapeUISequences($var);
    }
}

?>
