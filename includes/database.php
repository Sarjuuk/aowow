<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');



class DibiConnection extends \Dibi\Connection
{
    /**
     * Executes SQL query and fetch result - shortcut for query() & fetch().
     */
    public function selectRow(mixed ...$args) : ?array
    {
        try
        {
            return (array)$this->query($args)->fetch();
        }
        catch (\Exception $e) {}                            // logged via \Dibi\Event in errorLogger

        return null;
    }

    /**
     * Executes SQL query and fetch first column - shortcut for query() & fetchSingle().
     */
    public function selectCell(mixed ...$args) : mixed
    {
        try
        {
            $x = $this->query($args)->fetchSingle();
            return is_array($x) ? array_pop($x) : $x;
        }
        catch (\Exception $e) {}                            // logged via \Dibi\Event in errorLogger

        return null;
    }

    /**
     * Executes SQL query and fetch first column - shortcut for query() & fetchSingle().
     */
    public function selectCol(mixed ...$args) : ?array
    {
        try
        {
            $result = $this->query($args);
            if (strpos($args[0], 'ARRAY_KEY2'))
                $data = $result->fetchAssoc('ARRAY_KEY|ARRAY_KEY2');
            else if (strpos($args[0], 'ARRAY_KEY'))
                $data = $result->fetchAssoc('ARRAY_KEY');
            else
                $data = $result->fetchAll();

            $result->free();

            // convert Dibi/Row to array
            // remove array keys from result set and set result to next cell
            array_walk_recursive($data, function(&$row) {
                if (get_debug_type($row) == 'Dibi\Row')
                    $row = (array)$row;

                unset($row['ARRAY_KEY'], $row['ARRAY_KEY2']);
                $row = array_pop($row);
            });
            return $data;
        }
        catch (\Exception $e) {}                            // logged via \Dibi\Event in errorLogger

        return null;
    }

    /**
     * Executes SQL query and fetch ass associative array
     */
    public function selectAssoc(mixed ...$args) : ?array
    {
        try
        {
            $result = $this->query($args);
            if (strpos($args[0], 'ARRAY_KEY2'))
                $data = $result->fetchAssoc('ARRAY_KEY|ARRAY_KEY2');
            else if (strpos($args[0], 'ARRAY_KEY'))
                $data = $result->fetchAssoc('ARRAY_KEY');
            else
                $data = $result->fetchAll();

            $result->free();

            // convert Dibi/Row to array
            // remove array keys from result set
            array_walk_recursive($data, function(&$row) {
                if (get_debug_type($row) == 'Dibi\Row')
                    $row = (array)$row;

                unset($row['ARRAY_KEY'], $row['ARRAY_KEY2']);
            });
            return $data;
        }
        catch (\Exception $e) {}                            // logged via \Dibi\Event in errorLogger

        return null;
    }

    /**
     * Executes SQL query and fetch pairs - shortcut for query() & fetchPairs().
     */
    public function selectPairs(mixed ...$args): ?array
    {
        try
        {
            return $this->query($args)->fetchPairs();
        }
        catch (\Exception $e) {}                            // logged via \Dibi\Event in errorLogger

        return null;
    }

    /**
     * Executes SQL query and returns new insertId or num affected rows.
     */
    public function qry(mixed ...$args) : ?int
    {
        try
        {
            $this->nativeQuery($this->translate(...$args));
            if (strstr($args[0], 'INSERT'))
                return $this->getDriver()?->getResource()?->insert_id;
            else
                return $this->getAffectedRows();
        }
        catch (\Exception $e) {}                            // logged via \Dibi\Event in errorLogger

        return null;
    }
}

class DB
{
    public const /* string */ AND = '%and';
    public const /* string */ OR  = '%or';

    private static array $interfaceCache = [];
    private static array $interfaceTimes = [];
    private static array $optionsCache   = [];
    private static array $logs           = [];

    public static function connect(int $idx) : bool
    {
        if (self::isConnected($idx))
        {
            self::$interfaceCache[$idx]->disconnect();
            self::$interfaceCache[$idx] = null;
        }

        $config = self::$optionsCache[$idx] + array(
            'charset'     => 'utf8mb4',                     // executes: SET NAMES $charset
            'substitutes' => array(
                '' => self::$optionsCache[$idx]['prefix']   // old: ?_ - new: ::
            )
        );

        // alias old DBSimple format
        if (empty($config['database']) && !empty($config['db']))
            $config['database'] = &$config['db'];

        try
        {
            $interface = new DibiConnection($config);
        }
        catch (\Exception $e)
        {
            return false;
        }

        // disable STRICT_TRANS_TABLES and STRICT_ALL_TABLES. It prevents usage of implicit default values.
        // disable ONLY_FULL_GROUP_BY (Allows for non-aggregated selects in a group-by query)
        $extraModes = ['STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'ONLY_FULL_GROUP_BY', 'NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'ERROR_FOR_DIVISION_BY_ZERO'];
        $oldModes   = explode(',', $interface->fetchSingle('SELECT @@sql_mode'));
        $newModes   = array_diff($oldModes, $extraModes);
        if ($oldModes != $newModes)
            $interface->query("SET SESSION sql_mode = %s", implode(',', $newModes));

        $interface->onEvent[] = self::errorLogger(...);
        $interface->onEvent[] = self::profiler(...);

        self::$interfaceCache[$idx] = &$interface;
        return true;
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

    public static function errorLogger(\Dibi\Event $evt/* string $message, array $data */) : void
    {
        if (!error_reporting())
            return;

        if (!$evt->result instanceof \Exception)
            return;

        $msg = <<<MSG
        DB ERROR
          code:    {$evt->result->getCode()}
          message: {$evt->result->getMessage()}
          query:   {$evt->sql}
          context: {$evt->source[0]} line {$evt->source[1]}
        MSG;

        if (CLI)
            fwrite(STDERR, $msg);
        else if (User::isInGroup(U_GROUP_ADMIN) && Cfg::get('DEBUG') >= LOG_LEVEL_INFO)
            echo PHP_EOL . '<pre>' . $msg . '</pre>' . PHP_EOL;

        trigger_error($evt->result->getMessage(), E_USER_ERROR);
    }

    public static function profiler(\Dibi\Event $evt/* mixed $self, string $query, mixed $trace */) : void
    {
        $query = \dibi::$sql;
        $time  = \dibi::$elapsedTime;

        self::$logs[] = [str_replace("\n", ' ', $query), $time];
    }

    public static function getProfiles() : string
    {
        $out = '<pre><table style="font-size:12;"><tr><th></th><th>Time</th><th>Query</th></tr>';
        foreach (self::$logs as $i => [$l, $t])
        {
            // t in seconds
            $c = 'inherit';
            if ($t > (100 / 1000))
                $c = '#FFA0A0';
            else if ($t > (20 / 1000))
                $c = '#FFFFA0';

            $out .= '<tr><td>'.++$i.'.</td><td style="background-color:'.$c.';">'.round($t * 1000, 2).'ms</td><td>'.$l.'</td></tr>';
        }

        $out .= '<tr><td><b>∑t:</b></td><td colspan="2"><b>' . round(array_sum(array_column(self::$logs, 1)) * 1000, 2) . 'ms</b></td></tr>';

        return Util::jsEscape($out).'</table></pre>';
    }

    public static function load(int $idx, array $config, int $keepAlive = 1 * HOUR) : void
    {
        self::$optionsCache[$idx] = $config;
        if (self::connect($idx))
            self::$interfaceTimes[$idx] = [time() + $keepAlive, $keepAlive];
    }

    public static function isConnected(int $idx) : bool
    {
        return isset(self::$interfaceCache[$idx]) && self::$interfaceCache[$idx]->isConnected();
    }

    public static function isConnectable(int $idx) : bool
    {
        return isset(self::$optionsCache[$idx]);
    }

    /**
     * @static
     * @return DibiConnection
     */
    public static function Characters(int $realmId) : ?DibiConnection
    {
        if (!isset(self::$optionsCache[DB_CHARACTERS.$realmId]))
            die('Connection info not found for live database of realm #'.$realmId.'. Aborted.');

        return self::getDB(DB_CHARACTERS.$realmId);
    }

    /**
     * @static
     * @return DibiConnection
     */
    public static function Auth() : ?DibiConnection
    {
        return self::getDB(DB_AUTH);
    }

    /**
     * @static
     * @return DibiConnection
     */
    public static function World() : ?DibiConnection
    {
        return self::getDB(DB_WORLD);
    }

    /**
     * @static
     * @return DibiConnection
     */
    public static function Aowow() : ?DibiConnection
    {
        return self::getDB(DB_AOWOW);
    }

    private static function getDB(int $idx) : ?DibiConnection
    {
        if (self::$interfaceTimes[$idx][0] < time())
        {
            self::$interfaceCache[$idx]->disconnect();
            if (!self::connect($idx))
                return null;

            self::$interfaceTimes[$idx][0] = time() + self::$interfaceTimes[$idx][1];
        }

        return self::$interfaceCache[$idx];
    }
}

?>
