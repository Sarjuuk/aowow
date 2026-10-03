<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


require_once 'includes/setup/cli.class.php';
require_once 'includes/setup/timer.class.php';
require_once 'includes/setup/datatypes/primitives.php';
require_once 'includes/setup/files/binaryfile.class.php';
require_once 'includes/setup/files/dbcfile.class.php';
require_once 'includes/setup/files/blp2file.class.php';

require_once 'setup/tools/setupScript.class.php';
require_once 'setup/tools/utilityScript.class.php';
require_once 'setup/tools/CLISetup.class.php';
require_once 'setup/tools/dbcreader.class.php';

CLISetup::init();
CLISetup::loadScripts();

if (CLISetup::getOpt('help'))
{
    CLISetup::writeCLIHelp(true);
    exit(CLI::errorCount() ? 1 : 0);
}
else if (!CLISetup::getOpt(1 << CLISetup::OPT_GRP_SETUP | 1 << CLISetup::OPT_GRP_UTIL))
{
    CLISetup::writeCLIHelp();
    exit(CLI::errorCount() ? 1 : 0);
}

if (CLISetup::getOpt('delete'))                             // generated with TEMPORARY keyword. Manual deletion is not needed
    CLI::write('generated dbc_* - tables have been deleted.', CLI::LOG_INFO);

$success = CLISetup::runInitial();

fwrite(STDOUT, "\n");
exit($success && CLI::errorCount() === 0 ? 0 : 1);

?>
