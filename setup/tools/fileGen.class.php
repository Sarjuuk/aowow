<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class FileGen
{
    const MODE_NORMAL   = 1;
    const MODE_FIRSTRUN = 2;
    const MODE_UPDATE   = 3;

    private static $mode = 0;

    public  static $tplPath = 'setup/tools/filegen/templates/';

    public static $subScripts = [];
    public static $tplFiles   = array(                      // name => [file, path, TCDeps]
        'searchplugin'    => ['aowow.xml',      'static/download/searchplugins/', []],
        'power'           => ['power.js',       'static/widgets/',                []],
        'searchboxScript' => ['searchbox.js',   'static/widgets/',                []],
        'demo'            => ['demo.html',      'static/widgets/power/',          []],
        'searchboxBody'   => ['searchbox.html', 'static/widgets/searchbox/',      []],
        'realmMenu'       => ['profile_all.js', 'static/js/',                     ['realmlist']],
        'locales'         => ['locale.js',      'static/js/',                     []],
        'markup'          => ['Markup.js',      'static/js/',                     []],
        'itemScaling'     => ['item-scaling',   'datasets/',                      []]
    );
    public static $datasets   = array(                      // name => [AowowDeps, TCDeps, info]
        'realms'        => [null, ['realmlist'],                                                                  'datasets/realms'],
        'statistics'    => [null, ['player_levelstats', 'player_classlevelstats'],                                'datasets/statistics'],
        'simpleImg'     => [null, null,                                                                           'static/images/wow/[icons, Interface, ]/*'],
        'complexImg'    => [null, null,                                                                           'static/images/wow/[maps, talents/backgrounds, ]/*'],
        'talentCalc'    => [null, null,                                                                           'datasets/<locale>/talents-*'],
        'pets'          => [['spawns', 'creature'], null,                                                         'datasets/<locale>/pets'],
        'talentIcons'   => [null, null,                                                                           'static/images/wow/talents/icons/*'],
        'glyphs'        => [['items', 'spell'], null,                                                             'datasets/<locale>/glyphs'],
        'itemsets'      => [['itemset', 'spell'], null,                                                           'datasets/<locale>/itemsets'],
        'enchants'      => [['items', 'spell', 'itemenchantment'], null,                                          'datasets/<locale>/enchants'],
        'gems'          => [['items', 'spell', 'itemenchantment'], null,                                          'datasets/<locale>/gems'],
        'profiler'      => [['quests', 'quests_startend', 'spell', 'currencies', 'achievement', 'titles'], null,  'datasets/<locale>/p-*'],
        'weightPresets' => [null, null,                                                                           'datasets/weight-presets'],
        'soundfiles'    => [['sounds'], null,                                                                     'static/wowsounds/*']
    );

    public  static $defaultExecTime = 30;

    private static $reqDirs   = array(
        'static/uploads/screenshots/normal/',
        'static/uploads/screenshots/pending/',
        'static/uploads/screenshots/resized/',
        'static/uploads/screenshots/temp/',
        'static/uploads/screenshots/thumb/',
        'static/uploads/temp/',
        'static/uploads/guide/images/',
        'static/download/searchplugins/',
        'static/wowsounds/'
    );

    private static $txtConstants = array(
        'CFG_NAME'          => '',
        'CFG_NAME_SHORT'    => '',
        'CFG_CONTACT_EMAIL' => '',
        'HOST_URL'          => '',
        'STATIC_URL'        => ''
    );

    public static function init(int $mode = self::MODE_NORMAL, array $updScripts = []) : bool
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        self::$mode = $mode;

        if (!CLISetup::$localeIds)
        {
            CLI::write('No valid locale specified. Check your config or --locales parameter, if used', CLI::LOG_ERROR);
            return false;
        }

        // create directory structure
        CLI::write('FileGen::init() - creating required directories');
        $pathOk = 0;
        foreach (self::$reqDirs as $rd)
            if (CLISetup::writeDir($rd))
                $pathOk++;

        CLI::write('created '.$pathOk.' extra paths'.($pathOk == count(self::$reqDirs) ? '' : ' with errors'));
        CLI::write();

        // handle command prompts
        if (!self::handleCLIOpts($doScripts) && !$updScripts)
            return false;

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_merge(array_keys(self::$tplFiles), array_keys(self::$datasets));
        if ($doScripts || $updScripts)
            self::$subScripts = array_intersect($doScripts ?: $updScripts, self::$subScripts);

        return true;
    }

    private static function handleCLIOpts(?array &$doScripts) : bool
    {
        $doScripts = [];

        if (CLISetup::getOpt('help') && self::$mode == self::MODE_NORMAL)
        {
            if (in_array('simpleImg', CLISetup::getOpt('build')))
                CLISetup::optHelp(1 << 3);
            else if (in_array('complexImg', CLISetup::getOpt('build')))
                CLISetup::optHelp(1 << 4);
            else
                self::printCLIHelp();

            return false;
        }

        // required subScripts
        if ($sync = CLISetup::getOpt('sync'))
        {
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

            if (!$doScripts)
                return false;

            $doScripts = array_unique($doScripts);
            return true;
        }
        else if (is_array($_ = CLISetup::getOpt('build')))
        {
            $doScripts = $_;
            return true;
        }

        return false;
    }

    private static function printCLIHelp()
    {
        CLI::write();
        CLI::write('  usage: php aowow --build=<subScriptList,> [--mpqDataDir: --locales:]', -1, false);
        CLI::write();
        CLI::write('  Compiles files for a given subScript. Existing files are kept by default. Dependencies are taken into account by the triggered calls of --sync and --update', -1, false);

        $lines = [['available subScripts', 'affected files', 'TC dependencies', 'AoWoW dependencies']];
        foreach (array_merge(array_keys(self::$tplFiles), array_keys(self::$datasets)) as $s)
            $lines[] = array(
                ' * '.$s,
                isset(self::$tplFiles[$s]) ? self::$tplFiles[$s][1].self::$tplFiles[$s][0] : self::$datasets[$s][2],
                !empty(self::$tplFiles[$s][2]) ? implode(' ', self::$tplFiles[$s][2]) : (!empty(self::$datasets[$s][1]) ? implode(' ', self::$datasets[$s][1]) : ''),
                !empty(self::$datasets[$s][0]) ? implode(' ', self::$datasets[$s][0]) : ''
            );

        CLI::writeTable($lines);
    }

    public static function generate($key, array $updateIds = [])
    {
        $success = false;
        $reqDBC = [];

        if (file_exists('setup/tools/filegen/'.$key.'.func.php'))
            require_once 'setup/tools/filegen/'.$key.'.func.php';
        else if (empty(self::$tplFiles[$key]))
        {
            CLI::write(sprintf(ERR_MISSING_INCL, $key, 'setup/tools/filegen/'.$key.'.func.php', CLI::LOG_ERROR));
            return false;
        }

        CLI::write('FileGen::generate() - gathering data for '.$key);

        if (!empty(self::$tplFiles[$key]))
        {
            [$file, $destPath, $deps] = self::$tplFiles[$key];

            foreach (self::$txtConstants as $n => &$c)
                if (!$c && defined($n))
                    $c = constant($n);

            if ($content = file_get_contents(self::$tplPath.$file.'.in'))
            {
                // replace constants
                $content = strtr($content, self::$txtConstants);

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
                            CLI::write('No function for was registered for placeholder '.$func.'().', CLI::LOG_ERROR);
                            if (!array_reduce(get_included_files(), function ($inArray, $itr) use ($func) { return $inArray || false !== strpos($itr, $func); }, false))
                                CLI::write('Also, expected include setup/tools/filegen/'.$name.'.func.php was not found.');
                        }
                    }
                }

                if ($content && $funcOK)
                    if (CLISetup::writeFile($destPath.$file, $content))
                        $success = true;
            }
            else
                CLI::write(sprintf(ERR_READ_FILE, CLI::bold(self::$tplPath.$file.'.in')), CLI::LOG_ERROR);
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
                CLI::write(' - subscript \''.$key.'\' not defined in included file', CLI::LOG_ERROR);
        }

        set_time_limit(self::$defaultExecTime);             // reset to default for the next script

        return $success;
    }

    public static function getMode()
    {
        return self::$mode;
    }
}

?>
