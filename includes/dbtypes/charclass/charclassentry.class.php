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

    public const string QUERY_BASE = 'SELECT c.*, c.`id` AS ARRAY_KEY FROM ::classes c';
    public const array  QUERY_OPTS = array(
        'c'  => [['ic']],
        'ic' => ['j' => ['::icons ic ON ic.`id` = c.`iconId`', true], 's' => ', ic.`name` AS "icon"']
    );

    public function applyInitData(array $initData, array $opts) : void
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags        = $initData['cuFlags'];
        $this->name           = new LocString($initData, 'name');
        $this->fileString     = $initData['fileString'];
        $this->iconId         = $initData['iconId'];
        $this->icon           = $initData['icon'] ?: 'trade_engineering';
        $this->powerType      = $initData['powerType'];
        $this->raceMask       = $initData['raceMask'];
        $this->roles          = $initData['roles'];
        $this->skills         = explode(' ', $initData['skills']);
        $this->flags          = $initData['flags'];
        $this->weaponTypeMask = $initData['weaponTypeMask'];
        $this->armorTypeMask  = $initData['armorTypeMask'];
        $this->expansion      = $initData['expansion'];
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

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
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
