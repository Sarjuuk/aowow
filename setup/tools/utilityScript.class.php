<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

trait TrSubScripts
{
    public int $defaultExecTime = 30;

    private array $generators = [];
    private bool  $inited     = false;

    public function assignGenerators(string $usName) : bool
    {
        if (!$this->inited)
            return false;

        // link to my subscripts
        foreach (CLISetup::getSubScripts($usName) as $cmd => [, $scriptRef])
            $this->generators[$cmd] = $scriptRef;

        return true;
    }
}

abstract class UtilityScript
{
    public array  $argvOpts    = [];
    public int    $argvFlags   = 0x0;
    public int    $optGroup    = -1;
    public array  $childArgs   = [];
    public string $followupFn  = '';

    public const string COMMAND       = '';
    public const string DESCRIPTION   = '';
    public const string APPENDIX      = '';
    public const string PROMPT        = '';
    public const string NOTE_START    = '';
    public const string NOTE_ERROR    = '';
    public const string NOTE_END_OK   = '';
    public const string NOTE_END_FAIL = '';

    public const array  REQUIRED_DB   = [];

    public const bool   USE_CLI_ARGS  = false;
    public const int    LOCK_SITE     = CLISetup::LOCK_OFF;

    /*
        actual UtilityScript functionality
        $args[4]
            variable use parameters for passing data to followup UtilityScripts
        return
            script success

    */
    abstract public function run(array &$args) : bool;

    /*
        implement help output here.
        return
            true  - Help has been provided. Do not process further.
            false - Fall back to help of parent container.
    */
    public function writeCLIHelp() : bool
    {
        return false;
    }

    /*
        implement tests for script success here.
        $error
            list of error messages to display
        return
            test success
    */
    public function test(?array &$error = []) : bool
    {
        return true;
    }
}

?>
