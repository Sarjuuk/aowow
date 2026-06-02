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

    public const /* string */ QUERY_BASE = 'SELECT p.*, p.`id` AS ARRAY_KEY FROM ::pet p';
    public const /* array  */ QUERY_OPTS = array(
        'p'  => [['ic']],
        'ic' => ['j' => ['::icons ic ON p.`iconId` = ic.`id`', true], 's' => ', ic.`name` AS "icon"'],
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name = new LocString($initData, 'name', pruneFromSrc: true);

        $this->spells = [$initData['spellId1'], $initData['spellId2'], $initData['spellId3'], $initData['spellId4']];

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

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        if ($addMask & GLOBALINFO_RELATED)
            if ($_ = array_filter($this->spells))
                $data[Type::SPELL] = array_combine($_, $_);

        if ($addMask & GLOBALINFO_SELF)
            $data[Type::PET][$this->id] = ['icon' => $this->icon];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }

    public function getFoodIds() : array
    {
        return Util::mask2bits($this->foodMask, 1);
    }
}

?>
