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
 * @version 2.x $Id: Mysql.php 247 2008-08-18 21:17:08Z dk $
 */
require_once dirname(__FILE__).'/Database.php';


/**
 * Database class for MySQL.
 */
class DbSimple_Mysql extends DbSimple_Database
{
    var $link;

    /**
     * constructor(string $dsn)
     * Connect to MySQL.
     */
    function DbSimple_Mysql($dsn)
    {
        $connect = 'mysql_'.((isset($dsn['persist']) && $dsn['persist'])?'p':'').'connect';
        if (!is_callable($connect))
            return $this->_setLastError("-1", "MySQL extension is not loaded", $connect);
        $ok = $this->link = @call_user_func($connect,
            $dsn['host'] . (empty($dsn['port'])? "" : ":".$dsn['port']),
            empty($dsn['user'])?'':$dsn['user'],
            empty($dsn['pass'])?'':$dsn['pass'],
            true
        );
        $this->_resetLastError();
        if (!$ok)
            if (!$ok) return $this->_setDbError('mysql_connect("' . $str . '", "' . $p['user'] . '")');
        $ok = @mysql_select_db(preg_replace('{^/}s', '', $dsn['path']), $this->link);
        if (!$ok)
            return $this->_setDbError('mysql_select_db()');
        mysql_query('SET NAMES '.(isset($dsn['enc'])?$dsn['enc']:'UTF8'));
    }


    protected function _performEscape($s, $isIdent=false)
    {
        if (!$isIdent)
            return "'" . mysql_real_escape_string($s, $this->link) . "'";
        else
            return "`" . str_replace('`', '``', $s) . "`";
    }


    protected function _performNewBlob($blobid=null)
    {
        return new DbSimple_Mysql_Blob($this, $blobid);
    }


    protected function _performGetBlobFieldNames($result)
    {
        $blobFields = array();
        for ($i=mysql_num_fields($result)-1; $i>=0; $i--)
        {
            $type = mysql_field_type($result, $i);
            if (stripos($type, "BLOB") !== false)
                $blobFields[] = mysql_field_name($result, $i);
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
        return $this->query('BEGIN');
    }


    protected function _performCommit()
    {
        return $this->query('COMMIT');
    }


    protected function _performRollback()
    {
        return $this->query('ROLLBACK');
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
        $result = mysql_query($queryMain[0], $this->link);
        if ($result === false)
            return $this->_setDbError($queryMain[0]);
        if (!is_resource($result)) {
            if (preg_match('/^\s* INSERT \s+/six', $queryMain[0]))
            {
                // INSERT queries return generated ID.
                return mysql_insert_id($this->link);
            }
            // Non-SELECT queries return number of affected rows, SELECT - resource.
            return mysql_affected_rows($this->link);
        }
        return $result;
    }


    protected function _performFetch($result)
    {
        $row = mysql_fetch_assoc($result);
        if (mysql_error()) return $this->_setDbError($this->_lastQuery);
        if ($row === false) return null;
        return $row;
    }


    protected function _setDbError($query)
    {
    	if ($this->link) {
	        return $this->_setLastError(mysql_errno($this->link), mysql_error($this->link), $query);
	    } else {
	        return $this->_setLastError(mysql_errno(), mysql_error(), $query);
	    }
    }
}


class DbSimple_Mysql_Blob implements DbSimple_Blob
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
?>