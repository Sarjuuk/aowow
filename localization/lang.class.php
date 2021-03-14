<?php

class Lang
{
    private static $timeUnits;
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

    private static $emote;
    private static $enchantment;

    public static function load($loc)
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

        // not localized .. for whatever reason
        self::$profiler['regions'] = array(
            'eu' => "Europe",
            'us' => "US & Oceanic"
        );

        self::$main['moreTitles']['privilege'] = self::$privileges['_privileges'];
    }

    public static function __callStatic($prop, $args)
    {
        if (!isset(self::$$prop))
        {
            $dbt  = debug_backtrace()[0];
            $file = explode(DIRECTORY_SEPARATOR, $dbt['file']);
            trigger_error('Lang - tried to use undefined property Lang::$'.$prop.', called in '.array_pop($file).':'.$dbt['line'], E_USER_WARNING);
            return null;
        }

        $vspfArgs = [];

        $var = self::$$prop;
        foreach ($args as $arg)
        {
            if (is_array($arg))
            {
                $vspfArgs = $arg;
                continue;
            }
            else if (!isset($var[$arg]))
            {
                $dbt  = debug_backtrace()[0];
                $file = explode(DIRECTORY_SEPARATOR, $dbt['file']);
                trigger_error('Lang - undefined property Lang::$'.$prop.'[\''.implode('\'][\'', $args).'\'], called in '.array_pop($file).':'.$dbt['line'], E_USER_WARNING);
                return null;
            }

            $var = $var[$arg];
        }

        // meh :x
        if ($var === null && $prop == 'spell' && count($args) == 1)
        {
            if ($args[0] == 'effects')
                $var = self::$$prop['unkEffect'];
            else if ($args[0] == 'auras')
                $var = self::$$prop['unkAura'];
        }

        return self::vspf($var, $vspfArgs);
    }

    public static function concat($args, $useAnd = true, $callback = null)
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
                $b .= Lang::main($useAnd ? 'and' : 'or');

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

        if ($len > 0 && mb_strlen($text) > $len)
        {
            $n = 0;
            $b = [];
            $parts = explode(' ', $text);
            while ($n < $len && $parts)
            {
                $_   = array_shift($parts);
                $n  += mb_strlen($_);
                $b[] = $_;
            }

            $text = implode(' ', $b).'…';
        }

        return $text;
    }

    // add line breaks to string after X chars. If X is inside a word break behind it.
    public static function breakTextClean(string $text, int $len = 30, bool $asHTML = true) : string
    {
        // remove line breaks
        $text = strtr($text, ["\n" => ' ', "\r" => ' ']);

        // limit whitespaces to one at a time
        $text = preg_replace('/\s+/', ' ', trim($text));

        $row = [];
        if ($len > 0 && mb_strlen($text) > $len)
        {
            $i = 0;
            $n = 0;
            $parts = explode(' ', $text);
            foreach ($parts as $p)
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
        }

        return implode($asHTML ? '<br />' : '[br]', $row);
    }

    public static function sort($prop, $group, $method = SORT_NATURAL)
    {

        if (!isset(self::$$prop))
        {
            trigger_error('Lang::sort - tried to use undefined property Lang::$'.$prop, E_USER_WARNING);
            return null;
        }

        $var = &self::$$prop;
        if (!isset($var[$group]))
        {
            trigger_error('Lang::sort - tried to use undefined property Lang::$'.$prop.'[\''.$group.'\']', E_USER_WARNING);
            return null;
        }

        asort($var[$group], $method);
    }

    // todo: expand
    public static function getInfoBoxForFlags($flags)
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

    public static function getLocks($lockId, $interactive = false)
    {
        $locks = [];
        $lock  = DB::Aowow()->selectRow('SELECT * FROM ?_lock WHERE id = ?d', $lockId);
        if (!$lock)
            return $locks;

        for ($i = 1; $i <= 5; $i++)
        {
            $prop = $lock['properties'.$i];
            $rank = $lock['reqSkill'.$i];
            $name = '';

            if ($lock['type'.$i] == 1)                      // opened by item
            {
                $name = ItemList::getName($prop);
                if (!$name)
                    continue;

                if ($interactive)
                    $name = '<a class="q1" href="?item='.$prop.'">'.$name.'</a>';
            }
            else if ($lock['type'.$i] == 2)                 // opened by skill
            {
                // exclude unusual stuff
                if (!in_array($prop, [1, 2, 3, 4, 9, 16, 20]))
                    continue;

                $name = self::spell('lockType', $prop);
                if (!$name)
                    continue;

                if ($interactive)
                {
                    $skill = 0;
                    switch ($prop)
                    {
                        case  1: $skill = 633; break;       // Lockpicking
                        case  2: $skill = 182; break;       // Herbing
                        case  3: $skill = 186; break;       // Mining
                        case 20: $skill = 773; break;       // Scribing
                    }

                    if ($skill)
                        $name = '<a href="?skill='.$skill.'">'.$name.'</a>';
                }

                if ($rank > 0)
                    $name .= ' ('.$rank.')';
            }
            else
                continue;

            $locks[$lock['type'.$i] == 1 ? $prop : -$prop] = sprintf(self::game('requires'), $name);
        }

        return $locks;
    }

    public static function getReputationLevelForPoints($pts)
    {
        $_ = Game::getReputationLevelForPoints($pts);

        return self::game('rep', $_);
    }

    public static function getRequiredItems($class, $mask, $short = true)
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

    public static function getStances($stanceMask)
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

    public static function getMagicSchools($schoolMask)
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

    public static function getClassString(int $classMask, array &$ids = [], bool $asHTML = true) : string
    {
        $classMask &= CLASS_MASK_ALL;                       // clamp to available classes..

        if ($classMask == CLASS_MASK_ALL)                   // available to all classes
            return false;

        $tmp  = [];
        $i    = 1;
        $base = $asHTML ? '<a href="?class=%d" class="c%1$d">%2$s</a>' : '[class=%d]';
        $br   = $asHTML ? '' : '[br]';

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

    public static function getRaceString(int $raceMask, array &$ids = [], bool $asHTML = true) : string
    {
        $raceMask &= RACE_MASK_ALL;                         // clamp to available races..

        if ($raceMask == RACE_MASK_ALL)                     // available to all races (we don't display 'both factions')
            return false;

        if (!$raceMask)
            return false;

        $tmp  = [];
        $i    = 1;
        $base = $asHTML ? '<a href="?race=%d" class="q1">%s</a>' : '[race=%d]';
        $br   = $asHTML ? '' : '[br]';

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

    public static function formatSkillBreakpoints(array $bp, bool $html = false) : string
    {
        $tmp = Lang::game('difficulty').Lang::main('colon');

        for ($i = 0; $i < 4; $i++)
            if (!empty($bp[$i]))
                $tmp .= $html ? '<span class="r'.($i + 1).'">'.$bp[$i].'</span> ' : '[color=r'.($i + 1).']'.$bp[$i].'[/color] ';

        return trim($tmp);
    }

    public static function nf($number, $decimals = 0, $no1k = false)
    {
        //               [decimal, thousand]
        $seps = array(
            LOCALE_EN => [',', '.'],
            LOCALE_FR => [' ', ','],
            LOCALE_DE => ['.', ','],
            LOCALE_CN => [',', '.'],
            LOCALE_ES => ['.', ','],
            LOCALE_RU => [' ', ',']
        );

        return number_format($number, $decimals, $seps[User::$localeId][1], $no1k ? '' : $seps[User::$localeId][0]);
    }

    private static function vspf($var, $args)
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
