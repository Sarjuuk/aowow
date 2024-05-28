<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class Cfg
{
    public const PATTERN_CONF_KEY      = '/[a-z0-9_\.\-]/i';
    public const PATTERN_INV_CONF_KEY  = '/[^a-z0-9_\.\-]/i';
    public const PATTERN_INVALID_CHARS = '/\p{C}/ui';

    // config flags
    public const FLAG_TYPE_INT    = 0x001;                   // validate with intVal()
    public const FLAG_TYPE_FLOAT  = 0x002;                   // validate with floatVal()
    public const FLAG_TYPE_BOOL   = 0x004;                   // 0 || 1
    public const FLAG_TYPE_STRING = 0x008;                   //
    public const FLAG_OPT_LIST    = 0x010;                   // single option
    public const FLAG_BITMASK     = 0x020;                   // multiple options
    public const FLAG_PHP         = 0x040;                   // applied with ini_set() [restrictions apply!]
    public const FLAG_PERSISTENT  = 0x080;                   // can not be deleted
    public const FLAG_REQUIRED    = 0x100;                   // required to have non-empty value
    public const FLAG_ON_LOAD_FN  = 0x200;                   // run static function of the same name after load
    public const FLAG_ON_SET_FN   = 0x400;                   // run static function of the same name as validator
    public const FLAG_INTERNAL    = 0x800;                   // can not be configures, automaticly calculated, skip on lists

    public const CAT_MISCELLANEOUS   = 0;
    public const CAT_SITE            = 1;
    public const CAT_CACHE           = 2;
    public const CAT_ACCOUNT         = 3;
    public const CAT_SESSION         = 4;
    public const CAT_SITE_REPUTATION = 5;
    public const CAT_ANALYTICS       = 6;
    public const CAT_PROFILER        = 7;

    public static $categories = array(                      // don't mind the ordering ... please?
        1 => 'Site', 'Caching', 'Account', 'Session', 'Site Reputation', 'Google Analytics', 'Profiler', 0 => 'Other'
    );

    private const IDX_VALUE    = 0;
    private const IDX_FLAGS    = 1;
    private const IDX_CATEGORY = 2;
    private const IDX_DEFAULT  = 3;
    private const IDX_COMMENT  = 4;

    private static $store = [];                             // name => [value, flags, cat, default, comment]

    private static $rebuildScripts = array(
    //  'rep_req_border_unco' => ['global'],                // currently not a template or buildScript
    //  'rep_req_border_rare' => ['global'],
    //  'rep_req_border_epic' => ['global'],
    //  'rep_req_border_lege' => ['global'],
        'profiler_enable'     => ['realms', 'realmMenu'],
        'battlegroup'         => ['realms', 'realmMenu'],
        'name_short'          => ['searchplugin', 'searchboxBody', 'searchboxScript', 'demo'],
        'site_host'           => ['searchplugin', 'searchboxBody', 'searchboxScript', 'demo', 'power'],
        'static_host'         => ['searchplugin', 'searchboxBody', 'searchboxScript', 'power'],
        'contact_email'       => ['markup'],
        'locales'             => ['locales']
    );

    public static function load() : void
    {
        if (!DB::isConnectable(DB_AOWOW))
            return;

        $sets = DB::Aowow()->select('SELECT `key` AS ARRAY_KEY, `value` AS "0", `flags` AS "1", `cat` AS "2", `default` AS "3", `comment` AS "4" FROM ?_config ORDER BY `key` ASC');
        foreach ($sets as $key => [$value, $flags, $catg, $default, $comment])
        {
            $php = $flags & self::FLAG_PHP;

            if ($err = self::validate($value, $flags, $comment))
            {
                trigger_error('Aowow config '.strtoupper($key).' failed validation and was skipped: '.$err, E_USER_ERROR);
                continue;
            }

            if ($flags & self::FLAG_INTERNAL)
            {
                trigger_error('Aowow config '.strtoupper($key).' is flagged as internaly generated and should not have been set in DB.', E_USER_ERROR);
                continue;
            }

            if ($flags & self::FLAG_ON_LOAD_FN)
            {
                if (!method_exists('Cfg', $key))
                    trigger_error('Aowow config '.strtoupper($key).' flagged for onLoadFN handling, but no handler was set', E_USER_WARNING);
                else
                    self::{$key}($value);
            }

            if ($php)
                ini_set(strtolower($key), $value);

            self::$store[strtolower($key)] = [$value, $flags, $catg, $default, $comment];
        }
    }

    public static function add(string $key, /*int|string*/ $value) : string
    {
        if (!$key)
            return 'empty option name given';

        $key = strtolower($key);

        if (preg_match(self::PATTERN_INV_CONF_KEY,  $key))
            return 'invalid chars in option name: [a-z 0-9 _ . -] are allowed';

        if (isset(self::$store[$key]))
            return 'this configuration option is already in use';

        if ($errStr = self::validate($value))
            return $errStr;

        if (ini_get($key) === false || ini_set($key, $value) === false)
            return 'this configuration option cannot be set';

        $flags = self::FLAG_TYPE_STRING | self::FLAG_PHP;
        if (!DB::Aowow()->query('INSERT IGNORE INTO ?_config (`key`, `value`, `cat`, `flags`) VALUES (?, ?, ?d, ?d)', $key, $value, self::CAT_MISCELLANEOUS, $flags))
            return 'internal error';

        self::$store[$key] = [$value, $flags, self::CAT_MISCELLANEOUS, null, null];
        return '';
    }

    public static function delete(string $key) : string
    {
        $key = strtolower($key);

        if (!isset(self::$store[$key]))
            return 'configuration option not found';

        if (self::$store[$key][self::IDX_FLAGS] & self::FLAG_PERSISTENT)
            return 'can\'t delete persistent options';

        if (!(self::$store[$key][self::IDX_FLAGS] & self::FLAG_PHP))
            return 'can\'t delete non-php options';

        if (self::$store[$key][self::IDX_FLAGS] & self::FLAG_INTERNAL)
            return 'can\'t delete internal options';

        if (!DB::Aowow()->query('DELETE FROM ?_config WHERE `key` = ? AND (`flags` & ?d) = 0 AND (`flags` & ?d) > 0', $key, self::FLAG_PERSISTENT, self::FLAG_PHP))
            return 'internal error';

        unset(self::$store[$key]);
        return '';
    }

    public static function get(string $key, bool $fromDB = false, bool $fullInfo = false) // : int|float|string
    {
        $key = strtolower($key);

        if (!isset(self::$store[$key]))
        {
            trigger_error('cfg not defined: '.$key, E_USER_ERROR);
            return '';
        }

        if ($fromDB && $fullInfo)
            return array_values(DB::Aowow()->selectRow('SELECT `value`, `flags`, `cat`, `default`, `comment` FROM ?_config WHERE `key` = ?', $key));
        if ($fromDB)
            return DB::Aowow()->selectCell('SELECT `value` FROM ?_config WHERE `key` = ?', $key);
        if ($fullInfo)
            return self::$store[$key];

        return self::$store[$key][self::IDX_VALUE];
    }

    public static function set(string $key, /*int|string*/ $value, ?array &$rebuildFiles = []) : string
    {
        $key = strtolower($key);

        if (!isset(self::$store[$key]))
            return 'configuration option not found';

        [$oldValue, $flags, , , $comment] = self::$store[$key];

        if ($flags & self::FLAG_INTERNAL)
            return 'can\'t set internal options directly';

        if ($err = self::validate($value, $flags, $comment))
            return $err;

        if ($flags & self::FLAG_REQUIRED && !strlen($value))
            return 'empty value given for required config';

        DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $value, $key);

        self::$store[$key][self::IDX_VALUE] = $value;

        // validate change
        if ($flags & self::FLAG_ON_SET_FN)
        {
            $errMsg = '';
            if (!method_exists('Cfg', $key))
                $errMsg = 'required onSetFN validator not set';
            else
                self::{$key}($value, $errMsg);

            if ($errMsg)
            {
                // rollback change
                DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $oldValue, $key);
                self::$store[$key][self::IDX_VALUE] = $oldValue;

                // trigger_error($errMsg) ?
                return $errMsg;
            }
        }

        if ($flags & self::FLAG_ON_LOAD_FN)
        {
            if (!method_exists('Cfg', $key))
                return 'Aowow config '.strtoupper($key).' flagged for onLoadFN handling, but no handler was set';
            else
                self::{$key}($value);
        }

        // trigger setup build
        return self::handleFileBuild($key, $rebuildFiles);
    }

    public static function reset(string $key, ?array &$rebuildFiles = []) : string
    {
        $key = strtolower($key);

        if (!isset(self::$store[$key]))
            return 'configuration option not found';

        [, $flags, , $default, ] = self::$store[$key];
        if ($flags & self::FLAG_INTERNAL)
            return 'can\'t set internal options directly';

        if (!$default)
            return 'config option has no default value';

        // @eval .. some dafault values are supplied as bitmask or the likes
        if (!($flags & Cfg::FLAG_TYPE_STRING))
            $default = @eval('return ('.$default.');');

        DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $default, $key);
        self::$store[$key][self::IDX_VALUE] = $default;

        // trigger setup build
        return self::handleFileBuild($key, $rebuildFiles);
    }

    public static function forCategory(int $category) : Generator
    {
        foreach (self::$store as $k => [, $flags, $catg, , ])
            if ($catg == $category && !($flags & self::FLAG_INTERNAL))
                yield $k => self::$store[$k];
    }

    public static function applyToString(string $string) : string
    {
        return preg_replace_callback(
            ['/CFG_([A-Z_]+)/', '/((HOST|STATIC)_URL)/'],
            function ($m) {
                if (!isset(self::$store[strtolower($m[1])]))
                    return $m[1];

                [$val, $flags, , , ] = self::$store[strtolower($m[1])];
                return $flags & (self::FLAG_TYPE_FLOAT | self::FLAG_TYPE_INT) ? Lang::nf($val) : $val;
            },
            $string
        );
    }


    /************/
    /* internal */
    /************/

    private static function validate(&$value, int $flags = self::FLAG_TYPE_STRING | self::FLAG_PHP, string $comment = ' - ') : string
    {
        $value = preg_replace(self::PATTERN_INVALID_CHARS, '', $value);

        if (!($flags & (self::FLAG_TYPE_BOOL | self::FLAG_TYPE_FLOAT | self::FLAG_TYPE_INT | self::FLAG_TYPE_STRING)))
            return 'no type set for value';

        if ($flags & self::FLAG_TYPE_INT && !Util::checkNumeric($value, NUM_CAST_INT))
            return 'value must be integer';

        if ($flags & self::FLAG_TYPE_FLOAT && !Util::checkNumeric($value, NUM_CAST_FLOAT))
            return 'value must be float';

        if ($flags & self::FLAG_OPT_LIST)
        {
            $info = explode(' - ', $comment)[1];
            foreach (explode(', ', $info) as $option)
                if (explode(':', $option)[0] == $value)
                    return '';

            return 'value not in range';
        }

        if ($flags & self::FLAG_BITMASK)
        {
            $mask = 0x0;
            $info = explode(' - ', $comment)[1];
            foreach (explode(', ', $info) as $option)
                $mask |= (1 << explode(':', $option)[0]);

            if (!($value &= $mask))
                return 'value not in range';
        }

        if ($flags & self::FLAG_TYPE_BOOL)
            $value = (bool)$value;

        return '';
    }

    private static function handleFileBuild(string $key, array &$rebuildFiles) : string
    {
        if (!isset(self::$rebuildScripts[$key]))
            return '';

        $msg = '';

        if (CLI)
        {
            $rebuildFiles = array_merge($rebuildFiles, self::$rebuildScripts[$key]);
            return '';
        }

        // not in CLI mode and build() can only be run from CLI. .. todo: other options..?
        exec('php aowow --build='.implode(',', self::$rebuildScripts[$key]), $out);
        foreach ($out as $o)
            if (strstr($o, 'ERR'))
                $msg .= explode('0m]', $o)[1]."<br />\n";

        return $msg;
    }

    private static function acc_auth_mode(/*int|string*/ $value, ?string $msg = '') : bool
    {
        if ($value == 1 && !extension_loaded('gmp'))
        {
            $msg .= 'PHP extension GMP is required to use TrinityCore as auth source, but is not currently enabled.';
            return false;
        }

        return true;
    }

    private static function profiler_enable(/*int|string*/ $value, ?string $msg = '') : bool
    {
        if ($value != 1)
            return true;

        return Profiler::queueStart($msg);
    }

    private static function static_host(/*int|string*/ $value, ?string $msg = '') : bool
    {
        self::$store['static_url'] = array(                 // points js to images & scripts
            (self::useSSL() ? 'https://' : 'http://').$value,
            self::FLAG_PERSISTENT | self::FLAG_TYPE_STRING | self::FLAG_INTERNAL,
            self::CAT_SITE,
            null,                                           // no default value
            null,                                           // no comment/info
        );

        return true;
    }

    private static function site_host(/*int|string*/ $value, ?string $msg = '') : bool
    {
        self::$store['host_url'] = array(                   // points js to executable files
            (self::useSSL() ? 'https://' : 'http://').$value,
            self::FLAG_PERSISTENT | self::FLAG_TYPE_STRING | self::FLAG_INTERNAL,
            self::CAT_SITE,
            null,                                           // no default value
            null,                                           // no comment/info
        );

        return true;
    }

    private static function useSSL() : bool
    {
        return (($_SERVER['HTTPS'] ?? 'off') != 'off') || (self::$store['force_ssl'][self::IDX_VALUE] ?? 0);
    }
}

?>
