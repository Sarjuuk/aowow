<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*********************************************/
/* string setup steps together for first use */
/*********************************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvOpts = ['s'];
    public $optGroup = CLISetup::OPT_GRP_SETUP;

    public const COMMAND       = 'setup';
    public const DESCRIPTION   = 'Step by step initial setup. Resumes if interrupted.';
    public const NOTE_END_OK   = 'setup finished successfully';
    public const NOTE_END_FAIL = 'setup finished with errors';

    public const SITE_LOCK     = CLISetup::LOCK_ON;

    private $dynArgs = ['doSql' => [], 'doBuild' => []]; // ref to pass commands from 'update' to 'sync'
    private $steps   = array(
     // [staticUS, $name, [...args]]
        ['database',  '', []],
        ['configure', '', []],
        // sql- and build- stuff here
        ['update',    '', []],
        ['sync',      '', []],
        ['account',   '', []]
    );

    private const STEP_FILE = 'cache/setup/firstrun';

    private function getSavedStartStep() : int
    {
        if (file_exists(self::STEP_FILE))
        {
            $rows = file(self::STEP_FILE);
            if ((int)$rows[0] == AOWOW_REVISION)
                return (int)$rows[1];
        }

        return 0;
    }

    // Note! Must be loaded after all SetupScripts have been registered
    public function __construct()
    {
        /****************/
        /* define steps */
        /****************/

        # link required steps with param
        $this->steps[2][2] = &$this->dynArgs;               // update
        $this->steps[3][2] = &$this->dynArgs;               // sync

        # from /sqlgen + /filegen .. already sorted by CLISetup
        foreach (CLISetup::getSubScripts() as $name => [$invoker, $ssRef])
        {
            if ($ssRef->isOptional)
                continue;

            if ($invoker == 'sql')
                array_splice($this->steps, -3, 0, [[$invoker, $name, ['doSql' => $name]]]);
            else if ($invoker == 'build')
                array_splice($this->steps, -3, 0, [[$invoker, $name, ['doBuild' => $name]]]);
        }
    }

    // args: null, null, null, null // nnnn
    public function run(&$args) : bool
    {
        /******************/
        /* get start step */
        /******************/

        $startStep = 0;
        if (($cliStartStep = CLISetup::getOpt('step')) !== false)
        {
            $startStep = ((int)$cliStartStep) - 1;
            if ($startStep < 0 || $startStep >= count($this->steps))
            {
                CLI::write('Invalid step number. Use --step <1-'.count($this->steps).'>', CLI::LOG_ERROR);
                return false;
            }

            CLI::write('[setup] starting from step '.($startStep + 1).'...');
        }
        elseif (($startStep = $this->getSavedStartStep()) !== 0)
        {
            CLI::write('[setup] found firstrun progression info. (Halted on subscript: '.($this->steps[$startStep][1] ?: $this->steps[$startStep][0]).')', CLI::LOG_INFO);
            if (!CLI::read(['x' => ['continue setup? (y/n)', true, true, '/y|n/i']], $uiN))
            {
                CLI::write('Failed to read answer. Use --step in a non-interactive environment.', CLI::LOG_ERROR);
                return false;
            }

            CLI::write();
            if (strtolower($uiN['x']) == 'n')
            {
                $startStep = 0;
                CLI::write('[setup] starting from scratch...');
            }
            else
                CLI::write('[setup] resuming from step '.($startStep + 1).'...');

            sleep(1);
        }


        // init temp setup dir
        if ($info = new \SplFileInfo(self::STEP_FILE))
            CLISetup::writeDir($info->getPath());


        /*******/
        /* run */
        /*******/

        foreach ($this->steps as $idx => [$usName, , $param])
        {
            if ($startStep > $idx)
                continue;

            while (true)
            {
                CLI::write('[setup] step '.($idx + 1).' / '.count($this->steps));
                if (CLISetup::run($usName, $param))
                {
                    $this->saveProgress($idx);
                    break;
                }

                if (CLI::read(['x' => ['['.CLI::bold('c').']ontinue anyway? ['.CLI::bold('r').']etry? ['.CLI::bold('a').']bort?', true, true, '/c|r|a/i']], $uiCRA) && $uiCRA)
                {
                    CLI::write();
                    switch (strtolower($uiCRA['x']))
                    {
                        case 'c':
                            $this->saveProgress($idx);
                            break 2;
                        case 'r':
                            break;
                        case 'a':
                            return false;
                    }
                }
                else
                {
                    CLI::write();
                    return false;
                }
            }
        }

        unlink(self::STEP_FILE);

        return true;
    }

    public function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --setup [--locales: --datasrc:] [--step=<step>]', -1, false);
        CLI::write();
        CLI::write('  Initially essential connection information are set up and basic connectivity tests run afterwards.', -1, false);
        CLI::write('  In the main stage dbc and world data is compiled into the database and required sound, image and data files are generated.', -1, false);
        CLI::write('    This should not require further input and will take about 15-20 minutes, plus 10 minutes per additional locale.', -1, false);
        CLI::write('  Lastly pending updates are applied and you are prompted to create an administrator account.', -1, false);

        if (($startStep = $this->getSavedStartStep()) !== 0)
        {
            CLI::write();
            CLI::write('  You are currently on step '.($startStep + 1).' / '.count($this->steps).' ('.($this->steps[$startStep][1] ?: $this->steps[$startStep][0]).'). You can resume or restart the setup process.', -1, false);
        }

        CLI::write();
        CLI::write();

        return true;
    }


    /**********/
    /* helper */
    /**********/

    private function saveProgress (int $nStep) : void
    {
        if ($h = fopen(self::STEP_FILE, 'w'))
        {
            fwrite($h, AOWOW_REVISION."\n".($nStep + 1)."\n");
            fclose($h);
        }
        else
            CLI::write(' * UtilScript::setup - Could not access step file', CLI::LOG_ERROR);
    }
});

?>
