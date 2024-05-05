<?php

if (!defined('AOWOW_REVISION'))
    die("illegal access");


abstract class BaseType
{
    public    $id        = 0;
    public    $error     = true;

    protected $templates = [];
    protected $curTpl    = [];
    protected $matches   = 0;                               // total matches unaffected by sqlLimit in config

    protected $dbNames   = ['Aowow'];                       // multiple DBs in profiler
    protected $queryBase = '';
    protected $queryOpts = [];

    private   $itrStack  = [];

    public static $dataTable  = '';
    public static $contribute = CONTRIBUTE_ANY;

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

                return $sql ? '('.implode($subLink, $sql).')' : null;
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
        $rows = [];
        // this is purely because of multiple realms per server
        foreach ($this->dbNames as $dbIdx => $n)
        {
            $query = str_replace('DB_IDX', $dbIdx, $this->queryBase);
            if ($rows  = DB::{$n}($dbIdx)->SelectPage($mtch, $query))
            {
                $this->matches += $mtch;
                foreach ($rows as $id => $row)
                {
                    if (isset($this->templates[$id]))
                        trigger_error('GUID for List already in use #'.$id.'. Additional occurrence omitted!', E_USER_ERROR);
                    else
                        $this->templates[$id] = $row;
                }
            }
        }

        if (!$this->templates)
            return;

        // push first element for instant use
        $this->reset();

        // all clear
        $this->error = false;
    }

    public function &iterate()
    {
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
            unset($this->curTpl);                           // kill reference or strange stuff will happen
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

    public function getAllFields($field, $localized = false, $silent = false)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[$this->id] = $this->getField($field, $localized, $silent);

        return $data;
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

    /* source More .. keys seen used
         'n':   name [always set]
         't':   type [always set]
        'ti':   typeId [always set]
        'bd':   BossDrop [0; 1] [Creature / GO]
        'dd':   DungeonDifficulty [-2: DungeonHC; -1: DungeonNM; 1: Raid10NM; 2:Raid25NM; 3:Raid10HM; 4: Raid25HM] [Creature / GO]
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
    abstract public function getListviewData();

    // should return data to extend global js variables for a certain type (e.g. g_items)
    abstract public function getJSGlobals($addMask = GLOBALINFO_ANY);

    // NPC, GO, Item, Quest, Spell, Achievement, Profile would require this
    abstract public function renderTooltip();
}

trait listviewHelper
{
    public function hasSetFields(?string ...$fields) : int
    {
        $result = 0x0;

        foreach ($this->iterate() as $__)
        {
            foreach ($fields as $k => $str)
            {
                if (!$str)
                {
                    unset($fields[$k]);
                    continue;
                }

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

    public function hasDiffFields(?string ...$fields) : int
    {
        $base   = [];
        $result = 0x0;

        foreach ($fields as $k => $str)
            $base[$str] = $this->getField($str);

        foreach ($this->iterate() as $__)
        {
            foreach ($fields as $k => $str)
            {
                if (!$str)
                {
                    unset($fields[$k]);
                    continue;
                }

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
        SPAWNINFO_ZONES => null,
        SPAWNINFO_QUEST => null
    );

    private function createShortSpawns()                    // [zoneId, floor, [[x1, y1], [x2, y2], ..]] as tooltip2 if enabled by <a rel="map" ...> or anchor #map (one area, one floor, one creature, no survivors)
    {
        $this->spawnResult[SPAWNINFO_SHORT] = new StdClass;

        // first get zone/floor with the most spawns
        if ($res = DB::Aowow()->selectRow('SELECT areaId, floor FROM ?_spawns WHERE type = ?d AND typeId = ?d AND posX > 0 AND posY > 0 GROUP BY areaId, floor ORDER BY count(1) DESC LIMIT 1', self::$type, $this->id))
        {
            // get relevant spawn points
            $points = DB::Aowow()->select('SELECT posX, posY FROM ?_spawns WHERE type = ?d AND typeId = ?d AND areaId = ?d AND floor = ?d AND posX > 0 AND posY > 0', self::$type, $this->id, $res['areaId'], $res['floor']);
            $spawns = [];
            foreach ($points as $p)
                $spawns[] = [$p['posX'], $p['posY']];

            $this->spawnResult[SPAWNINFO_SHORT]->zone   = $res['areaId'];
            $this->spawnResult[SPAWNINFO_SHORT]->coords = [$res['floor'] => $spawns];
        }
    }

    private function createFullSpawns()                     // for display on map (object/npc detail page)
    {
        $data     = [];
        $wpSum    = [];
        $wpIdx    = 0;
        $worldPos = [];
        $spawns   = DB::Aowow()->select("SELECT * FROM ?_spawns WHERE type = ?d AND typeId = ?d AND posX > 0 AND posY > 0", self::$type, $this->id);

        if (!$spawns)
            return;

        if (User::isInGroup(U_GROUP_MODERATOR))
            $worldPos = Game::getWorldPosForGUID(self::$type, ...array_column($spawns, 'guid'));

        foreach ($spawns as $s)
        {
            // check, if we can attach waypoints to creature
            // we will get a nice clusterfuck of dots if we do this for more GUIDs, than we have colors though
            if (count($spawns) < 6 && self::$type == Type::NPC)
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
                        if ($i > 0 && $wPoints[$i - 1]['areaId'] == $p['areaId'])
                            $opts['lines'] = [[$wPoints[$i - 1]['posX'], $wPoints[$i - 1]['posY']]];

                        $data[$p['areaId']][$p['floor']]['coords'][] = [$p['posX'], $p['posY'], $opts];
                        if (empty($wpSum[$p['areaId']][$p['floor']]))
                            $wpSum[$p['areaId']][$p['floor']] = 1;
                        else
                            $wpSum[$p['areaId']][$p['floor']]++;
                    }
                    $wpIdx++;
                }
            }

            $opts   = $menu = $tt = $info = [];
            $footer = '';

            if ($s['respawn'])
                $info[1] = '<span class="q0">'.Lang::npc('respawnIn').Lang::main('colon').Lang::formatTime($s['respawn'] * 1000, 'game', 'timeAbbrev', true).'</span>';

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

                if (self::$type == Type::AREATRIGGER)
                {
                    $o = Util::O2Deg($this->getField('orientation'));
                    $info[5] = 'Orientation'.Lang::main('colon').$o[0].'° ('.$o[1].')';
                }

                // guid < 0 are vehicle accessories. those are moved by moving the vehicle
                if (User::isInGroup(U_GROUP_MODERATOR) && $worldPos && $s['guid'] > 0 && isset($worldPos[$s['guid']]))
                {
                    if ($points = Game::worldPosToZonePos($worldPos[$s['guid']]['mapId'], $worldPos[$s['guid']]['posX'], $worldPos[$s['guid']]['posY']))
                    {
                        $floors = [];
                        foreach ($points as $p)
                        {
                            if (isset(Game::$areaFloors[$p['areaId']]))
                                $floors[$p['areaId']][] = $p['floor'];

                            if (isset($menu[$p['areaId']]))
                                continue;
                            else if ($p['areaId'] == $s['areaId'])
                                $menu[$p['areaId']] = [$p['areaId'], '$g_zones['.$p['areaId'].']', '', null, ['class' => 'checked q0']];
                            else
                                $menu[$p['areaId']] = [$p['areaId'], '$g_zones['.$p['areaId'].']', '$spawnposfix.bind(null, '.self::$type.', '.$s['guid'].', '.$p['areaId'].', -1)', null, null];
                        }

                        foreach ($floors as $area => $f)
                        {
                            $menu[$area][2] = '';
                            $menu[$area][3] = [];
                            if ($menu[$area][4])
                                $menu[$area][4]['class'] = 'checked';

                            foreach ($f as $n)
                            {
                                $jsRef = $n;
                                if ($area != 4273)          // Ulduar is weird maaaan.....
                                    $jsRef--;

                                // todo: 3959 (BT) and 4075 (Sunwell) start at level 0 or something

                                if ($n == $s['floor'])
                                    $menu[$area][3][] = [$jsRef, '$g_zone_areas['.$area.']['.$jsRef.']', '', null, ['class' => 'checked q0']];
                                else
                                    $menu[$area][3][] = [$jsRef, '$g_zone_areas['.$area.']['.$jsRef.']', '$spawnposfix.bind(null, '.self::$type.', '.$s['guid'].', '.$area.', '.$n.')'];
                            }
                        }

                        $menu = array_values($menu);
                    }

                    if ($menu)
                    {
                        $footer = '<br /><span class="q2">Click to move displayed spawn point</span>';
                        array_unshift($menu, [null, "Move to..."]);
                    }
                }
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

        uasort($data, array($this, 'sortBySpawnCount'));
        $this->spawnResult[SPAWNINFO_FULL] = $data;
    }

    private function sortBySpawnCount($a, $b)
    {
        $aCount = current($a)['count'];
        $bCount = current($b)['count'];

        if ($aCount == $bCount) {
            return 0;
        }

        return ($aCount < $bCount) ? 1 : -1;
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

    private function createQuestSpawns()                    // [zoneId => [floor => [[x1, y1], [x2, y2], ..]]]      mapper on quest detail page
    {
        if (self::$type == Type::SOUND)
            return;

        $res    = DB::Aowow()->select('SELECT areaId, floor, typeId, posX, posY FROM ?_spawns WHERE type = ?d AND typeId IN (?a) AND posX > 0 AND posY > 0', self::$type, $this->getFoundIDs());
        $spawns = [];
        foreach ($res as $data)
        {
            // zone => floor => spawnData
            // todo (low): why is there a single set of coordinates; which one should be picked, instead of the first? gets used in ShowOnMap.buildTooltip i think
            if (!isset($spawns[$data['areaId']][$data['floor']][$data['typeId']]))
            {
                $spawns[$data['areaId']][$data['floor']][$data['typeId']] = array(
                    'type'          => self::$type,
                    'id'            => $data['typeId'],
                    'point'         => '',                      // tbd later (start, end, requirement, sourcestart, sourceend, sourcerequirement)
                    'name'          => Util::localizedString($this->templates[$data['typeId']], 'name'),
                    'coord'         => [$data['posX'], $data['posY']],
                    'coords'        => [[$data['posX'], $data['posY']]],
                    'objective'     => 0,                       // tbd later (1-4 set a color; id of creature this entry gives credit for)
                    'reactalliance' => $this->templates[$data['typeId']]['A'] ?: 0,
                    'reacthorde'    => $this->templates[$data['typeId']]['H'] ?: 0
                );
            }
            else
                $spawns[$data['areaId']][$data['floor']][$data['typeId']]['coords'][] = [$data['posX'], $data['posY']];
        }

        $this->spawnResult[SPAWNINFO_QUEST] = $spawns;
    }

    public function getSpawns($mode)
    {
        // only Creatures, GOs and SoundEmitters can be spawned
        if (!self::$type || !$this->getfoundIDs() || (self::$type != Type::NPC && self::$type != Type::OBJECT && self::$type != Type::SOUND && self::$type != Type::AREATRIGGER))
            return [];

        switch ($mode)
        {
            case SPAWNINFO_SHORT:
                if ($this->spawnResult[SPAWNINFO_SHORT] === null)
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
            case SPAWNINFO_QUEST:
                if (empty($this->spawnResult[SPAWNINFO_QUEST]))
                    $this->createQuestSpawns();

                return $this->spawnResult[SPAWNINFO_QUEST];
        }

        return [];
    }
}

trait profilerHelper
{
    public  static $type        = 0;                        // arena teams dont actually have one
    public  static $brickFile   = 'profile';                // profile is multipurpose

    private static $subjectGUID = 0;

    public function selectRealms($fi)
    {
        $this->dbNames = [];

        foreach(Profiler::getRealms() as $idx => $r)
        {
            if (!empty($fi['sv']) && Profiler::urlize($r['name']) != Profiler::urlize($fi['sv']) && intVal($fi['sv']) != $idx)
                continue;

            if (!empty($fi['rg']) && Profiler::urlize($r['region']) != Profiler::urlize($fi['rg']))
                continue;

            $this->dbNames[$idx] = 'Characters';
        }

        return !!$this->dbNames;
    }
}

trait sourceHelper
{
    protected $sources    = [];
    protected $sourceMore = null;

    public function getSources(?array &$s, ?array &$sm) : bool
    {
        $s = $sm = null;
        if (empty($this->sources[$this->id]))
            return false;

        if ($this->sourceMore === null)
        {
            $buff = [];
            $this->sourceMore = [];

            foreach ($this->iterate() as $_curTpl)
                if ($_curTpl['moreType'] && $_curTpl['moreTypeId'])
                    $buff[$_curTpl['moreType']][] = $_curTpl['moreTypeId'];

            foreach ($buff as $type => $ids)
                $this->sourceMore[$type] = Type::newList($type, [CFG_SQL_LIMIT_NONE, ['id', $ids]]);
        }

        $s = array_keys($this->sources[$this->id]);
        if ($this->curTpl['moreType'] && $this->curTpl['moreTypeId'] && ($srcData = $this->sourceMore[$this->curTpl['moreType']]->getSourceData($this->curTpl['moreTypeId'])))
            $sm = $srcData;
        else if (!empty($this->sources[$this->id][SRC_PVP]))
            $sm['p'] = $this->sources[$this->id][SRC_PVP][0];

        if ($z = $this->curTpl['moreZoneId'])
            $sm['z'] = $z;

        if ($this->curTpl['moreMask'] & SRC_FLAG_BOSSDROP)
            $sm['bd'] = 1;

        if (isset($this->sources[$this->id][SRC_DROP][0]))
        {
            /*
                mode        srcFlag     log2    dd Flag
                10N/D-NH    0b0001      0       0b001
                25N/D-HC    0b0010      1       0b010
                10H         0b0100      2       0b011
                25H         0b1000      3       0b100
            */
            if ($this->curTpl['moreMask'] & SRC_FLAG_DUNGEON_DROP)
                $sm['dd'] = $this->sources[$this->id][SRC_DROP][0] * -1;
            else if ($this->curTpl['moreMask'] & SRC_FLAG_RAID_DROP)
            {
                $dd = log($this->sources[$this->id][SRC_DROP][0], 2);
                if ($dd == intVal($dd))                             // only one bit set
                    $sm['dd'] = $dd + 1;
            }
        }

        if ($sm)
            $sm = [$sm];

        return true;
    }
}


abstract class Filter
{
    private static  $wCards = ['*' => '%', '?' => '_'];

    public          $error  = false;                        // erronous search fields

    private         $cndSet = [];

    /* genericFilter: [FILTER_TYPE, colOrFnName, param1, param2]
        [FILTER_CR_BOOLEAN,   <string:colName>, <bool:isString>, null]
        [FILTER_CR_FLAG,      <string:colName>, <int:testBit>,   <bool:matchAny>]       # default param2: matchExact
        [FILTER_CR_NUMERIC,   <string:colName>, <int:NUM_FLAGS>, <bool:addExtraCol>]
        [FILTER_CR_STRING,    <string:colName>, <int:STR_FLAGS>, null]
        [FILTER_CR_ENUM,      <string:colName>, <bool:ANYNONE>,  <bool:isEnumVal>]      # param3 ? cr[2] is val in enum : key in enum
        [FILTER_CR_STAFFFLAG, <string:colName>, null,            null]
        [FILTER_CR_CALLBACK,  <string:fnName>,  <mixed:param1>,  <mixed:param2>]
        [FILTER_CR_NYI_PH,    null,             <int:returnVal>, param2]                # mostly 1: to ignore this criterium; 0: to fail the whole query
    */
    protected       $genericFilter = [];

    protected       $enums         = [];                    // criteriumID => [validOptionList]

    /*
        fieldId => [checkType, checkValue[, fieldIsArray]]
    */
    protected       $inputFields   = [];                    // list of input fields defined per page
    protected       $parentCats    = [];                    // used to validate ty-filter
    protected       $fiData        = ['c' => [], 'v' =>[]];
    protected       $formData      =  array(                // data to fill form fields
                        'form'           => [],             // base form - unsanitized
                        'setCriteria'    => [],             // dynamic criteria list             - index checked
                        'setWeights'     => [],             // dynamic weights list              - index checked
                        'extraCols'      => [],             // extra columns for LV              - added as required
                        'reputationCols' => []              // simlar and exclusive to extraCols - added as required
                    );

    protected const PATTERN_NAME = '/[\p{C};%\\\\]/ui';
    protected const PATTERN_CRV  = '/[\p{C};:%\\\\]/ui';
    protected const PATTERN_INT  = '/\D/';

    protected const ENUM_FACTION       = array(  469,  1037,  1106,   529,  1012,    87,    21,   910,   609,   942,   909,   530,    69,   577,   930,  1068,  1104,   729,   369,    92,
                                                  54,   946,    67,  1052,   749,    47,   989,  1090,  1098,   978,  1011,    93,  1015,  1038,    76,   470,   349,  1031,  1077,   809,
                                                 911,   890,   970,   169,   730,    72,    70,   932,  1156,   933,   510,  1126,  1067,  1073,   509,   941,  1105,   990,   934,   935,
                                                1094,  1119,  1124,  1064,   967,  1091,    59,   947,    81,   576,   922,    68,  1050,  1085,   889,   589,   270);
    protected const ENUM_CURRENCY      = array(32572, 32569, 29736, 44128, 20560, 20559, 29434, 37829, 23247, 44990, 24368, 52027, 52030, 43016, 41596, 34052, 45624, 49426, 40752, 47241,
                                               40753, 29024, 24245, 26045, 26044, 38425, 29735, 24579, 24581, 32897, 22484, 52026, 52029,  4291, 28558, 43228, 34664, 47242, 52025, 52028,
                                               37836, 20558, 34597, 43589);
    protected const ENUM_EVENT         = array(  372,   283,   285,   353,   420,   400,   284,   201,   374,   409,   141,   324,   321,   424,   423,   327,   341,  181,   404,    398,
                                                 301);
    protected const ENUM_ZONE          = array( 4494,    36,  2597,  3358,    45,   331,  3790,  4277,    16,  3524,     3,  3959,   719,  1584,    25,  1583,  2677,  3702,  3522,     4,
                                                3525,  3537,    46,  1941,  2918,  3905,  4024,  2817,  4395,  4378,   148,   393,  1657,    41,  2257,   405,  2557,    65,  4196,     1,
                                                  14,    10,    15,   139,    12,  3430,  3820,   361,   357,  3433,   721,   394,  3923,  4416,  2917,  4272,  4820,  4264,  3483,  3562,
                                                 267,   495,  4742,  3606,   210,  4812,  1537,  4710,  4080,  3457,    38,  4131,  3836,  3792,  2100,  2717,   493,   215,  3518,  3698,
                                                3456,  3523,  2367,  2159,  1637,  4813,  4298,  2437,   722,   491,    44,  3429,  3968,   796,  2057,    51,  3607,  3791,  3789,   209,
                                                3520,  3703,  3711,  1377,  3487,   130,  3679,   406,  1519,  4384,    33,  2017,  1477,  4075,     8,   440,   141,  3428,  3519,  3848,
                                                  17,  2366,  3840,  3713,  3847,  3775,  4100,  1581,  3557,  3845,  4500,  4809,    47,  3849,  4265,  4493,  4228,  3698,  4406,  3714,
                                                3717,  3715,   717,    67,  3716,   457,  4415,   400,  1638,  1216,    85,  4723,  4722,  1337,  4273,   490,  1497,   206,  1196,  4603,
                                                 718,  3277,    28,    40,    11,  4197,   618,  3521,  3805,    66,  1176,  1977);
    protected const ENUM_HEROICDUNGEON = array( 4494,  3790,  4277,  4196,  4416,  4272,  4820,  4264,  3562,  4131,  3792,  2367,  4813,  3791,  3789,  3848,  2366,  3713,  3847,  4100,
                                                4809,  3849,  4265,  4228,  3714,  3717,  3715,  3716,  4415,  4723,  206,   1196);
    protected const ENUM_MULTIMODERAID = array( 4812,  3456,  2159,  4500,  4493,  4722,  4273,  4603,  4987);
    protected const ENUM_HEROICRAID    = array( 4987,  4812,  4722);
    protected const ENUM_CLASSS        = array( null,     1,     2,     3,     4,     5,     6,     7,     8,     9,  null,    11,  true, false);
    protected const ENUM_RACE          = array( null,     1,     2,     3,     4,     5,     6,     7,     8,  null,    10,    11,  true, false);
    protected const ENUM_PROFESSION    = array( null,   171,   164,   185,   333,   202,   129,   755,   165,   186,   197,  true, false,   356,   182,   773);

    // parse the provided request into a usable format
    public function __construct($fromPOST = false, $opts = [])
    {
        if (!empty($opts['parentCats']))
            $this->parentCats = $opts['parentCats'];

        if ($fromPOST)
            $this->evaluatePOST();
        else
        {
            // an error occured, while processing POST
            if (isset($_SESSION['fiError']))
            {
                $this->error = $_SESSION['fiError'] == get_class($this);
                unset($_SESSION['fiError']);
            }

            $this->evaluateGET();
        }
    }

    // use to generate cacheKey for filterable pages
    public function __sleep()
    {
        return ['formData'];
    }

    public function mergeCat(&$cats)
    {
        foreach ($this->parentCats as $idx => $cat)
            $cats[$idx] = $cat;
    }

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


    /***********************/
    /* get prepared values */
    /***********************/

    public function getFilterString(array $override = [], array $addCr = [])
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

    public function getExtraCols()
    {
        return array_unique($this->formData['extraCols']);
    }

    public function getSetCriteria()
    {
        return $this->formData['setCriteria'];
    }

    public function getSetWeights()
    {
        return $this->formData['setWeights'];
    }

    public function getReputationCols()
    {
        return $this->formData['reputationCols'];
    }

    public function getForm()
    {
        return $this->formData['form'];
    }

    public function getConditions()
    {
        if (!$this->cndSet)
        {
            // values
            $this->cndSet = $this->createSQLForValues();

            // criteria
            foreach ($this->criteriaIterator() as &$_cr)
                if ($cnd = $this->createSQLForCriterium($_cr))
                    $this->cndSet[] = $cnd;

            if ($this->cndSet)
                array_unshift($this->cndSet, empty($this->fiData['v']['ma']) ? 'AND' : 'OR');
        }

        return $this->cndSet;
    }


    /**********************/
    /* input sanitization */
    /**********************/

    private function evaluatePOST()
    {
        // doesn't need to set formData['form']; this happens in GET-step

        foreach ($this->inputFields as $inp => [$type, $valid, $asArray])
        {
            if (!isset($_POST[$inp]) || $_POST[$inp] === '')
                continue;

            $val = $_POST[$inp];
            $k   = in_array($inp, ['cr', 'crs', 'crv']) ? 'c' : 'v';

            if ($asArray)
            {
                $buff = [];
                foreach ((array)$val as $v)
                    if ($v !== '' && $this->checkInput($type, $valid, $v) && $v !== '')
                       $buff[] = $v;

                if ($buff)
                    $this->fiData[$k][$inp] = $buff;
            }
            else if ($val !== '' && $this->checkInput($type, $valid, $val) && $val !== '')
                $this->fiData[$k][$inp] = $val;
        }

        $this->setWeights();
        $this->setCriteria();
    }

    private function evaluateGET()
    {
        if (empty($_GET['filter']))
            return;

        // squash into usable format
        $post = [];
        foreach (explode(';', $_GET['filter']) as $f)
        {
            if (!strstr($f, '='))
            {
                $this->error = true;
                continue;
            }

            $_ = explode('=', $f);
            $post[$_[0]] = $_[1];
        }

        $cr = $crs = $crv = [];
        foreach ($this->inputFields as $inp => [$type, $valid, $asArray])
        {
            if (!isset($post[$inp]) || $post[$inp] === '')
                continue;

            $val = $post[$inp];
            $k   = in_array($inp, ['cr', 'crs', 'crv']) ? 'c' : 'v';

            if ($asArray)
            {
                $buff = [];
                foreach (explode(':', $val) as $v)
                    if ($v !== '' && $this->checkInput($type, $valid, $v) && $v !== '')
                       $buff[] = $v;

                if ($buff)
                {
                    if ($k == 'v')
                        $this->formData['form'][$inp] = $buff;

                    $this->fiData[$k][$inp] = array_map(function ($x) { return strtr($x, Filter::$wCards); }, $buff);
                }
            }
            else if ($val !== '' && $this->checkInput($type, $valid, $val) && $val !== '')
            {
                if ($k == 'v')
                    $this->formData['form'][$inp] = $val;

                $this->fiData[$k][$inp] = strtr($val, Filter::$wCards);
            }
        }

        $this->setWeights();
        $this->setCriteria();
    }

    private function setCriteria()                          // [cr]iterium, [cr].[s]ign, [cr].[v]alue
    {
        if (empty($this->fiData['c']['cr']) && empty($this->fiData['c']['crs']) && empty($this->fiData['c']['crv']))
            return;
        else if (empty($this->fiData['c']['cr']) || empty($this->fiData['c']['crs']) || empty($this->fiData['c']['crv']))
        {
            unset($this->fiData['c']['cr']);
            unset($this->fiData['c']['crs']);
            unset($this->fiData['c']['crv']);

            $this->error = true;

            return;
        }

        $_cr  = &$this->fiData['c']['cr'];
        $_crs = &$this->fiData['c']['crs'];
        $_crv = &$this->fiData['c']['crv'];

        if (count($_cr) != count($_crv) || count($_cr) != count($_crs) || count($_cr) > 5 || count($_crs) > 5 /*|| count($_crv) > 5*/)
        {
            // use min provided criterion as basis; 5 criteria at most
            $min = max(5, min(count($_cr), count($_crv), count($_crs)));
            if (count($_cr) > $min)
                array_splice($_cr, $min);

            if (count($_crv) > $min)
                array_splice($_crv, $min);

            if (count($_crs) > $min)
                array_splice($_crs, $min);

            $this->error = true;
        }

        for ($i = 0; $i < count($_cr); $i++)
        {
            //  conduct filter specific checks & casts here
            $unsetme = false;
            if (isset($this->genericFilter[$_cr[$i]]))
            {
                $gf = $this->genericFilter[$_cr[$i]];
                switch ($gf[0])
                {
                    case FILTER_CR_NUMERIC:
                        $_ = $_crs[$i];
                        if (!Util::checkNumeric($_crv[$i], $gf[2]) || !$this->int2Op($_))
                            $unsetme = true;
                        break;
                    case FILTER_CR_BOOLEAN:
                    case FILTER_CR_FLAG:
                    case FILTER_CR_STAFFFLAG:
                        $_ = $_crs[$i];
                        if (!$this->int2Bool($_))
                            $unsetme = true;
                        break;
                    case FILTER_CR_ENUM:
                        if (!Util::checkNumeric($_crs[$i], NUM_REQ_INT))
                            $unsetme = true;
                        break;
                }
            }

            if (!$unsetme && intval($_cr[$i]) && $_crs[$i] !== '' && $_crv[$i] !== '')
                continue;

            unset($_cr[$i]);
            unset($_crs[$i]);
            unset($_crv[$i]);

            $this->error = true;
        }

        $this->formData['setCriteria'] = array(
            'cr'  => $_cr,
            'crs' => $_crs,
            'crv' => $_crv
        );
    }

    private function setWeights()
    {
        if (empty($this->fiData['v']['wt']) && empty($this->fiData['v']['wtv']))
            return;

        $_wt  = &$this->fiData['v']['wt'];
        $_wtv = &$this->fiData['v']['wtv'];

        if (empty($_wt) && !empty($_wtv))
        {
            unset($_wtv);
            $this->error = true;
            return;
        }

        if (empty($_wtv) && !empty($_wt))
        {
            unset($_wt);
            $this->error = true;
            return;
        }

        $nwt  = count($_wt);
        $nwtv = count($_wtv);

        if ($nwt > $nwtv)
        {
            array_splice($_wt, $nwtv);
            $this->error = true;
        }
        else if ($nwtv > $nwt)
        {
            array_splice($_wtv, $nwt);
            $this->error = true;
        }

        $this->formData['setWeights'] = [$_wt, $_wtv];
    }

    protected function checkInput($type, $valid, &$val, $recursive = false)
    {
        switch ($type)
        {
            case FILTER_V_EQUAL:
                if (gettype($valid) == 'integer')
                    $val = intval($val);
                else if (gettype($valid) == 'double')
                    $val = floatval($val);
                else /* if (gettype($valid) == 'string') */
                    $val = strval($val);

                if ($valid == $val)
                    return true;

                break;
            case FILTER_V_LIST:
                if (!Util::checkNumeric($val, NUM_CAST_INT))
                    return false;

                foreach ($valid as $k => $v)
                {
                    if (gettype($v) != 'array')
                        continue;

                    if ($this->checkInput(FILTER_V_RANGE, $v, $val, true))
                        return true;

                    unset($valid[$k]);
                }

                if (in_array($val, $valid))
                    return true;

                break;
            case FILTER_V_RANGE:
                if (Util::checkNumeric($val, NUM_CAST_INT) && $val >= $valid[0] && $val <= $valid[1])
                    return true;

                break;
            case FILTER_V_CALLBACK:
                if ($this->$valid($val))
                    return true;

                break;
            case FILTER_V_REGEX:
                if (!preg_match($valid, $val))
                    return true;

                break;
        }

        if (!$recursive)
            $this->error = true;

        return false;
    }

    protected function modularizeString(array $fields, $string = '', $exact = false, $shortStr = false)
    {
        if (!$string && !empty($this->fiData['v']['na']))
            $string = $this->fiData['v']['na'];

        $qry  = [];
        $exPH = $exact ? '%s' : '%%%s%%';
        foreach ($fields as $n => $f)
        {
            $sub   = [];
            $parts = $exact ? [$string] : array_filter(explode(' ', $string));
            foreach ($parts as $p)
            {
                if ($p[0] == '-' && (mb_strlen($p) > 3 || $shortStr))
                    $sub[] = [$f, sprintf($exPH, str_replace('_', '\\_', mb_substr($p, 1))), '!'];
                else if ($p[0] != '-' && (mb_strlen($p) > 2 || $shortStr))
                    $sub[] = [$f, sprintf($exPH, str_replace('_', '\\_', $p))];
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

    protected function list2Mask(array $list, $noOffset = false)
    {
        $mask = 0x0;
        $o    = $noOffset ? 0 : 1;                          // schoolMask requires this..?

        foreach ($list as $itm)
            $mask += (1 << (intval($itm) - $o));

        return $mask;
    }


    /**************************/
    /* create conditions from */
    /*    generic criteria    */
    /**************************/

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

    private function genericBooleanFlags($field, $value, $op, $matchAny = false)
    {
        if (!$this->int2Bool($op))
            return null;

        if (!$op)
            return [[$field, $value, '&'], 0];
        else if ($matchAny)
            return [[$field, $value, '&'], 0, '!'];
        else
            return [[$field, $value, '&'], $value];
    }

    private function genericString($field, $value, $strFlags)
    {
        if ($strFlags & STR_LOCALIZED)
            $field .= '_loc'.User::$localeId;

        return $this->modularizeString([$field], (string)$value, $strFlags & STR_MATCH_EXACT, $strFlags & STR_ALLOW_SHORT);
    }

    private function genericNumeric($field, &$value, $op, $typeCast)
    {
        if (!Util::checkNumeric($value, $typeCast))
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
        $gen    = array_pad($this->genericFilter[$cr[0]], 4, null);
        $result = null;

        switch ($gen[0])
        {
            case FILTER_CR_NUMERIC:
                $result = $this->genericNumeric($gen[1], $cr[2], $cr[1], $gen[2]);
                break;
            case FILTER_CR_FLAG:
                $result = $this->genericBooleanFlags($gen[1], $gen[2], $cr[1], $gen[3]);
                break;
            case FILTER_CR_STAFFFLAG:
                if (User::isInGroup(U_GROUP_EMPLOYEE) && $cr[1] >= 0)
                    $result = $this->genericBooleanFlags($gen[1], (1 << $cr[1]), true);
                break;
            case FILTER_CR_BOOLEAN:
                $result = $this->genericBoolean($gen[1], $cr[1], !empty($gen[2]));
                break;
            case FILTER_CR_STRING:
                $result = $this->genericString($gen[1], $cr[2], $gen[2]);
                break;
            case FILTER_CR_ENUM:
                if (!$gen[3] && isset($this->enums[$cr[0]][$cr[1]]))
                    $result = $this->genericEnum($gen[1], $this->enums[$cr[0]][$cr[1]]);
                if ($gen[3] && in_array($cr[1], $this->enums[$cr[0]]))
                    $result = $this->genericEnum($gen[1], $cr[1]);
                else if ($gen[2] && ($cr[1] == FILTER_ENUM_ANY || $cr[1] == FILTER_ENUM_NONE))
                    $result = $this->genericEnum($gen[1], $cr[1]);
                break;
            case FILTER_CR_CALLBACK:
                $result = $this->{$gen[1]}($cr, $gen[2], $gen[3]);
                break;
            case FILTER_CR_NYI_PH:                          // do not limit with not implemented filters
                if (is_int($gen[2]))
                    return [$gen[2]];

                // for nonsensical values; compare against 0
                if ($this->int2Op($cr[1]) && Util::checkNumeric($cr[2]))
                {
                    if ($cr[1] == '=')
                        $cr[1] = '==';

                    return eval('return ('.$cr[2].' '.$cr[1].' 0);') ? [1] : [0];
                }
                else
                    return [0];
        }

        if ($result && $gen[0] == FILTER_CR_NUMERIC && !empty($gen[3]))
            $this->formData['extraCols'][] = $cr[0];

        return $result;
    }


    /***********************************/
    /*     create conditions from      */
    /* non-generic values and criteria */
    /***********************************/

    protected function createSQLForCriterium(array &$cr) : array
    {
        if (!$this->genericFilter)                          // criteria not in use - no error
            return [];

        if (in_array($cr[0], array_keys($this->genericFilter)))
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

        $this->error = true;
        trigger_error('Filter::createSQLForCriterium - received unhandled criterium: ["'.$cr[0].'", "'.$cr[1].'", "'.$cr[2].'"]', E_USER_WARNING);

        unset($cr);

        return [];
    }

    abstract protected function createSQLForValues();
}

?>
