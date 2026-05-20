<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreatriggerFilter extends Filter
{
    protected string $type          = 'areatrigger';

    protected static array $enums = array(
        3 => parent::ENUM_ZONE                              // foundin
    );

    protected static array $genericFilter = array(
        2 => [parent::CR_NUMERIC, 'id',       NUM_CAST_INT     ], // id
        3 => [parent::CR_ENUM,    's.areaId', false,       true]  // foundin
    );

    // fieldId => [checkType, checkValue[, fieldIsArray]]
    protected static array $inputFields = array(
        'cr'  => [parent::V_LIST,  [2, 3],               true ], // criteria ids
        'crs' => [parent::V_RANGE, [1, 4987],            true ], // criteria operators
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
            if ($_ = $this->buildLikeLookup([['na', 'name']]))
                $parts[] = $_;

        // type [list]
        if ($_v['ty'])
            $parts[] = ['type', $_v['ty']];

        return $parts;
    }
}

?>
