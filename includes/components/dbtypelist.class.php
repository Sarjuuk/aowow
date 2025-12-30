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
    *           ['joinedTbl.field', NULL]                   // NULL must be explicitly specified "['joinedTbl.field']" would be skipped as erroneous definition (only really usefull when left-joining)
    *           'OR',
    *           5
    *       )
    *   results in
    *       WHERE ((`id` = 45) OR (`name` NOT LIKE "test%") OR ((`flags` & 255) AND (`flags2` & 15)) OR ((`mask` & 3) = 0)) OR (`joinedTbl`.`field` IS NULL) LIMIT 5
    */
    public function __construct(array $conditions = [], array $miscData = [])
    {
        $where   = [];
        $linking = ' AND ';
        $limit   = 0;

        $calcTotal  = false;
        $totalQuery = '';

        if (!$this->queryBase || $conditions === null)
            return;

        $prefixes = [];
        if (preg_match('/FROM \??[\w\_]+( AS)?\s?`?(\w+)`?$/i', $this->queryBase, $match))
            $prefixes['base'] = $match[2];
        else
            $prefixes['base'] = '';

        if (!empty($miscData['extraOpts']))
            $this->extendQueryOpts($miscData['extraOpts']);

        if (!empty($miscData['calcTotal']))
            $calcTotal = true;

        $resolveCondition = function (array $c, string $supLink) use (&$resolveCondition, &$prefixes) : ?string
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
                    $setPrefix = function(mixed $f) use(&$prefixes) : ?string
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
                    array_walk($c[1], fn(&$x) => $x = Util::checkNumeric($x) ? $x : DB::Aowow()->escape($x));

                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT IN' : 'IN';
                    $val = '('.implode(', ', $c[1]).')';
                }
                else if (Util::checkNumeric($c[1]))         // Note: should this be a NUM_REQ_* check?
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
                    if (is_numeric($c))
                        $limit = max(0, (int)$c);
                    else
                        $linking = $c == 'AND' ? ' AND ' : ' OR ';
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
        $rows = [];
        // this is purely because of multiple realms per server
        foreach ($this->dbNames as $dbIdx => $n)
        {
            $query = str_replace('DB_IDX', $dbIdx, $this->queryBase);
            if ($rows  = DB::{$n}($dbIdx)->select($query))
            {
                if ($calcTotal)
                {
                    // hackfix the inner items query to not contain duplicate column names
                    // yes i know the real solution would be to not have items and item_stats share column names
                    // soon™....
                    if (get_class($this) == ItemList::class)
                        $totalQuery = str_replace([', `is`.*', ', i.`id` AS "id"'], '', $totalQuery);

                    $this->matches += DB::{$n}($dbIdx)->selectCell('SELECT COUNT(*) FROM ('.$totalQuery.') x');
                }

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
        // ORDER BY RAND() is not optimal, so if anyone has an alternative idea..
        $where = User::isInGroup(U_GROUP_EMPLOYEE) ? ' WHERE (`cuFlags` & '.CUSTOM_EXCLUDE_FOR_LISTVIEW.') = 0' : '';

        if (preg_match('/SELECT .*? FROM (\?\_[\w_-]+) /i', $this->queryBase, $m))
            return DB::Aowow()->selectCell(sprintf('SELECT `id` FROM %s%s ORDER BY RAND() ASC LIMIT 1', $m[1], $where));

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
        if ($n = DB::Aowow()->SelectRow('SELECT `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM ?# WHERE `id` = ?d', static::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public static function makeLink(int $id, int $fmt = Lang::FMT_HTML, string $cssClass = '') : string
    {
        if ($n = static::getName($id))
        {
            return match ($fmt)
            {
                Lang::FMT_HTML   => '<a href="?'.Type::getFileString(static::$type).'='.$id.'"'.($cssClass ? ' class="'.$cssClass.'"' : '').'>'.$n.'</a>',
                Lang::FMT_MARKUP => '[url=?'.Type::getFileString(static::$type).'='.$id.']'.$n.'[/url]',
                default          => $n
            };
        }

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

    public function hasAnySource() : bool
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

    private function createShortSpawns() : void             // [zoneId, floor, [[x1, y1], [x2, y2], ..]] as tooltip2 if enabled by <a rel="map" ...> or anchor #map (one area, one floor, one creature, no survivors)
    {
        $this->spawnResult[SPAWNINFO_SHORT] = new \StdClass;

        // first get zone/floor with the most spawns
        if ($res = DB::Aowow()->selectRow('SELECT `areaId`, `floor` FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d AND `posX` > 0 AND `posY` > 0 GROUP BY `areaId`, `floor` ORDER BY COUNT(1) DESC LIMIT 1', self::$type, $this->id))
        {
            // get relevant spawn points
            $points = DB::Aowow()->select('SELECT `posX`, `posY` FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d AND `areaId` = ?d AND `floor` = ?d AND `posX` > 0 AND `posY` > 0', self::$type, $this->id, $res['areaId'], $res['floor']);
            $spawns = [];
            foreach ($points as $p)
                $spawns[] = [$p['posX'], $p['posY']];

            $this->spawnResult[SPAWNINFO_SHORT]->zone   = $res['areaId'];
            $this->spawnResult[SPAWNINFO_SHORT]->coords = [$res['floor'] => $spawns];
        }
    }

    // for display on map (object/npc detail page)
    private function createFullSpawns(bool $skipWPs = false, bool $skipAdmin = false, bool $hasLabel = false, bool $hasLink = false) : void
    {
        $data     = [];
        $wpSum    = [];
        $wpIdx    = 0;
        $worldPos = [];
        $spawns   = DB::Aowow()->select(
           'SELECT CASE WHEN z.`type` = ?d THEN 1
                        WHEN z.`type` = ?d THEN 2
                        WHEN z.`type` = ?d THEN 2
                        ELSE 0
                   END AS "mapType", s.*
            FROM   ?_spawns s
            JOIN   ?_zones z ON s.areaId = z.id
            WHERE s.`type` = ?d AND s.`typeId` IN (?a) AND s.`posX` > 0 AND s.`posY` > 0',
            MAP_TYPE_DUNGEON_HC, MAP_TYPE_MMODE_RAID, MAP_TYPE_MMODE_RAID_HC,
            self::$type, $this->getFoundIDs()
        ) ?: [];

        if (!$skipAdmin && User::isInGroup(U_GROUP_MODERATOR))
            if ($guids = array_column(array_filter($spawns, fn($x) => $x['guid'] > 0 || $x['type'] != Type::NPC), 'guid'))
                $worldPos = WorldPosition::getForGUID(self::$type, ...$guids);

        foreach ($spawns as $s)
        {
            $isAccessory = $s['guid'] < 0 && $s['type'] == Type::NPC;

            // check, if we can attach waypoints to creature
            // we will get a nice clusterfuck of dots if we do this for more GUIDs, than we have colors though
            if (!$skipWPs && count($spawns) < 6 && $s['type'] == Type::NPC)
            {
                if ($wPoints = DB::Aowow()->select('SELECT * FROM ?_creature_waypoints WHERE creatureOrPath = ?d AND floor = ?d', $s['pathId'] ? -$s['pathId'] : $this->id, $s['floor']))
                {
                    foreach ($wPoints as $i => $p)
                    {
                        $label = [Lang::npc('waypoint').Lang::main('colon').$p['point']];

                        if ($p['wait'])
                            $label[] = Lang::npc('wait').Lang::main('colon').DateTime::formatTimeElapsedFloat($p['wait']);

                        $opts = array(                      // \0 doesn't get printed and tricks Util::toJSON() into handling this as a string .. i feel slightly dirty now
                            'label' => "\0$<br /><span class=\"q0\">".implode('<br />', $label).'</span>',
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

            if ($s['respawn'] > 0)
                $info[1] = '<span class="q0">'.Lang::npc('respawnIn', [Lang::formatTime($s['respawn'] * 1000, 'game', 'timeAbbrev', true)]).'</span>';
            else if ($s['respawn'] < 0)
            {
                $info[1] = '<span class="q0">'.Lang::npc('despawnAfter', [Lang::formatTime(-$s['respawn'] * 1000, 'game', 'timeAbbrev', true)]).'</span>';
                $opts['type'] = 4;                          // make pip purple
            }

            if (!$skipAdmin && User::isInGroup(U_GROUP_STAFF))
            {
                if ($isAccessory)
                    $info[0] = 'Vehicle Accessory';
                else if ($s['guid'] > 0 && ($s['type'] == Type::NPC || $s['type'] == Type::OBJECT))
                    $info[0] = 'GUID'.Lang::main('colon').$s['guid'];

                if ($s['phaseMask'] > 1 && ($s['phaseMask'] & 0xFFFF) != 0xFFFF)
                    $info[2] = Lang::game('phases').Lang::main('colon').Util::asHex($s['phaseMask']);

                if ($s['spawnMask'] == 15)
                    $info[3] = Lang::game('mode').Lang::game('modes', 0, -1);
                else if ($s['spawnMask'])
                {
                    $_ = [];
                    for ($i = 0; $i < 4; $i++)
                        if ($s['spawnMask'] & 1 << $i)
                            $_[] = Lang::game('modes', $s['mapType'], $i);

                    $info[4] = Lang::game('mode').implode(', ', $_);
                }

                if ($s['ScriptName'])
                    $info[5] = 'ScriptName'.Lang::main('colon').$s['ScriptName'];
                if ($s['StringId'])
                    $info[6] = 'StringId'.Lang::main('colon').$s['StringId'];

                if ($s['type'] == Type::AREATRIGGER)
                {
                    // teleporter endpoint
                    if ($s['guid'] < 0)
                    {
                        $opts['type'] = 4;
                        $info[7] = 'Teleport Destination';
                    }
                    else
                    {
                        $o = Util::O2Deg($this->getField('orientation'));
                        $info[7] = 'Orientation'.Lang::main('colon').$o[0].'° ('.$o[1].')';
                    }
                }

                // guid < 0 are vehicle accessories. those are moved by moving the vehicle
                if (User::isInGroup(U_GROUP_MODERATOR) && $worldPos && !$isAccessory && isset($worldPos[$s['guid']]))
                    $menu = Util::buildPosFixMenu($worldPos[$s['guid']]['mapId'], $worldPos[$s['guid']]['posX'], $worldPos[$s['guid']]['posY'], $s['type'], $s['guid'], $s['areaId'], $s['floor']);

                if ($menu)
                    $footer = '<br /><span class="q2">Click to move pin</span>';
            }

            /* recognized opts
             * url:     string - makes pin clickable
             * tooltip: array  - title => [info: <arr>lines, footer: <string>line]
             * label:   string - single line tooltip (skipped if 'tooltip' is set)
             * menu:    array  - menu definiton (conflicts with url)
             * type:    int    - colors the pip [default, green, red, blue, purple]
             * lines:   array  - [[destX, destY]] - draws line from point to dest
             */

            if ($info)
                $tt['info'] = $info;

            if ($footer)
                $tt['footer'] = $footer;

            if ($tt && $this->getEntry($s['typeId']))
                $opts['tooltip'] = [$this->getField('name', true) => $tt];
            else if ($hasLabel && $this->getEntry($s['typeId']))
                $opts['label'] = $this->getField('name', true);

            if ($hasLink)
                $opts['url'] = '?' . Type::getFileString(self::$type) . '=' . $s['typeId'];

            if ($menu)
                $opts['menu'] = $menu;

            $data[$s['areaId']] [$s['floor']] ['coords'] [] = [$s['posX'], $s['posY'], $opts];
        }
        foreach ($data as $a => &$areas)
            foreach ($areas as $f => &$floor)
                $floor['count'] = count($floor['coords']) - ($wpSum[$a][$f] ?? 0);

        uasort($data, [$this, 'sortBySpawnCount']);
        $this->spawnResult[SPAWNINFO_FULL] = $data;
    }

    private function sortBySpawnCount(array $a, array $b) : int
    {
        $aCount = current($a)['count'];
        $bCount = current($b)['count'];

        return $bCount <=> $aCount;                         // sort descending
    }

    private function createZoneSpawns() : void              // [zoneId1, zoneId2, ..]             for locations-column in listview
    {
        $res = DB::Aowow()->selectCol("SELECT `typeId` AS ARRAY_KEY, GROUP_CONCAT(DISTINCT `areaId`) FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) AND `posX` > 0 AND `posY` > 0 GROUP BY `typeId`", self::$type, $this->getfoundIDs());
        foreach ($res as &$r)
        {
            $r = explode(',', $r);
            if (count($r) > 3)
                array_splice($r, 3, count($r), -1);
        }

        $this->spawnResult[SPAWNINFO_ZONES] = $res;
    }

    private function createQuestSpawns() :void              // [zoneId => [floor => [[x1, y1], [x2, y2], ..]]]      mapper on quest detail page
    {
        if (self::$type == Type::SOUND)
            return;

        $res    = DB::Aowow()->select('SELECT `areaId`, `floor`, `typeId`, `posX`, `posY` FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) AND `posX` > 0 AND `posY` > 0', self::$type, $this->getFoundIDs());
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

    public function getSpawns(int $mode, bool ...$info) : array|\StdClass
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
                    $this->createFullSpawns(...$info);

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
    public static $brickFile = 'profile';                   // profile is multipurpose

    private static $subjectGUID = 0;

    public function selectRealms(?array $fi) : bool
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
    protected  array $sources    = [];
    protected ?array $sourceMore = null;

    public function getRawSource(int $src) : array
    {
        return $this->sources[$this->id][$src] ?? [];
    }

    public function getSources(?array &$s = [], ?array &$sm = []) : bool
    {
        $s = $sm = [];
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
                $this->sourceMore[$type] = Type::newList($type, [['id', $ids]]);
        }

        $s = array_keys($this->sources[$this->id]);
        if ($this->curTpl['moreType'] && $this->curTpl['moreTypeId'] && ($srcData = $this->sourceMore[$this->curTpl['moreType']]->getSourceData($this->curTpl['moreTypeId'])))
            $sm = $srcData[$this->curTpl['moreTypeId']];
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
            if ($this->curTpl['moreMask'] & SRC_FLAG_COMMON)
                $sm['dd'] = 99;
            else if ($this->curTpl['moreMask'] & SRC_FLAG_DUNGEON_DROP)
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

?>
