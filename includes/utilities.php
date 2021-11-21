<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SimpleXML extends SimpleXMLElement
{
    public function addCData(string $cData) : SimpleXMLElement
    {
        $node = dom_import_simplexml($this);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cData));

        return $this;
    }
}


class CLI
{
    const CHR_BELL      = 7;
    const CHR_BACK      = 8;
    const CHR_TAB       = 9;
    const CHR_LF        = 10;
    const CHR_CR        = 13;
    const CHR_ESC       = 27;
    const CHR_BACKSPACE = 127;

    const LOG_BLANK     = 0;
    const LOG_OK        = 1;
    const LOG_WARN      = 2;
    const LOG_ERROR     = 3;
    const LOG_INFO      = 4;

    private static $logHandle   = null;
    private static $hasReadline = null;


    /********************/
    /* formatted output */
    /********************/

    public static function writeTable(array $out, bool $timestamp = false) : void
    {
        if (!$out)
            return;

        $pads  = [];
        $nCols = 0;

        foreach ($out as $i => $row)
        {
            if (!is_array($out[0]))
            {
                unset($out[$i]);
                continue;
            }

            if (!$nCols)
                $nCols = count($row);

            for ($j = 0; $j < $nCols - 1; $j++)             // don't pad last column
                $pads[$j] = max($pads[$j], mb_strlen($row[$j]));
        }
        self::write();

        foreach ($out as $row)
        {
            for ($i = 0; $i < $nCols - 1; $i++)             // don't pad last column
                $row[$i] = str_pad($row[$i], $pads[$i] + 2);

            self::write('  '.implode($row), -1, $timestamp);
        }

        self::write();
    }


    /***********/
    /* logging */
    /***********/

    public static function initLogFile(string $file = '') : void
    {
        if (!$file)
            return;

        $file = self::nicePath($file);
        if (!file_exists($file))
            self::$logHandle = fopen($file, 'w');
        else
        {
            $logFileParts = pathinfo($file);

            $i = 1;
            while (file_exists($logFileParts['dirname'].'/'.$logFileParts['filename'].$i.(isset($logFileParts['extension']) ? '.'.$logFileParts['extension'] : '')))
                $i++;

            $file = $logFileParts['dirname'].'/'.$logFileParts['filename'].$i.(isset($logFileParts['extension']) ? '.'.$logFileParts['extension'] : '');
            self::$logHandle = fopen($file, 'w');
        }
    }

    public static function red(string $str) : string
    {
        return OS_WIN ? $str : "\e[31m".$str."\e[0m";
    }

    public static function green(string $str) : string
    {
        return OS_WIN ? $str : "\e[32m".$str."\e[0m";
    }

    public static function yellow(string $str) : string
    {
        return OS_WIN ? $str : "\e[33m".$str."\e[0m";
    }

    public static function blue(string $str) : string
    {
        return OS_WIN ? $str : "\e[36m".$str."\e[0m";
    }

    public static function bold(string $str) : string
    {
        return OS_WIN ? $str : "\e[1m".$str."\e[0m";
    }

    public static function write(string $txt = '', int $lvl = self::LOG_BLANK, bool $timestamp = true) : void
    {
        $msg = '';
        if ($txt)
        {
            if ($timestamp)
                $msg = str_pad(date('H:i:s'), 10);

            switch ($lvl)
            {
                case self::LOG_ERROR:                       // red      critical error
                    $msg .= '['.self::red('ERR').']   ';
                    break;
                case self::LOG_WARN:                        // yellow   notice
                    $msg .= '['.self::yellow('WARN').']  ';
                    break;
                case self::LOG_OK:                          // green    success
                    $msg .= '['.self::green('OK').']    ';
                    break;
                case self::LOG_INFO:                        // blue     info
                    $msg .= '['.self::blue('INFO').']  ';
                    break;
                case self::LOG_BLANK:
                    $msg .= '        ';
                    break;
            }

            $msg .= $txt."\n";
        }
        else
            $msg = "\n";

        echo $msg;

        if (self::$logHandle)                               // remove highlights for logging
            fwrite(self::$logHandle, preg_replace(["/\e\[\d+m/", "/\e\[0m/"], '', $msg));

        flush();
    }

    public static function nicePath(string $fileOrPath, string ...$pathParts) : string
    {
        $path = '';

        if ($pathParts)
        {
            foreach ($pathParts as &$pp)
                $pp = trim($pp);

            $path .= implode(DIRECTORY_SEPARATOR, $pathParts);
        }

        $path .= ($path ? DIRECTORY_SEPARATOR : '').trim($fileOrPath);

        // remove quotes (from erronous user input)
        $path = str_replace(['"', "'"], ['', ''], $path);

        if (DIRECTORY_SEPARATOR == '/')                     // *nix
        {
            $path = str_replace('\\', '/', $path);
            $path = preg_replace('/\/+/i', '/', $path);
        }
        else if (DIRECTORY_SEPARATOR == '\\')               // win
        {
            $path = str_replace('/', '\\', $path);
            $path = preg_replace('/\\\\+/i', '\\', $path);
        }
        else
            CLI::write('Dafuq! Your directory separator is "'.DIRECTORY_SEPARATOR.'". Please report this!', CLI::LOG_ERROR);

        // resolve *nix home shorthand
        if (!OS_WIN)
        {
            if (preg_match('/^~(\w+)\/.*/i', $path, $m))
                $path = '/home/'.substr($path, 1);
            else if (substr($path, 0, 2) == '~/')
                $path = getenv('HOME').substr($path, 1);
            else if ($path[0] == DIRECTORY_SEPARATOR && substr($path, 0, 6) != '/home/')
                $path = substr($path, 1);
        }

        return $path;
    }


    /**************/
    /* read input */
    /**************/

    /*
        since the CLI on WIN ist not interactive, the following things have to be considered
        you do not receive keystrokes but whole strings upon pressing <Enter> (wich also appends a \r)
        as such <ESC> and probably other control chars can not be registered
        this also means, you can't hide input at all, least process it
    */

    public static function read(array &$fields, bool $singleChar = false) : bool
    {
        // first time set
        if (self::$hasReadline === null)
            self::$hasReadline = function_exists('readline_callback_handler_install');

        // prevent default output if able
        if (self::$hasReadline)
            readline_callback_handler_install('', function() { });

        foreach ($fields as $name => $data)
        {
            $vars = ['desc', 'isHidden', 'validPattern'];
            foreach ($vars as $idx => $v)
                $$v = isset($data[$idx]) ? $data[$idx] : false;

            $charBuff = '';

            if ($desc)
                echo "\n".$desc.": ";

            while (true) {
                $r = [STDIN];
                $w = $e = null;
                $n = stream_select($r, $w, $e, 200000);

                if ($n && in_array(STDIN, $r)) {
                    $char  = stream_get_contents(STDIN, 1);
                    $keyId = ord($char);

                    // ignore this one
                    if ($keyId == self::CHR_TAB)
                        continue;

                    // WIN sends \r\n as sequence, ignore one
                    if ($keyId == self::CHR_CR && OS_WIN)
                        continue;

                    // will not be send on WIN .. other ways of returning from setup? (besides ctrl + c)
                    if ($keyId == self::CHR_ESC)
                    {
                        echo chr(self::CHR_BELL);
                        return false;
                    }
                    else if ($keyId == self::CHR_BACKSPACE)
                    {
                        if (!$charBuff)
                            continue;

                        $charBuff = mb_substr($charBuff, 0, -1);
                        if (!$isHidden && self::$hasReadline)
                            echo chr(self::CHR_BACK)." ".chr(self::CHR_BACK);
                    }
                    else if ($keyId == self::CHR_LF)
                    {
                        $fields[$name] = $charBuff;
                        break;
                    }
                    else if (!$validPattern || preg_match($validPattern, $char))
                    {
                        $charBuff .= $char;
                        if (!$isHidden && self::$hasReadline)
                            echo $char;

                        if ($singleChar && self::$hasReadline)
                        {
                            $fields[$name] = $charBuff;
                            break;
                        }
                    }
                }
            }
        }

        echo chr(self::CHR_BELL);

        foreach ($fields as $f)
            if (strlen($f))
                return true;

        $fields = null;
        return true;
    }
}


class Util
{
    const FILE_ACCESS = 0777;

    const GEM_SCORE_BASE_WOTLK = 16;                        // rare quality wotlk gem score
    const GEM_SCORE_BASE_BC    = 8;                         // rare quality bc gem score

    private static $perfectGems             = null;

    public static $localeStrings            = array(        // zero-indexed
        'enus',         null,           'frfr',         'dede',         'zhcn',         null,           'eses',         null,           'ruru'
    );

    public static $subDomains               = array(
        'www',          null,           'fr',           'de',           'cn',           null,           'es',           null,           'ru'
    );

    public static $typeClasses              = array(
        null,               'CreatureList',     'GameObjectList',   'ItemList',         'ItemsetList',      'QuestList',        'SpellList',
        'ZoneList',         'FactionList',      'PetList',          'AchievementList',  'TitleList',        'WorldEventList',   'CharClassList',
        'CharRaceList',     'SkillList',        null,               'CurrencyList',     null,               'SoundList',
        TYPE_ICON        => 'IconList',
        TYPE_EMOTE       => 'EmoteList',
        TYPE_ENCHANTMENT => 'EnchantmentList',
        TYPE_AREATRIGGER => 'AreatriggerList',
        TYPE_MAIL        => 'MailList'
    );

    public static $typeStrings              = array(        // zero-indexed
        null,           'npc',          'object',       'item',         'itemset',      'quest',        'spell',        'zone',         'faction',
        'pet',          'achievement',  'title',        'event',        'class',        'race',         'skill',        null,           'currency',
        null,           'sound',
        TYPE_ICON        => 'icon',
        TYPE_USER        => 'user',
        TYPE_EMOTE       => 'emote',
        TYPE_ENCHANTMENT => 'enchantment',
        TYPE_AREATRIGGER => 'areatrigger',
        TYPE_MAIL        => 'mail'
    );

    # todo (high): find a sensible way to write data here on setup
    private static $gtCombatRatings         = array(
        12 => 1.5,      13 => 13.8,     14 => 13.8,     15 => 5,        16 => 10,       17 => 10,       18 => 8,        19 => 14,       20 => 14,
        21 => 14,       22 => 10,       23 => 10,       24 => 8,        25 => 0,        26 => 0,        27 => 0,        28 => 10,       29 => 10,
        30 => 10,       31 => 10,       32 => 14,       33 => 0,        34 => 0,        35 => 28.75,    36 => 10,       37 => 2.5,      44 => 4.268292513760655
    );

    public static $itemFilter               = array(
         20 => 'str',                21 => 'agi',                23 => 'int',                22 => 'sta',                24 => 'spi',                25 => 'arcres',             26 => 'firres',             27 => 'natres',
         28 => 'frores',             29 => 'shares',             30 => 'holres',             37 => 'mleatkpwr',          32 => 'dps',                35 => 'damagetype',         33 => 'dmgmin1',            34 => 'dmgmax1',
         36 => 'speed',              38 => 'rgdatkpwr',          39 => 'rgdhitrtng',         40 => 'rgdcritstrkrtng',    41 => 'armor',              44 => 'blockrtng',          43 => 'block',              42 => 'defrtng',
         45 => 'dodgertng',          46 => 'parryrtng',          48 => 'splhitrtng',         49 => 'splcritstrkrtng',    50 => 'splheal',            51 => 'spldmg',             52 => 'arcsplpwr',          53 => 'firsplpwr',
         54 => 'frosplpwr',          55 => 'holsplpwr',          56 => 'natsplpwr',          60 => 'healthrgn',          61 => 'manargn',            57 => 'shasplpwr',          77 => 'atkpwr',             78 => 'mlehastertng',
         79 => 'resirtng',           84 => 'mlecritstrkrtng',    94 => 'splpen',             95 => 'mlehitrtng',         96 => 'critstrkrtng',       97 => 'feratkpwr',         100 => 'nsockets',          101 => 'rgdhastertng',
        102 => 'splhastertng',      103 => 'hastertng',         114 => 'armorpenrtng',      115 => 'health',            116 => 'mana',              117 => 'exprtng',           119 => 'hitrtng',           123 => 'splpwr',
        134 => 'mledps',            135 => 'mledmgmin',         136 => 'mledmgmax',         137 => 'mlespeed',          138 => 'rgddps',            139 => 'rgddmgmin',         140 => 'rgddmgmax',         141 => 'rgdspeed'
    );

    public static $ssdMaskFields            = array(
        'shoulderMultiplier',           'trinketMultiplier',            'weaponMultiplier',             'primBudged',
        'rangedMultiplier',             'clothShoulderArmor',           'leatherShoulderArmor',         'mailShoulderArmor',
        'plateShoulderArmor',           'weaponDPS1H',                  'weaponDPS2H',                  'casterDPS1H',
        'casterDPS2H',                  'rangedDPS',                    'wandDPS',                      'spellPower',
        null,                           null,                           'tertBudged',                   'clothCloakArmor',
        'clothChestArmor',              'leatherChestArmor',            'mailChestArmor',               'plateChestArmor'
    );

    public static $weightScales             = array(
        'agi',             'int',             'sta',          'spi',          'str',       'health',          'mana',         'healthrgn', 'manargn',
        'armor',           'blockrtng',       'block',        'defrtng',      'dodgertng', 'parryrtng',       'resirtng',
        'atkpwr',          'feratkpwr',       'armorpenrtng', 'critstrkrtng', 'exprtng',   'hastertng',       'hitrtng',      'splpen',
        'splpwr',          'arcsplpwr',       'firsplpwr',    'frosplpwr',    'holsplpwr', 'natsplpwr',       'shasplpwr',
        'dmg',             'mledps',          'rgddps',       'mledmgmin',    'rgddmgmin', 'mledmgmax',       'rgddmgmax',    'mlespeed',  'rgdspeed',
        'arcres',          'firres',          'frores',       'holres',       'natres',    'shares',
        'mleatkpwr',       'mlecritstrkrtng', 'mlehastertng', 'mlehitrtng',   'rgdatkpwr', 'rgdcritstrkrtng', 'rgdhastertng', 'rgdhitrtng',
        'splcritstrkrtng', 'splhastertng',    'splhitrtng',   'spldmg',       'splheal',
        'nsockets'
    );

    public static $dateFormatInternal       = "Y/m/d H:i:s";

    public static $changeLevelString        = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_staticTooltipLevelClick(this, null, 0)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><!--lvl-->%s</a>';

    public static $setRatingLevelString     = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_setRatingLevel(this, %s, %s, %s)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">%s</a>';

    public static $filterResultString       = '$$WH.sprintf(LANG.lvnote_filterresults, \'%s\')';
    public static $tryFilteringString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryFilteringEntityString = '$$WH.sprintf(LANG.lvnote_entitiesfound, %s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryNarrowingString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_trynarrowing';

    public static $dfnString                = '<dfn title="%s" class="w">%s</dfn>';

    public static $mapSelectorString        = '<a href="javascript:;" onclick="myMapper.update({zone: %d}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">%s</a>&nbsp;(%d)';

    public static $expansionString          = array(        // 3 & 4 unused .. obviously
        null,           'bc',           'wotlk',            'cata',                'mop'
    );

    public static $bgImagePath              = array (
        'tiny'   => 'style="background-image: url(%s/images/wow/icons/tiny/%s.gif)"',
        'small'  => 'style="background-image: url(%s/images/wow/icons/small/%s.jpg)"',
        'medium' => 'style="background-image: url(%s/images/wow/icons/medium/%s.jpg)"',
        'large'  => 'style="background-image: url(%s/images/wow/icons/large/%s.jpg)"',
    );

    public static $configCats               = array(        // don't mind the ordering ... please?
        1 => 'Site', 'Caching', 'Account', 'Session', 'Site Reputation', 'Google Analytics', 'Profiler', 0 => 'Other'
    );

    public static $tcEncoding               = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789';
    public static $wowheadLink              = '';
    private static $notes                   = [];

    public static function addNote(int $uGroupMask, string $str) : void
    {
        self::$notes[] = [$uGroupMask, $str];
    }

    public static function getNotes() : array
    {
        $notes = [];

        foreach (self::$notes as $data)
            if (!$data[0] || User::isInGroup($data[0]))
                $notes[] = $data[1];

        return $notes;
    }

    private static $execTime = 0.0;

    public static function execTime(bool $set = false) : string
    {
        if ($set)
        {
            self::$execTime = microTime(true);
            return '';
        }

        if (!self::$execTime)
            return '';

        $newTime        = microTime(true);
        $tDiff          = $newTime - self::$execTime;
        self::$execTime = $newTime;

        return self::formatTime($tDiff * 1000, true);
    }

    public static function formatMoney(int $qty) : string
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

    private static function parseTime(int $msec) : array
    {
        $time = [0, 0, 0, 0, 0];

        if ($_ = ($msec % 1000))
            $time[0] = $_;

        $sec = $msec / 1000;

        if ($sec >= 3600 * 24)
        {
            $time[4] = floor($sec / 3600 / 24);
            $sec -= $time[4] * 3600 * 24;
        }

        if ($sec >= 3600)
        {
            $time[3] = floor($sec / 3600);
            $sec -= $time[3] * 3600;
        }

        if ($sec >= 60)
        {
            $time[2] = floor($sec / 60);
            $sec -= $time[2] * 60;
        }

        if ($sec > 0)
        {
            $time[1] = (int)$sec;
            $sec -= $time[1];
        }

        return $time;
    }

    public static function formatTime(int $msec, bool $short = false) : string
    {
        [$ms, $s, $m, $h, $d] = self::parseTime(abs($msec));

        if ($short)
        {
            if ($_ = round($d / 364))
                return $_." ".Lang::timeUnits('ab', 0);
            if ($_ = round($d / 30))
                return $_." ".Lang::timeUnits('ab', 1);
            if ($_ = round($d / 7))
                return $_." ".Lang::timeUnits('ab', 2);
            if ($_ = round($d))
                return $_." ".Lang::timeUnits('ab', 3);
            if ($_ = round($h))
                return $_." ".Lang::timeUnits('ab', 4);
            if ($_ = round($m))
                return $_." ".Lang::timeUnits('ab', 5);
            if ($_ = round($s + $ms / 1000, 2))
                return $_." ".Lang::timeUnits('ab', 6);
            if ($ms)
                return $ms." ".Lang::timeUnits('ab', 7);

            return '0 '.Lang::timeUnits('ab', 6);
        }
        else
        {
            $_ = $d + $h / 24;
            if ($_ > 1 && !($_ % 364))                      // whole years
                return round(($d + $h / 24) / 364, 2)." ".Lang::timeUnits($d / 364 == 1 && !$h ? 'sg' : 'pl', 0);
            if ($_ > 1 && !($_ % 30))                       // whole month
                return round(($d + $h / 24) /  30, 2)." ".Lang::timeUnits($d /  30 == 1 && !$h ? 'sg' : 'pl', 1);
            if ($_ > 1 && !($_ % 7))                        // whole weeks
                return round(($d + $h / 24) /   7, 2)." ".Lang::timeUnits($d /   7 == 1 && !$h ? 'sg' : 'pl', 2);
            if ($d)
                return round($d + $h  /   24, 2)." ".Lang::timeUnits($d == 1 && !$h  ? 'sg' : 'pl', 3);
            if ($h)
                return round($h + $m  /   60, 2)." ".Lang::timeUnits($h == 1 && !$m  ? 'sg' : 'pl', 4);
            if ($m)
                return round($m + $s  /   60, 2)." ".Lang::timeUnits($m == 1 && !$s  ? 'sg' : 'pl', 5);
            if ($s)
                return round($s + $ms / 1000, 2)." ".Lang::timeUnits($s == 1 && !$ms ? 'sg' : 'pl', 6);
            if ($ms)
                return $ms." ".Lang::timeUnits($ms == 1 ? 'sg' : 'pl', 7);

            return '0 '.Lang::timeUnits('pl', 6);
        }
    }

    // pageText for Books (Item or GO) and questText
    public static function parseHtmlText(string $text, bool $markdown = false) : string
    {
        if (stristr($text, '<HTML>'))                       // text is basically a html-document with weird linebreak-syntax
        {
            $pairs = array(
                '<HTML>'    => '',
                '</HTML>'   => '',
                '<BODY>'    => '',
                '</BODY>'   => '',
                '<BR></BR>' => $markdown ? '[br]' : '<br />'
            );

            // html may contain 'Pictures' and FlavorImages and "stuff"
            $text = preg_replace_callback(
                '/src="([^"]+)"/i',
                function ($m) { return 'src="'.STATIC_URL.'/images/wow/'.strtr($m[1], ['\\' => '/']).'.png"'; },
                strtr($text, $pairs)
            );
        }
        else
            $text = strtr($text, ["\n" => $markdown ? '[br]' : '<br />', "\r" => '']);

        $from = array(
            '/\|T([\w]+\\\)*([^\.]+)\.blp:\d+\|t/ui',       // images (force size to tiny)                      |T<fullPath>:<size>|t
            '/\|c(\w{6})\w{2}([^\|]+)\|r/ui',               // color                                            |c<RRGGBBAA><text>|r
            '/\$g\s*([^:;]+)\s*:\s*([^:;]+)\s*(:?[^:;]*);/ui',// directed gender-reference                      $g:<male>:<female>:<refVariable>
            '/\$t([^;]+);/ui',                              // nonsense, that the client apparently ignores
            '/\|\d\-?\d?\((\$\w)\)/ui',                     // and another modifier for something russian       |3-6($r)
            '/<([^\"=\/>]+\s[^\"=\/>]+)>/ui',               // emotes (workaround: at least one whitespace and never " or = between brackets)
            '/\$(\d+)w/ui',                                 // worldState(?)-ref found on some pageTexts        $1234w
            '/\$c/i',                                       // class-ref
            '/\$r/i',                                       // race-ref
            '/\$n/i',                                       // name-ref
            '/\$b/i',                                       // line break
            '/\|n/i'                                        // what .. the fuck .. another type of line terminator? (only in spanish though)
        );

        $toMD = array(
            '[icon name=\2]',
            '[span color=#\1>\2[/span]',
            '<\1/\2>',
            '',
            '\1',
            '<\1>',
            '[span class=q0>WorldState #\1[/span]',
            '<'.Lang::game('class').'>',
            '<'.Lang::game('race').'>',
            '<'.Lang::main('name').'>',
            '[br]',
            ''
        );

        $toHTML = array(
            '<span class="icontiny" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/\2.gif)">',
            '<span style="color: #\1">\2</span>',
            '&lt;\1/\2&gt;',
            '',
            '\1',
            '&lt;\1&gt;',
            '<span class="q0">WorldState #\1</span>',
            '&lt;'.Lang::game('class').'&gt;',
            '&lt;'.Lang::game('race').'&gt;',
            '&lt;'.Lang::main('name').'&gt;',
            '<br />',
            ''
        );

        return preg_replace($from, $markdown ? $toMD : $toHTML, $text);
    }

    public static function asHex($val) : string
    {
        $_ = decHex($val);
        while (fMod(strLen($_), 4))                         // in 4-blocks
            $_ = '0'.$_;

        return '0x'.strToUpper($_);
    }

    public static function asBin($val) : string
    {
        $_ = decBin($val);
        while (fMod(strLen($_), 4))                         // in 4-blocks
            $_ = '0'.$_;

        return 'b'.strToUpper($_);
    }

    public static function htmlEscape($data)
    {
        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::htmlEscape($v);

            return $data;
        }

        return htmlspecialchars($data, ENT_QUOTES, 'utf-8');
    }

    public static function jsEscape($data)
    {
        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::jsEscape($v);

            return $data;
        }

        return strtr($data, array(
            '\\' => '\\\\',
            "'"  => "\\'",
            '"'  => '\\"',
            "\r" => '\\r',
            "\n" => '\\n'
        ));
    }

    public static function defStatic($data)
    {
        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::defStatic($v);

            return $data;
        }

        return strtr($data, array(
            '<script'    => '<scr"+"ipt',
            'script>'    => 'scr"+"ipt>',
            'HOST_URL'   => HOST_URL,
            'STATIC_URL' => STATIC_URL
        ));
    }

    // default back to enUS if localization unavailable
    public static function localizedString(array $data, string $field, bool $silent = false) : string
    {
        // only display placeholder markers for staff
        if (!User::isInGroup(U_GROUP_EMPLOYEE | U_GROUP_TESTER | U_GROUP_LOCALIZER))
            $silent = true;

        // default case: selected locale available
        if (!empty($data[$field.'_loc'.User::$localeId]))
            return $data[$field.'_loc'.User::$localeId];

        // locale not enUS; aowow-type localization available; add brackets if not silent
        else if (User::$localeId != LOCALE_EN && !empty($data[$field.'_loc0']))
            return $silent ? $data[$field.'_loc0'] : '['.$data[$field.'_loc0'].']';

        // locale not enUS; TC localization; add brackets if not silent
        else if (User::$localeId != LOCALE_EN && !empty($data[$field]))
            return $silent ? $data[$field] : '['.$data[$field].']';

        // locale enUS; TC localization; return normal
        else if (User::$localeId == LOCALE_EN && !empty($data[$field]))
            return $data[$field];

        // nothing to find; be empty
        else
            return '';
    }

    // for item and spells
    public static function setRatingLevel(int $level, int $type, int $val) : string
    {
        if (in_array($type, [ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_DODGE_RATING, ITEM_MOD_PARRY_RATING, ITEM_MOD_BLOCK_RATING, ITEM_MOD_RESILIENCE_RATING]) && $level < 34)
            $level = 34;

        if (!isset(self::$gtCombatRatings[$type]))
            $result = 0;
        else
        {
            if ($level > 70)
                $c = 82 / 52 * pow(131 / 63, ($level - 70) / 10);
            else if ($level > 60)
                $c = 82 / (262 - 3 * $level);
            else if ($level > 10)
                $c = ($level - 8) / 52;
            else
                $c = 2 / 52;

            // do not use localized number format here!
            $result = number_format($val / self::$gtCombatRatings[$type] / $c, 2);
        }

        if (!in_array($type, array(ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_EXPERTISE_RATING)))
            $result .= '%';

        return sprintf(Lang::item('ratingString'), '<!--rtg%'.$type.'-->'.$result, '<!--lvl-->'.$level);
    }

    public static function powerUseLocale($domain = 'www')
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
            User::useLocale(LOCALE_EN);
            Lang::load(User::$localeString);
        }
    }

    // default ucFirst doesn't convert UTF-8 chars
    public static function ucFirst($str)
    {
        $len   = mb_strlen($str) - 1;
        $first = mb_substr($str, 0, 1);
        $rest  = mb_substr($str, 1, $len);

        return mb_strtoupper($first).$rest;
    }

    public static function ucWords($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE);
    }

    public static function lower($str)
    {
        return mb_strtolower($str);
    }

    // note: valid integer > 32bit are returned as float
    public static function checkNumeric(&$data, $typeCast = NUM_ANY)
    {
        if ($data === null)
            return false;
        else if (!is_array($data))
        {
            $rawData = $data;                               // do not transform strings

            $data = trim($data);
            if (preg_match('/^-?\d*,\d+$/', $data))
                $data = strtr($data, ',', '.');

            if (is_numeric($data))
            {
                $data += 0;                                 // becomes float or int

                if ((is_float($data) && $typeCast == NUM_REQ_INT) ||
                    (is_int($data) && $typeCast == NUM_REQ_FLOAT))
                    return false;

                if (is_float($data) && $typeCast == NUM_CAST_INT)
                    $data = intval($data);

                if (is_int($data) && $typeCast == NUM_CAST_FLOAT)
                    $data = floatval($data);

                return true;
            }

            $data = $rawData;
            return false;
        }

        array_walk($data, function(&$x) use($typeCast) { self::checkNumeric($x, $typeCast); });

        return false;                                       // always false for passed arrays
    }

    public static function arraySumByKey(array &$ref, array ...$adds) : void
    {
        if (!$adds)
            return;

        foreach ($adds as $arr)
        {
            foreach ($arr as $k => $v)
            {
                if (!isset($ref[$k]))
                    $ref[$k] = 0;

                $ref[$k] += $v;
            }
        }
    }

    public static function isValidEmail($email)
    {
        return preg_match('/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i', $email);
    }

    public static function loadStaticFile($file, &$result, $localized = false)
    {
        $success = true;
        if ($localized)
        {
            if (file_exists('datasets/'.User::$localeString.'/'.$file))
                $result .= file_get_contents('datasets/'.User::$localeString.'/'.$file);
            else if (file_exists('datasets/enus/'.$file))
                $result .= file_get_contents('datasets/enus/'.$file);
            else
                $success = false;
        }
        else
        {
            if (file_exists('datasets/'.$file))
                $result .= file_get_contents('datasets/'.$file);
            else
                $success = false;
        }

        return $success;
    }

    public static function createHash($length = 40)         // just some random numbers for unsafe identifictaion purpose
    {
        static $seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $hash = '';

        for ($i = 0; $i < $length; $i++)
            $hash .= substr($seed, mt_rand(0, 61), 1);

        return $hash;
    }

    public static function mergeJsGlobals(array &$master, array ...$adds) : bool
    {
        if (!$adds)                                         // insufficient args
            return false;

        foreach ($adds as $arr)
        {
            foreach ($arr as $type => $data)
            {
                // bad data or empty
                if (empty(Util::$typeStrings[$type]) || !is_array($data) || !$data)
                    continue;

                if (!isset($master[$type]))
                    $master[$type] = [];

                foreach ($data as $k => $d)
                {
                    if (!isset($master[$type][$k]))         // int: id, yet to look up
                        $master[$type][$k] = $d;
                    else if (is_array($d))                  // array: already fetched data (overwrite old value if set)
                        $master[$type][$k] = $d;
                    // else                                 // id overwrites data .. do not want
                }
            }
        }

        return true;
    }

    public static function gainSiteReputation($user, $action, $miscData = [])
    {
        if (!$user || !$action)
            return false;

        $x = [];

        switch ($action)
        {
            case SITEREP_ACTION_REGISTER:
                $x['amount'] = CFG_REP_REWARD_REGISTER;
                break;
            case SITEREP_ACTION_DAILYVISIT:
                $x['sourceA'] = time();
                $x['amount']  = CFG_REP_REWARD_DAILYVISIT;
                break;
            case SITEREP_ACTION_COMMENT:
                if (empty($miscData['id']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['amount']  = CFG_REP_REWARD_COMMENT;
                break;
            case SITEREP_ACTION_UPVOTED:
            case SITEREP_ACTION_DOWNVOTED:
                if (empty($miscData['id']) || empty($miscData['voterId']))
                    return false;

                DB::Aowow()->query(                         // delete old votes the user has cast
                    'DELETE FROM ?_account_reputation WHERE sourceA = ?d AND sourceB = ?d AND userId = ?d AND action IN (?a)',
                    $miscData['id'],
                    $miscData['voterId'],
                    $user,
                    [SITEREP_ACTION_UPVOTED, SITEREP_ACTION_DOWNVOTED]
                );

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['sourceB'] = $miscData['voterId'];
                $x['amount']  = $action == SITEREP_ACTION_UPVOTED ? CFG_REP_REWARD_UPVOTED : CFG_REP_REWARD_DOWNVOTED;
                break;
            case SITEREP_ACTION_UPLOAD:
                if (empty($miscData['id']) || empty($miscData['what']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // screenshotId or videoId
                $x['sourceB'] = $miscData['what'];          // screenshot:1 or video:NYD
                $x['amount']  = CFG_REP_REWARD_UPLOAD;
                break;
            case SITEREP_ACTION_GOOD_REPORT:                // NYI
            case SITEREP_ACTION_BAD_REPORT:
                if (empty($miscData['id']))                 // reportId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_GOOD_REPORT ? CFG_REP_REWARD_GOOD_REPORT : CFG_REP_REWARD_BAD_REPORT;
                break;
            case SITEREP_ACTION_ARTICLE:                    // NYI
                if (empty($miscData['id']))                 // reportId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = CFG_REP_REWARD_ARTICLE;
                break;
            case SITEREP_ACTION_USER_WARNED:                // NYI
            case SITEREP_ACTION_USER_SUSPENDED:
                if (empty($miscData['id']))                 // banId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_USER_WARNED ? CFG_REP_REWARD_USER_WARNED : CFG_REP_REWARD_USER_SUSPENDED;
                break;
        }

        $x = array_merge($x, array(
            'userId' => $user,
            'action' => $action,
            'date'   => !empty($miscData['date']) ? $miscData['date'] : time()
        ));

        return DB::Aowow()->query('INSERT IGNORE INTO ?_account_reputation (?#) VALUES (?a)', array_keys($x), array_values($x));
    }

    public static function getServerConditions($srcType, $srcGroup = null, $srcEntry = null)
    {
        if (!$srcGroup && !$srcEntry)
            return [];

        $result    = [];
        $jsGlobals = [];

        $conditions = DB::World()->select(
            'SELECT  SourceTypeOrReferenceId, SourceEntry, SourceGroup, ElseGroup,
                     ConditionTypeOrReference, ConditionTarget, ConditionValue1, ConditionValue2, ConditionValue3, NegativeCondition
            FROM     conditions
            WHERE    SourceTypeOrReferenceId IN (?a) AND ?# = ?d
            ORDER BY SourceTypeOrReferenceId, SourceEntry, SourceGroup, ElseGroup ASC',
            is_array($srcType) ? $srcType : [$srcType],
            $srcGroup ? 'SourceGroup' : 'SourceEntry',
            $srcGroup ?: $srcEntry
        );

        foreach ($conditions as $c)
        {
            switch ($c['SourceTypeOrReferenceId'])
            {
                case CND_SRC_SPELL_CLICK_EVENT:             // 18
                case CND_SRC_VEHICLE_SPELL:                 // 21
                case CND_SRC_NPC_VENDOR:                    // 23
                    $jsGlobals[TYPE_NPC][] = $c['SourceGroup'];
                    break;
            }

            switch ($c['ConditionTypeOrReference'])
            {
                case CND_AURA:                              // 1
                    $c['ConditionValue2'] = null;           // do not use his param
                case CND_SPELL:                             // 25
                    $jsGlobals[TYPE_SPELL][] = $c['ConditionValue1'];
                    break;
                case CND_ITEM:                              // 2
                    $c['ConditionValue3'] = null;           // do not use his param
                case CND_ITEM_EQUIPPED:                     // 3
                    $jsGlobals[TYPE_ITEM][] = $c['ConditionValue1'];
                    break;
                case CND_MAPID:                             // 22 - break down to area or remap for use with g_zone_categories
                    switch ($c['ConditionValue1'])
                    {
                        case 530:                           // outland
                            $c['ConditionValue1'] = 8;
                            break;
                        case 571:                           // northrend
                            $c['ConditionValue1'] = 10;
                            break;
                        case 0:                             // old world is fine
                        case 1:
                            break;
                        default:                            // remap for area
                            $cnd = array(
                                ['mapId', (int)$c['ConditionValue1']],
                                ['parentArea', 0],          // not child zones
                                [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0],
                                1                           // only one result
                            );
                            $zone = new ZoneList($cnd);
                            if (!$zone->error)
                            {
                                $jsGlobals[TYPE_ZONE][] = $zone->getField('id');
                                $c['ConditionTypeOrReference'] = CND_ZONEID;
                                $c['ConditionValue1'] = $zone->getField('id');
                                break;
                            }
                            else
                                continue 3;
                    }
                case CND_ZONEID:                            // 4
                case CND_AREAID:                            // 23
                    $jsGlobals[TYPE_ZONE][] = $c['ConditionValue1'];
                    break;
                case CND_REPUTATION_RANK:                   // 5
                    $jsGlobals[TYPE_FACTION][] = $c['ConditionValue1'];
                    break;
                case CND_SKILL:                             // 7
                    $jsGlobals[TYPE_SKILL][] = $c['ConditionValue1'];
                    break;
                case CND_QUESTREWARDED:                     // 8
                case CND_QUESTTAKEN:                        // 9
                case CND_QUEST_NONE:                        // 14
                case CND_QUEST_COMPLETE:                    // 28
                    $jsGlobals[TYPE_QUEST][] = $c['ConditionValue1'];
                    break;
                case CND_ACTIVE_EVENT:                      // 12
                    $jsGlobals[TYPE_WORLDEVENT][] = $c['ConditionValue1'];
                    break;
                case CND_ACHIEVEMENT:                       // 17
                    $jsGlobals[TYPE_ACHIEVEMENT][] = $c['ConditionValue1'];
                    break;
                case CND_TITLE:                             // 18
                    $jsGlobals[TYPE_TITLE][] = $c['ConditionValue1'];
                    break;
                case CND_NEAR_CREATURE:                     // 29
                    $jsGlobals[TYPE_NPC][] = $c['ConditionValue1'];
                    break;
                case CND_NEAR_GAMEOBJECT:                   // 30
                    $jsGlobals[TYPE_OBJECT][] = $c['ConditionValue1'];
                    break;
                case CND_CLASS:                             // 15
                    for ($i = 0; $i < 11; $i++)
                        if ($c['ConditionValue1'] & (1 << $i))
                            $jsGlobals[TYPE_CLASS][] = $i + 1;
                    break;
                case CND_RACE:                              // 16
                    for ($i = 0; $i < 11; $i++)
                        if ($c['ConditionValue1'] & (1 << $i))
                            $jsGlobals[TYPE_RACE][] = $i + 1;
                    break;
                case CND_OBJECT_ENTRY:                      // 31
                    if ($c['ConditionValue1'] == 3)
                        $jsGlobals[TYPE_NPC][] = $c['ConditionValue2'];
                    else if ($c['ConditionValue1'] == 5)
                        $jsGlobals[TYPE_OBJECT][] = $c['ConditionValue2'];
                    break;
                case CND_TEAM:                              // 6
                    if ($c['ConditionValue1'] == 469)       // Alliance
                        $c['ConditionValue1'] = 1;
                    else if ($c['ConditionValue1'] == 67)   // Horde
                        $c['ConditionValue1'] = 2;
                    else
                        continue 2;
            }

            $res = [$c['NegativeCondition'] ? -$c['ConditionTypeOrReference'] : $c['ConditionTypeOrReference']];
            foreach ([1, 2, 3] as $i)
                if (($_ = $c['ConditionValue'.$i]) || $c['ConditionTypeOrReference'] = CND_DISTANCE_TO)
                    $res[] = $_;

            $group = $c['SourceEntry'];
            if (!in_array($c['SourceTypeOrReferenceId'], [CND_SRC_CREATURE_TEMPLATE_VEHICLE, CND_SRC_SPELL, CND_SRC_QUEST_ACCEPT, CND_SRC_QUEST_SHOW_MARK, CND_SRC_SPELL_PROC]))
                $group = $c['SourceEntry'] . ':' . $c['SourceGroup'];

            $result[$c['SourceTypeOrReferenceId']] [$group] [$c['ElseGroup']] [] = $res;
        }

        return [$result, $jsGlobals];
    }

    public static function sendNoCacheHeader()
    {
        header('Expires: Sat, 01 Jan 2000 01:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }

    public static function toJSON($data, $forceFlags = 0)
    {
        $flags = $forceFlags ?: (JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        if (CFG_DEBUG && !$forceFlags)
            $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($data, $flags);

        // handle strings prefixed with $ as js-variables
        // literal: match everything (lazy) between first pair of unescaped double quotes. First character must be $.
        $json = preg_replace_callback('/(?<!\\\\)"\$(.+?)(?<!\\\\)"/i', function($m) { return str_replace('\"', '"', $m[1]); }, $json);

        return $json;
    }

    public static function createSqlBatchInsert(array $data)
    {
        $nRows  = 100;
        $nItems = count(reset($data));
        $result = [];
        $buff   = [];

        if (!count($data))
            return [];

        foreach ($data as $d)
        {
            if (count($d) != $nItems)
                return [];

            $d = array_map(function ($x) {
                if ($x === null)
                    return 'NULL';

                return DB::Aowow()->escape($x);
            }, $d);

            $buff[] = implode(',', $d);

            if (count($buff) >= $nRows)
            {
                $result[] = '('.implode('),(', $buff).')';
                $buff = [];
            }
        }

        if ($buff)
            $result[] = '('.implode('),(', $buff).')';

        return $result;
    }

    /*****************/
    /* file handling */
    /*****************/

    public static function writeFile($file, $content)
    {
        $success = false;
        if ($handle = @fOpen($file, "w"))
        {
            if (fWrite($handle, $content))
                $success = true;
            else
                trigger_error('could not write to file', E_USER_ERROR);

            fClose($handle);
        }
        else
            trigger_error('could not create file', E_USER_ERROR);

        if ($success)
            @chmod($file, Util::FILE_ACCESS);

        return $success;
    }

    public static function writeDir($dir)
    {
        // remove multiple slashes
        $dir = preg_replace('|/+|', '/', $dir);

        if (is_dir($dir))
        {
            if (!is_writable($dir) && !@chmod($dir, Util::FILE_ACCESS))
                trigger_error('cannot write into directory', E_USER_ERROR);

            return is_writable($dir);
        }

        if (@mkdir($dir, Util::FILE_ACCESS, true))
            return true;

        trigger_error('could not create directory', E_USER_ERROR);
        return false;
    }


    /**************/
    /* Good Skill */
    /**************/

    public static function getEquipmentScore($itemLevel, $quality, $slot, $nSockets = 0)
    {
        $score = $itemLevel;

        // quality mod
        switch ($quality)
        {
            case ITEM_QUALITY_POOR:
                $score = 0;                                 // guessed as crap
                break;
            case ITEM_QUALITY_NORMAL:
                $score = 0;                                 // guessed as crap
                break;
            case ITEM_QUALITY_UNCOMMON:
                $score /= 2.0;
                break;
            case ITEM_QUALITY_RARE:
                $score /= 1.8;
                break;
            case ITEM_QUALITY_EPIC:
                $score /= 1.2;
                break;
            case ITEM_QUALITY_LEGENDARY:
                $score /= 1;
                break;
            case ITEM_QUALITY_HEIRLOOM:                     // actual calculation in javascript .. still uses this as some sort of factor..?
                break;
            case ITEM_QUALITY_ARTIFACT:
                break;
        }

        switch ($slot)
        {
            case INVTYPE_WEAPON:
            case INVTYPE_WEAPONMAINHAND:
            case INVTYPE_WEAPONOFFHAND:
                $score *= 27/64;
                break;
            case INVTYPE_SHIELD:
            case INVTYPE_HOLDABLE:
                $score *= 9/16;
                break;
            case INVTYPE_HEAD:
            case INVTYPE_CHEST:
            case INVTYPE_LEGS:
            case INVTYPE_2HWEAPON:
                $score *= 1.0;
                break;
            case INVTYPE_SHOULDERS:
            case INVTYPE_HANDS:
            case INVTYPE_WAIST:
            case INVTYPE_FEET:
                $score *= 3/4;
                break;
            case INVTYPE_WRISTS:
            case INVTYPE_NECK:
            case INVTYPE_CLOAK:
            case INVTYPE_FINGER:
            case INVTYPE_TRINKET:
                $score *= 9/16;
                break;
            case INVTYPE_THROWN:
            case INVTYPE_RANGED:
            case INVTYPE_RELIC:
                $score *= 81/256;
                break;
            default:
                $score *= 0.0;
        }

        // subtract sockets
        if ($nSockets)
        {
            // items by expansion overlap in this range. luckily highlevel raid items are exclusivly epic or better
            if ($itemLevel > 164 || ($itemLevel > 134 && $quality < ITEM_QUALITY_EPIC))
                $score -= $nSockets * self::GEM_SCORE_BASE_WOTLK;
            else
                $score -= $nSockets * self::GEM_SCORE_BASE_BC;
        }

        return round(max(0.0, $score), 4);
    }

    public static function getGemScore($itemLevel, $quality, $profSpec = false, $itemId = 0)
    {
        // prepare score-lookup
        if (empty(self::$perfectGems))
            self::$perfectGems = DB::World()->selectCol('SELECT perfectItemType FROM skill_perfect_item_template WHERE requiredSpecialization = ?d', 55534);

        // epic - WotLK - increased stats / profession specific (Dragon's Eyes)
        if ($profSpec)
            return 32.0;
        // epic - WotLK - base stats
        if ($itemLevel == 80 && $quality == ITEM_QUALITY_EPIC)
            return 20.0;
        // rare - WotLK [GEM BASELINE!]
        if ($itemLevel == 80 && $quality == ITEM_QUALITY_RARE)
            return 16.0;
        // uncommon - WotLK - inreased stats
        if ($itemId > 0 && in_array($itemId, self::$perfectGems))
            return 14.0;
        // uncommon - WotLK - base stats
        if ($itemLevel == 70 && $quality == ITEM_QUALITY_UNCOMMON)
            return 12.0;
        // epic - BC - vendored (PvP)
        if ($itemLevel == 60 && $quality == ITEM_QUALITY_EPIC)
            return 10.0;
        // epic - BC - dropped / crafted
        if ($itemLevel == 70 && $quality == ITEM_QUALITY_EPIC)
            return 9.0;
        // rare - BC - crafted
        if ($itemLevel == 70 && $quality == ITEM_QUALITY_RARE)
            return 8.0;
        // rare - BC - vendored (pvp)
        if ($itemLevel == 60 && $quality == ITEM_QUALITY_RARE)
            return 7.0;
        // uncommon - BC
        if ($itemLevel == 60 && $quality == ITEM_QUALITY_UNCOMMON)
            return 6.0;
        // common - BC - vendored gems
        if ($itemLevel == 55 && $quality == ITEM_QUALITY_NORMAL)
            return 4.0;

        // dafuq..?
        return 0.0;
    }

    public static function getEnchantmentScore($itemLevel, $quality, $profSpec = false, $idOverride = 0)
    {
        // some hardcoded values, that defy lookups (cheaper but not skillbound profession versions of spell threads, leg armor)
        if (in_array($idOverride, [3327, 3328, 3872, 3873]))
            return 20.0;

        if ($profSpec)
            return 40.0;

        // other than the constraints (0 - 20 points; 40 for profession perks), everything in here is guesswork
        $score = max(min($itemLevel, 80), 0);

        switch ($quality)
        {
            case ITEM_QUALITY_HEIRLOOM:                 // because i say so!
                $score = 80.0;
                break;
            case ITEM_QUALITY_RARE:
                $score /= 1.2;
                break;
            case ITEM_QUALITY_UNCOMMON:
                $score /= 1.6;
                break;
            case ITEM_QUALITY_NORMAL:
                $score /= 2.5;
                break;
        }

        return round(max(0.0, $score / 4), 4);
    }

    public static function fixWeaponScores($class, $talents, $mainHand, $offHand)
    {
        $mh = 1;
        $oh = 1;

        if ($mainHand) { // Main Hand Equipped
            if ($offHand) { // Off Hand Equipped
                if ($mainHand['slotbak'] == 21 || $mainHand['slotbak'] == 13) { // Main Hand, One Hand
                    if ($offHand['slotbak'] == 22 || $offHand['slotbak'] == 13) { // Off Hand, One Hand
                        if ($class == 6 || $class == 3 || $class == 4 || // Death Knight, Hunter, Rogue
                           ($class == 7 && $talents['spent'][1] > 30 && $talents['spec'] == 2) || // Enhancement Shaman Over 39
                           ($class == 1 && $talents['spent'][1] < 51 && $talents['spec'] == 2)) // Fury Warrior Under 60
                        {
                            $mh = 64 / 27;
                            $oh = 64 / 27;
                        }
                    }
                    else if ($offHand['slotbak'] == 23 || $offHand['slotbak'] == 14) { // Held in Off Hand, Shield
                        if ($class == 5 || $class == 9 || $class == 8 || // Priest, Warlock, Mage
                           ($class == 11 && ($talents['spec'] == 1 || $talents['spec'] == 3)) || // Balance Druid, Restoration Druid
                           ($class == 7 && ($talents['spec'] == 1 || $talents['spec'] == 3)) || // Elemental Shaman, Restoration Shaman
                           ($class == 2 && ($talents['spec'] == 1 || $talents['spec'] == 2)) || // Holy Paladin, Protection Paladin
                           ($class == 1 && $talents['spec'] == 3))  // Protection Warrior
                        {
                            $mh = 64 / 27;
                            $oh = 16 / 9;
                        }
                    }
                }
            }
            else if ($mainHand['slotbak'] == 17) {  // Two Handed
                if ($class == 5 || $class == 9 || $class == 8 || // Priest, Warlock, Mage
                    $class == 11 || $class == 3 || $class == 6 || // Druid, Hunter, Death Knight
                   ($class == 7 && $talents['spent'][1] < 31 && $talents['spec'] == 2) || // Enhancement Shaman Under 40
                   ($class == 2 && $talents['spec'] == 3) || // Retribution Paladin
                   ($class == 1 && $talents['spec'] == 1)) // Arms Warrior
                {
                    $mh = 2;
                    $oh = 0;
                }
            }
        }

        return array(
            round($mainHand['gearscore'] * $mh),
            round($offHand['gearscore']  * $oh)
        );
    }

    static function createReport($mode, $reason, $subject, $desc, $userAgent = null, $appName = null, $url = null, $relUrl = null, $email = null)
    {
        $update = array(
            'userId'      => User::$id,
            'createDate'  => time(),
            'mode'        => $mode,
            'reason'      => $reason,
            'subject'     => $subject ?: 0,                 // not set for utility, tools and misc pages
            'ip'          => User::$ip,
            'description' => $desc,
            'userAgent'   => $userAgent ?: $_SERVER['HTTP_USER_AGENT'],
            'appName'     => $appName ?: (get_browser(null, true)['browser'] ?: '')
        );

        if ($url)
            $update['url'] = $url;

        if ($relUrl)
            $update['relatedurl'] = $relUrl;

        if ($email)
            $update['email'] = $email;

        return DB::Aowow()->query('INSERT INTO ?_reports (?#) VALUES (?a)', array_keys($update), array_values($update));
    }

    // orientation is 2*M_PI for a full circle, increasing counterclockwise
    static function O2Deg($o)
    {
        // orientation values can exceed boundaries (for whatever reason)
        while ($o < 0)
            $o += 2*M_PI;

        while ($o >= 2*M_PI)
            $o -= 2*M_PI;

        $deg = 360 * (1 - ($o / (2*M_PI) ) );
        if ($deg == 360)
            $deg = 0;

        $dir  = Lang::game('orientation');
        $desc = '';
        foreach ($dir as $f => $d)
        {
            if (!$f)
                continue;

            if ( ($deg >= (45 * $f) - 22.5) && ($deg <= (45 * $f) + 22.5) )
            {
                $desc = $d;
                break;
            }
        }

        if (!$desc)
            $desc = $dir[0];

        return [(int)$deg, $desc];
    }

    static function mask2bits($bitmask, $offset = 0)
    {
        $bits = [];
        $i    = 0;
        while ($bitmask)
        {
            if ($bitmask & (1 << $i))
            {
                $bitmask &= ~(1 << $i);
                $bits[] = ($i + $offset);
            }
            $i++;
        }

        return $bits;
    }
}

?>
