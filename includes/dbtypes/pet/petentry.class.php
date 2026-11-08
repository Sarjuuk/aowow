<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PetEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly int       $category;
    public readonly int       $minLevel;
    public readonly int       $maxLevel;
    public readonly int       $foodMask;
    public readonly int       $type;
    public readonly int       $exotic;
    public readonly int       $expansion;
    public readonly int       $iconId;
    public readonly string    $icon;
    public readonly int       $skillLineId;
    /** @var int[] $spells - length: 4 */
    public readonly array     $spells;
    public readonly int       $armor;
    public readonly int       $damage;
    public readonly int       $health;

    public static int    $dbType    = Type::PET;
    public static string $brickFile = 'pet';
    public static string $dataTable = '::pet';

    public const string QUERY_BASE = 'SELECT p.*, p.`id` AS ARRAY_KEY FROM ::pet p';
    public const array  QUERY_OPTS = array(
        'p'  => [['ic']],
        'ic' => ['j' => ['::icons ic ON p.`iconId` = ic.`id`', true], 's' => ', ic.`name` AS "icon"'],
    );

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        parent::applyInitData($dbRow, $opts);

        $this->cuFlags     = $dbRow->cuFlags;
        $this->name        = new LocString($dbRow, 'name');
        $this->category    = $dbRow->category;
        $this->minLevel    = $dbRow->minLevel;
        $this->maxLevel    = $dbRow->maxLevel;
        $this->foodMask    = $dbRow->foodMask;
        $this->type        = $dbRow->type;
        $this->exotic      = $dbRow->exotic;
        $this->expansion   = $dbRow->expansion;
        $this->iconId      = $dbRow->iconId;
        $this->icon        = $dbRow->icon;
        $this->skillLineId = $dbRow->skillLineId;
        $this->spells      = [$dbRow->spellId1, $dbRow->spellId2, $dbRow->spellId3, $dbRow->spellId4];
        $this->armor       = $dbRow->armor;
        $this->damage      = $dbRow->damage;
        $this->health      = $dbRow->health;

        return true;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'armor'    => $this->armor,
            'damage'   => $this->damage,
            'health'   => $this->health,
            'diet'     => $this->foodMask,
            'icon'     => $this->icon,
            'id'       => $this->id,
            'maxlevel' => $this->maxLevel,
            'minlevel' => $this->minLevel,
            'name'     => $this->name,
            'type'     => $this->type,
            'exotic'   => $this->exotic,
            'spells'   => array_filter($this->spells)
        );

        if ($this->expansion)
            $data['expansion'] = $this->expansion;

        return $data;
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add pet to @return
     *  - GLOBALINFO_RELATED - add pet spells to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        $data[self::$dbType][$this->id] = ['icon' => $this->icon];

        if ($addMask & GLOBALINFO_RELATED)
            if ($_ = array_filter($this->spells))
                $data[Type::SPELL] = array_combine($_, $_);

        return $data;
    }

    public function getFoodIds() : array
    {
        return Util::mask2bits($this->foodMask, 1);
    }
}

?>
