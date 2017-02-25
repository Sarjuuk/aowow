<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/*************************/
/* Create required files */
/*************************/

function build($syncMe = null)
{
    require_once 'setup/tools/fileGen.class.php';

    FileGen::init($syncMe !== null ? FileGen::MODE_UPDATE : FileGen::MODE_NORMAL, $syncMe ?: []);

    $done = [];
    if (FileGen::$subScripts)
    {
        $allOk = true;

        // start file generation
        CLISetup::log('begin generation of '. implode(', ', FileGen::$subScripts));
        CLISetup::log();

        // files with template
        foreach (FileGen::$tplFiles as $name => list($file, $destPath, $deps))
        {
            $reqDBC = [];

            if (!in_array($name, FileGen::$subScripts))
                continue;

            if (!file_exists(FileGen::$tplPath.$file.'.in'))
            {
                CLISetup::log(sprintf(ERR_MISSING_FILE, FileGen::$tplPath.$file.'.in'), CLISetup::LOG_ERROR);
                $allOk = false;
                continue;
            }

            if (!CLISetup::writeDir($destPath))
                continue;

            $syncIds = [];                                  // todo: fetch what exactly must be regenerated

            $ok = FileGen::generate($name, $syncIds);
            if (!$ok)
                $allOk = false;
            else
                $done[] = $name;

            CLISetup::log(' - subscript \''.$file.'\' returned '.($ok ? 'sucessfully' : 'with errors'), $ok ? CLISetup::LOG_OK : CLISetup::LOG_ERROR);
            set_time_limit(FileGen::$defaultExecTime);      // reset to default for the next script
        }

        // files without template
        foreach (FileGen::$datasets as $file => $deps)
        {
            if (!in_array($file, FileGen::$subScripts))
                continue;

            $syncIds = [];                                  // todo: fetch what exactly must be regenerated

            $ok = FileGen::generate($file, $syncIds);
            if (!$ok)
                $allOk = false;
            else
                $done[] = $file;

            CLISetup::log(' - subscript \''.$file.'\' returned '.($ok ? 'sucessfully' : 'with errors'), $ok ? CLISetup::LOG_OK : CLISetup::LOG_ERROR);
            set_time_limit(FileGen::$defaultExecTime);      // reset to default for the next script
        }

        // end
        CLISetup::log();
        if ($allOk)
            CLISetup::log('successfully finished file generation', CLISetup::LOG_OK);
        else
            CLISetup::log('finished file generation with errors', CLISetup::LOG_ERROR);
    }
    else
        CLISetup::log('no valid script names supplied', CLISetup::LOG_ERROR);

    return $done;
}

?>
