<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreatriggerEntry extends DBTypeEntry
{
    public readonly  int        $cuFlags;
    public readonly  string     $name;                       // not localized
    public readonly  int        $type;
    public readonly ?int        $quest;
    public readonly  float      $orientation;
    /** @var int[] $location areaIds [self[, teleporterDest]] */
    public readonly  array      $location;

    public static int    $dbType     = Type::AREATRIGGER;
    public static string $brickFile  = 'areatrigger';
    public static string $dataTable  = '::areatrigger';
    public static int    $contribute = CONTRIBUTE_CO;

    public const string QUERY_BASE = 'SELECT a.*, a.id AS ARRAY_KEY FROM ::areatrigger a';
    public const array  QUERY_OPTS = array(
        'a' => [['s']],                                     // guid < 0 are teleporter targets, so exclude them here
        's' => ['j' => ['::spawns s ON s.`type` = 503 AND s.`typeId` = a.`id` AND s.`guid` > 0', true], 's' => ', GROUP_CONCAT(s.`areaId` ORDER BY s.`typeId` DESC) AS "location"', 'g' => 'a.`id`']
    );

    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags     = $initData['cuFlags'];
        $this->name        = $initData['name'] ?: Lang::areatrigger('unnamed', [$initData['id']]);
        $this->type        = $initData['type'];
        $this->quest       = $initData['quest'];
        $this->orientation = $initData['orientation'];
        $this->location    = explode(',', $initData['location']);

        return true;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'   => $this->id,
            'type' => $this->type,
            'name' => $this->name
        );

        if ($_ = $this->location)
            $data['location'] = $_;

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array { return []; }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->selectRow('SELECT IF(`name`, `name`, %s) AS "name_loc0" FROM %n WHERE `id` = %i', Lang::areatrigger('unnamed', [$id]), self::$dataTable, $id))
            return new LocString($n);
        return null;
    }
}

?>
