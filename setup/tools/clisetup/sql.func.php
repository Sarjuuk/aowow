<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* Create content from world tables / dbc files */
/************************************************/

function sql($syncMe = null)
{
    require_once 'setup/tools/sqlGen.class.php';

    SqlGen::init($syncMe !== null ? SqlGen::MODE_UPDATE : SqlGen::MODE_NORMAL, $syncMe ?: []);

    $done = [];
    if (SqlGen::$subScripts)
    {
        $allOk = true;

        // start file generation
        CLISetup::log('begin generation of '. implode(', ', SqlGen::$subScripts));
        CLISetup::log();

        foreach (SqlGen::$subScripts as $tbl)
        {
            $syncIds = [];                                  // todo: fetch what exactly must be regenerated

            $ok = SqlGen::generate($tbl, $syncIds);
            if (!$ok)
                $allOk = false;
            else
                $done[] = $tbl;

            CLISetup::log(' - subscript \''.$tbl.'\' returned '.($ok ? 'sucessfully' : 'with errors'), $ok ? CLISetup::LOG_OK : CLISetup::LOG_ERROR);
            set_time_limit(SqlGen::$defaultExecTime);      // reset to default for the next script
        }

        // end
        CLISetup::log();
        if ($allOk)
            CLISetup::log('successfully finished sql generation', CLISetup::LOG_OK);
        else
            CLISetup::log('finished sql generation with errors', CLISetup::LOG_ERROR);
    }
    else
        CLISetup::log('no valid script names supplied', CLISetup::LOG_ERROR);

    return $done;
}

?>
