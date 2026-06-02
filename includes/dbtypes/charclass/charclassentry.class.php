<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharClassEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly string    $fileString;
    public readonly int       $iconId;
    public readonly string    $icon;
    public readonly int       $powerType;
    public readonly int       $raceMask;
    public readonly int       $roles;
    /** @var int[] $skills */
    public readonly array     $skills;
    public readonly int       $flags;
    public readonly int       $weaponTypeMask;
    public readonly int       $armorTypeMask;
    public readonly int       $expansion;

    public static int    $dbType    = Type::CHR_CLASS;
    public static string $brickFile = 'class';
    public static string $dataTable = '::classes';

    public const /* string */ QUERY_BASE = 'SELECT c.*, c.`id` AS ARRAY_KEY FROM ::classes c';
    public const /* array */  QUERY_OPTS = array(
        'c'  => [['ic']],
        'ic' => ['j' => ['::icons ic ON ic.`id` = c.`iconId`', true], 's' => ', ic.`name` AS "iconString"']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name = new LocString($initData, 'name', pruneFromSrc: true);

        $this->icon = $initData['iconString'] ?: 'trade_engineering';

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'skills':
                    $this->skills = explode(' ', $initData['skills']);
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'     => $this->id,
            'name'   => $this->name,
            'races'  => $this->raceMask,
            'roles'  => $this->roles,
            'weapon' => $this->weaponTypeMask,
            'armor'  => $this->armorTypeMask,
            'power'  => $this->powerType,
        );

        if ($this->isHeroClass())
            $data['hero'] = 1;

        if ($this->expansion)
            $data['expansion'] = $this->expansion;

        return $data;
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name
        )]];
    }

    public function isHeroClass() : bool
    {
        return $this->flags & 0x40;
    }
}

?>
