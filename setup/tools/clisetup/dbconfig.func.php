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
        $buff    = '['.CLISetup::bold($idx).'] '.str_pad($name, 17);
        $errStr  = '';
        $defPort = ini_get('mysqli.default_port');
        $port    = 0;

        if (strstr($dbInfo['host'], ':'))
            list($dbInfo['host'], $port) = explode(':', $dbInfo['host']);

        if ($dbInfo['host'])
        {
            // test DB
            if ($link = @mysqli_connect($dbInfo['host'], $dbInfo['user'], $dbInfo['pass'], $dbInfo['db'], $port ?: $defPort))
                mysqli_close($link);
            else
                $errStr = '['.mysqli_connect_errno().'] '.mysqli_connect_error();

            $buff .= $errStr ? CLISetup::red('ERR   ') : CLISetup::green('OK    ');
            $buff .= 'mysqli://'.$dbInfo['user'].':'.str_pad('', mb_strlen($dbInfo['pass']), '*').'@'.$dbInfo['host'].($port ? ':'.$port : null).'/'.$dbInfo['db'];
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
        if (empty($AoWoWconf[$name]) && $name != 'characters' )
            $AoWoWconf[$name] = array_combine(array_keys($dbFields), ['', '', '', '', '']);
    }

    while (true)
    {
        CLISetup::log();
        CLISetup::log("select a numerical index to use the corresponding entry");

        $nCharDBs = 0;
        foreach ($databases as $idx => $name)
        {
            if ($idx != 3)
                CLISetup::log($testDB($idx, $name, $AoWoWconf[$name]));
            else if (!empty($AoWoWconf[$name]))
                foreach ($AoWoWconf[$name] as $charIdx => $dbInfo)
                    CLISetup::log($testDB($idx + $nCharDBs++, $name.' ['.$charIdx.']', $AoWoWconf[$name][$charIdx]));
        }

        CLISetup::log("[".CLISetup::bold(3 + $nCharDBs)."] add an additional Character DB");

        while (true)
        {
            $inp = ['idx' => ['', true, '/\d/']];
            if (CLISetup::readInput($inp, true) && $inp)
            {
                if ($inp['idx'] >= 0 && $inp['idx'] <= (3 + $nCharDBs))
                {
                    $curFields = $dbFields;

                    if ($inp['idx'] == 3 + $nCharDBs)       // add new realmDB
                        $curFields['realmId'] = ['Realm Id',  false, '/[1-9][0-9]*/'];

                    if (CLISetup::readInput($curFields))
                    {
                        // auth, world or aowow
                        if ($inp['idx'] < 3)
                            $AoWoWconf[$databases[$inp['idx']]] = $curFields ?: array_combine(array_keys($dbFields), ['', '', '', '', '']);
                        // new char DB
                        else if ($inp['idx'] == 3 + $nCharDBs)
                        {
                            if ($curFields)
                            {
                                $_ = $curFields['realmId'];
                                unset($curFields['realmId']);
                                $AoWoWconf[$databases[3]][$_] = $curFields;
                            }
                        }
                        // existing char DB
                        else
                        {
                            $i = 0;
                            foreach ($AoWoWconf[$databases[3]] as $realmId => &$dbInfo)
                            {
                                if ($inp['idx'] - 3 != $i++)
                                    continue;

                                if ($curFields)
                                    $dbInfo = $curFields;
                                else
                                    unset($AoWoWconf[$databases[3]][$realmId]);
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
                        CLISetup::log("edit canceled! returning to list...", CLISetup::LOG_INFO);
                        sleep(1);
                        continue 2;
                    }
                }
            }
            else
            {
                CLISetup::log();
                CLISetup::log("leaving db setup...", CLISetup::LOG_INFO);
                break 2;
            }
        }
    }
}

?>
