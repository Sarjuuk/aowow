<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


abstract class DBTypeList
{
    protected array  $templates = [];
    protected array  $curTpl    = [];
    protected int    $matches   = 0;                        // total matches unaffected by sqlLimit in config

    protected array  $dbNames   = ['Aowow'];                // multiple DBs in profiler
    protected string $queryBase = '';
    protected array  $queryOpts = [];

    private array $itrStack = [];
    private array $prefixes = [];

    public static int        $type;
    public static int        $contribute = CONTRIBUTE_ANY;
    public static string     $dataTable;
    public        string|int $id         = 0;               // sooo .. everything is int, except profiler related stuff, whose keys are <realmId>:<subjectGUID>
    public        bool       $error      = true;

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
    public function __construct(array $conditions = [], array $miscData = [])
    {
        $where   = [];
        $linking = DB::AND;
        $limit   = 0;

        $calcTotal  = false;
        $totalQuery = '';

        if (!$this->queryBase || $conditions === null)
            return;

        if (preg_match('/FROM (?:::)?[\w\_]+( AS)?\s?`?(\w+)`?$/i', $this->queryBase, $match))
            $this->prefixes['base'] = $match[2];
        else
            $this->prefixes['base'] = '';

        if (!empty($miscData['extraOpts']))
            $this->extendQueryOpts($miscData['extraOpts']);

        if (!empty($miscData['calcTotal']))
            $calcTotal = true;

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
                $where[] = $x;

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

        // prepare usage of guids if using multiple realms (which have non-zoro indizes)
        if (key($this->dbNames) != 0)
            $this->queryBase = preg_replace('/\s([^\s]+)\sAS\sARRAY_KEY/i', ' CONCAT("DB_IDX", ":", \1) AS ARRAY_KEY', $this->queryBase);

        // insert additional selected fields
        if ($s = array_column($this->queryOpts, 's'))
            $this->queryBase = str_replace('ARRAY_KEY', 'ARRAY_KEY '.implode('', $s), $this->queryBase);

        // append joins
        if ($j = array_column($this->queryOpts, 'j'))
            foreach ($j as $_)
                $this->queryBase .= is_array($_) ? (empty($_[1]) ? ' JOIN ' : ' LEFT JOIN ').$_[0] : ' JOIN '.$_;

        // append conditions
        if ($where)
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
        if ($calcTotal)
            $totalQuery = $this->queryBase;

        // append ordering
        if ($o = array_filter(array_column($this->queryOpts, 'o')))
            $this->queryBase .= ' ORDER BY '.implode(', ', $o);

        // apply limit
        if ($limit)
            $this->queryBase .= ' LIMIT '.$limit;

        // execute query (finally)
        // this is purely because of multiple realms per server
        foreach ($this->dbNames as $dbIdx => $n)
        {
            try                                             // does not go through the compatibility layer as we need to be able to fetch individual rows here
            {
                $query  = str_replace('DB_IDX', $dbIdx, $this->queryBase);
                $result = DB::{$n}($dbIdx)->query($query, $where);

                if ($calcTotal && $result->getRowCount())
                {
                    // hackfix the inner items query to not contain duplicate column names
                    // yes i know the real solution would be to not have items and item_stats share column names
                    // soon™....
                    if (get_class($this) == ItemList::class)
                        $totalQuery = str_replace([', `is`.*', ', i.`id` AS "id"'], '', $totalQuery);

                    $this->matches += DB::{$n}($dbIdx)->selectCell('SELECT COUNT(*) FROM ('.$totalQuery.') x', $where);
                }

                foreach ($result->getIterator() as $row)
                {
                    // just .. roll with the unparsed, deprecated ARRAY_KEY, hmk?
                    if (isset($this->templates[$row['ARRAY_KEY']]))
                        trigger_error('GUID for List already in use #'.$row['ARRAY_KEY'].'. Additional occurrence omitted!', E_USER_ERROR);
                    else
                        $this->templates[$row['ARRAY_KEY']] = (array)$row;
                }

                $result->free();
            }
            catch (\Exception $e) {}                        // logged via \Dibi\Event in DB::errorLogger
        }

        if (!$this->templates)
            return;

        // push first element for instant use
        $this->reset();

        // all clear
        $this->error = false;
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
            return array_filter(array_map([$this, 'setColPrefix'], $colName)) ?: null;

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

    /**
     * iterate over fetched templates
     *
     * @return array the current template
     */
    public function &iterate() : \Generator
    {
        if (!$this->templates)
            return;

        $this->itrStack[] = $this->id;

        // reset on __construct
        $this->reset();

        foreach ($this->templates as $id => $__)
        {
            $this->id     = $id;
            $this->curTpl = &$this->templates[$id];         // do not use $tpl from each(), as we want to be referenceable

            yield $id => $this->curTpl;

            unset($this->curTpl);                           // kill reference or it will 'bleed' into the next iteration
        }

        // fforward to old index
        $this->reset();
        $oldIdx = array_pop($this->itrStack);
        do
        {
            if (key($this->templates) != $oldIdx)
                continue;

            $this->curTpl = current($this->templates);
            $this->id     = key($this->templates);
            next($this->templates);
            break;
        }
        while (next($this->templates));
    }

    protected function reset() : void
    {
        unset($this->curTpl);                               // kill reference or strange stuff will happen
        if (!$this->templates)
            return;

        $this->curTpl = reset($this->templates);
        $this->id     = key($this->templates);
    }

    // read-access to templates
    public function getEntry(string|int $id) : ?array
    {
        if (isset($this->templates[$id]))
        {
            unset($this->curTpl);                           // kill reference or strange stuff will happen
            $this->curTpl = $this->templates[$id];
            $this->id     = $id;
            return $this->templates[$id];
        }

        return null;
    }

    public function getField(string $field, bool $localized = false, bool $silent = false) : mixed
    {
        if (!$this->curTpl || (!$localized && !isset($this->curTpl[$field])))
            return '';

        if ($localized)
            return Util::localizedString($this->curTpl, $field, $silent);

        $value = $this->curTpl[$field];
        Util::checkNumeric($value);

        return $value;
    }

    public function getAllFields(string $field, bool $localized = false, bool $silent = false) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[$this->id] = $this->getField($field, $localized, $silent);

        return $data;
    }

    public function getRandomId() : int
    {
        if (preg_match('/SELECT .*? FROM (::[\w_-]+) /i', $this->queryBase, $m))
            return DB::Aowow()->selectCell('SELECT `id` FROM %n WHERE (`cuFlags` & %i) = 0  ORDER BY RAND() ASC LIMIT 1', $m[1], User::isInGroup(U_GROUP_EMPLOYEE) ? 0 : CUSTOM_EXCLUDE_FOR_LISTVIEW) ?: 0;

        return 0;
    }

    public function getFoundIDs() : array
    {
        return array_keys($this->templates);
    }

    public function getMatches() : int
    {
        return $this->matches;
    }

    protected function extendQueryOpts(array $extra) : void // needs to be called from __construct
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

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM %n WHERE `id` = %i', static::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public static function makeLink(int $id, int $fmt = Lang::FMT_HTML, string $cssClass = '') : string
    {
        if ($n = static::getName($id))
            return Lang::makeLink(static::$type, $id, $n, $fmt, $cssClass);

        return '';
    }

    /* source More .. keys seen used
         'n':   name [always set]
         't':   type [always set]
        'ti':   typeId [always set]
        'bd':   BossDrop [0; 1] [Creature / GO]
        'dd':   DungeonDifficulty [-2: DungeonHC; -1: DungeonNM; 1: Raid10NM; 2:Raid25NM; 3:Raid10HM; 4: Raid25HM; 99: filler trash] [Creature / GO]
         'q':   cssQuality [Items]
         'z':   zone [set when all happens in here]
         'p':   PvP [pvpSourceId]
         's':   Type::TITLE: side; Type::SPELL: skillId (yeah, double use. Ain't life just grand)
         'c':   category [Spells / Quests]
        'c2':   subCat [Quests]
      'icon':   iconString
    */
    public function getSourceData(int $id = 0) : array { return []; }

    // should return data required to display a listview of any kind
    // this is a rudimentary example, that will not suffice for most Types
    abstract public function getListviewData() : array;

    // should return data to extend global js variables for a certain type (e.g. g_items)
    abstract public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array;

    // NPC, GO, Item, Quest, Spell, Achievement, Profile would require this
    abstract public function renderTooltip() : ?string;
}

?>
