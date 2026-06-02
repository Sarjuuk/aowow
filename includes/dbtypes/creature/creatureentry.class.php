<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly  LocString $name;
    public readonly  LocString $subname;
    public readonly  int       $family;
    public readonly  int       $minLevel;
    public readonly  int       $maxLevel;
    public readonly  int       $rank;
    public readonly  int       $type;
    public readonly  int       $typeFlags;
    public readonly  int       $npcflag;
    public readonly  int       $flagsExtra;
    public readonly  int       $vehicleId;
    public readonly  bool      $humanoid;
    public readonly ?string    $ScriptOrAI;
    public readonly ?string    $StringId;
    /** @var int[] $killCredit */
    public readonly  array     $killCredit;
    // worth
    public readonly  int       $minGold;
    public readonly  int       $maxGold;
    public readonly  int       $lootId;
    public readonly  int       $skinLootId;
    public readonly  int       $pickpocketLootId;
    // display
    public readonly  int       $modelId;
    public readonly ?string    $textureString;
    /** @var int[] $displayIds */
    public readonly  array     $displayIds;
    // stats
    public readonly  int       $healthMin;
    public readonly  int       $healthMax;
    public readonly  int       $manaMin;
    public readonly  int       $manaMax;
    public readonly  int       $armorMin;
    public readonly  int       $armorMax;
    public readonly  int       $dmgMin;
    public readonly  int       $dmgMax;
    public readonly  int       $mleAtkPwrMin;
    public readonly  int       $mleAtkPwrMax;
    public readonly  float     $mleVariance;
    public readonly  float     $atkSpeed;
    public readonly  int       $rngAtkPwrMin;
    public readonly  int       $rngAtkPwrMax;
    public readonly  float     $rngVariance;
    public readonly  float     $rngAtkSpeed;
    public readonly  int       $dmgMultiplier;
    public readonly  int       $dmgSchool;
    /** @var array{int, int} $resistances */
    public readonly  array     $resistances;
    public readonly  int       $schoolImmuneMask;
    public readonly  int       $mechanicImmuneMask;
    /** @var int[] $spells spell ids */
    public readonly  array     $spells;
    // difficulty versioning
    public readonly  int       $parentId;                   // the orignal id if difficulty dummy
    public readonly  LocString $parent;                     // the orignal name if difficulty dummy
    /** @var int[] $difficultyEntries */
    public readonly  array     $difficultyEntries;
    /** @var int $faction - factiontemplate.dbc/id */
    public readonly  int       $faction;
    // ::faction
    /** @var int $factionId - faction.dbc/id */
    public readonly  int       $factionId;
    public readonly  int       $A;                          // react to alliance: -1: hostile, 0: neutral, 1: friendly
    public readonly  int       $H;                          // react to horde: -1: hostile, 0: neutral, 1: friendly
    // ::quest_startend
    public readonly  bool      $startsQuests;
 // public readonly  bool      $endsQuests;                 // only defined for filtering

    public static int    $dbType    = Type::NPC;
    public static string $brickFile = 'npc';
    public static string $dataTable = '::creature';

    public const /* string */ QUERY_BASE = 'SELECT ct.*, ct.`id` AS ARRAY_KEY FROM ::creature ct';
    public const /* array */  QUERY_OPTS = array(
        'ct'   => [['ft', 'qse', 'dct1', 'dct2', 'dct3'], 's' => ', IFNULL(dct1.`id`, IFNULL(dct2.`id`, IFNULL(dct3.`id`, 0))) AS "parentId", IFNULL(dct1.`name_loc0`, IFNULL(dct2.`name_loc0`, IFNULL(dct3.`name_loc0`, ""))) AS "parent_loc0", IFNULL(dct1.`name_loc2`, IFNULL(dct2.`name_loc2`, IFNULL(dct3.`name_loc2`, ""))) AS "parent_loc2", IFNULL(dct1.`name_loc3`, IFNULL(dct2.`name_loc3`, IFNULL(dct3.`name_loc3`, ""))) AS "parent_loc3", IFNULL(dct1.`name_loc4`, IFNULL(dct2.`name_loc4`, IFNULL(dct3.`name_loc4`, ""))) AS "parent_loc4", IFNULL(dct1.`name_loc6`, IFNULL(dct2.`name_loc6`, IFNULL(dct3.`name_loc6`, ""))) AS "parent_loc6", IFNULL(dct1.name_loc8, IFNULL(dct2.`name_loc8`, IFNULL(dct3.`name_loc8`, ""))) AS "parent_loc8", IF(dct1.`difficultyEntry1` = ct.`id`, 1, IF(dct2.`difficultyEntry2` = ct.`id`, 2, IF(dct3.`difficultyEntry3` = ct.`id`, 3, 0))) AS "difficultyMode"'],
        'nml'  => ['j' => ['::creature_search nml ON nml.`id` = ct.`id` AND nml.`locale` = DB_LOC_I']],
        'dct1' => ['j' => ['::creature dct1 ON ct.`cuFlags` & 0x02 AND dct1.`difficultyEntry1` = ct.`id`', true]],
        'dct2' => ['j' => ['::creature dct2 ON ct.`cuFlags` & 0x02 AND dct2.`difficultyEntry2` = ct.`id`', true]],
        'dct3' => ['j' => ['::creature dct3 ON ct.`cuFlags` & 0x02 AND dct3.`difficultyEntry3` = ct.`id`', true]],
        'ft'   => ['j' => '::factiontemplate ft ON ft.`id` = ct.`faction`', 's' => ', ft.`factionId`, IFNULL(ft.`A`, 0) AS "A", IFNULL(ft.`H`, 0) AS "H"'],
        'qse'  => ['j' => ['::quests_startend qse ON qse.`type` = 1 AND qse.`typeId` = ct.id', true], 's' => ', IF(MIN(qse.`method`) = 1 OR MAX(qse.`method`) = 3, 1, 0) AS "startsQuests", IF(MIN(qse.`method`) = 2 OR MAX(qse.`method`) = 3, 1, 0) AS "endsQuests"', 'g' => 'ct.`id`'],
        'qt'   => ['j' => '::quests qt ON qse.`questId` = qt.`id`'],
        's'    => ['j' => ['::spawns s ON s.`type` = 1 AND s.`typeId` = ct.`id`', true]]
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name              = new LocString($initData, 'name',    pruneFromSrc: true);
        $this->subname           = new LocString($initData, 'subname', pruneFromSrc: true);
        $this->parent            = new LocString($initData, 'parent',  pruneFromSrc: true);
        $this->displayIds        = [$initData['displayId1'], $initData['displayId2'], $initData['displayId3'], $initData['displayId4']];
        $this->spells            = [$initData['spell1'], $initData['spell2'], $initData['spell3'], $initData['spell4'], $initData['spell5'], $initData['spell6'], $initData['spell7'], $initData['spell8']];
        $this->resistances       = [$initData['resistance1'], $initData['resistance2'], $initData['resistance3'], $initData['resistance4'], $initData['resistance5'], $initData['resistance6']];
        $this->killCredit        = [$initData['KillCredit1'], $initData['KillCredit2']];
        $this->difficultyEntries = [$initData['difficultyEntry1'], $initData['difficultyEntry2'], $initData['difficultyEntry3']];

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'atkSpeed':                            // check for attackspeeds
                case 'rngAtkSpeed':
                    $this->$k = $v ? 2.0 : $v / 1000;
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    /**
     * @param int $addInfoMask
     * * `0x0010 - LISTVIEWINFO_MODEL`:
     * * `0x0020 - LISTVIEWINFO_TAMEABLE`: include texture
     * * `0x0040 - LISTVIEWINFO_REPUTATION`: include repreward
     */
    public function getListviewRow(int $addInfoMask = 0x0, ?array $location = null) : array
    {
        if (count($location) > 3)
            array_splice($location, 3, replacement: -1);

        $data = array(
            'family'         => $this->family,
            'minlevel'       => $this->minLevel,
            'maxlevel'       => $this->maxLevel,
            'id'             => $this->id,
            'boss'           => $this->isBoss() ? 1 : 0,
            'classification' => $this->rank,
            'location'       => $location,
            'name'           => $this->name,
            'type'           => $this->type,
            'react'          => [$this->A, $this->H],
        );

        if ($this->startsQuests)
            $data['hasQuests'] = 1;

        if (!$this->subname->isEmpty())
            $data['tag'] = $this->subname;

        if ($addInfoMask & LISTVIEWINFO_TAMEABLE)           // only first skin of first model ... we're omitting potentially 11 skins here .. but the lv accepts only one .. w/e
            $data['skin'] = $this->textureString;

        if ($addInfoMask & LISTVIEWINFO_REPUTATION)
            $data['reprewards'] = [];                       // batch filled by caller

        return $data;
    }

    public function getModelListviewRow() : array
    {
        return array(
            'family'    => $this->family,
            'minLevel'  => $this->minLevel,
            'maxLevel'  => $this->maxLevel,
            'modelId'   => $this->modelId,
            'displayId' => array_find($this->displayIds, fn($x) => $x), // first non-zero
            'skin'      => $this->textureString,
            'count'     => 1
        );
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name
        )]];
    }

    public function getSourceData() : array
    {
        return array(
            'n'  => $this->parentId ? $this->parent : $this->name,
            't'  => Type::NPC,
            'ti' => $this->parentId ?: $this->id
        );
    }

    public function renderTooltip() : ?string
    {
        $level = '??';
        if (!($this->typeFlags & NPC_TYPEFLAG_BOSS_MOB))
            $level = Util::createNumRange($this->minLevel, $this->maxLevel);

        // the client omits type for friendly targets and type 'Not Specified'
        $type = $this->type;
        if (($this->A == 1 && $this->H == 1) || $type == NPC_TYPE_NOT_SPECIFIED)
            $type = NPC_TYPE_NONE;

        // 'rank' is also only ever displayed as 'Elite' or 'Boss'
        $rank = $this->rank;
        if ($rank == NPC_RANK_RARE_ELITE)
            $rank = NPC_RANK_ELITE;
        else if ($rank == NPC_RANK_RARE)
            $rank = NPC_RANK_NORMAL;

        if ($rank && !$type)
            $row3 = Lang::npc('level', 3, [$level, Lang::npc('rank', $rank)]); // TOOLTIP_UNIT_LEVEL_TYPE
        else if ($rank && $type)
            $row3 = Lang::npc('level', 2, [$level, Lang::game('ct', $type), Lang::npc('rank', $rank)]); // TOOLTIP_UNIT_LEVEL_CLASS_TYPE
        else if (!$rank && $type)
            $row3 = Lang::npc('level', 1, [$level, Lang::game('ct', $type)]); // TOOLTIP_UNIT_LEVEL_CLASS
        else
            $row3 = Lang::npc('level', 0, [$level]); // TOOLTIP_UNIT_LEVEL

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.Util::htmlEscape($this->name).'</b></td></tr>';

        if ($this->subname)
            $x .= '<tr><td>'.Util::htmlEscape($this->subname).'</td></tr>';

        $x .= '<tr><td>'.$row3.'</td></tr>';

        if ($this->type == NPC_TYPE_BEAST && $this->family)
            $x .= '<tr><td>'.Lang::game('fa', $this->family).'</td></tr>';

        $fac = new FactionEntry($this->factionId);
        if (!$fac->error && !($fac->cuFlags & CUSTOM_EXCLUDE_FOR_LISTVIEW))
            $x .= '<tr><td>'.$fac->name.'</td></tr>';

        $x .= '</table>';

        return $x;
    }

    public function getRandomDisplayId() : int
    {
        // dwarf?? [null, 30754, 30753, 30755, 30736]
        // totems use hardcoded models, tauren model is base
        $totems = [null, 4589, 4588, 4587, 4590];           // slot => modelId
        $data   = array_filter($this->displayIds);

        if (count($data) == 1 && ($slotId = array_search($data[0], $totems)))
            $data = DB::World()->selectCol('SELECT `DisplayId` FROM player_totem_model WHERE `TotemSlot` = %i', $slotId);

        return !$data ? 0 : $data[array_rand($data)];
    }

    public function getTameable() : ?int
    {
        if (!($this->typeFlags & NPC_TYPEFLAG_TAMEABLE) || $this->type != NPC_TYPE_BEAST)
            return null;

        return $this->family ?: null;
    }

    public function calcMeleeDamage() : array
    {
        $mleMin = ($this->dmgMin       + ($this->mleVariance * $this->mleAtkPwrMin / 14)) * $this->dmgMultiplier * $this->atkSpeed;
        $mleMax = ($this->dmgMax * 1.5 + ($this->mleVariance * $this->mleAtkPwrMax / 14)) * $this->dmgMultiplier * $this->atkSpeed;

        return [$mleMin, $mleMax];
    }

    public function calcRangedDamage() : array
    {
        $rngMin = ($this->dmgMin       + ($this->rngVariance * $this->rngAtkPwrMin / 14)) * $this->dmgMultiplier * $this->rngAtkSpeed;
        $rngMax = ($this->dmgMax * 1.5 + ($this->rngVariance * $this->rngAtkPwrMax / 14)) * $this->dmgMultiplier * $this->rngAtkSpeed;

        return [$rngMin, $rngMax];
    }

    public function isBoss() : bool
    {
        return ($this->cuFlags & NPC_CU_INSTANCE_BOSS) || ($this->typeFlags & NPC_TYPEFLAG_BOSS_MOB && $this->rank);
    }

    public function isMineable() : bool
    {
        return $this->skinLootId && ($this->typeFlags & NPC_TYPEFLAG_SKIN_WITH_MINING);
    }

    public function isGatherable() : bool
    {
        return $this->skinLootId && ($this->typeFlags & NPC_TYPEFLAG_SKIN_WITH_HERBALISM);
    }

    public function isSalvageable() : bool
    {
        return $this->skinLootId && ($this->typeFlags & NPC_TYPEFLAG_SKIN_WITH_ENGINEERING);
    }
}


?>
