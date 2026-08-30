<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


/** signifies implementing DBTypeEntry can render a tooltip */
interface ITooltip
{
    public function renderTooltip() : ?string;
}

/** signifies implementing DBTypeEntry can serve as source */
interface ISource
{
    public function getSourceData() : array;                // not used. You never need the SourceMore from an already loaded container.
    public static function getSourceMore(int ...$ids) : array;
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
                $src[$i] = $_;

            unset($initData['src'.$i]);
        }

        if ($extSrc12)
            $src[SRC_ACHIEVEMENT] = 1;

        $this->sources = $src;

        $this->moreType   = $initData['moreType']   ?? ($extSrc12 ? Type::ACHIEVEMENT : null);
        $this->moreTypeId = $initData['moreTypeId'] ?? ($extSrc12 ?: null);
        $this->moreZoneId = $initData['moreZoneId'] ?? null;
        $this->moreMask   = $initData['moreMask']   ?? null;

        unset($initData['moreType'], $initData['moreTypeId'], $initData['moreZoneId'], $initData['moreMask']);
    }

    public function getRawSource(int $src) : ?int
    {
        return $this->sources[$src] ?? null;
    }

    public function hasAnySource() : bool
    {
        return !!array_filter($this->sources);
    }

    public function prepareSourceMore(?array $src = null) : void
    {
        if ($this->sourceMore !== null || !($this instanceof DBTypeEntry))
            return;

        if (!$this->moreType || !$this->moreTypeId)
            return;

        if (is_array($src))
        {
            $this->sourceMore = $src;
            return;
        }

        // not provided by external bulk operation
        if ($class = Type::getClassName($this->moreType))
            if (is_a($class, ISource::class, true))
                $this->sourceMore = $class::getSourceMore($this->moreTypeId);

        // tag as prepped and ampty on failure
        $this->sourceMore ??= [];
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

        if ($p = $this->getRawSource(SRC_PVP))
            $sm['p'] = $p;

        if ($z = $this->moreZoneId)
            $sm['z'] = $z;

        if ($this->moreMask & SRC_FLAG_BOSSDROP)
            $sm['bd'] = 1;

        if ($drop = $this->getRawSource(SRC_DROP))
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
                $sm['dd'] = $drop * -1;
            else if ($this->moreMask & SRC_FLAG_RAID_DROP)
                if (($drop & ($drop - 1)) == 0)             // only one bit set
                    $sm['dd'] = log($drop, 2) + 1;
        }

        return [$s, $sm ? [$sm] : []];
    }
}

interface IProfiler
{
    public function packId(int $realmId, int $realmGUID) : int;
    public function unpackId(int $packedId) : array;
    public static function getRealmDBs(?array $fi) : array;
}

trait TrProfilerHelper
{
    public static string $brickFile = 'profile';            // profile is multipurpose

    public readonly int    $subjectGUID;
    public readonly int    $realmId;
    public readonly int    $realmGUID;
    public readonly string $realmName;

    public readonly string $region;
 // public readonly string $battlegroup;

    // sooo subjectGUID cant' be used as $id, because it's not unique across realms <realmId>:<subjectGUID>
    // so we pack them .. assumes PHP_INT_SIZE == 8 / an x64 system
    public function packId(int $realmId, int $realmGUID) : int
    {
        // return ($this->realmId & 0xFFFF) << 40 | ($this->realmGUID & 0xFFFFFFFFFF);
        return ($realmId & 0xFFFF) << 40 | ($realmGUID & 0xFFFFFFFFFF);
    }

    public function unpackId(int $packedId) : array
    {
        // $this->realmId   = ($packedId >> 40) & 0xFFFF;
        // $this->realmGUID =  $packedId        & 0xFFFFFFFFFF;
        return [
            ($packedId >> 40) & 0xFFFF,
             $packedId        & 0xFFFFFFFFFF
        ];
    }

    public static function getRealmDBs(?array $fi) : array
    {
        $dbNames = [];

        foreach (Profiler::getRealms() as $idx => $r)
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

trait TrVendorHelper
{
    private  array $jsGlobals = [];
    private ?array $vendors   = null;

    // todo (med): information will get lost if one vendor sells one item multiple times with different costs (e.g. for item 54637)
    //             wowhead seems to have had the same issue
    public function getExtendedCost(int $itemId, array $filter = [], ?array &$reqRating = null) : array
    {
        // apply filter if given
        $tok = $filter[Type::ITEM]     ?? null;
        $cur = $filter[Type::CURRENCY] ?? null;
        $npc = $filter[Type::NPC]      ?? null;
        $res = [];

        if (!($vendors = $this->getVendorData([$itemId])))
            return [];

        foreach (array_filter($vendors[$itemId], fn($x) => !$npc || $x == $npc, ARRAY_FILTER_USE_KEY) as $npcId => $costEntries)
        {
            foreach ($costEntries as $cost)
            {
                // bought with specific token or currency
                if ($tok && empty($cost[$tok]))
                    continue;
                if ($cur && empty($cost[-$cur]))
                    continue;

                // use lowest total value for arena rating
                if ($cost['reqRating'])
                    if (!$reqRating || $reqRating[0] > $cost['reqRating'])
                        $reqRating = [$cost['reqRating'], $cost['reqBracket']];

                $res[$npcId] ??= [];
                $res[$npcId][] = $cost;
            }
        }

        return $res;
    }

    public function getVendorData(array $itemIds) : array
    {
        return $this->vendors ??= (function(array $itemIds)
        {
            $vendorResult =
            $xCostData    =
            $currencyData =
            $itemData     = [];

            $rawEntries = DB::World()->selectAssoc(
               'SELECT    nv.`item`,          nv.`entry`,              0  AS "eventId",    nv.`maxcount`,   nv.`extendedCost`,   nv.`incrtime`
                FROM      npc_vendor nv
                WHERE     nv.`item` IN %in
                    UNION
                SELECT    nv2.`item`,        nv1.`entry`,              0  AS "eventId",   nv2.`maxcount`,  nv2.`extendedCost`,  nv2.`incrtime`
                FROM      npc_vendor   nv1
                JOIN      npc_vendor   nv2 ON -nv1.`item` = nv2.`entry`
                WHERE     nv2.`item` IN %in
                    UNION
                SELECT    genv.`item`, c.`id` AS "entry", ge.`eventEntry` AS "eventId",  genv.`maxcount`, genv.`extendedCost`, genv.`incrtime`
                FROM      game_event_npc_vendor genv
                LEFT JOIN game_event ge ON genv.`eventEntry` = ge.`eventEntry`
                JOIN      creature c ON c.`guid` = genv.`guid`
                WHERE     genv.`item` IN %in',
                $itemIds, $itemIds, $itemIds
            );

            if (!$rawEntries)
                return [];

            if ($xCostIds = array_filter(array_column($rawEntries, 'extendedCost')))
                $xCostData = DB::Aowow()->selectAssoc('SELECT *, `id` AS ARRAY_KEY FROM ::itemextendedcost WHERE `id` IN %in', $xCostIds);

            $itemData = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `buyPrice` AS "0", IF(`flagsExtra` & 0x4, 1, 0) AS "1" FROM ::items WHERE `id` IN %in', array_column($rawEntries, 'item'));

            // don't fuss with item ids. there are only like 50 currencies in total
            if ($xCostData)
                $currencyData = DB::Aowow()->selectCol('SELECT `itemId` AS ARRAY_KEY, `id` FROM ::currencies');

            if (array_filter(array_column($xCostData, 'reqArenaPoints')))
                $this->jsGlobals[Type::CURRENCY][CURRENCY_ARENA_POINTS] = CURRENCY_ARENA_POINTS;
            if (array_filter(array_column($xCostData, 'reqHonorPoints')))
                $this->jsGlobals[Type::CURRENCY][CURRENCY_HONOR_POINTS] = CURRENCY_HONOR_POINTS;

            foreach ($rawEntries as $vendor)
            {
                $xCost = $xCostData[$vendor['extendedCost']] ?? [];
                $data  = array(
                    'stock'      => $vendor['maxcount'] ?: -1,
                    'event'      => $vendor['eventId'],
                    'restock'    => $vendor['incrtime'],
                    'reqRating'  => $xCost['reqPersonalRating'] ?? 0,
                    'reqBracket' => $xCost['reqArenaSlot']      ?? 0
                );

                // hardcode arena) & honor
                if (!empty($xCost['reqArenaPoints']))
                    $data[-CURRENCY_ARENA_POINTS] = $xCost['reqArenaPoints'];

                if (!empty($xCost['reqHonorPoints']))
                    $data[-CURRENCY_HONOR_POINTS] = $xCost['reqHonorPoints'];

                if ($xCost)
                {
                    for ($i = 1; $i < 6; $i++)
                    {
                        if (!$xCost['reqItemId'.$i]|| !$xCost['itemCount'.$i])
                            continue;

                        // convert items to currency if possible
                        $curr   = $currencyData[$xCost['reqItemId'.$i]] ?? null;
                        $typeId = $curr ?: $xCost['reqItemId'.$i];

                        $data[$curr ? -$typeId : $typeId] = $xCost['itemCount'.$i];
                        $this->jsGlobals[$curr ? Type::CURRENCY : Type::ITEM][$typeId] = $typeId;
                    }
                }

                // no extended cost or additional gold required
                if (!$xCost || $itemData[$vendor['item']][1])
                    if ($_ = $itemData[$vendor['item']][0])
                        $data[0] = $_;

                $vendorResult[$vendor['item']][$vendor['entry']] ??= [];
                $vendorResult[$vendor['item']][$vendor['entry']][] = $data;
            }

            return $vendorResult;

        })($itemIds);
    }

    /** for bulk apply from Container */
    public function setVendors(array $vendors) : void
    {
        $this->vendors = $vendors;
    }
}


abstract class DBTypeEntry
{
    protected array  $templates = [];
    protected int    $matches   = 0;                        // total matches unaffected by sqlLimit in config

    public static   int $dbType;
    public readonly int $id;

    public static int    $contribute = CONTRIBUTE_ANY;
    public static string $dataTable;
    public        bool   $error      = true;

    public const string QUERY_BASE = '';
    public const array  QUERY_OPTS = [];

    public function __construct(int|array $initData, array $extraOpts = [], array $targetDBs = ['Aowow'])
    {
        if (is_array($initData))
        {
            if ($this->applyInitData($initData, $extraOpts['apply'] ?? []))
                $this->error = false;

            return;
        }

        $dbQuery = new DBQuery($targetDBs, static::QUERY_BASE, static::QUERY_OPTS, $extraOpts['queryOpts'] ?? []);
        if (!$dbQuery->build([['id', $initData]]))
            return;

        foreach ($dbQuery->fetch() as $data)
        {
            $this->applyInitData($data, $extraOpts['apply'] ?? []);
            $this->error = false;
            break;                                      // should only ever be one row
        }
    }

    // readonly props may only be written like they were private; so force per DBTypeEntry implementation
    public function applyInitData(array $initData, array $opts) : void
    {
        $this->id = $initData['id'];
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
    abstract public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array;

    /** should return data for a single listview row */
    abstract public function getListviewRow(int $addInfoMask = 0x0) : array;
}

?>
