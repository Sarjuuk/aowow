<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


require_once 'setup/tools/CLISetup.class.php';
require_once 'setup/tools/setupScript.class.php';
require_once 'setup/tools/dbc.class.php';
require_once 'setup/tools/imagecreatefromblp.func.php';

function finish() : void
{
    if (CLISetup::getOpt('delete'))                         // generated with TEMPORARY keyword. Manual deletion is not needed
        CLI::write('generated dbc_* - tables have been deleted.', CLI::LOG_INFO);

    die("\n");
}

CLISetup::init();

if (!CLISetup::getOpt(0x3))
    die(CLISetup::optHelp(0x7));

$cmd = CLISetup::getOpt(0x3)[0];                            // get arguments present in argGroup 1 or 2, if set. Pick first.
$s   = [];
$b   = [];
switch ($cmd)                                               // we accept only one main parameter
{
    case 'setup':
    case 'sql':
    case 'build':
    case 'account':
    case 'dbconfig':
    case 'siteconfig':
        require_once 'setup/tools/clisetup/'.$cmd.'.func.php';
        $cmd();
        finish();
    case 'update':
        require_once 'setup/tools/clisetup/update.func.php';

        update($s, $b);                                     // return true if we do not rebuild stuff
        if (!$s && !$b)
            finish();
    case 'sync':
        require_once 'setup/tools/clisetup/sync.func.php';

        sync($s, $b);
        finish();
    case 'dbc':
        foreach (CLISetup::getOpt('dbc') as $n)
        {
            if (empty($n))
                continue;

            $dbc = new DBC(trim($n), ['temporary' => false]);
            if ($dbc->error)
                return false;

            if (!$dbc->readFile())
            {
                CLI::write('CLISetup::loadDBC() - DBC '.$n.'.dbc could not be written to DB!', CLI::LOG_ERROR);
                return false;
            }
        }
        break;
}

?>
