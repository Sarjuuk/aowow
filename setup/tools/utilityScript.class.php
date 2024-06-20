<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

trait TrSubScripts
{
    public $defaultExecTime = 30;

    private $generators = [];
    private $inited     = false;

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
    public $argvOpts    = [];
    public $argvFlags   = 0x0;
    public $optGroup    = -1;
    public $childArgs   = [];
    public $followupFn  = '';

    public const COMMAND       = '';
    public const DESCRIPTION   = '';
    public const APPENDIX      = '';
    public const PROMPT        = '';
    public const NOTE_START    = '';
    public const NOTE_ERROR    = '';
    public const NOTE_END_OK   = '';
    public const NOTE_END_FAIL = '';

    public const REQUIRED_DB = [];

    public const USE_CLI_ARGS = false;
    public const LOCK_SITE    = CLISetup::LOCK_OFF;

    /*
        actual UtilityScript functionality
        $args[4]
            variable use parameters for passing data to followup UtilityScripts
        return
            script success

    */
    abstract public function run(&$args) : bool;

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
