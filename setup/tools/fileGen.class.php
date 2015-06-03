<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');



class FileGen
{
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
        'realms'      => [null, ['realmlist']],
        'statistics'  => [null, ['player_levelstats', 'player_classlevelstats']],
        'simpleImg'   => [null, null],
        'complexImg'  => [null, null],
        'talents'     => [null, null],
        'pets'        => [['spawns', 'creature'], null],
        'talentIcons' => [null, null],
        'glyphs'      => [['items', 'spell'], null],
        'itemsets'    => [['itemset'], null],
        'enchants'    => [['items'], null],
        'gems'        => [['items'], null],
        'profiler'    => [['quests', 'quests_startend', 'spell', 'currencies', 'achievement', 'titles'], null]
    );

    public  static $defaultExecTime = 30;

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

    public static function init()
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        $doScripts = [];

        if (getopt(self::$shortOpts, self::$longOpts))
            self::handleCLIOpts($doScripts);
        else
        {
            self::printCLIHelp();
            exit;
        }

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_merge(array_keys(self::$tplFiles), array_keys(self::$datasets));
        if ($doScripts)
            self::$subScripts = array_intersect($doScripts, self::$subScripts);

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

            $doScripts = array_unique($doScripts);
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

    public static function generate($file, array $updateIds = [])
    {
        $success = false;

        if (file_exists('setup/tools/filegen/'.$file.'.func.php'))
        {
            $reqDBC = [];

            CLISetup::log('FileGen::generate() - gathering data for '.$file);

            require_once 'setup/tools/filegen/'.$file.'.func.php';

            if (function_exists($file))
            {
                // check for required auxiliary DBC files
                foreach ($reqDBC as $req)
                    if (!CLISetup::loadDBC($req))
                        return false;

                $success = $file($updateIds);
            }
            else
                CLISetup::log(' - subscript \''.$file.'\' not defined in included file', CLISetup::LOG_ERROR);

            set_time_limit(FileGen::$defaultExecTime);      // reset to default for the next script
        }
        else
            CLISetup::log(sprintf(ERR_MISSING_INCL, $file, 'setup/tools/filegen/'.$file.'.func.php', CLISetup::LOG_ERROR));

        return $success;
    }

}

?>