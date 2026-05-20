<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


abstract class DBTypeSet
{
    protected array  $sets     = [];
    protected DBType $curEntry;

    public static int        $dbType;
    public static int        $contribute = CONTRIBUTE_ANY;
    public static string     $dataTable;
    public        string|int $id         = 0;               // sooo .. everything is int, except profiler related stuff, whose keys are <realmId>:<subjectGUID>
    public        bool       $error      = true;

    private array $itrStack    = [];
    private int   $resultTotal = 0;

    public function __construct(array $conditions = [], array $miscData = [])
    {
        $query    = Type::getClassConst(static::$dbType, 'QUERY_BASE');
        $baseOpts = Type::getClassConst(static::$dbType, 'QUERY_OPTS');
        if (!$query)
            return;

        $dbQuery = new DBQuery($query, $baseOpts, $miscData['extraOpts'] ?? [], $miscData['calcTotal'] ?? false);
        if (!$dbQuery->build($conditions))
            return;

        foreach ($dbQuery->fetch() as $id => $data)
            $this->sets[$id] = Type::newType(static::$dbType, (array)$data);

        $this->error = empty($this->sets);

        // store found count if requested
        if (!empty($miscData['calcTotal']))
            $this->resultTotal = $dbQuery->getMatches();

        // push first result for use
        $this->reset();
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<int, DBType> id => current template
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
            $this->id       = $id;
            $this->curEntry = &$this->sets[$id];            // do not use $tpl from each(), as we want to be referenceable

            yield $id => $this->curEntry;

            unset($this->curEntry);                         // kill reference or it will 'bleed' into the next iteration
        }

        // fforward to old index
        $this->reset();
        $oldIdx = array_pop($this->itrStack);
        do
        {
            if (key($this->sets) != $oldIdx)
                continue;

            $this->id       = key($this->sets);
            $this->curEntry = current($this->sets);
            next($this->sets);
            break;
        }
        while (next($this->sets));
    }

    protected function reset() : void
    {
        unset($this->curEntry);                               // kill reference or strange stuff will happen
        if (!$this->sets)
            return;

        $this->curEntry = reset($this->sets);
        $this->id     = key($this->sets);
    }

    // read-access to sets
    public function getEntry(string|int $id) : ?DBType
    {
        if (isset($this->sets[$id]))
        {
            unset($this->curEntry);                           // kill reference or strange stuff will happen
            $this->curEntry = $this->sets[$id];
            $this->id     = $id;
            return $this->sets[$id];
        }

        return null;
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

    public function getAllFields(string $prop, bool $localized = false, bool $silent = false) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->$prop; // getField($prop, $localized, $silent); prop should be LocString, so localized/silent should be irrelevant?

        return $data;
    }

    /** returns data portion of a listview js object */
    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            $data[$id] = $entry->getListviewRow();

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
