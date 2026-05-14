<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenaTeamFilter extends Filter
{
    use TrProfilerFilter;

    protected string $type          = 'arenateams';
    protected static array $genericFilter = [];
    protected static array $inputFields   = array(
        'ex' => [parent::V_EQUAL,    'on',                 false], // only match exact - must be defined before 'na' as it's test relies on 'ex's value
        'na' => [parent::V_NAME,     true,                 false], // name - only printable chars, no delimiter
        'ma' => [parent::V_EQUAL,    1,                    false], // match any / all filter
        'si' => [parent::V_LIST,     [1, 2],               false], // side
        'sz' => [parent::V_LIST,     [2, 3, 5],            false], // tema size
        'rg' => [parent::V_CALLBACK, 'cbRegionCheck',      false], // region
        'bg' => [parent::V_EQUAL,    null,                 false], // battlegroup - unsued here, but var expected by template
        'sv' => [parent::V_CALLBACK, 'cbServerCheck',      false]  // server
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // region (rg), battlegroup (bg) and server (sv) are passed to ArenaTeamList as miscData and handled there

        // name [str]
        if ($_v['na'])
            if ($_ = $this->buildLikeLookup([['na', 'at.name']], $_v['ex'] == 'on'))
                $parts[] = $_;

        // side [list]
        if ($_v['si'] == SIDE_ALLIANCE)
            $parts[] = ['c.race', ChrRace::fromMask(ChrRace::MASK_ALLIANCE)];
        else if ($_v['si'] == SIDE_HORDE)
            $parts[] = ['c.race', ChrRace::fromMask(ChrRace::MASK_HORDE)];

        // size [int]
        if ($_v['sz'])
            $parts[] = ['at.type', $_v['sz']];

        return $parts;
    }
}

?>
