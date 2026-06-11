<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


interface ITooltip
{
    public function renderTooltip() : ?string;
}

interface ISource
{
    public function getRawSource(int $src) : array;
    public function getSources() : ?array;
    public function getSourceData(/* int $id = 0 */) : array;
    public function hasAnySource() : bool;
}

/* source more: keys seen used
     'n':   name [always set]
     't':   type [always set]
    'ti':   typeId [always set]
    'bd':   BossDrop [0; 1] [Creature / GO]
    'dd':   DungeonDifficulty [-2: DungeonHC; -1: DungeonNM; 1: Raid10NM; 2:Raid25NM; 3:Raid10HM; 4: Raid25HM; 99: filler trash] [Creature / GO]
     'q':   cssQuality [Items]
     'z':   zone [set when everything happens in one zone]
     'p':   PvP [pvpSourceId]
     's':   Type::TITLE: side; Type::SPELL: skillId (yeah, conflicting use cases. Ain't life just grand.)
     'c':   category [Spells / Quests]
    'c2':   subCat [Quests]
  'icon':   iconString
*/
trait TrSourceHelper
{
    public readonly  array $sources;
    public readonly ?int   $moreType;
    public readonly ?int   $moreTypeId;
    public readonly ?int   $moreZoneId;
    public readonly ?int   $moreMask;                       // srcFlags

    // the result for js
    public  ?array $source     = null;                      // NOTE! set public because of titles. Never used for anything else? Why cant ttles be handled like the rest? todo: investigate
    private ?array $sourceMore = null;

    /**
     * titles are special and can have an additional achievement source (12) stored in titles table
     */
    private function initSources(array &$initData, int $extSrc12 = 0) : void
    {
        $src = [];
        for ($i = SRC_CRAFTED; $i < MAX_SOURCES; $i++)
        {
            if ($_ = ($initData['src'.$i] ?? null))
                $src[$i][] = $_;

            unset($initData['src'.$i]);
        }

        if ($extSrc12)
            $src[SRC_ACHIEVEMENT][] = $extSrc12;

        $this->sources = $src;

        $this->moreType   = $initData['moreType'];
        $this->moreTypeId = $initData['moreTypeId'];
        $this->moreZoneId = $initData['moreZoneId'];
        $this->moreMask   = $initData['moreMask'];

        unset($initData['moreType'], $initData['moreTypeId'], $initData['moreZoneId'], $initData['moreMask']);
    }

    public function getRawSource(int $src) : array
    {
        return $this->sources[$src] ?? [];
    }

    public function hasAnySource() : bool
    {
        return !!array_filter($this->sources);
    }

    public function prepareSourceMore(?DBTypeEntry $src = null) : void
    {
        if (!$this->sourceMore !== null || !($this instanceof DBTypeEntry))
            return;

        // not provided by external bulk operation
        if (!$src && $this->moreType && $this->moreTypeId)
            $src = Type::newEntry($this->moreType, $this->moreTypeId);

        if ($src && $src instanceof ISource && $src->id == $this->id)
            $this->sourceMore = $src->getSourceData();
        else
            $this->sourceMore = [];
    }

    public function getSources() : ?array
    {
        if (empty($this->sources))
            return null;

        if ($this->sourceMore === null)
            $this->prepareSourceMore();

        $s  = array_keys($this->sources);
        $sm = [];

        if ($_ = $this->sourceMore)
            $sm = $_;

        if (!empty($this->sources[SRC_PVP]))
            $sm['p'] = $this->sources[SRC_PVP][0];

        if ($z = $this->moreZoneId)
            $sm['z'] = $z;

        if ($this->moreMask & SRC_FLAG_BOSSDROP)
            $sm['bd'] = 1;

        if (!empty($this->sources[SRC_DROP]))
        {
            /*
                mode        srcFlag     log2    dd Flag
                10N/D-NH    0b0001      0       0b001
                25N/D-HC    0b0010      1       0b010
                10H         0b0100      2       0b011
                25H         0b1000      3       0b100
            */
            if ($this->moreMask & SRC_FLAG_COMMON)
                $sm['dd'] = 99;
            else if ($this->moreMask & SRC_FLAG_DUNGEON_DROP)
                $sm['dd'] = $this->sources[SRC_DROP][0] * -1;
            else if ($this->moreMask & SRC_FLAG_RAID_DROP)
            {
                $dd = log($this->sources[SRC_DROP][0], 2);
                if ($dd == intVal($dd))                     // only one bit set
                    $sm['dd'] = $dd + 1;
            }
        }

        return [$s, $sm ? [$sm] : []];
    }
}

interface IListview
{
    // should return data required to display an arbitrary listview
    public function getListviewData() : array;
    // test if a property is in use
    public function hasSetFields(?string ...$fields) : int;
    // test if a property is in use and different between rows
    public function hasDiffFields(?string ...$fields) : int;
}


/*
    !IMPORTANT!
    It is flat out impossible to distinguish between floors for multi-level areas, if the floors overlap each other!
    The coordinates generated by the script WILL be on every level and will have to be removed MANUALLY!

    impossible := you are not keen on reading wmo data and match it to DungeonMapChunk.dbc;
*/
/** set of spawnpoint related aggregators */
trait TrSpawns
{
    /**
     * for locations-column in listview
     *
     * @param DBTypeContainer $set limited to:
     * * `CreatureSet`
     * * `GameobjectSet`
     * @return array [zoneId1, zoneId2, ..] up to three. If more, then `-1` indicates omitted zones
     */
    public static function createZoneSpawns(GameobjectContainer|CreatureContainer $set) : array
    {
        $result = DB::Aowow()->selectCol(
           'SELECT `typeId` AS ARRAY_KEY, GROUP_CONCAT(`areaId` ORDER BY `n` DESC)
            FROM (SELECT `typeId`, `areaId`, COUNT(1) AS "n" FROM ::spawns WHERE `type` = %i AND `typeId` IN %in AND `posX` > 0 AND `posY` > 0 GROUP BY `typeId`, `areaId`) x
            GROUP BY `typeId`',
            self::$dbType, $set->getfoundIDs()
        ) ?: [];

        foreach ($result as &$r)
            $r = explode(',', $r);

        return $result;
    }
}

trait TrProfilerHelper
{
    public static $brickFile = 'profile';                   // profile is multipurpose

    private int $subjectGUID = 0;
    private int $realmId     = 0;
    private int $realmGUID   = 0;

    // sooo subjectGUID cant' be used as $id, because it's not unique across realms <realmId>:<subjectGUID>
    // so we pack them .. assumes PHP_INT_SIZE == 8 / an x64 system
    public function packId() : int
    {
        return ($this->realmId & 0xFFFF) << 40 | ($this->realmGUID & 0xFFFFFFFFFF);
    }

    public function unpackId(int $packedId) : void
    {
        $this->realmId   = ($packedId >> 40) & 0xFFFF;
        $this->realmGUID =  $packedId        & 0xFFFFFFFFFF;
    }

    public static function getRealmDBs(?array $fi) : array
    {
        $dbNames = [];

        foreach(Profiler::getRealms() as $idx => $r)
        {
            if (!empty($fi['sv']) && Profiler::urlize($r['name']) != Profiler::urlize($fi['sv']) && intVal($fi['sv']) != $idx)
                continue;

            if (!empty($fi['rg']) && Profiler::urlize($r['region']) != Profiler::urlize($fi['rg']))
                continue;

            $dbNames[$idx] = 'characters';
        }

        return $dbNames;
    }
}


abstract class DBTypeEntry
{
    protected array  $templates = [];
    protected array  $curTpl    = [];
    protected int    $matches   = 0;                        // total matches unaffected by sqlLimit in config

    public static   int $dbType;
    public readonly int $id;

    public static int    $contribute = CONTRIBUTE_ANY;
    public static string $dataTable;
    public        bool   $error      = true;

    public const /* string */ QUERY_BASE = '';
    public const /* array */  QUERY_OPTS = [];

    public function __construct(
                  int|array $initData,
        protected array     $extraOpts = []
    )
    {
        if (is_int($initData))
        {
            $dbQuery = new DBQuery(static::QUERY_BASE, static::QUERY_OPTS, $this->extraOpts);
            if (!$dbQuery->build([['id', $initData]]))
                return;

            foreach ($dbQuery->fetch() as $data)
            {
                $this->applyInitData($data);
                $this->error = false;
                break;                                      // should only ever be one row
            }

            return;
        }

        if ($initData)
        {
            $this->applyInitData($initData);
            $this->error = false;
        }
    }

    // readonly props may only be written like they were private; so force per DBTypeEntry implementation
    public function applyInitData(array $initData) : void
    {
        $this->id = $initData['id'];
        // cuFlags also here..?
    }

    public static function getName(int $id) : ?LocString
    {
        if (!$id)
            return null;

        if ($n = DB::Aowow()->SelectRow('SELECT `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM %n WHERE `id` = %i', static::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public static function makeLink(int $id, int $fmt = Lang::FMT_HTML, string $cssClass = '') : string
    {
        if ($n = static::getName($id))
            return Lang::makeLink(static::$dbType, $id, $n, $fmt, $cssClass);
        return '';
    }

    /** should return data to extend global js variables for a certain type (e.g. g_items) */
    abstract public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array;

    /** should return data for a single listview row */
    abstract public function getListviewRow(int $addInfoMask = 0x0) : array;
}

?>
