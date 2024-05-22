<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/****************************/
/* Configure Site variables */
/****************************/

function siteconfig() : void
{
    $reqKeys    = ['site_host', 'static_host'];
    $updScripts = [];

    if (!DB::isConnected(DB_AOWOW))
    {
        CLI::write('Database not yet set up!', CLI::LOG_WARN);
        CLI::write('Please use '.CLI::bold('"php aowow --dbconfig"').' for setup', CLI::LOG_BLANK);
        CLI::write();
        return;
    }

    $onChange = function($key, $val) use (&$updScripts)
    {
        $fn = null;

        switch ($key)
        {
            case 'battlegroup':
                array_push($updScripts, 'realms', 'realmMenu');
                break;
            case 'name_short':
                array_push($updScripts, 'searchboxBody', 'demo', 'searchplugin');
                break;
            case 'site_host':
                array_push($updScripts, 'searchplugin', 'demo', 'power', 'searchboxBody');
                break;
            case 'static_host':
                array_push($updScripts, 'searchplugin', 'power', 'searchboxBody', 'searchboxScript');
                break;
            case 'contact_email':
                array_push($updScripts, 'markup');
                break;
            case 'locales':
                array_push($updScripts, 'locales');
                CLI::write(' * remember to rebuild all static files for the language you just added.', CLI::LOG_INFO);
                CLI::write(' * you can speed this up by supplying the regionCode to the setup: '.CLI::bold('--locales=<regionCodes,> -f'));
                break;
            case 'profiler_enable':
                array_push($updScripts, 'realms', 'realmMenu');
                $fn = function($x) {
                    if (!$x)
                        return true;

                    $ok = Profiler::queueStart($msg);
                    if ($msg)
                        CLI::write($msg, CLI::LOG_ERROR);

                    return $ok;
                };
                break;
            case 'acc_auth_mode':
                $fn = function($x) {
                    if ($x == 1 && !extension_loaded('gmp'))
                    {
                        CLI::write('PHP extension GMP is required to use TrinityCore as auth source, but it is currently not enabled.', CLI::LOG_ERROR);
                        return false;
                    }

                    return true;
                };
                break;
            default:                                        // nothing to do, everything is fine
                return true;
        }

        return $fn ? $fn($val) : true;
    };

    while (true)
    {
        CLI::write('select a numerical index to use the corresponding entry');
        CLI::write();

        $sumNum   = 0;
        $cfgList  = [];
        $hasEmpty = false;
        $mainBuff = [];
        $miscBuff = [];                                     // catg 'misc' should come last

        foreach (Util::$configCats as $idx => $cat)
        {
            if ($idx)
                $mainBuff[] = '=====  '.$cat.'  =====';
            else
                $miscBuff[] = '=====  '.$cat.'  =====';

            $results  = DB::Aowow()->select('SELECT *, (flags & ?d) AS php FROM ?_config WHERE `cat` = ?d ORDER BY `key` ASC', CON_FLAG_PHP, $idx);

            foreach ($results as $num => $data)
            {
                if (!($data['flags'] & CON_FLAG_PHP) && $data['value'] === '' && in_array($data['key'], $reqKeys))
                    $hasEmpty = true;

                $cfgList[$sumNum + $num] = $data;

                $php   = $data['flags'] & CON_FLAG_PHP;
                $buff  = "[".CLI::bold($sumNum + $num)."] ".(($sumNum + $num) > 9 ? '' : ' ').($php ? '  PHP   ' : ' AOWOW  ');
                $buff .= str_pad($php ? strtolower($data['key']) : strtoupper($data['key']), 35);
                if ($data['value'] === '')
                    $buff .= in_array($data['key'], $reqKeys) ? CLI::red('<empty>') : '<empty>';
                else
                {
                    $info = explode(' - ', $data['comment']);

                    if ($data['flags'] & CON_FLAG_TYPE_BOOL)
                        $buff .= '[bool] '.($data['value'] ? '<Enabled>' : '<Disabled>');
                    else if ($data['flags'] & CON_FLAG_OPT_LIST && !empty($info[2]))
                    {
                        $buff .= "[opt]  ";
                        foreach (explode(', ', $info[2]) as $option)
                        {
                            $opt = explode(':', $option);
                            $buff .= '['.($data['value'] == $opt[0] ? 'x' : ' ').']'.$opt[1].' ';
                        }
                    }
                    else if ($data['flags'] & CON_FLAG_BITMASK && !empty($info[2]))
                    {
                        $buff .= "[mask] ";
                        foreach (explode(', ', $info[2]) as $option)
                        {
                            $opt = explode(':', $option);
                            $buff .= '['.($data['value'] & (1 << $opt[0]) ? 'x' : ' ').']'.$opt[1].' ';
                        }
                    }
                    else if ($data['flags'] & CON_FLAG_TYPE_STRING)
                        $buff .= "[str]  ".$data['value'];
                    else if ($data['flags'] & CON_FLAG_TYPE_FLOAT)
                        $buff .= "[float] ".floatVal($data['value']);
                    else /* if ($data['flags'] & CON_FLAG_TYPE_INT) */
                        $buff .= "[int]  ".intVal($data['value']);
                }

                if ($idx)
                    $mainBuff[] = $buff;
                else
                    $miscBuff[] = $buff;

            }

            $sumNum += count($results);
        }

        foreach ($mainBuff as $b)
            CLI::write($b);

        foreach ($miscBuff as $b)
            CLI::write($b);

        CLI::write(str_pad("[".CLI::bold($sumNum)."]", 21)."add another php configuration");
        CLI::write();

        if ($hasEmpty)
        {
            CLI::write("please configure the required empty settings", CLI::LOG_WARN);
            CLI::write();
        }

        $inp = ['idx' => ['', false, '/\d/']];
        if (CLI::read($inp) && $inp && $inp['idx'] !== '')
        {
            $inp['idx'] = intVal($inp['idx']);

            // add new php setting
            if ($inp['idx'] == $sumNum)
            {
                CLI::write("Adding additional php configuration.");
                CLI::write();

                while (true)
                {
                    $setting = array(
                        'key' => ['option name', false, '/[\w_\.\-]/i'],
                        'val' => ['value',                            ]
                    );
                    if (CLI::read($setting) && $setting)
                    {
                        $key = strtolower($setting['key']);
                        if (ini_get($key) === false || ini_set($key, $setting['val']) === false)
                        {
                            CLI::write("this configuration option cannot be set", CLI::LOG_ERROR);
                            sleep(1);
                        }
                        else if (DB::Aowow()->selectCell('SELECT 1 FROM ?_config WHERE `flags` & ?d AND `key` = ?', CON_FLAG_PHP, $key))
                        {
                            CLI::write("this configuration option is already in use", CLI::LOG_ERROR);
                            sleep(1);
                        }
                        else
                        {
                            DB::Aowow()->query('INSERT IGNORE INTO ?_config (`key`, `value`, `cat`, `flags`) VALUES (?, ?, 0, ?d)', $key, $setting['val'], CON_FLAG_TYPE_STRING | CON_FLAG_PHP);
                            CLI::write("new php configuration added", CLI::LOG_OK);
                            sleep(1);
                        }

                        CLI::write();
                        break;
                    }
                    else
                    {
                        CLI::write("edit canceled! returning to list...", CLI::LOG_INFO);
                        CLI::write();
                        sleep(1);
                        break;
                    }
                }
            }
            // edit existing setting
            else if ($inp['idx'] >= 0 && $inp['idx'] < $sumNum)
            {
                $conf = $cfgList[$inp['idx']];
                $info = explode(' - ', $conf['comment']);
                $key  = strtolower($conf['key']);
                $buff = '';

                $buff .= $conf['flags'] & CON_FLAG_PHP ? "  PHP: " : "AOWOW: ";
                $buff .= $conf['flags'] & CON_FLAG_PHP ? $key : strtoupper('cfg_'.$conf['key']);

                if (!empty($info[1]))
                    $buff .= " - ".$info[1];

                CLI::write($buff);
                CLI::write();

                $buff = "VALUE: ";

                if ($conf['flags'] & CON_FLAG_TYPE_BOOL)
                    $buff .= $conf['value'] ? '<Enabled>' : '<Disabled>';
                else if ($conf['flags'] & CON_FLAG_OPT_LIST && !empty($info[2]))
                {
                    foreach (explode(', ', $info[2]) as $option)
                    {
                        $opt   = explode(':', $option);
                        $buff .= '['.($conf['value'] == $opt[0] ? 'x' : ' ').'] '.$opt[1].' ';
                    }
                }
                else if ($conf['flags'] & CON_FLAG_BITMASK && !empty($info[2]))
                {
                    foreach (explode(', ', $info[2]) as $option)
                    {
                        $opt = explode(':', $option);
                        $buff .= '['.($conf['value'] & (1 << $opt[0]) ? 'x' : ' ').'] '.$opt[1].' ';
                    }
                }
                else if ($conf['flags'] & CON_FLAG_TYPE_STRING)
                    $buff .= $conf['value'];
                else if ($conf['flags'] & CON_FLAG_TYPE_FLOAT)
                    $buff .= floatVal($conf['value']);
                else /* if ($conf['flags'] & CON_FLAG_TYPE_INT) */
                    $buff .= intVal($conf['value']);

                CLI::write($buff);
                CLI::write();
                CLI::write("[".CLI::bold('E')."]dit");

                if (!($conf['flags'] & CON_FLAG_PERSISTENT))
                    CLI::write("[".CLI::bold('D')."]elete");

                if (strstr($info[0], 'default:'))
                    CLI::write("[".CLI::bold('R')."]estore Default - ".trim(explode('default:', $info[0])[1]));

                CLI::write();

                while (true)
                {
                    $action = ['idx' => ['', true, '/[edr]/i']];
                    if (CLI::read($action, true) && $action)
                    {
                        switch (strtoupper($action['idx']))
                        {
                            case 'E':           // edit value
                                $pattern = false;
                                $single  = false;
                                $value   = ['idx' => ['Select new value', false, &$pattern]];

                                if ($conf['flags'] & CON_FLAG_OPT_LIST)
                                {
                                    $_valid = [];
                                    foreach (explode(', ', $info[2]) as $option)
                                    {
                                        $opt = explode(':', $option);
                                        $_valid[] = $opt[0];
                                        CLI::write('['.CLI::bold($opt[0]).'] '.$opt[1]);
                                    }
                                    $single   = true;
                                    $pattern  = '/\d/';
                                    $validate = function ($v) use($_valid) { return in_array($v, $_valid); };
                                }
                                else if ($conf['flags'] & CON_FLAG_BITMASK)
                                {
                                    CLI::write('Bitmask: sum fields to select multiple options');
                                    $_valid = 0x0;
                                    foreach (explode(', ', $info[2]) as $option)
                                    {
                                        $opt = explode(':', $option);
                                        $_valid |= (1 << $opt[0]);
                                        CLI::write('['.CLI::bold(1 << $opt[0]).']'.str_pad('', 4-strlen(1 << $opt[0])).$opt[1]);
                                    }
                                    $pattern  = '/\d+/';
                                    $validate = function ($v) use($_valid) { $v = ($v ?: 0) & $_valid; return $v; };
                                }
                                else if ($conf['flags'] & CON_FLAG_TYPE_BOOL)
                                {
                                    CLI::write('['.CLI::bold(0).'] Disabled');
                                    CLI::write('['.CLI::bold(1).'] Enabled');

                                    $single   = true;
                                    $pattern  = '/[01]/';
                                    $validate = function ($v) { return true; };
                                }
                                else if ($conf['flags'] & CON_FLAG_TYPE_INT)
                                    $validate = function ($v) { return preg_match('/^-?\d+$/i', $v); };
                                else if ($conf['flags'] & CON_FLAG_TYPE_FLOAT)
                                    $validate = function ($v) { return preg_match('/^-?\d*(,|.)?\d+$/i', $v); };
                                else            // string
                                    $validate = function ($v) { return true; };


                                while (true)
                                {
                                    $use = $value;
                                    if (CLI::read($use, $single))
                                    {
                                        CLI::write();

                                        $inp = $use['idx'] ?? '';
                                        if (!$validate($inp))
                                        {
                                            CLI::write("value not in range", CLI::LOG_ERROR);
                                            sleep(1);
                                            continue;
                                        }
                                        else
                                        {
                                            $oldVal = DB::Aowow()->selectCell('SELECT `value` FROM ?_config WHERE `key` = ?', $key);
                                            DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $inp, $key);

                                            // postChange returned false => reset value
                                            if (!$onChange($key, $inp))
                                            {
                                                DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $oldVal, $key);
                                                sleep(1);
                                                break;
                                            }

                                            CLI::write("setting updated", CLI::LOG_OK);
                                            sleep(1);
                                            break 3;
                                        }
                                    }
                                    else
                                    {
                                        CLI::write("edit canceled! returning to selection...", CLI::LOG_INFO);
                                        sleep(1);
                                        break;
                                    }
                                }

                                break 2;
                            case 'R':           // restore default
                                if (!strstr($info[0], 'default:'))
                                    continue 2;

                                // @eval .. some dafault values are supplied as bitmask or the likes
                                $val = trim(explode('default:', $info[0])[1]);
                                if (!($conf['flags'] & CON_FLAG_TYPE_STRING))
                                    $val = @eval('return ('.$val.');');
                                if (DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $val, $key))
                                {
                                    CLI::write("default value restored", CLI::LOG_OK);
                                    $onChange($key, $val);
                                    sleep(1);
                                }
                                break 2;
                            case 'D':           // delete config pair
                                if ($conf['flags'] & CON_FLAG_PERSISTENT)
                                    continue 2;

                                if (DB::Aowow()->query('DELETE FROM ?_config WHERE `key` = ? AND (`flags` & ?d) = 0', $key, CON_FLAG_PERSISTENT))
                                {
                                    CLI::write("php setting deleted ['".$conf['key']."': '".$conf['value']."']", CLI::LOG_OK);
                                    sleep(1);
                                }
                                break 2;
                        }
                    }
                    else
                    {
                        CLI::write('edit canceled! returning to list...', CLI::LOG_INFO);
                        CLI::write();
                        sleep(1);
                        break;
                    }
                }
            }
            else
            {
                CLI::write('invalid selection', CLI::LOG_ERROR);
                CLI::write();
                sleep(1);
            }
        }
        else
        {
            CLI::write('leaving site configuration...', CLI::LOG_INFO);
            CLI::write();
            break;
        }

        // propagate changes to static files
        if ($updScripts && (!class_exists('FileGen') || FileGen::getMode() != FileGen::MODE_FIRSTRUN))
        {
            require_once 'setup/tools/clisetup/build.func.php';
            CLI::write();
            CLI::write('regenerating affected static content', CLI::LOG_INFO);
            CLI::write();
            sleep(1);

            if ($_ = array_diff($updScripts, build($updScripts)))
            {
                CLI::write(' - the following updates returned with errors, please recheck those - '.implode(', ', $_), CLI::LOG_ERROR);
                sleep(1);
            }

            sleep(1);
            $updScripts = [];
        }
    }

    // actually load set constants
    loadConfig(true);
}

?>
