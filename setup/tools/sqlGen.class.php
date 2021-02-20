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

    private static $mode = 0;

    private static $tables   = [];
    private static $tmpStore = [];

    public static $subScripts = [];

    public static $defaultExecTime = 30;
    public static $sqlBatchSize    = 1000;

    public static function init(int $mode = self::MODE_NORMAL, array $updScripts = []) : bool
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        self::$mode = $mode;

        if (!CLISetup::$localeIds)
        {
            CLI::write('No valid locale specified. Check your config or --locales parameter, if used', CLI::LOG_ERROR);
            return false;
        }

        // register subscripts
        foreach (glob('setup/tools/sqlgen/*.func.php') as $file)
            include_once $file;

        while (self::$tmpStore)
        {
            $nDepsMissing = count(self::$tmpStore);

            foreach (self::$tmpStore as $idx => $ts)
            {
                $depsOK = true;
                foreach ($ts->getDependencies(true) as $d)
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
                CLI::write('the following SetupScripts have unresolved dependencies and have not been registered:', CLI::LOG_ERROR);
                foreach (self::$tmpStore as $ts)
                    CLI::write('  * '.CLI::bold($ts->getName()).' => '.implode(', ', $ts->getDependencies(true)));

                self::$tmpStore = [];
                break;
            }
        }

        // handle command prompts
        if (!self::handleCLIOpts($doScripts))
            return false;

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_keys(self::$tables);
        if ($doScripts || $updScripts)
            self::$subScripts = array_intersect($doScripts ?: $updScripts, self::$subScripts);

        return true;
    }

    public static function register(SetupScript $ssRef) : void
    {
        // if dependencies haven't been stored yet, put aside for later use
        foreach ($ssRef->getDependencies(true) as $d)
        {
            if (isset(self::$tables[$d]))
                continue;

            self::$tmpStore[] = $ssRef;
            return;
        }

        if (isset(self::$tables[$ssRef->getName()]))
        {
            CLI::write('a SetupScript named '.CLI::bold($ssRef->getName()).' was already registered. Skipping...', CLI::LOG_WARN);
            return;
        }

        self::$tables[$ssRef->getName()] = $ssRef;

        // recheck temp stored dependencies
        foreach (self::$tmpStore as $idx => $ts)
        {
            $depsOK = true;
            foreach ($ts->getDependencies(true) as $d)
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
    }

    private static function handleCLIOpts(?array &$doTbls) : bool
    {
        $doTbls = [];

        if (CLISetup::getOpt('help') && self::$mode == self::MODE_NORMAL)
        {
            self::printCLIHelp();
            return false;
        }

        // required subScripts
        if ($sync = CLISetup::getOpt('sync'))
        {
            foreach (self::$tables as $name => &$ssRef)
                if (array_intersect($sync, $ssRef->getDependencies(false)))
                    $doTbls[] = $name;

            // recursive dependencies
            foreach (self::$tables as $name => &$ssRef)
                if (array_intersect($sync, $ssRef->getDependencies(true)))
                    $doTbls[] = $name;

            if (!$doTbls)
                return false;

            $doTbls = array_unique($doTbls);
            return true;
        }
        else if ($_ = CLISetup::getOpt('sql'))
        {
            $doTbls = $_;
            return true;
        }

        return false;
    }

    private static function printCLIHelp() : void
    {
        CLI::write();
        CLI::write('  usage: php aowow --sql=<subScriptList,> [--mpqDataDir: --locales:]', -1, false);
        CLI::write();
        CLI::write('  Regenerates DB table content for a given subScript. Dependencies are taken into account by the triggered calls of --sync and --update', -1, false);

        $lines = [['available subScripts', 'TC dependencies', 'AoWoW dependencies']];
        foreach (self::$tables as $tbl => &$ssRef)
        {
            $aoRef = $ssRef->getDependencies(true);
            $len   = 0;
            $first = true;

            $row = [" * ".$tbl, '', ''];

            if ($tc = $ssRef->getDependencies(false))
            {
                $len = 0;
                foreach ($tc as $t)
                {
                    $n = strlen($t) + 1;
                    if ($n + $len > 60)
                    {
                        // max 2 tables, no multi-row shenanigans required
                        if ($first && $aoRef)
                            $row[2] = implode(' ', $aoRef);

                        $lines[] = $row;
                        $row     = ['', '', ''];
                        $len     = $n;
                        $first   = false;
                    }
                    else
                        $len += $n;

                    $row[1] .= $t." ";
                }
            }

            if ($first && $aoRef)
                $row[2] = implode(' ', $aoRef);

            $lines[] = $row;
        }

        CLI::writeTable($lines);
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
