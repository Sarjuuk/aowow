<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


$customData = array(
);
$reqDBC = ['soundentries'];

function sounds(/*array $ids = [] */)
{

    // file extraction and conversion in build step. data here is purely structural

    // reality check ... thats probably gigabytes worth of sound.. only growing in size with every locale added on top (RedRocketSite didn't do it. Should i then?)

    // .wav => audio/ogg; codecs="vorbis"
    // .mp3 => audio/mpeg

    $query = 'SELECT
        Id AS `id`,
        `type` AS `cat`,
        `name`,
        `file1` AS soundFile1,
        `file2` AS soundFile2,
        `file3` AS soundFile3,
        `file4` AS soundFile4,
        `file5` AS soundFile5,
        `file6` AS soundFile6,
        `file7` AS soundFile7,
        `file8` AS soundFile8,
        `file9` AS soundFile9,
        `file10` AS soundFile10,
        path,
        flags
        FROM dbc_soundentries
        WHERE id > ?d LIMIT ?d';

    CLISetup::log(' - filling aowow_sounds & preparing aowow_sounds_files');

    DB::Aowow()->query('TRUNCATE ?_sounds');
    DB::Aowow()->query('TRUNCATE ?_sounds_files');

    $lastMax      = 0;
    $soundFileIdx = 0;
    $soundIndex   = [];
    while ($sounds = DB::Aowow()->select($query, $lastMax, SqlGen::$stepSize))
    {
        $newMax = max(array_column($sounds, 'id'));

        CLISetup::log(' * sets '.($lastMax + 1).' - '.$newMax);

        $lastMax = $newMax;

        $groupSets = [];
        foreach ($sounds as $s)
        {
            /* attention!

                one sound can be used in 20 or more locations but may appear as multiple files,
                because of different cases, path being attached to file and other shenanigans

                build a usable path and create full index to compensate
                25.6k raw files => expect ~21k filtered files
            */

            $fileSets = [];
            $hasDupes = false;
            for ($i = 1; $i < 11; $i++)
            {
                $nicePath = CLISetup::nicePath($s['soundFile'.$i], $s['path']);
                if ($s['soundFile'.$i] && array_key_exists($nicePath, $soundIndex))
                {
                    $s['soundFile'.$i] = $soundIndex[$nicePath];
                    $hasDupes = true;
                    continue;
                }

                // convert to something web friendly => ogg
                if (stristr($s['soundFile'.$i], '.wav'))
                {
                    $soundIndex[$nicePath] = ++$soundFileIdx;

                    $fileSets[] = array(
                        $soundFileIdx,
                        strtolower($s['soundFile'.$i]),
                        strtolower($s['path']),
                        SOUND_TYPE_OGG
                    );
                    $s['soundFile'.$i] = $soundFileIdx;
                }
                // mp3 .. keep as is
                else if (stristr($s['soundFile'.$i], '.mp3'))
                {
                    $soundIndex[$nicePath] = ++$soundFileIdx;

                    $fileSets[] = array(
                        $soundFileIdx,
                        strtolower($s['soundFile'.$i]),
                        strtolower($s['path']),
                        SOUND_TYPE_MP3
                    );
                    $s['soundFile'.$i] = $soundFileIdx;
                }
                // i call bullshit
                else if ($s['soundFile'.$i])
                {
                    CLISetup::log(' - sound group #'.$s['id'].' "'.$s['name'].'" has invalid sound file "'.$s['soundFile'.$i].'" on index '.$i.'! Skipping...', CLISetup::LOG_WARN);
                    $s['soundFile'.$i] = null;
                }
                // empty case
                else
                    $s['soundFile'.$i] = null;
            }

            if (!$fileSets && !$hasDupes)
            {
                CLISetup::log(' - sound group #'.$s['id'].' "'.$s['name'].'" contains no sound files! Skipping...', CLISetup::LOG_WARN);
                continue;
            }
            else if ($fileSets)
                DB::Aowow()->query('INSERT INTO ?_sounds_files VALUES (?a)', array_values($fileSets));


            unset($s['path']);

            $groupSets[] = array_values($s);
        }

        DB::Aowow()->query('REPLACE INTO ?_sounds VALUES (?a)', array_values($groupSets));
    }

    return true;
}

?>
