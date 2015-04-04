<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/**************************/
/* Configure DB connection*/
/**************************/

function dbconfig()
{
    $databases = ['aowow', 'world', 'auth', 'characters'];
    $AoWoWconf = [];
    $dbFields  = array(
        'host'   => ['Server Host',   false],
        'user'   => ['User',          false],
        'pass'   => ['Password',      true ],
        'db'     => ['Database Name', false],
        'prefix' => ['Table prefix',  false]
    );
    $testDB    = function($idx, $name, $dbInfo)
    {
        $buff   = '['.CLISetup::bold($idx).'] '.str_pad($name, 12);
        $errStr = '';

        if ($dbInfo['host'])
        {
            // test DB
            if ($link = @mysqli_connect($dbInfo['host'], $dbInfo['user'], $dbInfo['pass'], $dbInfo['db']))
                mysqli_close($link);
            else
                $errStr = '['.mysqli_connect_errno().'] '.mysqli_connect_error();

            $buff .= $errStr ? CLISetup::red('ERR   ') : CLISetup::green('OK    ');
            $buff .= 'mysqli://'.$dbInfo['user'].':'.str_pad('', strlen($dbInfo['pass']), '*').'@'.$dbInfo['host'].'/'.$dbInfo['db'];
            $buff .= ($dbInfo['prefix'] ? '    table prefix: '.$dbInfo['prefix'] : null).'    '.$errStr;
        }
        else
            $buff .= '      '.CLISetup::bold('<empty>');

        return $buff;
    };

    if (file_exists('config/config.php'))
        require 'config/config.php';

    foreach ($databases as $idx => $name)
    {
        if ($name == 'characters' && empty($AoWoWconf[$name][0]))
            $AoWoWconf[$name][0] = array_combine(array_keys($dbFields), ['', '', '', '', '']);
        else if (empty($AoWoWconf[$name]))
            $AoWoWconf[$name] = array_combine(array_keys($dbFields), ['', '', '', '', '']);
    }

    while (true)
    {
        CLISetup::log();
        CLISetup::log("select a numerical index to use the corresponding entry");

        $charOffset = 0;
        foreach ($databases as $idx => $name)
        {
            if ($idx != 3)
                CLISetup::log($testDB($idx, $name, $AoWoWconf[$name]));
            else
                foreach ($AoWoWconf[$name] as $charIdx => $dbInfo)
                    CLISetup::log($testDB($idx + $charOffset++, $name, $AoWoWconf[$name][$charIdx]));
        }

        CLISetup::log("[".CLISetup::bold(3 + $charOffset)."] add an additional Character DB");

        while (true)
        {
            $inp = ['idx' => ['', true, '/\d/']];
            if (CLISetup::readInput($inp, true) && $inp)
            {
                if (is_numeric($inp['idx']) && $inp['idx'] >= 0 && $inp['idx'] <= (3 + $charOffset))
                {
                    $curFields = $dbFields;
                    if (CLISetup::readInput($curFields))
                    {
                        if ($inp['idx'] < 3)
                            $AoWoWconf[$databases[$inp['idx']]] = $curFields ?: array_combine(array_keys($dbFields), ['', '', '', '', '']);
                        else if ($inp['idx'] == 3 + $charOffset)
                        {
                            if ($curFields)
                                $AoWoWconf[$databases[3]][] = $curFields;
                        }
                        else
                        {
                            $i = 0;
                            foreach ($AoWoWconf[$databases[3]] as $offset => &$dbInfo)
                            {
                                if ($inp['idx'] - 3 != $i++)
                                    continue;

                                if ($curFields)
                                    $dbInfo = $curFields;
                                else
                                    unset($AoWoWconf[$databases[3]][$offset]);
                            }
                        }

                        // write config file
                        $buff = "<?php\n\nif (!defined('AOWOW_REVISION'))\n    die('illegal access');\n\n\n";
                        foreach ($databases as $db)
                        {
                            if ($db != 'characters')
                                $buff .= '$AoWoWconf[\''.$db.'\'] = '.var_export($AoWoWconf[$db], true).";\n\n";
                            else
                                foreach ($AoWoWconf[$db] as $idx => $charInfo)
                                    $buff .= '$AoWoWconf[\''.$db.'\'][\''.$idx.'\'] = '.var_export($AoWoWconf[$db][$idx], true).";\n\n";
                        }
                        $buff .= "?>\n";
                        CLISetup::log();
                        CLISetup::writeFile('config/config.php', $buff);
                        continue 2;
                    }
                    else
                    {
                        CLISetup::log();
                        CLISetup::log("edit canceled! returning to list...", CLISetup::LOG_WARN);
                        sleep(1);
                        continue 2;
                    }
                }
            }
            else
            {
                CLISetup::log();
                CLISetup::log("db setup aborted", CLISetup::LOG_WARN);
                break 2;
            }
        }
    }
}

?>
