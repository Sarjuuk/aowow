<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    Class designed by LordJZ for Aowow3

    https://github.com/LordJZ/aowow3/
*/

class DB
{
    private static $interfaceCache  = [];
    private static $optionsCache    = [];

    private static $logs            = [];

    private static function createConnectSyntax(&$options)
    {
        return 'mysqli://'.urlencode($options['user']).':'.urlencode($options['pass']).'@'.$options['host'].'/'.$options['db'];
    }

    public static function connect($idx)
    {
        if (self::isConnected($idx))
            return;

        $options = &self::$optionsCache[$idx];
        $interface = DbSimple_Generic::connect(self::createConnectSyntax($options));

        $interface->setErrorHandler(['DB', 'errorHandler']);
        if ($options['prefix'])
            $interface->setIdentPrefix($options['prefix']);

        self::$interfaceCache[$idx] = &$interface;

        // should be caught by registered error handler
        if (!$interface || !$interface->link)
            return;

        $interface->query('SET NAMES ?', 'utf8mb4');

        // disable STRICT_TRANS_TABLES and STRICT_ALL_TABLES off. It prevents usage of implicit default values.
        // disable ONLY_FULL_GROUP_BY (Allows for non-aggregated selects in a group-by query)
        $extraModes = ['STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'ONLY_FULL_GROUP_BY', 'NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'ERROR_FOR_DIVISION_BY_ZERO'];
        $oldModes   = explode(',', $interface->selectCell('SELECT @@sql_mode'));
        $newModes = array_diff($oldModes, $extraModes);

        if ($oldModes != $newModes)
            $interface->query("SET SESSION sql_mode = ?", implode(',', $newModes));
    }

    public static function test(array $options, ?string &$err = '') : bool
    {
        $defPort = ini_get('mysqli.default_port');
        $port = 0;
        if (strstr($options['host'], ':'))
            [$options['host'], $port] = explode(':', $options['host']);

        if ($link = mysqli_connect($options['host'], $options['user'], $options['pass'], $options['db'], $port ?: $defPort))
        {
            mysqli_close($link);
            return true;
        }

        $err = '['.mysqli_connect_errno().'] '.mysqli_connect_error();
        return false;
    }

    public static function errorHandler($message, $data)
    {
        if (!error_reporting())
            return;

        // continue on warning, end on error
        $isError = $data['code'] > 0;

        // make number sensible again
        $data['code'] = abs($data['code']);

        if (defined('CFG_DEBUG') && CFG_DEBUG)
        {
            echo "\nDB ERROR\n";
            foreach ($data as $k => $v)
                echo '  '.str_pad($k.':', 10).$v."\n";
        }

        trigger_error($message, $isError ? E_USER_ERROR : E_USER_WARNING);
    }

    public static function profiler($self, $query, $trace)
    {
        if ($trace)                                         // actual query
            self::$logs[] = [substr(str_replace("\n", ' ', $query), 0, 200)];
        else                                                // the statistics
        {
            end(self::$logs);
            self::$logs[key(self::$logs)][] = substr(explode(';', $query)[0], 5);
        }
    }

    public static function getProfiles()
    {
        $out = '<pre><table style="font-size:12;"><tr><th></th><th>Time</th><th>Query</th></tr>';
        foreach (self::$logs as $i => [$l, $t])
        {
            $c = 'inherit';
            preg_match('/(\d+)/', $t, $m);
            if ($m[1] > 100)
                $c = '#FFA0A0';
            else if ($m[1] > 20)
                $c = '#FFFFA0';

            $out .= '<tr><td>'.$i.'.</td><td style="background-color:'.$c.';">'.$t.'</td><td>'.$l.'</td></tr>';
        }

        return Util::jsEscape($out).'</table></pre>';
    }

    public static function getDB($idx)
    {
        return self::$interfaceCache[$idx];
    }

    public static function isConnected($idx)
    {
        return isset(self::$interfaceCache[$idx]) && self::$interfaceCache[$idx]->link;
    }

    public static function isConnectable($idx)
    {
        return isset(self::$optionsCache[$idx]);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Characters($realm)
    {
        if (!isset(self::$optionsCache[DB_CHARACTERS.$realm]))
            die('Connection info not found for live database of realm #'.$realm.'. Aborted.');

        return self::getDB(DB_CHARACTERS.$realm);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Auth()
    {
        return self::getDB(DB_AUTH);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function World()
    {
        return self::getDB(DB_WORLD);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Aowow()
    {
        return self::getDB(DB_AOWOW);
    }

    public static function load($idx, $config)
    {
        self::$optionsCache[$idx] = $config;
        self::connect($idx);
    }
}

?>
