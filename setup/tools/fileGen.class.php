<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');



class FileGen
{
    const MODE_NORMAL   = 0;
    const MODE_FIRSTRUN = 1;
    const MODE_UPDATE   = 2;

    public  static $tplPath = 'setup/tools/filegen/templates/';

    public  static $cliOpts   = [];
    private static $shortOpts = 'fh';
    private static $longOpts  = array(
        'build::', 'help',      'force',     'sync:',                       // general
        'icons',   'glyphs',    'pagetexts', 'loadingscreens',              // whole images
        'artwork', 'talentbgs', 'maps',      'spawn-maps',     'area-maps'  // images from image parts
    );

    public static $subScripts = [];
    public static $tplFiles   = array(
        'searchplugin'    => ['aowow.xml',      'static/download/searchplugins/', []],
        'power'           => ['power.js',       'static/widgets/',                []],
        'searchboxScript' => ['searchbox.js',   'static/widgets/',                []],
        'demo'            => ['demo.html',      'static/widgets/power/',          []],
        'searchboxBody'   => ['searchbox.html', 'static/widgets/searchbox/',      []],
        'realmMenu'       => ['profile_all.js', 'static/js/',                     ['realmlist']],
        'locales'         => ['locale.js',      'static/js/',                     []],
        'itemScaling'     => ['item-scaling',   'datasets/',                      []]
    );
    public static $datasets   = array(                      // name => [AowowDeps, TCDeps]
        'realms'        => [null, ['realmlist']],
        'statistics'    => [null, ['player_levelstats', 'player_classlevelstats']],
        'simpleImg'     => [null, null],
        'complexImg'    => [null, null],
        'talentCalc'    => [null, null],
        'pets'          => [['spawns', 'creature'], null],
        'talentIcons'   => [null, null],
        'glyphs'        => [['items', 'spell'], null],
        'itemsets'      => [['itemset', 'spell'], null],
        'enchants'      => [['items', 'spell', 'itemenchantment'], null],
        'gems'          => [['items', 'spell', 'itemenchantment'], null],
        'profiler'      => [['quests', 'quests_startend', 'spell', 'currencies', 'achievement', 'titles'], null],
        'weightPresets' => [null, null]
    );

    public  static $defaultExecTime = 30;

    private static $reqDirs   = array(
        'static/uploads/screenshots/normal',
        'static/uploads/screenshots/pending',
        'static/uploads/screenshots/resized',
        'static/uploads/screenshots/temp',
        'static/uploads/screenshots/thumb',
        'static/uploads/temp/',
        'static/download/searchplugins/'
    );

    public static $txtConstants = array(
        'CFG_NAME'       => CFG_NAME,
        'CFG_NAME_SHORT' => CFG_NAME_SHORT,
        'HOST_URL'       => HOST_URL,
        'STATIC_URL'     => STATIC_URL
    );

    public static function init($mode = self::MODE_NORMAL, array $updScripts = [])
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        $doScripts = null;

        if (getopt(self::$shortOpts, self::$longOpts) || $mode == self::MODE_FIRSTRUN)
            self::handleCLIOpts($doScripts);
        else if ($mode != self::MODE_UPDATE)
        {
            self::printCLIHelp();
            exit;
        }

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_merge(array_keys(self::$tplFiles), array_keys(self::$datasets));
        if ($doScripts || $updScripts)
            self::$subScripts = array_intersect($doScripts ?: $updScripts, self::$subScripts);
        else if ($doScripts === null)
            self::$subScripts = [];

        if (!CLISetup::$localeIds /* todo: && this script has localized text */)
        {
            CLISetup::log('No valid locale specified. Check your config or --locales parameter, if used', CLISetup::LOG_ERROR);
            exit;
        }

        // create directory structure
        CLISetup::log('FileGen::init() - creating required directories');
        $pathOk = 0;
        foreach (self::$reqDirs as $rd)
            if (CLISetup::writeDir($rd))
                $pathOk++;

        CLISetup::log('created '.$pathOk.' extra paths'.($pathOk == count(self::$reqDirs) ? '' : ' with errors'));
        CLISetup::log();
    }

    private static function handleCLIOpts(&$doScripts)
    {
        $_ = getopt(self::$shortOpts, self::$longOpts);

        if ((isset($_['help']) || isset($_['h'])) && empty($_['build']))
        {
            self::printCLIHelp();
            exit;
        }

        // required subScripts
        if (!empty($_['sync']))
        {
            $sync = explode(',', $_['sync']);
            foreach (self::$tplFiles as $name => $info)
                if (!empty($info[2]) && array_intersect($sync, $info[2]))
                    $doScripts[] = $name;

            foreach (self::$datasets as $name => $info)
            {
                // recursive deps from SqlGen
                if (!empty($info[0]) && array_intersect(SqlGen::$subScripts, $info[0]))
                    $doScripts[] = $name;
                else if (!empty($info[1]) && array_intersect($sync, $info[1]))
                    $doScripts[] = $name;
            }

            $doScripts = $doScripts ? array_unique($doScripts) : null;
        }
        else if (!empty($_['build']))
            $doScripts = explode(',', $_['build']);

        // optional, overwrite existing files
        if (isset($_['f']))
            self::$cliOpts['force'] = true;

        if (isset($_['h']))
            self::$cliOpts['help'] = true;

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

    public static function printCLIHelp()
    {
        echo "\nusage: php aowow --build=<subScriptList,> [-h --help] [-f --force]\n\n";
        echo "--build    : available subScripts:\n";
        foreach (array_merge(array_keys(self::$tplFiles), array_keys(self::$datasets)) as $s)
        {
            echo " * ".str_pad($s, 20).str_pad(isset(self::$tplFiles[$s]) ? self::$tplFiles[$s][1].self::$tplFiles[$s][0] : 'static data file', 45).
                (!empty(self::$tplFiles[$s][2]) ? ' - TC deps: '.implode(', ', self::$tplFiles[$s][2]) : (!empty(self::$datasets[$s][1]) ? ' - TC deps: '.implode(', ', self::$datasets[$s][1]) : '')).
                (!empty(self::$datasets[$s][0]) ? ' - Aowow deps: '.implode(', ', self::$datasets[$s][0]) : '')."\n";
        }

        echo "-h --help  : shows this info\n";
        echo "-f --force : enforces overwriting existing files\n";
    }

    public static function generate($key, array $updateIds = [])
    {
        $success = false;
        $reqDBC = [];

        if (file_exists('setup/tools/filegen/'.$key.'.func.php'))
            require_once 'setup/tools/filegen/'.$key.'.func.php';
        else if (empty(self::$tplFiles[$key]))
        {
            CLISetup::log(sprintf(ERR_MISSING_INCL, $key, 'setup/tools/filegen/'.$key.'.func.php', CLISetup::LOG_ERROR));
            return false;
        }

        CLISetup::log('FileGen::generate() - gathering data for '.$key);

        if (!empty(self::$tplFiles[$key]))
        {
            list($file, $destPath, $deps) = self::$tplFiles[$key];

            if ($content = file_get_contents(FileGen::$tplPath.$file.'.in'))
            {
                if ($dest = @fOpen($destPath.$file, "w"))
                {
                    // replace constants
                    $content = strtr($content, FileGen::$txtConstants);

                    // check for required auxiliary DBC files
                    foreach ($reqDBC as $req)
                        if (!CLISetup::loadDBC($req))
                            continue;

                    // must generate content
                    // PH format: /*setup:<setupFunc>*/
                    $funcOK = true;
                    if (preg_match_all('/\/\*setup:([\w\-_]+)\*\//i', $content, $m))
                    {
                        foreach ($m[1] as $func)
                        {
                            if (function_exists($func))
                                $content = str_replace('/*setup:'.$func.'*/', $func(), $content);
                            else
                            {
                                $funcOK = false;
                                CLISetup::log('No function for was registered for placeholder '.$func.'().', CLISetup::LOG_ERROR);
                                if (!array_reduce(get_included_files(), function ($inArray, $itr) use ($func) { return $inArray || false !== strpos($itr, $func); }, false))
                                    CLISetup::log('Also, expected include setup/tools/filegen/'.$name.'.func.php was not found.');
                            }
                        }
                    }

                    if (fWrite($dest, $content))
                    {
                        CLISetup::log(sprintf(ERR_NONE, CLISetup::bold($destPath.$file)), CLISetup::LOG_OK);
                        if ($content && $funcOK)
                            $success = true;
                    }
                    else
                        CLISetup::log(sprintf(ERR_WRITE_FILE, CLISetup::bold($destPath.$file)), CLISetup::LOG_ERROR);

                    fClose($dest);
                }
                else
                    CLISetup::log(sprintf(ERR_CREATE_FILE, CLISetup::bold($destPath.$file)), CLISetup::LOG_ERROR);
            }
            else
                CLISetup::log(sprintf(ERR_READ_FILE, CLISetup::bold(FileGen::$tplPath.$file.'.in')), CLISetup::LOG_ERROR);
        }
        else if (!empty(self::$datasets[$key]))
        {
            if (function_exists($key))
            {
                // check for required auxiliary DBC files
                foreach ($reqDBC as $req)
                    if (!CLISetup::loadDBC($req))
                        return false;

                $success = $key($updateIds);
            }
            else
                CLISetup::log(' - subscript \''.$key.'\' not defined in included file', CLISetup::LOG_ERROR);
        }

        set_time_limit(FileGen::$defaultExecTime);      // reset to default for the next script

        return $success;
    }

}

?>