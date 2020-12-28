<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class CLISetup
{
    public  static $locales       = [];
    public  static $localeIds     = [];

    public  static $srcDir        = 'setup/mpqdata/';

    public  static $tmpDBC        = false;

    private static $mpqFiles      = [];
    public  static $expectedPaths = array(                  // update paths [yes, you can have en empty string as key]
        ''     => LOCALE_EN,    'enGB' => LOCALE_EN,    'enUS' => LOCALE_EN,
        'frFR' => LOCALE_FR,
        'deDE' => LOCALE_DE,
        'zhCN' => LOCALE_CN,    'enCN' => LOCALE_CN,
        'esES' => LOCALE_ES,    'esMX' => LOCALE_ES,
        'ruRU' => LOCALE_RU
    );

    public static function init()
    {
        if ($_ = getopt('d', ['log::',   'locales::', 'mpqDataDir::', 'delete']))
        {
            // optional logging
            if (!empty($_['log']))
                CLI::initLogFile(trim($_['log']));

            // alternative data source (no quotes, use forward slash)
            if (!empty($_['mpqDataDir']))
                self::$srcDir = CLI::nicePath($_['mpqDataDir']);

            // optional limit handled locales
            if (!empty($_['locales']))
            {
                // engb and enus are identical for all intents and purposes
                $from = ['engb', 'esmx', 'encn'];
                $to   = ['enus', 'eses', 'zhcn'];
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
        CLI::write();
        CLI::write('reading MPQdata from '.self::$srcDir.' to list for first time use...');

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

            CLI::write('done');
            CLI::write();
        }
        catch (UnexpectedValueException $e)
        {
            CLI::write('- mpqData dir '.self::$srcDir.' does not exist', CLI::LOG_ERROR);
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


    /*****************/
    /* file handling */
    /*****************/

    public static function writeFile($file, $content)
    {
        if (Util::writeFile($file, $content))
        {
            CLI::write(sprintf(ERR_NONE, CLI::bold($file)), CLI::LOG_OK);
            return true;
        }

        $e = error_get_last();
        CLI::write($e['message'].' '.CLI::bold($file), CLI::LOG_ERROR);
        return false;
    }

    public static function writeDir($dir)
    {
        if (Util::writeDir($dir))
            return true;

        CLI::write(error_get_last()['message'].' '.CLI::bold($dir), CLI::LOG_ERROR);
        return false;
    }

    public static function loadDBC($name)
    {
        if (DB::Aowow()->selectCell('SHOW TABLES LIKE ?', 'dbc_'.$name) && DB::Aowow()->selectCell('SELECT count(1) FROM ?#', 'dbc_'.$name))
            return true;

        $dbc = new DBC($name, ['temporary' => self::$tmpDBC]);
        if ($dbc->error)
        {
            CLI::write('CLISetup::loadDBC() - required DBC '.$name.'.dbc not found!', CLI::LOG_ERROR);
            return false;
        }

        if (!$dbc->readFile())
        {
            CLI::write('CLISetup::loadDBC() - DBC '.$name.'.dbc could not be written to DB!', CLI::LOG_ERROR);
            return false;
        }

        return true;
    }
}

?>
