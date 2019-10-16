<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*********************************/
/* automaticly apply sql-updates */
/*********************************/

function update()
{
    [$date, $part] = array_values(DB::Aowow()->selectRow('SELECT `date`, `part` FROM ?_dbversion'));

    CLI::write('checking sql updates');

    $nFiles = 0;
    foreach (glob('setup/updates/*.sql') as $file)
    {
        $pi = pathinfo($file);
        [$fDate, $fPart] = explode('_', $pi['filename']);

        $fData = intVal($fDate);

        if ($date && $fDate < $date)
            continue;
        else if ($part && $date && $fDate == $date && $fPart <= $part)
            continue;

        $nFiles++;

        $updQuery = '';
        $nQuerys  = 0;
        foreach (file($file) as $line)
        {
            // skip comments
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            $updQuery .= $line;

            // semicolon at the end -> end of query
            if (substr(trim($line), -1, 1) == ';')
            {
                if (DB::Aowow()->query($updQuery))
                    $nQuerys++;

                $updQuery = '';
            }
        }

        DB::Aowow()->query('UPDATE ?_dbversion SET `date`= ?d, `part` = ?d', $fDate, $fPart);
        CLI::write(' -> '.date('d.m.Y', $fDate).' #'.$fPart.': '.$nQuerys.' queries applied', CLI::LOG_OK);
    }

    CLI::write($nFiles ? 'applied '.$nFiles.' update(s)' : 'db is already up to date', CLI::LOG_OK);

    // fetch sql/build after applying updates, as they may contain sync-prompts
    [$sql, $build] = array_values(DB::Aowow()->selectRow('SELECT `sql`, `build` FROM ?_dbversion'));

    sleep(1);

    $sql   = trim($sql)   ? array_unique(explode(' ', trim($sql)))   : [];
    $build = trim($build) ? array_unique(explode(' ', trim($build))) : [];

    if ($sql)
        CLI::write('The following table(s) require syncing: '.implode(', ', $sql));

    if ($build)
        CLI::write('The following file(s) require syncing: '.implode(', ', $build));

    return [$sql, $build];
}

?>
