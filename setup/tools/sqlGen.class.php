<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class SqlGen
{
    const MODE_NORMAL   = 1;
    const MODE_FIRSTRUN = 2;
    const MODE_UPDATE   = 3;

    private static $tables   = [];
    private static $tmpStore = [];

    public  static $cliOpts   = [];
    private static $shortOpts = 'h';
    private static $longOpts  = ['sql::', 'help', 'sync:']; // general
    private static $mode      = 0;

    public static $subScripts = [];

    public static $defaultExecTime = 30;
    public static $sqlBatchSize    = 1000;

    public static function init(int $mode = self::MODE_NORMAL, array $updScripts = []) : void
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        $doScripts = null;


        // register subscripts
        foreach (glob('setup/tools/sqlgen/*.func.php') as $file)
            include_once $file;

        while (self::$tmpStore)
        {
            $nDepsMissing = count(self::$tmpStore);

            foreach (self::$tmpStore as $idx => $ts)
            {
                $depsOK = true;
                foreach ($ts->getDependancies(true) as $d)
                {
                    if (isset(self::$tables[$d]))
                        continue;

                    $depsOK = false;
                    break;
                }

                if ($depsOK)
                {
                    if (isset(self::$tables[$ssRef->getName()]))
                    {
                        CLI::write('a SetupScript named '.CLI::bold($ts->getName()).' was already registered. Skipping...', CLI::LOG_WARN);
                        unset(self::$tmpStore[$idx]);
                        continue;
                    }

                    self::$tables[$ts->getName()] = $ts;
                    unset(self::$tmpStore[$idx]);
                }
            }

            if ($nDepsMissing == count(self::$tmpStore))
            {
                CLI::write('the flollowing SetupScripts have unresolved dependancies and have not been registered:', CLI::LOG_ERROR);
                foreach (self::$tmpStore as $ts)
                    CLI::write('  * '.CLI::bold($ts->getName()).' => '.implode(', ', $ts->getDependancies(true)));

                self::$tmpStore = [];
                break;
            }
        }


        // handle command prompts
        if (getopt(self::$shortOpts, self::$longOpts) || $mode == self::MODE_FIRSTRUN)
            self::handleCLIOpts($doScripts);
        else if ($mode != self::MODE_UPDATE)
        {
            self::printCLIHelp();
            exit;
        }

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_keys(self::$tables);
        if ($doScripts || $updScripts)
            self::$subScripts = array_intersect($doScripts ?: $updScripts, self::$subScripts);
        else if ($doScripts === null)
            self::$subScripts = [];

        if (!CLISetup::$localeIds /* && this script has localized text */)
        {
            CLI::write('No valid locale specified. Check your config or --locales parameter, if used', CLI::LOG_ERROR);
            exit;
        }

        self::$mode = $mode;
    }

    public static function register(SetupScript $ssRef) : bool
    {
        // if dependancies haven't been stored yet, put aside for later use
        foreach ($ssRef->getDependancies(true) as $d)
        {
            if (isset(self::$tables[$d]))
                continue;

            self::$tmpStore[] = $ssRef;
            return false;
        }

        if (isset(self::$tables[$ssRef->getName()]))
        {
            CLI::write('a SetupScript named '.CLI::bold($ssRef->getName()).' was already registered. Skipping...', CLI::LOG_WARN);
            return false;
        }

        self::$tables[$ssRef->getName()] = $ssRef;

        // recheck temp stored dependancies
        foreach (self::$tmpStore as $idx => $ts)
        {
            $depsOK = true;
            foreach ($ts->getDependancies(true) as $d)
            {
                if (isset(self::$tables[$d]))
                    continue;

                $depsOK = false;
                break;
            }

            if ($depsOK)
            {
                self::$tables[$ts->getName()] = $ts;
                unset(self::$tmpStore[$idx]);
            }
        }

        return true;
    }

    private static function handleCLIOpts(&$doTbls) : void
    {
        $doTbls = [];
        $_ = getopt(self::$shortOpts, self::$longOpts);

        if ((isset($_['help']) || isset($_['h'])) && empty($_['sql']))
        {
            self::printCLIHelp();
            exit;
        }

        // required subScripts
        if (!empty($_['sync']))
        {
            $sync = explode(',', $_['sync']);
            foreach (self::$tables as $name => &$ssRef)
                if (array_intersect($sync, $ssRef->getDependancies(false)))
                    $doTbls[] = $name;

            // recursive dependencies
            foreach (self::$tables as $name => &$ssRef)
                if (array_intersect($sync, $ssRef->getDependancies(true)))
                    $doTbls[] = $name;

            $doTbls = $doTbls ? array_unique($doTbls) : null;
        }
        else if (!empty($_['sql']))
            $doTbls = explode(',', $_['sql']);
    }

    public static function printCLIHelp() : void
    {
        echo "\nusage: php aowow --sql=<tableList,> [-h --help]\n\n";
        echo "--sql              : available tables:\n";
        foreach (self::$tables as $t => &$ssRef)
            echo " * ".str_pad($t, 24).($ssRef->getDependancies(false) ? ' - TC deps: '.implode(', ', $ssRef->getDependancies(false)) : '').($ssRef->getDependancies(true) ? ' - Aowow deps: '.implode(', ', $ssRef->getDependancies(true)) : '')."\n";

        echo "-h --help          : shows this info\n";
    }

    public static function generate(string $tableName, array $updateIds = []) : bool
    {
        if (!isset(self::$tables[$tableName]))
        {
            CLI::write('SqlGen::generate - invalid table given', CLI::LOG_ERROR);
            return false;
        }

        $ssRef = &self::$tables[$tableName];

        CLI::write('SqlGen::generate() - filling aowow_'.$tableName.' with data');

        // check for required auxiliary DBC files
        if (!in_array('TrDBCcopy', class_uses($ssRef)))
            foreach ($ssRef->getRequiredDBCs() as $req)
                if (!CLISetup::loadDBC($req))
                    return false;

        if ($ssRef->generate($updateIds))
        {
            if (method_exists($ssRef, 'applyCustomData'))
                $ssRef->applyCustomData();

            return true;
        }

        return false;
    }

    public static function getMode() : int
    {
        return self::$mode;
    }
}

?>
