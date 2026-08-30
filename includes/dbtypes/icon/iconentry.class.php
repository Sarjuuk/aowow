<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconEntry extends DBTypeEntry
{
    public readonly int    $cuFlags;
    /** @var string $name - fixed filename */
    public readonly string $name;
    /** @var string $name - original filename; may contain spaces and accents */
    public readonly string $name_source;

    public private(set) ?int $nItems = null {
        get => $this->nItems ?? ($this->setIconCounts() ?? $this->nItems);
    }
    public private(set) ?int $nSpells = null {
        get => $this->nSpells ?? ($this->setIconCounts() ?? $this->nSpells);
    }
    public private(set) ?int $nAchievements = null {
        get => $this->nAchievements ?? ($this->setIconCounts() ?? $this->nAchievements);
    }
    public private(set) ?int $nNpcs = null {                // UNUSED battle pet
        get => $this->nNpcs ?? ($this->setIconCounts() ?? $this->nNpcs);
    }
    public private(set) ?int $nPetabilities = null {        // UNUSED battle pet abilities
        get => $this->nPetabilities ?? ($this->setIconCounts() ?? $this->nPetabilities);
    }
    public private(set) ?int $nCurrencies = null {
        get => $this->nCurrencies ?? ($this->setIconCounts() ?? $this->nCurrencies);
    }
    public private(set) ?int $nMissionabilities = null {    // UNUSED garrison missions
        get => $this->nMissionabilities ?? ($this->setIconCounts() ?? $this->nMissionabilities);
    }
    public private(set) ?int $nBuildings = null {           // UNUSED garrison buildings
        get => $this->nBuildings ?? ($this->setIconCounts() ?? $this->nBuildings);
    }
    public private(set) ?int $nPets = null {
        get => $this->nPets ?? ($this->setIconCounts() ?? $this->nPets);
    }
    public private(set) ?int $nThreats = null {             // UNUSED garrison threats
        get => $this->nThreats ?? ($this->setIconCounts() ?? $this->nThreats);
    }
    public private(set) ?int $nClasses = null {
        get => $this->nClasses ?? ($this->setIconCounts() ?? $this->nClasses);
    }

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

    public const string QUERY_BASE = 'SELECT ic.*, ic.`id` AS ARRAY_KEY FROM ::icons ic';
 /* this works, but takes ~100x more time than i'm comfortable with .. kept as reference
  * public const array  QUERY_OPTS = array(
  *     'ic' => [['s', 'i', 'a', 'c', 'p'], 'g' => 'ic.id'],
  *     'i'  => ['j' => ['::items `i`  ON `i`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `i`.`id`) AS "nItems"'],
  *     's'  => ['j' => ['::spell `s`  ON `s`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `s`.`id`) AS "nSpells"'],
  *     'a'  => ['j' => ['::achievement `a`  ON `a`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `a`.`id`) AS "nAchievements"'],
  *     'c'  => ['j' => ['::currencies `c`  ON `c`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `c`.`id`) AS "nCurrencies"'],
  *     'p'  => ['j' => ['::pet `p`  ON `p`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `p`.`id`) AS "nPets"']
  * );
  */

    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags     = $initData['cuFlags'];
        $this->name        = $initData['name'];
        $this->name_source = $initData['name_source'];

        return true;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
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

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name,                          // use unaltered name_source?
            'icon' => $this->name
        )]];
    }

    public function setIconCounts(?array $iconCounts = null) : void
    {
        $iconCounts ??= current(self::fetchIconCounts($this->id));

        $this->nItems        = $iconCounts['nItems']        ?? 0;
        $this->nSpells       = $iconCounts['nSpells']       ?? 0;
        $this->nAchievements = $iconCounts['nAchievements'] ?? 0;
        $this->nCurrencies   = $iconCounts['nCurrencies']   ?? 0;
        $this->nPets         = $iconCounts['nPets']         ?? 0;
        $this->nClasses      = $iconCounts['nClasses']      ?? 0;

        // not in 3.3.5a
        $this->nNpcs             = 0;
        $this->nPetabilities     = 0;
        $this->nMissionabilities = 0;
        $this->nBuildings        = 0;
        $this->nThreats          = 0;
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
