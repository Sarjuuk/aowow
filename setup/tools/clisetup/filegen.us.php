<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*************************/
/* Create required files */
/*************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    use TrSubScripts;

    public $argvFlags   = CLISetup::ARGV_ARRAY | CLISetup::ARGV_OPTIONAL;
    public $optGroup    = CLISetup::OPT_GRP_UTIL;

    public const COMMAND       = 'build';
    public const DESCRIPTION   = 'Compile image files and data dumps.';
    public const APPENDIX      = '=<SetupScriptList,>';
    public const NOTE_START    = '[build] begin generation of:';
    public const NOTE_END_OK   = 'successfully finished file generation';
    public const NOTE_END_FAIL = 'finished file generation with errors';

    public const REQUIRED_DB = [DB_AOWOW, DB_WORLD];

    public const LOCK_SITE   = CLISetup::LOCK_RESTORE;

    private $uploadDirs = array(                            // stuff that should be writable by www-data and isn't directly created by setup steps
        'static/uploads/screenshots/normal/',
        'static/uploads/screenshots/pending/',
        'static/uploads/screenshots/resized/',
        'static/uploads/screenshots/temp/',
        'static/uploads/screenshots/thumb/',
        'static/uploads/temp/',
        'static/uploads/guide/images/',
    );

    public function __construct()
    {
        if ($this->inited)
            return true;

        $this->defaultExecTime = ini_get('max_execution_time');

        // register subscripts to CLISetup
        foreach (glob('setup/tools/filegen/*.ss.php') as $file)
            include_once $file;

        $this->inited = true;
        return true;
    }

    // args: scriptToDo, scriptSuccess, null, null // ionn
    public function run(&$args) : bool
    {
        $todo = &$args['doBuild'];
        $done = &$args['doneBuild'];

        if (!$this->inited)
            return false;


        // check passed subscript names; limit to real scriptNames
        if (($buildArgs = CLISetup::getOpt('build')) !== false)
        {
            if ($buildArgs === [])                          // used --build without arguments
                $todo = array_keys($this->generators);      // do everything
            else if ($_ = array_intersect(array_keys($this->generators), $buildArgs))
                $todo = $_;
            else
            {
                CLI::write('[build] no valid script names supplied', CLI::LOG_ERROR);
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

        // create user upload dir structure
        foreach ($this->uploadDirs as $ud)
        {
            if (CLISetup::writeDir($ud))
                continue;

            CLI::write('[build] could not create directory: '.CLI::bold($ud), CLI::LOG_ERROR);
            return false;
        }

        $done  = [];
        $allOk = true;

        // start file generation
        foreach ($todo as $cmd)
        {
            $success   = false;
            $scriptRef = &$this->generators[$cmd];

            CLI::write('[build] gathering data for '.$cmd);

            if ($scriptRef->fulfillRequirements())
                $success = $scriptRef->generate();

            if (!$success)
                $allOk = false;
            else
                $done[] = $cmd;

            CLI::write('[build] subscript \''.$cmd.'\' returned '.($success ? 'successfully' : 'with errors'), $success ? CLI::LOG_OK : CLI::LOG_ERROR);
            CLI::write();

            set_time_limit($this->defaultExecTime);         // reset to default for the next script
        }

        return $allOk;
    }

    public function writeCLIHelp() : bool
    {
        if ($args = CLISetup::getOpt('build'))
        {
            $anyHelp = false;
            foreach ($args as $cmd)
                if (isset($this->generators[$cmd]) && $this->generators[$cmd]->writeCLIHelp())
                    $anyHelp = true;

            if ($anyHelp)
                return true;
        }

        CLI::write('  usage: php aowow --build=<SetupScriptList,> [--datasrc: --locales:]', -1, false);
        CLI::write();
        CLI::write('  Compiles files for a given SetupScript. Existing files are kept by default. Dependencies are taken into account by the triggered calls of --sync --update', -1, false);
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
