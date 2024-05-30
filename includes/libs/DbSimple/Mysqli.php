<?php
/**
 * DbSimple_Mysql: MySQL database.
 * (C) Dk Lab, http://en.dklab.ru
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Placeholders end blobs are emulated.
 *
 * @author Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
 * @author Konstantin Zhinko, http://forum.dklab.ru/users/KonstantinGinkoTit/
 *
 * @version 2.x $Id: Mysqli.php 247 2008-08-18 21:17:08Z dk $
 */
require_once __DIR__.'/Database.php';


/**
 * Database class for MySQL.
 */
class DbSimple_Mysqli extends DbSimple_Database
{
    var $link;

    private $_lastQuery;

    /**
     * constructor(string $dsn)
     * Connect to MySQL server.
     */
    function __construct($dsn)
    {

        if (!is_callable("mysqli_connect"))
            return $this->_setLastError("-1", "MySQLi extension is not loaded", "mysqli_connect");

        if (!empty($dsn["persist"])) {
            if (version_compare(PHP_VERSION, '5.3') < 0) {
                return $this->_setLastError("-1", "Persistent connections in MySQLi is allowable since PHP 5.3", "mysqli_connect");
            } else {
                $dsn["host"] = "p:".$dsn["host"];
            }
        }

        if ( isset($dsn['socket']) ) {
            // Socket connection
            $this->link = mysqli_connect(
                null                                         // host
                ,empty($dsn['user']) ? 'root' : $dsn['user'] // user
                ,empty($dsn['pass']) ? '' : $dsn['pass']     // password
                ,preg_replace('{^/}s', '', $dsn['path'])     // schema
                ,null                                        // port
                ,$dsn['socket']                              // socket
            );
        } else if (isset($dsn['host']) ) {
            // Host connection
            $this->link = mysqli_connect(
                $dsn['host']
                ,empty($dsn['user']) ? 'root' : $dsn['user']
                ,empty($dsn['pass']) ? '' : $dsn['pass']
                ,preg_replace('{^/}s', '', $dsn['path'])
                ,empty($dsn['port']) ? null : $dsn['port']
            );
        } else {
            return $this->_setDbError('mysqli_connect()');
        }
        $this->_resetLastError();
        if (!$this->link) return $this->_setDbError('mysqli_connect()');

        mysqli_set_charset($this->link, isset($dsn['enc']) ? $dsn['enc'] : 'UTF8');
    }


    protected function _performEscape($s, $isIdent=false)
    {
        if (!$isIdent)
            return "'" . mysqli_real_escape_string($this->link, $s) . "'";
        else
            return "`" . str_replace('`', '``', $s) . "`";
    }


    protected function _performNewBlob($blobid=null)
    {
        return new DbSimple_Mysqli_Blob($this, $blobid);
    }


    protected function _performGetBlobFieldNames($result)
    {
        $allFields = mysqli_fetch_fields($result);
        $blobFields = array();

        if (!empty($allFields))
        {
            foreach ($allFields as $field)
                if (stripos($field["type"], "BLOB") !== false)
                    $blobFields[] = $field["name"];
        }
        return $blobFields;
    }


    protected function _performGetPlaceholderIgnoreRe()
    {
        return '
            "   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
            \'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
            `   (?> [^`]+ | ``)*              `   |   # backticks
            /\* .*?                          \*/      # comments
        ';
    }


    protected function _performTransaction($parameters=null)
    {
        return mysqli_begin_transaction($this->link);
    }


    protected function _performCommit()
    {
        return mysqli_commit($this->link);
    }


    protected function _performRollback()
    {
        return mysqli_rollback($this->link);
    }


    protected function _performTransformQuery(&$queryMain, $how)
    {
        // If we also need to calculate total number of found rows...
        switch ($how)
        {
            // Prepare total calculation (if possible)
            case 'CALC_TOTAL':
                $m = null;
                if (preg_match('/^(\s* SELECT)(.*)/six', $queryMain[0], $m))
                    $queryMain[0] = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
                return true;

            // Perform total calculation.
            case 'GET_TOTAL':
                // Built-in calculation available?
                $queryMain = array('SELECT FOUND_ROWS()');
                return true;
        }

        return false;
    }


    protected function _performQuery($queryMain)
    {
        $this->_lastQuery = $queryMain;
        $this->_expandPlaceholders($queryMain, false);
        mysqli_ping($this->link);
        $result = mysqli_query($this->link, $queryMain[0]);
        if ($result === false)
            return $this->_setDbError($queryMain[0]);

        if ($this->link->warning_count) {
            if ($warn = $this->link->query("SHOW WARNINGS")) {
                while ($warnRow = $warn->fetch_row())
                    if ($warnRow[0] === 'Warning')
                        $this->_setLastError(-$warnRow[1], $warnRow[2], $queryMain[0]);

                $warn->close();
            }
        }

        if (!is_object($result)) {
            if (preg_match('/^\s* INSERT \s+/six', $queryMain[0]))
            {
                // INSERT queries return generated ID.
                return mysqli_insert_id($this->link);
            }
            // Non-SELECT queries return number of affected rows, SELECT - resource.
            return mysqli_affected_rows($this->link);
        }
        return $result;
    }


    protected function _performFetch($result)
    {
        $row = mysqli_fetch_assoc($result);
        if (mysqli_error($this->link)) return $this->_setDbError($this->_lastQuery);
        if ($row === false) return null;
        return $row;
    }


    protected function _setDbError($query)
    {
    	if ($this->link) {
	        return $this->_setLastError(mysqli_errno($this->link), mysqli_error($this->link), $query);
	    } else {
	        return $this->_setLastError(mysqli_connect_errno(), mysqli_connect_error(), $query);
	    }
    }
}


class DbSimple_Mysqli_Blob implements DbSimple_Blob
{
    // MySQL does not support separate BLOB fetching.
    private $blobdata = null;
    private $curSeek = 0;

    public function __construct(&$database, $blobdata=null)
    {
        $this->blobdata = $blobdata;
        $this->curSeek = 0;
    }

    public function read($len)
    {
        $p = $this->curSeek;
        $this->curSeek = min($this->curSeek + $len, strlen($this->blobdata));
        return substr($this->blobdata, $p, $len);
    }

    public function write($data)
    {
        $this->blobdata .= $data;
    }

    public function close()
    {
        return $this->blobdata;
    }

    public function length()
    {
        return strlen($this->blobdata);
    }
}
