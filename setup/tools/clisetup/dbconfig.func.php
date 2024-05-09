<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/**************************/
/* Configure DB connection*/
/**************************/

function dbconfig() : void
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
        $buff = ['['.CLI::bold($idx).']', $name];

        if ($dbInfo['host'])
        {
            DB::test($dbInfo, $errStr);

            $buff[] = $errStr ? CLI::red('ERR') : CLI::green('OK');
            $buff[] = 'mysqli://'.$dbInfo['user'].':'.($dbInfo['pass'] ? '**********' : '').'@'.$dbInfo['host'].'/'.$dbInfo['db'];
            $buff[] = $dbInfo['prefix'] ? 'table prefix: '.$dbInfo['prefix'] : '';
            $buff[] = $errStr;
        }
        else
            $buff[] = CLI::bold('<empty>');

        return $buff;
    };

    if (file_exists('config/config.php'))
        require 'config/config.php';

    foreach ($databases as $idx => $name)
        if (empty($AoWoWconf[$name]) && $name != 'characters' )
            $AoWoWconf[$name] = array_combine(array_keys($dbFields), ['', '', '', '', '']);

    while (true)
    {
        CLI::write("select a numerical index to use the corresponding entry");

        $nCharDBs = 0;
        $tblRows = [];
        foreach ($databases as $idx => $name)
        {
            if ($idx != 3)
                $tblRows[] = $testDB($idx, $name, $AoWoWconf[$name]);
            else if (!empty($AoWoWconf[$name]))
                foreach ($AoWoWconf[$name] as $charIdx => $dbInfo)
                    $tblRows[] = $testDB($idx + $nCharDBs++, $name.' ['.$charIdx.']', $AoWoWconf[$name][$charIdx]);
        }

        $tblRows[] = ['['.CLI::bold(3 + $nCharDBs).']', 'add new character DB'];
        CLI::writeTable($tblRows, true);

        while (true)
        {
            $inp = ['idx' => ['', true, '/\d/']];
            if (CLI::read($inp, true) && $inp)
            {
                if ($inp['idx'] >= 0 && $inp['idx'] <= (3 + $nCharDBs))
                {
                    $curFields = $inp['idx'] ? $dbFields : array_slice($dbFields, 0, 4);

                    if ($inp['idx'] == 3 + $nCharDBs)       // add new realmDB
                        $curFields['realmId'] = ['Realm Id',  false, '/[1-9][0-9]*/'];

                    if (CLI::read($curFields))
                    {
                        if ($inp['idx'] == 0 && $curFields)
                            $curFields['prefix'] = 'aowow_';

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
                            else if (isset($AoWoWconf[$db]))
                                foreach ($AoWoWconf[$db] as $idx => $charInfo)
                                    $buff .= '$AoWoWconf[\''.$db.'\'][\''.$idx.'\'] = '.var_export($AoWoWconf[$db][$idx], true).";\n\n";
                        }
                        $buff .= "?>\n";
                        CLI::write();
                        CLISetup::writeFile('config/config.php', $buff);
                        continue 2;
                    }
                    else
                    {
                        CLI::write("edit canceled! returning to list...", CLI::LOG_INFO);
                        CLI::write();
                        sleep(1);
                        continue 2;
                    }
                }
            }
            else
            {
                CLI::write("leaving db setup...", CLI::LOG_INFO);
                CLI::write();
                break 2;
            }
        }
    }
}

?>
