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
        return 'mysql://'.$options['user'].':'.$options['pass'].'@'.$options['host'].'/'.$options['db'];
    }

    public static function connect($idx)
    {
        if (self::isConnected($idx))
            return;

        $options = &self::$optionsCache[$idx];
        $interface = DbSimple_Generic::connect(self::createConnectSyntax($options));

        if (!$interface)
            die('Failed to connect to database.');

        $interface->setErrorHandler(array('DB', 'errorHandler'));
        $interface->query('SET NAMES ?', 'utf8');
        if ($options['prefix'])
            $interface->setIdentPrefix($options['prefix']);

        self::$interfaceCache[$idx] = &$interface;
        self::$connectionCache[$idx] = true;
    }

    public static function errorHandler($message, $data)
    {
        if (!error_reporting())
            return;

        echo "DB ERROR:<br /><br />\n\n<pre>";
        print_r($data);
        echo "</pre>";
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

    private static function safeGetDB($idx)
    {
        if(!self::isConnected($idx))
            self::connect($idx);

        return self::getDB($idx);
    }

    /**
     * @static
     * @return DbSimple_Mysql
     */
    public static function Characters($realm_id)
    {
        if (!isset(self::$optionsCache[DB_CHARACTERS+$realm_id]))
            die('Connection info not found for live database of realm #'.$realm_id.'. Aborted.');

        return self::safeGetDB(DB_CHARACTERS+$realm_id);
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
