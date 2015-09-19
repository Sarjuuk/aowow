<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


class CLISetup
{
    const CHR_BELL      = 7;
    const CHR_BACK      = 8;
    const CHR_TAB       = 9;
    const CHR_LF        = 10;
    const CHR_CR        = 13;
    const CHR_ESC       = 27;
    const CHR_BACKSPACE = 127;

    const LOG_OK        = 0;
    const LOG_WARN      = 1;
    const LOG_ERROR     = 2;
    const LOG_INFO      = 3;

    private static $win           = true;
    private static $hasReadline   = false;
    private static $logFile       = '';
    private static $logHandle     = null;

    public  static $locales       = [];
    public  static $localeIds     = [];

    public  static $srcDir        = 'setup/mpqdata/';

    public  static $tmpDBC        = false;

    private static $mpqFiles      = [];
    public  static $expectedPaths = array(                  // update paths [yes, you can have en empty string as key]
        ''     => LOCALE_EN,    'enGB' => LOCALE_EN,    'enUS' => LOCALE_EN,
        'frFR' => LOCALE_FR,
        'deDE' => LOCALE_DE,
        'esES' => LOCALE_ES,    'esMX' => LOCALE_ES,
        'ruRU' => LOCALE_RU
    );

    public static function init()
    {
        self::$win         = substr(PHP_OS, 0, 3) == 'WIN';
        self::$hasReadline = function_exists('readline_callback_handler_install');

        if ($_ = getopt('d', ['log::',   'locales::', 'mpqDataDir::', 'delete']))
        {
            // optional logging
            if (!empty($_['log']))
                self::$logFile = trim($_['log']);

            // alternative data source (no quotes, use forward slash)
            if (!empty($_['mpqDataDir']))
                self::$srcDir = str_replace(['\\', '"', '\''], ['/', '', ''], $_['mpqDataDir']);

            // optional limit handled locales
            if (!empty($_['locales']))
            {
                // engb and enus are identical for all intents and purposes
                $from = ['engb', 'esmx'];
                $to   = ['enus', 'eses'];
                $_['locales'] = str_ireplace($from, $to, strtolower($_['locales']));

                self::$locales = array_intersect(Util::$localeStrings, explode(',', $_['locales']));
            }

            if (isset($_['d']) || isset($_['delete']))
                self::$tmpDBC = true;
        }

        if (!self::$locales)
            self::$locales = array_filter(Util::$localeStrings);

        // restrict actual locales
        foreach (self::$locales as $idx => $str)
            if (!defined('CFG_LOCALES') || CFG_LOCALES & (1 << $idx))
                self::$localeIds[] = $idx;
    }

    /*******************/
    /* MPQ-file access */
    /*******************/

    /*  the problem
        1) paths provided in dbc files are case-insensitive and random
        2) paths to the actual textures contained in the mpq archives are case-insensitive and random
        unix systems will throw a fit if you try to get from one to the other, so lets save the paths from 2) and cast it to lowercase
        lookups will be done in lowercase. A successfull match will return the real path.
    */
    private static function buildFileList()
    {
        self::log();
        self::log('reading MPQdata from '.self::$srcDir.' to list for first time use...');

        $setupDirs = glob('setup/*');
        foreach ($setupDirs as $sd)
        {
            if (mb_substr(self::$srcDir, -1) == '/')
                self::$srcDir = mb_substr(self::$srcDir, 0, -1);

            if (mb_substr($sd, -1) == '/')
                $sd = mb_substr($sd, 0, -1);

            if (Util::lower($sd) == Util::lower(self::$srcDir))
            {
                self::$srcDir = $sd.'/';
                break;
            }
        }

        try
        {
            $iterator = new RecursiveDirectoryIterator(self::$srcDir);
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

            foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST) as $path)
            {
                $_ = str_replace('\\', '/', $path->getPathname());
                self::$mpqFiles[strtolower($_)] = $_;
            }

            self::log('done');
            self::log();
        }
        catch (UnexpectedValueException $e)
        {
            self::log('- mpqData dir '.self::$srcDir.' does not exist', self::LOG_ERROR);
            return false;
        }

        return true;
    }

    public static function fileExists(&$file)
    {
        // read mpq source file structure to tree
        if (!self::$mpqFiles)
            if (!self::buildFileList())
                return false;

        // backslash to forward slash
        $_ = strtolower(str_replace('\\', '/', $file));

        // remove trailing slash
        if (mb_substr($_, -1, 1) == '/')
            $_ = mb_substr($_, 0, -1);

        if (isset(self::$mpqFiles[$_]))
        {
            $file = self::$mpqFiles[$_];
            return true;
        }

        return false;
    }

    public static function filesInPath($path, $useRegEx = false)
    {
        $result = [];

        // read mpq source file structure to tree
        if (!self::$mpqFiles)
            if (!self::buildFileList())
                return [];

        // backslash to forward slash
        $_ = strtolower(str_replace('\\', '/', $path));

        foreach (self::$mpqFiles as $lowerFile => $realFile)
        {
            if (!$useRegEx && strstr($lowerFile, $_))
                $result[] = $realFile;
            else if ($useRegEx && preg_match($path, $lowerFile))
                $result[] = $realFile;
        }

        return $result;
    }

    /***********/
    /* logging */
    /***********/

    public static function red($str)
    {
        return "\e[31m".$str."\e[0m";
    }

    public static function green($str)
    {
        return "\e[32m".$str."\e[0m";
    }

    public static function yellow($str)
    {
        return "\e[33m".$str."\e[0m";
    }

    public static function blue($str)
    {
        return "\e[36m".$str."\e[0m";
    }

    public static function bold($str)
    {
        return "\e[1m".$str."\e[0m";
    }

    public static function log($txt = '', $lvl = -1)
    {
        if (self::$logFile && !self::$logHandle)
        {
            if (!file_exists(self::$logFile))
                self::$logHandle = fopen(self::$logFile, 'w');
            else
            {
                $logFileParts = pathinfo(self::$logFile);

                $i = 1;
                while (file_exists($logFileParts['dirname'].'/'.$logFileParts['filename'].$i.(isset($logFileParts['extension']) ? '.'.$logFileParts['extension'] : '')))
                    $i++;

                self::$logFile   = $logFileParts['dirname'].'/'.$logFileParts['filename'].$i.(isset($logFileParts['extension']) ? '.'.$logFileParts['extension'] : '');
                self::$logHandle = fopen(self::$logFile, 'w');
            }
        }

        $msg = "\n";
        if ($txt)
        {
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
                default:
                    $msg .= '        ';
            }

            $msg .= $txt."\n";
        }

        // remove highlights for logging & win
        $raw = preg_replace(["/\e\[\d+m/", "/\e\[0m/"], '', $msg);

        echo self::$win ? $raw : $msg;

        if (self::$logHandle)
            fwrite(self::$logHandle, $raw);

        flush();
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
            {
                $success = true;
                self::log(sprintf(ERR_NONE, self::bold($file)), self::LOG_OK);
            }
            else
                self::log(sprintf(ERR_WRITE_FILE, self::bold($file)), self::LOG_ERROR);

            fClose($handle);
        }
        else
            self::log(sprintf(ERR_CREATE_FILE, self::bold($file)), self::LOG_ERROR);

        if ($success)
            @chmod($file, Util::FILE_ACCESS);

        return $success;
    }

    public static function writeDir($dir)
    {
        if (is_dir($dir))
        {
            if (!is_writable($dir) && !@chmod($dir, Util::FILE_ACCESS))
                self::log('cannot write into output directory '.$dir, self::LOG_ERROR);

            return is_writable($dir);
        }

        if (@mkdir($dir, Util::FILE_ACCESS, true))
            return true;

        self::log('could not create output directory '.$dir, self::LOG_ERROR);
        return false;
    }

    public static function loadDBC($name)
    {
        if (DB::Aowow()->selectCell('SHOW TABLES LIKE ?', 'dbc_'.$name) && DB::Aowow()->selectCell('SELECT count(1) FROM ?#', 'dbc_'.$name))
            return true;

        $dbc = new DBC($name, self::$tmpDBC);
        if ($dbc->error)
            return false;

        if ($dbc->readFromFile())
        {
            $dbc->writeToDB();
            return true;
        }

        self::log('SqlGen::generate() - required DBC '.$name.'.dbc found neither in DB nor as file!', self::LOG_ERROR);
        return false;
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

    public static function readInput(&$fields, $singleChar = false)
    {
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
                    if ($keyId == self::CHR_CR && self::$win)
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

?>
