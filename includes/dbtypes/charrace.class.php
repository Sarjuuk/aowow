<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharRaceList extends DBTypeList
{
    public static int    $type      = Type::CHR_RACE;
    public static string $brickFile = 'race';
    public static string $dataTable = '?_races';

    protected string $queryBase = 'SELECT r.*, id AS ARRAY_KEY FROM ?_races r';

    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'      => $this->id,
                'name'    => $this->getField('name', true),
                'classes' => $this->curTpl['classMask'],
                'faction' => $this->curTpl['factionId'],
                'leader'  => $this->curTpl['leader'],
                'zone'    => $this->curTpl['startAreaId'],
                'side'    => $this->curTpl['side']
            );

            if ($this->curTpl['expansion'])
                $data[$this->id]['expansion'] = $this->curTpl['expansion'];
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::CHR_RACE][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
