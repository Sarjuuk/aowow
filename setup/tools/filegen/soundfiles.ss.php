<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'soundfiles' => [[], CLISetup::ARGV_PARAM, 'Links converted sound files to database and moves them to destination.']
    );

    protected $requiredDirs = ['static/wowsounds/'];
    protected $setupAfter   = [['sounds'], []];

    public function generate() : bool
    {
        // ALL files
        $files  = DB::Aowow()->selectCol('SELECT ABS(`id`) AS ARRAY_KEY, CONCAT(`path`, "/", `file`) FROM ?_sounds_files');
        $nFiles = count($files);
        $qtLen  = strlen($nFiles);
        $sum    = 0;
        $time   = new Timer(500);

        foreach ($files as $fileId => $filePath)
        {
            $sum++;
            if ($time->update())
            {
                CLI::write(sprintf('[soundfiles] * %'.$qtLen.'d / %d (%4.1f%%)', $sum,  $nFiles, round(100 * $sum / $nFiles, 1)), CLI::LOG_BLANK, true, true);
                DB::Aowow()->selectCell('SELECT 1');        // keep mysql busy or it may go away
            }

            // expect converted files as file.wav_ or file.mp3_
            $filePath .= '_';

            // just use the first locale available .. there is no support for multiple audio files for now
            foreach (CLISetup::$locales as $loc)
            {
                foreach ($loc->gameDirs() as $dir)
                {
                    // get your paths straight!
                    $p = CLI::nicePath($filePath, CLISetup::$srcDir, $dir);
                    $lower_p = CLI::nicePath(strtolower($filePath), CLISetup::$srcDir, $loc);

                    if (!CLISetup::fileExists($p) && !CLISetup::fileExists($lower_p))
                        continue;

                    // copy over to static/wowsounds/
                    if (copy($p, 'static/wowsounds/'.$fileId) or copy($lower_p, 'static/wowsounds/'.$fileId))
                        continue 3;

                    $this->success = false;
                    CLI::write('[soundfiles]  - could not copy '.CLI::bold($p).' into '.CLI::bold('static/wowsounds/'.$fileId), CLI::LOG_ERROR);
                    $time->reset();
                }
            }

            CLI::write('[soundfiles]  - did not find file: '.CLI::bold(CLI::nicePath($filePath, CLISetup::$srcDir, '['.implode(',', array_map(fn($x) => $x->json(), CLISetup::$locales)).']')), CLI::LOG_WARN);
            $time->reset();
            // flag as unusable in DB
            DB::Aowow()->query('UPDATE ?_sounds_files SET `id` = ?d WHERE ABS(`id`) = ?d', -$fileId, $fileId);
        }

        return $this->success;
    }
});

?>
