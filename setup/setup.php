<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


require_once 'setup/tools/setupScript.class.php';
require_once 'setup/tools/utilityScript.class.php';
require_once 'setup/tools/CLISetup.class.php';
require_once 'setup/tools/dbc.class.php';
require_once 'setup/tools/imagecreatefromblp.func.php';

CLISetup::init();
CLISetup::loadScripts();

if (CLISetup::getOpt('help'))
    die(CLISetup::writeCLIHelp(true));
else if (!CLISetup::getOpt(1 << CLISetup::OPT_GRP_SETUP | 1 << CLISetup::OPT_GRP_UTIL))
    die(CLISetup::writeCLIHelp());

if (CLISetup::getOpt('delete'))                         // generated with TEMPORARY keyword. Manual deletion is not needed
    CLI::write('generated dbc_* - tables have been deleted.', CLI::LOG_INFO);

CLISetup::runInitial();

fwrite(STDOUT, "\n");
exit;

?>
