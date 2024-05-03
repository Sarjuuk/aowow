<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*************************/
/* Create required files */
/*************************/

function build($syncMe = null) : array
{
    if (!DB::isConnected(DB_AOWOW) || !DB::isConnected(DB_WORLD))
    {
        CLI::write('Database not yet set up!', CLI::LOG_WARN);
        CLI::write('Please use '.CLI::bold('"php aowow --dbconfig"').' for setup', CLI::LOG_BLANK);
        CLI::write();
        return [];
    }

    require_once 'setup/tools/fileGen.class.php';

    if(!FileGen::init($syncMe !== null ? FileGen::MODE_UPDATE : FileGen::MODE_NORMAL, $syncMe ?: []))
        return [];

    $done = [];
    if (FileGen::$subScripts)
    {
        CLISetup::siteLock(CLISetup::LOCK_ON);
        $allOk = true;

        // start file generation
        CLI::write('begin generation of '. implode(', ', FileGen::$subScripts));
        CLI::write();

        // files with template
        foreach (FileGen::$tplFiles as $name => [$file, $destPath, $deps])
        {
            $reqDBC = [];

            if (!in_array($name, FileGen::$subScripts))
                continue;

            if (!file_exists(FileGen::$tplPath.$file.'.in'))
            {
                CLI::write(sprintf(ERR_MISSING_FILE, FileGen::$tplPath.$file.'.in'), CLI::LOG_ERROR);
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

            CLI::write(' - subscript \''.$file.'\' returned '.($ok ? 'successfully' : 'with errors'), $ok ? CLI::LOG_OK : CLI::LOG_ERROR);
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

            CLI::write(' - subscript \''.$file.'\' returned '.($ok ? 'successfully' : 'with errors'), $ok ? CLI::LOG_OK : CLI::LOG_ERROR);
            set_time_limit(FileGen::$defaultExecTime);      // reset to default for the next script
        }

        // end
        CLI::write();
        if ($allOk)
            CLI::write('successfully finished file generation', CLI::LOG_OK);
        else
            CLI::write('finished file generation with errors', CLI::LOG_ERROR);

        CLISetup::siteLock(CLISetup::LOCK_RESTORE);
    }
    else if (FileGen::getMode() == FileGen::MODE_NORMAL)
        CLI::write('no valid script names supplied', CLI::LOG_ERROR);

    return $done;
}

?>
