<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/****************************/
/* Configure Site variables */
/****************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvOpts    = ['c'];
    public $optGroup    = CLISetup::OPT_GRP_SETUP;
    public $followupFn  = 'build';

    public const COMMAND      = 'configure';
    public const APPENDIX     = ' [action<E|R|N|D> cfgName [newValue]]';
    public const DESCRIPTION  = 'Configure site variables.';
    public const PROMPT       = 'SITE_HOST and STATIC_HOST *must* be set. Also enable FORCE_SSL if needed. You may also want to change other variables such as NAME, NAME_SHORT or LOCALES.';
    public const NOTE_ERROR   = 'could not access:';

    public const REQUIRED_DB = [DB_AOWOW];

    public const USE_CLI_ARGS = true;

    private const HTTP_STATUS_OK = 200;
    private const HTTP_STATUS_MOVED_PERM = 301;
    private const HTTP_STATUS_MOVED_TEMP = 302;

    private $updScripts = [];

    // args: action, configName, configValue, pendingUpdates[] // iiio
    public function run(&$args) : bool
    {
        $action = $args[0] ?? '';
        $name   = $args[1] ?? '';
        $value  = $args[2] ?? '';

        $result = true;
        switch (strtoupper($action))
        {
            case 'E':
                $result = $this->doEdit($name, $args[2]);
                break;
            case 'R':
                $result = $this->doRestore($name);
                break;
            case 'N':
                $result = $this->doNew($name, $value);
                break;
            case 'D':
                $result = $this->doDelete($name);
                break;
            default:
                $this->showConfigList();
        }

        $args['doBuild'] = $this->updScripts;               // push files to rebuild one level up

        return $result;
    }

    private function showConfigList() : void
    {
        while (true)
        {
            CLI::write('select a numerical index or name to use the corresponding entry', -1, false);
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
                    $row .= $this->formatValue($value, $flags, $opts[1] ?? '');

                    $listBuff[] = $row;
                    $sumNum++;
                }
            }

            foreach ($listBuff as $b)
                CLI::write($b, -1, false);

            CLI::write(str_pad('['.CLI::bold($sumNum).']', 21).'add another php configuration', -1, false);
            CLI::write();

            if ($hasEmpty)
            {
                CLI::write('please configure the required empty settings', CLI::LOG_WARN);
                CLI::write();
            }

            if (CLI::read(['idx' => ['', false, false, Cfg::PATTERN_CONF_KEY]], $uiIndex) && $uiIndex && $uiIndex['idx'] !== '')
            {
                $idx = array_search(strtolower($uiIndex['idx']), $cfgList);
                if ($idx === false)
                    $idx = intVal($uiIndex['idx']);

                // add new php setting
                if ($idx == $sumNum)
                    $this->showNewConfig();
                // edit existing setting
                else if ($idx >= 0 && $idx < $sumNum)
                    $this->showEditConfig($cfgList[$idx] ?? '');
                else
                    CLI::write('invalid selection', CLI::LOG_ERROR);

                CLI::write();
                sleep(1);
            }
            else
            {
                CLI::write('leaving site configuration...', CLI::LOG_INFO);
                CLI::write();
                break;
            }
        }
    }

    private function showNewConfig() : void
    {
        CLI::write('Adding additional php configuration.');
        CLI::write();

        $setting = array(
            'key' => ['option name', false, false, Cfg::PATTERN_CONF_KEY],
            'val' => ['value']
        );
        if (CLI::read($setting, $uiSetting) && $uiSetting)
            $this->doNew($uiSetting['key'], $uiSetting['val']);
        else
            CLI::write('edit canceled! returning to list...', CLI::LOG_INFO);
    }

    private function showEditConfig(string $key) : void
    {
        [$value, $flags, , $default, $comment] = Cfg::get($key, false, true);
        $info = explode(' - ', $comment);
        $buff = '';

        $buff .= $flags & Cfg::FLAG_PHP ? 'PHP: ' : 'AOWOW: ';
        $buff .= $flags & Cfg::FLAG_PHP ? $key : 'Cfg::'.strtoupper($key);

        if (!empty($info[0]))
            $buff .= ' - '.$info[0];

        CLI::write($buff);
        CLI::write();

        CLI::write('VALUE: '.$this->formatValue($value, $flags, $info[1] ?? ''));
        CLI::write();
        CLI::write('['.CLI::bold('E').']dit');

        if (!($flags & Cfg::FLAG_PERSISTENT))
            CLI::write('['.CLI::bold('D').']elete');

        if ($default)
            CLI::write('['.CLI::bold('R').']estore Default - '.$default);

        CLI::write();

        while (true)
        {
            CLI::write();
            sleep(1);

            if (CLI::read(['idx' => ['', true, true, '/[edr]/i']], $uiEDR) && $uiEDR)
            {
                switch (strtoupper($uiEDR['idx']))
                {
                    case 'E':                               // edit value
                        if (!$this->doEdit($key))
                            continue 2;
                        break 2;
                    case 'R':                               // restore default
                        if (!$this->doRestore($key))
                            continue 2;
                        break 2;
                    case 'D':                               // delete config pair
                        if (!$this->doDelete($key))
                            continue 2;
                        break 2;
                }
            }
            else
            {
                CLI::write('edit canceled! returning to list...', CLI::LOG_INFO);
                break;
            }
        }
    }

    private function doEdit(string $key, ?string $newVal = null) : bool
    {
        [, $flags, , , $comment] = Cfg::get($key, false, true);
        $info = explode(' - ', $comment);

        $pattern  = '/.*/';
        $single   = false;
        $typeHint = [];

        if ($flags & Cfg::FLAG_OPT_LIST)
        {
            foreach (explode(', ', $info[1]) as $option)
            {
                [$val, $name] = explode(':', $option);
                $typeHint[] = '['.CLI::bold($val).'] '.$name;
            }
            $single  = true;
            $pattern = '/^\d$/';
        }
        else if ($flags & Cfg::FLAG_BITMASK)
        {
            foreach (explode(', ', $info[1]) as $option)
            {
                [$val, $name] = explode(':', $option);
                $typeHint[] = '['.CLI::bold(1 << $val).']'.str_pad('', 6 - strlen(1 << $val)).$name;
            }
            $typeHint[] = 'Bitmask: sum fields to select multiple options';
            $pattern = '/^\d+$/';
        }
        else if ($flags & Cfg::FLAG_TYPE_BOOL)
        {
            $typeHint[] = '['.CLI::bold(0).'] Disabled';
            $typeHint[] = '['.CLI::bold(1).'] Enabled';

            $single  = true;
            $pattern = '/^[01]$/';
        }
        else if ($flags & Cfg::FLAG_TYPE_INT)
            $pattern = '/^-?\d+$/';
        else if ($flags & Cfg::FLAG_TYPE_FLOAT)
            $pattern = '/^-?\d*(,|.)?\d+$/i';

        while (true)
        {
            if (!isset($newVal))
                foreach ($typeHint as $th)
                    CLI::write($th);

            if ((isset($newVal) && preg_match($pattern, $newVal)) || CLI::read(['idx' => ['Select new value', false, $single, $pattern]], $uiValue))
            {
                CLI::write();

                $val = $newVal ?? $uiValue['idx'] ?? '';
                unset($newVal);                             // we loop infinitely if this is set and invalid

                if ($err = Cfg::set($key, $val, $this->updScripts))
                {
                    CLI::write($err, CLI::LOG_ERROR);
                    continue;
                }
                else
                {
                    CLI::write('setting updated', CLI::LOG_OK);
                    return true;
                }
            }
            else
            {
                CLI::write('edit canceled! returning to selection...', CLI::LOG_INFO);
                return false;
            }
        }
    }

    private function doRestore(string $key) : bool
    {
        if ($err = Cfg::reset($key, $this->updScripts))
        {
            CLI::write($err, CLI::LOG_ERROR);
            return false;
        }

        CLI::write('default value restored', CLI::LOG_OK);
        return true;
    }

    private function doNew(string $key, string $val) : bool
    {
        if ($err = Cfg::add($key, $val))
        {
            CLI::write($err, CLI::LOG_ERROR);
            return false;
        }

        CLI::write('new php configuration added', CLI::LOG_OK);
        return true;
    }

    private function doDelete(string $key) : bool
    {
        if ($err = Cfg::delete($key))
        {
            CLI::write($err, CLI::LOG_ERROR);
            return false;
        }

        CLI::write('php setting deleted: '.$key, CLI::LOG_OK);
        return true;
    }


    /******************/
    /* Unit formating */
    /******************/

    private function toOptList(string $options, $curVal, bool $bitmask = false) : string
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

    private function formatValue($value, int $flags, string $opts) : string
    {
        if ($flags & Cfg::FLAG_TYPE_BOOL)
            return '[bool]  '.($value ? '<Enabled>' : '<Disabled>');

        if ($flags & Cfg::FLAG_OPT_LIST)
            return '[opt]   '.$this->toOptList($opts, $value, false);

        if ($flags & Cfg::FLAG_BITMASK)
            return '[mask]  '.$this->toOptList($opts, $value, true);

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


    /****************/
    /* Help display */
    /****************/

    public function writeCLIHelp(string ...$ss) : bool
    {
        CLI::write('  usage: php aowow --configure [action cfgName [newValue]]', -1, false);
        CLI::write();
        CLI::write('  Configures site and php variables. If incomplete parameters are passed an interactive prompt will open.', -1, false);
        CLI::write();
        CLI::write('  action:', -1, false);
        CLI::write('          E - Edit variable named '.CLI::bold('cfgName').'. Optionally directly pass a new value.', -1, false);
        CLI::write('          R - Restore default value of a variable '.CLI::bold('cfgName').'.', -1, false);
        CLI::write('          N - Create a new php config value. Must be a valid php directive. Optionally directly pass a new value.', -1, false);
        CLI::write('          D - Delete an existing php config value.', -1, false);
        CLI::write();
        CLI::write();

        return true;
    }


    /***********/
    /* DB test */
    /***********/

    public function test(?array &$error = []) : bool
    {
        $error = [];

        if (!DB::isConnected(DB_AOWOW))
        {
            $error[] = ' * not connected to DB';
            return false;
        }

        $prot  = Cfg::get('FORCE_SSL') ? 'https://' : 'http://';
        $cases = array(
            'site_host'   => [$prot, Cfg::get('SITE_HOST'),   '/robots.txt'],
            'static_host' => [$prot, Cfg::get('STATIC_HOST'), '/css/aowow.css']
        );

        foreach ($cases as $conf => [$protocol, $host, $testFile])
        {
            if ($host)
            {
                $resp = 0;
                if (!$this->testCase($protocol, $host, $testFile, $resp))
                {
                    if ($resp == self::HTTP_STATUS_MOVED_PERM || $resp == self::HTTP_STATUS_MOVED_TEMP)
                    {
                        CLI::write('self test received status '.CLI::bold($resp).' (page moved) for '.$conf.', pointing to: '.$protocol.$host.$testFile, CLI::LOG_WARN);
                        if (!CLI::read(['x' => ['should '.CLI::bold($conf).' be set to '.CLI::bold($host).' and force_ssl be updated? (y/n)', true, true, '/y|n/i']], $uiN) || !$uiN || strtolower($uiN['x']) == 'n')
                            $error[] = ' * '.$protocol.$host.$testFile.' ['.$resp.']';
                        else
                        {
                            Cfg::set($conf, $host);
                            Cfg::set('FORCE_SSL', $protocol == 'https://');
                        }

                        CLI::write();
                    }
                    else
                        $error[] = ' * '.$protocol.$host.$testFile.' ['.$resp.']';
                }
            }
            else
                $error[] = ' * '.strtoupper($conf).' is empty';
        }

        return empty($error);
    }

    private function testCase(&$protocol, &$host, $testFile, &$status) : bool
    {
        $res = get_headers($protocol.$host.$testFile, true);

        if (!preg_match('/HTTP\/[0-9\.]+\s+([0-9]+)/', $res[0], $m))
            return false;

        $status = $m[1];

        if ($status == self::HTTP_STATUS_OK)
            return true;

        if ($status == self::HTTP_STATUS_MOVED_PERM || $status == self::HTTP_STATUS_MOVED_TEMP)
        {
            if (!empty($res['Location']) && preg_match('/(https?:\/\/)(.*)'.strtr($testFile, ['/' => '\/', '.' => '\.']).'/i', is_array($res['Location']) ? $res['Location'][0] : $res['Location'], $n))
            {
                $protocol = $n[1];
                $host     = $n[2];
            }

            return false;
        }

        $status = 0;
        return false;
    }
});

?>
