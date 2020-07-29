<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


require_once 'setup/tools/CLISetup.class.php';
require_once 'setup/tools/setupScript.class.php';
require_once 'setup/tools/dbc.class.php';
require_once 'setup/tools/imagecreatefromblp.func.php';


function finish(bool $unlock = true) : void
{
    if (CLISetup::getOpt('delete'))                         // generated with TEMPORARY keyword. Manual deletion is not needed
        CLI::write('generated dbc_* - tables have been deleted.', CLI::LOG_INFO);

    if ($unlock)
        lockSite(false);

    die("\n");
}

function lockSite(bool $enable = true) : void
{
    DB::Aowow()->query('UPDATE ?_config SET `value` = ?d WHERE `key` = "maintenance"', $enable ? 1 : 0);
}

CLISetup::init();

if (!CLISetup::getOpt(0x3))
    die(CLISetup::optHelp(0x7));

$cmd    = CLISetup::getOpt(0x3)[0];                         // get arguments present in argGroup 1 or 2, if set. Pick first.
$unlock = false;
$s      = [];
$b      = [];
switch ($cmd)                                               // we accept only one main parameter
{
    case 'setup':
        lockSite(true);
        require_once 'setup/tools/clisetup/firstrun.func.php';
        $success = firstrun();

        finish($success);
    case 'sql':
    case 'build':
        lockSite(true);
        $unlock = true;
    case 'account':
    case 'dbconfig':
    case 'siteconfig':
        require_once 'setup/tools/clisetup/'.$cmd.'.func.php';
        $cmd();

        finish($unlock);
    case 'update':
        lockSite(true);
        require_once 'setup/tools/clisetup/update.func.php';

        [$s, $b] = update();                                // return true if we do not rebuild stuff
        if (!$s && !$b)
            finish(true);
    case 'sync':
        lockSite(true);
        require_once 'setup/tools/clisetup/sync.func.php';

        sync($s, $b);
        finish(true);
    case 'dbc':
        foreach (CLISetup::getOpt('dbc') as $n)
        {
            if (empty($n))
                continue;

            $dbc = new DBC(trim($n), ['temporary' => false]);
            if ($dbc->error)
            {
                CLI::write('CLISetup::loadDBC() - required DBC '.$name.'.dbc not found!', CLI::LOG_ERROR);
                return false;
            }

            if (!$dbc->readFile())
            {
                CLI::write('CLISetup::loadDBC() - DBC '.$name.'.dbc could not be written to DB!', CLI::LOG_ERROR);
                return false;
            }
        }
        break;
}

?>
