<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharRace extends DBType
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly int       $classMask;
    public readonly int       $factionId;
    public readonly int       $leader;
    public readonly int       $startAreaId;
    public readonly int       $side;
    public readonly int       $expansion;
    /** @var int[] $iconId [male, female] */
    public readonly array     $iconId;
    /** @var string[] $icon [male, female] */
    public readonly array     $icon;

    public static int    $dbType    = Type::CHR_RACE;
    public static string $brickFile = 'race';
    public static string $dataTable = '::races';

    public const /* string */ QUERY_BASE = 'SELECT r.*, r.`id` AS ARRAY_KEY FROM ::races r';
    public const /* array */  QUERY_OPTS = array(
        'r'  => [['ic0', 'ic1']],
        'ic0' => ['j' => ['::icons ic0 ON ic0.`id` = r.`iconId0`', true], 's' => ', ic0.`name` AS "iconStringMale"'],
        'ic1' => ['j' => ['::icons ic1 ON ic1.`id` = r.`iconId1`', true], 's' => ', ic1.`name` AS "iconStringFemale"']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name   = new LocString($initData, 'name', pruneFromSrc: true);
        $this->icon   = [$initData['iconStringMale'], $initData['iconStringFemale']];
        $this->iconId = [$initData['iconId0'], $initData['iconId1']];

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
            'id'      => $this->id,
            'name'    => $this->name,
            'classes' => $this->classMask,
            'faction' => $this->factionId,
            'leader'  => $this->leader,
            'zone'    => $this->startAreaId,
            'side'    => $this->side,
        );

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
}

?>
