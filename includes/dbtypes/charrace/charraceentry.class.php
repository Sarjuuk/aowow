<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharRaceEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly int       $classMask;
 // public readonly int       $flags;
    public readonly int       $factionId;
    public readonly int       $startAreaId;
    public readonly int       $leader;
 // public readonly int       $baseLanguage;
    public readonly int       $side;
    public readonly string    $fileString;
    /** @var int[] $iconId [male, female] */
    public readonly array     $iconId;
    /** @var string[] $icon [male, female] */
    public readonly array     $icon;
    public readonly int       $expansion;

    public static int    $dbType    = Type::CHR_RACE;
    public static string $brickFile = 'race';
    public static string $dataTable = '::races';

    public const string QUERY_BASE = 'SELECT r.*, r.`id` AS ARRAY_KEY FROM ::races r';
    public const array  QUERY_OPTS = array(
        'r'  => [['ic0', 'ic1']],
        'ic0' => ['j' => ['::icons ic0 ON ic0.`id` = r.`iconId0`', true], 's' => ', ic0.`name` AS "iconMale"'],
        'ic1' => ['j' => ['::icons ic1 ON ic1.`id` = r.`iconId1`', true], 's' => ', ic1.`name` AS "iconFemale"']
    );

    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags     = $initData['cuFlags'];
        $this->name        = new LocString($initData, 'name');
        $this->classMask   = $initData['classMask'];
        $this->factionId   = $initData['factionId'];
        $this->startAreaId = $initData['startAreaId'];
        $this->leader      = $initData['leader'];
        $this->side        = $initData['side'];
        $this->fileString  = $initData['fileString'];
        $this->iconId      = [$initData['iconId0'], $initData['iconId1']];
        $this->icon        = [$initData['iconMale'], $initData['iconFemale']];
        $this->expansion   = $initData['expansion'];

        return true;
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

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name
        )]];
    }
}

?>
