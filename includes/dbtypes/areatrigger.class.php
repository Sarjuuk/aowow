<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreaTriggerList extends DBTypeList
{
    use spawnHelper;

    public static int    $type       = Type::AREATRIGGER;
    public static string $brickFile  = 'areatrigger';
    public static string $dataTable  = '?_areatrigger';
    public static int    $contribute = CONTRIBUTE_CO;

    protected string $queryBase = 'SELECT a.*, a.id AS ARRAY_KEY FROM ?_areatrigger a';
    protected array  $queryOpts = array(
                        'a' => [['s']],                     // guid < 0 are teleporter targets, so exclude them here
                        's' => ['j' => ['?_spawns s ON s.`type` = 503 AND s.`typeId` = a.`id` AND s.`guid` > 0', true], 's' => ', GROUP_CONCAT(s.`areaId`) AS "areaId"', 'g' => 'a.`id`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        foreach ($this->iterate() as $id => &$_curTpl)
            if (!$_curTpl['name'])
                $_curTpl['name'] = 'Unnamed Areatrigger #' . $id;
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT IF(`name`, `name`, CONCAT("Unnamed Areatrigger #", `id`) AS "name_loc0" FROM ?# WHERE `id` = ?d', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'      => $this->curTpl['id'],
                'type'    => $this->curTpl['type'],
                'name'    => $this->curTpl['name'],
            );

            if ($_ = $this->curTpl['areaId'])
                $data[$this->id]['location'] = explode(',', $_);
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array { return []; }

    public function renderTooltip() : ?string { return null; }
}

class AreaTriggerListFilter extends Filter
{
    protected string $type          = 'areatrigger';
    protected static array $genericFilter = array(
        2 => [parent::CR_NUMERIC, 'id', NUM_CAST_INT]       // id
    );

    // fieldId => [checkType, checkValue[, fieldIsArray]]
    protected static array $inputFields = array(
        'cr'  => [parent::V_LIST,  [2],                  true ], // criteria ids
        'crs' => [parent::V_RANGE, [1, 6],               true ], // criteria operators
        'crv' => [parent::V_REGEX, parent::PATTERN_INT,  true ], // criteria values - all criteria are numeric here
        'na'  => [parent::V_NAME,  false,                false], // name - only printable chars, no delimiter
        'ma'  => [parent::V_EQUAL, 1,                    false], // match any / all filter
        'ty'  => [parent::V_RANGE, [0, 5],               true ]  // types
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name [str]
        if ($_v['na'])
            if ($_ = $this->buildLikeLookup(['na' => 'name']))
                $parts[] = $_;

        // type [list]
        if ($_v['ty'])
            $parts[] = ['type', $_v['ty']];

        return $parts;
    }
}

?>
