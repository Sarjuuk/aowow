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
        'host'   => ['Hostname / IP', false],
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
            $result = CLI::green('OK');
            $note   = '';

            if (DB::test($dbInfo, $note))
            {
                DB::load($idx, $dbInfo);

                $ok = false;
                switch ($idx)
                {
                    case DB_AOWOW:
                        if (DB::Aowow()->selectCell('SHOW TABLES LIKE ?', 'aowow_dbversion'))
                        {
                            if ($date = DB::Aowow()->selectCell('SELECT `date` FROM ?_dbversion'))
                            {
                                $note = 'AoWoW DB version @ ' . date(Util::$dateFormatInternal, $date);
                                $ok   = true;
                            }
                            else
                                $note = CLI::yellow('AoWoW DB version empty! Import of DB dump failed?');
                        }
                        else
                            $note = CLI::yellow('DB test failed to find dbversion table. setup/db_structure.sql not yet imported?');
                        break;
                    case DB_WORLD:
                        if (DB::World()->selectCell('SHOW TABLES LIKE ?', 'version'))
                        {
                            [$vString, $vNo] = DB::World()->selectRow('SELECT `db_version` AS "0", `cache_id` AS "1" FROM `version`');
                            if (strpos($vString, 'TDB') === 0)
                            {
                                if ($vNo < TDB_WORLD_MINIMUM_VER)
                                    $note = CLI::yellow('DB test found TrinityDB version older than rev. ').CLI::bold(TDB_WORLD_MINIMUM_VER).CLI::yellow('. Please update to at least rev. ').CLI::bold(TDB_WORLD_MINIMUM_VER);
                                else if ($vNo > TDB_WORLD_EXPECTED_VER)
                                    $note = CLI::yellow('DB test found TrinityDB version newer than rev. ').CLI::bold(TDB_WORLD_EXPECTED_VER).CLI::yellow('. Be advised! DB structure may diverge!');
                                else
                                {
                                    $note = 'TrinityDB version @ ' . $vString;
                                    $ok   = true;
                                }
                            }
                            else if (strpos($vString, 'ACDB') === 0)
                                $note = CLI::yellow('DB test found AzerothCore DB version. AzerothCore DB structure is not supported!');
                            else
                                $note = CLI::yellow('DB test found unexpected vendor in expected version table. Uhh.. Good Luck..!?');
                        }
                        else if (DB::World()->selectCell('SHOW TABLES LIKE ?', 'db_version'))
                            $note = CLI::yellow('DB test found MaNGOS styled version table. MaNGOS DB structure is not supported!');
                        else
                            $note = CLI::yellow('DB test failed to find version table. TrinityDB world not yet imported?');
                        break;
                    default:
                        $ok = true;                         // no tests right now
                }

                if (!$ok)
                    $result = CLI::yellow('WARN');
            }
            else
                $result = CLI::red('ERR');

            $buff[] = $result;
            $buff[] = 'mysqli://'.$dbInfo['user'].':'.($dbInfo['pass'] ? '**********' : '').'@'.$dbInfo['host'].'/'.$dbInfo['db'];
            $buff[] = $dbInfo['prefix'] ? 'table prefix: '.$dbInfo['prefix'] : '';
            $buff[] = $note;

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
        CLI::write('select an index to use the corresponding entry');

        $nCharDBs = 0;
        $tblRows = [];
        foreach ($databases as $idx => $name)
        {
            if ($idx != DB_CHARACTERS)
                $tblRows[] = $testDB($idx, $name, $AoWoWconf[$name]);
            else if (!empty($AoWoWconf[$name]))
                foreach ($AoWoWconf[$name] as $charIdx => $dbInfo)
                    $tblRows[] = $testDB($idx + $nCharDBs++, $name.' ['.$charIdx.']', $AoWoWconf[$name][$charIdx]);
        }

        $tblRows[] = ['['.CLI::bold('N').']', 'new characters DB'];
        $tblRows[] = ['['.CLI::bold('R').']', 'retest / reload DBs'];
        CLI::writeTable($tblRows, true);

        while (true)
        {
            $inp = ['idx' => ['', true, '/\d|R|N/i']];
            if (CLI::read($inp, true) && $inp)
            {
                if (strtoupper($inp['idx']) == 'R')
                    continue 2;
                else if (($inp['idx'] >= DB_AOWOW && $inp['idx'] < (DB_CHARACTERS + $nCharDBs)) || strtoupper($inp['idx']) == 'N')
                {
                    $curFields = $inp['idx'] ? $dbFields : array_slice($dbFields, 0, 4);

                    if (strtoupper($inp['idx']) == 'N')     // add new characters DB
                        $curFields['realmId'] = ['Realm Id',  false, '/\d{1,3}/'];

                    if (CLI::read($curFields))
                    {
                        if ($inp['idx'] == DB_AOWOW && $curFields)
                            $curFields['prefix'] = 'aowow_';

                        if (strtoupper($inp['idx']) == 'N') // new char DB
                        {
                            if ($curFields)
                            {
                                $_ = $curFields['realmId'];
                                unset($curFields['realmId']);
                                $AoWoWconf[$databases[DB_CHARACTERS]][$_] = $curFields;
                            }
                        }
                        else if ($inp['idx'] < DB_CHARACTERS) // auth, world or aowow
                            $AoWoWconf[$databases[$inp['idx']]] = $curFields ?: array_combine(array_keys($dbFields), ['', '', '', '', '']);
                        else                                // existing char DB
                        {
                            $i = 0;
                            foreach ($AoWoWconf[$databases[DB_CHARACTERS]] as $realmId => &$dbInfo)
                            {
                                if ($inp['idx'] - DB_CHARACTERS != $i++)
                                    continue;

                                if ($curFields)
                                    $dbInfo = $curFields;
                                else
                                    unset($AoWoWconf[$databases[DB_CHARACTERS]][$realmId]);
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
