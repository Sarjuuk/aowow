<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class CLI
{
    private const CHR_BELL      = 7;
    private const CHR_BACK      = 8;
    private const CHR_TAB       = 9;
    private const CHR_LF        = 10;
    private const CHR_CR        = 13;
    private const CHR_ESC       = 27;
    private const CHR_BACKSPACE = 127;

    public const LOG_NONE       = -1;
    public const LOG_BLANK      = 0;
    public const LOG_ERROR      = LOG_LEVEL_ERROR;
    public const LOG_WARN       = LOG_LEVEL_WARN;
    public const LOG_INFO       = LOG_LEVEL_INFO;
    public const LOG_OK         = 4;

    private static $logHandle   = null;
    private static $hasReadline = null;

    private static $overwriteLast = false;

    /********************/
    /* formatted output */
    /********************/

    public static function writeTable(array $out, bool $timestamp = false, bool $headless = false) : void
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

            $nCols = max($nCols, count($row));

            for ($j = 0; $j < $nCols; $j++)
                $pads[$j] = max($pads[$j] ?? 0, mb_strlen(self::purgeEscapes($row[$j] ?? '')));
        }

        foreach ($out as $i => $row)
        {
            for ($j = 0; $j < $nCols; $j++)
            {
                if (!isset($row[$j]))
                    break;

                $len = ($pads[$j] - mb_strlen(self::purgeEscapes($row[$j])));
                for ($k = 0; $k < $len; $k++)               // can't use str_pad(). it counts invisible chars.
                    $row[$j] .= ' ';
            }

            if ($i || $headless)
                self::write(' '.implode(' ' . self::tblDelim(' ') . ' ', $row), self::LOG_NONE, $timestamp);
            else
                self::write(self::tblHead(' '.implode('   ', $row)), self::LOG_NONE, $timestamp);
        }

        if (!$headless)
            self::write(self::tblHead(str_pad('', array_sum($pads) + count($pads) * 3 - 2)), self::LOG_NONE, $timestamp);

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

    private static function tblHead(string $str) : string
    {
        return CLI_HAS_E ? "\e[1;48;5;236m".$str."\e[0m" : $str;
    }

    private static function tblDelim(string $str) : string
    {
        return CLI_HAS_E ? "\e[48;5;236m".$str."\e[0m" : $str;
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

        // https://shiroyasha.svbtle.com/escape-sequences-a-quick-guide-1#movement_1
        $msg = (self::$overwriteLast && CLI_HAS_E ? "\e[1G\e[0K" : "\n") . $msg;
        self::$overwriteLast = $tmpRow;

        fwrite($lvl == self::LOG_ERROR ? STDERR : STDOUT, $msg);

        if (self::$logHandle)                               // remove control sequences from log
            fwrite(self::$logHandle, self::purgeEscapes($msg));

        flush();
    }

    private static function purgeEscapes(string $msg) : string
    {
        return preg_replace(["/\e\[[\d;]+[mK]/", "/\e\[\d+G/"], ['', "\n"], $msg);
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

        // remove double quotes (from erroneous user input), single quotes are
        // valid chars for filenames and removing those mutilates several wow icons
        $path = str_replace('"', '', $path);

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
            self::write('Dafuq! Your directory separator is "'.DIRECTORY_SEPARATOR.'". Please report this!', self::LOG_ERROR);

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

    public static function read(array $fields, ?array &$userInput = []) : bool
    {
        // first time set
        if (self::$hasReadline === null)
            self::$hasReadline = function_exists('readline_callback_handler_install');

        // prevent default output if able
        if (self::$hasReadline)
            readline_callback_handler_install('', function() { });

        if (!STDIN)
            return false;

        stream_set_blocking(STDIN, false);

        // pad default values onto $fields
        array_walk($fields, function(&$val, $_, $pad) { $val += $pad; }, ['', false, false, '']);

        foreach ($fields as $name => [$desc, $isHidden, $singleChar, $validPattern])
        {
            $charBuff = '';

            if ($desc)
                fwrite(STDOUT, "\n".$desc.": ");

            while (true) {
                if (feof(STDIN))
                    return false;

                $r = [STDIN];
                $w = $e = null;
                $n = stream_select($r, $w, $e, 200000);

                if (!$n || !in_array(STDIN, $r))
                    continue;

                // stream_get_contents is always blocking under WIN - fgets should work similary as php always receives a terminated line of text
                $chars    = str_split(OS_WIN ? fgets(STDIN) : stream_get_contents(STDIN));
                $ordinals = array_map('ord', $chars);

                if ($ordinals[0] == self::CHR_ESC)
                {
                    if (count($ordinals) == 1)
                    {
                        fwrite(STDOUT, chr(self::CHR_BELL));
                        return false;
                    }
                    else
                        continue;
                }

                foreach ($chars as $idx => $char)
                {
                    $keyId = $ordinals[$idx];

                    // skip char if horizontal tab or \r if followed by \n
                    if ($keyId == self::CHR_TAB || ($keyId == self::CHR_CR && ($ordinals[$idx + 1] ?? '') == self::CHR_LF))
                        continue;

                    if ($keyId == self::CHR_BACKSPACE)
                    {
                        if (!$charBuff)
                            continue 2;

                        $charBuff = mb_substr($charBuff, 0, -1);
                        if (!$isHidden && self::$hasReadline)
                            fwrite(STDOUT, chr(self::CHR_BACK)." ".chr(self::CHR_BACK));
                    }
                    // standalone \n or \r
                    else if ($keyId == self::CHR_LF || $keyId == self::CHR_CR)
                    {
                        $userInput[$name] = $charBuff;
                        break 2;
                    }
                    else if (!$validPattern || preg_match($validPattern, $char))
                    {
                        $charBuff .= $char;
                        if (!$isHidden && self::$hasReadline)
                            fwrite(STDOUT, $char);

                        if ($singleChar && self::$hasReadline)
                        {
                            $userInput[$name] = $charBuff;
                            break 2;
                        }
                    }
                }
            }
        }

        fwrite(STDOUT, chr(self::CHR_BELL));

        foreach ($userInput as $ui)
            if (strlen($ui))
                return true;

        $userInput = null;
        return true;
    }
}

?>
