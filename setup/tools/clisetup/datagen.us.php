<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* Create content from world tables / dbc files */
/************************************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    use TrSubScripts;

    public $argvFlags = CLISetup::ARGV_ARRAY | CLISetup::ARGV_OPTIONAL;
    public $optGroup  = CLISetup::OPT_GRP_UTIL;

    public const COMMAND       = 'sql';
    public const DESCRIPTION   = 'Generate DB content from your world tables.';
    public const APPENDIX      = '=<SetupScriptList,>';
    public const NOTE_START    = '[sql] begin generation of:';
    public const NOTE_END_OK   = 'successfully finished sql generation';
    public const NOTE_END_FAIL = 'finished sql generation with errors';

    public const REQUIRED_DB = [DB_AOWOW, DB_WORLD];

    public const LOCK_SITE   = CLISetup::LOCK_RESTORE;

    public function __construct()
    {
        if ($this->inited)
            return true;

        $this->defaultExecTime = ini_get('max_execution_time');

        // register subscripts to CLISetup
        foreach (glob('setup/tools/sqlgen/*.ss.php') as $file)
            include_once $file;

        $this->inited = true;
        return true;
    }

    // args: scriptToDo, scriptSuccess, null, null // ionn
    public function run(&$args) : bool
    {
        $todo = &$args['doSql'];
        $done = &$args['doneSql'];

        if (!$this->inited)
            return false;

        // check passed subscript names; limit to real scriptNames
        if (($sqlArgs = CLISetup::getOpt('sql')) !== false)
        {
            if ($sqlArgs === [])                            // used --sql without arguments
                $todo = array_keys($this->generators);      // do everything
            else if ($_ = array_intersect(array_keys($this->generators), $sqlArgs))
                $todo = $_;
            else
            {
                CLI::write('[sql] no valid script names supplied', CLI::LOG_ERROR);
                return false;
            }

            // supplement self::NOTE_START
            CLI::write('             - '.Lang::concat($todo), CLI::LOG_BLANK, false);
            CLI::write();
        }
        else if ($todo)
        {
            $todo = array_intersect(array_keys($this->generators), is_array($todo) ? $todo : [$todo]);
            if (!$todo)
                return false;
        }
        else
            return false;

        $allOk = true;

        // start file generation
        foreach ($todo as $cmd)
        {
            $syncIds   = [];                                // todo: fetch what exactly must be regenerated
            $success   = false;
            $scriptRef = &$this->generators[$cmd];

            CLI::write('[sql] filling aowow_'.$cmd.' with data');

            if ($scriptRef->fulfillRequirements())
            {
                if ($scriptRef->generate($syncIds))
                {
                    if (method_exists($scriptRef, 'applyCustomData'))
                        $success = $scriptRef->applyCustomData();

                    $success = true;
                }
            }

            if (!$success)
                $allOk = false;
            else
                $done[] = $cmd;

            CLI::write('[sql] subscript \''.$cmd.'\' returned '.($success ? 'successfully' : 'with errors'), $success ? CLI::LOG_OK : CLI::LOG_ERROR);
            CLI::write();
            set_time_limit($this->defaultExecTime);         // reset to default for the next script
        }

        return $allOk;
    }

    public function writeCLIHelp() : bool
    {
        if ($args = CLISetup::getOpt('sql'))
        {
            $anyHelp = false;
            foreach ($args as $cmd)
                if (isset($this->generators[$cmd]) && $this->generators[$cmd]->writeCLIHelp())
                    $anyHelp = true;

            if ($anyHelp)
                return true;
        }

        CLI::write('  usage: php aowow --sql=<SetupScriptList,> [--datasrc: --locales:]', -1, false);
        CLI::write();
        CLI::write('  Regenerates DB table content for a given SetupScript. Dependencies are taken into account by the triggered calls of --sync and --update', -1, false);
        CLI::write();

        ksort($this->generators);

        $lines = [['Command', 'TC dependencies', 'AoWoW dependencies', 'Info']];
        foreach ($this->generators as $cmd => $ssRef)
        {
            $tcDeps = explode("\n", Lang::breakTextClean(implode(', ', $ssRef->getRemoteDependencies() ), 35, Lang::FMT_RAW));
            $aoDeps = explode("\n", Lang::breakTextClean(implode(', ', $ssRef->getSelfDependencies()[0]), 35, Lang::FMT_RAW));

            for ($i = 0; $i < max(count($tcDeps), count($aoDeps)); $i++)
                $lines[] = array(
                    $i ? '' : $cmd,
                    $tcDeps[$i] ?? '',
                    $aoDeps[$i] ?? '',
                    $i ? '' : $ssRef->getInfo()
                );
        }

        CLI::writeTable($lines);
        CLI::write();

        return true;
    }
});

?>
