<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Icon extends DBType
{
    public readonly int    $cuFlags;
    /** @var string $name - fixed filename */
    public readonly string $name;
    /** @var string $name - original filename; may contain spaces and accents */
    public readonly string $name_source;
    public readonly int    $nItems;
    public readonly int    $nSpells;
    public readonly int    $nAchievements;
    public readonly int    $nNpcs;                          // UNUSED battle pet
    public readonly int    $nPetabilities;                  // UNUSED battle pet abilities
    public readonly int    $nCurrencies;
    public readonly int    $nMissionabilities;              // UNUSED garrison missions
    public readonly int    $nBuildings;                     // UNUSED garrison buildings
    public readonly int    $nPets;
    public readonly int    $nThreats;                       // UNUSED garrison threats
    public readonly int    $nClasses;

    private bool $countsInited = false;

    public static int    $dbType     = Type::ICON;
    public static string $brickFile  = 'icongallery';
    public static string $dataTable  = '::icons';
    public static int    $contribute = CONTRIBUTE_CO;

    private static string $pseudoQry  = 'SELECT `iconId` AS ARRAY_KEY, COUNT(*) FROM %n WHERE `iconId` IN %in GROUP BY `iconId`';
    private static array  $pseudoJoin = array(
        'nItems'        => '::items',
        'nSpells'       => '::spell',
        'nAchievements' => '::achievement',
        'nCurrencies'   => '::currencies',
        'nPets'         => '::pet',
        'nClasses'      => '::classes'
    );

    public const /* string */ QUERY_BASE = 'SELECT ic.*, ic.`id` AS ARRAY_KEY FROM ::icons ic';
 /* this works, but takes ~100x more time than i'm comfortable with .. kept as reference
  * public const // array  // QUERY_OPTS = array(
  *     'ic' => [['s', 'i', 'a', 'c', 'p'], 'g' => 'ic.id'],
  *     'i'  => ['j' => ['::items `i`  ON `i`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `i`.`id`) AS "nItems"'],
  *     's'  => ['j' => ['::spell `s`  ON `s`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `s`.`id`) AS "nSpells"'],
  *     'a'  => ['j' => ['::achievement `a`  ON `a`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `a`.`id`) AS "nAchievements"'],
  *     'c'  => ['j' => ['::currencies `c`  ON `c`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `c`.`id`) AS "nCurrencies"'],
  *     'p'  => ['j' => ['::pet `p`  ON `p`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `p`.`id`) AS "nPets"']
  * );
  */

    public function __construct(int|array $initData, array $extraOpts = [])
    {
        // set unused data fields
        $this->nNpcs =
        $this->nPetabilities =
        $this->nMissionabilities =
        $this->nBuildings =
        $this->nThreats = 0;

        parent::__construct($initData, $extraOpts);
    }

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        if (!$this->countsInited)
            $this->setIconCounts();

        return array(
            'id'                  => $this->id,
            'name'                => $this->name_source,
            'icon'                => $this->name,
            'itemcount'           => $this->nItems,
            'spellcount'          => $this->nSpells,
            'achievementcount'    => $this->nAchievements,
            'npccount'            => $this->nNpcs,
            'petabilitycount'     => $this->nPetabilities,
            'currencycount'       => $this->nCurrencies,
            'missionabilitycount' => $this->nMissionabilities,
            'buildingcount'       => $this->nBuildings,
            'petcount'            => $this->nPets,
            'threatcount'         => $this->nPets,
            'classcount'          => $this->nClasses
        );
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name,                          // use unaltered name_source?
            'icon' => $this->name
        )]];
    }

    public function renderTooltip() : ?string { return null; }

    public function setIconCounts(?array $iconCounts = null) : void
    {
        if ($this->countsInited)
            return;

        if ($iconCounts === null)
            $iconCounts = self::fetchIconCounts($this->id)[$this->id] ?? [];

        $this->nItems        = $iconCounts['nItems']        ?? 0;
        $this->nSpells       = $iconCounts['nSpells']       ?? 0;
        $this->nAchievements = $iconCounts['nAchievements'] ?? 0;
        $this->nCurrencies   = $iconCounts['nCurrencies']   ?? 0;
        $this->nPets         = $iconCounts['nPets']         ?? 0;
        $this->nClasses      = $iconCounts['nClasses']      ?? 0;

        $this->countsInited  = true;
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->selectRow('SELECT `name` AS "name_loc0" FROM %n WHERE `id` = %i', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public static function fetchIconCounts(int ...$ids) : array
    {
        if (!$ids)
            return [];

        $result = [];
        foreach (self::$pseudoJoin as $var => $tbl)
        {
            $res = DB::Aowow()->selectCol(self::$pseudoQry, $tbl, $ids);
            foreach ($res as $icon => $qty)
                $result[$icon][$var] = $qty;
        }

        return $result;
    }
}

?>
