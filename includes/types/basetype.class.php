<?php

if(!defined('AOWOW_REVISION'))
    die("illegal access");


abstract class BaseType
{
    public    $id        = 0;
    public    $error     = true;

    protected $templates = [];
    protected $curTpl    = [];                              // lets iterate!
    protected $filter    = null;
    protected $matches   = null;                            // total matches unaffected by sqlLimit in config

    protected $queryBase = '';
    protected $queryOpts = [];

    /*
    *   condition as array [expression, value, operator]
    *       expression:    str   - must match fieldname;
    *                      int   - 1: select everything; 0: select nothing
    *                      array - another condition array
    *       value:         str   - operator defaults to: LIKE %<val>%
    *                      int   - operator defaults to: = <val>
    *                      array - operator defaults to: IN (<val>)
    *                      null  - operator defaults to: IS [NULL]
    *       operator:      modifies/overrides default
    *                      ! - negated default value (NOT LIKE; <>; NOT IN)
    *   condition as str
    *       defines linking (AND || OR)
    *   condition as int
    *       defines LIMIT
    *
    *   example:
    *       array(
    *           ['id', 45],
    *           ['name', 'test', '!'],
    *           [
    *               'AND',
    *               ['flags', 0xFF, '&'],
    *               ['flags2', 0xF, '&'],
    *           ]
    *           [['mask', 0x3, '&'], 0],
    *           ['joinedTbl.field', NULL]                   // NULL must be explicitly specified "['joinedTbl.field']" would be skipped as erronous definition (only really usefull when left-joining)
    *           'OR',
    *           5
    *       )
    *   results in
    *       WHERE ((`id` = 45) OR (`name` NOT LIKE "%test%") OR ((`flags` & 255) AND (`flags2` & 15)) OR ((`mask` & 3) = 0)) OR (`joinedTbl`.`field` IS NULL) LIMIT 5
    */
    public function __construct($conditions = [], $applyFilter = false)
    {
        $where     = [];
        $linking   = ' AND ';
        $limit     = SQL_LIMIT_DEFAULT;
        $className = get_class($this);

        if (!$this->queryBase || $conditions === null)
            return;

        // may be called without filtering
        if ($applyFilter && class_exists($className.'Filter'))
        {
            $fiName = $className.'Filter';
            $this->filter = new $fiName($this);
        }

        $prefixes = [];
        if (preg_match('/FROM \??[\w\_]+( AS)?\s?`?(\w+)`?$/i', $this->queryBase, $match))
            $prefixes['base'] = $match[2];
        else
            $prefixes['base'] = '';

        // add conditions from filter
        // to consider - conditions from filters are already escaped and contain sql-wildcards reescaping those sucks
        $conditions[] = $this->filter ? $this->filter->getConditions() : null;

        $resolveCondition = function ($c, $supLink) use (&$resolveCondition, &$prefixes)
        {
            $subLink = '';

            if (!$c)
                return null;

            foreach ($c as $foo)
            {
                if ($foo === 'AND')
                    $subLink = ' AND ';
                else if ($foo === 'OR')                     // nessi-bug: if (0 == 'OR') was true once... w/e
                    $subLink = ' OR ';
            }

            // need to manually set link for subgroups to be recognized as condition set
            if ($subLink)
            {
                $sql = [];

                foreach ($c as $foo)
                    if (is_array($foo))
                        if ($x = $resolveCondition($foo, $supLink))
                            $sql[] = $x;

                return '('.implode($subLink, $sql).')';
            }
            else
            {
                if ($c[0] == '1')
                    return '1';
                else if ($c[0] == '0')
                    return '(0)';                           // trick if ($x = 0) into true...
                else if (is_array($c[0]) && isset($c[1]))
                    $field = $resolveCondition($c[0], $supLink);
                else if ($c[0])
                {
                    $setPrefix = function($f) use(&$prefixes)
                    {
                        if (is_array($f))
                            $f = $f[0];

                        if (is_numeric($f))
                            return $f;

                        $f = explode('.', Util::sqlEscape($f));

                        switch (count($f))
                        {
                            case 2:
                                if (!in_array($f[0], $prefixes))
                                {
                                    // choose table to join or return null if prefix not existant
                                    if (!in_array($f[0], array_keys($this->queryOpts)))
                                        return null;

                                    $prefixes[] = $f[0];
                                }

                                return '`'.$f[0].'`.`'.$f[1].'`';
                            case 1:
                                return '`'.$prefixes['base'].'`.`'.$f[0].'`';
                            default:
                                return null;
                        }
                    };

                    // basic formulas
                    if (preg_match('/^\([\s\+\-\*\/\w\(\)\.]+\)$/i', strtr($c[0], ['`' => '', '´' => '', '--' => ''])))
                        $field = preg_replace_callback('/[\w\]*\.?[\w]+/i', $setPrefix, $c[0]);
                    else
                        $field = $setPrefix($c[0]);

                    if (!$field)
                        return null;
                }
                else
                    return null;

                if (is_array($c[1]))
                {
                    $val = implode(',', Util::sqlEscape($c[1], true));
                    if ($val === '')
                        return null;

                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT IN' : 'IN';
                    $val = '('.$val.')';
                }
                else if (Util::checkNumeric($c[1]))
                {
                    $op  = (isset($c[2]) && $c[2] == '!') ? '<>' : '=';
                    $val = $c[1];
                }
                else if (is_string($c[1]))
                {
                    $val = Util::sqlEscape($c[1], true);

                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT LIKE' : 'LIKE';
                    $val = $val === '' ? '""' : '"%'.$val.'%"';
                }
                else if (count($c) > 1 && $c[1] === null)   // specifficly check for NULL
                {
                    $op  = (isset($c[2]) && $c[2] == '!') ? 'IS NOT' : 'IS';
                    $val = 'NULL';
                }
                else                                        // null for example
                    return null;

                if (isset($c[2]) && $c[2] != '!')
                    $op = $c[2];

                return '('.$field.' '.$op.' '.$val.')';
            }
        };

        foreach ($conditions as $i => $c)
        {
            switch(getType($c))
            {
                case 'array':
                    break;
                case 'string':
                case 'integer':
                    if (is_string($c))
                        $linking = $c == 'AND' ? ' AND ' : ' OR ';
                    else
                        $limit = $c > 0 ? $c : 0;
                default:
                    unset($conditions[$i]);
            }
        }

        foreach ($conditions as $c)
            if ($x = $resolveCondition($c, $linking))
                $where[] = $x;

        // optional query parts may require other optional parts to work
        foreach ($prefixes as $pre)
            if (isset($this->queryOpts[$pre][0]))
                foreach ($this->queryOpts[$pre][0] as $req)
                    if (!in_array($req, $prefixes))
                        $prefixes[] = $req;

        // remove optional query parts, that are not required
        foreach ($this->queryOpts as $k => $arr)
            if (!in_array($k, $prefixes))
                unset($this->queryOpts[$k]);

        // insert additional selected fields
        if ($s = array_column($this->queryOpts, 's'))
            $this->queryBase = str_replace(' FROM', implode('', $s).' FROM', $this->queryBase);

        // append joins
        if ($j = array_column($this->queryOpts, 'j'))
            foreach ($j as $_)
                $this->queryBase .= is_array($_) ? (empty($_[1]) ? ' JOIN ' : ' LEFT JOIN ').$_[0] : ' JOIN '.$_;

        // append conditions
        if ($where)
            $this->queryBase .= ' WHERE ('.implode($linking, $where).')';

        // append grouping
        if ($g = array_column($this->queryOpts, 'g'))
            $this->queryBase .= ' GROUP BY '.implode(', ', $g);

        // append post filtering
        if ($h = array_column($this->queryOpts, 'h'))
            $this->queryBase .= ' HAVING '.implode(' AND ', $h);

        // append ordering
        if ($o = array_column($this->queryOpts, 'o'))
            $this->queryBase .= ' ORDER BY '.implode(', ', $o);

        // apply limit
        if ($limit)
            $this->queryBase .= ' LIMIT '.$limit;

        // execure query (finally)
        $rows = DB::Aowow()->SelectPage($this->matches, $this->queryBase);
        if (!$rows)
            return;

        // assign query results to template
        foreach ($rows as $k => $tpl)
            $this->templates[$k] = $tpl;

        // push first element for instant use
        $this->reset();

        // all clear
        $this->error = false;
    }

    public function &iterate()
    {
        // reset on __construct
        $this->reset();

        while (list($id, $_) = each($this->templates))
        {
            $this->id     = $id;
            $this->curTpl = &$this->templates[$id];         // do not use $tpl from each(), as we want to be referenceable

            yield $id => $this->curTpl;

            unset($this->curTpl);                           // kill reference or it will 'bleed' into the next iteration
        }

        // reset on __destruct .. Generator, Y U NO HAVE __destruct ?!
        $this->reset();
    }

    protected function reset()
    {
        unset($this->curTpl);                               // kill reference or strange stuff will happen
        $this->curTpl = reset($this->templates);
        $this->id     = key($this->templates);
    }

    // read-access to templates
    public function getEntry($id)
    {
        if (isset($this->templates[$id]))
            return $this->templates[$id];

        return null;
    }

    public function getField($field, $localized = false)
    {
        if (!$this->curTpl || (!$localized && !isset($this->curTpl[$field])))
            return '';

        if ($localized)
            return Util::localizedString($this->curTpl, $field);

        $value = $this->curTpl[$field];
        if (Util::checkNumeric($value))
        {
            $intVal   = intVal($value);
            $floatVal = floatVal($value);
            return $intVal == $floatVal ? $intVal : $floatVal;
        }
        else
            return $value;
    }

    public function getRandomId()
    {
        // its not optimal, so if anyone has an alternative idea..
        $pattern = '/SELECT .* (-?`?[\w_]*\`?.?`?(id|entry)`?) AS ARRAY_KEY,?.* FROM (\?[\w_-]+) (`?\w*`?)/i';
        $replace = 'SELECT $1 FROM $3 $4 WHERE (cuFlags & '.CUSTOM_EXCLUDE_FOR_LISTVIEW.') = 0 ORDER BY RAND() ASC LIMIT 1';
        $query   = preg_replace($pattern, $replace, $this->queryBase);

        return DB::Aowow()->selectCell($query);
    }

    public function getFoundIDs()
    {
        return array_keys($this->templates);
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function filterGetForm($key = null, $raw = false)
    {
        $form = [];

        if (!$this->filter)
            return $form;

        foreach ($this->filter->getForm() as $name => $data)
        {
            if (!$data || ($key && $name != $key))
                continue;

            switch ($name)
            {
                case 'setCriteria':
                    $form[$name] = $raw ? $data : 'fi_setCriteria('.$data['cr'].', '.$data['crs'].', '.$data['crv'].');';
                    break;
                case 'extraCols':
                    $form[$name] = $raw ? $data : 'fi_extraCols = '.json_encode(array_unique($data), JSON_NUMERIC_CHECK).';';
                    break;
                case 'setWeights':
                    $form[$name] = $raw ? $data : 'fi_setWeights('.json_encode($data, JSON_NUMERIC_CHECK).', 0, 1, 1);';
                    break;
                case 'form':
                    if ($key == $name)                      // only if explicitely specifies
                        $form[$name] = $data;
                    break;
                default:
                    break;
            }
        }

        return $key ? (empty($form[$key]) ? [] : $form[$key]) : $form;
    }

    public function filterGetError()
    {
        if ($this->filter)
            return $this->filter->error;
        else
            return false;
    }

    // should return data required to display a listview of any kind
    // this is a rudimentary example, that will not suffice for most Types
    abstract public function getListviewData();

    // should return data to extend global js variables for a certain type (e.g. g_items)
    abstract public function addGlobalsToJScript(&$smarty, $addMask = GLOBALINFO_ANY);

    // NPC, GO, Item, Quest, Spell, Achievement, Profile would require this
    abstract public function renderTooltip();
}

trait listviewHelper
{
    public function hasSetFields($fields)
    {
        if (!is_array($fields))
            return 0x0;

        $result = 0x0;

        foreach ($this->iterate() as $__)
        {
            foreach ($fields as $k => $str)
            {
                if ($this->getField($str))
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

    public function hasDiffFields($fields)
    {
        if (!is_array($fields))
            return 0x0;

        $base   = [];
        $result = 0x0;

        foreach ($fields as $k => $str)
            $base[$str] = $this->getField($str);

        foreach ($this->iterate() as $__)
        {
            foreach ($fields as $k => $str)
            {
                if ($base[$str] != $this->getField($str))
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

    public function hasAnySource()
    {
        if (!isset($this->sources))
            return false;

        foreach ($this->sources as $src)
        {
            if (!is_array($src))
                continue;

            if (!empty($src))
                return true;
        }

        return false;
    }

}

trait spawnHelper
{
    private static $spawnQuery = " SELECT a.guid AS ARRAY_KEY, map, position_x, position_y, spawnMask, phaseMask, spawntimesecs, eventEntry, pool_entry AS pool FROM ?# a LEFT JOIN ?# b ON a.guid = b.guid LEFT JOIN ?# c ON a.guid = c.guid WHERE id = ?d";

    private function fetch()
    {
        if (!$this->id)
            return false;

        switch (get_class($this))
        {
            case 'CreatureList':
                return DB::Aowow()->select(self::$spawnQuery, 'creature',   'game_event_creature',   'pool_creature',   $this->id);
            case 'GameObjectList':
                return DB::Aowow()->select(self::$spawnQuery, 'gameobject', 'game_event_gameobject', 'pool_gameobject', $this->id);
            default:
                return false;
        }
    }

    /*
        todo (med): implement this alpha-map-check-virtual-map-transform-wahey!
        note: map in tooltips is activated by either '#map' as anchor (will automatic open mapviewer, when clicking link) in the href or as parameterless rel-parameter e.g. rel="map" in the anchor
    */
    public function getSpawns($spawnInfo)
    {
        // SPAWNINFO_SHORT: true => only the most populated area and only coordinates
        $data = [];

        // $raw = $this->fetch();
        // if (!$raw)
            // return [];

        /*
        SPAWNINFO_FULL:
            $data = array(
                areaId => array(
                    floorNo => array (
                        posX      =>
                        posY      =>
                        respawn   =>
                        phaseMask =>
                        spawnMask =>
                        eventId   =>
                        poolId    =>
                    )
                )
            )

        SPAWNINFO_SHORT: [zoneId, [[x1, y1], [x2, y2], ..]] // only the most populated zone

        SPAWNINFO_ZONES: [zoneId1, zoneId2, ..]             // only zones
        */

        return $data;
    }
}

/*
    roight!
        just noticed, that the filters on pages originally pointed to ?filter=<pageName>
        wich probably checked for correctness of inputs and redirected the correct values as a get-request
        ..
        well, as it is now, its working .. and you never change a running system ..
*/

abstract class Filter
{
    private static  $pattern   = "/[^\p{L}0-9\s_\-\'\.\?\*]/ui";// delete any char not in unicode, number, space, underscore, hyphen, single quote, dot or common wildcard
    private static  $wCards    = ['*' => '%', '?' => '_'];
    private static  $criteria  = ['cr', 'crs', 'crv'];      // [cr]iterium, [cr].[s]ign, [cr].[v]alue

    public          $error     = false;                     // erronous search fields

    private         $cndSet    = [];

    protected       $parent    = null;                      // itemFilter requires this
    protected       $fiData    = ['c' => [], 'v' =>[]];
    protected       $formData =  array(                     // data to fill form fields
                        'form'        => [],                // base form - unsanitized
                        'setCriteria' => [],                // dynamic criteria list - index checked
                        'setWeights'  => [],                // dynamic weights list  - index checked
                        'extraCols'   => []                 // extra columns for LV  - added as required
                    );

    // parse the provided request into a usable format; recall self with GET-params if nessecary
    public function __construct($parent)
    {
        $this->parent = $parent;

        // prefer POST over GET, translate to url
        if (!empty($_POST))
        {
            foreach ($_POST as $k => $v)
            {
                if (is_array($v))                           // array -> max depths:1
                {
                    if (in_array($k, ['cr', 'wt']) && empty($v[0]))
                        continue;

                    $sub = [];
                    foreach ($v as $sk => $sv)
                        $sub[$sk] = Util::checkNumeric($sv) ? $sv : urlencode($sv);

                    if (!empty($sub) && in_array($k, self::$criteria))
                        $this->fiData['c'][$k] = $sub;
                    else if (!empty($sub))
                        $this->fiData['v'][$k] = $sub;
                }
                else                                        // stings and integer
                {
                    if (in_array($k, self::$criteria))
                        $this->fiData['c'][$k] = Util::checkNumeric($v) ? $v : urlencode($v);
                    else
                        $this->fiData['v'][$k] = Util::checkNumeric($v) ? $v : urlencode($v);
                }
            }

            // create get-data
            $tmp = [];
            foreach (array_merge($this->fiData['c'], $this->fiData['v']) as $k => $v)
            {
                if ($v === '')
                    continue;
                else if (is_array($v))
                    $tmp[$k] = $k."=".implode(':', $v);
                else
                    $tmp[$k] = $k."=".$v;
            }

            // do get request
            header('Location: '.STATIC_URL.'?'.$_SERVER['QUERY_STRING'].'='.implode(';', $tmp));
        }
        // sanitize input and build sql
        else if (!empty($_GET['filter']))
        {
            $tmp = explode(';', $_GET['filter']);
            $cr  = $crs = $crv = [];

            foreach (self::$criteria as $c)
            {
                foreach ($tmp as $i => $term)
                {
                    if (strpos($term, $c.'=') === 0)
                    {
                        $$c = explode(':', explode('=', $term)[1]);
                        $this->formData['setCriteria'][$c] = json_encode($$c, JSON_NUMERIC_CHECK);      // todo (high): move to checks
                        unset($tmp[$i]);
                    }
                }
            }

            for ($i = 0; $i < max(count($cr), count($crv), count($crs)); $i++)
            {
                if (!isset($cr[$i])  || !isset($crs[$i]) || !isset($crv[$i]) ||
                    !intVal($cr[$i]) ||  $crs[$i] === '' ||  $crv[$i] === '')
                {
                    $this->error = true;
                    continue;
                }

                $this->sanitize($crv[$i]);

                if ($crv[$i] !== '')
                {
                    $this->fiData['c']['cr'][]  = intVal($cr[$i]);
                    $this->fiData['c']['crs'][] = intVal($crs[$i]);
                    $this->fiData['c']['crv'][] = $crv[$i];
                }
                else
                    $this->error = true;

            }

            foreach ($tmp as $v)
            {
                if (!strstr($v, '='))
                    continue;

                $w = explode('=', $v);

                if (strstr($w[1], ':'))
                {
                    $tmp2 = explode(':', $w[1]);

                    $this->formData['form'][$w[0]] = $tmp2;

                    array_walk($tmp2, function(&$v) { $v = intVal($v); });
                    $this->fiData['v'][$w[0]] = $tmp2;

                }
                else
                {
                    $this->formData['form'][$w[0]] = $w[1];

                    $this->sanitize($w[1]);

                    if ($w[1] !== '')
                    {
                        Util::checkNumeric($w[1]);
                        $this->fiData['v'][$w[0]] = $w[1];
                    }
                    else
                        $this->error = true;
                }
            }

            $this->evaluateFilter();
        }
    }

    // todo: kill data, that is unexpected or points to wrong indizes
    private function evaluateFilter()
    {
        // values
        $this->cndSet = $this->createSQLForValues();

        // criteria
        foreach ($this->criteriaIterator() as &$_cr)
            $this->cndSet[] = $this->createSQLForCriterium($_cr);

        if ($this->cndSet)
            array_unshift($this->cndSet, empty($this->fiData['v']['ma']) ? 'AND' : 'OR');
    }

    public function getForm()
    {
        return $this->formData;
    }

    public function getConditions()
    {
        return $this->cndSet;
    }

    // santas little helper..
    private function &criteriaIterator()
    {
        if (!$this->fiData['c'])
            return;

        for ($i = 0; $i < count($this->fiData['c']['cr']); $i++)
        {
            // throws a notice if yielded directly "Only variable references should be yielded by reference"
            $v = [&$this->fiData['c']['cr'][$i], &$this->fiData['c']['crs'][$i], &$this->fiData['c']['crv'][$i]];
            yield $i => $v;
        }
    }

    protected function int2Op(&$op)
    {
        switch ($op)
        {
            case 1: $op = '>';    return true;
            case 2: $op = '>=';   return true;
            case 3: $op = '=';    return true;
            case 4: $op = '<=';   return true;
            case 5: $op = '<';    return true;
            default: return false;
        }
    }

    protected function int2Bool(&$op)
    {
        switch ($op)
        {
            case 1: $op = true;   return true;
            case 2: $op = false;  return true;
            default: return false;
        }
    }

    protected function list2Mask($list, $noOffset = false)
    {
        $mask = 0x0;
        $o    = $noOffset ? 0 : 1;                          // schoolMask requires this..?

        if (!is_array($list))
            $mask = (1 << (intVal($list) - $o));
        else
            foreach ($list as $itm)
                $mask += (1 << (intVal($itm) - $o));

        return $mask;
    }

    protected function isSaneNumeric(&$val, $castInt = true)
    {
        if ($castInt && is_float($val))
            $val = intVal($val);

        if (is_int($val) || (is_float($val) && $val >= 0.0))
            return true;

        return false;
    }

    private function sanitize(&$str)
    {
        $str = preg_replace(Filter::$pattern, '', trim($str));
        $str = Util::checkNumeric($str) ? $str : strtr($str, Filter::$wCards);
    }

    private function genericBoolean($field, $op, $isString)
    {
        if ($this->int2Bool($op))
        {
            $value    = $isString ? '' : 0;
            $operator = $op ? '!' : null;

            return [$field, $value, $operator];
        }

        return null;
    }

    private function genericBooleanFlags($field, $value, $op)
    {
        if ($this->int2Bool($op))
            return [[$field, $value, '&'], $op ? $value : 0];

        return null;
    }

    private function genericString($field, $value, $localized)
    {
        $field .= $localized ? '_loc'.User::$localeId : null;

        return [$field, (string)$value];
    }

    private function genericNumeric($field, &$value, $op, $castInt)
    {
        if (!$this->isSaneNumeric($value, $castInt))
            return null;

        if ($this->int2Op($op))
            return [$field, $value, $op];

        return null;
    }

    private function genericEnum($field, $value)
    {
        if (is_bool($value))
            return [$field, 0, ($value ? '>' : '<=')];
        else if ($value == -2323)                           // any
            return [$field, 0, '>'];
        else if ($value == -2324)                           // none
            return [$field, 0, '<='];
        else if ($value !== null)
            return [$field, $value];

        return null;
    }

    protected function genericCriterion(&$cr)
    {
        $gen    = $this->genericFilter[$cr[0]];
        $result = null;

        switch ($gen[0])
        {
            case FILTER_CR_NUMERIC:
                $result = $this->genericNumeric($gen[1], $cr[2], $cr[1], empty($gen[2]));
                break;
            case FILTER_CR_FLAG:
                $result = $this->genericBooleanFlags($gen[1], $gen[2], $cr[1]);
                break;
            case FILTER_CR_STAFFFLAG:
                if (User::isInGroup(U_GROUP_STAFF) && $cr[1] >= 0)
                    $result = $this->genericBooleanFlags($gen[1], (1 << $cr[1]), true);
                break;
            case FILTER_CR_BOOLEAN:
                $result = $this->genericBoolean($gen[1], $cr[1], !empty($gen[2]));
                break;
            case FILTER_CR_STRING:
                $result = $this->genericString($gen[1], $cr[2], !empty($gen[2]));
                break;
            case FILTER_CR_ENUM:
                if (isset($this->enums[$cr[0]][$cr[1]]))
                    $result = $this->genericEnum($gen[1], $this->enums[$cr[0]][$cr[1]]);
                else if (intVal($cr[1]) != 0)
                    $result = $this->genericEnum($gen[1], intVal($cr[1]));
                break;
        }

        if ($result && !empty($gen[3]))
            $this->formData['extraCols'][] = $cr[0];

        return $result;
    }

    // apply Util::sqlEscape() and intVal() in the implementation of these
    abstract protected function createSQLForCriterium(&$cr);
    abstract protected function createSQLForValues();
}

?>
