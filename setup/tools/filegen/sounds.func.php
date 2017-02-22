<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

    function sounds()
    {
        $ok = true;

        // ALL files
        $files  = DB::Aowow()->selectCol('SELECT ABS(id) AS ARRAY_KEY, CONCAT(path, "/", `file`) FROM ?_sounds_files');
        $nFiles = count($files);
        $itr = $i = 0;
        $step = 1000;
        foreach ($files as $fileId => $filePath)
        {
            $i++;
            $itr++;
            if ($i == $step)
            {
                $i = 0;
                CLISetup::log(' - '.$itr.'/'.$nFiles.' ('.(intVal(100 * $itr / $nFiles).'%) done'));
            }

            // they are ogg files by now...
            if (stristr($filePath, '.wav'))
                $filePath .= '.ogg';
            else
                $filePath .= '.mp3';

            // just use the first locale available .. i there is no support for multiple audio files anyway
            foreach (CLISetup::$expectedPaths as $locStr => $__)
            {
                // get your paths straight!
                $p = CLISetup::nicePath($filePath, CLISetup::$srcDir, $locStr);

                if (CLISetup::fileExists($p))
                {
                    // copy over to static/wowsounds/
                    if (!copy($p, 'static/wowsounds/'.$fileId))
                    {
                        $ok = false;
                        CLISetup::log(' - could not copy '.CLISetup::bold($p).' into '.CLISetup::bold('static/wowsounds/'.$fileId), CLISetup::LOG_ERROR);
                        die();
                    }

                    continue 2;
                }
            }

            CLISetup::log(' - did not find file: '.CLISetup::bold(CLISetup::nicePath($filePath, CLISetup::$srcDir, '<locale>')), CLISetup::LOG_WARN);
            // flag as unusable in DB
            DB::Aowow()->query('UPDATE ?_sounds_files SET id = ?d WHERE ABS(id) = ?d', -$fileId, $fileId);
        }

        return $ok;
    }

?>

