<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/*********************************/
/* automaticly apply sql-updates */
/*********************************/

function update()
{
    list($date, $part) = array_values(DB::Aowow()->selectRow('SELECT `date`, `part` FROM ?_dbversion'));

    CLISetup::log('checking sql updates');

    $nFiles = 0;
    foreach (glob('setup/updates/*.sql') as $file)
    {
        $pi = pathinfo($file);
        list($fDate, $fPart) = explode('_', $pi['filename']);

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
        CLISetup::log(' -> '.date('d.m.Y', $fDate).' #'.$fPart.': '.$nQuerys.' queries applied', CLISetup::LOG_OK);
    }

    CLISetup::log($nFiles ? 'applied '.$nFiles.' update(s)' : 'db is already up to date', CLISetup::LOG_OK);

    // fetch sql/build after applying updates, as they may contain sync-prompts
    list($sql, $build) = array_values(DB::Aowow()->selectRow('SELECT `sql`, `build` FROM ?_dbversion'));

    sleep(1);

    $sql   = trim($sql)   ? array_unique(explode(' ', trim($sql)))   : [];
    $build = trim($build) ? array_unique(explode(' ', trim($build))) : [];

    if ($sql)
        CLISetup::log('The following table(s) require syncing: '.implode(', ', $sql));

    if ($build)
        CLISetup::log('The following file(s) require syncing: '.implode(', ', $build));

    return [$sql, $build];
}

?>
