<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconFilter extends Filter
{
    private array $iconTotals      = [];
    private array $criterion2field = array(
          1 => '::items',                                   // items [num]
          2 => '::spell',                                   // spells [num]
          3 => '::achievement',                             // achievements [num]
    //    4 => '',                                          // battlepets [num]
    //    5 => '',                                          // battlepetabilities [num]
          6 => '::currencies',                              // currencies [num]
    //    7 => '',                                          // garrisonabilities [num]
    //    8 => '',                                          // garrisonbuildings [num]
          9 => '::pet',                                     // hunterpets [num]
    //   10 => '',                                          // garrisonmissionthreats [num]
         11 => '::classes',                                 // classes [num]
         13 => ''                                           // used [num]
    );

    protected string $type          = 'icons';
    protected static array $genericFilter = array(
         1 => [parent::CR_CALLBACK, 'cbUsedBy'      ],      // items [num]
         2 => [parent::CR_CALLBACK, 'cbUsedBy'      ],      // spells [num]
         3 => [parent::CR_CALLBACK, 'cbUsedBy'      ],      // achievements [num]
         6 => [parent::CR_CALLBACK, 'cbUsedBy'      ],      // currencies [num]
         9 => [parent::CR_CALLBACK, 'cbUsedBy'      ],      // hunterpets [num]
        11 => [parent::CR_CALLBACK, 'cbUsedBy'      ],      // classes [num]
        13 => [parent::CR_CALLBACK, 'cbUsedBy', true]       // used [num]
    );

    protected static array $inputFields = array(
        'cr'  => [parent::V_LIST,  [1, 2, 3, 6, 9, 11, 13], true ], // criteria ids
        'crs' => [parent::V_RANGE, [1, 6],                  true ], // criteria operators
        'crv' => [parent::V_REGEX, parent::PATTERN_INT,     true ], // criteria values - all criteria are numeric here
        'na'  => [parent::V_NAME,  false,                   false], // name - only printable chars, no delimiter
        'ma'  => [parent::V_EQUAL, 1,                       false]  // match any / all filter
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        //string
        if ($_v['na'])
            if ($_ = $this->buildLikeLookup([['na', 'name']]))
                $parts[] = $_;

        return $parts;
    }

    protected function cbUsedBy(int $cr, int $crs, string $crv, ?bool $all = false) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || ![$filter, $negate] = $this->int2Filter($crs, $crv))
            return null;

        $total = $this->prepareIconTotals($all ? 0 : $cr);

        $ids = array_filter($total, $filter);

        if ($negate)
            return $ids ? ['id', array_keys($ids), '!'] : [1];
        else
            return $ids ? ['id', array_keys($ids)] : ['id', array_keys($total), '!'];
    }

    private function int2Filter(mixed $op, int $y) : ?array
    {
        return match ($op) {
            1       => [fn($x) => $x >  $y, false],
            2       => [fn($x) => $x >= $y, false],
            3       => [fn($x) => $x == $y, false],
            4       => [fn($x) => $x >  $y, true],
            5       => [fn($x) => $x >= $y, true],
            6       => [fn($x) => $x == $y, true],
            default => null
        };
    }

    private function prepareIconTotals(int $forCr = 0) : array
    {
        foreach ($this->criterion2field as $cr => $tbl)
        {
            if (!$tbl || isset($this->iconTotals[$cr]) || ($forCr && $forCr != $cr))
                continue;

            $this->iconTotals[$cr] = DB::Aowow()->selectCol('SELECT `iconId` AS ARRAY_KEY, COUNT(*) AS "n" FROM %n GROUP BY `iconId`', $tbl);
        }

        if ($forCr)
            return $this->iconTotals[$forCr];

        if (!isset($this->iconTotals['all']))
        {
            $this->iconTotals['all'] = [];
            Util::arraySumByKey($this->iconTotals['all'], ...$this->iconTotals);
        }

        return $this->iconTotals['all'];
    }
}

?>
