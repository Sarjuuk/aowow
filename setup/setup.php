<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


// shared strings
define('ERR_CREATE_FILE',  'could not create file at destination %s'        );
define('ERR_WRITE_FILE',   'could not write to file at destination %s'      );
define('ERR_READ_FILE',    'file %s could not be read'                      );
define('ERR_MISSING_FILE', 'file %s not found'                              );
define('ERR_NONE',         'created file %s'                                );
define('ERR_MISSING_INCL', 'required function %s() could not be found at %s');


require_once 'setup/tools/CLISetup.class.php';
require_once 'setup/tools/dbc.class.php';
require_once 'setup/tools/imagecreatefromblp.func.php';

function finish()
{
    if (!getopt('d', ['delete']))                           // generated with TEMPORARY keyword. Manual deletion is not needed
        CLISetup::log('generated dbc_* - tables kept available', CLISetup::LOG_WARN);

/* send "i'm in use @" - ping
    if ($ch = curl_init('http://aowow.meedns.com/pingback'))
    {
        $u = !empty($_SERVER['USER']) ? $_SERVER['USER'] : 'NULL';
        $s = !empty($_SERVER['SSH_CONNECTION']) ? explode(' ', $_SERVER['SSH_CONNECTION'])[2] : 'NULL';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "u=".$u."&s=".$s);
        curl_exec($ch);
        curl_close($ch);
    }
*/
    die("\n");
}

$scriptOpt = getopt('', ['account', 'dbconfig', 'siteconfig', 'sql', 'build', 'sync']);
if (!$scriptOpt)
{
    echo "\nAowow Setup\n";
    echo "--dbconfig               : set up db connection\n";
    echo "--siteconfig             : set up site variables\n";
    echo "--account                : create an account with admin privileges\n";
    echo "--sql                    : generate db content from your world tables\n";
    echo "--build                  : create server specific files\n";
    echo "--sync=<tabelList,>      : regenerate tables/files that depend on given world-table\n";
    // echo "--firstrun               : goes through the nessecary hoops of the initial setup. Can be interrupted and --resume'd";
    echo "additional options\n";
    echo "--log logfile            : write ouput to file\n";
    echo "--locales=<regionCodes,> : limit setup to enUS, frFR, deDE, esES and/or ruRU (does not override config settings)\n";
    echo "--mpqDataDir=path/       : manually point to directory with extracted mpq files (default: setup/mpqData/)\n";
    echo "--delete | -d            : delete generated dbc_* tables when script finishes\n";
    echo "--help | -h              : contextual help\n";
    die("\n");
}
else
    CLISetup::init();

$cmd = array_pop(array_keys($scriptOpt));
switch ($cmd)                  // we accept only one main parameter
{
    case 'account':
    case 'dbconfig':
    case 'siteconfig':
    case 'sql':
    case 'build':
        require_once 'setup/tools/clisetup/'.$cmd.'.func.php';
        $cmd();

        finish();
    case 'sync':
        require_once 'setup/tools/clisetup/sql.func.php';
        require_once 'setup/tools/clisetup/build.func.php';
        sql();
        build();

        finish();
}

?>
