<?php

if (!defined('AOWOW_REVISION'))
    die("illegal access");


abstract class BaseType
{
    public    $id        = 0;
    public    $error     = true;

    protected $templates = [];
    protected $curTpl    = [];                              // lets iterate!
    protected $matches   = 0;                               // total matches unaffected by sqlLimit in config

    protected $dbNames   = ['Aowow'];                       // multiple DBs in profiler
    protected $queryBase = '';
    protected $queryOpts = [];

    /*
    *   condition as array [expression, value, operator]
    *       expression:    str   - must match fieldname;
    *                      int   - 1: select everything; 0: select nothing
    *                      array - another condition array
    *       value:         str   - operator defaults to: LIKE <val>
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
    *           ['name', 'test%', '!'],
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
    *       WHERE ((`id` = 45) OR (`name` NOT LIKE "test%") OR ((`flags` & 255) AND (`flags2` & 15)) OR ((`mask` & 3) = 0)) OR (`joinedTbl`.`field` IS NULL) LIMIT 5
    */
    public function __construct($conditions = [], $miscData = null)
    {
        $where     = [];
        $linking   = ' AND ';
        $limit     = CFG_SQL_LIMIT_DEFAULT;
        $className = get_class($this);

        if (!$this->queryBase || $conditions === null)
            return;

        $prefixes = [];
        if (preg_match('/FROM \??[\w\_]+( AS)?\s?`?(\w+)`?$/i', $this->queryBase, $match))
            $prefixes['base'] = $match[2];
        else
            $prefixes['base'] = '';

        if ($miscData && !empty($miscData['extraOpts']))
            $this->extendQueryOpts($miscData['extraOpts']);

        $resolveCondition = function ($c, $supLink) use (&$resolveCondition, &$prefixes, $miscData)
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

                        // numeric allows for formulas e.g. (1 < 3)
                        if (Util::checkNumeric($f))
                            return $f;

                        // skip condition if fieldName contains illegal chars
                        if (preg_match('/[^\d\w\.\_]/i', $f))
                            return null;

                        $f = explode('.', $f);

                        switch (count($f))
                        {
                            case 2:
                                if (!in_array($f[0], $prefixes))
                                {
                                    // choose table to join or return null if prefix does not exist
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

                if (is_array($c[1]) && !empty($c[1]))
                {
                    array_walk($c[1], function(&$item, $key) {
                        $item = Util::checkNumeric($item) ? $item : DB::Aowow()->escape($item);
                    });

                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT IN' : 'IN';
                    $val = '('.implode(', ', $c[1]).')';
                }
                else if (Util::checkNumeric($c[1]))
                {
                    $op  = (isset($c[2]) && $c[2] == '!') ? '<>' : '=';
                    $val = $c[1];
                }
                else if (is_string($c[1]))
                {
                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT LIKE' : 'LIKE';
                    $val = DB::Aowow()->escape($c[1]);
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
            switch (getType($c))
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

        // prepare usage of guids if using multiple DBs
        if (count($this->dbNames) > 1)
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
            $this->queryBase .= ' WHERE ('.implode($linking, $where).')';

        // append grouping
        if ($g = array_filter(array_column($this->queryOpts, 'g')))
            $this->queryBase .= ' GROUP BY '.implode(', ', $g);

        // append post filtering
        if ($h = array_filter(array_column($this->queryOpts, 'h')))
            $this->queryBase .= ' HAVING '.implode(' AND ', $h);

        // append ordering
        if ($o = array_filter(array_column($this->queryOpts, 'o')))
            $this->queryBase .= ' ORDER BY '.implode(', ', $o);

        // apply limit
        if ($limit)
            $this->queryBase .= ' LIMIT '.$limit;

        // execute query (finally)
        $mtch = 0;
        // this is purely because of multiple realms per server
        foreach ($this->dbNames as $dbIdx => $n)
        {
            $query = str_replace('DB_IDX', $dbIdx, $this->queryBase);

            if ($rows = DB::{$n}($dbIdx)->SelectPage($mtch, $query))
            {
                $this->matches += $mtch;
                foreach ($rows as $id => $row)
                {
                    if (isset($this->templates[$id]))
                        trigger_error('guid for List already in use #'.$id, E_USER_WARNING);
                    else
                        $this->templates[$id] = $row;
                }
            }
        }

        if (!$this->templates)
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
        {
            $this->curTpl = $this->templates[$id];
            $this->id     = $id;
            return $this->templates[$id];
        }

        return null;
    }

    public function getField($field, $localized = false, $silent = false)
    {
        if (!$this->curTpl || (!$localized && !isset($this->curTpl[$field])))
            return '';

        if ($localized)
            return Util::localizedString($this->curTpl, $field, $silent);

        $value = $this->curTpl[$field];
        Util::checkNumeric($value);

        return $value;
    }

    public function getRandomId()
    {
        // ORDER BY RAND() is not optimal, so if anyone has an alternative idea..
        $where   = User::isInGroup(U_GROUP_EMPLOYEE) ? 'WHERE (cuFlags & '.CUSTOM_EXCLUDE_FOR_LISTVIEW.') = 0' : null;
        $pattern = '/SELECT .* (-?`?[\w_]*\`?.?`?(id|entry)`?) AS ARRAY_KEY,?.* FROM (\?[\w_-]+) (`?\w*`?)/i';
        $replace = 'SELECT $1 FROM $3 $4 '.$where.' ORDER BY RAND() ASC LIMIT 1';

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

    protected function extendQueryOpts($extra)              // needs to be called from __construct
    {

        foreach ($extra as $tbl => $sets)
        {
            if (!isset($this->queryOpts[$tbl]))             // allow adding only to known tables
                continue;

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
                        if (is_array($this->queryOpts[$tbl][$module]))
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

    /* source More .. keys seen used
         'n':   name [always set]
         't':   type [always set]
        'ti':   typeId [always set]
        'bd':   BossDrop [0; 1] [Creature / GO]
        'dd':   DungeonDifficulty [-2: DungeonHC; -1: DungeonNM; 1: Raid10NM; 2:Raid25NM; 3:Raid10HM; 4: Raid25HM] [Creature / GO]
         'q':   cssQuality [Items]
         'z':   zone [set when all happens in here]
         'p':   PvP [pvpSourceId]
         's':   TYPE_TITLE: side; TYPE_SPELL: skillId (yeah, double use. Ain't life just grand)
         'c':   category [Spells / Quests]
        'c2':   subCat [Quests]
      'icon':   iconString
    */
    public function getSourceData() {}

    // should return data required to display a listview of any kind
    // this is a rudimentary example, that will not suffice for most Types
    abstract public function getListviewData();

    // should return data to extend global js variables for a certain type (e.g. g_items)
    abstract public function getJSGlobals($addMask = GLOBALINFO_ANY);

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

/*
    !IMPORTANT!
    It is flat out impossible to distinguish between floors for multi-level areas, if the floors overlap each other!
    The coordinates generated by the script WILL be on every level and will have to be removed MANUALLY!

    impossible := you are not keen on reading wmo-data;
*/
trait spawnHelper
{
    private $spawnResult = array(
        SPAWNINFO_FULL  => null,
        SPAWNINFO_SHORT => null,
        SPAWNINFO_ZONES => null
    );

    private function createShortSpawns()                    // [zoneId, floor, [[x1, y1], [x2, y2], ..]] as tooltip2 if enabled by <a rel="map" ...> or anchor #map (one area, one floor, one creature, no survivors)
    {
        // first get zone/floor with the most spawns
        if ($res = DB::Aowow()->selectRow('SELECT areaId, floor FROM ?_spawns WHERE type = ?d && typeId = ?d GROUP BY areaId, floor ORDER BY count(1) DESC LIMIT 1', self::$type, $this->id))
        {
            // get relevant spawn points
            $points = DB::Aowow()->select('SELECT posX, posY FROM ?_spawns WHERE type = ?d && typeId = ?d && areaId = ?d && floor = ?d', self::$type, $this->id, $res['areaId'], $res['floor']);
            $spawns = [];
            foreach ($points as $p)
                $spawns[] = [$p['posX'], $p['posY']];

            $this->spawnResult[SPAWNINFO_SHORT] = [$res['areaId'], $res['floor'], $spawns];
        }
    }

    private function createFullSpawns()                     // for display on map (objsct/npc detail page)
    {
        $data   = [];
        $wpSum  = [];
        $wpIdx  = 0;
        $spawns = DB::Aowow()->select("SELECT * FROM ?_spawns WHERE type = ?d AND typeId = ?d", self::$type, $this->id);
        if (!$spawns)
            return;

        foreach ($spawns as $s)
        {
            // check, if we can attach waypoints to creature
            // we will get a nice clusterfuck of dots if we do this for more GUIDs, than we have colors though
            if (count($spawns) < 6 && self::$type == TYPE_NPC)
            {
                if ($wPoints = DB::Aowow()->select('SELECT * FROM ?_creature_waypoints WHERE creatureOrPath = ?d AND floor = ?d', $s['pathId'] ? -$s['pathId'] : $this->id, $s['floor']))
                {
                    foreach ($wPoints as $i => $p)
                    {
                        $label = [Lang::npc('waypoint').Lang::main('colon').$p['point']];

                        if ($p['wait'])
                            $label[] = Lang::npc('wait').Lang::main('colon').Util::formatTime($p['wait'], false);

                        $opts = array(                      // \0 doesn't get printed and tricks Util::toJSON() into handling this as a string .. i feel slightly dirty now
                            'label' => "\0$<br><span class=\"q0\">".implode('<br>', $label).'</span>',
                            'type'  => $wpIdx
                        );

                        // connective line
                        if ($i > 0)
                            $opts['lines'] = [[$wPoints[$i - 1]['posX'], $wPoints[$i - 1]['posY']]];

                        $data[$s['areaId']][$s['floor']]['coords'][] = [$p['posX'], $p['posY'], $opts];
                        if (empty($wpSum[$s['areaId']][$s['floor']]))
                            $wpSum[$s['areaId']][$s['floor']] = 1;
                        else
                            $wpSum[$s['areaId']][$s['floor']]++;
                    }
                    $wpIdx++;
                }
            }

            $opts   = $menu = $tt = $info = [];
            $footer = '';

            if ($s['respawn'])
                $info[1] = '<span class="q0">'.Lang::npc('respawnIn').Lang::main('colon').Util::formatTime($s['respawn'] * 1000, false).'</span>';

            if (User::isInGroup(U_GROUP_STAFF))
            {
                $info[0] = $s['guid'] < 0 ? 'Vehicle Accessory' : 'GUID'.Lang::main('colon').$s['guid'];

                if ($s['phaseMask'] > 1 && ($s['phaseMask'] & 0xFFFF) != 0xFFFF)
                    $info[2] = Lang::game('phases').Lang::main('colon').Util::asHex($s['phaseMask']);

                if ($s['spawnMask'] == 15)
                    $info[3] = Lang::game('mode').Lang::main('colon').Lang::game('modes', -1);
                else if ($s['spawnMask'])
                {
                    $_ = [];
                    for ($i = 0; $i < 4; $i++)
                        if ($s['spawnMask'] & 1 << $i)
                            $_[] = Lang::game('modes', $i);

                    $info[4] = Lang::game('mode').Lang::main('colon').implode(', ', $_);
                }

                $footer = '<span class="q2">Click to move to different floor</span>';
            }

            if ($info)
                $tt['info'] = $info;

            if ($footer)
                $tt['footer'] = $footer;

            if ($tt)
                $opts['tooltip'] = [$this->getField('name', true) => $tt];

            if ($menu)
                $opts['menu'] = $menu;

            $data[$s['areaId']] [$s['floor']] ['coords'] [] = [$s['posX'], $s['posY'], $opts];
        }
        foreach ($data as $a => &$areas)
            foreach ($areas as $f => &$floor)
                $floor['count'] = count($floor['coords']) - (!empty($wpSum[$a][$f]) ? $wpSum[$a][$f] : 0);

        $this->spawnResult[SPAWNINFO_FULL] = $data;
    }

    private function createZoneSpawns()                     // [zoneId1, zoneId2, ..]             for locations-column in listview
    {
        $res = DB::Aowow()->selectCol("SELECT typeId AS ARRAY_KEY, GROUP_CONCAT(DISTINCT areaId) FROM ?_spawns WHERE type = ?d AND typeId IN (?a) GROUP BY typeId", self::$type, $this->getfoundIDs());
        foreach ($res as &$r)
        {
            $r = explode(',', $r);
            if (count($r) > 3)
                array_splice($r, 3, count($r), -1);
        }

        $this->spawnResult[SPAWNINFO_ZONES] = $res;
    }

    public function getSpawns($mode)
    {
        // ony Creatures and GOs can be spawned
        if (!self::$type || (self::$type != TYPE_NPC && self::$type != TYPE_OBJECT))
            return [];

        switch ($mode)
        {
            case SPAWNINFO_SHORT:
                if (empty($this->spawnResult[SPAWNINFO_SHORT]))
                    $this->createShortSpawns();

                return $this->spawnResult[SPAWNINFO_SHORT];
            case SPAWNINFO_FULL:
                if (empty($this->spawnResult[SPAWNINFO_FULL]))
                    $this->createFullSpawns();

                return $this->spawnResult[SPAWNINFO_FULL];
            case SPAWNINFO_ZONES:
                if (empty($this->spawnResult[SPAWNINFO_ZONES]))
                    $this->createZoneSpawns();

                return !empty($this->spawnResult[SPAWNINFO_ZONES][$this->id]) ? $this->spawnResult[SPAWNINFO_ZONES][$this->id] : [];
        }

        return [];
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

    protected       $fiData    = ['c' => [], 'v' =>[]];
    protected       $formData  =  array(                    // data to fill form fields
                        'form'           => [],             // base form - unsanitized
                        'setCriteria'    => [],             // dynamic criteria list             - index checked
                        'setWeights'     => [],             // dynamic weights list              - index checked
                        'extraCols'      => [],             // extra columns for LV              - added as required
                        'reputationCols' => []              // simlar and exclusive to extraCols - added as required
                    );

    // parse the provided request into a usable format; recall self with GET-params if nessecary
    public function __construct()
    {
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

            // do get request
            header('Location: '.HOST_URL.'?'.$_SERVER['QUERY_STRING'].'='.$this->urlize(), true, 302);
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
                        $this->formData['setCriteria'][$c] = $$c;      // todo (high): move to checks
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

                    array_walk($tmp2, function(&$v) {
                        $v = intVal($v);
                    });
                    $this->fiData['v'][$w[0]] = $tmp2;
                }
                else
                {
                    $this->formData['form'][$w[0]] = $w[1];

                    $this->sanitize($w[1]);

                    if ($w[1] !== '')
                        $this->fiData['v'][$w[0]] = $w[1];
                    else
                        $this->error = true;
                }
            }
        }
    }

    // use to generate cacheKey for filterable pages
    public function __sleep()
    {
        return ['formData'];
    }

    public function urlize(array $override = [], array $addCr = [])
    {
        $_ = [];
        foreach (array_merge($this->fiData['c'], $this->fiData['v'], $override) as $k => $v)
        {
            if (isset($addCr[$k]))
            {
                $v = $v ? array_merge((array)$v, (array)$addCr[$k]) : $addCr[$k];
                unset($addCr[$k]);
            }

            if (is_array($v) && !empty($v))
                $_[$k] = $k.'='.implode(':', $v);
            else if ($v !== '')
                $_[$k] = $k.'='.$v;
        }

        // no criteria were set, so no merge occured .. append
        if ($addCr)
        {
            $_['cr']  = 'cr='.$addCr['cr'];
            $_['crs'] = 'crs='.$addCr['crs'];
            $_['crv'] = 'crv='.$addCr['crv'];
        }

        return implode(';', $_);
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

    public function getForm($key = null, $raw = false)
    {
        $form = [];

        if (!$this->formData)
            return $form;

        foreach ($this->formData as $name => $data)
        {
            if (!$data || ($key && $name != $key))
                continue;

            switch ($name)
            {
                case 'setCriteria':
                    if ($data || $raw)
                        $form[$name] = $raw ? $data : 'fi_setCriteria('.Util::toJSON($data['cr']).', '.Util::toJSON($data['crs']).', '.Util::toJSON($data['crv']).');';
                    else
                        $form[$name] = 'fi_setCriteria([], [], []);';
                    break;
                case 'extraCols':
                    $form[$name] = $raw ? $data : 'fi_extraCols = '.Util::toJSON(array_unique($data)).';';
                    break;
                case 'setWeights':
                    $form[$name] = $raw ? $data : 'fi_setWeights('.Util::toJSON($data).', 0, 1, 1);';
                    break;
                case 'form':
                case 'reputationCols':
                    if ($key == $name)                      // only if explicitely specified
                        $form[$name] = $data;
                    break;
                default:
                    break;
            }
        }

        return $key ? (empty($form[$key]) ? [] : $form[$key]) : $form;
    }

    public function getConditions()
    {
        if (!$this->cndSet)
            $this->evaluateFilter();

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

    protected function modularizeString(array $fields, $string = '', $exact = false)
    {
        if (!$string && !empty($this->fiData['v']['na']))
            $string = $this->fiData['v']['na'];

        $qry  = [];
        $exPH = $exact ? '%s' : '%%%s%%';
        foreach ($fields as $n => $f)
        {
            $sub   = [];
            $parts = array_filter(explode(' ', $string));
            foreach ($parts as $p)
            {
                if ($p[0] == '-' && mb_strlen($p) > 3)
                    $sub[] = [$f, sprintf($exPH, mb_substr($p, 1)), '!'];
                else if ($p[0] != '-' && mb_strlen($p) > 2)
                    $sub[] = [$f, sprintf($exPH, $p)];
            }

            // single cnd?
            if (!$sub)
                continue;
            else if (count($sub) > 1)
                array_unshift($sub, 'AND');
            else
                $sub = $sub[0];

            $qry[] = $sub;
        }

        // single cnd?
        if (!$qry)
            $this->error = true;
        else if (count($qry) > 1)
            array_unshift($qry, 'OR');
        else
            $qry = $qry[0];

        return $qry;
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
            case 6: $op = '!=';   return true;
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
        if ($localized)
            $field .= '_loc'.User::$localeId;

        return $this->modularizeString([$field], (string)$value);
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
        else if ($value == FILTER_ENUM_ANY)                 // any
            return [$field, 0, '!'];
        else if ($value == FILTER_ENUM_NONE)                // none
            return [$field, 0];
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
                if (User::isInGroup(U_GROUP_EMPLOYEE) && $cr[1] >= 0)
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

    abstract protected function createSQLForCriterium(&$cr);
    abstract protected function createSQLForValues();
}

?>
