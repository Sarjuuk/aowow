<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// shared strings
define('ERR_CREATE_FILE',  'could not create file at destination %s'        );
define('ERR_WRITE_FILE',   'could not write to file at destination %s'      );
define('ERR_READ_FILE',    'file %s could not be read'                      );
define('ERR_MISSING_FILE', 'file %s not found'                              );
define('ERR_NONE',         'created file %s'                                );
define('ERR_MISSING_INCL', 'required function %s() could not be found at %s');

define('MSG_LVL_OK',    0);
define('MSG_LVL_WARN',  1);
define('MSG_LVL_ERROR', 2);


class FileGen
{
    public static $success = false;

    public  static $srcDir  = 'setup/mpqData/';
    public  static $tplPath = 'setup/tools/filegen/templates/';

    // update paths [yes, you can have en empty string as key]
    public static $expectedPaths = array(
        'frFR' => 2,
        'deDE' => 3,
        'esES' => 6,    'esMX' => 6,
        'ruRU' => 8,
        ''     => 0,    'enGB' => 0,    'enUS' => 0,
    );

    public  static $cliOpts   = [];
    private static $shortOpts = 'fh';
    private static $longOpts  = array(
        'build:',  'log::',     'help',      'locales::',      'force',     'mpqDataDir::',  // general
        'icons',   'glyphs',    'pagetexts', 'loadingscreens',                               // whole images
        'artwork', 'talentbgs', 'maps',      'spawn-maps',     'area-maps'                   // images from image parts
    );

    public static $subScripts = [];
    public static $tplFiles   = array(
        'searchplugin'    => ['aowow.xml',      'static/download/searchplugins/'],
        'power'           => ['power.js',       'static/widgets/'               ],
        'searchboxScript' => ['searchbox.js',   'static/widgets/'               ],
        'demo'            => ['demo.html',      'static/widgets/power/'         ],
        'searchboxBody'   => ['searchbox.html', 'static/widgets/searchbox/'     ],
        'realmMenu'       => ['profile_all.js', 'static/js/'                    ],
        'locales'         => ['locale.js',      'static/js/'                    ],
    //  'itemScaling      => ['item-scaling',   'datasets/'                     ],  # provided 'as is', as dbc-content doesn't usualy change
    );
    public static $datasets   = array(
        'pets',         'simpleImg',        'complexImg',
        'realms',       'statistics',       'profiler',     // profiler related
        'talents',      'talentIcons',      'glyphs',       // talentCalc related
        'itemsets',     'enchants',         'gems'          // comparison related
    );

    public  static $defaultExecTime = 30;

    public  static $accessMask = 0755;

    private static $logFile   = '';
    private static $logHandle = null;

    private static $mpqFiles  = [];

    private static $locales   = [];
    public  static $localeIds = [];

    private static $reqDirs   = array(
        'static/uploads/screenshots/normal',
        'static/uploads/screenshots/pending',
        'static/uploads/screenshots/resized',
        'static/uploads/screenshots/temp',
        'static/uploads/screenshots/thumb',
        'static/uploads/temp/'
    );

    public static $txtConstants = array(
        'CFG_NAME'       => CFG_NAME,
        'CFG_NAME_SHORT' => CFG_NAME_SHORT,
        'HOST_URL'       => HOST_URL,
        'STATIC_URL'     => STATIC_URL
    );

    public static function init($scriptList = '')
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        $doScripts = [];
        if (CLI)
        {
            if (getopt(self::$shortOpts, self::$longOpts))
                self::handleCLIOpts($doScripts);
            else
            {
                self::printCLIHelp(array_merge(array_keys(self::$tplFiles), self::$datasets));
                exit;
            }
        }
        else
        {
            $doScripts     = explode(',', $scriptList);
            self::$locales = Util::$localeStrings;
        }

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_merge(array_keys(self::$tplFiles), self::$datasets);
        if ($doScripts)
            self::$subScripts = array_intersect($doScripts, self::$subScripts);

        // restrict actual locales
        foreach (self::$locales as $idx => $str)
        {
            if (!$str)
                continue;

            if (!defined('CFG_LOCALES'))
                self::$localeIds[] = $idx;
            else if (CFG_LOCALES & (1 << $idx))
                self::$localeIds[] = $idx;
        }

        if (!self::$localeIds)
        {
            self::status('No valid locale specified. Check your config or --locales parameter, if used', MSG_LVL_ERROR);
            exit;
        }

        // create directory structure
        self::status('FileGen::init() - creating required directories');
        $pathOk = 0;
        foreach (self::$reqDirs as $rd)
            if (self::writeDir($rd))
                $pathOk++;

        FileGen::status('created '.$pathOk.' extra paths'.($pathOk == count(self::$reqDirs) ? '' : ' with errors'));
        FileGen::status();
    }

    // shared funcs
    public static function writeFile($file, $content)
    {
        $success = false;
        if ($handle = @fOpen($file, "w"))
        {
            if (fWrite($handle, $content))
            {
                $success = true;
                self::status(sprintf(ERR_NONE, $file), MSG_LVL_OK);
            }
            else
                self::status(sprintf(ERR_WRITE_FILE, $file), MSG_LVL_ERROR);

            fClose($handle);
        }
        else
            self::status(sprintf(ERR_CREATE_FILE, $file), MSG_LVL_ERROR);

        if ($success)
            @chmod($file, FileGen::$accessMask);

        return $success;
    }

    public static function writeDir($dir)
    {
        if (is_dir($dir))
        {
            if (!is_writable($dir) && !@chmod($dir, FileGen::$accessMask))
                self::status('cannot write into output directory '.$dir, MSG_LVL_ERROR);

            return is_writable($dir);
        }

        if (@mkdir($dir, FileGen::$accessMask, true))
            return true;

        self::status('could not create output directory '.$dir, MSG_LVL_ERROR);
        return false;
    }

    public static function status($txt = '', $lvl = -1)
    {
        if (isset(self::$cliOpts['help']))
            return;

        $cliColor  = "\033[%sm%s\033[0m";
        $htmlColor = '<span style="color:%s;">%s</span>';

        if (self::$logFile && !self::$logHandle)
        {
            if (!file_exists(self::$logFile))
                self::$logHandle = fopen(self::$logFile, 'w');
            else
            {
                $logFileParts = pathinfo(self::$logFile);

                $i = 1;
                while (file_exists($logFileParts['dirname'].'/'.$logFileParts['filename'].$i.'.'.$logFileParts['extension']))
                    $i++;

                self::$logFile   = $logFileParts['dirname'].'/'.$logFileParts['filename'].$i.'.'.$logFileParts['extension'];
                self::$logHandle = fopen(self::$logFile, 'w');
            }
        }

        $msg = $raw = "\n";
        if ($txt)
        {
            $msg = $raw = str_pad(date('H:i:s'), 10);
            switch ($lvl)
            {
                case MSG_LVL_ERROR:                             // red      error
                    $str  = 'Error:  ';
                    $raw .= $str;
                    $msg .= CLI ? sprintf($cliColor, '0;31', $str) : sprintf($htmlColor, 'darkred', $str);
                    break;
                case MSG_LVL_WARN:                              // yellow   warn
                    $str  = 'Notice: ';
                    $raw .= $str;
                    $msg .= CLI ? sprintf($cliColor, '0;33', $str) : sprintf($htmlColor, 'orange', $str);
                    break;
                case MSG_LVL_OK:                                // green    success
                    $str  = 'Success:';
                    $raw  = $raw . $str;
                    $msg .= CLI ? sprintf($cliColor, '0;32', $str) : sprintf($htmlColor, 'darkgreen', $str);
                    break;
                default:
                    $msg .= '        ';
                    $raw .= '        ';
            }

            $msg .= ' '.$txt."\n";
            $raw .= ' '.$txt."\n";
        }

        if (CLI)
        {
            // maybe for future use: writing \x08 deletes the last char, use to repeatedly update single line (and even WIN should be able to handle it)

            echo substr(PHP_OS, 0, 3) == 'WIN' ? $raw : $msg;

            if (self::$logHandle)
                fwrite(self::$logHandle, $raw);

            @ob_flush();
            flush();
            @ob_end_flush();
        }
        else
            echo "<pre>".$msg."</pre>\n";
    }

    private static function handleCLIOpts(&$doScripts)
    {
        $_ = getopt(self::$shortOpts, self::$longOpts);

        if ((isset($_['help']) || isset($_['h'])) && empty($_['build']))
        {
            self::printCLIHelp(array_merge(array_keys(self::$tplFiles), self::$datasets));
            exit;
        }

        // required subScripts
        if (!empty($_['build']))
            $doScripts = explode(',', $_['build']);

        // optional logging
        if (!empty($_['log']))
            self::$logFile = trim($_['log']);

        // optional, overwrite existing files
        if (isset($_['f']))
            self::$cliOpts['force'] = true;

        // alternative data source (no quotes, use forward slash)
        if (!empty($_['mpqDataDir']))
            self::$srcDir = str_replace(['\\', '"', '\''], ['/', '', ''], $_['mpqDataDir']);

        if (isset($_['h']))
            self::$cliOpts['help'] = true;

        // optional limit handled locales
        if (!empty($_['locales']))
        {
            // engb and enus are identical for all intents and purposes
            $from = ['engb', 'esmx'];
            $to   = ['enus', 'eses'];
            $_['locales'] = str_replace($from, $to, strtolower($_['locales']));

            self::$locales = array_intersect(Util::$localeStrings, explode(',', $_['locales']));
        }
        else
            self::$locales = Util::$localeStrings;

        // mostly build-instructions from longOpts
        foreach (self::$longOpts as $opt)
            if (!strstr($opt, ':') && isset($_[$opt]))
                self::$cliOpts[$opt] = true;
    }

    public static function hasOpt(/* ...$opt */)
    {
        $result = 0x0;
        foreach (func_get_args() as $idx => $arg)
        {
            if (!is_string($arg))
                continue;

            if (isset(self::$cliOpts[$arg]))
                $result |= (1 << $idx);
        }

        return $result;
    }

    /*  the problem
        1) paths provided in dbc files are case-insensitive and random
        2) paths to the actual textures contained in the mpq archives are case-insensitive and random
        unix systems will throw a fit if you try to get from one to the other, so lets save the paths from 2) and cast it to lowecase
        lookups will be done in lowercase. A successfull match will return the real path.
    */
    private static function buildFileList()
    {
        self::status('FileGen::init() - reading MPQdata from '.self::$srcDir.' to list for first time use...');

        $setupDirs = glob('setup/*');
        foreach ($setupDirs as $sd)
        {
            if (substr(self::$srcDir, -1) == '/')
                self::$srcDir = substr(self::$srcDir, 0, -1);

            if (substr($sd, -1) == '/')
                $sd = substr($sd, 0, -1);

            if (strtolower($sd) == strtolower(self::$srcDir))
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

            self::status('done');
            self::status();
        }
        catch (UnexpectedValueException $e) { self::status('- mpqData dir '.self::$srcDir.' does not exist', MSG_LVL_ERROR); }
    }

    public static function fileExists(&$file)
    {
        // read mpq source file structure to tree
        if (!self::$mpqFiles)
            self::buildFileList();

        // backslash to forward slash
        $_ = strtolower(str_replace('\\', '/', $file));

        // remove trailing slash
        if (substr($_, -1, 1) == '/')
            $_ = substr($_, 0, -1);

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
            self::buildFileList();

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

    public static function printCLIHelp($scripts)
    {
        echo "usage: php index.php --build=<subScriptList,> [-h --help] [--log logfile] [-f --force] [--mpqDataDir=path/to/mpqData/] [--locales=<regionCodes,>]\n\n";
        echo "build      -> available subScripts:\n";
        foreach ($scripts as $s)
            echo " - ".str_pad($s, 20).(isset(self::$tplFiles[$s]) ? self::$tplFiles[$s][1].self::$tplFiles[$s][0] : 'static data file')."\n";

        echo "help       -> shows this info\n";
        echo "log        -> writes ouput to file\n";
        echo "force      -> enforces overwriting existing files\n";
        echo "locales    -> limits setup to enUS, frFR, deDE, esES and/or ruRU (does not override config settings)\n";
        echo "mpqDataDir -> manually point to directory with extracted mpq data (default: setup/mpqData/)\n";
    }
}

?>