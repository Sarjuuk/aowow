<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/*************************/
/* Create required files */
/*************************/

function build()
{
    require_once 'setup/tools/fileGen.class.php';

    FileGen::init();

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

            if (file_exists('setup/tools/filegen/'.$name.'.func.php'))
                require_once 'setup/tools/filegen/'.$name.'.func.php';
            // else     not necessarily an error, may only need replacement of const text

            if (!CLISetup::writeDir($destPath))
                continue;

            $ok = false;
            if ($content = file_get_contents(FileGen::$tplPath.$file.'.in'))
            {
                if ($dest = @fOpen($destPath.$file, "w"))
                {
                    // replace constants
                    $content = strtr($content, FileGen::$txtConstants);

                    // check for required auxiliary DBC files
                    foreach ($reqDBC as $req)
                        if (!CLISetup::loadDBC($req))
                            continue 2;

                    // must generate content
                    // PH format: /*setup:<setupFunc>*/
                    if (preg_match_all('/\/\*setup:([\w\-_]+)\*\//i', $content, $m))
                    {
                        foreach ($m[1] as $func)
                        {
                            if (function_exists($func))
                                $content = str_replace('/*setup:'.$func.'*/', $func(), $content);
                            else
                            {
                                $allOk = false;
                                CLISetup::log('No function for was registered for placeholder '.$func.'().', CLISetup::LOG_ERROR);
                                if (!array_reduce(get_included_files(), function ($inArray, $itr) use ($func) { return $inArray || false !== strpos($itr, $func); }, false))
                                    CLISetup::log('Also, expected include setup/tools/filegen/'.$name.'.func.php was not found.');
                            }
                        }
                    }

                    if (fWrite($dest, $content))
                    {
                        CLISetup::log(sprintf(ERR_NONE, CLISetup::bold($destPath.$file)), CLISetup::LOG_OK);
                        if ($content && $allOk)
                            $ok = true;
                    }
                    else
                        CLISetup::log(sprintf(ERR_WRITE_FILE, CLISetup::bold($destPath.$file)), CLISetup::LOG_ERROR);

                    fClose($dest);
                }
                else
                    CLISetup::log(sprintf(ERR_CREATE_FILE, CLISetup::bold($destPath.$file)), CLISetup::LOG_ERROR);
            }
            else
                CLISetup::log(sprintf(ERR_READ_FILE, CLISetup::bold(FileGen::$tplPath.$file.'.in')), CLISetup::LOG_ERROR);

            if (!$ok)
                $allOk = false;
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

            CLISetup::log(' - subscript \''.$file.'\' returned '.($ok ? 'sucessfully' : 'with errors'), $ok ? CLISetup::LOG_OK : CLISetup::LOG_ERROR);
            set_time_limit(SqlGen::$defaultExecTime);      // reset to default for the next script
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
}

?>
