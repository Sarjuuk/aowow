<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/****************************/
/* Configure Site variables */
/****************************/

function siteconfig()
{
    if (!DB::isConnected(DB_AOWOW))
    {
        CLISetup::log();
        CLISetup::log("database not yet set up!\n        Please use --dbconfig for setup", CLISetup::LOG_WARN);
        return;
    }

    while (true)
    {
        CLISetup::log();
        CLISetup::log('select a numerical index to use the corresponding entry');

        $results  = DB::Aowow()->select('SELECT *, (flags & ?d) AS php FROM ?_config ORDER BY php ASC', CON_FLAG_PHP);
        $hasEmpty = false;

        foreach ($results as $idx => $data)
        {
            if (!($data['flags'] & CON_FLAG_PHP) && $data['value'] === '')
                $hasEmpty = true;

            $php   = $data['flags'] & CON_FLAG_PHP;
            $buff  = "[".CLISetup::bold($idx)."] ".($idx > 9 ? '' : ' ').($php ? '  PHP   ' : ' AOWOW  ');
            $buff .= str_pad($php ? strtolower($data['key']) : strtoupper('cfg_'.$data['key']), 35);
            if ($data['value'] === '')
                $buff .= CLISetup::red('<empty>');
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

            CLISetup::log($buff);
        }

        CLISetup::log(str_pad("[".CLISetup::bold(count($results))."]", 21)."add another php configuration");

        if ($hasEmpty)
        {
            CLISetup::log();
            CLISetup::log("please configure the required empty setings", CLISetup::LOG_WARN);
        }

        $inp = ['idx' => ['', false, '/\d/']];
        if (CLISetup::readInput($inp) && $inp && $inp['idx'] !== '')
        {
            // add new php setting
            if ($inp['idx'] == count($results))
            {
                CLISetup::log();
                CLISetup::log("Adding additional php configuration.");

                while (true)
                {
                    $setting = array(
                        'key' => ['option name', false, '/[\w_\.\-]/i'],
                        'val' => ['value',                            ]
                    );
                    if (CLISetup::readInput($setting) && $setting)
                    {
                        CLISetup::log();

                        $key = strtolower($setting['key']);
                        if (ini_get($key) === false || ini_set($key, $setting['val']) === false)
                        {
                            CLISetup::log("this configuration option cannot be set", CLISetup::LOG_ERROR);
                            sleep(1);
                        }
                        else if (DB::Aowow()->selectCell('SELECT 1 FROM ?_config WHERE `flags` & ?d AND `key` = ?', CON_FLAG_PHP, $key))
                        {
                            CLISetup::log("this configuration option is already in use", CLISetup::LOG_ERROR);
                            sleep(1);
                        }
                        else
                        {
                            DB::Aowow()->query('INSERT IGNORE INTO ?_config (`key`, `value`, `flags`) VALUES (?, ?, ?d)', $key, $setting['val'], CON_FLAG_TYPE_STRING | CON_FLAG_PHP);
                            CLISetup::log("new php configuration added", CLISetup::LOG_OK);
                            sleep(1);
                        }

                        break;
                    }
                    else
                    {
                        CLISetup::log();
                        CLISetup::log("edit canceled! returning to list...", CLISetup::LOG_WARN);
                        sleep(1);
                        break;
                    }
                }
            }
            // edit existing setting
            else if ($inp['idx'] >= 0 && $inp['idx'] < count($results))
            {
                $conf = $results[$inp['idx']];
                $info = explode(' - ', $conf['comment']);
                $buff = '';

                CLISetup::log();
                $buff .= $conf['flags'] & CON_FLAG_PHP ? "  PHP: " : "AOWOW: ";
                $buff .= $conf['flags'] & CON_FLAG_PHP ? strtolower($conf['key']) : strtoupper('cfg_'.$conf['key']);

                if (!empty($info[1]))
                    $buff .= " - ".$info[1];

                CLISetup::log($buff);

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

                CLISetup::log($buff);
                CLISetup::log();
                CLISetup::log("[".CLISetup::bold('E')."]dit");

                if (!($conf['flags'] & CON_FLAG_PERSISTENT))
                    CLISetup::log("[".CLISetup::bold('D')."]elete");

                if (strstr($info[0], 'default:'))
                    CLISetup::log("[".CLISetup::bold('R')."]estore Default - ".trim(explode('default:', $info[0])[1]));

                while (true)
                {
                    $action = ['idx' => ['', true, '/[edr]/i']];
                    if (CLISetup::readInput($action, true) && $action)
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
                                        CLISetup::log('['.CLISetup::bold($opt[0]).'] '.$opt[1]);
                                    }
                                    $single   = true;
                                    $pattern  = '/\d/';
                                    $validate = function ($v) use($_valid) { return in_array($v, $_valid); };
                                }
                                else if ($conf['flags'] & CON_FLAG_BITMASK)
                                {
                                    CLISetup::log('Bitmask: sum fields to select multiple options');
                                    $_valid = 0x0;
                                    foreach (explode(', ', $info[2]) as $option)
                                    {
                                        $opt = explode(':', $option);
                                        $_valid |= (1 << $opt[0]);
                                        CLISetup::log('['.CLISetup::bold(1 << $opt[0]).']'.str_pad('', 4-strlen(1 << $opt[0])).$opt[1]);
                                    }
                                    $pattern  = '/\d+/';
                                    $validate = function ($v) use($_valid) { $v = $v & $_valid; return $v; };
                                }
                                else if ($conf['flags'] & CON_FLAG_TYPE_BOOL)
                                {
                                    CLISetup::log('['.CLISetup::bold(0).'] Disabled');
                                    CLISetup::log('['.CLISetup::bold(1).'] Enabled');

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
                                    if (CLISetup::readInput($use, $single) && $use)
                                    {
                                        CLISetup::log();

                                        if (!$validate($use['idx']))
                                        {
                                            CLISetup::log("value not in range", CLISetup::LOG_ERROR);
                                            sleep(1);
                                            continue;
                                        }
                                        else
                                        {
                                            DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $use['idx'], strtolower($conf['key']));
                                            CLISetup::log("setting updated", CLISetup::LOG_OK);
                                            sleep(1);
                                            break 3;
                                        }
                                    }
                                    else
                                    {
                                        CLISetup::log("edit canceled! returning to selection...", CLISetup::LOG_WARN);
                                        sleep(1);
                                        break;
                                    }
                                }

                                break 2;
                            case 'R':           // restore default
                                if (!strstr($info[0], 'default:'))
                                    continue 2;

                                // @eval .. some dafault values are supplied as bitmask or the likes
                                if (DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', @eval('return ('.trim(explode('default:', $info[0])[1]).');'), strtolower($conf['key'])))
                                {
                                    CLISetup::log("default value restored", CLISetup::LOG_OK);
                                    sleep(1);
                                }
                                break 2;
                            case 'D':           // delete config pair
                                if ($conf['flags'] & CON_FLAG_PERSISTENT)
                                    continue 2;

                                if (DB::Aowow()->query('DELETE FROM ?_config WHERE `key` = ? AND (`flags` & ?d) = 0', strtolower($conf['key']), CON_FLAG_PERSISTENT))
                                {
                                    CLISetup::log("php setting deleted ['".$conf['key']."': '".$conf['value']."']", CLISetup::LOG_OK);
                                    sleep(1);
                                }
                                break 2;
                        }
                    }
                    else
                    {
                        CLISetup::log();
                        CLISetup::log("edit canceled! returning to list...", CLISetup::LOG_WARN);
                        sleep(1);
                        break;
                    }
                }
            }
            else
            {
                CLISetup::log();
                CLISetup::log("invalid selection", CLISetup::LOG_ERROR);
                sleep(1);
            }
        }
        else
        {
            CLISetup::log();
            CLISetup::log("site configuration aborted", CLISetup::LOG_WARN);
            break;
        }
    }
}

?>
