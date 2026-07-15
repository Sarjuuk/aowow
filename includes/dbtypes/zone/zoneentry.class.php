<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ZoneEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly int       $mapId;
    public readonly int       $mapIdBak;
    public readonly int       $parentArea;
    public readonly int       $category;
    public readonly int       $flags;
    public readonly int       $faction;
    public readonly int       $expansion;
    public readonly int       $type;
    public readonly int       $maxPlayer;
    public readonly int       $itemLevelReqN;
    public readonly int       $itemLevelReqH;
    public readonly int       $levelReq;
    public readonly int       $levelReqLFG;
    public readonly int       $levelHeroic;
    public readonly int       $levelMin;
    public readonly int       $levelMax;
    public readonly array     $attunements;
    public readonly int       $parentMapId;
    public readonly float     $parentX;
    public readonly float     $parentY;

    public static int    $dbType    = Type::ZONE;
    public static string $brickFile = 'zone';
    public static string $dataTable = '::zones';

    public const /* string */ QUERY_BASE = 'SELECT z.*, z.`id` AS ARRAY_KEY FROM ::zones z';

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name = new LocString($initData, 'name', pruneFromSrc: true);

        // unpack attunements
        $attnmt = [];
        foreach (array_filter(explode(' ', $initData['attunementsN'])) as $req)
        {
            [$type, $typeId] = explode(':', $req);
            $attnmt[$type] ??= [];
            $attnmt[$type][] = $typeId;
        }
        foreach (array_filter(explode(' ', $initData['attunementsH'])) as $req)
        {
            [$type, $typeId] = explode(':', $req);
            $attnmt[$type] ??= [];
            $attnmt[$type][] = -$typeId;
        }
        $this->attunements = $attnmt;

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
        return array(
            'id'          => $this->id,
            'category'    => $this->category,
            'territory'   => $this->faction,
            'minlevel'    => $this->levelMin,
            'maxlevel'    => $this->levelMax,
            'name'        => $this->name,
            'expansion'   => $this->expansion   ?: null,
            'instance'    => $this->type        ?: null,
            'nplayers'    => $this->maxPlayer   ?: null,
            'reqlevel'    => $this->levelReq    ?: null,
            'lfgReqLevel' => $this->levelReqLFG ?: null,
            'heroicLevel' => $this->levelHeroic ?: null
        );
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name
        )]];
    }

    public function renderTooltip() : ?string { return null; }
}

?>
