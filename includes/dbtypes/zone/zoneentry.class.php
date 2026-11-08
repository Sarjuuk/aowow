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

    public const string QUERY_BASE = 'SELECT z.*, z.`id` AS ARRAY_KEY FROM ::zones z';

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        parent::applyInitData($dbRow, $opts);

        $this->cuFlags       = $dbRow->cuFlags;
        $this->name          = new LocString($dbRow, 'name');
        $this->mapId         = $dbRow->mapId;
        $this->mapIdBak      = $dbRow->mapIdBak;
        $this->parentArea    = $dbRow->parentArea;
        $this->category      = $dbRow->category;
        $this->flags         = $dbRow->flags;
        $this->faction       = $dbRow->faction;
        $this->expansion     = $dbRow->expansion;
        $this->type          = $dbRow->type;
        $this->maxPlayer     = $dbRow->maxPlayer;
        $this->itemLevelReqN = $dbRow->itemLevelReqN;
        $this->itemLevelReqH = $dbRow->itemLevelReqH;
        $this->levelReq      = $dbRow->levelReq;
        $this->levelReqLFG   = $dbRow->levelReqLFG;
        $this->levelHeroic   = $dbRow->levelHeroic;
        $this->levelMin      = $dbRow->levelMin;
        $this->levelMax      = $dbRow->levelMax;
        $this->parentMapId   = $dbRow->parentMapId;
        $this->parentX       = $dbRow->parentX;
        $this->parentY       = $dbRow->parentY;

        // unpack attunements
        $attnmt = [];
        foreach (array_filter(explode(' ', $dbRow->attunementsN)) as $req)
        {
            [$type, $typeId] = explode(':', $req);
            $attnmt[$type] ??= [];
            $attnmt[$type][] = $typeId;
        }
        foreach (array_filter(explode(' ', $dbRow->attunementsH)) as $req)
        {
            [$type, $typeId] = explode(':', $req);
            $attnmt[$type] ??= [];
            $attnmt[$type][] = -$typeId;
        }
        $this->attunements = $attnmt;

        return true;
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

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name
        )]];
    }
}

?>
