<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

    function soundfiles()
    {
        $ok = true;

        // ALL files
        $files  = DB::Aowow()->selectCol('SELECT ABS(id) AS ARRAY_KEY, CONCAT(path, "/", `file`) FROM ?_sounds_files');
        $nFiles = count($files);
        $itr    = $i = 0;
        $step   = 1000;
        foreach ($files as $fileId => $filePath)
        {
            $i++;
            $itr++;
            if ($i == $step)
            {
                $i = 0;
                CLI::write(' - '.$itr.'/'.$nFiles.' ('.(intVal(100 * $itr / $nFiles).'%) done'));
                DB::Aowow()->selectCell('SELECT 1');        // keep mysql busy or it may go away
            }

            // expect converted files as file.wav_ or file.mp3_
            $filePath .= '_';

            // just use the first locale available .. there is no support for multiple audio files anyway
            foreach (CLISetup::$expectedPaths as $locStr => $__)
            {
                // get your paths straight!
                $p = CLI::nicePath($filePath, CLISetup::$srcDir, $locStr);

                if (CLISetup::fileExists($p))
                {
                    // copy over to static/wowsounds/
                    if (!copy($p, 'static/wowsounds/'.$fileId))
                    {
                        $ok = false;
                        CLI::write(' - could not copy '.CLI::bold($p).' into '.CLI::bold('static/wowsounds/'.$fileId), CLI::LOG_ERROR);
                        break 2;
                    }

                    continue 2;
                }
            }

            CLI::write(' - did not find file: '.CLI::bold(CLI::nicePath($filePath, CLISetup::$srcDir, '['.implode(',', CLISetup::$locales).']')), CLI::LOG_WARN);
            // flag as unusable in DB
            DB::Aowow()->query('UPDATE ?_sounds_files SET id = ?d WHERE ABS(id) = ?d', -$fileId, $fileId);
        }

        return $ok;
    }

?>

