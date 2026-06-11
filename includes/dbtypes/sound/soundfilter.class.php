<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundFilter extends Filter
{
    protected string $type        = 'sounds';
    protected static array $inputFields = array(
        'na' => [parent::V_NAME,  false,                                                        false], // name - only printable chars, no delimiter
        'ty' => [parent::V_LIST,  [[1, 4], 6, 9, 10, 12, 13, 14, 16, 17, [19, 31], 50, 52, 53], true ]  // type
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
            $parts[] = ['cat', $_v['ty']];

        return $parts;
    }
}

?>
