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

trait TrRequestData
{
    // const in trait supported in php8.2+
    public static $PATTERN_TEXT_LINE = '/[\p{Cc}\p{Cf}\p{Co}\p{Cs}\p{Cn}]/ui';
    public static $PATTERN_TEXT_BLOB = '/[\x00-\x09\x0B-\x1F\p{Cf}\p{Co}\p{Cs}\p{Cn}]/ui';

    protected $_get    = [];                                // fill with variables you that are going to be used; eg:
    protected $_post   = [];                                // 'id' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkIdList']
    protected $_cookie = [];

    private $filtered = false;

    private function initRequestData() : void
    {
        if ($this->filtered)
            return;

        // php bug? If INPUT_X is empty, filter_input_array returns null/fails
        // only really relevant for INPUT_POST
        // manuall set everything null in this case

        if ($this->_post)
        {
            if ($_POST)
                $this->_post = filter_input_array(INPUT_POST, $this->_post);
            else
                $this->_post = array_fill_keys(array_keys($this->_post), null);
        }

        if ($this->_get)
        {
            if ($_GET)
                $this->_get = filter_input_array(INPUT_GET, $this->_get);
            else
                $this->_get = array_fill_keys(array_keys($this->_get), null);
        }

        if ($this->_cookie)
        {
            if ($_COOKIE)
                $this->_cookie = filter_input_array(INPUT_COOKIE, $this->_cookie);
            else
                $this->_cookie = array_fill_keys(array_keys($this->_cookie), null);
        }

        $this->filtered = true;
    }

    private static function checkEmptySet(string $val) : bool
    {
        return $val === '';                                 // parameter is expected to be empty
    }

    private static function checkInt(string $val) : int
    {
        if (preg_match('/^-?\d+$/', $val))
            return intVal($val);

        return 0;
    }

    private static function checkLocale(string $val) : int
    {
        if (preg_match('/^'.implode('|', array_keys(array_filter(Util::$localeStrings))).'$/', $val))
            return intVal($val);

        return -1;
    }

    private static function checkDomain(string $val) : string
    {
        if (preg_match('/^'.implode('|', array_filter(Util::$subDomains)).'$/i', $val))
            return strtolower($val);

        return '';
    }

    private static function checkIdList(string $val) : array
    {
        if (preg_match('/^-?\d+(,-?\d+)*$/', $val))
            return array_map('intVal', explode(',', $val));

        return [];
    }

    private static function checkIntArray(string $val) : array
    {
        if (preg_match('/^-?\d+(:-?\d+)*$/', $val))
            return array_map('intVal', explode(':', $val));

        return [];
    }

    private static function checkIdListUnsigned(string $val) : array
    {
        if (preg_match('/^\d+(,\d+)*$/', $val))
            return array_map('intVal', explode(',', $val));

        return [];
    }

    private static function checkTextLine(string $val) : string
    {
        // trim non-printable chars
        return preg_replace(self::$PATTERN_TEXT_LINE, '', $val);
    }

    private static function checkTextBlob(string $val) : string
    {
        // trim non-printable chars
        return preg_replace(self::$PATTERN_TEXT_BLOB, '', $val);
    }
}

abstract class CLI
{
    private const CHR_BELL      = 7;
    private const CHR_BACK      = 8;
    private const CHR_TAB       = 9;
    private const CHR_LF        = 10;
    private const CHR_CR        = 13;
    private const CHR_ESC       = 27;
    private const CHR_BACKSPACE = 127;

    public const LOG_BLANK      = 0;
    public const LOG_ERROR      = 1;
    public const LOG_WARN       = 2;
    public const LOG_INFO       = 3;
    public const LOG_OK         = 4;

    private static $logHandle   = null;
    private static $hasReadline = null;

    private static $overwriteLast = false;

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
                $pads[$j] = max($pads[$j] ?? 0, mb_strlen($row[$j] ?? ''));
        }
        self::write();

        foreach ($out as $row)
        {
            for ($i = 0; $i < $nCols - 1; $i++)             // don't pad last column
                $row[$i] = str_pad($row[$i] ?? '', $pads[$i] + 2);

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

    public static function grey(string $str) : string
    {
        return CLI_HAS_E ? "\e[90m".$str."\e[0m" : $str;
    }

    public static function red(string $str) : string
    {
        return CLI_HAS_E ? "\e[31m".$str."\e[0m" : $str;
    }

    public static function green(string $str) : string
    {
        return CLI_HAS_E ? "\e[32m".$str."\e[0m" : $str;
    }

    public static function yellow(string $str) : string
    {
        return CLI_HAS_E ? "\e[33m".$str."\e[0m" : $str;
    }

    public static function blue(string $str) : string
    {
        return CLI_HAS_E ? "\e[36m".$str."\e[0m" : $str;
    }

    public static function bold(string $str) : string
    {
        return CLI_HAS_E ? "\e[1m".$str."\e[0m" : $str;
    }

    public static function write(string $txt = '', int $lvl = self::LOG_BLANK, bool $timestamp = true, bool $tmpRow = false) : void
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

            $msg .= $txt;
        }

        $msg = (self::$overwriteLast && CLI_HAS_E ? "\e[1G\e[0K" : "\n") . $msg;
        self::$overwriteLast = $tmpRow;

        echo $msg;

        if (self::$logHandle)                               // remove control sequences from log
            fwrite(self::$logHandle, preg_replace(["/\e\[\d+[mK]/", "/\e\[\d+G/"], ['', "\n"], $msg));

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

        if (!$path)                                         // empty strings given. (faulty dbc data?)
            return '';

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

                    // ignore these ones
                    if ($keyId == self::CHR_TAB || $keyId == self::CHR_CR)
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


class Timer
{
    private $t_cur = 0;
    private $t_new = 0;
    private $intv  = 0;

    public function __construct(int $intervall)
    {
        $this->intv  = $intervall / 1000;                   // in msec
        $this->t_cur = microtime(true);
    }

    public function update() : bool
    {
        $this->t_new = microtime(true);
        if ($this->t_new > $this->t_cur + $this->intv)
        {
            $this->t_cur = $this->t_cur + $this->intv;
            return true;
        }

        return false;
    }

    public function reset() : void
    {
        $this->t_cur = microtime(true) - $this->intv;
    }
}


abstract class Util
{
    const FILE_ACCESS = 0755;
    const DIR_ACCESS  = 0777;

    const GEM_SCORE_BASE_WOTLK = 16;                        // rare quality wotlk gem score
    const GEM_SCORE_BASE_BC    = 8;                         // rare quality bc gem score

    private static $perfectGems             = null;

    public static $localeStrings            = array(        // zero-indexed
        'enus',         null,           'frfr',         'dede',         'zhcn',         null,           'eses',         null,           'ruru'
    );

    public static $subDomains               = array(
        'en',           null,           'fr',           'de',           'cn',           null,           'es',           null,           'ru'
    );

    public static $regions                   = array(
        'us',           'eu',           'kr',           'tw',           'cn',           'dev'
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
    public static $lvTabNoteString          = '<b class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, \'%s\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">%s</b>';

    public static $filterResultString       = '$$WH.sprintf(LANG.lvnote_filterresults, \'%s\')';
    public static $tryFilteringString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryFilteringEntityString = '$$WH.sprintf(LANG.lvnote_entitiesfound, %s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryNarrowingString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_trynarrowing';

    public static $dfnString                = '<dfn title="%s" class="w">%s</dfn>';

    public static $mapSelectorString        = '<a href="javascript:;" onclick="myMapper.update({zone: %d}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">%s</a>&nbsp;(%d)';

    public static $guideratingString        = "        $(document).ready(function() {\n        $('#guiderating').append(GetStars(%.10F, %s, %u, %u));\n    });";

    public static $expansionString          = array(        // 3 & 4 unused .. obviously
        null,           'bc',           'wotlk',            'cata',                'mop'
    );

    public static $tcEncoding               = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789';
    private static $notes                   = [];

    public static function addNote(string $note, int $uGroupMask = U_GROUP_EMPLOYEE, int $level = CLI::LOG_ERROR) : void
    {
        self::$notes[] = [$note, $uGroupMask, $level];
    }

    public static function getNotes() : array
    {
        $notes = [];
        $severity = CLI::LOG_INFO;
        foreach (self::$notes as [$note, $uGroup, $level])
        {
            if ($uGroup && !User::isInGroup($uGroup))
                continue;

            if ($level < $severity)
                $severity = $level;

            $notes[] = $note;
        }

        return [$notes, $severity];
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

    public static function parseTime(int $msec) : array
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
                return $_."&nbsp;".Lang::timeUnits('ab', 0);
            if ($_ = round($d / 30))
                return $_."&nbsp;".Lang::timeUnits('ab', 1);
            if ($_ = round($d / 7))
                return $_."&nbsp;".Lang::timeUnits('ab', 2);
            if ($_ = round($d))
                return $_."&nbsp;".Lang::timeUnits('ab', 3);
            if ($_ = round($h))
                return $_."&nbsp;".Lang::timeUnits('ab', 4);
            if ($_ = round($m))
                return $_."&nbsp;".Lang::timeUnits('ab', 5);
            if ($_ = round($s + $ms / 1000, 2))
                return $_."&nbsp;".Lang::timeUnits('ab', 6);
            if ($ms)
                return $ms."&nbsp;".Lang::timeUnits('ab', 7);

            return '0 '.Lang::timeUnits('ab', 6);
        }
        else
        {
            $_ = $d + $h / 24;
            if ($_ > 1 && !($_ % 364))                      // whole years
                return round(($d + $h / 24) / 364, 2)."&nbsp;".Lang::timeUnits($d / 364 == 1 && !$h ? 'sg' : 'pl', 0);
            if ($_ > 1 && !($_ % 30))                       // whole month
                return round(($d + $h / 24) /  30, 2)."&nbsp;".Lang::timeUnits($d /  30 == 1 && !$h ? 'sg' : 'pl', 1);
            if ($_ > 1 && !($_ % 7))                        // whole weeks
                return round(($d + $h / 24) /   7, 2)."&nbsp;".Lang::timeUnits($d /   7 == 1 && !$h ? 'sg' : 'pl', 2);
            if ($d)
                return round($d + $h  /   24, 2)."&nbsp;".Lang::timeUnits($d == 1 && !$h  ? 'sg' : 'pl', 3);
            if ($h)
                return round($h + $m  /   60, 2)."&nbsp;".Lang::timeUnits($h == 1 && !$m  ? 'sg' : 'pl', 4);
            if ($m)
                return round($m + $s  /   60, 2)."&nbsp;".Lang::timeUnits($m == 1 && !$s  ? 'sg' : 'pl', 5);
            if ($s)
                return round($s + $ms / 1000, 2)."&nbsp;".Lang::timeUnits($s == 1 && !$ms ? 'sg' : 'pl', 6);
            if ($ms)
                return $ms." ".Lang::timeUnits($ms == 1 ? 'sg' : 'pl', 7);

            return '0 '.Lang::timeUnits('pl', 6);
        }
    }

    public static function formatTimeDiff(int $sec) : string
    {
        $delta = time() - $sec;

        [, $s, $m, $h, $d] = self::parseTime($delta * 1000);

        if ($delta > (1 * MONTH))                           // use absolute
            return date(Lang::main('dateFmtLong'), $sec);
        else if ($delta > (2 * DAY))                        // days ago
            return Lang::main('timeAgo', [$d . ' ' . Lang::timeUnits('pl', 3)]);
        else if ($h)                                        // hours, minutes ago
            return Lang::main('timeAgo', [$h . ' ' . Lang::timeUnits('ab', 4) . ' ' . $m . ' ' . Lang::timeUnits('ab', 5)]);
        else if ($m)                                        // minutes, seconds ago
            return Lang::main('timeAgo', [$m . ' ' . Lang::timeUnits('ab', 5) . ' ' . $s . ' ' . Lang::timeUnits('ab', 6)]);
        else                                                // seconds ago
            return Lang::main('timeAgo', [$s . ' ' . Lang::timeUnits($s == 1 ? 'sg' : 'pl', 6)]);
    }

    // pageTexts, questTexts and mails
    public static function parseHtmlText(string|array $text, bool $markdown = false) : string|array
    {
        if (is_array($text))
        {
            foreach ($text as &$t)
                $t = self::parseHtmlText($t, $markdown);

            return $text;
        }

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
                function ($m) { return sprintf('src="%s/images/wow/%s.png"', Cfg::get('STATIC_URL'), strtr($m[1], ['\\' => '/'])); },
                strtr($text, $pairs)
            );
        }
        else
            $text = strtr($text, ["\n" => $markdown ? '[br]' : '<br />', "\r" => '']);

        // escape fake html-ish tags the browser skipsh dishplaying ...<hic>!
        $text = preg_replace('/<([^\s\/]+)>/iu', '&lt;\1&gt;', $text);

        $from = array(
            '/\$g\s*([^:;]*)\s*:\s*([^:;]*)\s*(:?[^:;]*);/ui',// directed gender-reference                      $g<male>:<female>:<refVariable>
            '/\$t([^;]+);/ui',                              // nonsense, that the client apparently ignores
            '/<([^\"=\/>]+\s[^\"=\/>]+)>/ui',               // emotes (workaround: at least one whitespace and never " or = between brackets)
            '/\$(\d+)w/ui',                                 // worldState(?)-ref found on some pageTexts        $1234w
            '/\$c/i',                                       // class-ref
            '/\$r/i',                                       // race-ref
            '/\$n/i',                                       // name-ref
            '/\$b/i'                                        // line break
        );

        $toMD = array(
            '<\1/\2>',
            '',
            '<\1>',
            '[span class=q0>WorldState #\1[/span]',
            '<'.Lang::game('class').'>',
            '<'.Lang::game('race').'>',
            '<'.Lang::main('name').'>',
            '[br]'
        );

        $toHTML = array(
            '&lt;\1/\2&gt;',
            '',
            '&lt;\1&gt;',
            '<span class="q0">WorldState #\1</span>',
            '&lt;'.Lang::game('class').'&gt;',
            '&lt;'.Lang::game('race').'&gt;',
            '&lt;'.Lang::main('name').'&gt;',
            '<br />'
        );

        $text = preg_replace($from, $markdown ? $toMD : $toHTML, $text);

        return Lang::unescapeUISequences($text, $markdown ? Lang::FMT_MARKUP : Lang::FMT_HTML);
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

        return htmlspecialchars($data, ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, 'utf-8');
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
            'HOST_URL'   => Cfg::get('HOST_URL'),
            'STATIC_URL' => Cfg::get('STATIC_URL')
        ));
    }

    // todo: create Locale object and integrate
    public static function isLogographic(int $localeId) : bool
    {
        return $localeId == LOCALE_CN || $localeId == LOCALE_TW || $localeId == LOCALE_KR;
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

        return Lang::item('ratingString', [$type, $result, $level]);
    }

    public static function powerUseLocale($domain = 'www')
    {
        foreach (Util::$localeStrings as $k => $v)
        {
            if (strstr($v, $domain))
            {
                User::useLocale($k);
                Lang::load($k);
                return;
            }
        }

        if ($domain == 'www')
        {
            User::useLocale(LOCALE_EN);
            Lang::load(LOCALE_EN);
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

    // doesn't handle scientific notation .. why would you input 3e3 for 3000..?
    public static function checkNumeric(&$data, $typeCast = NUM_ANY)
    {
        if ($data === null)
            return false;

        if (is_array($data))
        {
            array_walk($data, function(&$x) use($typeCast) { self::checkNumeric($x, $typeCast); });
            return false;                                   // always false for passed arrays
        }

        // already in required state
        if ((is_float($data) && $typeCast == NUM_REQ_FLOAT) ||
            (is_int($data) && $typeCast == NUM_REQ_INT))
            return true;

        // irreconcilable state
        if ((!is_int($data) && $typeCast == NUM_REQ_INT) ||
            (!is_float($data) && $typeCast == NUM_REQ_FLOAT))
            return false;

        $number = $data;                                    // do not transform strings, store state
        $nMatches = 0;

        $number = trim($number);
        $number = preg_replace('/^(-?\d*)[.,](\d+)$/', '$1.$2', $number, -1, $nMatches);

        // is float string
        if ($nMatches)
        {
            if ($typeCast == NUM_CAST_INT)
                $data = intVal($number);
            else if ($typeCast == NUM_CAST_FLOAT)
                $data = floatVal($number);

            return true;
        }

        // is int string (is_numeric can only handle strings in base 10)
        if (is_numeric($number) || preg_match('/0[xb]?\d+/', $number))
        {
            $number = intVal($number, 0);                   // 'base 0' auto-detects base
            if ($typeCast == NUM_CAST_INT)
                $data = $number;
            else if ($typeCast == NUM_CAST_FLOAT)
                $data = floatVal($number);

            return true;
        }

        // is string string
        return false;
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

    public static function createHash($length = 40)         // just some random numbers for unsafe identification purpose
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
                if (!Type::exists($type) || !is_array($data) || !$data)
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
                $x['amount'] = Cfg::get('REP_REWARD_REGISTER');
                break;
            case SITEREP_ACTION_DAILYVISIT:
                $x['sourceA'] = time();
                $x['amount']  = Cfg::get('REP_REWARD_DAILYVISIT');
                break;
            case SITEREP_ACTION_COMMENT:
                if (empty($miscData['id']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['amount']  = Cfg::get('REP_REWARD_COMMENT');
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
                $x['amount']  = $action == SITEREP_ACTION_UPVOTED ? Cfg::get('REP_REWARD_UPVOTED') : Cfg::get('REP_REWARD_DOWNVOTED');
                break;
            case SITEREP_ACTION_UPLOAD:
                if (empty($miscData['id']) || empty($miscData['what']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // screenshotId or videoId
                $x['sourceB'] = $miscData['what'];          // screenshot:1 or video:NYD
                $x['amount']  = Cfg::get('REP_REWARD_UPLOAD');
                break;
            case SITEREP_ACTION_GOOD_REPORT:                // NYI
            case SITEREP_ACTION_BAD_REPORT:
                if (empty($miscData['id']))                 // reportId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_GOOD_REPORT ? Cfg::get('REP_REWARD_GOOD_REPORT') : Cfg::get('REP_REWARD_BAD_REPORT');
                break;
            case SITEREP_ACTION_ARTICLE:
                if (empty($miscData['id']))                 // guideId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = Cfg::get('REP_REWARD_ARTICLE');
                break;
            case SITEREP_ACTION_USER_WARNED:                // NYI
            case SITEREP_ACTION_USER_SUSPENDED:
                if (empty($miscData['id']))                 // banId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_USER_WARNED ? Cfg::get('REP_REWARD_USER_WARNED') : Cfg::get('REP_REWARD_USER_SUSPENDED');
                break;
        }

        $x = array_merge($x, array(
            'userId' => $user,
            'action' => $action,
            'date'   => !empty($miscData['date']) ? $miscData['date'] : time()
        ));

        return DB::Aowow()->query('INSERT IGNORE INTO ?_account_reputation (?#) VALUES (?a)', array_keys($x), array_values($x));
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

        if (Cfg::get('DEBUG') && !$forceFlags)
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
            if (!is_writable($dir) && !@chmod($dir, Util::DIR_ACCESS))
                trigger_error('cannot write into directory', E_USER_ERROR);

            return is_writable($dir);
        }

        if (@mkdir($dir, Util::DIR_ACCESS, true))
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
            round(($mainHand['gearscore'] ?? 0) * $mh),
            round(($offHand['gearscore']  ?? 0) * $oh)
        );
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

    static function mask2bits(int $bitmask, int $offset = 0) : array
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

abstract class Type
{
    public const NPC =                          1;
    public const OBJECT =                       2;
    public const ITEM =                         3;
    public const ITEMSET =                      4;
    public const QUEST =                        5;
    public const SPELL =                        6;
    public const ZONE =                         7;
    public const FACTION =                      8;
    public const PET =                          9;
    public const ACHIEVEMENT =                 10;
    public const TITLE =                       11;
    public const WORLDEVENT =                  12;
    public const CHR_CLASS =                   13;
    public const CHR_RACE =                    14;
    public const SKILL =                       15;
    public const STATISTIC =                   16;
    public const CURRENCY =                    17;
    //           PROJECT =                     18;
    public const SOUND =                       19;
    //           BUILDING =                    20;
    //           FOLLOWER =                    21;
    //           MISSION_ABILITY =             22;
    //           MISSION =                     23;
    //           SHIP   =                      25;
    //           THREAT =                      26;
    //           RESOURCE =                    27;
    //           CHAMPION =                    28;
    public const ICON =                        29;
    //           ORDER_ADVANCEMENT =           30;
    //           FOLLOWER_ALLIANCE =           31;
    //           FOLLOWER_HORDE =              32;
    //           SHIP_ALLIANCE =               33;
    //           SHIP_HORDE =                  34;
    //           CHAMPION_ALLIANCE =           35;
    //           CHAMPION_HORDE =              36;
    //           TRANSMOG_ITEM =               37;
    //           BFA_CHAMPION =                38;
    //           BFA_CHAMPION_ALLIANCE =       39;
    //           AFFIX =                       40;
    //           BFA_CHAMPION_HORDE =          41;
    //           AZERITE_ESSENCE_POWER =       42;
    //           AZERITE_ESSENCE =             43;
    //           STORYLINE =                   44;
    //           ADVENTURE_COMBATANT_ABILITY = 46;
    //           ENCOUNTER =                   47;
    //           COVENANT =                    48;
    //           SOULBIND =                    49;
    //           DI_ITEM =                     50;
    //           GATHERER_SCREENSHOT =         91;
    //           GATHERER_GUIDE_IMAGE =        98;
    public const PROFILE =                    100;
    // our own things
    public const GUILD =                      101;
    //           TRANSMOG_SET =               101;          // future conflict inc.
    public const ARENA_TEAM =                 102;
    //           OUTFIT =                     110;
    //           GEAR_SET =                   111;
    //           GATHERER_LISTVIEW =          158;
    //           GATHERER_SURVEY_COVENANTS =  161;
    //           NEWS_POST =                  162;
    //           BATTLE_PET_ABILITY =         200;
    public const GUIDE =                      300;          // should have been 100, but conflicts with old version of Profile/List
    public const USER =                       500;
    public const EMOTE =                      501;
    public const ENCHANTMENT =                502;
    public const AREATRIGGER =                503;
    public const MAIL =                       504;
    // Blizzard API things
    //           MOUNT =                    -1000;
    //           RECIPE =                   -1001;
    //           BATTLE_PET =               -1002;

    public const FLAG_NONE              = 0x0;
    public const FLAG_RANDOM_SEARCHABLE = 0x1;
 /* public const FLAG_SEARCHABLE        = 0x2 general search? */

    public const IDX_LIST_OBJ = 0;
    public const IDX_FILE_STR = 1;
    public const IDX_JSG_TPL  = 2;
    public const IDX_FLAGS    = 3;

    private static /* array */ $data = array(
        self::NPC         => ['CreatureList',    'npc',         'g_npcs',              0x1],
        self::OBJECT      => ['GameObjectList',  'object',      'g_objects',           0x1],
        self::ITEM        => ['ItemList',        'item',        'g_items',             0x1],
        self::ITEMSET     => ['ItemsetList',     'itemset',     'g_itemsets',          0x1],
        self::QUEST       => ['QuestList',       'quest',       'g_quests',            0x1],
        self::SPELL       => ['SpellList',       'spell',       'g_spells',            0x1],
        self::ZONE        => ['ZoneList',        'zone',        'g_gatheredzones',     0x1],
        self::FACTION     => ['FactionList',     'faction',     'g_factions',          0x1],
        self::PET         => ['PetList',         'pet',         'g_pets',              0x1],
        self::ACHIEVEMENT => ['AchievementList', 'achievement', 'g_achievements',      0x1],
        self::TITLE       => ['TitleList',       'title',       'g_titles',            0x1],
        self::WORLDEVENT  => ['WorldEventList',  'event',       'g_holidays',          0x1],
        self::CHR_CLASS   => ['CharClassList',   'class',       'g_classes',           0x1],
        self::CHR_RACE    => ['CharRaceList',    'race',        'g_races',             0x1],
        self::SKILL       => ['SkillList',       'skill',       'g_skills',            0x1],
        self::STATISTIC   => ['AchievementList', 'achievement', 'g_achievements',      0x1], // alias for achievements; exists only for Markup
        self::CURRENCY    => ['CurrencyList',    'currency',    'g_gatheredcurrencies',0x1],
        self::SOUND       => ['SoundList',       'sound',       'g_sounds',            0x1],
        self::ICON        => ['IconList',        'icon',        'g_icons',             0x1],
        self::GUIDE       => ['GuideList',       'guide',       '',                    0x0],
        self::PROFILE     => ['ProfileList',     '',            '',                    0x0], // x - not known in javascript
        self::GUILD       => ['GuildList',       '',            '',                    0x0], // x
        self::ARENA_TEAM  => ['ArenaTeamList',   '',            '',                    0x0], // x
        self::USER        => ['UserList',        'user',        'g_users',             0x0], // x
        self::EMOTE       => ['EmoteList',       'emote',       'g_emotes',            0x1],
        self::ENCHANTMENT => ['EnchantmentList', 'enchantment', 'g_enchantments',      0x1],
        self::AREATRIGGER => ['AreatriggerList', 'areatrigger', '',                    0x0],
        self::MAIL        => ['MailList',        'mail',        '',                    0x1]
    );


    /********************/
    /* Field Operations */
    /********************/

    public static function newList(int $type, ?array $conditions = []) : ?BaseType
    {
        if (!self::exists($type))
            return null;

        return new (self::$data[$type][self::IDX_LIST_OBJ])($conditions);
    }

    public static function getFileString(int $type) : string
    {
        if (!self::exists($type))
            return '';

        return self::$data[$type][self::IDX_FILE_STR];
    }

    public static function getJSGlobalString(int $type) : string
    {
        if (!self::exists($type))
            return '';

        return self::$data[$type][self::IDX_JSG_TPL];
    }

    public static function getJSGlobalTemplate(int $type) : array
    {
        if (!self::exists($type))
            return [];

            // [key, [data], [extraData]]
        return [self::$data[$type][self::IDX_JSG_TPL], [], []];
    }

    public static function checkClassAttrib(int $type, string $attr, ?int $attrVal = null) : bool
    {
        if (!self::exists($type))
            return false;

        return isset((self::$data[$type][self::IDX_LIST_OBJ])::$$attr) && ($attrVal === null || ((self::$data[$type][self::IDX_LIST_OBJ])::$$attr & $attrVal));
    }

    public static function getClassAttrib(int $type, string $attr) : mixed
    {
        if (!self::exists($type))
            return null;

        return (self::$data[$type][self::IDX_LIST_OBJ])::$$attr ?? null;
    }

    public static function exists(int $type) : bool
    {
        return !empty(self::$data[$type]);
    }

    public static function getIndexFrom(int $idx, string $match) : int
    {
        $i = array_search($match, array_column(self::$data, $idx));
        if ($i === false)
            return 0;

        return array_keys(self::$data)[$i];
    }


    /*********************/
    /* Column Operations */
    /*********************/

    public static function getClassesFor(int $flags = 0x0, string $attr = '', ?int $attrVal = null) : array
    {
        $x = [];
        foreach (self::$data as $k => [$o, , , $f])
            if ($o && (!$flags || $flags & $f))
                if (!$attr || self::checkClassAttrib($k, $attr, $attrVal))
                    $x[$k] = $o;

        return $x;
    }

    public static function getFileStringsFor(int $flags = 0x0) : array
    {
        $x = [];
        foreach (self::$data as $k => [, $s, , $f])
            if ($s && (!$flags || $flags & $f))
                $x[$k] = $s;

        return $x;
    }

    public static function getJSGTemplatesFor(int $flags = 0x0) : array
    {
        $x = [];
        foreach (self::$data as $k => [, , $a, $f])
            if ($a && (!$flags || $flags & $f))
                $x[$k] = $a;

        return $x;
    }
}


class Report
{
    public const MODE_GENERAL         = 0;
    public const MODE_COMMENT         = 1;
    public const MODE_FORUM_POST      = 2;
    public const MODE_SCREENSHOT      = 3;
    public const MODE_CHARACTER       = 4;
    public const MODE_VIDEO           = 5;
    public const MODE_GUIDE           = 6;

    public const GEN_FEEDBACK         = 1;
    public const GEN_BUG_REPORT       = 2;
    public const GEN_TYPO_TRANSLATION = 3;
    public const GEN_OP_ADVERTISING   = 4;
    public const GEN_OP_PARTNERSHIP   = 5;
    public const GEN_PRESS_INQUIRY    = 6;
    public const GEN_MISCELLANEOUS    = 7;
    public const GEN_MISINFORMATION   = 8;
    public const CO_ADVERTISING       = 15;
    public const CO_INACCURATE        = 16;
    public const CO_OUT_OF_DATE       = 17;
    public const CO_SPAM              = 18;
    public const CO_INAPPROPRIATE     = 19;
    public const CO_MISCELLANEOUS     = 20;
    public const FO_ADVERTISING       = 30;
    public const FO_AVATAR            = 31;
    public const FO_INACCURATE        = 32;
    public const FO_OUT_OF_DATE       = 33;
    public const FO_SPAM              = 34;
    public const FO_STICKY_REQUEST    = 35;
    public const FO_INAPPROPRIATE     = 36;
    public const FO_MISCELLANEOUS     = 37;
    public const SS_INACCURATE        = 45;
    public const SS_OUT_OF_DATE       = 46;
    public const SS_INAPPROPRIATE     = 47;
    public const SS_MISCELLANEOUS     = 48;
    public const PR_INACCURATE_DATA   = 60;
    public const PR_MISCELLANEOUS     = 61;
    public const VI_INACCURATE        = 45;
    public const VI_OUT_OF_DATE       = 46;
    public const VI_INAPPROPRIATE     = 47;
    public const VI_MISCELLANEOUS     = 48;
    public const AR_INACCURATE        = 45;
    public const AR_OUT_OF_DATE       = 46;
    public const AR_MISCELLANEOUS     = 48;

    private /* array */ $context = array(
        self::MODE_GENERAL => array(
            self::GEN_FEEDBACK         => true,
            self::GEN_BUG_REPORT       => true,
            self::GEN_TYPO_TRANSLATION => true,
            self::GEN_OP_ADVERTISING   => true,
            self::GEN_OP_PARTNERSHIP   => true,
            self::GEN_PRESS_INQUIRY    => true,
            self::GEN_MISCELLANEOUS    => true,
            self::GEN_MISINFORMATION   => true
        ),
        self::MODE_COMMENT => array(
            self::CO_ADVERTISING   => U_GROUP_MODERATOR,
            self::CO_INACCURATE    => true,
            self::CO_OUT_OF_DATE   => true,
            self::CO_SPAM          => U_GROUP_MODERATOR,
            self::CO_INAPPROPRIATE => U_GROUP_MODERATOR,
            self::CO_MISCELLANEOUS => U_GROUP_MODERATOR
        ),
        self::MODE_FORUM_POST => array(
            self::FO_ADVERTISING    => U_GROUP_MODERATOR,
            self::FO_AVATAR         => true,
            self::FO_INACCURATE     => true,
            self::FO_OUT_OF_DATE    => U_GROUP_MODERATOR,
            self::FO_SPAM           => U_GROUP_MODERATOR,
            self::FO_STICKY_REQUEST => U_GROUP_MODERATOR,
            self::FO_INAPPROPRIATE  => U_GROUP_MODERATOR
        ),
        self::MODE_SCREENSHOT => array(
            self::SS_INACCURATE    => true,
            self::SS_OUT_OF_DATE   => true,
            self::SS_INAPPROPRIATE => U_GROUP_MODERATOR,
            self::SS_MISCELLANEOUS => U_GROUP_MODERATOR
        ),
        self::MODE_CHARACTER => array(
            self::PR_INACCURATE_DATA => true,
            self::PR_MISCELLANEOUS   => true
        ),
        self::MODE_VIDEO => array(
            self::VI_INACCURATE    => true,
            self::VI_OUT_OF_DATE   => true,
            self::VI_INAPPROPRIATE => U_GROUP_MODERATOR,
            self::VI_MISCELLANEOUS => U_GROUP_MODERATOR
        ),
        self::MODE_GUIDE => array(
            self::AR_INACCURATE    => true,
            self::AR_OUT_OF_DATE   => true,
            self::AR_MISCELLANEOUS => true
        )
    );

    private const ERR_NONE             = 0;                 // aka: success
    private const ERR_INVALID_CAPTCHA  = 1;                 // captcha not in use
    private const ERR_DESC_TOO_LONG    = 2;
    private const ERR_NO_DESC          = 3;
    private const ERR_ALREADY_REPORTED = 7;
    private const ERR_MISCELLANEOUS    = -1;

    public  const STATUS_OPEN           = 0;
    public  const STATUS_ASSIGNED       = 1;
    public  const STATUS_CLOSED_WONTFIX = 2;
    public  const STATUS_CLOSED_SOLVED  = 3;

    private /* int */    $mode      = 0;
    private /* int */    $reason    = 0;
    private /* int */    $subject   = 0;

    public  /* readonly int */ $errorCode;


    public function __construct(int $mode, int $reason, int $subject = 0)
    {
        if ($mode < 0 || $reason <= 0 || !$subject)
        {
            trigger_error('Report - malformed contact request received', E_USER_ERROR);
            $this->errorCode = self::ERR_MISCELLANEOUS;
            return;
        }

        if (!isset($this->context[$mode][$reason]))
        {
            trigger_error('Report - report has invalid context (mode:'.$mode.' / reason:'.$reason.')', E_USER_ERROR);
            $this->errorCode = self::ERR_MISCELLANEOUS;
            return;
        }

        if (!User::$id && !User::$ip)
        {
            trigger_error('Report - could not determine IP for anonymous user', E_USER_ERROR);
            $this->errorCode = self::ERR_MISCELLANEOUS;
            return;
        }

        $this->mode    = $mode;
        $this->reason  = $reason;
        $this->subject = $subject;                          // 0 for utility, tools and misc pages?
    }

    private function checkTargetContext() : int
    {
        // check already reported
        $field = User::$id ? 'userId' : 'ip';
        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_reports WHERE `mode` = ?d AND `reason`= ?d AND `subject` = ?d AND ?# = ?', $this->mode, $this->reason, $this->subject, $field, User::$id ?: User::$ip))
            return self::ERR_ALREADY_REPORTED;

        // check targeted post/postOwner staff status
        $ctxCheck = $this->context[$this->mode][$this->reason];
        if (is_int($ctxCheck))
        {
            $roles = User::$groups;
            if ($this->mode == self::MODE_COMMENT)
                $roles = DB::Aowow()->selectCell('SELECT `roles` FROM ?_comments WHERE `id` = ?d', $this->subject);
        //  else if if ($this->mode == self::MODE_FORUM_POST)
        //      $roles = DB::Aowow()->selectCell('SELECT `roles` FROM ?_forum_posts WHERE `id` = ?d', $this->subject);

            return $roles & $ctxCheck ? self::ERR_NONE : self::ERR_MISCELLANEOUS;
        }
        else
            return $ctxCheck ? self::ERR_NONE : self::ERR_MISCELLANEOUS;

        // Forum not in use, else:
        //  check post owner
        //      User::$id == post.op && !post.sticky;
        //  check user custom avatar
        //      g_users[post.user].avatar == 2 && (post.roles & U_GROUP_MODERATOR) == 0
    }

    public function create(string $desc, ?string $userAgent = null, ?string $appName = null, ?string $pageUrl = null, ?string $relUrl = null, ?string $email = null) : bool
    {
        if ($this->errorCode)
            return false;

        if (!$desc)
        {
            $this->errorCode = self::ERR_NO_DESC;
            return false;
        }

        if (mb_strlen($desc) > 500)
        {
            $this->errorCode = self::ERR_DESC_TOO_LONG;
            return false;
        }

        if($err = $this->checkTargetContext())
        {
            $this->errorCode = $err;
            return false;
        }

        $update = array(
            'userId'      => User::$id,
            'createDate'  => time(),
            'mode'        => $this->mode,
            'reason'      => $this->reason,
            'subject'     => $this->subject,
            'ip'          => User::$ip,
            'description' => $desc,
            'userAgent'   => $userAgent ?: $_SERVER['HTTP_USER_AGENT'],
            'appName'     => $appName ?: (get_browser(null, true)['browser'] ?: '')
        );

        if ($pageUrl)
            $update['url'] = $pageUrl;

        if ($relUrl)
            $update['relatedurl'] = $relUrl;

        if ($email)
            $update['email'] = $email;

        return DB::Aowow()->query('INSERT INTO ?_reports (?#) VALUES (?a)', array_keys($update), array_values($update));
    }

    public function getSimilar(int ...$status) : array
    {
        if ($this->errorCode)
            return [];

        foreach ($status as &$s)
            if ($s < self::STATUS_OPEN || $s > self::STATUS_CLOSED_SOLVED)
                unset($s);

        return DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, r.* FROM ?_reports r WHERE {`status` IN (?a) AND }`mode` = ?d AND `reason` = ?d AND `subject` = ?d',
            $status ?: DBSIMPLE_SKIP, $this->mode, $this->reason, $this->subject);
    }

    public function close(int $closeStatus, bool $inclAssigned = false) : bool
    {
        if ($closeStatus != self::STATUS_CLOSED_SOLVED && $closeStatus != self::STATUS_CLOSED_WONTFIX)
            return false;

        if (!User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD))
            return false;

        $fromStatus = [self::STATUS_OPEN];
        if ($inclAssigned)
            $fromStatus[] = self::STATUS_ASSIGNED;

        if ($reports = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `userId` FROM ?_reports WHERE `status` IN (?a) AND `mode` = ?d AND `reason` = ?d AND `subject` = ?d',
            $fromStatus, $this->mode, $this->reason, $this->subject))
        {
            DB::Aowow()->query('UPDATE ?_reports SET `status` = ?d, `assigned` = 0 WHERE `id` IN (?a)', $closeStatus, array_keys($reports));

            foreach ($reports as $rId => $uId)
                Util::gainSiteReputation($uId, $closeStatus == self::STATUS_CLOSED_SOLVED ? SITEREP_ACTION_GOOD_REPORT : SITEREP_ACTION_BAD_REPORT, ['id' => $rId]);

            return true;
        }

        return false;
    }

    public function reopen(int $assignedTo = 0) : bool
    {
        // assignedTo = 0 ? status = STATUS_OPEN : status = STATUS_ASSIGNED, userId = assignedTo
        return false;
    }
}

?>
