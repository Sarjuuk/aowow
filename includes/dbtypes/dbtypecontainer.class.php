<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


abstract class DBTypeContainer
{
    protected array $sets = [];

    public static int        $dbType;
    public static int        $contribute = CONTRIBUTE_ANY;
    public static string     $dataTable;
    public        string|int $id         = 0;               // sooo .. everything is int, except profiler related stuff, whose keys are <realmId>:<subjectGUID>
    public        bool       $error      = true;

    private array $itrStack    = [];
    private int   $resultTotal = 0;

    public function __construct(?array $conditions = [], array $miscData = [], array $targetDBs = ['Aowow'])
    {
        $query    = Type::getClassConst(static::$dbType, 'QUERY_BASE');
        $baseOpts = Type::getClassConst(static::$dbType, 'QUERY_OPTS');
        if (!$query)
            return;

        // we want en empty set to import DBtypes into later on
        if (is_null($conditions))
        {
            $this->error = false;
            return;
        }

        $dbQuery = new DBQuery($targetDBs, $query, $baseOpts, $miscData['extraOpts'] ?? [], $miscData['calcTotal'] ?? false);
        if (!$dbQuery->build($conditions))
            return;

        foreach ($dbQuery->fetch() as $data)
            if (($entry = Type::newEntry(static::$dbType, (array)$data)) && !$entry->error)
                $this->import($entry);

        $this->error = empty($this->sets);

        // store found count if requested
        if (!empty($miscData['calcTotal']))
            $this->resultTotal = $dbQuery->getMatches();
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, DBTypeEntry> id => current template
     */
    public function iterate() : \Generator
    {
        if (!$this->sets)
            return;

        $this->itrStack[] = $this->id;

        // reset on __construct
        $this->reset();

        foreach ($this->sets as $id => $__)
        {
            $this->id = $id;
            yield $id => $this->sets[$id];
        }

        // fforward array pointer to old index
        $this->reset();
        $lastIdx = array_pop($this->itrStack);
        while (($k = key($this->sets)) !== null && $k != $lastIdx)
            next($this->sets);

        $this->id = $k;                                     // if $k is ever null, just eat the exception and report it
    }

    protected function reset() : void
    {
        if (!$this->sets)
            return;

        $this->id = key($this->sets);
    }

    // read-access to sets
    public function getEntry(null|string|int $key = null) : ?DBTypeEntry
    {
        if (is_null($key))
            return $this->sets[$this->id] ?? null;

        if (!isset($this->sets[$key]))
            return null;

        $this->id = $key;
        return $this->sets[$key];
    }

    public function getRandomId() : int
    {
        if (!($qb = Type::getClassConst(static::$dbType, 'QUERY_BASE')))
            return 0;

        if (!preg_match('/SELECT .*? FROM (\?\_[\w_-]+) /i', $qb, $m))
            return 0;

        $ids = DB::Aowow()->selectCol('SELECT `id` FROM %n', $m[1], '%if', User::isInGroup(U_GROUP_EMPLOYEE), 'WHERE (`cuFlags` & %i) = 0 %endif', CUSTOM_EXCLUDE_FOR_LISTVIEW) ?? [0];

        return $ids[rand(0, count($ids))];
    }

    public function getFoundIds() : array
    {
        return array_keys($this->sets);
    }

    public function getMatches() : int
    {
        return $this->resultTotal;
    }

    public function export(int ...$ids) : array
    {
        if (!$ids)
            return $this->sets;

        return array_filter($this->sets, fn($x) => in_array($x, $ids), ARRAY_FILTER_USE_KEY);
    }

    public function import(DBTypeEntry ...$entries) : void
    {
        foreach ($entries as $e)
            if ($e::$dbType == static::$dbType)
                $this->sets[$e->id] = $e;

        $this->reset();
    }


    /********************/
    /* listview helpers */
    /*                  */
    /* move to trait?   */
    /********************/

    public function hasSetFields(?string ...$fields) : int
    {
        $result = 0x0;

        foreach ($this->iterate() as $entry)
        {
            foreach ($fields as $k => $str)
            {
                if (!$str)
                {
                    unset($fields[$k]);
                    continue;
                }

                if ((is_array($entry->$str) && array_filter($entry->$str)) || (!is_array($entry->$str) && $entry->$str))
                {
                    $result |= 1 << $k;
                    unset($fields[$k]);
                }
            }

            if (empty($fields))                             // all set .. return early
            {
                $this->reset();                             // Generators have no __destruct, reset manually, when not doing a full iteration
                return $result;
            }
        }

        return $result;
    }

    public function hasDiffFields(?string ...$fields) : int
    {
        $base   = [];
        $result = 0x0;
        $fields = array_filter($fields);

        foreach ($this->iterate() as $entry)
        {
            foreach ($fields as $k => $str)
            {
                if (!isset($base[$str]))
                {
                    $base[$str] = $entry->$str;
                    continue;
                }

                if ($base[$str] != $entry->$str)
                {
                    $result |= 1 << $k;
                    unset($fields[$k]);
                }
            }

            if (empty($fields))                             // all fields diff .. return early
            {
                $this->reset();                             // Generators have no __destruct, reset manually, when not doing a full iteration
                return $result;
            }
        }

        return $result;
    }

    public function getAllFields(string $prop) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->$prop ?? null;

        return $data;
    }

    /** returns data portion of a listview js object */
    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            if ($row = $entry->getListviewRow($addInfoMask))
                $data[$id] = $row;

        return $data;
    }

    /** returns data of all items in set to extend global js variables for a certain type (e.g. g_items) */
    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        foreach ($this->iterate() as $entry)
            Util::mergeJsGlobals($data, $entry->getJSGlobal($addMask));

        return $data;
    }
}


?>
