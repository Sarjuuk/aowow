<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

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
        CLI::write('begin generation of '. implode(', ', SqlGen::$subScripts));
        CLI::write();

        foreach (SqlGen::$subScripts as $tbl)
        {
            $syncIds = [];                                  // todo: fetch what exactly must be regenerated

            $ok = SqlGen::generate($tbl, $syncIds);
            if (!$ok)
                $allOk = false;
            else
                $done[] = $tbl;

            CLI::write(' - subscript \''.$tbl.'\' returned '.($ok ? 'successfully' : 'with errors'), $ok ? CLI::LOG_OK : CLI::LOG_ERROR);
            set_time_limit(SqlGen::$defaultExecTime);      // reset to default for the next script
        }

        // end
        CLI::write();
        if ($allOk)
            CLI::write('successfully finished sql generation', CLI::LOG_OK);
        else
            CLI::write('finished sql generation with errors', CLI::LOG_ERROR);
    }
    else if ($syncMe)
        CLI::write('no valid script names supplied', CLI::LOG_ERROR);

    return $done;
}

?>
