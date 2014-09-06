<?php
/**
 * DbSimple_Generic: universal database connected by DSN.
 * (C) Dk Lab, http://en.dklab.ru
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Use static DbSimple_Generic::connect($dsn) call if you don't know
 * database type and parameters, but have its DSN.
 *
 * Additional keys can be added by appending a URI query string to the
 * end of the DSN.
 *
 * The format of the supplied DSN is in its fullest form:
 *   phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
 *
 * Most variations are allowed:
 *   phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
 *   phptype://username:password@hostspec/database_name
 *   phptype://username:password@hostspec
 *   phptype://username@hostspec
 *   phptype://hostspec/database
 *   phptype://hostspec
 *   phptype(dbsyntax)
 *   phptype
 *
 * Parsing code is partially grabbed from PEAR DB class,
 * initial author: Tomas V.V.Cox <cox@idecnet.com>.
 *
 * Contains 3 classes:
 * - DbSimple_Generic: database factory class
 * - DbSimple_Generic_Database: common database methods
 * - DbSimple_Generic_Blob: common BLOB support
 * - DbSimple_Generic_LastError: error reporting and tracking
 *
 * Special result-set fields:
 * - ARRAY_KEY* ("*" means "anything")
 * - PARENT_KEY
 *
 * Transforms:
 * - GET_ATTRIBUTES
 * - CALC_TOTAL
 * - GET_TOTAL
 * - UNIQ_KEY
 *
 * Query attributes:
 * - BLOB_OBJ
 * - CACHE
 *
 * @author Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
 * @author Konstantin Zhinko, http://forum.dklab.ru/users/KonstantinGinkoTit/
 *
 * @version 2.x $Id$
 */

/**
 * Use this constant as placeholder value to skip optional SQL block [...].
 */
if (!defined('DBSIMPLE_SKIP'))
	define('DBSIMPLE_SKIP', log(0));

/**
 * Names of special columns in result-set which is used
 * as array key (or karent key in forest-based resultsets) in
 * resulting hash.
 */
if (!defined('DBSIMPLE_ARRAY_KEY'))
	define('DBSIMPLE_ARRAY_KEY', 'ARRAY_KEY');   // hash-based resultset support
if (!defined('DBSIMPLE_PARENT_KEY'))
	define('DBSIMPLE_PARENT_KEY', 'PARENT_KEY'); // forrest-based resultset support


/**
 * DbSimple factory.
 */
class DbSimple_Generic
{
    /**
     * DbSimple_Generic connect(mixed $dsn)
     *
     * Universal static function to connect ANY database using DSN syntax.
     * Choose database driver according to DSN. Return new instance
     * of this driver.
     *
     * You can connect to MySQL by socket using this new syntax (like PDO DSN):
     * $dsn = 'mysqli:unix_socket=/cloudsql/app:instance;user=root;pass=;dbname=testdb';
     * $dsn = 'mypdo:unix_socket=/cloudsql/app:instance;charset=utf8;user=testuser;pass=mypassword;dbname=testdb';
     *
     * Connection by host also can be made with this syntax.
     * Or you can use old syntax:
     * $dsn = 'mysql://testuser:mypassword@127.0.0.1/testdb';
     *
     */
    public static function connect($dsn)
    {
        // Load database driver and create its instance.
        $parsed = DbSimple_Generic::parseDSN($dsn);
        if (!$parsed) {
            $dummy = null;
            return $dummy;
        }
        $class = 'DbSimple_'.ucfirst($parsed['scheme']);
        if (!class_exists($class)) {
            $file = __DIR__.'/'.ucfirst($parsed['scheme']). ".php";
            if (is_file($file)) {
                require_once($file);
            } else {
                trigger_error("Error loading database driver: no file $file", E_USER_ERROR);
                return null;
            }
        }
        $object = new $class($parsed);
        if (isset($parsed['ident_prefix'])) {
            $object->setIdentPrefix($parsed['ident_prefix']);
        }
        $object->setCachePrefix(md5(serialize($parsed['dsn'])));
        return $object;
    }


    /**
     * array parseDSN(mixed $dsn)
     * Parse a data source name.
     * See parse_url() for details.
     */
    public static function parseDSN($dsn)
    {
        if (is_array($dsn)) return $dsn;
        $parsed = parse_url($dsn);
        if (!$parsed) return null;

        $params = null;
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $parsed += $params;
        }

        if ( empty($parsed['host']) && empty($parsed['socket']) ) {
            // Parse as DBO DSN string
            $parsedPdo = self::parseDsnPdo($parsed['path']);
            unset($parsed['path']);
            $parsed = array_merge($parsed, $parsedPdo);
        }

        $parsed['dsn'] = $dsn;
        return $parsed;
    } // parseDSN


    /**
     * Parse string as DBO DSN string.
     *
     * @param $str
     * @return array
     */
    public static function parseDsnPdo($str) {

        if (substr($str, 0, strlen('mysql:')) == 'mysql:') {
            $str = substr($str, strlen('mysql:'));
        }

        $arr = explode(';', $str);

        $result = array();
        foreach ($arr as $k=>$v) {
            $v = explode('=', $v);
            if (count($v) == 2)
                $result[ $v[0] ] = $v[1];
        }

        if ( isset($result['unix_socket']) ) {
            $result['socket'] = $result['unix_socket'];
            unset($result['unix_socket']);
        }

        if ( isset($result['dbname']) ) {
            $result['path'] = $result['dbname'];
            unset($result['dbname']);
        }

        if ( isset($result['charset']) ) {
            $result['enc'] = $result['charset'];
            unset($result['charset']);
        }

        return $result;
    } // parseDsnPdo

} // DbSimple_Generic class
