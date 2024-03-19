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

    private static $locId;
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

    public static function load(int $locale) : void
    {
        if (!isset(Util::$localeStrings[$locale]))
            die($locale.' is not a known locale!');
        if (!file_exists('localization/locale_'.Util::$localeStrings[$locale].'.php'))
            die('File for locale '.$locale.' not found.');
        else
            require 'localization/locale_'.Util::$localeStrings[$locale].'.php';

        foreach ($lang as $k => $v)
            self::$$k = $v;

        // *cough* .. reuse-hacks (because copy-pastaing text for 5 locales sucks)
        self::$item['cat'][2] = [self::$item['cat'][2], self::$spell['weaponSubClass']];
        self::$item['cat'][2][1][14] .= ' ('.self::$item['cat'][2][0].')';
        self::$main['moreTitles']['privilege'] = self::$privileges['_privileges'];

        self::$locId = $locale;
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

        if (($x = self::exist($prop, ...$args)) !== null)
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

        if (!$var)                                          // may be null or empty. Handled differently depending on context
            return $var;

        if ($args)
            $var = vsprintf($var, $args);

        return self::unescapeUISequences($var);
    }

    /* Quoted from WoWWiki - UI Escape Sequences (https://wowwiki-archive.fandom.com/wiki/UI_escape_sequences)
     * number |1singular;plural;
           Will choose a word depending on whether the digit preceding it is 0/1 or not (i.e. 1,11,21 return the first string, as will 0,10,40). Note that unlike |4 singular and plural forms are separated by semi-colon.

     * |2text
           Before vowels outputs d' (with apostrophe) and removes any leading spaces from text, otherwise outputs de (with trailing space)

     * |3-formid(text)
           Displays text declined to the specified form (index ranges from 1 to GetNumDeclensionSets()).

     * number |4singular:plural; -or- number |4singular:plural1:plural2;
           Will choose a form based on the number preceding it. More than two forms (separated by colons) may be required by locale 8 (ruRU).
    **/

    public static function unescapeUISequences(string $var, int $fmt = -1) : string
    {
        if (strpos($var, '|') === false)
            return $var;

        // line break                   |n
        $var = preg_replace_callback('/\|n/i', function ($m) use ($fmt)
            {
                switch ($fmt)
                {
                    case -1:                                // default Lang::vspf case
                    case self::FMT_HTML:
                        return '<br />';
                    case self::FMT_MARKUP:
                        return '[br]';
                    case self::FMT_RAW:
                    default:
                        return '';
                }
            } , $var);

        // color                        |c<aarrggbb><word>|r
        $var = preg_replace_callback('/\|c([[:xdigit:]]{2})([[:xdigit:]]{6})(.+?)\|r/is', function ($m) use ($fmt)
            {
                [$_, $a, $rgb, $text] = $m;

                switch ($fmt)
                {
                    case -1:                                // default Lang::vspf case
                    case self::FMT_HTML:
                        return sprintf('<span style="color: #%1s%2s;">%3s</span>', $rgb, $a, $text);
                    case self::FMT_MARKUP:
                        return sprintf('[span color=#%1s]%3s[/span]', $rgb, $text); // doesn't support alpha
                    case self::FMT_RAW:
                    default:
                        return $text;
                }
            }, $var);

        // icon                         |T<imgPath+File.blp>:0:0:0:-1|t
        $var = preg_replace_callback('/\|T([\w]+\\\)*([^\.:]+)(?:\.[bB][lL][pP])?:([^\|]+)\|t/', function ($m) use ($fmt)
            {
                /* iconParam - size1, size2, xoffset, yoffset
                    size1 == 0; size2 omitted: Width = Height = TextHeight (always square!)
                    size1 > 0;  size2 omitted: Width = Height = size1 (always square!)
                    size1 == 0; size2 == 0   : Width = Height = TextHeight (always square!)
                    size1 > 0;  size2 == 0   : Width = TextHeight; Height = size1 (size1 is height!!!)
                    size1 == 0; size2 > 0    : Width = size2 * TextHeight; Height = TextHeight (size2 is an aspect ratio and defines width!!!)
                    size1 > 0;  size2 > 0    : Width = size1; Height = size2
                */

                [$_, $iconPath, $iconName, $iconParam] = $m;

                switch ($fmt)
                {
                    case self::FMT_HTML:
                        return '<span class="icontiny" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/'.Util::lower($iconName).'.gif)">';
                    case self::FMT_MARKUP:
                        return '[icon name='.Util::lower($iconName).']';
                    case self::FMT_RAW:
                    default:
                        return '';
                }
            }, $var);

        // hyperlink                    |H<hyperlinkStruct>|h<name>|h
        $var = preg_replace_callback('/\|H([^:]+):([^\|]+)\|h([^\|]+)\|h/i', function ($m) use ($fmt)
            {
                /*  type            Params
                    |Hchannel       channelName, channelname == CHANNEL ? channelNr : null
                    |Hachievement   AchievementID, PlayerGUID, isComplete, Month, Day, Year, criteriaMask1, criteriaMask2, criteriaMask3, criteriaMask4 - 32bit masks of Achievement_criteria.dbc/UIOrder only for achievements that display a todo list
                    |Hquest         QuestID, QuestLevel
                    |Hitem          itemId enchantId gemId1 gemId2 gemId3 gemId4 suffixId uniqueId linkLevel
                    |Henchant       SpellID (from craftwindow)
                    |Htalent        TalentID, TalentRank
                    |Hspell         SpellID, PlayerLevel?
                    |Htrade         SpellID, curSkill, maxSkill, PlayerGUID, base64_encode(known recipes bitmask)
                    |Hplayer        Name
                    |Hunit          GUID    ?               -  combatlog
                    |Hicon          ?   "source"|"dest"     -  combatlog
                    |Haction        ?                       -  combatlog
                */

                [$_, $linkType, $linkVars, $text] = $m;

                $linkVars = explode(':', $linkVars);

                $spfVars = ['', $linkVars[0], $text];

                switch ($linkType)
                {
                    case 'trade':
                    case 'enchant':
                        $linkType = 'spell';
                    case 'achievement':                             // markdown COULD implement completed status
                    case 'quest':
                    case 'item':                                    // markdown COULD implement enchantments/gems
                    case 'spell':
                        $spfVars[0] = $linkType;
                        break;
                    case 'talent':
                        if ($spell = DB::Aowow()->selectCell('SELECT `spell` FROM ?_talents WHERE `id` = ?d AND `rank` = ?d', $linkVars[0], $linkVars[1]))
                        {
                            $spfVars[0] = 'spell';
                            $spfVars[1] = $spell;
                            break;
                        }
                    default:
                        return '';
                }

                switch ($fmt)
                {
                    case self::FMT_HTML:
                        return sprintf('<a href="?%s=%d">%s</a>', $spfVars);
                    case self::FMT_MARKUP:
                        return sprintf('[%s=%d]', $spfVars);
                    case self::FMT_RAW:
                    default:
                        return sprintf('(%s #%d) %s', $spfVars);
                }
            }, $var);

        // |1 - digit singular/plural  <number> |1<singular;<plural>;
        $var = preg_replace_callback('/(\d+)\s*\|1([^;]+);([^;]+);/is', function ($m)
            {
                [$_, $num, $singular, $plural] = $m;

                switch ($num[-1])
                {
                    case 0:
                    case 1:
                        return $num . ' ' . $singular;
                    default:
                        return $num . ' ' . $plural;
                }
            }, $var);

        // |2 - frFR preposition: de    |2 <word>
        $var = preg_replace_callback('/\|2\s?(\w)/i', function ($m)
            {
                [$_, $word] = $m;

                switch (strtolower($word[1]))
                {
                    case 'h':
                        if (self::$locId != LOCALE_FR)
                            return 'de ' . $word;
                    case 'a':
                    case 'e':
                    case 'i':
                    case 'o':
                    case 'u':
                        return "d'" . $word;
                    default:
                        return 'de ' . $word;
                }
            }, $var);

        // |3 - ruRU declinations       |3-<caseIdx>(<word>)
        $var = preg_replace_callback('/\|3-(\d)\(([^\)]+)\)/iu', function ($m)
            {
                [$_, $caseIdx, $word] = $m;

                if ($caseIdx > 11 || $caseIdx < 1)          // max caseIdx seen in DeclinedWordCases.dbc
                    return $word;

                if (preg_match('/\P{Cyrillic}/iu', $word))  // not in cyrillic script
                    return $word;

                if ($declWord = DB::Aowow()->selectCell('SELECT dwc.word FROM ?_declinedwordcases dwc JOIN ?_declinedword dc ON dwc.wordId = dc.id WHERE dwc.caseIdx = ?d AND dc.word = ?', $caseIdx, $word))
                    return $declWord;

                return $word;
            }, $var);

        // |4 - numeric switch          <number>           |4<singular>:<plural>[:<plural2>];
        $var = preg_replace_callback('/([\d\.\,]+)([^\d]*)\|4([^:]*):([^:;]+)(?::([^;]+))?;/is', function ($m)
            {
                [$_, $num, $pad, $singular, $plural1, $plural2] = array_pad($m, 6, null);

                if (self::$locId != LOCALE_RU || !$plural2)
                    return $num . $pad . ($num == 1 ? $singular : $plural1);

                // singular - ends in 1, but not teen number
                if ($num[-1] == 1 && $num != 11)
                    return $num . $pad . $singular;

                // genitive singular - ends in 2, 3, 4, but not teen number
                if (($num[-1] == 2 && $num != 12) || ($num[-1] == 3 && $num != 13) || ($num[-1] == 4 && $num != 14))
                    return $num . $pad . $plural1;

                // genitive plural - everything else
                return $num . $pad . $plural2;
            }, $var);

        return $var;
    }
}

?>
