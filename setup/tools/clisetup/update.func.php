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
    $createQuery = "
        CREATE TABLE `aowow_dbversion` (
            `date` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            `part` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'
        ) ENGINE=MyISAM";

    $date = $part = 0;
    if (!DB::Aowow()->selectCell('SHOW TABLES LIKE "%dbversion"'))
    {
        DB::Aowow()->query($createQuery);
        DB::Aowow()->query('INSERT INTO ?_dbversion VALUES (0, 0)');
    }
    else
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
}

?>
