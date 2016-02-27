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
    private static $connectionCache = [];

    private static function createConnectSyntax(&$options)
    {
        return 'mysqli://'.$options['user'].':'.$options['pass'].'@'.$options['host'].'/'.$options['db'];
    }

    public static function connect($idx)
    {
        if (self::isConnected($idx))
            return;

        $options = &self::$optionsCache[$idx];
        $interface = DbSimple_Generic::connect(self::createConnectSyntax($options));

        if (!$interface || $interface->error)
            die('Failed to connect to database.');

        $interface->setErrorHandler(['DB', 'errorHandler']);
        $interface->query('SET NAMES ?', 'utf8');
        if ($options['prefix'])
            $interface->setIdentPrefix($options['prefix']);

        // kill STRICT_TRANS_TABLES and STRICT_ALL_TABLES off. It prevents usage of implicit default values.
        if ($idx == DB_AOWOW)
            $interface->query('SET SESSION sql_mode = \'NO_ENGINE_SUBSTITUTION\'');

        self::$interfaceCache[$idx] = &$interface;
        self::$connectionCache[$idx] = true;
    }

    public static function errorHandler($message, $data)
    {
        if (!error_reporting())
            return;

        $error = "DB ERROR:<br /><br />\n\n<pre>".print_r($data, true)."</pre>";

        echo CLI ? strip_tags($error) : $error;
        exit;
    }

    public static function getDB($idx)
    {
        return self::$interfaceCache[$idx];
    }

    public static function isConnected($idx)
    {
        return isset(self::$connectionCache[$idx]);
    }

    public static function isConnectable($idx)
    {
        return isset(self::$optionsCache[$idx]);
    }

    private static function safeGetDB($idx)
    {
        if (!self::isConnected($idx))
            self::connect($idx);

        return self::getDB($idx);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Characters($realm)
    {
        if (!isset(self::$optionsCache[DB_CHARACTERS.$realm]))
            die('Connection info not found for live database of realm #'.$realm.'. Aborted.');

        return self::safeGetDB(DB_CHARACTERS.$realm);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Auth()
    {
        return self::safeGetDB(DB_AUTH);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function World()
    {
        return self::safeGetDB(DB_WORLD);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Aowow()
    {
        return self::safeGetDB(DB_AOWOW);
    }

    public static function load($idx, $config)
    {
        self::$optionsCache[$idx] = $config;
    }
}

?>
