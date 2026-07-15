<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


class DBQuery
{
    private array  $prefixes    = [];
    private bool   $calcTotal   = false;
    private string $totalQuery  = '';
    private array  $where       = [];
    private int    $resultTotal = 0;

    public function __construct(
        private array  $queryDBs,
        private string $queryBase,
        private array  $queryOpts,
                array  $extraOpts = [],
                bool   $calcTotal = false
    )
    {
        $this->calcTotal = $calcTotal;

        if (!$extraOpts)
            $this->extendQueryOpts($extraOpts);
    }

    /*
     *   condition as array [expression, value, operator]
     *       expression:    str   - must match fieldname;
     *                      int   - 1: select everything; 0: select nothing
     *                      array - another condition array
     *       value:         str   - operator defaults to: = <val>
     *                      num   - operator defaults to: = <val>
     *                      array - operator defaults to: IN (<val>)
     *                      null  - operator defaults to: IS [NULL]
     *       operator:      modifies/overrides default
     *                      ! - negated default value (NOT LIKE; <>; NOT IN)
     *                      MATCH - creates fulltext search ('value' must be array; column must have fulltext index)
     *                      LIKE / NOT LIKE - partial string matching ('value' must be string (*d'uh*))
     *   condition as str
     *       defines linking (AND || OR)
     *   condition as int
     *       defines LIMIT
     *
     *   example:
     *       array(
     *           ['id', 45],
     *           ['name', 'test%', '!'],
     *           [
     *               DB::AND,
     *               ['flags', 0xFF, '&'],
     *               ['flags2', 0xF, '&'],
     *           ]
     *           [['mask', 0x3, '&'], 0],
     *           ['nameField', ['+contains*', '-excludes'], 'MATCH],
     *           ['joinedTbl.field', NULL]                   // NULL must be explicitly specified "['joinedTbl.field']" would be skipped as erroneous definition (only really usefull when left-joining)
     *           DB::OR,
     *           5
     *       )
     *   results in
     *       WHERE ((`id` = 45) OR (`name` NOT LIKE "test%") OR ((`flags` & 255) AND (`flags2` & 15)) OR ((`mask` & 3) = 0)) OR (MATCH(`nameField`) AGAINST("+contains* -excludes" IN BOOLEAN MODE)) OR (`joinedTbl`.`field` IS NULL) LIMIT 5
     */

    public function build(array $conditions = []) : bool
    {
        if (!$this->queryBase)
            return false;

        $linking = DB::AND;
        $limit   = 0;

        if (preg_match('/FROM (?:::)?[\w\_]+( AS)?\s?`?(\w+)`?$/i', $this->queryBase, $match))
            $this->prefixes['base'] = $match[2];
        else
            $this->prefixes['base'] = '';

        foreach ($conditions as $i => $c)
        {
            switch (getType($c))
            {
                case 'array':
                    break;
                case 'string':
                case 'integer':
                    if (is_numeric($c))
                        $limit = max(0, (int)$c);
                    else if ($c === DB::AND)
                        $linking = DB::AND;
                    else if ($c === DB::OR)
                        $linking = DB::OR;
                default:
                    unset($conditions[$i]);
            }
        }

        foreach ($conditions as $c)
            if ($x = $this->resolveCondition($c, $linking))
                $this->where[] = $x;

        // optional query parts may require other optional parts to work
        foreach ($this->prefixes as $pre)
            if (isset($this->queryOpts[$pre][0]))
                foreach ($this->queryOpts[$pre][0] as $req)
                    if (!in_array($req, $this->prefixes))
                        $this->prefixes[] = $req;

        // remove optional query parts, that are not required
        foreach ($this->queryOpts as $k => $arr)
            if (!in_array($k, $this->prefixes))
                unset($this->queryOpts[$k]);

        // insert additional selected fields
        if ($s = array_column($this->queryOpts, 's'))
            $this->queryBase = str_replace('ARRAY_KEY', 'ARRAY_KEY '.implode('', $s), $this->queryBase);

        // append joins
        if ($j = array_column($this->queryOpts, 'j'))
            foreach ($j as $_)
                $this->queryBase .= is_array($_) ? (empty($_[1]) ? ' JOIN ' : ' LEFT JOIN ').$_[0] : ' JOIN '.$_;

        // append conditions
        if ($this->where)
            $this->queryBase .= ' WHERE '.$linking;

        // append grouping
        if ($g = array_filter(array_column($this->queryOpts, 'g')))
            $this->queryBase .= ' GROUP BY '.implode(', ', $g);

        // append post filtering
        if ($h = array_filter(array_column($this->queryOpts, 'h')))
            $this->queryBase .= ' HAVING '.implode(' AND ', $h);

        // fill in locale
        $this->queryBase = str_replace(['DB_LOC_I', 'DB_LOC_S'], [Lang::getLocale()->value, '"'.Lang::getLocale()->json().'"'], $this->queryBase);

        // without applied LIMIT and ORDER
        if ($this->calcTotal)
            $this->totalQuery = $this->queryBase;

        // append ordering
        if ($o = array_filter(array_column($this->queryOpts, 'o')))
            $this->queryBase .= ' ORDER BY '.implode(', ', $o);

        // apply limit
        if ($limit)
            $this->queryBase .= ' LIMIT '.$limit;

        return true;
    }

    /**
     * @return \Generator<int, array> id => DBTypeEntry data
     */
    public function fetch() : \Generator
    {
        if (!$this->queryBase)
            return;

        // this is purely because of multiple realms per server
        foreach ($this->queryDBs as $dbIdx => $n)
        {
            try                                             // does not go through the compatibility layer as we need to be able to fetch individual rows here
            {
                $query  = str_replace('DB_IDX', $dbIdx, $this->queryBase);
                $result = DB::{$n}($dbIdx)->query($query, $this->where);

                if ($this->calcTotal && $result->getRowCount())
                {
                    // hackfix the inner items query to not contain duplicate column names
                    // yes i know the real solution would be to not have items and item_stats share column names
                    // soon™....
                    if (strpos($this->totalQuery, 'FROM ::items'))
                        $this->totalQuery = str_replace([', `is`.*', ', i.`id` AS "id"'], '', $this->totalQuery);

                    $this->resultTotal += DB::{$n}($dbIdx)->selectCell('SELECT COUNT(*) FROM ('.$this->totalQuery.') x', $this->where);
                }

                // just .. roll with the unparsed, deprecated ARRAY_KEY, hmk?
                foreach ($result->getIterator() as $row)
                    yield $row['ARRAY_KEY'] => (array)$row;

                $result->free();
            }
            catch (\Exception $e)
            {
                // logged via \Dibi\Event in DB::errorLogger
                return;
            }
        }
    }

    public function getMatches() : int
    {
        return $this->resultTotal;
    }

    private function resolveCondition(array $c, string $supLink) : ?array
    {
        if (!$c)
            return null;

        // i am recursive subcondition
        if ($subLink = array_find($c, fn($x) => $x === DB::AND || $x === DB::OR))
        {
            $sql = [];

            foreach ($c as $foo)
                if (is_array($foo))
                    if ($x = $this->resolveCondition($foo, $supLink))
                        $sql[] = $x;

            return $sql ? [$subLink, $sql] : null;
        }

        [$expOrField, $value, $op] = array_pad($c, 3, null);

        if (is_numeric($expOrField))
            return [$expOrField ? 1 : 0];                   // [1] / [0]
        if (!$expOrField)                                   // '', null, []
            return null;

        $literal = false;

        if (is_array($expOrField) && $op != 'MATCH')
            $field = $this->resolveCondition($expOrField, $supLink);
        else
        {
            // basic formulas ex: [((minGold + maxGold) / 2), 0, '>']
            if (is_string($expOrField) && preg_match('/^\([\s\+\-\*\/\w\(\)\.]+\)$/i', strtr($expOrField, ['`' => '', '´' => '', '--' => ''])))
            {
                $field   = preg_replace_callback('/[\w\]*\.?[\w]+/i', $this->setColPrefix(...), $expOrField);
                $literal = true;
            }
            else
                $field = $this->setColPrefix($expOrField);

            if (!$field)
                return null;
        }

        $neg  = $op === '!';
        $expr = match (gettype($value))
        {
            'integer' => ($neg ? '<>'     : ($op ?: '=')) . ' %i',
            'double'  => ($neg ? '<>'     : ($op ?: '=')) . ' %f',
            'string'  => ($neg ? '<>'     : ($op ?: '=')) . ' %s',
            'NULL'    => ($neg ? 'IS NOT' : 'IS')         . ' %sN',
            'array'   => ($neg ? 'NOT IN' : 'IN')         . ' %in',
            default   => null
        };

        if (!$expr)
            return null;

        if ($op == 'MATCH' && gettype($value) == 'array')
            return ['MATCH(%n)', $field, 'AGAINST(%s IN BOOLEAN MODE)', DB::Aowow()->translate($value)];
        if (($op == 'LIKE' || $op == 'NOT LIKE') && gettype($value) == 'string')
            return ['%n', $field, $op, '%~like~', $value];
        if (is_array($field))                               // $field is expression: [[flags, 0x4, '&'], 0] -> (`flags` & 4) = 0
            return [...$field, $expr, $value];

        return [$literal ? '%SQL' : '%n', $field, $expr, $value];
    }

    private function setColPrefix(mixed $colName) : null|string|array
    {
        if (is_array($colName))
            return array_filter(array_map($this->setColPrefix(...), $colName)) ?: null;

        // numeric allows for formulas e.g. (1 < 3)
        if (Util::checkNumeric($colName))
            return $colName;

        // skip condition if fieldName contains illegal chars
        if (preg_match('/[^\d\w\.\_]/i', $colName))
            return null;

        [$prefix, $col, $err] = array_pad(explode('.', $colName), 3, null);

        if ($err)                                           // more than one period
            return null;
        if (!$col)                                          // prefix not set, so everything is shifted to the left :/
            return $this->prefixes['base'].'.'.$prefix;

        if (!in_array($prefix, $this->prefixes))
        {
            // choose table to join or return null if prefix does not exist
            if (!in_array($prefix, array_keys($this->queryOpts)))
                return null;

            $this->prefixes[] = $prefix;
        }

        return $prefix.'.'.$col;
    }

    private function extendQueryOpts(array $extra) : void
    {
        foreach ($extra as $tbl => $sets)
        {
            foreach ($sets as $module => $value)
            {
                if (!$value || !is_array($value))
                    continue;

                switch ($module)
                {
                    // additional (str)
                    case 'g':                               // group by
                    case 's':                               // select
                        if (!empty($this->queryOpts[$tbl][$module]))
                            $this->queryOpts[$tbl][$module] .= implode(' ', $value);
                        else
                            $this->queryOpts[$tbl][$module] = implode(' ', $value);

                        break;
                    case 'h':                               // having
                        if (!empty($this->queryOpts[$tbl][$module]))
                            $this->queryOpts[$tbl][$module] .= implode(' AND ', $value);
                        else
                            $this->queryOpts[$tbl][$module] = implode(' AND ', $value);

                        break;
                    // additional (arr)
                    case 'j':                               // join
                        if (!empty($this->queryOpts[$tbl][$module]) && is_array($this->queryOpts[$tbl][$module]))
                            $this->queryOpts[$tbl][$module][0][] = $value;
                        else
                            $this->queryOpts[$tbl][$module] = $value;

                        break;
                    // replacement (str)
                    case 'l':                               // limit
                    case 'o':                               // order by
                        $this->queryOpts[$tbl][$module] = $value[0];
                        break;
                }
            }
        }
    }
}

?>
