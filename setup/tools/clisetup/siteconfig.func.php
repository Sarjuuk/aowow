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
    $updScripts = [];

    if (!DB::isConnected(DB_AOWOW))
    {
        CLI::write('Database not yet set up!', CLI::LOG_WARN);
        CLI::write('Please use '.CLI::bold('"php aowow --dbconfig"').' for setup', CLI::LOG_BLANK);
        CLI::write();
        return;
    }

    function toOptList(string $options, $curVal, bool $bitmask = false) : string
    {
        $result = '';
        foreach (explode(', ', $options) as $opt)
        {
            [$val, $name] = explode(':', $opt);
            $equal = $bitmask ? ($curVal & (1 << $val)) : $curVal == $val;

            $result .= '['.($equal ? 'x' : ' ').']'.$name.' ';
        }

        return substr($result, 0, -1);
    }

    function formatValue($value, $flags, $opts) : string
    {
        if ($flags & Cfg::FLAG_TYPE_BOOL)
            return '[bool]  '.($value ? '<Enabled>' : '<Disabled>');

        if ($flags & Cfg::FLAG_OPT_LIST)
            return '[opt]   '.toOptList($opts, $value, false);

        if ($flags & Cfg::FLAG_BITMASK)
            return '[mask]  '.toOptList($opts, $value, true);

        if ($flags & Cfg::FLAG_TYPE_FLOAT)
            return '[float] '.floatVal($value);

        if ($flags & Cfg::FLAG_TYPE_INT)
            return '[int]   '.intVal($value);

        // if ($flags & Cfg::FLAG_TYPE_STRING)
        if ($value === '')
            return '[str]   '.(($flags & Cfg::FLAG_REQUIRED) ? CLI::red('<empty>') : CLI::grey('<empty>'));
        else
            return '[str]   "'.$value.'"';
    }

    while (true)
    {
        CLI::write('select a numerical index or name to use the corresponding entry');
        CLI::write();

        $sumNum   = 0;
        $cfgList  = [];
        $hasEmpty = false;
        $listBuff = [];

        foreach (Cfg::$categories as $idx => $cat)
        {
            $listBuff[] = '=====  '.$cat.'  =====';

            foreach (Cfg::forCategory($idx) as $key => [$value, $flags, $catg, $default, $comment])
            {
                $isPhp = $flags & Cfg::FLAG_PHP;

                if ($value === '' && ($flags & Cfg::FLAG_REQUIRED))
                    $hasEmpty = true;

                $cfgList[$sumNum] = strtolower($key);

                $row  = '['.CLI::bold($sumNum).'] '.(($sumNum) > 9 ? '' : ' ').($isPhp ? '  PHP   ' : ' AOWOW  ');
                $row .= str_pad($isPhp ? strtolower($key) : strtoupper($key), 35);

                $opts = explode(' - ', $comment);
                $row .= formatValue($value, $flags, $opts[1] ?? '');

                $listBuff[] = $row;
                $sumNum++;
            }
        }

        foreach ($listBuff as $b)
            CLI::write($b);

        CLI::write(str_pad('['.CLI::bold($sumNum).']', 21).'add another php configuration');
        CLI::write();

        if ($hasEmpty)
        {
            CLI::write('please configure the required empty settings', CLI::LOG_WARN);
            CLI::write();
        }

        $inp = ['idx' => ['', false, Cfg::PATTERN_CONF_KEY]];
        if (CLI::read($inp) && $inp && $inp['idx'] !== '')
        {
            $idx = array_search(strtolower($inp['idx']), $cfgList);
            if ($idx === false)
                $idx = intVal($inp['idx']);

            // add new php setting
            if ($idx == $sumNum)
            {
                CLI::write('Adding additional php configuration.');
                CLI::write();

                while (true)
                {
                    $setting = array(
                        'key' => ['option name', false, Cfg::PATTERN_CONF_KEY],
                        'val' => ['value',                            ]
                    );
                    if (CLI::read($setting) && $setting)
                    {
                        $key = strtolower($setting['key']);
                        if ($err = Cfg::add($key, $setting['val']))
                            CLI::write($err, CLI::LOG_ERROR);
                        else
                            CLI::write('new php configuration added', CLI::LOG_OK);

                        sleep(1);
                        CLI::write();
                        break;
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
            // edit existing setting
            else if ($idx >= 0 && $idx < $sumNum)
            {
                [$value, $flags, , $default, $comment] = Cfg::get($cfgList[$idx], false, true);
                $key  = $cfgList[$idx];
                $info = explode(' - ', $comment);
                $buff = '';

                $buff .= $flags & Cfg::FLAG_PHP ? 'PHP: ' : 'AOWOW: ';
                $buff .= $flags & Cfg::FLAG_PHP ? $key : 'Cfg::'.strtoupper($key);

                if (!empty($info[0]))
                    $buff .= ' - '.$info[0];

                CLI::write($buff);
                CLI::write();
                CLI::write('VALUE: '.formatValue($value, $flags, $info[1] ?? ''));
                CLI::write();
                CLI::write('['.CLI::bold('E').']dit');

                if (!($flags & Cfg::FLAG_PERSISTENT))
                    CLI::write('['.CLI::bold('D').']elete');

                if ($default)
                    CLI::write('['.CLI::bold('R').']estore Default - '.$default);

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

                                if ($flags & Cfg::FLAG_OPT_LIST)
                                {
                                    foreach (explode(', ', $info[1]) as $option)
                                    {
                                        [$val, $name] = explode(':', $option);
                                        CLI::write('['.CLI::bold($val).'] '.$name);
                                    }
                                    $single   = true;
                                    $pattern  = '/\d/';
                                }
                                else if ($flags & Cfg::FLAG_BITMASK)
                                {
                                    CLI::write('Bitmask: sum fields to select multiple options');
                                    foreach (explode(', ', $info[1]) as $option)
                                    {
                                        [$val, $name] = explode(':', $option);
                                        CLI::write('['.CLI::bold(1 << $val).']'.str_pad('', 6 - strlen(1 << $val)).$name);
                                    }
                                    $pattern  = '/\d+/';
                                }
                                else if ($flags & Cfg::FLAG_TYPE_BOOL)
                                {
                                    CLI::write('['.CLI::bold(0).'] Disabled');
                                    CLI::write('['.CLI::bold(1).'] Enabled');

                                    $single   = true;
                                    $pattern  = '/[01]/';
                                }

                                while (true)
                                {
                                    $use = $value;
                                    if (CLI::read($use, $single))
                                    {
                                        CLI::write();

                                        $inp = $use['idx'] ?? '';

                                        if ($err = Cfg::set($key, $inp, $updScripts))
                                        {
                                            CLI::write($err, CLI::LOG_ERROR);
                                            sleep(1);
                                            continue;
                                        }
                                        else
                                        {
                                            CLI::write('setting updated', CLI::LOG_OK);
                                            sleep(1);
                                            break 3;
                                        }
                                    }
                                    else
                                    {
                                        CLI::write('edit canceled! returning to selection...', CLI::LOG_INFO);
                                        sleep(1);
                                        break;
                                    }
                                }

                                break 2;
                            case 'R':           // restore default
                                if (!$default)
                                    continue 2;

                                if ($err = Cfg::reset($key, $updScripts))
                                    CLI::write($err, CLI::LOG_ERROR);
                                else
                                    CLI::write('default value restored', CLI::LOG_OK);

                                sleep(1);
                                break 2;
                            case 'D':           // delete config pair
                                if ($flags & Cfg::FLAG_PERSISTENT)
                                    continue 2;

                                if ($err = Cfg::delete($key))
                                    CLI::write($err, CLI::LOG_ERROR);
                                else
                                    CLI::write("php setting deleted ['".$key."': '".$value."']", CLI::LOG_OK);

                                sleep(1);
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
}

?>
